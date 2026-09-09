<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\CoinPackageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepositRequestController;
use App\Http\Controllers\Admin\LoginHistoryController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\ProfileAdminController;
use App\Http\Controllers\Admin\RoleManagementController;
use App\Http\Controllers\Admin\StaffManagementController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Authentication Routes (Both /login and /admin/login supported)
Route::get('/', [AuthController::class, 'showLoginForm'])->name('home');
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::get('/admin/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Admin Dashboard & Management Routes
Route::middleware(['auth', 'admin.status'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboards
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/sub-admin', [DashboardController::class, 'subAdmin'])->name('dashboard.sub_admin');
    Route::get('/dashboard/manager', [DashboardController::class, 'manager'])->name('dashboard.manager');
    Route::get('/dashboard/employee', [DashboardController::class, 'employee'])->name('dashboard.employee');
    Route::get('/profile', [ProfileAdminController::class, 'index'])->name('profile');

    // Super Admin Web One-Click Setup & Cache Clearing Trigger
    Route::get('/system/setup', function () {
        if (!auth()->user() || !auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized');
        }
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'RoleAndPermissionSeeder', '--force' => true]);
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        return redirect()->route('admin.dashboard')->with('success', 'Database tables migrated, roles seeded, and cache cleared successfully!');
    })->name('system.setup');

    // Users Management
    Route::middleware(['permission:users.view'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
    });
    Route::match(['get', 'post'], '/users/{id}/adjust-coins', [UserController::class, 'adjustCoins'])->name('users.adjust-coins')->middleware('permission:users.adjust_coins');
    Route::match(['get', 'post'], '/users/{id}/coins', [UserController::class, 'adjustCoins'])->middleware('permission:users.adjust_coins');
    Route::match(['get', 'post'], '/users/{id}/adjust-coin', [UserController::class, 'adjustCoins'])->middleware('permission:users.adjust_coins');
    Route::match(['get', 'post'], '/users/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status')->middleware('permission:users.toggle_status');
    Route::match(['get', 'post'], '/users/{id}/toggle-lock', [UserController::class, 'toggleLock'])->name('users.toggle-lock')->middleware('permission:users.toggle_lock');
    Route::match(['get', 'post'], '/users/{id}/toggle-free-caller', [UserController::class, 'toggleFreeCaller'])->name('users.toggle-free-caller')->middleware('permission:users.free_caller');
    Route::match(['get', 'post'], '/users/{id}/toggle-free-host', [UserController::class, 'toggleFreeCaller'])->name('users.toggle-free-host')->middleware('permission:users.free_caller');
    Route::match(['get', 'post'], '/users/{id}/free-caller', [UserController::class, 'toggleFreeCaller'])->middleware('permission:users.free_caller');
    Route::match(['get', 'post'], '/users/{id}/free-host', [UserController::class, 'toggleFreeCaller'])->middleware('permission:users.free_caller');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy')->middleware('permission:users.delete');
    Route::post('/users/{id}/delete', [UserController::class, 'destroy'])->name('users.delete')->middleware('permission:users.delete');

    // 🎧 24/7 Live Support for App Users
    Route::get('/support', [\App\Http\Controllers\Admin\UserSupportAdminController::class, 'index'])->name('support.index');
    Route::get('/support/user/{userId}', [\App\Http\Controllers\Admin\UserSupportAdminController::class, 'showUserChat'])->name('support.user');
    Route::post('/support/user/{userId}/reply', [\App\Http\Controllers\Admin\UserSupportAdminController::class, 'reply'])->name('support.reply');

    // Payment Methods Management
    Route::get('/payment-methods', [PaymentMethodController::class, 'index'])->name('payment-methods.index')->middleware('permission:payment_methods.view');
    Route::post('/payment-methods', [PaymentMethodController::class, 'store'])->name('payment-methods.store')->middleware('permission:payment_methods.create');
    Route::put('/payment-methods/{id}', [PaymentMethodController::class, 'update'])->name('payment-methods.update')->middleware('permission:payment_methods.edit');
    Route::delete('/payment-methods/{id}', [PaymentMethodController::class, 'destroy'])->name('payment-methods.destroy')->middleware('permission:payment_methods.delete');
    Route::post('/payment-methods/{id}/toggle-status', [PaymentMethodController::class, 'toggleStatus'])->name('payment-methods.toggle-status')->middleware('permission:payment_methods.toggle_status');

    // Resellers Management
    Route::prefix('resellers')->name('resellers.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ResellerAdminController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\ResellerAdminController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\ResellerAdminController::class, 'store'])->name('store');
        Route::put('/{id}', [\App\Http\Controllers\Admin\ResellerAdminController::class, 'update'])->name('update');
        Route::delete('/{id}', [\App\Http\Controllers\Admin\ResellerAdminController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/toggle-status', [\App\Http\Controllers\Admin\ResellerAdminController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{id}/toggle-online', [\App\Http\Controllers\Admin\ResellerAdminController::class, 'toggleOnline'])->name('toggle-online');
        Route::post('/{id}/adjust-coins', [\App\Http\Controllers\Admin\ResellerAdminController::class, 'adjustCoins'])->name('adjust-coins');

        // Admin <-> Reseller Live Support Chat
        Route::get('/chat/{resellerId?}', [\App\Http\Controllers\Admin\ResellerAdminController::class, 'chat'])->name('chat');
        Route::get('/chat/reseller/{resellerId}', [\App\Http\Controllers\Admin\ResellerAdminController::class, 'chat'])->name('chat.selected');
        Route::post('/chat/send', [\App\Http\Controllers\Admin\ResellerAdminController::class, 'sendAdminMessage'])->name('chat.send');

        // Reseller Transfers Ledger
        Route::get('/transfers', [\App\Http\Controllers\Admin\ResellerAdminController::class, 'transfers'])->name('transfers');

        // Reseller Deposits (Refill Requests)
        Route::get('/deposits', [\App\Http\Controllers\Admin\ResellerAdminController::class, 'deposits'])->name('deposits');
        Route::post('/deposits/{id}/approve', [\App\Http\Controllers\Admin\ResellerAdminController::class, 'approveDeposit'])->name('deposits.approve');
        Route::post('/deposits/{id}/reject', [\App\Http\Controllers\Admin\ResellerAdminController::class, 'rejectDeposit'])->name('deposits.reject');

        // Reseller Withdrawals
        Route::get('/withdrawals', [\App\Http\Controllers\Admin\ResellerAdminController::class, 'withdrawals'])->name('withdrawals');
        Route::post('/withdrawals/{id}/approve', [\App\Http\Controllers\Admin\ResellerAdminController::class, 'approveWithdrawal'])->name('withdrawals.approve');
        Route::post('/withdrawals/{id}/reject', [\App\Http\Controllers\Admin\ResellerAdminController::class, 'rejectWithdrawal'])->name('withdrawals.reject');

        // Reseller Settings
        Route::get('/settings', [\App\Http\Controllers\Admin\ResellerAdminController::class, 'settings'])->name('settings');
        Route::post('/settings', [\App\Http\Controllers\Admin\ResellerAdminController::class, 'updateSettings'])->name('settings.update');
    });

    // Coin Packages Management
    Route::get('/coin-packages', [CoinPackageController::class, 'index'])->name('coin-packages.index')->middleware('permission:coin_packages.view');
    Route::post('/coin-packages', [CoinPackageController::class, 'store'])->name('coin-packages.store')->middleware('permission:coin_packages.create');
    Route::put('/coin-packages/{id}', [CoinPackageController::class, 'update'])->name('coin-packages.update')->middleware('permission:coin_packages.edit');
    Route::delete('/coin-packages/{id}', [CoinPackageController::class, 'destroy'])->name('coin-packages.destroy')->middleware('permission:coin_packages.delete');
    Route::post('/coin-packages/{id}/toggle-status', [CoinPackageController::class, 'toggleStatus'])->name('coin-packages.toggle-status')->middleware('permission:coin_packages.toggle_status');

    // Manual Deposit Requests
    Route::get('/deposits', [DepositRequestController::class, 'index'])->name('deposits.index')->middleware('permission:deposits.view');
    Route::post('/deposits/{id}/approve', [DepositRequestController::class, 'approve'])->name('deposits.approve')->middleware('permission:deposits.approve');
    Route::post('/deposits/{id}/reject', [DepositRequestController::class, 'reject'])->name('deposits.reject')->middleware('permission:deposits.reject');

    // Coin Withdrawal Requests & Settings
    Route::get('/withdrawals', [\App\Http\Controllers\Admin\WithdrawalAdminController::class, 'index'])->name('withdrawals.index')->middleware('permission:withdrawals.view');
    Route::post('/withdrawals/{id}/approve', [\App\Http\Controllers\Admin\WithdrawalAdminController::class, 'approve'])->name('withdrawals.approve')->middleware('permission:withdrawals.approve');
    Route::post('/withdrawals/{id}/reject', [\App\Http\Controllers\Admin\WithdrawalAdminController::class, 'reject'])->name('withdrawals.reject')->middleware('permission:withdrawals.reject');
    Route::get('/withdrawals/settings', [\App\Http\Controllers\Admin\WithdrawalAdminController::class, 'settings'])->name('withdrawals.settings')->middleware('permission:withdrawals.settings');
    Route::post('/withdrawals/settings', [\App\Http\Controllers\Admin\WithdrawalAdminController::class, 'updateSettings'])->name('withdrawals.settings.update')->middleware('permission:withdrawals.settings');
    Route::post('/withdrawals/methods/{id}/toggle', [\App\Http\Controllers\Admin\WithdrawalAdminController::class, 'toggleMethodWithdraw'])->name('withdrawals.toggle-method')->middleware('permission:withdrawals.settings');

    // Audio & Video Call Sessions & Revenue Settings
    Route::get('/calls', [\App\Http\Controllers\Admin\CallAdminController::class, 'index'])->name('calls.index')->middleware('permission:calls.view');
    Route::get('/calls/settings', [\App\Http\Controllers\Admin\CallAdminController::class, 'settings'])->name('calls.settings')->middleware('permission:calls.settings');
    Route::post('/calls/settings', [\App\Http\Controllers\Admin\CallAdminController::class, 'updateSettings'])->name('calls.settings.update')->middleware('permission:calls.settings');

    // KYC Identity Verification Management
    Route::get('/kyc', [\App\Http\Controllers\Admin\KycAdminController::class, 'index'])->name('kyc.index')->middleware('permission:kyc.view');
    Route::get('/kyc/{id}', [\App\Http\Controllers\Admin\KycAdminController::class, 'show'])->name('kyc.show')->middleware('permission:kyc.view');
    Route::post('/kyc/{id}/approve', [\App\Http\Controllers\Admin\KycAdminController::class, 'approve'])->name('kyc.approve')->middleware('permission:kyc.approve');
    Route::post('/kyc/{id}/reject', [\App\Http\Controllers\Admin\KycAdminController::class, 'reject'])->name('kyc.reject')->middleware('permission:kyc.reject');
    Route::post('/kyc/{id}/revoke', [\App\Http\Controllers\Admin\KycAdminController::class, 'revoke'])->name('kyc.revoke')->middleware('permission:kyc.revoke');

    // Gifts & Rewards Management
    Route::get('/gifts', [\App\Http\Controllers\Admin\GiftController::class, 'index'])->name('gifts.index')->middleware('permission:gifts.view');
    Route::post('/gifts', [\App\Http\Controllers\Admin\GiftController::class, 'store'])->name('gifts.store')->middleware('permission:gifts.create');
    Route::put('/gifts/{id}', [\App\Http\Controllers\Admin\GiftController::class, 'update'])->name('gifts.update')->middleware('permission:gifts.edit');
    Route::delete('/gifts/{id}', [\App\Http\Controllers\Admin\GiftController::class, 'destroy'])->name('gifts.destroy')->middleware('permission:gifts.delete');
    Route::post('/gifts/{id}/toggle-status', [\App\Http\Controllers\Admin\GiftController::class, 'toggleStatus'])->name('gifts.toggle-status')->middleware('permission:gifts.edit');
    Route::post('/gifts/give-to-user', [\App\Http\Controllers\Admin\GiftController::class, 'giveGiftToUser'])->name('gifts.give')->middleware('permission:gifts.give_to_user');
    Route::post('/gifts/levels', [\App\Http\Controllers\Admin\GiftController::class, 'updateLevels'])->name('gifts.levels.update')->middleware('permission:gifts.settings');
    Route::get('/gifts/logs', [\App\Http\Controllers\Admin\GiftController::class, 'logs'])->name('gifts.logs')->middleware('permission:gifts.view');

    // Premium VIP Cards & Floating Home Banner Management
    Route::get('/vip-cards', [\App\Http\Controllers\Admin\VipCardAdminController::class, 'index'])->name('vip-cards.index')->middleware('permission:vip_cards.view');
    Route::post('/vip-cards', [\App\Http\Controllers\Admin\VipCardAdminController::class, 'store'])->name('vip-cards.store')->middleware('permission:vip_cards.create');
    Route::put('/vip-cards/{id}', [\App\Http\Controllers\Admin\VipCardAdminController::class, 'update'])->name('vip-cards.update')->middleware('permission:vip_cards.edit');
    Route::delete('/vip-cards/{id}', [\App\Http\Controllers\Admin\VipCardAdminController::class, 'destroy'])->name('vip-cards.destroy')->middleware('permission:vip_cards.delete');
    Route::post('/vip-cards/{id}/toggle-status', [\App\Http\Controllers\Admin\VipCardAdminController::class, 'toggleStatus'])->name('vip-cards.toggle-status')->middleware('permission:vip_cards.toggle_status');
    Route::get('/vip-cards/subscriptions', [\App\Http\Controllers\Admin\VipCardAdminController::class, 'subscriptions'])->name('vip-cards.subscriptions')->middleware('permission:vip_cards.view');
    Route::post('/vip-cards/floating-banner', [\App\Http\Controllers\Admin\VipCardAdminController::class, 'updateFloatingBanner'])->name('vip-cards.floating-banner')->middleware('permission:vip_cards.floating_banner');

    // Spend Less, Get More Gems Management
    Route::get('/spend-less-cards', [\App\Http\Controllers\Admin\SpendLessCardAdminController::class, 'index'])->name('spend-less-cards.index')->middleware('permission:spend_less_cards.view');
    Route::post('/spend-less-cards', [\App\Http\Controllers\Admin\SpendLessCardAdminController::class, 'store'])->name('spend-less-cards.store')->middleware('permission:spend_less_cards.create');
    Route::put('/spend-less-cards/{id}', [\App\Http\Controllers\Admin\SpendLessCardAdminController::class, 'update'])->name('spend-less-cards.update')->middleware('permission:spend_less_cards.edit');
    Route::delete('/spend-less-cards/{id}', [\App\Http\Controllers\Admin\SpendLessCardAdminController::class, 'destroy'])->name('spend-less-cards.destroy')->middleware('permission:spend_less_cards.delete');
    Route::post('/spend-less-cards/{id}/toggle-status', [\App\Http\Controllers\Admin\SpendLessCardAdminController::class, 'toggleStatus'])->name('spend-less-cards.toggle-status')->middleware('permission:spend_less_cards.toggle_status');
    Route::get('/spend-less-cards/subscriptions', [\App\Http\Controllers\Admin\SpendLessCardAdminController::class, 'subscriptions'])->name('spend-less-cards.subscriptions')->middleware('permission:spend_less_cards.view');

    // Profile Bases & Level Badges Management
    Route::get('/profile-bases', [\App\Http\Controllers\Admin\ProfileBaseAdminController::class, 'index'])->name('profile-bases.index')->middleware('permission:level_badges.view');
    Route::post('/profile-bases/batch-update', [\App\Http\Controllers\Admin\ProfileBaseAdminController::class, 'batchUpdate'])->name('profile-bases.batch-update')->middleware('permission:level_badges.batch_update');
    Route::post('/profile-bases', [\App\Http\Controllers\Admin\ProfileBaseAdminController::class, 'store'])->name('profile-bases.store')->middleware('permission:level_badges.create');
    Route::put('/profile-bases/{id}', [\App\Http\Controllers\Admin\ProfileBaseAdminController::class, 'update'])->name('profile-bases.update')->middleware('permission:level_badges.edit');
    Route::delete('/profile-bases/{id}', [\App\Http\Controllers\Admin\ProfileBaseAdminController::class, 'destroy'])->name('profile-bases.destroy')->middleware('permission:level_badges.delete');
    Route::post('/profile-bases/{id}/toggle-status', [\App\Http\Controllers\Admin\ProfileBaseAdminController::class, 'toggleStatus'])->name('profile-bases.toggle-status')->middleware('permission:level_badges.edit');

    // My Bag Items & User Inventory Management
    Route::get('/my-bag', [\App\Http\Controllers\Admin\BagAdminController::class, 'index'])->name('my-bag.index')->middleware('permission:bag_items.view');
    Route::post('/my-bag', [\App\Http\Controllers\Admin\BagAdminController::class, 'store'])->name('my-bag.store')->middleware('permission:bag_items.create');
    Route::put('/my-bag/{id}', [\App\Http\Controllers\Admin\BagAdminController::class, 'update'])->name('my-bag.update')->middleware('permission:bag_items.edit');
    Route::delete('/my-bag/{id}', [\App\Http\Controllers\Admin\BagAdminController::class, 'destroy'])->name('my-bag.destroy')->middleware('permission:bag_items.delete');
    Route::post('/my-bag/{id}/toggle-status', [\App\Http\Controllers\Admin\BagAdminController::class, 'toggleStatus'])->name('my-bag.toggle-status')->middleware('permission:bag_items.edit');
    Route::post('/my-bag/give-user', [\App\Http\Controllers\Admin\BagAdminController::class, 'giveToUser'])->name('my-bag.give-user')->middleware('permission:bag_items.give_to_user');
    Route::get('/my-bag/inventory', [\App\Http\Controllers\Admin\BagAdminController::class, 'userInventory'])->name('my-bag.inventory')->middleware('permission:bag_items.view');

    // User Complaints & In-Chat Reports Moderation
    Route::get('/reports', [\App\Http\Controllers\Admin\ReportAdminController::class, 'index'])->name('reports.index')->middleware('permission:reports.view');
    Route::post('/reports/{id}/status', [\App\Http\Controllers\Admin\ReportAdminController::class, 'updateStatus'])->name('reports.update-status')->middleware('permission:reports.resolve');
    Route::post('/reports/{id}/block-and-resolve', [\App\Http\Controllers\Admin\ReportAdminController::class, 'blockAndResolve'])->name('reports.block-and-resolve')->middleware('permission:reports.block_user');

    // Staff Management (RBAC)
    Route::get('/staff', [StaffManagementController::class, 'index'])->name('staff.index')->middleware('permission:staff.view');
    Route::get('/staff/create', [StaffManagementController::class, 'create'])->name('staff.create')->middleware('permission:staff.create');
    Route::post('/staff', [StaffManagementController::class, 'store'])->name('staff.store')->middleware('permission:staff.create');
    Route::get('/staff/{staff}/edit', [StaffManagementController::class, 'edit'])->name('staff.edit')->middleware('permission:staff.edit');
    Route::put('/staff/{staff}', [StaffManagementController::class, 'update'])->name('staff.update')->middleware('permission:staff.edit');
    Route::delete('/staff/{staff}', [StaffManagementController::class, 'destroy'])->name('staff.destroy')->middleware('permission:staff.delete');
    Route::post('/staff/{staff}/status', [StaffManagementController::class, 'updateStatus'])->name('staff.status')->middleware('permission:staff.status_toggle');
    Route::get('/staff/{staff}/permissions', [StaffManagementController::class, 'editPermissions'])->name('staff.permissions')->middleware('permission:staff.permissions');
    Route::post('/staff/{staff}/permissions', [StaffManagementController::class, 'updatePermissions'])->name('staff.permissions.update')->middleware('permission:staff.permissions');

    // Role Management (RBAC)
    Route::get('/roles', [RoleManagementController::class, 'index'])->name('roles.index')->middleware('permission:roles.view');
    Route::get('/roles/create', [RoleManagementController::class, 'create'])->name('roles.create')->middleware('permission:roles.create');
    Route::post('/roles', [RoleManagementController::class, 'store'])->name('roles.store')->middleware('permission:roles.create');
    Route::get('/roles/{role}/edit', [RoleManagementController::class, 'edit'])->name('roles.edit')->middleware('permission:roles.edit');
    Route::put('/roles/{role}', [RoleManagementController::class, 'update'])->name('roles.update')->middleware('permission:roles.edit');
    Route::delete('/roles/{role}', [RoleManagementController::class, 'destroy'])->name('roles.destroy')->middleware('permission:roles.delete');
    Route::get('/roles/{role}/permissions', [RoleManagementController::class, 'editPermissions'])->name('roles.permissions')->middleware('permission:roles.permissions');
    Route::post('/roles/{role}/permissions', [RoleManagementController::class, 'updatePermissions'])->name('roles.permissions.update')->middleware('permission:roles.permissions');

    // Activity Audit Logs
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index')->middleware('permission:activity_logs.view');

    // Login History
    Route::get('/login-history', [LoginHistoryController::class, 'index'])->name('login-history.index')->middleware('permission:login_history.view');

    // App Branding & General Settings
    Route::get('/settings', [\App\Http\Controllers\Admin\AppSettingController::class, 'index'])->name('settings.index')->middleware('permission:settings.view');
    Route::post('/settings', [\App\Http\Controllers\Admin\AppSettingController::class, 'update'])->name('settings.update')->middleware('permission:settings.update');
    Route::post('/settings/version', [\App\Http\Controllers\Admin\AppSettingController::class, 'publishVersion'])->name('settings.version.publish')->middleware('permission:settings.update');
    Route::post('/settings/push-broadcast', [\App\Http\Controllers\Admin\AppSettingController::class, 'sendPushBroadcast'])->name('settings.push.broadcast')->middleware('permission:settings.push_broadcast');

    // Streaming & Video Calling Engine Management (Agora Cloud vs VPS WebRTC)
    Route::get('/settings/streaming', [\App\Http\Controllers\Admin\StreamingAdminController::class, 'index'])->name('settings.streaming.index')->middleware('permission:streaming.view');
    Route::post('/settings/streaming', [\App\Http\Controllers\Admin\StreamingAdminController::class, 'update'])->name('settings.streaming.update')->middleware('permission:streaming.update');

    // Party Rooms & Multi-Guest Live Stages Management
    Route::get('/party-rooms', [\App\Http\Controllers\Admin\PartyRoomAdminController::class, 'index'])->name('party-rooms.index');
    Route::get('/party-rooms/settings', [\App\Http\Controllers\Admin\PartyRoomAdminController::class, 'settings'])->name('party-rooms.settings');
    Route::post('/party-rooms/settings', [\App\Http\Controllers\Admin\PartyRoomAdminController::class, 'updateSettings'])->name('party-rooms.settings.update');
    Route::get('/party-rooms/{id}', [\App\Http\Controllers\Admin\PartyRoomAdminController::class, 'show'])->name('party-rooms.show');
    Route::post('/party-rooms/{id}/force-close', [\App\Http\Controllers\Admin\PartyRoomAdminController::class, 'forceClose'])->name('party-rooms.force-close');

    // Coin Transaction Ledger
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index')->middleware('permission:transactions.view');
});

