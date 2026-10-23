@extends('layouts.app')

@php
    $currency = SystemHelper::currencySymbol();
    $money = fn ($value) => $currency . ' ' . number_format((float) $value, 2);
    $isManager = auth()->user()->hasAnyRole(['admin', 'manager']);
@endphp

@section('content')
<div x-data="{ pageName: @js($facility->name) }">
    @include('partials.breadcrumb')
</div>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <a href="{{ route('facilities.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                <i class="fas fa-arrow-left"></i> Back to Facilities
            </a>
            <h2 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $facility->name }}</h2>
            <p class="text-gray-600 dark:text-gray-400">
                {{ $facility->capacity }} seats ·
                {{ (float) $facility->base_rate > 0 ? $money($facility->base_rate) . ' hire per day' : 'Hire included in packages' }} ·
                {{ $facility->status_label }}
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if ($isManager)
                <button type="button" @click="$dispatch('open-venue-form', @js($facility->formData()))"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                    <i class="fas fa-edit"></i> Edit Venue
                </button>
            @endif
            <a href="{{ route('events.index', ['new' => $facility->id]) }}"
               class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-primary/90">
                <i class="fas fa-calendar-plus"></i> Book Event
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Upcoming events</h3>
                </div>
                @include('events.partials.table', ['events' => $upcoming, 'hideVenue' => true, 'empty' => 'Nothing booked.'])
            </div>

            @if ($recent->isNotEmpty())
                <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                        <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Past &amp; cancelled</h3>
                    </div>
                    @include('events.partials.table', ['events' => $recent, 'hideVenue' => true])
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">About</h3>
                </div>
                <div class="space-y-4 p-5 text-sm">
                    @if ($facility->description)
                        <p class="whitespace-pre-line text-gray-700 dark:text-gray-300">{{ $facility->description }}</p>
                    @endif
                    @if ($facility->amenities)
                        <div class="flex flex-wrap gap-2">
                            @foreach ($facility->amenities as $amenity)
                                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ $amenity }}</span>
                            @endforeach
                        </div>
                    @endif
                    @unless ($facility->description || $facility->amenities)
                        <p class="text-gray-500 dark:text-gray-400">No description yet.</p>
                    @endunless
                </div>
            </div>

            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Packages</h3>
                </div>
                <ul class="divide-y divide-gray-100 text-sm dark:divide-gray-800">
                    @forelse ($packages as $package)
                        <li class="flex items-start justify-between gap-3 px-5 py-3">
                            <div>
                                <p class="font-medium text-gray-800 dark:text-white/90">{{ $package->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $package->basis_label }}</p>
                            </div>
                            <p class="whitespace-nowrap text-gray-800 dark:text-white/90">{{ $money($package->price) }}</p>
                        </li>
                    @empty
                        <li class="px-5 py-4 text-gray-500 dark:text-gray-400">No active packages.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

@if ($isManager)
    @include('facilities.partials.modal-form')
@endif
@endsection
