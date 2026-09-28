# ⚡ Chinchins Live — Dynamic Dual-Engine (VPS WebRTC vs Agora Cloud) RESTful API & Calling Specification
> **File:** `call_divided_restful_api.md`  
> **Production Base URL:** `https://chinchins.live`  
> **WebSocket URL:** `wss://chinchins.live/app/chinchins-app-key` (Laravel Reverb Signaling)  
> **Agora App ID:** `c13c72df342d4a1386da678ba4c95f13` (Dynamic HMAC-SHA256 Token)  
> **Admin Switch Location:** `https://chinchins.live/admin/settings` &rarr; `⚡ Streaming Engine (Agora vs VPS)`  

---

## 📑 Quick Navigation
1. [Dynamic Dual-Engine Overview](#1-dynamic-dual-engine-overview)
2. [Engine Configuration & Bootstrap Endpoint](#2-engine-configuration--bootstrap-endpoint)
3. [1-on-1 Call Initiation (Dynamic Engine Dispatch)](#3-1-on-1-call-initiation-dynamic-engine-dispatch)
4. [Receiver Incoming Call Socket & Token Handling](#4-receiver-incoming-call-socket--token-handling)
5. [Call Accept & Connect Flow](#5-call-accept--connect-flow)
6. [WebRTC vs Agora Flutter Execution Logic](#6-webrtc-vs-agora-flutter-execution-logic)
7. [Host Stream Hold & Resume State](#7-host-stream-hold--resume-state)
8. [Call End & Clean State Reset](#8-call-end--clean-state-reset)
9. [Heartbeat Billing & Balance Deduction](#9-heartbeat-billing--balance-deduction)

---

## 1. Dynamic Dual-Engine Overview

Chinchins Live backend supports **1-Click 100% Dynamic Engine Switching** between two calling & streaming architectures via Admin Panel (`/admin/settings`):

| Feature | Option A: Hostinger VPS (WebRTC + Reverb) | Option B: Agora Cloud Engine (RTC / RTM) |
|---|---|---|
| **Driver Key** | `vps_webrtc` | `agora` |
| **Signaling** | Laravel Reverb WebSocket (`wss://chinchins.live/app/...`) | Agora RTM / Pusher + Dynamic Token |
| **Media Transport** | Peer-to-Peer WebRTC + STUN/TURN | Agora SD-RTN™ Enterprise Cloud |
| **Token Mechanism** | Native WebRTC SDP Offer/Answer Relay | Dynamic HMAC-SHA256 Token Builder |
| **Admin Switch** | Selected in Admin &rarr; `Streaming Engine` | Selected in Admin &rarr; `Streaming Engine` |
| **APK Rebuild** | **Zero rebuild needed** (Dynamic runtime dispatch) | **Zero rebuild needed** (Dynamic runtime dispatch) |

---

## 2. Engine Configuration & Bootstrap Endpoint

### `GET /api/stream/driver` (or `GET /api/call/config`)
Flutter mobile app queries this endpoint to detect the active engine.

#### Response when VPS WebRTC is Active (`200 OK`):
```json
{
  "success": true,
  "status": true,
  "data": {
    "active_driver": "vps_webrtc",
    "active_engine": "vps_webrtc",
    "is_agora": false,
    "is_vps_webrtc": true,
    "agora_app_id": null,
    "signaling_host": "chinchins.live",
    "signaling_port": 443,
    "enable_video_call": true,
    "enable_audio_call": true,
    "enable_live_stream": true
  }
}
```

#### Response when Agora Cloud is Active (`200 OK`):
```json
{
  "success": true,
  "status": true,
  "data": {
    "active_driver": "agora",
    "active_engine": "agora",
    "is_agora": true,
    "is_vps_webrtc": false,
    "agora_app_id": "c13c72df342d4a1386da678ba4c95f13",
    "token_expire_seconds": 86400,
    "enable_video_call": true,
    "enable_audio_call": true,
    "enable_live_stream": true
  }
}
```

---

## 3. 1-on-1 Call Initiation (Dynamic Engine Dispatch)

### `POST /api/calls` (or `POST /api/call/initiate`)

#### Request:
```json
{
  "receiver_id": 12,
  "call_type": "video"
}
```

#### Success Response when Engine is Agora (`201 Created`):
```json
{
  "success": true,
  "message": "Call initiated successfully",
  "call": {
    "id": 1420,
    "caller_id": 5,
    "receiver_id": 12,
    "call_type": "video",
    "status": "calling",
    "room_id": "call_6df9680_1727508000",
    "channel_name": "call_6df9680_1727508000",
    "engine": "agora",
    "driver": "agora",
    "is_agora": true,
    "agora_app_id": "c13c72df342d4a1386da678ba4c95f13",
    "agora_token": "007eJxTYJj2e87u978...",
    "started_at": "2026-09-28T10:00:00Z"
  }
}
```

#### Success Response when Engine is VPS WebRTC (`201 Created`):
```json
{
  "success": true,
  "message": "Call initiated successfully",
  "call": {
    "id": 1420,
    "caller_id": 5,
    "receiver_id": 12,
    "call_type": "video",
    "status": "calling",
    "room_id": "call_6df9680_1727508000",
    "channel_name": "call_6df9680_1727508000",
    "engine": "vps_webrtc",
    "driver": "vps_webrtc",
    "is_agora": false,
    "agora_app_id": null,
    "agora_token": null,
    "started_at": "2026-09-28T10:00:00Z"
  }
}
```

---

## 4. Receiver Incoming Call Socket & Token Handling

When Caller initiates a call, backend broadcasts to `private-user.{receiverId}`:

### Socket Event: `IncomingCallEvent` / `call.incoming`
```json
{
  "event": "call.incoming",
  "id": 1420,
  "call_id": 1420,
  "channel_name": "call_6df9680_1727508000",
  "call_type": "video",
  "caller_id": 5,
  "engine": "agora", 
  "driver": "agora",
  "is_agora": true,
  "agora_app_id": "c13c72df342d4a1386da678ba4c95f13",
  "agora_token": "007eJxTYJ...", 
  "caller": {
    "id": 5,
    "name": "Kamrul Hasan",
    "display_name": "Kamrul Hasan",
    "avatar_url": "https://chinchins.live/storage/avatars/user5.jpg",
    "level": "Lv3"
  },
  "status": "calling"
}
```

---

## 5. Call Accept & Connect Flow

### `POST /api/calls/{call_id}/accept`

#### Response (`200 OK`):
```json
{
  "success": true,
  "call_id": 1420,
  "room_id": "call_6df9680_1727508000",
  "channel_name": "call_6df9680_1727508000",
  "status": "accepted",
  "engine": "agora",
  "driver": "agora",
  "is_agora": true,
  "agora_app_id": "c13c72df342d4a1386da678ba4c95f13",
  "agora_token": "007eJxTYJ..."
}
```

---

## 6. WebRTC vs Agora Flutter Execution Logic

Flutter app dynamically switches engines based on `call.engine`:

```dart
if (callData['engine'] == 'agora' || callData['is_agora'] == true) {
  // 🟢 1. Initialize Agora Engine
  await _rtcEngine.initialize(RtcEngineContext(appId: callData['agora_app_id']));
  await _rtcEngine.enableVideo();
  await _rtcEngine.joinChannel(
    token: callData['agora_token'],
    channelId: callData['channel_name'],
    uid: myUserId,
    options: const ChannelMediaOptions(
      clientRoleType: ClientRoleType.clientRoleBroadcaster,
    ),
  );
} else {
  // 🔵 2. Initialize Self-Hosted VPS WebRTC Engine
  await initWebRtcPeerConnection();
  // Exchange SDP Offer / Answer through /api/calls/{id}/offer & /answer
}
```

---

## 7. Host Stream Hold & Resume State

When a host who is currently live accepts a 1-on-1 call:
1. **Live Stream Audience receives:** `StreamHoldEvent` &rarr; Stream Paused with `"Host is in a private call. Please wait, host will come back soon."`
2. **When 1-on-1 Call Ends:** Audience receives `StreamResumeEvent` &rarr; Video automatically unpauses and resumes.

---

## 8. Call End & Clean State Reset

### `POST /api/calls/{call_id}/end`
#### Request:
```json
{
  "duration_seconds": 120
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

#### Flutter State Cleanup:
```dart
void disposeCall() {
  if (isAgora) {
    _rtcEngine.leaveChannel();
    _rtcEngine.release();
  } else {
    _localStream?.dispose();
    _peerConnection?.close();
    _peerConnection = null;
  }
  Navigator.of(context).pop();
}
```

---

## 9. Heartbeat Billing & Balance Deduction

### `POST /api/call/deduct-interval`
Send every 10–30 seconds during call to deduct user balance.

#### Request:
```json
{
  "call_id": 1420,
  "interval_seconds": 30,
  "elapsed_seconds": 60
}
```

---
*Verified on Chinchins Live Production Architecture*
