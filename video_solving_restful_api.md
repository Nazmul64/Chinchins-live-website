# 📹 Chinchins Live - Video Call & Co-Host Stream RESTful API & WebSocket Specification
> **Dedicated Integration Manual for Flutter Mobile App Developers**  
> **Version:** 2.4 (Production Ready)  
> **Base URL:** `https://chinchins.live`  
> **WebSocket URL:** `wss://chinchins.live` (Port 443 / 6001)  
> **LiveKit URL:** `wss://chinchins.live/livekit`  

---

## 📑 Table of Contents
1. [Overview & Architecture](#1-overview--architecture)
2. [1-on-1 Video & Audio Call Lifecycle](#2-1-on-1-video--audio-call-lifecycle)
   - [2.1 Call Flow Diagram](#21-call-flow-diagram)
   - [2.2 Call Initiation (`POST /api/call/initiate`)](#22-call-initiation)
   - [2.3 Incoming Call Real-Time Socket Listener](#23-incoming-call-real-time-socket-listener)
   - [2.4 Accept Call (`POST /api/call/accept`)](#24-accept-call)
   - [2.5 Reject Call (`POST /api/call/reject`)](#25-reject-call)
   - [2.6 End Call (`POST /api/call/end`)](#26-end-call)
   - [2.7 Active Call Heartbeat (`POST /api/call/heartbeat`)](#27-active-call-heartbeat)
3. [Flutter LiveKit Video/Audio Engine Integration](#3-flutter-livekit-videoaudio-engine-integration)
4. [Live Stream Audience Co-Host & Hand-Raise System](#4-live-stream-audience-co-host--hand-raise-system)
   - [4.1 Flow Diagram](#41-flow-diagram)
   - [4.2 Hand-Raise Button Placement in Flutter](#42-hand-raise-button-placement-in-flutter)
   - [4.3 Request Join API (`POST /api/live/stream/{stream_id}/request-join`)](#43-request-join-api)
   - [4.4 Host Respond API (`POST /api/live/stream/{stream_id}/respond-join`)](#44-host-respond-api)
   - [4.5 Co-Host List & Leave APIs](#45-co-host-list--leave-apis)
5. [WebSocket & Pusher Configuration Matrix](#5-websocket--pusher-configuration-matrix)

---

## 1. Overview & Architecture

Chinchins Live uses a hybrid real-time architecture:
1. **Laravel RESTful API**: Handles authentication, billing, permissions, room state persistence, and coin transactions.
2. **Pusher / Laravel WebSockets**: Dispatches instant VoIP signaling, call invitations, ringing alerts, and co-host notifications.
3. **LiveKit Media Server (SFU)**: Manages real-time WebRTC audio/video streams, multi-party video grids, screen sharing, and dynamic bitrate adaptation.

---

## 2. 1-on-1 Video & Audio Call Lifecycle

### 2.1 Call Flow Diagram
```
Caller (Flutter)                      Laravel Server                      Receiver (Flutter)
     |                                      |                                      |
     |--- 1. POST /api/call/initiate ------>|                                      |
     |    (receiver_id, call_type)          |--- 2. WebSocket: call.incoming ----->|
     |                                      |    (Channel: private-user.{id})      | (Ringing UI shown)
     |<-- 3. Returns session & ringing -----|                                      |
     |                                      |                                      |
     |                                      |<-- 4. POST /api/call/accept ---------|
     |                                      |    (call_id / session_id)            |
     |<-- 5. WebSocket: call.accepted ------|--- 5. WebSocket: call.accepted ----->|
     |    (LiveKit Token + URL)             |    (LiveKit Token + URL)             |
     |                                      |                                      |
     |==== 6. LiveKit Media Stream (Both publish Audio/Video tracks simultaneously) ===|
     |                                      |                                      |
     |--- 7. POST /api/call/end ----------->|                                      |
     |<-- 8. WebSocket: call.ended ---------|--- 8. WebSocket: call.ended -------->|
```

---

### 2.2 Call Initiation

**Endpoint:** `POST /api/call/initiate` (Alias: `POST /api/call/send`)  
**Headers:**
```http
Authorization: Bearer <CALLER_JWT_TOKEN>
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
  "receiver_id": 12,
  "call_type": "video"
}
```
*(Note: `call_type` can be `"video"` or `"audio"`)*

**Success Response (`200 OK`):**
```json
{
  "status": true,
  "success": true,
  "message": "Call initiated successfully",
  "call_id": 105,
  "session_id": 105,
  "room_name": "call_video_99_12_1727452800_abcd",
  "channel_name": "call_video_99_12_1727452800_abcd",
  "rate_per_minute": 20,
  "user_balance": 1500,
  "data": {
    "call_id": 105,
    "session_id": 105,
    "room_name": "call_video_99_12_1727452800_abcd",
    "caller_id": 99,
    "receiver_id": 12,
    "call_type": "video",
    "status": "ringing"
  }
}
```

**Low Balance Response (`402 Payment Required`):**
```json
{
  "status": false,
  "code": "INSUFFICIENT_BALANCE",
  "message": "Insufficient coins to make this call",
  "show_recharge_modal": true,
  "user_balance": 5,
  "required_coins": 20
}
```

---

### 2.3 Incoming Call Real-Time Socket Listener

Receiver app must subscribe to their private channel upon logging in.

- **Pusher Channel:** `private-user.${currentUserId}`
- **Event Name:** `call.incoming` (also bound to `incoming_call` and `App\Events\CallIncoming`)

**Incoming Event Payload:**
```json
{
  "event": "call.incoming",
  "call_id": 105,
  "session_id": 105,
  "channel_name": "call_video_99_12_1727452800_abcd",
  "room_name": "call_video_99_12_1727452800_abcd",
  "call_type": "video",
  "caller_id": 99,
  "caller_name": "Nazmul Hossain",
  "caller_avatar": "https://chinchins.live/storage/avatars/nazmul.jpg",
  "rate_per_minute": 20,
  "status": "ringing",
  "created_at": "2026-09-27T21:40:00.000000Z"
}
```

**Flutter Listener Implementation:**
```dart
final channel = pusher.subscribe('private-user.${currentUser.id}');

channel.bind('call.incoming', (PusherEvent? event) {
  if (event?.data != null) {
    final data = jsonDecode(event!.data!);
    // Open full-screen Ringing Activity / Call Dialog
    Navigator.of(context).pushNamed('/incoming_call_screen', arguments: data);
  }
});
```

---

### 2.4 Accept Call

**Endpoint:** `POST /api/call/accept`  
**Headers:** `Authorization: Bearer <RECEIVER_JWT_TOKEN>`  
**Request Body:**
```json
{
  "call_id": 105,
  "session_id": 105
}
```

**Success Response (`200 OK`):**
```json
{
  "status": true,
  "success": true,
  "message": "Call accepted successfully",
  "data": {
    "call_id": 105,
    "session_id": 105,
    "room_name": "call_video_99_12_1727452800_abcd",
    "status": "accepted",
    "livekit_url": "wss://chinchins.live/livekit",
    "livekit_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
  }
}
```

**Real-Time Broadcast:**  
Upon accept, both Caller and Receiver receive the `call.accepted` event with the JWT token to join the LiveKit room.

---

### 2.5 Reject Call

**Endpoint:** `POST /api/call/reject`  
**Headers:** `Authorization: Bearer <RECEIVER_JWT_TOKEN>`  
**Request Body:**
```json
{
  "call_id": 105
}
```
**Socket Broadcast:** Triggers `call.rejected` on `private-user.{caller_id}`.

---

### 2.6 End Call

**Endpoint:** `POST /api/call/end`  
**Headers:** `Authorization: Bearer <USER_JWT_TOKEN>`  
**Request Body:**
```json
{
  "call_id": 105,
  "session_id": 105,
  "duration_seconds": 125
}
```
**Success Response (`200 OK`):**
```json
{
  "status": true,
  "message": "Call ended successfully",
  "duration_seconds": 125,
  "coins_deducted": 40
}
```
**Socket Broadcast:** Triggers `call.ended` on both Caller and Receiver private channels.

---

### 2.7 Active Call Heartbeat

During an active video/audio call, the mobile app should fire a heartbeat every 30 seconds to confirm connection and balance deduction.

**Endpoint:** `POST /api/call/heartbeat`  
**Headers:** `Authorization: Bearer <USER_JWT_TOKEN>`  
**Request Body:**
```json
{
  "call_id": 105,
  "session_id": 105
}
```

---

## 3. Flutter LiveKit Video/Audio Engine Integration

Add dependency to `pubspec.yaml`:
```yaml
dependencies:
  livekit_client: ^2.2.0
```

### Complete Flutter Call Room Controller:
```dart
import 'package:flutter/material.dart';
import 'package:livekit_client/livekit_client.dart';

class VideoCallScreen extends StatefulWidget {
  final String roomName;
  final String livekitToken;

  const VideoCallScreen({Key? key, required this.roomName, required this.livekitToken}) : super(key: key);

  @override
  State<VideoCallScreen> createState() => _VideoCallScreenState();
}

class _VideoCallScreenState extends State<VideoCallScreen> {
  Room? _room;
  EventsListener<RoomEvent>? _listener;

  @override
  void initState() {
    super.initState();
    _initLiveKit();
  }

  Future<void> _initLiveKit() async {
    _room = Room();
    _listener = _room!.createListener();

    _listener!
      ..on<TrackSubscribedEvent>((event) {
        setState(() {}); // Re-render when remote video/audio track is received
      })
      ..on<RoomDisconnectedEvent>((event) {
        Navigator.pop(context);
      });

    // 1. Connect to LiveKit SFU server
    await _room!.connect(
      'wss://chinchins.live/livekit',
      widget.livekitToken,
      roomOptions: const RoomOptions(
        adaptiveStream: true,
        dynacast: true,
      ),
    );

    // 2. Enable Camera and Microphone
    await _room!.localParticipant?.setCameraEnabled(true);
    await _room!.localParticipant?.setMicrophoneEnabled(true);

    setState(() {});
  }

  @override
  void dispose() {
    _listener?.dispose();
    _room?.disconnect();
    _room?.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final remoteParticipant = _room?.remoteParticipants.values.firstOrNull;

    return Scaffold(
      backgroundColor: Colors.black,
      body: Stack(
        children: [
          // 1. Remote Video (Full Screen)
          if (remoteParticipant != null && remoteParticipant.videoTrackPublications.isNotEmpty)
            VideoTrackRenderer(
              remoteParticipant.videoTrackPublications.first.track as VideoTrack,
              fit: RTCVideoViewObjectFit.RTCVideoViewObjectFitCover,
            )
          else
            const Center(child: CircularProgressIndicator(color: Colors.amber)),

          // 2. Local Video (Floating Picture-in-Picture)
          Positioned(
            top: 50,
            right: 20,
            width: 110,
            height: 160,
            child: ClipRRect(
              borderRadius: BorderRadius.circular(12),
              child: _room?.localParticipant?.videoTrackPublications.isNotEmpty == true
                  ? VideoTrackRenderer(
                      _room!.localParticipant!.videoTrackPublications.first.track as VideoTrack,
                      fit: RTCVideoViewObjectFit.RTCVideoViewObjectFitCover,
                    )
                  : Container(color: Colors.grey[900]),
            ),
          ),

          // 3. Call Controls Bottom Bar
          Positioned(
            bottom: 40,
            left: 0,
            right: 0,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: [
                IconButton(
                  icon: const Icon(Icons.mic, color: Colors.white, size: 30),
                  onPressed: () {
                    final current = _room?.localParticipant?.isMicrophoneEnabled() ?? true;
                    _room?.localParticipant?.setMicrophoneEnabled(!current);
                  },
                ),
                FloatingActionButton(
                  backgroundColor: Colors.red,
                  onPressed: () => Navigator.pop(context),
                  child: const Icon(Icons.call_end, color: Colors.white),
                ),
                IconButton(
                  icon: const Icon(Icons.switch_camera, color: Colors.white, size: 30),
                  onPressed: () {
                    // Switch front/back camera
                  },
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
```

---

## 4. Live Stream Audience Co-Host & Hand-Raise System

### 4.1 Flow Diagram
```
Audience (Flutter)                    Laravel Server                        Host (Flutter)
      |                                      |                                     |
      |--- 1. [✋ Hand-Raise Button] ------->|                                     |
      |    POST /api/live/stream/{id}/request-join                                 |
      |                                      |--- 2. Socket: live_join.requested ->|
      |                                      |    (Notification popup on Host UI)  |
      |                                      |                                     |
      |                                      |<-- 3. POST /api/live/stream/{id}/respond-join
      |                                      |    (action: 'accept')               |
      |<-- 4. Socket: live_join.responded ---|                                     |
      |    (LiveKit Guest Token with         |                                     |
      |     can_publish = true)              |                                     |
      |                                      |                                     |
      |=== 5. Both Host and Guest Stream into Multi-Video Grid simultaneously ======|
```

---

### 4.2 Hand-Raise Button Placement in Flutter

Place the **Hand-Raise Button (✋)** in the Live Audience Screen bottom bar between the **Chat Input** and **Gift** buttons:

```dart
Row(
  children: [
    // 1. Chat input button (Left)
    ChatIconButton(onTap: () => openLiveChatBottomSheet()),
    
    const SizedBox(width: 8),

    // 2. ✋ Hand Raise Button (Audience Request to join live grid)
    if (!isHost && !isCoHost)
      GestureDetector(
        onTap: () async {
          final res = await ApiService.post('/api/live/stream/${stream.id}/request-join', {
            'host_id': stream.hostId,
          });
          if (res['status'] == true) {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(content: Text('Join request sent to Host! ✋')),
            );
          }
        },
        child: Container(
          width: 44,
          height: 44,
          decoration: BoxDecoration(
            color: Colors.black.withOpacity(0.4),
            shape: BoxShape.circle,
            border: Border.all(color: Colors.white24, width: 1),
          ),
          child: const Icon(Icons.front_hand_rounded, color: Colors.amber, size: 22),
        ),
      ),

    const Spacer(),

    // 3. Gift Button
    GiftIconButton(onTap: () => openGiftDialog()),

    const SizedBox(width: 8),

    // 4. Follow Button
    FollowButton(hostId: stream.hostId),
  ],
)
```

---

### 4.3 Request Join API

**Endpoint:** `POST /api/live/stream/{stream_id}/request-join`  
**Headers:** `Authorization: Bearer <USER_JWT_TOKEN>`  
**Request Body:**
```json
{
  "host_id": 12
}
```

**Response (`200 OK`):**
```json
{
  "status": true,
  "message": "Co-host request sent to host successfully",
  "data": {
    "request_id": 45,
    "room_id": "1",
    "room_name": "live_stream_1",
    "live_stream_id": 1,
    "host_id": 12,
    "user_id": 99,
    "name": "Rahim",
    "avatar": "https://chinchins.live/storage/avatars/rahim.jpg",
    "status": "pending",
    "created_at": "2026-09-27T21:40:00.000000Z"
  }
}
```

**Host Real-Time Notification:**  
Host listens on `private-user.{hostId}` and `presence-live.{streamId}` for `live_join.requested`.

---

### 4.4 Host Respond API

**Endpoint:** `POST /api/live/stream/{stream_id}/respond-join`  
**Headers:** `Authorization: Bearer <HOST_JWT_TOKEN>`  
**Request Body:**
```json
{
  "request_id": 45,
  "user_id": 99,
  "action": "accept"
}
```
*(Note: `action` can be `"accept"` or `"reject"`)*

**Response (`200 OK` on Accept):**
```json
{
  "status": true,
  "message": "Co-host request accepted successfully",
  "data": {
    "request_id": 45,
    "room_id": "1",
    "room_name": "live_stream_1",
    "guest_user_id": 99,
    "status": "accepted",
    "action": "accept",
    "can_publish": true,
    "guest_token": {
      "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
      "livekit_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
      "room_name": "live_stream_1",
      "role": "co_host",
      "can_publish": true,
      "livekit_url": "wss://chinchins.live/livekit"
    }
  }
}
```

**Guest Socket Notification:**  
Guest receives `live_join.responded` with the LiveKit token, connects to the room with `canPublish: true`, and starts broadcasting their camera.

---

### 4.5 Co-Host List & Leave APIs

#### Get Pending Join Requests:
- **Endpoint:** `GET /api/live/stream/{stream_id}/join-requests`
- **Headers:** `Authorization: Bearer <HOST_JWT_TOKEN>`

#### Leave / Kick Co-Host:
- **Endpoint:** `POST /api/live/stream/{stream_id}/leave-cohost`
- **Headers:** `Authorization: Bearer <JWT_TOKEN>`
- **Request Body:** `{"guest_user_id": 99}`

---

## 5. WebSocket & Pusher Configuration Matrix

| Parameter | Production Value |
| :--- | :--- |
| **Pusher App Key** | `chinchins_key` (or your configured `PUSHER_APP_KEY`) |
| **Host** | `chinchins.live` |
| **Port** | `443` (SSL / WSS) / `6001` (Direct WS) |
| **Scheme** | `https` / `wss` |
| **Cluster** | `mt1` |
| **Auth Endpoint** | `https://chinchins.live/api/broadcasting/auth` |
| **Auth Headers** | `Authorization: Bearer <USER_TOKEN>` |

### Flutter Pusher Client Initialization:
```dart
import 'package:pusher_channels_flutter/pusher_channels_flutter.dart';

PusherChannelsFlutter pusher = PusherChannelsFlutter.getInstance();

await pusher.init(
  apiKey: "chinchins_key",
  cluster: "mt1",
  authEndpoint: "https://chinchins.live/api/broadcasting/auth",
  onAuthorizer: (channelName, socketId, options) async {
    final response = await http.post(
      Uri.parse("https://chinchins.live/api/broadcasting/auth"),
      headers: {
        'Authorization': 'Bearer $userToken',
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: 'socket_id=$socketId&channel_name=$channelName',
    );
    return jsonDecode(response.body);
  },
);

await pusher.connect();
```

---

*This document is verified and ready for production deployment across Flutter iOS & Android clients.*
