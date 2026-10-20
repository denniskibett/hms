<?php

namespace App\Http\Controllers;

use App\Enums\StayStatus;
use App\Helpers\SystemHelper;
use App\Models\Guest;
use App\Models\Role;
use App\Models\Stay;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GuestController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['in_house', 'expected'])],
            'nationality' => ['nullable', 'string', 'max:100'],
        ]);

        $guests = Guest::with([
                'user',
                'stays' => fn ($q) => $q->whereIn('status', StayStatus::activeValues())
                    ->with('roomAllocations.room')
                    ->orderBy('arrival_date'),
            ])
            ->withCount('stays')
            ->when($filters['search'] ?? null, fn ($q, $term) => $q->search($term))
            ->when(($filters['status'] ?? null) === 'in_house', fn ($q) => $q->inHouse())
            ->when(($filters['status'] ?? null) === 'expected', fn ($q) => $q->expected())
            ->when($filters['nationality'] ?? null, fn ($q, $nationality) => $q->where('nationality', $nationality))
            ->latest('id')
            ->paginate(SystemHelper::paginationLimit())
            ->withQueryString();

        $stats = [
            'total' => Guest::count(),
            'in_house' => Guest::inHouse()->count(),
            'arriving_today' => Stay::arrivingToday()->distinct()->count('guest_id'),
            'new_this_month' => Guest::where('created_at', '>=', now()->startOfMonth())->count(),
        ];

        $nationalities = Guest::whereNotNull('nationality')->distinct()->orderBy('nationality')->pluck('nationality');

        return view('guests.index', compact('guests', 'stats', 'filters', 'nationalities'));
    }

    public function show(Guest $guest): View
    {
        $guest->load('user');

        $stays = $guest->stays()
            ->with(['guest', 'roomAllocations.room', 'invoices'])
            ->orderByDesc('arrival_date')
            ->get();

        $billable = $stays->where('status', '!=', StayStatus::Cancelled->value);

        $totals = [
            'stays' => $billable->count(),
            'nights' => $billable->sum(fn (Stay $stay) => $stay->nights),
            'billed' => $billable->sum(fn (Stay $stay) => (float) $stay->invoices->sum('total')),
            'paid' => $billable->sum(fn (Stay $stay) => $stay->amount_paid),
        ];
        $totals['balance'] = round($totals['billed'] - $totals['paid'], 2);

        return view('guests.show', compact('guest', 'stays', 'totals'));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $guest = DB::transaction(function () use ($data) {
            // Guests don't log in from the front desk; give them an unusable password
            $user = User::create([
                ...$data['user'],
                'password' => Hash::make(Str::random(40)),
            ]);
            $user->roles()->attach(Role::firstOrCreate(['name' => 'guest'])->id);

            return $user->guestProfile()->create($data['profile']);
        });

        return response()->json([
            'success' => true,
            'message' => "{$guest->name} registered.",
            'guest' => $this->summary($guest),
        ], 201);
    }

    public function update(Request $request, Guest $guest): JsonResponse
    {
        $data = $this->validated($request, $guest);

        DB::transaction(function () use ($guest, $data) {
            $guest->user->update($data['user']);
            $guest->update($data['profile']);
        });

        return response()->json([
            'success' => true,
            'message' => "{$guest->name} updated.",
            'guest' => $this->summary($guest),
        ]);
    }

    public function destroy(Guest $guest): JsonResponse
    {
        // Bookings and invoices point at the guest; keep that history intact
        if ($guest->stays()->exists()) {
            return response()->json([
                'success' => false,
                'message' => "{$guest->name} has bookings and can't be deleted.",
            ], 422);
        }

        $name = $guest->name;
        $user = $guest->user;

        DB::transaction(function () use ($guest, $user) {
            $guest->delete();

            // A staff member with a guest profile keeps their account
            $user->roles()->detach(Role::where('name', 'guest')->value('id'));
            if ($user->roles()->doesntExist()) {
                $user->delete();
            }
        });

        return response()->json([
            'success' => true,
            'message' => "{$name} deleted.",
        ]);
    }

    /**
     * @return array{user: array, profile: array}
     */
    private function validated(Request $request, ?Guest $guest = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($guest?->user_id)],
            'dob' => ['nullable', 'date', 'before:today'],
            'id_type' => ['nullable', 'required_with:id_number', Rule::in(array_keys(Guest::ID_TYPES))],
            'id_number' => [
                'nullable', 'required_with:id_type', 'string', 'max:50',
                Rule::unique('guest_profiles', 'id_number')->where('id_type', $request->input('id_type'))->ignore($guest?->id),
            ],
            'nationality' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'emergency_contact.name' => ['nullable', 'string', 'max:255'],
            'emergency_contact.phone' => ['nullable', 'string', 'max:30'],
            'emergency_contact.relationship' => ['nullable', 'string', 'max:50'],
            'preferences.tags' => ['nullable', 'array', 'max:20'],
            'preferences.tags.*' => ['string', 'max:50'],
            'preferences.allergies' => ['nullable', 'string', 'max:1000'],
            'preferences.notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'id_number.unique' => 'Another guest already has this ID number.',
        ]);

        $contact = [
            'name' => $data['emergency_contact']['name'] ?? null,
            'phone' => $data['emergency_contact']['phone'] ?? null,
            'relationship' => $data['emergency_contact']['relationship'] ?? null,
        ];

        return [
            'user' => [
                'name' => $data['name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'dob' => $data['dob'] ?? null,
            ],
            'profile' => [
                'id_type' => $data['id_type'] ?? null,
                'id_number' => $data['id_number'] ?? null,
                'nationality' => $data['nationality'] ?? null,
                'address' => $data['address'] ?? null,
                'emergency_contact' => array_filter($contact) ? $contact : null,
                'preferences' => [
                    'tags' => array_values(array_unique($data['preferences']['tags'] ?? [])),
                    'allergies' => $data['preferences']['allergies'] ?? null,
                    'notes' => $data['preferences']['notes'] ?? null,
                ],
            ],
        ];
    }

    /**
     * What the booking form needs to add the guest to its picker.
     */
    private function summary(Guest $guest): array
    {
        return [
            'id' => $guest->id,
            'user_id' => $guest->user_id,
            'name' => $guest->user->name,
            'phone' => $guest->user->phone,
            'email' => $guest->user->email,
            'url' => route('guests.show', $guest),
        ];
    }
}
