@extends('layouts.app')

@php
    $money = fn ($value) => SystemHelper::currencySymbol() . ' ' . number_format((float) $value, 2);
    $user = $staff->user;
    $role = $user->roles->first(fn ($r) => $r->name !== 'guest');
    $row = fn ($label, $value) => $value ? '<div class="flex justify-between gap-3"><dt class="text-gray-500 dark:text-gray-400">' . e($label) . '</dt><dd class="text-right text-gray-800 dark:text-white/90">' . e($value) . '</dd></div>' : '';
    $leaveBalance = $staff->annualLeaveBalance();
@endphp

@section('content')
<div x-data="{ pageName: @js($user->name) }">
    @include('partials.breadcrumb')
</div>

<div class="space-y-6" x-data="{ leaveOpen: @js($errors->has('end_date') && old('reason') !== null), terminateOpen: false }">
    <!-- Header -->
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <a href="{{ route('hr.staff.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                <i class="fas fa-arrow-left"></i> Back to Staff
            </a>
            <h2 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $user->name }}</h2>
            <p class="text-gray-600 dark:text-gray-400">
                {{ $staff->job_title ?: 'No job title' }} · {{ $staff->department->name ?? 'No department' }} · {{ $staff->status_label }}
                @unless ($staff->is_active) (left {{ $staff->end_date?->format('d M Y') }}) @endunless
            </p>
        </div>
        @if ($staff->is_active)
            <div class="flex flex-wrap gap-2">
                <button type="button" @click="$dispatch('open-staff-form', @js($staff->formData()))" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button type="button" @click="leaveOpen = true" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-primary/90">
                    <i class="fas fa-umbrella-beach"></i> Record Leave
                </button>
                @if ($user->id !== auth()->id())
                    <button type="button" @click="terminateOpen = true" class="inline-flex items-center gap-2 rounded-lg border border-error-300 px-4 py-2.5 text-sm font-medium text-error-600 hover:bg-error-50 dark:border-error-500/40 dark:text-error-400">
                        <i class="fas fa-user-slash"></i> Mark as Left
                    </button>
                @endif
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <!-- Rota -->
            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Shifts (last week → next 2 weeks)</h3>
                    <a href="{{ route('hr.rota.index', ['department_id' => $staff->department_id]) }}" class="text-sm text-primary hover:underline">Rota</a>
                </div>
                <ul class="divide-y divide-gray-100 text-sm dark:divide-gray-800">
                    @forelse ($shifts as $assignment)
                        <li class="flex items-center justify-between px-5 py-2.5">
                            <span class="{{ $assignment->date->isToday() ? 'font-semibold text-primary' : 'text-gray-800 dark:text-white/90' }}">{{ $assignment->date->format('D d M') }}</span>
                            <span class="text-gray-700 dark:text-gray-300">{{ $assignment->shift->name ?? '—' }} <span class="text-xs text-gray-500">{{ $assignment->shift?->hours_label }}</span></span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ \App\Models\ShiftAssignment::STATUSES[$assignment->status] ?? $assignment->status }}{{ (float) $assignment->overtime_hours ? ' · +' . (float) $assignment->overtime_hours . 'h' : '' }}
                            </span>
                        </li>
                    @empty
                        <li class="px-5 py-4 text-gray-500 dark:text-gray-400">Not on the rota.</li>
                    @endforelse
                </ul>
            </div>

            <!-- Leave -->
            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Leave</h3>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $leaveBalance }} of {{ SystemHelper::annualLeaveDays() }} annual days left this year</span>
                </div>
                <ul class="divide-y divide-gray-100 text-sm dark:divide-gray-800">
                    @forelse ($leave as $request)
                        <li class="flex flex-wrap items-center justify-between gap-2 px-5 py-2.5">
                            <span class="text-gray-800 dark:text-white/90">{{ $request->type_label }} · {{ $request->start_date->format('d M') }} → {{ $request->end_date->format('d M Y') }} ({{ $request->days }} {{ Str::plural('day', $request->days) }})</span>
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $request->status_classes }}">{{ \App\Models\LeaveRequest::STATUSES[$request->status] }}</span>
                        </li>
                    @empty
                        <li class="px-5 py-4 text-gray-500 dark:text-gray-400">No leave on record.</li>
                    @endforelse
                </ul>
            </div>

            <!-- Payslips -->
            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Payslips</h3>
                </div>
                <ul class="divide-y divide-gray-100 text-sm dark:divide-gray-800">
                    @forelse ($payslips as $payroll)
                        <li class="flex items-center justify-between px-5 py-2.5">
                            <span class="text-gray-800 dark:text-white/90">{{ $payroll->period_month->format('F Y') }}</span>
                            <span class="text-gray-700 dark:text-gray-300">Net {{ $money($payroll->net) }} · {{ $payroll->status_label }}</span>
                            <a href="{{ route('hr.payroll.payslip', $payroll) }}" target="_blank" class="text-xs text-primary hover:underline">Payslip</a>
                        </li>
                    @empty
                        <li class="px-5 py-4 text-gray-500 dark:text-gray-400">No payroll yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800"><h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Contact &amp; access</h3></div>
                <dl class="space-y-3 p-5 text-sm">
                    {!! $row('Phone', $user->phone) !!}
                    {!! $row('Email', $user->email) !!}
                    {!! $row('System role', $role ? Str::headline($role->name) : 'No access') !!}
                    {!! $row('Login', $user->status === 'active' ? 'Active' : 'Disabled') !!}
                    @if ($staff->emergency_contact)
                        {!! $row('Emergency', trim(implode(' · ', array_filter($staff->emergency_contact)))) !!}
                    @endif
                </dl>
            </div>

            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800"><h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Employment</h3></div>
                <dl class="space-y-3 p-5 text-sm">
                    {!! $row('Started', $staff->hire_date->format('d M Y')) !!}
                    {!! $row('Contract ends', $staff->contract_end_date?->format('d M Y')) !!}
                    {!! $row('Basic salary', $money($staff->salary)) !!}
                    {!! $row('ID / Passport', $staff->id_number) !!}
                    {!! $row('KRA PIN', $staff->kra_pin) !!}
                    {!! $row('NSSF', $staff->nssf_number) !!}
                    {!! $row('SHA', $staff->shif_number) !!}
                    {!! $row('Bank', trim(implode(', ', array_filter([$staff->bank_name, $staff->bank_branch, $staff->account_number])))) !!}
                </dl>
                @if ($staff->notes)
                    <p class="whitespace-pre-line border-t border-gray-100 px-5 py-3 text-sm text-gray-700 dark:border-gray-800 dark:text-gray-300">{{ $staff->notes }}</p>
                @endif
            </div>
        </div>
    </div>

    @if ($staff->is_active)
        @include('hr.partials.leave-modal', ['fixedStaff' => $staff])

        <div x-show="terminateOpen" x-cloak x-transition class="fixed inset-0 z-[99999] flex items-center justify-center p-5" @keydown.escape.window="terminateOpen = false">
            <div class="fixed inset-0 bg-gray-400/50 backdrop-blur-sm dark:bg-gray-900/80" @click="terminateOpen = false"></div>
            <form method="POST" action="{{ route('hr.staff.terminate', $staff) }}" class="relative w-full max-w-md space-y-4 rounded-2xl bg-white p-6 dark:bg-gray-900">
                @csrf
                <h4 class="text-lg font-medium text-gray-800 dark:text-white/90">{{ $user->name }} is leaving</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Their login is disabled and future shifts are removed. Pay, leave and history are kept.</p>
                <input type="date" name="end_date" required value="{{ today()->toDateString() }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">
                <input type="text" name="reason" required maxlength="500" placeholder="Reason (resigned, end of contract, dismissed…)" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">
                <div class="flex justify-end gap-3">
                    <button type="button" @click="terminateOpen = false" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                    <button type="submit" class="rounded-lg bg-error-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-error-600">Confirm</button>
                </div>
            </form>
        </div>

        @include('hr.partials.staff-modal')
    @endif
</div>
@endsection
