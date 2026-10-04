<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\Reservations\ReservationUnavailableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reservations\StoreReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Services\Reservations\CreateReservationService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ReservationController extends Controller
{
    public function store(StoreReservationRequest $request, CreateReservationService $reservationCreator): JsonResponse
    {
        try {
            $reservation = $reservationCreator->create($request->validated());
        } catch (ReservationUnavailableException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], Response::HTTP_CONFLICT);
        }

        return (new ReservationResource($reservation))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
