<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacilityPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'facility_id',
        'name',
        'price',
        'duration_hours',
        'inclusions',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Facility this package belongs to.
     */
    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    /**
     * Allocations using this package.
     */
    public function allocations()
    {
        return $this->hasMany(FacilityAllocation::class);
    }

    /**
     * Scope for active packages.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Calculate end time from start time.
     */
    public function calculateEndTime($startTime)
    {
        return $startTime->copy()->addHours($this->duration_hours);
    }
}