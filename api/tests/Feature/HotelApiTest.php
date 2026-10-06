<?php

namespace Tests\Feature;

use App\Models\Hotel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HotelApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_hotels_with_pagination_and_filters(): void
    {
        $foco = Hotel::factory()->create([
            'external_id' => 10,
            'name' => 'Hotel Foco Prime',
        ]);
        Hotel::factory()->create([
            'external_id' => 20,
            'name' => 'Pousada Central',
        ]);

        $this->getJson('/api/v1/hotels?name=foco&external_id=10&per_page=1')
            ->assertOk()
            ->assertJsonPath('data.0.id', $foco->id)
            ->assertJsonCount(1, 'data')
            ->assertJsonStructure(['data', 'links', 'meta']);
    }

    public function test_hotel_listing_validates_filter_parameters(): void
    {
        $this->getJson('/api/v1/hotels?per_page=101')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['per_page']);
    }
}
