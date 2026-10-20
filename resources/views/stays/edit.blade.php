@extends('layouts.app')

@section('content')
<div x-data="{ pageName: @js('Edit Booking #' . $stay->id) }">
    @include('partials.breadcrumb')
</div>

<div class="space-y-6">
    <div>
        <a href="{{ route('stays.show', $stay) }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
            <i class="fas fa-arrow-left"></i> Back to Booking #{{ $stay->id }}
        </a>
        <div class="mt-2 flex items-center gap-3">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Edit Booking #{{ $stay->id }}</h2>
            @include('partials.status-badge', ['status' => $stay->state])
        </div>
        <p class="text-gray-600 dark:text-gray-400">Rooms kept with the same guests and meal plan keep their agreed rate.</p>
    </div>

    @include('stays.partials.booking-form')
</div>
@endsection
