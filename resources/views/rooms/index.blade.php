@extends('layouts.app')


@section('content')
    <!-- Breadcrumb Start -->
    <div x-data="{ pageName: `Rooms Management`}">
        @include('partials.breadcrumb')
    </div>
    <!-- Breadcrumb End -->
<div class="p-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Rooms Management</h2>
            <p class="text-gray-600 dark:text-gray-400">Manage all rooms and their availability</p>
        </div>
        
        <div class="flex items-center gap-3">
            <!-- Filter Dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" type="button"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-800 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-white/[0.03] dark:hover:text-white">
                    <i class="fas fa-filter"></i>
                    Filter
                    <i class="fas fa-chevron-down text-xs"></i>
                </button>
                
                <div x-show="open" @click.outside="open = false" x-transition
                     class="absolute right-0 top-full mt-2 w-64 rounded-lg border border-gray-200 bg-white p-4 shadow-lg dark:border-gray-700 dark:bg-gray-800 z-50"
                     x-cloak>
                    <div class="space-y-4">
                        <!-- Status Filter -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Status
                            </label>
                            <select x-model="filters.status" @change="applyFilters()"
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-3 py-2 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                                <option value="">All Status</option>
                                @foreach(\App\Models\Room::getStatusOptions() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Floor Filter -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Floor
                            </label>
                            <input type="number" x-model="filters.floor" @change="applyFilters()"
                                   class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-3 py-2 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                   placeholder="Floor number">
                        </div>
                        
                        <!-- Wing Filter -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Wing
                            </label>
                            <input type="text" x-model="filters.wing" @change="applyFilters()"
                                   class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-3 py-2 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                   placeholder="Wing">
                        </div>
                        
                        <div class="flex justify-between gap-2">
                            <button @click="clearFilters()" type="button"
                                    class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm hover:bg-gray-50 dark:border-gray-600">
                                Clear
                            </button>
                            <button @click="open = false" type="button"
                                    class="flex-1 rounded-lg bg-primary px-3 py-2 text-sm text-white hover:bg-primary/90">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Add Room Button -->
            <button @click="openCreateModal()"
                    class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs transition-colors hover:bg-primary/90">
                <i class="fas fa-plus"></i> Add Room
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Rooms</p>
                    <h4 class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ $rooms->count() }}</h4>
                </div>
                <div class="rounded-full bg-primary/10 p-3">
                    <i class="fas fa-hotel text-primary text-lg"></i>
                </div>
            </div>
        </div>
        
        <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Available</p>
                    <h4 class="text-2xl font-bold text-success dark:text-success">{{ $rooms->where('status', 'available')->count() }}</h4>
                </div>
                <div class="rounded-full bg-success/10 p-3">
                    <i class="fas fa-door-open text-success text-lg"></i>
                </div>
            </div>
        </div>
        
        <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Occupied</p>
                    <h4 class="text-2xl font-bold text-warning dark:text-warning">{{ $rooms->where('status', 'occupied')->count() }}</h4>
                </div>
                <div class="rounded-full bg-warning/10 p-3">
                    <i class="fas fa-bed text-warning text-lg"></i>
                </div>
            </div>
        </div>
        
        <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Maintenance</p>
                    <h4 class="text-2xl font-bold text-danger dark:text-danger">{{ $rooms->whereIn('status', ['maintenance', 'out_of_order'])->count() }}</h4>
                </div>
                <div class="rounded-full bg-danger/10 p-3">
                    <i class="fas fa-tools text-danger text-lg"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Rooms Table -->
    <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="custom-scrollbar overflow-x-auto">
            <table class="min-w-full text-left text-sm text-gray-700 dark:border-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr class="border-b border-gray-100 whitespace-nowrap dark:border-gray-800">
                        <th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
                            Room Number
                        </th>
                        <th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
                            Room Type
                        </th>
                        <th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
                            Floor / Wing
                        </th>
                        <th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
                            Status
                        </th>
                        <th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
                            Features
                        </th>
                        <th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
                            Current Guest
                        </th>
                        <th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-white/[0.03]">
                    @forelse($rooms as $room)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-lg bg-primary/10 flex items-center justify-center">
                                    <i class="fas fa-door-closed text-primary"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                                        {{ $room->room_number }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        ID: {{ $room->id }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <div>
                                <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                                    {{ $room->roomType->name ?? 'N/A' }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    <span class="inline-flex items-center gap-1">
                                        <i class="fas fa-user"></i>
                                        {{ $room->roomType->capacity ?? 1 }} guests
                                    </span>
                                    • 
                                    <span class="inline-flex items-center gap-1">
                                        <i class="fas fa-bed"></i>
                                        {{ $room->roomType->bed_type ?? 'Double' }}
                                    </span>
                                </p>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-1 text-gray-700 dark:text-gray-300">
                                    <i class="fas fa-layer-group"></i>
                                    Floor {{ $room->floor }}
                                </span>
                                @if($room->wing)
                                <span class="inline-flex items-center gap-1 text-gray-500 dark:text-gray-400">
                                    <i class="fas fa-building"></i>
                                    {{ $room->wing }}
                                </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium"
                                  @class([
                                    'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500' => $room->status === 'available',
                                    'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-orange-400' => $room->status === 'occupied',
                                    'bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400' => $room->status === 'cleaning',
                                    'bg-danger-50 text-danger-600 dark:bg-danger-500/15 dark:text-danger-500' => in_array($room->status, ['maintenance', 'out_of_order']),
                                    'bg-gray-50 text-gray-600 dark:bg-gray-500/15 dark:text-gray-400' => $room->status === 'reserved',
                                  ])>
                                {{ \App\Models\Room::getStatusOptions()[$room->status] ?? $room->status }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            @if($room->features && count($room->features) > 0)
                            <div class="flex flex-wrap gap-1">
                                @foreach(array_slice($room->features, 0, 3) as $feature)
                                <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-1 text-xs text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                    <i class="fas fa-check text-xs"></i>
                                    {{ $feature }}
                                </span>
                                @endforeach
                                @if(count($room->features) > 3)
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-1 text-xs text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                    +{{ count($room->features) - 3 }} more
                                </span>
                                @endif
                            </div>
                            @else
                            <span class="text-gray-400 dark:text-gray-500 text-sm">No features</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @if($room->currentStay)
                            <div class="flex items-center gap-2">
                                <div class="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center">
                                    <i class="fas fa-user text-primary text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                                        {{ $room->currentStay->guest->user->name ?? 'Guest' }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Stay #{{ $room->currentStay->id }}
                                    </p>
                                </div>
                            </div>
                            @else
                            <span class="text-gray-400 dark:text-gray-500 text-sm">Vacant</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('rooms.show', $room) }}"
                                   class="inline-flex items-center gap-1 rounded-lg bg-primary/10 px-2.5 py-1 text-xs font-medium text-primary hover:bg-primary/20">
                                    <i class="fas fa-eye text-xs"></i>
                                    View
                                </a>
                                <button @click="openEditModal(@json($room))"
                                        class="inline-flex items-center gap-1 rounded-lg bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-600 hover:bg-blue-200 dark:bg-blue-500/15 dark:text-blue-400">
                                    <i class="fas fa-edit text-xs"></i>
                                    Edit
                                </button>
                                <button @click="openAvailabilityModal(@json($room))"
                                        class="inline-flex items-center gap-1 rounded-lg bg-green-100 px-2.5 py-1 text-xs font-medium text-green-600 hover:bg-green-200 dark:bg-green-500/15 dark:text-green-400">
                                    <i class="fas fa-calendar-check text-xs"></i>
                                    Check Availability
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-8 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                                <i class="fas fa-hotel text-4xl mb-3"></i>
                                <p class="text-lg">No rooms found</p>
                                <p class="text-sm mt-1">Create your first room to get started</p>
                                <button @click="openCreateModal()" 
                                        class="mt-4 inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/90">
                                    <i class="fas fa-plus"></i> Add First Room
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        

    </div>
</div>

<!-- Create/Edit Room Modal -->
@include('rooms.partials.modal-form')

<!-- Availability Check Modal -->
@include('rooms.partials.availability-modal')

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('roomManagement', () => ({
        // Modal states
        isCreateModalOpen: false,
        isEditModalOpen: false,
        isAvailabilityModalOpen: false,
        isLoading: false,
        
        // Form data
        formData: {
            id: null,
            room_number: '',
            room_type_id: '',
            status: 'available',
            floor: 1,
            wing: '',
            adult_price: '',
            child_price: '',
            features: [],
            new_feature: ''
        },
        
        // Filters
        filters: {
            status: '',
            floor: '',
            wing: ''
        },
        
        // Availability check data
        availabilityData: {
            room_id: null,
            room_number: '',
            arrival_date: '',
            departure_date: '',
            available_rooms: [],
            is_available: false
        },
        
        // Room types for dropdown
        roomTypes: @json($roomTypes ?? []),
        
        // Available features
        availableFeatures: [
            'Sea View', 'Mountain View', 'Balcony', 'Mini Bar',
            'Safe', 'TV', 'AC', 'Heating', 'Bathtub', 'Shower',
            'Kitchenette', 'Coffee Maker', 'Free WiFi', 'Room Service'
        ],
        
        init() {
            // Set default dates for availability check
            const today = new Date().toISOString().split('T')[0];
            const tomorrow = new Date(Date.now() + 86400000).toISOString().split('T')[0];
            this.availabilityData.arrival_date = today;
            this.availabilityData.departure_date = tomorrow;
        },
        
        // Open modals
        openCreateModal() {
            this.resetForm();
            this.isCreateModalOpen = true;
            document.body.classList.add('overflow-hidden');
        },
        
        openEditModal(room) {
            this.formData = {
                id: room.id,
                room_number: room.room_number,
                room_type_id: room.room_type_id,
                status: room.status,
                floor: room.floor,
                wing: room.wing || '',
                adult_price: room.adult_price || '',
                child_price: room.child_price || '',
                features: room.features || [],
                new_feature: ''
            };
            this.isEditModalOpen = true;
            document.body.classList.add('overflow-hidden');
        },
        
        openAvailabilityModal(room) {
            this.availabilityData.room_id = room.id;
            this.availabilityData.room_number = room.room_number;
            this.isAvailabilityModalOpen = true;
            document.body.classList.add('overflow-hidden');
        },
        
        closeModal() {
            this.isCreateModalOpen = false;
            this.isEditModalOpen = false;
            this.isAvailabilityModalOpen = false;
            document.body.classList.remove('overflow-hidden');
            this.resetForm();
        },
        
        resetForm() {
            this.formData = {
                id: null,
                room_number: '',
                room_type_id: '',
                status: 'available',
                floor: 1,
                wing: '',
                adult_price: '',
                child_price: '',
                features: [],
                new_feature: ''
            };
            this.isLoading = false;
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
        
        // Form submission
        async submitForm() {
            this.isLoading = true;
            
            const url = this.formData.id 
                ? `/rooms/${this.formData.id}`
                : '/rooms';
            const method = this.formData.id ? 'PUT' : 'POST';
            
            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(this.formData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showToast(`Room ${this.formData.id ? 'updated' : 'created'} successfully`, 'success');
                    this.closeModal();
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    this.showToast(data.message || 'Error saving room', 'error');
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
                    // Find the specific room
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
        
        // Filter functions
        applyFilters() {
            const params = new URLSearchParams();
            
            if (this.filters.status) params.append('status', this.filters.status);
            if (this.filters.floor) params.append('floor', this.filters.floor);
            if (this.filters.wing) params.append('wing', this.filters.wing);
            
            window.location.href = '/rooms?' + params.toString();
        },
        
        clearFilters() {
            this.filters = {
                status: '',
                floor: '',
                wing: ''
            };
            window.location.href = '/rooms';
        },
        
        // Helper functions
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
    }));
});
</script>
@endsection