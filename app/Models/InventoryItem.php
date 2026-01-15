<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'unit_of_measure',
        'quantity',
        'reorder_level',
        'unit_cost',
        'primary_supplier_id',
        'item_type', // 'cleaning', 'kitchen', 'office', 'maintenance', 'other'
        'category', // 'consumable', 'non_consumable'
        'minimum_stock',
        'maximum_stock',
        'location',
        'is_active',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'reorder_level' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'minimum_stock' => 'decimal:2',
        'maximum_stock' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'stock_status',
        'needs_reorder',
        'stock_value',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Primary supplier for this item.
     */
    public function primarySupplier()
    {
        return $this->belongsTo(Supplier::class, 'primary_supplier_id');
    }

    /**
     * Supplier prices for this item.
     */
    public function supplierPrices()
    {
        return $this->hasMany(SupplierPrice::class);
    }

    /**
     * Purchase order items for this item.
     */
    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /**
     * Task items usage (for cleaning items).
     */
    public function taskItems()
    {
        return $this->hasMany(TaskItem::class);
    }

    /**
     * Kitchen order items usage (for kitchen items).
     */
    public function kitchenOrderItems()
    {
        return $this->hasMany(KitchenOrderItem::class, 'inventory_item_id');
    }

    // ==================== SCOPES ====================

    /**
     * Scope for cleaning items.
     */
    public function scopeCleaningItems($query)
    {
        return $query->where('item_type', 'cleaning');
    }

    /**
     * Scope for kitchen items.
     */
    public function scopeKitchenItems($query)
    {
        return $query->where('item_type', 'kitchen');
    }

    /**
     * Scope for active items.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for low stock items.
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<=', 'reorder_level');
    }

    /**
     * Scope for out of stock items.
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', '<=', 0);
    }

    /**
     * Scope by item type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('item_type', $type);
    }

    // ==================== ATTRIBUTES ====================

    /**
     * Get stock status.
     */
    public function getStockStatusAttribute()
    {
        if ($this->quantity <= 0) {
            return 'out_of_stock';
        } elseif ($this->quantity <= $this->reorder_level) {
            return 'low_stock';
        } elseif ($this->quantity >= $this->maximum_stock) {
            return 'over_stock';
        } else {
            return 'in_stock';
        }
    }

    /**
     * Check if needs reorder.
     */
    public function getNeedsReorderAttribute()
    {
        return $this->quantity <= $this->reorder_level;
    }

    /**
     * Calculate stock value.
     */
    public function getStockValueAttribute()
    {
        return $this->quantity * $this->unit_cost;
    }

    /**
     * Get item type options.
     */
    public static function getItemTypeOptions()
    {
        return [
            'cleaning' => 'Cleaning Supplies',
            'kitchen' => 'Kitchen Items',
            'office' => 'Office Supplies',
            'maintenance' => 'Maintenance Items',
            'food' => 'Food Items',
            'beverage' => 'Beverages',
            'linen' => 'Linens',
            'amenity' => 'Guest Amenities',
            'other' => 'Other',
        ];
    }

    /**
     * Get category options.
     */
    public static function getCategoryOptions()
    {
        return [
            'consumable' => 'Consumable',
            'non_consumable' => 'Non-Consumable',
        ];
    }

    // ==================== METHODS ====================

    /**
     * Update quantity (add or subtract).
     */
    public function updateQuantity($quantity, $operation = 'add', $reason = '')
    {
        $oldQuantity = $this->quantity;
        
        if ($operation === 'add') {
            $this->quantity += $quantity;
        } elseif ($operation === 'subtract') {
            $this->quantity -= $quantity;
        }
        
        $this->save();
        
        // Record inventory transaction
        InventoryTransaction::create([
            'inventory_item_id' => $this->id,
            'type' => $operation === 'add' ? 'in' : 'out',
            'quantity' => $quantity,
            'unit_cost' => $this->unit_cost,
            'total_value' => $quantity * $this->unit_cost,
            'reference_type' => 'manual',
            'reference_id' => null,
            'notes' => $reason ?: ($operation === 'add' ? 'Stock added' : 'Stock used'),
            'created_by' => auth()->id(),
        ]);
        
        return $this;
    }

    /**
     * Get current supplier price.
     */
    public function currentSupplierPrice($supplierId = null)
    {
        $query = $this->supplierPrices()
            ->where('effective_from', '<=', now())
            ->where(function ($q) {
                $q->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', now());
            })
            ->orderBy('effective_from', 'desc');
        
        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }
        
        return $query->first();
    }

    /**
     * Get stock usage in last period.
     */
    public function getUsageLastPeriod($days = 30)
    {
        return $this->taskItems()
            ->whereHas('task', function ($q) use ($days) {
                $q->where('completed_at', '>=', now()->subDays($days));
            })
            ->sum('quantity_used');
    }
}