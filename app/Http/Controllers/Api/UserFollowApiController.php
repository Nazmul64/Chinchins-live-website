<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserFollow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserFollowApiController extends Controller
{
    /**
     * Resolve authenticated user with flexible token and fallback support.
     */
    protected function resolveUser(Request $request): ?User
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
                    if ($accessToken && $accessToken->tokenable) {
                        return $accessToken->tokenable;
                    }
                } catch (\Throwable $e) {}
            }
        }

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

        $headerUserId = $request->header('X-User-Id') 
                     ?? $request->header('User-Id') 
                     ?? $request->header('user-id') 
                     ?? $request->header('userId')
                     ?? $request->header('X-Account-Id');

        if ($headerUserId) {
            $u = User::find($headerUserId) ?? User::where('account_id', $headerUserId)->first();
            if ($u) return $u;
        }

        $idParam = $request->input('auth_user_id') 
                ?? $request->input('follower_id') 
                ?? $request->input('sender_id') 
                ?? $request->input('my_id');
        if ($idParam) {
            $u = User::find($idParam) ?? User::where('account_id', $idParam)->first();
            if ($u) return $u;
        }

        return null;
    }

    /**
     * Follow a target user.
     * POST /api/user/follow or POST /api/follow
     */
    public function follow(Request $request): JsonResponse
    {
        $currentUser = $this->resolveUser($request);
        if (!$currentUser) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated or user not identified.',
            ], 401);
        }

        $targetId = $request->input('user_id') 
                 ?? $request->input('target_id') 
                 ?? $request->input('id') 
                 ?? $request->input('account_id');

        if (!$targetId) {
            return response()->json([
                'status' => false,
                'message' => 'Target user_id is required.',
            ], 422);
        }

        $targetUser = User::find($targetId) ?? User::where('account_id', $targetId)->first();
        if (!$targetUser) {
            return response()->json([
                'status' => false,
                'message' => 'Target user not found.',
            ], 404);
        }

        if ($targetUser->id === $currentUser->id) {
            return response()->json([
                'status' => false,
                'message' => 'You cannot follow yourself.',
            ], 400);
        }

        $source = $request->input('source', 'call');

        // Prevent duplicate follow records
        $follow = UserFollow::firstOrCreate(
            [
                'user_id' => $targetUser->id,
                'follower_id' => $currentUser->id,
            ],
            [
                'source' => $source,
            ]
        );

        $followersCount = UserFollow::where('user_id', $targetUser->id)->count();
        $followingCount = UserFollow::where('follower_id', $currentUser->id)->count();

        return response()->json([
            'status' => true,
            'success' => true,
            'message' => 'Successfully followed ' . ($targetUser->name ?? $targetUser->nickname ?? 'user'),
            'is_following' => true,
            'data' => [
                'target_user_id' => $targetUser->id,
                'target_account_id' => $targetUser->account_id,
                'is_following' => true,
                'target_followers_count' => $followersCount,
                'my_following_count' => $followingCount,
            ],
        ], 200);
    }

    /**
     * Unfollow a target user.
     * POST /api/user/unfollow or POST /api/unfollow
     */
    public function unfollow(Request $request): JsonResponse
    {
        $currentUser = $this->resolveUser($request);
        if (!$currentUser) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated or user not identified.',
            ], 401);
        }

        $targetId = $request->input('user_id') 
                 ?? $request->input('target_id') 
                 ?? $request->input('id') 
                 ?? $request->input('account_id');

        if (!$targetId) {
            return response()->json([
                'status' => false,
                'message' => 'Target user_id is required.',
            ], 422);
        }

        $targetUser = User::find($targetId) ?? User::where('account_id', $targetId)->first();
        if (!$targetUser) {
            return response()->json([
                'status' => false,
                'message' => 'Target user not found.',
            ], 404);
        }

        UserFollow::where('user_id', $targetUser->id)
            ->where('follower_id', $currentUser->id)
            ->delete();

        $followersCount = UserFollow::where('user_id', $targetUser->id)->count();
        $followingCount = UserFollow::where('follower_id', $currentUser->id)->count();

        return response()->json([
            'status' => true,
            'success' => true,
            'message' => 'Successfully unfollowed user.',
            'is_following' => false,
            'data' => [
                'target_user_id' => $targetUser->id,
                'target_account_id' => $targetUser->account_id,
                'is_following' => false,
                'target_followers_count' => $followersCount,
                'my_following_count' => $followingCount,
            ],
        ], 200);
    }

    /**
     * Check follow relationship status.
     * GET /api/user/{id}/follow-status or GET /api/follow/status
     */
    public function status(Request $request, $id = null): JsonResponse
    {
        $currentUser = $this->resolveUser($request);
        $targetId = $id ?? $request->input('user_id') ?? $request->input('id');

        if (!$targetId) {
            return response()->json([
                'status' => false,
                'message' => 'Target user_id is required.',
            ], 422);
        }

        $targetUser = User::find($targetId) ?? User::where('account_id', $targetId)->first();
        if (!$targetUser) {
            return response()->json([
                'status' => false,
                'message' => 'Target user not found.',
            ], 404);
        }

        $isFollowing = false;
        $isFollowedBy = false;

        if ($currentUser) {
            $isFollowing = UserFollow::where('user_id', $targetUser->id)
                ->where('follower_id', $currentUser->id)
                ->exists();

            $isFollowedBy = UserFollow::where('user_id', $currentUser->id)
                ->where('follower_id', $targetUser->id)
                ->exists();
        }

        $followersCount = UserFollow::where('user_id', $targetUser->id)->count();
        $followingCount = UserFollow::where('follower_id', $targetUser->id)->count();

        return response()->json([
            'status' => true,
            'success' => true,
            'data' => [
                'target_user_id' => $targetUser->id,
                'is_following' => $isFollowing,
                'is_followed_by' => $isFollowedBy,
                'is_mutual' => ($isFollowing && $isFollowedBy),
                'followers_count' => $followersCount,
                'following_count' => $followingCount,
            ],
        ], 200);
    }

    /**
     * Get list of followers for a user.
     * GET /api/user/{id}/followers
     */
    public function followers(Request $request, $id = null): JsonResponse
    {
        $userId = $id ?? $request->input('user_id');
        $targetUser = $userId ? (User::find($userId) ?? User::where('account_id', $userId)->first()) : $this->resolveUser($request);

        if (!$targetUser) {
            return response()->json([
                'status' => false,
                'message' => 'User not found.',
            ], 404);
        }

        $currentUser = $this->resolveUser($request);
        $page = (int) $request->input('page', 1);
        $perPage = min((int) $request->input('per_page', 20), 50);

        $followers = UserFollow::where('user_id', $targetUser->id)
            ->with('follower:id,account_id,name,nickname,avatar,gender,age,level,country,country_flag,online_status')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);

        $items = collect($followers->items())->map(function ($follow) use ($currentUser) {
            $u = $follow->follower;
            if (!$u) return null;
            return [
                'id' => $u->id,
                'account_id' => $u->account_id,
                'name' => $u->name ?? $u->nickname ?? 'User',
                'avatar' => $u->avatar_url,
                'gender' => $u->gender,
                'age' => $u->age,
                'level' => $u->level ?? 1,
                'country' => $u->country,
                'country_flag' => $u->country_flag,
                'online_status' => $u->online_status,
                'is_following' => $currentUser ? $currentUser->isFollowing($u->id) : false,
                'followed_at' => $follow->created_at ? $follow->created_at->toIso8601String() : null,
            ];
        })->filter()->values();

        return response()->json([
            'status' => true,
            'success' => true,
            'data' => [
                'total' => $followers->total(),
                'current_page' => $followers->currentPage(),
                'last_page' => $followers->lastPage(),
                'users' => $items,
            ],
        ], 200);
    }

    /**
     * Get list of users followed by a user.
     * GET /api/user/{id}/following
     */
    public function following(Request $request, $id = null): JsonResponse
    {
        $userId = $id ?? $request->input('user_id');
        $targetUser = $userId ? (User::find($userId) ?? User::where('account_id', $userId)->first()) : $this->resolveUser($request);

        if (!$targetUser) {
            return response()->json([
                'status' => false,
                'message' => 'User not found.',
            ], 404);
        }

        $currentUser = $this->resolveUser($request);
        $page = (int) $request->input('page', 1);
        $perPage = min((int) $request->input('per_page', 20), 50);

        $followings = UserFollow::where('follower_id', $targetUser->id)
            ->with('user:id,account_id,name,nickname,avatar,gender,age,level,country,country_flag,online_status')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);

        $items = collect($followings->items())->map(function ($follow) use ($currentUser) {
            $u = $follow->user;
            if (!$u) return null;
            return [
                'id' => $u->id,
                'account_id' => $u->account_id,
                'name' => $u->name ?? $u->nickname ?? 'User',
                'avatar' => $u->avatar_url,
                'gender' => $u->gender,
                'age' => $u->age,
                'level' => $u->level ?? 1,
                'country' => $u->country,
                'country_flag' => $u->country_flag,
                'online_status' => $u->online_status,
                'is_following' => true,
                'followed_at' => $follow->created_at ? $follow->created_at->toIso8601String() : null,
            ];
        })->filter()->values();

        return response()->json([
            'status' => true,
            'success' => true,
            'data' => [
                'total' => $followings->total(),
                'current_page' => $followings->currentPage(),
                'last_page' => $followings->lastPage(),
                'users' => $items,
            ],
        ], 200);
    }
}
