<?php

namespace App\Services;

class CountryService
{
    /**
     * Master list of worldwide countries and territories with ISO codes, emoji flags, and international dial codes.
     */
    protected static ?array $countries = null;

    /**
     * Get all worldwide countries.
     *
     * @return array<int, array{name: string, code: string, iso3: string, flag: string, dial_code: string}>
     */
    public static function all(): array
    {
        if (static::$countries !== null) {
            return static::$countries;
        }

        static::$countries = [
            // South Asia & Priority Countries
            ['name' => 'Bangladesh', 'code' => 'BD', 'iso3' => 'BGD', 'flag' => '🇧🇩', 'dial_code' => '+880'],
            ['name' => 'Pakistan', 'code' => 'PK', 'iso3' => 'PAK', 'flag' => '🇵🇰', 'dial_code' => '+92'],
            ['name' => 'India', 'code' => 'IN', 'iso3' => 'IND', 'flag' => '🇮🇳', 'dial_code' => '+91'],
            ['name' => 'Nepal', 'code' => 'NP', 'iso3' => 'NPL', 'flag' => '🇳🇵', 'dial_code' => '+977'],
            ['name' => 'Philippines', 'code' => 'PH', 'iso3' => 'PHL', 'flag' => '🇵🇭', 'dial_code' => '+63'],
            ['name' => 'Bhutan', 'code' => 'BT', 'iso3' => 'BTN', 'flag' => '🇧🇹', 'dial_code' => '+975'],
            ['name' => 'Malaysia', 'code' => 'MY', 'iso3' => 'MYS', 'flag' => '🇲🇾', 'dial_code' => '+60'],
            ['name' => 'Saudi Arabia', 'code' => 'SA', 'iso3' => 'SAU', 'flag' => '🇸🇦', 'dial_code' => '+966'],
            ['name' => 'United Arab Emirates', 'code' => 'AE', 'iso3' => 'ARE', 'flag' => '🇦🇪', 'dial_code' => '+971'],
            ['name' => 'Afghanistan', 'code' => 'AF', 'iso3' => 'AFG', 'flag' => '🇦🇫', 'dial_code' => '+93'],
            ['name' => 'Indonesia', 'code' => 'ID', 'iso3' => 'IDN', 'flag' => '🇮🇩', 'dial_code' => '+62'],
            ['name' => 'Singapore', 'code' => 'SG', 'iso3' => 'SGP', 'flag' => '🇸🇬', 'dial_code' => '+65'],
            ['name' => 'Thailand', 'code' => 'TH', 'iso3' => 'THA', 'flag' => '🇹🇭', 'dial_code' => '+66'],
            ['name' => 'Vietnam', 'code' => 'VN', 'iso3' => 'VNM', 'flag' => '🇻🇳', 'dial_code' => '+84'],
            ['name' => 'Sri Lanka', 'code' => 'LK', 'iso3' => 'LKA', 'flag' => '🇱🇰', 'dial_code' => '+94'],
            ['name' => 'Maldives', 'code' => 'MV', 'iso3' => 'MDV', 'flag' => '🇲🇻', 'dial_code' => '+960'],
            ['name' => 'Myanmar', 'code' => 'MM', 'iso3' => 'MMR', 'flag' => '🇲🇲', 'dial_code' => '+95'],
            ['name' => 'Cambodia', 'code' => 'KH', 'iso3' => 'KHM', 'flag' => '🇰🇭', 'dial_code' => '+855'],
            ['name' => 'Laos', 'code' => 'LA', 'iso3' => 'LAO', 'flag' => '🇱🇦', 'dial_code' => '+856'],
            ['name' => 'Brunei', 'code' => 'BN', 'iso3' => 'BRN', 'flag' => '🇧🇳', 'dial_code' => '+673'],

            // Middle East
            ['name' => 'Qatar', 'code' => 'QA', 'iso3' => 'QAT', 'flag' => '🇶🇦', 'dial_code' => '+974'],
            ['name' => 'Kuwait', 'code' => 'KW', 'iso3' => 'KWT', 'flag' => '🇰🇼', 'dial_code' => '+965'],
            ['name' => 'Oman', 'code' => 'OM', 'iso3' => 'OMN', 'flag' => '🇴🇲', 'dial_code' => '+968'],
            ['name' => 'Bahrain', 'code' => 'BH', 'iso3' => 'BHR', 'flag' => '🇧🇭', 'dial_code' => '+973'],
            ['name' => 'Turkey', 'code' => 'TR', 'iso3' => 'TUR', 'flag' => '🇹🇷', 'dial_code' => '+90'],
            ['name' => 'Jordan', 'code' => 'JO', 'iso3' => 'JOR', 'flag' => '🇯🇴', 'dial_code' => '+962'],
            ['name' => 'Lebanon', 'code' => 'LB', 'iso3' => 'LBN', 'flag' => '🇱🇧', 'dial_code' => '+961'],
            ['name' => 'Iraq', 'code' => 'IQ', 'iso3' => 'IRQ', 'flag' => '🇮🇶', 'dial_code' => '+964'],
            ['name' => 'Iran', 'code' => 'IR', 'iso3' => 'IRN', 'flag' => '🇮🇷', 'dial_code' => '+98'],
            ['name' => 'Yemen', 'code' => 'YE', 'iso3' => 'YEM', 'flag' => '🇾🇪', 'dial_code' => '+967'],

            // Americas & Europe
            ['name' => 'United States', 'code' => 'US', 'iso3' => 'USA', 'flag' => '🇺🇸', 'dial_code' => '+1'],
            ['name' => 'United Kingdom', 'code' => 'GB', 'iso3' => 'GBR', 'flag' => '🇬🇧', 'dial_code' => '+44'],
            ['name' => 'Canada', 'code' => 'CA', 'iso3' => 'CAN', 'flag' => '🇨🇦', 'dial_code' => '+1'],
            ['name' => 'Australia', 'code' => 'AU', 'iso3' => 'AUS', 'flag' => '🇦🇺', 'dial_code' => '+61'],
            ['name' => 'New Zealand', 'code' => 'NZ', 'iso3' => 'NZL', 'flag' => '🇳🇿', 'dial_code' => '+64'],
            ['name' => 'Germany', 'code' => 'DE', 'iso3' => 'DEU', 'flag' => '🇩🇪', 'dial_code' => '+49'],
            ['name' => 'France', 'code' => 'FR', 'iso3' => 'FRA', 'flag' => '🇫🇷', 'dial_code' => '+33'],
            ['name' => 'Italy', 'code' => 'IT', 'iso3' => 'ITA', 'flag' => '🇮🇹', 'dial_code' => '+39'],
            ['name' => 'Spain', 'code' => 'ES', 'iso3' => 'ESP', 'flag' => '🇪🇸', 'dial_code' => '+34'],
            ['name' => 'Portugal', 'code' => 'PT', 'iso3' => 'PRT', 'flag' => '🇵🇹', 'dial_code' => '+351'],
            ['name' => 'Netherlands', 'code' => 'NL', 'iso3' => 'NLD', 'flag' => '🇳🇱', 'dial_code' => '+31'],
            ['name' => 'Belgium', 'code' => 'BE', 'iso3' => 'BEL', 'flag' => '🇧🇪', 'dial_code' => '+32'],
            ['name' => 'Switzerland', 'code' => 'CH', 'iso3' => 'CHE', 'flag' => '🇨🇭', 'dial_code' => '+41'],
            ['name' => 'Sweden', 'code' => 'SE', 'iso3' => 'SWE', 'flag' => '🇸🇪', 'dial_code' => '+46'],
            ['name' => 'Norway', 'code' => 'NO', 'iso3' => 'NOR', 'flag' => '🇳🇴', 'dial_code' => '+47'],
            ['name' => 'Denmark', 'code' => 'DK', 'iso3' => 'DNK', 'flag' => '🇩🇰', 'dial_code' => '+45'],
            ['name' => 'Finland', 'code' => 'FI', 'iso3' => 'FIN', 'flag' => '🇫🇮', 'dial_code' => '+358'],
            ['name' => 'Ireland', 'code' => 'IE', 'iso3' => 'IRL', 'flag' => '🇮🇪', 'dial_code' => '+353'],
            ['name' => 'Poland', 'code' => 'PL', 'iso3' => 'POL', 'flag' => '🇵🇱', 'dial_code' => '+48'],
            ['name' => 'Austria', 'code' => 'AT', 'iso3' => 'AUT', 'flag' => '🇦🇹', 'dial_code' => '+43'],
            ['name' => 'Greece', 'code' => 'GR', 'iso3' => 'GRC', 'flag' => '🇬🇷', 'dial_code' => '+30'],
            ['name' => 'Czech Republic', 'code' => 'CZ', 'iso3' => 'CZE', 'flag' => '🇨🇿', 'dial_code' => '+420'],
            ['name' => 'Romania', 'code' => 'RO', 'iso3' => 'ROU', 'flag' => '🇷🇴', 'dial_code' => '+40'],
            ['name' => 'Hungary', 'code' => 'HU', 'iso3' => 'HUN', 'flag' => '🇭🇺', 'dial_code' => '+36'],
            ['name' => 'Ukraine', 'code' => 'UA', 'iso3' => 'UKR', 'flag' => '🇺🇦', 'dial_code' => '+380'],
            ['name' => 'Russia', 'code' => 'RU', 'iso3' => 'RUS', 'flag' => '🇷🇺', 'dial_code' => '+7'],

            // East & Central Asia
            ['name' => 'China', 'code' => 'CN', 'iso3' => 'CHN', 'flag' => '🇨🇳', 'dial_code' => '+86'],
            ['name' => 'Japan', 'code' => 'JP', 'iso3' => 'JPN', 'flag' => '🇯🇵', 'dial_code' => '+81'],
            ['name' => 'South Korea', 'code' => 'KR', 'iso3' => 'KOR', 'flag' => '🇰🇷', 'dial_code' => '+82'],
            ['name' => 'Hong Kong', 'code' => 'HK', 'iso3' => 'HKG', 'flag' => '🇭🇰', 'dial_code' => '+852'],
            ['name' => 'Taiwan', 'code' => 'TW', 'iso3' => 'TWN', 'flag' => '🇹🇼', 'dial_code' => '+886'],
            ['name' => 'Macau', 'code' => 'MO', 'iso3' => 'MAC', 'flag' => '🇲🇴', 'dial_code' => '+853'],
            ['name' => 'Mongolia', 'code' => 'MN', 'iso3' => 'MNG', 'flag' => '🇲🇳', 'dial_code' => '+976'],
            ['name' => 'Kazakhstan', 'code' => 'KZ', 'iso3' => 'KAZ', 'flag' => '🇰🇿', 'dial_code' => '+7'],
            ['name' => 'Uzbekistan', 'code' => 'UZ', 'iso3' => 'UZB', 'flag' => '🇺🇿', 'dial_code' => '+998'],
            ['name' => 'Kyrgyzstan', 'code' => 'KG', 'iso3' => 'KGZ', 'flag' => '🇰🇬', 'dial_code' => '+996'],
            ['name' => 'Tajikistan', 'code' => 'TJ', 'iso3' => 'TJK', 'flag' => '🇹🇯', 'dial_code' => '+992'],
            ['name' => 'Turkmenistan', 'code' => 'TM', 'iso3' => 'TKM', 'flag' => '🇹🇲', 'dial_code' => '+993'],
            ['name' => 'Azerbaijan', 'code' => 'AZ', 'iso3' => 'AZE', 'flag' => '🇦🇿', 'dial_code' => '+994'],
            ['name' => 'Georgia', 'code' => 'GE', 'iso3' => 'GEO', 'flag' => '🇬🇪', 'dial_code' => '+995'],
            ['name' => 'Armenia', 'code' => 'AM', 'iso3' => 'ARM', 'flag' => '🇦🇲', 'dial_code' => '+374'],

            // Latin America
            ['name' => 'Brazil', 'code' => 'BR', 'iso3' => 'BRA', 'flag' => '🇧🇷', 'dial_code' => '+55'],
            ['name' => 'Mexico', 'code' => 'MX', 'iso3' => 'MEX', 'flag' => '🇲🇽', 'dial_code' => '+52'],
            ['name' => 'Argentina', 'code' => 'AR', 'iso3' => 'ARG', 'flag' => '🇦🇷', 'dial_code' => '+54'],
            ['name' => 'Colombia', 'code' => 'CO', 'iso3' => 'COL', 'flag' => '🇨🇴', 'dial_code' => '+57'],
            ['name' => 'Chile', 'code' => 'CL', 'iso3' => 'CHL', 'flag' => '🇨🇱', 'dial_code' => '+56'],
            ['name' => 'Peru', 'code' => 'PE', 'iso3' => 'PER', 'flag' => '🇵🇪', 'dial_code' => '+51'],
            ['name' => 'Venezuela', 'code' => 'VE', 'iso3' => 'VEN', 'flag' => '🇻🇪', 'dial_code' => '+58'],
            ['name' => 'Ecuador', 'code' => 'EC', 'iso3' => 'ECU', 'flag' => '🇪🇨', 'dial_code' => '+593'],

            // Africa
            ['name' => 'Egypt', 'code' => 'EG', 'iso3' => 'EGY', 'flag' => '🇪🇬', 'dial_code' => '+20'],
            ['name' => 'Nigeria', 'code' => 'NG', 'iso3' => 'NGA', 'flag' => '🇳🇬', 'dial_code' => '+234'],
            ['name' => 'South Africa', 'code' => 'ZA', 'iso3' => 'ZAF', 'flag' => '🇿🇦', 'dial_code' => '+27'],
            ['name' => 'Kenya', 'code' => 'KE', 'iso3' => 'KEN', 'flag' => '🇰🇪', 'dial_code' => '+254'],
            ['name' => 'Morocco', 'code' => 'MA', 'iso3' => 'MAR', 'flag' => '🇲🇦', 'dial_code' => '+212'],
            ['name' => 'Algeria', 'code' => 'DZ', 'iso3' => 'DZA', 'flag' => '🇩🇿', 'dial_code' => '+213'],
            ['name' => 'Tunisia', 'code' => 'TN', 'iso3' => 'TUN', 'flag' => '🇹🇳', 'dial_code' => '+216'],
            ['name' => 'Ghana', 'code' => 'GH', 'iso3' => 'GHA', 'flag' => '🇬🇭', 'dial_code' => '+233'],
            ['name' => 'Ethiopia', 'code' => 'ET', 'iso3' => 'ETH', 'flag' => '🇪🇹', 'dial_code' => '+251'],
            ['name' => 'Uganda', 'code' => 'UG', 'iso3' => 'UGA', 'flag' => '🇺🇬', 'dial_code' => '+256'],
            ['name' => 'Tanzania', 'code' => 'TZ', 'iso3' => 'TZA', 'flag' => '🇹🇿', 'dial_code' => '+255'],
        ];

        return static::$countries;
    }