// Shortcut aliases
Route::middleware(['auth'])->get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
});

Route::middleware(['auth'])->get('/profile', function () {
    return redirect()->route('admin.profile');
});

// Mobile App KYC Fallback Routes (Direct without /api prefix)
Route::post('/kyc/submit', [\App\Http\Controllers\Api\KycApiController::class, 'submit']);
Route::post('/kyc/verification/submit', [\App\Http\Controllers\Api\KycApiController::class, 'submit']);
Route::get('/kyc/status', [\App\Http\Controllers\Api\KycApiController::class, 'status']);
Route::get('/kyc/instructions', [\App\Http\Controllers\Api\KycApiController::class, 'instructions']);
Route::post('/kyc/ai-detect', [\App\Http\Controllers\Api\KycApiController::class, 'aiDetect']);
Route::post('/kyc/detect', [\App\Http\Controllers\Api\KycApiController::class, 'aiDetect']);
Route::post('/kyc/pre-check', [\App\Http\Controllers\Api\KycApiController::class, 'aiDetect']);
Route::post('/kyc/check', [\App\Http\Controllers\Api\KycApiController::class, 'aiDetect']);
Route::post('/kyc/video-verify', [\App\Http\Controllers\Api\KycApiController::class, 'videoScanVerify']);
Route::post('/kyc/video-scan', [\App\Http\Controllers\Api\KycApiController::class, 'videoScanVerify']);
Route::post('/kyc/video', [\App\Http\Controllers\Api\KycApiController::class, 'videoScanVerify']);
Route::post('/kyc/face/verify-step', [\App\Http\Controllers\Api\KycApiController::class, 'verifyFaceStep']);
Route::post('/kyc/face-liveness', [\App\Http\Controllers\Api\KycApiController::class, 'verifyFaceStep']);

