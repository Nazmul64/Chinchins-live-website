<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CoinTransaction;
use App\Models\Gift;
use App\Models\User;
use App\Models\UserGift;
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
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getCatalog(Request $request): JsonResponse
    {
        $query = Gift::where('is_active', true)->orderBy('sort_order')->orderBy('coins', 'desc');

        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', strtolower(trim($request->category)));
        }

        $gifts = $query->get();

        // Unique categories with counts
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
     *
     * @param Request $request
     * @param string|null $id (User ID or account_id, or 'me')
     * @return JsonResponse
     */
    public function getUserReceivedGifts(Request $request, ?string $id = null): JsonResponse
    {
        // 1. Determine target user
        if ($id === null || $id === 'me') {
            $user = $this->resolveUser($request);
            if (!$user) {
                // If not found, get first user or return 404
                $user = User::first();
            }
        } else {
            $user = User::where('id', $id)->orWhere('account_id', $id)->first();
        }

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'User not found.',
            ], 404);
        }

        // 2. Fetch all user gifts grouped by gift_id to compute exact total counts (e.g. x2, x4, x32)
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
                'formatted_coins'      => Gift::formatCoins($coinsPerUnit), // e.g. "17.70K", "17K", "9.99K"
                'quantity'             => $qty,
                'count_label'          => 'x' . $qty, // e.g. "x2", "x1", "x4", "x32"
                'total_coins'          => $totalCoins,
                'formatted_total'      => Gift::formatCoins($totalCoins),
                'badge'                => $gift->badge,
            ];
        }

        // Determine Top Fan (the user who sent the most coin value to this host)
        $topFanRecord = UserGift::where('user_id', $user->id)
            ->whereNotNull('sender_id')
            ->where('sender_id', '!=', $user->id)
            ->select('sender_id', DB::raw('SUM(total_coins) as fan_coins'))
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
            ];
        } else {
            $topFan = [
                'id'           => 999,
                'account_id'   => '1000293841',
                'name'         => 'Sajid',
                'avatar_url'   => asset('assets/images/defaults/avatar-male.png'),
                'fan_coins'    => 54200,
                'formatted'    => '54.20K',
            ];
        }

        // Determine Charm Level dynamically based on total coins received
        $calculatedLevel = max(1, (int) floor(sqrt($totalCoinsReceived / 2000)) + 1);
        $userLevel = $user->level ?: $calculatedLevel;
        $cleanLevel = is_numeric($userLevel) ? $userLevel : (preg_replace('/[^0-9]/', '', (string)$userLevel) ?: $calculatedLevel);
        $charmLevel = [
            'level'        => (int) $cleanLevel,
            'level_tag'    => 'Lv' . $cleanLevel,
            'progress'     => min(100, (int) (($totalCoinsReceived % 10000) / 100)),
        ];

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
                'summary'              => [
                    'total_unique_gifts' => count($formattedGifts),
                    'total_items_count'  => $totalItemsCount,
                    'total_coins'        => $totalCoinsReceived,
                    'formatted_coins'    => Gift::formatCoins($totalCoinsReceived),
                ],
                // For User Profile Preview Card (top 8 gifts as shown in Screenshot 1)
                'profile_preview_gifts' => array_slice($formattedGifts, 0, 8),
                // Full grid list for "Gifts Received" screen (Screenshot 2)
                'gifts_received'        => $formattedGifts,
            ],
        ]);
    }

    /**
     * 3. Send Gift (From Fan/Caller to Host/Streamer).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendGift(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required',
            'gift_id'     => 'required|exists:gifts,id',
            'quantity'    => 'nullable|integer|min:1|max:1000',
            'context'     => 'nullable|string|in:profile,live_call,chat,random_match',
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
                'message' => 'Unauthenticated. Please provide Bearer token or sender_id.',
            ], 401);
        }

        $receiverId = $request->input('receiver_id');
        $receiver = User::where('id', $receiverId)->orWhere('account_id', $receiverId)->first();

        if (!$receiver) {
            return response()->json([
                'status'  => false,
                'message' => 'Receiver user not found.',
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
        $totalCost = $gift->coins * $quantity;

        // Check if sender has enough coins
        if ($sender->coins < $totalCost) {
            return response()->json([
                'status'         => false,
                'message'        => "Insufficient coin balance! You need {$totalCost} coins but have {$sender->coins} coins.",
                'required_coins' => $totalCost,
                'current_coins'  => $sender->coins,
                'shortage'       => $totalCost - $sender->coins,
            ], 402);
        }

        return DB::transaction(function () use ($sender, $receiver, $gift, $quantity, $totalCost, $request) {
            // 1. Deduct coins from sender
            $sender->decrement('coins', $totalCost);
            $senderBalanceAfter = $sender->fresh()->coins;

            // 2. Credit host/receiver earnings (e.g. 50% revenue split or full diamond earnings)
            $hostEarnings = (int) floor($totalCost * 0.50); // 50% default host cut
            $receiver->increment('coins', $hostEarnings);
            $receiverBalanceAfter = $receiver->fresh()->coins;

            // 3. Record in UserGift ledger
            $userGift = UserGift::create([
                'user_id'         => $receiver->id,
                'sender_id'       => $sender->id,
                'gift_id'         => $gift->id,
                'quantity'        => $quantity,
                'coins_per_unit'  => $gift->coins,
                'total_coins'     => $totalCost,
                'call_session_id' => $request->input('call_session_id'),
                'context'         => $request->input('context', 'profile'),
            ]);

            // 4. Log Coin Transactions
            CoinTransaction::create([
                'user_id'       => $sender->id,
                'type'          => 'gift_sent',
                'amount'        => -$totalCost,
                'balance_after' => $senderBalanceAfter,
                'description'   => "Sent {$quantity}x {$gift->name} to {$receiver->display_name}",
                'reference_id'  => $userGift->id,
            ]);

            CoinTransaction::create([
                'user_id'       => $receiver->id,
                'type'          => 'gift_received',
                'amount'        => $hostEarnings,
                'balance_after' => $receiverBalanceAfter,
                'description'   => "Received {$quantity}x {$gift->name} from {$sender->display_name} (+{$hostEarnings} coins)",
                'reference_id'  => $userGift->id,
            ]);

            // Get updated total received quantity for this specific gift on receiver's profile
            $newTotalGiftCount = (int) UserGift::where('user_id', $receiver->id)
                ->where('gift_id', $gift->id)
                ->sum('quantity');

            return response()->json([
                'status'  => true,
                'message' => "Successfully sent {$quantity}x {$gift->name} to {$receiver->display_name}!",
                'data'    => [
                    'transaction_id'       => $userGift->id,
                    'gift'                 => [
                        'id'              => $gift->id,
                        'name'            => $gift->name,
                        'coins'           => $gift->coins,
                        'formatted_coins' => $gift->formatted_coins,
                        'image_url'       => $gift->image_url,
                        'animation_url'   => $gift->animation_full_url,
                        'animation_type'  => $gift->animation_type,
                        'sound_url'       => $gift->sound_url,
                        'is_broadcast'    => $gift->is_broadcast,
                    ],
                    'quantity'             => $quantity,
                    'count_label'          => 'x' . $quantity,
                    'total_cost'           => $totalCost,
                    'formatted_cost'       => Gift::formatCoins($totalCost),
                    'sender'               => [
                        'id'              => $sender->id,
                        'display_name'    => $sender->display_name,
                        'remaining_coins' => $senderBalanceAfter,
                        'formatted_coins' => Gift::formatCoins($senderBalanceAfter),
                    ],
                    'receiver'             => [
                        'id'              => $receiver->id,
                        'display_name'    => $receiver->display_name,
                        'updated_slot'    => 'x' . $newTotalGiftCount,
                    ],
                ],
            ]);
        });
    }
}
