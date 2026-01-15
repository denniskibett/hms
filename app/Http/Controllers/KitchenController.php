<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KitchenOrder;
use App\Models\MenuCategory;
use App\Services\KitchenService;

class KitchenController extends Controller
{
    public function __construct(
        private KitchenService $kitchenService
    ) {}
    
    /**
     * Display kitchen dashboard with orders overview.
     */
    public function index(Request $request)
    {
        // $this->authorize('viewAny', KitchenOrder::class);
        
        // Get orders for different statuses
        $pendingOrders = $this->kitchenService->getPendingOrders();
        $preparingOrders = $this->kitchenService->getPreparingOrders();
        
        // Get stats
        $stats = $this->kitchenService->getPendingOrdersCount();
        
        // Get all orders for the table view
        $allOrders = $this->kitchenService->getOrders($request->all());
        
        return view('kitchen.index', [
            'orders' => collect([
                'pending' => $pendingOrders,
                'preparing' => $preparingOrders,
                'ready' => KitchenOrder::where('status', 'ready')->get(),
            ]),
            'pendingOrders' => $stats['pending'],
            'preparingOrders' => $stats['preparing'],
            'readyOrders' => $stats['ready'],
            'cancelledOrders' => KitchenOrder::where('status', 'cancelled')->count(),
            'allOrders' => $allOrders,
            'stats' => $stats,
        ]);
    }
    
    /**
     * Display orders list (alternative to index).
     */
    public function orders(Request $request)
    {
        $this->authorize('viewAny', KitchenOrder::class);
        
        $orders = $this->kitchenService->getOrders($request->all());
        
        return view('kitchen.orders.index', compact('orders'));
    }
    
    /**
     * Display single order details.
     */
    public function show(KitchenOrder $order)
    {
        $this->authorize('view', $order);
        
        $order->load(['stay.guest', 'items.variant.menuItem', 'placer']);
        
        // Calculate progress for preparing orders
        if ($order->status === 'preparing' && $order->preparation_started_at) {
            $timeElapsed = now()->diffInMinutes($order->preparation_started_at);
            $progressPercentage = min(100, ($timeElapsed / ($order->estimated_time ?? 30)) * 100);
            
            // Add calculated properties to the order object
            $order->progress_percentage = round($progressPercentage);
            $order->time_elapsed = $timeElapsed;
        }
        
        return view('kitchen.show', compact('order'));
    }
    
    /**
     * Create new kitchen order.
     */
    public function create()
    {
        $this->authorize('create', KitchenOrder::class);
        
        // Get active menu
        $menu = $this->kitchenService->getActiveMenu();
        
        return view('kitchen.create', compact('menu'));
    }
    
    /**
     * Store new kitchen order.
     */
    public function store(Request $request)
    {
        $this->authorize('create', KitchenOrder::class);
        
        $request->validate([
            'stay_id' => 'nullable|exists:stays,id',
            'type' => 'required|in:dine_in,room_service,takeaway',
            'items' => 'required|array',
            'items.*.variant_id' => 'required|exists:menu_item_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string',
            'special_instructions' => 'nullable|string',
        ]);
        
        try {
            $order = $this->kitchenService->createOrder($request->all());
            
            return redirect()->route('kitchen.show', $order)
                ->with('success', 'Order created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating order: ' . $e->getMessage());
        }
    }
    
    /**
     * Edit kitchen order.
     */
    public function edit(KitchenOrder $order)
    {
        $this->authorize('update', $order);
        
        $order->load(['items.variant.menuItem']);
        $menu = $this->kitchenService->getActiveMenu();
        
        return view('kitchen.edit', compact('order', 'menu'));
    }
    
    /**
     * Update kitchen order.
     */
    public function update(Request $request, KitchenOrder $order)
    {
        $this->authorize('update', $order);
        
        $request->validate([
            'type' => 'sometimes|in:dine_in,room_service,takeaway',
            'table_number' => 'nullable|string|max:20',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'estimated_time' => 'nullable|integer|min:1',
            'special_instructions' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
        
        $order->update($request->only([
            'type', 'table_number', 'priority', 'estimated_time', 
            'special_instructions', 'notes'
        ]));
        
        return redirect()->route('kitchen.show', $order)
            ->with('success', 'Order updated successfully.');
    }
    
    /**
     * Update order status.
     */
    public function updateStatus(KitchenOrder $order, Request $request)
    {
        $this->authorize('update', $order);
        
        $request->validate([
            'status' => 'required|in:pending,preparing,ready,delivered,cancelled',
            'notes' => 'nullable|string',
        ]);
        
        try {
            $order = $this->kitchenService->updateOrderStatus(
                $order,
                $request->input('status'),
                $request->input('notes')
            );
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'order' => $order,
                    'status' => $order->status,
                    'status_display' => ucfirst($order->status)
                ]);
            }
            
            return redirect()->route('kitchen.show', $order)
                ->with('success', 'Order status updated successfully.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Error updating order status: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Error updating order status: ' . $e->getMessage());
        }
    }
    
    /**
     * Update order item status.
     */
    public function updateItemStatus(KitchenOrderItem $item, Request $request)
    {
        $this->authorize('update', $item->order);
        
        $request->validate([
            'status' => 'required|in:pending,preparing,ready,delivered',
        ]);
        
        try {
            $item = $this->kitchenService->updateOrderItemStatus(
                $item,
                $request->input('status')
            );
            
            return redirect()->route('kitchen.show', $item->order)
                ->with('success', 'Item status updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating item status: ' . $e->getMessage());
        }
    }
    
    /**
     * Display today's orders.
     */
    public function todaysOrders()
    {
        $this->authorize('viewAny', KitchenOrder::class);
        
        $orders = KitchenOrder::whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->get();
        
        $stats = [
            'total' => $orders->count(),
            'pending' => $orders->where('status', 'pending')->count(),
            'preparing' => $orders->where('status', 'preparing')->count(),
            'ready' => $orders->where('status', 'ready')->count(),
            'completed' => $orders->where('status', 'delivered')->count(),
            'cancelled' => $orders->where('status', 'cancelled')->count(),
        ];
        
        return view('kitchen.todays-orders', compact('orders', 'stats'));
    }
    
    /**
     * Display menu management.
     */
    public function menu()
    {
        $this->authorize('viewMenu', KitchenOrder::class);
        
        $menu = $this->kitchenService->getActiveMenu();
        $categories = MenuCategory::with('items.variants')->get();
        
        return view('kitchen.menu.index', compact('menu', 'categories'));
    }
    
    /**
     * Display kitchen inventory.
     */
    public function inventory()
    {
        $this->authorize('viewKitchenInventory', KitchenOrder::class);
        
        $inventory = $this->kitchenService->getKitchenInventoryStatus();
        
        return view('kitchen.inventory', compact('inventory'));
    }
    
    /**
     * Display kitchen reports.
     */
    public function reports(Request $request)
    {
        $this->authorize('viewKitchenReports', KitchenOrder::class);
        
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate = $request->input('end_date', now()->endOfMonth());
        
        $report = $this->kitchenService->generateKitchenReport($startDate, $endDate);
        
        return view('kitchen.reports', compact('report'));
    }
    
    /**
     * Cancel kitchen order.
     */
    public function cancel(KitchenOrder $order, Request $request)
    {
        $this->authorize('update', $order);
        
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);
        
        try {
            $order = $this->kitchenService->cancelOrder(
                $order,
                $request->input('reason')
            );
            
            return redirect()->route('kitchen.show', $order)
                ->with('success', 'Order cancelled successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error cancelling order: ' . $e->getMessage());
        }
    }
}