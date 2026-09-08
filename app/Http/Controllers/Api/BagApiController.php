<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BagItem;
use App\Models\CoinTransaction;
use App\Models\User;
use App\Models\UserBagItem;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BagApiController extends Controller
{
    /**
     * Resolve authenticated user resiliently across Bearer token, header, or query param.
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

        $idParam = $request->input('user_id') ?? $request->input('userId') ?? $request->input('id');
        if ($idParam) {
            $u = User::find($idParam) ?? User::where('account_id', $idParam)->first();
            if ($u) return $u;
        }

        return null;
    }

    /**
     * 1. Get User's Personal Bag / Backpack Inventory.
     * GET /api/bag or GET /api/my-bag
     */
    public function index(Request $request): JsonResponse
    {
        BagItem::seedDefaultItems();

        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized. Please provide valid Authorization Bearer token or user_id.',
            ], 401);
        }

        $category = $request->input('category', 'all'); // 'all', 'coupon', 'avatar_frame', 'chat_style', 'profile_card', 'entrance_bubble', 'big_entrance'
        $status = $request->input('status', 'unused');    // 'unused', 'used', 'expired', 'all'

        $now = Carbon::now();

        // Auto-update expired items
        UserBagItem::where('user_id', $user->id)
            ->where('status', '!=', 'expired')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', $now)
            ->update([
                'status'      => 'expired',
                'is_equipped' => false,
            ]);

        $query = UserBagItem::with('bagItem')
            ->where('user_id', $user->id);

        if ($status !== 'all' && in_array($status, ['unused', 'used', 'expired'])) {
            $query->where('status', $status);
        }

        if ($category !== 'all' && in_array($category, array_keys(BagItem::categories()))) {
            $query->whereHas('bagItem', function ($q) use ($category) {
                $q->where('category', $category);
            });
        }

        $userItems = $query->orderBy('is_equipped', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        // Calculate count per category for tabs
        $categoriesList = [];
        foreach (BagItem::categories() as $key => $label) {
            $count = UserBagItem::where('user_id', $user->id)
                ->where('status', $status === 'all' ? 'unused' : $status)
                ->whereHas('bagItem', fn($q) => $q->where('category', $key))
                ->count();

            $categoriesList[] = [
                'category'      => $key,
                'name'          => $label,
                'count'         => $count,
                'is_active_tab' => $category === $key || ($category === 'all' && $key === 'coupon'),
                'icon_url'      => \App\Models\CoinPackage::resolveAssetUrl("uploads/my_bag/" . match($key) {
                    'coupon'          => 'coupon_sale_yellow.svg',
                    'avatar_frame'    => 'frame_royal_amethyst.svg',
                    'chat_style'      => 'chat_bubble_neon_pink.svg',
                    'profile_card'    => 'profile_card_aurora_galaxy.svg',
                    'entrance_bubble' => 'entrance_bubble_gold_crown.svg',
                    'big_entrance'    => 'big_entrance_sports_car.svg',
                    default           => 'coupon_sale_yellow.svg',
                }),
            ];
        }

        $formattedItems = $userItems->map(function ($uItem) use ($now) {
            $item = $uItem->bagItem;
            if (!$item) return null;

            return [
                'user_bag_item_id'    => $uItem->id,
                'bag_item_id'         => $item->id,
                'name'                => $item->name,
                'category'            => $item->category,
                'category_name'       => $item->category_name,
                'code'                => $item->code,
                'badge'               => $item->badge,
                'discount_percent'    => $item->discount_percent,
                'coupon_value'        => $item->coupon_value,
                'icon_url'            => $item->icon_full_url,
                'image_url'           => $item->image_full_url,
                'preview_url'         => $item->preview_full_url,
                'animation_url'       => $item->animation_full_url,
                'format'              => $item->format,
                'effect_type'         => $item->effect_type,
                'description'         => $item->description,
                'quantity'            => (int) $uItem->quantity,
                'status'              => $uItem->status,
                'is_equipped'         => (bool) $uItem->is_equipped,
                'is_valid'            => (bool) $uItem->is_valid,
                'remaining_seconds'   => $uItem->remaining_seconds,
                'remaining_days_text' => $uItem->remaining_days_text,
                'started_at'          => $uItem->started_at?->toIso8601String(),
                'expires_at'          => $uItem->expires_at?->toIso8601String(),
                'used_at'             => $uItem->used_at?->toIso8601String(),
                'can_gift'            => (bool) $item->is_giftable,
            ];
        })->filter()->values();

        return response()->json([
            'status'  => true,
            'message' => 'My Bag items retrieved successfully.',
            'data'    => [
                'user_coins'      => (int) $user->coins,
                'selected_tab'    => $category,
                'selected_status' => $status,
                'has_items'       => count($formattedItems) > 0,
                'empty_message'   => 'You have no items yet',
                'categories'      => $categoriesList,
                'items_count'     => count($formattedItems),
                'items'           => $formattedItems,
            ],
        ], 200);
    }

    /**
     * 2. Get My Bag Store Catalog (Items available to purchase).
     * GET /api/bag/store or GET /api/my-bag/store
     */
    public function storeCatalog(Request $request): JsonResponse
    {
        BagItem::seedDefaultItems();

        $category = $request->input('category', 'all');

        $query = BagItem::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc');

        if ($category !== 'all' && in_array($category, array_keys(BagItem::categories()))) {
            $query->where('category', $category);
        }

        $items = $query->get()->map(function ($item) {
            return [
                'id'                  => $item->id,
                'name'                => $item->name,
                'category'            => $item->category,
                'category_name'       => $item->category_name,
                'code'                => $item->code,
                'price_coins'         => (int) $item->price_coins,
                'price_bdt'           => (float) $item->price_bdt,
                'formatted_price'     => $item->formatted_price,
                'duration_days'       => (int) $item->duration_days,
                'duration_text'       => $item->duration_text,
                'badge'               => $item->badge,
                'discount_percent'    => $item->discount_percent,
                'coupon_value'        => $item->coupon_value,
                'icon_url'            => $item->icon_full_url,
                'image_url'           => $item->image_full_url,
                'preview_url'         => $item->preview_full_url,
                'animation_url'       => $item->animation_full_url,
                'format'              => $item->format,
                'effect_type'         => $item->effect_type,
                'description'         => $item->description,
                'is_giftable'         => (bool) $item->is_giftable,
            ];
        });

        return response()->json([
            'status'  => true,
            'message' => 'My Bag store catalog retrieved successfully.',
            'data'    => [
                'categories' => BagItem::categories(),
                'total'      => $items->count(),
                'items'      => $items,
            ],
        ], 200);
    }

    /**
     * 3. Purchase an Item from My Bag Store.
     * POST /api/bag/purchase or POST /api/my-bag/buy
     */
    public function purchase(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized. Please login to purchase items.',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'bag_item_id' => 'required|exists:bag_items,id',
            'quantity'    => 'nullable|integer|min:1|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $item = BagItem::findOrFail($request->bag_item_id);
        if (!$item->is_active) {
            return response()->json([
                'status'  => false,
                'message' => 'This item is currently not available for purchase.',
            ], 404);
        }

        $qty = (int) ($request->input('quantity', 1));
        $totalCost = (int) ($item->price_coins * $qty);

        if ($totalCost > 0 && (int) $user->coins < $totalCost) {
            return response()->json([
                'status'              => false,
                'message'             => "Insufficient Gems balance! Required: {$totalCost} Gems, You have: {$user->coins} Gems.",
                'required_coins'      => $totalCost,
                'current_coins'       => (int) $user->coins,
                'redirect_to_deposit' => true,
            ], 200);
        }

        return DB::transaction(function () use ($user, $item, $qty, $totalCost) {
            // Deduct coins if cost > 0
            if ($totalCost > 0) {
                $user->decrement('coins', $totalCost);

                CoinTransaction::create([
                    'user_id'       => $user->id,
                    'amount'        => -$totalCost,
                    'type'          => 'bag_item_purchase',
                    'description'   => "Purchased {$qty}x {$item->name} from My Bag Store",
                    'balance_after' => $user->fresh()->coins,
                ]);
            }

            $now = Carbon::now();
            $expiresAt = $item->duration_days > 0 ? $now->copy()->addDays($item->duration_days) : null;

            // Check if user already has this item active (extend duration or add quantity)
            $existing = UserBagItem::where('user_id', $user->id)
                ->where('bag_item_id', $item->id)
                ->where('status', '!=', 'expired')
                ->first();

            if ($existing && $existing->expires_at && $expiresAt) {
                // Extend expiration
                $baseDate = $existing->expires_at->isFuture() ? $existing->expires_at : $now;
                $existing->expires_at = $baseDate->copy()->addDays($item->duration_days * $qty);
                $existing->quantity += $qty;
                $existing->save();
                $uItem = $existing;
            } else {
                $uItem = UserBagItem::create([
                    'user_id'       => $user->id,
                    'bag_item_id'   => $item->id,
                    'quantity'      => $qty,
                    'status'        => 'unused',
                    'is_equipped'   => false,
                    'acquired_from' => 'purchase',
                    'started_at'    => $now,
                    'expires_at'    => $expiresAt,
                ]);
            }

            return response()->json([
                'status'  => true,
                'message' => "Congratulations! {$qty}x {$item->name} has been added to your Bag!",
                'data'    => [
                    'user_bag_item_id'  => $uItem->id,
                    'item_name'         => $item->name,
                    'category'          => $item->category,
                    'new_coins_balance' => (int) $user->fresh()->coins,
                    'expires_at'        => $uItem->expires_at?->toIso8601String(),
                    'duration_text'     => $uItem->remaining_days_text,
                ],
            ], 200);
        });
    }

    /**
     * 4. Equip or Use an Item from Bag (Avatar Frame, Chat Style, Profile Card, Ride, Coupon).
     * POST /api/bag/use or POST /api/my-bag/equip
     */
    public function useItem(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'user_bag_item_id' => 'required|exists:user_bag_items,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $uItem = UserBagItem::with('bagItem')
            ->where('user_id', $user->id)
            ->where('id', $request->user_bag_item_id)
            ->first();

        if (!$uItem || !$uItem->is_valid) {
            return response()->json([
                'status'  => false,
                'message' => 'This bag item has expired or is invalid.',
            ], 400);
        }

        $item = $uItem->bagItem;

        // Handle Coupons
        if ($item->category === 'coupon') {
            return DB::transaction(function () use ($user, $uItem, $item) {
                $couponValue = (int) ($item->coupon_value ?: 500);

                // Credit bonus coins
                $user->increment('coins', $couponValue);

                CoinTransaction::create([
                    'user_id'       => $user->id,
                    'amount'        => $couponValue,
                    'type'          => 'coupon_redeem',
                    'description'   => "Redeemed {$item->name} (+{$couponValue} Gems)",
                    'balance_after' => $user->fresh()->coins,
                ]);

                $uItem->status = 'used';
                $uItem->used_at = Carbon::now();
                $uItem->save();

                return response()->json([
                    'status'  => true,
                    'message' => "Coupon redeemed successfully! +{$couponValue} Gems added to your wallet.",
                    'data'    => [
                        'credited_coins'    => $couponValue,
                        'new_coins_balance' => (int) $user->fresh()->coins,
                    ],
                ], 200);
            });
        }

        // Handle Visual Equipables (Avatar Frame, Chat Style, Profile Card, Entrance Bubble, Big Entrance)
        return DB::transaction(function () use ($user, $uItem, $item) {
            // Unequip any other item in the same category
            UserBagItem::where('user_id', $user->id)
                ->where('id', '!=', $uItem->id)
                ->whereHas('bagItem', fn($q) => $q->where('category', $item->category))
                ->update(['is_equipped' => false, 'status' => 'unused']);

            $uItem->is_equipped = true;
            $uItem->status = 'used';
            if (!$uItem->started_at) {
                $uItem->started_at = Carbon::now();
            }
            $uItem->save();

            return response()->json([
                'status'  => true,
                'message' => "{$item->name} is now active and equipped on your profile!",
                'data'    => [
                    'equipped_item_id' => $item->id,
                    'category'         => $item->category,
                    'name'             => $item->name,
                    'preview_url'      => $item->preview_full_url,
                    'expires_at'       => $uItem->expires_at?->toIso8601String(),
                ],
            ], 200);
        });
    }

    /**
     * 5. Unequip an active bag item.
     * POST /api/bag/unequip
     */
    public function unequip(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthorized.'], 401);
        }

        $userBagItemId = $request->input('user_bag_item_id');
        $category = $request->input('category');

        $query = UserBagItem::where('user_id', $user->id)->where('is_equipped', true);

        if ($userBagItemId) {
            $query->where('id', $userBagItemId);
        } elseif ($category) {
            $query->whereHas('bagItem', fn($q) => $q->where('category', $category));
        }

        $query->update(['is_equipped' => false, 'status' => 'unused']);

        return response()->json([
            'status'  => true,
            'message' => 'Item unequipped successfully.',
        ], 200);
    }

    /**
     * 6. Send / Gift a Bag Item to a Friend.
     * POST /api/bag/gift or POST /api/my-bag/send-gift
     */
    public function sendGift(Request $request): JsonResponse
    {
        $sender = $this->resolveUser($request);
        if (!$sender) {
            return response()->json(['status' => false, 'message' => 'Unauthorized.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'receiver_id'         => 'nullable',
            'receiver_account_id' => 'nullable',
            'account_id'          => 'nullable',
            'bag_item_id'         => 'required_without:user_bag_item_id|nullable|exists:bag_items,id',
            'user_bag_item_id'    => 'required_without:bag_item_id|nullable|exists:user_bag_items,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $receiverIdentifier = $request->input('receiver_account_id') 
            ?? $request->input('account_id') 
            ?? $request->input('receiver_id');

        if (!$receiverIdentifier) {
            return response()->json(['status' => false, 'message' => 'Receiver ID or Account ID is required.'], 422);
        }

        $receiver = User::where('account_id', (string) $receiverIdentifier)
            ->orWhere('id', is_numeric($receiverIdentifier) ? (int) $receiverIdentifier : 0)
            ->first();

        if (!$receiver) {
            return response()->json(['status' => false, 'message' => "Recipient user '{$receiverIdentifier}' not found."], 404);
        }

        if ($receiver->id === $sender->id) {
            return response()->json(['status' => false, 'message' => 'You cannot gift items to yourself.'], 400);
        }

        $item = null;

        // If transferring from sender's existing inventory
        if ($request->filled('user_bag_item_id')) {
            $uItem = UserBagItem::with('bagItem')
                ->where('user_id', $sender->id)
                ->where('id', $request->user_bag_item_id)
                ->first();

            if (!$uItem || !$uItem->is_valid) {
                return response()->json(['status' => false, 'message' => 'Item not found in your bag or expired.'], 404);
            }

            $item = $uItem->bagItem;

            return DB::transaction(function () use ($sender, $receiver, $uItem, $item) {
                // Deduct from sender or delete
                if ($uItem->quantity > 1) {
                    $uItem->decrement('quantity');
                } else {
                    $uItem->delete();
                }

                $now = Carbon::now();
                $expiresAt = $item->duration_days > 0 ? $now->copy()->addDays($item->duration_days) : null;

                // Grant to receiver
                UserBagItem::create([
                    'user_id'       => $receiver->id,
                    'bag_item_id'   => $item->id,
                    'quantity'      => 1,
                    'status'        => 'unused',
                    'is_equipped'   => false,
                    'acquired_from' => 'gift',
                    'sender_id'     => $sender->id,
                    'started_at'    => $now,
                    'expires_at'    => $expiresAt,
                ]);

                return response()->json([
                    'status'  => true,
                    'message' => "Successfully sent {$item->name} to {$receiver->display_name}!",
                ], 200);
            });
        }

        // Direct buy & gift
        if ($request->filled('bag_item_id')) {
            $item = BagItem::findOrFail($request->bag_item_id);
            $cost = (int) $item->price_coins;

            if ($cost > 0 && (int) $sender->coins < $cost) {
                return response()->json([
                    'status'  => false,
                    'message' => "Insufficient Gems balance to gift this item.",
                ], 400);
            }

            return DB::transaction(function () use ($sender, $receiver, $item, $cost) {
                if ($cost > 0) {
                    $sender->decrement('coins', $cost);
                    CoinTransaction::create([
                        'user_id'       => $sender->id,
                        'amount'        => -$cost,
                        'type'          => 'bag_item_gift_sent',
                        'description'   => "Gifted {$item->name} to {$receiver->display_name}",
                        'balance_after' => $sender->fresh()->coins,
                    ]);
                }

                $now = Carbon::now();
                $expiresAt = $item->duration_days > 0 ? $now->copy()->addDays($item->duration_days) : null;

                UserBagItem::create([
                    'user_id'       => $receiver->id,
                    'bag_item_id'   => $item->id,
                    'quantity'      => 1,
                    'status'        => 'unused',
                    'is_equipped'   => false,
                    'acquired_from' => 'gift',
                    'sender_id'     => $sender->id,
                    'started_at'    => $now,
                    'expires_at'    => $expiresAt,
                ]);

                return response()->json([
                    'status'  => true,
                    'message' => "Successfully gifted {$item->name} to {$receiver->display_name}!",
                    'data'    => [
                        'new_coins_balance' => (int) $sender->fresh()->coins,
                    ],
                ], 200);
            });
        }

        return response()->json(['status' => false, 'message' => 'Invalid request parameters.'], 400);
    }

    /**
     * 7. Search Recipient User by 8-Digit Account ID or Name for Gifting.
     * GET /api/bag/search-user?q={account_id_or_name}
     */
    public function searchRecipient(Request $request): JsonResponse
    {
        $query = trim($request->input('q', $request->input('query', $request->input('account_id', ''))));

        if (empty($query)) {
            return response()->json([
                'status'  => false,
                'message' => 'Please provide an Account ID or name to search.',
                'data'    => null,
            ], 422);
        }

        // Exact match by account_id or database id first
        $user = User::where('account_id', $query)
            ->orWhere('id', is_numeric($query) ? (int) $query : 0)
            ->first();

        if (!$user) {
            // Partial match on username, name, or phone
            $user = User::where('username', 'like', "%{$query}%")
                ->orWhere('name', 'like', "%{$query}%")
                ->orWhere('mobile_no', 'like', "%{$query}%")
                ->first();
        }

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => "User with Account ID or name '{$query}' not found.",
                'data'    => null,
            ], 404);
        }

        // Format user avatar properly using CoinPackage helper
        $avatarUrl = $user->profile_image ?? $user->image ?? $user->avatar;
        if (!empty($avatarUrl) && !str_starts_with($avatarUrl, 'http')) {
            $avatarUrl = \App\Models\CoinPackage::resolveAssetUrl(ltrim($avatarUrl, '/'));
        }
        if (empty($avatarUrl)) {
            $avatarUrl = \App\Models\CoinPackage::resolveAssetUrl('uploads/all_image/default_avatar.png');
        }

        return response()->json([
            'status'  => true,
            'message' => 'Recipient user found successfully.',
            'data'    => [
                'id'           => $user->id,
                'account_id'   => $user->account_id ?? (string) $user->id,
                'name'         => $user->name ?? $user->username ?? 'Chinchins User',
                'username'     => $user->username ?? $user->name,
                'display_name' => $user->display_name ?? $user->name ?? $user->username,
                'avatar'       => $avatarUrl,
                'avatar_url'   => $avatarUrl,
                'level'        => $user->level ?? 1,
                'country_flag' => $user->country_flag ?? '🇧🇩',
                'gender'       => $user->gender ?? 'male',
                'coins'        => (int) ($user->coins ?? 0),
            ],
        ], 200);
    }
}
