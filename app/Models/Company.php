<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A company account (city ledger): stays and events billed to the company,
 * paid later against a statement. Negotiated rate = % off room rates.
 */
class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'kra_pin', 'contact_name', 'email', 'phone', 'address',
        'credit_limit', 'payment_terms_days', 'rate_discount_percent', 'is_active', 'notes',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'payment_terms_days' => 'integer',
        'rate_discount_percent' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function stays(): HasMany
    {
        return $this->hasMany(Stay::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** What the company owes on all open invoices billed to it. */
    public function balance(?int $exceptInvoiceId = null): float
    {
        return round((float) $this->invoices()->unpaid()
            ->when($exceptInvoiceId, fn ($q) => $q->whereKeyNot($exceptInvoiceId))
            ->get()->sum(fn (Invoice $i) => $i->balance), 2);
    }

    public function hasCreditLimit(): bool
    {
        return (float) $this->credit_limit > 0;
    }

    /** Room rate after the negotiated discount. */
    public function applyRate(float $nightly): float
    {
        $discount = (float) $this->rate_discount_percent;

        return $discount > 0 ? round($nightly * (1 - $discount / 100), 2) : $nightly;
    }

    /**
     * Open invoices by age past their due date: [label => amount].
     */
    public function ageing(): array
    {
        $buckets = ['Not yet due' => 0.0, '1–30 days' => 0.0, '31–60 days' => 0.0, '61–90 days' => 0.0, 'Over 90 days' => 0.0];
        foreach ($this->invoices()->unpaid()->get() as $invoice) {
            $late = $invoice->due_date && $invoice->due_date->isPast() ? (int) $invoice->due_date->diffInDays(today()) : 0;
            $bucket = match (true) {
                $late <= 0 => 'Not yet due',
                $late <= 30 => '1–30 days',
                $late <= 60 => '31–60 days',
                $late <= 90 => '61–90 days',
                default => 'Over 90 days',
            };
            $buckets[$bucket] = round($buckets[$bucket] + $invoice->balance, 2);
        }

        return $buckets;
    }
}
