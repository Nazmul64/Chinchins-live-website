<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CoinTransaction;
use App\Models\Reseller;
use App\Models\ResellerChatMessage;
use App\Models\ResellerDeposit;
use App\Models\ResellerSetting;
use App\Models\ResellerTransfer;
use App\Models\ResellerWithdrawal;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ResellerApiController extends Controller
{
    /**
     * Resolve authenticated User from Sanctum or headers/fallback.
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

        $idParam = $request->input('user_id') ?? $request->input('userId') ?? $request->input('id');
        if ($idParam) {
            $u = User::find($idParam);
            if ($u) return $u;
        }

        $accParam = $request->input('account_id') ?? $request->input('accountId');
        if ($accParam) {
            $u = User::where('account_id', $accParam)->first();
            if ($u) return $u;
        }

        return User::first();
    }

    /**
     * Resolve Reseller instance from bearer token or header.
     */
    protected function resolveReseller(Request $request): ?Reseller
    {
        $token = $request->bearerToken() ?: $request->header('Authorization') ?: $request->input('reseller_token');
        if ($token) {
            $tokenClean = trim(str_replace(['Bearer', 'bearer'], '', $token));
            if (class_exists('\Laravel\Sanctum\PersonalAccessToken')) {
                try {
                    $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($tokenClean);
                    if ($accessToken && $accessToken->tokenable instanceof Reseller) {
                        return $accessToken->tokenable;
                    }
                } catch (\Throwable $e) {}
            }
        }

        $resellerId = $request->header('X-Reseller-Id') ?? $request->input('reseller_id');
        if ($resellerId) {
            $r = Reseller::find($resellerId);
            if ($r) return $r;
        }

        return null;
    }

    // ==========================================
    // 👤 Mobile App User Endpoints (Resellers & Chat)
    // ==========================================

    /**
     * List all active Resellers for Mobile App Recharge Screen.
     * GET /api/resellers
     */
    public function getResellers(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        $packageCoins = (int) ($request->input('coins') ?: $request->input('gems') ?: 7560);

        $resellers = Reseller::where('is_active', true)
            ->orderBy('is_online', 'desc')
            ->orderBy('total_sold_coins', 'desc')
            ->get()
            ->map(function ($r) use ($user, $packageCoins) {
                $userUid = $user ? ($user->account_id ?: $user->id) : '266813634';
                $prefillMsg = "Hello! My user ID is {$userUid}. I want to recharge {$packageCoins} gems. How much should I pay? 【GIVE THE BEST DISCOUNT 💎DIAMOND💎】";
                $resellerUid = $r->account_id ?: (string) (595000000 + $r->id);
                $sold = (int) ($r->total_sold_coins ?: 15549000);

                return [
                    'id' => $r->id,
                    'reseller_id' => $resellerUid,
                    'account_id' => $resellerUid,
                    'name' => $r->name,
                    'avatar' => $r->avatar_url,
                    'avatar_url' => $r->avatar_url,
                    'level' => $r->level ?: 'Lv5',
                    'location' => $r->location ?: 'Dhaka, Bangladesh',
                    'age' => (int) ($r->age ?: 25),
                    'gender' => $r->gender ?: 'male',
                    'phone' => $r->phone,
                    'bio' => $r->bio ?: "কয়েন রিচার্জ, হোস্টিং এবং বিভিন্ন ধরণের গিফট ক্রয় করা হয়\nযোগাযোগ " . ($r->phone ?: '০১848340232'),
                    'discount_tag' => $r->discount_tag ?: 'Up To 29%↑',
                    'badge_title' => $r->badge_title ?: 'Diamond Reseller',
                    'sales' => $sold,
                    'sales_diamonds' => $sold,
                    'formatted_sales' => '💎 ' . number_format($sold),
                    'success_rate' => $r->success_rate ?: '91.79%',
                    'is_online' => (bool) $r->is_online,
                    'status_text' => $r->is_online ? 'Online' : 'Offline',
                    'prefill_message' => $prefillMsg,
                ];
            });

        return response()->json([
            'status' => true,
            'message' => 'Resellers retrieved successfully.',
            'default_badge' => ResellerSetting::get('reseller_offer_badge', 'Up To 29%↑'),
            'bottom_sheet' => [
                'header_title' => 'Recharge via Reseller',
                'sub_title' => 'Fast🔥 & flexible🔥 & cheaper option',
                'help_text' => 'Need help?',
                'button_text' => 'Chat',
            ],
            'data' => $resellers,
        ], 200);
    }

    /**
     * Get specific Reseller profile & details.
     * GET /api/resellers/{id}
     */
    public function showReseller(Request $request, $id): JsonResponse
    {
        $reseller = Reseller::findOrFail($id);
        $user = $this->resolveUser($request);
        $packageCoins = (int) ($request->input('coins') ?: $request->input('gems') ?: 7560);
        $userUid = $user ? ($user->account_id ?: $user->id) : '266813634';
        $prefillMsg = "Hello! My user ID is {$userUid}. I want to recharge {$packageCoins} gems. How much should I pay? 【GIVE THE BEST DISCOUNT 💎DIAMOND💎】";
        $resellerUid = $reseller->account_id ?: (string) (595000000 + $reseller->id);
        $sold = (int) ($reseller->total_sold_coins ?: 15549000);

        return response()->json([
            'status' => true,
            'message' => 'Reseller profile retrieved successfully.',
            'data' => [
                'id' => $reseller->id,
                'reseller_id' => $resellerUid,
                'account_id' => $resellerUid,
                'name' => $reseller->name,
                'avatar' => $reseller->avatar_url,
                'avatar_url' => $reseller->avatar_url,
                'level' => $reseller->level ?: 'Lv5',
                'location' => $reseller->location ?: 'Dhaka, Bangladesh',
                'age' => (int) ($reseller->age ?: 25),
                'gender' => $reseller->gender ?: 'male',
                'phone' => $reseller->phone,
                'bio' => $reseller->bio,
                'discount_tag' => $reseller->discount_tag ?: 'Up To 29%↑',
                'badge_title' => $reseller->badge_title ?: 'Diamond Reseller',
                'sales' => $sold,
                'sales_diamonds' => $sold,
                'formatted_sales' => '💎 ' . number_format($sold),
                'success_rate' => $reseller->success_rate ?: '91.79%',
                'is_online' => (bool) $reseller->is_online,
                'status_text' => $reseller->is_online ? 'Online' : 'Offline',
                'prefill_message' => $prefillMsg,
            ]
        ], 200);
    }

    /**
     * Get User <-> Reseller Chat Messages.
     * GET /api/resellers/{id}/messages (or GET /api/reseller/chat/{resellerId})
     */
    public function getChatMessages(Request $request, $resellerId): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User could not be identified.'], 401);
        }

        $reseller = Reseller::findOrFail($resellerId);

        // Mark unread messages from reseller as read
        ResellerChatMessage::where('reseller_id', $reseller->id)
            ->where('user_id', $user->id)
            ->where('sender_type', 'reseller')
            ->update(['is_read' => true, 'read_at' => now()]);

        $messages = ResellerChatMessage::where('reseller_id', $reseller->id)
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($m) use ($user, $reseller) {
                return [
                    'id' => $m->id,
                    'reseller_id' => $m->reseller_id,
                    'user_id' => $m->user_id,
                    'sender_type' => $m->sender_type, // 'user' | 'reseller' | 'admin'
                    'is_me' => $m->sender_type === 'user',
                    'type' => $m->type, // 'text' | 'image' | 'voice' | 'system' | 'recharge_request' | 'gift'
                    'message' => $m->message,
                    'media_url' => $m->full_media_url,
                    'duration' => $m->duration,
                    'coins_amount' => $m->coins_amount,
                    'is_read' => (bool) $m->is_read,
                    'created_at' => $m->created_at ? $m->created_at->toIso8601String() : null,
                    'formatted_time' => $m->created_at ? $m->created_at->format('h:i A') : '',
                ];
            });

        return response()->json([
            'status' => true,
            'message' => 'Chat messages retrieved successfully.',
            'reseller' => [
                'id' => $reseller->id,
                'name' => $reseller->name,
                'avatar' => $reseller->avatar_url,
                'level' => $reseller->level,
                'is_online' => (bool) $reseller->is_online,
                'status_text' => $reseller->is_online ? 'Online' : 'Offline',
                'discount_tag' => $reseller->discount_tag,
            ],
            'user' => [
                'id' => $user->id,
                'account_id' => $user->account_id,
                'name' => $user->name,
                'avatar' => $user->avatar_url,
                'coins' => (int) $user->coins,
            ],
            'data' => $messages,
        ], 200);
    }

    /**
     * Send Message from User to Reseller.
     * POST /api/reseller/chat/send (or POST /api/resellers/{id}/messages)
     */
    public function sendUserMessage(Request $request, $resellerId = null): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User unauthorized.'], 401);
        }

        $rId = $resellerId ?: $request->input('reseller_id');
        $reseller = Reseller::findOrFail($rId);

        $msgText = $request->input('message');
        $type = $request->input('type', 'text');
        $coinsAmount = $request->filled('coins_amount') ? (int) $request->coins_amount : null;
        $mediaUrl = null;
        $duration = $request->filled('duration') ? (int) $request->duration : null;

        // Handle Image / Screenshot Upload
        if ($request->hasFile('image') || $request->hasFile('media') || $request->hasFile('file') || $request->hasFile('screenshot')) {
            $file = $request->file('image') ?: ($request->file('media') ?: ($request->file('file') ?: $request->file('screenshot')));
            $filename = 'chat_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $destDir = public_path('uploads/reseller');
            if (!file_exists($destDir)) {
                @mkdir($destDir, 0777, true);
            }
            $file->move($destDir, $filename);
            $mediaUrl = 'uploads/reseller/' . $filename;
            $type = 'image';
        }

        // Handle Voice Note Upload
        if ($request->hasFile('voice') || $request->hasFile('audio')) {
            $file = $request->file('voice') ?: $request->file('audio');
            $filename = 'voice_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $destDir = public_path('uploads/reseller');
            if (!file_exists($destDir)) {
                @mkdir($destDir, 0777, true);
            }
            $file->move($destDir, $filename);
            $mediaUrl = 'uploads/reseller/' . $filename;
            $type = 'voice';
        }

        if (empty($msgText) && empty($mediaUrl)) {
            return response()->json(['status' => false, 'message' => 'Message or attachment is required.'], 422);
        }

        $chatMsg = ResellerChatMessage::create([
            'reseller_id' => $reseller->id,
            'user_id' => $user->id,
            'sender_type' => 'user',
            'sender_id' => $user->id,
            'type' => $type,
            'message' => $msgText,
            'media_url' => $mediaUrl,
            'duration' => $duration,
            'coins_amount' => $coinsAmount,
            'target_account_id' => $user->account_id ?: (string) $user->id,
            'is_read' => false,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Message sent successfully.',
            'data' => [
                'id' => $chatMsg->id,
                'reseller_id' => $chatMsg->reseller_id,
                'user_id' => $chatMsg->user_id,
                'sender_type' => 'user',
                'is_me' => true,
                'type' => $chatMsg->type,
                'message' => $chatMsg->message,
                'media_url' => $chatMsg->full_media_url,
                'duration' => $chatMsg->duration,
                'coins_amount' => $chatMsg->coins_amount,
                'is_read' => false,
                'created_at' => $chatMsg->created_at->toIso8601String(),
                'formatted_time' => $chatMsg->created_at->format('h:i A'),
            ]
        ], 201);
    }

    /**
     * Upload Media file for Chat (Photo or Voice).
     * POST /api/reseller/chat/upload
     */
    public function uploadMedia(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthorized.'], 401);
        }

        $request->validate([
            'file' => 'required|file|max:15360', // max 15MB
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        $isAudio = in_array($ext, ['mp3', 'wav', 'aac', 'm4a', 'ogg']);
        $prefix = $isAudio ? 'voice_' : 'img_';

        $filename = $prefix . time() . '_' . Str::random(6) . '.' . $ext;
        $destDir = public_path('uploads/reseller');
        if (!file_exists($destDir)) {
            @mkdir($destDir, 0777, true);
        }
        $file->move($destDir, $filename);
        $relativePath = 'uploads/reseller/' . $filename;

        return response()->json([
            'status' => true,
            'message' => 'Media uploaded successfully.',
            'media_url' => asset($relativePath),
            'relative_path' => $relativePath,
            'type' => $isAudio ? 'voice' : 'image',
        ], 200);
    }

    /**
     * User sends Coin Gift to Reseller in Chat.
     * POST /api/reseller/chat/gift
     */
    public function sendGiftToReseller(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthorized.'], 401);
        }

        $request->validate([
            'reseller_id' => 'required|exists:resellers,id',
            'coins' => 'required|integer|min:1',
            'gift_name' => 'nullable|string|max:100',
        ]);

        $coins = (int) $request->coins;
        $reseller = Reseller::findOrFail($request->reseller_id);

        if (($user->coins ?? 0) < $coins) {
            return response()->json([
                'status' => false,
                'message' => 'Insufficient coins balance! Please recharge.',
                'current_coins' => (int) $user->coins,
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Deduct user coins
            $user->coins -= $coins;
            $user->save();

            // Credit Reseller
            $reseller->coins_balance += $coins;
            $reseller->save();

            $giftName = $request->gift_name ?: 'Diamond Gift';

            // Send chat message
            $msg = ResellerChatMessage::create([
                'reseller_id' => $reseller->id,
                'user_id' => $user->id,
                'sender_type' => 'user',
                'sender_id' => $user->id,
                'type' => 'gift',
                'message' => "🎁 Sent you a gift: {$giftName} ({$coins} Gems)!",
                'coins_amount' => $coins,
                'is_read' => false,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => "Gift sent to {$reseller->name} successfully!",
                'user_remaining_coins' => (int) $user->coins,
                'data' => $msg,
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Failed to send gift: ' . $e->getMessage()], 500);
        }
    }

    // ==========================================
    // 🏢 Reseller App / Web REST API Endpoints
    // ==========================================

    /**
     * Reseller App / Web API Login.
     * POST /api/reseller/login
     */
    public function resellerLogin(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $reseller = Reseller::where('email', strtolower(trim($request->email)))->first();

        if (!$reseller || !Hash::check($request->password, $reseller->password)) {
            return response()->json(['status' => false, 'message' => 'Invalid email or password.'], 401);
        }

        if (!$reseller->is_active) {
            return response()->json(['status' => false, 'message' => 'Reseller account is disabled by Admin.'], 403);
        }

        $reseller->update(['is_online' => true, 'last_seen_at' => now()]);
        $token = $reseller->createToken('reseller_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'token' => $token,
            'token_type' => 'Bearer',
            'reseller' => [
                'id' => $reseller->id,
                'name' => $reseller->name,
                'email' => $reseller->email,
                'phone' => $reseller->phone,
                'avatar' => $reseller->avatar_url,
                'level' => $reseller->level,
                'location' => $reseller->location,
                'coins_balance' => (int) $reseller->coins_balance,
                'total_sold_coins' => (int) $reseller->total_sold_coins,
                'discount_tag' => $reseller->discount_tag,
            ]
        ], 200);
    }

    /**
     * Validate User ID by Reseller.
     * POST /api/reseller/validate-user
     */
    public function apiValidateUser(Request $request): JsonResponse
    {
        $identifier = trim($request->input('account_id') ?: $request->input('user_id'));

        if (!$identifier) {
            return response()->json(['status' => false, 'message' => 'User ID is required.'], 400);
        }

        $user = User::where('account_id', $identifier)
            ->orWhere('id', $identifier)
            ->orWhere('phone', $identifier)
            ->first();

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User not found.'], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'User found',
            'user' => [
                'id' => $user->id,
                'account_id' => $user->account_id,
                'name' => $user->name,
                'avatar' => $user->avatar_url,
                'level' => $user->level,
                'coins' => (int) $user->coins,
                'country' => $user->country,
            ]
        ], 200);
    }

    /**
     * Transfer Coins to User via API.
     * POST /api/reseller/transfer-coins
     */
    public function apiTransferCoins(Request $request): JsonResponse
    {
        $reseller = $this->resolveReseller($request);
        if (!$reseller) {
            return response()->json(['status' => false, 'message' => 'Reseller authentication required.'], 401);
        }

        $request->validate([
            'target_account_id' => 'required|string',
            'coins' => 'required|integer|min:10',
            'amount_bdt' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|max:50',
            'transaction_id' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:255',
        ]);

        $identifier = trim($request->target_account_id);
        $coins = (int) $request->coins;

        if ($reseller->coins_balance < $coins) {
            return response()->json([
                'status' => false,
                'message' => "Insufficient coin stock! You only have " . number_format($reseller->coins_balance) . " gems available.",
                'available_coins' => (int) $reseller->coins_balance,
            ], 400);
        }

        $user = User::where('account_id', $identifier)
            ->orWhere('id', $identifier)
            ->orWhere('phone', $identifier)
            ->first();

        if (!$user) {
            return response()->json(['status' => false, 'message' => "User '{$identifier}' not found."], 404);
        }

        DB::beginTransaction();
        try {
            // Deduct from Reseller
            $reseller->coins_balance -= $coins;
            $reseller->total_sold_coins += $coins;
            $reseller->save();

            // Credit to User
            $user->coins = ($user->coins ?? 0) + $coins;
            $user->save();

            // Record Transfer
            $transfer = ResellerTransfer::create([
                'reseller_id' => $reseller->id,
                'user_id' => $user->id,
                'target_account_id' => $user->account_id ?: (string) $user->id,
                'coins' => $coins,
                'amount_bdt' => $request->amount_bdt,
                'payment_method' => $request->payment_method ?: 'bKash',
                'transaction_id' => $request->transaction_id,
                'notes' => $request->notes,
                'status' => 'completed',
            ]);

            // Log Coin Transaction
            try {
                CoinTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'credit',
                    'amount' => $coins,
                    'description' => "Coins recharged by Reseller: {$reseller->name}",
                ]);
            } catch (\Throwable $cte) {}

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => "Successfully transferred " . number_format($coins) . " gems to {$user->name}!",
                'data' => [
                    'transfer_id' => $transfer->id,
                    'coins_transferred' => $coins,
                    'user_account_id' => $user->account_id,
                    'user_name' => $user->name,
                    'reseller_remaining_coins' => (int) $reseller->coins_balance,
                ]
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Transfer failed: ' . $e->getMessage()], 500);
        }
    }
}
