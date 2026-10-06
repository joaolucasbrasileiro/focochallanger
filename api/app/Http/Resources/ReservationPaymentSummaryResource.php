<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationPaymentSummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $reservation = $this->resource['reservation'];

        return [
            'reservation' => [
                'id' => $reservation->id,
                'external_id' => $reservation->external_id,
                'hotel_id' => $reservation->room->hotel_id,
                'room_id' => $reservation->room_id,
                'check_in' => $reservation->check_in->toDateString(),
                'check_out' => $reservation->check_out->toDateString(),
            ],
            'financial' => $this->resource['financial'],
            'payments' => $reservation->payments
                ->map(fn ($payment): array => [
                    'id' => $payment->id,
                    'method_code' => $payment->method_code,
                    'amount' => $payment->amount,
                ])
                ->all(),
        ];
    }
}
