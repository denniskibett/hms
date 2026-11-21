<?php

namespace Tests\Feature;

use App\Helpers\SystemHelper;
use App\Models\EtimsReceipt;
use App\Models\Invoice;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Stay;
use App\Models\System;
use App\Models\User;
use App\Services\BillingService;
use App\Services\BookingService;
use App\Services\Etims\Etims;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EtimsTest extends TestCase
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
            'guest_id' => User::factory()->create(['name' => 'Jane Guest'])->id,
            'arrival_date' => today()->toDateString(),
            'departure_date' => today()->addDays(2)->toDateString(),
            'meal_plan' => 'BB',
            'status' => 'booked',
            'rooms' => [['room_id' => $room->id, 'adults' => 2, 'children' => 0]],
        ], $this->clerk);
    }

    private function turnOn(): void
    {
        $system = System::settings();
        $settings = $system->settings ?? [];
        $settings['billing']['kra_pin'] = 'P051234567X';
        $settings['etims'] = ['enabled' => true, 'environment' => 'sandbox', 'bhf_id' => '00', 'device_serial' => 'DVC1', 'cmc_key' => Crypt::encryptString('cmc-secret')];
        $system->update(['settings' => $settings]);
        SystemHelper::clearCache();
    }

    private function invoice(): Invoice
    {
        return $this->stay->invoices()->sole();
    }

    private function payInFull(): void
    {
        app(BillingService::class)->recordPayment($this->invoice(), ['amount' => $this->invoice()->total, 'method' => 'cash'], $this->clerk);
    }

    public function test_nothing_is_sent_while_etims_is_off(): void
    {
        Http::fake();
        $this->payInFull();

        Http::assertNothingSent();
        $this->assertSame(0, EtimsReceipt::count());
    }

    public function test_a_fully_paid_invoice_is_signed_by_kra_and_printed_with_the_qr(): void
    {
        $this->turnOn();
        Http::fake(['*/saveTrnsSalesOsdc' => Http::response(['resultCd' => '000', 'resultMsg' => 'It is succeeded', 'data' => [
            'rcptNo' => 41, 'totRcptNo' => 120, 'intrlData' => 'INTRL123', 'rcptSign' => 'SIGN456', 'sdcId' => 'KRACU0100000001', 'mrcNo' => 'WIS01000001',
        ]])]);

        $this->assertTrue(Etims::enabled(), 'eTIMS should be on');
        $this->payInFull();
        $this->assertSame('paid', $this->invoice()->status);

        $receipt = $this->invoice()->etimsReceipt;
        $this->assertSame('sent', $receipt->status);
        $this->assertSame('SIGN456', $receipt->rcpt_sign);
        $this->assertSame(1, $receipt->invc_no);

        Http::assertSent(function ($request) {
            $body = $request->data();

            return $request->hasHeader('cmcKey', 'cmc-secret')
                && $request->hasHeader('tin', 'P051234567X')
                && $body['totAmt'] == 10000
                && $body['taxRtB'] == 16
                && abs($body['totTaxAmt'] - 1379.31) < 0.05   // VAT inside 10,000 at 16%
                && $body['itemList'][0]['taxTyCd'] === 'B';
        });

        // Paying again (or a retry) never sends twice
        app(Etims::class)->submit($this->invoice());
        Http::assertSentCount(1);

        $this->actingAs($this->clerk)->get(route('stays.invoice', $this->stay))
            ->assertOk()->assertSee('KRA eTIMS')->assertSee('SIGN456')->assertSee('<svg', false);
    }

    public function test_a_failed_submission_is_kept_for_retry(): void
    {
        $this->turnOn();
        Http::fake(['*/saveTrnsSalesOsdc' => Http::sequence()
            ->push(['resultCd' => '910', 'resultMsg' => 'Request parameter error'])
            ->push(['resultCd' => '000', 'data' => ['rcptNo' => 1, 'rcptSign' => 'OK1', 'intrlData' => 'X', 'sdcId' => 'S']])]);

        $this->payInFull();
        $this->assertSame('failed', $this->invoice()->etimsReceipt->status);

        $this->artisan('etims:retry')->assertSuccessful();
        $this->assertSame('sent', $this->invoice()->etimsReceipt->fresh()->status);
    }
}
