<?php

namespace App\Http\Controllers;

use App\Enums\FacilityBookingStatus;
use App\Exceptions\BillingException;
use App\Helpers\SystemHelper;
use App\Models\Facility;
use App\Models\FacilityBooking;
use App\Models\FacilityBookingItem;
use App\Models\FacilityPackage;
use App\Services\BillingService;
use App\Services\CoreService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Events: conferences, meetings, weddings booked in a venue. Each event is
 * priced from its items and billed on its own invoice.
 */
class FacilityBookingController extends Controller
{
    public function __construct(private BillingService $billing, private CoreService $core)
    {
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::enum(FacilityBookingStatus::class)],
            'facility_id' => ['nullable', 'integer'],
            'when' => ['nullable', Rule::in(['upcoming', 'past'])],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $events = FacilityBooking::with(['facility:id,name', 'items', 'invoices' => fn ($q) => $q->where('status', '!=', 'cancelled')])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->withStatus($status))
            ->when($filters['facility_id'] ?? null, fn ($q, $id) => $q->where('facility_id', $id))
            ->when(($filters['when'] ?? null) === 'upcoming', fn ($q) => $q->whereDate('end_date', '>=', today()))
            ->when(($filters['when'] ?? null) === 'past', fn ($q) => $q->whereDate('end_date', '<', today()))
            ->when($filters['search'] ?? null, fn ($q, $term) => $q->search($term))
            ->when(($filters['when'] ?? null) === 'upcoming', fn ($q) => $q->orderBy('start_date'), fn ($q) => $q->latest('start_date'))
            ->paginate(SystemHelper::paginationLimit())
            ->withQueryString();

        $monthStart = today()->startOfMonth();
        $stats = [
            'today' => FacilityBooking::upcoming()->whereDate('start_date', '<=', today())->count(),
            'upcoming' => FacilityBooking::upcoming()->count(),
            'tentative' => FacilityBooking::withStatus(FacilityBookingStatus::Tentative)->whereDate('end_date', '>=', today())->count(),
            'this_month' => FacilityBooking::whereIn('status', [FacilityBookingStatus::Confirmed->value, FacilityBookingStatus::Completed->value])
                ->whereBetween('start_date', [$monthStart, $monthStart->copy()->endOfMonth()])
                ->count(),
        ];

        return view('events.index', array_merge(compact('events', 'stats', 'filters'), $this->formOptions()));
    }

    public function show(FacilityBooking $event): View
    {
        $event->load(['facility', 'items.package', 'stay.guest', 'createdByUser', 'invoices.items', 'invoices.payments']);

        return view('events.show', array_merge(compact('event'), $this->formOptions()));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $event = DB::transaction(function () use ($data, $request) {
            $this->ensureVenueFree($data);

            $event = FacilityBooking::create($data['event'] + ['created_by' => $request->user()->id]);
            $event->items()->createMany($data['items']);
            $this->billing->syncFacilityBookingInvoice($event);

            return $event;
        });

        $this->core->log($request->user()->id, 'event_booked', "Event {$event->reference} \"{$event->title}\" booked", ['facility_booking_id' => $event->id]);

        return response()->json([
            'success' => true,
            'message' => "Event {$event->reference} booked.",
            'url' => route('events.show', $event),
        ], 201);
    }

    public function update(Request $request, FacilityBooking $event): JsonResponse
    {
        if (! $event->status->holdsVenue()) {
            return response()->json([
                'success' => false,
                'message' => "A {$event->status->label()} event can't be changed.",
            ], 422);
        }

        $data = $this->validated($request);
        unset($data['event']['status']); // status changes go through updateStatus()

        try {
            DB::transaction(function () use ($event, $data) {
                $this->ensureVenueFree($data, $event->id);

                $event->update($data['event']);
                $event->items()->delete();
                $event->items()->createMany($data['items']);
                $this->billing->syncFacilityBookingInvoice($event);
            });
        } catch (BillingException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        $this->core->log($request->user()->id, 'event_updated', "Event {$event->reference} updated", ['facility_booking_id' => $event->id]);

        return response()->json([
            'success' => true,
            'message' => "Event {$event->reference} updated.",
            'url' => route('events.show', $event),
        ]);
    }

    /**
     * Confirm, complete, cancel, or put back to tentative.
     */
    public function updateStatus(Request $request, FacilityBooking $event): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::enum(FacilityBookingStatus::class)],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);
        $status = FacilityBookingStatus::from($data['status']);

        if (! $event->status->canTransitionTo($status)) {
            return back()->with('error', "A {$event->status->label()} event can't be marked {$status->label()}.");
        }

        DB::transaction(function () use ($event, $status, $data) {
            $note = $status === FacilityBookingStatus::Cancelled && ! empty($data['reason'])
                ? trim(($event->notes ? $event->notes . "\n" : '') . 'Cancelled: ' . $data['reason'])
                : $event->notes;

            $event->update(['status' => $status, 'notes' => $note]);

            if ($status === FacilityBookingStatus::Cancelled) {
                $this->billing->syncFacilityBookingInvoice($event);
            }
        });

        $this->core->log($request->user()->id, 'event_status', "Event {$event->reference} marked {$status->label()}", ['facility_booking_id' => $event->id]);

        return back()->with('success', "Event {$event->reference} is now {$status->label()}.");
    }

    /**
     * Venues and packages for the booking modal.
     */
    private function formOptions(): array
    {
        return [
            'venues' => Facility::orderBy('name')->get(['id', 'name', 'capacity', 'base_rate', 'status']),
            'packages' => FacilityPackage::active()->orderBy('name')->get(['id', 'facility_id', 'name', 'price', 'charge_basis']),
        ];
    }

    /**
     * @return array{event: array, items: array}
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'facility_id' => ['required', 'exists:facilities,id'],
            'stay_id' => ['nullable', 'integer', 'exists:stays,id'],
            'title' => ['required', 'string', 'max:255'],
            'client_name' => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:30'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'start_time' => ['nullable', 'date_format:H:i', 'required_with:end_time'],
            'end_time' => ['nullable', 'date_format:H:i', 'required_with:start_time', 'after:start_time'],
            'pax' => ['required', 'integer', 'min:1', 'max:100000'],
            'status' => ['nullable', Rule::in([FacilityBookingStatus::Tentative->value, FacilityBookingStatus::Confirmed->value])],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.facility_package_id' => ['nullable', 'integer', 'exists:facility_packages,id'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.charge_basis' => ['required', Rule::in(array_keys(FacilityBookingItem::BASES))],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01', 'max:100000'],
            'items.*.days' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0', 'max:99999999'],
        ], [
            'items.required' => 'Add at least one package or charge.',
            'end_time.after' => 'The end time must be after the start time.',
        ]);

        $days = (int) Carbon::parse($data['start_date'])->diffInDays(Carbon::parse($data['end_date'])) + 1;
        $venue = Facility::find($data['facility_id']);
        $errors = [];

        if ($data['pax'] > $venue->capacity) {
            $errors['pax'] = "{$venue->name} holds {$venue->capacity} people.";
        }
        foreach ($data['items'] as $i => $item) {
            if ($item['days'] > $days) {
                $errors["items.{$i}.days"] = "\"{$item['description']}\" is for {$item['days']} days but the event is {$days}.";
            }
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        $items = array_map(fn ($item) => [
            'facility_package_id' => $item['facility_package_id'] ?? null,
            'description' => $item['description'],
            'charge_basis' => $item['charge_basis'],
            'quantity' => $item['charge_basis'] === 'venue' ? 1 : $item['quantity'],
            'days' => $item['days'],
            'unit_price' => $item['unit_price'],
        ], $data['items']);

        unset($data['items']);
        $data['status'] ??= FacilityBookingStatus::Tentative->value;

        return ['event' => $data, 'items' => $items];
    }

    /**
     * Lock the venue and refuse dates that clash with another event.
     */
    private function ensureVenueFree(array $data, ?int $ignoreId = null): void
    {
        $venue = Facility::whereKey($data['event']['facility_id'])->lockForUpdate()->firstOrFail();

        if ($venue->status !== 'available') {
            throw ValidationException::withMessages(['facility_id' => "{$venue->name} is {$venue->status_label}."]);
        }

        $clash = $venue->conflictingBooking(
            Carbon::parse($data['event']['start_date']),
            Carbon::parse($data['event']['end_date']),
            $data['event']['start_time'] ?? null,
            $data['event']['end_time'] ?? null,
            $ignoreId,
        );

        if ($clash) {
            throw ValidationException::withMessages([
                'facility_id' => "{$venue->name} is booked for \"{$clash->title}\" ({$clash->reference}) on "
                    . $clash->start_date->format('d M') . ($clash->days > 1 ? '–' . $clash->end_date->format('d M') : '')
                    . ($clash->time_range ? ", {$clash->time_range}" : '') . '.',
            ]);
        }
    }
}
