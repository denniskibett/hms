
<script>
    window.STAY_DATA = {
        stays: @json($stays),
        rooms: @json($allRooms ?? []),
        roomAllocations: @json($roomAllocations ?? []),
        guestId: {{ $guest->id ?? 'null' }},
        guestUserId: {{ $guest->user->id ?? 'null' }},
        guestName: "{{ $guest->user->name ?? '' }}",
        currency: "{{ $currencySymbol ?? SystemHelper::currencySymbol() }}",
        csrfToken: '{{ csrf_token() }}',
        storeRoute: '{{ $storeRoute ?? (isset($guest) ? route("guest.stays.store", $guest) : route("stays.store")) }}',
        checkinRoute: '{{ $checkinRoute ?? (isset($guest) ? route("guest.stays.checkin", [$guest, ":stayId"]) : route("stays.checkin", ":stayId")) }}',
        checkoutRoute: '{{ $checkoutRoute ?? (isset($guest) ? route("guest.stays.checkout", [$guest, ":stayId"]) : route("stays.checkout", ":stayId")) }}',
        dateFormat: '{{ $dateFormat ?? SystemHelper::dateFormat() }}',
        // Configuration
        showGuestColumn: {{ $showGuestColumn ?? (isset($guest) ? 'false' : 'true') }},
        showActions: {{ $showActions ?? 'true' }},
        showSummary: {{ $showSummary ?? 'true' }},
        showAddButton: {{ $showAddButton ?? 'true' }},
        isGlobalStaysIndex: {{ isset($guest) ? 'false' : 'true' }}
    };
</script>