// Mobile App Wallet & Deposit Fallback Routes (Direct and /api prefix)
Route::match(['get', 'post'], '/api/deposit/submit', [\App\Http\Controllers\Api\PaymentController::class, 'submitDeposit']);
Route::match(['get', 'post'], '/api/deposit/request', [\App\Http\Controllers\Api\PaymentController::class, 'submitDeposit']);
Route::match(['get', 'post'], '/api/deposit/create', [\App\Http\Controllers\Api\PaymentController::class, 'submitDeposit']);
Route::match(['get', 'post'], '/api/deposit/store', [\App\Http\Controllers\Api\PaymentController::class, 'submitDeposit']);
Route::match(['get', 'post'], '/api/deposit', [\App\Http\Controllers\Api\PaymentController::class, 'submitDeposit']);
Route::match(['get', 'post'], '/api/wallet/deposit', [\App\Http\Controllers\Api\PaymentController::class, 'submitDeposit']);

Route::get('/api/wallet', [\App\Http\Controllers\Api\PaymentController::class, 'getWalletBalance']);
Route::get('/api/wallet/balance', [\App\Http\Controllers\Api\PaymentController::class, 'getWalletBalance']);
Route::get('/api/wallet/summary', [\App\Http\Controllers\Api\PaymentController::class, 'getWalletBalance']);
Route::get('/api/payment-methods', [\App\Http\Controllers\Api\PaymentController::class, 'getPaymentMethods']);
Route::get('/api/coin-packages', [\App\Http\Controllers\Api\PaymentController::class, 'getCoinPackages']);
Route::get('/api/deposit/history', [\App\Http\Controllers\Api\PaymentController::class, 'getDepositHistory']);
Route::get('/api/wallet/history', [\App\Http\Controllers\Api\PaymentController::class, 'getDepositHistory']);

