@extends('layouts.app')

@php
    use App\Enums\TaskStatus;

    $user = auth()->user();
    $status = $task->status;
    $isManager = $user->hasAnyRole(['admin', 'manager']);
    $canCancel = $isManager || $task->created_by === $user->id;
    $primary = 'inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-primary/90';
    $secondary = 'inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300';
    $actions = array_filter([
        [TaskStatus::InProgress, $status === TaskStatus::Completed ? 'Reopen' : ($status === TaskStatus::OnHold ? 'Resume' : 'Start'), 'fa-play', $secondary],
        [TaskStatus::Completed, 'Mark done', 'fa-check', $primary],
        $isManager ? [TaskStatus::Verified, 'Check off', 'fa-clipboard-check', $primary] : null,
    ], fn ($a) => $a && $status->canTransitionTo($a[0]));
    [$done, $total] = $task->checklist_counts;
    $row = fn ($label, $value) => '<div class="flex justify-between gap-3"><dt class="text-gray-500 dark:text-gray-400">' . e($label) . '</dt><dd class="text-right text-gray-800 dark:text-white/90">' . e($value ?? '—') . '</dd></div>';
@endphp

@section('content')
<div x-data="{ pageName: @js('Task #' . $task->id) }">
    @include('partials.breadcrumb')
</div>

