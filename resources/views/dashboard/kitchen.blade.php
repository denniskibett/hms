{{-- Kitchen at a glance. --}}
@php $money = fn ($v) => SystemHelper::currencySymbol() . ' ' . number_format((float) $v, 0); @endphp

<section class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Kitchen</h3>
        <a href="{{ route('kitchen.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/90"><i class="fas fa-fire"></i> Open the board</a>
    </div>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
        @include('partials.stat-card', ['label' => 'New orders', 'value' => $data['new'], 'icon' => 'fa-bell', 'tone' => 'warning', 'href' => route('kitchen.index')])
        @include('partials.stat-card', ['label' => 'Preparing', 'value' => $data['preparing'], 'icon' => 'fa-fire', 'tone' => 'info', 'href' => route('kitchen.index')])
        @include('partials.stat-card', ['label' => 'Ready to serve', 'value' => $data['ready'], 'icon' => 'fa-concierge-bell', 'tone' => 'success', 'href' => route('kitchen.index')])
        @include('partials.stat-card', ['label' => 'Orders today', 'value' => $data['orders_today'], 'icon' => 'fa-receipt', 'tone' => 'primary', 'href' => route('kitchen.orders.index', ['date' => today()->toDateString()])])
        @include('partials.stat-card', ['label' => 'Sales today', 'value' => $money($data['sales_today']), 'icon' => 'fa-coins', 'tone' => 'success'])
    </div>

    @if ($data['low_food']->isNotEmpty())
        @component('dashboard.partials.panel', ['title' => 'Kitchen stock running low', 'href' => route('inventory.index', ['stock' => 'low'])])
            <div class="flex flex-wrap gap-2 p-5">
                @foreach ($data['low_food'] as $item)
                    <a href="{{ route('inventory.show', $item) }}" class="rounded-lg bg-warning-50 px-3 py-1.5 text-sm text-warning-700 dark:bg-warning-500/15 dark:text-warning-400">{{ $item->name }}: {{ $item->qty() }}</a>
                @endforeach
            </div>
        @endcomponent
    @endif
</section>
