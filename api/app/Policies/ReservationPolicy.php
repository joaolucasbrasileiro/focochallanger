<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Hotel;
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
}
