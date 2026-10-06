<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HotelRevenueReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'hotel' => [
                'id' => $this->resource['hotel']->id,
                'external_id' => $this->resource['hotel']->external_id,
                'name' => $this->resource['hotel']->name,
            ],
            'period' => $this->resource['period'],
            'summary' => $this->resource['summary'],
            'groups' => $this->resource['groups'],
        ];
    }
}