Route::get('/wallet', [\App\Http\Controllers\Api\PaymentController::class, 'getWalletBalance']);
Route::get('/wallet/balance', [\App\Http\Controllers\Api\PaymentController::class, 'getWalletBalance']);
Route::get('/wallet/summary', [\App\Http\Controllers\Api\PaymentController::class, 'getWalletBalance']);
Route::get('/payment-methods', [\App\Http\Controllers\Api\PaymentController::class, 'getPaymentMethods']);
Route::get('/coin-packages', [\App\Http\Controllers\Api\PaymentController::class, 'getCoinPackages']);
Route::post('/deposit/submit', [\App\Http\Controllers\Api\PaymentController::class, 'submitDeposit']);
Route::post('/deposit/request', [\App\Http\Controllers\Api\PaymentController::class, 'submitDeposit']);
Route::post('/deposit', [\App\Http\Controllers\Api\PaymentController::class, 'submitDeposit']);
Route::get('/deposit/history', [\App\Http\Controllers\Api\PaymentController::class, 'getDepositHistory']);
Route::get('/wallet/history', [\App\Http\Controllers\Api\PaymentController::class, 'getDepositHistory']);

// Mobile App Withdrawal Fallback Routes
Route::match(['get', 'post'], '/api/withdraw/info', [\App\Http\Controllers\Api\WithdrawalApiController::class, 'getInfo']);
Route::match(['get', 'post'], '/api/withdraw/config', [\App\Http\Controllers\Api\WithdrawalApiController::class, 'getInfo']);
Route::match(['get', 'post'], '/api/withdraw/calculate', [\App\Http\Controllers\Api\WithdrawalApiController::class, 'calculate']);
Route::match(['get', 'post'], '/api/withdraw/submit', [\App\Http\Controllers\Api\WithdrawalApiController::class, 'submit']);
Route::match(['get', 'post'], '/api/withdraw/request', [\App\Http\Controllers\Api\WithdrawalApiController::class, 'submit']);
Route::match(['get', 'post'], '/api/withdraw/create', [\App\Http\Controllers\Api\WithdrawalApiController::class, 'submit']);
Route::match(['get', 'post'], '/api/wallet/withdraw', [\App\Http\Controllers\Api\WithdrawalApiController::class, 'submit']);
Route::get('/api/withdraw/history', [\App\Http\Controllers\Api\WithdrawalApiController::class, 'history']);
Route::get('/api/wallet/withdraw/history', [\App\Http\Controllers\Api\WithdrawalApiController::class, 'history']);

