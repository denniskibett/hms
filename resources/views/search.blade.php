@extends('layouts.app')

@section('content')
@include('partials.page-header', ['title' => 'Search'])
<div class="mx-auto max-w-4xl">
    <form method="GET" action="{{ route('search') }}">
        <input type="search" name="q" value="{{ $q }}" autofocus placeholder="Guest name, phone, booking number, room or invoice" class="h-12 w-full rounded-xl border border-gray-300 bg-white px-4 text-sm shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10">
    </form>

    @if (mb_strlen($q) < 2)
        <p class="mt-6 text-sm text-gray-500">Type at least two letters or a number.</p>
    @elseif ($results->isEmpty())
        <p class="mt-6 text-sm text-gray-500">Nothing found for "{{ $q }}".</p>
    @else
        <div class="mt-6 space-y-6">
            @foreach ($results as $group => $rows)
                <section class="rounded-2xl border border-gray-200 bg-white">
                    <h3 class="border-b border-gray-100 px-5 py-3 text-sm font-semibold text-gray-700">{{ $group }}</h3>
                    <ul class="divide-y divide-gray-100">
                        @foreach ($rows as $row)
                            <li><a href="{{ $row['href'] }}" class="block px-5 py-3 hover:bg-gray-50">
                                <span class="block text-sm font-medium text-gray-800">{{ $row['title'] }}</span>
                                @if ($row['meta'])<span class="block text-xs text-gray-500">{{ $row['meta'] }}</span>@endif
                            </a></li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </div>
    @endif
</div>
@endsection
