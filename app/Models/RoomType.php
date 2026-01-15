<?php

namespace App\Models;

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
}