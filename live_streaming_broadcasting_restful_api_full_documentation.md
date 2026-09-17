# 🔴 Live Streaming, Multi-Host Broadcasting & Real-Time Direct Messaging Full Documentation
**System:** Chinchins Live Streaming, Multi-Host Video Engine & Direct Real-Time Chat  
**Backend:** Laravel 11.x RESTful Backend + Laravel Reverb WebSocket Server + Coturn STUN/TURN  
**Client:** Flutter (Android & iOS) with WebRTC (`flutter_webrtc`), Laravel Echo / Reverb & SVGA Animation Engine  
**Version:** 9.0.0 Production Edition  
**Document Name:** `live_streaming_broadcasting_restful_api_full_documentation.md`

---

## 📑 সূচিপত্র (Table of Contents)
1. [১. আর্কিটেকচার ও সার্ভার কনফিগারেশন (Architecture & Server Setup)](#১-আর্কিটেকচার-ও-সার্ভার-কনফিগারেশন)
2. [২. লারাভেল ব্যাকএন্ড এন্ডপয়েন্ট ও ইভেন্ট স্পেসিফিকেশন (Laravel Backend Spec)](#২-লারাভেল-ব্যাকএন্ড-এন্ডপয়েন্ট-ও-ইভেন্ট-স্পেসিফিকেশন)
   - [২.১ লাইভ মেসেজ ও গিফট ব্রডকাস্ট (Live Chat & Gift Broadcast)](#২১-লাইভ-মেসেজ-ও-গিফট-ব্রডকাস্ট)
   - [২.২ কো-হোস্ট ৪-৫ জন জয়েন সিস্টেম (Co-Host 4-5 Grid Management)](#২২-কো-হোস্ট-৪-৫-জন-জয়েন-সিস্টেম)
   - [২.৩ WebRTC সিগনালিং হ্যান্ডশেক (Multi-Host Mesh WebRTC)](#২৩-webrtc-সিগনালিং-হ্যান্ডশেক)
   - [২.৪ অডিও মিউট/আনমিউট কন্ট্রোল (Audio Mute Toggle)](#২৪-অডিও-মিউটআনমিউট-কন্ট্রোল)
   - [২.৫ লাইভ স্ট্রিম লাইফসাইকেল (Start, Join, Leave, End, Active List)](#২৫-লাইভ-স্ট্রিম-লাইফসাইকেল)
3. [৩. ফ্লাটার ডেভেলপার ইমপ্লিমেন্টেশন ও ইন্টিগ্রেশন গাইড (Flutter Implementation Guide)](#৩-ফ্লাটার-ডেভেলপার-ইমপ্লিমেন্টেশন-ও-ইন্টিগ্রেশন-গাইড)
   - [৩.১ STUN/TURN সার্ভার কনফিগারেশন](#৩১-stunturn-সার্ভার-কনফিগারেশন)
   - [৩.২ Laravel Reverb / Echo চ্যানেল লিসেনিং](#৩২-laravel-reverb--echo-চ্যানেল-লিসেনিং)
   - [৩.৩ ফুল-স্ক্রিন গিফট অ্যানিমেশন প্লেয়ার (SVGA/Lottie)](#৩৩-ফুল-স্ক্রিন-গিফট-অ্যানিমেশন-প্লেয়ার)
   - [৩.৪ ৪-৫ জন মাল্টি-হোস্ট স্প্লিট স্ক্রিন গ্রিড ও WebRTC হ্যান্ডলিং](#৩৪-৪-৫-জন-মাল্টি-হোস্ট-স্প্লিট-স্ক্রিন-গ্রিড-ও-webrtc-হ্যান্ডলিং)
4. [৪. রিয়েল-টাইম ১-অন-১ ডিরেক্ট চ্যাট ও ইমেজ আপলোড (Real-Time 1-on-1 Direct Chat & Media)](#৪-রিয়েল-টাইম-১-অন-১-ডিরেক্ট-চ্যাট-ও-ইমেজ-আপলোড)
   - [৪.১ ডিরেক্ট চ্যাট আর্কিটেকচার ও কোনো হার্ডকোড ছাড়া রিয়েল চ্যাট](#৪১-ডিরেক্ট-চ্যাট-আর্কিটেকচার-ও-কোনো-হার্ডকোড-ছাড়া-রিয়েল-চ্যাট)
   - [৪.২ ইনবক্স লিস্ট, মেসেজ হিস্ট্রি ও ইমেজ আপলোড APIs](#৪২-ইনবক্স-লিস্ট-মেসেজ-হিস্ট্রি-ও-ইমেজ-আপলোড-apis)
   - [৪.৩ ফ্লাটারে রিয়েল-টাইম ডিরেক্ট চ্যাট লিসেনিং](#৪৩-ফ্লাটারে-রিয়েল-টাইম-ডিরেক্ট-চ্যাট-লিসেনিং)
5. [৫. সম্পূর্ণ REST API রেফারেন্স তালিকা (RESTful API Endpoint Summary)](#৫-সম্পূর্ণ-rest-api-রেফারেন্স-তালিকা)

---

## ১. আর্কিটেকচার ও সার্ভার কনফিগারেশন

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                                 FLUTTER MOBILE CLIENT                                  │
│                                                                                        │
│  ┌───────────────────────────┐  ┌───────────────────────────┐  ┌────────────────────┐  │
│  │ 1-on-1 Direct Chat & Img  │  │ Live Room (Viewer Mode)   │  │ 4-5 Multi-Host Grid│  │
│  └─────────────┬─────────────┘  └─────────────┬─────────────┘  └──────────┬─────────┘  │
└────────────────┼──────────────────────────────┼───────────────────────────┼────────────┘
                 │                              │                           │
      HTTP / REST (Bearer Token)                │                WebSocket (Reverb Client)
                 │                              │                           │
                 ▼                              ▼                           ▼
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                         LARAVEL 11 & REVERB WEBSOCKET SERVER                           │
│                                                                                        │
│  - WebSocket Port: 8080 (WS) / 443 (WSS)                                               │
│  - Live Broadcast Channel: `live-room.{roomId}` & `live-stream.{streamId}`             │
│  - Direct 1-on-1 User Channel: `user-chat.{receiver_id}`                               │
│  - Instant Execution: ShouldBroadcastNow (No Queue Lag)                                │
│  - Image Upload Directory: public/uploads/live_chat/ (Full Public URL)                 │
│  - 50/50 Gift Coins Revenue Split -> Diamonds System                                  │
└────────────────────────────────────────────────────────────────────────────────────────┘
                                 │
                                 ▼
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                        COTURN STUN / TURN SIGNALING SERVER                             │
│  - IP: 2.25.131.55                                                                     │
│  - Port: 3478                                                                          │
│  - WebRTC Peer-to-Peer Mesh Connection for 4-5 Video Streams                          │
└────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## ২. লারাভেল ব্যাকএন্ড এন্ডপয়েন্ট ও ইভেন্ট স্পেসিফিকেশন

### ২.১ লাইভ মেসেজ ও গিফট ব্রডকাস্ট

সব ইভেন্টে `implements ShouldBroadcastNow` যুক্ত করা হয়েছে যাতে মেসেজ কিউতে আটকা না পড়ে তাৎক্ষণিক যায়।

#### Event Class: `app/Events/LiveChatMessageEvent.php`
- **Channel**: `live-room.{roomId}`, `live-stream.{roomId}`
- **Broadcast As**: `message.sent`
- **Payload**:
```json
{
  "id": 105,
  "room_id": "12",
  "stream_id": "12",
  "user_id": 4,
  "user_name": "Rahim Khan",
  "user_avatar": "https://domain.com/uploads/avatars/4.jpg",
  "user": {
    "id": 4,
    "display_name": "Rahim Khan",
    "avatar_url": "https://domain.com/uploads/avatars/4.jpg",
    "level": "Lv5"
  },
  "message": "Hello everyone!",
  "type": "text",
  "gift_id": null,
  "gift_data": null,
  "level": "Lv5",
  "timestamp": "2026-09-15T22:00:00.000000Z"
}
```

#### API Endpoint: `POST /api/live/send-message` (Aliases: `/api/live/message`, `/api/live/comment`)
- **Headers**: `Authorization: Bearer {token}`, `Accept: application/json`
- **Request Body**:
```json
{
  "room_id": "12",
  "message": "Hello everyone!",
  "type": "text",
  "gift_id": null
}
```
- **Response (200 OK)**:
```json
{
  "status": "success",
  "success": true,
  "message": "Live message sent successfully.",
  "data": { ... }
}
```

---

### ২.২ কো-হোস্ট ৪-৫ জন জয়েন সিস্টেম

হোস্ট যখন কাউকে জয়েন করার জন্য ইনভাইট পাঠাবে, জয়েন একসেপ্ট করবে, বা রিমুভ করবে তখন তাৎক্ষণিক ইভেন্ট ফায়ার হবে।

#### Event Class: `app/Events/CoHostStatusEvent.php`
- **Channel**: `live-room.{roomId}`, `live-stream.{roomId}`
- **Broadcast As**: `cohost.status.changed`
- **Payload**:
```json
{
  "room_id": "12",
  "stream_id": "12",
  "action": "invited", // 'invited', 'accepted', 'rejected', 'removed'
  "target_user_id": 18,
  "target_user": {
    "id": 18,
    "user_id": 18,
    "name": "Karim Hossain",
    "display_name": "Karim Hossain",
    "avatar_url": "https://domain.com/uploads/avatars/18.jpg"
  },
  "timestamp": "2026-09-15T22:00:00.000000Z"
}
```

#### API Endpoint: `POST /api/live/cohost-action` (Aliases: `/api/live/handle-cohost`)
- **Headers**: `Authorization: Bearer {token}`, `Accept: application/json`
- **Request Body**:
```json
{
  "room_id": "12",
  "target_user_id": 18,
  "action": "invite" // 'invite', 'accept', 'reject', 'remove'
}
```
- **Response (200 OK)**:
```json
{
  "status": "success",
  "message": "Co-host invite successful",
  "data": {
    "room_id": "12",
    "action": "invite",
    "target_user": {
      "id": 18,
      "display_name": "Karim Hossain",
      "avatar_url": "https://domain.com/uploads/avatars/18.jpg"
    }
  }
}
```

---

### ২.৩ WebRTC সিগনালিং হ্যান্ডশেক

মাল্টি-হোস্ট মেশ নেটওয়ার্কে ৪-৫ জনের অডিও-ভিডিও স্ট্রিম শেয়ার করার জন্য WebRTC Offer, Answer ও ICE Candidate আদান-প্রদান এন্ডপয়েন্ট।

#### Event Class: `app/Events/WebRTCSignalEvent.php`
- **Channel**: `live-room.{roomId}`, `live-stream.{roomId}`
- **Broadcast As**: `webrtc.signal`
- **Payload**:
```json
{
  "room_id": "12",
  "stream_id": "12",
  "from_user_id": 4,
  "to_user_id": 18,
  "type": "offer", // 'offer', 'answer', 'candidate'
  "data": { "sdp": "..." },
  "timestamp": "2026-09-15T22:00:00.000000Z"
}
```

#### API Endpoint: `POST /api/live/signal` (Aliases: `/api/v1/stream/signal`, `/api/live/send-signal`)
- **Headers**: `Authorization: Bearer {token}`, `Accept: application/json`
- **Request Body**:
```json
{
  "room_id": "12",
  "to_user_id": 18,
  "type": "offer",
  "data": {
    "type": "offer",
    "sdp": "v=0\r\no=- 461173... IN IP4 127.0.0.1..."
  }
}
```
- **Response (200 OK)**:
```json
{
  "status": "sent",
  "message": "WebRTC signal 'offer' broadcast successfully.",
  "data": { ... }
}
```

---

### ২.৪ অডিও মিউট/আনমিউট কন্ট্রোল

হোস্ট বা কো-হোস্ট তাদের নিজস্ব মাইক্রোফোন মিউট/আনমিউট করতে পারবে এবং হোস্ট চাইলে যেকোনো কো-হোস্টকে মিউট করতে পারবে।

#### Event Class: `app/Events/AudioMuteEvent.php`
- **Channel**: `live-room.{roomId}`, `live-stream.{roomId}`
- **Broadcast As**: `audio.mute.toggled`
- **Payload**:
```json
{
  "room_id": "12",
  "target_user_id": 18,
  "is_muted": true,
  "muted_by_host": false,
  "timestamp": "2026-09-15T22:00:00.000000Z"
}
```

#### API Endpoint: `POST /api/live/mute-toggle` (Aliases: `/api/live/toggle-mute`)
- **Headers**: `Authorization: Bearer {token}`, `Accept: application/json`
- **Request Body**:
```json
{
  "room_id": "12",
  "target_user_id": 18,
  "is_muted": true,
  "muted_by_host": false
}
```
- **Response (200 OK)**:
```json
{
  "status": "success",
  "message": "Audio mute state updated successfully.",
  "data": {
    "room_id": "12",
    "target_user_id": 18,
    "is_muted": true,
    "muted_by_host": false,
    "timestamp": "2026-09-15T22:00:00.000000Z"
  }
}
```

---

### ২.৫ লাইভ গিফট সেন্ডিং ও ৫০/৫০ রেভিনিউ স্প্লিট

#### API Endpoint: `POST /api/live/send-gift` (Aliases: `/api/live/gift`)
- **Headers**: `Authorization: Bearer {token}`, `Accept: application/json`
- **Request Body**:
```json
{
  "room_id": "12",
  "gift_id": 5,
  "quantity": 1
}
```
- **Response (200 OK)**:
```json
{
  "status": true,
  "success": true,
  "message": "Gift sent successfully!",
  "user_coins": 1250,
  "data": {
    "transaction_id": 892,
    "room_id": "12",
    "sender": {
      "id": 4,
      "name": "Rahim Khan",
      "avatar": "https://domain.com/uploads/avatars/4.jpg"
    },
    "gift": {
      "id": 5,
      "name": "Super Rocket",
      "coin_price": 500,
      "icon_url": "https://domain.com/uploads/gifts/rocket.png",
      "animation_asset_url": "https://domain.com/uploads/gifts/rocket.svga",
      "animation_type": "svga"
    },
    "quantity": 1,
    "total_coins": 500
  }
}
```
> **নোট**: গিফট সেন্ড করার সাথে সাথে ব্যাকএন্ড স্বয়ংক্রিয়ভাবে `live-room.{roomId}` চ্যানেলে `message.sent` ইভেন্ট ব্রডকাস্ট করে যাতে দর্শক ও হোস্ট সবার স্ক্রিনে ফুল-স্ক্রিন SVGA অ্যানিমেশন চালু হয়ে যায়।

---

### ২.৬ লাইভ স্ট্রিম লাইফসাইকেল API সমূহ

| Endpoint | Method | বর্ণনা |
| :--- | :--- | :--- |
| `/api/live/start` | `POST` | হোস্ট নতুন লাইভ স্ট্রিমিং শুরু করবে |
| `/api/live/end` | `POST` | হোস্ট লাইভ ব্রডকাস্ট সমাপ্ত করবে |
| `/api/live/join` | `POST` | দর্শক/ভিউয়ার লাইভ রুমে প্রবেশ করবে |
| `/api/live/leave` | `POST` | দর্শক লাইভ রুম ত্যাগ করবে |
| `/api/live/active` | `GET` | বর্তমান রানিং লাইভ ব্রডকাস্টের তালিকা |

---

## ৩. ফ্লাটার ডেভেলপার ইমপ্লিমেন্টেশন ও ইন্টিগ্রেশন গাইড

### ৩.১ STUN/TURN সার্ভার কনফিগারেশন

ভিডিও কল ও মাল্টি-হোস্ট লাইভে যাতে NAT/Firewall বা মোবাইল নেটওয়ার্কে ভিডিও ড্রপ না করে, সেজন্য `RTCPeerConnection` তৈরির সময় নিচের কনফিগারেশন সেট করতে হবে:

```dart
final Map<String, dynamic> iceServers = {
  'iceServers': [
    {'urls': 'stun:2.25.131.55:3478'},
    {
      'urls': 'turn:2.25.131.55:3478',
      'username': 'chinchins',
      'credential': 'chinchins',
    },
  ],
  'sdpSemantics': 'unified-plan'
};
```

---

### ৩.২ Laravel Reverb / Echo চ্যানেল লিসেনিং

লারাভেল ইকো ক্লায়েন্টে `live-room.${roomId}` চ্যানেলে সাবস্ক্রাইব করে নিচের ৪টি ইভেন্ট লিসেন করুন।  
*(মনে রাখবেন: Laravel Reverb এ কাস্টম `broadcastAs()` ইভেন্ট লিসেন করার জন্য ইভেন্টের নামের শুরুতে অবশ্যই ডট `.` দিতে হয়)*:

```dart
import 'package:laravel_echo/laravel_echo.dart';
import 'package:pusher_client/pusher_client.dart';

void subscribeToLiveRoom(String roomId) {
  // 1. Subscribe to Live Room Channel
  Echo echo = EchoService.instance.echo;
  
  echo.channel('live-room.$roomId')
    // A. লাইভ চ্যাট মেসেজ ও ফুল-স্ক্রিন গিফট
    .listen('.message.sent', (data) {
      if (data['type'] == 'gift') {
        // ফুল-স্ক্রিন SVGA গিফট অ্যানিমেশন প্লে করুন
        final giftData = data['gift_data'] ?? data['gift'];
        final svgaUrl = giftData['animation_asset_url'] ?? giftData['animation_url'];
        final senderName = data['user_name'] ?? data['sender_name'];
        final giftName = giftData['name'];
        
        GiftOverlayManager.showFullScreenGift(
          svgaUrl: svgaUrl,
          senderName: senderName,
          giftName: giftName,
        );
      } else {
        // রেগুলার চ্যাট মেসেজ লিস্টে অ্যাড করুন
        LiveChatController.to.addMessage(data);
      }
    })
    
    // B. কো-হোস্ট স্টেটাস পরিবর্তন (ইনভাইট/একসেপ্ট/রিমুভ)
    .listen('.cohost.status.changed', (data) {
      String action = data['action'];
      int targetUserId = data['target_user_id'] ?? data['user_id'];
      
      if (action == 'invited' && currentUserId == targetUserId) {
        // কো-হোস্ট ইনভাইটেশন ডায়ালগ পপআপ দেখান
        showCoHostInviteDialog(data);
      } else if (action == 'accepted') {
        // নতুন কো-হোস্ট গ্রিডে যুক্ত হয়েছে, WebRTC কানেকশন ইনিশিয়ালাইজ করুন
        MultiHostWebRTCManager.to.handleNewCoHostJoined(targetUserId);
      } else if (action == 'removed' || action == 'rejected') {
        // গ্রিড থেকে রিমুভ করুন
        MultiHostWebRTCManager.to.removeCoHostFromGrid(targetUserId);
      }
    })
    
    // C. WebRTC সিগনালিং হ্যান্ডশেক (Offer / Answer / Candidate)
    .listen('.webrtc.signal', (data) {
      int toUserId = data['to_user_id'] ?? data['target_user_id'];
      int fromUserId = data['from_user_id'] ?? data['sender_id'];
      String type = data['type'];
      var signalPayload = data['data'] ?? data['payload'];

      // শুধুমাত্র যার উদ্দেশ্যে সিগনাল পাঠানো হয়েছে সে হ্যান্ডেল করবে
      if (toUserId == currentUserId) {
        MultiHostWebRTCManager.to.handleIncomingSignal(
          fromUserId: fromUserId,
          type: type,
          payload: signalPayload,
        );
      }
    })
    
    // D. অডিও মিউট/আনমিউট স্টেটাস
    .listen('.audio.mute.toggled', (data) {
      int targetUserId = data['target_user_id'] ?? data['user_id'];
      bool isMuted = data['is_muted'] ?? false;
      
      MultiHostWebRTCManager.to.updateUserMuteState(targetUserId, isMuted);
    });
}
```

---

### ৩.৩ ফুল-স্ক্রিন গিফট অ্যানিমেশন প্লেয়ার

Flutter এ SVGA গিফট অ্যানিমেশন রেন্ডার করার জন্য `svgaplayer_flutter` প্যাকেজ ব্যবহার করুন:

```dart
import 'package:flutter/material.dart';
import 'package:svgaplayer_flutter/svgaplayer_flutter.dart';

class FullScreenGiftOverlay extends StatefulWidget {
  final String svgaUrl;
  final String senderName;
  final String giftName;
  final VoidCallback onComplete;

  const FullScreenGiftOverlay({
    Key? key,
    required this.svgaUrl,
    required this.senderName,
    required this.giftName,
    required this.onComplete,
  }) : super(key: key);

  @override
  State<FullScreenGiftOverlay> createState() => _FullScreenGiftOverlayState();
}

class _FullScreenGiftOverlayState extends State<FullScreenGiftOverlay> 
    with SingleTickerProviderStateMixin {
  late SVGAAnimationController _animationController;

  @override
  void initState() {
    super.initState();
    _animationController = SVGAAnimationController(vsync: this);
    _loadAndPlayAnimation();
  }

  Future<void> _loadAndPlayAnimation() async {
    final videoItem = await SVGAParser.shared.decodeFromURL(widget.svgaUrl);
    if (mounted) {
      _animationController.videoItem = videoItem;
      _animationController.reset();
      _animationController.forward().whenComplete(() {
        widget.onComplete();
      });
    }
  }

  @override
  void dispose() {
    _animationController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return IgnorePointer(
      child: Stack(
        children: [
          // SVGA Full-Screen Animation Canvas
          Center(
            child: SVGAImage(_animationController, fit: BoxFit.contain),
          ),
          // Sender & Gift Banner at Top
          Positioned(
            top: 100,
            left: 20,
            right: 20,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              decoration: BoxDecoration(
                color: Colors.black.withOpacity(0.6),
                borderRadius: BorderRadius.circular(25),
                border: Border.all(color: Colors.amber, width: 1.5),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(Icons.card_giftcard, color: Colors.amber),
                  const SizedBox(width: 8),
                  Text(
                    "${widget.senderName} sent ${widget.giftName}!",
                    style: const TextStyle(
                      color: Colors.white,
                      fontWeight: FontWeight.bold,
                      fontSize: 16,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
```

---

### ৩.৪ ৪-৫ জন মাল্টি-হোস্ট স্প্লিট স্ক্রিন গ্রিড

লাইভ রুমে সর্বোচ্চ ৫ জন পর্যন্ত এক সাথে ব্রডকাস্ট করতে পারবে। ভিউয়ারদের কাছে এবং কো-হোস্টদের কাছে ভিডিও লেআউট অটো-অ্যাডজাস্ট হবে:

```dart
Widget buildMultiHostGrid(List<CoHostVideoTrack> coHosts) {
  if (coHosts.length == 1) {
    // 1 Person: Single Full-Screen View
    return RTCVideoView(coHosts[0].renderer, objectFit: RTCVideoViewObjectFit.RTCVideoViewObjectFitCover);
  } else if (coHosts.length == 2) {
    // 2 Persons: Side-by-Side 1x2 or Top/Bottom 2x1 Split
    return Row(
      children: [
        Expanded(child: RTCVideoView(coHosts[0].renderer)),
        Expanded(child: RTCVideoView(coHosts[1].renderer)),
      ],
    );
  } else if (coHosts.length <= 4) {
    // 3-4 Persons: 2x2 Grid View
    return GridView.builder(
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        childAspectRatio: 0.8,
      ),
      itemCount: coHosts.length,
      itemBuilder: (context, index) {
        return Stack(
          children: [
            RTCVideoView(coHosts[index].renderer),
            if (coHosts[index].isMuted)
              const Positioned(
                bottom: 8,
                right: 8,
                child: Icon(Icons.mic_off, color: Colors.red, size: 20),
              ),
          ],
        );
      },
    );
  } else {
    // 5 Persons: 1 Main Host on Top + 4 Co-Hosts in 2x2 Bottom Grid
    return Column(
      children: [
        Expanded(flex: 3, child: RTCVideoView(coHosts[0].renderer)),
        Expanded(
          flex: 2,
          child: GridView.builder(
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 4,
              childAspectRatio: 0.9,
            ),
            itemCount: coHosts.length - 1,
            itemBuilder: (context, index) {
              return RTCVideoView(coHosts[index + 1].renderer);
            },
          ),
        ),
      ],
    );
  }
}
```

---

## ৪. রিয়েল-টাইম ১-অন-১ ডিরেক্ট চ্যাট ও ইমেজ আপলোড

### ৪.১ ডিরেক্ট চ্যাট আর্কিটেকচার ও কোনো হার্ডকোড ছাড়া রিয়েল চ্যাট
- **রিয়েল-টাইম আদান-প্রদান:** ছেলে-মেয়ে বা সব ইউজার একে অপরের সাথে লাইভ চ্যাট করতে পারবে। কোনো হার্ডকোডেড রিপ্লাই নেই।
- **ইন্সট্যান্ট ব্রডকাস্টিং:** `DirectMessageSent` ইভেন্ট `ShouldBroadcastNow` ইন্টারফেস ব্যবহার করে মেসেজ ও ফটো সাথে সাথে অন্য প্রান্তে পুশ করে।
- **ইমেজ পাথ:** ছবি `public/uploads/live_chat/` ফোল্ডারে সেভ হয় এবং রেসপন্সে ছবির ফুল পাবলিক URL রিটার্ন করে।

### ৪.২ ইনবক্স লিস্ট, মেসেজ হিস্ট্রি ও ইমেজ আপলোড APIs

#### ১. ইনবক্স তালিকা (Get Conversations / Inbox List):
- **Endpoint:** `GET /api/chat/conversations`
- **Headers:** `Authorization: Bearer {token}`, `Accept: application/json`
- **Response (200 OK):**
```json
{
  "status": "success",
  "data": [
    {
      "conversation_id": 1,
      "user": {
        "id": 15,
        "account_id": "84729104",
        "name": "Nusrat Jahan",
        "display_name": "Nusrat Jahan",
        "avatar": "https://domain.com/uploads/avatars/15.jpg",
        "avatar_url": "https://domain.com/uploads/avatars/15.jpg",
        "gender": "female",
        "level": "Lv3",
        "is_online": true
      },
      "last_message": "Hey, how are you?",
      "last_message_at": "2026-09-15T22:40:00.000000Z"
    }
  ]
}
```

#### ২. চ্যাট হিস্ট্রি (Get Messages History):
- **Endpoint:** `GET /api/chat/messages/{conversationId}`
- **Headers:** `Authorization: Bearer {token}`, `Accept: application/json`
- **Response (200 OK):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "conversation_id": 1,
      "sender_id": 4,
      "receiver_id": 15,
      "message": "Hello!",
      "attachment_path": null,
      "type": "text",
      "is_read": true,
      "sender": {
        "id": 4,
        "name": "Rahim Khan",
        "display_name": "Rahim Khan",
        "avatar_url": "https://domain.com/uploads/avatars/4.jpg"
      },
      "created_at": "2026-09-15T22:38:00.000000Z"
    },
    {
      "id": 2,
      "conversation_id": 1,
      "sender_id": 15,
      "receiver_id": 4,
      "message": "📷 Photo",
      "attachment_path": "https://domain.com/uploads/live_chat/1726435000_66e74b.jpg",
      "type": "image",
      "is_read": true,
      "sender": {
        "id": 15,
        "name": "Nusrat Jahan",
        "display_name": "Nusrat Jahan",
        "avatar_url": "https://domain.com/uploads/avatars/15.jpg"
      },
      "created_at": "2026-09-15T22:39:00.000000Z"
    }
  ]
}
```

#### ৩. মেসেজ পাঠানো ও ইমেজ আপলোড (Send Direct Message / Image):
- **Endpoint:** `POST /api/chat/send-message` (Aliases: `/api/chat/send`)
- **Headers:** `Authorization: Bearer {token}`, `Accept: application/json`, `Content-Type: multipart/form-data`
- **Form Data (multipart):**
  - `receiver_id`: `15` (integer, required)
  - `message`: `"Check this out!"` (string, optional if image sent)
  - `image`: `[File Attachment]` (file, mimes: jpeg, png, jpg, webp, max 10MB, optional)
- **Response (200 OK):**
```json
{
  "status": "success",
  "message": "Message sent successfully",
  "data": {
    "id": 3,
    "conversation_id": 1,
    "sender_id": 4,
    "receiver_id": 15,
    "message": "Check this out!",
    "attachment_path": "https://domain.com/uploads/live_chat/1726435100_66e74c.jpg",
    "type": "image",
    "is_read": false,
    "sender": {
      "id": 4,
      "name": "Rahim Khan",
      "display_name": "Rahim Khan",
      "avatar_url": "https://domain.com/uploads/avatars/4.jpg"
    },
    "created_at": "2026-09-15T22:40:00.000000Z"
  }
}
```

### ৪.৩ ফ্লাটারে রিয়েল-টাইম ডিরেক্ট চ্যাট লিসেনিং

```dart
// লগইন করা ইউজারের ID দিয়ে লিসেন করুন
Echo.instance
    .channel('user-chat.$currentUserId')
    .listen('.message.received', (data) {
        // নতুন মেসেজ সরাসরি চ্যাট স্ক্রিনের লিস্টে যোগ হবে
        var newMessage = data;
        
        setState(() {
            messagesList.add(newMessage);
        });
        
        // অটো-স্ক্রোল
        scrollController.animateTo(
            scrollController.position.maxScrollExtent,
            duration: const Duration(milliseconds: 300),
            curve: Curves.easeOut,
        );
    });
```

---

## ৫. সম্পূর্ণ REST API রেফারেন্স তালিকা

| HTTP Method | API Route | বিবরণ | প্যারামিটারস (Payload) |
| :--- | :--- | :--- | :--- |
| **GET** | `/api/chat/conversations` | ইউজারের ইনবক্স তালিকা ও সর্বশেষ মেসেজ | `None` (Header: Bearer Token) |
| **GET** | `/api/chat/messages/{id}` | নির্দিষ্ট কনভারসেশনের চ্যাট হিস্ট্রি | `conversation_id` |
| **POST** | `/api/chat/send-message` | ডিরেক্ট টেক্সট মেসেজ ও ইমেজ পাঠানো (ShouldBroadcastNow) | `receiver_id`, `message`, `image` (multipart) |
| **POST** | `/api/live/send-message` | লাইভ চ্যাট মেসেজ বা গিফট পাঠানো (ShouldBroadcastNow) | `room_id`, `message`, `type`, `gift_id` |
| **POST** | `/api/live/cohost-action` | কো-হোস্ট ইনভাইট, একসেপ্ট, রিজেক্ট বা রিমুভ | `room_id`, `target_user_id`, `action` (`invite`/`accept`/`reject`/`remove`) |
| **POST** | `/api/live/signal` | WebRTC সিগনালিং আদান-প্রদান (P2P Mesh) | `room_id`, `to_user_id`, `type` (`offer`/`answer`/`candidate`), `data` |
| **POST** | `/api/live/mute-toggle` | অডিও মিউট/আনমিউট স্টেটাস আপডেট | `room_id`, `target_user_id`, `is_muted` (bool), `muted_by_host` (bool) |
| **POST** | `/api/live/send-gift` | ৫০/৫০ রেভিনিউ স্প্লিটে কয়েন কেটে গিফট পাঠানো | `room_id`, `gift_id`, `quantity` |
| **POST** | `/api/live/start` | হোস্টের লাইভ ব্রডকাস্ট শুরু করা | `title`, `cover_image` |
| **POST** | `/api/live/end` | হোস্টের লাইভ সমাপ্ত করা | `room_id` |
| **POST** | `/api/live/join` | দর্শক রুমে প্রবেশ করা ও ভিউয়ার কাউন্ট বৃদ্ধি | `room_id` |
| **POST** | `/api/live/leave` | দর্শক রুম ত্যাগ করা ও কাউন্ট কমানো | `room_id` |
| **GET** | `/api/live/active` | রানিং সব লাইভ ব্রডকাস্টের তালিকা | `page`, `per_page` |

  

