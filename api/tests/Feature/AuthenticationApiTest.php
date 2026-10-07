<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Hotel;
use App\Models\HotelMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_registers_without_a_hotel_membership_and_receives_a_token_without_privileged_access(): void
    {
        $register = $this->postJson('/api/v1/register', [
            'name' => '  João Silva  ',
            'email' => 'JOAO@FOCO.TEST',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
            ->assertCreated()
            ->assertJsonPath('data.user.name', 'João Silva')
            ->assertJsonPath('data.user.email', 'joao@foco.test')
            ->assertJsonCount(0, 'data.user.memberships')
            ->assertJsonPath('data.token_type', 'Bearer');

        $this->assertDatabaseHas('users', ['email' => 'joao@foco.test']);
        $this->assertDatabaseCount('hotel_memberships', 0);
        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertDatabaseHas('personal_access_tokens', ['name' => 'API Token']);

        $this->withToken($register->json('data.access_token'))
            ->getJson('/api/v1/rooms')
            ->assertForbidden()
            ->assertJsonPath('message', 'Você não tem permissão para consultar quartos.');
    }

    public function test_registration_rejects_an_existing_email(): void
    {
        User::factory()->create(['email' => 'existing@foco.test']);

        $this->postJson('/api/v1/register', [
            'name' => 'Usuário Existente',
            'email' => 'EXISTING@FOCO.TEST',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'device_name' => 'Postman',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email'])
            ->assertJsonPath('errors.email.0', 'Já existe um usuário cadastrado com este e-mail.');

        $this->assertDatabaseCount('hotel_memberships', 0);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_user_can_login_view_profile_and_revoke_current_token(): void
    {
        $hotel = Hotel::factory()->create();
        $user = User::factory()->create([
            'email' => 'admin@foco.test',
            'password' => 'password123',
        ]);
        HotelMembership::factory()->for($hotel)->for($user)->admin()->create();

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'ADMIN@FOCO.TEST',
            'password' => 'password123',
        ])
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.memberships.0.hotel.id', $hotel->id)
            ->assertJsonPath('data.user.memberships.0.role', UserRole::Admin->value)
            ->assertJsonPath('data.token_type', 'Bearer');

        $token = $login->json('data.access_token');

        $this->assertIsString($token);
        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertDatabaseHas('personal_access_tokens', ['name' => 'API Token']);

        $headers = ['Authorization' => "Bearer {$token}"];

        $this->withHeaders($headers)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'admin@foco.test');

        $this->withHeaders($headers)
            ->deleteJson('/api/v1/auth/logout')
            ->assertNoContent();

        $this->assertDatabaseCount('personal_access_tokens', 0);

        $this->app['auth']->forgetGuards();

        $this->withHeaders($headers)
            ->getJson('/api/v1/auth/me')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Não autenticado.');
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'user@foco.test',
            'password' => 'password123',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'user@foco.test',
            'password' => 'incorrect-password',
            'device_name' => 'Postman',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email'])
            ->assertJsonPath('errors.email.0', 'As credenciais informadas são inválidas.');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_protected_routes_require_authentication(): void
    {
        $this->getJson('/api/v1/rooms')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Não autenticado.');
    }

    public function test_create_admin_command_bootstraps_access_to_a_hotel(): void
    {
        $hotel = Hotel::factory()->create();

        $this->artisan('users:create-admin', [
            'hotel' => $hotel->id,
            'email' => 'owner@foco.test',
            '--name' => 'Hotel Owner',
            '--password' => 'password123',
        ])->assertExitCode(0);

        $user = User::query()->where('email', 'owner@foco.test')->sole();

        $this->assertDatabaseHas('hotel_memberships', [
            'hotel_id' => $hotel->id,
            'user_id' => $user->id,
            'role' => UserRole::Admin->value,
        ]);
    }
}
