<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\AppVersion;
use App\Models\CallSetting;
use App\Models\DeviceRegistration;
use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AppUpdateApiController extends Controller
{
    /**
     * Check for In-App Updates & Remote Feature Flags.
     * POST /api/app/check-update or GET /api/app/version-check
     */
    public function checkUpdate(Request $request): JsonResponse
    {
        $currentVersion = $request->input('app_version') 
                       ?? $request->input('version_name') 
                       ?? $request->header('X-App-Version') 
                       ?? '1.0.0';

        $currentVersionCode = (int) ($request->input('version_code') ?? $request->header('X-App-Version-Code') ?? 1);
        $platform = strtolower($request->input('platform') ?? 'android');

        $updateData = AppVersion::checkUpdate($currentVersion, $currentVersionCode, $platform);
        $appConfig = AppSetting::getAppConfig();

        return response()->json([
            'status'  => true,
            'message' => $updateData['has_update'] ? 'New update available!' : 'App is up to date.',
            'data'    => array_merge($updateData, [
                'current_installed_version'      => $currentVersion,
                'current_installed_version_code' => $currentVersionCode,
                'server_time'                    => now()->toIso8601String(),
                'branding'                       => $appConfig,
            ]),
        ], 200);
    }

    /**
     * Get Dynamic Remote Configuration (No APK rebuild required).
     * GET /api/app/remote-config or GET /api/app/config
     */
    public function getRemoteConfig(Request $request): JsonResponse
    {
        $appConfig = AppSetting::getAppConfig();
        $callConfig = CallSetting::getAllConfig();
        $latestVersion = AppVersion::getLatest();

        $remoteFlags = $latestVersion?->remote_flags ?? AppVersion::defaultRemoteFlags();

        return response()->json([
            'status' => true,
            'data'   => [
                'app_name'             => $appConfig['app_name'],
                'app_tagline'          => $appConfig['app_tagline'],
                'app_logo_url'         => $appConfig['app_logo_url'],
                'app_icon_url'         => $appConfig['app_icon_url'],
                'latest_version'       => $latestVersion?->version_name ?? '1.0.0',
                'free_messages_limit'  => $appConfig['free_messages_limit'],
                'message_coin_cost'    => $appConfig['message_coin_cost'],
                'video_call_rate'      => (int) ($callConfig['video_call_rate_per_minute'] ?? 100),
                'audio_call_rate'      => (int) ($callConfig['audio_call_rate_per_minute'] ?? 60),
                'free_trial_duration'  => (int) ($callConfig['free_call_duration_seconds'] ?? 15),
                'incoming_ringtone'    => $callConfig['incoming_ringtone_url'],
                'outgoing_ringtone'    => $callConfig['outgoing_ringtone_url'],
                'remote_flags'         => $remoteFlags,
                'support_email'        => AppSetting::get('support_email', 'support@chinchins.live'),
                'support_whatsapp'     => AppSetting::get('support_whatsapp', '+8801700000000'),
            ]
        ], 200);
    }

    /**
     * Register Universal Device for Push Notifications & Background Call Wake-Up.
     * POST /api/app/device/register or POST /api/device/register
     */
    public function registerDevice(Request $request): JsonResponse
    {
        $fcmToken = $request->input('fcm_token') 
                 ?? $request->input('device_token') 
                 ?? $request->input('token');

        if (empty($fcmToken)) {
            return response()->json([
                'status'  => false,
                'message' => 'FCM / Device Token is required.',
            ], 422);
        }

        $user = $request->user('sanctum');
        if (!$user) {
            $userId = $request->input('user_id') ?? $request->input('userId');
            $user = $userId ? User::find($userId) : null;
        }

        $device = DeviceRegistration::registerDevice(
            userId: $user?->id,
            fcmToken: $fcmToken,
            deviceMeta: [
                'device_id'    => $request->input('device_id'),
                'device_type'  => $request->input('device_type', 'android'),
                'device_brand' => $request->input('device_brand') ?? $request->input('brand'),
                'device_model' => $request->input('device_model') ?? $request->input('model'),
                'os_version'   => $request->input('os_version') ?? $request->input('os'),
                'app_version'  => $request->input('app_version'),
            ]
        );

        if ($user) {
            $user->update(['fcm_token' => $fcmToken]);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Device registered successfully for high-priority push notifications and incoming calls.',
            'data'    => [
                'device_id'   => $device->id,
                'user_id'     => $user?->id,
                'device_type' => $device->device_type,
                'is_active'   => $device->is_active,
            ]
        ], 200);
    }

    /**
     * Test Push Notification Dispatching for Debugging.
     * POST /api/notifications/test-push
     */
    public function testPush(Request $request): JsonResponse
    {
        $user = $request->user('sanctum') ?? User::first();
        $type = $request->input('type', 'general'); // general, incoming_call, chat_message, profile_view

        $title = $request->input('title', 'Chinchins Live Test Notification 🔔');
        $body = $request->input('body', 'This is a live test notification from your server!');

        $tokens = PushNotificationService::getUserTokens($user->id);

        if (empty($tokens)) {
            $fcm = $request->input('fcm_token');
            if ($fcm) {
                $tokens = [$fcm];
            }
        }

        if (empty($tokens)) {
            return response()->json([
                'status'  => false,
                'message' => 'No FCM tokens found. Please register a device first using /api/device/register',
            ], 404);
        }

        $result = PushNotificationService::sendToTokens(
            tokens: $tokens,
            title: $title,
            body: $body,
            data: [
                'action' => strtoupper($type),
                'type'   => $type,
                'test'   => 'true',
            ],
            priority: 'high',
            isCall: $type === 'incoming_call'
        );

        return response()->json([
            'status'  => true,
            'message' => 'Test notification dispatched.',
            'result'  => $result,
        ]);
    }
}
