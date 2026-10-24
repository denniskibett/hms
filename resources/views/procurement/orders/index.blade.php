@extends('layouts.app')

@php
    $inputClass = 'h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
    $money = fn ($value) => SystemHelper::currencySymbol() . ' ' . number_format((float) $value, 2);
@endphp

@section('content')
<div x-data="{ pageName: `Purchase Orders` }">
    @include('partials.breadcrumb')
</div>

<div class="space-y-6">
    @include('procurement.partials.header', [
        'title' => 'Purchase Orders',
        'subtitle' => 'Ordering from suppliers and booking deliveries into stock',
        'actions' => $canBuy ? '<button type="button" @click="$dispatch(\'open-order-form\')" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-primary/90"><i class="fas fa-plus"></i> New Order</button>' : null,
    ])

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @include('partials.stat-card', ['label' => 'Awaiting Approval', 'value' => $stats['awaiting'], 'icon' => 'fa-hourglass-half', 'tone' => 'warning', 'href' => route('purchase-orders.index', ['status' => 'submitted'])])
        @include('partials.stat-card', ['label' => 'Waiting for Delivery', 'value' => $stats['outstanding'], 'icon' => 'fa-truck', 'tone' => 'info'])
        @include('partials.stat-card', ['label' => 'Value on Order', 'value' => $money($stats['outstanding_value']), 'icon' => 'fa-file-invoice-dollar', 'tone' => 'primary'])
        @include('partials.stat-card', ['label' => 'Received This Month', 'value' => $money($stats['received_month']), 'icon' => 'fa-dolly', 'tone' => 'success'])
    </div>

    <form method="GET" action="{{ route('purchase-orders.index') }}" class="grid grid-cols-1 gap-3 rounded-2xl border border-gray-100 bg-white p-4 sm:grid-cols-2 lg:grid-cols-4 dark:border-gray-800 dark:bg-gray-900">
        <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="PO number, supplier or invoice no." class="{{ $inputClass }} lg:col-span-2">
        <select name="status" class="{{ $inputClass }}" onchange="this.form.submit()">
            <option value="">All statuses</option>
            @foreach (\App\Enums\PurchaseOrderStatus::options() as $value => $label)
                <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="supplier_id" class="{{ $inputClass }}" onchange="this.form.submit()">
            <option value="">All suppliers</option>
            @foreach ($suppliers as $supplier)
                <option value="{{ $supplier->id }}" @selected((int) ($filters['supplier_id'] ?? 0) === $supplier->id)>{{ $supplier->name }}</option>
            @endforeach
        </select>
    </form>

    <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
        @include('procurement.orders.partials.table', ['orders' => $orders])
        @if ($orders->hasPages())
            <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">{{ $orders->links() }}</div>
        @endif
    </div>
</div>

@if ($canBuy)
    @include('procurement.orders.partials.order-modal')
@endif
@endsection
