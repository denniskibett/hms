@extends('layouts.app')




@section('content')
<!-- Breadcrumb Start -->
<div x-data="{ pageName: 'Room {{ $room->room_number }}' }">
    @include('partials.breadcrumb')
</div>
<!-- Breadcrumb End -->

<div class="p-6 space-y-6" x-data="roomShow()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('rooms.index') }}" 
                   class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                    <i class="fas fa-arrow-left"></i>
                    Back to Rooms
                </a>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90 mt-2">Room {{ $room->room_number }}</h2>
            <p class="text-gray-600 dark:text-gray-400">{{ $room->roomType->name ?? 'N/A' }}</p>
        </div>
        
        <div class="flex items-center gap-3">
            <button @click="openEditModal()"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-800 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-white/[0.03] dark:hover:text-white">
                <i class="fas fa-edit"></i> Edit Room
            </button>
            <button @click="openAvailabilityModal()"
                    class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs transition-colors hover:bg-primary/90">
                <i class="fas fa-calendar-check"></i> Check Availability
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Room Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Room Information Card -->
            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Room Information</h3>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Room Details -->
                        <div class="space-y-4">
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Room Number</label>
                                <p class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $room->room_number }}</p>
                            </div>
                            
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Room Type</label>
                                <p class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $room->roomType->name ?? 'N/A' }}</p>
                                <div class="flex items-center gap-4 mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    <span class="inline-flex items-center gap-1">
                                        <i class="fas fa-user"></i>
                                        {{ $room->roomType->capacity ?? 1 }} guests
                                    </span>
                                    <span class="inline-flex items-center gap-1">
                                        <i class="fas fa-bed"></i>
                                        {{ $room->roomType->bed_type ?? 'Double' }}
                                    </span>
                                </div>
                            </div>
                            
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Location</label>
                                <div class="flex items-center gap-4 mt-1">
                                    <span class="inline-flex items-center gap-2 text-gray-700 dark:text-gray-300">
                                        <i class="fas fa-layer-group"></i>
                                        Floor {{ $room->floor }}
                                    </span>
                                    @if($room->wing)
                                    <span class="inline-flex items-center gap-2 text-gray-700 dark:text-gray-300">
                                        <i class="fas fa-building"></i>
                                        {{ $room->wing }} Wing
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Pricing & Status -->
                        <div class="space-y-4">
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</label>
                                <div class="mt-1">
                                    <span class="px-3 py-1 rounded-full text-sm font-medium"
                                          @class([
                                            'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500' => $room->status === 'available',
                                            'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-orange-400' => $room->status === 'occupied',
                                            'bg-blue-50 text-primary dark:bg-primary/15 dark:text-blue-400' => $room->status === 'cleaning',
                                            'bg-danger-50 text-danger-600 dark:bg-danger-500/15 dark:text-danger-500' => in_array($room->status, ['maintenance', 'out_of_order']),
                                            'bg-gray-50 text-gray-600 dark:bg-gray-500/15 dark:text-gray-400' => $room->status === 'reserved',
                                          ])>
                                        {{ \App\Models\Room::getStatusOptions()[$room->status] ?? $room->status }}
                                    </span>
                                </div>
                            </div>
                            
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Pricing (per night)</label>
                                <div class="grid grid-cols-2 gap-4 mt-2">
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Adult Rate</p>
                                        <p class="text-lg font-semibold text-gray-800 dark:text-white/90">
                                            {{ SystemHelper::currencySymbol() }} {{ number_format($room->adult_price ?: $room->roomType->base_rate ?? 0, 2) }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Child Rate</p>
                                        <p class="text-lg font-semibold text-gray-800 dark:text-white/90">
                                            {{ SystemHelper::currencySymbol() }} {{ number_format($room->child_price ?: ($room->roomType->base_rate ?? 0) * 0.5, 2) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Room ID</label>
                                <p class="text-gray-700 dark:text-gray-300">{{ $room->id }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Features -->
                    @if($room->features && count($room->features) > 0)
                    <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-800">
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3 block">Room Features</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($room->features as $feature)
                            <span class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1.5 text-sm text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                <i class="fas fa-check text-success text-xs"></i>
                                {{ $feature }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Current Allocation Card -->
            @if($room->currentStay)
            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Current Guest</h3>
                </div>
                <div class="p-5">
                    <div class="flex items-start gap-4">
                        <div class="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center">
                            <i class="fas fa-user text-primary"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                                        Stay #{{ $room->currentStay->id }}
                                    </h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Currently occupied
                                    </p>
                                </div>
                                <a :href="`/stays/${room.currentStay.id}`"
                                class="inline-flex items-center gap-1 rounded-lg bg-primary/10 px-2.5 py-1 text-xs font-medium text-primary hover:bg-primary/20">
                                    View Stay
                                </a>

                            </div>
                            
                            <div class="grid grid-cols-2 gap-4 mt-4">
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Check-in Date</p>
                                    <p class="font-medium text-gray-800 dark:text-white/90">
                                        {{ $room->currentStay->arrival_date?->format('D, ' . SystemHelper::dateFormat()) }}
                                    </p>
                                    @if($room->currentStay->check_in)
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $room->currentStay->check_in->format('h:i A') }}
                                    </p>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Check-out Date</p>
                                    <p class="font-medium text-gray-800 dark:text-white/90">
                                        {{ $room->currentStay->departure_date?->format('D, ' . SystemHelper::dateFormat()) }}
                                    </p>
                                    @if($room->currentStay->check_out)
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $room->currentStay->check_out->format('h:i A') }}
                                    </p>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Guests in Room</p>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1 text-sm">
                                        <i class="fas fa-user"></i>
                                        {{ $room->currentStay->adults }} Adults
                                    </span>
                                    @if($room->currentStay->children > 0)
                                    <span class="inline-flex items-center gap-1 text-sm">
                                        <i class="fas fa-child"></i>
                                        {{ $room->currentStay->children }} Children
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Room History Card -->
            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Room History</h3>
                        <button @click="loadHistory()" 
                                class="text-sm text-primary hover:text-primary/80">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="p-5">
                    <div class="space-y-4">
                        @forelse($room->allocations as $allocation)
                        <div class="flex items-start gap-4 p-3 rounded-lg border border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800">
                            <div class="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-calendar text-primary text-xs"></i>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="font-medium text-gray-800 dark:text-white/90">
                                            @if($allocation->stay)
                                                Allocation #{{ $allocation->id }}
                                            @else
                                                Allocation #{{ $allocation->id }}
                                            @endif
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $allocation->from_date?->format('D, ' . SystemHelper::dateFormat()) }} - {{ $allocation->to_date?->format('D, ' . SystemHelper::dateFormat()) }}
                                            • {{ $allocation->from_date->diffInDays($allocation->to_date) }} nights
                                        </p>
                                    </div>
                                    <span class="text-xs px-2 py-1 rounded-full 
                                        {{ $allocation->from_date->isPast() && $allocation->to_date->isPast() ? 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' : 
                                           ($allocation->from_date->isPast() && $allocation->to_date->isFuture() ? 'bg-green-100 text-green-600 dark:bg-green-500/15 dark:text-green-400' : 
                                           'bg-blue-100 text-primary dark:bg-primary/15 dark:text-blue-400') }}">
                                        {{ $allocation->from_date->isPast() && $allocation->to_date->isPast() ? 'Past' : 
                                           ($allocation->from_date->isPast() && $allocation->to_date->isFuture() ? 'Current' : 'Upcoming') }}
                                    </span>
                                </div>
                                @if($allocation->guest_notes)
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    <i class="fas fa-sticky-note text-xs mr-1"></i>
                                    {{ $allocation->guest_notes }}
                                </p>
                                @endif
                                <div class="flex items-center gap-3 mt-2 text-xs text-gray-500 dark:text-gray-400">
                                    <span class="inline-flex items-center gap-1">
                                        <i class="fas fa-user"></i>
                                        {{ $allocation->adults }}A, {{ $allocation->children }}C
                                    </span>
                                    <span class="inline-flex items-center gap-1">
                                        <i class="fas fa-money-bill"></i>
                                        {{ SystemHelper::currencySymbol() }} {{ $allocation->rate_applied }}/night
                                    </span>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8">
                            <i class="fas fa-history text-3xl text-gray-300 dark:text-gray-600 mb-3"></i>
                            <p class="text-gray-500 dark:text-gray-400">No room history found</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Actions & Info -->
        <div class="space-y-6">
            <!-- Quick Actions Card -->
            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Quick Actions</h3>
                </div>
                <div class="p-5">
                    <div class="space-y-3">
                        <!-- Change Status -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" type="button"
                                    class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-white/[0.03] dark:hover:text-white">
                                <span class="flex items-center gap-2">
                                    <i class="fas fa-exchange-alt"></i>
                                    Change Status
                                </span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            
                            <div x-show="open" @click.outside="open = false" x-transition
                                 class="absolute top-full mt-1 w-full rounded-lg border border-gray-200 bg-white p-2 shadow-lg dark:border-gray-700 dark:bg-gray-800 z-50"
                                 x-cloak>
                                @foreach(\App\Models\Room::getStatusOptions() as $value => $label)
                                <button type="button"
                                        @click="changeStatus('{{ $value }}'); open = false;"
                                        class="flex w-full items-center gap-2 rounded px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                                    <i class="fas fa-circle text-xs 
                                        {{ $value === 'available' ? 'text-success' : 
                                           ($value === 'occupied' ? 'text-warning' : 
                                           ($value === 'maintenance' ? 'text-danger' : 'text-gray-500')) }}"></i>
                                    {{ $label }}
                                </button>
                                @endforeach
                            </div>
                        </div>
                        
                        <!-- Print Room Details -->
                        <button type="button" @click="printRoomDetails()"
                                class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-white/[0.03] dark:hover:text-white">
                            <span class="flex items-center gap-2">
                                <i class="fas fa-print"></i>
                                Print Room Details
                            </span>
                            <i class="fas fa-arrow-right text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Room Type Info Card -->
            @if($room->roomType)
            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Room Type Details</h3>
                </div>
                <div class="p-5">
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Room Type</p>
                            <p class="text-lg font-semibold text-primary">{{ $room->roomType->name }}</p>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Base Rate</p>
                                <p class="font-semibold text-gray-800 dark:text-white/90">
                                    {{ SystemHelper::currencySymbol() }} {{ number_format($room->roomType->base_rate, 2) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Capacity</p>
                                <p class="font-semibold text-gray-800 dark:text-white/90">
                                    {{ $room->roomType->capacity }} guests
                                </p>
                            </div>
                        </div>
                        
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Bed Type</p>
                            <p class="font-semibold text-gray-800 dark:text-white/90">
                                {{ ucfirst($room->roomType->bed_type) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

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
                                {{ $room->created_at?->format('D, ' . SystemHelper::dateFormat()) }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Last Updated</span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white/90">
                                {{ $room->updated_at?->format('D, ' . SystemHelper::dateFormat()) }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Total Allocations</span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white/90">
                                {{ $room->allocations()->count() }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Room Modal -->
@include('rooms.partials.modal-form')

<!-- Availability Check Modal -->
@include('rooms.partials.availability-modal')

<script>
function roomShow() {
    return {
        isEditModalOpen: false,
        isAvailabilityModalOpen: false,
        isLoading: false,
        
        // Form data for edit modal
        formData: {
            id: {{ $room->id }},
            room_number: '{{ $room->room_number }}',
            room_type_id: {{ $room->room_type_id }},
            status: '{{ $room->status }}',
            floor: {{ $room->floor }},
            wing: '{{ $room->wing ?? '' }}',
            adult_price: '{{ $room->adult_price ?? '' }}',
            child_price: '{{ $room->child_price ?? '' }}',
            features: @json($room->features ?? []),
            new_feature: ''
        },
        
        // Availability data
        availabilityData: {
            room_id: {{ $room->id }},
            room_number: '{{ $room->room_number }}',
            arrival_date: new Date().toISOString().split('T')[0],
            departure_date: new Date(Date.now() + 86400000).toISOString().split('T')[0],
            available_rooms: [],
            is_available: false
        },
        
        // Available features
        availableFeatures: [
            'Sea View', 'Mountain View', 'Balcony', 'Mini Bar',
            'Safe', 'TV', 'AC', 'Heating', 'Bathtub', 'Shower',
            'Kitchenette', 'Coffee Maker', 'Free WiFi', 'Room Service'
        ],
        
        // Room types for dropdown
        roomTypes: @json($roomTypes ?? []),
        
        init() {
            // Initialization if needed
        },
        
        openEditModal() {
            this.isEditModalOpen = true;
            document.body.classList.add('overflow-hidden');
        },
        
        openAvailabilityModal() {
            this.isAvailabilityModalOpen = true;
            document.body.classList.add('overflow-hidden');
        },
        
        closeModal() {
            this.isEditModalOpen = false;
            this.isAvailabilityModalOpen = false;
            document.body.classList.remove('overflow-hidden');
        },
        
        // Feature management
        addFeature() {
            if (this.formData.new_feature.trim()) {
                this.formData.features.push(this.formData.new_feature.trim());
                this.formData.new_feature = '';
            }
        },
        
        removeFeature(index) {
            this.formData.features.splice(index, 1);
        },
        
        // Change room status
        async changeStatus(newStatus) {
            if (!confirm('Are you sure you want to change the room status?')) {
                return;
            }
            
            this.isLoading = true;
            
            try {
                const response = await fetch('/rooms/{{ $room->id }}/status', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ status: newStatus })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showToast('Room status updated successfully', 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    this.showToast(data.message || 'Error updating status', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                this.showToast('An error occurred', 'error');
            } finally {
                this.isLoading = false;
            }
        },
        
        // Update room
        async submitForm() {
            this.isLoading = true;
            
            try {
                const response = await fetch('/rooms/{{ $room->id }}', {
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
                    this.showToast('Room updated successfully', 'success');
                    this.closeModal();
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    this.showToast(data.message || 'Error updating room', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                this.showToast('An error occurred', 'error');
            } finally {
                this.isLoading = false;
            }
        },
        
        // Check availability
        async checkAvailability() {
            if (!this.availabilityData.arrival_date || !this.availabilityData.departure_date) {
                this.showToast('Please select arrival and departure dates', 'error');
                return;
            }
            
            if (new Date(this.availabilityData.arrival_date) >= new Date(this.availabilityData.departure_date)) {
                this.showToast('Departure date must be after arrival date', 'error');
                return;
            }
            
            this.isLoading = true;
            
            try {
                const response = await fetch('/api/rooms/check-availability', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        arrival_date: this.availabilityData.arrival_date,
                        departure_date: this.availabilityData.departure_date
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    const targetRoom = data.rooms.find(room => room.id === this.availabilityData.room_id);
                    this.availabilityData.is_available = targetRoom?.is_available || false;
                    this.availabilityData.available_rooms = data.rooms;
                    
                    if (targetRoom?.is_available) {
                        this.showToast(`Room ${this.availabilityData.room_number} is available for selected dates`, 'success');
                    } else {
                        this.showToast(`Room ${this.availabilityData.room_number} is not available. Next available from ${targetRoom?.next_available_date || 'N/A'}`, 'warning');
                    }
                } else {
                    this.showToast(data.message || 'Error checking availability', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                this.showToast('An error occurred', 'error');
            } finally {
                this.isLoading = false;
            }
        },
        
        // Print room details
        printRoomDetails() {
            const printWindow = window.open('', '_blank');
            const content = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Room {{ $room->room_number }} Details</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        .header { text-align: center; margin-bottom: 30px; }
                        .section { margin-bottom: 20px; }
                        .label { font-weight: bold; color: #666; }
                        .value { margin-bottom: 10px; }
                        .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #666; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>Room {{ $room->room_number }}</h1>
                        <h2>{{ $room->roomType->name ?? 'N/A' }}</h2>
                        <p>Printed on ${new Date().toLocaleDateString()}</p>
                    </div>
                    
                    <div class="section">
                        <h3>Room Information</h3>
                        <div class="value">
                            <span class="label">Room Number:</span> {{ $room->room_number }}
                        </div>
                        <div class="value">
                            <span class="label">Room Type:</span> {{ $room->roomType->name ?? 'N/A' }}
                        </div>
                        <div class="value">
                            <span class="label">Status:</span> {{ \App\Models\Room::getStatusOptions()[$room->status] ?? $room->status }}
                        </div>
                        <div class="value">
                            <span class="label">Floor:</span> {{ $room->floor }}
                        </div>
                        <div class="value">
                            <span class="label">Wing:</span> {{ $room->wing ?? 'N/A' }}
                        </div>
                        <div class="value">
                            <span class="label">Pricing:</span> 
                            Adult: {{ SystemHelper::currencySymbol() }} {{ number_format($room->adult_price ?: $room->roomType->base_rate ?? 0, 2) }} | 
                            Child: {{ SystemHelper::currencySymbol() }} {{ number_format($room->child_price ?: ($room->roomType->base_rate ?? 0) * 0.5, 2) }}
                        </div>
                    </div>
                    
                    @if($room->features && count($room->features) > 0)
                    <div class="section">
                        <h3>Room Features</h3>
                        <ul>
                            @foreach($room->features as $feature)
                            <li>{{ $feature }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    
                    @if($room->currentStay)
                    <div class="section">
                        <h3>Current Stay</h3>
                        <div class="value">
                            <span class="label">Stay ID:</span> {{ $room->currentStay->id }}
                        </div>
                        <div class="value">
                            <span class="label">Check-in:</span> {{ $room->currentStay->arrival_date?->format('D, ' . SystemHelper::dateFormat()) }}
                        </div>
                        <div class="value">
                            <span class="label">Check-out:</span> {{ $room->currentStay->departure_date?->format('D, ' . SystemHelper::dateFormat()) }}
                        </div>
                    </div>
                    @endif
                    
                    <div class="footer">
                        <p>Printed from {{ config('app.name') }} Hotel Management System</p>
                        <p>Room ID: {{ $room->id }}</p>
                    </div>
                </body>
                </html>
            `;
            
            printWindow.document.write(content);
            printWindow.document.close();
            printWindow.print();
        },
        
        // Load history
        async loadHistory() {
            window.location.reload();
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
                'bg-primary'
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