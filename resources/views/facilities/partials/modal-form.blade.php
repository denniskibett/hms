{{--
    Add / edit venue modal (managers).
      @include('facilities.partials.modal-form')
      open: $dispatch('open-venue-form') or $dispatch('open-venue-form', @js($facility->formData()))
--}}
@php
    $inputClass = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
@endphp

<div x-data="facilityForm(@js([
        'store' => route('facilities.store'),
        'update' => route('facilities.update', ':id'),
        'destroy' => route('facilities.destroy', ':id'),
        'blank' => ['id' => null, 'name' => '', 'code' => '', 'capacity' => 50, 'base_rate' => 0, 'description' => '', 'amenities' => [], 'status' => 'available'],
     ]))"
     @open-venue-form.window="open($event.detail)" @keydown.escape.window="close()">
    <div x-show="isOpen" x-transition x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center overflow-y-auto p-5">
        <div class="fixed inset-0 bg-gray-400/50 backdrop-blur-[32px] dark:bg-gray-900/80" @click="close()"></div>

        <div class="relative max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-3xl bg-white p-6 lg:p-10 dark:bg-gray-900">
            <button type="button" @click="close()"
                    class="absolute right-3 top-3 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-200 text-gray-500 hover:bg-gray-300 hover:text-gray-700 sm:right-6 sm:top-6 sm:h-11 sm:w-11 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>

            <h4 class="mb-6 text-lg font-medium text-gray-800 dark:text-white/90" x-text="form.id ? 'Edit Venue' : 'New Venue'"></h4>

            <form @submit.prevent="submit()" class="space-y-5">
                @include('facilities.partials.form-errors')

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="{{ $labelClass }}">Name *</label>
                        <input type="text" x-model="form.name" required maxlength="255" class="{{ $inputClass }}" placeholder="e.g. Main Conference Hall">
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Seats (capacity) *</label>
                        <input type="number" x-model.number="form.capacity" required min="1" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Venue hire per day ({{ SystemHelper::currencySymbol() }})</label>
                        <input type="number" x-model.number="form.base_rate" min="0" step="0.01" class="{{ $inputClass }}">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">0 if hire is included in the packages.</p>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Status *</label>
                        <select x-model="form.status" class="{{ $inputClass }}">
                            @foreach (\App\Models\Facility::STATUSES as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Code</label>
                        <input type="text" x-model="form.code" maxlength="50" class="{{ $inputClass }}" placeholder="Made from the name if blank">
                    </div>
                </div>

                <fieldset>
                    <legend class="mb-3 text-sm font-medium text-gray-800 dark:text-white/90">Amenities</legend>
                    <div class="flex flex-wrap gap-2">
                        @foreach (\App\Models\Facility::AMENITIES as $amenity)
                            <button type="button" @click="form.amenities.includes(@js($amenity)) ? form.amenities.splice(form.amenities.indexOf(@js($amenity)), 1) : form.amenities.push(@js($amenity))"
                                    :class="form.amenities.includes(@js($amenity))
                                        ? 'border-primary bg-primary/10 text-primary'
                                        : 'border-gray-300 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/[0.03]'"
                                    class="rounded-full border px-3 py-1 text-xs font-medium">{{ $amenity }}</button>
                        @endforeach
                    </div>
                </fieldset>

                <div>
                    <label class="{{ $labelClass }}">Description</label>
                    <textarea x-model="form.description" rows="3" maxlength="2000" class="{{ str_replace('h-11', '', $inputClass) }}" placeholder="Layout options, location, notes for clients"></textarea>
                </div>

                <div class="flex items-center justify-between gap-3">
                    <div>
                        <button type="button" x-show="form.id" @click="destroy()" class="text-sm text-error-500 hover:underline">Delete venue</button>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" @click="close()" class="rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">Cancel</button>
                        <button type="submit" :disabled="isLoading" :class="{ 'cursor-not-allowed opacity-50': isLoading }"
                                class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-primary/90">
                            <i class="fas fa-spinner fa-spin" x-show="isLoading" x-cloak></i>
                            <span x-text="form.id ? 'Save Changes' : 'Add Venue'"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@include('facilities.partials.scripts')
