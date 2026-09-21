# 🎙️ Voice Chat Room & 📹 Video Streaming RESTful API Documentation

This document is the official, comprehensive RESTful API and WebSocket specification for the **Voice Party Chatroom & Video Streaming Engine** in Chinchins Live.

---

## 📑 Table of Contents
1. [🌐 Base URL & Authentication](#1--base-url--authentication)
2. [🎙️ Voice & Video Party Room Architecture](#2-️-voice--video-party-room-architecture)
3. [🪑 Room Creation & Default Host Seat 1 Setup](#3--room-creation--default-host-seat-1-setup)
4. [👥 Seat Request & Host Approval Flow ("অনুরোধ লিস্ট ও গ্রহণ")](#4--seat-request--host-approval-flow-অনুরোধ-লিস্ট-ও-গ্রহণ)
5. [🟢 Real-Time Speaking Indicator (Green Glow / Wave Pulse)](#5--real-time-speaking-indicator-green-glow--wave-pulse)
6. [💬 Real-Time In-Room Chat Stream & Reverb Broadcast](#6--real-time-in-room-chat-stream--reverb-broadcast)
7. [🎁 Virtual Gifting & Host Revenue Split](#7--virtual-gifting--host-revenue-split)
8. [⚡ WebSocket Reverb Channels & Events](#8--websocket-reverb-channels--events)
9. [📱 Mobile Client Integration (Flutter / Android / iOS)](#9--mobile-client-integration-flutter--android--ios)

---

## 1. 🌐 Base URL & Authentication

- **Base URL**: `https://chinchins.live/api` (Production) or `http://127.0.0.1:8000/api` (Local)
- **LiveKit WebRTC Server**: `wss://chinchins.live/livekit`
- **Reverb WebSocket Server**: `wss://chinchins.live/app`

### Required Request Headers
```http
Content-Type: application/json
Accept: application/json
Authorization: Bearer <user_token>
```

---

## 2. 🎙️ Voice & Video Party Room Architecture

- **Host (সিট ১ / Seat 1)**: Automatically occupied by the room creator/host with microphone permissions (`can_publish: true`). The Host's avatar and name are permanently displayed on Seat 1.
- **Guests (সিট ২–১০+ / Seats 2..N)**: Audience members send a seat request. When the host accepts, the guest's profile photo and name appear on the requested seat.
- **Speaking Wave / Green Indicator**: When any seated user (Host or Guest) speaks, a green glowing border / pulse wave lights up around their seat avatar.
- **Real-Time Reverb Sync**: All chat messages, seat requests, seat updates, speaking states, and gifts broadcast instantly via Laravel Reverb WebSockets.

---

## 3. 🪑 Room Creation & Default Host Seat 1 Setup

### Create a Voice or Video Party Room
- **Endpoint**: `POST /api/party-rooms/create`
- **Headers**: `Authorization: Bearer <token>`
- **Behavior**: Creates room and automatically initializes Seat 1 assigned to the Host (`seat_index = 1`, `user_id = host_id`, `role = host`, `is_muted = 0`).

#### Request Body
```json
{
  "room_title": "Bollywood Karaoke & Hangout 🎙️✨",
  "room_type": "voice",
  "topic_tag": "Singing",
  "max_seats": 10,
  "coin_rate_per_minute": 0,
  "announcement": "Welcome to our live voice party! Enjoy your stay!"
}
```

#### Response: `201 Created`
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
      "id": 14,
      "room_id": "PR849201",
      "room_title": "Bollywood Karaoke & Hangout 🎙️✨",
      "room_type": "voice",
      "channel_name": "party_voice_pr849201",
      "max_seats": 10,
      "occupied_seats_count": 1,
      "host": {
        "id": 1,
        "name": "Host_User",
        "display_name": "Host_User",
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

## 4. 👥 Seat Request & Host Approval Flow ("অনুরোধ লিস্ট ও গ্রহণ")

### A. Audience Requests a Seat
- **Endpoint**: `POST /api/party-rooms/{id}/seat-requests`
- **Request Body**:
```json
{
  "seat_index": 2
}
```
- **Response**: `200 OK`
```json
{
  "success": true,
  "status": true,
  "message": "Seat request submitted. Waiting for host approval."
}
```

---

### B. Host Views Pending Seat Requests ("অনুরোধ লিস্ট")
- **Endpoint**: `GET /api/party-rooms/{id}/seat-requests`
- **Response**: `200 OK`
```json
{
  "success": true,
  "status": true,
  "count": 1,
  "data": [
    {
      "id": 25,
      "request_id": 25,
      "user_id": 89,
      "account_id": "USER_89",
      "name": "Imran_4",
      "display_name": "Imran_4",
      "avatar": "https://chinchins.live/uploads/user_image/avatar89.jpg",
      "avatar_url": "https://chinchins.live/uploads/user_image/avatar89.jpg",
      "level": 4,
      "coins": 500,
      "status": "pending",
      "created_at": "2026-09-21T18:00:00.000000Z"
    }
  ]
}
```

---

### C. Host Accepts ("গ্রহণ করুন") or Rejects ("বাতিল করুন") Seat Request
- **Endpoint**: `POST /api/party-rooms/{id}/seat-requests/{requestId}/respond`
- **Accept Request**:
```json
{
  "action": "accept"
}
```
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
      "avatar_url": "https://chinchins.live/uploads/user_image/avatar89.jpg"
    },
    "can_publish": true
  }
}
```
*(Broadcasts `SeatUpdatedEvent` to everyone in the room; Imran's picture instantly appears on Seat 2).*

- **Reject Request**:
```json
{
  "action": "reject"
}
```

---

### D. Host Moderation (Mute / Kick Seat)
- **Mute Seat**: `POST /api/party-rooms/{id}/mute-seat`
  - Body: `{"seat_index": 2, "is_muted": true}`
- **Kick Seat**: `POST /api/party-rooms/{id}/kick-seat`
  - Body: `{"seat_index": 2}`
- **Leave Seat (by Guest)**: `POST /api/party-rooms/{id}/leave-seat`

---

## 5. 🟢 Real-Time Speaking Indicator (Green Glow / Wave Pulse)

When any seated user (Host on Seat 1 or accepted Guests on Seats 2..N) starts talking:
- **Endpoint**: `POST /api/party-rooms/{id}/speaking`
- **Request Body**:
```json
{
  "is_speaking": true
}
```
- **Response**: `200 OK`
```json
{
  "success": true,
  "status": true,
  "is_speaking": true,
  "seat_index": 2,
  "user_id": 89,
  "message": "Speaking indicator broadcasted."
}
```
*(Broadcasts `SeatUpdatedEvent` with `is_speaking: true` / `false` so the UI illuminates the green pulsating halo around the speaker's photo).*

---

## 6. 💬 Real-Time In-Room Chat Stream & Reverb Broadcast

### A. Send Chat Message (Text / Image)
- **Endpoint**: `POST /api/party-rooms/{id}/send-message` or `POST /api/party-rooms/{id}/messages/send`
- **Headers**: `Authorization: Bearer <token>`
- **Request Body (Text)**:
```json
{
  "type": "text",
  "message": "আসসালামু আলাইকুম! কেমন আছেন সবাই? 🎉"
}
```
- **Request Body (Image Upload - multipart/form-data)**:
  - `type`: `image`
  - `file` or `image`: `[binary image file]`
- **Response**: `200 OK`
```json
{
  "success": true,
  "status": true,
  "message": "Message sent successfully.",
  "data": {
    "id": 105,
    "room_id": "PR849201",
    "type": "text",
    "message": "আসসালামু আলাইকুম! কেমন আছেন সবাই? 🎉",
    "image_url": null,
    "created_at": "2026-09-21T18:05:00+06:00",
    "sender": {
      "id": 89,
      "name": "Imran_4",
      "avatar_url": "https://chinchins.live/uploads/user_image/avatar89.jpg",
      "level": 4
    }
  }
}
```

### B. Broadcasted Event: `PartyRoomMessageSent`
Immediately after message creation, backend executes:
```php
broadcast(new \App\Events\PartyRoomMessageSent($roomId, [
    'id'         => $message->id,
    'user_id'    => auth()->id(),
    'user_name'  => auth()->user()->name,
    'avatar'     => auth()->user()->avatar,
    'avatar_url' => auth()->user()->avatar_url,
    'message'    => $request->message,
    'type'       => $message->type,
    'image_url'  => $message->full_image_url,
    'created_at' => now()->toDateTimeString(),
]))->toOthers();
```

---

## 7. 🎁 Virtual Gifting & Host Revenue Split

### Send Gift into Party Room
- **Endpoint**: `POST /api/party-rooms/{id}/send-gift`
- **Request Body**:
```json
{
  "gift_id": 5,
  "receiver_id": 1,
  "count": 1
}
```
- **Response**: `200 OK`
```json
{
  "success": true,
  "status": true,
  "message": "Gift Rocket 🚀 sent successfully!",
  "data": {
    "remaining_coins": 12500,
    "total_cost": 500,
    "gift": {
      "id": 5,
      "name": "Rocket 🚀",
      "icon_url": "https://chinchins.live/uploads/gifts/rocket.png"
    }
  }
}
```

---

## 8. ⚡ WebSocket Reverb Channels & Events

### Channels
- Public Channel: `party.{roomId}`
- Alternative Channel: `party-room.{roomId}`
- Presence Channel: `presence-party.{roomId}`

### Events Table
| Event Name | Broadcast As | Description |
| :--- | :--- | :--- |
| `PartyRoomMessageSent` | `PartyRoomMessageSent` | Real-time chat message broadcasted to all room participants |
| `SeatUpdatedEvent` | `SeatUpdatedEvent` | Seat taken, seat accepted, speaking green halo changed, muted, kicked |
| `SeatRequestEvent` | `seat.requested` | Audience requested a seat / Host rejected request |
| `GiftSentEvent` | `GiftSentEvent` | Virtual gift animation and coin celebration in room |

---

## 9. 📱 Mobile Client Integration (Flutter / Android / iOS)

### Flutter Laravel Reverb & LiveKit Client Setup

```dart
import 'package:laravel_echo/laravel_echo.dart';
import 'package:pusher_client/pusher_client.dart';
import 'package:livekit_client/livekit_client.dart';

// 1. Listen to Reverb WebSocket Channel
void listenToPartyRoom(String roomId, String token) {
  Echo echo = Echo({
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

  // Listen for Real-Time Chat Messages
  echo.channel('party.$roomId')
      .listen('.PartyRoomMessageSent', (data) {
        print("💬 New Chat Message: ${data['message']} from ${data['user_name']}");
        // Add message to in-room chat list
      })
      .listen('.SeatUpdatedEvent', (data) {
        print("🪑 Seat Updated: Seat #${data['seat_index']}, Speaking: ${data['is_speaking']}");
        // Update seat avatar picture and green glowing border
      });
}

// 2. Connect to LiveKit Room
Future<Room> connectLiveKit(String livekitUrl, String token, bool canPublish) async {
  final room = Room(
    roomOptions: const RoomOptions(
      adaptiveStream: true,
      dynacast: true,
    ),
  );

  await room.connect(livekitUrl, token);

  if (canPublish) {
    await room.localParticipant?.setMicrophoneEnabled(true);
  }

  return room;
}
```

---
*End of Documentation*
