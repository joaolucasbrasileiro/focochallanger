<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Hotel;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReservationPolicy
{
    public function create(User $user, Hotel $hotel): Response
    {
        return $user->hasPermissionForHotel($hotel, Permission::CreateReservations)
            ? Response::allow()
            : Response::deny('Você não tem permissão para criar reservas neste hotel.');
    }

    public function viewPayments(User $user, Reservation $reservation): Response
    {
        $hotelId = $reservation->room()->value('hotel_id');

        return $hotelId !== null
            && $user->hasPermissionForHotel($hotelId, Permission::ViewReservationPayments)
                ? Response::allow()
                : Response::deny('Você não tem permissão para consultar os pagamentos desta reserva.');
    }

    public function recordPayment(User $user, Reservation $reservation): Response
    {
        $hotelId = $reservation->room()->value('hotel_id');

        return $hotelId !== null
            && $user->hasPermissionForHotel($hotelId, Permission::ViewReservationPayments)
                ? Response::allow()
                : Response::deny('Você não tem permissão para registrar pagamentos nesta reserva.');
    }
}
