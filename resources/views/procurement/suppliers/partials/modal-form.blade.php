{{-- Add / edit supplier. open: $dispatch('open-supplier-form') | $dispatch('open-supplier-form', @js($supplier->formData())) --}}
@php
    $inputClass = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
@endphp

<div x-data="facilityForm(@js([
        'store' => route('suppliers.store'),
        'update' => route('suppliers.update', ':id'),
        'destroy' => '',
        'blank' => ['id' => null, 'name' => '', 'contact_person' => '', 'phone' => '', 'email' => '', 'kra_pin' => '', 'address' => '', 'payment_terms' => '', 'notes' => '', 'status' => 'active'],
     ]))"
     @open-supplier-form.window="open($event.detail)" @keydown.escape.window="close()">
    <div x-show="isOpen" x-transition x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center overflow-y-auto p-5">
        <div class="fixed inset-0 bg-gray-400/50 backdrop-blur-[32px] dark:bg-gray-900/80" @click="close()"></div>
        <form @submit.prevent="submit()" class="relative max-h-[92vh] w-full max-w-2xl space-y-5 overflow-y-auto rounded-3xl bg-white p-6 lg:p-8 dark:bg-gray-900">
            <h4 class="text-lg font-medium text-gray-800 dark:text-white/90" x-text="form.id ? 'Edit Supplier' : 'New Supplier'"></h4>
            @include('facilities.partials.form-errors')
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="{{ $labelClass }}">Business name *</label>
                    <input type="text" x-model="form.name" required maxlength="255" class="{{ $inputClass }}">
                </div>
                <div>
                    <label class="{{ $labelClass }}">Contact person</label>
                    <input type="text" x-model="form.contact_person" maxlength="255" class="{{ $inputClass }}">
                </div>
                <div>
                    <label class="{{ $labelClass }}">Phone</label>
                    <input type="tel" x-model="form.phone" maxlength="30" class="{{ $inputClass }}">
                </div>
                <div>
                    <label class="{{ $labelClass }}">Email</label>
                    <input type="email" x-model="form.email" maxlength="255" class="{{ $inputClass }}">
                </div>
                <div>
                    <label class="{{ $labelClass }}">KRA PIN</label>
                    <input type="text" x-model="form.kra_pin" maxlength="11" class="{{ $inputClass }} uppercase" placeholder="P051234567X">
                </div>
                <div>
                    <label class="{{ $labelClass }}">Payment terms</label>
                    <input type="text" x-model="form.payment_terms" maxlength="100" list="payment-terms" class="{{ $inputClass }}" placeholder="e.g. 30 days">
                    <datalist id="payment-terms">
                        @foreach (['Cash on delivery', 'M-Pesa on delivery', '7 days', '14 days', '30 days', '60 days'] as $term)
                            <option value="{{ $term }}">
                        @endforeach
                    </datalist>
                </div>
                <div>
                    <label class="{{ $labelClass }}">Status</label>
                    <select x-model="form.status" class="{{ $inputClass }}">
                        <option value="active">Active</option>
                        <option value="inactive">Not used any more</option>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="{{ $labelClass }}">Address</label>
                    <input type="text" x-model="form.address" maxlength="500" class="{{ $inputClass }}">
                </div>
                <div class="sm:col-span-2">
                    <label class="{{ $labelClass }}">Notes</label>
                    <textarea x-model="form.notes" rows="2" maxlength="2000" class="{{ str_replace('h-11', '', $inputClass) }}" placeholder="e.g. Delivers Tuesdays and Fridays; bank details for payment"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" @click="close()" class="rounded-lg border border-gray-300 px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                <button type="submit" :disabled="isLoading" class="rounded-lg bg-primary px-4 py-3 text-sm font-medium text-white hover:bg-primary/90 disabled:opacity-50" x-text="form.id ? 'Save Changes' : 'Add Supplier'"></button>
            </div>
        </form>
    </div>
</div>

@include('facilities.partials.scripts')
