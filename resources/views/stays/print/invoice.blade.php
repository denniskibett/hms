@extends('layouts.print')

@php
    $money = fn ($value) => SystemHelper::currencySymbol() . ' ' . number_format((float) $value, 2);
    $guest = $stay->guest;
@endphp

@section('title', 'Invoice ' . $invoice->invoice_number)

@section('document-heading')
    <p class="text-2xl font-bold uppercase tracking-wide text-primary">Invoice</p>
    <p class="text-sm font-medium">{{ $invoice->invoice_number }}</p>
    <p class="text-sm text-gray-500">Issued {{ $invoice->issue_date?->format('d M Y') }}</p>
    @if ($invoice->status === 'paid')
        <p class="mt-1 inline-block rounded bg-success-50 px-2 py-0.5 text-xs font-semibold uppercase text-success-700">Paid</p>
    @elseif ($invoice->status === 'cancelled')
        <p class="mt-1 inline-block rounded bg-error-50 px-2 py-0.5 text-xs font-semibold uppercase text-error-700">Cancelled</p>
    @endif
@endsection

@section('content')
    <section class="grid grid-cols-2 gap-6 py-6 text-sm">
        <div>
            <p class="mb-1 text-xs font-semibold uppercase text-gray-500">Billed to</p>
            <p class="font-medium">{{ $guest->name ?? '—' }}</p>
            @if ($guest?->phone)<p>{{ $guest->phone }}</p>@endif
            @if ($guest?->email)<p>{{ $guest->email }}</p>@endif
            @if ($guest?->guestProfile?->id_number)
                <p class="text-gray-500">{{ Str::headline($guest->guestProfile->id_type ?? 'ID') }}: {{ $guest->guestProfile->id_number }}</p>
            @endif
        </div>
        <div class="text-right">
            <p class="mb-1 text-xs font-semibold uppercase text-gray-500">Stay</p>
            <p>Booking #{{ $stay->id }} · {{ $stay->meal_plan->label() }}</p>
            <p>{{ $stay->arrival_date->format('d M Y') }} → {{ $stay->departure_date->format('d M Y') }} ({{ $stay->nights }} {{ Str::plural('night', $stay->nights) }})</p>
            <p>{{ $stay->adults }} adults{{ $stay->children ? ', ' . $stay->children . ' children' : '' }}</p>
        </div>
    </section>

    <table class="w-full text-sm">
        <thead>
            <tr class="border-y border-gray-300 text-left text-xs uppercase text-gray-500">
                <th class="py-2">Description</th>
                <th class="py-2 text-right">Qty</th>
                <th class="py-2 text-right">Unit price</th>
                <th class="py-2 text-right">Amount</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach ($invoice->items as $item)
                <tr>
                    <td class="py-2">{{ $item->description }}</td>
                    <td class="py-2 text-right">{{ (float) $item->quantity }}</td>
                    <td class="py-2 text-right">{{ $money($item->unit_price) }}</td>
                    <td class="py-2 text-right">{{ $money((float) $item->quantity * (float) $item->unit_price) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-6">
        @include('stays.print.partials.tax-summary')
    </div>

    @if ($invoice->payments->isNotEmpty())
        <section class="mt-8 text-sm">
            <p class="mb-2 text-xs font-semibold uppercase text-gray-500">Payments received</p>
            <table class="w-full">
                @foreach ($invoice->payments as $payment)
                    <tr class="border-b border-gray-100">
                        <td class="py-1">{{ $payment->received_at?->format('d M Y') }}</td>
                        <td class="py-1">{{ \App\Http\Controllers\StayBillingController::METHODS[$payment->method] ?? $payment->method }}</td>
                        <td class="py-1 text-gray-500">{{ $payment->payment_reference }}</td>
                        <td class="py-1 text-right">{{ $money($payment->amount) }}</td>
                    </tr>
                @endforeach
            </table>
        </section>
    @endif
@endsection
