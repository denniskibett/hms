@extends('layouts.app')

@php
    $currency = SystemHelper::currencySymbol();
    $money = fn ($value) => $currency . ' ' . number_format((float) $value, 2);
    $month = now()->format('F Y');
    $methodTotal = max((float) $byMethod->sum('total'), 1);
@endphp

@section('content')
    <div x-data="{ pageName: `Finance` }">
        @include('partials.breadcrumb')
    </div>

<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Finance</h2>
            <p class="text-gray-600 dark:text-gray-400">Takings, balances owed and tax for {{ $month }}</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('payments.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-800 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-white/[0.03]">
                <i class="fas fa-cash-register"></i> Today's cash-up
            </a>
            <a href="{{ route('invoices.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-primary/90">
                <i class="fas fa-file-invoice"></i> Invoices
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @include('partials.stat-card', ['label' => 'Collected Today', 'value' => $money($stats['collected_today']), 'icon' => 'fa-cash-register', 'tone' => 'success', 'href' => route('payments.index')])
        @include('partials.stat-card', ['label' => 'Collected This Month', 'value' => $money($stats['collected_month']), 'icon' => 'fa-coins', 'tone' => 'primary', 'href' => route('payments.index', ['from' => now()->startOfMonth()->toDateString(), 'to' => today()->toDateString()])])
        @include('partials.stat-card', ['label' => 'Owed by In-house Guests', 'value' => $money($stats['in_house']), 'icon' => 'fa-bed', 'tone' => 'info'])
        @include('partials.stat-card', ['label' => 'Overdue', 'value' => $money($stats['overdue']), 'icon' => 'fa-exclamation-triangle', 'tone' => 'error', 'href' => route('invoices.index', ['status' => 'overdue'])])
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Overdue balances -->
        <div class="rounded-2xl border border-gray-100 bg-white lg:col-span-2 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <div>
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">To follow up</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Guests past their departure date with a balance. Total outstanding: {{ $money($stats['outstanding']) }}</p>
                </div>
                <a href="{{ route('invoices.index', ['status' => 'overdue']) }}" class="text-sm text-primary hover:underline">All overdue</a>
            </div>
            @include('invoices.partials.table', ['invoices' => $overdue, 'empty' => 'Nothing overdue'])
        </div>

        <div class="space-y-6">
            <!-- Collections by method -->
            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">How guests paid</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $month }}</p>
                </div>
                <div class="space-y-4 p-5">
                    @forelse ($byMethod as $row)
                        <div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-700 dark:text-gray-300">{{ \App\Models\Payment::METHODS[$row->method] ?? $row->method }}</span>
                                <span class="font-medium text-gray-800 dark:text-white/90">{{ $money($row->total) }}</span>
                            </div>
                            <div class="mt-1.5 h-2 rounded-full bg-gray-100 dark:bg-gray-800">
                                <div class="h-2 rounded-full bg-primary" style="width: {{ round($row->total / $methodTotal * 100, 1) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400">No payments yet this month.</p>
                    @endforelse
                </div>
            </div>

            <!-- Tax -->
            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Tax on invoices</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Invoices issued in {{ $month }}</p>
                </div>
                <dl class="space-y-2 p-5 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Invoiced</dt><dd class="font-medium text-gray-800 dark:text-white/90">{{ $money($tax['billed']) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">VAT</dt><dd class="text-gray-800 dark:text-white/90">{{ $money($tax['vat']) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Tourism Levy</dt><dd class="text-gray-800 dark:text-white/90">{{ $money($tax['levy']) }}</dd></div>
                </dl>
            </div>
        </div>
    </div>

    <!-- Recent payments -->
    <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-800">
            <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Recent payments</h3>
            <a href="{{ route('payments.index') }}" class="text-sm text-primary hover:underline">All payments</a>
        </div>
        @include('payments.partials.table', ['payments' => $recentPayments, 'empty' => 'No payments yet'])
    </div>
</div>
@endsection
