<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', SystemHelper::appName()) · {{ SystemHelper::appName() }}</title>
    <meta name="description" content="{{ SystemHelper::metaDescription() ?: SystemHelper::slogan() }}">
    <link rel="icon" href="{{ SystemHelper::faviconUrl() }}">

    @vite(['resources/css/app.css', 'resources/js/index.js'])

    <style>
        :root {
            --color-primary-rgb: {{ SystemHelper::rgb(SystemHelper::primaryColor(), '58 87 232') }};
            --color-secondary-rgb: {{ SystemHelper::rgb(SystemHelper::secondaryColor(), '8 177 186') }};
        }
    </style>
</head>
@php
    $nav = [
        'home' => 'Home',
        'book' => 'Rooms & Booking',
        'about' => 'About',
        'contact' => 'Contact',
    ];
    $phone = SystemHelper::contactPhone();
    $email = SystemHelper::contactEmail();
@endphp
<body class="bg-white font-outfit text-gray-700 antialiased" x-data="{ menu: false }">
    <!-- Top bar -->
    <div class="hidden bg-gray-900 text-sm text-gray-300 md:block">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-2">
            <div class="flex gap-6">
                @if ($phone)<a href="tel:{{ $phone }}" class="hover:text-white"><i class="fas fa-phone mr-2"></i>{{ $phone }}</a>@endif
                @if ($email)<a href="mailto:{{ $email }}" class="hover:text-white"><i class="fas fa-envelope mr-2"></i>{{ $email }}</a>@endif
            </div>
            <a href="{{ route('login') }}" class="hover:text-white"><i class="fas fa-user mr-2"></i>Staff &amp; guest sign in</a>
        </div>
    </div>

    <!-- Header -->
    <header class="sticky top-0 z-40 border-b border-gray-100 bg-white/95 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <img src="{{ SystemHelper::logoUrl() }}" alt="{{ SystemHelper::appName() }}" class="h-10 w-auto">
            </a>
            <nav class="hidden items-center gap-8 lg:flex">
                @foreach ($nav as $route => $label)
                    <a href="{{ route($route) }}" class="text-sm font-medium {{ request()->routeIs($route) ? 'text-primary' : 'text-gray-700 hover:text-primary' }}">{{ $label }}</a>
                @endforeach
                <a href="{{ route('book') }}" class="rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary/90">Book a Stay</a>
            </nav>
            <button type="button" @click="menu = !menu" class="text-2xl text-gray-700 lg:hidden" aria-label="Menu"><i class="fas" :class="menu ? 'fa-times' : 'fa-bars'"></i></button>
        </div>
        <nav x-show="menu" x-cloak x-transition class="border-t border-gray-100 px-6 py-4 lg:hidden">
            @foreach ($nav as $route => $label)
                <a href="{{ route($route) }}" class="block py-2 font-medium {{ request()->routeIs($route) ? 'text-primary' : 'text-gray-700' }}">{{ $label }}</a>
            @endforeach
            <a href="{{ route('login') }}" class="block py-2 text-gray-500">Sign in</a>
            <a href="{{ route('book') }}" class="mt-2 block rounded-lg bg-primary px-5 py-3 text-center font-semibold text-white">Book a Stay</a>
        </nav>
    </header>

    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="mt-20 bg-gray-900 text-gray-400">
        <div class="mx-auto grid max-w-7xl grid-cols-1 gap-10 px-6 py-14 md:grid-cols-3">
            <div>
                <p class="text-lg font-semibold text-white">{{ SystemHelper::appName() }}</p>
                <p class="mt-2 text-sm">{{ SystemHelper::slogan() }}</p>
                <div class="mt-4 flex gap-4 text-lg">
                    @foreach (SystemHelper::socials() as $network => $url)
                        @continue(! is_string($url) || $url === '')
                        <a href="{{ $url }}" target="_blank" rel="noopener" class="hover:text-white" aria-label="{{ ucfirst($network) }}"><i class="fab fa-{{ $network }}"></i></a>
                    @endforeach
                </div>
            </div>
            <div class="text-sm">
                <p class="mb-3 font-semibold text-white">Contact</p>
                @if (SystemHelper::address())<p class="mb-2"><i class="fas fa-map-marker-alt mr-2"></i>{{ SystemHelper::address() }}</p>@endif
                @if ($phone)<p class="mb-2"><i class="fas fa-phone mr-2"></i><a href="tel:{{ $phone }}" class="hover:text-white">{{ $phone }}</a></p>@endif
                @if ($email)<p><i class="fas fa-envelope mr-2"></i><a href="mailto:{{ $email }}" class="hover:text-white">{{ $email }}</a></p>@endif
            </div>
            <div class="text-sm">
                <p class="mb-3 font-semibold text-white">Visit</p>
                @foreach ($nav as $route => $label)
                    <a href="{{ route($route) }}" class="mb-2 block hover:text-white">{{ $label }}</a>
                @endforeach
            </div>
        </div>
        <p class="border-t border-gray-800 py-5 text-center text-xs">© {{ now()->year }} {{ SystemHelper::appName() }}. Prices in {{ SystemHelper::currencySymbol() }}, including VAT and Tourism Levy.</p>
    </footer>
</body>
</html>