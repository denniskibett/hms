{{-- A guest's own bookings and what they owe. --}}
@php $money = fn ($v) => SystemHelper::currencySymbol() . ' ' . number_format((float) $v, 2); @endphp

@component('dashboard.partials.panel', ['title' => 'Your stays'])
    <ul class="divide-y divide-gray-100 text-sm dark:divide-gray-800">
        @forelse ($data['stays'] as $stay)
            @php $invoice = $stay->invoices->where('status', '!=', 'cancelled')->sortBy('id')->first(); @endphp
            <li class="flex flex-wrap items-center justify-between gap-3 px-5 py-4">
                <div>
                    <p class="font-medium text-gray-800 dark:text-white/90">{{ $stay->arrival_date->format('D d M Y') }} → {{ $stay->departure_date->format('D d M Y') }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $stay->roomAllocations->map(fn ($a) => 'Room ' . ($a->room->room_number ?? '?') . ($a->room?->roomType ? ' (' . $a->room->roomType->name . ')' : ''))->implode(', ') ?: 'Room to be assigned' }}
                        · {{ $stay->meal_plan->label() }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    @if ($invoice && $invoice->balance > 0)
                        <span class="text-sm text-gray-700 dark:text-gray-300">Balance {{ $money($invoice->balance) }}</span>
                    @endif
                    @include('partials.status-badge', ['status' => $stay->state])
                </div>
            </li>
        @empty
            <li class="px-5 py-6 text-gray-500 dark:text-gray-400">
                No stays yet. To book, call us on {{ SystemHelper::contactPhone() ?: 'the hotel number' }}{{ SystemHelper::contactEmail() ? ' or email ' . SystemHelper::contactEmail() : '' }}.
            </li>
        @endforelse
    </ul>
@endcomponent
