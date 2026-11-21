@extends('layouts.app')

@section('content')
<div x-data="{ pageName: `New Booking` }">
    @include('partials.breadcrumb')
</div>

<div class="space-y-6">
    <div>
        <a href="{{ route('stays.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
            <i class="fas fa-arrow-left"></i> Back to Bookings
        </a>
        <h2 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">New Booking</h2>
        <p class="text-gray-600 dark:text-gray-400">Choose the guest, dates and rooms. Prices come from the rate list; type a rate to override it.</p>
    </div>

    @if ($bookingRequest ?? null)
        <div class="rounded-lg border border-info-200 bg-info-50 px-4 py-3 text-sm text-info-700 dark:border-info-500/30 dark:bg-info-500/10 dark:text-info-400">
            <p class="font-medium"><i class="fas fa-globe mr-1"></i> From website request {{ $bookingRequest->reference }}: {{ $bookingRequest->name }}, {{ $bookingRequest->phone }}{{ $bookingRequest->email ? ', ' . $bookingRequest->email : '' }}</p>
            <p>
                {{ $bookingRequest->rooms }} {{ Str::plural('room', $bookingRequest->rooms) }}{{ $bookingRequest->roomType ? ' (' . $bookingRequest->roomType->name . ')' : '' }},
                {{ $bookingRequest->adults }} {{ Str::plural('adult', $bookingRequest->adults) }}{{ $bookingRequest->children ? ', ' . $bookingRequest->children . ' ' . Str::plural('child', $bookingRequest->children) : '' }}.
                Free rooms of that type are filled in; check them, then save.
            </p>
        </div>
    @endif

    @include('stays.partials.booking-form')
</div>
@endsection
