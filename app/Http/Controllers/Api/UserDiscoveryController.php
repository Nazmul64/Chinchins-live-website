<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserDiscoveryController extends Controller
{
    /**
     * Get discovery user list considering admin offline visibility setting.
     * GET /api/v1/users/discovery or GET /api/v1/users/active
     */
    public function index(Request $request): JsonResponse
    {
        $showOfflineSetting = AppSetting::get('show_offline_users', '0');
        $allowOffline = filter_var($showOfflineSetting, FILTER_VALIDATE_BOOLEAN) || $showOfflineSetting === '1' || $showOfflineSetting === 'true';

        $query = User::select([
            'id',
            'name',
            'account_id',
            'avatar',
            'avatar_frame',
            'gender',
            'level',
            'country',
            'is_online',
            'online_status',
            'current_status',
            'last_active_at',
            'last_seen_at',
            'created_at',
        ]);

        if (!$allowOffline) {
            $query->where(function ($q) {
                $q->where('is_online', true)
                  ->orWhere('online_status', 'online')
                  ->orWhere('last_seen_at', '>=', now()->subMinutes(5))
                  ->orWhere('last_active_at', '>=', now()->subMinutes(5));
            });
        }

        $users = $query->orderBy('is_online', 'desc')
                       ->orderBy('last_seen_at', 'desc')
                       ->paginate((int) $request->input('per_page', 30));

        return response()->json([
            'status'             => 'success',
            'show_offline_users' => $allowOffline,
            'data'               => $users,
        ], 200);
    }
}
