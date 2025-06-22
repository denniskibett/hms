<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Briefy') }}</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">

        <!-- Vite Assets -->
        @vite([
            'resources/css/app.css',
            'resources/js/app.js',
            'resources/js/bootstrap.js',
            'resources/js/index.js'
        ])

        <!-- Auto-load all JS components -->
        @php
            $components = array_diff(scandir(resource_path('js/components')), array('..', '.'));
        @endphp
        @foreach ($components as $component)
            @vite(['resources/js/components/' . $component])
        @endforeach
    </head>
    <body class="font-outfit antialiased bg-gray-100" 
          x-data="{ 
              page: '{{ request()->segment(1) ?? 'ecommerce' }}', 
              loaded: true, 
              darkMode: false, 
              stickyMenu: false, 
              sidebarToggle: false, 
              scrollTop: false,
              selected: $persist('{{ request()->segment(1) ? ucfirst(str_replace('-', '', request()->segment(1))) : 'Dashboard' }}')
          }" 
          x-init="darkMode = JSON.parse(localStorage.getItem('darkMode')); $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))" 
          :class="{'dark bg-gray-900': darkMode === true}">
        <!-- Preloader -->
        @include('partials.preloader')

        <div class="flex h-screen overflow-hidden">
            <!-- Sidebar -->
            @include('partials.sidebar')

            <!-- Main Content -->
            <div class="relative flex flex-col flex-1 overflow-x-hidden overflow-y-auto">
                <!-- Header -->
                @include('partials.header')

                <!-- Page Heading -->
                @if (isset($header))
                    <header class="bg-white shadow">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                <!-- Page Content -->
                <main class="flex-1 p-6">
                    @yield('content')
                </main>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    </body>
</html>