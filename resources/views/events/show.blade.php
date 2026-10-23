@extends('layouts.app')

@php
    use App\Enums\FacilityBookingStatus;

    $currency = SystemHelper::currencySymbol();
    $money = fn ($value) => $currency . ' ' . number_format((float) $value, 2);
    $dateFormat = 'D, ' . SystemHelper::dateFormat();
    $status = $event->status;
    $buttonClass = 'inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]';
@endphp

@section('content')
<div x-data="{ pageName: @js($event->title) }">
    @include('partials.breadcrumb')
</div>

<div class="space-y-6" x-data="{ cancelOpen: false }">
    @if (session('receipt_url'))
        <div class="flex items-center justify-between rounded-lg border border-primary/20 bg-primary/5 px-4 py-3 text-sm text-gray-700 dark:text-gray-300 print:hidden">
            <span><i class="fas fa-receipt mr-1 text-primary"></i> Payment saved. Print the receipt for the client?</span>
            <a href="{{ session('receipt_url') }}" target="_blank" class="rounded-lg bg-primary px-3 py-1.5 text-xs font-medium text-white hover:bg-primary/90">Print receipt</a>
        </div>
    @endif

    <!-- Header -->
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <a href="{{ route('events.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 print:hidden">
                <i class="fas fa-arrow-left"></i> Back to Events
            </a>
            <div class="mt-2 flex flex-wrap items-center gap-3">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ $event->title }}</h2>
                @include('partials.status-badge', ['status' => $status])
            </div>
            <p class="text-gray-600 dark:text-gray-400">
                {{ $event->reference }} · {{ $event->client_name }} · {{ $event->facility->name ?? '—' }} ·
                {{ $event->start_date->format($dateFormat) }}{{ $event->days > 1 ? ' → ' . $event->end_date->format($dateFormat) : '' }}
            </p>
        </div>

        <div class="flex flex-wrap gap-2 print:hidden">
            @if ($status->holdsVenue())
                <button type="button" @click="$dispatch('open-event-form', @js($event->formData()))" class="{{ $buttonClass }}">
                    <i class="fas fa-edit"></i> Edit
                </button>
            @endif
            @foreach ([[FacilityBookingStatus::Confirmed, 'fa-check', 'Confirm'], [FacilityBookingStatus::Tentative, 'fa-undo', 'Back to tentative'], [FacilityBookingStatus::Completed, 'fa-flag-checkered', 'Mark completed']] as [$target, $icon, $label])
                @if ($status->canTransitionTo($target))
                    <form method="POST" action="{{ route('events.status', $event) }}">
                        @csrf
                        <input type="hidden" name="status" value="{{ $target->value }}">
                        <button type="submit" class="{{ $target === FacilityBookingStatus::Tentative ? $buttonClass : 'inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-primary/90' }}">
                            <i class="fas {{ $icon }}"></i> {{ $label }}
                        </button>
                    </form>
                @endif
            @endforeach
            @if ($status->canTransitionTo(FacilityBookingStatus::Cancelled))
                <button type="button" @click="cancelOpen = true" class="inline-flex items-center gap-2 rounded-lg border border-error-300 px-4 py-2.5 text-sm font-medium text-error-600 hover:bg-error-50 dark:border-error-500/40 dark:text-error-400 dark:hover:bg-error-500/10">
                    <i class="fas fa-ban"></i> Cancel
                </button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <!-- What was sold -->
            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Packages &amp; charges</h3>
                </div>
                <div class="custom-scrollbar overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr class="whitespace-nowrap border-b border-gray-100 text-gray-700 dark:border-gray-800 dark:text-gray-400">
                                <th class="px-5 py-3 font-medium">Item</th>
                                <th class="px-5 py-3 font-medium">Quantity</th>
                                <th class="px-5 py-3 text-right font-medium">Price</th>
                                <th class="px-5 py-3 text-right font-medium">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($event->items as $item)
                                <tr>
                                    <td class="px-5 py-3">
                                        <p class="font-medium text-gray-800 dark:text-white/90">{{ $item->description }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ \App\Models\FacilityBookingItem::BASES[$item->charge_basis] ?? $item->charge_basis }}</p>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-3 text-gray-700 dark:text-gray-300">{{ $item->quantity_label }}</td>
                                    <td class="whitespace-nowrap px-5 py-3 text-right text-gray-700 dark:text-gray-300">{{ $money($item->unit_price) }}</td>
                                    <td class="whitespace-nowrap px-5 py-3 text-right font-medium text-gray-800 dark:text-white/90">{{ $money($item->total) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Folio: invoice, taxes, payments -->
            @include('stays.partials.folio', ['folioOwner' => $event, 'folioRoutePrefix' => 'events'])
        </div>

        <div class="space-y-6">
            @include('events.partials.client-card')

            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Details</h3>
                </div>
                <dl class="space-y-3 p-5 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Venue</dt><dd class="text-gray-800 dark:text-white/90">
                        @if ($event->facility)<a href="{{ route('facilities.show', $event->facility) }}" class="hover:text-primary">{{ $event->facility->name }}</a>@else — @endif
                    </dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Days</dt><dd class="text-gray-800 dark:text-white/90">{{ $event->days }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Time</dt><dd class="text-gray-800 dark:text-white/90">{{ $event->time_range ?? 'All day' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Guests (pax)</dt><dd class="text-gray-800 dark:text-white/90">{{ $event->pax }}</dd></div>
                    @if ($event->stay)
                        <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Room booking</dt><dd>
                            <a href="{{ route('stays.show', $event->stay) }}" class="text-primary hover:underline">#{{ $event->stay->id }} · {{ $event->stay->guest->name ?? '' }}</a>
                        </dd></div>
                    @endif
                    <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Booked by</dt><dd class="text-gray-800 dark:text-white/90">{{ $event->createdByUser->name ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Booked on</dt><dd class="text-gray-800 dark:text-white/90">{{ $event->created_at?->format('d M Y') }}</dd></div>
                </dl>
                @if ($event->notes)
                    <div class="border-t border-gray-100 px-5 py-4 text-sm dark:border-gray-800">
                        <p class="mb-1 font-medium text-gray-700 dark:text-gray-400">Notes</p>
                        <p class="whitespace-pre-line text-gray-800 dark:text-white/90">{{ $event->notes }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if ($status->canTransitionTo(FacilityBookingStatus::Cancelled))
        <div x-show="cancelOpen" x-cloak x-transition class="fixed inset-0 z-[99999] flex items-center justify-center p-5" @keydown.escape.window="cancelOpen = false">
            <div class="fixed inset-0 bg-gray-400/50 backdrop-blur-sm dark:bg-gray-900/80" @click="cancelOpen = false"></div>
            <form method="POST" action="{{ route('events.status', $event) }}" class="relative w-full max-w-md rounded-2xl bg-white p-6 dark:bg-gray-900">
                @csrf
                <input type="hidden" name="status" value="cancelled">
                <h4 class="text-lg font-medium text-gray-800 dark:text-white/90">Cancel {{ $event->reference }}?</h4>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">The venue is released. An unpaid invoice is cancelled; payments already taken stay on the invoice for refund or credit.</p>
                <input type="text" name="reason" maxlength="255" placeholder="Reason (optional)"
                       class="mt-4 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="cancelOpen = false" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">Keep event</button>
                    <button type="submit" class="rounded-lg bg-error-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-error-600">Cancel event</button>
                </div>
            </form>
        </div>
    @endif
</div>

@if ($status->holdsVenue())
    @include('events.partials.modal-form')
@endif
@endsection
