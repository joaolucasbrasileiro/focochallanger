<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payments\StoreReservationPaymentRequest;
use App\Http\Resources\ReservationPaymentSummaryResource;
use App\Models\Reservation;
use App\Services\Payments\ReservationFinancialSummaryService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

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

    public function store(
        StoreReservationPaymentRequest $request,
        Reservation $reservation,
        ReservationFinancialSummaryService $financialSummary,
    ): JsonResponse {
        $this->authorize('recordPayment', $reservation);

        $reservation->payments()->create($request->validated());
        $reservation->load([
            'room',
            'payments' => fn ($query) => $query->orderBy('id'),
        ]);

        return (new ReservationPaymentSummaryResource([
            'reservation' => $reservation,
            'financial' => $financialSummary->forReservation($reservation),
        ]))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
