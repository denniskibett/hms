@extends('layouts.app')

@section('content')
    <div class="p-4 mx-auto max-w-screen-2xl md:p-6">
        <!-- Welcome Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90 mb-2">Welcome back, {{ $user->name }} ({{ $rolesDisplay }})!</h1>
            <p class="text-gray-600 dark:text-gray-400">Here's your dashboard overview.</p>
        </div>
     
            @if(auth()->user()->hasRole('admin'))
                @include('dashboard.admin', [
                    'user' => auth()->user(),
                    'stats' => $stats ?? null,
                    'recentBookings' => $recentBookings ?? [],
                    'upcomingCheckins' => $upcomingCheckins ?? [],
                    'recentPayments' => $recentPayments ?? [],
                    'pendingOrders' => $pendingOrders ?? []
                ])
            @endif

            @if(auth()->user()->hasRole('finance'))
                @include('dashboard.finance', [
                    'user' => auth()->user(),
                    'stats' => $stats ?? null,
                    'pendingPayments' => $pendingPayments ?? 0,
                    'invoicesToApprove' => $invoicesToApprove ?? 0,
                    'recentPayments' => $recentPayments ?? [],
                    'overdueInvoices' => $overdueInvoices ?? []
                ])
            @endif

            @if(auth()->user()->hasRole('receptionist'))
                @include('dashboard.receptionist', [
                    'user' => auth()->user(),
                    'stats' => $stats ?? null,
                    'pendingCheckins' => $pendingCheckins ?? [],
                    'pendingCheckouts' => $pendingCheckouts ?? [],
                    'todayArrivals' => $todayArrivals ?? 0,
                    'todayDepartures' => $todayDepartures ?? 0,
                    'availableRooms' => $availableRooms ?? 0
                ])
            @endif

            @if(auth()->user()->hasRole('guest'))
                @include('dashboard.guest', [
                    'user' => auth()->user(),
                    'currentStays' => $currentStays ?? [],
                    'pastStays' => $pastStays ?? [],
                    'pendingInvoices' => $pendingInvoices ?? 0,
                    'totalSpent' => $totalSpent ?? 0
                ])
            @endif

            @if(auth()->user()->hasRole('housekeeping'))
                @include('dashboard.housekeeping', [
                    'user' => auth()->user(),
                    'stats' => $stats ?? null,
                    'tasks' => $tasks ?? [],
                    'todayShifts' => $todayShifts ?? [],
                    'dirtyRooms' => $dirtyRooms ?? [],
                    'recentlyCleaned' => $recentlyCleaned ?? []
                ])
            @endif

            @if(auth()->user()->hasRole('kitchen'))
                @include('dashboard.kitchen', [
                    'user' => auth()->user(),
                    'stats' => $stats ?? null,
                    'pendingOrders' => $pendingOrders ?? [],
                    'preparingOrders' => $preparingOrders ?? [],
                    'completedToday' => $completedToday ?? 0,
                    'lowStockItems' => $lowStockItems ?? []
                ])
            @endif

            @if(auth()->user()->hasRole('procurement'))
                @include('dashboard.procurement', [
                    'user' => auth()->user(),
                    'stats' => $stats ?? null,
                    'lowStockItems' => $lowStockItems ?? [],
                    'pendingOrders' => $pendingOrders ?? [],
                    'awaitingDelivery' => $awaitingDelivery ?? []
                ])
            @endif

            @if(auth()->user()->hasRole('hr'))
                @include('dashboard.hr', [
                    'user' => auth()->user(),
                    'stats' => $stats ?? null,
                    'pendingLeave' => $pendingLeave ?? [],
                    'activeStaff' => $activeStaff ?? 0,
                    'upcomingBirthdays' => $upcomingBirthdays ?? []
                ])
            @endif

            @if(auth()->user()->hasRole('manager'))
                @include('dashboard.manager', [
                    'user' => auth()->user(),
                    'stats' => $stats ?? null,
                    'recentBookings' => $recentBookings ?? [],
                    'staffPerformance' => $staffPerformance ?? [],
                    'departmentStats' => $departmentStats ?? []
                ])
            @endif

            <!-- Fallback for roles without specific dashboard -->
            @if(!auth()->user()->hasRole('admin') && 
                !auth()->user()->hasRole('finance') && 
                !auth()->user()->hasRole('receptionist') && 
                !auth()->user()->hasRole('guest') && 
                !auth()->user()->hasRole('housekeeping') && 
                !auth()->user()->hasRole('kitchen') && 
                !auth()->user()->hasRole('procurement') && 
                !auth()->user()->hasRole('hr') && 
                !auth()->user()->hasRole('manager'))
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Welcome</p>
                                <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ auth()->user()->name }}</p>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Your role: {{ auth()->user()->roles->first()->name ?? 'User' }}</p>
                            </div>
                            <div class="p-3 rounded-full bg-primary/10">
                                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
    </div>
@endsection
