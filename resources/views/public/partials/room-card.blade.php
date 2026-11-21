{{-- @include('public.partials.room-card', ['type' => RoomType, 'from' => float, 'i' => int]) --}}
<article class="lift group flex flex-col overflow-hidden rounded-xl border border-line bg-white">
    <a href="{{ route('stay.show', $type) }}" class="block overflow-hidden">
        <img src="{{ asset('twh/img/room/room-' . ($i % 6 + 1) . '.jpg') }}" alt="{{ $type->name }}" class="h-56 w-full object-cover" loading="lazy">
    </a>
    <div class="flex flex-1 flex-col gap-2 p-5">
        <h3 class="text-xl font-semibold"><a href="{{ route('stay.show', $type) }}" class="text-ink hover:text-clay">{{ $type->name }}</a></h3>
        <p class="text-sm text-muted">Sleeps {{ $type->capacity }} · {{ Str::headline($type->bed_type ?? 'double') }} bed</p>
        @if ($type->amenities)
            <ul class="mt-1 flex flex-wrap gap-1.5">
                @foreach (array_slice((array) $type->amenities, 0, 4) as $amenity)
                    <li class="rounded-full bg-cream px-2.5 py-1 text-xs font-medium text-clay-dark">{{ $amenity }}</li>
                @endforeach
            </ul>
        @endif
        <div class="mt-auto flex items-end justify-between pt-4">
            <p><span class="text-xs text-muted">from</span><br><span class="text-xl font-bold">{{ SystemHelper::currencySymbol() }} {{ number_format((float) $from) }}</span> <span class="text-xs text-muted">/ night, B&amp;B</span></p>
            <a href="{{ route('book', ['room_type_id' => $type->id]) }}" class="font-semibold text-clay-text transition group-hover:translate-x-1">Book <i class="fa-solid fa-arrow-right text-xs"></i></a>
        </div>
    </div>
</article>
