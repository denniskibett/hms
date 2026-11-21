<?php

namespace Tests\Feature;

use App\Enums\FacilityBookingStatus;
use App\Models\Facility;
use App\Models\FacilityBooking;
use App\Models\FacilityPackage;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacilityTest extends TestCase
{
    use RefreshDatabase;

    private User $clerk;
    private Facility $hall;
    private FacilityPackage $fullDay;
    private FacilityPackage $projector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clerk = $this->userWithRole('receptionist');
        $this->hall = Facility::create(['name' => 'Main Hall', 'code' => 'main_hall', 'capacity' => 100, 'base_rate' => 10000, 'status' => 'available']);
        $this->fullDay = FacilityPackage::create(['name' => 'Full Day', 'price' => 2500, 'charge_basis' => 'per_person', 'is_active' => true]);
        $this->projector = FacilityPackage::create(['name' => 'Projector', 'price' => 3000, 'charge_basis' => 'per_unit', 'is_active' => true]);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::firstOrCreate(['name' => $role])->id);

        return $user;
    }

    /**
     * Payload for a 2-day, 40-pax event in the hall: venue hire, full day package, one projector.
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'facility_id' => $this->hall->id,
            'title' => 'Staff Training',
            'client_name' => 'Acme Ltd',
            'contact_phone' => '0712345678',
            'start_date' => today()->addDays(5)->toDateString(),
            'end_date' => today()->addDays(6)->toDateString(),
            'pax' => 40,
            'status' => 'confirmed',
            'items' => [
                ['description' => 'Venue hire', 'charge_basis' => 'venue', 'quantity' => 1, 'days' => 2, 'unit_price' => 10000],
                ['facility_package_id' => $this->fullDay->id, 'description' => 'Full Day', 'charge_basis' => 'per_person', 'quantity' => 40, 'days' => 2, 'unit_price' => 2500],
                ['facility_package_id' => $this->projector->id, 'description' => 'Projector', 'charge_basis' => 'per_unit', 'quantity' => 1, 'days' => 2, 'unit_price' => 3000],
            ],
        ], $overrides);
    }

    private function book(array $overrides = []): FacilityBooking
    {
        $this->actingAs($this->clerk)->postJson(route('events.store'), $this->payload($overrides))->assertCreated();

        return FacilityBooking::latest('id')->first();
    }

    public function test_booking_an_event_creates_its_invoice(): void
    {
        $event = $this->book();
        $invoice = $event->invoices()->sole();

        // 2 × 10,000 + 40 × 2 × 2,500 + 1 × 2 × 3,000
        $this->assertSame('226000.00', $invoice->total);
        $this->assertNull($invoice->stay_id);
        $this->assertSame(3, $invoice->items()->where('source_type', 'facility')->count());
        $this->assertSame(FacilityBookingStatus::Confirmed, $event->status);
        $this->assertSame($this->clerk->id, $event->created_by);
    }

    public function test_overlapping_events_in_the_same_venue_are_refused(): void
    {
        $this->book();

        $this->actingAs($this->clerk)
            ->postJson(route('events.store'), $this->payload(['start_date' => today()->addDays(6)->toDateString(), 'end_date' => today()->addDays(7)->toDateString(), 'items' => [$this->payload()['items'][1]]]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('facility_id');
    }

    public function test_same_day_events_with_separate_times_can_share_a_venue(): void
    {
        $day = today()->addDays(3)->toDateString();
        $item = ['description' => 'Venue hire', 'charge_basis' => 'venue', 'quantity' => 1, 'days' => 1, 'unit_price' => 10000];

        $this->book(['start_date' => $day, 'end_date' => $day, 'start_time' => '08:00', 'end_time' => '12:00', 'items' => [$item]]);

        $this->actingAs($this->clerk)
            ->postJson(route('events.store'), $this->payload(['start_date' => $day, 'end_date' => $day, 'start_time' => '13:00', 'end_time' => '17:00', 'items' => [$item]]))
            ->assertCreated();

        $this->actingAs($this->clerk)
            ->postJson(route('events.store'), $this->payload(['start_date' => $day, 'end_date' => $day, 'start_time' => '11:00', 'end_time' => '14:00', 'items' => [$item]]))
            ->assertJsonValidationErrors('facility_id');
    }

    public function test_pax_over_capacity_and_lines_longer_than_the_event_are_refused(): void
    {
        $payload = $this->payload(['pax' => 150]);
        $payload['items'][1]['days'] = 3;

        $this->actingAs($this->clerk)
            ->postJson(route('events.store'), $payload)
            ->assertJsonValidationErrors(['pax', 'items.1.days']);
    }

    public function test_editing_resyncs_event_lines_and_keeps_extra_charges(): void
    {
        $event = $this->book();
        $this->actingAs($this->clerk)->post(route('events.charges.store', $event), ['description' => 'Extra lunch', 'quantity' => 5, 'unit_price' => 1000]);

        $payload = $this->payload(['pax' => 50]);
        $payload['items'][1]['quantity'] = 50;
        $this->actingAs($this->clerk)->putJson(route('events.update', $event), $payload)->assertOk();

        // 20,000 + 50 × 2 × 2,500 + 6,000 + extra 5,000
        $this->assertSame('281000.00', $event->invoices()->sole()->total);
        $this->assertSame(50, $event->fresh()->pax);
    }

    public function test_cancelling_releases_the_venue_and_cancels_the_unpaid_invoice(): void
    {
        $event = $this->book();

        $this->actingAs($this->clerk)
            ->post(route('events.status', $event), ['status' => 'cancelled', 'reason' => 'Client postponed'])
            ->assertSessionHas('success');

        $this->assertSame(FacilityBookingStatus::Cancelled, $event->fresh()->status);
        $this->assertSame('cancelled', $event->invoices()->sole()->status);
        $this->assertNull($this->hall->conflictingBooking($event->start_date, $event->end_date));

        // Cancelled is final
        $this->actingAs($this->clerk)
            ->post(route('events.status', $event), ['status' => 'confirmed'])
            ->assertSessionHas('error');
    }

    public function test_event_payment_receipt_and_finance_pages(): void
    {
        $event = $this->book();
        $finance = $this->userWithRole('finance');

        $this->actingAs($this->clerk)
            ->post(route('events.payments.store', $event), ['amount' => 100000, 'method' => 'mobile_money', 'payment_reference' => 'QJK3XYZ12A'])
            ->assertSessionHas('receipt_url');

        $invoice = $event->invoices()->sole();
        $this->assertSame('partial', $invoice->status);
        $payment = $invoice->payments()->sole();

        $this->actingAs($this->clerk)->get(route('events.receipt', [$event, $payment]))->assertOk()->assertSee('Acme Ltd');
        $this->actingAs($this->clerk)->get(route('events.invoice', $event))->assertOk()->assertSee('Staff Training');
        $this->actingAs($finance)->get(route('invoices.index'))->assertOk()->assertSee('Acme Ltd');
        $this->actingAs($finance)->get(route('invoices.show', $invoice))->assertOk()->assertSee('Acme Ltd');
        $this->actingAs($finance)->get(route('payments.index'))->assertOk()->assertSee('QJK3XYZ12A');
        $this->actingAs($finance)->get(route('finance.index'))->assertOk();
    }

    public function test_pages_render(): void
    {
        $event = $this->book();

        $this->actingAs($this->clerk)->get(route('facilities.index'))->assertOk()->assertSee('Main Hall')->assertSee('Full Day');
        $this->actingAs($this->clerk)->get(route('facilities.show', $this->hall))->assertOk()->assertSee('Staff Training');
        $this->actingAs($this->clerk)->get(route('events.index'))->assertOk()->assertSee('Acme Ltd');
        $this->actingAs($this->clerk)->get(route('events.show', $event))->assertOk()->assertSee('Staff Training');
    }

    public function test_only_managers_set_up_venues_and_packages(): void
    {
        $venue = ['name' => 'Boardroom', 'capacity' => 12, 'base_rate' => 5000, 'status' => 'available'];

        $this->actingAs($this->clerk)->postJson(route('facilities.store'), $venue)->assertForbidden();
        $this->actingAs($this->userWithRole('housekeeping'))->get(route('events.index'))->assertForbidden();

        $manager = $this->userWithRole('manager');
        $this->actingAs($manager)->postJson(route('facilities.store'), $venue)->assertCreated();
        $this->assertSame('boardroom', Facility::where('name', 'Boardroom')->value('code'));

        // A package sold on an event can't be deleted, and a venue with events can't be either
        $this->book();
        $this->actingAs($manager)->deleteJson(route('facility-packages.destroy', $this->fullDay))->assertUnprocessable();
        $this->actingAs($manager)->deleteJson(route('facilities.destroy', $this->hall))->assertUnprocessable();
    }
}
