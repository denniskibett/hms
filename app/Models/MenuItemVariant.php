<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A priced portion of a dish: "Regular", "Quarter", "Half", "500ml"…
 */
class MenuItemVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_item_id',
        'name',
        'price',
        'preparation_time',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'preparation_time' => 'integer',
        'is_active' => 'boolean',
    ];

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function kitchenOrderItems()
    {
        return $this->hasMany(KitchenOrderItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * "Beef Stew (Half)", or just "Tea" for a single-variant item called Regular.
     */
    public function getFullNameAttribute(): string
    {
        $item = $this->menuItem?->name ?? 'Item';

        return in_array(strtolower($this->name), ['', 'regular', 'standard'], true) ? $item : "{$item} ({$this->name})";
    }
}
