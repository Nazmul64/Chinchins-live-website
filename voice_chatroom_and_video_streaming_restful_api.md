# 🎙️ Voice Chatroom & 📹 Video Streaming RESTful API Documentation

This document provides complete, production-ready RESTful API endpoints and WebSocket architecture for:
1. **📹 4–5 Multi-Guest Video Chat Stage (Live HD Video Grid)**
2. **🎙️ 8–16 Multi-Guest Voice Party Room Stage**
3. **💬 Real-Time Unlimited In-Room Chat Stream (SMS, Photos, System Broadcasts)**
4. **🎁 Real-Time Virtual Gifting & 50/50 Revenue Split Billing Engine**
5. **⚡ WebSocket LiveKit WebRTC Token & Reverb Event Synchronization**

---

## 🌐 Base URL & Common Headers

**Base URL**: `https://chinchins.live/api` or `http://localhost:8000/api`

### Required Request Headers
```http
Content-Type: application/json
Accept: application/json
Authorization: Bearer <user_token>
```

---

## 1. 📹 Multi-Guest Video Stage (4–5 Video Tiles Grid)

### A. Create a Multi-Guest Video Room
- **Endpoint**: `POST /api/party-rooms/create`
- **Description**: Host opens a 4–5 guest video party room.
- **Request Body**:
```json
{
  "room_title": "Bollywood Karaoke & Hangout 🎥",
  "room_type": "video",
  "topic_tag": "Singing",
  "max_seats": 5,
  "coin_rate_per_minute": 100,
  "announcement": "Welcome to our live video party! Be nice and enjoy!"
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
      "id": 14,
      "room_id": "PR849201",
      "room_title": "Bollywood Karaoke & Hangout 🎥",
      "room_type": "video",
      "channel_name": "party_video_pr849201",
      "max_seats": 5,
      "occupied_seats_count": 1,
      "host": {
        "id": 1,
        "name": "Siddharth_P",
        "avatar_url": "https://chinchins.live/uploads/host_image/avatar1.jpg"
      }
    }
  }
}
```

---

### B. Toggle Video Camera on Seat
- **Endpoint**: `POST /api/party-rooms/{id}/toggle-video`
- **Request Body**:
```json
{
  "is_video_muted": false
}
```
- **Response**: `200 OK`
```json
{
  "success": true,
  "status": true,
  "is_video_muted": 0,
  "message": "Video camera enabled."
}
```

---

## 2. 🎙️ Live Voice Party Room (8–16 Multi-Guest Seats)

### A. Real-Time Speaking Wave Pulse Halo
- **Endpoint**: `POST /api/party-rooms/{id}/speaking`
- **Description**: Triggered whenever a seated guest or host speaks or pauses so UI animates pulsating green halos.
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
  "seat_index": 5,
  "user_id": 142,
  "message": "Speaking indicator active."
}
```

---

### B. Fetch Speaker Queue for Host ("অনুরোধ লিস্ট")
- **Endpoint**: `GET /api/party-rooms/{id}/seat-requests`
- **Description**: Exclusively fetched by the Host to review audience members waiting in the speaker queue.
- **Response**: `200 OK`
```json
{
  "success": true,
  "status": true,
  "count": 2,
  "data": [
    {
      "id": 12,
      "request_id": 12,
      "user_id": 89,
      "account_id": "IMRAN4_99",
      "name": "Imran_4",
      "display_name": "Imran_4",
      "avatar": "https://chinchins.live/uploads/user_image/avatar1.jpg",
      "avatar_url": "https://chinchins.live/uploads/user_image/avatar1.jpg",
      "level": 4,
      "coins": 500,
      "status": "pending",
      "created_at": "2026-09-21T07:45:00.000000Z"
    }
  ]
}
```

---

### C. Host Responds to Seat Request: Accept ("গ্রহণ করুন") / Reject ("বাতিল করুন")
- **Endpoint**: `POST /api/party-rooms/{id}/seat-requests/{requestId}/respond`
- **Accept Payload**:
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
  "message": "Seat request accepted. Imran_4 is now on Seat #3.",
  "seat_index": 3,
  "user_id": 89,
  "token": "eyJhbGciOi...",
  "livekit_token": "eyJhbGciOi...",
  "livekit_url": "wss://chinchins.live/livekit",
  "can_publish": true,
  "data": {
    "seat_index": 3,
    "user": {
      "id": 89,
      "name": "Imran_4",
      "avatar_url": "https://chinchins.live/uploads/user_image/avatar1.jpg"
    },
    "can_publish": true
  }
}
```

- **Reject Payload**:
```json
{
  "action": "reject"
}
```
- **Reject Response**: `200 OK`
```json
{
  "success": true,
  "status": true,
  "action": "rejected",
  "message": "Seat request has been rejected (বাতিল করা হয়েছে)."
}
```

---

