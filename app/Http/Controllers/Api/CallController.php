<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CallSession;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CallController extends Controller
{
    const CALL_RATE_PER_MINUTE = 100; // 100 coins per 1 minute

    /**
     * Resolve user instance.
     */
    protected function resolveUser(Request $request): ?User
    {
        if (Auth::guard('sanctum')->check()) {
            return Auth::guard('sanctum')->user();
        }

        if ($userId = $request->input('user_id') ?: $request->header('X-User-Id')) {
            return User::where('id', $userId)->orWhere('account_id', $userId)->first();
        }

        return null;
    }

    /**
     * Initiate a Video Call. Checks caller balance (must have at least 1 minute worth = 100 coins).
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

        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Receiver ID is required.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $receiverId = $request->input('receiver_id');
        $receiver = User::where('id', $receiverId)->orWhere('account_id', $receiverId)->first();

        if (!$receiver) {
            return response()->json([
                'status' => false,
                'message' => 'Receiver user not found.',
            ], 404);
        }

        if ($receiver->id === $caller->id) {
            return response()->json([
                'status' => false,
                'message' => 'You cannot call yourself.',
            ], 400);
        }

        $ratePerMinute = $receiver->video_call_rate ?: self::CALL_RATE_PER_MINUTE;
        if ($ratePerMinute <= 0) {
            $ratePerMinute = self::CALL_RATE_PER_MINUTE;
        }

        // Check if caller has enough coins for at least 1 minute
        if ($caller->coins < $ratePerMinute) {
            return response()->json([
                'status' => false,
                'message' => "Insufficient coin balance. You need at least {$ratePerMinute} coins for 1 minute of video call. Your balance is {$caller->coins} coins.",
                'current_coins' => (int) $caller->coins,
                'required_coins' => $ratePerMinute,
                'is_low_balance' => true,
            ], 402);
        }

        $channelName = 'call_' . $caller->id . '_' . $receiver->id . '_' . time() . '_' . Str::random(4);

        $call = CallSession::create([
            'caller_id' => $caller->id,
            'receiver_id' => $receiver->id,
            'channel_name' => $channelName,
            'status' => 'initiated',
            'rate_per_minute' => $ratePerMinute,
        ]);

        $maxMinutes = (int) floor($caller->coins / $ratePerMinute);

        return response()->json([
            'status' => true,
            'message' => 'Call initiated successfully.',
            'data' => [
                'call_id' => $call->id,
                'channel_name' => $channelName,
                'rate_per_minute' => $ratePerMinute,
                'caller_coins' => (int) $caller->coins,
                'max_call_minutes' => $maxMinutes,
                'max_call_seconds' => $maxMinutes * 60,
                'receiver' => [
                    'id' => $receiver->id,
                    'account_id' => $receiver->account_id,
                    'name' => $receiver->display_name,
                    'avatar' => $receiver->avatar_url,
                ],
            ],
        ], 200);
    }

    /**
     * Start/Connect Call Session (when receiver answers).
     * POST /api/call/start
     */
    public function start(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'call_id' => 'required_without:channel_name',
            'channel_name' => 'required_without:call_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => 'Call ID or Channel Name required.'], 422);
        }

        $call = CallSession::when($request->call_id, fn($q) => $q->where('id', $request->call_id))
            ->when($request->channel_name, fn($q) => $q->where('channel_name', $request->channel_name))
            ->firstOrFail();

        $call->status = 'connected';
        $call->started_at = now();
        $call->save();

        return response()->json([
            'status' => true,
            'message' => 'Call connected.',
            'data' => [
                'call_id' => $call->id,
                'channel_name' => $call->channel_name,
                'status' => $call->status,
                'started_at' => $call->started_at->toIso8601String(),
                'rate_per_minute' => $call->rate_per_minute,
            ],
        ], 200);
    }

    /**
     * End Call Session and deduct coins based on duration.
     * Rate: 100 coins per 1 minute (rounded up to the nearest minute or proportional).
     * POST /api/call/end
     */
    public function end(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'call_id' => 'required_without:channel_name',
            'channel_name' => 'required_without:call_id',
            'duration_seconds' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => 'Validation failed.', 'errors' => $validator->errors()], 422);
        }

        $call = CallSession::with(['caller', 'receiver'])
            ->when($request->call_id, fn($q) => $q->where('id', $request->call_id))
            ->when($request->channel_name, fn($q) => $q->where('channel_name', $request->channel_name))
            ->first();

        if (!$call) {
            return response()->json(['status' => false, 'message' => 'Call session not found.'], 404);
        }

        if ($call->status === 'ended') {
            return response()->json([
                'status' => true,
                'message' => 'Call has already ended.',
                'data' => [
                    'call_id' => $call->id,
                    'duration_seconds' => $call->duration_seconds,
                    'coins_deducted' => $call->coins_deducted,
                    'caller_balance' => $call->caller_balance_after,
                ],
            ], 200);
        }

        $endedAt = now();
        $startedAt = $call->started_at ?: $call->created_at;

        // Determine duration in seconds
        $durationSeconds = $request->filled('duration_seconds')
            ? (int) $request->input('duration_seconds')
            : max(0, $endedAt->diffInSeconds($startedAt));

        // If call was connected and had duration > 0
        $ratePerMinute = $call->rate_per_minute ?: self::CALL_RATE_PER_MINUTE;
        $coinsToDeduct = 0;

        if ($durationSeconds > 0) {
            // E.g. 1 second to 60 seconds = 1 minute (100 coins), 61 to 120 = 2 minutes (200 coins)
            $billableMinutes = (int) ceil($durationSeconds / 60);
            $coinsToDeduct = $billableMinutes * $ratePerMinute;
        }

        DB::beginTransaction();
        try {
            $caller = $call->caller;
            if ($caller && $coinsToDeduct > 0) {
                // Deduct up to current balance
                $actualDeduct = min($caller->coins, $coinsToDeduct);
                $caller->deductCoins(
                    $actualDeduct,
                    'video_call_spent',
                    "Video call with {$call->receiver?->display_name} ({$durationSeconds}s / " . ceil($durationSeconds / 60) . " min)",
                    "call_#{$call->id}"
                );
                $call->coins_deducted = $actualDeduct;
                $call->caller_balance_after = $caller->coins;
            } else {
                $call->coins_deducted = 0;
                $call->caller_balance_after = $caller ? $caller->coins : 0;
            }

            $call->status = 'ended';
            $call->ended_at = $endedAt;
            $call->duration_seconds = $durationSeconds;
            $call->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Call ended successfully.',
                'data' => [
                    'call_id' => $call->id,
                    'duration_seconds' => $durationSeconds,
                    'duration_formatted' => sprintf('%02d:%02d', floor($durationSeconds / 60), $durationSeconds % 60),
                    'rate_per_minute' => $ratePerMinute,
                    'coins_deducted' => $call->coins_deducted,
                    'caller_remaining_coins' => (int) $call->caller_balance_after,
                ],
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to finalize call: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Real-time heart-beat deduction during active video call (deducts 1 minute worth of coins).
     * POST /api/call/deduct-interval
     */
    public function deductInterval(Request $request): JsonResponse
    {
        $caller = $this->resolveUser($request);

        if (!$caller) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'call_id' => 'required',
            'coins' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => 'Validation error.', 'errors' => $validator->errors()], 422);
        }

        $call = CallSession::findOrFail($request->call_id);
        $rate = $request->input('coins') ?: ($call->rate_per_minute ?: self::CALL_RATE_PER_MINUTE);

        if ($caller->coins < $rate) {
            return response()->json([
                'status' => false,
                'message' => 'Insufficient coins to continue call.',
                'current_coins' => (int) $caller->coins,
                'should_terminate_call' => true,
            ], 402);
        }

        $caller->deductCoins(
            $rate,
            'video_call_spent',
            "In-call pulse deduction for call #{$call->id}",
            "call_pulse_#{$call->id}"
        );

        $call->increment('coins_deducted', $rate);
        $call->caller_balance_after = $caller->coins;
        $call->save();

        return response()->json([
            'status' => true,
            'message' => "Deducted {$rate} coins for ongoing call.",
            'data' => [
                'current_coins' => (int) $caller->coins,
                'coins_deducted' => $rate,
                'total_call_coins_deducted' => $call->coins_deducted,
                'can_continue' => $caller->coins >= $rate,
            ],
        ], 200);
    }

    /**
     * Get user's call history.
     * GET /api/call/history
     */
    public function history(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $calls = CallSession::with(['caller', 'receiver'])
            ->where(function ($q) use ($user) {
                $q->where('caller_id', $user->id)
                  ->orWhere('receiver_id', $user->id);
            })
            ->latest()
            ->paginate(20);

        return response()->json([
            'status' => true,
            'message' => 'Call history retrieved successfully.',
            'data' => $calls->items(),
            'pagination' => [
                'current_page' => $calls->currentPage(),
                'last_page' => $calls->lastPage(),
                'total' => $calls->total(),
            ],
        ], 200);
    }
}
