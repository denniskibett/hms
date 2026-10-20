<?php

namespace Tests\Feature;

use App\Enums\MealPlan;
use App\Enums\PricingBasis;
use App\Models\Room;
use App\Models\RoomRate;
use App\Models\RoomType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomRateTest extends TestCase
{
    use RefreshDatabase;

    private function rate(RoomType $type, array $attributes): RoomRate
    {
        return RoomRate::create(array_merge([
            'room_type_id' => $type->id,
            'meal_plan' => 'BB',
            'basis' => 'per_room',
            'adults' => null,
            'child_rate' => null,
            'is_active' => true,
        ], $attributes));
    }

    public function test_without_rates_the_room_type_base_rate_is_charged_per_room(): void
    {
        // This is how every existing booking was priced
        $type = RoomType::factory()->create(['base_rate' => 7000]);

        $quote = $type->quote(MealPlan::BedAndBreakfast, adults: 2, children: 1);

        $this->assertSame(7000.0, $quote['nightly']);
        $this->assertSame(PricingBasis::PerRoom, $quote['basis']);
        $this->assertSame('room_type', $quote['source']);
    }

    public function test_city_hotel_per_room_rate_by_occupancy(): void
    {
        $type = RoomType::factory()->create(['base_rate' => 9999]);
        $this->rate($type, ['adults' => 1, 'rate' => 12000]);
        $this->rate($type, ['adults' => 2, 'rate' => 14500]);
        $this->rate($type, ['adults' => null, 'rate' => 16000]);

        $this->assertSame(12000.0, $type->quote(MealPlan::BedAndBreakfast, 1)['nightly']);
        $this->assertSame(14500.0, $type->quote(MealPlan::BedAndBreakfast, 2)['nightly']);
        $this->assertSame(16000.0, $type->quote(MealPlan::BedAndBreakfast, 3)['nightly']); // falls back to "any"
    }

    public function test_resort_per_person_sharing_with_child_percentage(): void
    {
        $type = RoomType::factory()->create();
        $this->rate($type, ['meal_plan' => 'HB', 'basis' => 'per_person', 'rate' => 9500]);

        // 2 adults × 9,500 + 1 child at the default 50% = 23,750
        $quote = $type->quote(MealPlan::HalfBoard, adults: 2, children: 1);

        $this->assertSame(23750.0, $quote['nightly']);
        $this->assertSame(PricingBasis::PerPerson, $quote['basis']);
    }

    public function test_explicit_child_rate_overrides_the_percentage(): void
    {
        $type = RoomType::factory()->create();
        $this->rate($type, ['meal_plan' => 'FB', 'basis' => 'per_person', 'rate' => 10000, 'child_rate' => 3000]);

        $this->assertSame(26000.0, $type->quote(MealPlan::FullBoard, adults: 2, children: 2)['nightly']);
    }

    public function test_meal_plans_are_priced_separately(): void
    {
        $type = RoomType::factory()->create(['base_rate' => 4000]);
        $this->rate($type, ['meal_plan' => 'BB', 'rate' => 5000]);
        $this->rate($type, ['meal_plan' => 'HB', 'rate' => 6500]);

        $this->assertSame(5000.0, $type->quote(MealPlan::BedAndBreakfast, 2)['nightly']);
        $this->assertSame(6500.0, $type->quote(MealPlan::HalfBoard, 2)['nightly']);
        // No FB rate → base rate
        $this->assertSame(4000.0, $type->quote(MealPlan::FullBoard, 2)['nightly']);
    }

    public function test_inactive_rates_are_ignored(): void
    {
        $type = RoomType::factory()->create(['base_rate' => 4000]);
        $this->rate($type, ['rate' => 5000, 'is_active' => false]);

        $this->assertSame(4000.0, $type->quote(MealPlan::BedAndBreakfast, 2)['nightly']);
    }

    public function test_room_per_person_prices_are_used_when_no_rate_exists(): void
    {
        $room = Room::factory()->create(['adult_price' => 3000, 'child_price' => 1000]);

        $quote = $room->roomType->quote(MealPlan::BedAndBreakfast, adults: 2, children: 1, room: $room);

        $this->assertSame(7000.0, $quote['nightly']);
        $this->assertSame('room', $quote['source']);
    }
}
