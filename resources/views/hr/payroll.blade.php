@extends('layouts.app')

@php
    $money = fn ($value) => number_format((float) $value, 2);
    $currency = SystemHelper::currencySymbol();
    $user = auth()->user();
    $isHr = $user->hasAnyRole(['admin', 'manager', 'hr']);
    $canApprove = $user->hasAnyRole(['admin', 'manager']);
    $canPay = $user->hasAnyRole(['admin', 'manager', 'finance']);
    $period = \Carbon\Carbon::parse($month . '-01');
    $drafts = $payrolls->where('status', 'draft')->count();
    $approved = $payrolls->where('status', 'approved')->count();
    $statusClasses = [
        'draft' => 'bg-gray-100 text-gray-600 dark:bg-gray-500/15 dark:text-gray-400',
        'approved' => 'bg-info-50 text-info-700 dark:bg-info-500/15 dark:text-info-500',
        'paid' => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500',
    ];
    $bulk = fn ($status, $label, $class) => '<form method="POST" action="' . route('hr.payroll.status') . '" onsubmit="return confirm(\'' . $label . '?\')">'
        . csrf_field() . '<input type="hidden" name="status" value="' . $status . '"><input type="hidden" name="month" value="' . $month . '">'
        . '<button type="submit" class="' . $class . '">' . $label . '</button></form>';
    $primaryButton = 'inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-primary/90';
@endphp

@section('content')
<div x-data="{ pageName: `Payroll` }">
    @include('partials.breadcrumb')
</div>

<div class="space-y-6">
    @include('hr.partials.header', ['title' => 'Payroll', 'subtitle' => 'Monthly pay with PAYE, NSSF, SHA and Housing Levy'])

    <div class="flex flex-wrap items-center justify-between gap-3">
        <form method="GET" action="{{ route('hr.payroll.index') }}" class="flex items-center gap-2">
            <input type="month" name="month" value="{{ $month }}" onchange="this.form.submit()"
                   class="h-10 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $payrolls->count() }} {{ Str::plural('employee', $payrolls->count()) }}</span>
        </form>
        <div class="flex flex-wrap gap-2">
            @if ($isHr && $missing)
                <form method="POST" action="{{ route('hr.payroll.generate') }}">
                    @csrf
                    <input type="hidden" name="month" value="{{ $month }}">
                    <button type="submit" class="{{ $primaryButton }}"><i class="fas fa-calculator"></i> Prepare payroll ({{ $missing }})</button>
                </form>
            @endif
            @if ($canApprove && $drafts)
                {!! $bulk('approved', "Approve {$drafts} drafts", $primaryButton) !!}
            @endif
            @if ($canPay && $approved)
                {!! $bulk('paid', "Mark {$approved} as paid", 'inline-flex items-center gap-2 rounded-lg bg-success-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-success-600') !!}
            @endif
        </div>
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900" x-data>
        <div class="custom-scrollbar overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr class="whitespace-nowrap border-b border-gray-100 text-right text-gray-700 dark:border-gray-800 dark:text-gray-400">
                        <th class="px-4 py-3 text-left font-medium">Employee</th>
                        <th class="px-4 py-3 font-medium">Gross</th>
                        <th class="px-4 py-3 font-medium">PAYE</th>
                        <th class="px-4 py-3 font-medium">NSSF</th>
                        <th class="px-4 py-3 font-medium">SHA</th>
                        <th class="px-4 py-3 font-medium">Housing</th>
                        <th class="px-4 py-3 font-medium">Other</th>
                        <th class="px-4 py-3 font-medium">Net pay</th>
                        <th class="px-4 py-3 text-left font-medium">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-right dark:divide-gray-800">
                    @forelse ($payrolls as $payroll)
                        <tr>
                            <td class="px-4 py-3 text-left">
                                <p class="font-medium text-gray-800 dark:text-white/90">{{ $payroll->user->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Basic {{ $money($payroll->basic_salary) }}{{ (float) $payroll->overtime_amount ? ' · OT ' . $money($payroll->overtime_amount) : '' }}{{ (float) $payroll->allowances ? ' · Allow. ' . $money($payroll->allowances) : '' }}{{ (float) $payroll->bonuses ? ' · Bonus ' . $money($payroll->bonuses) : '' }}
                                </p>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-gray-800 dark:text-white/90">{{ $money($payroll->gross) }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-300">{{ $money($payroll->paye) }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-300">{{ $money($payroll->nssf) }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-300">{{ $money($payroll->shif) }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-300">{{ $money($payroll->housing_levy) }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-300">{{ $money($payroll->other_deductions) }}</td>
                            <td class="whitespace-nowrap px-4 py-3 font-semibold text-gray-800 dark:text-white/90">{{ $money($payroll->net) }}</td>
                            <td class="px-4 py-3 text-left"><span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusClasses[$payroll->status] ?? '' }}">{{ $payroll->status_label }}</span></td>
                            <td class="whitespace-nowrap px-4 py-3">
                                @if ($isHr && $payroll->status === 'draft')
                                    <button type="button" @click="$dispatch('open-payroll-form', @js($payroll->only('id', 'basic_salary', 'overtime_hours', 'allowances', 'bonuses', 'other_deductions', 'notes') + ['name' => $payroll->user->name]))" class="text-xs text-primary hover:underline">Adjust</button>
                                @endif
                                <a href="{{ route('hr.payroll.payslip', $payroll) }}" target="_blank" class="ml-2 text-xs text-primary hover:underline">Payslip</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                                <i class="fas fa-money-check-alt mb-3 text-4xl"></i>
                                <p>No payroll for {{ $period->format('F Y') }} yet.{{ $isHr && $missing ? ' Click "Prepare payroll" to create it from staff salaries and rota overtime.' : '' }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($payrolls->isNotEmpty())
                    <tfoot class="border-t-2 border-gray-200 text-right font-semibold text-gray-800 dark:border-gray-700 dark:text-white/90">
                        <tr>
                            <td class="px-4 py-3 text-left">Totals ({{ $currency }})</td>
                            <td class="whitespace-nowrap px-4 py-3">{{ $money($totals['gross']) }}</td>
                            <td class="whitespace-nowrap px-4 py-3">{{ $money($totals['paye']) }}</td>
                            <td class="whitespace-nowrap px-4 py-3">{{ $money($totals['nssf']) }}</td>
                            <td class="whitespace-nowrap px-4 py-3">{{ $money($totals['shif']) }}</td>
                            <td class="whitespace-nowrap px-4 py-3">{{ $money($totals['housing_levy']) }}</td>
                            <td class="whitespace-nowrap px-4 py-3">{{ $money($totals['other']) }}</td>
                            <td class="whitespace-nowrap px-4 py-3">{{ $money($totals['net']) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    <p class="text-xs text-gray-500 dark:text-gray-400">
        Deductions use the 2025 Kenyan rates: PAYE bands with {{ $currency }} {{ number_format(SystemHelper::payroll('personal_relief')) }} personal relief,
        NSSF {{ SystemHelper::payroll('nssf_rate') }}% up to {{ number_format(SystemHelper::payroll('nssf_upper_limit')) }},
        SHA {{ SystemHelper::payroll('shif_rate') }}% (min {{ SystemHelper::payroll('shif_minimum') }}),
        Housing Levy {{ SystemHelper::payroll('housing_levy_rate') }}%. NSSF, SHA and the levy are deducted before PAYE.
        Confirm these with your accountant whenever KRA changes them.
    </p>
</div>

@if ($isHr)
    @include('hr.partials.payroll-modal')
@endif
@endsection
