@extends('layouts.public')

@section('title', 'About')
@section('header_on_dark', '1')

@php $about = SystemHelper::aboutUs(); @endphp

@section('content')
    @include('public.partials.hero', [
        'image' => asset('images/site/exterior.jpg'),
        'eyebrow' => $about['title'] ?? 'About us',
        'title' => $about['subtitle'] ?? SystemHelper::appName(),
    ])

    <section id="after-hero" class="mx-auto grid max-w-[1240px] grid-cols-1 items-center gap-12 px-4 py-20 md:px-6 lg:grid-cols-2">
        <div>
            <p class="text-lg leading-relaxed text-muted" data-reveal="left">{{ $about['description'] ?? '' }}</p>
            @if (! empty($about['extra']))<p class="mt-4 text-lg leading-relaxed text-muted" data-reveal="left" style="--delay:.15s">{{ $about['extra'] }}</p>@endif
            <a href="{{ route('book') }}" class="site-btn mt-8" data-reveal="up">Book a room</a>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <img src="{{ asset('images/site/lobby.jpg') }}" alt="The lounge" class="h-72 w-full rounded-xl object-cover" data-reveal="down" loading="lazy">
            <img src="{{ asset('images/site/garden.jpg') }}" alt="The gardens" class="mt-12 h-72 w-full rounded-xl object-cover" data-reveal="up" loading="lazy">
        </div>
    </section>

    @if ($roomTypes->isNotEmpty())
        <section class="mx-auto max-w-[1240px] px-4 pb-24 md:px-6">
            <h2 class="site-h2 mb-8" data-split>Our rooms</h2>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4" data-stagger=".1" data-effect="up">
                @foreach ($roomTypes->take(4) as $i => ['type' => $type, 'from' => $from])
                    @include('public.partials.room-card', compact('type', 'from', 'i'))
                @endforeach
            </div>
        </section>
    @endif
@endsection
