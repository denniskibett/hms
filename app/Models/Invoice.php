<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'stay_id',
        'facility_booking_id',
        'invoice_number',
        'subtotal',
        'tax_amount',
        'tax_inclusive',
        'vat_rate',
        'vat_amount',
        'levy_rate',
        'levy_amount',
        'discount_amount',
        'total',
        'paid_amount',
        'status',
        'issue_date',
        'due_date',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'tax_inclusive' => 'boolean',
        'vat_rate' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'levy_rate' => 'decimal:2',
        'levy_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'issue_date' => 'date',
        'due_date' => 'date',
    ];

    protected $appends = [
        'due_amount',
    ];

    /**
     * Stay for this invoice.
     */
    public function stay()
    {
        return $this->belongsTo(Stay::class);
    }

    /**
     * Event this invoice is for (invoices have a stay or an event).
     */
    public function facilityBooking()
    {
        return $this->belongsTo(FacilityBooking::class);
    }

    /**
     * Items in this invoice.
     */
    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Payments for this invoice.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Room allocation items.
     */
    public function roomItems()
    {
        return $this->items()->where('source_type', 'room');
    }

    /**
     * Food items.
     */
    public function foodItems()
    {
        return $this->items()->where('source_type', 'food');
    }

    /**
     * Facility items.
     */
    public function facilityItems()
    {
        return $this->items()->where('source_type', 'facility');
    }

    /**
     * Service items.
     */
    public function serviceItems()
    {
        return $this->items()->where('source_type', 'service');
    }

    /**
     * Money still owed (not cancelled, not fully paid).
     */
    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['sent', 'partial', 'overdue'])
            ->whereColumn('paid_amount', '<', 'total');
    }

    /**
     * Unpaid and past the due date (the guest's departure date).
     */
    public function scopeOverdue($query)
    {
        return $query->unpaid()->whereDate('due_date', '<', today());
    }

    /**
     * Matches the invoice number or the guest's name / phone.
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(fn ($q) => $q
            ->where('invoice_number', 'like', "%{$term}%")
            ->orWhereHas('stay.guest', fn ($g) => $g
                ->where('name', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%"))
            ->orWhereHas('facilityBooking', fn ($b) => $b->search($term)));
    }

    /**
     * Who the invoice is billed to: the stay's guest or the event's client.
     * Load stay.guest and facilityBooking first.
     */
    public function getBilledToAttribute(): ?string
    {
        return $this->facility_booking_id ? $this->facilityBooking?->client_name : $this->stay?->guest?->name;
    }

    public function getBilledToPhoneAttribute(): ?string
    {
        return $this->facility_booking_id ? $this->facilityBooking?->contact_phone : $this->stay?->guest?->phone;
    }

    /**
     * "Booking #12" or "EVT-00003".
     */
    public function getSourceLabelAttribute(): string
    {
        return $this->facility_booking_id ? 'Event ' . sprintf('EVT-%05d', $this->facility_booking_id) : 'Booking #' . $this->stay_id;
    }

    /**
     * The page where the folio for this invoice is worked.
     */
    public function getSourceUrlAttribute(): ?string
    {
        return match (true) {
            (bool) $this->facility_booking_id => route('events.show', $this->facility_booking_id),
            (bool) $this->stay_id => route('stays.show', $this->stay_id),
            default => null,
        };
    }

    public function receiptUrl(Payment $payment): ?string
    {
        return match (true) {
            (bool) $this->facility_booking_id => route('events.receipt', [$this->facility_booking_id, $payment]),
            (bool) $this->stay_id => route('stays.receipt', [$this->stay_id, $payment]),
            default => null,
        };
    }

    public function getBalanceAttribute(): float
    {
        return round((float) $this->total - (float) $this->paid_amount, 2);
    }

    /**
     * Status for display: open invoices past their due date show as overdue.
     */
    public function getDisplayStatusAttribute(): InvoiceStatus
    {
        return in_array($this->status, ['sent', 'partial'], true) && $this->balance > 0 && $this->due_date?->isBefore(today())
            ? InvoiceStatus::Overdue
            : InvoiceStatus::from($this->status);
    }

    /**
     * Generate invoice number.
     */
    public static function generateInvoiceNumber()
    {
        $prefix = 'INV-' . date('Ymd') . '-';
        $lastInvoice = self::where('invoice_number', 'like', $prefix . '%')
            ->orderBy('invoice_number', 'desc')
            ->first();
        
        if ($lastInvoice) {
            $lastNumber = intval(substr($lastInvoice->invoice_number, -4));
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }
        
        return $prefix . $nextNumber;
    }

    /**
     * Calculate due amount.
     */
    public function getDueAmountAttribute()
    {
        return $this->total - $this->paid_amount;
    }

    /**
     * Check if invoice is fully paid.
     */
    public function getIsPaidAttribute()
    {
        return $this->status === 'paid' || $this->due_amount <= 0;
    }

    /**
     * Check if invoice is overdue.
     */
    public function getIsOverdueAttribute()
    {
        return $this->due_date < now() && !$this->is_paid;
    }

    /**
     * Record a payment.
     */
    // Payments are recorded through App\Services\BillingService::recordPayment()
}