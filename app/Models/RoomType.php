<?php

namespace App\Models;

use App\Enums\MealPlan;
use App\Enums\PricingBasis;
use App\Helpers\SystemHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'base_rate',
        'capacity',
        'bed_type',
        'description',
        'amenities',
    ];

    protected $casts = [
        'amenities' => 'array',
        'base_rate' => 'decimal:2',
    ];


    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    /**
     * Services included with this room type.
     */
    public function services()
    {
        return $this->hasMany(RoomTypeService::class);
    }

    /**
     * Available rooms of this type.
     */
    public function availableRooms()
    {
        return $this->rooms()->where('status', 'available');
    }

    /**
     * Scope by bed type.
     */
    public function scopeByBedType($query, $bedType)
    {
        return $query->where('bed_type', $bedType);
    }

    /**
     * Scope by minimum capacity.
     */
    public function scopeByMinCapacity($query, $capacity)
    {
        return $query->where('capacity', '>=', $capacity);
    }

    /**
     * Get bed type options.
     */
    public static function getBedTypeOptions()
    {
        return [
            'single' => 'Single',
            'double' => 'Double',
            'queen' => 'Queen',
            'king' => 'King',
            'twin' => 'Twin',
        ];
    }

    /**
     * Calculate price for given number of nights.
     */
    public function calculatePrice($nights)
    {
        return $this->base_rate * $nights;
    }

    public function rates()
    {
        return $this->hasMany(RoomRate::class);
    }

    /**
     * Price per night for one room of this type.
     *
     * Looks for an active rate for the meal plan (per_room: exact occupancy first,
     * then the "any occupancy" row). Without one it falls back to the room's
     * per-person prices if set, then to this type's base rate per room — which is
     * how bookings were priced before rates existed.
     *
     * @return array{nightly: float, basis: PricingBasis, source: string, description: string}
     */
    public function quote(MealPlan $mealPlan, int $adults, int $children = 0, ?Room $room = null): array
    {
        $childPercent = SystemHelper::childRatePercent();

        $rate = $this->rates()
            ->active()
            ->where('meal_plan', $mealPlan->value)
            ->where(fn ($q) => $q->where('adults', $adults)->orWhereNull('adults'))
            ->orderByRaw('adults IS NULL') // exact occupancy wins over "any"
            ->first();

        if ($rate) {
            $childEach = $rate->child_rate !== null
                ? (float) $rate->child_rate
                : ($rate->basis === PricingBasis::PerPerson ? (float) $rate->rate * $childPercent / 100 : 0.0);

            $nightly = $rate->basis === PricingBasis::PerPerson
                ? (float) $rate->rate * $adults + $childEach * $children
                : (float) $rate->rate + $childEach * $children;

            return [
                'nightly' => round($nightly, 2),
                'basis' => $rate->basis,
                'source' => 'rate',
                'description' => $rate->basis === PricingBasis::PerPerson
                    ? "{$mealPlan->value} per person: {$adults} × {$rate->rate}" . ($children ? " + {$children} child × " . number_format($childEach, 2) : '')
                    : "{$mealPlan->value} per room" . ($children && $childEach ? " + {$children} child × " . number_format($childEach, 2) : ''),
            ];
        }

        if ($room && (float) $room->adult_price > 0) {
            $childEach = (float) $room->child_price > 0 ? (float) $room->child_price : (float) $room->adult_price * $childPercent / 100;

            return [
                'nightly' => round((float) $room->adult_price * $adults + $childEach * $children, 2),
                'basis' => PricingBasis::PerPerson,
                'source' => 'room',
                'description' => "Room {$room->room_number} per person: {$adults} × {$room->adult_price}" . ($children ? " + {$children} child × " . number_format($childEach, 2) : ''),
            ];
        }

        return [
            'nightly' => round((float) $this->base_rate, 2),
            'basis' => PricingBasis::PerRoom,
            'source' => 'room_type',
            'description' => "{$this->name} base rate per room",
        ];
    }
}