Route::get('/withdraw/info', [\App\Http\Controllers\Api\WithdrawalApiController::class, 'getInfo']);
Route::get('/withdraw/config', [\App\Http\Controllers\Api\WithdrawalApiController::class, 'getInfo']);
Route::post('/withdraw/calculate', [\App\Http\Controllers\Api\WithdrawalApiController::class, 'calculate']);
Route::post('/withdraw/submit', [\App\Http\Controllers\Api\WithdrawalApiController::class, 'submit']);
Route::post('/withdraw/request', [\App\Http\Controllers\Api\WithdrawalApiController::class, 'submit']);
Route::get('/withdraw/history', [\App\Http\Controllers\Api\WithdrawalApiController::class, 'history']);

// Mobile App Call Fallback Routes
Route::match(['get', 'post'], '/api/call/config', [\App\Http\Controllers\Api\CallController::class, 'getConfig']);
Route::match(['get', 'post'], '/api/call/settings', [\App\Http\Controllers\Api\CallController::class, 'getConfig']);
Route::match(['get', 'post'], '/api/call/match', [\App\Http\Controllers\Api\CallController::class, 'randomMatch']);
Route::match(['get', 'post'], '/api/call/random-match', [\App\Http\Controllers\Api\CallController::class, 'randomMatch']);
Route::post('/api/call/initiate', [\App\Http\Controllers\Api\CallController::class, 'initiate']);
Route::post('/api/call/start', [\App\Http\Controllers\Api\CallController::class, 'start']);
Route::post('/api/call/connect', [\App\Http\Controllers\Api\CallController::class, 'start']);
Route::post('/api/call/deduct-interval', [\App\Http\Controllers\Api\CallController::class, 'deductInterval']);
Route::post('/api/call/end', [\App\Http\Controllers\Api\CallController::class, 'end']);
Route::get('/api/call/history', [\App\Http\Controllers\Api\CallController::class, 'history']);

