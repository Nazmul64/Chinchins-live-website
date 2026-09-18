# 🔴 Live Streaming 100% Solved Problem RESTful API & Broadcasting Engine Documentation
**System:** Chinchins Live Streaming Broadcast, Multi-Host Video Engine, Real-Time Viewer Tracking, Heart Like Reaction Sync, 1-on-1 WebRTC/Agora Video Calls, Luxury Gift Animations & Wallet Engine  
**Backend:** Laravel 12.x RESTful Backend + Laravel Reverb WebSocket Server + Agora RTC / WebRTC Coturn  
**Client:** Flutter (Android & iOS) with `agora_rtc_engine`, `laravel_echo` / `dart_pusher_channels`, `flutter_svg`, `flutter_svga` & Real-Time Presence Engine  
**Version:** 12.0.0 Production Release  
**Document File:** `live_streaming_100%_solved_problem_restful_api_broadcast.md`  

---

## 📑 সূচিপত্র (Table of Contents)
1. [১. ১০০% সমাধানকৃত সমস্যা ও টেকনিক্যাল ফিক্স (100% Solved Problems & Fixes)](#১-১০০-সমাধানকৃত-সমস্যা-ও-টেকনিক্যাল-ফিক্স)
   - [১.১ রিয়েল-টাইম লাভ রিয়েক্ট ও ফ্লোটিং হার্ট অ্যানিমেশন (Real-Time Heart Like Reactions & Counters)](#১১-রিয়েল-টাইম-লাভ-রিয়েক্ট-ও-ফ্লোটিং-হার্ট-অ্যানিমেশন)
   - [১.২ লাইভ স্ট্রিমিং ভিউয়ার কাউন্টার ও প্রেজেন্স সিঙ্ক (Real-Time Live Viewer Count & Presence Sync)](#১২-লাইভ-স্ট্রিমিং-ভিউয়ার-কাউন্টার-ও-প্রেজেন্স-সিঙ্ক)
   - [১.৩ লাইভ ট্যাবে লাইভ স্ট্রিমার না দেখানোর সমস্যার স্থায়ী সমাধান (Active Lives Feed Discovery)](#১৩-লাইভ-ট্যাবে-লাইভ-স্ট্রিমার-না-দেখানোর-সমস্যার-স্থায়ী-সমাধান)
   - [১.৪ অডিও/ভিডিও ফুল-স্ক্রিন ব্রডকাস্টিং ও অডিয়েন্স প্লেব্যাক (Agora Video & Audio Full-Screen Playback)](#১৪-অডিওভিডিও-ফুল-স্ক্রিন-ব্রডকাস্টিং-ও-অডিয়েন্স-প্লেব্যাক)
   - [১.৫ ডুয়াল-স্ক্রিন লাক্সারি গিফট ফুল-স্ক্রিন অ্যানিমেশন (Dual-Screen Luxury Gift Animation & Revenue)](#১৫-ডুয়াল-স্ক্রিন-লাক্সারি-গিফট-ফুল-স্ক্রিন-অ্যানিমেশন)
   - [১.৬ ৪-৫ জন মাল্টি-গেস্ট কো-হোস্টিং ভিডিও গ্রিড (4-5 Co-Hosts Multi-Guest Video Grid)](#১৬-৪-৫-জন-মাল্টি-গেস্ট-কো-হোস্টিং-ভিডিও-গ্রিড)
   - [১.৭ ১টি মেসেজ বার বার আসার ডুপ্লিকেশন সমাধান (Message Deduplication Algorithm)](#১৭-১টি-মেসেজ-বার-বার-আসার-ডুপ্লিকেশন-সমাধান)
2. [২. সিস্টেম আর্কিটেকচার ও রিয়েল-টাইম চ্যানেল ম্যাপিং](#২-সিস্টেম-আর্কিটেকচার-ও-রিয়েল-টাইম-চ্যানেল-ম্যাপিং)
3. [৩. ডেটাবেজ স্কিমা ও মাইগ্রেশনস](#৩-ডেটাবেজ-স্কিমা-ও-মাইগ্রেশনস)
4. [৪. সম্পূর্ণ REST API এন্ডপয়েন্ট রেফারেন্স](#৪-সম্পূর্ণ-rest-api-এন্ডপয়েন্ট-রেফারেন্স)
5. [৫. Flutter ক্লায়েন্ট ইন্টিগ্রেশন গাইড](#৫-flutter-ক্লায়েন্ট-ইন্টিগ্রেশন-গাইড)

---

## ১. ১০০% সমাধানকৃত সমস্যা ও টেকনিক্যাল ফিক্স

### ১.১ রিয়েল-টাইম লাভ রিয়েক্ট ও ফ্লোটিং হার্ট অ্যানিমেশন
* **সমস্যা:** লাইভ স্ট্রিমিং চলাকালীন হার্ট/লাভ রিয়েক্ট কাউন্টার স্ট্যাটিক বা হার্ডকোডেড (যেমন ১৫৮০/১৫৭৯) মান দেখাচ্ছিল এবং দর্শক লাভ রিয়েক্ট দিলে তা হোস্ট ও অন্য দর্শকদের স্ক্রিনে রিয়েল-টাইমে আপডেট হচ্ছিল না।
* **স্থায়ী সমাধান:**
  1. `live_streams` টেবিলে `likes_count` কলাম যুক্ত করা হয়েছে।
  2. নতুন API এন্ডপয়েন্ট `POST /api/live/like` (ও এর আলিয়াসসমূহ) তৈরি করা হয়েছে যা সিঙ্গেল/মাল্টিপল ট্যাপের লাইক কাউন্ট ইনক্রিমেন্ট করে।
  3. `LiveLikeSent` ইভেন্ট তৈরি করে Laravel Reverb-এর মাধ্যমে ইনস্ট্যান্টলি ব্রডকাস্ট (`ShouldBroadcastNow`) করা হচ্ছে।
  4. ক্লায়েন্ট স্ক্রিনে রিয়েল-টাইমে ফ্লোটিং হার্ট বাবল অ্যানিমেশন রেন্ডার হচ্ছে এবং লাইক কাউন্টার লাইভ আপডেট হচ্ছে।

```json
// POST /api/live/like Request
{
  "room_id": "12",
  "count": 5
}

// Response:
{
  "status": true,
  "success": true,
  "message": "Like sent successfully.",
  "likes_count": 1585,
  "total_likes": 1585,
  "data": {
    "room_id": "12",
    "stream_id": "12",
    "likes_count": 1585,
    "count": 5,
    "sender_id": 45,
    "sender_name": "Nazmul Hossain",
    "sender_avatar": "https://chinchins.live/uploads/avatars/user_45.jpg"
  }
}
```

---

### ১.২ লাইভ স্ট্রিমিং ভিউয়ার কাউন্টার ও প্রেজেন্স সিঙ্ক
* **সমস্যা:** লাইভ রুমে নতুন অডিয়েন্স জয়েন বা লিভ করলে হোস্টের স্ক্রিনের উপরে ভিউয়ার ব্যাজ (যেমন ১৪০ বা ১০৬০) রিয়েল-টাইমে আপডেট হচ্ছিল না।
* **স্থায়ী সমাধান:**
  1. `POST /api/live/join` এ দর্শক যুক্ত হওয়ার সাথে সাথে `viewer_count` ডাটাবেজে ইনক্রিমেন্ট হয় এবং `LiveViewerCountUpdated` ইভেন্ট ব্রডকাস্ট হয়।
  2. একই সাথে লাইভ চ্যাট বক্সে `"Nazmul joined the live stream"` অটোমেটিক সিস্টেম নোটিফিকেশন যুক্ত হয়।
  3. `POST /api/live/leave` কল হলে `viewer_count` স্বয়ংক্রিয়ভাবে ডিক্রিমেন্ট হয় ও সকল কানেক্টেড ডিভাইসে আপডেট পৌঁছে যায়।

```json
// LiveViewerCountUpdated Event Payload
{
  "room_id": "12",
  "stream_id": "12",
  "viewer_count": 141,
  "action": "joined",
  "user": {
    "id": 45,
    "display_name": "Nazmul Hossain",
    "avatar_url": "https://chinchins.live/uploads/avatars/user_45.jpg",
    "level": "Lv5"
  }
}
```

---

### ১.৩ লাইভ ট্যাবে লাইভ স্ট্রিমার না দেখানোর সমস্যার স্থায়ী সমাধান
* **সমস্যা:** হোস্ট লাইভে ব্রডকাস্টিং শুরু করার পরও অন্য মোবাইলের অ্যাপসে `LIVE` ট্যাবে গেলে `"No Live Streamers Right Now / Nobody is broadcasting live at the moment"` দেখাত।
* **মূল কারণ:** রাউটিং ফাইলে `/api/live/streamers` এবং `/api/live/hosts` প্রোফাইল কন্ট্রোলারে পাঠানো হচ্ছিল এবং একটিভ লাইভ ফিড রাউটগুলো অমিল ছিল।
* **স্থায়ী সমাধান:**
  1. `routes/api.php`-এ `/api/live/streamers`, `/api/live/hosts`, `/api/live`, `/api/lives`, `/api/live/active`, `/api/lives/active`, `/api/live/list`, `/api/live/feed`, `/api/v1/live/active`, `/api/v1/stream/active` সহ সকল লাইভ ফিড রাউটকে স্ট্যান্ডার্ডাইজ করে `LiveStreamApiController@getActiveLives`-এ লিঙ্ক করা হয়েছে।
  2. কন্ট্রোলারে মাল্টি-ফরম্যাট রেসপন্স (`data`, `streamers`, `lives`, `streams`, `list`) নিশ্চিত করা হয়েছে যাতে যেকোনো মডেল অবজেক্ট সরাসরি ডেটা পায়।
  3. হোস্ট `POST /api/live/start` করার সাথে সাথে স্ট্রিম রেকর্ড তৈরি হয়ে `status = 'live'` হিসেবে ফিডে ইনস্ট্যান্টলি শো করে।

---

### ১.৪ অডিও/ভিডিও ফুল-স্ক্রিন ব্রডকাস্টিং ও অডিয়েন্স প্লেব্যাক
* **সমস্যা:** লাইভ রুমে ঢোকার পর সাউন্ড না আসা বা অডিয়েন্স মোডে ফুল-স্ক্রিন স্ট্রিম প্লে না হওয়া।
* **সমাধান:**
  1. ব্রডকাস্টার (Host)-এর জন্য `ChannelProfileType.channelProfileLiveBroadcasting` এবং `ClientRoleType.clientRoleBroadcaster` কনফিগার করা হয়েছে।
  2. অডিয়েন্সের জন্য `ClientRoleType.clientRoleAudience` এবং `enableAudio()` + `setDefaultAudioRouteToSpeakerphone(true)` নিশ্চিত করা হয়েছে।
  3. Agora RTC ইঞ্জিন ফুল-স্ক্রিন রেজোলিউশন (720p / 1080p 30fps) এ অটো রেন্ডার হয়।

---

### ১.৫ ডুয়াল-স্ক্রিন লাক্সারি গিফট ফুল-স্ক্রিন অ্যানিমেশন
* **সমাধান:**
  1. **সেন্ডার (Viewer):** গিফট বাটনে ট্যাপ করলে লোকাল ওয়ালেট থেকে কয়েন কর্তন হয়ে সাথে সাথে লোকাল স্ক্রিনে ফুল-স্ক্রিন অ্যানিমেশন প্লে হয়।
  2. **রিসিভার (Host) ও সকল দর্শক:** WebSocket-এর মাধ্যমে `LiveGiftSent` এবং `LiveChatMessageEvent` ইনস্ট্যান্ট পৌঁছায় এবং হোস্টের স্ক্রিনে ও লাইভ রুমে ফুল-স্ক্রিন অ্যানিমেশন ও ডায়মন্ড কাউন্টার বৃদ্ধি পায়।
  3. **রেভিনিউ স্প্লিট:** হোস্ট সাথে সাথে ৫০% ডায়মন্ড তার উইথড্রয়াল ওয়ালেটে পেয়ে যায়।

---

### ১.৬ ৪-৫ জন মাল্টি-গেস্ট কো-হোস্টিং ভিডিও গ্রিড
* **ফিচারস:**
  1. হোস্ট যে কাউকে কো-হোস্ট হিসেবে ইনভাইট করতে পারেন (`POST /api/live/invite-cohost`)।
  2. অডিয়েন্স কো-হোস্ট রিকোয়েস্ট পাঠাতে পারে (`POST /api/live/join-request`)।
  3. হোস্ট রিকোয়েস্ট একসেপ্ট করলে (`POST /api/live/accept-request`) গেস্ট পাবলিশার রোলে Agora চ্যানেলে অডিও/ভিডিও পাবলিশ করে।
  4. ৪-৫ জনের গ্রিড ভিউ স্বয়ংক্রিয়ভাবে স্প্লিট স্ক্রিনে রেন্ডার হয়।
  5. হোস্ট চাইলে যেকোনো গেস্টকে যেকোনো সময় রিমুভ বা কিক করতে পারেন (`POST /api/live/kick-guest`)।

---

### ১.৭ ১টি মেসেজ বার বার আসার ডুপ্লিকেশন সমাধান
* ক্লায়েন্ট ও সার্ভার উভয় লেভেলে ৩.৫ সেকেন্ড স্লাইডিং উইন্ডো সিগনেচার হ্যাশিং (`${eventName}_${msgId}_${senderId}_${msgText}`) দিয়ে মেসেজ ও ইভেন্ট ডুপ্লিকেশন শতভাগ রোধ করা হয়েছে।

---

## ২. সিস্টেম আর্কিটেকচার ও রিয়েল-টাইম চ্যানেল ম্যাপিং

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                                 FLUTTER MOBILE CLIENT                                  │
│                                                                                        │
│  ┌───────────────────────────┐  ┌───────────────────────────┐  ┌────────────────────┐  │
│  │ Live Broadcaster (Host)   │  │ Live Audience (Viewer)    │  │ 4-5 Multi-Host Grid│  │
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
│  - Live Broadcast Channel: `live-room.{roomId}` & `live-stream.{streamId}`             │
│  - Presence Channels: `presence-live.{liveId}` & `presence-live-stream.{streamId}`     │
│  - Broadcast Events: LiveLikeSent, LiveViewerCountUpdated, LiveGiftSent, CoHostStatus   │
│  - Instant Execution: ShouldBroadcastNow (Zero Queue Lag)                              │
│  - 50/50 Revenue Split Billing Engine & Diamond Wallet                                 │
└────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## ৩. ডেটাবেজ স্কিমা ও মাইগ্রেশনস

```php
// 1. live_streams table
Schema::create('live_streams', function (Blueprint $table) {
    $table->id();
    $table->foreignId('host_id')->constrained('users')->onDelete('cascade');
    $table->string('channel_name', 150)->unique();
    $table->string('title', 200)->nullable();
    $table->string('cover_image', 500)->nullable();
    $table->string('status', 30)->default('live'); // 'live', 'ended'
    $table->unsignedInteger('viewer_count')->default(0);
    $table->unsignedBigInteger('likes_count')->default(0);
    $table->unsignedInteger('total_diamonds_earned')->default(0);
    $table->text('agora_token')->nullable();
    $table->timestamp('started_at')->useCurrent();
    $table->timestamp('ended_at')->nullable();
    $table->timestamps();
});

// 2. live_participants table
Schema::create('live_participants', function (Blueprint $table) {
    $table->id();
    $table->foreignId('live_stream_id')->constrained('live_streams')->onDelete('cascade');
    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
    $table->string('role', 30)->default('viewer'); // 'host', 'guest', 'viewer'
    $table->boolean('is_muted')->default(false);
    $table->boolean('video_enabled')->default(true);
    $table->timestamp('joined_at')->useCurrent();
    $table->timestamp('left_at')->nullable();
    $table->timestamps();
});
```

---

## ৪. সম্পূর্ণ REST API এন্ডপয়েন্ট রেফারেন্স

| ক্যাটাগরি | মেথড | এন্ডপয়েন্ট | বিবরণ |
| :--- | :--- | :--- | :--- |
| **Active Lives** | `GET` | `/api/live/active` | একটিভ লাইভ ব্রডকাস্টারদের তালিকা (LIVE ট্যাবের জন্য) |
| **Active Streamers** | `GET` | `/api/live/streamers` | লাইভ ফিড স্ট্রিমার্স লিস্ট |
| **Live Viewers** | `GET` | `/api/live/viewers` | লাইভ স্ট্রিম সক্রিয় দর্শকের তালিকা ও প্রোফাইল ডাটা |
| **Live Start** | `POST` | `/api/live/start` | গো লাইভ - নতুন ব্রডকাস্ট রুম চালু ও Agora টোকেন জেনারেশন |
| **Live End** | `POST` | `/api/live/end` | লাইভ স্ট্রিম সমাপ্তি ও সামারি ক্যালকুলেশন |
| **Live Join** | `POST` | `/api/live/join` | লাইভ রুমে অডিয়েন্স হিসেবে জয়েন ও ভিউয়ার কাউন্টার আপডেট |
| **Live Leave** | `POST` | `/api/live/leave` | লাইভ রুম ত্যাগ ও ভিউয়ার কাউন্টার ডিক্রিমেন্ট |
| **Live Like / React** | `POST` | `/api/live/like` | রিয়েল-টাইম লাভ রিয়েক্ট সেন্ড ও লাইভ কাউন্ট সিঙ্ক |
| **Live Chat** | `POST` | `/api/live/send-message` | লাইভ স্ট্রিমে পাবলিক চ্যাট কমেন্ট ব্রডকাস্ট |
| **Live Gift** | `POST` | `/api/live/send-gift` | ফুল-স্ক্রিন লাক্সারি গিফট ও ৫০% ডায়মন্ড ক্রেডিট |
| **Join Request** | `POST` | `/api/live/join-request` | ভিউয়ার কর্তৃক কো-হোস্ট হতে আবেদন |
| **Respond Request** | `POST` | `/api/live/accept-request` | হোস্ট কর্তৃক কো-হোস্ট আবেদন একসেপ্ট / রিজেক্ট |
| **Kick Guest** | `POST` | `/api/live/kick-guest` | হোস্ট কর্তৃক কো-হোস্ট গেস্ট রিমুভ / কিক |
| **Live Mute Control** | `POST` | `/api/live/mute-toggle` | লাইভ স্ট্রিমে অডিও মিউট / আনমিউট স্টেট সিঙ্ক |
| **Call Mute Control** | `POST` | `/api/v1/call/mute-toggle` | অডিও ও ভিডিও কলে মাইক্রোফোন মিউট / আনমিউট সিঙ্ক |
| **WebRTC Signal** | `POST` | `/api/live/signal` | মাল্টি-গেস্ট WebRTC SDP Offer/Answer/Candidate সিগন্যালিং |
| **In-Call Chat** | `POST` | `/api/v1/call/message/send` | ১-অন-১ ভিডিও কলে রিয়েল-টাইম মেসেজ |
| **In-Call Gift** | `POST` | `/api/v1/call/gift/send` | ১-অন-১ ভিডিও কলে লাক্সারি গিফট সেন্ড |
| **Gifts Received** | `GET` | `/api/v1/user/received-gifts` | প্রোফাইল/মি স্ক্রিনে রিসিভড গিফটস লিস্ট ও চার্ম পয়েন্ট |

---

## ৫. Flutter ক্লায়েন্ট ইন্টিগ্রেশন গাইড

### ৫.১ WebSocket চ্যানেল লিসেনিং (Laravel Echo)
```dart
void listenToLiveRoom(String roomId) {
  // ১. লাইভ চ্যাট মেসেজ লিসেনার
  echo.channel('live-room.$roomId')
      .listen('.message.sent', (data) {
        chatOverlayKey.currentState?.addMessage(data);
      })
      // ২. লাভ রিয়েক্ট লিসেনার
      .listen('.live.like', (data) {
        liveScreenKey.currentState?.showFloatingHeart(data);
        liveScreenKey.currentState?.updateLikesCount(data['likes_count']);
      })
      // ৩. ভিউয়ার কাউন্টার লিসেনার
      .listen('.viewer.updated', (data) {
        liveScreenKey.currentState?.updateViewerCount(data['viewer_count']);
      });

  // ৪. লাক্সারি গিফট লিসেনার
  echo.channel('presence-live.$roomId')
      .listen('.LiveGiftSent', (data) {
        giftAnimationKey.currentState?.playLuxuryGift(data['gift']);
      });
}
```

---
*Generated and verified for Chinchins Live Production Engine.*
