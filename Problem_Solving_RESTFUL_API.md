# 🚀 ChinChins Live — Complete Problem Solving & RESTful API Documentation

> **Base URL:** `https://chinchins.live/api` or `http://127.0.0.1:8000/api`  
> **WebSocket (Reverb):** `wss://chinchins.live/app`  
> **LiveKit Room Server:** `wss://chinchins.live/livekit`  
> **Standard Request Headers:**
> ```http
> Accept: application/json
> Content-Type: application/json
> Authorization: Bearer <AUTH_TOKEN>
> ```
> *(Fallback Header: `X-User-Id: <USER_ID>` or parameter `user_id`)*

---

## 📑 Table of Contents
1. [🚨 Urgent Backend Fixes: Caller Self-Accept Prevention & WebRTC STUN/TURN](#1-urgent-backend-fixes-caller-self-accept-prevention--webrtc-stunturn)
2. [🎵 Dynamic Call Ringtone & Outgoing Dial Tone Architecture](#2-dynamic-call-ringtone--outgoing-dial-tone-architecture)
3. [📞 1-on-1 Audio & Video Calling Lifecycle](#3-1-on-1-audio--video-calling-lifecycle)
4. [🎁 7-Day Daily Check-in & Rewards Claim Engine](#4-7-day-daily-check-in--rewards-claim-engine)
5. [💎 Home Screen Floating Action Widget / VIP Banner](#5-home-screen-floating-action-widget--vip-banner)
6. [⚡ Global Bootstrap Configuration (< 5ms In-Memory)](#6-global-bootstrap-configuration--5ms-in-memory)

---

## 1. 🚨 Urgent Backend Fixes: Caller Self-Accept Prevention & WebRTC STUN/TURN

### 1.1 Prevent Caller Self-Accept (Auto-Accept Bug Fix)
To prevent the caller from mistakenly or automatically accepting their own outgoing call when polling or callback events occur, strict verification is enforced in `CallController.php` / `WebRTCCallController.php`:

```php
$authUserId = auth()->id() ?? $user?->id;

// 1. কলার নিজে কখনো এক্সেপ্ট করতে পারবে না
if ($call->caller_id == $authUserId) {
    return response()->json([
        'status'  => false,
        'message' => 'Caller cannot accept their own call.',
    ], 403);
}

// 2. শুধুমাত্র নির্দিষ্ট রিসিভার এক্সেপ্ট করতে পারবে
if ($call->receiver_id != $authUserId) {
    return response()->json([
        'status'  => false,
        'message' => 'Unauthorized receiver.',
    ], 403);
}
```

### 1.2 STUN / TURN Server Credentials for Mobile 4G/5G Connectivity
To avoid P2P disconnection on mobile carrier data (NAT / CGNAT), valid TURN and STUN server credentials and fast LiveKit tokens are returned in all calling APIs:

```json
"ice_servers": [
  {
    "urls": [
      "stun:stun.l.google.com:19302",
      "stun:stun1.l.google.com:19302",
      "stun:stun2.l.google.com:19302",
      "stun:stun.cloudflare.com:3478"
    ]
  },
  {
    "urls": [
      "turn:openrelay.metered.ca:80",
      "turn:openrelay.metered.ca:443",
      "turn:openrelay.metered.ca:443?transport=tcp",
      "turns:openrelay.metered.ca:443?transport=tcp",
      "turns:openrelay.metered.ca:5349"
    ],
    "username": "openrelay",
    "credential": "openrelay"
  }
]
```

---

## 2. 🎵 Dynamic Call Ringtone & Outgoing Dial Tone Architecture

The mobile app must **never hardcode ringtone audio files**. All audio tones are dynamically set in the Admin Panel (`Call & Ringtone Settings`) and fetched from the backend database:

- **Incoming Call Ringtone (`incoming_ringtone_url`):** Plays continuously on the receiver's phone when an incoming call arrives until answered, rejected, or timed out.
- **Outgoing Call Dial Tone (`outgoing_ringtone_url`):** Plays continuously on the caller's phone while waiting for the receiver to answer.

### 2.1 Get Dynamic Ringtone & Audio Tone Settings
- **Method:** `GET`
- **Endpoints:**
  - `GET /api/call-settings`
  - `GET /api/call/settings`
  - `GET /api/ringtone-settings`
  - `GET /api/ringtones`

#### Response (`200 OK`):
```json
{
  "status": true,
  "success": true,
  "message": "Call settings and ringtones retrieved successfully.",
  "data": {
    "incoming_ringtone_url": "https://chinchins.live/uploads/ringtones/incoming_ringtone_1791453979.mp3",
    "outgoing_ringtone_url": "https://assets.mixkit.co/active_storage/sfx/1359/1359-preview.mp3",
    "is_call_enabled": true,
    "is_free_call_enabled": true,
    "free_call_duration_seconds": 16,
    "video_call_rate_per_minute": 1800,
    "audio_call_rate_per_minute": 100,
    "ice_servers": [...]
  }
}
```

---

## 3. 📞 1-on-1 Audio & Video Calling Lifecycle

### 3.1 Initiate Call
- **Method:** `POST`
- **Endpoints:** `/api/call/initiate`, `/api/call/make-call`, `/api/call/instant`
- **Body:**
```json
{
  "receiver_id": 2,
  "call_type": "video"
}
```
- **Response (`200 OK`):** Returns `call_id`, `channel_name`, `token` (LiveKit token), `ice_servers`, `outgoing_ringtone_url`, and `receiver` details.

### 3.2 Check Incoming Calls (For Receiver Device)
- **Method:** `GET` / `POST`
- **Endpoints:** `GET /api/call/incoming`, `POST /api/call/check-incoming`, `GET /api/call/wait-incoming`
- **Response (`200 OK`):**
```json
{
  "status": true,
  "has_incoming_call": true,
  "data": {
    "call_id": 188,
    "channel_name": "call_video_1_2_1740000000",
    "call_type": "video",
    "status": "ringing",
    "incoming_ringtone_url": "https://chinchins.live/uploads/ringtones/incoming_ringtone_1791453979.mp3",
    "caller": {
      "id": 1,
      "name": "Super Admin",
      "avatar": "https://chinchins.live/uploads/avatars/user_1.png"
    }
  }
}
```

### 3.3 Accept Call (Receiver Only)
- **Method:** `POST`
- **Endpoints:** `/api/call/accept`, `/api/call/answer`, `/api/call/receive`, `/api/call/connect`
- **Body:**
```json
{
  "call_id": 188
}
```
- **Response (`200 OK`):** Returns `status: "connected"`, LiveKit room tokens, `livekit_url`, and `ice_servers`.
- **Error Response (If Caller attempts self-accept `403 Forbidden`):**
```json
{
  "status": false,
  "message": "Invalid action: Caller cannot accept their own call."
}
```

### 3.4 Reject / Decline Call (Receiver Only)
- **Method:** `POST`
- **Endpoints:** `/api/call/reject`, `/api/call/decline`
- **Body:** `{"call_id": 188}`

### 3.5 Cancel Call (Caller Only)
- **Method:** `POST`
- **Endpoints:** `/api/call/cancel`
- **Body:** `{"call_id": 188}`

### 3.6 End / Hangup Active Call
- **Method:** `POST`
- **Endpoints:** `/api/call/end`, `/api/call/hangup`
- **Body:**
```json
{
  "call_id": 188,
  "duration_seconds": 120
}
```

---

## 4. 🎁 7-Day Daily Check-in & Rewards Claim Engine

### 4.1 Get Daily Rewards Status
- **Method:** `GET`
- **Endpoints:** `/api/daily-rewards/status`, `/api/daily-claim/status`, `/api/daily-rewards`

#### Response (`200 OK`):
```json
{
  "status": true,
  "should_open_popup": true,
  "can_claim": true,
  "current_streak": 2,
  "next_day_number": 3,
  "user_current_coins": 1520,
  "days": [
    {
      "day_number": 1,
      "reward_coins": 50,
      "icon_image": "https://chinchins.live/uploads/claim/day_1.svg",
      "is_claimed": true,
      "is_current": false,
      "is_locked": false
    },
    {
      "day_number": 2,
      "reward_coins": 50,
      "icon_image": "https://chinchins.live/uploads/claim/day_2.svg",
      "is_claimed": true,
      "is_current": false,
      "is_locked": false
    },
    {
      "day_number": 3,
      "reward_coins": 50,
      "icon_image": "https://chinchins.live/uploads/claim/day_3.svg",
      "is_claimed": false,
      "is_current": true,
      "is_locked": false
    },
    {
      "day_number": 4,
      "reward_coins": 50,
      "icon_image": "https://chinchins.live/uploads/claim/day_4.svg",
      "is_claimed": false,
      "is_current": false,
      "is_locked": true
    },
    {
      "day_number": 5,
      "reward_coins": 50,
      "icon_image": "https://chinchins.live/uploads/claim/day_5.svg",
      "is_claimed": false,
      "is_current": false,
      "is_locked": true
    },
    {
      "day_number": 6,
      "reward_coins": 50,
      "icon_image": "https://chinchins.live/uploads/claim/day_6.svg",
      "is_claimed": false,
      "is_current": false,
      "is_locked": true
    },
    {
      "day_number": 7,
      "reward_coins": 100,
      "icon_image": "https://chinchins.live/uploads/claim/day_7.svg",
      "is_claimed": false,
      "is_current": false,
      "is_locked": true
    }
  ]
}
```

### 4.2 Claim Today's Reward
- **Method:** `POST`
- **Endpoints:** `/api/daily-rewards/claim`, `/api/daily-claim`
- **Response (`200 OK`):**
```json
{
  "status": true,
  "message": "Congratulations! You claimed 50 coins for Day 3.",
  "claimed_day": 3,
  "reward_coins": 50,
  "new_coin_balance": 1570,
  "next_day_number": 4,
  "next_claim_at": "2026-10-09T05:29:53+06:00"
}
```

---

## 5. 💎 Home Screen Floating Action Widget / VIP Banner

- **Method:** `GET`
- **Endpoints:** `/api/widgets/floating-banner`, `/api/floating-widget`, `/api/banner/floating`
- **Response (`200 OK`):**
```json
{
  "status": true,
  "data": {
    "is_enabled": true,
    "icon_url": "https://chinchins.live/uploads/banners/vip_floating_icon.png",
    "title": "Extra Gems VIP",
    "badge_text": "+50% BONUS",
    "target_screen": "store_recharge_sheet",
    "redirect_url": "https://chinchins.live/deposit"
  }
}
```

---

## 6. ⚡ Global Bootstrap Configuration (< 5ms In-Memory)

Fetch all essential application settings, payment gateways, coin store packages, level thresholds, dynamic ringtones, and ICE servers in a single ultra-fast cached call.

- **Method:** `GET`
- **Endpoints:** `/api/bootstrap-config`, `/api/app-config`, `/api/v1/bootstrap`

#### Response (`200 OK`):
```json
{
  "success": true,
  "status": true,
  "message": "Global bootstrap configuration loaded from memory.",
  "data": {
    "payment_methods": [...],
    "coin_packages": [...],
    "gifts_catalog": [...],
    "level_badges": [...],
    "vip_frames": [...],
    "call_settings": {
      "is_call_enabled": true,
      "free_call_duration_seconds": 16,
      "video_call_rate_per_minute": 1800,
      "audio_call_rate_per_minute": 100,
      "incoming_ringtone_url": "https://chinchins.live/uploads/ringtones/incoming_ringtone_1791453979.mp3",
      "outgoing_ringtone_url": "https://assets.mixkit.co/active_storage/sfx/1359/1359-preview.mp3"
    },
    "ringtone_settings": {
      "incoming_ringtone_url": "https://chinchins.live/uploads/ringtones/incoming_ringtone_1791453979.mp3",
      "outgoing_ringtone_url": "https://assets.mixkit.co/active_storage/sfx/1359/1359-preview.mp3"
    },
    "ice_servers": [...],
    "app_settings": {
      "app_name": "ChinChins Live",
      "active_streaming_engine": "livekit",
      "active_calling_engine": "livekit",
      "livekit_ws_url": "wss://chinchins.live/livekit",
      "currency": "BDT",
      "currency_symbol": "৳"
    }
  }
}
```

---

## 7. 👑 VIP Privilege Cards & Monthly Card Subscriptions

### 7.1 Get All VIP Privilege Cards
- **Method:** `GET`
- **Endpoints:** `/api/vip-cards`, `/api/monthly-cards`

#### Response (`200 OK`):
```json
{
  "status": true,
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Super Monthly VIP Card",
      "price": 300,
      "price_bdt": 300,
      "original_price_bdt": 600,
      "diamonds_reward": 32940,
      "cost_diamonds": 300,
      "daily_checkin_diamonds": 26330,
      "perks": "3 Perks",
      "outfits": "VIP Outfits",
      "validity_days": 30,
      "duration_days": 30,
      "total_return_coins": 59270,
      "is_subscribed": false
    }
  ]
}
```

### 7.2 Purchase VIP Privilege Card
- **Method:** `POST`
- **Endpoint:** `/api/vip-cards/purchase`
- **Body:**
```json
{
  "vip_card_id": 1,
  "payment_method": "coins"
}
```

