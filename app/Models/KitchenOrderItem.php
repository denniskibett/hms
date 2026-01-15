<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'price_at_order' => 'decimal:2',
    ];

    /**
     * Order this item belongs to.
     */
    public function order()
    {
        return $this->belongsTo(KitchenOrder::class);
    }

    /**
     * Menu item variant for this order item.
     */
    public function variant()
    {
        return $this->belongsTo(MenuItemVariant::class, 'menu_item_variant_id');
    }

    /**
     * Calculate item total.
     */
    public function getTotalAttribute()
    {
        return $this->price_at_order * $this->quantity;
    }
}