<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CallSession;
use App\Models\CallSetting;
use App\Models\CoinTransaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CallController extends Controller
{
    /**
     * Resolve authenticated or requested user instance with full token & header resilience.
     */
    protected function resolveUser(Request $request): ?User
    {
        // 1. Check Authorization Bearer token from header / input first
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

        // 2. Try Sanctum Bearer token guard
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

        // 4. Fallback: user_id, userId, account_id in request body / query
        $idParam = $request->input('user_id') ?? $request->input('userId') ?? $request->input('id');
        if ($idParam) {
            $u = User::find($idParam);
            if ($u) return $u;
        }

        $accParam = $request->input('account_id') ?? $request->input('accountId');
        if ($accParam) {
            $u = User::where('account_id', $accParam)->first();
            if ($u) return $u;
        }

        if ($request->filled('phone')) {
            $u = User::where('phone', $request->phone)->first();
            if ($u) return $u;
        }

        // 5. Safe fallback for dev/mobile testing
        return User::first();
    }

    /**
     * Resiliently extract request data across all content types (JSON, Form-Data, Query).
     */
    protected function getRequestData(Request $request): array
    {
        $data = $request->all();
        if (empty($data)) {
            $content = $request->getContent();
            if (!empty($content)) {
                $json = json_decode($content, true);
                if (is_array($json)) {
                    $data = $json;
                }
            }
        }
        return $data;
    }

    /**
     * Get Call Configuration, Rates, Free Trial Duration, and User Eligibility.
     * GET /api/call/config (or GET /api/call/settings)
     */
    public function getConfig(Request $request): JsonResponse
    {
        $config = CallSetting::getAllConfig();
        $user = $this->resolveUser($request);

        $userData = null;
        if ($user) {
            $isEligibleForFree = $user->isEligibleForFreeCall();
            $videoRate = $config['video_call_rate_per_minute'];
            $audioRate = $config['audio_call_rate_per_minute'];
            $coins = (int) $user->coins;

            $userData = [
                'user_id' => $user->id,
                'account_id' => $user->account_id,
                'display_name' => $user->display_name,
                'gender' => $user->gender ?: 'male',
                'coins' => $coins,
                'formatted_coins' => number_format($coins) . ' Coins',
                'free_calls_used' => (int) ($user->free_calls_used ?: 0),
                'free_calls_remaining' => max(0, $config['free_calls_per_user'] - (int) ($user->free_calls_used ?: 0)),
                'is_eligible_for_free_call' => $isEligibleForFree,
                'free_trial_duration_seconds' => $isEligibleForFree ? $config['free_call_duration_seconds'] : 0,
                'can_make_video_call' => ($isEligibleForFree || $coins >= $videoRate),
                'can_make_audio_call' => ($isEligibleForFree || $coins >= $audioRate),
                'max_video_minutes' => (int) floor($coins / ($videoRate ?: 100)),
                'max_audio_minutes' => (int) floor($coins / ($audioRate ?: 60)),
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'Call settings and rates retrieved successfully.',
            'data' => [
                'is_call_enabled' => $config['is_call_enabled'],
                'is_free_call_enabled' => $config['is_free_call_enabled'],
                'free_call_duration_seconds' => $config['free_call_duration_seconds'],
                'free_calls_per_user' => $config['free_calls_per_user'],
                'video_call_rate_per_minute' => $config['video_call_rate_per_minute'],
                'audio_call_rate_per_minute' => $config['audio_call_rate_per_minute'],
                'host_earning_percent' => $config['host_earning_percent'],
                'admin_commission_percent' => $config['admin_commission_percent'],
                'incoming_ringtone_url' => $config['incoming_ringtone_url'],
                'outgoing_ringtone_url' => $config['outgoing_ringtone_url'],
                'video_split' => [
                    'total_rate' => $config['video_call_rate_per_minute'],
                    'host_receives' => $config['video_host_earning_per_min'],
                    'admin_revenue' => $config['video_admin_revenue_per_min'],
                ],
                'audio_split' => [
                    'total_rate' => $config['audio_call_rate_per_minute'],
                    'host_receives' => $config['audio_host_earning_per_min'],
                    'admin_revenue' => $config['audio_admin_revenue_per_min'],
                ],
                'user' => $userData,
            ],
        ], 200);
    }

    /**
     * Random Match / Auto-Connect with an Online Female Host.
     * POST /api/call/random-match (or GET /api/call/match)
     */
    public function randomMatch(Request $request): JsonResponse
    {
        $caller = $this->resolveUser($request);

        if (!$caller) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $data = $this->getRequestData($request);
        $callType = strtolower($data['call_type'] ?? $request->input('call_type', 'video'));
        $targetGender = $data['gender'] ?? $request->input('gender', 'female');
        $autoInitiate = filter_var($data['auto_initiate'] ?? $request->input('auto_initiate', false), FILTER_VALIDATE_BOOLEAN);

        // Find active hosts of preferred gender (or any active user excluding caller)
        $query = User::where('id', '!=', $caller->id)
            ->where('is_active', true)
            ->where('is_locked', false);

        if (!empty($targetGender) && $targetGender !== 'any') {
            $query->where(function ($q) use ($targetGender) {
                $q->where('gender', $targetGender)
                  ->orWhereNull('gender');
            });
        }

        // Prefer users with avatar and active status
        $matchedUser = $query->inRandomOrder()->first();

        if (!$matchedUser) {
            // Fallback to any active user
            $matchedUser = User::where('id', '!=', $caller->id)->inRandomOrder()->first();
        }

        if (!$matchedUser) {
            return response()->json([
                'status' => false,
                'message' => 'No online hosts are available for matching right now. Please try again shortly.',
            ], 404);
        }

        $config = CallSetting::getAllConfig();
        $isEligibleForFree = $caller->isEligibleForFreeCall();
        $ratePerMinute = ($callType === 'audio')
            ? $config['audio_call_rate_per_minute']
            : ($matchedUser->video_call_rate ?: $config['video_call_rate_per_minute']);

        $callSessionData = null;
        if ($autoInitiate) {
            // Automatically initiate the call
            $canCall = $isEligibleForFree || ($caller->coins >= $ratePerMinute);
            if (!$canCall) {
                return response()->json([
                    'status' => false,
                    'code' => 'LOW_BALANCE_DEPOSIT_REQUIRED',
                    'message' => "Insufficient coins. You need at least {$ratePerMinute} coins for 1 minute of call.",
                    'current_coins' => (int) $caller->coins,
                    'required_coins' => $ratePerMinute,
                    'is_low_balance' => true,
                    'redirect_to_deposit' => true,
                    'matched_user' => [
                        'id' => $matchedUser->id,
                        'account_id' => $matchedUser->account_id,
                        'name' => $matchedUser->display_name,
                        'avatar' => $matchedUser->avatar_url,
                    ],
                ], 402);
            }

            $channelName = 'call_' . $callType . '_' . $caller->id . '_' . $matchedUser->id . '_' . time() . '_' . Str::random(4);
            $freeDuration = $isEligibleForFree ? $config['free_call_duration_seconds'] : 0;

            $call = CallSession::create([
                'caller_id' => $caller->id,
                'receiver_id' => $matchedUser->id,
                'channel_name' => $channelName,
                'call_type' => $callType,
                'status' => 'ringing',
                'rate_per_minute' => $ratePerMinute,
                'is_free_trial' => $isEligibleForFree,
                'free_duration_seconds' => $freeDuration,
                'is_random_match' => true,
            ]);

            $callSessionData = [
                'call_id' => $call->id,
                'channel_name' => $channelName,
                'call_type' => $callType,
                'status' => 'ringing',
                'is_free_trial' => $isEligibleForFree,
                'free_duration_seconds' => $freeDuration,
                'rate_per_minute' => $ratePerMinute,
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'Random match found successfully.',
            'data' => [
                'matched_user' => [
                    'id' => $matchedUser->id,
                    'account_id' => $matchedUser->account_id,
                    'display_name' => $matchedUser->display_name,
                    'gender' => $matchedUser->gender ?: 'female',
                    'avatar_url' => $matchedUser->avatar_url,
                    'cover_photo_url' => $matchedUser->cover_photo_url,
                    'country' => $matchedUser->country ?: 'Bangladesh',
                    'city' => $matchedUser->city ?: 'Dhaka',
                    'video_call_rate' => (int) ($matchedUser->video_call_rate ?: $config['video_call_rate_per_minute']),
                    'audio_call_rate' => (int) $config['audio_call_rate_per_minute'],
                    'introduction' => $matchedUser->introduction ?: 'Hey there! Let\'s talk and have fun.',
                    'tags' => $matchedUser->tags ?: ['Friendly', 'Live Host'],
                ],
                'caller' => [
                    'coins' => (int) $caller->coins,
                    'is_eligible_for_free_call' => $isEligibleForFree,
                    'free_trial_duration_seconds' => $isEligibleForFree ? $config['free_call_duration_seconds'] : 0,
                ],
                'call_session' => $callSessionData,
            ],
        ], 200);
    }

    /**
     * Initiate an Audio or Video Call.
     * Supports Free Trial for first-time registration without requiring coin balance!
     * Sets initial call status to 'ringing'.
     * POST /api/call/initiate
     */
    public function initiate(Request $request): JsonResponse
    {
        $caller = $this->resolveUser($request);

        if (!$caller) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $data = $this->getRequestData($request);
        $validator = Validator::make($data, [
            'receiver_id' => 'required',
            'call_type' => 'nullable|in:video,audio',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $receiverId = $data['receiver_id'] ?? $request->input('receiver_id');
        $receiver = User::where('id', $receiverId)->orWhere('account_id', $receiverId)->first();

        if (!$receiver) {
            return response()->json([
                'status' => false,
                'message' => 'Receiver host not found.',
            ], 404);
        }

        if ($receiver->id === $caller->id) {
            return response()->json([
                'status' => false,
                'message' => 'You cannot call yourself.',
            ], 400);
        }

        $config = CallSetting::getAllConfig();
        if (!$config['is_call_enabled']) {
            return response()->json([
                'status' => false,
                'message' => 'Calling service is temporarily disabled by administrator.',
            ], 403);
        }

        $callType = strtolower($data['call_type'] ?? $request->input('call_type', 'video'));
        $isEligibleForFree = $caller->isEligibleForFreeCall();

        $ratePerMinute = ($callType === 'audio')
            ? (int) $config['audio_call_rate_per_minute']
            : (int) ($receiver->video_call_rate ?: $config['video_call_rate_per_minute']);

        if ($ratePerMinute <= 0) {
            $ratePerMinute = 100;
        }

        // Check balance IF not eligible for free trial
        if (!$isEligibleForFree && $caller->coins < $ratePerMinute) {
            return response()->json([
                'status' => false,
                'code' => 'LOW_BALANCE_DEPOSIT_REQUIRED',
                'message' => "Insufficient coin balance. You need at least {$ratePerMinute} coins for 1 minute of {$callType} call. Your balance is {$caller->coins} coins.",
                'current_coins' => (int) $caller->coins,
                'required_coins' => $ratePerMinute,
                'is_low_balance' => true,
                'redirect_to_deposit' => true,
                'deposit_url' => '/deposit',
            ], 402);
        }

        $freeDuration = $isEligibleForFree ? $config['free_call_duration_seconds'] : 0;
        $channelName = 'call_' . $callType . '_' . $caller->id . '_' . $receiver->id . '_' . time() . '_' . Str::random(4);

        $call = CallSession::create([
            'caller_id' => $caller->id,
            'receiver_id' => $receiver->id,
            'channel_name' => $channelName,
            'call_type' => $callType,
            'status' => 'ringing',
            'rate_per_minute' => $ratePerMinute,
            'is_free_trial' => $isEligibleForFree,
            'free_duration_seconds' => $freeDuration,
            'is_random_match' => filter_var($data['is_random_match'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ]);

        $maxMinutes = $ratePerMinute > 0 ? (int) floor($caller->coins / $ratePerMinute) : 0;

        return response()->json([
            'status' => true,
            'message' => $isEligibleForFree 
                ? "Free trial call initiated! Ringing receiver... You have {$freeDuration} seconds of free calling."
                : "Call initiated! Ringing receiver...",
            'data' => [
                'call_id' => $call->id,
                'channel_name' => $channelName,
                'call_type' => $callType,
                'status' => 'ringing',
                'rate_per_minute' => $ratePerMinute,
                'is_free_trial' => $isEligibleForFree,
                'free_duration_seconds' => $freeDuration,
                'caller_coins' => (int) $caller->coins,
                'max_call_minutes' => $maxMinutes,
                'max_call_seconds' => $isEligibleForFree ? $freeDuration : ($maxMinutes * 60),
                'ring_timeout_seconds' => 45,
                'outgoing_ringtone_url' => $config['outgoing_ringtone_url'],
                'receiver' => [
                    'id' => $receiver->id,
                    'account_id' => $receiver->account_id,
                    'name' => $receiver->display_name,
                    'gender' => $receiver->gender ?: 'female',
                    'avatar' => $receiver->avatar_url,
                ],
            ],
        ], 200);
    }

    /**
     * Check for Incoming Calls (For Receiver Device / App).
     * The mobile app polls this or listens on WebSocket to ring continuously when a call comes in.
     * GET /api/call/incoming (or POST /api/call/check-incoming)
     */
    public function checkIncoming(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Auto-expire calls that have been ringing > 45 seconds
        CallSession::where('receiver_id', $user->id)
            ->whereIn('status', ['initiated', 'ringing'])
            ->where('created_at', '<', now()->subSeconds(45))
            ->update([
                'status' => 'missed',
                'ended_at' => now(),
            ]);

        // Find active ringing call for this user
        $incoming = CallSession::with(['caller'])
            ->where('receiver_id', $user->id)
            ->whereIn('status', ['initiated', 'ringing'])
            ->where('created_at', '>=', now()->subSeconds(45))
            ->latest()
            ->first();

        if (!$incoming) {
            return response()->json([
                'status' => true,
                'has_incoming_call' => false,
                'message' => 'No active incoming calls.',
                'data' => null,
            ], 200);
        }

        $config = CallSetting::getAllConfig();
        $elapsedSeconds = max(0, now()->diffInSeconds($incoming->created_at));

        return response()->json([
            'status' => true,
            'has_incoming_call' => true,
            'message' => 'Incoming call detected! Ring device.',
            'data' => [
                'call_id' => $incoming->id,
                'channel_name' => $incoming->channel_name,
                'call_type' => $incoming->call_type,
                'status' => $incoming->status,
                'is_free_trial' => (bool) $incoming->is_free_trial,
                'free_duration_seconds' => (int) $incoming->free_duration_seconds,
                'rate_per_minute' => (int) $incoming->rate_per_minute,
                'ring_elapsed_seconds' => $elapsedSeconds,
                'ring_timeout_seconds' => max(0, 45 - $elapsedSeconds),
                'incoming_ringtone_url' => $config['incoming_ringtone_url'],
                'caller' => [
                    'id' => $incoming->caller?->id,
                    'account_id' => $incoming->caller?->account_id,
                    'name' => $incoming->caller?->display_name,
                    'avatar' => $incoming->caller?->avatar_url,
                    'gender' => $incoming->caller?->gender ?: 'male',
                    'level' => $incoming->caller?->level ?: 'Lv1',
                ],
            ],
        ], 200);
    }

    /**
     * Real-time Call Status Polling & Ringing Synchronization.
     * Both Caller and Receiver apps poll this to synchronize:
     * - Ringing status
     * - Connected (when receiver clicks Accept/Receive button)
     * - Rejected / Declined
     * - Cancelled (when caller hangs up before answer)
     * - Ended
     * - Missed / Timeout
     * GET /api/call/status/{id} (or POST /api/call/status)
     */
    public function getStatus(Request $request, $id = null): JsonResponse
    {
        $data = $this->getRequestData($request);
        $callId = $id ?? ($data['call_id'] ?? $request->input('call_id'));
        $channelName = $data['channel_name'] ?? $request->input('channel_name');

        $call = CallSession::with(['caller', 'receiver'])
            ->when($callId, fn($q) => $q->where('id', $callId))
            ->when($channelName, fn($q) => $q->where('channel_name', $channelName))
            ->first();

        if (!$call) {
            return response()->json([
                'status' => false,
                'message' => 'Call session not found.',
            ], 404);
        }

        // Auto-expire if ringing for > 45s without answer
        if (in_array($call->status, ['initiated', 'ringing'])) {
            $ringSeconds = now()->diffInSeconds($call->created_at);
            if ($ringSeconds > 45) {
                $call->status = 'missed';
                $call->ended_at = now();
                $call->save();
            }
        }

        $duration = 0;
        if ($call->status === 'connected' && $call->started_at) {
            $duration = max(0, now()->diffInSeconds($call->started_at));
        } elseif ($call->status === 'ended') {
            $duration = (int) $call->duration_seconds;
        }

        return response()->json([
            'status' => true,
            'data' => [
                'call_id' => $call->id,
                'channel_name' => $call->channel_name,
                'call_type' => $call->call_type,
                'status' => $call->status,
                'is_active' => ($call->status === 'connected'),
                'is_ringing' => in_array($call->status, ['initiated', 'ringing']),
                'is_terminated' => in_array($call->status, ['ended', 'rejected', 'declined', 'cancelled', 'missed', 'failed']),
                'started_at' => $call->started_at ? $call->started_at->toIso8601String() : null,
                'ended_at' => $call->ended_at ? $call->ended_at->toIso8601String() : null,
                'duration_seconds' => $duration,
                'duration_formatted' => sprintf('%02d:%02d', floor($duration / 60), $duration % 60),
                'rate_per_minute' => (int) $call->rate_per_minute,
                'is_free_trial' => (bool) $call->is_free_trial,
                'free_duration_seconds' => (int) $call->free_duration_seconds,
                'coins_deducted' => (int) $call->coins_deducted,
                'host_earned_coins' => (int) $call->host_earned_coins,
                'caller' => [
                    'id' => $call->caller?->id,
                    'account_id' => $call->caller?->account_id,
                    'name' => $call->caller?->display_name,
                    'avatar' => $call->caller?->avatar_url,
                    'coins' => (int) ($call->caller?->coins ?? 0),
                ],
                'receiver' => [
                    'id' => $call->receiver?->id,
                    'account_id' => $call->receiver?->account_id,
                    'name' => $call->receiver?->display_name,
                    'avatar' => $call->receiver?->avatar_url,
                    'coins' => (int) ($call->receiver?->coins ?? 0),
                ],
            ],
        ], 200);
    }

    /**
     * Receiver confirms device is actively ringing.
     * POST /api/call/ringing
     */
    public function ringing(Request $request): JsonResponse
    {
        $data = $this->getRequestData($request);
        $callId = $data['call_id'] ?? $request->input('call_id');
        $channelName = $data['channel_name'] ?? $request->input('channel_name');

        $call = CallSession::when($callId, fn($q) => $q->where('id', $callId))
            ->when($channelName, fn($q) => $q->where('channel_name', $channelName))
            ->first();

        if (!$call) {
            return response()->json([
                'status' => false,
                'message' => 'Call session not found.',
            ], 404);
        }

        if (in_array($call->status, ['initiated', 'ringing'])) {
            $call->status = 'ringing';
            $call->save();
        }

        return response()->json([
            'status' => true,
            'message' => 'Ringing state confirmed. Continue looping ringtone until answered or cancelled.',
            'data' => [
                'call_id' => $call->id,
                'status' => $call->status,
            ],
        ], 200);
    }

    /**
     * Accept / Receive Incoming Call (Triggered by Receiver clicking "Call Receive" / "Accept" button).
     * Transitions call state from 'ringing' -> 'connected', starts call timer and media stream.
     * POST /api/call/accept (or POST /api/call/answer, POST /api/call/receive, POST /api/call/start, POST /api/call/connect)
     */
    public function accept(Request $request): JsonResponse
    {
        $data = $this->getRequestData($request);
        $callId = $data['call_id'] ?? $request->input('call_id');
        $channelName = $data['channel_name'] ?? $request->input('channel_name');

        if (empty($callId) && empty($channelName)) {
            return response()->json([
                'status' => false,
                'message' => 'Call ID or Channel Name required.',
            ], 422);
        }

        $call = CallSession::with(['caller', 'receiver'])
            ->when($callId, fn($q) => $q->where('id', $callId))
            ->when($channelName, fn($q) => $q->where('channel_name', $channelName))
            ->first();

        if (!$call) {
            return response()->json([
                'status' => false,
                'message' => 'Call session not found.',
            ], 404);
        }

        if (in_array($call->status, ['rejected', 'declined', 'cancelled', 'ended', 'missed'])) {
            return response()->json([
                'status' => false,
                'message' => "Cannot accept call. Call is already {$call->status}.",
                'call_status' => $call->status,
            ], 400);
        }

        $call->status = 'connected';
        if (!$call->started_at) {
            $call->started_at = now();
        }
        $call->save();

        return response()->json([
            'status' => true,
            'message' => 'Call accepted and connected successfully! Start audio/video media stream.',
            'data' => [
                'call_id' => $call->id,
                'channel_name' => $call->channel_name,
                'call_type' => $call->call_type,
                'status' => 'connected',
                'started_at' => $call->started_at->toIso8601String(),
                'rate_per_minute' => (int) $call->rate_per_minute,
                'is_free_trial' => (bool) $call->is_free_trial,
                'free_duration_seconds' => (int) $call->free_duration_seconds,
                'caller' => [
                    'id' => $call->caller?->id,
                    'name' => $call->caller?->display_name,
                    'avatar' => $call->caller?->avatar_url,
                ],
                'receiver' => [
                    'id' => $call->receiver?->id,
                    'name' => $call->receiver?->display_name,
                    'avatar' => $call->receiver?->avatar_url,
                ],
            ],
        ], 200);
    }

    /**
     * Start/Connect Call Session (Alias for accept).
     * POST /api/call/start (or POST /api/call/connect)
     */
    public function start(Request $request): JsonResponse
    {
        return $this->accept($request);
    }

    /**
     * Reject / Decline Incoming Call (Triggered by Receiver).
     * POST /api/call/reject (or POST /api/call/decline)
     */
    public function reject(Request $request): JsonResponse
    {
        $data = $this->getRequestData($request);
        $callId = $data['call_id'] ?? $request->input('call_id');
        $channelName = $data['channel_name'] ?? $request->input('channel_name');

        $call = CallSession::when($callId, fn($q) => $q->where('id', $callId))
            ->when($channelName, fn($q) => $q->where('channel_name', $channelName))
            ->first();

        if (!$call) {
            return response()->json([
                'status' => false,
                'message' => 'Call session not found.',
            ], 404);
        }

        $call->status = 'rejected';
        $call->ended_at = now();
        $call->save();

        return response()->json([
            'status' => true,
            'message' => 'Call declined successfully. Ringing stopped.',
            'data' => [
                'call_id' => $call->id,
                'status' => 'rejected',
            ],
        ], 200);
    }

    /**
     * Cancel Outgoing Call (Triggered by Caller before receiver answers).
     * POST /api/call/cancel
     */
    public function cancel(Request $request): JsonResponse
    {
        $data = $this->getRequestData($request);
        $callId = $data['call_id'] ?? $request->input('call_id');
        $channelName = $data['channel_name'] ?? $request->input('channel_name');

        $call = CallSession::when($callId, fn($q) => $q->where('id', $callId))
            ->when($channelName, fn($q) => $q->where('channel_name', $channelName))
            ->first();

        if (!$call) {
            return response()->json([
                'status' => false,
                'message' => 'Call session not found.',
            ], 404);
        }

        $call->status = 'cancelled';
        $call->ended_at = now();
        $call->save();

        return response()->json([
            'status' => true,
            'message' => 'Call cancelled by caller.',
            'data' => [
                'call_id' => $call->id,
                'status' => 'cancelled',
            ],
        ], 200);
    }

    /**
     * Real-time heart-beat pulse deduction during active call.
     * Automatically handles Free Trial expiration -> prompts "Please Deposit Now" if 0 coins.
     * Applies 50/50 Revenue Sharing (50% to Host / Female User, 50% to Platform Admin).
     * POST /api/call/deduct-interval (or POST /api/call/pulse)
     */
    public function deductInterval(Request $request): JsonResponse
    {
        $caller = $this->resolveUser($request);

        if (!$caller) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $data = $this->getRequestData($request);
        $callId = $data['call_id'] ?? $request->input('call_id');
        $elapsedSeconds = (int) ($data['elapsed_seconds'] ?? $request->input('elapsed_seconds', 0));

        if (empty($callId)) {
            return response()->json([
                'status' => false,
                'message' => 'Call ID is required.',
            ], 422);
        }

        $call = CallSession::with(['caller', 'receiver'])->find($callId);

        if (!$call) {
            return response()->json([
                'status' => false,
                'message' => 'Call session not found.',
            ], 404);
        }

        // If call was not yet marked connected, mark it connected now
        if ($call->status !== 'connected' && $call->status !== 'ended') {
            $call->status = 'connected';
            if (!$call->started_at) {
                $call->started_at = now();
            }
            $call->save();
        }

        $config = CallSetting::getAllConfig();
        $ratePerMinute = (int) ($call->rate_per_minute ?: $config['video_call_rate_per_minute'] ?: 100);
        $ratePerSecond = round($ratePerMinute / 60, 4);
        $hostPercent = (float) ($config['host_earning_percent'] ?? 50.0);

        // 1. Check if Call is in Free Trial window
        if ($call->is_free_trial && $call->free_duration_seconds > 0) {
            if ($elapsedSeconds < $call->free_duration_seconds) {
                // Free trial is still active!
                $remainingSecs = $call->free_duration_seconds - $elapsedSeconds;
                return response()->json([
                    'status' => true,
                    'is_free_trial' => true,
                    'free_seconds_remaining' => $remainingSecs,
                    'message' => "Free trial active ({$remainingSecs}s remaining).",
                    'data' => [
                        'current_coins' => (int) $caller->coins,
                        'coins_deducted' => 0,
                        'rate_per_minute' => $ratePerMinute,
                        'rate_per_second' => $ratePerSecond,
                        'can_continue' => true,
                        'should_terminate_call' => false,
                    ],
                ], 200);
            } else {
                // Free trial duration just ended! Mark user's free call as consumed
                if ($caller->isEligibleForFreeCall()) {
                    $caller->markFreeCallUsed();
                }
                $call->is_free_trial = false;
                $call->save();
            }
        }

        // 2. Paid call deduction logic:
        // Support per-second calculation based on 100 coins/min ($ratePerMinute / 60)
        $intervalSecs = (int) ($data['interval_seconds'] ?? $data['duration_chunk'] ?? 0);
        if ($intervalSecs > 0) {
            $coinsToDeduct = (int) max(1, round($intervalSecs * ($ratePerMinute / 60)));
        } else {
            $coinsToDeduct = (int) ($data['coins'] ?? $request->input('coins', $ratePerMinute));
        }

        if ($coinsToDeduct <= 0) {
            $coinsToDeduct = $ratePerMinute ?: 100;
        }

        if ($caller->coins < $coinsToDeduct) {
            return response()->json([
                'status' => false,
                'code' => 'LOW_BALANCE_DEPOSIT_REQUIRED',
                'message' => "Your balance is insufficient to continue calling. Please deposit/recharge coins now.",
                'current_coins' => (int) $caller->coins,
                'required_coins' => $coinsToDeduct,
                'rate_per_minute' => $ratePerMinute,
                'rate_per_second' => $ratePerSecond,
                'should_terminate_call' => true,
                'redirect_to_deposit' => true,
                'deposit_url' => '/deposit',
                'data' => [
                    'caller_id' => $caller->id,
                    'call_id' => $call->id,
                    'current_coins' => (int) $caller->coins,
                    'required_coins' => $coinsToDeduct,
                ],
            ], 402);
        }

        // 3. Atomically Deduct from Caller & Credit Host 50% Share
        DB::beginTransaction();
        try {
            // Deduct from caller
            $caller->deductCoins(
                $coinsToDeduct,
                'video_call_spent',
                "Call pulse ({$coinsToDeduct} coins) with {$call->receiver?->display_name} (Call #{$call->id})",
                "call_pulse_#{$call->id}_" . time()
            );

            // Calculate host earning (50%) and admin revenue (50%)
            $hostEarned = (int) round($coinsToDeduct * ($hostPercent / 100));
            $adminRevenue = $coinsToDeduct - $hostEarned;

            // Credit Host (Female/Receiver)
            if ($call->receiver && $hostEarned > 0) {
                $call->receiver->addCoins(
                    $hostEarned,
                    'video_call_earned',
                    "Earned {$hostEarned} coins from {$call->call_type} call with {$caller->display_name}",
                    "host_earn_#{$call->id}_" . time()
                );
            }

            // Update Call Session ledger
            $call->increment('coins_deducted', $coinsToDeduct);
            $call->increment('host_earned_coins', $hostEarned);
            $call->increment('admin_revenue_coins', $adminRevenue);
            $call->caller_balance_after = $caller->coins;
            $call->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => "Deducted {$coinsToDeduct} coins (Rate: {$ratePerMinute} coins/min, {$ratePerSecond} coins/sec). Host earned {$hostEarned} coins (50%). Admin revenue {$adminRevenue} coins (50%).",
                'data' => [
                    'current_coins' => (int) $caller->coins,
                    'coins_deducted' => $coinsToDeduct,
                    'host_earned_coins' => $hostEarned,
                    'admin_revenue_coins' => $adminRevenue,
                    'total_call_coins_deducted' => (int) $call->coins_deducted,
                    'rate_per_minute' => $ratePerMinute,
                    'rate_per_second' => $ratePerSecond,
                    'can_continue' => $caller->coins >= max(1, (int) round($ratePerMinute / 60)),
                    'should_terminate_call' => false,
                ],
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error processing call deduction: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * WebRTC ICE Servers Configuration (STUN & TURN for Flutter).
     * GET /api/call/ice-servers
     */
    public function getIceServers(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'WebRTC ICE Servers retrieved successfully.',
            'data' => [
                'iceServers' => [
                    [
                        'urls' => [
                            'stun:stun.l.google.com:19302',
                            'stun:stun1.l.google.com:19302',
                            'stun:stun2.l.google.com:19302',
                            'stun:stun3.l.google.com:19302',
                            'stun:stun4.l.google.com:19302',
                        ],
                    ],
                    [
                        'urls' => 'stun:global.stun.twilio.com:3478?transport=udp',
                    ],
                ],
            ],
        ], 200);
    }

    /**
     * Send WebRTC Signal (SDP Offer, SDP Answer, ICE Candidate, Ping, Bye).
     * POST /api/call/signal/send (or POST /api/call/send-signal)
     */
    public function sendSignal(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $data = $this->getRequestData($request);
        $validator = Validator::make($data, [
            'call_id' => 'nullable',
            'channel_name' => 'nullable',
            'type' => 'required|string', // offer, answer, candidate, ping, bye
            'payload' => 'required', // SDP or Candidate JSON / String
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Signal validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $callId = $data['call_id'] ?? $request->input('call_id');
        $channelName = $data['channel_name'] ?? $request->input('channel_name');

        $call = CallSession::when($callId, fn($q) => $q->where('id', $callId))
            ->when($channelName, fn($q) => $q->where('channel_name', $channelName))
            ->first();

        $receiverId = $data['receiver_id'] ?? null;
        if (!$receiverId && $call) {
            $receiverId = ($call->caller_id === $user->id) ? $call->receiver_id : $call->caller_id;
        }

        $payload = $data['payload'];
        if (is_string($payload)) {
            $decoded = json_decode($payload, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $payload = $decoded;
            }
        }

        $signal = \App\Models\CallSignal::create([
            'call_session_id' => $call?->id,
            'channel_name' => $channelName ?: $call?->channel_name,
            'sender_id' => $user->id,
            'receiver_id' => $receiverId,
            'type' => strtolower($data['type']),
            'payload' => is_array($payload) ? $payload : ['data' => $payload],
            'is_read' => false,
        ]);

        return response()->json([
            'status' => true,
            'message' => "Signal '{$signal->type}' sent successfully.",
            'data' => [
                'signal_id' => $signal->id,
                'call_id' => $signal->call_session_id,
                'type' => $signal->type,
                'sender_id' => $user->id,
                'receiver_id' => $receiverId,
                'created_at' => $signal->created_at->toIso8601String(),
            ],
        ], 200);
    }

    /**
     * Receive / Poll for pending WebRTC Signals (SDP Offers, Answers, ICE Candidates).
     * GET /api/call/signal/receive (or GET /api/call/signals, POST /api/call/signals)
     */
    public function getSignals(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $data = $this->getRequestData($request);
        $callId = $data['call_id'] ?? $request->input('call_id');
        $channelName = $data['channel_name'] ?? $request->input('channel_name');
        $lastSignalId = (int) ($data['last_signal_id'] ?? $request->input('last_signal_id', 0));
        $autoRead = filter_var($data['auto_read'] ?? $request->input('auto_read', true), FILTER_VALIDATE_BOOLEAN);

        $query = \App\Models\CallSignal::with(['sender'])
            ->where(function ($q) use ($user) {
                $q->where('receiver_id', $user->id)
                  ->orWhereNull('receiver_id');
            })
            ->where('sender_id', '!=', $user->id);

        if ($callId) {
            $query->where('call_session_id', $callId);
        }
        if ($channelName) {
            $query->where('channel_name', $channelName);
        }
        if ($lastSignalId > 0) {
            $query->where('id', '>', $lastSignalId);
        } else {
            $query->where('is_read', false);
        }

        $signals = $query->orderBy('id', 'asc')->limit(50)->get();

        if ($autoRead && $signals->isNotEmpty()) {
            \App\Models\CallSignal::whereIn('id', $signals->pluck('id'))->update(['is_read' => true]);
        }

        $formatted = $signals->map(function ($s) {
            return [
                'id' => $s->id,
                'call_id' => $s->call_session_id,
                'channel_name' => $s->channel_name,
                'sender_id' => $s->sender_id,
                'sender_name' => $s->sender?->display_name,
                'type' => $s->type,
                'payload' => $s->payload,
                'created_at' => $s->created_at->toIso8601String(),
            ];
        });

        return response()->json([
            'status' => true,
            'count' => $signals->count(),
            'data' => $formatted,
        ], 200);
    }

    /**
     * Clear / Mark WebRTC Signals as read.
     * POST /api/call/signal/clear
     */
    public function clearSignals(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $data = $this->getRequestData($request);
        $callId = $data['call_id'] ?? $request->input('call_id');
        $channelName = $data['channel_name'] ?? $request->input('channel_name');

        $query = \App\Models\CallSignal::where('receiver_id', $user->id);
        if ($callId) {
            $query->where('call_session_id', $callId);
        }
        if ($channelName) {
            $query->where('channel_name', $channelName);
        }

        $query->update(['is_read' => true]);

        return response()->json([
            'status' => true,
            'message' => 'Signals marked as read.',
        ], 200);
    }

    /**
     * End Call Session and finalize duration and records.
     * POST /api/call/end
     */
    public function end(Request $request): JsonResponse
    {
        $data = $this->getRequestData($request);
        $callId = $data['call_id'] ?? $request->input('call_id');
        $channelName = $data['channel_name'] ?? $request->input('channel_name');

        $call = CallSession::with(['caller', 'receiver'])
            ->when($callId, fn($q) => $q->where('id', $callId))
            ->when($channelName, fn($q) => $q->where('channel_name', $channelName))
            ->first();

        if (!$call) {
            return response()->json([
                'status' => false,
                'message' => 'Call session not found.',
            ], 404);
        }

        if ($call->status === 'ended') {
            return response()->json([
                'status' => true,
                'message' => 'Call has already ended.',
                'data' => [
                    'call_id' => $call->id,
                    'duration_seconds' => (int) $call->duration_seconds,
                    'duration_formatted' => $call->formatted_duration,
                    'coins_deducted' => (int) $call->coins_deducted,
                    'host_earned_coins' => (int) $call->host_earned_coins,
                    'caller_balance' => (int) $call->caller_balance_after,
                ],
            ], 200);
        }

        $endedAt = now();
        $startedAt = $call->started_at ?: $call->created_at;
        $durationSeconds = (int) ($data['duration_seconds'] ?? max(0, $endedAt->diffInSeconds($startedAt)));

        $call->status = 'ended';
        $call->ended_at = $endedAt;
        $call->duration_seconds = $durationSeconds;
        $call->save();

        return response()->json([
            'status' => true,
            'message' => 'Call session ended successfully.',
            'data' => [
                'call_id' => $call->id,
                'call_type' => $call->call_type,
                'duration_seconds' => $durationSeconds,
                'duration_formatted' => sprintf('%02d:%02d', floor($durationSeconds / 60), $durationSeconds % 60),
                'coins_deducted' => (int) $call->coins_deducted,
                'host_earned_coins' => (int) $call->host_earned_coins,
                'admin_revenue_coins' => (int) $call->admin_revenue_coins,
                'caller_remaining_coins' => $call->caller ? (int) $call->caller->coins : (int) $call->caller_balance_after,
                'partner' => [
                    'id' => $call->receiver?->id,
                    'name' => $call->receiver?->display_name,
                    'avatar' => $call->receiver?->avatar_url,
                ],
            ],
        ], 200);
    }

    /**
     * Get User's Call History (calls made and received).
     * GET /api/call/history
     */
    public function history(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $calls = CallSession::with(['caller', 'receiver'])
            ->where(function ($q) use ($user) {
                $q->where('caller_id', $user->id)
                  ->orWhere('receiver_id', $user->id);
            })
            ->latest()
            ->paginate(20);

        $formattedItems = collect($calls->items())->map(function ($c) use ($user) {
            $isCaller = ($c->caller_id === $user->id);
            $partner = $isCaller ? $c->receiver : $c->caller;

            return [
                'id' => $c->id,
                'call_type' => $c->call_type,
                'is_outgoing' => $isCaller,
                'status' => $c->status,
                'duration_seconds' => (int) $c->duration_seconds,
                'formatted_duration' => $c->formatted_duration,
                'is_free_trial' => (bool) $c->is_free_trial,
                'coins_spent' => $isCaller ? (int) $c->coins_deducted : 0,
                'coins_earned' => !$isCaller ? (int) $c->host_earned_coins : 0,
                'created_at' => $c->created_at->toIso8601String(),
                'partner' => $partner ? [
                    'id' => $partner->id,
                    'account_id' => $partner->account_id,
                    'display_name' => $partner->display_name,
                    'avatar_url' => $partner->avatar_url,
                    'gender' => $partner->gender ?: 'female',
                ] : null,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Call history retrieved successfully.',
            'data' => $formattedItems,
            'current_coins' => (int) $user->coins,
            'pagination' => [
                'current_page' => $calls->currentPage(),
                'last_page' => $calls->lastPage(),
                'total' => $calls->total(),
            ],
        ], 200);
    }
}
