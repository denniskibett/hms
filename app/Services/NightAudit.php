<?php

namespace App\Services;

use App\Enums\RoomStatus;
use App\Enums\StayStatus;
use App\Models\NightAudit as NightAuditRecord;
use App\Models\Payment;
use App\Models\Room;
use App\Models\RoomAllocation;
use App\Models\Stay;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Night audit: close a business day.
 *
 *  1. Guests due since the last closed day who never checked in become no-shows
 *     (their unpaid invoice is cancelled; anything paid stays for a decision).
 *  2. Guests still in house past their departure date are listed (overstays).
 *  3. The day's figures are kept: rooms sold, occupancy, average room rate
 *     (ADR), revenue per available room (RevPAR), room revenue for that night,
 *     money taken by method, arrivals, departures, and what in-house guests owe.
 *
 * Running it again for the same day refreshes the figures (no-shows already
 * marked stay marked).
 */
class NightAudit
{
    public function __construct(private BookingService $bookings) {}

    public function run(CarbonInterface $date, ?User $by = null, ?string $notes = null): NightAuditRecord
    {
        $date = Carbon::parse($date)->startOfDay();
        if ($date->isAfter(today())) {
            throw new \InvalidArgumentException('A day can only be closed once it has started.');
        }

        // 1. No-shows: due since the last closed day (or on this day, the first time) and never checked in.
        //    Older forgotten bookings are left alone; they are a one-off clean-up, not this night's work.
        $lastClosed = NightAuditRecord::whereDate('business_date', '<', $date)->max('business_date');
        $from = $lastClosed ? Carbon::parse($lastClosed)->addDay() : $date->copy();
        $noShows = Stay::with('guest')
            ->whereIn('status', [StayStatus::Reserved->value, StayStatus::Booked->value])
            ->whereDate('arrival_date', '>=', $from)
            ->whereDate('arrival_date', '<=', $date)
            ->get()
            ->map(function (Stay $stay) use ($by) {
                $this->bookings->markNoShow($stay, $by);
                $paid = (float) $stay->invoices()->sum('paid_amount');

                return ['stay' => $stay->id, 'guest' => $stay->guest?->name, 'arrival' => $stay->arrival_date->toDateString(), 'paid' => $paid];
            })->values();

        // 2. Overstays: still checked in after their departure date
        $overstays = Stay::with('guest')
            ->where('status', StayStatus::CheckedIn->value)
            ->whereDate('departure_date', '<=', $date)
            ->get()
            ->map(fn (Stay $s) => ['stay' => $s->id, 'guest' => $s->guest?->name, 'departure' => $s->departure_date->toDateString()])
            ->values();

        // 3. Figures for the night of $date
        $sellable = Room::whereNotIn('status', [RoomStatus::Maintenance->value, RoomStatus::OutOfOrder->value])->count();
        $sold = RoomAllocation::query()
            ->whereDate('from_date', '<=', $date)
            ->whereDate('to_date', '>', $date)
            ->whereHas('stay', fn ($q) => $q->whereIn('status', [StayStatus::CheckedIn->value, StayStatus::CheckedOut->value]))
            ->get(['id', 'rate_applied', 'adults', 'children']);
        $roomsSold = $sold->count();
        $roomRevenue = round((float) $sold->sum('rate_applied'), 2);

        $payments = Payment::where('status', 'completed')
            ->whereDate('received_at', $date)
            ->selectRaw('method, SUM(amount) as total, COUNT(*) as n')
            ->groupBy('method')->get()
            ->mapWithKeys(fn ($row) => [$row->method => ['label' => Payment::METHODS[$row->method] ?? $row->method, 'total' => round((float) $row->total, 2), 'count' => (int) $row->n]]);

        $inHouse = Stay::where('status', StayStatus::CheckedIn->value)->with('invoices')->get();
        $inHouseOwed = round($inHouse->sum(fn (Stay $s) => $s->invoices->where('status', '!=', 'cancelled')->whereNull('company_id')->sum(fn ($i) => max($i->balance, 0))), 2);

        $summary = [
            'rooms_sellable' => $sellable,
            'rooms_sold' => $roomsSold,
            'guests' => (int) $sold->sum('adults') + (int) $sold->sum('children'),
            'occupancy_percent' => $sellable > 0 ? round($roomsSold / $sellable * 100, 1) : 0.0,
            'room_revenue' => $roomRevenue,
            'adr' => $roomsSold > 0 ? round($roomRevenue / $roomsSold, 2) : 0.0,
            'revpar' => $sellable > 0 ? round($roomRevenue / $sellable, 2) : 0.0,
            'arrivals' => Stay::whereDate('check_in', $date)->count(),
            'departures' => Stay::whereDate('check_out', $date)->count(),
            'new_bookings' => Stay::whereDate('created_at', $date)->count(),
            'payments' => $payments->all(),
            'payments_total' => round($payments->sum('total'), 2),
            'in_house' => $inHouse->count(),
            'in_house_owed' => $inHouseOwed,
            'no_shows' => $noShows->all(),
            'overstays' => $overstays->all(),
        ];

        return NightAuditRecord::updateOrCreate(
            ['business_date' => $date->toDateString()],
            ['run_by' => $by?->id, 'run_at' => now(), 'summary' => $summary, 'notes' => $notes]
        );
    }
}
