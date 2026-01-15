<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'po_number',
        'requested_by',
        'status',
        'total',
        'notes',
        'approved_by',
        'approved_at',
        'ordered_at',
        'received_at',
        'delivery_date',
        'payment_status', // 'pending', 'partial', 'paid'
        'payment_terms',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'approved_at' => 'datetime',
        'ordered_at' => 'datetime',
        'received_at' => 'datetime',
        'delivery_date' => 'date',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Supplier for this purchase order.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * User who requested this order.
     */
    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * User who approved this order.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Items in this purchase order.
     */
    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    // ==================== SCOPES ====================

    /**
     * Scope for pending POs.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'submitted');
    }

    /**
     * Scope for approved POs.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for ordered POs.
     */
    public function scopeOrdered($query)
    {
        return $query->where('status', 'ordered');
    }

    /**
     * Scope for overdue deliveries.
     */
    public function scopeOverdueDelivery($query)
    {
        return $query->where('status', 'ordered')
            ->where('delivery_date', '<', now());
    }

    // ==================== ATTRIBUTES ====================

    /**
     * Generate PO number.
     */
    public static function generatePONumber()
    {
        $prefix = 'PO-' . date('Ymd') . '-';
        $lastPO = self::where('po_number', 'like', $prefix . '%')
            ->orderBy('po_number', 'desc')
            ->first();
        
        if ($lastPO) {
            $lastNumber = intval(substr($lastPO->po_number, -4));
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }
        
        return $prefix . $nextNumber;
    }

    /**
     * Get status options.
     */
    public static function getStatusOptions()
    {
        return [
            'draft' => 'Draft',
            'submitted' => 'Submitted',
            'approved' => 'Approved',
            'ordered' => 'Ordered',
            'received' => 'Received',
            'cancelled' => 'Cancelled',
        ];
    }

    /**
     * Get payment status options.
     */
    public static function getPaymentStatusOptions()
    {
        return [
            'pending' => 'Pending',
            'partial' => 'Partial',
            'paid' => 'Paid',
        ];
    }

    /**
     * Calculate total from items.
     */
    public function calculateTotal()
    {
        return $this->items->sum('total');
    }

    // ==================== METHODS ====================

    /**
     * Submit for approval.
     */
    public function submit()
    {
        $this->status = 'submitted';
        $this->total = $this->calculateTotal();
        $this->save();
        
        return $this;
    }

    /**
     * Approve purchase order.
     */
    public function approve($approvedBy)
    {
        $this->status = 'approved';
        $this->approved_by = $approvedBy;
        $this->approved_at = now();
        $this->save();
        
        return $this;
    }

    /**
     * Mark as ordered.
     */
    public function markAsOrdered()
    {
        $this->status = 'ordered';
        $this->ordered_at = now();
        $this->save();
        
        return $this;
    }

    /**
     * Mark as received.
     */
    public function markAsReceived()
    {
        $this->status = 'received';
        $this->received_at = now();
        $this->save();
        
        // Update inventory quantities and create transactions
        foreach ($this->items as $item) {
            $item->inventoryItem->updateQuantity($item->quantity, 'add', "Received from PO #{$this->po_number}");
            
            // Create inventory transaction
            InventoryTransaction::create([
                'inventory_item_id' => $item->inventory_item_id,
                'type' => 'in',
                'quantity' => $item->quantity,
                'unit_cost' => $item->unit_price,
                'total_value' => $item->total,
                'reference_type' => 'purchase_order',
                'reference_id' => $this->id,
                'notes' => "Received from PO #{$this->po_number}",
                'created_by' => auth()->id(),
            ]);
        }
        
        return $this;
    }

    /**
     * Update payment status.
     */
    public function updatePaymentStatus($status, $amountPaid = null)
    {
        $this->payment_status = $status;
        
        if ($amountPaid) {
            // You could track payment amounts in a separate table
            // For now, just update status
        }
        
        $this->save();
        
        return $this;
    }
}