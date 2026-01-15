<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'stay_id',
        'invoice_number',
        'subtotal',
        'tax_amount',
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
     * Scope for unpaid invoices.
     */
    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['sent', 'partial']);
    }

    /**
     * Scope for overdue invoices.
     */
    public function scopeOverdue($query)
    {
        return $query->whereIn('status', ['sent', 'partial'])
            ->where('due_date', '<', now());
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
    public function recordPayment($amount, $method, $receivedBy, $details = null)
    {
        $this->paid_amount += $amount;
        
        if ($this->paid_amount >= $this->total) {
            $this->status = 'paid';
        } elseif ($this->paid_amount > 0) {
            $this->status = 'partial';
        }
        
        $this->save();
        
        // Create payment record
        return Payment::create([
            'invoice_id' => $this->id,
            'payment_reference' => Payment::generatePaymentReference(),
            'amount' => $amount,
            'method' => $method,
            'payment_details' => $details,
            'received_by' => $receivedBy,
        ]);
    }
}