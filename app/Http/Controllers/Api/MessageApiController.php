<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\CallSession;
use App\Models\CallSetting;
use App\Models\ChatMessage;
use App\Models\CoinPackage;
use App\Models\CoinTransaction;
use App\Models\PaymentMethod;
use App\Models\ProfileView;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
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
     * Get Conversations / Inbox List with Unread Badges and Latest Message Previews (Matching Screenshot).
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

            // Format message snippet matching mobile UI in Screenshot (Video call, Image, Bangla text)
            $preview = 'Start chatting';
            $previewType = 'text';
            $timestamp = 'Recently';

            if ($lastMessage) {
                $previewType = $lastMessage->type;
                if ($lastMessage->type === 'video_call') {
                    $preview = '[Video Call]';
                } elseif ($lastMessage->type === 'image') {
                    $preview = '[Image]';
                } elseif ($lastMessage->type === 'voice') {
                    $preview = '[Voice Note]';
                } else {
                    $preview = $lastMessage->message ?: 'Message';
                }

                // Format time: e.g. "59 minutes", "09:56", "Yesterday"
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

        return response()->json([
            'status'  => true,
            'message' => 'Messages retrieved successfully.',
            'data'    => [
                'chat_partner' => [
                    'id'              => $otherUser->id,
                    'account_id'      => $otherUser->account_id,
                    'name'            => $otherUser->display_name,
                    'avatar_url'      => $otherUser->avatar_url,
                    'is_online'       => (bool) $otherUser->is_online,
                    'is_busy'         => (bool) $otherUser->is_busy,
                    'video_call_rate' => (int) ($otherUser->video_call_rate ?: 100),
                ],
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
     * Send Message (Text, Voice Audio, Image) with Free Quota & Coin Balance Check.
     * Uploads media directly to public/uploads/sms_profile or public/uploads/messages.
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

        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required',
            'type'        => 'nullable|in:text,image,voice,video_call,audio_call',
            'message'     => 'nullable|string|max:2000',
            'voice_file'  => 'nullable|file|mimes:mp3,wav,m4a,aac,ogg,webm|max:10240',
            'image_file'  => 'nullable|image|max:10240',
            'duration'    => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $receiverId = $request->input('receiver_id');
        $receiver = User::find($receiverId) ?? User::where('account_id', $receiverId)->first();

        if (!$receiver) {
            return response()->json([
                'status'  => false,
                'message' => 'Receiver not found.',
            ], 404);
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
                $packages = CoinPackage::where('is_active', true)->orderBy('sort_order')->get();
                $paymentMethods = PaymentMethod::where('is_active', true)->get();

                return response()->json([
                    'status'              => false,
                    'code'                => 'MESSAGE_LIMIT_REACHED',
                    'message'             => "You have reached your free limit of {$freeLimit} messages. Please recharge coins to continue chatting.",
                    'is_limit_reached'    => true,
                    'redirect_to_deposit' => true,
                    'current_coins'       => (int) $sender->coins,
                    'required_coins'      => $coinCost,
                    'free_messages_used'  => $freeUsed,
                    'free_messages_limit' => $freeLimit,
                    'coin_packages'       => $packages,
                    'payment_methods'     => $paymentMethods,
                ], 402);
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

        // Handle Media Uploads to public/uploads/sms_profile
        $mediaUrl = $request->input('media_url');
        $type = $request->input('type', 'text');

        $smsUploadDir = public_path('uploads/sms_profile');
        if (!File::exists($smsUploadDir)) {
            File::makeDirectory($smsUploadDir, 0777, true, true);
        }

        if ($request->hasFile('voice_file')) {
            $voiceFile = $request->file('voice_file');
            $filename = 'voice_' . time() . '_' . Str::random(6) . '.' . $voiceFile->getClientOriginalExtension();
            $voiceFile->move($smsUploadDir, $filename);
            $mediaUrl = asset('uploads/sms_profile/' . $filename);
            $type = 'voice';
        } elseif ($request->hasFile('image_file')) {
            $imgFile = $request->file('image_file');
            $filename = 'sms_' . time() . '_' . Str::random(6) . '.' . $imgFile->getClientOriginalExtension();
            $imgFile->move($smsUploadDir, $filename);
            $mediaUrl = asset('uploads/sms_profile/' . $filename);
            $type = 'image';
        }

        $messageText = $request->input('message');
        if (!$messageText && $type === 'voice') {
            $messageText = '[Voice Note]';
        } elseif (!$messageText && $type === 'image') {
            $messageText = '[Image]';
        }

        $chatMessage = ChatMessage::create([
            'sender_id'   => $sender->id,
            'receiver_id' => $receiver->id,
            'type'        => $type,
            'message'     => $messageText,
            'media_url'   => $mediaUrl,
            'duration'    => (int) $request->input('duration', 0),
            'is_read'     => false,
            'is_free'     => $isFree,
            'coin_cost'   => $isFree ? 0 : $coinCost,
        ]);

        $remainingFree = max(0, $freeLimit - ($sender->fresh()->free_messages_used ?? 0));

        return response()->json([
            'status'  => true,
            'message' => 'Message sent successfully.',
            'data'    => [
                'chat_message' => $chatMessage,
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
     * POST /api/messages/read
     */
    public function markAsRead(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $senderId = $request->input('sender_id');
        if ($senderId) {
            ChatMessage::where('sender_id', $senderId)
                ->where('receiver_id', $user->id)
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
     * Record Profile View & Trigger Automatic Callback / Greeting.
     * When user visits host's profile, host sends an auto notification / call trigger back to user!
     * POST /api/profile/{id}/view or POST /api/user/view-profile
     */
    public function recordProfileView(Request $request, $id = null): JsonResponse
    {
        $viewer = $this->resolveUser($request);
        $hostId = $id ?? $request->input('host_id') ?? $request->input('id');

        if (!$viewer) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $host = User::find($hostId) ?? User::where('account_id', $hostId)->first();
        if (!$host) {
            return response()->json(['status' => false, 'message' => 'Host profile not found.'], 404);
        }

        // Record profile view
        ProfileView::create([
            'viewer_id'           => $viewer->id,
            'host_id'             => $host->id,
            'auto_call_triggered' => true,
            'callback_requested'  => true,
            'status'              => 'callback_pending',
            'viewed_at'           => now(),
        ]);

        $config = CallSetting::getAllConfig();
        $ratePerMinute = (int) ($host->video_call_rate ?: ($config['video_call_rate_per_minute'] ?? 100));
        $hasSufficientBalance = $viewer->isEligibleForFreeCall() || ($viewer->coins >= $ratePerMinute);

        // Simulated automatic greeting / invite from host to user's chat (Matching Screenshot #1)
        $welcomeMessage = ChatMessage::create([
            'sender_id'   => $host->id,
            'receiver_id' => $viewer->id,
            'type'        => 'text',
            'message'     => "Hi {$viewer->display_name}! I saw you visited my profile. Call me now?",
            'is_read'     => false,
            'is_free'     => true,
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
                    'video_call_rate' => $ratePerMinute,
                    'country'         => $host->country ?: 'Bangladesh',
                    'level'           => $host->level ?: 4,
                    'introduction'    => $host->introduction,
                ],
                'callback'     => [
                    'auto_call_triggered' => true,
                    'viewer_can_receive'  => $hasSufficientBalance,
                    'required_coins'      => $ratePerMinute,
                    'viewer_coins'        => (int) $viewer->coins,
                ],
                'auto_message' => $welcomeMessage,
            ],
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
}
