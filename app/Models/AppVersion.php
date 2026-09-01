<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'version_code',
        'version_name',
        'min_supported_version',
        'force_update',
        'title',
        'changelog',
        'download_url',
        'file_size',
        'platform',
        'is_active',
        'remote_flags',
        'release_date',
    ];

    protected $casts = [
        'version_code' => 'integer',
        'force_update' => 'boolean',
        'is_active' => 'boolean',
        'remote_flags' => 'array',
        'release_date' => 'datetime',
    ];

    /**
     * Get the latest active version for given platform.
     */
    public static function getLatest(string $platform = 'android'): ?self
    {
        return static::where('is_active', true)
            ->where(function ($q) use ($platform) {
                $q->where('platform', $platform)
                  ->orWhere('platform', 'all');
            })
            ->orderBy('version_code', 'desc')
            ->first();
    }

    /**
     * Check if a client version needs an update.
     */
    public static function checkUpdate(string $currentVersion, int $currentVersionCode = 1, string $platform = 'android'): array
    {
        $latest = static::getLatest($platform);

        if (!$latest) {
            return [
                'has_update'            => false,
                'force_update'          => false,
                'latest_version'        => $currentVersion,
                'latest_version_code'   => $currentVersionCode,
                'min_supported_version' => $currentVersion,
                'title'                 => 'Up to date',
                'changelog'             => '',
                'download_url'          => null,
                'file_size'             => null,
                'remote_flags'          => static::defaultRemoteFlags(),
            ];
        }

        $hasUpdate = $latest->version_code > $currentVersionCode 
                  || version_compare($latest->version_name, $currentVersion, '>');

        // Check if current version is below minimum supported version -> Force Update
        $isBelowMin = version_compare($currentVersion, $latest->min_supported_version, '<');
        $forceUpdate = $hasUpdate && ($latest->force_update || $isBelowMin);

        return [
            'has_update'            => $hasUpdate,
            'force_update'          => (bool) $forceUpdate,
            'latest_version'        => $latest->version_name,
            'latest_version_code'   => (int) $latest->version_code,
            'min_supported_version' => $latest->min_supported_version,
            'title'                 => $latest->title ?: 'New Version Available! 🚀',
            'changelog'             => $latest->changelog ?: 'Performance improvements and bug fixes.',
            'download_url'          => $latest->download_url ?: asset('downloads/chinchins_live.apk'),
            'file_size'             => $latest->file_size ?: '25 MB',
            'remote_flags'          => array_merge(static::defaultRemoteFlags(), $latest->remote_flags ?? []),
        ];
    }

    /**
     * Default dynamic feature flags that can be controlled remotely from server.
     */
    public static function defaultRemoteFlags(): array
    {
        return [
            'enable_video_calling'        => true,
            'enable_audio_calling'        => true,
            'enable_random_matching'      => true,
            'enable_instant_call_wake'    => true,
            'enable_push_notifications'   => true,
            'enable_in_app_updates'       => true,
            'enable_profile_view_alert'   => true,
            'enable_auto_chat_greetings'  => true,
            'maintenance_mode'            => false,
            'maintenance_message'         => 'Server is currently undergoing scheduled maintenance.',
        ];
    }
}
