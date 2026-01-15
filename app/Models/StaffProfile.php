<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'department_id',
        'salary',
        'hire_date',
        'contract_period',
        'employment_status',
        'bank_name',
        'bank_branch',
        'account_number',
        'emergency_contact',
    ];

    protected $casts = [
        'emergency_contact' => 'array',
        'hire_date' => 'date',
    ];

    /**
     * User associated with this staff profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Department of this staff member.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Shift assignments for this staff member.
     */
    public function shiftAssignments()
    {
        return $this->hasManyThrough(ShiftAssignment::class, User::class, 'id', 'user_id');
    }

    /**
     * Leave requests for this staff member.
     */
    public function leaveRequests()
    {
        return $this->hasManyThrough(LeaveRequest::class, User::class, 'id', 'user_id');
    }

    /**
     * Payroll records for this staff member.
     */
    public function payrolls()
    {
        return $this->hasManyThrough(Payroll::class, User::class, 'id', 'user_id');
    }

    /**
     * Current payroll record.
     */
    public function currentPayroll()
    {
        return $this->payrolls()
            ->where('period_month', now()->format('Y-m'))
            ->first();
    }

    /**
     * Scope for active staff.
     */
    public function scopeActive($query)
    {
        return $query->where('employment_status', '!=', 'terminated');
    }

    /**
     * Scope by department.
     */
    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Get employment status options.
     */
    public static function getEmploymentStatusOptions()
    {
        return [
            'internship' => 'Internship',
            'probation' => 'Probation',
            'permanent' => 'Permanent',
            'contract' => 'Contract',
            'terminated' => 'Terminated',
        ];
    }

    /**
     * Calculate service duration in months.
     */
    public function getServiceDurationAttribute()
    {
        return now()->diffInMonths($this->hire_date);
    }

    /**
     * Calculate contract end date.
     */
    public function getContractEndDateAttribute()
    {
        if ($this->employment_status === 'contract') {
            return $this->hire_date->addMonths($this->contract_period);
        }
        
        return null;
    }
}