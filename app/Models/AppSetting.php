<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
        'description',
    ];

    /**
     * Default settings dictionary.
     */
    public static function defaults(): array
    {
        return [
            'app_name'             => 'Chinchins Live',
            'app_tagline'          => 'Meet, Chat & Video Call Live',
            'app_logo'             => 'assets/images/branding/logo.png',
            'app_icon'             => 'assets/images/branding/icon.png',
            'app_version'          => '1.0.0',
            'free_messages_limit'  => '5',
            'message_coin_cost'    => '5',
            'currency_symbol'      => 'BDT',
        ];
    }

    /**
     * Get setting value by key.
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        if ($setting && $setting->value !== null) {
            return $setting->value;
        }

        $defaults = static::defaults();
        return $default ?? ($defaults[$key] ?? null);
    }

    /**
     * Set setting value by key.
     */
    public static function set(string $key, $value, ?string $group = 'general', ?string $description = null): self
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value'       => $value,
                'group'       => $group ?: 'general',
                'description' => $description,
            ]
        );
    }

    /**
     * Get all app config for mobile API consumption.
     */
    public static function getAppConfig(): array
    {
        $appName = static::get('app_name', 'Chinchins Live');
        $appLogo = static::get('app_logo', 'assets/images/branding/logo.png');
        $appTagline = static::get('app_tagline', 'Meet, Chat & Video Call Live');

        $logoUrl = asset(ltrim($appLogo, '/'));
        if (str_starts_with($appLogo, 'http://') || str_starts_with($appLogo, 'https://')) {
            $logoUrl = $appLogo;
        }

        return [
            'app_name'            => $appName,
            'app_tagline'         => $appTagline,
            'app_logo_url'        => $logoUrl,
            'app_icon_url'        => asset(ltrim(static::get('app_icon', 'assets/images/branding/icon.png'), '/')),
            'app_version'         => static::get('app_version', '1.0.0'),
            'free_messages_limit' => (int) static::get('free_messages_limit', 5),
            'message_coin_cost'   => (int) static::get('message_coin_cost', 5),
            'currency_symbol'     => static::get('currency_symbol', 'BDT'),
        ];
    }
}
