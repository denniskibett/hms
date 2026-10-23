@extends('layouts.app')

@php
    $inputClass = 'h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
    // facilities.show links here with ?new={venue} to open the booking form
    $openFor = request()->integer('new') ?: null;
@endphp

@section('content')
<div x-data="{ pageName: `Events` }">
    @include('partials.breadcrumb')
</div>

<div class="space-y-6" x-data x-init="@js($openFor) && $nextTick(() => $dispatch('open-event-form', { facility_id: @js($openFor) }))">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Conferences &amp; Events</h2>
            <p class="text-gray-600 dark:text-gray-400">Venue bookings, packages and event billing</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('facilities.index') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                <i class="fas fa-building"></i> Venues &amp; Packages
            </a>
            <button type="button" @click="$dispatch('open-event-form')"
                    class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-primary/90">
                <i class="fas fa-calendar-plus"></i> Book Event
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @include('partials.stat-card', ['label' => 'Running Today', 'value' => $stats['today'], 'icon' => 'fa-calendar-day', 'tone' => 'success'])
        @include('partials.stat-card', ['label' => 'Upcoming', 'value' => $stats['upcoming'], 'icon' => 'fa-calendar-alt', 'tone' => 'primary', 'href' => route('events.index', ['when' => 'upcoming'])])
        @include('partials.stat-card', ['label' => 'Tentative', 'value' => $stats['tentative'], 'icon' => 'fa-hourglass-half', 'tone' => 'warning', 'href' => route('events.index', ['status' => 'tentative', 'when' => 'upcoming'])])
        @include('partials.stat-card', ['label' => 'Confirmed This Month', 'value' => $stats['this_month'], 'icon' => 'fa-check-circle', 'tone' => 'info'])
    </div>

    <!-- Filters -->
    <form method="GET" action="{{ route('events.index') }}"
          class="grid grid-cols-1 gap-3 rounded-2xl border border-gray-100 bg-white p-4 sm:grid-cols-2 lg:grid-cols-5 dark:border-gray-800 dark:bg-gray-900">
        <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Event, client or phone" class="{{ $inputClass }} lg:col-span-2">

        <select name="when" class="{{ $inputClass }}" onchange="this.form.submit()">
            <option value="">All dates</option>
            <option value="upcoming" @selected(($filters['when'] ?? '') === 'upcoming')>Today &amp; upcoming</option>
            <option value="past" @selected(($filters['when'] ?? '') === 'past')>Past</option>
        </select>

        <select name="status" class="{{ $inputClass }}" onchange="this.form.submit()">
            <option value="">All statuses</option>
            @foreach (\App\Enums\FacilityBookingStatus::options() as $value => $label)
                <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>

        <div class="flex gap-2">
            <select name="facility_id" class="{{ $inputClass }}" onchange="this.form.submit()">
                <option value="">All venues</option>
                @foreach ($venues as $venue)
                    <option value="{{ $venue->id }}" @selected((int) ($filters['facility_id'] ?? 0) === $venue->id)>{{ $venue->name }}</option>
                @endforeach
            </select>
            @if (array_filter($filters))
                <a href="{{ route('events.index') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Clear</a>
            @endif
        </div>
    </form>

    <!-- Events -->
    <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
        @include('events.partials.table', ['events' => $events, 'empty' => array_filter($filters) ? 'No events match these filters' : 'No events booked yet'])

        @if ($events->hasPages())
            <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
                {{ $events->links() }}
            </div>
        @endif
    </div>
</div>

@include('events.partials.modal-form')
@endsection
