<!-- Stats Cards - Now 6 Cards -->
<div class="grid grid-cols-1 gap-4 mb-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
    <!-- Today's Arrivals -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-theme-sm text-gray-500 dark:text-gray-400">Today's Arrivals</p>
                <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                    {{ $stats['today_arrivals'] ?? ($stats['today_checkins'] ?? 0) }}
                </p>
                @php
                    $arrivalsChange = isset($stats['arrivals_change']) ? $stats['arrivals_change'] : 0;
                @endphp
                <div class="mt-2 flex items-center gap-1">
                    @if($arrivalsChange > 0)
                    <span class="flex items-center gap-1 rounded-full bg-success-50 px-2 py-0.5 text-theme-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">
                        <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20">
                            <path d="M10 17.5L3.75 11.25L5.25 9.75L10 14.5L14.75 9.75L16.25 11.25L10 17.5Z"/>
                        </svg>
                        +{{ $arrivalsChange }}%
                    </span>
                    <span class="text-theme-xs text-gray-500 dark:text-gray-400">vs yesterday</span>
                    @elseif($arrivalsChange < 0)
                    <span class="flex items-center gap-1 rounded-full bg-error-50 px-2 py-0.5 text-theme-xs font-medium text-error-600 dark:bg-error-500/15 dark:text-error-500">
                        <svg class="w-3 h-3 fill-current rotate-180" viewBox="0 0 20 20">
                            <path d="M10 17.5L3.75 11.25L5.25 9.75L10 14.5L14.75 9.75L16.25 11.25L10 17.5Z"/>
                        </svg>
                        {{ $arrivalsChange }}%
                    </span>
                    <span class="text-theme-xs text-gray-500 dark:text-gray-400">vs yesterday</span>
                    @else
                    <span class="text-theme-xs text-gray-500 dark:text-gray-400">No change</span>
                    @endif
                </div>
            </div>
            <div class="rounded-full bg-primary-50 p-3 dark:bg-primary-500/10">
                <svg class="w-6 h-6 text-primary-600 dark:text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Today's Departures -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-theme-sm text-gray-500 dark:text-gray-400">Today's Departures</p>
                <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                    {{ $stats['today_departures'] ?? ($stats['today_checkouts'] ?? 0) }}
                </p>
                @php
                    $departuresChange = isset($stats['departures_change']) ? $stats['departures_change'] : 0;
                @endphp
                <div class="mt-2 flex items-center gap-1">
                    @if($departuresChange > 0)
                    <span class="flex items-center gap-1 rounded-full bg-success-50 px-2 py-0.5 text-theme-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">
                        <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20">
                            <path d="M10 17.5L3.75 11.25L5.25 9.75L10 14.5L14.75 9.75L16.25 11.25L10 17.5Z"/>
                        </svg>
                        +{{ $departuresChange }}%
                    </span>
                    <span class="text-theme-xs text-gray-500 dark:text-gray-400">vs yesterday</span>
                    @elseif($departuresChange < 0)
                    <span class="flex items-center gap-1 rounded-full bg-error-50 px-2 py-0.5 text-theme-xs font-medium text-error-600 dark:bg-error-500/15 dark:text-error-500">
                        <svg class="w-3 h-3 fill-current rotate-180" viewBox="0 0 20 20">
                            <path d="M10 17.5L3.75 11.25L5.25 9.75L10 14.5L14.75 9.75L16.25 11.25L10 17.5Z"/>
                        </svg>
                        {{ $departuresChange }}%
                    </span>
                    <span class="text-theme-xs text-gray-500 dark:text-gray-400">vs yesterday</span>
                    @else
                    <span class="text-theme-xs text-gray-500 dark:text-gray-400">No change</span>
                    @endif
                </div>
            </div>
            <div class="rounded-full bg-error-50 p-3 dark:bg-error-500/10">
                <svg class="w-6 h-6 text-error-600 dark:text-error-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Occupancy Rate -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-theme-sm text-gray-500 dark:text-gray-400">Occupancy Rate</p>
                <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                    {{ number_format($stats['occupancy_rate'] ?? 0, 1) }}%
                </p>
                @php
                    $targetRate = isset($stats['target_occupancy']) ? $stats['target_occupancy'] : 80;
                    $currentRate = $stats['occupancy_rate'] ?? 0;
                    $occupancyDiff = $currentRate - $targetRate;
                @endphp
                <div class="mt-2 flex items-center gap-1">
                    @if($occupancyDiff > 0)
                    <span class="flex items-center gap-1 rounded-full bg-success-50 px-2 py-0.5 text-theme-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">
                        <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20">
                            <path d="M10 17.5L3.75 11.25L5.25 9.75L10 14.5L14.75 9.75L16.25 11.25L10 17.5Z"/>
                        </svg>
                        +{{ number_format($occupancyDiff, 1) }}%
                    </span>
                    <span class="text-theme-xs text-gray-500 dark:text-gray-400">vs target</span>
                    @elseif($occupancyDiff < 0)
                    <span class="flex items-center gap-1 rounded-full bg-warning-50 px-2 py-0.5 text-theme-xs font-medium text-warning-600 dark:bg-warning-500/15 dark:text-warning-500">
                        <svg class="w-3 h-3 fill-current rotate-180" viewBox="0 0 20 20">
                            <path d="M10 17.5L3.75 11.25L5.25 9.75L10 14.5L14.75 9.75L16.25 11.25L10 17.5Z"/>
                        </svg>
                        {{ number_format($occupancyDiff, 1) }}%
                    </span>
                    <span class="text-theme-xs text-gray-500 dark:text-gray-400">vs target</span>
                    @else
                    <span class="text-theme-xs text-gray-500 dark:text-gray-400">On target</span>
                    @endif
                </div>
            </div>
            <div class="rounded-full bg-success-50 p-3 dark:bg-success-500/10">
                <svg class="w-6 h-6 text-success-600 dark:text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Today's Revenue -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-theme-sm text-gray-500 dark:text-gray-400">Today's Revenue</p>
                <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                    KSH {{ number_format($stats['today_revenue'] ?? ($stats['today_payments'] ?? 0)) }}
                </p>
                @php
                    $revenueTarget = isset($stats['revenue_target']) ? $stats['revenue_target'] : 0;
                    $todayRevenue = $stats['today_revenue'] ?? ($stats['today_payments'] ?? 0);
                    $revenuePercent = $revenueTarget > 0 ? ($todayRevenue / $revenueTarget) * 100 : 0;
                @endphp
                <div class="mt-2 flex items-center gap-1">
                    @if($revenuePercent >= 100)
                    <span class="flex items-center gap-1 rounded-full bg-success-50 px-2 py-0.5 text-theme-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">
                        <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20">
                            <path d="M10 17.5L3.75 11.25L5.25 9.75L10 14.5L14.75 9.75L16.25 11.25L10 17.5Z"/>
                        </svg>
                        {{ number_format($revenuePercent) }}%
                    </span>
                    <span class="text-theme-xs text-gray-500 dark:text-gray-400">of target</span>
                    @elseif($revenuePercent > 0)
                    <span class="flex items-center gap-1 rounded-full bg-warning-50 px-2 py-0.5 text-theme-xs font-medium text-warning-600 dark:bg-warning-500/15 dark:text-warning-500">
                        {{ number_format($revenuePercent) }}%
                    </span>
                    <span class="text-theme-xs text-gray-500 dark:text-gray-400">of target</span>
                    @else
                    <span class="text-theme-xs text-gray-500 dark:text-gray-400">No revenue yet</span>
                    @endif
                </div>
            </div>
            <div class="rounded-full bg-warning-50 p-3 dark:bg-warning-500/10">
                <svg class="w-6 h-6 text-warning-600 dark:text-warning-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Pending Tasks -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-theme-sm text-gray-500 dark:text-gray-400">Pending Tasks</p>
                <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                    {{ $stats['pending_tasks'] ?? 0 }}
                </p>
                @php
                    $tasksTrend = isset($stats['tasks_trend']) ? $stats['tasks_trend'] : 0;
                @endphp
                <div class="mt-2 flex items-center gap-1">
                    @if($tasksTrend > 0)
                    <span class="flex items-center gap-1 rounded-full bg-error-50 px-2 py-0.5 text-theme-xs font-medium text-error-600 dark:bg-error-500/15 dark:text-error-500">
                        <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20">
                            <path d="M10 2.5L8.75 9.25L2.5 10L9.25 11.25L10 17.5L10.75 11.25L17.5 10L11.25 8.75L10 2.5Z"/>
                        </svg>
                        +{{ $tasksTrend }}
                    </span>
                    <span class="text-theme-xs text-gray-500 dark:text-gray-400">new today</span>
                    @elseif($tasksTrend < 0)
                    <span class="flex items-center gap-1 rounded-full bg-success-50 px-2 py-0.5 text-theme-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">
                        <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20">
                            <path d="M10 17.5L3.75 11.25L5.25 9.75L10 14.5L14.75 9.75L16.25 11.25L10 17.5Z"/>
                        </svg>
                        {{ $tasksTrend }}
                    </span>
                    <span class="text-theme-xs text-gray-500 dark:text-gray-400">reduced</span>
                    @else
                    <span class="text-theme-xs text-gray-500 dark:text-gray-400">No change</span>
                    @endif
                </div>
            </div>
            <div class="rounded-full bg-gray-50 p-3 dark:bg-gray-800">
                <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Low Stock Items -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-theme-sm text-gray-500 dark:text-gray-400">Low Stock Items</p>
                <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                    {{ $stats['low_stock_items'] ?? 0 }}
                </p>
                @php
                    $stockStatus = $stats['low_stock_items'] ?? 0;
                @endphp
                <div class="mt-2 flex items-center gap-1">
                    @if($stockStatus > 10)
                    <span class="flex items-center gap-1 rounded-full bg-error-50 px-2 py-0.5 text-theme-xs font-medium text-error-600 dark:bg-error-500/15 dark:text-error-500">
                        Critical
                    </span>
                    @elseif($stockStatus > 5)
                    <span class="flex items-center gap-1 rounded-full bg-warning-50 px-2 py-0.5 text-theme-xs font-medium text-warning-600 dark:bg-warning-500/15 dark:text-warning-500">
                        High
                    </span>
                    @elseif($stockStatus > 0)
                    <span class="flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-theme-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                        Moderate
                    </span>
                    @else
                    <span class="rounded-full bg-success-50 px-2 py-0.5 text-theme-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">
                        All Good
                    </span>
                    @endif
                </div>
            </div>
            <div class="rounded-full bg-info-50 p-3 dark:bg-info-500/10">
                <svg class="w-6 h-6 text-info-600 dark:text-info-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Kitchen Orders Card -->
