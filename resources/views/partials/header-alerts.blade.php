{{-- Header bell: real things waiting for this person (no sample data). --}}
@php
    $me = auth()->user();
    $alerts = collect();
    if ($me->hasAnyRole(['admin', 'manager', 'receptionist'])) {
        $n = \App\Models\BookingRequest::open()->count();
        if ($n) $alerts->push(['icon' => 'fa-calendar-plus', 'tone' => 'text-brand-600 bg-brand-50', 'text' => $n . ' new booking ' . Str::plural('request', $n) . ' from the website', 'href' => route('booking-requests.index')]);
    }
    $n = \App\Models\Task::overdue()->visibleTo($me)->where('assigned_to', $me->id)->count();
    if ($n) $alerts->push(['icon' => 'fa-clock', 'tone' => 'text-error-600 bg-error-50', 'text' => $n . ' overdue ' . Str::plural('task', $n) . ' assigned to you', 'href' => route('tasks.index')]);
    if ($me->hasAnyRole(['admin', 'manager', 'procurement', 'kitchen'])) {
        $n = \App\Models\InventoryItem::active()->lowStock()->count();
        if ($n) $alerts->push(['icon' => 'fa-box-open', 'tone' => 'text-warning-600 bg-warning-50', 'text' => $n . ' stock ' . Str::plural('item', $n) . ' running low', 'href' => Route::has('inventory.index') ? route('inventory.index') : '#']);
    }
@endphp
<div class="relative" x-data="{ open: false }" @click.outside="open = false">
    <button type="button" @click="open = !open" class="relative flex h-11 w-11 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 transition hover:bg-gray-100 hover:text-gray-700" aria-label="Alerts ({{ $alerts->count() }})">
        @if ($alerts->isNotEmpty())
            <span class="absolute right-0 top-0.5 z-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-brand-500 px-1 text-[10px] font-semibold text-white">{{ $alerts->count() }}</span>
        @endif
        <i class="fa-regular fa-bell"></i>
    </button>
    <div x-show="open" x-cloak x-transition class="absolute right-0 mt-3 w-[320px] rounded-2xl border border-gray-200 bg-white p-3 shadow-theme-lg">
        <p class="px-2 pb-2 text-sm font-semibold text-gray-800">Needs your attention</p>
        @forelse ($alerts as $alert)
            <a href="{{ $alert['href'] }}" class="flex items-center gap-3 rounded-lg px-2 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $alert['tone'] }}"><i class="fa-solid {{ $alert['icon'] }}"></i></span>
                {{ $alert['text'] }}
            </a>
        @empty
            <p class="px-2 py-3 text-sm text-gray-500">All clear. Nothing is waiting for you.</p>
        @endforelse
    </div>
</div>
