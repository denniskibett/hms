{{-- Dates and guests; submits to the booking page. @include('public.partials.search-form', ['search' => [...]?]) --}}
@php
    $search ??= [];
    $field = 'h-12 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 focus:border-primary focus:outline-none';
@endphp
<form method="GET" action="{{ route('book') }}" x-data="{ arrival: @js($search['arrival'] ?? today()->toDateString()) }"
      class="grid grid-cols-2 gap-3 md:grid-cols-6">
    <label class="col-span-2 md:col-span-1">
        <span class="mb-1 block text-xs font-semibold uppercase text-gray-500">Check in</span>
        <input type="date" name="arrival" x-model="arrival" min="{{ today()->toDateString() }}" required class="{{ $field }}">
    </label>
    <label class="col-span-2 md:col-span-1">
        <span class="mb-1 block text-xs font-semibold uppercase text-gray-500">Check out</span>
        <input type="date" name="departure" value="{{ $search['departure'] ?? today()->addDay()->toDateString() }}" :min="arrival" required class="{{ $field }}">
    </label>
    <label>
        <span class="mb-1 block text-xs font-semibold uppercase text-gray-500">Adults</span>
        <select name="adults" class="{{ $field }}">
            @foreach (range(1, 10) as $n)<option value="{{ $n }}" @selected((int) ($search['adults'] ?? 2) === $n)>{{ $n }}</option>@endforeach
        </select>
    </label>
    <label>
        <span class="mb-1 block text-xs font-semibold uppercase text-gray-500">Children</span>
        <select name="children" class="{{ $field }}">
            @foreach (range(0, 6) as $n)<option value="{{ $n }}" @selected((int) ($search['children'] ?? 0) === $n)>{{ $n }}</option>@endforeach
        </select>
    </label>
    <label>
        <span class="mb-1 block text-xs font-semibold uppercase text-gray-500">Rooms</span>
        <select name="rooms" class="{{ $field }}">
            @foreach (range(1, 5) as $n)<option value="{{ $n }}" @selected((int) ($search['rooms'] ?? 1) === $n)>{{ $n }}</option>@endforeach
        </select>
    </label>
    <div class="flex items-end">
        <button type="submit" class="h-12 w-full rounded-lg bg-primary px-4 text-sm font-semibold text-white hover:bg-primary/90">Check availability</button>
    </div>
</form>
