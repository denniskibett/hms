@extends('layouts.public')

@section('title', 'Conferences & events')
@section('header_on_dark', '1')

@php $money = fn ($v) => SystemHelper::currencySymbol() . ' ' . number_format((float) $v); @endphp

@section('content')
    @include('public.partials.hero', [
        'image' => asset('images/site/garden.jpg'),
        'eyebrow' => 'Meet & celebrate',
        'title' => 'Conferences & events',
        'text' => 'Workshops, meetings, weddings and garden parties, with rooms and meals for your guests on one invoice.',
    ])

    @if ($venues->isNotEmpty())
        <section id="after-hero" class="mx-auto max-w-[1240px] px-4 pb-6 pt-16 md:px-6">
            <h2 class="site-h2 mb-8" data-split>Venues</h2>
            <div class="grid grid-cols-1 gap-5 md:grid-cols-3" data-stagger=".12" data-effect="tilt">
                @foreach ($venues as $venue)
                    <article class="lift flex flex-col gap-3 rounded-xl border border-line bg-white p-6">
                        <h3 class="text-xl font-semibold">{{ $venue->name }}</h3>
                        <p class="text-sm text-muted">Up to <strong class="text-2xl text-ink" data-count="{{ (int) $venue->capacity }}">0</strong> guests</p>
                        @if ($venue->description)<p class="text-[15px] leading-relaxed text-muted">{{ $venue->description }}</p>@endif
                        @if ($venue->amenities)
                            <ul class="mt-auto flex flex-wrap gap-1.5 pt-2">@foreach ((array) $venue->amenities as $a)<li class="rounded-full bg-cream px-2.5 py-1 text-xs font-medium text-clay-dark">{{ $a }}</li>@endforeach</ul>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @if ($packages->isNotEmpty())
        <section class="mx-auto max-w-[1240px] px-4 py-10 md:px-6">
            <h2 class="site-h2 mb-8" data-split>Packages</h2>
            <div class="grid grid-cols-1 gap-5 md:grid-cols-3" data-stagger=".1" data-effect="zoom">
                @foreach ($packages as $package)
                    <article class="lift flex flex-col gap-2 rounded-xl border border-line bg-white p-6">
                        <h3 class="text-lg font-semibold">{{ $package->name }}</h3>
                        @if ($package->inclusions)<p class="text-[15px] text-muted">{{ $package->inclusions }}</p>@endif
                        <div class="mt-auto pt-3 text-xl font-bold">{{ $money($package->price) }} <span class="text-[13px] font-normal text-muted">{{ $package->charge_basis === 'per_person' ? 'per person' : 'per event' }}</span></div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <section class="relative overflow-hidden border-t border-cream-line bg-cream">
        <div class="blob -left-24 top-0 h-80 w-80 bg-amber-glow/25"></div>
        <div class="relative mx-auto grid max-w-[1240px] grid-cols-1 gap-12 px-4 py-20 md:px-6 lg:grid-cols-2">
            <div>
                <h2 class="site-h2" data-split>Request a quote</h2>
                <p class="mt-4 text-lg leading-relaxed text-muted" data-reveal="left">Tell us the date, how many guests and whether they need rooms. We'll reply with a full quote.</p>
            </div>
            <div data-reveal="right">@include('public.partials.contact-form', ['topic' => 'Conference or event'])</div>
        </div>
    </section>
@endsection
