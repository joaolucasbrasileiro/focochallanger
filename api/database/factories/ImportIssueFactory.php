<?php

namespace Database\Factories;

use App\Models\Hotel;
use App\Models\ImportIssue;
use App\Models\ImportRun;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ImportIssue>
 */
class ImportIssueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'import_run_id' => ImportRun::factory(),
            'source' => ImportIssue::SourceReservation,
            'external_identifier' => (string) fake()->numberBetween(1, 9_999_999),
            'status' => ImportIssue::StatusIncomplete,
            'error_code' => 'invalid_source_data',
            'error_message' => 'A reserva possui dados inválidos.',
            'raw_payload' => '<Reserve id="1" />',
            'metadata' => [
                'record_type' => 'Reserve',
                'hotel_external_id' => (string) fake()->numberBetween(1, 9_999_999),
            ],
        ];
    }

    public function forHotel(Hotel $hotel): static
    {
        return $this->state(fn (): array => [
            'metadata' => [
                'record_type' => 'Reserve',
                'hotel_external_id' => (string) $hotel->external_id,
            ],
        ]);
    }
}
