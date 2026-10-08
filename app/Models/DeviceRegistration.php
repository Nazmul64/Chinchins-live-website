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
        'firebase_app_id',
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

    public function firebaseApp(): BelongsTo
    {
        return $this->belongsTo(FirebaseApp::class, 'firebase_app_id');
    }

    /**
     * Register or update device record.
     */
    public static function registerDevice(?int $userId, string $fcmToken, array $deviceMeta = []): self
    {
        $deviceId = $deviceMeta['device_id'] ?? null;
        $firebaseAppId = $deviceMeta['firebase_app_id'] ?? null;

        // If firebase_app_id not passed, try matching by package_name or fallback to default
        if (!$firebaseAppId && !empty($deviceMeta['package_name'])) {
            $fApp = FirebaseApp::where('package_name', $deviceMeta['package_name'])->first();
            if ($fApp) {
                $firebaseAppId = $fApp->id;
            }
        }
        if (!$firebaseAppId) {
            $fApp = FirebaseApp::getDefault();
            if ($fApp) {
                $firebaseAppId = $fApp->id;
            }
        }

        // Try to find existing device by user_id, device_id, or token (to avoid duplicates)
        $device = null;
        if ($userId) {
            $device = static::where('user_id', $userId)->first();
            if ($device) {
                // Remove any obsolete duplicate rows for the same user
                static::where('user_id', $userId)->where('id', '!=', $device->id)->delete();
            }
        }
        if (!$device && $deviceId) {
            $device = static::where('device_id', $deviceId)->first();
        }
        if (!$device) {
            $device = static::where('fcm_token', $fcmToken)->first();
        }

        $attributes = [
            'user_id'         => $userId ?: ($device?->user_id ?? null),
            'firebase_app_id' => $firebaseAppId ?: ($device?->firebase_app_id ?? null),
            'fcm_token'       => $fcmToken,
            'device_id'       => $deviceId ?: ($device?->device_id ?? null),
            'device_type'     => strtolower($deviceMeta['device_type'] ?? ($device?->device_type ?? 'android')),
            'device_brand'    => $deviceMeta['device_brand'] ?? $deviceMeta['brand'] ?? ($device?->device_brand ?? null),
            'device_model'    => $deviceMeta['device_model'] ?? $deviceMeta['model'] ?? ($device?->device_model ?? null),
            'os_version'      => $deviceMeta['os_version'] ?? $deviceMeta['os'] ?? ($device?->os_version ?? null),
            'app_version'     => $deviceMeta['app_version'] ?? ($device?->app_version ?? null),
            'is_active'       => true,
            'last_active_at'  => now(),
        ];

        if ($device) {
            $device->update($attributes);
            return $device;
        }

        return static::create($attributes);
    }
}
