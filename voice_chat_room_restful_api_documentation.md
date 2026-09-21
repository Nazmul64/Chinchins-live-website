# ⚡ Blazing-Fast Voice Chat Room, Video Streaming & RESTful API Documentation
> **Engine Architecture**: Laravel 11 + LiveKit WebRTC (SFU) + Laravel Reverb (WebSocket) + High-Performance In-Memory Caching  
> **Latency Target**: Sub-50ms Response Time (Zero Loading Screens, Instant UI Transitions like TikTok / Bigo Live)

---

## 📑 Table of Contents
1. [🚀 Architecture & Ultra-Fast Zero-Loading Strategy](#1--architecture--ultra-fast-zero-loading-strategy)
2. [🌐 Base URL & Authentication](#2--base-url--authentication)
3. [💳 Instant Deposit & Payment Gateways (< 10ms)](#3--instant-deposit--payment-gateways--10ms)
4. [💸 Instant Withdrawal Information & Cashout (< 15ms)](#4--instant-withdrawal-information--cashout--15ms)
5. [📞 Instant 1-to-1 Video & Audio Calls (< 50ms Initiate / Join)](#5--instant-1-to-1-video--audio-calls--50ms-initiate--join)
6. [📹 Live Video Broadcasting & Instant Viewer Join (< 50ms)](#6--live-video-broadcasting--instant-viewer-join--50ms)
7. [🎙️ Voice Party Chatroom Stage (Host Seat 1 + Multi-Guest Seats)](#7-️-voice-party-chatroom-stage-host-seat-1--multi-guest-seats)
8. [👥 Seat Request & Host Approval Flow ("অনুরোধ লিস্ট ও গ্রহণ")](#8--seat-request--host-approval-flow-অনুরোধ-লিস্ট-ও-গ্রহণ)
9. [🟢 Real-Time Speaking Indicator (Green Glow / Wave Pulse)](#9--real-time-speaking-indicator-green-glow--wave-pulse)
10. [💬 Real-Time In-Room Chat Stream & Reverb Broadcast](#10--real-time-in-room-chat-stream--reverb-broadcast)
11. [🎁 Virtual Gifting & Host Revenue Split](#11--virtual-gifting--host-revenue-split)
12. [⚡ WebSocket Reverb Channels & Events Directory](#12--websocket-reverb-channels--events-directory)
13. [📱 Mobile App Developer Best Practices (Optimistic UI & Caching)](#13--mobile-app-developer-best-practices-optimistic-ui--caching)

---

## 1. 🚀 Architecture & Ultra-Fast Zero-Loading Strategy

Top-tier live streaming applications (like TikTok, Bigo Live, Tango) **never** show full-screen blocking loading spinners. They achieve instantaneous interaction using:
1. **Optimistic UI Updates**: Update UI immediately on tap before network roundtrips complete.
2. **Pre-warmed WebRTC / LiveKit Connections**: Reuse persistent LiveKit rooms and Reverb WebSockets without reconnecting from scratch.
3. **In-Memory Backend Model Serialization**: User models are stripped of heavy N+1 database queries. Accessors load from memory cache in `< 1ms`.
4. **Non-Blocking Push Notifications**: High-priority push notifications execute with `1.0s` connect timeouts and asynchronous execution.
5. **Local Client Caching**: Payment methods, coin packages, and user profiles are cached locally on device and refreshed in the background (`stale-while-revalidate`).

---

## 2. 🌐 Base URL & Authentication

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

## 3. 💳 Instant Deposit & Payment Gateways (< 10ms)

All payment methods and coin packages are served from high-speed memory cache.

### A. Fetch Payment Methods (bKash, Nagad, Rocket, Resellers)
- **Endpoint**: `GET /api/deposit/methods` or `GET /api/payment-methods`
- **Response**: `200 OK` (Average latency: 5ms)
```json
{
  "status": true,
  "reseller_enabled": true,
  "reseller_badge": "Up To 29%↑",
  "active_resellers_count": 2,
  "data": [
    {
      "id": 1,
      "name": "bKash Personal",
      "code": "bkash",
      "account_type": "Personal Send Money",
      "account_number": "017XXXXXXXX",
      "rate_coins": 1000,
      "bonus_coins": 100,
      "total_coins": 1100,
      "rate_bdt": 100.0,
      "icon": "https://chinchins.live/uploads/payment_methods/bkash.svg"
    }
  ]
}
```

### B. Fetch Coin Packages
- **Endpoint**: `GET /api/deposit/packages` or `GET /api/coin-packages`
- **Response**: `200 OK`

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

## 4. 💸 Instant Withdrawal Information & Cashout (< 15ms)

### A. Get User Withdrawal Info & Balance Summary
- **Endpoint**: `GET /api/withdraw/info`
- **Response**: `200 OK` (Aggregated database index query)
```json
{
  "status": true,
  "data": {
    "user_balance": {
      "user_id": 15,
      "account_id": "94827103",
      "display_name": "Host_Ayesha",
      "coins": 45000,
      "formatted_coins": "45,000 Coins",
      "estimated_gross_bdt": 4500.00,
      "estimated_commission_bdt": 450.00,
      "estimated_net_bdt": 4050.00,
      "formatted_estimated_net_bdt": "৳4,050.00",
      "can_withdraw": true,
      "total_withdrawn_coins": 120000,
      "total_withdrawn_bdt": 10800.00,
      "pending_withdraws_count": 0
    }
  }
}
```

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

## 5. 📞 Instant 1-to-1 Video & Audio Calls (< 50ms Initiate / Join)

Instantaneous call initiation without blocking for push notifications.

### A. Initiate 1-to-1 Call
- **Endpoint**: `POST /api/calls/initiate` or `POST /api/call/initiate`
- **Request Body**:
```json
{
  "receiver_id": 89,
  "call_type": "video"
}
```
- **Response**: `200 OK` (Immediate response with channel and tokens)
```json
{
  "status": true,
  "message": "Call initiated! Ringing receiver...",
  "data": {
    "call_id": 1420,
    "channel_name": "call_video_15_89_1774301928_a9f1",
    "call_type": "video",
    "status": "ringing",
    "rate_per_minute": 100,
    "caller_coins": 5000,
    "receiver": {
      "id": 89,
      "name": "Imran_4",
      "avatar": "https://chinchins.live/uploads/user_image/imran.jpg"
    }
  }
}
```

### B. Accept Incoming Call
- **Endpoint**: `POST /api/calls/accept`
- **Request Body**: `{"call_id": 1420}`
- **Response**: `200 OK` (Both parties join WebRTC channel instantly)

---

## 6. 📹 Live Video Broadcasting & Instant Viewer Join (< 50ms)

### A. Host Starts Live Video Broadcast
- **Endpoint**: `POST /api/live/start`
- **Request Body**:
```json
{
  "title": "Evening Music & Chat 🎵",
  "cover_image_url": "https://chinchins.live/uploads/live_streaming/cover1.jpg"
}
```
- **Response**: `200 OK` (Includes LiveKit Publisher Token)

### B. Viewer Joins Live Stream
- **Endpoint**: `POST /api/live/join` or `POST /api/live/{id}/join`
- **Request Body**: `{"room_id": 14}`
- **Response**: `200 OK` (Returns LiveKit Subscriber Token instantly)

---

## 7. 🎙️ Voice Party Chatroom Stage (Host Seat 1 + Multi-Guest Seats)

### A. Create a Voice Party Room (Host Automatically on Seat 1)
- **Endpoint**: `POST /api/party-rooms/create`
- **Backend Execution**: Automatically assigns Host to Seat 1 (`seat_index: 1`, `user_id: host_id`, `role: host`, `is_muted: 0`, `status: occupied`).
- **Request Body**:
```json
{
  "room_title": "Adda with Friends 🎤✨",
  "room_type": "voice",
  "topic_tag": "ChitChat",
  "max_seats": 10,
  "announcement": "Welcome to our party room! Respect everyone."
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
      "room_title": "Adda with Friends 🎤✨",
      "room_type": "voice",
      "channel_name": "party_voice_pr982103",
      "max_seats": 10,
      "occupied_seats_count": 1,
      "host": {
        "id": 1,
        "name": "Host_User",
        "avatar_url": "https://chinchins.live/uploads/user_image/host.jpg"
      }
    },
    "seats": [
      {
        "seat_index": 1,
        "user_id": 1,
        "role": "host",
        "is_muted": false,
        "status": "occupied",
        "user_profile": {
          "id": 1,
          "name": "Host_User",
          "avatar_url": "https://chinchins.live/uploads/user_image/host.jpg",
          "level": 5,
          "is_host": true
        }
      },
      {
        "seat_index": 2,
        "user_id": null,
        "role": "guest",
        "is_muted": false,
        "status": "empty",
        "user_profile": null
      }
    ]
  }
}
```

---

## 8. 👥 Seat Request & Host Approval Flow ("অনুরোধ লিস্ট ও গ্রহণ")

1. **Guest Requests Seat**: `POST /api/party-rooms/{id}/seat-requests` with `{"seat_index": 2}`.
2. **Host Fetches Queue**: `GET /api/party-rooms/{id}/seat-requests` returns pending requests.
3. **Host Accepts Request**: `POST /api/party-rooms/{id}/seat-requests/{requestId}/respond` with `{"action": "accept"}`.
   - Instantly assigns guest to Seat 2.
   - Triggers `SeatUpdatedEvent` on Reverb WebSocket.
   - Mobile app displays guest's photo on Seat 2 immediately.
4. **Host Mute/Kick**:
   - Mute: `POST /api/party-rooms/{id}/mute-seat` (`{"seat_index": 2, "is_muted": true}`)
   - Kick: `POST /api/party-rooms/{id}/kick-seat` (`{"seat_index": 2}`)

---

## 9. 🟢 Real-Time Speaking Indicator (Green Glow / Wave Pulse)

When a seated user speaks or stops speaking:
- **Endpoint**: `POST /api/party-rooms/{id}/speaking`
- **Request Body**:
```json
{
  "is_speaking": true
}
```
- **Reverb Broadcast**: Sends `SeatUpdatedEvent` with `is_speaking: true` / `false`.
- **Mobile UI**: Illuminates the green pulsating halo around the speaker's avatar on their designated seat.

---

## 10. 💬 Real-Time In-Room Chat Stream & Reverb Broadcast

### Send Message
- **Endpoint**: `POST /api/party-rooms/{id}/send-message` or `POST /api/party-rooms/{id}/messages/send`
- **Request Body (Text)**:
```json
{
  "type": "text",
  "message": "Hello everyone! Welcome to the stage! 🎉"
}
```
- **Real-Time WebSocket Broadcast**: Backend broadcasts `PartyRoomMessageSent` to all participants on channel `party.{roomId}`:
```php
broadcast(new \App\Events\PartyRoomMessageSent($room->id, [
    'id'         => $msg->id,
    'user_id'    => auth()->id(),
    'user_name'  => auth()->user()->name,
    'avatar'     => auth()->user()->avatar,
    'avatar_url' => auth()->user()->avatar_url,
    'message'    => $request->message,
    'type'       => $msg->type,
    'image_url'  => $msg->full_image_url,
    'created_at' => now()->toDateTimeString(),
]))->toOthers();
```

---

## 11. 🎁 Virtual Gifting & Host Revenue Split

### Send Gift into Party Room or Live Stream
- **Endpoint**: `POST /api/party-rooms/{id}/send-gift`
- **Request Body**:
```json
{
  "gift_id": 5,
  "receiver_id": 1,
  "count": 1
}
```
- **Response**: `200 OK` (Includes remaining coins and celebratory gift animation data)

---

## 12. ⚡ WebSocket Reverb Channels & Events Directory

| Channel | Event | Broadcast Name | Payload Data |
| :--- | :--- | :--- | :--- |
| `party.{roomId}` | `PartyRoomMessageSent` | `PartyRoomMessageSent` | `{id, user_id, user_name, avatar_url, message, created_at}` |
| `party.{roomId}` | `SeatUpdatedEvent` | `SeatUpdatedEvent` | `{room_id, seat_index, user_id, is_speaking, is_muted, user}` |
| `party.{roomId}` | `SeatRequestEvent` | `seat.requested` | `{invitation_id, user_id, status, action}` |
| `party.{roomId}` | `GiftSentEvent` | `GiftSentEvent` | `{sender_id, receiver_id, gift_id, gift_name, count}` |
| `live-stream.{id}`| `LiveViewerCountUpdated` | `LiveViewerCountUpdated` | `{viewer_count, action, user}` |
| `user.{userId}` | `CallIncoming` | `CallIncoming` | `{call_id, channel_name, caller, call_type}` |

---

## 13. 📱 Mobile App Developer Best Practices (Optimistic UI & Caching)

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

  void subscribeToRoom(String roomId, Function(Map) onMessage, Function(Map) onSeatUpdate) {
    echo.channel('party.$roomId')
      .listen('.PartyRoomMessageSent', (data) => onMessage(data))
      .listen('.SeatUpdatedEvent', (data) => onSeatUpdate(data));
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
*Official API Documentation — Chinchins Live High-Performance Architecture*
