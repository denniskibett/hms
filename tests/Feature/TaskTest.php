<?php

namespace Tests\Feature;

use App\Enums\RoomStatus;
use App\Enums\TaskStatus;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Task;
use App\Models\TaskType;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;
    private User $housekeeper;
    private TaskType $checkoutClean;
    private TaskType $repair;
    private Room $room;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = $this->userWithRole('manager');
        $this->housekeeper = $this->userWithRole('housekeeping');
        $this->checkoutClean = TaskType::create([
            'name' => 'Check-out Cleaning', 'code' => 'CHECKOUT_CLEAN', 'category' => 'cleaning', 'requires_room' => true,
            'default_checklist' => [['item' => 'Strip beds', 'completed' => false], ['item' => 'Clean bathroom', 'completed' => false]],
            'default_estimated_minutes' => 60,
        ]);
        TaskType::create(['name' => 'Daily Cleaning', 'code' => 'DAILY_CLEAN', 'category' => 'cleaning', 'requires_room' => true]);
        $this->repair = TaskType::create(['name' => 'Room Maintenance', 'code' => 'ROOM_MAINT', 'category' => 'maintenance']);
        $this->room = Room::factory()->create(['room_type_id' => RoomType::factory()->create(['base_rate' => 5000, 'capacity' => 2])->id]);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::firstOrCreate(['name' => $role])->id);

        return $user;
    }

    private function moveTask(Task $task, string $status, User $by, array $extra = [])
    {
        return $this->actingAs($by)->post(route('tasks.status', $task), ['status' => $status] + $extra);
    }

    public function test_checkout_creates_a_cleaning_task_and_finishing_it_frees_the_room(): void
    {
        $bookings = app(BookingService::class);
        $stay = $bookings->create([
            'guest_id' => User::factory()->create()->id,
            'arrival_date' => today()->toDateString(),
            'departure_date' => today()->addDay()->toDateString(),
            'meal_plan' => 'BB',
            'status' => 'booked',
            'rooms' => [['room_id' => $this->room->id, 'adults' => 1, 'children' => 0]],
        ], $this->manager);
        $bookings->checkIn($stay, $this->manager);
        $bookings->checkOut($stay->fresh(), $this->manager);

        $this->assertSame(RoomStatus::Cleaning, $this->room->fresh()->status);
        $task = Task::sole();
        $this->assertSame(TaskStatus::Pending, $task->status);
        $this->assertCount(2, $task->checklist);
        $this->assertSame(60, $task->estimated_minutes);

        // The housekeeper starts it (and takes it), ticks the list, finishes
        $this->moveTask($task, 'in_progress', $this->housekeeper)->assertSessionHas('success');
        $this->assertSame($this->housekeeper->id, $task->fresh()->assigned_to);
        $this->actingAs($this->housekeeper)->postJson(route('tasks.checklist', $task), ['index' => 0, 'completed' => true])->assertJson(['counts' => [1, 2]]);
        $this->moveTask($task, 'completed', $this->housekeeper)->assertSessionHas('success');

        $this->assertSame(RoomStatus::Available, $this->room->fresh()->status);

        // Only a manager checks it off
        $this->moveTask($task, 'verified', $this->housekeeper)->assertSessionHas('error');
        $this->moveTask($task, 'verified', $this->manager)->assertSessionHas('success');
        $this->assertSame(TaskStatus::Verified, $task->fresh()->status);
    }

    public function test_room_stays_in_cleaning_while_another_cleaning_task_is_open(): void
    {
        $this->room->update(['status' => RoomStatus::Cleaning]);
        $first = Task::create(['task_type_id' => $this->checkoutClean->id, 'title' => 'A', 'room_id' => $this->room->id, 'due_date' => now(), 'created_by' => $this->manager->id]);
        Task::create(['task_type_id' => $this->checkoutClean->id, 'title' => 'B', 'room_id' => $this->room->id, 'due_date' => now(), 'created_by' => $this->manager->id]);

        $this->moveTask($first, 'completed', $this->housekeeper);

        $this->assertSame(RoomStatus::Cleaning, $this->room->fresh()->status);
    }

    public function test_maintenance_can_take_a_room_out_of_service(): void
    {
        $this->actingAs($this->manager)->postJson(route('tasks.store'), [
            'task_type_id' => $this->repair->id,
            'title' => 'Fix leaking tap',
            'room_id' => $this->room->id,
            'blocks_room' => true,
            'priority' => 'high',
            'due_date' => now()->addDay()->format('Y-m-d H:i'),
            'assigned_to' => $this->housekeeper->id,
        ])->assertCreated();

        $task = Task::sole();
        $this->assertSame(TaskStatus::Assigned, $task->status);
        $this->assertSame(RoomStatus::Maintenance, $this->room->fresh()->status);

        // Hold needs a reason
        $this->moveTask($task, 'on_hold', $this->housekeeper)->assertSessionHasErrors('note');
        $this->moveTask($task, 'on_hold', $this->housekeeper, ['note' => 'Waiting for part']);
        $this->assertSame(1, $task->comments()->count());

        $this->moveTask($task, 'in_progress', $this->housekeeper);
        $this->moveTask($task, 'completed', $this->housekeeper);
        $this->assertSame(RoomStatus::Available, $this->room->fresh()->status);
    }

    public function test_room_required_and_validation(): void
    {
        $this->actingAs($this->manager)->postJson(route('tasks.store'), [
            'task_type_id' => $this->checkoutClean->id, 'title' => 'Clean', 'priority' => 'medium', 'due_date' => now()->format('Y-m-d H:i'),
        ])->assertJsonValidationErrors('room_id');
    }

    public function test_staff_only_see_their_own_and_their_departments_tasks(): void
    {
        $cook = $this->userWithRole('kitchen');
        $mine = Task::create(['task_type_id' => $this->repair->id, 'title' => 'Fix oven', 'assigned_to' => $cook->id, 'due_date' => now(), 'created_by' => $this->manager->id]);
        $other = Task::create(['task_type_id' => $this->repair->id, 'title' => 'Fix gate', 'due_date' => now(), 'created_by' => $this->manager->id]);

        $this->actingAs($cook)->get(route('tasks.index'))->assertOk()->assertSee('Fix oven')->assertDontSee('Fix gate');
        $this->actingAs($cook)->get(route('tasks.show', $other))->assertForbidden();
        $this->actingAs($cook)->get(route('tasks.show', $mine))->assertOk();
        $this->actingAs($this->housekeeper)->get(route('tasks.index'))->assertSee('Fix gate');

        // Only a manager or the creator cancels
        $this->moveTask($mine, 'cancelled', $cook, ['note' => 'Not needed'])->assertSessionHas('error');
        $this->moveTask($mine, 'cancelled', $this->manager, ['note' => 'Not needed'])->assertSessionHas('success');
    }

    public function test_daily_cleaning_for_occupied_rooms(): void
    {
        $this->room->update(['status' => RoomStatus::Occupied]);
        Room::factory()->create(['room_type_id' => $this->room->room_type_id, 'status' => RoomStatus::Available]);

        $this->actingAs($this->housekeeper)->post(route('tasks.daily-cleaning'))->assertSessionHas('success');
        $this->actingAs($this->housekeeper)->post(route('tasks.daily-cleaning'));

        $this->assertSame(1, Task::count());
        $this->assertSame($this->room->id, Task::sole()->room_id);
    }

    public function test_task_types_and_pages(): void
    {
        $this->actingAs($this->housekeeper)->postJson(route('task-types.store'), ['name' => 'X', 'category' => 'other'])->assertForbidden();
        $this->actingAs($this->manager)->postJson(route('task-types.store'), [
            'name' => 'AC Servicing', 'category' => 'maintenance', 'checklist' => "Clean filter\n\nCheck gas ", 'default_estimated_minutes' => 45,
        ])->assertCreated();
        $type = TaskType::where('name', 'AC Servicing')->first();
        $this->assertSame('AC_SERVICING', $type->code);
        $this->assertSame(['Clean filter', 'Check gas'], $type->checklist_items);

        $task = Task::create(['task_type_id' => $type->id, 'title' => 'Service AC', 'room_id' => $this->room->id, 'due_date' => now()->subHour(), 'created_by' => $this->manager->id]);
        $this->actingAs($this->manager)->get(route('tasks.index'))->assertOk()->assertSee('Service AC');
        $this->actingAs($this->manager)->get(route('tasks.index', ['view' => 'overdue']))->assertOk()->assertSee('Service AC');
        $this->actingAs($this->manager)->get(route('tasks.show', $task))->assertOk()->assertSee('Clean filter');
        $this->actingAs($this->housekeeper)->get(route('dashboard'))->assertOk();
        $this->actingAs($this->manager)->get(route('rooms.show', $this->room))->assertOk();
    }
}
