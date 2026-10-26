<?php

namespace App\Models;

use App\Enums\MealPlan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A request to stay, sent from the public website. The front desk turns it
 * into a booking (converted) or declines it.
 */
class BookingRequest extends Model
{
    public const STATUSES = [
        'new' => 'New',
        'converted' => 'Booked',
        'declined' => 'Declined',
    ];

    protected $fillable = [
        'name',
        'phone',
        'email',
        'arrival_date',
        'departure_date',
        'adults',
        'children',
        'rooms',
        'room_type_id',
        'meal_plan',
        'message',
        'status',
        'stay_id',
        'decline_reason',
        'handled_by',
        'handled_at',
        'ip_address',
    ];

    protected $casts = [
        'arrival_date' => 'date',
        'departure_date' => 'date',
        'adults' => 'integer',
        'children' => 'integer',
        'rooms' => 'integer',
        'meal_plan' => MealPlan::class,
        'handled_at' => 'datetime',
    ];

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function stay()
    {
        return $this->belongsTo(Stay::class);
    }

    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'new');
    }

    public function getReferenceAttribute(): string
    {
        return sprintf('BR-%05d', $this->id);
    }

    public function getNightsAttribute(): int
    {
        return (int) $this->arrival_date->diffInDays($this->departure_date);
    }

    public function getStatusClassesAttribute(): string
    {
        return match ($this->status) {
            'new' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400',
            'converted' => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500',
            default => 'bg-gray-100 text-gray-600 dark:bg-gray-500/15 dark:text-gray-400',
        };
    }
}
