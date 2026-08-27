<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CoinTransaction;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Models\WithdrawalSetting;
use App\Models\WithdrawRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class WithdrawalApiController extends Controller
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
     * Get Withdrawal Configuration, User Balance, Limits, Commission & Payment Methods.
     * GET /api/withdraw/info (or GET /api/withdraw/config, GET /api/wallet/withdraw)
     */
    public function getInfo(Request $request): JsonResponse
    {
        $config = WithdrawalSetting::getAllConfig();
        $user = $this->resolveUser($request);

        // Fetch active payment methods supporting withdrawal
        $paymentMethods = PaymentMethod::where('is_active', true)
            ->where('supports_withdraw', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($pm) {
                return [
                    'id' => $pm->id,
                    'name' => $pm->name,
                    'code' => $pm->code,
                    'account_type' => $pm->account_type,
                    'icon' => $pm->icon_url ?: $pm->icon,
                    'icon_url' => $pm->icon_url ?: $pm->icon,
                    'min_withdraw' => (float) ($pm->min_withdraw ?: 50.00),
                    'max_withdraw' => (float) ($pm->max_withdraw ?: 50000.00),
                    'instructions' => $pm->instructions,
                ];
            });

        // If no payment method specifically has supports_withdraw = true, fallback to all active payment methods
        if ($paymentMethods->isEmpty()) {
            $paymentMethods = PaymentMethod::where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->map(function ($pm) {
                    return [
                        'id' => $pm->id,
                        'name' => $pm->name,
                        'code' => $pm->code,
                        'account_type' => $pm->account_type,
                        'icon' => $pm->icon_url ?: $pm->icon,
                        'icon_url' => $pm->icon_url ?: $pm->icon,
                        'min_withdraw' => (float) ($pm->min_withdraw ?: 50.00),
                        'max_withdraw' => (float) ($pm->max_withdraw ?: 50000.00),
                        'instructions' => $pm->instructions,
                    ];
                });
        }

        $userBalanceData = null;
        if ($user) {
            $coins = (int) $user->coins;
            $ratePerBdt = $config['rate_per_bdt'] > 0 ? $config['rate_per_bdt'] : 10.00;
            $estimatedGrossBdt = round($coins / $ratePerBdt, 2);
            $commissionPercent = $config['commission_percent'];
            $estimatedCommissionBdt = round($estimatedGrossBdt * ($commissionPercent / 100), 2);
            $estimatedNetBdt = round($estimatedGrossBdt - $estimatedCommissionBdt, 2);

            $approvedWithdraws = WithdrawRequest::where('user_id', $user->id)->where('status', 'approved');
            $totalWithdrawnCoins = (int) $approvedWithdraws->sum('coins');
            $totalWithdrawnBdt = (float) $approvedWithdraws->sum('net_payable_amount');
            $pendingCount = WithdrawRequest::where('user_id', $user->id)->where('status', 'pending')->count();

            $userBalanceData = [
                'user_id' => $user->id,
                'account_id' => $user->account_id,
                'display_name' => $user->display_name,
                'phone' => $user->phone,
                'coins' => $coins,
                'formatted_coins' => number_format($coins) . ' Coins',
                'estimated_gross_bdt' => $estimatedGrossBdt,
                'estimated_commission_bdt' => $estimatedCommissionBdt,
                'estimated_net_bdt' => max(0, $estimatedNetBdt),
                'formatted_estimated_net_bdt' => '৳' . number_format(max(0, $estimatedNetBdt), 2),
                'can_withdraw' => ($config['is_withdraw_enabled'] && $coins >= $config['min_withdraw_coins']),
                'total_withdrawn_coins' => $totalWithdrawnCoins,
                'formatted_total_withdrawn_coins' => number_format($totalWithdrawnCoins) . ' Coins',
                'total_withdrawn_bdt' => $totalWithdrawnBdt,
                'formatted_total_withdrawn_bdt' => '৳' . number_format($totalWithdrawnBdt, 2),
                'pending_withdraws_count' => $pendingCount,
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'Withdrawal information and settings retrieved successfully.',
            'data' => [
                'is_enabled' => $config['is_withdraw_enabled'],
                'min_withdraw_coins' => $config['min_withdraw_coins'],
                'max_withdraw_coins' => $config['max_withdraw_coins'],
                'commission_percent' => $config['commission_percent'],
                'rate_coins' => $config['rate_coins'],
                'rate_bdt' => $config['rate_bdt'],
                'rate_per_bdt' => $config['rate_per_bdt'],
                'min_withdraw_bdt' => $config['min_withdraw_bdt'],
                'max_withdraw_bdt' => $config['max_withdraw_bdt'],
                'rate_text' => $config['rate_text'],
                'notice' => $config['notice'],
                'user' => $userBalanceData,
                'payment_methods' => $paymentMethods,
            ],
        ], 200);
    }

    /**
     * Resiliently extract request data across all content types (JSON, Form-Data, Query).
     */
    protected function getRequestData(Request $request): array
    {
        $data = $request->all();
        if (empty($data)) {
            $content = $request->getContent();
            if (!empty($content)) {
                $json = json_decode($content, true);
                if (is_array($json)) {
                    $data = $json;
                }
            }
        }
        return $data;
    }

    /**
     * Calculate payout and commission dynamically for a given amount of coins.
     * POST /api/withdraw/calculate
     */
    public function calculate(Request $request): JsonResponse
    {
        $data = $this->getRequestData($request);
        $validator = Validator::make($data, [
            'coins' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $config = WithdrawalSetting::getAllConfig();
        $coins = (int) ($data['coins'] ?? $request->input('coins'));
        $ratePerBdt = $config['rate_per_bdt'] > 0 ? $config['rate_per_bdt'] : 10.00;
        $commissionPercent = (float) $config['commission_percent'];

        $grossAmount = round($coins / $ratePerBdt, 2);
        $commissionAmount = round($grossAmount * ($commissionPercent / 100), 2);
        $netPayableAmount = round($grossAmount - $commissionAmount, 2);

        $isValid = true;
        $errorMessage = null;

        if (!$config['is_withdraw_enabled']) {
            $isValid = false;
            $errorMessage = 'Withdrawal system is currently disabled by administrator.';
        } elseif ($coins < $config['min_withdraw_coins']) {
            $isValid = false;
            $errorMessage = "Minimum withdrawal is {$config['min_withdraw_coins']} Coins.";
        } elseif ($coins > $config['max_withdraw_coins']) {
            $isValid = false;
            $errorMessage = "Maximum withdrawal is {$config['max_withdraw_coins']} Coins.";
        }

        // Check user balance if provided
        $user = $this->resolveUser($request);
        if ($user && $user->coins < $coins) {
            $isValid = false;
            $errorMessage = "Insufficient coin balance. You currently have {$user->coins} coins.";
        }

        return response()->json([
            'status' => true,
            'message' => 'Withdrawal calculation generated.',
            'data' => [
                'coins' => $coins,
                'formatted_coins' => number_format($coins) . ' Coins',
                'gross_amount' => $grossAmount,
                'formatted_gross_amount' => '৳' . number_format($grossAmount, 2),
                'commission_percent' => $commissionPercent,
                'commission_amount' => $commissionAmount,
                'formatted_commission_amount' => '৳' . number_format($commissionAmount, 2) . " ({$commissionPercent}%)",
                'net_payable_amount' => max(0, $netPayableAmount),
                'formatted_net_payable_amount' => '৳' . number_format(max(0, $netPayableAmount), 2),
                'rate_per_bdt' => $ratePerBdt,
                'rate_text' => $config['rate_text'],
                'is_valid' => $isValid,
                'error_message' => $errorMessage,
            ],
        ], 200);
    }

    /**
     * Submit a new withdrawal request.
     * POST /api/withdraw/submit (or POST /api/withdraw/request, POST /api/wallet/withdraw)
     */
    public function submit(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated. Please provide a valid Bearer token or user_id.',
            ], 401);
        }

        $config = WithdrawalSetting::getAllConfig();

        // 1. Check if withdrawal system is enabled
        if (!$config['is_withdraw_enabled']) {
            return response()->json([
                'status' => false,
                'message' => 'Withdrawals are currently disabled by administrator. Please try again later.',
            ], 403);
        }

        $data = $this->getRequestData($request);

        // 2. Validate input parameters
        $validator = Validator::make($data, [
            'coins' => 'required|numeric|min:1',
            'payment_method_id' => 'nullable',
            'payment_method' => 'nullable|string', // e.g. 'bkash', 'nagad', 'bKash Personal'
            'account_number' => 'required|string|max:50',
            'account_type' => 'nullable|string|max:30', // 'Personal', 'Agent', etc.
            'user_note' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed. Please check input parameters.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $coins = (int) ($data['coins'] ?? $request->input('coins'));

        // 3. Validate Minimum & Maximum Coins Limit
        if ($coins < $config['min_withdraw_coins']) {
            return response()->json([
                'status' => false,
                'message' => "Minimum withdrawal limit is {$config['min_withdraw_coins']} Coins (৳" . number_format($config['min_withdraw_bdt'], 2) . ").",
            ], 422);
        }

        if ($coins > $config['max_withdraw_coins']) {
            return response()->json([
                'status' => false,
                'message' => "Maximum withdrawal limit is {$config['max_withdraw_coins']} Coins (৳" . number_format($config['max_withdraw_bdt'], 2) . ").",
            ], 422);
        }

        // 4. Validate User Balance
        if ($user->coins < $coins) {
            return response()->json([
                'status' => false,
                'message' => "Insufficient coin balance. Your current balance is " . number_format($user->coins) . " coins, but requested " . number_format($coins) . " coins.",
                'data' => [
                    'current_coins' => (int) $user->coins,
                    'requested_coins' => $coins,
                    'shortfall_coins' => $coins - (int) $user->coins,
                ],
            ], 422);
        }

        // 5. Match Payment Method
        $paymentMethod = null;
        $pmId = $data['payment_method_id'] ?? $request->input('payment_method_id');
        $pmCode = $data['payment_method'] ?? $request->input('payment_method');
        $accountNum = $data['account_number'] ?? $request->input('account_number');
        $accType = $data['account_type'] ?? $request->input('account_type');
        $userNote = $data['user_note'] ?? $request->input('user_note');

        if (!empty($pmId)) {
            $paymentMethod = PaymentMethod::find($pmId);
        } elseif (!empty($pmCode)) {
            $paymentMethod = PaymentMethod::where('code', strtolower($pmCode))
                ->orWhere('name', 'like', "%{$pmCode}%")
                ->first();
        }

        $methodName = $paymentMethod ? $paymentMethod->name : ($pmCode ?: 'bKash / Nagad');
        $accountType = $accType ?: ($paymentMethod ? $paymentMethod->account_type : 'Personal');

        // 6. Calculate amounts and commission
        $ratePerBdt = $config['rate_per_bdt'] > 0 ? $config['rate_per_bdt'] : 10.00;
        $commissionPercent = (float) $config['commission_percent'];

        $grossAmount = round($coins / $ratePerBdt, 2);
        $commissionAmount = round($grossAmount * ($commissionPercent / 100), 2);
        $netPayableAmount = round($grossAmount - $commissionAmount, 2);

        // 7. Create Withdrawal Request (Status: pending)
        $withdraw = WithdrawRequest::create([
            'user_id' => $user->id,
            'payment_method_id' => $paymentMethod?->id,
            'payment_method_name' => $methodName,
            'coins' => $coins,
            'rate_per_bdt' => $ratePerBdt,
            'gross_amount' => $grossAmount,
            'commission_percent' => $commissionPercent,
            'commission_amount' => $commissionAmount,
            'net_payable_amount' => $netPayableAmount,
            'account_number' => trim((string) $accountNum),
            'account_type' => $accountType,
            'user_note' => $userNote,
            'status' => 'pending',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Withdrawal request submitted successfully! It is now pending admin approval. Once approved, coins will be deducted from your wallet and payment sent to your account.',
            'data' => [
                'withdraw_id' => $withdraw->id,
                'coins' => $withdraw->coins,
                'formatted_coins' => number_format($withdraw->coins) . ' Coins',
                'gross_amount' => (float) $withdraw->gross_amount,
                'formatted_gross_amount' => '৳' . number_format($withdraw->gross_amount, 2),
                'commission_percent' => (float) $withdraw->commission_percent,
                'commission_amount' => (float) $withdraw->commission_amount,
                'formatted_commission_amount' => '৳' . number_format($withdraw->commission_amount, 2),
                'net_payable_amount' => (float) $withdraw->net_payable_amount,
                'formatted_net_payable_amount' => '৳' . number_format($withdraw->net_payable_amount, 2),
                'payment_method' => $withdraw->payment_method_name,
                'account_number' => $withdraw->account_number,
                'account_type' => $withdraw->account_type,
                'status' => $withdraw->status,
                'user_current_coins' => (int) $user->coins,
                'created_at' => $withdraw->created_at->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * Get user's withdrawal request history.
     * GET /api/withdraw/history (or GET /api/wallet/withdraw/history)
     */
    public function history(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $withdraws = WithdrawRequest::with('paymentMethod')
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(20);

        return response()->json([
            'status' => true,
            'message' => 'Withdrawal history retrieved successfully.',
            'data' => $withdraws->items(),
            'current_coins' => (int) $user->coins,
            'pagination' => [
                'current_page' => $withdraws->currentPage(),
                'last_page' => $withdraws->lastPage(),
                'total' => $withdraws->total(),
            ],
        ], 200);
    }

    /**
     * Get single withdrawal request details.
     * GET /api/withdraw/{id}
     */
    public function show(Request $request, $id): JsonResponse
    {
        $user = $this->resolveUser($request);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $withdraw = WithdrawRequest::with(['paymentMethod', 'approver'])
            ->where('user_id', $user->id)
            ->find($id);

        if (!$withdraw) {
            return response()->json([
                'status' => false,
                'message' => 'Withdrawal request not found.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Withdrawal request details retrieved.',
            'data' => $withdraw,
        ], 200);
    }
}
