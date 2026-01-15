@extends('layouts.app')

@section('content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Check-in Guest</h2>
                <p class="text-gray-600 dark:text-gray-400">Process guest arrival</p>
            </div>
            <div>
                <a href="{{ route('stays.show', $stay) }}" class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Stay
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6">
        <!-- Left Column - Stay Summary -->
        <div class="col-span-12 lg:col-span-4">
            <!-- Stay Card -->
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm mb-6">
                <div class="p-6">
                    <!-- Stay Header -->
                    <div class="flex flex-col items-center mb-6">
                        <div class="text-center mb-4">
                            <h3 class="text-xl font-bold text-gray-800 dark:text-white/90">Stay #S{{ str_pad($stay->id, 5, '0', STR_PAD_LEFT) }}</h3>
                            <p class="text-gray-600 dark:text-gray-400">Checking in</p>
                        </div>
                        
                        <!-- Status Badge -->
                        <div class="mb-4">
                            <span class="px-4 py-2 text-sm font-medium rounded-full bg-success/10 text-success">
                                Checking In
                            </span>
                        </div>
                    </div>

                    <!-- Guest Info -->
                    <div class="mb-6">
                        <h4 class="font-medium text-gray-800 dark:text-white mb-3">Guest Information</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Name:</span>
                                <span class="text-gray-800 dark:text-white">{{ $stay->guest->full_name }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Room:</span>
                                <span class="text-gray-800 dark:text-white">Room {{ $stay->room->room_number }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Check-in:</span>
                                <span class="text-gray-800 dark:text-white">{{ $stay->check_in->format('M d, Y') }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Check-out:</span>
                                <span class="text-gray-800 dark:text-white">{{ $stay->check_out->format('M d, Y') }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Nights:</span>
                                <span class="text-gray-800 dark:text-white">{{ $stay->nights }}</span>
                            </div>
                        </div>
                    </div>

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

                    <!-- Room Info -->
                    <div class="mb-6">
                        <h4 class="font-medium text-gray-800 dark:text-white mb-3">Room Information</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Room Type:</span>
                                <span class="text-gray-800 dark:text-white">{{ $stay->room->roomType->name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Floor:</span>
                                <span class="text-gray-800 dark:text-white">{{ $stay->room->floor }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Max Occupancy:</span>
                                <span class="text-gray-800 dark:text-white">{{ $stay->room->roomType->max_occupancy }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Important Notes -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-blue-400"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200">Check-in Notes</h4>
                                <div class="mt-1 text-sm text-blue-700 dark:text-blue-300">
                                    <ul class="list-disc pl-5 space-y-1">
                                        <li>Check-in time is from 2:00 PM</li>
                                        <li>Early check-in subject to availability</li>
                                        <li>Proof of ID required</li>
                                        <li>Credit card pre-authorization may be required</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Check-in Form -->
        <div class="col-span-12 lg:col-span-8">
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm">
                <div class="p-6">
                    <form action="{{ route('stays.process-checkin', $stay) }}" method="POST" id="checkinForm" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Check-in Details -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Check-in Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Actual Check-in Time *
                                    </label>
                                    <input type="datetime-local" name="actual_check_in" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" 
                                        value="{{ now()->format('Y-m-d\TH:i') }}" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Early Check-in
                                    </label>
                                    <div class="flex items-center space-x-2">
                                        <input type="checkbox" id="early_checkin" name="early_checkin" value="1" class="rounded">
                                        <label for="early_checkin" class="text-sm text-gray-600 dark:text-gray-400">
                                            Guest arrived before standard check-in time
                                        </label>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Number of Keys Issued *
                                    </label>
                                    <input type="number" name="keys_issued" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" 
                                        min="1" max="5" value="1" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Deposit Amount
                                    </label>
                                    <input type="number" name="deposit_amount" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" 
                                        step="0.01" min="0" value="0">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Guest Verification -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Guest Verification</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        ID Type *
                                    </label>
                                    <select name="id_type" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" required>
                                        <option value="">Select ID Type</option>
                                        <option value="passport">Passport</option>
                                        <option value="national_id">National ID</option>
                                        <option value="driver_license">Driver's License</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        ID Number *
                                    </label>
                                    <input type="text" name="id_number" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" 
                                        required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        ID Issue Date
                                    </label>
                                    <input type="date" name="id_issue_date" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        ID Expiry Date
                                    </label>
                                    <input type="date" name="id_expiry_date" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Upload ID Copy
                                    </label>
                                    <input type="file" name="id_copy" accept=".pdf,.jpg,.jpeg,.png" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-medium file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Upload a clear copy of the ID (PDF, JPG, or PNG)
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Vehicle Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Vehicle Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Vehicle Make & Model
                                    </label>
                                    <input type="text" name="vehicle_make_model" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        License Plate Number
                                    </label>
                                    <input type="text" name="license_plate" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Parking Spot
                                    </label>
                                    <input type="text" name="parking_spot" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Valet Service
                                    </label>
                                    <div class="flex items-center space-x-2">
                                        <input type="checkbox" name="valet_service" value="1" class="rounded">
                                        <label class="text-sm text-gray-600 dark:text-gray-400">
                                            Request valet parking
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Guest Preferences -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Guest Preferences</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Room Temperature Preference
                                    </label>
                                    <select name="temperature_preference" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary">
                                        <option value="">Select Preference</option>
                                        <option value="cool">Cool</option>
                                        <option value="warm">Warm</option>
                                        <option value="neutral">Neutral</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Pillow Type
                                    </label>
                                    <select name="pillow_type" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary">
                                        <option value="">Select Preference</option>
                                        <option value="soft">Soft</option>
                                        <option value="medium">Medium</option>
                                        <option value="firm">Firm</option>
                                        <option value="memory_foam">Memory Foam</option>
                                    </select>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Additional Preferences
                                    </label>
                                    <textarea name="additional_preferences" rows="2" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" 
                                        placeholder="Any additional preferences or requirements..."></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Hotel Policies Agreement -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Hotel Policies Agreement</h3>
                            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-6">
                                <div class="space-y-4">
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input type="checkbox" id="policy_smoking" name="policy_smoking" value="1" class="rounded" required>
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="policy_smoking" class="font-medium text-gray-700 dark:text-gray-300">
                                                Smoking Policy
                                            </label>
                                            <p class="text-gray-600 dark:text-gray-400">
                                                I acknowledge that smoking is prohibited in all rooms and public areas. A cleaning fee of $250 will be charged for violations.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input type="checkbox" id="policy_damage" name="policy_damage" value="1" class="rounded" required>
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="policy_damage" class="font-medium text-gray-700 dark:text-gray-300">
                                                Damage Policy
                                            </label>
                                            <p class="text-gray-600 dark:text-gray-400">
                                                I accept responsibility for any damage to hotel property during my stay. Charges will be applied accordingly.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input type="checkbox" id="policy_checkout" name="policy_checkout" value="1" class="rounded" required>
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="policy_checkout" class="font-medium text-gray-700 dark:text-gray-300">
                                                Check-out Policy
                                            </label>
                                            <p class="text-gray-600 dark:text-gray-400">
                                                I understand that check-out time is 11:00 AM. Late check-out may incur additional charges.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input type="checkbox" id="policy_minibar" name="policy_minibar" value="1" class="rounded" required>
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="policy_minibar" class="font-medium text-gray-700 dark:text-gray-300">
                                                Mini-bar Policy
                                            </label>
                                            <p class="text-gray-600 dark:text-gray-400">
                                                I acknowledge that mini-bar consumption will be charged to my room. A daily restocking fee may apply.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input type="checkbox" id="policy_privacy" name="policy_privacy" value="1" class="rounded" required>
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="policy_privacy" class="font-medium text-gray-700 dark:text-gray-300">
                                                Privacy Policy
                                            </label>
                                            <p class="text-gray-600 dark:text-gray-400">
                                                I agree to the hotel's privacy policy regarding the handling of my personal information.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Guest Signature -->
                                <div class="mt-6 pt-6 border-t border-gray-300 dark:border-gray-700">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Guest Signature *
                                    </label>
                                    <div class="border border-gray-300 dark:border-gray-700 rounded h-32 bg-white dark:bg-gray-900 flex items-center justify-center">
                                        <canvas id="signatureCanvas" class="border-0 w-full h-full cursor-crosshair"></canvas>
                                    </div>
                                    <input type="hidden" name="guest_signature" id="guestSignature">
                                    <div class="flex justify-between mt-2">
                                        <button type="button" onclick="clearSignature()" class="text-sm text-danger hover:text-danger/80">
                                            <i class="fas fa-eraser mr-1"></i> Clear Signature
                                        </button>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            Sign above with your mouse or finger
                                        </p>
                                    </div>
                                </div>
                                
                                <!-- Upload Check-in Form -->
                                <div class="mt-6">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Upload Check-in Form (Optional)
                                    </label>
                                    <input type="file" name="checkin_form" accept=".pdf,.jpg,.jpeg,.png" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-medium file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <a href="{{ route('stays.show', $stay) }}" class="px-6 py-2 border border-gray-300 dark:border-gray-700 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                                Cancel
                            </a>
                            <button type="submit" class="px-6 py-2 bg-primary text-white rounded hover:bg-primary/90">
                                <i class="fas fa-door-open mr-1"></i> Complete Check-in
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/signature_pad/1.5.3/signature_pad.min.js"></script>
<script>
let signaturePad;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize signature pad
    const canvas = document.getElementById('signatureCanvas');
    signaturePad = new SignaturePad(canvas);
    
    // Set canvas background color
    canvas.style.backgroundColor = getComputedStyle(document.documentElement).getPropertyValue('--bg-color') || 'white';
    
    // Handle form submission
    document.getElementById('checkinForm').addEventListener('submit', function(e) {
        // Check if signature is empty
        if (signaturePad.isEmpty()) {
            e.preventDefault();
            alert('Please provide your signature.');
            return;
        }
        
        // Convert signature to data URL and save to hidden input
        const signatureData = signaturePad.toDataURL();
        document.getElementById('guestSignature').value = signatureData;
        
        // Check all policy agreements
        const policies = ['policy_smoking', 'policy_damage', 'policy_checkout', 'policy_minibar', 'policy_privacy'];
        for (const policy of policies) {
            if (!document.getElementById(policy).checked) {
                e.preventDefault();
                alert('You must agree to all hotel policies.');
                return;
            }
        }
        
        // Confirm check-in
        if (!confirm('Are you sure you want to complete the check-in?')) {
            e.preventDefault();
        }
    });
});

function clearSignature() {
    signaturePad.clear();
}
</script>
@endsection