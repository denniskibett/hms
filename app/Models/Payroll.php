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
        'insurance_relief',
        'employer_nssf',
        'employer_housing_levy',
        'breakdown',
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
        'insurance_relief' => 'decimal:2',
        'employer_nssf' => 'decimal:2',
        'employer_housing_levy' => 'decimal:2',
        'breakdown' => 'array',
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
     * A stipend (staff_profiles.pay_basis) has no statutory lines; everyone else gets the Kenyan
     * deductions, with insurance relief from their insurance premium. Each payslip line is kept in breakdown.
     */
    public function calculate(): static
    {
        $gross = (float) $this->basic_salary + (float) $this->overtime_amount + (float) $this->allowances + (float) $this->bonuses;
        $profile = $this->relationLoaded('staffProfile') ? $this->staffProfile : $this->staffProfile()->first();
        $stipend = ($profile?->pay_basis ?? 'statutory') === 'stipend';

        $f = $stipend
            ? ['paye' => 0.0, 'nssf' => 0.0, 'shif' => 0.0, 'housing_levy' => 0.0, 'insurance_relief' => 0.0, 'employer_nssf' => 0.0, 'employer_housing_levy' => 0.0, 'taxable_pay' => 0.0, 'tax_before_relief' => 0.0, 'personal_relief' => 0.0]
            : self::payslipFigures($gross, (float) ($profile?->insurance_premium ?? 0));

        $breakdown = (array) ($this->breakdown ?? []);
        $this->fill([
            'paye' => $f['paye'], 'nssf' => $f['nssf'], 'shif' => $f['shif'], 'housing_levy' => $f['housing_levy'],
            'tax_deductions' => round($f['paye'] + $f['nssf'] + $f['shif'] + $f['housing_levy'], 2),
            'insurance_relief' => $f['insurance_relief'],
            'employer_nssf' => $f['employer_nssf'],
            'employer_housing_levy' => $f['employer_housing_levy'],
            'breakdown' => array_merge($breakdown, [
                'stipend' => $stipend,
                'taxable_pay' => $f['taxable_pay'],
                'tax_before_relief' => $f['tax_before_relief'],
                'personal_relief' => $f['personal_relief'],
                'rates_label' => $stipend ? 'Stipend: no statutory deductions' : SystemHelper::payroll('rates_label'),
            ]),
        ]);

        return $this;
    }

    /** What the hotel pays in all for this payslip: gross plus its own NSSF and housing levy. */
    public function getEmployerCostAttribute(): float
    {
        return round((float) $this->gross + (float) $this->employer_nssf + (float) $this->employer_housing_levy, 2);
    }

    /** @return array<int, array{name: string, amount: float}> */
    public function allowanceLines(): array
    {
        return array_values($this->breakdown['allowances'] ?? []);
    }

    /** @return array<int, array{name: string, amount: float}> */
    public function deductionLines(): array
    {
        return array_values($this->breakdown['deductions'] ?? []);
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
     * Kenyan statutory deductions on a monthly gross (the legacy four lines).
     *
     * @return array{paye: float, nssf: float, shif: float, housing_levy: float}
     */
    public static function statutoryDeductions(float $gross, float $insurancePremium = 0): array
    {
        $f = self::payslipFigures($gross, $insurancePremium);

        return ['paye' => $f['paye'], 'nssf' => $f['nssf'], 'shif' => $f['shif'], 'housing_levy' => $f['housing_levy']];
    }

    /**
     * Every Kenyan payslip figure on a monthly gross, worked the same way as Breve (KenyaPayroll):
     *
     * - NSSF: rate × pensionable pay up to the upper earnings limit; the employer pays the same again
     * - SHA (SHIF): rate × gross, with a minimum
     * - Affordable Housing Levy: rate × gross; the employer pays the same again
     * - PAYE: banded tax on gross less NSSF, SHIF and the levy (all deductible),
     *   less personal relief and insurance relief (15% of premiums, capped)
     *
     * Rates come from settings (payroll.*) with the 2026 rates as defaults (Breve's config/payroll.php).
     * Check them with your accountant / KRA whenever the law changes (Kenya's budget is in June).
     */
    public static function payslipFigures(float $gross, float $insurancePremium = 0): array
    {
        $zero = ['paye' => 0.0, 'nssf' => 0.0, 'shif' => 0.0, 'housing_levy' => 0.0, 'insurance_relief' => 0.0, 'employer_nssf' => 0.0, 'employer_housing_levy' => 0.0, 'taxable_pay' => 0.0, 'tax_before_relief' => 0.0, 'personal_relief' => 0.0];
        if ($gross <= 0) {
            return $zero;
        }

        $nssf = round(min($gross, SystemHelper::payroll('nssf_upper_limit')) * SystemHelper::payroll('nssf_rate') / 100, 2);
        $shif = round(max($gross * SystemHelper::payroll('shif_rate') / 100, SystemHelper::payroll('shif_minimum')), 2);
        $levy = round($gross * SystemHelper::payroll('housing_levy_rate') / 100, 2);

        $taxable = round(max($gross - $nssf - $shif - $levy, 0), 2);
        $tax = 0.0;
        $lower = 0.0;
        foreach (SystemHelper::payroll('paye_bands') as [$upTo, $rate]) {
            $upper = $upTo === null ? INF : (float) $upTo;
            if ($taxable > $lower) {
                $tax += (min($taxable, $upper) - $lower) * $rate / 100;
            }
            $lower = $upper;
        }
        $tax = round($tax, 2);

        $insuranceRelief = round(min(max($insurancePremium, 0) * SystemHelper::payroll('insurance_relief_rate') / 100, SystemHelper::payroll('insurance_relief_cap')), 2);
        $personal = (float) SystemHelper::payroll('personal_relief');
        $paye = round(max($tax - $personal - $insuranceRelief, 0), 2);

        return [
            'paye' => $paye, 'nssf' => $nssf, 'shif' => $shif, 'housing_levy' => $levy,
            'insurance_relief' => round(min($insuranceRelief, max($tax - $personal, 0)), 2),
            'employer_nssf' => $nssf, 'employer_housing_levy' => $levy,
            'taxable_pay' => $taxable, 'tax_before_relief' => $tax, 'personal_relief' => round(min($personal, $tax), 2),
        ];
    }
}
