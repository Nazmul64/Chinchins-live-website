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

    // Users & Balance Management
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
    Route::post('/users/{id}/adjust-coins', [UserController::class, 'adjustCoins'])->name('users.adjust-coins');
    Route::post('/users/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

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

    // KYC Identity Verification Management
    Route::get('/kyc', [\App\Http\Controllers\Admin\KycAdminController::class, 'index'])->name('kyc.index');
    Route::get('/kyc/{id}', [\App\Http\Controllers\Admin\KycAdminController::class, 'show'])->name('kyc.show');
    Route::post('/kyc/{id}/approve', [\App\Http\Controllers\Admin\KycAdminController::class, 'approve'])->name('kyc.approve');
    Route::post('/kyc/{id}/reject', [\App\Http\Controllers\Admin\KycAdminController::class, 'reject'])->name('kyc.reject');
    Route::post('/kyc/{id}/revoke', [\App\Http\Controllers\Admin\KycAdminController::class, 'revoke'])->name('kyc.revoke');

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
Route::post('/kyc/face/verify-step', [\App\Http\Controllers\Api\KycApiController::class, 'verifyFaceStep']);
Route::post('/kyc/face-liveness', [\App\Http\Controllers\Api\KycApiController::class, 'verifyFaceStep']);


