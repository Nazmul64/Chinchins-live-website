<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LiveStream;
use App\Models\User;
use App\Services\LiveKitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserFeedController extends Controller
{
    /**
     * Resolve authenticated or requested user instance.
     */
    protected function resolveUserId(Request $request): ?int
    {
        $token = $request->bearerToken() 
              ?: $request->header('Authorization') 
              ?: $request->input('token') 
              ?: $request->input('auth_token');

        if ($token) {
            $tokenClean = trim(str_replace(['Bearer', 'bearer'], '', $token));
            if (class_exists('\Laravel\Sanctum\PersonalAccessToken')) {
                try {
                    $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($tokenClean);
                    if ($accessToken && $accessToken->tokenable_id) {
                        return (int) $accessToken->tokenable_id;
                    }
                } catch (\Throwable $e) {}
            }
        }

        return Auth::guard('sanctum')->id() 
            ?: $request->user()?->id 
            ?: (int) ($request->header('X-User-Id') ?? $request->header('User-Id') ?? $request->input('user_id'));
    }

    /**
     * High-Performance Paginated Dynamic User Feed (1 to 1,000,000 users).
     * GET /api/users/feed, GET /api/feed/users, GET /api/all-users-feed
     */
    public function getAllUsersFeed(Request $request): JsonResponse
    {
        $currentUserId = $this->resolveUserId($request);
        $perPage = min(100, max(1, (int) $request->input('per_page', 30)));

        $query = User::query()
            ->where('is_active', true)
            ->where('is_locked', false);

        if ($currentUserId) {
            $query->where('id', '!=', $currentUserId);
        }

        if ($request->filled('gender') && !in_array($request->gender, ['all', 'any'])) {
            $query->where('gender', $request->gender);
        }

        if ($request->filled('country') && !in_array(strtoupper($request->country), ['ALL', 'GLOBAL'])) {
            $c = $request->country;
            $query->where(function ($q) use ($c) {
                $q->where('country', 'LIKE', "%{$c}%")
                  ->orWhere('country_code', 'LIKE', "%{$c}%");
            });
        }

        $users = $query->with(['activeLiveStream' => function ($q) {
            $q->select([
                'live_streams.id',
                'live_streams.host_id',
                'live_streams.channel_name',
                'live_streams.title',
                'live_streams.cover_image',
                'live_streams.status',
                'live_streams.viewer_count',
                'live_streams.likes_count'
            ]);
        }])
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        $items = collect($users->items())->map(function ($u) {
            $liveStream = $u->activeLiveStream;
            $isLive = !empty($liveStream);
            $liveRoom = $liveStream ? $liveStream->channel_name : null;
            $liveToken = $isLive ? LiveKitService::generateFastToken($u, $liveRoom, false) : null;

            return [
                'id'              => $u->id,
                'account_id'      => $u->account_id ?: (string) $u->id,
                'name'            => $u->display_name ?? $u->name,
                'display_name'    => $u->display_name ?? $u->name,
                'nickname'        => $u->nickname ?: $u->display_name,
                'avatar'          => $u->avatar_url,
                'avatar_url'      => $u->avatar_url,
                'country_flag'    => $u->country_flag ?: '🇧🇩',
                'country'         => $u->country ?: 'Bangladesh',
                'country_code'    => $u->country_code ?: 'BD',
                'current_level'   => $u->level_number ?: 1,
                'level'           => $u->display_level ?: 'Lv.1',
                'level_number'    => $u->level_number ?: 1,
                'is_online'       => (bool) $u->is_online,
                'is_live'         => $isLive,
                'live_room'       => $liveRoom,
                'live_room_id'    => $liveRoom,
                'live_token'      => $liveToken,
                'live_stream_id'  => $liveStream?->id,
                'video_call_rate' => (int) ($u->video_call_rate ?: 100),
                'coins'           => (int) ($u->coins ?? 0),
                'coins_balance'   => (int) ($u->coins ?? 0),
                'gender'          => $u->gender ?: 'female',
                'age'             => $u->display_age ?: 22,
                'active_live_stream' => $liveStream ? [
                    'id'              => $liveStream->id,
                    'user_id'         => $liveStream->host_id,
                    'host_id'         => $liveStream->host_id,
                    'room_name'       => $liveStream->channel_name,
                    'channel_name'    => $liveStream->channel_name,
                    'status'          => $liveStream->status,
                    'viewer_count'    => (int) $liveStream->viewer_count,
                    'likes_count'     => (int) $liveStream->likes_count,
                    'livekit_url'     => config('services.livekit.url', env('LIVEKIT_URL', 'wss://chinchins.live/livekit')),
                ] : null,
            ];
        });

        return response()->json([
            'success'     => true,
            'status'      => true,
            'message'     => 'User feed loaded successfully',
            'data'        => $items,
            'users'       => $items,
            'has_more'    => $users->hasMorePages(),
            'pagination'  => [
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
                'per_page'     => $users->perPage(),
                'total'        => $users->total(),
                'has_more'     => $users->hasMorePages(),
            ],
        ], 200);
    }

    /**
     * Infinite Global Cursor-Paginated Feed.
     * GET /api/feed/global, GET /api/global-feed
     */
    public function getGlobalFeed(Request $request): JsonResponse
    {
        $currentUserId = $this->resolveUserId($request);
        $perPage = min(100, max(1, (int) $request->input('per_page', 30)));

        $query = User::query()
            ->where('is_active', true)
            ->where('is_locked', false);

        if ($currentUserId) {
            $query->where('id', '!=', $currentUserId);
        }

        $users = $query->with(['activeLiveStream' => function ($q) {
            $q->select([
                'live_streams.id',
                'live_streams.host_id',
                'live_streams.channel_name',
                'live_streams.title',
                'live_streams.cover_image',
                'live_streams.status',
                'live_streams.viewer_count',
                'live_streams.likes_count'
            ]);
        }])
            ->orderBy('id', 'desc')
            ->cursorPaginate($perPage);

        $items = collect($users->items())->map(function ($u) {
            $liveStream = $u->activeLiveStream;
            $isLive = !empty($liveStream);
            $liveRoom = $liveStream ? $liveStream->channel_name : null;
            $liveToken = $isLive ? LiveKitService::generateFastToken($u, $liveRoom, false) : null;

            return [
                'id'              => $u->id,
                'account_id'      => $u->account_id ?: (string) $u->id,
                'name'            => $u->display_name ?? $u->name,
                'display_name'    => $u->display_name ?? $u->name,
                'avatar'          => $u->avatar_url,
                'avatar_url'      => $u->avatar_url,
                'country_flag'    => $u->country_flag ?: '🇧🇩',
                'current_level'   => $u->level_number ?: 1,
                'level'           => $u->display_level ?: 'Lv.1',
                'is_online'       => (bool) $u->is_online,
                'is_live'         => $isLive,
                'live_room'       => $liveRoom,
                'live_room_id'    => $liveRoom,
                'live_token'      => $liveToken,
                'video_call_rate' => (int) ($u->video_call_rate ?: 100),
                'active_live_stream' => $liveStream ? [
                    'id'           => $liveStream->id,
                    'user_id'      => $liveStream->host_id,
                    'host_id'      => $liveStream->host_id,
                    'room_name'    => $liveStream->channel_name,
                    'channel_name' => $liveStream->channel_name,
                    'status'       => $liveStream->status,
                ] : null,
            ];
        });

        return response()->json([
            'success'     => true,
            'status'      => true,
            'data'        => $items,
            'users'       => $items,
            'has_more'    => $users->hasMorePages(),
            'next_cursor' => $users->nextCursor()?->encode(),
            'prev_cursor' => $users->previousCursor()?->encode(),
        ], 200);
    }
}
