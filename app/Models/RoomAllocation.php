<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'stay_id',
        'room_id',
        'from_date',
        'to_date',
        'rate_applied',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'rate_applied' => 'decimal:2',
    ];

    /**
     * Stay for this allocation.
     */
    public function stay()
    {
        return $this->belongsTo(Stay::class);
    }

    /**
     * Room for this allocation.
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Scope for current allocations.
     */
    public function scopeCurrent($query)
    {
        return $query->whereDate('from_date', '<=', now())
            ->whereDate('to_date', '>=', now());
    }

    /**
     * Calculate total nights.
     */
    public function getTotalNightsAttribute()
    {
        return $this->from_date->diffInDays($this->to_date);
    }

    /**
     * Calculate total cost.
     */
    public function getTotalCostAttribute()
    {
        return $this->rate_applied * $this->total_nights;
    }

    /**
     * Check if allocation is active.
     */
    public function getIsActiveAttribute()
    {
        return now()->between($this->from_date, $this->to_date);
    }
}