@extends('layouts.app')

@php
    $inputClass = 'h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
    $money = fn ($value) => SystemHelper::currencySymbol() . ' ' . number_format((float) $value, 2);
@endphp

@section('content')
<div x-data="{ pageName: `Orders` }">
    @include('partials.breadcrumb')
</div>

<div class="space-y-6">
    @include('kitchen.partials.header', ['title' => 'Orders', 'subtitle' => 'Every restaurant and room-service order'])

    <!-- Filters -->
    <form method="GET" action="{{ route('kitchen.orders.index') }}"
          class="grid grid-cols-1 gap-3 rounded-2xl border border-gray-100 bg-white p-4 sm:grid-cols-2 lg:grid-cols-5 dark:border-gray-800 dark:bg-gray-900">
        <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Order no., customer, guest or table" class="{{ $inputClass }} lg:col-span-2">
        <input type="date" name="date" value="{{ $filters['date'] ?? '' }}" class="{{ $inputClass }}" onchange="this.form.submit()">
        <select name="status" class="{{ $inputClass }}" onchange="this.form.submit()">
            <option value="">All statuses</option>
            @foreach (\App\Enums\KitchenOrderStatus::options() as $value => $label)
                <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <select name="type" class="{{ $inputClass }}" onchange="this.form.submit()">
                <option value="">All types</option>
                @foreach (\App\Models\KitchenOrder::TYPES as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @if (array_filter($filters))
                <a href="{{ route('kitchen.orders.index') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">Clear</a>
            @endif
        </div>
    </form>

    <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="custom-scrollbar overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr class="whitespace-nowrap border-b border-gray-100 text-gray-700 dark:border-gray-800 dark:text-gray-400">
                        <th class="px-5 py-3 font-medium">Order</th>
                        <th class="px-5 py-3 font-medium">For</th>
                        <th class="px-5 py-3 font-medium">Items</th>
                        <th class="px-5 py-3 text-right font-medium">Total</th>
                        <th class="px-5 py-3 font-medium">Billing</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($orders as $order)
                        @php $invoice = $order->invoices->sortBy('id')->first(); @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="whitespace-nowrap px-5 py-3">
                                <a href="{{ route('kitchen.orders.show', $order) }}" class="font-medium text-gray-800 hover:text-primary dark:text-white/90">{{ $order->order_number }}</a>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $order->created_at->format('d M, H:i') }}</p>
                            </td>
                            <td class="px-5 py-3">
                                <p class="text-gray-800 dark:text-white/90">{{ $order->serve_to }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $order->type_label }}</p>
                            </td>
                            <td class="px-5 py-3 text-gray-700 dark:text-gray-300">
                                {{ $order->items->map(fn ($i) => $i->quantity . '× ' . ($i->variant?->full_name ?? 'Item'))->implode(', ') }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-right font-medium text-gray-800 dark:text-white/90">{{ $money($order->total) }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-xs">
                                @if ($order->charge_to_room)
                                    <span class="text-info-600 dark:text-info-500"><i class="fas fa-bed"></i> Room charge</span>
                                @elseif ($invoice && $invoice->balance <= 0 && (float) $invoice->total > 0)
                                    <span class="text-success-600 dark:text-success-500"><i class="fas fa-check"></i> Paid</span>
                                @elseif ($invoice)
                                    <span class="text-error-600 dark:text-error-400">{{ $money($invoice->balance) }} due</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">@include('partials.status-badge', ['status' => $order->status])</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                                <i class="fas fa-receipt mb-3 text-4xl"></i>
                                <p>{{ array_filter($filters) ? 'No orders match these filters' : 'No orders yet' }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($orders->hasPages())
            <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">{{ $orders->links() }}</div>
        @endif
    </div>
</div>

@include('kitchen.partials.order-modal')
@endsection
