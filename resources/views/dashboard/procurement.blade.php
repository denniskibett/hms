{{-- Buying: what is low, what is on order. --}}
@php $money = fn ($v) => SystemHelper::currencySymbol() . ' ' . number_format((float) $v, 2); @endphp

<section class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Purchasing</h3>
        <div class="flex gap-4 text-sm">
            <a href="{{ route('purchase-orders.index', ['status' => 'draft']) }}" class="text-primary hover:underline">{{ $data['drafts'] }} drafts</a>
            <a href="{{ route('purchase-orders.index', ['status' => 'submitted']) }}" class="text-primary hover:underline">{{ $data['awaiting_approval'] }} awaiting approval</a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        @component('dashboard.partials.panel', ['title' => 'Running low', 'href' => route('inventory.index', ['stock' => 'low'])])
            <ul class="divide-y divide-gray-100 text-sm dark:divide-gray-800">
                @forelse ($data['low_stock'] as $item)
                    <li class="flex flex-wrap items-center justify-between gap-2 px-5 py-3">
                        <span>
                            <a href="{{ route('inventory.show', $item) }}" class="font-medium text-gray-800 hover:text-primary dark:text-white/90">{{ $item->name }}</a>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $item->qty() }} left, reorder at {{ $item->qty($item->reorder_level) }}</span>
                        </span>
                        @if ($item->primarySupplier)
                            <a href="{{ route('suppliers.show', $item->primarySupplier) }}" class="text-xs text-primary hover:underline">{{ $item->primarySupplier->name }}</a>
                        @endif
                    </li>
                @empty
                    <li class="px-5 py-4 text-gray-500 dark:text-gray-400">Stock is fine.</li>
                @endforelse
            </ul>
        @endcomponent
        @component('dashboard.partials.panel', ['title' => 'Waiting for delivery', 'href' => route('purchase-orders.index')])
            <ul class="divide-y divide-gray-100 text-sm dark:divide-gray-800">
                @forelse ($data['awaiting_delivery'] as $order)
                    <li class="flex flex-wrap items-center justify-between gap-2 px-5 py-3">
                        <span>
                            <a href="{{ route('purchase-orders.show', $order) }}" class="font-medium text-gray-800 hover:text-primary dark:text-white/90">{{ $order->po_number }}</a>
                            <span class="text-gray-500 dark:text-gray-400">· {{ $order->supplier->name ?? '' }} · {{ $money($order->total) }}</span>
                        </span>
                        <span class="text-xs {{ $order->is_late ? 'font-medium text-error-600 dark:text-error-400' : 'text-gray-500 dark:text-gray-400' }}">
                            {{ $order->delivery_date ? 'due ' . $order->delivery_date->format('d M') : $order->status->label() }}{{ $order->is_late ? ' (late)' : '' }}
                        </span>
                    </li>
                @empty
                    <li class="px-5 py-4 text-gray-500 dark:text-gray-400">Nothing on order.</li>
                @endforelse
            </ul>
        @endcomponent
    </div>
</section>
