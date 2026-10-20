<?php

namespace App\Models;

use App\Enums\RoomStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    /**
     * Stay statuses that hold a room for their dates.
     */
    public const ACTIVE_STAY_STATUSES = ['reserved', 'booked', 'checked_in'];

    /**
     * Fields the create/edit modal works with (also what we send to the browser).
     */
    public const FORM_FIELDS = ['id', 'room_number', 'room_type_id', 'status', 'floor', 'wing', 'adult_price', 'child_price', 'features'];

    protected $fillable = [
        'room_number',
        'room_type_id',
        'status',
        'floor',
        'wing',
        'features',
        'adult_price',
        'child_price',
    ];

    protected $casts = [
        'status' => RoomStatus::class,
        'features' => 'array',
        'floor' => 'integer',
        'adult_price' => 'decimal:2',
        'child_price' => 'decimal:2',
    ];

    /**
     * Room type of this room.
     */
    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * Room allocations.
     */
    public function allocations()
    {
        return $this->hasMany(RoomAllocation::class);
    }

    /**
     * Allocation covering today (departure day excluded).
     */
    public function currentAllocation()
    {
        return $this->hasOne(RoomAllocation::class)
            ->whereDate('from_date', '<=', today())
            ->whereDate('to_date', '>', today());
    }

    /**
     * Checked-in stay currently in this room.
     */
    public function currentStay()
    {
        return $this->hasOneThrough(Stay::class, RoomAllocation::class, 'room_id', 'id', 'id', 'stay_id')
            ->where('stays.status', 'checked_in')
            ->whereDate('room_allocations.from_date', '<=', today())
            ->whereDate('room_allocations.to_date', '>=', today());
    }

    /**
     * Housekeeping / maintenance tasks raised against this room.
     */
    public function housekeepingTasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Open tasks for this room.
     */
    public function pendingTasks()
    {
        return $this->housekeepingTasks()->whereIn('status', ['pending', 'assigned', 'in_progress']);
    }

    /**
     * Scope by status.
     */
    public function scopeWithStatus(Builder $query, RoomStatus|string $status): Builder
    {
        return $query->where('status', $status instanceof RoomStatus ? $status->value : $status);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->withStatus(RoomStatus::Available);
    }

    public function scopeOccupied(Builder $query): Builder
    {
        return $query->withStatus(RoomStatus::Occupied);
    }

    public function scopeByFloor(Builder $query, $floor): Builder
    {
        return $query->where('floor', $floor);
    }

    public function scopeByRoomType(Builder $query, $roomTypeId): Builder
    {
        return $query->where('room_type_id', $roomTypeId);
    }

    /**
     * Rooms that can be booked from $from (arrival) to $to (departure).
     *
     * Dates are half-open: a guest leaving on the 10th doesn't block one arriving on the 10th.
     * Only active stays block a room; cancelled, checked-out and deleted stays don't.
     * Today's housekeeping status doesn't matter for future dates, but rooms under
     * maintenance / out of order are excluded.
     */
    public function scopeAvailableBetween(Builder $query, $from, $to, ?int $ignoreStayId = null): Builder
    {
        return $query
            ->whereNotIn('status', [RoomStatus::Maintenance->value, RoomStatus::OutOfOrder->value])
            ->whereDoesntHave('allocations', function (Builder $q) use ($from, $to, $ignoreStayId) {
                $q->whereDate('from_date', '<', $to)
                    ->whereDate('to_date', '>', $from)
                    ->whereHas('stay', fn (Builder $s) => $s->whereIn('status', self::ACTIVE_STAY_STATUSES))
                    ->when($ignoreStayId, fn (Builder $q) => $q->where('stay_id', '!=', $ignoreStayId));
            });
    }

    /**
     * Check if this room can be booked for the given dates.
     */
    public function isAvailableForDates($fromDate, $toDate, ?int $ignoreStayId = null): bool
    {
        return static::whereKey($this->getKey())
            ->availableBetween($fromDate, $toDate, $ignoreStayId)
            ->exists();
    }

    /**
     * First allocation blocking these dates, if any (for "next available" hints).
     */
    public function conflictingAllocation($fromDate, $toDate): ?RoomAllocation
    {
        return $this->allocations()
            ->whereDate('from_date', '<', $toDate)
            ->whereDate('to_date', '>', $fromDate)
            ->whereHas('stay', fn (Builder $s) => $s->whereIn('status', self::ACTIVE_STAY_STATUSES))
            ->orderByDesc('to_date')
            ->first();
    }

    /**
     * value => label, for <select> options.
     */
    public static function getStatusOptions(): array
    {
        return RoomStatus::options();
    }
}
