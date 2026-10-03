<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\ReservationDaily;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReservationDaily>
 */
class ReservationDailyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reservation_id' => Reservation::factory(),
            'daily_date' => fake()->date(),
            'amount' => fake()->randomFloat(2, 50, 1_000),
        ];
    }
}
