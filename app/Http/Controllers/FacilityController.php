<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Facility;
use App\Models\FacilityAllocation;
use App\Models\FacilityPackage;
use App\Models\Stay;
use App\Services\OperationsService;
use App\Services\CoreService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FacilityController extends Controller
{
    public function __construct(
        private OperationsService $operationsService,
        private CoreService $coreService
    ) {}
    
    /**
     * Display a listing of the facilities.
     */
    public function index(Request $request)
    {
        $query = Facility::withCount(['allocations', 'packages']);
        
        // Apply search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Apply status filter
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        
        // Apply type filter
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }
        
        // Apply capacity filter
        if ($request->has('min_capacity')) {
            $query->where('capacity', '>=', $request->min_capacity);
        }
        
        // Ordering
        $orderBy = $request->order_by ?? 'name';
        $orderDir = $request->order_dir ?? 'asc';
        $query->orderBy($orderBy, $orderDir);
        
        $facilities = $query->paginate($request->per_page ?? 20);
        
        // Get statistics for dashboard
        $stats = [
            'total_facilities' => Facility::count(),
            'available_facilities' => Facility::available()->count(),
            'booked_today' => FacilityAllocation::whereDate('start_time', today())->count(),
            'revenue_today' => FacilityAllocation::whereDate('created_at', today())
                ->sum(DB::raw('rate_applied * TIMESTAMPDIFF(HOUR, start_time, end_time)')),
        ];
        
        // For AJAX requests, return JSON
        if ($request->ajax()) {
            return response()->json([
                'facilities' => $facilities,
                'stats' => $stats,
                'filters' => $request->all()
            ]);
        }
        
        return view('facilities.index', compact('facilities', 'stats'));
    }
    
    /**
     * Show the form for creating a new facility.
     */
    public function create()
    {
        $facilityTypes = ['conference', 'pool', 'gym', 'spa', 'restaurant', 'bar', 'meeting_room', 'banquet_hall'];
        $amenityOptions = ['wifi', 'ac', 'projector', 'sound_system', 'whiteboard', 'catering', 'parking', 'security'];
        
        return view('facilities.create', compact('facilityTypes', 'amenityOptions'));
    }
    
    /**
     * Store a newly created facility in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:facilities,code',
            'type' => 'required|string',
            'capacity' => 'required|integer|min:1',
            'base_rate' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'amenities' => 'nullable|array',
            'amenities.*' => 'string',
            'status' => 'required|in:available,maintenance,unavailable',
        ]);
        
        $facility = Facility::create($validated);
        
        $this->coreService->log(auth()->id(), 'facility_created', 
            "Facility {$facility->name} created with ID {$facility->id}");
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Facility created successfully.',
                'facility' => $facility->loadCount(['allocations', 'packages'])
            ]);
        }
        
        return redirect()->route('facilities.show', $facility)
            ->with('success', 'Facility created successfully.');
    }
    
    /**
     * Display the specified facility.
     */
    public function show(Request $request, Facility $facility)
    {
        $facility->load(['packages', 'allocations' => function($query) {
            $query->orderBy('start_time', 'desc')->limit(10);
        }, 'allocations.stay.guest']);
        
        // Get upcoming allocations
        $upcomingAllocations = $facility->allocations()
            ->where('start_time', '>', now())
            ->whereIn('status', ['booked', 'confirmed'])
            ->with('stay.guest')
            ->orderBy('start_time')
            ->limit(10)
            ->get();
        
        // Get current allocations
        $currentAllocations = $facility->currentAllocations()->with('stay.guest')->get();
        
        // Get statistics for this facility
        $stats = [
            'total_bookings' => $facility->allocations()->count(),
            'today_bookings' => $facility->allocations()->whereDate('start_time', today())->count(),
            'upcoming_bookings' => $upcomingAllocations->count(),
            'current_bookings' => $currentAllocations->count(),
            'total_revenue' => $facility->allocations()->sum(DB::raw('rate_applied * TIMESTAMPDIFF(HOUR, start_time, end_time)')),
        ];
        
        // For AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'facility' => $facility,
                'upcoming_allocations' => $upcomingAllocations,
                'current_allocations' => $currentAllocations,
                'stats' => $stats
            ]);
        }
        
        return view('facilities.show', compact('facility', 'upcomingAllocations', 'currentAllocations', 'stats'));
    }
    
    /**
     * Show the form for editing the specified facility.
     */
    public function edit(Facility $facility)
    {
        $facilityTypes = ['conference', 'pool', 'gym', 'spa', 'restaurant', 'bar', 'meeting_room', 'banquet_hall'];
        $amenityOptions = ['wifi', 'ac', 'projector', 'sound_system', 'whiteboard', 'catering', 'parking', 'security'];
        
        return view('facilities.edit', compact('facility', 'facilityTypes', 'amenityOptions'));
    }
    
    /**
     * Update the specified facility in storage.
     */
    public function update(Request $request, Facility $facility)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:facilities,code,' . $facility->id,
            'type' => 'required|string',
            'capacity' => 'required|integer|min:1',
            'base_rate' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'amenities' => 'nullable|array',
            'amenities.*' => 'string',
            'status' => 'required|in:available,maintenance,unavailable',
        ]);
        
        $oldStatus = $facility->status;
        $facility->update($validated);
        
        // Log status change
        if ($oldStatus !== $facility->status) {
            $this->coreService->log(auth()->id(), 'facility_status_updated', 
                "Facility {$facility->name} status changed: {$oldStatus} → {$facility->status}");
        }
        
        $this->coreService->log(auth()->id(), 'facility_updated', 
            "Facility {$facility->name} updated");
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Facility updated successfully.',
                'facility' => $facility->fresh()->loadCount(['allocations', 'packages'])
            ]);
        }
        
        return redirect()->route('facilities.show', $facility)
            ->with('success', 'Facility updated successfully.');
    }
    
    /**
     * Remove the specified facility from storage.
     */
    public function destroy(Request $request, Facility $facility)
    {
        // Check if facility has any active allocations
        $activeAllocations = $facility->allocations()
            ->whereIn('status', ['booked', 'confirmed', 'in_use'])
            ->exists();
        
        if ($activeAllocations) {
            $message = 'Cannot delete facility with active bookings.';
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 400);
            }
            
            return redirect()->back()->with('error', $message);
        }
        
        $facilityName = $facility->name;
        $facility->delete();
        
        $this->coreService->log(auth()->id(), 'facility_deleted', 
            "Facility {$facilityName} deleted");
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Facility deleted successfully.'
            ]);
        }
        
        return redirect()->route('facilities.index')
            ->with('success', 'Facility deleted successfully.');
    }
    
    /**
     * Book a facility.
     */
    public function book(Request $request)
    {
        $validated = $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'stay_id' => 'nullable|exists:stays,id',
            'package_id' => 'nullable|exists:facility_packages,id',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'guest_name' => 'required_if:stay_id,null|string|max:255',
            'guest_email' => 'nullable|email',
            'guest_phone' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
        
        try {
            $allocation = $this->operationsService->createFacilityBooking([
                'facility_id' => $validated['facility_id'],
                'stay_id' => $validated['stay_id'] ?? null,
                'package_id' => $validated['package_id'] ?? null,
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'guest_name' => $validated['guest_name'] ?? null,
                'guest_email' => $validated['guest_email'] ?? null,
                'guest_phone' => $validated['guest_phone'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);
            
            $message = 'Facility booked successfully.';
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'allocation' => $allocation->load(['facility', 'stay.guest'])
                ]);
            }
            
            return redirect()->route('facilities.allocations.show', $allocation)
                ->with('success', $message);
                
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
            
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    
    /**
     * Show booking form.
     */
    public function showBookForm(Request $request, Facility $facility = null)
    {
        $facilities = Facility::available()->get();
        $stays = Stay::whereIn('status', ['booked', 'checked_in'])->get();
        
        // If specific facility is selected
        $selectedFacility = $facility;
        $packages = $selectedFacility ? $selectedFacility->activePackages()->get() : collect();
        
        return view('facilities.book', compact('facilities', 'stays', 'selectedFacility', 'packages'));
    }
    
    /**
     * Check facility availability.
     */
    public function checkAvailability(Request $request)
    {
        $validated = $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);
        
        $facility = Facility::findOrFail($validated['facility_id']);
        
        $isAvailable = $this->operationsService->isFacilityAvailable(
            $facility,
            $validated['start_time'],
            $validated['end_time']
        );
        
        $conflictingAllocations = [];
        
        if (!$isAvailable) {
            $conflictingAllocations = FacilityAllocation::where('facility_id', $facility->id)
                ->where(function ($query) use ($validated) {
                    $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                        ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                        ->orWhere(function ($q) use ($validated) {
                            $q->where('start_time', '<=', $validated['start_time'])
                                ->where('end_time', '>=', $validated['end_time']);
                        });
                })
                ->whereIn('status', ['booked', 'confirmed', 'in_use'])
                ->with('stay.guest')
                ->get();
        }
        
        return response()->json([
            'available' => $isAvailable,
            'facility' => $facility,
            'conflicting_allocations' => $conflictingAllocations,
            'message' => $isAvailable ? 
                'Facility is available for booking.' : 
                'Facility is not available for the selected time slot.'
        ]);
    }
    
    /**
     * Get available facilities for time slot.
     */
    public function getAvailableFacilities(Request $request)
    {
        $validated = $request->validate([
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'capacity' => 'nullable|integer|min:1',
            'type' => 'nullable|string',
        ]);
        
        $availableFacilities = $this->operationsService->getAvailableFacilities(
            $validated['start_time'],
            $validated['end_time'],
            $validated['capacity'] ?? null,
            $validated['type'] ?? null
        );
        
        return response()->json([
            'available_facilities' => $availableFacilities,
            'count' => $availableFacilities->count(),
            'time_slot' => [
                'start' => $validated['start_time'],
                'end' => $validated['end_time']
            ]
        ]);
    }
    
    /**
     * Confirm a facility booking.
     */
    public function confirmBooking(Request $request, FacilityAllocation $allocation)
    {
        try {
            $confirmedAllocation = $this->operationsService->confirmFacilityBooking($allocation);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Booking confirmed successfully.',
                    'allocation' => $confirmedAllocation->load(['facility', 'stay.guest'])
                ]);
            }
            
            return redirect()->back()->with('success', 'Booking confirmed successfully.');
            
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
            
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    
    /**
     * Cancel a facility booking.
     */
    public function cancelBooking(Request $request, FacilityAllocation $allocation)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:255',
        ]);
        
        try {
            DB::transaction(function () use ($allocation, $validated) {
                $allocation->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'cancelled_by' => auth()->id(),
                    'cancellation_reason' => $validated['reason'],
                ]);
                
                // If linked to stay, remove from invoice
                if ($allocation->stay_id) {
                    $invoice = $allocation->stay->currentInvoice();
                    if ($invoice) {
                        $invoice->items()
                            ->where('source_type', 'facility')
                            ->where('reference_id', $allocation->id)
                            ->delete();
                    }
                }
                
                // Update facility status if it was in use
                if ($allocation->facility->status === 'in_use') {
                    $allocation->facility->update(['status' => 'available']);
                }
                
                $this->coreService->log(auth()->id(), 'facility_booking_cancelled', 
                    "Facility booking #{$allocation->id} cancelled. Reason: {$validated['reason']}");
            });
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Booking cancelled successfully.',
                    'allocation' => $allocation->fresh()->load(['facility', 'stay.guest'])
                ]);
            }
            
            return redirect()->back()->with('success', 'Booking cancelled successfully.');
            
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
            
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    
    /**
     * List all facility allocations.
     */
    public function allocations(Request $request)
    {
        $query = FacilityAllocation::with(['facility', 'stay.guest', 'package']);
        
        // Apply filters
        if ($request->has('facility_id') && $request->facility_id) {
            $query->where('facility_id', $request->facility_id);
        }
        
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('start_time', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('start_time', '<=', $request->date_to);
        }
        
        if ($request->has('stay_id') && $request->stay_id) {
            $query->where('stay_id', $request->stay_id);
        }
        
        // Ordering
        $orderBy = $request->order_by ?? 'start_time';
        $orderDir = $request->order_dir ?? 'desc';
        $query->orderBy($orderBy, $orderDir);
        
        $allocations = $query->paginate($request->per_page ?? 20);
        
        // Get facilities for filter dropdown
        $facilities = Facility::all();
        
        // For AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'allocations' => $allocations,
                'facilities' => $facilities
            ]);
        }
        
        return view('facilities.allocations.index', compact('allocations', 'facilities'));
    }
    
    /**
     * Show a specific allocation.
     */
    public function showAllocation(Request $request, FacilityAllocation $allocation)
    {
        $allocation->load(['facility', 'stay.guest', 'stay.roomAllocations.room', 'package']);
        
        if ($request->ajax()) {
            return response()->json([
                'allocation' => $allocation
            ]);
        }
        
        return view('facilities.allocations.show', compact('allocation'));
    }
    
    /**
     * Update facility status.
     */
    public function updateStatus(Request $request, Facility $facility)
    {
        $validated = $request->validate([
            'status' => 'required|in:available,maintenance,unavailable',
            'reason' => 'nullable|string|max:500',
        ]);
        
        try {
            $updatedFacility = $this->operationsService->updateFacilityStatus(
                $facility,
                $validated['status'],
                $validated['reason'] ?? null
            );
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Facility status updated successfully.',
                    'facility' => $updatedFacility
                ]);
            }
            
            return redirect()->back()->with('success', 'Facility status updated successfully.');
            
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
            
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    
    /**
     * Manage facility packages.
     */
    public function packages(Request $request, Facility $facility)
    {
        $packages = $facility->packages()->orderBy('price')->get();
        
        if ($request->ajax()) {
            return response()->json([
                'packages' => $packages
            ]);
        }
        
        return view('facilities.packages.index', compact('facility', 'packages'));
    }
    
    /**
     * Create a new package for facility.
     */
    public function createPackage(Request $request, Facility $facility)
    {
        if ($request->ajax()) {
            return response()->json([
                'facility' => $facility
            ]);
        }
        
        return view('facilities.packages.create', compact('facility'));
    }
    
    /**
     * Store a new package.
     */
    public function storePackage(Request $request, Facility $facility)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration_hours' => 'required|integer|min:1',
            'inclusions' => 'required|string',
            'is_active' => 'boolean',
        ]);
        
        $package = $facility->packages()->create($validated);
        
        $this->coreService->log(auth()->id(), 'facility_package_created', 
            "Package {$package->name} created for facility {$facility->name}");
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Package created successfully.',
                'package' => $package
            ]);
        }
        
        return redirect()->route('facilities.packages', $facility)
            ->with('success', 'Package created successfully.');
    }
    
    /**
     * Edit a package.
     */
    public function editPackage(Request $request, Facility $facility, FacilityPackage $package)
    {
        if ($request->ajax()) {
            return response()->json([
                'facility' => $facility,
                'package' => $package
            ]);
        }
        
        return view('facilities.packages.edit', compact('facility', 'package'));
    }
    
    /**
     * Update a package.
     */
    public function updatePackage(Request $request, Facility $facility, FacilityPackage $package)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration_hours' => 'required|integer|min:1',
            'inclusions' => 'required|string',
            'is_active' => 'boolean',
        ]);
        
        $package->update($validated);
        
        $this->coreService->log(auth()->id(), 'facility_package_updated', 
            "Package {$package->name} updated for facility {$facility->name}");
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Package updated successfully.',
                'package' => $package->fresh()
            ]);
        }
        
        return redirect()->route('facilities.packages', $facility)
            ->with('success', 'Package updated successfully.');
    }
    
    /**
     * Delete a package.
     */
    public function destroyPackage(Request $request, Facility $facility, FacilityPackage $package)
    {
        // Check if package has any allocations
        $hasAllocations = $package->allocations()->exists();
        
        if ($hasAllocations) {
            $message = 'Cannot delete package that has existing allocations.';
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 400);
            }
            
            return redirect()->back()->with('error', $message);
        }
        
        $packageName = $package->name;
        $package->delete();
        
        $this->coreService->log(auth()->id(), 'facility_package_deleted', 
            "Package {$packageName} deleted from facility {$facility->name}");
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Package deleted successfully.'
            ]);
        }
        
        return redirect()->route('facilities.packages', $facility)
            ->with('success', 'Package deleted successfully.');
    }
    
    /**
     * Get facility calendar data.
     */
    public function calendar(Request $request, Facility $facility = null)
    {
        $query = FacilityAllocation::query();
        
        if ($facility) {
            $query->where('facility_id', $facility->id);
        }
        
        if ($request->has('start') && $request->has('end')) {
            $query->whereBetween('start_time', [$request->start, $request->end])
                ->orWhereBetween('end_time', [$request->start, $request->end]);
        }
        
        $allocations = $query->with(['facility', 'stay.guest'])
            ->whereIn('status', ['booked', 'confirmed', 'in_use'])
            ->get()
            ->map(function ($allocation) {
                return [
                    'id' => $allocation->id,
                    'title' => $allocation->facility->name . 
                              ($allocation->stay ? ' - ' . $allocation->stay->guest->name : ''),
                    'start' => $allocation->start_time->toIso8601String(),
                    'end' => $allocation->end_time->toIso8601String(),
                    'color' => $this->getAllocationColor($allocation->status),
                    'extendedProps' => [
                        'facility' => $allocation->facility->name,
                        'guest' => $allocation->stay ? $allocation->stay->guest->name : 'External Guest',
                        'status' => $allocation->status,
                        'duration' => $allocation->duration_hours . ' hours',
                        'amount' => 'KES ' . number_format($allocation->total_cost, 2)
                    ]
                ];
            });
        
        return response()->json($allocations);
    }
    
    /**
     * Get allocation status color.
     */
    private function getAllocationColor($status)
    {
        return match($status) {
            'booked' => '#f59e0b', // amber
            'confirmed' => '#10b981', // emerald
            'in_use' => '#3b82f6', // blue
            'completed' => '#6b7280', // gray
            'cancelled' => '#ef4444', // red
            default => '#9ca3af' // gray-400
        };
    }
    
    /**
     * Get facility statistics for dashboard.
     */
    public function dashboardStats(Request $request)
    {
        $period = $request->period ?? 'today';
        $date = Carbon::now();
        
        switch ($period) {
            case 'week':
                $startDate = $date->copy()->startOfWeek();
                $endDate = $date->copy()->endOfWeek();
                break;
            case 'month':
                $startDate = $date->copy()->startOfMonth();
                $endDate = $date->copy()->endOfMonth();
                break;
            case 'year':
                $startDate = $date->copy()->startOfYear();
                $endDate = $date->copy()->endOfYear();
                break;
            default: // today
                $startDate = $date->copy()->startOfDay();
                $endDate = $date->copy()->endOfDay();
        }
        
        $stats = [
            'total_bookings' => FacilityAllocation::whereBetween('created_at', [$startDate, $endDate])->count(),
            'confirmed_bookings' => FacilityAllocation::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'confirmed')->count(),
            'revenue' => FacilityAllocation::whereBetween('created_at', [$startDate, $endDate])
                ->sum(DB::raw('rate_applied * TIMESTAMPDIFF(HOUR, start_time, end_time)')),
            'most_popular_facility' => Facility::withCount(['allocations' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }])->orderBy('allocations_count', 'desc')->first(),
        ];
        
        return response()->json([
            'success' => true,
            'period' => $period,
            'stats' => $stats
        ]);
    }
}