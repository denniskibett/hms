<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreStayRequest;
use App\Models\Stay;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Guest;
use App\Models\Payment;
use App\Models\RoomAllocation;
use App\Services\GuestService;
use App\Services\FinanceService;
use DB;

class StayController extends Controller
{
    public function __construct(
        private GuestService $guestService,
        private FinanceService $financeService
    ) {}
    
    public function index(Request $request)
    {
        $this->authorize('viewAny', Stay::class);

        $query = Stay::with([
            'guest', 
            'roomAllocations.room.roomType', 
            'invoices'
        ]);

        // Apply filters
        if ($request->has('status')) {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->whereIn('status', ['reserved', 'booked', 'checked_in']);
            } elseif ($status === 'upcoming') {
                $query->where('status', 'reserved')
                    ->where('arrival_date', '>', now());
            } elseif ($status === 'completed') {
                $query->where('status', 'checked_out');
            } elseif ($status === 'cancelled') {
                $query->where('status', 'cancelled');
            } else {
                $query->where('status', $status);
            }
        }

        if ($request->has('guest_id')) {
            $query->where('guest_id', $request->input('guest_id'));
        }

        if ($request->has('date_from')) {
            $query->where('arrival_date', '>=', $request->input('date_from'));
        }

        if ($request->has('date_to')) {
            $query->where('departure_date', '<=', $request->input('date_to'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('guest', function ($q2) use ($search) {
                    $q2->where('name', 'like', '%' . $search . '%')
                       ->orWhere('email', 'like', '%' . $search . '%');
                })
                ->orWhereHas('roomAllocations.room', function ($q2) use ($search) {
                    $q2->where('room_number', 'like', '%' . $search . '%');
                });
            });
        }

        if ($request->has('room_type')) {
            $roomType = $request->input('room_type');
            $query->whereHas('roomAllocations.room.roomType', function ($q) use ($roomType) {
                $q->where('name', 'like', '%' . $roomType . '%');
            });
        }

        $stays = $query->latest()->get(); // Changed from paginate() to get() for Alpine.js

        // Get all rooms for the dropdown
        $allRooms = Room::with('roomType')->get();
        
        // Get all room allocations for availability checking
        $roomAllocations = RoomAllocation::with(['room', 'stay'])
            ->whereDate('to_date', '>=', now())
            ->get();

        // Fetch statistics
        $occupiedRooms = Room::where('status', 'occupied')->count();
        $activeStays = Stay::whereIn('status', ['reserved', 'booked', 'checked_in'])->count();
        $upcomingStays = Stay::where('status', 'reserved')->where('arrival_date', '>', now())->count();
        $checkinsToday = Stay::todayCheckins()->count();
        $checkoutsToday = Stay::todayCheckouts()->count();

        // Fetch all room types for filter dropdown
        $roomTypes = RoomType::pluck('name')->toArray();

        // Fetch today's check-ins
        $todaysCheckins = Stay::with(['guest', 'roomAllocations.room.roomType'])
            ->whereDate('arrival_date', today())
            ->where('status', 'booked')
            ->get();

        // Fetch today's check-outs
        $todaysCheckouts = Stay::with(['guest', 'roomAllocations.room.roomType'])
            ->whereDate('departure_date', today())
            ->where('status', 'checked_in')
            ->get();

