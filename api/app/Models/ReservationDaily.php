<?php

namespace App\Models;

use Database\Factories\ReservationDailyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['reservation_id', 'daily_date', 'amount'])]
class ReservationDaily extends Model
{
    /** @use HasFactory<ReservationDailyFactory> */
    use HasFactory;

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'daily_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }
}
