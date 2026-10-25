{{-- Things waiting for a manager's decision. Hidden when there are none. --}}
@php
    $money = fn ($v) => SystemHelper::currencySymbol() . ' ' . number_format((float) $v, 2);
    $count = $data['purchase_orders']->count() + $data['leave']->count() + $data['payroll_drafts']->count() + $data['tentative_events']->count();
@endphp

@if ($count)
    @component('dashboard.partials.panel', ['title' => "Waiting for you ({$count})"])
        <ul class="divide-y divide-gray-100 text-sm dark:divide-gray-800">
            @foreach ($data['purchase_orders'] as $order)
                <li class="flex flex-wrap items-center justify-between gap-2 px-5 py-3">
                    <span><i class="fas fa-file-invoice mr-2 text-gray-400"></i>Purchase order <a href="{{ route('purchase-orders.show', $order) }}" class="font-medium text-gray-800 hover:text-primary dark:text-white/90">{{ $order->po_number }}</a> from {{ $order->supplier->name ?? '—' }}</span>
                    <span class="text-gray-600 dark:text-gray-400">{{ $money($order->total) }} · <a href="{{ route('purchase-orders.show', $order) }}" class="text-primary hover:underline">Review</a></span>
                </li>
            @endforeach
            @foreach ($data['leave'] as $leave)
                <li class="flex flex-wrap items-center justify-between gap-2 px-5 py-3">
                    <span><i class="fas fa-umbrella-beach mr-2 text-gray-400"></i>{{ $leave->type_label }} leave for <span class="font-medium text-gray-800 dark:text-white/90">{{ $leave->user->name ?? '—' }}</span>, {{ $leave->start_date->format('d M') }} → {{ $leave->end_date->format('d M') }}</span>
                    <a href="{{ route('hr.leave.index', ['status' => 'pending']) }}" class="text-primary hover:underline">Review</a>
                </li>
            @endforeach
            @foreach ($data['payroll_drafts'] as $draft)
                <li class="flex flex-wrap items-center justify-between gap-2 px-5 py-3">
                    <span><i class="fas fa-money-check-alt mr-2 text-gray-400"></i>Payroll for {{ \Carbon\Carbon::parse($draft->period_month)->format('F Y') }}: {{ $draft->staff }} staff, net {{ $money($draft->net) }}</span>
                    <a href="{{ route('hr.payroll.index', ['month' => \Carbon\Carbon::parse($draft->period_month)->format('Y-m')]) }}" class="text-primary hover:underline">Approve</a>
                </li>
            @endforeach
            @foreach ($data['tentative_events'] as $event)
                <li class="flex flex-wrap items-center justify-between gap-2 px-5 py-3">
                    <span><i class="fas fa-hourglass-half mr-2 text-gray-400"></i>Tentative event <a href="{{ route('events.show', $event) }}" class="font-medium text-gray-800 hover:text-primary dark:text-white/90">{{ $event->title }}</a> on {{ $event->start_date->format('D d M') }}, holding {{ $event->facility->name ?? 'the venue' }}</span>
                    <a href="{{ route('events.show', $event) }}" class="text-primary hover:underline">Confirm or release</a>
                </li>
            @endforeach
        </ul>
    @endcomponent
@endif
