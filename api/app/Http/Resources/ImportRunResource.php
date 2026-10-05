<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImportRunResource extends JsonResource
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
            'status' => $this->status,
            'started_at' => $this->started_at?->toISOString(),
            'finished_at' => $this->finished_at?->toISOString(),
            'hotels_imported' => $this->hotels_imported,
            'rooms_imported' => $this->rooms_imported,
            'reservations_imported' => $this->reservations_imported,
            'issues_count' => $this->whenCounted('issues'),
            'reservation_issues_count' => $this->when(
                isset($this->reservation_issues_count),
                $this->reservation_issues_count,
            ),
            'error_message' => $this->error_message,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
