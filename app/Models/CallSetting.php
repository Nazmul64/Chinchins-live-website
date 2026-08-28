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
            'free_call_duration_seconds' => '10', // 10s default, admin can change to 5, 10, 30, 60, etc.
            'free_calls_per_user' => '1', // 1 free trial call per new user
            'video_call_rate_per_minute' => '100', // 100 coins / min
            'audio_call_rate_per_minute' => '100', // 100 coins / min
            'host_earning_percent' => '50.00', // 50% to host/female user
            'admin_commission_percent' => '50.00', // 50% to platform revenue
            'incoming_ringtone_url' => 'https://assets.mixkit.co/active_storage/sfx/2874/2874-preview.mp3', // default incoming ringtone
            'outgoing_ringtone_url' => 'https://assets.mixkit.co/active_storage/sfx/1359/1359-preview.mp3', // default outgoing dial tone
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
                'value' => (string) $value,
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
        $freeSecs = (int) ($merged['free_call_duration_seconds'] ?? 10);
        $videoRate = (int) ($merged['video_call_rate_per_minute'] ?? 100);
        $audioRate = (int) ($merged['audio_call_rate_per_minute'] ?? 100);

        $incomingRingtone = $merged['incoming_ringtone_url'] ?? $defaults['incoming_ringtone_url'];
        if ($incomingRingtone && !str_starts_with($incomingRingtone, 'http')) {
            $incomingRingtone = asset(ltrim($incomingRingtone, '/'));
        }

        $outgoingRingtone = $merged['outgoing_ringtone_url'] ?? $defaults['outgoing_ringtone_url'];
        if ($outgoingRingtone && !str_starts_with($outgoingRingtone, 'http')) {
            $outgoingRingtone = asset(ltrim($outgoingRingtone, '/'));
        }

        return [
            'is_call_enabled' => (bool) ($merged['is_call_enabled'] ?? '1'),
            'is_free_call_enabled' => (bool) ($merged['is_free_call_enabled'] ?? '1'),
            'free_call_duration_seconds' => $freeSecs,
            'free_calls_per_user' => (int) ($merged['free_calls_per_user'] ?? 1),
            'video_call_rate_per_minute' => $videoRate,
            'audio_call_rate_per_minute' => $audioRate,
            'host_earning_percent' => $hostPercent,
            'admin_commission_percent' => $adminPercent,
            'video_host_earning_per_min' => (int) round($videoRate * ($hostPercent / 100)),
            'video_admin_revenue_per_min' => (int) round($videoRate * ($adminPercent / 100)),
            'audio_host_earning_per_min' => (int) round($audioRate * ($hostPercent / 100)),
            'audio_admin_revenue_per_min' => (int) round($audioRate * ($adminPercent / 100)),
            'incoming_ringtone_url' => $incomingRingtone,
            'outgoing_ringtone_url' => $outgoingRingtone,
        ];
    }
}
