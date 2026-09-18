<?php

namespace App\Http\Controllers\Api;

use App\Events\AudioMuteEvent;
use App\Events\CoHostStatusEvent;
use App\Events\LiveChatMessageEvent;
use App\Events\LiveGiftSent;
use App\Events\LiveGiftSentEvent;
use App\Events\LiveGuestKicked;
use App\Events\LiveJoinRequested;
use App\Events\LiveJoinResponded;
use App\Events\LiveLikeSent;
use App\Events\LiveMessageSent;
use App\Events\LiveStreamEnded;
use App\Events\LiveViewerCountUpdated;
use App\Events\StreamSignalingEvent;
use App\Events\WebRTCSignalEvent;
use App\Http\Controllers\Controller;
use App\Models\Gift;
use App\Models\GiftTransaction;
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
     * GET /api/lives/active, GET /api/live/active, GET /api/live/list, GET /api/live/active-streams, GET /api/live/streamers
     */
    public function getActiveLives(Request $request): JsonResponse
    {
        $page = (int) $request->input('page', 1);
        $perPage = min((int) $request->input('per_page', 30), 100);

        $query = LiveStream::with([
                'host',
                'guests.user'
            ])
            ->whereIn('status', ['live', 'active']);

        // Optional search filter
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('title', 'LIKE', "%{$s}%")
                  ->orWhere('channel_name', 'LIKE', "%{$s}%")
                  ->orWhereHas('host', function ($hq) use ($s) {
                      $hq->where('name', 'LIKE', "%{$s}%")
                         ->orWhere('nickname', 'LIKE', "%{$s}%")
                         ->orWhere('account_id', 'LIKE', "%{$s}%");
                  });
            });
        }

        $sort = $request->input('sort', 'latest');
        if ($sort === 'viewers' || $sort === 'popular') {
            $query->orderByDesc('viewer_count')->orderByDesc('likes_count')->orderByDesc('id');
        } else {
            $query->orderByDesc('started_at')->orderByDesc('id')->orderByDesc('viewer_count');
        }

        $streams = $query->paginate($perPage, ['*'], 'page', $page);

        $formatted = collect($streams->items())->map(function ($s) {
            $host = $s->host;
            $coverUrl = $s->cover_image_url ?: ($host?->avatar_url ?? url('assets/images/default_avatar.png'));
            
            return [
                'id'                    => $s->id,
                'room_id'               => (string) $s->id,
                'live_stream_id'        => $s->id,
                'stream_id'             => (string) $s->id,
                'channel_name'          => $s->channel_name,
                'title'                 => $s->title ?: (($host?->display_name ?? 'Host') . "'s Live Broadcast"),
                'cover_image'           => $coverUrl,
                'cover_image_url'       => $coverUrl,
                'status'                => $s->status,
                'viewer_count'          => (int) $s->viewer_count,
                'viewers'               => (int) $s->viewer_count,
                'likes_count'           => (int) ($s->likes_count ?? 0),
                'likes'                 => (int) ($s->likes_count ?? 0),
                'total_diamonds_earned' => (int) $s->total_diamonds_earned,
                'diamonds'              => (int) $s->total_diamonds_earned,
                'agora_token'           => $s->agora_token,
                'started_at'            => $s->started_at ? $s->started_at->toIso8601String() : null,
                'host'                  => $host ? [
                    'id'           => $host->id,
                    'account_id'   => $host->account_id,
                    'display_name' => $host->display_name ?? $host->name ?? 'Host',
                    'name'         => $host->display_name ?? $host->name ?? 'Host',
                    'avatar'       => $host->avatar_url,
                    'avatar_url'   => $host->avatar_url,
                    'gender'       => $host->gender ?: 'female',
                    'country'      => $host->country ?: 'Bangladesh',
                    'city'         => $host->city ?: '',
                    'level'        => $host->level ?: 'Lv1',
                    'bio'          => $host->bio ?: '',
                ] : null,
                'user'                  => $host ? [
                    'id'           => $host->id,
                    'account_id'   => $host->account_id,
                    'display_name' => $host->display_name ?? $host->name ?? 'Host',
                    'name'         => $host->display_name ?? $host->name ?? 'Host',
                    'avatar'       => $host->avatar_url,
                    'avatar_url'   => $host->avatar_url,
                    'gender'       => $host->gender ?: 'female',
                    'country'      => $host->country ?: 'Bangladesh',
                    'city'         => $host->city ?: '',
                    'level'        => $host->level ?: 'Lv1',
                ] : null,
                'active_guests'         => $s->guests->map(fn($g) => [
                    'user_id'      => $g->user_id,
                    'account_id'   => $g->user?->account_id,
                    'display_name' => $g->user?->display_name ?? 'Guest',
                    'avatar_url'   => $g->user?->avatar_url,
                ])->values(),
                'guests_count'          => $s->guests->count(),
            ];
        });

        return response()->json([
            'status'     => true,
            'success'    => true,
            'message'    => 'Active live streams retrieved successfully.',
            'data'       => $formatted,
            'streamers'  => $formatted,
            'lives'      => $formatted,
            'streams'    => $formatted,
            'list'       => $formatted,
            'pagination' => [
                'current_page' => $streams->currentPage(),
                'last_page'    => $streams->lastPage(),
                'total'        => $streams->total(),
            ],
        ], 200);
    }

    /**
     * 2. Host Start Live Broadcasting.
     * POST /api/live/start, POST /api/v1/live/start, POST /api/v1/stream/start
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

        // Generate RTC credentials for Broadcaster / Host (respecting Admin active_driver)
        $sessionTokenData = $this->callingManager->initializeSession(
            $host,
            $channelName,
            'live',
            'publisher',
            ['uid' => $host->id]
        );

        $activeDriver = $sessionTokenData['driver'] ?? $this->callingManager->getActiveDriverName();

        $agoraToken = $sessionTokenData['agora_token'] 
                   ?? $sessionTokenData['rtc_token'] 
                   ?? $sessionTokenData['token'] 
                   ?? $sessionTokenData['agora']['token'] 
                   ?? $sessionTokenData['data']['token'] 
                   ?? null;

        $appId = $sessionTokenData['agora_app_id'] 
              ?? $sessionTokenData['app_id'] 
              ?? config('services.agora.app_id', env('AGORA_APP_ID', 'c13c72df342d4a1386da678ba4c95f13'));

        $liveStream = LiveStream::create([
            'host_id'               => $host->id,
            'channel_name'          => $channelName,
            'title'                 => $title,
            'cover_image'           => $coverImageUrl,
            'status'                => 'live',
            'viewer_count'          => 1,
            'likes_count'           => 0,
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

        $streamPayload = [
            'room_id'            => (string) $liveStream->id,
            'live_stream_id'     => $liveStream->id,
            'stream_id'          => $liveStream->id,
            'channel_name'       => $channelName,
            'title'              => $title,
            'cover_image_url'    => $liveStream->cover_image_url,
            'cover_image'        => $liveStream->cover_image_url,
            'status'             => 'active',
            'role'               => 'host',
            'viewer_count'       => 1,
            'likes_count'        => 0,
            'host_id'            => $host->id,
            'active_engine'      => $activeDriver,
            'active_driver'      => $activeDriver,
            'driver'             => $activeDriver,
            'is_agora'           => $activeDriver === 'agora',
            'is_vps_webrtc'      => $activeDriver === 'vps_webrtc',
            'app_id'             => $appId,
            'agora_app_id'       => $appId,
            'uid'                => $host->id,
            'agora_uid'          => $host->id,
            'token'              => $agoraToken,
            'agora_token'        => $agoraToken,
            'rtc_token'          => $agoraToken,
            'reverb_channel'     => 'presence-stream.' . $liveStream->id,
            'engine_credentials' => array_merge([
                'driver'       => $activeDriver,
                'app_id'       => $appId,
                'agora_app_id' => $appId,
                'token'        => $agoraToken,
                'agora_token'  => $agoraToken,
                'rtc_token'    => $agoraToken,
                'channel_name' => $channelName,
                'uid'          => $host->id,
                'agora_uid'    => $host->id,
            ], $sessionTokenData),
            'session'            => $sessionTokenData,
            'host'               => [
                'id'           => $host->id,
                'account_id'   => $host->account_id,
                'display_name' => $host->display_name,
                'name'         => $host->display_name,
                'avatar'       => $host->avatar_url,
                'avatar_url'   => $host->avatar_url,
                'level'        => $host->level ?: 'Lv1',
            ],
        ];

        // 1. Broadcast to global lobby so all phones update their Live tab in real time without refreshing
        try {
            event(new \App\Events\StreamStatusChangedEvent($liveStream->id, 'live', $streamPayload));
        } catch (\Throwable $e) {}

        // 2. Notify Admin Panel: Create ActivityLog audit record & Admin notifications
        try {
            \App\Models\ActivityLog::record(
                'live_streaming',
                'live_started',
                "{$host->display_name} (#{$host->account_id}) started a live broadcast: '{$title}'",
                ['stream_id' => $liveStream->id, 'channel_name' => $channelName, 'host_id' => $host->id, 'engine' => $activeDriver],
                null,
                $host
            );

            $adminUsers = User::where('is_admin', true)->orWhereIn('role', ['admin', 'super_admin'])->get();
            foreach ($adminUsers as $adm) {
                \App\Models\Notification::createNotification(
                    $adm->id,
                    $host->id,
                    'live_stream_started',
                    '🔴 New Live Stream Started',
                    "{$host->display_name} (#{$host->account_id}) has started live broadcasting: '{$title}'",
                    [
                        'stream_id'     => $liveStream->id,
                        'channel_name'  => $channelName,
                        'host_id'       => $host->id,
                        'host_name'     => $host->display_name,
                        'active_engine' => $activeDriver,
                    ]
                );
            }
        } catch (\Throwable $e) {}

        return response()->json([
            'status'  => true,
            'success' => true,
            'message' => 'Live stream broadcast started successfully!',
            'data'    => $streamPayload,
        ], 200);
    }

    /**
     * 3. Host End Live Stream.
     * POST /api/live/end, POST /api/v1/live/end, POST /api/v1/stream/end
     */
    public function endLive(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        $streamId = $request->input('room_id') ?? $request->input('live_stream_id') ?? $request->input('id') ?? $request->input('channel_name');

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
            'room_id'               => (string) $stream->id,
            'live_stream_id'        => $stream->id,
            'stream_id'             => $stream->id,
            'channel_name'          => $stream->channel_name,
            'status'                => 'ended',
            'duration_seconds'      => $stream->ended_at ? $stream->ended_at->diffInSeconds($stream->started_at) : 0,
            'total_diamonds_earned' => (int) $stream->total_diamonds_earned,
            'peak_viewers'          => (int) $stream->viewer_count,
        ];

        // Broadcast event to all connected viewers and guests & global lobby
        try {
            event(new LiveStreamEnded($stream->id, $summary));
            event(new \App\Events\StreamStatusChangedEvent($stream->id, 'ended', $summary));
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
     * POST /api/live/join, POST /api/live/{id}/join, POST /api/v1/live/join, POST /api/v1/stream/join
     */
    public function joinLive(Request $request, $id = null): JsonResponse
    {
        $viewer = $this->resolveUser($request);
        $streamId = $id ?? $request->input('room_id') ?? $request->input('live_stream_id') ?? $request->input('id') ?? $request->input('channel_name');

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
            $stream->refresh();

            // Broadcast real-time viewer count update and join notification
            try {
                $viewerPayload = [
                    'id'           => $viewer->id,
                    'account_id'   => $viewer->account_id,
                    'display_name' => $viewer->display_name ?? $viewer->name ?? 'Viewer',
                    'avatar_url'   => $viewer->avatar_url,
                    'level'        => $viewer->level ?: 'Lv1',
                ];

                event(new LiveViewerCountUpdated($stream->id, [
                    'viewer_count' => (int) $stream->viewer_count,
                    'action'       => 'joined',
                    'user'         => $viewerPayload,
                ]));

                broadcast(new LiveChatMessageEvent($stream->id, [
                    'id'        => (int) (now()->timestamp . rand(100, 999)),
                    'room_id'   => (string) $stream->id,
                    'user_id'   => $viewer->id,
                    'user'      => $viewerPayload,
                    'message'   => "joined the live stream",
                    'type'      => 'join',
                    'timestamp' => now()->toIso8601String(),
                ]))->toOthers();
            } catch (\Throwable $e) {}
        }

        $viewerUid = (int) ($viewer?->id ?? rand(100000, 999999));

        // Generate audience credentials (respecting Admin active_driver)
        $sessionTokenData = $this->callingManager->initializeSession(
            $viewer,
            $stream->channel_name,
            'live',
            'subscriber',
            ['uid' => $viewerUid]
        );

        $activeDriver = $sessionTokenData['driver'] ?? $this->callingManager->getActiveDriverName();

        $agoraAudienceToken = $sessionTokenData['agora_token'] 
                            ?? $sessionTokenData['rtc_token'] 
                            ?? $sessionTokenData['token'] 
                            ?? $sessionTokenData['agora']['token'] 
                            ?? $sessionTokenData['data']['token'] 
                            ?? null;

        $appId = $sessionTokenData['agora_app_id'] 
              ?? $sessionTokenData['app_id'] 
              ?? config('services.agora.app_id', env('AGORA_APP_ID', 'c13c72df342d4a1386da678ba4c95f13'));

        return response()->json([
            'status'  => true,
            'success' => true,
            'message' => 'Joined live stream successfully.',
            'data'    => [
                'room_id'            => (string) $stream->id,
                'live_stream_id'     => $stream->id,
                'stream_id'          => $stream->id,
                'channel_name'       => $stream->channel_name,
                'title'              => $stream->title,
                'cover_image_url'    => $stream->cover_image_url,
                'viewer_count'       => (int) $stream->viewer_count,
                'likes_count'        => (int) ($stream->likes_count ?? 0),
                'role'               => 'audience',
                'active_engine'      => $activeDriver,
                'active_driver'      => $activeDriver,
                'driver'             => $activeDriver,
                'is_agora'           => $activeDriver === 'agora',
                'is_vps_webrtc'      => $activeDriver === 'vps_webrtc',
                'app_id'             => $appId,
                'agora_app_id'       => $appId,
                'uid'                => $viewerUid,
                'agora_uid'          => $viewerUid,
                'token'              => $agoraAudienceToken,
                'agora_token'        => $agoraAudienceToken,
                'rtc_token'          => $agoraAudienceToken,
                'reverb_channel'     => 'presence-stream.' . $stream->id,
                'seat_layout'        => 'single',
                'is_following'       => false,
                'session'            => $sessionTokenData,
                'engine_credentials' => array_merge([
                    'driver'       => $activeDriver,
                    'app_id'       => $appId,
                    'agora_app_id' => $appId,
                    'token'        => $agoraAudienceToken,
                    'agora_token'  => $agoraAudienceToken,
                    'rtc_token'    => $agoraAudienceToken,
                    'channel_name' => $stream->channel_name,
                    'uid'          => $viewerUid,
                    'agora_uid'    => $viewerUid,
                ], $sessionTokenData),
                'host'               => [
                    'id'           => $stream->host?->id,
                    'account_id'   => $stream->host?->account_id,
                    'display_name' => $stream->host?->display_name ?? 'Host',
                    'name'         => $stream->host?->display_name ?? 'Host',
                    'avatar_url'   => $stream->host?->avatar_url,
                    'avatar'       => $stream->host?->avatar_url,
                    'level'        => $stream->host?->level ?: 'Lv1',
                    'gender'       => $stream->host?->gender ?: 'female',
                ],
            ],
        ], 200);
    }

    /**
     * 5. Leave Live Stream.
     * POST /api/live/leave, POST /api/live/{id}/leave, POST /api/v1/live/leave, POST /api/v1/stream/leave
     */
    public function leaveLive(Request $request, $id = null): JsonResponse
    {
        $user = $this->resolveUser($request);
        $streamId = $id ?? $request->input('room_id') ?? $request->input('live_stream_id') ?? $request->input('id');

        $stream = LiveStream::where('id', $streamId)->orWhere('channel_name', $streamId)->first();

        if ($stream && $user) {
            LiveParticipant::where('live_stream_id', $stream->id)
                ->where('user_id', $user->id)
                ->update(['left_at' => now()]);

            if ($stream->viewer_count > 1) {
                $stream->decrement('viewer_count');
                $stream->refresh();
            }

            try {
                event(new LiveViewerCountUpdated($stream->id, [
                    'viewer_count' => (int) $stream->viewer_count,
                    'action'       => 'left',
                    'user'         => [
                        'id'           => $user->id,
                        'display_name' => $user->display_name ?? $user->name ?? 'Viewer',
                        'avatar_url'   => $user->avatar_url,
                    ],
                ]));
            } catch (\Throwable $e) {}
        }

        return response()->json([
            'status'  => true,
            'success' => true,
            'message' => 'Left live stream successfully.',
        ], 200);
    }

    /**
     * 5.1 Real-Time Like / Heart React in Live Stream.
     * POST /api/live/like, POST /api/live/send-like, POST /api/live/react, POST /api/v1/live/like, POST /api/v1/stream/like
     */
    public function sendLike(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        $streamId = $request->input('room_id') ?? $request->input('live_stream_id') ?? $request->input('stream_id') ?? $request->input('id');
        $count = max(1, min(50, (int) $request->input('count', 1)));

        $stream = LiveStream::where('id', $streamId)->orWhere('channel_name', $streamId)->first();
        if (!$stream) {
            return response()->json([
                'status'  => false,
                'message' => 'Live stream not found.',
            ], 404);
        }

        $stream->increment('likes_count', $count);
        $stream->refresh();

        $senderPayload = $user ? [
            'id'           => $user->id,
            'account_id'   => $user->account_id,
            'display_name' => $user->display_name ?? $user->name ?? 'Viewer',
            'avatar_url'   => $user->avatar_url,
            'level'        => $user->level ?: 'Lv1',
        ] : [
            'id'           => (int) ($request->input('user_id') ?? 0),
            'display_name' => 'Viewer',
            'avatar_url'   => null,
            'level'        => 'Lv1',
        ];

        $likeData = [
            'room_id'       => (string) $stream->id,
            'stream_id'     => (string) $stream->id,
            'likes_count'   => (int) $stream->likes_count,
            'total_likes'   => (int) $stream->likes_count,
            'count'         => $count,
            'sender_id'     => $senderPayload['id'],
            'sender_name'   => $senderPayload['display_name'],
            'sender_avatar' => $senderPayload['avatar_url'],
            'user'          => $senderPayload,
            'timestamp'     => now()->toIso8601String(),
        ];

        // Broadcast real-time like event across all connected participants
        try {
            event(new LiveLikeSent($stream->id, $likeData));
        } catch (\Throwable $e) {}

        return response()->json([
            'status'      => true,
            'success'     => true,
            'message'     => 'Like sent successfully.',
            'likes_count' => (int) $stream->likes_count,
            'total_likes' => (int) $stream->likes_count,
            'data'        => $likeData,
        ], 200);
    }

    /**
     * 6. Send Public Chat Message or Gift in Live Stream.
     * Instant broadcast using ShouldBroadcastNow.
     * POST /api/live/send-message, POST /api/live/message, POST /api/live/comment, POST /api/v1/stream/comment
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);

        $request->validate([
            'room_id'        => 'sometimes|required',
            'message'        => 'required_without:gift_id',
            'type'           => 'sometimes|required|in:text,gift',
            'gift_id'        => 'nullable|integer',
        ]);

        $streamId = $request->input('room_id') ?? $request->input('live_stream_id') ?? $request->input('stream_id') ?? $request->input('id');
        $messageText = trim($request->input('message') ?? $request->input('text') ?? '');
        $type = $request->input('type', 'text');
        $giftId = $request->input('gift_id');

        $stream = LiveStream::where('id', $streamId)->orWhere('channel_name', $streamId)->first();
        $roomId = $stream ? (string)$stream->id : (string)($streamId ?: '1');

        $gift = null;
        if ($giftId) {
            $gift = Gift::find($giftId);
            $type = 'gift';
        }

        $senderId = $user ? $user->id : (int)($request->input('user_id') ?? 0);
        $senderName = $user ? ($user->display_name ?? $user->name ?? 'User') : 'User';
        $senderAvatar = $user ? $user->avatar_url : null;

        $msgRecord = LiveMessage::create([
            'live_stream_id' => (int) $roomId,
            'user_id'        => $senderId,
            'message'        => $messageText ?: ($gift ? "Sent {$gift->name}" : ''),
            'type'           => $type,
            'gift_id'        => $giftId,
            'metadata'       => [
                'sender_name'   => $senderName,
                'sender_avatar' => $senderAvatar,
                'level'         => $user?->level ?: 'Lv1',
                'gift_data'     => $gift,
            ],
        ]);

        $messagePayload = [
            'id'             => $msgRecord->id,
            'room_id'        => (string) $roomId,
            'stream_id'      => (string) $roomId,
            'live_stream_id' => (int) $roomId,
            'user_id'        => $senderId,
            'user_name'      => $senderName,
            'user_avatar'    => $senderAvatar,
            'user'           => [
                'id'           => $senderId,
                'display_name' => $senderName,
                'avatar_url'   => $senderAvatar,
                'level'        => $user?->level ?: 'Lv1',
            ],
            'message'        => $msgRecord->message,
            'type'           => $type,
            'gift_id'        => $giftId,
            'gift_data'      => $gift,
            'gift'           => $gift,
            'level'          => $user?->level ?: 'Lv1',
            'created_at'     => $msgRecord->created_at->toIso8601String(),
            'timestamp'      => $msgRecord->created_at->toIso8601String(),
        ];

        try {
            // Broadcast without suppression to ensure both sender and receiver devices receive chat updates
            event(new LiveChatMessageEvent($roomId, $messagePayload));
            event(new LiveMessageSent($roomId, $messagePayload));
        } catch (\Throwable $e) {}

        return response()->json([
            'status'  => 'success',
            'success' => true,
            'message' => 'Live message sent successfully.',
            'data'    => $messagePayload,
        ], 200);
    }

    /**
     * 7. Co-Host Action Controller (Invite, Accept, Reject, Remove).
     * POST /api/live/cohost-action, POST /api/live/handle-cohost, POST /api/v1/stream/cohost-action
     */
    public function handleCoHost(Request $request): JsonResponse
    {
        $request->validate([
            'room_id'        => 'sometimes|required',
            'target_user_id' => 'required',
            'action'         => 'required|in:invite,invited,accept,accepted,reject,rejected,remove,removed',
        ]);

        $roomId = $request->input('room_id') ?? $request->input('stream_id') ?? $request->input('live_stream_id') ?? $request->input('id');
        $action = strtolower($request->input('action'));
        $targetUserId = $request->input('target_user_id') ?? $request->input('user_id');

        $user = User::findOrFail($targetUserId);
        $stream = LiveStream::where('id', $roomId)->orWhere('channel_name', $roomId)->first();
        $streamId = $stream ? (string)$stream->id : (string)$roomId;

        // Manage room members in database
        if ($action === 'accept' || $action === 'accepted') {
            if ($stream) {
                LiveParticipant::updateOrCreate(
                    ['live_stream_id' => $stream->id, 'user_id' => $user->id],
                    ['role' => 'guest', 'joined_at' => now(), 'left_at' => null, 'video_enabled' => true]
                );
            }
        } elseif ($action === 'remove' || $action === 'removed' || $action === 'reject' || $action === 'rejected') {
            if ($stream) {
                LiveParticipant::where('live_stream_id', $stream->id)
                    ->where('user_id', $user->id)
                    ->update(['left_at' => now()]);
            }
        }

        try {
            broadcast(new CoHostStatusEvent($streamId, $action, $user))->toOthers();
        } catch (\Throwable $e) {}

        return response()->json([
            'status'  => 'success',
            'message' => "Co-host {$action} successful",
            'data'    => [
                'room_id'     => (string) $streamId,
                'action'      => $action,
                'target_user' => [
                    'id'           => $user->id,
                    'display_name' => $user->display_name ?? $user->name,
                    'avatar_url'   => $user->avatar_url,
                ],
            ],
        ], 200);
    }

    /**
     * 8. WebRTC Signaling API (Offer, Answer, Candidate).
     * POST /api/live/signal, POST /api/v1/stream/signal, POST /api/v1/live/signal
     */
    public function sendSignal(Request $request): JsonResponse
    {
        $sender = $this->resolveUser($request);
        $roomId = $request->input('room_id') ?? $request->input('stream_id') ?? $request->input('live_stream_id') ?? $request->input('id');
        $toUserId = $request->input('to_user_id') ?? $request->input('target_user_id');
        $fromUserId = $sender ? $sender->id : ($request->input('from_user_id') ?? $request->input('sender_id'));
        $type = $request->input('type', 'offer'); // 'offer', 'answer', 'candidate'
        $data = $request->input('data') ?? $request->input('sdp_or_candidate') ?? $request->input('payload');

        $signalData = [
            'room_id'          => (string) $roomId,
            'stream_id'        => (string) $roomId,
            'from_user_id'     => (int) $fromUserId,
            'sender_id'        => (int) $fromUserId,
            'to_user_id'       => (int) $toUserId,
            'target_user_id'   => (int) $toUserId,
            'type'             => $type,
            'data'             => $data,
            'payload'          => $data,
            'sdp_or_candidate' => $data,
            'timestamp'        => now()->toIso8601String(),
        ];

        try {
            broadcast(new WebRTCSignalEvent($roomId, $signalData))->toOthers();
            event(new StreamSignalingEvent((string) $roomId, $signalData));
        } catch (\Throwable $e) {}

        return response()->json([
            'status'  => 'sent',
            'message' => "WebRTC signal '{$type}' broadcast successfully.",
            'data'    => $signalData,
        ], 200);
    }

    /**
     * 9. Audio Mute / Unmute Control API.
     * POST /api/live/mute-toggle, POST /api/live/toggle-mute, POST /api/v1/live/mute-toggle
     */
    public function toggleMute(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'room_id'        => 'sometimes|required',
            'target_user_id' => 'sometimes|required',
            'is_muted'       => 'required|boolean',
            'muted_by_host'  => 'nullable|boolean'
        ]);

        $roomId = $request->input('room_id') ?? $request->input('stream_id') ?? $request->input('live_stream_id') ?? $request->input('id');
        $targetUserId = $request->input('target_user_id') ?? $request->input('user_id');
        $isMuted = (bool) $request->input('is_muted');
        $mutedByHost = (bool) $request->input('muted_by_host', false);

        $payload = [
            'room_id'        => (string) $roomId,
            'stream_id'      => (string) $roomId,
            'target_user_id' => (int) $targetUserId,
            'user_id'        => (int) $targetUserId,
            'is_muted'       => $isMuted,
            'muted_by_host'  => $mutedByHost,
            'timestamp'      => now()->toIso8601String(),
        ];

        try {
            broadcast(new AudioMuteEvent($roomId, $payload))->toOthers();
        } catch (\Throwable $e) {}

        return response()->json([
            'status'  => 'success',
            'message' => 'Audio mute state updated successfully.',
            'data'    => $payload,
        ], 200);
    }

    /**
     * 10. Send Virtual Gift in Live Stream with 50/50 Revenue Split.
     * POST /api/live/gift, POST /api/live/send-gift, POST /api/v1/stream/send-gift
     */
    public function sendGift(Request $request): JsonResponse
    {
        $sender = $this->resolveUser($request);
        if (!$sender) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $streamId = $request->input('room_id') ?? $request->input('live_stream_id') ?? $request->input('stream_id') ?? $request->input('id');
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

        $price = (int) ($gift->coins ?? $gift->coin_price ?? 0);
        $totalCost = $price * $quantity;

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
                    'gift_svga'     => $gift->animation_url ?? $gift->animation_asset_url,
                    'quantity'      => $quantity,
                    'sender_name'   => $sender->display_name,
                    'sender_avatar' => $sender->avatar_url,
                ],
            ]);

            $transaction = GiftTransaction::create([
                'stream_id'   => $stream->id,
                'sender_id'   => $sender->id,
                'receiver_id' => $stream->host_id,
                'gift_id'     => $gift->id,
                'coins_spent' => $totalCost,
            ]);

            DB::commit();

            $giftPayload = [
                'transaction_id'      => $transaction->id,
                'room_id'             => (string) $stream->id,
                'stream_id'           => $stream->id,
                'sender'              => [
                    'id'     => $sender->id,
                    'name'   => $sender->display_name,
                    'avatar' => $sender->avatar_url,
                ],
                'gift'                => [
                    'id'                  => $gift->id,
                    'name'                => $gift->name,
                    'slug'                => Str::slug($gift->name),
                    'coin_price'          => $price,
                    'icon_url'            => $gift->icon_url,
                    'animation_asset_url' => $gift->animation_url ?? $gift->animation_asset_url,
                    'animation_type'      => $gift->animation_type ?? 'svga',
                ],
                'quantity'            => $quantity,
                'total_coins'         => $totalCost,
                'timestamp'           => now()->timestamp,
                // Backward compatibility properties
                'id'                  => $liveMsg->id,
                'sender_id'           => $sender->id,
                'sender_name'         => $sender->display_name,
                'sender_avatar'       => $sender->avatar_url,
                'gift_id'             => $gift->id,
                'gift_name'           => $gift->name,
                'gift_icon'           => $gift->icon_url,
                'animation_url'       => $gift->animation_url ?? $gift->animation_asset_url,
                'created_at'          => $liveMsg->created_at->toIso8601String(),
            ];

            // Broadcast to live presence channel, live-stream.{id}, and live-room.{id}
            try {
                event(new LiveGiftSent($stream->id, $giftPayload));
                event(new LiveGiftSentEvent($stream->id, $giftPayload));
                event(new LiveChatMessageEvent($stream->id, [
                    'id'        => $liveMsg->id,
                    'room_id'   => (string) $stream->id,
                    'user_id'   => $sender->id,
                    'user'      => [
                        'id'           => $sender->id,
                        'display_name' => $sender->display_name,
                        'avatar_url'   => $sender->avatar_url,
                        'level'        => $sender->level ?: 'Lv1',
                    ],
                    'message'   => "Sent {$quantity}x {$gift->name}",
                    'type'      => 'gift',
                    'gift_id'   => $gift->id,
                    'gift_data' => $giftPayload['gift'],
                    'gift'      => $giftPayload['gift'],
                    'timestamp' => now()->toIso8601String(),
                ]));
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
     * 11. Legacy Helper Methods for Co-Host and Join Requests.
     */
    public function inviteCoHost(Request $request): JsonResponse
    {
        $request->merge(['action' => 'invite']);
        return $this->handleCoHost($request);
    }

    public function acceptCoHost(Request $request): JsonResponse
    {
        $request->merge(['action' => 'accept']);
        return $this->handleCoHost($request);
    }

    public function sendStreamSignal(Request $request): JsonResponse
    {
        return $this->sendSignal($request);
    }

    public function requestJoin(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $streamId = $request->input('room_id') ?? $request->input('live_stream_id') ?? $request->input('id');
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
            'room_id'        => (string) $stream->id,
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

    public function respondJoinRequest(Request $request): JsonResponse
    {
        $host = $this->resolveUser($request);
        $requestId = $request->input('request_id');
        $action = strtolower($request->input('action', 'accept'));

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
            LiveParticipant::updateOrCreate(
                ['live_stream_id' => $stream->id, 'user_id' => $guestUser->id],
                ['role' => 'guest', 'joined_at' => now(), 'left_at' => null, 'video_enabled' => true]
            );

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
            'room_id'        => (string) $stream->id,
            'live_stream_id' => $stream->id,
            'guest_user_id'  => $guestUser->id,
            'status'         => $joinReq->status,
            'action'         => $action,
            'guest_session'  => $guestToken,
        ];

        try {
            event(new LiveJoinResponded($stream->id, $guestUser->id, $responsePayload));
            broadcast(new CoHostStatusEvent($stream->id, $action, $guestUser))->toOthers();
        } catch (\Throwable $e) {}

        return response()->json([
            'status'  => true,
            'success' => true,
            'message' => "Co-host request {$action}ed successfully.",
            'data'    => $responsePayload,
        ], 200);
    }

    public function kickGuest(Request $request): JsonResponse
    {
        $host = $this->resolveUser($request);
        $streamId = $request->input('room_id') ?? $request->input('live_stream_id') ?? $request->input('id');
        $guestUserId = $request->input('guest_user_id') ?? $request->input('target_user_id') ?? $request->input('user_id');

        $stream = LiveStream::where('id', $streamId)->orWhere('channel_name', $streamId)->first();
        if (!$stream) {
            return response()->json(['status' => false, 'message' => 'Live stream not found.'], 404);
        }

        if ($host && $stream->host_id !== $host->id && !$host->isSuperAdmin()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized.'], 403);
        }

        LiveParticipant::where('live_stream_id', $stream->id)
            ->where('user_id', $guestUserId)
            ->where('role', 'guest')
            ->update(['left_at' => now()]);

        $kickPayload = [
            'room_id'        => (string) $stream->id,
            'live_stream_id' => $stream->id,
            'guest_user_id'  => (int) $guestUserId,
            'reason'         => 'host_removed',
        ];

        try {
            $user = User::find($guestUserId);
            event(new LiveGuestKicked($stream->id, $guestUserId, $kickPayload));
            if ($user) {
                broadcast(new CoHostStatusEvent($stream->id, 'removed', $user))->toOthers();
            }
        } catch (\Throwable $e) {}

        return response()->json([
            'status'  => true,
            'success' => true,
            'message' => 'Guest kicked from live co-hosting.',
            'data'    => $kickPayload,
        ], 200);
    }

    /**
     * 12. Get Active Viewers List for Live Stream.
     * GET /api/live/{id}/viewers, GET /api/live/viewers, GET /api/v1/live/viewers
     */
    public function getViewers(Request $request, $id = null): JsonResponse
    {
        $streamId = $id ?? $request->input('room_id') ?? $request->input('live_stream_id') ?? $request->input('id');
        $stream = LiveStream::where('id', $streamId)->orWhere('channel_name', $streamId)->first();

        if (!$stream) {
            return response()->json(['status' => false, 'message' => 'Live stream not found.'], 404);
        }

        $viewers = LiveParticipant::with('user:id,account_id,name,display_name,avatar,level,gender,country')
            ->where('live_stream_id', $stream->id)
            ->where('role', 'viewer')
            ->whereNull('left_at')
            ->orderByDesc('id')
            ->get()
            ->map(function ($p) {
                $u = $p->user;
                return [
                    'user_id'      => $p->user_id,
                    'account_id'   => $u?->account_id,
                    'display_name' => $u?->display_name ?? $u?->name ?? 'Viewer',
                    'name'         => $u?->display_name ?? $u?->name ?? 'Viewer',
                    'avatar_url'   => $u?->avatar_url,
                    'avatar'       => $u?->avatar_url,
                    'level'        => $u?->level ?: 'Lv1',
                    'gender'       => $u?->gender ?: 'female',
                    'joined_at'    => $p->joined_at ? $p->joined_at->toIso8601String() : null,
                ];
            });

        return response()->json([
            'status'       => true,
            'success'      => true,
            'message'      => 'Viewers retrieved successfully.',
            'viewer_count' => $viewers->count() ?: (int) $stream->viewer_count,
            'data'         => $viewers,
            'viewers'      => $viewers,
        ], 200);
    }

    /**
     * 13. Get Real Live Chat Messages from Database (No mock data).
     * GET /api/live/messages, GET /api/live/{id}/messages, GET /api/v1/live/messages
     */
    public function getLiveMessages(Request $request, $id = null): JsonResponse
    {
        $streamId = $id ?? $request->input('room_id') ?? $request->input('live_stream_id') ?? $request->input('id');
        $stream = LiveStream::where('id', $streamId)->orWhere('channel_name', $streamId)->first();

        if (!$stream) {
            return response()->json(['status' => false, 'message' => 'Live stream not found.'], 404);
        }

        $messages = LiveMessage::with(['user:id,account_id,name,display_name,avatar,level', 'gift'])
            ->where('live_stream_id', $stream->id)
            ->orderBy('id', 'asc')
            ->take(100)
            ->get()
            ->map(function ($m) {
                $u = $m->user;
                return [
                    'id'          => $m->id,
                    'room_id'     => (string) $m->live_stream_id,
                    'user_id'     => $m->user_id,
                    'user_name'   => $u?->display_name ?? $u?->name ?? 'User',
                    'user_avatar' => $u?->avatar_url,
                    'user'        => [
                        'id'           => $m->user_id,
                        'account_id'   => $u?->account_id,
                        'display_name' => $u?->display_name ?? $u?->name ?? 'User',
                        'avatar_url'   => $u?->avatar_url,
                        'level'        => $u?->level ?: 'Lv1',
                    ],
                    'message'     => $m->message,
                    'type'        => $m->type,
                    'gift_id'     => $m->gift_id,
                    'gift'        => $m->gift,
                    'level'       => $u?->level ?: 'Lv1',
                    'created_at'  => $m->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'status'   => true,
            'success'  => true,
            'messages' => $messages,
            'data'     => $messages,
        ], 200);
    }

    /**
     * 14. Get Host Received Gifts Summary for Room.
     * GET /api/v1/streams/{stream_id}/gift-summary, GET /api/v1/live/{id}/gift-summary
     */
    public function getGiftSummary(Request $request, $id = null): JsonResponse
    {
        $streamId = $id ?? $request->input('stream_id') ?? $request->input('room_id') ?? $request->input('live_stream_id') ?? $request->input('id');
        $stream = LiveStream::where('id', $streamId)->orWhere('channel_name', $streamId)->first();

        if (!$stream) {
            return response()->json(['status' => false, 'message' => 'Live stream not found.'], 404);
        }

        $giftsSummary = GiftTransaction::with('gift')
            ->where('live_stream_id', $stream->id)
            ->select('gift_id', DB::raw('count(*) as count'), DB::raw('sum(coin_amount) as total_coins'))
            ->groupBy('gift_id')
            ->get()
            ->map(function ($gt) {
                return [
                    'gift_id'     => $gt->gift_id,
                    'name'        => $gt->gift?->name ?? 'Gift',
                    'count'       => (int) $gt->count,
                    'icon'        => $gt->gift?->icon_url ?? $gt->gift?->animation_url,
                    'total_coins' => (int) $gt->total_coins,
                ];
            });

        return response()->json([
            'status'  => true,
            'success' => true,
            'data'    => [
                'total_coins_earned'    => (int) $stream->total_diamonds_earned,
                'total_diamonds_earned' => (int) $stream->total_diamonds_earned,
                'gifts'                 => $giftsSummary,
            ],
        ], 200);
    }

    /**
     * 15. Guest Mic / Seat Request (TikTok & Bigo 4/9 Seat Grid).
     * POST /api/v1/live/{stream_id}/seat-request, POST /api/v1/streams/{stream_id}/seat-request
     */
    public function requestSeat(Request $request, $id = null): JsonResponse
    {
        return $this->requestJoin($request, $id);
    }

    /**
     * 16. Switch Streaming Engine (Agora vs Self-Hosted WebRTC).
     * POST /api/v1/admin/live/switch-engine
     */
    public function switchEngine(Request $request): JsonResponse
    {
        $targetEngine = $request->input('target_engine', 'agora');
        $streamId = $request->input('stream_id') ?? $request->input('room_id');

        try {
            $setting = StreamingSetting::first();
            if ($setting) {
                $setting->update(['primary_driver' => $targetEngine]);
            }
        } catch (\Throwable $e) {}

        return response()->json([
            'status'  => true,
            'success' => true,
            'message' => 'Engine switch broadcast dispatched',
            'data'    => [
                'stream_id'      => $streamId,
                'target_engine'  => $targetEngine,
            ]
        ], 200);
    }
}
