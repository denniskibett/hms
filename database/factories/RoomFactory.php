<?php

namespace Database\Factories;

use App\Enums\RoomStatus;
use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Room>
 */
class RoomFactory extends Factory
{
    public function definition(): array
    {
        return [
            'room_number' => (string) fake()->unique()->numberBetween(100, 9999),
            'room_type_id' => RoomType::factory(),
            'status' => RoomStatus::Available,
            'floor' => fake()->numberBetween(1, 6),
            'wing' => null,
            'features' => [],
            'adult_price' => 0,
            'child_price' => 0,
        ];
    }

    public function status(RoomStatus $status): static
    {
        return $this->state(['status' => $status]);
    }
}
