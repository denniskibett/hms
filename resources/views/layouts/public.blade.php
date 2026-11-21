<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', SystemHelper::appName()) · {{ SystemHelper::appName() }}</title>
    <meta name="description" content="{{ SystemHelper::metaDescription() ?: SystemHelper::slogan() }}">
    <link rel="icon" href="{{ SystemHelper::faviconUrl() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/index.js', 'resources/js/site.js'])
</head>
@php
    $nav = [
        'stay' => 'Rooms',
        'dining' => 'Dining & bar',
        'events' => 'Events',
        'about' => 'About',
        'contact' => 'Contact',
    ];
    $phone = SystemHelper::contactPhone();
    $email = SystemHelper::contactEmail();
    // Pages that open with a photo banner start with a white header text.
    $onDark = trim($__env->yieldContent('header_on_dark')) === '1';
@endphp
<body class="bg-sand font-inter text-ink antialiased" x-data="{ menu: false }">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[70] focus:rounded-md focus:bg-white focus:px-4 focus:py-2">Skip to content</a>
    <div class="site-progress" aria-hidden="true"></div>

    <header class="site-header fixed inset-x-0 top-0 z-50 {{ $onDark ? 'on-dark' : '' }}" :class="menu && 'is-solid'">
        <div class="mx-auto flex max-w-[1240px] items-center justify-between gap-4 px-4 py-3 md:px-6">
            <a href="{{ route('home') }}" class="flex shrink-0 items-center" aria-label="{{ SystemHelper::appName() }} home">
                <span class="transition hover:scale-105">@include('public.partials.logo', ['class' => 'h-16 w-16 md:h-[72px] md:w-[72px]'])</span>
            </a>
            <nav aria-label="Main" class="site-nav hidden items-center gap-7 text-[15px] font-medium lg:flex">
                @foreach ($nav as $route => $label)
                    <a href="{{ route($route) }}" @if (request()->routeIs($route . '*')) aria-current="page" @endif class="py-2 text-ink">{{ $label }}</a>
                @endforeach
            </nav>
            <div class="hidden items-center gap-3 lg:flex">
                <a href="{{ route('login') }}" class="px-2 py-3 text-[15px] font-medium">Sign in</a>
                <a href="{{ route('book') }}" class="site-btn !py-3 text-[15px]">Book a room</a>
            </div>
            <button type="button" @click="menu = !menu" class="flex h-11 w-11 items-center justify-center rounded-md text-2xl lg:hidden" :aria-expanded="menu" aria-label="Menu">
                <i class="fa-solid" :class="menu ? 'fa-xmark' : 'fa-bars'"></i>
            </button>
        </div>
        <nav x-show="menu" x-cloak x-transition.origin.top class="border-t border-line bg-white px-4 py-4 text-ink lg:hidden" aria-label="Mobile">
            @foreach ($nav as $route => $label)
                <a href="{{ route($route) }}" class="block py-3 text-lg font-medium {{ request()->routeIs($route . '*') ? 'text-clay' : '' }}">{{ $label }}</a>
            @endforeach
            <a href="{{ route('login') }}" class="block py-3 text-muted">Sign in</a>
            <a href="{{ route('book') }}" class="site-btn mt-2 w-full">Book a room</a>
        </nav>
    </header>

    <main id="main" class="{{ $onDark ? '' : 'pt-24' }}">
        @yield('content')
    </main>

    <footer class="relative overflow-hidden border-t border-line bg-white text-sm text-muted">
        <div class="mx-auto grid max-w-[1240px] grid-cols-1 gap-10 px-4 py-14 md:grid-cols-4 md:px-6" data-stagger=".1">
            <div class="md:col-span-2">
                @include('public.partials.logo', ['class' => 'h-24 w-24'])
                <p class="mt-3 max-w-sm">{{ SystemHelper::slogan() }}</p>
                <div class="mt-4 flex gap-4 text-lg">
                    @foreach (SystemHelper::socials() as $network => $url)
                        @continue(! is_string($url) || $url === '')
                        <a href="{{ $url }}" target="_blank" rel="noopener" class="text-muted transition hover:-translate-y-0.5 hover:text-clay" aria-label="{{ ucfirst($network) }}"><i class="fa-brands fa-{{ $network }}"></i></a>
                    @endforeach
                </div>
            </div>
            <div>
                <p class="mb-3 font-semibold text-ink">Contact</p>
                @if (SystemHelper::address())<p class="mb-2">{{ SystemHelper::address() }}</p>@endif
                @if ($phone)<p class="mb-2"><a href="tel:{{ $phone }}" class="text-muted hover:text-clay">{{ $phone }}</a></p>@endif
                @if ($email)<p><a href="mailto:{{ $email }}" class="text-muted hover:text-clay">{{ $email }}</a></p>@endif
            </div>
            <div>
                <p class="mb-3 font-semibold text-ink">Visit</p>
                @foreach ($nav as $route => $label)
                    <a href="{{ route($route) }}" class="mb-2 block text-muted hover:text-clay">{{ $label }}</a>
                @endforeach
            </div>
        </div>
        <div class="border-t border-line">
            <div class="mx-auto flex max-w-[1240px] flex-wrap justify-between gap-3 px-4 py-5 text-xs md:px-6">
                <span>© {{ now()->year }} {{ SystemHelper::appName() }}. Prices in {{ SystemHelper::currencyCode() }}{{ SystemHelper::pricesIncludeTax() ? ', including taxes' : '' }}.</span>
                <span>Payments by Sharet Pay</span>
            </div>
        </div>
    </footer>
</body>
</html>