    /**
     * Resolve 2-letter ISO Country Code from country name, code, or iso3 string.
     */
    public static function toIso(?string $country): string
    {
        if (empty($country)) {
            return 'BD';
        }

        $c = trim($country);
        $upper = strtoupper($c);

        if (strlen($upper) === 2 && ctype_alpha($upper)) {
            return $upper;
        }

        // Check common aliases
        $aliases = [
            'USA'                  => 'US',
            'UK'                   => 'GB',
            'ENGLAND'              => 'GB',
            'UAE'                  => 'AE',
            'DUBAI'                => 'AE',
            'ABU DHABI'            => 'AE',
            'KOREA'                => 'KR',
            'SOUTH KOREA'          => 'KR',
            'RUSSIA'               => 'RU',
            'PHILIPPINES'          => 'PH',
            'PHILIPPINES '         => 'PH',
            'SAUDI'                => 'SA',
            'SAUDI ARABIA'         => 'SA',
        ];

        if (isset($aliases[$upper])) {
            return $aliases[$upper];
        }

        foreach (static::all() as $item) {
            if (strcasecmp($item['name'], $c) === 0 || strcasecmp($item['code'], $c) === 0 || strcasecmp($item['iso3'], $c) === 0) {
                return $item['code'];
            }
        }

        // Fuzzy search
        foreach (static::all() as $item) {
            if (stripos($item['name'], $c) !== false || stripos($c, $item['name']) !== false) {
                return $item['code'];
            }
        }

        return 'BD';
    }

