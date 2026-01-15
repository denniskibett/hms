<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierPrice extends Model
{
    use HasFactory;

    protected $table = 'supplier_prices';

    protected $fillable = [
        'supplier_id',
        'inventory_item_id',
        'unit_price',
        'effective_from',
        'effective_to',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    public $timestamps = false;
    public $incrementing = false;

    // ==================== RELATIONSHIPS ====================

    /**
     * Supplier for this price.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Inventory item for this price.
     */
    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    // ==================== ATTRIBUTES ====================

    /**
     * Check if price is current.
     */
    public function getIsCurrentAttribute()
    {
        $now = now();
        return $this->effective_from <= $now && 
               (!$this->effective_to || $this->effective_to >= $now);
    }
}