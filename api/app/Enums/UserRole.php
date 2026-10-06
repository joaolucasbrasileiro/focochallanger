<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Manager = 'manager';
    case Receptionist = 'receptionist';

    /**
     * @return list<Permission>
     */
    public function permissions(): array
    {
        return match ($this) {
            self::Admin => Permission::cases(),
            self::Manager => [
                Permission::ViewRooms,
                Permission::ManageRooms,
                Permission::CreateReservations,
                Permission::ViewReservationPayments,
                Permission::ViewFinancialReports,
                Permission::ViewImports,
            ],
            self::Receptionist => [
                Permission::ViewRooms,
                Permission::CreateReservations,
                Permission::ViewReservationPayments,
            ],
        };
    }

    public function allows(Permission $permission): bool
    {
        return in_array($permission, $this->permissions(), true);
    }
}
