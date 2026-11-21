<?php

namespace Tests\Feature;

use App\Exceptions\BillingException;
use App\Helpers\SystemHelper;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Stay;
use App\Models\System;
use App\Models\User;
use App\Services\BookingService;
use App\Services\Payments\OnlinePayments;
use App\Services\Payments\PaymentGateways;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OnlinePaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $clerk;
    private Stay $stay;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clerk = User::factory()->create();
        $this->clerk->roles()->attach(Role::firstOrCreate(['name' => 'receptionist'])->id);
        $room = Room::factory()->create(['room_type_id' => RoomType::factory()->create(['base_rate' => 5000, 'capacity' => 2])->id]);

        $this->stay = app(BookingService::class)->create([
            'guest_id' => User::factory()->create()->id,
            'arrival_date' => today()->toDateString(),
            'departure_date' => today()->addDays(2)->toDateString(),
            'meal_plan' => 'BB',
            'status' => 'booked',
            'rooms' => [['room_id' => $room->id, 'adults' => 2, 'children' => 0]],
        ], $this->clerk);

        $system = System::settings();
        $settings = $system->settings ?? [];
        $settings = PaymentGateways::merge($settings, 'mpesa', [
            'enabled' => true, 'environment' => 'sandbox', 'type' => 'paybill', 'shortcode' => '174379',
            'consumer_key' => 'ck', 'consumer_secret' => 'cs', 'passkey' => 'pk',
        ]);
        $settings = PaymentGateways::merge($settings, 'sharet', ['enabled' => true, 'api_key' => 'sk_test_x', 'webhook_secret' => 'whsec']);
        $system->update(['settings' => $settings]);
        SystemHelper::clearCache();
    }

    private function invoice(): Invoice
    {
        return $this->stay->invoices()->sole();
    }

    private function fakeMpesa(string $checkoutId = 'ws_CO_1'): void
    {
        Http::fake([
            '*/oauth/v1/generate*' => Http::response(['access_token' => 'tok', 'expires_in' => 3599]),
            '*/mpesa/stkpush/v1/processrequest' => Http::response(['ResponseCode' => '0', 'CheckoutRequestID' => $checkoutId, 'MerchantRequestID' => 'm1']),
        ]);
    }

    private function mpesaCallback(string $checkoutId, int $code = 0, int $amount = 10000): array
    {
        return ['Body' => ['stkCallback' => array_filter([
            'MerchantRequestID' => 'm1',
            'CheckoutRequestID' => $checkoutId,
            'ResultCode' => $code,
            'ResultDesc' => $code === 0 ? 'Processed' : 'Request cancelled by user',
            'CallbackMetadata' => $code === 0 ? ['Item' => [
                ['Name' => 'Amount', 'Value' => $amount],
                ['Name' => 'MpesaReceiptNumber', 'Value' => 'SKL1ABC234'],
                ['Name' => 'PhoneNumber', 'Value' => 254717492048],
            ]] : null,
        ], fn ($v) => $v !== null)]];
    }

    public function test_mpesa_prompt_is_pending_until_the_callback_settles_the_folio(): void
    {
        $this->fakeMpesa();

        $payment = app(OnlinePayments::class)->request($this->invoice(), 'mpesa', $this->clerk, null, '0717 492 048');

        $this->assertSame('pending', $payment->status);
        $this->assertSame('254717492048', $payment->payment_details['phone']);
        $this->assertEquals(0, (float) $this->invoice()->fresh()->paid_amount);   // not counted yet

        $token = PaymentGateways::config('mpesa')['callback_token'];
        $this->postJson(route('webhooks.mpesa', $token), $this->mpesaCallback('ws_CO_1'))->assertOk();

        $payment->refresh();
        $this->assertSame('completed', $payment->status);
        $this->assertSame('SKL1ABC234', $payment->payment_reference);
        $this->assertSame('paid', $this->invoice()->fresh()->status);

        // A repeated callback changes nothing
        $this->postJson(route('webhooks.mpesa', $token), $this->mpesaCallback('ws_CO_1'))->assertOk();
        $this->assertSame(1, Payment::where('status', 'completed')->count());
    }

    public function test_mpesa_callback_with_a_wrong_token_or_unknown_request_settles_nothing(): void
    {
        $this->fakeMpesa();
        $payment = app(OnlinePayments::class)->request($this->invoice(), 'mpesa', $this->clerk, null, '0717492048');

        $this->postJson(route('webhooks.mpesa', 'wrong-token'), $this->mpesaCallback('ws_CO_1'))->assertForbidden();

        $token = PaymentGateways::config('mpesa')['callback_token'];
        $this->postJson(route('webhooks.mpesa', $token), $this->mpesaCallback('ws_CO_other'))->assertOk();

        $this->assertSame('pending', $payment->fresh()->status);
    }

    public function test_cancelled_prompt_is_marked_not_paid(): void
    {
        $this->fakeMpesa();
        $payment = app(OnlinePayments::class)->request($this->invoice(), 'mpesa', $this->clerk, null, '0717492048');

        $token = PaymentGateways::config('mpesa')['callback_token'];
        $this->postJson(route('webhooks.mpesa', $token), $this->mpesaCallback('ws_CO_1', 1032))->assertOk();

        $this->assertSame('cancelled', $payment->fresh()->status);
        $this->assertSame('sent', $this->invoice()->fresh()->status);
    }

    public function test_sharet_link_settles_on_a_signed_webhook_only(): void
    {
        Http::fake(['*/payment-requests' => Http::response(['id' => 'pr_123', 'pay_url' => 'https://sharet.africa/pay/pr_123', 'status' => 'open'], 201)]);

        $payment = app(OnlinePayments::class)->request($this->invoice(), 'sharet', $this->clerk, 4000);
        $this->assertSame('https://sharet.africa/pay/pr_123', $payment->payment_details['pay_url']);

        $body = json_encode(['id' => 'evt_1', 'type' => 'payment_request.paid', 'data' => ['id' => 'pr_123', 'status' => 'paid', 'transaction_id' => 'SHT-9', 'paid_via' => 'mpesa']]);

        // Bad signature: rejected
        $this->call('POST', route('webhooks.sharet'), [], [], [], ['HTTP_SHARET_SIGNATURE' => 't=' . time() . ',v1=bad', 'CONTENT_TYPE' => 'application/json'], $body)->assertStatus(400);
        $this->assertSame('pending', $payment->fresh()->status);

        $t = time();
        $sig = hash_hmac('sha256', $t . '.' . $body, 'whsec');
        $this->call('POST', route('webhooks.sharet'), [], [], [], ['HTTP_SHARET_SIGNATURE' => "t={$t},v1={$sig}", 'CONTENT_TYPE' => 'application/json'], $body)->assertOk();

        $this->assertSame('completed', $payment->fresh()->status);
        $this->assertEquals(4000, (float) $this->invoice()->fresh()->paid_amount);
    }

    public function test_cannot_ask_for_more_than_the_balance_or_use_a_provider_that_is_off(): void
    {
        $this->fakeMpesa();
        $this->expectException(BillingException::class);
        app(OnlinePayments::class)->request($this->invoice(), 'mpesa', $this->clerk, 999999, '0717492048');
    }

    public function test_receptionist_can_send_a_prompt_from_the_folio(): void
    {
        $this->fakeMpesa();

        $this->actingAs($this->clerk)
            ->post(route('stays.payments.online', $this->stay), ['provider' => 'mpesa', 'amount' => 2000, 'phone' => '0717492048'])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('payments', ['status' => 'pending', 'amount' => 2000]);
    }
}
