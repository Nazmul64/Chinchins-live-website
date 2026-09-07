<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StreamingSetting;
use App\Models\User;
use App\Services\Calling\CallingManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class StreamingController extends Controller
{
    protected CallingManager $callingManager;

    public function __construct(CallingManager $callingManager)
    {
        $this->callingManager = $callingManager;
    }

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
     * POST /api/calls
     * POST /api/stream/session-token
     * POST /api/v1/stream/initialize
     * POST /api/stream/initialize
     */
    public function getSessionToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'channel_name'   => 'nullable|string|max:150',
            'call_type'      => 'nullable|in:audio,video,live,1on1_video,1on1_audio',
            'role'           => 'nullable|in:publisher,subscriber,host,audience',
            'uid'            => 'nullable',
            'target_user_id' => 'nullable',
            'receiver_id'    => 'nullable',
            'peer_id'        => 'nullable',
            'driver'         => 'nullable|string|in:vps_webrtc,webrtc,agora',
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
        
        // Auto-generate dynamic unique channel name if not provided
        $channelName = trim($request->input('channel_name') ?: $this->callingManager->generateChannelName('call'));
        $callType = $request->input('call_type', 'video');
        $rawRole = strtolower($request->input('role', 'publisher'));
        $overrideDriver = $request->input('driver');

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

        $options = [
            'uid'         => $request->input('uid'),
            'target_user' => $targetUserData,
        ];

        // Execute unified calling driver
        $sessionData = $this->callingManager->initializeSession(
            $user,
            $channelName,
            $callType,
            $rawRole,
            $options,
            $overrideDriver
        );

        return response()->json($sessionData, 200);
    }

    /**
     * Refresh RTC Token for ongoing calls.
     * POST /api/agora/token/refresh
     * POST /api/stream/token/refresh
     */
    public function refreshToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'channel_name' => 'required|string|max:150',
            'uid'          => 'required',
            'role'         => 'nullable|in:publisher,subscriber,host,audience',
            'driver'       => 'nullable|string|in:agora,vps_webrtc',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $channelName = trim($request->input('channel_name'));
        $uid = $request->input('uid');
        $role = $request->input('role', 'publisher');
        $driver = $request->input('driver', 'agora');

        $result = $this->callingManager->refreshToken($channelName, $uid, $role, $driver);

        return response()->json($result, $result['success'] ? 200 : 400);
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
                'active_driver'         => $this->callingManager->getActiveDriverName(),
                'is_agora'              => $this->callingManager->getActiveDriverName() === 'agora',
                'is_vps_webrtc'         => $this->callingManager->getActiveDriverName() === 'vps_webrtc',
                'agora_app_id'          => $setting->isAgora() ? $setting->agora_app_id : null,
                'agora_project_name'    => $setting->agora_project_name,
                'token_expire_seconds'  => (int) ($setting->token_expire_seconds ?: 3600),
                'debug_mode'            => (bool) $setting->agora_debug_mode,
                'sdk_logging'           => (bool) $setting->agora_sdk_logging,
                'log_level'             => $setting->agora_log_level ?: 'info',
                'signaling_host'        => $reverbConfig['host'],
                'signaling_port'        => $reverbConfig['port'],
                'enable_video_call'     => (bool) $setting->enable_video_call,
                'enable_audio_call'     => (bool) $setting->enable_audio_call,
                'enable_live_stream'    => (bool) $setting->enable_live_stream,
            ],
        ], 200);
    }
}
