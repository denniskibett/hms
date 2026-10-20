@extends('layouts.app')

@php
    $inputClass = 'h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
    $currency = SystemHelper::currencySymbol();
    $money = fn ($value) => $currency . ' ' . number_format((float) $value, 2);
    $period = $from->isSameDay($to)
        ? $from->format('D, ' . SystemHelper::dateFormat())
        : $from->format(SystemHelper::dateFormat()) . ' – ' . $to->format(SystemHelper::dateFormat());
@endphp

@section('content')
    <div x-data="{ pageName: `Payments` }" class="print:hidden">
        @include('partials.breadcrumb')
    </div>

<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Payments</h2>
            <p class="text-gray-600 dark:text-gray-400">Collections for {{ $period }}</p>
        </div>
        <button type="button" onclick="window.print()"
                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-800 shadow-theme-xs hover:bg-gray-50 print:hidden dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-white/[0.03]">
            <i class="fas fa-print"></i> Print cash-up
        </button>
    </div>

    <!-- Filters -->
    <form method="GET" action="{{ route('payments.index') }}"
          class="grid grid-cols-1 gap-3 rounded-2xl border border-gray-100 bg-white p-4 sm:grid-cols-2 lg:grid-cols-6 print:hidden dark:border-gray-800 dark:bg-gray-900">
        <input type="date" name="from" value="{{ $from->toDateString() }}" title="From" class="{{ $inputClass }}">
        <input type="date" name="to" value="{{ $to->toDateString() }}" title="To" class="{{ $inputClass }}">

        <select name="method" class="{{ $inputClass }}" onchange="this.form.submit()">
            <option value="">All methods</option>
            @foreach (\App\Models\Payment::METHODS as $value => $label)
                <option value="{{ $value }}" @selected(($filters['method'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>

        <select name="status" class="{{ $inputClass }}" onchange="this.form.submit()">
            <option value="">Completed & voided</option>
            <option value="completed" @selected(($filters['status'] ?? '') === 'completed')>Completed</option>
            <option value="cancelled" @selected(($filters['status'] ?? '') === 'cancelled')>Voided</option>
        </select>

        <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Reference, invoice or guest" class="{{ $inputClass }}">

        <div class="flex gap-2">
            <button type="submit" class="flex-1 rounded-lg bg-primary px-3 py-2 text-sm font-medium text-white hover:bg-primary/90">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            @if (array_filter($filters))
                <a href="{{ route('payments.index') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                    Today
                </a>
            @endif
        </div>
    </form>

    <!-- Cash-up: totals per method (completed payments only) -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-2xl border border-gray-100 bg-white p-5 lg:col-span-1 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total collected</p>
            <p class="mt-1 text-3xl font-bold text-gray-800 dark:text-white/90">{{ $money($stats['total']) }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $stats['count'] }} {{ Str::plural('payment', $stats['count']) }}</p>
            @if ($stats['voided'] > 0)
                <p class="mt-3 text-xs text-error-500">{{ $money($stats['voided']) }} voided (not counted)</p>
            @endif
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white lg:col-span-2 dark:border-gray-800 dark:bg-gray-900">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr class="border-b border-gray-100 text-left dark:border-gray-800">
                        <th class="px-5 py-3 font-medium text-gray-700 dark:text-gray-400">Method</th>
                        <th class="px-5 py-3 text-right font-medium text-gray-700 dark:text-gray-400">Payments</th>
                        <th class="px-5 py-3 text-right font-medium text-gray-700 dark:text-gray-400">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach (\App\Models\Payment::METHODS as $value => $label)
                        @php $row = $byMethod->get($value); @endphp
                        <tr class="{{ $row ? '' : 'text-gray-400 dark:text-gray-600' }}">
                            <td class="px-5 py-2.5 {{ $row ? 'text-gray-800 dark:text-white/90' : '' }}">{{ $label }}</td>
                            <td class="px-5 py-2.5 text-right">{{ $row->count ?? 0 }}</td>
                            <td class="px-5 py-2.5 text-right font-medium {{ $row ? 'text-gray-800 dark:text-white/90' : '' }}">{{ $money($row->total ?? 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
        @include('payments.partials.table', ['payments' => $payments, 'empty' => 'No payments in this period'])

        @if ($payments->hasPages())
            <div class="border-t border-gray-100 px-5 py-4 print:hidden dark:border-gray-800">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
