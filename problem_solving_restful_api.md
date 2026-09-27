# 🚀 Chinchins Live — Problem Solving RESTful API & WebSocket Engine Specification

> **Version:** 2.0.0 Enterprise  
> **Status:** Production Ready  
> **Base URL:** `https://chinchins.live/api` or `http://127.0.0.1:8000/api`  
> **WebSocket Engine:** Laravel Reverb / Pusher WebSockets (`wss://chinchins.live`)  
> **Video/Audio Engine:** LiveKit Cloud/VPS WebRTC Engine  

---

## 📑 Table of Contents
1. [Overview & Solved Problem Statements](#1-overview--solved-problem-statements)
2. [Call Disconnect Status Clearing & Sync (`POST /api/call/end`)](#2-call-disconnect-status-clearing--sync)
3. [Live Host Incoming Call Signal Push (`POST /api/call/make-call` & `/api/call/instant`)](#3-live-host-incoming-call-signal-push)
4. [Real-Time Live Status in User Profile (`GET /api/user/profile/{id}`)](#4-real-time-live-status-in-user-profile)
5. [Auto-Greetings Engine on Profile Visit (`POST /api/user/profile-visit`)](#5-auto-greetings-engine-on-profile-visit)
6. [Call History & Logs API (`GET /api/calls/history`)](#6-call-history--logs-api)
7. [Conversation Chat List API with Zero Dummy Data (`GET /api/chat/conversations`)](#7-conversation-chat-list-api)
8. [WebSocket Channels & Events Reference](#8-websocket-channels--events-reference)

---

## 1. Overview & Solved Problem Statements

| Problem Identified | Root Cause | Solution Implemented |
| :--- | :--- | :--- |
| **Call Disconnect Re-dial Loop** | Server kept call in intermediate state without broadcasting status `completed` to both parties. | `POST /api/call/end` immediately marks status as `completed`, clears caller/receiver busy state, and broadcasts `CallEndedEvent` & `CallEnded` to both `private-user.{id}` channels. |
| **Live Host Missed Calls** | Live streamers' incoming private call socket signals were blocked when streaming. | Socket events are never blocked during live streaming; high-priority `IncomingCallEvent` is pushed to `private-user.{host_id}`. |
| **Live Badge Disappearing on Client** | Profile API only checked DB rather than in-memory real-time Redis/Cache. | `GET /api/user/profile/{id}` checks in-memory Redis cache + active streams to return stable `is_live: true/false`. |
| **Missing Auto-Greeting on Profile Visit** | No automated visitor greeting engine existed. | `POST /api/user/profile-visit` auto inserts greeting from host to visitor if none sent in last 24h and broadcasts `NewMessageEvent`. |
| **Dummy Conversation Inboxes** | Conversation API returned fallback mock streamers when empty. | `GET /api/chat/conversations` strictly groups authentic user conversations with unread count and latest messages without mock data. |

---

## 2. Call Disconnect Status Clearing & Sync

### Endpoint: `POST /api/call/end`
Also available on: `POST /api/calls/{call}/end`

When a user or host terminates a call, this API updates the call status to `completed`, releases user busy flags, clears Redis cache, and broadcasts `CallEndedEvent` to both users.

#### Request Headers:
```http
Authorization: Bearer {user_access_token}
Content-Type: application/json
Accept: application/json
```

#### Request Body:
```json
{
  "call_id": 1054,
  "channel_name": "call_video_101_202_1727421100",
  "duration_seconds": 125
}
```

#### Success Response (`200 OK`):
```json
{
  "status": true,
  "success": true,
  "call_status": "completed",
  "message": "Call session ended and marked completed successfully. Real-time disconnect broadcasted.",
  "data": {
    "call_id": 1054,
    "call_type": "video",
    "duration_seconds": 125,
    "duration_formatted": "02:05",
    "coins_deducted": 200,
    "host_earned_coins": 100,
    "admin_revenue_coins": 100,
    "caller_remaining_coins": 1450,
    "partner": {
      "id": 202,
      "account_id": "84729103",
      "name": "Nusrat Jahan",
      "avatar_url": "https://chinchins.live/storage/avatars/host_202.jpg",
      "gender": "female",
      "country": "Bangladesh"
    }
  }
}
```

#### WebSocket Broadcast (`private-user.{caller_id}`, `private-user.{receiver_id}`):
```json
{
  "event": "CallEndedEvent",
  "action": "call_ended",
  "call_id": 1054,
  "channel_name": "call_video_101_202_1727421100",
  "status": "completed",
  "call_status": "completed",
  "ended_by": 101,
  "duration_seconds": 125,
  "timestamp": "2026-09-27T15:00:00+06:00"
}
```

---

## 3. Live Host Incoming Call Signal Push

### Endpoints:
- `POST /api/call/make-call`
- `POST /api/call/instant`
- `POST /api/call/initiate`

Ensures that even if the host is actively broadcasting in a live room, incoming private 1-on-1 audio/video call invitations are pushed immediately without backend blockage.

#### Request Body:
```json
{
  "receiver_id": 202,
  "call_type": "video"
}
```

#### Success Response (`200 OK`):
```json
{
  "success": true,
  "status": true,
  "message": "Call initiated! Ringing receiver...",
  "channel_name": "call_video_101_202_1727421100",
  "call_id": 1054,
  "call_type": "video",
  "token": "eyJhbGciOiJIUzI1NiIsIn...",
  "livekit_url": "wss://chinchins.live/livekit",
  "caller": {
    "id": 101,
    "account_id": "39102847",
    "display_name": "Rahim Ahmed",
    "avatar_url": "https://chinchins.live/storage/avatars/user_101.jpg",
    "level": "Lv.5"
  },
  "receiver": {
    "id": 202,
    "account_id": "84729103",
    "display_name": "Nusrat Jahan",
    "avatar_url": "https://chinchins.live/storage/avatars/host_202.jpg"
  }
}
```

#### WebSocket Broadcast on `private-user.{host_id}`:
```json
{
  "event": "IncomingCallEvent",
  "id": 1054,
  "call_id": 1054,
  "channel_name": "call_video_101_202_1727421100",
  "call_type": "video",
  "token": "eyJhbGciOiJIUzI1NiIsIn...",
  "livekit_url": "wss://chinchins.live/livekit",
  "status": "ringing",
  "caller": {
    "id": 101,
    "account_id": "39102847",
    "display_name": "Rahim Ahmed",
    "avatar_url": "https://chinchins.live/storage/avatars/user_101.jpg",
    "level": "Lv.5",
    "gender": "male"
  },
  "timestamp": "2026-09-27T15:00:00+06:00"
}
```

---

## 4. Real-Time Live Status in User Profile

### Endpoints:
- `GET /api/user/profile/{id}`
- `GET /api/profile/{id}`

Retrieves user profile data with real-time Redis in-memory `is_live: true/false` status so mobile clients never experience flickering or disappearing live badges.

#### Success Response (`200 OK`):
```json
{
  "status": true,
  "success": true,
  "is_live": true,
  "data": {
    "user": {
      "id": 202,
      "account_id": "84729103",
      "display_name": "Nusrat Jahan",
      "avatar_url": "https://chinchins.live/storage/avatars/host_202.jpg",
      "gender": "female",
      "is_live": true,
      "online_status": "in_live",
      "coins": 450,
      "diamonds": 12800,
      "video_call_rate": 100
    },
    "is_live": true,
    "online_status": "in_live",
    "status_text": "🔴 In Live Streaming",
    "live_badge": {
      "label": "Live",
      "type": "live",
      "is_live": true,
      "is_online": true,
      "sound_wave_animation": true,
      "equalizer_bars_count": 3,
      "badge_style": "purple_gradient",
      "gradient_colors": ["#A855F7", "#EC4899"],
      "dot_color": "#FFFFFF",
      "has_dot": false
    },
    "action_button": {
      "type": "join_live",
      "icon": "video_camera",
      "is_live": true,
      "is_animating": true,
      "animation_type": "pulsing_ripple",
      "gradient_colors": ["#8B5CF6", "#EC4899"],
      "shape": "notched_floating_circle"
    },
    "can_call": false,
    "call_button_mode": "watch_live",
    "call_button_text": "Watch Live",
    "live_stream": {
      "id": 88,
      "room_id": "88",
      "channel_name": "live_host_202_1727420000",
      "title": "Welcome to Nusrat's Live Room!",
      "viewer_count": 24,
      "likes_count": 120,
      "status": "live"
    }
  }
}
```

---

## 5. Auto-Greetings Engine on Profile Visit

### Endpoints:
- `POST /api/user/profile-visit`
- `POST /api/profile-visit`

When a user visits a host's profile, this API checks if an auto-greeting has already been sent within the last 24 hours. If not, it creates a new greeting message in the database and broadcasts `NewMessageEvent` in real time to the visitor.

#### Request Body:
```json
{
  "host_id": 202
}
```

#### Success Response (`200 OK`):
```json
{
  "success": true,
  "status": true,
  "message": "Profile visit tracked successfully.",
  "auto_greeting": {
    "id": 4512,
    "sender_id": 202,
    "receiver_id": 101,
    "message": "Hi baby, how are you? আমি ফ্রি আছি, তুমি কি আমার সাথে কথা বলতে চাও?",
    "created_at": "2026-09-27T15:00:00+06:00"
  }
}
```

#### WebSocket Broadcast on `private-user.{visitor_id}` / `private-chat.{visitor_id}`:
```json
{
  "event": "NewMessageEvent",
  "id": 4512,
  "sender_id": 202,
  "receiver_id": 101,
  "message": "Hi baby, how are you? আমি ফ্রি আছি, তুমি কি আমার সাথে কথা বলতে চাও?",
  "type": "text",
  "is_read": false,
  "sender": {
    "id": 202,
    "account_id": "84729103",
    "name": "Nusrat Jahan",
    "display_name": "Nusrat Jahan",
    "avatar_url": "https://chinchins.live/storage/avatars/host_202.jpg",
    "level": "Lv.8"
  },
  "created_at": "2026-09-27T15:00:00+06:00",
  "timestamp": "2026-09-27T15:00:00+06:00"
}
```

---

## 6. Call History & Logs API

### Endpoints:
- `GET /api/calls/history`
- `GET /api/call/history`

Returns complete incoming and outgoing call records for the authenticated user, formatted with partner metadata, duration, call type, and coins.

#### Request Headers:
```http
Authorization: Bearer {user_access_token}
```

#### Success Response (`200 OK`):
```json
{
  "status": true,
  "success": true,
  "message": "Call history retrieved successfully.",
  "data": [
    {
      "id": 1054,
      "call_session_id": "1054",
      "channel_name": "call_video_101_202_1727421100",
      "call_type": "video",
      "call_type_label": "[Video]",
      "is_caller": true,
      "is_outgoing": true,
      "direction": "outgoing",
      "status": "completed",
      "status_label": "Completed",
      "duration_seconds": 125,
      "formatted_duration": "02:05",
      "duration_formatted": "02:05",
      "is_free_trial": false,
      "coins_spent": 200,
      "coins_earned": 0,
      "created_at": "2026-09-27T14:35:00+06:00",
      "formatted_date": "2026/09/27 14:35",
      "time_ago": "25 minutes ago",
      "partner": {
        "id": 202,
        "account_id": "84729103",
        "name": "Nusrat Jahan",
        "display_name": "Nusrat Jahan",
        "avatar_url": "https://chinchins.live/storage/avatars/host_202.jpg",
        "gender": "female",
        "level": "Lv.8",
        "level_number": 8,
        "is_online": true,
        "video_rate": 100
      }
    }
  ],
  "current_coins": 1450,
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "total": 1
  }
}
```

---

## 7. Conversation Chat List API

### Endpoints:
- `GET /api/chat/conversations`
- `GET /api/messages/conversations`
- `GET /api/messages`

Aggregates real-time peer-to-peer conversations grouped by contact. Zero dummy records are returned if the user has not chatted.

#### Request Headers:
```http
Authorization: Bearer {user_access_token}
```

#### Success Response (`200 OK`):
```json
{
  "status": true,
  "success": true,
  "message": "Conversations loaded successfully.",
  "data": {
    "total_unread_badge": 1,
    "free_messages_limit": 5,
    "free_messages_remaining": 4,
    "user_coins": 1450,
    "conversations": [
      {
        "user_id": 202,
        "account_id": "84729103",
        "name": "Nusrat Jahan",
        "display_name": "Nusrat Jahan",
        "avatar_url": "https://chinchins.live/storage/avatars/host_202.jpg",
        "is_online": true,
        "is_busy": false,
        "unread_count": 1,
        "user": {
          "id": 202,
          "account_id": "84729103",
          "name": "Nusrat Jahan",
          "display_name": "Nusrat Jahan",
          "avatar_url": "https://chinchins.live/storage/avatars/host_202.jpg",
          "gender": "female",
          "level": "Lv.8",
          "is_online": true,
          "is_busy": false
        },
        "partner": {
          "id": 202,
          "account_id": "84729103",
          "name": "Nusrat Jahan",
          "display_name": "Nusrat Jahan",
          "avatar_url": "https://chinchins.live/storage/avatars/host_202.jpg",
          "gender": "female",
          "level": "Lv.8",
          "is_online": true,
          "is_busy": false
        },
        "last_message": {
          "text": "Hi baby, how are you? আমি ফ্রি আছি, তুমি কি আমার সাথে কথা বলতে চাও?",
          "message": "Hi baby, how are you? আমি ফ্রি আছি, তুমি কি আমার সাথে কথা বলতে চাও?",
          "type": "text",
          "time": "Just now",
          "time_formatted": "Just now",
          "time_ago": "Just now",
          "minutes_ago": 0,
          "media_url": null,
          "created_at": "2026-09-27T15:00:00+06:00"
        },
        "last_message_at": "2026-09-27T15:00:00+06:00",
        "video_call_rate": 100
      }
    ]
  },
  "conversations": [
    {
      "conversation_id": 202,
      "user_id": 202,
      "user": {
        "id": 202,
        "account_id": "84729103",
        "name": "Nusrat Jahan",
        "avatar_url": "https://chinchins.live/storage/avatars/host_202.jpg"
      },
      "unread_count": 1,
      "last_message": "Hi baby, how are you? আমি ফ্রি আছি, তুমি কি আমার সাথে কথা বলতে চাও?",
      "last_message_at": "2026-09-27T15:00:00+06:00"
    }
  ]
}
```

---

## 8. WebSocket Channels & Events Reference

| Event Class | Channel | Event Name | Payload Highlights |
| :--- | :--- | :--- | :--- |
| `CallEndedEvent` | `private-user.{id}` / `call.{name}` | `CallEndedEvent` / `call.ended` | `call_id`, `status: "completed"`, `duration_seconds` |
| `IncomingCallEvent` | `private-user.{id}` / `user.{id}` | `IncomingCallEvent` / `incoming_call` | `call_id`, `channel_name`, `token`, `caller` details |
| `NewMessageEvent` | `private-user.{id}` / `chat.{id}` | `NewMessageEvent` / `message.sent` | `id`, `sender_id`, `message`, `sender` details |

---

## 9. Call Initiation & `calls` Table Synchronous Insertion

### Endpoint:
- `POST /api/call/initiate`
- `POST /api/call/instant`
- `POST /api/calls/make`

When a call is initiated:
1. Validates `receiver_id` (or `user_id`/`host_id`) and `call_type` (`video`/`audio`).
2. Synchronously creates and inserts a record in both the `calls` table and the `call_sessions` table:
   - `calls.caller_id` = authenticated user ID
   - `calls.receiver_id` = host / receiver user ID
   - `calls.call_type` = `video` or `audio`
   - `calls.status` = `pending`
   - `calls.room_id` = generated channel/room name
3. Instantly broadcasts `IncomingCallEvent` / `IncomingPrivateCallEvent` on the receiver's socket channels (`private-user.{host_id}` and `user.{host_id}`).

#### Request Body:
```json
{
  "receiver_id": 202,
  "call_type": "video"
}
```

#### Success Response (`200 OK`):
```json
{
  "status": true,
  "success": true,
  "message": "Call initiated successfully.",
  "data": {
    "call_id": 1055,
    "call_session_id": "1055",
    "channel_name": "call_video_101_202_1727421200",
    "room_id": "call_video_101_202_1727421200",
    "call_type": "video",
    "status": "pending",
    "token": "livekit_or_agora_token_string",
    "receiver": {
      "id": 202,
      "name": "Nusrat Jahan",
      "avatar_url": "https://chinchins.live/storage/avatars/host_202.jpg"
    }
  }
}
```

---

## 10. LiveKit Server Setup & Maintenance on Ubuntu VPS

### Check LiveKit Service Status:
```bash
sudo systemctl status livekit-server
```

### Start & Enable LiveKit Service (if stopped/disabled):
```bash
sudo systemctl start livekit-server
sudo systemctl enable livekit-server
```

### View LiveKit Server Logs:
```bash
sudo journalctl -u livekit-server -f
```

### If Running in Docker:
```bash
# Check running containers
sudo docker ps

# If LiveKit container is stopped, restart it:
sudo docker start livekit
# Or docker compose
cd /opt/livekit && sudo docker compose up -d
```

---
**✅ All Backend Requirements Completed & Tested for Chinchins Live.**
