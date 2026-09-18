# 🔴 Chinchins Live — Video Streaming, Audio Call, Video Call & Live Broadcasting RESTful API Documentation

> **Base URL:** `https://chinchins.live/api`  
> **WebSocket Engine:** Laravel Reverb (`wss://chinchins.live:443/app/chinchins_reverb_key`)  
> **RTC Calling Engine:** Dynamic Dual-Engine (Hostinger VPS WebRTC / Agora Cloud RTC — strictly controlled from Admin Panel).  
> **Headers required for all API calls:**
> ```http
> Accept: application/json
> Content-Type: application/json
> Authorization: Bearer <SANCTUM_BEARER_TOKEN>
> ```
> *(Note: You may also pass `user_id` in headers `X-User-Id` or request body if token is omitted).*

---

## 📌 সূচিপত্র (Table of Contents)
1. [Dynamic Engine Resolution (VPS WebRTC vs Agora)](#1-dynamic-engine-resolution)
2. [1-on-1 Video & Audio Calling APIs](#2-1-on-1-video--audio-calling-apis)
3. [In-Call Real-Time Messaging (উভয় প্রান্তে মেসেজ সিঙ্ক)](#3-in-call-real-time-messaging)
4. [In-Call Real-Time Gift Sending (উভয় স্ক্রিনেই SVGA/Lottie অ্যানিমেশন)](#4-in-call-real-time-gift-sending)
5. [Call Termination & Ringing Control (রিং তাৎক্ষণিক বন্ধ হওয়া)](#5-call-termination--ringing-control)
6. [Live Streaming Broadcast (Host Go Live & Viewer Join)](#6-live-streaming-broadcast)
7. [Live In-Room Chat & Comments (আনলিমিটেড মেসেজিং)](#7-live-in-room-chat--comments)
8. [Live Stream Gift Animation (হোস্ট ও সকল দর্শকের স্ক্রিনে অ্যানিমেশন)](#8-live-stream-gift-animation)
9. [Admin Real-Time Live Notification](#9-admin-real-time-live-notification)
10. [Flutter Laravel Echo WebSocket Channel & Event Mapping](#10-flutter-laravel-echo-websocket-channel--event-mapping)

---

## 1. Dynamic Engine Resolution

এডমিন প্যানেলে **Hostinger VPS (WebRTC + Reverb)** অথবা **Agora Cloud Engine** যেটিই সিলেক্ট করা হোক না কেন, ব্যাকএন্ড স্বয়ংক্রিয়ভাবে ক্লায়েন্টকে সঠিক কনফিগারেশন পাঠাবে।

### 🔹 Get Active Calling Engine
* **Method:** `GET`
* **Endpoints:**
  - `/api/stream/driver`
  - `/api/stream/config`
  - `/api/v1/config/streaming-driver`
* **Response (200 OK):**
  ```json
  {
    "success": true,
    "status": true,
    "data": {
      "active_driver": "vps_webrtc", // অথবা "agora"
      "is_agora": false,
      "is_vps_webrtc": true,
      "agora_app_id": "c13c72df342d4a1386da678ba4c95f13",
      "signaling_host": "chinchins.live",
      "signaling_port": 443,
      "enable_video_call": true,
      "enable_audio_call": true,
      "enable_live_stream": true
    }
  }
  ```

---

## 2. 1-on-1 Video & Audio Calling APIs

যেকোনো ইউজার অন্য অনলাইন ইউজারকে **অডিও কল** বা **ভিডিও কল** করতে পারবে।

### 🔹 Initiate Outgoing Call
* **Method:** `POST`
* **Endpoints:**
  - `/api/call/initiate`
  - `/api/v1/call/initiate`
* **Request Body:**
  ```json
  {
    "receiver_id": 2,
    "call_type": "video" // অথবা "audio"
  }
  ```
* **Response (200 OK):**
  ```json
  {
    "status": true,
    "message": "Call initiated successfully",
    "data": {
      "call_id": 105,
      "call_session_id": "105",
      "channel_name": "call_1_2_1726671234",
      "call_type": "video",
      "active_engine": "vps_webrtc", // অথবা "agora"
      "driver": "vps_webrtc",
      "is_agora": false,
      "is_vps_webrtc": true,
      "agora_app_id": "c13c72df342d4a1386da678ba4c95f13",
      "agora_token": null, // Agora সক্রিয় থাকলে dynamic HMAC-SHA256 RTC Token আসবে
      "caller": { "id": 1, "name": "Caller Name", "avatar": "https://..." },
      "receiver": { "id": 2, "name": "Receiver Name", "avatar": "https://..." },
      "reverb_channel": "call.105",
      "ice_servers": [
        { "urls": "stun:stun.l.google.com:19302" },
        { "urls": "stun:stun1.l.google.com:19302" }
      ]
    }
  }
  ```

### 🔹 Accept Incoming Call
* **Method:** `POST`
* **Endpoints:** `/api/call/accept` বা `/api/call/answer`
* **Request Body:** `{"call_id": 105}`

---

## 3. In-Call Real-Time Messaging

অডিও বা ভিডিও কল চলাকালীন উভয় প্রান্তের ইউজার একে অপরকে মেসেজ পাঠাতে পারবে। প্রেরক মেসেজ পাঠালে তা সাথে সাথে **প্রেরক ও প্রাপক উভয়ের স্ক্রিনেই রিয়েলটাইমে প্রদর্শিত হবে**।

### 🔹 Send Message During Call
* **Method:** `POST`
* **Endpoints:**
  - `/api/call/send-message`
  - `/api/call/message/send`
  - `/api/call/chat/send`
  - `/api/v1/call/message/send`
* **Request Body:**
  ```json
  {
    "call_id": "105", // অথবা "call_session_id": "105"
    "message": "হ্যালো! কেমন আছো?",
    "type": "text"
  }
  ```
  *(নোট: `receiver_id` প্রদান করা ঐচ্ছিক; ব্যাকএন্ড সক্রিয় কল সেশন থেকে নিজে থেকেই অপর পক্ষের ইউজার আইডি নির্ধারণ করে নেবে)।*
* **Response (200 OK):**
  ```json
  {
    "status": true,
    "message": "Message sent successfully",
    "data": {
      "id": 501,
      "call_id": "105",
      "sender_id": 1,
      "receiver_id": 2,
      "message": "হ্যালো! কেমন আছো?",
      "type": "text",
      "timestamp": "2026-09-18T18:45:00Z"
    }
  }
  ```

### 🔹 Flutter WebSocket Listener (In-Call Messages)
Flutter অ্যাপে Laravel Echo দিয়ে নিচের যেকোনো চ্যানেলে লিসেন করুন:
```dart
// ১. কল সেশন চ্যানেলে লিসেন করুন (উভয় ফোনে মেসেজ শো হবে)
Echo.instance.channel('call.$callId')
    .listen('.call.message.sent', (data) {
        setState(() {
            messagesList.add({
                'sender_id': data['sender_id'],
                'sender_name': data['sender_name'],
                'message': data['message'],
                'created_at': data['timestamp']
            });
        });
    });

// ২. অথবা ইউজারের নিজস্ব চ্যানেলে লিসেন করুন:
Echo.instance.channel('user.$myUserId')
    .listen('.call.message.sent', (data) {
        setState(() {
            messagesList.add(data);
        });
    });
```

---

## 4. In-Call Real-Time Gift Sending

কল চলাকালীন যেকোনো উপহার পাঠালে:
1. প্রেরকের ওয়ালেট থেকে কয়েন কাটা হবে।
2. প্রাপকের ওয়ালেটে ৫০% কয়েন যোগ হবে।
3. **উভয় স্ক্রিনেই সাথে সাথে SVGA বা Lottie অ্যানিমেশন প্লে হবে**।

### 🔹 Send Gift During Call
* **Method:** `POST`
* **Endpoints:**
  - `/api/call/send-gift`
  - `/api/call/gift/send`
  - `/api/call/gift`
  - `/api/v1/call/gift/send`
* **Request Body:**
  ```json
  {
    "call_id": "105",
    "gift_id": 12,
    "quantity": 1
  }
  ```
* **Response (200 OK):**
  ```json
  {
    "status": true,
    "message": "Gift sent successfully!",
    "data": {
      "gift": {
        "id": 12,
        "name": "Luxury Sports Car",
        "coin_price": 500,
        "icon_url": "https://chinchins.live/assets/gifts/car.png",
        "animation_asset_url": "https://chinchins.live/assets/gifts/car.svga",
        "animation_type": "svga"
      },
      "sender": { "id": 1, "name": "Sender Name", "avatar": "https://..." },
      "receiver": { "id": 2, "name": "Receiver Name", "avatar": "https://..." },
      "quantity": 1,
      "total_coins": 500
    }
  }
  ```

### 🔹 Flutter WebSocket Listener (In-Call Gifts)
```dart
Echo.instance.channel('call.$callId')
    .listen('.gift.received', (data) {
        String svgaUrl = data['gift']['animation_asset_url'];
        // উভয় ফোনেই অ্যানিমেশন ট্রিগার করুন
        svgaPlayerController.playUrl(svgaUrl);
    });
```

---

## 5. Call Termination & Ringing Control

কল বাতিল, রিজেক্ট বা হ্যাংআপ করলে **অপর প্রান্তের ফোনে রিংটোন সাথে সাথে বন্ধ হবে** এবং কল স্ক্রিন পপ হয়ে যাবে।

### 🔹 End / Reject / Cancel Call
* **Endpoints:**
  - `POST /api/call/reject` (কল প্রত্যাখ্যান)
  - `POST /api/call/cancel` (কলকারী কল কেটে দিলে)
  - `POST /api/call/end` (চলমান কল সমাপ্তি)
* **Request Body:** `{"call_id": 105}`
* **Flutter WebSocket Listeners:**
  ```dart
  Echo.instance.channel('user.$myUserId')
      .listen('.call.rejected', (data) {
          RingtonePlayer.stop();
          Navigator.of(context).pop();
      })
      .listen('.call.cancelled', (data) {
          RingtonePlayer.stop();
          Navigator.of(context).pop();
      })
      .listen('.call.ended', (data) {
          RingtonePlayer.stop();
          Navigator.of(context).pop();
      });
  ```

---

## 6. Live Streaming Broadcast

### 🔹 ক. হোস্ট লাইভ শুরু করা (Host Start Live)
হোস্ট যখন "Go Live" বাটনে ক্লিক করবে:
* **Method:** `POST`
* **Endpoints:**
  - `/api/live/start`
  - `/api/stream/start`
  - `/api/v1/stream/start`
  - `/api/live-stream/start`
* **Request Body (Multipart Form / JSON):**
  ```json
  {
    "title": "Welcome to my Live Broadcast!",
    "cover_image_url": "https://..." // অথবা 'cover_image' ফাইলে ছবি আপলোড
  }
  ```
* **Response (200 OK):**
  ```json
  {
    "status": true,
    "message": "Live stream broadcast started successfully!",
    "data": {
      "room_id": "18",
      "live_stream_id": 18,
      "stream_id": "18",
      "channel_name": "live_1_1726671234_abc",
      "title": "Welcome to my Live Broadcast!",
      "active_engine": "vps_webrtc", // অথবা "agora" (এডমিন অনুযায়ী)
      "driver": "vps_webrtc",
      "is_agora": false,
      "is_vps_webrtc": true,
      "app_id": "c13c72df342d4a1386da678ba4c95f13",
      "agora_app_id": "c13c72df342d4a1386da678ba4c95f13",
      "uid": 1,
      "agora_uid": 1,
      "token": null, // Agora সক্রিয় থাকলে ভ্যালিড RTC Token থাকবে
      "reverb_channel": "presence-stream.18",
      "host": {
        "id": 1,
        "name": "Host Name",
        "avatar_url": "https://..."
      }
    }
  }
  ```

### 🔹 খ. সক্রিয় লাইভ স্ট্রিম তালিকা (Get Active Live Streams)
অন্য যেকোনো ইউজার যখন লাইভ বাটনে ক্লিক করবে, সে সকল সক্রিয় লাইভ স্ট্রিমার দেখতে পাবে:
* **Method:** `GET`
* **Endpoints:**
  - `/api/live/active`
  - `/api/lives`
  - `/api/live-streams`
  - `/api/live/feed`
  - `/api/live/list`
  - `/api/stream/active`
* **Response (200 OK):**
  ```json
  {
    "status": true,
    "success": true,
    "message": "Active live streams retrieved successfully.",
    "data": [
      {
        "id": 18,
        "room_id": "18",
        "channel_name": "live_1_1726671234_abc",
        "title": "Welcome to my Live Broadcast!",
        "cover_image": "https://...",
        "viewer_count": 12,
        "host": {
          "id": 1,
          "name": "Host Name",
          "avatar_url": "https://...",
          "level": "Lv10"
        }
      }
    ],
    "streamers": [ /* একই তালিকা */ ],
    "lives": [ /* একই তালিকা */ ]
  }
  ```

### 🔹 গ. দর্শক লাইভে জয়েন করা (Audience Join Live)
* **Method:** `POST`
* **Endpoints:** `/api/live/join` অথবা `/api/stream/join`
* **Request Body:** `{"room_id": "18"}`
* **Response (200 OK):** দর্শকের জন্য টোকেন এবং প্রেজেন্স চ্যানেল রিটার্ন করে।

---

## 7. Live In-Room Chat & Comments

লাইভ রুমে থাকা সকল দর্শক ও হোস্ট আনলিমিটেড চ্যাট করতে পারবে।

### 🔹 Send Live Comment
* **Method:** `POST`
* **Endpoints:**
  - `/api/live/send-message`
  - `/api/live/comment`
  - `/api/v1/stream/comment`
* **Request Body:**
  ```json
  {
    "room_id": "18",
    "message": "চমৎকার লাইভ স্ট্রিমিং!"
  }
  ```
* **Flutter WebSocket Listener (All Viewers & Host):**
  ```dart
  Echo.instance.channel('presence-stream.$roomId')
      .listen('.message.sent', (data) {
          setState(() {
              liveComments.add({
                  'user_id': data['user_id'],
                  'user_name': data['user_name'],
                  'message': data['message'],
                  'avatar': data['user_avatar']
              });
          });
      });
  ```

---

## 8. Live Stream Gift Animation

লাইভে দর্শক হোস্টকে গিফট পাঠালে **হোস্ট এবং রুমে থাকা সকল দর্শকের স্ক্রিনে একযোগে অ্যানিমেশন প্রদর্শিত হবে**।

### 🔹 Send Live Gift
* **Method:** `POST`
* **Endpoints:**
  - `/api/live/send-gift`
  - `/api/live/gift`
  - `/api/v1/stream/send-gift`
* **Request Body:**
  ```json
  {
    "room_id": "18",
    "gift_id": 5,
    "quantity": 1
  }
  ```
* **Flutter WebSocket Listener (All Viewers & Host):**
  ```dart
  Echo.instance.channel('presence-stream.$roomId')
      .listen('.gift.received', (data) {
          String svgaAsset = data['gift']['animation_asset_url'];
          // হোস্ট ও সকল দর্শকের স্ক্রিনে SVGA প্লেয়ার চালু হবে
          svgaPlayer.play(svgaAsset);
      });
  ```

---

## 9. Admin Real-Time Live Notification

যখনই কোনো হোস্ট লাইভ স্ট্রিমিং শুরু করবে (`/api/live/start`), ব্যাকএন্ড সাথে সাথে:
1. **ActivityLog** অডিট ট্রেইলে রিয়েল-টাইম লগ রেকর্ড করে (`live_started`)।
2. সকল এডমিন ইউজারের জন্য **Notification** তৈরি করে:
   ```json
   {
     "title": "🔴 New Live Stream Started",
     "message": "Host Name (#1000000001) has started live broadcasting: 'Welcome to my Live Broadcast!'",
     "data": { "stream_id": 18, "channel_name": "live_1_1726671234_abc" }
   }
   ```
3. এডমিন প্যানেলের গ্লোবাল লবি চ্যানেলে রিয়েল-টাইম ইভেন্ট ব্রডকাস্ট পাঠায়।

---

---

## 11. Party Rooms Feed & Voice Party Grid APIs

### 🔹 1. Browse Party Rooms (Party Tab Feed)
* **Method:** `GET`
* **Endpoints:** `/api/party-rooms`, `/api/v1/party-rooms/list`
* **Response (200 OK):**
  ```json
  {
    "success": true,
    "status": true,
    "data": [
      {
        "id": 12,
        "room_id": "P89201",
        "room_title": "SONA 🌹 SONA 🌹",
        "room_type": "voice",
        "topic_tag": "ChitChat",
        "room_cover": "https://chinchins.live/uploads/host_image/cover1.jpg",
        "viewer_count": 13,
        "heat_score": 35150,
        "heat_score_formatted": "35.15K",
        "speaking_indicator": true,
        "active_seats_avatars": [
          {
            "seat_index": 1,
            "user_id": 101,
            "name": "Alex",
            "avatar_url": "https://chinchins.live/uploads/alex.jpg",
            "is_speaking": true,
            "is_muted": false
          },
          {
            "seat_index": 2,
            "user_id": 102,
            "name": "Maria",
            "avatar_url": "https://chinchins.live/uploads/maria.jpg",
            "is_speaking": false,
            "is_muted": false
          }
        ],
        "host": {
          "id": 5,
          "name": "SONA",
          "avatar_url": "https://chinchins.live/uploads/sona.jpg",
          "level": 8,
          "charm_level": 6
        }
      }
    ]
  }
  ```

---

## 12. 1-on-1 Call Moderation & Abuse Reporting (Admin Video Inspection)

### 🔹 1. Save Call Recording & Snapshot Logs
* **Method:** `POST`
* **Endpoints:** `/api/v1/call/recording/save`, `/api/call/recording/save`
* **Request Body:**
  ```json
  {
    "call_session_id": "call_105_1726671234",
    "caller_id": 1,
    "receiver_id": 2,
    "call_type": "video",
    "duration_seconds": 185,
    "recording_url": "https://chinchins.live/recordings/call_105.mp4",
    "snapshots": [
      "https://chinchins.live/snapshots/call_105_01.jpg",
      "https://chinchins.live/snapshots/call_105_02.jpg"
    ],
    "engine": "vps_webrtc"
  }
  ```

### 🔹 2. Submit Call Abuse Complaint / Report
* **Method:** `POST`
* **Endpoints:** `/api/v1/call/report`, `/api/call/report`
* **Request Body:**
  ```json
  {
    "call_session_id": "call_105_1726671234",
    "reported_user_id": 2,
    "reason": "Inappropriate Behavior / Harassment",
    "description": "The user exhibited vulgar behavior during video call."
  }
  ```

---

## 13. User Honor Profile & Gifts Received Wall (Me Screen)

### 🔹 Get Honor Profile & Gifts Received
* **Method:** `GET`
* **Endpoints:** `/api/v1/user/honor-profile`, `/api/user/honor-profile`
* **Query Params:** `?user_id=2`
* **Response (200 OK):**
  ```json
  {
    "status": true,
    "data": {
      "user": {
        "id": 2,
        "name": "SONA",
        "avatar_url": "https://chinchins.live/uploads/sona.jpg",
        "is_live": true,
        "video_rate": 2700,
        "video_rate_tag": "2700/min"
      },
      "honor": {
        "charm_level": 6,
        "charm_level_tag": "Lv6",
        "top_fan": {
          "rank": 1,
          "name": "SUPER_BOY...",
          "avatar_url": "https://chinchins.live/uploads/fan.jpg"
        }
      },
      "gifts_received": [
        {
          "id": 1,
          "name": "Dragon King",
          "icon_url": "https://chinchins.live/images/gifts/dragon.png",
          "coin_tag": "18.88K",
          "count_formatted": "x2",
          "received_count": 2
        },
        {
          "id": 2,
          "name": "Super Car",
          "icon_url": "https://chinchins.live/images/gifts/car.png",
          "coin_tag": "9.99K",
          "count_formatted": "x50",
          "received_count": 50
        }
      ]
    }
  }
  ```

---

*ডকুমেন্টেশন প্রস্তুতকারক: Antigravity AI — Chinchins Live Backend Team*
