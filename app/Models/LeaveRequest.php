<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Time off. duration_days is a virtual column (calendar days, inclusive).
 * Status: pending → approved / rejected; pending or approved can be cancelled.
 */
class LeaveRequest extends Model
{
    use HasFactory;

    public const TYPES = [
        'annual' => 'Annual',
        'sick' => 'Sick',
        'maternity' => 'Maternity',
        'paternity' => 'Paternity',
        'unpaid' => 'Unpaid',
        'other' => 'Other',
    ];

    public const STATUSES = [
        'pending' => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'cancelled' => 'Cancelled',
    ];

    protected $fillable = [
        'user_id',
        'leave_type',
        'start_date',
        'end_date',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'approval_notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'duration_days' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function staffProfile()
    {
        return $this->hasOne(StaffProfile::class, 'user_id', 'user_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Pending or approved leave touching the given dates.
     */
    public function scopeOverlapping(Builder $query, $from, $to): Builder
    {
        return $query->whereIn('status', ['pending', 'approved'])
            ->whereDate('start_date', '<=', $to)
            ->whereDate('end_date', '>=', $from);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->leave_type] ?? $this->leave_type;
    }

    public function getDaysAttribute(): int
    {
        return (int) $this->start_date->diffInDays($this->end_date) + 1;
    }

    public function getStatusClassesAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400',
            'approved' => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-500',
            'rejected' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
            default => 'bg-gray-100 text-gray-600 dark:bg-gray-500/15 dark:text-gray-400',
        };
    }
}
