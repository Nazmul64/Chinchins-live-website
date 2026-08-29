<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPresence extends Model
{
    use HasFactory;

    protected $table = 'user_presences';

    protected $fillable = [
        'user_id',
        'status',          // 'online', 'offline', 'inactive', 'busy', 'in_call'
        'is_online',
        'last_seen_at',
        'device_type',     // 'android', 'ios', 'web'
        'fcm_token',
        'device_token',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'is_online'    => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    /**
     * Associated user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record/update user heartbeat.
     */
    public static function heartbeat(User $user, array $meta = []): self
    {
        $now = now();
        $status = $meta['status'] ?? 'online';
        $isOnline = in_array($status, ['online', 'busy', 'in_call']);

        // Update user model directly
        $userUpdate = [
            'last_seen_at'  => $now,
            'online_status' => $status,
        ];
        if (!empty($meta['fcm_token'])) {
            $userUpdate['fcm_token'] = $meta['fcm_token'];
        }
        if (!empty($meta['device_token'])) {
            $userUpdate['device_token'] = $meta['device_token'];
        }
        if (!empty($meta['device_type'])) {
            $userUpdate['device_type'] = $meta['device_type'];
        }
        $user->update($userUpdate);

        // Record in user_presences table
        return self::updateOrCreate(
            ['user_id' => $user->id],
            [
                'status'       => $status,
                'is_online'    => $isOnline,
                'last_seen_at' => $now,
                'device_type'  => $meta['device_type'] ?? $user->device_type,
                'fcm_token'    => $meta['fcm_token'] ?? $user->fcm_token,
                'device_token' => $meta['device_token'] ?? $user->device_token,
                'ip_address'   => $meta['ip_address'] ?? request()->ip(),
                'user_agent'   => $meta['user_agent'] ?? request()->userAgent(),
            ]
        );
    }
}
