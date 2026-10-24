{{--
    New / edit purchase order. Needs $suppliers, $stockItems, $supplierPrices.
    open: $dispatch('open-order-form') | $dispatch('open-order-form', @js($order->formData()))
    Prices start from the supplier's last price, else the item's average cost.
--}}
@php
    $currency = SystemHelper::currencySymbol();
    $inputClass = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
    $cellInput = 'h-9 w-full rounded-lg border border-gray-300 bg-transparent px-2 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
    $items = $stockItems->map(fn ($i) => ['id' => $i->id, 'name' => $i->name, 'unit' => $i->unit_of_measure, 'cost' => (float) $i->unit_cost, 'stock' => (float) $i->quantity, 'supplier_id' => $i->primary_supplier_id])->values();
@endphp

<div x-data="facilityForm(@js([
        'store' => route('purchase-orders.store'),
        'update' => route('purchase-orders.update', ':id'),
        'destroy' => '',
        'blank' => ['id' => null, 'supplier_id' => '', 'delivery_date' => '', 'notes' => '', 'items' => []],
     ]))"
     @open-order-form.window="open($event.detail)" @keydown.escape.window="close()">
    <div x-show="isOpen" x-transition x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center overflow-y-auto p-5">
        <div class="fixed inset-0 bg-gray-400/50 backdrop-blur-[32px] dark:bg-gray-900/80" @click="close()"></div>
        <form x-data="{
                stock: @js($items),
                prices: @js($supplierPrices),
                pick: '',
                item(id) { return this.stock.find(i => i.id === Number(id)); },
                price(id) { return Number(this.prices[form.supplier_id]?.[id] ?? this.item(id)?.cost ?? 0); },
                add() {
                    if (!this.pick || form.items.some(l => l.inventory_item_id === Number(this.pick))) return;
                    form.items.push({ inventory_item_id: Number(this.pick), quantity: 1, unit_price: this.price(this.pick) });
                    this.pick = '';
                },
                get total() { return form.items.reduce((s, l) => s + (Number(l.quantity) || 0) * (Number(l.unit_price) || 0), 0); },
                money(v) { return @js($currency) + ' ' + Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); },
              }"
              @submit.prevent="submit()" class="relative max-h-[92vh] w-full max-w-3xl space-y-5 overflow-y-auto rounded-3xl bg-white p-6 lg:p-8 dark:bg-gray-900">
            <h4 class="text-lg font-medium text-gray-800 dark:text-white/90" x-text="form.id ? 'Edit Purchase Order' : 'New Purchase Order'"></h4>
            @include('facilities.partials.form-errors')

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="{{ $labelClass }}">Supplier *</label>
                    <select x-model.number="form.supplier_id" required class="{{ $inputClass }}">
                        <option value="">Choose…</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $labelClass }}">Deliver by</label>
                    <input type="date" x-model="form.delivery_date" class="{{ $inputClass }}">
                </div>
            </div>

            <fieldset>
                <legend class="mb-2 text-sm font-medium text-gray-800 dark:text-white/90">Items</legend>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-500 dark:text-gray-400">
                            <th class="pb-1 pr-2 font-medium">Item</th>
                            <th class="w-28 pb-1 pr-2 font-medium">Quantity</th>
                            <th class="w-32 pb-1 pr-2 font-medium">Price / unit</th>
                            <th class="w-28 pb-1 text-right font-medium">Total</th>
                            <th class="w-8"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(line, i) in form.items" :key="line.inventory_item_id">
                            <tr>
                                <td class="py-1 pr-2">
                                    <p class="text-gray-800 dark:text-white/90" x-text="item(line.inventory_item_id)?.name"></p>
                                    <p class="text-xs text-gray-500" x-text="`${item(line.inventory_item_id)?.stock} ${item(line.inventory_item_id)?.unit} in stock`"></p>
                                </td>
                                <td class="py-1 pr-2"><input type="number" x-model.number="line.quantity" min="0.01" step="0.01" required class="{{ $cellInput }}"></td>
                                <td class="py-1 pr-2"><input type="number" x-model.number="line.unit_price" min="0" step="0.01" required class="{{ $cellInput }}"></td>
                                <td class="whitespace-nowrap py-1 text-right text-gray-800 dark:text-white/90" x-text="money(line.quantity * line.unit_price)"></td>
                                <td class="py-1 text-right"><button type="button" @click="form.items.splice(i, 1)" class="text-gray-400 hover:text-error-500"><i class="fas fa-times"></i></button></td>
                            </tr>
                        </template>
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-gray-100 dark:border-gray-800">
                            <td colspan="3" class="pt-3 text-right font-medium text-gray-700 dark:text-gray-300">Total</td>
                            <td class="whitespace-nowrap pt-3 text-right font-semibold text-gray-800 dark:text-white/90" x-text="money(total)"></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
                <div class="mt-3 flex gap-2">
                    <select x-model="pick" class="{{ $inputClass }}">
                        <option value="">Add an item…</option>
                        <template x-for="s in stock" :key="s.id">
                            <option :value="s.id" x-text="s.name + (s.supplier_id && s.supplier_id === form.supplier_id ? ' ★' : '')"></option>
                        </template>
                    </select>
                    <button type="button" @click="add()" class="shrink-0 rounded-lg border border-gray-300 px-4 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Add</button>
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">★ usually bought from this supplier. Items must be in the store first (Inventory → New Item).</p>
            </fieldset>

            <div>
                <label class="{{ $labelClass }}">Notes for the supplier / approver</label>
                <textarea x-model="form.notes" rows="2" maxlength="2000" class="{{ str_replace('h-11', '', $inputClass) }}"></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" @click="close()" class="rounded-lg border border-gray-300 px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                <button type="submit" :disabled="isLoading || !form.items.length" class="rounded-lg bg-primary px-4 py-3 text-sm font-medium text-white hover:bg-primary/90 disabled:opacity-50" x-text="form.id ? 'Save Changes' : 'Save Draft'"></button>
            </div>
        </form>
    </div>
</div>

@include('facilities.partials.scripts')
