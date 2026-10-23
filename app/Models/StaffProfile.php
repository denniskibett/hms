<?php

namespace App\Models;

use App\Helpers\SystemHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * An employee's HR record. The person (name, phone, email, login, role) is
 * the linked user; this holds the job, pay and statutory details.
 */
class StaffProfile extends Model
{
    use HasFactory;

    public const EMPLOYMENT_STATUSES = [
        'probation' => 'Probation',
        'permanent' => 'Permanent',
        'contract' => 'Contract',
        'internship' => 'Internship',
        'terminated' => 'Left',
    ];

    protected $fillable = [
        'user_id',
        'department_id',
        'job_title',
        'id_number',
        'kra_pin',
        'nssf_number',
        'shif_number',
        'salary',
        'hire_date',
        'end_date',
        'contract_period',
        'employment_status',
        'bank_name',
        'bank_branch',
        'account_number',
        'emergency_contact',
        'notes',
    ];

    protected $casts = [
        'emergency_contact' => 'array',
        'hire_date' => 'date',
        'end_date' => 'date',
        'salary' => 'decimal:2',
        'contract_period' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function shiftAssignments()
    {
        return $this->hasMany(ShiftAssignment::class, 'user_id', 'user_id');
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'user_id', 'user_id');
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class, 'user_id', 'user_id');
    }

    /**
     * Still employed.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('employment_status', '!=', 'terminated');
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(fn ($q) => $q
            ->where('job_title', 'like', "%{$term}%")
            ->orWhere('id_number', 'like', "%{$term}%")
            ->orWhereHas('user', fn ($u) => $u
                ->where('name', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")));
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->employment_status !== 'terminated';
    }

    public function getStatusLabelAttribute(): string
    {
        return self::EMPLOYMENT_STATUSES[$this->employment_status] ?? ucfirst((string) $this->employment_status);
    }

    /**
     * End of a fixed-term contract or internship.
     */
    public function getContractEndDateAttribute()
    {
        return in_array($this->employment_status, ['contract', 'internship'], true) && $this->contract_period
            ? $this->hire_date->copy()->addMonths($this->contract_period)
            : null;
    }

    /**
     * Annual leave left this year: the yearly entitlement less approved annual leave.
     */
    public function annualLeaveBalance(?int $year = null): int
    {
        $year ??= now()->year;
        $taken = (int) LeaveRequest::where('user_id', $this->user_id)
            ->where('leave_type', 'annual')
            ->where('status', 'approved')
            ->whereYear('start_date', $year)
            ->sum('duration_days');

        return SystemHelper::annualLeaveDays() - $taken;
    }

    /**
     * Fields for the add / edit modal.
     */
    public function formData(): array
    {
        $user = $this->user;

        return [
            'id' => $this->id,
            'name' => $user->name,
            'phone' => $user->phone ?? '',
            'email' => $user->email ?? '',
            'role' => $user->roles->first(fn ($r) => $r->name !== 'guest')?->name ?? '',
            'department_id' => $this->department_id ?? '',
            'job_title' => $this->job_title ?? '',
            'id_number' => $this->id_number ?? '',
            'kra_pin' => $this->kra_pin ?? '',
            'nssf_number' => $this->nssf_number ?? '',
            'shif_number' => $this->shif_number ?? '',
            'salary' => (float) $this->salary,
            'hire_date' => $this->hire_date?->toDateString(),
            'employment_status' => $this->employment_status,
            'contract_period' => $this->contract_period,
            'bank_name' => $this->bank_name ?? '',
            'bank_branch' => $this->bank_branch ?? '',
            'account_number' => $this->account_number ?? '',
            'emergency_contact' => array_merge(['name' => '', 'phone' => '', 'relationship' => ''], $this->emergency_contact ?? []),
            'notes' => $this->notes ?? '',
        ];
    }
}
