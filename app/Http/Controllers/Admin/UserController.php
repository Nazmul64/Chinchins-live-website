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
        $query = User::with(['kycVerification', 'wallet']);

        // Search by Name, Phone, Account ID, Country, City, or Email
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('nickname', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('account_id', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('is_active', $request->boolean('status'));
        }

        // Filter by Free Caller / Free Host Status
        if ($request->filled('free_caller')) {
            $query->where('is_free_caller', $request->boolean('free_caller'));
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
            'total_free_callers' => User::where('is_free_caller', true)->count(),
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
     * Manually Add, Deduct, or Set coins for user balance.
     */
    public function adjustCoins(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Graceful redirect if accessed via GET
        if ($request->isMethod('GET')) {
            return redirect()->route('admin.users.show', $user->id)
                ->with('info', "Adjust coins for {$user->display_name} using the Action buttons.");
        }

        $request->validate([
            'action' => 'required|in:add,deduct,set',
            'amount' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
        ]);

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
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json([
                            'status' => false,
                            'message' => "User only has {$user->coins} coins. Cannot deduct {$amount} coins."
                        ], 422);
                    }
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

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => true,
                    'message' => $message,
                    'data' => [
                        'user_id' => $user->id,
                        'coins' => $user->coins,
                        'formatted_coins' => number_format($user->coins),
                    ]
                ]);
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to adjust coins: ' . $e->getMessage()
                ], 500);
            }
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

    /**
     * Toggle lock/unlock status of user.
     */
    public function toggleLock(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->is_locked = !$user->is_locked;
        if ($user->is_locked) {
            $user->locked_reason = $request->input('reason') ?: 'Locked by Admin';
            $user->locked_at = now();
            $user->is_active = false;
        } else {
            $user->locked_reason = null;
            $user->unlocked_at = now();
            $user->is_active = true;
        }
        $user->save();

        $lockStr = $user->is_locked ? 'Locked' : 'Unlocked';
        return back()->with('success', "User {$user->display_name} account has been {$lockStr}.");
    }

    /**
     * Toggle Free Caller / Free Host status of user.
     * When active, user can call anyone for free even with 0 coins.
     */
    public function toggleFreeCaller($id)
    {
        $user = User::findOrFail($id);
        $user->is_free_caller = !$user->is_free_caller;
        $user->save();

        $freeStr = $user->is_free_caller 
            ? "designated as Free Host (can make unlimited calls with 0 coin balance)" 
            : "Free Host permission revoked";

        return back()->with('success', "User {$user->display_name} has been {$freeStr}.");
    }

    /**
     * Delete user permanently or soft-delete with audit reason.
     */
    public function destroy(Request $request, $id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $displayName = $user->display_name;
        $reason = $request->input('reason', 'Deleted by Administrator');

        // Prevent deleting Super Admin accounts or the currently logged-in admin
        if ($user->isSuperAdmin() || in_array(strtolower($user->email ?? ''), ['admin@gmail.com', 'admin@chinchins.live', 'nazmul@gmail.com', 'admin@admin.com']) || ($user->account_id ?? '') === '1000000001' || $user->id === auth()->id()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Super Administrator account cannot be deleted.',
                ], 403);
            }
            return back()->with('error', 'Super Administrator account cannot be deleted.');
        }

        DB::beginTransaction();
        try {
            // 1. Revoke all active API tokens
            $user->tokens()->delete();

            // 2. Mark user inactive & locked
            $user->is_active = false;
            $user->is_locked = true;
            $user->locked_reason = 'Account has been deleted by Administrator';
            $user->deleted_reason = $reason;
            $user->deleted_by = auth()->user() ? (auth()->user()->name ?: 'Admin') : 'admin';

            // 3. Free up phone / email so a new account can register if needed later
            $timestamp = time();
            if ($user->phone && !str_starts_with($user->phone, 'deleted_')) {
                $user->phone = "deleted_{$timestamp}_" . $user->phone;
            }
            if ($user->email && !str_starts_with($user->email, 'deleted_')) {
                $user->email = "deleted_{$timestamp}_" . $user->email;
            }
            $user->save();

            // 4. Soft delete
            $user->delete();

            DB::commit();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => true,
                    'message' => "User {$displayName} has been successfully deleted.",
                ]);
            }

            return redirect()->route('admin.users.index')
                ->with('success', "User '{$displayName}' has been successfully deleted and logged out of all devices.");
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to delete user: ' . $e->getMessage(),
                ], 500);
            }
            return back()->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }
}
