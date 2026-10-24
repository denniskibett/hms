@extends('layouts.app')

@php
    $inputClass = 'h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
    $money = fn ($value) => SystemHelper::currencySymbol() . ' ' . number_format((float) $value, 2);
    $canEdit = auth()->user()->hasAnyRole(['admin', 'manager', 'procurement']);
@endphp

@section('content')
<div x-data="{ pageName: `Suppliers` }">
    @include('partials.breadcrumb')
</div>

<div class="space-y-6">
    @include('procurement.partials.header', [
        'title' => 'Suppliers',
        'subtitle' => 'Who the hotel buys from',
        'actions' => $canEdit ? '<button type="button" @click="$dispatch(\'open-supplier-form\')" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-primary/90"><i class="fas fa-plus"></i> New Supplier</button>' : null,
    ])

    <form method="GET" action="{{ route('suppliers.index') }}" class="grid grid-cols-1 gap-3 rounded-2xl border border-gray-100 bg-white p-4 sm:grid-cols-3 dark:border-gray-800 dark:bg-gray-900">
        <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Name, contact or phone" class="{{ $inputClass }} sm:col-span-2">
        <select name="status" class="{{ $inputClass }}" onchange="this.form.submit()">
            <option value="active" @selected(($filters['status'] ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Not used any more</option>
        </select>
    </form>

    <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="custom-scrollbar overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr class="whitespace-nowrap border-b border-gray-100 text-gray-700 dark:border-gray-800 dark:text-gray-400">
                        <th class="px-5 py-3 font-medium">Supplier</th>
                        <th class="px-5 py-3 font-medium">Contact</th>
                        <th class="px-5 py-3 font-medium">Terms</th>
                        <th class="px-5 py-3 text-right font-medium">Items supplied</th>
                        <th class="px-5 py-3 text-right font-medium">On order</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($suppliers as $supplier)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-5 py-3">
                                <a href="{{ route('suppliers.show', $supplier) }}" class="font-medium text-gray-800 hover:text-primary dark:text-white/90">{{ $supplier->name }}</a>
                                @if ($supplier->kra_pin)<p class="text-xs text-gray-500 dark:text-gray-400">PIN {{ $supplier->kra_pin }}</p>@endif
                            </td>
                            <td class="px-5 py-3">
                                <p class="text-gray-800 dark:text-white/90">{{ $supplier->contact_person ?: '—' }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $supplier->phone }}</p>
                            </td>
                            <td class="px-5 py-3 text-gray-700 dark:text-gray-300">{{ $supplier->payment_terms ?: '—' }}</td>
                            <td class="px-5 py-3 text-right text-gray-700 dark:text-gray-300">{{ $supplier->items_count }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-right text-gray-800 dark:text-white/90">{{ (float) $supplier->outstanding_total ? $money($supplier->outstanding_total) : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400"><i class="fas fa-truck mb-3 text-4xl"></i><p>No suppliers yet</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($suppliers->hasPages())
            <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">{{ $suppliers->links() }}</div>
        @endif
    </div>
</div>

@if ($canEdit)
    @include('procurement.suppliers.partials.modal-form')
@endif
@endsection
