<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Models\WithdrawalSetting;
use App\Models\WithdrawRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WithdrawalAdminController extends Controller
{
    /**
     * Display a listing of all withdrawal requests with filters and stats.
     */
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');

        $query = WithdrawRequest::with(['user', 'paymentMethod', 'approver'])->latest();

        if (in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('account_number', 'like', "%{$search}%")
                  ->orWhere('transaction_id', 'like', "%{$search}%")
                  ->orWhere('payment_method_name', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('display_name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%")
                         ->orWhere('account_id', 'like', "%{$search}%");
                  });
            });
        }

        $withdrawals = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => WithdrawRequest::count(),
            'pending' => WithdrawRequest::where('status', 'pending')->count(),
            'approved' => WithdrawRequest::where('status', 'approved')->count(),
            'rejected' => WithdrawRequest::where('status', 'rejected')->count(),
            'total_coins' => WithdrawRequest::where('status', 'approved')->sum('coins'),
            'total_gross' => WithdrawRequest::where('status', 'approved')->sum('gross_amount'),
            'total_commission' => WithdrawRequest::where('status', 'approved')->sum('commission_amount'),
            'total_paid' => WithdrawRequest::where('status', 'approved')->sum('net_payable_amount'),
        ];

        $config = WithdrawalSetting::getAllConfig();

        return view('admin.withdrawals.index', compact('withdrawals', 'stats', 'status', 'config'));
    }

    /**
     * Approve a withdrawal request and deduct coins from the user's balance.
     */
    public function approve(Request $request, $id)
    {
        $withdraw = WithdrawRequest::with('user')->findOrFail($id);

        if ($withdraw->status !== 'pending') {
            return back()->with('error', "This withdrawal request has already been {$withdraw->status}.");
        }

        DB::beginTransaction();
        try {
            $user = $withdraw->user;
            if (!$user) {
                return back()->with('error', 'Associated user not found.');
            }

            // Check if user still has enough coins to deduct
            if ($user->coins < $withdraw->coins) {
                return back()->with('error', "Cannot approve: User only has {$user->coins} coins, but withdrawal requires {$withdraw->coins} coins.");
            }

            // 1. Deduct coins from user balance & record in CoinTransaction ledger
            $deducted = $user->deductCoins(
                (int) $withdraw->coins,
                'withdraw',
                "Withdrawal to {$withdraw->payment_method_name} ({$withdraw->account_number}) - Req #{$withdraw->id}",
                "withdraw_#{$withdraw->id}"
            );

            if (!$deducted) {
                DB::rollBack();
                return back()->with('error', 'Failed to deduct coins from user wallet.');
            }

            // 2. Mark withdrawal request as approved
            $withdraw->status = 'approved';
            $withdraw->approved_at = now();
            $withdraw->approved_by = Auth::id();
            if ($request->filled('transaction_id')) {
                $withdraw->transaction_id = strtoupper(trim($request->input('transaction_id')));
            }
            $withdraw->admin_note = $request->input('admin_note') ?: 'Approved and payout processed by admin';
            $withdraw->save();

            DB::commit();
            return back()->with('success', "Withdrawal of " . number_format($withdraw->coins) . " Coins (৳" . number_format($withdraw->net_payable_amount, 2) . " BDT) for {$user->display_name} has been Approved and Deducted!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error approving withdrawal: ' . $e->getMessage());
        }
    }

    /**
     * Reject a withdrawal request (Coins are not deducted).
     */
    public function reject(Request $request, $id)
    {
        $withdraw = WithdrawRequest::with('user')->findOrFail($id);

        if ($withdraw->status !== 'pending') {
            return back()->with('error', "This withdrawal request has already been {$withdraw->status}.");
        }

        $withdraw->status = 'rejected';
        $withdraw->rejected_at = now();
        $withdraw->approved_by = Auth::id();
        $withdraw->admin_note = $request->input('admin_note') ?: 'Rejected by administrator';
        $withdraw->save();

        return back()->with('success', "Withdrawal request #{$withdraw->id} was rejected.");
    }

    /**
     * Display Withdrawal Settings & Gateway configuration page.
     */
    public function settings()
    {
        $config = WithdrawalSetting::getAllConfig();
        $paymentMethods = PaymentMethod::orderBy('sort_order')->get();

        return view('admin.withdrawals.settings', compact('config', 'paymentMethods'));
    }

    /**
     * Update Withdrawal Configuration settings.
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'is_withdraw_enabled' => 'nullable|boolean',
            'min_withdraw_coins' => 'required|integer|min:1',
            'max_withdraw_coins' => 'required|integer|min:1|gte:min_withdraw_coins',
            'commission_percent' => 'required|numeric|min:0|max:100',
            'rate_coins' => 'required|integer|min:1',
            'rate_bdt' => 'required|numeric|min:0.01',
            'notice' => 'nullable|string|max:1000',
        ]);

        WithdrawalSetting::set('is_withdraw_enabled', $request->boolean('is_withdraw_enabled') ? '1' : '0', 'Enable or disable withdrawal feature globally');
        WithdrawalSetting::set('min_withdraw_coins', $request->input('min_withdraw_coins'), 'Minimum coins required for single withdrawal');
        WithdrawalSetting::set('max_withdraw_coins', $request->input('max_withdraw_coins'), 'Maximum coins allowed for single withdrawal');
        WithdrawalSetting::set('commission_percent', $request->input('commission_percent'), 'Commission percentage deducted on withdrawal');
        WithdrawalSetting::set('rate_coins', $request->input('rate_coins'), 'Coins quantity for rate calculation');
        WithdrawalSetting::set('rate_bdt', $request->input('rate_bdt'), 'BDT value for rate calculation');
        WithdrawalSetting::set('notice', $request->input('notice') ?: '', 'Notice / instructions displayed to users on withdraw screen');

        // Update payment methods withdrawal support if submitted
        if ($request->has('methods') && is_array($request->input('methods'))) {
            foreach ($request->input('methods') as $pmId => $pmData) {
                $pm = PaymentMethod::find($pmId);
                if ($pm) {
                    $pm->supports_withdraw = isset($pmData['supports_withdraw']) && $pmData['supports_withdraw'] == '1';
                    if (isset($pmData['min_withdraw'])) $pm->min_withdraw = (float) $pmData['min_withdraw'];
                    if (isset($pmData['max_withdraw'])) $pm->max_withdraw = (float) $pmData['max_withdraw'];
                    $pm->save();
                }
            }
        }

        return back()->with('success', 'Withdrawal settings and commission rates updated successfully!');
    }

    /**
     * Toggle withdrawal support on a specific Payment Method.
     */
    public function toggleMethodWithdraw($id)
    {
        $method = PaymentMethod::findOrFail($id);
        $method->supports_withdraw = !$method->supports_withdraw;
        $method->save();

        $statusText = $method->supports_withdraw ? 'Enabled' : 'Disabled';
        return back()->with('success', "Withdrawal support {$statusText} for {$method->name}.");
    }
}
