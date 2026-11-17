<?php

namespace App\Support;

/**
 * Nationalities offered on staff records: Malaysia first, then countries Malaysian employers commonly hire
 * from. Keys are ISO 3166-1 alpha-2 codes; names are English and translated with __() where shown. Add a
 * country here (and its Malay name to lang/ms.json) when it's needed.
 */
class Countries
{
    public const string MALAYSIA = 'MY';

    /**
     * @var array<string, string>
     */
    private const array NAMES = [
        'MY' => 'Malaysia',
        'SG' => 'Singapore',
        'ID' => 'Indonesia',
        'TH' => 'Thailand',
        'PH' => 'Philippines',
        'VN' => 'Vietnam',
        'MM' => 'Myanmar',
        'KH' => 'Cambodia',
        'BN' => 'Brunei',
        'BD' => 'Bangladesh',
        'NP' => 'Nepal',
        'IN' => 'India',
        'PK' => 'Pakistan',
        'LK' => 'Sri Lanka',
        'CN' => 'China',
        'HK' => 'Hong Kong',
        'TW' => 'Taiwan',
        'JP' => 'Japan',
        'KR' => 'South Korea',
        'AU' => 'Australia',
        'NZ' => 'New Zealand',
        'GB' => 'United Kingdom',
        'US' => 'United States',
        'CA' => 'Canada',
        'DE' => 'Germany',
        'FR' => 'France',
        'NL' => 'Netherlands',
    ];

    /**
     * @return list<string>
     */
    public static function codes(): array
    {
        return array_keys(self::NAMES);
    }

    /**
     * Code => translated name, Malaysia first.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return array_map(fn (string $name): string => __($name), self::NAMES);
    }

    public static function name(string $code): string
    {
        return isset(self::NAMES[$code]) ? __(self::NAMES[$code]) : $code;
    }
}
