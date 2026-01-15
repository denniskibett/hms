<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'type',
        'is_active',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_active' => 'boolean',
    ];

    /**
     * Assignments for this shift.
     */
    public function assignments()
    {
        return $this->hasMany(ShiftAssignment::class);
    }

    /**
     * Today's assignments.
     */
    public function todayAssignments()
    {
        return $this->assignments()->whereDate('date', today());
    }

    /**
     * Scope for active shifts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get shift type options.
     */
    public static function getTypeOptions()
    {
        return [
            'morning' => 'Morning Shift',
            'evening' => 'Evening Shift',
            'night' => 'Night Shift',
            'custom' => 'Custom Shift',
        ];
    }

    /**
     * Calculate shift duration in hours.
     */
    public function getDurationHoursAttribute()
    {
        return $this->start_time->diffInHours($this->end_time);
    }
}