<?php

namespace App\Services;

use App\Enums\MealPlan;
use App\Enums\RoomStatus;
use App\Enums\StayStatus;
use App\Exceptions\BookingException;
use App\Models\Room;
use App\Models\RoomAllocation;
use App\Models\Stay;
use App\Models\Task;
use App\Models\TaskType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Owns the booking lifecycle: quote, create, change, cancel, check in, check out.
 *
 * Booking data shape (create / update / quote):
 *   guest_id, arrival_date, departure_date, meal_plan, status, special_requests,
 *   rooms: [ ['room_id' => 1, 'adults' => 2, 'children' => 0, 'rate' => null, 'guest_notes' => null], … ]
 * 'rate' is an optional manual nightly price; when empty the room is priced from its rates.
 */
class BookingService
{
    public function __construct(private CoreService $core)
    {
    }

    /**
     * Price and availability for a booking, without saving anything.
     */
    public function quote(array $data, ?Stay $ignore = null): array
    {
        [$arrival, $departure] = $this->dates($data);
        $mealPlan = MealPlan::from($data['meal_plan'] ?? MealPlan::BedAndBreakfast->value);
        $nights = (int) $arrival->diffInDays($departure);

        $rooms = Room::with('roomType')->findMany(collect($data['rooms'] ?? [])->pluck('room_id'))->keyBy('id');
        $availableIds = Room::availableBetween($arrival, $departure, $ignore?->id)
            ->whereKey($rooms->keys())
            ->pluck('id')
            ->flip();

        $lines = collect($data['rooms'] ?? [])->map(function (array $line) use ($rooms, $availableIds, $mealPlan, $nights) {
            $room = $rooms[$line['room_id']] ?? null;
            if (! $room) {
                return null;
            }

            $quote = $room->roomType->quote($mealPlan, (int) $line['adults'], (int) ($line['children'] ?? 0), $room);
            $nightly = filled($line['rate'] ?? null) ? round((float) $line['rate'], 2) : $quote['nightly'];

            return [
                'room_id' => $room->id,
                'room_number' => $room->room_number,
                'room_type' => $room->roomType->name,
                'adults' => (int) $line['adults'],
                'children' => (int) ($line['children'] ?? 0),
                'is_available' => $availableIds->has($room->id),
                'quoted_nightly' => $quote['nightly'],
                'nightly' => $nightly,
                'is_manual_rate' => $nightly !== $quote['nightly'],
                'basis' => $quote['basis']->value,
                'description' => $quote['description'],
                'total' => round($nightly * $nights, 2),
            ];
        })->filter()->values();

        return [
            'arrival_date' => $arrival->toDateString(),
            'departure_date' => $departure->toDateString(),
            'nights' => $nights,
            'meal_plan' => $mealPlan->value,
            'rooms' => $lines,
            'total' => round($lines->sum('total'), 2),
            'all_available' => $lines->every('is_available'),
        ];
    }

    /**
     * Create a booking. Rooms are locked while we check and save, so two
     * people can't book the same room for the same night.
     */
    public function create(array $data, User $by): Stay
    {
        $status = StayStatus::from($data['status'] ?? StayStatus::Booked->value);
        if (! in_array($status, [StayStatus::Reserved, StayStatus::Booked, StayStatus::CheckedIn], true)) {
            throw new BookingException('A new booking must be provisional, confirmed or checked in.');
        }

        [$arrival, $departure] = $this->dates($data);
        if ($arrival->lt(today())) {
            throw new BookingException('Arrival date cannot be in the past.');
        }

        $stay = DB::transaction(function () use ($data, $by, $arrival, $departure, $status) {
            $lines = $this->lockAndPrice($data, $arrival, $departure);

            $stay = Stay::create([
                'guest_id' => $data['guest_id'],
                // A walk-in is saved as confirmed, then checked in below
                'status' => ($status === StayStatus::Reserved ? StayStatus::Reserved : StayStatus::Booked)->value,
                'arrival_date' => $arrival,
                'departure_date' => $departure,
                'meal_plan' => $data['meal_plan'] ?? MealPlan::BedAndBreakfast->value,
                'adults' => $lines->sum('adults'),
                'children' => $lines->sum('children'),
                'special_requests' => $data['special_requests'] ?? null,
                'created_by' => $by->id,
            ]);

            $this->saveAllocations($stay, $lines, $arrival, $departure);

            $this->core->log($by->id, 'stay_created', "Stay #{$stay->id} created", ['stay_id' => $stay->id]);

            return $stay;
        });

        // Walk-in: book and check in straight away
        if ($status === StayStatus::CheckedIn) {
            $stay = $this->checkIn($stay, $by);
        }

        return $stay->fresh(['roomAllocations.room']);
    }

