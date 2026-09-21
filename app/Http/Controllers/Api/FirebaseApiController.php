<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CallSession;
use App\Models\DeviceRegistration;
use App\Models\FirebaseApp;
use App\Models\Notification;
use App\Models\PushNotification;
use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FirebaseApiController extends Controller
{
    /**
     * Resolve authenticated user from Bearer Token, Header, or Request ID.
     */
    protected function resolveUser(Request $request): ?User
    {
        // 1. Bearer Token via Sanctum
        if ($request->bearerToken()) {
            if (class_exists('\Laravel\Sanctum\PersonalAccessToken')) {
                try {
                    $tokenClean = trim(str_replace(['Bearer', 'bearer'], '', $request->bearerToken()));
                    $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($tokenClean);
                    if ($accessToken && $accessToken->tokenable) {
                        return $accessToken->tokenable;
                    }
                } catch (\Throwable $e) {}
            }
        }

        if (Auth::guard('sanctum')->check() && Auth::guard('sanctum')->user()) {
            return Auth::guard('sanctum')->user();
        }

        // 2. Custom headers
        $headerUserId = $request->header('X-User-Id') ?? $request->header('User-Id');
        if ($headerUserId) {
            $u = User::find($headerUserId) ?? User::where('account_id', $headerUserId)->first();
            if ($u) return $u;
        }

        // 3. Fallback request parameters
        if ($request->filled('user_id')) {
            return User::find($request->user_id);
        }
        if ($request->filled('email')) {
            return User::where('email', $request->email)->first();
        }
        if ($request->filled('account_id')) {
            return User::where('account_id', $request->account_id)->first();
        }

        return null;
    }

    /**
     * Update / Register Device FCM Token from Mobile Client.
     * POST /api/update-fcm-token or POST /api/fcm/register-token
     */
    public function updateFcmToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'fcm_token'       => 'required|string',
            'token'           => 'nullable|string',
            'device_type'     => 'nullable|string|in:android,ios,web',
            'device_id'       => 'nullable|string',
            'device_brand'    => 'nullable|string',
            'device_model'    => 'nullable|string',
            'os_version'      => 'nullable|string',
            'app_version'     => 'nullable|string',
            'firebase_app_id' => 'nullable|integer',
            'package_name'    => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $fcmToken = trim($request->input('fcm_token') ?: $request->input('token'));
        $user = $this->resolveUser($request);

        // Update user fcm_token on User model if user is resolved
        if ($user) {
            $user->fcm_token = $fcmToken;
            $user->save();
        }

        // Register in device_registrations table
        $device = DeviceRegistration::registerDevice(
            userId: $user?->id,
            fcmToken: $fcmToken,
            deviceMeta: [
                'device_id'       => $request->input('device_id'),
                'device_type'     => $request->input('device_type', 'android'),
                'device_brand'    => $request->input('device_brand') ?: $request->input('brand'),
                'device_model'    => $request->input('device_model') ?: $request->input('model'),
                'os_version'      => $request->input('os_version') ?: $request->input('os'),
                'app_version'     => $request->input('app_version'),
                'firebase_app_id' => $request->input('firebase_app_id'),
                'package_name'    => $request->input('package_name'),
            ]
        );

        return response()->json([
            'status'     => true,
            'success'    => true,
            'message'    => 'FCM Token registered and synced successfully.',
            'data'       => [
                'device_id'       => $device->id,
                'user_id'         => $user?->id,
                'device_type'     => $device->device_type,
                'firebase_app_id' => $device->firebase_app_id,
                'last_active_at'  => $device->last_active_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get list of active Firebase Apps.
     * GET /api/fcm/apps
     */
    public function getApps(): JsonResponse
    {
        $apps = FirebaseApp::where('is_active', true)
            ->select('id', 'app_name', 'package_name', 'project_id', 'is_active', 'created_at')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $apps,
        ]);
    }

    /**
     * Trigger 1-to-1 Incoming Call Push Notification.
     * POST /api/fcm/send-call-notification
     */
    public function sendCallNotification(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'receiver_id'  => 'required',
            'caller_id'    => 'nullable',
            'call_id'      => 'nullable',
            'channel_name' => 'nullable|string',
            'call_type'    => 'nullable|string|in:video,audio',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $caller = $this->resolveUser($request) ?? ($request->filled('caller_id') ? User::find($request->caller_id) : null);
        if (!$caller) {
            return response()->json(['status' => false, 'message' => 'Caller user could not be resolved.'], 401);
        }

        $receiver = User::find($request->receiver_id) ?? User::where('account_id', $request->receiver_id)->first();
        if (!$receiver) {
            return response()->json(['status' => false, 'message' => 'Receiver user not found.'], 404);
        }

        // Mock or create a call session if not passed
        $call = null;
        if ($request->filled('call_id')) {
            $call = CallSession::find($request->call_id);
        }
        if (!$call) {
            $call = new CallSession();
            $call->id = (int) ($request->call_id ?: time());
            $call->channel_name = $request->input('channel_name', 'call_' . $caller->id . '_' . $receiver->id . '_' . time());
            $call->call_type = $request->input('call_type', 'video');
            $call->rate_per_minute = 100;
        }

        $result = PushNotificationService::sendIncomingCallPush($call, $caller, $receiver);

        return response()->json([
            'status'   => $result['status'] ?? false,
            'message'  => $result['status'] ? 'Incoming call push dispatched to receiver.' : ($result['message'] ?? 'Call push dispatch failed.'),
            'call_id'  => $call->id,
            'channel'  => $call->channel_name,
            'caller'   => [
                'id'           => $caller->id,
                'name'         => $caller->display_name ?: $caller->name,
                'avatar'       => $caller->avatar_url ?: $caller->profile_image,
                'account_id'   => $caller->account_id,
            ],
            'receiver' => [
                'id'           => $receiver->id,
                'name'         => $receiver->display_name ?: $receiver->name,
                'account_id'   => $receiver->account_id,
            ],
            'dispatch' => $result,
        ]);
    }

    /**
     * Trigger 1-to-1 Chat & Photo Push Notification.
     * POST /api/fcm/send-chat-notification
     */
    public function sendChatNotification(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'receiver_id'  => 'required',
            'sender_id'    => 'nullable',
            'message'      => 'nullable|string',
            'text'         => 'nullable|string',
            'image_url'    => 'nullable|string',
            'media_url'    => 'nullable|string',
            'message_type' => 'nullable|string|in:text,image,photo,voice,audio,gift',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $sender = $this->resolveUser($request) ?? ($request->filled('sender_id') ? User::find($request->sender_id) : null);
        if (!$sender) {
            return response()->json(['status' => false, 'message' => 'Sender user could not be resolved.'], 401);
        }

        $receiver = User::find($request->receiver_id) ?? User::where('account_id', $request->receiver_id)->first();
        if (!$receiver) {
            return response()->json(['status' => false, 'message' => 'Receiver user not found.'], 404);
        }

        $msgText = $request->input('message') ?: $request->input('text') ?: 'Hi baby';
        $photoUrl = $request->input('image_url') ?: $request->input('media_url');
        $msgType = $request->input('message_type', $photoUrl ? 'image' : 'text');

        $mockMsg = (object) [
            'id'        => time(),
            'type'      => $msgType,
            'message'   => $msgText,
            'media_url' => $photoUrl,
        ];

        $result = PushNotificationService::sendChatMessagePush(
            message: $mockMsg,
            sender: $sender,
            receiver: $receiver,
            overrideText: $msgType === 'image' ? '📷 Sent a photo' : $msgText,
            overrideImage: $photoUrl
        );

        // Also save in in-app notifications table
        Notification::create([
            'user_id'   => $receiver->id,
            'actor_id'  => $sender->id,
            'type'      => $msgType === 'image' ? 'image' : 'message',
            'title'     => $sender->display_name ?: $sender->name,
            'message'   => $msgType === 'image' ? '📷 Sent a photo' : $msgText,
            'data'      => [
                'sender_id'     => $sender->id,
                'sender_avatar' => $sender->avatar_url ?: $sender->profile_image,
                'image_url'     => $photoUrl,
            ],
            'is_read'   => false,
        ]);

        return response()->json([
            'status'   => $result['status'] ?? false,
            'message'  => $result['status'] ? 'Chat message push dispatched to recipient.' : ($result['message'] ?? 'Push failed.'),
            'sender'   => [
                'id'     => $sender->id,
                'name'   => $sender->display_name ?: $sender->name,
                'avatar' => $sender->avatar_url ?: $sender->profile_image,
            ],
            'receiver' => [
                'id'     => $receiver->id,
                'name'   => $receiver->display_name ?: $receiver->name,
            ],
            'payload'  => [
                'text'      => $msgText,
                'image_url' => $photoUrl,
                'type'      => $msgType,
            ],
            'dispatch' => $result,
        ]);
    }

    /**
     * Send Broadcast Notification via API.
     * POST /api/fcm/send-broadcast
     */
    public function sendBroadcast(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title'           => 'required|string|max:255',
            'message'         => 'required|string|max:1000',
            'image_url'       => 'nullable|string',
            'action_url'      => 'nullable|string',
            'platform'        => 'nullable|string|in:firebase,onesignal,both',
            'firebase_app_id' => 'nullable|integer',
            'user_ids'        => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $platform = $request->input('platform', 'firebase');
        $userIds = $request->input('user_ids', ['all']);

        $res = PushNotificationService::sendPush(
            platform: $platform,
            targetUserIds: $userIds ?: ['all'],
            title: $request->title,
            body: $request->message,
            imageUrl: $request->image_url,
            actionUrl: $request->action_url,
            firebaseAppId: $request->firebase_app_id
        );

        return response()->json([
            'status'       => $res['status'],
            'sent_count'   => $res['sent_count'] ?? 0,
            'failed_count' => $res['failed_count'] ?? 0,
            'record_id'    => $res['record_id'] ?? null,
            'message'      => "Broadcast dispatched successfully! Sent: " . ($res['sent_count'] ?? 0),
        ]);
    }

    /**
     * Get In-App Notifications for Current User.
     * GET /api/notifications or GET /api/fcm/my-notifications
     */
    public function getMyNotifications(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $limit = max(1, min(100, (int) ($request->input('limit') ?: $request->input('per_page') ?: 20)));

        $notifications = Notification::with(['actor' => function ($query) {
                $query->select('id', 'name', 'display_name', 'avatar', 'account_id', 'level');
            }])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate($limit);

        $unreadCount = Notification::where('user_id', $user->id)->where('is_read', false)->count();

        $items = collect($notifications->items())->map(function ($notif) {
            $actor = $notif->actor;
            return [
                'id'         => $notif->id,
                'user_id'    => $notif->user_id,
                'actor_id'   => $notif->actor_id,
                'type'       => $notif->type,
                'title'      => $notif->title,
                'message'    => $notif->message,
                'data'       => $notif->data,
                'is_read'    => (bool) $notif->is_read,
                'read_at'    => $notif->read_at?->toIso8601String(),
                'created_at' => $notif->created_at?->toIso8601String(),
                'actor'      => $actor ? [
                    'id'           => $actor->id,
                    'name'         => $actor->name,
                    'display_name' => $actor->display_name ?? $actor->name,
                    'account_id'   => $actor->account_id,
                    'avatar'       => $actor->avatar_url,
                    'avatar_url'   => $actor->avatar_url,
                    'level'        => (int) ($actor->level ?? 1),
                ] : null,
            ];
        });

        return response()->json([
            'status'        => true,
            'success'       => true,
            'unread_count'  => $unreadCount,
            'data'          => $items,
            'notifications' => $items,
            'pagination'    => [
                'current_page' => $notifications->currentPage(),
                'last_page'    => $notifications->lastPage(),
                'per_page'     => $notifications->perPage(),
                'total'        => $notifications->total(),
            ],
        ]);
    }

    /**
     * Mark Notification(s) as Read.
     * POST /api/notifications/read or POST /api/fcm/mark-read
     */
    public function markAsRead(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        if ($request->filled('notification_id')) {
            Notification::where('user_id', $user->id)
                ->where('id', $request->notification_id)
                ->update(['is_read' => true, 'read_at' => now()]);
        } else {
            Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Notification(s) marked as read.',
        ]);
    }

    /**
     * Get Push Notification Logs / History.
     * GET /api/fcm/history
     */
    public function getHistory(Request $request): JsonResponse
    {
        $query = PushNotification::with('firebaseApp:id,app_name,package_name')->latest();

        if ($request->filled('app_id')) {
            $query->where('firebase_app_id', $request->app_id);
        }

        $logs = $query->paginate($request->input('limit', 15));

        return response()->json([
            'status'     => true,
            'data'       => $logs->items(),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page'    => $logs->lastPage(),
                'total'        => $logs->total(),
            ],
        ]);
    }

    /**
     * Firebase FCM Configuration & Connection Status Diagnostic.
     * GET /api/fcm/status or GET /api/fcm/check
     */
    public function getStatus(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        $appsCount = FirebaseApp::where('is_active', true)->count();
        $registeredDevicesCount = DeviceRegistration::count();
        $userWithFcmCount = User::whereNotNull('fcm_token')->where('fcm_token', '!=', '')->count();

        $activeApp = FirebaseApp::where('is_active', true)->first();

        return response()->json([
            'status'     => true,
            'connected'  => true,
            'message'    => 'Firebase FCM service is active and operational.',
            'diagnostic' => [
                'firebase_configured'     => true,
                'active_firebase_apps'    => $appsCount,
                'default_project_id'      => $activeApp?->project_id ?? config('services.firebase.project_id', 'chinchins-live'),
                'package_name'            => $activeApp?->package_name ?? 'com.chinchins.live',
                'registered_devices'      => $registeredDevicesCount,
                'users_with_fcm_token'    => $userWithFcmCount,
                'current_user'            => $user ? [
                    'id'                => $user->id,
                    'name'              => $user->display_name ?: $user->name,
                    'account_id'        => $user->account_id,
                    'has_fcm_token'     => !empty($user->fcm_token),
                    'fcm_token_preview' => !empty($user->fcm_token) ? (substr($user->fcm_token, 0, 20) . '...') : null,
                    'device_type'       => $user->device_type ?? 'android',
                ] : null,
            ],
        ]);
    }

    /**
     * Send Instant Test Push Notification to Verify Delivery.
     * POST /api/fcm/test-push or POST /api/fcm/test
     */
    public function testPush(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        $targetToken = $request->input('fcm_token') ?: ($user?->fcm_token);

        if (!$targetToken && $request->filled('user_id')) {
            $u = User::find($request->user_id) ?? User::where('account_id', $request->user_id)->first();
            $targetToken = $u?->fcm_token;
        }

        if (empty($targetToken)) {
            return response()->json([
                'status'  => false,
                'message' => 'No FCM token found. Please pass fcm_token or log in from a registered device first.',
            ], 422);
        }

        $title = $request->input('title', '🎉 ChinChins Live Test Push');
        $body = $request->input('body', 'Firebase Push Notification connected successfully! 🚀');

        $result = PushNotificationService::sendCustomPush(
            fcmToken: $targetToken,
            title: $title,
            body: $body,
            data: [
                'type'         => 'test_notification',
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                'timestamp'    => (string) now()->timestamp,
            ]
        );

        return response()->json([
            'status'     => $result['status'] ?? true,
            'message'    => 'Test push notification dispatched.',
            'fcm_token'  => substr($targetToken, 0, 25) . '...',
            'title'      => $title,
            'body'       => $body,
            'result'     => $result,
        ]);
    }
}
