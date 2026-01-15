<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'payment_reference',
        'amount',
        'method',
        'payment_details',
        'received_by',
        'received_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_details' => 'array',
        'received_at' => 'datetime',
    ];

    /**
     * Invoice for this payment.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * User who received this payment.
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Scope by payment method.
     */
    public function scopeByMethod($query, $method)
    {
        return $query->where('method', $method);
    }

    /**
     * Scope for today's payments.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('received_at', today());
    }

    /**
     * Generate payment reference.
     */
    public static function generatePaymentReference()
    {
        return 'PAY-' . date('Ymd') . '-' . strtoupper(uniqid());
    }

    /**
     * Get payment method options.
     */
    public static function getMethodOptions()
    {
        return [
            'cash' => 'Cash',
            'credit_card' => 'Credit Card',
            'mobile_money' => 'Mobile Money',
            'bank_transfer' => 'Bank Transfer',
            'cheque' => 'Cheque',
        ];
    }

    /**
     * Get payment details based on method.
     */
    public function getPaymentDetailsFormattedAttribute()
    {
        if (!$this->payment_details) {
            return null;
        }
        
        switch ($this->method) {
            case 'mobile_money':
                return "{$this->payment_details['provider']}: {$this->payment_details['phone']}";
            case 'credit_card':
                return "**** **** **** " . substr($this->payment_details['last_four'], -4);
            case 'bank_transfer':
                return "Bank: {$this->payment_details['bank']}, Ref: {$this->payment_details['reference']}";
            case 'cheque':
                return "Cheque #: {$this->payment_details['cheque_number']}";
            default:
                return 'Cash Payment';
        }
    }
}