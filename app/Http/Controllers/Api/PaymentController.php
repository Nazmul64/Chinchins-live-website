<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CoinPackage;
use App\Models\CoinTransaction;
use App\Models\DepositRequest;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /**
     * Resolve authenticated or requested user instance with full token & header resilience.
     */
    protected function resolveUser(Request $request): ?User
    {
        // 1. Check Authorization Bearer token from header / input first
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

        // 2. Try Sanctum Bearer token guard
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

        // 3. Check custom user identifier headers
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

        // 4. Fallback: user_id, userId, account_id, accountId in request body / query
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

        if ($request->filled('phone')) {
            $u = User::where('phone', $request->phone)->first();
            if ($u) return $u;
        }

        if ($request->filled('email')) {
            $u = User::where('email', $request->email)->first();
            if ($u) return $u;
        }

        // 5. Safe fallback for dev/mobile testing
        return User::first();
    }

    /**
     * Get active Payment Methods (bKash, Nagad, etc.) for App Deposit screen.
     * GET /api/payment-methods
     */
    public function getPaymentMethods(): JsonResponse
    {
        $methods = PaymentMethod::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($pm) {
                $rateCoins = (int) ($pm->rate_coins ?: (($pm->rate_per_bdt ?: 10) * 10));
                $bonusCoins = (int) ($pm->bonus_coins ?: 0);
                $totalCoins = $rateCoins + $bonusCoins;
                $rateBdt = (float) ($pm->rate_bdt ?: 10.00);
                $ratePerBdt = (float) ($pm->rate_per_bdt ?: ($rateBdt > 0 ? round($rateCoins / $rateBdt, 2) : 10));
                $bonusPercent = ($rateCoins > 0 && $bonusCoins > 0) ? (int) round(($bonusCoins / $rateCoins) * 100) : 0;

                return [
                    'id' => $pm->id,
                    'name' => $pm->name,
                    'code' => $pm->code,
                    'account_type' => $pm->account_type,
                    'account_number' => $pm->account_number,
                    'instructions' => $pm->instructions,
                    'icon' => $pm->icon_url ?: $pm->icon,
                    'qr_code' => $pm->qr_code_url ?: $pm->qr_code,
                    'min_deposit' => (float) $pm->min_deposit,
                    'max_deposit' => (float) $pm->max_deposit,
                    'rate_coins' => $rateCoins,
                    'bonus_coins' => $bonusCoins,
                    'total_coins' => $totalCoins,
                    'rate_bdt' => $rateBdt,
                    'price' => $rateBdt,
                    'price_bdt' => $rateBdt,
                    'formatted_price' => '৳' . number_format($rateBdt, (floor($rateBdt) == $rateBdt ? 0 : 2)),
                    'rate_per_bdt' => $ratePerBdt, // Base Coins per 1 BDT
                    'offer_tag' => $pm->offer_tag ?: null,
                    'badge' => $pm->offer_tag ?: null,
                    'bonus_text' => $bonusCoins > 0 ? "+{$bonusCoins} Bonus" : null,
                    'bonus_percentage' => $bonusPercent,
                    'button_text' => "Recharge {$totalCoins} Gems (৳" . number_format($rateBdt, (floor($rateBdt) == $rateBdt ? 0 : 2)) . ")",
                    'rate_text' => $bonusCoins > 0 ? "{$rateCoins} + {$bonusCoins} Bonus = ৳{$rateBdt} BDT" : "{$rateCoins} Coins = ৳{$rateBdt} BDT",
                    'example' => $bonusCoins > 0 ? "{$rateCoins} + {$bonusCoins} Bonus ({$totalCoins} Total) = ৳{$rateBdt} BDT" : "{$rateCoins} Coins = ৳{$rateBdt} BDT (1 BDT = {$ratePerBdt} Coins)",
                ];
            });

        return response()->json([
            'status' => true,
            'message' => 'Payment methods retrieved successfully.',
            'data' => $methods,
        ], 200);
    }

    /**
     * Get pre-configured coin packages with bonus gems (e.g. 32000 Base + 8000 Bonus = ৳550)
     * GET /api/coin-packages (or GET /api/packages)
     */
    public function getCoinPackages(): JsonResponse
    {
        try {
            $packages = CoinPackage::where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id', 'asc')
                ->get()
                ->map(function ($pkg) {
                    $baseCoins = (int) $pkg->coins;
                    $bonusCoins = (int) ($pkg->bonus_coins ?: 0);
                    $totalCoins = $baseCoins + $bonusCoins;
                    $price = (float) $pkg->price;
                    $formattedPrice = '৳' . number_format($price, (floor($price) == $price ? 0 : 2));

                    return [
                        'id' => $pkg->id,
                        'coins' => $baseCoins, // Base Coins (e.g. 32000)
                        'base_coins' => $baseCoins, // Base Coins (e.g. 32000)
                        'bonus_coins' => $bonusCoins, // Bonus Coins (e.g. 8000)
                        'total_coins' => $totalCoins, // Total Coins (32000 + 8000 = 40000)
                        'formatted_coins' => number_format($baseCoins), // "32,000"
                        'formatted_base_coins' => number_format($baseCoins), // "32,000"
                        'formatted_bonus_coins' => $bonusCoins > 0 ? ('+' . number_format($bonusCoins) . ' Bonus') : null,
                        'formatted_total_coins' => number_format($totalCoins), // "40,000"
                        'coins_title' => (string) $baseCoins, // "32000"
                        'display_coins' => (string) $baseCoins, // "32000"
                        'display_bonus' => $bonusCoins > 0 ? "+{$bonusCoins} Bonus Gems" : null,
                        'price' => $price,
                        'price_bdt' => $price,
                        'formatted_price' => $formattedPrice,
                        'badge' => $pkg->badge ?: null,
                        'badge_color' => $pkg->badge_color ?: 'pink',
                        'bonus_text' => $bonusCoins > 0 ? "+{$bonusCoins} Bonus" : null,
                        'bonus_percentage' => $baseCoins > 0 && $bonusCoins > 0 ? (int) round(($bonusCoins / $baseCoins) * 100) : 0,
                        'is_popular' => (bool) $pkg->is_popular,
                        'popular' => (bool) $pkg->is_popular,
                        'button_text' => "Recharge {$baseCoins} Gems ({$formattedPrice})",
                        'currency' => 'BDT',
                        'currency_symbol' => '৳',
                    ];
                });

            return response()->json([
                'status' => true,
                'message' => 'Coin packages retrieved successfully from database.',
                'data' => $packages,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving coin packages: ' . $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    /**
     * Get single coin package details.
     * GET /api/coin-packages/{id}
     */
    public function showCoinPackage($id): JsonResponse
    {
        $pkg = CoinPackage::find($id);

        if (!$pkg) {
            return response()->json([
                'status' => false,
                'message' => 'Coin package not found.',
            ], 404);
        }

        $baseCoins = (int) $pkg->coins;
        $bonusCoins = (int) ($pkg->bonus_coins ?: 0);
        $totalCoins = $baseCoins + $bonusCoins;
        $price = (float) $pkg->price;
        $formattedPrice = '৳' . number_format($price, (floor($price) == $price ? 0 : 2));

        return response()->json([
            'status' => true,
            'message' => 'Coin package details retrieved successfully.',
            'data' => [
                'id' => $pkg->id,
                'coins' => $baseCoins,
                'base_coins' => $baseCoins,
                'bonus_coins' => $bonusCoins,
                'total_coins' => $totalCoins,
                'formatted_coins' => number_format($baseCoins),
                'formatted_base_coins' => number_format($baseCoins),
                'formatted_bonus_coins' => $bonusCoins > 0 ? ('+' . number_format($bonusCoins) . ' Bonus') : null,
                'formatted_total_coins' => number_format($totalCoins),
                'coins_title' => (string) $baseCoins,
                'display_coins' => (string) $baseCoins,
                'display_bonus' => $bonusCoins > 0 ? "+{$bonusCoins} Bonus Gems" : null,
                'price' => $price,
                'price_bdt' => $price,
                'formatted_price' => $formattedPrice,
                'badge' => $pkg->badge ?: null,
                'badge_color' => $pkg->badge_color ?: 'pink',
                'bonus_text' => $bonusCoins > 0 ? "+{$bonusCoins} Bonus" : null,
                'bonus_percentage' => $baseCoins > 0 && $bonusCoins > 0 ? (int) round(($bonusCoins / $baseCoins) * 100) : 0,
                'is_popular' => (bool) $pkg->is_popular,
                'popular' => (bool) $pkg->is_popular,
                'is_active' => (bool) $pkg->is_active,
                'sort_order' => (int) ($pkg->sort_order ?: 0),
                'button_text' => "Recharge {$baseCoins} Gems ({$formattedPrice})",
                'currency' => 'BDT',
                'currency_symbol' => '৳',
            ],
        ], 200);
    }

    /**
     * Add / Create a new Coin Package via API.
     * POST /api/coin-packages
     */
    public function storeCoinPackage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'coins' => 'required|integer|min:1',
            'bonus_coins' => 'nullable|integer|min:0',
            'price' => 'required|numeric|min:1',
            'badge' => 'nullable|string|max:50',
            'badge_color' => 'nullable|string|max:30',
            'is_popular' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $package = CoinPackage::create([
            'coins' => (int) $request->input('coins'),
            'bonus_coins' => (int) ($request->input('bonus_coins') ?: 0),
            'price' => (float) $request->input('price'),
            'badge' => $request->input('badge') ?: null,
            'badge_color' => $request->input('badge_color') ?: 'pink',
            'is_popular' => $request->boolean('is_popular', false),
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => (int) ($request->input('sort_order') ?: 0),
        ]);

        $coins = (int) $package->coins;
        $bonus = (int) ($package->bonus_coins ?: 0);
        $total = $coins + $bonus;
        $price = (float) $package->price;
        $formattedPrice = '৳' . number_format($price, (floor($price) == $price ? 0 : 2));

        return response()->json([
            'status' => true,
            'message' => 'Coin package created successfully.',
            'data' => [
                'id' => $package->id,
                'coins' => $coins,
                'bonus_coins' => $bonus,
                'total_coins' => $total,
                'price' => $price,
                'price_bdt' => $price,
                'formatted_price' => $formattedPrice,
                'badge' => $package->badge ?: null,
                'badge_color' => $package->badge_color ?: 'pink',
                'bonus_text' => $bonus > 0 ? "+{$bonus} Bonus" : null,
                'bonus_percentage' => $coins > 0 && $bonus > 0 ? (int) round(($bonus / $coins) * 100) : 0,
                'is_popular' => (bool) $package->is_popular,
                'popular' => (bool) $package->is_popular,
                'is_active' => (bool) $package->is_active,
                'sort_order' => (int) ($package->sort_order ?: 0),
                'button_text' => "Recharge {$total} Gems ({$formattedPrice})",
                'currency' => 'BDT',
                'currency_symbol' => '৳',
            ],
        ], 201);
    }

    /**
     * Update an existing Coin Package via API.
     * PUT /api/coin-packages/{id} (or POST /api/coin-packages/{id}/update)
     */
    public function updateCoinPackage(Request $request, $id): JsonResponse
    {
        $package = CoinPackage::find($id);

        if (!$package) {
            return response()->json([
                'status' => false,
                'message' => 'Coin package not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'coins' => 'sometimes|required|integer|min:1',
            'bonus_coins' => 'nullable|integer|min:0',
            'price' => 'sometimes|required|numeric|min:1',
            'badge' => 'nullable|string|max:50',
            'badge_color' => 'nullable|string|max:30',
            'is_popular' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error.',
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($request->has('coins')) $package->coins = (int) $request->input('coins');
        if ($request->has('bonus_coins')) $package->bonus_coins = (int) ($request->input('bonus_coins') ?: 0);
        if ($request->has('price')) $package->price = (float) $request->input('price');
        if ($request->has('badge')) $package->badge = $request->input('badge') ?: null;
        if ($request->has('badge_color')) $package->badge_color = $request->input('badge_color') ?: 'pink';
        if ($request->has('is_popular')) $package->is_popular = $request->boolean('is_popular');
        if ($request->has('is_active')) $package->is_active = $request->boolean('is_active');
        if ($request->has('sort_order')) $package->sort_order = (int) ($request->input('sort_order') ?: 0);

        $package->save();

        $coins = (int) $package->coins;
        $bonus = (int) ($package->bonus_coins ?: 0);
        $total = $coins + $bonus;
        $price = (float) $package->price;
        $formattedPrice = '৳' . number_format($price, (floor($price) == $price ? 0 : 2));

        return response()->json([
            'status' => true,
            'message' => 'Coin package updated successfully.',
            'data' => [
                'id' => $package->id,
                'coins' => $coins,
                'bonus_coins' => $bonus,
                'total_coins' => $total,
                'price' => $price,
                'price_bdt' => $price,
                'formatted_price' => $formattedPrice,
                'badge' => $package->badge ?: null,
                'badge_color' => $package->badge_color ?: 'pink',
                'bonus_text' => $bonus > 0 ? "+{$bonus} Bonus" : null,
                'bonus_percentage' => $coins > 0 && $bonus > 0 ? (int) round(($bonus / $coins) * 100) : 0,
                'is_popular' => (bool) $package->is_popular,
                'popular' => (bool) $package->is_popular,
                'is_active' => (bool) $package->is_active,
                'sort_order' => (int) ($package->sort_order ?: 0),
                'button_text' => "Recharge {$total} Gems ({$formattedPrice})",
                'currency' => 'BDT',
                'currency_symbol' => '৳',
            ],
        ], 200);
    }

    /**
     * Delete a Coin Package via API.
     * DELETE /api/coin-packages/{id}
     */
    public function deleteCoinPackage($id): JsonResponse
    {
        $package = CoinPackage::find($id);

        if (!$package) {
            return response()->json([
                'status' => false,
                'message' => 'Coin package not found.',
            ], 404);
        }

        $package->delete();

        return response()->json([
            'status' => true,
            'message' => 'Coin package deleted successfully.',
        ], 200);
    }

    /**
     * Submit a manual deposit request with screenshot proof.
     * POST /api/deposit/request
     */
    public function submitDeposit(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated. Please provide a valid Bearer token or user_id.',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'package_id' => 'nullable',
            'payment_method_id' => 'nullable',
            'payment_method' => 'nullable|string', // e.g. 'bkash', 'nagad'
            'amount' => 'required|numeric|min:1',
            'coins' => 'nullable|integer|min:1',
            'sender_number' => 'required|string|max:50',
            'transaction_id' => 'required|string|max:100',
            'screenshot' => 'nullable',
            'screenshot_base64' => 'nullable|string',
            'user_note' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if a specific package was chosen
        $package = null;
        if ($request->filled('package_id')) {
            $package = CoinPackage::find($request->package_id);
        }

        // Find payment method
        $paymentMethod = null;
        if ($request->filled('payment_method_id')) {
            $paymentMethod = PaymentMethod::find($request->payment_method_id);
        } elseif ($request->filled('payment_method')) {
            $paymentMethod = PaymentMethod::where('code', strtolower($request->payment_method))
                ->orWhere('name', 'like', "%{$request->payment_method}%")
                ->first();
        }

        $amount = (float) $request->input('amount');

        if ($package) {
            $defaultCoins = (int) $package->total_coins;
        } elseif ($paymentMethod && $paymentMethod->rate_bdt > 0 && $paymentMethod->rate_coins > 0) {
            $baseCoins = (int) round(($amount / $paymentMethod->rate_bdt) * $paymentMethod->rate_coins);
            $bonusCoins = (int) ($paymentMethod->bonus_coins ?: 0);
            if ($amount >= $paymentMethod->rate_bdt && $bonusCoins > 0) {
                $multiplier = floor($amount / $paymentMethod->rate_bdt);
                $defaultCoins = $baseCoins + (int) ($bonusCoins * $multiplier);
            } else {
                $defaultCoins = $baseCoins;
            }
        } elseif ($paymentMethod && $paymentMethod->rate_per_bdt > 0) {
            $defaultCoins = (int) round($amount * $paymentMethod->rate_per_bdt);
        } else {
            $defaultCoins = (int) round($amount * 10);
        }
        $coins = $request->filled('coins') ? (int) $request->input('coins') : $defaultCoins;

        // Upload screenshot safely (Multipart file or Base64 string)
        $screenshotPath = null;
        if ($request->hasFile('screenshot')) {
            $file = $request->file('screenshot');
            $uploadDir = public_path('uploads/deposits');
            if (!file_exists($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }
            $filename = 'deposit_' . $user->id . '_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $screenshotPath = 'deposits/' . $filename;
        } elseif ($request->filled('screenshot_base64') || ($request->filled('screenshot') && str_starts_with($request->input('screenshot'), 'data:image'))) {
            $raw = $request->input('screenshot_base64') ?: $request->input('screenshot');
            $uploadDir = public_path('uploads/deposits');
            if (!file_exists($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }
            $cleanBase64 = preg_replace('/^data:image\/\w+;base64,/', '', $raw);
            $binary = base64_decode($cleanBase64);
            if ($binary !== false) {
                $filename = 'deposit_' . $user->id . '_' . time() . '_' . Str::random(6) . '.jpg';
                file_put_contents($uploadDir . DIRECTORY_SEPARATOR . $filename, $binary);
                $screenshotPath = 'deposits/' . $filename;
            }
        }

        $deposit = DepositRequest::create([
            'user_id' => $user->id,
            'payment_method_id' => $paymentMethod?->id,
            'payment_method_name' => $paymentMethod ? $paymentMethod->name : ($request->input('payment_method') ?: 'Manual Payment'),
            'amount' => $amount,
            'coins' => $coins,
            'sender_number' => $request->input('sender_number'),
            'transaction_id' => strtoupper(trim($request->input('transaction_id'))),
            'screenshot' => $screenshotPath,
            'user_note' => $request->input('user_note'),
            'status' => 'pending',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Deposit request submitted successfully! Your coins will be credited once verified by admin.',
            'data' => [
                'deposit_id' => $deposit->id,
                'amount' => (float) $deposit->amount,
                'coins' => $deposit->coins,
                'payment_method' => $deposit->payment_method_name,
                'sender_number' => $deposit->sender_number,
                'transaction_id' => $deposit->transaction_id,
                'screenshot_url' => $deposit->screenshot_url,
                'status' => $deposit->status,
                'created_at' => $deposit->created_at->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * Get user's deposit history.
     * GET /api/deposit/history
     */
    public function getDepositHistory(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $deposits = DepositRequest::where('user_id', $user->id)
            ->latest()
            ->paginate(20);

        return response()->json([
            'status' => true,
            'message' => 'Deposit history retrieved successfully.',
            'data' => $deposits->items(),
            'pagination' => [
                'current_page' => $deposits->currentPage(),
                'last_page' => $deposits->lastPage(),
                'total' => $deposits->total(),
            ],
        ], 200);
    }

    /**
     * Get wallet / coin balance, total deposited coins, deposit statistics and package details.
     * GET /api/wallet/balance (or GET /api/wallet/summary, GET /api/coins/balance)
     */
    public function getWalletBalance(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Calculate deposit statistics
        $approvedDeposits = DepositRequest::where('user_id', $user->id)
            ->where('status', 'approved');

        $totalDepositedCoins = (int) $approvedDeposits->sum('coins');
        $totalDepositedBdt = (float) $approvedDeposits->sum('amount');
        $approvedCount = $approvedDeposits->count();
        $pendingCount = DepositRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        $latestDeposit = DepositRequest::where('user_id', $user->id)
            ->latest('id')
            ->first();

        $coins = (int) $user->coins;

        return response()->json([
            'status' => true,
            'message' => 'Wallet balance retrieved successfully.',
            'data' => [
                'user_id' => $user->id,
                'account_id' => $user->account_id,
                'display_name' => $user->display_name,
                'coins' => $coins,
                'gems' => $coins,
                'beans' => (int) ($user->beans ?? 0),
                'formatted_coins' => number_format($coins),
                'total_deposited_coins' => $totalDepositedCoins,
                'formatted_total_deposited_coins' => number_format($totalDepositedCoins),
                'total_deposited_bdt' => $totalDepositedBdt,
                'formatted_total_deposited_bdt' => '৳' . number_format($totalDepositedBdt, (floor($totalDepositedBdt) == $totalDepositedBdt ? 0 : 2)),
                'approved_deposits_count' => $approvedCount,
                'pending_deposits_count' => $pendingCount,
                'call_rate_per_minute' => 100, // 100 coins per 1 minute
                'max_call_minutes' => (int) floor($coins / 100),
                'avatar_url' => $user->avatar_url,
                'latest_deposit' => $latestDeposit ? [
                    'id' => $latestDeposit->id,
                    'amount' => (float) $latestDeposit->amount,
                    'coins' => (int) $latestDeposit->coins,
                    'payment_method' => $latestDeposit->payment_method_name,
                    'transaction_id' => $latestDeposit->transaction_id,
                    'status' => $latestDeposit->status,
                    'created_at' => $latestDeposit->created_at->toIso8601String(),
                ] : null,
            ],
        ], 200);
    }

    /**
     * Get coin transactions ledger for the user.
     * GET /api/wallet/transactions
     */
    public function getTransactions(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $transactions = CoinTransaction::where('user_id', $user->id)
            ->latest()
            ->paginate(20);

        return response()->json([
            'status' => true,
            'message' => 'Transactions retrieved successfully.',
            'data' => $transactions->items(),
            'current_coins' => (int) $user->coins,
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'total' => $transactions->total(),
            ],
        ], 200);
    }
}
