<?php

namespace App\Http\Controllers;

use App\Enums\FacilityBookingStatus;
use App\Enums\KitchenOrderStatus;
use App\Enums\PurchaseOrderStatus;
use App\Enums\RoomStatus;
use App\Enums\TaskStatus;
use App\Models\FacilityBooking;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\KitchenOrder;
use App\Models\LeaveRequest;
use App\Models\Payment;
use App\Models\Payroll;
use App\Models\PurchaseOrder;
use App\Models\Room;
use App\Models\StaffProfile;
use App\Models\Stay;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * The home page after sign-in. It is made of sections; which ones a person
 * sees depends on their roles (someone with two roles sees both sets).
 * Each section's data is only loaded when it is shown.
 */
class DashboardController extends Controller
{
    /**
     * Sections in page order, with the roles that see them. Admins see the
     * manager's sections.
     */
    private const SECTIONS = [
        'overview' => ['manager'],
        'approvals' => ['manager'],
        'front_desk' => ['manager', 'receptionist'],
        'housekeeping' => ['housekeeping'],
        'kitchen' => ['kitchen'],
        'finance' => ['finance'],
        'hr' => ['hr'],
        'procurement' => ['procurement'],
        'my_tasks' => ['manager', 'receptionist', 'housekeeping', 'kitchen', 'procurement', 'hr', 'finance'],
        'guest' => ['guest'],
    ];

    public function index(Request $request): View
    {
        $user = $request->user();
        $roles = $user->roles->pluck('name')->all();
        if (in_array('admin', $roles, true)) {
            $roles[] = 'manager';
        }

        $sections = [];
        foreach (self::SECTIONS as $name => $allowed) {
            if (array_intersect($allowed, $roles)) {
                $sections[$name] = $this->{lcfirst(str_replace('_', '', ucwords($name, '_')))}($user);
            }
        }

        // A guest who is also staff only needs the staff view
        if (count($sections) > 1) {
            unset($sections['guest']);
        }

        return view('dashboard', compact('sections', 'user'));
    }

    // ---- Sections ----------------------------------------------------------

    private function overview(User $user): array
    {
        $rooms = Room::count();
        $inHouse = Stay::inHouse()->count();
        $occupiedTonight = DB::table('room_allocations as a')
            ->join('stays as s', 's.id', '=', 'a.stay_id')
            ->whereNull('s.deleted_at')
            ->whereIn('s.status', ['reserved', 'booked', 'checked_in'])
            ->whereDate('a.from_date', '<=', today())
            ->whereDate('a.to_date', '>', today())
            ->distinct()
            ->count('a.room_id');
        $collected = Payment::where('status', 'completed');

        return [
            'occupancy' => $rooms ? round($occupiedTonight / $rooms * 100) : 0,
            'occupied' => $occupiedTonight,
            'rooms' => $rooms,
            'in_house' => $inHouse,
            'arrivals' => Stay::arrivingToday()->count(),
            'departures' => Stay::departingToday()->count(),
            'collected_today' => (float) (clone $collected)->whereDate('received_at', today())->sum('amount'),
            'collected_month' => (float) (clone $collected)->where('received_at', '>=', today()->startOfMonth())->sum('amount'),
            'outstanding' => (float) Invoice::unpaid()->sum('due_amount'),
            'overdue' => (float) Invoice::overdue()->sum('due_amount'),
            'events_today' => FacilityBooking::where('status', FacilityBookingStatus::Confirmed->value)
                ->whereDate('start_date', '<=', today())->whereDate('end_date', '>=', today())->with('facility:id,name')->get(),
            'kitchen_active' => KitchenOrder::active()->count(),
            'tasks_overdue' => Task::overdue()->count(),
            'low_stock' => InventoryItem::active()->lowStock()->count(),
        ];
    }

    private function approvals(User $user): array
    {
        return [
            'purchase_orders' => PurchaseOrder::withStatus(PurchaseOrderStatus::Submitted)->with('supplier:id,name')->latest('id')->get(),
            'leave' => LeaveRequest::where('status', 'pending')->where('user_id', '!=', $user->id)->with('user:id,name')->orderBy('start_date')->get(),
            'payroll_drafts' => Payroll::where('status', 'draft')->selectRaw('period_month, COUNT(*) as staff, SUM(net) as net')->groupBy('period_month')->get(),
            'tentative_events' => FacilityBooking::withStatus(FacilityBookingStatus::Tentative)->whereDate('start_date', '<=', today()->addDays(14))
                ->whereDate('end_date', '>=', today())->with('facility:id,name')->orderBy('start_date')->get(),
        ];
    }

