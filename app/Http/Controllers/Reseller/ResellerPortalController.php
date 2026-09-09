<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\CoinTransaction;
use App\Models\PaymentMethod;
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
use Illuminate\Support\Str;

class ResellerPortalController extends Controller
{
    /**
     * Show Reseller Login Page.
     */
    public function showLoginForm()
    {
        if (session()->has('reseller_id')) {
            return redirect()->route('reseller.dashboard');
        }
        return view('reseller.auth.login');
    }

    /**
     * Handle Reseller Login.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $reseller = Reseller::where('email', strtolower(trim($request->email)))->first();

        if (!$reseller || !Hash::check($request->password, $reseller->password)) {
            return back()->with('error', 'Invalid email or password.')->withInput();
        }

        if (!$reseller->is_active) {
            return back()->with('error', 'Your reseller account is disabled. Please contact admin.')->withInput();
        }

        // Set Reseller session
        session(['reseller_id' => $reseller->id]);
        $reseller->update(['is_online' => true, 'last_seen_at' => now()]);

        return redirect()->route('reseller.dashboard')->with('success', "Welcome back, {$reseller->name}!");
    }

    /**
     * Reseller Logout.
     */
    public function logout()
    {
        $resellerId = session('reseller_id');
        if ($resellerId) {
            Reseller::where('id', $resellerId)->update(['is_online' => false, 'last_seen_at' => now()]);
        }
        session()->forget('reseller_id');

        return redirect()->route('reseller.login')->with('success', 'Logged out successfully.');
    }

    /**
     * Helper to get current logged in reseller.
     */
    protected function getReseller(): ?Reseller
    {
        $id = session('reseller_id');
        return $id ? Reseller::find($id) : null;
    }

    /**
     * Reseller Dashboard.
     */
    public function dashboard(Request $request)
    {
        $reseller = $this->getReseller();
        if (!$reseller) {
            return redirect()->route('reseller.login');
        }

        $stats = [
            'coins_balance' => $reseller->coins_balance,
            'total_sold' => $reseller->total_sold_coins,
            'total_transfers' => $reseller->transfers()->count(),
            'pending_deposits' => $reseller->deposits()->where('status', 'pending')->count(),
            'pending_withdrawals' => $reseller->withdrawals()->where('status', 'pending')->count(),
        ];

        $recentTransfers = $reseller->transfers()->with('user')->latest()->take(8)->get();
        $paymentMethods = PaymentMethod::where('is_active', true)->get();

        $settings = [
            'min_deposit' => ResellerSetting::get('min_deposit', '500'),
            'max_deposit' => ResellerSetting::get('max_deposit', '100000'),
            'min_withdraw' => ResellerSetting::get('min_withdraw', '1000'),
            'max_withdraw' => ResellerSetting::get('max_withdraw', '100000'),
            'withdraw_commission_rate' => ResellerSetting::get('withdraw_commission_rate', '2.50'),
            'reseller_coin_rate_per_bdt' => ResellerSetting::get('reseller_coin_rate_per_bdt', '60.00'),
            'reseller_instructions' => ResellerSetting::get('reseller_instructions', 'Transfer coins to valid user account IDs only.'),
        ];

        return view('reseller.dashboard', compact('reseller', 'stats', 'recentTransfers', 'paymentMethods', 'settings'));
    }

