@extends('layouts.app')

@section('content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Edit Kitchen Order</h2>
                <p class="text-gray-600 dark:text-gray-400">Update order #{{ $order->order_number }}</p>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('kitchen.show', $order) }}" class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                    <i class="fas fa-eye mr-1"></i> View Order
                </a>
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
                    <form action="{{ route('kitchen.update', $order) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <!-- Order Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Order Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Order Type *</label>
                                    <select name="order_type" required class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">
                                        <option value="">Select Type</option>
                                        <option value="dine_in" {{ old('order_type', $order->order_type) == 'dine_in' ? 'selected' : '' }}>Dine-in</option>
                                        <option value="takeaway" {{ old('order_type', $order->order_type) == 'takeaway' ? 'selected' : '' }}>Takeaway</option>
                                        <option value="delivery" {{ old('order_type', $order->order_type) == 'delivery' ? 'selected' : '' }}>Delivery</option>
                                        <option value="room_service" {{ old('order_type', $order->order_type) == 'room_service' ? 'selected' : '' }}>Room Service</option>
                                    </select>
                                    @error('order_type')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div id="tableNumberField">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Table Number</label>
                                    <input type="number" name="table_number" 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('table_number', $order->table_number) }}" placeholder="e.g., 12" min="1">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Priority *</label>
                                    <select name="priority" required class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">
                                        <option value="normal" {{ old('priority', $order->priority) == 'normal' ? 'selected' : '' }}>Normal</option>
                                        <option value="high" {{ old('priority', $order->priority) == 'high' ? 'selected' : '' }}>High</option>
                                        <option value="urgent" {{ old('priority', $order->priority) == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                    </select>
                                    @error('priority')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estimated Time (minutes) *</label>
                                    <input type="number" name="estimated_time" required 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('estimated_time', $order->estimated_time) }}" min="5" max="120">
                                    @error('estimated_time')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status *</label>
                                    <select name="status" required class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">
                                        <option value="pending" {{ old('status', $order->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="preparing" {{ old('status', $order->status) == 'preparing' ? 'selected' : '' }}>Preparing</option>
                                        <option value="ready" {{ old('status', $order->status) == 'ready' ? 'selected' : '' }}>Ready</option>
                                        <option value="completed" {{ old('status', $order->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="cancelled" {{ old('status', $order->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                    @error('status')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes/Instructions</label>
                                    <textarea name="notes" rows="2" 
                                              class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">{{ old('notes', $order->notes) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Current Order Items -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Current Order Items</h3>
                            
                            <div class="space-y-4">
                                @foreach($order->items as $index => $item)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                    <div class="flex items-start justify-between mb-3">
                                        <div>
                                            <h4 class="font-medium text-gray-800 dark:text-white">{{ $item->menu_item->name }}</h4>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">${{ number_format($item->unit_price, 2) }}</p>
                                        </div>
                                        <div class="flex items-center space-x-3">
                                            <div class="flex items-center space-x-2">
                                                <button type="button" onclick="decrementExistingQuantity({{ $index }})" class="w-6 h-6 flex items-center justify-center border border-gray-300 dark:border-gray-700 rounded">
                                                    <i class="fas fa-minus text-xs"></i>
                                                </button>
                                                <input type="number" id="existing_quantity_{{ $index }}" 
                                                       name="existing_items[{{ $item->id }}][quantity]" 
                                                       class="w-12 text-center border border-gray-300 dark:border-gray-700 rounded px-1 py-1 dark:bg-gray-900" 
                                                       value="{{ old('existing_items.' . $item->id . '.quantity', $item->quantity) }}" 
                                                       min="0" max="20">
                                                <button type="button" onclick="incrementExistingQuantity({{ $index }})" class="w-6 h-6 flex items-center justify-center border border-gray-300 dark:border-gray-700 rounded">
                                                    <i class="fas fa-plus text-xs"></i>
                                                </button>
                                            </div>
                                            <button type="button" onclick="removeItem({{ $index }})" class="text-danger hover:text-danger/80">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <input type="hidden" name="existing_items[{{ $item->id }}][menu_item_id]" value="{{ $item->menu_item_id }}">
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Special Instructions</label>
                                        <textarea name="existing_items[{{ $item->id }}][special_instructions]" 
                                                  class="w-full text-sm border border-gray-300 dark:border-gray-700 rounded px-2 py-1 dark:bg-gray-900">{{ old('existing_items.' . $item->id . '.special_instructions', $item->special_instructions) }}</textarea>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Add New Items -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Add New Menu Items</h3>
                            
                            <!-- Category Filter -->
                            <div class="mb-4">
                                <div class="flex space-x-2 overflow-x-auto pb-2">
                                    <button type="button" onclick="filterNewCategory('all')" class="px-3 py-1 bg-primary text-white rounded text-sm">All</button>
                                    @foreach($categories as $category)
                                    <button type="button" onclick="filterNewCategory('{{ $category->id }}')" class="px-3 py-1 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white rounded text-sm">{{ $category->name }}</button>
                                    @endforeach
                                </div>
                            </div>

                            <!-- New Menu Items Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                                @foreach($menuItems as $item)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 new-menu-item" data-category="{{ $item->category_id }}">
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
                                            <button type="button" onclick="decrementNewQuantity({{ $item->id }})" class="w-8 h-8 flex items-center justify-center border border-gray-300 dark:border-gray-700 rounded">
                                                <i class="fas fa-minus text-sm"></i>
                                            </button>
                                            <input type="number" id="new_quantity_{{ $item->id }}" 
                                                   name="new_items[{{ $item->id }}][quantity]" 
                                                   class="w-16 text-center border border-gray-300 dark:border-gray-700 rounded px-2 py-1 dark:bg-gray-900" 
                                                   value="0" min="0" max="20">
                                            <button type="button" onclick="incrementNewQuantity({{ $item->id }})" class="w-8 h-8 flex items-center justify-center border border-gray-300 dark:border-gray-700 rounded">
                                                <i class="fas fa-plus text-sm"></i>
                                            </button>
                                        </div>
                                        <div>
                                            <input type="hidden" name="new_items[{{ $item->id }}][menu_item_id]" value="{{ $item->id }}">
                                            <button type="button" onclick="addNewSpecialInstructions({{ $item->id }})" class="text-sm text-warning">
                                                <i class="fas fa-edit"></i> Notes
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Special Instructions Modal -->
                                    <div id="new_instructions_{{ $item->id }}" class="hidden mt-3">
                                        <textarea name="new_items[{{ $item->id }}][special_instructions]" 
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
                                    <!-- Summary will be populated by JavaScript -->
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
                                    <div class="flex justify-between text-lg font-bold mt-2 text-primary">
                                        <span>Original Total:</span>
                                        <span>${{ number_format($order->total_amount, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-8 flex justify-end space-x-3">
                            <a href="{{ route('kitchen.show', $order) }}" class="px-6 py-2 border border-gray-300 dark:border-gray-700 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                                Cancel
                            </a>
                            <button type="submit" class="px-6 py-2 bg-primary text-white rounded hover:bg-primary/90">
                                <i class="fas fa-save mr-1"></i> Update Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-span-12 lg:col-span-4">
            <!-- Order Summary -->
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm p-6 mb-6">
                <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Order Information</h4>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Order Number:</span>
                        <span class="font-medium text-gray-800 dark:text-white">#{{ $order->order_number }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Current Status:</span>
                        <span class="px-2 py-1 text-xs rounded-full 
                            @if($order->status === 'pending') bg-primary/10 text-primary 
                            @elseif($order->status === 'preparing') bg-warning/10 text-warning 
                            @elseif($order->status === 'ready') bg-success/10 text-success 
                            @elseif($order->status === 'completed') bg-info/10 text-info 
                            @else bg-danger/10 text-danger @endif">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Order Type:</span>
                        <span class="font-medium text-gray-800 dark:text-white">
                            {{ ucfirst(str_replace('_', ' ', $order->order_type)) }}
                        </span>
                    </div>
                    @if($order->table_number)
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Table Number:</span>
                        <span class="font-medium text-gray-800 dark:text-white">{{ $order->table_number }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Current Items:</span>
                        <span class="font-medium text-gray-800 dark:text-white">{{ $order->items->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Original Total:</span>
                        <span class="font-medium text-gray-800 dark:text-white">${{ number_format($order->total_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Created:</span>
                        <span class="text-gray-800 dark:text-white">{{ $order->created_at->format('M d, Y h:i A') }}</span>
                    </div>
                </div>
            </div>

            <!-- Update Notes -->
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm p-6 mb-6">
                <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Update Notes</h4>
                <div class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                    <div class="flex items-start">
                        <i class="fas fa-history text-primary mt-1 mr-2"></i>
                        <span>All changes are logged in the system audit trail</span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-circle text-warning mt-1 mr-2"></i>
                        <span>Changing status may affect kitchen workflow</span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-user-check text-success mt-1 mr-2"></i>
                        <span>Your name will be recorded as the editor</span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-utensils text-info mt-1 mr-2"></i>
                        <span>Kitchen staff will be notified of changes</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm p-6">
                <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Quick Actions</h4>
                <div class="space-y-3">
                    <button onclick="calculateNewTotal()" class="w-full flex items-center p-3 bg-primary/10 text-primary rounded hover:bg-primary/20">
                        <i class="fas fa-calculator mr-2"></i>
                        <span>Recalculate Total</span>
                    </button>
                    <button onclick="clearNewItems()" class="w-full flex items-center p-3 bg-danger/10 text-danger rounded hover:bg-danger/20">
                        <i class="fas fa-times mr-2"></i>
                        <span>Clear New Items</span>
                    </button>
                    <a href="{{ route('kitchen.duplicate', $order) }}" class="flex items-center p-3 bg-success/10 text-success rounded hover:bg-success/20">
                        <i class="fas fa-copy mr-2"></i>
                        <span>Duplicate Order</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Initialize order summary
document.addEventListener('DOMContentLoaded', function() {
    updateOrderSummary();
    
    // Toggle table number field
    const orderTypeSelect = document.querySelector('select[name="order_type"]');
    const tableField = document.getElementById('tableNumberField');
    
    orderTypeSelect.addEventListener('change', function() {
        if (this.value === 'dine_in' || this.value === 'room_service') {
            tableField.style.display = 'block';
        } else {
            tableField.style.display = 'none';
        }
    });
    
    // Set initial visibility
    if (orderTypeSelect.value === 'dine_in' || orderTypeSelect.value === 'room_service') {
        tableField.style.display = 'block';
    } else {
        tableField.style.display = 'none';
    }
});

// Filter functions for new items
function filterNewCategory(categoryId) {
    const items = document.querySelectorAll('.new-menu-item');
    items.forEach(item => {
        if (categoryId === 'all' || item.dataset.category === categoryId) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

// Quantity control for existing items
function incrementExistingQuantity(index) {
    const input = document.getElementById(`existing_quantity_${index}`);
    const current = parseInt(input.value);
    if (current < 20) {
        input.value = current + 1;
        updateOrderSummary();
    }
}

function decrementExistingQuantity(index) {
    const input = document.getElementById(`existing_quantity_${index}`);
    const current = parseInt(input.value);
    if (current > 0) {
        input.value = current - 1;
        updateOrderSummary();
    }
}

function removeItem(index) {
    const input = document.getElementById(`existing_quantity_${index}`);
    input.value = 0;
    updateOrderSummary();
}

// Quantity control for new items
function incrementNewQuantity(itemId) {
    const input = document.getElementById(`new_quantity_${itemId}`);
    const current = parseInt(input.value);
    if (current < 20) {
        input.value = current + 1;
        updateOrderSummary();
    }
}

function decrementNewQuantity(itemId) {
    const input = document.getElementById(`new_quantity_${itemId}`);
    const current = parseInt(input.value);
    if (current > 0) {
        input.value = current - 1;
        updateOrderSummary();
    }
}

function addNewSpecialInstructions(itemId) {
    const instructionsDiv = document.getElementById(`new_instructions_${itemId}`);
    instructionsDiv.classList.toggle('hidden');
}

// Update order summary
function updateOrderSummary() {
    const summaryDiv = document.getElementById('orderSummary');
    const totalItemsSpan = document.getElementById('totalItems');
    const totalAmountSpan = document.getElementById('totalAmount');
    
    let totalItems = 0;
    let totalAmount = 0;
    let itemsHTML = '';
    
    // Process existing items
    const existingInputs = document.querySelectorAll('input[name^="existing_items"][name$="[quantity]"]');
    existingInputs.forEach(input => {
        const quantity = parseInt(input.value);
        if (quantity > 0) {
            const itemId = input.name.match(/\[(\d+)\]\[quantity\]/)[1];
            totalItems += quantity;
            
            // In a real application, you would fetch the price from a data attribute
            // For now, we'll use a placeholder
            const price = 10; // This should come from a data attribute
            totalAmount += quantity * price;
            
            itemsHTML += `
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-800 dark:text-white">Item #${itemId}</span>
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-600 dark:text-gray-400">x${quantity}</span>
                        <span class="font-medium">$${(quantity * price).toFixed(2)}</span>
                    </div>
                </div>
            `;
        }
    });
    
    // Process new items
    const newInputs = document.querySelectorAll('input[name^="new_items"][name$="[quantity]"]');
    newInputs.forEach(input => {
        const quantity = parseInt(input.value);
        if (quantity > 0) {
            const itemId = input.name.match(/\[(\d+)\]\[quantity\]/)[1];
            totalItems += quantity;
            
            // In a real application, you would fetch the price from a data attribute
            const price = 10; // This should come from a data attribute
            totalAmount += quantity * price;
            
            itemsHTML += `
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-800 dark:text-white">New Item #${itemId}</span>
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-600 dark:text-gray-400">x${quantity}</span>
                        <span class="font-medium text-success">$${(quantity * price).toFixed(2)}</span>
                    </div>
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

// Clear new items
function clearNewItems() {
    if (confirm('Are you sure you want to clear all new items?')) {
        const newInputs = document.querySelectorAll('input[name^="new_items"][name$="[quantity]"]');
        newInputs.forEach(input => {
            input.value = 0;
        });
        
        const instructionTextareas = document.querySelectorAll('textarea[name^="new_items"][name$="[special_instructions]"]');
        instructionTextareas.forEach(textarea => {
            textarea.value = '';
        });
        
        updateOrderSummary();
    }
}

// Calculate new total (placeholder function)
function calculateNewTotal() {
    updateOrderSummary();
    alert('Order total has been recalculated.');
}
</script>
@endpush