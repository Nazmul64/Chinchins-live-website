# 📱 Chinchins Live — RESTful API & System Update Documentation
**Project:** Chinchins Live Video Streaming, Calling & Social Platform  
**Architecture:** Laravel 11.x RESTful Backend & WebSocket Server (Reverb/Pusher) + Flutter Mobile Client (Android & iOS)  
**Version:** 3.0.0  
**Updated:** September 2026  
**Document Name:** `restful_api_for_update.md`

---

## 📑 Table of Contents
1. [Executive Summary & Technical Architecture](#1-executive-summary--technical-architecture)
2. [Critical Bug Fixes: Video Calling, Lagging & Disconnection](#2-critical-bug-fixes-video-calling-lagging--disconnection)
3. [In-Call Real-Time Messaging & Chat Sync Engine](#3-in-call-real-time-messaging--chat-sync-engine)
4. [User Presence, Status Tracking & Availability Rules](#4-user-presence-status-tracking--availability-rules)
5. [Multi-User Live Streaming, Broadcasting & Co-Hosting System](#5-multi-user-live-streaming-broadcasting--co-hosting-system)
6. [Complete RESTful API Reference & Payloads](#6-complete-restful-api-reference--payloads)
7. [WebSocket Channels & Real-Time Events Reference](#7-websocket-channels--real-time-events-reference)
8. [Database Schema & Migrations Reference](#8-database-schema--migrations-reference)
9. [Flutter Integration Guides & Architecture Snippets](#9-flutter-integration-guides--architecture-snippets)

---

## 1. Executive Summary & Technical Architecture

Chinchins Live backend is built on a **High-Performance Dual-Engine Streaming Architecture**:
- **Media Streaming Engine:** Dual-compatible with **Agora RTC SDK** and **Custom VPS WebRTC (Coturn STUN/TURN)**, switchable dynamically from the Admin Panel.
- **Signaling & Real-Time Sync:** **Laravel Reverb / Pusher WebSockets** for zero-latency (< 50ms) events + RESTful fallback.
- **Database:** MySQL 8.x with indexed relationships and sub-second query caching.

```
┌────────────────────────────────────────────────────────────────────────┐
│                        FLUTTER MOBILE CLIENT                           │
│  ┌───────────────────────┐  ┌────────────────────┐  ┌───────────────┐  │
│  │ 1-on-1 Video Call     │  │ Live Broadcasting  │  │ Co-Hosting    │  │
│  │ (WebRTC / Agora RTC)  │  │ (Multi-Viewer)     │  │ (Multi-Guest) │  │
│  └───────────────────────┘  └────────────────────┘  └───────────────┘  │
└────────────────────────────────────┬───────────────────────────────────┘
                                     │
         ┌───────────────────────────┴───────────────────────────┐
         ▼                                                       ▼
┌─────────────────────────────────┐             ┌────────────────────────────────┐
│   LARAVEL REVERB WEBSOCKET      │             │   LARAVEL RESTFUL API          │
│   • presence-call.{call_id}     │             │   • /api/live/*                │
│   • presence-live.{live_id}     │             │   • /api/call/*                │
│   • user.{user_id}              │             │   • /api/user/heartbeat        │
└─────────────────────────────────┘             └────────────────────────────────┘
```

---

## 2. Critical Bug Fixes: Video Calling, Lagging & Disconnection

### 1. Token & Session Optimization:
- Unified dynamic token generation (`POST /api/stream/session-token` or `/api/calls`).
- Token expiration set to **3600 seconds (1 hour)** with automatic background token refresh (`POST /api/agora/token/refresh`).

### 2. Background State & Heartbeat Mechanism (30s Grace Period):
- **Problem:** When an app minimizes to background or triggers floating PiP mode, premature `call_end` was sent.
- **Fix:** 
  - Flutter frontend intercepts back button with `PopScope` and calls `POST /api/call/minimize`.
  - Backend maintains call state in `connected` status. Call session is **ONLY** terminated if no heartbeat/pulse is received for **30 consecutive seconds**.

### 3. Keep-Alive Socket Connection:
- Laravel Reverb WebSocket connection configured with `heartbeat: 25s` interval to prevent background socket drops.

---

## 3. In-Call Real-Time Messaging & Chat Sync Engine

### Real-Time Event Architecture:
- Event: `InCallMessageSent` / `CallMessageSent`
- Broadcast Channels:
  1. `presence-call.{call_id}` (Presence channel with caller and receiver member list)
  2. `call.{call_id}` (Public channel)
  3. `user.{receiver_id}` (Direct user push alert channel)
  4. `call_chat.{call_id}` (Dedicated chat channel)

### Dual-Channel Delivery:
1. **WebSocket Broadcast (< 50ms):** Delivered immediately upon saving.
2. **WebRTC Signaling Relay (`call_signals`):** Inserted as `type: chat_message` for clients on weak 3G networks.
3. **Persistent Chat Storage (`chat_messages` & `call_messages`):** Preserves conversation history in user's inbox.

---

## 4. User Presence, Status Tracking & Availability Rules

### User Status Model:
Each user has `current_status` set to one of:
- `available`: User is online in app and free to receive calls.
- `in_call` / `busy_call`: User is currently in a 1-on-1 audio/video call.
- `in_live`: User is currently broadcasting or co-hosting a live stream.
- `offline`: User has exited app or heartbeat timed out (> 5 mins).

### Real-Time Heartbeat API:
- `POST /api/user/heartbeat`: Sent every 30 seconds by the mobile client.
- When app is closed or backgrounded, user status is immediately marked `offline`.

### Profile View & Calling Rules:
1. **Calling an Online & Available User:**
   - Initiates call ringing -> Returns `200 OK`.
2. **Calling an Offline User (`is_online == false`):**
   - Returns **`400 Bad Request`** with `code: USER_OFFLINE`, `message: "Host is currently offline"`.
   - **No call session is started.**
3. **Calling a Busy User (`current_status == 'in_call' || 'in_live'`):**
   - Returns **`400 Bad Request`** with `code: USER_BUSY`, `message: "Host is currently busy in another call or live broadcast"`.
4. **Profile View Auto-Callback:**
   - If host `is_available == true`, `auto_call_triggered: true, trigger_action: 'INCOMING_CALL'`.
   - If host is offline or busy, `auto_call_triggered: false, trigger_action: 'NONE'`.

---

## 5. Multi-User Live Streaming, Broadcasting & Co-Hosting System

### System Components:
1. **Active Live Stream List (`GET /api/lives/active`):**
   - Returns all currently active broadcasts (`status = 'live'`) with host profile, current viewer count, and active guest co-hosts.
   - If no live is running, returns `[]`.

2. **Host Start & End Live (`POST /api/live/start` & `POST /api/live/end`):**
   - **Start:** Creates live session, generates Broadcaster RTC token, sets host `current_status = 'in_live'`, and opens channel `presence-live.{live_id}`.
   - **End:** Broadcasts `LiveStreamEnded` to all viewers and guests, marks session `status = 'ended'`, resets host to `available`.

3. **Viewer Join & Public Chat:**
   - Viewers join via `POST /api/live/join` and subscribe to `presence-live.{live_id}`.
   - Send chat: `POST /api/live/message` -> Broadcasts `LiveMessageSent`.
   - Send gifts: `POST /api/live/gift` -> Applies **50/50 revenue split** and broadcasts `LiveGiftSent` with SVGA animation url.

4. **Co-Hosting & Guest Join Grid (Multi-Guest):**
   - **Request Join:** Viewer calls `POST /api/live/join-request` -> Dispatches `LiveJoinRequested` to host.
   - **Host Accept:** `POST /api/live/accept-request` (`action: 'accept'`) -> Upgrades viewer to `guest` role, generates Broadcaster RTC token, dispatches `LiveJoinResponded`.
   - **Host Reject:** `POST /api/live/accept-request` (`action: 'reject'`) -> Rejects request.
   - **Host Kick Guest:** `POST /api/live/kick-guest` -> Dispatches `LiveGuestKicked`, removes guest video stream, and downgrades back to viewer.

---

## 6. Complete RESTful API Reference & Payloads

### 🔴 Live Streaming APIs

#### 1. Get Active Live Broadcasts
- **Endpoint:** `GET /api/lives/active` (or `GET /api/live/active`, `GET /api/live/list`)
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
      "title": "Evening Music & Chat with Nusrat ❤️",
      "cover_image_url": "https://chinchins.live/uploads/live_streaming/cover_1.jpg",
      "status": "live",
      "viewer_count": 142,
      "total_diamonds_earned": 5400,
      "started_at": "2026-09-14T08:00:00+06:00",
      "host": {
        "id": 2,
        "account_id": "90218492",
        "display_name": "Nusrat Jahan",
        "avatar_url": "https://chinchins.live/uploads/avatars/host2.jpg",
        "gender": "female",
        "country": "Bangladesh",
        "level": "Lv8"
      },
      "active_guests": [
        {
          "user_id": 14,
          "account_id": "83719201",
          "display_name": "Tamanna",
          "avatar_url": "https://chinchins.live/uploads/avatars/guest1.jpg"
        }
      ]
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

#### 2. Host Start Live Broadcast
- **Endpoint:** `POST /api/live/start`
- **Headers:** `Authorization: Bearer {token}`, `Content-Type: application/json`
- **Request Body:**
```json
{
  "title": "Welcome to my official live stream! 🌟",
  "cover_image_url": "https://chinchins.live/uploads/live_streaming/cover_custom.jpg"
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
    "title": "Welcome to my official live stream! 🌟",
    "cover_image_url": "https://chinchins.live/uploads/live_streaming/cover_custom.jpg",
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

#### 3. Host End Live Broadcast
- **Endpoint:** `POST /api/live/end`
- **Headers:** `Authorization: Bearer {token}`, `Content-Type: application/json`
- **Request Body:**
```json
{
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
    "duration_seconds": 1845,
    "total_diamonds_earned": 5400,
    "peak_viewers": 142
  }
}
```

---

#### 4. Audience Join Live Broadcast
- **Endpoint:** `POST /api/live/join`
- **Headers:** `Authorization: Bearer {token}`, `Content-Type: application/json`
- **Request Body:**
```json
{
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
    "title": "Welcome to my official live stream! 🌟",
    "cover_image_url": "https://chinchins.live/uploads/live_streaming/cover_custom.jpg",
    "viewer_count": 143,
    "role": "audience",
    "session": {
      "success": true,
      "driver": "agora",
      "channel_name": "live_2_1726300000_abcd",
      "agora": {
        "app_id": "YOUR_AGORA_APP_ID",
        "token": "007eJxTY...",
        "uid": 105,
        "role": 2
      }
    }
  }
}
```

---

#### 5. Send Chat Message in Live Stream
- **Endpoint:** `POST /api/live/message`
- **Headers:** `Authorization: Bearer {token}`, `Content-Type: application/json`
- **Request Body:**
```json
{
  "live_stream_id": 1,
  "message": "You look so beautiful tonight! ❤️",
  "type": "text"
}
```
- **Response (200 OK):**
```json
{
  "status": true,
  "success": true,
  "message": "Live message sent.",
  "data": {
    "id": 101,
    "live_stream_id": 1,
    "user_id": 105,
    "sender_name": "Alex",
    "sender_avatar": "https://chinchins.live/uploads/avatars/user105.jpg",
    "level": "Lv3",
    "message": "You look so beautiful tonight! ❤️",
    "type": "text",
    "created_at": "2026-09-14T08:15:00+06:00"
  }
}
```

---

#### 6. Send Virtual Gift in Live Stream (50/50 Revenue Split)
- **Endpoint:** `POST /api/live/gift`
- **Headers:** `Authorization: Bearer {token}`, `Content-Type: application/json`
- **Request Body:**
```json
{
  "live_stream_id": 1,
  "gift_id": 15,
  "quantity": 1
}
```
- **Response (200 OK):**
```json
{
  "status": true,
  "success": true,
  "message": "Gift sent successfully!",
  "user_coins": 1250,
  "data": {
    "id": 102,
    "live_stream_id": 1,
    "sender_id": 105,
    "sender_name": "Alex",
    "sender_avatar": "https://chinchins.live/uploads/avatars/user105.jpg",
    "gift_id": 15,
    "gift_name": "Luxury Sports Car",
    "gift_icon": "https://chinchins.live/assets/images/gifts/car.png",
    "animation_url": "https://chinchins.live/assets/animations/luxury_car.svga",
    "quantity": 1,
    "total_coins": 500,
    "created_at": "2026-09-14T08:16:00+06:00"
  }
}
```

---

#### 7. Co-Hosting / Guest Join Request Flow

1. **Viewer Sends Request:**
   - **POST** `/api/live/join-request`
   - **Body:** `{"live_stream_id": 1}`
   - **Response (200 OK):**
   ```json
   {
     "status": true,
     "success": true,
     "message": "Co-host request sent to host.",
     "data": {
       "request_id": 12,
       "live_stream_id": 1,
       "status": "pending"
     }
   }
   ```

2. **Host Responds to Request (Accept / Reject):**
   - **POST** `/api/live/accept-request`
   - **Body:** `{"request_id": 12, "action": "accept"}`
   - **Response (200 OK):**
   ```json
   {
     "status": true,
     "success": true,
     "message": "Co-host request accepted successfully.",
     "data": {
       "request_id": 12,
       "live_stream_id": 1,
       "guest_user_id": 105,
       "status": "accepted",
       "action": "accept",
       "guest_session": {
         "driver": "agora",
         "channel_name": "live_2_1726300000_abcd",
         "role": "publisher",
         "agora": {
           "app_id": "YOUR_AGORA_APP_ID",
           "token": "007eJxTY_GUEST_BROADCASTER_TOKEN",
           "uid": 105,
           "role": 1
         }
       }
     }
   }
   ```

3. **Host Kicks Guest from Video Grid:**
   - **POST** `/api/live/kick-guest`
   - **Body:** `{"live_stream_id": 1, "guest_user_id": 105}`
   - **Response (200 OK):**
   ```json
   {
     "status": true,
     "success": true,
     "message": "Guest kicked from live co-hosting.",
     "data": {
       "live_stream_id": 1,
       "guest_user_id": 105,
       "reason": "host_removed"
     }
   }
   ```

---

### 📞 1-on-1 Audio / Video Calling APIs

#### 1. Check Caller Permission & Target Host Availability
- **Endpoint:** `POST /api/call/check-permission`
- **Request Body:** `{"receiver_id": 2, "call_type": "video"}`
- **Response (Available Host):**
```json
{
  "status": true,
  "can_call": true,
  "code": "CALL_ALLOWED",
  "message": "User has permission and balance to make a call.",
  "user_balance": 1500,
  "rate_per_minute": 100,
  "is_free_trial": false
}
```

#### 2. Initiate Call (With Offline / Busy Defense)
- **Endpoint:** `POST /api/call/initiate`
- **Request Body:** `{"receiver_id": 2, "call_type": "video"}`
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
- **Response (If Host is in Another Call or Live - 400 Bad Request):**
```json
{
  "status": false,
  "can_call": false,
  "code": "USER_BUSY",
  "is_busy": true,
  "message": "Nusrat Jahan is currently busy in another call or live broadcast."
}
```

---

#### 3. Send In-Call Live Chat Message
- **Endpoint:** `POST /api/call/chat/send`
- **Request Body:**
```json
{
  "call_session_id": "15",
  "receiver_id": 2,
  "type": "text",
  "message": "Hi, love your smile! ❤️"
}
```
- **Response (200 OK):**
```json
{
  "status": true,
  "success": true,
  "message": "Message sent successfully during video call.",
  "data": {
    "id": 50,
    "call_id": 15,
    "call_session_id": "15",
    "channel_name": "call_video_1_2_1726300000_abcd",
    "sender_id": 1,
    "sender_name": "John",
    "receiver_id": 2,
    "type": "text",
    "message": "Hi, love your smile! ❤️",
    "created_at": "2026-09-14T08:20:00+06:00"
  }
}
```

---

## 7. WebSocket Channels & Real-Time Events Reference

| Event Class | Broadcast Channel | Description |
|:---|:---|:---|
| `InCallMessageSent` | `presence-call.{call_id}`, `call.{call_id}` | Live text/image message sent during 1-on-1 video call. |
| `CallMessageSent` | `call.{call_id}`, `user.{receiver_id}` | Direct user notification channel for in-call messaging. |
| `LiveMessageSent` | `presence-live.{live_id}`, `live.{live_id}` | Public viewer comment sent in live broadcast. |
| `LiveGiftSent` | `presence-live.{live_id}`, `live.{live_id}` | Virtual gift animation trigger in live stream. |
| `LiveJoinRequested` | `presence-live.{live_id}`, `user.{host_id}` | Viewer requests host to co-host on video grid. |
| `LiveJoinResponded` | `presence-live.{live_id}`, `user.{guest_id}`| Host accepts or rejects co-hosting request. |
| `LiveGuestKicked` | `presence-live.{live_id}`, `user.{guest_id}`| Host kicks guest off the live broadcast. |
| `LiveStreamEnded` | `presence-live.{live_id}`, `live.{live_id}` | Broadcast ended notification with final statistics. |

---

## 8. Database Schema & Migrations Reference

### `users` Table Extensions:
```sql
ALTER TABLE users 
  ADD COLUMN online_status VARCHAR(30) DEFAULT 'available',
  ADD COLUMN current_status VARCHAR(30) DEFAULT 'available',
  ADD COLUMN is_online BOOLEAN DEFAULT FALSE,
  ADD COLUMN last_seen_at TIMESTAMP NULL;
```

### `live_streams` Table:
```sql
CREATE TABLE live_streams (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  host_id BIGINT UNSIGNED NOT NULL,
  channel_name VARCHAR(150) UNIQUE NOT NULL,
  title VARCHAR(200) NULL,
  cover_image VARCHAR(500) NULL,
  status VARCHAR(30) DEFAULT 'live',
  viewer_count INT UNSIGNED DEFAULT 0,
  total_diamonds_earned INT UNSIGNED DEFAULT 0,
  agora_token TEXT NULL,
  started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  ended_at TIMESTAMP NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (host_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### `live_participants` Table:
```sql
CREATE TABLE live_participants (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  live_stream_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  role VARCHAR(30) DEFAULT 'viewer', -- 'host', 'guest', 'viewer'
  is_muted BOOLEAN DEFAULT FALSE,
  video_enabled BOOLEAN DEFAULT TRUE,
  joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  left_at TIMESTAMP NULL,
  FOREIGN KEY (live_stream_id) REFERENCES live_streams(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

---

## 9. Flutter Integration Guides & Architecture Snippets

### 1. Multi-Viewer Live Stream Client (`LiveRoomController.dart`):
```dart
import 'dart:convert';
import 'package:agora_rtc_engine/agora_rtc_engine.dart';
import 'package:laravel_flutter_pusher/laravel_flutter_pusher.dart';

class LiveRoomController {
  final int liveStreamId;
  final String channelName;
  final String agoraAppId;
  final String rtcToken;
  final int currentUserId;
  final bool isHost;

  late RtcEngine rtcEngine;

  LiveRoomController({
    required this.liveStreamId,
    required this.channelName,
    required this.agoraAppId,
    required this.rtcToken,
    required this.currentUserId,
    this.isHost = false,
  });

  Future<void> initEngine() async {
    rtcEngine = createAgoraRtcEngine();
    await rtcEngine.initialize(RtcEngineContext(appId: agoraAppId));
    
    await rtcEngine.enableVideo();
    await rtcEngine.setClientRole(
      role: isHost ? ClientRoleType.clientRoleBroadcaster : ClientRoleType.clientRoleAudience
    );

    // Subscribe to Laravel Reverb WebSocket Presence Channel
    final channel = PusherClient.subscribe('presence-live.$liveStreamId');
    
    // Listen for Public Chat
    channel.bind('LiveMessageSent', (event) {
      final msg = jsonDecode(event.data);
      // Update UI chat list
    });

    // Listen for Gifts
    channel.bind('LiveGiftSent', (event) {
      final gift = jsonDecode(event.data);
      // Play SVGA Animation
    });

    // Listen for Co-Host Join Response (For Guest)
    channel.bind('LiveJoinResponded', (event) async {
      final data = jsonDecode(event.data);
      if (data['guest_user_id'] == currentUserId && data['action'] == 'accept') {
        // Upgrade Role to Broadcaster on the fly!
        await rtcEngine.setClientRole(role: ClientRoleType.clientRoleBroadcaster);
      }
    });

    // Join RTC Channel
    await rtcEngine.joinChannel(
      token: rtcToken,
      channelId: channelName,
      uid: currentUserId,
      options: ChannelMediaOptions(
        publishCameraTrack: isHost,
        publishMicrophoneTrack: isHost,
        autoSubscribeAudio: true,
        autoSubscribeVideo: true,
      ),
    );
  }
}
```

---
*Chinchins Live Technical Specification — Confidential & Proprietary*
