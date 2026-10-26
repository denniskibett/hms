<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Task;
use App\Models\TaskType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class RecurringTaskTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;
    private TaskType $type;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = User::factory()->create();
        $this->manager->roles()->attach(Role::firstOrCreate(['name' => 'manager'])->id);
        $this->type = TaskType::create([
            'name' => 'Lobby clean', 'code' => 'LOBBY', 'category' => 'cleaning',
            'default_checklist' => [['item' => 'Mop floor', 'completed' => false]],
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function createRepeating(array $overrides = []): Task
    {
        $this->actingAs($this->manager)->postJson(route('tasks.store'), array_merge([
            'task_type_id' => $this->type->id,
            'title' => 'Clean the lobby',
            'priority' => 'medium',
            'due_date' => now()->setTime(9, 0)->format('Y-m-d H:i'),
            'assigned_to' => $this->manager->id,
            'recurrence_pattern' => 'daily',
            'recurrence_interval' => 1,
        ], $overrides))->assertCreated();

        return Task::whereNull('parent_task_id')->latest('id')->first();
    }

    public function test_creating_a_daily_task_makes_tomorrows_copy(): void
    {
        Carbon::setTestNow('2026-11-02 08:00');
        $root = $this->createRepeating();

        $this->assertTrue($root->is_recurring);
        $this->assertSame('Every day', $root->repeat_label);

        $copy = Task::where('parent_task_id', $root->id)->sole();
        $this->assertSame('2026-11-03 09:00', $copy->due_date->format('Y-m-d H:i'));
        $this->assertSame($this->manager->id, $copy->assigned_to);
        $this->assertFalse($copy->checklist[0]['completed']);

        // Running again the same day makes nothing new
        $this->assertSame(0, Task::generateRecurring());
    }

    public function test_copies_keep_coming_whether_or_not_the_last_was_done(): void
    {
        Carbon::setTestNow('2026-11-02 08:00');
        $root = $this->createRepeating(['recurrence_pattern' => 'weekly', 'recurrence_interval' => 2]);
        $this->assertSame(0, $root->occurrences()->count());   // next is 16 Nov, too far ahead

        Carbon::setTestNow('2026-11-15 07:00');
        $this->assertSame(1, Task::generateRecurring());
        $this->assertSame('2026-11-16', $root->occurrences()->sole()->due_date->toDateString());
    }

    public function test_long_gaps_skip_ahead_instead_of_piling_up(): void
    {
        Carbon::setTestNow('2026-11-02 08:00');
        $root = $this->createRepeating();

        // Nothing ran for a month
        Carbon::setTestNow('2026-12-10 06:00');
        Task::generateRecurring();

        $dates = $root->occurrences()->orderBy('due_date')->pluck('due_date')->map->toDateString()->all();
        $this->assertSame(['2026-11-03', '2026-12-10', '2026-12-11'], $dates);
    }

    public function test_end_date_and_stop_repeating(): void
    {
        Carbon::setTestNow('2026-11-02 08:00');
        $root = $this->createRepeating(['recurrence_end_date' => '2026-11-04']);

        Carbon::setTestNow('2026-11-04 08:00');
        Task::generateRecurring();
        Carbon::setTestNow('2026-11-08 08:00');
        Task::generateRecurring();
        $this->assertSame(['2026-11-03', '2026-11-04'], $root->occurrences()->orderBy('due_date')->pluck('due_date')->map->toDateString()->all());

        $open = $this->createRepeating(['title' => 'Water plants']);
        $copy = $open->occurrences()->first();
        $this->actingAs($this->manager)->post(route('tasks.stop-repeating', $copy))->assertSessionHas('success');
        $this->assertFalse($open->fresh()->is_recurring);

        Carbon::setTestNow('2026-11-20 08:00');
        Task::generateRecurring();
        $this->assertSame(1, $open->occurrences()->count());
    }

    public function test_monthly_on_the_31st_and_copies_cannot_repeat(): void
    {
        Carbon::setTestNow('2026-01-31 08:00');
        $root = $this->createRepeating(['recurrence_pattern' => 'monthly']);
        $this->assertSame('2026-02-28', $root->nextDueAfter($root->due_date)->toDateString());

        Carbon::setTestNow('2026-02-27 10:00');
        Task::generateRecurring();
        $copy = $root->occurrences()->sole();

        $this->actingAs($this->manager)->putJson(route('tasks.update', $copy), $copy->formData() + ['recurrence_pattern' => 'daily'])->assertOk();
        $this->assertFalse($copy->fresh()->is_recurring);
        $this->assertNull($copy->fresh()->recurrence_pattern);
    }

    public function test_pages_and_command(): void
    {
        Carbon::setTestNow('2026-11-02 08:00');
        $root = $this->createRepeating();
        $copy = $root->occurrences()->first();

        Cache::forget('tasks:repeat:ran');
        $this->actingAs($this->manager)->get(route('tasks.index'))->assertOk()->assertSee('Clean the lobby');
        $this->actingAs($this->manager)->get(route('tasks.show', $root))->assertOk()->assertSee('Every day')->assertSee('Stop repeating');
        $this->actingAs($this->manager)->get(route('tasks.show', $copy))->assertOk()->assertSee("task #{$root->id}");

        Carbon::setTestNow('2026-11-03 08:00');
        $this->artisan('tasks:repeat')->expectsOutputToContain('1 repeating task created')->assertExitCode(0);
    }
}
