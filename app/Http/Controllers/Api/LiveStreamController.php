<?php

namespace App\Http\Controllers\Api;

use Agence104\LiveKit\AccessToken;
use Agence104\LiveKit\AccessTokenOptions;
use Agence104\LiveKit\VideoGrant;
use App\Events\CoHostAcceptedEvent;
use App\Events\CoHostRequestAccepted;
use App\Events\CoHostRequestReceived;
use App\Events\CoHostStatusEvent;
use App\Events\LiveChatMessageEvent;
use App\Events\LiveJoinRequested;
use App\Events\LiveJoinResponded;
use App\Events\LiveMessageSent;
use App\Http\Controllers\Controller;
use App\Models\LiveJoinRequest;
use App\Models\LiveMessage;
use App\Models\LiveParticipant;
use App\Models\LiveStream;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LiveStreamController extends Controller
{
    /**
     * Helper to resolve authenticated user.
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
     * 1. Generate LiveKit Token for Host, Co-Host, and Viewers
     * POST /api/live/get-token
     */
    public function getRoomToken(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request) ?? auth()->user();
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated user.',
            ], 401);
        }

        $rawRoom = $request->input('room_name') 
                ?? $request->input('room_id') 
                ?? $request->input('live_stream_id') 
                ?? $request->input('channel_name')
                ?? $request->input('host_id')
                ?? $request->input('id');

        $role = $request->input('role', 'viewer');

        // Resolve to real active stream channel_name
        $stream = null;
        if ($rawRoom) {
            $stream = LiveStream::where('channel_name', $rawRoom)
                ->orWhere('id', $rawRoom)
                ->orWhere('host_id', $rawRoom)
                ->latest()
                ->first();
        }

        if (!$stream) {
            $stream = LiveStream::whereIn('status', ['live', 'active'])->latest()->first();
        }

        $roomName = $stream ? $stream->channel_name : ($rawRoom ?: 'live_' . $user->id);

        // রোল চেক: হোস্ট এবং কো-হোস্টের জন্য ক্যান-পাবলিশ ট্রু হবে
        $canPublish = in_array($role, ['host', 'co_host', 'publisher', 'guest']);

        // .env থেকে ক্রেডেনশিয়াল রিড
        $apiKey = config('services.livekit.api_key', env('LIVEKIT_API_KEY'));
        $apiSecret = config('services.livekit.api_secret', env('LIVEKIT_API_SECRET'));
        $livekitUrl = config('services.livekit.url', env('LIVEKIT_URL', 'wss://chinchins.live/livekit'));

        if (empty($apiKey) || empty($apiSecret)) {
            $apiKey = env('LIVEKIT_API_KEY', 'APIVbeXzKatSo3u');
            $apiSecret = env('LIVEKIT_API_SECRET', 'thzlQ2sYGQQIxBPQMkjO9Rres6xuuMsqweZdT61XNsK');
        }

        $token = new AccessToken($apiKey, $apiSecret);

        $grant = new VideoGrant();
        $grant->setRoomJoin(true)
              ->setRoomName($roomName)
              ->setCanPublish(true)               // টোকেনে পাবলিশ পারমিশন সর্বদা true
              ->setCanSubscribe(true)             // সবার কথা ও ভিডিও দেখার জন্য
              ->setCanPublishData(true);          // লাইভ চ্যাটের জন্য Data Packet পারমিশন

        $tokenOptions = (new AccessTokenOptions())
            ->setIdentity((string) $user->id)
            ->setName($user->display_name ?? $user->name ?? "User_{$user->id}")
            ->setTtl(86400); // ২৪ ঘণ্টার ভ্যালিডিটি

        $token->init($tokenOptions);
        $token->setGrant($grant);

        $jwt = $token->toJwt();

        return response()->json([
            'status'        => true,
            'message'       => 'Token generated successfully',
            'token'         => $jwt,
            'livekit_token' => $jwt,
            'room_name'     => $roomName,
            'channel_name'  => $roomName,
            'livekit_url'   => $livekitUrl,
            'data'          => [
                'token'         => $jwt,
                'livekit_token' => $jwt,
                'room_name'     => $roomName,
                'channel_name'  => $roomName,
                'livekit_url'   => $livekitUrl,
                'role'          => $role,
                'can_publish'   => $canPublish,
                'user'          => [
                    'id'           => $user->id,
                    'account_id'   => $user->account_id,
                    'display_name' => $user->display_name ?? $user->name,
                    'avatar_url'   => $user->avatar_url,
                ]
            ]
        ], 200);
    }

    /**
     * 2. Viewer Request to Join as Co-Host
     * POST /api/live/request-join
     */
    public function requestJoin(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request) ?? auth()->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $roomId = $request->input('room_id') ?? $request->input('room_name') ?? $request->input('live_stream_id') ?? $request->input('id');
        $stream = LiveStream::with('host')->where('id', $roomId)->orWhere('channel_name', $roomId)->first();

        if (!$stream || $stream->status !== 'live') {
            return response()->json(['status' => false, 'message' => 'Live stream is not active.'], 404);
        }

        $joinReq = LiveJoinRequest::updateOrCreate(
            ['live_stream_id' => $stream->id, 'user_id' => $user->id],
            ['status' => 'pending']
        );

        $hostId = $request->input('host_id') ?? $stream->host_id;

        $requestPayload = [
            'request_id'     => $joinReq->id,
            'room_id'        => (string) $stream->id,
            'room_name'      => $stream->channel_name ?: (string) $stream->id,
            'live_stream_id' => $stream->id,
            'host_id'        => (int) $hostId,
            'user_id'        => $user->id,
            'name'           => $user->display_name ?? $user->name,
            'avatar'         => $user->avatar_url ?? $user->avatar ?? null,
            'user'           => [
                'id'           => $user->id,
                'account_id'   => $user->account_id,
                'display_name' => $user->display_name ?? $user->name,
                'name'         => $user->display_name ?? $user->name,
                'avatar_url'   => $user->avatar_url,
                'avatar'       => $user->avatar_url ?? $user->avatar ?? null,
                'gender'       => $user->gender ?: 'female',
                'level'        => $user->level ?: 'Lv1',
            ],
            'status'         => 'pending',
            'created_at'     => now()->toIso8601String(),
        ];

        try {
            broadcast(new CoHostRequestReceived($hostId, [
                'user_id'    => auth()->id() ?? $user->id,
                'name'       => $user->display_name ?? $user->name,
                'avatar'     => $user->avatar_url ?? $user->avatar ?? null,
                'request_id' => $joinReq->id,
                'room_id'    => (string) $stream->id,
                'room_name'  => $stream->channel_name ?: (string) $stream->id,
            ]))->toOthers();
            broadcast(new CoHostRequestReceived($hostId, $requestPayload));
            event(new LiveJoinRequested($stream->id, $hostId, $requestPayload));
        } catch (\Throwable $e) {
            Log::warning('CoHostRequestReceived broadcast failed: ' . $e->getMessage());
        }

        return response()->json([
            'status'  => true,
            'message' => 'Co-host request sent to host successfully',
            'data'    => $requestPayload,
        ], 200);
    }

    /**
     * Dedicated Accept Join Endpoint
     * POST /api/live/accept-join
     */
    public function acceptJoin(Request $request): JsonResponse
    {
        $request->merge(['action' => 'accept']);
        return $this->respondRequest($request);
    }

    /**
     * 3. Host Accept or Reject Co-Host Request
     * POST /api/live/respond-request
     */
    public function respondRequest(Request $request): JsonResponse
    {
        $host = $this->resolveUser($request) ?? auth()->user();
        $requestId = $request->input('request_id');
        $action = strtolower($request->input('action', 'accept')); // 'accept' or 'reject'

        $joinReq = null;
        if ($requestId) {
            $joinReq = LiveJoinRequest::with(['liveStream', 'user'])->find($requestId);
        }

        if (!$joinReq && $request->filled('user_id')) {
            $targetUserId = $request->input('user_id');
            $roomId = $request->input('room_id') ?? $request->input('live_stream_id');
            $joinReqQuery = LiveJoinRequest::with(['liveStream', 'user'])
                ->where('user_id', $targetUserId);
            if ($roomId) {
                $joinReqQuery->where(function($q) use ($roomId) {
                    $q->where('live_stream_id', $roomId)
                      ->orWhereHas('liveStream', fn($sq) => $sq->where('channel_name', $roomId));
                });
            }
            $joinReq = $joinReqQuery->latest()->first();
        }

        if (!$joinReq) {
            return response()->json(['status' => false, 'message' => 'Join request not found.'], 404);
        }

        $stream = $joinReq->liveStream;
        $guestUser = $joinReq->user;
        $roomName = $stream ? ($stream->channel_name ?: (string) $stream->id) : 'live_room';

        $guestToken = null;

        if ($action === 'accept') {
            $joinReq->update(['status' => 'accepted']);
            if ($stream) {
                LiveParticipant::updateOrCreate(
                    ['live_stream_id' => $stream->id, 'user_id' => $guestUser->id],
                    ['role' => 'guest', 'joined_at' => now(), 'left_at' => null, 'video_enabled' => true]
                );
            }

            // Generate LiveKit token with canPublish = true for co-host
            $apiKey = config('services.livekit.api_key', env('LIVEKIT_API_KEY', 'APIVbeXzKatSo3u'));
            $apiSecret = config('services.livekit.api_secret', env('LIVEKIT_API_SECRET', 'thzlQ2sYGQQIxBPQMkjO9Rres6xuuMsqweZdT61XNsK'));
            $livekitUrl = config('services.livekit.url', env('LIVEKIT_URL', 'wss://chinchins.live/livekit'));

            $token = new AccessToken($apiKey, $apiSecret);
            $grant = new VideoGrant();
            $grant->setRoomJoin(true)
                  ->setRoomName($roomName)
                  ->setCanPublish(true)      // কো-হোস্টের জন্য canPublish: true
                  ->setCanSubscribe(true)
                  ->setCanPublishData(true);

            $tokenOptions = (new AccessTokenOptions())
                ->setIdentity((string) $guestUser->id)
                ->setName($guestUser->display_name ?? $guestUser->name ?? "User_{$guestUser->id}")
                ->setTtl(86400);

            $token->init($tokenOptions);
            $token->setGrant($grant);

            $guestToken = [
                'token'         => $token->toJwt(),
                'livekit_token' => $token->toJwt(),
                'room_name'     => $roomName,
                'role'          => 'co_host',
                'can_publish'   => true,
                'livekit_url'   => $livekitUrl,
            ];

            // Dispatch CoHostRequestAccepted & CoHostAcceptedEvent
            $acceptedPayload = [
                'room_name'     => $roomName,
                'room_id'       => (string) ($stream ? $stream->id : ''),
                'can_publish'   => true,
                'user_id'       => $guestUser->id,
                'name'          => $guestUser->display_name ?? $guestUser->name,
                'avatar'        => $guestUser->avatar_url ?? $guestUser->avatar ?? null,
                'request_id'    => $joinReq->id,
                'token'         => $guestToken['token'],
                'livekit_token' => $guestToken['token'],
                'livekit_url'   => $livekitUrl,
            ];

            try {
                broadcast(new CoHostRequestAccepted($guestUser->id, [
                    'room_name'   => $roomName,
                    'room_id'     => (string) ($stream ? $stream->id : ''),
                    'can_publish' => true,
                    'token'       => $guestToken['token'],
                    'livekit_url' => $livekitUrl,
                ]));
                broadcast(new CoHostRequestAccepted($guestUser->id, $acceptedPayload))->toOthers();
                if ($stream) {
                    event(new CoHostAcceptedEvent($stream->id, $guestUser->id, $acceptedPayload));
                    broadcast(new CoHostStatusEvent($stream->id, 'accept', $guestUser))->toOthers();
                }
            } catch (\Throwable $e) {
                Log::warning('CoHostRequestAccepted broadcast failed: ' . $e->getMessage());
            }
        } else {
            $joinReq->update(['status' => 'rejected']);
            if ($stream) {
                try {
                    broadcast(new CoHostStatusEvent($stream->id, 'reject', $guestUser))->toOthers();
                } catch (\Throwable $e) {}
            }
        }

        $responsePayload = [
            'request_id'     => $joinReq->id,
            'room_id'        => (string) ($stream ? $stream->id : ''),
            'room_name'      => $roomName,
            'guest_user_id'  => $guestUser->id,
            'status'         => $joinReq->status,
            'action'         => $action,
            'can_publish'    => ($action === 'accept'),
            'guest_token'    => $guestToken,
        ];

        if ($stream) {
            try {
                event(new LiveJoinResponded($stream->id, $guestUser->id, $responsePayload));
            } catch (\Throwable $e) {}
        }

        return response()->json([
            'status'  => true,
            'message' => "Co-host request {$action}ed successfully",
            'data'    => $responsePayload,
        ], 200);
    }


    /**
     * 4. Send Real-Time Live Chat Message
     * POST /api/live/send-message
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request) ?? auth()->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $request->validate([
            'message' => 'required|string',
        ]);

        $streamId = $request->input('room_id') ?? $request->input('room_name') ?? $request->input('live_stream_id') ?? $request->input('id') ?? '1';
        $messageText = trim($request->input('message'));

        $stream = LiveStream::where('id', $streamId)->orWhere('channel_name', $streamId)->first();
        $roomId = $stream ? (string) $stream->id : (string) $streamId;

        $msgRecord = LiveMessage::create([
            'live_stream_id' => (int) $roomId,
            'user_id'        => $user->id,
            'message'        => $messageText,
            'type'           => 'text',
            'metadata'       => [
                'sender_name'   => $user->display_name ?? $user->name,
                'sender_avatar' => $user->avatar_url,
                'level'         => $user->level ?: 'Lv1',
            ],
        ]);

        $messagePayload = [
            'id'         => $msgRecord->id,
            'room_id'    => (string) $roomId,
            'user_id'    => $user->id,
            'user_name'  => $user->display_name ?? $user->name,
            'message'    => $messageText,
            'user'       => [
                'id'           => $user->id,
                'display_name' => $user->display_name ?? $user->name,
                'avatar_url'   => $user->avatar_url,
                'level'        => $user->level ?: 'Lv1',
            ],
            'created_at' => now()->toDateTimeString(),
            'timestamp'  => now()->toIso8601String(),
        ];

        try {
            event(new LiveChatMessageEvent($roomId, $messagePayload));
            event(new LiveMessageSent($roomId, $messagePayload));
        } catch (\Throwable $e) {}

        return response()->json([
            'status'  => true,
            'message' => 'Live message sent successfully',
            'data'    => $messagePayload,
        ], 200);
    }

    /**
     * 5. Get List of Co-Host Join Requests for Host
     * GET/POST /api/live/join-requests
     */
    public function getJoinRequests(Request $request): JsonResponse
    {
        $host = $this->resolveUser($request) ?? auth()->user();
        if (!$host) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $roomId = $request->input('room_id') ?? $request->input('room_name') ?? $request->input('live_stream_id') ?? $request->input('id');
        $stream = LiveStream::where('id', $roomId)->orWhere('channel_name', $roomId)->first();

        if (!$stream) {
            $stream = LiveStream::where('host_id', $host->id)->whereIn('status', ['live', 'active'])->latest()->first();
        }

        if (!$stream) {
            return response()->json([
                'status'   => true,
                'message'  => 'No active live stream found',
                'data'     => [],
                'requests' => [],
            ], 200);
        }

        $requests = LiveJoinRequest::with('user')
            ->where('live_stream_id', $stream->id)
            ->whereIn('status', ['pending', 'accepted'])
            ->orderByRaw("FIELD(status, 'pending', 'accepted')")
            ->latest()
            ->get()
            ->map(function ($req) {
                $u = $req->user;
                return [
                    'request_id'   => $req->id,
                    'id'           => $req->id,
                    'user_id'      => $req->user_id,
                    'status'       => $req->status, // 'pending' or 'accepted'
                    'user_name'    => $u?->display_name ?? $u?->name ?? "User_{$req->user_id}",
                    'display_name' => $u?->display_name ?? $u?->name ?? "User_{$req->user_id}",
                    'avatar_url'   => $u?->avatar_url ?? 'https://chinchins.live/default-avatar.png',
                    'level'        => $u?->level ?? 'Lv1',
                    'gender'       => $u?->gender ?? 'female',
                    'created_at'   => $req->created_at?->toIso8601String(),
                ];
            });

        return response()->json([
            'status'   => true,
            'message'  => 'Join requests retrieved successfully',
            'room_id'  => (string) $stream->id,
            'data'     => $requests,
            'requests' => $requests,
        ], 200);
    }

    /**
     * 6. Host Kicks / Removes a Co-Host
     * POST /api/live/kick-guest
     */
    public function kickGuest(Request $request): JsonResponse
    {
        $host = $this->resolveUser($request) ?? auth()->user();
        $guestUserId = $request->input('guest_user_id') ?? $request->input('user_id');
        $requestId = $request->input('request_id');
        $roomId = $request->input('room_id') ?? $request->input('live_stream_id');

        $stream = LiveStream::where('id', $roomId)->orWhere('channel_name', $roomId)->first();
        if (!$stream && $host) {
            $stream = LiveStream::where('host_id', $host->id)->whereIn('status', ['live', 'active'])->latest()->first();
        }

        if ($stream && $guestUserId) {
            LiveParticipant::where('live_stream_id', $stream->id)
                ->where('user_id', $guestUserId)
                ->update(['left_at' => now()]);

            LiveJoinRequest::where('live_stream_id', $stream->id)
                ->where('user_id', $guestUserId)
                ->update(['status' => 'rejected']);

            try {
                $guestUser = User::find($guestUserId);
                broadcast(new CoHostStatusEvent($stream->id, 'reject', $guestUser))->toOthers();
            } catch (\Throwable $e) {}
        } elseif ($requestId) {
            $req = LiveJoinRequest::find($requestId);
            if ($req) {
                $req->update(['status' => 'rejected']);
                LiveParticipant::where('live_stream_id', $req->live_stream_id)
                    ->where('user_id', $req->user_id)
                    ->update(['left_at' => now()]);
            }
        }

        return response()->json([
            'status'  => true,
            'message' => 'Co-host removed successfully',
        ], 200);
    }
}