<div class="mb-6">
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Kitchen Orders Status</h3>
                <p class="text-theme-sm text-gray-500 dark:text-gray-400">Current kitchen operations overview</p>
            </div>
            <a href="{{ route('kitchen.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                View Kitchen
            </a>
        </div>
        
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
            <!-- Pending Orders -->
            <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-theme-sm text-gray-500 dark:text-gray-400">Pending</p>
                        <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">
                            {{ $pendingOrders['pending'] ?? (isset($stats['pending_orders']) ? $stats['pending_orders'] : 0) }}
                        </p>
                    </div>
                    <div class="rounded-full bg-warning-50 p-2 dark:bg-warning-500/10">
                        <svg class="w-5 h-5 text-warning-600 dark:text-warning-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                    @php
                        $pendingPercent = isset($pendingOrders['pending']) && isset($stats['total_orders']) && $stats['total_orders'] > 0 
                            ? ($pendingOrders['pending'] / $stats['total_orders']) * 100 
                            : 0;
                    @endphp
                    <div class="h-full rounded-full bg-warning-500" style="width: {{ $pendingPercent }}%"></div>
                </div>
            </div>
            
            <!-- Preparing Orders -->
            <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-theme-sm text-gray-500 dark:text-gray-400">Preparing</p>
                        <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">
                            {{ $pendingOrders['preparing'] ?? (isset($stats['preparing_orders']) ? $stats['preparing_orders'] : 0) }}
                        </p>
                    </div>
                    <div class="rounded-full bg-primary-50 p-2 dark:bg-primary-500/10">
                        <svg class="w-5 h-5 text-primary-600 dark:text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                    @php
                        $preparingPercent = isset($pendingOrders['preparing']) && isset($stats['total_orders']) && $stats['total_orders'] > 0 
                            ? ($pendingOrders['preparing'] / $stats['total_orders']) * 100 
                            : 0;
                    @endphp
                    <div class="h-full rounded-full bg-primary-500" style="width: {{ $preparingPercent }}%"></div>
                </div>
            </div>
            
            <!-- Ready Orders -->
            <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-theme-sm text-gray-500 dark:text-gray-400">Ready</p>
                        <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">
                            {{ $pendingOrders['ready'] ?? (isset($stats['ready_orders']) ? $stats['ready_orders'] : 0) }}
                        </p>
                    </div>
                    <div class="rounded-full bg-success-50 p-2 dark:bg-success-500/10">
                        <svg class="w-5 h-5 text-success-600 dark:text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                    @php
                        $readyPercent = isset($pendingOrders['ready']) && isset($stats['total_orders']) && $stats['total_orders'] > 0 
                            ? ($pendingOrders['ready'] / $stats['total_orders']) * 100 
                            : 0;
                    @endphp
                    <div class="h-full rounded-full bg-success-500" style="width: {{ $readyPercent }}%"></div>
                </div>
            </div>
            
            <!-- Completed Today -->
            <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-theme-sm text-gray-500 dark:text-gray-400">Completed Today</p>
                        <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">
                            {{ $pendingOrders['completed'] ?? (isset($stats['completed_today']) ? $stats['completed_today'] : 0) }}
                        </p>
                    </div>
                    <div class="rounded-full bg-info-50 p-2 dark:bg-info-500/10">
                        <svg class="w-5 h-5 text-info-600 dark:text-info-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                    @php
                        $completedPercent = isset($pendingOrders['completed']) && isset($pendingOrders['total_today']) && $pendingOrders['total_today'] > 0 
                            ? ($pendingOrders['completed'] / $pendingOrders['total_today']) * 100 
                            : 0;
                    @endphp
                    <div class="h-full rounded-full bg-info-500" style="width: {{ $completedPercent }}%"></div>
                </div>
            </div>
        </div>
        
        @if(isset($pendingOrders['total_today']) && $pendingOrders['total_today'] > 0)
        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-800">
            <div class="flex items-center justify-between">
                <p class="text-theme-sm text-gray-500 dark:text-gray-400">Total orders today</p>
                <p class="text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $pendingOrders['total_today'] ?? 0 }}</p>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Main Content Grid -->
