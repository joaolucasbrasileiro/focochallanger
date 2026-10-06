<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Hotel;
use App\Models\HotelMembership;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RoomApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_rooms_with_their_hotels(): void
    {
        $hotel = Hotel::factory()->create();
        $this->actingAsForHotel($hotel);
        $room = Room::factory()->for($hotel)->create([
            'name' => 'Standard',
        ]);

        $this->getJson('/api/v1/rooms')
            ->assertOk()
            ->assertJsonPath('data.0.id', $room->id)
            ->assertJsonPath('data.0.hotel.id', $hotel->id)
            ->assertJsonStructure(['data', 'links', 'meta']);
    }

    public function test_it_lists_rooms_with_pagination_and_filters(): void
    {
        $hotel = Hotel::factory()->create();
        $this->actingAsForHotel($hotel);
        $standard = Room::factory()->for($hotel)->create([
            'name' => 'Standard Casal',
            'is_active' => true,
        ]);
        Room::factory()->for($hotel)->create([
            'name' => 'Standard Inativo',
            'is_active' => false,
        ]);
        Room::factory()->for($hotel)->create([
            'name' => 'Luxo',
            'is_active' => true,
        ]);

        $this->getJson("/api/v1/rooms?hotel_id={$hotel->id}&name=standard&is_active=true&per_page=1")
            ->assertOk()
            ->assertJsonPath('data.0.id', $standard->id)
            ->assertJsonCount(1, 'data')
            ->assertJsonStructure(['data', 'links', 'meta']);
    }

    public function test_room_listing_validates_filter_parameters(): void
    {
        $hotel = Hotel::factory()->create();
        $this->actingAsForHotel($hotel);

        $this->getJson('/api/v1/rooms?per_page=101&hotel_id=999')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['per_page', 'hotel_id']);
    }

    public function test_it_creates_a_room(): void
    {
        $hotel = Hotel::factory()->create();
        $this->actingAsForHotel($hotel);

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
        $this->actingAsForHotel(Hotel::factory()->create());

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
        $this->actingAsForHotel($hotel);
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
        $this->actingAsForHotel($room->hotel);

        $this->getJson("/api/v1/rooms/{$room->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $room->id)
            ->assertJsonPath('data.external_id', $room->external_id);
    }

    public function test_it_deletes_a_room_without_reservations(): void
    {
        $room = Room::factory()->create();
        $this->actingAsForHotel($room->hotel);

        $this->deleteJson("/api/v1/rooms/{$room->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('rooms', [
            'id' => $room->id,
        ]);
    }

    public function test_it_does_not_delete_a_room_with_reservations(): void
    {
        $room = Room::factory()->create();
        $this->actingAsForHotel($room->hotel);
        Reservation::factory()->for($room)->create();

        $this->deleteJson("/api/v1/rooms/{$room->id}")
            ->assertConflict()
            ->assertJsonPath('message', 'Não é possível excluir o quarto porque existem reservas vinculadas a ele.');

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
        ]);
    }

    private function actingAsForHotel(Hotel $hotel, UserRole $role = UserRole::Manager): User
    {
        $user = User::factory()->create();
        HotelMembership::factory()->for($hotel)->for($user)->create(['role' => $role]);
        Sanctum::actingAs($user, ['api:access']);

        return $user;
    }
}
