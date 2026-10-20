@extends('layouts.app')

@php
    $frontOffice = auth()->user()->hasAnyRole(['admin', 'manager', 'receptionist']);
    $currency = SystemHelper::currencySymbol();
    $money = fn ($value) => $currency . ' ' . number_format((float) $value, 2);
    $dateFormat = 'D, ' . SystemHelper::dateFormat();
    $guest = $stay->guest;
    $profile = $guest?->guestProfile;
    $cancelled = $invoice->status === 'cancelled';
@endphp

@section('content')
<div x-data="{ pageName: @js('Invoice ' . $invoice->invoice_number) }">
    @include('partials.breadcrumb')
</div>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('invoices.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                <i class="fas fa-arrow-left"></i> Back to Invoices
            </a>
            <div class="mt-2 flex items-center gap-3">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ $invoice->invoice_number }}</h2>
                @include('partials.status-badge', ['status' => $invoice->display_status])
            </div>
            <p class="text-gray-600 dark:text-gray-400">
                Issued {{ $invoice->issue_date?->format($dateFormat) }} · Due {{ $invoice->due_date?->format($dateFormat) }}
            </p>
        </div>
        @if ($frontOffice)
            <a href="{{ route('stays.show', $stay) }}"
               class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-800 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-white/[0.03]">
                <i class="fas fa-calendar-check"></i> Open Booking
            </a>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            @if ($cancelled)
                <!-- Cancelled invoices are kept for the record, read-only -->
                <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                        <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Cancelled invoice</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">The booking was cancelled before anything was paid.</p>
                    </div>
                    <table class="min-w-full text-sm">
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($invoice->items as $item)
                                <tr>
                                    <td class="px-5 py-2 text-gray-500 line-through dark:text-gray-400">{{ $item->description }}</td>
                                    <td class="whitespace-nowrap px-5 py-2 text-right text-gray-500 line-through dark:text-gray-400">{{ $money((float) $item->quantity * (float) $item->unit_price) }}</td>
                                </tr>
                            @empty
                                <tr><td class="px-5 py-4 text-gray-500 dark:text-gray-400">No lines.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                @include('stays.partials.folio')
            @endif
        </div>

        <!-- Guest & booking -->
        <div class="space-y-6">
            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Guest</h3>
                    @if ($frontOffice && $profile)
                        <a href="{{ route('guests.show', $profile) }}" class="text-sm text-primary hover:underline">Profile</a>
                    @endif
                </div>
                <dl class="space-y-3 p-5 text-sm">
                    <div><dt class="text-gray-500 dark:text-gray-400">Name</dt><dd class="font-medium text-gray-800 dark:text-white/90">{{ $guest->name ?? '—' }}</dd></div>
                    @if ($guest?->phone)
                        <div><dt class="text-gray-500 dark:text-gray-400">Phone</dt><dd><a href="tel:{{ $guest->phone }}" class="text-gray-800 hover:text-primary dark:text-white/90">{{ $guest->phone }}</a></dd></div>
                    @endif
                    @if ($guest?->email)
                        <div><dt class="text-gray-500 dark:text-gray-400">Email</dt><dd><a href="mailto:{{ $guest->email }}" class="break-all text-gray-800 hover:text-primary dark:text-white/90">{{ $guest->email }}</a></dd></div>
                    @endif
                </dl>
            </div>

            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Booking #{{ $stay->id }}</h3>
                </div>
                <dl class="space-y-3 p-5 text-sm">
                    <div><dt class="text-gray-500 dark:text-gray-400">Status</dt><dd class="mt-1">@include('partials.status-badge', ['status' => $stay->state])</dd></div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Dates</dt>
                        <dd class="text-gray-800 dark:text-white/90">{{ $stay->arrival_date->format($dateFormat) }} → {{ $stay->departure_date->format($dateFormat) }}</dd>
                        <dd class="text-xs text-gray-500 dark:text-gray-400">{{ $stay->nights }} {{ Str::plural('night', $stay->nights) }} · {{ $stay->meal_plan->value }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Rooms</dt>
                        <dd class="text-gray-800 dark:text-white/90">{{ $stay->roomAllocations->map(fn ($a) => $a->room?->room_number)->filter()->implode(', ') ?: '—' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
