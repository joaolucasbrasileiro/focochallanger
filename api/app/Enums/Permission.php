<?php

namespace App\Enums;

enum Permission: string
{
    case ManageUsers = 'users.manage';
    case ViewRooms = 'rooms.view';
    case ManageRooms = 'rooms.manage';
    case ViewReservations = 'reservations.view';
    case CreateReservations = 'reservations.create';
    case ViewReservationPayments = 'reservation_payments.view';
    case ViewFinancialReports = 'financial_reports.view';
    case ViewImports = 'imports.view';
}
