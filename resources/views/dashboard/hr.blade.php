{{-- People: leave, who is away, contracts, this month's payroll. --}}
@php
    $payroll = $data['payroll_month'];
    $payrollText = $payroll->isEmpty() ? 'Not prepared yet' : collect($payroll)->map(fn ($n, $status) => $n . ' ' . (\App\Models\Payroll::STATUSES[$status] ?? $status))->implode(', ');
@endphp

<section class="space-y-4">
    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">People</h3>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @include('partials.stat-card', ['label' => 'Staff', 'value' => $data['staff'], 'icon' => 'fa-users', 'tone' => 'primary', 'href' => route('hr.staff.index')])
        @include('partials.stat-card', ['label' => 'Leave to approve', 'value' => $data['pending_leave']->count(), 'icon' => 'fa-hourglass-half', 'tone' => 'warning', 'href' => route('hr.leave.index', ['status' => 'pending'])])
        @include('partials.stat-card', ['label' => 'Away today', 'value' => $data['away_today']->count(), 'icon' => 'fa-umbrella-beach', 'tone' => 'info', 'href' => route('hr.leave.index', ['status' => 'approved'])])
        @include('partials.stat-card', ['label' => now()->format('F') . ' payroll', 'value' => $payrollText, 'icon' => 'fa-money-check-alt', 'tone' => 'success', 'href' => route('hr.payroll.index')])
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        @component('dashboard.partials.panel', ['title' => 'Leave to approve', 'href' => route('hr.leave.index', ['status' => 'pending'])])
            <ul class="divide-y divide-gray-100 text-sm dark:divide-gray-800">
                @forelse ($data['pending_leave'] as $leave)
                    <li class="px-5 py-3">
                        <p class="font-medium text-gray-800 dark:text-white/90">{{ $leave->user->name ?? '—' }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $leave->type_label }} · {{ $leave->start_date->format('d M') }} → {{ $leave->end_date->format('d M') }} ({{ $leave->days }} days)</p>
                    </li>
                @empty
                    <li class="px-5 py-4 text-gray-500 dark:text-gray-400">None waiting.</li>
                @endforelse
            </ul>
        @endcomponent
        @component('dashboard.partials.panel', ['title' => 'Away today'])
            <ul class="divide-y divide-gray-100 text-sm dark:divide-gray-800">
                @forelse ($data['away_today'] as $leave)
                    <li class="flex justify-between px-5 py-3">
                        <span class="text-gray-800 dark:text-white/90">{{ $leave->user->name ?? '—' }}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $leave->type_label }}, back {{ $leave->end_date->copy()->addDay()->format('D d M') }}</span>
                    </li>
                @empty
                    <li class="px-5 py-4 text-gray-500 dark:text-gray-400">Everyone is in.</li>
                @endforelse
            </ul>
        @endcomponent
        @component('dashboard.partials.panel', ['title' => 'Contracts ending in 30 days'])
            <ul class="divide-y divide-gray-100 text-sm dark:divide-gray-800">
                @forelse ($data['contracts_ending'] as $staff)
                    <li class="flex justify-between px-5 py-3">
                        <a href="{{ route('hr.staff.show', $staff) }}" class="text-gray-800 hover:text-primary dark:text-white/90">{{ $staff->user->name ?? '—' }}</a>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $staff->status_label }}, ends {{ $staff->contract_end_date->format('d M') }}</span>
                    </li>
                @empty
                    <li class="px-5 py-4 text-gray-500 dark:text-gray-400">None.</li>
                @endforelse
            </ul>
        @endcomponent
    </div>
</section>
