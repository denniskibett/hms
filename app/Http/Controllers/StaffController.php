<?php

namespace App\Http\Controllers;

use App\Helpers\SystemHelper;
use App\Models\Department;
use App\Models\Payroll;
use App\Models\Role;
use App\Models\StaffProfile;
use App\Models\User;
use App\Services\CoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * The staff register. Each employee is a user (name, contacts, login, role)
 * with a staff profile (job, pay, statutory numbers).
 */
class StaffController extends Controller
{
    /**
     * Roles HR can give. Admin is given by an admin in Users.
     */
    public const ROLES = ['manager', 'receptionist', 'housekeeping', 'kitchen', 'procurement', 'hr', 'finance'];

    public function __construct(private CoreService $core)
    {
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'department_id' => ['nullable', 'integer'],
            'status' => ['nullable', Rule::in([...array_keys(StaffProfile::EMPLOYMENT_STATUSES), 'current'])],
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $filters['status'] ??= 'current';

        $staff = StaffProfile::with(['user.roles', 'department'])
            ->when($filters['status'] === 'current', fn ($q) => $q->active(), fn ($q) => $q->where('employment_status', $filters['status']))
            ->when($filters['department_id'] ?? null, fn ($q, $id) => $q->where('department_id', $id))
            ->when($filters['search'] ?? null, fn ($q, $term) => $q->search($term))
            ->join('users', 'users.id', '=', 'staff_profiles.user_id')
            ->orderBy('users.name')
            ->select('staff_profiles.*')
            ->paginate(SystemHelper::paginationLimit())
            ->withQueryString();

        $active = StaffProfile::active();
        $stats = [
            'active' => (clone $active)->count(),
            'on_leave_today' => \App\Models\LeaveRequest::where('status', 'approved')->whereDate('start_date', '<=', today())->whereDate('end_date', '>=', today())->count(),
            'pending_leave' => \App\Models\LeaveRequest::where('status', 'pending')->count(),
            'monthly_salaries' => (float) (clone $active)->sum('salary'),
        ];

        return view('hr.index', array_merge(compact('staff', 'stats', 'filters'), $this->formOptions()));
    }

    public function show(StaffProfile $staff): View
    {
        $staff->load(['user.roles', 'department']);

        $leave = $staff->leaveRequests()->with('approver')->latest('start_date')->take(10)->get();
        $payslips = Payroll::where('user_id', $staff->user_id)->where('status', '!=', 'cancelled')->latest('period_month')->take(12)->get();
        $shifts = $staff->shiftAssignments()->with('shift')
            ->whereBetween('date', [today()->subDays(7), today()->addDays(14)])
            ->orderBy('date')
            ->get();

        return view('hr.show', array_merge(compact('staff', 'leave', 'payslips', 'shifts'), $this->formOptions()));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $staff = DB::transaction(function () use ($data) {
            $user = User::create($data['user'] + [
                // Staff without a login get an unusable password
                'password' => Hash::make($data['password'] ?? Str::random(40)),
            ]);
            if ($data['role']) {
                $user->roles()->attach(Role::firstOrCreate(['name' => $data['role']])->id);
            }

            return $user->staffProfile()->create($data['profile']);
        });

        $this->core->log($request->user()->id, 'staff_hired', "{$staff->user->name} added to staff", ['staff_profile_id' => $staff->id]);

        return response()->json([
            'success' => true,
            'message' => "{$staff->user->name} added.",
            'url' => route('hr.staff.show', $staff),
        ], 201);
    }

    public function update(Request $request, StaffProfile $staff): JsonResponse
    {
        if (! $staff->is_active) {
            return response()->json(['success' => false, 'message' => "{$staff->user->name} has left; their record is kept as it was."], 422);
        }

        $data = $this->validated($request, $staff);

        DB::transaction(function () use ($staff, $data) {
            $user = $staff->user;
            $user->update($data['user'] + (isset($data['password']) ? ['password' => Hash::make($data['password'])] : []));

            // Swap the staff role; leave admin / guest roles alone
            $staffRoleIds = Role::whereIn('name', self::ROLES)->pluck('id');
            $user->roles()->detach($staffRoleIds);
            if ($data['role']) {
                $user->roles()->attach(Role::firstOrCreate(['name' => $data['role']])->id);
            }

            $staff->update($data['profile']);
        });

        $this->core->log($request->user()->id, 'staff_updated', "{$staff->user->name}'s record updated", ['staff_profile_id' => $staff->id]);

        return response()->json(['success' => true, 'message' => "{$staff->user->name} updated."]);
    }

