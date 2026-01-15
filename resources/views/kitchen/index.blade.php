@extends('layouts.app')

@section('content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Kitchen Management</h2>
                <p class="text-gray-600 dark:text-gray-400">Manage kitchen orders and food preparation</p>
            </div>
            @can('create', App\Models\KitchenOrder::class)
            <div class="flex items-center space-x-2">
                <a href="{{ route('kitchen.create') }}" class="px-4 py-2 bg-primary text-white rounded hover:bg-primary/90">
                    <i class="fas fa-plus mr-1"></i> New Order
                </a>
                <button onclick="refreshOrders()" class="px-4 py-2 bg-success text-white rounded hover:bg-success/90">
                    <i class="fas fa-sync-alt mr-1"></i> Refresh
                </button>
                <button onclick="printKitchenDisplay()" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                    <i class="fas fa-print mr-1"></i> Print KDS
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
                    <i class="fas fa-clock text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Pending Orders</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $pendingOrders }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-warning/10 text-warning mr-4">
                    <i class="fas fa-utensils text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Preparing</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $preparingOrders }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-success/10 text-success mr-4">
                    <i class="fas fa-check-circle text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Ready</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $readyOrders }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-danger/10 text-danger mr-4">
                    <i class="fas fa-times-circle text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Cancelled</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $cancelledOrders }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Filters -->
    <div class="flex flex-wrap gap-3 mb-6">
        <a href="{{ route('kitchen.index', ['status' => 'pending']) }}" class="px-4 py-2 bg-primary/10 text-primary rounded hover:bg-primary/20">
            <i class="fas fa-clock mr-1"></i> Pending ({{ $pendingOrders }})
        </a>
        <a href="{{ route('kitchen.index', ['status' => 'preparing']) }}" class="px-4 py-2 bg-warning/10 text-warning rounded hover:bg-warning/20">
            <i class="fas fa-utensils mr-1"></i> Preparing ({{ $preparingOrders }})
        </a>
        <a href="{{ route('kitchen.index', ['status' => 'ready']) }}" class="px-4 py-2 bg-success/10 text-success rounded hover:bg-success/20">
            <i class="fas fa-check-circle mr-1"></i> Ready ({{ $readyOrders }})
        </a>
        <a href="{{ route('kitchen.index', ['status' => 'completed']) }}" class="px-4 py-2 bg-info/10 text-info rounded hover:bg-info/20">
            <i class="fas fa-check-double mr-1"></i> Completed
        </a>
        <a href="{{ route('kitchen.index', ['priority' => 'high']) }}" class="px-4 py-2 bg-danger/10 text-danger rounded hover:bg-danger/20">
            <i class="fas fa-exclamation-triangle mr-1"></i> High Priority
        </a>
        <a href="{{ route('kitchen.index') }}" class="px-4 py-2 bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400 rounded hover:bg-blue-200 dark:hover:bg-blue-900/30">
            <i class="fas fa-calendar-day mr-1"></i> Today's Orders
        </a>
    </div>

    <!-- Kitchen Display System (KDS) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Pending Orders Column -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm h-full">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 flex items-center">
                        <span class="w-3 h-3 rounded-full bg-primary mr-2"></span>
                        Pending Orders
                        <span class="ml-2 px-2 py-1 text-xs bg-primary/10 text-primary rounded">{{ $pendingOrders }}</span>
                    </h3>
                </div>
                <div class="p-4 space-y-4 max-h-[600px] overflow-y-auto">
                    @forelse($orders->where('status', 'pending') as $order)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-primary dark:hover:border-primary">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h4 class="font-medium text-gray-800 dark:text-white">Order #{{ $order->order_number }}</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Table {{ $order->table_number ?? 'Takeaway' }}
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="px-2 py-1 text-xs rounded-full bg-primary/10 text-primary">
                                    {{ strtoupper($order->priority) }}
                                </span>
                                <p class="text-xs text-gray-500 mt-1">{{ $order->created_at->format('h:i A') }}</p>
                            </div>
                        </div>
                        
                        <div class="space-y-2 mb-3">
                            @foreach($order->items as $item)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-700 dark:text-gray-300">
                                    {{ $item->quantity }}x {{ $item->menu_item->name }}
                                </span>
                                @if($item->special_instructions)
                                <span class="text-xs text-warning" title="{{ $item->special_instructions }}">
                                    <i class="fas fa-info-circle"></i>
                                </span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-800 dark:text-white">
                                {{ $order->estimated_time }} min
                            </span>
                            <div class="flex space-x-2">
                                @can('update', $order)
                                <form action="{{ route('kitchen.update-status', $order) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="preparing">
                                    <button type="submit" class="text-sm bg-warning text-white px-3 py-1 rounded hover:bg-warning/90">
                                        Start
                                    </button>
                                </form>
                                @endcan
                                
                                <a href="{{ route('kitchen.show', $order) }}" class="text-sm border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 px-3 py-1 rounded hover:bg-gray-50 dark:hover:bg-gray-800">
                                    View
                                </a>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                        <i class="fas fa-check-circle text-3xl mb-3"></i>
                        <p>No pending orders</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Preparing Orders Column -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm h-full">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 flex items-center">
                        <span class="w-3 h-3 rounded-full bg-warning mr-2"></span>
                        Preparing
                        <span class="ml-2 px-2 py-1 text-xs bg-warning/10 text-warning rounded">{{ $preparingOrders }}</span>
                    </h3>
                </div>
                <div class="p-4 space-y-4 max-h-[600px] overflow-y-auto">
                    @forelse($orders->where('status', 'preparing') as $order)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-warning dark:hover:border-warning">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h4 class="font-medium text-gray-800 dark:text-white">Order #{{ $order->order_number }}</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Table {{ $order->table_number ?? 'Takeaway' }}
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="px-2 py-1 text-xs rounded-full bg-warning/10 text-warning">
                                    {{ strtoupper($order->priority) }}
                                </span>
                                <p class="text-xs text-gray-500 mt-1">
                                    Started: {{ $order->started_at ? $order->started_at->format('h:i A') : '--:--' }}
                                </p>
                            </div>
                        </div>
                        
                        <div class="space-y-2 mb-3">
                            @foreach($order->items as $item)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-700 dark:text-gray-300">
                                    {{ $item->quantity }}x {{ $item->menu_item->name }}
                                </span>
                                @if($item->special_instructions)
                                <span class="text-xs text-warning" title="{{ $item->special_instructions }}">
                                    <i class="fas fa-info-circle"></i>
                                </span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        
                        <!-- Progress Bar -->
                        <div class="mb-3">
                            <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400 mb-1">
                                <span>Preparation Progress</span>
                                <span>
                                    @php
                                        $progress = $order->started_at ? min(100, (now()->diffInMinutes($order->started_at) / $order->estimated_time) * 100) : 0;
                                    @endphp
                                    {{ number_format($progress) }}%
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-warning h-2 rounded-full" style="width: {{ $progress }}%"></div>
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-800 dark:text-white">
                                {{ max(0, $order->estimated_time - ($order->started_at ? now()->diffInMinutes($order->started_at) : 0)) }} min left
                            </span>
                            <div class="flex space-x-2">
                                @can('update', $order)
                                <form action="{{ route('kitchen.update-status', $order) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="ready">
                                    <button type="submit" class="text-sm bg-success text-white px-3 py-1 rounded hover:bg-success/90">
                                        Ready
                                    </button>
                                </form>
                                @endcan
                                
                                <a href="{{ route('kitchen.show', $order) }}" class="text-sm border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 px-3 py-1 rounded hover:bg-gray-50 dark:hover:bg-gray-800">
                                    View
                                </a>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                        <i class="fas fa-utensils text-3xl mb-3"></i>
                        <p>No orders in preparation</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Ready Orders Column -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm h-full">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 flex items-center">
                        <span class="w-3 h-3 rounded-full bg-success mr-2"></span>
                        Ready for Service
                        <span class="ml-2 px-2 py-1 text-xs bg-success/10 text-success rounded">{{ $readyOrders }}</span>
                    </h3>
                </div>
                <div class="p-4 space-y-4 max-h-[600px] overflow-y-auto">
                    @forelse($orders->where('status', 'ready') as $order)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-success dark:hover:border-success">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h4 class="font-medium text-gray-800 dark:text-white">Order #{{ $order->order_number }}</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Table {{ $order->table_number ?? 'Takeaway' }}
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="px-2 py-1 text-xs rounded-full bg-success/10 text-success">
                                    READY
                                </span>
                                <p class="text-xs text-gray-500 mt-1">
                                    Ready: {{ $order->ready_at ? $order->ready_at->format('h:i A') : '--:--' }}
                                </p>
                                <p class="text-xs text-danger mt-1">
                                    @if($order->ready_at)
                                        {{ now()->diffInMinutes($order->ready_at) }} min waiting
                                    @endif
                                </p>
                            </div>
                        </div>
                        
                        <div class="space-y-2 mb-3">
                            @foreach($order->items as $item)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-700 dark:text-gray-300">
                                    {{ $item->quantity }}x {{ $item->menu_item->name }}
                                </span>
                                @if($item->special_instructions)
                                <span class="text-xs text-warning" title="{{ $item->special_instructions }}">
                                    <i class="fas fa-info-circle"></i>
                                </span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-danger font-medium">
                                @if($order->ready_at)
                                    Waiting {{ now()->diffInMinutes($order->ready_at) }} min
                                @endif
                            </span>
                            <div class="flex space-x-2">
                                @can('update', $order)
                                <form action="{{ route('kitchen.update-status', $order) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="completed">
                                    <button type="submit" class="text-sm bg-info text-white px-3 py-1 rounded hover:bg-info/90">
                                        Served
                                    </button>
                                </form>
                                @endcan
                                
                                <a href="{{ route('kitchen.show', $order) }}" class="text-sm border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 px-3 py-1 rounded hover:bg-gray-50 dark:hover:bg-gray-800">
                                    View
                                </a>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                        <i class="fas fa-concierge-bell text-3xl mb-3"></i>
                        <p>No orders ready for service</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- All Orders Table (Optional - below KDS) -->
    <div class="mt-6 bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">All Kitchen Orders</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Order #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Table/Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Items</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Priority</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($allOrders as $order)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                {{ $order->order_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                @if($order->table_number)
                                Table {{ $order->table_number }}
                                @else
                                <span class="text-warning">Takeaway</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                {{ $order->items->count() }} items
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    @if($order->status === 'pending') bg-primary/10 text-primary 
                                    @elseif($order->status === 'preparing') bg-warning/10 text-warning 
                                    @elseif($order->status === 'ready') bg-success/10 text-success 
                                    @elseif($order->status === 'completed') bg-info/10 text-info 
                                    @else bg-danger/10 text-danger @endif">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    @if($order->priority === 'high') bg-danger/10 text-danger 
                                    @elseif($order->priority === 'medium') bg-warning/10 text-warning 
                                    @else bg-success/10 text-success @endif">
                                    {{ ucfirst($order->priority) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $order->created_at->format('h:i A') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="{{ route('kitchen.show', $order) }}" class="text-primary hover:text-primary/80">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @can('update', $order)
                                    <a href="{{ route('kitchen.edit', $order) }}" class="text-warning hover:text-warning/80">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                No orders found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($allOrders->hasPages())
            <div class="mt-6">
                {{ $allOrders->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function refreshOrders() {
    window.location.reload();
}

function printKitchenDisplay() {
    var printWindow = window.open('', '', 'height=800,width=1200');
    printWindow.document.write(`
        <html>
        <head>
            <title>Kitchen Display System - {{ date('Y-m-d H:i') }}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .kds-container { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
                .column { border: 2px solid #ddd; border-radius: 10px; padding: 15px; }
                .column-title { font-size: 18px; font-weight: bold; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid; }
                .pending .column-title { color: #3b82f6; border-color: #3b82f6; }
                .preparing .column-title { color: #f59e0b; border-color: #f59e0b; }
                .ready .column-title { color: #10b981; border-color: #10b981; }
                .order-card { border: 1px solid #ddd; border-radius: 8px; padding: 12px; margin-bottom: 15px; }
                .order-header { display: flex; justify-content: space-between; margin-bottom: 10px; }
                .order-number { font-weight: bold; font-size: 16px; }
                .order-time { font-size: 12px; color: #666; }
                .order-items { margin-bottom: 10px; }
                .order-item { display: flex; justify-content: space-between; margin-bottom: 5px; }
                .priority { font-size: 12px; padding: 2px 6px; border-radius: 4px; }
                .priority-high { background: #fef2f2; color: #dc2626; }
                .priority-medium { background: #fffbeb; color: #d97706; }
                .priority-low { background: #f0fdf4; color: #059669; }
                .status-badge { font-size: 12px; padding: 2px 8px; border-radius: 4px; }
                .status-pending { background: #dbeafe; color: #1d4ed8; }
                .status-preparing { background: #fef3c7; color: #d97706; }
                .status-ready { background: #d1fae5; color: #065f46; }
                .no-orders { text-align: center; padding: 30px; color: #9ca3af; }
                @media print {
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            <h1>Kitchen Display System</h1>
            <p>Printed: {{ date('Y-m-d H:i') }}</p>
            
            <div class="kds-container">
                <!-- Pending Column -->
                <div class="column pending">
                    <div class="column-title">Pending Orders ({{ $pendingOrders }})</div>
                    @forelse($orders->where('status', 'pending') as $order)
                    <div class="order-card">
                        <div class="order-header">
                            <div>
                                <div class="order-number">Order #{{ $order->order_number }}</div>
                                <div class="order-time">{{ $order->created_at->format('h:i A') }}</div>
                            </div>
                            <div class="priority priority-{{ $order->priority }}">{{ strtoupper($order->priority) }}</div>
                        </div>
                        <div class="order-items">
                            @foreach($order->items as $item)
                            <div class="order-item">
                                <span>{{ $item->quantity }}x {{ $item->menu_item->name }}</span>
                                @if($item->special_instructions)
                                <span title="{{ $item->special_instructions }}">⚠️</span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        <div>Table: {{ $order->table_number ?? 'Takeaway' }}</div>
                        <div>Est. Time: {{ $order->estimated_time }} min</div>
                    </div>
                    @empty
                    <div class="no-orders">No pending orders</div>
                    @endforelse
                </div>
                
                <!-- Preparing Column -->
                <div class="column preparing">
                    <div class="column-title">Preparing ({{ $preparingOrders }})</div>
                    @forelse($orders->where('status', 'preparing') as $order)
                    <div class="order-card">
                        <div class="order-header">
                            <div>
                                <div class="order-number">Order #{{ $order->order_number }}</div>
                                <div class="order-time">Started: {{ $order->started_at ? $order->started_at->format('h:i A') : '--:--' }}</div>
                            </div>
                            <div class="priority priority-{{ $order->priority }}">{{ strtoupper($order->priority) }}</div>
                        </div>
                        <div class="order-items">
                            @foreach($order->items as $item)
                            <div class="order-item">
                                <span>{{ $item->quantity }}x {{ $item->menu_item->name }}</span>
                                @if($item->special_instructions)
                                <span title="{{ $item->special_instructions }}">⚠️</span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        <div>Table: {{ $order->table_number ?? 'Takeaway' }}</div>
                        <div>Time Left: {{ max(0, $order->estimated_time - ($order->started_at ? now()->diffInMinutes($order->started_at) : 0)) }} min</div>
                    </div>
                    @empty
                    <div class="no-orders">No orders in preparation</div>
                    @endforelse
                </div>
                
                <!-- Ready Column -->
                <div class="column ready">
                    <div class="column-title">Ready for Service ({{ $readyOrders }})</div>
                    @forelse($orders->where('status', 'ready') as $order)
                    <div class="order-card">
                        <div class="order-header">
                            <div>
                                <div class="order-number">Order #{{ $order->order_number }}</div>
                                <div class="order-time">Ready: {{ $order->ready_at ? $order->ready_at->format('h:i A') : '--:--' }}</div>
                            </div>
                            <div class="status-badge status-ready">READY</div>
                        </div>
                        <div class="order-items">
                            @foreach($order->items as $item)
                            <div class="order-item">
                                <span>{{ $item->quantity }}x {{ $item->menu_item->name }}</span>
                                @if($item->special_instructions)
                                <span title="{{ $item->special_instructions }}">⚠️</span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        <div>Table: {{ $order->table_number ?? 'Takeaway' }}</div>
                        <div>Waiting: {{ $order->ready_at ? now()->diffInMinutes($order->ready_at) : 0 }} min</div>
                    </div>
                    @empty
                    <div class="no-orders">No orders ready for service</div>
                    @endforelse
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