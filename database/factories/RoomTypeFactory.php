<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RoomType>
 */
class RoomTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Standard', 'Deluxe', 'Executive', 'Suite']) . ' ' . fake()->unique()->numberBetween(1, 9999),
            'code' => strtoupper(fake()->unique()->bothify('RT-####')),
            'base_rate' => fake()->randomElement([5000, 7500, 12000, 20000]),
            'capacity' => fake()->numberBetween(2, 4), // tests book 2 adults by default
            'bed_type' => fake()->randomElement(['single', 'double', 'queen', 'king', 'twin']),
            'description' => null,
            'amenities' => [],
        ];
    }
}
