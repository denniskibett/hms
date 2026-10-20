@extends('layouts.app')

@php
    $inputClass = 'h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
    $activeFilters = collect($filters)->except('tab')->filter();
@endphp

@section('content')
<div x-data="{ pageName: `Bookings` }">
    @include('partials.breadcrumb')
</div>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Bookings</h2>
            <p class="text-gray-600 dark:text-gray-400">Reservations, arrivals, in-house guests and departures</p>
        </div>
        <a href="{{ route('stays.create') }}"
           class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-primary/90">
            <i class="fas fa-plus"></i> New Booking
        </a>
    </div>

    <!-- Front-desk tabs -->
    <div class="flex flex-wrap gap-2">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('stays.index', array_filter(['tab' => $key === 'all' ? null : $key] + $activeFilters->all())) }}"
               @class([
                   'inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition',
                   'bg-primary text-white' => $tab === $key,
                   'border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/[0.03]' => $tab !== $key,
               ])>
                {{ $label }}
                @if (! is_null($tabCounts[$key]))
                    <span @class([
                        'rounded-full px-2 text-xs',
                        'bg-white/20' => $tab === $key,
                        'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' => $tab !== $key,
                    ])>{{ $tabCounts[$key] }}</span>
                @endif
            </a>
        @endforeach
    </div>

    <!-- Filters -->
    <form method="GET" action="{{ route('stays.index') }}"
          class="grid grid-cols-1 gap-3 rounded-2xl border border-gray-100 bg-white p-4 sm:grid-cols-2 lg:grid-cols-6 dark:border-gray-800 dark:bg-gray-900">
        @if ($tab !== 'all')
            <input type="hidden" name="tab" value="{{ $tab }}">
        @endif

        <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Guest, phone, room or #booking"
               class="{{ $inputClass }} lg:col-span-2">

        <select name="status" class="{{ $inputClass }}" onchange="this.form.submit()">
            <option value="">All statuses</option>
            @foreach (\App\Enums\StayStatus::options() as $value => $label)
                <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>

        <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" title="Staying on or after" class="{{ $inputClass }}">
        <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" title="Staying before" class="{{ $inputClass }}">

        <div class="flex gap-2">
            <button type="submit" class="flex-1 rounded-lg bg-primary px-3 py-2 text-sm font-medium text-white hover:bg-primary/90">
                <i class="fas fa-search mr-1"></i> Search
            </button>
            @if ($activeFilters->isNotEmpty())
                <a href="{{ route('stays.index', $tab === 'all' ? [] : ['tab' => $tab]) }}"
                   class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                    Clear
                </a>
            @endif
        </div>
    </form>

    <!-- Bookings -->
    <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
        @include('stays.partials.table', [
            'stays' => $stays,
            'empty' => $activeFilters->isNotEmpty() ? 'No bookings match these filters' : 'No bookings here',
        ])

        @if ($stays->hasPages())
            <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
                {{ $stays->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
