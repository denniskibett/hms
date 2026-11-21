<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ SystemHelper::appName() }} | {{ ucfirst(request()->segment(1) ?? 'Dashboard') }}</title>

    {{-- Dynamic Meta --}}
    @if(SystemHelper::metaDescription())
        <meta name="description" content="{{ SystemHelper::metaDescription() }}">
    @endif

    @if(SystemHelper::metaKeywords())
        <meta name="keywords" content="{{ SystemHelper::metaKeywords() }}">
    @endif

    {{-- Dynamic Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ SystemHelper::faviconUrl() }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ SystemHelper::faviconUrl() }}">
    <link rel="apple-touch-icon" href="{{ SystemHelper::faviconUrl() }}">

    {{-- Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">

   
    {{-- Vite --}}
    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/js/index.js'
    ])


    {{-- Theme colours from System Settings as "r g b", so Tailwind opacity modifiers (bg-primary/10) work --}}
    <style>
        :root {
            --color-primary-rgb: {{ SystemHelper::rgb(SystemHelper::primaryColor(), '58 87 232') }};
            --color-secondary-rgb: {{ SystemHelper::rgb(SystemHelper::secondaryColor(), '8 177 186') }};
        }
    </style>

    {{-- Unsaved form drafts: forms keep what was typed if the page reloads or a dialog
         is closed, until it is saved. Kept in this browser only, per user; never passwords. --}}
    <script>
        window.formDrafts = {
            prefix: @js('hms:draft:' . auth()->id() . ':'),
            maxAgeDays: 7,
            key(form, id) { return this.prefix + form + ':' + (id || 'new'); },
            load(key) {
                try {
                    const saved = JSON.parse(localStorage.getItem(key) || 'null');
                    if (!saved || Date.now() - saved.at > this.maxAgeDays * 864e5) return null;
                    return saved.data;
                } catch (e) { return null; }
            },
            save(key, data) {
                try {
                    const clean = JSON.parse(JSON.stringify(data, (k, v) => /password/i.test(k) ? undefined : v));
                    localStorage.setItem(key, JSON.stringify({ at: Date.now(), data: clean }));
                } catch (e) { /* storage full or blocked: drafts are a convenience */ }
            },
            clear(key) { try { localStorage.removeItem(key); } catch (e) {} },
        };
    </script>
</head>

<body class="font-outfit antialiased bg-gray-100"
      x-data="{
          page: '{{ request()->segment(1) ?? 'dashboard' }}',
          loaded: true,
          darkMode: JSON.parse(localStorage.getItem('darkMode')) || false,
          stickyMenu: false,
          sidebarToggle: false,
          scrollTop: false,
          selected: $persist('{{ ucfirst(request()->segment(1) ?? 'Dashboard') }}')
      }"
      x-init="$watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))"
      :class="{'dark bg-gray-900': darkMode}">

@include('partials.preloader')

<div class="flex h-screen overflow-hidden">
    @include('partials.sidebar')

    <div class="relative flex flex-col flex-1 overflow-x-hidden overflow-y-auto">

        {{-- Header --}}
        @include('partials.header')

        

        {{-- Page Content --}}
        <main class="flex-1 p-6">
            @include('partials.flash')
            @yield('content')
        </main>

    </div>
</div>

</body>
</html>
