# 🔴 Chinchins Live — Complete Technical API, WebSocket, LiveKit RTC & Flutter Documentation

> **Base URL:** `https://chinchins.live/api`  
> **WebSocket Engine:** Laravel Reverb (`wss://chinchins.live:443/app/chinchins_reverb_key`)  
> **RTC Calling & Live Streaming Engine:** LiveKit RTC Engine (`wss://chinchins.live/livekit`) & Agora Cloud RTC  
> **Mandatory Request Headers:**
> ```http
> Accept: application/json
> Content-Type: application/json
> Authorization: Bearer <SANCTUM_BEARER_TOKEN>
> ```
> *(Note: For background services or legacy mobile builds, `X-User-Id` header or `user_id` parameter in request body is supported).*

---

# 📑 পূর্ণাঙ্গ সূচিপত্র (Table of Contents)

1. [LiveKit & Reverb Technical Solution (Black Screen & Co-Host Fix)](#1-livekit--reverb-technical-solution-black-screen--co-host-fix)
2. [Flutter Developer Integration Guide (Tracks & Split-Screen UI)](#2-flutter-developer-integration-guide-tracks--split-screen-ui)
3. [Complete A-to-Z 67-Points Technical Architecture](#3-complete-a-to-z-67-points-technical-architecture)
4. [LiveKit & Reverb WebSocket Events Specification](#4-livekit--reverb-websocket-events-specification)
5. [Complete RESTful API Endpoints Catalog](#5-complete-restful-api-endpoints-catalog)

---

# 1. LiveKit & Reverb Technical Solution (Black Screen & Co-Host Fix)

### 📌 মূল সমস্যা ও সমাধান
গেস্ট রিকোয়েস্ট অ্যাকসেপ্ট করার পর স্ক্রিন ব্ল্যাক হয়ে থাকা এবং অডিও-ভিডিও কানেক্ট না হয়ে শুধু "Connecting" এসে আটকে থাকার কারণ:
1. **LiveKit Token Permission:** গেস্টের টোকেনে `canPublish: true` এবং `canPublishData: true` দেওয়া হয়েছে।
2. **Real-time Trigger:** হোস্ট রিকোয়েস্ট অ্যাকসেপ্ট করা মাত্রই `CoHostAcceptedEvent` ফায়ার হবে (`cohost.accepted`), যা পাওয়া মাত্রই গেস্টের অ্যাপ থেকে স্বয়ংক্রিয়ভাবে ক্যামেরা ও মাইক্রোফোন ট্র্যাক অন হবে (`setCameraEnabled(true)` এবং `setMicrophoneEnabled(true)`)।

---

### 🔹 1.1 Generate LiveKit Room Token API
* **Endpoint:** `POST /api/live/get-token`
* **Method:** `POST`
* **Headers:** `Authorization: Bearer <TOKEN>`
* **Request Body:**
  ```json
  {
    "room_name": "live_1_1726718400",
    "role": "co_host" // 'host', 'co_host', 'viewer'
  }
  ```
* **Response (200 OK):**
  ```json
  {
    "status": true,
    "message": "Token generated successfully",
    "data": {
      "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
      "room_name": "live_1_1726718400",
      "role": "co_host",
      "can_publish": true,
      "livekit_url": "wss://chinchins.live/livekit",
      "user": {
        "id": 8,
        "account_id": "87452190",
        "display_name": "Tanvir",
        "avatar_url": "https://chinchins.live/storage/avatars/8.jpg"
      }
    }
  }
  ```

---

### 🔹 1.2 Viewer Request to Join as Co-Host API
* **Endpoint:** `POST /api/live/request-join`
* **Request Body:**
  ```json
  {
    "room_id": "45"
  }
  ```
* **Response (200 OK):**
  ```json
  {
    "status": true,
    "message": "Co-host request sent to host successfully",
    "data": {
      "request_id": 102,
      "room_id": "45",
      "room_name": "live_1_1726718400",
      "status": "pending"
    }
  }
  ```

---

### 🔹 1.3 Host Accept or Reject Co-Host Request API
* **Endpoint:** `POST /api/live/respond-request`
* **Request Body:**
  ```json
  {
    "request_id": 102,
    "action": "accept" // "accept" or "reject"
  }
  ```
* **Response (200 OK):**
  ```json
  {
    "status": true,
    "message": "Co-host request accepted successfully",
    "data": {
      "request_id": 102,
      "room_id": "45",
      "room_name": "live_1_1726718400",
      "guest_user_id": 8,
      "status": "accepted",
      "action": "accept",
      "guest_token": {
        "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
        "room_name": "live_1_1726718400",
        "role": "co_host",
        "can_publish": true,
        "livekit_url": "wss://chinchins.live/livekit"
      }
    }
  }
  ```

---

### 🔹 1.4 Send Live Chat Message API
* **Endpoint:** `POST /api/live/send-message`
* **Request Body:**
  ```json
  {
    "room_id": "45",
    "message": "Hello everyone! Welcome to the live! ❤️"
  }
  ```
* **Response (200 OK):**
  ```json
  {
    "status": true,
    "message": "Live message sent successfully",
    "data": {
      "id": 1540,
      "room_id": "45",
      "user_id": 1,
      "user_name": "Nazmul",
      "message": "Hello everyone! Welcome to the live! ❤️",
      "created_at": "2026-09-19 12:45:00"
    }
  }
  ```

---

# 2. Flutter Developer Integration Guide (Tracks & Split-Screen UI)

### 📌 Flutter Requirement: Publishing Tracks & Split Screen UI

#### ১. গেস্ট অ্যাকসেপ্ট হওয়ার পর ট্র্যাক অন করা:
হোস্ট রিকোয়েস্ট অ্যাকসেপ্ট করার নোটিফিকেশন (`cohost.accepted`) পাওয়া মাত্রই গেস্টের ফোনে এই ফাংশনটি ট্রিগার করতে হবে:
```dart
// CoHostAcceptedEvent পাওয়ার পর গেস্টের ক্যামেরা ও মাইক্রোফোন পাবলিশ করা
await room.localParticipant?.setCameraEnabled(true);
await room.localParticipant?.setMicrophoneEnabled(true);
```
> **Important:** এটি রান না হলে হোস্টের স্ক্রিনে গেস্ট বক্স কালো হয়ে থাকবে।

---

#### ২. ইউজার ইন্টারফেস (৬০% ভিডিও গ্রিড, ৪০% লাইভ চ্যাট ও গিফট):
৪-৫ জন জয়েন করলে বা হোস্ট-গেস্ট মিলে ভিডিও দেখার সুবিধার্থে স্ক্রিনটিকে দুটি অংশে বিভক্ত করার ফ্লাটার কোড:

```dart
import 'package:flutter/material.dart';
import 'package:livekit_client/livekit_client.dart';

class LiveRoomScreen extends StatefulWidget {
  final Room room;
  const LiveRoomScreen({Key? key, required this.room}) : super(key: key);

  @override
  State<LiveRoomScreen> createState() => _LiveRoomScreenState();
}

class _LiveRoomScreenState extends State<LiveRoomScreen> {
  List<ChatMessage> chatMessages = [];
  TrackPublication<VideoTrack>? localVideoTrack;

  @override
  void initState() {
    super.initState();
    _setupListeners();
  }

  void _setupListeners() {
    // 1. Reverb WebSocket Listener for Co-Host Accept
    LaravelEcho.instance
        .channel('live-room.${widget.room.name}')
        .listen('.cohost.accepted', (data) async {
      if (data['guest_user_id'] == currentUserId) {
        // গেস্ট ক্যামেরা এবং মাইক লাইভকিটে পাবলিশ করবে
        await widget.room.localParticipant?.setCameraEnabled(true);
        await widget.room.localParticipant?.setMicrophoneEnabled(true);
        setState(() {});
      }
    });

    // 2. Reverb WebSocket Listener for Live Chat
    LaravelEcho.instance
        .channel('live-room.${widget.room.name}')
        .listen('.chat.message', (data) {
      setState(() {
        chatMessages.add(ChatMessage(
          sender: data['user_name'] ?? 'User',
          message: data['message'] ?? '',
        ));
      });
    });
  }

  @override
  Widget build(BuildContext context) {
    final remoteParticipants = widget.room.remoteParticipants.values.toList();
    final isCoHosting = remoteParticipants.isNotEmpty;

    return Scaffold(
      backgroundColor: Colors.black,
      body: SafeArea(
        child: Column(
          children: [
            // উপরের অংশ: মাল্টিপল ভিডিও গ্রিড (হোস্ট + কো-হোস্ট) - ৬০% স্ক্রিন
            Expanded(
              flex: 6,
              child: GridView.builder(
                padding: const EdgeInsets.all(4),
                gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                  crossAxisCount: isCoHosting ? 2 : 1, // ১ জন হলে ফুল, বেশি হলে ২ কলাম
                  crossAxisSpacing: 4,
                  mainAxisSpacing: 4,
                  childAspectRatio: 1.0,
                ),
                itemCount: 1 + remoteParticipants.length,
                itemBuilder: (context, index) {
                  if (index == 0) {
                    // লোকাল ক্যামেরা (হোস্ট বা নিজের ভিডিও)
                    final localTrack = widget.room.localParticipant?.videoTrackPublications.firstOrNull?.track;
                    return localTrack != null
                        ? VideoTrackRenderer(localTrack)
                        : Container(
                            color: Colors.grey[900],
                            child: const Center(child: Icon(Icons.person, color: Colors.white54, size: 40)),
                          );
                  }
                  // কো-হোস্ট বা গেস্টদের ক্যামেরা
                  final p = remoteParticipants[index - 1];
                  final track = p.videoTrackPublications.firstOrNull?.track as VideoTrack?;
                  return track != null
                      ? VideoTrackRenderer(track)
                      : Container(
                          color: Colors.grey[900],
                          child: const Center(
                            child: Text("Connecting...", style: TextStyle(color: Colors.white70)),
                          ),
                        );
                },
              ),
            ),

            // নিচের অংশ: লাইভ চ্যাট মেসেজ লিস্ট এবং ইনপুট - ৪০% স্ক্রিন
            Expanded(
              flex: 4,
              child: Container(
                decoration: const BoxDecoration(
                  color: Colors.black87,
                  borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
                ),
                child: Column(
                  children: [
                    // মেসেজ লিস্ট
                    Expanded(
                      child: ListView.builder(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                        itemCount: chatMessages.length,
                        itemBuilder: (context, index) => Padding(
                          padding: const EdgeInsets.symmetric(vertical: 2.0),
                          child: RichText(
                            text: TextSpan(
                              children: [
                                TextSpan(
                                  text: "${chatMessages[index].sender}: ",
                                  style: const TextStyle(color: Colors.amber, fontWeight: FontWeight.bold, fontSize: 13),
                                ),
                                TextSpan(
                                  text: chatMessages[index].message,
                                  style: const TextStyle(color: Colors.white, fontSize: 13),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),
                    ),

                    // মেসেজ ইনপুট বার
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 6),
                      color: Colors.grey[950],
                      child: Row(
                        children: [
                          Expanded(
                            child: TextField(
                              controller: _messageController,
                              style: const TextStyle(color: Colors.white),
                              decoration: const InputDecoration(
                                hintText: "Send a public comment...",
                                hintStyle: TextStyle(color: Colors.white38),
                                border: InputBorder.none,
                                isDense: true,
                              ),
                            ),
                          ),
                          IconButton(
                            icon: const Icon(Icons.send, color: Colors.amber),
                            onPressed: () => _sendMessage(),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
```

---

# 3. Complete A-to-Z 67-Points Technical Architecture

### 1. Existing Technology Stack
* Laravel 11, PHP 8.2+, MySQL 8.0, Redis, Laravel Reverb WebSocket, Sanctum Auth.
* Media Traffic: LiveKit RTC Server (`wss://chinchins.live/livekit`) & Agora Cloud RTC.
* App Events: Laravel Reverb (`wss://chinchins.live:443/app/chinchins_reverb_key`).

### 2. 1-to-1 Video Call System
* **Call Button:** Profile View ➔ Call Button ➔ `POST /api/call/initiate`.
* **Incoming Dialog:** Receiver gets full-screen incoming call UI with `Accept`, `Reject`, `Busy`, `Timeout`.
* **Two-Way RTC:** LiveKit / Agora 2-way video/audio with camera flip, mic mute, speaker toggle.

### 3. Video Call During Chat
* In-call real-time messaging via `POST /api/call/message/send`.
* Instant sync to both participants and permanently saved to `messages` and `conversations`.

### 4. Video Call Chat Database
* Table: `messages` (`sender_id`, `receiver_id`, `conversation_id`, `call_id`, `sent_during_call = true`).

### 5. Profile → Online Status
* `online`, `offline`, `busy`, `in_call`, `in_live`, `in_party`.
* Profile view displays real-time status. Call button active only if `online`.

### 6. Profile View → Call / Message
* Profile view does not auto-call. Action buttons: `Video Call`, `Message`, `Follow`, `Send Gift`.

### 7. Online Presence System
* `POST /api/user/heartbeat` every 20-30s.

### 8. Live Streaming System
* TikTok/BIGO style live broadcast. Host goes live via `POST /api/live/start` with cover photo and title.

### 9. Live Streaming List
* Dynamic database feed: `GET /api/lives/active?page=1&per_page=30`.

### 10. Join Live
* `POST /api/live/join`. Viewer joins room with subscriber token.

### 11. Live Chat
* `POST /api/live/send-message`. Real-time comment broadcast via `LiveChatMessageEvent` (`chat.message`).

### 12. Live Gift System
* `POST /api/live/send-gift` with coin cost and quantity.

### 13. Gift Animation
* Server dispatches `LiveGiftSentEvent` with SVGA / Lottie asset URLs for full-screen animations.

### 14. Host Earnings
* 50% revenue split credited to host diamond balance (`users.received_coins`, `wallets.earnings`).

### 15. Coin / Wallet System
* `GET /api/wallet/balance`. Atomic deductions inside `DB::transaction()`.

### 16. Live Host → Guest Request
* Host invites viewer via `POST /api/live/invite-cohost`.

### 17. Multi-Guest Live
* 1 Host + 4 Co-Hosts grid layout.

### 18. Guest Request System
* Viewer sends `POST /api/live/request-join`. Host accepts via `POST /api/live/respond-request`.

### 19. Host Controls
* Kick guest (`POST /api/live/kick-guest`), Mute mic (`POST /api/live/mute-toggle`), End live (`POST /api/live/end`).

### 20. Voice Party Room
* Voice party rooms (e.g. `Party Room #123`) created via `POST /api/party-rooms/create`.

### 21. Voice Room Seats
* 8-10 Seat layout grid.

### 22. Voice Room Request
* `POST /api/party-rooms/{id}/take-seat` to occupy speaker seats.

### 23. Voice Room Chat
* `POST /api/party-rooms/{id}/send-message`.

### 24. Voice Room Gifts
* `POST /api/party-rooms/{id}/send-gift`.

### 25. Follow System
* `POST /api/user/follow`, `POST /api/user/unfollow`, `GET /api/user/{id}/followers`.

### 26. Notifications
* 11 real-time notification types supported via Reverb and FCM.

### 27. Call Status Management
* Calling ➔ Ringing ➔ Accepted ➔ Connected ➔ Active ➔ Ended.

### 28. Network Handling
* Automatic reconnection on Wi-Fi / Mobile Data switches without freezing Flutter UI.

### 29. Background / Minimize Video Call
* Back button minimizes call into Picture-in-Picture floating mini-window (`POST /api/call/minimize`).

### 30. Call End
* `POST /api/call/end`. Channels closed and status reset to `online`.

### 31. Call History
* `GET /api/call/history`.

### 32. Live History
* Duration, peak viewers, and diamond earnings saved.

### 33. Viewer Count
* Real-time viewer count sync via `LiveViewerCountUpdated`.

### 34. Live Ranking / Popularity
* Dynamic sorting by current viewers, gifts, and likes.

### 35. Likes
* `POST /api/live/like` broadcasts floating hearts via `LiveLikeSent`.

### 36. Share Live
* Deep link sharing: `https://chinchins.live/live/{id}`.

### 37. Report / Block
* `POST /api/chat/block`, `POST /api/chat/report`.

### 38. Moderation
* Host and Admin moderation tools.

### 39. Security Requirements
* LiveKit and Agora tokens generated dynamically from backend with 24-hour TTL and exact role grants.

### 40. WebSocket Security
* All Reverb private channels authorized with Sanctum Bearer tokens.

### 41. Gift Security
* Server-side validation of balance, prices, and recipient IDs.

### 42. Database Transaction
* `DB::beginTransaction()` and `DB::commit()` for wallet operations.

### 43. Real-Time Event Architecture
* `CoHostAcceptedEvent`, `LiveChatMessageEvent`, `LiveGiftSentEvent`, `LiveViewerCountUpdated`, `CallIncoming`.

### 44. Agora / LiveKit Channel Architecture
* Unique channel naming: `live_{host_id}_{timestamp}`, `call_{caller}_{receiver}_{time}`.

### 45. RTC Roles
* Host/Co-Host = `canPublish: true`, Viewers = `canPublish: false`.

### 46. App Performance
* Memory leak prevention and disposal of RTC renderers and WebSocket listeners.

### 47. Flutter Lifecycle
* Foreground ➔ Background ➔ Foreground state handling.

### 48. Camera & Microphone Permission
* Runtime permissions handling with user-friendly dialogs.

### 49. Beauty / Camera Filters
* `GET /api/filters`, `GET /api/camera/filters`.

### 50. Message System
* Unified messaging supporting text, images, gifts, and system messages.

### 51. Message Persistence
* All messages permanently stored in database.

### 52. Call + Chat Synchronization
* In-call messages synced to main inbox conversation history.

### 53. Live Room Data
* `live_streams` table with title, cover, host, viewer count, likes count, and diamond earnings.

### 54. Party Room Data
* `party_rooms` and `party_room_seats` tables.

### 55. API Requirements
* RESTful JSON endpoints.

### 56. Redis / Queue
* Redis queue workers for notifications and heavy operations.

### 57. VPS Requirements
* Nginx reverse proxy, PHP-FPM, MySQL, Redis, and Supervisor daemon for Reverb (`php artisan reverb:start`).

### 58. SSL
* Secure `https://` and `wss://` on all endpoints.

### 59. Error Handling
* Standard error codes (`INSUFFICIENT_BALANCE`, `USER_BUSY`, `STREAM_ENDED`).

### 60. Reconnection
* Automatic retry with exponential back-off on connection drops.

### 61. Duplicate Prevention
* Debouncing and backend idempotency protection for gift sending and call actions.

### 62. Race Condition Protection
* Pessimistic database locking (`lockForUpdate()`).

### 63. Admin Configuration
* Admin panel controls for gift prices, revenue split, and package rates.

### 64. Analytics
* Post-live metrics summary.

### 65. Important User Flow
* Clear end-to-end flows for 1-to-1 Calls, Live Broadcasts, and Voice Party Rooms.

### 66. Architectural Preservation Guidelines
* Preserves all existing UI, design, database structure, and business logic.

### 67. Final Testing
* Verified on Android & iOS devices across Wi-Fi and 4G/5G mobile networks.

---

# 4. LiveKit & Reverb WebSocket Events Specification

| Event Class | Channel | Event Name | Description |
| :--- | :--- | :--- | :--- |
| `CoHostAcceptedEvent` | `live-room.{roomId}` | `cohost.accepted` | Fired when host accepts guest co-host request (`can_publish: true`) |
| `LiveChatMessageEvent` | `live-room.{roomId}` | `chat.message` / `message.sent` | Real-time live room public comment |
| `LiveGiftSentEvent` | `live-room.{roomId}` | `LiveGiftSentEvent` | Real-time SVGA gift animation |
| `LiveViewerCountUpdated` | `live-room.{roomId}` | `LiveViewerCountUpdated` | Viewer joined or left count update |
| `LiveJoinRequested` | `user.{hostId}` | `LiveJoinRequested` | Popup on host screen when viewer requests co-host |
| `CoHostStatusEvent` | `live-room.{roomId}` | `cohost.status` | Co-host invite/accept/reject/remove status |
| `CallIncoming` | `user.{receiverId}` | `CallIncoming` | 1-to-1 incoming call popup |
| `CallAccepted` | `call.{callId}` | `CallAccepted` | 1-to-1 call accepted signal |
| `CallEnded` | `call.{callId}` | `CallEnded` | Call termination |

---

# 5. Complete RESTful API Endpoints Catalog

| Module | Method | Endpoint | Description |
| :--- | :---: | :--- | :--- |
| **LiveKit Token** | `POST` | `/api/live/get-token` | Generate LiveKit room token with `canPublish` permissions |
| **Live Co-Host** | `POST` | `/api/live/request-join` | Viewer requests to join live stream as co-host |
| **Live Co-Host** | `POST` | `/api/live/respond-request` | Host accepts or rejects co-host request |
| **Live Chat** | `POST` | `/api/live/send-message` | Send real-time comment in live stream |
| **Live Stream** | `GET` | `/api/lives/active` | Get active live streamers feed |
| **Live Stream** | `POST` | `/api/live/start` | Host starts live broadcast |
| **Live Stream** | `POST` | `/api/live/join` | Viewer joins live stream |
| **Live Stream** | `POST` | `/api/live/leave` | Viewer leaves live stream |
| **Live Stream** | `POST` | `/api/live/end` | Host ends live stream |
| **Live Gift** | `POST` | `/api/live/send-gift` | Send virtual gift to host with animation |
| **Live Like** | `POST` | `/api/live/like` | Send real-time heart reactions |
| **1-to-1 Call** | `POST` | `/api/call/initiate` | Initiate 1-on-1 audio/video call |
| **1-to-1 Call** | `POST` | `/api/call/accept` | Accept incoming call |
| **1-to-1 Call** | `POST` | `/api/call/reject` | Reject incoming call |
| **1-to-1 Call** | `POST` | `/api/call/end` | Terminate active call |
| **In-Call Chat** | `POST` | `/api/call/message/send` | Send real-time text message during call |
| **In-Call Gift** | `POST` | `/api/call/gift/send` | Send virtual gift during call |
| **Party Rooms** | `POST` | `/api/party-rooms/create` | Create new voice party room |
| **Party Seats** | `POST` | `/api/party-rooms/{id}/take-seat` | Occupy or request speaker seat |
| **Wallet** | `GET` | `/api/wallet/balance` | Get user coins and earnings balance |
| **Profile** | `GET` | `/api/profile/{id}` | Get profile details and real-time online status |
| **Follow** | `POST` | `/api/user/follow` | Follow a user |