    private function frontDesk(User $user): array
    {
        $with = ['guest', 'roomAllocations.room', 'invoices'];
        $statusCounts = Room::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return [
            'arrivals' => Stay::arrivingToday()->with($with)->orderBy('arrival_date')->get(),
            'departures' => Stay::departingToday()->with($with)->orderBy('departure_date')->get(),
            'rooms' => collect(RoomStatus::cases())->mapWithKeys(fn (RoomStatus $s) => [$s->value => ['status' => $s, 'count' => (int) ($statusCounts[$s->value] ?? 0)]]),
        ];
    }

    private function housekeeping(User $user): array
    {
        return [
            'to_clean' => Room::where('status', RoomStatus::Cleaning->value)->with('roomType:id,name')->orderBy('room_number')->get(),
            'cleaning_tasks' => Task::open()->category('cleaning')->with(['room:id,room_number', 'assignee:id,name'])
                ->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")->orderBy('due_date')->take(10)->get(),
            'occupied' => Room::where('status', RoomStatus::Occupied->value)->count(),
        ];
    }

    private function kitchen(User $user): array
    {
        $today = KitchenOrder::with('items')->whereDate('created_at', today())->where('status', '!=', KitchenOrderStatus::Cancelled->value)->get();
        $byStatus = KitchenOrder::active()->selectRaw('status, COUNT(*) as n')->groupBy('status')->pluck('n', 'status');

        return [
            'new' => (int) ($byStatus['pending'] ?? 0),
            'preparing' => (int) ($byStatus['preparing'] ?? 0),
            'ready' => (int) ($byStatus['ready'] ?? 0),
            'orders_today' => $today->count(),
            'sales_today' => round($today->sum(fn ($o) => $o->total), 2),
            'low_food' => InventoryItem::active()->lowStock()->whereIn('item_type', ['food', 'beverage', 'kitchen'])->orderBy('name')->take(8)->get(),
        ];
    }

    private function finance(User $user): array
    {
        $collected = Payment::where('status', 'completed');

        return [
            'collected_today' => (float) (clone $collected)->whereDate('received_at', today())->sum('amount'),
            'collected_month' => (float) (clone $collected)->where('received_at', '>=', today()->startOfMonth())->sum('amount'),
            'outstanding' => (float) Invoice::unpaid()->sum('due_amount'),
            'overdue' => (float) Invoice::overdue()->sum('due_amount'),
            'overdue_invoices' => Invoice::overdue()->with(['stay.guest', 'facilityBooking', 'kitchenOrder'])->orderBy('due_date')->take(6)->get(),
            'payroll_to_pay' => Payroll::where('status', 'approved')->selectRaw('period_month, COUNT(*) as staff, SUM(net) as net')->groupBy('period_month')->get(),
        ];
    }

    private function hr(User $user): array
    {
        return [
            'staff' => StaffProfile::active()->count(),
            'pending_leave' => LeaveRequest::where('status', 'pending')->with('user:id,name')->orderBy('start_date')->take(6)->get(),
            'away_today' => LeaveRequest::where('status', 'approved')->whereDate('start_date', '<=', today())->whereDate('end_date', '>=', today())->with('user:id,name')->get(),
            'contracts_ending' => StaffProfile::active()->with('user:id,name')->whereIn('employment_status', ['contract', 'internship'])->get()
                ->filter(fn ($s) => $s->contract_end_date && $s->contract_end_date->between(today(), today()->addDays(30)))
                ->sortBy('contract_end_date')->values(),
            'payroll_month' => Payroll::forMonth(today()->format('Y-m'))->where('status', '!=', 'cancelled')->selectRaw('status, COUNT(*) as n')->groupBy('status')->pluck('n', 'status'),
        ];
    }

    private function procurement(User $user): array
    {
        return [
            'low_stock' => InventoryItem::active()->lowStock()->with('primarySupplier:id,name')->orderBy('name')->take(10)->get(),
            'awaiting_approval' => PurchaseOrder::withStatus(PurchaseOrderStatus::Submitted)->count(),
            'drafts' => PurchaseOrder::withStatus(PurchaseOrderStatus::Draft)->count(),
            'awaiting_delivery' => PurchaseOrder::outstanding()->with('supplier:id,name')->orderBy('delivery_date')->take(8)->get(),
        ];
    }

    private function myTasks(User $user): array
    {
        return [
            'tasks' => Task::open()->where('assigned_to', $user->id)->with('room:id,room_number')
                ->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")->orderBy('due_date')->take(8)->get(),
            'unassigned' => Task::visibleTo($user)->where('status', TaskStatus::Pending->value)->count(),
        ];
    }

    private function guest(User $user): array
    {
        return [
            'stays' => Stay::where('guest_id', $user->id)->with(['roomAllocations.room.roomType', 'invoices'])->latest('arrival_date')->take(10)->get(),
        ];
    }
}