### D. Host Mute / Unmute Speaker
- **Endpoint**: `POST /api/party-rooms/{id}/mute-seat`
- **Request Body**:
```json
{
  "seat_index": 3,
  "is_muted": true
}
```
- **Response**: `200 OK`
```json
{
  "success": true,
  "status": true,
  "is_muted": 1,
  "seat_index": 3,
  "message": "Seat #3 has been muted."
}
```

---

### E. Host Kick Speaker from Stage
- **Endpoint**: `POST /api/party-rooms/{id}/kick-seat`
- **Request Body**:
```json
{
  "seat_index": 3
}
```
- **Response**: `200 OK`
```json
{
  "success": true,
  "status": true,
  "message": "Guest removed from Seat #3."
}
```

---

## 3. 💬 Real-Time In-Room Chat Stream (Unlimited SMS)

### A. Get Room Chat Messages
- **Endpoint**: `GET /api/party-rooms/{id}/messages`
- **Query Params**: `page=1`, `per_page=50`
- **Response**: `200 OK`
```json
{
  "success": true,
  "status": true,
  "data": [
    {
      "id": 105,
      "user_id": 42,
      "user_name": "Sadia_B",
      "avatar_url": "https://chinchins.live/uploads/user_image/sadia.jpg",
      "type": "text",
      "message": "সবাই কথা বলতেছে খুব ভালো লাগছে!",
      "created_at": "2026-09-21T07:50:00.000000Z"
    }
  ]
}
```

---

### B. Send Text or Image Message
- **Endpoint**: `POST /api/party-rooms/{id}/send-message`
- **Request Body (Text)**:
```json
{
  "type": "text",
  "message": "গেম নিয়ে কথা হচ্ছে?"
}
```
- **Request Body (Image Upload - multipart/form-data)**:
  - `type`: `image`
  - `image_file`: File attachment (PNG/JPG up to 10MB)

---

### C. Send Virtual Gifts into Room
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
    "remaining_coins": 14200,
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

## 4. ⚡ WebSocket Events & Channel Specifications

Listen on channels: `party.{roomId}` or `presence-party.{roomId}` or `party-room.{roomId}`.

### Event 1: `SeatUpdatedEvent`
Triggered on **seat join**, **speaking indicator change**, **mic toggle**, **video toggle**, and **seat leave/kick**:
```json
{
  "event": "SeatUpdatedEvent",
  "data": {
    "room_id": "14",
    "room_name": "party_voice_PR849201",
    "seat_index": 5,
    "user_id": 142,
    "is_speaking": true,
    "action": "speaking_change",
    "user": {
      "id": 142,
      "account_id": "SHAKIL_77",
      "name": "Shakil",
      "display_name": "Shakil",
      "avatar_url": "https://chinchins.live/uploads/user_image/shakil.jpg",
      "level": 7
    },
    "timestamp": "2026-09-21T07:50:00+06:00"
  }
}
```

### Event 2: `PartyRoomMessageEvent`
Triggered whenever a message or gift is sent into the room:
```json
{
  "event": "PartyRoomMessageEvent",
  "data": {
    "room_id": "14",
    "user": {
      "id": 42,
      "name": "Sadia_B",
      "avatar_url": "https://chinchins.live/uploads/user_image/sadia.jpg"
    },
    "type": "text",
    "message": "হেই সবাই কে!",
    "timestamp": "2026-09-21T07:51:00+06:00"
  }
}
```

---

## 5. 📱 Flutter / Dart Client Code Integration

### A. LiveKit Audio & Video Stream Initialization
```dart
import 'package:livekit_client/livekit_client.dart';

Future<Room> connectToPartyRoom({
  required String livekitUrl,
  required String token,
  required bool canPublish,
}) async {
  final room = Room(
    roomOptions: RoomOptions(
      adaptiveStream: true,
      dynacast: true,
      defaultAudioPublishOptions: AudioPublishOptions(
        dtx: true,
        echoCancellation: true,
        noiseSuppression: true,
      ),
    ),
  );

  await room.connect(livekitUrl, token);

  if (canPublish) {
    // Unmute Microphone for Seated Speaker / Host
    await room.localParticipant?.setMicrophoneEnabled(true);
  }

  return room;
}
```

---

### B. Real-Time Speaking Wave Pulse Listener
```dart
void setupSpeakingDetector(Room room, String roomId, String authToken) {
  room.addListener(() {
    for (var participant in room.remoteParticipants.values) {
      if (participant.isSpeaking) {
        print("🔊 Speaker active: ${participant.identity}");
      }
    }

    // Report local speaking state to backend
    bool isLocalSpeaking = room.localParticipant?.isSpeaking ?? false;
    http.post(
      Uri.parse('https://chinchins.live/api/party-rooms/$roomId/speaking'),
      headers: {
        'Authorization': 'Bearer $authToken',
        'Content-Type': 'application/json',
      },
      body: jsonEncode({'is_speaking': isLocalSpeaking}),
    );
  });
}
```

---

## 🚀 Summary
The Voice Chatroom and Video Streaming RESTful backend is 100% complete, fully synchronized with LiveKit WebRTC, and features live moderation tools directly within the Laravel Admin Panel.
