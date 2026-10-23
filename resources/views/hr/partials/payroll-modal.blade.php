{{-- Adjust a draft payroll. open: $dispatch('open-payroll-form', {...}) --}}
@php
    $inputClass = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
@endphp

<div x-data="facilityForm(@js([
        'store' => '',
        'update' => route('hr.payroll.update', ':id'),
        'destroy' => '',
        'blank' => ['id' => null, 'name' => '', 'basic_salary' => 0, 'overtime_hours' => 0, 'allowances' => 0, 'bonuses' => 0, 'other_deductions' => 0, 'notes' => ''],
     ]))"
     @open-payroll-form.window="open($event.detail)" @keydown.escape.window="close()">
    <div x-show="isOpen" x-transition x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center p-5">
        <div class="fixed inset-0 bg-gray-400/50 backdrop-blur-sm dark:bg-gray-900/80" @click="close()"></div>
        <form @submit.prevent="submit()" class="relative w-full max-w-lg space-y-4 rounded-2xl bg-white p-6 dark:bg-gray-900">
            <h4 class="text-lg font-medium text-gray-800 dark:text-white/90" x-text="'Pay for ' + form.name"></h4>
            @include('facilities.partials.form-errors')
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="{{ $labelClass }}">Basic salary</label>
                    <input type="number" x-model.number="form.basic_salary" min="0" step="0.01" required class="{{ $inputClass }}">
                </div>
                <div>
                    <label class="{{ $labelClass }}">Overtime hours</label>
                    <input type="number" x-model.number="form.overtime_hours" min="0" step="0.5" required class="{{ $inputClass }}">
                </div>
                <div>
                    <label class="{{ $labelClass }}">Allowances</label>
                    <input type="number" x-model.number="form.allowances" min="0" step="0.01" required class="{{ $inputClass }}">
                </div>
                <div>
                    <label class="{{ $labelClass }}">Bonus</label>
                    <input type="number" x-model.number="form.bonuses" min="0" step="0.01" required class="{{ $inputClass }}">
                </div>
                <div class="col-span-2">
                    <label class="{{ $labelClass }}">Other deductions (advances, loans, meals…)</label>
                    <input type="number" x-model.number="form.other_deductions" min="0" step="0.01" required class="{{ $inputClass }}">
                </div>
                <div class="col-span-2">
                    <label class="{{ $labelClass }}">Note on payslip</label>
                    <input type="text" x-model="form.notes" maxlength="500" class="{{ $inputClass }}">
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400">Overtime pay and statutory deductions are worked out when you save.</p>
            <div class="flex justify-end gap-3">
                <button type="button" @click="close()" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                <button type="submit" :disabled="isLoading" class="rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white hover:bg-primary/90 disabled:opacity-50">Save</button>
            </div>
        </form>
    </div>
</div>

@include('facilities.partials.scripts')
