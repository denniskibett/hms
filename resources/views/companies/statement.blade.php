@extends('layouts.print')

@php $money = fn ($v) => SystemHelper::currencySymbol() . ' ' . number_format((float) $v, 2); @endphp

@section('title', 'Statement · ' . $company->name)

@section('document-heading')
    <p class="text-2xl font-bold uppercase tracking-wide text-primary">Statement</p>
    <p class="text-sm text-gray-500">{{ today()->format('d M Y') }}</p>
@endsection

@section('content')
    <section class="mt-6 grid grid-cols-2 gap-6 text-sm">
        <div>
            <p class="text-xs uppercase text-gray-500">Account</p>
            <p class="font-semibold">{{ $company->name }}</p>
            @if ($company->contact_name)<p>Attn: {{ $company->contact_name }}</p>@endif
            @if ($company->address)<p>{{ $company->address }}</p>@endif
            @if ($company->kra_pin)<p>KRA PIN {{ $company->kra_pin }}</p>@endif
        </div>
        <div class="text-right">
            <p class="text-xs uppercase text-gray-500">Amount due</p>
            <p class="text-2xl font-bold">{{ $money($balance) }}</p>
            <p class="text-gray-500">Terms: {{ $company->payment_terms_days }} days</p>
        </div>
    </section>

    <table class="mt-6 w-full text-sm">
        <thead class="border-b border-gray-300 text-left"><tr>
            <th class="py-2">Invoice</th><th class="py-2">For</th><th class="py-2">Issued</th><th class="py-2">Due</th><th class="py-2 text-right">Total</th><th class="py-2 text-right">Paid</th><th class="py-2 text-right">Balance</th>
        </tr></thead>
        <tbody class="divide-y divide-gray-200">
            @forelse ($invoices as $invoice)
                <tr>
                    <td class="py-2">{{ $invoice->invoice_number }}</td>
                    <td class="py-2">{{ $invoice->stay ? ($invoice->stay->guest?->name ?? 'Guest') . ', ' . $invoice->stay->arrival_date?->format('d M') . '–' . $invoice->stay->departure_date?->format('d M') : ($invoice->facilityBooking?->title ?? '') }}</td>
                    <td class="py-2">{{ $invoice->issue_date?->format('d M Y') }}</td>
                    <td class="py-2">{{ $invoice->due_date?->format('d M Y') }}</td>
                    <td class="py-2 text-right">{{ number_format((float) $invoice->total, 2) }}</td>
                    <td class="py-2 text-right">{{ number_format((float) $invoice->paid_amount, 2) }}</td>
                    <td class="py-2 text-right font-semibold">{{ number_format($invoice->balance, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="py-6 text-center text-gray-500">Nothing outstanding. Thank you.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="mt-6 w-full text-center text-sm">
        <tr class="text-xs uppercase text-gray-500">@foreach ($ageing as $label => $amount)<td class="py-1">{{ $label }}</td>@endforeach</tr>
        <tr class="font-semibold">@foreach ($ageing as $amount)<td class="py-1">{{ number_format($amount, 2) }}</td>@endforeach</tr>
    </table>
    @php unset($invoice); @endphp {{-- the print layout's eTIMS block is for single invoices --}}
@endsection
