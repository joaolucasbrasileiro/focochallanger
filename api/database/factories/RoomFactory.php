<?php

namespace Database\Factories;

use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'hotel_id' => Hotel::factory(),
            'external_id' => fake()->unique()->numberBetween(1, 9_999_999),
            'name' => 'Room '.fake()->unique()->numberBetween(1, 9_999),
            'capacity' => 1,
            'is_active' => true,
        ];
    }
}
