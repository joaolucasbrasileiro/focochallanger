<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class HotelPolicy
{
    public function manageUsers(User $user, Hotel $hotel): Response
    {
        return $user->hasPermissionForHotel($hotel, Permission::ManageUsers)
            ? Response::allow()
            : Response::deny('Você não tem permissão para gerenciar os usuários deste hotel.');
    }

    public function viewFinancialReports(User $user, Hotel $hotel): Response
    {
        return $user->hasPermissionForHotel($hotel, Permission::ViewFinancialReports)
            ? Response::allow()
            : Response::deny('Você não tem permissão para consultar os relatórios financeiros deste hotel.');
    }
}
