<?php

namespace App\Http\Controllers\Api;

use App\Events\LiveGiftSentEvent;
use App\Http\Controllers\Controller;
use App\Models\CharmLevelSetting;
use App\Models\CoinTransaction;
use App\Models\Gift;
use App\Models\GiftTransaction;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserGift;
use App\Models\UserLike;
use App\Models\Wallet;
use App\Services\PushNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GiftApiController extends Controller
{
    /**
     * Resolve the requesting user from Sanctum token, header, or user_id fallback.
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
                     ?? $request->header('user-id') 
                     ?? $request->header('userId');

        if ($headerUserId) {
            $u = User::find($headerUserId) ?? User::where('account_id', $headerUserId)->first();
            if ($u) return $u;
        }

        $idParam = $request->input('sender_id') ?? $request->input('user_id') ?? $request->input('userId') ?? $request->input('id');
        if ($idParam) {
            $u = User::find($idParam) ?? User::where('account_id', $idParam)->first();
            if ($u) return $u;
        }

        return null;
    }

    /**
     * 1. Get Gift Catalog (Store of gifts to send in live, calls, chat, profile).
     */
    public function getCatalog(Request $request): JsonResponse
    {
        $query = Gift::where('is_active', true)->orderBy('sort_order')->orderBy('coins', 'desc');

        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', strtolower(trim($request->category)));
        }

        $gifts = $query->get();

        $allCategories = Gift::where('is_active', true)
            ->select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();

        $sender = $this->resolveUser($request);

        return response()->json([
            'status'  => true,
            'message' => 'Gifts catalog loaded successfully.',
            'data'    => [
                'user_balance' => [
                    'coins'           => $sender ? $sender->coins : 0,
                    'formatted_coins' => $sender ? Gift::formatCoins($sender->coins) : '0',
                ],
                'categories'   => array_merge(['all' => $gifts->count()], $allCategories),
                'total_gifts'  => $gifts->count(),
                'gifts'        => $gifts,
            ],
        ]);
    }

    /**
     * 2. Get User's Received Gifts (For Profile Screen Card & Full Screen Gifts Received Page).
     */
    public function getUserReceivedGifts(Request $request, ?string $id = null): JsonResponse
    {
        if ($id === null || $id === 'me') {
            $user = $this->resolveUser($request) ?? User::first();
        } else {
            $user = User::where('id', $id)->orWhere('account_id', $id)->first();
        }

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'User not found.',
            ], 404);
        }

        // Aggregate gifts received by this user
        $giftSummaries = UserGift::where('user_id', $user->id)
            ->with('gift')
            ->select('gift_id', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(total_coins) as total_coins_sum'), DB::raw('MAX(coins_per_unit) as unit_coins'))
            ->groupBy('gift_id')
            ->orderBy('total_coins_sum', 'desc')
            ->get();

        $formattedGifts = [];
        $totalItemsCount = 0;
        $totalCoinsReceived = 0;

        foreach ($giftSummaries as $item) {
            $gift = $item->gift;
            if (!$gift) continue;

            $qty = (int) $item->total_quantity;
            $coinsPerUnit = (int) ($item->unit_coins ?: $gift->coins);
            $totalCoins = (int) ($item->total_coins_sum ?: ($coinsPerUnit * $qty));

            $totalItemsCount += $qty;
            $totalCoinsReceived += $totalCoins;

            $formattedGifts[] = [
                'gift_id'              => $gift->id,
                'name'                 => $gift->name,
                'category'             => $gift->category,
                'image'                => $gift->image,
                'image_url'            => $gift->image_url,
                'animation_url'        => $gift->animation_full_url,
                'animation_type'       => $gift->animation_type,
                'coins'                => $coinsPerUnit,
                'formatted_coins'      => Gift::formatCoins($coinsPerUnit),
                'quantity'             => $qty,
                'count_label'          => 'x' . $qty,
                'total_coins'          => $totalCoins,
                'formatted_total'      => Gift::formatCoins($totalCoins),
                'badge'                => $gift->badge,
            ];
        }

        // Top Fan calculation
        $topFanRecord = UserGift::where('user_id', $user->id)
            ->whereNotNull('sender_id')
            ->where('sender_id', '!=', $user->id)
            ->select('sender_id', DB::raw('SUM(total_coins) as fan_coins'), DB::raw('SUM(quantity) as gifts_count'))
            ->groupBy('sender_id')
            ->orderBy('fan_coins', 'desc')
            ->with('sender')
            ->first();

        $topFan = null;
        if ($topFanRecord && $topFanRecord->sender) {
            $topFan = [
                'id'           => $topFanRecord->sender->id,
                'account_id'   => $topFanRecord->sender->account_id,
                'name'         => $topFanRecord->sender->display_name,
                'avatar_url'   => $topFanRecord->sender->avatar_url,
                'fan_coins'    => (int) $topFanRecord->fan_coins,
                'formatted'    => Gift::formatCoins($topFanRecord->fan_coins),
                'crown'        => 'gold',
                'badge_icon'   => 'crown',
            ];
        } else {
            $topFan = [
                'id'           => 999,
                'account_id'   => '1000293841',
                'name'         => 'Sajid',
                'avatar_url'   => asset('assets/images/defaults/avatar-male.png'),
                'fan_coins'    => 54200,
                'formatted'    => '54.20K',
                'crown'        => 'gold',
                'badge_icon'   => 'crown',
            ];
        }

        // Charm Level dynamically calculated from configured admin level thresholds
        $charmLevel = CharmLevelSetting::calculateLevel($totalCoinsReceived);

        // Total Likes received
        $totalLikes = (int) UserLike::where('user_id', $user->id)->sum('likes_count');

        return response()->json([
            'status'  => true,
            'message' => 'User gifts loaded successfully.',
            'data'    => [
                'user' => [
                    'id'           => $user->id,
                    'account_id'   => $user->account_id,
                    'display_name' => $user->display_name,
                    'avatar_url'   => $user->avatar_url,
                    'coins'        => $user->coins,
                ],
                'charm_level'          => $charmLevel,
                'top_fan'              => $topFan,
                'likes'                => [
                    'total_likes'     => $totalLikes,
                    'formatted_likes' => Gift::formatCoins($totalLikes),
                ],
                'summary'              => [
                    'total_unique_gifts' => count($formattedGifts),
                    'total_items_count'  => $totalItemsCount,
                    'total_coins'        => $totalCoinsReceived,
                    'formatted_coins'    => Gift::formatCoins($totalCoinsReceived),
                ],
                'profile_preview_gifts' => array_slice($formattedGifts, 0, 8),
                'gifts_received'        => $formattedGifts,
            ],
        ]);
    }

    /**
     * 3. Get Top Fans Leaderboard for a Host (When user taps on Top Fans).
     */
    public function getTopFans(Request $request, ?string $id = null): JsonResponse
    {
        if ($id === null || $id === 'me') {
            $user = $this->resolveUser($request) ?? User::first();
        } else {
            $user = User::where('id', $id)->orWhere('account_id', $id)->first();
        }

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User not found.'], 404);
        }

        $fanRecords = UserGift::where('user_id', $user->id)
            ->whereNotNull('sender_id')
            ->where('sender_id', '!=', $user->id)
            ->select('sender_id', DB::raw('SUM(total_coins) as fan_coins'), DB::raw('SUM(quantity) as gifts_count'))
            ->groupBy('sender_id')
            ->orderBy('fan_coins', 'desc')
            ->with('sender')
            ->take(50)
            ->get();

        $topFansList = [];
        $rank = 1;

        foreach ($fanRecords as $record) {
            if (!$record->sender) continue;

            $crown = $rank === 1 ? 'gold' : ($rank === 2 ? 'silver' : ($rank === 3 ? 'bronze' : null));
            $topFansList[] = [
                'rank'            => $rank,
                'user_id'         => $record->sender->id,
                'account_id'      => $record->sender->account_id,
                'display_name'    => $record->sender->display_name,
                'avatar_url'      => $record->sender->avatar_url,
                'gender'          => $record->sender->gender,
                'total_coins'     => (int) $record->fan_coins,
                'formatted_coins' => Gift::formatCoins($record->fan_coins),
                'gifts_count'     => (int) $record->gifts_count,
                'crown_type'      => $crown,
                'badge'           => $rank <= 3 ? "Top #{$rank}" : "#{$rank}",
            ];
            $rank++;
        }

        // Fallback demo data if empty
        if (empty($topFansList)) {
            $topFansList = [
                [
                    'rank'            => 1,
                    'user_id'         => 999,
                    'account_id'      => '1000293841',
                    'display_name'    => 'Sajid',
                    'avatar_url'      => asset('assets/images/defaults/avatar-male.png'),
                    'gender'          => 'male',
                    'total_coins'     => 54200,
                    'formatted_coins' => '54.20K',
                    'gifts_count'     => 14,
                    'crown_type'      => 'gold',
                    'badge'           => 'Top #1',
                ]
            ];
        }

        return response()->json([
            'status'  => true,
            'message' => 'Top fans leaderboard loaded successfully.',
            'data'    => [
                'host'     => [
                    'id'           => $user->id,
                    'account_id'   => $user->account_id,
                    'display_name' => $user->display_name,
                    'avatar_url'   => $user->avatar_url,
                ],
                'top_fans' => $topFansList,
            ],
        ]);
    }

    /**
     * 4. Send Like / Love Heart to Host during video call or profile.
     */
    public function sendLike(Request $request, ?string $id = null): JsonResponse
    {
        $sender = $this->resolveUser($request);
        if (!$sender) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $receiverId = $id ?? $request->input('receiver_id') ?? $request->input('user_id');
        $receiver = User::where('id', $receiverId)->orWhere('account_id', $receiverId)->first();

        if (!$receiver) {
            return response()->json(['status' => false, 'message' => 'Host not found.'], 404);
        }

        $context = $request->input('context', 'call');

        $like = UserLike::firstOrNew([
            'user_id'   => $receiver->id,
            'sender_id' => $sender->id,
            'context'   => $context,
        ]);

        $like->likes_count = ($like->likes_count ?? 0) + (int) ($request->input('count', 1) ?: 1);
        $like->save();

        $totalLikes = (int) UserLike::where('user_id', $receiver->id)->sum('likes_count');

        return response()->json([
            'status'  => true,
            'message' => 'Love heart sent!',
            'data'    => [
                'receiver_id'     => $receiver->id,
                'total_likes'     => $totalLikes,
                'formatted_likes' => Gift::formatCoins($totalLikes),
            ],
        ]);
    }

    /**
     * 5. Send Gift (Live Streaming Gift & Wallet Engine with Reverb Real-Time Broadcasting).
     * POST /api/gifts/send or POST /api/gift/send or POST /api/live/send-gift
     */
     public function sendGift(Request $request): JsonResponse
     {
         $validator = Validator::make($request->all(), [
             'stream_id'       => 'nullable',
             'receiver_id'     => 'required',
             'gift_id'         => 'required|exists:gifts,id',
             'quantity'        => 'nullable|integer|min:1|max:1000',
             'context'         => 'nullable|string|in:profile,live_call,chat,random_match,live_stream',
             'call_session_id' => 'nullable|integer',
         ]);
 
         if ($validator->fails()) {
             return response()->json([
                 'status'  => false,
                 'message' => 'Validation error.',
                 'errors'  => $validator->errors(),
             ], 422);
         }
 
         $sender = $this->resolveUser($request);
         if (!$sender) {
             return response()->json([
                 'status'  => false,
                 'message' => 'Unauthenticated. Please provide Bearer token or user_id.',
             ], 401);
         }
 
         $receiverId = $request->input('receiver_id');
         $receiver = User::where('id', $receiverId)->orWhere('account_id', $receiverId)->first();
 
         if (!$receiver) {
             return response()->json([
                 'status'  => false,
                 'message' => 'Receiver host not found.',
             ], 404);
         }
 
         if ($sender->id === $receiver->id) {
             return response()->json([
                 'status'  => false,
                 'message' => 'You cannot send a gift to yourself.',
             ], 400);
         }
 
         $gift = Gift::findOrFail($request->input('gift_id'));
         if (!$gift->is_active) {
             return response()->json([
                 'status'  => false,
                 'message' => 'This gift is currently unavailable.',
             ], 400);
         }
 
         $quantity = (int) ($request->input('quantity', 1) ?: 1);
         $coinPrice = (int) ($gift->coin_price ?: $gift->coins);
         $totalCost = $coinPrice * $quantity;
 
         return DB::transaction(function () use ($sender, $receiver, $gift, $quantity, $coinPrice, $totalCost, $request) {
             // 1. Lock Sender Wallet row to prevent race conditions & negative balance
             $senderWallet = Wallet::where('user_id', $sender->id)->lockForUpdate()->first();
             if (!$senderWallet) {
                 $senderWallet = $sender->getOrCreateWallet();
                 $senderWallet = Wallet::where('user_id', $sender->id)->lockForUpdate()->first();
             }
 
             // If wallet balance is lower than user coins, sync
             if ($senderWallet->balance < $sender->coins) {
                 $senderWallet->balance = (int) $sender->coins;
                 $senderWallet->save();
             }
 
             if (!$senderWallet || $senderWallet->balance < $totalCost) {
                 return response()->json([
                     'status'         => false,
                     'message'        => "Insufficient coins! You need {$totalCost} coins but have " . ($senderWallet ? $senderWallet->balance : 0) . " coins.",
                     'required_coins' => $totalCost,
                     'current_coins'  => $senderWallet ? $senderWallet->balance : 0,
                     'shortage'       => $totalCost - ($senderWallet ? $senderWallet->balance : 0),
                 ], 400);
             }
 
             // 2. Deduct coins from sender wallet & user balance
             $senderWallet->decrement('balance', $totalCost);
             $sender->decrement('coins', $totalCost);
             $senderBalanceAfter = (int) $senderWallet->fresh()->balance;
 
             // 3. Credit receiver/host earnings
             $hostEarnings = $totalCost; // 100% earnings into Host Withdrawable Wallet
             $receiverWallet = Wallet::firstOrCreate(['user_id' => $receiver->id]);
             $receiverWallet->increment('earnings', $hostEarnings);
             $receiver->increment('coins', (int) floor($totalCost * 0.50)); // standard user balance share
 
             $streamId = (string) ($request->input('stream_id') ?: ('stream_' . $receiver->id));
 
             // 4. Record Gift Transaction for live stream history
             $giftTx = GiftTransaction::create([
                 'stream_id'   => $streamId,
                 'sender_id'   => $sender->id,
                 'receiver_id' => $receiver->id,
                 'gift_id'     => $gift->id,
                 'coins_spent' => $totalCost,
             ]);
 
             // 5. Record UserGift collection ledger
             $userGift = UserGift::create([
                 'user_id'         => $receiver->id,
                 'sender_id'       => $sender->id,
                 'gift_id'         => $gift->id,
                 'quantity'        => $quantity,
                 'coins_per_unit'  => $coinPrice,
                 'total_coins'     => $totalCost,
                 'call_session_id' => $request->input('call_session_id'),
                 'context'         => $request->input('context', 'live_stream'),
             ]);
 
             // 6. Log Coin Transaction ledger
             CoinTransaction::create([
                 'user_id'       => $sender->id,
                 'type'          => 'gift_sent',
                 'amount'        => -$totalCost,
                 'balance_after' => $senderBalanceAfter,
                 'description'   => "Sent {$quantity}x {$gift->name} to {$receiver->display_name}",
                 'reference_id'  => $giftTx->id,
             ]);
 
             CoinTransaction::create([
                 'user_id'       => $receiver->id,
                 'type'          => 'gift_received',
                 'amount'        => $hostEarnings,
                 'balance_after' => (int) $receiverWallet->fresh()->earnings,
                 'description'   => "Received {$quantity}x {$gift->name} from {$sender->display_name} (+{$hostEarnings} earnings)",
                 'reference_id'  => $giftTx->id,
             ]);
 
             // 7. Real-Time Broadcast Payload for Flutter / Web Clients
             $eventData = [
                 'stream_id'      => $streamId,
                 'sender_id'      => $sender->id,
                 'sender_name'    => $sender->display_name ?? $sender->name,
                 'sender_avatar'  => $sender->avatar_url,
                 'gift_id'        => $gift->id,
                 'gift_name'      => $gift->name,
                 'icon_url'       => $gift->icon_url ?: $gift->image_url,
                 'file_url'       => $gift->file_url ?: $gift->animation_full_url,
                 'format'         => $gift->format ?? ($gift->animation_type ?: 'svga'),
                 'display_type'   => $gift->display_type ?? ($gift->is_broadcast ? 'fullscreen' : 'bubble'),
                 'quantity'       => $quantity,
                 'coins_spent'    => $totalCost,
             ];
 
             // 8. Trigger Laravel Reverb Real-Time Broadcast Event (live-stream.{stream_id} -> gift.received)
             try {
                 broadcast(new LiveGiftSentEvent($streamId, $eventData))->toOthers();
             } catch (\Throwable $e) {
                 \Illuminate\Support\Facades\Log::warning("Reverb broadcast error: " . $e->getMessage());
             }
 
             // 9. Send In-App & FCM Push Notification
             Notification::createNotification(
                 userId: $receiver->id,
                 actorId: $sender->id,
                 type: 'gift',
                 title: "New Gift Received! 🎁",
                 message: "{$sender->display_name} sent you {$quantity}x {$gift->name} (+{$totalCost} coins)!",
                 data: [
                     'gift_id'      => $gift->id,
                     'gift_name'    => $gift->name,
                     'gift_icon'    => $gift->image_url,
                     'quantity'     => $quantity,
                     'coins_earned' => $hostEarnings,
                     'sender_id'    => $sender->id,
                     'sender_name'  => $sender->display_name,
                 ]
             );
 
             try {
                 PushNotificationService::sendGiftPush($sender, $receiver, $gift, $hostEarnings);
             } catch (\Throwable $e) {}
 
             return response()->json([
                 'status'            => true,
                 'message'           => 'Gift sent successfully!',
                 'remaining_balance' => $senderBalanceAfter,
                 'gift'              => $eventData,
                 'data'              => [
                     'transaction_id'    => $giftTx->id,
                     'remaining_balance' => $senderBalanceAfter,
                     'gift'              => $eventData,
                 ],
             ]);
         });
     }
}

