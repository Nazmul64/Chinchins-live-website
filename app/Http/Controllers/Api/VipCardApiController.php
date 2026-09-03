<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CoinTransaction;
use App\Models\User;
use App\Models\UserVipCardSubscription;
use App\Models\VipPrivilegeCard;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VipCardApiController extends Controller
{
    /**
     * Resolve authenticated user from Bearer Token or User ID fallback.
     */
    protected function resolveUser(Request $request): ?User
    {
        $user = $request->user();
        if (!$user) {
            $userId = $request->input('user_id') ?? $request->header('X-User-ID');
            if ($userId) {
                $user = User::find($userId);
            }
        }
        return $user;
    }

    /**
     * Format Extra Perks array with absolute image/icon URLs.
     */
    public static function formatExtraRewards(?array $rewards): array
    {
        if (empty($rewards)) return [];

        $formatted = [];
        foreach ($rewards as $reward) {
            $title = $reward['title'] ?? 'VIP Privilege';
            $tag = $reward['tag'] ?? 'Perk';
            $icon = $reward['icon'] ?? 'frame_avatar';
            $image = $reward['image'] ?? null;

            $imageUrl = null;
            if (!empty($image)) {
                $imageUrl = str_starts_with($image, 'http') ? $image : url($image);
            }

            $formatted[] = [
                'title'     => $title,
                'tag'       => $tag,
                'icon'      => $icon,
                'image'     => $image,
                'image_url' => $imageUrl,
            ];
        }

        return $formatted;
    }

    /**
     * Format Daily Schedule array.
     */
    public static function formatDailySchedule(?array $schedule): array
    {
        if (empty($schedule)) return [];

        $formatted = [];
        foreach ($schedule as $item) {
            $day = (int) ($item['day'] ?? 1);
            $coins = (int) ($item['coins'] ?? 0);
            $extra = $item['extra'] ?? null;
            $icon = $item['icon'] ?? null;
            $image = $item['image'] ?? null;

            $imageUrl = null;
            if (!empty($image)) {
                $imageUrl = str_starts_with($image, 'http') ? $image : url($image);
            }

            $formatted[] = [
                'day'       => $day,
                'day_label' => static::getDayLabel($day),
                'coins'     => $coins,
                'extra'     => $extra,
                'icon'      => $icon,
                'image_url' => $imageUrl,
            ];
        }

        usort($formatted, fn($a, $b) => $a['day'] <=> $b['day']);
        return $formatted;
    }

    /**
     * Helper to get day suffix (1st, 2nd, 3rd, 4th...).
     */
    protected static function getDayLabel(int $day): string
    {
        if ($day % 100 >= 11 && $day % 100 <= 13) {
            return $day . 'th';
        }
        return match ($day % 10) {
            1 => $day . 'st',
            2 => $day . 'nd',
            3 => $day . 'rd',
            default => $day . 'th',
        };
    }

    /**
     * Format seconds into Days : Hours : Minutes : Seconds (DD : HH : MM : SS)
     */
    public static function formatCountdown(int $seconds): string
    {
        if ($seconds <= 0) {
            return '00 : 00 : 00 : 00';
        }

        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        return sprintf('%02d : %02d : %02d : %02d', $days, $hours, $minutes, $secs);
    }

    /**
     * Get All Monthly & Weekly Privilege Card Packages with Schedule & Outfits.
     * GET /api/vip-cards (or GET /api/monthly-cards)
     */
    public function index(Request $request): JsonResponse
    {
        // Seed default 4 cards if empty
        VipPrivilegeCard::seedDefaultCards();

        $user = $this->resolveUser($request);

        $cards = VipPrivilegeCard::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        $userSubscriptions = [];
        if ($user) {
            $userSubscriptions = UserVipCardSubscription::where('user_id', $user->id)
                ->where('status', 'active')
                ->where('expires_at', '>', Carbon::now())
                ->get()
                ->keyBy('vip_card_id');
        }

        $now = Carbon::now();

        $formattedCards = $cards->map(function ($card) use ($userSubscriptions, $now) {
            $sub = $userSubscriptions[$card->id] ?? null;

            $isSubscribed = $sub !== null;
            $remainingSeconds = $isSubscribed ? max(0, $now->diffInSeconds($sub->expires_at, false)) : 0;
            $currentDay = $isSubscribed ? $sub->getCurrentDayNumber() : 1;
            $hasClaimedToday = $isSubscribed ? $sub->hasClaimedToday() : false;

            // Rolling promotional offer countdown timer for display (e.g. 7 days / duration timer)
            $offerDurationSeconds = ((int) $card->duration_days) * 86400 - 60; // e.g. 6 days 23 hrs 59 mins
            $displayCountdownSeconds = $isSubscribed ? $remainingSeconds : $offerDurationSeconds;
            $countdownFormatted = static::formatCountdown($displayCountdownSeconds);

            $formattedSchedule = static::formatDailySchedule($card->daily_schedule ?? []);
            $formattedRewards = static::formatExtraRewards($card->extra_rewards ?? []);

            return [
                'id'                        => $card->id,
                'card_type'                 => $card->card_type,
                'name'                      => $card->name,
                'badge_text'                => $card->badge_text,
                'price_bdt'                 => (float) $card->price_bdt,
                'formatted_price_bdt'       => 'BDT ' . number_format($card->price_bdt, 2),
                'price_coins'               => (int) $card->price_coins,
                'duration_days'             => (int) $card->duration_days,
                'instant_reward_coins'      => (int) $card->instant_reward_coins,
                'daily_checkin_total_coins' => (int) $card->daily_checkin_total_coins,
                'total_return_coins'        => (int) $card->total_return_coins,
                'card_color'                => $card->card_color ?? '#FF4081',
                'banner_tag'                => $card->banner_tag ?? 'Spend Less, Get More Gems!',
                'icon_url'                  => $card->icon_url,
                'icon_full_url'             => $card->icon_full_url,
                'animation_url'             => $card->animation_url,
                'animation_full_url'        => $card->animation_full_url,
                'bg_image_url'              => $card->bg_image_url,
                'bg_image_full_url'         => $card->bg_image_full_url,
                'format'                    => $card->format ?? 'lottie',
                'description'               => $card->description,
                'countdown_seconds'         => $displayCountdownSeconds,
                'countdown_timer'           => $countdownFormatted,
                'daily_schedule'            => $formattedSchedule,
                'extra_rewards'             => $formattedRewards,
                'user_subscription'         => [
                    'is_subscribed'     => $isSubscribed,
                    'subscription_id'   => $sub?->id,
                    'started_at'        => $sub?->started_at?->toIso8601String(),
                    'expires_at'        => $sub?->expires_at?->toIso8601String(),
                    'remaining_seconds' => $remainingSeconds,
                    'countdown_timer'   => $isSubscribed ? static::formatCountdown($remainingSeconds) : null,
                    'current_day'       => $currentDay,
                    'has_claimed_today' => $hasClaimedToday,
                    'claimed_days'      => $sub?->claimed_days ?? [],
                ],
            ];
        });

        return response()->json([
            'status'  => true,
            'message' => 'Monthly and weekly VIP privilege cards retrieved successfully.',
            'data'    => [
                'banner' => [
                    'title'       => 'Spend Less, Get More Gems!',
                    'subtitle'    => 'Update to New User Weekly Card',
                    'action_type' => 'OPEN_VIP_CARDS',
                ],
                'cards'  => $formattedCards,
            ],
        ], 200);
    }

    /**
     * Get Logged-in User's Active Card Subscriptions & Claim Status.
     * GET /api/vip-cards/my-subscriptions (or GET /api/monthly-cards/my)
     */
    public function mySubscriptions(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized. Please login to view your VIP cards.',
            ], 401);
        }

        $subscriptions = UserVipCardSubscription::with('card')
            ->where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->get();

        $now = Carbon::now();
        $activeSubscriptions = [];

        foreach ($subscriptions as $sub) {
            if (!$sub->card) continue;

            $isActive = $sub->is_active;
            $remainingSeconds = $isActive ? max(0, $now->diffInSeconds($sub->expires_at, false)) : 0;
            $currentDay = $sub->getCurrentDayNumber();
            $hasClaimedToday = $sub->hasClaimedToday();

            $activeSubscriptions[] = [
                'subscription_id'   => $sub->id,
                'card_id'           => $sub->vip_card_id,
                'card_name'         => $sub->card->name,
                'card_type'         => $sub->card_type,
                'card_color'        => $sub->card->card_color ?? '#FF4081',
                'is_active'         => $isActive,
                'started_at'        => $sub->started_at?->toIso8601String(),
                'expires_at'        => $sub->expires_at?->toIso8601String(),
                'remaining_seconds' => $remainingSeconds,
                'countdown_timer'   => static::formatCountdown($remainingSeconds),
                'current_day'       => $currentDay,
                'total_days'        => $sub->card->duration_days,
                'has_claimed_today' => $hasClaimedToday,
                'claimed_days'      => $sub->claimed_days ?? [],
                'daily_schedule'    => static::formatDailySchedule($sub->card->daily_schedule ?? []),
                'extra_rewards'     => static::formatExtraRewards($sub->card->extra_rewards ?? []),
            ];
        }

        return response()->json([
            'status'  => true,
            'message' => 'User card subscriptions retrieved successfully.',
            'data'    => [
                'user_coins'     => (int) $user->coins,
                'subscriptions'  => $activeSubscriptions,
            ],
        ], 200);
    }

    /**
     * Purchase a VIP / Monthly Card using In-App Balance or Deposit.
     * POST /api/vip-cards/purchase
     */
    public function purchase(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized. Please login to purchase a VIP card.',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'card_id'        => 'required_without:card_type|nullable|exists:vip_privilege_cards,id',
            'card_type'      => 'required_without:card_id|nullable|string',
            'payment_method' => 'nullable|string', // 'coins', 'wallet', 'bkash', 'nagad'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $card = null;
        if ($request->filled('card_id')) {
            $card = VipPrivilegeCard::find($request->card_id);
        } elseif ($request->filled('card_type')) {
            $card = VipPrivilegeCard::where('card_type', $request->card_type)->first();
        }

        if (!$card || !$card->is_active) {
            return response()->json([
                'status'  => false,
                'message' => 'Selected VIP Card package is not available.',
            ], 404);
        }

        // Check user balance
        $priceCoins = (int) $card->price_coins;
        if ((int) $user->coins < $priceCoins) {
            return response()->json([
                'status'              => false,
                'message'             => "Insufficient Gems/Coins balance! Required: {$priceCoins} coins, You have: {$user->coins} coins.",
                'required_coins'      => $priceCoins,
                'current_coins'       => (int) $user->coins,
                'redirect_to_deposit' => true,
            ], 200);
        }

        return DB::transaction(function () use ($user, $card, $priceCoins, $request) {
            // Deduct price from coins
            $user->decrement('coins', $priceCoins);

            // Credit Instant Reward Coins immediately!
            $instantReward = (int) $card->instant_reward_coins;
            if ($instantReward > 0) {
                $user->increment('coins', $instantReward);
            }

            // Record transaction
            CoinTransaction::create([
                'user_id'          => $user->id,
                'amount'           => -$priceCoins,
                'type'             => 'card_purchase',
                'description'      => "Purchased {$card->name} (Price: {$priceCoins} Gems, Instant Reward: +{$instantReward} Gems)",
                'balance_after'    => $user->fresh()->coins,
            ]);

            $now = Carbon::now();
            $expiresAt = $now->copy()->addDays($card->duration_days);

            // Create subscription
            $subscription = UserVipCardSubscription::create([
                'user_id'         => $user->id,
                'vip_card_id'     => $card->id,
                'card_type'       => $card->card_type,
                'price_paid'      => $card->price_bdt,
                'payment_method'  => $request->input('payment_method', 'coins'),
                'started_at'      => $now,
                'expires_at'      => $expiresAt,
                'claimed_days'    => [1], // Day 1 instant reward claimed upon purchase
                'last_claimed_at' => $now,
                'status'          => 'active',
            ]);

            return response()->json([
                'status'  => true,
                'message' => "Congratulations! {$card->name} activated successfully! Instant {$instantReward} Gems credited to your wallet.",
                'data'    => [
                    'subscription_id'      => $subscription->id,
                    'card_name'            => $card->name,
                    'instant_reward_coins' => $instantReward,
                    'new_coins_balance'    => (int) $user->fresh()->coins,
                    'expires_at'           => $expiresAt->toIso8601String(),
                    'duration_days'        => $card->duration_days,
                ],
            ], 200);
        });
    }

    /**
     * Claim Daily Scheduled Reward Coins / Gems.
     * POST /api/vip-cards/claim-daily
     */
    public function claimDaily(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized. Please login to claim daily reward.',
            ], 401);
        }

        $subscriptionId = $request->input('subscription_id');
        $cardId = $request->input('card_id');

        $subscription = UserVipCardSubscription::with('card')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->where('expires_at', '>', Carbon::now())
            ->when($subscriptionId, fn($q) => $q->where('id', $subscriptionId))
            ->when($cardId, fn($q) => $q->where('vip_card_id', $cardId))
            ->first();

        if (!$subscription || !$subscription->card) {
            return response()->json([
                'status'  => false,
                'message' => 'No active VIP Card subscription found.',
            ], 404);
        }

        $currentDay = $subscription->getCurrentDayNumber();
        $claimedDays = $subscription->claimed_days ?? [];

        if (in_array($currentDay, $claimedDays)) {
            return response()->json([
                'status'       => false,
                'message'      => "You have already claimed Day {$currentDay} reward today! Come back tomorrow for next day bonus.",
                'current_day'  => $currentDay,
                'claimed_days' => $claimedDays,
            ], 200);
        }

        // Find coins for today from daily_schedule
        $dailySchedule = $subscription->card->daily_schedule ?? [];
        $todayCoins = 500; // default fallback
        $extraReward = null;

        foreach ($dailySchedule as $item) {
            if ((int) ($item['day'] ?? 0) === $currentDay) {
                $todayCoins = (int) ($item['coins'] ?? 500);
                $extraReward = $item['extra'] ?? null;
                break;
            }
        }

        return DB::transaction(function () use ($user, $subscription, $currentDay, $claimedDays, $todayCoins, $extraReward) {
            // Credit today's reward
            $user->increment('coins', $todayCoins);

            // Append today to claimed_days
            $claimedDays[] = $currentDay;
            $subscription->claimed_days = array_values(array_unique($claimedDays));
            $subscription->last_claimed_at = Carbon::now();
            $subscription->save();

            // Record transaction
            CoinTransaction::create([
                'user_id'          => $user->id,
                'amount'           => $todayCoins,
                'type'             => 'card_daily_claim',
                'description'      => "Claimed {$subscription->card->name} Day {$currentDay} Bonus (+{$todayCoins} Gems)",
                'balance_after'    => $user->fresh()->coins,
            ]);

            return response()->json([
                'status'  => true,
                'message' => "Day {$currentDay} bonus claimed successfully! +{$todayCoins} Gems added to your wallet.",
                'data'    => [
                    'day'               => $currentDay,
                    'claimed_coins'     => $todayCoins,
                    'extra_reward'      => $extraReward,
                    'new_coins_balance' => (int) $user->fresh()->coins,
                    'claimed_days'      => $subscription->claimed_days,
                ],
            ], 200);
        });
    }

    /**
     * Admin: Create or Store New VIP Card Package.
     * POST /api/admin/vip-cards
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'card_type'                 => 'required|string',
            'name'                      => 'required|string',
            'price_bdt'                 => 'required|numeric',
            'price_coins'               => 'required|integer',
            'duration_days'             => 'required|integer',
            'instant_reward_coins'      => 'required|integer',
            'daily_checkin_total_coins' => 'required|integer',
            'total_return_coins'        => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $card = VipPrivilegeCard::create($request->all());

        return response()->json([
            'status'  => true,
            'message' => 'VIP Card created successfully.',
            'data'    => $card,
        ], 201);
    }

    /**
     * Admin: Update VIP Card Package.
     * POST /api/admin/vip-cards/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $card = VipPrivilegeCard::find($id);
        if (!$card) {
            return response()->json(['status' => false, 'message' => 'VIP Card not found.'], 404);
        }

        $card->update($request->all());

        return response()->json([
            'status'  => true,
            'message' => 'VIP Card updated successfully.',
            'data'    => $card,
        ], 200);
    }

    /**
     * Admin: Delete VIP Card.
     * DELETE /api/admin/vip-cards/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $card = VipPrivilegeCard::find($id);
        if (!$card) {
            return response()->json(['status' => false, 'message' => 'VIP Card not found.'], 404);
        }

        $card->delete();

        return response()->json([
            'status'  => true,
            'message' => 'VIP Card deleted successfully.',
        ], 200);
    }
}
