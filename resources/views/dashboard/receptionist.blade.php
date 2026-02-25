<!-- Receptionist Dashboard - Complete Blade File -->
<div 
    x-data="receptionistDashboard()"
    x-init="init()"
    @receptionist-dashboard-updated.window="handleDashboardUpdate($event.detail)"
    class="space-y-6"
>
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
        <!-- Today's Check-ins -->
        <div 
            x-data="{ value: {{ $todayArrivals }}, trend: 12 }"
            data-card="today-arrivals"
            class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] hover:shadow-lg transition-shadow duration-200"
        >
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Today's Check-ins</p>
                    <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90" x-text="value"></p>
                    <div class="mt-2 flex items-center gap-1">
                        <span 
                            x-text="stats.today_checkins_pending" 
                            class="text-theme-xs text-gray-500 dark:text-gray-400"
                        ></span>
                        <span 
                            x-show="trend > 0"
                            class="flex items-center gap-1 rounded-full bg-success-50 px-2 py-0.5 text-theme-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500"
                        >
                            <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20">
                                <path d="M10 17.5L3.75 11.25L5.25 9.75L10 14.5L14.75 9.75L16.25 11.25L10 17.5Z"/>
                            </svg>
                            <span x-text="'+' + trend + '%'"></span>
                        </span>
                    </div>
                </div>
                <div class="rounded-full bg-primary-50 p-3 dark:bg-primary-500/10">
                    <svg class="w-6 h-6 text-primary-600 dark:text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Today's Check-outs -->
        <div 
            x-data="{ value: {{ $todayDepartures }}, trend: -8 }"
            data-card="today-departures"
            class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] hover:shadow-lg transition-shadow duration-200"
        >
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Today's Check-outs</p>
                    <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90" x-text="value"></p>
                    <div class="mt-2 flex items-center gap-1">
                        <span 
                            x-text="stats.today_checkouts_pending" 
                            class="text-theme-xs text-gray-500 dark:text-gray-400"
                        ></span>
                        <span 
                            x-show="trend < 0"
                            class="flex items-center gap-1 rounded-full bg-error-50 px-2 py-0.5 text-theme-xs font-medium text-error-600 dark:bg-error-500/15 dark:text-error-500"
                        >
                            <svg class="w-3 h-3 fill-current rotate-180" viewBox="0 0 20 20">
                                <path d="M10 17.5L3.75 11.25L5.25 9.75L10 14.5L14.75 9.75L16.25 11.25L10 17.5Z"/>
                            </svg>
                            <span x-text="trend + '%'"></span>
                        </span>
                    </div>
                </div>
                <div class="rounded-full bg-error-50 p-3 dark:bg-error-500/10">
                    <svg class="w-6 h-6 text-error-600 dark:text-error-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Available Rooms -->
        <div 
            x-data="{ value: {{ $availableRooms }}, total: {{ $stats['total_rooms'] ?? 0 }} }"
            data-card="available-rooms"
            class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] hover:shadow-lg transition-shadow duration-200"
        >
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Available Rooms</p>
                    <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90" x-text="value"></p>
                    <div class="mt-2 flex items-center gap-1">
                        <span class="text-theme-xs text-gray-500 dark:text-gray-400" x-text="total + ' total rooms'"></span>
                    </div>
                </div>
                <div class="rounded-full bg-success-50 p-3 dark:bg-success-500/10">
                    <svg class="w-6 h-6 text-success-600 dark:text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Available Facilities -->
        <div 
            x-data="{ value: 0, trend: 5 }"
            data-card="available-facilities"
            class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] hover:shadow-lg transition-shadow duration-200"
        >
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Available Facilities</p>
                    <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90" x-text="facilities.filter(f => f.is_available).length"></p>
                    <div class="mt-2 flex items-center gap-1">
                        <span class="text-theme-xs text-gray-500 dark:text-gray-400" x-text="facilities.length + ' total facilities'"></span>
                        <span 
                            x-show="trend > 0"
                            class="flex items-center gap-1 rounded-full bg-info-50 px-2 py-0.5 text-theme-xs font-medium text-info-600 dark:bg-info-500/15 dark:text-info-500"
                        >
                            <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20">
                                <path d="M10 17.5L3.75 11.25L5.25 9.75L10 14.5L14.75 9.75L16.25 11.25L10 17.5Z"/>
                            </svg>
                            <span x-text="'+' + trend + '%'"></span>
                        </span>
                    </div>
                </div>
                <div class="rounded-full bg-info-50 p-3 dark:bg-info-500/10">
                    <svg class="w-6 h-6 text-info-600 dark:text-info-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="border-b border-gray-200 px-4 py-4 dark:border-gray-800 sm:px-6">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Quick Actions</h3>
                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Frequent reception tasks</p>
                </div>
                
                <!-- Action Stats -->
                <div class="flex items-center gap-4">
                    <div class="text-center">
                        <p x-text="stats.today_checkins_pending || 0" class="text-2xl font-bold text-gray-800 dark:text-white/90"></p>
                        <p class="text-theme-xs text-gray-500 dark:text-gray-400">Pending Check-ins</p>
                    </div>
                    <div class="text-center">
                        <p x-text="stats.today_checkouts_pending || 0" class="text-2xl font-bold text-gray-800 dark:text-white/90"></p>
                        <p class="text-theme-xs text-gray-500 dark:text-gray-400">Departing Today</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Action Grid -->
        <div class="p-4 sm:p-6">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                <template x-for="action in quickActions" :key="action.title">
                    <a 
                        :href="action.url"
                        class="group relative rounded-lg border border-gray-200 bg-white p-4 text-center transition-all duration-200 hover:shadow-lg hover:-translate-y-1 dark:border-gray-800 dark:bg-gray-800 dark:hover:bg-gray-700"
                    >
                        <div 
                            class="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-full"
                            :class="{
                                'bg-primary-50': action.color === 'primary',
                                'bg-success-50': action.color === 'success',
                                'bg-warning-50': action.color === 'warning',
                                'bg-info-50': action.color === 'info',
                                'bg-secondary-50': action.color === 'secondary'
                            }"
                        >
                            <span class="text-lg font-semibold" x-text="getActionIcon(action.icon)"></span>
                        </div>
                        <p class="font-medium text-gray-800 dark:text-white/90" x-text="action.title"></p>
                        <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400" x-text="action.description"></p>
                    </a>
                </template>
            </div>
        </div>
    </div>

    <!-- Facilities Booking Table -->
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="border-b border-gray-200 px-4 py-4 dark:border-gray-800 sm:px-6">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Facilities Booking</h3>
                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Manage hotel facilities</p>
                </div>
                
                <div class="flex items-center gap-3">
                    <a href="{{ route('facilities.create') }}" 
                       class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs hover:bg-primary-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Book Facility
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Facilities Table -->
        <div class="p-4 sm:p-6">
            <div x-data="facilitiesTable()" x-init="loadData()">
                <!-- Search and Filter -->
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="relative flex-1">
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
                    
                    <div class="flex items-center gap-2">
                        <select x-model="statusFilter" class="rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-theme-sm dark:border-gray-700 dark:bg-gray-800">
                            <option value="all">All Status</option>
                            <option value="available">Available</option>
                            <option value="booked">Booked</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                        
                        <select x-model="typeFilter" class="rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-theme-sm dark:border-gray-700 dark:bg-gray-800">
                            <option value="all">All Types</option>
                            <option value="conference">Conference</option>
                            <option value="pool">Pool</option>
                            <option value="gym">Gym</option>
                            <option value="spa">Spa</option>
                        </select>
                    </div>
                </div>
                
                <!-- Loading State -->
                <template x-if="loading">
                    <div class="space-y-3">
                        <div class="h-12 bg-gray-200 animate-pulse rounded-lg dark:bg-gray-700"></div>
                        <template x-for="i in 5" :key="i">
                            <div class="h-16 bg-gray-200 animate-pulse rounded-lg dark:bg-gray-700"></div>
                        </template>
                    </div>
                </template>
                
                <!-- Facilities List -->
                <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-800" x-show="!loading">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Facility</th>
                                <th class="px-4 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                                <th class="px-4 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Current Bookings</th>
                                <th class="px-4 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            <template x-for="facility in filteredFacilities" :key="facility.id">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-4 py-3">
                                        <div>
                                            <p class="font-medium text-gray-800 dark:text-white/90" x-text="facility.name"></p>
                                            <p class="text-theme-xs text-gray-500 dark:text-gray-400">
                                                Capacity: <span x-text="facility.capacity"></span>
                                            </p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-theme-xs font-medium" 
                                              :class="getFacilityTypeClass(facility.type)">
                                            <span x-text="facility.type"></span>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-theme-xs font-medium" 
                                              :class="getStatusClass(facility.status)">
                                            <span x-text="facility.status"></span>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="space-y-1">
                                            <template x-for="booking in facility.current_bookings" :key="booking.guest_name">
                                                <p class="text-theme-xs text-gray-500 dark:text-gray-400">
                                                    <span x-text="booking.guest_name"></span> • 
                                                    <span x-text="booking.start_time + ' - ' + booking.end_time"></span>
                                                </p>
                                            </template>
                                            <template x-if="facility.current_bookings.length === 0">
                                                <p class="text-theme-xs text-gray-500 dark:text-gray-400">No current bookings</p>
                                            </template>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <a :href="facility.book_url" 
                                               class="rounded-lg bg-primary px-3 py-1.5 text-theme-xs font-medium text-white hover:bg-primary-600"
                                               :class="{ 'opacity-50 cursor-not-allowed': !facility.is_available }"
                                               :disabled="!facility.is_available">
                                                Book Now
                                            </a>
                                            <a :href="facility.view_url" 
                                               class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-theme-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700">
                                                View
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            
                            <!-- Empty State -->
                            <template x-if="!loading && filteredFacilities.length === 0">
                                <tr>
                                    <td colspan="5" class="px-4 py-12 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        <p class="mt-2 text-gray-500 dark:text-gray-400">No facilities found</p>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- All Stays Management Table -->
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="border-b border-gray-200 px-4 py-4 dark:border-gray-800 sm:px-6">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">All Stays Management</h3>
                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Manage guest stays and bookings</p>
                </div>
                
                <div class="flex items-center gap-3">
                    <a href="{{ route('stays.create') }}" 
                       class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs hover:bg-primary-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        New Booking
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Stays Table -->
        <div class="p-4 sm:p-6">
            <div x-data="staysTable()" x-init="loadData()">
                <!-- Search and Filter -->
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="relative flex-1">
                        <input
                            type="text"
                            x-model="search"
                            @input="debouncedSearch()"
                            placeholder="Search stays by guest name, room number..."
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
                    
                    <div class="flex items-center gap-2">
                        <select x-model="statusFilter" class="rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-theme-sm dark:border-gray-700 dark:bg-gray-800">
                            <option value="all">All Status</option>
                            <option value="booked">Booked</option>
                            <option value="checked_in">Checked In</option>
                            <option value="checked_out">Checked Out</option>
                        </select>
                        
                        <button @click="toggleAdvancedFilters = !toggleAdvancedFilters" 
                                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            More Filters
                        </button>
                    </div>
                </div>
                
                <!-- Advanced Filters -->
                <div x-show="toggleAdvancedFilters" x-transition class="mb-4 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-800">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="mb-1 block text-theme-xs font-medium text-gray-500 dark:text-gray-400">Arrival Date</label>
                            <input type="date" x-model="arrivalDateFilter" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-theme-sm dark:border-gray-700 dark:bg-gray-800">
                        </div>
                        <div>
                            <label class="mb-1 block text-theme-xs font-medium text-gray-500 dark:text-gray-400">Departure Date</label>
                            <input type="date" x-model="departureDateFilter" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-theme-sm dark:border-gray-700 dark:bg-gray-800">
                        </div>
                        <div>
                            <label class="mb-1 block text-theme-xs font-medium text-gray-500 dark:text-gray-400">Room Type</label>
                            <select x-model="roomTypeFilter" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-theme-sm dark:border-gray-700 dark:bg-gray-800">
                                <option value="">All Types</option>
                                <option value="standard">Standard</option>
                                <option value="deluxe">Deluxe</option>
                                <option value="suite">Suite</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Loading State -->
                <template x-if="loading">
                    <div class="space-y-3">
                        <div class="h-12 bg-gray-200 animate-pulse rounded-lg dark:bg-gray-700"></div>
                        <template x-for="i in 5" :key="i">
                            <div class="h-20 bg-gray-200 animate-pulse rounded-lg dark:bg-gray-700"></div>
                        </template>
                    </div>
                </template>
                
                <!-- Stays List -->
                <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-800" x-show="!loading">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Guest</th>
                                <th class="px-4 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Dates</th>
                                <th class="px-4 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Rooms</th>
                                <th class="px-4 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount Due</th>
                                <th class="px-4 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            <template x-for="stay in filteredStays" :key="stay.id">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                                                <div class="flex h-full w-full items-center justify-center" 
                                                     :class="getStatusColorClass(stay.status)">
                                                    <span class="font-medium" x-text="stay.guest_initials"></span>
                                                </div>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-800 dark:text-white/90" x-text="stay.guest_name"></p>
                                                <p class="text-theme-xs text-gray-500 dark:text-gray-400">
                                                    <span x-text="stay.adults + ' Adult(s), ' + stay.children + ' Child(ren)'"></span>
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-theme-xs font-medium" 
                                              :class="getStatusClass(stay.status)">
                                            <span x-text="stay.status.replace('_', ' ')"></span>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div>
                                            <p class="text-theme-sm text-gray-800 dark:text-white/90">
                                                <span x-text="stay.arrival_date"></span> - <span x-text="stay.departure_date"></span>
                                            </p>
                                            <p class="text-theme-xs text-gray-500 dark:text-gray-400">
                                                Arrival: <span x-text="stay.arrival_time"></span>
                                            </p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="space-y-1">
                                            <template x-for="room in stay.rooms" :key="room.number">
                                                <p class="text-theme-xs text-gray-800 dark:text-white/90">
                                                    Room <span x-text="room.number"></span> • <span x-text="room.type"></span>
                                                </p>
                                            </template>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-theme-sm font-medium text-gray-800 dark:text-white/90">
                                            <span x-text="formatCurrency(stay.total_invoice)"></span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <template x-if="stay.is_booked">
                                                <button @click="checkInGuest(stay.id, $dispatch)" 
                                                        class="rounded-lg bg-primary px-3 py-1.5 text-theme-xs font-medium text-white hover:bg-primary-600">
                                                    Check-in
                                                </button>
                                            </template>
                                            <template x-if="stay.is_checked_in">
                                                <button @click="checkOutGuest(stay.id, $dispatch)" 
                                                        class="rounded-lg bg-error px-3 py-1.5 text-theme-xs font-medium text-white hover:bg-error-600">
                                                    Check-out
                                                </button>
                                            </template>
                                            <a :href="stay.view_url" 
                                               class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-theme-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700">
                                                View
                                            </a>
                                            <template x-if="stay.total_invoice > 0">
                                                <a :href="'{{ route('payments.create') }}?stay_id=' + stay.id" 
                                                   class="rounded-lg bg-info px-3 py-1.5 text-theme-xs font-medium text-white hover:bg-info-600">
                                                    Pay
                                                </a>
                                            </template>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            
                            <!-- Empty State -->
                            <template x-if="!loading && filteredStays.length === 0">
                                <tr>
                                    <td colspan="6" class="px-4 py-12 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <p class="mt-2 text-gray-500 dark:text-gray-400">No stays found</p>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Initialize Alpine.js components