<div class="grid grid-cols-12 gap-4 md:gap-6">
    <!-- Recent Bookings -->
    <div class="col-span-12 lg:col-span-7">
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-4 pb-3 pt-4 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6">
            <div class="flex flex-col gap-2 mb-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Recent Bookings</h3>
                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Latest guest bookings and check-ins</p>
                </div>
                
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <!-- Search Bar -->
                    <div class="relative">
                        <input
                            type="text"
                            placeholder="Search bookings..."
                            class="w-full sm:w-64 rounded-lg border border-gray-300 bg-white py-2.5 pl-11 pr-4 text-theme-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-500 focus:border-primary focus:ring-2 focus:ring-primary/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-400 dark:focus:border-primary"
                        />
                        <svg
                            class="absolute left-4 top-1/2 -translate-y-1/2 stroke-current"
                            width="20"
                            height="20"
                            viewBox="0 0 20 20"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M9.16667 15.8333C12.8486 15.8333 15.8333 12.8486 15.8333 9.16667C15.8333 5.48477 12.8486 2.5 9.16667 2.5C5.48477 2.5 2.5 5.48477 2.5 9.16667C2.5 12.8486 5.48477 15.8333 9.16667 15.8333Z"
                                stroke=""
                                stroke-width="1.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                            <path
                                d="M17.5 17.5L13.875 13.875"
                                stroke=""
                                stroke-width="1.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </div>
                    
                    <!-- Filter Button -->
                    <button
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
                    >
                        <svg
                            class="stroke-current fill-white dark:fill-gray-800"
                            width="20"
                            height="20"
                            viewBox="0 0 20 20"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M2.29004 5.90393H17.7067"
                                stroke=""
                                stroke-width="1.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                            <path
                                d="M17.7075 14.0961H2.29085"
                                stroke=""
                                stroke-width="1.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                            <path
                                d="M12.0826 3.33331C13.5024 3.33331 14.6534 4.48431 14.6534 5.90414C14.6534 7.32398 13.5024 8.47498 12.0826 8.47498C10.6627 8.47498 9.51172 7.32398 9.51172 5.90415C9.51172 4.48432 10.6627 3.33331 12.0826 3.33331Z"
                                fill=""
                                stroke=""
                                stroke-width="1.5"
                            />
                            <path
                                d="M7.91745 11.525C6.49762 11.525 5.34662 12.676 5.34662 14.0959C5.34661 15.5157 6.49762 16.6667 7.91745 16.6667C9.33728 16.6667 10.4883 15.5157 10.4883 14.0959C10.4883 12.676 9.33728 11.525 7.91745 11.525Z"
                                fill=""
                                stroke=""
                                stroke-width="1.5"
                            />
                        </svg>
                        Filter
                    </button>
                    
                    <!-- View All Button -->
                    <a href="{{ route('stays.index') }}" 
                       class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                        View All
                    </a>
                </div>
            </div>
            
            <!-- Table -->
            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-gray-100 border-y dark:border-gray-800">
                            <th class="py-3">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Guest</p>
                            </th>
                            <th class="py-3">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Room</p>
                            </th>
                            <th class="py-3">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Dates</p>
                            </th>
                            <th class="py-3">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Status</p>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($recentBookings as $stay)
                        <tr>
                            <td class="py-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                                        @if($stay->guest->profile_picture)
                                        <img src="{{ asset('storage/' . $stay->guest->profile_picture) }}" alt="{{ $stay->guest->name }}" class="h-full w-full object-cover">
                                        @else
                                        <div class="flex h-full w-full items-center justify-center bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-500">
                                            {{ strtoupper(substr($stay->guest->name, 0, 1)) }}
                                        </div>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">
                                            {{ $stay->guest->name ?? 'N/A' }}
                                        </p>
                                        <p class="text-gray-500 text-theme-xs dark:text-gray-400">
                                            {{ $stay->guest->email ?? 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3">
                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                    @forelse($stay->roomAllocations as $allocation)
                                        <span class="rounded-full bg-gray-100 px-2 py-1 text-theme-xs dark:bg-gray-800">
                                            {{ $allocation->room->room_number ?? 'N/A' }}
                                        </span>
                                    @empty
                                        <span class="text-gray-400 text-theme-xs">No room assigned</span>
                                    @endforelse
                                </p>
                            </td>
                            <td class="py-3">
                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                    @if($stay->arrival_date && $stay->departure_date)
                                        {{ $stay->arrival_date->format('M d') }} - {{ $stay->departure_date->format('M d') }}
                                    @else
                                        N/A
                                    @endif
                                </p>
                            </td>
                            <td class="py-3">
                                @if($stay->status === 'checked_in')
                                <span class="rounded-full bg-success-50 px-2 py-0.5 text-theme-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">
                                    Checked In
                                </span>
                                @elseif($stay->status === 'confirmed' || $stay->status === 'booked')
                                <span class="rounded-full bg-primary-50 px-2 py-0.5 text-theme-xs font-medium text-primary-600 dark:bg-primary-500/15 dark:text-primary-500">
                                    {{ ucfirst(str_replace('_', ' ', $stay->status)) }}
                                </span>
                                @else
                                <span class="rounded-full bg-warning-50 px-2 py-0.5 text-theme-xs font-medium text-warning-600 dark:bg-warning-500/15 dark:text-warning-500">
                                    {{ ucfirst(str_replace('_', ' ', $stay->status ?? 'unknown')) }}
                                </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                    <p class="mt-2 text-gray-500 dark:text-gray-400">No recent bookings found</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="flex flex-col items-center justify-between gap-4 border-t border-gray-100 px-4 py-4 dark:border-gray-800 sm:flex-row sm:px-6">
                <p class="text-theme-sm text-gray-500 dark:text-gray-400">
                    Showing <span class="font-medium text-gray-800 dark:text-white/90">1</span> to 
                    <span class="font-medium text-gray-800 dark:text-white/90">10</span> of 
                    <span class="font-medium text-gray-800 dark:text-white/90">100</span> results
                </p>
                
                <div class="flex items-center gap-2">
                    <!-- Previous Button -->
                    <button
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
                    >
                        <svg
                            class="h-4 w-4 stroke-current"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                            fill="none"
                        >
                            <path
                                d="M12.5 15L7.5 10L12.5 5"
                                stroke=""
                                stroke-width="1.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                        Previous
                    </button>
                    
                    <!-- Page Numbers -->
                    <div class="hidden items-center gap-2 sm:flex">
                        <button
                            class="inline-flex items-center justify-center rounded-lg border border-primary bg-primary px-3 py-2 text-theme-sm font-medium text-white shadow-theme-xs dark:border-primary dark:bg-primary"
                        >
                            1
                        </button>
                        <button
                            class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
                        >
                            2
                        </button>
                        <button
                            class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
                        >
                            3
                        </button>
                        <span class="px-2 text-theme-sm text-gray-500 dark:text-gray-400">...</span>
                        <button
                            class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
                        >
                            10
                        </button>
                    </div>
                    
                    <!-- Next Button -->
                    <button
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
                    >
                        Next
                        <svg
                            class="h-4 w-4 stroke-current"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                            fill="none"
                        >
                            <path
                                d="M7.5 15L12.5 10L7.5 5"
                                stroke=""
                                stroke-width="1.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

<!-- Tasks Section -->
<div class="col-span-12 lg:col-span-5">
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-4 pb-3 pt-4 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6">
        <div class="flex flex-col gap-2 mb-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">My Tasks</h3>
                <p class="text-theme-sm text-gray-500 dark:text-gray-400">Upcoming tasks assigned to you</p>
            </div>
            
            <div class="flex items-center gap-2">
                <a href="{{ route('tasks.index') }}" 
                   class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                    View All
                </a>
                
                <!-- Tasks Dropdown -->
                <div x-data="{openDropDown: false}" class="relative">
                    <button
                        @click="openDropDown = !openDropDown"
                        :class="openDropDown ? 'text-gray-700 dark:text-white' : 'text-gray-400 hover:text-gray-700 dark:hover:text-white'"
                        class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white p-2 shadow-theme-xs dark:border-gray-700 dark:bg-gray-800"
                    >
                        <svg
                            class="fill-current h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                fill-rule="evenodd"
                                clip-rule="evenodd"
                                d="M10.2441 6C10.2441 5.0335 11.0276 4.25 11.9941 4.25H12.0041C12.9706 4.25 13.7541 5.0335 13.7541 6C13.7541 6.9665 12.9706 7.75 12.0041 7.75H11.9941C11.0276 7.75 10.2441 6.9665 10.2441 6ZM10.2441 18C10.2441 17.0335 11.0276 16.25 11.9941 16.25H12.0041C12.9706 16.25 13.7541 17.0335 13.7541 18C13.7541 18.9665 12.9706 19.75 12.0041 19.75H11.9941C11.0276 19.75 10.2441 18.9665 10.2441 18ZM11.9941 10.25C11.0276 10.25 10.2441 11.0335 10.2441 12C10.2441 12.9665 11.0276 13.75 11.9941 13.75H12.0041C12.9706 13.75 13.7541 12.9665 13.7541 12C13.7541 11.0335 12.9706 10.25 12.0041 10.25H11.9941Z"
                                fill=""
                            />
                        </svg>
                    </button>
                    <div
                        x-show="openDropDown"
                        @click.outside="openDropDown = false"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-1"
                        class="absolute right-0 top-full z-40 mt-2 w-48 space-y-1 rounded-2xl border border-gray-200 bg-white p-2 shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark"
                    >
                        <button
                            @click="filterTasks('all')"
                            class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-theme-xs font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            All Tasks
                        </button>
                        <button
                            @click="filterTasks('pending')"
                            class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-theme-xs font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Pending
                        </button>
                        <button
                            @click="filterTasks('in_progress')"
                            class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-theme-xs font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            In Progress
                        </button>
                        <button
                            @click="filterTasks('overdue')"
                            class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-theme-xs font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Overdue
                        </button>
                        <hr class="border-gray-200 dark:border-gray-800">
                        <button
                            @click="createNewTask()"
                            class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-theme-xs font-medium text-primary-600 hover:bg-gray-100 dark:text-primary-500 dark:hover:bg-white/5"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            New Task
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tasks List -->
        <div class="space-y-3" id="tasks-list">
            @forelse($tasks as $task)
            <div 
                x-data="{checked: {{ $task->status === 'completed' ? 'true' : 'false' }} }"
                data-task-id="{{ $task->id }}"
                data-task-status="{{ $task->status }}"
                data-due-date="{{ $task->due_date ? $task->due_date->toISOString() : '' }}"
                data-priority="{{ $task->priority }}"
                class="flex cursor-pointer items-center gap-4 rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-800 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150"
                @click="toggleTaskStatus()"
            >
                <!-- Checkbox -->
                <div class="flex items-start gap-3">
                    <div
                        class="flex h-5 w-5 items-center justify-center rounded-md border-[1.25px] transition-colors duration-200"
                        :class="checked 
                            ? 'border-primary-500 dark:border-primary-500 bg-primary-500' 
                            : 'bg-white dark:bg-white/0 border-gray-300 dark:border-gray-700'"
                    >
                        <svg
                            :class="checked ? 'block' : 'hidden'"
                            width="14"
                            height="14"
                            viewBox="0 0 14 14"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                            class="text-white"
                        >
                            <path
                                d="M11.6668 3.5L5.25016 9.91667L2.3335 7"
                                stroke="currentColor"
                                stroke-width="1.94437"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </div>
                </div>
                
                <!-- Task Details -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1">
                        <h4 class="text-theme-sm font-medium text-gray-800 dark:text-white/90 truncate">
                            {{ $task->title }}
                        </h4>
                        <!-- Priority Badge -->
                        <span class="ml-2 shrink-0">
                            @if($task->priority === 'urgent')
                            <span class="rounded-full bg-error-50 px-2 py-0.5 text-theme-xs font-medium text-error-600 dark:bg-error-500/15 dark:text-error-500">
                                Urgent
                            </span>
                            @elseif($task->priority === 'high')
                            <span class="rounded-full bg-warning-50 px-2 py-0.5 text-theme-xs font-medium text-warning-600 dark:bg-warning-500/15 dark:text-warning-500">
                                High
                            </span>
                            @elseif($task->priority === 'medium')
                            <span class="rounded-full bg-primary-50 px-2 py-0.5 text-theme-xs font-medium text-primary-600 dark:bg-primary-500/15 dark:text-primary-500">
                                Medium
                            </span>
                            @else
                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-theme-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                Low
                            </span>
                            @endif
                        </span>
                    </div>
                    
                    <!-- Task Info -->
                    <div class="flex flex-wrap items-center gap-3 text-theme-xs text-gray-500 dark:text-gray-400">
                        <!-- Due Date -->
                        <div class="flex items-center gap-1">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>
                                @if($task->due_date)
                                    {{ $task->due_date->isToday() ? 'Today' : $task->due_date->format('M d') }}
                                    @if($task->due_date->isToday())
                                        • {{ $task->due_date->format('h:i A') }}
                                    @endif
                                @else
                                    No due date
                                @endif
                            </span>
                        </div>
                        
                        <!-- Task Type -->
                        @if($task->taskType)
                        <div class="flex items-center gap-1">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <span>{{ $task->taskType->name ?? 'Task' }}</span>
                        </div>
                        @endif
                        
                        <!-- Room/Stay Info -->
                        @if($task->room)
                        <div class="flex items-center gap-1">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <span>Room {{ $task->room->room_number }}</span>
                        </div>
                        @endif
                    </div>
                    
                    <!-- Description -->
                    @if($task->description)
                    <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400 line-clamp-1">
                        {{ $task->description }}
                    </p>
                    @endif
                </div>
                
                <!-- Action Menu -->
                <div class="relative" x-data="{openTaskMenu: false}">
                    <button
                        @click.stop="openTaskMenu = !openTaskMenu"
                        :class="openTaskMenu ? 'text-gray-700 dark:text-white' : 'text-gray-400 hover:text-gray-700 dark:hover:text-white'"
                        class="rounded-lg p-1 hover:bg-gray-200 dark:hover:bg-gray-700"
                    >
                        <svg
                            class="h-5 w-5 fill-current"
                            viewBox="0 0 24 24"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                fill-rule="evenodd"
                                clip-rule="evenodd"
                                d="M10.2441 6C10.2441 5.0335 11.0276 4.25 11.9941 4.25H12.0041C12.9706 4.25 13.7541 5.0335 13.7541 6C13.7541 6.9665 12.9706 7.75 12.0041 7.75H11.9941C11.0276 7.75 10.2441 6.9665 10.2441 6ZM10.2441 18C10.2441 17.0335 11.0276 16.25 11.9941 16.25H12.0041C12.9706 16.25 13.7541 17.0335 13.7541 18C13.7541 18.9665 12.9706 19.75 12.0041 19.75H11.9941C11.0276 19.75 10.2441 18.9665 10.2441 18ZM11.9941 10.25C11.0276 10.25 10.2441 11.0335 10.2441 12C10.2441 12.9665 11.0276 13.75 11.9941 13.75H12.0041C12.9706 13.75 13.7541 12.9665 13.7541 12C13.7541 11.0335 12.9706 10.25 12.0041 10.25H11.9941Z"
                                fill=""
                            />
                        </svg>
                    </button>
                    <div
                        x-show="openTaskMenu"
                        @click.outside="openTaskMenu = false"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-1"
                        class="absolute right-0 top-full z-40 mt-2 w-40 space-y-1 rounded-2xl border border-gray-200 bg-white p-2 shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark"
                    >
                        <button
                            @click="openTaskMenu = false; editTask('{{ $task->id }}')"
                            class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-theme-xs font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit
                        </button>
                        <button
                            @click="openTaskMenu = false; viewTaskDetails('{{ $task->id }}')"
                            class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-theme-xs font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            View Details
                        </button>
                        <button
                            @click="openTaskMenu = false; assignTaskToSelf('{{ $task->id }}')"
                            class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-theme-xs font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                            Assign to Me
                        </button>
                        <hr class="border-gray-200 dark:border-gray-800">
                        <button
                            @click="openTaskMenu = false; deleteTask('{{ $task->id }}')"
                            class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-theme-xs font-medium text-error-600 hover:bg-error-50 hover:text-error-700 dark:text-error-500 dark:hover:bg-error-500/10"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Delete
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <!-- Empty State -->
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-6 text-center dark:border-gray-800 dark:bg-gray-800">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <p class="mt-2 text-gray-500 dark:text-gray-400">No tasks assigned to you</p>
                <button 
                    onclick="createNewTask()"
                    class="mt-3 inline-flex items-center gap-2 rounded-lg border border-primary bg-primary px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs hover:bg-primary-600 dark:border-primary dark:bg-primary"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Create Your First Task
                </button>
            </div>
            @endforelse
        </div>
        
        <!-- Task Statistics -->
        @if($tasks->isNotEmpty())
        <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-800">
            <div class="grid grid-cols-3 gap-4">
                <div class="text-center">
                    <p class="text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ $tasks->where('status', 'pending')->count() }}
                    </p>
                    <p class="text-theme-xs text-gray-500 dark:text-gray-400">Pending</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ $tasks->where('status', 'in_progress')->count() }}
                    </p>
                    <p class="text-theme-xs text-gray-500 dark:text-gray-400">In Progress</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ $tasks->where('due_date', '<', now())->whereIn('status', ['pending', 'in_progress'])->count() }}
                    </p>
                    <p class="text-theme-xs text-gray-500 dark:text-gray-400">Overdue</p>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>


    <!-- Recent Payments -->
    <div class="col-span-12">
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-4 pb-3 pt-4 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6">
            <div class="flex flex-col gap-2 mb-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Recent Payments</h3>
                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Latest payment transactions</p>
                </div>
                
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <!-- Date Filter -->
                    <select
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs focus:border-primary focus:ring-2 focus:ring-primary/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:focus:border-primary"
                    >
                        <option>Last 7 days</option>
                        <option>Last 30 days</option>
                        <option>This month</option>
                        <option>Last month</option>
                    </select>
                    
                    <!-- Status Filter -->
                    <select
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs focus:border-primary focus:ring-2 focus:ring-primary/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:focus:border-primary"
                    >
                        <option>All Methods</option>
                        <option>Cash</option>
                        <option>Card</option>
                        <option>Mobile Money</option>
                    </select>
                    
                    <a href="{{ route('finance.index') }}" 
                       class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                        View All
                    </a>
                </div>
            </div>
            
            <!-- Table -->
            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-gray-100 border-y dark:border-gray-800">
                            <th class="py-3">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Invoice</p>
                            </th>
                            <th class="py-3">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Guest</p>
                            </th>
                            <th class="py-3">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Amount</p>
                            </th>
                            <th class="py-3">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Method</p>
                            </th>
                            <th class="py-3">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Date</p>
                            </th>
                            <th class="py-3">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Actions</p>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($recentPayments as $payment)
                        <tr>
                            <td class="py-3">
                                @if($payment->invoice)
                                <a href="{{ route('finance.invoices.show', $payment->invoice_id) }}" 
                                   class="font-medium text-primary-600 text-theme-sm hover:underline dark:text-primary-500">
                                    {{ $payment->invoice->invoice_number ?? 'N/A' }}
                                </a>
                                @else
                                <span class="text-gray-400 text-theme-sm">No invoice</span>
                                @endif
                            </td>
                            <td class="py-3">
                                <div class="flex items-center gap-2">
                                    <div class="h-8 w-8 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                                        @if($payment->invoice && $payment->invoice->stay && $payment->invoice->stay->guest)
                                            @if($payment->invoice->stay->guest->profile_picture)
                                            <img src="{{ asset('storage/' . $payment->invoice->stay->guest->profile_picture) }}" 
                                                 alt="{{ $payment->invoice->stay->guest->name }}" 
                                                 class="h-full w-full object-cover">
                                            @else
                                            <div class="flex h-full w-full items-center justify-center bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                                {{ strtoupper(substr($payment->invoice->stay->guest->name, 0, 1)) }}
                                            </div>
                                            @endif
                                        @else
                                        <div class="flex h-full w-full items-center justify-center bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                            ?
                                        </div>
                                        @endif
                                    </div>
                                    <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                        @if($payment->invoice && $payment->invoice->stay && $payment->invoice->stay->guest)
                                            {{ $payment->invoice->stay->guest->name }}
                                        @else
                                            N/A
                                        @endif
                                    </p>
                                </div>
                            </td>
                            <td class="py-3">
                                <p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">
                                    KSH {{ number_format($payment->amount ?? 0) }}
                                </p>
                            </td>
                            <td class="py-3">
                                @php
                                    $method = $payment->method ?? 'unknown';
                                @endphp
                                @if($method === 'cash')
                                <span class="rounded-full bg-success-50 px-2 py-0.5 text-theme-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">
                                    Cash
                                </span>
                                @elseif($method === 'card')
                                <span class="rounded-full bg-primary-50 px-2 py-0.5 text-theme-xs font-medium text-primary-600 dark:bg-primary-500/15 dark:text-primary-500">
                                    Card
                                </span>
                                @else
                                <span class="rounded-full bg-warning-50 px-2 py-0.5 text-theme-xs font-medium text-warning-600 dark:bg-warning-500/15 dark:text-warning-500">
                                    {{ ucfirst(str_replace('_', ' ', $method)) }}
                                </span>
                                @endif
                            </td>
                            <td class="py-3">
                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                    @if($payment->created_at)
                                        {{ $payment->created_at->format('M d, h:i A') }}
                                    @else
                                        N/A
                                    @endif
                                </p>
                            </td>
                            <td class="py-3">
                                <div class="flex items-center gap-2">
                                    @if($payment->invoice)
                                    <a href="{{ route('finance.invoices.show', $payment->invoice_id) }}"
                                        class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white p-2 text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
                                        title="View Details">
                                        <svg class="h-4 w-4 stroke-current" viewBox="0 0 20 20" fill="none">
                                            <path d="M10 12.5C11.3807 12.5 12.5 11.3807 12.5 10C12.5 8.61929 11.3807 7.5 10 7.5C8.61929 7.5 7.5 8.61929 7.5 10C7.5 11.3807 8.61929 12.5 10 12.5Z" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M17.5 10C15.5 13.75 12.5 15.8333 10 15.8333C7.5 15.8333 4.5 13.75 2.5 10C4.5 6.25 7.5 4.16667 10 4.16667C12.5 4.16667 15.5 6.25 17.5 10Z" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </a>
                                    @endif
                                    <button
                                        class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white p-2 text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
                                        title="Print Receipt"
                                        onclick="printReceipt('{{ $payment->id }}')">
                                        <svg class="h-4 w-4 stroke-current" viewBox="0 0 20 20" fill="none">
                                            <path d="M5 15V18.3333H15V15" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M5 10H15V15H5V10Z" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M15 5H5V10H15V5Z" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M15 1.66667H5V5H15V1.66667Z" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="mt-2 text-gray-500 dark:text-gray-400">No recent payments found</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="flex flex-col items-center justify-between gap-4 border-t border-gray-100 px-4 py-4 dark:border-gray-800 sm:flex-row sm:px-6">
                <div class="flex items-center gap-3">
                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Show:</p>
                    <select
                        class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs focus:border-primary focus:ring-2 focus:ring-primary/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:focus:border-primary"
                    >
                        <option>10</option>
                        <option>25</option>
                        <option>50</option>
                        <option>100</option>
                    </select>
                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">entries</p>
                </div>
                
                <div class="flex items-center gap-2">
                    <!-- Previous Button -->
                    <button
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
                    >
                        <svg
                            class="h-4 w-4 stroke-current"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                            fill="none"
                        >
                            <path
                                d="M12.5 15L7.5 10L12.5 5"
                                stroke=""
                                stroke-width="1.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                        Previous
                    </button>
                    
                    <!-- Page Numbers -->
                    <div class="flex items-center gap-1">
                        <button
                            class="inline-flex items-center justify-center rounded-lg border border-primary bg-primary px-3 py-2 text-theme-sm font-medium text-white shadow-theme-xs dark:border-primary dark:bg-primary"
                        >
                            1
                        </button>
                        <button
                            class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
                        >
                            2
                        </button>
                        <button
                            class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
                        >
                            3
                        </button>
                        <span class="px-2 text-theme-sm text-gray-500 dark:text-gray-400">...</span>
                        <button
                            class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
                        >
                            10
                        </button>
                    </div>
                    
                    <!-- Next Button -->
                    <button
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
                    >
                        Next
                        <svg
                            class="h-4 w-4 stroke-current"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                            fill="none"
                        >
                            <path
                                d="M7.5 15L12.5 10L7.5 5"
                                stroke=""
                                stroke-width="1.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ==================== GLOBAL FUNCTIONS ====================

