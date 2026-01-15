<!-- Availability Check Modal -->
<div x-show="isAvailabilityModalOpen" x-transition class="fixed inset-0 z-[99999] flex items-center justify-center p-5 overflow-y-auto" x-cloak>
    <div class="fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-[32px] dark:bg-gray-900/80" @click="closeModal()"></div>
    
    <div class="relative w-full max-w-2xl rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-10 max-h-[90vh] overflow-y-auto" @click.outside="closeModal()">
        
        <!-- Close Button -->
        <button @click="closeModal()" class="group absolute right-3 top-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-200 text-gray-500 transition-colors hover:bg-gray-300 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200 sm:right-6 sm:top-6 sm:h-11 sm:w-11">
            <svg class="transition-colors fill-current group-hover:text-gray-600 dark:group-hover:text-gray-200" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z" fill=""/>
            </svg>
        </button>

        <h4 class="mb-6 text-lg font-medium text-gray-800 dark:text-white/90">
            Check Room Availability
        </h4>

        <div class="space-y-6">
            <!-- Selected Room -->
            <div class="p-4 bg-primary/5 rounded-lg border border-primary/20">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Selected Room</p>
                        <h5 class="text-xl font-bold text-primary" x-text="availabilityData.room_number"></h5>
                    </div>
                    <div class="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center">
                        <i class="fas fa-door-closed text-primary text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Date Selection -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Arrival Date -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Arrival Date *
                    </label>
                    <div class="relative">
                        <input type="date" x-model="availabilityData.arrival_date" required
                               class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        <span class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                            <i class="fas fa-calendar"></i>
                        </span>
                    </div>
                </div>

                <!-- Departure Date -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Departure Date *
                    </label>
                    <div class="relative">
                        <input type="date" x-model="availabilityData.departure_date" required
                               :min="availabilityData.arrival_date"
                               class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        <span class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                            <i class="fas fa-calendar"></i>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Check Button -->
            <div>
                <button @click="checkAvailability()"
                        :disabled="isLoading || !availabilityData.arrival_date || !availabilityData.departure_date"
                        :class="{ 'opacity-50 cursor-not-allowed': isLoading || !availabilityData.arrival_date || !availabilityData.departure_date }"
                        class="w-full rounded-lg bg-primary px-4 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-primary/90">
                    <span x-show="!isLoading" class="flex items-center justify-center gap-2">
                        <i class="fas fa-search"></i>
                        Check Availability
                    </span>
                    <span x-show="isLoading" class="flex items-center justify-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Checking...
                    </span>
                </button>
            </div>

            <!-- Results -->
            <template x-if="availabilityData.available_rooms.length > 0">
                <div>
                    <h5 class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-400">
                        Availability Results
                    </h5>
                    
                    <!-- Target Room Result -->
                    <div class="p-4 mb-4 rounded-lg border"
                         :class="availabilityData.is_available ? 
                                 'border-success bg-success/5' : 
                                 'border-danger bg-danger/5'">
                        <div class="flex items-center justify-between">
                            <div>
                                <h6 class="font-medium" x-text="availabilityData.room_number"></h6>
                                <p class="text-sm" 
                                   :class="availabilityData.is_available ? 
                                           'text-success dark:text-success-400' : 
                                           'text-danger dark:text-danger-400'">
                                    <span x-show="availabilityData.is_available">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Available for selected dates
                                    </span>
                                    <span x-show="!availabilityData.is_available">
                                        <i class="fas fa-times-circle mr-1"></i>
                                        Not available for selected dates
                                    </span>
                                </p>
                            </div>
                            <div class="h-10 w-10 rounded-full flex items-center justify-center"
                                 :class="availabilityData.is_available ? 
                                         'bg-success/10 text-success' : 
                                         'bg-danger/10 text-danger'">
                                <i :class="availabilityData.is_available ? 
                                          'fas fa-check' : 
                                          'fas fa-times'"></i>
                            </div>
                        </div>
                    </div>

                    <!-- All Rooms Results -->
                    <div class="space-y-2 max-h-60 overflow-y-auto">
                        <template x-for="room in availabilityData.available_rooms" :key="room.id">
                            <div class="p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-800 dark:text-white/90">
                                            Room <span x-text="room.room_number"></span>
                                            <span class="text-sm text-gray-500 dark:text-gray-400 ml-2">
                                                (<span x-text="room.room_type.name"></span>)
                                            </span>
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Floor <span x-text="room.floor"></span>
                                            <template x-if="room.wing">
                                                • <span x-text="room.wing"></span> Wing
                                            </template>
                                        </p>
                                    </div>
                                    <div>
                                        <span class="px-2 py-1 rounded-full text-xs font-medium"
                                              :class="room.is_available ? 
                                                      'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500' : 
                                                      'bg-danger-50 text-danger-600 dark:bg-danger-500/15 dark:text-danger-500'">
                                            <span x-show="room.is_available">Available</span>
                                            <span x-show="!room.is_available && room.next_available_date">
                                                Available from <span x-text="formatDisplayDate(room.next_available_date)"></span>
                                            </span>
                                            <span x-show="!room.is_available && !room.next_available_date">
                                                Not Available
                                            </span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        <!-- Form Actions -->
        <div class="flex w-full items-center justify-end gap-3 mt-8">
            <button type="button"
                    @click="closeModal()"
                    class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:w-auto">
                Close
            </button>
        </div>
    </div>
</div>