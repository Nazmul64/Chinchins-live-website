<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerProfileIcon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CustomerProfileIconApiController extends Controller
{
    /**
     * Get All 10 Customer Profile Icons for "Me" Screen with Zero-Latency Local Storage Ready Payload.
     * GET /api/customer-profile-icons, GET /api/customer-profile/icons, GET /api/v1/customer-profile-icons
     */
    public function index(Request $request): JsonResponse
    {
        // 24-Hour In-Memory Cache with instant refresh capability
        $cacheKey = 'api_customer_profile_icons_v1';
        
        $payload = Cache::remember($cacheKey, 86400, function () {
            $icons = CustomerProfileIcon::where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->get();

            $formattedList = [];
            $iconMap = [];
            $sections = [
                'wallet_bar'    => [],
                'banner_card'   => null,
                'quick_actions' => [],
            ];

            $latestUpdatedAt = 0;

            foreach ($icons as $icon) {
                $time = $icon->updated_at ? $icon->updated_at->timestamp : 0;
                if ($time > $latestUpdatedAt) {
                    $latestUpdatedAt = $time;
                }

                $item = [
                    'id'               => $icon->id,
                    'key'              => $icon->key,
                    'title'            => $icon->title,
                    'subtitle'         => $icon->subtitle,
                    'category'         => $icon->category,
                    'icon_url'         => $icon->icon_url,
                    'default_icon_url' => $icon->default_icon_url,
                    'is_custom'        => $icon->is_custom,
                    'badge_text'       => $icon->badge_text,
                    'badge_color'      => $icon->badge_color,
                    'target_route'     => $icon->target_route,
                    'sort_order'       => (int) $icon->sort_order,
                    'is_active'        => (bool) $icon->is_active,
                    'updated_at'       => $icon->updated_at ? $icon->updated_at->toIso8601String() : null,
                ];

                $formattedList[] = $item;
                $iconMap[$icon->key] = $item;

                if ($icon->category === 'wallet_card') {
                    $sections['wallet_bar'][] = $item;
                } elseif ($icon->category === 'banner_card') {
                    $sections['banner_card'] = $item;
                } else {
                    $sections['quick_actions'][] = $item;
                }
            }

            $versionHash = md5('chinchins_icons_' . count($formattedList) . '_' . $latestUpdatedAt);

            return [
                'version_hash'       => $versionHash,
                'total_icons'        => count($formattedList),
                'cache_ttl_seconds'  => 86400,
                'cache_strategy'     => 'CACHE_FIRST_WITH_ETAG_REVALIDATION',
                'icons'              => $formattedList,
                'map'                => $iconMap,
                'sections'           => $sections,
                'offline_storage'    => [
                    'storage_key'    => 'chinchins_customer_profile_icons_v1',
                    'version'        => $versionHash,
                    'last_synced_at' => now()->toIso8601String(),
                    'data'           => $iconMap,
                ],
            ];
        });

        $clientEtag = $request->header('If-None-Match');
        $etag = '"' . $payload['version_hash'] . '"';

        if ($clientEtag && trim($clientEtag) === $etag) {
            return response()->json(null, 304)->withHeaders([
                'ETag'          => $etag,
                'Cache-Control' => 'public, max-age=86400, stale-while-revalidate=3600',
            ]);
        }

        return response()->json([
            'status'     => true,
            'success'    => true,
            'message'    => 'Customer profile icons retrieved successfully.',
            'timestamp'  => now()->toIso8601String(),
            'data'       => $payload,
        ], 200)->withHeaders([
            'ETag'          => $etag,
            'Cache-Control' => 'public, max-age=86400, stale-while-revalidate=3600',
        ]);
    }

    /**
     * Get single icon by key.
     * GET /api/customer-profile-icons/{key}
     */
    public function getByKey(Request $request, string $key): JsonResponse
    {
        $icon = CustomerProfileIcon::where('key', $key)->where('is_active', true)->first();

        if (!$icon) {
            return response()->json([
                'status'  => false,
                'success' => false,
                'message' => "Profile icon with key '{$key}' not found.",
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'success' => true,
            'data'    => [
                'id'               => $icon->id,
                'key'              => $icon->key,
                'title'            => $icon->title,
                'subtitle'         => $icon->subtitle,
                'category'         => $icon->category,
                'icon_url'         => $icon->icon_url,
                'default_icon_url' => $icon->default_icon_url,
                'is_custom'        => $icon->is_custom,
                'badge_text'       => $icon->badge_text,
                'badge_color'      => $icon->badge_color,
                'target_route'     => $icon->target_route,
            ],
        ], 200)->header('Cache-Control', 'public, max-age=86400');
    }
}
