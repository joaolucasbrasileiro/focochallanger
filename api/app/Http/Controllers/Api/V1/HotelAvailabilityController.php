<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Availability\CheckAvailabilityRequest;
use App\Http\Resources\HotelAvailabilityResource;
use App\Models\Hotel;
use App\Services\Reservations\RoomAvailabilityService;

class HotelAvailabilityController extends Controller
{
    public function show(
        CheckAvailabilityRequest $request,
        Hotel $hotel,
        RoomAvailabilityService $roomAvailability,
    ): HotelAvailabilityResource {
        $data = $request->validated();

        return new HotelAvailabilityResource([
            'hotel' => $hotel,
            'check_in' => $data['check_in'],
            'check_out' => $data['check_out'],
            'rooms' => $roomAvailability->forHotel(
                $hotel,
                $data['check_in'],
                $data['check_out'],
            ),
        ]);
    }
}
