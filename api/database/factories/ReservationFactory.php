<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $checkIn = fake()->dateTimeBetween('+1 day', '+30 days');

        return [
            'room_id' => Room::factory(),
            'external_id' => fake()->unique()->numberBetween(1, 9_999_999),
            'check_in' => $checkIn->format('Y-m-d'),
            'check_out' => (clone $checkIn)->modify('+'.fake()->numberBetween(1, 10).' days')->format('Y-m-d'),
            'total' => fake()->randomFloat(2, 100, 2_000),
        ];
    }
}
