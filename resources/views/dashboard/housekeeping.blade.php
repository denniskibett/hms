<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 mb-6">
    <!-- Pending Tasks -->
    <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending Tasks</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['pending_tasks'] ?? 0 }}</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Assigned to you</p>
            </div>
            <div class="p-3 rounded-full bg-primary/10">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Rooms to Clean -->
    <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Rooms to Clean</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['rooms_to_clean'] ?? 0 }}</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Dirty rooms</p>
            </div>
            <div class="p-3 rounded-full bg-warning/10">
                <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.342 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Completed Today -->
    <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Completed Today</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['completed_today'] ?? 0 }}</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tasks completed</p>
            </div>
            <div class="p-3 rounded-full bg-success/10">
                <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Avg. Cleaning Time -->
    <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Avg. Cleaning Time</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['cleaning_time_avg'] ?? 'N/A' }}</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Per room</p>
            </div>
            <div class="p-3 rounded-full bg-info/10">
                <svg class="w-6 h-6 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-12 gap-6">
    <!-- My Tasks -->
    <div class="col-span-12 lg:col-span-7">
        <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">My Tasks</h3>
                <a href="{{ route('tasks.my-tasks') }}" class="text-sm text-primary hover:underline">View All</a>
            </div>
            <div class="space-y-4">
                @forelse($tasks as $task)
                <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-800">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-medium text-gray-900 dark:text-white">{{ $task->title }}</h4>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ 
                                    $task->priority === 'urgent' ? 'bg-danger/10 text-danger' : 
                                    ($task->priority === 'high' ? 'bg-warning/10 text-warning' : 'bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-300')
                                }}">
                                    {{ ucfirst($task->priority) }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ $task->description }}</p>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    @if($task->room)
                                    <span class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                        </svg>
                                        Room {{ $task->room->room_number }}
                                    </span>
                                    @endif
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        Due: {{ $task->due_date->format('M d, h:i A') }}
                                    </span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ 
                                        $task->status === 'completed' ? 'bg-success/10 text-success' : 
                                        ($task->status === 'in_progress' ? 'bg-primary/10 text-primary' : 'bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-300')
                                    }}">
                                        {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                    </span>
                                    <a href="{{ route('tasks.show', $task) }}" class="px-3 py-1 text-sm bg-primary text-white rounded hover:bg-primary/90">
                                        View
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <p class="text-gray-500 dark:text-gray-400">No tasks assigned to you.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Room Status & Schedule -->
    <div class="col-span-12 lg:col-span-5 space-y-6">
        <!-- Dirty Rooms -->
        <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Dirty Rooms</h3>
                <a href="{{ route('rooms.index', ['status' => 'dirty']) }}" class="text-sm text-primary hover:underline">View All</a>
            </div>
            <div class="space-y-3">
                @forelse($dirtyRooms as $room)
                <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">Room {{ $room->room_number }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $room->roomType->name }}</p>
                    </div>
                    <a href="{{ route('rooms.show', $room) }}" class="px-3 py-1 text-sm bg-primary text-white rounded hover:bg-primary/90">
                        Clean
                    </a>
                </div>
                @empty
                <p class="text-gray-500 dark:text-gray-400 text-center py-4">No dirty rooms</p>
                @endforelse
            </div>
        </div>

        <!-- Recently Cleaned -->
        <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Recently Cleaned</h3>
            </div>
            <div class="space-y-3">
                @forelse($recentlyCleaned as $room)
                <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">Room {{ $room->room_number }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $room->last_cleaned_at ? $room->last_cleaned_at->diffForHumans() : 'N/A' }}
                        </p>
                    </div>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-success/10 text-success">
                        Clean
                    </span>
                </div>
                @empty
                <p class="text-gray-500 dark:text-gray-400 text-center py-4">No recently cleaned rooms</p>
                @endforelse
            </div>
        </div>

        <!-- Today's Schedule -->
        @if($todayShifts->count() > 0)
        <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Today's Schedule</h3>
            <div class="space-y-3">
                @foreach($todayShifts as $assignment)
                <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $assignment->shift->name }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $assignment->shift->start_time->format('h:i A') }} - {{ $assignment->shift->end_time->format('h:i A') }}
                        </p>
                    </div>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ 
                        $assignment->status === 'completed' ? 'bg-success/10 text-success' : 'bg-primary/10 text-primary'
                    }}">
                        {{ ucfirst($assignment->status) }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>