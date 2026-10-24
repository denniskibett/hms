@extends('layouts.app')

@php
    use App\Enums\PurchaseOrderStatus;

    $money = fn ($value) => SystemHelper::currencySymbol() . ' ' . number_format((float) $value, 2);
    $status = $order->status;
    $primary = 'inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-primary/90';
    $secondary = 'inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300';
    // [target status, label, icon, class, who]
    $actions = array_filter([
        $status === PurchaseOrderStatus::Draft && ! $isManager ? [PurchaseOrderStatus::Submitted, 'Submit for approval', 'fa-paper-plane', $primary] : null,
        in_array($status, [PurchaseOrderStatus::Draft, PurchaseOrderStatus::Submitted], true) && $isManager ? [PurchaseOrderStatus::Approved, 'Approve', 'fa-check', $primary] : null,
        $status === PurchaseOrderStatus::Approved ? [PurchaseOrderStatus::Ordered, 'Mark as ordered', 'fa-phone', $secondary] : null,
    ]);
    $canAct = $canBuy;
@endphp

@section('content')
<div x-data="{ pageName: @js($order->po_number) }">
    @include('partials.breadcrumb')
</div>

<div class="space-y-6" x-data="{ reason: null }">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <a href="{{ route('purchase-orders.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400"><i class="fas fa-arrow-left"></i> Back to Purchase Orders</a>
            <div class="mt-2 flex flex-wrap items-center gap-3">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ $order->po_number }}</h2>
                @include('partials.status-badge', ['status' => $status])
            </div>
            <p class="text-gray-600 dark:text-gray-400">
                <a href="{{ route('suppliers.show', $order->supplier) }}" class="hover:text-primary">{{ $order->supplier->name }}</a>
                · {{ $money($order->total) }}
                @if ($order->delivery_date) · deliver by {{ $order->delivery_date->format('d M Y') }}@if ($order->is_late) <span class="font-medium text-error-600">(late)</span>@endif @endif
            </p>
        </div>
        @if ($canAct)
            <div class="flex flex-wrap gap-2">
                @if ($status->isEditable())
                    <button type="button" @click="$dispatch('open-order-form', @js($order->formData()))" class="{{ $secondary }}"><i class="fas fa-edit"></i> Edit</button>
                @endif
                @if ($status === PurchaseOrderStatus::Submitted && $isManager)
                    <button type="button" @click="reason = 'draft'" class="{{ $secondary }}"><i class="fas fa-undo"></i> Send back</button>
                @endif
                @foreach ($actions as [$target, $label, $icon, $class])
                    <form method="POST" action="{{ route('purchase-orders.status', $order) }}">
                        @csrf
                        <input type="hidden" name="status" value="{{ $target->value }}">
                        <button type="submit" class="{{ $class }}"><i class="fas {{ $icon }}"></i> {{ $label }}</button>
                    </form>
                @endforeach
                @if ($status->canCancel())
                    <button type="button" @click="reason = 'cancelled'" class="inline-flex items-center gap-2 rounded-lg border border-error-300 px-4 py-2.5 text-sm font-medium text-error-600 hover:bg-error-50 dark:border-error-500/40 dark:text-error-400"><i class="fas fa-ban"></i> Cancel</button>
                @endif
            </div>
        @endif
    </div>

    @if ($status === PurchaseOrderStatus::Submitted && ! $isManager)
        <p class="rounded-lg border border-warning-200 bg-warning-50 px-4 py-3 text-sm text-warning-700 dark:border-warning-500/30 dark:bg-warning-500/10 dark:text-warning-400">Waiting for a manager to approve.</p>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <!-- Lines, and the delivery form when it can be received -->
            <form method="POST" action="{{ route('purchase-orders.receive', $order) }}" class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                @csrf
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">{{ $status->canReceive() && $canAct ? 'Items — book in a delivery' : 'Items' }}</h3>
                </div>
                <div class="custom-scrollbar overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr class="whitespace-nowrap border-b border-gray-100 text-gray-700 dark:border-gray-800 dark:text-gray-400">
                                <th class="px-5 py-3 font-medium">Item</th>
                                <th class="px-5 py-3 text-right font-medium">Ordered</th>
                                <th class="px-5 py-3 text-right font-medium">Received</th>
                                <th class="px-5 py-3 text-right font-medium">Price</th>
                                <th class="px-5 py-3 text-right font-medium">Total</th>
                                @if ($status->canReceive() && $canAct)
                                    <th class="px-5 py-3 font-medium">Arrived now</th>
                                    <th class="px-5 py-3 font-medium">Price paid</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($order->items as $line)
                                <tr>
                                    <td class="px-5 py-3">
                                        @if ($line->inventoryItem)
                                            <a href="{{ route('inventory.show', $line->inventoryItem) }}" class="text-gray-800 hover:text-primary dark:text-white/90">{{ $line->inventoryItem->name }}</a>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-3 text-right text-gray-700 dark:text-gray-300">{{ $line->inventoryItem?->qty($line->quantity) }}</td>
                                    <td class="whitespace-nowrap px-5 py-3 text-right {{ $line->outstanding <= 0 ? 'text-success-600 dark:text-success-500' : 'text-gray-700 dark:text-gray-300' }}">{{ $line->inventoryItem?->qty($line->received_quantity) }}</td>
                                    <td class="whitespace-nowrap px-5 py-3 text-right text-gray-700 dark:text-gray-300">{{ number_format((float) $line->unit_price, 2) }}</td>
                                    <td class="whitespace-nowrap px-5 py-3 text-right text-gray-800 dark:text-white/90">{{ $money($line->line_total) }}</td>
                                    @if ($status->canReceive() && $canAct)
                                        <td class="px-5 py-2">
                                            @if ($line->outstanding > 0)
                                                <input type="number" name="lines[{{ $line->id }}][quantity]" min="0" max="{{ $line->outstanding }}" step="0.01" value="{{ old("lines.{$line->id}.quantity", $line->outstanding) }}"
                                                       class="h-9 w-24 rounded-lg border border-gray-300 bg-transparent px-2 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">
                                            @else
                                                <span class="text-xs text-success-600">Complete</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-2">
                                            @if ($line->outstanding > 0)
                                                <input type="number" name="lines[{{ $line->id }}][unit_price]" min="0" step="0.01" value="{{ old("lines.{$line->id}.unit_price", (float) $line->unit_price) }}"
                                                       class="h-9 w-28 rounded-lg border border-gray-300 bg-transparent px-2 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($status->canReceive() && $canAct)
                    <div class="flex flex-wrap items-center justify-end gap-3 border-t border-gray-100 px-5 py-4 dark:border-gray-800">
                        <input type="text" name="supplier_reference" maxlength="100" value="{{ old('supplier_reference', $order->supplier_reference) }}" placeholder="Supplier invoice / delivery note no."
                               class="h-10 w-64 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">
                        <button type="submit" class="{{ $primary }}" onclick="return confirm('Add these quantities to stock?')"><i class="fas fa-dolly"></i> Receive into stock</button>
                    </div>
                @endif
            </form>

            @if ($order->receipts->isNotEmpty())
                <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800"><h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Deliveries</h3></div>
                    <ul class="divide-y divide-gray-100 text-sm dark:divide-gray-800">
                        @foreach ($order->receipts->sortByDesc('id') as $receipt)
                            <li class="flex flex-wrap justify-between gap-2 px-5 py-2.5">
                                <span class="text-gray-800 dark:text-white/90">{{ $receipt->item?->qty($receipt->quantity) }} {{ $receipt->item?->name }} at {{ number_format((float) $receipt->unit_cost, 2) }}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $receipt->created_at->format('d M Y H:i') }} · {{ $receipt->creator->name ?? '' }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800"><h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Details</h3></div>
                <dl class="space-y-3 p-5 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-gray-500 dark:text-gray-400">Requested by</dt><dd class="text-gray-800 dark:text-white/90">{{ $order->requester->name ?? '—' }}, {{ $order->created_at->format('d M') }}</dd></div>
                    @if ($order->approved_at)<div class="flex justify-between gap-3"><dt class="text-gray-500 dark:text-gray-400">Approved by</dt><dd class="text-gray-800 dark:text-white/90">{{ $order->approver->name ?? '—' }}, {{ $order->approved_at->format('d M') }}</dd></div>@endif
                    @if ($order->ordered_at)<div class="flex justify-between gap-3"><dt class="text-gray-500 dark:text-gray-400">Ordered</dt><dd class="text-gray-800 dark:text-white/90">{{ $order->ordered_at->format('d M Y') }}</dd></div>@endif
                    @if ($order->received_at)<div class="flex justify-between gap-3"><dt class="text-gray-500 dark:text-gray-400">Fully received</dt><dd class="text-gray-800 dark:text-white/90">{{ $order->received_at->format('d M Y') }}</dd></div>@endif
                    @if ($order->supplier_reference)<div class="flex justify-between gap-3"><dt class="text-gray-500 dark:text-gray-400">Supplier ref.</dt><dd class="text-gray-800 dark:text-white/90">{{ $order->supplier_reference }}</dd></div>@endif
                    <div class="flex justify-between gap-3"><dt class="text-gray-500 dark:text-gray-400">Received value</dt><dd class="text-gray-800 dark:text-white/90">{{ $money($order->received_value) }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-gray-500 dark:text-gray-400">Supplier terms</dt><dd class="text-gray-800 dark:text-white/90">{{ $order->supplier->payment_terms ?: '—' }}</dd></div>
                </dl>
                @if ($order->notes)
                    <p class="whitespace-pre-line border-t border-gray-100 px-5 py-3 text-sm text-gray-700 dark:border-gray-800 dark:text-gray-300">{{ $order->notes }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Send back / cancel reason -->
    <div x-show="reason" x-cloak x-transition class="fixed inset-0 z-[99999] flex items-center justify-center p-5" @keydown.escape.window="reason = null">
        <div class="fixed inset-0 bg-gray-400/50 backdrop-blur-sm dark:bg-gray-900/80" @click="reason = null"></div>
        <form method="POST" action="{{ route('purchase-orders.status', $order) }}" class="relative w-full max-w-md space-y-4 rounded-2xl bg-white p-6 dark:bg-gray-900">
            @csrf
            <input type="hidden" name="status" :value="reason">
            <h4 class="text-lg font-medium text-gray-800 dark:text-white/90" x-text="reason === 'cancelled' ? 'Cancel this order?' : 'Send back for changes'"></h4>
            <input type="text" name="note" required maxlength="500" placeholder="Why?" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">
            <div class="flex justify-end gap-3">
                <button type="button" @click="reason = null" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Back</button>
                <button type="submit" class="rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white hover:bg-primary/90">Save</button>
            </div>
        </form>
    </div>
</div>

@if ($canBuy && $status->isEditable())
    @include('procurement.orders.partials.order-modal')
@endif
@endsection
