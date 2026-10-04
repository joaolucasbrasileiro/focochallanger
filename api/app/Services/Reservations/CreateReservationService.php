<?php

namespace App\Services\Reservations;

use App\Exceptions\Reservations\ReservationUnavailableException;
use App\Models\Hotel;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;

class CreateReservationService
{
    public function __construct(private readonly RoomAvailabilityService $roomAvailability) {}

    /**
     * @param array{
     *     hotel_id: int,
     *     room_name: string,
     *     check_in: string,
     *     check_out: string,
     *     guests: array<int, array{first_name: string, last_name: string, phone: string}>,
     *     dailies: array<int, array{daily_date: string, amount: string|int|float}>
     * } $data
     */
    public function create(array $data): Reservation
    {
        return DB::transaction(function () use ($data): Reservation {
            $hotel = Hotel::query()->findOrFail($data['hotel_id']);
            $room = $this->roomAvailability->firstAvailableRoom(
                $hotel,
                $data['room_name'],
                $data['check_in'],
                $data['check_out'],
            );

            if ($room === null) {
                throw new ReservationUnavailableException(
                    "Não há disponibilidade para a acomodação {$data['room_name']} no período informado.",
                );
            }

            $reservation = Reservation::query()->create([
                'room_id' => $room->id,
                'check_in' => $data['check_in'],
                'check_out' => $data['check_out'],
                'total' => $this->totalFor($data['dailies']),
            ]);

            $reservation->guests()->createMany($data['guests']);
            $reservation->dailies()->createMany($data['dailies']);

            return $reservation->load(['room', 'guests', 'dailies', 'payments']);
        }, attempts: 3);
    }

    /**
     * @param  array<int, array{daily_date: string, amount: string|int|float}>  $dailies
     */
    private function totalFor(array $dailies): string
    {
        $totalInCents = array_sum(array_map(
            fn (array $daily): int => $this->amountInCents($daily['amount']),
            $dailies,
        ));

        return sprintf('%d.%02d', intdiv($totalInCents, 100), $totalInCents % 100);
    }

    private function amountInCents(string|int|float $amount): int
    {
        [$whole, $decimal] = array_pad(explode('.', (string) $amount, 2), 2, '');

        return ((int) $whole * 100) + (int) str_pad($decimal, 2, '0');
    }
}
