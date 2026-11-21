@extends('layouts.app')

@section('content')
<div x-data="{ pageName: `Reports` }">
    @include('partials.breadcrumb')
</div>

<div class="space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Reports</h2>
        <p class="text-gray-600 dark:text-gray-400">Pick a report; each can be printed or downloaded for Excel.</p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($reports as $key => [$title, $description, $icon])
            <a href="{{ route('reports.show', $key) }}"
               class="group rounded-2xl border border-gray-100 bg-white p-5 transition hover:border-primary/40 hover:shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary/10 text-primary"><i class="fas {{ $icon }}"></i></span>
                <p class="mt-4 font-semibold text-gray-800 group-hover:text-primary dark:text-white/90">{{ $title }}</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>
            </a>
        @endforeach
    </div>
</div>
@endsection
