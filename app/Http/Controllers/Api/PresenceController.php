<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserPresence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PresenceController extends Controller
{
    /**
     * Resolve user from Bearer token, custom headers, or body/query params.
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
                     ?? $request->header('Account-Id')
                     ?? $request->header('X-Phone');

        if ($headerUserId) {
            $u = User::find($headerUserId) ?? User::where('account_id', $headerUserId)->orWhere('phone', $headerUserId)->first();
            if ($u) return $u;
        }

        // 4. Fallback: user_id, userId, caller_id, sender_id, account_id in request body / query
        $idParam = $request->input('user_id') 
                ?? $request->input('userId') 
                ?? $request->input('id');
        if ($idParam) {
            $u = User::find($idParam);
            if ($u) return $u;
        }

        $accParam = $request->input('account_id') ?? $request->input('accountId');
        if ($accParam) {
            $u = User::where('account_id', $accParam)->first();
            if ($u) return $u;
        }

        return null;
    }

    /**
     * Send Heartbeat / Ping (Client app calls this every 30-60 seconds while open).
     * Automatically keeps user marked as 'Online' in the live database.
     * POST /api/user/heartbeat (or POST /api/presence/heartbeat, POST /api/user/ping)
     */
    public function heartbeat(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated or user not identified.',
            ], 401);
        }

        $status = $request->input('status', 'online');
        $deviceType = $request->input('device_type') ?? $request->header('X-Device-Type', 'android');
        $fcmToken = $request->input('fcm_token') ?? $request->input('device_token');

        $presence = UserPresence::heartbeat($user, [
            'status'       => $status,
            'device_type'  => $deviceType,
            'fcm_token'    => $fcmToken,
            'device_token' => $fcmToken,
        ]);

        $user->refresh();

        return response()->json([
            'status' => true,
            'message' => 'Heartbeat received. User presence updated to Online.',
            'data' => [
                'user_id'      => $user->id,
                'account_id'   => $user->account_id,
                'is_online'    => $user->is_online,
                'status_text'  => $user->status_text,
                'online_status'=> $user->online_status,
                'last_seen_at' => $user->last_seen_at ? $user->last_seen_at->toIso8601String() : now()->toIso8601String(),
            ],
        ], 200);
    }

    /**
     * Manually Update User Online/Offline Status (e.g. Host toggles "Go Offline" / "Go Online" / "Busy").
     * POST /api/user/presence/status (or POST /api/profile/status, POST /api/user/status)
     */
    public function updateStatus(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated or user not identified.',
            ], 401);
        }

        $status = strtolower($request->input('status', ''));
        if (!in_array($status, ['online', 'offline', 'inactive', 'busy', 'in_call'])) {
            // Also support boolean is_online
            if ($request->has('is_online')) {
                $isOnline = filter_var($request->input('is_online'), FILTER_VALIDATE_BOOLEAN);
                $status = $isOnline ? 'online' : 'offline';
            } elseif ($request->has('is_active')) {
                $isActive = filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN);
                $status = $isActive ? 'online' : 'offline';
            } else {
                $status = 'online';
            }
        }

        $now = now();
        $isOnline = in_array($status, ['online', 'busy', 'in_call']);

        $user->update([
            'online_status' => $status,
            'last_seen_at'  => $isOnline ? $now : null,
        ]);

        UserPresence::updateOrCreate(
            ['user_id' => $user->id],
            [
                'status'       => $status,
                'is_online'    => $isOnline,
                'last_seen_at' => $isOnline ? $now : null,
                'ip_address'   => $request->ip(),
                'user_agent'   => $request->userAgent(),
            ]
        );

        $user->refresh();

        return response()->json([
            'status' => true,
            'message' => "User presence changed to '{$user->status_text}'.",
            'data' => [
                'user_id'      => $user->id,
                'account_id'   => $user->account_id,
                'is_online'    => $user->is_online,
                'status_text'  => $user->status_text,
                'online_status'=> $user->online_status,
                'last_seen_at' => $user->last_seen_at ? $user->last_seen_at->toIso8601String() : null,
            ],
        ], 200);
    }

    /**
     * Register or Update Mobile FCM Push Token (for instant Incoming Call Wake-Up).
     * POST /api/user/fcm-token (or POST /api/user/device-token)
     */
    public function updateFcmToken(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated or user not identified.',
            ], 401);
        }

        $token = $request->input('fcm_token') ?? $request->input('device_token') ?? $request->input('token');
        $deviceType = $request->input('device_type') ?? $request->header('X-Device-Type', 'android');

        if (empty($token)) {
            return response()->json([
                'status' => false,
                'message' => 'FCM or device token is required.',
            ], 422);
        }

        $user->update([
            'fcm_token'    => $token,
            'device_token' => $token,
            'device_type'  => $deviceType,
            'last_seen_at' => now(),
            'online_status'=> 'online',
        ]);

        UserPresence::updateOrCreate(
            ['user_id' => $user->id],
            [
                'status'       => 'online',
                'is_online'    => true,
                'last_seen_at' => now(),
                'device_type'  => $deviceType,
                'fcm_token'    => $token,
                'device_token' => $token,
                'ip_address'   => $request->ip(),
                'user_agent'   => $request->userAgent(),
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'Device FCM token registered successfully for incoming call push notifications.',
            'data' => [
                'user_id'     => $user->id,
                'device_type' => $deviceType,
                'fcm_token'   => $token,
            ],
        ], 200);
    }

    /**
     * Get Real-Time Online Presence & Status of a specific user (e.g. "Ruma").
     * GET /api/user/presence/{id} (or GET /api/presence/{id})
     */
    public function getPresence(Request $request, $id = null): JsonResponse
    {
        $targetId = $id ?? $request->input('user_id') ?? $request->input('id');
        $accountId = $request->input('account_id');

        $user = User::when($targetId, fn($q) => $q->where('id', $targetId)->orWhere('account_id', $targetId))
            ->when($accountId, fn($q) => $q->where('account_id', $accountId))
            ->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'User presence status retrieved successfully.',
            'data' => [
                'user_id'      => $user->id,
                'account_id'   => $user->account_id,
                'display_name' => $user->display_name,
                'avatar_url'   => $user->avatar_url,
                'is_online'    => $user->is_online,
                'status_text'  => $user->status_text,
                'online_status'=> $user->online_status ?: ($user->is_online ? 'online' : 'inactive'),
                'last_seen_at' => $user->last_seen_at ? $user->last_seen_at->toIso8601String() : null,
                'last_seen_human' => $user->last_seen_at ? $user->last_seen_at->diffForHumans() : 'Never',
                'video_call_rate' => (int) $user->video_call_rate,
                'can_receive_call'=> $user->is_online && !$user->is_locked,
            ],
        ], 200);
    }

    /**
     * Get List of Currently Online Users / Hosts.
     * GET /api/users/online
     */
    public function getOnlineUsers(Request $request): JsonResponse
    {
        $gender = $request->input('gender');
        $perPage = (int) $request->input('per_page', 20);

        $query = User::where('is_active', true)
            ->where('is_locked', false)
            ->where(function ($q) {
                $q->where('last_seen_at', '>=', now()->subMinutes(5))
                  ->orWhereIn('online_status', ['online', 'busy', 'in_call']);
            });

        if ($gender) {
            $query->where('gender', $gender);
        }

        $users = $query->latest('last_seen_at')->paginate($perPage);

        return response()->json([
            'status' => true,
            'message' => 'Online users loaded successfully.',
            'data' => [
                'users'        => $users->items(),
                'total_online' => $users->total(),
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
            ],
        ], 200);
    }
}
