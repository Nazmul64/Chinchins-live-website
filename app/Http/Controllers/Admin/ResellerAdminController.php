<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reseller;
use App\Models\ResellerDeposit;
use App\Models\ResellerSetting;
use App\Models\ResellerTransfer;
use App\Models\ResellerWithdrawal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResellerAdminController extends Controller
{
    /**
     * Resellers Listing & Management.
     */
    public function index(Request $request)
    {
        $query = Reseller::withCount(['transfers', 'deposits', 'withdrawals']);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('location', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $resellers = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total_resellers' => Reseller::count(),
            'active_resellers' => Reseller::where('is_active', true)->count(),
            'online_resellers' => Reseller::where('is_online', true)->count(),
            'total_coins_held' => Reseller::sum('coins_balance'),
            'total_coins_sold' => Reseller::sum('total_sold_coins'),
            'pending_deposits' => ResellerDeposit::where('status', 'pending')->count(),
            'pending_withdrawals' => ResellerWithdrawal::where('status', 'pending')->count(),
        ];

        return view('admin.resellers.index', compact('resellers', 'stats'));
    }

    /**
     * Show Create Reseller page.
     */
    public function create()
    {
        return view('admin.resellers.create');
    }

    /**
     * Store newly created reseller.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:resellers,email',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string|max:30',
            'level' => 'nullable|string|max:20',
            'location' => 'nullable|string|max:100',
            'age' => 'nullable|integer|min:18|max:100',
            'gender' => 'nullable|in:male,female,other',
            'bio' => 'nullable|string',
            'discount_tag' => 'nullable|string|max:50',
            'badge_title' => 'nullable|string|max:50',
            'initial_coins' => 'nullable|integer|min:0',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:5120',
            'is_active' => 'nullable|boolean',
            'is_online' => 'nullable|boolean',
        ]);

        try {
            $data = [
                'name' => $request->name,
                'email' => strtolower(trim($request->email)),
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'level' => $request->level ?: 'Lv1',
                'location' => $request->location ?: 'Dhaka, Bangladesh',
                'age' => $request->age ?: 25,
                'gender' => $request->gender ?: 'male',
                'bio' => $request->bio,
                'discount_tag' => $request->discount_tag ?: 'Up To 29%↑',
                'badge_title' => $request->badge_title ?: 'Diamond Reseller',
                'coins_balance' => (int) ($request->initial_coins ?: 0),
                'total_deposited_coins' => (int) ($request->initial_coins ?: 0),
                'is_active' => $request->has('is_active'),
                'is_online' => $request->has('is_online'),
            ];

            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $filename = 'reseller_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
                $destDir = public_path('uploads/reseller');
                if (!file_exists($destDir)) {
                    @mkdir($destDir, 0777, true);
                }
                $file->move($destDir, $filename);
                $data['avatar'] = 'uploads/reseller/' . $filename;
            }

            $reseller = Reseller::create($data);

            return redirect()->route('admin.resellers.index')->with('success', "Reseller '{$reseller->name}' created successfully!");
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to create reseller: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Update reseller details.
     */
    public function update(Request $request, $id)
    {
        $reseller = Reseller::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:resellers,email,' . $reseller->id,
            'password' => 'nullable|string|min:6|confirmed',
            'phone' => 'nullable|string|max:30',
            'level' => 'nullable|string|max:20',
            'location' => 'nullable|string|max:100',
            'age' => 'nullable|integer|min:18|max:100',
            'gender' => 'nullable|in:male,female,other',
            'bio' => 'nullable|string',
            'discount_tag' => 'nullable|string|max:50',
            'badge_title' => 'nullable|string|max:50',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:5120',
            'is_active' => 'nullable|boolean',
            'is_online' => 'nullable|boolean',
        ]);

        try {
            $data = [
                'name' => $request->name,
                'email' => strtolower(trim($request->email)),
                'phone' => $request->phone,
                'level' => $request->level ?: $reseller->level,
                'location' => $request->location ?: $reseller->location,
                'age' => $request->age ?: $reseller->age,
                'gender' => $request->gender ?: $reseller->gender,
                'bio' => $request->bio,
                'discount_tag' => $request->discount_tag ?: 'Up To 29%↑',
                'badge_title' => $request->badge_title ?: 'Diamond Reseller',
                'is_active' => $request->has('is_active'),
                'is_online' => $request->has('is_online'),
            ];

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $filename = 'reseller_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
                $destDir = public_path('uploads/reseller');
                if (!file_exists($destDir)) {
                    @mkdir($destDir, 0777, true);
                }
                $file->move($destDir, $filename);
                $data['avatar'] = 'uploads/reseller/' . $filename;
            }

            $reseller->update($data);

            return redirect()->route('admin.resellers.index')->with('success', "Reseller '{$reseller->name}' updated successfully!");
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to update reseller: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Adjust Reseller Coin Balance directly by Admin.
     */
    public function adjustCoins(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:add,subtract,set',
            'coins' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:255',
        ]);

        $reseller = Reseller::findOrFail($id);
        $amount = (int) $request->coins;
        $prevBalance = $reseller->coins_balance;

        if ($request->action === 'add') {
            $reseller->coins_balance += $amount;
            $reseller->total_deposited_coins += $amount;
        } elseif ($request->action === 'subtract') {
            if ($reseller->coins_balance < $amount) {
                return back()->with('error', "Cannot subtract {$amount} coins. Reseller only has {$reseller->coins_balance} coins.");
            }
            $reseller->coins_balance -= $amount;
        } elseif ($request->action === 'set') {
            $reseller->coins_balance = $amount;
        }

        $reseller->save();

        return redirect()->route('admin.resellers.index')->with('success', "Reseller '{$reseller->name}' balance updated from {$prevBalance} to {$reseller->coins_balance} coins.");
    }

    /**
     * Toggle reseller active status.
     */
    public function toggleStatus($id)
    {
        $reseller = Reseller::findOrFail($id);
        $reseller->is_active = !$reseller->is_active;
        $reseller->save();

        $status = $reseller->is_active ? 'Activated' : 'Disabled';
        return back()->with('success', "Reseller {$reseller->name} has been {$status}.");
    }

    /**
     * Toggle reseller online status.
     */
    public function toggleOnline($id)
    {
        $reseller = Reseller::findOrFail($id);
        $reseller->is_online = !$reseller->is_online;
        $reseller->save();

        $status = $reseller->is_online ? 'Online' : 'Offline';
        return back()->with('success', "Reseller {$reseller->name} is now marked {$status}.");
    }

    /**
     * Delete reseller.
     */
    public function destroy($id)
    {
        $reseller = Reseller::findOrFail($id);
        $reseller->delete();

        return redirect()->route('admin.resellers.index')->with('success', 'Reseller removed successfully.');
    }

    /**
     * Reseller Coin Transfers to Users Ledger.
     */
    public function transfers(Request $request)
    {
        $query = ResellerTransfer::with(['reseller', 'user']);

        if ($request->filled('reseller_id')) {
            $query->where('reseller_id', $request->reseller_id);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('target_account_id', 'like', "%{$s}%")
                  ->orWhere('transaction_id', 'like', "%{$s}%")
                  ->orWhereHas('user', function ($uq) use ($s) {
                      $uq->where('name', 'like', "%{$s}%")->orWhere('account_id', 'like', "%{$s}%");
                  });
            });
        }

        $transfers = $query->latest()->paginate(20)->withQueryString();
        $resellers = Reseller::where('is_active', true)->orderBy('name')->get();

        $stats = [
            'total_transfers' => ResellerTransfer::count(),
            'total_coins_transferred' => ResellerTransfer::where('status', 'completed')->sum('coins'),
            'total_bdt_transferred' => ResellerTransfer::where('status', 'completed')->sum('amount_bdt'),
        ];

        return view('admin.resellers.transfers', compact('transfers', 'resellers', 'stats'));
    }

    /**
     * Reseller Deposit Requests from Admin.
     */
    public function deposits(Request $request)
    {
        $query = ResellerDeposit::with(['reseller', 'approvedByUser']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $deposits = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'pending' => ResellerDeposit::where('status', 'pending')->count(),
            'approved' => ResellerDeposit::where('status', 'approved')->count(),
            'total_approved_coins' => ResellerDeposit::where('status', 'approved')->sum('coins_requested'),
            'total_approved_bdt' => ResellerDeposit::where('status', 'approved')->sum('amount_bdt'),
        ];

        return view('admin.resellers.deposits', compact('deposits', 'stats'));
    }

    /**
     * Approve Reseller Deposit Request.
     */
    public function approveDeposit(Request $request, $id)
    {
        $deposit = ResellerDeposit::with('reseller')->findOrFail($id);

        if ($deposit->status !== 'pending') {
            return back()->with('error', 'This deposit request has already been processed.');
        }

        DB::beginTransaction();
        try {
            $deposit->status = 'approved';
            $deposit->admin_notes = $request->input('admin_notes');
            $deposit->approved_at = now();
            $deposit->approved_by = auth()->id();
            $deposit->save();

            // Credit Reseller Coins Balance
            $reseller = $deposit->reseller;
            $reseller->coins_balance += $deposit->coins_requested;
            $reseller->total_deposited_coins += $deposit->coins_requested;
            $reseller->save();

            DB::commit();

            return back()->with('success', "Deposit #{$deposit->id} approved. {$deposit->coins_requested} coins added to {$reseller->name}'s wallet.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Approval failed: ' . $e->getMessage());
        }
    }

    /**
     * Reject Reseller Deposit Request.
     */
    public function rejectDeposit(Request $request, $id)
    {
        $deposit = ResellerDeposit::findOrFail($id);

        if ($deposit->status !== 'pending') {
            return back()->with('error', 'This deposit request has already been processed.');
        }

        $deposit->status = 'rejected';
        $deposit->admin_notes = $request->input('admin_notes', 'Rejected by Admin');
        $deposit->save();

        return back()->with('success', "Deposit #{$deposit->id} has been rejected.");
    }

    /**
     * Reseller Withdrawal Requests.
     */
    public function withdrawals(Request $request)
    {
        $query = ResellerWithdrawal::with(['reseller', 'processedByUser']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $withdrawals = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'pending' => ResellerWithdrawal::where('status', 'pending')->count(),
            'approved' => ResellerWithdrawal::where('status', 'approved')->count(),
            'total_withdrawn_coins' => ResellerWithdrawal::where('status', 'approved')->sum('coins_amount'),
            'total_net_payout_bdt' => ResellerWithdrawal::where('status', 'approved')->sum('net_bdt'),
            'total_commission_bdt' => ResellerWithdrawal::where('status', 'approved')->sum('commission_amount'),
        ];

        return view('admin.resellers.withdrawals', compact('withdrawals', 'stats'));
    }

    /**
     * Approve Reseller Withdrawal Request.
     */
    public function approveWithdrawal(Request $request, $id)
    {
        $withdrawal = ResellerWithdrawal::with('reseller')->findOrFail($id);

        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'This withdrawal request has already been processed.');
        }

        $withdrawal->status = 'approved';
        $withdrawal->transaction_id = $request->input('transaction_id');
        $withdrawal->admin_notes = $request->input('admin_notes');
        $withdrawal->processed_at = now();
        $withdrawal->processed_by = auth()->id();
        $withdrawal->save();

        $reseller = $withdrawal->reseller;
        $reseller->total_withdrawn_coins += $withdrawal->coins_amount;
        $reseller->save();

        return back()->with('success', "Withdrawal #{$withdrawal->id} marked as Paid & Approved.");
    }

    /**
     * Reject Reseller Withdrawal Request (refund coins to reseller).
     */
    public function rejectWithdrawal(Request $request, $id)
    {
        $withdrawal = ResellerWithdrawal::with('reseller')->findOrFail($id);

        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'This withdrawal request has already been processed.');
        }

        DB::beginTransaction();
        try {
            $withdrawal->status = 'rejected';
            $withdrawal->admin_notes = $request->input('admin_notes', 'Rejected by Admin');
            $withdrawal->save();

            // Refund Coins to Reseller Wallet
            $reseller = $withdrawal->reseller;
            $reseller->coins_balance += $withdrawal->coins_amount;
            $reseller->save();

            DB::commit();

            return back()->with('success', "Withdrawal #{$withdrawal->id} rejected. {$withdrawal->coins_amount} coins refunded to {$reseller->name}.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Rejection failed: ' . $e->getMessage());
        }
    }

    /**
     * Reseller System Global Settings.
     */
    public function settings()
    {
        $settings = [
            'min_deposit' => ResellerSetting::get('min_deposit', '500'),
            'max_deposit' => ResellerSetting::get('max_deposit', '100000'),
            'min_withdraw' => ResellerSetting::get('min_withdraw', '1000'),
            'max_withdraw' => ResellerSetting::get('max_withdraw', '100000'),
            'withdraw_commission_rate' => ResellerSetting::get('withdraw_commission_rate', '2.50'),
            'reseller_coin_rate_per_bdt' => ResellerSetting::get('reseller_coin_rate_per_bdt', '60.00'),
            'reseller_offer_badge' => ResellerSetting::get('reseller_offer_badge', 'Up To 29%↑'),
            'reseller_instructions' => ResellerSetting::get('reseller_instructions', "1. Transfer coins to valid User Account ID only.\n2. Confirm bKash/Nagad TrxID before sending coins.\n3. Contact admin live support for coin balance refills."),
        ];

        return view('admin.resellers.settings', compact('settings'));
    }

    /**
     * Update Reseller System Global Settings.
     */
    public function updateSettings(Request $request)
    {
        $keys = [
            'min_deposit',
            'max_deposit',
            'min_withdraw',
            'max_withdraw',
            'withdraw_commission_rate',
            'reseller_coin_rate_per_bdt',
            'reseller_offer_badge',
            'reseller_instructions',
        ];

        foreach ($keys as $k) {
            if ($request->has($k)) {
                ResellerSetting::set($k, $request->input($k));
            }
        }

        return back()->with('success', 'Reseller settings updated successfully!');
    }

    /**
     * Admin Live Support Chat with Resellers.
     */
    public function chat(Request $request, $resellerId = null)
    {
        $resellers = Reseller::where('is_active', true)
            ->withCount(['chatMessages as unread_count' => function ($q) {
                $q->where('sender_type', 'reseller')->where('user_id', null)->where('is_read', false);
            }])
            ->orderBy('is_online', 'desc')
            ->orderBy('name', 'asc')
            ->get();

        $selectedReseller = null;
        $messages = collect();

        if ($resellerId) {
            $selectedReseller = Reseller::find($resellerId);
            if ($selectedReseller) {
                // Mark messages as read
                \App\Models\ResellerChatMessage::where('reseller_id', $selectedReseller->id)
                    ->where('user_id', null)
                    ->where('sender_type', 'reseller')
                    ->update(['is_read' => true, 'read_at' => now()]);

                $messages = \App\Models\ResellerChatMessage::where('reseller_id', $selectedReseller->id)
                    ->where('user_id', null)
                    ->orderBy('created_at', 'asc')
                    ->get();
            }
        } elseif ($resellers->isNotEmpty()) {
            return redirect()->route('admin.resellers.chat.selected', $resellers->first()->id);
        }

        return view('admin.resellers.chat', compact('resellers', 'selectedReseller', 'messages'));
    }

    /**
     * Admin Sends message to Reseller.
     */
    public function sendAdminMessage(Request $request)
    {
        $request->validate([
            'reseller_id' => 'required|exists:resellers,id',
            'message' => 'nullable|string',
            'image' => 'nullable|image|max:5120',
        ]);

        $resellerId = $request->reseller_id;
        $mediaUrl = null;
        $type = 'text';

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'admin_chat_' . time() . '_' . \Illuminate\Support\Str::random(6) . '.' . $file->getClientOriginalExtension();
            $destDir = public_path('uploads/reseller');
            if (!file_exists($destDir)) {
                @mkdir($destDir, 0777, true);
            }
            $file->move($destDir, $filename);
            $mediaUrl = 'uploads/reseller/' . $filename;
            $type = 'image';
        }

        \App\Models\ResellerChatMessage::create([
            'reseller_id' => $resellerId,
            'user_id' => null, // null means Admin <-> Reseller direct channel
            'sender_type' => 'admin',
            'sender_id' => auth()->id(),
            'type' => $type,
            'message' => $request->message,
            'media_url' => $mediaUrl,
            'is_read' => false,
        ]);

        return back()->with('success', 'Message sent to reseller.');
    }
}

