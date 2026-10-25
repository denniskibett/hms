@extends('layouts.app')

@php
    $money = fn ($value) => SystemHelper::currencySymbol() . ' ' . number_format((float) $value, 2);
    $user = auth()->user();
    $canEdit = $user->hasAnyRole(['admin', 'manager', 'procurement']);
    $canBuy = $user->hasAnyRole(['admin', 'manager', 'procurement', 'kitchen']);
    $canPay = $user->hasAnyRole(['admin', 'manager', 'finance']);
@endphp

@section('content')
<div x-data="{ pageName: @js($supplier->name) }">
    @include('partials.breadcrumb')
</div>

<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <a href="{{ route('suppliers.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400"><i class="fas fa-arrow-left"></i> Back to Suppliers</a>
            <h2 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $supplier->name }}</h2>
            <p class="text-gray-600 dark:text-gray-400">
                {{ collect([$supplier->contact_person, $supplier->phone, $supplier->email])->filter()->implode(' · ') ?: 'No contact details' }}
                @if ($supplier->status !== 'active') · <span class="text-error-500">not used any more</span>@endif
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if ($canEdit)
                <button type="button" @click="$dispatch('open-supplier-form', @js($supplier->formData()))" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"><i class="fas fa-edit"></i> Edit</button>
            @endif
            @if ($canBuy && $lowStock->isNotEmpty() && $supplier->status === 'active')
                <form method="POST" action="{{ route('purchase-orders.reorder') }}">
                    @csrf
                    <input type="hidden" name="supplier_id" value="{{ $supplier->id }}">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-primary/90"><i class="fas fa-redo"></i> Order low stock ({{ $lowStock->count() }})</button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800"><h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Purchase orders</h3></div>
                @include('procurement.orders.partials.table', ['orders' => $orders, 'hideSupplier' => true])
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Account</h3>
                    @if ($canPay && $account['balance'] > 0)
                        <a href="{{ route('expenses.index', ['pay_supplier' => $supplier->id]) }}" class="text-sm text-primary hover:underline">Record payment</a>
                    @endif
                </div>
                <dl class="space-y-3 p-5 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Goods received</dt><dd class="text-gray-800 dark:text-white/90">{{ $money($account['received']) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Paid</dt><dd class="text-gray-800 dark:text-white/90">{{ $money($account['paid']) }}</dd></div>
                    <div class="flex justify-between font-semibold"><dt class="text-gray-800 dark:text-white/90">{{ $account['balance'] < 0 ? 'Paid in advance' : 'We owe' }}</dt><dd class="{{ $account['balance'] > 0 ? 'text-warning-600 dark:text-warning-400' : 'text-success-600 dark:text-success-500' }}">{{ $money(abs($account['balance'])) }}</dd></div>
                </dl>
                @if ($payments->isNotEmpty())
                    <ul class="divide-y divide-gray-100 border-t border-gray-100 text-xs dark:divide-gray-800 dark:border-gray-800">
                        @foreach ($payments as $payment)
                            <li class="flex justify-between px-5 py-2">
                                <span class="text-gray-600 dark:text-gray-400">{{ $payment->spent_on->format('d M Y') }} · {{ $payment->method_label }}{{ $payment->purchaseOrder ? ' · ' . $payment->purchaseOrder->po_number : '' }}</span>
                                <span class="text-gray-800 dark:text-white/90">{{ $money($payment->amount) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800"><h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Details</h3></div>
                <dl class="space-y-3 p-5 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-gray-500 dark:text-gray-400">KRA PIN</dt><dd class="text-gray-800 dark:text-white/90">{{ $supplier->kra_pin ?: '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-gray-500 dark:text-gray-400">Payment terms</dt><dd class="text-gray-800 dark:text-white/90">{{ $supplier->payment_terms ?: '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-gray-500 dark:text-gray-400">Address</dt><dd class="text-right text-gray-800 dark:text-white/90">{{ $supplier->address ?: '—' }}</dd></div>
                </dl>
                @if ($supplier->notes)
                    <p class="whitespace-pre-line border-t border-gray-100 px-5 py-3 text-sm text-gray-700 dark:border-gray-800 dark:text-gray-300">{{ $supplier->notes }}</p>
                @endif
            </div>

            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800"><h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Last prices paid</h3></div>
                <ul class="divide-y divide-gray-100 text-sm dark:divide-gray-800">
                    @forelse ($prices as $price)
                        <li class="flex justify-between px-5 py-2.5">
                            <span class="text-gray-800 dark:text-white/90">{{ $price->inventoryItem->name ?? 'Item' }}</span>
                            <span class="text-gray-600 dark:text-gray-400">{{ $money($price->unit_price) }} / {{ $price->inventoryItem->unit_of_measure ?? 'unit' }} <span class="text-xs">({{ $price->effective_from->format('d M Y') }})</span></span>
                        </li>
                    @empty
                        <li class="px-5 py-3 text-gray-500 dark:text-gray-400">Nothing received yet.</li>
                    @endforelse
                </ul>
            </div>

            @if ($lowStock->isNotEmpty())
                <div class="rounded-2xl border border-warning-200 bg-warning-50 p-5 text-sm dark:border-warning-500/30 dark:bg-warning-500/10">
                    <p class="mb-2 font-medium text-warning-700 dark:text-warning-400">Running low</p>
                    @foreach ($lowStock as $item)
                        <p class="text-warning-700 dark:text-warning-400">{{ $item->name }}: {{ $item->qty() }}</p>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

@if ($canEdit)
    @include('procurement.suppliers.partials.modal-form')
@endif
@endsection
