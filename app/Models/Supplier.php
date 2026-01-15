<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'status',
        'supplier_type', // 'cleaning', 'food', 'maintenance', 'general'
        'payment_terms',
        'tax_id',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Items supplied by this supplier.
     */
    public function items()
    {
        return $this->belongsToMany(InventoryItem::class, 'supplier_prices')
            ->withPivot('unit_price', 'effective_from', 'effective_to');
    }

    /**
     * Current prices for items.
     */
    public function currentPrices()
    {
        return $this->hasMany(SupplierPrice::class)
            ->where('effective_from', '<=', now())
            ->where(function ($q) {
                $q->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', now());
            });
    }

    /**
     * Purchase orders from this supplier.
     */
    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    // ==================== SCOPES ====================

    /**
     * Scope for active suppliers.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope by supplier type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('supplier_type', $type);
    }

    // ==================== ATTRIBUTES ====================

    /**
     * Get supplier type options.
     */
    public static function getSupplierTypeOptions()
    {
        return [
            'cleaning' => 'Cleaning Supplies',
            'food' => 'Food & Beverage',
            'maintenance' => 'Maintenance',
            'office' => 'Office Supplies',
            'linen' => 'Linens',
            'general' => 'General Supplier',
        ];
    }
}