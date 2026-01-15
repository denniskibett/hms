<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'capacity',
        'base_rate',
        'description',
        'amenities',
        'status',
    ];

    protected $casts = [
        'amenities' => 'array',
        'base_rate' => 'decimal:2',
    ];

    /**
     * Packages for this facility.
     */
    public function packages()
    {
        return $this->hasMany(FacilityPackage::class);
    }

    /**
     * Allocations for this facility.
     */
    public function allocations()
    {
        return $this->hasMany(FacilityAllocation::class);
    }

    /**
     * Active packages.
     */
    public function activePackages()
    {
        return $this->packages()->where('is_active', true);
    }

    /**
     * Current allocations.
     */
    public function currentAllocations()
    {
        return $this->allocations()
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->whereIn('status', ['confirmed', 'in_use']);
    }

    /**
     * Scope for available facilities.
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope by minimum capacity.
     */
    public function scopeByMinCapacity($query, $capacity)
    {
        return $query->where('capacity', '>=', $capacity);
    }

    /**
     * Check if facility is available for time slot.
     */
    public function isAvailableForTimeSlot($startTime, $endTime)
    {
        $conflictingAllocation = $this->allocations()
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                    });
            })
            ->whereIn('status', ['booked', 'confirmed', 'in_use'])
            ->exists();
        
        return !$conflictingAllocation && $this->status === 'available';
    }
}