    /**
     * Change dates, rooms, guests, meal plan or notes.
     *
     * Rooms kept with the same guests and meal plan keep their agreed rate;
     * changed or new rooms are re-priced. For in-house stays only the
     * departure date and notes can change (room moves come later).
     */
    public function update(Stay $stay, array $data, User $by): Stay
    {
        if (! $stay->state->isEditable()) {
            throw new BookingException("A {$stay->state->label()} booking can't be changed.");
        }

        if ($stay->state === StayStatus::CheckedIn) {
            return $this->updateInHouse($stay, $data, $by);
        }

        [$arrival, $departure] = $this->dates($data);
        if ($arrival->lt(today()) && ! $arrival->equalTo($stay->arrival_date)) {
            throw new BookingException('Arrival date cannot be moved into the past.');
        }

        // Editing can only switch between provisional and confirmed;
        // check-in and cancel have their own actions.
        $newStatus = isset($data['status']) ? StayStatus::from($data['status']) : $stay->state;
        if (! in_array($newStatus, [StayStatus::Reserved, StayStatus::Booked], true)) {
            throw new BookingException('Use the Check In and Cancel actions to change the booking status.');
        }

        return DB::transaction(function () use ($stay, $data, $by, $arrival, $departure, $newStatus) {
            $existing = $stay->roomAllocations->keyBy('room_id');
            $mealPlanChanged = ($data['meal_plan'] ?? $stay->meal_plan->value) !== $stay->meal_plan->value;

            // Keep the agreed rate for unchanged rooms unless a new rate was typed in
            $data['rooms'] = collect($data['rooms'] ?? [])->map(function (array $line) use ($existing, $mealPlanChanged) {
                $old = $existing[$line['room_id']] ?? null;
                $sameGuests = $old && $old->adults === (int) $line['adults'] && $old->children === (int) ($line['children'] ?? 0);

                if ($old && $sameGuests && ! $mealPlanChanged && blank($line['rate'] ?? null)) {
                    $line['rate'] = $old->rate_applied;
                }

                return $line;
            })->all();

            $lines = $this->lockAndPrice($data, $arrival, $departure, $stay);

            $stay->update([
                'guest_id' => $data['guest_id'] ?? $stay->guest_id,
                'arrival_date' => $arrival,
                'departure_date' => $departure,
                'meal_plan' => $data['meal_plan'] ?? $stay->meal_plan->value,
                'adults' => $lines->sum('adults'),
                'children' => $lines->sum('children'),
                'special_requests' => $data['special_requests'] ?? $stay->special_requests,
                'status' => $newStatus->value,
            ]);

            $stay->roomAllocations()->delete();
            $this->saveAllocations($stay, $lines, $arrival, $departure);

            $this->core->log($by->id, 'stay_updated', "Stay #{$stay->id} updated", ['stay_id' => $stay->id]);

            return $stay->fresh(['roomAllocations.room']);
        });
    }