<div x-data="stayTable()" x-init="init()">
    <!-- Add New Stay Button -->
    <div class="mb-5 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">
            Stay History
        </h2>
        <div class="flex items-center gap-4">
            <!-- Date Range Filter -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" type="button"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-800 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-white/[0.03] dark:hover:text-white">
                    <i class="fas fa-calendar-alt"></i>
                    <span x-text="dateRangeText"></span>
                </button>
                
                <!-- Date Range Dropdown -->
                <div x-show="open" @click.outside="open = false" x-transition
                     class="absolute right-0 top-full mt-2 w-80 rounded-lg border border-gray-200 bg-white p-4 shadow-lg dark:border-gray-700 dark:bg-gray-800 z-50"
                     x-cloak>
                    <div class="mb-4">
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            From Date
                        </label>
                        <div class="relative">
                            <input type="date" x-model="filters.date_from" 
                                   class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            To Date
                        </label>
                        <div class="relative">
                            <input type="date" x-model="filters.date_to" 
                                   class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        </div>
                    </div>
                    <div class="flex justify-between gap-2">
                        <button @click="clearDateFilter()" type="button"
                                class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm hover:bg-gray-50 dark:border-gray-600">
                            Clear
                        </button>
                        <button @click="applyDateFilter()" type="button"
                                class="flex-1 rounded-lg bg-primary px-3 py-2 text-sm text-white hover:bg-primary/90">
                            Apply
                        </button>
                    </div>
                </div>
            </div>
            
            <button @click="openModal()" 
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-800 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-white/[0.03] dark:hover:text-white">
                <i class="fas fa-plus"></i> Add New Stay
            </button>
        </div>
    </div>

    <!-- Stays Table -->
    <div class="overflow-hidden rounded-2xl border border-gray-100 dark:border-gray-800">
        <div class="custom-scrollbar overflow-x-auto">
            <table class="min-w-full text-left text-sm text-gray-700 dark:border-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr class="border-b border-gray-100 whitespace-nowrap dark:border-gray-800">
                        <th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
                            Stay ID
                        </th>
                        <th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-500 dark:text-gray-400">
                            Room
                        </th>
                        <th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
                            Arrival Date
                        </th>
                        <th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
                            Departure Date
                        </th>
                        <th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
                            Nights
                        </th>
                        <th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
                            Adults
                        </th>
                        <th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
                            Children
                        </th>
                        <th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
                            Status
                        </th>
                        <th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-white/[0.03]">
                    <template x-for="stay in filteredStays" :key="stay.id">
                        <tr>
                            <td class="px-5 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                S<span x-text="String(stay.id).padStart(5, '0')"></span>
                            </td>
                            <!-- SHOW ACTUAL ALLOCATED ROOMS -->
                            <td class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-800 dark:text-white/90">
                                <template x-if="stay.room_allocations && stay.room_allocations.length > 0">
                                    <div class="space-y-2">
                                        <template x-for="allocation in stay.room_allocations" :key="allocation.id">
                                            <div class="flex items-center">
                                                <div class="w-3 h-3 rounded-full bg-primary mr-2 flex-shrink-0"></div>
                                                <div class="min-w-0">
                                                    <div class="font-medium">
                                                        Room <span x-text="allocation.room?.room_number || 'N/A'"></span>
                                                        <template x-if="allocation.guest_notes">
                                                            <span class="ml-2 text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-2 py-0.5 rounded" 
                                                                  x-text="allocation.guest_notes"></span>
                                                        </template>
                                                    </div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                        <span x-text="allocation.room?.room_type?.name || ''"></span>
                                                        • <span x-text="allocation.adults"></span>A, <span x-text="allocation.children"></span>C
                                                        • <span x-text="currency"></span><span x-text="allocation.rate_applied"></span>/night
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="!stay.room_allocations || stay.room_allocations.length === 0">
                                    <span class="text-gray-500 dark:text-gray-400 italic">No rooms assigned</span>
                                </template>
                            </td>
                            <td class="px-5 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                <span x-text="formatDate(stay.arrival_date)"></span>
                                <template x-if="stay.check_in">
                                    <div class="text-xs text-gray-400 dark:text-gray-500">
                                        <i class="fas fa-clock mr-1"></i>
                                        <span x-text="formatTime(stay.check_in)"></span>
                                    </div>
                                </template>
                            </td>
                            <td class="px-5 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                <span x-text="formatDate(stay.departure_date)"></span>
                                <template x-if="stay.check_out">
                                    <div class="text-xs text-gray-400 dark:text-gray-500">
                                        <i class="fas fa-clock mr-1"></i>
                                        <span x-text="formatTime(stay.check_out)"></span>
                                    </div>
                                </template>
                            </td>
                            <td class="px-5 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                <span x-text="calculateNights(stay.arrival_date, stay.departure_date)"></span>
                            </td>
                            <td class="px-5 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                <span x-text="String(stay.adults)"></span>
                            </td>
                            <td class="px-5 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                <span x-text="String(stay.children)"></span>
                            </td>
                            <td class="px-5 py-4 text-sm whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="{
                                        'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500': stay.status === 'checked_in',
                                        'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-orange-400': stay.status === 'booked',
                                        'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-orange-400': stay.status === 'reserved',
                                        'bg-danger-50 text-danger-600 dark:bg-danger-500/15 dark:text-danger-500': ['checked_out', 'cancelled'].includes(stay.status)
                                      }">
                                    <span x-text="formatStatus(stay.status)"></span>
                                </span>
                            </td>
                            <td class="px-5 py-4 text-sm whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <a :href="`/stays/${stay.id}`" 
                                       class="inline-flex items-center gap-1 rounded-lg bg-primary/10 px-2.5 py-1 text-xs font-medium text-primary hover:bg-primary/20">
                                        <i class="fas fa-eye text-xs"></i>
                                        View
                                    </a>
                                    <template x-if="stay.status === 'booked' || stay.status === 'reserved'">
                                        <button @click="checkinStay(stay.id)"
                                                class="inline-flex items-center gap-1 rounded-lg bg-success/10 px-2.5 py-1 text-xs font-medium text-success hover:bg-success/20">
                                            <i class="fas fa-door-open text-xs"></i>
                                            Check-in
                                        </button>
                                    </template>
                                    <template x-if="stay.status === 'checked_in'">
                                        <button @click="checkoutStay(stay.id)"
                                                class="inline-flex items-center gap-1 rounded-lg bg-danger/10 px-2.5 py-1 text-xs font-medium text-danger hover:bg-danger/20">
                                            <i class="fas fa-door-closed text-xs"></i>
                                            Check-out
                                        </button>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <template x-if="!filteredStays || filteredStays.length === 0">
                        <tr>
                            <td colspan="9" class="px-5 py-8 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                                    <i class="fas fa-bed text-4xl mb-3"></i>
                                    <p class="text-lg">No stays found</p>
                                    <p class="text-sm mt-1" x-text="dateRangeText !== 'All Dates' ? 'No stays in selected date range' : 'This guest has no stay history yet'"></p>
                                    <button @click="openModal()" 
                                            class="mt-4 inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/90">
                                        <i class="fas fa-plus"></i> Add First Stay
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Stay Summary -->
    <template x-if="filteredStays && filteredStays.length > 0">
        <div class="mt-6 flex flex-wrap justify-between sm:justify-end">
            <div class="mt-6 w-full space-y-1 text-right sm:w-[220px]">
                <p class="mb-4 text-left text-sm font-medium text-gray-800 dark:text-white/90">
                    Stay Summary
                </p>
                <ul class="space-y-2">
                    <li class="flex justify-between gap-5">
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            Total Stays
                        </span>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-400" x-text="filteredStays.length"></span>
                    </li>
                    <li class="flex justify-between gap-5">
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            Total Rooms
                        </span>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-400" x-text="totalRooms"></span>
                    </li>
                    <li class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            Total Nights:
                        </span>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-400" x-text="totalNights"></span>
                    </li>
                    <li class="flex items-center justify-between">
                        <span class="font-medium text-gray-700 dark:text-gray-400">
                            Average Nights
                        </span>
                        <span class="text-lg font-semibold text-gray-800 dark:text-white/90" x-text="averageNights"></span>
                    </li>
                </ul>
            </div>
        </div>
    </template>

    <!-- Add Stay Modal -->
    <div x-show="isModalOpen" x-transition class="fixed inset-0 z-[99999] flex items-center justify-center p-5 overflow-y-auto" x-cloak>
        <div class="fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-[32px] dark:bg-gray-900/80" @click="closeModal()"></div>
        
        <div class="relative w-full max-w-4xl rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-10 max-h-[90vh] overflow-y-auto" @click.outside="closeModal()">
            
            <!-- Close Button -->
            <button @click="closeModal()" class="group absolute right-3 top-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-200 text-gray-500 transition-colors hover:bg-gray-300 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200 sm:right-6 sm:top-6 sm:h-11 sm:w-11">
                <svg class="transition-colors fill-current group-hover:text-gray-600 dark:group-hover:text-gray-200" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z" fill=""/>
                </svg>
            </button>

            <h4 class="mb-6 text-lg font-medium text-gray-800 dark:text-white/90">
                Add New Stay for {{ $guest->user->name ?? 'Guest' }}
            </h4>

            <form @submit.prevent="submitForm()">
                <!-- Dates Section -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
                    <!-- Arrival Date -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Arrival Date *
                        </label>
                        <div class="relative" x-data="{ 
                            showPicker: false,
                            togglePicker() { 
                                this.showPicker = !this.showPicker; 
                                if (this.showPicker) {
                                    this.$nextTick(() => {
                                        const input = this.$el.querySelector('input');
                                        if (input) input.showPicker();
                                    });
                                }
                            }
                        }">
                            <input
                                type="date"
                                x-model="formData.arrival_date"
                                required
                                :min="dateValidation.minArrival"
                                @change="onDateChange()"
                                class="dark:bg-dark-900 datepickerTwo shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                            />
                            <span 
                                class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-gray-500 dark:text-gray-400 cursor-pointer"
                                @click="$event.preventDefault(); togglePicker()"
                            >
                                <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6.66659 1.5415C7.0808 1.5415 7.41658 1.87729 7.41658 2.2915V2.99984H12.5833V2.2915C12.5833 1.87729 12.919 1.5415 13.3333 1.5415C13.7475 1.5415 14.0833 1.87729 14.0833 2.2915V2.99984L15.4166 2.99984C16.5212 2.99984 17.4166 3.89527 17.4166 4.99984V7.49984V15.8332C17.4166 16.9377 16.5212 17.8332 15.4166 17.8332H4.58325C3.47868 17.8332 2.58325 16.9377 2.58325 15.8332V7.49984V4.99984C2.58325 3.89527 3.47868 2.99984 4.58325 2.99984L5.91659 2.99984V2.2915C5.91659 1.87729 6.25237 1.5415 6.66659 1.5415ZM6.66659 4.49984H4.58325C4.30711 4.49984 4.08325 4.7237 4.08325 4.99984V6.74984H15.9166V4.99984C15.9166 4.7237 15.6927 4.49984 15.4166 4.49984H13.3333H6.66659ZM15.9166 8.24984H4.08325V15.8332C4.08325 16.1093 4.30711 16.3332 4.58325 16.3332H15.4166C15.6927 16.3332 15.9166 16.1093 15.9166 15.8332V8.24984Z" fill=""/>
                                </svg>
                            </span>
                        </div>
                    </div>

                    <!-- Departure Date -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Departure Date *
                        </label>
                        <div class="relative" x-data="{ 
                            showPicker: false,
                            togglePicker() { 
                                this.showPicker = !this.showPicker; 
                                if (this.showPicker) {
                                    this.$nextTick(() => {
                                        const input = this.$el.querySelector('input');
                                        if (input) input.showPicker();
                                    });
                                }
                            }
                        }">
                            <input
                                type="date"
                                x-model="formData.departure_date"
                                required
                                :min="dateValidation.minDeparture"
                                @change="onDateChange()"
                                class="dark:bg-dark-900 datepickerTwo shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                            />
                            <span 
                                class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-gray-500 dark:text-gray-400 cursor-pointer"
                                @click="$event.preventDefault(); togglePicker()"
                            >
                                <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6.66659 1.5415C7.0808 1.5415 7.41658 1.87729 7.41658 2.2915V2.99984H12.5833V2.2915C12.5833 1.87729 12.919 1.5415 13.3333 1.5415C13.7475 1.5415 14.0833 1.87729 14.0833 2.2915V2.99984L15.4166 2.99984C16.5212 2.99984 17.4166 3.89527 17.4166 4.99984V7.49984V15.8332C17.4166 16.9377 16.5212 17.8332 15.4166 17.8332H4.58325C3.47868 17.8332 2.58325 16.9377 2.58325 15.8332V7.49984V4.99984C2.58325 3.89527 3.47868 2.99984 4.58325 2.99984L5.91659 2.99984V2.2915C5.91659 1.87729 6.25237 1.5415 6.66659 1.5415ZM6.66659 4.49984H4.58325C4.30711 4.49984 4.08325 4.7237 4.08325 4.99984V6.74984H15.9166V4.99984C15.9166 4.7237 15.6927 4.49984 15.4166 4.49984H13.3333H6.66659ZM15.9166 8.24984H4.08325V15.8332C4.08325 16.1093 4.30711 16.3332 4.58325 16.3332H15.4166C15.6927 16.3332 15.9166 16.1093 15.9166 15.8332V8.24984Z" fill=""/>
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Room Allocations Section -->
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h5 class="text-sm font-medium text-gray-700 dark:text-gray-400">
                            Room Allocations *
                        </h5>
                        <button type="button" @click="addRoomAllocation()"
                                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-theme-xs transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                            <i class="fas fa-plus text-xs"></i> Add Another Room
                        </button>
                    </div>

                    <template x-for="(allocation, index) in formData.room_allocations" :key="index">
                        <div class="relative p-4 border border-gray-200 dark:border-gray-700 rounded-lg mb-4">
                            <!-- Remove button for non-first allocation -->
                            <template x-if="formData.room_allocations.length > 1">
                                <button type="button" @click="removeRoomAllocation(index)"
                                        class="absolute right-3 top-3 text-gray-400 hover:text-red-500">
                                    <i class="fas fa-times"></i>
                                </button>
                            </template>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Room Selection -->
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">
                                        Room *
                                    </label>
                                    <select x-model="allocation.room_id" required
                                            @change="onRoomChange(index)"
                                            class="dark:bg-dark-900 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                        <option value="">Select Room</option>
                                        <template x-for="room in getAvailableRoomsForAllocation(index)" :key="room.id">
                                            <option :value="room.id">
                                                <template x-if="room.is_available">
                                                    <span x-text="`${room.room_number} - ${room.room_type?.name || 'N/A'}`"></span>
                                                    <span x-text="` (${currency} ${room.room_type?.base_rate || 0}/night)`"></span>
                                                </template>
                                                <template x-if="!room.is_available && room.next_available_date">
                                                    <span x-text="`${room.room_number} - ${room.room_type?.name || 'N/A'}`"></span>
                                                    <span x-text="` (${currency} ${room.room_type?.base_rate || 0}/night)`"></span>
                                                    <span class="text-red-500"> - Available from <span x-text="formatDisplayDate(room.next_available_date)"></span></span>
                                                </template>
                                                <template x-if="!room.is_available && !room.next_available_date">
                                                    <span x-text="`${room.room_number} - ${room.room_type?.name || 'N/A'}`"></span>
                                                    <span class="text-red-500"> - Not available</span>
                                                </template>
                                            </option>
                                        </template>
                                    </select>
                                    <template x-if="allocation.room_id">
                                        <div class="mt-1 text-xs text-gray-500">
                                            <span x-text="getRoomDetails(allocation.room_id)?.room_type?.capacity || 0"></span> person capacity
                                            • <span x-text="getRoomDetails(allocation.room_id)?.room_type?.bed_type || 'Double'"></span> bed
                                        </div>
                                    </template>
                                </div>

                                <!-- Guest Notes -->
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">
                                        Guest Notes (Optional)
                                    </label>
                                    <input type="text" x-model="allocation.guest_notes"
                                           class="dark:bg-dark-900 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                           placeholder="e.g., Parents room, Child 1, etc.">
                                </div>

                                <!-- Adults -->
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">
                                        Adults *
                                    </label>
                                    <input type="number" x-model="allocation.adults" min="1" max="4" required
                                           @change="validateRoomCapacity(index)"
                                           class="dark:bg-dark-900 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                </div>

                                <!-- Children -->
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">
                                        Children
                                    </label>
                                    <input type="number" x-model="allocation.children" min="0" max="4"
                                           @change="validateRoomCapacity(index)"
                                           class="dark:bg-dark-900 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                </div>

                                <!-- Rate -->
                                <div class="sm:col-span-2">
                                    <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">
                                        Daily Rate <span x-text="currency"></span> (per adult)
                                    </label>
                                    <input type="number" x-model="allocation.rate" step="0.01" min="0"
                                           class="dark:bg-dark-900 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        Children: 50% of adult rate. Leave empty to use room's default rate.
                                    </p>
                                </div>

                                <!-- Room Total Preview -->
                                <div class="sm:col-span-2">
                                    <div class="p-2 bg-gray-50 dark:bg-gray-800 rounded text-xs">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 dark:text-gray-400">Room Total:</span>
                                            <span class="font-medium">
                                                <span x-text="currency"></span> <span x-text="calculateRoomTotal(allocation) || '0.00'"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Total Preview -->
                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg mb-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h6 class="text-sm font-medium text-gray-700 dark:text-gray-300">Total Stay Amount</h6>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                <span x-text="calculateNights(formData.arrival_date, formData.departure_date) || 0"></span> nights
                                × <span x-text="formData.room_allocations.length"></span> rooms
                            </p>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-primary">
                                <span x-text="currency"></span> <span x-text="calculateTotalStayAmount() || '0.00'"></span>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                Total Guests: <span x-text="formData.room_allocations.reduce((sum, a) => sum + (parseInt(a.adults) || 0) + (parseInt(a.children) || 0), 0)"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status and Special Requests -->
                <div class="grid grid-cols-1 gap-6 mb-6">
                    <!-- Status -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Status *
                        </label>
                        <select x-model="formData.status" required
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            <option value="reserved">Reserved</option>
                            <option value="booked">Booked</option>
                            <option value="checked_in">Checked In</option>
                        </select>
                    </div>

                    <!-- Special Requests -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Special Requests
                        </label>
                        <textarea x-model="formData.special_requests" rows="3"
                                  class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                  placeholder="Any special requests or requirements for the entire stay..."></textarea>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex w-full items-center justify-end gap-3 mt-6">
                    <button type="button"
                            @click="closeModal()"
                            class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:w-auto">
                        Close
                    </button>
                    <button type="submit"
                            :disabled="!formData.arrival_date || !formData.departure_date || isLoading"
                            :class="{ 'opacity-50 cursor-not-allowed': !formData.arrival_date || !formData.departure_date || isLoading }"
                            class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg bg-primary shadow-theme-xs hover:bg-primary/90 sm:w-auto">
                        <span x-show="!isLoading">Create Stay</span>
                        <span x-show="isLoading" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Creating...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Define Alpine.js component for multi-room stays
