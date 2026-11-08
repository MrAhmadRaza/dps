<?php

namespace App\Support;

/**
 * Amount in words for printed challans and vouchers ("ten thousand two hundred").
 *
 * Plain PHP, so it works without the intl extension (NumberFormatter), which is
 * often switched off on XAMPP and shared hosting. Same wording as
 * NumberFormatter SPELLOUT in English for whole numbers; amounts are rounded to
 * whole rupees, as on the challans.
 */
class AmountInWords
{
    private const ONES = [
        'zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine',
        'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen',
        'seventeen', 'eighteen', 'nineteen',
    ];

    private const TENS = [2 => 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];

    private const SCALES = [1_000_000_000_000 => 'trillion', 1_000_000_000 => 'billion', 1_000_000 => 'million', 1_000 => 'thousand'];

    public static function english(int|float|string|null $amount): string
    {
        $n = (int) round((float) $amount);

        if ($n < 0) {
            return 'minus ' . self::english(-$n);
        }
        if ($n === 0) {
            return 'zero';
        }

        $words = [];
        foreach (self::SCALES as $size => $name) {
            if ($n >= $size) {
                $words[] = self::belowThousand(intdiv($n, $size)) . ' ' . $name;
                $n %= $size;
            }
        }
        if ($n > 0) {
            $words[] = self::belowThousand($n);
        }

        return implode(' ', $words);
    }

    private static function belowThousand(int $n): string
    {
        $words = [];
        if ($n >= 100) {
            $words[] = self::ONES[intdiv($n, 100)] . ' hundred';
            $n %= 100;
        }
        if ($n >= 20) {
            $words[] = self::TENS[intdiv($n, 10)] . ($n % 10 ? '-' . self::ONES[$n % 10] : '');
        } elseif ($n > 0) {
            $words[] = self::ONES[$n];
        }

        return implode(' ', $words);
    }
}
