<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Hotel;
use App\Models\HotelMembership;
use App\Models\Reservation;
use App\Models\ReservationDaily;
use App\Models\ReservationPayment;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class HotelRevenueReportApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_view_revenue_grouped_by_daily_date_without_duplicating_reservations(): void
    {
        [$hotel] = $this->actingAsHotelRole(UserRole::Manager);
        $room = Room::factory()->for($hotel)->create();
        $partial = Reservation::factory()->for($room)->create(['total' => '300.00']);
        $paid = Reservation::factory()->for($room)->create(['total' => '200.00']);
        $overpaid = Reservation::factory()->for($room)->create(['total' => '100.00']);
        $unpaid = Reservation::factory()->for($room)->create(['total' => '100.00']);

        ReservationDaily::factory()->for($partial)->create([
            'daily_date' => '2026-01-31',
            'amount' => '100.00',
        ]);
        ReservationDaily::factory()->for($partial)->create([
            'daily_date' => '2026-02-01',
            'amount' => '200.00',
        ]);
        ReservationDaily::factory()->for($paid)->create([
            'daily_date' => '2026-02-15',
            'amount' => '200.00',
        ]);
        ReservationDaily::factory()->for($overpaid)->create([
            'daily_date' => '2026-02-20',
            'amount' => '100.00',
        ]);
        ReservationDaily::factory()->for($unpaid)->create([
            'daily_date' => '2026-02-21',
            'amount' => '100.00',
        ]);
        ReservationPayment::factory()->for($partial)->create(['amount' => '100.00']);
        ReservationPayment::factory()->for($paid)->create(['amount' => '200.00']);
        ReservationPayment::factory()->for($overpaid)->create(['amount' => '150.00']);

        $otherHotel = Hotel::factory()->create();
        $otherReservation = Reservation::factory()
            ->for(Room::factory()->for($otherHotel))
            ->create(['total' => '900.00']);
        ReservationDaily::factory()->for($otherReservation)->create([
            'daily_date' => '2026-02-10',
            'amount' => '900.00',
        ]);

        $this->getJson("/api/v1/hotels/{$hotel->id}/revenue-reports?from=2026-01-01&to=2026-02-28&group_by=month")
            ->assertOk()
            ->assertJsonPath('data.hotel.id', $hotel->id)
            ->assertJsonPath('data.period.group_by', 'month')
            ->assertJsonPath('data.summary.lodging_revenue', '700.00')
            ->assertJsonPath('data.summary.occupied_room_nights', 5)
            ->assertJsonPath('data.summary.reservations_count', 4)
            ->assertJsonPath('data.summary.average_daily_rate', '140.00')
            ->assertJsonPath('data.summary.payment_summary.expected_amount', '700.00')
            ->assertJsonPath('data.summary.payment_summary.received_amount', '450.00')
            ->assertJsonPath('data.summary.payment_summary.outstanding_amount', '300.00')
            ->assertJsonPath('data.summary.payment_summary.overpaid_amount', '50.00')
            ->assertJsonPath('data.summary.payment_summary.coverage_percentage', '64.29')
            ->assertJsonPath('data.summary.payment_summary.status_counts.unpaid', 1)
            ->assertJsonPath('data.summary.payment_summary.status_counts.partially_paid', 1)
            ->assertJsonPath('data.summary.payment_summary.status_counts.paid', 1)
            ->assertJsonPath('data.summary.payment_summary.status_counts.overpaid', 1)
            ->assertJsonCount(2, 'data.groups')
            ->assertJsonPath('data.groups.0.period', '2026-01')
            ->assertJsonPath('data.groups.0.lodging_revenue', '100.00')
            ->assertJsonPath('data.groups.0.reservations_count', 1)
            ->assertJsonPath('data.groups.1.period', '2026-02')
            ->assertJsonPath('data.groups.1.lodging_revenue', '600.00')
            ->assertJsonPath('data.groups.1.reservations_count', 4);
    }

    public function test_receptionist_cannot_view_hotel_financial_reports(): void
    {
        [$hotel] = $this->actingAsHotelRole(UserRole::Receptionist);

        $this->getJson("/api/v1/hotels/{$hotel->id}/revenue-reports?from=2026-01-01&to=2026-01-31")
            ->assertForbidden()
            ->assertJsonPath('message', 'Você não tem permissão para consultar os relatórios financeiros deste hotel.');
    }

    public function test_revenue_report_validates_period_and_grouping(): void
    {
        [$hotel] = $this->actingAsHotelRole(UserRole::Manager);

        $this->getJson("/api/v1/hotels/{$hotel->id}/revenue-reports?from=2026-02-01&to=2026-01-01&group_by=week")
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['to', 'group_by'])
            ->assertJsonPath('errors.to.0', 'A data final deve ser igual ou posterior à data inicial.')
            ->assertJsonPath('errors.group_by.0', 'O agrupamento deve ser day, month, quarter, semester ou year.');
    }

    /**
     * @return array{Hotel, User}
     */
    private function actingAsHotelRole(UserRole $role): array
    {
        $hotel = Hotel::factory()->create();
        $user = User::factory()->create();
        HotelMembership::factory()->for($hotel)->for($user)->create(['role' => $role]);
        Sanctum::actingAs($user, ['api:access']);

        return [$hotel, $user];
    }
}
