<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\User;
use App\Models\Stay;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomAllocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PragmaRX\Countries\Package\Countries;
use Illuminate\Support\Carbon;
use App\Helpers\SystemHelper;
use App\Enums\StayStatus;

class GuestController extends Controller
{

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
        $allStays = Stay::where('guest_id', $guest->user_id)
            ->with(['guest', 'roomAllocations.room.roomType'])
            ->orderByDesc('arrival_date')
            ->get();

        $totalNights = $allStays
            ->where('status', '!=', StayStatus::Cancelled->value)
            ->sum(fn (Stay $stay) => $stay->nights);

        return view('guests.show', [
            'guest' => $guest,
            'allStays' => $allStays,
            'totalNights' => $totalNights,
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