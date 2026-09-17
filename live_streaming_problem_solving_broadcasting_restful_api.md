# 🔴 Live Streaming Problem Solving Broadcasting RESTful API & In-Call Real-Time Documentation
**System:** Chinchins Live Streaming, Multi-Host Video Engine, 1-on-1 WebRTC/Agora Video Calls & In-Call Real-Time Chat & Gifting  
**Backend:** Laravel 12.x RESTful Backend + Laravel Reverb WebSocket Server + Agora RTC / Coturn STUN/TURN  
**Client:** Flutter (Android & iOS) with `flutter_webrtc`, `agora_rtc_engine`, `laravel_echo` / `dart_pusher_channels` & High-Motion Full-Screen Luxury Gift Animation Engine  
**Version:** 11.0.0 Production Release  
**Document File:** `live_streaming_problem_solving_broadcasting_restful_api.md`  

---

## 📑 সূচিপত্র (Table of Contents)
1. [১. সাম্প্রতিক সমস্যা ও স্থায়ী সমাধান (Problem Solving & Key Fixes)](#১-সাম্প্রতিক-সমস্যা-ও-স্থায়ী-সমাধান)
   - [১.১ ১টি মেসেজ ৫ বার আসার সমস্যার সমাধান (Message Deduplication Algorithm)](#১১-১টি-মেসেজ-৫-বার-আসার-সমস্যার-সমাধান)
   - [১.২ গিফট দিলে উভয় স্ক্রিনেই ফুল-স্ক্রিন অ্যানিমেশন প্লে (Dual-Screen Luxury Gift Animation)](#১২-গিফট-দিলে-উভয়-স্ক্রিনেই-ফুল-স্ক্রিন-অ্যানিমেশন-প্লে)
   - [১.৩ "Me" প্রোফাইল স্ক্রিনে প্রাপ্ত উপহার ও মাই ব্যাগ ডিসপ্লে (Received Gifts & My Bag Showcase)](#১৩-me-প্রোফাইল-স্ক্রিনে-প্রাপ্ত-উপহার-ও-মাই-ব্যাগ-ডিসপ্লে)
   - [১.৪ গো-লাইভ (Go Live) সাউন্ড ও ভিডিও ফিক্স (Agora Audio & Video Initialization)](#১৪-গো-লাইভ-go-live-সাউন্ড-ও-ভিডিও-ফিক্স)
2. [২. সিস্টেম আর্কিটেকচার ও রিয়েল-টাইম ইঞ্জিন](#২-সিস্টেম-আর্কিটেকচার-ও-রিয়েল-টাইম-ইঞ্জিন)
3. [৩. ডেটাবেজ স্কিমা ও মাইগ্রেশনস (Database Schema & Migrations)](#৩-ডেটাবেজ-স্কিমা-ও-মাইগ্রেশনস)
4. [৪. Laravel Reverb ব্রডকাস্ট ইভেন্টস ও চ্যানেল অথেনটিকেশন](#৪-laravel-reverb-ব্রডকাস্ট-ইভেন্টস-ও-চ্যানেল-অথেনটিকেশন)
5. [৫. ইন-কল ও ১-অন-১ ভিডিও চ্যাট API এন্ডপয়েন্ট](#৫-ইন-কল-ও-১-অন-১-ভিডিও-চ্যাট-api-এন্ডপয়েন্ট)
6. [৬. লাইভ স্ট্রিমিং ও ব্রডকাস্টিং লাইফসাইকেল (Live Streaming Engine)](#৬-লাইভ-স্ট্রিমিং-ও-ব্রডকাস্টিং-লাইফসাইকেল)
7. [৭. সম্পূর্ণ REST API এন্ডপয়েন্ট রেফারেন্স](#৭-সম্পূর্ণ-rest-api-এন্ডপয়েন্ট-রেফারেন্স)

---

## ১. সাম্প্রতিক সমস্যা ও স্থায়ী সমাধান (Problem Solving & Key Fixes)

### ১.১ ১টি মেসেজ ৫ বার আসার সমস্যার সমাধান (Message Deduplication Algorithm)
* **মূল কারণ:** ক্লায়েন্ট যখন একই কলের জন্য একাধিক চ্যানেল ভ্যারিয়েশন (`call.{id}`, `presence-call.{id}`, `user-chat.{id}`) সাবস্ক্রাইব করে, তখন ব্যাকএন্ড ব্রডকাস্ট ইভেন্ট সবকটি চ্যানেলে আসার কারণে UI তে মেসেজ ৫ বার যুক্ত হচ্ছিল।
* **সমাধান:** 
  1. `SignalingService`-এ একটি ৩.৫ সেকেন্ড স্লাইডিং উইন্ডো সিগনেচার হ্যাশিং (`${eventName}_${msgId}_${senderId}_${msgText}`) যুক্ত করা হয়েছে যা ডুপ্লিকেট ইভেন্ট রিসিভ ব্লক করে।
  2. `InCallChatOverlayState.addIncomingMessage`-এ ইনকামিং ও লোকাল উভয় মেসেজের জন্য কঠোর আইডি ও কনটেন্ট ম্যাচিং ডিডুপ্লিকেটর চালু করা হয়েছে।

```dart
// SignalingService স্লাইডিং উইন্ডো ডিডুপ্লিকেটর:
bool _isDuplicateEvent(String eventName, Map<String, dynamic> data) {
  final msgId = data['id'] ?? data['message_id'] ?? data['message']?['id'];
  final msgText = data['message'] is String ? data['message'] : data['message']?['message'] ?? data['text'];
  final senderId = data['sender_id'] ?? data['user_id'] ?? data['message']?['sender_id'];
  final sig = '${eventName}_${msgId ?? ''}_${senderId ?? ''}_${msgText ?? ''}';

  final now = DateTime.now().millisecondsSinceEpoch;
  _recentEventSignatures.removeWhere((_, time) => now - time > 3500);

  if (_recentEventSignatures.containsKey(sig)) return true;
  _recentEventSignatures[sig] = now;
  return false;
}
```

---

### ১.২ গিফট দিলে উভয় স্ক্রিনেই ফুল-স্ক্রিন অ্যানিমেশন প্লে (Dual-Screen Luxury Gift Animation)
* **সমাধান:** 
  1. যিনি গিফট পাঠাচ্ছেন (Sender), তার স্ক্রিনে লোকাল `onGiftSent` কলব্যাকের মাধ্যমে সাথে সাথে ফুল-স্ক্রিন লাক্সারি অ্যানিমেশন প্লে হয়।
  2. রিসিভার (Receiver/Host) স্ক্রিনে WebSocket চ্যানেল থেকে `onLiveGift` ইভেন্ট আসার সাথে সাথে `_giftAnimKey.currentState?.playGiftAnimationDynamic(...)` ট্রিগার হয়।
  3. একই সাথে চ্যাট বক্সে স্পেশাল গিফট ব্যানার মেসেজ (`🎁 sent Fire Dragon (50,000 Coins)!`) রিয়েল-টাইমে যুক্ত হয়।

---

### ১.৩ "Me" প্রোফাইল স্ক্রিনে প্রাপ্ত উপহার ও মাই ব্যাগ ডিসপ্লে (Received Gifts & My Bag Showcase)
* **অবস্থান:** "Me" স্ক্রিনে **Create a party room** ব্যানারটির ঠিক নিচে **"Received Gifts / প্রাপ্ত উপহার"** সেকশন যুক্ত করা হয়েছে।
* **ফিচারস:**
  - মোট প্রাপ্ত গিফট সংখ্যা (`totalGiftsReceived`) এবং চার্ম পয়েন্ট (`charmPoints`) ডিসপ্লে।
  - প্রতিটি গিফটের ৩D আইকন/অ্যানিমেশন, নাম এবং কোয়ান্টিটি ব্যাজ (`x10`) সহ চমৎকার হরিজন্টাল ক্যারোসেল।
  - খালি থাকলে আকর্ষণীয় এম্পটি স্টেট কার্ড।
  - **View All** বাটনে ট্যাপ করলে ফুল ডিটেইলস `GiftsReceivedScreen`-এ নিয়ে যাবে।
  - **My Bag:** ব্যবহারকারী যে সব ফ্রেম/ব্যাজ আনলক করেছেন তা প্রোফাইলে ইকুইপ করে ব্যবহার করতে পারবেন।

---

### ১.৪ গো-লাইভ (Go Live) সাউন্ড ও ভিডিও ফিক্স (Agora Audio & Video Initialization)
* **সমাধান:**
  1. `LiveRoomScreen`-এ `enableAudio()`, `enableVideo()`, `setDefaultAudioRouteToSpeakerphone(true)`, এবং ব্রডকাস্টারদের জন্য `startPreview()` নিশ্চিত করা হয়েছে।
  2. `ChannelMediaOptions`-এ `enableAudioRecordingOrPlayout: true`, `publishCameraTrack: isBroadcaster`, `publishMicrophoneTrack: isBroadcaster` সক্রিয় করা হয়েছে।
  3. ফলে হোস্ট লাইভে যাওয়ার সাথে সাথেই তার স্ক্রিনে ক্যামেরা প্রিভিউ এবং লাউডস্পিকারে ক্লিয়ার অডিও সক্রিয় হয়ে যায়।

---

## ২. সিস্টেম আর্কিটেকচার ও রিয়েল-টাইম ইঞ্জিন

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                                 FLUTTER MOBILE CLIENT                                  │
│                                                                                        │
│  ┌───────────────────────────┐  ┌───────────────────────────┐  ┌────────────────────┐  │
│  │ 1-on-1 Video Call & Chat  │  │ Live Room (Viewer Mode)   │  │ 4-5 Multi-Host Grid│  │
│  └─────────────┬─────────────┘  └─────────────┬─────────────┘  └──────────┬─────────┘  │
└────────────────┼──────────────────────────────┼───────────────────────────┼────────────┘
                 │                              │                           │
      HTTP / REST (Bearer Token)                │                WebSocket (Reverb Client)
                 │                              │                           │
                 ▼                              ▼                           ▼
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                         LARAVEL 12 & REVERB WEBSOCKET SERVER                           │
│                                                                                        │
│  - WebSocket Port: 8080 (WS) / 443 (WSS)                                               │
│  - Call Private Channel: `call.{sessionId}` & `chat.{userId}`                          │
│  - Live Broadcast Channel: `live-room.{roomId}` & `live-stream.{streamId}`             │
│  - Instant Execution: ShouldBroadcastNow (Zero Queue Lag)                              │
│  - 50/50 Revenue Split Billing Engine & Diamond Wallet                                 │
└────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## ৩. ডেটাবেজ স্কিমা ও মাইগ্রেশনস

```php
// 1. messages table (ইনবক্স এবং লাইভ কল দুই জায়গার জন্যই কমন)
Schema::create('messages', function (Blueprint $table) {
    $table->id();
    $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
    $table->foreignId('receiver_id')->nullable()->constrained('users')->onDelete('cascade');
    $table->string('call_session_id')->nullable()->index();
    $table->text('message');
    $table->string('type')->default('text'); // text, image, gift
    $table->boolean('is_read')->default(false);
    $table->timestamps();
});

// 2. user_gifts table (প্রোফাইলে গিফট হিস্ট্রি শো করার জন্য)
Schema::create('user_gifts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('sender_id')->nullable()->constrained('users')->onDelete('cascade');
    $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
    $table->foreignId('gift_id')->constrained('gifts')->onDelete('cascade');
    $table->string('call_session_id')->nullable()->index();
    $table->integer('coin_amount')->default(0);
    $table->timestamps();
});
```

---

## ৪. সম্পূর্ণ REST API এন্ডপয়েন্ট রেফারেন্স

| ক্যাটাগরি | মেথড | এন্ডপয়েন্ট | বিবরণ |
| :--- | :--- | :--- | :--- |
| **Call Chat** | `POST` | `/api/v1/call/message/send` | ইন-কল রিয়েল-টাইম টেক্সট ও ছবি মেসেজ সেন্ড |
| **Call Gift** | `POST` | `/api/v1/call/gift/send` | ইন-কল লাক্সারি গিফট সেন্ড ও ৫০% ডায়মন্ড ক্রেডিট |
| **Gifts Received** | `GET` | `/api/v1/user/received-gifts` | প্রোফাইল/মি স্ক্রিনে রিসিভড গিফটস লিস্ট ও চার্ম পয়েন্ট |
| **Live Start** | `POST` | `/api/live/start` | গো লাইভ - নতুন ব্রডকাস্ট রুম চালু |
| **Active Lives** | `GET` | `/api/lives/active` | একটিভ লাইভ ব্রডকাস্টারদের ফিড তালিকা |
| **Live Join** | `POST` | `/api/live/join` | লাইভ রুমে অডিয়েন্স হিসেবে জয়েন |
| **Live Chat** | `POST` | `/api/live/send-message` | লাইভ স্ট্রিমে আনলিমিটেড পাবলিক চ্যাট কমেন্ট |
| **Live Gift** | `POST` | `/api/live/send-gift` | লাইভ স্ট্রিমে ফুল-স্ক্রিন গিফট ব্রডকাস্ট |
| **Co-Host Action** | `POST` | `/api/live/cohost-action` | কো-হোস্ট ইনভাইট / রিকোয়েস্ট একসেপ্ট / রিমুভ |

---
*Generated and verified for Chinchins Live Production Engine.*
