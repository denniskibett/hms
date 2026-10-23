<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * One line on a kitchen order. The price is copied from the menu when ordered.
 */
class KitchenOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'menu_item_variant_id',
        'quantity',
        'price_at_order',
        'status',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price_at_order' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(KitchenOrder::class, 'order_id');
    }

    public function variant()
    {
        return $this->belongsTo(MenuItemVariant::class, 'menu_item_variant_id');
    }

    public function getTotalAttribute(): float
    {
        return round($this->quantity * (float) $this->price_at_order, 2);
    }
}
