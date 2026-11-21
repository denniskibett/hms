<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * What a supplier charged for an item from a date. Written when a delivery is
 * received; used to suggest prices on new purchase orders.
 * Primary key is (supplier_id, inventory_item_id, effective_from).
 */
class SupplierPrice extends Model
{
    protected $primaryKey = null;

    public $incrementing = false;

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

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    /**
     * Record today's price, closing the previous one.
     */
    public static function record(int $supplierId, int $itemId, float $price): void
    {
        $today = today()->toDateString();

        DB::table('supplier_prices')
            ->where('supplier_id', $supplierId)
            ->where('inventory_item_id', $itemId)
            ->whereNull('effective_to')
            ->where('effective_from', '<', $today)
            ->update(['effective_to' => today()->subDay()->toDateString(), 'updated_at' => now()]);

        DB::table('supplier_prices')->updateOrInsert(
            ['supplier_id' => $supplierId, 'inventory_item_id' => $itemId, 'effective_from' => $today],
            ['unit_price' => $price, 'effective_to' => null, 'updated_at' => now(), 'created_at' => now()],
        );
    }
}
