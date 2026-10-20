{{--
    List of stays with quick front-desk actions.
    @include('stays.partials.table', [
        'stays' => $stays,          // collection or paginator; load guest + roomAllocations.room
        'showGuest' => true,        // hide the guest column on a guest's own page
        'empty' => 'No bookings',   // optional empty-state text
    ])
--}}
@php
    $showGuest = $showGuest ?? true;
    $dateFormat = 'D, ' . SystemHelper::dateFormat();
    $currency = SystemHelper::currencySymbol();
@endphp

<div class="custom-scrollbar overflow-x-auto">
    <table class="min-w-full text-left text-sm text-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-900">
            <tr class="whitespace-nowrap border-b border-gray-100 dark:border-gray-800">
                <th class="px-5 py-3 font-medium text-gray-700 dark:text-gray-400">Booking</th>
                @if ($showGuest)
                    <th class="px-5 py-3 font-medium text-gray-700 dark:text-gray-400">Guest</th>
                @endif
                <th class="px-5 py-3 font-medium text-gray-700 dark:text-gray-400">Rooms</th>
                <th class="px-5 py-3 font-medium text-gray-700 dark:text-gray-400">Dates</th>
                <th class="px-5 py-3 font-medium text-gray-700 dark:text-gray-400">Status</th>
                <th class="px-5 py-3 text-right font-medium text-gray-700 dark:text-gray-400">Room charges</th>
                <th class="px-5 py-3 font-medium text-gray-700 dark:text-gray-400"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-white/[0.03]">
            @forelse ($stays as $stay)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                    <td class="whitespace-nowrap px-5 py-3">
                        <a href="{{ route('stays.show', $stay) }}" class="font-medium text-gray-800 hover:text-primary dark:text-white/90">#{{ $stay->id }}</a>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $stay->meal_plan->value }} • {{ $stay->adults }}A{{ $stay->children ? ' ' . $stay->children . 'C' : '' }}</p>
                    </td>
                    @if ($showGuest)
                        <td class="px-5 py-3">
                            <p class="font-medium text-gray-800 dark:text-white/90">{{ $stay->guest->name ?? '—' }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $stay->guest->phone ?? $stay->guest->email ?? '' }}</p>
                        </td>
                    @endif
                    <td class="px-5 py-3">
                        <div class="flex flex-wrap gap-1">
                            @forelse ($stay->roomAllocations as $allocation)
                                <span class="rounded-md bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary">{{ $allocation->room->room_number ?? '?' }}</span>
                            @empty
                                <span class="text-xs text-gray-400">No room</span>
                            @endforelse
                        </div>
                    </td>
                    <td class="whitespace-nowrap px-5 py-3">
                        <p class="text-gray-800 dark:text-white/90">{{ $stay->arrival_date->format($dateFormat) }} → {{ $stay->departure_date->format($dateFormat) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $stay->nights }} {{ Str::plural('night', $stay->nights) }}</p>
                    </td>
                    <td class="px-5 py-3">
                        @include('partials.status-badge', ['status' => $stay->state])
                    </td>
                    <td class="whitespace-nowrap px-5 py-3 text-right font-medium text-gray-800 dark:text-white/90">
                        {{ $currency }} {{ number_format($stay->room_charges, 2) }}
                    </td>
                    <td class="whitespace-nowrap px-5 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            @if ($stay->state->canTransitionTo(\App\Enums\StayStatus::CheckedIn) && $stay->arrival_date->lte(today()))
                                <form method="POST" action="{{ route('stays.check-in', $stay) }}">
                                    @csrf
                                    <button type="submit" class="rounded-lg bg-success-500 px-2.5 py-1 text-xs font-medium text-white hover:bg-success-600">
                                        <i class="fas fa-sign-in-alt"></i> Check in
                                    </button>
                                </form>
                            @elseif ($stay->state === \App\Enums\StayStatus::CheckedIn)
                                <form method="POST" action="{{ route('stays.check-out', $stay) }}"
                                      onsubmit="return confirm('Check out stay #{{ $stay->id }}?')">
                                    @csrf
                                    <button type="submit" class="rounded-lg bg-warning-500 px-2.5 py-1 text-xs font-medium text-white hover:bg-warning-600">
                                        <i class="fas fa-sign-out-alt"></i> Check out
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('stays.show', $stay) }}" class="rounded-lg bg-primary/10 px-2.5 py-1 text-xs font-medium text-primary hover:bg-primary/20">
                                View
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $showGuest ? 7 : 6 }}" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                        <i class="fas fa-calendar-times mb-2 text-3xl text-gray-300 dark:text-gray-600"></i>
                        <p>{{ $empty ?? 'No bookings' }}</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
