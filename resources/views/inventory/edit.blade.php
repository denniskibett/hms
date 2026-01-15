@extends('layouts.app')

@section('content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Edit Inventory Item</h2>
                <p class="text-gray-600 dark:text-gray-400">Update item information - {{ $item->name }}</p>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('inventory.show', $item) }}" class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                    <i class="fas fa-eye mr-1"></i> View Details
                </a>
                <a href="{{ route('inventory.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                    <i class="fas fa-arrow-left mr-1"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6">
        <!-- Form -->
        <div class="col-span-12 lg:col-span-8">
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm">
                <div class="p-6">
                    <form action="{{ route('inventory.update', $item) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <!-- Basic Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Basic Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Item Name *</label>
                                    <input type="text" name="name" required 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('name', $item->name) }}">
                                    @error('name')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">SKU</label>
                                    <input type="text" readonly 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900 bg-gray-50" 
                                           value="{{ $item->sku }}">
                                    <input type="hidden" name="sku" value="{{ $item->sku }}">
                                    <p class="mt-1 text-xs text-gray-500">Stock Keeping Unit (cannot be changed)</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Brand</label>
                                    <input type="text" name="brand" 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('brand', $item->brand) }}">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Model</label>
                                    <input type="text" name="model" 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('model', $item->model) }}">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                                    <textarea name="description" rows="3" 
                                              class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">{{ old('description', $item->description) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Category & Classification -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Category & Classification</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category *</label>
                                    <select name="category_id" required class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id', $item->category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subcategory</label>
                                    <select name="subcategory_id" class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">
                                        <option value="">Select Subcategory</option>
                                        @foreach($subcategories as $subcategory)
                                        <option value="{{ $subcategory->id }}" {{ old('subcategory_id', $item->subcategory_id) == $subcategory->id ? 'selected' : '' }}>
                                            {{ $subcategory->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Item Type</label>
                                    <select name="item_type" class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">
                                        <option value="">Select Type</option>
                                        <option value="consumable" {{ old('item_type', $item->item_type) == 'consumable' ? 'selected' : '' }}>Consumable</option>
                                        <option value="non_consumable" {{ old('item_type', $item->item_type) == 'non_consumable' ? 'selected' : '' }}>Non-Consumable</option>
                                        <option value="raw_material" {{ old('item_type', $item->item_type) == 'raw_material' ? 'selected' : '' }}>Raw Material</option>
                                        <option value="finished_goods" {{ old('item_type', $item->item_type) == 'finished_goods' ? 'selected' : '' }}>Finished Goods</option>
                                        <option value="equipment" {{ old('item_type', $item->item_type) == 'equipment' ? 'selected' : '' }}>Equipment</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Unit of Measure *</label>
                                    <select name="unit_of_measure" required class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">
                                        <option value="">Select Unit</option>
                                        <option value="piece" {{ old('unit_of_measure', $item->unit_of_measure) == 'piece' ? 'selected' : '' }}>Piece</option>
                                        <option value="kg" {{ old('unit_of_measure', $item->unit_of_measure) == 'kg' ? 'selected' : '' }}>Kilogram (kg)</option>
                                        <option value="gram" {{ old('unit_of_measure', $item->unit_of_measure) == 'gram' ? 'selected' : '' }}>Gram (g)</option>
                                        <option value="liter" {{ old('unit_of_measure', $item->unit_of_measure) == 'liter' ? 'selected' : '' }}>Liter (L)</option>
                                        <option value="ml" {{ old('unit_of_measure', $item->unit_of_measure) == 'ml' ? 'selected' : '' }}>Milliliter (ml)</option>
                                        <option value="meter" {{ old('unit_of_measure', $item->unit_of_measure) == 'meter' ? 'selected' : '' }}>Meter (m)</option>
                                        <option value="box" {{ old('unit_of_measure', $item->unit_of_measure) == 'box' ? 'selected' : '' }}>Box</option>
                                        <option value="pack" {{ old('unit_of_measure', $item->unit_of_measure) == 'pack' ? 'selected' : '' }}>Pack</option>
                                        <option value="dozen" {{ old('unit_of_measure', $item->unit_of_measure) == 'dozen' ? 'selected' : '' }}>Dozen</option>
                                    </select>
                                    @error('unit_of_measure')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Pricing Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Pricing Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cost Price *</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500">$</span>
                                        </div>
                                        <input type="number" name="cost_price" step="0.01" required 
                                               class="pl-8 w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                               value="{{ old('cost_price', $item->cost_price) }}">
                                    </div>
                                    @error('cost_price')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Unit Price *</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500">$</span>
                                        </div>
                                        <input type="number" name="unit_price" step="0.01" required 
                                               class="pl-8 w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                               value="{{ old('unit_price', $item->unit_price) }}">
                                    </div>
                                    @error('unit_price')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Retail Price</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500">$</span>
                                        </div>
                                        <input type="number" name="retail_price" step="0.01" 
                                               class="pl-8 w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                               value="{{ old('retail_price', $item->retail_price) }}">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tax Rate (%)</label>
                                    <input type="number" name="tax_rate" step="0.01" 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('tax_rate', $item->tax_rate) }}">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Discount (%)</label>
                                    <input type="number" name="discount" step="0.01" 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('discount', $item->discount) }}">
                                </div>
                            </div>
                        </div>

                        <!-- Stock Management -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Stock Management</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Current Quantity *</label>
                                    <input type="number" name="quantity" required 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('quantity', $item->quantity) }}">
                                    <p class="mt-1 text-xs text-gray-500">Use stock adjustment for changes</p>
                                    @error('quantity')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Minimum Stock Level *</label>
                                    <input type="number" name="min_stock" required 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('min_stock', $item->min_stock) }}">
                                    @error('min_stock')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Maximum Stock Level *</label>
                                    <input type="number" name="max_stock" required 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('max_stock', $item->max_stock) }}">
                                    @error('max_stock')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reorder Quantity</label>
                                    <input type="number" name="reorder_quantity" 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('reorder_quantity', $item->reorder_quantity) }}">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status *</label>
                                    <select name="status" required class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">
                                        <option value="in_stock" {{ old('status', $item->status) == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                                        <option value="low_stock" {{ old('status', $item->status) == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                                        <option value="out_of_stock" {{ old('status', $item->status) == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                                        <option value="discontinued" {{ old('status', $item->status) == 'discontinued' ? 'selected' : '' }}>Discontinued</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Storage & Location -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Storage & Location</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Storage Location *</label>
                                    <select name="location_id" required class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">
                                        <option value="">Select Location</option>
                                        @foreach($locations as $location)
                                        <option value="{{ $location->id }}" {{ old('location_id', $item->location_id) == $location->id ? 'selected' : '' }}>
                                            {{ $location->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('location_id')
                                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Shelf/Bin Number</label>
                                    <input type="text" name="shelf_number" 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('shelf_number', $item->shelf_number) }}">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Rack/Row</label>
                                    <input type="text" name="rack_number" 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('rack_number', $item->rack_number) }}">
                                </div>
                            </div>
                        </div>

                        <!-- Supplier Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Supplier Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Primary Supplier</label>
                                    <select name="supplier_id" class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">
                                        <option value="">Select Supplier</option>
                                        @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ old('supplier_id', $item->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Supplier SKU/Code</label>
                                    <input type="text" name="supplier_code" 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('supplier_code', $item->supplier_code) }}">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Lead Time (Days)</label>
                                    <input type="number" name="lead_time" 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('lead_time', $item->lead_time) }}">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Last Ordered Date</label>
                                    <input type="date" name="last_ordered_at" 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('last_ordered_at', $item->last_ordered_at ? $item->last_ordered_at->format('Y-m-d') : '') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Expiry & Serial Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Expiry & Serial Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Expiry Date</label>
                                    <input type="date" name="expiry_date" 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('expiry_date', $item->expiry_date ? $item->expiry_date->format('Y-m-d') : '') }}">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Batch/Lot Number</label>
                                    <input type="text" name="batch_number" 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('batch_number', $item->batch_number) }}">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Serial Numbers</label>
                                    <textarea name="serial_numbers" rows="2" 
                                              class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">{{ old('serial_numbers', $item->serial_numbers) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Item Image -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Item Image</h3>
                            <div class="flex items-center space-x-6">
                                @if($item->image)
                                <div class="flex-shrink-0">
                                    <img src="{{ asset('storage/' . $item->image) }}" alt="Current Image" 
                                         class="w-20 h-20 rounded-lg object-cover border-2 border-gray-300 dark:border-gray-700">
                                </div>
                                @endif
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Update Image</label>
                                    <input type="file" name="image" accept="image/*" 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">
                                    <p class="mt-1 text-xs text-gray-500">Leave empty to keep current image</p>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Additional Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                                    <textarea name="notes" rows="3" 
                                              class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">{{ old('notes', $item->notes) }}</textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Weight (kg)</label>
                                    <input type="number" name="weight" step="0.01" 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('weight', $item->weight) }}">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dimensions (cm)</label>
                                    <input type="text" name="dimensions" 
                                           class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900" 
                                           value="{{ old('dimensions', $item->dimensions) }}" placeholder="L x W x H">
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-8 flex justify-end space-x-3">
                            <a href="{{ route('inventory.show', $item) }}" class="px-6 py-2 border border-gray-300 dark:border-gray-700 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                                Cancel
                            </a>
                            <button type="submit" class="px-6 py-2 bg-primary text-white rounded hover:bg-primary/90">
                                <i class="fas fa-save mr-1"></i> Update Inventory Item
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-span-12 lg:col-span-4">
            <!-- Item Summary -->
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm p-6 mb-6">
                <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Item Summary</h4>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">SKU:</span>
                        <span class="font-medium text-gray-800 dark:text-white">{{ $item->sku }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Current Status:</span>
                        <span class="px-2 py-1 text-xs rounded-full 
                            @if($item->status === 'in_stock') bg-success/10 text-success 
                            @elseif($item->status === 'low_stock') bg-warning/10 text-warning 
                            @elseif($item->status === 'out_of_stock') bg-danger/10 text-danger 
                            @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 @endif">
                            {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Current Stock:</span>
                        <span class="font-medium text-gray-800 dark:text-white">{{ $item->quantity }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Stock Value:</span>
                        <span class="font-medium text-gray-800 dark:text-white">
                            ${{ number_format($item->quantity * $item->unit_price, 2) }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Created:</span>
                        <span class="text-gray-800 dark:text-white">{{ $item->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Last Updated:</span>
                        <span class="text-gray-800 dark:text-white">{{ $item->updated_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>

            <!-- Stock Alert -->
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm p-6 mb-6">
                <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Stock Level Alert</h4>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Current Stock:</span>
                        <span class="font-medium {{ $item->quantity <= $item->min_stock ? 'text-danger' : ($item->quantity <= ($item->max_stock * 0.3) ? 'text-warning' : 'text-gray-800 dark:text-white') }}">
                            {{ $item->quantity }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Min Stock Level:</span>
                        <span class="font-medium text-gray-800 dark:text-white">{{ $item->min_stock }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Max Stock Level:</span>
                        <span class="font-medium text-gray-800 dark:text-white">{{ $item->max_stock }}</span>
                    </div>
                    <div class="pt-3 border-t border-gray-200 dark:border-gray-700">
                        @if($item->quantity <= $item->min_stock)
                        <div class="flex items-center p-3 bg-danger/10 text-danger rounded">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <span>Stock is below minimum level!</span>
                        </div>
                        @elseif($item->quantity <= ($item->max_stock * 0.3))
                        <div class="flex items-center p-3 bg-warning/10 text-warning rounded">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <span>Stock is getting low</span>
                        </div>
                        @else
                        <div class="flex items-center p-3 bg-success/10 text-success rounded">
                            <i class="fas fa-check-circle mr-2"></i>
                            <span>Stock level is good</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Update Notes -->
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm p-6">
                <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Update Notes</h4>
                <div class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                    <div class="flex items-start">
                        <i class="fas fa-history text-primary mt-1 mr-2"></i>
                        <span>All changes are logged in the system audit trail</span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-circle text-warning mt-1 mr-2"></i>
                        <span>Changing stock levels may trigger alerts</span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-user-check text-success mt-1 mr-2"></i>
                        <span>Your name will be recorded as the editor</span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-sync-alt text-info mt-1 mr-2"></i>
                        <span>Use stock adjustment for quantity changes</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection