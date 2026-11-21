@extends('layouts.public')

@section('title', 'Welcome')
@section('header_on_dark', '1')

@php
    $money = fn ($v) => SystemHelper::currencySymbol() . ' ' . number_format((float) $v);
    $about = SystemHelper::aboutUs();
    $services = collect(SystemHelper::services())->filter(fn ($s) => ! empty($s['title']))->values();
    $phone = SystemHelper::contactPhone();
    $email = SystemHelper::contactEmail();
    $address = SystemHelper::address();
@endphp

@section('content')
    @component('public.partials.hero', [
        'image' => asset('images/site/exterior.jpg'),
        'alt' => SystemHelper::appName(),
        'eyebrow' => SystemHelper::slogan(),
        'title' => $about['subtitle'] ?? SystemHelper::appName(),
        'text' => SystemHelper::metaDescription() ?: ($about['extra'] ?? null),
        'tall' => true,
    ])
        <div class="mt-9 max-w-5xl rounded-xl bg-white p-4 shadow-2xl shadow-black/20 md:p-5" data-reveal="up" style="--delay:.8s">
            @include('public.partials.search-form')
        </div>
    @endcomponent


    {{-- Rooms --}}
    <section id="after-hero" class="relative mx-auto max-w-[1240px] px-4 pb-10 pt-24 md:px-6">
        <div class="blob -left-20 top-10 h-72 w-72 bg-amber-glow/30"></div>
        <div class="relative mb-10 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="site-eyebrow" data-reveal="left">Stay</p>
                <h2 class="site-h2" data-split>Rooms &amp; suites</h2>
            </div>
            <p class="max-w-md text-muted" data-reveal="right">Prices are per night{{ SystemHelper::pricesIncludeTax() ? ' and include taxes' : '' }}. Choose room only, bed &amp; breakfast, half board or full board when you book.</p>
        </div>
        <div class="relative grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4" data-stagger=".12" data-effect="tilt">
            @forelse ($roomTypes->take(4) as $i => ['type' => $type, 'from' => $from])
                @include('public.partials.room-card', compact('type', 'from', 'i'))
            @empty
                <p class="text-muted">Room details are coming soon. Call us to book.</p>
            @endforelse
        </div>
        @if ($roomTypes->count() > 4)
            <div class="mt-8 text-center" data-reveal="zoom"><a href="{{ route('stay') }}" class="site-btn-outline">See all rooms</a></div>
        @endif
    </section>

    {{-- Services grid --}}
    @if ($services->isNotEmpty())
        <section class="mx-auto max-w-[1240px] px-4 py-20 md:px-6">
            <p class="site-eyebrow text-center" data-reveal="down">Everything in one place</p>
            <h2 class="site-h2 text-center" data-split>What we offer</h2>
            <div class="mt-12 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3" data-stagger=".1" data-effect="flip">
                @foreach ($services as $service)
                    <div class="lift rounded-xl border border-line bg-white p-7">
                        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-cream text-xl text-clay"><i class="fa-solid fa-{{ ['bed', 'utensils', 'champagne-glasses', 'people-group', 'map-location-dot', 'martini-glass-citrus'][$loop->index % 6] }}"></i></span>
                        <h3 class="mt-4 text-lg font-semibold">{{ $service['title'] }}</h3>
                        <p class="mt-2 text-[15px] leading-relaxed text-muted">{{ $service['description'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Dining --}}
    <section class="mx-auto grid max-w-[1240px] grid-cols-1 items-center gap-12 px-4 py-16 md:px-6 lg:grid-cols-2">
        <img src="{{ asset('images/site/lobby.jpg') }}" alt="Inside {{ SystemHelper::appName() }}" class="h-[440px] w-full rounded-xl object-cover" data-reveal="clip" loading="lazy">
        <div>
            <p class="site-eyebrow" data-reveal="right">Eat &amp; drink</p>
            <h2 class="site-h2" data-split>Restaurant &amp; bar</h2>
            <p class="mt-4 text-lg leading-relaxed text-muted" data-reveal="right" style="--delay:.15s">Staying with us? Put meals, drinks and room service on your room bill and settle once at check-out.</p>
            <div class="mt-8 flex flex-wrap gap-3" data-reveal="up" style="--delay:.3s">
                <a href="{{ route('dining') }}" class="site-btn">See the menu</a>
                <a href="{{ route('contact', ['topic' => 'Restaurant table']) }}" class="site-btn-outline">Reserve a table</a>
            </div>
        </div>
    </section>

    {{-- Events --}}
    @if ($venues->isNotEmpty())
        <section class="relative overflow-hidden border-y border-line bg-white">
            <div class="blob -right-24 bottom-0 h-80 w-80 bg-clay/20"></div>
            <div class="relative mx-auto grid max-w-[1240px] grid-cols-1 items-center gap-12 px-4 py-20 md:px-6 lg:grid-cols-2">
                <div>
                    <p class="site-eyebrow" data-reveal="left">Meet &amp; celebrate</p>
                    <h2 class="site-h2" data-split>Conferences, weddings &amp; events</h2>
                    <p class="mt-4 text-lg leading-relaxed text-muted" data-reveal="left" style="--delay:.15s">{{ $venues->pluck('name')->join(', ', ' and ') }}. Rooms, meals and payments for your whole group on one invoice.</p>
                    <div class="mt-7 grid grid-cols-3 gap-3" data-stagger=".12" data-effect="zoom">
                        <div class="rounded-lg border border-line p-4"><div class="text-2xl font-bold" data-count="{{ (int) $venues->max('capacity') }}">0</div><div class="text-sm text-muted">guests in our largest venue</div></div>
                        <div class="rounded-lg border border-line p-4"><div class="text-2xl font-bold" data-count="{{ $venues->count() }}">0</div><div class="text-sm text-muted">{{ Str::plural('venue', $venues->count()) }}</div></div>
                        <div class="rounded-lg border border-line p-4"><div class="text-2xl font-bold" data-count="{{ $packages->count() }}">0</div><div class="text-sm text-muted">{{ Str::plural('package', $packages->count()) }}</div></div>
                    </div>
                    <a href="{{ route('events') }}" class="site-btn mt-8" data-reveal="up">Plan your event</a>
                </div>
                <img src="{{ asset('images/site/garden.jpg') }}" alt="The gardens at {{ SystemHelper::appName() }}" class="h-[460px] w-full rounded-xl object-cover" data-reveal="right" loading="lazy">
            </div>
        </section>
    @endif

    {{-- About and location --}}
    <section class="mx-auto grid max-w-[1240px] grid-cols-1 gap-12 px-4 py-20 md:px-6 lg:grid-cols-2">
        <div>
            <p class="site-eyebrow" data-reveal="left">{{ $about['title'] ?? 'About us' }}</p>
            <h2 class="site-h2" data-split>{{ $address ? 'Getting here' : ($about['subtitle'] ?? SystemHelper::appName()) }}</h2>
            <p class="mt-4 text-lg leading-relaxed text-muted" data-reveal="up">{{ $about['description'] ?? '' }}</p>
            <a href="{{ route('about') }}" class="mt-6 inline-block font-semibold text-clay-text" data-reveal="up">More about us <i class="fa-solid fa-arrow-right text-xs"></i></a>
        </div>
        @if ($address)
            <iframe title="Map to {{ SystemHelper::appName() }}" src="https://maps.google.com/maps?q={{ urlencode(SystemHelper::appName() . ', ' . $address) }}&output=embed" class="h-[360px] w-full rounded-xl border-0" loading="lazy" referrerpolicy="no-referrer-when-downgrade" data-reveal="blur"></iframe>
        @else
            <img src="{{ asset('images/site/garden.jpg') }}" alt="" class="h-[360px] w-full rounded-xl object-cover" data-reveal="blur" loading="lazy">
        @endif
    </section>

    {{-- Contact --}}
    <section class="border-y border-cream-line bg-cream">
        <div class="mx-auto grid max-w-[1240px] grid-cols-1 gap-12 px-4 py-20 md:px-6 lg:grid-cols-2">
            <div>
                <h2 class="site-h2" data-split>Talk to the front desk</h2>
                <p class="mt-4 text-lg leading-relaxed text-muted" data-reveal="left">Group bookings, company accounts and events.</p>
                <dl class="mt-6 grid gap-3 text-base" data-stagger=".1" data-effect="left">
                    @if ($phone)<div>Phone: <a href="tel:{{ $phone }}" class="font-semibold text-ink">{{ $phone }}</a></div>@endif
                    @if ($email)<div>Email: <a href="mailto:{{ $email }}" class="font-semibold text-clay-text">{{ $email }}</a></div>@endif
                    @if ($address)<div>Address: <span class="font-semibold">{{ $address }}</span></div>@endif
                </dl>
            </div>
            <div data-reveal="right">@include('public.partials.contact-form')</div>
        </div>
    </section>
@endsection