    /**
     * Record that someone has left. Their login stops working; history is kept.
     */
    public function terminate(Request $request, StaffProfile $staff): RedirectResponse
    {
        $data = $request->validate([
            'end_date' => ['required', 'date', 'after_or_equal:' . $staff->hire_date->toDateString()],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        if ($staff->user_id === $request->user()->id) {
            return back()->with('error', "You can't end your own employment.");
        }

        DB::transaction(function () use ($staff, $data) {
            $staff->update([
                'employment_status' => 'terminated',
                'end_date' => $data['end_date'],
                'notes' => trim(($staff->notes ? $staff->notes . "\n" : '') . 'Left ' . $data['end_date'] . ': ' . $data['reason']),
            ]);
            // status isn't mass assignable on users
            $staff->user->forceFill(['status' => 'inactive'])->save();
            $staff->shiftAssignments()->where('date', '>', $data['end_date'])->where('status', 'scheduled')->delete();
        });

        $this->core->log($request->user()->id, 'staff_terminated', "{$staff->user->name} left on {$data['end_date']}: {$data['reason']}", ['staff_profile_id' => $staff->id]);

        return back()->with('success', "{$staff->user->name} marked as left. Their login is disabled.");
    }

    // ---- Departments -------------------------------------------------------

    public function storeDepartment(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:departments,name'],
        ]);
        $code = Str::upper(Str::substr(Str::slug($data['name'], ''), 0, 6));
        $department = Department::create(['name' => $data['name'], 'code' => Department::where('code', $code)->exists() ? $code . Str::upper(Str::random(2)) : $code]);

        return response()->json(['success' => true, 'message' => "{$department->name} added.", 'department' => $department->only('id', 'name')], 201);
    }

    public function destroyDepartment(Department $department): JsonResponse
    {
        if ($department->staff()->exists()) {
            return response()->json(['success' => false, 'message' => "{$department->name} still has staff."], 422);
        }

        $department->delete();

        return response()->json(['success' => true, 'message' => "{$department->name} deleted."]);
    }

    private function formOptions(): array
    {
        return [
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'roles' => self::ROLES,
        ];
    }

    /**
     * @return array{user: array, profile: array, role: ?string, password: ?string}
     */
    private function validated(Request $request, ?StaffProfile $staff = null): array
    {
        $userId = $staff?->user_id;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId), 'required_with:password'],
            'role' => ['nullable', Rule::in(self::ROLES)],
            'password' => ['nullable', 'string', Password::min(8)],
            'department_id' => ['nullable', 'exists:departments,id'],
            'job_title' => ['nullable', 'string', 'max:100'],
            'id_number' => ['nullable', 'string', 'max:30'],
            'kra_pin' => ['nullable', 'string', 'regex:/^[A-Z]\d{9}[A-Z]$/i'],
            'nssf_number' => ['nullable', 'string', 'max:30'],
            'shif_number' => ['nullable', 'string', 'max:30'],
            'salary' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'hire_date' => ['required', 'date'],
            'employment_status' => ['required', Rule::in(array_keys(array_diff_key(StaffProfile::EMPLOYMENT_STATUSES, ['terminated' => 1])))],
            'contract_period' => ['nullable', 'integer', 'min:1', 'max:120'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_branch' => ['nullable', 'string', 'max:100'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'emergency_contact' => ['nullable', 'array'],
            'emergency_contact.name' => ['nullable', 'string', 'max:255'],
            'emergency_contact.phone' => ['nullable', 'string', 'max:30'],
            'emergency_contact.relationship' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'kra_pin.regex' => 'A KRA PIN looks like A123456789B.',
            'email.required_with' => 'Staff who log in need an email address.',
        ]);

        // A role without a login is fine (e.g. records only); a login needs an email
        $emergency = array_filter($data['emergency_contact'] ?? []);

        return [
            'user' => ['name' => $data['name'], 'phone' => $data['phone'], 'email' => $data['email'] ?? null],
            'role' => $data['role'] ?? null,
            'password' => $data['password'] ?? null,
            'profile' => [
                'department_id' => $data['department_id'] ?? null,
                'job_title' => $data['job_title'] ?? null,
                'id_number' => $data['id_number'] ?? null,
                'kra_pin' => isset($data['kra_pin']) ? Str::upper($data['kra_pin']) : null,
                'nssf_number' => $data['nssf_number'] ?? null,
                'shif_number' => $data['shif_number'] ?? null,
                'salary' => $data['salary'],
                'hire_date' => $data['hire_date'],
                'employment_status' => $data['employment_status'],
                'contract_period' => $data['contract_period'] ?? 3,
                'bank_name' => $data['bank_name'] ?? null,
                'bank_branch' => $data['bank_branch'] ?? null,
                'account_number' => $data['account_number'] ?? null,
                'emergency_contact' => $emergency ?: null,
                'notes' => $data['notes'] ?? null,
            ],
        ];
    }
}
