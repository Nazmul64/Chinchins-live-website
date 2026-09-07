<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StreamingSetting;
use App\Models\User;
use App\Services\AgoraTokenBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class StreamingController extends Controller
{
    /**
     * Resolve user from Bearer Token, Sanctum guard, or Custom Headers/Params.
     */
    protected function resolveUser(Request $request): ?User
    {
        try {
            if ($user = $request->user('sanctum')) {
                return $user;
            }
            if ($user = $request->user()) {
                return $user;
            }
            if (Auth::guard('sanctum')->check()) {
                return Auth::guard('sanctum')->user();
            }
        } catch (\Throwable $e) {}

        $token = $request->bearerToken() 
              ?: $request->header('Authorization') 
              ?: $request->input('token') 
              ?: $request->input('auth_token');

        if ($token) {
            $tokenClean = trim(str_replace(['Bearer', 'bearer'], '', $token));
            if (class_exists('\Laravel\Sanctum\PersonalAccessToken')) {
                try {
                    $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($tokenClean);
                    if ($accessToken && $accessToken->tokenable) {
                        return $accessToken->tokenable;
                    }
                } catch (\Throwable $e) {}
            }
        }

        $headerUserId = $request->header('X-User-Id') 
                     ?? $request->header('User-Id') 
                     ?? $request->header('X-User-ID') 
                     ?? $request->header('X-Account-Id');

        if ($headerUserId) {
            $u = User::find($headerUserId) ?? User::where('account_id', $headerUserId)->first();
            if ($u) return $u;
        }

        $paramId = $request->input('user_id') ?? $request->input('userId') ?? $request->input('uid') ?? $request->input('account_id');
        if ($paramId) {
            $u = User::find($paramId) ?? User::where('account_id', $paramId)->first();
            if ($u) return $u;
        }

        return null;
    }

    /**
     * Unified Call / Stream Session Token Initializer (Dynamic Dual-Engine Router).
     * Automatically inspects Admin switch and returns Agora Token or WebRTC/Reverb credentials.
     * 
     * POST /api/stream/session-token
     * POST /api/v1/stream/initialize
     * POST /api/stream/initialize
     */
    public function getSessionToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'channel_name'   => 'required|string|max:150',
            'call_type'      => 'nullable|in:audio,video,live,1on1_video,1on1_audio',
            'role'           => 'nullable|in:publisher,subscriber,host,audience',
            'uid'            => 'nullable',
            'target_user_id' => 'nullable',
            'receiver_id'    => 'nullable',
            'peer_id'        => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status'  => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = $this->resolveUser($request);
        $setting = StreamingSetting::getSettings();
        $driver = strtolower($setting->active_driver ?: 'vps_webrtc');

        $channelName = trim($request->input('channel_name'));
        $callType = $request->input('call_type', 'video');
        $rawRole = strtolower($request->input('role', 'publisher'));
        
        // UID resolution
        $uid = (int) ($request->input('uid') ?: ($user ? $user->id : mt_rand(100000, 999999)));

        // Resolve Target/Peer User Profile (Avatar, Name, Gems/Coins for Full-Screen Caller Display)
        $targetId = $request->input('target_user_id') 
                 ?? $request->input('receiver_id') 
                 ?? $request->input('peer_id') 
                 ?? $request->input('target_id')
                 ?? $request->input('to_user_id');
        $targetUserData = null;
        if ($targetId) {
            $targetUser = User::find($targetId) ?? User::where('account_id', $targetId)->first();
            if ($targetUser) {
                $targetUserData = [
                    'id'          => $targetUser->id,
                    'account_id'  => $targetUser->account_id,
                    'name'        => $targetUser->display_name ?? $targetUser->name ?? 'User',
                    'avatar_url'  => $targetUser->avatar_url,
                    'level'       => (int) ($targetUser->level ?? 1),
                    'coins'       => (int) ($targetUser->coins ?? 0),
                    'frame_url'   => $targetUser->avatar_frame_url ?? null,
                ];
            }
        }

        // Agora RTC Mode (Dynamic Builder OR Admin Temp-Token Override)
        if ($driver === 'agora') {
            $appId = $setting->agora_app_id ?: env('AGORA_APP_ID', '');
            $appCert = $setting->agora_app_certificate ?: env('AGORA_APP_CERTIFICATE', '');
            $expireSeconds = $setting->token_expire_seconds ?: 86400; // 24 hours

            // Automated Dynamic HMAC-SHA256 Token Builder when Primary Certificate is present
            if (!empty($appCert)) {
                $agoraRole = in_array($rawRole, ['publisher', 'host']) 
                    ? AgoraTokenBuilder::ROLE_PUBLISHER 
                    : AgoraTokenBuilder::ROLE_SUBSCRIBER;

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
                // Admin Panel Temp-Token Override (Used when no certificate is set)
                $token = trim($setting->agora_temp_token);
                if (!empty($setting->agora_manual_channel)) {
                    $channelName = trim($setting->agora_manual_channel);
                }
                $isTempToken = true;
            } else {
                $token = $appId; // Testing without certificate
                $isTempToken = false;
            }

            return response()->json([
                'success'          => true,
                'status'           => true,
                'driver'           => 'agora',
                'channel_name'     => $channelName,
                'agora_app_id'     => $appId,
                'agora_token'      => $token,
                'agora_uid'        => $uid,
                'is_temp_token'    => $isTempToken,
                'user_id'          => $user?->id ?? $uid,
                'account_id'       => $user?->account_id,
                'target_user'      => $targetUserData,
                'call_type'        => $callType,
                'role'             => $rawRole,
                'expire_seconds'   => $expireSeconds,
                'enable_video'     => (bool) $setting->enable_video_call,
                'enable_audio'     => (bool) $setting->enable_audio_call,
                'enable_live'      => (bool) $setting->enable_live_stream,
                'status_text'      => 'Connecting...',
                'message'          => 'Ready',
            ], 200);
        }

        // Hostinger VPS WebRTC + Reverb Mode
        $reverbConfig = $setting->getReverbConfig();

        return response()->json([
            'success'          => true,
            'status'           => true,
            'driver'           => 'vps_webrtc',
            'channel_name'     => $channelName,
            'user_id'          => $user?->id ?? $uid,
            'account_id'       => $user?->account_id,
            'target_user'      => $targetUserData,
            'call_type'        => $callType,
            'role'             => $rawRole,
            'signaling_host'   => $reverbConfig['host'],
            'signaling_port'   => $reverbConfig['port'],
            'signaling_scheme' => $reverbConfig['scheme'],
            'reverb_app_key'   => $reverbConfig['app_key'],
            'auth_endpoint'    => $reverbConfig['auth_endpoint'],
            'enable_video'     => (bool) $setting->enable_video_call,
            'enable_audio'     => (bool) $setting->enable_audio_call,
            'enable_live'      => (bool) $setting->enable_live_stream,
            'status_text'      => 'Connecting...',
            'message'          => 'Ready',
        ], 200);
    }

    /**
     * Get Current Active Streaming Driver Configuration.
     * GET /api/stream/driver or GET /api/v1/config/streaming-driver or GET /api/stream/config
     */
    public function getDriverConfig(Request $request): JsonResponse
    {
        $setting = StreamingSetting::getSettings();
        $reverbConfig = $setting->getReverbConfig();

        return response()->json([
            'success' => true,
            'status'  => true,
            'data'    => [
                'active_driver'         => $setting->active_driver,
                'is_agora'              => $setting->isAgora(),
                'is_vps_webrtc'         => $setting->isWebRTC(),
                'agora_app_id'          => $setting->isAgora() ? $setting->agora_app_id : null,
                'agora_project_name'    => $setting->agora_project_name,
                'has_temp_token'        => $setting->hasTempToken(),
                'agora_manual_channel'  => $setting->agora_manual_channel,
                'signaling_host'        => $reverbConfig['host'],
                'signaling_port'        => $reverbConfig['port'],
                'enable_video_call'     => (bool) $setting->enable_video_call,
                'enable_audio_call'     => (bool) $setting->enable_audio_call,
                'enable_live_stream'    => (bool) $setting->enable_live_stream,
            ],
        ], 200);
    }
}
