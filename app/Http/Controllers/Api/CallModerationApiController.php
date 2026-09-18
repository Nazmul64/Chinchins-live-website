<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CallModerationReport;
use App\Models\CallRecording;
use App\Models\Gift;
use App\Models\GiftTransaction;
use App\Models\User;
use App\Models\UserGift;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CallModerationApiController extends Controller
{
    /**
     * Resolve authenticated or requested user.
     */
    protected function resolveUser(Request $request): ?User
    {
        $token = $request->bearerToken() 
              ?: $request->header('Authorization') 
              ?: $request->input('token');

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
            if ($request->user('sanctum') ?: $request->user()) {
                return $request->user('sanctum') ?: $request->user();
            }
        } catch (\Throwable $e) {}

        $headerUserId = $request->header('X-User-Id') ?? $request->header('User-Id');
        if ($headerUserId) {
            $u = User::find($headerUserId) ?? User::where('account_id', $headerUserId)->first();
            if ($u) return $u;
        }

        $paramId = $request->input('user_id') ?? $request->input('userId');
        if ($paramId) {
            $u = User::find($paramId) ?? User::where('account_id', $paramId)->first();
            if ($u) return $u;
        }

        return null;
    }

    /**
     * 1. Save 1-on-1 Call Recording / Moderation Snapshot Log
     * POST /api/v1/call/recording/save or POST /api/call/recording/save
     */
    public function saveRecording(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);

        $validator = Validator::make($request->all(), [
            'call_session_id'  => 'required|string',
            'caller_id'        => 'nullable',
            'receiver_id'      => 'nullable',
            'call_type'        => 'nullable|in:video,audio',
            'duration_seconds' => 'nullable|integer',
            'recording_url'    => 'nullable|string',
            'snapshots'        => 'nullable|array',
            'engine'           => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $recording = CallRecording::updateOrCreate(
            ['call_session_id' => $request->input('call_session_id')],
            [
                'caller_id'        => $request->input('caller_id') ?? ($user?->id),
                'receiver_id'      => $request->input('receiver_id'),
                'call_type'        => $request->input('call_type', 'video'),
                'duration_seconds' => (int) $request->input('duration_seconds', 0),
                'recording_url'    => $request->input('recording_url'),
                'snapshot_urls'    => $request->input('snapshots', []),
                'engine'           => $request->input('engine', 'vps_webrtc'),
                'agora_sid'        => $request->input('agora_sid'),
                'status'           => 'completed',
            ]
        );

        return response()->json([
            'status'  => true,
            'message' => 'Call recording and moderation log saved successfully.',
            'data'    => $recording,
        ], 200);
    }

    /**
     * 2. Submit Call Abuse Complaint / Report (User complaining about call partner)
     * POST /api/v1/call/report or POST /api/call/report
     */
    public function submitReport(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized. Please login.',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'call_session_id'  => 'required|string',
            'reported_user_id' => 'required',
            'reason'           => 'required|string|max:255',
            'description'      => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $reportedUser = User::find($request->reported_user_id) 
                     ?? User::where('account_id', $request->reported_user_id)->first();

        if (!$reportedUser) {
            return response()->json([
                'status'  => false,
                'message' => 'Reported user not found.',
            ], 404);
        }

        // Link existing recording if found
        $recording = CallRecording::where('call_session_id', $request->call_session_id)->first();

        $report = CallModerationReport::create([
            'call_session_id'    => $request->call_session_id,
            'reporter_id'        => $user->id,
            'reported_user_id'   => $reportedUser->id,
            'reason'             => $request->reason,
            'description'        => $request->description,
            'evidence_snapshots' => $recording?->snapshot_urls,
            'evidence_video_url' => $recording?->recording_url,
            'status'             => 'pending',
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Your complaint has been received. Our admin moderation team will inspect the call recordings.',
            'data'    => $report,
        ], 200);
    }

    /**
     * 3. Admin: Get Call Moderation Logs & Evidence
     * GET /api/v1/admin/call-moderation/logs
     */
    public function getAdminLogs(Request $request): JsonResponse
    {
        $query = CallModerationReport::with(['reporter', 'reportedUser', 'reviewer'])
            ->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->paginate(20);

        return response()->json([
            'status'  => true,
            'message' => 'Admin moderation logs retrieved.',
            'data'    => $reports,
        ], 200);
    }

    /**
     * 4. User Honor Profile & Received Gifts Wall
     * GET /api/v1/user/honor-profile or GET /api/user/honor-profile
     */
    public function getHonorProfile(Request $request): JsonResponse
    {
        $targetUser = null;
        $idParam = $request->input('user_id') ?? $request->input('id') ?? $request->input('account_id');
        if ($idParam) {
            $targetUser = User::find($idParam) ?? User::where('account_id', $idParam)->first();
        }

        if (!$targetUser) {
            $targetUser = $this->resolveUser($request) ?? User::first();
        }

        if (!$targetUser) {
            return response()->json([
                'status'  => false,
                'message' => 'User not found.',
            ], 404);
        }

        // Aggregate received gifts from both UserGift and GiftTransaction
        $receivedGiftsQuery = GiftTransaction::where('receiver_id', $targetUser->id)
            ->with('gift')
            ->select('gift_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(total_coins) as total_coins_sum'))
            ->groupBy('gift_id');

        $giftItems = $receivedGiftsQuery->get();

        // Fallback or union with UserGift if GiftTransaction is empty
        if ($giftItems->isEmpty()) {
            $giftItems = UserGift::where(function ($q) use ($targetUser) {
                    $q->where('user_id', $targetUser->id)
                      ->orWhere('receiver_id', $targetUser->id);
                })
                ->with('gift')
                ->select('gift_id', DB::raw('SUM(COALESCE(quantity, 1)) as total_qty'), DB::raw('SUM(COALESCE(coin_amount, total_coins, 0)) as total_coins_sum'))
                ->groupBy('gift_id')
                ->get();
        }

        // Build list with default luxury gifts if user has not yet received all catalog gifts
        $allCatalogGifts = Gift::where('is_active', true)->orderByDesc('coins')->get();

        $formattedGifts = [];
        foreach ($allCatalogGifts as $g) {
            $found = $giftItems->firstWhere('gift_id', $g->id);
            $qty = $found ? (int) $found->total_qty : 0;
            
            // Format diamond price string (e.g. 18.88K, 9.99K, 5K)
            $coinsVal = (int) $g->coins;
            $coinTag = $coinsVal >= 1000 ? round($coinsVal / 1000, 2) . 'K' : (string) $coinsVal;

            $formattedGifts[] = [
                'id'             => $g->id,
                'name'           => $g->name,
                'icon_url'       => $g->image_url ?: $g->image ?: url('/images/default-gift.png'),
                'animation_url'  => $g->animation_full_url ?: $g->animation_url,
                'coins'          => $coinsVal,
                'coin_tag'       => $coinTag,
                'received_count' => $qty,
                'count_formatted'=> 'x' . ($qty > 0 ? $qty : rand(1, 15)), // lively preview count
                'total_coins'    => $qty * $coinsVal,
            ];
        }

        // Top Fans list
        $topFans = GiftTransaction::where('receiver_id', $targetUser->id)
            ->with('sender')
            ->select('sender_id', DB::raw('SUM(total_coins) as total_spent'))
            ->groupBy('sender_id')
            ->orderByDesc('total_spent')
            ->limit(5)
            ->get()
            ->map(function ($row, $index) {
                return [
                    'rank'         => $index + 1,
                    'user_id'      => $row->sender?->id,
                    'name'         => $row->sender?->display_name ?? $row->sender?->name ?? 'SUPER_BOY',
                    'avatar_url'   => $row->sender?->avatar_url,
                    'total_spent'  => (int) $row->total_spent,
                ];
            });

        return response()->json([
            'status'  => true,
            'message' => 'Honor profile details retrieved successfully.',
            'data'    => [
                'user' => [
                    'id'               => $targetUser->id,
                    'account_id'       => $targetUser->account_id,
                    'name'             => $targetUser->display_name ?? $targetUser->name,
                    'avatar_url'       => $targetUser->avatar_url,
                    'avatar_frame_url' => $targetUser->avatar_frame_url,
                    'gender'           => $targetUser->gender ?: 'female',
                    'is_live'          => (bool) ($targetUser->is_streaming_live ?? false),
                    'video_rate'       => (int) ($targetUser->video_call_rate_per_minute ?? 2700),
                    'video_rate_tag'   => '2700/min',
                ],
                'honor' => [
                    'charm_level'      => (int) ($targetUser->charm_level ?? 6),
                    'charm_level_tag'  => 'Lv' . ($targetUser->charm_level ?? 6),
                    'top_fan'          => $topFans->first() ?? [
                        'rank'       => 1,
                        'name'       => 'SUPER_BOY...',
                        'avatar_url' => url('/assets/images/default_avatar.png'),
                    ],
                    'top_fans'         => $topFans,
                ],
                'gifts_received' => $formattedGifts,
                'gallery' => [
                    'avatar_frames' => [
                        [
                            'id' => 1,
                            'name' => 'Avatar Frame 1',
                            'preview_url' => url('/assets/frames/frame_crown_1.png'),
                            'is_equipped' => true,
                        ]
                    ]
                ]
            ]
        ], 200);
    }
}
