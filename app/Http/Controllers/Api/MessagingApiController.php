<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\MessagingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MessagingApiController extends Controller
{
    protected MessagingService $messagingService;

    public function __construct(MessagingService $messagingService)
    {
        $this->messagingService = $messagingService;
    }

    protected function resolveUser(Request $request): ?User
    {
        try {
            if ($user = $request->user('sanctum')) return $user;
            if ($user = $request->user()) return $user;
            if (Auth::guard('sanctum')->check()) return Auth::guard('sanctum')->user();
        } catch (\Throwable $e) {}

        $userId = $request->input('user_id') ?? $request->header('X-User-Id');
        if ($userId) {
            return User::find($userId);
        }
        return null;
    }

    /**
     * Get or create direct conversation with a recipient.
     * POST /api/v1/conversations/direct or POST /api/conversations/direct
     */
    public function getOrCreateDirect(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        $recipientId = (int) ($request->input('recipient_id') ?? $request->input('receiver_id'));
        if (!$recipientId || $recipientId === $user->id) {
            return response()->json(['status' => 'error', 'message' => 'Valid recipient_id is required.'], 422);
        }

        $conversation = $this->messagingService->getOrCreateDirectConversation($user->id, $recipientId);
        $conversation->load(['participants', 'lastMessage']);

        return response()->json([
            'status'  => 'success',
            'data'    => $conversation,
        ], 200);
    }

    /**
     * Send direct message or in-call message.
     * POST /api/v1/messages/send or POST /api/messages/send
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'conversation_id' => 'nullable|integer',
            'recipient_id'    => 'nullable|integer',
            'receiver_id'     => 'nullable|integer',
            'message'         => 'required|string',
            'client_uuid'     => 'nullable|string',
            'call_id'         => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $conversationId = $request->input('conversation_id');
        if (!$conversationId) {
            $recipientId = (int) ($request->input('recipient_id') ?? $request->input('receiver_id'));
            if (!$recipientId) {
                return response()->json(['status' => 'error', 'message' => 'Either conversation_id or recipient_id is required.'], 422);
            }
            $conversation = $this->messagingService->getOrCreateDirectConversation($user->id, $recipientId);
            $conversationId = $conversation->id;
        }

        $payload = [
            'conversation_id' => $conversationId,
            'client_uuid'     => $request->input('client_uuid'),
            'message'         => trim($request->input('message')),
            'type'            => $request->input('type', 'text'),
            'media_url'       => $request->input('media_url'),
            'call_id'         => $request->input('call_id'),
        ];

        try {
            $message = $this->messagingService->sendMessage($user, $payload);
            return response()->json([
                'status'  => 'success',
                'message' => 'Message dispatched successfully.',
                'data'    => [
                    'id'               => $message->id,
                    'client_uuid'      => $message->client_uuid,
                    'conversation_id'  => $message->conversation_id,
                    'sender_id'        => $message->sender_id,
                    'message'          => $message->message,
                    'type'             => $message->type,
                    'call_id'          => $message->call_id,
                    'sent_during_call' => (bool) $message->sent_during_call,
                    'created_at'       => $message->created_at?->toIso8601String() ?? now()->toIso8601String(),
                ],
            ], 200);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get messages for a conversation.
     * GET /api/v1/conversations/{id}/messages
     */
    public function getMessages(Request $request, $id): JsonResponse
    {
        $user = $this->resolveUser($request);
        $conversation = Conversation::findOrFail($id);

        $messages = Message::where('conversation_id', $conversation->id)
            ->with('sender:id,name,account_id,avatar,avatar_frame')
            ->orderBy('id', 'desc')
            ->paginate(50);

        return response()->json([
            'status' => 'success',
            'data'   => $messages,
        ], 200);
    }
}
