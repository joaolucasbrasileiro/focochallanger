<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Hotel;
use App\Models\HotelMembership;
use App\Models\ImportRun;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class HotelAuthorizationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_hotel_and_availability_routes_do_not_require_authentication(): void
    {
        $hotel = Hotel::factory()->create();

        $this->getJson('/api/v1/hotels')->assertOk();

        $this->getJson("/api/v1/hotels/{$hotel->id}/availability?check_in=2026-11-10&check_out=2026-11-12")
            ->assertOk();
    }

    public function test_user_permissions_are_evaluated_for_each_hotel(): void
    {
        $managerHotel = Hotel::factory()->create();
        $receptionHotel = Hotel::factory()->create();
        $user = User::factory()->create();
        HotelMembership::factory()->for($managerHotel)->for($user)->manager()->create();
        HotelMembership::factory()->for($receptionHotel)->for($user)->create([
            'role' => UserRole::Receptionist,
        ]);
        Sanctum::actingAs($user, ['api:access']);

        $this->postJson('/api/v1/rooms', [
            'hotel_id' => $managerHotel->id,
            'name' => 'Standard',
        ])->assertCreated();

        $this->postJson('/api/v1/rooms', [
            'hotel_id' => $receptionHotel->id,
            'name' => 'Luxo',
        ])
            ->assertForbidden()
            ->assertJsonPath('message', 'Você não tem permissão para realizar esta ação neste hotel.');
    }

    public function test_room_listing_only_contains_accessible_hotels(): void
    {
        $accessibleHotel = Hotel::factory()->create();
        $unrelatedHotel = Hotel::factory()->create();
        $accessibleRoom = Room::factory()->for($accessibleHotel)->create();
        $unrelatedRoom = Room::factory()->for($unrelatedHotel)->create();
        $user = User::factory()->create();
        HotelMembership::factory()->for($accessibleHotel)->for($user)->manager()->create();
        Sanctum::actingAs($user, ['api:access']);

        $this->getJson('/api/v1/rooms')
            ->assertOk()
            ->assertJsonFragment(['id' => $accessibleRoom->id])
            ->assertJsonMissing(['id' => $unrelatedRoom->id]);
    }

    public function test_receptionist_cannot_view_import_audit_data(): void
    {
        $hotel = Hotel::factory()->create();
        $user = User::factory()->create();
        HotelMembership::factory()->for($hotel)->for($user)->create([
            'role' => UserRole::Receptionist,
        ]);
        Sanctum::actingAs($user, ['api:access']);
        ImportRun::factory()->create();

        $this->getJson('/api/v1/import-runs')
            ->assertForbidden()
            ->assertJsonPath('message', 'Você não tem permissão para consultar importações.');
    }
}
