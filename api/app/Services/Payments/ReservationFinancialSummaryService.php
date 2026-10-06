<?php

namespace App\Services\Payments;

use App\Enums\ReservationFinancialStatus;
use App\Models\Reservation;
use App\Support\Money;

class ReservationFinancialSummaryService
{
    /**
     * @return array{
     *     expected_amount: string,
     *     received_amount: string,
     *     outstanding_amount: string,
     *     overpaid_amount: string,
     *     coverage_percentage: string,
     *     status: string
     * }
     */
    public function forReservation(Reservation $reservation): array
    {
        $reservation->loadMissing('payments');
        $receivedCents = $reservation->payments->sum(
            fn ($payment): int => Money::toCents($payment->amount),
        );

        return $this->forAmounts($reservation->total, Money::format($receivedCents));
    }

    /**
     * @return array{
     *     expected_amount: string,
     *     received_amount: string,
     *     outstanding_amount: string,
     *     overpaid_amount: string,
     *     coverage_percentage: string,
     *     status: string
     * }
     */
    public function forAmounts(string|int|float $expected, string|int|float $received): array
    {
        $expectedCents = Money::toCents($expected);
        $receivedCents = Money::toCents($received);
        $outstandingCents = max($expectedCents - $receivedCents, 0);
        $overpaidCents = max($receivedCents - $expectedCents, 0);

        return [
            'expected_amount' => Money::format($expectedCents),
            'received_amount' => Money::format($receivedCents),
            'outstanding_amount' => Money::format($outstandingCents),
            'overpaid_amount' => Money::format($overpaidCents),
            'coverage_percentage' => Money::percentage($receivedCents, $expectedCents),
            'status' => $this->status($expectedCents, $receivedCents)->value,
        ];
    }

    private function status(int $expectedCents, int $receivedCents): ReservationFinancialStatus
    {
        if ($receivedCents === 0) {
            return ReservationFinancialStatus::Unpaid;
        }

        if ($receivedCents < $expectedCents) {
            return ReservationFinancialStatus::PartiallyPaid;
        }

        if ($receivedCents === $expectedCents) {
            return ReservationFinancialStatus::Paid;
        }

        return ReservationFinancialStatus::Overpaid;
    }
}
