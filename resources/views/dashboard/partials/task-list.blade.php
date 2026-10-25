{{-- Compact task list. @include('dashboard.partials.task-list', ['tasks' => ..., 'showAssignee' => false, 'empty' => '...']) (load room, assignee) --}}
<ul class="divide-y divide-gray-100 text-sm dark:divide-gray-800">
    @forelse ($tasks as $task)
        <li class="flex flex-wrap items-center justify-between gap-2 px-5 py-3">
            <div>
                <a href="{{ route('tasks.show', $task) }}" class="font-medium text-gray-800 hover:text-primary dark:text-white/90">{{ $task->title }}</a>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    <span class="{{ $task->priority_classes }}">{{ $task->priority_label }}</span>
                    @if ($task->room) · Room {{ $task->room->room_number }}@endif
                    @if ($showAssignee ?? false) · {{ $task->assignee->name ?? 'Not assigned' }}@endif
                </p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs {{ $task->is_overdue ? 'font-medium text-error-600 dark:text-error-400' : 'text-gray-500 dark:text-gray-400' }}">due {{ $task->due_date?->format('D H:i') }}</span>
                @include('partials.status-badge', ['status' => $task->status])
            </div>
        </li>
    @empty
        <li class="px-5 py-4 text-gray-500 dark:text-gray-400">{{ $empty ?? 'Nothing here.' }}</li>
    @endforelse
</ul>
