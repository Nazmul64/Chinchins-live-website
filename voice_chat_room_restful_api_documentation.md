# 🎙️ Complete RESTful API & Real-Time WebSocket Documentation
> **Chinchins Live High-Performance Production Backend**  
> **Engine**: Laravel 11 + LiveKit SFU (WebRTC) + Laravel Reverb (WebSocket) + In-Memory Caching  
> **Latency Target**: Sub-50ms (Zero-Loading Screen Experience like BIGO Live & TikTok)

---

## 📑 Table of Contents
1. [🌐 Base URL & Authentication](#1--base-url--authentication)
2. [🔔 Notifications API (Fixed & Optimized)](#2--notifications-api-fixed--optimized)
3. [🎙️ Voice Party Chatroom APIs (Full Lifecycle & Host Object)](#3-️-voice-party-chatroom-apis-full-lifecycle--host-object)
4. [🪑 Seat Management & Host Approval Flow ("অনুরোধ লিস্ট ও গ্রহণ")](#4--seat-management--host-approval-flow-অনুরোধ-লিস্ট-ও-গ্রহণ)
5. [🟢 Real-Time Speaking Indicator (Green Glow / Wave Pulse)](#5--real-time-speaking-indicator-green-glow--wave-pulse)
6. [💬 Real-Time In-Room Chat Stream & Reverb Broadcast](#6--real-time-in-room-chat-stream--reverb-broadcast)
7. [🎁 Zero-Latency Virtual Gifting & Atomic Balance Engine](#7--zero-latency-virtual-gifting--atomic-balance-engine)
8. [💳 Deposit & Payment Gateways (<10ms Response)](#8--deposit--payment-gateways-10ms-response)
9. [💸 Withdrawal Information & Cashout (<15ms Response)](#9--withdrawal-information--cashout-15ms-response)
10. [📞 Instant 1-to-1 Video & Audio Calls (<50ms Initiate)](#10--instant-1-to-1-video--audio-calls-50ms-initiate)
11. [📹 Live Video Broadcasting & Instant Viewer Join](#11--live-video-broadcasting--instant-viewer-join)
12. [⚡ WebSocket Reverb Channels & Events Directory](#12--websocket-reverb-channels--events-directory)
13. [📱 Mobile Client Best Practices (Flutter / Android / iOS)](#13--mobile-client-best-practices-flutter--android--ios)

---

## 1. 🌐 Base URL & Authentication

- **Base REST API URL**: `https://chinchins.live/api`
- **LiveKit WebRTC Server**: `wss://chinchins.live/livekit`
- **Laravel Reverb WebSocket**: `wss://chinchins.live/app`

### Standard Request Headers
```http
Content-Type: application/json
Accept: application/json
Authorization: Bearer <sanctum_user_token>
```

---

## 2. 🔔 Notifications API (Fixed & Optimized)

### Get In-App Notifications
- **Endpoint**: `GET /api/notifications` or `GET /api/fcm/my-notifications`
- **Query Params**: `page=1`, `limit=20`
- **Response**: `200 OK`
```json
{
  "status": true,
  "success": true,
  "unread_count": 3,
  "data": [
    {
      "id": 108,
      "user_id": 15,
      "actor_id": 89,
      "type": "gift",
      "title": "New Gift Received! 🎁",
      "message": "Imran_4 sent you 1x Rocket 🚀 (+500 coins)!",
      "data": {
        "gift_id": 5,
        "gift_name": "Rocket 🚀",
        "gift_icon": "https://chinchins.live/uploads/gifts/rocket.png",
        "quantity": 1,
        "coins_earned": 500
      },
      "is_read": false,
      "read_at": null,
      "created_at": "2026-09-21T20:15:00.000000Z",
      "actor": {
        "id": 89,
        "name": "Imran_4",
        "display_name": "Imran_4",
        "account_id": "94827103",
        "avatar": "https://chinchins.live/uploads/user_image/imran.jpg",
        "avatar_url": "https://chinchins.live/uploads/user_image/imran.jpg",
        "level": 4
      }
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 92
  }
}
```

### Mark Notifications as Read
- **Endpoint**: `POST /api/notifications/read`
- **Request Body (Single or All)**:
```json
{
  "notification_id": 108
}
```
*(Leave empty `{}` to mark all unread notifications as read).*

---

## 3. 🎙️ Voice Party Chatroom APIs (Full Lifecycle & Host Object)

### A. Create a Voice Party Room (Seat 1 Default Host)
- **Endpoint**: `POST /api/party-rooms/create`
- **Behavior**: Automatically initializes Seat 1 assigned to the Host (`seat_index = 1`, `user_id = host_id`, `role = host`, `is_muted = 0`).
- **Request Body**:
```json
{
  "room_title": "Bollywood Karaoke & Hangout 🎙️✨",
  "room_type": "voice",
  "topic_tag": "Singing",
  "max_seats": 10,
  "coin_rate_per_minute": 0,
  "announcement": "Welcome to our live voice party room! Enjoy your stay!"
}
```
- **Response**: `201 Created`
```json
{
  "success": true,
  "status": true,
  "message": "Party room created successfully!",
  "token": "eyJhbGciOi...",
  "livekit_token": "eyJhbGciOi...",
  "livekit_url": "wss://chinchins.live/livekit",
  "can_publish": true,
  "data": {
    "room": {
      "id": 24,
      "room_id": "PR982103",
      "room_title": "Bollywood Karaoke & Hangout 🎙️✨",
      "room_type": "voice",
      "channel_name": "party_voice_pr982103",
      "max_seats": 10,
      "occupied_seats_count": 1,
      "host": {
        "id": 1,
        "name": "Host_User",
        "display_name": "Host_User",
        "account_id": "10008899",
        "avatar": "https://chinchins.live/uploads/user_image/host.jpg",
        "avatar_url": "https://chinchins.live/uploads/user_image/host.jpg",
        "level": 5,
        "coins": 15000
      }
    }
  }
}
```

---

### B. Get Party Room Details
- **Endpoint**: `GET /api/party-rooms/{id}`
- **Response**: `200 OK`
```json
{
  "success": true,
  "status": true,
  "token": "eyJhbGciOi...",
  "livekit_token": "eyJhbGciOi...",
  "livekit_url": "wss://chinchins.live/livekit",
  "can_publish": true,
  "data": {
    "room": {
      "id": 24,
      "room_id": "PR982103",
      "room_title": "Bollywood Karaoke & Hangout 🎙️✨",
      "room_type": "voice",
      "topic_tag": "Singing",
      "room_cover": "https://chinchins.live/uploads/host_image/cover.jpg",
      "channel_name": "party_voice_pr982103",
      "max_seats": 10,
      "occupied_seats_count": 1,
      "online_members_count": 12,
      "is_locked": false,
      "announcement": "Welcome to our live voice party room!",
      "is_host": true,
      "host": {
        "id": 1,
        "name": "Host_User",
        "display_name": "Host_User",
        "account_id": "10008899",
        "avatar": "https://chinchins.live/uploads/user_image/host.jpg",
        "avatar_url": "https://chinchins.live/uploads/user_image/host.jpg",
        "avatar_frame_url": "https://chinchins.live/uploads/frames/gold.svg",
        "level": 5,
        "coins": 15000
      },
      "seats": [
        {
          "seat_index": 1,
          "role": "host",
          "status": "occupied",
          "is_occupied": true,
          "is_muted": false,
          "is_video_muted": false,
          "is_locked": false,
          "user": {
            "id": 1,
            "account_id": "10008899",
            "name": "Host_User",
            "avatar_url": "https://chinchins.live/uploads/user_image/host.jpg",
            "level": 5,
            "is_host": true
          }
        },
        {
          "seat_index": 2,
          "role": "guest",
          "status": "empty",
          "is_occupied": false,
          "is_muted": false,
          "is_video_muted": false,
          "is_locked": false,
          "user": null
        }
      ]
    }
  }
}
```

---

## 4. 🪑 Seat Management & Host Approval Flow ("অনুরোধ লিস্ট ও গ্রহণ")

### A. Audience Sends Seat Request
- **Endpoint**: `POST /api/party-rooms/{id}/seat-requests`
- **Request Body**: `{"seat_index": 2}`

### B. Host Fetches Pending Requests ("অনুরোধ লিস্ট")
- **Endpoint**: `GET /api/party-rooms/{id}/seat-requests`
- **Response**: `200 OK`
```json
{
  "success": true,
  "status": true,
  "count": 1,
  "data": [
    {
      "id": 45,
      "request_id": 45,
      "user_id": 89,
      "account_id": "94827103",
      "name": "Imran_4",
      "display_name": "Imran_4",
      "avatar": "https://chinchins.live/uploads/user_image/imran.jpg",
      "avatar_url": "https://chinchins.live/uploads/user_image/imran.jpg",
      "level": 4,
      "coins": 500,
      "status": "pending"
    }
  ]
}
```

### C. Host Responds to Seat Request ("গ্রহণ করুন" / "বাতিল করুন")
- **Endpoint**: `POST /api/party-rooms/{id}/seat-requests/{requestId}/respond`
- **Accept Body**: `{"action": "accept"}`
- **Reject Body**: `{"action": "reject"}`
- **Accept Response**: `200 OK`
```json
{
  "success": true,
  "status": true,
  "action": "accepted",
  "message": "Seat request accepted. Imran_4 is now on Seat #2.",
  "seat_index": 2,
  "user_id": 89,
  "token": "eyJhbGciOi...",
  "livekit_token": "eyJhbGciOi...",
  "livekit_url": "wss://chinchins.live/livekit",
  "can_publish": true,
  "data": {
    "seat_index": 2,
    "user": {
      "id": 89,
      "name": "Imran_4",
      "avatar_url": "https://chinchins.live/uploads/user_image/imran.jpg"
    },
    "can_publish": true
  }
}
```

### D. Host Moderation (Mute / Kick Seat)
- **Mute Seat**: `POST /api/party-rooms/{id}/mute-seat` (`{"seat_index": 2, "is_muted": true}`)
- **Kick Seat**: `POST /api/party-rooms/{id}/kick-seat` (`{"seat_index": 2}`)
- **Leave Seat (by Guest)**: `POST /api/party-rooms/{id}/leave-seat`

---

## 5. 🟢 Real-Time Speaking Indicator (Green Glow / Wave Pulse)

- **Endpoint**: `POST /api/party-rooms/{id}/speaking`
- **Request Body**: `{"is_speaking": true}`
- **WebSocket Broadcast**: Sends `SeatUpdatedEvent` with `is_speaking: true` / `false`.
- **Mobile UI**: Illuminates the green pulsating halo border around the speaker's photo on their seat.

---

## 6. 💬 Real-Time In-Room Chat Stream & Reverb Broadcast

### A. Send Chat Message (Text / Image)
- **Endpoint**: `POST /api/party-rooms/{id}/send-message` or `POST /api/party-rooms/{id}/messages/send`
- **Request Body (Text)**:
```json
{
  "type": "text",
  "message": "Hello everyone! Welcome to the stage! 🎉"
}
```
- **Real-Time WebSocket Event (`PartyRoomMessageSent`)**:
```json
{
  "event": "PartyRoomMessageSent",
  "channel": "party.24",
  "data": {
    "id": 105,
    "user_id": 89,
    "user_name": "Imran_4",
    "avatar": "https://chinchins.live/uploads/user_image/imran.jpg",
    "avatar_url": "https://chinchins.live/uploads/user_image/imran.jpg",
    "message": "Hello everyone! Welcome to the stage! 🎉",
    "type": "text",
    "created_at": "2026-09-21 20:20:00"
  }
}
```

---

## 7. 🎁 Zero-Latency Virtual Gifting & Atomic Balance Engine

### A. Get Full Gifts Catalog (24hr In-Memory Cache — <5ms)
- **Endpoint**: `GET /api/gifts/catalog` or `GET /api/gifts`
- **Response**: `200 OK`

### B. Send Gift (Atomic Deduction + Background Job Dispatch — <35ms)
- **Endpoint**: `POST /api/gifts/send` or `POST /api/gift/send`
- **Request Body**:
```json
{
  "gift_id": 5,
  "receiver_id": 1,
  "room_name": "party_voice_pr982103",
  "gift_count": 1
}
```
- **Response**: `200 OK`
```json
{
  "success": true,
  "status": true,
  "message": "গিফট সফলভাবে পাঠানো হয়েছে",
  "remaining_coins": 14500,
  "data": {
    "remaining_coins": 14500,
    "gift_id": 5,
    "gift_name": "Rocket 🚀",
    "total_coins": 500,
    "icon_url": "https://chinchins.live/uploads/gifts/rocket.png",
    "animation_url": "https://chinchins.live/uploads/gifts/rocket.svga"
  }
}
```

---

## 8. 💳 Deposit & Payment Gateways (<10ms Response)

### A. Fetch Payment Methods
- **Endpoint**: `GET /api/deposit/methods` or `GET /api/payment/gateways`
- **Response**: `200 OK`

### B. Fetch Coin Packages
- **Endpoint**: `GET /api/deposit/packages` or `GET /api/coin-packages`

### C. Submit Deposit Request
- **Endpoint**: `POST /api/deposit/submit`
- **Request Body**:
```json
{
  "payment_method_id": 1,
  "amount": 500,
  "sender_number": "017XXXXXXXX",
  "transaction_id": "9H76BKL99"
}
```

---

## 9. 💸 Withdrawal Information & Cashout (<15ms Response)

### A. Get Withdrawal Information & Balance Summary
- **Endpoint**: `GET /api/withdraw/info`
- **Response**: `200 OK`

### B. Submit Withdrawal Request
- **Endpoint**: `POST /api/withdraw/request`
- **Request Body**:
```json
{
  "coins": 10000,
  "payment_method": "bkash",
  "account_number": "017XXXXXXXX",
  "account_name": "Ayesha Akter"
}
```

---

## 10. 📞 Instant 1-to-1 Video & Audio Calls (<50ms Initiate)

### A. Initiate Call
- **Endpoint**: `POST /api/calls/initiate`
- **Request Body**:
```json
{
  "receiver_id": 89,
  "call_type": "video"
}
```

### B. Accept Call
- **Endpoint**: `POST /api/calls/accept`
- **Request Body**: `{"call_id": 1420}`

---

## 11. 📹 Live Video Broadcasting & Instant Viewer Join

### A. Start Live Stream
- **Endpoint**: `POST /api/live/start`
- **Request Body**: `{"title": "Evening Music & Chat 🎵"}`

### B. Join Live Stream
- **Endpoint**: `POST /api/live/join`
- **Request Body**: `{"room_id": 14}`

---

## 12. ⚡ WebSocket Reverb Channels & Events Directory

| Channel | Event Class | Broadcast Name (`.listen`) | Description |
| :--- | :--- | :--- | :--- |
| `party.{roomId}` | `PartyRoomMessageSent` | `.PartyRoomMessageSent` | Real-time chat message broadcast |
| `party.{roomId}` | `SeatUpdatedEvent` | `.SeatUpdatedEvent` | Seat occupancy, avatar display, and speaking halo glow |
| `party.{roomId}` | `LiveGiftSentEvent` | `.gift.received` | Virtual gift SVGA / Lottie animation celebration |
| `party-room.{roomId}`| `SeatRequestEvent` | `.seat.requested` | Audience seat request queue updates |
| `user.{userId}` | `CallIncoming` | `.CallIncoming` | Incoming 1-to-1 video/audio call ring notification |

---

## 13. 📱 Mobile Client Best Practices (Flutter / Android / iOS)

### Flutter Client Implementation

```dart
import 'package:laravel_echo/laravel_echo.dart';
import 'package:livekit_client/livekit_client.dart';
import 'package:flutter/material.dart';

// 1. Singleton LiveKit & Reverb Service
class LivePartyService {
  late Echo echo;
  Room? livekitRoom;

  void initEcho(String token) {
    echo = Echo({
      'broadcaster': 'reverb',
      'key': 'your-reverb-app-key',
      'wsHost': 'chinchins.live',
      'wsPort': 443,
      'wssPort': 443,
      'forceTLS': true,
      'auth': {
        'headers': {'Authorization': 'Bearer $token'}
      }
    });
  }

  void subscribeToRoom(String roomId, Function(Map) onMessage, Function(Map) onSeatUpdate, Function(Map) onGift) {
    echo.channel('party.$roomId')
      .listen('.PartyRoomMessageSent', (data) => onMessage(data))
      .listen('.SeatUpdatedEvent', (data) => onSeatUpdate(data))
      .listen('.gift.received', (data) => onGift(data));
  }

  Future<void> joinVoiceStage(String livekitUrl, String token, bool canPublish) async {
    livekitRoom = Room(
      roomOptions: const RoomOptions(
        adaptiveStream: true,
        dynacast: true,
      ),
    );

    await livekitRoom!.connect(livekitUrl, token);

    if (canPublish) {
      await livekitRoom!.localParticipant?.setMicrophoneEnabled(true);
    }
  }
}
```

---
*Official Production RESTful API Documentation — Chinchins Live High-Performance Platform*
