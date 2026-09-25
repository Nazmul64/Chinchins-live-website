<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\LiveStream;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LiveAppIconApiController extends Controller
{
    /**
     * Get Live App Badges, Equalizer Wave Icon Specs, Verified Badges & Floating Button Configurations.
     * GET /api/live/app-icons, GET /api/v1/live/app-icons, GET /api/live/icons
     */
    public function getAppIcons(Request $request): JsonResponse
    {
        $iconsData = [
            'live_badge' => [
                'name'                 => 'Live Stream Active Badge',
                'label'                => 'Live',
                'description'          => 'Capsule badge displayed at top-left of live streamer card with 3 animated jumping equalizer bars',
                'badge_style'          => 'purple_gradient',
                'gradient_colors'      => ['#A855F7', '#EC4899'],
                'gradient_direction'   => 'to_right',
                'text_color'           => '#FFFFFF',
                'font_size'            => 11,
                'font_weight'          => 'bold',
                'border_radius'        => 12,
                'padding'              => ['horizontal' => 8, 'vertical' => 3],
                'has_equalizer_waves'  => true,
                'equalizer_bars_count' => 3,
                'animation'            => [
                    'type'             => 'sound_wave_bars',
                    'bar_color'        => '#FFFFFF',
                    'bar_width'        => 2.5,
                    'bar_max_height'   => 11.0,
                    'bar_min_height'   => 3.0,
                    'bar_spacing'      => 1.8,
                    'cycle_duration_ms'=> 650,
                    'delays_ms'        => [0, 180, 360],
                ],
            ],
            'online_badge' => [
                'name'                 => 'Online User Badge',
                'label'                => 'Online',
                'description'          => 'Semi-transparent dark capsule badge displayed at top-left of online user card with glowing green indicator dot',
                'badge_style'          => 'glass_dark',
                'background_color'     => 'rgba(0, 0, 0, 0.45)',
                'border_color'         => 'rgba(255, 255, 255, 0.15)',
                'text_color'           => '#FFFFFF',
                'font_size'            => 11,
                'font_weight'          => 'medium',
                'border_radius'        => 12,
                'padding'              => ['horizontal' => 8, 'vertical' => 3],
                'has_equalizer_waves'  => false,
                'has_status_dot'       => true,
                'dot_color'            => '#22C55E',
                'dot_size'             => 6,
                'dot_glow'             => '0 0 6px rgba(34, 197, 94, 0.8)',
            ],
            'offline_badge' => [
                'name'                 => 'Offline User Badge',
                'label'                => 'Offline',
                'badge_style'          => 'glass_subtle',
                'background_color'     => 'rgba(0, 0, 0, 0.35)',
                'text_color'           => '#9CA3AF',
                'has_status_dot'       => true,
                'dot_color'            => '#9CA3AF',
                'dot_size'             => 6,
            ],
            'verified_badge' => [
                'name'                 => 'Official Host Verified Badge',
                'label'                => 'V',
                'description'          => 'Cyan-blue badge with white checkmark V displayed next to the live badge or username',
                'badge_type'           => 'verified_v',
                'background_color'     => '#38BDF8',
                'gradient_colors'      => ['#38BDF8', '#0284C7'],
                'text_color'           => '#FFFFFF',
                'size'                 => 16,
                'shape'                => 'rounded_circle',
                'icon_svg_path'        => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            ],
            'action_button' => [
                'name'                 => 'Floating Video Call / Live Stream Button',
                'description'          => 'Purple-magenta gradient circular button nested in the bottom-right corner notch cutout with animated pulse waves when host is live',
                'icon'                 => 'video_camera',
                'icon_color'           => '#FFFFFF',
                'gradient_colors'      => ['#8B5CF6', '#EC4899'],
                'button_size'          => 44,
                'shape'                => 'notched_floating_circle',
                'notch_position'       => 'bottom_right',
                'notch_radius'         => 26,
                'animations'           => [
                    'live' => [
                        'is_animated'      => true,
                        'animation_type'   => 'pulsing_ripple_waves',
                        'scale_range'      => [1.0, 1.12],
                        'ripple_rings'     => 2,
                        'ripple_color'     => 'rgba(236, 72, 153, 0.5)',
                        'duration_ms'      => 1200,
                        'repeat'           => 'infinite',
                    ],
                    'online' => [
                        'is_animated'      => false,
                        'animation_type'   => 'static_gradient',
                        'scale'            => 1.0,
                    ],
                ],
            ],
            'card_notch_layout' => [
                'description'          => 'Smooth inward cutout shape on the bottom-right of the user card thumbnail so the video button appears uniquely styled outside/nested',
                'corner_radius'        => 16,
                'notch_corner'         => 'bottom_right',
                'notch_cutout_radius'  => 28,
                'aspect_ratio'         => '3:4',
            ],
        ];

        return response()->json([
            'status'     => true,
            'success'    => true,
            'message'    => 'Live app icons, badges and animation specifications retrieved successfully.',
            'timestamp'  => now()->toIso8601String(),
            'data'       => $iconsData,
        ], 200)->header('Cache-Control', 'public, max-age=300');
    }

    /**
     * Get Hot Streamers & Live Broadcast Cards Feed with Real-Time Badges & Animation Flags.
     * GET /api/live/card-feed, GET /api/v1/live/card-feed, GET /api/hot/feed
     */
    public function getCardFeed(Request $request): JsonResponse
    {
        $showOfflineSetting = AppSetting::get('show_offline_users', '0');
        $canShowOffline = filter_var($showOfflineSetting, FILTER_VALIDATE_BOOLEAN) || $showOfflineSetting === '1' || $showOfflineSetting === 'true';

        $query = User::with(['kycVerification', 'wallet'])
            ->where('is_active', true)
            ->where('is_locked', false);

        if (!$canShowOffline && !$request->has('include_offline') && !$request->has('search')) {
            $query->where('is_online', true);
        }

        // Country filter
        if ($request->filled('country') && !in_array(strtoupper($request->country), ['ALL', 'GLOBAL', 'WORLD', 'ANY'])) {
            $c = trim($request->country);
            $query->where(function ($q) use ($c) {
                $q->where('country', 'LIKE', "%{$c}%")
                  ->orWhere('country_code', 'LIKE', "%{$c}%");
            });
        }

        // Search
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'LIKE', "%{$s}%")
                  ->orWhere('display_name', 'LIKE', "%{$s}%")
                  ->orWhere('nickname', 'LIKE', "%{$s}%")
                  ->orWhere('account_id', 'LIKE', "%{$s}%");
            });
        }

        $query->orderByDesc('is_online')
              ->orderByDesc('last_seen_at')
              ->latest('id');

        $perPage = min((int) $request->input('per_page', 30), 100);
        $users = $query->paginate($perPage);

        $userIds = collect($users->items())->pluck('id')->toArray();
        $activeLives = LiveStream::whereIn('host_id', $userIds)
            ->whereIn('status', ['live', 'active'])
            ->get()
            ->keyBy('host_id');

        $items = collect($users->items())->map(function ($u) use ($activeLives) {
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
                'cover_photo'     => $live?->cover_image_url ?: ($u->cover_photo_url ?: $u->avatar_url),
                'cover_image_url' => $live?->cover_image_url ?: ($u->cover_photo_url ?: $u->avatar_url),
                'gender'          => $u->gender ?: 'female',
                'age'             => $u->display_age,
                'country'         => $u->country ?: 'Bangladesh',
                'country_code'    => $u->country_code ?: 'BD',
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

                // 📡 Live Stream Metadata if currently active
                'live_stream'     => $live ? [
                    'id'              => $live->id,
                    'room_id'         => (string) $live->id,
                    'channel_name'    => $live->channel_name,
                    'title'           => $live->title,
                    'viewer_count'    => (int) $live->viewer_count,
                    'likes_count'     => (int) ($live->likes_count ?? 0),
                    'cover_image_url' => $live->cover_image_url,
                    'agora_token'     => $live->agora_token,
                    'livekit_url'     => config('services.livekit.url', env('LIVEKIT_URL', 'wss://chinchins.live/livekit')),
                ] : null,

                'video_call_rate' => (int) ($u->video_call_rate ?: 100),
                'coins'           => (int) $u->coins,
            ];
        });

        return response()->json([
            'status'     => true,
            'success'    => true,
            'message'    => 'Hot streamers and live broadcast cards loaded successfully.',
            'data'       => $items,
            'streamers'  => $items,
            'users'      => $items,
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
                'per_page'     => $users->perPage(),
                'total'        => $users->total(),
            ],
        ], 200);
    }
}
