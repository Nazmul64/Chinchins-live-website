# 🔴 TikTok & Bigo Live Standard: Live Streaming & Broadcasting Implementation Engine

**System:** Chinchins Live Enterprise Streaming Platform (TikTok & Bigo Live Standard Architecture)  
**Media Streaming Engine:** Dual-Engine Hybrid (Agora RTC Engine v4.x + Self-Hosted WebRTC SFU / Coturn STUN/TURN + NGINX)  
**Real-Time State & Signaling:** Laravel 12.x RESTful Backend + Laravel Reverb WebSocket Server  
**Client:** Flutter (Android & iOS) with `agora_rtc_engine`, `flutter_webrtc`, `laravel_echo` / `dart_pusher_channels`, `flutter_svga`, `lottie` & Real-Time Presence Engine  
**Version:** 12.0.0 Production Enterprise Release  
**Document File:** `live_streaming_broadcasting_implementation.md`

---

## 📑 সূচিপত্র (Table of Contents)
1. [১. টিকটক ও বিগো লাইভ স্ট্যান্ডার্ড আর্কিটেকচার](#১-টিকটক-ও-বিগো-লাইভ-স্ট্যান্ডার্ড-আর্কিটেকচার)
2. [২. সার্ভার ও ব্যাকগ্রাউন্ড ওয়ার্কার রিকোয়ারমেন্ট](#২-সার্ভার-ও-ব্যাকগ্রাউন্ড-ওয়ার্কার-রিকোয়ারমেন্ট)
3. [৩. লারাভেল রিয়েল-টাইম ব্রডকাস্টিং ইভেন্ট ও চ্যানেল আর্কিটেকচার](#৩-লারাভেল-রিয়েল-টাইম-ব্রডকাস্টিং-ইভেন্ট-ও-চ্যানেল-আর্কিটেকচার)
4. [৪. সম্পূর্ণ RESTful API স্পেসিফিকেশন](#৪-সম্পূর্ণ-restful-api-স্পেসিফিকেশন)
   - [৪.১ লাইভ স্ট্রিম ও ফিড ম্যানেজমেন্ট](#৪১-লাইভ-স্ট্রিম-ও-ফিড-ম্যানেজমেন্ট)
   - [৪.২ অডিয়েন্স স্ট্রিম জয়েনিং ও রিয়েল-টাইম ভিউয়ার্স](#৪২-অডিয়েন্স-স্ট্রিম-জয়েনিং-ও-রিয়েল-টাইম-ভিউয়ার্স)
   - [৪.৩ লাইভ লাভ রিয়েক্ট ও ফ্লোটিং হার্ট বাবল](#৪৩-লাইভ-লাভ-রিয়েক্ট-ও-ফ্লোটিং-হার্ট-বাবল)
   - [৪.৪ ডুয়াল-স্ক্রিন লাক্সারি গিফট ও ৫০% ডায়মন্ড স্প্লিট](#৪৪-ডুয়াল-স্ক্রিন-লাক্সারি-গিফট-ও-৫০-ডায়মন্ড-স্প্লিট)
   - [৪.৫ লাইভ চ্যাট মেসেজিং ও ডুপ্লিকেশন প্রতিরোধ](#৪৫-লাইভ-চ্যাট-মেসেজিং-ও-ডুপ্লিকেশন-প্রতিরোধ)
   - [৪.৬ ৪/৯ সিট মাল্টি-গেস্ট কো-হোস্টিং ও পিকে ব্যাটল](#৪৬-৪৯-সিট-মাল্টি-গেস্ট-কো-হোস্টিং-ও-পিকে-ব্যাটল)
   - [৪.৭ হাইব্রিড ইঞ্জিন সুইচিং কন্ট্রোল (Agora vs VPS WebRTC)](#৪৭-হাইব্রিড-ইঞ্জিন-সুইচিং-কন্ট্রোল-agora-vs-vps-webrtc)
5. [৫. ডেটাবেজ স্কিমা ও মাইগ্রেশনস](#৫-ডেটাবেজ-স্কিমা-ও-মাইগ্রেশনস)
6. [৬. Flutter ক্লায়েন্ট ফুল ইমপ্লিমেন্টেশন গাইড](#৬-flutter-ক্লায়েন্ট-ফুল-ইমপ্লিমেন্টেশন-গাইড)

---

## ১. টিকটক ও বিগো লাইভ স্ট্যান্ডার্ড আর্কিটেকচার

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                                 FLUTTER MOBILE CLIENT                                  │
│                                                                                        │
│  ┌───────────────────────────┐  ┌───────────────────────────┐  ┌────────────────────┐  │
│  │ TikTok-Style Vertical     │  │ Bigo Multi-Guest Grid     │  │ 1v1 PK Battle      │  │
│  │ Swipe Feed (PageView)     │  │ (4 / 9 Mic Seats)         │  │ Split Screen Timer │  │
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
│  - Global Lobby Channel: `stream-lobby` & `presence-stream-lobby`                      │
│  - Live Room Channels: `presence-stream.{id}`, `presence-room.{id}`, `live-room.{id}`   │
│  - Instant Execution: ShouldBroadcastNow (Zero Queue Lag)                              │
│  - 50/50 Revenue Split Billing Engine & Diamond Wallet                                 │
└───────────────────────────────────────────────┬────────────────────────────────────────┘
                                                │
                 ┌──────────────────────────────┴─────────────────────────────┐
                 ▼                                                           ▼
┌──────────────────────────────────────────────┐  ┌──────────────────────────────────────┐
│           ENGINE A: AGORA RTC ENGINE         │  │    ENGINE B: SELF-HOSTED VPS WEBRTC  │
│  - Channel Profile: Live Broadcasting        │  │  - NGINX + Reverb WS Signaling       │
│  - Broadcaster: 720p/1080p 30fps Audio/Video │  │  - Coturn STUN/TURN (3478 / 5349)    │
│  - Audience: Ultra-Low Latency (<300ms)      │  │  - P2P / SFU Mesh Media Pipeline     │
└──────────────────────────────────────────────┘  └──────────────────────────────────────┘
```

### প্রধান ফিচারসমূহ:
1. **TikTok Style Vertical Swipe Feed:** ব্যবহারকারী লাইভ ওপেন করার পর উপর-নিচ সুইপ করে যেকোনো লাইভে সেকেন্ডের মধ্যে সুইচ করতে পারে।
2. **প্রি-ফেচিং ও অটো রিয়েল-টাইম রোস্টার:** কোনো হোস্ট লাইভ শুরু (`StreamStatusChangedEvent` - `live`) বা শেষ (`ended`) করলে গ্লোবাল `stream-lobby` চ্যানেলে তাৎক্ষণিক আপডেট যায়। ফলে ব্যবহারকারীদের অ্যাপ রিফ্রেশ না করেই লাইভ ট্যাব রিয়েল-টাইমে আপডেট থাকে।
3. **মাল্টি-সিট কো-হোস্টিং (4 বা 9 সিট):** হোস্ট মাইক সিট অন রাখলে অডিয়েন্স `POST /api/v1/live/{id}/seat-request` পাঠিয়ে কো-হোস্ট হতে পারে।
4. **ডুয়াল-স্ক্রিন লাক্সারি গিফট অ্যানিমেশন:** সেন্ডার ও রিসিভার দুজনের ডিভাইসেই একই সাথে ফুল-স্ক্রিন SVGA/Lottie অ্যানিমেশন প্লে হয় এবং ৫০% ডায়মন্ড হোস্টের উইথড্রয়াল ওয়ালেটে জমা হয়।

---

## ২. সার্ভার ও ব্যাকগ্রাউন্ড ওয়ার্কার রিকোয়ারমেন্ট

### ১. Reverb WebSocket সার্ভার
```bash
php artisan reverb:start --host="0.0.0.0" --port=8080
```

### ২. কিউ ওয়ার্কার (Queue Worker)
```bash
php artisan queue:work --sleep=3 --tries=3
```

### ৩. NGINX WebSocket Reverse Proxy কনফিগারেশন (`/etc/nginx/sites-available/chinchins.live`)
```nginx
location /app {
    proxy_pass http://127.0.0.1:8080;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "Upgrade";
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_read_timeout 600s;
    proxy_send_timeout 600s;
}
```

---

## ৩. লারাভেল রিয়েল-টাইম ব্রডকাস্টিং ইভেন্ট ও চ্যানেল আর্কিটেকচার

| ইভেন্ট ক্লাস | চ্যানেলসমূহ | ব্রডকাস্ট নাম | উদ্দেশ্য |
| :--- | :--- | :--- | :--- |
| `StreamStatusChangedEvent` | `stream-lobby`, `presence-stream-lobby` | `StreamStatusChanged` | হোস্ট লাইভ শুরু/শেষ করলে পুরো অ্যাপের লাইভ ফিড ইনস্ট্যান্ট আপডেট |
| `LiveGiftSent` / `GiftSentEvent` | `presence-stream.{id}`, `presence-room.{id}`, `live-room.{id}` | `LiveGiftSent` / `gift.sent` | সেন্ডার ও রিসিভারের স্ক্রিনে ফুল-স্ক্রিন SVGA গিফট অ্যানিমেশন |
| `LiveChatMessageEvent` | `presence-stream.{id}`, `presence-room.{id}`, `live-room.{id}` | `message.sent` | লাইভ চ্যাট মেসেজ ইনস্ট্যান্ট ব্রডকাস্ট (ডামি ছাড়া আসল মেসেজ) |
| `LiveLikeSent` | `presence-stream.{id}`, `presence-room.{id}`, `live-room.{id}` | `live.like` | স্ক্রিন ডাবল ট্যাপে লাভ রিয়েক্ট ফ্লোটিং হার্ট বাবল ও লাইভ কাউন্ট |
| `LiveViewerCountUpdated` | `presence-stream.{id}`, `presence-room.{id}`, `live-room.{id}` | `viewer.updated` | ভিউয়ার জয়েন/লিভ ও সক্রিয় ভিউয়ার সংখ্যা সিঙ্ক |
| `CoHostStatusEvent` | `presence-stream.{id}`, `presence-room.{id}`, `live-room.{id}` | `cohost.status` | ৪/৯ সিট মাইক অন/অফ এবং গেস্ট স্পিকার প্রমোশন |
| `LiveStreamEnded` | `presence-stream.{id}`, `presence-room.{id}`, `live-room.{id}` | `stream.ended` | হোস্ট লাইভ বন্ধ করলে সামারি দেখানো ও রুম ক্লোজ |

---

## ৪. সম্পূর্ণ RESTful API স্পেসিফিকেশন

### ৪.১ লাইভ স্ট্রিম ও ফিড ম্যানেজমেন্ট

#### ১. হোস্ট লাইভ শুরু করা (Go Live)
* **Endpoint:** `POST /api/v1/streams/start` (অথবা `/api/v1/live/start`, `/api/live/start`)
* **Headers:** `Authorization: Bearer {token}`, `Content-Type: application/json`

**Request Body:**
```json
{
  "title": "Evening Hangout & Music 🎵",
  "cover_image_url": "https://chinchins.live/uploads/covers/live_12.jpg",
  "preferred_engine": "agora"
}
```

**Success Response (200 OK):**
```json
{
  "status": true,
  "success": true,
  "message": "Live stream broadcast started successfully!",
  "data": {
    "stream_id": 45,
    "room_id": "45",
    "channel_name": "live_12_1789701823_a9b1",
    "title": "Evening Hangout & Music 🎵",
    "status": "active",
    "role": "host",
    "viewer_count": 1,
    "likes_count": 0,
    "active_engine": "agora",
    "agora_token": "006c13c72df342d4a1386da678ba4c95f13IAB...",
    "rtc_token": "006c13c72df342d4a1386da678ba4c95f13IAB...",
    "reverb_channel": "presence-stream.45",
    "engine_credentials": {
      "app_id": "c13c72df342d4a1386da678ba4c95f13",
      "token": "006c13c72df342d4a1386da678ba4c95f13IAB...",
      "channel_name": "live_12_1789701823_a9b1",
      "uid": 12
    },
    "host": {
      "id": 12,
      "account_id": "87654321",
      "display_name": "Nazmul Hossain",
      "avatar_url": "https://chinchins.live/uploads/avatars/user_12.jpg",
      "level": "Lv5"
    }
  }
}
```

---

#### ২. অ্যাক্টিভ লাইভ স্ট্রিমার্স ফিড (TikTok/Bigo Swipe List)
* **Endpoint:** `GET /api/v1/streams/active` (অথবা `/api/v1/live/feed`, `/api/live/streamers`, `/api/live/active`)
* **Headers:** `Authorization: Bearer {token}`

**Success Response (200 OK):**
```json
{
  "status": true,
  "success": true,
  "count": 1,
  "data": [
    {
      "id": 45,
      "stream_id": 45,
      "room_id": "45",
      "channel_name": "live_12_1789701823_a9b1",
      "title": "Evening Hangout & Music 🎵",
      "cover_image": "https://chinchins.live/uploads/covers/live_12.jpg",
      "status": "live",
      "viewer_count": 14,
      "likes_count": 250,
      "host": {
        "id": 12,
        "account_id": "87654321",
        "display_name": "Nazmul Hossain",
        "name": "Nazmul Hossain",
        "avatar_url": "https://chinchins.live/uploads/avatars/user_12.jpg",
        "level": "Lv5",
        "gender": "male",
        "country": "BD"
      }
    }
  ]
}
```

---

### ৪.২ অডিয়েন্স স্ট্রিম জয়েনিং ও রিয়েল-টাইম ভিউয়ার্স

#### ১. লাইভ রুমে অডিয়েন্স হিসেবে জয়েন করা
* **Endpoint:** `POST /api/v1/streams/{stream_id}/join` (অথবা `/api/v1/live/{stream_id}/join`, `/api/live/join`)
* **Headers:** `Authorization: Bearer {token}`

**Success Response (200 OK):**
```json
{
  "status": true,
  "success": true,
  "message": "Joined live stream successfully.",
  "data": {
    "stream_id": 45,
    "room_id": "45",
    "channel_name": "live_12_1789701823_a9b1",
    "title": "Evening Hangout & Music 🎵",
    "role": "audience",
    "active_engine": "agora",
    "agora_token": "006c13c72df342d4a1386da678ba4c95f13IAC...",
    "rtc_token": "006c13c72df342d4a1386da678ba4c95f13IAC...",
    "reverb_channel": "presence-stream.45",
    "seat_layout": "single",
    "viewer_count": 15,
    "likes_count": 250,
    "engine_credentials": {
      "app_id": "c13c72df342d4a1386da678ba4c95f13",
      "token": "006c13c72df342d4a1386da678ba4c95f13IAC...",
      "channel_name": "live_12_1789701823_a9b1",
      "uid": 88
    },
    "host": {
      "id": 12,
      "display_name": "Nazmul Hossain",
      "avatar_url": "https://chinchins.live/uploads/avatars/user_12.jpg",
      "level": "Lv5"
    }
  }
}
```

---

### ৪.৩ লাইভ লাভ রিয়েক্ট ও ফ্লোটিং হার্ট বাবল

* **Endpoint:** `POST /api/v1/streams/{stream_id}/heart-beat` (অথবা `/api/live/like`)
* **Headers:** `Authorization: Bearer {token}`, `Content-Type: application/json`

**Request Body:**
```json
{
  "count": 5
}
```

**Success Response (200 OK):**
```json
{
  "status": true,
  "success": true,
  "likes_count": 255,
  "total_likes": 255,
  "data": {
    "stream_id": 45,
    "room_id": "45",
    "likes_count": 255,
    "count": 5,
    "sender_id": 88,
    "sender_name": "Viewer Name"
  }
}
```

---

### ৪.৪ ডুয়াল-স্ক্রিন লাক্সারি গিফট ও ৫০% ডায়মন্ড স্প্লিট

#### ১. গিফট সেন্ড করা
* **Endpoint:** `POST /api/v1/streams/{stream_id}/send-gift` (অথবা `/api/live/send-gift`)
* **Headers:** `Authorization: Bearer {token}`, `Content-Type: application/json`

**Request Body:**
```json
{
  "gift_id": 7,
  "quantity": 1
}
```

**Success Response (200 OK):**
```json
{
  "status": true,
  "success": true,
  "message": "Gift sent successfully",
  "data": {
    "sender_id": 88,
    "sender_name": "Viewer Name",
    "gift_id": 7,
    "gift_name": "Rose",
    "animation_type": "svga",
    "animation_url": "https://chinchins.live/assets/gifts/rose.svga",
    "is_fullscreen": true,
    "remaining_wallet_balance": 350,
    "host_diamonds_earned": 50
  }
}
```

#### ২. হোস্টের রিসিভড গিফট সামারি
* **Endpoint:** `GET /api/v1/streams/{stream_id}/gift-summary`
* **Headers:** `Authorization: Bearer {token}`

**Success Response (200 OK):**
```json
{
  "status": true,
  "data": {
    "total_coins_earned": 1200,
    "total_diamonds_earned": 600,
    "gifts": [
      {
        "gift_id": 7,
        "name": "Rose",
        "count": 12,
        "icon": "https://chinchins.live/assets/gifts/rose_thumb.png",
        "total_coins": 1200
      }
    ]
  }
}
```

---

### ৪.৫ লাইভ চ্যাট মেসেজিং

* **Endpoint:** `POST /api/v1/streams/{stream_id}/messages` (অথবা `/api/live/send-message`)
* **Headers:** `Authorization: Bearer {token}`, `Content-Type: application/json`

**Request Body:**
```json
{
  "message": "Hello Host! Great live stream!"
}
```

**Success Response (200 OK):**
```json
{
  "status": true,
  "data": {
    "message_id": 1054,
    "room_id": "45",
    "sender_id": 88,
    "sender_name": "Viewer Name",
    "sender_avatar": "https://chinchins.live/uploads/avatars/user_88.jpg",
    "message": "Hello Host! Great live stream!",
    "created_at": "2026-09-18T10:45:00Z"
  }
}
```

---

### ৪.৬ ৪/৯ সিট মাল্টি-গেস্ট কো-হোস্টিং ও পিকে ব্যাটল

* **সিট রিকোয়েস্ট:** `POST /api/v1/live/{stream_id}/seat-request`
* **সিট একসেপ্ট/রিজেক্ট:** `POST /api/v1/live/accept-request`
* **গেস্ট কিক/রিমুভ:** `POST /api/v1/live/kick-guest`
* **অডিও মিউট কন্ট্রোল:** `POST /api/v1/live/mute-toggle`

---

### ৪.৮ ভয়েস পার্টি রুম ও ৮/১৬ সিট মাল্টি-গেস্ট গ্রিড (StreamKar & Bigo Voice Party Rooms)

* **ভয়েস পার্টি রুম আর্কিটেকচার:**
  * ৮, ৯, ১২ এবং ১৬ সিটের সার্কুলার অডিও গ্রিড।
  * সিটে থাকা প্রতিটি ইউজারের চারদিকে গোল্ডেন উইংস, লাক্সারি বেস ফ্রেম ও স্পিকিং সাউন্ড ওয়েভ লাইভ অ্যানিমেশন।
  * সিটে থাকা অবস্থায় রিয়েল-টাইম মিউট/আনমিউট ও ডায়মন্ড কাউন্টার।
  * রুমে থাকা যে কাউকে ডিরেক্ট গিফট সেন্ড এবং ফুল-স্ক্রিন লাক্সারি অ্যানিমেশন প্লে।

#### ১. পার্টি রুম লিস্ট ও অ্যাক্টিভ রুমস
* **Endpoint:** `GET /api/v1/party-rooms` (অথবা `/api/party/list`)
* **Headers:** `Authorization: Bearer {token}`

#### ২. পার্টি রুমে সিট গ্রহণ (Take Seat / Mic Up)
* **Endpoint:** `POST /api/v1/party-rooms/{id}/take-seat`
* **Headers:** `Authorization: Bearer {token}`, `Content-Type: application/json`

**Request Body:**
```json
{
  "seat_index": 3
}
```

**Success Response (200 OK):**
```json
{
  "status": true,
  "message": "Seat taken successfully",
  "data": {
    "room_id": 105,
    "seat_index": 3,
    "user": {
      "id": 88,
      "name": "Hayat Star",
      "avatar_url": "https://chinchins.live/uploads/avatars/user_88.jpg",
      "profile_base_frame": "https://chinchins.live/uploads/bases/profile_base_royal_gold.svg",
      "level": "Lv4",
      "is_muted": false
    },
    "role": "speaker",
    "rtc_token": "006c13c72df342d4a1386da678ba4c95f13IAD..."
  }
}
```

#### ৩. পার্টি রুমে মাইক মিউট / আনমিউট
* **Endpoint:** `POST /api/v1/party-rooms/{id}/toggle-mic`
* **Headers:** `Authorization: Bearer {token}`

---

### ৪.৯ ভিআইপি প্রিভিলেজ অ্যাভাটার বেস ও উইংস ফ্রেম আর্কিটেকচার (TOP 1 Winged Crown, SVIP 9)

* **ফিচারস:**
  * **TOP 1 Golden Winged Crown Base:** গোল্ডেন উইংস ও ক্রাউন ফ্রেম প্রোফাইল ও সিট অ্যাভাটারের চারদিকে অটোম্যাটিক রেন্ডার হয়।
  * **SVIP 9 Badges & Wings:** ভিআইপি সাবস্ক্রাইবারদের জন্য এক্সক্লুসিভ গোল্ডেন লায়ন, ফায়ার টাইগার এবং সুপারকার এক্সক্লুসিভ বেস।
  * **অটো প্রোফাইল বাইন্ডিং:** ইউজার যে বেস সিলেক্ট করবে, লাইভ স্ট্রিম, ভয়েস পার্টি রুম, এবং চ্যাট বক্সে তার প্রোফাইল ছবির ওপর সেই ফ্রেমটি স্বয়ংক্রিয়ভাবে ভেসে উঠবে।

#### ১. ইউজার প্রোফাইল বেস ফ্রেম লিস্ট
* **Endpoint:** `GET /api/v1/profile-bases` (অথবা `/api/v1/user/bag`)
* **Headers:** `Authorization: Bearer {token}`

**Success Response (200 OK):**
```json
{
  "status": true,
  "data": [
    {
      "id": 1,
      "name": "TOP 1 Golden Winged Crown",
      "level": 9,
      "base_frame_image": "https://chinchins.live/uploads/bases/profile_base_royal_gold.svg",
      "glow_color": "rgba(245, 158, 11, 0.6)",
      "privilege_text": "TOP 1 Emperor Winged Base Frame"
    },
    {
      "id": 2,
      "name": "Celestial Diamond Wings",
      "level": 8,
      "base_frame_image": "https://chinchins.live/uploads/bases/profile_base_diamond_wings.svg",
      "glow_color": "rgba(6, 182, 212, 0.6)",
      "privilege_text": "Celestial Dragon Wings Frame"
    }
  ]
}
```

---

## ৫. ডেটাবেজ স্কিমা ও মাইগ্রেশনস

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

## ৬. Flutter ক্লায়েন্ট ফুল ইমপ্লিমেন্টেশন গাইড

### ৬.১ TikTok স্টাইল ভার্টিক্যাল সুইপ ও চ্যানেল ক্লিনআপ
```dart
class LiveFeedPlayerScreen extends StatefulWidget {
  final List<LiveStreamModel> initialStreams;
  const LiveFeedPlayerScreen({Key? key, required this.initialStreams}) : super(key: key);

  @override
  State<LiveFeedPlayerScreen> createState() => _LiveFeedPlayerScreenState();
}

class _LiveFeedPlayerScreenState extends State<LiveFeedPlayerScreen> {
  late PageController _pageController;
  int _currentIndex = 0;

  @override
  void initState() {
    super.initState();
    _pageController = PageController(initialPage: _currentIndex);
  }

  void _onPageChanged(int index) {
    // ১. আগের রুমের RTC চ্যানেল ও সকেট ডিসপোজ
    _leavePreviousChannel(widget.initialStreams[_currentIndex].streamId);
    
    // ২. নতুন রুম ইনিশিয়ালাইজ ও জয়েন
    setState(() => _currentIndex = index);
    _joinNewChannel(widget.initialStreams[index].streamId);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      body: PageView.builder(
        scrollDirection: Axis.vertical,
        controller: _pageController,
        onPageChanged: _onPageChanged,
        itemCount: widget.initialStreams.length,
        itemBuilder: (context, index) {
          return LiveRoomView(stream: widget.initialStreams[index]);
        },
      ),
    );
  }
}
```

### ৬.২ রিয়েল-টাইম ইভেন্ট লিসেনার (Laravel Echo)
```dart
void listenToLiveRoom(String roomId) {
  // ১. লাইভ চ্যাট মেসেজ লিসেনার (ডামি ছাড়া আসল মেসেজ)
  echo.channel('presence-stream.$roomId')
      .listen('.message.sent', (data) {
        chatOverlayKey.currentState?.addMessage(data);
      })
      // ২. লাভ রিয়েক্ট ফ্লোটিং হার্ট
      .listen('.live.like', (data) {
        liveScreenKey.currentState?.showFloatingHeart(data);
        liveScreenKey.currentState?.updateLikesCount(data['likes_count']);
      })
      // ৩. রিয়েল-টাইম ভিউয়ার কাউন্টার
      .listen('.viewer.updated', (data) {
        liveScreenKey.currentState?.updateViewerCount(data['viewer_count']);
      })
      // ৪. ফুল-স্ক্রিন লাক্সারি SVGA গিফট
      .listen('.LiveGiftSent', (data) {
        giftAnimationKey.currentState?.playLuxuryGift(data['gift']);
      });

  // ৫. গ্লোবাল লবি লিসেনার (ফিড অটো-আপডেট)
  echo.channel('stream-lobby')
      .listen('.StreamStatusChanged', (data) {
        liveLobbyKey.currentState?.updateLiveList(data);
      });
}
```

---
*Generated and verified for Chinchins Live Production Engine (TikTok & Bigo Live Standard).*
