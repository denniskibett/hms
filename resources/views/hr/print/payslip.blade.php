@extends('layouts.print')

@php
    $money = fn ($value) => number_format((float) $value, 2);
    $staff = $payroll->staffProfile;
    $earnings = array_filter([
        'Basic salary' => $payroll->basic_salary,
        'Overtime (' . (float) $payroll->overtime_hours . ' h)' => (float) $payroll->overtime_amount ? $payroll->overtime_amount : null,
        'Allowances' => (float) $payroll->allowances ? $payroll->allowances : null,
        'Bonus' => (float) $payroll->bonuses ? $payroll->bonuses : null,
    ], fn ($v) => $v !== null);
    $deductions = array_filter([
        'PAYE' => $payroll->paye,
        'NSSF' => $payroll->nssf,
        'SHA (SHIF)' => $payroll->shif,
        'Housing Levy' => $payroll->housing_levy,
        'Other deductions' => (float) $payroll->other_deductions ? $payroll->other_deductions : null,
    ], fn ($v) => $v !== null);
@endphp

@section('title', 'Payslip ' . $payroll->user->name . ' ' . $payroll->period_month->format('M Y'))

@section('document-heading')
    <p class="text-2xl font-bold uppercase tracking-wide text-primary">Payslip</p>
    <p class="text-sm font-medium">{{ $payroll->period_month->format('F Y') }}</p>
    @if ($payroll->status !== 'paid')
        <p class="mt-1 inline-block rounded bg-warning-50 px-2 py-0.5 text-xs font-semibold uppercase text-warning-700">{{ $payroll->status_label }}</p>
    @endif
@endsection

@section('content')
    <section class="grid grid-cols-2 gap-6 py-6 text-sm">
        <div>
            <p class="mb-1 text-xs font-semibold uppercase text-gray-500">Employee</p>
            <p class="font-medium">{{ $payroll->user->name }}</p>
            <p>{{ $staff?->job_title }}{{ $staff?->department ? ' · ' . $staff->department->name : '' }}</p>
            @if ($staff?->id_number)<p>ID {{ $staff->id_number }}</p>@endif
        </div>
        <div class="text-right">
            @if ($staff?->kra_pin)<p>KRA PIN {{ $staff->kra_pin }}</p>@endif
            @if ($staff?->nssf_number)<p>NSSF {{ $staff->nssf_number }}</p>@endif
            @if ($staff?->shif_number)<p>SHA {{ $staff->shif_number }}</p>@endif
            @if ($staff?->account_number)<p>{{ $staff->bank_name }} {{ $staff->account_number }}</p>@endif
        </div>
    </section>

    <div class="grid grid-cols-2 gap-8 text-sm">
        <table class="w-full">
            <thead><tr class="border-y border-gray-300 text-left text-xs uppercase text-gray-500"><th class="py-2">Earnings</th><th class="py-2 text-right">Amount</th></tr></thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($earnings as $label => $amount)
                    <tr><td class="py-1.5">{{ $label }}</td><td class="py-1.5 text-right">{{ $money($amount) }}</td></tr>
                @endforeach
            </tbody>
            <tfoot><tr class="border-t border-gray-300 font-semibold"><td class="py-2">Gross pay</td><td class="py-2 text-right">{{ $money($payroll->gross) }}</td></tr></tfoot>
        </table>
        <table class="w-full">
            <thead><tr class="border-y border-gray-300 text-left text-xs uppercase text-gray-500"><th class="py-2">Deductions</th><th class="py-2 text-right">Amount</th></tr></thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($deductions as $label => $amount)
                    <tr><td class="py-1.5">{{ $label }}</td><td class="py-1.5 text-right">{{ $money($amount) }}</td></tr>
                @endforeach
            </tbody>
            <tfoot><tr class="border-t border-gray-300 font-semibold"><td class="py-2">Total deductions</td><td class="py-2 text-right">{{ $money((float) $payroll->tax_deductions + (float) $payroll->other_deductions) }}</td></tr></tfoot>
        </table>
    </div>

    <div class="mt-8 flex items-center justify-between rounded-lg bg-gray-50 px-5 py-4">
        <span class="text-sm font-semibold uppercase text-gray-600">Net pay ({{ SystemHelper::currencySymbol() }})</span>
        <span class="text-2xl font-bold">{{ $money($payroll->net) }}</span>
    </div>

    @if ($payroll->notes)
        <p class="mt-4 text-sm text-gray-600">{{ $payroll->notes }}</p>
    @endif
    <p class="mt-6 text-xs text-gray-500">
        Personal relief of {{ number_format(SystemHelper::payroll('personal_relief'), 2) }} applied. NSSF, SHA and Housing Levy are deducted before PAYE.
        @if ($payroll->paid_at) Paid {{ $payroll->paid_at->format('d M Y') }}. @endif
    </p>
@endsection
