{{--
    Add / edit package modal (managers).
      @include('facilities.partials.package-modal', ['venues' => $facilities])
      open: $dispatch('open-package-form') or $dispatch('open-package-form', @js($package->formData()))
--}}
@php
    $inputClass = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
@endphp

<div x-data="facilityForm(@js([
        'store' => route('facility-packages.store'),
        'update' => route('facility-packages.update', ':id'),
        'destroy' => route('facility-packages.destroy', ':id'),
        'blank' => ['id' => null, 'facility_id' => '', 'name' => '', 'price' => '', 'charge_basis' => 'per_person', 'inclusions' => '', 'is_active' => true],
     ]))"
     @open-package-form.window="open($event.detail)" @keydown.escape.window="close()">
    <div x-show="isOpen" x-transition x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center overflow-y-auto p-5">
        <div class="fixed inset-0 bg-gray-400/50 backdrop-blur-[32px] dark:bg-gray-900/80" @click="close()"></div>

        <div class="relative max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-3xl bg-white p-6 lg:p-10 dark:bg-gray-900">
            <button type="button" @click="close()"
                    class="absolute right-3 top-3 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-200 text-gray-500 hover:bg-gray-300 hover:text-gray-700 sm:right-6 sm:top-6 sm:h-11 sm:w-11 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>

            <h4 class="mb-6 text-lg font-medium text-gray-800 dark:text-white/90" x-text="form.id ? 'Edit Package' : 'New Package'"></h4>

            <form @submit.prevent="submit()" class="space-y-5">
                @include('facilities.partials.form-errors')

                <div>
                    <label class="{{ $labelClass }}">Name *</label>
                    <input type="text" x-model="form.name" required maxlength="255" class="{{ $inputClass }}" placeholder="e.g. Full Day Conference">
                </div>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label class="{{ $labelClass }}">Price ({{ SystemHelper::currencySymbol() }}) *</label>
                        <input type="number" x-model.number="form.price" required min="0" step="0.01" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Charged *</label>
                        <select x-model="form.charge_basis" class="{{ $inputClass }}">
                            @foreach (\App\Models\FacilityPackage::BASES as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="{{ $labelClass }}">Venue</label>
                    <select x-model="form.facility_id" class="{{ $inputClass }}">
                        <option value="">Any venue</option>
                        @foreach ($venues as $venue)
                            <option value="{{ $venue->id }}">{{ $venue->name }} only</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $labelClass }}">Inclusions</label>
                    <textarea x-model="form.inclusions" rows="3" maxlength="2000" class="{{ str_replace('h-11', '', $inputClass) }}" placeholder="e.g. 10 o'clock tea, buffet lunch, 4 o'clock tea, mineral water, stationery"></textarea>
                </div>
                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-400">
                    <input type="checkbox" x-model="form.is_active" class="h-4 w-4 rounded border-gray-300 text-primary">
                    Active (can be sold on new events)
                </label>

                <div class="flex items-center justify-between gap-3">
                    <div>
                        <button type="button" x-show="form.id" @click="destroy()" class="text-sm text-error-500 hover:underline">Delete package</button>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" @click="close()" class="rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">Cancel</button>
                        <button type="submit" :disabled="isLoading" :class="{ 'cursor-not-allowed opacity-50': isLoading }"
                                class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-primary/90">
                            <i class="fas fa-spinner fa-spin" x-show="isLoading" x-cloak></i>
                            <span x-text="form.id ? 'Save Changes' : 'Add Package'"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@include('facilities.partials.scripts')
