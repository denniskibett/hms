<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'source_type',
        'reference_id',
        'description',
        'quantity',
        'unit_price',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
    ];

    protected $appends = [
        'total',
    ];

    /**
     * Invoice this item belongs to.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get source type options.
     */
    public static function getSourceTypeOptions()
    {
        return [
            'room' => 'Room',
            'food' => 'Food',
            'facility' => 'Facility',
            'service' => 'Service',
            'other' => 'Other',
        ];
    }

    /**
     * Calculate total for this item.
     */
    public function getTotalAttribute()
    {
        return $this->quantity * $this->unit_price;
    }
}