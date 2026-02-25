@extends('layouts.app')

@section('content')
    <!-- Breadcrumb Start -->
    <div x-data="{ pageName: `Rooms Types Management`}">
        @include('partials.breadcrumb')
    </div>
    <!-- Breadcrumb End -->
<div class="p-6 space-y-6" x-data="roomTypeManagement()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Room Types</h2>
            <p class="text-gray-600 dark:text-gray-400">Manage room types and their configurations</p>
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
                        <!-- Bed Type Filter -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Bed Type
                            </label>
                            <select x-model="filters.bed_type" @change="applyFilters()"
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-3 py-2 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                                <option value="">All Bed Types</option>
                                @foreach(\App\Models\RoomType::getBedTypeOptions() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Capacity Filter -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Min Capacity
                            </label>
                            <input type="number" x-model="filters.min_capacity" @change="applyFilters()"
                                   class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-3 py-2 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                   placeholder="Minimum guests">
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
            
            <!-- Search -->
            <div class="relative">
                <input type="text" placeholder="Search room types..." 
                       class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 pl-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 sm:w-[250px]"
                       x-model="searchTerm" @input.debounce.500ms="searchRoomTypes()">
                <span class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                    <i class="fas fa-search"></i>
                </span>
            </div>
            
            <!-- Add Room Type Button -->
            <button @click="openCreateModal()"
                    class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs transition-colors hover:bg-primary/90">
                <i class="fas fa-plus"></i> Add Room Type
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Room Types</p>
                    <h4 class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ $roomTypes->total() }}</h4>
                </div>
                <div class="rounded-full bg-primary/10 p-3">
                    <i class="fas fa-tags text-primary text-lg"></i>
                </div>
            </div>
        </div>
        
        <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Rooms</p>
                    <h4 class="text-2xl font-bold text-primary dark:text-primary">{{ $roomTypes->sum('rooms_count') }}</h4>
                </div>
                <div class="rounded-full bg-primary/10 p-3">
                    <i class="fas fa-hotel text-primary text-lg"></i>
                </div>
            </div>
        </div>
        
        <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Available Rooms</p>
                    <h4 class="text-2xl font-bold text-success dark:text-success">{{ $roomTypes->sum('available_rooms_count') }}</h4>
                </div>
                <div class="rounded-full bg-success/10 p-3">
                    <i class="fas fa-door-open text-success text-lg"></i>
                </div>
            </div>
        </div>
        
        <div class="rounded-2xl border border-gray-100 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Avg. Rate</p>
                    <h4 class="text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ SystemHelper::currencySymbol() }} {{ number_format($roomTypes->avg('base_rate') ?? 0, 2) }}
                    </h4>
                </div>
                <div class="rounded-full bg-primary/10 p-3">
                    <i class="fas fa-money-bill text-blue-500 text-lg"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Room Types Table -->
    <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="custom-scrollbar overflow-x-auto">
            <table class="min-w-full text-left text-sm text-gray-700 dark:border-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr class="border-b border-gray-100 whitespace-nowrap dark:border-gray-800">
                        <th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
                            Room Type
                        </th>
                        <th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
                            Code & Rate
                        </th>
                        <th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
                            Capacity & Bed
                        </th>
                        <th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
                            Rooms
                        </th>
                        <th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
                            Amenities
                        </th>
                        <th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-white/[0.03]">
                    @forelse($roomTypes as $roomType)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-lg bg-primary/10 flex items-center justify-center">
                                    <i class="fas fa-bed text-primary"></i>
                                </div>
                                <div>
                                    <a href="{{ route('room-types.show', $roomType) }}" 
                                       class="text-sm font-medium text-gray-800 hover:text-primary dark:text-white/90 dark:hover:text-primary">
                                        {{ $roomType->name }}
                                    </a>
                                    @if($roomType->description)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 line-clamp-1">
                                        {{ Str::limit($roomType->description, 60) }}
                                    </p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <div>
                                <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                                    {{ $roomType->code }}
                                </p>
                                <p class="text-lg font-bold text-primary">
                                    {{ SystemHelper::currencySymbol() }} {{ number_format($roomType->base_rate, 2) }}
                                    <span class="text-xs font-normal text-gray-500 dark:text-gray-400">/night</span>
                                </p>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1 text-gray-700 dark:text-gray-300">
                                        <i class="fas fa-user"></i>
                                        {{ $roomType->capacity }} guests
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center gap-1 text-gray-500 dark:text-gray-400">
                                        <i class="fas fa-bed"></i>
                                        {{ \App\Models\RoomType::getBedTypeOptions()[$roomType->bed_type] ?? $roomType->bed_type }}
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="space-y-1">
                                <div class="flex items-center gap-3">
                                    <div>
                                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                                            {{ $roomType->rooms_count }}
                                            <span class="text-xs text-gray-500 dark:text-gray-400">total</span>
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-success">
                                            {{ $roomType->available_rooms_count }}
                                            <span class="text-xs text-gray-500 dark:text-gray-400">available</span>
                                        </p>
                                    </div>
                                </div>
                                @if($roomType->rooms_count > 0)
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    @php
                                        $roomNumbers = $roomType->rooms->pluck('room_number')->take(3)->implode(', ');
                                        $remaining = $roomType->rooms_count - 3;
                                    @endphp
                                    Rooms: {{ $roomNumbers }}
                                    @if($remaining > 0)
                                    +{{ $remaining }} more
                                    @endif
                                </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            @if($roomType->amenities && count($roomType->amenities) > 0)
                            <div class="flex flex-wrap gap-1">
                                @foreach(array_slice($roomType->amenities, 0, 3) as $amenity)
                                <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-1 text-xs text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                    <i class="fas fa-check text-xs"></i>
                                    {{ $amenity }}
                                </span>
                                @endforeach
                                @if(count($roomType->amenities) > 3)
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-1 text-xs text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                    +{{ count($roomType->amenities) - 3 }} more
                                </span>
                                @endif
                            </div>
                            @else
                            <span class="text-gray-400 dark:text-gray-500 text-sm">No amenities</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('room-types.show', $roomType) }}"
                                   class="inline-flex items-center gap-1 rounded-lg bg-primary/10 px-2.5 py-1 text-xs font-medium text-primary hover:bg-primary/20">
                                    <i class="fas fa-eye text-xs"></i>
                                    View
                                </a>
                                <button @click="openEditModal(@json($roomType))"
                                        class="inline-flex items-center gap-1 rounded-lg bg-blue-100 px-2.5 py-1 text-xs font-medium text-primary hover:bg-blue-200 dark:bg-primary/15 dark:text-blue-400">
                                    <i class="fas fa-edit text-xs"></i>
                                    Edit
                                </button>
                                <button @click="openDeleteModal(@json($roomType))"
                                        class="inline-flex items-center gap-1 rounded-lg bg-red-100 px-2.5 py-1 text-xs font-medium text-red-600 hover:bg-red-200 dark:bg-red-500/15 dark:text-red-400">
                                    <i class="fas fa-trash text-xs"></i>
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                                <i class="fas fa-bed text-4xl mb-3"></i>
                                <p class="text-lg">No room types found</p>
                                <p class="text-sm mt-1">Create your first room type to get started</p>
                                <button @click="openCreateModal()" 
                                        class="mt-4 inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/90">
                                    <i class="fas fa-plus"></i> Add First Room Type
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($roomTypes->hasPages())
        <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
            {{ $roomTypes->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Create/Edit Room Type Modal -->
@include('room-types.partials.modal-form')

<!-- Delete Confirmation Modal -->
@include('room-types.partials.delete-modal')

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('roomTypeManagement', () => ({
        // Modal states
        isCreateModalOpen: false,
        isEditModalOpen: false,
        isDeleteModalOpen: false,
        isLoading: false,
        
        // Form data
        formData: {
            id: null,
            name: '',
            code: '',
            base_rate: '',
            capacity: 2,
            bed_type: 'double',
            description: '',
            amenities: [],
            new_amenity: ''
        },
        
        // Delete confirmation data
        deleteData: {
            id: null,
            name: ''
        },
        
        // Filters
        filters: {
            bed_type: '',
            min_capacity: ''
        },
        
        // Search
        searchTerm: '',
        
        // Available amenities
        availableAmenities: [
            'Free WiFi', 'TV', 'Air Conditioning', 'Heating', 'Mini Bar',
            'Safe', 'Room Service', 'Daily Housekeeping', 'Private Bathroom',
            'Hairdryer', 'Iron', 'Desk', 'Telephone', 'Wake-up Service',
            'Breakfast Included', 'Balcony', 'Sea View', 'Mountain View',
            'Coffee Maker', 'Tea/Coffee Facilities', 'Refrigerator'
        ],
        
        // Bed types for dropdown
        bedTypes: @json(\App\Models\RoomType::getBedTypeOptions()),
        
        init() {
            // Set URL parameters from current filters
            const urlParams = new URLSearchParams(window.location.search);
            this.filters.bed_type = urlParams.get('bed_type') || '';
            this.filters.min_capacity = urlParams.get('min_capacity') || '';
            this.searchTerm = urlParams.get('search') || '';
        },
        
        // Open modals
        openCreateModal() {
            this.resetForm();
            this.isCreateModalOpen = true;
            document.body.classList.add('overflow-hidden');
        },
        
        openEditModal(roomType) {
            this.formData = {
                id: roomType.id,
                name: roomType.name,
                code: roomType.code,
                base_rate: roomType.base_rate,
                capacity: roomType.capacity,
                bed_type: roomType.bed_type,
                description: roomType.description || '',
                amenities: roomType.amenities || [],
                new_amenity: ''
            };
            this.isEditModalOpen = true;
            document.body.classList.add('overflow-hidden');
        },
        
        openDeleteModal(roomType) {
            this.deleteData = {
                id: roomType.id,
                name: roomType.name
            };
            this.isDeleteModalOpen = true;
            document.body.classList.add('overflow-hidden');
        },
        
        closeModal() {
            this.isCreateModalOpen = false;
            this.isEditModalOpen = false;
            this.isDeleteModalOpen = false;
            document.body.classList.remove('overflow-hidden');
            this.resetForm();
        },
        
        resetForm() {
            this.formData = {
                id: null,
                name: '',
                code: '',
                base_rate: '',
                capacity: 2,
                bed_type: 'double',
                description: '',
                amenities: [],
                new_amenity: ''
            };
            this.isLoading = false;
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
        
        // Form submission
        async submitForm() {
            this.isLoading = true;
            
            const url = this.formData.id 
                ? `/room-types/${this.formData.id}`
                : '/room-types';
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
                    this.showToast(`Room type ${this.formData.id ? 'updated' : 'created'} successfully`, 'success');
                    this.closeModal();
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    this.showToast(data.message || 'Error saving room type', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                this.showToast('An error occurred', 'error');
            } finally {
                this.isLoading = false;
            }
        },
        
        // Delete room type
        async deleteRoomType() {
            this.isLoading = true;
            
            try {
                const response = await fetch(`/room-types/${this.deleteData.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                if (response.ok) {
                    this.showToast('Room type deleted successfully', 'success');
                    this.closeModal();
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    const data = await response.json();
                    this.showToast(data.message || 'Error deleting room type', 'error');
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
            
            if (this.filters.bed_type) params.append('bed_type', this.filters.bed_type);
            if (this.filters.min_capacity) params.append('min_capacity', this.filters.min_capacity);
            
            window.location.href = '/room-types?' + params.toString();
        },
        
        clearFilters() {
            this.filters = {
                bed_type: '',
                min_capacity: ''
            };
            window.location.href = '/room-types';
        },
        
        // Search function
        searchRoomTypes() {
            const params = new URLSearchParams(window.location.search);
            
            if (this.searchTerm) {
                params.set('search', this.searchTerm);
            } else {
                params.delete('search');
            }
            
            window.location.href = '/room-types?' + params.toString();
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
    }));
});
</script>
@endsection