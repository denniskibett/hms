{{--
    Record an expense or a supplier payment. Needs $suppliers, $departments, $openOrders.
    open: $dispatch('open-expense-form')
          $dispatch('open-expense-form', { category: 'supplier_payment', supplier_id: 3, purchase_order_id: 9, amount: 1200, description: '…' })
--}}
@php
    $inputClass = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
    $canPayNow = \App\Http\Controllers\ExpenseController::canPay(auth()->user());
@endphp

<div x-data="facilityForm(@js([
        'store' => route('expenses.store'),
        'update' => '',
        'destroy' => '',
        'blank' => ['id' => null, 'spent_on' => today()->toDateString(), 'category' => 'utilities', 'description' => '', 'amount' => '', 'method' => 'mobile_money',
                    'reference' => '', 'supplier_id' => '', 'purchase_order_id' => '', 'department_id' => '', 'paid_now' => $canPayNow],
     ]))"
     @open-expense-form.window="open($event.detail)" @keydown.escape.window="close()">
    <div x-show="isOpen" x-transition.opacity x-cloak class="slideover">
        <div class="fixed inset-0 modal-overlay" @click="close()"></div>
        <form x-data="{ orders: @js($openOrders), get supplierOrders() { return this.orders.filter(o => String(o.supplier_id) === String(form.supplier_id)); } }"
              @submit.prevent="submit()" class="slideover-panel space-y-5">
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
                    <label class="{{ $labelClass }}">Date of the bill *</label>
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
                @if ($canPayNow)
                    <label class="flex items-center gap-2 self-end pb-3 text-sm text-gray-700">
                        <input type="checkbox" x-model="form.paid_now" class="h-4 w-4 rounded-sm border-gray-300"> Already paid
                    </label>
                @endif
                <div x-show="form.paid_now">
                    <label class="{{ $labelClass }}">Paid by *</label>
                    <select x-model="form.method" class="{{ $inputClass }}">
                        @foreach (\App\Models\Payment::METHODS as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div x-show="form.paid_now && !['cash', 'credit_card'].includes(form.method)">
                    <label class="{{ $labelClass }}">Reference *</label>
                    <input type="text" x-model="form.reference" maxlength="100" :required="form.paid_now && !['cash', 'credit_card'].includes(form.method)" class="{{ $inputClass }} uppercase" placeholder="M-Pesa code / cheque no.">
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
                <div class="sm:col-span-2">
                    <label class="{{ $labelClass }}">Receipt or bill (photo or PDF)</label>
                    <input type="file" data-field="receipt" accept="image/*,application/pdf" class="block w-full text-sm text-gray-700 file:mr-3 file:rounded-lg file:border-0 file:bg-gray-100 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-200">
                </div>
            </div>

            <p x-show="!form.paid_now" class="rounded-lg bg-warning-50 px-4 py-3 text-sm text-warning-700">This goes to a manager to approve; finance pays it after.</p>

            <div class="flex justify-end gap-3">
                <button type="button" @click="close()" class="rounded-lg border border-gray-300 px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                <button type="submit" :disabled="isLoading" class="rounded-lg bg-primary px-4 py-3 text-sm font-medium text-white hover:bg-primary/90 disabled:opacity-50" x-text="form.paid_now ? 'Save' : 'Send for approval'"></button>
            </div>
        </form>
    </div>
</div>

@include('facilities.partials.scripts')
