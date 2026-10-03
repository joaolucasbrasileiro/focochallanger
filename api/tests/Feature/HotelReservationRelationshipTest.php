<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\Reservation;
use App\Models\ReservationDaily;
use App\Models\ReservationGuest;
use App\Models\ReservationPayment;
use App\Models\Room;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class HotelReservationRelationshipTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_hotel_retrieves_reservations_through_its_rooms(): void
    {
        $hotel = Hotel::factory()->create();
        $room = Room::factory()->for($hotel)->create();
        $reservation = Reservation::factory()->for($room)->create([
            'check_in' => '2026-12-01',
            'check_out' => '2026-12-04',
            'total' => '300.00',
        ]);
        $guest = ReservationGuest::factory()->for($reservation)->create();
        $daily = ReservationDaily::factory()->for($reservation)->create([
            'daily_date' => '2026-12-01',
            'amount' => '100.00',
        ]);
        $payment = ReservationPayment::factory()->for($reservation)->create([
            'method_code' => '1',
            'amount' => '100.00',
        ]);

        $reservation->load(['room.hotel', 'guests', 'dailies', 'payments']);

        $this->assertSame($room->id, $reservation->room->id);
        $this->assertSame($hotel->id, $reservation->room->hotel->id);
        $this->assertTrue($hotel->reservations->contains($reservation));
        $this->assertSame('2026-12-01', $reservation->check_in->toDateString());
        $this->assertSame('300.00', $reservation->total);
        $this->assertSame($guest->id, $reservation->guests->sole()->id);
        $this->assertSame('2026-12-01', $reservation->dailies->sole()->daily_date->toDateString());
        $this->assertSame('100.00', $reservation->payments->sole()->amount);
        $this->assertSame($payment->id, $reservation->payments->sole()->id);
    }
}
