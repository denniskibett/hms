<?php

namespace App\Http\Controllers;

use App\Enums\MealPlan;
use App\Enums\StayStatus;
use App\Exceptions\BookingException;
use App\Helpers\SystemHelper;
use App\Models\BookingRequest;
use App\Models\GuestMessage;
use App\Models\Room;
use App\Models\Stay;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StayController extends Controller
{
    /**
     * List tabs: label => scope applied to the query.
     */
    private const TABS = [
        'all' => 'All',
        'arrivals' => 'Arriving today',
        'in_house' => 'In house',
        'departures' => 'Departing today',
        'upcoming' => 'Upcoming',
    ];

    public function __construct(private BookingService $bookings)
    {
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'tab' => ['nullable', Rule::in(array_keys(self::TABS))],
            'status' => ['nullable', Rule::enum(StayStatus::class)],
            'search' => ['nullable', 'string', 'max:100'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);
        $tab = $filters['tab'] ?? 'all';

        $stays = $this->applyTab(Stay::query(), $tab)
            ->with(['guest', 'roomAllocations.room'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->withStatus($status))
            ->when($filters['from'] ?? null, fn ($q, $from) => $q->whereDate('departure_date', '>', $from))
            ->when($filters['to'] ?? null, fn ($q, $to) => $q->whereDate('arrival_date', '<', $to))
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->whereHas('guest', fn ($g) => $g->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%"))
                        ->orWhereHas('roomAllocations.room', fn ($r) => $r->where('room_number', $search));
                    if (ctype_digit(ltrim($search, '#'))) {
                        $q->orWhere('id', (int) ltrim($search, '#'));
                    }
                });
            })
            // Front-desk tabs read soonest first; the full list newest first
            ->orderBy('arrival_date', $tab === 'all' ? 'desc' : 'asc')
            ->paginate(SystemHelper::paginationLimit())
            ->withQueryString();

        $tabCounts = collect(self::TABS)->map(fn ($label, $key) => $key === 'all' ? null : $this->applyTab(Stay::query(), $key)->count());

        return view('stays.index', [
            'stays' => $stays,
            'tabs' => self::TABS,
            'tab' => $tab,
            'tabCounts' => $tabCounts,
            'filters' => $filters,
        ]);
    }

    public function create(Request $request): View
    {
        $stay = new Stay([
            'guest_id' => $request->integer('guest_id') ?: null,
            'arrival_date' => today(),
            'departure_date' => today()->addDay(),
            'meal_plan' => MealPlan::BedAndBreakfast,
            'status' => StayStatus::Booked->value,
        ]);

        // Converting a website request: fill in its dates, plan and notes, and
        // suggest free rooms of the type asked for
        $bookingRequest = $request->integer('request')
            ? BookingRequest::with('roomType')->where('status', 'new')->find($request->integer('request'))
            : null;
        $lines = null;

        if ($bookingRequest) {
            $stay->fill([
                'arrival_date' => $bookingRequest->arrival_date,
                'departure_date' => $bookingRequest->departure_date,
                'meal_plan' => $bookingRequest->meal_plan,
                'special_requests' => trim("Website request {$bookingRequest->reference}" . ($bookingRequest->message ? ": {$bookingRequest->message}" : '')),
            ]);

            $free = Room::availableBetween($bookingRequest->arrival_date, $bookingRequest->departure_date)
                ->when($bookingRequest->room_type_id, fn ($q, $type) => $q->orderByRaw('room_type_id = ? desc', [$type]))
                ->orderBy('room_number')
                ->take($bookingRequest->rooms)
                ->pluck('id');
            $count = max($bookingRequest->rooms, 1);
            $lines = collect(range(0, $count - 1))->map(fn ($i) => [
                'room_id' => $free[$i] ?? '',
                'adults' => max(intdiv($bookingRequest->adults, $count) + ($i < $bookingRequest->adults % $count ? 1 : 0), 1),
                'children' => intdiv($bookingRequest->children, $count) + ($i < $bookingRequest->children % $count ? 1 : 0),
                'rate' => null,
                'guest_notes' => null,
            ])->all();
        }

        return view('stays.create', $this->formData($stay, $lines) + ['bookingRequest' => $bookingRequest]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        try {
            $stay = $this->bookings->create($data, $request->user());
        } catch (BookingException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        // Booked from a website request
        if ($requestId = $request->integer('booking_request_id')) {
            BookingRequest::whereKey($requestId)->where('status', 'new')->update([
                'status' => 'converted',
                'stay_id' => $stay->id,
                'handled_by' => $request->user()->id,
                'handled_at' => now(),
            ]);
        }

        // Reservations are provisional; confirm only firm bookings
        if ($stay->status === StayStatus::Booked->value) {
            GuestMessage::forStay($stay, 'booking_confirmed', by: $request->user());
        }

        return redirect()->route('stays.show', $stay)->with('success', "Booking #{$stay->id} saved.");
    }

    public function show(Stay $stay): View
    {
        $stay->load([
            'guest.guestProfile',
            'createdByUser',
            'roomAllocations.room.roomType',
            'invoices.items',
            'invoices.payments',
            'services',
        ]);

        $messages = GuestMessage::with('sender:id,name')->where('stay_id', $stay->id)->latest('id')->take(10)->get();

        return view('stays.show', compact('stay', 'messages'));
    }

    public function edit(Stay $stay): View|RedirectResponse
    {
        if (! $stay->state->isEditable()) {
            return redirect()->route('stays.show', $stay)->with('error', "A {$stay->state->label()} booking can't be changed.");
        }

        $stay->load('roomAllocations');

        return view('stays.edit', $this->formData($stay));
    }

    public function update(Request $request, Stay $stay): RedirectResponse
    {
        $data = $stay->state === StayStatus::CheckedIn
            ? $request->validate([
                'departure_date' => ['required', 'date'],
                'special_requests' => ['nullable', 'string', 'max:2000'],
            ])
            : $this->validated($request);

        try {
            $this->bookings->update($stay, $data, $request->user());
        } catch (BookingException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('stays.show', $stay)->with('success', "Booking #{$stay->id} updated.");
    }

    /**
     * Only cancelled bookings without payments can be removed.
     */
    public function destroy(Stay $stay): RedirectResponse
    {
        if ($stay->state !== StayStatus::Cancelled || $stay->invoices()->where('paid_amount', '>', 0)->exists()) {
            return back()->with('error', 'Only cancelled bookings with no payments can be deleted.');
        }

        $stay->delete();

        return redirect()->route('stays.index')->with('success', "Booking #{$stay->id} deleted.");
    }

    public function checkIn(Request $request, Stay $stay): RedirectResponse
    {
        return $this->runAction(function () use ($request, $stay) {
            $this->bookings->checkIn($stay, $request->user());
            GuestMessage::forStay($stay->fresh(), 'checked_in', by: $request->user());
        }, "Stay #{$stay->id} checked in.");
    }

    public function checkOut(Request $request, Stay $stay): RedirectResponse
    {
        return $this->runAction(fn () => $this->bookings->checkOut($stay, $request->user()), "Stay #{$stay->id} checked out. Rooms sent for cleaning.");
    }

    public function cancel(Request $request, Stay $stay): RedirectResponse
    {
        $reason = $request->validate(['reason' => ['nullable', 'string', 'max:500']])['reason'] ?? null;

        return $this->runAction(fn () => $this->bookings->cancel($stay, $request->user(), $reason), "Booking #{$stay->id} cancelled.");
    }

    /**
     * Live price and availability for the booking form.
     */
    public function quote(Request $request): JsonResponse
    {
        $data = $request->validate([
            'stay_id' => ['nullable', 'integer'],
            'arrival_date' => ['required', 'date'],
            'departure_date' => ['required', 'date'],
            'meal_plan' => ['required', Rule::enum(MealPlan::class)],
            'rooms' => ['array'],
            'rooms.*.room_id' => ['required', 'integer'],
            'rooms.*.adults' => ['required', 'integer', 'min:1', 'max:20'],
            'rooms.*.children' => ['nullable', 'integer', 'min:0', 'max:20'],
            'rooms.*.rate' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $quote = $this->bookings->quote($data, isset($data['stay_id']) ? Stay::find($data['stay_id']) : null);
        } catch (BookingException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        // Rooms free for these dates, so the form can offer them
        $free = Room::availableBetween($data['arrival_date'], $data['departure_date'], $data['stay_id'] ?? null)->pluck('id');

        return response()->json(['success' => true, 'quote' => $quote, 'available_room_ids' => $free]);
    }

    // ---- Helpers ---------------------------------------------------------

    private function applyTab($query, string $tab)
    {
        return match ($tab) {
            'arrivals' => $query->arrivingToday(),
            'in_house' => $query->inHouse(),
            'departures' => $query->departingToday(),
            'upcoming' => $query->upcoming(),
            default => $query,
        };
    }

    private function runAction(callable $action, string $message): RedirectResponse
    {
        try {
            $action();
        } catch (BookingException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $message);
    }

    private function formData(Stay $stay, ?array $lines = null): array
    {
        $guests = User::whereHas('roles', fn ($q) => $q->where('name', 'guest'))
            ->orWhere('id', $stay->guest_id)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone']);

        $rooms = Room::with('roomType:id,name,base_rate,capacity')
            ->whereNotIn('status', ['maintenance', 'out_of_order'])
            ->orWhereIn('id', $stay->roomAllocations->pluck('room_id'))
            ->orderBy('room_number')
            ->get(['id', 'room_number', 'room_type_id', 'floor', 'status']);

        return [
            'stay' => $stay,
            'guests' => $guests,
            'rooms' => $rooms,
            'lines' => old('rooms', $lines ?? $stay->roomAllocations->map(fn ($a) => [
                'room_id' => $a->room_id,
                'adults' => $a->adults,
                'children' => $a->children,
                'rate' => (float) $a->rate_applied,
                'guest_notes' => $a->guest_notes,
            ])->values()->all() ?: [['room_id' => '', 'adults' => 1, 'children' => 0, 'rate' => null, 'guest_notes' => null]]),
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'guest_id' => ['required', 'exists:users,id'],
            'arrival_date' => ['required', 'date'],
            'departure_date' => ['required', 'date', 'after:arrival_date'],
            'meal_plan' => ['required', Rule::enum(MealPlan::class)],
            'status' => ['required', Rule::in([StayStatus::Reserved->value, StayStatus::Booked->value, StayStatus::CheckedIn->value])],
            'special_requests' => ['nullable', 'string', 'max:2000'],
            'rooms' => ['required', 'array', 'min:1'],
            'rooms.*.room_id' => ['required', 'integer', 'distinct', 'exists:rooms,id'],
            'rooms.*.adults' => ['required', 'integer', 'min:1', 'max:20'],
            'rooms.*.children' => ['nullable', 'integer', 'min:0', 'max:20'],
            'rooms.*.rate' => ['nullable', 'numeric', 'min:0'],
            'rooms.*.guest_notes' => ['nullable', 'string', 'max:255'],
        ], [
            'rooms.required' => 'Choose at least one room.',
            'rooms.*.room_id.required' => 'Choose a room for every line.',
            'rooms.*.room_id.distinct' => 'The same room is listed twice.',
        ]);
    }
}
