<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'period_month',
        'basic_salary',
        'overtime_amount',
        'allowances',
        'bonuses',
        'tax_deductions',
        'other_deductions',
        'status',
        'notes',
        'approved_by',
        'approved_at',
        'paid_at',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'overtime_amount' => 'decimal:2',
        'allowances' => 'decimal:2',
        'bonuses' => 'decimal:2',
        'tax_deductions' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'period_month' => 'date',
    ];

    protected $appends = [
        'gross',
        'net',
    ];

    /**
     * User for this payroll.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * User who approved this payroll.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Calculate gross salary.
     */
    public function getGrossAttribute()
    {
        return $this->basic_salary + $this->overtime_amount + $this->allowances + $this->bonuses;
    }

    /**
     * Calculate net salary.
     */
    public function getNetAttribute()
    {
        return $this->gross - $this->tax_deductions - $this->other_deductions;
    }

    /**
     * Get status options.
     */
    public static function getStatusOptions()
    {
        return [
            'draft' => 'Draft',
            'calculated' => 'Calculated',
            'approved' => 'Approved',
            'paid' => 'Paid',
            'cancelled' => 'Cancelled',
        ];
    }
}