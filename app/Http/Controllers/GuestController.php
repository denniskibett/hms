<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\User;
use App\Models\Stay;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomAllocation;
use App\Services\GuestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PragmaRX\Countries\Package\Countries;
use Illuminate\Support\Carbon;
use App\Helpers\SystemHelper;

class GuestController extends Controller
{
    protected $guestService;
    
    public function __construct(GuestService $guestService)
    {
        $this->guestService = $guestService;
    }

    public function index(Request $request)
    {
        $query = Guest::with(['user' => function($query) {
            $query->with(['stays' => function($stayQuery) {
                $stayQuery->whereIn('status', ['booked', 'checked_in'])
                        ->where('departure_date', '>=', now())
                        ->with('roomAllocations.room.roomType')
                        ->latest();
            }]);
        }]);
        
        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($userQuery) use ($search) {
                    $userQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%')
                            ->orWhere('phone', 'like', '%' . $search . '%');
                })
                ->orWhere('id_number', 'like', '%' . $search . '%')
                ->orWhere('nationality', 'like', '%' . $search . '%')
                ->orWhere('address', 'like', '%' . $search . '%');
            });
        }
        
        // Status filter - Fixed to show Active/Inactive guests
        if ($request->has('status') && !empty($request->status)) {
            if ($request->status === 'active') {
                $query->whereHas('user.stays', function($stayQuery) {
                    $stayQuery->whereIn('status', ['booked', 'checked_in'])
                            ->where('departure_date', '>=', now());
                });
            } elseif ($request->status === 'inactive') {
                $query->where(function($q) {
                    $q->whereDoesntHave('user.stays')
                    ->orWhereHas('user.stays', function($stayQuery) {
                        $stayQuery->where('status', 'checked_out')
                                ->orWhere('departure_date', '<', now());
                    });
                });
            }
        }
        
        // Nationality filter
        if ($request->has('nationality') && !empty($request->nationality)) {
            $query->where('nationality', 'like', '%' . $request->nationality . '%');
        }

        // Sort
        $sortBy = $request->get('sort_by', 'id');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        if (in_array($sortBy, ['name', 'email'])) {
            $query->join('users', 'guests.user_id', '=', 'users.id')
                ->orderBy("users.$sortBy", $sortDirection)
                ->select('guests.*');
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }
        
        // For AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            try {
                $perPage = $request->get('per_page', 10);
                $guests = $query->paginate($perPage);
                
                // Calculate stats - Updated to match Active/Inactive filter
                $totalGuests = Guest::count();
                $activeGuests = Guest::whereHas('user.stays', function($stayQuery) {
                    $stayQuery->whereIn('status', ['booked', 'checked_in'])
                            ->where('departure_date', '>=', now());
                })->count();
                $inactiveGuests = $totalGuests - $activeGuests;
                
                $today = now()->toDateString();
                $checkinsToday = \App\Models\Stay::whereDate('arrival_date', $today)->count();
                $checkoutsToday = \App\Models\Stay::whereDate('departure_date', $today)->count();
                
                // Transform data for frontend
                $transformedGuests = $guests->getCollection()->map(function($guest) {
                    $user = $guest->user;
                    
                    // Get current stay safely
                    $currentStay = null;
                    if ($user && $user->stays) {
                        $currentStay = $user->stays->first();
                    }
                    
                    // Parse preferences and emergency contact
                    $preferences = $guest->preferences;
                    $emergencyContact = $guest->emergency_contact;
                    
                    if (is_string($preferences)) {
                        try {
                            $preferences = json_decode($preferences, true);
                        } catch (\Exception $e) {
                            $preferences = [];
                        }
                    }
                    
                    if (is_string($emergencyContact)) {
                        try {
                            $emergencyContact = json_decode($emergencyContact, true);
                        } catch (\Exception $e) {
                            $emergencyContact = ['name' => $emergencyContact];
                        }
                    }
                    
                    // Determine guest status
                    $guestStatus = 'Inactive';
                    if ($currentStay) {
                        if ($currentStay->status === 'checked_in') {
                            $guestStatus = 'Active';
                        } elseif ($currentStay->status === 'booked') {
                            $guestStatus = 'Booked';
                        }
                    }
                    
                    return [
                        'id' => $guest->id,
                        'id_type' => $guest->id_type,
                        'id_number' => $guest->id_number,
                        'nationality' => $guest->nationality,
                        'address' => $guest->address,
                        'emergency_contact' => $emergencyContact,
                        'preferences' => $preferences,
                        'status' => $guestStatus, // Add status for sorting
                        'user' => $user ? [
                            'id' => $user->id,
                            'name' => $user->name,
                            'first_name' => explode(' ', $user->name)[0] ?? '',
                            'last_name' => explode(' ', $user->name)[1] ?? '',
                            'email' => $user->email,
                            'phone' => $user->phone,
                            'avatar' => $user->avatar,
                            'currentStay' => $currentStay ? [
                                'id' => $currentStay->id,
                                'status' => $currentStay->status,
                                'arrival_date' => $currentStay->arrival_date?->format(SystemHelper::dateFormat()),
                                'departure_date' => $currentStay->departure_date?->format(SystemHelper::dateFormat()),
                                'roomAllocations' => $currentStay->roomAllocations ? $currentStay->roomAllocations->map(function($allocation) {
                                    return [
                                        'room_id' => $allocation->room_id,
                                        'room' => $allocation->room ? [
                                            'id' => $allocation->room->id,
                                            'room_number' => $allocation->room->room_number,
                                            'type' => $allocation->room->roomType ? [
                                                'id' => $allocation->room->roomType->id,
                                                'name' => $allocation->room->roomType->name,
                                            ] : null
                                        ] : null
                                    ];
                                })->toArray() : []
                            ] : null,
                        ] : null,
                        'created_at' => $guest->created_at?->format(SystemHelper::dateFormat()),
                        'updated_at' => $guest->updated_at?->format(SystemHelper::dateFormat()),
                    ];
                });
                
                $guests->setCollection($transformedGuests);
                
                // Add stats to response
                $responseData = $guests->toArray();
                $responseData['stats'] = [
                    'totalGuests' => $totalGuests,
                    'activeGuests' => $activeGuests,
                    'inactiveGuests' => $inactiveGuests,
                    'checkinsToday' => $checkinsToday,
                    'checkoutsToday' => $checkoutsToday,
                ];
                
                return response()->json($responseData);
                
            } catch (\Exception $e) {
                Log::error('Error in guest index AJAX: ' . $e->getMessage());
                return response()->json([
                    'error' => 'Failed to load guests',
                    'message' => $e->getMessage()
                ], 500);
            }
        }
        
        // For initial page load
        $perPage = $request->get('per_page', 10);
        $guests = $query->paginate($perPage);
        
        // Calculate stats
        $totalGuests = Guest::count();
        $activeGuests = Guest::whereHas('user.stays', function($stayQuery) {
            $stayQuery->whereIn('status', ['booked', 'checked_in'])
                    ->where('departure_date', '>=', now());
        })->count();
        
        $today = now()->toDateString();
        $checkinsToday = \App\Models\Stay::whereDate('arrival_date', $today)->count();
        $checkoutsToday = \App\Models\Stay::whereDate('departure_date', $today)->count();
        
        // Get countries list
        $countries = $this->getCountriesList();
        
        return view('guests.index', compact('guests', 'totalGuests', 'activeGuests', 'checkinsToday', 'checkoutsToday', 'countries'));
    }

    public function store(Request $request)
    {
        Log::info('Guest creation request received', [
            'data' => $request->all(),
            'ajax' => $request->ajax(),
        ]);

        $validated = $request->validate([
            'user.first_name' => 'required|string|max:255',
            'user.last_name' => 'required|string|max:255',
            'user.email' => 'required|email|unique:users,email',
            'user.phone' => 'nullable|string|max:20',
            'guest_profile.id_type' => 'required|string',
            'guest_profile.id_number' => 'required|string|max:50',
            'guest_profile.nationality' => 'required|string|max:100',
            'guest_profile.address' => 'nullable|string',
            'guest_profile.emergency_contact' => 'nullable|array',
            'guest_profile.emergency_contact.name' => 'nullable|string|max:255',
            'guest_profile.emergency_contact.email' => 'nullable|email',
            'guest_profile.emergency_contact.phone' => 'nullable|string|max:20',
            'guest_profile.emergency_contact.relationship' => 'required|string|max:100',
            'guest_profile.emergency_contact.address' => 'nullable|string',
            'guest_profile.preferences' => 'nullable|array',
            'guest_profile.preferences.room_preference' => 'nullable|array',
            'guest_profile.preferences.room_preference.*' => 'nullable|string|max:255',
            'guest_profile.preferences.entertainment' => 'nullable|array',
            'guest_profile.preferences.entertainment.*' => 'nullable|string|max:255',
            'guest_profile.preferences.room_service' => 'nullable|array',
            'guest_profile.preferences.room_service.*' => 'nullable|string|max:255',
            'guest_profile.preferences.restaurant' => 'nullable|array',
            'guest_profile.preferences.restaurant.*' => 'nullable|string|max:255',
            'guest_profile.preferences.checkin_time' => 'nullable|array',
            'guest_profile.preferences.checkin_time.*' => 'nullable|string|max:255',
            'guest_profile.preferences.other' => 'nullable|string',
            'guest_profile.preferences.allergies' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();
            
            // Create user
            $user = User::create([
                'name' => $validated['user']['first_name'] . ' ' . $validated['user']['last_name'],
                'email' => $validated['user']['email'],
                'phone' => $validated['user']['phone'] ?? null,
                'password' => bcrypt('00000000'),
            ]);
            
            Log::info('User created', ['user_id' => $user->id]);
            
            // Assign guest role
            $guestRole = Role::where('name', 'guest')->first();
            if ($guestRole) {
                $user->roles()->attach($guestRole->id);
            }
            
            // Prepare guest data - FIX: Don't use array_filter on preferences as it removes empty strings
            $preferences = isset($validated['guest_profile']['preferences']) 
                ? $validated['guest_profile']['preferences'] 
                : [];
            
            // Ensure all preference fields exist even if empty
            $preferences = array_merge([
                'room_preference' => [],
                'entertainment' => [],
                'room_service' => [],
                'restaurant' => [],
                'checkin_time' => [],
                'other' => '',
                'allergies' => ''
            ], $preferences);
            
            $guestData = [
                'user_id' => $user->id,
                'id_type' => $validated['guest_profile']['id_type'],
                'id_number' => $validated['guest_profile']['id_number'],
                'nationality' => $validated['guest_profile']['nationality'],
                'address' => $validated['guest_profile']['address'] ?? null,
                'emergency_contact' => isset($validated['guest_profile']['emergency_contact']) 
                    ? json_encode($validated['guest_profile']['emergency_contact']) 
                    : null,
                'preferences' => json_encode($preferences),
            ];
            
            $guest = Guest::create($guestData);
            
            Log::info('Guest created', [
                'guest_id' => $guest->id,
                'user_id' => $guest->user_id,
                'preferences' => $guest->preferences
            ]);
            
            DB::commit();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Guest created successfully',
                    'guest_id' => $guest->id,
                    'redirect_url' => route('guests.show', $guest)
                ]);
            }
            
            return redirect()->route('guests.show', $guest)
                            ->with('success', 'Guest created successfully');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error creating guest', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating guest: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Error creating guest: ' . $e->getMessage())
                        ->withInput();
        }
    }

    public function update(Request $request, Guest $guest)
    {
        $validated = $request->validate([
            'user.first_name' => 'required|string|max:255',
            'user.last_name' => 'required|string|max:255',
            'user.email' => 'required|email|unique:users,email,' . $guest->user_id,
            'user.phone' => 'nullable|string|max:20',
            'guest_profile.id_type' => 'required|string',
            'guest_profile.id_number' => 'required|string|max:50',
            'guest_profile.nationality' => 'required|string|max:100',
            'guest_profile.address' => 'nullable|string',
            'guest_profile.emergency_contact' => 'nullable|array',
            'guest_profile.emergency_contact.name' => 'nullable|string|max:255',
            'guest_profile.emergency_contact.email' => 'nullable|email',
            'guest_profile.emergency_contact.phone' => 'nullable|string|max:20',
            'guest_profile.emergency_contact.relationship' => 'required|string|max:100',
            'guest_profile.emergency_contact.address' => 'nullable|string',
            'guest_profile.preferences' => 'nullable|array',
            'guest_profile.preferences.room_preference' => 'nullable|array',
            'guest_profile.preferences.room_preference.*' => 'nullable|string|max:255',
            'guest_profile.preferences.entertainment' => 'nullable|array',
            'guest_profile.preferences.entertainment.*' => 'nullable|string|max:255',
            'guest_profile.preferences.room_service' => 'nullable|array',
            'guest_profile.preferences.room_service.*' => 'nullable|string|max:255',
            'guest_profile.preferences.restaurant' => 'nullable|array',
            'guest_profile.preferences.restaurant.*' => 'nullable|string|max:255',
            'guest_profile.preferences.checkin_time' => 'nullable|array',
            'guest_profile.preferences.checkin_time.*' => 'nullable|string|max:255',
            'guest_profile.preferences.other' => 'nullable|string',
            'guest_profile.preferences.allergies' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();
            
            // Update user
            $guest->user->update([
                'name' => $validated['user']['first_name'] . ' ' . $validated['user']['last_name'],
                'email' => $validated['user']['email'],
                'phone' => $validated['user']['phone'] ?? null,
            ]);
            
            // Prepare preferences - FIX: Don't use array_filter on preferences
            $preferences = isset($validated['guest_profile']['preferences']) 
                ? $validated['guest_profile']['preferences'] 
                : [];
            
            // Ensure all preference fields exist even if empty
            $preferences = array_merge([
                'room_preference' => [],
                'entertainment' => [],
                'room_service' => [],
                'restaurant' => [],
                'checkin_time' => [],
                'other' => '',
                'allergies' => ''
            ], $preferences);
            
            // Update guest
            $guest->update([
                'id_type' => $validated['guest_profile']['id_type'],
                'id_number' => $validated['guest_profile']['id_number'],
                'nationality' => $validated['guest_profile']['nationality'],
                'address' => $validated['guest_profile']['address'] ?? null,
                'emergency_contact' => isset($validated['guest_profile']['emergency_contact']) 
                    ? json_encode($validated['guest_profile']['emergency_contact']) 
                    : null,
                'preferences' => json_encode($preferences),
            ]);
            
            DB::commit();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Guest updated successfully',
                ]);
            }
            
            return redirect()->route('guests.show', $guest->id)
                            ->with('success', 'Guest updated successfully');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating guest: ' . $e->getMessage(),
                ], 500);
            }
            
            return back()->with('error', 'Error updating guest: ' . $e->getMessage())
                        ->withInput();
        }
    }

