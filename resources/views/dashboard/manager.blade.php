<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 mb-6">
    <!-- Today's Revenue -->
    <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Today's Revenue</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">KSH {{ number_format($stats['today_revenue'] ?? 0) }}</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">From all sources</p>
            </div>
            <div class="p-3 rounded-full bg-primary/10">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Occupancy Rate -->
    <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Occupancy Rate</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($stats['occupancy_rate'] ?? 0, 1) }}%</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $stats['available_rooms'] ?? 0 }} rooms available</p>
            </div>
            <div class="p-3 rounded-full bg-success/10">
                <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Pending Tasks -->
    <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending Tasks</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['pending_tasks'] ?? 0 }}</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Across all departments</p>
            </div>
            <div class="p-3 rounded-full bg-warning/10">
                <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Low Stock Items -->
    <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Low Stock Items</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['low_stock_items'] ?? 0 }}</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Needs attention</p>
            </div>
            <div class="p-3 rounded-full bg-danger/10">
                <svg class="w-6 h-6 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.342 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-12 gap-6">
    <!-- Recent Bookings -->
    <div class="col-span-12 lg:col-span-7">
        <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Recent Bookings</h3>
                <a href="{{ route('stays.index') }}" class="text-sm text-primary hover:underline">View All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800">
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Guest</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Room</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Dates</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Amount</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse($recentBookings as $stay)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900 dark:text-white">{{ $stay->guest->name }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $stay->guest->email }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                @foreach($stay->roomAllocations as $allocation)
                                    {{ $allocation->room->room_number }}<br>
                                @endforeach
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                {{ $stay->arrival_date->format('M d') }} - {{ $stay->departure_date->format('M d') }}
                            </td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">
                                KSH {{ number_format($stay->total_amount) }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ 
                                    $stay->status === 'checked_in' ? 'bg-success/10 text-success' : 
                                    ($stay->status === 'booked' ? 'bg-primary/10 text-primary' : 'bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-300')
                                }}">
                                    {{ ucfirst(str_replace('_', ' ', $stay->status)) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                No recent bookings
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="col-span-12 lg:col-span-5 space-y-6">
        <!-- Department Performance -->
        <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Department Stats</h3>
            </div>
            <div class="space-y-4">
                @forelse($departmentStats as $dept)
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $dept['department'] }}</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $dept['staff_count'] }} staff</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-primary h-2 rounded-full" style="width: {{ min(($dept['tasks'] / 50) * 100, 100) }}%"></div>
                    </div>
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $dept['tasks'] }} active tasks</span>
                        <span class="text-xs font-medium text-primary">{{ number_format(($dept['tasks'] / 50) * 100, 0) }}%</span>
                    </div>
                </div>
                @empty
                <p class="text-gray-500 dark:text-gray-400 text-center py-4">No department data available</p>
                @endforelse
            </div>
        </div>

        <!-- Staff Performance -->
        <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Top Performers</h3>
            </div>
            <div class="space-y-3">
                @forelse($staffPerformance as $staff)
                <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $staff['name'] }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $staff['department'] }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-medium text-gray-900 dark:text-white">{{ $staff['tasks_completed'] }} tasks</p>
                        <div class="flex items-center">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-4 h-4 {{ $i <= floor($staff['rating']) ? 'text-warning' : 'text-gray-300 dark:text-gray-600' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            @endfor
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-gray-500 dark:text-gray-400 text-center py-4">No performance data available</p>
                @endforelse
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('reports.index') }}" class="p-4 rounded-lg border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 text-center">
                    <div class="p-2 rounded-full bg-primary/10 inline-block mb-2">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <p class="font-medium text-gray-900 dark:text-white">Reports</p>
                </a>
                <a href="{{ route('hr.staff.index') }}" class="p-4 rounded-lg border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 text-center">
                    <div class="p-2 rounded-full bg-success/10 inline-block mb-2">
                        <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <p class="font-medium text-gray-900 dark:text-white">Staff</p>
                </a>
                <a href="{{ route('inventory.index') }}" class="p-4 rounded-lg border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 text-center">
                    <div class="p-2 rounded-full bg-warning/10 inline-block mb-2">
                        <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                    </div>
                    <p class="font-medium text-gray-900 dark:text-white">Inventory</p>
                </a>
                <a href="{{ route('finance.dashboard') }}" class="p-4 rounded-lg border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 text-center">
                    <div class="p-2 rounded-full bg-info/10 inline-block mb-2">
                        <svg class="w-6 h-6 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="font-medium text-gray-900 dark:text-white">Finance</p>
                </a>
            </div>
        </div>
    </div>
</div>