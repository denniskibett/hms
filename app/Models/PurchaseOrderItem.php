<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A line on a purchase order. `total` is a virtual column (quantity × unit_price).
 */
class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'inventory_item_id',
        'quantity',
        'received_quantity',
        'unit_price',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'received_quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function getOutstandingAttribute(): float
    {
        return max(round((float) $this->quantity - (float) $this->received_quantity, 2), 0);
    }

    public function getLineTotalAttribute(): float
    {
        return round((float) $this->quantity * (float) $this->unit_price, 2);
    }
}
