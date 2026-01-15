<?php

namespace App\Http\Controllers;

use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoomTypeController extends Controller
{
    /**
     * Display a listing of room types.
     */
    public function index(Request $request)
    {
        $query = RoomType::withCount(['rooms', 'availableRooms']);
        
        // Apply filters
        if ($request->has('bed_type')) {
            $query->where('bed_type', $request->bed_type);
        }
        
        if ($request->has('min_capacity')) {
            $query->where('capacity', '>=', $request->min_capacity);
        }
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('code', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }
        
        $roomTypes = $query->orderBy('name')->paginate(20);
        $bedTypes = RoomType::getBedTypeOptions();
        
        return view('room-types.index', compact('roomTypes', 'bedTypes'));
    }

    /**
     * Show the form for creating a new room type.
     */
    public function create()
    {
        $bedTypes = RoomType::getBedTypeOptions();
        return view('room-types.create', compact('bedTypes'));
    }

    /**
     * Store a newly created room type in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:room_types,code',
            'base_rate' => 'required|numeric|min:0',
            'capacity' => 'required|integer|min:1',
            'bed_type' => ['required', Rule::in(array_keys(RoomType::getBedTypeOptions()))],
            'description' => 'nullable|string',
            'amenities' => 'nullable|array',
        ]);
        
        $roomType = RoomType::create([
            'name' => $request->name,
            'code' => $request->code,
            'base_rate' => $request->base_rate,
            'capacity' => $request->capacity,
            'bed_type' => $request->bed_type,
            'description' => $request->description,
            'amenities' => $request->amenities ?? [],
        ]);
        
        return redirect()->route('room-types.show', $roomType)
            ->with('success', 'Room type created successfully.');
    }

    /**
     * Display the specified room type.
     */
    public function show(RoomType $roomType)
    {
        $roomType->load(['rooms' => function($query) {
            $query->orderBy('room_number');
        }, 'services']);
        
        $bedTypes = RoomType::getBedTypeOptions();
        
        return view('room-types.show', compact('roomType', 'bedTypes'));
    }

    /**
     * Show the form for editing the specified room type.
     */
    public function edit(RoomType $roomType)
    {
        $bedTypes = RoomType::getBedTypeOptions();
        return view('room-types.edit', compact('roomType', 'bedTypes'));
    }

    /**
     * Update the specified room type in storage.
     */
    public function update(Request $request, RoomType $roomType)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:room_types,code,' . $roomType->id,
            'base_rate' => 'required|numeric|min:0',
            'capacity' => 'required|integer|min:1',
            'bed_type' => ['required', Rule::in(array_keys(RoomType::getBedTypeOptions()))],
            'description' => 'nullable|string',
            'amenities' => 'nullable|array',
        ]);
        
        $roomType->update([
            'name' => $request->name,
            'code' => $request->code,
            'base_rate' => $request->base_rate,
            'capacity' => $request->capacity,
            'bed_type' => $request->bed_type,
            'description' => $request->description,
            'amenities' => $request->amenities ?? [],
        ]);
        
        return redirect()->route('room-types.show', $roomType)
            ->with('success', 'Room type updated successfully.');
    }

    /**
     * Remove the specified room type from storage.
     */
    public function destroy(RoomType $roomType)
    {
        if ($roomType->rooms()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete room type that has rooms assigned to it.');
        }
        
        $roomType->delete();
        
        return redirect()->route('room-types.index')
            ->with('success', 'Room type deleted successfully.');
    }
    
    /**
     * API endpoint to get room types for dropdown.
     */
    public function apiIndex(Request $request)
    {
        $roomTypes = RoomType::orderBy('name')->get();
        
        return response()->json([
            'success' => true,
            'roomTypes' => $roomTypes
        ]);
    }
}