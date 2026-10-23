<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Something sold on an event, priced per day: a conference package per person
 * (Full Day, Half Day, Lunch Only) or an item per unit (projector hire).
 * With no facility_id the package can be sold with any venue.
 */
class FacilityPackage extends Model
{
    use HasFactory;

    public const BASES = [
        'per_person' => 'Per person per day',
        'per_unit' => 'Per unit per day',
    ];

    protected $fillable = [
        'facility_id',
        'name',
        'price',
        'charge_basis',
        'inclusions',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function bookingItems()
    {
        return $this->hasMany(FacilityBookingItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Packages that can be sold with a venue: its own plus the general ones.
     */
    public function scopeForFacility(Builder $query, ?int $facilityId): Builder
    {
        return $query->where(fn ($q) => $q->whereNull('facility_id')->when($facilityId, fn ($q) => $q->orWhere('facility_id', $facilityId)));
    }

    public function getBasisLabelAttribute(): string
    {
        return self::BASES[$this->charge_basis] ?? $this->charge_basis;
    }

    public function formData(): array
    {
        return [
            'id' => $this->id,
            'facility_id' => (string) ($this->facility_id ?? ''),
            'name' => $this->name,
            'price' => (float) $this->price,
            'charge_basis' => $this->charge_basis,
            'inclusions' => $this->inclusions ?? '',
            'is_active' => $this->is_active,
        ];
    }
}
