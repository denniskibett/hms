@extends('layouts.app')

@php
    $dateFormat = 'D d M Y';
    $tabs = ['new' => 'New', 'converted' => 'Booked', 'declined' => 'Declined', 'all' => 'All'];
@endphp

@section('content')
<div x-data="{ pageName: `Website Requests` }">
    @include('partials.breadcrumb')
</div>

<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Website Requests</h2>
            <p class="text-gray-600 dark:text-gray-400">Stays asked for on the website. Turn each into a booking or decline it.</p>
        </div>
        <a href="{{ route('book') }}" target="_blank" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
            <i class="fas fa-external-link-alt"></i> Booking page
        </a>
    </div>

    <div class="flex gap-2 overflow-x-auto border-b border-gray-200 dark:border-gray-800">
        @foreach ($tabs as $value => $label)
            <a href="{{ route('booking-requests.index', ['status' => $value]) }}"
               class="-mb-px whitespace-nowrap border-b-2 px-4 py-2 text-sm font-medium {{ $status === $value ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400' }}">
                {{ $label }}
                @if ($value !== 'all' && ($counts[$value] ?? 0))<span class="ml-1 rounded-full bg-gray-100 px-2 text-xs dark:bg-gray-800">{{ $counts[$value] }}</span>@endif
            </a>
        @endforeach
    </div>

    <div class="space-y-4">
        @forelse ($requests as $item)
            <div x-data="{ declining: false }" class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="space-y-1 text-sm">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="text-base font-semibold text-gray-800 dark:text-white/90">{{ $item->name }}</p>
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $item->status_classes }}">{{ \App\Models\BookingRequest::STATUSES[$item->status] }}</span>
                            <span class="text-xs text-gray-500">{{ $item->reference }} · sent {{ $item->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-gray-700 dark:text-gray-300">
                            <a href="tel:{{ $item->phone }}" class="hover:text-primary"><i class="fas fa-phone mr-1 text-gray-400"></i>{{ $item->phone }}</a>
                            @if ($item->email)<a href="mailto:{{ $item->email }}" class="ml-3 hover:text-primary"><i class="fas fa-envelope mr-1 text-gray-400"></i>{{ $item->email }}</a>@endif
                        </p>
                        <p class="text-gray-800 dark:text-white/90">
                            {{ $item->arrival_date->format($dateFormat) }} → {{ $item->departure_date->format($dateFormat) }} ({{ $item->nights }} {{ Str::plural('night', $item->nights) }})
                            · {{ $item->rooms }} {{ Str::plural('room', $item->rooms) }}{{ $item->roomType ? ', ' . $item->roomType->name : '' }}
                            · {{ $item->adults }} {{ Str::plural('adult', $item->adults) }}{{ $item->children ? ', ' . $item->children . ' ' . Str::plural('child', $item->children) : '' }}
                            · {{ $item->meal_plan->label() }}
                        </p>
                        @if ($item->message)<p class="text-gray-600 dark:text-gray-400">"{{ $item->message }}"</p>@endif
                        @if ($item->status === 'converted' && $item->stay)
                            <p class="text-success-600 dark:text-success-500">Booked as <a href="{{ route('stays.show', $item->stay) }}" class="underline">booking #{{ $item->stay->id }}</a> by {{ $item->handler->name ?? '—' }}</p>
                        @elseif ($item->status === 'declined')
                            <p class="text-gray-500">Declined by {{ $item->handler->name ?? '—' }}: {{ $item->decline_reason }}</p>
                        @endif
                    </div>

                    @if ($item->status === 'new')
                        <div class="flex shrink-0 flex-wrap gap-2">
                            <form method="POST" action="{{ route('booking-requests.convert', $item) }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/90"><i class="fas fa-calendar-check"></i> Book it</button>
                            </form>
                            <button type="button" @click="declining = !declining" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Decline</button>
                        </div>
                    @endif
                </div>
                <form x-show="declining" x-cloak method="POST" action="{{ route('booking-requests.decline', $item) }}" class="mt-3 flex flex-wrap gap-2">
                    @csrf
                    <input type="text" name="reason" required maxlength="255" placeholder="Why? The guest gets this by SMS, e.g. fully booked those dates"
                           class="h-10 flex-1 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">
                    <button type="submit" class="rounded-lg bg-error-500 px-4 text-sm font-medium text-white hover:bg-error-600">Decline</button>
                </form>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-300 p-10 text-center text-gray-500 dark:border-gray-700 dark:text-gray-400">
                <i class="fas fa-inbox mb-3 text-4xl"></i>
                <p>{{ $status === 'new' ? 'No new requests.' : 'Nothing here.' }}</p>
            </div>
        @endforelse
    </div>

    @if ($requests->hasPages())
        <div>{{ $requests->links() }}</div>
    @endif
</div>
@endsection
