{{-- Rooms to turn around and open cleaning jobs. --}}
<section class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Housekeeping</h3>
        <form method="POST" action="{{ route('tasks.daily-cleaning') }}">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">
                <i class="fas fa-broom"></i> Today's room cleaning ({{ $data['occupied'] }} occupied)
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        @component('dashboard.partials.panel', ['title' => 'Rooms to clean (' . $data['to_clean']->count() . ')', 'href' => route('rooms.index', ['status' => 'cleaning'])])
            <div class="flex flex-wrap gap-2 p-5">
                @forelse ($data['to_clean'] as $room)
                    <a href="{{ route('rooms.show', $room) }}" class="rounded-lg bg-info-50 px-3 py-2 text-sm font-medium text-info-700 hover:bg-info-100 dark:bg-info-500/15 dark:text-info-400">
                        {{ $room->room_number }} <span class="block text-xs font-normal">{{ $room->roomType->name ?? '' }}</span>
                    </a>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">All rooms are clean.</p>
                @endforelse
            </div>
        @endcomponent

        <div class="xl:col-span-2">
            @component('dashboard.partials.panel', ['title' => 'Open cleaning jobs', 'href' => route('tasks.index', ['category' => 'cleaning'])])
                @include('dashboard.partials.task-list', ['tasks' => $data['cleaning_tasks'], 'showAssignee' => true, 'empty' => 'No cleaning jobs open.'])
            @endcomponent
        </div>
    </div>
</section>