document.addEventListener('alpine:init', () => {
    // Register all components
    Alpine.data('receptionistDashboard', receptionistDashboard);
    Alpine.data('facilitiesTable', facilitiesTable);
    Alpine.data('staysTable', staysTable);
    
    // Store for dashboard data
    Alpine.store('receptionistDashboard', {
        stats: {
            today_checkins_pending: 0,
            today_checkouts_pending: 0,
            available_rooms: 0,
            total_rooms: 0
        },
        quickActions: []
    });
});

// Main dashboard component
function receptionistDashboard() {
    return {
        stats: {
            today_checkins_pending: 0,
            today_checkouts_pending: 0,
            available_rooms: 0,
            total_rooms: {{ $stats['total_rooms'] ?? 0 }}
        },
        quickActions: [],
        facilities: [],
        rooms: [],
        refreshInterval: null,
        
        async init() {
            // Load initial data
            await this.loadDashboardData();
            
            // Start real-time updates every 30 seconds
            this.refreshInterval = setInterval(() => {
                this.loadDashboardData();
            }, 30000);
        },
        
        async loadDashboardData() {
            try {
                const response = await fetch('{{ route("dashboard") }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.updateDashboard(data);
                }
            } catch (error) {
                console.error('Error loading dashboard data:', error);
            }
        },
        
        updateDashboard(data) {
            if (data.success) {
                this.stats = {
                    ...this.stats,
                    ...data.stats
                };
                this.quickActions = data.quickActions;
                this.facilities = data.facilities;
                this.rooms = data.rooms;
                
                // Update store
                Alpine.store('receptionistDashboard').stats = this.stats;
                Alpine.store('receptionistDashboard').quickActions = this.quickActions;
                
                // Update individual cards
                this.updateCardValue('today-arrivals', data.stats.today_arrivals || 0);
                this.updateCardValue('today-departures', data.stats.today_departures || 0);
                this.updateCardValue('available-rooms', data.stats.available_rooms || 0);
            }
        },
        
        updateCardValue(cardSelector, value) {
            const card = document.querySelector(`[data-card="${cardSelector}"]`);
            if (card && card.__x) {
                card.__x.$data.value = value;
                
                // Add update animation
                card.classList.add('ring-2', 'ring-primary-200', 'dark:ring-primary-500/20');
                setTimeout(() => {
                    card.classList.remove('ring-2', 'ring-primary-200', 'dark:ring-primary-500/20');
                }, 1000);
            }
        },
        
        handleDashboardUpdate(data) {
            // Handle updates from other components
            if (data.stats) {
                this.stats = { ...this.stats, ...data.stats };
            }
        },
        
        getActionIcon(icon) {
            const icons = {
                'new-booking': '📋',
                'walk-in': '👤',
                'room-status': '🏨',
                'payment': '💳',
                'facility': '🏊',
                'check-in': '✅'
            };
            return icons[icon] || '⚡';
        }
    }
}

