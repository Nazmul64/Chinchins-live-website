<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
                    'rate_per_bdt' => $pm->rate_per_bdt, // e.g. 1 BDT = 10 Coins
                    'example' => "100 BDT = " . (100 * $pm->rate_per_bdt) . " Coins",
                ];
            });

        return response()->json([
            'status' => true,
            'message' => 'Payment methods retrieved successfully.',
            'data' => $methods,
        ], 200);
    }

    /**
     * Get pre-configured coin packages (e.g. 500 Coins = 50 BDT, 1000 Coins = 100 BDT, etc.)
     * GET /api/coin-packages
     */
    public function getCoinPackages(): JsonResponse
    {
        $packages = [
            ['id' => 1, 'bdt' => 50, 'coins' => 500, 'popular' => false, 'title' => 'Starter Pack'],
            ['id' => 2, 'bdt' => 100, 'coins' => 1000, 'popular' => true, 'title' => 'Basic Pack'],
            ['id' => 3, 'bdt' => 200, 'coins' => 2100, 'popular' => false, 'bonus' => '100 Extra Coins', 'title' => 'Standard Pack'],
            ['id' => 4, 'bdt' => 500, 'coins' => 5500, 'popular' => true, 'bonus' => '500 Extra Coins', 'title' => 'Popular Pack'],
            ['id' => 5, 'bdt' => 1000, 'coins' => 11500, 'popular' => false, 'bonus' => '1,500 Extra Coins', 'title' => 'VIP Pack'],
            ['id' => 6, 'bdt' => 2000, 'coins' => 24000, 'popular' => false, 'bonus' => '4,000 Extra Coins', 'title' => 'Ultimate Pack'],
        ];

        return response()->json([
            'status' => true,
            'message' => 'Coin packages retrieved successfully.',
            'data' => $packages,
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
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'payment_method' => 'nullable|string', // e.g. 'bkash', 'nagad'
            'amount' => 'required|numeric|min:10',
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
        $rate = $paymentMethod ? $paymentMethod->rate_per_bdt : 10;
        $coins = $request->filled('coins') ? (int) $request->input('coins') : (int) ($amount * $rate);

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
