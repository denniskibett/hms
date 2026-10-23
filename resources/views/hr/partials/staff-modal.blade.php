{{--
    Add / edit employee. Needs $departments and $roles.
    open: $dispatch('open-staff-form') or $dispatch('open-staff-form', @js($staff->formData()))
--}}
@php
    $inputClass = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
    $legendClass = 'mb-3 text-sm font-medium text-gray-800 dark:text-white/90';
@endphp

<div x-data="facilityForm(@js([
        'store' => route('hr.staff.store'),
        'update' => route('hr.staff.update', ':id'),
        'destroy' => '',
        'blank' => [
            'id' => null, 'name' => '', 'phone' => '', 'email' => '', 'role' => '', 'password' => '',
            'department_id' => '', 'job_title' => '', 'id_number' => '', 'kra_pin' => '', 'nssf_number' => '', 'shif_number' => '',
            'salary' => '', 'hire_date' => today()->toDateString(), 'employment_status' => 'probation', 'contract_period' => 3,
            'bank_name' => '', 'bank_branch' => '', 'account_number' => '',
            'emergency_contact' => ['name' => '', 'phone' => '', 'relationship' => ''], 'notes' => '',
        ],
     ]))"
     @open-staff-form.window="open($event.detail); form.password = ''" @keydown.escape.window="close()">
    <div x-show="isOpen" x-transition x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center overflow-y-auto p-5">
        <div class="fixed inset-0 bg-gray-400/50 backdrop-blur-[32px] dark:bg-gray-900/80" @click="close()"></div>

        <div class="relative max-h-[92vh] w-full max-w-3xl overflow-y-auto rounded-3xl bg-white p-6 lg:p-10 dark:bg-gray-900">
            <button type="button" @click="close()"
                    class="absolute right-3 top-3 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-200 text-gray-500 hover:bg-gray-300 sm:right-6 sm:top-6 sm:h-11 sm:w-11 dark:bg-gray-800 dark:text-gray-400">
                <i class="fas fa-times"></i>
            </button>
            <h4 class="mb-6 text-lg font-medium text-gray-800 dark:text-white/90" x-text="form.id ? 'Edit Employee' : 'New Employee'"></h4>

            <form @submit.prevent="submit()" class="space-y-6">
                @include('facilities.partials.form-errors')

                <fieldset>
                    <legend class="{{ $legendClass }}">Person</legend>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                        <div class="sm:col-span-2">
                            <label class="{{ $labelClass }}">Full name *</label>
                            <input type="text" x-model="form.name" required maxlength="255" class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Phone *</label>
                            <input type="tel" x-model="form.phone" required maxlength="30" class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">National ID / Passport</label>
                            <input type="text" x-model="form.id_number" maxlength="30" class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">KRA PIN</label>
                            <input type="text" x-model="form.kra_pin" maxlength="11" class="{{ $inputClass }} uppercase" placeholder="A123456789B">
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">NSSF no.</label>
                            <input type="text" x-model="form.nssf_number" maxlength="30" class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">SHA (SHIF) no.</label>
                            <input type="text" x-model="form.shif_number" maxlength="30" class="{{ $inputClass }}">
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend class="{{ $legendClass }}">Job</legend>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                        <div>
                            <label class="{{ $labelClass }}">Job title</label>
                            <input type="text" x-model="form.job_title" maxlength="100" class="{{ $inputClass }}" placeholder="e.g. Head Chef">
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Department</label>
                            <select x-model="form.department_id" class="{{ $inputClass }}">
                                <option value="">—</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Started *</label>
                            <input type="date" x-model="form.hire_date" required class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Terms *</label>
                            <select x-model="form.employment_status" class="{{ $inputClass }}">
                                @foreach (array_diff_key(\App\Models\StaffProfile::EMPLOYMENT_STATUSES, ['terminated' => 1]) as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div x-show="['contract', 'internship'].includes(form.employment_status)">
                            <label class="{{ $labelClass }}">Length (months)</label>
                            <input type="number" x-model.number="form.contract_period" min="1" max="120" class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Basic salary / month ({{ SystemHelper::currencySymbol() }}) *</label>
                            <input type="number" x-model.number="form.salary" required min="0" step="0.01" class="{{ $inputClass }}">
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend class="{{ $legendClass }}">System access</legend>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                        <div>
                            <label class="{{ $labelClass }}">Role</label>
                            <select x-model="form.role" class="{{ $inputClass }}">
                                <option value="">No system access</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role }}">{{ Str::headline($role) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Email <span class="font-normal text-gray-500" x-show="form.role">(their login)</span></label>
                            <input type="email" x-model="form.email" maxlength="255" class="{{ $inputClass }}">
                        </div>
                        <div x-show="form.role">
                            <label class="{{ $labelClass }}" x-text="form.id ? 'New password' : 'Password'"></label>
                            <input type="password" x-model="form.password" minlength="8" autocomplete="new-password" class="{{ $inputClass }}"
                                   :placeholder="form.id ? 'Leave blank to keep' : 'At least 8 characters'">
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend class="{{ $legendClass }}">Bank (for salary)</legend>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                        <input type="text" x-model="form.bank_name" maxlength="100" class="{{ $inputClass }}" placeholder="Bank">
                        <input type="text" x-model="form.bank_branch" maxlength="100" class="{{ $inputClass }}" placeholder="Branch">
                        <input type="text" x-model="form.account_number" maxlength="50" class="{{ $inputClass }}" placeholder="Account number">
                    </div>
                </fieldset>

                <fieldset>
                    <legend class="{{ $legendClass }}">Emergency contact</legend>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                        <input type="text" x-model="form.emergency_contact.name" maxlength="255" class="{{ $inputClass }}" placeholder="Name">
                        <input type="tel" x-model="form.emergency_contact.phone" maxlength="30" class="{{ $inputClass }}" placeholder="Phone">
                        <input type="text" x-model="form.emergency_contact.relationship" maxlength="50" class="{{ $inputClass }}" placeholder="Relationship">
                    </div>
                </fieldset>

                <div>
                    <label class="{{ $labelClass }}">Notes</label>
                    <textarea x-model="form.notes" rows="2" maxlength="2000" class="{{ str_replace('h-11', '', $inputClass) }}"></textarea>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" @click="close()" class="rounded-lg border border-gray-300 px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                    <button type="submit" :disabled="isLoading" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-3 text-sm font-medium text-white hover:bg-primary/90 disabled:opacity-50">
                        <i class="fas fa-spinner fa-spin" x-show="isLoading" x-cloak></i>
                        <span x-text="form.id ? 'Save Changes' : 'Add Employee'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('facilities.partials.scripts')
