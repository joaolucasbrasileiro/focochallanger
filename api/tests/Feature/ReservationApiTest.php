<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_reservation_in_an_available_room(): void
    {
        $hotel = Hotel::factory()->create();
        $occupiedRoom = Room::factory()->for($hotel)->create(['name' => 'Standard']);
        $availableRoom = Room::factory()->for($hotel)->create(['name' => 'Standard']);
        Reservation::factory()->for($occupiedRoom)->create([
            'check_in' => '2026-11-10',
            'check_out' => '2026-11-12',
        ]);

        $this->postJson('/api/v1/reservations', $this->reservationPayload($hotel->id))
            ->assertCreated()
            ->assertJsonPath('data.room_id', $availableRoom->id)
            ->assertJsonPath('data.total', '500.00')
            ->assertJsonCount(1, 'data.guests')
            ->assertJsonCount(2, 'data.dailies')
            ->assertJsonCount(0, 'data.payments');

        $this->assertDatabaseHas('reservations', [
            'room_id' => $availableRoom->id,
            'check_in' => '2026-11-10',
            'check_out' => '2026-11-12',
            'total' => '500.00',
        ]);
        $this->assertDatabaseCount('reservation_guests', 1);
        $this->assertDatabaseCount('reservation_dailies', 2);
    }

    public function test_it_rejects_a_reservation_when_no_room_is_available(): void
    {
        $hotel = Hotel::factory()->create();
        $room = Room::factory()->for($hotel)->create(['name' => 'Standard']);
        Reservation::factory()->for($room)->create([
            'check_in' => '2026-11-10',
            'check_out' => '2026-11-12',
        ]);

        $this->postJson('/api/v1/reservations', $this->reservationPayload($hotel->id))
            ->assertConflict()
            ->assertJsonPath('message', 'Não há disponibilidade para a acomodação Standard no período informado.');

        $this->assertDatabaseCount('reservations', 1);
    }

    public function test_it_validates_that_dailies_cover_the_full_stay_period(): void
    {
        $hotel = Hotel::factory()->create();
        Room::factory()->for($hotel)->create(['name' => 'Standard']);
        $payload = $this->reservationPayload($hotel->id);
        $payload['dailies'] = [[
            'daily_date' => '2026-11-10',
            'amount' => 250.00,
        ]];

        $this->postJson('/api/v1/reservations', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['dailies']);

        $this->assertDatabaseCount('reservations', 0);
    }

    /**
     * @return array<string, mixed>
     */
    private function reservationPayload(int $hotelId): array
    {
        return [
            'hotel_id' => $hotelId,
            'room_name' => 'Standard',
            'check_in' => '2026-11-10',
            'check_out' => '2026-11-12',
            'guests' => [[
                'first_name' => 'Maria',
                'last_name' => 'Silva',
                'phone' => '71999999999',
            ]],
            'dailies' => [
                [
                    'daily_date' => '2026-11-10',
                    'amount' => 250.00,
                ],
                [
                    'daily_date' => '2026-11-11',
                    'amount' => 250.00,
                ],
            ],
        ];
    }
}
