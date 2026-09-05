<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CoinTransaction;
use App\Models\SpendLessCard;
use App\Models\User;
use App\Models\UserSpendLessSubscription;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SpendLessCardApiController extends Controller
{
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

    public static function formatExtraRewards(?array $rewards): array
    {
        if (empty($rewards)) return [];

        $formatted = [];
        foreach ($rewards as $reward) {
            $title = $reward['title'] ?? 'Card Perk';
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

    public static function formatDailySchedule(?array $schedule): array
    {
        if (empty($schedule)) return [];

        $formatted = [];
        foreach ($schedule as $item) {
            $day = (int) ($item['day'] ?? 1);
            $coins = (int) ($item['coins'] ?? 0);
            $extra = $item['extra'] ?? null;

            $formatted[] = [
                'day'       => $day,
                'day_label' => static::getDayLabel($day),
                'coins'     => $coins,
                'extra'     => $extra,
            ];
        }

        usort($formatted, fn($a, $b) => $a['day'] <=> $b['day']);
        return $formatted;
    }

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
     * Get All Spend Less, Get More Gems (Monthly & Weekly Cards).
     * GET /api/spend-less-get-more
     */
    public function index(Request $request): JsonResponse
    {
        SpendLessCard::seedDefaultCards();

        $user = $this->resolveUser($request);

        $cards = SpendLessCard::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        $userSubscriptions = [];
        if ($user) {
            $userSubscriptions = UserSpendLessSubscription::where('user_id', $user->id)
                ->where('status', 'active')
                ->where('expires_at', '>', Carbon::now())
                ->get()
                ->keyBy('spend_less_card_id');
        }

        $now = Carbon::now();

        $formattedCards = $cards->map(function ($card) use ($userSubscriptions, $now) {
            $sub = $userSubscriptions[$card->id] ?? null;

            $isSubscribed = $sub !== null;
            $remainingSeconds = $isSubscribed ? max(0, $now->diffInSeconds($sub->expires_at, false)) : 0;
            $currentDay = $isSubscribed ? $sub->getCurrentDayNumber() : 1;
            $hasClaimedToday = $isSubscribed ? $sub->hasClaimedToday() : false;

            $offerDurationSeconds = ((int) $card->duration_days) * 86400 - 60;
            $displayCountdownSeconds = $isSubscribed ? $remainingSeconds : $offerDurationSeconds;
            $countdownFormatted = static::formatCountdown($displayCountdownSeconds);

            $formattedSchedule = static::formatDailySchedule($card->daily_schedule ?? []);
            $formattedRewards = static::formatExtraRewards($card->extra_rewards ?? []);

            $instantCoins = (int) $card->instant_reward_coins;
            $dailyCoins = (int) $card->daily_checkin_total_coins;
            $instantText = $card->instant_reward_text ?: ('Gems in total ' . number_format($instantCoins));
            $dailyText = $card->daily_checkin_text ?: ('Gems in total ' . number_format($dailyCoins));

            return [
                'id'                            => $card->id,
                'card_type'                     => $card->card_type,
                'name'                          => $card->name,
                'category_name'                 => $card->category_name ?? $card->name,
                'badge_text'                    => $card->badge_text,
                'price_bdt'                     => (float) $card->price_bdt,
                'original_price_bdt'            => $card->original_price_bdt ? (float) $card->original_price_bdt : null,
                'formatted_price_bdt'           => $card->formatted_price_bdt,
                'formatted_original_price_bdt'  => $card->formatted_original_price_bdt,
                'discount_percent'              => $card->discount_percent,
                'price_coins'                   => (int) $card->price_coins,
                'duration_days'                 => (int) $card->duration_days,
                'instant_reward_coins'          => $instantCoins,
                'instant_reward_text'           => $instantText,
                'daily_checkin_total_coins'     => $dailyCoins,
                'daily_checkin_text'            => $dailyText,
                'total_return_coins'            => (int) $card->total_return_coins,
                'card_color'                    => $card->card_color ?? '#EC4899',
                'banner_tag'                    => $card->banner_tag ?? 'Spend Less, Get More Gems!',
                'icon_url'                      => $card->icon_url,
                'icon_full_url'                 => $card->icon_full_url,
                'animation_url'                 => $card->animation_url,
                'animation_full_url'            => $card->animation_full_url,
                'bg_image_url'                  => $card->bg_image_url,
                'bg_image_full_url'             => $card->bg_image_full_url,
                'format'                        => $card->format ?? 'image',
                'description'                   => $card->description,
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

        return response()->json([
            'status'  => true,
            'message' => 'Spend Less, Get More cards and rewards retrieved successfully.',
            'data'    => [
                'banner' => [
                    'title'       => 'Spend Less, Get More Gems!',
                    'subtitle'    => 'Update to Monthly Card',
                    'action_type' => 'OPEN_MONTHLY_CARDS',
                ],
                'cards' => $formattedCards,
            ],
        ], 200);
    }

    /**
     * Get Logged-in User's Active Card Subscriptions & Claim Status.
     * GET /api/spend-less-get-more/my
     */
    public function mySubscriptions(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized. Please login to view your active cards.',
            ], 401);
        }

        $subscriptions = UserSpendLessSubscription::with('card')
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
                'card_id'           => $sub->spend_less_card_id,
                'card_name'         => $sub->card->name,
                'card_type'         => $sub->card_type,
                'card_color'        => $sub->card->card_color ?? '#EC4899',
                'is_active'         => $isActive,
                'started_at'        => $sub->started_at?->toIso8601String(),
                'expires_at'        => $sub->expires_at?->toIso8601String(),
                'remaining_seconds' => $remainingSeconds,
                'countdown_timer'   => $isActive ? static::formatCountdown($remainingSeconds) : '00 : 00 : 00 : 00',
                'current_day'       => $currentDay,
                'claimed_days_count'=> (int) $sub->claimed_days_count,
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
                'user_id'       => $user->id,
                'account_id'    => $user->account_id,
                'coin_balance'  => (int) $user->coin_balance,
                'subscriptions' => $activeSubscriptions,
            ],
        ], 200);
    }

    /**
     * Purchase a Card.
     * POST /api/spend-less-get-more/purchase
     */
    public function purchase(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized. Please login to purchase a card.',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'card_id'   => 'nullable|exists:spend_less_cards,id',
            'card_type' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $card = null;
        if ($request->filled('card_id')) {
            $card = SpendLessCard::where('id', $request->input('card_id'))->where('is_active', true)->first();
        } elseif ($request->filled('card_type')) {
            $card = SpendLessCard::where('card_type', $request->input('card_type'))->where('is_active', true)->first();
        }

        if (!$card) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid or inactive card package selected.',
            ], 404);
        }

        $priceCoins = (int) $card->price_coins;
        $costGems = $priceCoins > 0 ? $priceCoins : (int) $card->instant_reward_coins;

        if ((int) $user->coin_balance < $costGems) {
            return response()->json([
                'status'  => false,
                'message' => 'Insufficient coin balance to purchase this card. Please recharge your wallet.',
                'data'    => [
                    'required_coins' => $costGems,
                    'current_balance'=> (int) $user->coin_balance,
                    'shortage'       => $costGems - (int) $user->coin_balance,
                ],
            ], 400);
        }

        $subscription = DB::transaction(function () use ($user, $card, $costGems) {
            // Deduct purchase price
            $user->decrement('coin_balance', $costGems);

            // Credit Instant Reward Gems
            $instantGems = (int) $card->instant_reward_coins;
            if ($instantGems > 0) {
                $user->increment('coin_balance', $instantGems);
            }

            // Create Transaction Logs
            CoinTransaction::create([
                'user_id'     => $user->id,
                'amount'      => -$costGems,
                'type'        => 'card_purchase',
                'description' => "Purchased card: {$card->name}",
            ]);

            if ($instantGems > 0) {
                CoinTransaction::create([
                    'user_id'     => $user->id,
                    'amount'      => $instantGems,
                    'type'        => 'card_instant_reward',
                    'description' => "Instant gems reward from {$card->name}",
                ]);
            }

            $existingSub = UserSpendLessSubscription::where('user_id', $user->id)
                ->where('spend_less_card_id', $card->id)
                ->where('status', 'active')
                ->where('expires_at', '>', Carbon::now())
                ->first();

            $startedAt = Carbon::now();
            $expiresAt = $existingSub ? $existingSub->expires_at->addDays($card->duration_days) : Carbon::now()->addDays($card->duration_days);

            if ($existingSub) {
                $existingSub->update([
                    'expires_at' => $expiresAt,
                    'status'     => 'active',
                ]);
                return $existingSub;
            }

            return UserSpendLessSubscription::create([
                'user_id'            => $user->id,
                'spend_less_card_id' => $card->id,
                'card_type'          => $card->card_type,
                'status'             => 'active',
                'started_at'         => $startedAt,
                'expires_at'         => $expiresAt,
                'claimed_days_count' => 0,
                'claimed_days'       => [],
            ]);
        });

        $user->refresh();

        return response()->json([
            'status'  => true,
            'message' => "Congratulations! You purchased {$card->name} successfully. Instant " . number_format($card->instant_reward_coins) . " Gems credited to your balance.",
            'data'    => [
                'subscription_id'       => $subscription->id,
                'card_name'             => $card->name,
                'card_type'             => $card->card_type,
                'duration_days'         => (int) $card->duration_days,
                'instant_gems_credited' => (int) $card->instant_reward_coins,
                'new_balance'           => (int) $user->coin_balance,
                'expires_at'            => $subscription->expires_at?->toIso8601String(),
                'extra_rewards'         => static::formatExtraRewards($card->extra_rewards ?? []),
            ],
        ], 200);
    }

    /**
     * Claim Today's Daily Check-in Bonus.
     * POST /api/spend-less-get-more/claim
     */
    public function claimDaily(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized. Please login to claim your daily bonus.',
            ], 401);
        }

        $subscriptionId = $request->input('subscription_id');
        $cardType = $request->input('card_type');

        $query = UserSpendLessSubscription::with('card')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->where('expires_at', '>', Carbon::now());

        if ($subscriptionId) {
            $query->where('id', $subscriptionId);
        } elseif ($cardType) {
            $query->where('card_type', $cardType);
        }

        $subscription = $query->first();

        if (!$subscription || !$subscription->card) {
            return response()->json([
                'status'  => false,
                'message' => 'No active card subscription found to claim daily rewards.',
            ], 404);
        }

        if ($subscription->hasClaimedToday()) {
            return response()->json([
                'status'  => false,
                'message' => 'You have already claimed today\'s bonus. Please return tomorrow!',
            ], 400);
        }

        $currentDay = $subscription->getCurrentDayNumber();
        $schedule = $subscription->card->daily_schedule ?? [];

        $dayRewardCoins = 0;
        $dayExtraReward = null;

        foreach ($schedule as $item) {
            if ((int) ($item['day'] ?? 0) === $currentDay) {
                $dayRewardCoins = (int) ($item['coins'] ?? 0);
                $dayExtraReward = $item['extra'] ?? null;
                break;
            }
        }

        if ($dayRewardCoins <= 0) {
            $totalDailyCoins = (int) $subscription->card->daily_checkin_total_coins;
            $duration = (int) $subscription->card->duration_days ?: 1;
            $dayRewardCoins = (int) round($totalDailyCoins / $duration);
        }

        $claimedDays = $subscription->claimed_days ?? [];
        if (!in_array($currentDay, $claimedDays)) {
            $claimedDays[] = $currentDay;
        }

        DB::transaction(function () use ($user, $subscription, $dayRewardCoins, $currentDay, $claimedDays) {
            if ($dayRewardCoins > 0) {
                $user->increment('coin_balance', $dayRewardCoins);

                CoinTransaction::create([
                    'user_id'     => $user->id,
                    'amount'      => $dayRewardCoins,
                    'type'        => 'card_daily_checkin',
                    'description' => "Daily check-in reward Day #{$currentDay} from {$subscription->card->name}",
                ]);
            }

            $subscription->update([
                'last_claimed_at'    => Carbon::now(),
                'claimed_days_count' => count($claimedDays),
                'claimed_days'       => $claimedDays,
            ]);
        });

        $user->refresh();

        return response()->json([
            'status'  => true,
            'message' => "Day {$currentDay} daily check-in reward of " . number_format($dayRewardCoins) . " Gems claimed successfully!",
            'data'    => [
                'day_number'    => $currentDay,
                'coins_claimed' => $dayRewardCoins,
                'extra_reward'  => $dayExtraReward,
                'new_balance'   => (int) $user->coin_balance,
                'claimed_days'  => $claimedDays,
            ],
        ], 200);
    }
}
