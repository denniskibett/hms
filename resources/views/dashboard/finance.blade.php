{{-- Money in, money owed, payroll to pay. --}}
@php $money = fn ($v) => SystemHelper::currencySymbol() . ' ' . number_format((float) $v, 2); @endphp

<section class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Finance</h3>
        <a href="{{ route('finance.index') }}" class="text-sm text-primary hover:underline">Finance overview</a>
    </div>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @include('partials.stat-card', ['label' => 'Collected today', 'value' => $money($data['collected_today']), 'icon' => 'fa-coins', 'tone' => 'success', 'href' => route('payments.index')])
        @include('partials.stat-card', ['label' => 'Collected this month', 'value' => $money($data['collected_month']), 'icon' => 'fa-chart-line', 'tone' => 'primary'])
        @include('partials.stat-card', ['label' => 'Owed to the hotel', 'value' => $money($data['outstanding']), 'icon' => 'fa-hand-holding-usd', 'tone' => 'info', 'href' => route('reports.show', 'debtors')])
        @include('partials.stat-card', ['label' => 'Overdue', 'value' => $money($data['overdue']), 'icon' => 'fa-exclamation-circle', 'tone' => 'error', 'href' => route('invoices.index', ['status' => 'overdue'])])
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="xl:col-span-2">
            @component('dashboard.partials.panel', ['title' => 'Overdue invoices', 'href' => route('invoices.index', ['status' => 'overdue'])])
                <ul class="divide-y divide-gray-100 text-sm dark:divide-gray-800">
                    @forelse ($data['overdue_invoices'] as $invoice)
                        <li class="flex flex-wrap items-center justify-between gap-2 px-5 py-3">
                            <span>
                                <a href="{{ route('invoices.show', $invoice) }}" class="font-medium text-gray-800 hover:text-primary dark:text-white/90">{{ $invoice->invoice_number }}</a>
                                <span class="text-gray-500 dark:text-gray-400">· {{ $invoice->billed_to ?? '—' }} · due {{ $invoice->due_date?->format('d M') }}</span>
                            </span>
                            <span class="font-medium text-error-600 dark:text-error-400">{{ $money($invoice->balance) }}</span>
                        </li>
                    @empty
                        <li class="px-5 py-4 text-gray-500 dark:text-gray-400">Nothing overdue.</li>
                    @endforelse
                </ul>
            @endcomponent
        </div>
        @component('dashboard.partials.panel', ['title' => 'Payroll to pay', 'href' => route('hr.payroll.index')])
            <ul class="divide-y divide-gray-100 text-sm dark:divide-gray-800">
                @forelse ($data['payroll_to_pay'] as $run)
                    <li class="flex items-center justify-between px-5 py-3">
                        <a href="{{ route('hr.payroll.index', ['month' => \Carbon\Carbon::parse($run->period_month)->format('Y-m')]) }}" class="text-gray-800 hover:text-primary dark:text-white/90">{{ \Carbon\Carbon::parse($run->period_month)->format('F Y') }} ({{ $run->staff }})</a>
                        <span class="font-medium text-gray-800 dark:text-white/90">{{ $money($run->net) }}</span>
                    </li>
                @empty
                    <li class="px-5 py-4 text-gray-500 dark:text-gray-400">No approved payroll waiting.</li>
                @endforelse
            </ul>
        @endcomponent
    </div>
</section>
