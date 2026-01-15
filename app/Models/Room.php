<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_number',
        'room_type_id',
        'status',
        'floor',
        'wing',
        'features',
    ];

    protected $casts = [
        'features' => 'array',
    ];

    /**
     * Room type of this room.
     */
    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * Current room allocation.
     */
    public function currentAllocation()
    {
        return $this->hasOne(RoomAllocation::class)
            ->whereDate('from_date', '<=', now())
            ->whereDate('to_date', '>=', now());
    }

    /**
     * Room allocations.
     */
    public function allocations()
    {
        return $this->hasMany(RoomAllocation::class);
    }

    /**
     * Current stay in this room.
     */
    public function currentStay()
    {
        return $this->hasOneThrough(Stay::class, RoomAllocation::class, 'room_id', 'id', 'id', 'stay_id')
            ->whereIn('stays.status', ['checked_in'])
            ->whereDate('room_allocations.from_date', '<=', now())
            ->whereDate('room_allocations.to_date', '>=', now());
    }

    /**
     * Housekeeping tasks for this room.
     */
    public function housekeepingTasks()
    {
        return $this->hasMany(HousekeepingTask::class);
    }
    

    /**
     * Pending housekeeping tasks.
     */
    public function pendingTasks()
    {
        return $this->housekeepingTasks()->whereIn('status', ['pending', 'assigned']);
    }

    /**
     * Scope for available rooms.
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope for occupied rooms.
     */
    public function scopeOccupied($query)
    {
        return $query->where('status', 'occupied');
    }

    /**
     * Scope by floor.
     */
    public function scopeByFloor($query, $floor)
    {
        return $query->where('floor', $floor);
    }

    /**
     * Scope by room type.
     */
    public function scopeByRoomType($query, $roomTypeId)
    {
        return $query->where('room_type_id', $roomTypeId);
    }

    /**
     * Check if room is available for dates.
     */
    public function isAvailableForDates($fromDate, $toDate)
    {
        $conflictingAllocation = $this->allocations()
            ->where(function ($query) use ($fromDate, $toDate) {
                $query->whereBetween('from_date', [$fromDate, $toDate])
                    ->orWhereBetween('to_date', [$fromDate, $toDate])
                    ->orWhere(function ($q) use ($fromDate, $toDate) {
                        $q->where('from_date', '<=', $fromDate)
                            ->where('to_date', '>=', $toDate);
                    });
            })
            ->exists();
        
        return !$conflictingAllocation && $this->status === 'available';
    }

    /**
     * Get status options.
     */
    public static function getStatusOptions()
    {
        return [
            'available' => 'Available',
            'occupied' => 'Occupied',
            'cleaning' => 'Cleaning',
            'maintenance' => 'Maintenance',
            'out_of_order' => 'Out of Order',
        ];
    }
}