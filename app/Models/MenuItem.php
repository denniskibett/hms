<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
        'description',
        'image_url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Category of this menu item.
     */
    public function category()
    {
        return $this->belongsTo(MenuCategory::class);
    }

    /**
     * Variants of this menu item.
     */
    public function variants()
    {
        return $this->hasMany(MenuItemVariant::class);
    }

    /**
     * Active variants.
     */
    public function activeVariants()
    {
        return $this->variants()->where('is_active', true);
    }

    /**
     * Scope for active items.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get price range for this item.
     */
    public function getPriceRangeAttribute()
    {
        $prices = $this->activeVariants->pluck('price')->toArray();
        
        if (empty($prices)) {
            return null;
        }
        
        $min = min($prices);
        $max = max($prices);
        
        if ($min == $max) {
            return 'KSH ' . number_format($min, 2);
        }
        
        return 'KSH ' . number_format($min, 2) . ' - ' . number_format($max, 2);
    }
}