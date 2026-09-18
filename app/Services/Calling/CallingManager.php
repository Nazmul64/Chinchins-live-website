<?php

namespace App\Services\Calling;

use App\Models\StreamingSetting;
use App\Models\User;
use App\Services\Calling\Contracts\CallingDriverInterface;
use App\Services\Calling\Drivers\AgoraDriver;
use App\Services\Calling\Drivers\WebRTCDriver;
use Illuminate\Support\Str;

class CallingManager
{
    /**
     * Map of available calling drivers.
     */
    protected array $drivers = [];

    public function __construct()
    {
        $this->drivers = [
            'vps_webrtc' => new WebRTCDriver(),
            'webrtc'     => new WebRTCDriver(),
            'agora'      => new AgoraDriver(),
        ];
    }

    /**
     * Get active driver key ('vps_webrtc' or 'agora') based on Admin Panel setting.
     */
    public function getActiveDriverName(): string
    {
        $setting = StreamingSetting::getSettings();
        $driver = strtolower($setting->active_driver ?: 'vps_webrtc');
        return in_array($driver, ['agora']) ? 'agora' : 'vps_webrtc';
    }

    /**
     * Resolve specific or active calling driver instance.
     */
    public function getDriver(?string $driverName = null): CallingDriverInterface
    {
        $key = strtolower($driverName ?: $this->getActiveDriverName());
        return $this->drivers[$key] ?? $this->drivers['vps_webrtc'];
    }

    /**
     * Generate dynamic unique channel name for a call session.
     */
    public function generateChannelName(string $prefix = 'call'): string
    {
        return $prefix . '_' . Str::random(10) . '_' . time();
    }

    /**
     * Initialize call session with credentials.
     * Dynamic dual-engine: strictly respects the Admin Panel setting (vps_webrtc vs agora).
     */
    public function initializeSession(
        ?User $user,
        string $channelName,
        string $callType = 'video',
        string $role = 'publisher',
        array $options = [],
        ?string $overrideDriver = null
    ): array {
        // Priority:
        // 1. Explicit override passed in method call ($overrideDriver)
        // 2. Explicit options 'driver' or 'engine'
        // 3. Admin Panel configured active driver (StreamingSetting::$active_driver: 'vps_webrtc' or 'agora')
        $targetDriver = $overrideDriver 
                     ?: ($options['driver'] ?? $options['engine'] ?? null) 
                     ?: $this->getActiveDriverName();

        $driver = $this->getDriver($targetDriver);
        return $driver->initializeSession($user, $channelName, $callType, $role, $options);
    }

    /**
     * Refresh RTC token.
     */
    public function refreshToken(
        string $channelName,
        int|string $uid,
        string $role = 'publisher',
        ?string $overrideDriver = null
    ): array {
        $driver = $this->getDriver($overrideDriver);
        return $driver->refreshToken($channelName, $uid, $role);
    }
}
