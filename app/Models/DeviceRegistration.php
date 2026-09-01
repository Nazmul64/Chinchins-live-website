<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'fcm_token',
        'device_id',
        'device_type',
        'device_brand',
        'device_model',
        'os_version',
        'app_version',
        'is_active',
        'last_active_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_active_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Register or update device record.
     */
    public static function registerDevice(?int $userId, string $fcmToken, array $deviceMeta = []): self
    {
        $deviceId = $deviceMeta['device_id'] ?? null;

        // Try to find existing device by token or device_id
        $device = null;
        if ($deviceId) {
            $device = static::where('device_id', $deviceId)->first();
        }
        if (!$device) {
            $device = static::where('fcm_token', $fcmToken)->first();
        }

        $attributes = [
            'user_id'        => $userId,
            'fcm_token'      => $fcmToken,
            'device_id'      => $deviceId ?: ($device?->device_id ?? null),
            'device_type'    => strtolower($deviceMeta['device_type'] ?? 'android'),
            'device_brand'   => $deviceMeta['device_brand'] ?? $deviceMeta['brand'] ?? null,
            'device_model'   => $deviceMeta['device_model'] ?? $deviceMeta['model'] ?? null,
            'os_version'     => $deviceMeta['os_version'] ?? $deviceMeta['os'] ?? null,
            'app_version'    => $deviceMeta['app_version'] ?? null,
            'is_active'      => true,
            'last_active_at' => now(),
        ];

        if ($device) {
            $device->update($attributes);
            return $device;
        }

        return static::create($attributes);
    }
}
