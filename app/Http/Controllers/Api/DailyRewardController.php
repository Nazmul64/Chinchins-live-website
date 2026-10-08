<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CoinTransaction;
use App\Models\DailyReward;
use App\Models\User;
use App\Models\UserDailyClaim;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DailyRewardController extends Controller
{
    /**
     * Resolve authenticated user from Bearer Token (Sanctum), Header, or Request.
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
     * Ensure default 7 daily rewards exist.
     */
    protected function ensureDefaultRewards()
    {
        if (DailyReward::count() === 0) {
            for ($i = 1; $i <= 7; $i++) {
                DailyReward::create([
                    'day_number'   => $i,
                    'reward_coins' => ($i === 7 ? 100 : 50),
                    'is_active'    => true,
                    'icon_image'   => null,
                ]);
            }
        }
    }

    /**
     * Get Daily Check-in Status and popup trigger state.
     * GET /api/daily-rewards/status
     */
    public function getStatus(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized. Please login to check daily rewards.',
            ], 401);
        }

        $this->ensureDefaultRewards();
        $rewards = DailyReward::orderBy('day_number')->get();

        $lastClaim = UserDailyClaim::where('user_id', $user->id)
            ->latest('claimed_at')
            ->first();

        $canClaim = true;
        $nextClaimTime = null;
        $currentStreak = 0;

        if ($lastClaim) {
            $lastClaimTime = Carbon::parse($lastClaim->claimed_at);
            $cooldownExpiry = $lastClaimTime->copy()->addHours(12);

            if (now()->lessThan($cooldownExpiry)) {
                $canClaim = false;
                $nextClaimTime = $cooldownExpiry->toIso8601String();
            }

            // If gap is more than 36 hours, reset streak to 0 (Day 1 next)
            if (now()->diffInHours($lastClaimTime) > 36) {
                $currentStreak = 0;
            } else {
                $currentStreak = $lastClaim->day_claimed % 7;
            }
        }

        $nextDayNumber = ($currentStreak % 7) + 1;
        $tomorrowDayNumber = ($nextDayNumber % 7) + 1;
        $tomorrowReward = $rewards->firstWhere('day_number', $tomorrowDayNumber);
        $tomorrowCoins = $tomorrowReward ? $tomorrowReward->reward_coins : 50;

        $daysData = $rewards->map(function ($reward) use ($currentStreak, $canClaim) {
            $isClaimed = $reward->day_number <= $currentStreak;
            $isCurrent = $reward->day_number === ($currentStreak + 1);

            return [
                'day_number'   => $reward->day_number,
                'reward_coins' => $reward->reward_coins,
                'icon_image'   => $reward->icon_image_url,
                'is_claimed'   => $isClaimed,
                'is_current'   => $isCurrent && $canClaim,
                'is_locked'    => $reward->day_number > ($currentStreak + 1) || (!$canClaim && $isCurrent),
            ];
        });

        return response()->json([
            'status'             => true,
            'should_open_popup'  => $canClaim, // When true, client automatically shows popup
            'can_claim'          => $canClaim,
            'current_streak'     => $currentStreak,
            'next_day_number'    => $nextDayNumber,
            'next_claim_at'      => $nextClaimTime,
            'tomorrow_reward'    => [
                'coins' => $tomorrowCoins,
                'text'  => "Tomorrow for {$tomorrowCoins} Reward!",
            ],
            'user_current_coins' => (int) ($user->coins ?? 0),
            'days'               => $daysData,
        ]);
    }

    /**
     * Claim Daily Reward & credit coins into main balance.
     * POST /api/daily-rewards/claim
     */
    public function claimReward(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized. Please login to claim daily reward.',
            ], 401);
        }

        $this->ensureDefaultRewards();

        return DB::transaction(function () use ($user) {
            $lastClaim = UserDailyClaim::where('user_id', $user->id)
                ->latest('claimed_at')
                ->lockForUpdate()
                ->first();

            if ($lastClaim) {
                $cooldownExpiry = Carbon::parse($lastClaim->claimed_at)->addHours(12);
                if (now()->lessThan($cooldownExpiry)) {
                    return response()->json([
                        'status'            => false,
                        'message'           => 'Please wait 12 hours before claiming the next reward.',
                        'next_available_at' => $cooldownExpiry->toIso8601String(),
                    ], 422);
                }

                $nextDay = (now()->diffInHours($lastClaim->claimed_at) > 36) 
                    ? 1 
                    : (($lastClaim->day_claimed % 7) + 1);
            } else {
                $nextDay = 1;
            }

            $rewardConfig = DailyReward::where('day_number', $nextDay)->first();
            $coinsToAdd = $rewardConfig ? (int) $rewardConfig->reward_coins : ($nextDay === 7 ? 100 : 50);

            // Create claim history record
            $claim = UserDailyClaim::create([
                'user_id'       => $user->id,
                'day_claimed'   => $nextDay,
                'coins_awarded' => $coinsToAdd,
                'claimed_at'    => now(),
            ]);

            // Add coins to user wallet
            $user->increment('coins', $coinsToAdd);

            // Record transaction
            CoinTransaction::create([
                'user_id'       => $user->id,
                'amount'        => $coinsToAdd,
                'type'          => 'daily_claim',
                'description'   => "Day {$nextDay} Daily Check-in Claim (+{$coinsToAdd} Coins)",
                'balance_after' => $user->fresh()->coins,
            ]);

            return response()->json([
                'status'        => true,
                'message'       => "Claimed successfully! +{$coinsToAdd} coins added.",
                'claimed_day'   => $nextDay,
                'coins_awarded' => $coinsToAdd,
                'total_balance' => (int) $user->fresh()->coins,
                'next_claim_at' => now()->addHours(12)->toIso8601String(),
            ]);
        });
    }
}
