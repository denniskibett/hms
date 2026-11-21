@extends('layouts.app')

@php
    $inputClass = 'h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
    $money = fn ($value) => SystemHelper::currencySymbol() . ' ' . number_format((float) $value, 2);
    $moveData = fn ($item, $action = 'out') => ['id' => $item->id, 'name' => $item->name, 'unit' => $item->unit_of_measure, 'quantity' => (float) $item->quantity, 'unit_cost' => (float) $item->unit_cost, 'action' => $action];
@endphp

@section('content')
<div x-data="{ pageName: `Inventory` }">
    @include('partials.breadcrumb')
</div>

<div class="space-y-6">
    @include('inventory.partials.header', ['title' => 'Inventory', 'subtitle' => 'Stock in the stores, what is running low and what it is worth'])

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @include('partials.stat-card', ['label' => 'Items Stocked', 'value' => $stats['items'], 'icon' => 'fa-boxes', 'tone' => 'primary', 'href' => route('inventory.index')])
        @include('partials.stat-card', ['label' => 'Running Low', 'value' => $stats['low'], 'icon' => 'fa-exclamation-triangle', 'tone' => 'warning', 'href' => route('inventory.index', ['stock' => 'low'])])
        @include('partials.stat-card', ['label' => 'Out of Stock', 'value' => $stats['out'], 'icon' => 'fa-times-circle', 'tone' => 'error', 'href' => route('inventory.index', ['stock' => 'out'])])
        @include('partials.stat-card', ['label' => 'Stock Value', 'value' => $money($stats['value']), 'icon' => 'fa-coins', 'tone' => 'success'])
    </div>

    <form method="GET" action="{{ route('inventory.index') }}" class="grid grid-cols-1 gap-3 rounded-2xl border border-gray-100 bg-white p-4 sm:grid-cols-2 lg:grid-cols-4 dark:border-gray-800 dark:bg-gray-900">
        <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Name, code or barcode" class="{{ $inputClass }} lg:col-span-2">
        <select name="item_type" class="{{ $inputClass }}" onchange="this.form.submit()">
            <option value="">All kinds</option>
            @foreach (\App\Models\InventoryItem::TYPES as $value => $label)
                <option value="{{ $value }}" @selected(($filters['item_type'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <select name="stock" class="{{ $inputClass }}" onchange="this.form.submit()">
                <option value="">All stock levels</option>
                <option value="low" @selected(($filters['stock'] ?? '') === 'low')>Running low</option>
                <option value="out" @selected(($filters['stock'] ?? '') === 'out')>Out of stock</option>
            </select>
            @if (array_filter($filters))
                <a href="{{ route('inventory.index') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Clear</a>
            @endif
        </div>
    </form>

    <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="custom-scrollbar overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr class="whitespace-nowrap border-b border-gray-100 text-gray-700 dark:border-gray-800 dark:text-gray-400">
                        <th class="px-5 py-3 font-medium">Item</th>
                        <th class="px-5 py-3 font-medium">Kind</th>
                        <th class="px-5 py-3 text-right font-medium">In stock</th>
                        <th class="px-5 py-3 text-right font-medium">Reorder at</th>
                        <th class="px-5 py-3 text-right font-medium">Value</th>
                        <th class="px-5 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($items as $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 {{ $item->status ? '' : 'opacity-60' }}">
                            <td class="px-5 py-3">
                                <a href="{{ route('inventory.show', $item) }}" class="font-medium text-gray-800 hover:text-primary dark:text-white/90">{{ $item->name }}</a>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $item->sku }}{{ $item->location ? ' · ' . $item->location : '' }}</p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-gray-700 dark:text-gray-300">{{ $item->type_label }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-right font-medium {{ (float) $item->quantity <= 0 ? 'text-error-600 dark:text-error-400' : ($item->is_low ? 'text-warning-600 dark:text-warning-400' : 'text-gray-800 dark:text-white/90') }}">
                                {{ $item->qty() }}
                                @if ($item->is_low)<i class="fas fa-exclamation-triangle ml-1 text-xs"></i>@endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-right text-gray-500 dark:text-gray-400">{{ $item->qty($item->reorder_level) }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-right text-gray-700 dark:text-gray-300">{{ $money($item->stock_value) }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-right">
                                @if ($canReceive)
                                    <button type="button" @click="$dispatch('open-move', @js($moveData($item, 'in')))" class="rounded-lg bg-success-500/10 px-2.5 py-1 text-xs font-medium text-success-600 hover:bg-success-500/20">Receive</button>
                                @endif
                                <button type="button" @click="$dispatch('open-move', @js($moveData($item)))" class="rounded-lg bg-primary/10 px-2.5 py-1 text-xs font-medium text-primary hover:bg-primary/20">Issue</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                                <i class="fas fa-boxes mb-3 text-4xl"></i>
                                <p class="text-lg">{{ array_filter($filters) ? 'No items match' : 'Nothing in the store yet' }}</p>
                                @if ($canManage && ! array_filter($filters))
                                    <button type="button" @click="$dispatch('open-item-form')" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/90"><i class="fas fa-plus"></i> Add the first item</button>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($items->hasPages())
            <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">{{ $items->links() }}</div>
        @endif
    </div>
</div>

@include('inventory.partials.move-modal')
@if ($canManage)
    @include('inventory.partials.item-modal')
@endif
@endsection
