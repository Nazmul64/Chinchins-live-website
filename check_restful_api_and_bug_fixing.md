# 📄 RESTful API Specification, System Architecture & Critical Bug Fixes Documentation
**Target Roles:** Full-Stack Engineers (Laravel Backend & Flutter Frontend)  
**System Scope:** Live Streaming (Audio/Video), 1-on-1 Video Calling, Unified Core Messaging, Reverb Real-Time Pipeline, FinTech-Grade Coin & Gift Engine, Admin-Controlled Feature Flags & PIP Optimization  
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
   - [Bug 3: Dynamic Screenshot Protection & Debug Mode FLAG_SECURE](#bug-3-dynamic-screenshot-protection--debug-mode-flag_secure)
   - [Bug 4: Home Screen Floating VIP Widget Background Transparency](#bug-4-home-screen-floating-vip-widget-background-transparency)
   - [Bug 5: Strict 30 Strong-Motion Animated SVG Gifts Synchronization](#bug-5-strict-30-strong-motion-animated-svg-gifts-synchronization)
   - [Bug 6: Diamond Burst Motion Coin Packages Synchronization](#bug-6-diamond-burst-motion-coin-packages-synchronization)
   - [Bug 7: Agora/WebRTC Token Expiry & Background Auto-Drop Prevention](#bug-7-agorawebrtc-token-expiry--background-auto-drop-prevention)
3. [Database Schema & Migrations Reference](#3-database-schema--migrations-reference)
4. [Unified Core Messaging Engine & Live Streaming Pipeline](#4-unified-core-messaging-engine--live-streaming-pipeline)
5. [FinTech-Grade Concurrency-Safe Coin & Gift Engine](#5-fintech-grade-concurrency-safe-coin--gift-engine)
6. [Complete RESTful API Reference & Payloads](#6-complete-restful-api-reference--payloads)
   - [A. App Configuration, Remote Feature Flags & Dynamic Settings](#a-app-configuration-remote-feature-flags--dynamic-settings)
   - [B. User Discovery & Visibility Engine](#b-user-discovery--visibility-engine)
   - [C. Unified Messaging & Free In-Room Chat](#c-unified-messaging--free-in-room-chat)
   - [D. Multi-Guest Live Streaming & Broadcasting](#d-multi-guest-live-streaming--broadcasting)
   - [E. Virtual Gifts Catalog & Instant Transfer](#e-virtual-gifts-catalog--instant-transfer)
   - [F. Coin Packages & Recharge Store](#f-coin-packages--recharge-store)
   - [G. Premium VIP Privilege Cards & Floating Banner](#g-premium-vip-privilege-cards--floating-banner)
   - [H. 1-on-1 Video & Audio Calling](#h-1-on-1-video--audio-calling)
7. [WebSocket Channels & Real-Time Event Payloads](#7-websocket-channels--real-time-event-payloads)
8. [Flutter Frontend Implementation & State Isolation](#8-flutter-frontend-implementation--state-isolation)
9. [Admin Panel Dashboard & Management Architecture](#9-admin-panel-dashboard--management-architecture)
10. [Deployment & VPS Synchronization Guide](#10-deployment--vps-synchronization-guide)

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
- **Root Problem:** Previously, chatting during a live broadcast or active video call was deducting coins or consuming private DM free message quota after 5 messages.
- **Resolution:**
  - `MessageApiController.php` & `LiveStreamApiController.php` now detect when `is_in_call: true`, `is_live: true`, `context: 'in_call'`, or `live_stream_id` is supplied.
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
- **Root Problem:** When users tapped on the minimized floating Picture-in-Picture (PIP) video call box to enlarge it, Flutter was accidentally executing `onClose()` or popping the navigator which sent a `call_end` event and hung up the call.
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

### Bug 3: Dynamic Screenshot Protection & Debug Mode FLAG_SECURE
- **Root Problem:** When toggling off "Screenshot Protection (FLAG_SECURE)" or "In-App Debug HUD" from `/admin/settings/debug`, mobile devices continued to block screenshots because Android cached `FLAG_SECURE` in window manager on startup.
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
            window.clearFlags(WindowManager.LayoutParams.FLAG_SECURE)
        }
        result.success(true)
    }
}
```

---

### Bug 4: Home Screen Floating VIP Widget Background Transparency
- **Root Problem:** In the mobile app explore/home feed, the floating VIP widget ("Extra Gems") was wrapped in a solid navy square container with an X button, obscuring the transparent vector artwork.
- **Resolution:**
  - Removed container background box styling in Flutter explore header, allowing `vip_privilege_full_motion.svg` to float transparently over host avatar cards.

---

### Bug 5: Strict 30 Strong-Motion Animated SVG Gifts Synchronization
- **Root Problem:** Previous database seeders accumulated 160+ old gifts, causing the count to show 190.
- **Resolution:**
  - Replaced and truncated legacy gifts.
  - Exactly 30 Strong-Motion Animated SVGs (from `01_rose.svg` to `30_royal_palace.svg`) are stored in `public/uploads/gifts/` and mapped with IDs `1..30`.
  - Locked `Gift::seedDefaultGifts()` and `GiftSeeder` so legacy gifts can never be re-seeded.

---

### Bug 6: Diamond Burst Motion Coin Packages Synchronization
- **Root Problem:** Coin package icons were static and mismatched.
- **Resolution:**
  - Mapped all 6 Store Packages to high-motion SVG & PNG diamond burst artworks in `public/uploads/coin_packages/` (`burst`, `diamond_orb`, `diamond_crown`, `crystal_crown`, `magic_bag`, `royal_chest`).

---

### Bug 7: Agora/WebRTC Token Expiry & Background Auto-Drop Prevention
- **Root Problem:** Mobile backgrounding sent false `call_end` disconnects.
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

### B. Free Live Stream Chat Message
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

### C. 30 Strong-Motion Gifts Catalog
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

### D. Coin Recharge Packages Store
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
