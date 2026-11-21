{{-- Today's arrivals and departures, room status. --}}
<section class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Front desk</h3>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('stays.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/90"><i class="fas fa-calendar-plus"></i> New booking</a>
            <a href="{{ route('rooms.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"><i class="fas fa-door-open"></i> Rooms</a>
        </div>
    </div>

    <div class="flex flex-wrap gap-2">
        @foreach ($data['rooms'] as $value => ['status' => $status, 'count' => $count])
            @if ($count)
                <a href="{{ route('rooms.index', ['status' => $value]) }}" class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-medium {{ $status->badgeClasses() }}">
                    {{ $status->label() }} <span class="font-bold">{{ $count }}</span>
                </a>
            @endif
        @endforeach
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        @component('dashboard.partials.panel', ['title' => 'Arriving (' . $data['arrivals']->count() . ')', 'href' => route('stays.index')])
            @include('stays.partials.table', ['stays' => $data['arrivals'], 'empty' => 'No arrivals waiting'])
        @endcomponent
        @component('dashboard.partials.panel', ['title' => 'Leaving (' . $data['departures']->count() . ')', 'href' => route('stays.index')])
            @include('stays.partials.table', ['stays' => $data['departures'], 'empty' => 'Nobody due out'])
        @endcomponent
    </div>
</section>