        return view('stays.index', compact(
            'stays',          // Pass stays as collection (not paginated)
            'allRooms',       // All rooms for dropdown
            'roomAllocations', // Room allocations for availability check
            'roomTypes',      // Room types for filter
            'todaysCheckins',
            'todaysCheckouts',
            'occupiedRooms',
            'activeStays',
            'upcomingStays',
            'checkinsToday',
            'checkoutsToday'
        ));
    }
    public function create(Request $request)
    {
        $this->authorize('create', Stay::class);
        
        $guestId = $request->input('guest_id');
        $guest = $guestId ? \App\Models\User::find($guestId) : null;
        
        $roomTypes = RoomType::with(['rooms' => function ($q) {
            $q->where('status', 'available');
        }])->get();
        
        return view('stays.create', compact('guest', 'roomTypes'));
    }

    public function store(StoreStayRequest $request)
    {
        $this->authorize('create', Stay::class);
        
        try {
            $guest = \App\Models\User::findOrFail($request->input('guest_id'));
            
            // Prepare stay data
            $stayData = [
                'arrival_date' => $request->input('arrival_date'),
                'departure_date' => $request->input('departure_date'),
                'adults' => $request->input('adults', 1),
                'children' => $request->input('children', 0),
                'special_requests' => $request->input('special_requests'),
                'status' => $request->input('status', 'reserved'),
            ];
            
            // Create stay
            $stay = $this->guestService->createStay($guest, $stayData);
            
            // Assign room if provided
            if ($request->has('room_id')) {
                $roomData = [
                    'room_id' => $request->input('room_id'),
                    'rate' => $request->input('rate'),
                    'adults' => $request->input('room_adults', $stayData['adults']),
                    'children' => $request->input('room_children', $stayData['children']),
                    'guest_notes' => $request->input('guest_notes'),
                ];
                
                $this->guestService->assignRoom($stay, $roomData);
            }
            
            // Create initial invoice if not draft
            if ($stay->status !== 'reserved') {
                $this->financeService->createStayInvoice($stay);
            }
            
            return redirect()->route('stays.show', $stay)
                ->with('success', 'Stay created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating stay: ' . $e->getMessage());
        }
    }
    

    public function show(Stay $stay)
    {
        $this->authorize('view', $stay);
        
        $stay->load([
            'guest.guest', // Load guest and their guest profile
            'createdByUser',
            'roomAllocations.room.roomType',
            'invoices.payments' // Load invoices with their payments
        ]);
        
        // Get all payments for this stay (through invoices)
        $payments = Payment::whereIn('invoice_id', $stay->invoices->pluck('id'))->get();
        
        // Calculate stats using the computed attributes
        $roomCharges = $stay->room_charges;
        $additionalCharges = $stay->additional_charges;
        $totalAmount = $stay->total_amount;
        $amountPaid = $stay->amount_paid;
        $balanceDue = $totalAmount - $amountPaid;
        
        return view('stays.show', compact(
            'stay',
            'payments',
            'roomCharges',
            'additionalCharges',
            'totalAmount',
            'amountPaid',
            'balanceDue'
        ));
    }
    
    public function edit(Stay $stay)
    {
        $this->authorize('update', $stay);
        
        $stay->load([
            'guest.guest',
            'roomAllocations.room.roomType'
        ]);
        
        // Get available rooms for room allocation
        $availableRooms = Room::with('roomType')
            ->where('status', 'available')
            ->get()
            ->map(function ($room) {
                return [
                    'id' => $room->id,
                    'room_number' => $room->room_number,
                    'room_type' => $room->roomType ? [
                        'id' => $room->roomType->id,
                        'name' => $room->roomType->name,
                        'base_rate' => $room->roomType->base_rate,
                    ] : null,
                ];
            });
        
        // Get all guests for dropdown
        $guests = \App\Models\User::whereHas('roles', function($q) {
            $q->where('name', 'guest');
        })->with('guest')->get();
        
        // For AJAX requests, return JSON
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'stay' => [
                    'id' => $stay->id,
                    'guest_id' => $stay->guest_id,
                    'guest_name' => $stay->guest->name,
                    'status' => $stay->status,
                    'arrival_date' => $stay->arrival_date?->format('Y-m-d'),
                    'departure_date' => $stay->departure_date?->format('Y-m-d'),
                    'adults' => $stay->adults,
                    'children' => $stay->children,
                    'special_requests' => $stay->special_requests,
                    'room_allocations' => $stay->roomAllocations->map(function ($allocation) {
                        return [
                            'id' => $allocation->id,
                            'room_id' => $allocation->room_id,
                            'room_number' => $allocation->room->room_number,
                            'room_type' => $allocation->room->roomType->name,
                            'rate_applied' => $allocation->rate_applied,
                            'adults' => $allocation->adults,
                            'children' => $allocation->children,
                            'guest_notes' => $allocation->guest_notes,
                            'from_date' => $allocation->from_date?->format('Y-m-d'),
                            'to_date' => $allocation->to_date?->format('Y-m-d'),
                        ];
                    }),
                ],
                'availableRooms' => $availableRooms,
                'guests' => $guests->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                    ];
                }),
            ]);
        }
        
        return view('stays.edit', compact('stay', 'availableRooms'));
    }
    
    public function update(Request $request, Stay $stay)
    {
        $this->authorize('update', $stay);
        
        $validated = $request->validate([
            'guest_id' => 'required|exists:users,id',
            'status' => 'required|in:reserved,booked,checked_in,checked_out,cancelled',
            'arrival_date' => 'required|date',
            'departure_date' => 'required|date|after:arrival_date',
            'adults' => 'required|integer|min:1',
            'children' => 'integer|min:0',
            'special_requests' => 'nullable|string',
            'room_allocations' => 'nullable|array',
            'room_allocations.*.id' => 'nullable|exists:room_allocations,id',
            'room_allocations.*.room_id' => 'required|exists:rooms,id',
            'room_allocations.*.rate_applied' => 'required|numeric|min:0',
            'room_allocations.*.adults' => 'required|integer|min:1',
            'room_allocations.*.children' => 'integer|min:0',
            'room_allocations.*.guest_notes' => 'nullable|string|max:255',
            'room_allocations.*.from_date' => 'required|date',
            'room_allocations.*.to_date' => 'required|date|after:from_date',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Update stay
            $stay->update([
                'guest_id' => $validated['guest_id'],
                'status' => $validated['status'],
                'arrival_date' => $validated['arrival_date'],
                'departure_date' => $validated['departure_date'],
                'adults' => $validated['adults'],
                'children' => $validated['children'] ?? 0,
                'special_requests' => $validated['special_requests'] ?? null,
            ]);
            
            // Handle room allocations if provided
            if (isset($validated['room_allocations'])) {
                $this->updateRoomAllocations($stay, $validated['room_allocations']);
            }
            
            DB::commit();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Stay updated successfully',
                    'redirect' => route('stays.show', $stay)
                ]);
            }
            
            return redirect()->route('stays.show', $stay)
                ->with('success', 'Stay updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating stay: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating stay: ' . $e->getMessage());
        }
    }

    private function updateRoomAllocations(Stay $stay, array $roomAllocations)
    {
        $existingAllocations = $stay->roomAllocations->keyBy('id');
        
        foreach ($roomAllocations as $allocationData) {
            if (isset($allocationData['id']) && $existingAllocations->has($allocationData['id'])) {
                // Update existing allocation
                $allocation = $existingAllocations[$allocationData['id']];
                $allocation->update([
                    'room_id' => $allocationData['room_id'],
                    'rate_applied' => $allocationData['rate_applied'],
                    'adults' => $allocationData['adults'],
                    'children' => $allocationData['children'] ?? 0,
                    'guest_notes' => $allocationData['guest_notes'] ?? null,
                    'from_date' => $allocationData['from_date'],
                    'to_date' => $allocationData['to_date'],
                ]);
            } else {
                // Create new allocation
                RoomAllocation::create([
                    'stay_id' => $stay->id,
                    'room_id' => $allocationData['room_id'],
                    'rate_applied' => $allocationData['rate_applied'],
                    'adults' => $allocationData['adults'],
                    'children' => $allocationData['children'] ?? 0,
                    'guest_notes' => $allocationData['guest_notes'] ?? null,
                    'from_date' => $allocationData['from_date'],
                    'to_date' => $allocationData['to_date'],
                ]);
            }
        }
        
        // Delete allocations that were removed
        $updatedAllocationIds = collect($roomAllocations)->pluck('id')->filter();
        $allocationsToDelete = $existingAllocations->keys()->diff($updatedAllocationIds);
        
        if ($allocationsToDelete->count() > 0) {
            RoomAllocation::whereIn('id', $allocationsToDelete)->delete();
        }
    }
    
    public function destroy(Stay $stay)
    {
        $this->authorize('delete', $stay);
        
        try {
            $stay->delete();
            
            return redirect()->route('stays.index')
                ->with('success', 'Stay deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error deleting stay: ' . $e->getMessage());
        }
    }
    
    public function checkin(Stay $stay)
    {
        $this->authorize('update', $stay);
        
        try {
            $this->guestService->checkIn($stay);
            
            return redirect()->back()
                ->with('success', 'Guest checked in successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error checking in: ' . $e->getMessage());
        }
    }
    
    public function checkout(Stay $stay)
    {
        $this->authorize('update', $stay);
        
        try {
            $this->guestService->checkOut($stay);
            
            return redirect()->back()
                ->with('success', 'Guest checked out successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error checking out: ' . $e->getMessage());
        }
    }

    public function cancel(Stay $stay, Request $request)
    {
        $this->authorize('cancel', $stay);
        
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);
        
        try {
            $stay->update([
                'status' => 'cancelled',
                'special_requests' => $stay->special_requests . "\nCancelled: " . $request->input('reason'),
            ]);
            
            return redirect()->route('stays.show', $stay)
                ->with('success', 'Stay cancelled successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error cancelling stay: ' . $e->getMessage());
        }
    }

    public function todayArrivals()
    {
        $this->authorize('viewAny', Stay::class);
        
        $arrivals = Stay::with(['guest', 'roomAllocations.room.roomType'])
            ->where('status', 'booked')
            ->whereDate('arrival_date', today())
            ->orderBy('arrival_date')
            ->get();
        
        return view('stays.today-arrivals', compact('arrivals'));
    }

    public function todayDepartures()
    {
        $this->authorize('viewAny', Stay::class);
        
        $departures = Stay::with(['guest', 'roomAllocations.room.roomType'])
            ->where('status', 'checked_in')
            ->whereDate('departure_date', today())
            ->orderBy('departure_date')
            ->get();
        
        return view('stays.today-departures', compact('departures'));
    }
}