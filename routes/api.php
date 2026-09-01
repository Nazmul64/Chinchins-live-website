<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CallController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\WebRTCCallController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes for Chinchins Live
|--------------------------------------------------------------------------
*/

// ==========================================
// 📡 Real-Time Broadcasting Channel Auth Route
// ==========================================
// Enables Bearer Token Authorization for Flutter & Web Pusher/Reverb Private Channels (/api/broadcasting/auth)
Broadcast::routes(['middleware' => ['auth:sanctum']]);

// Public App Configuration & Branding (Logo, Name, Free limits for Login/Register Screen)
Route::get('/app/config', [\App\Http\Controllers\Api\AppUpdateApiController::class, 'getRemoteConfig']);
Route::get('/settings', [\App\Http\Controllers\Api\AppUpdateApiController::class, 'getRemoteConfig']);
Route::get('/app/settings', [\App\Http\Controllers\Api\AppUpdateApiController::class, 'getRemoteConfig']);
Route::get('/app/remote-config', [\App\Http\Controllers\Api\AppUpdateApiController::class, 'getRemoteConfig']);

// 🚀 In-App OTA Update Engine & Version Check (Dynamic Features without manual APK rebuild)
Route::match(['get', 'post'], '/app/check-update', [\App\Http\Controllers\Api\AppUpdateApiController::class, 'checkUpdate']);
Route::match(['get', 'post'], '/app/version-check', [\App\Http\Controllers\Api\AppUpdateApiController::class, 'checkUpdate']);
Route::match(['get', 'post'], '/app/version', [\App\Http\Controllers\Api\AppUpdateApiController::class, 'checkUpdate']);
Route::match(['get', 'post'], '/version', [\App\Http\Controllers\Api\AppUpdateApiController::class, 'checkUpdate']);

// 📲 Universal Device Registration for Push Notifications & Background Call Wake-up
Route::post('/app/device/register', [\App\Http\Controllers\Api\AppUpdateApiController::class, 'registerDevice']);
Route::post('/device/register', [\App\Http\Controllers\Api\AppUpdateApiController::class, 'registerDevice']);
Route::post('/notifications/test-push', [\App\Http\Controllers\Api\AppUpdateApiController::class, 'testPush']);

// Public Authentication & Registration Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public Home Feed & Users List (Live from Database)
Route::get('/home', [ProfileController::class, 'index']);
Route::get('/users', [ProfileController::class, 'index']);

// Profile Visitors List
Route::get('/profile/visitors', [\App\Http\Controllers\Api\MessageApiController::class, 'getVisitors']);
Route::get('/visitors', [\App\Http\Controllers\Api\MessageApiController::class, 'getVisitors']);
Route::get('/user/visitors', [\App\Http\Controllers\Api\MessageApiController::class, 'getVisitors']);

// Public User Profile Route (view any profile by ID or 10-12 digit Account ID)
Route::get('/profile/{id}', [ProfileController::class, 'show']);

