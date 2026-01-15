<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskType;
use App\Models\TaskItem;
use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\SupplierPrice;
use App\Models\Facility;
use App\Models\FacilityAllocation;
use App\Models\FacilityPackage;
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
    
    // ==================== TASK MANAGEMENT ====================
    
    /**
     * Create a new task.
     */
    public function createTask(array $data): Task
    {
        return DB::transaction(function () use ($data) {
            $task = Task::create([
                'task_type_id' => $data['task_type_id'],
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'room_id' => $data['room_id'] ?? null,
                'stay_id' => $data['stay_id'] ?? null,
                'facility_id' => $data['facility_id'] ?? null,
                'priority' => $data['priority'] ?? 'medium',
                'status' => 'pending',
                'estimated_minutes' => $data['estimated_minutes'] ?? null,
                'due_date' => $data['due_date'] ?? now()->addDay(),
                'department_id' => $data['department_id'] ?? TaskType::find($data['task_type_id'])->department_id,
                'created_by' => auth()->id(),
                'checklist' => $data['checklist'] ?? TaskType::find($data['task_type_id'])->default_checklist,
                'notes' => $data['notes'] ?? null,
            ]);
            
            // Assign if user provided
            if (isset($data['assigned_to'])) {
                $this->assignTask($task, $data['assigned_to']);
            }
            
            $this->coreService->log(auth()->id(), 'task_created', 
                "Task #{$task->id} created: {$task->title}");
            
            return $task;
        });
    }
    
    /**
     * Assign task to staff member.
     */
    public function assignTask(Task $task, int $userId): Task
    {
        return DB::transaction(function () use ($task, $userId) {
            $oldAssignee = $task->assigned_to;
            
            $task->update([
                'assigned_to' => $userId,
                'status' => 'assigned',
                'assigned_at' => now(),
                'department_id' => $task->department_id ?? User::find($userId)->staffProfile->department_id ?? null,
            ]);
            
            // Send notification
            $assignedUser = User::find($userId);
            if ($assignedUser) {
                $this->coreService->sendNotification($assignedUser, 'task_assigned', [
                    'task_id' => $task->id,
                    'task_title' => $task->title,
                    'due_date' => $task->due_date->format('Y-m-d H:i'),
                    'priority' => $task->priority,
                ]);
            }
            
            $this->coreService->log(auth()->id(), 'task_assigned', 
                "Task #{$task->id} assigned from {$oldAssignee} to {$userId}");
            
            return $task->fresh();
        });
    }
    
    /**
     * Start task execution.
     */
    public function startTask(Task $task, string $notes = null): Task
    {
        $task->update([
            'status' => 'in_progress',
            'started_at' => now(),
            'notes' => $notes ? ($task->notes . "\n" . $notes) : $task->notes,
        ]);
        
        $this->coreService->log(auth()->id(), 'task_started', 
            "Task #{$task->id} started");
        
        return $task->fresh();
    }
    
    /**
     * Complete a task.
     */
    public function completeTask(Task $task, array $data = []): Task
    {
        return DB::transaction(function () use ($task, $data) {
            $task->update([
                'status' => 'completed',
                'completed_at' => now(),
                'actual_minutes' => $data['actual_minutes'] ?? $task->estimated_minutes,
                'notes' => $data['notes'] ? ($task->notes . "\n" . $data['notes']) : $task->notes,
            ]);
            
            // If it's a room cleaning task, update room status
            if ($task->room_id && $task->taskType->category === 'cleaning') {
                $room = $task->room;
                if ($room->status === 'cleaning') {
                    $room->update(['status' => 'available']);
                }
            }
            
            // Record actual cost if provided
            if (isset($data['actual_cost'])) {
                $task->update(['actual_cost' => $data['actual_cost']]);
            }
            
            $this->coreService->log(auth()->id(), 'task_completed', 
                "Task #{$task->id} completed");
            
            return $task->fresh();
        });
    }
    
    /**
     * Verify a completed task.
     */
    public function verifyTask(Task $task, User $verifier, string $notes = null): Task
    {
        $task->update([
            'status' => 'verified',
            'verified_by' => $verifier->id,
            'verified_at' => now(),
            'verification_notes' => $notes,
        ]);
        
        $this->coreService->log($verifier->id, 'task_verified', 
            "Task #{$task->id} verified");
        
        return $task->fresh();
    }
    
    /**
     * Use inventory item in task.
     */
    public function useInventoryInTask(Task $task, int $inventoryItemId, float $quantity, float $unitCost = null): TaskItem
    {
        return DB::transaction(function () use ($task, $inventoryItemId, $quantity, $unitCost) {
            $inventoryItem = InventoryItem::findOrFail($inventoryItemId);
            
            // Check stock availability
            if ($inventoryItem->quantity < $quantity) {
                throw new \Exception("Insufficient stock. Available: {$inventoryItem->quantity}, Required: {$quantity}");
            }
            
            // Create task item record
            $taskItem = TaskItem::create([
                'task_id' => $task->id,
                'inventory_item_id' => $inventoryItemId,
                'quantity_used' => $quantity,
                'unit_cost_at_time' => $unitCost ?? $inventoryItem->unit_cost,
                'notes' => "Used in task #{$task->id}",
            ]);
            
            // Update inventory
            $inventoryItem->updateQuantity($quantity, 'subtract', "Used in task #{$task->id}");
            
            // Record transaction
            InventoryTransaction::create([
                'inventory_item_id' => $inventoryItemId,
                'type' => 'out',
                'quantity' => $quantity,
                'unit_cost' => $unitCost ?? $inventoryItem->unit_cost,
                'total_value' => $quantity * ($unitCost ?? $inventoryItem->unit_cost),
                'reference_type' => 'task',
                'reference_id' => $task->id,
                'notes' => "Used in task #{$task->id}",
                'created_by' => auth()->id(),
            ]);
            
            $this->coreService->log(auth()->id(), 'inventory_used', 
                "Used {$quantity} {$inventoryItem->name} in task #{$task->id}");
            
            return $taskItem;
        });
    }
    
    /**
     * Update task checklist item.
     */
    public function updateChecklistItem(Task $task, int $itemIndex, bool $completed, string $notes = null): Task
    {
        $checklist = $task->checklist ?? [];
        
        if (isset($checklist[$itemIndex])) {
            $checklist[$itemIndex]['completed'] = $completed;
            $checklist[$itemIndex]['completed_at'] = $completed ? now() : null;
            $checklist[$itemIndex]['completed_by'] = $completed ? auth()->id() : null;
            $checklist[$itemIndex]['notes'] = $notes ?? $checklist[$itemIndex]['notes'] ?? null;
            
            $task->update(['checklist' => $checklist]);
        }
        
        return $task->fresh();
    }
    

    public function getTasks(array $filters = [])
    {
        $query = Task::with(['taskType', 'assignedStaff', 'room', 'department']);
        
        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['task_type_id'])) {
            $query->where('task_type_id', $filters['task_type_id']);
        }
        
        if (isset($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }
        
        if (isset($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }
        
        if (isset($filters['room_id'])) {
            $query->where('room_id', $filters['room_id']);
        }
        
        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }
        
        if (isset($filters['date_from'])) {
            $query->whereDate('due_date', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->whereDate('due_date', '<=', $filters['date_to']);
        }
        
        if (isset($filters['overdue'])) {
            $query->where('due_date', '<', now())
                  ->whereNotIn('status', ['completed', 'verified', 'cancelled']);
        }
        
        // Ordering
        $orderBy = $filters['order_by'] ?? 'due_date';
        $orderDir = $filters['order_dir'] ?? 'asc';
        $query->orderBy($orderBy, $orderDir);
        
        return $query->paginate($filters['per_page'] ?? 50);
    }

    public function getTaskStats(): array
    {
        return [
            'total'     => Task::count(),
            'pending'   => Task::where('status', 'pending')->count(),
            'completed' => Task::where('status', 'completed')->count(),
            'overdue'   => Task::where('due_date', '<', now())
                            ->whereNotIn('status', ['completed', 'verified', 'cancelled'])
                            ->count(),
        ];
    }

    
    // ==================== INVENTORY MANAGEMENT ====================
    
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
    
    // ==================== FACILITY MANAGEMENT ====================
    
    /**
     * Create facility booking.
     */
    public function createFacilityBooking(array $data): FacilityAllocation
    {
        return DB::transaction(function () use ($data) {
            $facility = Facility::findOrFail($data['facility_id']);
            
            // Check availability
            if (!$this->isFacilityAvailable($facility, $data['start_time'], $data['end_time'])) {
                throw new \Exception("Facility {$facility->name} is not available for the selected time slot");
            }
            
            $allocation = FacilityAllocation::create([
                'stay_id' => $data['stay_id'] ?? null,
                'facility_id' => $facility->id,
                'package_id' => $data['package_id'] ?? null,
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'rate_applied' => $data['rate'] ?? ($data['package_id'] 
                    ? FacilityPackage::find($data['package_id'])->price 
                    : $facility->base_rate),
                'status' => 'booked',
            ]);
            
            // If linked to stay, add to invoice
            if ($allocation->stay_id) {
                $invoice = $allocation->stay->currentInvoice();
                if ($invoice) {
                    $invoice->items()->create([
                        'description' => "Facility: {$facility->name}",
                        'quantity' => $allocation->duration_hours,
                        'unit_price' => $allocation->rate_applied,
                        'source_type' => 'facility',
                        'reference_id' => $allocation->id,
                    ]);
                    $this->financeService->calculateInvoiceTotals($invoice);
                }
            }
            
            $this->coreService->log(auth()->id(), 'facility_booked', 
                "Facility {$facility->name} booked from {$data['start_time']} to {$data['end_time']}");
            
            return $allocation->fresh()->load(['facility', 'stay']);
        });
    }
    
    /**
     * Check facility availability.
     */
    public function isFacilityAvailable(Facility $facility, $startTime, $endTime): bool
    {
        $conflictingBooking = FacilityAllocation::where('facility_id', $facility->id)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                      ->orWhereBetween('end_time', [$startTime, $endTime])
                      ->orWhere(function ($q) use ($startTime, $endTime) {
                          $q->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                      });
            })
            ->whereIn('status', ['booked', 'confirmed', 'in_use'])
            ->exists();
        
        return !$conflictingBooking && $facility->status === 'available';
    }
    
    /**
     * Confirm facility booking.
     */
    public function confirmFacilityBooking(FacilityAllocation $allocation): FacilityAllocation
    {
        $allocation->update(['status' => 'confirmed']);
        
        // Update facility status if needed
        if ($allocation->start_time <= now() && $allocation->end_time >= now()) {
            $allocation->facility->update(['status' => 'in_use']);
        }
        
        $this->coreService->log(auth()->id(), 'facility_confirmed', 
            "Facility booking #{$allocation->id} confirmed");
        
        return $allocation->fresh();
    }
    
    /**
     * Get available facilities for time slot.
     */
    public function getAvailableFacilities($startTime, $endTime, $capacity = null, $facilityType = null)
    {
        $query = Facility::where('status', 'available');
        
        if ($capacity) {
            $query->where('capacity', '>=', $capacity);
        }
        
        if ($facilityType) {
            $query->where('type', $facilityType);
        }
        
        $facilities = $query->get();
        
        // Filter by availability
        return $facilities->filter(function ($facility) use ($startTime, $endTime) {
            return $this->isFacilityAvailable($facility, $startTime, $endTime);
        });
    }
    
    /**
     * Update facility status.
     */
    public function updateFacilityStatus(Facility $facility, string $status, string $reason = null): Facility
    {
        $oldStatus = $facility->status;
        $facility->update(['status' => $status]);
        
        $this->coreService->log(auth()->id(), 'facility_status_updated', 
            "Facility {$facility->name} status: {$oldStatus} → {$status}" . ($reason ? " ({$reason})" : ''));
        
        return $facility->fresh();
    }
}