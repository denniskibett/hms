<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-4 pb-3 pt-4 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6" x-data="guestTable()">

    <!-- Combined Table with Filters -->

        <!-- Table Header with Filters -->
        <div class="flex flex-col gap-2 mb-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                    Guest List
                </h3>
            </div>

            <div class="flex items-center gap-3">
                <!-- Entries Per Page -->
                <div class="flex items-center gap-3">
                    <span class="text-gray-500 dark:text-gray-400 text-sm"> Show </span>
                    <div class="relative z-20 bg-transparent">
                        <select x-model="pagination.perPage" @change="fetchGuests()"
                                class="shadow-theme-xs h-9 w-20 appearance-none rounded-lg border border-gray-300 bg-transparent bg-none py-2 pr-8 pl-3 text-sm text-gray-800 placeholder:text-gray-400 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="absolute top-1/2 right-2 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                            <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke="" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </div>
                    <span class="text-gray-500 dark:text-gray-400 text-sm"> entries </span>
                </div>
                
                <!-- Search Input -->
                <div class="relative">
                    <input type="text" x-model="filters.search" @input.debounce.500ms="fetchGuests()" 
                           placeholder="Search guests..." 
                           class="w-full md:w-64 border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2.5 text-sm dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/20"> {{-- UPDATED --}}
                    <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
                </div>
                
                <!-- Status Filter -->
                <select x-model="filters.status" @change="fetchGuests()" 
                        class="border border-gray-300 dark:border-gray-700 rounded-lg px-3 py-2.5 text-sm dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-primary/20"> {{-- UPDATED --}}
                    <option value="">All Guests</option>
                    <option value="active">Active Guests</option>
                    <option value="inactive">Inactive Guests</option>
                </select>
                
                <!-- Clear Filters -->
                <button @click="clearFilters()" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                    Clear
                </button>

                <div class="flex items-center space-x-2">
                    <button @click="openCreateModal()" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                        <i class="fas fa-plus mr-1"></i> New Guest
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div x-show="loading" class="py-12 text-center">
            <div class="inline-block animate-spin rounded-full h-10 w-10 border-b-2 border-primary"></div> {{-- UPDATED --}}
            <p class="mt-3 text-gray-500">Loading guests...</p>
        </div>

        <!-- Table -->
        <div x-show="!loading" class="w-full overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-gray-100 border-y dark:border-gray-800">
                        <th class="py-3">
                            <div class="flex items-center">
                                <button @click="sortBy('name')" class="flex items-center gap-1 font-medium text-gray-500 text-sm dark:text-gray-400 hover:text-gray-700">
                                    Guest
                                    <template x-if="sort.field === 'name'">
                                        <i :class="sort.direction === 'asc' ? 'fas fa-chevron-up' : 'fas fa-chevron-down'" class="text-xs"></i>
                                    </template>
                                </button>
                            </div>
                        </th>
                        <th class="py-3">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-sm dark:text-gray-400">
                                    Phone
                                </p>
                            </div>
                        </th>
                        <th class="py-3">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-sm dark:text-gray-400">
                                    Email
                                </p>
                            </div>
                        </th>
                        <th class="py-3">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-sm dark:text-gray-400">
                                    ID Details
                                </p>
                            </div>
                        </th>
                        <th class="py-3">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-sm dark:text-gray-400">
                                    Nationality
                                </p>
                            </div>
                        </th>
                        <th class="py-3">
                            <div class="flex items-center">
                                <button @click="sortBy('status')" class="flex items-center gap-1 font-medium text-gray-500 text-sm dark:text-gray-400 hover:text-gray-700">
                                    Status
                                    <template x-if="sort.field === 'status'">
                                        <i :class="sort.direction === 'asc' ? 'fas fa-chevron-up' : 'fas fa-chevron-down'" class="text-xs"></i>
                                    </template>
                                </button>
                            </div>
                        </th>
                        <th class="py-3 pr-4">
                            <div class="flex items-center">
                                <p class="font-medium text-gray-500 text-sm dark:text-gray-400">
                                    Actions
                                </p>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <template x-for="guest in guests" :key="guest.id">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            
                            <td class="py-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-primary-10">
                                        <template x-if="guest.user?.avatar">
                                            <img :src="`/storage/${guest.user.avatar}`" :alt="guest.user.name" class="h-10 w-10 rounded-full object-cover">
                                        </template>
                                        <template x-if="!guest.user?.avatar">
                                            <span class="text-xs font-semibold text-primary" x-text="getInitials(guest.user?.name)"></span>
                                        </template>
                                    </div>
                                    <div>
                                        <a :href="`/guests/${guest.id}`"
                                           class="font-medium text-gray-800 text-sm dark:text-white/90 hover:text-primary hover:underline" {{-- UPDATED --}}
                                           x-text="guest.user?.name || 'N/A'"></a>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3">
                                <div class="flex flex-col">
                                    <span class="text-gray-800 text-sm dark:text-white/90" x-text="guest.user?.phone || 'N/A'"></span>
                                </div>
                            </td>
                            <td class="py-3">
                                <div class="flex flex-col">
                                    <span class="text-gray-800 text-sm dark:text-white/90" x-text="guest.user?.email || 'N/A'"></span>
                                </div>
                            </td>
                            <td class="py-3">
                                <div class="flex flex-col">
                                    <span class="text-gray-800 text-sm dark:text-white/90">
                                        <span x-text="getReadableIdType(guest.id_type)"></span>
                                    </span>
                                </div>
                            </td>
                            <td class="py-3">
                                <div class="flex items-center">
                                    <p class="text-gray-800 text-sm dark:text-white/90" x-text="guest.nationality || 'N/A'"></p>
                                </div>
                            </td>
                            <td class="py-3">
                                <div class="flex items-center">
                                    <span 
                                        :class="{
                                            'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500': getGuestStatus(guest) === 'Active',
                                            'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500': getGuestStatus(guest) === 'Inactive',
                                            'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-orange-400': getGuestStatus(guest) === 'Booked'
                                        }"
                                        class="rounded-full px-2 py-0.5 text-xs font-medium"
                                        x-text="getGuestStatus(guest)"
                                    ></span>
                                </div>
                            </td>
                            <td class="py-3 pr-4">
                                <div x-data="dropdown()" class="relative">
                                    <button @click="toggle()" class="text-gray-500 dark:text-gray-400 hover:text-primary"> {{-- UPDATED --}}
                                        <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.99902 10.245C6.96552 10.245 7.74902 11.0285 7.74902 11.995V12.005C7.74902 12.9715 6.96552 13.755 5.99902 13.755C5.03253 13.755 4.24902 12.9715 4.24902 12.005V11.995C4.24902 11.0285 5.03253 10.245 5.99902 10.245ZM17.999 10.245C18.9655 10.245 19.749 11.0285 19.749 11.995V12.005C19.749 12.9715 18.9655 13.755 17.999 13.755C17.0325 13.755 16.249 12.9715 16.249 12.005V11.995C16.249 11.0285 17.0325 10.245 17.999 10.245ZM13.749 11.995C13.749 11.0285 12.9655 10.245 11.999 10.245C11.0325 10.245 10.249 11.0285 10.249 11.995V12.005C10.249 12.9715 11.0325 13.755 11.999 13.755C12.9655 13.755 13.749 12.9715 13.749 12.005V11.995Z" fill=""/>
                                        </svg>
                                    </button>
                                    <div x-show="open" @click.outside="open = false" class="shadow-theme-lg dark:bg-gray-dark absolute right-0 top-full z-50 mt-2 w-40 space-y-1 rounded-2xl border border-gray-200 bg-white p-2 dark:border-gray-800" x-ref="dropdown">
                                        <a :href="`/guests/${guest.id}`" 
                                           class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-gray-500 hover:bg-primary/10 hover:text-primary dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-primary"> {{-- UPDATED --}}
                                            View More
                                        </a>
                                        <button @click="openEditModal(guest)"
                                                class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-gray-500 hover:bg-primary/10 hover:text-primary dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-primary"> {{-- UPDATED --}}
                                            Edit
                                        </button>
                                        <button @click="confirmDeleteGuest(guest.id, guest.user?.name)"
                                                class="text-theme-xs flex w-full rounded-lg px-3 py-2 text-left font-medium text-red-600 hover:bg-red-50 hover:text-red-700 dark:text-red-400 dark:hover:bg-white/5 dark:hover:text-red-300">
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </template>
                    
                    <template x-if="guests.length === 0 && !loading">
                        <tr>
                            <td colspan="10" class="py-8 px-4 text-center">
                                <div class="text-gray-500 dark:text-gray-400">
                                    <i class="fas fa-users text-4xl mb-2"></i>
                                    <p class="text-lg">No guests found</p>
                                    <p class="text-sm mt-1">Try adjusting your filters or add a new guest</p>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div x-show="!loading && pagination.lastPage > 1" class="border-t border-gray-100 py-4 pr-4 pl-[18px] dark:border-gray-800">
            <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between">
                <p class="border-b border-gray-100 pb-3 text-center text-sm font-medium text-gray-500 xl:border-b-0 xl:pb-0 xl:text-left dark:border-gray-800 dark:text-gray-400">
                    Showing <span x-text="pagination.from"></span> to
                    <span x-text="pagination.to"></span> of
                    <span x-text="pagination.total"></span> entries
                </p>

                <div class="flex items-center justify-center gap-0.5 pt-4 xl:justify-end xl:pt-0">
                    <button @click="prevPage()" :disabled="pagination.currentPage === 1"
                            class="shadow-theme-xs mr-2.5 flex h-10 w-10 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-primary/10 hover:text-primary disabled:opacity-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]"> {{-- UPDATED --}}
                        <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M2.58301 9.99868C2.58272 10.1909 2.65588 10.3833 2.80249 10.53L7.79915 15.5301C8.09194 15.8231 8.56682 15.8233 8.85981 15.5305C9.15281 15.2377 9.15297 14.7629 8.86018 14.4699L5.14009 10.7472L16.6675 10.7472C17.0817 10.7472 17.4175 10.4114 17.4175 9.99715C17.4175 9.58294 17.0817 9.24715 16.6675 9.24715L5.14554 9.24715L8.86017 5.53016C9.15297 5.23717 9.15282 4.7623 8.85983 4.4695C8.56684 4.1767 8.09197 4.17685 7.79917 4.46984L2.84167 9.43049C2.68321 9.568 2.58301 9.77087 2.58301 9.99715C2.58301 9.99766 2.58301 9.99817 2.58301 9.99868Z" fill=""/>
                        </svg>
                    </button>

                    <template x-if="pagination.currentPage > 3">
                        <span class="hover:text-primary dark:hover:text-primary flex h-10 w-10 items-center justify-center rounded-lg hover:bg-primary/10">...</span> {{-- UPDATED --}}
                    </template>

                    <template x-for="page in getVisiblePages()" :key="page">
                        <button @click="goToPage(page)" 
                                :class="pagination.currentPage === page ? 'bg-primary/10 text-primary' : 'text-gray-700 dark:text-gray-400 hover:text-primary hover:bg-primary/10'" {{-- UPDATED --}}
                                class="flex h-10 w-10 items-center justify-center rounded-lg text-sm font-medium">
                            <span x-text="page"></span>
                        </button>
                    </template>

                    <template x-if="pagination.currentPage < pagination.lastPage - 2">
                        <span class="hover:text-primary dark:hover:text-primary flex h-10 w-10 items-center justify-center rounded-lg text-sm font-medium text-gray-700 hover:bg-primary/10 dark:text-gray-400">...</span> {{-- UPDATED --}}
                    </template>

                    <button @click="nextPage()" :disabled="pagination.currentPage === pagination.lastPage"
                            class="shadow-theme-xs ml-2.5 flex h-10 w-10 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-primary/10 hover:text-primary disabled:opacity-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]"> {{-- UPDATED --}}
                        <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M17.4175 9.9986C17.4178 10.1909 17.3446 10.3832 17.198 10.53L12.2013 15.5301C11.9085 15.8231 11.4337 15.8233 11.1407 15.5305C10.8477 15.2377 10.8475 14.7629 11.1403 14.4699L14.8604 10.7472L3.33301 10.7472C2.91879 10.7472 2.58301 10.4114 2.58301 9.99715C2.58301 9.58294 2.91879 9.24715 3.33301 9.24715L14.8549 9.24715L11.1403 5.53016C10.8475 5.23717 10.8477 4.7623 11.1407 4.4695C11.4336 4.1767 11.9085 4.17685 12.2013 4.46984L17.1588 9.43049C17.3173 9.568 17.4175 9.77087 17.4175 9.99715C17.4175 9.99763 17.4175 9.99812 17.4175 9.9986Z" fill=""/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

    <!-- Create/Edit Guest Modal -->
    <div x-show="showGuestModal" x-cloak class="fixed inset-0 z-99999 overflow-y-auto px-4 py-6 sm:px-0">
        <div x-show="showGuestModal" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-500 opacity-75" @click="showGuestModal = false"></div>

        <div x-show="showGuestModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative bg-white rounded-lg shadow-xl transform transition-all sm:w-full sm:max-w-4xl sm:mx-auto my-8">
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-800" x-text="editingGuest ? 'Edit Guest' : 'Add New Guest'"></h3>
                <button @click="showGuestModal = false" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="p-6 max-h-[80vh] overflow-y-auto">
                <form id="guestForm" @submit.prevent="editingGuest ? updateGuest() : createGuest()" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Personal Information -->
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 mb-4 pb-2 border-b">Personal Information</h4>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1">
                                        First Name *
                                    </label>
                                    <input type="text" x-model="guestForm.user.first_name" required
                                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20"> {{-- UPDATED --}}
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1">
                                        Last Name *
                                    </label>
                                    <input type="text" x-model="guestForm.user.last_name" required
                                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20"> {{-- UPDATED --}}
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1">
                                        Email *
                                    </label>
                                    <input type="email" x-model="guestForm.user.email" required
                                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20"> {{-- UPDATED --}}
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1">
                                        Phone Number
                                    </label>
                                    <input type="tel" x-model="guestForm.user.phone"
                                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20"> {{-- UPDATED --}}
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1">
                                        Address
                                    </label>
                                    <textarea x-model="guestForm.guest_profile.address" rows="2"
                                              class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20"></textarea> {{-- UPDATED --}}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Identification & Preferences -->
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 mb-4 pb-2 border-b">Identification & Preferences</h4>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1">
                                        ID Type *
                                    </label>
                                    <select x-model="guestForm.guest_profile.id_type" required
                                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20"> {{-- UPDATED --}}
                                        <option value="">Select ID Type</option>
                                        <option value="passport">Passport</option>
                                        <option value="national_id">National ID</option>
                                        <option value="driving_license">Driving License</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1">
                                        ID Number *
                                    </label>
                                    <input type="text" x-model="guestForm.guest_profile.id_number" required
                                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20"> {{-- UPDATED --}}
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1">
                                        Nationality *
                                    </label>
                                    <div class="relative">
                                        <select x-model="guestForm.guest_profile.nationality" required
                                                class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20"> {{-- UPDATED --}}
                                            <option value="">Select Nationality</option>
                                            @foreach($countries as $country)
                                                <option value="{{ $country['name'] }}">
                                                    {{ $country['flag_emoji'] ?? '' }} {{ $country['name'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="absolute right-2 top-2 text-gray-400 pointer-events-none">
                                            <i class="fas fa-chevron-down"></i>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Preferences with Multiple Selection -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-2">
                                        Preferences
                                    </label>
                                    <div class="space-y-3">
                                        <!-- Room Preferences (Multiple Select) - Simplified Version -->
                                        <div x-data="{
                                            open: false,
                                            get selected() {
                                                return guestForm.guest_profile.preferences.room_preference || [];
                                            },
                                            set selected(value) {
                                                guestForm.guest_profile.preferences.room_preference = value;
                                            },
                                            options: [
                                                { id: 'quiet_room', name: 'Quiet Room' },
                                                { id: 'high_floor', name: 'High Floor' },
                                                { id: 'near_elevator', name: 'Near Elevator' },
                                                { id: 'pool_view', name: 'Pool View' },
                                                { id: 'city_view', name: 'City View' },
                                                { id: 'smoking', name: 'Smoking Room' },
                                                { id: 'non_smoking', name: 'Non-Smoking Room' },
                                                { id: 'accessible', name: 'Accessible Room' },
                                                { id: 'connecting', name: 'Connecting Room' }
                                            ],
                                            toggleOption(id) {
                                                if (this.selected.includes(id)) {
                                                    this.selected = this.selected.filter(i => i !== id);
                                                } else {
                                                    this.selected = [...this.selected, id];
                                                }
                                            },
                                            isSelected(id) {
                                                return this.selected.includes(id);
                                            }
                                        }" class="relative">
                                            <label class="block text-sm font-medium text-gray-600 mb-1">
                                                Room Preferences
                                            </label>
                                            
                                            <!-- Select Input with Selected Tags -->
                                            <div @click="open = !open"
                                                class="shadow-theme-xs flex min-h-11 cursor-pointer gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 transition dark:border-gray-700 dark:bg-gray-900">
                                                <!-- Selected Items as Tags -->
                                                <div class="flex flex-1 flex-wrap items-center gap-2">
                                                    <template x-for="id in selected" :key="id">
                                                        <div class="group flex items-center justify-center rounded-full border-[0.7px] border-transparent bg-gray-100 py-1 pr-2 pl-2.5 text-sm text-gray-800 hover:border-gray-200 dark:bg-gray-800 dark:text-white/90 dark:hover:border-gray-800">
                                                            <span x-text="options.find(o => o.id === id).name"></span>
                                                            <button type="button"
                                                                    @click.stop="toggleOption(id)"
                                                                    class="ml-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                                                <svg class="fill-current" role="button" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M3.40717 4.46881C3.11428 4.17591 3.11428 3.70104 3.40717 3.40815C3.70006 3.11525 4.17494 3.11525 4.46783 3.40815L6.99943 5.93975L9.53095 3.40822C9.82385 3.11533 10.2987 3.11533 10.5916 3.40822C10.8845 3.70112 10.8845 4.17599 10.5916 4.46888L8.06009 7.00041L10.5916 9.53193C10.8845 9.82482 10.8845 10.2997 10.5916 10.5926C10.2987 10.8855 9.82385 10.8855 9.53095 10.5926L6.99943 8.06107L4.46783 10.5927C4.17494 10.8856 3.70006 10.8856 3.40717 10.5927C3.11428 10.2998 3.11428 9.8249 3.40717 9.53201L5.93877 7.00041L3.40717 4.46881Z" fill=""/>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </template>
                                                    <template x-if="selected.length === 0">
                                                        <span class="text-gray-400">Select room preferences...</span>
                                                    </template>
                                                </div>
                                                <!-- Dropdown Arrow -->
                                                <span class="flex items-center text-gray-400">
                                                    <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M5.29289 7.29289C5.68342 6.90237 6.31658 6.90237 6.70711 7.29289L10 10.5858L13.2929 7.29289C13.6834 6.90237 14.3166 6.90237 14.7071 7.29289C15.0976 7.68342 15.0976 8.31658 14.7071 8.70711L10.7071 12.7071C10.3166 13.0976 9.68342 13.0976 9.29289 12.7071L5.29289 8.70711C4.90237 8.31658 4.90237 7.68342 5.29289 7.29289Z" fill=""/>
                                                    </svg>
                                                </span>
                                            </div>
                                            
                                            <!-- Dropdown Options -->
                                            <div x-show="open" @click.outside="open = false"
                                                class="absolute z-99999 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900">
                                                <div class="max-h-60 overflow-y-auto p-2">
                                                    <template x-for="option in options" :key="option.id">
                                                        <div @click="toggleOption(option.id)"
                                                            :class="isSelected(option.id) ? 'bg-primary/5 text-primary' : 'hover:bg-gray-50 dark:hover:bg-gray-800'" {{-- UPDATED --}}
                                                            class="flex cursor-pointer items-center rounded-lg px-3 py-2 text-sm">
                                                            <div class="flex h-5 w-5 items-center justify-center rounded border border-gray-300 mr-2">
                                                                <template x-if="isSelected(option.id)">
                                                                    <svg class="fill-current" width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M10.7071 2.29289C11.0976 2.68342 11.0976 3.31658 10.7071 3.70711L4.70711 9.70711C4.31658 10.0976 3.68342 10.0976 3.29289 9.70711L0.292893 6.70711C-0.0976311 6.31658 -0.0976311 5.68342 0.292893 5.29289C0.683417 4.90237 1.31658 4.90237 1.70711 5.29289L4 7.58579L9.29289 2.29289C9.68342 1.90237 10.3166 1.90237 10.7071 2.29289Z" fill=""/>
                                                                    </svg>
                                                                </template>
                                                            </div>
                                                            <span x-text="option.name"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Entertainment Preferences (Multiple Select) -->
                                        <div x-data="{
                                            open: false,
                                            get selected() {
                                                return guestForm.guest_profile.preferences.entertainment || [];
                                            },
                                            set selected(value) {
                                                guestForm.guest_profile.preferences.entertainment = value;
                                            },
                                            options: [
                                                { id: 'netflix', name: 'Netflix' },
                                                { id: 'cable_tv', name: 'Cable TV' },
                                                { id: 'streaming', name: 'Streaming Services' },
                                                { id: 'hbo', name: 'HBO Max' },
                                                { id: 'disney', name: 'Disney+' },
                                                { id: 'music', name: 'Music Streaming' },
                                                { id: 'gaming', name: 'Gaming Console' },
                                                { id: 'none', name: 'None' }
                                            ],
                                            toggleOption(id) {
                                                if (this.selected.includes(id)) {
                                                    this.selected = this.selected.filter(i => i !== id);
                                                } else {
                                                    this.selected = [...this.selected, id];
                                                }
                                            },
                                            isSelected(id) {
                                                return this.selected.includes(id);
                                            }
                                        }" class="relative">
                                            <label class="block text-sm font-medium text-gray-600 mb-1">
                                                Entertainment Preferences
                                            </label>
                                            
                                            <div @click="open = !open"
                                                class="shadow-theme-xs flex min-h-11 cursor-pointer gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 transition dark:border-gray-700 dark:bg-gray-900">
                                                <div class="flex flex-1 flex-wrap items-center gap-2">
                                                    <template x-for="id in selected" :key="id">
                                                        <div class="group flex items-center justify-center rounded-full border-[0.7px] border-transparent bg-gray-100 py-1 pr-2 pl-2.5 text-sm text-gray-800 hover:border-gray-200 dark:bg-gray-800 dark:text-white/90 dark:hover:border-gray-800">
                                                            <span x-text="options.find(o => o.id === id).name"></span>
                                                            <button type="button"
                                                                    @click.stop="toggleOption(id)"
                                                                    class="ml-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                                                <svg class="fill-current" role="button" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M3.40717 4.46881C3.11428 4.17591 3.11428 3.70104 3.40717 3.40815C3.70006 3.11525 4.17494 3.11525 4.46783 3.40815L6.99943 5.93975L9.53095 3.40822C9.82385 3.11533 10.2987 3.11533 10.5916 3.40822C10.8845 3.70112 10.8845 4.17599 10.5916 4.46888L8.06009 7.00041L10.5916 9.53193C10.8845 9.82482 10.8845 10.2997 10.5916 10.5926C10.2987 10.8855 9.82385 10.8855 9.53095 10.5926L6.99943 8.06107L4.46783 10.5927C4.17494 10.8856 3.70006 10.8856 3.40717 10.5927C3.11428 10.2998 3.11428 9.8249 3.40717 9.53201L5.93877 7.00041L3.40717 4.46881Z" fill=""/>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </template>
                                                    <template x-if="selected.length === 0">
                                                        <span class="text-gray-400">Select entertainment preferences...</span>
                                                    </template>
                                                </div>
                                                <span class="flex items-center text-gray-400">
                                                    <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M5.29289 7.29289C5.68342 6.90237 6.31658 6.90237 6.70711 7.29289L10 10.5858L13.2929 7.29289C13.6834 6.90237 14.3166 6.90237 14.7071 7.29289C15.0976 7.68342 15.0976 8.31658 14.7071 8.70711L10.7071 12.7071C10.3166 13.0976 9.68342 13.0976 9.29289 12.7071L5.29289 8.70711C4.90237 8.31658 4.90237 7.68342 5.29289 7.29289Z" fill=""/>
                                                    </svg>
                                                </span>
                                            </div>
                                            
                                            <div x-show="open" @click.outside="open = false"
                                                class="absolute z-99999 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900">
                                                <div class="max-h-60 overflow-y-auto p-2">
                                                    <template x-for="option in options" :key="option.id">
                                                        <div @click="toggleOption(option.id)"
                                                            :class="isSelected(option.id) ? 'bg-primary/5 text-primary' : 'hover:bg-gray-50 dark:hover:bg-gray-800'" {{-- UPDATED --}}
                                                            class="flex cursor-pointer items-center rounded-lg px-3 py-2 text-sm">
                                                            <div class="flex h-5 w-5 items-center justify-center rounded border border-gray-300 mr-2">
                                                                <template x-if="isSelected(option.id)">
                                                                    <svg class="fill-current" width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M10.7071 2.29289C11.0976 2.68342 11.0976 3.31658 10.7071 3.70711L4.70711 9.70711C4.31658 10.0976 3.68342 10.0976 3.29289 9.70711L0.292893 6.70711C-0.0976311 6.31658 -0.0976311 5.68342 0.292893 5.29289C0.683417 4.90237 1.31658 4.90237 1.70711 5.29289L4 7.58579L9.29289 2.29289C9.68342 1.90237 10.3166 1.90237 10.7071 2.29289Z" fill=""/>
                                                                    </svg>
                                                                </template>
                                                            </div>
                                                            <span x-text="option.name"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Room Service Preferences -->
                                        <div x-data="{
                                            open: false,
                                            get selected() {
                                                return guestForm.guest_profile.preferences.room_service || [];
                                            },
                                            set selected(value) {
                                                guestForm.guest_profile.preferences.room_service = value;
                                            },
                                            options: [
                                                { id: 'full_service', name: 'Full Service' },
                                                { id: 'limited', name: 'Limited' },
                                                { id: 'morning', name: 'Morning Only' },
                                                { id: 'evening', name: 'Evening Only' },
                                                { id: 'turndown', name: 'Turndown Service' },
                                                { id: 'minibar', name: 'Minibar Restock' },
                                                { id: 'none', name: 'No Room Service' }
                                            ],
                                            toggleOption(id) {
                                                if (this.selected.includes(id)) {
                                                    this.selected = this.selected.filter(i => i !== id);
                                                } else {
                                                    this.selected = [...this.selected, id];
                                                }
                                            },
                                            isSelected(id) {
                                                return this.selected.includes(id);
                                            }
                                        }" class="relative">
                                            <label class="block text-sm font-medium text-gray-600 mb-1">
                                                Room Service Preferences
                                            </label>
                                            
                                            <div @click="open = !open"
                                                class="shadow-theme-xs flex min-h-11 cursor-pointer gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 transition dark:border-gray-700 dark:bg-gray-900">
                                                <div class="flex flex-1 flex-wrap items-center gap-2">
                                                    <template x-for="id in selected" :key="id">
                                                        <div class="group flex items-center justify-center rounded-full border-[0.7px] border-transparent bg-gray-100 py-1 pr-2 pl-2.5 text-sm text-gray-800 hover:border-gray-200 dark:bg-gray-800 dark:text-white/90 dark:hover:border-gray-800">
                                                            <span x-text="options.find(o => o.id === id).name"></span>
                                                            <button type="button"
                                                                    @click.stop="toggleOption(id)"
                                                                    class="ml-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                                                <svg class="fill-current" role="button" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M3.40717 4.46881C3.11428 4.17591 3.11428 3.70104 3.40717 3.40815C3.70006 3.11525 4.17494 3.11525 4.46783 3.40815L6.99943 5.93975L9.53095 3.40822C9.82385 3.11533 10.2987 3.11533 10.5916 3.40822C10.8845 3.70112 10.8845 4.17599 10.5916 4.46888L8.06009 7.00041L10.5916 9.53193C10.8845 9.82482 10.8845 10.2997 10.5916 10.5926C10.2987 10.8855 9.82385 10.8855 9.53095 10.5926L6.99943 8.06107L4.46783 10.5927C4.17494 10.8856 3.70006 10.8856 3.40717 10.5927C3.11428 10.2998 3.11428 9.8249 3.40717 9.53201L5.93877 7.00041L3.40717 4.46881Z" fill=""/>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </template>
                                                    <template x-if="selected.length === 0">
                                                        <span class="text-gray-400">Select room service preferences...</span>
                                                    </template>
                                                </div>
                                                <span class="flex items-center text-gray-400">
                                                    <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M5.29289 7.29289C5.68342 6.90237 6.31658 6.90237 6.70711 7.29289L10 10.5858L13.2929 7.29289C13.6834 6.90237 14.3166 6.90237 14.7071 7.29289C15.0976 7.68342 15.0976 8.31658 14.7071 8.70711L10.7071 12.7071C10.3166 13.0976 9.68342 13.0976 9.29289 12.7071L5.29289 8.70711C4.90237 8.31658 4.90237 7.68342 5.29289 7.29289Z" fill=""/>
                                                    </svg>
                                                </span>
                                            </div>
                                            
                                            <div x-show="open" @click.outside="open = false"
                                                class="absolute z-99999 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900">
                                                <div class="max-h-60 overflow-y-auto p-2">
                                                    <template x-for="option in options" :key="option.id">
                                                        <div @click="toggleOption(option.id)"
                                                            :class="isSelected(option.id) ? 'bg-primary/5 text-primary' : 'hover:bg-gray-50 dark:hover:bg-gray-800'" {{-- UPDATED --}}
                                                            class="flex cursor-pointer items-center rounded-lg px-3 py-2 text-sm">
                                                            <div class="flex h-5 w-5 items-center justify-center rounded border border-gray-300 mr-2">
                                                                <template x-if="isSelected(option.id)">
                                                                    <svg class="fill-current" width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M10.7071 2.29289C11.0976 2.68342 11.0976 3.31658 10.7071 3.70711L4.70711 9.70711C4.31658 10.0976 3.68342 10.0976 3.29289 9.70711L0.292893 6.70711C-0.0976311 6.31658 -0.0976311 5.68342 0.292893 5.29289C0.683417 4.90237 1.31658 4.90237 1.70711 5.29289L4 7.58579L9.29289 2.29289C9.68342 1.90237 10.3166 1.90237 10.7071 2.29289Z" fill=""/>
                                                                    </svg>
                                                                </template>
                                                            </div>
                                                            <span x-text="option.name"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Restaurant Preferences -->
                                        <div x-data="{
                                            open: false,
                                            get selected() {
                                                return guestForm.guest_profile.preferences.restaurant || [];
                                            },
                                            set selected(value) {
                                                guestForm.guest_profile.preferences.restaurant = value;
                                            },
                                            options: [
                                                { id: 'quiet_spot', name: 'Quiet Spot' },
                                                { id: 'window_table', name: 'Window Table' },
                                                { id: 'private_booth', name: 'Private Booth' },
                                                { id: 'outdoor', name: 'Outdoor Seating' },
                                                { id: 'family', name: 'Family Area' },
                                                { id: 'business', name: 'Business Area' },
                                                { id: 'any', name: 'Any' }
                                            ],
                                            toggleOption(id) {
                                                if (this.selected.includes(id)) {
                                                    this.selected = this.selected.filter(i => i !== id);
                                                } else {
                                                    this.selected = [...this.selected, id];
                                                }
                                            },
                                            isSelected(id) {
                                                return this.selected.includes(id);
                                            }
                                        }" class="relative">
                                            <label class="block text-sm font-medium text-gray-600 mb-1">
                                                Restaurant Preferences
                                            </label>
                                            
                                            <div @click="open = !open"
                                                class="shadow-theme-xs flex min-h-11 cursor-pointer gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 transition dark:border-gray-700 dark:bg-gray-900">
                                                <div class="flex flex-1 flex-wrap items-center gap-2">
                                                    <template x-for="id in selected" :key="id">
                                                        <div class="group flex items-center justify-center rounded-full border-[0.7px] border-transparent bg-gray-100 py-1 pr-2 pl-2.5 text-sm text-gray-800 hover:border-gray-200 dark:bg-gray-800 dark:text-white/90 dark:hover:border-gray-800">
                                                            <span x-text="options.find(o => o.id === id).name"></span>
                                                            <button type="button"
                                                                    @click.stop="toggleOption(id)"
                                                                    class="ml-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                                                <svg class="fill-current" role="button" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M3.40717 4.46881C3.11428 4.17591 3.11428 3.70104 3.40717 3.40815C3.70006 3.11525 4.17494 3.11525 4.46783 3.40815L6.99943 5.93975L9.53095 3.40822C9.82385 3.11533 10.2987 3.11533 10.5916 3.40822C10.8845 3.70112 10.8845 4.17599 10.5916 4.46888L8.06009 7.00041L10.5916 9.53193C10.8845 9.82482 10.8845 10.2997 10.5916 10.5926C10.2987 10.8855 9.82385 10.8855 9.53095 10.5926L6.99943 8.06107L4.46783 10.5927C4.17494 10.8856 3.70006 10.8856 3.40717 10.5927C3.11428 10.2998 3.11428 9.8249 3.40717 9.53201L5.93877 7.00041L3.40717 4.46881Z" fill=""/>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </template>
                                                    <template x-if="selected.length === 0">
                                                        <span class="text-gray-400">Select restaurant preferences...</span>
                                                    </template>
                                                </div>
                                                <span class="flex items-center text-gray-400">
                                                    <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M5.29289 7.29289C5.68342 6.90237 6.31658 6.90237 6.70711 7.29289L10 10.5858L13.2929 7.29289C13.6834 6.90237 14.3166 6.90237 14.7071 7.29289C15.0976 7.68342 15.0976 8.31658 14.7071 8.70711L10.7071 12.7071C10.3166 13.0976 9.68342 13.0976 9.29289 12.7071L5.29289 8.70711C4.90237 8.31658 4.90237 7.68342 5.29289 7.29289Z" fill=""/>
                                                    </svg>
                                                </span>
                                            </div>
                                            
                                            <div x-show="open" @click.outside="open = false"
                                                class="absolute z-99999 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900">
                                                <div class="max-h-60 overflow-y-auto p-2">
                                                    <template x-for="option in options" :key="option.id">
                                                        <div @click="toggleOption(option.id)"
                                                            :class="isSelected(option.id) ? 'bg-primary/5 text-primary' : 'hover:bg-gray-50 dark:hover:bg-gray-800'" {{-- UPDATED --}}
                                                            class="flex cursor-pointer items-center rounded-lg px-3 py-2 text-sm">
                                                            <div class="flex h-5 w-5 items-center justify-center rounded border border-gray-300 mr-2">
                                                                <template x-if="isSelected(option.id)">
                                                                    <svg class="fill-current" width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M10.7071 2.29289C11.0976 2.68342 11.0976 3.31658 10.7071 3.70711L4.70711 9.70711C4.31658 10.0976 3.68342 10.0976 3.29289 9.70711L0.292893 6.70711C-0.0976311 6.31658 -0.0976311 5.68342 0.292893 5.29289C0.683417 4.90237 1.31658 4.90237 1.70711 5.29289L4 7.58579L9.29289 2.29289C9.68342 1.90237 10.3166 1.90237 10.7071 2.29289Z" fill=""/>
                                                                    </svg>
                                                                </template>
                                                            </div>
                                                            <span x-text="option.name"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Check-in Time Preferences -->
                                        <div x-data="{
                                            open: false,
                                            get selected() {
                                                return guestForm.guest_profile.preferences.checkin_time || [];
                                            },
                                            set selected(value) {
                                                guestForm.guest_profile.preferences.checkin_time = value;
                                            },
                                            options: [
                                                { id: 'early_check_in', name: 'Early Check-in (Before 2 PM)' },
                                                { id: 'standard_time', name: 'Standard Time (2 PM)' },
                                                { id: 'late_check_in', name: 'Late Check-in (After 4 PM)' },
                                                { id: 'flexible', name: 'Flexible' },
                                                { id: 'specific_time', name: 'Specific Time' }
                                            ],
                                            toggleOption(id) {
                                                if (this.selected.includes(id)) {
                                                    this.selected = this.selected.filter(i => i !== id);
                                                } else {
                                                    this.selected = [...this.selected, id];
                                                }
                                            },
                                            isSelected(id) {
                                                return this.selected.includes(id);
                                            }
                                        }" class="relative">
                                            <label class="block text-sm font-medium text-gray-600 mb-1">
                                                Check-in Time Preferences
                                            </label>
                                            
                                            <div @click="open = !open"
                                                class="shadow-theme-xs flex min-h-11 cursor-pointer gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 transition dark:border-gray-700 dark:bg-gray-900">
                                                <div class="flex flex-1 flex-wrap items-center gap-2">
                                                    <template x-for="id in selected" :key="id">
                                                        <div class="group flex items-center justify-center rounded-full border-[0.7px] border-transparent bg-gray-100 py-1 pr-2 pl-2.5 text-sm text-gray-800 hover:border-gray-200 dark:bg-gray-800 dark:text-white/90 dark:hover:border-gray-800">
                                                            <span x-text="options.find(o => o.id === id).name"></span>
                                                            <button type="button"
                                                                    @click.stop="toggleOption(id)"
                                                                    class="ml-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                                                <svg class="fill-current" role="button" width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M3.40717 4.46881C3.11428 4.17591 3.11428 3.70104 3.40717 3.40815C3.70006 3.11525 4.17494 3.11525 4.46783 3.40815L6.99943 5.93975L9.53095 3.40822C9.82385 3.11533 10.2987 3.11533 10.5916 3.40822C10.8845 3.70112 10.8845 4.17599 10.5916 4.46888L8.06009 7.00041L10.5916 9.53193C10.8845 9.82482 10.8845 10.2997 10.5916 10.5926C10.2987 10.8855 9.82385 10.8855 9.53095 10.5926L6.99943 8.06107L4.46783 10.5927C4.17494 10.8856 3.70006 10.8856 3.40717 10.5927C3.11428 10.2998 3.11428 9.8249 3.40717 9.53201L5.93877 7.00041L3.40717 4.46881Z" fill=""/>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </template>
                                                    <template x-if="selected.length === 0">
                                                        <span class="text-gray-400">Select check-in time preferences...</span>
                                                    </template>
                                                </div>
                                                <span class="flex items-center text-gray-400">
                                                    <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M5.29289 7.29289C5.68342 6.90237 6.31658 6.90237 6.70711 7.29289L10 10.5858L13.2929 7.29289C13.6834 6.90237 14.3166 6.90237 14.7071 7.29289C15.0976 7.68342 15.0976 8.31658 14.7071 8.70711L10.7071 12.7071C10.3166 13.0976 9.68342 13.0976 9.29289 12.7071L5.29289 8.70711C4.90237 8.31658 4.90237 7.68342 5.29289 7.29289Z" fill=""/>
                                                    </svg>
                                                </span>
                                            </div>
                                            
                                            <div x-show="open" @click.outside="open = false"
                                                class="absolute z-99999 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900">
                                                <div class="max-h-60 overflow-y-auto p-2">
                                                    <template x-for="option in options" :key="option.id">
                                                        <div @click="toggleOption(option.id)"
                                                            :class="isSelected(option.id) ? 'bg-primary/5 text-primary' : 'hover:bg-gray-50 dark:hover:bg-gray-800'" {{-- UPDATED --}}
                                                            class="flex cursor-pointer items-center rounded-lg px-3 py-2 text-sm">
                                                            <div class="flex h-5 w-5 items-center justify-center rounded border border-gray-300 mr-2">
                                                                <template x-if="isSelected(option.id)">
                                                                    <svg class="fill-current" width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M10.7071 2.29289C11.0976 2.68342 11.0976 3.31658 10.7071 3.70711L4.70711 9.70711C4.31658 10.0976 3.68342 10.0976 3.29289 9.70711L0.292893 6.70711C-0.0976311 6.31658 -0.0976311 5.68342 0.292893 5.29289C0.683417 4.90237 1.31658 4.90237 1.70711 5.29289L4 7.58579L9.29289 2.29289C9.68342 1.90237 10.3166 1.90237 10.7071 2.29289Z" fill=""/>
                                                                    </svg>
                                                                </template>
                                                            </div>
                                                            <span x-text="option.name"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Other Preferences Text Input -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-600 mb-1">
                                                Other Preferences
                                            </label>
                                            <textarea x-model="guestForm.guest_profile.preferences.other" rows="2"
                                                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20"
                                                    placeholder="Any other preferences or comments..."></textarea> {{-- UPDATED --}}
                                        </div>
                                        
                                        <!-- Allergies Text Input -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-600 mb-1">
                                                Allergies / Dietary Restrictions
                                            </label>
                                            <textarea x-model="guestForm.guest_profile.preferences.allergies" rows="2"
                                                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20"
                                                    placeholder="List any allergies or dietary restrictions..."></textarea> {{-- UPDATED --}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Emergency Contact -->
                    <div class="border-t border-gray-200 pt-6">
                        <h4 class="text-sm font-semibold text-gray-700 mb-4">Emergency Contact</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">
                                    Emergency Contact Name
                                </label>
                                <input type="text" x-model="guestForm.guest_profile.emergency_contact.name"
                                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20"> {{-- UPDATED --}}
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">
                                    Relationship *
                                </label>
                                <select x-model="guestForm.guest_profile.emergency_contact.relationship"
                                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20"> {{-- UPDATED --}}
                                    <option value="">Select Relationship</option>
                                    <option value="spouse">Spouse</option>
                                    <option value="parent">Parent</option>
                                    <option value="child">Child</option>
                                    <option value="sibling">Sibling</option>
                                    <option value="friend">Friend</option>
                                    <option value="colleague">Colleague</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">
                                    Emergency Phone
                                </label>
                                <input type="tel" x-model="guestForm.guest_profile.emergency_contact.phone"
                                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20"> {{-- UPDATED --}}
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">
                                    Emergency Email
                                </label>
                                <input type="email" x-model="guestForm.guest_profile.emergency_contact.email"
                                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20"> {{-- UPDATED --}}
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-600 mb-1">
                                    Emergency Address
                                </label>
                                <textarea x-model="guestForm.guest_profile.emergency_contact.address" rows="2"
                                          class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20"></textarea> {{-- UPDATED --}}
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 mt-6">
                        <button type="button" @click="showGuestModal = false" 
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                            Cancel
                        </button>
                        <button type="submit" :disabled="isSubmitting"
                                class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed"> {{-- UPDATED --}}
                            <span x-show="!isSubmitting" x-text="editingGuest ? 'Update Guest' : 'Create Guest'"></span>
                            <span x-show="isSubmitting">
                                <i class="fas fa-spinner fa-spin mr-1"></i> <span x-text="editingGuest ? 'Updating...' : 'Creating...'"></span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-99999 overflow-y-auto">
        <div x-show="showDeleteModal" 
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 flex items-center justify-center p-5 modal">
            <div class="modal-close-btn fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-[32px]" @click="showDeleteModal = false"></div>
            
            <div x-show="showDeleteModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                @click.outside="showDeleteModal = false"
                class="relative w-full max-w-[480px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-8">
                
                <!-- Close Button -->
                <button
                    @click="showDeleteModal = false"
                    class="group absolute right-3 top-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-200 text-gray-500 transition-colors hover:bg-gray-300 hover:text-gray-500 dark:bg-gray-800 dark:hover:bg-gray-700 sm:right-6 sm:top-6 sm:h-11 sm:w-11"
                >
                    <svg
                        class="transition-colors fill-current group-hover:text-gray-600 dark:group-hover:text-gray-200"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            fill-rule="evenodd"
                            clip-rule="evenodd"
                            d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z"
                            fill=""
                        />
                    </svg>
                </button>

                <div class="text-center pt-4">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                        <svg class="h-8 w-8 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.236 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <h4 class="mt-4 text-lg font-medium text-gray-800 dark:text-white/90">Delete Guest</h4>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Are you sure you want to delete guest <span class="font-semibold text-gray-800 dark:text-white/90" x-text="guestToDeleteName"></span>?
                        This action cannot be undone and will also delete all associated stays.
                    </p>
                </div>
                
                <div class="flex items-center justify-end w-full gap-3 mt-6 pt-6 border-t border-gray-100 dark:border-gray-800">
                    <button
                        @click="showDeleteModal = false"
                        type="button"
                        class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
                    >
                        Cancel
                    </button>
                    <button
                        @click="deleteGuest()"
                        type="button"
                        class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg bg-red-600 shadow-theme-xs hover:bg-red-700 sm:w-auto"
                    >
                        Delete Guest
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Redirect Modal -->
    <div x-show="showRedirectModal" x-cloak class="fixed inset-0 z-99999 overflow-y-auto">
        <div x-show="showRedirectModal" 
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 flex items-center justify-center p-5 modal">
            <div class="modal-close-btn fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-[32px]" @click="showRedirectModal = false"></div>
            
            <div x-show="showRedirectModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                @click.outside="showRedirectModal = false"
                class="relative w-full max-w-[480px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-8">
                
                <!-- Close Button -->
                <button
                    @click="showRedirectModal = false"
                    class="group absolute right-3 top-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-200 text-gray-500 transition-colors hover:bg-gray-300 hover:text-gray-500 dark:bg-gray-800 dark:hover:bg-gray-700 sm:right-6 sm:top-6 sm:h-11 sm:w-11"
                >
                    <svg
                        class="transition-colors fill-current group-hover:text-gray-600 dark:group-hover:text-gray-200"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            fill-rule="evenodd"
                            clip-rule="evenodd"
                            d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z"
                            fill=""
                        />
                    </svg>
                </button>

                <div class="text-center pt-4">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                        <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h4 class="mt-4 text-lg font-medium text-gray-800 dark:text-white/90">Guest Created Successfully!</h4>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        What would you like to do next?
                    </p>
                </div>
                
                <div class="mt-6 space-y-3">
                    <button @click="redirectToGuest()" 
                            class="flex w-full items-center justify-center rounded-lg bg-primary px-4 py-3 text-sm font-medium text-white shadow-theme-xs transition-colors hover:bg-primary/90"> {{-- UPDATED --}}
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Go to Guest Profile
                    </button>
                    
                    <button @click="redirectToGuestWithStay()" 
                            class="flex w-full items-center justify-center rounded-lg bg-secondary px-4 py-3 text-sm font-medium text-white shadow-theme-xs transition-colors hover:bg-secondary/90"> {{-- UPDATED --}}
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Go to Profile & Add Stay
                    </button>
                    
                    <button @click="closeRedirectModal()" 
                            class="flex w-full items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const countries = @json($countries);

// Dropdown Component
function dropdown() {
    return {
        open: false,
        toggle() {
            this.open = !this.open;
        }
    }
}

// Main Guest Table Component
function guestTable() {
    return {
        showGuestModal: false,
        showDeleteModal: false,
        showRedirectModal: false,
        redirectUrl: '',
        guestToDeleteId: null,
        guestToDeleteName: '',
        editingGuest: false,

        loading: true,
        guests: [],
        stats: {
            totalGuests: {{ $totalGuests ?? 0 }},
            activeGuests: {{ $activeGuests ?? 0 }},
            checkinsToday: {{ $checkinsToday ?? 0 }},
            checkoutsToday: {{ $checkoutsToday ?? 0 }}
        },
        filters: {
            search: '',
            status: '',
            nationality: '',
        },
        sort: {
            field: 'id',
            direction: 'desc'
        },
        pagination: {
            currentPage: 1,
            perPage: 10,
            total: 0,
            lastPage: 1,
            from: 0,
            to: 0
        },
        isSubmitting: false,
        guestForm: {
            id: null,
            user: {
                first_name: '',
                last_name: '',
                email: '',
                phone: '',
            },
            guest_profile: {
                id_type: '',
                id_number: '',
                nationality: '',
                address: '',
                emergency_contact: {
                    name: '',
                    email: '',
                    phone: '',
                    relationship: '',
                    address: ''
                },
                preferences: {
                    room_preference: [],
                    entertainment: [],
                    room_service: [],
                    restaurant: [],
                    checkin_time: [],
                    other: '',
                    allergies: ''
                }
            }
        },

        init() {
            // Make guestTable accessible globally for multiSelect component
            window.guestTable = this;
            this.fetchGuests();
        },

        async fetchGuests() {
            this.loading = true;
            
            const params = new URLSearchParams({
                page: this.pagination.currentPage,
                per_page: this.pagination.perPage,
                sort_by: this.sort.field,
                sort_direction: this.sort.direction
            });

            Object.entries(this.filters).forEach(([key, value]) => {
                if (value && value !== '') {
                    params.append(key, value);
                }
            });

            try {
                const response = await fetch(`{{ route('guests.index') }}?${params}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.guests = data.data || [];
                    this.pagination = {
                        currentPage: data.current_page || 1,
                        perPage: data.per_page || 10,
                        total: data.total || 0,
                        lastPage: data.last_page || 1,
                        from: data.from || 0,
                        to: data.to || 0
                    };
                    
                    if (data.stats) {
                        this.stats = data.stats;
                    }
                } else {
                    console.error('Failed to fetch guests:', response.status);
                    this.guests = [];
                }
            } catch (error) {
                console.error('Error fetching guests:', error);
                this.guests = [];
                this.showToast('Failed to load guests. Please try again.', 'error');
            } finally {
                this.loading = false;
            }
        },

        getInitials(name) {
            if (!name) return 'G';
            const names = name.split(' ');
            let initials = names[0].charAt(0).toUpperCase();
            if (names.length > 1) {
                initials += names[1].charAt(0).toUpperCase();
            }
            return initials;
        },

        getReadableIdType(idType) {
            const map = {
                'passport': 'Passport',
                'national_id': 'National ID',
                'driving_license': 'Driving License',
                'other': 'Other'
            };
            return map[idType] || idType;
        },

        getGuestStatus(guest) {
            if (!guest.user?.currentStay) return 'Inactive';
            if (guest.user.currentStay.status === 'checked_in') return 'Active';
            if (guest.user.currentStay.status === 'booked') return 'Booked';
            return 'Inactive';
        },

        sortBy(field) {
            if (this.sort.field === field) {
                this.sort.direction = this.sort.direction === 'asc' ? 'desc' : 'asc';
            } else {
                this.sort.field = field;
                this.sort.direction = 'asc';
            }
            this.fetchGuests();
        },

        prevPage() {
            if (this.pagination.currentPage > 1) {
                this.pagination.currentPage--;
                this.fetchGuests();
            }
        },

        nextPage() {
            if (this.pagination.currentPage < this.pagination.lastPage) {
                this.pagination.currentPage++;
                this.fetchGuests();
            }
        },

        goToPage(page) {
            if (page !== '...') {
                this.pagination.currentPage = page;
                this.fetchGuests();
            }
        },

        getVisiblePages() {
            const current = this.pagination.currentPage;
            const last = this.pagination.lastPage;
            const delta = 2;
            const range = [];
            
            if (last <= 1) return [1];
            
            for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i++) {
                range.push(i);
            }
            
            if (current - delta > 2) {
                range.unshift('...');
            }
            if (current + delta < last - 1) {
                range.push('...');
            }
            
            range.unshift(1);
            if (last !== 1) range.push(last);
            
            return range.filter((page, index, array) => {
                return page === '...' || array.indexOf(page) === index;
            });
        },

        clearFilters() {
            this.filters = {
                search: '',
                status: '',
                nationality: '',
            };
            this.pagination.currentPage = 1;
            this.fetchGuests();
        },

        openCreateModal() {
            this.showGuestModal = true;
            this.editingGuest = false;
            this.resetGuestForm();
        },

openEditModal(guest) {
    this.showGuestModal = true;
    this.editingGuest = true;
    this.resetGuestForm();
    
    // Populate form with guest data
    this.guestForm.id = guest.id;
    
    // Split name into first and last
    if (guest.user?.name) {
        const names = guest.user.name.split(' ');
        this.guestForm.user.first_name = names[0] || '';
        this.guestForm.user.last_name = names.slice(1).join(' ') || '';
    }
    
    this.guestForm.user.email = guest.user?.email || '';
    this.guestForm.user.phone = guest.user?.phone || '';
    
    this.guestForm.guest_profile.id_type = guest.id_type || '';
    this.guestForm.guest_profile.id_number = guest.id_number || '';
    this.guestForm.guest_profile.nationality = guest.nationality || '';
    this.guestForm.guest_profile.address = guest.address || '';
    
    // Parse emergency contact
    if (guest.emergency_contact) {
        if (typeof guest.emergency_contact === 'string') {
            try {
                const ec = JSON.parse(guest.emergency_contact);
                this.guestForm.guest_profile.emergency_contact = {
                    name: ec.name || '',
                    email: ec.email || '',
                    phone: ec.phone || '',
                    relationship: ec.relationship || '',
                    address: ec.address || ''
                };
            } catch (e) {
                this.guestForm.guest_profile.emergency_contact.name = guest.emergency_contact;
            }
        } else if (typeof guest.emergency_contact === 'object') {
            this.guestForm.guest_profile.emergency_contact = guest.emergency_contact;
        }
    }
    
    // Parse preferences - FIX: Handle null values
    if (guest.preferences) {
        if (typeof guest.preferences === 'string') {
            try {
                const prefs = JSON.parse(guest.preferences);
                // Convert null to empty strings
                this.guestForm.guest_profile.preferences = {
                    room_preference: prefs.room_preference ? 
                        (Array.isArray(prefs.room_preference) ? prefs.room_preference : 
                        (typeof prefs.room_preference === 'string' ? prefs.room_preference.split(',').filter(v => v) : [])) 
                        : [],
                    entertainment: prefs.entertainment ? 
                        (Array.isArray(prefs.entertainment) ? prefs.entertainment : 
                        (typeof prefs.entertainment === 'string' ? prefs.entertainment.split(',').filter(v => v) : [])) 
                        : [],
                    room_service: prefs.room_service ? 
                        (Array.isArray(prefs.room_service) ? prefs.room_service : 
                        (typeof prefs.room_service === 'string' ? prefs.room_service.split(',').filter(v => v) : [])) 
                        : [],
                    restaurant: prefs.restaurant ? 
                        (Array.isArray(prefs.restaurant) ? prefs.restaurant : 
                        (typeof prefs.restaurant === 'string' ? prefs.restaurant.split(',').filter(v => v) : [])) 
                        : [],
                    checkin_time: prefs.checkin_time ? 
                        (Array.isArray(prefs.checkin_time) ? prefs.checkin_time : 
                        (typeof prefs.checkin_time === 'string' ? prefs.checkin_time.split(',').filter(v => v) : [])) 
                        : [],
                    other: prefs.other || '',
                    allergies: prefs.allergies || ''
                };
            } catch (e) {
                console.error('Error parsing preferences:', e);
                // Set defaults
                this.guestForm.guest_profile.preferences = {
                    room_preference: [],
                    entertainment: [],
                    room_service: [],
                    restaurant: [],
                    checkin_time: [],
                    other: '',
                    allergies: ''
                };
            }
        } else if (typeof guest.preferences === 'object') {
            // Handle object directly
            this.guestForm.guest_profile.preferences = {
                room_preference: guest.preferences.room_preference || [],
                entertainment: guest.preferences.entertainment || [],
                room_service: guest.preferences.room_service || [],
                restaurant: guest.preferences.restaurant || [],
                checkin_time: guest.preferences.checkin_time || [],
                other: guest.preferences.other || '',
                allergies: guest.preferences.allergies || ''
            };
        }
    }
},

        resetGuestForm() {
            this.guestForm = {
                id: null,
                user: {
                    first_name: '',
                    last_name: '',
                    email: '',
                    phone: '',
                },
                guest_profile: {
                    id_type: '',
                    id_number: '',
                    nationality: '',
                    address: '',
                    emergency_contact: {
                        name: '',
                        email: '',
                        phone: '',
                        relationship: '',
                        address: ''
                    },
                    preferences: {
                        room_preference: [],
                        entertainment: [],
                        room_service: [],
                        restaurant: [],
                        checkin_time: [],
                        other: '',
                        allergies: ''
                    }
                }
            };
        },

        confirmDeleteGuest(guestId, guestName) {
            this.guestToDeleteId = guestId;
            this.guestToDeleteName = guestName || 'this guest';
            this.showDeleteModal = true;
        },

        async deleteGuest() {
            if (!this.guestToDeleteId) return;

            try {
                const response = await fetch(`/guests/${this.guestToDeleteId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    this.showToast('Guest deleted successfully', 'success');
                    this.showDeleteModal = false;
                    this.guestToDeleteId = null;
                    this.guestToDeleteName = '';
                    this.refreshTable();
                } else {
                    this.showToast('Error deleting guest', 'error');
                }
            } catch (error) {
                console.error('Error deleting guest:', error);
                this.showToast('Error deleting guest', 'error');
            }
        },

        async refreshTable() {
            await this.fetchGuests();
        },

        async createGuest() {
            // Validate required fields
            if (!this.guestForm.user.first_name.trim() || 
                !this.guestForm.user.last_name.trim() || 
                !this.guestForm.user.email.trim() ||
                !this.guestForm.guest_profile.id_type ||
                !this.guestForm.guest_profile.id_number.trim() ||
                !this.guestForm.guest_profile.nationality.trim() ||
                !this.guestForm.guest_profile.emergency_contact.relationship.trim()) {
                
                this.showToast('Please fill all required fields (marked with *)', 'error');
                return;
            }

            // Validate email format
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(this.guestForm.user.email)) {
                this.showToast('Please enter a valid email address', 'error');
                return;
            }

            this.isSubmitting = true;
            
            try {
                // Prepare preferences arrays as comma-separated strings for form data
                const formData = {
                    user: {
                        first_name: this.guestForm.user.first_name.trim(),
                        last_name: this.guestForm.user.last_name.trim(),
                        email: this.guestForm.user.email.trim(),
                        phone: this.guestForm.user.phone ? this.guestForm.user.phone.trim() : null
                    },
                    guest_profile: {
                        id_type: this.guestForm.guest_profile.id_type,
                        id_number: this.guestForm.guest_profile.id_number.trim(),
                        nationality: this.guestForm.guest_profile.nationality.trim(),
                        address: this.guestForm.guest_profile.address ? this.guestForm.guest_profile.address.trim() : null,
                        emergency_contact: this.guestForm.guest_profile.emergency_contact,
                        preferences: {
                            room_preference: Array.isArray(this.guestForm.guest_profile.preferences.room_preference) ? 
                                            this.guestForm.guest_profile.preferences.room_preference : [],
                            entertainment: Array.isArray(this.guestForm.guest_profile.preferences.entertainment) ? 
                                          this.guestForm.guest_profile.preferences.entertainment : [],
                            room_service: Array.isArray(this.guestForm.guest_profile.preferences.room_service) ? 
                                          this.guestForm.guest_profile.preferences.room_service : [],
                            restaurant: Array.isArray(this.guestForm.guest_profile.preferences.restaurant) ? 
                                        this.guestForm.guest_profile.preferences.restaurant : [],
                            checkin_time: Array.isArray(this.guestForm.guest_profile.preferences.checkin_time) ? 
                                          this.guestForm.guest_profile.preferences.checkin_time : [],
                            other: this.guestForm.guest_profile.preferences.other || '',
                            allergies: this.guestForm.guest_profile.preferences.allergies || ''
                        }
                    }
                };

                console.log('Sending data to server:', JSON.stringify(formData, null, 2));

                const response = await fetch('{{ route("guests.store") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });
                
                const data = await response.json();
                console.log('Response from server:', data);
                
                if (response.status === 422) {
                    // Validation errors
                    let errorMessages = [];
                    if (data.errors) {
                        Object.values(data.errors).forEach(errors => {
                            errorMessages = errorMessages.concat(errors);
                        });
                    }
                    this.showToast(errorMessages.join('\n') || 'Validation failed', 'error');
                    this.isSubmitting = false;
                    return;
                }

                if (response.ok && data.success) {
                    this.showToast(data.message || 'Guest created successfully!', 'success');
                    this.showGuestModal = false;
                    
                    // Reset the form
                    this.resetGuestForm();
                    
                    // Refresh the table
                    await this.refreshTable();
                    
                    // Show redirect options if redirect URL is provided
                    if (data.redirect_url) {
                        this.redirectUrl = data.redirect_url;
                        this.showRedirectModal = true;
                    }
                } else {
                    this.showToast(data.message || 'Error creating guest', 'error');
                }
            } catch (error) {
                console.error('Error creating guest:', error);
                this.showToast('Error creating guest. Please try again.', 'error');
            } finally {
                this.isSubmitting = false;
            }
        },

        async updateGuest() {
            if (!this.guestForm.id) {
                this.showToast('Guest ID not found', 'error');
                return;
            }

            // Validate required fields
            if (!this.guestForm.user.first_name.trim() || 
                !this.guestForm.user.last_name.trim() || 
                !this.guestForm.user.email.trim() ||
                !this.guestForm.guest_profile.id_type ||
                !this.guestForm.guest_profile.id_number.trim() ||
                !this.guestForm.guest_profile.nationality.trim() ||
                !this.guestForm.guest_profile.emergency_contact.relationship.trim()) {
                
                this.showToast('Please fill all required fields (marked with *)', 'error');
                return;
            }

            // Validate email format
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(this.guestForm.user.email)) {
                this.showToast('Please enter a valid email address', 'error');
                return;
            }

            this.isSubmitting = true;
            
            try {
                const formData = {
                    user: {
                        first_name: this.guestForm.user.first_name.trim(),
                        last_name: this.guestForm.user.last_name.trim(),
                        email: this.guestForm.user.email.trim(),
                        phone: this.guestForm.user.phone ? this.guestForm.user.phone.trim() : null
                    },
                    guest_profile: {
                        id_type: this.guestForm.guest_profile.id_type,
                        id_number: this.guestForm.guest_profile.id_number.trim(),
                        nationality: this.guestForm.guest_profile.nationality.trim(),
                        address: this.guestForm.guest_profile.address ? this.guestForm.guest_profile.address.trim() : null,
                        emergency_contact: this.guestForm.guest_profile.emergency_contact,
                        preferences: {
                            room_preference: Array.isArray(this.guestForm.guest_profile.preferences.room_preference) ? 
                                            this.guestForm.guest_profile.preferences.room_preference : [],
                            entertainment: Array.isArray(this.guestForm.guest_profile.preferences.entertainment) ? 
                                          this.guestForm.guest_profile.preferences.entertainment : [],
                            room_service: Array.isArray(this.guestForm.guest_profile.preferences.room_service) ? 
                                          this.guestForm.guest_profile.preferences.room_service : [],
                            restaurant: Array.isArray(this.guestForm.guest_profile.preferences.restaurant) ? 
                                        this.guestForm.guest_profile.preferences.restaurant : [],
                            checkin_time: Array.isArray(this.guestForm.guest_profile.preferences.checkin_time) ? 
                                          this.guestForm.guest_profile.preferences.checkin_time : [],
                            other: this.guestForm.guest_profile.preferences.other || '',
                            allergies: this.guestForm.guest_profile.preferences.allergies || ''
                        }
                    }
                };

                console.log('Updating guest with data:', JSON.stringify(formData, null, 2));

                const response = await fetch(`/guests/${this.guestForm.id}`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });
                
                const data = await response.json();
                console.log('Response from server:', data);
                
                if (response.status === 422) {
                    // Validation errors
                    let errorMessages = [];
                    if (data.errors) {
                        Object.values(data.errors).forEach(errors => {
                            errorMessages = errorMessages.concat(errors);
                        });
                    }
                    this.showToast(errorMessages.join('\n') || 'Validation failed', 'error');
                    this.isSubmitting = false;
                    return;
                }

                if (response.ok && data.success) {
                    this.showToast(data.message || 'Guest updated successfully!', 'success');
                    this.showGuestModal = false;
                    
                    // Reset the form
                    this.resetGuestForm();
                    this.editingGuest = false;
                    
                    // Refresh the table
                    await this.refreshTable();
                } else {
                    this.showToast(data.message || 'Error updating guest', 'error');
                }
            } catch (error) {
                console.error('Error updating guest:', error);
                this.showToast('Error updating guest. Please try again.', 'error');
            } finally {
                this.isSubmitting = false;
            }
        },

        redirectToGuest() {
            if (this.redirectUrl) {
                window.location.href = this.redirectUrl;
            }
        },

        redirectToGuestWithStay() {
            if (this.redirectUrl) {
                window.location.href = this.redirectUrl + '?add_stay=true';
            }
        },

        closeRedirectModal() {
            this.showRedirectModal = false;
            this.redirectUrl = '';
        },

        showToast(message, type = 'info') {
            // Remove existing toasts
            document.querySelectorAll('.custom-toast').forEach(toast => toast.remove());
            
            const toast = document.createElement('div');
            toast.className = `custom-toast fixed top-4 right-4 px-4 py-3 rounded-lg text-white text-sm shadow-lg z-99999 transition-transform duration-300 transform ${
                type === 'success' ? 'bg-green-500' : 
                type === 'error' ? 'bg-red-500' : 
                'bg-primary'
            }`;
            toast.textContent = message;
            document.body.appendChild(toast);
            
            // Animate in
            setTimeout(() => {
                toast.classList.remove('transform');
            }, 10);
            
            // Remove after 4 seconds
            setTimeout(() => {
                toast.classList.add('opacity-0', 'transition-opacity', 'duration-300');
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 300);
            }, 4000);
        }
    }
}
</script>

<style>
[x-cloak] { display: none !important; }
.custom-toast {
    animation: slideIn 0.3s ease-out;
}

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

/* TailAdmin-like button styles */
.shadow-theme-xs {
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.shadow-theme-lg {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.text-theme-xs {
    font-size: 0.75rem;
    line-height: 1rem;
}
</style>