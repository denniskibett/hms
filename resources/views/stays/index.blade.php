@extends('layouts.app')

@section('content')
    <!-- Breadcrumb Start -->
    <div x-data="{ pageName: `Stays Management`}">
        @include('partials.breadcrumb')
    </div>
    <!-- Breadcrumb End -->

<div x-data="{
    stays: {{ Js::from($stays->toArray()) }},
    allRooms: {{ Js::from($allRooms->toArray()) }},
    roomAllocations: {{ Js::from($roomAllocations->toArray()) }},
    search: '',
    status: '',
    roomType: '',
    dateFrom: '',
    dateTo: '',
    currentPage: 1,
    itemsPerPage: 10,
    sortField: 'created_at',
    sortDirection: 'desc',
    activeTab: 'all',
    
    // Computed properties
    get filteredStays() {
        let filtered = this.stays;
        
        // Apply search
        if (this.search) {
            const searchTerm = this.search.toLowerCase();
            filtered = filtered.filter(stay => 
                (stay.guest?.name?.toLowerCase().includes(searchTerm)) ||
                (stay.guest?.email?.toLowerCase().includes(searchTerm)) ||
                stay.room_allocations?.some(ra => 
                    ra.room?.room_number?.toLowerCase().includes(searchTerm)
                )
            );
        }
        
        // Apply status filter
        if (this.status) {
            filtered = filtered.filter(stay => {
                switch(this.status) {
                    case 'active':
                        return ['reserved', 'booked', 'checked_in'].includes(stay.status);
                    case 'upcoming':
                        return stay.status === 'reserved' && 
                               new Date(stay.arrival_date) > new Date();
                    case 'completed':
                        return stay.status === 'checked_out';
                    case 'cancelled':
                        return stay.status === 'cancelled';
                    default:
                        return stay.status === this.status;
                }
            });
        }
        
        // Apply room type filter
        if (this.roomType) {
            filtered = filtered.filter(stay => 
                stay.room_allocations?.some(ra => 
                    ra.room?.room_type?.name?.toLowerCase().includes(this.roomType.toLowerCase())
                )
            );
        }
        
        // Apply date filters
        if (this.dateFrom) {
            filtered = filtered.filter(stay => 
                new Date(stay.arrival_date) >= new Date(this.dateFrom)
            );
        }
        
        if (this.dateTo) {
            filtered = filtered.filter(stay => 
                new Date(stay.departure_date) <= new Date(this.dateTo)
            );
        }
        
        // Apply tab filter
        switch(this.activeTab) {
            case 'active':
                filtered = filtered.filter(stay => 
                    ['reserved', 'booked', 'checked_in'].includes(stay.status)
                );
                break;
            case 'upcoming':
                filtered = filtered.filter(stay => 
                    stay.status === 'reserved' && 
                    new Date(stay.arrival_date) > new Date()
                );
                break;
            case 'completed':
                filtered = filtered.filter(stay => stay.status === 'checked_out');
                break;
            case 'cancelled':
                filtered = filtered.filter(stay => stay.status === 'cancelled');
                break;
        }
        
        // Apply sorting
        filtered.sort((a, b) => {
            let aValue = a[this.sortField];
            let bValue = b[this.sortField];
            
            // Handle nested properties
            if (this.sortField === 'guest_name') {
                aValue = a.guest?.name;
                bValue = b.guest?.name;
            } else if (this.sortField === 'room_number') {
                aValue = a.room_allocations?.[0]?.room?.room_number;
                bValue = b.room_allocations?.[0]?.room?.room_number;
            }
            
            if (aValue < bValue) return this.sortDirection === 'asc' ? -1 : 1;
            if (aValue > bValue) return this.sortDirection === 'asc' ? 1 : -1;
            return 0;
        });
        
        return filtered;
    },
    
    get paginatedStays() {
        const start = (this.currentPage - 1) * this.itemsPerPage;
        const end = start + this.itemsPerPage;
        return this.filteredStays.slice(start, end);
    },
    
    get totalPages() {
        return Math.ceil(this.filteredStays.length / this.itemsPerPage);
    },
    
    get stayStats() {
        return {
            active: this.stays.filter(s => ['reserved', 'booked', 'checked_in'].includes(s.status)).length,
            upcoming: this.stays.filter(s => s.status === 'reserved' && new Date(s.arrival_date) > new Date()).length,
            completed: this.stays.filter(s => s.status === 'checked_out').length,
            cancelled: this.stays.filter(s => s.status === 'cancelled').length,
            todayCheckins: this.stays.filter(s => 
                s.status === 'booked' && 
                new Date(s.arrival_date).toDateString() === new Date().toDateString()
            ).length,
            todayCheckouts: this.stays.filter(s => 
                s.status === 'checked_in' && 
                new Date(s.departure_date).toDateString() === new Date().toDateString()
            ).length,
            occupiedRooms: {{ $occupiedRooms }}
        };
    },
    
    // Methods
    sortBy(field) {
        if (this.sortField === field) {
            this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            this.sortField = field;
            this.sortDirection = 'asc';
        }
        this.currentPage = 1;
    },
    
    changePage(page) {
        if (page >= 1 && page <= this.totalPages) {
            this.currentPage = page;
        }
    },
    
    resetFilters() {
        this.search = '';
        this.status = '';
        this.roomType = '';
        this.dateFrom = '';
        this.dateTo = '';
        this.currentPage = 1;
    },
    
    formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { 
            weekday: 'short', 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
    },
    
    formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: '{{ config("app.currency", "USD") }}'
        }).format(amount || 0);
    },
    
    getStatusBadgeClass(status) {
        switch(status) {
            case 'checked_in': return 'bg-success/10 text-success dark:bg-success/15';
            case 'booked': return 'bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-500';
            case 'reserved': return 'bg-warning/10 text-warning dark:bg-warning/15';
            case 'checked_out': return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400';
            case 'cancelled': return 'bg-danger/10 text-danger dark:bg-danger/15';
            default: return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400';
        }
    }
}">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Stay Management</h2>
                <p class="text-gray-600 dark:text-gray-400">Manage guest stays and room bookings</p>
            </div>
            @can('create', Stay::class)
            <div class="flex items-center space-x-2">
                <a href="{{ route('stays.create') }}" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                    <i class="fas fa-plus mr-1"></i> New Stay
                </a>
                <button onclick="printStays()" class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                    <i class="fas fa-print mr-1"></i> Print
                </button>
            </div>
            @endcan
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <!-- Occupied Rooms -->
        <div class="rounded-2xl border border-gray-200 bg-white px-6 py-5 dark:border-gray-800 dark:bg-white/3">
            <div class="flex items-center gap-3">
                <div class="rounded-full bg-primary/10 p-3 text-primary dark:bg-primary/15">
                    <i class="fas fa-bed text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Occupied Rooms</p>
                    <p class="text-2xl font-semibold text-gray-800 dark:text-white/90" x-text="stayStats.occupiedRooms"></p>
                </div>
            </div>
        </div>

        <!-- Active Stays -->
        <div class="rounded-2xl border border-gray-200 bg-white px-6 py-5 dark:border-gray-800 dark:bg-white/3">
            <div class="flex items-center gap-3">
                <div class="rounded-full bg-success/10 p-3 text-success dark:bg-success/15">
                    <i class="fas fa-calendar-check text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Active Stays</p>
                    <p class="text-2xl font-semibold text-gray-800 dark:text-white/90" x-text="stayStats.active"></p>
                </div>
            </div>
        </div>

        <!-- Upcoming Stays -->
        <div class="rounded-2xl border border-gray-200 bg-white px-6 py-5 dark:border-gray-800 dark:bg-white/3">
            <div class="flex items-center gap-3">
                <div class="rounded-full bg-warning/10 p-3 text-warning dark:bg-warning/15">
                    <i class="fas fa-calendar-day text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Upcoming Stays</p>
                    <p class="text-2xl font-semibold text-gray-800 dark:text-white/90" x-text="stayStats.upcoming"></p>
                </div>
            </div>
        </div>

        <!-- Today's Check-outs -->
        <div class="rounded-2xl border border-gray-200 bg-white px-6 py-5 dark:border-gray-800 dark:bg-white/3">
            <div class="flex items-center gap-3">
                <div class="rounded-full bg-danger/10 p-3 text-danger dark:bg-danger/15">
                    <i class="fas fa-calendar-times text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Check-outs Today</p>
                    <p class="text-2xl font-semibold text-gray-800 dark:text-white/90" x-text="stayStats.todayCheckouts"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="mb-6">
        <div class="border-b border-gray-200 dark:border-gray-800">
            <nav class="-mb-px flex space-x-1 overflow-x-auto px-6">
                <button @click="activeTab = 'all'; currentPage = 1" 
                        :class="activeTab === 'all' 
                            ? 'border-primary text-primary' 
                            : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                        class="whitespace-nowrap border-b-2 px-4 py-3 text-sm font-medium">
                    All Stays
                    <span class="ml-2 rounded-full bg-gray-100 px-2 py-0.5 text-xs dark:bg-gray-800" 
                          x-text="stays.length"></span>
                </button>
                <button @click="activeTab = 'active'; currentPage = 1" 
                        :class="activeTab === 'active' 
                            ? 'border-success text-success' 
                            : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                        class="whitespace-nowrap border-b-2 px-4 py-3 text-sm font-medium">
                    Active
                    <span class="ml-2 rounded-full bg-success/10 px-2 py-0.5 text-xs text-success" 
                          x-text="stayStats.active"></span>
                </button>
                <button @click="activeTab = 'upcoming'; currentPage = 1" 
                        :class="activeTab === 'upcoming' 
                            ? 'border-warning text-warning' 
                            : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                        class="whitespace-nowrap border-b-2 px-4 py-3 text-sm font-medium">
                    Upcoming
                    <span class="ml-2 rounded-full bg-warning/10 px-2 py-0.5 text-xs text-warning" 
                          x-text="stayStats.upcoming"></span>
                </button>
                <button @click="activeTab = 'completed'; currentPage = 1" 
                        :class="activeTab === 'completed' 
                            ? 'border-gray-400 text-gray-600' 
                            : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                        class="whitespace-nowrap border-b-2 px-4 py-3 text-sm font-medium">
                    Completed
                    <span class="ml-2 rounded-full bg-gray-100 px-2 py-0.5 text-xs dark:bg-gray-800" 
                          x-text="stayStats.completed"></span>
                </button>
                <button @click="activeTab = 'cancelled'; currentPage = 1" 
                        :class="activeTab === 'cancelled' 
                            ? 'border-danger text-danger' 
                            : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                        class="whitespace-nowrap border-b-2 px-4 py-3 text-sm font-medium">
                    Cancelled
                    <span class="ml-2 rounded-full bg-danger/10 px-2 py-0.5 text-xs text-danger" 
                          x-text="stayStats.cancelled"></span>
                </button>
            </nav>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/3">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <!-- Search Bar -->
            <div class="relative w-full md:w-auto md:flex-1 md:max-w-sm">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input x-model="search" 
                       type="search" 
                       placeholder="Search stays, guests, rooms..." 
                       class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-3 pl-10 text-sm text-gray-900 focus:border-primary focus:ring-primary dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400">
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap gap-3">
                <!-- Status Filter -->
                <select x-model="status" 
                        class="rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 focus:border-primary focus:ring-primary dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="upcoming">Upcoming</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>

                <!-- Room Type Filter -->
                <select x-model="roomType" 
                        class="rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 focus:border-primary focus:ring-primary dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <option value="">All Room Types</option>
                    @foreach($roomTypes as $type)
                    <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>

                <!-- Date Range -->
                <div class="flex gap-2">
                    <input x-model="dateFrom" 
                           type="date" 
                           class="rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 focus:border-primary focus:ring-primary dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <input x-model="dateTo" 
                           type="date" 
                           class="rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 focus:border-primary focus:ring-primary dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-2">
                    <button @click="resetFilters()" 
                            class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-800">
                        <i class="fas fa-times mr-1"></i> Clear
                    </button>
                    <button class="rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white hover:bg-primary/90">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Timeline -->
    <div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-2">
        <!-- Today's Check-ins -->
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/3">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                        <i class="fas fa-door-open text-success mr-2"></i>
                        Today's Check-ins
                    </h3>
                    <span class="rounded-full bg-success/10 px-3 py-1 text-sm font-medium text-success" 
                          x-text="stayStats.todayCheckins"></span>
                </div>
            </div>
            <div class="p-6">
                <template x-if="todaysCheckins && todaysCheckins.length > 0">
                    <div class="space-y-4">
                        <template x-for="stay in todaysCheckins" :key="stay.id">
                            <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800/50">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-800 dark:text-white/90" x-text="stay.guest?.name"></p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            <span x-text="stay.room_allocations?.[0]?.room?.room_number ? 'Room ' + stay.room_allocations[0].room.room_number : 'No room assigned'"></span>
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-gray-800 dark:text-white/90" 
                                           x-text="formatDate(stay.arrival_date)"></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400" x-text="stay.nights + ' night(s)'"></p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
                <template x-if="!todaysCheckins || todaysCheckins.length === 0">
                    <div class="py-8 text-center text-gray-500 dark:text-gray-400">
                        <i class="fas fa-door-open text-3xl mb-3 opacity-50"></i>
                        <p>No check-ins today</p>
                    </div>
                </template>
            </div>
        </div>

        <!-- Today's Check-outs -->
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/3">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                        <i class="fas fa-door-closed text-danger mr-2"></i>
                        Today's Check-outs
                    </h3>
                    <span class="rounded-full bg-danger/10 px-3 py-1 text-sm font-medium text-danger" 
                          x-text="stayStats.todayCheckouts"></span>
                </div>
            </div>
            <div class="p-6">
                <template x-if="todaysCheckouts && todaysCheckouts.length > 0">
                    <div class="space-y-4">
                        <template x-for="stay in todaysCheckouts" :key="stay.id">
                            <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800/50">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-800 dark:text-white/90" x-text="stay.guest?.name"></p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            <span x-text="stay.room_allocations?.[0]?.room?.room_number ? 'Room ' + stay.room_allocations[0].room.room_number : 'No room assigned'"></span>
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-gray-800 dark:text-white/90" 
                                           x-text="formatDate(stay.departure_date)"></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400" x-text="stay.nights + ' night(s)'"></p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
                <template x-if="!todaysCheckouts || todaysCheckouts.length === 0">
                    <div class="py-8 text-center text-gray-500 dark:text-gray-400">
                        <i class="fas fa-door-closed text-3xl mb-3 opacity-50"></i>
                        <p>No check-outs today</p>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Stays Table -->
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/3">
        <!-- Table Header -->
        <div class="flex flex-col items-center justify-between gap-4 border-b border-gray-200 px-6 py-4 dark:border-gray-800 md:flex-row">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">All Stays</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400" 
                   x-text="'Showing ' + paginatedStays.length + ' of ' + filteredStays.length + ' stays'"></p>
            </div>
            
            <!-- Items Per Page -->
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Show</span>
                    <select x-model="itemsPerPage" @change="currentPage = 1"
                            class="rounded-lg border border-gray-300 bg-gray-50 px-3 py-1.5 text-sm text-gray-900 focus:border-primary focus:ring-primary dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                    </select>
                    <span class="text-sm text-gray-500 dark:text-gray-400">entries</span>
                </div>
                
                <!-- Export Button -->
                <button class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-800">
                    <i class="fas fa-download mr-1"></i> Export
                </button>
            </div>
        </div>

        <!-- Table Content -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-700 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400">
                    <tr>
                        <th class="px-6 py-4">
                            <button @click="sortBy('id')" class="flex items-center gap-1 font-medium">
                                Stay ID
                                <i class="fas fa-sort" :class="{ 
                                    'fa-sort-up': sortField === 'id' && sortDirection === 'asc',
                                    'fa-sort-down': sortField === 'id' && sortDirection === 'desc'
                                }"></i>
                            </button>
                        </th>
                        <th class="px-6 py-4">
                            <button @click="sortBy('guest_name')" class="flex items-center gap-1 font-medium">
                                Guest
                                <i class="fas fa-sort" :class="{ 
                                    'fa-sort-up': sortField === 'guest_name' && sortDirection === 'asc',
                                    'fa-sort-down': sortField === 'guest_name' && sortDirection === 'desc'
                                }"></i>
                            </button>
                        </th>
                        <th class="px-6 py-4">
                            <button @click="sortBy('room_number')" class="flex items-center gap-1 font-medium">
                                Room
                                <i class="fas fa-sort" :class="{ 
                                    'fa-sort-up': sortField === 'room_number' && sortDirection === 'asc',
                                    'fa-sort-down': sortField === 'room_number' && sortDirection === 'desc'
                                }"></i>
                            </button>
                        </th>
                        <th class="px-6 py-4">Check-in</th>
                        <th class="px-6 py-4">Check-out</th>
                        <th class="px-6 py-4">Nights</th>
                        <th class="px-6 py-4">Amount</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="paginatedStays.length > 0">
                        <template x-for="stay in paginatedStays" :key="stay.id">
                            <tr class="border-b border-gray-200 bg-white hover:bg-gray-50 dark:border-gray-800 dark:bg-white/3 dark:hover:bg-gray-800/50">
                                <!-- Stay ID -->
                                <td class="px-6 py-4">
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        <span x-text="'S' + String(stay.id).padStart(5, '0')"></span>
                                    </span>
                                </td>
                                
                                <!-- Guest -->
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white" x-text="stay.guest?.name"></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400" x-text="stay.guest?.email"></p>
                                    </div>
                                </td>
                                
                                <!-- Room -->
                                <td class="px-6 py-4">
                                    <template x-if="stay.room_allocations?.[0]">
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white" 
                                               x-text="'Room ' + stay.room_allocations[0].room?.room_number"></p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400" 
                                               x-text="stay.room_allocations[0].room?.room_type?.name"></p>
                                        </div>
                                    </template>
                                    <template x-if="!stay.room_allocations?.[0]">
                                        <span class="text-gray-400 dark:text-gray-500">Not assigned</span>
                                    </template>
                                </td>
                                
                                <!-- Check-in -->
                                <td class="px-6 py-4">
                                    <span x-text="formatDate(stay.arrival_date)" class="whitespace-nowrap"></span>
                                </td>
                                
                                <!-- Check-out -->
                                <td class="px-6 py-4">
                                    <span x-text="formatDate(stay.departure_date)" class="whitespace-nowrap"></span>
                                </td>
                                
                                <!-- Nights -->
                                <td class="px-6 py-4">
                                    <span x-text="stay.nights || '0'" class="font-medium"></span> nights
                                </td>
                                
                                <!-- Amount -->
                                <td class="px-6 py-4">
                                    <span x-text="formatCurrency(stay.total_amount)" class="font-medium"></span>
                                </td>
                                
                                <!-- Status -->
                                <td class="px-6 py-4">
                                    <span :class="getStatusBadgeClass(stay.status)" 
                                          class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium">
                                        <i class="fas fa-circle text-[8px] mr-1.5"></i>
                                        <span x-text="stay.status.charAt(0).toUpperCase() + stay.status.slice(1)"></span>
                                    </span>
                                </td>
                                
                                <!-- Actions -->
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a  :href="`{{ route('stays.show', ':id') }}`.replace(':id', stay.id)"
                                           class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('updateAny', Stay::class)
                                        <a :href="`{{ route('stays.edit', ':id') }}`.replace(':id', stay.id)"
                                           class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                        <div class="relative" x-data="{ open: false }">
                                            <button @click="open = !open" 
                                                    class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div x-show="open" @click.outside="open = false" 
                                                 class="absolute right-0 z-10 mt-2 w-48 rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800">
                                                <div class="py-1">
                                                    <a :href="`{{ route('stays.show', ':id') }}?tab=billing`.replace(':id', stay.id)"
                                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                                                        <i class="fas fa-file-invoice-dollar mr-2"></i> View Invoice
                                                    </a>
                                                    <template x-if="stay.status === 'booked'">
                                                        <a :href="`{{ route('stays.create', '') }}/${stay.id}`" 
                                                           class="block px-4 py-2 text-sm text-green-600 hover:bg-gray-100 dark:text-green-400 dark:hover:bg-gray-700">
                                                            <i class="fas fa-door-open mr-2"></i> Check-in
                                                        </a>
                                                    </template>
                                                    <template x-if="stay.status === 'checked_in'">
                                                        <a :href="`{{ route('stays.create', '') }}/${stay.id}`" 
                                                           class="block px-4 py-2 text-sm text-orange-600 hover:bg-gray-100 dark:text-orange-400 dark:hover:bg-gray-700">
                                                            <i class="fas fa-door-closed mr-2"></i> Check-out
                                                        </a>
                                                    </template>
                                                    @can('deleteAny', Stay::class)
                                                    <button @click="deleteStay(stay.id)"
                                                            class="block w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-gray-100 dark:text-red-400 dark:hover:bg-gray-700">
                                                        <i class="fas fa-trash mr-2"></i> Delete
                                                    </button>
                                                    @endcan
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </template>
                    <template x-if="paginatedStays.length === 0">
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">
                                <div class="mx-auto max-w-md">
                                    <i class="fas fa-bed text-4xl text-gray-300 dark:text-gray-600 mb-4"></i>
                                    <h3 class="mb-2 text-lg font-medium text-gray-700 dark:text-gray-300">No stays found</h3>
                                    <p class="text-gray-500 dark:text-gray-400" x-show="search || status || roomType || dateFrom || dateTo">
                                        Try adjusting your search or filters
                                    </p>
                                    <p class="text-gray-500 dark:text-gray-400" x-show="!search && !status && !roomType && !dateFrom && !dateTo">
                                        No stays available. <a href="{{ route('stays.create') }}" class="text-primary hover:underline">Create a new stay</a>
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Table Footer -->
        <div class="flex flex-col items-center justify-between gap-4 border-t border-gray-200 px-6 py-4 dark:border-gray-800 md:flex-row">
            <!-- Showing info -->
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Showing <span class="font-medium text-gray-700 dark:text-gray-300" 
                             x-text="Math.min((currentPage - 1) * itemsPerPage + 1, filteredStays.length)"></span> 
                to <span class="font-medium text-gray-700 dark:text-gray-300" 
                         x-text="Math.min(currentPage * itemsPerPage, filteredStays.length)"></span> 
                of <span class="font-medium text-gray-700 dark:text-gray-300" x-text="filteredStays.length"></span> 
                entries
            </div>
            
            <!-- Pagination -->
            <div class="inline-flex items-center gap-1">
                <!-- Previous Button -->
                <button @click="changePage(currentPage - 1)" 
                        :disabled="currentPage === 1"
                        :class="currentPage === 1 ? 'cursor-not-allowed opacity-50' : 'hover:bg-gray-100 dark:hover:bg-gray-800'"
                        class="rounded-lg border border-gray-300 p-2 text-gray-500 dark:border-gray-700 dark:text-gray-400">
                    <i class="fas fa-chevron-left"></i>
                </button>
                
                <!-- Page Numbers -->
                <template x-for="page in Math.min(5, totalPages)" :key="page">
                    <button @click="changePage(page)" 
                            :class="currentPage === page 
                                ? 'bg-primary text-white' 
                                : 'border border-gray-300 text-gray-500 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800'"
                            class="rounded-lg px-3 py-2 text-sm font-medium"
                            x-text="page"></button>
                </template>
                
                <!-- Ellipsis for many pages -->
                <span x-show="totalPages > 5" class="px-2 text-gray-500 dark:text-gray-400">...</span>
                
                <!-- Last page if different -->
                <button x-show="totalPages > 5 && currentPage < totalPages - 2" 
                        @click="changePage(totalPages)"
                        class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-500 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800"
                        x-text="totalPages"></button>
                
                <!-- Next Button -->
                <button @click="changePage(currentPage + 1)" 
                        :disabled="currentPage === totalPages"
                        :class="currentPage === totalPages ? 'cursor-not-allowed opacity-50' : 'hover:bg-gray-100 dark:hover:bg-gray-800'"
                        class="rounded-lg border border-gray-300 p-2 text-gray-500 dark:border-gray-700 dark:text-gray-400">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Global todaysCheckins and todaysCheckouts variables (passed from controller)
