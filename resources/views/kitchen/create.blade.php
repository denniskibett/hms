@extends('layouts.app')

@section('content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">New Kitchen Order</h2>
                <p class="text-gray-600 dark:text-gray-400">Create a new kitchen order</p>
            </div>
            <div>
                <a href="{{ route('kitchen.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Orders
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6">
        <!-- Form -->
        <div class="col-span-12 lg:col-span-8">
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm">
                <div class="p-6">
                    <form action="{{ route('kitchen.store') }}" method="POST">
                        @csrf
                        
                        <!-- Order Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Order Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Order Type *</label>
                                    <select name="order_type" required class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">
                                        <option value="">Select Type</option>
                                        <option value="dine_in" {{ old('order_type') == 'dine_in' ? 'selected' : '' }}>Dine-in</option>
                                        <option value="takeaway" {{ old('order_type') == 'takeaway' ? 'selected' : '' }}>Takeaway</option>
                                        <option value="delivery" {{ old('order_type') == 'delivery' ? 'selected' : '' }}>Delivery</option>
                                        <option value="room_service" {{ old('order_type') == 'room_service' ? 'selected' : '' }}>Room Service</option>
                                    </select>
                                    @error('order_type')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div id="tableNumberField">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Table Number</label>
                                    <input type="number" name="table_number" 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('table_number') }}" placeholder="e.g., 12" min="1">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Priority *</label>
                                    <select name="priority" required class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">
                                        <option value="normal" {{ old('priority') == 'normal' ? 'selected' : '' }}>Normal</option>
                                        <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                        <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                    </select>
                                    @error('priority')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estimated Time (minutes) *</label>
                                    <input type="number" name="estimated_time" required 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('estimated_time', 20) }}" min="5" max="120">
                                    @error('estimated_time')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes/Instructions</label>
                                    <textarea name="notes" rows="2" 
                                              class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">{{ old('notes') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Menu Items Selection -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Select Menu Items</h3>
                            
                            <!-- Category Filter -->
                            <div class="mb-4">
                                <div class="flex space-x-2 overflow-x-auto pb-2">
                                    <button type="button" onclick="filterCategory('all')" class="px-3 py-1 bg-primary text-white rounded text-sm">All</button>
                                    @foreach($categories as $category)
                                    <button type="button" onclick="filterCategory('{{ $category->id }}')" class="px-3 py-1 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white rounded text-sm">{{ $category->name }}</button>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Menu Items Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                                @foreach($menuItems as $item)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 menu-item" data-category="{{ $item->category_id }}">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <h4 class="font-medium text-gray-800 dark:text-white">{{ $item->name }}</h4>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">${{ number_format($item->price, 2) }}</p>
                                        </div>
                                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            {{ $item->category->name }}
                                        </span>
                                    </div>
                                    
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">{{ Str::limit($item->description, 60) }}</p>
                                    
                                    @if($item->is_available)
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-2">
                                            <button type="button" onclick="decrementQuantity({{ $item->id }})" class="w-8 h-8 flex items-center justify-center border border-gray-300 dark:border-gray-700 rounded">
                                                <i class="fas fa-minus text-sm"></i>
                                            </button>
                                            <input type="number" id="quantity_{{ $item->id }}" 
                                                   name="items[{{ $item->id }}][quantity]" 
                                                   class="w-16 text-center border border-gray-300 dark:border-gray-700 rounded px-2 py-1 dark:bg-gray-900" 
                                                   value="0" min="0" max="20" readonly>
                                            <button type="button" onclick="incrementQuantity({{ $item->id }})" class="w-8 h-8 flex items-center justify-center border border-gray-300 dark:border-gray-700 rounded">
                                                <i class="fas fa-plus text-sm"></i>
                                            </button>
                                        </div>
                                        <div>
                                            <input type="hidden" name="items[{{ $item->id }}][menu_item_id]" value="{{ $item->id }}">
                                            <button type="button" onclick="addSpecialInstructions({{ $item->id }})" class="text-sm text-warning">
                                                <i class="fas fa-edit"></i> Notes
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Special Instructions Modal (hidden by default) -->
                                    <div id="instructions_{{ $item->id }}" class="hidden mt-3">
                                        <textarea name="items[{{ $item->id }}][special_instructions]" 
                                                  placeholder="Special instructions..." 
                                                  class="w-full text-sm border border-gray-300 dark:border-gray-700 rounded px-2 py-1 dark:bg-gray-900"></textarea>
                                    </div>
                                    @else
                                    <div class="text-center py-2 text-danger text-sm">
                                        <i class="fas fa-times-circle mr-1"></i> Not Available
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Order Summary -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Order Summary</h3>
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6">
                                <div id="orderSummary" class="space-y-3">
                                    <div class="text-center text-gray-500 dark:text-gray-400">
                                        <i class="fas fa-shopping-cart text-2xl mb-2"></i>
                                        <p>No items selected</p>
                                    </div>
                                </div>
                                
                                <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                                    <div class="flex justify-between text-lg font-bold">
                                        <span>Total Items:</span>
                                        <span id="totalItems">0</span>
                                    </div>
                                    <div class="flex justify-between text-lg font-bold mt-2">
                                        <span>Total Amount:</span>
                                        <span id="totalAmount">$0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-8 flex justify-end space-x-3">
                            <button type="button" onclick="clearOrder()" class="px-6 py-2 border border-gray-300 dark:border-gray-700 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                                Clear Order
                            </button>
                            <button type="submit" class="px-6 py-2 bg-primary text-white rounded hover:bg-primary/90">
                                <i class="fas fa-paper-plane mr-1"></i> Place Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-span-12 lg:col-span-4">
            <!-- Order Guidelines -->
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm p-6 mb-6">
                <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Order Guidelines</h4>
                <ul class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                    <li class="flex items-start">
                        <i class="fas fa-asterisk text-primary mt-1 mr-2 text-xs"></i>
                        <span>Fields marked with * are mandatory</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-clock text-warning mt-1 mr-2"></i>
                        <span>Set realistic estimated preparation times</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-danger mt-1 mr-2"></i>
                        <span>Use high priority only for urgent orders</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-utensils text-success mt-1 mr-2"></i>
                        <span>Add special instructions for dietary requirements</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-info mt-1 mr-2"></i>
                        <span>Review order summary before placing</span>
                    </li>
                </ul>
            </div>

            <!-- Quick Menu Items -->
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm p-6 mb-6">
                <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Popular Items</h4>
                <div class="space-y-3">
                    @foreach($popularItems as $item)
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded">
                        <div>
                            <p class="font-medium text-gray-800 dark:text-white">{{ $item->name }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">${{ number_format($item->price, 2) }}</p>
                        </div>
                        <button type="button" onclick="addPopularItem({{ $item->id }})" class="text-sm bg-primary text-white px-3 py-1 rounded hover:bg-primary/90">
                            Add
                        </button>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Current Time & Info -->
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm p-6">
                <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Order Information</h4>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Current Time:</span>
                        <span class="font-medium text-gray-800 dark:text-white" id="currentTime">{{ now()->format('h:i A') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Order Number:</span>
                        <span class="font-medium text-gray-800 dark:text-white">#{{ $nextOrderNumber }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Staff:</span>
                        <span class="font-medium text-gray-800 dark:text-white">{{ auth()->user()->name }}</span>
                    </div>
                    <div class="pt-3 border-t border-gray-200 dark:border-gray-700">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            <i class="fas fa-info-circle mr-1"></i>
                            Orders are automatically assigned to the kitchen display system
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Filter menu items by category
function filterCategory(categoryId) {
    const items = document.querySelectorAll('.menu-item');
    items.forEach(item => {
        if (categoryId === 'all' || item.dataset.category === categoryId) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

// Quantity control functions
function incrementQuantity(itemId) {
    const input = document.getElementById(`quantity_${itemId}`);
    const current = parseInt(input.value);
    if (current < 20) {
        input.value = current + 1;
        updateOrderSummary();
    }
}

function decrementQuantity(itemId) {
    const input = document.getElementById(`quantity_${itemId}`);
    const current = parseInt(input.value);
    if (current > 0) {
        input.value = current - 1;
        updateOrderSummary();
    }
}

// Add special instructions
function addSpecialInstructions(itemId) {
    const instructionsDiv = document.getElementById(`instructions_${itemId}`);
    instructionsDiv.classList.toggle('hidden');
}

// Add popular item
function addPopularItem(itemId) {
    const input = document.getElementById(`quantity_${itemId}`);
    if (input) {
        const current = parseInt(input.value);
        if (current < 20) {
            input.value = current + 1;
            updateOrderSummary();
        }
    }
}

// Update order summary
function updateOrderSummary() {
    const summaryDiv = document.getElementById('orderSummary');
    const totalItemsSpan = document.getElementById('totalItems');
    const totalAmountSpan = document.getElementById('totalAmount');
    
    let totalItems = 0;
    let totalAmount = 0;
    let itemsHTML = '';
    
    // Get all quantity inputs
    const quantityInputs = document.querySelectorAll('input[id^="quantity_"]');
    
    quantityInputs.forEach(input => {
        const quantity = parseInt(input.value);
        if (quantity > 0) {
            const itemId = input.id.replace('quantity_', '');
            const itemName = document.querySelector(`[data-item="${itemId}"]`)?.textContent || `Item ${itemId}`;
            const itemPrice = parseFloat(document.querySelector(`[data-price="${itemId}"]`)?.textContent || 0);
            
            totalItems += quantity;
            totalAmount += quantity * itemPrice;
            
            itemsHTML += `
                <div class="flex justify-between items-center">
                    <div>
                        <span class="font-medium text-gray-800 dark:text-white">${itemName}</span>
                        <span class="text-sm text-gray-600 dark:text-gray-400 ml-2">x${quantity}</span>
                    </div>
                    <span class="font-medium text-gray-800 dark:text-white">$${(quantity * itemPrice).toFixed(2)}</span>
                </div>
            `;
        }
    });
    
    if (totalItems > 0) {
        summaryDiv.innerHTML = itemsHTML;
    } else {
        summaryDiv.innerHTML = `
            <div class="text-center text-gray-500 dark:text-gray-400">
                <i class="fas fa-shopping-cart text-2xl mb-2"></i>
                <p>No items selected</p>
            </div>
        `;
    }
    
    totalItemsSpan.textContent = totalItems;
    totalAmountSpan.textContent = `$${totalAmount.toFixed(2)}`;
}

// Clear order
function clearOrder() {
    if (confirm('Are you sure you want to clear the entire order?')) {
        const quantityInputs = document.querySelectorAll('input[id^="quantity_"]');
        quantityInputs.forEach(input => {
            input.value = 0;
        });
        
        const instructionTextareas = document.querySelectorAll('textarea[name$="[special_instructions]"]');
        instructionTextareas.forEach(textarea => {
            textarea.value = '';
        });
        
        updateOrderSummary();
    }
}

// Toggle table number field based on order type
document.querySelector('select[name="order_type"]').addEventListener('change', function() {
    const tableField = document.getElementById('tableNumberField');
    if (this.value === 'dine_in' || this.value === 'room_service') {
        tableField.style.display = 'block';
    } else {
        tableField.style.display = 'none';
    }
});

// Initialize table field visibility
document.addEventListener('DOMContentLoaded', function() {
    const orderType = document.querySelector('select[name="order_type"]').value;
    const tableField = document.getElementById('tableNumberField');
    if (orderType === 'dine_in' || orderType === 'room_service') {
        tableField.style.display = 'block';
    } else {
        tableField.style.display = 'none';
    }
    
    // Update current time every minute
    function updateCurrentTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        document.getElementById('currentTime').textContent = timeString;
    }
    
    updateCurrentTime();
    setInterval(updateCurrentTime, 60000);
});
</script>
@endpush