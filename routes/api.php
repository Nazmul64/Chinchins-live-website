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
Route::get('/deposit/methods', [PaymentController::class, 'getPaymentMethods']);

// Coin Packages RESTful CRUD APIs
Route::get('/coin-packages', [PaymentController::class, 'getCoinPackages']);
Route::get('/packages', [PaymentController::class, 'getCoinPackages']);
Route::get('/deposit/packages', [PaymentController::class, 'getCoinPackages']);
Route::get('/coin-packages/{id}', [PaymentController::class, 'showCoinPackage']);
Route::post('/coin-packages', [PaymentController::class, 'storeCoinPackage']);
Route::post('/coin-packages/store', [PaymentController::class, 'storeCoinPackage']);
Route::post('/coin-packages/create', [PaymentController::class, 'storeCoinPackage']);
Route::match(['put', 'post'], '/coin-packages/{id}', [PaymentController::class, 'updateCoinPackage']);
Route::post('/coin-packages/{id}/update', [PaymentController::class, 'updateCoinPackage']);
Route::delete('/coin-packages/{id}', [PaymentController::class, 'deleteCoinPackage']);
Route::post('/coin-packages/{id}/delete', [PaymentController::class, 'deleteCoinPackage']);

// Wallet balance, Total Deposited Coins & Summary
Route::get('/wallet', [PaymentController::class, 'getWalletBalance']);
Route::get('/wallet/balance', [PaymentController::class, 'getWalletBalance']);
Route::get('/wallet/summary', [PaymentController::class, 'getWalletBalance']);
Route::get('/coins/balance', [PaymentController::class, 'getWalletBalance']);
Route::get('/wallet/transactions', [PaymentController::class, 'getTransactions']);
Route::get('/coins/transactions', [PaymentController::class, 'getTransactions']);

// Submit Deposit request & view history (bKash, Nagad, Rocket, etc.)
Route::post('/deposit/submit', [PaymentController::class, 'submitDeposit']);
Route::post('/deposit/request', [PaymentController::class, 'submitDeposit']);
Route::post('/deposit/create', [PaymentController::class, 'submitDeposit']);
Route::post('/wallet/deposit', [PaymentController::class, 'submitDeposit']);
Route::get('/deposit/history', [PaymentController::class, 'getDepositHistory']);
Route::get('/wallet/history', [PaymentController::class, 'getDepositHistory']);
Route::get('/wallet/deposits', [PaymentController::class, 'getDepositHistory']);

// ==========================================
// 💸 Coin Withdrawal & Cash Out APIs
// ==========================================
Route::get('/withdraw/info', [\App\Http\Controllers\Api\WithdrawalApiController::class, 'getInfo']);
Route::get('/withdraw/config', [\App\Http\Controllers\Api\WithdrawalApiController::class, 'getInfo']);
Route::get('/withdraw/settings', [\App\Http\Controllers\Api\WithdrawalApiController::class, 'getInfo']);
Route::get('/wallet/withdraw', [\App\Http\Controllers\Api\WithdrawalApiController::class, 'getInfo']);
Route::get('/wallet/withdraw/info', [\App\Http\Controllers\Api\WithdrawalApiController::class, 'getInfo']);

Route::post('/withdraw/calculate', [\App\Http\Controllers\Api\WithdrawalApiController::class, 'calculate']);
Route::post('/withdraw/preview', [\App\Http\Controllers\Api\WithdrawalApiController::class, 'calculate']);

Route::post('/withdraw/submit', [\App\Http\Controllers\Api\WithdrawalApiController::class, 'submit']);
Route::post('/withdraw/request', [\App\Http\Controllers\Api\WithdrawalApiController::class, 'submit']);
Route::post('/withdraw/create', [\App\Http\Controllers\Api\WithdrawalApiController::class, 'submit']);
Route::post('/wallet/withdraw', [\App\Http\Controllers\Api\WithdrawalApiController::class, 'submit']);
Route::post('/wallet/withdraw/submit', [\App\Http\Controllers\Api\WithdrawalApiController::class, 'submit']);

Route::get('/withdraw/history', [\App\Http\Controllers\Api\WithdrawalApiController::class, 'history']);
Route::get('/wallet/withdraw/history', [\App\Http\Controllers\Api\WithdrawalApiController::class, 'history']);
Route::get('/wallet/withdrawals', [\App\Http\Controllers\Api\WithdrawalApiController::class, 'history']);
Route::get('/withdraw/{id}', [\App\Http\Controllers\Api\WithdrawalApiController::class, 'show']);

