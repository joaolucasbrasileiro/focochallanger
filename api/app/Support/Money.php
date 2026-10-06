<?php

namespace App\Support;

use InvalidArgumentException;

final class Money
{
    public static function toCents(string|int|float $amount): int
    {
        $normalized = is_float($amount)
            ? number_format($amount, 2, '.', '')
            : trim((string) $amount);

        if (! preg_match('/^-?\d+(?:\.\d{1,2})?$/', $normalized)) {
            throw new InvalidArgumentException("Valor monetário inválido [{$normalized}].");
        }

        $negative = str_starts_with($normalized, '-');
        $unsigned = ltrim($normalized, '-');
        [$whole, $decimal] = array_pad(explode('.', $unsigned, 2), 2, '');
        $cents = ((int) $whole * 100) + (int) str_pad($decimal, 2, '0');

        return $negative ? -$cents : $cents;
    }

    public static function format(int $cents): string
    {
        $absolute = abs($cents);
        $formatted = sprintf('%d.%02d', intdiv($absolute, 100), $absolute % 100);

        return $cents < 0 ? '-'.$formatted : $formatted;
    }

    public static function percentage(int $numerator, int $denominator): string
    {
        if ($denominator === 0) {
            return '0.00';
        }

        return number_format(($numerator / $denominator) * 100, 2, '.', '');
    }
}
