# 📱 Chinchins Live — Comprehensive RESTful API & System Architecture Specification
**Project:** Chinchins Live Video Streaming, Calling & Social Platform  
**Architecture:** Laravel 11.x RESTful Backend & WebSocket Server (Reverb/Pusher) + Flutter Mobile Client (Android & iOS)  
**Version:** 3.5.0  
**Updated:** September 2026  
**Document Name:** `add_more_restful_api.md`  

---

## 📑 Table of Contents
1. [Executive Summary & System Overview](#1-executive-summary--system-overview)
2. [Online / Offline User Visibility & Admin Controls](#2-online--offline-user-visibility--admin-controls)
3. [User Presence, Heartbeat & Availability Rules](#3-user-presence-heartbeat--availability-rules)
4. [Multi-User Live Streaming & Co-Hosting Mechanism](#4-multi-user-live-streaming--co-hosting-mechanism)
5. [Dynamic Virtual Gift System & Revenue Split](#5-dynamic-virtual-gift-system--revenue-split)
6. [Complete RESTful API Reference & Payloads](#6-complete-restful-api-reference--payloads)
   - [Live Streaming Endpoints](#-live-streaming-endpoints)
   - [Gift System Endpoints](#-gift-system-endpoints)
   - [User Presence & Active Users Endpoints](#-user-presence--active-users-endpoints)
   - [1-on-1 Calling & In-Call Messaging Endpoints](#-1-on-1-calling--in-call-messaging-endpoints)
7. [Real-Time WebSocket Architecture & Events Reference](#7-real-time-websocket-architecture--events-reference)
8. [Database Schema & Migrations Reference](#8-database-schema--migrations-reference)
9. [Flutter Integration Guide (Stack Layer Architecture)](#9-flutter-integration-guide-stack-layer-architecture)
10. [Admin Panel Specifications & Requirements](#10-admin-panel-specifications--requirements)

---

## 1. Executive Summary & System Overview

Chinchins Live backend operates as an orchestrator and state manager over a high-concurrency streaming and real-time messaging pipeline:
- **Media Streaming Engine:** Dual-compatible with **Agora RTC SDK** and **VPS WebRTC (Coturn STUN/TURN)**, switchable dynamically from the Admin Panel.
- **Real-Time Signaling & Broadcast:** **Laravel Reverb / Pusher WebSockets** for zero-latency (< 50ms) events + RESTful fallback.
- **Database:** MySQL 8.x with indexed relationships, atomic coin balance transactions, and sub-second query execution.

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                           FLUTTER MOBILE CLIENT                                 │
│  ┌─────────────────────────┐  ┌───────────────────────┐  ┌───────────────────┐  │
│  │ 1-on-1 Video Calling    │  │ Live Broadcasting     │  │ Multi-Guest Grid  │  │
│  │ (Agora RTC / WebRTC)    │  │ (Multi-Viewer Stream) │  │ (Co-Hosting)      │  │
│  └─────────────────────────┘  └───────────────────────┘  └───────────────────┘  │
└────────────────────────────────────────┬────────────────────────────────────────┘
                                         │
             ┌───────────────────────────┴───────────────────────────┐
             ▼                                                       ▼
┌─────────────────────────────────┐             ┌────────────────────────────────┐
│   LARAVEL REVERB WEBSOCKET      │             │   LARAVEL RESTFUL API          │
│   • live-stream.{stream_id}     │             │   • /api/v1/live/*             │
│   • presence-live.{live_id}     │             │   • /api/v1/gifts              │
│   • presence-call.{call_id}     │             │   • /api/v1/users/active       │
│   • call.{call_id}              │             │   • /api/call/*                │
│   • user.{user_id}              │             │   • /api/user/heartbeat        │
└─────────────────────────────────┘             └────────────────────────────────┘
```

---

## 2. Online / Offline User Visibility & Admin Controls

### Business Logic
1. **Default Behavior:** Under normal operation, only **Online Users** (`is_online = true`) are visible in the app's explore and user lists.
2. **Admin Toggle Control:** The Admin Panel features a global toggle switch: **Show Offline Users in App** stored in the `app_settings` table under the key `show_offline_users`.
   - **When ON (`value = "1"` or `"true"`):** Both Online and Offline users are displayed in the list. Online users appear first (`ORDER BY is_online DESC, last_seen_at DESC`).
   - **When OFF (`value = "0"` or `"false"`):** Only active online users (`is_online = true`) are returned by the API.

### Controller Logic Implementation
```php
// Query Builder with Admin Toggle Consideration
$showOfflineSetting = AppSetting::where('key', 'show_offline_users')->value('value');
$canShowOffline = filter_var($showOfflineSetting, FILTER_VALIDATE_BOOLEAN) || $showOfflineSetting === '1';

$usersQuery = User::select('id', 'name', 'account_id', 'avatar', 'avatar_frame', 'is_online', 'online_status', 'current_status', 'last_seen_at');

if (!$canShowOffline) {
    // Only online users when admin toggle is OFF
    $usersQuery->where(function ($q) {
        $q->where('is_online', true)
          ->orWhere('online_status', 'online')
          ->orWhere('last_seen_at', '>=', now()->subMinutes(5));
    });
}

$users = $usersQuery->orderBy('is_online', 'desc')
                    ->orderBy('last_seen_at', 'desc')
                    ->paginate(20);
```

---

## 3. User Presence, Heartbeat & Availability Rules

### User Status Model
Each user has `current_status` and `online_status` set to one of:
- `available`: Online and ready to receive 1-on-1 audio/video calls.
- `in_call`: Busy in an active 1-on-1 audio/video call.
- `in_live`: Busy hosting or co-hosting a live stream broadcast.
- `offline`: Exited app or heartbeat expired.

### 30-Second Grace Period & Minimize Handling
- **Problem:** Minimizing the app or answering in floating PiP mode was triggering premature call drops.
- **Fix:** When minimized, Flutter calls `POST /api/call/minimize`. The session remains active in `connected` status. The call is **ONLY** terminated if no heartbeat pulse is received for **30 consecutive seconds**.

### Call Initiation Defense Rules
1. **Calling an Available User:**
   - Host `is_online == true` & `current_status == 'available'` -> Returns `200 OK` (Ringing started).
2. **Calling an Offline User (`is_online == false`):**
   - Returns **`400 Bad Request`** with `code: USER_OFFLINE`, `message: "Host is currently offline"`.
3. **Calling a Busy User (`current_status == 'in_call' || 'in_live'`):**
   - Returns **`400 Bad Request`** with `code: USER_BUSY`, `message: "Host is currently busy in another call or live broadcast"`.

---

## 4. Multi-User Live Streaming & Co-Hosting Mechanism

### Architecture Flow
1. **Host Starts Broadcast (`POST /api/v1/live/start`):**
   - Creates a `live_streams` record (`status = 'live'`).
   - Generates Broadcaster RTC token (Agora/WebRTC).
   - Updates Host `current_status = 'in_live'`.
   - Broadcasts to public channel that the host has gone live.
2. **Viewers Browse & Join (`GET /api/v1/live/active-streams` & `POST /api/v1/live/join`):**
   - Fetches active live streams.
   - Viewer connects to video feed and subscribes to `live-stream.{stream_id}` and `presence-live.{live_id}`.
   - Viewer count increments automatically.
3. **Real-Time Live Chat (`POST /api/v1/live/send-message`):**
   - Persists chat message in `live_messages`.
   - Broadcasts event `chat.message` on `live-stream.{stream_id}`.
4. **Multi-Guest Co-Hosting:**
   - **Request:** Viewer calls `POST /api/live/join-request` (`LiveJoinRequested` to host).
   - **Host Accept:** `POST /api/live/accept-request` (`action = 'accept'`) -> Upgrades viewer to `guest` role, generates Broadcaster RTC token, fires `LiveJoinResponded`.
   - **Host Kick:** `POST /api/live/kick-guest` -> Fires `LiveGuestKicked`, removes guest video stream.
5. **Host Ends Broadcast (`POST /api/v1/live/end`):**
   - Marks stream `ended_at = now()`, `status = 'ended'`.
   - Broadcasts `LiveStreamEnded` to all viewers.
   - Resets Host `current_status = 'available'`.

---

## 5. Dynamic Virtual Gift System & Revenue Split

### Revenue Model
- Every virtual gift has a `coin_price`.
- When sent, the coin cost is deducted from sender and a **50/50 revenue split** is executed in a database transaction (`DB::transaction`):
  - 50% diamonds/coins credited to Host.
  - 50% platform fee retained.
- Recorded in `gift_transactions` and `live_messages`.

### Real-Time Gift Animation Event
- Dispatches `LiveGiftSentEvent` on channel `live-stream.{stream_id}` with event name `gift.received`.
- Flutter mobile client renders an `IgnorePointer` overlay container and plays the SVG / Lottie / SVGA animation for **3.5 seconds** before automatically dismounting.

---

## 6. Complete RESTful API Reference & Payloads

### 🔴 Live Streaming Endpoints

#### 1. Host Start Live Stream
- **URL:** `POST /api/v1/live/start` or `POST /api/live/start`
- **Headers:** `Authorization: Bearer {token}`, `Content-Type: application/json`
- **Request Body:**
```json
{
  "title": "Evening Live Chat & Songs 🎵",
  "cover_image_url": "https://chinchins.live/uploads/live_streaming/cover_1.jpg"
}
```
- **Response (200 OK):**
```json
{
  "status": true,
  "success": true,
  "message": "Live stream broadcast started successfully!",
  "data": {
    "live_stream_id": 1,
    "channel_name": "live_2_1726300000_abcd",
    "title": "Evening Live Chat & Songs 🎵",
    "cover_image_url": "https://chinchins.live/uploads/live_streaming/cover_1.jpg",
    "status": "live",
    "role": "host",
    "session": {
      "success": true,
      "driver": "agora",
      "channel_name": "live_2_1726300000_abcd",
      "role": "publisher",
      "agora": {
        "app_id": "YOUR_AGORA_APP_ID",
        "token": "007eJxTYGDA9q30...YOUR_RTC_TOKEN",
        "channel_name": "live_2_1726300000_abcd",
        "uid": 2,
        "role": 1,
        "token_expire_seconds": 3600
      }
    },
    "host": {
      "id": 2,
      "account_id": "90218492",
      "display_name": "Nusrat Jahan",
      "avatar_url": "https://chinchins.live/uploads/avatars/host2.jpg"
    }
  }
}
```

---

#### 2. Get Active Live Streams List (Home & Live Section)
- **URL:** `GET /api/v1/live/active-streams` or `GET /api/lives/active`
- **Headers:** `Accept: application/json`
- **Response (200 OK):**
```json
{
  "status": true,
  "success": true,
  "message": "Active live streams retrieved successfully.",
  "data": [
    {
      "id": 1,
      "channel_name": "live_2_1726300000_abcd",
      "title": "Evening Live Chat & Songs 🎵",
      "cover_image_url": "https://chinchins.live/uploads/live_streaming/cover_1.jpg",
      "status": "live",
      "viewer_count": 85,
      "total_diamonds_earned": 4200,
      "started_at": "2026-09-14T08:00:00+06:00",
      "host": {
        "id": 2,
        "account_id": "90218492",
        "display_name": "Nusrat Jahan",
        "avatar_url": "https://chinchins.live/uploads/avatars/host2.jpg",
        "gender": "female",
        "level": "Lv8"
      },
      "active_guests": []
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "total": 1
  }
}
```

---

#### 3. Viewer Join Live Stream
- **URL:** `POST /api/v1/live/join` or `POST /api/live/join`
- **Headers:** `Authorization: Bearer {token}`, `Content-Type: application/json`
- **Request Body:**
```json
{
  "stream_id": 1,
  "live_stream_id": 1
}
```
- **Response (200 OK):**
```json
{
  "status": true,
  "success": true,
  "message": "Joined live stream successfully.",
  "data": {
    "live_stream_id": 1,
    "channel_name": "live_2_1726300000_abcd",
    "title": "Evening Live Chat & Songs 🎵",
    "cover_image_url": "https://chinchins.live/uploads/live_streaming/cover_1.jpg",
    "viewer_count": 86,
    "role": "audience",
    "session": {
      "driver": "agora",
      "channel_name": "live_2_1726300000_abcd",
      "agora": {
        "app_id": "YOUR_AGORA_APP_ID",
        "token": "007eJxTYGDA9q30...AUDIENCE_TOKEN",
        "uid": 105,
        "role": 2
      }
    },
    "host": {
      "id": 2,
      "account_id": "90218492",
      "display_name": "Nusrat Jahan",
      "avatar_url": "https://chinchins.live/uploads/avatars/host2.jpg"
    }
  }
}
```

---

#### 4. Viewer Leave Live Stream
- **URL:** `POST /api/v1/live/leave` or `POST /api/live/leave`
- **Headers:** `Authorization: Bearer {token}`, `Content-Type: application/json`
- **Request Body:**
```json
{
  "stream_id": 1,
  "live_stream_id": 1
}
```
- **Response (200 OK):**
```json
{
  "status": true,
  "success": true,
  "message": "Left live stream successfully."
}
```

---

#### 5. Host End Live Stream
- **URL:** `POST /api/v1/live/end` or `POST /api/live/end`
- **Headers:** `Authorization: Bearer {token}`, `Content-Type: application/json`
- **Request Body:**
```json
{
  "stream_id": 1,
  "live_stream_id": 1
}
```
- **Response (200 OK):**
```json
{
  "status": true,
  "success": true,
  "message": "Live stream ended successfully.",
  "data": {
    "live_stream_id": 1,
    "channel_name": "live_2_1726300000_abcd",
    "duration_seconds": 1920,
    "total_diamonds_earned": 4200,
    "peak_viewers": 86
  }
}
```

---

#### 6. Send Public Chat Message in Live
- **URL:** `POST /api/v1/live/send-message` or `POST /api/live/message`
- **Headers:** `Authorization: Bearer {token}`, `Content-Type: application/json`
- **Request Body:**
```json
{
  "stream_id": 1,
  "live_stream_id": 1,
  "message": "Hello everyone! Beautiful stream! ❤️"
}
```
- **Response (200 OK):**
```json
{
  "status": true,
  "success": true,
  "message": "Live message sent.",
  "data": {
    "id": 204,
    "live_stream_id": 1,
    "stream_id": 1,
    "user_id": 105,
    "sender_name": "Alex",
    "sender_avatar": "https://chinchins.live/uploads/avatars/user105.jpg",
    "level": "Lv3",
    "message": "Hello everyone! Beautiful stream! ❤️",
    "type": "text",
    "created_at": "2026-09-14T08:30:00+06:00"
  }
}
```

---

#### 7. Send Virtual Gift in Live Stream
- **URL:** `POST /api/v1/live/send-gift` or `POST /api/live/gift`
- **Headers:** `Authorization: Bearer {token}`, `Content-Type: application/json`
- **Request Body:**
```json
{
  "stream_id": 1,
  "gift_id": 4,
  "quantity": 1
}
```
- **Response (200 OK):**
```json
{
  "status": "success",
  "message": "Gift successfully sent!",
  "user_coins": 1250,
  "data": {
    "transaction_id": 85,
    "stream_id": 1,
    "sender": {
      "id": 105,
      "name": "Alex",
      "avatar": "https://chinchins.live/uploads/avatars/user105.jpg"
    },
    "gift": {
      "id": 4,
      "name": "Luxury Sports Car",
      "slug": "sports_car",
      "coin_price": 500,
      "icon_url": "https://chinchins.live/uploads/gifts/car_icon.png",
      "animation_asset_url": "https://chinchins.live/uploads/gifts/car_animation.svg",
      "animation_type": "svg"
    },
    "quantity": 1,
    "total_coins": 500,
    "timestamp": 1726301400
  }
}
```

---

#### 8. Co-Hosting: Request, Respond & Kick

| Action | Method | URL | Body |
|:---|:---|:---|:---|
| **Request to Join Grid** | `POST` | `/api/live/join-request` | `{"live_stream_id": 1}` |
| **Host Respond** | `POST` | `/api/live/accept-request` | `{"request_id": 12, "action": "accept"}` |
| **Host Kick Guest** | `POST` | `/api/live/kick-guest` | `{"live_stream_id": 1, "guest_user_id": 105}` |

---

### 🎁 Gift System Endpoints

#### 1. Get Approved Gifts Catalog (For Gift Drawer Tray)
- **URL:** `GET /api/v1/gifts` or `GET /api/gifts`
- **Headers:** `Accept: application/json`
- **Response (200 OK):**
```json
{
  "status": true,
  "success": true,
  "message": "Gifts retrieved successfully.",
  "data": [
    {
      "id": 1,
      "name": "Rose",
      "slug": "rose",
      "coin_price": 10,
      "icon_url": "https://chinchins.live/uploads/gifts/rose_icon.png",
      "animation_asset_url": "https://chinchins.live/uploads/gifts/rose_anim.svg",
      "animation_type": "svg",
      "is_active": true
    },
    {
      "id": 4,
      "name": "Luxury Sports Car",
      "slug": "sports_car",
      "coin_price": 500,
      "icon_url": "https://chinchins.live/uploads/gifts/car_icon.png",
      "animation_asset_url": "https://chinchins.live/uploads/gifts/car_anim.svg",
      "animation_type": "svg",
      "is_active": true
    },
    {
      "id": 7,
      "name": "Helicopter",
      "slug": "helicopter",
      "coin_price": 2000,
      "icon_url": "https://chinchins.live/uploads/gifts/helicopter_icon.png",
      "animation_asset_url": "https://chinchins.live/uploads/gifts/helicopter.json",
      "animation_type": "lottie",
      "is_active": true
    }
  ]
}
```

---

### 👥 User Presence & Active Users Endpoints

#### 1. Get Active Users List (Filtered by Admin Offline Visibility Setting)
- **URL:** `GET /api/v1/users/active` or `GET /api/profiles/active`
- **Headers:** `Accept: application/json`
- **Response (200 OK):**
```json
{
  "status": "success",
  "show_offline_users": false,
  "data": [
    {
      "id": 2,
      "account_id": "90218492",
      "name": "Nusrat Jahan",
      "avatar": "https://chinchins.live/uploads/avatars/host2.jpg",
      "is_online": true,
      "is_available": true,
      "status_text": "Available",
      "last_active_at": "2026-09-14T08:35:00+06:00"
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "total": 1
  }
}
```

#### 2. User Heartbeat Pulse (Send every 30s)
- **URL:** `POST /api/user/heartbeat`
- **Headers:** `Authorization: Bearer {token}`, `Content-Type: application/json`
- **Request Body:** `{"status": "available"}`
- **Response (200 OK):** `{"status": true, "message": "Heartbeat acknowledged."}`

---

### 📞 1-on-1 Calling & In-Call Messaging Endpoints

#### 1. Initiate 1-on-1 Video/Audio Call
- **URL:** `POST /api/call/initiate`
- **Headers:** `Authorization: Bearer {token}`, `Content-Type: application/json`
- **Request Body:**
```json
{
  "receiver_id": 2,
  "call_type": "video"
}
```
- **Response (If Host is Online & Available - 200 OK):**
```json
{
  "status": true,
  "success": true,
  "call_session_id": "18",
  "channel_name": "call_video_1_2_1726300000_abcd",
  "caller": {"id": 1, "name": "John"},
  "receiver": {"id": 2, "name": "Nusrat Jahan"},
  "agora_token": "007eJxTY...TOKEN"
}
```
- **Response (If Host is Offline - 400 Bad Request):**
```json
{
  "status": false,
  "can_call": false,
  "code": "USER_OFFLINE",
  "is_online": false,
  "message": "Nusrat Jahan is currently offline."
}
```
- **Response (If Host is Busy - 400 Bad Request):**
```json
{
  "status": false,
  "can_call": false,
  "code": "USER_BUSY",
  "is_busy": true,
  "message": "Nusrat Jahan is currently busy in another call or live broadcast."
}
```

#### 2. Send In-Call Live Chat Message
- **URL:** `POST /api/call/chat/send`
- **Headers:** `Authorization: Bearer {token}`, `Content-Type: application/json`
- **Request Body:**
```json
{
  "call_session_id": "18",
  "receiver_id": 2,
  "type": "text",
  "message": "You look wonderful! ❤️"
}
```
- **Response (200 OK):**
```json
{
  "status": true,
  "success": true,
  "message": "Message sent successfully during video call.",
  "data": {
    "id": 62,
    "call_id": 18,
    "sender_id": 1,
    "receiver_id": 2,
    "message": "You look wonderful! ❤️",
    "created_at": "2026-09-14T08:36:00+06:00"
  }
}
```

---

## 7. Real-Time WebSocket Architecture & Events Reference

| Event Class | Broadcast Channel | Broadcast Event Name (`broadcastAs`) | Description |
|:---|:---|:---|:---|
| **`LiveChatMessageEvent`** | `live-stream.{stream_id}` | `chat.message` | Real-time chat message broadcast to all viewers in live room. |
| **`LiveGiftSentEvent`** | `live-stream.{stream_id}` | `gift.received` | Real-time full-screen gift animation event (SVG/Lottie). |
| **`InCallMessageSent`** | `presence-call.{call_id}`, `call.{call_id}` | `InCallMessageSent` | Live chat message between caller & receiver in 1-on-1 call. |
| **`LiveJoinRequested`** | `presence-live.{live_id}`, `user.{host_id}` | `LiveJoinRequested` | Co-host request sent to broadcast host. |
| **`LiveJoinResponded`** | `presence-live.{live_id}`, `user.{guest_id}`| `LiveJoinResponded` | Host acceptance/rejection sent to guest. |
| **`LiveGuestKicked`** | `presence-live.{live_id}`, `user.{guest_id}`| `LiveGuestKicked` | Host removes guest from live grid. |
| **`LiveStreamEnded`** | `presence-live.{live_id}`, `live-stream.{stream_id}` | `LiveStreamEnded` | Live broadcast ended notification with statistics. |

---

## 8. Database Schema & Migrations Reference

```sql
-- 1. User Table Extensions
ALTER TABLE users 
  ADD COLUMN is_online BOOLEAN DEFAULT FALSE,
  ADD COLUMN last_active_at TIMESTAMP NULL,
  ADD COLUMN coins_balance BIGINT UNSIGNED DEFAULT 0,
  ADD COLUMN current_status VARCHAR(30) DEFAULT 'available',
  ADD COLUMN online_status VARCHAR(30) DEFAULT 'available';

-- 2. App Settings Table
CREATE TABLE app_settings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(191) UNIQUE NOT NULL,
  `value` TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);

-- 3. Live Stream Sessions Table
CREATE TABLE live_streams (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  host_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(255) NULL,
  channel_name VARCHAR(191) UNIQUE NOT NULL,
  stream_token TEXT NULL,
  status ENUM('live', 'ended') DEFAULT 'live',
  viewer_count INT UNSIGNED DEFAULT 0,
  total_diamonds_earned BIGINT UNSIGNED DEFAULT 0,
  cover_image VARCHAR(500) NULL,
  agora_token TEXT NULL,
  started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  ended_at TIMESTAMP NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (host_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 4. Gifts Catalog Table
CREATE TABLE gifts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(191) NOT NULL,
  slug VARCHAR(191) UNIQUE NOT NULL,
  coin_price INT UNSIGNED NOT NULL,
  icon_url VARCHAR(500) NOT NULL,
  animation_asset_url VARCHAR(500) NOT NULL,
  animation_type ENUM('svg', 'lottie') DEFAULT 'svg',
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);

-- 5. Gift Transactions Table
CREATE TABLE gift_transactions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  stream_id BIGINT UNSIGNED NOT NULL,
  sender_id BIGINT UNSIGNED NOT NULL,
  receiver_id BIGINT UNSIGNED NOT NULL,
  gift_id BIGINT UNSIGNED NOT NULL,
  coins_spent INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (stream_id) REFERENCES live_streams(id) ON DELETE CASCADE,
  FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (gift_id) REFERENCES gifts(id) ON DELETE CASCADE
);
```

---

## 9. Flutter Integration Guide (Stack Layer Architecture)

### 3-Layer Stack Architecture
The Flutter live screen is structured in a 3-layer `Stack`:
1. **Background Layer:** Full-screen Agora RTC Video Feed (`AgoraVideoView`).
2. **Middle Layer:** Interactive UI components (Header, Viewer avatars, Public Chat message list, Gift drawer button, Like animations).
3. **Top Layer (Animation Layer):** An `IgnorePointer` transparent overlay. When a `gift.received` WebSocket event is received, it mounts the SVG or Lottie animation on full screen, plays for **3.5 seconds**, and automatically dismounts.

```
┌────────────────────────────────────────────────────────────────────────┐
│                        FLUTTER LIVE ROOM STACK                         │
│                                                                        │
│   ┌────────────────────────────────────────────────────────────────┐   │
│   │ TOP LAYER: IgnorePointer Overlay (SVG / Lottie 3.5s Animation) │   │
│   └────────────────────────────────────────────────────────────────┘   │
│                                                                        │
│   ┌────────────────────────────────────────────────────────────────┐   │
│   │ MIDDLE LAYER: Header, Real-Time Chat, Viewers, Gift Drawer     │   │
│   └────────────────────────────────────────────────────────────────┘   │
│                                                                        │
│   ┌────────────────────────────────────────────────────────────────┐   │
│   │ BACKGROUND LAYER: Agora RTC Video Feed (Broadcaster / Grid)    │   │
│   └────────────────────────────────────────────────────────────────┘   │
└────────────────────────────────────────────────────────────────────────┘
```

### Flutter Live Screen Code Template:
```dart
import 'dart:async';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:lottie/lottie.dart';
import 'package:laravel_flutter_pusher/laravel_flutter_pusher.dart';
import 'package:agora_rtc_engine/agora_rtc_engine.dart';

class LiveStreamingScreen extends StatefulWidget {
  final int streamId;
  final String channelName;
  final String rtcToken;
  final bool isHost;

  const LiveStreamingScreen({
    Key? key,
    required this.streamId,
    required this.channelName,
    required this.rtcToken,
    this.isHost = false,
  }) : super(key: key);

  @override
  _LiveStreamingScreenState createState() => _LiveStreamingScreenState();
}

class _LiveStreamingScreenState extends State<LiveStreamingScreen> {
  // Gift animation state
  Map<String, dynamic>? _activeGiftPayload;
  Timer? _giftTimer;

  // Chat messages
  final List<Map<String, dynamic>> _messages = [];

  @override
  void initState() {
    super.initState();
    _subscribeToWebSockets();
  }

  void _subscribeToWebSockets() {
    // Channel: live-stream.{stream_id}
    final channel = PusherClient.subscribe('live-stream.${widget.streamId}');

    // 1. Listen for Public Chat Messages
    channel.bind('chat.message', (event) {
      if (event?.data != null) {
        final data = jsonDecode(event.data);
        setState(() {
          _messages.add(data);
        });
      }
    });

    // 2. Listen for Gift Received Animations
    channel.bind('gift.received', (event) {
      if (event?.data != null) {
        final payload = jsonDecode(event.data);
        _playGiftAnimation(payload);
      }
    });
  }

  void _playGiftAnimation(Map<String, dynamic> giftPayload) {
    _giftTimer?.cancel();
    setState(() {
      _activeGiftPayload = giftPayload;
    });

    // Automatically dismount after 3.5 seconds
    _giftTimer = Timer(const Duration(milliseconds: 3500), () {
      if (mounted) {
        setState(() {
          _activeGiftPayload = null;
        });
      }
    });
  }

  @override
  void dispose() {
    _giftTimer?.cancel();
    PusherClient.unsubscribe('live-stream.${widget.streamId}');
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Stack(
        children: [
          // 1. LAYER 1: Background Video Feed
          _buildBackgroundVideoFeed(),

          // 2. LAYER 2: Live Room UI (Header, Chat, Controls)
          _buildMiddleLiveControls(),

          // 3. LAYER 3: Top Transparent Gift Animation (IgnorePointer)
          if (_activeGiftPayload != null)
            IgnorePointer(
              ignoring: true,
              child: _buildGiftAnimationOverlay(_activeGiftPayload!),
            ),
        ],
      ),
    );
  }

  Widget _buildBackgroundVideoFeed() {
    return Container(
      color: Colors.black,
      child: const Center(
        child: Text("Agora RTC Video Feed", style: TextStyle(color: Colors.white70)),
      ),
    );
  }

  Widget _buildMiddleLiveControls() {
    return SafeArea(
      child: Column(
        children: [
          // Header (Host info, Viewer count, Close button)
          _buildHeader(),
          const Spacer(),
          // Live Chat Messages List
          _buildChatList(),
          // Bottom Actions (Chat Input, Gift Tray Button)
          _buildBottomActionTray(),
        ],
      ),
    );
  }

  Widget _buildHeader() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Row(
        children: [
          CircleAvatar(
            backgroundColor: Colors.pinkAccent,
            child: const Icon(Icons.person, color: Colors.white),
          ),
          const SizedBox(width: 8),
          const Text("Live Broadcast", style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
          const Spacer(),
          IconButton(
            icon: const Icon(Icons.close, color: Colors.white),
            onPressed: () => Navigator.of(context).pop(),
          ),
        ],
      ),
    );
  }

  Widget _buildChatList() {
    return Container(
      height: 200,
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: ListView.builder(
        itemCount: _messages.length,
        itemBuilder: (context, index) {
          final msg = _messages[index];
          return Padding(
            padding: const EdgeInsets.symmetric(vertical: 4),
            child: Row(
              children: [
                Text("${msg['sender_name'] ?? 'User'}: ", style: const TextStyle(color: Colors.amber, fontWeight: FontWeight.bold)),
                Text("${msg['message'] ?? ''}", style: const TextStyle(color: Colors.white)),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildBottomActionTray() {
    return Padding(
      padding: const EdgeInsets.all(16.0),
      child: Row(
        children: [
          Expanded(
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 12),
              decoration: BoxDecoration(color: Colors.black45, borderRadius: BorderRadius.circular(24)),
              child: const TextField(
                style: TextStyle(color: Colors.white),
                decoration: InputDecoration(hintText: "Say something...", hintStyle: TextStyle(color: Colors.white60), border: InputBorder.none),
              ),
            ),
          ),
          const SizedBox(width: 10),
          FloatingActionButton.small(
            backgroundColor: Colors.pink,
            child: const Icon(Icons.card_giftcard, color: Colors.white),
            onPressed: () {
              // Open Gift Drawer Bottom Sheet
            },
          )
        ],
      ),
    );
  }

  Widget _buildGiftAnimationOverlay(Map<String, dynamic> payload) {
    final gift = payload['gift'] ?? {};
    final sender = payload['sender'] ?? {};
    final animationUrl = gift['animation_asset_url'] ?? gift['icon_url'] ?? '';
    final animationType = gift['animation_type'] ?? 'svg';

    return Stack(
      children: [
        // Full screen animation
        Center(
          child: animationType == 'lottie'
              ? Lottie.network(animationUrl, repeat: true, width: 320, height: 320)
              : SvgPicture.network(animationUrl, width: 280, height: 280),
        ),
        // Sender notification badge
        Positioned(
          top: 100,
          left: 20,
          right: 20,
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            decoration: BoxDecoration(
              gradient: const LinearGradient(colors: [Colors.purple, Colors.pinkAccent]),
              borderRadius: BorderRadius.circular(25),
              boxShadow: const [BoxShadow(color: Colors.black45, blurRadius: 10)],
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                CircleAvatar(
                  backgroundImage: sender['avatar'] != null ? NetworkImage(sender['avatar']) : null,
                  child: sender['avatar'] == null ? const Icon(Icons.person) : null,
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    "${sender['name'] ?? 'Someone'} sent ${gift['name'] ?? 'a Gift'}! ✨",
                    style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
                  ),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }
}
```

---

## 10. Admin Panel Specifications & Requirements

1. **Global App Settings Page (`/admin/app-settings`):**
   - **Toggle Switch:** `Show Offline Users in App` (Stored in `app_settings` with key `show_offline_users`).
   - When switched ON/OFF, immediately invalidates the setting cache.
2. **Gift Management CRUD (`/admin/gifts`):**
   - Manage Gift Name, Coin Price, 2D Preview Icon upload, and Screen Animation Asset upload (SVG / Lottie JSON / SVGA).
3. **Live Stream Monitoring & Terminate Action (`/admin/live-streams`):**
   - View currently active broadcasts and terminate inappropriate streams with immediate socket notification.
4. **Gift Transactions Audit Log (`/admin/gift-transactions`):**
   - Complete record of sender, receiver, coins spent, and timestamp.

---
*Chinchins Live Technical Specification — Confidential & Proprietary*