window.stayTableComponent = () => ({
    // Modal state
    isModalOpen: false,
    isLoading: false,
    
    // Multi-room form data
    formData: {
        arrival_date: '',
        departure_date: '',
        room_allocations: [
            {
                room_id: '',
                adults: 1,
                children: 0,
                rate: '',
                guest_notes: ''
            }
        ],
        special_requests: '',
        status: 'reserved',
    },
    
    // Date validation
    dateValidation: {
        minArrival: new Date().toISOString().split('T')[0],
        minDeparture: new Date(Date.now() + 86400000).toISOString().split('T')[0]
    },
    
    // Filter state
    filters: {
        date_from: '',
        date_to: ''
    },
    
    // Data from window object
    allStays: window.STAY_DATA.stays || [],
    allRooms: window.STAY_DATA.rooms || [],
    allRoomAllocations: window.STAY_DATA.roomAllocations || [],
    guestId: window.STAY_DATA.guestId,
    guestUserId: window.STAY_DATA.guestUserId,
    currency: window.STAY_DATA.currency,
    csrfToken: window.STAY_DATA.csrfToken,
    storeRoute: window.STAY_DATA.storeRoute,
    checkinRoute: window.STAY_DATA.checkinRoute,
    checkoutRoute: window.STAY_DATA.checkoutRoute,
    dateFormat: window.STAY_DATA.dateFormat || 'en-US',
    
    // Computed properties
    get filteredStays() {
        if (!this.filters.date_from && !this.filters.date_to) {
            return this.allStays;
        }
        
        return this.allStays.filter(stay => {
            const arrivalDate = new Date(stay.arrival_date);
            const departureDate = new Date(stay.departure_date);
            
            let matches = true;
            
            if (this.filters.date_from) {
                const filterFrom = new Date(this.filters.date_from);
                matches = matches && arrivalDate >= filterFrom;
            }
            
            if (this.filters.date_to) {
                const filterTo = new Date(this.filters.date_to);
                matches = matches && departureDate <= filterTo;
            }
            
            return matches;
        });
    },
    
    get dateRangeText() {
        if (!this.filters.date_from && !this.filters.date_to) {
            return 'All Dates';
        }
        
        const from = this.filters.date_from ? this.formatDisplayDate(this.filters.date_from) : 'Any';
        const to = this.filters.date_to ? this.formatDisplayDate(this.filters.date_to) : 'Any';
        
        return `${from} - ${to}`;
    },
    
    get totalNights() {
        return this.filteredStays.reduce((total, stay) => {
            return total + this.calculateNights(stay.arrival_date, stay.departure_date);
        }, 0);
    },
    
    get totalRooms() {
        return this.filteredStays.reduce((total, stay) => {
            return total + (stay.room_allocations?.length || 0);
        }, 0);
    },
    
    get averageNights() {
        if (this.filteredStays.length === 0) return 0;
        return (this.totalNights / this.filteredStays.length).toFixed(1);
    },
    
    // Initialization
    init() {
        this.setDefaultDates();
        
        // Watch for arrival date changes to update min departure date
        this.$watch('formData.arrival_date', (value) => {
            if (value) {
                const arrivalDate = new Date(value);
                const nextDay = new Date(arrivalDate);
                nextDay.setDate(nextDay.getDate() + 1);
                this.dateValidation.minDeparture = nextDay.toISOString().split('T')[0];
                
                // Reset departure date if it's before arrival
                if (this.formData.departure_date && this.formData.departure_date < value) {
                    this.formData.departure_date = '';
                }
                
                // Update room availability
                this.updateAllRoomAvailability();
            }
        });
        
        // Watch for departure date changes
        this.$watch('formData.departure_date', (value) => {
            if (value && this.formData.arrival_date) {
                this.updateAllRoomAvailability();
            }
        });
    },
    
    setDefaultDates() {
        const today = new Date().toISOString().split('T')[0];
        const tomorrow = new Date(Date.now() + 86400000).toISOString().split('T')[0];
        
        if (!this.formData.arrival_date) {
            this.formData.arrival_date = today;
        }
        if (!this.formData.departure_date) {
            this.formData.departure_date = tomorrow;
        }
    },
    
    // Check if room is available for selected dates
    isRoomAvailable(roomId, arrivalDate, departureDate) {
        if (!arrivalDate || !departureDate) return true;
        
        const checkIn = new Date(arrivalDate);
        const checkOut = new Date(departureDate);
        
        // Find conflicting allocations for this room
        const conflictingAllocations = this.allRoomAllocations.filter(allocation => {
            // Check if the room ID matches
            if (parseInt(allocation.room_id) !== parseInt(roomId)) {
                return false;
            }
            
            const allocationFrom = new Date(allocation.from_date);
            const allocationTo = new Date(allocation.to_date);
            
            // Check for date overlap
            return (
                (checkIn >= allocationFrom && checkIn < allocationTo) || // Check-in during allocation
                (checkOut > allocationFrom && checkOut <= allocationTo) || // Check-out during allocation
                (checkIn <= allocationFrom && checkOut >= allocationTo) // Allocation completely within stay
            );
        });
        
        return conflictingAllocations.length === 0;
    },
    
    // Get next available date for a room
    getNextAvailableDate(roomId) {
        const allocations = this.allRoomAllocations
            .filter(allocation => parseInt(allocation.room_id) === parseInt(roomId))
            .sort((a, b) => new Date(a.to_date) - new Date(b.to_date));
        
        if (allocations.length === 0) {
            return null; // Available now
        }
        
        // Get the latest allocation end date
        const latestAllocation = allocations[allocations.length - 1];
        const nextAvailable = new Date(latestAllocation.to_date);
        nextAvailable.setDate(nextAvailable.getDate() + 1); // Next day after allocation ends
        
        return nextAvailable.toISOString().split('T')[0];
    },
    
    // Date change handler
    onDateChange() {
        if (this.formData.arrival_date && this.formData.departure_date) {
            this.updateAllRoomAvailability();
        }
    },
    
    // Room allocation management
    addRoomAllocation() {
        this.formData.room_allocations.push({
            room_id: '',
            adults: 1,
            children: 0,
            rate: '',
            guest_notes: ''
        });
        
        this.updateTotalGuests();
    },
    
    removeRoomAllocation(index) {
        if (this.formData.room_allocations.length > 1) {
            this.formData.room_allocations.splice(index, 1);
            this.updateTotalGuests();
        }
    },
    
    updateTotalGuests() {
        // This is calculated automatically in the backend
    },
    
    getRoomDetails(roomId) {
        return this.allRooms.find(room => parseInt(room.id) === parseInt(roomId)) || null;
    },
    
    calculateRoomTotal(allocation) {
        if (!allocation.room_id || !this.formData.arrival_date || !this.formData.departure_date) {
            return 0;
        }
        
        const nights = this.calculateNights(this.formData.arrival_date, this.formData.departure_date);
        const room = this.getRoomDetails(allocation.room_id);
        const dailyRate = allocation.rate || (room?.room_type?.base_rate || 0);
        
        // Calculate: adults pay full rate, children pay 50%
        const adultTotal = dailyRate * nights * (allocation.adults || 1);
        const childRate = dailyRate * 0.5;
        const childTotal = childRate * nights * (allocation.children || 0);
        
        return (adultTotal + childTotal).toFixed(2);
    },
    
    calculateTotalStayAmount() {
        return this.formData.room_allocations.reduce((total, allocation) => {
            return total + parseFloat(this.calculateRoomTotal(allocation) || 0);
        }, 0).toFixed(2);
    },
    
    // Available rooms for allocation (excluding already selected rooms)
    getAvailableRoomsForAllocation(index) {
        // Get all selected room IDs except current one
        const selectedRoomIds = this.formData.room_allocations
            .filter((_, i) => i !== index)
            .map(allocation => allocation.room_id)
            .filter(id => id);
        
        // Get all rooms with availability info
        const roomsWithAvailability = this.allRooms.map(room => {
            const isAvailable = this.isRoomAvailable(
                room.id, 
                this.formData.arrival_date, 
                this.formData.departure_date
            );
            
            const nextAvailableDate = !isAvailable ? this.getNextAvailableDate(room.id) : null;
            
            return {
                ...room,
                is_available: isAvailable,
                next_available_date: nextAvailableDate
            };
        });
        
        // Filter out already selected rooms
        return roomsWithAvailability.filter(room => {
            // Check if room is already selected in another allocation
            if (selectedRoomIds.includes(room.id.toString())) {
                return false;
            }
            
            return true; // Show room but indicate availability in dropdown
        }).sort((a, b) => {
            // Sort available rooms first, then by room number
            if (a.is_available !== b.is_available) {
                return a.is_available ? -1 : 1;
            }
            return a.room_number.localeCompare(b.room_number, undefined, { numeric: true });
        });
    },
    
    // Validate room capacity
    validateRoomCapacity(index) {
        const allocation = this.formData.room_allocations[index];
        if (!allocation.room_id) return;
        
        const room = this.getRoomDetails(allocation.room_id);
        if (!room || !room.room_type?.capacity) return;
        
        const totalGuests = (allocation.adults || 0) + (allocation.children || 0);
        const capacity = room.room_type.capacity;
        
        if (totalGuests > capacity) {
            this.showToast(`Room ${room.room_number} capacity exceeded (max ${capacity} guests)`, 'warning');
        }
    },
    
    updateAllRoomAvailability() {
        // Force UI update for all room selection dropdowns
        this.formData.room_allocations = [...this.formData.room_allocations];
    },
    
    // When room selection changes, update the rate field
    onRoomChange(index) {
        const allocation = this.formData.room_allocations[index];
        const room = this.getRoomDetails(allocation.room_id);
        
        if (room && !allocation.rate) {
            allocation.rate = room.room_type?.base_rate || '';
        }
        
        // Validate capacity
        this.validateRoomCapacity(index);
        
        // Force Alpine to update the UI
        this.$nextTick(() => {
            this.formData.room_allocations = [...this.formData.room_allocations];
        });
    },
    
    // Modal functions
    openModal() {
        this.isModalOpen = true;
        document.body.classList.add('overflow-hidden');
        this.setDefaultDates();
        this.resetForm();
    },
    
    closeModal() {
        this.isModalOpen = false;
        document.body.classList.remove('overflow-hidden');
        this.resetForm();
    },
    
    resetForm() {
        this.formData = {
            arrival_date: '',
            departure_date: '',
            room_allocations: [
                {
                    room_id: '',
                    adults: 1,
                    children: 0,
                    rate: '',
                    guest_notes: ''
                }
            ],
            special_requests: '',
            status: 'reserved',
        };
        this.isLoading = false;
        this.setDefaultDates();
    },
    
    // Submit form with multi-room data
    async submitForm() {
        // Validate required fields
        if (!this.formData.arrival_date || !this.formData.departure_date) {
            this.showToast('Please select arrival and departure dates', 'error');
            return;
        }
        
        // Validate dates
        if (new Date(this.formData.arrival_date) >= new Date(this.formData.departure_date)) {
            this.showToast('Departure date must be after arrival date', 'error');
            return;
        }
        
        // Validate room allocations
        if (this.formData.room_allocations.length === 0) {
            this.showToast('Please add at least one room', 'error');
            return;
        }
        
        // Check for duplicate room selections
        const roomIds = this.formData.room_allocations
            .map(allocation => allocation.room_id)
            .filter(id => id);
        
        const uniqueRoomIds = [...new Set(roomIds)];
        if (roomIds.length !== uniqueRoomIds.length) {
            this.showToast('Cannot select the same room multiple times', 'error');
            return;
        }
        
        for (let i = 0; i < this.formData.room_allocations.length; i++) {
            const allocation = this.formData.room_allocations[i];
            if (!allocation.room_id) {
                this.showToast(`Please select a room for allocation ${i + 1}`, 'error');
                return;
            }
            if (!allocation.adults || allocation.adults < 1) {
                this.showToast(`Please enter number of adults for allocation ${i + 1}`, 'error');
                return;
            }
            
            // Check room capacity
            const room = this.getRoomDetails(allocation.room_id);
            const totalGuests = (allocation.adults || 0) + (allocation.children || 0);
            if (room && room.room_type?.capacity && totalGuests > room.room_type.capacity) {
                this.showToast(`Room ${room.room_number} capacity exceeded (max ${room.room_type.capacity} guests)`, 'error');
                return;
            }
            
            // Check availability
            const isAvailable = this.isRoomAvailable(
                allocation.room_id,
                this.formData.arrival_date,
                this.formData.departure_date
            );
            
            if (!isAvailable) {
                this.showToast(`Room ${room.room_number} is not available for selected dates`, 'error');
                return;
            }
        }
        
        this.isLoading = true;
        
        try {
            const response = await fetch(this.storeRoute, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify({
                    arrival_date: this.formData.arrival_date,
                    departure_date: this.formData.departure_date,
                    room_allocations: this.formData.room_allocations,
                    special_requests: this.formData.special_requests,
                    status: this.formData.status,
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showToast(`Stay created successfully with ${data.stay.room_count} rooms`, 'success');
                this.closeModal();
                
                // Reload page after a short delay
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                this.showToast(data.message || 'Error creating stay', 'error');
            }
        } catch (error) {
            console.error('Error creating stay:', error);
            this.showToast('An error occurred while creating stay', 'error');
        } finally {
            this.isLoading = false;
        }
    },
    
    // Date filter functions
    applyDateFilter() {
        // Validate date range
        if (this.filters.date_from && this.filters.date_to) {
            const from = new Date(this.filters.date_from);
            const to = new Date(this.filters.date_to);
            
            if (from > to) {
                this.showToast('"From" date cannot be after "To" date', 'error');
                return;
            }
        }
        
        // Close the dropdown
        document.querySelectorAll('[x-data*="open"]').forEach(el => {
            if (el.__x.$data && el.__x.$data.open !== undefined) {
                el.__x.$data.open = false;
            }
        });
    },
    
    clearDateFilter() {
        this.filters.date_from = '';
        this.filters.date_to = '';
        
        // Close the dropdown
        document.querySelectorAll('[x-data*="open"]').forEach(el => {
            if (el.__x.$data && el.__x.$data.open !== undefined) {
                el.__x.$data.open = false;
            }
        });
    },
    
    // Helper functions
    formatDate(dateString) {
        if (!dateString) return '—';
        const date = new Date(dateString);
        if (isNaN(date)) return '—';
        return date.toLocaleDateString('en-GB', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    },
    
    formatTime(dateTimeString) {
        if (!dateTimeString) return '';
        const date = new Date(dateTimeString);
        return date.toLocaleTimeString(this.dateFormat, {
            hour: '2-digit',
            minute: '2-digit'
        });
    },
    
    formatDisplayDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString(this.dateFormat, {
            month: 'short',
            day: 'numeric'
        });
    },
    
    formatStatus(status) {
        return status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ');
    },
    
    calculateNights(arrivalDate, departureDate) {
        if (!arrivalDate || !departureDate) return 0;
        const arrival = new Date(arrivalDate);
        const departure = new Date(departureDate);
        const diffTime = Math.abs(departure - arrival);
        return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    },
    
    // Check-in/Check-out functions
    async checkinStay(stayId) {
        if (!confirm('Are you sure you want to check-in this stay?')) {
            return;
        }
        
        try {
            const route = this.checkinRoute.replace(':stayId', stayId);
            const response = await fetch(route, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showToast('Guest checked in successfully', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                this.showToast(data.message || 'Failed to check-in', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showToast('An error occurred', 'error');
        }
    },
    
    async checkoutStay(stayId) {
        if (!confirm('Are you sure you want to check-out this stay?')) {
            return;
        }
        
        try {
            const route = this.checkoutRoute.replace(':stayId', stayId);
            const response = await fetch(route, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showToast('Guest checked out successfully', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                this.showToast(data.message || 'Failed to check-out', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showToast('An error occurred', 'error');
        }
    },
    
    showToast(message, type = 'info') {
        // Remove existing toasts
        document.querySelectorAll('.custom-toast').forEach(toast => toast.remove());
        
        // Create new toast
        const toast = document.createElement('div');
        toast.className = `custom-toast fixed top-4 right-4 px-4 py-3 rounded-lg text-white text-sm shadow-lg z-[99999] transition-all duration-300 ${
            type === 'success' ? 'bg-green-500' : 
            type === 'error' ? 'bg-red-500' : 
            type === 'warning' ? 'bg-yellow-500' : 
            'bg-primary'
        }`;
        toast.textContent = message;
        
        // Add icon based on type
        const icon = document.createElement('i');
        icon.className = `fas mr-2 ${
            type === 'success' ? 'fa-check-circle' : 
            type === 'error' ? 'fa-times-circle' : 
            type === 'warning' ? 'fa-exclamation-triangle' : 
            'fa-info-circle'
        }`;
        toast.prepend(icon);
        
        document.body.appendChild(toast);
        
        // Auto remove after 4 seconds
        setTimeout(() => {
            toast.classList.add('opacity-0', 'transition-opacity', 'duration-300');
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }, 4000);
    }
});

// Register Alpine.js component
document.addEventListener('alpine:init', () => {
    Alpine.data('stayTable', window.stayTableComponent);
});
</script>