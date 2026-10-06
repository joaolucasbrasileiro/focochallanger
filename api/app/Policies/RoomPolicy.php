<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RoomPolicy
{
    public function viewAny(User $user): Response
    {
        return $user->hasPermissionInAnyHotel(Permission::ViewRooms)
            ? Response::allow()
            : Response::deny('Você não tem permissão para consultar quartos.');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Room $room): Response
    {
        return $this->forHotel($user, $room->hotel_id, Permission::ViewRooms);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Hotel $hotel): Response
    {
        return $this->forHotel($user, $hotel, Permission::ManageRooms);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Room $room): Response
    {
        return $this->forHotel($user, $room->hotel_id, Permission::ManageRooms);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Room $room): Response
    {
        return $this->forHotel($user, $room->hotel_id, Permission::ManageRooms);
    }

    private function forHotel(User $user, Hotel|int $hotel, Permission $permission): Response
    {
        return $user->hasPermissionForHotel($hotel, $permission)
            ? Response::allow()
            : Response::deny('Você não tem permissão para realizar esta ação neste hotel.');
    }
}