    public function checkIn(Stay $stay, User $by): Stay
    {
        $this->assertTransition($stay, StayStatus::CheckedIn);

        if ($stay->arrival_date->gt(today())) {
            throw new BookingException("Stay #{$stay->id} arrives on {$stay->arrival_date->toFormattedDateString()}. Change the arrival date to check in early.");
        }

        return DB::transaction(function () use ($stay, $by) {
            $rooms = Room::whereIn('id', $stay->roomAllocations()->pluck('room_id'))->lockForUpdate()->get();

            if ($rooms->isEmpty()) {
                throw new BookingException('Assign at least one room before checking in.');
            }

            foreach ($rooms as $room) {
                if ($room->status->isOutOfService()) {
                    throw new BookingException("Room {$room->room_number} is {$room->status->label()}.");
                }

                $other = $room->currentStay()->where('stays.id', '!=', $stay->id)->first();
                if ($other) {
                    throw new BookingException("Room {$room->room_number} is still occupied by stay #{$other->id}. Check that guest out first.");
                }
            }

            $stay->update(['status' => StayStatus::CheckedIn->value, 'check_in' => now()]);
            Room::whereKey($rooms->modelKeys())->update(['status' => RoomStatus::Occupied->value]);

            $this->core->log($by->id, 'check_in', "Stay #{$stay->id} checked in", ['stay_id' => $stay->id]);

            return $stay->fresh(['roomAllocations.room']);
        });
    }

    /**
     * Check out. Leaving early shortens the stay to today (minimum one night)
     * so the rooms become bookable again and only nights used are charged.
     */
    public function checkOut(Stay $stay, User $by): Stay
    {
        $this->assertTransition($stay, StayStatus::CheckedOut);

        return DB::transaction(function () use ($stay, $by) {
            $departure = $stay->departure_date->copy();
            $leavingEarly = today()->lt($departure);

            if ($leavingEarly) {
                $departure = today()->max($stay->arrival_date->copy()->addDay());

                $stay->roomAllocations()->whereDate('from_date', '>=', $departure)->delete();
                $stay->roomAllocations()->whereDate('to_date', '>', $departure)->update(['to_date' => $departure->toDateString()]);
            }

            $stay->update([
                'status' => StayStatus::CheckedOut->value,
                'check_out' => now(),
                'departure_date' => $departure,
            ]);

            $roomIds = $stay->roomAllocations()->pluck('room_id');
            Room::whereKey($roomIds)->update(['status' => RoomStatus::Cleaning->value]);
            $this->createCleaningTasks($stay, $roomIds, $by);

            $this->core->log($by->id, 'check_out', "Stay #{$stay->id} checked out" . ($leavingEarly ? ' early' : ''), ['stay_id' => $stay->id]);

            return $stay->fresh(['roomAllocations.room']);
        });
    }

    public function cancel(Stay $stay, User $by, ?string $reason = null): Stay
    {
        $this->assertTransition($stay, StayStatus::Cancelled);

        $stay->update(['status' => StayStatus::Cancelled->value]);

        $this->core->log($by->id, 'stay_cancelled', "Stay #{$stay->id} cancelled" . ($reason ? ": {$reason}" : ''), ['stay_id' => $stay->id, 'reason' => $reason]);

        return $stay->fresh();
    }

    // ---- Internals -------------------------------------------------------

