{{-- Hotel at a glance (managers). --}}
@php $money = fn ($v) => SystemHelper::currencySymbol() . ' ' . number_format((float) $v, 0); @endphp

<section class="space-y-4">
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @include('partials.stat-card', ['label' => "Occupied tonight ({$data['occupied']}/{$data['rooms']})", 'value' => $data['occupancy'] . '%', 'icon' => 'fa-bed', 'tone' => 'primary', 'href' => route('rooms.index')])
        @include('partials.stat-card', ['label' => 'Arriving today', 'value' => $data['arrivals'], 'icon' => 'fa-plane-arrival', 'tone' => 'info', 'href' => route('stays.index')])
        @include('partials.stat-card', ['label' => 'Leaving today', 'value' => $data['departures'], 'icon' => 'fa-plane-departure', 'tone' => 'warning', 'href' => route('stays.index')])
        @include('partials.stat-card', ['label' => 'Guests in house', 'value' => $data['in_house'], 'icon' => 'fa-users', 'tone' => 'success', 'href' => route('guests.index', ['status' => 'in_house'])])
        @include('partials.stat-card', ['label' => 'Collected today', 'value' => $money($data['collected_today']), 'icon' => 'fa-coins', 'tone' => 'success', 'href' => route('payments.index')])
        @include('partials.stat-card', ['label' => 'Collected this month', 'value' => $money($data['collected_month']), 'icon' => 'fa-chart-line', 'tone' => 'primary', 'href' => route('reports.show', 'revenue')])
        @include('partials.stat-card', ['label' => 'Owed to the hotel', 'value' => $money($data['outstanding']), 'icon' => 'fa-hand-holding-usd', 'tone' => 'info', 'href' => route('reports.show', 'debtors')])
        @include('partials.stat-card', ['label' => 'Overdue', 'value' => $money($data['overdue']), 'icon' => 'fa-exclamation-circle', 'tone' => 'error', 'href' => route('invoices.index', ['status' => 'overdue'])])
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <a href="{{ route('kitchen.index') }}" class="flex items-center justify-between rounded-2xl border border-gray-100 bg-white px-5 py-4 hover:border-primary/40 dark:border-gray-800 dark:bg-gray-900">
            <span class="text-sm text-gray-600 dark:text-gray-400"><i class="fas fa-fire mr-2 text-warning-500"></i>Orders in the kitchen</span>
            <span class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $data['kitchen_active'] }}</span>
        </a>
        <a href="{{ route('tasks.index', ['view' => 'overdue']) }}" class="flex items-center justify-between rounded-2xl border border-gray-100 bg-white px-5 py-4 hover:border-primary/40 dark:border-gray-800 dark:bg-gray-900">
            <span class="text-sm text-gray-600 dark:text-gray-400"><i class="fas fa-tasks mr-2 text-error-500"></i>Overdue tasks</span>
            <span class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $data['tasks_overdue'] }}</span>
        </a>
        <a href="{{ route('inventory.index', ['stock' => 'low']) }}" class="flex items-center justify-between rounded-2xl border border-gray-100 bg-white px-5 py-4 hover:border-primary/40 dark:border-gray-800 dark:bg-gray-900">
            <span class="text-sm text-gray-600 dark:text-gray-400"><i class="fas fa-boxes mr-2 text-warning-500"></i>Stock running low</span>
            <span class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $data['low_stock'] }}</span>
        </a>
    </div>

    @if ($data['events_today']->isNotEmpty())
        @component('dashboard.partials.panel', ['title' => 'Events today', 'href' => route('events.index', ['when' => 'upcoming'])])
            <ul class="divide-y divide-gray-100 text-sm dark:divide-gray-800">
                @foreach ($data['events_today'] as $event)
                    <li class="flex flex-wrap items-center justify-between gap-2 px-5 py-3">
                        <a href="{{ route('events.show', $event) }}" class="font-medium text-gray-800 hover:text-primary dark:text-white/90">{{ $event->title }}</a>
                        <span class="text-gray-500 dark:text-gray-400">{{ $event->client_name }} · {{ $event->facility->name ?? '' }} · {{ $event->pax }} pax{{ $event->time_range ? ' · ' . $event->time_range : '' }}</span>
                    </li>
                @endforeach
            </ul>
        @endcomponent
    @endif
</section>
