<?php

namespace App\Services\Calling\Drivers;

use App\Models\StreamingSetting;
use App\Models\User;
use App\Services\AgoraTokenBuilder;
use App\Services\Calling\Contracts\CallingDriverInterface;
use Illuminate\Support\Facades\Log;

class AgoraDriver implements CallingDriverInterface
{
    public function getName(): string
    {
        return 'agora';
    }

    public function initializeSession(?User $user, string $channelName, string $callType = 'video', string $role = 'publisher', array $options = []): array
    {
        $setting = StreamingSetting::getSettings();

        $appId = $setting->agora_app_id ?: env('AGORA_APP_ID', config('services.agora.app_id', 'c13c72df342d4a1386da678ba4c95f13'));
        $appCert = $setting->agora_app_certificate ?: env('AGORA_APP_CERTIFICATE', config('services.agora.app_certificate', ''));
        $expireSeconds = (int) ($setting->token_expire_seconds ?: 86400); // 24-hour default
        $uid = (int) ($options['uid'] ?? ($user ? $user->id : mt_rand(100000, 999999)));

        $agoraRole = in_array(strtolower($role), ['publisher', 'host']) 
            ? AgoraTokenBuilder::ROLE_PUBLISHER 
            : AgoraTokenBuilder::ROLE_SUBSCRIBER;

        $isTempToken = false;
        $token = '';

        // 1. Check if Primary Certificate is available for Dynamic HMAC-SHA256 Token Builder
        if (!empty($appCert)) {
            $token = AgoraTokenBuilder::buildTokenWithUid(
                appId: $appId,
                appCertificate: $appCert,
                channelName: $channelName,
                uid: $uid,
                role: $agoraRole,
                privilegeExpireTs: time() + $expireSeconds
            );
            $isTempToken = false;
        } elseif ($setting->hasTempToken()) {
            // Manual Admin Temp Token Override (fallback if no certificate configured)
            $token = trim($setting->agora_temp_token);
            if (!empty($setting->agora_manual_channel)) {
                $channelName = trim($setting->agora_manual_channel);
            }
            $isTempToken = true;
        } else {
            // Testing App ID without certificate
            $token = $appId;
            $isTempToken = false;
        }

        $expiresAt = now()->addSeconds($expireSeconds)->toIso8601String();

        // 2. Server-side logging if Debug Mode is enabled
        if ($setting->agora_debug_mode) {
            Log::channel('single')->info("🎙️ [AgoraDriver] Call Initialized", [
                'channel_name'     => $channelName,
                'user_id'          => $user?->id,
                'uid'              => $uid,
                'role'             => $role,
                'call_type'        => $callType,
                'is_temp_token'    => $isTempToken,
                'token_expires_at' => $expiresAt,
            ]);
        }

        // Return clean response for Flutter SDK (Primary Certificate is NEVER included)
        return [
            'success'          => true,
            'status'           => true,
            'driver'           => 'agora',
            'channel_name'     => $channelName,
            'app_id'           => $appId,
            'agora_app_id'     => $appId,
            'uid'              => $uid,
            'agora_uid'        => $uid,
            'token'            => $token,
            'rtc_token'        => $token,
            'agora_token'      => $token,
            'is_temp_token'    => $isTempToken,
            'token_expires_at' => $expiresAt,
            'expire_seconds'   => $expireSeconds,
            'user_id'          => $user?->id ?? $uid,
            'account_id'       => $user?->account_id,
            'target_user'      => $options['target_user'] ?? null,
            'call_type'        => $callType,
            'role'             => $role,
            'debug_mode'       => (bool) $setting->agora_debug_mode,
            'sdk_logging'      => (bool) $setting->agora_sdk_logging,
            'log_level'        => $setting->agora_log_level ?: 'info',
            'enable_video'     => (bool) $setting->enable_video_call,
            'enable_audio'     => (bool) $setting->enable_audio_call,
            'enable_live'      => (bool) $setting->enable_live_stream,
            'status_text'      => 'Connecting via Agora Cloud Engine...',
            'message'          => 'Ready',
        ];
    }

    public function refreshToken(string $channelName, int|string $uid, string $role = 'publisher'): array
    {
        $setting = StreamingSetting::getSettings();
        $appId = $setting->agora_app_id ?: env('AGORA_APP_ID', config('services.agora.app_id', 'c13c72df342d4a1386da678ba4c95f13'));
        $appCert = $setting->agora_app_certificate ?: env('AGORA_APP_CERTIFICATE', config('services.agora.app_certificate', ''));
        $expireSeconds = (int) ($setting->token_expire_seconds ?: 86400);

        $agoraRole = in_array(strtolower($role), ['publisher', 'host']) 
            ? AgoraTokenBuilder::ROLE_PUBLISHER 
            : AgoraTokenBuilder::ROLE_SUBSCRIBER;

        if (empty($appCert)) {
            // App ID without certificate test mode fallback
            return [
                'success'          => true,
                'driver'           => 'agora',
                'token'            => $appId,
                'rtc_token'        => $appId,
                'channel_name'     => $channelName,
                'uid'              => (int) $uid,
                'expires_at'       => now()->addSeconds($expireSeconds)->toIso8601String(),
                'token_expires_at' => now()->addSeconds($expireSeconds)->toIso8601String(),
                'expire_seconds'   => $expireSeconds,
            ];
        }

        $token = AgoraTokenBuilder::buildTokenWithUid(
            appId: $appId,
            appCertificate: $appCert,
            channelName: $channelName,
            uid: (int) $uid,
            role: $agoraRole,
            privilegeExpireTs: time() + $expireSeconds
        );

        $expiresAt = now()->addSeconds($expireSeconds)->toIso8601String();

        if ($setting->agora_debug_mode) {
            Log::channel('single')->info("🔄 [AgoraDriver] Token Refreshed", [
                'channel_name'     => $channelName,
                'uid'              => $uid,
                'token_expires_at' => $expiresAt,
            ]);
        }

        return [
            'success'          => true,
            'driver'           => 'agora',
            'token'            => $token,
            'rtc_token'        => $token,
            'channel_name'     => $channelName,
            'uid'              => (int) $uid,
            'expires_at'       => $expiresAt,
            'token_expires_at' => $expiresAt,
            'expire_seconds'   => $expireSeconds,
        ];
    }
}
