<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\CallSession;
use App\Models\CallSetting;
use App\Models\ChatMessage;
use App\Models\DeviceRegistration;
use App\Models\Gift;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    /**
     * Get configured FCM Server Key.
     */
    public static function getServerKey(): ?string
    {
        return AppSetting::get('fcm_server_key') 
            ?? config('services.fcm.key') 
            ?? env('FCM_SERVER_KEY');
    }

    /**
     * Get all active FCM tokens for a user across all registered devices.
     */
    public static function getUserTokens(int $userId): array
    {
        $tokens = [];

        // 1. From device_registrations table
        $deviceTokens = DeviceRegistration::where('user_id', $userId)
            ->where('is_active', true)
            ->pluck('fcm_token')
            ->toArray();
        
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
     * Rings receiver's mobile device with IMO / WhatsApp-like incoming call screen.
     */
    public static function sendIncomingCallPush(CallSession $call, User $caller, User $receiver): array
    {
        $tokens = static::getUserTokens($receiver->id);
        if (empty($tokens)) {
            Log::info("PushNotification: No device tokens found for receiver ID {$receiver->id}");
            return ['status' => false, 'message' => 'No active device tokens found.'];
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
            'caller_account_id'   => (string) $caller->account_id,
            'caller_name'         => (string) $caller->display_name,
            'caller_avatar'       => (string) ($caller->avatar_url ?: ''),
            'rate_per_minute'     => (string) ($call->rate_per_minute ?: 100),
            'is_free_trial'       => $call->is_free_trial ? '1' : '0',
            'free_duration'       => (string) ($call->free_duration_seconds ?: 0),
            'ringtone_url'        => (string) $incomingRingtoneUrl,
            'ring_timeout'        => '45',
            'timestamp'           => (string) time(),
        ];

        $title = "Incoming " . ucfirst($call->call_type ?: 'Video') . " Call";
        $body = "{$caller->display_name} is calling you...";

        return static::sendToTokens(
            tokens: $tokens,
            title: $title,
            body: $body,
            data: $dataPayload,
            priority: 'high',
            isCall: true
        );
    }

    /**
     * Send Instant Chat Message Push Notification (like TikTok / WhatsApp).
     */
    public static function sendChatMessagePush(ChatMessage $message, User $sender, User $receiver): array
    {
        $tokens = static::getUserTokens($receiver->id);
        if (empty($tokens)) {
            return ['status' => false, 'message' => 'No active device tokens found.'];
        }

        $bodyText = match ($message->type) {
            'image' => '📷 Sent a photo',
            'voice' => '🎤 Sent a voice message',
            'gift'  => '🎁 Sent you a gift',
            default => $message->message ?: 'New message',
        };

        $dataPayload = [
            'action'      => 'CHAT_MESSAGE',
            'type'        => 'chat_message',
            'message_id'  => (string) $message->id,
            'sender_id'   => (string) $sender->id,
            'sender_name' => (string) $sender->display_name,
            'sender_avatar' => (string) ($sender->avatar_url ?: ''),
            'message_type' => (string) $message->type,
            'text'        => (string) $bodyText,
            'timestamp'   => (string) time(),
        ];

        return static::sendToTokens(
            tokens: $tokens,
            title: $sender->display_name,
            body: $bodyText,
            data: $dataPayload,
            priority: 'high'
        );
    }

    /**
     * Send Profile View Push Notification.
     */
    public static function sendProfileViewPush(User $viewer, User $host): array
    {
        $tokens = static::getUserTokens($host->id);
        if (empty($tokens)) {
            return ['status' => false, 'message' => 'No active device tokens found.'];
        }

        $title = "New Profile Visitor 👁️";
        $body = "{$viewer->display_name} viewed your profile! Say hi!";

        $dataPayload = [
            'action'        => 'PROFILE_VIEW',
            'type'          => 'profile_view',
            'viewer_id'     => (string) $viewer->id,
            'viewer_name'   => (string) $viewer->display_name,
            'viewer_avatar' => (string) ($viewer->avatar_url ?: ''),
            'timestamp'     => (string) time(),
        ];

        return static::sendToTokens(
            tokens: $tokens,
            title: $title,
            body: $body,
            data: $dataPayload,
            priority: 'normal'
        );
    }

    /**
     * Send Gift Received Push Notification.
     */
    public static function sendGiftPush(User $sender, User $receiver, Gift $gift, int $coins = 0): array
    {
        $tokens = static::getUserTokens($receiver->id);
        if (empty($tokens)) {
            return ['status' => false, 'message' => 'No active device tokens found.'];
        }

        $title = "Gift Received! 🎁";
        $body = "{$sender->display_name} sent you a {$gift->name} (+{$coins} coins)!";

        $dataPayload = [
            'action'      => 'GIFT_RECEIVED',
            'type'        => 'gift_received',
            'sender_id'   => (string) $sender->id,
            'sender_name' => (string) $sender->display_name,
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
            priority: 'high'
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
            return ['status' => false, 'message' => 'No active device tokens to broadcast.'];
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
     * Dispatch FCM Push Payload via Firebase HTTP Gateway.
     */
    public static function sendToTokens(
        array $tokens,
        string $title,
        string $body,
        array $data = [],
        string $priority = 'high',
        bool $isCall = false
    ): array {
        $serverKey = static::getServerKey();

        if (!$serverKey) {
            Log::info("PushNotification: FCM Server key not configured in settings. Skipping external dispatch. (Tokens: " . count($tokens) . ")");
            return [
                'status'  => true,
                'mock'    => true,
                'message' => 'Push notification dispatched (Server key not set, mocked successfully).',
                'target_count' => count($tokens),
                'payload' => $data,
            ];
        }

        try {
            // FCM Legacy / Standard HTTP Payload
            $payload = [
                'registration_ids' => array_values($tokens),
                'priority'         => $priority,
                'notification'     => [
                    'title'        => $title,
                    'body'         => $body,
                    'sound'        => $isCall ? 'call_ringtone' : 'default',
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'badge'        => '1',
                ],
                'data'             => array_merge($data, [
                    'title'        => $title,
                    'body'         => $body,
                    'is_call'      => $isCall ? 'true' : 'false',
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
            ])->timeout(10)->post('https://fcm.googleapis.com/fcm/send', $payload);

            $result = $response->json();
            Log::info("PushNotification FCM Response: ", ['status' => $response->status(), 'res' => $result]);

            return [
                'status'  => $response->successful(),
                'code'    => $response->status(),
                'data'    => $result,
                'tokens'  => count($tokens),
            ];
        } catch (\Throwable $e) {
            Log::error("PushNotification Exception: " . $e->getMessage());
            return [
                'status'  => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
