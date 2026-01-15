@extends('layouts.app')


@section('content')
    <!-- Breadcrumb Start -->
    <div x-data="{ pageName: `Facilities Management`}">
        @include('partials.breadcrumb')
    </div>
    <!-- Breadcrumb End -->

<div x-data="facilitiesIndex()" x-init="init()">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">Facilities Management</h1>
                <p class="text-gray-500 dark:text-gray-400">Manage hotel facilities and bookings</p>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('facilities.create') }}" 
                   class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs hover:bg-primary-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Add Facility
                </a>
                <a href="{{ route('facilities.create') }}" 
                   class="inline-flex items-center gap-2 rounded-lg border border-primary bg-white px-4 py-2.5 text-theme-sm font-medium text-primary shadow-theme-xs hover:bg-primary-50 dark:border-primary dark:bg-gray-800 dark:text-primary-400 dark:hover:bg-gray-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Book Facility
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-4 mb-6 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Total Facilities</p>
                    <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90" x-text="stats.total_facilities"></p>
                </div>
                <div class="rounded-full bg-primary-50 p-3 dark:bg-primary-500/10">
                    <svg class="w-6 h-6 text-primary-600 dark:text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Available</p>
                    <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90" x-text="stats.available_facilities"></p>
                </div>
                <div class="rounded-full bg-success-50 p-3 dark:bg-success-500/10">
                    <svg class="w-6 h-6 text-success-600 dark:text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Booked Today</p>
                    <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90" x-text="stats.booked_today"></p>
                </div>
                <div class="rounded-full bg-warning-50 p-3 dark:bg-warning-500/10">
                    <svg class="w-6 h-6 text-warning-600 dark:text-warning-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Today's Revenue</p>
                    <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90" x-text="'KES ' + formatCurrency(stats.revenue_today)"></p>
                </div>
                <div class="rounded-full bg-info-50 p-3 dark:bg-info-500/10">
                    <svg class="w-6 h-6 text-info-600 dark:text-info-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Facilities Table -->
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="border-b border-gray-200 px-4 py-4 dark:border-gray-800 sm:px-6">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">All Facilities</h2>
                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Manage and view all hotel facilities</p>
                </div>
                
                <div class="flex items-center gap-3">
                    <!-- Search -->
                    <div class="relative">
                        <input
                            type="text"
                            x-model="search"
                            @input="debouncedSearch()"
                            placeholder="Search facilities..."
                            class="w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-11 pr-4 text-theme-sm font-medium text-gray-700 shadow-theme-xs placeholder:text-gray-500 focus:border-primary focus:ring-2 focus:ring-primary/10 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:placeholder:text-gray-400 dark:focus:border-primary"
                        />
                        <svg
                            class="absolute left-4 top-1/2 -translate-y-1/2 stroke-current text-gray-400"
                            width="20"
                            height="20"
                            viewBox="0 0 20 20"
                            fill="none"
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
                    
                    <!-- Filters -->
                    <select x-model="statusFilter" @change="loadData()" class="rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-theme-sm dark:border-gray-700 dark:bg-gray-800">
                        <option value="all">All Status</option>
                        <option value="available">Available</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="unavailable">Unavailable</option>
                    </select>
                    
                    <select x-model="typeFilter" @change="loadData()" class="rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-theme-sm dark:border-gray-700 dark:bg-gray-800">
                        <option value="">All Types</option>
                        <option value="conference">Conference</option>
                        <option value="pool">Pool</option>
                        <option value="gym">Gym</option>
                        <option value="spa">Spa</option>
                        <option value="restaurant">Restaurant</option>
                        <option value="bar">Bar</option>
                        <option value="meeting_room">Meeting Room</option>
                        <option value="banquet_hall">Banquet Hall</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Loading State -->
        <template x-if="loading">
            <div class="p-6">
                <div class="space-y-3">
                    <div class="h-12 bg-gray-200 animate-pulse rounded-lg dark:bg-gray-700"></div>
                    <template x-for="i in 5" :key="i">
                        <div class="h-16 bg-gray-200 animate-pulse rounded-lg dark:bg-gray-700"></div>
                    </template>
                </div>
            </div>
        </template>
        
        <!-- Facilities List -->
        <div class="p-4 sm:p-6" x-show="!loading">
            <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-800">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Facility</th>
                            <th class="px-4 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                            <th class="px-4 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Capacity</th>
                            <th class="px-4 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Rate</th>
                            <th class="px-4 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        <template x-for="facility in filteredFacilities" :key="facility.id">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="px-4 py-3">
                                    <div>
                                        <a :href="facility.view_url" class="font-medium text-gray-800 dark:text-white/90 hover:text-primary" x-text="facility.name"></a>
                                        <p class="text-theme-xs text-gray-500 dark:text-gray-400" x-text="facility.code"></p>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-theme-xs font-medium" 
                                          :class="getTypeClass(facility.type)">
                                        <span x-text="facility.type.replace('_', ' ')"></span>
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-theme-sm text-gray-800 dark:text-white/90" x-text="facility.capacity"></span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-theme-xs font-medium" 
                                          :class="getStatusClass(facility.status)">
                                        <span x-text="facility.status"></span>
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-theme-sm font-medium text-gray-800 dark:text-white/90" x-text="'KES ' + formatCurrency(facility.base_rate)"></span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <a :href="facility.view_url" 
                                           class="rounded-lg bg-primary px-3 py-1.5 text-theme-xs font-medium text-white hover:bg-primary-600">
                                            View
                                        </a>
                                        <a :href="facility.edit_url" 
                                           class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-theme-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700">
                                            Edit
                                        </a>
                                        <button @click="bookFacility(facility.id)" 
                                                class="rounded-lg bg-success px-3 py-1.5 text-theme-xs font-medium text-white hover:bg-success-600">
                                            Book
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        
                        <!-- Empty State -->
                        <template x-if="!loading && filteredFacilities.length === 0">
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    <p class="mt-2 text-gray-500 dark:text-gray-400">No facilities found</p>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-4 flex items-center justify-between" x-show="facilities.length > 0">
                <p class="text-theme-sm text-gray-500 dark:text-gray-400">
                    Showing <span x-text="(currentPage - 1) * perPage + 1"></span> to 
                    <span x-text="Math.min(currentPage * perPage, total)"></span> of 
                    <span x-text="total"></span> facilities
                </p>
                
                <div class="flex items-center gap-2">
                    <button @click="previousPage()" :disabled="currentPage === 1" 
                            class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-theme-sm hover:bg-gray-50 disabled:opacity-50 dark:border-gray-700 dark:bg-gray-800">
                        Previous
                    </button>
                    <button @click="nextPage()" :disabled="currentPage === lastPage" 
                            class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-theme-sm hover:bg-gray-50 disabled:opacity-50 dark:border-gray-700 dark:bg-gray-800">
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function facilitiesIndex() {
    return {
        facilities: [],
        stats: {
            total_facilities: 0,
            available_facilities: 0,
            booked_today: 0,
            revenue_today: 0
        },
        search: '',
        statusFilter: 'all',
        typeFilter: '',
        loading: true,
        debounceTimer: null,
        currentPage: 1,
        perPage: 10,
        total: 0,
        lastPage: 1,
        
        get filteredFacilities() {
            let filtered = this.facilities;
            
            // Apply search filter
            if (this.search.trim()) {
                const term = this.search.toLowerCase();
                filtered = filtered.filter(facility => 
                    facility.name.toLowerCase().includes(term) ||
                    facility.code.toLowerCase().includes(term) ||
                    facility.type.toLowerCase().includes(term)
                );
            }
            
            // Apply status filter
            if (this.statusFilter !== 'all') {
                filtered = filtered.filter(facility => 
                    facility.status === this.statusFilter
                );
            }
            
            // Apply type filter
            if (this.typeFilter) {
                filtered = filtered.filter(facility => 
                    facility.type === this.typeFilter
                );
            }
            
            return filtered;
        },
        
        async init() {
            await this.loadData();
        },
        
        async loadData() {
            this.loading = true;
            try {
                const params = new URLSearchParams({
                    page: this.currentPage,
                    per_page: this.perPage,
                    ...(this.statusFilter !== 'all' && { status: this.statusFilter }),
                    ...(this.typeFilter && { type: this.typeFilter }),
                    ...(this.search && { search: this.search })
                });
                
                const response = await fetch(`/facilities?${params.toString()}&ajax=1`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.facilities = data.facilities.data;
                    this.stats = data.stats;
                    this.total = data.facilities.total;
                    this.lastPage = data.facilities.last_page;
                }
            } catch (error) {
                console.error('Error loading facilities:', error);
            } finally {
                this.loading = false;
            }
        },
        
        debouncedSearch() {
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => {
                this.currentPage = 1;
                this.loadData();
            }, 500);
        },
        
        previousPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.loadData();
            }
        },
        
        nextPage() {
            if (this.currentPage < this.lastPage) {
                this.currentPage++;
                this.loadData();
            }
        },
        
        getTypeClass(type) {
            const classes = {
                'conference': 'bg-purple-100 text-purple-800 dark:bg-purple-500/15 dark:text-purple-500',
                'pool': 'bg-blue-100 text-blue-800 dark:bg-blue-500/15 dark:text-blue-500',
                'gym': 'bg-green-100 text-green-800 dark:bg-green-500/15 dark:text-green-500',
                'spa': 'bg-pink-100 text-pink-800 dark:bg-pink-500/15 dark:text-pink-500',
                'restaurant': 'bg-orange-100 text-orange-800 dark:bg-orange-500/15 dark:text-orange-500',
                'bar': 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-500',
                'meeting_room': 'bg-indigo-100 text-indigo-800 dark:bg-indigo-500/15 dark:text-indigo-500',
                'banquet_hall': 'bg-red-100 text-red-800 dark:bg-red-500/15 dark:text-red-500'
            };
            return classes[type] || 'bg-gray-100 text-gray-800 dark:bg-gray-500/15 dark:text-gray-500';
        },
        
        getStatusClass(status) {
            const classes = {
                'available': 'bg-success-100 text-success-800 dark:bg-success-500/15 dark:text-success-500',
                'maintenance': 'bg-warning-100 text-warning-800 dark:bg-warning-500/15 dark:text-warning-500',
                'unavailable': 'bg-error-100 text-error-800 dark:bg-error-500/15 dark:text-error-500'
            };
            return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-500/15 dark:text-gray-500';
        },
        
        formatCurrency(amount) {
            return new Intl.NumberFormat('en-KE', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(amount);
        },
        
        bookFacility(facilityId) {
            window.location.href = `/facilities/book?facility_id=${facilityId}`;
        }
    }
}
</script>
@endpush
@endsection