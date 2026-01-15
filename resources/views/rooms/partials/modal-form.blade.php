<!-- Room Form Modal -->
<div x-show="isCreateModalOpen || isEditModalOpen" x-transition class="fixed inset-0 z-[99999] flex items-center justify-center p-5 overflow-y-auto" x-cloak>
    <div class="fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-[32px] dark:bg-gray-900/80" @click="closeModal()"></div>
    
    <div class="relative w-full max-w-2xl rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-10 max-h-[90vh] overflow-y-auto" @click.outside="closeModal()">
        
        <!-- Close Button -->
        <button @click="closeModal()" class="group absolute right-3 top-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-200 text-gray-500 transition-colors hover:bg-gray-300 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200 sm:right-6 sm:top-6 sm:h-11 sm:w-11">
            <svg class="transition-colors fill-current group-hover:text-gray-600 dark:group-hover:text-gray-200" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z" fill=""/>
            </svg>
        </button>

        <h4 class="mb-6 text-lg font-medium text-gray-800 dark:text-white/90">
            <span x-text="formData.id ? 'Edit Room' : 'Add New Room'"></span>
        </h4>

        <form @submit.prevent="submitForm()">
            <!-- Basic Information -->
            <div class="space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Room Number -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Room Number *
                        </label>
                        <input type="text" x-model="formData.room_number" required
                               class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                               placeholder="e.g., 101, 202A">
                    </div>

                    <!-- Room Type -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Room Type *
                        </label>
                        <select x-model="formData.room_type_id" required
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            <option value="">Select Room Type</option>
                            <template x-for="roomType in roomTypes" :key="roomType.id">
                                <option :value="roomType.id" x-text="roomType.name"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Status -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Status *
                        </label>
                        <select x-model="formData.status" required
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            @foreach(\App\Models\Room::getStatusOptions() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Floor -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Floor *
                        </label>
                        <input type="number" x-model="formData.floor" required min="1" max="50"
                               class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                               placeholder="Floor number">
                    </div>
                </div>

                <!-- Wing -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Wing (Optional)
                    </label>
                    <input type="text" x-model="formData.wing"
                           class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                           placeholder="e.g., North, South, East, West">
                </div>

                <!-- Pricing -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Adult Price (Optional)
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                {{ config('system.currency_symbol', '$') }}
                            </span>
                            <input type="number" x-model="formData.adult_price" min="0" step="0.01"
                                   class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none pl-10 pr-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                   placeholder="Leave empty to use room type default">
                        </div>
                    </div>
                    
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Child Price (Optional)
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                {{ config('system.currency_symbol', '$') }}
                            </span>
                            <input type="number" x-model="formData.child_price" min="0" step="0.01"
                                   class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none pl-10 pr-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                   placeholder="Leave empty to use 50% of adult price">
                        </div>
                    </div>
                </div>

                <!-- Features -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Room Features
                    </label>
                    
                    <!-- Add Feature -->
                    <div class="flex gap-2 mb-3">
                        <select x-model="formData.new_feature"
                                class="dark:bg-dark-900 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                            <option value="">Select a feature</option>
                            <template x-for="feature in availableFeatures" :key="feature">
                                <option :value="feature" x-text="feature"></option>
                            </template>
                        </select>
                        <button type="button" @click="addFeature()"
                                class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white shadow-theme-xs hover:bg-primary/90">
                            Add
                        </button>
                    </div>
                    
                    <!-- Features List -->
                    <div class="min-h-[60px] p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                        <template x-if="formData.features.length === 0">
                            <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-2">
                                No features added. Add some features above.
                            </p>
                        </template>
                        <template x-if="formData.features.length > 0">
                            <div class="flex flex-wrap gap-2">
                                <template x-for="(feature, index) in formData.features" :key="index">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1.5 text-sm text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        <span x-text="feature"></span>
                                        <button type="button" @click="removeFeature(index)"
                                                class="ml-1 text-gray-400 hover:text-red-500">
                                            <i class="fas fa-times text-xs"></i>
                                        </button>
                                    </span>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex w-full items-center justify-end gap-3 mt-8">
                <button type="button"
                        @click="closeModal()"
                        class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:w-auto">
                    Cancel
                </button>
                <button type="submit"
                        :disabled="isLoading"
                        :class="{ 'opacity-50 cursor-not-allowed': isLoading }"
                        class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg bg-primary shadow-theme-xs hover:bg-primary/90 sm:w-auto">
                    <span x-show="!isLoading">
                        <span x-text="formData.id ? 'Update Room' : 'Create Room'"></span>
                    </span>
                    <span x-show="isLoading" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="formData.id ? 'Updating...' : 'Creating...'"></span>
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>