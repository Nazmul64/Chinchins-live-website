<?php

namespace App\Http\Controllers\Api;

use App\Events\DirectMessageSent;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\DirectMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class ChatController extends Controller
{
    /**
     * Resolve authenticated or requested user.
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

        $headerUserId = $request->header('X-User-Id') ?? $request->header('User-Id') ?? $request->header('userId');
        if ($headerUserId) {
            $u = User::find($headerUserId) ?? User::where('account_id', $headerUserId)->first();
            if ($u) return $u;
        }

        $paramId = $request->input('sender_id') ?? $request->input('user_id') ?? $request->input('userId') ?? $request->input('uid');
        if ($paramId) {
            $u = User::find($paramId) ?? User::where('account_id', $paramId)->first();
            if ($u) return $u;
        }

        return null;
    }

    /**
     * 1. Get Inbox / Conversations List.
     * GET /api/chat/conversations
     */
    public function getConversations(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request) ?? auth()->user() ?? auth('sanctum')->user();
        if (!$user) {
            return response()->json(['status' => false, 'success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $userId = $user->id;

        // Collect all distinct users with whom active user had interactions (Conversation, Message, ChatMessage, DirectMessage)
        $convUsers1 = Conversation::where('user_one', $userId)->pluck('user_two');
        $convUsers2 = Conversation::where('user_two', $userId)->pluck('user_one');
        $sentChat = \App\Models\ChatMessage::where('sender_id', $userId)->pluck('receiver_id');
        $recvChat = \App\Models\ChatMessage::where('receiver_id', $userId)->pluck('sender_id');
        $sentMsg = \App\Models\Message::where('sender_id', $userId)->pluck('receiver_id');
        $recvMsg = \App\Models\Message::where('receiver_id', $userId)->pluck('sender_id');

        $contactIds = $convUsers1->merge($convUsers2)
            ->merge($sentChat)
            ->merge($recvChat)
            ->merge($sentMsg)
            ->merge($recvMsg)
            ->unique()
            ->reject(fn($id) => (int)$id === (int)$userId || empty($id))
            ->values();

        $conversations = [];

        foreach ($contactIds as $contactId) {
            $otherUser = User::find($contactId);
            if (!$otherUser) continue;

            $u1 = min($userId, $contactId);
            $u2 = max($userId, $contactId);
            $conv = Conversation::where('user_one', $u1)->where('user_two', $u2)->first();

            // Find latest message across models
            $lastChat = \App\Models\ChatMessage::where(function ($q) use ($userId, $contactId) {
                $q->where('sender_id', $userId)->where('receiver_id', $contactId);
            })->orWhere(function ($q) use ($userId, $contactId) {
                $q->where('sender_id', $contactId)->where('receiver_id', $userId);
            })->latest('created_at')->first();

            $lastMsg = \App\Models\Message::where(function ($q) use ($userId, $contactId) {
                $q->where('sender_id', $userId)->where('receiver_id', $contactId);
            })->orWhere(function ($q) use ($userId, $contactId) {
                $q->where('sender_id', $contactId)->where('receiver_id', $userId);
            })->latest('created_at')->first();

            $lastDirect = DirectMessage::where('conversation_id', $conv?->id)->latest('created_at')->first();

            $candidates = array_filter([$lastChat, $lastMsg, $lastDirect]);
            $lastMessageText = $conv?->last_message ?: 'Start chatting';
            $lastMessageAt = $conv?->last_message_at ? $conv->last_message_at->toIso8601String() : null;

            if (!empty($candidates)) {
                usort($candidates, fn($a, $b) => $b->created_at <=> $a->created_at);
                $newest = $candidates[0];
                $lastMessageText = $newest->message ?: $lastMessageText;
                $lastMessageAt = $newest->created_at ? $newest->created_at->toIso8601String() : $lastMessageAt;
            }

            $unreadCountChat = \App\Models\ChatMessage::where('sender_id', $contactId)->where('receiver_id', $userId)->where('is_read', false)->count();
            $unreadCountMsg = \App\Models\Message::where('sender_id', $contactId)->where('receiver_id', $userId)->where('is_read', false)->count();
            $unreadCountDirect = DirectMessage::where('conversation_id', $conv?->id)->where('receiver_id', $userId)->where('is_read', false)->count();
            $unreadCount = max($unreadCountChat, $unreadCountMsg, $unreadCountDirect);

            $partnerData = [
                'id'           => $otherUser->id,
                'account_id'   => $otherUser->account_id,
                'name'         => $otherUser->display_name ?? $otherUser->name,
                'display_name' => $otherUser->display_name ?? $otherUser->name,
                'avatar'       => $otherUser->avatar_url,
                'avatar_url'   => $otherUser->avatar_url,
                'gender'       => $otherUser->gender ?: 'female',
                'level'        => $otherUser->level ?: 'Lv1',
                'is_online'    => (bool) $otherUser->is_online,
                'is_busy'      => (bool) $otherUser->is_busy,
            ];

            $conversations[] = [
                'conversation_id' => $conv?->id ?: $otherUser->id,
                'user_id'         => $otherUser->id,
                'user'            => $partnerData,
                'partner'         => $partnerData,
                'unread_count'    => $unreadCount,
                'last_message'    => $lastMessageText,
                'last_message_at' => $lastMessageAt,
            ];
        }

        // Sort by last message date descending
        usort($conversations, function ($a, $b) {
            $tA = $a['last_message_at'] ?? '';
            $tB = $b['last_message_at'] ?? '';
            return strcmp($tB, $tA);
        });

        return response()->json([
            'status'        => true,
            'success'       => true,
            'data'          => $conversations,
            'conversations' => $conversations,
            'message'       => 'Conversations retrieved successfully.'
        ], 200);
    }

    /**
     * 2. Get Chat Messages History for Conversation.
     * GET /api/chat/messages/{conversationId}
     */
    public function getMessages(Request $request, $conversationId): JsonResponse
    {
        $user = $this->resolveUser($request);
        $userId = $user ? $user->id : 0;

        $messages = DirectMessage::where('conversation_id', $conversationId)
            ->with([
                'sender:id,account_id,name,display_name,avatar',
                'receiver:id,account_id,name,display_name,avatar'
            ])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) {
                return [
                    'id'              => $msg->id,
                    'conversation_id' => $msg->conversation_id,
                    'sender_id'       => $msg->sender_id,
                    'receiver_id'     => $msg->receiver_id,
                    'message'         => $msg->message,
                    'attachment_path' => $msg->attachment_path,
                    'image_url'       => $msg->attachment_path,
                    'type'            => $msg->type,
                    'is_read'         => (bool) $msg->is_read,
                    'created_at'      => $msg->created_at ? $msg->created_at->toIso8601String() : null,
                    'sender'          => $msg->sender ? [
                        'id'           => $msg->sender->id,
                        'account_id'   => $msg->sender->account_id,
                        'name'         => $msg->sender->display_name ?? $msg->sender->name,
                        'display_name' => $msg->sender->display_name ?? $msg->sender->name,
                        'avatar'       => $msg->sender->avatar_url,
                        'avatar_url'   => $msg->sender->avatar_url,
                    ] : null,
                ];
            });

        // Mark unread messages as read
        if ($userId > 0) {
            DirectMessage::where('conversation_id', $conversationId)
                ->where('receiver_id', $userId)
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        return response()->json([
            'status'  => 'success',
            'data'    => $messages,
            'message' => 'Messages retrieved successfully.'
        ], 200);
    }

    /**
     * 3. Send Real-Time Direct Message or Image.
     * POST /api/chat/send-message, POST /api/chat/send
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message'     => 'nullable|string',
            'image'       => 'nullable|file|mimes:jpeg,png,jpg,webp,avif,gif,svg,bmp,tiff,tif,heic,heif,ico,apng,psd,raw,eps,ai,dds,tga,jp2,jxl,exr,hdr,pic,ppm,pgm,pbm|max:51200', // Max 50MB
        ]);

        $senderId = $user->id;
        $receiverId = (int) $request->input('receiver_id');

        if ($senderId === $receiverId) {
            return response()->json(['status' => 'error', 'message' => 'Cannot send message to yourself.'], 422);
        }

        // Find or create conversation
        $u1 = min($senderId, $receiverId);
        $u2 = max($senderId, $receiverId);
        $conversation = Conversation::firstOrCreate(
            ['user_one' => $u1, 'user_two' => $u2]
        );

        $attachmentUrl = null;
        $type = 'text';

        // Image Handling: saved in public/uploads/live_chat/
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $uploadDirectory = public_path('uploads/live_chat');

            if (!File::isDirectory($uploadDirectory)) {
                File::makeDirectory($uploadDirectory, 0777, true, true);
            }

            $fileName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move($uploadDirectory, $fileName);

            $attachmentUrl = url('uploads/live_chat/' . $fileName);
            $type = 'image';
        } elseif ($request->input('attachment_path') || $request->input('image_url')) {
            $attachmentUrl = $request->input('attachment_path') ?? $request->input('image_url');
            $type = 'image';
        }

        $messageText = trim($request->input('message') ?? '');
        if (empty($messageText) && empty($attachmentUrl)) {
            return response()->json(['status' => 'error', 'message' => 'Message or image file is required.'], 422);
        }

        // Create Direct Message
        $directMessage = DirectMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $senderId,
            'receiver_id'     => $receiverId,
            'message'         => $messageText ?: ($type === 'image' ? '📷 Photo' : ''),
            'attachment_path' => $attachmentUrl,
            'type'            => $type,
            'is_read'         => false,
        ]);

        // Update Conversation Last Message
        $conversation->update([
            'last_message'    => ($type === 'image' && empty($messageText)) ? '📷 Photo' : $messageText,
            'last_message_at' => now(),
        ]);

        // Broadcast instantaneously using ShouldBroadcastNow
        try {
            broadcast(new DirectMessageSent($directMessage))->toOthers();
        } catch (\Throwable $e) {}

        $payload = [
            'id'              => $directMessage->id,
            'conversation_id' => $conversation->id,
            'sender_id'       => $senderId,
            'receiver_id'     => $receiverId,
            'message'         => $directMessage->message,
            'attachment_path' => $directMessage->attachment_path,
            'image_url'       => $directMessage->attachment_path,
            'type'            => $type,
            'is_read'         => false,
            'created_at'      => $directMessage->created_at->toIso8601String(),
            'sender'          => [
                'id'           => $user->id,
                'account_id'   => $user->account_id,
                'name'         => $user->display_name ?? $user->name,
                'display_name' => $user->display_name ?? $user->name,
                'avatar'       => $user->avatar_url,
                'avatar_url'   => $user->avatar_url,
            ],
        ];

        return response()->json([
            'status'  => 'success',
            'message' => 'Message sent successfully',
            'data'    => $payload,
        ], 200);
    }
}
