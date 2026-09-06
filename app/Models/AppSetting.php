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
            'free_messages_limit'         => '5',
            'message_coin_cost'           => '5',
            'currency_symbol'             => 'BDT',
            'floating_vip_banner_enabled' => '1',
            'floating_vip_banner_title'   => 'Extra Gems',
            'floating_vip_banner_tag'     => 'Monthly Card',
            'floating_vip_banner_image'   => 'assets/images/vip/floating_extra_gems.png',
            'floating_vip_banner_action'  => 'OPEN_PREMIUM_VIP',
        ];
    }

    const CACHE_KEY = 'app_settings_dictionary_v2';
    protected static ?array $_staticSettings = null;

    /**
     * Clear app settings cache.
     */
    public static function clearCache(): void
    {
        static::$_staticSettings = null;
        \Illuminate\Support\Facades\Cache::forget(static::CACHE_KEY);
    }

    /**
     * Get all settings as key-value dictionary in 0 DB queries (cached).
     */
    public static function getAllCached(): array
    {
        if (static::$_staticSettings !== null) {
            return static::$_staticSettings;
        }

        static::$_staticSettings = \Illuminate\Support\Facades\Cache::remember(
            static::CACHE_KEY,
            3600,
            function () {
                $defaults = static::defaults();
                $dbValues = static::pluck('value', 'key')->toArray();
                return array_merge($defaults, $dbValues);
            }
        );

        return static::$_staticSettings;
    }

    /**
     * Get setting value by key in 0 DB queries.
     */
    public static function get(string $key, $default = null)
    {
        $all = static::getAllCached();
        return $all[$key] ?? $default ?? (static::defaults()[$key] ?? null);
    }

    /**
     * Set setting value by key and invalidate cache.
     */
    public static function set(string $key, $value, ?string $group = 'general', ?string $description = null): self
    {
        $record = static::updateOrCreate(
            ['key' => $key],
            [
                'value'       => $value,
                'group'       => $group ?: 'general',
                'description' => $description,
            ]
        );

        static::clearCache();
        return $record;
    }

    /**
     * Get all app config for mobile API consumption.
     */
    public static function getAppConfig(): array
    {
        $all = static::getAllCached();

        $appName = $all['app_name'] ?? 'Chinchins Live';
        $appLogo = $all['app_logo'] ?? 'assets/images/branding/logo.png';
        $appTagline = $all['app_tagline'] ?? 'Meet, Chat & Video Call Live';

        $logoUrl = asset(ltrim($appLogo, '/'));
        if (str_starts_with($appLogo, 'http://') || str_starts_with($appLogo, 'https://')) {
            $logoUrl = $appLogo;
        }

        $floatingBannerImage = $all['floating_vip_banner_image'] ?? 'assets/images/vip/floating_extra_gems.png';
        $floatingBannerImageUrl = asset(ltrim($floatingBannerImage, '/'));
        if (str_starts_with($floatingBannerImage, 'http://') || str_starts_with($floatingBannerImage, 'https://')) {
            $floatingBannerImageUrl = $floatingBannerImage;
        }

        return [
            'app_name'                    => $appName,
            'app_tagline'                 => $appTagline,
            'app_logo_url'                => $logoUrl,
            'app_icon_url'                => asset(ltrim($all['app_icon'] ?? 'assets/images/branding/icon.png', '/')),
            'app_version'                 => $all['app_version'] ?? '1.0.0',
            'free_messages_limit'         => (int) ($all['free_messages_limit'] ?? 5),
            'message_coin_cost'           => (int) ($all['message_coin_cost'] ?? 5),
            'currency_symbol'             => $all['currency_symbol'] ?? 'BDT',
            'floating_vip_banner'         => [
                'is_enabled'    => (bool) filter_var($all['floating_vip_banner_enabled'] ?? '1', FILTER_VALIDATE_BOOLEAN),
                'title'         => $all['floating_vip_banner_title'] ?? 'Extra Gems',
                'tag'           => $all['floating_vip_banner_tag'] ?? 'Monthly Card',
                'image_url'     => $floatingBannerImageUrl,
                'action_type'   => $all['floating_vip_banner_action'] ?? 'OPEN_PREMIUM_VIP',
                'target_screen' => '/premium-vip',
            ],
        ];
    }
}
