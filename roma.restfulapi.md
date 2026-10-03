# 📱 ChinChins Live - Roma Complete RESTful API Documentation

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
4. [Virtual Gifts & Real-time Animations](#4-virtual-gifts--real-time-animations)
5. [Summary of Key Fixes](#5-summary-of-key-fixes)

---

## 1. User Registration & Authentication

### 1.1 Register New User
- **Method:** `POST`
- **Endpoint:** `/api/register`
- **Description:** Creates a new user account with automatic account ID, wallet setup, and token generation.

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

---

## 2. 1-to-1 Audio & Video Calling Lifecycle

### 2.1 Call Config & Rates
- **Method:** `GET`
- **Endpoint:** `/api/call/config` or `/api/call/settings`

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

### 2.3 Check Incoming Calls
- **Method:** `GET` or `POST`
- **Endpoint:** `/api/call/incoming` or `/api/call/check-incoming`
- **Query / Body:** `user_id=2`

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

### 2.5 Real-Time Billing Pulse
- **Method:** `POST`
- **Endpoint:** `/api/call/deduct-interval` or `/api/call/pulse`

### 2.6 Reject / Cancel / End Call
- **Reject (Receiver):** `POST /api/call/reject`
- **Cancel (Caller before answer):** `POST /api/call/cancel`
- **End / Hangup (Either party during call):** `POST /api/call/end` or `POST /api/call/hangup`

---

## 3. Live Streaming Co-Host & Join Requests

### 3.1 Viewer Sends Join Request
- **Method:** `POST`
- **Endpoint:** `/api/live/request-join` or `/api/live/join-request`

### 3.2 Host Retrieves Pending Join Requests
- **Method:** `GET` or `POST`
- **Endpoint:** `/api/live/join-requests` or `/api/live/requests`
- **Query / Body:** `stream_id=1&status=pending`

### 3.3 Host Accepts Co-Host Request (Dual Video Streaming Starts)
- **Method:** `POST`
- **Endpoint:** `/api/live/accept-join` or `/api/live/respond-request` (with `action: "accept"`) or `/api/live/cohost-action`

### 3.4 Host Rejects Join Request
- **Method:** `POST`
- **Endpoint:** `/api/live/respond-request` or `/api/live/cohost-action`

---

## 4. Virtual Gifts & Real-time Animations

### 4.1 Get Active Gifts Catalog
- **Method:** `GET`
- **Endpoint:** `/api/gifts/active` or `/api/gifts`

### 4.2 Send Gift (Live Stream or 1-to-1 Call)
- **Method:** `POST`
- **Endpoint:** `/api/gifts/send` or `/api/gift/send` or `/api/live/send-gift`

---

## 5. Summary of Key Fixes

1. Fixed `Column not found: 1054 Unknown column 'current_level'` error during registration.
2. Fixed 1-to-1 call disconnection issues, unified balance checks, updated ringing timeout, and added LiveKit tokens to accept responses.
3. Fixed live streaming co-host accept workflow: pending list now automatically clears accepted requests, and dual video streaming token generation is activated.
4. Ensured gifts provide animations (`animation_url`, `animation_asset_url`, `format`).