// ==========================================
// 🏪 Reseller Web Portal Routes
// ==========================================
Route::prefix('reseller')->name('reseller.')->group(function () {
    Route::get('/login', [\App\Http\Controllers\Reseller\ResellerPortalController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Reseller\ResellerPortalController::class, 'login'])->name('login.submit');
    Route::post('/logout', [\App\Http\Controllers\Reseller\ResellerPortalController::class, 'logout'])->name('logout');

    // Authenticated Reseller Portal
    Route::get('/dashboard', [\App\Http\Controllers\Reseller\ResellerPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/validate-user', [\App\Http\Controllers\Reseller\ResellerPortalController::class, 'validateUser'])->name('validate-user');
    Route::post('/transfer', [\App\Http\Controllers\Reseller\ResellerPortalController::class, 'transferCoins'])->name('transfer');
    Route::post('/deposit/submit', [\App\Http\Controllers\Reseller\ResellerPortalController::class, 'submitDeposit'])->name('deposit.submit');
    Route::post('/withdrawal/submit', [\App\Http\Controllers\Reseller\ResellerPortalController::class, 'submitWithdrawal'])->name('withdrawal.submit');

    // Live Chat Interface (Customer Chats)
    Route::get('/chat/{userId?}', [\App\Http\Controllers\Reseller\ResellerPortalController::class, 'chat'])->name('chat');
    Route::get('/chat/user/{userId}', [\App\Http\Controllers\Reseller\ResellerPortalController::class, 'chat'])->name('chat.user');
    Route::post('/chat/send', [\App\Http\Controllers\Reseller\ResellerPortalController::class, 'sendMessage'])->name('chat.send');

    // Admin Live Support Chat (Reseller <-> Admin)
    Route::get('/admin-support', [\App\Http\Controllers\Reseller\ResellerPortalController::class, 'adminChat'])->name('admin-chat');
    Route::post('/admin-support/send', [\App\Http\Controllers\Reseller\ResellerPortalController::class, 'sendAdminChatMessage'])->name('admin-chat.send');

    // Reseller Ledger & Lists
    Route::get('/transfers', [\App\Http\Controllers\Reseller\ResellerPortalController::class, 'transfers'])->name('transfers');
    Route::get('/deposits', [\App\Http\Controllers\Reseller\ResellerPortalController::class, 'deposits'])->name('deposits');
    Route::get('/withdrawals', [\App\Http\Controllers\Reseller\ResellerPortalController::class, 'withdrawals'])->name('withdrawals');
});