const todaysCheckins = @json($todaysCheckins ?? []);
const todaysCheckouts = @json($todaysCheckouts ?? []);

// Add these to the Alpine.js data
document.addEventListener('alpine:init', () => {
    Alpine.data('stayIndex', () => ({
        todaysCheckins: todaysCheckins,
        todaysCheckouts: todaysCheckouts,
        
        // Add delete method
        async deleteStay(stayId) {
            if (!confirm('Are you sure you want to delete this stay? This action cannot be undone.')) {
                return;
            }
            
            try {
                const response = await fetch(`/stays/${stayId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Remove from stays array
                    this.stays = this.stays.filter(stay => stay.id !== stayId);
                    
                    // Show success message
                    this.showNotification('Stay deleted successfully', 'success');
                } else {
                    throw new Error(data.message || 'Failed to delete stay');
                }
            } catch (error) {
                console.error('Error deleting stay:', error);
                this.showNotification(error.message || 'Error deleting stay', 'error');
            }
        },
        
        showNotification(message, type = 'success') {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 rounded-lg p-4 text-white shadow-lg ${
                type === 'success' ? 'bg-green-500' : 
                type === 'error' ? 'bg-red-500' : 
                'bg-blue-500'
            }`;
            notification.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} mr-2"></i>
                    <span>${message}</span>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Remove after 3 seconds
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    }));
});

// Print function
function printStays() {
    const printWindow = window.open('', '_blank');
    const today = new Date().toLocaleDateString();
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Stays Report - ${today}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .header h1 { color: #333; margin-bottom: 5px; }
                .header p { color: #666; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th { background-color: #f8f9fa; text-align: left; padding: 12px; border-bottom: 2px solid #dee2e6; }
                td { padding: 10px 12px; border-bottom: 1px solid #dee2e6; }
                .status { padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500; }
                .status-checked_in { background-color: #d1e7dd; color: #0f5132; }
                .status-booked { background-color: #cfe2ff; color: #052c65; }
                .status-reserved { background-color: #fff3cd; color: #664d03; }
                .status-checked_out { background-color: #e2e3e5; color: #41464b; }
                .status-cancelled { background-color: #f8d7da; color: #842029; }
                .total-row { background-color: #f8f9fa; font-weight: bold; }
                @media print {
                    body { margin: 0; }
                    table { break-inside: avoid; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Stays Report</h1>
                <p>Generated on ${today}</p>
                <p>Total Stays: ${document.querySelector('[x-data]').__x.$data.filteredStays?.length || 0}</p>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Stay ID</th>
                        <th>Guest</th>
                        <th>Room</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Nights</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    ${(document.querySelector('[x-data]').__x.$data.filteredStays || []).map(stay => `
                        <tr>
                            <td>S${String(stay.id).padStart(5, '0')}</td>
                            <td>${stay.guest?.name || 'N/A'}</td>
                            <td>${stay.room_allocations?.[0]?.room?.room_number ? 'Room ' + stay.room_allocations[0].room.room_number : 'Not assigned'}</td>
                            <td>${new Date(stay.arrival_date).toLocaleDateString()}</td>
                            <td>${new Date(stay.departure_date).toLocaleDateString()}</td>
                            <td>${stay.nights || '0'} nights</td>
                            <td>${document.querySelector('[x-data]').__x.$data.formatCurrency(stay.total_amount)}</td>
                            <td>
                                <span class="status status-${stay.status}">
                                    ${stay.status.charAt(0).toUpperCase() + stay.status.slice(1)}
                                </span>
                            </td>
                        </tr>
                    `).join('') || '<tr><td colspan="8" style="text-align: center; padding: 20px;">No stays found</td></tr>'}
                </tbody>
            </table>
            <script>
                window.onload = function() {
                    window.print();
                    setTimeout(() => window.close(), 100);
                }
            <\/script>
        </body>
        </html>
    `);
    
    printWindow.document.close();
}
</script>
@endpush