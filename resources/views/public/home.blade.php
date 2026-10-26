@extends('layouts.public')

@section('title', 'Welcome')

@php
    $money = fn ($v) => SystemHelper::currencySymbol() . ' ' . number_format((float) $v);
    $about = SystemHelper::aboutUs();
@endphp

@section('content')
    <!-- Hero -->
    <section class="relative">
        <img src="{{ asset('twh/img/hero/hero-1.jpg') }}" alt="" class="absolute inset-0 h-full w-full object-cover">
        <div class="absolute inset-0 bg-gray-900/55"></div>
        <div class="relative mx-auto max-w-7xl px-6 pb-16 pt-28 md:pt-40">
            <h1 class="max-w-2xl text-4xl font-bold text-white md:text-6xl">{{ SystemHelper::appName() }}</h1>
            <p class="mt-4 max-w-xl text-lg text-white/90">{{ SystemHelper::slogan() }}</p>
            <div class="mt-10 rounded-2xl bg-white p-5 shadow-xl md:p-6">
                @include('public.partials.search-form')
            </div>
        </div>
    </section>

    <!-- Rooms -->
    <section id="rooms" class="mx-auto max-w-7xl px-6 pt-20">
        <p class="text-sm font-semibold uppercase tracking-wider text-primary">Stay with us</p>
        <h2 class="mt-2 text-3xl font-bold text-gray-900">Our rooms</h2>
        <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($roomTypes as $i => ['type' => $type, 'from' => $from])
                <div class="overflow-hidden rounded-2xl border border-gray-100 shadow-sm">
                    <img src="{{ asset('twh/img/room/room-' . ($i % 6 + 1) . '.jpg') }}" alt="{{ $type->name }}" class="h-56 w-full object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-900">{{ $type->name }}</h3>
                        <p class="mt-1 text-sm text-gray-500">Sleeps {{ $type->capacity }} · {{ Str::headline($type->bed_type ?? 'double') }} bed</p>
                        @if ($type->amenities)
                            <p class="mt-3 text-sm text-gray-600">{{ implode(' · ', array_slice((array) $type->amenities, 0, 5)) }}</p>
                        @endif
                        <div class="mt-5 flex items-end justify-between">
                            <p><span class="text-xs text-gray-500">from</span><br><span class="text-2xl font-bold text-gray-900">{{ $money($from) }}</span> <span class="text-sm text-gray-500">/ night, B&amp;B</span></p>
                            <a href="{{ route('book') }}" class="rounded-lg border border-primary px-4 py-2 text-sm font-semibold text-primary hover:bg-primary hover:text-white">Book</a>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-gray-500">Room details coming soon. Call us to book.</p>
            @endforelse
        </div>
    </section>

    <!-- Conferences -->
    @if ($venues->isNotEmpty())
        <section class="mx-auto mt-20 max-w-7xl px-6">
            <div class="grid grid-cols-1 items-center gap-10 rounded-3xl bg-gray-50 p-8 md:p-12 lg:grid-cols-2">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wider text-primary">Meetings &amp; events</p>
                    <h2 class="mt-2 text-3xl font-bold text-gray-900">Conferences, workshops and celebrations</h2>
                    <p class="mt-4 text-gray-600">
                        {{ $venues->pluck('name')->join(', ', ' and ') }}, for up to {{ $venues->max('capacity') }} guests.
                        Packages include meals and conference essentials.
                    </p>
                    <a href="{{ route('contact') }}" class="mt-6 inline-block rounded-lg bg-primary px-6 py-3 text-sm font-semibold text-white hover:bg-primary/90">Ask about your event</a>
                </div>
                @if ($packages->isNotEmpty())
                    <ul class="divide-y divide-gray-200 rounded-2xl bg-white p-2 shadow-sm">
                        @foreach ($packages as $package)
                            <li class="flex items-center justify-between gap-4 px-4 py-4">
                                <span>
                                    <span class="font-medium text-gray-900">{{ $package->name }}</span>
                                    @if ($package->inclusions)<span class="block text-xs text-gray-500">{{ $package->inclusions }}</span>@endif
                                </span>
                                <span class="whitespace-nowrap text-right"><span class="font-semibold text-gray-900">{{ $money($package->price) }}</span><span class="block text-xs text-gray-500">per person</span></span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>
    @endif

    <!-- About -->
    <section class="mx-auto mt-20 grid max-w-7xl grid-cols-1 items-center gap-10 px-6 lg:grid-cols-2">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wider text-primary">{{ $about['title'] ?? 'About us' }}</p>
            <h2 class="mt-2 text-3xl font-bold text-gray-900">{{ $about['subtitle'] ?? SystemHelper::appName() }}</h2>
            <p class="mt-4 text-gray-600">{{ $about['description'] ?? '' }}</p>
            <a href="{{ route('about') }}" class="mt-6 inline-block font-semibold text-primary hover:underline">More about us →</a>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <img src="{{ asset('twh/img/about/about-1.jpg') }}" alt="" class="h-64 w-full rounded-2xl object-cover">
            <img src="{{ asset('twh/img/about/about-2.jpg') }}" alt="" class="mt-10 h-64 w-full rounded-2xl object-cover">
        </div>
    </section>
@endsection
