<?php

namespace App\Http\Controllers\Api;

use App\Events\LiveGiftSent;
use App\Events\LiveGuestKicked;
use App\Events\LiveJoinRequested;
use App\Events\LiveJoinResponded;
use App\Events\LiveMessageSent;
use App\Events\LiveStreamEnded;
use App\Http\Controllers\Controller;
use App\Models\Gift;
use App\Models\LiveJoinRequest;
use App\Models\LiveMessage;
use App\Models\LiveParticipant;
use App\Models\LiveStream;
use App\Models\StreamingSetting;
use App\Models\User;
use App\Services\Calling\CallingManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class LiveStreamApiController extends Controller
{
    protected CallingManager $callingManager;

    public function __construct(CallingManager $callingManager)
    {
        $this->callingManager = $callingManager;
    }

    /**
     * Resolve authenticated or requested user.
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

        $headerUserId = $request->header('X-User-Id') ?? $request->header('User-Id') ?? $request->header('userId');
        if ($headerUserId) {
            $u = User::find($headerUserId) ?? User::where('account_id', $headerUserId)->first();
            if ($u) return $u;
        }

        $paramId = $request->input('user_id') ?? $request->input('userId') ?? $request->input('uid') ?? $request->input('host_id');
        if ($paramId) {
            $u = User::find($paramId) ?? User::where('account_id', $paramId)->first();
            if ($u) return $u;
        }

        return null;
    }

    /**
     * 1. Get Currently Active Live Streams List.
     * Only returns broadcasters with status = 'live'. Returns [] if none.
     * GET /api/lives/active (or GET /api/live/active, GET /api/live/list)
     */
    public function getActiveLives(Request $request): JsonResponse
    {
        $page = (int) $request->input('page', 1);
        $perPage = min((int) $request->input('per_page', 20), 50);

        $streams = LiveStream::with([
                'host:id,account_id,name,display_name,avatar,gender,country,city,level',
                'guests.user:id,account_id,name,display_name,avatar'
            ])
            ->where('status', 'live')
            ->orderByDesc('viewer_count')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);

        $formatted = collect($streams->items())->map(function ($s) {
            $host = $s->host;
            return [
                'id'                    => $s->id,
                'channel_name'          => $s->channel_name,
                'title'                 => $s->title ?: (($host?->display_name ?? 'Host') . "'s Live Broadcast"),
                'cover_image_url'       => $s->cover_image_url,
                'status'                => $s->status,
                'viewer_count'          => (int) $s->viewer_count,
                'total_diamonds_earned' => (int) $s->total_diamonds_earned,
                'started_at'            => $s->started_at ? $s->started_at->toIso8601String() : null,
                'host'                  => $host ? [
                    'id'           => $host->id,
                    'account_id'   => $host->account_id,
                    'display_name' => $host->display_name ?? $host->name ?? 'Host',
                    'avatar_url'   => $host->avatar_url,
                    'gender'       => $host->gender ?: 'female',
                    'country'      => $host->country ?: 'Bangladesh',
                    'level'        => $host->level ?: 'Lv1',
                ] : null,
                'active_guests'         => $s->guests->map(fn($g) => [
                    'user_id'      => $g->user_id,
                    'account_id'   => $g->user?->account_id,
                    'display_name' => $g->user?->display_name ?? 'Guest',
                    'avatar_url'   => $g->user?->avatar_url,
                ])->values(),
            ];
        });

        return response()->json([
            'status'     => true,
            'success'    => true,
            'message'    => 'Active live streams retrieved successfully.',
            'data'       => $formatted,
            'pagination' => [
                'current_page' => $streams->currentPage(),
                'last_page'    => $streams->lastPage(),
                'total'        => $streams->total(),
            ],
        ], 200);
    }

    /**
     * 2. Host Start Live Broadcasting.
     * Generates unique channel_name and Agora/WebRTC token with broadcaster role.
     * POST /api/live/start
     */
    public function startLive(Request $request): JsonResponse
    {
        $host = $this->resolveUser($request);
        if (!$host) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated. Pass Authorization Bearer token or user_id.',
            ], 401);
        }

        $title = trim($request->input('title') ?? "Live with {$host->display_name}");
        $channelName = 'live_' . $host->id . '_' . time() . '_' . Str::random(4);

        // Upload custom cover image if attached
        $coverImageUrl = null;
        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $uploadDir = public_path('uploads/live_streaming');
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $filename = 'cover_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $coverImageUrl = url('uploads/live_streaming/' . $filename);
        } else {
            $coverImageUrl = $request->input('cover_image_url') ?? $host->avatar_url;
        }

        // Close any previously hanging live stream for this host
        LiveStream::where('host_id', $host->id)->where('status', 'live')->update([
            'status'   => 'ended',
            'ended_at' => now(),
        ]);

        // Generate RTC token for Broadcaster / Host
        $sessionTokenData = $this->callingManager->initializeSession(
            $host,
            $channelName,
            'live',
            'publisher',
            ['uid' => $host->id]
        );

        $agoraToken = $sessionTokenData['agora']['token'] ?? $sessionTokenData['data']['token'] ?? null;

        $liveStream = LiveStream::create([
            'host_id'               => $host->id,
            'channel_name'          => $channelName,
            'title'                 => $title,
            'cover_image'           => $coverImageUrl,
            'status'                => 'live',
            'viewer_count'          => 1,
            'total_diamonds_earned' => 0,
            'agora_token'           => $agoraToken,
            'started_at'            => now(),
        ]);

        // Add Host as participant
        LiveParticipant::create([
            'live_stream_id' => $liveStream->id,
            'user_id'        => $host->id,
            'role'           => 'host',
            'video_enabled'  => true,
            'joined_at'      => now(),
        ]);

        // Update Host status to in_live
        $host->update([
            'is_online'      => true,
            'online_status'  => 'in_live',
            'current_status' => 'in_live',
            'last_seen_at'   => now(),
        ]);

        return response()->json([
            'status'  => true,
            'success' => true,
            'message' => 'Live stream broadcast started successfully!',
            'data'    => [
                'live_stream_id'  => $liveStream->id,
                'channel_name'    => $channelName,
                'title'           => $title,
                'cover_image_url' => $liveStream->cover_image_url,
                'status'          => 'live',
                'role'            => 'host',
                'session'         => $sessionTokenData,
                'host'            => [
                    'id'           => $host->id,
                    'account_id'   => $host->account_id,
                    'display_name' => $host->display_name,
                    'avatar_url'   => $host->avatar_url,
                ],
            ],
        ], 200);
    }

    /**
     * 3. Host End Live Stream.
     * POST /api/live/end
     */
    public function endLive(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        $streamId = $request->input('live_stream_id') ?? $request->input('id') ?? $request->input('channel_name');

        $stream = LiveStream::where('id', $streamId)
            ->orWhere('channel_name', $streamId)
            ->first();

        if (!$stream) {
            return response()->json([
                'status'  => false,
                'message' => 'Live stream not found.',
            ], 404);
        }

        if ($user && $stream->host_id !== $user->id && !$user->isSuperAdmin()) {
            return response()->json([
                'status'  => false,
                'message' => 'Only the broadcast host can end this live stream.',
            ], 403);
        }

        $stream->update([
            'status'   => 'ended',
            'ended_at' => now(),
        ]);

        // Update all participants left_at
        LiveParticipant::where('live_stream_id', $stream->id)->whereNull('left_at')->update(['left_at' => now()]);

        // Reset host status back to available
        if ($stream->host) {
            $stream->host->update([
                'online_status'  => 'online',
                'current_status' => 'available',
                'is_busy'        => false,
            ]);
        }

        $summary = [
            'live_stream_id'        => $stream->id,
            'channel_name'          => $stream->channel_name,
            'duration_seconds'      => $stream->ended_at->diffInSeconds($stream->started_at),
            'total_diamonds_earned' => (int) $stream->total_diamonds_earned,
            'peak_viewers'          => (int) $stream->viewer_count,
        ];

        // Broadcast event to all connected viewers and guests
        try {
            event(new LiveStreamEnded($stream->id, $summary));
        } catch (\Throwable $e) {}

        return response()->json([
            'status'  => true,
            'success' => true,
            'message' => 'Live stream ended successfully.',
            'data'    => $summary,
        ], 200);
    }

    /**
     * 4. Audience / Viewer Join Live Stream.
     * POST /api/live/join or POST /api/live/{id}/join
     */
    public function joinLive(Request $request, $id = null): JsonResponse
    {
        $viewer = $this->resolveUser($request);
        $streamId = $id ?? $request->input('live_stream_id') ?? $request->input('id') ?? $request->input('channel_name');

        $stream = LiveStream::with('host')
            ->where('id', $streamId)
            ->orWhere('channel_name', $streamId)
            ->first();

        if (!$stream || $stream->status !== 'live') {
            return response()->json([
                'status'  => false,
                'message' => 'Live stream has ended or is not found.',
            ], 404);
        }

        if ($viewer) {
            LiveParticipant::updateOrCreate(
                ['live_stream_id' => $stream->id, 'user_id' => $viewer->id, 'role' => 'viewer'],
                ['joined_at' => now(), 'left_at' => null]
            );

            // Increment viewer count
            $stream->increment('viewer_count');
        }

        // Generate audience token
        $sessionTokenData = $this->callingManager->initializeSession(
            $viewer,
            $stream->channel_name,
            'live',
            'subscriber',
            ['uid' => $viewer?->id ?? rand(100000, 999999)]
        );

        return response()->json([
            'status'  => true,
            'success' => true,
            'message' => 'Joined live stream successfully.',
            'data'    => [
                'live_stream_id'  => $stream->id,
                'channel_name'    => $stream->channel_name,
                'title'           => $stream->title,
                'cover_image_url' => $stream->cover_image_url,
                'viewer_count'    => (int) $stream->viewer_count,
                'role'            => 'audience',
                'session'         => $sessionTokenData,
                'host'            => [
                    'id'           => $stream->host?->id,
                    'account_id'   => $stream->host?->account_id,
                    'display_name' => $stream->host?->display_name,
                    'avatar_url'   => $stream->host?->avatar_url,
                    'gender'       => $stream->host?->gender ?: 'female',
                ],
            ],
        ], 200);
    }

    /**
     * 5. Leave Live Stream.
     * POST /api/live/leave or POST /api/live/{id}/leave
     */
    public function leaveLive(Request $request, $id = null): JsonResponse
    {
        $user = $this->resolveUser($request);
        $streamId = $id ?? $request->input('live_stream_id') ?? $request->input('id');

        $stream = LiveStream::where('id', $streamId)->orWhere('channel_name', $streamId)->first();

        if ($stream && $user) {
            LiveParticipant::where('live_stream_id', $stream->id)
                ->where('user_id', $user->id)
                ->update(['left_at' => now()]);

            if ($stream->viewer_count > 1) {
                $stream->decrement('viewer_count');
            }
        }

        return response()->json([
            'status'  => true,
            'success' => true,
            'message' => 'Left live stream successfully.',
        ], 200);
    }

    /**
     * 6. Send Public Chat Message in Live Stream.
     * Broadcasts instantaneously to presence-live.{live_id}.
     * POST /api/live/message or POST /api/live/messages/send
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated user.',
            ], 401);
        }

        $streamId = $request->input('live_stream_id') ?? $request->input('id');
        $message = trim($request->input('message') ?? $request->input('text') ?? '');
        $type = $request->input('type', 'text');

        if (empty($message)) {
            return response()->json([
                'status'  => false,
                'message' => 'Message content is required.',
            ], 422);
        }

        $stream = LiveStream::where('id', $streamId)->orWhere('channel_name', $streamId)->first();
        if (!$stream || $stream->status !== 'live') {
            return response()->json([
                'status'  => false,
                'message' => 'Live stream is not active.',
            ], 404);
        }

        $senderName = $user->display_name ?? $user->name ?? 'User';
        $senderAvatar = $user->avatar_url;

        $msgRecord = LiveMessage::create([
            'live_stream_id' => $stream->id,
            'user_id'        => $user->id,
            'message'        => $message,
            'type'           => $type,
            'metadata'       => [
                'sender_name'   => $senderName,
                'sender_avatar' => $senderAvatar,
                'level'         => $user->level ?: 'Lv1',
                'frame_url'     => $user->avatar_frame_url,
            ],
        ]);

        $payload = [
            'id'             => $msgRecord->id,
            'live_stream_id' => $stream->id,
            'user_id'        => $user->id,
            'sender_name'    => $senderName,
            'sender_avatar'  => $senderAvatar,
            'level'          => $user->level ?: 'Lv1',
            'message'        => $message,
            'type'           => $type,
            'created_at'     => $msgRecord->created_at->toIso8601String(),
        ];

        // Broadcast to WebSocket presence-live.{id}
        try {
            event(new LiveMessageSent($stream->id, $payload));
        } catch (\Throwable $e) {}

        return response()->json([
            'status'  => true,
            'success' => true,
            'message' => 'Live message sent.',
            'data'    => $payload,
        ], 200);
    }

    /**
     * 7. Send Virtual Gift in Live Stream with 50/50 Revenue Split.
     * POST /api/live/gift or POST /api/live/send-gift
     */
    public function sendGift(Request $request): JsonResponse
    {
        $sender = $this->resolveUser($request);
        if (!$sender) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $streamId = $request->input('live_stream_id') ?? $request->input('id');
        $giftId = $request->input('gift_id');
        $quantity = max(1, (int) $request->input('quantity', 1));

        $stream = LiveStream::with('host')->where('id', $streamId)->orWhere('channel_name', $streamId)->first();
        if (!$stream || $stream->status !== 'live') {
            return response()->json(['status' => false, 'message' => 'Live stream is not active.'], 404);
        }

        $gift = Gift::find($giftId);
        if (!$gift) {
            return response()->json(['status' => false, 'message' => 'Gift item not found.'], 404);
        }

        $totalCost = ((int) $gift->coins) * $quantity;

        if ($sender->coins < $totalCost) {
            return response()->json([
                'status'         => false,
                'code'           => 'INSUFFICIENT_COINS',
                'message'        => "Insufficient coins. You need {$totalCost} coins, but have {$sender->coins} coins.",
                'user_coins'     => (int) $sender->coins,
                'required_coins' => $totalCost,
            ], 200);
        }

        $host = $stream->host;
        $hostEarned = (int) round($totalCost * 0.50); // 50% split

        DB::beginTransaction();
        try {
            $sender->deductCoins($totalCost, 'live_gift_spent', "Sent {$quantity}x {$gift->name} in Live Broadcast");
            if ($host) {
                $host->addCoins($hostEarned, 'live_gift_earned', "Received {$quantity}x {$gift->name} from {$sender->display_name} in Live Broadcast");
            }
            $stream->increment('total_diamonds_earned', $totalCost);

            $liveMsg = LiveMessage::create([
                'live_stream_id' => $stream->id,
                'user_id'        => $sender->id,
                'message'        => "Sent {$quantity}x {$gift->name}",
                'type'           => 'gift',
                'gift_id'        => $gift->id,
                'coin_amount'    => $totalCost,
                'metadata'       => [
                    'gift_name'     => $gift->name,
                    'gift_icon'     => $gift->icon_url,
                    'gift_svga'     => $gift->animation_url,
                    'quantity'      => $quantity,
                    'sender_name'   => $sender->display_name,
                    'sender_avatar' => $sender->avatar_url,
                ],
            ]);

            DB::commit();

            $giftPayload = [
                'id'             => $liveMsg->id,
                'live_stream_id' => $stream->id,
                'sender_id'      => $sender->id,
                'sender_name'    => $sender->display_name,
                'sender_avatar'  => $sender->avatar_url,
                'gift_id'        => $gift->id,
                'gift_name'      => $gift->name,
                'gift_icon'      => $gift->icon_url,
                'animation_url'  => $gift->animation_url,
                'quantity'       => $quantity,
                'total_coins'    => $totalCost,
                'created_at'     => $liveMsg->created_at->toIso8601String(),
            ];

            // Broadcast to live presence channel
            try {
                event(new LiveGiftSent($stream->id, $giftPayload));
            } catch (\Throwable $e) {}

            return response()->json([
                'status'     => true,
                'success'    => true,
                'message'    => "Gift sent successfully!",
                'user_coins' => (int) $sender->coins,
                'data'       => $giftPayload,
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Failed to send gift: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 8. Viewer Request to Co-Host / Join Video Grid.
     * POST /api/live/join-request or POST /api/live/request-join
     */
    public function requestJoin(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $streamId = $request->input('live_stream_id') ?? $request->input('id');
        $stream = LiveStream::with('host')->where('id', $streamId)->orWhere('channel_name', $streamId)->first();

        if (!$stream || $stream->status !== 'live') {
            return response()->json(['status' => false, 'message' => 'Live stream is not active.'], 404);
        }

        $joinReq = LiveJoinRequest::updateOrCreate(
            ['live_stream_id' => $stream->id, 'user_id' => $user->id],
            ['status' => 'pending']
        );

        $requestPayload = [
            'request_id'     => $joinReq->id,
            'live_stream_id' => $stream->id,
            'user'           => [
                'id'           => $user->id,
                'account_id'   => $user->account_id,
                'display_name' => $user->display_name,
                'avatar_url'   => $user->avatar_url,
                'gender'       => $user->gender ?: 'female',
                'level'        => $user->level ?: 'Lv1',
            ],
            'status'         => 'pending',
            'created_at'     => now()->toIso8601String(),
        ];

        // Broadcast to Host
        try {
            event(new LiveJoinRequested($stream->id, $stream->host_id, $requestPayload));
        } catch (\Throwable $e) {}

        return response()->json([
            'status'  => true,
            'success' => true,
            'message' => 'Co-host request sent to host.',
            'data'    => $requestPayload,
        ], 200);
    }

    /**
     * 9. Host Accept or Reject Co-Host Request.
     * POST /api/live/accept-request or POST /api/live/respond-request
     */
    public function respondJoinRequest(Request $request): JsonResponse
    {
        $host = $this->resolveUser($request);
        $requestId = $request->input('request_id');
        $action = strtolower($request->input('action', 'accept')); // 'accept', 'reject'

        $joinReq = LiveJoinRequest::with(['liveStream', 'user'])->find($requestId);
        if (!$joinReq) {
            return response()->json(['status' => false, 'message' => 'Join request not found.'], 404);
        }

        $stream = $joinReq->liveStream;
        if ($host && $stream->host_id !== $host->id && !$host->isSuperAdmin()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized.'], 403);
        }

        $guestUser = $joinReq->user;
        $guestToken = null;

        if ($action === 'accept') {
            $joinReq->update(['status' => 'accepted']);

            // Upgrade role to guest in participants table
            LiveParticipant::updateOrCreate(
                ['live_stream_id' => $stream->id, 'user_id' => $guestUser->id],
                ['role' => 'guest', 'joined_at' => now(), 'left_at' => null, 'video_enabled' => true]
            );

            // Generate Broadcaster Token for Guest
            $guestSession = $this->callingManager->initializeSession(
                $guestUser,
                $stream->channel_name,
                'live',
                'publisher',
                ['uid' => $guestUser->id]
            );
            $guestToken = $guestSession;
        } else {
            $joinReq->update(['status' => 'rejected']);
        }

        $responsePayload = [
            'request_id'     => $joinReq->id,
            'live_stream_id' => $stream->id,
            'guest_user_id'  => $guestUser->id,
            'status'         => $joinReq->status,
            'action'         => $action,
            'guest_session'  => $guestToken,
        ];

        // Broadcast to guest and presence channel
        try {
            event(new LiveJoinResponded($stream->id, $guestUser->id, $responsePayload));
        } catch (\Throwable $e) {}

        return response()->json([
            'status'  => true,
            'success' => true,
            'message' => "Co-host request {$action}ed successfully.",
            'data'    => $responsePayload,
        ], 200);
    }

    /**
     * 10. Host Kick / Remove Guest from Co-Hosting.
     * POST /api/live/kick-guest or POST /api/live/kick
     */
    public function kickGuest(Request $request): JsonResponse
    {
        $host = $this->resolveUser($request);
        $streamId = $request->input('live_stream_id') ?? $request->input('id');
        $guestUserId = $request->input('guest_user_id') ?? $request->input('user_id');

        $stream = LiveStream::where('id', $streamId)->orWhere('channel_name', $streamId)->first();
        if (!$stream) {
            return response()->json(['status' => false, 'message' => 'Live stream not found.'], 404);
        }

        if ($host && $stream->host_id !== $host->id && !$host->isSuperAdmin()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized.'], 403);
        }

        // Downgrade participant to left / viewer
        LiveParticipant::where('live_stream_id', $stream->id)
            ->where('user_id', $guestUserId)
            ->where('role', 'guest')
            ->update(['left_at' => now()]);

        $kickPayload = [
            'live_stream_id' => $stream->id,
            'guest_user_id'  => (int) $guestUserId,
            'reason'         => 'host_removed',
        ];

        // Broadcast kick event
        try {
            event(new LiveGuestKicked($stream->id, $guestUserId, $kickPayload));
        } catch (\Throwable $e) {}

        return response()->json([
            'status'  => true,
            'success' => true,
            'message' => 'Guest kicked from live co-hosting.',
            'data'    => $kickPayload,
        ], 200);
    }
}