// Print Receipt Function
function printReceipt(paymentId) {
    // Show loading state
    const printBtn = event.target.closest('button');
    const originalContent = printBtn.innerHTML;
    printBtn.innerHTML = '<div class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></div>';
    printBtn.disabled = true;
    
    // AJAX call to generate/print receipt
    fetch(`/api/payments/${paymentId}/receipt`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Open receipt in new window for printing
            const receiptWindow = window.open(data.receipt_url || `/payments/${paymentId}/receipt`, '_blank');
            
            if (receiptWindow) {
                receiptWindow.focus();
                showNotification('Receipt generated successfully!', 'success');
            }
        } else {
            showNotification(data.message || 'Error generating receipt', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error generating receipt. Please try again.', 'error');
    })
    .finally(() => {
        // Restore button state
        printBtn.innerHTML = originalContent;
        printBtn.disabled = false;
    });
}

// Show Notification
function showNotification(message, type = 'info') {
    // Remove existing notifications
    document.querySelectorAll('.dashboard-notification').forEach(n => n.remove());
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `dashboard-notification fixed top-4 right-4 z-50 rounded-lg px-4 py-3 shadow-lg transform transition-all duration-300 translate-x-full opacity-0 ${
        type === 'success' ? 'bg-success-100 text-success-800 border border-success-200' :
        type === 'error' ? 'bg-error-100 text-error-800 border border-error-200' :
        'bg-info-100 text-info-800 border border-info-200'
    }`;
    notification.innerHTML = `
        <div class="flex items-center gap-2">
            ${type === 'success' ? '✓' : type === 'error' ? '✕' : 'ℹ'}
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full', 'opacity-0');
        notification.classList.add('translate-x-0', 'opacity-100');
    }, 10);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Format Number with Commas
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// ==================== DASHBOARD INITIALIZATION ====================

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all dashboard components
    initializeSearch();
    initializeFilters();
    initializePagination();
    initializeTaskFunctions();
    initializeStatsRefresh();
    initializeDataTables();
});

// ==================== SEARCH FUNCTIONALITY ====================

function initializeSearch() {
    const searchInputs = document.querySelectorAll('input[type="text"][placeholder*="Search"]');
    
    searchInputs.forEach(input => {
        // Add debounce to search
        let debounceTimer;
        input.addEventListener('input', function(e) {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                performSearch(this);
            }, 300);
        });
        
        // Add clear button
        const clearBtn = document.createElement('button');
        clearBtn.type = 'button';
        clearBtn.className = 'absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden';
        clearBtn.innerHTML = `
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        `;
        clearBtn.addEventListener('click', function() {
            input.value = '';
            input.dispatchEvent(new Event('input'));
            this.classList.add('hidden');
        });
        
        input.parentNode.appendChild(clearBtn);
        
        // Show/hide clear button
        input.addEventListener('input', function() {
            clearBtn.classList.toggle('hidden', !this.value);
        });
    });
}

function performSearch(input) {
    const searchTerm = input.value.toLowerCase().trim();
    const container = input.closest('.overflow-hidden');
    
    if (!container) return;
    
    const table = container.querySelector('tbody');
    if (table) {
        // Table search
        const rows = table.querySelectorAll('tr');
        let visibleCount = 0;
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const isVisible = text.includes(searchTerm);
            row.style.display = isVisible ? '' : 'none';
            if (isVisible) visibleCount++;
        });
        
        // Show/hide no results message
        let noResults = table.parentNode.querySelector('.no-results');
        if (visibleCount === 0 && !noResults) {
            noResults = document.createElement('tr');
            noResults.className = 'no-results';
            noResults.innerHTML = `
                <td colspan="100" class="py-8 text-center">
                    <div class="flex flex-col items-center justify-center">
                        <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="mt-2 text-gray-500 dark:text-gray-400">No results found for "${searchTerm}"</p>
                    </div>
                </td>
            `;
            table.appendChild(noResults);
        } else if (noResults && visibleCount > 0) {
            noResults.remove();
        }
    } else {
        // List search (for tasks or upcoming check-ins)
        const items = container.querySelectorAll('[data-searchable]');
        items.forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    }
}

// ==================== FILTER FUNCTIONALITY ====================

function initializeFilters() {
    const filterSelects = document.querySelectorAll('select');
    
    filterSelects.forEach(select => {
        select.addEventListener('change', function(e) {
            applyFilter(this);
        });
        
        // Add loading indicator
        const originalOption = select.innerHTML;
        select.dataset.original = originalOption;
    });
}

function applyFilter(select) {
    const value = select.value;
    const container = select.closest('.overflow-hidden');
    
    if (!container) return;
    
    // Show loading state
    const originalColor = select.style.color;
    select.style.color = '#9CA3AF'; // Gray-400
    select.disabled = true;
    
    // Get filter parameters
    const filterParams = {
        [select.name || 'filter']: value,
        date_from: container.querySelector('[name="date_from"]')?.value,
        date_to: container.querySelector('[name="date_to"]')?.value,
        status: container.querySelector('[name="status"]')?.value,
    };
    
    // Remove empty params
    Object.keys(filterParams).forEach(key => {
        if (!filterParams[key]) delete filterParams[key];
    });
    
    // Build URL
    let url = select.dataset.filterUrl || window.location.pathname;
    const params = new URLSearchParams(filterParams).toString();
    if (params) url += '?' + params;
    
    // AJAX request for filtered data
    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (response.ok) return response.text();
        throw new Error('Filter request failed');
    })
    .then(html => {
        // Replace table content
        const table = container.querySelector('table');
        const tbody = table?.querySelector('tbody');
        
        if (tbody) {
            // Parse new HTML and extract tbody content
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newTbody = doc.querySelector('tbody');
            
            if (newTbody) {
                tbody.innerHTML = newTbody.innerHTML;
                showNotification('Filters applied successfully', 'success');
            }
        }
    })
    .catch(error => {
        console.error('Filter error:', error);
        showNotification('Error applying filters', 'error');
    })
    .finally(() => {
        // Restore select state
        select.style.color = originalColor;
        select.disabled = false;
    });
}

// ==================== PAGINATION FUNCTIONALITY ====================

function initializePagination() {
    // Handle pagination buttons
    document.addEventListener('click', function(e) {
        const prevBtn = e.target.closest('button:has(svg)');
        const pageBtn = e.target.closest('button:not(:has(svg))');
        
        if (prevBtn && (prevBtn.textContent.includes('Previous') || prevBtn.textContent.includes('Next'))) {
            e.preventDefault();
            navigateToPage(prevBtn);
        } else if (pageBtn && !isNaN(pageBtn.textContent)) {
            e.preventDefault();
            navigateToPage(pageBtn);
        }
    });
}

function navigateToPage(button) {
    const container = button.closest('.overflow-hidden');
    if (!container) return;
    
    // Get current page and direction
    let page;
    if (button.textContent.includes('Previous')) {
        page = parseInt(container.dataset.currentPage || 1) - 1;
    } else if (button.textContent.includes('Next')) {
        page = parseInt(container.dataset.currentPage || 1) + 1;
    } else {
        page = parseInt(button.textContent);
    }
    
    if (page < 1) return;
    
    // Show loading
    const pagination = button.closest('.flex.items-center');
    const originalHTML = pagination.innerHTML;
    pagination.innerHTML = `
        <div class="flex items-center gap-2">
            <div class="animate-spin h-5 w-5 border-2 border-primary border-t-transparent rounded-full"></div>
            <span class="text-sm text-gray-500">Loading page ${page}...</span>
        </div>
    `;
    
    // Build URL with pagination
    const url = new URL(window.location.href);
    url.searchParams.set('page', page);
    
    // AJAX request for paginated data
    fetch(url.toString(), {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        // Parse response and update table
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newTable = doc.querySelector('table');
        
        if (newTable && container) {
            const oldTable = container.querySelector('table');
            if (oldTable) {
                oldTable.replaceWith(newTable);
            }
            
            // Update pagination info
            const newPagination = doc.querySelector('.flex.items-center.justify-between');
            if (newPagination) {
                pagination.innerHTML = newPagination.innerHTML;
            }
            
            // Update current page in dataset
            container.dataset.currentPage = page;
            
            showNotification(`Loaded page ${page}`, 'success');
        }
    })
    .catch(error => {
        console.error('Pagination error:', error);
        showNotification('Error loading page', 'error');
        pagination.innerHTML = originalHTML;
    });
}

// ==================== TASK FUNCTIONS ====================

function initializeTaskFunctions() {
    // Task checkbox toggles
    document.addEventListener('click', function(e) {
        const checkbox = e.target.closest('.flex.h-5.w-5.items-center');
        if (checkbox) {
            e.stopPropagation();
            toggleTaskStatus(checkbox.closest('[data-task-id]'));
        }
        
        // Task action menu items
        const menuItem = e.target.closest('[data-task-action]');
        if (menuItem) {
            const action = menuItem.dataset.taskAction;
            const taskId = menuItem.closest('[data-task-id]')?.dataset.taskId;
            
            if (taskId) {
                switch(action) {
                    case 'edit':
                        editTask(taskId);
                        break;
                    case 'view':
                        viewTaskDetails(taskId);
                        break;
                    case 'assign':
                        assignTaskToSelf(taskId);
                        break;
                    case 'delete':
                        deleteTask(taskId);
                        break;
                }
            }
        }
    });
    
    // Task filter dropdown
    document.addEventListener('click', function(e) {
        const filterBtn = e.target.closest('[data-task-filter]');
        if (filterBtn) {
            const filter = filterBtn.dataset.taskFilter;
            filterTasks(filter);
        }
    });
}

function toggleTaskStatus(taskElement) {
    const taskId = taskElement.dataset.taskId;
    const isChecked = taskElement.__x?.$data.checked || taskElement.querySelector('svg:not(.hidden)');
    
    // Show loading on checkbox
    const checkbox = taskElement.querySelector('.flex.h-5.w-5.items-center');
    const originalHTML = checkbox.innerHTML;
    checkbox.innerHTML = '<div class="animate-spin h-3 w-3 border-2 border-white border-t-transparent rounded-full"></div>';
    
    fetch(`/api/tasks/${taskId}/toggle`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update Alpine.js state if exists
            if (taskElement.__x) {
                taskElement.__x.$data.checked = data.status === 'completed';
            } else {
                // Manual update
                const svg = taskElement.querySelector('svg');
                if (data.status === 'completed') {
                    checkbox.innerHTML = `
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M11.6668 3.5L5.25016 9.91667L2.3335 7" stroke="white" stroke-width="1.94437" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    `;
                    checkbox.classList.add('border-primary-500', 'bg-primary-500');
                    checkbox.classList.remove('border-gray-300', 'bg-white');
                } else {
                    checkbox.innerHTML = '';
                    checkbox.classList.remove('border-primary-500', 'bg-primary-500');
                    checkbox.classList.add('border-gray-300', 'bg-white');
                }
            }
            
            // Update task statistics
            updateTaskStatistics();
            
            showNotification(`Task marked as ${data.status === 'completed' ? 'completed' : 'pending'}`, 'success');
        }
    })
    .catch(error => {
        console.error('Error toggling task:', error);
        checkbox.innerHTML = originalHTML;
        showNotification('Error updating task', 'error');
    });
}

