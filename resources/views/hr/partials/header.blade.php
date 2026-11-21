{{--
    HR page header and tabs.
    @include('hr.partials.header', ['title' => ..., 'subtitle' => ..., 'actions' => 'optional html'])
--}}
@php
    $isHr = auth()->user()->hasAnyRole(['admin', 'manager', 'hr']);
    $tabs = array_filter([
        'hr.staff.*' => $isHr ? ['Staff', 'fa-users', 'hr.staff.index'] : null,
        'hr.rota.*' => $isHr ? ['Rota', 'fa-calendar-week', 'hr.rota.index'] : null,
        'hr.leave.*' => $isHr ? ['Leave', 'fa-umbrella-beach', 'hr.leave.index'] : null,
        'hr.payroll.*' => ['Payroll', 'fa-money-check-alt', 'hr.payroll.index'],
    ]);
@endphp

<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ $title }}</h2>
        <p class="text-gray-600 dark:text-gray-400">{{ $subtitle }}</p>
    </div>
    @isset($actions)
        <div class="flex flex-wrap gap-2">{!! $actions !!}</div>
    @endisset
</div>

<div class="flex gap-2 overflow-x-auto border-b border-gray-200 dark:border-gray-800">
    @foreach ($tabs as $pattern => [$label, $icon, $route])
        <a href="{{ route($route) }}"
           class="-mb-px inline-flex items-center gap-2 whitespace-nowrap border-b-2 px-4 py-2 text-sm font-medium {{ request()->routeIs($pattern) ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
            <i class="fas {{ $icon }}"></i> {{ $label }}
        </a>
    @endforeach
</div>
