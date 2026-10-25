@extends('layouts.app')

@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
@endphp

@section('content')
<div class="space-y-8">
    <div>
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ $greeting }}, {{ Str::before($user->name, ' ') }}</h2>
        <p class="text-gray-600 dark:text-gray-400">{{ now()->format('l, j F Y') }}</p>
    </div>

    @forelse ($sections as $name => $data)
        @include('dashboard.' . $name, ['data' => $data])
    @empty
        <div class="rounded-2xl border border-gray-100 bg-white p-10 text-center text-gray-500 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400">
            <i class="fas fa-user-lock mb-3 text-4xl"></i>
            <p>Your account has no role yet. Ask an administrator to give you access.</p>
        </div>
    @endforelse
</div>
@endsection
