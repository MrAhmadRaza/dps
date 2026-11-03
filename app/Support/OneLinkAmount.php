<?php

namespace App\Support;

/**
 * Amounts for the 1LINK API.
 *
 * BOP: amounts are JSON numbers with whole-rupee integer values, e.g. 27500.
 * Incoming transaction_amount is also accepted as a numeric string ("27500")
 * or in the legacy 1LINK padded format ("+0000002750000", last two digits paisa).
 */
class OneLinkAmount
{
    public static function format(float $amount): int
    {
        return (int) round($amount, 0, PHP_ROUND_HALF_UP);
    }

    /**
     * Returns null when the value is not a valid amount.
     */
    public static function parse(mixed $value): ?float
    {
        if (is_int($value) || is_float($value)) {
            return round((float) $value, 2);
        }

        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        // Padded 1LINK format: optional sign + 12 or more digits, last two are paisa.
        if (preg_match('/^[+-]?\d{12,}$/', $value)) {
            return round(((int) ltrim($value, '+')) / 100, 2);
        }

        if (preg_match('/^\d+(\.\d{1,2})?$/', $value)) {
            return round((float) $value, 2);
        }

        return null;
    }

    public static function equals(float $a, float $b): bool
    {
        return abs(round($a, 2) - round($b, 2)) < 0.005;
    }
}
