<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One line on an event: quantity (pax or units) × days × unit price.
 */
class FacilityBookingItem extends Model
{
    public const BASES = FacilityPackage::BASES + [
        'venue' => 'Venue hire per day',
    ];

    protected $fillable = [
        'facility_booking_id',
        'facility_package_id',
        'description',
        'charge_basis',
        'quantity',
        'days',
        'unit_price',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'days' => 'integer',
        'unit_price' => 'decimal:2',
    ];

    public function booking()
    {
        return $this->belongsTo(FacilityBooking::class, 'facility_booking_id');
    }

    public function package()
    {
        return $this->belongsTo(FacilityPackage::class, 'facility_package_id');
    }

    public function getTotalAttribute(): float
    {
        return round((float) $this->quantity * $this->days * (float) $this->unit_price, 2);
    }

    /**
     * "40 pax × 2 days", "1 unit × 3 days", "2 days".
     */
    public function getQuantityLabelAttribute(): string
    {
        $qty = rtrim(rtrim(number_format((float) $this->quantity, 2), '0'), '.');
        $days = $this->days . ' ' . ($this->days === 1 ? 'day' : 'days');

        return match ($this->charge_basis) {
            'per_person' => "{$qty} pax × {$days}",
            'per_unit' => "{$qty} " . ((float) $this->quantity === 1.0 ? 'unit' : 'units') . " × {$days}",
            default => $days,
        };
    }
}
