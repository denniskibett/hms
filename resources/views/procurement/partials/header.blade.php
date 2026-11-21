{{-- Procurement header and tabs. @include('procurement.partials.header', ['title' => ..., 'subtitle' => ..., 'actions' => 'optional html']) --}}
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ $title }}</h2>
        <p class="text-gray-600 dark:text-gray-400">{{ $subtitle }}</p>
    </div>
    @isset($actions)
        <div class="flex flex-wrap gap-2">{!! $actions !!}</div>
    @endisset
</div>

<div class="flex gap-2 border-b border-gray-200 dark:border-gray-800">
    @foreach (['purchase-orders.*' => ['Purchase Orders', 'fa-file-invoice', 'purchase-orders.index'], 'suppliers.*' => ['Suppliers', 'fa-truck', 'suppliers.index']] as $pattern => [$label, $icon, $route])
        <a href="{{ route($route) }}" class="-mb-px inline-flex items-center gap-2 border-b-2 px-4 py-2 text-sm font-medium {{ request()->routeIs($pattern) ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400' }}">
            <i class="fas {{ $icon }}"></i> {{ $label }}
        </a>
    @endforeach
</div>