// Profile & Media Management Endpoints (Supports Bearer Token or User ID Fallback, Always Returns JSON)
Route::prefix('profile')->group(function () {
    Route::get('/visitors', [\App\Http\Controllers\Api\MessageApiController::class, 'getVisitors']);
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
// 📡 WebRTC Signaling & Call Management (Laravel Reverb / P2P)
// ==========================================
Route::prefix('calls')->group(function () {
    Route::get('/', [WebRTCCallController::class, 'index']);             // Call history
    Route::get('/ice-servers', [CallController::class, 'getIceServers']); // WebRTC ICE Servers
    Route::post('/', [WebRTCCallController::class, 'store']);            // Create / Initiate Call
    Route::get('/{call}', [WebRTCCallController::class, 'show']);        // Call details
    Route::post('/{call}/accept', [WebRTCCallController::class, 'accept']); // Accept Call
    Route::post('/{call}/reject', [WebRTCCallController::class, 'reject']); // Reject Call
    Route::post('/{call}/cancel', [WebRTCCallController::class, 'cancel']); // Cancel Call
    Route::post('/{call}/end', [WebRTCCallController::class, 'end']);       // End Call
    
    // WebRTC Signaling Relay (Offer, Answer, ICE Candidates)
    Route::post('/{call}/offer', [WebRTCCallController::class, 'offer']);
    Route::post('/{call}/answer', [WebRTCCallController::class, 'answer']);
    Route::post('/{call}/ice-candidate', [WebRTCCallController::class, 'iceCandidate']);
    Route::post('/{call}/candidate', [WebRTCCallController::class, 'iceCandidate']);
    Route::post('/{call}/signal', [WebRTCCallController::class, 'signal']);
});

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
    Route::match(['get', 'post'], '/wait-incoming', [CallController::class, 'waitIncoming']);
    Route::match(['get', 'post'], '/stream-incoming', [CallController::class, 'waitIncoming']);
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
// 🟢 User Online Presence, Heartbeat & Push Tokens
// ==========================================
Route::prefix('user')->group(function () {
    Route::post('/heartbeat', [\App\Http\Controllers\Api\PresenceController::class, 'heartbeat']);
    Route::post('/ping', [\App\Http\Controllers\Api\PresenceController::class, 'heartbeat']);
    Route::post('/status', [\App\Http\Controllers\Api\PresenceController::class, 'updateStatus']);
    Route::post('/presence/status', [\App\Http\Controllers\Api\PresenceController::class, 'updateStatus']);
    Route::post('/fcm-token', [\App\Http\Controllers\Api\PresenceController::class, 'updateFcmToken']);
    Route::post('/device-token', [\App\Http\Controllers\Api\PresenceController::class, 'updateFcmToken']);
    Route::get('/presence/{id?}', [\App\Http\Controllers\Api\PresenceController::class, 'getPresence']);
});

Route::prefix('presence')->group(function () {
    Route::post('/heartbeat', [\App\Http\Controllers\Api\PresenceController::class, 'heartbeat']);
    Route::post('/ping', [\App\Http\Controllers\Api\PresenceController::class, 'heartbeat']);
    Route::post('/status', [\App\Http\Controllers\Api\PresenceController::class, 'updateStatus']);
    Route::get('/{id?}', [\App\Http\Controllers\Api\PresenceController::class, 'getPresence']);
});

Route::get('/users/online', [\App\Http\Controllers\Api\PresenceController::class, 'getOnlineUsers']);
Route::get('/home/online', [\App\Http\Controllers\Api\PresenceController::class, 'getOnlineUsers']);
Route::post('/profile/heartbeat', [\App\Http\Controllers\Api\PresenceController::class, 'heartbeat']);
Route::post('/device/token', [\App\Http\Controllers\Api\PresenceController::class, 'updateFcmToken']);

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

// ==========================================
// 💘 Match Tab & Live Waiting Hosts Matching APIs
// ==========================================
Route::prefix('match')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\MatchApiController::class, 'getMatchTab']);
    Route::get('/status', [\App\Http\Controllers\Api\MatchApiController::class, 'getMatchTab']);
    Route::get('/hosts', [\App\Http\Controllers\Api\MatchApiController::class, 'getMatchTab']);
    Route::post('/start', [\App\Http\Controllers\Api\MatchApiController::class, 'startMatch']);
    Route::post('/random', [\App\Http\Controllers\Api\MatchApiController::class, 'startMatch']);
    Route::post('/', [\App\Http\Controllers\Api\MatchApiController::class, 'startMatch']);
});

// ==========================================
// 💬 In-App Messages, Chat, Voice, Emojis & Media APIs
// ==========================================
Route::prefix('messages')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\MessageApiController::class, 'getConversations']);
    Route::get('/conversations', [\App\Http\Controllers\Api\MessageApiController::class, 'getConversations']);
    Route::get('/inbox', [\App\Http\Controllers\Api\MessageApiController::class, 'getConversations']);
    Route::get('/{userId}', [\App\Http\Controllers\Api\MessageApiController::class, 'getMessages'])->whereNumber('userId');
    Route::post('/send', [\App\Http\Controllers\Api\MessageApiController::class, 'sendMessage']);
    Route::post('/upload', [\App\Http\Controllers\Api\MessageApiController::class, 'uploadMedia']);
    Route::post('/read', [\App\Http\Controllers\Api\MessageApiController::class, 'markAsRead']);
});

Route::prefix('chat')->group(function () {
    Route::get('/conversations', [\App\Http\Controllers\Api\MessageApiController::class, 'getConversations']);
    Route::get('/{userId}', [\App\Http\Controllers\Api\MessageApiController::class, 'getMessages'])->whereNumber('userId');
    Route::post('/send', [\App\Http\Controllers\Api\MessageApiController::class, 'sendMessage']);
    Route::post('/upload', [\App\Http\Controllers\Api\MessageApiController::class, 'uploadMedia']);
    Route::post('/read', [\App\Http\Controllers\Api\MessageApiController::class, 'markAsRead']);
});

Route::post('/upload/chat-media', [\App\Http\Controllers\Api\MessageApiController::class, 'uploadMedia']);

