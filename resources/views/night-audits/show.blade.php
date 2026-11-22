@extends('layouts.app')

@php $money = fn ($v) => SystemHelper::currencySymbol() . ' ' . number_format((float) $v, 2); @endphp

@section('content')
@include('partials.page-header', ['title' => 'Night audit'])

<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a href="{{ route('night-audits.index') }}" class="text-sm text-gray-500 hover:text-brand-600"><i class="fas fa-arrow-left text-xs"></i> Night audit</a>
            <h2 class="mt-1 text-2xl font-bold text-gray-800">Day closed: {{ $audit->business_date->format('l d F Y') }}</h2>
            <p class="text-sm text-gray-500">By {{ $audit->runner?->name ?? 'the automatic run' }} at {{ $audit->run_at->format('d M Y, H:i') }}</p>
        </div>
        <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 print:hidden"><i class="fas fa-print"></i> Print</button>
    </div>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @include('partials.stat-card', ['label' => 'Occupancy', 'value' => $audit->figure('occupancy_percent') . '%', 'icon' => 'fa-bed', 'tone' => 'primary'])
        @include('partials.stat-card', ['label' => 'Room revenue', 'value' => $money($audit->figure('room_revenue')), 'icon' => 'fa-coins', 'tone' => 'success'])
        @include('partials.stat-card', ['label' => 'Average room rate (ADR)', 'value' => $money($audit->figure('adr')), 'icon' => 'fa-tag', 'tone' => 'info'])
        @include('partials.stat-card', ['label' => 'Revenue per available room', 'value' => $money($audit->figure('revpar')), 'icon' => 'fa-chart-line', 'tone' => 'info'])
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <section class="rounded-2xl border border-gray-200 bg-white p-5">
            <h3 class="mb-3 text-sm font-semibold text-gray-700">The night</h3>
            <dl class="grid grid-cols-2 gap-y-2 text-sm">
                <dt class="text-gray-500">Rooms sold</dt><dd class="text-right font-medium">{{ $audit->figure('rooms_sold') }} of {{ $audit->figure('rooms_sellable') }}</dd>
                <dt class="text-gray-500">Guests</dt><dd class="text-right font-medium">{{ $audit->figure('guests') }}</dd>
                <dt class="text-gray-500">Arrivals</dt><dd class="text-right font-medium">{{ $audit->figure('arrivals') }}</dd>
                <dt class="text-gray-500">Departures</dt><dd class="text-right font-medium">{{ $audit->figure('departures') }}</dd>
                <dt class="text-gray-500">New bookings made</dt><dd class="text-right font-medium">{{ $audit->figure('new_bookings') }}</dd>
                <dt class="text-gray-500">In house now</dt><dd class="text-right font-medium">{{ $audit->figure('in_house') }}</dd>
                <dt class="text-gray-500">In-house guests owe</dt><dd class="text-right font-semibold">{{ $money($audit->figure('in_house_owed')) }}</dd>
            </dl>
        </section>
        <section class="rounded-2xl border border-gray-200 bg-white p-5">
            <h3 class="mb-3 text-sm font-semibold text-gray-700">Money taken</h3>
            <dl class="grid grid-cols-2 gap-y-2 text-sm">
                @forelse ($audit->figure('payments', []) as $row)
                    <dt class="text-gray-500">{{ $row['label'] }} <span class="text-xs">({{ $row['count'] }})</span></dt><dd class="text-right font-medium">{{ $money($row['total']) }}</dd>
                @empty
                    <dt class="col-span-2 text-gray-500">No payments that day.</dt>
                @endforelse
                <dt class="border-t border-gray-100 pt-2 font-semibold text-gray-700">Total</dt><dd class="border-t border-gray-100 pt-2 text-right font-semibold">{{ $money($audit->figure('payments_total')) }}</dd>
            </dl>
        </section>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <section class="rounded-2xl border border-gray-200 bg-white p-5">
            <h3 class="mb-3 text-sm font-semibold text-gray-700">No-shows marked</h3>
            @forelse ($audit->figure('no_shows', []) as $row)
                <a href="{{ route('stays.show', $row['stay']) }}" class="flex justify-between rounded-lg px-2 py-2 text-sm hover:bg-gray-50">
                    <span>Booking #{{ $row['stay'] }} · {{ $row['guest'] }}</span>
                    <span class="{{ $row['paid'] > 0 ? 'font-medium text-warning-700' : 'text-gray-500' }}">{{ $row['paid'] > 0 ? 'Paid ' . $money($row['paid']) . ': refund or keep?' : 'Invoice cancelled' }}</span>
                </a>
            @empty
                <p class="text-sm text-gray-500">None.</p>
            @endforelse
        </section>
        <section class="rounded-2xl border border-gray-200 bg-white p-5">
            <h3 class="mb-3 text-sm font-semibold text-gray-700">Overstays (still in house after departure)</h3>
            @forelse ($audit->figure('overstays', []) as $row)
                <a href="{{ route('stays.show', $row['stay']) }}" class="flex justify-between rounded-lg px-2 py-2 text-sm hover:bg-gray-50">
                    <span>Booking #{{ $row['stay'] }} · {{ $row['guest'] }}</span><span class="text-error-600">Due out {{ \Carbon\Carbon::parse($row['departure'])->format('d M') }}</span>
                </a>
            @empty
                <p class="text-sm text-gray-500">None.</p>
            @endforelse
        </section>
    </div>

    @if ($audit->notes)
        <section class="rounded-2xl border border-gray-200 bg-white p-5 text-sm"><h3 class="mb-2 font-semibold text-gray-700">Notes</h3><p class="whitespace-pre-line text-gray-600">{{ $audit->notes }}</p></section>
    @endif
</div>
@endsection
