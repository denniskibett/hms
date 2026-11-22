{{-- Add / edit a room type (slide-over). State lives in roomTypesPage() on room-types.index. --}}
@php
    $f = 'h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10';
    $l = 'mb-1.5 block text-sm font-medium text-gray-700';
@endphp
<div x-show="isCreateModalOpen || isEditModalOpen" x-transition.opacity x-cloak class="slideover" @keydown.escape.window="closeModal()">
    <div class="fixed inset-0 modal-overlay" @click="closeModal()"></div>

    <form method="POST" :action="formData.id ? `/room-types/${formData.id}` : '{{ route('room-types.store') }}'" class="slideover-panel space-y-5">
        @csrf
        <template x-if="formData.id"><input type="hidden" name="_method" value="PUT"></template>

        <div class="flex items-start justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-800" x-text="formData.id ? 'Edit room type' : 'Add room type'"></h3>
                <p class="mt-1 text-sm text-gray-500">The rate here is the rack rate per night, before any company discount.</p>
            </div>
            <button type="button" @click="closeModal()" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-gray-700" aria-label="Close"><i class="fas fa-times"></i></button>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <label class="sm:col-span-2"><span class="{{ $l }}">Name *</span>
                <input type="text" name="name" x-model="formData.name" required maxlength="255" placeholder="e.g. Deluxe rooms" class="{{ $f }}"></label>
            <label><span class="{{ $l }}">Code *</span>
                <input type="text" name="code" x-model="formData.code" required maxlength="255" placeholder="e.g. DLX" class="{{ $f }} uppercase"></label>
            <label><span class="{{ $l }}">Rate per night ({{ SystemHelper::currencySymbol() }}) *</span>
                <input type="number" name="base_rate" x-model="formData.base_rate" required min="0" step="0.01" class="{{ $f }}"></label>
            <label><span class="{{ $l }}">Sleeps *</span>
                <input type="number" name="capacity" x-model="formData.capacity" required min="1" max="20" class="{{ $f }}"></label>
            <label><span class="{{ $l }}">Bed *</span>
                <select name="bed_type" x-model="formData.bed_type" required class="{{ $f }}">
                    <template x-for="(label, value) in bedTypes" :key="value"><option :value="value" x-text="label" :selected="value === formData.bed_type"></option></template>
                </select></label>
            <label class="sm:col-span-2"><span class="{{ $l }}">Description</span>
                <textarea name="description" x-model="formData.description" rows="3" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10"></textarea></label>
        </div>

        <div>
            <span class="{{ $l }}">Amenities</span>
            <div class="flex gap-2">
                <input type="text" list="room-type-amenities" x-model="formData.new_amenity" @keydown.enter.prevent="addAmenity()" placeholder="e.g. Free WiFi" class="{{ $f }}">
                <datalist id="room-type-amenities"><template x-for="a in availableAmenities" :key="a"><option :value="a"></option></template></datalist>
                <button type="button" @click="addAmenity()" class="shrink-0 rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 hover:bg-gray-50">Add</button>
            </div>
            <div class="mt-3 flex flex-wrap gap-2">
                <template x-for="(amenity, i) in formData.amenities" :key="amenity + i">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                        <span x-text="amenity"></span>
                        <input type="hidden" name="amenities[]" :value="amenity">
                        <button type="button" @click="removeAmenity(i)" class="text-gray-400 hover:text-error-600" aria-label="Remove"><i class="fas fa-times"></i></button>
                    </span>
                </template>
                <p x-show="!formData.amenities.length" class="text-sm text-gray-400">None added yet.</p>
            </div>
        </div>

        <div class="flex justify-end gap-3 border-t border-gray-100 pt-5">
            <button type="button" @click="closeModal()" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
            <button type="submit" class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 disabled:opacity-60" x-text="formData.id ? 'Save changes' : 'Add room type'"></button>
        </div>
    </form>
</div>
