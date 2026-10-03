# 📱 ChinChins Live - Noyon Complete RESTful API Documentation

> **Base URL:** `https://chinchins.live/api` or `http://127.0.0.1:8000/api`  
> **WebSocket / Real-Time Server:** Laravel Reverb (`wss://chinchins.live/app` or `ws://127.0.0.1:8080`)  
> **LiveKit Server:** `wss://chinchins.live/livekit`  
> **Headers:**
> - `Accept: application/json`
> - `Content-Type: application/json`
> - `Authorization: Bearer <AUTH_TOKEN>` (or pass `user_id` / `X-User-Id`)

---

## 📋 Table of Contents
1. [User Registration & Authentication](#1-user-registration--authentication)
2. [1-to-1 Audio & Video Calling Lifecycle](#2-1-to-1-audio--video-calling-lifecycle)
3. [Live Streaming Co-Host & Join Requests](#3-live-streaming-co-host--join-requests)
4. [Virtual Gifts & Real-Time Animations](#4-virtual-gifts--real-time-animations)
5. [Summary of Key Features & Fixes](#5-summary-of-key-features--fixes)

---

## 1. User Registration & Authentication

### 1.1 Register New User
- **Method:** `POST`
- **Endpoint:** `/api/register`
- **Description:** Registers a new user with automatic 8-digit Account ID, wallet setup, and token generation. (Fixed `current_level` SQL column error).

#### Request Body
```json
{
  "first_name": "Miru",
  "last_name": "shop",
  "nickname": "Miru",
  "phone": "+8801705579299",
  "password": "your_secure_password",
  "password_confirmation": "your_secure_password",
  "country": "Bangladesh",
  "city": "Dhaka",
  "gender": "male",
  "age": 22,
  "fcm_token": "fcm_token_device_string...",
  "device_type": "android"
}
```

#### Success Response (`201 Created` / `200 OK`)
```json
{
  "status": true,
  "message": "Registration successful",
  "data": {
    "user": {
      "id": 3,
      "account_id": "33626512",
      "first_name": "Miru",
      "last_name": "shop",
      "name": "Miru shop",
      "display_name": "Miru",
      "phone": "+8801705579299",
      "email": "8801705579299@user.chinchins.live",
      "coins": 0,
      "wallet_balance": 0,
      "level": "Lv.1",
      "current_level": 1,
      "total_earned_coins": 0,
      "is_online": true,
      "online_status": "online",
      "avatar_url": "https://ui-avatars.com/api/?name=Miru&background=3b82f6&color=ffffff&size=256&bold=true"
    },
    "token": "2|pVpBde6zt6UcKYEU5jlxkuq2ALchchNigjHPgEzd7a9939e3",
    "token_type": "Bearer"
  }
}
```

### 1.2 Login User
- **Method:** `POST`
- **Endpoint:** `/api/login`

#### Request Body
```json
{
  "phone": "+8801705579299",
  "password": "your_secure_password",
  "fcm_token": "fcm_token_device_string...",
  "device_type": "android"
}
```

---

## 2. 1-to-1 Audio & Video Calling Lifecycle

### 2.1 Call Config & Rates
- **Method:** `GET`
- **Endpoint:** `/api/call/config` or `/api/call/settings`
- **Description:** Returns rate per minute, free preview seconds, ICE servers, and user eligibility.

---

### 2.2 Make / Initiate Call
- **Method:** `POST`
- **Endpoint:** `/api/call/make-call` or `/api/call/instant` or `/api/call/start`

#### Request Body
```json
{
  "receiver_id": 2,
  "call_type": "video",
  "room_name": "optional_custom_room_name"
}
```

#### Success Response (`200 OK`)
```json
{
  "success": true,
  "status": true,
  "message": "Call initiated! Ringing receiver...",
  "room_name": "call_video_1_2_1791001961",
  "channel_name": "call_video_1_2_1791001961",
  "call_id": 2,
  "session_id": 2,
  "call_type": "video",
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "livekit_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "livekit_url": "wss://chinchins.live/livekit"
}
```

---

### 2.3 Check Incoming Calls (Receiver Polling / Push Trigger)
- **Method:** `GET` or `POST`
- **Endpoint:** `/api/call/incoming` or `/api/call/check-incoming`
- **Query / Body:** `user_id=2`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "has_incoming_call": true,
  "message": "Incoming call detected! Ring device.",
  "data": {
    "call_id": 2,
    "channel_name": "call_video_1_2_1791001961",
    "call_type": "video",
    "status": "ringing",
    "rate_per_minute": 100,
    "is_free_trial": false,
    "caller": {
      "id": 1,
      "name": "Nazmul",
      "avatar": "https://chinchins.live/avatar.png"
    }
  }
}
```

---

### 2.4 Accept / Connect Call (Receiver Action)
- **Method:** `POST`
- **Endpoint:** `/api/call/accept` or `/api/call/answer` or `/api/call/receive` or `/api/call/connect`

#### Request Body
```json
{
  "call_id": 2,
  "channel_name": "call_video_1_2_1791001961"
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "success": true,
  "message": "Call accepted and connected successfully! Start audio/video media stream.",
  "data": {
    "call_id": 2,
    "channel_name": "call_video_1_2_1791001961",
    "room_name": "call_video_1_2_1791001961",
    "call_type": "video",
    "status": "connected",
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "receiver_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "caller_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "livekit_url": "wss://chinchins.live/livekit",
    "started_at": "2026-10-03T04:32:00.000000Z"
  }
}
```

---

### 2.5 Real-Time Billing Pulse (In-Call Heartbeat & 50/50 Split)
- **Method:** `POST`
- **Endpoint:** `/api/call/deduct-interval` or `/api/call/pulse`

#### Request Body
```json
{
  "call_id": 2,
  "elapsed_seconds": 60,
  "interval_seconds": 60
}
```

---

### 2.6 Reject / Cancel / End Call
- **Reject (Receiver):** `POST /api/call/reject`
- **Cancel (Caller before answer):** `POST /api/call/cancel`
- **End / Hangup (Either party during call):** `POST /api/call/end` or `POST /api/call/hangup`

#### Request Body
```json
{
  "call_id": 2,
  "channel_name": "call_video_1_2_1791001961",
  "duration_seconds": 120
}
```

---

## 3. Live Streaming Co-Host & Join Requests

### 3.1 Viewer Sends Join Request (Request Seat / Co-Host)
- **Method:** `POST`
- **Endpoint:** `/api/live/request-join` or `/api/live/join-request`

#### Request Body
```json
{
  "live_stream_id": 1,
  "user_id": 2
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Co-host request sent to host successfully",
  "data": {
    "request_id": 1,
    "room_id": "1",
    "status": "pending",
    "user": {
      "id": 2,
      "display_name": "Sara",
      "avatar_url": "https://chinchins.live/avatar.png"
    }
  }
}
```

---

### 3.2 Host Retrieves Pending Join Requests
- **Method:** `GET` or `POST`
- **Endpoint:** `/api/live/join-requests` or `/api/live/requests`
- **Query / Body:** `stream_id=1&status=pending`
- **Behavior:** Only pending requests are returned. Once accepted or rejected, requests disappear from this list automatically.

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Join requests retrieved successfully",
  "room_id": "1",
  "data": [
    {
      "request_id": 1,
      "id": 1,
      "user_id": 2,
      "status": "pending",
      "user_name": "Sara",
      "display_name": "Sara",
      "avatar_url": "https://chinchins.live/avatar.png",
      "level": "Lv.1",
      "gender": "female"
    }
  ]
}
```

---

### 3.3 Host Accepts Co-Host Request (Dual Video Streaming Starts)
- **Method:** `POST`
- **Endpoint:** `/api/live/accept-join` or `/api/live/respond-request` (with `action: "accept"`) or `/api/live/cohost-action`

#### Request Body
```json
{
  "live_stream_id": 1,
  "guest_user_id": 2,
  "action": "accept"
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Co-host request accepted successfully",
  "data": {
    "request_id": 1,
    "room_id": "1",
    "room_name": "live_room_1",
    "guest_user_id": 2,
    "status": "accepted",
    "action": "accept",
    "can_publish": true,
    "guest_token": {
      "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
      "livekit_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
      "room_name": "live_room_1",
      "role": "co_host",
      "can_publish": true,
      "livekit_url": "wss://chinchins.live/livekit"
    }
  }
}
```

#### Real-Time Broadcast Events Dispatched:
- `CoHostRequestAccepted` (`private-user.{guest_user_id}`)
- `CoHostAcceptedEvent` (`live-stream.{stream_id}`)
- `CoHostJoinedEvent` (`live-stream.{stream_id}`)
- `CoHostStatusEvent` (`live-stream.{stream_id}`)

---

### 3.4 Host Rejects Join Request
- **Method:** `POST`
- **Endpoint:** `/api/live/respond-request` or `/api/live/cohost-action`

#### Request Body
```json
{
  "live_stream_id": 1,
  "guest_user_id": 2,
  "action": "reject"
}
```

---

## 4. Virtual Gifts & Real-Time Animations

### 4.1 Get Active Gifts Catalog with Animation URLs
- **Method:** `GET`
- **Endpoint:** `/api/gifts/active` or `/api/gifts`

#### Success Response (`200 OK`)
```json
{
  "success": true,
  "status": true,
  "data": [
    {
      "id": 1,
      "name": "Heart Rocket",
      "coins": 100,
      "icon_url": "https://chinchins.live/gifts/rocket.png",
      "image_url": "https://chinchins.live/gifts/rocket.png",
      "animation_url": "https://chinchins.live/gifts/rocket.svga",
      "animation_asset_url": "https://chinchins.live/gifts/rocket.svga",
      "file_url": "https://chinchins.live/gifts/rocket.svga",
      "format": "svga",
      "animation_type": "svga",
      "display_type": "fullscreen"
    }
  ]
}
```

---

### 4.2 Send Gift (In Live Stream or 1-to-1 Call)
- **Method:** `POST`
- **Endpoint:** `/api/gifts/send` or `/api/gift/send` or `/api/live/send-gift`

#### Request Body
```json
{
  "stream_id": "1",
  "receiver_id": 2,
  "gift_id": 1,
  "quantity": 1,
  "context": "live_stream"
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Gift sent successfully!",
  "remaining_balance": 400,
  "gift": {
    "stream_id": "1",
    "sender_id": 1,
    "sender_name": "Miru",
    "gift_id": 1,
    "gift_name": "Heart Rocket",
    "icon_url": "https://chinchins.live/gifts/rocket.png",
    "animation_url": "https://chinchins.live/gifts/rocket.svga",
    "animation_asset_url": "https://chinchins.live/gifts/rocket.svga",
    "file_url": "https://chinchins.live/gifts/rocket.svga",
    "format": "svga",
    "display_type": "fullscreen",
    "quantity": 1,
    "coins_spent": 100,
    "sender_coins_left": 400
  }
}
```

---

## 5. Summary of Key Features & Fixes

1. **User Registration:** Fixed `Column not found: 1054 Unknown column 'current_level'` by removing `current_level` from `User` `$fillable` and `AuthController.php`. Added computed accessor `getCurrentLevelAttribute()`.
2. **1-to-1 Calling Stability:** Unified `coins` & `wallet_balance` balance checks, removed offline blocking on dial, increased ringing timeout to 90s, added LiveKit tokens to accept response, and wired `/api/call/end`.
3. **Live Stream Co-Host Dual Streaming:** Fixed join request management (`getJoinRequests` filters pending requests) and automated dual video streaming token generation upon host accept.
4. **Gift Animations:** Supported `animation_url`, `animation_asset_url`, `file_url`, and `format` across all gift endpoints and broadcast events.
