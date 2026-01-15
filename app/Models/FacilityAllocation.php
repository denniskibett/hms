<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacilityAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'stay_id',
        'facility_id',
        'package_id',
        'start_time',
        'end_time',
        'rate_applied',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
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
     * Facility for this allocation.
     */
    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    /**
     * Package used for this allocation.
     */
    public function package()
    {
        return $this->belongsTo(FacilityPackage::class);
    }

    /**
     * Scope for current allocations.
     */
    public function scopeCurrent($query)
    {
        return $query->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->whereIn('status', ['confirmed', 'in_use']);
    }

    /**
     * Scope for upcoming allocations.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', now())
            ->whereIn('status', ['booked', 'confirmed']);
    }

    /**
     * Calculate duration in hours.
     */
    public function getDurationHoursAttribute()
    {
        return $this->start_time->diffInHours($this->end_time);
    }

    /**
     * Calculate total cost.
     */
    public function getTotalCostAttribute()
    {
        if ($this->package) {
            return $this->package->price;
        }
        
        return $this->rate_applied * $this->duration_hours;
    }

    /**
     * Check if allocation is active.
     */
    public function getIsActiveAttribute()
    {
        return now()->between($this->start_time, $this->end_time) && 
               in_array($this->status, ['confirmed', 'in_use']);
    }
}