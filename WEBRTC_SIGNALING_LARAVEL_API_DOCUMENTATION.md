# 📡 WebRTC Signaling Backend & Laravel Reverb API Documentation

> **Chinchins Live — WebRTC Signaling Server, Real-time Events & Call Management Backend**
> **Backend Architecture:** Laravel 11/12 (PHP 8.2+) | Laravel Reverb (WebSockets) | Laravel Sanctum | MySQL

---

## 📑 Table of Contents

1. [ভূমিকা ও আর্কিটেকচার (Architecture & Core Role)](#1-ভূমিকা-ও-আর্কিটেকচার)
2. [প্রয়োজনীয় কম্পোনেন্টস (Required Backend Components)](#2-প্রয়োজনীয়-কম্পোনেন্টস)
3. [ডাটাবেজ স্কিমা (`calls` Table Schema)](#3-ডাটাবেজ-স্কিমা-calls-table)
4. [কল লাইফসাইকেল ও সিগন্যালিং ফ্লো (Call Lifecycle Flow)](#4-কল-লাইফসাইকেল-ও-সিগন্যালিং-ফ্লো)
5. [Private Channel কনফিগারেশন (`routes/channels.php`)](#5-private-channel-কনফিগারেশন)
6. [Laravel Broadcasting Events ও Payload স্পেসিফিকেশন](#6-laravel-broadcasting-events)
7. [সম্পূর্ণ REST API এন্ডপয়েন্টস রেফারেন্স (Full API Reference)](#7-সম্পূর্ণ-rest-api-এন্ডপয়েন্টস-রেফারেন্স)
   - [7.1 Create Call (`POST /api/calls`)](#71-create-call)
   - [7.2 Accept Call (`POST /api/calls/{id}/accept`)](#72-accept-call)
   - [7.3 Reject Call (`POST /api/calls/{id}/reject`)](#73-reject-call)
   - [7.4 Cancel Call (`POST /api/calls/{id}/cancel`)](#74-cancel-call)
   - [7.5 End Call (`POST /api/calls/{id}/end`)](#75-end-call)
   - [7.6 Relay SDP Offer (`POST /api/calls/{id}/offer`)](#76-relay-sdp-offer)
   - [7.7 Relay SDP Answer (`POST /api/calls/{id}/answer`)](#77-relay-sdp-answer)
   - [7.8 Relay ICE Candidate (`POST /api/calls/{id}/ice-candidate`)](#78-relay-ice-candidate)
   - [7.9 Unified Signal Relay (`POST /api/calls/{id}/signal`)](#79-unified-signal-relay)
   - [7.10 Call History & Details (`GET /api/calls`, `GET /api/calls/{id}`)](#710-call-history--details)
8. [SDP ও ICE Candidate সংক্রান্ত কঠোর নিয়ম (Critical Integrity Rules)](#8-sdp-ও-ice-candidate-সংক্রান্ত-কঠোর-নিয়ম)
9. [Flutter Client Integration Guide (Laravel Echo & WebSockets)](#9-flutter-client-integration-guide)

---

## 1. ভূমিকা ও আর্কিটেকচার

### Laravel Developer-এর মূল দায়িত্ব:
1. **Laravel সার্ভার শুধুমাত্র WebRTC Signaling Server হিসেবে কাজ করবে।**
2. Audio বা Video স্ট্রিম Laravel সার্ভারের মধ্য দিয়ে **প্রবাহিত হবে না** (P2P Direct Connection)।
3. Flutter ক্লায়েন্টদ্বয় (Caller ও Receiver) নিজেদের মধ্যে সরাসরি WebRTC কানেকশন তৈরি করবে।
4. Laravel Reverb / WebSockets শুধুমাত্র Call স্টেট কন্ট্রোল এবং Signaling Data (Offer, Answer, ICE Candidate) রিলে করবে।

---

## 2. প্রয়োজনীয় কম্পোনেন্টস

* **Framework:** Laravel 11 / 12
* **Language:** PHP 8.2+
* **Database:** MySQL
* **WebSockets / Signaling Server:** Laravel Reverb (`Broadcasting`)
* **Authentication:** Laravel Sanctum (Bearer Token)
* **Events:** `ShouldBroadcastNow` (Zero latency instant broadcast)
* **Broadcasting Channel Type:** `PrivateChannel`

---

## 3. ডাটাবেজ স্কিমা (`calls` Table)

| Field Name | Type | Constraints / Attributes | Description |
|---|---|---|---|
| `id` | `BIGINT UNSIGNED` | Primary Key, Auto Increment | কলের ইউনিক আইডি |
| `caller_id` | `BIGINT UNSIGNED` | Foreign Key -> `users.id`, Index | কলকারীর ইউজার আইডি |
| `receiver_id` | `BIGINT UNSIGNED` | Foreign Key -> `users.id`, Index | কল প্রাপকের ইউজার আইডি |
| `call_type` | `ENUM` | `'audio'`, `'video'` | কলের ধরন |
| `status` | `ENUM` | `'calling'`, `'ringing'`, `'accepted'`, `'rejected'`, `'busy'`, `'ended'`, `'missed'`, `'cancelled'` | কলের বর্তমান অবস্থা |
| `room_id` | `VARCHAR(100)` | Unique, Indexed | WebRTC রুম আইডেন্টিফায়ার |
| `started_at` | `TIMESTAMP` | Nullable | কল ইনিশিয়েট হওয়ার সময় |
| `answered_at` | `TIMESTAMP` | Nullable | কল রিসিভ হওয়ার সময় |
| `ended_at` | `TIMESTAMP` | Nullable | কল শেষ হওয়ার সময় |
| `ended_by` | `BIGINT UNSIGNED` | Foreign Key -> `users.id`, Nullable | যে ইউজার কলটি কেটে দিয়েছে |
| `created_at` | `TIMESTAMP` | Nullable | রেকর্ড তৈরির সময় |
| `updated_at` | `TIMESTAMP` | Nullable | রেকর্ড আপডেটের সময় |

---

## 4. কল লাইফসাইকেল ও সিগন্যালিং ফ্লো

```
[ Caller (User A - ID: 10) ]          [ Laravel Server + Reverb ]          [ Receiver (User B - ID: 25) ]
             |                                     |                                     |
             |--- 1. POST /api/calls ------------->|                                     |
             |    {"receiver_id": 25,              |                                     |
             |     "call_type": "video"}           |--- 2. Broadcast: call.incoming ---->|
             |<-- 3. Return Call Data -------------|    (Channel: private-user.25)       |
             |    (status: "calling", room_id: ...) |                                     |
             |                                     |                                     |
             |                                     |<-- 4. POST /api/calls/100/accept ---|
             |<-- 5. Broadcast: call.accepted -----|                                     |
             |    (Channel: private-user.10)       |                                     |
             |                                     |                                     |
    ============================= WebRTC Signaling Phase =============================
             |                                     |                                     |
             |--- 6. POST /api/calls/100/offer --->|                                     |
             |    (Unmodified SDP Offer)           |--- 7. Broadcast: webrtc.offer ----->|
             |                                     |    (Channel: private-user.25)       |
             |                                     |                                     |
             |                                     |<-- 8. POST /api/calls/100/answer ---|
             |<-- 9. Broadcast: webrtc.answer -----|       (Unmodified SDP Answer)       |
             |    (Channel: private-user.10)       |                                     |
             |                                     |                                     |
             |--- 10. POST /api/calls/100/ice-c. ->|--- 11. Broadcast: webrtc.ice_cand. >|
             |<-- 12. Broadcast: webrtc.ice_cand. -|<-- 13. POST /api/calls/100/ice-c. --|
             |                                     |                                     |
    ============================ P2P Direct Media Streaming ==========================
             |<=================== Direct Audio/Video WebRTC Stream ====================>|
```

---

## 5. Private Channel কনফিগারেশন

প্রত্যেক ইউজারের নিজস্ব প্রাইভেট চ্যানেল রয়েছে (`private-user.{user_id}`)। 

### `routes/channels.php`
```php
use Illuminate\Support\Facades\Broadcast;

// Private user channel authentication
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Call Room channel authentication
Broadcast::channel('call.{roomId}', function ($user, $roomId) {
    $call = \App\Models\Call::where('room_id', $roomId)->first();
    if ($call) {
        return (int) $user->id === (int) $call->caller_id || (int) $user->id === (int) $call->receiver_id;
    }
    return true;
});
```

---

## 6. Laravel Broadcasting Events

| Event Class | Broadcast Event Name | Target Private Channel | Dispatched When |
|---|---|---|---|
| `CallIncoming` | `call.incoming` | `private-user.{receiver_id}` | কলার নতুন কল শুরু করলে |
| `CallAccepted` | `call.accepted` | `private-user.{caller_id}` | রিসিভার কল এক্সেপ্ট করলে |
| `CallRejected` | `call.rejected` | `private-user.{caller_id}` | রিসিভার কল রিজেক্ট করলে |
| `CallCancelled` | `call.cancelled` | `private-user.{receiver_id}` | কলার কল কেটে দিলে (ধরার পূর্বে) |
| `CallEnded` | `call.ended` | `private-user.{target_user_id}` | যে কোনো পক্ষ কল শেষ করলে |
| `WebRTCOffer` | `webrtc.offer` | `private-user.{receiver_id}` | কলার WebRTC SDP Offer পাঠালে |
| `WebRTCAnswer` | `webrtc.answer` | `private-user.{caller_id}` | রিসিভার WebRTC SDP Answer পাঠালে |
| `WebRTCICECandidate` | `webrtc.ice_candidate` | `private-user.{target_user_id}` | কোনো পক্ষ ICE Candidate পাঠালে |

### Event Payloads:

#### `call.incoming`
```json
{
  "event": "call.incoming",
  "call_id": 100,
  "caller_id": 10,
  "caller_name": "Rahim Ahmed",
  "caller_avatar": "https://example.com/storage/avatars/10.jpg",
  "receiver_id": 25,
  "call_type": "video",
  "room_id": "call_9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d",
  "status": "calling",
  "created_at": "2026-08-30T03:45:00.000000Z"
}
```

#### `call.accepted`
```json
{
  "event": "call.accepted",
  "call_id": 100,
  "room_id": "call_9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d",
  "caller_id": 10,
  "receiver_id": 25,
  "receiver_name": "Ayesha Akter",
  "status": "accepted",
  "answered_at": "2026-08-30T03:45:05.000000Z"
}
```

#### `call.rejected`
```json
{
  "event": "call.rejected",
  "call_id": 100,
  "room_id": "call_9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d",
  "status": "rejected",
  "reason": "declined"
}
```

#### `call.cancelled`
```json
{
  "event": "call.cancelled",
  "call_id": 100,
  "room_id": "call_9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d",
  "status": "cancelled"
}
```

#### `call.ended`
```json
{
  "event": "call.ended",
  "call_id": 100,
  "room_id": "call_9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d",
  "status": "ended",
  "ended_by": 10,
  "ended_at": "2026-08-30T03:50:00.000000Z"
}
```

#### `webrtc.offer`
```json
{
  "event": "webrtc.offer",
  "call_id": 100,
  "room_id": "call_9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d",
  "type": "offer",
  "sdp": "v=0\r\no=- 4611731400431189761 2 IN IP4 127.0.0.1\r\ns=-\r\nt=0 0\r\na=group:BUNDLE 0 1\r\n..."
}
```

#### `webrtc.answer`
```json
{
  "event": "webrtc.answer",
  "call_id": 100,
  "room_id": "call_9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d",
  "type": "answer",
  "sdp": "v=0\r\no=- 5231731400431189761 2 IN IP4 127.0.0.1\r\ns=-\r\nt=0 0\r\na=group:BUNDLE 0 1\r\n..."
}
```

#### `webrtc.ice_candidate`
```json
{
  "event": "webrtc.ice_candidate",
  "call_id": 100,
  "room_id": "call_9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d",
  "candidate": "candidate:842163049 1 udp 1677729535 192.168.1.100 54321 typ srflx raddr 192.168.1.100 rport 54321 generation 0 ufrag abcd network-id 1",
  "sdpMid": "0",
  "sdpMLineIndex": 0
}
```

---

## 7. সম্পূর্ণ REST API এন্ডপয়েন্টস রেফারেন্স

### Authentication Headers:
সবগুলো এন্ডপয়েন্টে কলার/রিসিভারের অথেনটিকেশন টোকেন পাঠাতে হবে:
```http
Authorization: Bearer <SANCTUM_USER_TOKEN>
Content-Type: application/json
Accept: application/json
```

---

### 7.1 Create Call
* **Method:** `POST`
* **URL:** `/api/calls`

#### Request Body:
```json
{
  "receiver_id": 25,
  "call_type": "video"
}
```

#### Success Response (`201 Created`):
```json
{
  "success": true,
  "message": "Call initiated successfully",
  "call": {
    "id": 100,
    "caller_id": 10,
    "receiver_id": 25,
    "call_type": "video",
    "status": "calling",
    "room_id": "call_9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d",
    "started_at": "2026-08-30T03:45:00.000000Z"
  }
}
```

---

### 7.2 Accept Call
* **Method:** `POST`
* **URL:** `/api/calls/{call_id}/accept`

#### Success Response (`200 OK`):
```json
{
  "success": true,
  "call_id": 100,
  "room_id": "call_9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d",
  "status": "accepted"
}
```

---

### 7.3 Reject Call
* **Method:** `POST`
* **URL:** `/api/calls/{call_id}/reject`

#### Request Body (Optional):
```json
{
  "reason": "busy"
}
```

#### Success Response (`200 OK`):
```json
{
  "success": true,
  "call_id": 100,
  "status": "rejected"
}
```

---

### 7.4 Cancel Call
* **Method:** `POST`
* **URL:** `/api/calls/{call_id}/cancel`

#### Success Response (`200 OK`):
```json
{
  "success": true,
  "call_id": 100,
  "status": "cancelled"
}
```

---

### 7.5 End Call
* **Method:** `POST`
* **URL:** `/api/calls/{call_id}/end`

#### Success Response (`200 OK`):
```json
{
  "success": true,
  "call_id": 100,
  "status": "ended"
}
```

---

### 7.6 Relay SDP Offer
* **Method:** `POST`
* **URL:** `/api/calls/{call_id}/offer`

#### Request Body:
```json
{
  "sdp": "v=0\r\no=- 4611731400431189761 2 IN IP4 127.0.0.1\r\ns=-\r\nt=0 0\r\na=group:BUNDLE 0 1\r\n..."
}
```

#### Success Response (`200 OK`):
```json
{
  "success": true,
  "message": "Offer relayed successfully",
  "call_id": 100
}
```

---

### 7.7 Relay SDP Answer
* **Method:** `POST`
* **URL:** `/api/calls/{call_id}/answer`

#### Request Body:
```json
{
  "sdp": "v=0\r\no=- 5231731400431189761 2 IN IP4 127.0.0.1\r\ns=-\r\nt=0 0\r\na=group:BUNDLE 0 1\r\n..."
}
```

#### Success Response (`200 OK`):
```json
{
  "success": true,
  "message": "Answer relayed successfully",
  "call_id": 100
}
```

---

### 7.8 Relay ICE Candidate
* **Method:** `POST`
* **URL:** `/api/calls/{call_id}/ice-candidate`

#### Request Body:
```json
{
  "candidate": "candidate:842163049 1 udp 1677729535 192.168.1.100 54321 typ srflx raddr 192.168.1.100 rport 54321 generation 0 ufrag abcd network-id 1",
  "sdpMid": "0",
  "sdpMLineIndex": 0
}
```

#### Success Response (`200 OK`):
```json
{
  "success": true,
  "message": "ICE candidate relayed successfully"
}
```

---

### 7.9 Unified Signal Relay
* **Method:** `POST`
* **URL:** `/api/calls/{call_id}/signal`

> **নোট:** এই সিঙ্গেল এন্ডপয়েন্ট দিয়ে `type` ফিল্ডের উপর ভিত্তি করে Offer, Answer বা Candidate রিলে করা যায়।

---

### 7.10 Call History & Details
* **Get Call History:** `GET /api/calls?page=1`
* **Get Call Details:** `GET /api/calls/{call_id}`

---

## 8. SDP ও ICE Candidate সংক্রান্ত কঠোর নিয়ম

> [!CAUTION]
> **কখনোই কোনো SDP বা ICE Candidate মডিফাই করা যাবে না:**
> 1. **Zero String Alteration:** ক্লায়েন্ট যে হুবহু SDP স্ট্রিং (Newlines, Carriage Returns `\r\n`, Indentation সহ) পাঠাবে, Laravel কোনো ট্রিম বা পরিবর্তন না করে সরাসরি ইভেন্টে পাঠিয়ে দিবে।
> 2. **Zero Candidate Alteration:** `candidate`, `sdpMid`, `sdpMLineIndex` হুবহু ফরওয়ার্ড হবে।
> 3. **Instant Latency:** সব সিগন্যালিং ইভেন্টে `ShouldBroadcastNow` ইন্টারফেস ব্যবহার করা হয়েছে যাতে কিউ ডিলে ছাড়া তৎক্ষণাৎ ডেলিভারি নিশ্চিত হয়।

---

## 9. Flutter Client Integration Guide

### ১. Laravel Echo ইনিশিয়ালাইজেশন:
```dart
import 'package:laravel_echo/laravel_echo.dart';
import 'package:pusher_client/pusher_client.dart';

PusherClient pusher = PusherClient(
  'your-reverb-app-key',
  PusherOptions(
    host: 'your-domain.com',
    wsPort: 8080,
    wssPort: 443,
    encrypted: true,
    auth: PusherAuth(
      'https://your-domain.com/broadcasting/auth',
      headers: {
        'Authorization': 'Bearer $sanctumToken',
        'Accept': 'application/json',
      },
    ),
  ),
  enableLogging: true,
);

Echo echo = Echo(
  broadcaster: EchoBroadcasterType.Pusher,
  client: pusher,
);
```

### ২. ইউজার প্রাইভেট চ্যানেলে লিসেন করা:
```dart
// Receiver App (User ID: 25)
echo.private('user.$myUserId')
  .listen('.call.incoming', (data) {
    print("Incoming Call Received: $data");
    // Show Incoming Call Screen (Ringtone, Accept / Reject buttons)
  })
  .listen('.call.accepted', (data) {
    print("Call Accepted by Receiver! Start WebRTC Offer...");
    // Caller creates SDP Offer & sends to /api/calls/{id}/offer
  })
  .listen('.webrtc.offer', (data) async {
    print("SDP Offer Received: ${data['sdp']}");
    // Receiver sets Remote Description, creates SDP Answer & sends to /api/calls/{id}/answer
  })
  .listen('.webrtc.answer', (data) async {
    print("SDP Answer Received: ${data['sdp']}");
    // Caller sets Remote Description
  })
  .listen('.webrtc.ice_candidate', (data) async {
    print("ICE Candidate Received: ${data['candidate']}");
    // Add ICE candidate to RTCPeerConnection
  })
  .listen('.call.ended', (data) {
    print("Call Ended by peer");
    // Close WebRTC connection and dispose tracks
  })
  .listen('.call.cancelled', (data) {
    print("Call Cancelled by caller");
    // Dismiss incoming call screen
  });
```
