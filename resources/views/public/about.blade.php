@extends('layouts.public')

@section('title', 'About')

@php
    $about = SystemHelper::aboutUs();
    $money = fn ($v) => SystemHelper::currencySymbol() . ' ' . number_format((float) $v);
@endphp

@section('content')
<section class="mx-auto grid max-w-7xl grid-cols-1 items-center gap-12 px-6 py-16 lg:grid-cols-2">
    <div>
        <p class="text-sm font-semibold uppercase tracking-wider text-primary">{{ $about['title'] ?? 'About us' }}</p>
        <h1 class="mt-2 text-4xl font-bold text-gray-900">{{ $about['subtitle'] ?? SystemHelper::appName() }}</h1>
        <p class="mt-6 text-gray-600">{{ $about['description'] ?? '' }}</p>
        @if (! empty($about['extra']))<p class="mt-4 text-gray-600">{{ $about['extra'] }}</p>@endif
        <a href="{{ route('book') }}" class="mt-8 inline-block rounded-lg bg-primary px-6 py-3 font-semibold text-white hover:bg-primary/90">Book a stay</a>
    </div>
    <div class="grid grid-cols-2 gap-4">
        <img src="{{ asset('twh/img/about/about-p1.jpg') }}" alt="" class="h-72 w-full rounded-2xl object-cover">
        <img src="{{ asset('twh/img/about/about-p2.jpg') }}" alt="" class="mt-12 h-72 w-full rounded-2xl object-cover">
    </div>
</section>

@if ($roomTypes->isNotEmpty())
    <section class="mx-auto max-w-7xl px-6">
        <h2 class="text-2xl font-bold text-gray-900">Rooms</h2>
        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
            @foreach ($roomTypes as ['type' => $type, 'from' => $from])
                <div class="rounded-2xl border border-gray-100 p-5">
                    <p class="font-semibold text-gray-900">{{ $type->name }}</p>
                    <p class="text-sm text-gray-500">Sleeps {{ $type->capacity }} · from {{ $money($from) }} a night, B&amp;B</p>
                </div>
            @endforeach
        </div>
    </section>
@endif
@endsection
