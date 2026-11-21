{{-- Sign-in, sign-up and password pages: hotel photo on the left, form on the right. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex">
    <title>{{ $title ? $title . ' | ' : '' }}{{ SystemHelper::appName() }}</title>
    <link rel="icon" href="{{ SystemHelper::faviconUrl() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Cinzel:wght@600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/index.js'])
    <style>
        :root { --color-primary-rgb: {{ SystemHelper::rgb(SystemHelper::primaryColor(), '168 85 8') }}; }
    </style>
</head>
<body class="min-h-screen bg-gray-50 font-outfit text-gray-800 antialiased">
    <div class="grid min-h-screen lg:grid-cols-2">
        {{-- Photo side --}}
        <aside class="relative hidden overflow-hidden lg:block" aria-hidden="true">
            <img src="{{ asset('images/site/exterior.jpg') }}" alt="" class="absolute inset-0 h-full w-full object-cover">
            <div class="absolute inset-0" style="background: linear-gradient(180deg, rgb(58 32 8 / .15) 0%, rgb(58 32 8 / .65) 100%)"></div>
            <div class="relative flex h-full flex-col justify-between p-12">
                <a href="{{ route('home') }}" class="inline-flex w-fit items-center gap-2 rounded-full bg-white/90 px-4 py-2 text-sm font-medium text-gray-800 transition hover:bg-white">
                    <i class="fa-solid fa-arrow-left text-xs"></i> Back to the website
                </a>
                <div class="max-w-md text-white">
                    <p class="font-[Cinzel] text-4xl font-bold leading-tight">{{ SystemHelper::appName() }}</p>
                    @if (mb_strlen((string) SystemHelper::slogan()) <= 140)
                        <p class="mt-3 text-lg text-white/90">{{ SystemHelper::slogan() }}</p>
                    @endif
                </div>
            </div>
        </aside>

        {{-- Form side --}}
        <main class="flex flex-col items-center justify-center px-5 py-10 sm:px-10">
            <div class="w-full max-w-md">
                <a href="{{ route('home') }}" class="mb-8 flex justify-center" aria-label="{{ SystemHelper::appName() }} home">
                    @include('public.partials.logo', ['class' => 'h-24 w-24'])
                </a>
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                    @if ($title)<h1 class="text-2xl font-semibold text-gray-900">{{ $title }}</h1>@endif
                    @if ($subtitle)<p class="mt-1 text-sm text-gray-500">{{ $subtitle }}</p>@endif
                    <div class="{{ $title ? 'mt-6' : '' }}">{{ $slot }}</div>
                </div>
                <a href="{{ route('home') }}" class="mt-6 block text-center text-sm text-gray-500 hover:text-brand-600 lg:hidden"><i class="fa-solid fa-arrow-left text-xs"></i> Back to the website</a>
                <p class="mt-6 text-center text-xs text-gray-400">© {{ now()->year }} {{ SystemHelper::appName() }} · Built and managed by IMAGINE-NATION AGENCY LTD</p>
            </div>
        </main>
    </div>
</body>
</html>
