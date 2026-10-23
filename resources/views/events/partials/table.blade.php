{{--
    Events table.
    @include('events.partials.table', [
        'events' => $events,     // collection or paginator; load facility, items, invoices (not cancelled)
        'hideVenue' => false,    // optional
        'empty' => 'No events',  // optional
    ])
--}}
@php
    $money = fn ($value) => SystemHelper::currencySymbol() . ' ' . number_format((float) $value, 2);
    $dateFormat = 'D, d M Y';
    $hideVenue ??= false;
@endphp

<div class="custom-scrollbar overflow-x-auto">
    <table class="min-w-full text-left text-sm">
        <thead class="bg-gray-50 dark:bg-gray-900">
            <tr class="whitespace-nowrap border-b border-gray-100 text-gray-700 dark:border-gray-800 dark:text-gray-400">
                <th class="px-5 py-3 font-medium">Event</th>
                <th class="px-5 py-3 font-medium">Client</th>
                @unless ($hideVenue)<th class="px-5 py-3 font-medium">Venue</th>@endunless
                <th class="px-5 py-3 font-medium">Dates</th>
                <th class="px-5 py-3 font-medium">Pax</th>
                <th class="px-5 py-3 text-right font-medium">Total</th>
                <th class="px-5 py-3 font-medium">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse ($events as $event)
                @php $invoice = $event->invoices->sortBy('id')->first(); @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                    <td class="px-5 py-3">
                        <a href="{{ route('events.show', $event) }}" class="font-medium text-gray-800 hover:text-primary dark:text-white/90">{{ $event->title }}</a>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $event->reference }}</p>
                    </td>
                    <td class="px-5 py-3">
                        <p class="text-gray-800 dark:text-white/90">{{ $event->client_name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $event->contact_phone }}</p>
                    </td>
                    @unless ($hideVenue)
                        <td class="px-5 py-3 text-gray-700 dark:text-gray-300">{{ $event->facility->name ?? '—' }}</td>
                    @endunless
                    <td class="whitespace-nowrap px-5 py-3">
                        <p class="text-gray-800 dark:text-white/90">
                            {{ $event->start_date->format($dateFormat) }}{{ $event->days > 1 ? ' → ' . $event->end_date->format($dateFormat) : '' }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $event->days }} {{ Str::plural('day', $event->days) }}{{ $event->time_range ? ' · ' . $event->time_range : '' }}
                        </p>
                    </td>
                    <td class="px-5 py-3 text-gray-700 dark:text-gray-300">{{ $event->pax }}</td>
                    <td class="whitespace-nowrap px-5 py-3 text-right">
                        <p class="text-gray-800 dark:text-white/90">{{ $money($invoice->total ?? $event->total) }}</p>
                        @if ($invoice && $invoice->balance > 0)
                            <p class="text-xs text-error-600 dark:text-error-400">{{ $money($invoice->balance) }} due</p>
                        @elseif ($invoice && (float) $invoice->total > 0)
                            <p class="text-xs text-success-600 dark:text-success-500">Paid</p>
                        @endif
                    </td>
                    <td class="px-5 py-3">@include('partials.status-badge', ['status' => $event->status])</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                        <i class="fas fa-calendar-alt mb-3 text-4xl"></i>
                        <p>{{ $empty ?? 'No events' }}</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
