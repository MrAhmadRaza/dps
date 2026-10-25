<?php

namespace App\Support;

/**
 * Formats / parses amounts for the 1LINK API.
 *
 * plain  : "27500" or "27500.50"  (BOP sample document)
 * padded : "+0000002750000"       (standard 1LINK: sign + 13 digits, last two digits are paisa)
 */
class OneLinkAmount
{
    public static function format(float $amount): string
    {
        $amount = round($amount, 2);

        if (config('onelink.amount_format') === 'padded') {
            return '+' . str_pad((string) (int) round($amount * 100), 13, '0', STR_PAD_LEFT);
        }

        return self::plain($amount);
    }

    public static function plain(float $amount): string
    {
        $amount = round($amount, 2);

        return fmod($amount, 1.0) == 0.0
            ? number_format($amount, 0, '.', '')
            : number_format($amount, 2, '.', '');
    }

    /**
     * Accepts either format. Returns null when the value is not a valid amount.
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