function filterTasks(filterType) {
    const tasksList = document.getElementById('tasks-list');
    if (!tasksList) return;
    
    // Show loading
    const originalContent = tasksList.innerHTML;
    tasksList.innerHTML = `
        <div class="flex items-center justify-center p-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
        </div>
    `;
    
    // Update active filter button
    document.querySelectorAll('[data-task-filter]').forEach(btn => {
        btn.classList.remove('text-primary-600', 'bg-primary-50');
        if (btn.dataset.taskFilter === filterType) {
            btn.classList.add('text-primary-600', 'bg-primary-50');
        }
    });
    
    // Filter tasks
    const allTasks = Array.from(tasksList.querySelectorAll('[data-task-id]'));
    
    setTimeout(() => {
        let filteredTasks = [];
        
        switch(filterType) {
            case 'all':
                filteredTasks = allTasks;
                break;
            case 'pending':
                filteredTasks = allTasks.filter(task => 
                    task.dataset.taskStatus === 'pending' || 
                    task.__x?.$data.checked === false
                );
                break;
            case 'in_progress':
                filteredTasks = allTasks.filter(task => 
                    task.dataset.taskStatus === 'in_progress'
                );
                break;
            case 'overdue':
                filteredTasks = allTasks.filter(task => {
                    const dueDate = new Date(task.dataset.dueDate);
                    const now = new Date();
                    return dueDate < now && 
                          (task.dataset.taskStatus === 'pending' || 
                           task.dataset.taskStatus === 'in_progress');
                });
                break;
            default:
                filteredTasks = allTasks;
        }
        
        // Hide all tasks first
        allTasks.forEach(task => task.style.display = 'none');
        
        // Show filtered tasks
        filteredTasks.forEach(task => task.style.display = '');
        
        // Show empty state if no tasks
        if (filteredTasks.length === 0) {
            tasksList.innerHTML = `
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-6 text-center dark:border-gray-800 dark:bg-gray-800">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="mt-2 text-gray-500 dark:text-gray-400">No ${filterType} tasks found</p>
                </div>
            `;
        } else {
            tasksList.innerHTML = originalContent;
            // Re-run filter logic to properly display
            filterTasks(filterType);
        }
        
        // Update task counts
        updateTaskStatistics();
        
    }, 300); // Simulate network delay
}

