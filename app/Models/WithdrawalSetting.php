<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithdrawalSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
        'description',
    ];

    /**
     * Default default withdrawal settings configuration.
     */
    public static function defaults(): array
    {
        return [
            'is_withdraw_enabled' => '1',
            'min_withdraw_coins' => '1000',
            'max_withdraw_coins' => '100000',
            'commission_percent' => '5.00',
            'rate_coins' => '100', // e.g. 100 Coins = 10 BDT (i.e. 10 Coins = 1 BDT)
            'rate_bdt' => '10.00',
            'rate_per_bdt' => '10.00', // Calculated: rate_coins / rate_bdt
            'notice' => 'Withdrawals are processed manually via bKash, Nagad, and Rocket within 1-24 hours. A standard platform commission applies on all cash outs.',
        ];
    }

    /**
     * Get a setting value by key with optional fallback.
     */
    public static function get(string $key, $default = null)
    {
        $defaults = static::defaults();
        $fallback = $default ?? ($defaults[$key] ?? null);

        $setting = static::where('key', $key)->first();
        if ($setting) {
            return $setting->value ?? $fallback;
        }

        return $fallback;
    }

    /**
     * Set a setting value by key.
     */
    public static function set(string $key, $value, ?string $description = null): self
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => (string) $value,
                'description' => $description,
            ]
        );
    }

    /**
     * Get all withdrawal config as an associative array.
     */
    public static function getAllConfig(): array
    {
        $defaults = static::defaults();
        $dbSettings = static::pluck('value', 'key')->toArray();
        $merged = array_merge($defaults, $dbSettings);

        $rateCoins = (int) ($merged['rate_coins'] ?? 100);
        $rateBdt = (float) ($merged['rate_bdt'] ?? 10.00);
        $ratePerBdt = $rateBdt > 0 ? round($rateCoins / $rateBdt, 2) : 10.00;

        return [
            'is_withdraw_enabled' => (bool) ($merged['is_withdraw_enabled'] ?? '1'),
            'min_withdraw_coins' => (int) ($merged['min_withdraw_coins'] ?? 1000),
            'max_withdraw_coins' => (int) ($merged['max_withdraw_coins'] ?? 100000),
            'commission_percent' => (float) ($merged['commission_percent'] ?? 5.00),
            'rate_coins' => $rateCoins,
            'rate_bdt' => $rateBdt,
            'rate_per_bdt' => $ratePerBdt,
            'min_withdraw_bdt' => round(((int) ($merged['min_withdraw_coins'] ?? 1000)) / ($ratePerBdt ?: 10), 2),
            'max_withdraw_bdt' => round(((int) ($merged['max_withdraw_coins'] ?? 100000)) / ($ratePerBdt ?: 10), 2),
            'notice' => $merged['notice'] ?? '',
            'rate_text' => "{$rateCoins} Coins = ৳" . number_format($rateBdt, 2) . " BDT (1 BDT = {$ratePerBdt} Coins)",
        ];
    }
}
