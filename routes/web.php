<?php

use App\Http\Controllers\Admin\CoinPackageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepositRequestController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\ProfileAdminController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::get('/', [AuthController::class, 'showLoginForm'])->name('home');
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Admin Dashboard Routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileAdminController::class, 'index'])->name('profile');

    // Users Management
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
    Route::post('/users/{id}/coins', [UserController::class, 'adjustCoins'])->name('users.adjust-coins');
    Route::post('/users/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::post('/users/{id}/toggle-lock', [UserController::class, 'toggleLock'])->name('users.toggle-lock');

    // Payment Methods Management
    Route::get('/payment-methods', [PaymentMethodController::class, 'index'])->name('payment-methods.index');
    Route::post('/payment-methods', [PaymentMethodController::class, 'store'])->name('payment-methods.store');
    Route::put('/payment-methods/{id}', [PaymentMethodController::class, 'update'])->name('payment-methods.update');
    Route::delete('/payment-methods/{id}', [PaymentMethodController::class, 'destroy'])->name('payment-methods.destroy');
    Route::post('/payment-methods/{id}/toggle-status', [PaymentMethodController::class, 'toggleStatus'])->name('payment-methods.toggle-status');

    // Coin Packages Management
    Route::get('/coin-packages', [CoinPackageController::class, 'index'])->name('coin-packages.index');
    Route::post('/coin-packages', [CoinPackageController::class, 'store'])->name('coin-packages.store');
    Route::put('/coin-packages/{id}', [CoinPackageController::class, 'update'])->name('coin-packages.update');
    Route::delete('/coin-packages/{id}', [CoinPackageController::class, 'destroy'])->name('coin-packages.destroy');
    Route::post('/coin-packages/{id}/toggle-status', [CoinPackageController::class, 'toggleStatus'])->name('coin-packages.toggle-status');

    // Manual Deposit Requests
    Route::get('/deposits', [DepositRequestController::class, 'index'])->name('deposits.index');
    Route::post('/deposits/{id}/approve', [DepositRequestController::class, 'approve'])->name('deposits.approve');
    Route::post('/deposits/{id}/reject', [DepositRequestController::class, 'reject'])->name('deposits.reject');

    // Coin Withdrawal Requests & Settings
    Route::get('/withdrawals', [\App\Http\Controllers\Admin\WithdrawalAdminController::class, 'index'])->name('withdrawals.index');
    Route::post('/withdrawals/{id}/approve', [\App\Http\Controllers\Admin\WithdrawalAdminController::class, 'approve'])->name('withdrawals.approve');
    Route::post('/withdrawals/{id}/reject', [\App\Http\Controllers\Admin\WithdrawalAdminController::class, 'reject'])->name('withdrawals.reject');
    Route::get('/withdrawals/settings', [\App\Http\Controllers\Admin\WithdrawalAdminController::class, 'settings'])->name('withdrawals.settings');
    Route::post('/withdrawals/settings', [\App\Http\Controllers\Admin\WithdrawalAdminController::class, 'updateSettings'])->name('withdrawals.settings.update');
    Route::post('/withdrawals/methods/{id}/toggle', [\App\Http\Controllers\Admin\WithdrawalAdminController::class, 'toggleMethodWithdraw'])->name('withdrawals.toggle-method');

    // Audio & Video Call Sessions & Revenue Settings
    Route::get('/calls', [\App\Http\Controllers\Admin\CallAdminController::class, 'index'])->name('calls.index');
    Route::get('/calls/settings', [\App\Http\Controllers\Admin\CallAdminController::class, 'settings'])->name('calls.settings');
    Route::post('/calls/settings', [\App\Http\Controllers\Admin\CallAdminController::class, 'updateSettings'])->name('calls.settings.update');

    // KYC Identity Verification Management
    Route::get('/kyc', [\App\Http\Controllers\Admin\KycAdminController::class, 'index'])->name('kyc.index');
    Route::get('/kyc/{id}', [\App\Http\Controllers\Admin\KycAdminController::class, 'show'])->name('kyc.show');
    Route::post('/kyc/{id}/approve', [\App\Http\Controllers\Admin\KycAdminController::class, 'approve'])->name('kyc.approve');
    Route::post('/kyc/{id}/reject', [\App\Http\Controllers\Admin\KycAdminController::class, 'reject'])->name('kyc.reject');
    Route::post('/kyc/{id}/revoke', [\App\Http\Controllers\Admin\KycAdminController::class, 'revoke'])->name('kyc.revoke');

    // Gifts & Rewards Management
    Route::get('/gifts', [\App\Http\Controllers\Admin\GiftController::class, 'index'])->name('gifts.index');
    Route::post('/gifts', [\App\Http\Controllers\Admin\GiftController::class, 'store'])->name('gifts.store');
    Route::put('/gifts/{id}', [\App\Http\Controllers\Admin\GiftController::class, 'update'])->name('gifts.update');
    Route::delete('/gifts/{id}', [\App\Http\Controllers\Admin\GiftController::class, 'destroy'])->name('gifts.destroy');
    Route::post('/gifts/{id}/toggle-status', [\App\Http\Controllers\Admin\GiftController::class, 'toggleStatus'])->name('gifts.toggle-status');
    Route::post('/gifts/give-to-user', [\App\Http\Controllers\Admin\GiftController::class, 'giveGiftToUser'])->name('gifts.give');
    Route::post('/gifts/levels', [\App\Http\Controllers\Admin\GiftController::class, 'updateLevels'])->name('gifts.levels.update');
    Route::get('/gifts/logs', [\App\Http\Controllers\Admin\GiftController::class, 'logs'])->name('gifts.logs');

    // App Branding & General Settings
    Route::get('/settings', [\App\Http\Controllers\Admin\AppSettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [\App\Http\Controllers\Admin\AppSettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/version', [\App\Http\Controllers\Admin\AppSettingController::class, 'publishVersion'])->name('settings.version.publish');
    Route::post('/settings/push-broadcast', [\App\Http\Controllers\Admin\AppSettingController::class, 'sendPushBroadcast'])->name('settings.push.broadcast');

    // Coin Transaction Ledger
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
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
Route::post('/api/call/pulse', [\App\Http\Controllers\Api\CallController::class, 'deductInterval']);
Route::post('/api/call/end', [\App\Http\Controllers\Api\CallController::class, 'end']);
Route::get('/api/call/history', [\App\Http\Controllers\Api\CallController::class, 'history']);



