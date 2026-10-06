<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReservationPaymentSummaryResource;
use App\Models\Reservation;
use App\Services\Payments\ReservationFinancialSummaryService;

class ReservationPaymentController extends Controller
{
    public function index(
        Reservation $reservation,
        ReservationFinancialSummaryService $financialSummary,
    ): ReservationPaymentSummaryResource {
        $this->authorize('viewPayments', $reservation);
        $reservation->load([
            'room',
            'payments' => fn ($query) => $query->orderBy('id'),
        ]);

        return new ReservationPaymentSummaryResource([
            'reservation' => $reservation,
            'financial' => $financialSummary->forReservation($reservation),
        ]);
    }
}
