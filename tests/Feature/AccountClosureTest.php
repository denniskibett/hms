<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Stay;
use App\Models\User;
use App\Services\BillingService;
use App\Services\BookingService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountClosureTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $role, array $attributes = []): User
    {
        $user = User::factory()->create($attributes + ['password' => bcrypt('secret-pass-1')]);
        $user->roles()->attach(Role::firstOrCreate(['name' => $role])->id);

        return $user;
    }

    /**
     * A guest with a booking, its invoice and a payment.
     */
    private function guestWithHistory(): User
    {
        $guest = $this->userWithRole('guest');
        $clerk = $this->userWithRole('receptionist');
        $room = Room::factory()->create(['room_type_id' => RoomType::factory()->create(['base_rate' => 5000, 'capacity' => 2])->id]);

        $stay = app(BookingService::class)->create([
            'guest_id' => $guest->id,
            'arrival_date' => today()->toDateString(),
            'departure_date' => today()->addDay()->toDateString(),
            'meal_plan' => 'BB',
            'status' => 'booked',
            'rooms' => [['room_id' => $room->id, 'adults' => 1, 'children' => 0]],
        ], $clerk);
        app(BillingService::class)->recordPayment($stay->invoices()->sole(), ['amount' => 2000, 'method' => 'cash'], $clerk);

        return $guest;
    }

    public function test_a_guest_with_bookings_closes_their_login_but_records_stay(): void
    {
        $guest = $this->guestWithHistory();

        $this->actingAs($guest)->delete(route('profile.destroy'), ['password' => 'secret-pass-1'])->assertRedirect('/');

        $this->assertGuest();
        $this->assertSame('inactive', $guest->fresh()->status);
        $this->assertSame(1, Stay::where('guest_id', $guest->id)->count());
        $this->assertSame(1, Invoice::count());
        $this->assertSame(1, Payment::count());

        // Can't sign back in
        $this->post(route('login'), ['email' => $guest->email, 'password' => 'secret-pass-1']);
        $this->assertGuest();
    }

    public function test_a_guest_without_history_is_deleted(): void
    {
        $guest = $this->userWithRole('guest');

        $this->actingAs($guest)->delete(route('profile.destroy'), ['password' => 'secret-pass-1'])->assertRedirect('/');

        $this->assertModelMissing($guest);
    }

    public function test_staff_cannot_delete_their_own_account(): void
    {
        $staff = $this->userWithRole('kitchen');

        $this->actingAs($staff)->delete(route('profile.destroy'), ['password' => 'secret-pass-1'])->assertSessionHas('error');

        $this->assertAuthenticatedAs($staff);
        $this->assertModelExists($staff);
    }

    public function test_the_database_refuses_to_cascade_away_financial_records(): void
    {
        $guest = $this->guestWithHistory();

        $this->expectException(QueryException::class);
        $guest->delete();
    }
}
