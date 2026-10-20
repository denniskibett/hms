<?php

namespace Tests\Feature;

use App\Exceptions\BillingException;
use App\Models\Invoice;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Stay;
use App\Models\User;
use App\Services\BillingService;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceTest extends TestCase
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

    /**
     * 2 nights × 5,000 = 10,000 invoice.
     */
    private function book(int $from = 0, int $to = 2, ?Room $room = null): Stay
    {
        return $this->bookings->create([
            'guest_id' => User::factory()->create(['name' => fake()->unique()->name()])->id,
            'arrival_date' => today()->addDays($from)->toDateString(),
            'departure_date' => today()->addDays($to)->toDateString(),
            'meal_plan' => 'BB',
            'status' => 'booked',
            'rooms' => [['room_id' => ($room ?? $this->room)->id, 'adults' => 2, 'children' => 0]],
        ], $this->clerk);
    }

    private function invoice(Stay $stay): Invoice
    {
        return $stay->invoices()->sole();
    }

    // ---- Extra charges -----------------------------------------------------

    public function test_front_desk_adds_an_extra_charge_and_taxes_follow(): void
    {
        $stay = $this->book();

        $this->actingAs($this->clerk)
            ->post(route('stays.charges.store', $stay), ['description' => 'Laundry', 'quantity' => 2, 'unit_price' => 590])
            ->assertRedirect()->assertSessionHas('success');

        $invoice = $this->invoice($stay)->fresh();
        $this->assertSame('11180.00', $invoice->total);
        $this->assertSame('1515.93', $invoice->vat_amount); // 11,180 / 1.18 × 16%
        $this->assertSame(1, $invoice->items()->where('source_type', 'other')->count());

        // Booking changes rebuild room lines but keep extras
        $this->billing->syncStayInvoice($stay->fresh());
        $this->assertSame('11180.00', $invoice->fresh()->total);
    }

    public function test_only_managers_and_finance_remove_charges_and_room_lines_stay(): void
    {
        $stay = $this->book();
        $extra = $this->billing->addCharge($this->invoice($stay), 'Minibar', 1, 800, $this->clerk);
        $roomLine = $this->invoice($stay)->items()->where('source_type', 'room')->first();

        $this->actingAs($this->clerk)
            ->delete(route('stays.charges.destroy', [$stay, $extra]))->assertForbidden();

        $this->actingAs($this->userWithRole('finance'));
        $this->delete(route('stays.charges.destroy', [$stay, $roomLine]))->assertSessionHas('error');
        $this->delete(route('stays.charges.destroy', [$stay, $extra]))->assertSessionHas('success');

        $this->assertModelExists($roomLine);
        $this->assertSame('10000.00', $this->invoice($stay)->fresh()->total);
    }

    public function test_removing_a_charge_cannot_leave_the_guest_overpaid(): void
    {
        $stay = $this->book();
        $invoice = $this->invoice($stay);
        $extra = $this->billing->addCharge($invoice, 'Airport transfer', 1, 3000, $this->clerk);
        $this->billing->recordPayment($invoice, ['amount' => 13000, 'method' => 'cash'], $this->clerk);

        $this->expectException(BillingException::class);
        $this->billing->removeCharge($extra, $this->clerk);
    }

    // ---- Discounts -------------------------------------------------------

    public function test_manager_discount_reduces_total_and_needs_a_reason(): void
    {
        $stay = $this->book();
        $this->actingAs($this->userWithRole('manager'));

        $this->post(route('stays.discount', $stay), ['discount_amount' => 1000])
            ->assertSessionHasErrors('reason');

        $this->post(route('stays.discount', $stay), ['discount_amount' => 1000, 'reason' => 'Returning guest'])
            ->assertSessionHas('success');

        $invoice = $this->invoice($stay)->fresh();
        $this->assertSame('9000.00', $invoice->total);
        $this->assertSame('1000.00', $invoice->discount_amount);
        $this->assertStringContainsString('Returning guest', $invoice->notes);

        // Receptionists can't discount
        $this->actingAs($this->clerk)
            ->post(route('stays.discount', $stay), ['discount_amount' => 0])->assertForbidden();
    }

    public function test_discount_cannot_exceed_charges_or_undercut_payments(): void
    {
        $stay = $this->book();
        $invoice = $this->invoice($stay);
        $manager = $this->userWithRole('manager');

        try {
            $this->billing->setDiscount($invoice, 20000, 'too much', $manager);
            $this->fail('Discount above the charges was accepted');
        } catch (BillingException) {
        }

        $this->billing->recordPayment($invoice, ['amount' => 9500, 'method' => 'cash'], $this->clerk);

        try {
            $this->billing->setDiscount($invoice, 1000, 'would overpay', $manager);
            $this->fail('Discount leaving the guest overpaid was accepted');
        } catch (BillingException) {
        }

        // Rolled back: still the original total
        $this->assertSame('10000.00', $invoice->fresh()->total);
        $this->assertSame('0.00', $invoice->fresh()->discount_amount);
    }

    // ---- Registers -------------------------------------------------------

    public function test_invoice_register_filters_and_totals(): void
    {
        $paid = $this->book();
        $this->billing->recordPayment($this->invoice($paid), ['amount' => 10000, 'method' => 'cash'], $this->clerk);

        $overdue = $this->book(0, 2, Room::factory()->create(['room_type_id' => $this->room->room_type_id]));
        $this->invoice($overdue)->update(['due_date' => today()->subDay()]);

        $this->actingAs($this->userWithRole('finance'))
            ->get(route('invoices.index'))
            ->assertOk()
            ->assertViewHas('stats', fn ($s) => $s['billed'] === 20000.0 && $s['collected'] === 10000.0
                && $s['outstanding'] === 10000.0 && $s['overdue'] === 10000.0);

        $this->get(route('invoices.index', ['status' => 'overdue']))
            ->assertSee($this->invoice($overdue)->invoice_number)
            ->assertDontSee($this->invoice($paid)->invoice_number);

        $this->get(route('invoices.index', ['search' => $paid->guest->name]))
            ->assertSee($this->invoice($paid)->invoice_number)
            ->assertDontSee($this->invoice($overdue)->invoice_number);

        $this->get(route('invoices.show', $this->invoice($overdue)))
            ->assertOk()->assertSee('Overdue')->assertSee('Record Payment');
    }

    public function test_cash_up_totals_by_method_and_skips_voided(): void
    {
        $invoice = $this->invoice($this->book());
        $this->billing->recordPayment($invoice, ['amount' => 3000, 'method' => 'cash'], $this->clerk);
        $this->billing->recordPayment($invoice, ['amount' => 4000, 'method' => 'mobile_money', 'payment_reference' => 'QJK3XYZ12A'], $this->clerk);
        $mistake = $this->billing->recordPayment($invoice, ['amount' => 1000, 'method' => 'cash'], $this->clerk);
        $this->billing->voidPayment($mistake, $this->userWithRole('manager'), 'Wrong guest');

        $this->actingAs($this->userWithRole('finance'))
            ->get(route('payments.index'))
            ->assertOk()
            ->assertViewHas('stats', fn ($s) => $s['total'] === 7000.0 && $s['count'] === 2 && $s['voided'] === 1000.0)
            ->assertViewHas('byMethod', fn ($m) => (float) $m['cash']->total === 3000.0 && (float) $m['mobile_money']->total === 4000.0)
            ->assertSee('QJK3XYZ12A');

        // Yesterday's register is empty
        $this->get(route('payments.index', ['from' => today()->subDay()->toDateString(), 'to' => today()->subDay()->toDateString()]))
            ->assertViewHas('stats', fn ($s) => $s['count'] === 0);
    }

    public function test_finance_overview_and_access(): void
    {
        $this->billing->recordPayment($this->invoice($this->book()), ['amount' => 2500, 'method' => 'cash'], $this->clerk);

        foreach (['finance', 'manager', 'admin'] as $role) {
            $this->actingAs($this->userWithRole($role))
                ->get(route('finance.index'))
                ->assertOk()
                ->assertViewHas('stats', fn ($s) => $s['collected_today'] === 2500.0 && $s['outstanding'] === 7500.0);
        }

        $this->actingAs($this->clerk);
        $this->get(route('finance.index'))->assertForbidden();
        $this->get(route('invoices.index'))->assertForbidden();
        $this->get(route('payments.index'))->assertForbidden();
    }
}
