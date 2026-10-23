@extends('layouts.app')

@php
    $currency = SystemHelper::currencySymbol();
    $money = fn ($value) => $currency . ' ' . number_format((float) $value, 2);
    $isManager = auth()->user()->hasAnyRole(['admin', 'manager']);
    $statusClasses = [
        'available' => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500',
        'maintenance' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400',
        'unavailable' => 'bg-gray-100 text-gray-600 dark:bg-gray-500/15 dark:text-gray-400',
    ];
@endphp

@section('content')
<div x-data="{ pageName: `Facilities` }">
    @include('partials.breadcrumb')
</div>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Facilities</h2>
            <p class="text-gray-600 dark:text-gray-400">Conference venues and the packages sold with them</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('events.index') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                <i class="fas fa-calendar-alt"></i> Events
            </a>
            @if ($isManager)
                <button type="button" @click="$dispatch('open-venue-form')"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-primary/90">
                    <i class="fas fa-plus"></i> New Venue
                </button>
            @endif
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @include('partials.stat-card', ['label' => 'Venues', 'value' => $stats['venues'], 'icon' => 'fa-building', 'tone' => 'primary'])
        @include('partials.stat-card', ['label' => 'Events Today', 'value' => $stats['today'], 'icon' => 'fa-calendar-day', 'tone' => 'success', 'href' => route('events.index', ['when' => 'upcoming'])])
        @include('partials.stat-card', ['label' => 'Upcoming Events', 'value' => $stats['upcoming'], 'icon' => 'fa-calendar-alt', 'tone' => 'info', 'href' => route('events.index', ['when' => 'upcoming'])])
        @include('partials.stat-card', ['label' => 'Tentative', 'value' => $stats['tentative'], 'icon' => 'fa-hourglass-half', 'tone' => 'warning', 'href' => route('events.index', ['status' => 'tentative', 'when' => 'upcoming'])])
    </div>

    <!-- Venues -->
    <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
            <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Venues</h3>
        </div>
        <div class="custom-scrollbar overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr class="whitespace-nowrap border-b border-gray-100 text-gray-700 dark:border-gray-800 dark:text-gray-400">
                        <th class="px-5 py-3 font-medium">Venue</th>
                        <th class="px-5 py-3 font-medium">Seats</th>
                        <th class="px-5 py-3 text-right font-medium">Hire / day</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium">Upcoming events</th>
                        <th class="px-5 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($facilities as $facility)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-5 py-3">
                                <a href="{{ route('facilities.show', $facility) }}" class="font-medium text-gray-800 hover:text-primary dark:text-white/90">{{ $facility->name }}</a>
                                @if ($facility->amenities)
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ implode(' · ', $facility->amenities) }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-gray-700 dark:text-gray-300">{{ $facility->capacity }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-right text-gray-700 dark:text-gray-300">{{ (float) $facility->base_rate > 0 ? $money($facility->base_rate) : 'Included' }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusClasses[$facility->status] ?? '' }}">{{ $facility->status_label }}</span>
                            </td>
                            <td class="px-5 py-3 text-gray-700 dark:text-gray-300">{{ $facility->upcoming_count }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2 whitespace-nowrap">
                                    <a href="{{ route('facilities.show', $facility) }}" class="inline-flex items-center gap-1 rounded-lg bg-primary/10 px-2.5 py-1 text-xs font-medium text-primary hover:bg-primary/20">
                                        <i class="fas fa-eye text-xs"></i> View
                                    </a>
                                    @if ($isManager)
                                        <button type="button" @click="$dispatch('open-venue-form', @js($facility->formData()))"
                                                class="inline-flex items-center gap-1 rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300">
                                            <i class="fas fa-edit text-xs"></i> Edit
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                                <i class="fas fa-building mb-3 text-4xl"></i>
                                <p class="text-lg">No venues yet</p>
                                @if ($isManager)
                                    <button type="button" @click="$dispatch('open-venue-form')"
                                            class="mt-4 inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/90">
                                        <i class="fas fa-plus"></i> Add your first venue
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Packages -->
    <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-800">
            <div>
                <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Packages &amp; extras</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Prices include tax.</p>
            </div>
            @if ($isManager)
                <button type="button" @click="$dispatch('open-package-form')"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                    <i class="fas fa-plus"></i> New Package
                </button>
            @endif
        </div>
        <div class="custom-scrollbar overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr class="whitespace-nowrap border-b border-gray-100 text-gray-700 dark:border-gray-800 dark:text-gray-400">
                        <th class="px-5 py-3 font-medium">Package</th>
                        <th class="px-5 py-3 text-right font-medium">Price</th>
                        <th class="px-5 py-3 font-medium">Charged</th>
                        <th class="px-5 py-3 font-medium">Venue</th>
                        @if ($isManager)<th class="px-5 py-3 font-medium"></th>@endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($packages as $package)
                        <tr class="{{ $package->is_active ? '' : 'opacity-60' }}">
                            <td class="px-5 py-3">
                                <p class="font-medium text-gray-800 dark:text-white/90">
                                    {{ $package->name }}
                                    @unless ($package->is_active)
                                        <span class="ml-1 rounded bg-gray-100 px-1.5 py-0.5 text-[10px] uppercase text-gray-500 dark:bg-gray-800 dark:text-gray-400">inactive</span>
                                    @endunless
                                </p>
                                @if ($package->inclusions)
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ Str::limit($package->inclusions, 120) }}</p>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-right font-medium text-gray-800 dark:text-white/90">{{ $money($package->price) }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-gray-700 dark:text-gray-300">{{ $package->basis_label }}</td>
                            <td class="px-5 py-3 text-gray-700 dark:text-gray-300">{{ $package->facility->name ?? 'Any venue' }}</td>
                            @if ($isManager)
                                <td class="px-5 py-3 text-right">
                                    <button type="button" @click="$dispatch('open-package-form', @js($package->formData()))"
                                            class="inline-flex items-center gap-1 rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300">
                                        <i class="fas fa-edit text-xs"></i> Edit
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">No packages yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if ($isManager)
    @include('facilities.partials.modal-form')
    @include('facilities.partials.package-modal', ['venues' => $facilities])
@endif
@endsection
