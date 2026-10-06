<?php

namespace App\Enums;

enum ReservationFinancialStatus: string
{
    case Unpaid = 'unpaid';
    case PartiallyPaid = 'partially_paid';
    case Paid = 'paid';
    case Overpaid = 'overpaid';
}
