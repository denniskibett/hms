@extends('layouts.app')

@php
    $inputClass = 'h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
@endphp

@section('content')
<div x-data="{ pageName: `Stock Movements` }">
    @include('partials.breadcrumb')
</div>

<div class="space-y-6">
    @include('inventory.partials.header', ['title' => 'Stock Movements', 'subtitle' => 'Everything received, issued, written off or counted', 'hideNew' => true])

    <form method="GET" action="{{ route('inventory.movements') }}" class="grid grid-cols-1 gap-3 rounded-2xl border border-gray-100 bg-white p-4 sm:grid-cols-2 lg:grid-cols-4 dark:border-gray-800 dark:bg-gray-900">
        <select name="type" class="{{ $inputClass }}" onchange="this.form.submit()">
            <option value="">All movements</option>
            @foreach (\App\Models\InventoryTransaction::TYPES as $value => $label)
                <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="department_id" class="{{ $inputClass }}" onchange="this.form.submit()">
            <option value="">All departments</option>
            @foreach ($departments as $department)
                <option value="{{ $department->id }}" @selected((int) ($filters['department_id'] ?? 0) === $department->id)>{{ $department->name }}</option>
            @endforeach
        </select>
        <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="{{ $inputClass }}" onchange="this.form.submit()">
        <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="{{ $inputClass }}" onchange="this.form.submit()">
    </form>

    <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
        @include('inventory.partials.movements-table', ['movements' => $movements, 'showItem' => true])
        @if ($movements->hasPages())
            <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">{{ $movements->links() }}</div>
        @endif
    </div>
</div>
@endsection
