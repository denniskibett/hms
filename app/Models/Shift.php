<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A shift pattern staff are put on: Morning 06:00–14:00, Night 22:00–06:00…
 * Times are stored as "HH:MM:SS" strings; a night shift ends the next day.
 */
class Shift extends Model
{
    use HasFactory;

    public const TYPES = [
        'morning' => 'Morning',
        'evening' => 'Evening',
        'night' => 'Night',
        'custom' => 'Other',
    ];

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function assignments()
    {
        return $this->hasMany(ShiftAssignment::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * "06:00–14:00"
     */
    public function getHoursLabelAttribute(): string
    {
        return substr($this->start_time, 0, 5) . '–' . substr($this->end_time, 0, 5);
    }

    public function getDurationHoursAttribute(): float
    {
        [$sh, $sm] = array_map('intval', explode(':', $this->start_time));
        [$eh, $em] = array_map('intval', explode(':', $this->end_time));
        $minutes = ($eh * 60 + $em) - ($sh * 60 + $sm);

        return round(($minutes <= 0 ? $minutes + 1440 : $minutes) / 60, 2);
    }

    public function formData(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'start_time' => substr($this->start_time, 0, 5),
            'end_time' => substr($this->end_time, 0, 5),
            'type' => $this->type,
            'is_active' => $this->is_active,
        ];
    }
}
