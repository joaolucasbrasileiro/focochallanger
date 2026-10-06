<?php

namespace App\Enums;

use Carbon\CarbonInterface;

enum RevenueReportGrouping: string
{
    case Day = 'day';
    case Month = 'month';
    case Quarter = 'quarter';
    case Semester = 'semester';
    case Year = 'year';

    public function periodKey(CarbonInterface $date): string
    {
        return match ($this) {
            self::Day => $date->format('Y-m-d'),
            self::Month => $date->format('Y-m'),
            self::Quarter => $date->format('Y').'-Q'.$date->quarter,
            self::Semester => $date->format('Y').'-S'.($date->month <= 6 ? '1' : '2'),
            self::Year => $date->format('Y'),
        };
    }
}
