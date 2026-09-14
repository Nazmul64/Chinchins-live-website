# 📄 RESTful API Specification, System Architecture & Critical Bug Fixes Documentation
**Target Roles:** Full-Stack Engineers (Laravel Backend & Flutter Frontend)  
**System Scope:** Live Streaming (Audio/Video), 1-on-1 Video Calling, Unified Core Messaging, Reverb Real-Time Pipeline, FinTech-Grade Coin & Gift Engine, Admin-Controlled Remote Flags (FLAG_SECURE, Debug HUD, Offline Visibility, PIP Restore)  
**Architecture:** Laravel 11.x RESTful Backend & WebSocket Server (Reverb/Pusher) + Flutter Mobile Client (Android & iOS)  
**Version:** 5.0.0 Enterprise Edition  
**Updated:** September 14, 2026  
**Document Name:** `check_restful_api_and_bug_fixing.md`  

---

## 📑 Table of Contents
1. [System Topology & High-Level Architecture](#1-system-topology--high-level-architecture)
2. [Critical Bug Fixes & Architectural Resolutions](#2-critical-bug-fixes--architectural-resolutions)
   - [Bug 1: Live Stream & In-Call Free Chat Policy (0 Coin Cost)](#bug-1-live-stream--in-call-free-chat-policy-0-coin-cost)
   - [Bug 2: Video Call PIP (Minimize) Tap to Restore without Call Drop](#bug-2-video-call-pip-minimize-tap-to-restore-without-call-drop)
   - [Bug 3: Dynamic Screenshot Protection (FLAG_SECURE) & Debug HUD Switch](#bug-3-dynamic-screenshot-protection-flag_secure--debug-hud-switch)
   - [Bug 4: Offline Users Visibility Toggle (`show_offline_users`)](#bug-4-offline-users-visibility-toggle-show_offline_users)
   - [Bug 5: Home Screen Floating VIP Widget Background Transparency](#bug-5-home-screen-floating-vip-widget-background-transparency)
   - [Bug 6: Strict 30 Strong-Motion Animated SVG Gifts Synchronization](#bug-6-strict-30-strong-motion-animated-svg-gifts-synchronization)
   - [Bug 7: Diamond Burst Motion Coin Packages Synchronization](#bug-7-diamond-burst-motion-coin-packages-synchronization)
   - [Bug 8: Agora/WebRTC Token Expiry & Background Auto-Drop Prevention](#bug-8-agorawebrtc-token-expiry--background-auto-drop-prevention)
3. [Database Schema & Migrations Reference](#3-database-schema--migrations-reference)
4. [Complete RESTful API Reference & Payloads](#4-complete-restful-api-reference--payloads)
   - [A. App Configuration & Remote Feature Flags](#a-app-configuration--remote-feature-flags)
   - [B. User Discovery & Visibility Engine](#b-user-discovery--visibility-engine)
   - [C. Unified Messaging & Free In-Room Chat](#c-unified-messaging--free-in-room-chat)
   - [D. Multi-Guest Live Streaming & Broadcasting](#d-multi-guest-live-streaming--broadcasting)
   - [E. Virtual Gifts Catalog & Instant Transfer](#e-virtual-gifts-catalog--instant-transfer)
   - [F. Coin Packages & Recharge Store](#f-coin-packages--recharge-store)
   - [G. Premium VIP Privilege Cards & Floating Banner](#g-premium-vip-privilege-cards--floating-banner)
   - [H. 1-on-1 Video & Audio Calling](#h-1-on-1-video--audio-calling)
5. [Real-Time WebSocket Pipeline & Reverb Events](#5-real-time-websocket-pipeline--reverb-events)
6. [Flutter Mobile Implementation & State Handlers](#6-flutter-mobile-implementation--state-handlers)
7. [Deployment & VPS Synchronization Guide](#7-deployment--vps-synchronization-guide)

---

## 1. System Topology & High-Level Architecture

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

## 2. Critical Bug Fixes & Architectural Resolutions

### Bug 1: Live Stream & In-Call Free Chat Policy (0 Coin Cost)
- **Problem:** Chatting during a live broadcast or active video call was previously deducting coins or exhausting free message quota after 5 messages.
- **Resolution:**
  - `MessageApiController.php` & `LiveStreamApiController.php` detect when `is_in_call: true`, `is_live: true`, `context: 'in_call'`, or `live_stream_id` is supplied.
  - In-room / In-call messaging is set to **100% Free ($0 coins)** with **zero quota deduction**.
  - Direct 1-on-1 private DM messaging retains the 5 free messages limit before coin deduction.

```php
// Backend Resolution in MessageApiController.php
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

### Bug 2: Video Call PIP (Minimize) Tap to Restore without Call Drop
- **Problem:** When users tapped on the minimized floating Picture-in-Picture (PIP) video call box to enlarge it, Flutter was executing `onClose()` or popping the navigator which sent a `call_end` event and hung up the call.
- **Resolution:**
  - The minimized PIP widget tap handler must strictly restore full-screen mode by changing the state provider flag `isMinimized = false` and navigating back to `ActiveVideoCallScreen` without tearing down Agora/WebRTC engine.

```dart
// Flutter PIP Restoration Logic
GestureDetector(
  onTap: () {
    // 1. Update call state to full screen
    ref.read(callStateProvider.notifier).setPipMode(false);
    // 2. Re-open fullscreen video call without leaving Agora channel
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

### Bug 3: Dynamic Screenshot Protection (FLAG_SECURE) & Debug HUD Switch
- **Problem:** When toggling off "Screenshot Protection (FLAG_SECURE)" or "In-App Debug HUD" from `/admin/settings/debug`, mobile devices continued to block screenshots because Android cached `FLAG_SECURE` in window manager on startup.
- **Resolution:**
  - Flutter dynamically fetches `/api/app/remote-config` on launch and updates Android native window flags immediately:

```kotlin
// Android MainActivity.kt
MethodChannel(flutterEngine.dartExecutor.binaryMessenger, "chinchins/security").setMethodCallHandler { call, result ->
    if (call.method == "setScreenshotProtection") {
        val enabled = call.argument<Boolean>("enabled") ?: false
        if (enabled) {
            window.setFlags(WindowManager.LayoutParams.FLAG_SECURE, WindowManager.LayoutParams.FLAG_SECURE)
        } else {
            // Clears hardware screenshot blocking when admin turns it OFF
            window.clearFlags(WindowManager.LayoutParams.FLAG_SECURE)
        }
        result.success(true)
    }
}
```

```dart
// Flutter Dart Handler
final remoteConfig = await ApiService.getRemoteConfig();
if (remoteConfig.screenshotProtectionEnabled) {
  await FlutterWindowManager.addFlags(FlutterWindowManager.FLAG_SECURE);
} else {
  await FlutterWindowManager.clearFlags(FlutterWindowManager.FLAG_SECURE);
}
```

---

### Bug 4: Offline Users Visibility Toggle (`show_offline_users`)
- **Problem:** Admin toggling "Show Offline Users in App Discovery List" was not saving or updating mobile discovery list.
- **Resolution:**
  - Added save handler in `AppSettingController@update` for `show_offline_users`.
  - Updated `UserDiscoveryController@index` and `ProfileController@getActiveUsers` to filter `is_online = true` when `show_offline_users` is `0` (OFF), and show all users when `1` (ON).

---

### Bug 5: Home Screen Floating VIP Widget Background Transparency
- **Problem:** In the mobile app explore/home feed, the floating VIP widget ("Extra Gems") was wrapped in a solid navy square container with an X button, obscuring the transparent vector artwork.
- **Resolution:**
  - Removed container background box styling in Flutter explore header, allowing `vip_privilege_full_motion.svg` to float transparently over host avatar cards.

---

### Bug 6: Strict 30 Strong-Motion Animated SVG Gifts Synchronization
- **Problem:** Previous database seeders accumulated 160+ old gifts, causing the count to show 190.
- **Resolution:**
  - Truncated legacy gifts table.
  - Exactly 30 Strong-Motion Animated SVGs (from `01_rose.svg` to `30_royal_palace.svg`) are stored in `public/uploads/gifts/` and mapped with IDs `1..30`.
  - Locked `Gift::seedDefaultGifts()` and `GiftSeeder` so legacy gifts can never be re-seeded.

---

### Bug 7: Diamond Burst Motion Coin Packages Synchronization
- **Problem:** Coin package icons were static and mismatched.
- **Resolution:**
  - Mapped all 6 Store Packages to high-motion SVG & PNG diamond burst artworks in `public/uploads/coin_packages/` (`burst`, `diamond_orb`, `diamond_crown`, `crystal_crown`, `magic_bag`, `royal_chest`).

---

### Bug 8: Agora/WebRTC Token Expiry & Background Auto-Drop Prevention
- **Problem:** Mobile backgrounding sent false `call_end` disconnects.
- **Resolution:**
  - Tokens generated with 24-hour TTL (`agora_token_ttl = 86400`).
  - Mobile client sends periodic heartbeat `/api/v1/calls/heartbeat` every 30 seconds to maintain presence.
  - Backgrounding triggers temporary mute, NOT `call_end`.

---

## 3. Database Schema & Migrations Reference

```sql
-- Gifts Catalog Table (30 Strong-Motion Animated Items)
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

-- Live Streams Table
CREATE TABLE live_streams (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  host_id BIGINT UNSIGNED NOT NULL,
  channel_name VARCHAR(255) UNIQUE NOT NULL,
  title VARCHAR(255) NOT NULL,
  cover_image VARCHAR(255) NULL,
  status ENUM('live', 'ended', 'banned') NOT NULL DEFAULT 'live',
  viewer_count INT UNSIGNED NOT NULL DEFAULT 1,
  total_diamonds_earned BIGINT UNSIGNED NOT NULL DEFAULT 0,
  agora_token TEXT NULL,
  started_at TIMESTAMP NULL,
  ended_at TIMESTAMP NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (host_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Live Messages Table (Free Real-time Public Stream Messages)
CREATE TABLE live_messages (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  live_stream_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  message TEXT NOT NULL,
  type VARCHAR(50) NOT NULL DEFAULT 'text',
  metadata JSON NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (live_stream_id) REFERENCES live_streams(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
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

### C. Free Live Stream Chat Message
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

### D. 30 Strong-Motion Gifts Catalog
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

### E. Coin Recharge Packages Store
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

## 5. Deployment & VPS Synchronization Guide

Run the following commands on your production VPS (`/var/www/chinchins-live-website`):

```bash
cd /var/www/chinchins-live-website
git pull origin main
php artisan migrate --force
php artisan db:seed --class=StrongMotionGiftsSeeder --force
php artisan db:seed --class=CoinPackageSeeder --force
chmod -R 775 public/assets public/uploads
chown -R www-data:www-data public/assets public/uploads
php artisan optimize:clear
```
