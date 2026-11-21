<?php

namespace App\Models;

use App\Services\Etims\Etims;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * KRA eTIMS receipt for a paid invoice: the signature KRA returned, printed
 * on the invoice and receipt with its QR code.
 */
class EtimsReceipt extends Model
{
    protected $fillable = [
        'invoice_id', 'invc_no', 'status', 'rcpt_no', 'tot_rcpt_no', 'intrl_data', 'rcpt_sign',
        'sdc_id', 'mrc_no', 'receipt_at', 'attempts', 'error', 'request', 'response',
    ];

    protected $casts = [
        'receipt_at' => 'datetime',
        'request' => 'array',
        'response' => 'array',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    /** Link KRA's verification page for this receipt (what the QR code holds). */
    public function getVerifyUrlAttribute(): ?string
    {
        return $this->isSent() ? Etims::verifyUrl($this->rcpt_sign) : null;
    }
}
