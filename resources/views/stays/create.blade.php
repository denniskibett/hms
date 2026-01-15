@extends('layouts.app')

@section('content')
<div x-data="{ pageName: `New Stay Booking` }">
    @include('partials.breadcrumb')
</div>

<div class="p-4 mx-auto max-w-screen-2xl md:p-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Create New Stay</h2>
                <p class="text-gray-600 dark:text-gray-400">Book a new guest stay</p>
            </div>
            <div>
                <a href="{{ route('stays.index') }}" class="shadow-theme-xs inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 ring-1 ring-gray-300 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Stays
                </a>
            </div>
        </div>
    </div>

    <form action="{{ route('stays.store') }}" method="POST" id="stayForm">
        @csrf
        
        <div class="grid grid-cols-12 gap-6">
            <!-- Main Form -->
            <div class="col-span-12 lg:col-span-8">
                <div class="space-y-6">
                    <!-- Guest Selection Card -->
                    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/3">
                        <div class="px-6 py-5">
                            <div class="flex items-center mb-4">
                                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-primary/10 text-primary mr-3">
                                    <span>1</span>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Guest Information</h3>
                            </div>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-800 dark:text-white/90">
                                        Select Guest *
                                    </label>
                                    <div class="flex gap-2">
                                        <div class="flex-1">
                                            <select name="guest_id" id="guest_id" 
                                                class="block w-full rounded-lg border-gray-300 px-4 py-3 text-sm font-medium placeholder:text-gray-400 focus:border-primary focus:ring-primary dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:focus:border-primary"
                                                required>
                                                <option value="">Select a guest...</option>
                                                @foreach($guests as $guest)
                                                <option value="{{ $guest->id }}" 
                                                    data-email="{{ $guest->email }}"
                                                    data-phone="{{ $guest->phone }}"
                                                    data-nationality="{{ optional($guest->guest)->nationality }}">
                                                    {{ $guest->name }} ({{ $guest->email }})
                                                </option>
                                                @endforeach
                                                <option value="new">+ Add New Guest</option>
                                            </select>
                                            @error('guest_id')
                                            <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- New Guest Fields (Hidden by default) -->
                                <div id="newGuestFields" class="hidden space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="mb-2 block text-sm font-medium text-gray-800 dark:text-white/90">
                                                First Name *
                                            </label>
                                            <input type="text" name="guest_first_name" 
                                                class="block w-full rounded-lg border-gray-300 px-4 py-3 text-sm font-medium placeholder:text-gray-400 focus:border-primary focus:ring-primary dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:focus:border-primary">
                                        </div>
                                        <div>
                                            <label class="mb-2 block text-sm font-medium text-gray-800 dark:text-white/90">
                                                Last Name *
                                            </label>
                                            <input type="text" name="guest_last_name" 
                                                class="block w-full rounded-lg border-gray-300 px-4 py-3 text-sm font-medium placeholder:text-gray-400 focus:border-primary focus:ring-primary dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:focus:border-primary">
                                        </div>
                                        <div>
                                            <label class="mb-2 block text-sm font-medium text-gray-800 dark:text-white/90">
                                                Email *
                                            </label>
                                            <input type="email" name="guest_email" 
                                                class="block w-full rounded-lg border-gray-300 px-4 py-3 text-sm font-medium placeholder:text-gray-400 focus:border-primary focus:ring-primary dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:focus:border-primary">
                                        </div>
                                        <div>
                                            <label class="mb-2 block text-sm font-medium text-gray-800 dark:text-white/90">
                                                Phone *
                                            </label>
                                            <input type="text" name="guest_phone" 
                                                class="block w-full rounded-lg border-gray-300 px-4 py-3 text-sm font-medium placeholder:text-gray-400 focus:border-primary focus:ring-primary dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:focus:border-primary">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Guest Information Display -->
                                <div id="guestInfo" class="hidden">
                                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900">
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div>
                                                <label class="mb-1 block text-xs text-gray-500 dark:text-gray-400">Email</label>
                                                <p id="guestEmail" class="text-sm text-gray-800 dark:text-white/90"></p>
                                            </div>
                                            <div>
                                                <label class="mb-1 block text-xs text-gray-500 dark:text-gray-400">Phone</label>
                                                <p id="guestPhone" class="text-sm text-gray-800 dark:text-white/90"></p>
                                            </div>
                                            <div>
                                                <label class="mb-1 block text-xs text-gray-500 dark:text-gray-400">Nationality</label>
                                                <p id="guestNationality" class="text-sm text-gray-800 dark:text-white/90"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stay Details Card -->
                    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/3">
                        <div class="px-6 py-5">
                            <div class="flex items-center mb-4">
                                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-primary/10 text-primary mr-3">
                                    <span>2</span>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Stay Details</h3>
                            </div>
                            
                            <div class="space-y-4">
                                <!-- Room Selection -->
                                <div id="roomSelectionSection">
                                    <label class="mb-2 block text-sm font-medium text-gray-800 dark:text-white/90">
                                        Select Room(s) *
                                    </label>
                                    <div class="space-y-3" id="roomAllocations">
                                        <!-- Single room allocation by default -->
                                        <div class="room-allocation border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                            <div class="flex items-center justify-between mb-3">
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Room 1</span>
                                                <button type="button" onclick="removeRoomAllocation(this)" class="text-danger hover:text-danger/80 text-sm hidden" id="removeRoomBtn">
                                                    <i class="fas fa-trash mr-1"></i> Remove
                                                </button>
                                            </div>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                        Room *
                                                    </label>
                                                    <select name="room_allocations[0][room_id]" 
                                                        class="room-select block w-full rounded-lg border-gray-300 px-4 py-3 text-sm font-medium placeholder:text-gray-400 focus:border-primary focus:ring-primary dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:focus:border-primary"
                                                        required>
                                                        <option value="">Select a room...</option>
                                                        @foreach($rooms as $room)
                                                        <option value="{{ $room->id }}" 
                                                            data-type="{{ $room->roomType->name }}"
                                                            data-price="{{ $room->roomType->price_per_night }}"
                                                            data-capacity="{{ $room->roomType->max_occupancy }}"
                                                            data-base-rate="{{ $room->roomType->base_rate ?? $room->roomType->price_per_night }}">
                                                            Room {{ $room->room_number }} - {{ $room->roomType->name }} ({{ SystemHelper::currencySymbol() }}{{ number_format($room->roomType->price_per_night, 2) }}/night)
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                        Rate per Night ({{ SystemHelper::currencySymbol() }})
                                                    </label>
                                                    <input type="number" name="room_allocations[0][rate]" 
                                                        class="room-rate block w-full rounded-lg border-gray-300 px-4 py-3 text-sm font-medium placeholder:text-gray-400 focus:border-primary focus:ring-primary dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:focus:border-primary"
                                                        step="0.01" min="0" value="0" required>
                                                </div>
                                                <div>
                                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                        Adults *
                                                    </label>
                                                    <input type="number" name="room_allocations[0][adults]" 
                                                        class="block w-full rounded-lg border-gray-300 px-4 py-3 text-sm font-medium placeholder:text-gray-400 focus:border-primary focus:ring-primary dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:focus:border-primary"
                                                        min="1" max="10" value="1" required>
                                                </div>
                                                <div>
                                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                        Children
                                                    </label>
                                                    <input type="number" name="room_allocations[0][children]" 
                                                        class="block w-full rounded-lg border-gray-300 px-4 py-3 text-sm font-medium placeholder:text-gray-400 focus:border-primary focus:ring-primary dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:focus:border-primary"
                                                        min="0" max="10" value="0">
                                                </div>
                                                <div class="md:col-span-2">
                                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                        Guest Notes (Optional)
                                                    </label>
                                                    <input type="text" name="room_allocations[0][guest_notes]" 
                                                        class="block w-full rounded-lg border-gray-300 px-4 py-3 text-sm font-medium placeholder:text-gray-400 focus:border-primary focus:ring-primary dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:focus:border-primary"
                                                        placeholder="e.g., Honeymoon suite, Near elevator, etc.">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <button type="button" onclick="addRoomAllocation()" class="mt-3 inline-flex items-center text-sm text-primary hover:text-primary/80">
                                        <i class="fas fa-plus mr-2"></i> Add Another Room
                                    </button>
                                </div>
                                
                                <!-- Dates -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-gray-800 dark:text-white/90">
                                            Arrival Date *
                                        </label>
                                        <input type="date" name="arrival_date" id="arrival_date" 
                                            class="block w-full rounded-lg border-gray-300 px-4 py-3 text-sm font-medium placeholder:text-gray-400 focus:border-primary focus:ring-primary dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:focus:border-primary"
                                            min="{{ date('Y-m-d') }}" required>
                                        @error('arrival_date')
                                        <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-gray-800 dark:text-white/90">
                                            Departure Date *
                                        </label>
                                        <input type="date" name="departure_date" id="departure_date" 
                                            class="block w-full rounded-lg border-gray-300 px-4 py-3 text-sm font-medium placeholder:text-gray-400 focus:border-primary focus:ring-primary dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:focus:border-primary"
                                            min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                                        @error('departure_date')
                                        <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                
                                <!-- Status -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-800 dark:text-white/90">
                                        Status *
                                    </label>
                                    <select name="status" 
                                        class="block w-full rounded-lg border-gray-300 px-4 py-3 text-sm font-medium placeholder:text-gray-400 focus:border-primary focus:ring-primary dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:focus:border-primary"
                                        required>
                                        <option value="reserved">Reserved</option>
                                        <option value="booked">Booked</option>
                                        <option value="checked_in">Checked In</option>
                                    </select>
                                    @error('status')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <!-- Special Requests -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-800 dark:text-white/90">
                                        Special Requests
                                    </label>
                                    <textarea name="special_requests" rows="3" 
                                        class="block w-full rounded-lg border-gray-300 px-4 py-3 text-sm font-medium placeholder:text-gray-400 focus:border-primary focus:ring-primary dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:focus:border-primary"
                                        placeholder="Any special requests or notes..."></textarea>
                                    @error('special_requests')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar - Booking Summary -->
            <div class="col-span-12 lg:col-span-4">
                <div class="space-y-6">
                    <!-- Booking Summary Card -->
                    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/3">
                        <div class="px-6 py-5">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">
                                Booking Summary
                            </h3>
                            
                            <!-- Quick Stats -->
                            <div class="grid grid-cols-2 gap-4 mb-6">
                                <div class="text-center rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                                    <p id="nightsCount" class="text-2xl font-bold text-gray-800 dark:text-white">0</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Nights</p>
                                </div>
                                <div class="text-center rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                                    <p id="totalPrice" class="text-2xl font-bold text-gray-800 dark:text-white">{{ SystemHelper::currencySymbol() }}0.00</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Total</p>
                                </div>
                            </div>
                            
                            <!-- Stay Timeline -->
                            <div class="mb-6">
                                <h4 class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">Stay Timeline</h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Check-in:</span>
                                        <span id="checkInDate" class="text-gray-800 dark:text-white">-</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Check-out:</span>
                                        <span id="checkOutDate" class="text-gray-800 dark:text-white">-</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Rooms:</span>
                                        <span id="roomCount" class="text-gray-800 dark:text-white">0</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Price Breakdown -->
                            <div class="mb-6">
                                <h4 class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">Price Breakdown</h4>
                                <div class="space-y-2 text-sm" id="priceBreakdown">
                                    <div class="text-center py-4 text-gray-500 dark:text-gray-400">
                                        Select rooms and dates to see pricing
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Important Notes -->
                            <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-900/20">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-exclamation-circle text-yellow-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Important Notes</h4>
                                        <div class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
                                            <ul class="list-disc pl-5 space-y-1">
                                                <li>Check-in time: 2:00 PM</li>
                                                <li>Check-out time: 11:00 AM</li>
                                                <li>Early check-in/late check-out subject to availability</li>
                                                <li>Proof of ID required at check-in</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Submit Card -->
                    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/3">
                        <div class="px-6 py-5">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Total Amount</span>
                                    <span id="finalTotal" class="text-lg font-bold text-primary">{{ SystemHelper::currencySymbol() }}0.00</span>
                                </div>
                                
                                <div class="flex gap-3">
                                    <a href="{{ route('stays.index') }}" 
                                       class="shadow-theme-xs flex-1 inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 ring-1 ring-gray-300 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]">
                                        Cancel
                                    </a>
                                    <button type="submit" 
                                            class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 flex-1 inline-flex items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-white transition">
                                        <i class="fas fa-save mr-1"></i> Create Stay
                                    </button>
                                </div>
                                
                                <p class="text-center text-xs text-gray-500 dark:text-gray-400">
                                    By creating this stay, you agree to our terms and conditions
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const guestSelect = document.getElementById('guest_id');
    const guestInfo = document.getElementById('guestInfo');
    const newGuestFields = document.getElementById('newGuestFields');
    const arrivalInput = document.getElementById('arrival_date');
    const departureInput = document.getElementById('departure_date');
    let roomAllocationIndex = 0;

    // Format date for display
    function formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { 
            weekday: 'short', 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
    }

    // Calculate number of nights
    function calculateNights(arrival, departure) {
        if (!arrival || !departure) return 0;
        const oneDay = 24 * 60 * 60 * 1000;
        const start = new Date(arrival);
        const end = new Date(departure);
        return Math.round(Math.abs((end - start) / oneDay));
    }

    // Calculate total price
    function calculateTotal() {
        const nights = calculateNights(arrivalInput.value, departureInput.value);
        let total = 0;
        let roomDetails = [];

        // Get all room allocations
        const roomAllocations = document.querySelectorAll('.room-allocation');
        
        roomAllocations.forEach((allocation, index) => {
            const roomSelect = allocation.querySelector('.room-select');
            const roomOption = roomSelect.options[roomSelect.selectedIndex];
            const roomRate = parseFloat(allocation.querySelector('.room-rate').value) || 0;
            const adults = parseInt(allocation.querySelector('input[name="room_allocations[' + index + '][adults]"]').value) || 0;
            const children = parseInt(allocation.querySelector('input[name="room_allocations[' + index + '][children]"]').value) || 0;

            if (roomOption.value && roomRate > 0) {
                const roomTotal = roomRate * nights * adults;
                const childTotal = roomRate * 0.5 * nights * children;
                const allocationTotal = roomTotal + childTotal;
                total += allocationTotal;

                roomDetails.push({
                    room: roomOption.text.split(' - ')[0],
                    rate: roomRate,
                    adults: adults,
                    children: children,
                    total: allocationTotal
                });
            }
        });

        return {
            nights: nights,
            total: total,
            roomDetails: roomDetails,
            roomCount: roomAllocations.length
        };
    }

    // Update booking summary
    function updateBookingSummary() {
        const calculation = calculateTotal();
        
        // Update sidebar
        document.getElementById('nightsCount').textContent = calculation.nights;
        document.getElementById('totalPrice').textContent = '{{ SystemHelper::currencySymbol() }}' + calculation.total.toFixed(2);
        document.getElementById('checkInDate').textContent = formatDate(arrivalInput.value);
        document.getElementById('checkOutDate').textContent = formatDate(departureInput.value);
        document.getElementById('roomCount').textContent = calculation.roomCount;
        document.getElementById('finalTotal').textContent = '{{ SystemHelper::currencySymbol() }}' + calculation.total.toFixed(2);

        // Update price breakdown
        const priceBreakdown = document.getElementById('priceBreakdown');
        if (calculation.nights > 0 && calculation.roomDetails.length > 0) {
            let html = '';
            
            calculation.roomDetails.forEach((room, index) => {
                html += `
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">${room.room}</span>
                        <span class="text-gray-800 dark:text-white">{{ SystemHelper::currencySymbol() }}${room.total.toFixed(2)}</span>
                    </div>
                `;
            });
            
            html += `
                <div class="flex justify-between border-t border-gray-200 pt-2 dark:border-gray-700">
                    <span class="font-medium text-gray-700 dark:text-gray-300">Total</span>
                    <span class="font-bold text-primary">{{ SystemHelper::currencySymbol() }}${calculation.total.toFixed(2)}</span>
                </div>
            `;
            
            priceBreakdown.innerHTML = html;
        } else {
            priceBreakdown.innerHTML = `
                <div class="text-center py-4 text-gray-500 dark:text-gray-400">
                    Select rooms and dates to see pricing
                </div>
            `;
        }
    }

    // Update room rate when room is selected
    function updateRoomRate(selectElement) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const baseRate = parseFloat(selectedOption.dataset.baseRate) || 0;
        const rateInput = selectElement.closest('.room-allocation').querySelector('.room-rate');
        rateInput.value = baseRate;
        updateBookingSummary();
    }

    // Add room allocation
    window.addRoomAllocation = function() {
        const container = document.getElementById('roomAllocations');
        roomAllocationIndex++;
        
        const newAllocation = document.createElement('div');
        newAllocation.className = 'room-allocation border border-gray-200 dark:border-gray-700 rounded-lg p-4 mt-3';
        newAllocation.innerHTML = `
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Room ${roomAllocationIndex + 1}</span>
                <button type="button" onclick="removeRoomAllocation(this)" class="text-danger hover:text-danger/80 text-sm">
                    <i class="fas fa-trash mr-1"></i> Remove
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Room *
                    </label>
                    <select name="room_allocations[${roomAllocationIndex}][room_id]" 
                        class="room-select block w-full rounded-lg border-gray-300 px-4 py-3 text-sm font-medium placeholder:text-gray-400 focus:border-primary focus:ring-primary dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:focus:border-primary"
                        onchange="updateRoomRate(this)" required>
                        <option value="">Select a room...</option>
                        @foreach($rooms as $room)
                        <option value="{{ $room->id }}" 
                            data-type="{{ $room->roomType->name }}"
                            data-price="{{ $room->roomType->price_per_night }}"
                            data-capacity="{{ $room->roomType->max_occupancy }}"
                            data-base-rate="{{ $room->roomType->base_rate ?? $room->roomType->price_per_night }}">
                            Room {{ $room->room_number }} - {{ $room->roomType->name }} ({{ SystemHelper::currencySymbol() }}{{ number_format($room->roomType->price_per_night, 2) }}/night)
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Rate per Night ({{ SystemHelper::currencySymbol() }})
                    </label>
                    <input type="number" name="room_allocations[${roomAllocationIndex}][rate]" 
                        class="room-rate block w-full rounded-lg border-gray-300 px-4 py-3 text-sm font-medium placeholder:text-gray-400 focus:border-primary focus:ring-primary dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:focus:border-primary"
                        step="0.01" min="0" value="0" required>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Adults *
                    </label>
                    <input type="number" name="room_allocations[${roomAllocationIndex}][adults]" 
                        class="block w-full rounded-lg border-gray-300 px-4 py-3 text-sm font-medium placeholder:text-gray-400 focus:border-primary focus:ring-primary dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:focus:border-primary"
                        min="1" max="10" value="1" required>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Children
                    </label>
                    <input type="number" name="room_allocations[${roomAllocationIndex}][children]" 
                        class="block w-full rounded-lg border-gray-300 px-4 py-3 text-sm font-medium placeholder:text-gray-400 focus:border-primary focus:ring-primary dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:focus:border-primary"
                        min="0" max="10" value="0">
                </div>
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Guest Notes (Optional)
                    </label>
                    <input type="text" name="room_allocations[${roomAllocationIndex}][guest_notes]" 
                        class="block w-full rounded-lg border-gray-300 px-4 py-3 text-sm font-medium placeholder:text-gray-400 focus:border-primary focus:ring-primary dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:focus:border-primary"
                        placeholder="e.g., Honeymoon suite, Near elevator, etc.">
                </div>
            </div>
        `;
        
        container.appendChild(newAllocation);
        
        // Show remove button on first allocation if more than one exists
        const firstRemoveBtn = document.querySelector('#removeRoomBtn');
        if (firstRemoveBtn) {
            firstRemoveBtn.classList.remove('hidden');
        }
        
        updateBookingSummary();
    };

    // Remove room allocation
    window.removeRoomAllocation = function(button) {
        const allocation = button.closest('.room-allocation');
        allocation.remove();
        
        // Update indexes
        const allocations = document.querySelectorAll('.room-allocation');
        allocations.forEach((alloc, index) => {
            alloc.querySelector('span:first-child').textContent = `Room ${index + 1}`;
            
            // Update input names
            const inputs = alloc.querySelectorAll('select, input');
            inputs.forEach(input => {
                const name = input.name;
                input.name = name.replace(/room_allocations\[\d+\]/, `room_allocations[${index}]`);
            });
        });
        
        // Hide remove button if only one allocation remains
        if (allocations.length === 1) {
            allocations[0].querySelector('button').classList.add('hidden');
        }
        
        roomAllocationIndex = allocations.length - 1;
        updateBookingSummary();
    };

    // Guest selection handler
    guestSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value === 'new') {
            guestInfo.classList.add('hidden');
            newGuestFields.classList.remove('hidden');
        } else if (selectedOption.value) {
            document.getElementById('guestEmail').textContent = selectedOption.dataset.email || '-';
            document.getElementById('guestPhone').textContent = selectedOption.dataset.phone || '-';
            document.getElementById('guestNationality').textContent = selectedOption.dataset.nationality || '-';
            guestInfo.classList.remove('hidden');
            newGuestFields.classList.add('hidden');
        } else {
            guestInfo.classList.add('hidden');
            newGuestFields.classList.add('hidden');
        }
    });

    // Date change handlers
    arrivalInput.addEventListener('change', function() {
        if (this.value) {
            const nextDay = new Date(this.value);
            nextDay.setDate(nextDay.getDate() + 1);
            departureInput.min = nextDay.toISOString().split('T')[0];
            
            if (departureInput.value && new Date(departureInput.value) <= new Date(this.value)) {
                departureInput.value = '';
            }
        }
        updateBookingSummary();
    });

    departureInput.addEventListener('change', updateBookingSummary);

    // Room allocation change handlers
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('room-rate') || 
            e.target.name?.includes('[adults]') || 
            e.target.name?.includes('[children]')) {
            updateBookingSummary();
        }
    });

    // Form validation
    document.getElementById('stayForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Check dates
        if (!arrivalInput.value || !departureInput.value) {
            alert('Please select both arrival and departure dates.');
            return;
        }

        if (new Date(departureInput.value) <= new Date(arrivalInput.value)) {
            alert('Departure date must be after arrival date.');
            return;
        }

        // Check at least one room is selected with valid rate
        const roomAllocations = document.querySelectorAll('.room-allocation');
        let hasValidRoom = false;
        
        roomAllocations.forEach(allocation => {
            const roomSelect = allocation.querySelector('.room-select');
            const roomRate = allocation.querySelector('.room-rate');
            
            if (roomSelect.value && parseFloat(roomRate.value) > 0) {
                hasValidRoom = true;
            }
        });

        if (!hasValidRoom) {
            alert('Please select at least one room with a valid rate.');
            return;
        }

        // Submit form
        this.submit();
    });

    // Set minimum dates
    const today = new Date().toISOString().split('T')[0];
    arrivalInput.min = today;

    // Initialize room select change handlers
    document.querySelectorAll('.room-select').forEach(select => {
        select.addEventListener('change', function() {
            updateRoomRate(this);
        });
    });
});
</script>
@endsection