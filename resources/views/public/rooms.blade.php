@extends('layouts.public')

@section('title', 'Rooms')

@section('content')
<section class="mx-auto max-w-[1240px] px-4 pb-6 pt-14 md:px-6">
    <p class="site-eyebrow" data-reveal="left">Stay</p>
    <h1 class="font-display text-4xl md:text-5xl" data-split>Rooms &amp; suites</h1>
    <p class="mt-4 max-w-2xl text-lg leading-relaxed text-muted" data-reveal="up">Prices are per night{{ SystemHelper::pricesIncludeTax() ? ' and include taxes' : '' }}. Choose room only, bed &amp; breakfast, half board or full board when you book.</p>
</section>

<div class="mx-auto max-w-[1240px] px-4 md:px-6">
    <div class="rounded-xl border border-line bg-white p-4 md:p-5" data-reveal="zoom">@include('public.partials.search-form')</div>
</div>

<div class="mx-auto mt-10 grid max-w-[1240px] gap-6 px-4 pb-24 md:px-6">
    @forelse ($roomTypes as $i => ['type' => $type, 'from' => $from])
        <article class="lift grid overflow-hidden rounded-xl border border-line bg-white md:grid-cols-[380px_minmax(0,1fr)_260px]" data-reveal="{{ $i % 2 ? 'right' : 'left' }}">
            <a href="{{ route('stay.show', $type) }}" class="block overflow-hidden"><img src="{{ asset('twh/img/room/room-' . ($i % 6 + 1) . '.jpg') }}" alt="{{ $type->name }}" class="h-60 w-full object-cover md:h-full" loading="lazy"></a>
            <div class="flex flex-col gap-3 p-6 md:p-7">
                <h2 class="text-2xl font-semibold">{{ $type->name }}</h2>
                <p class="text-[15px] text-muted">Sleeps {{ $type->capacity }} · {{ Str::headline($type->bed_type ?? 'double') }} bed</p>
                @if ($type->description)<p class="text-[15px] leading-relaxed text-muted">{{ Str::limit($type->description, 180) }}</p>@endif
                @if ($type->amenities)
                    <ul class="flex flex-wrap gap-2">
                        @foreach ((array) $type->amenities as $amenity)<li class="rounded-full bg-cream px-3 py-1.5 text-[13px] font-medium text-clay-dark">{{ $amenity }}</li>@endforeach
                    </ul>
                @endif
                <a href="{{ route('stay.show', $type) }}" class="mt-auto font-semibold text-clay-text">Room details <i class="fa-solid fa-arrow-right text-xs"></i></a>
            </div>
            <div class="flex flex-col justify-center gap-2 border-t border-line p-6 md:border-l md:border-t-0 md:p-7">
                <span class="text-[13px] text-muted">From</span>
                <span class="text-2xl font-bold">{{ SystemHelper::currencySymbol() }} {{ number_format((float) $from) }}</span>
                <span class="text-[13px] text-muted">per night, bed &amp; breakfast</span>
                <a href="{{ route('book', ['room_type_id' => $type->id]) }}" class="site-btn mt-2">Book this room</a>
            </div>
        </article>
    @empty
        <p class="text-muted">Room details are coming soon. Call us to book.</p>
    @endforelse
</div>
@endsection
