<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
        'description',
    ];

    /**
     * Default call & revenue configuration.
     */
    public static function defaults(): array
    {
        return [
            'is_call_enabled' => '1',
            'is_free_call_enabled' => '1',
            'free_call_duration_seconds' => '16', // 16s default as per UI/Audio request
            'free_calls_per_user' => '1', // 1 free trial call per new user
            'video_call_rate_per_minute' => '100', // 100 coins / min
            'audio_call_rate_per_minute' => '100', // 100 coins / min
            'host_earning_percent' => '50.00', // 50% to host/female user
            'admin_commission_percent' => '50.00', // 50% to platform revenue
            'free_message_chances' => '2', // 2 free message chances during call
            'call_recharge_teaser_text' => "Let's play baby! Recharge and call me,I want to show you 💋",
            'call_top_badge_text' => 'VIDEO NOW! Sexy Girl request video chat!',
            'call_quick_messages' => json_encode([
                'Be my girlfriend',
                "Hi , what's up babe?",
                'Can we talk privately?',
                'You look so pretty! ❤️',
            ]),
            'incoming_ringtone_url' => 'https://assets.mixkit.co/active_storage/sfx/2874/2874-preview.mp3', // default incoming ringtone
            'outgoing_ringtone_url' => 'https://assets.mixkit.co/active_storage/sfx/1359/1359-preview.mp3', // default outgoing dial tone
            'in_call_promo_coins' => '7560',
            'in_call_promo_price_bdt' => '150.00',
            'in_call_promo_original_price_bdt' => '300.00',
            'in_call_promo_teaser' => 'Girls are still eagerly waiting for your reply. Recharge and enjoy happy time with her now~',
            'in_call_promo_badge' => '50% OFF',
        ];
    }

    /**
     * Get setting value by key.
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
     * Set setting value.
     */
    public static function set(string $key, $value, ?string $description = null): self
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_array($value) ? json_encode($value) : (string) $value,
                'description' => $description,
            ]
        );
    }

    /**
     * Get all aggregated call configuration.
     */
    public static function getAllConfig(): array
    {
        $defaults = static::defaults();
        $dbSettings = static::pluck('value', 'key')->toArray();
        $merged = array_merge($defaults, $dbSettings);

        $hostPercent = (float) ($merged['host_earning_percent'] ?? 50.00);
        $adminPercent = (float) ($merged['admin_commission_percent'] ?? 50.00);
        $freeSecs = (int) ($merged['free_call_duration_seconds'] ?? 16);
        $videoRate = (int) ($merged['video_call_rate_per_minute'] ?? 1800); // 1800 coins/min as shown in screenshot
        $audioRate = (int) ($merged['audio_call_rate_per_minute'] ?? 100);
        $freeMessages = (int) ($merged['free_message_chances'] ?? 2);

        $quickMessages = $merged['call_quick_messages'] ?? $defaults['call_quick_messages'];
        if (is_string($quickMessages)) {
            $decoded = json_decode($quickMessages, true);
            $quickMessages = is_array($decoded) ? $decoded : [
                'Be my girlfriend',
                "Hi , what's up babe?",
                'Can we talk privately?',
                'You look so pretty! ❤️',
            ];
        }

        $incomingRingtone = $merged['incoming_ringtone_url'] ?? $defaults['incoming_ringtone_url'];
        if ($incomingRingtone && !str_starts_with($incomingRingtone, 'http')) {
            $incomingRingtone = asset(ltrim($incomingRingtone, '/'));
        }

        $outgoingRingtone = $merged['outgoing_ringtone_url'] ?? $defaults['outgoing_ringtone_url'];
        if ($outgoingRingtone && !str_starts_with($outgoingRingtone, 'http')) {
            $outgoingRingtone = asset(ltrim($outgoingRingtone, '/'));
        }

        $promoCoins = (int) ($merged['in_call_promo_coins'] ?? 7560);
        $promoPrice = (float) ($merged['in_call_promo_price_bdt'] ?? 150.00);
        $promoOrigPrice = (float) ($merged['in_call_promo_original_price_bdt'] ?? 300.00);

        return [
            'is_call_enabled' => (bool) ($merged['is_call_enabled'] ?? '1'),
            'is_free_call_enabled' => (bool) ($merged['is_free_call_enabled'] ?? '1'),
            'free_call_duration_seconds' => $freeSecs,
            'free_calls_per_user' => (int) ($merged['free_calls_per_user'] ?? 1),
            'free_message_chances' => $freeMessages,
            'video_call_rate_per_minute' => $videoRate,
            'audio_call_rate_per_minute' => $audioRate,
            'host_earning_percent' => $hostPercent,
            'admin_commission_percent' => $adminPercent,
            'video_host_earning_per_min' => (int) round($videoRate * ($hostPercent / 100)),
            'video_admin_revenue_per_min' => (int) round($videoRate * ($adminPercent / 100)),
            'audio_host_earning_per_min' => (int) round($audioRate * ($hostPercent / 100)),
            'audio_admin_revenue_per_min' => (int) round($audioRate * ($adminPercent / 100)),
            'call_recharge_teaser_text' => $merged['call_recharge_teaser_text'] ?? "Girls are still eagerly waiting for your reply. Recharge and enjoy happy time with her now~",
            'call_top_badge_text' => $merged['call_top_badge_text'] ?? 'Continue Video Call',
            'call_quick_messages' => $quickMessages,
            'incoming_ringtone_url' => $incomingRingtone,
            'outgoing_ringtone_url' => $outgoingRingtone,
            'in_call_recharge_offer' => [
                'preview_seconds'           => $freeSecs,
                'rate_per_minute'           => $videoRate,
                'rate_text'                 => "Continue Video Call 💎 {$videoRate}/min",
                'promo_coins'               => $promoCoins,
                'promo_price_bdt'           => $promoPrice,
                'formatted_promo_price_bdt' => 'BDT ' . number_format($promoPrice, 2),
                'promo_original_price_bdt'  => $promoOrigPrice,
                'formatted_original_price'  => 'BDT ' . number_format($promoOrigPrice, 2),
                'discount_badge'            => $merged['in_call_promo_badge'] ?? '50% OFF',
                'teaser_text'               => $merged['in_call_promo_teaser'] ?? 'Girls are still eagerly waiting for your reply. Recharge and enjoy happy time with her now~',
                'button_text'               => 'Get Coins',
            ],
        ];
    }
}
