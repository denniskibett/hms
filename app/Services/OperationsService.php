<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\SupplierPrice;
use App\Models\InventoryTransaction;
use App\Services\CoreService;
use App\Services\FinanceService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OperationsService
{
    public function __construct(
        private CoreService $coreService,
        private FinanceService $financeService
    ) {}
    
    // ==================== INVENTORY ====================
    
    /**
     * Create new inventory item.
     */
    public function createInventoryItem(array $data): InventoryItem
    {
        $inventoryItem = InventoryItem::create([
            'name' => $data['name'],
            'sku' => $data['sku'] ?? 'ITM-' . strtoupper(uniqid()),
            'description' => $data['description'] ?? null,
            'unit_of_measure' => $data['unit_of_measure'],
            'quantity' => $data['quantity'] ?? 0,
            'reorder_level' => $data['reorder_level'] ?? 10,
            'unit_cost' => $data['unit_cost'] ?? 0,
            'item_type' => $data['item_type'] ?? 'other',
            'category' => $data['category'] ?? 'consumable',
            'is_active' => true,
            'primary_supplier_id' => $data['primary_supplier_id'] ?? null,
        ]);
        
        $this->coreService->log(auth()->id(), 'inventory_item_created', 
            "Inventory item created: {$inventoryItem->name}");
        
        return $inventoryItem;
    }
    
    /**
     * Update inventory stock.
     */
    public function updateInventoryStock(InventoryItem $item, string $operation, float $quantity, string $reason, array $details = []): InventoryTransaction
    {
        return DB::transaction(function () use ($item, $operation, $quantity, $reason, $details) {
            $oldQuantity = $item->quantity;
            
            if ($operation === 'add') {
                $item->quantity += $quantity;
            } elseif ($operation === 'subtract') {
                if ($item->quantity < $quantity) {
                    throw new \Exception("Cannot subtract {$quantity}. Available: {$item->quantity}");
                }
                $item->quantity -= $quantity;
            } elseif ($operation === 'set') {
                $item->quantity = $quantity;
            }
            
            $item->save();
            
            // Record transaction
            $transaction = InventoryTransaction::create([
                'inventory_item_id' => $item->id,
                'type' => $operation === 'add' ? 'in' : 'out',
                'quantity' => $quantity,
                'unit_cost' => $item->unit_cost,
                'total_value' => $quantity * $item->unit_cost,
                'reference_type' => $details['reference_type'] ?? 'manual',
                'reference_id' => $details['reference_id'] ?? null,
                'notes' => $reason,
                'created_by' => auth()->id(),
            ]);
            
            $this->coreService->log(auth()->id(), 'inventory_updated', 
                "Inventory {$item->name}: {$oldQuantity} → {$item->quantity} ({$reason})");
            
            return $transaction;
        });
    }
    
    /**
     * Create purchase order.
     */
    public function createPurchaseOrder(array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($data) {
            $purchaseOrder = PurchaseOrder::create([
                'supplier_id' => $data['supplier_id'],
                'po_number' => PurchaseOrder::generatePONumber(),
                'requested_by' => auth()->id(),
                'status' => 'draft',
                'notes' => $data['notes'] ?? null,
                'delivery_date' => $data['delivery_date'] ?? null,
            ]);
            
            // Add items if provided
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $itemData) {
                    $this->addItemToPurchaseOrder($purchaseOrder, $itemData);
                }
            }
            
            // Calculate total
            $purchaseOrder->update(['total' => $purchaseOrder->calculateTotal()]);
            
            $this->coreService->log(auth()->id(), 'purchase_order_created', 
                "Purchase order created: {$purchaseOrder->po_number}");
            
            return $purchaseOrder->fresh()->load(['supplier', 'items.inventoryItem']);
        });
    }
    
    /**
     * Add item to purchase order.
     */
    public function addItemToPurchaseOrder(PurchaseOrder $purchaseOrder, array $itemData): PurchaseOrderItem
    {
        $inventoryItem = InventoryItem::findOrFail($itemData['inventory_item_id']);
        
        $item = PurchaseOrderItem::create([
            'purchase_order_id' => $purchaseOrder->id,
            'inventory_item_id' => $inventoryItem->id,
            'quantity' => $itemData['quantity'],
            'unit_price' => $itemData['unit_price'] ?? $inventoryItem->currentSupplierPrice($purchaseOrder->supplier_id)?->unit_price ?? $inventoryItem->unit_cost,
        ]);
        
        return $item;
    }
    
    /**
     * Submit purchase order for approval.
     */
    public function submitPurchaseOrder(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        $purchaseOrder->update(['status' => 'submitted']);
        
        $this->coreService->log(auth()->id(), 'purchase_order_submitted', 
            "Purchase order submitted: {$purchaseOrder->po_number}");
        
        // Notify approver (manager/procurement head)
        // Implementation depends on your notification system
        
        return $purchaseOrder->fresh();
    }
    
    /**
     * Approve purchase order.
     */
    public function approvePurchaseOrder(PurchaseOrder $purchaseOrder, User $approver): PurchaseOrder
    {
        $purchaseOrder->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);
        
        $this->coreService->log($approver->id, 'purchase_order_approved', 
            "Purchase order approved: {$purchaseOrder->po_number}");
        
        return $purchaseOrder->fresh();
    }
    
    /**
     * Mark purchase order as received.
     */
    public function receivePurchaseOrder(PurchaseOrder $purchaseOrder, array $receivedItems = []): PurchaseOrder
    {
        return DB::transaction(function () use ($purchaseOrder, $receivedItems) {
            $purchaseOrder->update([
                'status' => 'received',
                'received_at' => now(),
            ]);
            
            // Update inventory for each item
            foreach ($purchaseOrder->items as $item) {
                $receivedQty = $receivedItems[$item->id] ?? $item->quantity;
                
                if ($receivedQty > 0) {
                    $this->updateInventoryStock(
                        $item->inventoryItem,
                        'add',
                        $receivedQty,
                        "Received from PO {$purchaseOrder->po_number}",
                        ['reference_type' => 'purchase_order', 'reference_id' => $purchaseOrder->id]
                    );
                }
            }
            
            // Create expense for the purchase
            $this->financeService->recordExpense([
                'description' => "Purchase from {$purchaseOrder->supplier->name} - PO {$purchaseOrder->po_number}",
                'amount' => $purchaseOrder->total,
                'category' => 'purchases',
                'payment_method' => 'pending', // Will be paid later
                'paid_to' => $purchaseOrder->supplier->name,
                'reference_number' => $purchaseOrder->po_number,
            ]);
            
            $this->coreService->log(auth()->id(), 'purchase_order_received', 
                "Purchase order received: {$purchaseOrder->po_number}");
            
            return $purchaseOrder->fresh();
        });
    }
    
    /**
     * Get low stock items.
     */
    public function getLowStockItems(): array
    {
        return InventoryItem::whereColumn('quantity', '<=', 'reorder_level')
            ->where('is_active', true)
            ->get()
            ->map(function ($item) {
                return [
                    'item' => $item,
                    'status' => $item->quantity <= 0 ? 'out_of_stock' : 'low_stock',
                    'needed' => max(0, $item->reorder_level - $item->quantity),
                ];
            })
            ->toArray();
    }
    
    /**
     * Get inventory transactions.
     */
    public function getInventoryTransactions(array $filters = [])
    {
        $query = InventoryTransaction::with(['inventoryItem', 'creator']);
        
        if (isset($filters['inventory_item_id'])) {
            $query->where('inventory_item_id', $filters['inventory_item_id']);
        }
        
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        
        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }
        
        if (isset($filters['reference_type'])) {
            $query->where('reference_type', $filters['reference_type']);
        }
        
        return $query->orderBy('created_at', 'desc')
                     ->paginate($filters['per_page'] ?? 50);
    }
}
