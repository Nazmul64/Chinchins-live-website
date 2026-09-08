<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\BlockedUser;
use App\Models\CallSetting;
use App\Models\ChatMessage;
use App\Models\CoinPackage;
use App\Models\CoinTransaction;
use App\Models\Notification;
use App\Models\PaymentMethod;
use App\Models\ProfileView;
use App\Models\User;
use App\Models\UserReport;
use App\Services\PushNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MessageApiController extends Controller
{
    /**
     * Resolve the target user from Sanctum token, header, or fallback parameters.
     */
    protected function resolveUser(Request $request): ?User
    {
        $token = $request->bearerToken() 
              ?: $request->header('Authorization') 
              ?: $request->input('token') 
              ?: $request->input('auth_token');

        if ($token) {
            $tokenClean = trim(str_replace(['Bearer', 'bearer'], '', $token));
            $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($tokenClean);
            if ($accessToken && $accessToken->tokenable) {
                return $accessToken->tokenable;
            }
        }

        if ($request->user('sanctum')) {
            return $request->user('sanctum');
        }

        if ($request->user()) {
            return $request->user();
        }

        $headerUserId = $request->header('X-User-Id') 
                     ?? $request->header('User-Id') 
                     ?? $request->header('userId')
                     ?? $request->header('X-Account-Id')
                     ?? $request->header('Account-Id');

        if ($headerUserId) {
            $u = User::find($headerUserId) ?? User::where('account_id', $headerUserId)->first();
            if ($u) return $u;
        }

        $idParam = $request->input('user_id') ?? $request->input('userId') ?? $request->input('id') ?? $request->input('sender_id');
        if ($idParam) {
            $u = User::find($idParam) ?? User::where('account_id', $idParam)->first();
            if ($u) return $u;
        }

        return null;
    }

    /**
     * Get Conversations / Inbox List with Unread Badges and Latest Message Previews.
     * GET /api/messages or GET /api/messages/conversations or GET /api/chat/conversations
     */
    public function getConversations(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated. Pass Authorization token or user_id.',
            ], 401);
        }

        // Get distinct user IDs that user interacted with
        $sentTo = ChatMessage::where('sender_id', $user->id)->pluck('receiver_id');
        $receivedFrom = ChatMessage::where('receiver_id', $user->id)->pluck('sender_id');
        $contactIds = $sentTo->merge($receivedFrom)->unique()->values();

        // If no prior chat messages, fetch active hosts as initial suggestions
        if ($contactIds->isEmpty()) {
            $suggestedHosts = User::where('id', '!=', $user->id)
                ->where('is_active', true)
                ->take(10)
                ->get();
            $contactIds = $suggestedHosts->pluck('id');
        }

        $conversations = [];
        $totalUnreadCount = 0;

        foreach ($contactIds as $contactId) {
            $contact = User::find($contactId);
            if (!$contact) continue;

            $lastMessage = ChatMessage::where(function ($q) use ($user, $contactId) {
                $q->where('sender_id', $user->id)->where('receiver_id', $contactId);
            })->orWhere(function ($q) use ($user, $contactId) {
                $q->where('sender_id', $contactId)->where('receiver_id', $user->id);
            })->latest()->first();

            $unreadCount = ChatMessage::where('sender_id', $contactId)
                ->where('receiver_id', $user->id)
                ->where('is_read', false)
                ->count();

            $totalUnreadCount += $unreadCount;

            $preview = 'Start chatting';
            $previewType = 'text';
            $timestamp = 'Recently';

            if ($lastMessage) {
                $previewType = $lastMessage->type;
                if ($lastMessage->type === 'video_call') {
                    $preview = '[Video Call]';
                } elseif ($lastMessage->type === 'audio_call') {
                    $preview = '[Audio Call]';
                } elseif ($lastMessage->type === 'image' || $lastMessage->type === 'profile_picture') {
                    $preview = '[Image]';
                } elseif ($lastMessage->type === 'voice') {
                    $preview = '[Voice Note]';
                } elseif ($lastMessage->type === 'emoji') {
                    $preview = $lastMessage->message ?: '😊';
                } else {
                    $preview = $lastMessage->message ?: 'Message';
                }

                if ($lastMessage->created_at->isToday()) {
                    if ($lastMessage->created_at->diffInMinutes(now()) < 60) {
                        $mins = max(1, $lastMessage->created_at->diffInMinutes(now()));
                        $timestamp = "{$mins} minutes";
                    } else {
                        $timestamp = $lastMessage->created_at->format('H:i');
                    }
                } elseif ($lastMessage->created_at->isYesterday()) {
                    $timestamp = 'Yesterday';
                } else {
                    $timestamp = $lastMessage->created_at->format('M d');
                }
            }

            $conversations[] = [
                'user_id'         => $contact->id,
                'account_id'      => $contact->account_id,
                'name'            => $contact->display_name,
                'avatar_url'      => $contact->avatar_url,
                'is_online'       => (bool) $contact->is_online,
                'is_busy'         => (bool) $contact->is_busy,
                'unread_count'    => $unreadCount,
                'last_message'    => [
                    'text'       => $preview,
                    'type'       => $previewType,
                    'time'       => $timestamp,
                    'media_url'  => $lastMessage ? $lastMessage->media_url : null,
                    'created_at' => $lastMessage ? $lastMessage->created_at->toIso8601String() : null,
                ],
                'video_call_rate' => (int) ($contact->video_call_rate ?: 100),
            ];
        }

        // Sort by latest message date if available (latest on top)
        usort($conversations, function ($a, $b) {
            $tA = $a['last_message']['created_at'] ?? '';
            $tB = $b['last_message']['created_at'] ?? '';
            return strcmp($tB, $tA);
        });

        $freeLimit = (int) AppSetting::get('free_messages_limit', $user->free_messages_limit ?? 5);
        $freeUsed = $user->free_messages_used ?? 0;

        return response()->json([
            'status'  => true,
            'message' => 'Conversations loaded successfully.',
            'data'    => [
                'total_unread_badge'      => $totalUnreadCount,
                'free_messages_limit'     => $freeLimit,
                'free_messages_remaining' => max(0, $freeLimit - $freeUsed),
                'user_coins'              => (int) $user->coins,
                'conversations'           => $conversations,
            ],
        ], 200);
    }

    /**
     * Get Chat History with a Specific User.
     * GET /api/messages/{userId} or GET /api/chat/{userId}
     */
    public function getMessages(Request $request, int $userId): JsonResponse
    {
        $currentUser = $this->resolveUser($request);

        if (!$currentUser) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated. Pass Authorization token or user_id.',
            ], 401);
        }

        $otherUser = User::find($userId) ?? User::where('account_id', $userId)->first();
        if (!$otherUser) {
            return response()->json([
                'status'  => false,
                'message' => 'Chat partner not found.',
            ], 404);
        }

        // Mark incoming unread messages as read
        ChatMessage::where('sender_id', $otherUser->id)
            ->where('receiver_id', $currentUser->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        $perPage = (int) $request->input('per_page', 50);
        $messages = ChatMessage::between($currentUser->id, $otherUser->id)
            ->orderBy('created_at', 'asc')
            ->paginate($perPage);

        $freeLimit = (int) AppSetting::get('free_messages_limit', $currentUser->free_messages_limit ?? 5);
        $freeRemaining = max(0, $freeLimit - ($currentUser->free_messages_used ?? 0));
        $coinCost = (int) AppSetting::get('message_coin_cost', 5);

        $isBlockedByMe = $currentUser->hasBlocked($otherUser->id);
        $isBlockedByThem = $otherUser->hasBlocked($currentUser->id);

        $level = $otherUser->level ?: 'Lv. 1';
        $country = $otherUser->country ?: 'Bangladesh';
        $flag = $otherUser->country_flag ?: '🇧🇩';
        $age = $otherUser->display_age;
        $gender = strtolower($otherUser->gender ?: 'female');
        $genderIcon = $gender === 'male' ? '♂' : '♀';
        $genderText = ucfirst($gender);
        $greeting = $otherUser->introduction ?: 'Hey handsome! Thanks for visiting my profile ❤️';

        $chatPartner = [
            'id'                     => $otherUser->id,
            'account_id'             => $otherUser->account_id,
            'name'                   => $otherUser->display_name,
            'avatar_url'             => $otherUser->avatar_url,
            'is_online'              => (bool) $otherUser->is_online,
            'is_busy'                => (bool) $otherUser->is_busy,
            'video_call_rate'        => (int) ($otherUser->video_call_rate ?: 1800),
            'level'                  => $level,
            'level_number'           => (int) preg_replace('/[^0-9]/', '', $level) ?: 1,
            'level_badge_url'        => $otherUser->level_info['badge_image_url'] ?? asset('uploads/bases/badge_level_1.svg'),
            'badge_color'            => $otherUser->badge_color,
            'badge_icon'             => $otherUser->badge_icon,
            'country'                => $country,
            'country_flag'           => $flag,
            'age'                    => $age,
            'gender'                 => $gender,
            'gender_icon'            => $genderIcon,
            'bio'                    => $greeting,
            'greeting_message'       => $greeting,
            'is_blocked_by_me'       => $isBlockedByMe,
            'is_blocked_by_them'     => $isBlockedByThem,
            'header_card'            => [
                'name'               => $otherUser->display_name,
                'star_icon'          => '⭐',
                'level'              => $level,
                'country_flag'       => $flag,
                'country_name'       => $country,
                'age'                => $age,
                'gender_text'        => $genderText,
                'gender_icon'        => $genderIcon,
                'summary_text'       => "{$flag} {$country} • {$age} yrs • {$genderIcon} {$genderText} • {$level}",
                'greeting_text'      => $greeting,
            ],
            'menu_options'           => [
                ['id' => 'block', 'title' => $isBlockedByMe ? 'Unblock User' : 'Block User', 'action' => $isBlockedByMe ? 'unblock' : 'block'],
                ['id' => 'report', 'title' => 'Report User', 'action' => 'report'],
                ['id' => 'cancel', 'title' => 'Cancel', 'action' => 'cancel'],
            ],
        ];

        return response()->json([
            'status'  => true,
            'message' => 'Messages retrieved successfully.',
            'data'    => [
                'chat_partner' => $chatPartner,
                'free_messages_remaining' => $freeRemaining,
                'user_coins'              => (int) $currentUser->coins,
                'message_cost_after_free' => $coinCost,
                'messages'                => $messages->items(),
                'pagination'              => [
                    'current_page' => $messages->currentPage(),
                    'last_page'    => $messages->lastPage(),
                    'total'        => $messages->total(),
                ],
            ],
        ], 200);
    }

    /**
     * Standalone Upload Media for Chat (Images, Profile Pictures, Voice Audios).
     * Uploads media directly to public/uploads/chat_messages/
     * POST /api/messages/upload or POST /api/chat/upload
     */
    public function uploadMedia(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated. Pass Authorization token or user_id.',
            ], 401);
        }

        $chatUploadDir = public_path('uploads/chat_messages');
        if (!File::exists($chatUploadDir)) {
            File::makeDirectory($chatUploadDir, 0777, true, true);
        }

        $file = $request->file('file') 
             ?? $request->file('image') 
             ?? $request->file('image_file') 
             ?? $request->file('photo') 
             ?? $request->file('picture') 
             ?? $request->file('avatar') 
             ?? $request->file('profile_picture') 
             ?? $request->file('voice') 
             ?? $request->file('voice_file') 
             ?? $request->file('audio') 
             ?? $request->file('audio_file');

        if (!$file || !$file->isValid()) {
            return response()->json([
                'status'  => false,
                'message' => 'No valid media file provided. Attach file with key `file`, `image`, or `voice`.',
            ], 422);
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: 'bin');
        $mime = $file->getMimeType();

        $isAudio = in_array($extension, ['mp3', 'wav', 'm4a', 'aac', 'ogg', 'webm', '3gp', 'amr', 'opus']) 
                || str_starts_with($mime, 'audio/');

        $prefix = $isAudio ? 'voice_' : 'msg_img_';
        $filename = $prefix . time() . '_' . Str::random(8) . '.' . $extension;

        $file->move($chatUploadDir, $filename);

        $relativeUrl = 'uploads/chat_messages/' . $filename;
        $fullUrl = asset($relativeUrl);

        return response()->json([
            'status'  => true,
            'message' => 'Media uploaded successfully to public/uploads/chat_messages.',
            'data'    => [
                'type'       => $isAudio ? 'voice' : 'image',
                'filename'   => $filename,
                'file_path'  => $relativeUrl,
                'media_url'  => $fullUrl,
                'url'        => $fullUrl,
                'extension'  => $extension,
                'mime_type'  => $mime,
                'file_size'  => File::size($chatUploadDir . '/' . $filename),
            ],
        ], 200);
    }

    /**
     * Send Message (Text, Emojis, Voice Audio, Image, Profile Picture) with Free Quota & Coin Balance Check.
     * Uploads media directly to public/uploads/chat_messages/.
     * POST /api/messages/send or POST /api/chat/send
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $sender = $this->resolveUser($request);

        if (!$sender) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated. Pass Authorization token or user_id.',
            ], 401);
        }

        $receiverId = $request->input('receiver_id') 
                   ?? $request->input('receiverId') 
                   ?? $request->input('to_user_id') 
                   ?? $request->input('user_id');

        if (!$receiverId) {
            return response()->json([
                'status'  => false,
                'message' => 'The receiver_id field is required.',
            ], 422);
        }

        $receiver = User::find($receiverId) ?? User::where('account_id', $receiverId)->first();
        if (!$receiver) {
            return response()->json([
                'status'  => false,
                'message' => 'Receiver user not found.',
            ], 404);
        }

        // ==========================================
        // 🚫 Peer-to-Peer Block Check
        // ==========================================
        if ($sender->hasBlocked($receiver->id)) {
            return response()->json([
                'status'  => false,
                'code'    => 'USER_BLOCKED',
                'message' => 'You have blocked this user. Unblock them in order to send messages.',
            ], 403);
        }

        if ($receiver->hasBlocked($sender->id)) {
            return response()->json([
                'status'  => false,
                'code'    => 'BLOCKED_BY_USER',
                'message' => 'You cannot send messages to this user.',
            ], 403);
        }

        // ==========================================
        // 🔒 Free Message Limit & Coin Balance Check
        // ==========================================
        $freeLimit = (int) AppSetting::get('free_messages_limit', $sender->free_messages_limit ?? 5);
        $freeUsed = $sender->free_messages_used ?? 0;
        $isFree = $freeUsed < $freeLimit;
        $coinCost = (int) AppSetting::get('message_coin_cost', 5);

        if (!$isFree) {
            // Check if sender has enough coins to pay for message
            if ($sender->coins < $coinCost) {
                $modalData = $this->buildRechargeModalData($sender, $receiver, $coinCost, 'chat');

                return response()->json([
                    'status'                  => false,
                    'code'                    => 'MESSAGE_LIMIT_REACHED',
                    'message'                 => "You have reached your free limit of {$freeLimit} messages. Please recharge coins to continue chatting.",
                    'is_limit_reached'        => true,
                    'redirect_to_deposit'     => true,
                    'current_coins'           => (int) $sender->coins,
                    'user_gems'               => (int) $sender->coins,
                    'required_coins'          => $coinCost,
                    'free_messages_used'      => $freeUsed,
                    'free_messages_limit'     => $freeLimit,
                    'free_messages_remaining' => 0,
                    'show_recharge_modal'     => true,
                    'recharge_modal_data'     => $modalData,
                    'modal'                   => $modalData,
                    'packages'                => $modalData['packages'],
                    'coin_packages'           => $modalData['packages'],
                    'target_user'             => $modalData['target_user'],
                ], 200);
            }

            // Deduct coins for paid message
            $sender->decrement('coins', $coinCost);

            // Record transaction
            CoinTransaction::create([
                'user_id'       => $sender->id,
                'type'          => 'admin_deduct',
                'amount'        => -$coinCost,
                'balance_after' => (int) $sender->fresh()->coins,
                'description'   => "Message sent to {$receiver->display_name}",
            ]);
        } else {
            // Increment free messages used
            $sender->increment('free_messages_used');
        }

        // ==========================================
        // 📁 Handle Media Uploads to public/uploads/chat_messages
        // ==========================================
        $chatUploadDir = public_path('uploads/chat_messages');
        if (!File::exists($chatUploadDir)) {
            File::makeDirectory($chatUploadDir, 0777, true, true);
        }

        $mediaUrl = $request->input('media_url') ?? $request->input('url');
        $rawType = $request->input('type');
        $type = 'text';

        // Check for Audio / Voice file upload
        $voiceFile = $request->file('voice_file') 
                  ?? $request->file('audio_file') 
                  ?? $request->file('voice') 
                  ?? $request->file('audio')
                  ?? $request->file('recording')
                  ?? $request->file('voice_note');

        // Check for Image / Photo / Profile Picture file upload
        $imgFile = $request->file('image_file') 
                ?? $request->file('image') 
                ?? $request->file('photo') 
                ?? $request->file('picture') 
                ?? $request->file('avatar') 
                ?? $request->file('profile_picture') 
                ?? $request->file('file')
                ?? $request->file('media_file');

        if ($voiceFile && $voiceFile->isValid()) {
            $ext = strtolower($voiceFile->getClientOriginalExtension() ?: 'mp3');
            $filename = 'voice_' . time() . '_' . Str::random(8) . '.' . $ext;
            $voiceFile->move($chatUploadDir, $filename);
            $mediaUrl = asset('uploads/chat_messages/' . $filename);
            $type = 'voice';
        } elseif ($imgFile && $imgFile->isValid()) {
            $ext = strtolower($imgFile->getClientOriginalExtension() ?: 'jpg');
            $prefix = ($rawType === 'profile_picture' || $request->hasFile('profile_picture') || $request->hasFile('avatar')) ? 'msg_avatar_' : 'msg_img_';
            $filename = $prefix . time() . '_' . Str::random(8) . '.' . $ext;
            $imgFile->move($chatUploadDir, $filename);
            $mediaUrl = asset('uploads/chat_messages/' . $filename);
            $type = ($rawType === 'profile_picture') ? 'profile_picture' : 'image';
        } elseif (!empty($rawType)) {
            $type = $rawType;
        }

        $messageText = $request->input('message') 
                    ?? $request->input('text') 
                    ?? $request->input('content') 
                    ?? $request->input('emoji') 
                    ?? $request->input('caption');

        $isEmojiOnly = false;
        if (!empty($messageText)) {
            $clean = preg_replace('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{2300}-\x{23FF}\x{2B50}\x{2B55}\x{200D}\x{FE0F}\x{FE0E}\s]/u', '', trim($messageText));
            if ($clean === '' && mb_strlen(trim($messageText)) > 0) {
                $isEmojiOnly = true;
            }
        }

        if ($rawType === 'emoji' || $request->has('emoji') || $isEmojiOnly) {
            if ($type === 'text') {
                $type = 'emoji';
            }
        }

        if (empty($messageText)) {
            if ($type === 'voice') {
                $messageText = '[Voice Note]';
            } elseif ($type === 'image' || $type === 'profile_picture') {
                $messageText = '[Image]';
            } elseif ($type === 'emoji') {
                $messageText = '😊';
            }
        }

        $duration = (int) ($request->input('duration') ?? $request->input('voice_duration') ?? $request->input('audio_duration') ?? 0);

        $chatMessage = ChatMessage::create([
            'sender_id'   => $sender->id,
            'receiver_id' => $receiver->id,
            'type'        => $type,
            'message'     => $messageText,
            'media_url'   => $mediaUrl,
            'duration'    => $duration,
            'is_read'     => false,
            'is_free'     => $isFree,
            'coin_cost'   => $isFree ? 0 : $coinCost,
        ]);

        // ==========================================
        // 🔔 Create In-App Notification for Receiver
        // ==========================================
        $notificationSnippet = match($type) {
            'voice'           => '🎤 Sent you a voice message',
            'image'           => '📷 Sent you a photo',
            'profile_picture' => '🖼️ Sent you a profile picture',
            'emoji'           => "{$messageText} Sent you an emoji",
            default           => Str::limit($messageText, 60),
        };

        Notification::createNotification(
            userId: $receiver->id,
            actorId: $sender->id,
            type: 'message',
            title: "Message from {$sender->display_name}",
            message: $notificationSnippet,
            data: [
                'message_id'   => $chatMessage->id,
                'sender_id'    => $sender->id,
                'sender_name'  => $sender->display_name,
                'avatar_url'   => $sender->avatar_url,
                'type'         => $type,
                'media_url'    => $mediaUrl,
                'created_at'   => $chatMessage->created_at->toIso8601String(),
            ]
        );

        // 📲 Trigger Real-Time FCM Push Notification to Receiver's Mobile Device
        try {
            PushNotificationService::sendChatMessagePush($chatMessage, $sender, $receiver);
        } catch (\Throwable $e) {
            Log::error("Chat message push notification error: " . $e->getMessage());
        }

        $remainingFree = max(0, $freeLimit - ($sender->fresh()->free_messages_used ?? 0));

        return response()->json([
            'status'  => true,
            'message' => 'Message sent successfully.',
            'data'    => [
                'chat_message' => [
                    'id'          => $chatMessage->id,
                    'sender_id'   => $chatMessage->sender_id,
                    'receiver_id' => $chatMessage->receiver_id,
                    'type'        => $chatMessage->type,
                    'message'     => $chatMessage->message,
                    'media_url'   => $chatMessage->media_url,
                    'duration'    => $chatMessage->duration,
                    'is_read'     => (bool) $chatMessage->is_read,
                    'is_free'     => (bool) $chatMessage->is_free,
                    'coin_cost'   => $chatMessage->coin_cost,
                    'created_at'  => $chatMessage->created_at->toIso8601String(),
                ],
                'sender'       => [
                    'id'                      => $sender->id,
                    'name'                    => $sender->display_name,
                    'coins'                   => (int) $sender->fresh()->coins,
                    'free_messages_remaining' => $remainingFree,
                ],
            ],
        ], 201);
    }

    /**
     * Mark Messages as Read.
     * POST /api/messages/read or POST /api/chat/read
     */
    public function markAsRead(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $senderId = $request->input('sender_id') ?? $request->input('user_id') ?? $request->input('partner_id');
        if ($senderId) {
            ChatMessage::where('sender_id', $senderId)
                ->where('receiver_id', $user->id)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
        } else {
            ChatMessage::where('receiver_id', $user->id)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Messages marked as read.',
        ]);
    }

    /**
     * Record Profile View & Trigger Automatic Real-Time Notification & Auto-Callback / Greeting.
     * When user views someone's profile, the profile owner receives an automatic notification and greeting!
     * POST /api/profile/{id}/view or POST /api/profile/view or POST /api/user/view-profile
     */
    public function recordProfileView(Request $request, $id = null): JsonResponse
    {
        $viewer = $this->resolveUser($request);
        $hostId = $id ?? $request->input('host_id') ?? $request->input('id') ?? $request->input('profile_id') ?? $request->input('user_id');

        if (!$viewer) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $host = User::find($hostId) ?? User::where('account_id', $hostId)->first();
        if (!$host) {
            return response()->json(['status' => false, 'message' => 'Host profile not found.'], 404);
        }

        // ==========================================
        // 🛑 Self-Profile Check: If user views their OWN profile, DO NOT trigger callback or notification!
        // ==========================================
        if ($viewer->id === $host->id) {
            return response()->json([
                'status'  => true,
                'message' => 'Viewing own profile. Auto-callback is disabled for self.',
                'data'    => [
                    'is_self'  => true,
                    'host'     => [
                        'id'           => $host->id,
                        'account_id'   => $host->account_id,
                        'display_name' => $host->display_name,
                        'avatar_url'   => $host->avatar_url,
                    ],
                    'callback' => [
                        'auto_call_triggered' => false,
                        'host_is_available'   => false,
                        'trigger_action'      => 'NONE',
                    ],
                ],
            ], 200);
        }

        // Check if host is currently available (online and not busy talking to someone else)
        $isHostAvailable = (bool) $host->is_online && !(bool) $host->is_busy;

        // Record profile view in ledger
        $profileView = ProfileView::create([
            'viewer_id'           => $viewer->id,
            'host_id'             => $host->id,
            'auto_call_triggered' => $isHostAvailable,
            'callback_requested'  => true,
            'status'              => $isHostAvailable ? 'callback_ready' : 'host_busy',
            'viewed_at'           => now(),
        ]);

        // ==========================================
        // 🔔 Send Automatic Profile Visitor Notification to Host
        // ==========================================
        $notification = Notification::createNotification(
            userId: $host->id,
            actorId: $viewer->id,
            type: 'profile_view',
            title: 'New Profile Visitor 👁️',
            message: "{$viewer->display_name} viewed your profile!",
            data: [
                'viewer_id'       => $viewer->id,
                'account_id'      => $viewer->account_id,
                'name'            => $viewer->display_name,
                'avatar_url'      => $viewer->avatar_url,
                'is_online'       => (bool) $viewer->is_online,
                'video_call_rate' => (int) ($viewer->video_call_rate ?: 100),
                'viewed_at'       => now()->toIso8601String(),
            ]
        );

        // 📲 Dispatch Real-Time Push Alert to Host
        try {
            PushNotificationService::sendProfileViewPush($viewer, $host);
        } catch (\Throwable $e) {
            Log::error("Profile view push notification error: " . $e->getMessage());
        }

        $config = CallSetting::getAllConfig();
        $ratePerMinute = (int) ($host->video_call_rate ?: ($config['video_call_rate_per_minute'] ?? 100));
        $hasSufficientBalance = $viewer->isEligibleForFreeCall() || ($viewer->coins >= $ratePerMinute);

        // Auto greetings matching Bangladesh / Global app audience
        $greetings = [
            "Hi {$viewer->display_name}! I saw you visited my profile. Call me now?",
            "Hi {$viewer->display_name}! আমাকে দেখতে চাও?",
            "Hey handsome! Thanks for visiting my profile ❤️",
            "Hi {$viewer->display_name}! I am free right now, let's video chat!",
        ];
        $greetingText = $greetings[array_rand($greetings)];

        // Simulated automatic greeting / invite from host to visitor's chat
        $welcomeMessage = ChatMessage::create([
            'sender_id'   => $host->id,
            'receiver_id' => $viewer->id,
            'type'        => 'text',
            'message'     => $greetingText,
            'is_read'     => false,
            'is_free'     => true,
            'coin_cost'   => 0,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Profile view recorded. Auto-callback notification triggered.',
            'data'    => [
                'host'         => [
                    'id'              => $host->id,
                    'account_id'      => $host->account_id,
                    'display_name'    => $host->display_name,
                    'avatar_url'      => $host->avatar_url,
                    'is_online'       => (bool) $host->is_online,
                    'is_busy'         => (bool) $host->is_busy,
                    'is_available'    => $isHostAvailable,
                    'video_call_rate' => $ratePerMinute,
                    'country'         => $host->country ?: 'Bangladesh',
                    'level'           => $host->level ?: 'Lv1',
                    'introduction'    => $host->introduction,
                ],
                'notification' => [
                    'id'          => $notification->id,
                    'receiver_id' => $host->id,
                    'type'        => 'profile_view',
                    'title'       => $notification->title,
                    'message'     => $notification->message,
                ],
                'callback'     => [
                    'auto_call_triggered' => $isHostAvailable,
                    'host_is_available'   => $isHostAvailable,
                    'viewer_can_receive'  => $hasSufficientBalance,
                    'required_coins'      => $ratePerMinute,
                    'viewer_coins'        => (int) $viewer->coins,
                    'trigger_action'      => $isHostAvailable ? 'INCOMING_CALL' : 'CHAT_NOTIFICATION',
                ],
                'auto_message' => [
                    'id'         => $welcomeMessage->id,
                    'sender_id'  => $host->id,
                    'message'    => $greetingText,
                    'type'       => 'text',
                    'time'       => 'Just now',
                    'created_at' => $welcomeMessage->created_at->toIso8601String(),
                ],
            ],
        ], 200);
    }

    /**
     * Get List of Users Who Viewed My Profile (Profile Visitors List).
     * GET /api/profile/visitors or GET /api/visitors or GET /api/user/visitors
     */
    public function getVisitors(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $perPage = (int) $request->input('per_page', 20);

        // Get profile views where host_id is current user
        $views = ProfileView::where('host_id', $user->id)
            ->with('viewer')
            ->orderBy('viewed_at', 'desc')
            ->paginate($perPage);

        $visitorsList = [];
        foreach ($views as $view) {
            $viewer = $view->viewer;
            if (!$viewer) continue;

            $timeAgo = 'Recently';
            if ($view->viewed_at) {
                if ($view->viewed_at->diffInMinutes(now()) < 60) {
                    $mins = max(1, $view->viewed_at->diffInMinutes(now()));
                    $timeAgo = "{$mins} mins ago";
                } elseif ($view->viewed_at->isToday()) {
                    $timeAgo = $view->viewed_at->format('H:i');
                } elseif ($view->viewed_at->isYesterday()) {
                    $timeAgo = 'Yesterday';
                } else {
                    $timeAgo = $view->viewed_at->format('M d, Y');
                }
            }

            $visitorsList[] = [
                'view_id'         => $view->id,
                'user_id'         => $viewer->id,
                'account_id'      => $viewer->account_id,
                'name'            => $viewer->display_name,
                'avatar_url'      => $viewer->avatar_url,
                'is_online'       => (bool) $viewer->is_online,
                'is_busy'         => (bool) $viewer->is_busy,
                'video_call_rate' => (int) ($viewer->video_call_rate ?: 100),
                'country'         => $viewer->country ?: 'Bangladesh',
                'gender'          => $viewer->gender ?: 'other',
                'level'           => $viewer->level ?: 'Lv1',
                'viewed_at'       => $view->viewed_at ? $view->viewed_at->toIso8601String() : null,
                'time_ago'        => $timeAgo,
            ];
        }

        $unreadVisitorNotifications = Notification::where('user_id', $user->id)
            ->where('type', 'profile_view')
            ->where('is_read', false)
            ->count();

        return response()->json([
            'status'  => true,
            'message' => 'Profile visitors loaded successfully.',
            'data'    => [
                'total_visitors'   => $views->total(),
                'unread_visitors'  => $unreadVisitorNotifications,
                'visitors'         => $visitorsList,
                'pagination'       => [
                    'current_page' => $views->currentPage(),
                    'last_page'    => $views->lastPage(),
                    'total'        => $views->total(),
                ],
            ],
        ], 200);
    }

    /**
     * Get In-App Notifications List.
     * GET /api/notifications or GET /api/user/notifications
     */
    public function getNotifications(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $type = $request->input('type');
        $query = Notification::where('user_id', $user->id)->with('actor')->latest();

        if ($type) {
            $query->where('type', $type);
        }

        $perPage = (int) $request->input('per_page', 25);
        $notifications = $query->paginate($perPage);

        $unreadTotal = Notification::where('user_id', $user->id)->where('is_read', false)->count();
        $unreadProfileViews = Notification::where('user_id', $user->id)->where('type', 'profile_view')->where('is_read', false)->count();
        $unreadMessages = Notification::where('user_id', $user->id)->where('type', 'message')->where('is_read', false)->count();

        return response()->json([
            'status'  => true,
            'message' => 'Notifications retrieved successfully.',
            'data'    => [
                'unread_count'        => $unreadTotal,
                'profile_view_unread' => $unreadProfileViews,
                'message_unread'      => $unreadMessages,
                'notifications'       => $notifications->items(),
                'pagination'          => [
                    'current_page' => $notifications->currentPage(),
                    'last_page'    => $notifications->lastPage(),
                    'total'        => $notifications->total(),
                ],
            ],
        ], 200);
    }

    /**
     * Get Fast Notification Unread Badges Count.
     * GET /api/notifications/unread-count
     */
    public function getNotificationUnreadCount(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $totalUnread = Notification::where('user_id', $user->id)->where('is_read', false)->count();
        $profileViewUnread = Notification::where('user_id', $user->id)->where('type', 'profile_view')->where('is_read', false)->count();
        $messageUnread = Notification::where('user_id', $user->id)->where('type', 'message')->where('is_read', false)->count();

        return response()->json([
            'status'  => true,
            'message' => 'Unread counts retrieved successfully.',
            'data'    => [
                'total_unread'        => $totalUnread,
                'profile_view_unread' => $profileViewUnread,
                'message_unread'      => $messageUnread,
            ],
        ], 200);
    }

    /**
     * Mark Notifications as Read.
     * POST /api/notifications/read or POST /api/notifications/{id}/read
     */
    public function markNotificationRead(Request $request, $id = null): JsonResponse
    {
        $user = $this->resolveUser($request);

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $notificationId = $id ?? $request->input('id') ?? $request->input('notification_id');
        $type = $request->input('type');

        if ($notificationId) {
            Notification::where('id', $notificationId)
                ->where('user_id', $user->id)
                ->update(['is_read' => true, 'read_at' => now()]);
        } elseif ($type) {
            Notification::where('user_id', $user->id)
                ->where('type', $type)
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);
        } else {
            Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Notification(s) marked as read.',
        ], 200);
    }

    /**
     * Clear / Delete Notifications.
     * DELETE /api/notifications or POST /api/notifications/clear
     */
    public function clearNotifications(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $type = $request->input('type');
        $query = Notification::where('user_id', $user->id);
        if ($type) {
            $query->where('type', $type);
        }
        $query->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Notifications cleared successfully.',
        ], 200);
    }

    /**
     * Get App General Configuration (Logo, Name, Version, Limits) for Mobile Login/Register screen.
     * GET /api/app/config or GET /api/settings
     */
    public function getAppConfig(): JsonResponse
    {
        return response()->json([
            'status'  => true,
            'message' => 'App settings loaded successfully.',
            'data'    => AppSetting::getAppConfig(),
        ]);
    }

    /**
     * Block a User from Chat / Profile.
     * POST /api/chat/block or POST /api/user/block
     */
    public function blockUser(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $targetId = $request->input('target_user_id') 
                 ?? $request->input('user_id') 
                 ?? $request->input('blocked_user_id');

        if (!$targetId) {
            return response()->json(['status' => false, 'message' => 'target_user_id field is required.'], 422);
        }

        $target = User::find($targetId) ?? User::where('account_id', $targetId)->first();
        if (!$target) {
            return response()->json(['status' => false, 'message' => 'Target user not found.'], 404);
        }

        if ($target->id === $user->id) {
            return response()->json(['status' => false, 'message' => 'You cannot block yourself.'], 422);
        }

        BlockedUser::firstOrCreate([
            'user_id' => $user->id,
            'blocked_user_id' => $target->id,
        ], [
            'reason' => $request->input('reason', 'Blocked by user'),
        ]);

        return response()->json([
            'status' => true,
            'message' => "Successfully blocked {$target->display_name}.",
            'is_blocked' => true,
            'target_user' => [
                'id' => $target->id,
                'account_id' => $target->account_id,
                'name' => $target->display_name,
            ],
        ], 200);
    }

    /**
     * Unblock a User.
     * POST /api/chat/unblock or POST /api/user/unblock
     */
    public function unblockUser(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $targetId = $request->input('target_user_id') 
                 ?? $request->input('user_id') 
                 ?? $request->input('blocked_user_id');

        if (!$targetId) {
            return response()->json(['status' => false, 'message' => 'target_user_id field is required.'], 422);
        }

        $target = User::find($targetId) ?? User::where('account_id', $targetId)->first();
        if (!$target) {
            return response()->json(['status' => false, 'message' => 'Target user not found.'], 404);
        }

        BlockedUser::where('user_id', $user->id)
            ->where('blocked_user_id', $target->id)
            ->delete();

        return response()->json([
            'status' => true,
            'message' => "Successfully unblocked {$target->display_name}.",
            'is_blocked' => false,
            'target_user' => [
                'id' => $target->id,
                'account_id' => $target->account_id,
                'name' => $target->display_name,
            ],
        ], 200);
    }

    /**
     * List Blocked Users for current user.
     * GET /api/chat/blocked-users or GET /api/user/blocked
     */
    public function getBlockedUsers(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $blocked = BlockedUser::with('blockedUser')
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(function ($b) {
                $u = $b->blockedUser;
                return [
                    'id' => $b->id,
                    'user_id' => $u ? $u->id : null,
                    'account_id' => $u ? $u->account_id : null,
                    'name' => $u ? $u->display_name : 'Deleted User',
                    'avatar_url' => $u ? $u->avatar_url : null,
                    'blocked_at' => $b->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'status' => true,
            'message' => 'Blocked users retrieved successfully.',
            'count' => $blocked->count(),
            'data' => $blocked,
        ], 200);
    }

    /**
     * Report a User from Chat / Profile with standard reason types.
     * POST /api/chat/report or POST /api/user/report
     */
    public function reportUser(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $targetId = $request->input('reported_user_id') 
                 ?? $request->input('target_user_id') 
                 ?? $request->input('user_id');

        if (!$targetId) {
            return response()->json(['status' => false, 'message' => 'reported_user_id is required.'], 422);
        }

        $target = User::find($targetId) ?? User::where('account_id', $targetId)->first();
        if (!$target) {
            return response()->json(['status' => false, 'message' => 'Target user not found.'], 404);
        }

        if ($target->id === $user->id) {
            return response()->json(['status' => false, 'message' => 'You cannot report yourself.'], 422);
        }

        $reasonType = $request->input('reason_type', 'other');
        $validReasons = UserReport::$reasonTypes;
        $reasonTitle = $validReasons[$reasonType] ?? ($request->input('reason_title') ?: 'Rule violation');

        $proofImagePath = null;
        if ($request->hasFile('proof_image') || $request->hasFile('image') || $request->hasFile('screenshot')) {
            $proofFile = $request->file('proof_image') ?? $request->file('image') ?? $request->file('screenshot');
            $destPath = public_path('uploads/reports');
            if (!File::exists($destPath)) {
                File::makeDirectory($destPath, 0777, true, true);
            }
            $filename = 'report_' . time() . '_' . Str::random(6) . '.' . $proofFile->getClientOriginalExtension();
            $proofFile->move($destPath, $filename);
            $proofImagePath = 'uploads/reports/' . $filename;
        }

        $report = UserReport::create([
            'reporter_id' => $user->id,
            'reported_user_id' => $target->id,
            'reason_type' => $reasonType,
            'reason_title' => $reasonTitle,
            'description' => $request->input('description') ?: $request->input('reason'),
            'proof_image' => $proofImagePath,
            'status' => 'pending',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Thank you. Your report has been submitted. Our moderation team will investigate promptly.',
            'report_id' => $report->id,
            'reported_user' => [
                'id' => $target->id,
                'name' => $target->display_name,
            ],
        ], 200);
    }

    /**
     * Get Report Reason Options for Mobile App modal.
     * GET /api/chat/report-reasons or GET /api/report/reasons
     */
    public function getReportReasons(): JsonResponse
    {
        $reasons = collect(UserReport::$reasonTypes)->map(function ($title, $key) {
            return [
                'type' => $key,
                'title' => $title,
            ];
        })->values();

        return response()->json([
            'status' => true,
            'reasons' => $reasons,
        ], 200);
    }

    /**
     * Check if user has permission / free quota / coins to send messages.
     * GET|POST /api/messages/check-permission or GET|POST /api/chat/check-permission
     */
    public function checkPermission(Request $request): JsonResponse
    {
        $sender = $this->resolveUser($request);
        if (!$sender) {
            return response()->json([
                'status'      => false,
                'can_message' => false,
                'code'        => 'UNAUTHENTICATED',
                'message'     => 'Unauthenticated. Please pass Authorization Bearer token or user_id.',
            ], 401);
        }

        $receiverId = $request->input('receiver_id') ?? $request->input('to_user_id') ?? $request->input('user_id');
        $receiver = $receiverId ? (User::find($receiverId) ?? User::where('account_id', $receiverId)->first()) : null;

        $freeLimit = (int) AppSetting::get('free_messages_limit', $sender->free_messages_limit ?? 5);
        $freeUsed = $sender->free_messages_used ?? 0;
        $freeRemaining = max(0, $freeLimit - $freeUsed);
        $coinCost = (int) AppSetting::get('message_coin_cost', 5);

        $isFree = $freeRemaining > 0;
        $hasBalance = $sender->coins >= $coinCost;
        $canMessage = $isFree || $hasBalance;

        $modalData = $this->buildRechargeModalData($sender, $receiver, $coinCost, 'chat');

        if (!$canMessage) {
            return response()->json([
                'status'                  => false,
                'can_message'             => false,
                'code'                    => 'MESSAGE_LIMIT_REACHED',
                'message'                 => "You have reached your free limit of {$freeLimit} messages. Please recharge coins to continue chatting.",
                'free_messages_limit'     => $freeLimit,
                'free_messages_used'      => $freeUsed,
                'free_messages_remaining' => 0,
                'user_coins'              => (int) $sender->coins,
                'user_gems'               => (int) $sender->coins,
                'required_coins'          => $coinCost,
                'show_recharge_modal'     => true,
                'recharge_modal_data'     => $modalData,
                'modal'                   => $modalData,
                'packages'                => $modalData['packages'],
                'target_user'             => $modalData['target_user'],
            ], 200);
        }

        return response()->json([
            'status'                  => true,
            'can_message'             => true,
            'code'                    => 'MESSAGE_ALLOWED',
            'message'                 => 'User is allowed to send messages.',
            'is_free'                 => $isFree,
            'free_messages_limit'     => $freeLimit,
            'free_messages_used'      => $freeUsed,
            'free_messages_remaining' => $freeRemaining,
            'user_coins'              => (int) $sender->coins,
            'user_gems'               => (int) $sender->coins,
            'show_recharge_modal'     => false,
            'target_user'             => $modalData['target_user'],
        ], 200);
    }

    /**
     * Helper to build full Dynamic Recharge Modal Data for Low-Balance / Reached Limit.
     */
    public function buildRechargeModalData(?User $sender, ?User $receiver, int $cost = 5, string $action = 'chat'): array
    {
        $config = CallSetting::getAllConfig();
        $userCoins = $sender ? (int) $sender->coins : 0;
        
        $teaserText = $config['in_call_recharge_offer']['teaser_text'] 
            ?? $config['call_recharge_teaser_text'] 
            ?? 'I want to talk more with you. Recharge and call me back~';

        $targetData = null;
        if ($receiver) {
            $targetData = [
                'id'              => $receiver->id,
                'account_id'      => $receiver->account_id,
                'name'            => $receiver->display_name ?? $receiver->name ?? 'User',
                'display_name'    => $receiver->display_name ?? $receiver->name ?? 'User',
                'avatar_url'      => $receiver->avatar_url,
                'profile_picture' => $receiver->avatar_url,
                'gender'          => $receiver->gender ?? 'female',
                'country'         => $receiver->country ?? 'Bangladesh',
                'country_flag'    => $receiver->country_flag ?? '🇧🇩',
                'age'             => $receiver->display_age,
                'level'           => $receiver->display_level,
                'is_online'       => (bool) $receiver->is_online,
                'is_busy'         => (bool) $receiver->is_busy,
                'video_call_rate' => (int) ($receiver->video_call_rate ?: 100),
            ];
        }

        $packages = CoinPackage::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($pkg) {
                $baseCoins = (int) $pkg->coins;
                $bonusCoins = (int) ($pkg->bonus_coins ?: 0);
                $totalCoins = $baseCoins + $bonusCoins;
                $price = (float) $pkg->price;
                $formattedPrice = 'BDT ' . number_format($price, 2);

                return [
                    'id'                  => $pkg->id,
                    'title'               => $pkg->title ?: ($baseCoins . ' Coins'),
                    'coins'               => $baseCoins,
                    'base_coins'          => $baseCoins,
                    'bonus_coins'         => $bonusCoins,
                    'total_coins'         => $totalCoins,
                    'formatted_coins'     => (string) $baseCoins,
                    'price'               => $price,
                    'price_bdt'           => $price,
                    'formatted_price'     => $formattedPrice,
                    'badge'               => $pkg->badge ?: null,
                    'badge_color'         => $pkg->badge_color ?: 'danger',
                    'badge_tag'           => ($baseCoins <= 7560 || $pkg->id === 1) ? 'ONCE' : null,
                    'is_once_offer'       => ($baseCoins <= 7560 || $pkg->id === 1),
                    'png_url'             => $pkg->png_url ?? $pkg->icon_full_url,
                    'svg_url'             => $pkg->svg_url ?? $pkg->icon_full_url,
                    'image_url'           => $pkg->image_url ?? $pkg->icon_full_url,
                    'icon_url'            => $pkg->icon_url,
                    'icon_full_url'       => $pkg->icon_full_url,
                    'is_popular'          => (bool) $pkg->is_popular,
                    'button_text'         => "Recharge {$baseCoins} Coins ({$formattedPrice})",
                ];
            });

        $defaultSelectedId = $packages->firstWhere('is_popular', true)['id'] ?? ($packages->first()['id'] ?? 1);

        return [
            'header_title'                => $teaserText,
            'teaser_text'                 => $teaserText,
            'action_type'                 => $action,
            'receiver'                    => $targetData,
            'target_user'                 => $targetData,
            'user_coins'                  => $userCoins,
            'user_gems'                   => $userCoins,
            'formatted_user_coins'        => (string) $userCoins,
            'formatted_user_gems'         => (string) $userCoins,
            'wallet_text'                 => "My Gems: {$userCoins}",
            'user_gems_text'              => "My Gems: {$userCoins}",
            'currency_symbol'             => '💎',
            'default_selected_package_id' => $defaultSelectedId,
            'button_text'                 => 'Continue',
            'recharge_button_text'        => 'Continue',
            'packages'                    => $packages,
        ];
    }
}
