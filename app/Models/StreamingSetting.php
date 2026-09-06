<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class StreamingSetting extends Model
{
    use HasFactory;

    protected $table = 'streaming_settings';

    protected $fillable = [
        'active_driver',
        'agora_project_name',
        'agora_app_id',
        'agora_app_certificate',
        'enable_video_call',
        'enable_audio_call',
        'enable_live_stream',
        'reverb_host',
        'reverb_port',
        'reverb_scheme',
        'token_expire_seconds',
    ];

    protected $casts = [
        'enable_video_call'    => 'boolean',
        'enable_audio_call'    => 'boolean',
        'enable_live_stream'   => 'boolean',
        'token_expire_seconds' => 'integer',
        'reverb_port'          => 'integer',
    ];

    const CACHE_KEY = 'streaming_settings_cached_v1';

    /**
     * Clear cached settings.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Get singleton streaming setting record with caching.
     */
    public static function getSettings(): self
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            $setting = static::first();
            if (!$setting) {
                $setting = static::create([
                    'active_driver'         => 'vps_webrtc',
                    'agora_project_name'    => 'Chinchins Live Default Project',
                    'agora_app_id'          => env('AGORA_APP_ID', ''),
                    'agora_app_certificate' => env('AGORA_APP_CERTIFICATE', ''),
                    'enable_video_call'     => true,
                    'enable_audio_call'     => true,
                    'enable_live_stream'    => true,
                    'token_expire_seconds'  => 86400,
                ]);
            }
            return $setting;
        });
    }

    /**
     * Check if Agora engine is currently active.
     */
    public function isAgora(): bool
    {
        return strtolower($this->active_driver) === 'agora';
    }

    /**
     * Check if VPS WebRTC engine is currently active.
     */
    public function isWebRTC(): bool
    {
        return in_array(strtolower($this->active_driver), ['vps_webrtc', 'webrtc']);
    }

    /**
     * Get Reverb WebSocket config.
     */
    public function getReverbConfig(): array
    {
        $host = $this->reverb_host ?: (config('reverb.servers.reverb.host') ?: parse_url(config('app.url'), PHP_URL_HOST) ?: 'chinchins.live');
        $port = $this->reverb_port ?: (int) (config('reverb.servers.reverb.port') ?: 443);
        $scheme = $this->reverb_scheme ?: (config('reverb.servers.reverb.scheme') ?: 'https');
        $appKey = config('broadcasting.connections.reverb.key') ?: env('REVERB_APP_KEY', 'chinchins_reverb_key');

        return [
            'host'    => $host,
            'port'    => $port,
            'scheme'  => $scheme,
            'app_key' => $appKey,
            'auth_endpoint' => url('/api/broadcasting/auth'),
        ];
    }
}
