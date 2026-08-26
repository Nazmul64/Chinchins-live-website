<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DepositRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DepositRequestController extends Controller
{
    /**
     * Display a listing of all deposit requests.
     */
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');

        $query = DepositRequest::with(['user', 'paymentMethod', 'approver'])->latest();

        if (in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                  ->orWhere('sender_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%")
                         ->orWhere('account_id', 'like', "%{$search}%");
                  });
            });
        }

        $deposits = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => DepositRequest::count(),
            'pending' => DepositRequest::where('status', 'pending')->count(),
            'approved' => DepositRequest::where('status', 'approved')->count(),
            'rejected' => DepositRequest::where('status', 'rejected')->count(),
            'total_amount' => DepositRequest::where('status', 'approved')->sum('amount'),
            'total_coins' => DepositRequest::where('status', 'approved')->sum('coins'),
        ];

        return view('admin.deposits.index', compact('deposits', 'stats', 'status'));
    }

    /**
     * Approve a deposit request and credit coins to the user.
     */
    public function approve(Request $request, $id)
    {
        $deposit = DepositRequest::with('user')->findOrFail($id);

        if ($deposit->status !== 'pending') {
            return back()->with('error', "This request has already been {$deposit->status}.");
        }

        DB::beginTransaction();
        try {
            $user = $deposit->user;
            if (!$user) {
                return back()->with('error', 'Associated user not found.');
            }

            // 1. Credit coins to user balance & create transaction ledger
            $user->addCoins(
                (int) $deposit->coins,
                'deposit',
                "Deposit via {$deposit->payment_method_name} (TrxID: {$deposit->transaction_id})",
                "deposit_#{$deposit->id}"
            );

            // 2. Mark deposit request as approved
            $deposit->status = 'approved';
            $deposit->approved_at = now();
            $deposit->approved_by = Auth::id();
            $deposit->admin_note = $request->input('admin_note') ?: 'Approved by admin';
            $deposit->save();

            DB::commit();
            return back()->with('success', "Deposit of " . number_format($deposit->coins) . " Coins for {$user->display_name} has been Approved and Credited!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error approving deposit: ' . $e->getMessage());
        }
    }

    /**
     * Reject a deposit request.
     */
    public function reject(Request $request, $id)
    {
        $deposit = DepositRequest::with('user')->findOrFail($id);

        if ($deposit->status !== 'pending') {
            return back()->with('error', "This request has already been {$deposit->status}.");
        }

        $deposit->status = 'rejected';
        $deposit->approved_at = now();
        $deposit->approved_by = Auth::id();
        $deposit->admin_note = $request->input('admin_note') ?: 'Rejected by admin';
        $deposit->save();

        return back()->with('success', "Deposit request #{$deposit->id} was rejected.");
    }
}
