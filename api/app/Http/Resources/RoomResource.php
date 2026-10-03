<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'external_id' => $this->external_id,
            'hotel_id' => $this->hotel_id,
            'name' => $this->name,
            'capacity' => $this->capacity,
            'is_active' => $this->is_active,
            'hotel' => $this->whenLoaded('hotel', fn (): array => [
                'id' => $this->hotel->id,
                'external_id' => $this->hotel->external_id,
                'name' => $this->hotel->name,
            ]),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
