{{-- Dates and guests; submits to the booking page. @include('public.partials.search-form', ['search' => [...]?]) --}}
@php $search ??= []; @endphp
<form method="GET" action="{{ route('book') }}" x-data="{ arrival: @js($search['arrival'] ?? today()->toDateString()) }"
      class="grid grid-cols-2 items-end gap-3 md:grid-cols-6" aria-label="Check availability">
    <label class="site-label col-span-2 md:col-span-1">Check-in
        <input type="date" name="arrival" x-model="arrival" min="{{ today()->toDateString() }}" required class="site-field">
    </label>
    <label class="site-label col-span-2 md:col-span-1">Check-out
        <input type="date" name="departure" value="{{ $search['departure'] ?? today()->addDay()->toDateString() }}" :min="arrival" required class="site-field">
    </label>
    <label class="site-label">Adults
        <select name="adults" class="site-field">
            @foreach (range(1, 10) as $n)<option value="{{ $n }}" @selected((int) ($search['adults'] ?? 2) === $n)>{{ $n }}</option>@endforeach
        </select>
    </label>
    <label class="site-label">Children
        <select name="children" class="site-field">
            @foreach (range(0, 6) as $n)<option value="{{ $n }}" @selected((int) ($search['children'] ?? 0) === $n)>{{ $n }}</option>@endforeach
        </select>
    </label>
    <label class="site-label">Rooms
        <select name="rooms" class="site-field">
            @foreach (range(1, 5) as $n)<option value="{{ $n }}" @selected((int) ($search['rooms'] ?? 1) === $n)>{{ $n }}</option>@endforeach
        </select>
    </label>
    <button type="submit" class="site-btn">Check availability</button>
</form>
