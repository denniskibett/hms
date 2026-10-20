@extends('layouts.app')

@php
    $inputClass = 'h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
    $dateFormat = 'D, ' . SystemHelper::dateFormat();
@endphp

@section('content')
    <div x-data="{ pageName: `Guests` }">
        @include('partials.breadcrumb')
    </div>

<div class="space-y-6"
     x-data
     x-on:guest-saved.window="$event.detail.created ? window.location.href = $event.detail.guest.url : window.location.reload()">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Guests</h2>
            <p class="text-gray-600 dark:text-gray-400">Guest register, contacts and stay history</p>
        </div>

        <button type="button" @click="$dispatch('open-guest-form')"
                class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs transition-colors hover:bg-primary/90">
            <i class="fas fa-user-plus"></i> New Guest
        </button>
    </div>

    <!-- Stats (all guests, not just this page) -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @include('partials.stat-card', ['label' => 'Total Guests', 'value' => $stats['total'], 'icon' => 'fa-users', 'tone' => 'primary', 'href' => route('guests.index')])
        @include('partials.stat-card', ['label' => 'In House', 'value' => $stats['in_house'], 'icon' => 'fa-bed', 'tone' => 'success', 'href' => route('guests.index', ['status' => 'in_house'])])
        @include('partials.stat-card', ['label' => 'Arriving Today', 'value' => $stats['arriving_today'], 'icon' => 'fa-plane-arrival', 'tone' => 'warning', 'href' => route('guests.index', ['status' => 'expected'])])
        @include('partials.stat-card', ['label' => 'New This Month', 'value' => $stats['new_this_month'], 'icon' => 'fa-user-plus', 'tone' => 'info'])
    </div>

    <!-- Filters -->
    <form method="GET" action="{{ route('guests.index') }}"
          class="grid grid-cols-1 gap-3 rounded-2xl border border-gray-100 bg-white p-4 sm:grid-cols-2 lg:grid-cols-5 dark:border-gray-800 dark:bg-gray-900">
        <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Name, phone, email or ID number"
               class="{{ $inputClass }} lg:col-span-2">

        <select name="status" class="{{ $inputClass }}" onchange="this.form.submit()">
            <option value="">All guests</option>
            <option value="in_house" @selected(($filters['status'] ?? '') === 'in_house')>In house</option>
            <option value="expected" @selected(($filters['status'] ?? '') === 'expected')>Expected (has reservation)</option>
        </select>

        <select name="nationality" class="{{ $inputClass }}" onchange="this.form.submit()">
            <option value="">All nationalities</option>
            @foreach ($nationalities as $nationality)
                <option value="{{ $nationality }}" @selected(($filters['nationality'] ?? '') === $nationality)>{{ $nationality }}</option>
            @endforeach
        </select>

        <div class="flex gap-2">
            <button type="submit" class="flex-1 rounded-lg bg-primary px-3 py-2 text-sm font-medium text-white hover:bg-primary/90">
                <i class="fas fa-search mr-1"></i> Search
            </button>
            @if (array_filter($filters))
                <a href="{{ route('guests.index') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                    Clear
                </a>
            @endif
        </div>
    </form>

    <!-- Guests Table -->
    <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="custom-scrollbar overflow-x-auto">
            <table class="min-w-full text-left text-sm text-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr class="whitespace-nowrap border-b border-gray-100 dark:border-gray-800">
                        @foreach (['Guest', 'Contact', 'ID', 'Nationality', 'Current / Next Stay', 'Stays', 'Actions'] as $heading)
                            <th class="px-5 py-4 text-sm font-medium text-gray-700 dark:text-gray-400">{{ $heading }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-white/[0.03]">
                    @forelse ($guests as $guest)
                        @php $stay = $guest->stays->first(); @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-5 py-4">
                                <a href="{{ route('guests.show', $guest) }}" class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary/10 text-sm font-semibold text-primary">
                                        {{ Str::upper(Str::substr($guest->name, 0, 1)) }}
                                    </div>
                                    <p class="font-medium text-gray-800 hover:text-primary dark:text-white/90">{{ $guest->name }}</p>
                                </a>
                            </td>
                            <td class="px-5 py-4">
                                <p class="text-gray-800 dark:text-white/90">{{ $guest->user->phone ?? '—' }}</p>
                                @if ($guest->user?->email)
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $guest->user->email }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if ($guest->id_number)
                                    <p class="text-gray-800 dark:text-white/90">{{ $guest->id_number }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $guest->id_type_label }}</p>
                                @else
                                    <span class="text-xs text-warning-600 dark:text-warning-500"><i class="fas fa-exclamation-circle"></i> Not captured</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-gray-700 dark:text-gray-300">{{ $guest->nationality ?? '—' }}</td>
                            <td class="px-5 py-4">
                                @if ($stay)
                                    <a href="{{ route('stays.show', $stay) }}" class="block">
                                        <div class="flex items-center gap-2">
                                            @include('partials.status-badge', ['status' => $stay->state])
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $stay->roomAllocations->map(fn ($a) => $a->room?->room_number)->filter()->implode(', ') ?: 'No room' }}
                                            </span>
                                        </div>
                                        <p class="mt-1 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                            {{ $stay->arrival_date->format($dateFormat) }} → {{ $stay->departure_date->format($dateFormat) }}
                                        </p>
                                    </a>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-gray-700 dark:text-gray-300">{{ $guest->stays_count }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2 whitespace-nowrap">
                                    <a href="{{ route('guests.show', $guest) }}"
                                       class="inline-flex items-center gap-1 rounded-lg bg-primary/10 px-2.5 py-1 text-xs font-medium text-primary hover:bg-primary/20">
                                        <i class="fas fa-eye text-xs"></i> View
                                    </a>
                                    <button type="button" @click="$dispatch('open-guest-form', @js($guest->formData()))"
                                            class="inline-flex items-center gap-1 rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300">
                                        <i class="fas fa-edit text-xs"></i> Edit
                                    </button>
                                    <a href="{{ route('stays.create', ['guest_id' => $guest->user_id]) }}"
                                       class="inline-flex items-center gap-1 rounded-lg bg-success-500/10 px-2.5 py-1 text-xs font-medium text-success-600 hover:bg-success-500/20 dark:text-success-500">
                                        <i class="fas fa-calendar-plus text-xs"></i> Book
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                                <i class="fas fa-users mb-3 text-4xl"></i>
                                @if (array_filter($filters))
                                    <p class="text-lg">No guests match these filters</p>
                                    <a href="{{ route('guests.index') }}" class="mt-2 inline-block text-sm text-primary hover:underline">Clear filters</a>
                                @else
                                    <p class="text-lg">No guests yet</p>
                                    <button type="button" @click="$dispatch('open-guest-form')"
                                            class="mt-4 inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/90">
                                        <i class="fas fa-user-plus"></i> Register First Guest
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($guests->hasPages())
            <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
                {{ $guests->links() }}
            </div>
        @endif
    </div>
</div>

@include('guests.partials.modal-form')
@endsection
