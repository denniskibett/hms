{{--
    Record an expense or a supplier payment. Needs $suppliers, $departments, $openOrders.
    open: $dispatch('open-expense-form')
          $dispatch('open-expense-form', { category: 'supplier_payment', supplier_id: 3, purchase_order_id: 9, amount: 1200, description: '…' })
--}}
@php
    $inputClass = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
@endphp

<div x-data="facilityForm(@js([
        'store' => route('expenses.store'),
        'update' => '',
        'destroy' => '',
        'blank' => ['id' => null, 'spent_on' => today()->toDateString(), 'category' => 'utilities', 'description' => '', 'amount' => '', 'method' => 'mobile_money',
                    'reference' => '', 'supplier_id' => '', 'purchase_order_id' => '', 'department_id' => ''],
     ]))"
     @open-expense-form.window="open($event.detail)" @keydown.escape.window="close()">
    <div x-show="isOpen" x-transition x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center overflow-y-auto p-5">
        <div class="fixed inset-0 bg-gray-400/50 backdrop-blur-[32px] dark:bg-gray-900/80" @click="close()"></div>
        <form x-data="{ orders: @js($openOrders), get supplierOrders() { return this.orders.filter(o => String(o.supplier_id) === String(form.supplier_id)); } }"
              @submit.prevent="submit()" class="relative max-h-[92vh] w-full max-w-2xl space-y-5 overflow-y-auto rounded-3xl bg-white p-6 lg:p-8 dark:bg-gray-900">
            <h4 class="text-lg font-medium text-gray-800 dark:text-white/90" x-text="form.category === 'supplier_payment' ? 'Pay a Supplier' : 'Record an Expense'"></h4>
            @include('facilities.partials.form-errors')

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="{{ $labelClass }}">Kind *</label>
                    <select x-model="form.category" class="{{ $inputClass }}">
                        @foreach (\App\Models\Expense::CATEGORIES as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $labelClass }}">Paid on *</label>
                    <input type="date" x-model="form.spent_on" required max="{{ today()->toDateString() }}" class="{{ $inputClass }}">
                </div>

                <template x-if="form.category === 'supplier_payment' || form.supplier_id">
                    <div>
                        <label class="{{ $labelClass }}">Supplier <span x-show="form.category === 'supplier_payment'">*</span></label>
                        <select x-model="form.supplier_id" @change="form.purchase_order_id = ''" :required="form.category === 'supplier_payment'" class="{{ $inputClass }}">
                            <option value="">—</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </template>
                <template x-if="form.category === 'supplier_payment' && form.supplier_id">
                    <div>
                        <label class="{{ $labelClass }}">For purchase order</label>
                        <select x-model="form.purchase_order_id"
                                @change="const o = orders.find(o => String(o.id) === String(form.purchase_order_id)); if (o) { form.amount = o.owed; form.description ||= 'Payment for ' + o.label; }"
                                class="{{ $inputClass }}">
                            <option value="">General payment / advance</option>
                            <template x-for="o in supplierOrders" :key="o.id">
                                <option :value="o.id" :selected="String(o.id) === String(form.purchase_order_id)" x-text="`${o.label} (owed ${Number(o.owed).toLocaleString()})`"></option>
                            </template>
                        </select>
                    </div>
                </template>

                <div class="sm:col-span-2">
                    <label class="{{ $labelClass }}">What for *</label>
                    <input type="text" x-model="form.description" required maxlength="255" class="{{ $inputClass }}" placeholder="e.g. KPLC tokens for October, Generator diesel 40L">
                </div>
                <div>
                    <label class="{{ $labelClass }}">Amount ({{ SystemHelper::currencySymbol() }}) *</label>
                    <input type="number" x-model.number="form.amount" required min="0.01" step="0.01" class="{{ $inputClass }}">
                </div>
                <div>
                    <label class="{{ $labelClass }}">Paid by *</label>
                    <select x-model="form.method" class="{{ $inputClass }}">
                        @foreach (\App\Models\Payment::METHODS as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div x-show="!['cash', 'credit_card'].includes(form.method)">
                    <label class="{{ $labelClass }}">Reference *</label>
                    <input type="text" x-model="form.reference" maxlength="100" :required="!['cash', 'credit_card'].includes(form.method)" class="{{ $inputClass }} uppercase" placeholder="M-Pesa code / cheque no.">
                </div>
                <div>
                    <label class="{{ $labelClass }}">Department</label>
                    <select x-model="form.department_id" class="{{ $inputClass }}">
                        <option value="">Whole hotel</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" @click="close()" class="rounded-lg border border-gray-300 px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                <button type="submit" :disabled="isLoading" class="rounded-lg bg-primary px-4 py-3 text-sm font-medium text-white hover:bg-primary/90 disabled:opacity-50">Save</button>
            </div>
        </form>
    </div>
</div>

@include('facilities.partials.scripts')
