<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CallSession;
use App\Models\CallSetting;
use App\Models\CoinPackage;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MatchApiController extends Controller
{
    /**
     * Resolve the target user from Sanctum token, header, or fallback parameters.
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
                     ?? $request->header('userId')
                     ?? $request->header('X-Account-Id')
                     ?? $request->header('Account-Id');

        if ($headerUserId) {
            $u = User::find($headerUserId) ?? User::where('account_id', $headerUserId)->first();
            if ($u) return $u;
        }

        $idParam = $request->input('user_id') ?? $request->input('userId') ?? $request->input('id') ?? $request->input('caller_id');
        if ($idParam) {
            $u = User::find($idParam) ?? User::where('account_id', $idParam)->first();
            if ($u) return $u;
        }

        return null;
    }

    /**
     * Get Match Tab Dashboard Data: Available Free Hosts Count & Photo Grid.
     * GET /api/match or GET /api/match/status
     */
    public function getMatchTab(Request $request): JsonResponse
    {
        $caller = $this->resolveUser($request);
        $config = CallSetting::getAllConfig();

        $callerId = $caller ? $caller->id : 0;

        // Query active, non-locked female hosts (or all active hosts) who are not busy
        $hostsQuery = User::where('id', '!=', $callerId)
            ->where('is_active', true)
            ->where('is_locked', false)
            ->where('is_busy', false);

        if ($request->filled('gender')) {
            $hostsQuery->where('gender', $request->gender);
        }

        // Live count of actual active online hosts in database
        $actualActiveCount = (clone $hostsQuery)->count();
        
        // Base virtual pool number (e.g. 5000+ base multiplier as seen in live matching apps)
        $displayWaitingCount = max(5000 + ($actualActiveCount * 37), $actualActiveCount);

        // Fetch host list with photos for grid display
        $limit = (int) $request->input('limit', 20);
        $hosts = $hostsQuery->inRandomOrder()->take($limit)->get()->map(function ($host) use ($config) {
            return [
                'id' => $host->id,
                'account_id' => $host->account_id,
                'display_name' => $host->display_name,
                'avatar_url' => $host->avatar_url,
                'cover_photo_url' => $host->cover_photo_url,
                'gallery_images' => $host->gallery_image_urls,
                'gender' => $host->gender ?: 'female',
                'age' => $host->age ?: 22,
                'level' => $host->level ?: 4,
                'country' => $host->country ?: 'Bangladesh',
                'city' => $host->city ?: 'Dhaka',
                'is_active' => (bool) $host->is_active,
                'is_busy' => (bool) $host->is_busy,
                'is_online' => (bool) $host->is_online,
                'video_call_rate' => (int) ($host->video_call_rate ?: $config['video_call_rate_per_minute']),
                'introduction' => $host->introduction ?: 'Sweet girl looking for friendly chats.',
                'tags' => $host->tags ?: ['Sweet', 'Active', 'Live Host'],
            ];
        });

        // User status & balance info
        $callerData = null;
        if ($caller) {
            $isEligible = $caller->isEligibleForFreeCall();
            $callerData = [
                'id' => $caller->id,
                'account_id' => $caller->account_id,
                'name' => $caller->display_name,
                'coins' => (int) $caller->coins,
                'is_eligible_for_free_call' => $isEligible,
                'free_trial_duration_seconds' => $isEligible ? $config['free_call_duration_seconds'] : 0,
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'Match tab data loaded successfully.',
            'data' => [
                'waiting_count' => $displayWaitingCount,
                'actual_online_hosts' => $actualActiveCount,
                'heading' => 'People waiting to meet you',
                'button_text' => 'Start Matching',
                'hosts' => $hosts,
                'caller' => $callerData,
                'call_config' => [
                    'is_call_enabled' => $config['is_call_enabled'],
                    'default_video_rate' => $config['video_call_rate_per_minute'],
                    'free_call_duration_seconds' => $config['free_call_duration_seconds'],
                ],
            ],
        ], 200);
    }

    /**
     * Start Instant Random Match (Button: "Start Matching").
     * Checks wallet balance, selects random free host, and initiates call session.
     * POST /api/match/start or POST /api/call/match
     */
    public function startMatch(Request $request): JsonResponse
    {
        $caller = $this->resolveUser($request);

        if (!$caller) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated. Please provide Authorization token or user_id.',
            ], 401);
        }

        $config = CallSetting::getAllConfig();
        $targetGender = $request->input('gender', 'female');
        $callType = strtolower($request->input('call_type', 'video'));

        // Find available online host
        $query = User::where('id', '!=', $caller->id)
            ->where('is_active', true)
            ->where('is_locked', false)
            ->where('is_busy', false);

        if ($targetGender && $targetGender !== 'any') {
            $query->where(function ($q) use ($targetGender) {
                $q->where('gender', $targetGender)->orWhereNull('gender');
            });
        }

        $matchedHost = $query->inRandomOrder()->first();

        // Fallback to any host
        if (!$matchedHost) {
            $matchedHost = User::where('id', '!=', $caller->id)->inRandomOrder()->first();
        }

        if (!$matchedHost) {
            return response()->json([
                'status' => false,
                'message' => 'No online hosts are available right now. Please try again shortly.',
            ], 404);
        }

        $ratePerMinute = (int) ($matchedHost->video_call_rate ?: $config['video_call_rate_per_minute']);
        if ($callType === 'audio') {
            $ratePerMinute = (int) $config['audio_call_rate_per_minute'];
        }

        $isEligibleForFree = $caller->isEligibleForFreeCall();
        $canCall = $isEligibleForFree || ($caller->coins >= $ratePerMinute);

        // INSUFFICIENT COINS CHECK
        if (!$canCall) {
            $packages = CoinPackage::where('is_active', true)->orderBy('sort_order')->get();
            $paymentMethods = PaymentMethod::where('is_active', true)->get();

            return response()->json([
                'status' => false,
                'code' => 'LOW_BALANCE_DEPOSIT_REQUIRED',
                'message' => "Insufficient coin balance. You need at least {$ratePerMinute} coins for 1 minute of call.",
                'current_coins' => (int) $caller->coins,
                'required_coins' => $ratePerMinute,
                'is_low_balance' => true,
                'redirect_to_deposit' => true,
                'matched_host' => [
                    'id' => $matchedHost->id,
                    'account_id' => $matchedHost->account_id,
                    'display_name' => $matchedHost->display_name,
                    'avatar_url' => $matchedHost->avatar_url,
                    'video_call_rate' => $ratePerMinute,
                ],
                'coin_packages' => $packages,
                'payment_methods' => $paymentMethods,
            ], 402);
        }

        // Create WebRTC Call Session
        $channelName = 'match_' . $caller->id . '_' . $matchedHost->id . '_' . time() . '_' . Str::random(4);
        $freeDuration = $isEligibleForFree ? $config['free_call_duration_seconds'] : 0;

        $callSession = CallSession::create([
            'caller_id' => $caller->id,
            'receiver_id' => $matchedHost->id,
            'channel_name' => $channelName,
            'call_type' => $callType,
            'status' => 'ringing',
            'rate_per_minute' => $ratePerMinute,
            'is_free_trial' => $isEligibleForFree,
            'free_duration_seconds' => $freeDuration,
            'is_random_match' => true,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Match created successfully. Connecting call...',
            'data' => [
                'matched_host' => [
                    'id' => $matchedHost->id,
                    'account_id' => $matchedHost->account_id,
                    'display_name' => $matchedHost->display_name,
                    'avatar_url' => $matchedHost->avatar_url,
                    'cover_photo_url' => $matchedHost->cover_photo_url,
                    'country' => $matchedHost->country ?: 'Bangladesh',
                    'video_call_rate' => $ratePerMinute,
                    'level' => $matchedHost->level ?: 4,
                ],
                'call_session' => [
                    'call_id' => $callSession->id,
                    'channel_name' => $channelName,
                    'call_type' => $callType,
                    'status' => 'ringing',
                    'rate_per_minute' => $ratePerMinute,
                    'is_free_trial' => $isEligibleForFree,
                    'free_duration_seconds' => $freeDuration,
                ],
                'caller' => [
                    'id' => $caller->id,
                    'coins' => (int) $caller->coins,
                    'is_eligible_for_free_call' => $isEligibleForFree,
                ],
            ],
        ], 200);
    }
}
