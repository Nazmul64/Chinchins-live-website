# 🚀 Chinchins Live — Flutter RESTful API & WebSocket Specification
> **Authoritative Backend API Documentation for Mobile (Flutter iOS & Android)**  
> **Production Base URL:** `https://chinchins.live`  
> **WebSocket URL:** `wss://chinchins.live/app/chinchins-app-key` (Reverb / Pusher Protocol)  
> **LiveKit RTC Server:** `wss://chinchins.live/livekit`  

---

## 📑 Quick Navigation
1. [Authentication & Global Headers](#1-authentication--global-headers)
2. [Mandate 1: Call Initiation & Balance Check](#2-mandate-1-call-initiation--balance-check)
3. [Mandate 2: Host Stream Hold & Resume State](#3-mandate-2-host-stream-hold--resume-state)
4. [Mandate 3: Join Request Deduplication](#4-mandate-3-join-request-deduplication)
5. [Complete 1-on-1 Video/Audio Calling Flow](#5-complete-1-on-1-videoaudio-calling-flow)
6. [WebRTC Signaling & ICE Servers](#6-webrtc-signaling--ice-servers)
7. [Payment Options & Coin Packages](#7-payment-options--coin-packages)
8. [Live Streaming & Party Room Endpoints](#8-live-streaming--party-room-endpoints)
9. [Socket Events & Real-Time Channels](#9-socket-events--real-time-channels)
10. [Flutter Dart Client Integration Snippet](#10-flutter-dart-client-integration-snippet)

---

## 1. Authentication & Global Headers

All authenticated requests must include the user's Bearer token in the `Authorization` header:

```http
Authorization: Bearer <SANCTUM_TOKEN>
Accept: application/json
Content-Type: application/json
```

---

## 2. Mandate 1: Call Initiation & Balance Check

### Rules:
- Before ringing the receiver, the backend validates that the caller has at least **1 minute of coins** (`call_rate_per_minute` / `video_call_rate`, default: `100` coins).
- If the balance is insufficient, the call **will not ring** and returns **`402 Payment Required`**.

### `POST /api/call/initiate`
**Also supports:** `POST /api/call/make-call`, `POST /api/calls`

#### Request:
```json
{
  "receiver_id": 12,
  "call_type": "video"
}
```

#### Success Response (`200 OK` / `201 Created`):
```json
{
  "status": true,
  "success": true,
  "message": "Call initiated! Ringing receiver...",
  "data": {
    "call_id": 1420,
    "session_id": 1420,
    "channel_name": "call_video_5_12_1727508000_aBcD",
    "call_type": "video",
    "status": "ringing",
    "rate_per_minute": 100,
    "caller_coins": 1500,
    "max_call_minutes": 15,
    "max_call_seconds": 900,
    "receiver": {
      "id": 12,
      "account_id": "8801911223344",
      "name": "Ruma Akter",
      "avatar": "https://chinchins.live/storage/avatars/ruma.jpg"
    }
  }
}
```

#### Insufficient Balance Response (`402 Payment Required`):
```json
{
  "success": false,
  "status": false,
  "can_call": false,
  "code": "INSUFFICIENT_BALANCE",
  "message": "Insufficient balance to start call",
  "user_balance": 20,
  "required_coins": 100,
  "rate_per_minute": 100,
  "is_low_balance": true,
  "show_recharge_sheet": true,
  "redirect_to_deposit": true,
  "deposit_url": "/deposit"
}
```

---

## 3. Mandate 2: Host Stream Hold & Resume State

When a host who is actively live streaming accepts a 1-on-1 private call, the live stream room is **automatically paused** for all audience members, and resumes when the private call finishes.

### Flow:
1. **Host Accepts Private Call (`POST /api/call/accept`):**
   - Backend automatically broadcasts `StreamHoldEvent` to `live-stream.{stream_id}`, `presence-stream.{stream_id}`, `live-room.{stream_id}`:
   ```json
   {
     "event": "StreamHoldEvent",
     "stream_id": "58",
     "room_id": "58",
     "status": "paused",
     "is_paused": true,
     "message": "I will come back soon",
     "timestamp": "2026-09-28T09:00:00Z"
   }
   ```
   - **Flutter Live Player Action:** Display overlay: *"Host is currently in a private call. Please wait, host will come back soon."*

2. **Host Ends/Declines Private Call (`POST /api/call/end`, `POST /api/call/reject`, `POST /api/call/cancel`):**
   - Backend automatically broadcasts `StreamResumeEvent` to `live-stream.{stream_id}`, `presence-stream.{stream_id}`:
   ```json
   {
     "event": "StreamResumeEvent",
     "stream_id": "58",
     "room_id": "58",
     "status": "live",
     "is_paused": false,
     "message": "Host is back live",
     "timestamp": "2026-09-28T09:05:00Z"
   }
   ```
   - **Flutter Live Player Action:** Remove pause overlay and resume real-time video stream.

---

## 4. Mandate 3: Join Request Deduplication

Prevents duplicate pending join requests from audience to host.

### `POST /api/live/request-join`
**Also supports:** `POST /api/live/{id}/request-join`

#### Request:
```json
{
  "room_id": 58,
  "host_id": 12
}
```

#### Success Response (`200 OK`):
```json
{
  "status": true,
  "success": true,
  "message": "Co-host request sent to host.",
  "data": {
    "request_id": 302,
    "room_id": "58",
    "status": "pending"
  }
}
```

#### Duplicate Pending Response (`400 Bad Request`):
```json
{
  "status": false,
  "success": false,
  "code": "REQUEST_ALREADY_PENDING",
  "message": "Request already pending",
  "data": {
    "request_id": 302,
    "status": "pending"
  }
}
```

---

## 5. Complete 1-on-1 Video/Audio Calling Flow

### Endpoints Overview:

| Action | HTTP Method | Endpoint | Description |
|---|---|---|---|
| **Initiate Call** | `POST` | `/api/call/initiate` | Caller starts ringing (validates balance) |
| **Accept Call** | `POST` | `/api/call/accept` | Receiver accepts call (broadcasts StreamHoldEvent if live) |
| **Reject Call** | `POST` | `/api/call/reject` | Receiver declines call (broadcasts StreamResumeEvent if live) |
| **Cancel Call** | `POST` | `/api/call/cancel` | Caller cancels call before answer |
| **End Call** | `POST` | `/api/call/end` | Either party ends call (finalizes billing & resumes stream) |
| **Heartbeat Billing** | `POST` | `/api/call/deduct-interval` | Periodic per-second/minute billing pulse |
| **Check Incoming** | `GET` | `/api/call/incoming` | Poll/check active incoming ringing call |
| **Call Status** | `GET` | `/api/call/status/{id}` | Poll call connection/termination status |

---

### `POST /api/call/accept`
#### Request:
```json
{
  "call_id": 1420,
  "channel_name": "call_video_5_12_1727508000_aBcD"
}
```
#### Response (`200 OK`):
```json
{
  "status": true,
  "message": "Call accepted and connected successfully! Start audio/video media stream.",
  "data": {
    "call_id": 1420,
    "channel_name": "call_video_5_12_1727508000_aBcD",
    "status": "connected",
    "started_at": "2026-09-28T09:00:00Z",
    "rate_per_minute": 100
  }
}
```

---

### `POST /api/call/end`
#### Request:
```json
{
  "call_id": 1420,
  "channel_name": "call_video_5_12_1727508000_aBcD",
  "duration_seconds": 185
}
```
#### Response (`200 OK`):
```json
{
  "status": true,
  "success": true,
  "call_status": "completed",
  "message": "Call session ended and marked completed successfully. Real-time disconnect broadcasted.",
  "data": {
    "call_id": 1420,
    "duration_seconds": 185
  }
}
```

---

### `POST /api/call/deduct-interval` (Billing Pulse)
Call every 10–30 seconds during an active call.
#### Request:
```json
{
  "call_id": 1420,
  "interval_seconds": 30,
  "elapsed_seconds": 90
}
```
#### Success Response (`200 OK`):
```json
{
  "status": true,
  "should_blur_video": false,
  "data": {
    "current_coins": 1450,
    "coins_deducted": 50,
    "host_earned_coins": 25,
    "admin_revenue_coins": 25,
    "rate_per_minute": 100,
    "can_continue": true,
    "should_terminate_call": false
  }
}
```
#### Low Balance Alert (`200 OK` with `LOW_BALANCE_DEPOSIT_REQUIRED`):
```json
{
  "status": false,
  "code": "LOW_BALANCE_DEPOSIT_REQUIRED",
  "message": "Your balance is insufficient to continue calling. Please deposit/recharge coins now.",
  "current_coins": 0,
  "required_coins": 50,
  "should_terminate_call": false,
  "should_blur_video": true,
  "show_recharge_sheet": true,
  "redirect_to_deposit": true
}
```

---

## 6. WebRTC Signaling & ICE Servers

### `GET /api/call/ice-servers`
Returns high-availability STUN and TURN relay credentials.

#### Response:
```json
{
  "status": true,
  "iceServers": [
    {
      "urls": [
        "stun:stun.l.google.com:19302",
        "stun:stun1.l.google.com:19302",
        "stun:stun.cloudflare.com:3478"
      ]
    },
    {
      "urls": [
        "turn:openrelay.metered.ca:80",
        "turn:openrelay.metered.ca:443",
        "turn:openrelay.metered.ca:443?transport=tcp"
      ],
      "username": "openrelay",
      "credential": "openrelay"
    }
  ]
}
```

### `POST /api/call/signal/send`
Send SDP Offer, Answer, ICE Candidate, or Bye.
```json
{
  "call_id": 1420,
  "type": "offer", // "offer" | "answer" | "candidate" | "bye"
  "payload": { "sdp": "..." }
}
```

---

## 7. Payment Options & Coin Packages

### `GET /api/payment/options`
Returns payment gateways (bKash, Nagad, Rocket, Reseller).

#### Response (`200 OK`):
```json
{
  "status": true,
  "message": "Payment options retrieved successfully.",
  "header_title": "Payment options",
  "options_title": "Options for you",
  "button_text": "Continue",
  "default_selected": "reseller",
  "options": [
    {
      "key": "reseller",
      "title": "Agent / Reseller Deposit",
      "description": "Instant coin credit through official resellers",
      "icon_url": "https://chinchins.live/assets/icons/reseller.png",
      "is_active": true
    },
    {
      "key": "bkash",
      "title": "bKash Direct",
      "description": "Pay with bKash personal / merchant",
      "icon_url": "https://chinchins.live/assets/icons/bkash.png",
      "is_active": true
    }
  ]
}
```

### `GET /api/coin-packages`
```json
{
  "status": true,
  "data": [
    {
      "id": 1,
      "name": "Standard Starter",
      "coins": 32000,
      "bonus_coins": 8000,
      "total_coins": 40000,
      "price_bdt": 550,
      "formatted_price": "৳ 550"
    }
  ]
}
```

---

## 8. Live Streaming & Party Room Endpoints

| Method | Endpoint | Description |
|---|---|---|
| `POST` | `/api/live/start` | Host starts live stream |
| `POST` | `/api/live/end` | Host ends live stream |
| `GET` | `/api/live/list` | List active live streams |
| `POST` | `/api/live/request-join` | Audience requests to co-host / join |
| `POST` | `/api/live/accept-join` | Host accepts co-host join |
| `POST` | `/api/live/reject-join` | Host rejects co-host join |
| `POST` | `/api/party/create` | Create 4/6/9 seat party room |
| `POST` | `/api/party/{id}/join-seat` | Take a seat in party room |

---

## 9. Socket Events & Real-Time Channels

### Channel Naming Convention:
- **User Private Channel:** `private-user.{userId}`
- **Live Stream Public/Presence Channel:** `live-stream.{streamId}`, `presence-stream.{streamId}`
- **Call Session Channel:** `presence-call.{callId}`

### Real-time Events List:

| Channel | Event Name | Payload Highlights |
|---|---|---|
| `private-user.{receiverId}` | `IncomingCallEvent` / `call.incoming` | `call_id`, `caller`, `room_name`, `call_type` |
| `private-user.{callerId}` | `PrivateCallAcceptedEvent` / `call.accepted` | `call_id`, `status: connected` |
| `private-user.{callerId}` | `PrivateCallRejectedEvent` / `call.rejected` | `call_id`, `reason: declined` |
| `private-user.{receiverId}` | `PrivateCallEndedEvent` / `call.cancelled` | `call_id`, `reason: cancelled` |
| `live-stream.{streamId}` | `StreamHoldEvent` | `status: paused`, `message: "I will come back soon"` |
| `live-stream.{streamId}` | `StreamResumeEvent` | `status: live`, `message: "Host is back live"` |
| `live-stream.{streamId}` | `CoHostRequestReceived` | `request_id`, `user` |

---

## 10. Flutter Dart Client Integration Snippet

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;

class ChinchinsApi {
  static const String baseUrl = 'https://chinchins.live/api';

  static Future<Map<String, dynamic>> initiateCall({
    required String token,
    required int receiverId,
    String callType = 'video',
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/call/initiate'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: jsonEncode({
        'receiver_id': receiverId,
        'call_type': callType,
      }),
    );

    if (response.statusCode == 200 || response.statusCode == 201) {
      return jsonDecode(response.body);
    } else if (response.statusCode == 402) {
      // ⚠️ Insufficient Balance -> Prompt User to Recharge
      final errorData = jsonDecode(response.body);
      throw InsufficientBalanceException(errorData['message'] ?? 'Insufficient balance to start call');
    } else {
      throw Exception('Call failed: ${response.body}');
    }
  }

  static Future<void> requestJoinLive({
    required String token,
    required int streamId,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/live/request-join'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: jsonEncode({'room_id': streamId}),
    );

    if (response.statusCode == 400) {
      final data = jsonDecode(response.body);
      if (data['code'] == 'REQUEST_ALREADY_PENDING') {
        // Handle duplicate join request feedback to user
        print('Join request is already pending host approval.');
      }
    }
  }
}

class InsufficientBalanceException implements Exception {
  final String message;
  InsufficientBalanceException(this.message);
  @override
  String toString() => message;
}
```

---
*Generated & Verified on Production Backend — Chinchins Live*
