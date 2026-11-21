<?php

namespace Tests\Feature;

use App\Enums\StayStatus;
use App\Exceptions\BookingException;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\NightAudit;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Stay;
use App\Models\User;
use App\Services\BillingService;
use App\Services\BookingService;
use App\Services\NightAudit as NightAuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyAndNightAuditTest extends TestCase
{
    use RefreshDatabase;

    private BookingService $bookings;
    private User $clerk;
    private Room $room;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bookings = app(BookingService::class);
        $this->clerk = $this->userWithRole('receptionist');
        $this->room = Room::factory()->create(['room_type_id' => RoomType::factory()->create(['base_rate' => 5000, 'capacity' => 2])->id]);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::firstOrCreate(['name' => $role])->id);

        return $user;
    }

    private function company(array $attrs = []): Company
    {
        return Company::create($attrs + ['name' => 'Acme Ltd', 'payment_terms_days' => 30, 'rate_discount_percent' => 10, 'credit_limit' => 0]);
    }

    private function book(array $extra = [], int $from = 0, int $to = 2, ?Room $room = null): Stay
    {
        return $this->bookings->create($extra + [
            'guest_id' => User::factory()->create()->id,
            'arrival_date' => today()->addDays($from)->toDateString(),
            'departure_date' => today()->addDays($to)->toDateString(),
            'meal_plan' => 'BB',
            'status' => 'booked',
            'rooms' => [['room_id' => ($room ?? $this->room)->id, 'adults' => 2, 'children' => 0]],
        ], $this->clerk);
    }

    // ---- Company accounts ------------------------------------------------

    public function test_company_booking_gets_the_negotiated_rate_and_its_payment_terms(): void
    {
        $company = $this->company();
        $stay = $this->book(['company_id' => $company->id]);
        $invoice = $stay->invoices()->sole();

        $this->assertEquals(4500, (float) $stay->roomAllocations->first()->rate_applied);   // 10% off 5,000
        $this->assertSame($company->id, $invoice->company_id);
        $this->assertTrue($invoice->due_date->isSameDay(today()->addDays(2 + 30)));
        $this->assertSame('Acme Ltd', $invoice->billed_to);
    }

    public function test_company_billed_guest_checks_out_without_paying(): void
    {
        $stay = $this->book(['company_id' => $this->company()->id]);
        $this->bookings->checkIn($stay, $this->clerk);

        $out = $this->bookings->checkOut($stay->fresh(), $this->clerk);   // no reason needed

        $this->assertSame(StayStatus::CheckedOut, $out->state);
        $this->assertEquals(4500, $out->company->balance());   // left on the first day: one night at the company rate
    }

    public function test_credit_limit_blocks_a_booking_that_would_go_over(): void
    {
        $company = $this->company(['credit_limit' => 10000, 'rate_discount_percent' => 0]);
        $this->book(['company_id' => $company->id]);   // 10,000: at the limit

        $other = Room::factory()->create(['room_type_id' => $this->room->room_type_id]);
        $this->expectException(BookingException::class);
        $this->book(['company_id' => $company->id], 0, 1, $other);
    }

    public function test_company_payment_clears_the_oldest_invoices_first(): void
    {
        $company = $this->company(['rate_discount_percent' => 0]);
        $first = $this->book(['company_id' => $company->id], 0, 1);
        $second = $this->book(['company_id' => $company->id], 3, 4);
        $manager = $this->userWithRole('finance');

        $this->actingAs($manager)->post(route('companies.pay', $company), [
            'amount' => 7000, 'method' => 'bank_transfer', 'payment_reference' => 'TRX-881',
        ])->assertSessionHas('success');

        $this->assertSame('paid', $first->invoices()->sole()->status);          // 5,000
        $this->assertEquals(2000, (float) $second->invoices()->sole()->paid_amount);
        $this->assertEquals(3000, $company->fresh()->balance());
    }

    public function test_receptionist_can_view_but_not_change_companies(): void
    {
        $company = $this->company();
        $this->actingAs($this->clerk)->get(route('companies.index'))->assertOk()->assertSee('Acme Ltd');
        $this->actingAs($this->clerk)->get(route('companies.show', $company))->assertOk();
        $this->actingAs($this->clerk)->get(route('companies.statement', $company))->assertOk();
        $this->actingAs($this->clerk)->post(route('companies.store'), ['name' => 'X'])->assertForbidden();
    }

    public function test_finance_adds_a_company(): void
    {
        $this->actingAs($this->userWithRole('finance'))
            ->post(route('companies.store'), ['name' => 'Kenya Power', 'kra_pin' => 'p051abc', 'payment_terms_days' => 45, 'credit_limit' => '', 'rate_discount_percent' => ''])
            ->assertRedirect();

        $this->assertDatabaseHas('companies', ['name' => 'Kenya Power', 'kra_pin' => 'P051ABC', 'payment_terms_days' => 45, 'credit_limit' => 0]);
    }

    public function test_future_bookings_are_not_counted_as_debts(): void
    {
        $this->book([], 5, 7);                     // not arrived yet
        $inHouse = $this->book([], 0, 2, Room::factory()->create(['room_type_id' => $this->room->room_type_id]));
        $this->bookings->checkIn($inHouse, $this->clerk);

        $this->assertSame(1, Invoice::receivable()->count());
        $this->assertSame(2, Invoice::unpaid()->count());
    }

    // ---- Night audit -----------------------------------------------------

    public function test_night_audit_marks_no_shows_lists_overstays_and_keeps_the_figures(): void
    {
        $noShow = $this->book([], 0, 2);
        $inHouse = $this->book([], 0, 1, $room2 = Room::factory()->create(['room_type_id' => $this->room->room_type_id]));
        $this->bookings->checkIn($inHouse, $this->clerk);
        app(BillingService::class)->recordPayment($inHouse->invoices()->sole(), ['amount' => 1000, 'method' => 'cash'], $this->clerk);

        $audit = app(NightAuditService::class)->run(today(), $this->userWithRole('manager'));

        $this->assertSame(StayStatus::NoShow, $noShow->fresh()->state);
        $this->assertSame('cancelled', $noShow->invoices()->sole()->status);   // nothing paid → cancelled
        $this->assertSame(1, $audit->figure('rooms_sold'));
        $this->assertEquals(5000, $audit->figure('room_revenue'));
        $this->assertEquals(1000, $audit->figure('payments_total'));
        $this->assertCount(1, $audit->figure('no_shows'));

        // Tomorrow the in-house guest is past departure: an overstay
        $this->travel(1)->days();
        $next = app(NightAuditService::class)->run(today());
        $this->assertCount(1, $next->figure('overstays'));

        // No-show rooms are free again
        $this->assertTrue($this->room->fresh()->isAvailableForDates(today(), today()->addDay()));
    }

    public function test_first_night_audit_leaves_older_forgotten_bookings_alone(): void
    {
        $old = $this->book([], 0, 1);
        $this->travel(5)->days();

        app(NightAuditService::class)->run(today());
        $this->assertSame(StayStatus::Booked, $old->fresh()->state);   // arrived 5 days ago: not tonight's job

        // Once days are being closed, a missed day is caught up
        $later = $this->book([], 1, 2, Room::factory()->create(['room_type_id' => $this->room->room_type_id]));
        $this->travel(3)->days();
        app(NightAuditService::class)->run(today());
        $this->assertSame(StayStatus::NoShow, $later->fresh()->state);
    }

    public function test_scheduled_run_does_not_overwrite_a_day_closed_by_hand(): void
    {
        $manager = $this->userWithRole('manager');
        $this->actingAs($manager)->post(route('night-audits.store'), ['business_date' => today()->subDay()->toDateString(), 'notes' => 'All good'])
            ->assertRedirect();

        $this->artisan('hotel:night-audit')->assertSuccessful();

        $audit = NightAudit::sole();
        $this->assertSame($manager->id, $audit->run_by);
        $this->assertSame('All good', $audit->notes);
    }

    public function test_night_audit_pages_load(): void
    {
        $manager = $this->userWithRole('manager');
        $audit = app(NightAuditService::class)->run(today(), $manager);

        $this->actingAs($manager)->get(route('night-audits.index'))->assertOk();
        $this->actingAs($manager)->get(route('night-audits.show', $audit))->assertOk()->assertSee('Money taken');
        $this->actingAs($this->userWithRole('kitchen'))->get(route('night-audits.index'))->assertForbidden();
    }
}