<div class="space-y-6" x-data="{ reason: null }">
    <!-- Header -->
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <a href="{{ route('tasks.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                <i class="fas fa-arrow-left"></i> Back to Tasks
            </a>
            <div class="mt-2 flex flex-wrap items-center gap-3">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ $task->title }}</h2>
                @include('partials.status-badge', ['status' => $status])
            </div>
            <p class="text-gray-600 dark:text-gray-400">
                {{ $task->taskType->name ?? 'Task' }} · <span class="{{ $task->priority_classes }}">{{ $task->priority_label }} priority</span> ·
                due {{ $task->due_date?->format('D d M, H:i') }}
                @if ($task->is_overdue)<span class="font-medium text-error-600 dark:text-error-400">(overdue)</span>@endif
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if ($status->isOpen())
                <button type="button" @click="$dispatch('open-task-form', @js($task->formData()))" class="{{ $secondary }}"><i class="fas fa-edit"></i> Edit</button>
            @endif
            @foreach ($actions as [$target, $label, $icon, $class])
                <form method="POST" action="{{ route('tasks.status', $task) }}">
                    @csrf
                    <input type="hidden" name="status" value="{{ $target->value }}">
                    <button type="submit" class="{{ $class }}"><i class="fas {{ $icon }}"></i> {{ $label }}</button>
                </form>
            @endforeach
            @if ($status->canTransitionTo(TaskStatus::OnHold))
                <button type="button" @click="reason = 'on_hold'" class="{{ $secondary }}"><i class="fas fa-pause"></i> Hold</button>
            @endif
            @if ($canCancel && $status->canTransitionTo(TaskStatus::Cancelled))
                <button type="button" @click="reason = 'cancelled'" class="inline-flex items-center gap-2 rounded-lg border border-error-300 px-4 py-2.5 text-sm font-medium text-error-600 hover:bg-error-50 dark:border-error-500/40 dark:text-error-400"><i class="fas fa-ban"></i> Cancel</button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            @if ($task->description)
                <div class="rounded-2xl border border-gray-100 bg-white p-5 text-sm text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                    <p class="whitespace-pre-line">{{ $task->description }}</p>
                </div>
            @endif

            <!-- Checklist -->
            @if ($total)
                <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900"
                     x-data="{
                        items: @js($task->checklist),
                        editable: @js($status->isOpen()),
                        async toggle(i) {
                            if (!this.editable) return;
                            const value = !this.items[i].completed;
                            this.items[i].completed = value;
                            const r = await fetch(@js(route('tasks.checklist', $task)), { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }, body: JSON.stringify({ index: i, completed: value }) });
                            if (!r.ok) this.items[i].completed = !value;
                        },
                        get done() { return this.items.filter(i => i.completed).length; },
                     }">
                    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                        <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Checklist</h3>
                        <span class="text-sm text-gray-500 dark:text-gray-400" x-text="`${done} of ${items.length}`"></span>
                    </div>
                    <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                        <template x-for="(item, i) in items" :key="i">
                            <li>
                                <button type="button" @click="toggle(i)" :disabled="!editable" class="flex w-full items-center gap-3 px-5 py-3 text-left text-sm disabled:cursor-default">
                                    <i class="fa-lg" :class="item.completed ? 'fas fa-check-square text-success-500' : 'far fa-square text-gray-400'"></i>
                                    <span :class="item.completed ? 'text-gray-400 line-through' : 'text-gray-800 dark:text-white/90'" x-text="item.item"></span>
                                </button>
                            </li>
                        </template>
                    </ul>
                </div>
            @endif

            <!-- Stock used -->
            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Items used</h3>
                    @if ($task->items->isNotEmpty())
                        <span class="text-sm text-gray-500 dark:text-gray-400">Cost {{ SystemHelper::currencySymbol() }} {{ number_format($task->items->sum(fn ($i) => $i->total_cost), 2) }}</span>
                    @endif
                </div>
                <ul class="divide-y divide-gray-100 text-sm dark:divide-gray-800">
                    @forelse ($task->items as $line)
                        <li class="flex justify-between px-5 py-2.5">
                            <span class="text-gray-800 dark:text-white/90">{{ $line->inventoryItem->name ?? 'Item' }}</span>
                            <span class="text-gray-600 dark:text-gray-400">{{ $line->inventoryItem?->qty($line->quantity_used) }}</span>
                        </li>
                    @empty
                        <li class="px-5 py-3 text-gray-500 dark:text-gray-400">Nothing recorded.</li>
                    @endforelse
                </ul>
                @if (($status->isOpen() || $status === TaskStatus::Completed) && $stock->isNotEmpty())
                    <form method="POST" action="{{ route('tasks.items.store', $task) }}" class="flex flex-wrap gap-2 border-t border-gray-100 p-4 dark:border-gray-800">
                        @csrf
                        <select name="inventory_item_id" required class="h-10 min-w-0 flex-1 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="">Item from the store…</option>
                            @foreach ($stock as $stockItem)
                                <option value="{{ $stockItem->id }}" @selected((int) old('inventory_item_id') === $stockItem->id)>{{ $stockItem->name }} ({{ $stockItem->qty() }} left)</option>
                            @endforeach
                        </select>
                        <input type="number" name="quantity" required min="0.01" step="0.01" value="{{ old('quantity', 1) }}" class="h-10 w-24 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">
                        <button type="submit" class="rounded-lg bg-primary px-4 text-sm font-medium text-white hover:bg-primary/90">Use</button>
                    </form>
                @endif
            </div>

            <!-- Comments -->
            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Notes</h3>
                </div>
                <ul class="divide-y divide-gray-100 text-sm dark:divide-gray-800">
                    @forelse ($task->comments as $comment)
                        <li class="px-5 py-3">
                            <p class="whitespace-pre-line text-gray-800 dark:text-white/90">{{ $comment->content }}</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $comment->user->name ?? '—' }} · {{ $comment->created_at->format('d M, H:i') }}</p>
                        </li>
                    @empty
                        <li class="px-5 py-3 text-gray-500 dark:text-gray-400">No notes yet.</li>
                    @endforelse
                </ul>
                <form method="POST" action="{{ route('tasks.comments.store', $task) }}" class="flex gap-2 border-t border-gray-100 p-4 dark:border-gray-800">
                    @csrf
                    <input type="text" name="content" required maxlength="2000" placeholder="Add a note, e.g. needs a new bulb from stores"
                           class="h-10 flex-1 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">
                    <button type="submit" class="rounded-lg bg-primary px-4 text-sm font-medium text-white hover:bg-primary/90">Add</button>
                </form>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800"><h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Details</h3></div>
                <dl class="space-y-3 p-5 text-sm">
                    {!! $row('Assigned to', $task->assignee->name ?? 'Not assigned') !!}
                    @if ($task->room)
                        <div class="flex justify-between gap-3"><dt class="text-gray-500 dark:text-gray-400">Room</dt><dd class="text-right">
                            <a href="{{ route('rooms.show', $task->room) }}" class="text-gray-800 hover:text-primary dark:text-white/90">{{ $task->room->room_number }}</a>
                            <span class="block text-xs text-gray-500">{{ $task->room->status->label() }}{{ $task->blocks_room ? ' · held for this job' : '' }}</span>
                        </dd></div>
                    @endif
                    @if ($task->stay)
                        <div class="flex justify-between gap-3"><dt class="text-gray-500 dark:text-gray-400">Booking</dt><dd class="text-right text-gray-800 dark:text-white/90">#{{ $task->stay->id }} · {{ $task->stay->guest->name ?? '' }}</dd></div>
                    @endif
                    {!! $row('Department', $task->department?->name) !!}
                    {!! $row('Expected time', $task->estimated_minutes ? $task->estimated_minutes . ' min' : null) !!}
                    {!! $row('Took', $task->actual_minutes !== null ? $task->actual_minutes . ' min' : null) !!}
                    {!! $row('Created', ($task->creator->name ?? '—') . ', ' . $task->created_at->format('d M H:i')) !!}
                    @if ($task->started_at){!! $row('Started', $task->started_at->format('d M H:i')) !!}@endif
                    @if ($task->completed_at){!! $row('Done', $task->completed_at->format('d M H:i')) !!}@endif
                    @if ($task->verified_at){!! $row('Checked by', ($task->verifier->name ?? '—') . ', ' . $task->verified_at->format('d M H:i')) !!}@endif
                </dl>
                @if ($task->verification_notes)
                    <p class="border-t border-gray-100 px-5 py-3 text-sm text-gray-700 dark:border-gray-800 dark:text-gray-300">{{ $task->verification_notes }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Hold / cancel reason -->
    <div x-show="reason" x-cloak x-transition class="fixed inset-0 z-[99999] flex items-center justify-center p-5" @keydown.escape.window="reason = null">
        <div class="fixed inset-0 bg-gray-400/50 backdrop-blur-sm dark:bg-gray-900/80" @click="reason = null"></div>
        <form method="POST" action="{{ route('tasks.status', $task) }}" class="relative w-full max-w-md space-y-4 rounded-2xl bg-white p-6 dark:bg-gray-900">
            @csrf
            <input type="hidden" name="status" :value="reason">
            <h4 class="text-lg font-medium text-gray-800 dark:text-white/90" x-text="reason === 'cancelled' ? 'Cancel this task?' : 'Put on hold'"></h4>
            <input type="text" name="note" required maxlength="1000" placeholder="Why? e.g. waiting for spare part"
                   class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">
            <div class="flex justify-end gap-3">
                <button type="button" @click="reason = null" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Back</button>
                <button type="submit" class="rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white hover:bg-primary/90">Save</button>
            </div>
        </form>
    </div>
</div>

@if ($status->isOpen())
    @include('tasks.partials.task-modal')
@endif
@endsection
