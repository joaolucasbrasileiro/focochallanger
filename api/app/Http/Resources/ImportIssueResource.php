<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImportIssueResource extends JsonResource
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
            'import_run_id' => $this->import_run_id,
            'source' => $this->source,
            'external_identifier' => $this->external_identifier,
            'status' => $this->status,
            'error_code' => $this->error_code,
            'error_message' => $this->error_message,
            'raw_payload' => $this->raw_payload,
            'metadata' => $this->metadata,
            'import_run' => $this->whenLoaded('importRun', fn (): array => [
                'id' => $this->importRun->id,
                'status' => $this->importRun->status,
                'started_at' => $this->importRun->started_at?->toISOString(),
            ]),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
