<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Display a listing of all users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search by Name, Phone, Account ID or Email
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('nickname', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('account_id', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('is_active', $request->boolean('status'));
        }

        // Filter by Gender
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        // Sorting
        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'coins_high' => $query->orderBy('coins', 'desc'),
            'coins_low' => $query->orderBy('coins', 'asc'),
            'name_asc' => $query->orderBy('name', 'asc'),
            default => $query->latest(),
        };

        $users = $query->paginate(15)->withQueryString();

        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'total_coins' => User::sum('coins'),
            'verified_users' => User::where('is_verified', true)->count(),
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    /**
     * Display user details and coin transaction history.
     */
    public function show($id)
    {
        $user = User::with(['coinTransactions' => function ($q) {
            $q->latest()->limit(20);
        }, 'depositRequests' => function ($q) {
            $q->latest()->limit(10);
        }])->findOrFail($id);

        return view('admin.users.show', compact('user'));
    }

    /**
     * Manually Add or Deduct coins from user balance.
     */
    public function adjustCoins(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:add,deduct,set',
            'amount' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
        ]);

        $user = User::findOrFail($id);
        $amount = (int) $request->input('amount');
        $action = $request->input('action');
        $reason = $request->input('reason') ?: 'Manual admin adjustment';

        DB::beginTransaction();
        try {
            if ($action === 'add') {
                $user->addCoins($amount, 'admin_add', $reason);
                $message = "Successfully added " . number_format($amount) . " coins to {$user->display_name}. New Balance: " . number_format($user->coins);
            } elseif ($action === 'deduct') {
                if ($user->coins < $amount) {
                    return back()->with('error', "User only has {$user->coins} coins. Cannot deduct {$amount} coins.");
                }
                $user->deductCoins($amount, 'admin_deduct', $reason);
                $message = "Successfully deducted " . number_format($amount) . " coins from {$user->display_name}. New Balance: " . number_format($user->coins);
            } elseif ($action === 'set') {
                $diff = $amount - $user->coins;
                if ($diff >= 0) {
                    $user->addCoins($diff, 'admin_add', $reason . " (Balance set to {$amount})");
                } else {
                    $user->deductCoins(abs($diff), 'admin_deduct', $reason . " (Balance set to {$amount})");
                }
                $message = "Successfully set balance to " . number_format($amount) . " coins for {$user->display_name}.";
            }

            DB::commit();
            return back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to adjust coins: ' . $e->getMessage());
        }
    }

    /**
     * Toggle active/inactive status of user.
     */
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        $user->is_active = !$user->is_active;
        $user->save();

        $statusStr = $user->is_active ? 'Activated' : 'Deactivated';
        return back()->with('success', "User {$user->display_name} has been {$statusStr}.");
    }
}
