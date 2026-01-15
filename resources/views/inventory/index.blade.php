@extends('layouts.app')

@section('content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Inventory Management</h2>
                <p class="text-gray-600 dark:text-gray-400">Manage inventory items and stock levels</p>
            </div>
            @can('create', App\Models\Inventory::class)
            <div class="flex items-center space-x-2">
                <a href="{{ route('inventory.create') }}" class="px-4 py-2 bg-primary text-white rounded hover:bg-primary/90">
                    <i class="fas fa-plus mr-1"></i> Add Item
                </a>
                <a href="{{ route('inventory.export') }}" class="px-4 py-2 bg-success text-white rounded hover:bg-success/90">
                    <i class="fas fa-file-export mr-1"></i> Export
                </a>
                <button onclick="printTable()" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                    <i class="fas fa-print mr-1"></i> Print
                </button>
            </div>
            @endcan
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-primary/10 text-primary mr-4">
                    <i class="fas fa-boxes text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Items</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $totalItems ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-success/10 text-success mr-4">
                    <i class="fas fa-check-circle text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">In Stock</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $inStock ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-warning/10 text-warning mr-4">
                    <i class="fas fa-exclamation-triangle text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Low Stock</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $lowStock ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-danger/10 text-danger mr-4">
                    <i class="fas fa-times-circle text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Out of Stock</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $outOfStock ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 p-4 mb-6">
        <form action="{{ route('inventory.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                <input type="text" name="search" placeholder="Item name, SKU, description..." 
                       value="{{ request('search') }}" class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                <select name="category" class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                <select name="status" class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">
                    <option value="">All Status</option>
                    <option value="in_stock" {{ request('status') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                    <option value="low_stock" {{ request('status') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                    <option value="out_of_stock" {{ request('status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                    <option value="discontinued" {{ request('status') == 'discontinued' ? 'selected' : '' }}>Discontinued</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Location</label>
                <select name="location" class="w-full border border-gray-300 dark:border-gray-700 rounded px-3 py-2 dark:bg-gray-900">
                    <option value="">All Locations</option>
                    @foreach($locations as $location)
                    <option value="{{ $location->id }}" {{ request('location') == $location->id ? 'selected' : '' }}>
                        {{ $location->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-4 flex justify-end space-x-2">
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded hover:bg-primary/90">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
                <a href="{{ route('inventory.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Quick Actions -->
    <div class="flex flex-wrap gap-3 mb-6">
        <a href="{{ route('inventory.index') }}" class="px-4 py-2 bg-warning/10 text-warning rounded hover:bg-warning/20">
            <i class="fas fa-exclamation-triangle mr-1"></i> Low Stock Alert ({{ $lowStock ?? 0 }})
        </a>
        <a href="{{ route('inventory.create') }}" class="px-4 py-2 bg-danger/10 text-danger rounded hover:bg-danger/20">
            <i class="fas fa-shopping-cart mr-1"></i> Reorder Items ({{ $reorderCount ?? 0 }})
        </a>
        <a href="{{ route('inventory.index') }}" class="px-4 py-2 bg-info/10 text-info rounded hover:bg-info/20">
            <i class="fas fa-calendar-times mr-1"></i> Expiring Soon
        </a>
        <a href="{{ route('inventory.index') }}" class="px-4 py-2 bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400 rounded hover:bg-purple-200 dark:hover:bg-purple-900/30">
            <i class="fas fa-chart-bar mr-1"></i> Inventory Reports
        </a>
    </div>

    <!-- Inventory Table -->
    <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" id="inventoryTable">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">SKU</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Item</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Value</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Location</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($items as $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                {{ $item->sku }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    @if($item->image)
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <img class="h-10 w-10 rounded-lg object-cover" src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}">
                                    </div>
                                    @else
                                    <div class="flex-shrink-0 h-10 w-10 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-box text-gray-400"></i>
                                    </div>
                                    @endif
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $item->name }}
                                            @if($item->brand)
                                            <span class="text-xs text-gray-500">({{ $item->brand }})</span>
                                            @endif
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ Str::limit($item->description, 50) }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    {{ $item->category->name ?? 'Uncategorized' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-24 bg-gray-200 dark:bg-gray-700 rounded-full h-2 mr-3">
                                        @php
                                            $percentage = $item->quantity > 0 ? min(100, ($item->quantity / $item->max_stock) * 100) : 0;
                                            $color = $item->quantity <= $item->min_stock ? 'bg-danger' : ($item->quantity <= ($item->max_stock * 0.3) ? 'bg-warning' : 'bg-success');
                                        @endphp
                                        <div class="h-2 rounded-full {{ $color }}" style="width: {{ $percentage }}%"></div>
                                    </div>
                                    <span class="text-sm font-medium {{ $item->quantity <= $item->min_stock ? 'text-danger' : ($item->quantity <= ($item->max_stock * 0.3) ? 'text-warning' : 'text-gray-900 dark:text-white') }}">
                                        {{ $item->quantity }}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Min: {{ $item->min_stock }} | Max: {{ $item->max_stock }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                <div class="font-medium">${{ number_format($item->unit_price, 2) }}</div>
                                @if($item->cost_price)
                                <div class="text-xs text-gray-500">Cost: ${{ number_format($item->cost_price, 2) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">
                                ${{ number_format($item->quantity * $item->unit_price, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $item->location->name ?? 'Main Store' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    @if($item->status === 'in_stock') bg-success/10 text-success 
                                    @elseif($item->status === 'low_stock') bg-warning/10 text-warning 
                                    @elseif($item->status === 'out_of_stock') bg-danger/10 text-danger 
                                    @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 @endif">
                                    {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                                </span>
                                @if($item->expiry_date && $item->expiry_date->isPast())
                                <span class="ml-1 px-2 py-1 text-xs rounded-full bg-danger/10 text-danger">
                                    Expired
                                </span>
                                @elseif($item->expiry_date && $item->expiry_date->diffInDays(now()) <= 30)
                                <span class="ml-1 px-2 py-1 text-xs rounded-full bg-warning/10 text-warning">
                                    Expiring
                                </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    @can('view', $item)
                                    <a href="{{ route('inventory.show', $item) }}" class="text-primary hover:text-primary/80" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endcan
                                    
                                    @can('update', $item)
                                    <a href="{{ route('inventory.edit', $item) }}" class="text-warning hover:text-warning/80" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    
                                    @if($item->quantity > 0)
                                    <a href="{{ route('inventory.adjust', $item) }}" class="text-info hover:text-info/80" title="Adjust Stock">
                                        <i class="fas fa-sliders-h"></i>
                                    </a>
                                    @endif
                                    
                                    @can('delete', $item)
                                    <form action="{{ route('inventory.destroy', $item) }}" method="POST" class="inline" 
                                          onsubmit="return confirm('Are you sure you want to delete this item?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-danger hover:text-danger/80" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                No inventory items found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($items->hasPages())
            <div class="mt-6">
                {{ $items->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function printTable() {
    var printContents = document.getElementById('inventoryTable').outerHTML;
    var originalContents = document.body.innerHTML;
    
    document.body.innerHTML = `
        <html>
        <head>
            <title>Inventory List - {{ date('Y-m-d') }}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                .no-print { display: none; }
                .status-in_stock { color: green; }
                .status-low_stock { color: orange; }
                .status-out_of_stock { color: red; }
            </style>
        </head>
        <body>
            <h2>Inventory List - {{ date('Y-m-d') }}</h2>
            ${printContents}
        </body>
        </html>
    `;
    
    window.print();
    document.body.innerHTML = originalContents;
    window.location.reload();
}
</script>
@endpush