<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GuestService;
use App\Services\FinanceService;
use App\Services\OperationsService;
use App\Services\KitchenService;
use App\Services\CoreService;
use App\Models\Stay;
use App\Models\Room;
use App\Models\RoomAllocation;
use App\Models\Payment;
use App\Models\Task;
use App\Models\User; 
use App\Models\ShiftAssignment;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\LeaveRequest;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(
        private GuestService $guestService,
        private FinanceService $financeService,
        private OperationsService $operationsService,
        private KitchenService $kitchenService,
        private CoreService $coreService
    ) {}
    

    public function index(Request $request)
    {
        $user = $request->user();

        // Fetch all role names for this user from the database
        $userRoles = DB::table('role_user')
            ->join('roles', 'role_user.role_id', '=', 'roles.id')
            ->where('role_user.user_id', $user->id)
            ->pluck('roles.name') // get array of role names
            ->toArray();

        $validRoles = ['admin', 'manager', 'finance', 'receptionist', 'housekeeping', 
                    'kitchen', 'procurement', 'hr', 'guest'];

        // Check if the user has any valid role
        if (!array_intersect($userRoles, $validRoles)) {
            abort(403, 'You do not have permission to view the dashboard.');
        }

        // Convert array to comma-separated string for display
        $rolesDisplay = implode(', ', $userRoles);

        // Route based on specific roles with proper fallbacks
        if (in_array('admin', $userRoles)) {
            return $this->adminDashboard($user, $rolesDisplay);
        }
        
        if (in_array('finance', $userRoles)) {
            return $this->financeDashboard($user, $rolesDisplay);
        }
        
        if (in_array('receptionist', $userRoles)) {
            return $this->receptionistDashboard($user, $rolesDisplay);
        }
        
        if (in_array('guest', $userRoles)) {
            return $this->guestDashboard($user, $rolesDisplay);
        }
        
        if (in_array('housekeeping', $userRoles)) {
            return $this->housekeepingDashboard($user, $rolesDisplay);
        }
        
        if (in_array('kitchen', $userRoles)) {
            return $this->kitchenDashboard($user, $rolesDisplay);
        }
        
        if (in_array('procurement', $userRoles)) {
            return $this->procurementDashboard($user, $rolesDisplay);
        }
        
        if (in_array('hr', $userRoles)) {
            return $this->hrDashboard($user, $rolesDisplay);
        }
        
        if (in_array('manager', $userRoles)) {
            return $this->managerDashboard($user, $rolesDisplay);
        }

        // Fallback: return to main dashboard view with safe defaults
        return $this->getFallbackDashboard($user, $rolesDisplay);
    }


    private function adminDashboard(User $user, string $rolesDisplay)
    {
        $stats = $this->getAdminStats();
        
        $recentBookings = Stay::with(['guest', 'roomAllocations.room'])
            ->whereIn('status', ['checked_in', 'booked'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        $upcomingCheckins = Stay::with(['guest', 'roomAllocations.room'])
            ->where('status', 'booked')
            ->whereDate('arrival_date', today())
            ->orderBy('arrival_date')
            ->limit(5)
            ->get();
        
        $recentPayments = Payment::with(['invoice.stay.guest'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        $pendingOrders = $this->kitchenService->getPendingOrdersCount();
        
        return view('dashboard', [
            'user' => $user,
            'rolesDisplay' => $rolesDisplay,
            'stats' => $stats,
            'recentBookings' => $recentBookings,
            'upcomingCheckins' => $upcomingCheckins,
            'recentPayments' => $recentPayments,
            'pendingOrders' => $pendingOrders,
            
            // Safe defaults for other variables
            'tasks' => collect(),
            'todayShifts' => collect(),
            'notifications' => collect(),
            'pendingCheckins' => collect(),
            'pendingCheckouts' => collect(),
            'todayArrivals' => 0,
            'todayDepartures' => 0,
            'pendingPayments' => 0,
            'invoicesToApprove' => 0,
            'currentStays' => collect(),
            'pastStays' => collect(),
            'pendingTasksCount' => 0,
            'todayShiftsCount' => 0,
            'notificationsCount' => 0,
        ]);
    }

    private function financeDashboard(User $user, string $rolesDisplay)
    {
        $stats = $this->getFinanceStats();
        $pendingPayments = $this->financeService->getPendingPaymentsCount();
        $invoicesToApprove = $this->financeService->getInvoicesToApproveCount();
        
        $recentPayments = Payment::with(['invoice.stay.guest'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        $overdueInvoices = Invoice::where('due_date', '<', today())
            ->whereIn('status', ['sent', 'partial'])
            ->orderBy('due_date')
            ->limit(5)
            ->get();
        
        return view('dashboard', [
            'user' => $user,
            'rolesDisplay' => $rolesDisplay,
            'stats' => $stats,
            'pendingPayments' => $pendingPayments,
            'invoicesToApprove' => $invoicesToApprove,
            'recentPayments' => $recentPayments,
            'overdueInvoices' => $overdueInvoices,
            
            // Safe defaults
            'tasks' => collect(),
            'todayShifts' => collect(),
            'notifications' => collect(),
            'recentBookings' => collect(),
            'upcomingCheckins' => collect(),
            'pendingOrders' => [],
            'pendingCheckins' => collect(),
            'pendingCheckouts' => collect(),
            'todayArrivals' => 0,
            'todayDepartures' => 0,
            'currentStays' => collect(),
            'pastStays' => collect(),
            'pendingTasksCount' => 0,
            'todayShiftsCount' => 0,
            'notificationsCount' => 0,
        ]);
    }

    private function receptionistDashboard(User $user, string $rolesDisplay)
    {
        // Basic stats
        $stats = $this->getReceptionistStats();
        
        // For initial page load, we'll load minimal data
        // Alpine.js will handle dynamic loading
        
        return view('dashboard', [
            'user' => $user,
            'rolesDisplay' => $rolesDisplay,
            'stats' => $stats,
            'todayArrivals' => Stay::whereDate('arrival_date', today())
                ->where('status', 'booked')
                ->count(),
            'todayDepartures' => Stay::whereDate('departure_date', today())
                ->where('status', 'checked_in')
                ->count(),
            'availableRooms' => Room::where('status', 'available')->count(),
            
            // Safe defaults for Alpine.js to work with
            'pendingCheckins' => collect(),
            'pendingCheckouts' => collect(),
            'tasks' => collect(),
            'todayShifts' => collect(),
            'notifications' => collect(),
            'recentBookings' => collect(),
            'upcomingCheckins' => collect(),
            'recentPayments' => collect(),
            'pendingOrders' => [],
            'pendingPayments' => 0,
            'invoicesToApprove' => 0,
            'currentStays' => collect(),
            'pastStays' => collect(),
            'pendingTasksCount' => 0,
            'todayShiftsCount' => 0,
            'notificationsCount' => 0,
        ]);
    }


    private function guestDashboard(User $user, string $rolesDisplay)
    {
        $currentStays = collect($this->guestService->getGuestCurrentStays($user));
        $pastStays = collect($this->guestService->getGuestPastStays($user, 5));
        
        $pendingInvoices = Invoice::whereHas('stay', function ($query) use ($user) {
                $query->where('guest_id', $user->id);
            })
            ->whereIn('status', ['sent', 'partial'])
            ->count();
        
        $totalSpent = Payment::whereHas('invoice.stay', function ($query) use ($user) {
                $query->where('guest_id', $user->id);
            })
            ->sum('amount');
        
        return view('dashboard', [
            'user' => $user,
            'rolesDisplay' => $rolesDisplay,
            'currentStays' => $currentStays,
            'pastStays' => $pastStays,
            'pendingInvoices' => $pendingInvoices,
            'totalSpent' => $totalSpent,
            
            // Safe defaults
            'stats' => $this->getDefaultStats(),
            'tasks' => collect(),
            'todayShifts' => collect(),
            'notifications' => collect(),
            'recentBookings' => collect(),
            'upcomingCheckins' => collect(),
            'recentPayments' => collect(),
            'pendingOrders' => [],
            'pendingCheckins' => collect(),
            'pendingCheckouts' => collect(),
            'todayArrivals' => 0,
            'todayDepartures' => 0,
            'pendingPayments' => 0,
            'invoicesToApprove' => 0,
            'pendingTasksCount' => 0,
            'todayShiftsCount' => 0,
            'notificationsCount' => 0,
        ]);
    }

    private function housekeepingDashboard(User $user, string $rolesDisplay)
    {
        $stats = $this->getHousekeepingStats($user);
        
        $tasks = Task::where('assigned_to', $user->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('due_date')
            ->limit(5)
            ->get();
        
        $todayShifts = ShiftAssignment::with('shift')
            ->where('user_id', $user->id)
            ->whereDate('date', today())
            ->get();
        
        $dirtyRooms = Room::where('status', 'dirty')
            ->orderBy('room_number')
            ->limit(5)
            ->get();
        
        $recentlyCleaned = Room::where('status', 'available')
            ->where('updated_at', '>=', now()->subHours(24))
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();
        
        return view('dashboard', [
            'user' => $user,
            'rolesDisplay' => $rolesDisplay,
            'stats' => $stats,
            'tasks' => $tasks,
            'todayShifts' => $todayShifts,
            'dirtyRooms' => $dirtyRooms,
            'recentlyCleaned' => $recentlyCleaned,
            
            // Safe defaults
            'notifications' => collect(),
            'recentBookings' => collect(),
            'upcomingCheckins' => collect(),
            'recentPayments' => collect(),
            'pendingOrders' => [],
            'pendingCheckins' => collect(),
            'pendingCheckouts' => collect(),
            'todayArrivals' => 0,
            'todayDepartures' => 0,
            'pendingPayments' => 0,
            'invoicesToApprove' => 0,
            'currentStays' => collect(),
            'pastStays' => collect(),
            'pendingTasksCount' => $tasks->count(),
            'todayShiftsCount' => $todayShifts->count(),
            'notificationsCount' => 0,
        ]);
    }

    private function kitchenDashboard(User $user, string $rolesDisplay)
    {
        // Get overall kitchen stats
        $stats = $this->getKitchenStats();

        // Fetch kitchen orders
        $pendingOrders = $this->kitchenService->getPendingOrders();
        $preparingOrders = $this->kitchenService->getPreparingOrders();
        $completedToday = $this->kitchenService->getCompletedOrdersCount(today());

        // Fetch low stock items (limit 5)
        $lowStockItems = InventoryItem::whereColumn('quantity', '<=', 'reorder_level')
            ->where('status', true)
            ->orderBy('quantity')
            ->limit(5)
            ->get();

        // Return dashboard view with all data
        return view('dashboard', [
            'user' => $user,
            'rolesDisplay' => $rolesDisplay,
            'stats' => $stats,
            'pendingOrders' => $pendingOrders,
            'preparingOrders' => $preparingOrders,
            'completedToday' => $completedToday,
            'lowStockItems' => $lowStockItems,

            // Safe defaults for other dashboard sections
            'tasks' => collect(),
            'todayShifts' => collect(),
            'notifications' => collect(),
            'recentBookings' => collect(),
            'upcomingCheckins' => collect(),
            'recentPayments' => collect(),
            'pendingCheckins' => collect(),
            'pendingCheckouts' => collect(),
            'todayArrivals' => 0,
            'todayDepartures' => 0,
            'pendingPayments' => 0,
            'invoicesToApprove' => 0,
            'currentStays' => collect(),
            'pastStays' => collect(),
            'pendingTasksCount' => 0,
            'todayShiftsCount' => 0,
            'notificationsCount' => 0,
        ]);
    }

    private function procurementDashboard(User $user, string $rolesDisplay)
    {
        $stats = $this->getProcurementStats();
        
        $lowStockItems = InventoryItem::whereColumn('quantity', '<=', 'reorder_level')
            ->where('status', true)
            ->orderBy('quantity')
            ->limit(10)
            ->get();
        
        $pendingOrders = PurchaseOrder::where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        $awaitingDelivery = PurchaseOrder::where('status', 'approved')
            ->where('status', '!=', 'delivered')
            ->orderBy('received_at')
            ->limit(5)
            ->get();
        
        return view('dashboard', [
            'user' => $user,
            'rolesDisplay' => $rolesDisplay,
            'stats' => $stats,
            'lowStockItems' => $lowStockItems,
            'pendingOrders' => $pendingOrders,
            'awaitingDelivery' => $awaitingDelivery,
            
            // Safe defaults
            'tasks' => collect(),
            'todayShifts' => collect(),
            'notifications' => collect(),
            'recentBookings' => collect(),
            'upcomingCheckins' => collect(),
            'recentPayments' => collect(),
            'pendingCheckins' => collect(),
            'pendingCheckouts' => collect(),
            'todayArrivals' => 0,
            'todayDepartures' => 0,
            'pendingPayments' => 0,
            'invoicesToApprove' => 0,
            'currentStays' => collect(),
            'pastStays' => collect(),
            'pendingTasksCount' => 0,
            'todayShiftsCount' => 0,
            'notificationsCount' => 0,
        ]);
    }

    private function hrDashboard(User $user, string $rolesDisplay)
    {
        $stats = $this->getHRStats();
        
        $pendingLeave = LeaveRequest::where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        $activeStaff = User::where('status', 'active')
            ->whereHas('roles', function ($q) {
                $q->where('name', '!=', 'guest');
            })
            ->count();
        
        $upcomingBirthdays = User::whereMonth('dob', now()->month)
            ->whereDay('dob', '>=', now()->day)
            ->orderByRaw('DAY(dob)')
            ->limit(5)
            ->get();
        
        return view('dashboard', [
            'user' => $user,
            'rolesDisplay' => $rolesDisplay,
            'stats' => $stats,
            'pendingLeave' => $pendingLeave,
            'activeStaff' => $activeStaff,
            'upcomingBirthdays' => $upcomingBirthdays,
            
            // Safe defaults
            'tasks' => collect(),
            'todayShifts' => collect(),
            'notifications' => collect(),
            'recentBookings' => collect(),
            'upcomingCheckins' => collect(),
            'recentPayments' => collect(),
            'pendingCheckins' => collect(),
            'pendingCheckouts' => collect(),
            'todayArrivals' => 0,
            'todayDepartures' => 0,
            'pendingPayments' => 0,
            'invoicesToApprove' => 0,
            'currentStays' => collect(),
            'pastStays' => collect(),
            'pendingTasksCount' => 0,
            'todayShiftsCount' => 0,
            'notificationsCount' => 0,
        ]);
    }

    private function managerDashboard(User $user, string $rolesDisplay)
    {
        // Managers see admin stats but with some additional data
        $stats = $this->getAdminStats();
    
        $recentBookings = Stay::with(['guest', 'roomAllocations.room'])
            ->whereIn('status', ['checked_in', 'booked'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        $staffPerformance = $this->getStaffPerformance();
        $departmentStats = $this->getDepartmentStats();
        
        return view('dashboard', [
            'user' => $user,
            'rolesDisplay' => $rolesDisplay,
            'stats' => $stats,
            'recentBookings' => $recentBookings,
            'staffPerformance' => $staffPerformance,
            'departmentStats' => $departmentStats,
            
            // Safe defaults
            'tasks' => collect(),
            'todayShifts' => collect(),
            'notifications' => collect(),
            'upcomingCheckins' => collect(),
            'recentPayments' => collect(),
            'pendingOrders' => [],
            'pendingCheckins' => collect(),
            'pendingCheckouts' => collect(),
            'todayArrivals' => 0,
            'todayDepartures' => 0,
            'pendingPayments' => 0,
            'invoicesToApprove' => 0,
            'currentStays' => collect(),
            'pastStays' => collect(),
            'pendingTasksCount' => 0,
            'todayShiftsCount' => 0,
            'notificationsCount' => 0
        ]);
    }

    private function getFallbackDashboard(User $user, string $rolesDisplay)
    {
        return view('dashboard', [
            'user' => $user,
            'rolesDisplay' => $rolesDisplay,
            'stats' => $this->getDefaultStats(),
            'tasks' => collect(),
            'todayShifts' => collect(),
            'notifications' => collect(),
            'recentBookings' => collect(),
            'upcomingCheckins' => collect(),
            'recentPayments' => collect(),
            'pendingOrders' => [],
            'pendingCheckins' => collect(),
            'pendingCheckouts' => collect(),
            'todayArrivals' => 0,
            'todayDepartures' => 0,
            'pendingPayments' => 0,
            'invoicesToApprove' => 0,
            'currentStays' => collect(),
            'pastStays' => collect(),
            'pendingTasksCount' => 0,
            'todayShiftsCount' => 0,
            'notificationsCount' => 0,
        ]);
    }

    // ==================== STATS METHODS ====================

    private function getAdminStats(): array
    {
        $today = now()->format('d/m/Y');
        
        return [
            'today_arrivals' => Stay::whereDate('arrival_date', $today)
                ->where('status', '!=', 'cancelled')
                ->count(),
            'today_departures' => Stay::whereDate('departure_date', $today)
                ->whereIn('status', ['checked_in', 'checked_out'])
                ->count(),
            'occupancy_rate' => $this->calculateOccupancyRate(),
            'today_revenue' => Payment::whereDate('created_at', $today)->sum('amount'),
            'pending_tasks' => Task::whereIn('status', ['pending', 'assigned'])->count(),
            'low_stock_items' => InventoryItem::whereColumn('quantity', '<=', 'reorder_level')
                ->where('status', true)
                ->count(),
            'total_rooms' => Room::count(),
            'available_rooms' => Room::where('status', 'available')->count(),
        ];
    }

    private function getFinanceStats(): array
    {
        $today = now()->format('d/m/Y');
        $monthStart = now()->startOfMonth();
        
        return [
            'today_payments' => Payment::whereDate('created_at', $today)->sum('amount'),
            'month_revenue' => Payment::whereBetween('created_at', [$monthStart, now()])->sum('amount'),
            'pending_payments' => Payment::where('status', 'pending')->count(),
            'overdue_invoices' => Invoice::where('due_date', '<', $today)
                ->whereIn('status', ['sent', 'partial'])
                ->count(),
            'total_receivables' => Invoice::whereIn('status', ['sent', 'partial'])
                ->sum('total'),
            'collected_today' => Payment::whereDate('created_at', $today)->sum('amount'),
        ];
    }

    private function getReceptionistStats(): array
    {
        $today = now()->format('d/m/Y');
        
        return [
            'today_checkins' => Stay::whereDate('arrival_date', $today)
                ->where('status', 'booked')
                ->count(),
            'today_checkouts' => Stay::whereDate('departure_date', $today)
                ->where('status', 'checked_in')
                ->count(),
            'available_rooms' => Room::where('status', 'available')->count(),
            'dirty_rooms' => Room::where('status', 'dirty')->count(),
            'occupied_rooms' => Room::where('status', 'occupied')->count(),
            'today_reservations' => Stay::whereDate('arrival_date', $today)
                ->where('status', 'booked')
                ->count(),
        ];
    }

    private function getHousekeepingStats(User $user): array
    {
        $today = now()->format('d/m/Y');
        
        return [
            'pending_tasks' => Task::where('assigned_to', $user->id)
                ->whereIn('status', ['pending', 'assigned'])
                ->count(),
            'completed_today' => Task::where('assigned_to', $user->id)
                ->where('status', 'completed')
                ->whereDate('completed_at', $today)
                ->count(),
            'rooms_to_clean' => Room::where('status', 'dirty')->count(),
            'rooms_cleaned_today' => Room::where('status', 'available')
                ->whereDate('updated_at', $today)
                ->count(),
            'cleaning_time_avg' => '45 mins', // Placeholder
        ];
    }

    private function getKitchenStats(): array
    {
        $today = now()->format('d/m/Y');
        
        return [
            'pending_orders' => $this->kitchenService->getPendingOrdersCount(),
            'preparing_orders' => $this->kitchenService->getPreparingOrdersCount(),
            'completed_today' => $this->kitchenService->getCompletedOrdersCount($today),
            'low_stock_items' => InventoryItem::whereColumn('quantity', '<=', 'reorder_level')
                ->where('status', true)
                ->count(),
            'popular_item' => 'Chicken Curry', // Placeholder
        ];
    }

    private function getProcurementStats(): array
    {
        return [
            'low_stock_items' => InventoryItem::whereColumn('quantity', '<=', 'reorder_level')
                ->where('status', true)
                ->count(),
            'pending_orders' => PurchaseOrder::where('status', 'pending')->count(),
            'awaiting_delivery' => PurchaseOrder::where('status', 'approved')
                ->where('status', '!=', 'delivered')
                ->count(),
            'total_inventory_value' => InventoryItem::sum(\DB::raw('quantity * unit_cost')),
            'recent_deliveries' => PurchaseOrder::where('status', 'delivered')
                ->whereDate('delivery_date', '>=', now()->subDays(7))
                ->count(),
        ];
    }

    private function getHRStats(): array
    {
        return [
            'total_staff' => User::whereHas('roles', function ($q) {
                $q->where('name', '!=', 'guest');
            })->count(),
            'active_staff' => User::where('status', 'active')
                ->whereHas('roles', function ($q) {
                    $q->where('name', '!=', 'guest');
                })->count(),
            'pending_leave' => LeaveRequest::where('status', 'pending')->count(),
            'on_leave_today' => LeaveRequest::whereDate('start_date', '<=', today())
                ->whereDate('end_date', '>=', today())
                ->where('status', 'approved')
                ->count(),
            'new_hires_this_month' => User::whereHas('roles', function ($q) {
                $q->where('name', '!=', 'guest');
            })->whereMonth('created_at', now()->month)->count(),
        ];
    }

    private function getDefaultStats(): array
    {
        return [
            'today_arrivals' => 0,
            'today_departures' => 0,
            'occupancy_rate' => 0,
            'today_revenue' => 0,
            'pending_tasks' => 0,
            'low_stock_items' => 0,
        ];
    }

    public function getReceptionistDashboardData(Request $request)
    {
        $user = $request->user();
        
        if (!$user->hasRole('receptionist')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        // Get all stays with current status
        $stays = $this->getAllStays();
        
        // Get available facilities
        $facilities = $this->getAvailableFacilities();
        
        // Get rooms that need attention
        $rooms = $this->getRoomsForDashboard();
        
        return response()->json([
            'success' => true,
            'stays' => $stays,
            'facilities' => $facilities,
            'rooms' => $rooms,
            'stats' => $this->getReceptionistStats(),
            'quickActions' => $this->getQuickActions()
        ]);
    }
    
    private function getAllStays()
    {
        $stays = Stay::with([
                'guest',
                'roomAllocations.room.roomType',
                'facilityAllocations.facility',
                'invoices'
            ])
            ->whereIn('status', ['booked', 'checked_in', 'checked_out'])
            ->orderBy('arrival_date', 'asc')
            ->limit(20)
            ->get()
            ->map(function ($stay) {
                return [
                    'id' => $stay->id,
                    'guest_name' => $stay->guest->name ?? 'N/A',
                    'guest_initials' => isset($stay->guest->name) ? strtoupper(substr($stay->guest->name, 0, 1)) : '?',
                    'guest_id' => $stay->guest_id,
                    'status' => $stay->status,
                    'arrival_date' => optional($stay->arrival_date)->format('M d, Y') ?? 'N/A',
                    'departure_date' => optional($stay->departure_date)->format('M d, Y') ?? 'N/A',
                    'arrival_time' => optional($stay->arrival_date)->format('h:i A') ?? 'N/A',
                    'departure_time' => optional($stay->departure_date)->format('h:i A') ?? 'N/A',
                    'adults' => $stay->adults ?? 1,
                    'children' => $stay->children ?? 0,
                    'rooms' => $stay->roomAllocations->map(function ($allocation) {
                        return [
                            'number' => $allocation->room->room_number ?? 'N/A',
                            'type' => $allocation->room->roomType->name ?? 'N/A',
                            'allocation_id' => $allocation->id
                        ];
                    })->toArray(),
                    'facilities' => $stay->facilityAllocations->map(function ($allocation) {
                        return [
                            'name' => $allocation->facility->name ?? 'N/A',
                            'start_time' => optional($allocation->start_time)->format('h:i A') ?? 'N/A',
                            'end_time' => optional($allocation->end_time)->format('h:i A') ?? 'N/A'
                        ];
                    })->toArray(),
                    'total_invoice' => $stay->invoices->whereIn('status', ['sent', 'partial'])->sum('total'),
                    'check_in_url' => route('guest.check-in', $stay),
                    'check_out_url' => route('guest.check-out', $stay),
                    'view_url' => route('stays.show', $stay),
                    'is_checked_in' => $stay->status === 'checked_in',
                    'is_booked' => $stay->status === 'booked',
                    'is_checked_out' => $stay->status === 'checked_out'
                ];
            });
        
        return $stays;
    }
    
    private function getAvailableFacilities()
    {
        $facilities = Facility::with(['currentAllocations.stay.guest'])
            ->where('status', 'available')
            ->get()
            ->map(function ($facility) {
                $currentAllocations = $facility->currentAllocations;
                
                return [
                    'id' => $facility->id,
                    'name' => $facility->name,
                    'type' => $facility->type,
                    'capacity' => $facility->capacity,
                    'base_rate' => $facility->base_rate,
                    'status' => $facility->status,
                    'is_available' => $currentAllocations->isEmpty() && $facility->status === 'available',
                    'current_bookings' => $currentAllocations->map(function ($allocation) {
                        return [
                            'guest_name' => $allocation->stay->guest->name ?? 'N/A',
                            'start_time' => optional($allocation->start_time)->format('h:i A'),
                            'end_time' => optional($allocation->end_time)->format('h:i A'),
                            'status' => $allocation->status
                        ];
                    })->toArray(),
                    'book_url' => route('facilities.book', $facility),
                    'view_url' => route('facilities.show', $facility)
                ];
            });
        
        return $facilities;
    }
    
    private function getRoomsForDashboard()
    {
        $rooms = Room::with(['roomType', 'currentAllocation.stay.guest'])
            ->get()
            ->map(function ($room) {
                $currentAllocation = $room->currentAllocation;
                
                return [
                    'id' => $room->id,
                    'room_number' => $room->room_number,
                    'type' => $room->roomType->name ?? 'N/A',
                    'status' => $room->status,
                    'current_guest' => $currentAllocation ? $currentAllocation->stay->guest->name ?? 'N/A' : null,
                    'check_out_date' => $currentAllocation ? optional($currentAllocation->to_date)->format('M d') : null,
                    'is_available' => $room->status === 'available',
                    'needs_cleaning' => $room->status === 'dirty',
                    'is_occupied' => $room->status === 'occupied',
                    'view_url' => route('rooms.show', $room)
                ];
            });
        
        return $rooms;
    }
    
    private function getQuickActions()
    {
        return [
            [
                'title' => 'New Booking',
                'icon' => 'new-booking',
                'color' => 'primary',
                'url' => route('stays.create'),
                'description' => 'Create new reservation'
            ],
            [
                'title' => 'Walk-in Guest',
                'icon' => 'walk-in',
                'color' => 'success',
                'url' => route('stays.create', ['walk_in' => true]),
                'description' => 'Check-in without reservation'
            ],
            [
                'title' => 'Room Status',
                'icon' => 'room-status',
                'color' => 'warning',
                'url' => route('rooms.index'),
                'description' => 'View all room statuses'
            ],
            [
                'title' => 'Process Payment',
                'icon' => 'payment',
                'color' => 'info',
                'url' => route('finance.payments.create'),
                'description' => 'Receive guest payment'
            ],
            [
                'title' => 'Book Facility',
                'icon' => 'facility',
                'color' => 'secondary',
                'url' => route('facilities.index'),
                'description' => 'Book hotel facilities'
            ],
            [
                'title' => 'Quick Check-in',
                'icon' => 'check-in',
                'color' => 'primary',
                'url' => route('reception.quick-checkin'),
                'description' => 'Fast guest check-in'
            ]
        ];
    }


    private function calculateOccupancyRate(): float
    {
        $totalRooms = Room::count();
        $occupiedRooms = RoomAllocation::whereDate('from_date', '<=', today())
            ->whereDate('to_date', '>=', today())
            ->count();
        
        return $totalRooms > 0 ? ($occupiedRooms / $totalRooms) * 100 : 0;
    }

    private function getStaffPerformance(): Collection
    {
        // Placeholder - implement actual performance metrics
        return collect([
            ['name' => 'John Doe', 'department' => 'Reception', 'tasks_completed' => 45, 'rating' => 4.5],
            ['name' => 'Jane Smith', 'department' => 'Housekeeping', 'tasks_completed' => 38, 'rating' => 4.8],
            ['name' => 'Bob Wilson', 'department' => 'Kitchen', 'tasks_completed' => 52, 'rating' => 4.3],
        ]);
    }

    private function getDepartmentStats(): Collection
    {
        // Placeholder - implement actual department stats
        return collect([
            ['department' => 'Reception', 'staff_count' => 4, 'tasks' => 15],
            ['department' => 'Housekeeping', 'staff_count' => 8, 'tasks' => 42],
            ['department' => 'Kitchen', 'staff_count' => 6, 'tasks' => 28],
            ['department' => 'Finance', 'staff_count' => 3, 'tasks' => 12],
        ]);
    }

    /**
     * Get dashboard data for AJAX requests.
     */
    public function getDashboardData(Request $request)
    {
        $user = $request->user();
        $period = $request->input('period', 'today');
        
        // Determine date range based on period
        switch ($period) {
            case 'week':
                $startDate = now()->startOfWeek();
                $endDate = now()->endOfWeek();
                break;
            case 'month':
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
                break;
            case 'year':
                $startDate = now()->startOfYear();
                $endDate = now()->endOfYear();
                break;
            default: // today
                $startDate = now()->startOfDay();
                $endDate = now()->endOfDay();
        }
        
        $data = [];
        
        // Admin/Manager specific data
        if ($user->hasRole('admin') || $user->hasRole('manager')) {
            $data = [
                'financial' => $this->financeService->getIncomeStatement($startDate, $endDate),
                'occupancy' => $this->calculateOccupancyRate(),
                'pending_tasks' => Task::whereIn('status', ['pending', 'assigned'])->count(),
                'kitchen_stats' => $this->kitchenService->getPendingOrdersCount(),
            ];
        }
        
        // Finance specific data
        if ($user->hasRole('finance')) {
            $data = [
                'revenue' => $this->financeService->getRevenueStats($startDate, $endDate),
                'pending_payments' => $this->financeService->getPendingPaymentsCount(),
                'overdue_invoices' => $this->financeService->getOverdueInvoicesCount(),
            ];
        }
        
        // Guest specific data
        if ($user->hasRole('guest')) {
            $data['my_stays'] = $this->guestService->getGuestCurrentStays($user);
            $data['my_invoices'] = $user->stays()
                ->with('invoices')
                ->whereHas('invoices', function ($q) {
                    $q->whereIn('status', ['sent', 'partial']);
                })
                ->get();
        }
        
        return response()->json([
            'success' => true,
            'data' => $data,
            'period' => $period,
        ]);
    }

    // Add to DashboardController

/**
 * Get receptionist dashboard data for AJAX
 */
public function getReceptionistData(Request $request)
{
    $user = $request->user();
    
    if (!$user->hasRole('receptionist')) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }
    
    $today = now()->format('Y-m-d');
    
    return response()->json([
        'today_arrivals' => Stay::whereDate('arrival_date', $today)
            ->where('status', 'booked')
            ->count(),
        'today_departures' => Stay::whereDate('departure_date', $today)
            ->where('status', 'checked_in')
            ->count(),
        'available_rooms' => Room::where('status', 'available')->count(),
        'dirty_rooms' => Room::where('status', 'dirty')->count(),
        'pending_checkins' => Stay::where('status', 'booked')
            ->whereDate('arrival_date', '<=', $today)
            ->whereDoesntHave('roomAllocations', function ($query) {
                $query->whereDate('to_date', '>=', now());
            })
            ->count(),
        'pending_checkouts' => Stay::where('status', 'checked_in')
            ->whereDate('departure_date', $today)
            ->count(),
    ]);
}

/**
 * Get check-ins data for AJAX
 */
public function getCheckinsData(Request $request)
{
    $user = $request->user();
    
    if (!$user->hasRole('receptionist')) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }
    
    $checkins = Stay::with(['guest', 'roomAllocations.room'])
        ->where('status', 'booked')
        ->whereDate('arrival_date', '<=', now())
        ->whereDoesntHave('roomAllocations', function ($query) {
            $query->whereDate('to_date', '>=', now());
        })
        ->orderBy('arrival_date')
        ->limit(10)
        ->get()
        ->map(function ($stay) {
            return [
                'id' => $stay->id,
                'guest_name' => $stay->guest->name ?? 'N/A',
                'guest_initials' => isset($stay->guest->name) ? strtoupper(substr($stay->guest->name, 0, 1)) : '?',
                'adults' => $stay->adults ?? 1,
                'children' => $stay->children ?? 0,
                'arrival_time' => optional($stay->arrival_date)->format('h:i A') ?? 'N/A',
                'arrival_date' => optional($stay->arrival_date)->format('M d, Y') ?? 'N/A',
                'departure_date' => optional($stay->departure_date)->format('M d, Y') ?? 'N/A',
                'rooms' => $stay->roomAllocations->map(function ($allocation) {
                    return $allocation->room->room_number ?? 'N/A';
                })->toArray(),
                'check_in_url' => route('guest.check-in', $stay)
            ];
        });
    
    return response()->json($checkins);
}

/**
 * Get check-outs data for AJAX
 */
public function getCheckoutsData(Request $request)
{
    $user = $request->user();
    
    if (!$user->hasRole('receptionist')) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }
    
    $checkouts = Stay::with(['guest', 'roomAllocations.room.roomType'])
        ->where('status', 'checked_in')
        ->whereDate('departure_date', now())
        ->orderBy('departure_date')
        ->limit(10)
        ->get()
        ->map(function ($stay) {
            return [
                'id' => $stay->id,
                'guest_name' => $stay->guest->name ?? 'N/A',
                'guest_initials' => isset($stay->guest->name) ? strtoupper(substr($stay->guest->name, 0, 1)) : '?',
                'departure_time' => optional($stay->departure_date)->format('h:i A') ?? 'N/A',
                'arrival_date' => optional($stay->arrival_date)->format('M d') ?? 'N/A',
                'rooms' => $stay->roomAllocations->map(function ($allocation) {
                    return [
                        'number' => $allocation->room->room_number ?? 'N/A',
                        'type' => $allocation->room->roomType->name ?? 'N/A'
                    ];
                })->toArray(),
                'check_out_url' => route('guest.check-out', $stay)
            ];
        });
    
    return response()->json($checkouts);
}
}