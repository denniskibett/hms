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
        'adults',
        'children',
        'guest_notes',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'rate_applied' => 'decimal:2',
        'adults' => 'integer',
        'children' => 'integer',
    ];

    public function stay()
    {
        return $this->belongsTo(Stay::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Allocations covering today (departure day excluded).
     */
    public function scopeCurrent($query)
    {
        return $query->whereDate('from_date', '<=', today())
            ->whereDate('to_date', '>', today());
    }

    public function getNightsAttribute(): int
    {
        return (int) $this->from_date->diffInDays($this->to_date);
    }

    /**
     * rate_applied is the room's full nightly price, so total = rate × nights.
     */
    public function getTotalAttribute(): float
    {
        return round((float) $this->rate_applied * $this->nights, 2);
    }

    // Older names kept for existing callers
    public function getTotalNightsAttribute(): int
    {
        return $this->nights;
    }

    public function getTotalCostAttribute(): float
    {
        return $this->total;
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->from_date->lte(today()) && $this->to_date->gt(today());
    }
}
