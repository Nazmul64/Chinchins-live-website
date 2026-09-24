<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gift;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CommonDataController extends Controller
{
    /**
     * Get Gifts Catalog with 24-hour In-Memory Caching (Zero DB Hits).
     * GET /api/gifts/catalog, GET /api/gifts
     */
    public function getGiftsCatalog(): JsonResponse
    {
        $catalog = Cache::remember('full_gifts_catalog', 86400, function () {
            return Gift::where('is_active', true)
                ->select('id', 'name', 'coins', 'coin_price', 'icon_url', 'image', 'animation_url', 'file_url', 'category', 'is_broadcast')
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
                        'category'      => $g->category ?: 'all',
                        'category_id'   => $g->category ?: 'all',
                    ];
                });
        });

        return response()->json([
            'success' => true,
            'status'  => true,
            'data'    => $catalog,
            'gifts'   => $catalog,
        ], 200)->header('Cache-Control', 'public, max-age=3600, stale-while-revalidate=86400');
    }

    /**
     * Get Active Payment Gateways & Methods with In-Memory Caching.
     * GET /api/payment/gateways, GET /api/payment-gateways
     */
    public function getPaymentGateways(): JsonResponse
    {
        $gateways = Cache::remember('payment_gateways_list', 86400, function () {
            return PaymentMethod::where('is_active', true)
                ->whereNotIn('code', ['google_play', 'google_pay', 'in_app_purchase', 'play_store', 'google'])
                ->where('name', 'not like', '%google%')
                ->where('name', 'not like', '%play store%')
                ->orderBy('sort_order')
                ->get()
                ->map(function ($pm) {
                    return [
                        'id'             => $pm->id,
                        'name'           => $pm->name,
                        'code'           => $pm->code,
                        'account_type'   => $pm->account_type,
                        'account_number' => $pm->account_number,
                        'instructions'   => $pm->instructions,
                        'icon'           => $pm->icon_url ?: $pm->icon,
                        'rate_coins'     => (int) ($pm->rate_coins ?: 1000),
                        'bonus_coins'    => (int) ($pm->bonus_coins ?: 0),
                        'rate_bdt'       => (float) ($pm->rate_bdt ?: 100.00),
                    ];
                });
        });

        return response()->json([
            'success' => true,
            'status'  => true,
            'data'    => $gateways,
        ], 200)->header('Cache-Control', 'public, max-age=3600, stale-while-revalidate=86400');
    }
}
