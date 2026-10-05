<?php

namespace App\Models;

use Database\Factories\ImportIssueFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'import_run_id',
    'source',
    'external_identifier',
    'status',
    'error_code',
    'error_message',
    'raw_payload',
    'metadata',
])]
class ImportIssue extends Model
{
    /** @use HasFactory<ImportIssueFactory> */
    use HasFactory;

    public const SourceHotel = 'hotel';

    public const SourceRoom = 'room';

    public const SourceReservation = 'reservation';

    public const StatusIncomplete = 'incomplete';

    public const StatusResolved = 'resolved';

    public const StatusIgnored = 'ignored';

    public function importRun(): BelongsTo
    {
        return $this->belongsTo(ImportRun::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }
}
