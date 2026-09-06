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
            'company_name'                => 'Chinchins Live Network Inc.',
            'official_website'            => 'https://chinchins.live',
            'support_email'               => 'support@chinchins.live',
            'support_whatsapp'            => '+8801700000000',
            'privacy_policy_updated_at'   => 'September 2026',
            'about_us'                    => "Welcome to Chinchins Live — the premier real-time interactive live video streaming, social connection, and entertainment platform.\n\nOur mission is to connect people across the globe through crystal-clear 1-on-1 private video calls, dynamic live broadcasting, interactive virtual gifting, and instant messaging.\n\n✨ Key Highlights:\n• Low-Latency HD Video Calls: Seamless WebRTC real-time audio and video conversations.\n• 160+ Animated Gifts & Leveling: Express emotions with luxury SVG and 3D animated gifts.\n• Safety & Security: 24/7 AI moderation, end-to-end encrypted transactions, and full user data control.\n• VIP Privileges: Unlock exclusive badges, entry effects, avatar frames, and personalized room styling.\n\nFor questions or business inquiries, contact us at support@chinchins.live.",
            'privacy_policy'              => "At Chinchins Live (operated by Chinchins Live Network Inc.), we are deeply committed to protecting the privacy, confidentiality, and security of our users' personal data.\n\n1. Information We Collect:\n• Account Profile: Phone number, email, display name, age (18+ only), gender, profile photo, and bio.\n• Technical & Device Data: Unique device identifier (FCM push token), OS version, and network IP address.\n• Communications: Video and audio call session logs (call duration and timestamps only; call video and audio streams are encrypted peer-to-peer and NEVER recorded on servers).\n\n2. Device Permissions:\n• Camera & Microphone: Strictly requested for live 1-on-1 video and voice conversations initiated by you.\n• Photos & Media: Only accessed when you choose to upload an avatar or gallery photo.\n\n3. Financial Security:\n• All coin purchases, VIP packages, and recharge transactions are processed via secure payment gateways with end-to-end encryption. We never store credit card numbers or banking passwords.\n\n4. Right to Delete Your Account:\n• You have the absolute right to delete your Chinchins Live account and all associated personal data at any time from Settings -> Delete Account. Upon deletion, all tokens, profile info, and sessions are immediately terminated.",
            'terms_of_service'            => "Welcome to Chinchins Live. By using our application, you agree to comply with our Terms of Service.\n\n1. Age Requirement: Users must be 18 years of age or older to register and participate in live video calls.\n2. Prohibited Content: Nudity, harassment, hate speech, spam, and illegal activities are strictly forbidden and will result in an immediate permanent ban and hardware block.\n3. Virtual Currency & Gifts: Coins, VIP passes, and virtual gifts are in-app digital entertainment items and are non-refundable.\n4. Host Code of Conduct: Hosts are required to maintain polite, professional behavior during all live and private video sessions.",
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
