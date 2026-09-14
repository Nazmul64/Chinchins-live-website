# 📱 Chinchins Live — RESTful API & Bug Fixing Documentation
**System:** Laravel 11.x RESTful Backend & Flutter Mobile Client (Android & iOS)  
**Version:** 2.5.0  
**Updated:** September 2026  
**Document Code:** `CHINCHINS-REST-DOC-2026`

---

## 📑 Table of Contents
1. [Executive Summary & Resolved Issues](#1-executive-summary--resolved-issues)
2. [In-Call Real-Time Messaging & Image Sharing Architecture](#2-in-call-real-time-messaging--image-sharing-architecture)
3. [Admin Remote Feature Switches & Instant Sync Engine](#3-admin-remote-feature-switches--instant-sync-engine)
4. [Back Button Minimization (In-App PiP Window) Handling](#4-back-button-minimization-in-app-pip-window-handling)
5. [User Online Presence & Calling Verification Engine](#5-user-online-presence--calling-verification-engine)
6. [App Hanging / Freezing Prevention & Zero-Lag Calling (<500ms Connect)](#6-app-hanging--freezing-prevention--zero-lag-calling-500ms-connect)
7. [Complete RESTful API Reference & Payloads](#7-complete-restful-api-reference--payloads)
8. [Flutter Integration Code & Controllers](#8-flutter-integration-code--controllers)
9. [Database Schema & Migration Structure](#9-database-schema--migration-structure)

---

## 1. Executive Summary & Resolved Issues

### 🛠️ Issues Addressed & Fixed:
1. **In-Call Real-Time Text Messaging & Image Delivery:**
   - Fixed two-way communication between caller and host during active video calls.
   - Built dual-channel delivery: **Laravel Reverb WebSocket (`call.{sessionId}`, `call_chat.{sessionId}`, `chat.{receiverId}`)** + **WebRTC Signaling Relay (`CallSignal` type `chat_message`)** + **Database Storage (`CallMessage` and `ChatMessage`)**.
   - Added live image upload endpoints storing directly into `public/uploads/live_chat` and `public/uploads/live_streaming`.

2. **Admin Remote Feature Switches (Debug HUD, Security & Filters):**
   - Fixed switch toggling synchronization from Admin Panel (`/admin/settings/debug`) to Flutter mobile clients.
   - All flags (`debug_mode_enabled`, `screenshot_protection_enabled`, `screen_recording_protection_enabled`, `camera_filters_enabled`, `call_minimize_enabled`) now return strict boolean values in `GET /api/app/remote-config` and `GET /api/call/config`.
   - Dynamic cache eviction ensures instant updates across mobile apps without requiring an APK rebuild.

3. **Back Button Call Disconnection Fix (PiP Mode):**
   - Pressing back button or minimizing to floating PiP overlay keeps call session active (`connected`).
   - Dedicated REST synchronization endpoints `POST /api/call/minimize` and `POST /api/call/restore`.

4. **Offline User Call Ringing / Hanging Fix:**
   - Corrected `User::getIsOnlineAttribute()` to strictly inspect `online_status` and heartbeat validity (`subMinutes(5)`) instead of returning true for all active users.
   - When viewing an offline user profile via `POST /api/profile/{id}/view`, `auto_call_triggered` is strictly `false` and `trigger_action` is `'NONE'`, completely stopping ghost calls.

---

## 2. In-Call Real-Time Messaging & Image Sharing Architecture

```
┌────────────────────────────────────────────────────────────────────────┐
│                        FLUTTER CALL SCREEN                             │
│                                                                        │
│   [ Live Video Preview ] ────────► User sends Text or Photo           │
│                                           │                            │
│                                           ▼                            │
│                           POST /api/call/chat/send                     │
└───────────────────────────────────────────┬────────────────────────────┘
                                            │
                                            ▼
┌────────────────────────────────────────────────────────────────────────┐
│                         LARAVEL BACKEND                                │
│                                                                        │
│   1. Store in `call_messages` table (Call Message History)            │
│   2. Store in `chat_messages` table (Inbox / Chat Sync)                │
│   3. Insert into `call_signals` table (WebRTC Polling Fallback)        │
│   4. Broadcast `CallMessageSent` via Reverb WebSocket (< 50ms)         │
└───────────────────┬───────────────────────────────────┬────────────────┘
                    │                                   │
                    ▼                                   ▼
        WebSocket Channel:                  Signaling / REST Polling:
        • call.{sessionId}                  • GET /api/call/signals
        • call_chat.{sessionId}             • GET /api/call/{id}/messages
        • user.{receiverId}
```

---

## 3. Admin Remote Feature Switches & Instant Sync Engine

Admin URL: `/admin/settings/debug`  
API Configuration: `GET /api/app/remote-config` & `GET /api/call/config`

| Switch Key | Description | Flutter Action |
|:---|:---|:---|
| `debug_mode_enabled` | Real-time FPS, Bitrate & Latency HUD Overlay | If `false`, hides HUD overlay completely. |
| `screenshot_protection_enabled` | `FLAG_SECURE` screen protection | If `true`, enables Android `FLAG_SECURE` and iOS blur shield. |
| `screen_recording_protection_enabled` | Screen Recording Defense | Blocks screen recorder apps from capturing live video stream. |
| `camera_filters_enabled` | TikTok-style real-time beauty shaders & LUTs | If `true`, enables filter selector bar for video calls. |
| `call_minimize_enabled` | Back button floating PiP overlay | If `true`, back button minimizes call to draggable PIP window. |

---

## 4. Back Button Minimization (In-App PiP Window) Handling

- **Android Back Button:** Intercepted with `PopScope` or `WillPopScope`. Call session remains `connected`.
- **In-App Top-Left Back Arrow:** Shrinks call screen into a floating draggable overlay (`OverlayEntry`) while audio and video WebRTC tracks remain active.
- **REST Endpoints:**
  - `POST /api/call/minimize`: Syncs minimized status.
  - `POST /api/call/restore`: Restores call to full screen.
  - `POST /api/call/end`: Hangs up and ends billing.

---

## 5. User Online Presence & Calling Verification Engine

### Heartbeat & Status Lifecycle:
1. Every 30 seconds, Flutter sends `POST /api/user/heartbeat`.
2. A user is `is_online = true` **ONLY IF**:
   - `is_active = true` AND `!is_locked`.
   - `online_status` is `'online'`, `'busy'`, or `'in_call'`.
   - Last heartbeat received within last 5 minutes (`last_seen_at >= now()->subMinutes(5)`).
3. If user is offline, `auto_call_triggered` on profile view is `false`.

---

## 6. App Hanging / Freezing Prevention & Zero-Lag Calling (<500ms Connect)

1. **Trickle ICE Gathering:** Send SDP Offer/Answer immediately without waiting for gathering.
2. **Camera Pre-Warming:** Initialize camera preview during outgoing/incoming ringing screen so it connects with 0ms delay.
3. **RepaintBoundary Isolates:** Video rendering happens inside hardware texture without invalidating Flutter UI tree.
4. **Cached ICE Servers:** STUN/TURN servers fetched once at startup via `GET /api/call/ice-servers`.

---

## 7. Complete RESTful API Reference & Payloads

### 📡 1. App Remote Configuration
- **URL:** `GET /api/app/remote-config` (or `GET /api/app/config`)
- **Headers:** `Accept: application/json`
- **Response (200 OK):**
```json
{
  "status": true,
  "data": {
    "app_name": "Chinchins Live",
    "app_tagline": "Meet, Chat & Video Call Live",
    "app_logo_url": "https://chinchins.live/uploads/app/logo.png",
    "app_icon_url": "https://chinchins.live/uploads/app/icon.png",
    "latest_version": "1.0.0",
    "free_messages_limit": 5,
    "message_coin_cost": 5,
    "video_call_rate": 100,
    "audio_call_rate": 60,
    "free_trial_duration": 16,
    "incoming_ringtone": "https://assets.mixkit.co/active_storage/sfx/2874/2874-preview.mp3",
    "outgoing_ringtone": "https://assets.mixkit.co/active_storage/sfx/1359/1359-preview.mp3",
    "screenshot_protection_enabled": true,
    "screen_recording_protection_enabled": true,
    "camera_filters_enabled": true,
    "call_minimize_enabled": true,
    "debug_mode_enabled": false,
    "debug_logs_enabled": false,
    "remote_flags": {
      "screenshot_protection_enabled": true,
      "screen_recording_protection_enabled": true,
      "camera_filters_enabled": true,
      "call_minimize_enabled": true,
      "debug_mode_enabled": false,
      "debug_logs_enabled": false
    }
  }
}
```

---

### 💬 2. Send In-Call Live Chat Message
- **URL:** `POST /api/call/chat/send` (or `POST /api/call/send-message`)
- **Headers:** `Authorization: Bearer {token}`, `Content-Type: application/json`
- **Request Body (Text):**
```json
{
  "call_session_id": "15",
  "receiver_id": 2,
  "type": "text",
  "message": "Hi, you look amazing! ❤️"
}
```
- **Request Body (Image Message):**
```json
{
  "call_session_id": "15",
  "receiver_id": 2,
  "type": "image",
  "image_url": "https://chinchins.live/uploads/live_chat/chat_1710000000_abc123.jpg"
}
```
- **Response (200 OK):**
```json
{
  "status": true,
  "success": true,
  "message": "Message sent successfully during video call.",
  "data": {
    "id": 42,
    "call_id": 15,
    "call_session_id": "15",
    "channel_name": "call_video_1_2_1710000000_xyzt",
    "sender_id": 1,
    "sender_name": "John Doe",
    "sender_avatar": "https://chinchins.live/uploads/avatars/user1.jpg",
    "receiver_id": 2,
    "type": "text",
    "message": "Hi, you look amazing! ❤️",
    "image_url": null,
    "created_at": "2026-09-14T07:50:00+06:00"
  }
}
```

---

### 🖼️ 3. Upload Live In-Call / Stream Image
- **URL:** `POST /api/call/upload-image` (or `POST /api/call/chat/upload-image`)
- **Headers:** `Authorization: Bearer {token}`, `Content-Type: multipart/form-data`
- **Form-Data:**
  - `image`: *(binary file)*
  - `folder`: `live_chat` (or `live_streaming`)
- **Response (200 OK):**
```json
{
  "status": true,
  "success": true,
  "message": "Image uploaded successfully.",
  "data": {
    "image_url": "https://chinchins.live/uploads/live_chat/chat_1710000000_xyz890.jpg",
    "file_url": "https://chinchins.live/uploads/live_chat/chat_1710000000_xyz890.jpg",
    "relative_path": "uploads/live_chat/chat_1710000000_xyz890.jpg",
    "folder": "live_chat",
    "filename": "chat_1710000000_xyz890.jpg"
  }
}
```

---

### 📜 4. Get In-Call Messages History
- **URL:** `GET /api/call/{callId}/messages` (or `GET /api/call/chat/messages?call_session_id=15`)
- **Headers:** `Authorization: Bearer {token}`
- **Response (200 OK):**
```json
{
  "status": true,
  "success": true,
  "data": {
    "total": 2,
    "messages": [
      {
        "id": 40,
        "call_session_id": "15",
        "sender_id": 1,
        "sender_name": "John Doe",
        "sender_avatar": "https://chinchins.live/uploads/avatars/user1.jpg",
        "receiver_id": 2,
        "type": "text",
        "message": "Hi babe!",
        "image_url": null,
        "created_at": "2026-09-14T07:49:00+06:00"
      },
      {
        "id": 41,
        "call_session_id": "15",
        "sender_id": 2,
        "sender_name": "Nusrat Jahan",
        "sender_avatar": "https://chinchins.live/uploads/avatars/host2.jpg",
        "receiver_id": 1,
        "type": "text",
        "message": "Hello handsome! How are you?",
        "image_url": null,
        "created_at": "2026-09-14T07:49:15+06:00"
      }
    ]
  }
}
```

---

### 🪟 5. Video Call Minimization & Restore (PiP)
- **Minimize Call:** `POST /api/call/minimize`
  - **Payload:** `{"call_session_id": "15"}`
  - **Response:**
```json
{
  "status": true,
  "success": true,
  "message": "Call state minimized. Call session remains active and ongoing.",
  "data": {
    "call_session_id": "15",
    "is_minimized": true,
    "is_active": true
  }
}
```
- **Restore Call:** `POST /api/call/restore`
  - **Payload:** `{"call_session_id": "15"}`
  - **Response:**
```json
{
  "status": true,
  "success": true,
  "message": "Call restored to full-screen mode.",
  "data": {
    "call_session_id": "15",
    "is_minimized": false,
    "is_active": true
  }
}
```

---

### 👁️ 6. Profile View & Online Presence Check
- **URL:** `POST /api/profile/{id}/view`
- **Headers:** `Authorization: Bearer {token}`
- **Response (Online Host):**
```json
{
  "status": true,
  "message": "Profile view recorded. Auto-callback notification triggered.",
  "data": {
    "host": {
      "id": 2,
      "account_id": "90218492",
      "display_name": "Nusrat Jahan",
      "avatar_url": "https://chinchins.live/uploads/avatars/host2.jpg",
      "is_online": true,
      "is_busy": false,
      "is_available": true,
      "video_call_rate": 100
    },
    "callback": {
      "auto_call_triggered": true,
      "host_is_available": true,
      "trigger_action": "INCOMING_CALL"
    }
  }
}
```
- **Response (Offline Host):**
```json
{
  "status": true,
  "message": "Profile view recorded. Auto-callback notification triggered.",
  "data": {
    "host": {
      "id": 5,
      "account_id": "84729103",
      "display_name": "Sarah Khan",
      "avatar_url": "https://chinchins.live/uploads/avatars/host5.jpg",
      "is_online": false,
      "is_busy": false,
      "is_available": false,
      "video_call_rate": 100
    },
    "callback": {
      "auto_call_triggered": false,
      "host_is_available": false,
      "trigger_action": "NONE"
    }
  }
}
```

---

## 8. Flutter Integration Code & Controllers

### 1. In-Call Chat Service (`CallChatService.dart`):
```dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:laravel_flutter_pusher/laravel_flutter_pusher.dart';

class CallChatService {
  final String baseUrl = 'https://chinchins.live/api';
  final String token;
  final String callSessionId;
  final Function(Map<String, dynamic>) onNewMessage;

  CallChatService({
    required this.token,
    required this.callSessionId,
    required this.onNewMessage,
  });

  void subscribeToCallChat() {
    // 1. Listen via Laravel Reverb WebSocket
    PusherClient.subscribe('call.$callSessionId').bind('call.message.sent', (event) {
      if (event != null && event.data != null) {
        final data = jsonDecode(event.data);
        onNewMessage(data);
      }
    });
  }

  Future<bool> sendTextMessage({required int receiverId, required String text}) async {
    final response = await http.post(
      Uri.parse('$baseUrl/call/chat/send'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode({
        'call_session_id': callSessionId,
        'receiver_id': receiverId,
        'type': 'text',
        'message': text,
      }),
    );
    return response.statusCode == 200;
  }
}
```

### 2. Back Button PiP Protection (`CallScreen.dart`):
```dart
@override
Widget build(BuildContext context) {
  return PopScope(
    canPop: false,
    onPopInvokedWithResult: (didPop, result) {
      if (!didPop) {
        // Do NOT end call — Minimize to floating PiP overlay
        FloatingCallOverlay.show(context, callSession: currentCallSession);
      }
    },
    child: Scaffold(
      body: VideoCallContent(),
    ),
  );
}
```

---

## 9. Database Schema & Migration Structure

### `call_messages` Table:
| Column | Type | Description |
|:---|:---|:---|
| `id` | `BIGINT UNSIGNED AUTO_INCREMENT` | Primary Key |
| `call_session_id` | `VARCHAR(100)` | Call Session or Channel Name |
| `sender_id` | `BIGINT UNSIGNED` | Foreign key to `users.id` |
| `receiver_id` | `BIGINT UNSIGNED` | Foreign key to `users.id` |
| `type` | `VARCHAR(20)` | `text`, `image`, `emoji`, `gift` |
| `message` | `TEXT` | Message text content |
| `image_url` | `VARCHAR(500)` | Uploaded image path |
| `metadata` | `JSON` | Sender name, avatar, timestamp |
| `is_read` | `BOOLEAN` | Read status |
| `created_at` | `TIMESTAMP` | Created timestamp |

---
*Chinchins Live Engineering Team — Confidential & Proprietary*
