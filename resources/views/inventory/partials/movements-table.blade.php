{{--
    Stock movements. @include('inventory.partials.movements-table', ['movements' => ..., 'showItem' => true])
    Load item, creator, department.
--}}
@php $showItem ??= false; @endphp
<div class="custom-scrollbar overflow-x-auto">
    <table class="min-w-full text-left text-sm">
        <thead class="bg-gray-50 dark:bg-gray-900">
            <tr class="whitespace-nowrap border-b border-gray-100 text-gray-700 dark:border-gray-800 dark:text-gray-400">
                <th class="px-5 py-3 font-medium">When</th>
                @if ($showItem)<th class="px-5 py-3 font-medium">Item</th>@endif
                <th class="px-5 py-3 font-medium">Movement</th>
                <th class="px-5 py-3 text-right font-medium">Change</th>
                <th class="px-5 py-3 text-right font-medium">Balance</th>
                <th class="px-5 py-3 font-medium">Details</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse ($movements as $movement)
                @php [$sourceLabel, $sourceUrl] = $movement->source(); $unit = $movement->item?->unit_of_measure; @endphp
                <tr>
                    <td class="whitespace-nowrap px-5 py-3">
                        <p class="text-gray-800 dark:text-white/90">{{ $movement->created_at->format('d M Y, H:i') }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $movement->creator->name ?? '—' }}</p>
                    </td>
                    @if ($showItem)
                        <td class="px-5 py-3">
                            @if ($movement->item)
                                <a href="{{ route('inventory.show', $movement->item) }}" class="text-gray-800 hover:text-primary dark:text-white/90">{{ $movement->item->name }}</a>
                            @endif
                        </td>
                    @endif
                    <td class="whitespace-nowrap px-5 py-3 text-gray-700 dark:text-gray-300">
                        {{ $movement->type_label }}
                        @if ($movement->department)<p class="text-xs text-gray-500 dark:text-gray-400">to {{ $movement->department->name }}</p>@endif
                    </td>
                    <td class="whitespace-nowrap px-5 py-3 text-right font-medium {{ (float) $movement->quantity > 0 ? 'text-success-600 dark:text-success-500' : 'text-error-600 dark:text-error-400' }}">
                        {{ (float) $movement->quantity > 0 ? '+' : '' }}{{ rtrim(rtrim(number_format((float) $movement->quantity, 2), '0'), '.') }} {{ $unit }}
                    </td>
                    <td class="whitespace-nowrap px-5 py-3 text-right text-gray-700 dark:text-gray-300">{{ rtrim(rtrim(number_format((float) $movement->balance_after, 2), '0'), '.') }}</td>
                    <td class="px-5 py-3 text-xs text-gray-500 dark:text-gray-400">
                        @if ($sourceUrl)<a href="{{ $sourceUrl }}" class="text-primary hover:underline">{{ $sourceLabel }}</a>@elseif ($movement->reference_type !== 'manual'){{ $sourceLabel }}@endif
                        @if ($movement->type === 'in') · {{ number_format((float) $movement->unit_cost, 2) }}/unit @endif
                        @if ($movement->notes)<p>{{ $movement->notes }}</p>@endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="{{ $showItem ? 6 : 5 }}" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">No movements yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
