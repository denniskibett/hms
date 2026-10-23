@extends('layouts.print')

@php
    $money = fn ($value) => SystemHelper::currencySymbol() . ' ' . number_format((float) $value, 2);
@endphp

@section('title', 'Receipt ' . $payment->payment_reference)

@section('document-heading')
    <p class="text-2xl font-bold uppercase tracking-wide text-primary">Receipt</p>
    <p class="text-sm font-medium">{{ $payment->payment_reference }}</p>
    <p class="text-sm text-gray-500">{{ $payment->received_at?->format('d M Y, H:i') }}</p>
    @if ($payment->status !== 'completed')
        <p class="mt-1 inline-block rounded bg-error-50 px-2 py-0.5 text-xs font-semibold uppercase text-error-700">Voided</p>
    @endif
@endsection

@section('content')
    <section class="py-6 text-center">
        <p class="text-sm text-gray-500">Received with thanks from</p>
        <p class="text-lg font-semibold">{{ $event->client_name }}</p>
        <p class="mt-4 text-sm text-gray-500">The sum of</p>
        <p class="text-3xl font-bold">{{ $money($payment->amount) }}</p>
        <p class="mt-2 text-sm text-gray-600">
            by {{ $methods[$payment->method] ?? $payment->method }}
            @if (! str_starts_with($payment->payment_reference, 'PAY-'))
                · Ref {{ $payment->payment_reference }}
            @endif
        </p>
        @if ($payment->notes)
            <p class="mt-1 text-sm text-gray-500">{{ $payment->notes }}</p>
        @endif
    </section>

    <section class="grid grid-cols-2 gap-6 border-t border-gray-200 py-6 text-sm">
        <div>
            <p class="mb-1 text-xs font-semibold uppercase text-gray-500">For</p>
            <p>{{ $event->title }} ({{ $event->reference }}) · Invoice {{ $invoice->invoice_number }}</p>
            <p>{{ $event->start_date->format('d M Y') }}{{ $event->days > 1 ? ' → ' . $event->end_date->format('d M Y') : '' }}</p>
            <p>{{ $event->facility->name ?? '' }}</p>
            <p class="mt-3 text-gray-500">Received by {{ $payment->receiver->name ?? '—' }}</p>
        </div>
        <div>
            @include('stays.print.partials.tax-summary')
        </div>
    </section>
@endsection
