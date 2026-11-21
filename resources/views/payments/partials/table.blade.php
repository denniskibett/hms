{{--
    Payment rows.
    @include('payments.partials.table', [
        'payments' => $payments,   // collection or paginator; load invoice.stay.guest + invoice.facilityBooking + invoice.kitchenOrder + receiver
        'empty' => 'No payments',  // optional empty-state text
    ])
--}}
@php
    $currency = SystemHelper::currencySymbol();
    $money = fn ($value) => $currency . ' ' . number_format((float) $value, 2);
@endphp

<div class="custom-scrollbar overflow-x-auto">
    <table class="min-w-full text-left text-sm text-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-900">
            <tr class="whitespace-nowrap border-b border-gray-100 dark:border-gray-800">
                <th class="px-5 py-3 font-medium text-gray-700 dark:text-gray-400">Received</th>
                <th class="px-5 py-3 font-medium text-gray-700 dark:text-gray-400">Reference</th>
                <th class="px-5 py-3 font-medium text-gray-700 dark:text-gray-400">Guest / Invoice</th>
                <th class="px-5 py-3 font-medium text-gray-700 dark:text-gray-400">Method</th>
                <th class="px-5 py-3 text-right font-medium text-gray-700 dark:text-gray-400">Amount</th>
                <th class="px-5 py-3 font-medium text-gray-700 dark:text-gray-400">Taken by</th>
                <th class="px-5 py-3 print:hidden"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse ($payments as $payment)
                @php $voided = $payment->status !== 'completed'; @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 {{ $voided ? 'opacity-60' : '' }}">
                    <td class="whitespace-nowrap px-5 py-3 text-gray-800 dark:text-white/90">{{ $payment->received_at?->format('d M Y, H:i') }}</td>
                    <td class="whitespace-nowrap px-5 py-3">
                        <p class="font-mono text-xs text-gray-800 dark:text-white/90">{{ $payment->payment_reference }}</p>
                        @if ($voided)
                            <p class="text-xs text-error-500">{{ $payment->status === 'cancelled' ? 'Voided' : ucfirst($payment->status) }}</p>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-800 dark:text-white/90">{{ $payment->invoice?->billed_to ?? '—' }}</p>
                        @if ($payment->invoice)
                            <a href="{{ route('invoices.show', $payment->invoice) }}" class="text-xs text-primary hover:underline">{{ $payment->invoice->invoice_number }}</a>
                        @endif
                    </td>
                    <td class="whitespace-nowrap px-5 py-3 text-gray-700 dark:text-gray-300">{{ $payment->method_label }}</td>
                    <td class="whitespace-nowrap px-5 py-3 text-right font-medium text-gray-800 dark:text-white/90 {{ $voided ? 'line-through' : '' }}">{{ $money($payment->amount) }}</td>
                    <td class="whitespace-nowrap px-5 py-3 text-gray-700 dark:text-gray-300">{{ $payment->receiver?->name ?? '—' }}</td>
                    <td class="whitespace-nowrap px-5 py-3 text-right print:hidden">
                        @if (! $voided && $payment->invoice?->receiptUrl($payment))
                            <a href="{{ $payment->invoice->receiptUrl($payment) }}" target="_blank" class="text-xs text-primary hover:underline">Receipt</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                        <i class="fas fa-money-bill-wave mb-3 text-4xl"></i>
                        <p>{{ $empty ?? 'No payments' }}</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
