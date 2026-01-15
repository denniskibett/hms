@extends('layouts.app')


@section('content')
<!-- Breadcrumb Start -->
<div x-data="{ pageName: 'The {{ $roomType->name }}' }">
    @include('partials.breadcrumb')
</div>
<!-- Breadcrumb End -->

<div class="p-6 space-y-6" x-data="roomTypeShow()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('room-types.index') }}" 
                   class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                    <i class="fas fa-arrow-left"></i>
                    Back to Room Types
                </a>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90 mt-2">{{ $roomType->name }}</h2>
            <p class="text-gray-600 dark:text-gray-400">{{ $roomType->code }}</p>
        </div>
        
        <div class="flex items-center gap-3">
            <button @click="openEditModal()"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-800 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-white/[0.03] dark:hover:text-white">
                <i class="fas fa-edit"></i> Edit Room Type
            </button>
            <a href="{{ route('rooms.index', ['room_type' => $roomType->id]) }}"
               class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs transition-colors hover:bg-primary/90">
                <i class="fas fa-hotel"></i> View Rooms
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Room Type Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information Card -->
            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Basic Information</h3>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Room Type Name</label>
                                <p class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $roomType->name }}</p>
                            </div>
                            
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Type Code</label>
                                <p class="text-lg font-semibold text-primary">{{ $roomType->code }}</p>
                            </div>
                            
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</label>
                                <p class="text-gray-700 dark:text-gray-300">
                                    {{ $roomType->description ?? 'No description provided.' }}
                                </p>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Base Rate (per night)</label>
                                <p class="text-3xl font-bold text-primary">
                                    {{ SystemHelper::currencySymbol() }} {{ number_format($roomType->base_rate, 2) }}
                                </p>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Capacity</label>
                                    <p class="text-lg font-semibold text-gray-800 dark:text-white/90">
                                        {{ $roomType->capacity }} guests
                                    </p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Bed Type</label>
                                    <p class="text-lg font-semibold text-gray-800 dark:text-white/90">
                                        {{ \App\Models\RoomType::getBedTypeOptions()[$roomType->bed_type] ?? $roomType->bed_type }}
                                    </p>
                                </div>
                            </div>
                            
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Room Type ID</label>
                                <p class="text-gray-700 dark:text-gray-300">{{ $roomType->id }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Amenities Card -->
            @if($roomType->amenities && count($roomType->amenities) > 0)
            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Amenities & Features</h3>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($roomType->amenities as $amenity)
                        <div class="flex items-center gap-2 p-3 rounded-lg border border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800">
                            <div class="h-8 w-8 rounded-full bg-success/10 flex items-center justify-center">
                                <i class="fas fa-check text-success text-sm"></i>
                            </div>
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $amenity }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Rooms Card -->
            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Rooms ({{ $roomType->rooms->count() }})</h3>
                        <a href="{{ route('rooms.create', ['room_type_id' => $roomType->id]) }}"
                           class="inline-flex items-center gap-2 rounded-lg bg-primary px-3 py-1.5 text-sm font-medium text-white hover:bg-primary/90">
                            <i class="fas fa-plus"></i> Add Room
                        </a>
                    </div>
                </div>
                <div class="p-5">
                    @if($roomType->rooms->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($roomType->rooms as $room)
                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:bg-gray-50 dark:hover:bg-gray-800">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <h4 class="font-medium text-gray-800 dark:text-white/90">Room {{ $room->room_number }}</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Floor {{ $room->floor }}
                                        @if($room->wing)
                                        • {{ $room->wing }} Wing
                                        @endif
                                    </p>
                                </div>
                                <span class="px-2 py-1 rounded-full text-xs font-medium"
                                      @class([
                                        'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500' => $room->status === 'available',
                                        'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-orange-400' => $room->status === 'occupied',
                                        'bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400' => $room->status === 'cleaning',
                                        'bg-danger-50 text-danger-600 dark:bg-danger-500/15 dark:text-danger-500' => in_array($room->status, ['maintenance', 'out_of_order']),
                                        'bg-gray-50 text-gray-600 dark:bg-gray-500/15 dark:text-gray-400' => $room->status === 'reserved',
                                      ])>
                                    {{ \App\Models\Room::getStatusOptions()[$room->status] ?? $room->status }}
                                </span>
                            </div>
                            
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500 dark:text-gray-400">Adult Rate:</span>
                                    <span class="font-medium text-gray-800 dark:text-white/90">
                                        {{ SystemHelper::currencySymbol() }} {{ number_format($room->adult_price ?: $roomType->base_rate, 2) }}
                                    </span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500 dark:text-gray-400">Child Rate:</span>
                                    <span class="font-medium text-gray-800 dark:text-white/90">
                                        {{ SystemHelper::currencySymbol() }} {{ number_format($room->child_price ?: ($roomType->base_rate * 0.5), 2) }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="mt-4 flex justify-between">
                                <a href="{{ route('rooms.show', $room) }}"
                                   class="text-sm text-primary hover:text-primary/80">
                                    View Details
                                </a>
                                @if($room->currentStay)
                                <span class="text-xs bg-primary/10 text-primary px-2 py-1 rounded">
                                    Occupied
                                </span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-8">
                        <i class="fas fa-door-closed text-3xl text-gray-300 dark:text-gray-600 mb-3"></i>
                        <p class="text-gray-500 dark:text-gray-400">No rooms assigned to this room type yet</p>
                        <a href="{{ route('rooms.create', ['room_type_id' => $roomType->id]) }}"
                           class="mt-3 inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/90">
                            <i class="fas fa-plus"></i> Create First Room
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column: Stats & Actions -->
        <div class="space-y-6">
            <!-- Stats Card -->
            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Statistics</h3>
                </div>
                <div class="p-5">
                    <div class="space-y-4">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Total Rooms</span>
                                <span class="text-lg font-bold text-gray-800 dark:text-white/90">
                                    {{ $roomType->rooms->count() }}
                                </span>
                            </div>
                            <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                                @php
                                    $totalRooms = $roomType->rooms->count();
                                    $availableRooms = $roomType->rooms->where('status', 'available')->count();
                                    $percentage = $totalRooms > 0 ? ($availableRooms / $totalRooms) * 100 : 0;
                                @endphp
                                <div class="h-full rounded-full bg-success" style="width: {{ $percentage }}%"></div>
                            </div>
                            <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mt-1">
                                <span>{{ $availableRooms }} available</span>
                                <span>{{ $totalRooms - $availableRooms }} occupied</span>
                            </div>
                        </div>
                        
                        <div class="pt-4 border-t border-gray-100 dark:border-gray-800">
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Average Rate</span>
                                    <span class="text-sm font-medium text-gray-800 dark:text-white/90">
                                        {{ SystemHelper::currencySymbol() }} {{ number_format($roomType->base_rate, 2) }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Total Capacity</span>
                                    <span class="text-sm font-medium text-gray-800 dark:text-white/90">
                                        {{ $roomType->rooms->count() * $roomType->capacity }} guests
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Revenue Potential</span>
                                    <span class="text-sm font-medium text-gray-800 dark:text-white/90">
                                        {{ SystemHelper::currencySymbol() }} {{ number_format($roomType->rooms->count() * $roomType->base_rate, 2) }}/night
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Quick Actions</h3>
                </div>
                <div class="p-5">
                    <div class="space-y-3">
                        <a href="{{ route('rooms.create', ['room_type_id' => $roomType->id]) }}"
                           class="flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-white/[0.03] dark:hover:text-white">
                            <span class="flex items-center gap-2">
                                <i class="fas fa-plus-circle"></i>
                                Add New Room
                            </span>
                            <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                        
                        <button @click="openEditModal()"
                                class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-white/[0.03] dark:hover:text-white">
                            <span class="flex items-center gap-2">
                                <i class="fas fa-edit"></i>
                                Edit Room Type
                            </span>
                            <i class="fas fa-arrow-right text-xs"></i>
                        </button>
                        
                        <button @click="duplicateRoomType()"
                                class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-white/[0.03] dark:hover:text-white">
                            <span class="flex items-center gap-2">
                                <i class="fas fa-copy"></i>
                                Duplicate Room Type
                            </span>
                            <i class="fas fa-arrow-right text-xs"></i>
                        </button>
                        
                        <button @click="printRoomTypeDetails()"
                                class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-white/[0.03] dark:hover:text-white">
                            <span class="flex items-center gap-2">
                                <i class="fas fa-print"></i>
                                Print Details
                            </span>
                            <i class="fas fa-arrow-right text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- System Info Card -->
            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">System Information</h3>
                </div>
                <div class="p-5">
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Created</span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white/90">
                                {{ $roomType->created_at->format('M d, Y') }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Last Updated</span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white/90">
                                {{ $roomType->updated_at->format('M d, Y') }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Room Count</span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white/90">
                                {{ $roomType->rooms->count() }}
                            </span>
                        </div>
                        @if($roomType->services)
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Services</span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white/90">
                                {{ $roomType->services->count() }}
                            </span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Room Type Modal -->
@include('room-types.partials.modal-form')

<script>
function roomTypeShow() {
    return {
        isEditModalOpen: false,
        isLoading: false,
        
        // Form data for edit modal
        formData: {
            id: {{ $roomType->id }},
            name: '{{ $roomType->name }}',
            code: '{{ $roomType->code }}',
            base_rate: {{ $roomType->base_rate }},
            capacity: {{ $roomType->capacity }},
            bed_type: '{{ $roomType->bed_type }}',
            description: '{{ $roomType->description ?? '' }}',
            amenities: @json($roomType->amenities ?? []),
            new_amenity: ''
        },
        
        // Available amenities
        availableAmenities: [
            'Free WiFi', 'TV', 'Air Conditioning', 'Heating', 'Mini Bar',
            'Safe', 'Room Service', 'Daily Housekeeping', 'Private Bathroom',
            'Hairdryer', 'Iron', 'Desk', 'Telephone', 'Wake-up Service',
            'Breakfast Included', 'Balcony', 'Sea View', 'Mountain View',
            'Coffee Maker', 'Tea/Coffee Facilities', 'Refrigerator'
        ],
        
        // Bed types for dropdown
        bedTypes: @json($bedTypes),
        
        init() {
            // Initialization if needed
        },
        
        openEditModal() {
            this.isEditModalOpen = true;
            document.body.classList.add('overflow-hidden');
        },
        
        closeModal() {
            this.isEditModalOpen = false;
            document.body.classList.remove('overflow-hidden');
        },
        
        // Amenity management
        addAmenity() {
            if (this.formData.new_amenity.trim()) {
                this.formData.amenities.push(this.formData.new_amenity.trim());
                this.formData.new_amenity = '';
            }
        },
        
        removeAmenity(index) {
            this.formData.amenities.splice(index, 1);
        },
        
        // Update room type
        async submitForm() {
            this.isLoading = true;
            
            try {
                const response = await fetch('/room-types/{{ $roomType->id }}', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(this.formData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showToast('Room type updated successfully', 'success');
                    this.closeModal();
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    this.showToast(data.message || 'Error updating room type', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                this.showToast('An error occurred', 'error');
            } finally {
                this.isLoading = false;
            }
        },
        
        // Duplicate room type
        async duplicateRoomType() {
            if (!confirm('Duplicate this room type? This will create a new room type with the same settings.')) {
                return;
            }
            
            this.isLoading = true;
            
            try {
                const response = await fetch('/room-types/{{ $roomType->id }}/duplicate', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showToast('Room type duplicated successfully', 'success');
                    setTimeout(() => {
                        window.location.href = `/room-types/${data.room_type_id}`;
                    }, 1500);
                } else {
                    this.showToast(data.message || 'Error duplicating room type', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                this.showToast('An error occurred', 'error');
            } finally {
                this.isLoading = false;
            }
        },
        
        // Print room type details
        printRoomTypeDetails() {
            const printWindow = window.open('', '_blank');
            const content = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>{{ $roomType->name }} - Room Type Details</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        .header { text-align: center; margin-bottom: 30px; }
                        .section { margin-bottom: 20px; }
                        .label { font-weight: bold; color: #666; }
                        .value { margin-bottom: 10px; }
                        .amenities-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-top: 10px; }
                        .amenity-item { padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
                        .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #666; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>{{ $roomType->name }}</h1>
                        <h2>Room Type Details</h2>
                        <p>Printed on ${new Date().toLocaleDateString()}</p>
                    </div>
                    
                    <div class="section">
                        <h3>Basic Information</h3>
                        <div class="value">
                            <span class="label">Room Type:</span> {{ $roomType->name }}
                        </div>
                        <div class="value">
                            <span class="label">Code:</span> {{ $roomType->code }}
                        </div>
                        <div class="value">
                            <span class="label">Base Rate:</span> {{ SystemHelper::currencySymbol() }} {{ number_format($roomType->base_rate, 2) }} per night
                        </div>
                        <div class="value">
                            <span class="label">Capacity:</span> {{ $roomType->capacity }} guests
                        </div>
                        <div class="value">
                            <span class="label">Bed Type:</span> {{ \App\Models\RoomType::getBedTypeOptions()[$roomType->bed_type] ?? $roomType->bed_type }}
                        </div>
                        @if($roomType->description)
                        <div class="value">
                            <span class="label">Description:</span> {{ $roomType->description }}
                        </div>
                        @endif
                    </div>
                    
                    @if($roomType->amenities && count($roomType->amenities) > 0)
                    <div class="section">
                        <h3>Amenities & Features</h3>
                        <div class="amenities-grid">
                            @foreach($roomType->amenities as $amenity)
                            <div class="amenity-item">{{ $amenity }}</div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    
                    @if($roomType->rooms->count() > 0)
                    <div class="section">
                        <h3>Assigned Rooms ({{ $roomType->rooms->count() }})</h3>
                        <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                            <thead>
                                <tr>
                                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Room Number</th>
                                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Floor</th>
                                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($roomType->rooms as $room)
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 8px;">{{ $room->room_number }}</td>
                                    <td style="border: 1px solid #ddd; padding: 8px;">{{ $room->floor }}</td>
                                    <td style="border: 1px solid #ddd; padding: 8px;">{{ \App\Models\Room::getStatusOptions()[$room->status] ?? $room->status }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                    
                    <div class="footer">
                        <p>Printed from {{ config('app.name') }} Hotel Management System</p>
                        <p>Room Type ID: {{ $roomType->id }}</p>
                    </div>
                </body>
                </html>
            `;
            
            printWindow.document.write(content);
            printWindow.document.close();
            printWindow.print();
        },
        
        // Toast notification
        showToast(message, type = 'info') {
            // Remove existing toasts
            document.querySelectorAll('.custom-toast').forEach(toast => toast.remove());
            
            // Create new toast
            const toast = document.createElement('div');
            toast.className = `custom-toast fixed top-4 right-4 px-4 py-3 rounded-lg text-white text-sm shadow-lg z-[99999] transition-all duration-300 ${
                type === 'success' ? 'bg-green-500' : 
                type === 'error' ? 'bg-red-500' : 
                type === 'warning' ? 'bg-yellow-500' : 
                'bg-blue-500'
            }`;
            toast.textContent = message;
            
            // Add icon
            const icon = document.createElement('i');
            icon.className = `fas mr-2 ${
                type === 'success' ? 'fa-check-circle' : 
                type === 'error' ? 'fa-times-circle' : 
                type === 'warning' ? 'fa-exclamation-triangle' : 
                'fa-info-circle'
            }`;
            toast.prepend(icon);
            
            document.body.appendChild(toast);
            
            // Auto remove
            setTimeout(() => {
                toast.classList.add('opacity-0', 'transition-opacity', 'duration-300');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }
    };
}
</script>
@endsection