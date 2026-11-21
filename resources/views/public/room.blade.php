@extends('layouts.public')

@section('title', $type->name)

@php $money = fn ($v) => SystemHelper::currencySymbol() . ' ' . number_format((float) $v); @endphp

@section('content')
<div class="mx-auto max-w-[1240px] px-4 pt-8 md:px-6">
    <a href="{{ route('stay') }}" class="font-medium text-clay-text"><i class="fa-solid fa-arrow-left text-xs"></i> All rooms</a>
</div>

<section aria-label="Photos" class="mx-auto mt-4 grid max-w-[1240px] grid-cols-2 gap-3 px-4 md:grid-cols-4 md:px-6" data-stagger=".08" data-effect="zoom">
    <img src="{{ asset('twh/img/room/room-details.jpg') }}" alt="{{ $type->name }}" class="col-span-2 row-span-2 h-72 w-full rounded-xl object-cover md:h-[440px]">
    <img src="{{ asset('twh/img/room/room-b1.jpg') }}" alt="" class="h-36 w-full rounded-xl object-cover md:h-[214px]">
    <img src="{{ asset('twh/img/room/room-b2.jpg') }}" alt="" class="h-36 w-full rounded-xl object-cover md:h-[214px]">
    <img src="{{ asset('images/site/lobby.jpg') }}" alt="The lounge" class="h-36 w-full rounded-xl object-cover md:h-[214px]">
    <img src="{{ asset('images/site/garden.jpg') }}" alt="The gardens" class="h-36 w-full rounded-xl object-cover md:h-[214px]">
</section>

<div class="mx-auto flex max-w-[1240px] flex-col gap-12 px-4 py-12 md:px-6 lg:flex-row lg:items-start">
    <div class="flex min-w-0 flex-1 flex-col gap-8">
        <div>
            <h1 class="font-display text-4xl md:text-[44px]" data-split>{{ $type->name }}</h1>
            <p class="mt-2 text-muted" data-reveal="up">{{ Str::headline($type->bed_type ?? 'double') }} bed · sleeps {{ $type->capacity }}</p>
        </div>
        @if ($type->description)<p class="text-lg leading-relaxed text-[#333]" data-reveal="up">{{ $type->description }}</p>@endif
        @if ($type->amenities)
            <div data-reveal="left">
                <h2 class="mb-3 text-xl font-semibold">In the room</h2>
                <ul class="grid grid-cols-2 gap-2.5 md:grid-cols-3" data-stagger=".05" data-effect="up">
                    @foreach ((array) $type->amenities as $amenity)<li><i class="fa-solid fa-check mr-2 text-clay"></i>{{ $amenity }}</li>@endforeach
                </ul>
            </div>
        @endif
        <div data-reveal="tilt">
            <h2 class="mb-3 text-xl font-semibold">Meal plans</h2>
            <div class="overflow-x-auto rounded-lg border border-line bg-white">
                <table class="w-full text-[15px]">
                    <thead><tr class="text-left text-[13px] text-muted"><th class="border-b border-line px-4 py-3">Plan</th><th class="border-b border-line px-4 py-3 text-right">Per night, {{ min(2, max((int) $type->capacity, 1)) }} {{ Str::plural('adult', min(2, max((int) $type->capacity, 1))) }}</th></tr></thead>
                    <tbody>
                        @foreach ($plans as ['plan' => $plan, 'nightly' => $nightly])
                            <tr class="border-b border-[#F1ECE4] last:border-0"><td class="px-4 py-3 font-semibold">{{ $plan->label() }}</td><td class="px-4 py-3 text-right font-bold">{{ $money($nightly) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div data-reveal="up">
            <h2 class="mb-3 text-xl font-semibold">Good to know</h2>
            <ul class="grid list-disc gap-2 pl-5 leading-relaxed text-[#333]">
                <li>Bring a national ID, or a passport if you are visiting from abroad.</li>
                <li>Meals, bar and laundry can go on your room bill and are paid at check-out.</li>
                <li>Companies can be billed directly with an approved company account.</li>
            </ul>
        </div>
    </div>

    <aside aria-label="Book this room" class="flex w-full flex-col gap-4 rounded-xl border border-line bg-white p-6 lg:sticky lg:top-28 lg:w-[340px] lg:flex-none" data-reveal="right">
        <div><span class="text-3xl font-bold">{{ $money($plans->firstWhere('plan', \App\Enums\MealPlan::BedAndBreakfast)['nightly'] ?? 0) }}</span><span class="text-sm text-muted"> / night, B&amp;B</span></div>
        <form method="GET" action="{{ route('book') }}" class="flex flex-col gap-3" x-data="{ arrival: '{{ today()->toDateString() }}' }">
            <input type="hidden" name="room_type_id" value="{{ $type->id }}">
            <label class="site-label">Check-in<input type="date" name="arrival" x-model="arrival" min="{{ today()->toDateString() }}" required class="site-field"></label>
            <label class="site-label">Check-out<input type="date" name="departure" value="{{ today()->addDay()->toDateString() }}" :min="arrival" required class="site-field"></label>
            <label class="site-label">Adults<select name="adults" class="site-field">@foreach (range(1, max((int) $type->capacity, 1) + 1) as $n)<option value="{{ $n }}" @selected($n === 2)>{{ $n }}</option>@endforeach</select></label>
            <button type="submit" class="site-btn">Check availability</button>
        </form>
        <p class="text-[13px] text-muted">Pay with M-Pesa or card through Sharet Pay, or pay at the hotel.</p>
    </aside>
</div>

@if ($others->isNotEmpty())
    <section class="mx-auto max-w-[1240px] px-4 pb-24 md:px-6">
        <h2 class="site-h2 mb-8" data-split>Other rooms</h2>
        <div class="grid grid-cols-1 gap-5 md:grid-cols-3" data-stagger=".12" data-effect="up">
            @foreach ($others as $i => ['type' => $other, 'from' => $from])
                @include('public.partials.room-card', ['type' => $other, 'from' => $from, 'i' => $i])
            @endforeach
        </div>
    </section>
@endif
@endsection
