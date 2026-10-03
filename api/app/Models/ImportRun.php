<?php

namespace App\Models;

use Database\Factories\ImportRunFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'status',
    'started_at',
    'finished_at',
    'hotels_imported',
    'rooms_imported',
    'reservations_imported',
    'error_message',
])]
class ImportRun extends Model
{
    /** @use HasFactory<ImportRunFactory> */
    use HasFactory;

    public const StatusRunning = 'running';

    public const StatusCompleted = 'completed';

    public const StatusFailed = 'failed';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'hotels_imported' => 'integer',
            'rooms_imported' => 'integer',
            'reservations_imported' => 'integer',
        ];
    }
}
