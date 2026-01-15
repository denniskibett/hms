<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomAllocation;
use App\Models\RoomType;
use App\Services\GuestService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RoomController extends Controller
{
    protected $guestService;
    
    public function __construct(GuestService $guestService)
    {
        $this->guestService = $guestService;
    }

    public function index(Request $request)
    {
        $query = Room::with(['roomType', 'currentStay']);
        
        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('floor')) {
            $query->where('floor', $request->floor);
        }
        
        if ($request->has('wing')) {
            $query->where('wing', 'like', '%' . $request->wing . '%');
        }
        
        $rooms = $query->orderBy('floor')->orderBy('room_number')->paginate(20);
        $roomTypes = RoomType::orderBy('name')->get();
        
        return view('rooms.index', compact('rooms', 'roomTypes'));
    }

    public function show(Room $room)
    {
        // Load relationships without trying to access user through guest
        $room->load([
            'roomType',
            'currentStay',
            'allocations' => function($query) {
                $query->with('stay')->latest()->take(10);
            }
        ]);
        
        $roomTypes = RoomType::orderBy('name')->get();
        
        return view('rooms.show', compact('room', 'roomTypes'));
    }
    
    public function checkAvailability(Request $request)
    {
        $request->validate([
            'arrival_date' => 'required|date',
            'departure_date' => 'required|date|after:arrival_date',
            'guest_id' => 'nullable|exists:users,id'
        ]);
        
        $arrivalDate = Carbon::parse($request->arrival_date);
        $departureDate = Carbon::parse($request->departure_date);
        
        // Get all rooms
        $rooms = Room::with('roomType')
            ->where('status', 'available')
            ->orderBy('room_number')
            ->get();
            
        $availableCount = 0;
        
        // Check availability for each room
        $rooms = $rooms->map(function ($room) use ($arrivalDate, $departureDate, &$availableCount) {
            $isAvailable = $this->guestService->isRoomAvailable($room->id, $arrivalDate, $departureDate);
            
            if ($isAvailable) {
                $availableCount++;
                $nextAvailableDate = null;
            } else {
                // Find next available date
                $nextAllocation = RoomAllocation::where('room_id', $room->id)
                    ->where('to_date', '>=', $arrivalDate)
                    ->orderBy('to_date', 'asc')
                    ->first();
                    
                $nextAvailableDate = $nextAllocation ? 
                    Carbon::parse($nextAllocation->to_date)->addDay()->toDateString() : 
                    $arrivalDate->toDateString();
            }
            
            return [
                'id' => $room->id,
                'room_number' => $room->room_number,
                'room_type' => [
                    'id' => $room->roomType->id,
                    'name' => $room->roomType->name ?? 'N/A',
                    'base_rate' => $room->roomType->base_rate ?? 0,
                    'capacity' => $room->roomType->capacity ?? 1,
                    'bed_type' => $room->roomType->bed_type ?? 'double',
                ],
                'floor' => $room->floor,
                'wing' => $room->wing,
                'is_available' => $isAvailable,
                'next_available_date' => $nextAvailableDate,
            ];
        });
        
        return response()->json([
            'success' => true,
            'rooms' => $rooms,
            'available_count' => $availableCount,
            'arrival_date' => $arrivalDate->toDateString(),
            'departure_date' => $departureDate->toDateString(),
            'nights' => $arrivalDate->diffInDays($departureDate)
        ]);
    }
    
    // Add these methods for AJAX operations
    
    public function update(Request $request, Room $room)
    {
        $request->validate([
            'room_number' => 'required|unique:rooms,room_number,' . $room->id,
            'room_type_id' => 'required|exists:room_types,id',
            'floor' => 'required|integer|min:1',
            'status' => 'required|in:available,occupied,cleaning,maintenance,out_of_order,reserved',
            'adult_price' => 'nullable|numeric|min:0',
            'child_price' => 'nullable|numeric|min:0',
        ]);
        
        $room->update([
            'room_number' => $request->room_number,
            'room_type_id' => $request->room_type_id,
            'status' => $request->status,
            'floor' => $request->floor,
            'wing' => $request->wing,
            'adult_price' => $request->adult_price,
            'child_price' => $request->child_price,
            'features' => $request->features ? json_decode($request->features, true) : [],
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Room updated successfully.'
        ]);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'room_number' => 'required|unique:rooms,room_number',
            'room_type_id' => 'required|exists:room_types,id',
            'floor' => 'required|integer|min:1',
            'status' => 'required|in:available,occupied,cleaning,maintenance,out_of_order,reserved',
            'adult_price' => 'nullable|numeric|min:0',
            'child_price' => 'nullable|numeric|min:0',
        ]);
        
        $room = Room::create([
            'room_number' => $request->room_number,
            'room_type_id' => $request->room_type_id,
            'status' => $request->status,
            'floor' => $request->floor,
            'wing' => $request->wing,
            'adult_price' => $request->adult_price,
            'child_price' => $request->child_price,
            'features' => $request->features ? json_decode($request->features, true) : [],
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Room created successfully.',
            'room_id' => $room->id
        ]);
    }
    
    public function updateStatus(Request $request, Room $room)
    {
        $request->validate([
            'status' => 'required|in:available,occupied,cleaning,maintenance,out_of_order,reserved'
        ]);
        
        $room->update(['status' => $request->status]);
        
        return response()->json([
            'success' => true,
            'message' => 'Room status updated successfully.'
        ]);
    }
}