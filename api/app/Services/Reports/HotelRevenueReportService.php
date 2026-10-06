<?php

namespace App\Services\Reports;

use App\Enums\ReservationFinancialStatus;
use App\Enums\RevenueReportGrouping;
use App\Models\Hotel;
use App\Models\Reservation;
use App\Models\ReservationDaily;
use App\Services\Payments\ReservationFinancialSummaryService;
use App\Support\Money;
use Carbon\CarbonImmutable;

class HotelRevenueReportService
{
    public function __construct(
        private readonly ReservationFinancialSummaryService $financialSummary,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function generate(
        Hotel $hotel,
        string $from,
        string $to,
        RevenueReportGrouping $grouping,
    ): array {
        $dailies = ReservationDaily::query()
            ->select(['reservation_id', 'daily_date', 'amount'])
            ->whereBetween('daily_date', [$from, $to])
            ->whereHas(
                'reservation.room',
                fn ($query) => $query->where('hotel_id', $hotel->id),
            )
            ->orderBy('daily_date')
            ->get();
        $reservationIds = $dailies->pluck('reservation_id')->unique()->values();
        $reservations = Reservation::query()
            ->select(['id', 'total'])
            ->whereKey($reservationIds)
            ->withSum('payments', 'amount')
            ->get();
        $groups = [];
        $lodgingRevenueCents = 0;

        foreach ($dailies as $daily) {
            $period = $grouping->periodKey($daily->daily_date);
            $amountCents = Money::toCents($daily->amount);
            $lodgingRevenueCents += $amountCents;
            $groups[$period] ??= [
                'lodging_revenue_cents' => 0,
                'occupied_room_nights' => 0,
                'reservation_ids' => [],
            ];
            $groups[$period]['lodging_revenue_cents'] += $amountCents;
            $groups[$period]['occupied_room_nights']++;
            $groups[$period]['reservation_ids'][$daily->reservation_id] = true;
        }

        $expectedCents = 0;
        $receivedCents = 0;
        $outstandingCents = 0;
        $overpaidCents = 0;
        $statusCounts = array_fill_keys(array_map(
            fn (ReservationFinancialStatus $status): string => $status->value,
            ReservationFinancialStatus::cases(),
        ), 0);

        foreach ($reservations as $reservation) {
            $received = $reservation->payments_sum_amount ?? '0.00';
            $financial = $this->financialSummary->forAmounts($reservation->total, $received);
            $expectedCents += Money::toCents($financial['expected_amount']);
            $receivedCents += Money::toCents($financial['received_amount']);
            $outstandingCents += Money::toCents($financial['outstanding_amount']);
            $overpaidCents += Money::toCents($financial['overpaid_amount']);
            $statusCounts[$financial['status']]++;
        }

        return [
            'hotel' => $hotel,
            'period' => [
                'from' => CarbonImmutable::parse($from)->toDateString(),
                'to' => CarbonImmutable::parse($to)->toDateString(),
                'group_by' => $grouping->value,
            ],
            'summary' => [
                'lodging_revenue' => Money::format($lodgingRevenueCents),
                'occupied_room_nights' => $dailies->count(),
                'reservations_count' => $reservations->count(),
                'average_daily_rate' => Money::format(
                    $dailies->isEmpty() ? 0 : (int) round($lodgingRevenueCents / $dailies->count()),
                ),
                'payment_summary' => [
                    'scope' => 'reservations_with_dailies_in_period',
                    'expected_amount' => Money::format($expectedCents),
                    'received_amount' => Money::format($receivedCents),
                    'outstanding_amount' => Money::format($outstandingCents),
                    'overpaid_amount' => Money::format($overpaidCents),
                    'coverage_percentage' => Money::percentage($receivedCents, $expectedCents),
                    'status_counts' => $statusCounts,
                ],
            ],
            'groups' => collect($groups)
                ->map(fn (array $group, string $period): array => [
                    'period' => $period,
                    'lodging_revenue' => Money::format($group['lodging_revenue_cents']),
                    'occupied_room_nights' => $group['occupied_room_nights'],
                    'reservations_count' => count($group['reservation_ids']),
                    'average_daily_rate' => Money::format(
                        (int) round($group['lodging_revenue_cents'] / $group['occupied_room_nights']),
                    ),
                ])
                ->values()
                ->all(),
        ];
    }
}
