{{-- Event details card. @include('events.partials.details-card', ['event' => $event])  (load facility) --}}
@php $dateFormat = 'D, ' . SystemHelper::dateFormat(); @endphp
<div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
    <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
        <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Event {{ $event->reference }}</h3>
    </div>
    <dl class="space-y-3 p-5 text-sm">
        <div><dt class="text-gray-500 dark:text-gray-400">Status</dt><dd class="mt-1">@include('partials.status-badge', ['status' => $event->status])</dd></div>
        <div><dt class="text-gray-500 dark:text-gray-400">Venue</dt><dd class="text-gray-800 dark:text-white/90">{{ $event->facility->name ?? '—' }}</dd></div>
        <div>
            <dt class="text-gray-500 dark:text-gray-400">Dates</dt>
            <dd class="text-gray-800 dark:text-white/90">{{ $event->start_date->format($dateFormat) }}{{ $event->days > 1 ? ' → ' . $event->end_date->format($dateFormat) : '' }}</dd>
            <dd class="text-xs text-gray-500 dark:text-gray-400">{{ $event->days }} {{ Str::plural('day', $event->days) }} · {{ $event->time_range ?? 'All day' }}</dd>
        </div>
        <div><dt class="text-gray-500 dark:text-gray-400">Guests (pax)</dt><dd class="text-gray-800 dark:text-white/90">{{ $event->pax }}</dd></div>
    </dl>
</div>
