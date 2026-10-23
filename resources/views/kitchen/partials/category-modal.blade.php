{{--
    Add / edit menu category. open: $dispatch('open-category-form', record?)
--}}
@php
    $inputClass = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
@endphp

<div x-data="facilityForm(@js([
        'store' => route('kitchen.menu.categories.store'),
        'update' => route('kitchen.menu.categories.update', ':id'),
        'destroy' => route('kitchen.menu.categories.destroy', ':id'),
        'blank' => ['id' => null, 'name' => '', 'sort_order' => 0, 'is_active' => true],
     ]))"
     @open-category-form.window="open($event.detail)" @keydown.escape.window="close()">
    <div x-show="isOpen" x-transition x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center p-5">
        <div class="fixed inset-0 bg-gray-400/50 backdrop-blur-[32px] dark:bg-gray-900/80" @click="close()"></div>
        <form @submit.prevent="submit()" class="relative w-full max-w-md space-y-5 rounded-3xl bg-white p-6 lg:p-8 dark:bg-gray-900">
            <h4 class="text-lg font-medium text-gray-800 dark:text-white/90" x-text="form.id ? 'Edit Category' : 'New Category'"></h4>
            @include('facilities.partials.form-errors')
            <div>
                <label class="{{ $labelClass }}">Name *</label>
                <input type="text" x-model="form.name" required maxlength="100" class="{{ $inputClass }}" placeholder="e.g. Main Course">
            </div>
            <div>
                <label class="{{ $labelClass }}">Position on the menu</label>
                <input type="number" x-model.number="form.sort_order" min="0" class="{{ $inputClass }}">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Lower numbers come first.</p>
            </div>
            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-400">
                <input type="checkbox" x-model="form.is_active" class="h-4 w-4 rounded border-gray-300 text-primary"> Show on the menu
            </label>
            <div class="flex items-center justify-between gap-3">
                <button type="button" x-show="form.id" @click="destroy()" class="text-sm text-error-500 hover:underline">Delete</button>
                <div class="ml-auto flex gap-3">
                    <button type="button" @click="close()" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                    <button type="submit" :disabled="isLoading" class="rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white hover:bg-primary/90 disabled:opacity-50">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

@include('facilities.partials.scripts')
