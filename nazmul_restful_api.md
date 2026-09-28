# 🚀 Chinchins Live — RESTful API & WebRTC Socket Specification
> **Authoritative Backend API Documentation for Mobile (Flutter iOS & Android) & WebRTC**  
> **Production Base URL:** `https://chinchins.live`  
> **WebSocket URL:** `wss://chinchins.live/app/chinchins-app-key` (Reverb / Pusher Protocol)  
> **LiveKit RTC Server:** `wss://chinchins.live/livekit`  

---

## 📑 Quick Navigation
1. [Authentication & Global Headers](#1-authentication--global-headers)
2. [Mandate 1: Call Initiation & Balance Check](#2-mandate-1-call-initiation--balance-check)
3. [Mandate 2: Host Stream Hold & Resume State](#3-mandate-2-host-stream-hold--resume-state)
4. [Mandate 3: Join Request Deduplication](#4-mandate-3-join-request-deduplication)
5. [Complete 1-on-1 WebRTC Calling Flow](#5-complete-1-on-1-webrtc-calling-flow)
6. [WebRTC Signaling Endpoints (SDP / ICE)](#6-webrtc-signaling-endpoints-sdp--ice)
7. [Pusher / Reverb Socket Channels & Events](#7-pusher--reverb-socket-channels--events)
8. [Flutter Mobile UI & State Cleanup Guidelines](#8-flutter-mobile-ui--state-cleanup-guidelines)
9. [Payment Options & Coin Packages](#9-payment-options--coin-packages)
10. [Live Streaming & Party Room Endpoints](#10-live-streaming--party-room-endpoints)

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
**Also supports:** `POST /api/calls`, `POST /api/call/make-call`

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
    "room_id": "call_video_5_12_1727508000_aBcD",
    "call_type": "video",
    "status": "calling",
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
1. **Host Accepts Private Call (`POST /api/calls/{id}/accept` or `POST /api/call/accept`):**
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

2. **Host Ends/Declines Private Call (`POST /api/calls/{id}/end`, `POST /api/calls/{id}/reject`, `POST /api/calls/{id}/cancel`):**
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

## 5. Complete 1-on-1 WebRTC Calling Flow

### Endpoints Overview:

| Action | HTTP Method | Endpoint | Description |
|---|---|---|---|
| **Initiate Call** | `POST` | `/api/calls` or `/api/call/initiate` | Caller starts ringing (validates balance) |
| **Accept Call** | `POST` | `/api/calls/{call}/accept` | Receiver accepts call (broadcasts StreamHoldEvent if live) |
| **Reject Call** | `POST` | `/api/calls/{call}/reject` | Receiver declines call (broadcasts StreamResumeEvent if live) |
| **Cancel Call** | `POST` | `/api/calls/{call}/cancel` | Caller cancels call before answer |
| **End Call** | `POST` | `/api/calls/{call}/end` | Either party ends call (finalizes billing & resumes stream) |
| **Relay Offer (SDP)** | `POST` | `/api/calls/{call}/offer` | Relay WebRTC SDP Offer to peer |
| **Relay Answer (SDP)**| `POST` | `/api/calls/{call}/answer` | Relay WebRTC SDP Answer to peer |
| **Relay ICE Candidate**| `POST`| `/api/calls/{call}/ice-candidate` | Relay ICE Candidate to peer |
| **Heartbeat Billing** | `POST` | `/api/call/deduct-interval` | Periodic per-second/minute billing pulse |
| **ICE Servers** | `GET` | `/api/calls/ice-servers` | Get STUN/TURN server credentials |

---

### `POST /api/calls/{call}/accept`
#### Request:
```json
{}
```
#### Response (`200 OK`):
```json
{
  "success": true,
  "call_id": 1420,
  "room_id": "call_video_5_12_1727508000_aBcD",
  "status": "accepted"
}
```

---

### `POST /api/calls/{call}/end`
#### Request:
```json
{
  "duration_seconds": 185
}
```
#### Response (`200 OK`):
```json
{
  "success": true,
  "call_id": 1420,
  "status": "completed",
  "call_status": "completed"
}
```

---

## 6. WebRTC Signaling Endpoints (SDP / ICE)

### 1. `POST /api/calls/{call}/offer`
Relays WebRTC SDP Offer from caller to receiver via socket:
```json
{
  "sdp": "v=0\r\no=- 461173... IN IP4 0.0.0.0\r\ns=-\r\nt=0 0\r\n...",
  "type": "offer"
}
```

### 2. `POST /api/calls/{call}/answer`
Relays WebRTC SDP Answer from receiver to caller via socket:
```json
{
  "sdp": "v=0\r\no=- 461173... IN IP4 0.0.0.0\r\ns=-\r\nt=0 0\r\n...",
  "type": "answer"
}
```

### 3. `POST /api/calls/{call}/ice-candidate`
Relays ICE candidate:
```json
{
  "candidate": "candidate:842163049 1 udp 1677729535 192.168.1.5 54321 typ host",
  "sdpMid": "0",
  "sdpMLineIndex": 0
}
```

---

## 7. Pusher / Reverb Socket Channels & Events

### Channel Naming:
- **User Channel (Pusher format):** `private-user.{userId}`
- **Call Presence Channel:** `presence-call.{callId}`

### Call Signaling Socket Events:

| Channel | Event Name | Description & Payload |
|---|---|---|
| `private-user.{receiverId}` | `IncomingCallEvent` / `call.incoming` | Notifies receiver phone of incoming call popup. Contains `call_id`, `caller` object, `room_id`, `call_type`. |
| `private-user.{callerId}` | `CallAccepted` / `call.accepted` | Notifies caller that receiver accepted. Ready to start WebRTC offer/answer exchange. |
| `private-user.{callerId}` | `CallRejected` / `call.rejected` | Notifies caller that call was declined. |
| `private-user.{receiverId}` | `CallCancelled` / `call.cancelled` | Notifies receiver that caller stopped ringing. Dismiss call dialog. |
| `private-user.{targetUserId}`| `CallEndedEvent` / `call.ended` | Notifies peer that call was terminated. Immediately close WebRTC peer connection. |
| `private-user.{targetUserId}`| `WebRTCOffer` / `webrtc.offer` | Receives peer's SDP offer. |
| `private-user.{targetUserId}`| `WebRTCAnswer` / `webrtc.answer` | Receives peer's SDP answer. |
| `private-user.{targetUserId}`| `WebRTCICECandidate` / `webrtc.ice-candidate` | Receives peer's ICE Candidate. |

---

## 8. Flutter Mobile UI & State Cleanup Guidelines

### 1. UI Screen Layout Cleanup
- **Top Bar:** Display remote user's name, avatar, and call duration timer.
- **Center Area:** Clean video views (local small floating pip, remote fullscreen view).
- **Remove Duplicate UI:** Completely remove the large duplicate middle profile picture and middle Cancel button.
- **Bottom Bar:** Single red "End Call" button with Audio Mute and Camera Switch toggles.

### 2. Receiver Side Incoming Call Listener
In Flutter App initialization / home controller, subscribe to the user's private channel:
```dart
final channel = pusher.subscribe('private-user.$myUserId');

// Listen for incoming call
channel.bind('IncomingCallEvent', (PusherEvent? event) {
  if (event != null && event.data != null) {
    final data = jsonDecode(event.data!);
    // Open Incoming Call Dialog / Screen with Caller Name, Avatar, Accept & Reject buttons
    showIncomingCallScreen(data);
  }
});

channel.bind('call.incoming', (PusherEvent? event) {
  if (event != null && event.data != null) {
    final data = jsonDecode(event.data!);
    showIncomingCallScreen(data);
  }
});
```

### 3. WebRTC Peer Connection Cleanup on Call End
When call ends (`CallEndedEvent`, `CallRejected`, or User taps End button):
```dart
void cleanupCallSession() {
  try {
    localRenderer?.srcObject = null;
    remoteRenderer?.srcObject = null;
    localStream?.getTracks().forEach((track) => track.stop());
    localStream?.dispose();
    peerConnection?.close();
    peerConnection = null;
  } catch (e) {
    print('WebRTC cleanup error: $e');
  }
  // Pop call screen back to previous view
  Navigator.of(context).pop();
}
```

---

## 9. Payment Options & Coin Packages

### `GET /api/payment/options`
Returns available deposit payment channels (bKash, Nagad, Rocket, Reseller).

---

## 10. Live Streaming & Party Room Endpoints

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
*Verified on Chinchins Live Production Architecture*
