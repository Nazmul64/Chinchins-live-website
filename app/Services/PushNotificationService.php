<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\CallSession;
use App\Models\CallSetting;
use App\Models\ChatMessage;
use App\Models\DeviceRegistration;
use App\Models\FirebaseApp;
use App\Models\Gift;
use App\Models\PushNotification;
use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    /**
     * Get configured FCM Server Key (Legacy fallback).
     */
    public static function getServerKey(?FirebaseApp $app = null): ?string
    {
        if ($app && !empty($app->server_key)) {
            return $app->server_key;
        }

        $defaultApp = FirebaseApp::getDefault();
        if ($defaultApp && !empty($defaultApp->server_key)) {
            return $defaultApp->server_key;
        }

        return AppSetting::get('fcm_server_key') 
            ?? config('services.fcm.key') 
            ?? env('FCM_SERVER_KEY');
    }

    /**
     * Get Google OAuth2 Access Token for Firebase HTTP v1 API.
     */
    public static function getOAuth2AccessToken(?FirebaseApp $app = null): ?array
    {
        $targetApp = $app ?? FirebaseApp::getDefault();
        if (!$targetApp) {
            return null;
        }

        $serviceAccount = null;

        // Try from raw JSON column in DB
        if (!empty($targetApp->service_account_json)) {
            $serviceAccount = json_decode($targetApp->service_account_json, true);
        }

        // Try from JSON file path
        if (!$serviceAccount && !empty($targetApp->service_account_path) && file_exists(storage_path('app/' . $targetApp->service_account_path))) {
            $serviceAccount = json_decode(file_get_contents(storage_path('app/' . $targetApp->service_account_path)), true);
        }

        if (!$serviceAccount || empty($serviceAccount['client_email']) || empty($serviceAccount['private_key']) || empty($serviceAccount['project_id'])) {
            return null;
        }

        $cacheKey = 'fcm_v1_token_' . $targetApp->id;
        $cachedToken = Cache::get($cacheKey);
        if ($cachedToken) {
            return [
                'access_token' => $cachedToken,
                'project_id'   => $serviceAccount['project_id'],
            ];
        }

        try {
            $now = time();
            $payload = [
                'iss'   => $serviceAccount['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud'   => 'https://oauth2.googleapis.com/token',
                'iat'   => $now,
                'exp'   => $now + 3600,
            ];

            $jwt = JWT::encode($payload, $serviceAccount['private_key'], 'RS256');

            $response = Http::asForm()->connectTimeout(1)->timeout(2)->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $accessToken = $data['access_token'] ?? null;
                if ($accessToken) {
                    Cache::put($cacheKey, $accessToken, now()->addMinutes(50));
                    return [
                        'access_token' => $accessToken,
                        'project_id'   => $serviceAccount['project_id'],
                    ];
                }
            } else {
                Log::error("FCM OAuth2 Token Error: " . $response->body());
            }
        } catch (\Throwable $e) {
            Log::error("FCM OAuth2 Exception: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Get all active FCM tokens for a user across all registered devices.
     */
    public static function getUserTokens(int $userId, ?int $firebaseAppId = null): array
    {
        $tokens = [];

        // 1. From device_registrations table
        $query = DeviceRegistration::where('user_id', $userId)
            ->where('is_active', true);
        
        if ($firebaseAppId) {
            $query->where(function ($q) use ($firebaseAppId) {
                $q->where('firebase_app_id', $firebaseAppId)
                  ->orWhereNull('firebase_app_id');
            });
        }

        $deviceTokens = $query->pluck('fcm_token')->toArray();
        
        foreach ($deviceTokens as $t) {
            if (!empty($t)) $tokens[] = trim($t);
        }

        // 2. From users table
        $user = User::find($userId);
        if ($user && !empty($user->fcm_token)) {
            $tokens[] = trim($user->fcm_token);
        }

        return array_values(array_unique(array_filter($tokens)));
    }

    /**
     * Send High-Priority Incoming Call Push Notification.
     * Rings receiver's mobile device with full screen / floating incoming call UI.
     */
    public static function sendIncomingCallPush(CallSession $call, User $caller, User $receiver): array
    {
        $tokens = static::getUserTokens($receiver->id);
        if (empty($tokens)) {
            Log::info("PushNotification: No device tokens found for receiver ID {$receiver->id}");
            return ['status' => false, 'message' => 'No active device tokens found.', 'sent' => 0, 'failed' => 0];
        }

        $config = CallSetting::getAllConfig();
        $incomingRingtoneUrl = $config['incoming_ringtone_url'] ?? asset('assets/audio/incoming_call.mp3');

        $dataPayload = [
            'action'              => 'INCOMING_CALL',
            'type'                => 'incoming_call',
            'call_id'             => (string) $call->id,
            'channel_name'        => (string) $call->channel_name,
            'call_type'           => (string) ($call->call_type ?: 'video'),
            'caller_id'           => (string) $caller->id,
            'caller_account_id'   => (string) ($caller->account_id ?: $caller->id),
            'caller_name'         => (string) ($caller->display_name ?: $caller->name),
            'caller_avatar'       => (string) ($caller->avatar_url ?: $caller->profile_image ?: ''),
            'rate_per_minute'     => (string) ($call->rate_per_minute ?: 100),
            'is_free_trial'       => $call->is_free_trial ? '1' : '0',
            'free_duration'       => (string) ($call->free_duration_seconds ?: 0),
            'ringtone_url'        => (string) $incomingRingtoneUrl,
            'ring_timeout'        => '45',
            'timestamp'           => (string) time(),
        ];

        $title = "Incoming " . ucfirst($call->call_type ?: 'Video') . " Call";
        $body = ($caller->display_name ?: $caller->name) . " is calling you...";

        return static::sendToTokens(
            tokens: $tokens,
            title: $title,
            body: $body,
            data: $dataPayload,
            priority: 'high',
            isCall: true,
            imageUrl: $caller->avatar_url ?: $caller->profile_image
        );
    }

    /**
     * Send Instant Chat Message & Photo Push Notification.
     * Delivered when a user or female host sends a message (e.g. "Hi baby", "How are you?") or a picture.
     */
    public static function sendChatMessagePush($message, User $sender, User $receiver, ?string $overrideText = null, ?string $overrideImage = null): array
    {
        $tokens = static::getUserTokens($receiver->id);
        if (empty($tokens)) {
            return ['status' => false, 'message' => 'No active device tokens found.', 'sent' => 0, 'failed' => 0];
        }

        $messageType = is_object($message) ? ($message->type ?? 'text') : 'text';
        $messageContent = is_object($message) ? ($message->message ?? $message->content ?? '') : (string) $message;

        $bodyText = $overrideText;
        if (!$bodyText) {
            $bodyText = match ($messageType) {
                'image', 'photo' => '📷 Sent a photo',
                'voice', 'audio' => '🎤 Sent a voice message',
                'gift'           => '🎁 Sent you a gift',
                default          => $messageContent ?: 'New message',
            };
        }

        $senderName = $sender->display_name ?: $sender->name ?: 'Someone';
        $senderAvatar = $sender->avatar_url ?: $sender->profile_image ?: '';
        $photoUrl = $overrideImage ?? (is_object($message) ? ($message->media_url ?? $message->image_url ?? null) : null);

        $dataPayload = [
            'action'        => 'CHAT_MESSAGE',
            'type'          => 'chat_message',
            'message_id'    => (string) (is_object($message) ? ($message->id ?? time()) : time()),
            'sender_id'     => (string) $sender->id,
            'sender_name'   => (string) $senderName,
            'sender_avatar' => (string) $senderAvatar,
            'message_type'  => (string) $messageType,
            'image_url'     => (string) ($photoUrl ?: ''),
            'text'          => (string) $bodyText,
            'timestamp'     => (string) time(),
        ];

        return static::sendToTokens(
            tokens: $tokens,
            title: $senderName,
            body: $bodyText,
            data: $dataPayload,
            priority: 'high',
            imageUrl: $photoUrl ?: $senderAvatar,
            actionUrl: '/chat/' . $sender->id
        );
    }

    /**
     * Send Profile View Push Notification.
     */
    public static function sendProfileViewPush(User $viewer, User $host): array
    {
        $tokens = static::getUserTokens($host->id);
        if (empty($tokens)) {
            return ['status' => false, 'message' => 'No active device tokens found.', 'sent' => 0, 'failed' => 0];
        }

        $viewerName = $viewer->display_name ?: $viewer->name;
        $title = "New Profile Visitor 👁️";
        $body = "{$viewerName} viewed your profile! Say hi!";

        $dataPayload = [
            'action'        => 'PROFILE_VIEW',
            'type'          => 'profile_view',
            'viewer_id'     => (string) $viewer->id,
            'viewer_name'   => (string) $viewerName,
            'viewer_avatar' => (string) ($viewer->avatar_url ?: $viewer->profile_image ?: ''),
            'timestamp'     => (string) time(),
        ];

        return static::sendToTokens(
            tokens: $tokens,
            title: $title,
            body: $body,
            data: $dataPayload,
            priority: 'normal',
            imageUrl: $viewer->avatar_url ?: $viewer->profile_image
        );
    }

    /**
     * Send Gift Received Push Notification.
     */
    public static function sendGiftPush(User $sender, User $receiver, Gift $gift, int $coins = 0): array
    {
        $tokens = static::getUserTokens($receiver->id);
        if (empty($tokens)) {
            return ['status' => false, 'message' => 'No active device tokens found.', 'sent' => 0, 'failed' => 0];
        }

        $senderName = $sender->display_name ?: $sender->name;
        $title = "Gift Received! 🎁";
        $body = "{$senderName} sent you a {$gift->name} (+{$coins} coins)!";

        $dataPayload = [
            'action'      => 'GIFT_RECEIVED',
            'type'        => 'gift_received',
            'sender_id'   => (string) $sender->id,
            'sender_name' => (string) $senderName,
            'gift_id'     => (string) $gift->id,
            'gift_name'   => (string) $gift->name,
            'coins'       => (string) $coins,
            'timestamp'   => (string) time(),
        ];

        return static::sendToTokens(
            tokens: $tokens,
            title: $title,
            body: $body,
            data: $dataPayload,
            priority: 'high',
            imageUrl: $gift->icon_url ?: $gift->image_url
        );
    }

    /**
     * Broadcast In-App Update Notice to All Active Devices.
     */
    public static function broadcastAppUpdate(string $versionName, string $title, string $changelog, ?string $downloadUrl = null, bool $force = false): array
    {
        $tokens = DeviceRegistration::where('is_active', true)
            ->pluck('fcm_token')
            ->merge(User::whereNotNull('fcm_token')->pluck('fcm_token'))
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if (empty($tokens)) {
            return ['status' => false, 'message' => 'No active device tokens to broadcast.', 'sent' => 0, 'failed' => 0];
        }

        $dataPayload = [
            'action'       => 'APP_UPDATE',
            'type'         => 'app_update',
            'version_name' => (string) $versionName,
            'title'        => (string) $title,
            'changelog'    => (string) $changelog,
            'download_url' => (string) ($downloadUrl ?: asset('downloads/chinchins_live.apk')),
            'force_update' => $force ? '1' : '0',
            'timestamp'    => (string) time(),
        ];

        return static::sendToTokens(
            tokens: $tokens,
            title: $title,
            body: "Version {$versionName} is now available! {$changelog}",
            data: $dataPayload,
            priority: 'high'
        );
    }

    /**
     * Dispatch Push Notification across FCM and/or OneSignal with full multi-app & logging support.
     */
    public static function sendPush(
        string $platform,
        array $targetUserIds,
        string $title,
        string $body,
        ?string $imageUrl = null,
        ?string $actionUrl = null,
        ?int $firebaseAppId = null,
        array $extraData = []
    ): array {
        $sentCount = 0;
        $failedCount = 0;
        $responses = [];

        $isAll = in_array('all', $targetUserIds) || empty($targetUserIds);

        // Resolve FCM tokens
        $tokens = [];
        if ($isAll) {
            $deviceQuery = DeviceRegistration::where('is_active', true);
            if ($firebaseAppId) {
                $deviceQuery->where(function ($q) use ($firebaseAppId) {
                    $q->where('firebase_app_id', $firebaseAppId)->orWhereNull('firebase_app_id');
                });
            }
            $tokens = $deviceQuery->pluck('fcm_token')
                ->merge(User::whereNotNull('fcm_token')->pluck('fcm_token'))
                ->filter()
                ->unique()
                ->values()
                ->toArray();
        } else {
            foreach ($targetUserIds as $uId) {
                $userTokens = static::getUserTokens((int) $uId, $firebaseAppId);
                $tokens = array_merge($tokens, $userTokens);
            }
            $tokens = array_values(array_unique(array_filter($tokens)));
        }

        // 1. Dispatch Firebase FCM
        if (in_array($platform, ['firebase', 'both'])) {
            if (!empty($tokens)) {
                $data = array_merge($extraData, [
                    'action'     => $actionUrl ?: 'NOTIFICATION_CLICK',
                    'action_url' => (string) ($actionUrl ?: ''),
                    'image_url'  => (string) ($imageUrl ?: ''),
                ]);

                $fcmRes = static::sendToTokens(
                    tokens: $tokens,
                    title: $title,
                    body: $body,
                    data: $data,
                    priority: 'high',
                    isCall: false,
                    firebaseAppId: $firebaseAppId,
                    imageUrl: $imageUrl,
                    actionUrl: $actionUrl
                );

                $sentCount += $fcmRes['sent'] ?? (isset($fcmRes['status']) && $fcmRes['status'] ? count($tokens) : 0);
                $failedCount += $fcmRes['failed'] ?? 0;
                $responses['firebase'] = $fcmRes;
            } else {
                $responses['firebase'] = ['status' => false, 'message' => 'No FCM tokens found'];
            }
        }

        // 2. Dispatch OneSignal
        if (in_array($platform, ['onesignal', 'both'])) {
            $osRes = static::sendOneSignal(
                isAll: $isAll,
                targetUserIds: $targetUserIds,
                title: $title,
                body: $body,
                imageUrl: $imageUrl,
                actionUrl: $actionUrl,
                data: $extraData
            );
            $responses['onesignal'] = $osRes;
            if (!empty($osRes['status'])) {
                $sentCount += $osRes['recipients'] ?? 1;
            } else {
                $failedCount += 1;
            }
        }

        $totalTargets = count($tokens);
        $status = 'delivered';
        if ($sentCount == 0 && $failedCount > 0) {
            $status = 'failed';
        } elseif ($failedCount > 0 && $sentCount > 0) {
            $status = 'partial';
        }

        // Store Push Notification Record in DB
        $notificationRecord = PushNotification::create([
            'firebase_app_id'  => $firebaseAppId,
            'platform'         => $platform,
            'title'            => $title,
            'message'          => $body,
            'image_url'        => $imageUrl,
            'action_url'       => $actionUrl,
            'send_to'          => $isAll ? 'all' : 'specific',
            'target_user_ids'  => $isAll ? null : $targetUserIds,
            'sent_count'       => $sentCount,
            'failed_count'     => $failedCount,
            'total_target'     => $totalTargets ?: ($sentCount + $failedCount),
            'status'           => $status,
            'response_payload' => json_encode($responses),
            'created_by'       => auth()->id(),
        ]);

        return [
            'status'       => $status !== 'failed',
            'record_id'    => $notificationRecord->id,
            'sent_count'   => $sentCount,
            'failed_count' => $failedCount,
            'responses'    => $responses,
        ];
    }

    /**
     * Dispatch FCM Push Payload via Firebase HTTP v1 or Legacy Gateway.
     */
    public static function sendToTokens(
        array $tokens,
        string $title,
        string $body,
        array $data = [],
        string $priority = 'high',
        bool $isCall = false,
        ?int $firebaseAppId = null,
        ?string $imageUrl = null,
        ?string $actionUrl = null
    ): array {
        if (empty($tokens)) {
            return ['status' => false, 'sent' => 0, 'failed' => 0, 'message' => 'Token list is empty.'];
        }

        $tokens = array_values(array_unique(array_filter($tokens)));
        $targetApp = $firebaseAppId ? FirebaseApp::find($firebaseAppId) : FirebaseApp::getDefault();

        // 1. Try FCM HTTP v1 API first if service account JSON exists
        $oauth2 = static::getOAuth2AccessToken($targetApp);
        if ($oauth2 && !empty($oauth2['access_token']) && !empty($oauth2['project_id'])) {
            $accessToken = $oauth2['access_token'];
            $projectId = $oauth2['project_id'];
            $v1Url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

            $sent = 0;
            $failed = 0;

            foreach ($tokens as $token) {
                try {
                    $v1Payload = [
                        'message' => [
                            'token' => $token,
                            'notification' => [
                                'title' => $title,
                                'body'  => $body,
                                'image' => $imageUrl ?: null,
                            ],
                            'data' => array_map('strval', array_merge($data, [
                                'title'      => $title,
                                'body'       => $body,
                                'is_call'    => $isCall ? 'true' : 'false',
                                'action_url' => $actionUrl ?: '',
                            ])),
                            'android' => [
                                'priority' => $priority === 'high' ? 'HIGH' : 'NORMAL',
                                'ttl'      => ($isCall ? '45s' : '86400s'),
                                'notification' => [
                                    'channel_id' => $isCall ? 'chinchins_call_channel' : 'chinchins_messages_channel',
                                    'sound'      => $isCall ? 'call_ringtone' : 'default',
                                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                                ],
                            ],
                        ],
                    ];

                    $res = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Content-Type'  => 'application/json',
                    ])->connectTimeout(1)->timeout(2)->post($v1Url, $v1Payload);

                    if ($res->successful()) {
                        $sent++;
                    } else {
                        $failed++;
                        Log::warning("FCM v1 Individual Send Error: " . $res->body());
                    }
                } catch (\Throwable $e) {
                    $failed++;
                    Log::error("FCM v1 Send Exception: " . $e->getMessage());
                }
            }

            return [
                'status'  => $sent > 0,
                'api'     => 'fcm_v1',
                'sent'    => $sent,
                'failed'  => $failed,
                'tokens'  => count($tokens),
            ];
        }

        // 2. Try FCM Legacy HTTP API via Server Key
        $serverKey = static::getServerKey($targetApp);
        if ($serverKey) {
            try {
                $payload = [
                    'registration_ids' => array_values($tokens),
                    'priority'         => $priority,
                    'notification'     => [
                        'title'        => $title,
                        'body'         => $body,
                        'image'        => $imageUrl ?: null,
                        'sound'        => $isCall ? 'call_ringtone' : 'default',
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'badge'        => '1',
                    ],
                    'data'             => array_merge($data, [
                        'title'        => $title,
                        'body'         => $body,
                        'is_call'      => $isCall ? 'true' : 'false',
                        'action_url'   => $actionUrl ?: '',
                        'image_url'    => $imageUrl ?: '',
                    ]),
                    'android'          => [
                        'priority'     => 'high',
                        'ttl'          => $isCall ? '45s' : '86400s',
                        'notification' => [
                            'channel_id' => $isCall ? 'chinchins_call_channel' : 'chinchins_messages_channel',
                            'sound'      => $isCall ? 'call_ringtone' : 'default',
                        ],
                    ],
                ];

                $response = Http::withHeaders([
                    'Authorization' => 'key=' . $serverKey,
                    'Content-Type'  => 'application/json',
                ])->connectTimeout(1)->timeout(2)->post('https://fcm.googleapis.com/fcm/send', $payload);

                $result = $response->json();
                $successCount = $result['success'] ?? 0;
                $failureCount = $result['failure'] ?? 0;

                return [
                    'status'  => $response->successful() && $successCount > 0,
                    'api'     => 'legacy_fcm',
                    'code'    => $response->status(),
                    'sent'    => $successCount,
                    'failed'  => $failureCount,
                    'tokens'  => count($tokens),
                    'data'    => $result,
                ];
            } catch (\Throwable $e) {
                Log::error("PushNotification Legacy Exception: " . $e->getMessage());
                return [
                    'status'  => false,
                    'sent'    => 0,
                    'failed'  => count($tokens),
                    'message' => $e->getMessage(),
                ];
            }
        }

        // Mock Dispatch (Neither JSON nor Server Key configured)
        Log::info("PushNotification: Neither FCM JSON nor Server Key configured. (Tokens: " . count($tokens) . ")");
        return [
            'status'       => true,
            'mock'         => true,
            'sent'         => count($tokens),
            'failed'       => 0,
            'message'      => 'Dispatched (FCM credentials not configured, simulated successfully).',
            'tokens'       => count($tokens),
        ];
    }

    /**
     * Dispatch OneSignal Push Notification.
     */
    public static function sendOneSignal(
        bool $isAll,
        array $targetUserIds,
        string $title,
        string $body,
        ?string $imageUrl = null,
        ?string $actionUrl = null,
        array $data = []
    ): array {
        $appId = AppSetting::get('onesignal_app_id') ?? env('ONESIGNAL_APP_ID');
        $restKey = AppSetting::get('onesignal_rest_api_key') ?? env('ONESIGNAL_REST_API_KEY');

        if (!$appId || !$restKey) {
            return [
                'status'  => false,
                'message' => 'OneSignal App ID or REST API Key not configured.',
            ];
        }

        try {
            $payload = [
                'app_id'   => $appId,
                'headings' => ['en' => $title],
                'contents' => ['en' => $body],
                'data'     => array_merge($data, [
                    'action_url' => $actionUrl ?: '',
                ]),
            ];

            if ($imageUrl) {
                $payload['big_picture'] = $imageUrl;
                $payload['ios_attachments'] = ['id' => $imageUrl];
            }

            if ($isAll) {
                $payload['included_segments'] = ['Total Subscriptions', 'All'];
            } else {
                $payload['include_external_user_ids'] = array_map('strval', $targetUserIds);
            }

            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $restKey,
                'Content-Type'  => 'application/json',
            ])->timeout(10)->post('https://onesignal.com/api/v1/notifications', $payload);

            return [
                'status'     => $response->successful(),
                'code'       => $response->status(),
                'recipients' => $response->json('recipients', 1),
                'data'       => $response->json(),
            ];
        } catch (\Throwable $e) {
            Log::error("OneSignal Push Exception: " . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Non-blocking Queue Dispatch for Chat Message Push Notifications (< 1ms execution).
     */
    public static function queueChatMessagePush($message, User $sender, User $receiver, ?string $overrideText = null, ?string $overrideImage = null): void
    {
        dispatch(new \App\Jobs\SendPushNotificationJob('chat_message', [
            'message_id'     => is_object($message) ? ($message->id ?? null) : null,
            'message_type'   => is_object($message) ? ($message->type ?? 'text') : 'text',
            'content'        => is_object($message) ? ($message->message ?? $message->content ?? '') : (string) $message,
            'media_url'      => is_object($message) ? ($message->media_url ?? $message->image_url ?? null) : null,
            'sender_id'      => $sender->id,
            'receiver_id'    => $receiver->id,
            'override_text'  => $overrideText,
            'override_image' => $overrideImage,
        ]));
    }

    /**
     * Non-blocking Queue Dispatch for Profile View Push Notifications.
     */
    public static function queueProfileViewPush(User $viewer, User $host): void
    {
        dispatch(new \App\Jobs\SendPushNotificationJob('profile_view', [
            'viewer_id' => $viewer->id,
            'host_id'   => $host->id,
        ]));
    }

    /**
     * Non-blocking Queue Dispatch for Gift Push Notifications.
     */
    public static function queueGiftPush(User $sender, User $receiver, Gift $gift, int $coins): void
    {
        dispatch(new \App\Jobs\SendPushNotificationJob('gift_received', [
            'sender_id'   => $sender->id,
            'receiver_id' => $receiver->id,
            'gift_id'     => $gift->id,
            'coins'       => $coins,
        ]));
    }

    /**
     * Non-blocking Queue Dispatch for Party Room Live Stage Invites.
     */
    public static function queueLivePartyInvite(User $user, string $hostName, string $roomTitle, int $roomId): void
    {
        dispatch(new \App\Jobs\SendPushNotificationJob('party_invite', [
            'user_id'    => $user->id,
            'host_name'  => $hostName,
            'room_title' => $roomTitle,
            'room_id'    => $roomId,
        ]));
    }

    /**
     * Execute queued asynchronous push notifications in worker thread.
     */
    public static function executeAsyncPush(string $actionType, array $payload): void
    {
        switch ($actionType) {
            case 'chat_message':
                $sender = User::find($payload['sender_id'] ?? 0);
                $receiver = User::find($payload['receiver_id'] ?? 0);
                if (!$sender || !$receiver) return;

                $msgObj = (object) [
                    'id'        => $payload['message_id'] ?? null,
                    'type'      => $payload['message_type'] ?? 'text',
                    'message'   => $payload['content'] ?? '',
                    'media_url' => $payload['media_url'] ?? null,
                ];
                static::sendChatMessagePush($msgObj, $sender, $receiver, $payload['override_text'] ?? null, $payload['override_image'] ?? null);
                break;

            case 'profile_view':
                $viewer = User::find($payload['viewer_id'] ?? 0);
                $host = User::find($payload['host_id'] ?? 0);
                if ($viewer && $host) {
                    static::sendProfileViewPush($viewer, $host);
                }
                break;

            case 'gift_received':
                $sender = User::find($payload['sender_id'] ?? 0);
                $receiver = User::find($payload['receiver_id'] ?? 0);
                $gift = Gift::find($payload['gift_id'] ?? 0);
                if ($sender && $receiver && $gift) {
                    static::sendGiftPush($sender, $receiver, $gift, (int) ($payload['coins'] ?? 0));
                }
                break;

            case 'party_invite':
                $user = User::find($payload['user_id'] ?? 0);
                if ($user) {
                    static::sendLivePartyInvite(
                        $user,
                        $payload['host_name'] ?? 'Host',
                        $payload['room_title'] ?? 'Live Voice Party',
                        (int) ($payload['room_id'] ?? 0)
                    );
                }
                break;

            case 'custom_push':
                static::sendPush(
                    platform: $payload['platform'] ?? 'firebase',
                    targetUserIds: $payload['target_user_ids'] ?? [],
                    title: $payload['title'] ?? '',
                    body: $payload['body'] ?? '',
                    imageUrl: $payload['image_url'] ?? null,
                    actionUrl: $payload['action_url'] ?? null,
                    firebaseAppId: $payload['firebase_app_id'] ?? null,
                    extraData: $payload['extra_data'] ?? []
                );
                break;
        }
    }
}
