<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Hotel;
use App\Models\HotelMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class HotelUserManagementApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_list_and_update_a_hotel_user(): void
    {
        [$hotel] = $this->actingAsAdmin();

        $created = $this->postJson("/api/v1/hotels/{$hotel->id}/users", [
            'name' => 'Maria Recepção',
            'email' => 'maria@foco.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => UserRole::Receptionist->value,
        ])
            ->assertCreated()
            ->assertJsonPath('data.hotel.id', $hotel->id)
            ->assertJsonPath('data.user.email', 'maria@foco.test')
            ->assertJsonPath('data.role', UserRole::Receptionist->value);

        $userId = $created->json('data.user.id');

        $this->getJson("/api/v1/hotels/{$hotel->id}/users")
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->patchJson("/api/v1/hotels/{$hotel->id}/users/{$userId}", [
            'role' => UserRole::Manager->value,
        ])
            ->assertOk()
            ->assertJsonPath('data.role', UserRole::Manager->value);

        $this->assertDatabaseHas('hotel_memberships', [
            'hotel_id' => $hotel->id,
            'user_id' => $userId,
            'role' => UserRole::Manager->value,
        ]);
    }

    public function test_existing_user_can_have_different_roles_in_different_hotels(): void
    {
        [$firstHotel, $admin] = $this->actingAsAdmin();
        $secondHotel = Hotel::factory()->create();
        HotelMembership::factory()->for($secondHotel)->for($admin)->admin()->create();
        $employee = User::factory()->create(['email' => 'employee@foco.test']);
        HotelMembership::factory()->for($firstHotel)->for($employee)->manager()->create();

        $this->postJson("/api/v1/hotels/{$secondHotel->id}/users", [
            'email' => 'employee@foco.test',
            'role' => UserRole::Receptionist->value,
        ])
            ->assertCreated()
            ->assertJsonPath('data.role', UserRole::Receptionist->value);

        $this->assertDatabaseHas('hotel_memberships', [
            'hotel_id' => $firstHotel->id,
            'user_id' => $employee->id,
            'role' => UserRole::Manager->value,
        ]);
        $this->assertDatabaseHas('hotel_memberships', [
            'hotel_id' => $secondHotel->id,
            'user_id' => $employee->id,
            'role' => UserRole::Receptionist->value,
        ]);
    }

    public function test_manager_cannot_manage_hotel_users(): void
    {
        $hotel = Hotel::factory()->create();
        $manager = User::factory()->create();
        HotelMembership::factory()->for($hotel)->for($manager)->manager()->create();
        Sanctum::actingAs($manager, ['api:access']);

        $this->getJson("/api/v1/hotels/{$hotel->id}/users")
            ->assertForbidden()
            ->assertJsonPath('message', 'Você não tem permissão para gerenciar os usuários deste hotel.');
    }

    public function test_admin_can_assign_an_unlinked_user_to_a_hotel_as_receptionist(): void
    {
        [$hotel] = $this->actingAsAdmin();
        $user = User::factory()->create();

        $this->postJson("/api/v1/hotels/{$hotel->id}/users", [
            'email' => $user->email,
            'role' => UserRole::Receptionist->value,
        ])
            ->assertCreated()
            ->assertJsonPath('data.role', UserRole::Receptionist->value);

        $this->assertDatabaseHas('hotel_memberships', [
            'hotel_id' => $hotel->id,
            'user_id' => $user->id,
            'role' => UserRole::Receptionist->value,
        ]);
    }

    public function test_last_admin_cannot_be_demoted_or_removed(): void
    {
        [$hotel, $admin] = $this->actingAsAdmin();

        $this->patchJson("/api/v1/hotels/{$hotel->id}/users/{$admin->id}", [
            'role' => UserRole::Manager->value,
        ])
            ->assertConflict()
            ->assertJsonPath('message', 'O hotel deve possuir pelo menos um administrador.');

        $this->deleteJson("/api/v1/hotels/{$hotel->id}/users/{$admin->id}")
            ->assertConflict()
            ->assertJsonPath('message', 'O hotel deve possuir pelo menos um administrador.');

        $this->assertDatabaseHas('hotel_memberships', [
            'hotel_id' => $hotel->id,
            'user_id' => $admin->id,
            'role' => UserRole::Admin->value,
        ]);
    }

    /**
     * @return array{Hotel, User}
     */
    private function actingAsAdmin(): array
    {
        $hotel = Hotel::factory()->create();
        $admin = User::factory()->create();
        HotelMembership::factory()->for($hotel)->for($admin)->admin()->create();
        Sanctum::actingAs($admin, ['api:access']);

        return [$hotel, $admin];
    }
}
