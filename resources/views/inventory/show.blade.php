@extends('layouts.app')

@php
    $money = fn ($value) => SystemHelper::currencySymbol() . ' ' . number_format((float) $value, 2);
    $moveData = fn ($action) => ['id' => $item->id, 'name' => $item->name, 'unit' => $item->unit_of_measure, 'quantity' => (float) $item->quantity, 'unit_cost' => (float) $item->unit_cost, 'action' => $action];
    $button = 'inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300';
@endphp

@section('content')
<div x-data="{ pageName: @js($item->name) }">
    @include('partials.breadcrumb')
</div>

<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <a href="{{ route('inventory.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                <i class="fas fa-arrow-left"></i> Back to Inventory
            </a>
            <h2 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $item->name }}</h2>
            <p class="text-gray-600 dark:text-gray-400">{{ $item->sku }} · {{ $item->type_label }}{{ $item->location ? ' · ' . $item->location : '' }}{{ $item->status ? '' : ' · not in use' }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if ($canManage)
                <button type="button" @click="$dispatch('open-item-form', @js($item->formData()))" class="{{ $button }}"><i class="fas fa-edit"></i> Edit</button>
                <button type="button" @click="$dispatch('open-move', @js($moveData('count')))" class="{{ $button }}"><i class="fas fa-clipboard-list"></i> Count</button>
            @endif
            <button type="button" @click="$dispatch('open-move', @js($moveData('damage')))" class="{{ $button }}"><i class="fas fa-trash-alt"></i> Write off</button>
            <button type="button" @click="$dispatch('open-move', @js($moveData('out')))" class="{{ $button }}"><i class="fas fa-sign-out-alt"></i> Issue</button>
            @if ($canReceive)
                <button type="button" @click="$dispatch('open-move', @js($moveData('in')))" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-primary/90"><i class="fas fa-sign-in-alt"></i> Receive</button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @include('partials.stat-card', ['label' => 'In Stock', 'value' => $item->qty(), 'icon' => 'fa-box', 'tone' => (float) $item->quantity <= 0 ? 'error' : ($item->is_low ? 'warning' : 'primary')])
        @include('partials.stat-card', ['label' => 'Reorder At', 'value' => $item->qty($item->reorder_level), 'icon' => 'fa-redo', 'tone' => 'gray'])
        @include('partials.stat-card', ['label' => 'Used, Last 30 Days', 'value' => $item->qty($usedLast30), 'icon' => 'fa-chart-line', 'tone' => 'info'])
        @include('partials.stat-card', ['label' => 'Value (avg ' . number_format((float) $item->unit_cost, 2) . ')', 'value' => $money($item->stock_value), 'icon' => 'fa-coins', 'tone' => 'success'])
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
            <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Movements</h3>
        </div>
        @include('inventory.partials.movements-table', ['movements' => $movements])
        @if ($movements->hasPages())
            <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">{{ $movements->links() }}</div>
        @endif
    </div>
</div>

@include('inventory.partials.move-modal')
@if ($canManage)
    @include('inventory.partials.item-modal')
@endif
@endsection
