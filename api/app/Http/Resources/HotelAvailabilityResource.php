<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HotelAvailabilityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'hotel' => new HotelResource($this->resource['hotel']),
            'check_in' => $this->resource['check_in'],
            'check_out' => $this->resource['check_out'],
            'guests' => $this->resource['guests'],
            'rooms' => $this->resource['rooms'],
        ];
    }
}
