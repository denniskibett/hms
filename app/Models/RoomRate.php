<?php

namespace App\Models;

use App\Enums\MealPlan;
use App\Enums\PricingBasis;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_type_id',
        'meal_plan',
        'basis',
        'adults',
        'rate',
        'child_rate',
        'is_active',
    ];

    protected $casts = [
        'meal_plan' => MealPlan::class,
        'basis' => PricingBasis::class,
        'adults' => 'integer',
        'rate' => 'decimal:2',
        'child_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
