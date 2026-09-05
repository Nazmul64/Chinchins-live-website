<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProfileBase;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileBaseApiController extends Controller
{
    /**
     * Resolve requesting user resiliently across Bearer token, headers, or query params.
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
                     ?? $request->header('X-Account-Id')
                     ?? $request->header('Account-Id');

        if ($headerUserId) {
            $u = User::find($headerUserId) ?? User::where('account_id', $headerUserId)->first();
            if ($u) return $u;
        }

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
     * Get list of all available Level Bases / Avatar Frames and required coins.
     * GET /api/profile-bases, GET /api/levels, GET /api/profile-frames
     */
    public function index(Request $request): JsonResponse
    {
        ProfileBase::seedDefaultBases();

        $bases = ProfileBase::where('is_active', true)
            ->orderBy('level', 'asc')
            ->get()
            ->map(function ($base) {
                return [
                    'id'                  => $base->id,
                    'level'               => (int) $base->level,
                    'name'                => $base->name,
                    'required_coins'      => (int) $base->required_coins,
                    'frame_image_url'     => $base->base_frame_image_url,
                    'base_frame_image'    => $base->base_frame_image,
                    'badge_icon'          => $base->badge_icon ?: 'star',
                    'badge_color'         => $base->badge_color ?: '#f59e0b',
                    'glow_color'          => $base->glow_color ?: 'rgba(245, 158, 11, 0.45)',
                    'privilege_text'      => $base->privilege_text ?: '',
                    'is_active'           => (bool) $base->is_active,
                ];
            });

        return response()->json([
            'status'  => true,
            'message' => 'Level bases and avatar frames fetched successfully.',
            'total'   => $bases->count(),
            'data'    => $bases,
        ], 200);
    }

    /**
     * Get user's current level progression, earned coins, active frame, and next level goals.
     * GET /api/user/level-status, GET /api/profile/level-status, GET /api/level/status
     */
    public function levelStatus(Request $request): JsonResponse
    {
        ProfileBase::seedDefaultBases();

        $user = $this->resolveUser($request);

        if (!$user && $request->filled('user_id')) {
            $user = User::find($request->user_id);
        }

        if (!$user && $request->filled('account_id')) {
            $user = User::where('account_id', $request->account_id)->first();
        }

        $earnedCoins = $user ? $user->total_earned_coins : 0;
        $explicitLevel = $user ? ($user->level ?: 0) : 0;

        $progress = ProfileBase::calculateLevelProgress($earnedCoins, $explicitLevel > 0 ? $explicitLevel : null);
        $allBases = ProfileBase::where('is_active', true)->orderBy('level', 'asc')->get();

        return response()->json([
            'status'  => true,
            'message' => 'User level progression status retrieved successfully.',
            'data'    => [
                'user' => $user ? [
                    'id'                 => $user->id,
                    'account_id'         => $user->display_id,
                    'name'               => $user->name,
                    'nickname'           => $user->display_name,
                    'avatar_url'         => $user->avatar_url,
                    'gender'             => $user->gender,
                    'total_earned_coins' => $earnedCoins,
                ] : null,
                'progression' => [
                    'current_level'               => $progress['current_level'],
                    'level_name'                  => $progress['level_name'],
                    'total_earned_coins'          => $progress['earned_coins'],
                    'coins_for_current_level'     => $progress['coins_for_current_level'],
                    'next_level'                  => $progress['next_level'],
                    'coins_for_next_level'        => $progress['coins_for_next_level'],
                    'coins_needed_to_level_up'    => $progress['coins_needed_to_level_up'],
                    'progress_percentage'         => $progress['progress_percentage'],
                    'is_max_level'                => $progress['is_max_level'],
                    'avatar_frame_url'            => $progress['avatar_frame_url'],
                    'badge_color'                 => $progress['badge_color'],
                    'badge_icon'                  => $progress['badge_icon'],
                    'glow_color'                  => $progress['glow_color'],
                    'privilege_text'              => $progress['privilege_text'],
                ],
                'levels_scale' => $allBases->map(function ($b) use ($progress) {
                    return [
                        'level'            => (int) $b->level,
                        'name'             => $b->name,
                        'required_coins'   => (int) $b->required_coins,
                        'frame_image_url'  => $b->base_frame_image_url,
                        'badge_icon'       => $b->badge_icon,
                        'badge_color'      => $b->badge_color,
                        'privilege_text'   => $b->privilege_text,
                        'is_unlocked'      => $progress['current_level'] >= $b->level,
                        'is_current'       => $progress['current_level'] === (int) $b->level,
                    ];
                }),
            ],
        ], 200);
    }
}