    /**
     * Validate User ID (AJAX).
     */
    public function validateUser(Request $request): JsonResponse
    {
        $identifier = trim($request->input('account_id') ?: $request->input('user_id'));

        if (!$identifier) {
            return response()->json(['status' => false, 'message' => 'Please provide a User ID or Account ID.'], 400);
        }

        $user = User::where('account_id', $identifier)
            ->orWhere('id', $identifier)
            ->orWhere('phone', $identifier)
            ->first();

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User not found for ID: ' . $identifier], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'User verified successfully',
            'data' => [
                'id' => $user->id,
                'account_id' => $user->account_id,
                'name' => $user->name,
                'nickname' => $user->nickname,
                'avatar_url' => $user->avatar_url,
                'level' => $user->level,
                'coins' => (int) $user->coins,
                'country' => $user->country,
            ]
        ]);
    }

    /**
     * Execute Coin Transfer from Reseller to User.
     */
    public function transferCoins(Request $request)
    {
        $reseller = $this->getReseller();
        if (!$reseller) {
            return redirect()->route('reseller.login');
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
            return back()->with('error', "Insufficient coins stock! You only have " . number_format($reseller->coins_balance) . " coins available.")->withInput();
        }

        $user = User::where('account_id', $identifier)
            ->orWhere('id', $identifier)
            ->orWhere('phone', $identifier)
            ->first();

        if (!$user) {
            return back()->with('error', "User with ID '{$identifier}' could not be found.")->withInput();
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

            // Update Wallet if exists
            try {
                $wallet = Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 0]);
                $wallet->balance += $coins;
                $wallet->save();
            } catch (\Throwable $we) {}

            // Record Reseller Transfer
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
                    'description' => "Coins recharged by Reseller: {$reseller->name} (Trx #{$transfer->id})",
                ]);
            } catch (\Throwable $cte) {}

            // Send confirmation system message to chat if conversation exists
            try {
                ResellerChatMessage::create([
                    'reseller_id' => $reseller->id,
                    'user_id' => $user->id,
                    'sender_type' => 'reseller',
                    'sender_id' => $reseller->id,
                    'type' => 'system',
                    'message' => "✅ Successfully recharged " . number_format($coins) . " gems to your account (ID: " . ($user->account_id ?: $user->id) . ")! Thank you!",
                    'coins_amount' => $coins,
                    'is_read' => false,
                ]);
            } catch (\Throwable $cme) {}

            DB::commit();

            return redirect()->route('reseller.dashboard')->with('success', "Successfully transferred " . number_format($coins) . " gems to {$user->name} (UID: {$user->account_id})!");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Transfer failed: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Submit Refill / Deposit Request to Admin.
     */
    public function submitDeposit(Request $request)
    {
        $reseller = $this->getReseller();
        if (!$reseller) {
            return redirect()->route('reseller.login');
        }

        $minDeposit = (float) ResellerSetting::get('min_deposit', '500');
        $maxDeposit = (float) ResellerSetting::get('max_deposit', '100000');
        $ratePerBdt = (float) ResellerSetting::get('reseller_coin_rate_per_bdt', '60.00');

        $request->validate([
            'amount_bdt' => "required|numeric|min:{$minDeposit}|max:{$maxDeposit}",
            'payment_method' => 'required|string|max:50',
            'sender_number' => 'required|string|max:50',
            'transaction_id' => 'required|string|max:100',
            'screenshot' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'notes' => 'nullable|string|max:255',
        ]);

        $bdt = (float) $request->amount_bdt;
        $coinsRequested = (int) round($bdt * $ratePerBdt);

        $data = [
            'reseller_id' => $reseller->id,
            'payment_method' => $request->payment_method,
            'sender_number' => $request->sender_number,
            'transaction_id' => $request->transaction_id,
            'amount_bdt' => $bdt,
            'coins_requested' => $coinsRequested,
            'notes' => $request->notes,
            'status' => 'pending',
        ];

        if ($request->hasFile('screenshot')) {
            $file = $request->file('screenshot');
            $filename = 'dep_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $destDir = public_path('uploads/reseller');
            if (!file_exists($destDir)) {
                @mkdir($destDir, 0777, true);
            }
            $file->move($destDir, $filename);
            $data['screenshot'] = 'uploads/reseller/' . $filename;
        }

        ResellerDeposit::create($data);

        return back()->with('success', "Deposit request for " . number_format($coinsRequested) . " coins submitted! Awaiting Admin approval.");
    }

    /**
     * Submit Withdrawal Request to Admin.
     */
    public function submitWithdrawal(Request $request)
    {
        $reseller = $this->getReseller();
        if (!$reseller) {
            return redirect()->route('reseller.login');
        }

        $minWithdraw = (int) ResellerSetting::get('min_withdraw', '1000');
        $maxWithdraw = (int) ResellerSetting::get('max_withdraw', '100000');
        $commissionRate = (float) ResellerSetting::get('withdraw_commission_rate', '2.50');
        $ratePerBdt = (float) ResellerSetting::get('reseller_coin_rate_per_bdt', '60.00');

        $request->validate([
            'coins_amount' => "required|integer|min:{$minWithdraw}|max:{$maxWithdraw}",
            'payment_method' => 'required|string|max:50',
            'account_number' => 'required|string|max:50',
            'account_name' => 'nullable|string|max:100',
            'reseller_notes' => 'nullable|string|max:255',
        ]);

        $coins = (int) $request->coins_amount;

        if ($reseller->coins_balance < $coins) {
            return back()->with('error', "Insufficient coins! You have " . number_format($reseller->coins_balance) . " coins.")->withInput();
        }

        $grossBdt = round($coins / ($ratePerBdt > 0 ? $ratePerBdt : 60), 2);
        $commissionAmount = round(($grossBdt * $commissionRate) / 100, 2);
        $netBdt = $grossBdt - $commissionAmount;

        DB::beginTransaction();
        try {
            // Deduct coins immediately from Reseller Wallet
            $reseller->coins_balance -= $coins;
            $reseller->save();

            ResellerWithdrawal::create([
                'reseller_id' => $reseller->id,
                'payment_method' => $request->payment_method,
                'account_number' => $request->account_number,
                'account_name' => $request->account_name,
                'coins_amount' => $coins,
                'gross_bdt' => $grossBdt,
                'commission_percentage' => $commissionRate,
                'commission_amount' => $commissionAmount,
                'net_bdt' => $netBdt,
                'reseller_notes' => $request->reseller_notes,
                'status' => 'pending',
            ]);

            DB::commit();

            return back()->with('success', "Withdrawal request for ৳" . number_format($netBdt, 2) . " submitted successfully! Coins deducted pending Admin payout.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Withdrawal request failed: ' . $e->getMessage());
        }
    }

    /**
     * Reseller Live Chat Interface with Users.
     */
    public function chat(Request $request, $userId = null)
    {
        $reseller = $this->getReseller();
        if (!$reseller) {
            return redirect()->route('reseller.login');
        }

        // Get distinct users who messaged this reseller
        $conversationUserIds = ResellerChatMessage::where('reseller_id', $reseller->id)
            ->select('user_id')
            ->distinct()
            ->pluck('user_id')
            ->filter();

        $conversations = User::whereIn('id', $conversationUserIds)
            ->get()
            ->map(function ($u) use ($reseller) {
                $lastMsg = ResellerChatMessage::where('reseller_id', $reseller->id)
                    ->where('user_id', $u->id)
                    ->latest()
                    ->first();
                $unread = ResellerChatMessage::where('reseller_id', $reseller->id)
                    ->where('user_id', $u->id)
                    ->where('sender_type', 'user')
                    ->where('is_read', false)
                    ->count();

                $u->last_message = $lastMsg;
                $u->unread_count = $unread;
                return $u;
            })
            ->sortByDesc(fn($u) => $u->last_message?->created_at);

        $selectedUser = null;
        $messages = collect();

        if ($userId) {
            $selectedUser = User::find($userId);
            if ($selectedUser) {
                // Mark unread as read
                ResellerChatMessage::where('reseller_id', $reseller->id)
                    ->where('user_id', $selectedUser->id)
                    ->where('sender_type', 'user')
                    ->update(['is_read' => true, 'read_at' => now()]);

                $messages = ResellerChatMessage::where('reseller_id', $reseller->id)
                    ->where('user_id', $selectedUser->id)
                    ->orderBy('created_at', 'asc')
                    ->get();
            }
        } elseif ($conversations->isNotEmpty()) {
            return redirect()->route('reseller.chat.user', $conversations->first()->id);
        }

        return view('reseller.chat', compact('reseller', 'conversations', 'selectedUser', 'messages'));
    }

    /**
     * Send Message from Reseller to User in Chat.
     */
    public function sendMessage(Request $request)
    {
        $reseller = $this->getReseller();
        if (!$reseller) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'message' => 'nullable|string',
            'image' => 'nullable|image|max:5120',
        ]);

        $userId = $request->user_id;
        $msgText = $request->message;
        $mediaUrl = null;
        $type = 'text';

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'chat_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $destDir = public_path('uploads/reseller');
            if (!file_exists($destDir)) {
                @mkdir($destDir, 0777, true);
            }
            $file->move($destDir, $filename);
            $mediaUrl = 'uploads/reseller/' . $filename;
            $type = 'image';
        }

        $chatMsg = ResellerChatMessage::create([
            'reseller_id' => $reseller->id,
            'user_id' => $userId,
            'sender_type' => 'reseller',
            'sender_id' => $reseller->id,
            'type' => $type,
            'message' => $msgText,
            'media_url' => $mediaUrl,
            'is_read' => false,
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'Message sent',
                'data' => $chatMsg,
            ]);
        }

        return back();
    }

    /**
     * Reseller Transfer History Page.
     */
    public function transfers(Request $request)
    {
        $reseller = $this->getReseller();
        if (!$reseller) {
            return redirect()->route('reseller.login');
        }

        $transfers = $reseller->transfers()->with('user')->latest()->paginate(20);
        return view('reseller.transfers', compact('reseller', 'transfers'));
    }

    /**
     * Reseller Deposits Page.
     */
    public function deposits(Request $request)
    {
        $reseller = $this->getReseller();
        if (!$reseller) {
            return redirect()->route('reseller.login');
        }

        $deposits = $reseller->deposits()->latest()->paginate(20);
        $paymentMethods = PaymentMethod::where('is_active', true)->get();
        return view('reseller.deposits', compact('reseller', 'deposits', 'paymentMethods'));
    }

    /**
     * Reseller Withdrawals Page.
     */
    public function withdrawals(Request $request)
    {
        $reseller = $this->getReseller();
        if (!$reseller) {
            return redirect()->route('reseller.login');
        }

        $withdrawals = $reseller->withdrawals()->latest()->paginate(20);
        $paymentMethods = PaymentMethod::where('is_active', true)->get();
        return view('reseller.withdrawals', compact('reseller', 'withdrawals', 'paymentMethods'));
    }

    /**
     * Reseller Live Chat with Admin Support.
     */
    public function adminChat(Request $request)
    {
        $reseller = $this->getReseller();
        if (!$reseller) {
            return redirect()->route('reseller.login');
        }

        // Mark unread admin messages as read
        ResellerChatMessage::where('reseller_id', $reseller->id)
            ->where('user_id', null)
            ->where('sender_type', 'admin')
            ->update(['is_read' => true, 'read_at' => now()]);

        $messages = ResellerChatMessage::where('reseller_id', $reseller->id)
            ->where('user_id', null)
            ->orderBy('created_at', 'asc')
            ->get();

        return view('reseller.admin_chat', compact('reseller', 'messages'));
    }

    /**
     * Send Message to Admin.
     */
    public function sendAdminChatMessage(Request $request)
    {
        $reseller = $this->getReseller();
        if (!$reseller) {
            return redirect()->route('reseller.login');
        }

        $request->validate([
            'message' => 'nullable|string',
            'image' => 'nullable|image|max:5120',
        ]);

        $mediaUrl = null;
        $type = 'text';

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'reseller_admin_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $destDir = public_path('uploads/reseller');
            if (!file_exists($destDir)) {
                @mkdir($destDir, 0777, true);
            }
            $file->move($destDir, $filename);
            $mediaUrl = 'uploads/reseller/' . $filename;
            $type = 'image';
        }

        ResellerChatMessage::create([
            'reseller_id' => $reseller->id,
            'user_id' => null,
            'sender_type' => 'reseller',
            'sender_id' => $reseller->id,
            'type' => $type,
            'message' => $request->message,
            'media_url' => $mediaUrl,
            'is_read' => false,
        ]);

        return back()->with('success', 'Message sent to Admin.');
    }
}
