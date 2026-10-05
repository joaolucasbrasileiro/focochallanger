<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_rooms_with_their_hotels(): void
    {
        $hotel = Hotel::factory()->create();
        $room = Room::factory()->for($hotel)->create([
            'name' => 'Standard',
        ]);

        $this->getJson('/api/v1/rooms')
            ->assertOk()
            ->assertJsonPath('data.0.id', $room->id)
            ->assertJsonPath('data.0.hotel.id', $hotel->id)
            ->assertJsonStructure(['data', 'links', 'meta']);
    }

    public function test_it_creates_a_room(): void
    {
        $hotel = Hotel::factory()->create();

        $this->postJson('/api/v1/rooms', [
            'hotel_id' => $hotel->id,
            'name' => 'Standard',
            'is_active' => true,
        ])
            ->assertCreated()
            ->assertJsonPath('data.hotel_id', $hotel->id)
            ->assertJsonPath('data.name', 'Standard')
            ->assertJsonPath('data.is_active', true);

        $this->assertDatabaseHas('rooms', [
            'hotel_id' => $hotel->id,
            'name' => 'Standard',
            'is_active' => true,
        ]);
    }

    public function test_it_validates_the_hotel_when_creating_a_room(): void
    {
        $this->postJson('/api/v1/rooms', [
            'hotel_id' => 999,
            'name' => 'Standard',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['hotel_id']);
    }

    public function test_it_updates_a_room_with_put_and_patch(): void
    {
        $hotel = Hotel::factory()->create();
        $room = Room::factory()->for($hotel)->create([
            'name' => 'Standard',
            'is_active' => true,
        ]);

        $this->putJson("/api/v1/rooms/{$room->id}", [
            'name' => 'Luxo',
        ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Luxo');

        $this->patchJson("/api/v1/rooms/{$room->id}", [
            'is_active' => false,
        ])
            ->assertOk()
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'name' => 'Luxo',
            'is_active' => false,
        ]);
    }

    public function test_it_returns_a_room_by_its_internal_identifier(): void
    {
        $room = Room::factory()->create();

        $this->getJson("/api/v1/rooms/{$room->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $room->id)
            ->assertJsonPath('data.external_id', $room->external_id);
    }

    public function test_it_deletes_a_room_without_reservations(): void
    {
        $room = Room::factory()->create();

        $this->deleteJson("/api/v1/rooms/{$room->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('rooms', [
            'id' => $room->id,
        ]);
    }

    public function test_it_does_not_delete_a_room_with_reservations(): void
    {
        $room = Room::factory()->create();
        Reservation::factory()->for($room)->create();

        $this->deleteJson("/api/v1/rooms/{$room->id}")
            ->assertConflict()
            ->assertJsonPath('message', 'Não é possível excluir o quarto porque existem reservas vinculadas a ele.');

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
        ]);
    }
}
