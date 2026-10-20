@extends('layouts.app')

@php
    $canManage = auth()->user()->hasAnyRole(['admin', 'manager']);
    $inputClass = 'h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
@endphp

@section('content')
    <div x-data="{ pageName: `Rooms` }">
        @include('partials.breadcrumb')
    </div>

<div class="space-y-6" x-data="rooms(@js([
    'roomTypes' => $roomTypes,
    // ?create=1 (e.g. from a room type page) opens the Add Room modal
    'openCreate' => $canManage && request()->boolean('create'),
    'createRoomTypeId' => $filters['room_type_id'] ?? null,
]))">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Rooms</h2>
            <p class="text-gray-600 dark:text-gray-400">Room inventory, status and availability</p>
        </div>

        @if ($canManage)
            <button type="button" @click="openCreateModal()"
                    class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs transition-colors hover:bg-primary/90">
                <i class="fas fa-plus"></i> Add Room
            </button>
        @endif
    </div>

    <!-- Stats (whole hotel, not just this page) -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        @include('partials.stat-card', ['label' => 'Total Rooms', 'value' => $stats['total'], 'icon' => 'fa-hotel', 'tone' => 'primary', 'href' => route('rooms.index')])
        @include('partials.stat-card', ['label' => 'Available', 'value' => $stats['available'], 'icon' => 'fa-door-open', 'tone' => 'success', 'href' => route('rooms.index', ['status' => 'available'])])
        @include('partials.stat-card', ['label' => 'Occupied', 'value' => $stats['occupied'], 'icon' => 'fa-bed', 'tone' => 'warning', 'href' => route('rooms.index', ['status' => 'occupied'])])
        @include('partials.stat-card', ['label' => 'Cleaning', 'value' => $stats['cleaning'], 'icon' => 'fa-broom', 'tone' => 'info', 'href' => route('rooms.index', ['status' => 'cleaning'])])
        @include('partials.stat-card', ['label' => 'Out of Service', 'value' => $stats['out_of_service'], 'icon' => 'fa-tools', 'tone' => 'error', 'href' => route('rooms.index', ['status' => 'maintenance'])])
    </div>

    <!-- Filters -->
    <form method="GET" action="{{ route('rooms.index') }}"
          class="grid grid-cols-1 gap-3 rounded-2xl border border-gray-100 bg-white p-4 sm:grid-cols-2 lg:grid-cols-6 dark:border-gray-800 dark:bg-gray-900">
        <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Room number"
               class="{{ $inputClass }} lg:col-span-2">

        <select name="status" class="{{ $inputClass }}" onchange="this.form.submit()">
            <option value="">All statuses</option>
            @foreach (\App\Enums\RoomStatus::options() as $value => $label)
                <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>

        <select name="room_type_id" class="{{ $inputClass }}" onchange="this.form.submit()">
            <option value="">All room types</option>
            @foreach ($roomTypes as $type)
                <option value="{{ $type->id }}" @selected((string) ($filters['room_type_id'] ?? '') === (string) $type->id)>{{ $type->name }}</option>
            @endforeach
        </select>

        <input type="number" name="floor" value="{{ $filters['floor'] ?? '' }}" placeholder="Floor" class="{{ $inputClass }}">

        <div class="flex gap-2">
            <button type="submit" class="flex-1 rounded-lg bg-primary px-3 py-2 text-sm font-medium text-white hover:bg-primary/90">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            @if (array_filter($filters))
                <a href="{{ route('rooms.index') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                    Clear
                </a>
            @endif
        </div>
    </form>

    <!-- Rooms Table -->
    <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="custom-scrollbar overflow-x-auto">
            <table class="min-w-full text-left text-sm text-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr class="whitespace-nowrap border-b border-gray-100 dark:border-gray-800">
                        @foreach (['Room', 'Room Type', 'Floor / Wing', 'Status', 'Features', 'Current Guest', 'Actions'] as $heading)
                            <th class="px-5 py-4 text-sm font-medium text-gray-700 dark:text-gray-400">{{ $heading }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-white/[0.03]">
                    @forelse ($rooms as $room)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-5 py-4">
                                <a href="{{ route('rooms.show', $room) }}" class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10">
                                        <i class="fas fa-door-closed text-primary"></i>
                                    </div>
                                    <p class="text-sm font-medium text-gray-800 hover:text-primary dark:text-white/90">{{ $room->room_number }}</p>
                                </a>
                            </td>
                            <td class="px-5 py-4">
                                <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $room->roomType->name ?? 'N/A' }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    <i class="fas fa-user"></i> {{ $room->roomType->capacity ?? 1 }} guests •
                                    <i class="fas fa-bed"></i> {{ ucfirst($room->roomType->bed_type ?? 'double') }}
                                </p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-gray-700 dark:text-gray-300">
                                <i class="fas fa-layer-group"></i> Floor {{ $room->floor }}
                                @if ($room->wing)
                                    <span class="ml-2 text-gray-500 dark:text-gray-400"><i class="fas fa-building"></i> {{ $room->wing }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @include('partials.status-badge', ['status' => $room->status])
                            </td>
                            <td class="px-5 py-4">
                                @if (! empty($room->features))
                                    <div class="flex flex-wrap gap-1">
                                        @foreach (array_slice($room->features, 0, 3) as $feature)
                                            <span class="rounded-full bg-gray-100 px-2 py-1 text-xs text-gray-600 dark:bg-gray-800 dark:text-gray-400">{{ $feature }}</span>
                                        @endforeach
                                        @if (count($room->features) > 3)
                                            <span class="rounded-full bg-gray-100 px-2 py-1 text-xs text-gray-600 dark:bg-gray-800 dark:text-gray-400">+{{ count($room->features) - 3 }} more</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-sm text-gray-400 dark:text-gray-500">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if ($room->currentStay)
                                    <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $room->currentStay->guest->name ?? 'Guest' }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Stay #{{ $room->currentStay->id }} • until {{ $room->currentStay->departure_date?->format(SystemHelper::dateFormat()) }}
                                    </p>
                                @else
                                    <span class="text-sm text-gray-400 dark:text-gray-500">Vacant</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2 whitespace-nowrap">
                                    <a href="{{ route('rooms.show', $room) }}"
                                       class="inline-flex items-center gap-1 rounded-lg bg-primary/10 px-2.5 py-1 text-xs font-medium text-primary hover:bg-primary/20">
                                        <i class="fas fa-eye text-xs"></i> View
                                    </a>
                                    @if ($canManage)
                                        <button type="button" @click="openEditModal(@js($room->only(\App\Models\Room::FORM_FIELDS)))"
                                                class="inline-flex items-center gap-1 rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300">
                                            <i class="fas fa-edit text-xs"></i> Edit
                                        </button>
                                    @endif
                                    <button type="button" @click="openAvailabilityModal(@js($room->only('id', 'room_number')))"
                                            class="inline-flex items-center gap-1 rounded-lg bg-success-500/10 px-2.5 py-1 text-xs font-medium text-success-600 hover:bg-success-500/20 dark:text-success-500">
                                        <i class="fas fa-calendar-check text-xs"></i> Availability
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                                <i class="fas fa-hotel mb-3 text-4xl"></i>
                                @if (array_filter($filters))
                                    <p class="text-lg">No rooms match these filters</p>
                                    <a href="{{ route('rooms.index') }}" class="mt-2 inline-block text-sm text-primary hover:underline">Clear filters</a>
                                @else
                                    <p class="text-lg">No rooms yet</p>
                                    @if ($canManage)
                                        <button type="button" @click="openCreateModal()"
                                                class="mt-4 inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/90">
                                            <i class="fas fa-plus"></i> Add First Room
                                        </button>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($rooms->hasPages())
            <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
                {{ $rooms->links() }}
            </div>
        @endif
    </div>

    {{-- Modals must sit inside the x-data element to share its state --}}
    @if ($canManage)
        @include('rooms.partials.modal-form')
    @endif
    @include('rooms.partials.availability-modal')
</div>

@include('rooms.partials.scripts')
@endsection
