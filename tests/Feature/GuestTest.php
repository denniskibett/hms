<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomAllocation;
use App\Models\Stay;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::firstOrCreate(['name' => $role])->id);

        return $user;
    }

    private function guest(array $profile = []): Guest
    {
        return $this->userWithRole('guest')->guestProfile()->create(array_merge([
            'id_type' => 'national_id',
            'id_number' => (string) fake()->unique()->numerify('########'),
            'nationality' => 'Kenya',
        ], $profile));
    }

    private function stayFor(Guest $guest, string $status = 'checked_in'): Stay
    {
        $stay = Stay::create([
            'guest_id' => $guest->user_id,
            'status' => $status,
            'arrival_date' => today(),
            'departure_date' => today()->addDays(2),
            'adults' => 1,
            'children' => 0,
            'created_by' => $guest->user_id,
        ]);

        RoomAllocation::create([
            'stay_id' => $stay->id,
            'room_id' => Room::factory()->create()->id,
            'from_date' => today(),
            'to_date' => today()->addDays(2),
            'rate_applied' => 5000,
            'adults' => 1,
            'children' => 0,
        ]);

        return $stay;
    }

    private function payload(array $overrides = []): array
    {
        return array_replace_recursive([
            'name' => 'Wanjiru Kamau',
            'phone' => '0712345678',
            'email' => null,
            'id_type' => 'national_id',
            'id_number' => '29384756',
            'nationality' => 'Kenya',
            'emergency_contact' => ['name' => 'Otieno', 'phone' => '0700111222', 'relationship' => 'Spouse'],
            'preferences' => ['tags' => ['Quiet Room', 'Quiet Room'], 'allergies' => 'Peanuts', 'notes' => null],
        ], $overrides);
    }

    // ---- Access ---------------------------------------------------------

    public function test_front_office_can_view_guests_and_others_cannot(): void
    {
        $guest = $this->guest();

        foreach (['receptionist', 'manager', 'admin'] as $role) {
            $this->actingAs($this->userWithRole($role))
                ->get(route('guests.index'))->assertOk()->assertSee($guest->user->name);
            $this->get(route('guests.show', $guest))->assertOk()->assertSee($guest->id_number);
        }

        $this->actingAs($this->userWithRole('housekeeping'))
            ->get(route('guests.index'))->assertForbidden();
    }

    // ---- Create / update --------------------------------------------------

    public function test_receptionist_registers_a_walk_in_guest_without_email(): void
    {
        $response = $this->actingAs($this->userWithRole('receptionist'))
            ->postJson(route('guests.store'), $this->payload())
            ->assertCreated()
            ->assertJsonPath('guest.name', 'Wanjiru Kamau');

        $user = User::findOrFail($response->json('guest.user_id'));
        $this->assertNull($user->email);
        $this->assertTrue($user->hasRole('guest'));

        $guest = $user->guestProfile;
        $this->assertSame('29384756', $guest->id_number);
        $this->assertSame(['Quiet Room'], $guest->preferences['tags']);
        $this->assertSame('Otieno', $guest->emergency_contact['name']);
    }

    public function test_id_can_be_left_for_check_in_but_not_half_filled(): void
    {
        $this->actingAs($this->userWithRole('receptionist'));

        $this->postJson(route('guests.store'), $this->payload(['id_type' => null, 'id_number' => null]))
            ->assertCreated();

        $this->postJson(route('guests.store'), $this->payload(['id_type' => null, 'phone' => '0799']))
            ->assertUnprocessable()->assertJsonValidationErrors('id_type');
    }

    public function test_the_same_id_cannot_be_registered_twice(): void
    {
        $existing = $this->guest(['id_number' => '29384756']);

        $this->actingAs($this->userWithRole('receptionist'))
            ->postJson(route('guests.store'), $this->payload())
            ->assertUnprocessable()->assertJsonValidationErrors('id_number');

        // Editing the guest who owns the ID is fine
        $this->putJson(route('guests.update', $existing), $this->payload(['name' => 'Renamed']))
            ->assertOk();
        $this->assertSame('Renamed', $existing->user->fresh()->name);
    }

    public function test_update_changes_user_and_profile(): void
    {
        $guest = $this->guest();

        $this->actingAs($this->userWithRole('receptionist'))
            ->putJson(route('guests.update', $guest), $this->payload([
                'email' => 'wanjiru@example.com',
                'emergency_contact' => ['name' => null, 'phone' => null, 'relationship' => null],
            ]))
            ->assertOk();

        $guest->refresh();
        $this->assertSame('wanjiru@example.com', $guest->user->email);
        $this->assertSame('0712345678', $guest->user->phone);
        $this->assertNull($guest->emergency_contact);
    }

    // ---- Delete -----------------------------------------------------------

    public function test_only_managers_delete_and_only_guests_without_bookings(): void
    {
        $guest = $this->guest();
        $booked = $this->guest();
        $this->stayFor($booked);

        $this->actingAs($this->userWithRole('receptionist'))
            ->deleteJson(route('guests.destroy', $guest))->assertForbidden();

        $this->actingAs($this->userWithRole('manager'));

        $this->deleteJson(route('guests.destroy', $booked))->assertUnprocessable();
        $this->assertModelExists($booked);

        $user = $guest->user;
        $this->deleteJson(route('guests.destroy', $guest))->assertOk();
        $this->assertModelMissing($guest);
        $this->assertModelMissing($user);
    }

    // ---- Listing ----------------------------------------------------------

    public function test_in_house_filter_and_history_totals(): void
    {
        $inHouse = $this->guest();
        $this->stayFor($inHouse);
        $other = $this->guest();

        $this->actingAs($this->userWithRole('receptionist'))
            ->get(route('guests.index', ['status' => 'in_house']))
            ->assertOk()
            ->assertSee($inHouse->user->name)
            ->assertDontSee($other->user->name);

        $this->get(route('guests.show', $inHouse))
            ->assertOk()
            ->assertViewHas('totals', fn ($t) => $t['stays'] === 1 && $t['nights'] === 2);
    }

    public function test_booking_form_can_register_a_guest_inline(): void
    {
        $this->actingAs($this->userWithRole('receptionist'))
            ->get(route('stays.create'))
            ->assertOk()
            ->assertSee('open-guest-form')
            ->assertSee(route('guests.store'));
    }
}
