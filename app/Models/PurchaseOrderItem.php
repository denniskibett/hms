<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'inventory_item_id',
        'quantity',
        'unit_price',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
    ];

    protected $appends = [
        'total',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Purchase order this item belongs to.
     */
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Inventory item for this PO item.
     */
    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    // ==================== ATTRIBUTES ====================

    /**
     * Calculate item total.
     */
    public function getTotalAttribute()
    {
        return $this->quantity * $this->unit_price;
    }
}