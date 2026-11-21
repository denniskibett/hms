{{-- Purchase orders. @include('procurement.orders.partials.table', ['orders' => ..., 'hideSupplier' => false]) (load supplier, items) --}}
@php
    $money = fn ($value) => SystemHelper::currencySymbol() . ' ' . number_format((float) $value, 2);
    $hideSupplier ??= false;
@endphp
<div class="custom-scrollbar overflow-x-auto">
    <table class="min-w-full text-left text-sm">
        <thead class="bg-gray-50 dark:bg-gray-900">
            <tr class="whitespace-nowrap border-b border-gray-100 text-gray-700 dark:border-gray-800 dark:text-gray-400">
                <th class="px-5 py-3 font-medium">Order</th>
                @unless ($hideSupplier)<th class="px-5 py-3 font-medium">Supplier</th>@endunless
                <th class="px-5 py-3 font-medium">Items</th>
                <th class="px-5 py-3 font-medium">Delivery</th>
                <th class="px-5 py-3 text-right font-medium">Total</th>
                <th class="px-5 py-3 font-medium">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse ($orders as $order)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                    <td class="whitespace-nowrap px-5 py-3">
                        <a href="{{ route('purchase-orders.show', $order) }}" class="font-medium text-gray-800 hover:text-primary dark:text-white/90">{{ $order->po_number }}</a>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $order->created_at->format('d M Y') }}{{ $order->relationLoaded('requester') && $order->requester ? ' · ' . $order->requester->name : '' }}</p>
                    </td>
                    @unless ($hideSupplier)
                        <td class="px-5 py-3 text-gray-800 dark:text-white/90">{{ $order->supplier->name ?? '—' }}</td>
                    @endunless
                    <td class="px-5 py-3 text-gray-700 dark:text-gray-300">{{ $order->items->count() }} {{ Str::plural('item', $order->items->count()) }}</td>
                    <td class="whitespace-nowrap px-5 py-3 {{ $order->is_late ? 'font-medium text-error-600 dark:text-error-400' : 'text-gray-700 dark:text-gray-300' }}">
                        {{ $order->delivery_date?->format('d M Y') ?? '—' }}{{ $order->is_late ? ' (late)' : '' }}
                    </td>
                    <td class="whitespace-nowrap px-5 py-3 text-right text-gray-800 dark:text-white/90">{{ $money($order->total) }}</td>
                    <td class="px-5 py-3">@include('partials.status-badge', ['status' => $order->status])</td>
                </tr>
            @empty
                <tr><td colspan="{{ $hideSupplier ? 5 : 6 }}" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400"><i class="fas fa-file-invoice mb-3 text-4xl"></i><p>No purchase orders</p></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
