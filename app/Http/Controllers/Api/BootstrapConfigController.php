<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\CharmLevelSetting;
use App\Models\CoinPackage;
use App\Models\Gift;
use App\Models\PaymentMethod;
use App\Models\VipPrivilegeCard;
use App\Models\WithdrawalSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BootstrapConfigController extends Controller
{
    /**
     * Get All-In-One Global App Bootstrap Configuration from Redis (Zero DB Hits, < 5ms).
     * Returns payment methods, coin packages, gifts catalog, level badges, VIP frames & app settings.
     * GET /api/bootstrap-config, GET /api/app-config, GET /api/v1/bootstrap
     */
    public function getAppBootstrapData(Request $request): JsonResponse
    {
        $data = Cache::remember('app_global_bootstrap_config', 86400, function () {
            // 1. Payment Methods (bKash, Nagad, Rocket, Upay, etc. - Google Play Excluded)
            $paymentMethods = PaymentMethod::where('is_active', true)
                ->whereNotIn('code', ['google_play', 'google_pay', 'in_app_purchase', 'play_store', 'google'])
                ->where('name', 'not like', '%google%')
                ->where('name', 'not like', '%play store%')
                ->orderBy('sort_order', 'asc')
                ->get()
                ->map(function ($pm) {
                    $rateCoins = (int) ($pm->rate_coins ?: (($pm->rate_per_bdt ?: 10) * 10));
                    $bonusCoins = (int) ($pm->bonus_coins ?: 0);
                    $rateBdt = (float) ($pm->rate_bdt ?: 10.00);
                    return [
                        'id'             => $pm->id,
                        'name'           => $pm->name,
                        'code'           => $pm->code,
                        'account_type'   => $pm->account_type,
                        'account_number' => $pm->account_number,
                        'icon'           => $pm->icon_url ?: $pm->icon,
                        'icon_url'       => $pm->icon_url ?: $pm->icon,
                        'rate_coins'     => $rateCoins,
                        'bonus_coins'    => $bonusCoins,
                        'total_coins'    => $rateCoins + $bonusCoins,
                        'rate_bdt'       => $rateBdt,
                        'instructions'   => $pm->instructions,
                        'offer_tag'      => $pm->offer_tag,
                    ];
                });

            // 2. Coin Packages (Store Diamond Packages with Bonus)
            $coinPackages = CoinPackage::where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->orderBy('id', 'asc')
                ->get()
                ->map(function ($pkg) {
                    $baseCoins = (int) $pkg->coins;
                    $bonusCoins = (int) ($pkg->bonus_coins ?: 0);
                    $price = (float) $pkg->price;
                    return [
                        'id'                 => $pkg->id,
                        'title'              => $pkg->title ?: ($baseCoins . ' Gems Pack'),
                        'coins'              => $baseCoins,
                        'bonus_coins'        => $bonusCoins,
                        'total_coins'        => $baseCoins + $bonusCoins,
                        'price'              => $price,
                        'formatted_price'    => '৳' . number_format($price, 0),
                        'badge'              => $pkg->badge,
                        'badge_color'        => $pkg->badge_color ?: 'pink',
                        'icon_url'           => $pkg->icon_url ?: $pkg->icon_full_url,
                        'animation_url'      => $pkg->animation_url ?: $pkg->animation_full_url,
                        'format'             => $pkg->format ?: 'image',
                        'is_popular'         => (bool) $pkg->is_popular,
                    ];
                });

            // 3. Gifts Catalog (SVGA, MP4, PNG animations)
            $giftsCatalog = Gift::where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->orderBy('coins', 'asc')
                ->get()
                ->map(function ($g) {
                    return [
                        'id'            => $g->id,
                        'name'          => $g->name,
                        'coins'         => (int) ($g->coins ?: $g->coin_price),
                        'coin_price'    => (int) ($g->coin_price ?: $g->coins),
                        'icon_url'      => $g->icon_url ?: ($g->image_url ?: $g->image),
                        'image_url'     => $g->image_url ?: ($g->icon_url ?: $g->image),
                        'animation_url' => $g->animation_url ?: ($g->file_url ?: $g->animation_full_url),
                        'format'        => $g->format ?: ($g->animation_type ?: 'svga'),
                        'display_type'  => $g->display_type ?: ($g->is_broadcast ? 'fullscreen' : 'bubble'),
                        'category'      => $g->category ?: 'all',
                    ];
                });

            // 4. Level Badges / Configurations
            $levelBadges = CharmLevelSetting::orderBy('level', 'asc')->get()->map(function ($lvl) {
                return [
                    'id'               => $lvl->id,
                    'level'            => (int) $lvl->level,
                    'name'             => $lvl->name,
                    'required_coins'   => (int) $lvl->required_coins,
                    'icon_url'         => $lvl->icon_url ?: asset($lvl->icon ?: 'uploads/levels/lv' . $lvl->level . '.svg'),
                    'badge_color'      => $lvl->badge_color ?: '#ff4d88',
                    'privilege_badge'  => $lvl->privilege_badge ?? "Level {$lvl->level}",
                ];
            });

            // Fallback if charm levels table is empty
            if ($levelBadges->isEmpty()) {
                $levelBadges = collect([
                    ['level' => 1, 'name' => 'Charm Lv.1', 'required_coins' => 0, 'icon_url' => asset('uploads/levels/lv1.svg'), 'badge_color' => '#10b981'],
                    ['level' => 2, 'name' => 'Charm Lv.2', 'required_coins' => 1000, 'icon_url' => asset('uploads/levels/lv2.svg'), 'badge_color' => '#3b82f6'],
                    ['level' => 3, 'name' => 'Charm Lv.3', 'required_coins' => 5000, 'icon_url' => asset('uploads/levels/lv3.svg'), 'badge_color' => '#8b5cf6'],
                    ['level' => 4, 'name' => 'Charm Lv.4', 'required_coins' => 20000, 'icon_url' => asset('uploads/levels/lv4.svg'), 'badge_color' => '#f59e0b'],
                    ['level' => 5, 'name' => 'Charm Lv.5', 'required_coins' => 100000, 'icon_url' => asset('uploads/levels/lv5.svg'), 'badge_color' => '#ef4444'],
                ]);
            }

            // 5. VIP Packages / Frames
            $vipFrames = VipPrivilegeCard::where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->get()
                ->map(function ($vip) {
                    return [
                        'id'             => $vip->id,
                        'name'           => $vip->name,
                        'card_type'      => $vip->card_type,
                        'price'          => (float) $vip->price,
                        'validity_days'  => (int) $vip->validity_days,
                        'badge_label'    => $vip->badge_label,
                        'badge_color'    => $vip->badge_color,
                        'avatar_frame'   => $vip->avatar_frame_url ?: $vip->avatar_frame,
                        'avatar_frame_url' => $vip->avatar_frame_url ?: $vip->avatar_frame,
                        'chat_bubble'    => $vip->chat_bubble_url ?: $vip->chat_bubble,
                        'card_icon'      => $vip->card_icon_url ?: $vip->card_icon,
                        'perks'          => $vip->perks ?? [],
                    ];
                });

            // 6. Global App Settings & Live Streaming Drivers
            $appSettings = [
                'app_name'                => AppSetting::get('app_name', 'ChinChins Live'),
                'active_streaming_engine' => AppSetting::get('active_streaming_engine', 'livekit'),
                'active_calling_engine'   => AppSetting::get('active_driver', 'livekit'),
                'livekit_ws_url'          => config('services.livekit.host', env('LIVEKIT_HOST', 'wss://livekit.chinchins.live')),
                'video_call_rate_default' => (int) AppSetting::get('video_call_rate_per_minute', 100),
                'audio_call_rate_default' => (int) AppSetting::get('audio_call_rate_per_minute', 60),
                'free_call_duration'      => (int) AppSetting::get('free_call_duration_seconds', 30),
                'currency'                => 'BDT',
                'currency_symbol'         => '৳',
            ];

            return [
                'payment_methods'     => $paymentMethods,
                'coin_packages'       => $coinPackages,
                'gifts_catalog'       => $giftsCatalog,
                'level_badges'        => $levelBadges,
                'vip_frames'          => $vipFrames,
                'app_settings'        => $appSettings,
                'withdrawal_settings' => WithdrawalSetting::getAllConfig(),
            ];
        });

        return response()->json([
            'success'   => true,
            'status'    => true,
            'message'   => 'Global bootstrap configuration loaded from memory.',
            'timestamp' => time(),
            'data'      => $data,
        ], 200)->header('Cache-Control', 'public, max-age=3600, stale-while-revalidate=86400');
    }

    /**
     * Invalidate Global Bootstrap Cache.
     */
    public static function clearCache(): void
    {
        Cache::forget('app_global_bootstrap_config');
    }
}
