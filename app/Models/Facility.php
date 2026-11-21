<?php

namespace App\Models;

use App\Enums\FacilityBookingStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A venue that can be booked for events: conference hall, boardroom, garden.
 * base_rate is the venue hire per day (0 when hire is included in the packages).
 */
class Facility extends Model
{
    use HasFactory;

    public const STATUSES = [
        'available' => 'Available',
        'maintenance' => 'Maintenance',
        'unavailable' => 'Unavailable',
    ];

    public const AMENITIES = ['Wi-Fi', 'Air conditioning', 'Projector', 'Screen', 'PA system', 'Microphones', 'Flip chart', 'Whiteboard', 'Stage', 'Parking'];

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

    public function packages()
    {
        return $this->hasMany(FacilityPackage::class);
    }

    public function bookings()
    {
        return $this->hasMany(FacilityBooking::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', 'available');
    }

    /**
     * The first booking that holds this venue over the given dates and times.
     * Two bookings on the same day only clash when both have times that overlap,
     * or when either has no times (whole day).
     */
    public function conflictingBooking(CarbonInterface $from, CarbonInterface $to, ?string $startTime = null, ?string $endTime = null, ?int $ignoreId = null): ?FacilityBooking
    {
        return $this->bookings()
            ->whereIn('status', array_map(fn ($s) => $s->value, array_filter(FacilityBookingStatus::cases(), fn ($s) => $s->holdsVenue())))
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->whereDate('start_date', '<=', $to)
            ->whereDate('end_date', '>=', $from)
            ->when($startTime && $endTime, fn ($q) => $q->where(fn ($q) => $q
                ->whereNull('start_time')
                ->orWhereNull('end_time')
                ->orWhere(fn ($q) => $q->where('start_time', '<', $endTime)->where('end_time', '>', $startTime))))
            ->orderBy('start_date')
            ->first();
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    /**
     * Fields for the add / edit modal.
     */
    public function formData(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'capacity' => $this->capacity,
            'base_rate' => (float) $this->base_rate,
            'description' => $this->description ?? '',
            'amenities' => $this->amenities ?? [],
            'status' => $this->status,
        ];
    }
}
