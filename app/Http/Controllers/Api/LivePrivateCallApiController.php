<?php

namespace App\Http\Controllers\Api;

use App\Events\GlobalTopGiftBannerEvent;
use App\Events\IncomingPrivateCallEvent;
use App\Events\PKBattleEndedEvent;
use App\Events\PKBattleInviteEvent;
use App\Events\PKBattleScoreEvent;
use App\Events\PKBattleStartedEvent;
use App\Events\PrivateCallAcceptedEvent;
use App\Events\PrivateCallEndedEvent;
use App\Events\PrivateCallRejectedEvent;
use App\Http\Controllers\Controller;
use App\Models\CallSession;
use App\Models\CallSetting;
use App\Models\Gift;
use App\Models\LiveStream;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class LivePrivateCallApiController extends Controller
{
    /**
     * Resolve authenticated or token user.
     */
    protected function resolveUser(Request $request): ?User
    {
        try {
            if ($user = $request->user('sanctum')) {
                return $user;
            }
            if ($user = $request->user()) {
                return $user;
            }
            if (Auth::guard('sanctum')->check()) {
                return Auth::guard('sanctum')->user();
            }
        } catch (\Throwable $e) {}

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

        $userId = $request->header('X-User-Id') ?? $request->input('user_id') ?? $request->input('caller_id');
        if ($userId) {
            return User::find($userId) ?? User::where('account_id', $userId)->first();
        }

        return null;
    }

    /**
     * Generate Fast LiveKit Token
     */
    protected function generateLiveKitToken(string $roomName, User $user, bool $canPublish = true): string
    {
        $apiKey = config('services.livekit.api_key', env('LIVEKIT_API_KEY', 'APIVbeXzKatSo3u'));
        $apiSecret = config('services.livekit.api_secret', env('LIVEKIT_API_SECRET', 'thzlQ2sYGQQIxBPQMkjO9Rres6xuuMsqweZdT61XNsK'));

        if (class_exists('\Agence104\LiveKit\AccessToken')) {
            try {
                $token = new \Agence104\LiveKit\AccessToken($apiKey, $apiSecret);
                $grant = new \Agence104\LiveKit\VideoGrant();
                $grant->setRoomJoin(true)
                      ->setRoomName($roomName)
                      ->setCanPublish($canPublish)
                      ->setCanSubscribe(true)
                      ->setCanPublishData(true);

                $tokenOptions = (new \Agence104\LiveKit\AccessTokenOptions())
                    ->setIdentity((string) $user->id)
                    ->setName($user->display_name ?? $user->name ?? "User_{$user->id}")
                    ->setTtl(86400);

                $token->init($tokenOptions);
                $token->setGrant($grant);
                return $token->toJwt();
            } catch (\Throwable $e) {
                Log::error("LiveKit token generation failed: " . $e->getMessage());
            }
        }

        return base64_encode(json_encode(['room' => $roomName, 'user' => $user->id, 'time' => time()]));
    }

    // =========================================================================
    // 📌 MODULE 1: 1-on-1 Private Paid Video Call during Live Stream
    // =========================================================================

    /**
     * 1. Initiate 1-on-1 Private Call to Live Stream Host.
     * POST /api/live/private-call/initiate
     * POST /api/live/private-call/start
     */
    public function initiateCall(Request $request): JsonResponse
    {
        $caller = $this->resolveUser($request);
        if (!$caller) {
            return response()->json(['success' => false, 'status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $hostId = $request->input('host_id') ?? $request->input('receiver_id') ?? $request->input('target_user_id');
        $liveStreamId = $request->input('live_stream_id') ?? $request->input('room_id') ?? $request->input('stream_id');
        $callType = $request->input('call_type', 'video');

        $host = User::find($hostId) ?? User::where('account_id', $hostId)->first();
        if (!$host) {
            return response()->json(['success' => false, 'status' => false, 'message' => 'Host not found.'], 404);
        }

        if ($caller->id === $host->id) {
            return response()->json(['success' => false, 'status' => false, 'message' => 'Cannot initiate private call to yourself.'], 422);
        }

        // 1. Validation & Balance Check (Check rate per minute)
        $config = CallSetting::getAllConfig();
        $dbRate = null;
        if (Schema::hasTable('settings')) {
            $dbRate = DB::table('settings')->where('key', 'video_call_rate_per_min')->value('value');
        }
        $ratePerMinute = (int) ($dbRate ?: ($host->video_call_rate ?: ($config['video_call_rate_per_minute'] ?? 100)));

        if ($caller->coins < $ratePerMinute) {
            return response()->json([
                'success'        => false,
                'status'         => false,
                'code'           => 'INSUFFICIENT_COINS',
                'message'        => 'পর্যাপ্ত কয়েন নেই! কমপক্ষে ১ মিনিটের কয়েন প্রয়োজন।',
                'required_coins' => $ratePerMinute,
                'current_coins'  => (int) $caller->coins,
                'redirect_url'   => '/deposit',
            ], 400);
        }

        // 2. Generate Dedicated Private LiveKit Room Name & Token
        $timestamp = time();
        $privateRoomName = "private_call_{$caller->id}_{$host->id}_{$timestamp}";
        $livekitToken = $this->generateLiveKitToken($privateRoomName, $caller, true);
        $livekitWsUrl = config('services.livekit.url', env('LIVEKIT_WS_URL', 'wss://chinchins.live/livekit'));

        // 3. Create CallSession in Database
        $call = CallSession::create([
            'caller_id'       => $caller->id,
            'receiver_id'     => $host->id,
            'channel_name'    => $privateRoomName,
            'call_type'       => $callType,
            'status'          => 'initiated',
            'rate_per_minute' => $ratePerMinute,
            'is_random_match' => false,
        ]);

        // 4. Real-Time Host Alert (Broadcast ONLY to host's private channel so live viewers are not alerted)
        $callAlertPayload = [
            'call_id'         => $call->id,
            'caller_id'       => $caller->id,
            'caller_account'  => $caller->account_id,
            'caller_name'     => $caller->display_name ?? $caller->name,
            'caller_avatar'   => $caller->avatar_url,
            'caller_level'    => $caller->level ?: 'Lv1',
            'room_name'       => $privateRoomName,
            'rate'            => $ratePerMinute,
            'rate_per_minute' => $ratePerMinute,
            'live_stream_id'  => $liveStreamId,
            'caller_coins'    => (int) $caller->coins,
            'call_type'       => $callType,
            'created_at'      => now()->toIso8601String(),
        ];

        try {
            broadcast(new IncomingPrivateCallEvent($host->id, $callAlertPayload));
        } catch (\Throwable $e) {
            Log::error("IncomingPrivateCallEvent broadcast error: " . $e->getMessage());
        }

        // 📲 High-Priority VoIP FCM Data Push to Host (Wakes host device during live stream)
        try {
            dispatch(new \App\Jobs\SendCallNotificationJob($call->id, $caller->id, $host->id));
        } catch (\Throwable $e) {
            Log::error("LivePrivateCall VoIP push notification error: " . $e->getMessage());
        }

        return response()->json([
            'success'         => true,
            'status'          => true,
            'message'         => 'Private call initiated. Host has been alerted privately.',
            'call_id'         => $call->id,
            'room_name'       => $privateRoomName,
            'channel_name'    => $privateRoomName,
            'token'           => $livekitToken,
            'livekit_token'   => $livekitToken,
            'livekit_url'     => $livekitWsUrl,
            'rate_per_minute' => $ratePerMinute,
            'caller_coins'    => (int) $caller->coins,
            'host'            => [
                'id'           => $host->id,
                'account_id'   => $host->account_id,
                'display_name' => $host->display_name ?? $host->name,
                'avatar_url'   => $host->avatar_url,
            ],
        ], 200);
    }

    /**
     * 2. Host Accepts Private Call.
     * POST /api/live/private-call/accept
     */
    public function acceptCall(Request $request): JsonResponse
    {
        $host = $this->resolveUser($request);
        if (!$host) {
            return response()->json(['success' => false, 'status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $callId = $request->input('call_id');
        $call = CallSession::find($callId);
        if (!$call) {
            return response()->json(['success' => false, 'status' => false, 'message' => 'Call session not found.'], 404);
        }

        $call->update([
            'status'     => 'connected',
            'started_at' => now(),
        ]);

        // Generate LiveKit token for Host
        $hostToken = $this->generateLiveKitToken($call->channel_name, $host, true);
        $livekitWsUrl = config('services.livekit.url', env('LIVEKIT_WS_URL', 'wss://chinchins.live/livekit'));

        // Notify caller that host accepted
        try {
            broadcast(new PrivateCallAcceptedEvent($call->caller_id, [
                'call_id'         => $call->id,
                'room_name'       => $call->channel_name,
                'host_id'         => $host->id,
                'host_name'       => $host->display_name ?? $host->name,
                'host_avatar'     => $host->avatar_url,
                'livekit_url'     => $livekitWsUrl,
                'status'          => 'connected',
                'connected_at'    => now()->toIso8601String(),
            ]));
        } catch (\Throwable $e) {
            Log::error("PrivateCallAcceptedEvent error: " . $e->getMessage());
        }

        return response()->json([
            'success'       => true,
            'status'        => true,
            'message'       => 'Call accepted. Camera stream paused on public live stream.',
            'call_id'       => $call->id,
            'room_name'     => $call->channel_name,
            'token'         => $hostToken,
            'livekit_token' => $hostToken,
            'livekit_url'   => $livekitWsUrl,
        ], 200);
    }

    /**
     * 3. Host Rejects Private Call.
     * POST /api/live/private-call/reject
     */
    public function rejectCall(Request $request): JsonResponse
    {
        $host = $this->resolveUser($request);
        $callId = $request->input('call_id');
        $reason = $request->input('reason', 'host_declined');

        $call = CallSession::find($callId);
        if (!$call) {
            return response()->json(['success' => false, 'status' => false, 'message' => 'Call session not found.'], 404);
        }

        $call->update([
            'status'   => 'rejected',
            'ended_at' => now(),
        ]);

        try {
            broadcast(new PrivateCallRejectedEvent($call->caller_id, [
                'call_id' => $call->id,
                'host_id' => $host?->id ?? $call->receiver_id,
                'reason'  => $reason,
            ]));
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'status'  => true,
            'message' => 'Call rejected.',
            'call_id' => $call->id,
        ], 200);
    }

    /**
     * 4. Per-Minute Billing Pulse & Revenue Sharing.
     * Executes every 60 seconds while call is connected.
     * POST /api/live/private-call/billing-pulse
     * POST /api/live/private-call/deduct
     */
    public function billingPulse(Request $request): JsonResponse
    {
        $callId = $request->input('call_id');
        $call = CallSession::find($callId);

        if (!$call) {
            return response()->json(['success' => false, 'status' => false, 'message' => 'Call session not found.'], 404);
        }

        $caller = User::find($call->caller_id);
        $host = User::find($call->receiver_id);

        if (!$caller || !$host) {
            return response()->json(['success' => false, 'status' => false, 'message' => 'Participants not found.'], 404);
        }

        $ratePerMinute = (int) ($call->rate_per_minute ?: 100);

        // Check if caller has enough coins for the upcoming minute
        if ($caller->coins < $ratePerMinute) {
            $call->update([
                'status'   => 'ended',
                'ended_at' => now(),
            ]);

            try {
                broadcast(new PrivateCallEndedEvent($call->caller_id, $call->receiver_id, [
                    'call_id'           => $call->id,
                    'reason'            => 'insufficient_coins',
                    'duration_seconds'  => (int) $call->duration_seconds,
                    'coins_deducted'    => (int) $call->coins_deducted,
                    'host_earned_coins' => (int) $call->host_earned_coins,
                    'ended_at'          => now()->toIso8601String(),
                ]));
            } catch (\Throwable $e) {}

            return response()->json([
                'success'        => false,
                'status'         => false,
                'code'           => 'INSUFFICIENT_COINS',
                'action'         => 'TERMINATE_CALL',
                'should_end'     => true,
                'message'        => 'ইউজারের কয়েন শেষ হয়ে গেছে! কল বন্ধ করা হয়েছে।',
                'current_coins'  => (int) $caller->coins,
                'required_coins' => $ratePerMinute,
            ], 402);
        }

        // DB Transaction: Deduct caller coins, split between Admin & Host
        $billingResult = DB::transaction(function () use ($call, $caller, $host, $ratePerMinute) {
            $adminSharePercent = 50;
            if (Schema::hasTable('settings')) {
                $dbCommission = DB::table('settings')->where('key', 'admin_call_commission')->value('value');
                if ($dbCommission !== null) {
                    $adminSharePercent = (float) $dbCommission;
                }
            }

            $adminEarn = ($ratePerMinute * $adminSharePercent) / 100;
            $hostEarn  = $ratePerMinute - $adminEarn;

            DB::table('users')->where('id', $caller->id)->decrement('coins', $ratePerMinute);
            DB::table('users')->where('id', $host->id)->increment('received_coins', $hostEarn);

            if (Schema::hasTable('admin_revenues')) {
                DB::table('admin_revenues')->insert([
                    'caller_id'          => $caller->id,
                    'host_id'            => $host->id,
                    'amount'             => $adminEarn,
                    'source'             => 'video_call',
                    'commission_percent' => $adminSharePercent,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
            }

            $call->increment('duration_seconds', 60);
            $call->increment('coins_deducted', $ratePerMinute);
            $call->increment('host_earned_coins', $hostEarn);
            $call->increment('admin_revenue_coins', $adminEarn);

            $freshCaller = User::find($caller->id);
            return [
                'caller_id'         => $caller->id,
                'host_id'           => $host->id,
                'coins_deducted'    => $ratePerMinute,
                'host_earned_coins' => (int) $hostEarn,
                'admin_earn'        => (int) $adminEarn,
                'caller_coins'      => (int) ($freshCaller?->coins ?? 0),
                'total_duration'    => (int) $call->duration_seconds,
            ];
        });

        return response()->json([
            'success' => true,
            'status'  => true,
            'message' => '1-minute billing pulse processed successfully.',
            'data'    => $billingResult,
        ], 200);
    }

    /**
     * 5. End Private Call & Resume Live Stream Video.
     * POST /api/live/private-call/end
     */
    public function endCall(Request $request): JsonResponse
    {
        $callId = $request->input('call_id');
        $call = CallSession::find($callId);

        if (!$call) {
            return response()->json(['success' => false, 'status' => false, 'message' => 'Call session not found.'], 404);
        }

        $duration = (int) ($request->input('duration_seconds') ?? $call->duration_seconds ?? 0);

        $call->update([
            'status'           => 'ended',
            'ended_at'         => now(),
            'duration_seconds' => $duration,
        ]);

        $summary = [
            'call_id'           => $call->id,
            'duration_seconds'  => $duration,
            'coins_deducted'    => (int) $call->coins_deducted,
            'host_earned_coins' => (int) $call->host_earned_coins,
            'ended_at'          => now()->toIso8601String(),
        ];

        try {
            broadcast(new PrivateCallEndedEvent($call->caller_id, $call->receiver_id, $summary));
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'status'  => true,
            'message' => 'Private call ended. Live stream video resumed.',
            'data'    => $summary,
        ], 200);
    }

    // =========================================================================
    // 📌 MODULE 2: Global Top Sliding Gift/Coin Banner Broadcast
    // =========================================================================

    /**
     * Custom Global Top Gift Banner Trigger.
     * POST /api/live/gift-banner/broadcast
     */
    public function broadcastGiftBanner(Request $request): JsonResponse
    {
        $sender = $this->resolveUser($request);
        $liveRoomName = $request->input('room_name') ?? $request->input('stream_id') ?? $request->input('live_room_id');
        $receiverId = $request->input('receiver_id') ?? $request->input('host_id');
        $amountOrCoins = (int) ($request->input('amount') ?? $request->input('coins') ?? 100);
        $giftId = $request->input('gift_id');

        $gift = $giftId ? Gift::find($giftId) : null;
        $receiver = $receiverId ? User::find($receiverId) : null;

        $bannerData = [
            'sender_id'       => $sender?->id,
            'sender_name'     => $sender ? ($sender->display_name ?? $sender->name) : ($request->input('sender_name', 'User')),
            'sender_avatar'   => $sender?->avatar_url ?? $request->input('sender_avatar'),
            'receiver_id'     => $receiver?->id ?? $receiverId,
            'receiver_name'   => $receiver ? ($receiver->display_name ?? $receiver->name) : ($request->input('receiver_name', 'Host')),
            'receiver_avatar' => $receiver?->avatar_url ?? $request->input('receiver_avatar'),
            'gift_id'         => $gift?->id,
            'gift_name'       => $gift?->name ?? $request->input('gift_name', 'Coins'),
            'gift_icon'       => $gift?->icon_url ?? $request->input('gift_icon', asset('assets/coin.png')),
            'amount'          => $amountOrCoins,
            'quantity'        => (int) ($request->input('quantity', 1)),
            'banner_duration' => 4,
        ];

        try {
            broadcast(new GlobalTopGiftBannerEvent($liveRoomName, $bannerData))->toOthers();
        } catch (\Throwable $e) {
            Log::error("GlobalTopGiftBannerEvent broadcast error: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'status'  => true,
            'message' => 'Global top gift banner broadcast dispatched.',
            'data'    => $bannerData,
        ], 200);
    }

    // =========================================================================
    // 📌 MODULE 3: PK Battle & Split Screen Management
    // =========================================================================

    /**
     * Invite another host for PK Battle.
     * POST /api/live/pk/invite
     */
    public function invitePK(Request $request): JsonResponse
    {
        $host = $this->resolveUser($request);
        $targetHostId = $request->input('target_host_id');
        $durationSeconds = (int) ($request->input('duration_seconds', 180)); // 3 mins default
        $roomId = $request->input('room_id') ?? $request->input('stream_id');

        $targetHost = User::find($targetHostId);
        if (!$targetHost) {
            return response()->json(['success' => false, 'status' => false, 'message' => 'Target host not found.'], 404);
        }

        $pkId = 'pk_' . time() . '_' . rand(100, 999);
        $pkPayload = [
            'pk_id'            => $pkId,
            'inviter_id'       => $host?->id,
            'inviter_name'     => $host?->display_name ?? $host?->name,
            'inviter_avatar'   => $host?->avatar_url,
            'inviter_room_id'  => $roomId,
            'target_host_id'   => $targetHost->id,
            'duration_seconds' => $durationSeconds,
        ];

        try {
            broadcast(new PKBattleInviteEvent($targetHost->id, $pkPayload));
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'status'  => true,
            'message' => 'PK battle invitation sent to host.',
            'data'    => $pkPayload,
        ], 200);
    }

    /**
     * Accept or Reject PK Battle.
     * POST /api/live/pk/respond or POST /api/live/pk/accept / reject
     */
    public function respondPK(Request $request): JsonResponse
    {
        $host = $this->resolveUser($request);
        $pkId = $request->input('pk_id');
        $action = $request->input('action', 'accept'); // 'accept' or 'reject'
        $inviterRoomId = $request->input('inviter_room_id');
        $targetRoomId = $request->input('target_room_id') ?? $request->input('room_id');

        $roomIds = array_filter([$inviterRoomId, $targetRoomId]);

        if ($action === 'accept') {
            $startPayload = [
                'pk_id'            => $pkId,
                'status'           => 'active',
                'duration_seconds' => (int) ($request->input('duration_seconds', 180)),
                'host1_id'         => (int) $request->input('inviter_id'),
                'host1_name'       => $request->input('inviter_name'),
                'host1_avatar'     => $request->input('inviter_avatar'),
                'host1_score'      => 0,
                'host2_id'         => $host?->id,
                'host2_name'       => $host?->display_name ?? $host?->name,
                'host2_avatar'     => $host?->avatar_url,
                'host2_score'      => 0,
                'started_at'       => now()->toIso8601String(),
            ];

            try {
                broadcast(new PKBattleStartedEvent($roomIds, $startPayload));
            } catch (\Throwable $e) {}

            return response()->json([
                'success' => true,
                'status'  => true,
                'message' => 'PK battle started with split screen layout.',
                'data'    => $startPayload,
            ], 200);
        }

        return response()->json([
            'success' => true,
            'status'  => true,
            'message' => 'PK battle invitation declined.',
            'data'    => ['pk_id' => $pkId, 'status' => 'rejected'],
        ], 200);
    }

    /**
     * Update PK Battle Live Scores.
     * POST /api/live/pk/score
     */
    public function updatePKScore(Request $request): JsonResponse
    {
        $pkId = $request->input('pk_id');
        $host1Score = (int) $request->input('host1_score', 0);
        $host2Score = (int) $request->input('host2_score', 0);
        $roomIds = (array) ($request->input('room_ids') ?? [$request->input('room_id')]);

        $scorePayload = [
            'pk_id'       => $pkId,
            'host1_score' => $host1Score,
            'host2_score' => $host2Score,
            'latest_gift' => $request->input('latest_gift'),
        ];

        try {
            broadcast(new PKBattleScoreEvent($roomIds, $scorePayload));
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'status'  => true,
            'message' => 'PK scores updated.',
            'data'    => $scorePayload,
        ], 200);
    }

    /**
     * End PK Battle.
     * POST /api/live/pk/end
     */
    public function endPK(Request $request): JsonResponse
    {
        $pkId = $request->input('pk_id');
        $winnerHostId = $request->input('winner_host_id');
        $host1Score = (int) $request->input('host1_score', 0);
        $host2Score = (int) $request->input('host2_score', 0);
        $roomIds = (array) ($request->input('room_ids') ?? [$request->input('room_id')]);

        $resultPayload = [
            'pk_id'          => $pkId,
            'status'         => 'ended',
            'winner_host_id' => $winnerHostId,
            'is_draw'        => ($host1Score === $host2Score),
            'host1_score'    => $host1Score,
            'host2_score'    => $host2Score,
            'ended_at'       => now()->toIso8601String(),
        ];

        try {
            broadcast(new PKBattleEndedEvent($roomIds, $resultPayload));
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'status'  => true,
            'message' => 'PK battle ended and winner announced.',
            'data'    => $resultPayload,
        ], 200);
    }
}
