<?php

namespace App\Http\Controllers;

use App\Enums\MealPlan;
use App\Helpers\SystemHelper;
use App\Models\BookingRequest;
use App\Models\Facility;
use App\Models\FacilityPackage;
use App\Models\GuestMessage;
use App\Models\Room;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * The public website: home, rooms and availability, booking requests,
 * about and contact. Requests become bookings at the front desk.
 */
class FrontController extends Controller
{
    public function home(): View
    {
        return view('public.home', [
            'roomTypes' => $this->roomTypeCards(),
            'venues' => Facility::available()->orderByDesc('capacity')->get(),
            'packages' => FacilityPackage::active()->where('charge_basis', 'per_person')->orderBy('price')->get(),
        ]);
    }

    public function about(): View
    {
        return view('public.about', ['roomTypes' => $this->roomTypeCards()]);
    }

    public function contact(): View
    {
        return view('public.contact');
    }

    public function submitContact(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'message' => 'required|string|max:5000',
            'website' => 'prohibited', // spam trap, hidden from people
        ]);

        try {
            Mail::raw("From: {$data['name']} <{$data['email']}>\n\n{$data['message']}", function ($mail) use ($data) {
                $mail->to(SystemHelper::contactEmail() ?: config('mail.from.address'))
                    ->replyTo($data['email'], $data['name'])
                    ->subject('Website enquiry from ' . $data['name']);
            });
        } catch (\Throwable $e) {
            Log::error('Contact form mail failed', ['error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'Sorry, we could not send your message. Please try again later.');
        }

        return back()->with('success', 'Thank you! We will get back to you shortly.');
    }

    /**
     * Pick dates and guests; see which room types are free and what they cost.
     */
    public function book(Request $request): View
    {
        $search = $request->validate([
            'arrival' => ['nullable', 'date', 'after_or_equal:today'],
            'departure' => ['nullable', 'date', 'after:arrival'],
            'adults' => ['nullable', 'integer', 'min:1', 'max:20'],
            'children' => ['nullable', 'integer', 'min:0', 'max:20'],
            'rooms' => ['nullable', 'integer', 'min:1', 'max:10'],
            'meal_plan' => ['nullable', Rule::enum(MealPlan::class)],
        ]);

        $search += ['adults' => 2, 'children' => 0, 'rooms' => 1, 'meal_plan' => MealPlan::BedAndBreakfast->value];
        $options = null;

        if (! empty($search['arrival']) && ! empty($search['departure'])) {
            $arrival = Carbon::parse($search['arrival'])->startOfDay();
            $departure = Carbon::parse($search['departure'])->startOfDay();
            $nights = (int) $arrival->diffInDays($departure);
            $free = Room::availableBetween($arrival, $departure)->selectRaw('room_type_id, COUNT(*) as n')->groupBy('room_type_id')->pluck('n', 'room_type_id');

            // Guests spread over the rooms asked for
            $rooms = (int) $search['rooms'];
            $adultsPerRoom = (int) ceil($search['adults'] / $rooms);
            $childrenPerRoom = (int) ceil($search['children'] / $rooms);
            $plan = MealPlan::from($search['meal_plan']);

            $options = RoomType::orderBy('base_rate')->get()->map(function (RoomType $type) use ($free, $rooms, $adultsPerRoom, $childrenPerRoom, $plan, $nights) {
                $nightly = $type->quote($plan, $adultsPerRoom, $childrenPerRoom)['nightly'];

                return [
                    'type' => $type,
                    'free' => (int) ($free[$type->id] ?? 0),
                    'fits' => $adultsPerRoom + $childrenPerRoom <= max((int) $type->capacity, 1) + 1,
                    'nightly' => $nightly,
                    'total' => round($nightly * $rooms * $nights, 2),
                ];
            })->map(fn ($o) => $o + ['bookable' => $o['free'] >= $rooms && $o['fits']]);

            $search['nights'] = $nights;
        }

        return view('public.book', compact('search', 'options'));
    }

    public function submitBooking(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'arrival_date' => ['required', 'date', 'after_or_equal:today'],
            'departure_date' => ['required', 'date', 'after:arrival_date'],
            'adults' => ['required', 'integer', 'min:1', 'max:20'],
            'children' => ['nullable', 'integer', 'min:0', 'max:20'],
            'rooms' => ['required', 'integer', 'min:1', 'max:10'],
            'room_type_id' => ['nullable', 'exists:room_types,id'],
            'meal_plan' => ['required', Rule::enum(MealPlan::class)],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+\s()-]{7,}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'message' => ['nullable', 'string', 'max:2000'],
            'website' => ['prohibited'], // spam trap, hidden from people
        ], [
            'phone.regex' => 'Enter a phone number we can call or text, e.g. 0712 345 678.',
            'departure_date.after' => 'Check-out must be after check-in.',
        ]);

        unset($data['website']);
        $data['children'] ??= 0;
        $bookingRequest = BookingRequest::create($data + ['ip_address' => $request->ip()]);
        GuestMessage::forBookingRequest($bookingRequest, 'request_received');

        return redirect()->route('book.thanks')->with('booking_request', $bookingRequest->reference);
    }

    public function thanks(Request $request): View|RedirectResponse
    {
        if (! session('booking_request')) {
            return redirect()->route('book');
        }

        return view('public.thanks', ['reference' => session('booking_request')]);
    }

    /**
     * Room types with a "from" price (B&B, two adults or the room's capacity).
     */
    private function roomTypeCards()
    {
        return RoomType::withCount('rooms')->orderBy('base_rate')->get()
            ->filter(fn (RoomType $type) => $type->rooms_count > 0)
            ->map(fn (RoomType $type) => [
                'type' => $type,
                'from' => $type->quote(MealPlan::BedAndBreakfast, min(2, max((int) $type->capacity, 1)))['nightly'],
            ])
            ->values();
    }
}