// Facilities table component
function facilitiesTable() {
    return {
        facilities: [],
        search: '',
        statusFilter: 'all',
        typeFilter: 'all',
        loading: false,
        debounceTimer: null,
        
        get filteredFacilities() {
            let filtered = this.facilities;
            
            // Apply search filter
            if (this.search.trim()) {
                const term = this.search.toLowerCase();
                filtered = filtered.filter(facility => 
                    facility.name.toLowerCase().includes(term) ||
                    facility.type.toLowerCase().includes(term)
                );
            }
            
            // Apply status filter
            if (this.statusFilter !== 'all') {
                filtered = filtered.filter(facility => 
                    facility.status.toLowerCase() === this.statusFilter.toLowerCase()
                );
            }
            
            // Apply type filter
            if (this.typeFilter !== 'all') {
                filtered = filtered.filter(facility => 
                    facility.type.toLowerCase() === this.typeFilter.toLowerCase()
                );
            }
            
            return filtered;
        },
        
        async loadData() {
            this.loading = true;
            try {
                const response = await fetch('{{ route("dashboard") }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.facilities = data;
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
                // Search will auto-update via computed property
            }, 300);
        },
        
        getFacilityTypeClass(type) {
            const classes = {
                'conference': 'bg-purple-100 text-purple-800 dark:bg-purple-500/15 dark:text-purple-500',
                'pool': 'bg-blue-100 text-blue-800 dark:bg-primary/15 dark:text-blue-500',
                'gym': 'bg-green-100 text-green-800 dark:bg-green-500/15 dark:text-green-500',
                'spa': 'bg-pink-100 text-pink-800 dark:bg-pink-500/15 dark:text-pink-500'
            };
            return classes[type.toLowerCase()] || 'bg-gray-100 text-gray-800 dark:bg-gray-500/15 dark:text-gray-500';
        },
        
        getStatusClass(status) {
            const classes = {
                'available': 'bg-success-100 text-success-800 dark:bg-success-500/15 dark:text-success-500',
                'booked': 'bg-warning-100 text-warning-800 dark:bg-warning-500/15 dark:text-warning-500',
                'maintenance': 'bg-error-100 text-error-800 dark:bg-error-500/15 dark:text-error-500',
                'in_use': 'bg-primary-100 text-primary-800 dark:bg-primary-500/15 dark:text-primary-500'
            };
            return classes[status.toLowerCase()] || 'bg-gray-100 text-gray-800 dark:bg-gray-500/15 dark:text-gray-500';
        }
    }
}

