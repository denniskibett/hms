@extends('layouts.app')

@section('content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Inventory Item Details</h2>
                <p class="text-gray-600 dark:text-gray-400">View item information and stock details</p>
            </div>
            <div class="flex items-center space-x-2">
                @can('update', $item)
                <a href="{{ route('inventory.edit', $item) }}" class="px-4 py-2 bg-warning text-white rounded hover:bg-warning/90">
                    <i class="fas fa-edit mr-1"></i> Edit Item
                </a>
                @endcan
                <a href="{{ route('inventory.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                    <i class="fas fa-arrow-left mr-1"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6">
        <!-- Left Column - Item Details -->
        <div class="col-span-12 lg:col-span-4">
            <!-- Item Card -->
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm mb-6">
                <div class="p-6">
                    <!-- Item Header -->
                    <div class="flex flex-col items-center mb-6">
                        @if($item->image)
                        <div class="mb-4">
                            <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" 
                                 class="w-32 h-32 rounded-lg object-cover mx-auto">
                        </div>
                        @else
                        <div class="w-32 h-32 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-4">
                            <i class="fas fa-box text-gray-400 text-4xl"></i>
                        </div>
                        @endif
                        
                        <h3 class="text-xl font-bold text-gray-800 dark:text-white/90 text-center">{{ $item->name }}</h3>
                        <p class="text-gray-600 dark:text-gray-400">{{ $item->sku }}</p>
                        
                        <!-- Status Badge -->
                        <div class="mt-2">
                            <span class="px-3 py-1 text-xs font-medium rounded-full 
                                @if($item->status === 'in_stock') bg-success/10 text-success 
                                @elseif($item->status === 'low_stock') bg-warning/10 text-warning 
                                @elseif($item->status === 'out_of_stock') bg-danger/10 text-danger 
                                @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 @endif">
                                {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                            </span>
                            @if($item->expiry_date && $item->expiry_date->isPast())
                            <span class="ml-1 px-3 py-1 text-xs font-medium rounded-full bg-danger/10 text-danger">
                                Expired
                            </span>
                            @elseif($item->expiry_date && $item->expiry_date->diffInDays(now()) <= 30)
                            <span class="ml-1 px-3 py-1 text-xs font-medium rounded-full bg-warning/10 text-warning">
                                Expiring Soon
                            </span>
                            @endif
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $item->quantity }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Current Stock</p>
                        </div>
                        <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <p class="text-2xl font-bold text-gray-800 dark:text-white">
                                ${{ number_format($item->quantity * $item->unit_price, 2) }}
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Stock Value</p>
                        </div>
                    </div>

                    <!-- Stock Progress -->
                    <div class="mb-6">
                        <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-1">
                            <span>Stock Level</span>
                            <span>{{ $item->quantity }} / {{ $item->max_stock }}</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            @php
                                $percentage = $item->quantity > 0 ? min(100, ($item->quantity / $item->max_stock) * 100) : 0;
                                $color = $item->quantity <= $item->min_stock ? 'bg-danger' : ($item->quantity <= ($item->max_stock * 0.3) ? 'bg-warning' : 'bg-success');
                            @endphp
                            <div class="h-2 rounded-full {{ $color }}" style="width: {{ $percentage }}%"></div>
                        </div>
                        <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mt-1">
                            <span>Min: {{ $item->min_stock }}</span>
                            <span>Max: {{ $item->max_stock }}</span>
                        </div>
                    </div>

                    <!-- Quick Info -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Category</span>
                            <span class="font-medium text-gray-800 dark:text-white">
                                {{ $item->category->name ?? 'Uncategorized' }}
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Location</span>
                            <span class="font-medium text-gray-800 dark:text-white">
                                {{ $item->location->name ?? 'Main Store' }}
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Unit Price</span>
                            <span class="font-medium text-gray-800 dark:text-white">
                                ${{ number_format($item->unit_price, 2) }}
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Unit of Measure</span>
                            <span class="font-medium text-gray-800 dark:text-white">
                                {{ $item->unit_of_measure }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm">
                <div class="p-6">
                    <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Quick Actions</h4>
                    <div class="space-y-3">
                        @can('update', $item)
                        <a href="{{ route('inventory.adjust', $item) }}" class="flex items-center p-3 bg-info/10 text-info rounded hover:bg-info/20">
                            <i class="fas fa-sliders-h mr-2"></i>
                            <span>Adjust Stock</span>
                        </a>
                        @endcan
                        
                        @can('update', $item)
                        <a href="{{ route('inventory.transfer', $item) }}" class="flex items-center p-3 bg-warning/10 text-warning rounded hover:bg-warning/20">
                            <i class="fas fa-exchange-alt mr-2"></i>
                            <span>Transfer Stock</span>
                        </a>
                        @endcan
                        
                        <a href="{{ route('inventory.history', $item) }}" class="flex items-center p-3 bg-primary/10 text-primary rounded hover:bg-primary/20">
                            <i class="fas fa-history mr-2"></i>
                            <span>View History</span>
                        </a>
                        
                        <button onclick="printDetails()" class="w-full flex items-center p-3 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white rounded hover:bg-gray-200 dark:hover:bg-gray-600">
                            <i class="fas fa-print mr-2"></i>
                            <span>Print Details</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Details -->
        <div class="col-span-12 lg:col-span-8">
            <!-- Tabs -->
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm mb-6">
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <nav class="flex space-x-1 px-6 overflow-x-auto">
                        <button onclick="showTab('details')" id="details-tab" class="px-4 py-3 text-sm font-medium border-b-2 border-primary text-primary whitespace-nowrap">Item Details</button>
                        <button onclick="showTab('stock')" id="stock-tab" class="px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 whitespace-nowrap">Stock Info</button>
                        <button onclick="showTab('pricing')" id="pricing-tab" class="px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 whitespace-nowrap">Pricing</button>
                        <button onclick="showTab('supplier')" id="supplier-tab" class="px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 whitespace-nowrap">Supplier</button>
                        <button onclick="showTab('history')" id="history-tab" class="px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 whitespace-nowrap">History</button>
                    </nav>
                </div>

                <!-- Details Tab -->
                <div id="details-tab-content" class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Item Name</label>
                            <p class="text-gray-800 dark:text-white font-medium">{{ $item->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">SKU</label>
                            <p class="text-gray-800 dark:text-white font-medium">{{ $item->sku }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Brand</label>
                            <p class="text-gray-800 dark:text-white font-medium">{{ $item->brand ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Model</label>
                            <p class="text-gray-800 dark:text-white font-medium">{{ $item->model ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Category</label>
                            <p class="text-gray-800 dark:text-white font-medium">{{ $item->category->name ?? 'Uncategorized' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Subcategory</label>
                            <p class="text-gray-800 dark:text-white font-medium">{{ $item->subcategory->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Item Type</label>
                            <p class="text-gray-800 dark:text-white font-medium">
                                {{ ucfirst(str_replace('_', ' ', $item->item_type)) }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Unit of Measure</label>
                            <p class="text-gray-800 dark:text-white font-medium">{{ $item->unit_of_measure }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Description</label>
                            <p class="text-gray-800 dark:text-white">{{ $item->description ?? 'No description' }}</p>
                        </div>
                    </div>

                    <!-- Additional Details -->
                    @if($item->weight || $item->dimensions)
                    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Physical Details</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @if($item->weight)
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Weight</label>
                                <p class="text-gray-800 dark:text-white">{{ $item->weight }} kg</p>
                            </div>
                            @endif
                            @if($item->dimensions)
                            <div>
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Dimensions</label>
                                <p class="text-gray-800 dark:text-white">{{ $item->dimensions }} cm</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Stock Tab -->
                <div id="stock-tab-content" class="p-6 hidden">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Current Quantity</label>
                            <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $item->quantity }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Stock Value</label>
                            <p class="text-2xl font-bold text-gray-800 dark:text-white">
                                ${{ number_format($item->quantity * $item->unit_price, 2) }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Minimum Stock Level</label>
                            <p class="text-gray-800 dark:text-white font-medium">{{ $item->min_stock }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Maximum Stock Level</label>
                            <p class="text-gray-800 dark:text-white font-medium">{{ $item->max_stock }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Reorder Quantity</label>
                            <p class="text-gray-800 dark:text-white font-medium">{{ $item->reorder_quantity }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Status</label>
                            <p class="text-gray-800 dark:text-white font-medium">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    @if($item->status === 'in_stock') bg-success/10 text-success 
                                    @elseif($item->status === 'low_stock') bg-warning/10 text-warning 
                                    @elseif($item->status === 'out_of_stock') bg-danger/10 text-danger 
                                    @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 @endif">
                                    {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Location</label>
                            <p class="text-gray-800 dark:text-white font-medium">{{ $item->location->name ?? 'Main Store' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Shelf/Bin Number</label>
                            <p class="text-gray-800 dark:text-white font-medium">{{ $item->shelf_number ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Expiry Date</label>
                            <p class="text-gray-800 dark:text-white font-medium">
                                @if($item->expiry_date)
                                    {{ $item->expiry_date->format('M d, Y') }}
                                    @if($item->expiry_date->isPast())
                                    <span class="ml-2 text-danger">(Expired)</span>
                                    @elseif($item->expiry_date->diffInDays(now()) <= 30)
                                    <span class="ml-2 text-warning">(Expiring in {{ $item->expiry_date->diffInDays(now()) }} days)</span>
                                    @endif
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Batch/Lot Number</label>
                            <p class="text-gray-800 dark:text-white font-medium">{{ $item->batch_number ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <!-- Stock History Chart -->
                    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Stock Movement (Last 30 Days)</h4>
                        <div class="h-64 bg-gray-50 dark:bg-gray-700/50 rounded-lg flex items-center justify-center">
                            <p class="text-gray-500 dark:text-gray-400">Stock movement chart would be displayed here</p>
                        </div>
                    </div>
                </div>

                <!-- Pricing Tab -->
                <div id="pricing-tab-content" class="p-6 hidden">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Cost Price</label>
                            <p class="text-2xl font-bold text-gray-800 dark:text-white">
                                ${{ number_format($item->cost_price, 2) }}
                            </p>
                            <p class="text-sm text-gray-500">Purchase cost per unit</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Unit Price</label>
                            <p class="text-2xl font-bold text-gray-800 dark:text-white">
                                ${{ number_format($item->unit_price, 2) }}
                            </p>
                            <p class="text-sm text-gray-500">Selling price per unit</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Retail Price</label>
                            <p class="text-xl font-medium text-gray-800 dark:text-white">
                                ${{ number_format($item->retail_price ?? $item->unit_price, 2) }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Profit Margin</label>
                            <p class="text-xl font-medium text-success">
                                @php
                                    $margin = $item->cost_price > 0 ? (($item->unit_price - $item->cost_price) / $item->cost_price * 100) : 0;
                                @endphp
                                {{ number_format($margin, 2) }}%
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Tax Rate</label>
                            <p class="text-gray-800 dark:text-white">{{ $item->tax_rate }}%</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Discount</label>
                            <p class="text-gray-800 dark:text-white">{{ $item->discount }}%</p>
                        </div>
                        <div class="md:col-span-2">
                            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <h5 class="font-medium text-gray-800 dark:text-white mb-2">Pricing Summary</h5>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="text-center">
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Total Cost Value</p>
                                        <p class="text-lg font-bold text-gray-800 dark:text-white">
                                            ${{ number_format($item->quantity * $item->cost_price, 2) }}
                                        </p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Total Selling Value</p>
                                        <p class="text-lg font-bold text-gray-800 dark:text-white">
                                            ${{ number_format($item->quantity * $item->unit_price, 2) }}
                                        </p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Potential Profit</p>
                                        <p class="text-lg font-bold text-success">
                                            ${{ number_format($item->quantity * ($item->unit_price - $item->cost_price), 2) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Supplier Tab -->
                <div id="supplier-tab-content" class="p-6 hidden">
                    @if($item->supplier)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Supplier Name</label>
                            <p class="text-gray-800 dark:text-white font-medium">{{ $item->supplier->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Supplier Code</label>
                            <p class="text-gray-800 dark:text-white font-medium">{{ $item->supplier_code ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Lead Time</label>
                            <p class="text-gray-800 dark:text-white font-medium">{{ $item->lead_time }} days</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Last Ordered</label>
                            <p class="text-gray-800 dark:text-white font-medium">
                                {{ $item->last_ordered_at ? $item->last_ordered_at->format('M d, Y') : 'Never' }}
                            </p>
                        </div>
                        <div class="md:col-span-2">
                            <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                <h5 class="font-medium text-gray-800 dark:text-white mb-2">Supplier Contact Information</h5>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Contact Person</p>
                                        <p class="text-gray-800 dark:text-white">{{ $item->supplier->contact_person ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Phone</p>
                                        <p class="text-gray-800 dark:text-white">{{ $item->supplier->phone ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Email</p>
                                        <p class="text-gray-800 dark:text-white">{{ $item->supplier->email ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Website</p>
                                        <p class="text-gray-800 dark:text-white">{{ $item->supplier->website ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                        <i class="fas fa-truck text-4xl mb-3"></i>
                        <p>No supplier assigned to this item</p>
                    </div>
                    @endif

                    <!-- Purchase History -->
                    @if($purchaseHistory && $purchaseHistory->count() > 0)
                    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Recent Purchases</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">PO Number</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Quantity</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Unit Cost</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($purchaseHistory->take(5) as $purchase)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                            {{ $purchase->purchase_date->format('M d, Y') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                            {{ $purchase->purchase_order_number }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                            {{ $purchase->quantity }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                            ${{ number_format($purchase->unit_cost, 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                            ${{ number_format($purchase->total_cost, 2) }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- History Tab -->
                <div id="history-tab-content" class="p-6 hidden">
                    <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Stock Movement History</h4>
                    
                    @if($stockHistory && $stockHistory->count() > 0)
                    <div class="space-y-4">
                        @foreach($stockHistory as $history)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-gray-800 dark:text-white">
                                        {{ ucfirst($history->type) }} - {{ $history->reference }}
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $history->created_at->format('M d, Y h:i A') }}
                                    </p>
                                    @if($history->notes)
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                        {{ $history->notes }}
                                    </p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold 
                                        @if($history->type === 'in') text-success 
                                        @elseif($history->type === 'out') text-danger 
                                        @else text-warning @endif">
                                        @if($history->type === 'in')+ @elseif($history->type === 'out')- @endif{{ $history->quantity }}
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        New Stock: {{ $history->new_quantity }}
                                    </p>
                                </div>
                            </div>
                            <div class="mt-2 flex items-center text-sm text-gray-500">
                                <i class="fas fa-user mr-1"></i>
                                <span>{{ $history->user->name ?? 'System' }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    @if($stockHistory->hasPages())
                    <div class="mt-6">
                        {{ $stockHistory->links() }}
                    </div>
                    @endif
                    @else
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                        <i class="fas fa-history text-4xl mb-3"></i>
                        <p>No stock movement history found</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Danger Zone -->
            @can('delete', $item)
            <div class="bg-white rounded-lg border border-red-200 dark:bg-gray-800 dark:border-red-900 shadow-sm">
                <div class="p-6">
                    <h4 class="text-lg font-semibold text-red-700 dark:text-red-400 mb-2">Danger Zone</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Deleting this item will permanently remove it from the inventory. This action cannot be undone.
                    </p>
                    <form action="{{ route('inventory.destroy', $item) }}" method="POST" 
                          onsubmit="return confirm('Are you sure you want to delete this inventory item? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-danger text-white rounded hover:bg-danger/90">
                            <i class="fas fa-trash mr-1"></i> Delete Item
                        </button>
                    </form>
                </div>
            </div>
            @endcan
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('[id$="-tab-content"]').forEach(tab => {
        tab.classList.add('hidden');
    });
    
    // Remove active state from all tabs
    document.querySelectorAll('[id$="-tab"]').forEach(tabBtn => {
        tabBtn.classList.remove('border-primary', 'text-primary');
        tabBtn.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab content
    document.getElementById(tabName + '-tab-content').classList.remove('hidden');
    
    // Activate selected tab button
    document.getElementById(tabName + '-tab').classList.add('border-primary', 'text-primary');
    document.getElementById(tabName + '-tab').classList.remove('border-transparent', 'text-gray-500');
}

function printDetails() {
    var printWindow = window.open('', '', 'height=600,width=800');
    printWindow.document.write(`
        <html>
        <head>
            <title>Inventory Item Details - {{ $item->name }}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .section { margin-bottom: 20px; }
                .section-title { font-weight: bold; border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-bottom: 10px; }
                .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
                .label { font-weight: bold; color: #666; }
                .value { margin-bottom: 5px; }
                .no-print { display: none; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Inventory Item Details</h1>
                <p>Printed on: {{ date('Y-m-d H:i:s') }}</p>
            </div>
            
            <div class="section">
                <div class="section-title">Basic Information</div>
                <div class="grid-2">
                    <div><span class="label">Item Name:</span> <span class="value">{{ $item->name }}</span></div>
                    <div><span class="label">SKU:</span> <span class="value">{{ $item->sku }}</span></div>
                    <div><span class="label">Brand:</span> <span class="value">{{ $item->brand ?? 'N/A' }}</span></div>
                    <div><span class="label">Model:</span> <span class="value">{{ $item->model ?? 'N/A' }}</span></div>
                    <div><span class="label">Category:</span> <span class="value">{{ $item->category->name ?? 'Uncategorized' }}</span></div>
                    <div><span class="label">Status:</span> <span class="value">{{ ucfirst(str_replace('_', ' ', $item->status)) }}</span></div>
                </div>
            </div>
            
            <div class="section">
                <div class="section-title">Stock Information</div>
                <div class="grid-2">
                    <div><span class="label">Current Quantity:</span> <span class="value">{{ $item->quantity }}</span></div>
                    <div><span class="label">Min Stock:</span> <span class="value">{{ $item->min_stock }}</span></div>
                    <div><span class="label">Max Stock:</span> <span class="value">{{ $item->max_stock }}</span></div>
                    <div><span class="label">Reorder Qty:</span> <span class="value">{{ $item->reorder_quantity }}</span></div>
                    <div><span class="label">Location:</span> <span class="value">{{ $item->location->name ?? 'Main Store' }}</span></div>
                    <div><span class="label">Shelf/Bin:</span> <span class="value">{{ $item->shelf_number ?? 'N/A' }}</span></div>
                </div>
            </div>
            
            <div class="section">
                <div class="section-title">Pricing Information</div>
                <div class="grid-2">
                    <div><span class="label">Cost Price:</span> <span class="value">${{ number_format($item->cost_price, 2) }}</span></div>
                    <div><span class="label">Unit Price:</span> <span class="value">${{ number_format($item->unit_price, 2) }}</span></div>
                    <div><span class="label">Retail Price:</span> <span class="value">${{ number_format($item->retail_price ?? $item->unit_price, 2) }}</span></div>
                    <div><span class="label">Stock Value:</span> <span class="value">${{ number_format($item->quantity * $item->unit_price, 2) }}</span></div>
                </div>
            </div>
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}
</script>
@endpush