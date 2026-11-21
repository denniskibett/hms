<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\StaffProfile;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * The weekly rota: who works which shift each day, attendance and overtime.
 * Also the shift patterns themselves.
 */
class RotaController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'week' => ['nullable', 'date'],
            'department_id' => ['nullable', 'integer'],
        ]);

        $start = Carbon::parse($filters['week'] ?? today())->startOfWeek();
        $days = collect(range(0, 6))->map(fn ($i) => $start->copy()->addDays($i));
        $end = $days->last();

        $staff = StaffProfile::with('user:id,name')
            ->active()
            ->whereDate('hire_date', '<=', $end)
            ->when($filters['department_id'] ?? null, fn ($q, $id) => $q->where('department_id', $id))
            ->get()
            ->sortBy(fn ($s) => $s->user->name)
            ->values();
        $userIds = $staff->pluck('user_id');

        // [user_id][Y-m-d] => assignment
        $assignments = ShiftAssignment::with('shift')
            ->whereIn('user_id', $userIds)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->groupBy('user_id')
            ->map(fn ($rows) => $rows->keyBy(fn ($a) => $a->date->toDateString()));

        $leave = LeaveRequest::whereIn('user_id', $userIds)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $end)
            ->whereDate('end_date', '>=', $start)
            ->get()
            ->groupBy('user_id');

        $shifts = Shift::orderBy('start_time')->get();
        $departments = Department::orderBy('name')->get(['id', 'name']);

        return view('hr.rota', compact('staff', 'days', 'assignments', 'leave', 'shifts', 'departments', 'filters', 'start'));
    }

    /**
     * Set (or clear) one person's shift on one day, with attendance and overtime.
     */
    public function assign(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:staff_profiles,user_id'],
            'date' => ['required', 'date'],
            'shift_id' => ['nullable', 'exists:shifts,id'],
            'status' => ['nullable', Rule::in(array_keys(ShiftAssignment::STATUSES))],
            'overtime_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $staff = StaffProfile::where('user_id', $data['user_id'])->first();
        if (! $staff->is_active) {
            return response()->json(['success' => false, 'message' => "{$staff->user->name} has left."], 422);
        }

        $assignment = DB::transaction(function () use ($data) {
            // One shift per person per day
            ShiftAssignment::where('user_id', $data['user_id'])
                ->whereDate('date', $data['date'])
                ->when($data['shift_id'] ?? null, fn ($q, $id) => $q->where('shift_id', '!=', $id))
                ->delete();

            if (empty($data['shift_id'])) {
                return null;
            }

            return ShiftAssignment::updateOrCreate(
                ['user_id' => $data['user_id'], 'shift_id' => $data['shift_id'], 'date' => $data['date']],
                [
                    'status' => $data['status'] ?? 'scheduled',
                    'overtime_hours' => $data['overtime_hours'] ?? 0,
                    'notes' => $data['notes'] ?? null,
                ],
            );
        });

        return response()->json([
            'success' => true,
            'assignment' => $assignment?->load('shift')->only('id', 'shift_id', 'status', 'overtime_hours', 'notes'),
        ]);
    }

    /**
     * Copy last week's rota into this week (only empty cells are filled).
     */
    public function copyWeek(Request $request): JsonResponse
    {
        $data = $request->validate(['week' => ['required', 'date']]);
        $start = Carbon::parse($data['week'])->startOfWeek();

        $previous = ShiftAssignment::whereBetween('date', [$start->copy()->subWeek()->toDateString(), $start->copy()->subDay()->toDateString()])
            ->whereIn('user_id', StaffProfile::active()->pluck('user_id'))
            ->get();

        $copied = 0;
        DB::transaction(function () use ($previous, &$copied) {
            foreach ($previous as $old) {
                $date = $old->date->copy()->addWeek()->toDateString();
                if (ShiftAssignment::where('user_id', $old->user_id)->whereDate('date', $date)->exists()) {
                    continue;
                }
                ShiftAssignment::create(['user_id' => $old->user_id, 'shift_id' => $old->shift_id, 'date' => $date, 'status' => 'scheduled']);
                $copied++;
            }
        });

        return response()->json(['success' => true, 'message' => "{$copied} shifts copied from last week."]);
    }

    // ---- Shift patterns ----------------------------------------------------

    public function storeShift(Request $request): JsonResponse
    {
        $shift = Shift::create($this->validatedShift($request));

        return response()->json(['success' => true, 'message' => "{$shift->name} added."], 201);
    }

    public function updateShift(Request $request, Shift $shift): JsonResponse
    {
        $shift->update($this->validatedShift($request, $shift));

        return response()->json(['success' => true, 'message' => "{$shift->name} updated."]);
    }

    public function destroyShift(Shift $shift): JsonResponse
    {
        if ($shift->assignments()->exists()) {
            return response()->json(['success' => false, 'message' => "{$shift->name} is on the rota. Turn it off instead."], 422);
        }

        $shift->delete();

        return response()->json(['success' => true, 'message' => "{$shift->name} deleted."]);
    }

    private function validatedShift(Request $request, ?Shift $shift = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('shifts', 'name')->ignore($shift?->id)],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'different:start_time'],
            'type' => ['required', Rule::in(array_keys(Shift::TYPES))],
            'is_active' => ['boolean'],
        ]) + ['is_active' => true];
    }
}
