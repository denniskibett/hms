<?php

namespace Tests\Feature;

use App\Exceptions\BillingException;
use App\Helpers\SystemHelper;
use App\Models\Invoice;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Stay;
use App\Models\System;
use App\Models\User;
use App\Services\BillingService;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingTest extends TestCase
{
    use RefreshDatabase;

    private BookingService $bookings;
    private BillingService $billing;
    private User $clerk;
    private Room $room;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bookings = app(BookingService::class);
        $this->billing = app(BillingService::class);
        $this->clerk = $this->userWithRole('receptionist');
        $this->room = Room::factory()->create(['room_type_id' => RoomType::factory()->create(['base_rate' => 5000, 'capacity' => 2])->id]);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::firstOrCreate(['name' => $role])->id);

        return $user;
    }

    private function book(int $from = 0, int $to = 2, string $status = 'booked'): Stay
    {
        return $this->bookings->create([
            'guest_id' => User::factory()->create()->id,
            'arrival_date' => today()->addDays($from)->toDateString(),
            'departure_date' => today()->addDays($to)->toDateString(),
            'meal_plan' => 'BB',
            'status' => $status,
            'rooms' => [['room_id' => $this->room->id, 'adults' => 2, 'children' => 0]],
        ], $this->clerk);
    }

    private function invoice(Stay $stay): Invoice
    {
        return $stay->invoices()->sole();
    }

    // ---- Invoice ---------------------------------------------------------

    public function test_booking_creates_a_tax_inclusive_invoice(): void
    {
        $invoice = $this->invoice($this->book(0, 2));

        // Guest pays the price shown; the invoice shows the tax inside it
        $this->assertSame('10000.00', $invoice->total);              // 2 nights × 5,000
        $this->assertTrue($invoice->tax_inclusive);
        $this->assertSame('1355.93', $invoice->vat_amount);         // 10,000 / 1.18 × 16%
        $this->assertSame('169.49', $invoice->levy_amount);         // 10,000 / 1.18 × 2%
        $this->assertSame('8474.58', $invoice->subtotal);
        $this->assertSame('sent', $invoice->status);
        $this->assertMatchesRegularExpression('/^INV-\d{4}-\d{5}$/', $invoice->invoice_number);
        $this->assertSame(1, $invoice->items()->count());
        $this->assertEquals(2, $invoice->items->first()->quantity);
    }

    public function test_tax_exclusive_setting_adds_tax_on_top(): void
    {
        System::settings()->update(['settings' => ['billing' => ['prices_include_tax' => false]]]);
        SystemHelper::clearCache();

        $invoice = $this->invoice($this->book(0, 2));

        $this->assertSame('10000.00', $invoice->subtotal);
        $this->assertSame('1600.00', $invoice->vat_amount);
        $this->assertSame('200.00', $invoice->levy_amount);
        $this->assertSame('11800.00', $invoice->total);
    }

    public function test_invoice_follows_booking_changes(): void
    {
        $stay = $this->book(1, 3);
        $data = [
            'guest_id' => $stay->guest_id,
            'arrival_date' => today()->addDay()->toDateString(),
            'departure_date' => today()->addDays(5)->toDateString(),
            'meal_plan' => 'BB',
            'rooms' => [['room_id' => $this->room->id, 'adults' => 2, 'children' => 0]],
        ];

        $this->bookings->update($stay, $data, $this->clerk);

        $invoice = $this->invoice($stay);
        $this->assertSame('20000.00', $invoice->total);
        $this->assertSame(1, $invoice->items()->count()); // rebuilt, not duplicated
    }

    public function test_early_checkout_bills_only_nights_used(): void
    {
        $stay = $this->book(0, 5, 'checked_in');
        $this->travelTo(today()->addDays(2)->setTime(10, 0));

        $this->bookings->checkOut($stay, $this->clerk);

        $this->assertSame('10000.00', $this->invoice($stay)->total);
    }

    public function test_cancelling_an_unpaid_booking_cancels_its_invoice(): void
    {
        $stay = $this->book(1, 3);

        $this->bookings->cancel($stay, $this->clerk);

        $this->assertSame('cancelled', $this->invoice($stay)->status);
    }

    public function test_cancelling_a_booking_with_a_deposit_keeps_the_invoice_open(): void
    {
        $stay = $this->book(1, 3);
        $this->billing->recordPayment($this->invoice($stay), ['amount' => 3000, 'method' => 'cash'], $this->clerk);

        $this->bookings->cancel($stay, $this->clerk);

        $this->assertSame('partial', $this->invoice($stay)->status); // deposit needs handling (refund/forfeit)
    }

    // ---- Payments --------------------------------------------------------

    public function test_partial_then_full_payment(): void
    {
        $invoice = $this->invoice($this->book(0, 2));

        $this->billing->recordPayment($invoice, ['amount' => 4000, 'method' => 'mobile_money', 'payment_reference' => 'QJK3XYZ12A'], $this->clerk);
        $this->assertSame('partial', $invoice->fresh()->status);
        $this->assertSame('4000.00', $invoice->fresh()->paid_amount);

        $payment = $this->billing->recordPayment($invoice, ['amount' => 6000, 'method' => 'cash'], $this->clerk);
        $this->assertSame('paid', $invoice->fresh()->status);
        $this->assertSame('completed', $payment->status);
        $this->assertStringStartsWith('PAY-', $payment->payment_reference);
    }

    public function test_overpayment_and_reused_codes_are_rejected(): void
    {
        $invoice = $this->invoice($this->book(0, 2));
        $this->billing->recordPayment($invoice, ['amount' => 1000, 'method' => 'mobile_money', 'payment_reference' => 'QJK3XYZ12A'], $this->clerk);

        foreach ([
            ['amount' => 9500, 'method' => 'cash'],                                               // balance is 9,000
            ['amount' => 500, 'method' => 'mobile_money', 'payment_reference' => 'QJK3XYZ12A'],   // same M-Pesa code
            ['amount' => 0, 'method' => 'cash'],
        ] as $data) {
            try {
                $this->billing->recordPayment($invoice, $data, $this->clerk);
                $this->fail('Payment should have been rejected: ' . json_encode($data));
            } catch (BillingException) {
                $this->addToAssertionCount(1);
            }
        }

        $this->assertSame('1000.00', $invoice->fresh()->paid_amount);
    }

    public function test_voiding_a_payment_restores_the_balance(): void
    {
        $invoice = $this->invoice($this->book(0, 2));
        $payment = $this->billing->recordPayment($invoice, ['amount' => 10000, 'method' => 'cash'], $this->clerk);

        $this->billing->voidPayment($payment, $this->clerk, 'Wrong booking');

        $this->assertSame('cancelled', $payment->fresh()->status);
        $this->assertSame('0.00', $invoice->fresh()->paid_amount);
        $this->assertSame('sent', $invoice->fresh()->status);
    }

    // ---- Pages -----------------------------------------------------------

    public function test_front_desk_records_payment_and_prints_receipt_and_invoice(): void
    {
        $stay = $this->book(0, 2);

        $this->actingAs($this->clerk)
            ->post(route('stays.payments.store', $stay), ['amount' => 2500, 'method' => 'mobile_money', 'payment_reference' => 'QJK3XYZ12A'])
            ->assertSessionHas('success')
            ->assertSessionHas('receipt_url');

        $payment = $this->invoice($stay)->payments()->sole();

        $this->actingAs($this->clerk)->get(route('stays.receipt', [$stay, $payment]))
            ->assertOk()->assertSee('Receipt')->assertSee('QJK3XYZ12A')->assertSee('VAT 16%')->assertSee('Tourism Levy 2%');

        $this->actingAs($this->clerk)->get(route('stays.invoice', $stay))
            ->assertOk()->assertSee($this->invoice($stay)->invoice_number)->assertSee('Total (tax inclusive)');

        $this->actingAs($this->clerk)->get(route('stays.show', $stay))
            ->assertOk()->assertSee('Includes VAT 16%');
    }

    public function test_mpesa_payment_needs_a_transaction_code(): void
    {
        $stay = $this->book(0, 2);

        $this->actingAs($this->clerk)
            ->post(route('stays.payments.store', $stay), ['amount' => 2500, 'method' => 'mobile_money'])
            ->assertSessionHasErrors('payment_reference');
    }

    public function test_only_managers_and_finance_can_void(): void
    {
        $stay = $this->book(0, 2);
        $payment = $this->billing->recordPayment($this->invoice($stay), ['amount' => 1000, 'method' => 'cash'], $this->clerk);

        $this->actingAs($this->clerk)->post(route('stays.payments.void', [$stay, $payment]))->assertForbidden();

        $this->actingAs($this->userWithRole('finance'))
            ->post(route('stays.payments.void', [$stay, $payment]), ['reason' => 'Duplicate'])
            ->assertSessionHas('success');
        $this->assertSame('cancelled', $payment->fresh()->status);
    }

    public function test_sync_command_rebuilds_existing_invoices(): void
    {
        $stay = $this->book(0, 2);
        $this->invoice($stay)->update(['total' => 0, 'subtotal' => 0]); // like the old broken invoices

        $this->artisan('hms:sync-invoices')->assertSuccessful();

        $this->assertSame('10000.00', $this->invoice($stay)->total);
    }
}