function updateTaskStatistics() {
    const tasks = document.querySelectorAll('[data-task-id]');
    let pending = 0;
    let inProgress = 0;
    let overdue = 0;
    
    tasks.forEach(task => {
        const status = task.dataset.taskStatus;
        const dueDate = task.dataset.dueDate;
        const isCompleted = task.__x?.$data.checked || task.querySelector('svg:not(.hidden)');
        
        if (!isCompleted) {
            if (status === 'pending') pending++;
            if (status === 'in_progress') inProgress++;
            
            if (dueDate && new Date(dueDate) < new Date()) {
                overdue++;
            }
        }
    });
    
    // Update statistics display
    document.querySelectorAll('[data-stat="pending"]').forEach(el => {
        if (el.tagName === 'P') {
            el.textContent = pending;
        }
    });
    
    document.querySelectorAll('[data-stat="in_progress"]').forEach(el => {
        if (el.tagName === 'P') {
            el.textContent = inProgress;
        }
    });
    
    document.querySelectorAll('[data-stat="overdue"]').forEach(el => {
        if (el.tagName === 'P') {
            el.textContent = overdue;
        }
    });
}

// ==================== STATS REFRESH FUNCTIONALITY ====================

function initializeStatsRefresh() {
    // Auto-refresh stats every 2 minutes
    let refreshInterval = setInterval(() => {
        if (document.visibilityState === 'visible') {
            fetchDashboardData();
        }
    }, 120000); // 2 minutes
    
    // Manual refresh button
    const refreshBtn = document.querySelector('[data-refresh-stats]');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', () => {
            fetchDashboardData(true);
        });
    }
    
    // Clean up on unload
    window.addEventListener('beforeunload', () => {
        clearInterval(refreshInterval);
    });
}

