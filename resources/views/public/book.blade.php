@extends('layouts.public')

@section('title', 'Rooms & Booking')

@php
    $money = fn ($v) => SystemHelper::currencySymbol() . ' ' . number_format((float) $v);
    $field = 'h-12 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 focus:border-primary focus:outline-none';
    $searched = $options !== null;
    $chosen = old('room_type_id', request('room_type_id'));
@endphp

@section('content')
<section class="bg-gray-50">
    <div class="mx-auto max-w-7xl px-6 py-10">
        <h1 class="text-3xl font-bold text-gray-900">Book a stay</h1>
        <p class="mt-1 text-gray-600">Choose your dates to see what's free. We confirm every request by phone or SMS.</p>
        <div class="mt-6 rounded-2xl bg-white p-5 shadow-sm">
            @include('public.partials.search-form', ['search' => $search])
        </div>
        @if ($errors->hasAny(['arrival', 'departure']))
            <p class="mt-3 text-sm text-red-600">{{ $errors->first('arrival') ?: $errors->first('departure') }}</p>
        @endif
    </div>
</section>

@if ($searched)
    <section class="mx-auto max-w-7xl px-6 py-10" x-data="{ roomType: @js((string) $chosen) }">
        <p class="text-sm text-gray-600">
            {{ \Carbon\Carbon::parse($search['arrival'])->format('D d M') }} → {{ \Carbon\Carbon::parse($search['departure'])->format('D d M Y') }}
            · {{ $search['nights'] }} {{ Str::plural('night', $search['nights']) }}
            · {{ $search['adults'] }} {{ Str::plural('adult', $search['adults']) }}{{ $search['children'] ? ', ' . $search['children'] . ' ' . Str::plural('child', $search['children']) : '' }}
            · {{ $search['rooms'] }} {{ Str::plural('room', $search['rooms']) }}, bed &amp; breakfast
        </p>

        <div class="mt-6 grid grid-cols-1 gap-4">
            @foreach ($options as $i => $option)
                <label class="flex flex-col gap-4 rounded-2xl border p-4 md:flex-row md:items-center {{ $option['bookable'] ? 'cursor-pointer border-gray-200 hover:border-primary' : 'border-gray-100 opacity-60' }}"
                       :class="roomType === @js((string) $option['type']->id) ? 'border-primary ring-2 ring-primary/20' : ''">
                    <img src="{{ asset('twh/img/room/room-' . ($i % 6 + 1) . '.jpg') }}" alt="" class="h-32 w-full rounded-xl object-cover md:w-48">
                    <div class="flex-1">
                        <p class="text-lg font-semibold text-gray-900">{{ $option['type']->name }}</p>
                        <p class="text-sm text-gray-500">Sleeps {{ $option['type']->capacity }}</p>
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
                        <p class="text-2xl font-bold text-gray-900">{{ $money($option['total']) }}</p>
                        <p class="text-xs text-gray-500">{{ $money($option['nightly']) }} / room / night</p>
                    </div>
                    @if ($option['bookable'])
                        <input type="radio" name="pick" value="{{ $option['type']->id }}" x-model="roomType" class="h-5 w-5 text-primary">
                    @endif
                </label>
            @endforeach
        </div>
        <p class="mt-3 text-xs text-gray-500">Prices include VAT and Tourism Levy. Final price is confirmed by the hotel.</p>

        <!-- Request -->
        <form method="POST" action="{{ route('book.submit') }}" class="mt-10 rounded-2xl border border-gray-200 p-6">
            @csrf
            <h2 class="text-xl font-semibold text-gray-900">Your details</h2>
            <p class="mt-1 text-sm text-gray-600">Send your request and we'll call or text to confirm. Nothing is charged now.</p>

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
                    <span class="mb-1 block text-sm font-medium text-gray-700">Full name *</span>
                    <input type="text" name="name" value="{{ old('name') }}" required maxlength="255" class="{{ $field }}">
                </label>
                <label>
                    <span class="mb-1 block text-sm font-medium text-gray-700">Phone *</span>
                    <input type="tel" name="phone" value="{{ old('phone') }}" required maxlength="30" placeholder="0712 345 678" class="{{ $field }}">
                </label>
                <label>
                    <span class="mb-1 block text-sm font-medium text-gray-700">Email</span>
                    <input type="email" name="email" value="{{ old('email') }}" maxlength="255" class="{{ $field }}">
                </label>
                <label class="md:col-span-3">
                    <span class="mb-1 block text-sm font-medium text-gray-700">Anything we should know?</span>
                    <textarea name="message" rows="3" maxlength="2000" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-800 focus:border-primary focus:outline-none" placeholder="Arrival time, airport pick-up, dietary needs…">{{ old('message') }}</textarea>
                </label>
            </div>
            <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm text-gray-500" x-text="roomType ? '' : 'Any free room type is fine, or pick one above.'"></p>
                <button type="submit" class="rounded-lg bg-primary px-8 py-3 font-semibold text-white hover:bg-primary/90">Send booking request</button>
            </div>
        </form>
    </section>
@endif
@endsection
