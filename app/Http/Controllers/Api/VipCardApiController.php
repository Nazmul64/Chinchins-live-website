<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\CoinPackage;
use App\Models\CoinTransaction;
use App\Models\User;
use App\Models\UserVipCardSubscription;
use App\Models\VipCard;
use App\Models\VipPrivilegeCard;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VipCardApiController extends Controller
{
    /**
     * Resolve authenticated user from Bearer Token (Sanctum), Session, or custom identifier.
     */
    protected function resolveUser(Request $request): ?User
    {
        // 1. Check Authorization Bearer token from header / input first
        $token = $request->bearerToken() 
              ?: $request->header('Authorization') 
              ?: $request->input('token') 
              ?: $request->input('auth_token');

        if ($token) {
            $tokenClean = trim(preg_replace('/^Bearer\s+/i', '', $token));
            if (class_exists('\Laravel\Sanctum\PersonalAccessToken')) {
                try {
                    $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($tokenClean);
                    if ($accessToken && $accessToken->tokenable) {
                        return $accessToken->tokenable;
                    }
                } catch (\Throwable $e) {}
            }
        }

        // 2. Try Sanctum Bearer token guard & default user guard
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

        // 3. Check custom user identifier headers
        $headerUserId = $request->header('X-User-Id') 
                     ?? $request->header('User-Id') 
                     ?? $request->header('user-id') 
                     ?? $request->header('userId')
                     ?? $request->header('X-Account-Id')
                     ?? $request->header('Account-Id');

        if ($headerUserId) {
            $u = User::find($headerUserId) ?? User::where('account_id', $headerUserId)->first();
            if ($u) return $u;
        }

        // 4. Fallback: user_id, userId, id, account_id in request body / query
        $idParam = $request->input('user_id') ?? $request->input('userId') ?? $request->input('id') ?? $request->input('account_id');
        if ($idParam) {
            return User::find($idParam) ?? User::where('account_id', $idParam)->first();
        }

        return null;
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
        $user = $this->resolveUser($request);

        // Fetch cards from vip_privilege_cards table
        $cards = VipPrivilegeCard::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        if ($cards->isEmpty()) {
            $cards = DB::table('vip_privilege_cards')->where('is_active', 1)->orderBy('sort_order', 'asc')->get();
        }

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

            $durationDays = (int) ($card->duration_days ?: 30);
            $offerDurationSeconds = $durationDays * 86400 - 60;
            $displayCountdownSeconds = $isSubscribed ? $remainingSeconds : $offerDurationSeconds;
            $countdownFormatted = static::formatCountdown($displayCountdownSeconds);

            $dailySchedule = is_string($card->daily_schedule) ? json_decode($card->daily_schedule, true) : ($card->daily_schedule ?? []);
            $extraRewards = is_string($card->extra_rewards) ? json_decode($card->extra_rewards, true) : ($card->extra_rewards ?? []);

            $formattedSchedule = static::formatDailySchedule($dailySchedule);
            $formattedRewards = static::formatExtraRewards($extraRewards);

            $instantCoins = (int) ($card->diamonds_reward ?? $card->instant_reward_coins ?? 32940);
            $dailyCoins = (int) ($card->daily_checkin_diamonds ?? $card->daily_checkin_total_coins ?? 26330);
            $costCoins = (int) ($card->cost_diamonds ?? $card->price_coins ?? (int) ($card->price ?? $card->price_bdt ?? 300));
            $totalCoins = (int) ($card->total_return_coins ?: ($instantCoins + $dailyCoins));

            $price = (float) ($card->price ?? $card->price_bdt ?? $costCoins);
            $name = $card->name ?? 'Super Monthly VIP Card';
            $perks = $card->perks ?? (!empty($formattedRewards) ? count($formattedRewards) . ' Perks' : '3 Perks');
            $outfits = $card->outfits ?? 'VIP Outfits';

            $instantText = $card->instant_reward_text ?: ('Gems in total ' . number_format($instantCoins));
            $dailyText = $card->daily_checkin_text ?: ('Gems in total ' . number_format($dailyCoins));

            $iconFullUrl = $card->icon_full_url ?? (empty($card->icon_url) ? asset('assets/images/vip/vip_card_badge.png') : (str_starts_with($card->icon_url, 'http') ? $card->icon_url : asset(ltrim($card->icon_url, '/'))));
            $animFullUrl = $card->animation_full_url ?? (empty($card->animation_url) ? null : (str_starts_with($card->animation_url, 'http') ? $card->animation_url : asset(ltrim($card->animation_url, '/'))));
            $bgFullUrl = $card->bg_image_full_url ?? (empty($card->bg_image_url) ? null : (str_starts_with($card->bg_image_url, 'http') ? $card->bg_image_url : asset(ltrim($card->bg_image_url, '/'))));

            return [
                'id'                            => $card->id,
                'name'                          => $name,
                'price'                         => $price,
                'diamonds_reward'               => $instantCoins,
                'cost_diamonds'                 => $costCoins,
                'daily_checkin_diamonds'        => $dailyCoins,
                'perks'                         => $perks,
                'outfits'                       => $outfits,
                'card_type'                     => $card->card_type ?? 'monthly_card',
                'category_name'                 => $card->category_name ?? $name,
                'badge_text'                    => $card->badge_text ?? 'VIP PRIVILEGE',
                'price_bdt'                     => (float) ($card->price_bdt ?? $price),
                'original_price_bdt'            => $card->original_price_bdt ? (float) $card->original_price_bdt : null,
                'formatted_price_bdt'           => $card->formatted_price_bdt ?? ('৳ ' . number_format($price, 0)),
                'formatted_original_price_bdt'  => $card->formatted_original_price_bdt ?? null,
                'discount_percent'              => $card->discount_percent ?? null,
                'price_coins'                   => $costCoins,
                'duration_days'                 => $durationDays,
                'instant_reward_coins'          => $instantCoins,
                'instant_reward_text'           => $instantText,
                'daily_checkin_total_coins'     => $dailyCoins,
                'daily_checkin_text'            => $dailyText,
                'total_return_coins'            => $totalCoins,
                'card_color'                    => $card->card_color ?? '#FF4081',
                'banner_tag'                    => $card->banner_tag ?? 'Spend Less, Get More Gems!',
                'icon_url'                      => $card->icon_url,
                'icon_full_url'                 => $iconFullUrl,
                'animation_url'                 => $card->animation_url,
                'animation_full_url'            => $animFullUrl,
                'bg_image_url'                  => $card->bg_image_url,
                'bg_image_full_url'             => $bgFullUrl,
                'format'                        => $card->format ?? 'lottie',
                'description'                   => $card->description ?? 'VIP Privilege Card Subscription',
                'countdown_seconds'             => $displayCountdownSeconds,
                'countdown_timer'               => $countdownFormatted,
                'daily_schedule'                => $formattedSchedule,
                'extra_rewards'                 => $formattedRewards,
                'user_subscription'             => [
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

        $appConfig = \App\Models\AppSetting::getAppConfig();

        // Support direct list format if requested
        if ($request->query('mode') === 'flat' || $request->query('format') === 'list') {
            return response()->json([
                'status' => true,
                'data'   => $formattedCards,
            ]);
        }

        $responsePayload = [
            'status'  => true,
            'message' => 'Premium VIP cards and privileges retrieved successfully.',
            'data'    => [
                'banner' => [
                    'title'       => 'Spend Less, Get More Gems!',
                    'subtitle'    => 'Update to New User Weekly Card',
                    'action_type' => 'OPEN_PREMIUM_VIP',
                ],
                'floating_banner' => $appConfig['floating_vip_banner'] ?? null,
                'cards'           => $formattedCards,
            ],
            'cards'   => $formattedCards,
        ];

        $etag = '"' . md5(json_encode($responsePayload)) . '"';
        if ($request->header('If-None-Match') === $etag) {
            return response()->json(null, 304)->withHeaders([
                'ETag'          => $etag,
                'Cache-Control' => 'public, max-age=600, stale-while-revalidate=3600',
            ]);
        }

        return response()->json($responsePayload, 200)->withHeaders([
            'ETag'          => $etag,
            'Cache-Control' => 'public, max-age=600, stale-while-revalidate=3600',
        ]);
    }

    /**
     * Get Floating Home Screen VIP Widget / Action Icon settings.
     * GET /api/floating-banner, GET /api/floating-action-icon, GET /api/floating-widget, GET /api/vip-cards/banner
     */
    public function getFloatingBanner(Request $request = null): JsonResponse
    {
        $all = AppSetting::getAllCached();
        $isEnabled = (bool) filter_var($all['floating_vip_banner_enabled'] ?? '1', FILTER_VALIDATE_BOOLEAN);
        $title = $all['floating_vip_banner_title'] ?? 'Extra Gems';
        $subtitle = $all['floating_vip_banner_tag'] ?? 'Monthly Card';
        $targetAction = $all['floating_vip_banner_action'] ?? 'OPEN_PREMIUM_VIP';
        $imagePath = $all['floating_vip_banner_image'] ?? 'assets/images/vip/vip_privilege_full_motion.svg';

        $imageUrl = null;
        if (!empty($imagePath)) {
            $imageUrl = (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://'))
                ? $imagePath
                : asset(ltrim($imagePath, '/'));
        } else {
            $imageUrl = asset('assets/images/vip/vip_privilege_full_motion.svg');
        }

        $payload = [
            'is_enabled'    => $isEnabled,
            'title'         => $title,
            'subtitle'      => $subtitle,
            'tag'           => $subtitle,
            'image_url'     => $imageUrl,
            'target_action' => $targetAction,
            'action_type'   => $targetAction,
            'target_screen' => '/premium-vip',
        ];

        return response()->json([
            'success' => true,
            'status'  => true,
            'data'    => $payload,
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

        $cardId = $request->input('card_id') ?? $request->input('id');
        $cardType = $request->input('card_type');

        $card = null;
        if ($cardId) {
            $card = VipPrivilegeCard::find($cardId) ?? DB::table('vip_privilege_cards')->where('id', $cardId)->first();
        } elseif ($cardType) {
            $card = VipPrivilegeCard::where('card_type', $cardType)->first() ?? DB::table('vip_privilege_cards')->where('card_type', $cardType)->first();
        }

        if (!$card) {
            return response()->json([
                'status'  => false,
                'message' => 'Selected VIP Card package is not available.',
            ], 404);
        }

        // Check user balance
        $priceCoins = (int) ($card->cost_diamonds ?? $card->price_coins ?? $card->price ?? $card->price_bdt ?? 300);
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
            $instantReward = (int) ($card->diamonds_reward ?? $card->instant_reward_coins ?? $card->total_return_coins ?? 0);
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

            $durationDays = (int) ($card->duration_days ?: 30);
            $now = Carbon::now();
            $expiresAt = $now->copy()->addDays($durationDays);

            // Create subscription
            $subscription = UserVipCardSubscription::create([
                'user_id'         => $user->id,
                'vip_card_id'     => $card->id,
                'card_type'       => $card->card_type ?? 'monthly_card',
                'price_paid'      => $card->price_bdt ?? $priceCoins,
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
                    'duration_days'        => $durationDays,
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
        $cardId = $request->input('card_id') ?? $request->input('id');

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
        $dailySchedule = is_string($subscription->card->daily_schedule) ? json_decode($subscription->card->daily_schedule, true) : ($subscription->card->daily_schedule ?? []);
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
