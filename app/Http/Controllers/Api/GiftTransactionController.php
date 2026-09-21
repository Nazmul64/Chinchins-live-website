<?php

namespace App\Http\Controllers\Api;

use App\Events\LiveGiftSentEvent;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessGiftHistoryJob;
use App\Models\Gift;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GiftTransactionController extends Controller
{
    /**
     * Resilient user resolver.
     */
    protected function resolveUser(Request $request): ?User
    {
        $token = $request->bearerToken() 
              ?: $request->header('Authorization') 
              ?: $request->input('token') 
              ?: $request->input('auth_token');

        if ($token) {
            $tokenClean = trim(str_replace(['Bearer', 'bearer'], '', $token));
            $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($tokenClean);
            if ($accessToken && $accessToken->tokenable) {
                return $accessToken->tokenable;
            }
        }

        if ($request->user('sanctum')) {
            return $request->user('sanctum');
        }

        if ($request->user()) {
            return $request->user();
        }

        $headerUserId = $request->header('X-User-Id') 
                     ?? $request->header('User-Id') 
                     ?? $request->header('user-id') 
                     ?? $request->header('userId');

        if ($headerUserId) {
            $u = User::find($headerUserId) ?? User::where('account_id', $headerUserId)->first();
            if ($u) return $u;
        }

        $idParam = $request->input('sender_id') ?? $request->input('user_id') ?? $request->input('userId') ?? $request->input('id');
        if ($idParam) {
            $u = User::find($idParam) ?? User::where('account_id', $idParam)->first();
            if ($u) return $u;
        }

        return auth()->user();
    }

    /**
     * Send a gift with Zero-Latency, Atomic Balance Deduction, Real-Time Reverb Broadcast and Background History Logging.
     * POST /api/gifts/send, POST /api/gift/send, POST /api/live/gifts/send
     */
    public function sendGift(Request $request): JsonResponse
    {
        $request->validate([
            'gift_id'     => 'required|integer',
            'receiver_id' => 'required|integer',
            'room_name'   => 'nullable|string',
            'stream_id'   => 'nullable|string',
            'gift_count'  => 'nullable|integer|min:1',
            'quantity'    => 'nullable|integer|min:1',
        ]);

        $sender = $this->resolveUser($request);
        if (!$sender) {
            return response()->json(['success' => false, 'status' => false, 'message' => 'Unauthorized.'], 401);
        }

        $giftCount = (int) ($request->gift_count ?? $request->quantity ?? 1);
        $giftId = (int) $request->gift_id;
        $receiverId = (int) $request->receiver_id;
        $roomName = $request->room_name ?: ($request->stream_id ?: ('stream_' . $receiverId));

        if ($sender->id === $receiverId) {
            return response()->json(['success' => false, 'status' => false, 'message' => 'You cannot send a gift to yourself.'], 400);
        }

        // 1. নির্দিষ্ট গিফটের ডাটা ক্যাশ (Redis/Memory) থেকে ইনস্ট্যান্ট রিড (< 1ms)
        $gift = Cache::remember("gift_item_{$giftId}", 86400, function () use ($giftId) {
            return Gift::select('id', 'name', 'coins', 'coin_price', 'icon_url', 'image', 'animation_url', 'animation_type', 'file_url', 'format')
                ->find($giftId);
        });

        if (!$gift) {
            return response()->json(['success' => false, 'status' => false, 'message' => 'Invalid gift.'], 404);
        }

        $coinPrice = (int) ($gift->coin_price ?: $gift->coins ?: 10);
        $totalCoins = $coinPrice * $giftCount;

        // 2. অ্যাটমিক ব্যালেন্স চেক ও ডিডাক্ট (Race condition ছাড়া সাব-মিলিসেকেন্ড এক্সিকিউশন)
        $affected = DB::table('users')
            ->where('id', $sender->id)
            ->where('coins', '>=', $totalCoins)
            ->decrement('coins', $totalCoins);

        if (!$affected) {
            $currentCoins = (int) (DB::table('users')->where('id', $sender->id)->value('coins') ?? 0);
            return response()->json([
                'success'             => false,
                'status'              => false,
                'code'                => 'INSUFFICIENT_BALANCE',
                'message'             => 'ব্যালেন্স পর্যাপ্ত নয়!',
                'required_coins'      => $totalCoins,
                'current_coins'       => $currentCoins,
                'show_recharge_modal' => true,
            ], 400);
        }

        // 3. প্রাপকের অ্যাকাউন্টে কয়েন যোগ (Atomic Increment)
        DB::table('users')->where('id', $receiverId)->increment('coins', (int) floor($totalCoins * 0.50));

        $remainingCoins = (int) (DB::table('users')->where('id', $sender->id)->value('coins') ?? 0);

        // 4. রিয়েল-টাইম স্ক্রিন অ্যানিমেশন ইভেন্ট (WebSockets / Reverb / LiveKit Data Channel)
        $animationUrl = $gift->animation_url ?: ($gift->file_url ?: $gift->animation_full_url);
        $iconUrl = $gift->icon_url ?: ($gift->image_url ?: $gift->image);

        try {
            broadcast(new LiveGiftSentEvent([
                'room_name'     => $roomName,
                'stream_id'     => $roomName,
                'sender_id'     => $sender->id,
                'sender_name'   => $sender->display_name ?? $sender->name ?? 'User',
                'sender_avatar' => $sender->avatar_url ?? $sender->avatar,
                'receiver_id'   => $receiverId,
                'gift_id'       => $gift->id,
                'gift_name'     => $gift->name,
                'icon_url'      => $iconUrl,
                'animation_url' => $animationUrl,
                'file_url'      => $animationUrl,
                'gift_count'    => $giftCount,
                'quantity'      => $giftCount,
                'total_coins'   => $totalCoins,
            ]))->toOthers();
        } catch (\Throwable $e) {
            Log::warning('Gift broadcast error: ' . $e->getMessage());
        }

        // 5. ট্রানজ্যাকশন হিস্ট্রি ও নোটিফিকেশন ব্যাকগ্রাউন্ড কিউতে হস্তান্তর (UI ব্লকিং হবে না)
        dispatch(new ProcessGiftHistoryJob(
            $sender->id,
            $receiverId,
            $gift->id,
            $giftCount,
            $totalCoins,
            $roomName,
            'live_stream'
        ));

        return response()->json([
            'success'         => true,
            'status'          => true,
            'message'         => 'গিফট সফলভাবে পাঠানো হয়েছে',
            'remaining_coins' => $remainingCoins,
            'remaining_balance' => $remainingCoins,
            'data'            => [
                'remaining_coins' => $remainingCoins,
                'gift_id'         => $gift->id,
                'gift_name'       => $gift->name,
                'total_coins'     => $totalCoins,
                'icon_url'        => $iconUrl,
                'animation_url'   => $animationUrl,
            ],
        ]);
    }
}