// ==========================================
// 👁️ Profile View Tracking, Auto-Callback & Visitors List
// ==========================================
Route::post('/profile/{id}/view', [\App\Http\Controllers\Api\MessageApiController::class, 'recordProfileView']);
Route::post('/profile/view', [\App\Http\Controllers\Api\MessageApiController::class, 'recordProfileView']);
Route::post('/user/view-profile', [\App\Http\Controllers\Api\MessageApiController::class, 'recordProfileView']);
Route::get('/profile/visitors', [\App\Http\Controllers\Api\MessageApiController::class, 'getVisitors']);
Route::get('/visitors', [\App\Http\Controllers\Api\MessageApiController::class, 'getVisitors']);
Route::get('/user/visitors', [\App\Http\Controllers\Api\MessageApiController::class, 'getVisitors']);

// ==========================================
// 🔔 In-App Notifications & Alerts APIs
// ==========================================
Route::prefix('notifications')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\MessageApiController::class, 'getNotifications']);
    Route::get('/unread-count', [\App\Http\Controllers\Api\MessageApiController::class, 'getNotificationUnreadCount']);
    Route::get('/visitors', [\App\Http\Controllers\Api\MessageApiController::class, 'getVisitors']);
    Route::post('/read', [\App\Http\Controllers\Api\MessageApiController::class, 'markNotificationRead']);
    Route::post('/{id}/read', [\App\Http\Controllers\Api\MessageApiController::class, 'markNotificationRead']);
    Route::match(['post', 'delete'], '/clear', [\App\Http\Controllers\Api\MessageApiController::class, 'clearNotifications']);
    Route::delete('/', [\App\Http\Controllers\Api\MessageApiController::class, 'clearNotifications']);
});

Route::get('/user/notifications', [\App\Http\Controllers\Api\MessageApiController::class, 'getNotifications']);

// ==========================================
// 🎁 Gifts, Rewards & Profile Received Gifts APIs
// ==========================================
Route::prefix('gifts')->group(function () {
    // 1. Gift Catalog (Store of gifts)
    Route::get('/', [\App\Http\Controllers\Api\GiftApiController::class, 'getCatalog']);
    Route::get('/catalog', [\App\Http\Controllers\Api\GiftApiController::class, 'getCatalog']);
    Route::get('/list', [\App\Http\Controllers\Api\GiftApiController::class, 'getCatalog']);
    Route::get('/categories', [\App\Http\Controllers\Api\GiftApiController::class, 'getCatalog']);

    // 2. User's Received Gifts (For Profile Charm Level & Gifts Received Screen)
    Route::get('/received/{id?}', [\App\Http\Controllers\Api\GiftApiController::class, 'getUserReceivedGifts']);
    Route::get('/user/{id?}', [\App\Http\Controllers\Api\GiftApiController::class, 'getUserReceivedGifts']);

    // 3. Send Gift to Host/User
    Route::post('/send', [\App\Http\Controllers\Api\GiftApiController::class, 'sendGift']);
    Route::post('/give', [\App\Http\Controllers\Api\GiftApiController::class, 'sendGift']);

    // 4. Top Fans Leaderboard & Likes
    Route::get('/top-fans/{id?}', [\App\Http\Controllers\Api\GiftApiController::class, 'getTopFans']);
    Route::post('/like', [\App\Http\Controllers\Api\GiftApiController::class, 'sendLike']);
});

// Profile / User level aliases for Gifts, Top Fans & Likes
Route::get('/profile/{id}/gifts', [\App\Http\Controllers\Api\GiftApiController::class, 'getUserReceivedGifts']);
Route::get('/profile/{id}/gifts-received', [\App\Http\Controllers\Api\GiftApiController::class, 'getUserReceivedGifts']);
Route::get('/profile/{id}/top-fans', [\App\Http\Controllers\Api\GiftApiController::class, 'getTopFans']);
Route::post('/profile/{id}/like', [\App\Http\Controllers\Api\GiftApiController::class, 'sendLike']);
Route::post('/user/{id}/like', [\App\Http\Controllers\Api\GiftApiController::class, 'sendLike']);
Route::get('/users/{id}/gifts', [\App\Http\Controllers\Api\GiftApiController::class, 'getUserReceivedGifts']);
Route::get('/users/{id}/gifts-received', [\App\Http\Controllers\Api\GiftApiController::class, 'getUserReceivedGifts']);
Route::get('/users/{id}/top-fans', [\App\Http\Controllers\Api\GiftApiController::class, 'getTopFans']);
Route::post('/gift/send', [\App\Http\Controllers\Api\GiftApiController::class, 'sendGift']);

// Authenticated Routes (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Auth status & logout
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});




