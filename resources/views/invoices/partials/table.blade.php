{{--
    Invoice rows.
    @include('invoices.partials.table', [
        'invoices' => $invoices,   // collection or paginator; load stay.guest + facilityBooking
        'empty' => 'No invoices',  // optional empty-state text
    ])
--}}
@php
    $currency = SystemHelper::currencySymbol();
    $money = fn ($value) => $currency . ' ' . number_format((float) $value, 2);
    $dateFormat = SystemHelper::dateFormat();
@endphp

<div class="custom-scrollbar overflow-x-auto">
    <table class="min-w-full text-left text-sm text-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-900">
            <tr class="whitespace-nowrap border-b border-gray-100 dark:border-gray-800">
                <th class="px-5 py-3 font-medium text-gray-700 dark:text-gray-400">Invoice</th>
                <th class="px-5 py-3 font-medium text-gray-700 dark:text-gray-400">Guest</th>
                <th class="px-5 py-3 font-medium text-gray-700 dark:text-gray-400">Issued / Due</th>
                <th class="px-5 py-3 text-right font-medium text-gray-700 dark:text-gray-400">Total</th>
                <th class="px-5 py-3 text-right font-medium text-gray-700 dark:text-gray-400">Paid</th>
                <th class="px-5 py-3 text-right font-medium text-gray-700 dark:text-gray-400">Balance</th>
                <th class="px-5 py-3 font-medium text-gray-700 dark:text-gray-400">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse ($invoices as $invoice)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                    <td class="whitespace-nowrap px-5 py-3">
                        <a href="{{ route('invoices.show', $invoice) }}" class="font-medium text-gray-800 hover:text-primary dark:text-white/90">{{ $invoice->invoice_number }}</a>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $invoice->source_label }}</p>
                    </td>
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-800 dark:text-white/90">{{ $invoice->billed_to ?? '—' }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $invoice->billed_to_phone }}</p>
                    </td>
                    <td class="whitespace-nowrap px-5 py-3">
                        <p class="text-gray-800 dark:text-white/90">{{ $invoice->issue_date?->format($dateFormat) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Due {{ $invoice->due_date?->format($dateFormat) }}</p>
                    </td>
                    <td class="whitespace-nowrap px-5 py-3 text-right text-gray-800 dark:text-white/90">{{ $money($invoice->total) }}</td>
                    <td class="whitespace-nowrap px-5 py-3 text-right text-success-600 dark:text-success-500">{{ $money($invoice->paid_amount) }}</td>
                    <td class="whitespace-nowrap px-5 py-3 text-right font-medium {{ $invoice->balance > 0 && $invoice->status !== 'cancelled' ? 'text-error-600 dark:text-error-400' : 'text-gray-500 dark:text-gray-400' }}">
                        {{ $money($invoice->status === 'cancelled' ? 0 : $invoice->balance) }}
                    </td>
                    <td class="px-5 py-3">@include('partials.status-badge', ['status' => $invoice->display_status])</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                        <i class="fas fa-file-invoice mb-3 text-4xl"></i>
                        <p>{{ $empty ?? 'No invoices' }}</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
