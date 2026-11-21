<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One closed business day and its figures (see App\Services\NightAudit).
 */
class NightAudit extends Model
{
    protected $fillable = ['business_date', 'run_by', 'run_at', 'summary', 'notes'];

    protected $casts = [
        'business_date' => 'date',
        'run_at' => 'datetime',
        'summary' => 'array',
    ];

    public function runner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'run_by');
    }

    public function figure(string $key, mixed $default = 0): mixed
    {
        return $this->summary[$key] ?? $default;
    }
}
