<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
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
            'room_id' => $this->room_id,
            'check_in' => $this->check_in?->toDateString(),
            'check_out' => $this->check_out?->toDateString(),
            'total' => $this->total,
            'room' => $this->whenLoaded('room', fn (): array => [
                'id' => $this->room->id,
                'hotel_id' => $this->room->hotel_id,
                'name' => $this->room->name,
                'capacity' => $this->room->capacity,
            ]),
            'guests' => $this->whenLoaded('guests', fn (): array => $this->guests
                ->map(fn ($guest): array => [
                    'id' => $guest->id,
                    'first_name' => $guest->first_name,
                    'last_name' => $guest->last_name,
                    'phone' => $guest->phone,
                ])
                ->all()),
            'dailies' => $this->whenLoaded('dailies', fn (): array => $this->dailies
                ->map(fn ($daily): array => [
                    'id' => $daily->id,
                    'daily_date' => $daily->daily_date->toDateString(),
                    'amount' => $daily->amount,
                ])
                ->all()),
            'payments' => $this->whenLoaded('payments', fn (): array => $this->payments
                ->map(fn ($payment): array => [
                    'id' => $payment->id,
                    'method_code' => $payment->method_code,
                    'amount' => $payment->amount,
                ])
                ->all()),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