function fetchDashboardData(showLoading = false) {
    if (showLoading) {
        showNotification('Refreshing dashboard data...', 'info');
    }
    
    fetch('{{ route("dashboard") }}', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateDashboardStats(data.data);
            if (showLoading) {
                showNotification('Dashboard data refreshed!', 'success');
            }
        }
    })
    .catch(error => {
        console.error('Error fetching dashboard data:', error);
        if (showLoading) {
            showNotification('Error refreshing data', 'error');
        }
    });
}

function updateDashboardStats(data) {
    // Update all stats cards
    const statCards = document.querySelectorAll('[data-stat-card]');
    
    statCards.forEach(card => {
        const statKey = card.dataset.statCard;
        if (data[statKey] !== undefined) {
            const valueElement = card.querySelector('.text-2xl');
            if (valueElement) {
                // Format based on stat type
                let displayValue;
                if (statKey.includes('revenue') || statKey.includes('amount')) {
                    displayValue = 'KSH ' + formatNumber(data[statKey]);
                } else if (statKey.includes('rate') || statKey.includes('percentage')) {
                    displayValue = data[statKey].toFixed(1) + '%';
                } else {
                    displayValue = data[statKey];
                }
                
                valueElement.textContent = displayValue;
                
                // Add animation
                valueElement.classList.add('scale-105');
                setTimeout(() => {
                    valueElement.classList.remove('scale-105');
                }, 300);
            }
            
            // Update trend indicators if available
            const trendKey = `${statKey}_trend`;
            if (data[trendKey] !== undefined) {
                const trendElement = card.querySelector('[data-trend]');
                if (trendElement) {
                    const trend = data[trendKey];
                    const isPositive = trend >= 0;
                    
                    trendElement.className = `flex items-center gap-1 rounded-full px-2 py-0.5 text-theme-xs font-medium ${
                        isPositive 
                            ? 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500'
                            : 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500'
                    }`;
                    
                    trendElement.innerHTML = `
                        ${isPositive ? '↑' : '↓'} ${Math.abs(trend)}%
                    `;
                }
            }
        }
    });
    
    // Update kitchen orders
    if (data.kitchen_stats) {
        updateKitchenOrders(data.kitchen_stats);
    }
    
    // Update task statistics
    if (data.task_stats) {
        updateTaskStatisticsFromData(data.task_stats);
    }
}

function updateKitchenOrders(stats) {
    // Update kitchen order counts
    const elements = {
        pending: document.querySelector('[data-kitchen="pending"]'),
        preparing: document.querySelector('[data-kitchen="preparing"]'),
        ready: document.querySelector('[data-kitchen="ready"]'),
        completed: document.querySelector('[data-kitchen="completed"]'),
        total: document.querySelector('[data-kitchen="total"]')
    };
    
    Object.keys(elements).forEach(key => {
        if (elements[key] && stats[key] !== undefined) {
            elements[key].textContent = stats[key];
            
            // Add update animation
            elements[key].classList.add('scale-110', 'text-primary-600');
            setTimeout(() => {
                elements[key].classList.remove('scale-110', 'text-primary-600');
            }, 300);
        }
    });
    
    // Update progress bars
    const totalToday = stats.total_today || 0;
    ['pending', 'preparing', 'ready', 'completed'].forEach(status => {
        const progressBar = document.querySelector(`[data-kitchen-progress="${status}"]`);
        if (progressBar && stats[status] !== undefined && totalToday > 0) {
            const percentage = (stats[status] / totalToday) * 100;
            progressBar.style.width = `${percentage}%`;
        }
    });
}

function updateTaskStatisticsFromData(stats) {
    document.querySelectorAll('[data-stat="pending"]').forEach(el => {
        if (el.tagName === 'P' && stats.pending !== undefined) {
            el.textContent = stats.pending;
        }
    });
    
    document.querySelectorAll('[data-stat="in_progress"]').forEach(el => {
        if (el.tagName === 'P' && stats.in_progress !== undefined) {
            el.textContent = stats.in_progress;
        }
    });
    
    document.querySelectorAll('[data-stat="overdue"]').forEach(el => {
        if (el.tagName === 'P' && stats.overdue !== undefined) {
            el.textContent = stats.overdue;
        }
    });
}

