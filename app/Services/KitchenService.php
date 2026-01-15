<?php

namespace App\Services;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\MenuItemVariant;
use App\Models\KitchenOrder;
use App\Models\KitchenOrderItem;
use App\Models\InventoryItem;
use App\Models\Task;
use App\Models\TaskType;
use App\Services\CoreService;
use App\Services\FinanceService;
use Illuminate\Support\Facades\DB;

class KitchenService
{
    public function __construct(
        private CoreService $coreService,
        private FinanceService $financeService,
        private OperationsService $operationsService
    ) {}
    
    // ==================== MENU MANAGEMENT ====================
    
    /**
     * Create menu category.
     */
    public function createMenuCategory(array $data): MenuCategory
    {
        $category = MenuCategory::create([
            'name' => $data['name'],
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $data['is_active'] ?? true,
        ]);
        
        $this->coreService->log(auth()->id(), 'menu_category_created', 
            "Menu category created: {$category->name}");
        
        return $category;
    }
    
    /**
     * Create menu item.
     */
    public function createMenuItem(array $data): MenuItem
    {
        $menuItem = MenuItem::create([
            'name' => $data['name'],
            'category_id' => $data['category_id'],
            'description' => $data['description'] ?? null,
            'image_url' => $data['image_url'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
        
        // Add variants if provided
        if (isset($data['variants']) && is_array($data['variants'])) {
            foreach ($data['variants'] as $variantData) {
                $this->createMenuItemVariant($menuItem, $variantData);
            }
        }
        
        $this->coreService->log(auth()->id(), 'menu_item_created', 
            "Menu item created: {$menuItem->name}");
        
        return $menuItem->fresh()->load(['category', 'variants']);
    }
    
    /**
     * Create menu item variant.
     */
    public function createMenuItemVariant(MenuItem $menuItem, array $data): MenuItemVariant
    {
        $variant = MenuItemVariant::create([
            'menu_item_id' => $menuItem->id,
            'name' => $data['name'],
            'price' => $data['price'],
            'preparation_time' => $data['preparation_time'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
        
        return $variant;
    }
    
    /**
     * Update menu item availability.
     */
    public function updateMenuItemAvailability(MenuItem $menuItem, bool $isAvailable): MenuItem
    {
        $menuItem->update(['is_active' => $isAvailable]);
        
        $status = $isAvailable ? 'available' : 'unavailable';
        $this->coreService->log(auth()->id(), 'menu_item_availability_updated', 
            "Menu item availability: {$menuItem->name} -> {$status}");
        
        return $menuItem->fresh();
    }
    
    /**
     * Get active menu with categories.
     */
    public function getActiveMenu(): array
    {
        $categories = MenuCategory::with(['activeItems.activeVariants'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
        
        // Transform for frontend
        $menu = $categories->map(function ($category) {
            return [
                'category' => $category,
                'items' => $category->activeItems->map(function ($item) {
                    return [
                        'item' => $item,
                        'price_range' => $item->price_range,
                        'variants' => $item->activeVariants,
                    ];
                }),
            ];
        });
        
        return $menu->toArray();
    }
    
    // ==================== ORDER MANAGEMENT ====================
    
    /**
     * Create kitchen order.
     */
    public function createOrder(array $data): KitchenOrder
    {
        return DB::transaction(function () use ($data) {
            $order = KitchenOrder::create([
                'stay_id' => $data['stay_id'],
                'order_number' => KitchenOrder::generateOrderNumber(),
                'type' => $data['type'] ?? 'room_service',
                'status' => 'pending',
                'special_instructions' => $data['special_instructions'] ?? null,
                'placed_by' => auth()->id(),
            ]);
            
            // Add order items
            $totalAmount = 0;
            foreach ($data['items'] as $itemData) {
                $orderItem = $this->addOrderItem($order, $itemData);
                $totalAmount += $orderItem->total;
            }
            
            // Add to stay invoice if exists
            if ($order->stay) {
                $invoice = $order->stay->currentInvoice();
                if ($invoice) {
                    $invoice->items()->create([
                        'description' => "Food Order #{$order->order_number}",
                        'quantity' => 1,
                        'unit_price' => $totalAmount,
                        'source_type' => 'food',
                        'reference_id' => $order->id,
                    ]);
                    $this->financeService->calculateInvoiceTotals($invoice);
                }
            }
            
            // Create kitchen task
            $this->createKitchenTask($order);
            
            $this->coreService->log(auth()->id(), 'kitchen_order_created', 
                "Kitchen order created: {$order->order_number}");
            
            return $order->fresh()->load(['stay', 'items.variant.menuItem']);
        });
    }
    
    /**
     * Add item to kitchen order.
     */
    public function addOrderItem(KitchenOrder $order, array $itemData): KitchenOrderItem
    {
        $variant = MenuItemVariant::findOrFail($itemData['variant_id']);
        
        $orderItem = KitchenOrderItem::create([
            'order_id' => $order->id,
            'menu_item_variant_id' => $variant->id,
            'quantity' => $itemData['quantity'],
            'price_at_order' => $variant->price,
            'status' => 'pending',
            'notes' => $itemData['notes'] ?? null,
        ]);
        
        // Deduct inventory if the menu item uses ingredients
        $this->deductIngredientsForMenuItem($variant->menuItem, $itemData['quantity']);
        
        return $orderItem;
    }
    
    /**
     * Deduct ingredients for menu item preparation.
     */
    private function deductIngredientsForMenuItem(MenuItem $menuItem, int $quantity): void
    {
        // This would typically reference a recipe/ingredients table
        // For now, we'll use a simplified approach
        
        $ingredientMap = [
            'Coffee' => ['coffee_beans' => 0.01, 'sugar' => 0.005, 'milk' => 0.1],
            'Tea' => ['tea_leaves' => 0.005, 'sugar' => 0.005, 'milk' => 0.05],
            'Sandwich' => ['bread' => 2, 'cheese' => 0.05, 'tomato' => 0.1, 'lettuce' => 0.05],
            // Add more mappings as needed
        ];
        
        $ingredients = $ingredientMap[$menuItem->name] ?? [];
        
        foreach ($ingredients as $itemName => $amountPerServing) {
            $inventoryItem = InventoryItem::where('name', 'like', "%{$itemName}%")->first();
            if ($inventoryItem) {
                $totalAmount = $amountPerServing * $quantity;
                try {
                    $this->operationsService->updateInventoryStock(
                        $inventoryItem,
                        'subtract',
                        $totalAmount,
                        "Used for {$menuItem->name} x{$quantity}"
                    );
                } catch (\Exception $e) {
                    // Log but continue - might need to handle out-of-stock differently
                    \Log::warning("Insufficient {$itemName} for {$menuItem->name}: " . $e->getMessage());
                }
            }
        }
    }

    public function getPreparingOrdersCount(): int
    {
        return KitchenOrder::where('status', 'preparing')->count();
    }




    
    /**
     * Create kitchen task for order preparation.
     */
    private function createKitchenTask(KitchenOrder $order): void
    {
        $taskType = TaskType::where('code', 'KITCHEN_ORDER')->first();
        
        if ($taskType) {
            Task::create([
                'task_type_id' => $taskType->id,
                'title' => "Prepare Order #{$order->order_number}",
                'description' => "Kitchen order for " . ($order->stay ? "Stay #{$order->stay->id}" : "Takeaway"),
                'priority' => 'high',
                'status' => 'pending',
                'due_date' => now()->addMinutes(30), // Standard preparation time
                'department_id' => $taskType->department_id,
                'created_by' => auth()->id(),
                'notes' => "Order type: {$order->type}\nItems: " . $order->items->count(),
            ]);
        }
    }
    
    /**
     * Update order status.
     */
    public function updateOrderStatus(KitchenOrder $order, string $status, string $notes = null): KitchenOrder
    {
        $oldStatus = $order->status;
        
        $updateData = ['status' => $status];
        
        // Set timestamps based on status
        switch ($status) {
            case 'preparing':
                $updateData['preparation_started_at'] = now();
                break;
            case 'ready':
                $updateData['ready_at'] = now();
                break;
            case 'delivered':
                $updateData['delivered_at'] = now();
                break;
        }
        
        if ($notes) {
            $updateData['special_instructions'] = $notes;
        }
        
        $order->update($updateData);
        
        $this->coreService->log(auth()->id(), 'kitchen_order_status_updated', 
            "Kitchen order status: #{$order->order_number} {$oldStatus} -> {$status}");
        
        return $order->fresh();
    }
    
    /**
     * Update order item status.
     */
    public function updateOrderItemStatus(KitchenOrderItem $orderItem, string $status): KitchenOrderItem
    {
        $orderItem->update(['status' => $status]);
        
        // Check if all items are ready/delivered
        $order = $orderItem->order;
        $allItemsReady = $order->items()->where('status', '!=', 'delivered')->count() === 0;
        
        if ($allItemsReady && $order->status !== 'delivered') {
            $this->updateOrderStatus($order, 'ready');
        }
        
        return $orderItem->fresh();
    }
    
    /**
     * Get orders with filters.
     */
    public function getOrders(array $filters = [])
    {
        $query = KitchenOrder::with(['stay.guest', 'items.variant.menuItem', 'placer']);
        
        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        
        if (isset($filters['stay_id'])) {
            $query->where('stay_id', $filters['stay_id']);
        }
        
        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }
        
        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('order_number', 'like', "%{$filters['search']}%")
                  ->orWhereHas('stay', function ($q2) use ($filters) {
                      $q2->whereHas('guest', function ($q3) use ($filters) {
                          $q3->where('name', 'like', "%{$filters['search']}%");
                      });
                  });
            });
        }
        
        // Ordering
        $orderBy = $filters['order_by'] ?? 'created_at';
        $orderDir = $filters['order_dir'] ?? 'desc';
        $query->orderBy($orderBy, $orderDir);
        
        return $query->paginate($filters['per_page'] ?? 50);
    }
    

    public function getPendingOrdersCount(): array
    {
        return [
            'pending' => KitchenOrder::where('status', 'pending')->count(),
            'preparing' => KitchenOrder::where('status', 'preparing')->count(),
            'ready' => KitchenOrder::where('status', 'ready')->count(),
            'completed' => KitchenOrder::where('status', 'delivered')->count(),
            'total_today' => KitchenOrder::whereDate('created_at', today())->count(),
        ];
    }


    // Get pending orders
    public function getPendingOrders()
    {
        return KitchenOrder::with(['stay.guest', 'items.variant.menuItem', 'placer'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    // Get preparing orders
    public function getPreparingOrders()
    {
        return KitchenOrder::with(['stay.guest', 'items.variant.menuItem', 'placer'])
            ->where('status', 'preparing')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    // Get completed orders (delivered), optionally filter by date
    public function getCompletedOrdersCount($date = null): int
    {
        $query = KitchenOrder::where('status', 'delivered');

        if ($date) {
            $query->whereDate('created_at', $date);
        }

        return $query->count();
    }

    
    /**
     * Cancel kitchen order.
     */
    public function cancelOrder(KitchenOrder $order, string $reason): KitchenOrder
    {
        return DB::transaction(function () use ($order, $reason) {
            $order->update([
                'status' => 'cancelled',
                'special_instructions' => $reason,
            ]);
            
            // Remove from invoice if exists
            if ($order->stay) {
                $invoice = $order->stay->currentInvoice();
                if ($invoice) {
                    $invoice->items()
                        ->where('source_type', 'food')
                        ->where('reference_id', $order->id)
                        ->delete();
                    $this->financeService->calculateInvoiceTotals($invoice);
                }
            }
            
            // Return ingredients to inventory
            foreach ($order->items as $item) {
                $this->returnIngredientsForMenuItem($item->variant->menuItem, $item->quantity);
            }
            
            $this->coreService->log(auth()->id(), 'kitchen_order_cancelled', 
                "Kitchen order cancelled: #{$order->order_number} - Reason: {$reason}");
            
            return $order->fresh();
        });
    }
    
    /**
     * Return ingredients to inventory.
     */
    private function returnIngredientsForMenuItem(MenuItem $menuItem, int $quantity): void
    {
        $ingredientMap = [
            'Coffee' => ['coffee_beans' => 0.01, 'sugar' => 0.005, 'milk' => 0.1],
            'Tea' => ['tea_leaves' => 0.005, 'sugar' => 0.005, 'milk' => 0.05],
            'Sandwich' => ['bread' => 2, 'cheese' => 0.05, 'tomato' => 0.1, 'lettuce' => 0.05],
        ];
        
        $ingredients = $ingredientMap[$menuItem->name] ?? [];
        
        foreach ($ingredients as $itemName => $amountPerServing) {
            $inventoryItem = InventoryItem::where('name', 'like', "%{$itemName}%")->first();
            if ($inventoryItem) {
                $totalAmount = $amountPerServing * $quantity;
                $this->operationsService->updateInventoryStock(
                    $inventoryItem,
                    'add',
                    $totalAmount,
                    "Returned from cancelled {$menuItem->name} order"
                );
            }
        }
    }
    
    // ==================== KITCHEN INVENTORY ====================
    
    /**
     * Get kitchen inventory status.
     */
    public function getKitchenInventoryStatus(): array
    {
        $kitchenItems = InventoryItem::where('item_type', 'kitchen')
            ->orWhere('item_type', 'food')
            ->orWhere('item_type', 'beverage')
            ->get();
        
        $lowStock = $kitchenItems->filter(function ($item) {
            return $item->needs_reorder;
        });
        
        $outOfStock = $kitchenItems->filter(function ($item) {
            return $item->quantity <= 0;
        });
        
        return [
            'total_items' => $kitchenItems->count(),
            'low_stock' => $lowStock->values(),
            'out_of_stock' => $outOfStock->values(),
            'stock_value' => $kitchenItems->sum('stock_value'),
            'by_category' => $kitchenItems->groupBy('item_type')->map(function ($items) {
                return [
                    'count' => $items->count(),
                    'stock_value' => $items->sum('stock_value'),
                ];
            }),
        ];
    }
    
    /**
     * Generate kitchen report.
     */
    public function generateKitchenReport($startDate, $endDate): array
    {
        $orders = KitchenOrder::whereBetween('created_at', [$startDate, $endDate])
            ->with(['items.variant.menuItem'])
            ->get();
        
        $totalRevenue = $orders->sum(function ($order) {
            return $order->items->sum('total');
        });
        
        $popularItems = collect();
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $key = $item->variant->menuItem->name . ' - ' . $item->variant->name;
                if (!isset($popularItems[$key])) {
                    $popularItems[$key] = [
                        'item' => $item->variant->menuItem,
                        'variant' => $item->variant,
                        'quantity' => 0,
                        'revenue' => 0,
                    ];
                }
                $popularItems[$key]['quantity'] += $item->quantity;
                $popularItems[$key]['revenue'] += $item->total;
            }
        }
        
        $popularItems = $popularItems->sortByDesc('quantity')->take(10);
        
        return [
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
            'summary' => [
                'total_orders' => $orders->count(),
                'total_items' => $orders->sum(function ($order) {
                    return $order->items->count();
                }),
                'total_revenue' => $totalRevenue,
                'by_type' => $orders->groupBy('type')->map->count(),
                'by_status' => $orders->groupBy('status')->map->count(),
            ],
            'popular_items' => $popularItems->values(),
            'average_order_value' => $orders->count() > 0 ? $totalRevenue / $orders->count() : 0,
            'busiest_time' => $this->getBusiestTime($orders),
        ];
    }
    
    /**
     * Get busiest time from orders.
     */
    private function getBusiestTime($orders): array
    {
        $hours = array_fill(0, 24, 0);
        
        foreach ($orders as $order) {
            $hour = $order->created_at->hour;
            $hours[$hour]++;
        }
        
        $maxOrders = max($hours);
        $busiestHour = array_search($maxOrders, $hours);
        
        return [
            'hour' => $busiestHour,
            'orders' => $maxOrders,
            'distribution' => $hours,
        ];
    }
    
    /**
     * Create quick purchase order for low stock items.
     */
    public function createQuickPurchaseOrderForKitchen(): ?PurchaseOrder
    {
        $lowStockItems = InventoryItem::whereIn('item_type', ['kitchen', 'food', 'beverage'])
            ->whereColumn('quantity', '<=', 'reorder_level')
            ->where('is_active', true)
            ->get();
        
        if ($lowStockItems->isEmpty()) {
            return null;
        }
        
        // Find primary supplier for kitchen items
        $supplier = Supplier::where('supplier_type', 'food')->first();
        
        if (!$supplier) {
            return null;
        }
        
        $orderData = [
            'supplier_id' => $supplier->id,
            'notes' => 'Automatic reorder for low stock kitchen items',
            'items' => [],
        ];
        
        foreach ($lowStockItems as $item) {
            $quantityNeeded = max($item->reorder_level * 2 - $item->quantity, 10);
            
            $orderData['items'][] = [
                'inventory_item_id' => $item->id,
                'quantity' => $quantityNeeded,
                'unit_price' => $item->currentSupplierPrice($supplier->id)?->unit_price ?? $item->unit_cost,
            ];
        }
        
        return $this->operationsService->createPurchaseOrder($orderData);
    }
}