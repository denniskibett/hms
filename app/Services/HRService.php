<?php

namespace App\Services;

use App\Models\User;
use App\Models\StaffProfile;
use App\Models\Shift;
use App\Models\Role;
use App\Models\ShiftAssignment;
use App\Models\Payroll;
use App\Models\LeaveRequest;
use App\Models\Department;
use App\Services\CoreService;
use App\Services\FinanceService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class HRService
{
    public function __construct(
        private CoreService $coreService,
        private FinanceService $financeService
    ) {}
    
    // ==================== STAFF MANAGEMENT ====================
    
    public function hireStaff(array $data): User
    {
        return DB::transaction(function () use ($data) {

            // ✅ Normalize role (default = staff)
            $roleSlug = $data['role'] ?? 'housekeeping';

            $role = Role::where('name', $roleSlug)->firstOrFail();

            // ✅ Create user and attach role
            $user = $this->coreService->createUser($data);
            $user->roles()->sync([$role->id]);

            // ✅ Create or update staff profile (DO NOT skip)
            $user->staffProfile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'department_id'      => $data['department_id'] ?? null,
                    'salary'             => $data['salary'] ?? 0,
                    'hire_date'          => $data['hire_date'] ?? now(),
                    'contract_period'    => $data['contract_period'] ?? 3,
                    'employment_status'  => $data['employment_status'] ?? 'probation',
                    'bank_name'          => $data['bank_name'] ?? null,
                    'bank_branch'        => $data['bank_branch'] ?? null,
                    'account_number'     => $data['account_number'] ?? null,
                    'emergency_contact'  => $data['emergency_contact'] ?? null,
                ]
            );

            // ✅ Safe logging (system-safe)
            $this->coreService->log(
                Auth::id() ?? $user->id,
                'staff_hired',
                "Staff hired: {$user->name} ({$role->display_name})"
            );

            return $user->fresh()->load(['staffProfile', 'roles']);
        });
    }
        
    /**
     * Update staff information.
     */
    public function updateStaff(User $staff, array $data): User
    {
        DB::transaction(function () use ($staff, $data) {
            // Update user info
            $staff->update([
                'name' => $data['name'] ?? $staff->name,
                'email' => $data['email'] ?? $staff->email,
                'phone' => $data['phone'] ?? $staff->phone,
                'status' => $data['status'] ?? $staff->status,
            ]);
            
            // Update staff profile
            if ($staff->staffProfile) {
                $staff->staffProfile->update([
                    'department_id' => $data['department_id'] ?? $staff->staffProfile->department_id,
                    'salary' => $data['salary'] ?? $staff->staffProfile->salary,
                    'employment_status' => $data['employment_status'] ?? $staff->staffProfile->employment_status,
                    'bank_name' => $data['bank_name'] ?? $staff->staffProfile->bank_name,
                    'account_number' => $data['account_number'] ?? $staff->staffProfile->account_number,
                ]);
            }
            
            // Update roles if specified
            if (isset($data['roles'])) {
                $this->coreService->assignRoles($staff, $data['roles']);
            }
        });
        
        $this->coreService->log(auth()->id(), 'staff_updated', 
            "Staff updated: {$staff->name}");
        
        return $staff->fresh()->load(['staffProfile', 'roles']);
    }
    
    /**
     * Terminate staff employment.
     */
    public function terminateStaff(User $staff, string $reason, $terminationDate = null): void
    {
        DB::transaction(function () use ($staff, $reason, $terminationDate) {
            // Update user status
            $staff->update(['status' => 'suspended']);
            
            // Update staff profile
            if ($staff->staffProfile) {
                $staff->staffProfile->update([
                    'employment_status' => 'terminated',
                ]);
            }
            
            // Cancel any future shift assignments
            ShiftAssignment::where('user_id', $staff->id)
                ->whereDate('date', '>', $terminationDate ?? now())
                ->delete();
            
            $this->coreService->log(auth()->id(), 'staff_terminated', 
                "Staff terminated: {$staff->name} - Reason: {$reason}");
        });
    }
    
    /**
     * Get all staff with filters.
     */
    public function getStaff(array $filters = [])
    {
        $query = User::whereHas('roles', function ($q) {
            $q->where('name', '!=', 'guest');
        })->with(['staffProfile.department', 'roles']);
        
        // Apply filters
        if (isset($filters['department_id'])) {
            $query->whereHas('staffProfile', function ($q) use ($filters) {
                $q->where('department_id', $filters['department_id']);
            });
        }
        
        if (isset($filters['employment_status'])) {
            $query->whereHas('staffProfile', function ($q) use ($filters) {
                $q->where('employment_status', $filters['employment_status']);
            });
        }
        
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('email', 'like', "%{$filters['search']}%");
            });
        }
        
        return $query->orderBy('name')
                     ->paginate($filters['per_page'] ?? 20);
    }
    
    /**
     * Get staff attendance summary.
     */
    public function getStaffAttendance(User $staff, $startDate, $endDate): array
    {
        $assignments = ShiftAssignment::where('user_id', $staff->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->with('shift')
            ->get();
        
        $summary = [
            'total_days' => $assignments->count(),
            'present' => $assignments->where('status', 'completed')->count(),
            'absent' => $assignments->where('status', 'absent')->count(),
            'on_leave' => $assignments->where('status', 'on_leave')->count(),
            'overtime_hours' => $assignments->sum('overtime_hours'),
            'details' => $assignments,
        ];
        
        return $summary;
    }
    
    // ==================== SHIFT MANAGEMENT ====================
    
    /**
     * Create new shift.
     */
    public function createShift(array $data): Shift
    {
        $shift = Shift::create([
            'name' => $data['name'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'type' => $data['type'] ?? 'custom',
            'is_active' => $data['is_active'] ?? true,
        ]);
        
        $this->coreService->log(auth()->id(), 'shift_created', 
            "Shift created: {$shift->name} ({$shift->start_time} - {$shift->end_time})");
        
        return $shift;
    }
    
    /**
     * Assign staff to shift.
     */
    public function assignShift(User $staff, Shift $shift, $date, array $data = []): ShiftAssignment
    {
        // Check if already assigned
        $existing = ShiftAssignment::where('user_id', $staff->id)
            ->where('shift_id', $shift->id)
            ->whereDate('date', $date)
            ->first();
        
        if ($existing) {
            throw new \Exception("Staff already assigned to this shift on {$date}");
        }
        
        $assignment = ShiftAssignment::create([
            'user_id' => $staff->id,
            'shift_id' => $shift->id,
            'date' => $date,
            'status' => 'scheduled',
            'notes' => $data['notes'] ?? null,
        ]);
        
        $this->coreService->log(auth()->id(), 'shift_assigned', 
            "Shift assigned: {$staff->name} to {$shift->name} on {$date}");
        
        return $assignment;
    }
    
    /**
     * Bulk assign shifts for period.
     */
    public function bulkAssignShifts(array $staffIds, Shift $shift, $startDate, $endDate, array $excludeDays = []): array
    {
        $period = CarbonPeriod::create($startDate, $endDate);
        $assignments = [];
        
        foreach ($period as $date) {
            // Skip excluded days
            if (in_array($date->dayOfWeek, $excludeDays)) {
                continue;
            }
            
            foreach ($staffIds as $staffId) {
                $staff = User::find($staffId);
                if ($staff) {
                    try {
                        $assignment = $this->assignShift($staff, $shift, $date->format('Y-m-d'));
                        $assignments[] = $assignment;
                    } catch (\Exception $e) {
                        // Log but continue
                        continue;
                    }
                }
            }
        }
        
        $this->coreService->log(auth()->id(), 'shifts_bulk_assigned', 
            "Bulk assigned shifts for " . count($staffIds) . " staff from {$startDate} to {$endDate}");
        
        return $assignments;
    }
    
    /**
     * Update shift assignment status.
     */
    public function updateShiftStatus(ShiftAssignment $assignment, string $status, array $data = []): ShiftAssignment
    {
        $assignment->update([
            'status' => $status,
            'overtime_hours' => $data['overtime_hours'] ?? $assignment->overtime_hours,
            'notes' => $data['notes'] ?? $assignment->notes,
        ]);
        
        $this->coreService->log(auth()->id(), 'shift_status_updated', 
            "Shift status updated: {$assignment->id} -> {$status}");
        
        return $assignment->fresh();
    }
    
    /**
     * Get shift schedule for period.
     */
    public function getShiftSchedule($startDate, $endDate, $departmentId = null)
    {
        $query = ShiftAssignment::with(['user', 'shift'])
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->orderBy('shift_id');
        
        if ($departmentId) {
            $query->whereHas('user.staffProfile', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }
        
        $assignments = $query->get();
        
        // Group by date for easier display
        $schedule = [];
        foreach ($assignments as $assignment) {
            $date = $assignment->date->format('Y-m-d');
            if (!isset($schedule[$date])) {
                $schedule[$date] = [];
            }
            $schedule[$date][] = $assignment;
        }
        
        return [
            'period' => ['start' => $startDate, 'end' => $endDate],
            'schedule' => $schedule,
            'summary' => [
                'total_assignments' => $assignments->count(),
                'by_status' => $assignments->groupBy('status')->map->count(),
                'by_department' => $assignments->groupBy('user.staffProfile.department.name')->map->count(),
            ],
        ];
    }
    
    // ==================== PAYROLL MANAGEMENT ====================
    
    /**
     * Process payroll for period.
     */
    public function processPayroll($month, $year): array
    {
        $period = Carbon::create($year, $month, 1);
        $payrolls = [];
        
        // Get all active staff
        $staff = User::whereHas('staffProfile', function ($q) {
            $q->where('employment_status', '!=', 'terminated');
        })->where('status', 'active')->get();
        
        foreach ($staff as $employee) {
            $payroll = $this->calculateEmployeePayroll($employee, $period);
            $payrolls[] = $payroll;
        }
        
        $this->coreService->log(auth()->id(), 'payroll_processed', 
            "Payroll processed for {$period->format('F Y')}: " . count($payrolls) . " employees");
        
        return [
            'period' => $period->format('F Y'),
            'payrolls' => $payrolls,
            'summary' => [
                'total_gross' => collect($payrolls)->sum('gross'),
                'total_deductions' => collect($payrolls)->sum('total_deductions'),
                'total_net' => collect($payrolls)->sum('net'),
                'employee_count' => count($payrolls),
            ],
        ];
    }
    
    /**
     * Calculate payroll for single employee.
     */
    private function calculateEmployeePayroll(User $employee, Carbon $period): array
    {
        $staffProfile = $employee->staffProfile;
        $baseSalary = $staffProfile->salary;
        
        // Get shift assignments for the month
        $assignments = ShiftAssignment::where('user_id', $employee->id)
            ->whereYear('date', $period->year)
            ->whereMonth('date', $period->month)
            ->get();
        
        // Calculate overtime
        $overtimeHours = $assignments->sum('overtime_hours');
        $overtimeRate = $baseSalary / 176; // Assuming 176 hours/month = 22 days * 8 hours
        $overtimeAmount = $overtimeHours * $overtimeRate * 1.5; // 1.5x for overtime
        
        // Calculate deductions for leave
        $leaveDays = $this->getLeaveDaysInMonth($employee, $period);
        $dailyRate = $baseSalary / 30; // Simplified
        $leaveDeduction = $leaveDays * $dailyRate;
        
        // Other deductions (tax, NHIF, NSSF in Kenya)
        $tax = $this->calculatePAYE($baseSalary + $overtimeAmount);
        $nhif = $this->calculateNHIF($baseSalary + $overtimeAmount);
        $nssf = $this->calculateNSSF($baseSalary + $overtimeAmount);
        
        $gross = $baseSalary + $overtimeAmount;
        $totalDeductions = $tax + $nhif + $nssf + $leaveDeduction;
        $net = $gross - $totalDeductions;
        
        return [
            'employee' => $employee,
            'period' => $period->format('Y-m'),
            'base_salary' => $baseSalary,
            'overtime' => [
                'hours' => $overtimeHours,
                'rate' => $overtimeRate,
                'amount' => $overtimeAmount,
            ],
            'deductions' => [
                'tax' => $tax,
                'nhif' => $nhif,
                'nssf' => $nssf,
                'leave' => $leaveDeduction,
                'total' => $totalDeductions,
            ],
            'gross' => $gross,
            'net' => $net,
            'attendance' => [
                'total_shifts' => $assignments->count(),
                'completed' => $assignments->where('status', 'completed')->count(),
                'absent' => $assignments->where('status', 'absent')->count(),
                'leave_days' => $leaveDays,
            ],
        ];
    }
    
    /**
     * Get leave days taken in month.
     */
    private function getLeaveDaysInMonth(User $employee, Carbon $period): int
    {
        return LeaveRequest::where('user_id', $employee->id)
            ->where('status', 'approved')
            ->where(function ($q) use ($period) {
                $q->whereYear('start_date', $period->year)
                  ->whereMonth('start_date', $period->month)
                  ->orWhereYear('end_date', $period->year)
                  ->whereMonth('end_date', $period->month);
            })
            ->get()
            ->sum(function ($leave) use ($period) {
                $start = max($leave->start_date, $period->startOfMonth());
                $end = min($leave->end_date, $period->endOfMonth());
                return $start->diffInDays($end) + 1;
            });
    }
    
    /**
     * Calculate PAYE (Kenya tax).
     */
    private function calculatePAYE(float $gross): float
    {
        // Simplified PAYE calculation for Kenya
        // Real implementation should follow KRA tax bands
        if ($gross <= 24000) return 0;
        if ($gross <= 32333) return ($gross - 24000) * 0.25;
        if ($gross <= 500000) return ($gross - 32333) * 0.30 + 2083;
        return ($gross - 500000) * 0.325 + 141158;
    }
    
    /**
     * Calculate NHIF (Kenya health insurance).
     */
    private function calculateNHIF(float $gross): float
    {
        // NHIF rates in Kenya
        $rates = [
            0 => 150, 6000 => 300, 8000 => 400, 12000 => 500,
            15000 => 600, 20000 => 750, 25000 => 850, 30000 => 900,
            35000 => 950, 40000 => 1000, 45000 => 1100, 50000 => 1200,
            60000 => 1300, 70000 => 1400, 80000 => 1500, 90000 => 1600,
            100000 => 1700
        ];
        
        $rate = 1700; // Default for 100,000+
        foreach ($rates as $limit => $premium) {
            if ($gross <= $limit) {
                $rate = $premium;
                break;
            }
        }
        
        return $rate;
    }
    
    /**
     * Calculate NSSF (Kenya pension).
     */
    private function calculateNSSF(float $gross): float
    {
        // NSSF Tier I: 6% of first 7,000 (employee + employer)
        // Simplified: Employee pays half = 3%
        $tierI = min($gross, 7000) * 0.03;
        
        // NSSF Tier II: 6% of next 29,000 (36,000 - 7,000)
        if ($gross > 7000) {
            $tierIIAmount = min($gross, 36000) - 7000;
            $tierII = $tierIIAmount * 0.06; // Employee + employer
        } else {
            $tierII = 0;
        }
        
        return $tierI + ($tierII / 2); // Employee pays half of total
    }
    
    /**
     * Generate payslip and record payment.
     */
    public function generatePayslip(array $payrollData): Payroll
    {
        return DB::transaction(function () use ($payrollData) {
            $payroll = Payroll::create([
                'user_id' => $payrollData['employee']->id,
                'period_month' => $payrollData['period'],
                'basic_salary' => $payrollData['base_salary'],
                'overtime_amount' => $payrollData['overtime']['amount'],
                'allowances' => 0, // Could be added
                'bonuses' => 0, // Could be added
                'tax_deductions' => $payrollData['deductions']['tax'],
                'other_deductions' => $payrollData['deductions']['nhif'] + 
                                     $payrollData['deductions']['nssf'] + 
                                     $payrollData['deductions']['leave'],
                'status' => 'calculated',
                'notes' => "Payroll for {$payrollData['period']}",
                'created_by' => auth()->id(),
            ]);
            
            $this->coreService->log(auth()->id(), 'payslip_generated', 
                "Payslip generated for {$payrollData['employee']->name} - {$payrollData['period']}");
            
            return $payroll;
        });
    }
    
    /**
     * Approve and pay payroll.
     */
    public function approvePayroll(Payroll $payroll, User $approver): Payroll
    {
        return DB::transaction(function () use ($payroll, $approver) {
            $payroll->update([
                'status' => 'approved',
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);
            
            // Record payment as expense
            $this->financeService->recordExpense([
                'description' => "Salary payment for {$payroll->user->name} - {$payroll->period_month}",
                'amount' => $payroll->net,
                'category' => 'payroll',
                'payment_method' => 'bank_transfer',
                'paid_to' => $payroll->user->name,
                'reference_number' => "PAYROLL-{$payroll->id}",
            ]);
            
            // Update payroll as paid
            $payroll->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);
            
            $this->coreService->log($approver->id, 'payroll_approved', 
                "Payroll approved and paid: {$payroll->user->name} - {$payroll->period_month}");
            
            return $payroll->fresh();
        });
    }
    
    // ==================== LEAVE MANAGEMENT ====================
    
    /**
     * Apply for leave.
     */
    public function applyLeave(User $staff, array $data): LeaveRequest
    {
        $leaveRequest = LeaveRequest::create([
            'user_id' => $staff->id,
            'leave_type' => $data['leave_type'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'reason' => $data['reason'],
            'status' => 'pending',
        ]);
        
        // Update shift assignments to on_leave for the period
        ShiftAssignment::where('user_id', $staff->id)
            ->whereBetween('date', [$data['start_date'], $data['end_date']])
            ->update(['status' => 'on_leave']);
        
        $this->coreService->log($staff->id, 'leave_applied', 
            "Leave applied: {$staff->name} from {$data['start_date']} to {$data['end_date']}");
        
        return $leaveRequest;
    }
    
    /**
     * Approve leave request.
     */
    public function approveLeave(LeaveRequest $leaveRequest, User $approver, string $notes = null): LeaveRequest
    {
        $leaveRequest->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);
        
        $this->coreService->log($approver->id, 'leave_approved', 
            "Leave approved: {$leaveRequest->user->name} #{$leaveRequest->id}");
        
        return $leaveRequest->fresh();
    }
    
    /**
     * Reject leave request.
     */
    public function rejectLeave(LeaveRequest $leaveRequest, User $rejecter, string $reason): LeaveRequest
    {
        $leaveRequest->update([
            'status' => 'rejected',
            'approved_by' => $rejecter->id,
            'approved_at' => now(),
            'approval_notes' => $reason,
        ]);
        
        // Revert shift assignments
        ShiftAssignment::where('user_id', $leaveRequest->user_id)
            ->whereBetween('date', [$leaveRequest->start_date, $leaveRequest->end_date])
            ->where('status', 'on_leave')
            ->update(['status' => 'scheduled']);
        
        $this->coreService->log($rejecter->id, 'leave_rejected', 
            "Leave rejected: {$leaveRequest->user->name} #{$leaveRequest->id} - Reason: {$reason}");
        
        return $leaveRequest->fresh();
    }
    
    /**
     * Get staff leave balance.
     */
    public function getLeaveBalance(User $staff): array
    {
        $currentYear = now()->year;
        
        // Get annual leave entitlement (typically 21 days)
        $entitlement = 21;
        
        // Get used leave this year
        $usedLeave = LeaveRequest::where('user_id', $staff->id)
            ->where('leave_type', 'annual')
            ->where('status', 'approved')
            ->whereYear('start_date', $currentYear)
            ->get()
            ->sum('duration_days');
        
        // Get pending leave
        $pendingLeave = LeaveRequest::where('user_id', $staff->id)
            ->where('status', 'pending')
            ->whereYear('start_date', $currentYear)
            ->get()
            ->sum('duration_days');
        
        return [
            'year' => $currentYear,
            'entitlement' => $entitlement,
            'used' => $usedLeave,
            'pending' => $pendingLeave,
            'remaining' => $entitlement - $usedLeave,
            'breakdown' => LeaveRequest::where('user_id', $staff->id)
                ->whereYear('start_date', $currentYear)
                ->groupBy('leave_type', 'status')
                ->selectRaw('leave_type, status, SUM(duration_days) as days')
                ->get(),
        ];
    }
}