    private function updateInHouse(Stay $stay, array $data, User $by): Stay
    {
        $departure = Carbon::parse($data['departure_date'] ?? $stay->departure_date)->startOfDay();

        if ($departure->lte(today()) || $departure->lte($stay->arrival_date)) {
            throw new BookingException('Departure must be after today. To leave today, use Check Out.');
        }

        return DB::transaction(function () use ($stay, $data, $by, $departure) {
            $allocations = $stay->roomAllocations()->get();

            // Extending: the rooms must be free for the added nights
            if ($departure->gt($stay->departure_date)) {
                Room::whereIn('id', $allocations->pluck('room_id'))->lockForUpdate()->get();
                $free = Room::availableBetween($stay->departure_date, $departure, $stay->id)
                    ->whereKey($allocations->pluck('room_id'))
                    ->pluck('id');

                $taken = $allocations->pluck('room_id')->diff($free);
                if ($taken->isNotEmpty()) {
                    $numbers = Room::whereKey($taken)->pluck('room_number')->join(', ');
                    throw new BookingException("Room {$numbers} is booked by someone else for the extra nights.");
                }
            }

            $stay->update([
                'departure_date' => $departure,
                'special_requests' => $data['special_requests'] ?? $stay->special_requests,
            ]);
            $stay->roomAllocations()->update(['to_date' => $departure->toDateString()]);

            $this->core->log($by->id, 'stay_updated', "Stay #{$stay->id} departure changed to {$departure->toDateString()}", ['stay_id' => $stay->id]);

            return $stay->fresh(['roomAllocations.room']);
        });
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function dates(array $data): array
    {
        $arrival = Carbon::parse($data['arrival_date'])->startOfDay();
        $departure = Carbon::parse($data['departure_date'])->startOfDay();

        if ($departure->lte($arrival)) {
            throw new BookingException('Departure must be at least one night after arrival.');
        }

        return [$arrival, $departure];
    }

    /**
     * Lock the requested rooms, check they're free and price them.
     */
    private function lockAndPrice(array $data, Carbon $arrival, Carbon $departure, ?Stay $ignore = null): Collection
    {
        $requested = collect($data['rooms'] ?? []);
        if ($requested->isEmpty()) {
            throw new BookingException('Choose at least one room.');
        }
        if ($requested->pluck('room_id')->duplicates()->isNotEmpty()) {
            throw new BookingException('The same room is listed twice.');
        }

        // Row locks serialise concurrent bookings of the same rooms until we commit
        $rooms = Room::with('roomType')->whereIn('id', $requested->pluck('room_id'))->lockForUpdate()->get()->keyBy('id');

        foreach ($requested as $line) {
            $room = $rooms[$line['room_id']] ?? throw new BookingException('One of the selected rooms no longer exists.');

            if ((int) $line['adults'] < 1) {
                throw new BookingException("Room {$room->room_number} needs at least one adult.");
            }
            if ($room->roomType && (int) $line['adults'] > $room->roomType->capacity) {
                throw new BookingException("Room {$room->room_number} ({$room->roomType->name}) takes at most {$room->roomType->capacity} adults.");
            }
            if (! $room->isAvailableForDates($arrival, $departure, $ignore?->id)) {
                throw new BookingException("Room {$room->room_number} is not available from {$arrival->toFormattedDateString()} to {$departure->toFormattedDateString()}.");
            }
        }

        return collect($this->quote($data, $ignore)['rooms'])->map(function (array $line) use ($requested) {
            $line['guest_notes'] = $requested->firstWhere('room_id', $line['room_id'])['guest_notes'] ?? null;

            return $line;
        });
    }

    private function saveAllocations(Stay $stay, Collection $lines, Carbon $arrival, Carbon $departure): void
    {
        foreach ($lines as $line) {
            RoomAllocation::create([
                'stay_id' => $stay->id,
                'room_id' => $line['room_id'],
                'from_date' => $arrival->toDateString(),
                'to_date' => $departure->toDateString(),
                'rate_applied' => $line['nightly'],
                'adults' => $line['adults'],
                'children' => $line['children'],
                'guest_notes' => $line['guest_notes'] ?? null,
            ]);
        }
    }

    private function assertTransition(Stay $stay, StayStatus $to): void
    {
        if (! $stay->state->canTransitionTo($to)) {
            throw new BookingException("Stay #{$stay->id} is {$stay->state->label()} and can't be changed to {$to->label()}.");
        }
    }

    private function createCleaningTasks(Stay $stay, Collection $roomIds, User $by): void
    {
        $taskType = TaskType::where('code', 'CHECKOUT_CLEAN')->first();
        if (! $taskType) {
            return;
        }

        foreach (Room::whereKey($roomIds)->get() as $room) {
            Task::create([
                'task_type_id' => $taskType->id,
                'title' => "Check-out cleaning – Room {$room->room_number}",
                'description' => "Clean after stay #{$stay->id} checked out",
                'room_id' => $room->id,
                'stay_id' => $stay->id,
                'due_date' => now()->addHour(),
                'priority' => 'urgent',
                'created_by' => $by->id,
            ]);
        }
    }
}
