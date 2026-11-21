<?php

namespace App\Support;

use Carbon\CarbonImmutable;

/**
 * Malaysian identity card (MyKad/NRIC) numbers: 12 digits, YYMMDD-PB-###G, where the first six digits are
 * the date of birth and the last digit G is odd for men and even for women. Mirrored for the staff form in
 * resources/js/admin/nric.js.
 */
class MyKad
{
    /**
     * Digits only, so 900101-14-5678 and 900101145678 are the same number.
     */
    public static function normalize(string $number): string
    {
        return preg_replace('/[\s-]/', '', $number) ?? $number;
    }

    /**
     * The date of birth, or null when the number isn't 12 digits starting with a real date. A two-digit year
     * later than this year belongs to the previous century.
     */
    public static function birthDate(string $number, ?CarbonImmutable $today = null): ?CarbonImmutable
    {
        $digits = self::normalize($number);

        if (! preg_match('/^(\d{2})(\d{2})(\d{2})\d{6}$/', $digits, $parts)) {
            return null;
        }

        $today ??= CarbonImmutable::today();
        $year = (int) $parts[1] + 2000;
        $year = $year > $today->year ? $year - 100 : $year;

        if (! checkdate((int) $parts[2], (int) $parts[3], $year)) {
            return null;
        }

        return CarbonImmutable::create($year, (int) $parts[2], (int) $parts[3]);
    }

    /**
     * The Data Dictionary gender code the number gives (an odd last digit is male, even is female), or null
     * when it isn't a valid number.
     */
    public static function gender(string $number): ?string
    {
        if (self::birthDate($number) === null) {
            return null;
        }

        return ((int) substr(self::normalize($number), -1)) % 2 === 1 ? 'male' : 'female';
    }

    /**
     * Shown as 900101-14-5678.
     */
    public static function format(string $number): string
    {
        $digits = self::normalize($number);

        return strlen($digits) === 12 ? substr($digits, 0, 6).'-'.substr($digits, 6, 2).'-'.substr($digits, 8) : $number;
    }
}
