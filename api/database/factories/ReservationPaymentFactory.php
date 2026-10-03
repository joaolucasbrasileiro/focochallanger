<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\ReservationPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReservationPayment>
 */
class ReservationPaymentFactory extends Factory
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
            'method_code' => (string) fake()->numberBetween(1, 9),
            'amount' => fake()->randomFloat(2, 50, 1_000),
        ];
    }
}