// ==========================================
// 📞 WebRTC Audio & Video Calling & Revenue APIs
// ==========================================
Route::prefix('call')->group(function () {
    Route::get('/config', [CallController::class, 'getConfig']);
    Route::get('/settings', [CallController::class, 'getConfig']);
    Route::match(['get', 'post'], '/match', [CallController::class, 'randomMatch']);
    Route::match(['get', 'post'], '/random-match', [CallController::class, 'randomMatch']);
    
    // Call Signaling & Ringing Lifecycle
    Route::post('/initiate', [CallController::class, 'initiate']);
    Route::match(['get', 'post'], '/incoming', [CallController::class, 'checkIncoming']);
    Route::match(['get', 'post'], '/check-incoming', [CallController::class, 'checkIncoming']);
    Route::match(['get', 'post'], '/active-incoming', [CallController::class, 'checkIncoming']);
    Route::match(['get', 'post'], '/status/{id?}', [CallController::class, 'getStatus']);
    Route::post('/ringing', [CallController::class, 'ringing']);
    Route::post('/ring-ping', [CallController::class, 'ringing']);
    
    // WebRTC Signaling & ICE Servers for Flutter App
    Route::get('/ice-servers', [CallController::class, 'getIceServers']);
    Route::post('/signal/send', [CallController::class, 'sendSignal']);
    Route::post('/send-signal', [CallController::class, 'sendSignal']);
    Route::post('/signal', [CallController::class, 'sendSignal']);
    Route::match(['get', 'post'], '/signal/receive', [CallController::class, 'getSignals']);
    Route::match(['get', 'post'], '/signals', [CallController::class, 'getSignals']);
    Route::match(['get', 'post'], '/get-signals', [CallController::class, 'getSignals']);
    Route::post('/signal/clear', [CallController::class, 'clearSignals']);
    Route::post('/clear-signals', [CallController::class, 'clearSignals']);

    // Call Actions (Receive / Accept / Start / Connect / Reject / Cancel)
    Route::post('/accept', [CallController::class, 'accept']);
    Route::post('/answer', [CallController::class, 'accept']);
    Route::post('/receive', [CallController::class, 'accept']);
    Route::post('/start', [CallController::class, 'accept']);
    Route::post('/connect', [CallController::class, 'accept']);
    Route::post('/reject', [CallController::class, 'reject']);
    Route::post('/decline', [CallController::class, 'reject']);
    Route::post('/cancel', [CallController::class, 'cancel']);

    // Real-Time In-Call Coin Billing (Pulse Heartbeat & 50/50 Revenue Split)
    Route::post('/deduct-interval', [CallController::class, 'deductInterval']);
    Route::post('/pulse', [CallController::class, 'deductInterval']);
    Route::post('/bill', [CallController::class, 'deductInterval']);

    // End Call & History
    Route::match(['get', 'post'], '/end', [CallController::class, 'end']);
    Route::match(['get', 'post'], '/finish', [CallController::class, 'end']);
    Route::match(['get', 'post'], '/hangup', [CallController::class, 'end']);
    Route::get('/history', [CallController::class, 'history']);
});

// ==========================================
// 🪪 KYC Identity Verification APIs
// ==========================================
Route::prefix('kyc')->group(function () {
    Route::post('/submit', [\App\Http\Controllers\Api\KycApiController::class, 'submit']);
    Route::post('/verification/submit', [\App\Http\Controllers\Api\KycApiController::class, 'submit']);
    Route::get('/status', [\App\Http\Controllers\Api\KycApiController::class, 'status']);
    Route::get('/verification/status', [\App\Http\Controllers\Api\KycApiController::class, 'status']);
    Route::get('/instructions', [\App\Http\Controllers\Api\KycApiController::class, 'instructions']);
    Route::get('/guidelines', [\App\Http\Controllers\Api\KycApiController::class, 'instructions']);
    
    // AI Pre-check & Quality Detection
    Route::post('/ai-detect', [\App\Http\Controllers\Api\KycApiController::class, 'aiDetect']);
    Route::post('/detect', [\App\Http\Controllers\Api\KycApiController::class, 'aiDetect']);
    Route::post('/pre-check', [\App\Http\Controllers\Api\KycApiController::class, 'aiDetect']);
    Route::post('/check', [\App\Http\Controllers\Api\KycApiController::class, 'aiDetect']);
    Route::post('/face-check', [\App\Http\Controllers\Api\KycApiController::class, 'aiDetect']);

    // Video & Face Liveness Verification
    Route::post('/video-verify', [\App\Http\Controllers\Api\KycApiController::class, 'videoScanVerify']);
    Route::post('/video-scan', [\App\Http\Controllers\Api\KycApiController::class, 'videoScanVerify']);
    Route::post('/video', [\App\Http\Controllers\Api\KycApiController::class, 'videoScanVerify']);
    Route::post('/face/verify-step', [\App\Http\Controllers\Api\KycApiController::class, 'verifyFaceStep']);
    Route::post('/face-liveness', [\App\Http\Controllers\Api\KycApiController::class, 'verifyFaceStep']);
    Route::post('/face/unlock', [\App\Http\Controllers\Api\KycApiController::class, 'unlockAccountWithFace']);
    Route::post('/unlock', [\App\Http\Controllers\Api\KycApiController::class, 'unlockAccountWithFace']);
});

// Direct Face Re-Unlock Authentication Route
Route::post('/auth/face-unlock', [\App\Http\Controllers\Api\KycApiController::class, 'unlockAccountWithFace']);

// KYC Aliases for Profile
Route::prefix('profile/kyc')->group(function () {
    Route::post('/submit', [\App\Http\Controllers\Api\KycApiController::class, 'submit']);
    Route::get('/', [\App\Http\Controllers\Api\KycApiController::class, 'status']);
    Route::get('/status', [\App\Http\Controllers\Api\KycApiController::class, 'status']);
    Route::post('/ai-detect', [\App\Http\Controllers\Api\KycApiController::class, 'aiDetect']);
    Route::post('/pre-check', [\App\Http\Controllers\Api\KycApiController::class, 'aiDetect']);
    Route::post('/video-verify', [\App\Http\Controllers\Api\KycApiController::class, 'videoScanVerify']);
    Route::post('/face/verify-step', [\App\Http\Controllers\Api\KycApiController::class, 'verifyFaceStep']);
    Route::post('/face/unlock', [\App\Http\Controllers\Api\KycApiController::class, 'unlockAccountWithFace']);
});

// Admin REST APIs for KYC
Route::prefix('admin/kyc-verifications')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\KycAdminController::class, 'index']);
    Route::post('/{id}/approve', [\App\Http\Controllers\Admin\KycAdminController::class, 'approve']);
    Route::post('/{id}/reject', [\App\Http\Controllers\Admin\KycAdminController::class, 'reject']);
});

// Authenticated Routes (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Auth status & logout
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});


