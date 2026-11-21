<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A dish or drink. Its prices live on its variants (portions / sizes);
 * a dish with one price has a single "Regular" variant.
 */
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

    public function category()
    {
        return $this->belongsTo(MenuCategory::class);
    }

    public function variants()
    {
        return $this->hasMany(MenuItemVariant::class);
    }

    public function activeVariants()
    {
        return $this->variants()->where('is_active', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * "KES 450.00" or "KES 450.00 – 800.00". Load variants first.
     */
    public function getPriceRangeAttribute(): ?string
    {
        $prices = $this->variants->where('is_active', true)->pluck('price')->map(fn ($p) => (float) $p);
        if ($prices->isEmpty()) {
            return null;
        }

        $currency = \App\Helpers\SystemHelper::currencySymbol();

        return $prices->min() === $prices->max()
            ? $currency . ' ' . number_format($prices->min(), 2)
            : $currency . ' ' . number_format($prices->min(), 2) . ' – ' . number_format($prices->max(), 2);
    }

    public function formData(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category_id' => $this->category_id,
            'description' => $this->description ?? '',
            'is_active' => $this->is_active,
            'variants' => $this->variants->map(fn (MenuItemVariant $v) => [
                'id' => $v->id,
                'name' => $v->name,
                'price' => (float) $v->price,
                'preparation_time' => $v->preparation_time,
                'is_active' => $v->is_active,
            ])->values()->all(),
        ];
    }
}
