<?php

namespace Tests\Feature;

use App\Enums\RoomStatus;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomAllocation;
use App\Models\RoomType;
use App\Models\Stay;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::firstOrCreate(['name' => $role])->id);

        return $user;
    }

    /**
     * Book $room for a stay with the given status and dates.
     */
    private function book(Room $room, string $from, string $to, string $status = 'booked'): Stay
    {
        $guest = User::factory()->create();

        $stay = Stay::create([
            'guest_id' => $guest->id,
            'status' => $status,
            'arrival_date' => $from,
            'departure_date' => $to,
            'adults' => 2,
            'children' => 0,
            'created_by' => $guest->id,
        ]);

        RoomAllocation::create([
            'stay_id' => $stay->id,
            'room_id' => $room->id,
            'from_date' => $from,
            'to_date' => $to,
            'rate_applied' => 5000,
            'adults' => 2,
            'children' => 0,
        ]);

        return $stay;
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'room_number' => '101',
            'room_type_id' => RoomType::factory()->create()->id,
            'floor' => 1,
            'wing' => 'East',
            'status' => 'available',
            'adult_price' => 6000,
            'child_price' => 3000,
            'features' => ['TV', 'Balcony', 'TV'],
        ], $overrides);
    }

    // ---- Access ---------------------------------------------------------

    public function test_front_office_and_housekeeping_can_view_rooms(): void
    {
        $room = Room::factory()->create();

        foreach (['receptionist', 'housekeeping', 'manager', 'admin'] as $role) {
            $this->actingAs($this->userWithRole($role))
                ->get(route('rooms.index'))->assertOk()->assertSee($room->room_number);
            $this->get(route('rooms.show', $room))->assertOk();
        }
    }

    public function test_guests_and_unrelated_staff_cannot_view_rooms(): void
    {
        foreach (['guest', 'kitchen', 'finance'] as $role) {
            $this->actingAs($this->userWithRole($role))->get(route('rooms.index'))->assertForbidden();
        }
    }

    public function test_only_managers_can_create_update_or_delete(): void
    {
        $room = Room::factory()->create();
        $receptionist = $this->userWithRole('receptionist');

        $this->actingAs($receptionist)->postJson(route('rooms.store'), $this->validPayload())->assertForbidden();
        $this->actingAs($receptionist)->putJson(route('rooms.update', $room), $this->validPayload())->assertForbidden();
        $this->actingAs($receptionist)->deleteJson(route('rooms.destroy', $room))->assertForbidden();
    }

    // ---- Create / update / delete ----------------------------------------

    public function test_manager_can_create_a_room(): void
    {
        $this->actingAs($this->userWithRole('manager'))
            ->postJson(route('rooms.store'), $this->validPayload())
            ->assertCreated()
            ->assertJson(['success' => true]);

        $room = Room::firstWhere('room_number', '101');
        $this->assertSame(RoomStatus::Available, $room->status);
        $this->assertSame(['TV', 'Balcony'], $room->features); // duplicates removed
        $this->assertEquals(6000, $room->adult_price);           // prices are saved (were silently dropped before)
    }

    public function test_room_numbers_must_be_unique_and_status_valid(): void
    {
        Room::factory()->create(['room_number' => '101']);

        $this->actingAs($this->userWithRole('manager'))
            ->postJson(route('rooms.store'), $this->validPayload(['status' => 'dirty']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['room_number', 'status']);
    }

    public function test_manager_can_update_a_room_keeping_its_own_number(): void
    {
        $room = Room::factory()->create(['room_number' => '101']);

        $this->actingAs($this->userWithRole('manager'))
            ->putJson(route('rooms.update', $room), $this->validPayload(['floor' => 3]))
            ->assertOk();

        $this->assertSame(3, $room->fresh()->floor);
    }

    public function test_room_with_booking_history_cannot_be_deleted(): void
    {
        $room = Room::factory()->create();
        $this->book($room, '2026-01-01', '2026-01-03', 'checked_out');
        $manager = $this->userWithRole('manager');

        $this->actingAs($manager)->deleteJson(route('rooms.destroy', $room))->assertUnprocessable();
        $this->assertModelExists($room);

        $unused = Room::factory()->create();
        $this->actingAs($manager)->deleteJson(route('rooms.destroy', $unused))->assertOk();
        $this->assertModelMissing($unused);
    }

    // ---- Status ----------------------------------------------------------

    public function test_housekeeping_can_change_status(): void
    {
        $room = Room::factory()->status(RoomStatus::Cleaning)->create();

        $this->actingAs($this->userWithRole('housekeeping'))
            ->patchJson(route('rooms.status', $room), ['status' => 'available'])
            ->assertOk();

        $this->assertSame(RoomStatus::Available, $room->fresh()->status);
    }

    public function test_room_with_checked_in_guest_cannot_be_marked_available(): void
    {
        $room = Room::factory()->status(RoomStatus::Occupied)->create();
        $this->book($room, today()->subDay()->toDateString(), today()->addDays(2)->toDateString(), 'checked_in');

        $this->actingAs($this->userWithRole('receptionist'))
            ->patchJson(route('rooms.status', $room), ['status' => 'available'])
            ->assertUnprocessable();

        $this->assertSame(RoomStatus::Occupied, $room->fresh()->status);
    }

    // ---- Availability ----------------------------------------------------

    public function test_overlapping_booking_blocks_the_room(): void
    {
        $room = Room::factory()->create();
        $this->book($room, '2026-11-10', '2026-11-15');

        $this->assertFalse($room->isAvailableForDates('2026-11-12', '2026-11-13')); // inside
        $this->assertFalse($room->isAvailableForDates('2026-11-08', '2026-11-11')); // overlaps start
        $this->assertFalse($room->isAvailableForDates('2026-11-14', '2026-11-20')); // overlaps end
        $this->assertFalse($room->isAvailableForDates('2026-11-01', '2026-11-30')); // surrounds
    }

    public function test_back_to_back_bookings_are_allowed(): void
    {
        $room = Room::factory()->create();
        $this->book($room, '2026-11-10', '2026-11-15');

        // Guest leaves on the 15th, next guest arrives on the 15th
        $this->assertTrue($room->isAvailableForDates('2026-11-15', '2026-11-18'));
        // Next guest leaves on the 10th, before this one arrives
        $this->assertTrue($room->isAvailableForDates('2026-11-07', '2026-11-10'));
    }

    public function test_cancelled_and_checked_out_stays_do_not_block(): void
    {
        $room = Room::factory()->create();
        $this->book($room, '2026-11-10', '2026-11-15', 'cancelled');
        $this->book($room, '2026-11-10', '2026-11-15', 'checked_out');

        $this->assertTrue($room->isAvailableForDates('2026-11-11', '2026-11-12'));
    }

    public function test_soft_deleted_stays_do_not_block(): void
    {
        $room = Room::factory()->create();
        $this->book($room, '2026-11-10', '2026-11-15')->delete();

        $this->assertTrue($room->isAvailableForDates('2026-11-11', '2026-11-12'));
    }

    public function test_occupied_today_can_still_be_booked_next_month(): void
    {
        $room = Room::factory()->status(RoomStatus::Occupied)->create();

        $this->assertTrue($room->isAvailableForDates(today()->addMonth(), today()->addMonth()->addDays(2)));
    }

    public function test_rooms_out_of_service_are_never_available(): void
    {
        $room = Room::factory()->status(RoomStatus::Maintenance)->create();

        $this->assertFalse($room->isAvailableForDates(today()->addMonth(), today()->addMonth()->addDays(2)));
    }

    public function test_availability_endpoint_reports_each_room(): void
    {
        $free = Room::factory()->create();
        $booked = Room::factory()->create();
        $this->book($booked, '2026-11-10', '2026-11-15');

        $response = $this->actingAs($this->userWithRole('receptionist'))
            ->getJson(route('rooms.availability', ['arrival_date' => '2026-11-12', 'departure_date' => '2026-11-14']))
            ->assertOk()
            ->assertJsonPath('available_count', 1)
            ->assertJsonPath('nights', 2);

        $rooms = collect($response->json('rooms'))->keyBy('id');
        $this->assertTrue($rooms[$free->id]['is_available']);
        $this->assertFalse($rooms[$booked->id]['is_available']);
        $this->assertSame('2026-11-15', $rooms[$booked->id]['next_available_date']);
    }

    // ---- Pages -----------------------------------------------------------

    public function test_index_stats_count_every_room_not_just_the_page(): void
    {
        Room::factory()->count(25)->create();
        Room::factory()->count(3)->status(RoomStatus::Cleaning)->create();

        $this->actingAs($this->userWithRole('manager'))
            ->get(route('rooms.index'))
            ->assertOk()
            ->assertViewHas('stats', fn ($stats) => $stats['total'] === 28 && $stats['cleaning'] === 3);
    }

    public function test_show_page_renders_current_guest_and_history(): void
    {
        $room = Room::factory()->status(RoomStatus::Occupied)->create();
        $stay = $this->book($room, today()->subDay()->toDateString(), today()->addDays(2)->toDateString(), 'checked_in');

        $this->actingAs($this->userWithRole('manager'))
            ->get(route('rooms.show', $room))
            ->assertOk()
            ->assertSee('Current Guest')
            ->assertSee($stay->guest->name);
    }

    public function test_room_type_page_renders_with_its_rooms(): void
    {
        $room = Room::factory()->create();

        $this->actingAs($this->userWithRole('manager'))
            ->get(route('room-types.show', $room->roomType))
            ->assertOk()
            ->assertSee($room->room_number);
    }
}
