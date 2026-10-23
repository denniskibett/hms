{{--
    Add / edit dish with its portions and prices. Needs $categories.
    open: $dispatch('open-dish-form', record?)
--}}
@php
    $inputClass = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
    $cellInput = 'h-9 w-full rounded-lg border border-gray-300 bg-transparent px-2 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
    $newVariant = ['name' => '', 'price' => '', 'preparation_time' => null, 'is_active' => true];
@endphp

<div x-data="facilityForm(@js([
        'store' => route('kitchen.menu.items.store'),
        'update' => route('kitchen.menu.items.update', ':id'),
        'destroy' => route('kitchen.menu.items.destroy', ':id'),
        'blank' => ['id' => null, 'name' => '', 'category_id' => $categories->first()?->id, 'description' => '', 'is_active' => true,
                    'variants' => [['name' => 'Regular', 'price' => '', 'preparation_time' => 15, 'is_active' => true]]],
     ]))"
     @open-dish-form.window="open($event.detail)" @keydown.escape.window="close()">
    <div x-show="isOpen" x-transition x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center overflow-y-auto p-5">
        <div class="fixed inset-0 bg-gray-400/50 backdrop-blur-[32px] dark:bg-gray-900/80" @click="close()"></div>
        <form @submit.prevent="submit()" class="relative max-h-[90vh] w-full max-w-2xl space-y-5 overflow-y-auto rounded-3xl bg-white p-6 lg:p-8 dark:bg-gray-900">
            <h4 class="text-lg font-medium text-gray-800 dark:text-white/90" x-text="form.id ? 'Edit Dish' : 'New Dish'"></h4>
            @include('facilities.partials.form-errors')

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="{{ $labelClass }}">Name *</label>
                    <input type="text" x-model="form.name" required maxlength="255" class="{{ $inputClass }}" placeholder="e.g. Beef Stew">
                </div>
                <div>
                    <label class="{{ $labelClass }}">Category *</label>
                    <select x-model.number="form.category_id" required class="{{ $inputClass }}">
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="{{ $labelClass }}">Description</label>
                    <input type="text" x-model="form.description" maxlength="1000" class="{{ $inputClass }}" placeholder="e.g. Served with rice or chapati">
                </div>
            </div>

            <fieldset>
                <div class="mb-2 flex items-center justify-between">
                    <legend class="text-sm font-medium text-gray-800 dark:text-white/90">Portions &amp; prices</legend>
                    <button type="button" @click="form.variants.push(@js($newVariant))" class="text-sm text-primary hover:underline">+ Add portion</button>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-500 dark:text-gray-400">
                            <th class="pb-1 pr-2 font-medium">Portion</th>
                            <th class="w-32 pb-1 pr-2 font-medium">Price *</th>
                            <th class="w-24 pb-1 pr-2 font-medium">Prep (min)</th>
                            <th class="w-16 pb-1 pr-2 text-center font-medium">On</th>
                            <th class="w-8"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(v, i) in form.variants" :key="i">
                            <tr>
                                <td class="py-1 pr-2"><input type="text" x-model="v.name" required maxlength="50" class="{{ $cellInput }}" placeholder="Regular / Half / 500ml"></td>
                                <td class="py-1 pr-2"><input type="number" x-model.number="v.price" required min="0" step="0.01" class="{{ $cellInput }}"></td>
                                <td class="py-1 pr-2"><input type="number" x-model.number="v.preparation_time" min="0" class="{{ $cellInput }}"></td>
                                <td class="py-1 pr-2 text-center"><input type="checkbox" x-model="v.is_active" class="h-4 w-4 rounded border-gray-300 text-primary"></td>
                                <td class="py-1 text-right">
                                    <button type="button" x-show="form.variants.length > 1" @click="form.variants.splice(i, 1)" class="text-gray-400 hover:text-error-500"><i class="fas fa-times"></i></button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </fieldset>

            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-400">
                <input type="checkbox" x-model="form.is_active" class="h-4 w-4 rounded border-gray-300 text-primary"> Available to order
            </label>

            <div class="flex items-center justify-between gap-3">
                <button type="button" x-show="form.id" @click="destroy()" class="text-sm text-error-500 hover:underline">Delete dish</button>
                <div class="ml-auto flex gap-3">
                    <button type="button" @click="close()" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                    <button type="submit" :disabled="isLoading" class="rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white hover:bg-primary/90 disabled:opacity-50">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

@include('facilities.partials.scripts')
