{{--
    Add / edit account. Needs $roles (role names).
    open: $dispatch('open-user-form') | $dispatch('open-user-form', {...user fields, roles: [...]})
--}}
@php
    $inputClass = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
    $labelClass = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
    $roleHelp = [
        'admin' => 'Everything, including users and system settings',
        'manager' => 'Runs the hotel; approves orders, leave, payroll',
        'receptionist' => 'Bookings, guests, folios, events',
        'housekeeping' => 'Rooms, cleaning tasks, issuing stock',
        'kitchen' => 'Kitchen board, menu, kitchen stock',
        'finance' => 'Invoices, payments, reports, paying payroll',
        'hr' => 'Staff, rota, leave, payroll',
        'procurement' => 'Stock, suppliers, purchase orders',
        'guest' => 'A hotel guest; sees their own stays',
    ];
@endphp

<div x-data="facilityForm(@js([
        'store' => route('users.store'),
        'update' => route('users.update', ':id'),
        'destroy' => route('users.destroy', ':id'),
        'blank' => ['id' => null, 'name' => '', 'email' => '', 'phone' => '', 'roles' => [], 'status' => 'active', 'password' => ''],
     ]))"
     @open-user-form.window="open($event.detail); form.password = ''" @keydown.escape.window="close()">
    <div x-show="isOpen" x-transition x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center overflow-y-auto p-5">
        <div class="fixed inset-0 bg-gray-400/50 backdrop-blur-[32px] dark:bg-gray-900/80" @click="close()"></div>
        <form @submit.prevent="submit()" class="relative max-h-[92vh] w-full max-w-2xl space-y-5 overflow-y-auto rounded-3xl bg-white p-6 lg:p-8 dark:bg-gray-900">
            <h4 class="text-lg font-medium text-gray-800 dark:text-white/90" x-text="form.id ? 'Edit Account' : 'New Account'"></h4>
            @include('facilities.partials.form-errors')

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="{{ $labelClass }}">Full name *</label>
                    <input type="text" x-model="form.name" required maxlength="255" class="{{ $inputClass }}">
                </div>
                <div>
                    <label class="{{ $labelClass }}">Email (used to sign in)</label>
                    <input type="email" x-model="form.email" maxlength="255" class="{{ $inputClass }}">
                </div>
                <div>
                    <label class="{{ $labelClass }}">Phone</label>
                    <input type="tel" x-model="form.phone" maxlength="30" class="{{ $inputClass }}">
                </div>
                <div>
                    <label class="{{ $labelClass }}">Status *</label>
                    <select x-model="form.status" class="{{ $inputClass }}">
                        @foreach (\App\Http\Controllers\UserController::STATUSES as $value => $label)
                            <option value="{{ $value }}">{{ $label }}{{ $value === 'active' ? '' : ' (cannot sign in)' }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $labelClass }}" x-text="form.id ? 'New password' : 'Password *'"></label>
                    <input type="password" x-model="form.password" minlength="8" autocomplete="new-password" :required="!form.id"
                           :placeholder="form.id ? 'Leave blank to keep the current one' : 'At least 8 characters'" class="{{ $inputClass }}">
                </div>
            </div>

            <fieldset>
                <legend class="mb-2 text-sm font-medium text-gray-800 dark:text-white/90">Roles *</legend>
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    @foreach ($roles as $role)
                        <label class="flex cursor-pointer items-start gap-3 rounded-lg border p-3"
                               :class="form.roles.includes(@js($role)) ? 'border-primary bg-primary/5' : 'border-gray-200 dark:border-gray-700'">
                            <input type="checkbox" value="{{ $role }}" x-model="form.roles" class="mt-0.5 h-4 w-4 rounded border-gray-300 text-primary">
                            <span>
                                <span class="block text-sm font-medium text-gray-800 dark:text-white/90">{{ Str::headline($role) }}</span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $roleHelp[$role] ?? '' }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Job details, pay and leave for staff are kept in HR.</p>
            </fieldset>

            <div class="flex items-center justify-between gap-3">
                <button type="button" x-show="form.id" @click="destroy()" class="text-sm text-error-500 hover:underline">Delete account</button>
                <div class="ml-auto flex gap-3">
                    <button type="button" @click="close()" class="rounded-lg border border-gray-300 px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                    <button type="submit" :disabled="isLoading" class="rounded-lg bg-primary px-4 py-3 text-sm font-medium text-white hover:bg-primary/90 disabled:opacity-50" x-text="form.id ? 'Save Changes' : 'Create Account'"></button>
                </div>
            </div>
        </form>
    </div>
</div>

@include('facilities.partials.scripts')
