<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\ShiftAssignment;
use App\Models\StaffProfile;
use App\Services\CoreService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Monthly payroll: HR generates and adjusts drafts, a manager approves,
 * finance (or a manager) marks them paid. Payslips print from here.
 */
class PayrollController extends Controller
{
    public function __construct(private CoreService $core)
    {
    }

    public function index(Request $request): View
    {
        $month = $request->validate(['month' => ['nullable', 'date_format:Y-m']])['month'] ?? now()->format('Y-m');

        $payrolls = Payroll::with(['user:id,name', 'staffProfile.department'])
            ->forMonth($month)
            ->where('status', '!=', 'cancelled')
            ->get()
            ->sortBy(fn ($p) => $p->user->name)
            ->values();

        $totals = [
            'gross' => round($payrolls->sum(fn ($p) => (float) $p->gross), 2),
            'paye' => round($payrolls->sum(fn ($p) => (float) $p->paye), 2),
            'nssf' => round($payrolls->sum(fn ($p) => (float) $p->nssf), 2),
            'shif' => round($payrolls->sum(fn ($p) => (float) $p->shif), 2),
            'housing_levy' => round($payrolls->sum(fn ($p) => (float) $p->housing_levy), 2),
            'other' => round($payrolls->sum(fn ($p) => (float) $p->other_deductions), 2),
            'net' => round($payrolls->sum(fn ($p) => (float) $p->net), 2),
        ];

        $missing = StaffProfile::active()
            ->whereDate('hire_date', '<=', Carbon::parse($month . '-01')->endOfMonth())
            ->whereNotIn('user_id', $payrolls->pluck('user_id'))
            ->count();

        return view('hr.payroll', compact('payrolls', 'totals', 'month', 'missing'));
    }

    /**
     * Create draft payroll for everyone employed in the month who doesn't have one yet.
     * Overtime comes from the rota.
     */
    public function generate(Request $request): RedirectResponse
    {
        $month = $request->validate(['month' => ['required', 'date_format:Y-m']])['month'];
        $start = Carbon::parse($month . '-01');
        $end = $start->copy()->endOfMonth();

        $staff = StaffProfile::active()
            ->whereDate('hire_date', '<=', $end)
            ->whereNotIn('user_id', Payroll::forMonth($month)->where('status', '!=', 'cancelled')->pluck('user_id'))
            ->get();

        $overtime = ShiftAssignment::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->where('status', 'completed')
            ->groupBy('user_id')
            ->selectRaw('user_id, SUM(overtime_hours) as hours')
            ->pluck('hours', 'user_id');

        DB::transaction(function () use ($staff, $start, $overtime) {
            foreach ($staff as $member) {
                $hours = (float) ($overtime[$member->user_id] ?? 0);

                // A cancelled payroll for the month is reused (user + month is unique)
                $payroll = Payroll::firstOrNew(['user_id' => $member->user_id, 'period_month' => $start->toDateString()]);
                $payroll->fill([
                    'basic_salary' => $member->salary,
                    'overtime_hours' => $hours,
                    'overtime_amount' => Payroll::overtimePay((float) $member->salary, $hours),
                    'allowances' => 0,
                    'bonuses' => 0,
                    'other_deductions' => 0,
                    'status' => 'draft',
                    'approved_by' => null,
                    'approved_at' => null,
                ])->calculate()->save();
            }
        });

        $this->core->log($request->user()->id, 'payroll_generated', "Payroll for {$month}: {$staff->count()} drafts");

        return back()->with('success', $staff->count() . ' payroll ' . str('draft')->plural($staff->count()) . " created for {$start->format('F Y')}.");
    }

    /**
     * Adjust a draft: overtime hours, allowances, bonuses, other deductions.
     */
    public function update(Request $request, Payroll $payroll): JsonResponse
    {
        if ($payroll->status !== 'draft') {
            return response()->json(['success' => false, 'message' => 'Only draft payroll can be changed.'], 422);
        }

        $data = $request->validate([
            'basic_salary' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'overtime_hours' => ['required', 'numeric', 'min:0', 'max:500'],
            'allowances' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'bonuses' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'other_deductions' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $payroll->fill($data + ['overtime_amount' => Payroll::overtimePay((float) $data['basic_salary'], (float) $data['overtime_hours'])])->calculate();

        $gross = (float) $payroll->basic_salary + (float) $payroll->overtime_amount + (float) $payroll->allowances + (float) $payroll->bonuses;
        if ($gross - (float) $payroll->tax_deductions - (float) $payroll->other_deductions < 0) {
            return response()->json(['success' => false, 'errors' => ['other_deductions' => ['Deductions are more than the pay.']]], 422);
        }

        $payroll->save();

        return response()->json(['success' => true, 'message' => "Payroll for {$payroll->user->name} updated."]);
    }

    /**
     * Approve, mark paid or cancel one payroll, or every payroll of the month at once.
     */
    public function updateStatus(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['approved', 'paid', 'cancelled'])],
            'month' => ['required_without:payroll_id', 'nullable', 'date_format:Y-m'],
            'payroll_id' => ['nullable', 'exists:payrolls,id'],
        ]);

        $user = $request->user();
        $from = ['approved' => 'draft', 'paid' => 'approved', 'cancelled' => 'draft'][$data['status']];

        // Approving is a manager's call; paying is a manager's or finance's
        $permitted = match ($data['status']) {
            'approved' => $user->hasAnyRole(['admin', 'manager']),
            'paid' => $user->hasAnyRole(['admin', 'manager', 'finance']),
            'cancelled' => $user->hasAnyRole(['admin', 'manager', 'hr']),
        };
        abort_unless($permitted, 403);

        $query = Payroll::where('status', $from)
            ->when($data['payroll_id'] ?? null, fn ($q, $id) => $q->whereKey($id), fn ($q) => $q->forMonth($data['month']));

        $count = $query->update(match ($data['status']) {
            'approved' => ['status' => 'approved', 'approved_by' => $user->id, 'approved_at' => now()],
            'paid' => ['status' => 'paid', 'paid_by' => $user->id, 'paid_at' => now()],
            'cancelled' => ['status' => 'cancelled'],
        });

        if (! $count) {
            return back()->with('error', "Nothing to mark {$data['status']}: only " . Payroll::STATUSES[$from] . ' payroll can be.');
        }

        $this->core->log($user->id, 'payroll_' . $data['status'], "{$count} payroll " . str('record')->plural($count) . " {$data['status']}" . (isset($data['month']) ? " for {$data['month']}" : ''));

        return back()->with('success', "{$count} payroll " . str('record')->plural($count) . " {$data['status']}.");
    }

    public function payslip(Payroll $payroll): View
    {
        abort_if($payroll->status === 'cancelled', 404);
        $payroll->load(['user', 'staffProfile.department']);

        return view('hr.print.payslip', compact('payroll'));
    }
}
