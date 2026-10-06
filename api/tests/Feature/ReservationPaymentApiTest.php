<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Hotel;
use App\Models\HotelMembership;
use App\Models\Reservation;
use App\Models\ReservationPayment;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReservationPaymentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_receptionist_can_view_the_financial_summary_of_a_reservation(): void
    {
        $hotel = Hotel::factory()->create();
        $room = Room::factory()->for($hotel)->create();
        $reservation = Reservation::factory()->for($room)->create(['total' => '300.00']);
        ReservationPayment::factory()->for($reservation)->create([
            'method_code' => '1',
            'amount' => '100.00',
        ]);
        ReservationPayment::factory()->for($reservation)->create([
            'method_code' => '2',
            'amount' => '50.00',
        ]);
        $user = User::factory()->create();
        HotelMembership::factory()->for($hotel)->for($user)->create([
            'role' => UserRole::Receptionist,
        ]);
        Sanctum::actingAs($user, ['api:access']);

        $this->getJson("/api/v1/reservations/{$reservation->id}/payments")
            ->assertOk()
            ->assertJsonPath('data.reservation.id', $reservation->id)
            ->assertJsonPath('data.reservation.hotel_id', $hotel->id)
            ->assertJsonPath('data.financial.expected_amount', '300.00')
            ->assertJsonPath('data.financial.received_amount', '150.00')
            ->assertJsonPath('data.financial.outstanding_amount', '150.00')
            ->assertJsonPath('data.financial.overpaid_amount', '0.00')
            ->assertJsonPath('data.financial.coverage_percentage', '50.00')
            ->assertJsonPath('data.financial.status', 'partially_paid')
            ->assertJsonCount(2, 'data.payments')
            ->assertJsonPath('data.payments.0.method_code', '1');
    }

    public function test_financial_summary_identifies_an_overpaid_reservation(): void
    {
        [$reservation] = $this->reservationForAuthenticatedManager('100.00');
        ReservationPayment::factory()->for($reservation)->create(['amount' => '125.50']);

        $this->getJson("/api/v1/reservations/{$reservation->id}/payments")
            ->assertOk()
            ->assertJsonPath('data.financial.expected_amount', '100.00')
            ->assertJsonPath('data.financial.received_amount', '125.50')
            ->assertJsonPath('data.financial.outstanding_amount', '0.00')
            ->assertJsonPath('data.financial.overpaid_amount', '25.50')
            ->assertJsonPath('data.financial.coverage_percentage', '125.50')
            ->assertJsonPath('data.financial.status', 'overpaid');
    }

    public function test_user_cannot_view_payments_from_another_hotel(): void
    {
        $hotel = Hotel::factory()->create();
        $reservation = Reservation::factory()
            ->for(Room::factory()->for($hotel))
            ->create();
        $unrelatedHotel = Hotel::factory()->create();
        $user = User::factory()->create();
        HotelMembership::factory()->for($unrelatedHotel)->for($user)->manager()->create();
        Sanctum::actingAs($user, ['api:access']);

        $this->getJson("/api/v1/reservations/{$reservation->id}/payments")
            ->assertForbidden()
            ->assertJsonPath('message', 'Você não tem permissão para consultar os pagamentos desta reserva.');
    }

    /**
     * @return array{Reservation, User}
     */
    private function reservationForAuthenticatedManager(string $total): array
    {
        $hotel = Hotel::factory()->create();
        $room = Room::factory()->for($hotel)->create();
        $reservation = Reservation::factory()->for($room)->create(['total' => $total]);
        $user = User::factory()->create();
        HotelMembership::factory()->for($hotel)->for($user)->manager()->create();
        Sanctum::actingAs($user, ['api:access']);

        return [$reservation, $user];
    }
}
