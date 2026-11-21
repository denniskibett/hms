<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Stay;
use App\Models\Task;
use App\Models\TaskType;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string ...$roles): User
    {
        $user = User::factory()->create();
        foreach ($roles as $role) {
            $user->roles()->attach(Role::firstOrCreate(['name' => $role])->id);
        }

        return $user;
    }

    private function bookArrivalToday(User $guest): Stay
    {
        $room = Room::factory()->create(['room_type_id' => RoomType::factory()->create(['base_rate' => 5000, 'capacity' => 2])->id]);

        return app(BookingService::class)->create([
            'guest_id' => $guest->id,
            'arrival_date' => today()->toDateString(),
            'departure_date' => today()->addDays(2)->toDateString(),
            'meal_plan' => 'BB',
            'status' => 'booked',
            'rooms' => [['room_id' => $room->id, 'adults' => 2, 'children' => 0]],
        ], $this->userWithRole('receptionist'));
    }

    public function test_every_role_gets_a_working_dashboard(): void
    {
        $guest = $this->userWithRole('guest');
        $guest->update(['name' => 'Wanjiru Guest']);
        $this->bookArrivalToday($guest);

        foreach (['admin', 'manager', 'receptionist', 'housekeeping', 'kitchen', 'finance', 'hr', 'procurement'] as $role) {
            $this->actingAs($this->userWithRole($role))->get(route('dashboard'))->assertOk();
        }

        $this->actingAs($guest)->get(route('dashboard'))->assertOk()->assertSee('Your stays')->assertDontSee('Front desk');
    }

    public function test_sections_follow_roles(): void
    {
        $guest = User::factory()->create(['name' => 'Wanjiru Guest']);
        $this->bookArrivalToday($guest);

        $this->actingAs($this->userWithRole('receptionist'))->get(route('dashboard'))
            ->assertSee('Front desk')->assertSee('Wanjiru Guest')->assertDontSee('Waiting for you')->assertDontSee('Owed to the hotel');

        $this->actingAs($this->userWithRole('manager'))->get(route('dashboard'))
            ->assertSee('Occupied tonight')->assertSee('Front desk');

        // Two roles, both sets of sections
        $this->actingAs($this->userWithRole('kitchen', 'finance'))->get(route('dashboard'))
            ->assertSee('Open the board')->assertSee('Overdue invoices')->assertDontSee('Front desk');

        // No role: told to ask for access
        $this->actingAs(User::factory()->create())->get(route('dashboard'))->assertOk()->assertSee('no role yet');
    }

    public function test_my_tasks_and_approvals(): void
    {
        $cleaner = $this->userWithRole('housekeeping');
        $type = TaskType::create(['name' => 'Clean', 'code' => 'CLEAN', 'category' => 'cleaning']);
        Task::create(['task_type_id' => $type->id, 'title' => 'Polish lobby floor', 'assigned_to' => $cleaner->id, 'due_date' => now()->addHour(), 'created_by' => $cleaner->id]);

        $this->actingAs($cleaner)->get(route('dashboard'))->assertSee('My tasks')->assertSee('Polish lobby floor');

        $staff = $this->userWithRole('kitchen');
        \App\Models\LeaveRequest::create(['user_id' => $staff->id, 'leave_type' => 'annual', 'start_date' => today()->addDays(5), 'end_date' => today()->addDays(6), 'reason' => 'Wedding', 'status' => 'pending']);

        $this->actingAs($this->userWithRole('manager'))->get(route('dashboard'))->assertSee('Waiting for you (1)')->assertSee('Annual leave for');
    }
}
