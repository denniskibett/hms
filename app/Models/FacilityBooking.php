<?php

namespace App\Models;

use App\Enums\FacilityBookingStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * An event in a venue: a conference, meeting, wedding or party. Priced from its
 * items (venue hire, packages, extras) and billed on its own invoice.
 */
class FacilityBooking extends Model
{
    protected $fillable = [
        'facility_id',
        'stay_id',
        'title',
        'client_name',
        'contact_name',
        'contact_phone',
        'contact_email',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'pax',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'pax' => 'integer',
        'status' => FacilityBookingStatus::class,
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function stay()
    {
        return $this->belongsTo(Stay::class);
    }

    public function items()
    {
        return $this->hasMany(FacilityBookingItem::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeWithStatus(Builder $query, FacilityBookingStatus|string $status): Builder
    {
        return $query->where('status', $status instanceof FacilityBookingStatus ? $status->value : $status);
    }

    /**
     * Events running on or after today that still hold their venue.
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereIn('status', [FacilityBookingStatus::Tentative->value, FacilityBookingStatus::Confirmed->value])
            ->whereDate('end_date', '>=', today());
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(fn ($q) => $q
            ->where('title', 'like', "%{$term}%")
            ->orWhere('client_name', 'like', "%{$term}%")
            ->orWhere('contact_name', 'like', "%{$term}%")
            ->orWhere('contact_phone', 'like', "%{$term}%"));
    }

    public function getReferenceAttribute(): string
    {
        return sprintf('EVT-%05d', $this->id);
    }

    public function getDaysAttribute(): int
    {
        return (int) $this->start_date->diffInDays($this->end_date) + 1;
    }

    public function getTotalAttribute(): float
    {
        return round((float) $this->items->sum(fn (FacilityBookingItem $i) => $i->total), 2);
    }

    /**
     * "08:00–17:00", or null for an all-day event.
     */
    public function getTimeRangeAttribute(): ?string
    {
        return $this->start_time && $this->end_time
            ? substr($this->start_time, 0, 5) . '–' . substr($this->end_time, 0, 5)
            : null;
    }

    public function currentInvoice(): ?Invoice
    {
        return $this->invoices()->where('status', '!=', 'cancelled')->oldest('id')->first();
    }

    /**
     * Fields for the add / edit modal.
     */
    public function formData(): array
    {
        return [
            'id' => $this->id,
            'facility_id' => $this->facility_id,
            'stay_id' => $this->stay_id,
            'title' => $this->title,
            'client_name' => $this->client_name,
            'contact_name' => $this->contact_name ?? '',
            'contact_phone' => $this->contact_phone,
            'contact_email' => $this->contact_email ?? '',
            'start_date' => $this->start_date->toDateString(),
            'end_date' => $this->end_date->toDateString(),
            'start_time' => $this->start_time ? substr($this->start_time, 0, 5) : '',
            'end_time' => $this->end_time ? substr($this->end_time, 0, 5) : '',
            'pax' => $this->pax,
            'status' => $this->status->value,
            'notes' => $this->notes ?? '',
            'items' => $this->items->map(fn (FacilityBookingItem $i) => [
                'facility_package_id' => $i->facility_package_id,
                'description' => $i->description,
                'charge_basis' => $i->charge_basis,
                'quantity' => (float) $i->quantity,
                'days' => $i->days,
                'unit_price' => (float) $i->unit_price,
            ])->values()->all(),
        ];
    }
}