// Stays table component
function staysTable() {
    return {
        stays: [],
        search: '',
        statusFilter: 'all',
        arrivalDateFilter: '',
        departureDateFilter: '',
        roomTypeFilter: '',
        toggleAdvancedFilters: false,
        loading: false,
        debounceTimer: null,
        
        get filteredStays() {
            let filtered = this.stays;
            
            // Apply search filter
            if (this.search.trim()) {
                const term = this.search.toLowerCase();
                filtered = filtered.filter(stay => 
                    stay.guest_name.toLowerCase().includes(term) ||
                    stay.rooms.some(room => 
                        room.number.toLowerCase().includes(term) ||
                        room.type.toLowerCase().includes(term)
                    )
                );
            }
            
            // Apply status filter
            if (this.statusFilter !== 'all') {
                const statusMap = {
                    'booked': 'booked',
                    'checked_in': 'checked_in',
                    'checked_out': 'checked_out'
                };
                const targetStatus = statusMap[this.statusFilter];
                filtered = filtered.filter(stay => stay.status === targetStatus);
            }
            
            // Apply arrival date filter
            if (this.arrivalDateFilter) {
                filtered = filtered.filter(stay => 
                    stay.arrival_date.includes(this.arrivalDateFilter)
                );
            }
            
            // Apply departure date filter
            if (this.departureDateFilter) {
                filtered = filtered.filter(stay => 
                    stay.departure_date.includes(this.departureDateFilter)
                );
            }
            
            // Apply room type filter
            if (this.roomTypeFilter) {
                filtered = filtered.filter(stay => 
                    stay.rooms.some(room => 
                        room.type.toLowerCase().includes(this.roomTypeFilter.toLowerCase())
                    )
                );
            }
            
            return filtered;
        },
        
        async loadData() {
            this.loading = true;
            try {
                const response = await fetch('{{ route("dashboard") }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.stays = data;
                }
            } catch (error) {
                console.error('Error loading stays:', error);
            } finally {
                this.loading = false;
            }
        },
        
        debouncedSearch() {
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => {
                // Search will auto-update via computed property
            }, 300);
        },
        
        getStatusClass(status) {
            const classes = {
                'booked': 'bg-warning-100 text-warning-800 dark:bg-warning-500/15 dark:text-warning-500',
                'checked_in': 'bg-success-100 text-success-800 dark:bg-success-500/15 dark:text-success-500',
                'checked_out': 'bg-gray-100 text-gray-800 dark:bg-gray-500/15 dark:text-gray-500'
            };
            return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-500/15 dark:text-gray-500';
        },
        
        getStatusColorClass(status) {
            const classes = {
                'booked': 'bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-500',
                'checked_in': 'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-500',
                'checked_out': 'bg-gray-50 text-gray-600 dark:bg-gray-500/10 dark:text-gray-500'
            };
            return classes[status] || 'bg-gray-50 text-gray-600 dark:bg-gray-500/10 dark:text-gray-500';
        },
        
        formatCurrency(amount) {
            return new Intl.NumberFormat('en-KE', {
                style: 'currency',
                currency: 'KES'
            }).format(amount);
        },
        
        async checkInGuest(stayId, dispatch) {
            const button = event.target;
            const originalText = button.textContent;
            
            button.textContent = 'Processing...';
            button.disabled = true;
            button.classList.add('opacity-75');
            
            try {
                const response = await fetch(`/api/stays/${stayId}/check-in`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification('Guest checked in successfully!', 'success');
                    
                    // Refresh data
                    await this.loadData();
                    
                    // Update dashboard
                    dispatch('receptionist-dashboard-updated', {
                        stats: { today_checkins_pending: this.stays.filter(s => s.is_booked).length }
                    });
                } else {
                    showNotification(data.message || 'Error checking in guest', 'error');
                }
            } catch (error) {
                console.error('Error checking in guest:', error);
                showNotification('Error checking in guest', 'error');
            } finally {
                button.textContent = originalText;
                button.disabled = false;
                button.classList.remove('opacity-75');
            }
        },
        
        async checkOutGuest(stayId, dispatch) {
            if (!confirm('Are you sure you want to check out this guest?')) {
                return;
            }
            
            const button = event.target;
            const originalText = button.textContent;
            
            button.textContent = 'Processing...';
            button.disabled = true;
            button.classList.add('opacity-75');
            
            try {
                const response = await fetch(`/api/stays/${stayId}/check-out`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification('Guest checked out successfully!', 'success');
                    
                    // Refresh data
                    await this.loadData();
                    
                    // Update dashboard
                    dispatch('receptionist-dashboard-updated', {
                        stats: { today_checkouts_pending: this.stays.filter(s => s.is_checked_in).length }
                    });
                } else {
                    showNotification(data.message || 'Error checking out guest', 'error');
                }
            } catch (error) {
                console.error('Error checking out guest:', error);
                showNotification('Error checking out guest', 'error');
            } finally {
                button.textContent = originalText;
                button.disabled = false;
                button.classList.remove('opacity-75');
            }
        }
    }
}

// Utility functions
function showNotification(message, type = 'info') {
    // Use existing notification system or create simple alert
    alert(`${type.toUpperCase()}: ${message}`);
}
</script>
@endpush