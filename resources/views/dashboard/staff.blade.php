@extends('layouts.app')

@section('content')
    <div class="p-4 mx-auto max-w-screen-2xl md:p-6">
        <!-- Welcome Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">Welcome, {{ $user->name }}({{$user->role_id}})!</h1>
            <p class="text-gray-600 dark:text-gray-400">Here are your tasks and schedule for today.</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 gap-4 mb-6 sm:grid-cols-2 lg:grid-cols-3">
            <!-- Today's Tasks -->
            <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Today's Tasks</p>
                        <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $tasks->count() }}</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ $tasks->where('status', 'pending')->count() }} pending
                        </p>
                    </div>
                    <div class="p-3 rounded-full bg-primary/10">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Today's Shifts -->
            <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Today's Shifts</p>
                        <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $todayShifts->count() }}</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ $todayShifts->where('status', 'scheduled')->count() }} scheduled
                        </p>
                    </div>
                    <div class="p-3 rounded-full bg-success/10">
                        <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Unread Notifications -->
            <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Notifications</p>
                        <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $notifications->count() ?? 0 }}</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Unread messages</p>
                    </div>
                    <div class="p-3 rounded-full bg-warning/10">
                        <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-4 md:gap-6">
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
                                            <a href="{{ route('tasks.show', $task) }}" class="text-primary hover:underline text-sm">View</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8">
                            <p class="text-gray-500 dark:text-gray-400">No tasks assigned to you today.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Today's Schedule & Notifications -->
            <div class="col-span-12 lg:col-span-5 space-y-6">
                <!-- Today's Shifts -->
                <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Today's Schedule</h3>
                    <div class="space-y-3">
                        @forelse($todayShifts as $assignment)
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
                        @empty
                        <p class="text-gray-500 dark:text-gray-400 text-center py-4">No shifts scheduled for today.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Notifications -->
                <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Notifications</h3>
                        <button class="text-sm text-primary hover:underline">Mark all as read</button>
                    </div>
                    <div class="space-y-3">
                        @forelse($notifications as $notification)
                        <div class="p-3 rounded-lg border border-gray-200 dark:border-gray-800">
                            <div class="flex items-start">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $notification->title }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $notification->message }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        </div>
                        @empty
                        <p class="text-gray-500 dark:text-gray-400 text-center py-4">No new notifications.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection