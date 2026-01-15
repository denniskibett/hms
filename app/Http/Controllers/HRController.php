<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\Shift;
use App\Models\Payroll;
use App\Models\LeaveRequest;
use App\Models\Department;
use App\Models\ActivityLog;
use App\Models\AuditTrail;
use App\Models\StaffProfile;
use App\Services\HRService;
use App\Services\CoreService;
use Carbon\Carbon;

class HRController extends Controller
{
    public function __construct(
        private HRService $hrService,
        private CoreService $coreService
    ) {}
    
    /**
     * HR Dashboard
     */
    public function index()
    {
        // $this->authorize('viewHR', User::class);
        
        // Calculate stats
        $stats = [
            'total_staff' => User::whereHas('roles', function($q) {
                $q->where('name', 'staff')->orWhere('name', 'manager')->orWhere('name', 'admin');
            })->where('status', 'active')->count(),
            'on_leave' => LeaveRequest::where('status', 'approved')
                ->whereDate('start_date', '<=', today())
                ->whereDate('end_date', '>=', today())
                ->count(),
            'month_payroll' => Payroll::where('period_month', now()->format('Y-m'))
                ->where('status', 'paid')
                ->sum('gross'),
            'departments' => Department::count(),
        ];
        
        // Department stats
        $departmentStats = Department::withCount(['staff' => function($q) {
            $q->whereHas('user', function($q) {
                $q->where('status', 'active');
            });
        }])->get();
        
        // Recent activities
        $recentActivities = AuditTrail::where(function($q){
            $q->where('action', 'like', 'staff_%')
            ->orWhere('action', 'like', 'payroll_%')
            ->orWhere('action', 'like', 'leave_%');
        })
        ->with('user')
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get();

        
        // Upcoming birthdays
        $upcomingBirthdays = User::where('status', 'active')
            ->whereMonth('dob', now()->month)
            ->whereHas('roles', function($q) {
                $q->where('name', '!=', 'guest');
            })
            ->orderByRaw('DAY(dob)')
            ->take(3)
            ->get();
        
        // Contract renewals
        $contractRenewals = StaffProfile::where('employment_status', 'contract')
            ->whereDate('hire_date', '>=', now()->subMonths(11))
            ->with('user')
            ->get()
            ->map(function($profile) {
                $profile->contract_end = $profile->hire_date->addMonths($profile->contract_period);
                return $profile;
            })
            ->sortBy('contract_end')
            ->take(3);
        
        // Upcoming leave
        $upcomingLeave = LeaveRequest::where('status', 'approved')
            ->whereDate('start_date', '>=', today())
            ->whereDate('start_date', '<=', today()->addDays(7))
            ->with('user')
            ->take(3)
            ->get();

        $departments = Department::all();
        $positions = Role::where('name', '!=', 'guest')->get();
        $employees = User::whereHas('roles', function($q) {
            $q->where('name', '!=', 'guest');
        })->where('status', 'active')->get();

                
        return view('hr.index', compact(
            'stats',
            'departmentStats',
            'recentActivities',
            'upcomingBirthdays',
            'contractRenewals',
            'upcomingLeave',
            'departments',
            'positions',
            'employees'
        ));
    }
    


    public function staff(Request $request)
    {
        $this->authorize('viewStaff', User::class);
        
        $staff = $this->hrService->getStaff($request->all());
        $departments = Department::all();
        
        return view('hr.staff.index', compact('staff', 'departments'));
    }
    
    /**
     * Show Staff Profile
     */
    public function show(User $staff)
    {
        $this->authorize('view', $staff);
        
        $staff->load([
            'staffProfile.department',
            'roles',
            'payrolls' => function($q) {
                $q->orderBy('period_month', 'desc')->take(6);
            },
            'leaveRequests' => function($q) {
                $q->orderBy('created_at', 'desc')->take(5);
            },
            'shiftAssignments.shift' => function($q) {
                $q->where('date', '>=', now()->startOfWeek())
                  ->where('date', '<=', now()->endOfWeek());
            }
        ]);
        
        // Calculate attendance for current month
        $attendance = $this->hrService->getStaffAttendance(
            $staff,
            now()->startOfMonth(),
            now()->endOfMonth()
        );
        
        // Get leave balance
        $leaveBalance = $this->hrService->getLeaveBalance($staff);
        
        // Get current payroll
        $currentPayroll = $staff->payrolls()
            ->where('period_month', now()->format('Y-m'))
            ->first();
        $departments = Department::all();
        
        return view('hr.show', compact(
            'staff',
            'attendance',
            'leaveBalance',
            'currentPayroll',
            'departments'
        ));
    }
    
    /**
     * Hire Staff Form
     */
    public function hire(Request $request)
    {
        $this->authorize('hire', User::class);
        
        $roles = Role::where('name', '!=', 'guest')->get();
        $departments = Department::all();
        
        return view('hr.staff.hire', compact('roles', 'departments'));
    }
    
    /**
     * Store New Staff
     */
    public function storeStaff(Request $request)
    {
        $this->authorize('hire', User::class);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'dob' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'role' => 'required|string|exists:roles,name',
            'department_id' => 'required|exists:departments,id',
            'salary' => 'required|numeric|min:0',
            'hire_date' => 'required|date',
            'employment_status' => 'required|in:internship,probation,permanent,contract',
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:50',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'contract_period' => 'nullable|integer|min:1',
        ]);
        
        // Format emergency contact
        if ($request->filled('emergency_contact_name')) {
            $validated['emergency_contact'] = [
                'name' => $validated['emergency_contact_name'],
                'phone' => $validated['emergency_contact_phone'],
                'relationship' => $request->input('emergency_contact_relationship', 'relative'),
            ];
        }
        
        try {
            $staff = $this->hrService->hireStaff($validated);
            
            return redirect()->route('hr.staff.show', $staff)
                ->with('success', 'Staff hired successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error hiring staff: ' . $e->getMessage());
        }
    }
    

    public function editStaff(User $staff)
    {
        $this->authorize('update', $staff);
        
        $staff->load('staffProfile');
        $roles = Role::where('name', '!=', 'guest')->get();
        $departments = Department::all();
        
        return view('hr.staff.edit', compact('staff', 'roles', 'departments'));
    }
    
    /**
     * Update Staff
     */
    public function updateStaff(Request $request, User $staff)
    {
        $this->authorize('update', $staff);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $staff->id,
            'phone' => 'required|string|max:20',
            'status' => 'required|in:active,suspended',
            'role' => 'required|string|exists:roles,name',
            'department_id' => 'required|exists:departments,id',
            'salary' => 'required|numeric|min:0',
            'employment_status' => 'required|in:internship,probation,permanent,contract,terminated',
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:50',
        ]);
        
        try {
            $staff = $this->hrService->updateStaff($staff, $validated);
            
            return redirect()->route('hr.staff.show', $staff)
                ->with('success', 'Staff updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating staff: ' . $e->getMessage());
        }
    }
    
    /**
     * Terminate Staff
     */
    public function terminateStaff(Request $request, User $staff)
    {
        $this->authorize('terminate', $staff);
        
        $request->validate([
            'termination_date' => 'required|date',
            'reason' => 'required|string|max:1000',
            'exit_interview_notes' => 'nullable|string|max:2000',
        ]);
        
        try {
            $this->hrService->terminateStaff(
                $staff,
                $request->input('reason'),
                $request->input('termination_date')
            );
            
            return redirect()->route('hr.staff.index')
                ->with('success', 'Staff terminated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error terminating staff: ' . $e->getMessage());
        }
    }
    
    /**
     * Shift Schedule
     */
    public function schedule(Request $request)
    {
        $this->authorize('viewSchedule', Shift::class);
        
        $startDate = $request->input('start_date', now()->startOfWeek());
        $endDate = $request->input('end_date', now()->endOfWeek());
        $departmentId = $request->input('department_id');
        
        $schedule = $this->hrService->getShiftSchedule($startDate, $endDate, $departmentId);
        $departments = Department::all();
        $shifts = Shift::where('is_active', true)->get();
        
        return view('hr.schedule.index', compact('schedule', 'departments', 'shifts'));
    }
    
    /**
     * Payroll Management
     */
    public function payroll(Request $request)
    {
        $this->authorize('viewPayroll', Payroll::class);
        
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        
        $payroll = $this->hrService->processPayroll($month, $year);
        
        return view('hr.payroll.index', compact('payroll'));
    }
    
    /**
     * Approve Payroll
     */
    public function approvePayroll(Payroll $payroll)
    {
        $this->authorize('approve', $payroll);
        
        try {
            $payroll = $this->hrService->approvePayroll($payroll, auth()->user());
            
            return redirect()->route('hr.payroll.show', $payroll)
                ->with('success', 'Payroll approved and paid successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error approving payroll: ' . $e->getMessage());
        }
    }
    
    /**
     * Leave Management
     */
    public function leaveRequests(Request $request)
    {
        $this->authorize('viewAny', LeaveRequest::class);
        
        $query = LeaveRequest::with(['user', 'approver']);
        
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }
        
        $leaveRequests = $query->latest()->paginate(20);
        
        return view('hr.leave.index', compact('leaveRequests'));
    }
    
    /**
     * Approve Leave
     */
    public function approveLeave(LeaveRequest $leaveRequest, Request $request)
    {
        $this->authorize('approve', $leaveRequest);
        
        $request->validate([
            'notes' => 'nullable|string',
        ]);
        
        try {
            $leaveRequest = $this->hrService->approveLeave($leaveRequest, auth()->user(), $request->input('notes'));
            
            return redirect()->route('hr.leave.index')
                ->with('success', 'Leave request approved successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error approving leave request: ' . $e->getMessage());
        }
    }
    
    /**
     * Reject Leave
     */
    public function rejectLeave(LeaveRequest $leaveRequest, Request $request)
    {
        $this->authorize('approve', $leaveRequest);
        
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);
        
        try {
            $leaveRequest = $this->hrService->rejectLeave($leaveRequest, auth()->user(), $request->input('reason'));
            
            return redirect()->route('hr.leave.index')
                ->with('success', 'Leave request rejected.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error rejecting leave request: ' . $e->getMessage());
        }
    }
}