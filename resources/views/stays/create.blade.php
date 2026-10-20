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

    @include('stays.partials.booking-form')
</div>
@endsection
