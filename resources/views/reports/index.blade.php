@extends('layouts.app')

@section('content')
    <div x-data="{ pageName: `Reports` }">
        @include('partials.breadcrumb')
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white px-5 py-16 text-center dark:border-gray-800 dark:bg-gray-900">
        <i class="fas fa-chart-line mb-4 text-4xl text-gray-400"></i>
        <h2 class="text-lg font-medium text-gray-800 dark:text-white/90">Reports are coming soon</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">This section is being rebuilt. Booking and payment details are on each stay's folio for now.</p>
        <a href="{{ route('stays.index') }}" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/90">
            <i class="fas fa-calendar-check"></i> Go to Bookings
        </a>
    </div>
@endsection
