@extends('layouts.public')

@section('title', 'Rooms & Booking')

@php
    $money = fn ($v) => SystemHelper::currencySymbol() . ' ' . number_format((float) $v);
    $field = 'site-field';
    $searched = $options !== null;
    $chosen = old('room_type_id', request('room_type_id'));
@endphp

@section('content')
<section class="border-b border-cream-line bg-cream">
    <div class="mx-auto max-w-[1240px] px-4 py-12 md:px-6">
        <h1 class="font-display text-4xl md:text-5xl" data-split>Book a stay</h1>
        <p class="mt-3 text-lg text-muted" data-reveal="up">Choose your dates to see what's free. We confirm every request by phone or SMS.</p>
        <div class="mt-6 rounded-xl border border-line bg-white p-4 md:p-5" data-reveal="zoom">
            @include('public.partials.search-form', ['search' => $search])
        </div>
        @if ($errors->hasAny(['arrival', 'departure']))
            <p class="mt-3 text-sm text-red-600">{{ $errors->first('arrival') ?: $errors->first('departure') }}</p>
        @endif
    </div>
</section>

@if ($searched)
    <section class="mx-auto max-w-[1240px] px-4 py-10 md:px-6" x-data="{ roomType: @js((string) $chosen) }">
        <p class="text-sm text-muted">
            {{ \Carbon\Carbon::parse($search['arrival'])->format('D d M') }} → {{ \Carbon\Carbon::parse($search['departure'])->format('D d M Y') }}
            · {{ $search['nights'] }} {{ Str::plural('night', $search['nights']) }}
            · {{ $search['adults'] }} {{ Str::plural('adult', $search['adults']) }}{{ $search['children'] ? ', ' . $search['children'] . ' ' . Str::plural('child', $search['children']) : '' }}
            · {{ $search['rooms'] }} {{ Str::plural('room', $search['rooms']) }}, bed &amp; breakfast
        </p>

        <div class="mt-6 grid grid-cols-1 gap-4" data-stagger=".08" data-effect="left">
            @foreach ($options as $i => $option)
                <label class="flex flex-col gap-4 rounded-xl border bg-white p-4 transition md:flex-row md:items-center {{ $option['bookable'] ? 'lift cursor-pointer border-line hover:border-clay' : 'border-line opacity-60' }}"
                       :class="roomType === @js((string) $option['type']->id) ? 'border-clay ring-2 ring-clay/20' : ''">
                    <img src="{{ asset('twh/img/room/room-' . ($i % 6 + 1) . '.jpg') }}" alt="" class="h-32 w-full rounded-xl object-cover md:w-48">
                    <div class="flex-1">
                        <p class="text-lg font-semibold text-ink">{{ $option['type']->name }}</p>
                        <p class="text-sm text-muted">Sleeps {{ $option['type']->capacity }}</p>
                        <p class="mt-1 text-sm {{ $option['bookable'] ? 'text-green-700' : 'text-red-600' }}">
                            @if (! $option['fits'])
                                Too many guests per room; add a room
                            @elseif ($option['bookable'])
                                {{ $option['free'] }} {{ Str::plural('room', $option['free']) }} free
                            @else
                                Fully booked for these dates
                            @endif
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-ink">{{ $money($option['total']) }}</p>
                        <p class="text-xs text-muted">{{ $money($option['nightly']) }} / room / night</p>
                    </div>
                    @if ($option['bookable'])
                        <input type="radio" name="pick" value="{{ $option['type']->id }}" x-model="roomType" class="h-5 w-5 accent-[#A85508]">
                    @endif
                </label>
            @endforeach
        </div>
        <p class="mt-3 text-xs text-muted">Prices include VAT and Tourism Levy. Final price is confirmed by the hotel.</p>

        <!-- Request -->
        <form method="POST" action="{{ route('book.submit') }}" class="mt-10 rounded-xl border border-line bg-white p-6 md:p-8" data-reveal="up">
            @csrf
            <h2 class="text-xl font-semibold text-ink">Your details</h2>
            <p class="mt-1 text-sm text-muted">Send your request and we'll call or text to confirm. Nothing is charged now.</p>

            @if ($errors->any() && ! $errors->hasAny(['arrival', 'departure']))
                <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    @foreach ($errors->all() as $message)<p>{{ $message }}</p>@endforeach
                </div>
            @endif

            @foreach (['arrival' => 'arrival_date', 'departure' => 'departure_date', 'adults' => 'adults', 'children' => 'children', 'rooms' => 'rooms', 'meal_plan' => 'meal_plan'] as $from => $to)
                <input type="hidden" name="{{ $to }}" value="{{ $search[$from] ?? '' }}">
            @endforeach
            <input type="hidden" name="room_type_id" :value="roomType">
            {{-- Spam trap: people never see or fill this --}}
            <div class="hidden" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>

            <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-3">
                <label>
                    <span class="mb-1 block text-sm font-medium text-muted">Full name *</span>
                    <input type="text" name="name" value="{{ old('name') }}" required maxlength="255" class="{{ $field }}">
                </label>
                <label>
                    <span class="mb-1 block text-sm font-medium text-muted">Phone *</span>
                    <input type="tel" name="phone" value="{{ old('phone') }}" required maxlength="30" placeholder="0712 345 678" class="{{ $field }}">
                </label>
                <label>
                    <span class="mb-1 block text-sm font-medium text-muted">Email</span>
                    <input type="email" name="email" value="{{ old('email') }}" maxlength="255" class="{{ $field }}">
                </label>
                <label class="md:col-span-3">
                    <span class="mb-1 block text-sm font-medium text-muted">Anything we should know?</span>
                    <textarea name="message" rows="3" maxlength="2000" class="site-field" placeholder="Arrival time, airport pick-up, dietary needs…">{{ old('message') }}</textarea>
                </label>
            </div>
            <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm text-muted" x-text="roomType ? '' : 'Any free room type is fine, or pick one above.'"></p>
                <button type="submit" class="site-btn">Send booking request</button>
            </div>
        </form>
    </section>
@endif
@endsection
