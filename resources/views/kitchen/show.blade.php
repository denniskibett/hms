@extends('layouts.app')

@php
    use App\Enums\KitchenOrderStatus;

    $money = fn ($value) => SystemHelper::currencySymbol() . ' ' . number_format((float) $value, 2);
    $status = $order->status;
    $next = $status->next();
    $canCancel = $status->canTransitionTo(KitchenOrderStatus::Cancelled);
    $timeline = [
        ['Placed', $order->created_at],
        ['Started', $order->preparation_started_at],
        ['Ready', $order->ready_at],
        ['Served', $order->delivered_at],
    ];
@endphp

@section('content')
<div x-data="{ pageName: @js('Order ' . $order->order_number) }">
    @include('partials.breadcrumb')
</div>

<div class="space-y-6" x-data="{ cancelOpen: @js($errors->has('reason')) }">
    @if (session('receipt_url'))
        <div class="flex items-center justify-between rounded-lg border border-primary/20 bg-primary/5 px-4 py-3 text-sm text-gray-700 dark:text-gray-300 print:hidden">
            <span><i class="fas fa-receipt mr-1 text-primary"></i> Payment saved. Print the receipt?</span>
            <a href="{{ session('receipt_url') }}" target="_blank" class="rounded-lg bg-primary px-3 py-1.5 text-xs font-medium text-white hover:bg-primary/90">Print receipt</a>
        </div>
    @endif

    <!-- Header -->
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <a href="{{ route('kitchen.orders.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                <i class="fas fa-arrow-left"></i> Back to Orders
            </a>
            <div class="mt-2 flex flex-wrap items-center gap-3">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ $order->order_number }}</h2>
                @include('partials.status-badge', ['status' => $status])
            </div>
            <p class="text-gray-600 dark:text-gray-400">{{ $order->type_label }} · {{ $order->serve_to }} · {{ $order->created_at->format('d M Y, H:i') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if ($next)
                <form method="POST" action="{{ route('kitchen.orders.update-status', $order) }}">
                    @csrf
                    <input type="hidden" name="status" value="{{ $next[0]->value }}">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-primary/90">
                        <i class="fas fa-arrow-right"></i> {{ $next[1] }}
                    </button>
                </form>
            @endif
            @if ($canCancel)
                <button type="button" @click="cancelOpen = true" class="inline-flex items-center gap-2 rounded-lg border border-error-300 px-4 py-2.5 text-sm font-medium text-error-600 hover:bg-error-50 dark:border-error-500/40 dark:text-error-400 dark:hover:bg-error-500/10">
                    <i class="fas fa-ban"></i> Cancel
                </button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Items</h3>
                </div>
                <table class="min-w-full text-sm">
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($order->items as $item)
                            <tr>
                                <td class="px-5 py-3">
                                    <p class="font-medium text-gray-800 dark:text-white/90">{{ $item->variant?->full_name ?? 'Item' }}</p>
                                    @if ($item->notes)<p class="text-xs text-warning-700 dark:text-warning-400">{{ $item->notes }}</p>@endif
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 text-right text-gray-500 dark:text-gray-400">{{ $item->quantity }} × {{ number_format((float) $item->price_at_order, 2) }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-right font-medium text-gray-800 dark:text-white/90">{{ $money($item->total) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-gray-100 dark:border-gray-800">
                            <td colspan="2" class="px-5 py-3 text-right font-semibold text-gray-800 dark:text-white/90">Total</td>
                            <td class="whitespace-nowrap px-5 py-3 text-right font-semibold text-gray-800 dark:text-white/90">{{ $money($order->total) }}</td>
                        </tr>
                    </tfoot>
                </table>
                @if ($order->special_instructions)
                    <p class="border-t border-gray-100 px-5 py-3 text-sm text-gray-700 dark:border-gray-800 dark:text-gray-300"><i class="fas fa-exclamation-circle text-warning-500"></i> {{ $order->special_instructions }}</p>
                @endif
            </div>

            @if ($order->charge_to_room)
                <div class="rounded-2xl border border-info-200 bg-info-50 p-5 text-sm text-info-700 dark:border-info-500/30 dark:bg-info-500/10 dark:text-info-400">
                    <i class="fas fa-bed mr-1"></i>
                    @if ($status === KitchenOrderStatus::Cancelled)
                        This order was charged to the room and has been taken off the guest's bill.
                    @else
                        Charged to the room: it is on the guest's bill and paid at check-out.
                    @endif
                    @if ($order->stay && auth()->user()->hasAnyRole(['admin', 'manager', 'receptionist']))
                        <a href="{{ route('stays.show', $order->stay) }}" class="ml-1 font-medium underline">Open booking #{{ $order->stay->id }}</a>
                    @endif
                </div>
            @else
                @include('stays.partials.folio', ['folioOwner' => $order, 'folioRoutePrefix' => 'kitchen.orders'])
            @endif
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Progress</h3>
                </div>
                <ol class="space-y-3 p-5 text-sm">
                    @foreach ($timeline as [$label, $time])
                        <li class="flex items-center justify-between">
                            <span class="{{ $time ? 'text-gray-800 dark:text-white/90' : 'text-gray-400 dark:text-gray-600' }}">
                                <i class="fas {{ $time ? 'fa-check-circle text-success-500' : 'fa-circle' }} mr-2"></i>{{ $label }}
                            </span>
                            <span class="text-gray-500 dark:text-gray-400">{{ $time?->format('H:i') ?? '—' }}</span>
                        </li>
                    @endforeach
                    @if ($status === KitchenOrderStatus::Cancelled)
                        <li class="text-error-600 dark:text-error-400"><i class="fas fa-ban mr-2"></i>Cancelled{{ $order->cancel_reason ? ': ' . $order->cancel_reason : '' }}</li>
                    @endif
                </ol>
                <p class="border-t border-gray-100 px-5 py-3 text-xs text-gray-500 dark:border-gray-800 dark:text-gray-400">Taken by {{ $order->placer->name ?? '—' }}</p>
            </div>
        </div>
    </div>

    @if ($canCancel)
        <div x-show="cancelOpen" x-cloak x-transition class="fixed inset-0 z-[99999] flex items-center justify-center p-5" @keydown.escape.window="cancelOpen = false">
            <div class="fixed inset-0 bg-gray-400/50 backdrop-blur-sm dark:bg-gray-900/80" @click="cancelOpen = false"></div>
            <form method="POST" action="{{ route('kitchen.orders.update-status', $order) }}" class="relative w-full max-w-md rounded-2xl bg-white p-6 dark:bg-gray-900">
                @csrf
                <input type="hidden" name="status" value="cancelled">
                <h4 class="text-lg font-medium text-gray-800 dark:text-white/90">Cancel {{ $order->order_number }}?</h4>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $order->charge_to_room ? 'It comes off the guest\'s bill.' : 'An unpaid bill is cancelled. If it was paid, void the payment to refund it.' }}
                </p>
                <input type="text" name="reason" required maxlength="255" placeholder="Reason, e.g. guest changed their mind"
                       class="mt-4 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="cancelOpen = false" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Keep order</button>
                    <button type="submit" class="rounded-lg bg-error-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-error-600">Cancel order</button>
                </div>
            </form>
        </div>
    @endif
</div>
@endsection
