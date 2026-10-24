{{-- Store header and tabs. @include('inventory.partials.header', ['title' => ..., 'subtitle' => ...]) --}}
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ $title }}</h2>
        <p class="text-gray-600 dark:text-gray-400">{{ $subtitle }}</p>
    </div>
    @if ($canManage && ! ($hideNew ?? false))
        <button type="button" @click="$dispatch('open-item-form')" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-primary/90">
            <i class="fas fa-plus"></i> New Item
        </button>
    @endif
</div>

<div class="flex gap-2 border-b border-gray-200 dark:border-gray-800">
    @foreach (['inventory.index' => ['Stock', 'fa-boxes'], 'inventory.movements' => ['Movements', 'fa-exchange-alt']] as $route => [$label, $icon])
        <a href="{{ route($route) }}" class="-mb-px inline-flex items-center gap-2 border-b-2 px-4 py-2 text-sm font-medium {{ request()->routeIs($route) ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400' }}">
            <i class="fas {{ $icon }}"></i> {{ $label }}
        </a>
    @endforeach
</div>
