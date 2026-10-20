@extends('layouts.app')

@php
    $canManage = auth()->user()->hasAnyRole(['admin', 'manager']);
    $dateFormat = 'D, ' . SystemHelper::dateFormat();
    $currency = SystemHelper::currencySymbol();
    $roomJs = $room->only(\App\Models\Room::FORM_FIELDS);
@endphp

@section('content')
<div x-data="{ pageName: @js('Room ' . $room->room_number) }">
    @include('partials.breadcrumb')
</div>

<div class="space-y-6" x-data="rooms(@js(['roomTypes' => $roomTypes]))">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('rooms.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                <i class="fas fa-arrow-left"></i> Back to Rooms
            </a>
            <div class="mt-2 flex items-center gap-3">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Room {{ $room->room_number }}</h2>
                @include('partials.status-badge', ['status' => $room->status])
            </div>
            <p class="text-gray-600 dark:text-gray-400">{{ $room->roomType->name ?? 'N/A' }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-3 print:hidden">
            @if ($canManage)
                <button type="button" @click="openEditModal(@js($roomJs))"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-800 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-white/[0.03]">
                    <i class="fas fa-edit"></i> Edit
                </button>
            @endif
            <button type="button" @click="openAvailabilityModal(@js($roomJs))"
                    class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-primary/90">
                <i class="fas fa-calendar-check"></i> Check Availability
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Left Column -->
        <div class="space-y-6 lg:col-span-2">
            <!-- Room Information -->
            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Room Information</h3>
                </div>
                <div class="grid grid-cols-1 gap-6 p-5 md:grid-cols-2">
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Room Type</p>
                            <p class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $room->roomType->name ?? 'N/A' }}</p>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                <i class="fas fa-user"></i> {{ $room->roomType->capacity ?? 1 }} guests •
                                <i class="fas fa-bed"></i> {{ ucfirst($room->roomType->bed_type ?? 'double') }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Location</p>
                            <p class="mt-1 text-gray-700 dark:text-gray-300">
                                <i class="fas fa-layer-group"></i> Floor {{ $room->floor }}
                                @if ($room->wing)
                                    <span class="ml-3"><i class="fas fa-building"></i> {{ $room->wing }} Wing</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Pricing (per night)</p>
                        <div class="mt-2 grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Room type rate</p>
                                <p class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $currency }} {{ number_format($room->roomType->base_rate ?? 0, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Room overrides</p>
                                @if ((float) $room->adult_price > 0 || (float) $room->child_price > 0)
                                    <p class="text-sm text-gray-800 dark:text-white/90">Adult {{ $currency }} {{ number_format($room->adult_price, 2) }}</p>
                                    <p class="text-sm text-gray-800 dark:text-white/90">Child {{ $currency }} {{ number_format($room->child_price, 2) }}</p>
                                @else
                                    <p class="text-sm text-gray-500 dark:text-gray-400">None</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if (! empty($room->features))
                        <div class="border-t border-gray-100 pt-5 md:col-span-2 dark:border-gray-800">
                            <p class="mb-3 text-sm font-medium text-gray-500 dark:text-gray-400">Features</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($room->features as $feature)
                                    <span class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1.5 text-sm text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        <i class="fas fa-check text-xs text-success-500"></i> {{ $feature }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Current Guest -->
            @if ($room->currentStay)
                @php $stay = $room->currentStay; @endphp
                <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                        <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Current Guest</h3>
                        <a href="{{ route('stays.show', $stay) }}" class="rounded-lg bg-primary/10 px-2.5 py-1 text-xs font-medium text-primary hover:bg-primary/20 print:hidden">
                            View Stay #{{ $stay->id }}
                        </a>
                    </div>
                    <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-3">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Guest</p>
                            <p class="font-medium text-gray-800 dark:text-white/90">{{ $stay->guest->name ?? 'Guest' }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $stay->adults }} adults{{ $stay->children ? ', ' . $stay->children . ' children' : '' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Arrival</p>
                            <p class="font-medium text-gray-800 dark:text-white/90">{{ $stay->arrival_date?->format($dateFormat) }}</p>
                            @if ($stay->check_in)
                                <p class="text-xs text-gray-500 dark:text-gray-400">Checked in {{ $stay->check_in->format('h:i A') }}</p>
                            @endif
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Departure</p>
                            <p class="font-medium text-gray-800 dark:text-white/90">{{ $stay->departure_date?->format($dateFormat) }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Booking History -->
            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Booking History</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Latest 10 of {{ $room->allocations_count }}</p>
                </div>
                <div class="space-y-3 p-5">
                    @forelse ($room->allocations as $allocation)
                        @php
                            $phase = $allocation->to_date->lte(today()) ? ['Past', 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400']
                                : ($allocation->from_date->lte(today()) ? ['Current', 'bg-success-500/10 text-success-600 dark:text-success-500']
                                : ['Upcoming', 'bg-primary/10 text-primary']);
                        @endphp
                        <div class="flex items-start justify-between gap-4 rounded-lg border border-gray-100 p-3 dark:border-gray-800">
                            <div>
                                <p class="font-medium text-gray-800 dark:text-white/90">
                                    @if ($allocation->stay)
                                        <a href="{{ route('stays.show', $allocation->stay) }}" class="hover:text-primary">Stay #{{ $allocation->stay_id }}</a>
                                        <span class="font-normal text-gray-500 dark:text-gray-400">— {{ $allocation->stay->guest->name ?? 'Guest' }}</span>
                                    @else
                                        Stay #{{ $allocation->stay_id }} <span class="font-normal text-gray-500">(deleted)</span>
                                    @endif
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $allocation->from_date->format($dateFormat) }} → {{ $allocation->to_date->format($dateFormat) }}
                                    • {{ (int) $allocation->from_date->diffInDays($allocation->to_date) }} nights
                                </p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $allocation->adults }} adults, {{ $allocation->children }} children •
                                    {{ $currency }} {{ number_format($allocation->rate_applied, 2) }}/night
                                </p>
                                @if ($allocation->guest_notes)
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400"><i class="fas fa-sticky-note text-xs"></i> {{ $allocation->guest_notes }}</p>
                                @endif
                            </div>
                            <span class="shrink-0 rounded-full px-2 py-1 text-xs {{ $phase[1] }}">{{ $phase[0] }}</span>
                        </div>
                    @empty
                        <div class="py-8 text-center text-gray-500 dark:text-gray-400">
                            <i class="fas fa-history mb-3 text-3xl text-gray-300 dark:text-gray-600"></i>
                            <p>No bookings yet</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
            <!-- Change Status -->
            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900 print:hidden">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Change Status</h3>
                </div>
                <div class="grid grid-cols-2 gap-2 p-5">
                    @foreach (\App\Enums\RoomStatus::cases() as $status)
                        <button type="button"
                                @click="changeStatus(@js($roomJs), @js($status->value))"
                                :disabled="isLoading"
                                @disabled($room->status === $status)
                                class="rounded-lg px-3 py-2 text-sm font-medium transition disabled:cursor-not-allowed {{ $room->status === $status ? $status->badgeClasses() . ' ring-2 ring-current' : 'border border-gray-200 text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]' }}">
                            {{ $status->label() }}
                        </button>
                    @endforeach
                </div>
                <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
                    <button type="button" onclick="window.print()"
                            class="flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </div>

            <!-- Details -->
            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Details</h3>
                </div>
                <dl class="space-y-3 p-5 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Total bookings</dt><dd class="font-medium text-gray-800 dark:text-white/90">{{ $room->allocations_count }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Open tasks</dt><dd class="font-medium text-gray-800 dark:text-white/90">{{ $room->pending_tasks_count }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Created</dt><dd class="font-medium text-gray-800 dark:text-white/90">{{ $room->created_at?->format($dateFormat) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500 dark:text-gray-400">Last updated</dt><dd class="font-medium text-gray-800 dark:text-white/90">{{ $room->updated_at?->format($dateFormat) }}</dd></div>
                </dl>
            </div>

            @if ($canManage)
                <!-- Danger zone -->
                <div class="rounded-2xl border border-error-200 bg-white p-5 dark:border-error-500/30 dark:bg-gray-900 print:hidden">
                    <h3 class="font-medium text-gray-800 dark:text-white/90">Delete room</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        @if ($room->allocations_count)
                            This room has booking history, so it can't be deleted. Set it to Out of Order instead.
                        @else
                            Permanently remove this room.
                        @endif
                    </p>
                    <button type="button" @click="deleteRoom(@js($roomJs))" @disabled($room->allocations_count)
                            class="mt-3 rounded-lg bg-error-500 px-4 py-2 text-sm font-medium text-white hover:bg-error-600 disabled:cursor-not-allowed disabled:opacity-50">
                        Delete Room
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- Modals must sit inside the x-data element to share its state --}}
    @if ($canManage)
        @include('rooms.partials.modal-form')
    @endif
    @include('rooms.partials.availability-modal')
</div>

@include('rooms.partials.scripts')
@endsection
