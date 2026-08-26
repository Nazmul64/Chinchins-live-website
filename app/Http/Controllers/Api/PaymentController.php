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
     * Resolve authenticated or requested user instance.
     */
    protected function resolveUser(Request $request): ?User
    {
        if (Auth::guard('sanctum')->check()) {
            return Auth::guard('sanctum')->user();
        }

        if ($userId = $request->input('user_id') ?: $request->header('X-User-Id')) {
            return User::where('id', $userId)->orWhere('account_id', $userId)->first();
        }

        return null;
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
     * Get pre-configured coin packages with bonus gems (e.g. 32000 + 8000 Bonus = ৳550)
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
                    $coins = (int) $pkg->coins;
                    $bonus = (int) ($pkg->bonus_coins ?: 0);
                    $total = $coins + $bonus;
                    $price = (float) $pkg->price;
                    $formattedPrice = '৳' . number_format($price, (floor($price) == $price ? 0 : 2));

                    return [
                        'id' => $pkg->id,
                        'coins' => $coins,
                        'bonus_coins' => $bonus,
                        'total_coins' => $total,
                        'price' => $price,
                        'price_bdt' => $price,
                        'formatted_price' => $formattedPrice,
                        'badge' => $pkg->badge ?: null,
                        'badge_color' => $pkg->badge_color ?: 'pink',
                        'bonus_text' => $bonus > 0 ? "+{$bonus} Bonus" : null,
                        'bonus_percentage' => $coins > 0 && $bonus > 0 ? (int) round(($bonus / $coins) * 100) : 0,
                        'is_popular' => (bool) $pkg->is_popular,
                        'popular' => (bool) $pkg->is_popular,
                        'button_text' => "Recharge {$total} Gems ({$formattedPrice})",
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
            'package_id' => 'nullable|exists:coin_packages,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'payment_method' => 'nullable|string', // e.g. 'bkash', 'nagad'
            'amount' => 'required|numeric|min:1',
            'coins' => 'nullable|integer|min:1',
            'sender_number' => 'required|string|max:30',
            'transaction_id' => 'required|string|max:100',
            'screenshot' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
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

        // Upload screenshot safely
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
     * Get wallet / coin balance and details.
     * GET /api/wallet/balance (or GET /api/coins/balance)
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

        return response()->json([
            'status' => true,
            'message' => 'Wallet balance retrieved successfully.',
            'data' => [
                'user_id' => $user->id,
                'account_id' => $user->account_id,
                'name' => $user->display_name,
                'coins' => (int) $user->coins,
                'call_rate_per_minute' => 100, // 100 coins per 1 minute
                'max_call_minutes' => (int) floor($user->coins / 100),
                'avatar_url' => $user->avatar_url,
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
