<?php

namespace Tests\Feature;

use App\Models\BookingRequest;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingRequestTest extends TestCase
{
    use RefreshDatabase;

    private Room $room;

    protected function setUp(): void
    {
        parent::setUp();

        $this->room = Room::factory()->create(['room_type_id' => RoomType::factory()->create(['base_rate' => 5000, 'capacity' => 2])->id]);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::firstOrCreate(['name' => $role])->id);

        return $user;
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'arrival_date' => today()->addDay()->toDateString(),
            'departure_date' => today()->addDays(3)->toDateString(),
            'adults' => 2,
            'children' => 0,
            'rooms' => 1,
            'room_type_id' => $this->room->room_type_id,
            'meal_plan' => 'BB',
            'name' => 'Wanjiru Kamau',
            'phone' => '0712 345 678',
            'email' => 'wanjiru@example.com',
        ], $overrides);
    }

    public function test_public_pages_render(): void
    {
        foreach (['home', 'about', 'contact', 'book'] as $route) {
            $this->get(route($route))->assertOk();
        }

        $this->get(route('book', [
            'arrival' => today()->addDay()->toDateString(),
            'departure' => today()->addDays(2)->toDateString(),
        ]))->assertOk()->assertSee($this->room->roomType->name);
    }

    public function test_visitor_can_send_a_request(): void
    {
        $this->post(route('book.submit'), $this->payload())->assertRedirect(route('book.thanks'));

        $request = BookingRequest::sole();
        $this->assertSame('new', $request->status);
        $this->assertSame(2, $request->nights);

        $this->get(route('book.thanks'))->assertOk()->assertSee($request->reference);
    }

    public function test_spam_trap_and_past_dates_are_rejected(): void
    {
        $this->post(route('book.submit'), $this->payload(['website' => 'http://spam']))->assertSessionHasErrors('website');
        $this->post(route('book.submit'), $this->payload(['arrival_date' => today()->subDay()->toDateString()]))->assertSessionHasErrors('arrival_date');

        $this->assertSame(0, BookingRequest::count());
    }

    public function test_front_desk_sees_requests_but_guests_cannot(): void
    {
        BookingRequest::create($this->payload());

        $this->actingAs($this->userWithRole('receptionist'))->get(route('booking-requests.index'))->assertOk()->assertSee('Wanjiru Kamau');
        $this->actingAs($this->userWithRole('guest'))->get(route('booking-requests.index'))->assertForbidden();
    }

    public function test_converting_registers_the_guest_and_marks_the_request_booked(): void
    {
        $receptionist = $this->userWithRole('receptionist');
        $request = BookingRequest::create($this->payload());

        $response = $this->actingAs($receptionist)->post(route('booking-requests.convert', $request));
        $guest = User::where('phone', '0712 345 678')->sole();
        $response->assertRedirect(route('stays.create', ['guest_id' => $guest->id, 'request' => $request->id]));

        $this->get(route('stays.create', ['guest_id' => $guest->id, 'request' => $request->id]))
            ->assertOk()->assertSee($request->reference);

        $this->post(route('stays.store'), [
            'guest_id' => $guest->id,
            'booking_request_id' => $request->id,
            'arrival_date' => $request->arrival_date->toDateString(),
            'departure_date' => $request->departure_date->toDateString(),
            'meal_plan' => 'BB',
            'status' => 'booked',
            'rooms' => [['room_id' => $this->room->id, 'adults' => 2, 'children' => 0]],
        ])->assertSessionHasNoErrors();

        $request->refresh();
        $this->assertSame('converted', $request->status);
        $this->assertNotNull($request->stay_id);
        $this->assertSame($receptionist->id, $request->handled_by);
    }

    public function test_converting_reuses_a_guest_with_the_same_phone(): void
    {
        $existing = $this->userWithRole('guest');
        $existing->update(['phone' => '+254712345678']);
        $request = BookingRequest::create($this->payload());

        $this->actingAs($this->userWithRole('receptionist'))->post(route('booking-requests.convert', $request))
            ->assertRedirect(route('stays.create', ['guest_id' => $existing->id, 'request' => $request->id]));
    }

    public function test_decline_needs_a_reason_and_only_happens_once(): void
    {
        $receptionist = $this->userWithRole('receptionist');
        $request = BookingRequest::create($this->payload());

        $this->actingAs($receptionist)->post(route('booking-requests.decline', $request))->assertSessionHasErrors('reason');
        $this->post(route('booking-requests.decline', $request), ['reason' => 'Fully booked'])->assertSessionHas('success');
        $this->assertSame('declined', $request->fresh()->status);

        $this->post(route('booking-requests.convert', $request))->assertSessionHas('error');
    }
}
