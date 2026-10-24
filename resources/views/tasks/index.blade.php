@extends('layouts.app')

@php
    $inputClass = 'h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
    $user = auth()->user();
    $isManager = $user->hasAnyRole(['admin', 'manager']);
    $canGenerate = $user->hasAnyRole(['admin', 'manager', 'receptionist', 'housekeeping']);
    $views = ['open' => 'Open', 'mine' => 'Mine', 'overdue' => 'Overdue', 'done' => 'Done', 'all' => 'All'];
    $query = fn (array $change) => route('tasks.index', array_filter(array_merge($filters, $change)));
@endphp

@section('content')
<div x-data="{ pageName: `Tasks` }">
    @include('partials.breadcrumb')
</div>

<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Tasks</h2>
            <p class="text-gray-600 dark:text-gray-400">Room cleaning, maintenance and other jobs</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if ($isManager)
                <button type="button" @click="$dispatch('open-type-form')" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <i class="fas fa-list-check"></i> Kinds of job
                </button>
            @endif
            @if ($canGenerate)
                <form method="POST" action="{{ route('tasks.daily-cleaning') }}" onsubmit="return confirm('Create a cleaning task for every occupied room that has none today?')">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        <i class="fas fa-broom"></i> Today's room cleaning
                    </button>
                </form>
            @endif
            <button type="button" @click="$dispatch('open-task-form')" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-primary/90">
                <i class="fas fa-plus"></i> New Task
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @include('partials.stat-card', ['label' => 'Open', 'value' => $stats['open'], 'icon' => 'fa-tasks', 'tone' => 'primary', 'href' => $query(['view' => 'open'])])
        @include('partials.stat-card', ['label' => 'Assigned to Me', 'value' => $stats['mine'], 'icon' => 'fa-user-check', 'tone' => 'info', 'href' => $query(['view' => 'mine'])])
        @include('partials.stat-card', ['label' => 'Overdue', 'value' => $stats['overdue'], 'icon' => 'fa-exclamation-triangle', 'tone' => 'error', 'href' => $query(['view' => 'overdue'])])
        @include('partials.stat-card', ['label' => 'Done Today', 'value' => $stats['done_today'], 'icon' => 'fa-check-circle', 'tone' => 'success', 'href' => $query(['view' => 'done'])])
    </div>

    <div class="flex gap-2 overflow-x-auto border-b border-gray-200 dark:border-gray-800">
        @foreach ($views as $value => $label)
            <a href="{{ $query(['view' => $value]) }}" class="-mb-px whitespace-nowrap border-b-2 px-4 py-2 text-sm font-medium {{ $filters['view'] === $value ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400' }}">{{ $label }}</a>
        @endforeach
    </div>

    <form method="GET" action="{{ route('tasks.index') }}" class="grid grid-cols-1 gap-3 rounded-2xl border border-gray-100 bg-white p-4 sm:grid-cols-2 lg:grid-cols-5 dark:border-gray-800 dark:bg-gray-900">
        <input type="hidden" name="view" value="{{ $filters['view'] }}">
        <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Title or room number" class="{{ $inputClass }} lg:col-span-2">
        <select name="category" class="{{ $inputClass }}" onchange="this.form.submit()">
            <option value="">All kinds</option>
            @foreach (\App\Models\TaskType::CATEGORIES as $value => $label)
                <option value="{{ $value }}" @selected(($filters['category'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="assigned_to" class="{{ $inputClass }}" onchange="this.form.submit()">
            <option value="">Anyone</option>
            @foreach ($staff as $member)
                <option value="{{ $member->id }}" @selected((int) ($filters['assigned_to'] ?? 0) === $member->id)>{{ $member->name }}</option>
            @endforeach
        </select>
        <select name="room_id" class="{{ $inputClass }}" onchange="this.form.submit()">
            <option value="">All rooms</option>
            @foreach ($rooms as $room)
                <option value="{{ $room->id }}" @selected((int) ($filters['room_id'] ?? 0) === $room->id)>Room {{ $room->room_number }}</option>
            @endforeach
        </select>
    </form>

    <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="custom-scrollbar overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr class="whitespace-nowrap border-b border-gray-100 text-gray-700 dark:border-gray-800 dark:text-gray-400">
                        <th class="px-5 py-3 font-medium">Task</th>
                        <th class="px-5 py-3 font-medium">Room</th>
                        <th class="px-5 py-3 font-medium">Assigned</th>
                        <th class="px-5 py-3 font-medium">Due</th>
                        <th class="px-5 py-3 font-medium">Checklist</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($tasks as $task)
                        @php [$done, $total] = $task->checklist_counts; @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-5 py-3">
                                <a href="{{ route('tasks.show', $task) }}" class="font-medium text-gray-800 hover:text-primary dark:text-white/90">{{ $task->title }}</a>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    <span class="{{ $task->priority_classes }}">{{ $task->priority_label }}</span> · {{ $task->taskType->name ?? '—' }}
                                </p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3">
                                @if ($task->room)
                                    <span class="text-gray-800 dark:text-white/90">{{ $task->room->room_number }}</span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $task->room->status->label() }}</p>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-gray-700 dark:text-gray-300">{{ $task->assignee->name ?? 'Not assigned' }}</td>
                            <td class="whitespace-nowrap px-5 py-3 {{ $task->is_overdue ? 'font-medium text-error-600 dark:text-error-400' : 'text-gray-700 dark:text-gray-300' }}">
                                {{ $task->due_date?->format('D d M, H:i') }}
                                @if ($task->is_overdue)<p class="text-xs">{{ $task->due_date->diffForHumans() }}</p>@endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-gray-700 dark:text-gray-300">{{ $total ? "{$done}/{$total}" : '—' }}</td>
                            <td class="px-5 py-3">@include('partials.status-badge', ['status' => $task->status])</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                                <i class="fas fa-tasks mb-3 text-4xl"></i>
                                <p>{{ $filters['view'] === 'open' ? 'Nothing to do. All caught up.' : 'No tasks here' }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($tasks->hasPages())
            <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">{{ $tasks->links() }}</div>
        @endif
    </div>
</div>

@include('tasks.partials.task-modal')
@if ($isManager)
    @include('tasks.partials.type-modal')
@endif
@endsection
