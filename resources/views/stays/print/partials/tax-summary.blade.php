{{-- Totals with the VAT / Tourism Levy breakdown, shared by invoice and receipt. --}}
@php
    $money = fn ($value) => SystemHelper::currencySymbol() . ' ' . number_format((float) $value, 2);
    $balance = round((float) $invoice->total - (float) $invoice->paid_amount, 2);
@endphp
<table class="ml-auto w-full max-w-xs text-sm">
    @if ($invoice->tax_inclusive)
        <tr><td class="py-1 text-gray-500">Amount before tax</td><td class="py-1 text-right">{{ $money($invoice->subtotal) }}</td></tr>
        <tr><td class="py-1 text-gray-500">VAT {{ (float) $invoice->vat_rate }}%</td><td class="py-1 text-right">{{ $money($invoice->vat_amount) }}</td></tr>
        <tr><td class="py-1 text-gray-500">Tourism Levy {{ (float) $invoice->levy_rate }}%</td><td class="py-1 text-right">{{ $money($invoice->levy_amount) }}</td></tr>
        @if ((float) $invoice->discount_amount > 0)
            <tr><td class="py-1 text-gray-500">Discount</td><td class="py-1 text-right">− {{ $money($invoice->discount_amount) }}</td></tr>
        @endif
        <tr class="border-t border-gray-300 font-semibold"><td class="py-2">Total (tax inclusive)</td><td class="py-2 text-right">{{ $money($invoice->total) }}</td></tr>
    @else
        <tr><td class="py-1 text-gray-500">Subtotal</td><td class="py-1 text-right">{{ $money($invoice->subtotal) }}</td></tr>
        <tr><td class="py-1 text-gray-500">VAT {{ (float) $invoice->vat_rate }}%</td><td class="py-1 text-right">{{ $money($invoice->vat_amount) }}</td></tr>
        <tr><td class="py-1 text-gray-500">Tourism Levy {{ (float) $invoice->levy_rate }}%</td><td class="py-1 text-right">{{ $money($invoice->levy_amount) }}</td></tr>
        <tr class="border-t border-gray-300 font-semibold"><td class="py-2">Total</td><td class="py-2 text-right">{{ $money($invoice->total) }}</td></tr>
    @endif
    <tr><td class="py-1 text-gray-500">Paid to date</td><td class="py-1 text-right">{{ $money($invoice->paid_amount) }}</td></tr>
    <tr class="font-semibold"><td class="py-1">Balance due</td><td class="py-1 text-right">{{ $money($balance) }}</td></tr>
</table>
