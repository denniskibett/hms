<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 mb-6">
    <!-- Pending Orders -->
    <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending Orders</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $pendingOrders ?? 0 }}</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Awaiting preparation</p>
            </div>
            <div class="p-3 rounded-full bg-primary/10">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Preparing Orders -->
    <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Preparing Orders</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['preparing_orders'] ?? 0 }}</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">In progress</p>
            </div>
            <div class="p-3 rounded-full bg-warning/10">
                <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Completed Today -->
    <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Completed Today</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['completed_today'] ?? 0 }}</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Orders fulfilled</p>
            </div>
            <div class="p-3 rounded-full bg-success/10">
                <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Low Stock Items -->
    <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Low Stock Items</p>
                <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['low_stock_items'] ?? 0 }}</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Needs restocking</p>
            </div>
            <div class="p-3 rounded-full bg-danger/10">
                <svg class="w-6 h-6 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.342 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-12 gap-6">
    <!-- Pending Orders -->
    <div class="col-span-12 lg:col-span-7">
        <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Pending Orders</h3>
                <a href="{{ route('kitchen.orders.index') }}" class="text-sm text-primary hover:underline">View All</a>
            </div>
            <div class="space-y-4">
                @forelse($pendingOrders as $order)
                <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-800">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-medium text-gray-900 dark:text-white">Order #{{ $order->order_number }}</h4>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-primary/10 text-primary">
                                    Room {{ $order->room->room_number ?? 'N/A' }}
                                </span>
                            </div>
                            <div class="mb-3">
                                @foreach($order->items as $item)
                                <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400 mb-1">
                                    <span>{{ $item->quantity }}x {{ $item->menuItem->name }}</span>
                                    <span class="font-medium">{{ $item->menuItem->price }}</span>
                                </div>
                                @endforeach
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $order->created_at->format('h:i A') }}
                                </div>
                                <div class="flex items-center space-x-2">
                                    <form action="{{ route('kitchen.orders.update-status', $order) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="preparing">
                                        <button type="submit" class="px-3 py-1 text-sm bg-primary text-white rounded hover:bg-primary/90">
                                            Start Prep
                                        </button>
                                    </form>
                                    <a href="{{ route('kitchen.orders.show', $order) }}" class="px-3 py-1 text-sm border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                                        View
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <p class="text-gray-500 dark:text-gray-400">No pending orders</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="col-span-12 lg:col-span-5 space-y-6">
        <!-- Preparing Orders -->
        @if($preparingOrders->count() > 0)
        <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Preparing Orders</h3>
                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-warning/10 text-warning">
                    {{ $preparingOrders->count() }} orders
                </span>
            </div>
            <div class="space-y-3">
                @foreach($preparingOrders as $order)
                <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">Order #{{ $order->order_number }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Room {{ $order->room->room_number ?? 'N/A' }} • {{ $order->items->count() }} items
                        </p>
                    </div>
                    <form action="{{ route('kitchen.orders.update-status', $order) }}" method="POST" class="inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="completed">
                        <button type="submit" class="px-3 py-1 text-sm bg-success text-white rounded hover:bg-success/90">
                            Complete
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Low Stock Items -->
        <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Low Stock Items</h3>
                <a href="{{ route('inventory.index') }}" class="text-sm text-primary hover:underline">View All</a>
            </div>
            <div class="space-y-3">
                @forelse($lowStockItems as $item)
                <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $item->name }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $item->quantity }} left • Reorder at {{ $item->reorder_level }}
                        </p>
                    </div>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-danger/10 text-danger">
                        Low Stock
                    </span>
                </div>
                @empty
                <p class="text-gray-500 dark:text-gray-400 text-center py-4">No low stock items</p>
                @endforelse
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg border border-gray-200 dark:border-gray-800 dark:bg-white/[0.03] p-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('kitchen.menu.index') }}" class="p-4 rounded-lg border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 text-center">
                    <div class="p-2 rounded-full bg-primary/10 inline-block mb-2">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <p class="font-medium text-gray-900 dark:text-white">View Menu</p>
                </a>
                <a href="{{ route('inventory.index') }}" class="p-4 rounded-lg border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 text-center">
                    <div class="p-2 rounded-full bg-success/10 inline-block mb-2">
                        <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                    </div>
                    <p class="font-medium text-gray-900 dark:text-white">Inventory</p>
                </a>
                <a href="{{ route('inventory.purchase-orders.create') }}" class="p-4 rounded-lg border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 text-center">
                    <div class="p-2 rounded-full bg-warning/10 inline-block mb-2">
                        <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="font-medium text-gray-900 dark:text-white">Order Supplies</p>
                </a>
                <a href="{{ route('kitchen.orders.create') }}" class="p-4 rounded-lg border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 text-center">
                    <div class="p-2 rounded-full bg-info/10 inline-block mb-2">
                        <svg class="w-6 h-6 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                    <p class="font-medium text-gray-900 dark:text-white">New Order</p>
                </a>
            </div>
        </div>
    </div>
</div>