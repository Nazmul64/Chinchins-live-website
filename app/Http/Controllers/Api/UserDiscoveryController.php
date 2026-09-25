<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\LiveStream;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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
            'display_name',
            'nickname',
            'account_id',
            'avatar',
            'avatar_frame',
            'cover_photo',
            'gender',
            'level',
            'country',
            'country_flag',
            'city',
            'is_verified',
            'is_online',
            'online_status',
            'current_status',
            'last_active_at',
            'last_seen_at',
            'video_call_rate',
            'coins',
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

        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 30);
        
        $users = $query->orderBy('is_online', 'desc')
                      ->orderBy('last_seen_at', 'desc')
                      ->paginate($perPage);

        $userIds = collect($users->items())->pluck('id')->toArray();
        $activeLives = LiveStream::whereIn('host_id', $userIds)
            ->whereIn('status', ['live', 'active'])
            ->get()
            ->keyBy('host_id');

        $formatted = collect($users->items())->map(function ($u) use ($activeLives) {
            $live = $activeLives->get($u->id);
            $isLive = !empty($live);
            $isOnline = (bool) $u->is_online;
            $isVerified = (bool) $u->is_verified;

            return [
                'id'              => $u->id,
                'account_id'      => $u->account_id ?: (string) $u->id,
                'name'            => $u->display_name ?: $u->name,
                'display_name'    => $u->display_name ?: $u->name,
                'nickname'        => $u->nickname ?: $u->display_name ?: $u->name,
                'avatar'          => $u->avatar_url,
                'avatar_url'      => $u->avatar_url,
                'cover_photo_url' => $live?->cover_image_url ?: ($u->cover_photo_url ?: $u->avatar_url),
                'gender'          => $u->gender ?: 'female',
                'level'           => $u->display_level,
                'country'         => $u->country ?: 'Bangladesh',
                'country_flag'    => $u->country_flag ?: '🇧🇩',
                'city'            => $u->city ?: 'Dhaka',
                
                // 🔴 Live & Presence States
                'is_live'         => $isLive,
                'is_online'       => $isOnline,
                'is_verified'     => $isVerified,
                'online_status'   => $isLive ? 'in_live' : ($isOnline ? 'online' : 'offline'),
                'status_text'     => $isLive ? 'Live' : ($isOnline ? 'Online' : 'Offline'),

                // 🏷️ Top-Left Live / Online Badge Config
                'live_badge'      => [
                    'label'                => $isLive ? 'Live' : ($isOnline ? 'Online' : 'Offline'),
                    'type'                 => $isLive ? 'live' : ($isOnline ? 'online' : 'offline'),
                    'is_live'              => $isLive,
                    'is_online'            => $isOnline,
                    'sound_wave_animation' => $isLive, // 3 equalizer bars jump when Live
                    'equalizer_bars_count' => 3,
                    'badge_style'          => $isLive ? 'purple_gradient' : ($isOnline ? 'glass_dark' : 'glass_subtle'),
                    'gradient_colors'      => $isLive ? ['#A855F7', '#EC4899'] : ['rgba(0,0,0,0.45)', 'rgba(0,0,0,0.45)'],
                    'dot_color'            => $isLive ? '#FFFFFF' : ($isOnline ? '#22C55E' : '#9CA3AF'),
                    'has_dot'              => !$isLive,
                ],

                // 🛡️ Verified Checkmark Badge Config
                'verified_badge'  => [
                    'is_verified'          => $isVerified,
                    'badge_type'           => 'verified_v',
                    'label'                => 'V',
                    'color'                => '#38BDF8',
                    'icon_url'             => url('assets/images/badges/verified_v.png'),
                ],

                // 🎥 Bottom-Right Floating Video Button & Notch Shape
                'action_button'   => [
                    'type'                 => $isLive ? 'join_live' : 'video_call',
                    'icon'                 => 'video_camera',
                    'is_live'              => $isLive,
                    'is_animating'         => $isLive, // Pulsing wave ripple animation
                    'animation_type'       => $isLive ? 'pulsing_ripple' : 'none',
                    'gradient_colors'      => ['#8B5CF6', '#EC4899'],
                    'shape'                => 'notched_floating_circle',
                    'notch_position'       => 'bottom_right',
                    'notch_radius'         => 28,
                ],

                // 📐 Card Notched Layout Specs
                'card_design'     => [
                    'has_bottom_right_notch' => true,
                    'notch_radius'           => 28,
                    'corner_radius'          => 16,
                    'button_floating_outside'=> true,
                ],

                // 📡 Live Stream Metadata
                'live_stream'     => $live ? [
                    'id'              => $live->id,
                    'room_id'         => (string) $live->id,
                    'channel_name'    => $live->channel_name,
                    'title'           => $live->title,
                    'viewer_count'    => (int) $live->viewer_count,
                    'likes_count'     => (int) ($live->likes_count ?? 0),
                    'cover_image_url' => $live->cover_image_url,
                ] : null,

                'video_call_rate' => (int) ($u->video_call_rate ?: 100),
                'coins'           => (int) $u->coins,
            ];
        });

        return response()->json([
            'status'             => 'success',
            'show_offline_users' => $allowOffline,
            'data'               => $formatted,
            'users'              => $formatted,
            'pagination'         => [
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
                'per_page'     => $users->perPage(),
                'total'        => $users->total(),
            ],
        ], 200)->header('Cache-Control', 'public, max-age=10, stale-while-revalidate=30');
    }
}
