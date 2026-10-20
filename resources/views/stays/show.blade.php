@extends('layouts.app')

@php
    $dateFormat = 'D, ' . SystemHelper::dateFormat();
    $currency = SystemHelper::currencySymbol();
    $guest = $stay->guest;
    $profile = $guest?->guestProfile;
    $money = fn ($value) => $currency . ' ' . number_format((float) $value, 2);
@endphp

@section('content')
<div x-data="{ pageName: @js('Booking #' . $stay->id) }">
    @include('partials.breadcrumb')
</div>

<div class="space-y-6" x-data="{ cancelOpen: false }">
    @if (session('receipt_url'))
        <div class="flex items-center justify-between rounded-lg border border-primary/20 bg-primary/5 px-4 py-3 text-sm text-gray-700 dark:text-gray-300 print:hidden">
            <span><i class="fas fa-receipt mr-1 text-primary"></i> Payment saved. Print the receipt for the guest?</span>
            <a href="{{ session('receipt_url') }}" target="_blank" class="rounded-lg bg-primary px-3 py-1.5 text-xs font-medium text-white hover:bg-primary/90">Print receipt</a>
        </div>
    @endif

    <!-- Header -->
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <a href="{{ route('stays.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 print:hidden">
                <i class="fas fa-arrow-left"></i> Back to Bookings
            </a>
            <div class="mt-2 flex flex-wrap items-center gap-3">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Booking #{{ $stay->id }}</h2>
                @include('partials.status-badge', ['status' => $stay->state])
                @include('partials.status-badge', ['status' => $stay->meal_plan])
            </div>
            <p class="text-gray-600 dark:text-gray-400">
                {{ $guest->name ?? 'Unknown guest' }} •
                {{ $stay->arrival_date->format($dateFormat) }} → {{ $stay->departure_date->format($dateFormat) }}
                ({{ $stay->nights }} {{ Str::plural('night', $stay->nights) }})
            </p>
        </div>

        @include('stays.partials.actions')
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Left -->
        <div class="space-y-6 lg:col-span-2">
            <!-- Rooms -->
            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Rooms</h3>
                </div>
                <div class="custom-scrollbar overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr class="whitespace-nowrap border-b border-gray-100 text-gray-700 dark:border-gray-800 dark:text-gray-400">
                                <th class="px-5 py-3 font-medium">Room</th>
                                <th class="px-5 py-3 font-medium">Guests</th>
                                <th class="px-5 py-3 font-medium">Dates</th>
                                <th class="px-5 py-3 text-right font-medium">Rate / night</th>
                                <th class="px-5 py-3 text-right font-medium">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($stay->roomAllocations as $allocation)
                                <tr>
                                    <td class="px-5 py-3">
                                        @if ($allocation->room)
                                            <a href="{{ route('rooms.show', $allocation->room) }}" class="font-medium text-gray-800 hover:text-primary dark:text-white/90">Room {{ $allocation->room->room_number }}</a>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $allocation->room->roomType->name ?? '' }}</p>
                                        @else
                                            <span class="text-gray-400">Room removed</span>
                                        @endif
                                        @if ($allocation->guest_notes)
                                            <p class="mt-1 text-xs text-gray-600 dark:text-gray-400"><i class="fas fa-sticky-note"></i> {{ $allocation->guest_notes }}</p>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-3 text-gray-700 dark:text-gray-300">
                                        {{ $allocation->adults }} {{ Str::plural('adult', $allocation->adults) }}{{ $allocation->children ? ', ' . $allocation->children . ' ' . Str::plural('child', $allocation->children) : '' }}
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-3 text-gray-700 dark:text-gray-300">
                                        {{ $allocation->from_date->format('d M') }} → {{ $allocation->to_date->format('d M') }}
                                        <span class="text-gray-500">({{ $allocation->nights }})</span>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-3 text-right text-gray-700 dark:text-gray-300">{{ $money($allocation->rate_applied) }}</td>
                                    <td class="whitespace-nowrap px-5 py-3 text-right font-medium text-gray-800 dark:text-white/90">{{ $money($allocation->total) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-5 py-8 text-center text-gray-500">No rooms assigned</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Folio: invoice, taxes, payments -->
            @include('stays.partials.folio')
        </div>

        <!-- Right -->
        <div class="space-y-6">
            <!-- Guest -->
            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Guest</h3>
                    @if ($profile)
                        <a href="{{ route('guests.show', $profile) }}" class="text-sm text-primary hover:underline print:hidden">Profile</a>
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
                    @if ($profile?->id_number)
                        <div><dt class="text-gray-500 dark:text-gray-400">{{ Str::headline($profile->id_type ?? 'ID') }}</dt><dd class="text-gray-800 dark:text-white/90">{{ $profile->id_number }}</dd></div>
                    @endif
                    @if ($profile?->nationality)
                        <div><dt class="text-gray-500 dark:text-gray-400">Nationality</dt><dd class="text-gray-800 dark:text-white/90">{{ $profile->nationality }}</dd></div>
                    @endif
                </dl>
            </div>

            <!-- Stay details -->
            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Details</h3>
                </div>
                <dl class="space-y-3 p-5 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Meal plan</dt><dd class="text-gray-800 dark:text-white/90">{{ $stay->meal_plan->label() }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Guests</dt><dd class="text-gray-800 dark:text-white/90">{{ $stay->adults }} adults{{ $stay->children ? ', ' . $stay->children . ' children' : '' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Checked in</dt><dd class="text-gray-800 dark:text-white/90">{{ $stay->check_in?->format('d M Y, h:i A') ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Checked out</dt><dd class="text-gray-800 dark:text-white/90">{{ $stay->check_out?->format('d M Y, h:i A') ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Booked by</dt><dd class="text-gray-800 dark:text-white/90">{{ $stay->createdByUser->name ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Booked on</dt><dd class="text-gray-800 dark:text-white/90">{{ $stay->created_at?->format('d M Y') }}</dd></div>
                </dl>
                @if ($stay->special_requests)
                    <div class="border-t border-gray-100 px-5 py-4 text-sm dark:border-gray-800">
                        <p class="mb-1 font-medium text-gray-700 dark:text-gray-400">Special requests</p>
                        <p class="whitespace-pre-line text-gray-800 dark:text-white/90">{{ $stay->special_requests }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if ($stay->state->canTransitionTo(\App\Enums\StayStatus::Cancelled))
        @include('stays.partials.cancel-modal')
    @endif
</div>
@endsection
