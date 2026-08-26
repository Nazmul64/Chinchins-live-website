<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CallController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes for Chinchins Live
|--------------------------------------------------------------------------
*/

// Public Authentication & Registration Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public Home Feed & Users List (Live from Database)
Route::get('/home', [ProfileController::class, 'index']);
Route::get('/users', [ProfileController::class, 'index']);

// Public User Profile Route (view any profile by ID or 10-12 digit Account ID)
Route::get('/profile/{id}', [ProfileController::class, 'show']);

// Profile & Media Management Endpoints (Supports Bearer Token or User ID Fallback, Always Returns JSON)
Route::prefix('profile')->group(function () {
    Route::get('/me', [ProfileController::class, 'show']);
    Route::post('/update', [ProfileController::class, 'update']);
    Route::post('/status', [ProfileController::class, 'toggleStatus']);

    // Profile Avatar (Upload, Replace, Delete)
    Route::post('/upload-avatar', [ProfileController::class, 'uploadAvatar']);
    Route::match(['post', 'delete'], '/delete-avatar', [ProfileController::class, 'deleteAvatar']);
    Route::match(['post', 'delete'], '/avatar', [ProfileController::class, 'deleteAvatar']);

    // Cover Photo (Upload, Replace, Delete)
    Route::post('/upload-cover', [ProfileController::class, 'uploadCover']);
    Route::match(['post', 'delete'], '/delete-cover', [ProfileController::class, 'deleteCover']);
    Route::match(['post', 'delete'], '/cover', [ProfileController::class, 'deleteCover']);

    // Multi-image Gallery (Upload, Delete, Update/Reorder, Clear)
    Route::post('/upload-photos', [ProfileController::class, 'uploadPhotos']);
    Route::match(['post', 'delete'], '/delete-photo', [ProfileController::class, 'deletePhoto']);
    Route::match(['post', 'delete'], '/photo', [ProfileController::class, 'deletePhoto']);
    Route::match(['post', 'delete'], '/photos', [ProfileController::class, 'deletePhoto']);
    Route::match(['post', 'delete'], '/gallery/delete', [ProfileController::class, 'deletePhoto']);
    Route::post('/update-gallery', [ProfileController::class, 'updateGallery']);
    Route::match(['post', 'delete'], '/clear-gallery', [ProfileController::class, 'clearGallery']);
});

// Gallery Aliases for Mobile Clients
Route::prefix('gallery')->group(function () {
    Route::post('/upload', [ProfileController::class, 'uploadPhotos']);
    Route::post('/add', [ProfileController::class, 'uploadPhotos']);
    Route::match(['post', 'delete'], '/delete', [ProfileController::class, 'deletePhoto']);
    Route::match(['post', 'delete'], '/photo', [ProfileController::class, 'deletePhoto']);
    Route::post('/update', [ProfileController::class, 'updateGallery']);
    Route::match(['post', 'delete'], '/clear', [ProfileController::class, 'clearGallery']);
});

// Root-level Aliases for Mobile Clients
Route::post('/upload-avatar', [ProfileController::class, 'uploadAvatar']);
Route::match(['post', 'delete'], '/delete-avatar', [ProfileController::class, 'deleteAvatar']);
Route::post('/upload-cover', [ProfileController::class, 'uploadCover']);
Route::match(['post', 'delete'], '/delete-cover', [ProfileController::class, 'deleteCover']);
Route::post('/upload-photos', [ProfileController::class, 'uploadPhotos']);
Route::match(['post', 'delete'], '/delete-photo', [ProfileController::class, 'deletePhoto']);
Route::match(['post', 'delete'], '/clear-gallery', [ProfileController::class, 'clearGallery']);

// ==========================================
// 💳 Wallet, Coins & Manual Deposit APIs
// ==========================================
// Payment methods list (bKash, Nagad, etc.) & Coin Packages
Route::get('/payment-methods', [PaymentController::class, 'getPaymentMethods']);
Route::get('/coin-packages', [PaymentController::class, 'getCoinPackages']);
Route::get('/packages', [PaymentController::class, 'getCoinPackages']);

// Wallet balance & transactions
Route::get('/wallet/balance', [PaymentController::class, 'getWalletBalance']);
Route::get('/coins/balance', [PaymentController::class, 'getWalletBalance']);
Route::get('/wallet/transactions', [PaymentController::class, 'getTransactions']);
Route::get('/coins/transactions', [PaymentController::class, 'getTransactions']);

// Submit Deposit request & view history
Route::post('/deposit/request', [PaymentController::class, 'submitDeposit']);
Route::post('/wallet/deposit', [PaymentController::class, 'submitDeposit']);
Route::get('/deposit/history', [PaymentController::class, 'getDepositHistory']);
Route::get('/wallet/deposits', [PaymentController::class, 'getDepositHistory']);

// ==========================================
// 📞 Video Calling & Coin Deduction APIs
// ==========================================
Route::prefix('call')->group(function () {
    Route::post('/initiate', [CallController::class, 'initiate']);
    Route::post('/start', [CallController::class, 'start']);
    Route::post('/end', [CallController::class, 'end']);
    Route::post('/deduct-interval', [CallController::class, 'deductInterval']);
    Route::get('/history', [CallController::class, 'history']);
});

// Authenticated Routes (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Auth status & logout
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

