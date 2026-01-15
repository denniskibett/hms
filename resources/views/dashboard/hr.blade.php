<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 mb-6">
    <!-- Total Staff -->
    <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Staff</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['total_staff'] ?? 0 }}</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $activeStaff }} active</p>
            </div>
            <div class="p-3 rounded-full bg-primary/10">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Pending Leave -->
    <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending Leave</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['pending_leave'] ?? 0 }}</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Awaiting approval</p>
            </div>
            <div class="p-3 rounded-full bg-warning/10">
                <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- On Leave Today -->
    <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">On Leave Today</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['on_leave_today'] ?? 0 }}</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Staff absent</p>
            </div>
            <div class="p-3 rounded-full bg-danger/10">
                <svg class="w-6 h-6 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                </svg>
            </div>
        </div>
    </div>

    <!-- New Hires This Month -->
    <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">New Hires</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['new_hires_this_month'] ?? 0 }}</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">This month</p>
            </div>
            <div class="p-3 rounded-full bg-success/10">
                <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-12 gap-6">
    <!-- Pending Leave Requests -->
    <div class="col-span-12 lg:col-span-7">
        <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Pending Leave Requests</h3>
                <a href="{{ route('hr.leave.index') }}" class="text-sm text-primary hover:underline">View All</a>
            </div>
            <div class="space-y-4">
                @forelse($pendingLeave as $request)
                <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-800">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-2">
                                <div>
                                    <h4 class="font-medium text-gray-900 dark:text-white">{{ $request->staff->name }}</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $request->staff->staffProfile->department->name ?? 'N/A' }}</p>
                                </div>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ 
                                    $request->type === 'annual' ? 'bg-primary/10 text-primary' : 
                                    ($request->type === 'sick' ? 'bg-danger/10 text-danger' : 'bg-warning/10 text-warning')
                                }}">
                                    {{ ucfirst($request->type) }} Leave
                                </span>
                            </div>
                            <div class="mb-3">
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $request->reason }}</p>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $request->start_date->format('M d') }} - {{ $request->end_date->format('M d') }}
                                    ({{ $request->duration }} days)
                                </div>
                                <div class="flex items-center space-x-2">
                                    <form action="{{ route('hr.leave-requests.approve', $request) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 text-sm bg-success text-white rounded hover:bg-success/90">
                                            Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('hr.leave-requests.reject', $request) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 text-sm bg-danger text-white rounded hover:bg-danger/90">
                                            Reject
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <p class="text-gray-500 dark:text-gray-400">No pending leave requests</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="col-span-12 lg:col-span-5 space-y-6">
        <!-- Upcoming Birthdays -->
        <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Upcoming Birthdays</h3>
            </div>
            <div class="space-y-3">
                @forelse($upcomingBirthdays as $staff)
                <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center mr-3">
                            <span class="text-primary font-medium">
                                {{ substr($staff->name, 0, 1) }}
                            </span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $staff->name }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $staff->staffProfile->department->name ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-primary/10 text-primary">
                        {{ $staff->date_of_birth ? $staff->date_of_birth->format('M d') : 'N/A' }}
                    </span>
                </div>
                @empty
                <p class="text-gray-500 dark:text-gray-400 text-center py-4">No upcoming birthdays</p>
                @endforelse
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('hr.staff.create') }}" class="p-4 rounded-lg border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 text-center">
                    <div class="p-2 rounded-full bg-primary/10 inline-block mb-2">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </div>
                    <p class="font-medium text-gray-900 dark:text-white">Add Staff</p>
                </a>
                <a href="{{ route('hr.payroll.index') }}" class="p-4 rounded-lg border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 text-center">
                    <div class="p-2 rounded-full bg-success/10 inline-block mb-2">
                        <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="font-medium text-gray-900 dark:text-white">Payroll</p>
                </a>
                <a href="{{ route('hr.attendance.index') }}" class="p-4 rounded-lg border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 text-center">
                    <div class="p-2 rounded-full bg-warning/10 inline-block mb-2">
                        <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="font-medium text-gray-900 dark:text-white">Attendance</p>
                </a>
                <a href="{{ route('hr.staff.index') }}" class="p-4 rounded-lg border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 text-center">
                    <div class="p-2 rounded-full bg-info/10 inline-block mb-2">
                        <svg class="w-6 h-6 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <p class="font-medium text-gray-900 dark:text-white">Staff Directory</p>
                </a>
            </div>
        </div>
    </div>
</div>