    /**
     * Resolve Flag Emoji from country name or 2-letter code.
     */
    public static function toFlag(?string $country): string
    {
        $code = static::toIso($country);
        if (strlen($code) !== 2 || !ctype_alpha($code)) {
            return '🇧🇩';
        }

        $firstChar = mb_chr(ord($code[0]) - ord('A') + 0x1F1E6, 'UTF-8');
        $secondChar = mb_chr(ord($code[1]) - ord('A') + 0x1F1E6, 'UTF-8');
        return $firstChar . $secondChar;
    }

    /**
     * Resolve International Dial Code (+880, +92, +91, +977, etc.).
     */
    public static function toDialCode(?string $country): string
    {
        $code = static::toIso($country);
        foreach (static::all() as $item) {
            if ($item['code'] === $code) {
                return $item['dial_code'];
            }
        }
        return '+880';
    }

    /**
     * Find country record by name or code.
     */
    public static function find(?string $query): ?array
    {
        if (empty($query)) return null;
        $q = trim($query);

        foreach (static::all() as $item) {
            if (strcasecmp($item['name'], $q) === 0 || strcasecmp($item['code'], $q) === 0 || strcasecmp($item['iso3'], $q) === 0) {
                return $item;
            }
        }

        foreach (static::all() as $item) {
            if (stripos($item['name'], $q) !== false) {
                return $item;
            }
        }

        return null;
    }
}
