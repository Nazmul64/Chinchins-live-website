# 🇧🇩 Chinchins Live - Bangladesh RESTful API & Flutter Developer Full Integration Guide

> **Official API Base URL:** `https://chinchins.live/api` (Local Dev: `http://localhost:8000/api` or `http://10.0.2.2:8000/api` for Android Emulator)  
> **WebSocket / Pusher Reverb Host:** `chinchins.live` (Port `443` SSL / Port `8080` local)  
> **LiveKit RTC Server:** `wss://chinchins.live/livekit`  

---

## 📑 সূচিপত্র (Table of Contents)

1. [সমস্যা ও তাদের স্থায়ী সমাধান (Problems Identified & Solutions)](#1-সমস্যা-ও-তাদের-স্থায়ী-সমাধান-problems-identified--solutions)
   - [১. ১-অন-১ ভিডিও কল সমস্যা ও সমাধান (1-to-1 Video Call Auto-Ring / Disconnect Fix)](#১-১-অন-১-ভিডিও-কল-সমস্যা-ও-সমাধান)
   - [২. লাইভ স্ট্রিমিং এ জয়েন করলে অটো কল না যাওয়ার সমাধান (Live Stream Join != Video Call)](#২-লাইভ-স্ট্রিমিং-এ-জয়েন-করলে-অটো-কল-না-যাওয়ার-সমাধান)
   - [৩. হোস্ট লাইভে থাকলে প্রোফাইলে লাইভ স্ট্যাটাস দেখানো (Live Host Profile Badge / Watch Live)](#৩-হোস্ট-লাইভে-থাকলে-প্রোফাইলে-লাইভ-স্ট্যাটাস-দেখানো)
   - [৪. লাইভ / ভয়েস রুম কেটে দিলে লিস্ট থেকে রিয়েল-টাইমে বন্ধ হওয়া (End Stream / Party Room Real-time Cleanup)](#৪-লাইভ--ভয়েস-রুম-কেটে-দিলে-লিস্ট-থেকে-রিয়েল-টাইমে-বন্ধ-হওয়া)
2. [পূর্ণাঙ্গ ১-অন-১ কল ফ্লো ও এপিআই (1-to-1 Calling API Flow)](#2-পূর্ণাঙ্গ-১-অন-১-কল-ফ্লো-ও-এপিআই-1-to-1-calling-api-flow)
3. [লাইভ ব্রডকাস্ট ও জয়েনিং ফ্লো (Live Streaming RESTful APIs)](#3-লাইভ-ব্রডকাস্ট-ও-জয়েনিং-ফ্লো-live-streaming-restful-apis)
4. [ভয়েস পার্টি রুম ফ্লো (Voice Party Rooms RESTful APIs)](#4-ভয়েস-পার্টি-রুম-ফ্লো-voice-party-rooms-restful-apis)
5. [ইউজার প্রোফাইল এপিআই (User Profile with Live Status)](#5-ইউজার-প্রোফাইল-এপিআই-user-profile-with-live-status)
6. [ওয়েব সকেট ইভেন্ট ও চ্যানেল গাইড (WebSocket Channels & Events Matrix)](#6-ওয়েব-সকেট-ইভেন্ট-ও-চ্যানেল-গাইড-websocket-channels--events-matrix)
7. [ফ্লাটার ডেভেলপারদের জন্য সম্পূর্ণ কোড গাইড (Flutter Developer Integration Code)](#7-ফ্লাটার-ডেভেলপারদের-জন্য-সম্পূর্ণ-কোড-গাইড-flutter-developer-integration-code)

---

## 1. সমস্যা ও তাদের স্থায়ী সমাধান (Problems Identified & Solutions)

### ১. ১-অন-১ ভিডিও কল সমস্যা ও সমাধান
* **সমস্যা ১ (Auto Disconnect / Auto Ringing):** কল দিলে নিজে নিজে কল কেটে যাওয়া অথবা রিসিভ করতে না পারা।  
  * **মূল কারণ:** রিসিভার যখন `Accept` বাটন চাপত, তখন ব্যাকএন্ড থেকে কলারের কাছে `CallAccepted` এবং `private_call.accepted` ইভেন্ট ব্রডকাস্ট হতো না। ফলে কলার ডিভাইস জানত না যে রিসিভার রিসিভ করেছে এবং কলার ডিভাইস ১৫-২০ সেকেন্ড পর অটো-টাইমআউট হয়ে কল কেটে দিত।
  * **সমাধান:** `CallController::accept()`-এ `CallAccepted`, `PrivateCallAcceptedEvent`, এবং `CallSignal (type: accepted)` যুক্ত করা হয়েছে। একই সাথে Sanctum Bearer token এবং User ID সিঙ্ক করে রাখা হয়েছে।
* **সমস্যা ২ (Hanging / Ringing Desync):** একজন কল কেটে দিলে অপরজনের ফোনে রিং বাজতে থাকা।  
  * **মূল কারণ:** `cancel()` বা `reject()` বা `end()` কল হওয়ার পর রিয়েল-টাইম ব্রডকাস্ট শুধুমাত্র একটি চ্যানেলে যেত। অপর প্রান্তের লিসেনার চ্যানেল মিস করছিল।
  * **সমাধান:** `cancel()`, `reject()`, এবং `end()` মেথডে সব ধরনের ইভেন্ট (`CallCancelled`, `CallRejected`, `CallEnded`, `CallEndedEvent`, `PrivateCallEndedEvent`, `PrivateCallRejectedEvent`) এবং পোলিং সিগন্যাল (`type: bye / cancelled / rejected`) প্রেরণ নিশ্চিত করা হয়েছে। সাথে সাথে Redis ক্যাশ ক্লিয়ার ও ইউজার স্ট্যাটাস `online` এ ফিরিয়ে আনা হয়েছে।

---

### ২. লাইভ স্ট্রিমিং এ জয়েন করলে অটো কল না যাওয়ার সমাধান
* **সমস্যা:** হোস্ট লাইভে থাকলে ভিউয়ার রুমে ঢুকলেই যেন ১-অন-১ কল ট্রিগার না হয়।
* **সঠিক আর্কিটেকচার:**
  1. একজন ভিউয়ার লাইভ রুমে ঢুকলে সে কেবল **`Audience / Viewer`** হিসেবে ঢুকবে (`POST /api/live/join`)। তার ভিডিও/অডিও পাবলিশ পারমিশন থাকবে না (`can_publish: false`)। সে শুধু হোস্টের ভিডিও দেখবে এবং টেক্সট চ্যাট/গিফট পাঠাবে।
  2. ভিউয়ার যদি হোস্টের সাথে ভিডিওতে কথা বলতে চায়:
     - **অপশন A (Co-Host / Seat Request):** ভিউয়ার হোস্টকে সিট রিকোয়েস্ট পাঠাবে (`POST /api/live/request-join` বা `/api/v1/streams/{id}/seat-request`)। হোস্ট অ্যাকসেপ্ট করলে সে কো-হোস্ট সিটে বসবে।
     - **অপশন B (Paid 1-on-1 Call):** ভিউয়ার যদি পারসোনাল প্রাইভেট ১-অন-১ ভিডিও কল দিতে চায়, তবে ব্যালেন্সে কয়েন থাকলে সে আলাদা ভিডিও কল বাটনে ট্যাপ করে কল ইনিশিয়েট করবে (`POST /api/call/initiate`)।

---

### ৩. হোস্ট লাইভে থাকলে প্রোফাইলে লাইভ স্ট্যাটাস দেখানো
* **সমাধান:** যখন কোনো ইউজার বা হোস্ট লাইভ স্ট্রিমে থাকবে:
  - `GET /api/profile/{id}` এবং `GET /api/user/profile/{id}` এপিআই-তে রেসপন্সে আসবে:
    - `is_live: true`
    - `live_room: "live_123_4567"`
    - `live_stream`: অবজেক্টে থাকবে লাইভ স্ট্রিমের আইডি, টাইটেল, ভিউয়ার কাউন্ট, লাইভকিট ইউআরএল।
    - `call_button_mode: "watch_live"`
    - `call_button_text: "Watch Live"`
    - `action_button.type: "join_live"`
  - ফ্লাটার অ্যাপে প্রোফাইল পেজে ইউজার যদি `is_live == true` দেখে, তবে মূল অ্যাকশন বাটনে **"Watch Live"** (লাল/গোলাপি লাইভ ব্যাজসহ) দেখাবে এবং ট্যাপ করলে সরাসরি `LiveStreamingScreen`-এ নিয়ে যাবে।

---

### ৪. লাইভ / ভয়েস রুম কেটে দিলে লিস্ট থেকে রিয়েল-টাইমে বন্ধ হওয়া
* **সমাধান:**
  1. হোস্ট যখন লাইভ শেষ করে (`POST /api/live/end`) অথবা ভয়েস পার্টি রুম শেষ করে (`POST /api/party-rooms/{id}/end`):
     - ব্যাকএন্ডে রুমের স্ট্যাটাস `ended` হয়ে যায় এবং ডাটাবেজে `ended_at` বসে।
     - সাথে সাথে `StreamStatusChangedEvent` ইভেন্ট ব্রডকাস্ট হয় `global-live-feed`, `stream-lobby`, `live-feed`, `live-stream.{id}`, `presence-stream.{id}` চ্যানেলে `action: 'ended'` সহ।
  2. ফ্লাটার অ্যাপের হোম/হট/পার্টি স্ক্রিন যখন এই ইভেন্ট পায়, সাথে সাথে লোকাল লিস্ট থেকে ওই রুমটি রিমুভ করে দেয় (অ্যাপ রিফ্রেশ বা রিস্টার্ট করার প্রয়োজন নেই)।

---

## 2. পূর্ণাঙ্গ ১-অন-১ কল ফ্লো ও এপিআই (1-to-1 Calling API Flow)

### ১. কল ইনিশিয়েট করা (Initiate Call / Make Call)
* **Endpoint:** `POST /api/call/initiate` (বা `POST /api/call/make-call` বা `POST /api/call/instant`)
* **Headers:** `Authorization: Bearer <TOKEN>` (বা `X-User-Id: <CALLER_ID>`)
* **Request Body (JSON):**
```json
{
  "receiver_id": 12,
  "call_type": "video"
}
```
* **Success Response (200 OK):**
```json
{
  "status": true,
  "success": true,
  "message": "Call initiated! Ringing receiver...",
  "data": {
    "call_id": 142,
    "id": 142,
    "session_id": 142,
    "channel_name": "call_video_5_12_1727500000_abc1",
    "call_type": "video",
    "status": "ringing",
    "rate_per_minute": 100,
    "is_free_trial": false,
    "free_duration_seconds": 0,
    "token": "<LIVEKIT_OR_AGORA_TOKEN>",
    "livekit_token": "<LIVEKIT_TOKEN>",
    "livekit_url": "wss://chinchins.live/livekit",
    "receiver": {
      "id": 12,
      "account_id": "84920183",
      "name": "Nusrat Jahan",
      "gender": "female",
      "avatar": "https://chinchins.live/uploads/avatars/nusrat.jpg"
    }
  }
}
```

---

### ২. ইনকামিং কল চেক করা (Check Incoming Call - Polling Fallback)
* **Endpoint:** `GET /api/call/incoming` বা `POST /api/call/check-incoming`
* **Response (কল আসলে):**
```json
{
  "status": true,
  "has_incoming_call": true,
  "message": "Incoming call detected! Ring device.",
  "data": {
    "call_id": 142,
    "channel_name": "call_video_5_12_1727500000_abc1",
    "call_type": "video",
    "status": "ringing",
    "caller": {
      "id": 5,
      "account_id": "93820114",
      "name": "Tanvir Hasan",
      "avatar": "https://chinchins.live/uploads/avatars/tanvir.jpg",
      "level": "Lv.3"
    }
  }
}
```

---

### ৩. কল রিসিভ / অ্যাকসেপ্ট করা (Accept / Receive Call)
* **Endpoint:** `POST /api/call/accept` (বা `POST /api/call/answer`, `POST /api/call/receive`)
* **Request Body:**
```json
{
  "call_id": 142,
  "channel_name": "call_video_5_12_1727500000_abc1"
}
```
* **Success Response (200 OK):**
```json
{
  "status": true,
  "message": "Call accepted and connected successfully! Start audio/video media stream.",
  "data": {
    "call_id": 142,
    "channel_name": "call_video_5_12_1727500000_abc1",
    "status": "connected",
    "started_at": "2026-09-28T07:15:00.000000Z"
  }
}
```

---

### ৪. কল ডিক্লাইন / রিজেক্ট করা (Reject Call - By Receiver)
* **Endpoint:** `POST /api/call/reject` (বা `POST /api/call/decline`)
* **Request Body:**
```json
{
  "call_id": 142,
  "reason": "declined"
}
```

---

### ৫. কল ক্যানসেল করা (Cancel Call - By Caller before answer)
* **Endpoint:** `POST /api/call/cancel`
* **Request Body:**
```json
{
  "call_id": 142
}
```

---

### ৬. কল শেষ করা (End Call - By Either Party)
* **Endpoint:** `POST /api/call/end`
* **Request Body:**
```json
{
  "call_id": 142,
  "duration_seconds": 125
}
```

---

### ৭. কল চলাকালীন হার্টবিট বিলিং ডিডাকশন (Pulse Heartbeat Deduction)
* **Endpoint:** `POST /api/call/deduct-interval` (বা `POST /api/call/pulse`)
* **Request Body:**
```json
{
  "call_id": 142,
  "elapsed_seconds": 60,
  "interval_seconds": 30
}
```

---

## 3. লাইভ স্ট্রিমিং ও জয়েনিং ফ্লো (Live Streaming RESTful APIs)

### ১. একটিভ লাইভ স্ট্রিম তালিকা (Get Active Live Streams)
* **Endpoint:** `GET /api/lives/active` (বা `GET /api/live/list`, `GET /api/live/active-streams`)
* **Response:**
```json
{
  "status": true,
  "success": true,
  "data": [
    {
      "id": 18,
      "room_id": "18",
      "channel_name": "live_12_1727500000_xyz9",
      "title": "Nusrat's Musical Live 🎵",
      "cover_image_url": "https://chinchins.live/uploads/live_streaming/cover.jpg",
      "status": "live",
      "viewer_count": 45,
      "likes_count": 312,
      "host": {
        "id": 12,
        "account_id": "84920183",
        "display_name": "Nusrat Jahan",
        "avatar_url": "https://chinchins.live/uploads/avatars/nusrat.jpg"
      },
      "action_button": {
        "type": "join_live",
        "icon": "video_camera",
        "is_live": true
      }
    }
  ]
}
```

---

### ২. হোস্ট লাইভ শুরু করা (Host Start Live)
* **Endpoint:** `POST /api/stream/start` (বা `POST /api/live-stream/start`, `POST /api/live/start`)
* **Request Body (Multipart or JSON):**
```json
{
  "title": "Adda with fans ✨",
  "cover_image_url": "https://chinchins.live/uploads/cover.jpg"
}
```

---

### ৩. ভিউয়ার লাইভে জয়েন করা (Audience Join Live Stream)
* **Endpoint:** `POST /api/stream/join` (বা `POST /api/live/join`)
* **Request Body:**
```json
{
  "room_id": 18
}
```
* **Response (Audience Mode):**
```json
{
  "status": true,
  "success": true,
  "token": "<LIVEKIT_VIEWER_TOKEN>",
  "livekit_token": "<LIVEKIT_VIEWER_TOKEN>",
  "channel_name": "live_12_1727500000_xyz9",
  "livekit_url": "wss://chinchins.live/livekit",
  "data": {
    "room_id": "18",
    "role": "audience",
    "viewer_count": 46,
    "title": "Adda with fans ✨"
  }
}
```

---

### ৪. হোস্ট লাইভ বন্ধ করা (Host End Live Stream)
* **Endpoint:** `POST /api/stream/end` (বা `POST /api/live/end`, `POST /api/live-stream/end`)
* **Request Body:**
```json
{
  "room_id": 18
}
```

---

## 4. ভয়েস পার্টি রুম ফ্লো (Voice Party Rooms RESTful APIs)

* **পার্টি রুম লিস্ট:** `GET /api/party-rooms`
* **রুম তৈরি করা:** `POST /api/party-rooms/create` (প্যারামিটার: `room_title`, `room_type`: `voice`|`video`, `topic_tag`, `max_seats`: `10`)
* **রুমে জয়েন করা:** `POST /api/party-rooms/{id}/join`
* **হোস্ট রুম শেষ করা:** `POST /api/party-rooms/{id}/end`
* **সিট রিকোয়েস্ট করা:** `POST /api/party-rooms/{id}/request-seat`
* **সিটে বসা (Take Seat):** `POST /api/party-rooms/{id}/take-seat`
* **সিট ত্যাগ করা (Leave Seat):** `POST /api/party-rooms/{id}/leave-seat`

---

## 5. ইউজার প্রোফাইল এপিআই (User Profile with Live Status)

* **Endpoint:** `GET /api/profile/{id}` বা `GET /api/user/profile/{id}`
* **Response (হোস্ট লাইভে থাকলে):**
```json
{
  "status": true,
  "success": true,
  "is_live": true,
  "live_room": "live_12_1727500000_xyz9",
  "data": {
    "id": 12,
    "name": "Nusrat Jahan",
    "is_live": true,
    "live_room": "live_12_1727500000_xyz9",
    "online_status": "in_live",
    "can_call": false,
    "call_button_mode": "watch_live",
    "call_button_text": "Watch Live",
    "live_stream": {
      "id": 18,
      "room_id": "18",
      "channel_name": "live_12_1727500000_xyz9",
      "title": "Adda with fans ✨",
      "viewer_count": 46,
      "likes_count": 312,
      "livekit_url": "wss://chinchins.live/livekit"
    },
    "live_badge": {
      "label": "Live",
      "type": "live",
      "is_live": true,
      "sound_wave_animation": true
    },
    "action_button": {
      "type": "join_live",
      "icon": "video_camera",
      "is_live": true,
      "is_animating": true
    }
  }
}
```

---

## 6. ওয়েব সকেট ইভেন্ট ও চ্যানেল গাইড (WebSocket Channels & Events Matrix)

| Channel Name | Event Name | বিবরণ (Description) |
|---|---|---|
| `private-user.{userId}` | `incoming_call` / `call.incoming` | ইনকামিং ১-অন-১ কলের রিংটোন বাজানো |
| `private-user.{callerId}` | `call.accepted` / `private_call.accepted` | রিসিভার কল অ্যাকসেপ্ট করলে কলার মিডিয়া স্ট্রিম শুরু করবে |
| `private-user.{userId}` | `call.cancelled` / `call.rejected` | কল ক্যানসেল বা রিজেক্ট হলে রিং বন্ধ হবে |
| `private-user.{userId}` | `call.ended` / `CallEndedEvent` | যেকোনো প্রান্ত থেকে কল কেটে দিলে কল স্ক্রিন বন্ধ হবে |
| `call.{roomId}` | `call.ended` / `call.accepted` | কল সেশন সংক্রান্ত সমস্ত ইভেন্ট |
| `global-live-feed` | `StreamStatusChanged` | লাইভ বা পার্টি রুম শুরু/শেষ হলে লাইভ লিস্ট থেকে স্বয়ংক্রিয়ভাবে আপডেট হওয়া |
| `presence-stream.{streamId}` | `LiveViewerCountUpdated` | লাইভ স্ট্রিমে ভিউয়ার সংখ্যা আপডেট |
| `presence-stream.{streamId}` | `LiveChatMessageEvent` | লাইভ চ্যাট মেসেজ |
| `presence-stream.{streamId}` | `LiveGiftSent` | লাইভে গিফট এনিমেশন ও ব্যালেন্স আপডেট |

---

## 7. ফ্লাটার ডেভেলপারদের জন্য সম্পূর্ণ কোড গাইড (Flutter Developer Integration Code)

### ক. ইনকামিং কল লিসেনার ও রিংটোন নিয়ন্ত্রণ (Pusher / Laravel Echo)

```dart
// 1. ইউজার প্রাইভেট চ্যানেলে সাবস্ক্রাইব করা (Init on App Login)
void listenToUserEvents(int currentUserId) {
  final userChannel = pusherEcho.private('user.$currentUserId');

  // 📞 ১. ইনকামিং কল আসলে রিংটোন বাজানো এবং কল ডায়ালগ শো করা
  userChannel.listen('incoming_call', (data) {
    debugPrint('Incoming Call received: $data');
    final callId = data['call_id'] ?? data['id'];
    final channelName = data['channel_name'] ?? data['channel'];
    final caller = data['caller'] ?? {};
    
    // শো ইনকামিং কল ডায়ালগ
    showIncomingCallModal(
      callId: callId,
      channelName: channelName,
      callerName: caller['display_name'] ?? caller['name'] ?? 'Caller',
      callerAvatar: caller['avatar_url'] ?? caller['avatar'] ?? '',
      callType: data['call_type'] ?? 'video',
    );
  });

  // ✅ ২. রিসিভার কল অ্যাকসেপ্ট করলে কলার ভিডিও চালু করবে
  userChannel.listen('call.accepted', (data) {
    debugPrint('Call Accepted by Receiver: $data');
    stopOutgoingRingtone();
    navigateToVideoCallScreen(
      callId: data['call_id'],
      channelName: data['channel_name'] ?? data['room_id'],
      isCaller: true,
    );
  });

  // ❌ ৩. কলার কল কেটে দিলে অথবা রিসিভার রিজেক্ট করলে রিংটোন সাথে সাথে বন্ধ হবে
  userChannel.listen('call.cancelled', (data) {
    debugPrint('Call Cancelled: $data');
    stopIncomingRingtone();
    dismissIncomingCallModal();
  });

  userChannel.listen('call.rejected', (data) {
    debugPrint('Call Rejected: $data');
    stopOutgoingRingtone();
    showToast('User is busy or declined the call.');
    closeCallingScreen();
  });

  // 🛑 ৪. যেকোনো একজন কল কেটে দিলে দুই পাশেই কল শেষ হবে
  userChannel.listen('call.ended', (data) {
    debugPrint('Call Ended: $data');
    stopAllRingtones();
    exitVideoCallScreen();
  });
}
```

---

### খ. রিসিভার "Call Receive" / "Accept" বাটন ট্যাপ করলে

```dart
Future<void> onAcceptCall(int callId, String channelName) async {
  stopIncomingRingtone();
  
  final response = await http.post(
    Uri.parse('https://chinchins.live/api/call/accept'),
    headers: {
      'Authorization': 'Bearer $userToken',
      'Content-Type': 'application/json',
    },
    body: jsonEncode({
      'call_id': callId,
      'channel_name': channelName,
    }),
  );

  if (response.statusCode == 200) {
    // সরাসরি LiveKit / WebRTC ভিডিও কলে যুক্ত হোন
    navigateToVideoCallScreen(
      callId: callId,
      channelName: channelName,
      isCaller: false,
    );
  }
}
```

---

### গ. লাইভ স্ট্রিমিং ও রুম লিস্ট অটো-সিঙ্ক (Lobby Listener)

```dart
void listenToGlobalLiveFeed() {
  final lobbyChannel = pusherEcho.channel('global-live-feed');

  lobbyChannel.listen('StreamStatusChanged', (data) {
    final status = data['status']; // 'live' or 'ended'
    final streamId = data['stream_id'] ?? data['room_id'];

    if (status == 'ended') {
      // লোকাল লিস্ট থেকে লাইভ রুমটি মুছে ফেলুন (অটো রিফ্রেশ)
      setState(() {
        activeLiveStreamsList.removeWhere((item) => item.id.toString() == streamId.toString());
      });
    } else if (status == 'live') {
      // নতুন লাইভ স্ট্রিম আসলে লিস্টের শীর্ষে যুক্ত করুন
      fetchActiveLiveStreams(); // অথবা data['stream'] লিস্টে ইনসার্ট করুন
    }
  });
}
```

---

### ঘ. প্রোফাইল স্ক্রিনে হোস্ট লাইভে থাকলে "Watch Live" হ্যান্ডলিং

```dart
Widget buildProfileActionButton(Map<String, dynamic> userProfile) {
  final bool isLive = userProfile['is_live'] == true;
  final dynamic liveStream = userProfile['live_stream'];

  if (isLive && liveStream != null) {
    return ElevatedButton.icon(
      style: ElevatedButton.styleFrom(
        backgroundColor: Colors.pinkAccent,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
      ),
      icon: const Icon(Icons.videocam, color: Colors.white),
      label: const Text('Watch Live 🔴', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
      onPressed: () {
        // লাইভ ভিউয়ার স্ক্রিনে নিয়ে যান (ভিডিও কল নয়!)
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (context) => LiveStreamingViewerScreen(
              roomId: liveStream['id'],
              channelName: liveStream['channel_name'],
            ),
          ),
        );
      },
    );
  }

  // হোস্ট লাইভে না থাকলে সাধারণ ১-অন-১ ভিডিও কল বাটন
  return ElevatedButton.icon(
    style: ElevatedButton.styleFrom(backgroundColor: Colors.purple),
    icon: const Icon(Icons.call, color: Colors.white),
    label: const Text('Video Call'),
    onPressed: () => initiateOneToOneCall(userProfile['id']),
  );
}
```

---

## 8. সারসংক্ষেপ ও চেকপয়েন্ট (Checklist)

- [x] ১-অন-১ ভিডিও কল অ্যাকসেপ্ট করলে সাথে সাথে কলারের কাছে `call.accepted` ইভেন্ট পৌঁছাবে।
- [x] যেকোনো এক পাশ থেকে কল কাটলে দুই পাশেই রিংটোন থেমে কল ডায়ালগ বন্ধ হবে (`call.cancelled`, `call.rejected`, `call.ended`)।
- [x] লাইভে জয়েন করলে শুধুমাত্র ভিউয়ার/অডিয়েন্স মোডে জয়েন হবে, পারসোনাল ১-অন-১ কল চালু হবে না।
- [x] লাইভ হোস্টের প্রোফাইলে ঢুকলে লাইভ ব্যাজ ও "Watch Live" অ্যাকশন বাটন পাওয়া যাবে।
- [x] লাইভ বা ভয়েস রুম হোস্ট বন্ধ করে দিলে `global-live-feed` ইভেন্টের মাধ্যমে ক্লায়েন্ট লিস্ট সাথে সাথে রিয়েল-টাইমে আপডেট হবে।

---
*Created by Chinchins Live Engineering Team | Laravel Backend & Flutter Client Specifications*
