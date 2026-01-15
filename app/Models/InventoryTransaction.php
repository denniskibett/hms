<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_item_id',
        'type', // 'in', 'out', 'adjustment', 'transfer', 'damage', 'expired'
        'quantity',
        'unit_cost',
        'total_value',
        'reference_type', // 'purchase_order', 'task', 'manual', 'adjustment'
        'reference_id',
        'notes',
        'created_by',
        'location_from',
        'location_to',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_value' => 'decimal:2',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Inventory item for this transaction.
     */
    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    /**
     * User who created this transaction.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ==================== SCOPES ====================

    /**
     * Scope for incoming transactions.
     */
    public function scopeIncoming($query)
    {
        return $query->where('type', 'in');
    }

    /**
     * Scope for outgoing transactions.
     */
    public function scopeOutgoing($query)
    {
        return $query->where('type', 'out');
    }

    /**
     * Scope for today's transactions.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope by reference type.
     */
    public function scopeByReference($query, $type, $id = null)
    {
        $query = $query->where('reference_type', $type);
        
        if ($id) {
            $query->where('reference_id', $id);
        }
        
        return $query;
    }

    // ==================== ATTRIBUTES ====================

    /**
     * Get transaction type options.
     */
    public static function getTypeOptions()
    {
        return [
            'in' => 'Stock In',
            'out' => 'Stock Out',
            'adjustment' => 'Adjustment',
            'transfer' => 'Transfer',
            'damage' => 'Damage',
            'expired' => 'Expired',
            'return' => 'Return',
        ];
    }

    /**
     * Get reference type options.
     */
    public static function getReferenceTypeOptions()
    {
        return [
            'purchase_order' => 'Purchase Order',
            'task' => 'Task',
            'manual' => 'Manual Entry',
            'adjustment' => 'Stock Adjustment',
            'transfer' => 'Location Transfer',
            'return' => 'Supplier Return',
        ];
    }

    /**
     * Get reference model instance.
     */
    public function getReferenceAttribute()
    {
        if (!$this->reference_type || !$this->reference_id) {
            return null;
        }
        
        $models = [
            'purchase_order' => PurchaseOrder::class,
            'task' => Task::class,
        ];
        
        if (isset($models[$this->reference_type])) {
            return $models[$this->reference_type]::find($this->reference_id);
        }
        
        return null;
    }
}