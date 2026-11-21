{{-- Kitchen page header, stats and tabs. @include('kitchen.partials.header', ['title' => ..., 'subtitle' => ...]) --}}
@php
    $money = fn ($value) => SystemHelper::currencySymbol() . ' ' . number_format((float) $value, 2);
    $canEditMenu = auth()->user()->hasAnyRole(['admin', 'manager', 'kitchen']);
    $tabs = array_filter([
        'kitchen.index' => ['Board', 'fa-fire'],
        'kitchen.orders.index' => ['Orders', 'fa-receipt'],
        'kitchen.menu.index' => $canEditMenu ? ['Menu', 'fa-book-open'] : null,
    ]);
@endphp

<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ $title }}</h2>
        <p class="text-gray-600 dark:text-gray-400">{{ $subtitle }}</p>
    </div>
    @unless ($hideNewOrder ?? false)
        <button type="button" @click="$dispatch('open-order-form')"
                class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-primary/90">
            <i class="fas fa-plus"></i> New Order
        </button>
    @endunless
</div>

@isset($stats)
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @include('partials.stat-card', ['label' => 'On the Board', 'value' => $stats['active'], 'icon' => 'fa-fire', 'tone' => 'warning', 'href' => route('kitchen.index')])
        @include('partials.stat-card', ['label' => 'Orders Today', 'value' => $stats['orders_today'], 'icon' => 'fa-receipt', 'tone' => 'primary', 'href' => route('kitchen.orders.index', ['date' => today()->toDateString()])])
        @include('partials.stat-card', ['label' => 'Sales Today', 'value' => $money($stats['sales_today']), 'icon' => 'fa-coins', 'tone' => 'success'])
        @include('partials.stat-card', ['label' => 'Charged to Rooms Today', 'value' => $money($stats['room_charges_today']), 'icon' => 'fa-bed', 'tone' => 'info'])
    </div>
@endisset

<div class="flex gap-2 border-b border-gray-200 dark:border-gray-800">
    @foreach ($tabs as $route => [$label, $icon])
        <a href="{{ route($route) }}"
           class="-mb-px inline-flex items-center gap-2 border-b-2 px-4 py-2 text-sm font-medium {{ request()->routeIs($route) ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
            <i class="fas {{ $icon }}"></i> {{ $label }}
        </a>
    @endforeach
</div>
