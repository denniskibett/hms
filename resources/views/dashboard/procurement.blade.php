<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 mb-6">
    <!-- Low Stock Items -->
    <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Low Stock Items</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['low_stock_items'] ?? 0 }}</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Needs reordering</p>
            </div>
            <div class="p-3 rounded-full bg-danger/10">
                <svg class="w-6 h-6 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.342 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Pending Orders -->
    <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending Orders</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['pending_orders'] ?? 0 }}</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Awaiting approval</p>
            </div>
            <div class="p-3 rounded-full bg-warning/10">
                <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Awaiting Delivery -->
    <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Awaiting Delivery</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['awaiting_delivery'] ?? 0 }}</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Orders in transit</p>
            </div>
            <div class="p-3 rounded-full bg-primary/10">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Inventory Value -->
    <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Inventory Value</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">KSH {{ number_format($stats['total_inventory_value'] ?? 0) }}</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Total stock value</p>
            </div>
            <div class="p-3 rounded-full bg-success/10">
                <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-12 gap-6">
    <!-- Low Stock Items -->
    <div class="col-span-12 lg:col-span-7">
        <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Low Stock Items</h3>
                <a href="{{ route('inventory.index', ['filter' => 'low_stock']) }}" class="text-sm text-primary hover:underline">View All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800">
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Item</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Current Stock</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Reorder Level</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Unit Price</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse($lowStockItems as $item)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900 dark:text-white">{{ $item->name }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $item->category->name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ 
                                    $item->quantity <= $item->reorder_level * 0.5 ? 'bg-danger/10 text-danger' : 'bg-warning/10 text-warning'
                                }}">
                                    {{ $item->quantity }} {{ $item->unit }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                {{ $item->reorder_level }} {{ $item->unit }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                KSH {{ number_format($item->unit_price) }}
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('inventory.purchase-orders.create', ['item_id' => $item->id]) }}" 
                                   class="px-3 py-1 text-sm bg-primary text-white rounded hover:bg-primary/90">
                                    Reorder
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                No low stock items
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="col-span-12 lg:col-span-5 space-y-6">
        <!-- Pending Purchase Orders -->
        <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Pending Orders</h3>
                <a href="{{ route('inventory.purchase-orders.index', ['status' => 'pending']) }}" class="text-sm text-primary hover:underline">View All</a>
            </div>
            <div class="space-y-3">
                @forelse($pendingOrders as $order)
                <div class="p-3 rounded-lg border border-gray-200 dark:border-gray-800">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">PO #{{ $order->order_number }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $order->supplier->name ?? 'N/A' }}</p>
                            <div class="flex items-center mt-2">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">
                                    KSH {{ number_format($order->total_amount) }}
                                </span>
                                <span class="mx-2 text-gray-400">•</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $order->created_at->format('M d') }}
                                </span>
                            </div>
                        </div>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-warning/10 text-warning">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                </div>
                @empty
                <p class="text-gray-500 dark:text-gray-400 text-center py-4">No pending orders</p>
                @endforelse
            </div>
        </div>

        <!-- Awaiting Delivery -->
        <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Awaiting Delivery</h3>
                <a href="{{ route('inventory.purchase-orders.index', ['delivery_status' => 'pending']) }}" class="text-sm text-primary hover:underline">View All</a>
            </div>
            <div class="space-y-3">
                @forelse($awaitingDelivery as $order)
                <div class="p-3 rounded-lg border border-gray-200 dark:border-gray-800">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">PO #{{ $order->order_number }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $order->supplier->name ?? 'N/A' }}</p>
                            <div class="flex items-center mt-2">
                                <span class="text-sm text-gray-900 dark:text-white">
                                    Expected: {{ $order->expected_delivery_date ? $order->expected_delivery_date->format('M d') : 'N/A' }}
                                </span>
                            </div>
                        </div>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-primary/10 text-primary">
                            {{ ucfirst($order->delivery_status) }}
                        </span>
                    </div>
                </div>
                @empty
                <p class="text-gray-500 dark:text-gray-400 text-center py-4">No orders awaiting delivery</p>
                @endforelse
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('inventory.purchase-orders.create') }}" class="p-4 rounded-lg border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 text-center">
                    <div class="p-2 rounded-full bg-primary/10 inline-block mb-2">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                    <p class="font-medium text-gray-900 dark:text-white">New Order</p>
                </a>
                <a href="#" class="p-4 rounded-lg border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 text-center">
                    <div class="p-2 rounded-full bg-success/10 inline-block mb-2">
                        <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <p class="font-medium text-gray-900 dark:text-white">Suppliers</p>
                </a>
                <a href="{{ route('inventory.index') }}" class="p-4 rounded-lg border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 text-center">
                    <div class="p-2 rounded-full bg-warning/10 inline-block mb-2">
                        <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                    </div>
                    <p class="font-medium text-gray-900 dark:text-white">Inventory</p>
                </a>
                <a href="{{ route('inventory.purchase-orders.index') }}" class="p-4 rounded-lg border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 text-center">
                    <div class="p-2 rounded-full bg-info/10 inline-block mb-2">
                        <svg class="w-6 h-6 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <p class="font-medium text-gray-900 dark:text-white">All Orders</p>
                </a>
            </div>
        </div>
    </div>
</div>