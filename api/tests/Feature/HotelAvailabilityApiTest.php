<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HotelAvailabilityApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_groups_active_rooms_and_excludes_occupied_units(): void
    {
        $hotel = Hotel::factory()->create();
        $occupiedRoom = Room::factory()->for($hotel)->create(['name' => 'Standard']);
        Room::factory()->for($hotel)->create(['name' => 'Standard']);
        Room::factory()->for($hotel)->create(['name' => 'Luxo']);
        Room::factory()->for($hotel)->create([
            'name' => 'Standard',
            'is_active' => false,
        ]);
        Reservation::factory()->for($occupiedRoom)->create([
            'check_in' => '2026-11-10',
            'check_out' => '2026-11-12',
        ]);

        $this->getJson("/api/v1/hotels/{$hotel->id}/availability?check_in=2026-11-10&check_out=2026-11-12")
            ->assertOk()
            ->assertJsonPath('data.hotel.id', $hotel->id)
            ->assertJsonPath('data.check_in', '2026-11-10')
            ->assertJsonPath('data.check_out', '2026-11-12')
            ->assertJsonFragment([
                'name' => 'Standard',
                'total_units' => 2,
                'available_units' => 1,
            ])
            ->assertJsonFragment([
                'name' => 'Luxo',
                'total_units' => 1,
                'available_units' => 1,
            ]);
    }

    public function test_it_validates_the_availability_period(): void
    {
        $hotel = Hotel::factory()->create();

        $this->getJson("/api/v1/hotels/{$hotel->id}/availability?check_in=2026-11-12&check_out=2026-11-10")
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['check_out']);
    }
}
