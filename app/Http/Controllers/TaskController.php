<?php

namespace App\Http\Controllers;

use App\Enums\RoomStatus;
use App\Enums\TaskStatus;
use App\Exceptions\StockException;
use App\Helpers\SystemHelper;
use App\Models\Department;
use App\Models\InventoryItem;
use App\Models\Room;
use App\Models\Task;
use App\Models\TaskType;
use App\Models\User;
use App\Services\CoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Jobs for staff: room cleaning, maintenance, kitchen and other work.
 */
class TaskController extends Controller
{
    public function __construct(private CoreService $core)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        self::repeatDueTasks();
        $filters = $request->validate([
            'view' => ['nullable', Rule::in(['open', 'mine', 'overdue', 'done', 'all'])],
            'category' => ['nullable', Rule::in(array_keys(TaskType::CATEGORIES))],
            'assigned_to' => ['nullable', 'integer'],
            'room_id' => ['nullable', 'integer'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $filters['view'] ??= 'open';

        $tasks = Task::with(['taskType', 'assignee:id,name', 'room:id,room_number,status'])
            ->visibleTo($user)
            ->when($filters['view'] === 'open', fn ($q) => $q->open())
            ->when($filters['view'] === 'mine', fn ($q) => $q->open()->where('assigned_to', $user->id))
            ->when($filters['view'] === 'overdue', fn ($q) => $q->overdue())
            ->when($filters['view'] === 'done', fn ($q) => $q->whereIn('status', [TaskStatus::Completed->value, TaskStatus::Verified->value]))
            ->when($filters['category'] ?? null, fn ($q, $c) => $q->category($c))
            ->when($filters['assigned_to'] ?? null, fn ($q, $id) => $q->where('assigned_to', $id))
            ->when($filters['room_id'] ?? null, fn ($q, $id) => $q->where('room_id', $id))
            ->when($filters['search'] ?? null, fn ($q, $term) => $q->search($term))
            ->when(in_array($filters['view'], ['done', 'all'], true),
                fn ($q) => $q->latest('updated_at'),
                fn ($q) => $q->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")->orderBy('due_date'))
            ->paginate(SystemHelper::paginationLimit())
            ->withQueryString();

        $visible = fn () => Task::visibleTo($user);
        $stats = [
            'open' => $visible()->open()->count(),
            'mine' => $visible()->open()->where('assigned_to', $user->id)->count(),
            'overdue' => $visible()->overdue()->count(),
            'done_today' => $visible()->whereIn('status', [TaskStatus::Completed->value, TaskStatus::Verified->value])->whereDate('completed_at', today())->count(),
        ];

        return view('tasks.index', array_merge(compact('tasks', 'stats', 'filters'), $this->formOptions()));
    }

    public function show(Request $request, Task $task): View
    {
        $this->authorizeView($request->user(), $task);

        $task->load(['taskType', 'assignee', 'department', 'room.roomType', 'stay.guest', 'creator', 'verifier', 'comments.user', 'items.inventoryItem', 'series'])
            ->loadCount('occurrences');
        $stock = InventoryItem::active()->where('quantity', '>', 0)->orderBy('name')->get(['id', 'name', 'unit_of_measure', 'quantity']);

        return view('tasks.show', array_merge(compact('task', 'stock'), $this->formOptions()));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $task = DB::transaction(function () use ($data, $request) {
            $task = Task::create($data + ['created_by' => $request->user()->id]);
            $this->blockRoom($task);

            return $task;
        });

        if ($task->is_recurring) {
            Task::generateRecurring();
        }

        $this->core->log($request->user()->id, 'task_created', "Task #{$task->id} \"{$task->title}\" created", ['task_id' => $task->id]);

        return response()->json(['success' => true, 'message' => 'Task created.', 'url' => route('tasks.show', $task)], 201);
    }

    public function update(Request $request, Task $task): JsonResponse
    {
        $this->authorizeView($request->user(), $task);
        if (! $task->status->isOpen()) {
            return response()->json(['success' => false, 'message' => 'Finished tasks can\'t be edited.'], 422);
        }

        $data = $this->validated($request, $task);

        DB::transaction(function () use ($task, $data) {
            $reassigned = $data['assigned_to'] != $task->assigned_to;
            $task->fill($data);

            if ($reassigned) {
                $task->assigned_at = $task->assigned_to ? now() : null;
                if (in_array($task->status, [TaskStatus::Pending, TaskStatus::Assigned], true)) {
                    $task->status = $task->assigned_to ? TaskStatus::Assigned : TaskStatus::Pending;
                }
            }

            $task->save();
            $this->blockRoom($task);
        });

        return response()->json(['success' => true, 'message' => 'Task updated.']);
    }

    /**
     * End a repeating series. Copies already made stay as they are.
     */
    public function stopRepeating(Request $request, Task $task): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeView($user, $task);
        $root = $task->series ?? $task;

        if (! $root->is_recurring) {
            return back()->with('error', 'This task does not repeat.');
        }
        if (! $user->hasAnyRole(['admin', 'manager']) && $root->created_by !== $user->id) {
            return back()->with('error', 'Only a manager or the person who set it up can stop a repeating task.');
        }

        $root->update(['is_recurring' => false]);
        $root->comments()->create(['user_id' => $user->id, 'content' => "Stopped repeating ({$root->repeat_label})."]);
        $this->core->log($user->id, 'task_repeat_stopped', "Task #{$root->id} \"{$root->title}\" no longer repeats", ['task_id' => $root->id]);

        return back()->with('success', 'This task will not repeat any more.');
    }

    /**
     * Make due copies of repeating tasks, at most once an hour. The scheduler
     * also does this daily (tasks:repeat); this keeps it working without one.
     */
    public static function repeatDueTasks(): void
    {
        if (\Illuminate\Support\Facades\Cache::add('tasks:repeat:ran', true, now()->addHour())) {
            Task::generateRecurring();
        }
    }

    /**
     * Start, finish, put on hold, cancel, check (verify) or reopen.
     */
    public function updateStatus(Request $request, Task $task): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeView($user, $task);

        $data = $request->validate([
            'status' => ['required', Rule::enum(TaskStatus::class)],
            'note' => ['nullable', 'string', 'max:1000', Rule::requiredIf(fn () => in_array($request->input('status'), ['cancelled', 'on_hold'], true))],
        ], [
            'note.required' => 'Say why.',
        ]);
        $status = TaskStatus::from($data['status']);

        if (! $task->status->canTransitionTo($status)) {
            return back()->with('error', "This task is {$task->status->label()} and can't be marked {$status->label()}.");
        }
        if ($status === TaskStatus::Verified && ! $user->hasAnyRole(['admin', 'manager'])) {
            return back()->with('error', 'Only a manager can check off finished work.');
        }
        if ($status === TaskStatus::Cancelled && ! $user->hasAnyRole(['admin', 'manager']) && $task->created_by !== $user->id) {
            return back()->with('error', 'Only a manager or the person who created the task can cancel it.');
        }

        DB::transaction(function () use ($task, $status, $data, $user) {
            $changes = ['status' => $status];

            match ($status) {
                TaskStatus::InProgress => $changes += [
                    'started_at' => $task->started_at ?? now(),
                    // Whoever starts an unassigned job takes it
                    'assigned_to' => $task->assigned_to ?? $user->id,
                    'assigned_at' => $task->assigned_at ?? now(),
                    'completed_at' => null,
                ],
                TaskStatus::Completed => $changes += [
                    'completed_at' => now(),
                    'assigned_to' => $task->assigned_to ?? $user->id,
                    'actual_minutes' => $task->started_at ? (int) $task->started_at->diffInMinutes(now()) : $task->actual_minutes,
                ],
                TaskStatus::Verified => $changes += ['verified_by' => $user->id, 'verified_at' => now(), 'verification_notes' => $data['note'] ?? null],
                default => null,
            };

            $task->update($changes);

            if (! empty($data['note']) && $status !== TaskStatus::Verified) {
                $task->comments()->create(['user_id' => $user->id, 'content' => "{$status->label()}: {$data['note']}"]);
            }

            if (in_array($status, [TaskStatus::Completed, TaskStatus::Cancelled], true)) {
                $task->releaseRoom();
            }
        });

        $this->core->log($user->id, 'task_status', "Task #{$task->id} marked {$status->label()}", ['task_id' => $task->id]);

        return back()->with('success', "Task marked {$status->label()}.");
    }

    /**
     * Tick or untick a checklist item.
     */
    public function toggleChecklist(Request $request, Task $task): JsonResponse
    {
        $this->authorizeView($request->user(), $task);

        $data = $request->validate([
            'index' => ['required', 'integer', 'min:0'],
            'completed' => ['required', 'boolean'],
        ]);

        $checklist = $task->checklist ?? [];
        abort_unless(isset($checklist[$data['index']]) && $task->status->isOpen(), 422);

        $checklist[$data['index']]['completed'] = $data['completed'];
        $task->update(['checklist' => $checklist]);

        return response()->json(['success' => true, 'counts' => $task->checklist_counts]);
    }

    public function comment(Request $request, Task $task): RedirectResponse
    {
        $this->authorizeView($request->user(), $task);

        $data = $request->validate(['content' => ['required', 'string', 'max:2000']]);
        $task->comments()->create(['user_id' => $request->user()->id, 'content' => $data['content']]);

        return back()->with('success', 'Comment added.');
    }

    /**
     * Record stock used on the task; it comes out of the store.
     */
    public function useItem(Request $request, Task $task): RedirectResponse
    {
        $this->authorizeView($request->user(), $task);
        if (! $task->status->isOpen() && $task->status !== TaskStatus::Completed) {
            return back()->with('error', 'This task is closed.');
        }

        $data = $request->validate([
            'inventory_item_id' => ['required', Rule::exists('inventory_items', 'id')->where('status', true)],
            'quantity' => ['required', 'numeric', 'min:0.01', 'max:99999'],
        ]);
        $item = InventoryItem::find($data['inventory_item_id']);

        try {
            DB::transaction(function () use ($task, $item, $data, $request) {
                $item->move('out', -(float) $data['quantity'], $request->user(), [
                    'reference_type' => 'task',
                    'reference_id' => $task->id,
                    'department_id' => $task->department_id,
                    'notes' => $task->title,
                ]);

                $line = $task->items()->firstOrNew(['inventory_item_id' => $item->id]);
                $previous = (float) $line->quantity_used;
                $quantity = $previous + (float) $data['quantity'];
                // Keep a blended cost if the item was used before at another cost
                $line->unit_cost_at_time = $quantity > 0
                    ? round(($previous * (float) $line->unit_cost_at_time + (float) $data['quantity'] * (float) $item->unit_cost) / $quantity, 2)
                    : $item->unit_cost;
                $line->quantity_used = $quantity;
                $line->save();
            });
        } catch (StockException $e) {
            throw ValidationException::withMessages(['quantity' => $e->getMessage()]);
        }

        return back()->with('success', "{$item->qty((float) $data['quantity'])} of {$item->name} recorded.");
    }

    /**
     * One daily-clean task for every occupied room that doesn't have an open cleaning task today.
     */
    public function generateDailyCleaning(Request $request): RedirectResponse
    {
        $type = TaskType::where('code', 'DAILY_CLEAN')->first();
        if (! $type) {
            return back()->with('error', 'There is no task type with code DAILY_CLEAN.');
        }

        $rooms = Room::where('status', RoomStatus::Occupied->value)
            ->whereDoesntHave('housekeepingTasks', fn ($q) => $q->open()->category('cleaning')->whereDate('due_date', today()))
            ->with('currentStay')
            ->get();

        DB::transaction(function () use ($rooms, $type, $request) {
            foreach ($rooms as $room) {
                Task::create([
                    'task_type_id' => $type->id,
                    'title' => "Daily cleaning – Room {$room->room_number}",
                    'room_id' => $room->id,
                    'stay_id' => $room->currentStay?->id,
                    'priority' => 'medium',
                    'due_date' => today()->setTime(14, 0),
                    'created_by' => $request->user()->id,
                ]);
            }
        });

        return back()->with('success', $rooms->count() . ' daily cleaning ' . Str::plural('task', $rooms->count()) . ' created.');
    }

    // ---- Task types (managers) ---------------------------------------------

    public function storeType(Request $request): JsonResponse
    {
        $data = $this->validatedType($request);
        $data['code'] = $this->uniqueCode($data['name']);
        $type = TaskType::create($data);

        return response()->json(['success' => true, 'message' => "{$type->name} added."], 201);
    }

    public function updateType(Request $request, TaskType $taskType): JsonResponse
    {
        $taskType->update($this->validatedType($request, $taskType));

        return response()->json(['success' => true, 'message' => "{$taskType->name} updated."]);
    }

    private function validatedType(Request $request, ?TaskType $type = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('task_types', 'name')->ignore($type?->id)->whereNull('deleted_at')],
            'category' => ['required', Rule::in(array_keys(TaskType::CATEGORIES))],
            'department_id' => ['nullable', 'exists:departments,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'checklist' => ['nullable', 'string', 'max:3000'],
            'default_estimated_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'requires_room' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $items = collect(preg_split('/\r?\n/', $data['checklist'] ?? ''))->map(fn ($l) => trim($l))->filter()->values();
        unset($data['checklist']);
        $data['default_checklist'] = $items->map(fn ($item) => ['item' => $item, 'completed' => false])->all() ?: null;

        return $data;
    }

    private function uniqueCode(string $name): string
    {
        $code = Str::upper(Str::limit(Str::slug($name, '_'), 40, ''));
        $candidate = $code;
        for ($i = 2; TaskType::withTrashed()->where('code', $candidate)->exists(); $i++) {
            $candidate = "{$code}_{$i}";
        }

        return $candidate;
    }

    // ---- Helpers -----------------------------------------------------------

    private function authorizeView(User $user, Task $task): void
    {
        abort_unless(Task::visibleTo($user)->whereKey($task->id)->exists(), 403);
    }

    /**
     * A maintenance task that takes its room out of service.
     */
    private function blockRoom(Task $task): void
    {
        $room = $task->room;
        if ($task->blocks_room && $room && $room->status === RoomStatus::Available) {
            $room->update(['status' => RoomStatus::Maintenance]);
        }
    }

    private function formOptions(): array
    {
        return [
            'taskTypes' => TaskType::active()->orderBy('category')->orderBy('name')->get(),
            'staff' => User::where('status', 'active')
                ->whereHas('roles', fn ($q) => $q->whereNotIn('name', ['guest']))
                ->orderBy('name')
                ->get(['id', 'name']),
            'rooms' => Room::orderBy('room_number')->get(['id', 'room_number', 'status']),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
        ];
    }

    private function validated(Request $request, ?Task $task = null): array
    {
        $data = $request->validate([
            'task_type_id' => ['required', Rule::exists('task_types', 'id')->whereNull('deleted_at')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'blocks_room' => ['boolean'],
            'priority' => ['required', Rule::in(array_keys(Task::PRIORITIES))],
            'due_date' => ['required', 'date'],
            'estimated_minutes' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'recurrence_pattern' => ['nullable', Rule::in(array_keys(Task::REPEATS))],
            'recurrence_interval' => ['nullable', 'required_with:recurrence_pattern', 'integer', 'min:1', 'max:365'],
            'recurrence_end_date' => ['nullable', 'date', 'after_or_equal:due_date'],
        ], [
            'recurrence_end_date.after_or_equal' => 'The repeat must end on or after the first due date.',
        ]);

        $type = TaskType::find($data['task_type_id']);
        if ($type->requires_room && empty($data['room_id'])) {
            throw ValidationException::withMessages(['room_id' => "{$type->name} needs a room."]);
        }

        $data['assigned_to'] ??= null;
        $data['room_id'] ??= null;
        $data['blocks_room'] = ! empty($data['room_id']) && ($data['blocks_room'] ?? false);

        // Repeat settings live on the first task of a series; copies can't repeat themselves
        if ($task?->parent_task_id || empty($data['recurrence_pattern'])) {
            $data = array_merge($data, ['is_recurring' => false, 'recurrence_pattern' => null, 'recurrence_interval' => null, 'recurrence_end_date' => null]);
        } else {
            $data['is_recurring'] = true;
            $data['recurrence_end_date'] ??= null;
        }
        if ($task?->parent_task_id) {
            unset($data['is_recurring'], $data['recurrence_pattern'], $data['recurrence_interval'], $data['recurrence_end_date']);
        }

        return $data;
    }
}
