<?php

namespace App\Models;

use App\Helpers\SystemHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * One employee's pay for one month.
 *
 * gross and net are virtual columns:
 *   gross = basic + overtime + allowances + bonuses
 *   net   = gross - tax_deductions - other_deductions
 * tax_deductions is the total of the statutory deductions (PAYE, NSSF, SHA,
 * Housing Levy), kept separately for the payslip.
 *
 * Status: draft → approved → paid (draft can be cancelled).
 */
class Payroll extends Model
{
    use HasFactory;

    public const STATUSES = [
        'draft' => 'Draft',
        'approved' => 'Approved',
        'paid' => 'Paid',
        'cancelled' => 'Cancelled',
    ];

    protected $fillable = [
        'user_id',
        'period_month',
        'basic_salary',
        'overtime_hours',
        'overtime_amount',
        'allowances',
        'bonuses',
        'tax_deductions',
        'paye',
        'nssf',
        'shif',
        'housing_levy',
        'other_deductions',
        'status',
        'notes',
        'approved_by',
        'approved_at',
        'paid_at',
        'paid_by',
    ];

    protected $casts = [
        'period_month' => 'date',
        'basic_salary' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'overtime_amount' => 'decimal:2',
        'allowances' => 'decimal:2',
        'bonuses' => 'decimal:2',
        'gross' => 'decimal:2',
        'tax_deductions' => 'decimal:2',
        'paye' => 'decimal:2',
        'nssf' => 'decimal:2',
        'shif' => 'decimal:2',
        'housing_levy' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'net' => 'decimal:2',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
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

    public function payer()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function scopeForMonth(Builder $query, string $month): Builder
    {
        return $query->whereDate('period_month', $month . '-01');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    /**
     * Fill in gross-dependent figures from the editable ones. Call before saving.
     */
    public function calculate(): static
    {
        $gross = (float) $this->basic_salary + (float) $this->overtime_amount + (float) $this->allowances + (float) $this->bonuses;
        $deductions = self::statutoryDeductions($gross);

        $this->fill($deductions + ['tax_deductions' => array_sum($deductions)]);

        return $this;
    }

    /**
     * Overtime pay for a number of hours at the configured multiple of the hourly rate.
     */
    public static function overtimePay(float $basicSalary, float $hours): float
    {
        $hourly = $basicSalary / max(SystemHelper::payroll('monthly_hours'), 1);

        return round($hours * $hourly * SystemHelper::payroll('overtime_multiplier'), 2);
    }

    /**
     * Kenyan statutory deductions on a monthly gross.
     *
     * - NSSF: rate × pensionable pay up to the upper earnings limit
     * - SHA (SHIF): rate × gross, with a minimum
     * - Affordable Housing Levy: rate × gross
     * - PAYE: banded tax on gross less NSSF, SHIF and the levy (all deductible),
     *   less personal relief
     *
     * Rates come from settings (payroll.*) with the 2025 rates as defaults.
     * Check them with your accountant / KRA whenever the law changes.
     *
     * @return array{paye: float, nssf: float, shif: float, housing_levy: float}
     */
    public static function statutoryDeductions(float $gross): array
    {
        if ($gross <= 0) {
            return ['paye' => 0.0, 'nssf' => 0.0, 'shif' => 0.0, 'housing_levy' => 0.0];
        }

        $nssf = round(min($gross, SystemHelper::payroll('nssf_upper_limit')) * SystemHelper::payroll('nssf_rate') / 100, 2);
        $shif = round(max($gross * SystemHelper::payroll('shif_rate') / 100, SystemHelper::payroll('shif_minimum')), 2);
        $levy = round($gross * SystemHelper::payroll('housing_levy_rate') / 100, 2);

        $taxable = max($gross - $nssf - $shif - $levy, 0);
        $tax = 0.0;
        $lower = 0.0;
        foreach (SystemHelper::payroll('paye_bands') as [$upTo, $rate]) {
            $upper = $upTo === null ? INF : (float) $upTo;
            if ($taxable > $lower) {
                $tax += (min($taxable, $upper) - $lower) * $rate / 100;
            }
            $lower = $upper;
        }
        $paye = round(max($tax - SystemHelper::payroll('personal_relief'), 0), 2);

        return ['paye' => $paye, 'nssf' => $nssf, 'shif' => $shif, 'housing_levy' => $levy];
    }
}
