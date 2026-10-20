<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | {{ SystemHelper::appName() }}</title>
    <link rel="icon" href="{{ SystemHelper::faviconUrl() }}">
    @vite(['resources/css/app.css'])
    <style>
        :root {
            --color-primary-rgb: {{ SystemHelper::rgb(SystemHelper::primaryColor(), '58 87 232') }};
            --color-secondary-rgb: {{ SystemHelper::rgb(SystemHelper::secondaryColor(), '8 177 186') }};
        }
        @page { margin: 12mm; }
    </style>
</head>
<body class="bg-gray-100 font-outfit text-gray-800 print:bg-white">
    <div class="mx-auto my-6 flex max-w-3xl justify-end gap-2 px-4 print:hidden">
        <button type="button" onclick="window.close()" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Close</button>
        <button type="button" onclick="window.print()" class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/90">Print</button>
    </div>

    <main class="mx-auto mb-10 max-w-3xl bg-white p-8 shadow-theme-sm print:m-0 print:max-w-none print:p-0 print:shadow-none">
        <!-- Hotel header -->
        <header class="flex items-start justify-between gap-6 border-b border-gray-200 pb-6">
            <div class="flex items-center gap-4">
                @if (SystemHelper::logoUrl())
                    <img src="{{ SystemHelper::logoUrl() }}" alt="" class="h-14 w-auto">
                @endif
                <div>
                    <h1 class="text-xl font-bold">{{ SystemHelper::appName() }}</h1>
                    <p class="text-sm text-gray-500">{{ SystemHelper::address() }}</p>
                    <p class="text-sm text-gray-500">
                        {{ collect([SystemHelper::contactPhone(), SystemHelper::contactEmail()])->filter()->join(' · ') }}
                    </p>
                    @if (SystemHelper::get('billing.kra_pin'))
                        <p class="text-sm text-gray-500">KRA PIN: {{ SystemHelper::get('billing.kra_pin') }}</p>
                    @endif
                </div>
            </div>
            <div class="text-right">
                @yield('document-heading')
            </div>
        </header>

        @yield('content')

        <footer class="mt-10 border-t border-gray-200 pt-4 text-center text-xs text-gray-500">
            Thank you for staying with {{ SystemHelper::appName() }}.
        </footer>
    </main>
</body>
</html>
