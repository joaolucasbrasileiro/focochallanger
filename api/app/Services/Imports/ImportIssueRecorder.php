<?php

namespace App\Services\Imports;

use App\Exceptions\Imports\XmlImportException;
use App\Models\ImportIssue;
use App\Models\ImportRun;

class ImportIssueRecorder
{
    /**
     * @param  array<string, string|null>  $metadata
     */
    public function record(
        ImportRun $importRun,
        string $source,
        ?string $externalIdentifier,
        XmlImportException $exception,
        ?string $rawPayload,
        array $metadata = [],
    ): ImportIssue {
        return ImportIssue::query()->create([
            'import_run_id' => $importRun->id,
            'source' => $source,
            'external_identifier' => $externalIdentifier,
            'status' => ImportIssue::StatusIncomplete,
            'error_code' => $exception->issueCode() ?? 'invalid_source_data',
            'error_message' => $exception->getMessage(),
            'raw_payload' => $rawPayload,
            'metadata' => array_filter([
                'record_type' => 'Reserve',
                ...$metadata,
            ], fn (mixed $value): bool => $value !== null),
        ]);
    }
}
