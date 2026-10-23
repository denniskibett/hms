{{-- One ticket on the kitchen board. @include('kitchen.partials.order-card', ['order' => $order]) --}}
@php
    $next = $order->status->next();
    $late = $order->age_minutes >= 30;
@endphp
<div class="rounded-xl border bg-white p-4 shadow-theme-xs dark:bg-gray-900 {{ $late ? 'border-error-300 dark:border-error-500/40' : 'border-gray-100 dark:border-gray-800' }}">
    <div class="flex items-start justify-between gap-2">
        <div>
            <a href="{{ route('kitchen.orders.show', $order) }}" class="font-semibold text-gray-800 hover:text-primary dark:text-white/90">{{ $order->order_number }}</a>
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $order->type_label }} · {{ $order->serve_to }}</p>
        </div>
        <span class="whitespace-nowrap text-xs {{ $late ? 'font-semibold text-error-600 dark:text-error-400' : 'text-gray-500 dark:text-gray-400' }}">
            <i class="far fa-clock"></i> {{ $order->age_minutes }} min
        </span>
    </div>

    <ul class="mt-3 space-y-1 text-sm">
        @foreach ($order->items as $item)
            <li class="text-gray-800 dark:text-white/90">
                <span class="font-semibold">{{ $item->quantity }}×</span> {{ $item->variant?->full_name ?? 'Item' }}
                @if ($item->notes)
                    <span class="block pl-6 text-xs text-warning-700 dark:text-warning-400">{{ $item->notes }}</span>
                @endif
            </li>
        @endforeach
    </ul>

    @if ($order->special_instructions)
        <p class="mt-2 rounded bg-warning-50 px-2 py-1 text-xs text-warning-700 dark:bg-warning-500/10 dark:text-warning-400">
            <i class="fas fa-exclamation-circle"></i> {{ $order->special_instructions }}
        </p>
    @endif

    @if ($next)
        <form method="POST" action="{{ route('kitchen.orders.update-status', $order) }}" class="mt-3">
            @csrf
            <input type="hidden" name="status" value="{{ $next[0]->value }}">
            <button type="submit" class="w-full rounded-lg bg-primary px-3 py-2 text-sm font-medium text-white hover:bg-primary/90">{{ $next[1] }}</button>
        </form>
    @endif
</div>
