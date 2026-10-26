<?php

namespace App\Http\Controllers;

use App\Helpers\SystemHelper;
use App\Models\BookingRequest;
use App\Models\GuestMessage;
use App\Models\Role;
use App\Models\User;
use App\Services\CoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Booking requests from the website, worked by the front desk: convert into
 * a booking (via the normal booking form) or decline.
 */
class BookingRequestController extends Controller
{
    public function __construct(private CoreService $core)
    {
    }

    public function index(Request $request): View
    {
        $status = $request->validate(['status' => ['nullable', Rule::in([...array_keys(BookingRequest::STATUSES), 'all'])]])['status'] ?? 'new';

        $requests = BookingRequest::with(['roomType:id,name', 'stay:id', 'handler:id,name'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($status === 'new', fn ($q) => $q->orderBy('arrival_date'), fn ($q) => $q->latest('id'))
            ->paginate(SystemHelper::paginationLimit())
            ->withQueryString();

        $counts = BookingRequest::selectRaw('status, COUNT(*) as n')->groupBy('status')->pluck('n', 'status');

        return view('booking-requests.index', compact('requests', 'status', 'counts'));
    }

    /**
     * Find or register the guest, then open the booking form filled in from the request.
     */
    public function convert(Request $request, BookingRequest $bookingRequest): RedirectResponse
    {
        if ($bookingRequest->status !== 'new') {
            return back()->with('error', "{$bookingRequest->reference} has already been handled.");
        }

        $guest = $this->findGuest($bookingRequest) ?? DB::transaction(function () use ($bookingRequest) {
            $user = User::create([
                'name' => $bookingRequest->name,
                'phone' => $bookingRequest->phone,
                'email' => $bookingRequest->email && ! User::where('email', $bookingRequest->email)->exists() ? $bookingRequest->email : null,
                // Guests don't sign in from a website request; an unusable password
                'password' => Hash::make(Str::random(40)),
            ]);
            $user->roles()->attach(Role::firstOrCreate(['name' => 'guest'])->id);
            $user->guestProfile()->create(['nationality' => 'Kenya']);

            return $user;
        });

        return redirect()->route('stays.create', [
            'guest_id' => $guest->id,
            'request' => $bookingRequest->id,
        ]);
    }

    public function decline(Request $request, BookingRequest $bookingRequest): RedirectResponse
    {
        $reason = $request->validate(['reason' => ['required', 'string', 'max:255']])['reason'];

        if ($bookingRequest->status !== 'new') {
            return back()->with('error', "{$bookingRequest->reference} has already been handled.");
        }

        $bookingRequest->update([
            'status' => 'declined',
            'decline_reason' => $reason,
            'handled_by' => $request->user()->id,
            'handled_at' => now(),
        ]);
        GuestMessage::forBookingRequest($bookingRequest, 'request_declined', ['reason' => $reason], $request->user());
        $this->core->log($request->user()->id, 'booking_request_declined', "{$bookingRequest->reference} declined: {$reason}", ['booking_request_id' => $bookingRequest->id]);

        return back()->with('success', "{$bookingRequest->reference} declined.");
    }

    /**
     * An existing guest with the same phone (digits only, last 9) or email.
     */
    private function findGuest(BookingRequest $bookingRequest): ?User
    {
        $digits = substr(preg_replace('/\D/', '', $bookingRequest->phone), -9);

        return User::whereHas('roles', fn ($q) => $q->where('name', 'guest'))
            ->where(fn ($q) => $q
                ->when(strlen($digits) === 9, fn ($q) => $q->orWhereRaw("RIGHT(REGEXP_REPLACE(phone, '[^0-9]', ''), 9) = ?", [$digits]))
                ->when($bookingRequest->email, fn ($q) => $q->orWhere('email', $bookingRequest->email)))
            ->first();
    }
}
