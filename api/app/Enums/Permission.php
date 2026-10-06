<?php

namespace App\Enums;

enum Permission: string
{
    case ManageUsers = 'users.manage';
    case ViewRooms = 'rooms.view';
    case ManageRooms = 'rooms.manage';
    case CreateReservations = 'reservations.create';
    case ViewImports = 'imports.view';
}
