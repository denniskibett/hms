@extends('layouts.app')

@section('content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Edit Stay</h2>
                <p class="text-gray-600 dark:text-gray-400">Update stay information for Stay #S{{ str_pad($stay->id, 5, '0', STR_PAD_LEFT) }}</p>
            </div>
            <div>
                <a href="{{ route('stays.show', $stay) }}" class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Stay
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6">
        <!-- Main Form -->
        <div class="col-span-12 lg:col-span-8">
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm">
                <div class="p-6">
                    <form action="{{ route('stays.update', $stay) }}" method="POST" id="stayForm">
                        @csrf
                        @method('PUT')
                        
                        <!-- Stay Information -->
                        <div class="mb-8">
                            <div class="flex items-center mb-4">
                                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-primary text-white mr-3">
                                    <i class="fas fa-calendar"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Stay Information</h3>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Guest Information (Read-only) -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Guest
                                    </label>
                                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="font-medium text-gray-800 dark:text-white">{{ $stay->guest->full_name }}</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $stay->guest->email }}</p>
                                            </div>
                                            <a href="{{ route('guests.show', $stay->guest) }}" class="text-primary hover:text-primary/80 text-sm">
                                                View Profile
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Room Information (Read-only if checked in) -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Room
                                    </label>
                                    @if($stay->is_checked_in)
                                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="font-medium text-gray-800 dark:text-white">Room {{ $stay->room->room_number }}</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $stay->room->roomType->name }}</p>
                                            </div>
                                            <span class="text-sm text-gray-500">
                                                Cannot change after check-in
                                            </span>
                                        </div>
                                    </div>
                                    <input type="hidden" name="room_id" value="{{ $stay->room_id }}">
                                    @else
                                    <select name="room_id" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary">
                                        @foreach($rooms as $room)
                                        <option value="{{ $room->id }}" 
                                            {{ $stay->room_id == $room->id ? 'selected' : '' }}
                                            data-price="{{ $room->roomType->price_per_night }}"
                                            data-occupancy="{{ $room->roomType->max_occupancy }}">
                                            Room {{ $room->room_number }} - {{ $room->roomType->name }} (${{ number_format($room->roomType->price_per_night, 2) }}/night)
                                        </option>
                                        @endforeach
                                    </select>
                                    @endif
                                </div>
                                
                                <!-- Dates -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Check-in Date *
                                    </label>
                                    <input type="date" name="check_in" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" 
                                        value="{{ $stay->check_in->format('Y-m-d') }}" 
                                        min="{{ date('Y-m-d') }}" {{ $stay->is_checked_in ? 'readonly' : 'required' }}>
                                    @if($stay->is_checked_in)
                                    <p class="mt-1 text-xs text-gray-500">Actual check-in: {{ $stay->actual_check_in->format('M d, Y h:i A') }}</p>
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Check-out Date *
                                    </label>
                                    <input type="date" name="check_out" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" 
                                        value="{{ $stay->check_out->format('Y-m-d') }}" 
                                        min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                                    @if($stay->is_checked_out)
                                    <p class="mt-1 text-xs text-gray-500">Actual check-out: {{ $stay->actual_check_out->format('M d, Y h:i A') }}</p>
                                    @endif
                                </div>
                                
                                <!-- Number of Guests -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Number of Guests *
                                    </label>
                                    <input type="number" name="number_of_guests" id="number_of_guests" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" 
                                        min="1" max="10" value="{{ $stay->number_of_guests }}" required>
                                    <p id="occupancyWarning" class="mt-1 text-sm text-danger hidden">
                                        Exceeds maximum occupancy for this room
                                    </p>
                                </div>
                                
                                <!-- Booking Source -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Booking Source *
                                    </label>
                                    <select name="booking_source" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" required>
                                        <option value="direct" {{ $stay->booking_source == 'direct' ? 'selected' : '' }}>Direct Booking</option>
                                        <option value="website" {{ $stay->booking_source == 'website' ? 'selected' : '' }}>Website</option>
                                        <option value="travel_agent" {{ $stay->booking_source == 'travel_agent' ? 'selected' : '' }}>Travel Agent</option>
                                        <option value="online_travel_agency" {{ $stay->booking_source == 'online_travel_agency' ? 'selected' : '' }}>Online Travel Agency</option>
                                        <option value="corporate" {{ $stay->booking_source == 'corporate' ? 'selected' : '' }}>Corporate</option>
                                        <option value="group" {{ $stay->booking_source == 'group' ? 'selected' : '' }}>Group</option>
                                        <option value="other" {{ $stay->booking_source == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>
                                
                                <!-- Status -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Status *
                                    </label>
                                    <select name="status" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" required>
                                        <option value="upcoming" {{ $stay->status == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                                        <option value="active" {{ $stay->status == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="completed" {{ $stay->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="cancelled" {{ $stay->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </div>
                                
                                <!-- Special Requests -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Special Requests
                                    </label>
                                    <textarea name="special_requests" rows="3" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary">{{ $stay->special_requests }}</textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Billing Information -->
                        <div class="mb-8">
                            <div class="flex items-center mb-4">
                                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-primary text-white mr-3">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Billing Information</h3>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Payment Method -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Payment Method *
                                    </label>
                                    <select name="payment_method" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" required>
                                        <option value="cash" {{ $stay->payment_method == 'cash' ? 'selected' : '' }}>Cash</option>
                                        <option value="credit_card" {{ $stay->payment_method == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                                        <option value="debit_card" {{ $stay->payment_method == 'debit_card' ? 'selected' : '' }}>Debit Card</option>
                                        <option value="bank_transfer" {{ $stay->payment_method == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                        <option value="online_payment" {{ $stay->payment_method == 'online_payment' ? 'selected' : '' }}>Online Payment</option>
                                        <option value="other" {{ $stay->payment_method == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>
                                
                                <!-- Payment Status -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Payment Status *
                                    </label>
                                    <select name="payment_status" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" required>
                                        <option value="pending" {{ $stay->payment_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="partial" {{ $stay->payment_status == 'partial' ? 'selected' : '' }}>Partial</option>
                                        <option value="paid" {{ $stay->payment_status == 'paid' ? 'selected' : '' }}>Paid</option>
                                    </select>
                                </div>
                                
                                <!-- Advanced Billing Options -->
                                <div class="md:col-span-2">
                                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Advanced Billing Options</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">
                                                    Room Charges ($)
                                                </label>
                                                <input type="number" name="room_charges" 
                                                    class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" 
                                                    step="0.01" min="0" value="{{ $stay->room_charges }}">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">
                                                    Additional Charges ($)
                                                </label>
                                                <input type="number" name="additional_charges" 
                                                    class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" 
                                                    step="0.01" min="0" value="{{ $stay->additional_charges }}">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">
                                                    Amount Paid ($)
                                                </label>
                                                <input type="number" name="amount_paid" 
                                                    class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" 
                                                    step="0.01" min="0" value="{{ $stay->amount_paid }}">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">
                                                    Discount (%)
                                                </label>
                                                <input type="number" name="discount" 
                                                    class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" 
                                                    min="0" max="100" value="{{ $stay->discount }}" step="0.01">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">
                                                    Tax Rate (%)
                                                </label>
                                                <input type="number" name="tax_rate" 
                                                    class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" 
                                                    min="0" max="50" value="{{ $stay->tax_rate }}" step="0.01">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Additional Information -->
                        <div class="mb-8">
                            <div class="flex items-center mb-4">
                                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-primary text-white mr-3">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Additional Information</h3>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Check-in Information -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Actual Check-in Time
                                    </label>
                                    @if($stay->actual_check_in)
                                    <input type="datetime-local" name="actual_check_in" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" 
                                        value="{{ $stay->actual_check_in->format('Y-m-d\TH:i') }}">
                                    @else
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Not checked in yet</p>
                                    @endif
                                </div>
                                
                                <!-- Check-out Information -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Actual Check-out Time
                                    </label>
                                    @if($stay->actual_check_out)
                                    <input type="datetime-local" name="actual_check_out" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" 
                                        value="{{ $stay->actual_check_out->format('Y-m-d\TH:i') }}">
                                    @else
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Not checked out yet</p>
                                    @endif
                                </div>
                                
                                <!-- Notes -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Internal Notes
                                    </label>
                                    <textarea name="internal_notes" rows="3" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary">{{ $stay->internal_notes }}</textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <a href="{{ route('stays.show', $stay) }}" class="px-6 py-2 border border-gray-300 dark:border-gray-700 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                                Cancel
                            </a>
                            <button type="submit" class="px-6 py-2 bg-primary text-white rounded hover:bg-primary/90">
                                <i class="fas fa-save mr-1"></i> Update Stay
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Sidebar - Current Information -->
        <div class="col-span-12 lg:col-span-4">
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm sticky top-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Current Information</h3>
                    
                    <!-- Quick Stats -->
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $stay->nights }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Nights</p>
                        </div>
                        <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <p class="text-2xl font-bold text-gray-800 dark:text-white">
                                ${{ number_format($stay->total_amount, 2) }}
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Total</p>
                        </div>
                    </div>
                    
                    <!-- Current Status -->
                    <div class="mb-6">
                        <h4 class="font-medium text-gray-800 dark:text-white mb-3">Current Status</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Status:</span>
                                <span class="font-medium text-gray-800 dark:text-white">{{ ucfirst($stay->status) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Payment Status:</span>
                                <span class="font-medium text-gray-800 dark:text-white">{{ ucfirst($stay->payment_status) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Check-in:</span>
                                <span class="font-medium text-gray-800 dark:text-white">
                                    @if($stay->is_checked_in)
                                    <span class="text-success">Completed</span>
                                    @else
                                    <span class="text-warning">Pending</span>
                                    @endif
                                </span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Check-out:</span>
                                <span class="font-medium text-gray-800 dark:text-white">
                                    @if($stay->is_checked_out)
                                    <span class="text-success">Completed</span>
                                    @else
                                    <span class="text-warning">Pending</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Current Pricing -->
                    <div class="mb-6">
                        <h4 class="font-medium text-gray-800 dark:text-white mb-3">Current Pricing</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Room Charges:</span>
                                <span class="text-gray-800 dark:text-white">${{ number_format($stay->room_charges, 2) }}</span>
                            </div>
                            @if($stay->additional_charges > 0)
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Additional Charges:</span>
                                <span class="text-gray-800 dark:text-white">${{ number_format($stay->additional_charges, 2) }}</span>
                            </div>
                            @endif
                            @if($stay->discount > 0)
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Discount:</span>
                                <span class="text-success">-${{ number_format($stay->discount_amount, 2) }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Tax:</span>
                                <span class="text-gray-800 dark:text-white">${{ number_format($stay->tax_amount, 2) }}</span>
                            </div>
                            <div class="flex justify-between font-medium pt-2 border-t border-gray-300 dark:border-gray-700">
                                <span>Total:</span>
                                <span class="text-primary">${{ number_format($stay->total_amount, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Paid:</span>
                                <span class="text-gray-800 dark:text-white">${{ number_format($stay->amount_paid, 2) }}</span>
                            </div>
                            <div class="flex justify-between font-medium">
                                <span>Balance:</span>
                                <span class="{{ $stay->total_amount - $stay->amount_paid > 0 ? 'text-danger' : 'text-success' }}">
                                    ${{ number_format($stay->total_amount - $stay->amount_paid, 2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Important Notes -->
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-yellow-400"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Important Notes</h4>
                                <div class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
                                    <ul class="list-disc pl-5 space-y-1">
                                        <li>Changing dates may affect pricing</li>
                                        <li>Room cannot be changed after check-in</li>
                                        <li>Payment status updates may require manual review</li>
                                        <li>Check-in/check-out times are for reference only</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roomSelect = document.querySelector('select[name="room_id"]');
    const numberOfGuestsInput = document.getElementById('number_of_guests');
    const occupancyWarning = document.getElementById('occupancyWarning');
    
    function checkOccupancy() {
        if (!roomSelect) return;
        
        const selectedOption = roomSelect.options[roomSelect.selectedIndex];
        const maxOccupancy = parseInt(selectedOption?.dataset.occupancy) || 0;
        const numberOfGuests = parseInt(numberOfGuestsInput.value) || 0;
        
        if (maxOccupancy > 0 && numberOfGuests > maxOccupancy) {
            occupancyWarning.classList.remove('hidden');
            numberOfGuestsInput.classList.add('border-danger');
        } else {
            occupancyWarning.classList.add('hidden');
            numberOfGuestsInput.classList.remove('border-danger');
        }
    }
    
    if (roomSelect) {
        roomSelect.addEventListener('change', checkOccupancy);
    }
    
    numberOfGuestsInput.addEventListener('input', checkOccupancy);
    
    // Initial check
    checkOccupancy();
    
    // Form validation
    document.getElementById('stayForm').addEventListener('submit', function(e) {
        const checkInDate = new Date(document.querySelector('input[name="check_in"]').value);
        const checkOutDate = new Date(document.querySelector('input[name="check_out"]').value);
        
        if (checkOutDate <= checkInDate) {
            e.preventDefault();
            alert('Check-out date must be after check-in date.');
            return;
        }
        
        if (!confirm('Are you sure you want to update this stay?')) {
            e.preventDefault();
        }
    });
});
</script>
@endsection