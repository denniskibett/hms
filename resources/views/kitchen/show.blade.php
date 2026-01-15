@extends('layouts.app')

@section('content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Kitchen Order Details</h2>
                <p class="text-gray-600 dark:text-gray-400">View order information and preparation status</p>
            </div>
            <div class="flex items-center space-x-2">
                @can('update', $order)
                <a href="{{ route('kitchen.edit', $order) }}" class="px-4 py-2 bg-warning text-white rounded hover:bg-warning/90">
                    <i class="fas fa-edit mr-1"></i> Edit Order
                </a>
                @endcan
                <a href="{{ route('kitchen.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Orders
                </a>
                <button onclick="printOrder()" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                    <i class="fas fa-print mr-1"></i> Print
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6">
        <!-- Left Column - Order Details -->
        <div class="col-span-12 lg:col-span-4">
            <!-- Order Card -->
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm mb-6">
                <div class="p-6">
                    <!-- Order Header -->
                    <div class="flex flex-col items-center mb-6">
                        <div class="text-center mb-4">
                            <h3 class="text-xl font-bold text-gray-800 dark:text-white/90">Order #{{ $order->order_number }}</h3>
                            <p class="text-gray-600 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $order->order_type)) }}</p>
                        </div>
                        
                        <!-- Status Badge -->
                        <div class="mb-4">
                            <span class="px-4 py-2 text-sm font-medium rounded-full 
                                @if($order->status === 'pending') bg-primary/10 text-primary 
                                @elseif($order->status === 'preparing') bg-warning/10 text-warning 
                                @elseif($order->status === 'ready') bg-success/10 text-success 
                                @elseif($order->status === 'completed') bg-info/10 text-info 
                                @else bg-danger/10 text-danger @endif">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                        
                        <!-- Priority Badge -->
                        <div>
                            <span class="px-3 py-1 text-xs font-medium rounded-full 
                                @if($order->priority === 'urgent') bg-danger/10 text-danger 
                                @elseif($order->priority === 'high') bg-danger/20 text-danger 
                                @elseif($order->priority === 'medium') bg-warning/10 text-warning 
                                @else bg-success/10 text-success @endif">
                                {{ ucfirst($order->priority) }} Priority
                            </span>
                        </div>
                    </div>

                    <!-- Order Timeline -->
                    <div class="mb-6">
                        <h4 class="font-medium text-gray-800 dark:text-white mb-3">Order Timeline</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Placed:</span>
                                <span class="text-gray-800 dark:text-white">{{ $order->created_at->format('h:i A') }}</span>
                            </div>
                            @if($order->started_at)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Started:</span>
                                <span class="text-gray-800 dark:text-white">{{ $order->started_at->format('h:i A') }}</span>
                            </div>
                            @endif
                            @if($order->ready_at)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Ready:</span>
                                <span class="text-gray-800 dark:text-white">{{ $order->ready_at->format('h:i A') }}</span>
                            </div>
                            @endif
                            @if($order->completed_at)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Completed:</span>
                                <span class="text-gray-800 dark:text-white">{{ $order->completed_at->format('h:i A') }}</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Order Information -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Order Type</span>
                            <span class="font-medium text-gray-800 dark:text-white">
                                {{ ucfirst(str_replace('_', ' ', $order->order_type)) }}
                            </span>
                        </div>
                        
                        @if($order->table_number)
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Table Number</span>
                            <span class="font-medium text-gray-800 dark:text-white">{{ $order->table_number }}</span>
                        </div>
                        @endif
                        
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Estimated Time</span>
                            <span class="font-medium text-gray-800 dark:text-white">{{ $order->estimated_time }} minutes</span>
                        </div>
                        
                        @if($order->staff)
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Order Taken By</span>
                            <span class="font-medium text-gray-800 dark:text-white">{{ $order->staff->name }}</span>
                        </div>
                        @endif
                        
                        @if($order->cook)
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Prepared By</span>
                            <span class="font-medium text-gray-800 dark:text-white">{{ $order->cook->name }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Status Actions -->
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm">
                <div class="p-6">
                    <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Update Status</h4>
                    
                    <div class="space-y-3">
                        @if($order->status === 'pending')
                        @can('update', $order)
                        <form action="{{ route('kitchen.update-status', $order) }}" method="POST" class="w-full">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="preparing">
                            <button type="submit" class="w-full flex items-center justify-center p-3 bg-warning text-white rounded hover:bg-warning/90">
                                <i class="fas fa-play mr-2"></i>
                                <span>Start Preparation</span>
                            </button>
                        </form>
                        @endcan
                        @endif
                        
                        @if($order->status === 'preparing')
                        @can('update', $order)
                        <form action="{{ route('kitchen.update-status', $order) }}" method="POST" class="w-full">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="ready">
                            <button type="submit" class="w-full flex items-center justify-center p-3 bg-success text-white rounded hover:bg-success/90">
                                <i class="fas fa-check-circle mr-2"></i>
                                <span>Mark as Ready</span>
                            </button>
                        </form>
                        @endcan
                        @endif
                        
                        @if($order->status === 'ready')
                        @can('update', $order)
                        <form action="{{ route('kitchen.update-status', $order) }}" method="POST" class="w-full">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="completed">
                            <button type="submit" class="w-full flex items-center justify-center p-3 bg-info text-white rounded hover:bg-info/90">
                                <i class="fas fa-check-double mr-2"></i>
                                <span>Mark as Served</span>
                            </button>
                        </form>
                        @endcan
                        @endif
                        
                        @if(in_array($order->status, ['pending', 'preparing']))
                        @can('update', $order)
                        <form action="{{ route('kitchen.update-status', $order) }}" method="POST" class="w-full" 
                              onsubmit="return confirm('Are you sure you want to cancel this order?');">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="cancelled">
                            <button type="submit" class="w-full flex items-center justify-center p-3 bg-danger text-white rounded hover:bg-danger/90">
                                <i class="fas fa-times mr-2"></i>
                                <span>Cancel Order</span>
                            </button>
                        </form>
                        @endcan
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Order Items -->
        <div class="col-span-12 lg:col-span-8">
            <!-- Order Items -->
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Order Items</h3>
                    
                    <div class="space-y-4">
                        @foreach($order->items as $item)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        <h4 class="font-medium text-gray-800 dark:text-white">{{ $item->menu_item->name }}</h4>
                                        <span class="ml-2 px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            {{ $item->menu_item->category->name }}
                                        </span>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <span class="text-gray-600 dark:text-gray-400">Quantity:</span>
                                            <span class="ml-2 font-medium text-gray-800 dark:text-white">{{ $item->quantity }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-600 dark:text-gray-400">Price:</span>
                                            <span class="ml-2 font-medium text-gray-800 dark:text-white">${{ number_format($item->unit_price, 2) }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-600 dark:text-gray-400">Total:</span>
                                            <span class="ml-2 font-bold text-gray-800 dark:text-white">${{ number_format($item->quantity * $item->unit_price, 2) }}</span>
                                        </div>
                                        @if($item->menu_item->preparation_time)
                                        <div>
                                            <span class="text-gray-600 dark:text-gray-400">Prep Time:</span>
                                            <span class="ml-2 font-medium text-gray-800 dark:text-white">{{ $item->menu_item->preparation_time }} min</span>
                                        </div>
                                        @endif
                                    </div>
                                    
                                    @if($item->special_instructions)
                                    <div class="mt-3 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded">
                                        <div class="flex items-start">
                                            <i class="fas fa-info-circle text-warning mt-1 mr-2"></i>
                                            <span class="text-sm text-gray-800 dark:text-white">{{ $item->special_instructions }}</span>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                
                                <!-- Item Status -->
                                <div class="ml-4 text-right">
                                    <span class="px-2 py-1 text-xs rounded-full 
                                        @if($item->status === 'pending') bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                        @elseif($item->status === 'preparing') bg-warning/10 text-warning
                                        @elseif($item->status === 'ready') bg-success/10 text-success
                                        @else bg-info/10 text-info @endif">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Item Actions -->
                            @if(in_array($order->status, ['pending', 'preparing']))
                            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <div class="flex space-x-2">
                                    @can('update', $order)
                                    @if($item->status === 'pending')
                                    <form action="{{ route('kitchen.update-item-status', $item) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="preparing">
                                        <button type="submit" class="px-3 py-1 bg-warning text-white text-sm rounded hover:bg-warning/90">
                                            Start Item
                                        </button>
                                    </form>
                                    @endif
                                    
                                    @if($item->status === 'preparing')
                                    <form action="{{ route('kitchen.update-item-status', $item) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="ready">
                                        <button type="submit" class="px-3 py-1 bg-success text-white text-sm rounded hover:bg-success/90">
                                            Mark Ready
                                        </button>
                                    </form>
                                    @endif
                                    @endcan
                                </div>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    
                    <!-- Order Summary -->
                    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex justify-between items-center">
                            <div>
                                <h4 class="font-medium text-gray-800 dark:text-white">Order Summary</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $order->items->count() }} items</p>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-bold text-gray-800 dark:text-white">
                                    ${{ number_format($order->total_amount, 2) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Notes -->
            @if($order->notes || $order->special_instructions)
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Order Notes & Instructions</h3>
                    
                    <div class="space-y-4">
                        @if($order->notes)
                        <div>
                            <h4 class="font-medium text-gray-800 dark:text-white mb-2">Order Notes</h4>
                            <p class="text-gray-600 dark:text-gray-400">{{ $order->notes }}</p>
                        </div>
                        @endif
                        
                        @if($order->special_instructions)
                        <div>
                            <h4 class="font-medium text-gray-800 dark:text-white mb-2">Special Instructions</h4>
                            <p class="text-gray-600 dark:text-gray-400">{{ $order->special_instructions }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Preparation Progress -->
            @if($order->status === 'preparing')
            <div class="bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700 shadow-sm">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Preparation Progress</h3>
                    
                    <!-- Overall Progress -->
                    <div class="mb-6">
                        <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-1">
                            <span>Overall Progress</span>
                            <span>{{ $order->progress_percentage }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                            <div class="bg-warning h-3 rounded-full" style="width: {{ $order->progress_percentage }}%"></div>
                        </div>
                    </div>
                    
                    <!-- Time Information -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <p class="text-2xl font-bold text-gray-800 dark:text-white">
                                {{ $order->time_elapsed }}
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Minutes Elapsed</p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <p class="text-2xl font-bold text-gray-800 dark:text-white">
                                {{ $order->estimated_time - $order->time_elapsed }}
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Minutes Remaining</p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <p class="text-2xl font-bold text-gray-800 dark:text-white">
                                {{ $order->estimated_time }}
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Estimated Total</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function printOrder() {
    var printWindow = window.open('', '', 'height=800,width=600');
    printWindow.document.write(`
        <html>
        <head>
            <title>Kitchen Order #{{ $order->order_number }}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .order-number { font-size: 24px; font-weight: bold; margin-bottom: 5px; }
                .order-type { font-size: 14px; color: #666; margin-bottom: 10px; }
                .status { display: inline-block; padding: 5px 10px; border-radius: 4px; font-size: 12px; }
                .status-pending { background: #dbeafe; color: #1d4ed8; }
                .status-preparing { background: #fef3c7; color: #d97706; }
                .status-ready { background: #d1fae5; color: #065f46; }
                .status-completed { background: #dbeafe; color: #1d4ed8; }
                .status-cancelled { background: #fee2e2; color: #dc2626; }
                .section { margin-bottom: 20px; }
                .section-title { font-weight: bold; border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-bottom: 10px; }
                .item { margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
                .item-name { font-weight: bold; }
                .item-details { display: flex; justify-content: space-between; font-size: 14px; }
                .item-instructions { font-size: 12px; color: #d97706; margin-top: 5px; }
                .order-summary { margin-top: 20px; padding-top: 20px; border-top: 2px solid #ddd; }
                .summary-row { display: flex; justify-content: space-between; margin-bottom: 5px; }
                .summary-total { font-size: 18px; font-weight: bold; }
                .timeline { font-size: 12px; color: #666; }
                .timeline-item { margin-bottom: 3px; }
                .no-print { display: none; }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="order-number">Order #{{ $order->order_number }}</div>
                <div class="order-type">{{ ucfirst(str_replace('_', ' ', $order->order_type)) }}</div>
                <div class="status status-{{ $order->status }}">{{ ucfirst($order->status) }}</div>
                @if($order->table_number)
                <div style="margin-top: 10px;">Table: {{ $order->table_number }}</div>
                @endif
                <div style="margin-top: 5px;">Printed: {{ date('Y-m-d H:i') }}</div>
            </div>
            
            <div class="section">
                <div class="section-title">Order Timeline</div>
                <div class="timeline">
                    <div class="timeline-item">Placed: {{ $order->created_at->format('h:i A') }}</div>
                    @if($order->started_at)
                    <div class="timeline-item">Started: {{ $order->started_at->format('h:i A') }}</div>
                    @endif
                    @if($order->ready_at)
                    <div class="timeline-item">Ready: {{ $order->ready_at->format('h:i A') }}</div>
                    @endif
                    @if($order->completed_at)
                    <div class="timeline-item">Completed: {{ $order->completed_at->format('h:i A') }}</div>
                    @endif
                </div>
            </div>
            
            <div class="section">
                <div class="section-title">Order Items</div>
                @foreach($order->items as $item)
                <div class="item">
                    <div class="item-name">{{ $item->menu_item->name }}</div>
                    <div class="item-details">
                        <span>{{ $item->quantity }} x ${{ number_format($item->unit_price, 2) }}</span>
                        <span>${{ number_format($item->quantity * $item->unit_price, 2) }}</span>
                    </div>
                    @if($item->special_instructions)
                    <div class="item-instructions">Note: {{ $item->special_instructions }}</div>
                    @endif
                </div>
                @endforeach
            </div>
            
            <div class="order-summary">
                <div class="summary-row">
                    <span>Total Items:</span>
                    <span>{{ $order->items->count() }}</span>
                </div>
                <div class="summary-row summary-total">
                    <span>Total Amount:</span>
                    <span>${{ number_format($order->total_amount, 2) }}</span>
                </div>
            </div>
            
            @if($order->notes || $order->special_instructions)
            <div class="section">
                <div class="section-title">Notes</div>
                @if($order->notes)
                <div style="margin-bottom: 10px;">{{ $order->notes }}</div>
                @endif
                @if($order->special_instructions)
                <div>{{ $order->special_instructions }}</div>
                @endif
            </div>
            @endif
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}
</script>
@endpush