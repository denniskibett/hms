<?php

namespace Tests\Feature;

use App\Enums\StayStatus;
use App\Models\Guest;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Stay;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StayPagesTest extends TestCase
{
    use RefreshDatabase;

    private User $guest;
    private Room $room;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guest = $this->userWithRole('guest');
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
            'guest_id' => $this->guest->id,
            'arrival_date' => today()->addDay()->toDateString(),
            'departure_date' => today()->addDays(3)->toDateString(),
            'meal_plan' => 'BB',
            'status' => 'booked',
            'rooms' => [['room_id' => $this->room->id, 'adults' => 2, 'children' => 0]],
        ], $overrides);
    }

    private function makeStay(array $overrides = []): Stay
    {
        return app(BookingService::class)->create($this->payload($overrides), $this->guest);
    }

    public function test_front_desk_and_managers_can_use_bookings_but_guests_cannot(): void
    {
        foreach (['receptionist', 'manager'] as $role) {
            $this->actingAs($this->userWithRole($role))->get(route('stays.index'))->assertOk();
        }

        $this->actingAs($this->guest)->get(route('stays.index'))->assertForbidden();
    }

    public function test_every_tab_renders(): void
    {
        $this->makeStay();
        $receptionist = $this->userWithRole('receptionist');

        foreach (['all', 'arrivals', 'in_house', 'departures', 'upcoming'] as $tab) {
            $this->actingAs($receptionist)->get(route('stays.index', ['tab' => $tab]))->assertOk();
        }
    }

    public function test_booking_form_renders_and_saves(): void
    {
        $receptionist = $this->userWithRole('receptionist');

        $this->actingAs($receptionist)->get(route('stays.create', ['guest_id' => $this->guest->id]))
            ->assertOk()->assertSee($this->guest->name);

        $response = $this->actingAs($receptionist)->post(route('stays.store'), $this->payload());

        $stay = Stay::sole();
        $response->assertRedirect(route('stays.show', $stay))->assertSessionHas('success');
        $this->assertSame(10000.0, $stay->room_charges);
    }

    public function test_booking_a_taken_room_returns_to_the_form_with_a_message(): void
    {
        $this->makeStay();

        $this->actingAs($this->userWithRole('receptionist'))
            ->from(route('stays.create'))
            ->post(route('stays.store'), $this->payload())
            ->assertRedirect(route('stays.create'))
            ->assertSessionHas('error');

        $this->assertSame(1, Stay::count());
    }

    public function test_invalid_booking_shows_validation_errors(): void
    {
        $this->actingAs($this->userWithRole('receptionist'))
            ->post(route('stays.store'), $this->payload(['rooms' => []]))
            ->assertSessionHasErrors('rooms');
    }

    public function test_show_and_edit_pages_render(): void
    {
        $stay = $this->makeStay();
        $receptionist = $this->userWithRole('receptionist');

        $this->actingAs($receptionist)->get(route('stays.show', $stay))
            ->assertOk()->assertSee('Booking #' . $stay->id)->assertSee($this->room->room_number);

        $this->actingAs($receptionist)->get(route('stays.edit', $stay))->assertOk();
    }

    public function test_update_through_the_form(): void
    {
        $stay = $this->makeStay();

        $this->actingAs($this->userWithRole('receptionist'))
            ->put(route('stays.update', $stay), $this->payload(['departure_date' => today()->addDays(5)->toDateString()]))
            ->assertRedirect(route('stays.show', $stay));

        $this->assertSame(4, $stay->fresh()->nights);
    }

    public function test_front_desk_actions(): void
    {
        $stay = $this->makeStay(['arrival_date' => today()->toDateString()]);
        $receptionist = $this->userWithRole('receptionist');

        $this->actingAs($receptionist)->post(route('stays.check-in', $stay))->assertSessionHas('success');
        $this->assertSame(StayStatus::CheckedIn, $stay->fresh()->state);

        // Can't cancel someone who is in house
        $this->actingAs($receptionist)->post(route('stays.cancel', $stay))->assertSessionHas('error');

        $this->actingAs($receptionist)->post(route('stays.check-out', $stay))->assertSessionHas('success');
        $this->assertSame(StayStatus::CheckedOut, $stay->fresh()->state);
    }

    public function test_cancel_then_only_a_manager_can_delete(): void
    {
        $stay = $this->makeStay();

        $this->actingAs($this->userWithRole('receptionist'))
            ->post(route('stays.cancel', $stay), ['reason' => 'Changed plans'])
            ->assertSessionHas('success');

        $this->actingAs($this->userWithRole('receptionist'))->delete(route('stays.destroy', $stay))->assertForbidden();

        $this->actingAs($this->userWithRole('manager'))->delete(route('stays.destroy', $stay))->assertRedirect(route('stays.index'));
        $this->assertSoftDeleted($stay);
    }

    public function test_quote_endpoint_prices_the_booking(): void
    {
        $this->actingAs($this->userWithRole('receptionist'))
            ->postJson(route('stays.quote'), $this->payload())
            ->assertOk()
            ->assertJsonPath('quote.nights', 2)
            ->assertJsonPath('quote.total', 10000)
            ->assertJsonPath('quote.all_available', true);
    }

    public function test_guest_profile_lists_bookings(): void
    {
        $stay = $this->makeStay();
        $profile = Guest::create(['user_id' => $this->guest->id, 'id_type' => 'passport', 'id_number' => 'A123']);

        $this->actingAs($this->userWithRole('receptionist'))
            ->get(route('guests.show', $profile))
            ->assertOk()
            ->assertSee('#' . $stay->id)
            ->assertSee(route('stays.create', ['guest_id' => $this->guest->id]), false);
    }
}
