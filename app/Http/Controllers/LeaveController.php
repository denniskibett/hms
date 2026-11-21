<?php

namespace App\Http\Controllers;

use App\Helpers\SystemHelper;
use App\Models\LeaveRequest;
use App\Models\ShiftAssignment;
use App\Models\StaffProfile;
use App\Services\CoreService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Leave: HR records requests, a manager or HR approves or rejects them.
 * Approved leave marks the person's rota shifts as on leave.
 */
class LeaveController extends Controller
{
    public function __construct(private CoreService $core)
    {
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(array_keys(LeaveRequest::STATUSES))],
            'leave_type' => ['nullable', Rule::in(array_keys(LeaveRequest::TYPES))],
            'user_id' => ['nullable', 'integer'],
        ]);

        $requests = LeaveRequest::with(['user:id,name', 'staffProfile.department', 'approver:id,name'])
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->when($filters['leave_type'] ?? null, fn ($q, $t) => $q->where('leave_type', $t))
            ->when($filters['user_id'] ?? null, fn ($q, $id) => $q->where('user_id', $id))
            ->orderByRaw("status = 'pending' desc")
            ->latest('start_date')
            ->paginate(SystemHelper::paginationLimit())
            ->withQueryString();

        $staff = StaffProfile::with('user:id,name')->active()->get()->sortBy(fn ($s) => $s->user->name)->values();

        $stats = [
            'pending' => LeaveRequest::where('status', 'pending')->count(),
            'on_leave_today' => LeaveRequest::where('status', 'approved')->whereDate('start_date', '<=', today())->whereDate('end_date', '>=', today())->count(),
            'upcoming' => LeaveRequest::where('status', 'approved')->whereDate('start_date', '>', today())->count(),
            'entitlement' => SystemHelper::annualLeaveDays(),
        ];

        return view('hr.leave', compact('requests', 'staff', 'stats', 'filters'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:staff_profiles,user_id'],
            'leave_type' => ['required', Rule::in(array_keys(LeaveRequest::TYPES))],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $staff = StaffProfile::with('user')->where('user_id', $data['user_id'])->first();
        if (! $staff->is_active) {
            throw ValidationException::withMessages(['user_id' => "{$staff->user->name} has left."]);
        }
        if (LeaveRequest::where('user_id', $data['user_id'])->overlapping($data['start_date'], $data['end_date'])->exists()) {
            throw ValidationException::withMessages(['start_date' => "{$staff->user->name} already has leave on some of these days."]);
        }
        if ($data['leave_type'] === 'annual') {
            $days = Carbon::parse($data['start_date'])->diffInDays(Carbon::parse($data['end_date'])) + 1;
            $balance = $staff->annualLeaveBalance(Carbon::parse($data['start_date'])->year);
            if ($days > $balance) {
                throw ValidationException::withMessages(['end_date' => "{$staff->user->name} has {$balance} days of annual leave left; this is {$days}."]);
            }
        }

        $leave = LeaveRequest::create($data + ['status' => 'pending']);
        $this->core->log($request->user()->id, 'leave_requested', "{$leave->type_label} leave for {$staff->user->name}, {$data['start_date']} → {$data['end_date']}", ['leave_request_id' => $leave->id]);

        return back()->with('success', "Leave recorded for {$staff->user->name}; waiting for approval.");
    }

    /**
     * Approve, reject or cancel.
     */
    public function decide(Request $request, LeaveRequest $leave): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected', 'cancelled'])],
            'approval_notes' => ['nullable', 'required_if:status,rejected', 'string', 'max:500'],
        ], [
            'approval_notes.required_if' => 'Say why the leave is rejected.',
        ]);

        $allowed = match ($leave->status) {
            'pending' => ['approved', 'rejected', 'cancelled'],
            'approved' => ['cancelled'],
            default => [],
        };
        if (! in_array($data['status'], $allowed, true)) {
            return back()->with('error', 'This leave is already ' . strtolower(LeaveRequest::STATUSES[$leave->status]) . '.');
        }
        if ($data['status'] === 'approved' && $leave->user_id === $request->user()->id) {
            return back()->with('error', "You can't approve your own leave.");
        }

        DB::transaction(function () use ($leave, $data, $request) {
            $leave->update([
                'status' => $data['status'],
                'approval_notes' => $data['approval_notes'] ?? $leave->approval_notes,
                'approved_by' => in_array($data['status'], ['approved', 'rejected'], true) ? $request->user()->id : $leave->approved_by,
                'approved_at' => in_array($data['status'], ['approved', 'rejected'], true) ? now() : $leave->approved_at,
            ]);

            // Keep the rota in step with approved leave
            $shifts = ShiftAssignment::where('user_id', $leave->user_id)
                ->whereBetween('date', [$leave->start_date->toDateString(), $leave->end_date->toDateString()]);
            if ($data['status'] === 'approved') {
                $shifts->where('status', 'scheduled')->update(['status' => 'on_leave']);
            } elseif ($data['status'] === 'cancelled') {
                $shifts->where('status', 'on_leave')->update(['status' => 'scheduled']);
            }
        });

        $name = $leave->user->name ?? 'staff';
        $this->core->log($request->user()->id, 'leave_' . $data['status'], "Leave #{$leave->id} for {$name} {$data['status']}", ['leave_request_id' => $leave->id]);

        return back()->with('success', "Leave for {$name} " . $data['status'] . '.');
    }
}
