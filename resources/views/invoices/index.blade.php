@extends('layouts.app')

@php
    $inputClass = 'h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
    $currency = SystemHelper::currencySymbol();
    $money = fn ($value) => $currency . ' ' . number_format((float) $value, 2);
@endphp

@section('content')
    <div x-data="{ pageName: `Invoices` }">
        @include('partials.breadcrumb')
    </div>

<div class="space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Invoices</h2>
        <p class="text-gray-600 dark:text-gray-400">Every booking's invoice. Charges and payments are made on the folio.</p>
    </div>

    <!-- Totals for the current filters -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @include('partials.stat-card', ['label' => 'Billed', 'value' => $money($stats['billed']), 'icon' => 'fa-file-invoice', 'tone' => 'primary'])
        @include('partials.stat-card', ['label' => 'Collected', 'value' => $money($stats['collected']), 'icon' => 'fa-check-circle', 'tone' => 'success'])
        @include('partials.stat-card', ['label' => 'Outstanding', 'value' => $money($stats['outstanding']), 'icon' => 'fa-hourglass-half', 'tone' => 'warning'])
        @include('partials.stat-card', ['label' => 'Overdue', 'value' => $money($stats['overdue']), 'icon' => 'fa-exclamation-triangle', 'tone' => 'error', 'href' => route('invoices.index', ['status' => 'overdue'])])
    </div>

    <!-- Filters -->
    <form method="GET" action="{{ route('invoices.index') }}"
          class="grid grid-cols-1 gap-3 rounded-2xl border border-gray-100 bg-white p-4 sm:grid-cols-2 lg:grid-cols-6 dark:border-gray-800 dark:bg-gray-900">
        <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Invoice number, guest name or phone"
               class="{{ $inputClass }} lg:col-span-2">

        <select name="status" class="{{ $inputClass }}" onchange="this.form.submit()">
            <option value="">All statuses</option>
            @foreach (\App\Enums\InvoiceStatus::options() as $value => $label)
                <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>

        <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" title="Issued from" class="{{ $inputClass }}">
        <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" title="Issued to" class="{{ $inputClass }}">

        <div class="flex gap-2">
            <button type="submit" class="flex-1 rounded-lg bg-primary px-3 py-2 text-sm font-medium text-white hover:bg-primary/90">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            @if (array_filter($filters))
                <a href="{{ route('invoices.index') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                    Clear
                </a>
            @endif
        </div>
    </form>

    <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
        @include('invoices.partials.table', ['invoices' => $invoices, 'empty' => array_filter($filters) ? 'No invoices match these filters' : 'No invoices yet'])

        @if ($invoices->hasPages())
            <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
