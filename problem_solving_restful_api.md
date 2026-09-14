# 📄 Comprehensive Problem Solving & RESTful API Architecture Specification
**Target Roles:** Full-Stack Engineers (Laravel Backend & Flutter Frontend)  
**System Scope:** Live Streaming (Audio/Video), 1-on-1 Video Calling, Unified Core Messaging, Reverb Real-Time Pipeline, FinTech-Grade Coin & Gift Engine, Admin-Controlled Remote Flags (FLAG_SECURE, Debug HUD, Offline Visibility, PIP Restore)  
**Architecture:** Laravel 11.x RESTful Backend & WebSocket Server (Reverb/Pusher) + Flutter Mobile Client (Android & iOS)  
**Version:** 6.0.0 Enterprise Edition  
**Updated:** September 14, 2026  
**Document Name:** `problem_solving_restful_api.md`  

---

## 📑 Table of Contents
1. [System Overview & Architecture Topology](#1-system-overview--architecture-topology)
2. [Problem Solving & Core Logic Resolutions](#2-problem-solving--core-logic-resolutions)
   - [Problem 1: Call State Ringing vs Answer (No Auto-Pickup or Auto-Drop on PIP Tap)](#problem-1-call-state-ringing-vs-answer-no-auto-pickup-or-auto-drop-on-pip-tap)
   - [Problem 2: Bidirectional In-Call & Live Stream Messaging (100% Free Chat)](#problem-2-bidirectional-in-call--live-stream-messaging-100-free-chat)
   - [Problem 3: Offline User Calling Gate (`USER_OFFLINE` Error Handling)](#problem-3-offline-user-calling-gate-user_offline-error-handling)
   - [Problem 4: Complete Database Fresh Seed (`migrate:fresh --seed`) for All Admin Modules](#problem-4-complete-database-fresh-seed-migratefresh---seed-for-all-admin-modules)
   - [Problem 5: Home Screen Floating VIP Widget Background Transparency & Dynamic Upload](#problem-5-home-screen-floating-vip-widget-background-transparency--dynamic-upload)
   - [Problem 6: Dynamic Screenshot Protection (FLAG_SECURE) & Debug HUD Toggle](#problem-6-dynamic-screenshot-protection-flag_secure--debug-hud-toggle)
   - [Problem 7: Strict 30 Strong-Motion SVG Gifts & 6 Diamond Burst Coin Packages](#problem-7-strict-30-strong-motion-svg-gifts--6-diamond-burst-coin-packages)
3. [Database Schema & Migrations Reference](#3-database-schema--migrations-reference)
4. [Complete RESTful API Reference & Payloads](#4-complete-restful-api-reference--payloads)
   - [A. App Configuration & Remote Feature Flags](#a-app-configuration--remote-feature-flags)
   - [B. User Discovery & Visibility Engine](#b-user-discovery--visibility-engine)
   - [C. 1-on-1 Video & Audio Calling Endpoints](#c-1-on-1-video--audio-calling-endpoints)
   - [D. Unified Messaging & In-Call Free Chat](#d-unified-messaging--in-call-free-chat)
   - [E. Multi-Guest Live Streaming & Broadcasting](#e-multi-guest-live-streaming--broadcasting)
   - [F. Virtual Gifts Catalog (30 Items) & Transfer](#f-virtual-gifts-catalog-30-items--transfer)
   - [G. Coin Packages Store (6 Diamond Packages)](#g-coin-packages-store-6-diamond-packages)
   - [H. Premium VIP Privilege Cards & Floating Banner](#h-premium-vip-privilege-cards--floating-banner)
5. [Real-Time WebSocket Pipeline & Reverb Events](#5-real-time-websocket-pipeline--reverb-events)
6. [Flutter Mobile Architecture & State Handlers](#6-flutter-mobile-architecture--state-handlers)
7. [Production Deployment & VPS Commands](#7-production-deployment--vps-commands)

---

## 1. System Overview & Architecture Topology

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                                 FLUTTER CLIENT                                         │
│                                                                                        │
│  ┌───────────────────────┐  ┌────────────────────────┐  ┌───────────────────────────┐  │
│  │ Unified Messaging UI  │  │ Video Call + Chat Sheet │  │ Live Room (Audio / Video) │  │
│  └───────────┬───────────┘  └───────────┬────────────┘  └─────────────┬─────────────┘  │
└──────────────┼──────────────────────────┼─────────────────────────────┼────────────────┘
               │                          │                             │
    HTTP / REST (Bearer Token)            │                  WebSocket (Presence/Private)
               │                          │                             │
               ▼                          ▼                             ▼
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                              LARAVEL BACKEND ECOSYSTEM                                 │
│                                                                                        │
│  ┌──────────────────────────────────────────────────────────────────────────────────┐  │
│  │ REST Routing & Middleware (Sanctum Auth, Idempotency Guard, Input Validation)   │  │
│  └──────────────────────────────────────┬───────────────────────────────────────────┘  │
│                                         │                                              │
│                               DB Transaction Block                                     │
│                     ┌───────────────────┴───────────────────┐                          │
│                     ▼                                       ▼                          │
│      ┌─────────────────────────────┐         ┌─────────────────────────────┐           │
│      │   Pessimistic Row Lock      │         │ Unified Conversation &      │           │
│      │   `lockForUpdate()` Balance │         │ Messaging Service Engine    │           │
│      └──────────────┬──────────────┘         └──────────────┬──────────────┘           │
│                     │                                       │                          │
│                     ▼                                       ▼                          │
│           [ MySQL 8.0 InnoDB ]                     Dispatch Job / Event                │
│                                                             │                          │
│                                                             ▼                          │
│                                                  [ Laravel Reverb Server ]             │
│                                                             │ (Broadcasting)           │
│ └────────────────────────────────────────────────────────────┼──────────────────────────┘
                                                              │
                                                              ▼
                                            Subscribed Channels (Clients Update UI)
```

---

## 2. Problem Solving & Core Logic Resolutions

### Problem 1: Call State Ringing vs Answer (No Auto-Pickup or Auto-Drop on PIP Tap)
- **Root Problem:**
  - When minimizing a call to Picture-in-Picture (PIP) and tapping back to enlarge, the UI was previously triggering `onClose()` or popping the view, causing auto-hangup or false `call_accept` events.
  - A call MUST remain in `ringing` state until the Receiver explicitly taps the "Answer / Accept" button.
- **Resolution:**
  - Caller initiates call -> `status = 'ringing'`.
  - Receiver receives push / WebSocket event `incoming_call` -> Displays Fullscreen Incoming Call Dialog with "Accept" and "Decline" buttons.
  - When Caller or Receiver minimizes to floating PIP, state provider stores `isPipMode = true` without altering call state.
  - Tapping the PIP overlay simply toggles `isPipMode = false` and opens `ActiveVideoCallScreen` without sending any WebSocket terminate event.

```dart
// Flutter PIP Restoration (lib/features/call/widgets/pip_call_overlay.dart)
GestureDetector(
  onTap: () {
    // 1. Maintain active call state without auto-accepting or hanging up
    ref.read(callStateProvider.notifier).setPipMode(false);
    
    // 2. Push fullscreen call interface without re-initializing WebRTC/Agora
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => const ActiveVideoCallScreen(),
        settings: const RouteSettings(name: '/active-call'),
      ),
    );
  },
  child: const PipFloatingVideoSurface(),
)
```

---

### Problem 2: Bidirectional In-Call & Live Stream Messaging (100% Free Chat)
- **Root Problem:** Chatting during live broadcast or active video calls was consuming user coins or deducting from the 5 free private DM messages limit.
- **Resolution:**
  - In `MessageApiController.php` & `LiveStreamApiController.php`, in-room chat messages (with `is_in_call: true`, `is_live: true`, or `live_stream_id`) are **100% Free ($0 coins)** with **zero quota deduction**.
  - WebSocket broadcasts messages instantly to both parties via `private-conversation.{id}` or `presence-live-room.{id}`.

```php
// Backend Free Messaging Gate (MessageApiController.php)
$isInCallOrLive = $request->boolean('is_in_call') 
               || $request->boolean('in_call') 
               || $request->boolean('is_live') 
               || $request->input('context') === 'in_call' 
               || $request->input('context') === 'live' 
               || $request->filled('call_id') 
               || $request->filled('live_stream_id')
               || $request->filled('live_id');

$freeLimit = (int) AppSetting::get('free_messages_limit', $sender->free_messages_limit ?? 5);
$freeUsed = $sender->free_messages_used ?? 0;
$isFree = $isInCallOrLive || ($freeUsed < $freeLimit);
$coinCost = $isInCallOrLive ? 0 : (int) AppSetting::get('message_coin_cost', 5);
```

---

### Problem 3: Offline User Calling Gate (`USER_OFFLINE` Error Handling)
- **Root Problem:** Attempting to call an offline host must be prevented upfront.
- **Resolution:**
  - In `CallController@initiateDirectCall`, the backend strictly validates `$receiver->is_online`.
  - If `$receiver->is_online == false`, the API rejects with HTTP 400 and `code: 'USER_OFFLINE'`.
  - In Flutter, profile buttons disable the call button when `is_online == false`, or display an immediate toast: `"User is currently offline."`.

```json
// Error Response when Receiver is Offline (400 Bad Request):
{
  "status": false,
  "can_call": false,
  "code": "USER_OFFLINE",
  "is_online": false,
  "message": "Maya is currently offline.",
  "receiver": {
    "id": 105,
    "account_id": "84920183",
    "display_name": "Maya",
    "avatar": "https://chinchins.live/uploads/avatars/maya.jpg",
    "is_online": false
  }
}
```

---

### Problem 4: Complete Database Fresh Seed (`migrate:fresh --seed`) for All Admin Modules
- **Root Problem:** Running `migrate:fresh --seed` was previously missing some admin modules.
- **Resolution:**
  - `DatabaseSeeder.php` has been configured to cleanly seed all 10 core modules:
    1. `RoleAndPermissionSeeder` (All admin permissions)
    2. `AdminUserSeeder` (Admin credentials)
    3. `ResellerSeeder` (Reseller accounts & ledger)
    4. `PaymentMethodSeeder` (bKash, Nagad, Rocket gateways)
    5. `CoinPackageSeeder` (6 Diamond Burst Store Packages)
    6. `StrongMotionGiftsSeeder` (30 Strong-Motion SVG Gifts)
    7. `VipPrivilegeCard::seedDefaultCards()` (8 Premium VIP Cards)
    8. `SpendLessCard::seedDefaultCards()` (4 Spend Less Cards)
    9. `BagItem::seedDefaultItems()` (11 Backpack Items)
    10. `ProfileBase::seedDefaultBases()` (Profile Bases)

---

### Problem 5: Home Screen Floating VIP Widget Background Transparency & Dynamic Upload
- **Root Problem:** In the mobile explore feed, the floating VIP widget was rendered with a dark navy background box container.
- **Resolution:**
  - Removed container background color in Flutter, rendering the SVG / PNG directly as a floating overlay with transparent background.
  - Admin can upload custom image anytime from `/admin/vip-cards` ("Upload Custom Floating Widget Image"), and it syncs immediately to `/api/app/remote-config`.

---

### Problem 6: Dynamic Screenshot Protection (FLAG_SECURE) & Debug HUD Toggle
- **Root Problem:** When toggled OFF in `/admin/settings/debug`, Android phones continued blocking screenshots because `FLAG_SECURE` was set statically on app start.
- **Resolution:**
  - Flutter dynamically fetches `/api/app/remote-config` on launch and calls `window.clearFlags(WindowManager.LayoutParams.FLAG_SECURE)` when disabled by admin.

```kotlin
// Android MainActivity.kt
MethodChannel(flutterEngine.dartExecutor.binaryMessenger, "chinchins/security").setMethodCallHandler { call, result ->
    if (call.method == "setScreenshotProtection") {
        val enabled = call.argument<Boolean>("enabled") ?: false
        if (enabled) {
            window.setFlags(WindowManager.LayoutParams.FLAG_SECURE, WindowManager.LayoutParams.FLAG_SECURE)
        } else {
            window.clearFlags(WindowManager.LayoutParams.FLAG_SECURE)
        }
        result.success(true)
    }
}
```

---

### Problem 7: Strict 30 Strong-Motion SVG Gifts & 6 Diamond Burst Coin Packages
- **Gifts:** Exactly 30 Strong-Motion Animated SVGs (`01_rose.svg` to `30_royal_palace.svg`) mapped to `sort_order: 1..30`.
- **Coin Packages:** 6 packages with high-motion vector and PNG diamond icons (`burst`, `diamond_orb`, `diamond_crown`, `crystal_crown`, `magic_bag`, `royal_chest`).

---

## 3. Database Schema & Migrations Reference

```sql
-- 30 Strong-Motion Animated Gifts Table
CREATE TABLE gifts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  slug VARCHAR(255) UNIQUE NOT NULL,
  coins INT UNSIGNED NOT NULL,
  coin_price INT UNSIGNED NOT NULL,
  category ENUM('hot', 'romantic', 'luxury', 'svip', 'wealth', 'cute', 'party', 'lucky') NOT NULL DEFAULT 'hot',
  badge VARCHAR(50) NULL,
  image VARCHAR(255) NOT NULL,
  icon_url VARCHAR(255) NOT NULL,
  animation_url VARCHAR(255) NOT NULL,
  animation_asset_url VARCHAR(255) NOT NULL,
  animation_type VARCHAR(50) NOT NULL DEFAULT 'svg',
  format VARCHAR(50) NOT NULL DEFAULT 'svg',
  display_type VARCHAR(50) NOT NULL DEFAULT 'fullscreen',
  sort_order INT NOT NULL DEFAULT 0,
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  is_broadcast BOOLEAN NOT NULL DEFAULT FALSE,
  description VARCHAR(500) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);

-- Coin Packages Recharge Store Table
CREATE TABLE coin_packages (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  coins INT UNSIGNED NOT NULL,
  bonus_coins INT UNSIGNED NOT NULL DEFAULT 0,
  price DECIMAL(10,2) NOT NULL,
  currency VARCHAR(10) NOT NULL DEFAULT 'BDT',
  badge VARCHAR(50) NULL,
  badge_color VARCHAR(50) NOT NULL DEFAULT 'pink',
  icon_url VARCHAR(255) NOT NULL,
  animation_url VARCHAR(255) NULL,
  format VARCHAR(50) NOT NULL DEFAULT 'svg',
  is_popular BOOLEAN NOT NULL DEFAULT FALSE,
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);
```

---

## 4. Complete RESTful API Reference & Payloads

### A. App Configuration & Remote Feature Flags
- **Endpoint:** `GET /api/app/remote-config`
- **Authentication:** Public / Optional Bearer Token

#### Success Response (200 OK):
```json
{
  "status": true,
  "data": {
    "app_name": "Chinchins Live",
    "app_tagline": "Meet, Chat & Video Call Live",
    "app_logo_url": "https://chinchins.live/assets/images/branding/logo.png",
    "free_messages_limit": 5,
    "message_coin_cost": 5,
    "screenshot_protection_enabled": false,
    "screen_recording_protection_enabled": false,
    "camera_filters_enabled": true,
    "call_minimize_enabled": true,
    "debug_mode_enabled": false,
    "floating_vip_banner": {
      "is_enabled": true,
      "title": "Extra Gems",
      "tag": "Monthly Card",
      "image_url": "https://chinchins.live/assets/images/vip/vip_privilege_full_motion.svg",
      "action_type": "OPEN_PREMIUM_VIP",
      "target_screen": "/premium-vip"
    }
  }
}
```

---

### B. User Discovery & Visibility Engine
- **Endpoint:** `GET /api/v1/users/discovery` (or `GET /api/v1/users/active`)
- **Authentication:** Public / Bearer Token

#### Success Response (200 OK):
```json
{
  "status": "success",
  "show_offline_users": false,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 105,
        "name": "Maya",
        "account_id": "84920183",
        "avatar": "https://chinchins.live/uploads/avatars/maya.jpg",
        "is_online": true,
        "online_status": "online",
        "current_status": "available",
        "video_call_rate": 22
      }
    ]
  }
}
```

---

### C. Direct 1-on-1 Video Call Initiation
- **Endpoint:** `POST /api/call/initiate`
- **Authentication:** `Bearer {token}`

#### Request Body:
```json
{
  "receiver_id": 105,
  "call_type": "video"
}
```

#### Success Response (200 OK):
```json
{
  "status": true,
  "message": "Call initiated successfully. Waiting for receiver to accept.",
  "data": {
    "call_id": 9821,
    "channel_name": "call_video_12_105_1726320000_a8bc",
    "status": "ringing",
    "agora_token": "006e8a...==",
    "agora_app_id": "934...b1",
    "uid": 12,
    "is_free_trial": false,
    "rate_per_minute": 22,
    "receiver": {
      "id": 105,
      "account_id": "84920183",
      "display_name": "Maya",
      "avatar": "https://chinchins.live/uploads/avatars/maya.jpg",
      "is_online": true
    }
  }
}
```

---

### D. Free Live Stream Chat Message
- **Endpoint:** `POST /api/live/message` (or `POST /api/live/messages/send`)
- **Authentication:** `Bearer {token}`

#### Request Body:
```json
{
  "live_stream_id": 12,
  "message": "Hello everyone! Sending love from Dhaka ❤️",
  "type": "text"
}
```

#### Success Response (200 OK):
```json
{
  "status": true,
  "success": true,
  "message": "Live message sent.",
  "data": {
    "id": 4820,
    "live_stream_id": 12,
    "user_id": 105,
    "sender_name": "Shakib",
    "sender_avatar": "https://chinchins.live/uploads/avatars/u105.jpg",
    "level": "Lv3",
    "message": "Hello everyone! Sending love from Dhaka ❤️",
    "type": "text",
    "created_at": "2026-09-14T18:00:00+06:00"
  }
}
```

---

### E. 30 Strong-Motion Gifts Catalog
- **Endpoint:** `GET /api/v1/gifts`
- **Authentication:** `Bearer {token}`

#### Success Response (200 OK):
```json
{
  "status": true,
  "message": "Active gifts retrieved successfully.",
  "data": [
    {
      "id": 1,
      "name": "Red Rose",
      "slug": "rose",
      "coins": 10,
      "badge": "ROSE",
      "image_url": "https://chinchins.live/uploads/gifts/01_rose.svg",
      "animation_full_url": "https://chinchins.live/uploads/gifts/01_rose.svg",
      "format": "svg",
      "display_type": "fullscreen",
      "sort_order": 1
    },
    {
      "id": 2,
      "name": "Diamond Heart",
      "slug": "diamond_heart",
      "coins": 520,
      "badge": "520 LOVE",
      "image_url": "https://chinchins.live/uploads/gifts/02_diamond_heart.svg",
      "animation_full_url": "https://chinchins.live/uploads/gifts/02_diamond_heart.svg",
      "format": "svg",
      "display_type": "fullscreen",
      "sort_order": 2
    },
    {
      "id": 30,
      "name": "Imperial Royal Palace",
      "slug": "royal_palace",
      "coins": 50000,
      "badge": "50K PALACE",
      "image_url": "https://chinchins.live/uploads/gifts/30_royal_palace.svg",
      "animation_full_url": "https://chinchins.live/uploads/gifts/30_royal_palace.svg",
      "format": "svg",
      "display_type": "fullscreen",
      "sort_order": 30
    }
  ]
}
```

---

### F. Coin Recharge Packages Store
- **Endpoint:** `GET /api/v1/coin-packages`
- **Authentication:** `Bearer {token}`

#### Success Response (200 OK):
```json
{
  "status": true,
  "data": [
    {
      "id": 1,
      "title": "Starter Pack",
      "coins": 7560,
      "price": "150.00",
      "currency": "BDT",
      "badge": "50% off",
      "image_url": "https://chinchins.live/uploads/coin_packages/burst.png",
      "animation_full_url": "https://chinchins.live/uploads/coin_packages/burst_animated.svg",
      "button_text": "Recharge 7560 Gems (৳150)"
    },
    {
      "id": 6,
      "title": "VIP King Pack",
      "coins": 167400,
      "price": "6100.00",
      "currency": "BDT",
      "badge": "80% off",
      "image_url": "https://chinchins.live/uploads/coin_packages/royal_chest.png",
      "animation_full_url": "https://chinchins.live/uploads/coin_packages/royal_chest_animated.svg",
      "button_text": "Recharge 167400 Gems (৳6,100)"
    }
  ]
}
```

---

## 5. Real-Time WebSocket Pipeline & Reverb Events

| Event Class | Channel Name | Client Event Name | Description |
|---|---|---|---|
| `CallSignalEvent` | `private-user.{id}` | `incoming_call` | Incoming video/audio call dialog trigger |
| `CallAnsweredEvent` | `private-call.{call_id}` | `call_answered` | Transition caller from ringing to active |
| `CallEndedEvent` | `private-call.{call_id}` | `call_ended` | Disconnect WebRTC/Agora stream cleanly |
| `LiveChatMessageEvent` | `presence-live-room.{id}` | `chat.message` | 100% Free real-time live chat message |
| `LiveGiftSentEvent` | `presence-live-room.{id}` | `gift.received` | Trigger SVG strong-motion gift canvas |
| `UserOnlineStatusEvent` | `presence-global` | `user.status` | Update online/offline badge real-time |

---

## 6. Zero-Latency Preloading & Instant App Performance Architecture

To achieve **instantaneous / sub-second** response times without showing loading spinners or "Connecting..." freezes:

### A. Backend In-Memory Acceleration & HTTP Cache Headers
1. **Gift Catalog (`/api/v1/gifts`):**
   - Removed runtime database seeding calls.
   - Catalog data and category counts cached in Laravel Memory/Redis (`Cache::remember('api_gifts_catalog_data_{cat}', 3600)`).
   - Responses served with `Cache-Control: public, max-age=60, stale-while-revalidate=300`. Response time reduced from ~180ms to `< 5ms`.
2. **Coin Packages & Payment Methods (`/api/coin-packages`, `/api/payment-methods`):**
   - Pre-compiled packages and payment gateway definitions cached in-memory.
   - Response time: `< 4ms`.
3. **Remote Configuration (`/api/app/remote-config`):**
   - Feature flags and dynamic settings cached with auto-invalidation on admin updates.
   - Response time: `< 3ms`.
4. **Call Initiation (`/api/call/initiate`):**
   - Returns call session, Agora channel name, and receiver metadata immediately in a single non-blocking payload.
   - Eliminates client-side "Connecting..." delay; Flutter app transitions straight into `ringing` state with zero UI lag.

### B. Flutter Mobile App Cache-First & Asset Preloader Strategy
```dart
// 1. App Startup (Splash / Auth Check)
class AppStartupPreloader {
  static Future<void> preloadAll() async {
    // Parallel non-blocking pre-fetch
    await Future.wait([
      GiftRepository.fetchAndCacheGifts(),
      CoinPackageRepository.fetchAndCachePackages(),
      RemoteConfigRepository.fetchAndCacheConfig(),
    ]);
  }
}

// 2. Cache-First Riverpod / BLoC Provider Pattern
// In Flutter UI, always render cached in-memory state immediately (zero spinner),
// then silently revalidate in the background (stale-while-revalidate).
```

---

## 7. Production Deployment & VPS Commands

Run the following commands on your production VPS (`/var/www/chinchins-live-website`):

```bash
cd /var/www/chinchins-live-website
git pull origin main
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear
php artisan optimize
chmod -R 775 public/assets public/uploads storage bootstrap/cache
chown -R www-data:www-data public/assets public/uploads storage bootstrap/cache
```
