{{--
    Add / edit stock item (managers, procurement).
    open: $dispatch('open-item-form') | $dispatch('open-item-form', @js($item->formData()))
--}}
@php
    $inputClass = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
@endphp

<div x-data="facilityForm(@js([
        'store' => route('inventory.store'),
        'update' => route('inventory.update', ':id'),
        'destroy' => '',
        'blank' => ['id' => null, 'name' => '', 'sku' => '', 'barcode' => '', 'item_type' => 'cleaning', 'unit_of_measure' => 'pcs', 'reorder_level' => 0,
                    'location' => '', 'primary_supplier_id' => '', 'description' => '', 'status' => true, 'opening_quantity' => '', 'opening_unit_cost' => ''],
     ]))"
     @open-item-form.window="open($event.detail)" @keydown.escape.window="close()">
    <div x-show="isOpen" x-transition x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center overflow-y-auto p-5">
        <div class="fixed inset-0 bg-gray-400/50 backdrop-blur-[32px] dark:bg-gray-900/80" @click="close()"></div>
        <form @submit.prevent="submit()" class="relative max-h-[92vh] w-full max-w-2xl space-y-5 overflow-y-auto rounded-3xl bg-white p-6 lg:p-8 dark:bg-gray-900">
            <h4 class="text-lg font-medium text-gray-800 dark:text-white/90" x-text="form.id ? 'Edit Item' : 'New Stock Item'"></h4>
            @include('facilities.partials.form-errors')

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="{{ $labelClass }}">Name *</label>
                    <input type="text" x-model="form.name" required maxlength="255" class="{{ $inputClass }}" placeholder="e.g. Toilet paper, Cooking oil 20L">
                </div>
                <div>
                    <label class="{{ $labelClass }}">Kind *</label>
                    <select x-model="form.item_type" class="{{ $inputClass }}">
                        @foreach (\App\Models\InventoryItem::TYPES as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $labelClass }}">Counted in *</label>
                    <input type="text" x-model="form.unit_of_measure" required maxlength="20" list="inventory-units" class="{{ $inputClass }}">
                    <datalist id="inventory-units">
                        @foreach (\App\Models\InventoryItem::UNITS as $unit)
                            <option value="{{ $unit }}">
                        @endforeach
                    </datalist>
                </div>
                <div>
                    <label class="{{ $labelClass }}">Reorder when at or below *</label>
                    <input type="number" x-model.number="form.reorder_level" required min="0" step="0.01" class="{{ $inputClass }}">
                </div>
                <div>
                    <label class="{{ $labelClass }}">Kept in</label>
                    <input type="text" x-model="form.location" maxlength="100" class="{{ $inputClass }}" placeholder="e.g. Main store, Kitchen store">
                </div>
                <div>
                    <label class="{{ $labelClass }}">Usual supplier</label>
                    <select x-model="form.primary_supplier_id" class="{{ $inputClass }}">
                        <option value="">—</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $labelClass }}">Code (SKU)</label>
                    <input type="text" x-model="form.sku" maxlength="50" class="{{ $inputClass }}" placeholder="Made for you if blank">
                </div>
                <div>
                    <label class="{{ $labelClass }}">Barcode</label>
                    <input type="text" x-model="form.barcode" maxlength="50" class="{{ $inputClass }}">
                </div>
            </div>

            <div x-show="!form.id" class="grid grid-cols-2 gap-5 rounded-lg bg-gray-50 p-4 dark:bg-white/[0.03]">
                <div>
                    <label class="{{ $labelClass }}">Opening stock</label>
                    <input type="number" x-model.number="form.opening_quantity" min="0" step="0.01" class="{{ $inputClass }}" placeholder="0">
                </div>
                <div>
                    <label class="{{ $labelClass }}">Cost per unit ({{ SystemHelper::currencySymbol() }})</label>
                    <input type="number" x-model.number="form.opening_unit_cost" min="0" step="0.01" class="{{ $inputClass }}" :required="form.opening_quantity > 0">
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-400">
                <input type="checkbox" x-model="form.status" class="h-4 w-4 rounded border-gray-300 text-primary"> In use (untick to hide items you no longer stock)
            </label>

            <div class="flex justify-end gap-3">
                <button type="button" @click="close()" class="rounded-lg border border-gray-300 px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                <button type="submit" :disabled="isLoading" class="rounded-lg bg-primary px-4 py-3 text-sm font-medium text-white hover:bg-primary/90 disabled:opacity-50" x-text="form.id ? 'Save Changes' : 'Add Item'"></button>
            </div>
        </form>
    </div>
</div>

@include('facilities.partials.scripts')
