<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\Reservations\ReservationUnavailableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reservations\StoreReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Models\Hotel;
use App\Models\Reservation;
use App\Services\Reservations\CreateReservationService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ReservationController extends Controller
{
    public function show(Reservation $reservation): ReservationResource
    {
        $this->authorize('view', $reservation);

        return new ReservationResource($reservation->load(['room', 'guests', 'dailies', 'payments']));
    }

    public function store(StoreReservationRequest $request, CreateReservationService $reservationCreator): JsonResponse
    {
        $data = $request->validated();
        $hotel = Hotel::query()->findOrFail($data['hotel_id']);

        $this->authorize('create', [Reservation::class, $hotel]);

        try {
            $reservation = $reservationCreator->create($data);
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
