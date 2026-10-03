<?php

namespace Database\Factories;

use App\Models\ImportRun;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ImportRun>
 */
class ImportRunFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'status' => 'completed',
            'started_at' => now()->subMinute(),
            'finished_at' => now(),
            'hotels_imported' => fake()->numberBetween(0, 100),
            'rooms_imported' => fake()->numberBetween(0, 1_000),
            'reservations_imported' => fake()->numberBetween(0, 10_000),
        ];
    }
}
