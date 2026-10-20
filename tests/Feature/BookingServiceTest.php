<?php

namespace Tests\Feature;

use App\Enums\RoomStatus;
use App\Enums\StayStatus;
use App\Exceptions\BookingException;
use App\Models\Room;
use App\Models\RoomRate;
use App\Models\RoomType;
use App\Models\Stay;
use App\Models\Task;
use App\Models\TaskType;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingServiceTest extends TestCase
{
    use RefreshDatabase;

    private BookingService $bookings;
    private User $clerk;
    private User $guest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bookings = app(BookingService::class);
        $this->clerk = User::factory()->create();
        $this->guest = User::factory()->create();
    }

    private function day(int $offset): string
    {
        return today()->addDays($offset)->toDateString();
    }

    private function booking(array $rooms, int $from = 1, int $to = 4, array $extra = []): array
    {
        return array_merge([
            'guest_id' => $this->guest->id,
            'arrival_date' => $this->day($from),
            'departure_date' => $this->day($to),
            'meal_plan' => 'BB',
            'status' => 'booked',
            'rooms' => collect($rooms)->map(fn (Room $room) => ['room_id' => $room->id, 'adults' => 2, 'children' => 0])->all(),
        ], $extra);
    }

    private function room(float $baseRate = 5000, int $capacity = 2): Room
    {
        return Room::factory()->create([
            'room_type_id' => RoomType::factory()->create(['base_rate' => $baseRate, 'capacity' => $capacity])->id,
        ]);
    }

    // ---- Creating --------------------------------------------------------

    public function test_booking_saves_rooms_guests_and_the_agreed_rate(): void
    {
        $room = $this->room(5000);

        $stay = $this->bookings->create($this->booking([$room], 1, 4), $this->clerk);

        $this->assertSame(StayStatus::Booked, $stay->state);
        $this->assertSame(3, $stay->nights);
        $allocation = $stay->roomAllocations->first();
        $this->assertSame(2, $allocation->adults);               // was silently dropped before
        $this->assertEquals(5000, $allocation->rate_applied);
        $this->assertSame(15000.0, $stay->room_charges);        // 5,000 × 3 nights, not × adults
    }

    public function test_per_person_rates_are_applied_when_configured(): void
    {
        $room = $this->room();
        RoomRate::create(['room_type_id' => $room->room_type_id, 'meal_plan' => 'HB', 'basis' => 'per_person', 'rate' => 9500]);

        $data = $this->booking([$room], 1, 3, ['meal_plan' => 'HB']);
        $data['rooms'][0]['children'] = 1;

        $stay = $this->bookings->create($data, $this->clerk);

        // (2 × 9,500 + 1 child × 4,750) × 2 nights
        $this->assertEquals(23750, $stay->roomAllocations->first()->rate_applied);
        $this->assertSame(47500.0, $stay->room_charges);
    }

    public function test_a_typed_in_rate_overrides_the_price_list(): void
    {
        $room = $this->room(5000);
        $data = $this->booking([$room]);
        $data['rooms'][0]['rate'] = 4200;

        $stay = $this->bookings->create($data, $this->clerk);

        $this->assertEquals(4200, $stay->roomAllocations->first()->rate_applied);
    }

    public function test_a_room_cannot_be_double_booked(): void
    {
        $room = $this->room();
        $this->bookings->create($this->booking([$room], 1, 4), $this->clerk);

        $this->expectException(BookingException::class);
        $this->bookings->create($this->booking([$room], 3, 6), $this->clerk);
    }

    public function test_back_to_back_and_cancelled_bookings_do_not_block(): void
    {
        $room = $this->room();
        $first = $this->bookings->create($this->booking([$room], 1, 4), $this->clerk);

        $this->bookings->create($this->booking([$room], 4, 6), $this->clerk); // arrives the day the first leaves

        $this->bookings->cancel($first, $this->clerk, 'Changed plans');
        $this->bookings->create($this->booking([$room], 1, 3), $this->clerk); // cancelled nights are free again

        $this->assertSame(3, Stay::count());
    }

    public function test_invalid_bookings_are_rejected(): void
    {
        $room = $this->room(capacity: 2);

        foreach ([
            'past arrival' => $this->booking([$room], -1, 2),
            'no nights' => $this->booking([$room], 2, 2),
            'too many adults' => array_replace_recursive($this->booking([$room]), ['rooms' => [['adults' => 3]]]),
            'same room twice' => $this->booking([$room, $room]),
            'no rooms' => $this->booking([]),
        ] as $case => $data) {
            try {
                $this->bookings->create($data, $this->clerk);
                $this->fail("Expected '{$case}' to be rejected");
            } catch (BookingException) {
                $this->addToAssertionCount(1);
            }
        }

        $this->assertSame(0, Stay::count());
    }

    public function test_walk_in_is_booked_and_checked_in_at_once(): void
    {
        $room = $this->room();

        $stay = $this->bookings->create($this->booking([$room], 0, 2, ['status' => 'checked_in']), $this->clerk);

        $this->assertSame(StayStatus::CheckedIn, $stay->state);
        $this->assertNotNull($stay->check_in);
        $this->assertSame(RoomStatus::Occupied, $room->fresh()->status);
    }

    // ---- Changing --------------------------------------------------------

    public function test_changing_dates_keeps_the_agreed_rate(): void
    {
        $room = $this->room(5000);
        $data = $this->booking([$room], 1, 3);
        $data['rooms'][0]['rate'] = 4000;
        $stay = $this->bookings->create($data, $this->clerk);

        $stay = $this->bookings->update($stay, $this->booking([$room], 2, 5), $this->clerk);

        $this->assertEquals(4000, $stay->roomAllocations->first()->rate_applied); // not re-priced to 5,000
        $this->assertSame(12000.0, $stay->room_charges);
    }

    public function test_changing_meal_plan_reprices(): void
    {
        $room = $this->room(5000);
        RoomRate::create(['room_type_id' => $room->room_type_id, 'meal_plan' => 'HB', 'rate' => 6500]);
        $stay = $this->bookings->create($this->booking([$room], 1, 3), $this->clerk);

        $stay = $this->bookings->update($stay, $this->booking([$room], 1, 3, ['meal_plan' => 'HB']), $this->clerk);

        $this->assertEquals(6500, $stay->roomAllocations->first()->rate_applied);
    }

    public function test_a_change_cannot_overlap_another_booking(): void
    {
        $room = $this->room();
        $mine = $this->bookings->create($this->booking([$room], 1, 3), $this->clerk);
        $this->bookings->create($this->booking([$room], 5, 7), $this->clerk);

        $this->expectException(BookingException::class);
        $this->bookings->update($mine, $this->booking([$room], 1, 6), $this->clerk);
    }

    // ---- Check in / out --------------------------------------------------

    public function test_cannot_check_in_before_arrival_date(): void
    {
        $stay = $this->bookings->create($this->booking([$this->room()], 2, 4), $this->clerk);

        $this->expectException(BookingException::class);
        $this->bookings->checkIn($stay, $this->clerk);
    }

    public function test_cannot_check_in_to_a_room_someone_still_occupies(): void
    {
        $room = $this->room();
        // Previous guest overstayed: due out yesterday but still checked in
        $old = $this->bookings->create($this->booking([$room], 0, 1), $this->clerk);
        $this->bookings->checkIn($old, $this->clerk);
        $old->update(['departure_date' => $this->day(-1)]);
        $old->roomAllocations()->update(['from_date' => $this->day(-3), 'to_date' => $this->day(1)]);

        $new = Stay::create(['guest_id' => $this->guest->id, 'status' => 'booked', 'arrival_date' => $this->day(0), 'departure_date' => $this->day(2), 'created_by' => $this->clerk->id]);
        $new->roomAllocations()->create(['room_id' => $room->id, 'from_date' => $this->day(0), 'to_date' => $this->day(2), 'rate_applied' => 5000, 'adults' => 1]);

        $this->expectException(BookingException::class);
        $this->bookings->checkIn($new, $this->clerk);
    }

    public function test_check_out_frees_the_room_for_cleaning(): void
    {
        TaskType::create(['name' => 'Checkout clean', 'code' => 'CHECKOUT_CLEAN', 'category' => 'cleaning']);
        $room = $this->room();
        $stay = $this->bookings->create($this->booking([$room], 0, 1, ['status' => 'checked_in']), $this->clerk);
        $this->travelTo(today()->addDay()->setTime(10, 0));

        $stay = $this->bookings->checkOut($stay, $this->clerk);

        $this->assertSame(StayStatus::CheckedOut, $stay->state);
        $this->assertSame(RoomStatus::Cleaning, $room->fresh()->status);
        $this->assertSame(1, Task::where('room_id', $room->id)->where('stay_id', $stay->id)->count());
    }

    public function test_leaving_early_shortens_the_stay_and_frees_the_remaining_nights(): void
    {
        $room = $this->room(5000);
        $stay = $this->bookings->create($this->booking([$room], 0, 5, ['status' => 'checked_in']), $this->clerk);
        $this->travelTo(today()->addDays(2)->setTime(9, 0));

        $stay = $this->bookings->checkOut($stay, $this->clerk);

        $this->assertSame(2, $stay->nights);
        $this->assertSame(10000.0, $stay->room_charges);
        $this->assertTrue($room->fresh()->isAvailableForDates(today(), today()->addDays(3)));
    }

    public function test_in_house_guest_can_extend_only_if_the_room_is_free(): void
    {
        $room = $this->room();
        $stay = $this->bookings->create($this->booking([$room], 0, 2, ['status' => 'checked_in']), $this->clerk);

        $stay = $this->bookings->update($stay, ['departure_date' => $this->day(4)], $this->clerk);
        $this->assertSame(4, $stay->nights);

        $this->bookings->create($this->booking([$room], 4, 6), $this->clerk);

        $this->expectException(BookingException::class);
        $this->bookings->update($stay, ['departure_date' => $this->day(5)], $this->clerk);
    }

    // ---- Status rules ----------------------------------------------------

    public function test_status_rules_are_enforced(): void
    {
        $stay = $this->bookings->create($this->booking([$this->room()], 0, 2, ['status' => 'checked_in']), $this->clerk);

        foreach (['cancel', 'checkIn'] as $action) {
            try {
                $this->bookings->{$action}($stay, $this->clerk);
                $this->fail("{$action} should not be allowed for a checked-in stay");
            } catch (BookingException) {
                $this->addToAssertionCount(1);
            }
        }

        $stay = $this->bookings->checkOut($stay, $this->clerk);

        $this->expectException(BookingException::class);
        $this->bookings->update($stay, $this->booking([$this->room()]), $this->clerk);
    }
}
