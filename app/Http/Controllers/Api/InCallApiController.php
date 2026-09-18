<?php

namespace App\Http\Controllers\Api;

use App\Events\GiftSentEvent;
use App\Events\MessageSentEvent;
use App\Http\Controllers\Controller;
use App\Models\CallMessage;
use App\Models\ChatMessage;
use App\Models\Gift;
use App\Models\Message;
use App\Models\User;
use App\Models\UserGift;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class InCallApiController extends Controller
{
    /**
     * Resolve authenticated or requested user instance.
     */
    protected function resolveUser(Request $request): ?User
    {
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

        try {
            if (Auth::guard('sanctum')->check() && Auth::guard('sanctum')->user()) {
                return Auth::guard('sanctum')->user();
            }
            if ($request->user('sanctum')) {
                return $request->user('sanctum');
            }
            if ($request->user()) {
                return $request->user();
            }
        } catch (\Throwable $e) {}

        $headerUserId = $request->header('X-User-Id') 
                     ?? $request->header('User-Id') 
                     ?? $request->header('user-id') 
                     ?? $request->header('userId');

        if ($headerUserId) {
            $u = User::find($headerUserId) ?? User::where('account_id', $headerUserId)->first();
            if ($u) return $u;
        }

        $idParam = $request->input('sender_id') 
                ?? $request->input('user_id') 
                ?? $request->input('userId') 
                ?? $request->input('caller_id');

        if ($idParam) {
            $u = User::find($idParam) ?? User::where('account_id', $idParam)->first();
            if ($u) return $u;
        }

        return null;
    }

    /**
     * 1. Send In-Call Message API.
     * POST /api/v1/call/message/send or POST /api/call/message/send
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'receiver_id'     => 'required|exists:users,id',
            'call_session_id' => 'required|string',
            'message'         => 'required|string|max:1000',
            'type'            => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $sender = $this->resolveUser($request) ?? auth()->user();
        if (!$sender) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated. Pass Authorization Bearer token or sender_id.',
            ], 401);
        }

        $receiverId = (int) $request->receiver_id;
        $callSessionId = (string) $request->call_session_id;
        $messageText = trim($request->message);
        $type = $request->input('type', 'text');

        // 1. Persist to unified messages table (inbox & live calls common)
        $message = Message::create([
            'sender_id'        => $sender->id,
            'receiver_id'      => $receiverId,
            'call_session_id'  => $callSessionId,
            'call_id'          => $callSessionId,
            'sent_during_call' => true,
            'message'          => $messageText,
            'type'             => $type,
            'is_read'          => false,
        ]);

        // 2. Also save to CallMessage and ChatMessage tables for history sync
        try {
            CallMessage::create([
                'call_session_id' => $callSessionId,
                'sender_id'       => $sender->id,
                'receiver_id'     => $receiverId,
                'type'            => $type,
                'message'         => $messageText,
                'is_read'         => false,
            ]);

            ChatMessage::create([
                'sender_id'   => $sender->id,
                'receiver_id' => $receiverId,
                'message'     => $messageText,
                'type'        => $type,
                'is_read'     => false,
                'is_free'     => true,
                'coin_cost'   => 0,
            ]);
        } catch (\Throwable $e) {}

        $msgData = [
            'id'              => $message->id,
            'call_id'         => $callSessionId,
            'call_session_id' => $callSessionId,
            'sender_id'       => $sender->id,
            'sender_name'     => $sender->display_name ?? $sender->name ?? 'User',
            'sender_avatar'   => $sender->avatar_url,
            'receiver_id'     => $receiverId,
            'message'         => $messageText,
            'type'            => $type,
            'timestamp'       => now()->toIso8601String(),
            'created_at'      => now()->toIso8601String(),
            'sender'          => [
                'id'           => $sender->id,
                'account_id'   => $sender->account_id,
                'display_name' => $sender->display_name ?? $sender->name ?? 'User',
                'avatar_url'   => $sender->avatar_url,
            ],
        ];

        // 3. Broadcast real-time Reverb events to BOTH parties (no suppression)
        try {
            event(new MessageSentEvent($message));
            event(new \App\Events\CallMessageSent($callSessionId, $msgData));
            event(new \App\Events\CallMessageEvent($callSessionId, $msgData));

            // Also save CallSignal for devices polling signaling table
            \App\Models\CallSignal::create([
                'call_session_id' => is_numeric($callSessionId) ? (int)$callSessionId : null,
                'channel_name'    => $callSessionId,
                'sender_id'       => $sender->id,
                'receiver_id'     => $receiverId,
                'type'            => 'message',
                'payload'         => $msgData,
                'is_read'         => false,
            ]);
        } catch (\Throwable $e) {}

        return response()->json([
            'status'  => true,
            'message' => 'Message sent successfully',
            'data'    => $message->loadMissing(['sender:id,account_id,name,nickname,avatar']),
        ], 200);
    }

    /**
     * 2. Send Gift & Wallet Handling API.
     * POST /api/v1/call/gift/send or POST /api/call/gift/send
     */
    public function sendGift(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'receiver_id'     => 'required|integer|exists:users,id',
            'gift_id'         => 'required|integer|exists:gifts,id',
            'call_session_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $sender = $this->resolveUser($request) ?? auth()->user();
        if (!$sender) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated user. Pass Authorization Bearer token.',
            ], 401);
        }

        $receiver = User::findOrFail($request->receiver_id);
        $gift = Gift::findOrFail($request->gift_id);

        $senderBalance = (int) ($sender->wallet_balance ?: $sender->coins ?: ($sender->wallet?->balance ?? 0));
        $giftCost = (int) $gift->coins;

        // 1. Balance check (return INSUFFICIENT_BALANCE error code if balance is low)
        if ($senderBalance < $giftCost) {
            return response()->json([
                'status'  => false,
                'code'    => 'INSUFFICIENT_BALANCE',
                'message' => 'আপনার পর্যাপ্ত পরিমাণে ব্যালেন্স নেই! অনুগ্রহ করে রিচার্জ করুন।',
                'data'    => [
                    'current_balance' => $senderBalance,
                    'required_coins'  => $giftCost,
                    'recharge_url'    => '/api/coin-packages',
                ]
            ], 422);
        }

        $payload = null;

        // 2. Transaction and database history preservation
        DB::transaction(function () use ($sender, $receiver, $gift, $giftCost, $request, &$payload) {
            // Deduct sender balance and coins
            if ($sender->wallet_balance >= $giftCost) {
                $sender->decrement('wallet_balance', $giftCost);
            } else {
                $sender->wallet_balance = 0;
                $sender->save();
            }

            if ($sender->coins >= $giftCost) {
                $sender->decrement('coins', $giftCost);
            } else {
                $sender->coins = 0;
                $sender->save();
            }

            if ($sender->wallet) {
                $sender->wallet->decrement('balance', min($sender->wallet->balance, $giftCost));
            }

            // Increment receiver balance and earnings
            $receiver->increment('received_coins', $giftCost);
            if ($receiver->wallet) {
                $receiver->wallet->increment('earnings', $giftCost);
            }

            // Create UserGift record for profile showcase & history
            $userGift = UserGift::create([
                'sender_id'       => $sender->id,
                'receiver_id'     => $receiver->id,
                'user_id'         => $receiver->id,
                'gift_id'         => $gift->id,
                'call_session_id' => $request->call_session_id,
                'coin_amount'     => $giftCost,
                'total_coins'     => $giftCost,
                'quantity'        => 1,
                'coins_per_unit'  => $giftCost,
                'context'         => 'live_call',
            ]);

            // Create GiftTransaction record
            try {
                \App\Models\GiftTransaction::create([
                    'sender_id'       => $sender->id,
                    'receiver_id'     => $receiver->id,
                    'gift_id'         => $gift->id,
                    'call_session_id' => $request->call_session_id,
                    'quantity'        => 1,
                    'total_coins'     => $giftCost,
                    'status'          => 'completed',
                ]);
            } catch (\Throwable $e) {}

            $animationUrl = $gift->animation_full_url 
                         ?: $gift->animation_url 
                         ?: $gift->animation_asset_url 
                         ?: $gift->image_url 
                         ?: $gift->image;

            $payload = [
                'call_session_id'     => (string) $request->call_session_id,
                'room_id'             => (string) $request->call_session_id,
                'stream_id'           => (string) $request->call_session_id,
                'sender_id'           => $sender->id,
                'receiver_id'         => $receiver->id,
                'id'                  => $gift->id,
                'gift_id'             => $gift->id,
                'gift_name'           => $gift->name,
                'name'                => $gift->name,
                'image_url'           => $gift->image_url ?: $gift->image,
                'icon_url'            => $gift->image_url ?: $gift->image,
                'animation_url'       => $animationUrl,
                'animation_full_url'  => $animationUrl,
                'animation_asset_url' => $animationUrl,
                'file_url'            => $animationUrl,
                'animation_type'      => $gift->animation_type ?: 'svga',
                'format'              => $gift->animation_type ?: 'svga',
                'display_type'        => 'fullscreen',
                'quantity'            => 1,
                'coins'               => $giftCost,
                'total_coins'         => $giftCost,
                'sender' => [
                    'id'           => $sender->id,
                    'name'         => $sender->display_name ?? $sender->name ?? $sender->nickname ?? 'User',
                    'display_name' => $sender->display_name ?? $sender->name ?? $sender->nickname ?? 'User',
                    'avatar'       => $sender->avatar_url,
                    'avatar_url'   => $sender->avatar_url,
                ],
                'receiver' => [
                    'id'           => $receiver->id,
                    'name'         => $receiver->display_name ?? $receiver->name ?? $receiver->nickname ?? 'User',
                    'display_name' => $receiver->display_name ?? $receiver->name ?? $receiver->nickname ?? 'User',
                    'avatar'       => $receiver->avatar_url,
                    'avatar_url'   => $receiver->avatar_url,
                ],
                'gift' => [
                    'id'                  => $gift->id,
                    'name'                => $gift->name,
                    'gift_name'           => $gift->name,
                    'coins'               => $giftCost,
                    'image_url'           => $gift->image_url ?: $gift->image,
                    'icon_url'            => $gift->image_url ?: $gift->image,
                    'animation_url'       => $animationUrl,
                    'animation_full_url'  => $animationUrl,
                    'animation_asset_url' => $animationUrl,
                    'file_url'            => $animationUrl,
                    'animation_type'      => $gift->animation_type ?: 'svga',
                    'display_type'        => 'fullscreen',
                ],
                'timestamp' => now()->toIso8601String(),
            ];

            // Reverb broadcast to call session channels and user channels (NO suppression so both parties receive it!)
            try {
                event(new \App\Events\GiftSentEvent($payload));
                event(new \App\Events\GiftSent((string)$request->call_session_id, $payload));
                event(new \App\Events\LiveGiftSentEvent((string)$request->call_session_id, $payload));

                // Also save CallSignal for devices polling signaling table
                \App\Models\CallSignal::create([
                    'call_session_id' => is_numeric($request->call_session_id) ? (int)$request->call_session_id : null,
                    'channel_name'    => (string)$request->call_session_id,
                    'sender_id'       => $sender->id,
                    'receiver_id'     => $receiver->id,
                    'type'            => 'gift',
                    'payload'         => $payload,
                    'is_read'         => false,
                ]);
            } catch (\Throwable $e) {}
        });

        $freshSender = $sender->fresh();

        return response()->json([
            'status'          => true,
            'success'         => true,
            'message'         => 'Gift sent successfully',
            'current_balance' => (int) ($freshSender->wallet_balance ?? $freshSender->coins ?? 0),
            'gift_data'       => $payload,
            'data'            => $payload,
        ], 200);
    }

    /**
     * 3. Dynamic Quick Messages API (to eliminate hardcoded strings in Flutter).
     * GET /api/v1/call/quick-messages or GET /api/call/quick-messages
     */
    public function getQuickMessages(Request $request): JsonResponse
    {
        $messages = [
            ["id" => 1, "text" => "Hi, what's up babe?"],
            ["id" => 2, "text" => "Be my girlfriend"],
            ["id" => 3, "text" => "You look beautiful!"],
            ["id" => 4, "text" => "Can we talk for a few minutes?"],
            ["id" => 5, "text" => "Sending you lots of love ❤️"],
            ["id" => 6, "text" => "Let's video chat!"],
            ["id" => 7, "text" => "How was your day?"],
            ["id" => 8, "text" => "Nice to meet you!"]
        ];

        return response()->json([
            'status' => true,
            'data'   => $messages,
        ], 200);
    }

    /**
     * 4. Receiver Profile Gift History (Me Screen / Profile Screen) API.
     * GET /api/v1/user/received-gifts or GET /api/user/received-gifts
     */
    public function getReceivedGifts(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request) ?? auth()->user();
        if (!$user) {
            $userIdParam = $request->input('user_id') ?? $request->input('id');
            if ($userIdParam) {
                $user = User::find($userIdParam) ?? User::where('account_id', $userIdParam)->first();
            }
        }

        if (!$user) {
            $user = User::first();
        }

        if (!$user) {
            return response()->json([
                'status'               => true,
                'total_received_coins' => 0,
                'gifts'                => [],
            ], 200);
        }

        // Aggregate user gifts grouped by gift_id
        $groupedGifts = UserGift::where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('receiver_id', $user->id);
            })
            ->with('gift')
            ->select('gift_id', DB::raw('SUM(COALESCE(quantity, 1)) as total_quantity'), DB::raw('SUM(COALESCE(coin_amount, total_coins, 0)) as total_coins_sum'))
            ->groupBy('gift_id')
            ->orderByDesc('total_coins_sum')
            ->get();

        $totalCoinsReceived = (int) UserGift::where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('receiver_id', $user->id);
            })->sum(DB::raw('COALESCE(coin_amount, total_coins, 0)'));

        if ($totalCoinsReceived === 0 && !empty($user->received_coins)) {
            $totalCoinsReceived = (int) $user->received_coins;
        }

        $giftsList = [];
        foreach ($groupedGifts as $item) {
            $gift = $item->gift;
            if (!$gift) continue;

            $giftsList[] = [
                'gift_id'        => $gift->id,
                'gift_name'      => $gift->name,
                'icon_url'       => $gift->image_url ?: $gift->image ?: url('/images/default-gift.png'),
                'count'          => (int) $item->total_quantity,
                'coins_per_unit' => (int) $gift->coins,
                'total_coins'    => (int) $item->total_coins_sum,
                'animation_url'  => $gift->animation_full_url ?: $gift->animation_url,
            ];
        }

        return response()->json([
            'status'               => true,
            'total_received_coins' => $totalCoinsReceived,
            'gifts'                => $giftsList,
        ], 200);
    }
}