public function show(Guest $guest)
{
    $roomAllocations = RoomAllocation::with('stay')
    ->whereHas('stay', function($query) {
        $query->whereNotIn('status', ['checked_out', 'cancelled']);
    })
    ->get()
    ->map(function($allocation) {
        return [
            'id' => $allocation->id,
            'room_id' => $allocation->room_id,
            'stay_id' => $allocation->stay_id,
            'from_date' => $allocation->from_date->toDateString(),
            'to_date' => $allocation->to_date->toDateString(),
        ];
    });

    // Load stays with room allocations and rooms
    $allStays = Stay::where('guest_id', $guest->user_id)
        ->with(['roomAllocations.room.roomType', 'invoices'])
        ->latest()
        ->get();

    // Transform stays for the view
    $stays = $allStays->map(function ($stay) {
        // Get all room allocations for this stay
        $roomAllocations = $stay->roomAllocations;
        
        // Calculate total nights
        $nights = 0;
        if ($stay->arrival_date && $stay->departure_date) {
            $arrival = Carbon::parse($stay->arrival_date);
            $departure = Carbon::parse($stay->departure_date);
            $nights = $arrival->diffInDays($departure);
        }
        
        return [
            'id' => $stay->id,
            'status' => $stay->status,
            'arrival_date' => $stay->arrival_date?->toDateString(), 
            'departure_date' => $stay->departure_date?->toDateString(),
            'check_in' => $stay->check_in?->format(SystemHelper::dateFormat()),
            'check_out' => $stay->check_out?->format(SystemHelper::dateFormat()),
            'adults' => $stay->adults,
            'children' => $stay->children,
            'nights' => $nights,
            'room_allocations' => $roomAllocations->map(function ($allocation) {
                return [
                    'id' => $allocation->id,
                    'room_id' => $allocation->room_id,
                    'adults' => $allocation->adults,
                    'children' => $allocation->children,
                    'guest_notes' => $allocation->guest_notes,
                    'rate_applied' => $allocation->rate_applied,
                    'room' => $allocation->room ? [
                        'id' => $allocation->room->id,
                        'room_number' => $allocation->room->room_number,
                        'room_type' => $allocation->room->roomType ? [
                            'name' => $allocation->room->roomType->name,
                            'base_rate' => $allocation->room->roomType->base_rate,
                            'capacity' => $allocation->room->roomType->capacity,
                        ] : null
                    ] : null
                ];
            })->toArray(),
            'created_at' => $stay->created_at?->format(SystemHelper::dateFormat()),
        ];
    });

    // Get available rooms
    $allRooms = Room::with(['roomType'])
        ->where('status', 'available')
        ->orderBy('room_number')
        ->get()
        ->map(function ($room) {
            return [
                'id' => $room->id,
                'room_number' => $room->room_number,
                'room_type' => [
                    'id' => $room->roomType->id,
                    'name' => $room->roomType->name ?? 'N/A',
                    'base_rate' => $room->roomType->base_rate ?? 0,
                    'capacity' => $room->roomType->capacity ?? 1,
                    'bed_type' => $room->roomType->bed_type ?? 'double',
                    'amenities' => $room->roomType->amenities ?? [],
                ],
                'floor' => $room->floor,
                'wing' => $room->wing,
                'features' => $room->features ?? [],
            ];
        });

    // Calculate total nights
    $totalNights = $allStays->sum(function ($stay) {
        if ($stay->arrival_date && $stay->departure_date) {
            return Carbon::parse($stay->arrival_date)
                ->diffInDays(Carbon::parse($stay->departure_date));
        }
        return 0;
    });

    return view('guests.show', [
        'guest'    => $guest,
        'stays'    => $stays,
        'allRooms' => $allRooms,
        'allStays' => $allStays,
        'totalNights' => $totalNights,    
        'roomAllocations' => $roomAllocations, 
    ]);
}
    
    public function destroy(Guest $guest)
    {
        try {
            DB::beginTransaction();
            
            // Delete associated stays first
            if ($guest->user) {
                $guest->user->stays()->delete();
            }
            
            // Delete guest profile
            $guest->delete();
            
            DB::commit();
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Guest deleted successfully',
                ]);
            }
            
            return redirect()->route('guests.index')
                             ->with('success', 'Guest deleted successfully');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting guest: ' . $e->getMessage(),
                ], 500);
            }
            
            return back()->with('error', 'Error deleting guest: ' . $e->getMessage());
        }
    }

    public function createStay(Request $request, Guest $guest)
    {
        try {
            $validated = $request->validate([
                'arrival_date' => 'required|date|after_or_equal:today',
                'departure_date' => 'required|date|after:arrival_date',
                'room_allocations' => 'required|array|min:1',
                'room_allocations.*.room_id' => 'required|exists:rooms,id',
                'room_allocations.*.adults' => 'required|integer|min:1|max:4',
                'room_allocations.*.children' => 'integer|min:0|max:4',
                'room_allocations.*.rate' => 'nullable|numeric|min:0',
                'room_allocations.*.guest_notes' => 'nullable|string|max:255',
                'special_requests' => 'nullable|string',
                'status' => 'required|in:booked,checked_in',
            ]);

            // Get the guest's user
            $guestUser = $guest->user;
            
            // Prepare stay data
            $stayData = [
                'arrival_date' => $validated['arrival_date'],
                'departure_date' => $validated['departure_date'],
                'adults' => collect($validated['room_allocations'])->sum('adults'),
                'children' => collect($validated['room_allocations'])->sum('children'),
                'special_requests' => $validated['special_requests'] ?? null,
                'status' => $validated['status'],
                'room_allocations' => $validated['room_allocations'],
            ];
            
            // Use enhanced GuestService to create stay with multiple rooms
            $result = $this->guestService->createStayWithMultipleRooms($guestUser, $stayData);
            
            return response()->json([
                'success' => true,
                'message' => 'Stay created successfully with ' . count($result['room_allocations']) . ' rooms',
                'stay' => [
                    'id' => $result['stay']->id,
                    'status' => $result['stay']->status,
                    'total_amount' => $result['total_amount'],
                    'room_count' => count($result['room_allocations']),
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error creating stay: ' . $e->getMessage(), [
                'guest_id' => $guest->id,
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error creating stay: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkinStay(Request $request, Guest $guest, $stayId)
    {
        try {
            $stay = Stay::findOrFail($stayId);
            
            // Verify the stay belongs to this guest
            if ($stay->guest_id !== $guest->user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stay does not belong to this guest'
                ], 403);
            }
            
            if ($stay->status !== 'booked') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only booked stays can be checked in'
                ], 400);
            }
            
            // Use GuestService to check in
            $stay = $this->guestService->checkIn($stay);
            
            return response()->json([
                'success' => true,
                'message' => 'Guest checked in successfully',
                'stay' => [
                    'id' => $stay->id,
                    'status' => $stay->status,
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error checking in stay: ' . $e->getMessage(), [
                'guest_id' => $guest->id,
                'stay_id' => $stayId
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error checking in: ' . $e->getMessage()
            ], 500);
        }
    }
    

    public function checkoutStay(Request $request, Guest $guest, $stayId)
    {
        try {
            $stay = Stay::findOrFail($stayId);
            
            // Verify the stay belongs to this guest
            if ($stay->guest_id !== $guest->user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stay does not belong to this guest'
                ], 403);
            }
            
            if ($stay->status !== 'checked_in') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only checked-in stays can be checked out'
                ], 400);
            }
            
            // Use GuestService to check out
            $result = $this->guestService->checkOut($stay, [
                'notes' => $request->input('notes', 'Checked out via guest profile')
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Guest checked out successfully',
                'stay' => [
                    'id' => $stay->id,
                    'status' => $stay->status,
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error checking out stay: ' . $e->getMessage(), [
                'guest_id' => $guest->id,
                'stay_id' => $stayId
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error checking out: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getCountriesList()
    {
        try {
            $countries = new Countries();
            
            $countriesList = $countries->all()
                ->map(function ($country) {
                    return [
                        'name' => $country->name->common ?? '',
                        'official_name' => $country->name->official ?? '',
                        'cca2' => $country->cca2 ?? '',
                        'flag_emoji' => $country->flag->emoji ?? ''
                    ];
                })
                ->sortBy('name')
                ->values()
                ->toArray();
            
            return $countriesList;
            
        } catch (\Exception $e) {
            Log::error('Error loading countries: ' . $e->getMessage());
            
            return [
                ['name' => 'United States', 'cca2' => 'US', 'flag_emoji' => '🇺🇸'],
                ['name' => 'United Kingdom', 'cca2' => 'GB', 'flag_emoji' => '🇬🇧'],
                ['name' => 'Canada', 'cca2' => 'CA', 'flag_emoji' => '🇨🇦'],
                ['name' => 'Australia', 'cca2' => 'AU', 'flag_emoji' => '🇦🇺'],
                ['name' => 'Germany', 'cca2' => 'DE', 'flag_emoji' => '🇩🇪'],
                ['name' => 'France', 'cca2' => 'FR', 'flag_emoji' => '🇫🇷'],
                ['name' => 'Japan', 'cca2' => 'JP', 'flag_emoji' => '🇯🇵'],
                ['name' => 'China', 'cca2' => 'CN', 'flag_emoji' => '🇨🇳'],
            ];
        }
    }

    public static function getIdTypeOptions()
    {
        return [
            'passport' => 'Passport',
            'national_id' => 'National ID',
            'driving_license' => 'Driving License',
            'other' => 'Other'
        ];
    }
}