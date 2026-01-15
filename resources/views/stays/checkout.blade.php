@extends('layouts.app')

@section('content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Check-out Guest</h2>
                <p class="text-gray-600 dark:text-gray-400">Process guest departure</p>
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
                            <p class="text-gray-600 dark:text-gray-400">Checking out</p>
                        </div>
                        
                        <!-- Status Badge -->
                        <div class="mb-4">
                            <span class="px-4 py-2 text-sm font-medium rounded-full bg-warning/10 text-warning">
                                Checking Out
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
                                <span class="text-gray-800 dark:text-white">{{ $stay->actual_check_in->format('M d, Y h:i A') }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Nights stayed:</span>
                                <span class="text-gray-800 dark:text-white">{{ $stay->calculateNights() }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $stay->calculateNights() }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Nights</p>
                        </div>
                        <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <p class="text-2xl font-bold text-gray-800 dark:text-white">
                                ${{ number_format($stay->total_amount, 2) }}
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Total</p>
                        </div>
                    </div>

                    <!-- Important Notes -->
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-yellow-400"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Check-out Notes</h4>
                                <div class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
                                    <ul class="list-disc pl-5 space-y-1">
                                        <li>Check-out time is 11:00 AM</li>
                                        <li>Late check-out may incur additional charges</li>
                                        <li>All room keys must be returned</li>
                                        <li>Mini-bar charges will be added to final bill</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Check-out Form -->
        <div class="col-span-12 lg:col-span-8">
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm">
                <div class="p-6">
                    <form action="{{ route('stays.process-checkout', $stay) }}" method="POST" id="checkoutForm">
                        @csrf
                        
                        <!-- Current Charges -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Current Charges</h3>
                            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-6">
                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Room Charges ({{ $stay->nights }} nights)</span>
                                        <span class="text-gray-800 dark:text-white">${{ number_format($stay->room_charges, 2) }}</span>
                                    </div>
                                    
                                    @if($stay->additional_charges > 0)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Additional Charges</span>
                                        <span class="text-gray-800 dark:text-white">${{ number_format($stay->additional_charges, 2) }}</span>
                                    </div>
                                    @endif
                                    
                                    <!-- Services Used -->
                                    @if($stay->services && $stay->services->count() > 0)
                                    <div class="pt-3 border-t border-gray-300 dark:border-gray-700">
                                        <h4 class="font-medium text-gray-800 dark:text-white mb-2">Services Used</h4>
                                        @foreach($stay->services as $service)
                                        <div class="flex justify-between text-sm mb-1">
                                            <span class="text-gray-600 dark:text-gray-400">{{ $service->service->name }} ×{{ $service->quantity }}</span>
                                            <span class="text-gray-800 dark:text-white">${{ number_format($service->total_amount, 2) }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                    @endif
                                    
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Tax ({{ $stay->tax_rate }}%)</span>
                                        <span class="text-gray-800 dark:text-white">${{ number_format($stay->tax_amount, 2) }}</span>
                                    </div>
                                    
                                    @if($stay->discount > 0)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Discount ({{ $stay->discount }}%)</span>
                                        <span class="text-success">-${{ number_format($stay->discount_amount, 2) }}</span>
                                    </div>
                                    @endif
                                    
                                    <div class="flex justify-between text-lg font-bold pt-3 border-t border-gray-200 dark:border-gray-700">
                                        <span>Total Amount</span>
                                        <span class="text-primary">${{ number_format($stay->total_amount, 2) }}</span>
                                    </div>
                                    
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Amount Paid</span>
                                        <span class="text-gray-800 dark:text-white">${{ number_format($stay->amount_paid, 2) }}</span>
                                    </div>
                                    
                                    <div class="flex justify-between text-lg font-bold">
                                        <span>Balance Due</span>
                                        <span class="{{ $stay->total_amount - $stay->amount_paid > 0 ? 'text-danger' : 'text-success' }}">
                                            ${{ number_format($stay->total_amount - $stay->amount_paid, 2) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Additional Charges -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Additional Charges</h3>
                            <div class="space-y-4" id="additionalCharges">
                                <!-- Dynamic charges will be added here -->
                            </div>
                            <button type="button" onclick="addAdditionalCharge()" class="mt-3 px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-300 dark:hover:bg-gray-600">
                                <i class="fas fa-plus mr-1"></i> Add Additional Charge
                            </button>
                        </div>
                        
                        <!-- Final Payment -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Final Payment</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Final Amount Due *
                                    </label>
                                    <input type="number" id="finalAmount" name="final_amount" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" 
                                        step="0.01" readonly>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Payment Method *
                                    </label>
                                    <select name="final_payment_method" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" required>
                                        <option value="cash">Cash</option>
                                        <option value="credit_card">Credit Card</option>
                                        <option value="debit_card">Debit Card</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="online_payment">Online Payment</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Amount Paid *
                                    </label>
                                    <input type="number" id="amountPaid" name="amount_paid" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" 
                                        step="0.01" min="0" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Transaction Reference
                                    </label>
                                    <input type="text" name="transaction_reference" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Check-out Details -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Check-out Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Actual Check-out Time *
                                    </label>
                                    <input type="datetime-local" name="actual_check_out" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" 
                                        value="{{ now()->format('Y-m-d\TH:i') }}" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Late Check-out
                                    </label>
                                    <div class="flex items-center space-x-2">
                                        <input type="checkbox" id="late_checkout" name="late_checkout" value="1" class="rounded">
                                        <label for="late_checkout" class="text-sm text-gray-600 dark:text-gray-400">
                                            Guest requested late check-out
                                        </label>
                                    </div>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Guest Feedback
                                    </label>
                                    <textarea name="guest_feedback" rows="3" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" 
                                        placeholder="Any feedback from the guest..."></textarea>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Housekeeping Notes
                                    </label>
                                    <textarea name="housekeeping_notes" rows="2" 
                                        class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" 
                                        placeholder="Any notes for housekeeping..."></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Documents -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Documents</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <input type="checkbox" id="generate_invoice" name="generate_invoice" value="1" class="rounded" checked>
                                        <label for="generate_invoice" class="text-sm text-gray-600 dark:text-gray-400">
                                            Generate final invoice
                                        </label>
                                    </div>
                                </div>
                                <div class="md:col-span-2">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <input type="checkbox" id="generate_receipt" name="generate_receipt" value="1" class="rounded" checked>
                                        <label for="generate_receipt" class="text-sm text-gray-600 dark:text-gray-400">
                                            Generate payment receipt
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <a href="{{ route('stays.show', $stay) }}" class="px-6 py-2 border border-gray-300 dark:border-gray-700 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                                Cancel
                            </a>
                            <button type="submit" class="px-6 py-2 bg-primary text-white rounded hover:bg-primary/90">
                                <i class="fas fa-door-closed mr-1"></i> Process Check-out
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let chargeCounter = 0;

function addAdditionalCharge() {
    chargeCounter++;
    const chargesDiv = document.getElementById('additionalCharges');
    
    const chargeDiv = document.createElement('div');
    chargeDiv.className = 'flex space-x-4 items-start';
    chargeDiv.id = 'charge-' + chargeCounter;
    chargeDiv.innerHTML = `
        <div class="flex-1">
            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Description</label>
            <input type="text" name="additional_charges[${chargeCounter}][description]" 
                class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary" 
                placeholder="e.g., Mini-bar, Laundry, etc.">
        </div>
        <div class="w-32">
            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Amount</label>
            <input type="number" name="additional_charges[${chargeCounter}][amount]" 
                class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary charge-amount" 
                step="0.01" min="0" value="0">
        </div>
        <div class="pt-6">
            <button type="button" onclick="removeCharge(${chargeCounter})" class="text-danger hover:text-danger/80">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    chargesDiv.appendChild(chargeDiv);
    
    // Add event listener to the new amount input
    const amountInput = chargeDiv.querySelector('.charge-amount');
    amountInput.addEventListener('input', updateFinalAmount);
    
    updateFinalAmount();
}

function removeCharge(id) {
    const chargeDiv = document.getElementById('charge-' + id);
    if (chargeDiv) {
        chargeDiv.remove();
        updateFinalAmount();
    }
}

function updateFinalAmount() {
    // Base total from stay
    let total = {{ $stay->total_amount }};
    
    // Add additional charges
    const chargeAmounts = document.querySelectorAll('.charge-amount');
    chargeAmounts.forEach(input => {
        total += parseFloat(input.value) || 0;
    });
    
    // Update final amount field
    const finalAmountInput = document.getElementById('finalAmount');
    finalAmountInput.value = total.toFixed(2);
    
    // Update amount paid placeholder
    const amountPaidInput = document.getElementById('amountPaid');
    amountPaidInput.placeholder = 'Amount to pay (Balance: $' + (total - {{ $stay->amount_paid }}).toFixed(2) + ')';
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize final amount
    updateFinalAmount();
    
    // Add event listener to amount paid field
    document.getElementById('amountPaid').addEventListener('input', function() {
        const finalAmount = parseFloat(document.getElementById('finalAmount').value) || 0;
        const amountPaid = parseFloat(this.value) || 0;
        
        if (amountPaid > finalAmount) {
            this.classList.add('border-danger');
        } else {
            this.classList.remove('border-danger');
        }
    });
    
    // Form validation
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        const finalAmount = parseFloat(document.getElementById('finalAmount').value) || 0;
        const amountPaid = parseFloat(document.getElementById('amountPaid').value) || 0;
        
        if (amountPaid > finalAmount) {
            e.preventDefault();
            alert('Amount paid cannot exceed the final amount due.');
            return;
        }
        
        if (!confirm('Are you sure you want to process check-out? This action cannot be undone.')) {
            e.preventDefault();
        }
    });
});
</script>
@endsection