// ==================== DATA TABLE ENHANCEMENTS ====================

function initializeDataTables() {
    // Add sort functionality to table headers
    document.querySelectorAll('th[data-sortable]').forEach(th => {
        th.style.cursor = 'pointer';
        th.addEventListener('click', () => {
            sortTable(th);
        });
        
        // Add sort indicator
        th.innerHTML += `
            <span class="ml-1 text-gray-400">
                <svg class="h-3 w-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                </svg>
            </span>
        `;
    });
    
    // Add row hover effects
    document.querySelectorAll('tbody tr').forEach(row => {
        row.addEventListener('mouseenter', () => {
            row.classList.add('bg-gray-50', 'dark:bg-gray-800/50');
        });
        
        row.addEventListener('mouseleave', () => {
            row.classList.remove('bg-gray-50', 'dark:bg-gray-800/50');
        });
    });
    
    // Add click to select row
    document.querySelectorAll('tbody tr[data-selectable]').forEach(row => {
        row.addEventListener('click', (e) => {
            if (!e.target.closest('a') && !e.target.closest('button')) {
                row.classList.toggle('bg-primary-50', 'dark:bg-primary-500/10');
            }
        });
    });
}

function sortTable(header) {
    const table = header.closest('table');
    const tbody = table.querySelector('tbody');
    const columnIndex = Array.from(header.parentNode.children).indexOf(header);
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    // Determine sort direction
    const isAscending = !header.classList.contains('sorted-asc');
    header.classList.toggle('sorted-asc', isAscending);
    header.classList.toggle('sorted-desc', !isAscending);
    
    // Sort rows
    rows.sort((a, b) => {
        const aText = a.children[columnIndex].textContent.trim();
        const bText = b.children[columnIndex].textContent.trim();
        
        // Try to compare as numbers first
        const aNum = parseFloat(aText.replace(/[^0-9.-]+/g, ''));
        const bNum = parseFloat(bText.replace(/[^0-9.-]+/g, ''));
        
        if (!isNaN(aNum) && !isNaN(bNum)) {
            return isAscending ? aNum - bNum : bNum - aNum;
        }
        
        // Fall back to string comparison
        return isAscending 
            ? aText.localeCompare(bText)
            : bText.localeCompare(aText);
    });
    
    // Re-add sorted rows
    rows.forEach(row => tbody.appendChild(row));
    
    // Update sort indicators
    table.querySelectorAll('th').forEach(th => {
        if (th !== header) {
            th.classList.remove('sorted-asc', 'sorted-desc');
        }
    });
    
    // Update sort icon
    const icon = header.querySelector('svg');
    if (icon) {
        icon.style.transform = isAscending ? 'rotate(180deg)' : 'rotate(0deg)';
    }
    
    showNotification(`Sorted by ${header.textContent.trim()} ${isAscending ? 'ascending' : 'descending'}`, 'info');
}

// ==================== EXPORT FUNCTIONS ====================

function exportTableToCSV(tableId, filename = 'export.csv') {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    const rows = table.querySelectorAll('tr');
    const csv = [];
    
    rows.forEach(row => {
        const rowData = [];
        row.querySelectorAll('th, td').forEach(cell => {
            // Exclude action buttons
            if (!cell.querySelector('button') && !cell.querySelector('a')) {
                rowData.push(`"${cell.textContent.trim().replace(/"/g, '""')}"`);
            }
        });
        csv.push(rowData.join(','));
    });
    
    const csvString = csv.join('\n');
    const blob = new Blob([csvString], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
    
    showNotification('CSV exported successfully!', 'success');
}

function exportToPDF(elementId, filename = 'export.pdf') {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    showNotification('Generating PDF...', 'info');
    
    // Using html2pdf library (would need to be included)
    if (typeof html2pdf !== 'undefined') {
        const opt = {
            margin: [0.5, 0.5],
            filename: filename,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2 },
            jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
        };
        
        html2pdf().set(opt).from(element).save().then(() => {
            showNotification('PDF generated successfully!', 'success');
        });
    } else {
        showNotification('PDF export library not loaded', 'error');
    }
}

// ==================== UTILITY FUNCTIONS ====================

// Debounce function for performance
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Throttle function for performance
function throttle(func, limit) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Copy to clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showNotification('Copied to clipboard!', 'success');
    }).catch(err => {
        console.error('Copy failed:', err);
        showNotification('Copy failed', 'error');
    });
}

// Format date for display
function formatDate(date, format = 'short') {
    const d = new Date(date);
    if (format === 'short') {
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    } else if (format === 'long') {
        return d.toLocaleDateString('en-US', { 
            weekday: 'short', 
            month: 'short', 
            day: 'numeric',
            year: 'numeric'
        });
    } else if (format === 'time') {
        return d.toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
    }
    return d.toLocaleDateString();
}

// Initialize tooltips
function initializeTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    
    tooltipElements.forEach(el => {
        const tooltipText = el.dataset.tooltip;
        const tooltip = document.createElement('div');
        tooltip.className = 'absolute z-50 px-2 py-1 text-xs text-white bg-gray-900 rounded opacity-0 pointer-events-none transition-opacity duration-200';
        tooltip.textContent = tooltipText;
        el.appendChild(tooltip);
        
        el.addEventListener('mouseenter', () => {
            tooltip.classList.remove('opacity-0');
            tooltip.classList.add('opacity-100');
        });
        
        el.addEventListener('mouseleave', () => {
            tooltip.classList.remove('opacity-100');
            tooltip.classList.add('opacity-0');
        });
    });
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', initializeTooltips);

// ==================== ERROR HANDLING ====================

// Global error handler
window.addEventListener('error', function(e) {
    console.error('Global error:', e.error);
    showNotification('An error occurred. Please refresh the page.', 'error');
});

// Unhandled promise rejection handler
window.addEventListener('unhandledrejection', function(e) {
    console.error('Unhandled promise rejection:', e.reason);
    showNotification('An error occurred. Please try again.', 'error');
});

// Network status monitoring
window.addEventListener('online', function() {
    showNotification('You are back online!', 'success');
});

window.addEventListener('offline', function() {
    showNotification('You are offline. Some features may not work.', 'warning');
});

// Service Worker registration for PWA
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/sw.js').then(function(registration) {
            console.log('ServiceWorker registration successful with scope: ', registration.scope);
        }, function(err) {
            console.log('ServiceWorker registration failed: ', err);
        });
    });
}

// Initialize dashboard when Alpine.js is ready
document.addEventListener('alpine:initialized', () => {
    console.log('Alpine.js initialized, dashboard ready');
    // Any Alpine.js specific initialization
});

</script>

<style>
/* Custom scrollbar for dashboard */
.custom-scrollbar::-webkit-scrollbar {
    height: 6px;
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Dark mode scrollbar */
.dark .custom-scrollbar::-webkit-scrollbar-track {
    background: #374151;
}

.dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background: #6b7280;
}

.dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}

/* Animation classes */
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Smooth transitions */
.smooth-transition {
    transition: all 0.3s ease-in-out;
}

/* Line clamp utility */
.line-clamp-1 {
    overflow: hidden;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 1;
}

.line-clamp-2 {
    overflow: hidden;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
}

/* Loading skeleton */
.skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
}

.dark .skeleton {
    background: linear-gradient(90deg, #374151 25%, #4b5563 50%, #374151 75%);
    background-size: 200% 100%;
}

@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* Notification animation */
@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}

.dashboard-notification {
    animation: slideIn 0.3s ease-out forwards;
}

.dashboard-notification.hiding {
    animation: slideOut 0.3s ease-in forwards;
}

/* Table row hover effect */
tbody tr {
    transition: background-color 0.15s ease-in-out;
}

tbody tr:hover {
    background-color: rgba(243, 244, 246, 0.5);
}

.dark tbody tr:hover {
    background-color: rgba(31, 41, 55, 0.5);
}

/* Card hover effects */
.rounded-2xl.border {
    transition: box-shadow 0.3s ease, transform 0.3s ease;
}

.rounded-2xl.border:hover {
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    transform: translateY(-2px);
}

.dark .rounded-2xl.border:hover {
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2), 0 10px 10px -5px rgba(0, 0, 0, 0.1);
}

/* Button hover effects */
button:not(:disabled) {
    transition: all 0.2s ease-in-out;
}

button:not(:disabled):hover {
    transform: translateY(-1px);
}

button:not(:disabled):active {
    transform: translateY(0);
}
</style>