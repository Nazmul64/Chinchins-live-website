# 🔴 Chinchins Live — Complete RESTful API & Real-Time WebSocket/Agora Engine Documentation

> **Base URL:** `https://chinchins.live/api`  
> **WebSocket Engine:** Laravel Reverb (`wss://chinchins.live:443/app/chinchins_reverb_key`)  
> **RTC Calling & Streaming Engine:** Agora Cloud RTC (Primary Dynamic Token Generator) & VPS WebRTC Fallback  
> **Headers Required for all API requests:**
> ```http
> Accept: application/json
> Content-Type: application/json
> Authorization: Bearer <SANCTUM_TOKEN>
> ```
> *(Note: For legacy or background requests, `X-User-Id` header or `user_id` parameter is supported as a fallback).*

---

## 📋 সূচিপত্র (Table of Contents)

1. [Existing Technology Stack & Engine Architecture](#1-existing-technology-stack--engine-architecture)
2. [1-to-1 Video Call System](#2-1-to-1-video-call-system)
3. [Real-Time Text Chat During Video Call](#3-real-time-text-chat-during-video-call)
4. [Video Call Chat Database & Schema](#4-video-call-chat-database--schema)
5. [Profile View & Real-Time Online Status](#5-profile-view--real-time-online-status)
6. [Profile Action Buttons & Call Initiation](#6-profile-action-buttons--call-initiation)
7. [Online Presence System (Laravel Reverb)](#7-online-presence-system-laravel-reverb)
8. [Live Video Streaming System (TikTok/BIGO Style)](#8-live-video-streaming-system-tiktokbigo-style)
9. [Live Streaming Feed & Active Streamers List](#9-live-streaming-feed--active-streamers-list)
10. [Audience Join Live Room](#10-audience-join-live-room)
11. [Live In-Room Real-Time Chat](#11-live-in-room-real-time-chat)
12. [Live Virtual Gift System](#12-live-virtual-gift-system)
13. [Real-Time Gift Animation (SVGA / Lottie)](#13-real-time-gift-animation-svga--lottie)
14. [Host Earnings & Diamond Balance](#14-host-earnings--diamond-balance)
15. [Wallet & Coin System (Atomic Deductions)](#15-wallet--coin-system-atomic-deductions)
16. [Live Host & Viewer Guest Invitations](#16-live-host--viewer-guest-invitations)
17. [Multi-Guest Live Grid (Host + 4 Guests)](#17-multi-guest-live-grid-host--4-guests)
18. [Guest Request & Notification System](#18-guest-request--notification-system)
19. [Host Controls & Moderation](#19-host-controls--moderation)
20. [Voice Party Room System (Party Room #123)](#20-voice-party-room-system-party-room-123)
21. [Voice Room Seats Layout (8-10 Seats Grid)](#21-voice-room-seats-layout-8-10-seats-grid)
22. [Voice Room Speaker Request & Seat Assignment](#22-voice-room-speaker-request--seat-assignment)
23. [Voice Room Chat & Messaging](#23-voice-room-chat--messaging)
24. [Voice Room Gifts & Earnings](#24-voice-room-gifts--earnings)
25. [Follow / Unfollow System](#25-follow--unfollow-system)
26. [Real-Time Notification System (11 Types)](#26-real-time-notification-system-11-types)
27. [Call Lifecycle & State Machine](#27-call-lifecycle--state-machine)
28. [Network Handling & Non-Freezing UI](#28-network-handling--non-freezing-ui)
29. [Background / Floating Mini-Window (PiP) Video Call](#29-background--floating-mini-window-pip-video-call)
30. [Call Termination & Resource Cleanup](#30-call-termination--resource-cleanup)
31. [Call History Records](#31-call-history-records)
32. [Live Stream Broadcast History](#32-live-stream-broadcast-history)
33. [Real-Time Viewer Count Sync](#33-real-time-viewer-count-sync)
34. [Live Dynamic Ranking & Popularity Metrics](#34-live-dynamic-ranking--popularity-metrics)
35. [Real-Time Likes & Floating Reactions](#35-real-time-likes--floating-reactions)
36. [Share Live Room](#36-share-live-room)
37. [Report & Block System](#37-report--block-system)
38. [Host & Admin Moderation](#38-host--admin-moderation)
39. [Backend-Generated Dynamic Agora Tokens](#39-backend-generated-dynamic-agora-tokens)
40. [Laravel Reverb Private & Presence Channels Security](#40-laravel-reverb-private--presence-channels-security)
41. [Server-Side Gift & Wallet Security Validation](#41-server-side-gift--wallet-security-validation)
42. [Atomic Database Transactions](#42-atomic-database-transactions)
43. [Real-Time Event Architecture & Event Class Catalog](#43-real-time-event-architecture--event-class-catalog)
44. [Agora Channel Naming Standards](#44-agora-channel-naming-standards)
45. [Agora RTC Roles Architecture](#45-agora-rtc-roles-architecture)
46. [Flutter Performance & Anti-Freeze Architecture](#46-flutter-performance--anti-freeze-architecture)
47. [Flutter App Lifecycle Handling](#47-flutter-app-lifecycle-handling)
48. [Device Permissions (Camera, Mic, Notifications)](#48-device-permissions-camera-mic-notifications)
49. [Beauty Filters & Camera Controls](#49-beauty-filters--camera-controls)
50. [Unified Messaging System](#50-unified-messaging-system)
51. [Message History Persistence](#51-message-history-persistence)
52. [Call Status Message Synchronization](#52-call-status-message-synchronization)
53. [Live Room Data Schema](#53-live-room-data-schema)
54. [Party Room Data Schema](#54-party-room-data-schema)
55. [Complete RESTful API Endpoints Catalog](#55-complete-restful-api-endpoints-catalog)
56. [Redis Queue & Concurrency Optimization](#56-redis-queue--concurrency-optimization)
57. [VPS Server Configuration & Supervisor Daemon](#57-vps-server-configuration--supervisor-daemon)
58. [HTTPS & WSS SSL Encryption](#58-https--wss-ssl-encryption)
59. [Standardized Error Handling & Error Codes](#59-standardized-error-handling--error-codes)
60. [Reconnection Strategy for WebSocket & Agora](#60-reconnection-strategy-for-websocket--agora)
61. [Duplicate Event & Transaction Prevention](#61-duplicate-event--transaction-prevention)
62. [Race Condition Protection & Database Locking](#62-race-condition-protection--database-locking)
63. [Admin Panel Dynamic Configurations](#63-admin-panel-dynamic-configurations)
64. [Post-Live Analytics & Summary](#64-post-live-analytics--summary)
65. [End-to-End User Flow Charts](#65-end-to-end-user-flow-charts)
66. [Architectural Preservation Guidelines](#66-architectural-preservation-guidelines)
67. [Production QA & Handover Checklist](#67-production-qa--handover-checklist)

---

## 1. Existing Technology Stack & Engine Architecture

* **Backend:** Laravel 11.x, PHP 8.2+, MySQL 8.0, Redis, Laravel Reverb (WebSocket), Laravel Sanctum.
* **Frontend:** Flutter (Android & iOS).
* **Media Layer (Audio/Video Streams):** Agora Cloud RTC SDK (Dynamic Token Authentication) / Hostinger VPS WebRTC fallback.
* **Signaling & Application Event Layer:** Laravel Reverb (`wss://chinchins.live:443/app/chinchins_reverb_key`) for chats, incoming call popups, live counters, gifts, and room state.

```
┌─────────────────────────────────────────────────────────────┐
│                    Flutter Client (App)                     │
└──────────────┬───────────────────────────────┬──────────────┘
               │                               │
    Audio/Video Media Streams          Application Events & Chat
               │                               │
               ▼                               ▼
  ┌─────────────────────────┐     ┌─────────────────────────┐
  │     Agora Cloud RTC     │     │  Laravel Reverb (WSS)   │
  │   (Real-time Audio/Vid) │     │ (Events/Signaling/Chat) │
  └─────────────────────────┘     └────────────┬────────────┘
                                               │
                                               ▼
                                  ┌─────────────────────────┐
                                  │     Laravel Backend     │
                                  │   (Auth/Wallet/DB/API)  │
                                  └─────────────────────────┘
```

---

## 2. 1-to-1 Video Call System

### 🔹 2.1 Initiate Video Call
* **Method:** `POST`
* **Endpoints:** `/api/call/initiate`, `/api/v1/call/initiate`, `/api/calls`
* **Request Body:**
  ```json
  {
    "receiver_id": 2,
    "call_type": "video"
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
      "channel_name": "call_1_2_1726718400",
      "call_type": "video",
      "active_engine": "agora",
      "driver": "agora",
      "is_agora": true,
      "agora_app_id": "c13c72df342d4a1386da678ba4c95f13",
      "agora_token": "007eJxTYDiw6Xf2xZtVj396tZ...==",
      "caller": { "id": 1, "name": "Nazmul", "avatar_url": "https://chinchins.live/storage/avatars/1.jpg" },
      "receiver": { "id": 2, "name": "Sara", "avatar_url": "https://chinchins.live/storage/avatars/2.jpg" },
      "reverb_channel": "call.105"
    }
  }
  ```

### 🔹 2.2 Accept Call
* **Method:** `POST`
* **Endpoints:** `/api/call/accept`, `/api/calls/{id}/accept`
* **Request Body:** `{"call_id": 105, "call_session_id": "105"}`

### 🔹 2.3 Reject Call
* **Method:** `POST`
* **Endpoints:** `/api/call/reject`, `/api/calls/{id}/reject`
* **Request Body:** `{"call_id": 105, "reason": "busy"}`

### 🔹 2.4 End Call
* **Method:** `POST`
* **Endpoints:** `/api/call/end`, `/api/calls/{id}/end`
* **Request Body:** `{"call_id": 105, "duration_seconds": 185}`

---

## 3. Real-Time Text Chat During Video Call

Video call চলাকালীন caller এবং receiver উভয়ই সরাসরি text message পাঠাতে ও গ্রহণ করতে পারবে। এই মেসেজ কোনো অস্থায়ী মেসেজ নয়—এটি সরাসরি তাদের প্রধান ইনবক্স `conversations` এবং `messages` ডাটাবেসে স্থায়ীভাবে সেভ থাকবে।

### 🔹 3.1 Send Message During Call
* **Method:** `POST`
* **Endpoints:** `/api/call/message/send`, `/api/v1/call/message/send`, `/api/call/chat/send`
* **Request Body:**
  ```json
  {
    "call_id": 105,
    "call_session_id": "105",
    "receiver_id": 2,
    "message": "How are you?",
    "type": "text"
  }
  ```
* **Response (200 OK):**
  ```json
  {
    "status": true,
    "message": "Message sent successfully",
    "data": {
      "id": 8421,
      "call_id": "105",
      "sender_id": 1,
      "receiver_id": 2,
      "message": "How are you?",
      "type": "text",
      "sent_during_call": true,
      "is_read": false,
      "created_at": "2026-09-19T10:45:00Z"
    }
  }
  ```

### 🔹 3.2 Get Call Messages History
* **Method:** `GET`
* **Endpoints:** `/api/call/{callId}/messages`, `/api/call/chat/messages`

---

## 4. Video Call Chat Database & Schema

মেসেজ ডাটাবেস কাঠামো:
```sql
CREATE TABLE `messages` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `conversation_id` BIGINT UNSIGNED NULL,
  `call_id` VARCHAR(100) NULL,
  `call_session_id` VARCHAR(100) NULL,
  `sent_during_call` TINYINT(1) DEFAULT 0,
  `sender_id` BIGINT UNSIGNED NOT NULL,
  `receiver_id` BIGINT UNSIGNED NOT NULL,
  `message` TEXT NOT NULL,
  `type` VARCHAR(50) DEFAULT 'text',
  `media_url` VARCHAR(255) NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  INDEX (`call_id`),
  INDEX (`sender_id`),
  INDEX (`receiver_id`)
);
```

---

## 5. Profile View & Real-Time Online Status

### 🔹 5.1 Fetch User Profile with Online Status
* **Method:** `GET`
* **Endpoints:** `/api/profile/{id}`
* **Response (200 OK):**
  ```json
  {
    "status": true,
    "data": {
      "id": 2,
      "account_id": "87452190",
      "display_name": "Sara Khan",
      "avatar_url": "https://chinchins.live/storage/avatars/2.jpg",
      "online_status": "online", // "online", "offline", "busy", "in_call", "in_live", "in_party"
      "is_online": true,
      "is_busy": false,
      "video_call_rate": 50,
      "followers_count": 1420,
      "following_count": 180,
      "received_coins": 12500,
      "charm_level": "Lv.5"
    }
  }
  ```

---

## 6. Profile Action Buttons & Call Initiation

ইউজারের প্রোফাইল দেখলে স্বয়ংক্রিয়ভাবে কল যাবে না। প্রোফাইলে নিচের বাটনগুলো থাকবে:
1. **Video Call:** ইউজারের `online_status` `online` থাকলে সচল থাকবে; `in_call`/`busy` থাকলে Busy দেখাবে।
2. **Message:** চ্যাট স্ক্রিনে নিয়ে যাবে।
3. **Follow / Unfollow:** ফলো স্টেটাস টগল করবে।
4. **Send Gift:** সরাসরি গিফট পাঠাবে।

---

## 7. Online Presence System (Laravel Reverb)

### 🔹 7.1 Send User Heartbeat
* **Method:** `POST`
* **Endpoints:** `/api/user/heartbeat`, `/api/presence/heartbeat`, `/api/user/ping`
* **Request Body:**
  ```json
  {
    "status": "online" // "online", "busy", "in_call", "in_live", "in_party"
  }
  ```

---

## 8. Live Video Streaming System (TikTok/BIGO Style)

### 🔹 8.1 Host Start Live Broadcast
* **Method:** `POST`
* **Endpoints:** `/api/live/start`, `/api/v1/live/start`, `/api/stream/start`
* **Request Body (Multipart or JSON):**
  ```json
  {
    "title": "Welcome to my weekend show! 🎵",
    "cover_image": "<File or Image URL>"
  }
  ```
* **Response (200 OK):**
  ```json
  {
    "status": true,
    "message": "Live stream broadcast started successfully!",
    "data": {
      "room_id": "45",
      "live_stream_id": 45,
      "channel_name": "live_1_1726718400_abc8",
      "title": "Welcome to my weekend show! 🎵",
      "cover_image_url": "https://chinchins.live/uploads/live_streaming/cover_1.jpg",
      "status": "active",
      "role": "host",
      "viewer_count": 1,
      "active_engine": "agora",
      "agora_app_id": "c13c72df342d4a1386da678ba4c95f13",
      "agora_token": "007eJxTYDhw54LzT0...",
      "reverb_channel": "presence-stream.45"
    }
  }
  ```

### 🔹 8.2 Host End Live Stream
* **Method:** `POST`
* **Endpoints:** `/api/live/end`, `/api/v1/live/end`, `/api/stream/end`
* **Request Body:** `{"room_id": "45"}`

---

## 9. Live Streaming Feed & Active Streamers List

### 🔹 9.1 Get Active Live Streams List
* **Method:** `GET`
* **Endpoints:** `/api/lives`, `/api/live/active`, `/api/live/list`, `/api/stream/list`
* **Query Parameters:** `page=1&per_page=30&sort=popular`
* **Response (200 OK):**
  ```json
  {
    "status": true,
    "data": [
      {
        "id": 45,
        "room_id": "45",
        "title": "Weekend Live Party 🎵",
        "cover_image_url": "https://chinchins.live/uploads/live_streaming/cover_1.jpg",
        "viewer_count": 1245,
        "likes_count": 5830,
        "total_diamonds_earned": 14500,
        "host": {
          "id": 1,
          "account_id": "10023456",
          "display_name": "Nazmul",
          "avatar_url": "https://chinchins.live/storage/avatars/1.jpg",
          "level": "Lv5"
        }
      }
    ]
  }
  ```

---

## 10. Audience Join Live Room

### 🔹 10.1 Viewer Join Live Stream
* **Method:** `POST`
* **Endpoints:** `/api/live/join`, `/api/live/{id}/join`, `/api/v1/live/join`
* **Request Body:** `{"room_id": "45"}`
* **Response (200 OK):**
  ```json
  {
    "status": true,
    "message": "Joined live stream successfully.",
    "data": {
      "room_id": "45",
      "channel_name": "live_1_1726718400_abc8",
      "role": "audience",
      "agora_app_id": "c13c72df342d4a1386da678ba4c95f13",
      "agora_token": "007eJxTYDjw...",
      "viewer_count": 1246,
      "reverb_channel": "presence-stream.45"
    }
  }
  ```

### 🔹 10.2 Viewer Leave Live Stream
* **Method:** `POST`
* **Endpoints:** `/api/live/leave`, `/api/live/{id}/leave`
* **Request Body:** `{"room_id": "45"}`

---

## 11. Live In-Room Real-Time Chat

### 🔹 11.1 Send Live In-Room Message
* **Method:** `POST`
* **Endpoints:** `/api/live/send-message`, `/api/live/message`, `/api/live/comment`
* **Request Body:**
  ```json
  {
    "room_id": "45",
    "message": "Welcome everyone! ❤️",
    "type": "text"
  }
  ```

### 🔹 11.2 Get Live Chat Messages History
* **Method:** `GET`
* **Endpoints:** `/api/live/messages`, `/api/live/{id}/messages`

---

## 12. Live Virtual Gift System

### 🔹 12.1 Send Virtual Gift to Host
* **Method:** `POST`
* **Endpoints:** `/api/live/send-gift`, `/api/live/gift`, `/api/gifts/send`
* **Request Body:**
  ```json
  {
    "room_id": "45",
    "gift_id": 12,
    "quantity": 5
  }
  ```
* **Response (200 OK):**
  ```json
  {
    "status": true,
    "message": "Gift sent successfully!",
    "user_coins": 450,
    "data": {
      "room_id": "45",
      "sender": { "id": 8, "name": "Tanvir", "avatar": "https://..." },
      "gift": {
        "id": 12,
        "name": "Super Rocket 🚀",
        "coin_price": 100,
        "icon_url": "https://chinchins.live/gifts/rocket.png",
        "animation_asset_url": "https://chinchins.live/gifts/rocket.svga",
        "animation_type": "svga"
      },
      "quantity": 5,
      "total_coins": 500
    }
  }
  ```

---

## 13. Real-Time Gift Animation (SVGA / Lottie)

যখনই কোনো দর্শক বা কল পার্টিসিপেন্ট গিফট পাঠাবে, সার্ভার তাৎক্ষণিকভাবে WebSocket ইভেন্ট (`GiftSentEvent` / `LiveGiftSentEvent`) ব্রডকাস্ট করবে:
```json
{
  "event": "LiveGiftSentEvent",
  "channel": "live-stream.45",
  "data": {
    "sender_name": "Tanvir",
    "gift_name": "Super Rocket 🚀",
    "quantity": 5,
    "animation_url": "https://chinchins.live/gifts/rocket.svga",
    "animation_type": "svga",
    "display_type": "fullscreen"
  }
}
```

---

## 14. Host Earnings & Diamond Balance

* দর্শক গিফট পাঠালে হোস্টের অ্যাকাউন্টে নির্ধারিত রেভিনিউ শেয়ার (যেমন ৫০%) ডায়মন্ড/কয়েন আর্নিং হিসেবে যোগ হয়।
* ডাটাবেস টেবিল: `gift_transactions`, `user_gifts`, `wallets`.

---

## 15. Wallet & Coin System (Atomic Deductions)

* ব্যালেন্স যাচাই এবং কয়েন কাটা `DB::transaction` এর মধ্যে অ্যাটমিকভাবে সম্পন্ন হয়।
* অপ্রতুল ব্যালেন্স থাকলে `INSUFFICIENT_BALANCE` ত্রুটি ফেরত দেয়।

### 🔹 15.1 Get Wallet Balance
* **Method:** `GET`
* **Endpoints:** `/api/wallet/balance`, `/api/wallet`

---

## 16. Live Host & Viewer Guest Invitations

* **Viewer Request to Join:** `POST /api/live/join-request` (Body: `{"room_id": "45"}`)
* **Host Accept/Reject Request:** `POST /api/live/accept-request` (Body: `{"request_id": 102, "action": "accept"}`)

---

## 17. Multi-Guest Live Grid (Host + 4 Guests)

* হোস্টের সাথে সর্বাধিক ৪-৫ জন গেস্ট ভিডিও গ্রিডে একসাথে লাইভে সম্প্রচার করতে পারে।
* এক্সেপ্ট হওয়া গেস্টকে ব্রডকাস্টার রোল এবং ডায়নামিক অ্যাগোরা টোকেন প্রদান করা হয়।

---

## 18. Guest Request & Notification System

* দর্শক জয়েন রিকোয়েস্ট পাঠালে হোস্টের স্ক্রিনে রিয়েল-টাইম পপআপ আসবে (`LiveJoinRequested` ইভেন্ট)।

---

## 19. Host Controls & Moderation

* **Kick Guest:** `POST /api/live/kick-guest` (Body: `{"room_id": "45", "guest_user_id": 8}`)
* **Mute/Unmute Guest Mic:** `POST /api/live/mute-toggle` (Body: `{"room_id": "45", "target_user_id": 8, "is_muted": true}`)

---

## 20. Voice Party Room System (Party Room #123)

### 🔹 20.1 Create Voice Party Room
* **Method:** `POST`
* **Endpoints:** `/api/party-rooms/create`, `/api/party-room/create`
* **Request Body:**
  ```json
  {
    "title": "Chinchins Bangla Adda 🎙️",
    "room_type": "voice",
    "max_seats": 8
  }
  ```

---

## 21. Voice Room Seats Layout (8-10 Seats Grid)

হোস্টের শীর্ষ আসনের নিচে ৮-১০টি সিট গ্রিড বিন্যাসে থাকবে।

---

## 22. Voice Room Speaker Request & Seat Assignment

* **Take / Request Seat:** `POST /api/party-rooms/{id}/take-seat` (Body: `{"seat_index": 2}`)
* **Leave Seat:** `POST /api/party-rooms/{id}/leave-seat`
* **Kick Seat (Host only):** `POST /api/party-rooms/{id}/kick-seat` (Body: `{"seat_index": 2}`)

---

## 23. Voice Room Chat & Messaging

* **Send Message:** `POST /api/party-rooms/{id}/send-message`
* **Get Messages:** `GET /api/party-rooms/{id}/messages`

---

## 24. Voice Room Gifts & Earnings

* **Send Gift in Voice Room:** `POST /api/party-rooms/{id}/send-gift`

---

## 25. Follow / Unfollow System

* **Follow User:** `POST /api/user/follow` (Body: `{"target_user_id": 2}`)
* **Unfollow User:** `POST /api/user/unfollow` (Body: `{"target_user_id": 2}`)
* **Followers List:** `GET /api/user/{id}/followers`
* **Following List:** `GET /api/user/{id}/following`

---

## 26. Real-Time Notification System (11 Types)

1. `incoming_call` — Incoming video/audio call request.
2. `missed_call` — Missed call alert.
3. `new_message` — New direct or in-call chat message.
4. `live_started` — Followed host started a live broadcast.
5. `viewer_joined` — User joined host's live room.
6. `guest_requested` — Viewer requested to join as co-host.
7. `guest_accepted` — Host approved co-host request.
8. `gift_received` — Received virtual gift with coins.
9. `user_followed` — Someone started following your profile.
10. `party_room_invitation` — Invited to join voice party room.
11. `voice_seat_responded` — Speaker seat request approved/rejected.

---

## 27. Call Lifecycle & State Machine

```
Calling (ডায়ালিং) ──► Ringing (রিসিভার স্ক্রিনে রিং) ──► Accepted (রিসিভ) ──► Connected (Agora RTC Active) ──► Ended
       │                        │
   (Timeout)               (Rejected)
       │                        │
       ▼                        ▼
    Cancelled              Call Ended
```

---

## 28. Network Handling & Non-Freezing UI

* নেটওয়ার্ক ড্রপ হলে বা Wi-Fi থেকে Mobile Data তে পরিবর্তন হলে Agora এবং Reverb অটোমেটিক ব্যাকগ্রাউন্ডে রিকানেক্ট করবে।
* কোনো অবস্থাতেই Flutter UI থ্রেড ব্লক করা যাবে না।

---

## 29. Background / Floating Mini-Window (PiP) Video Call

* ভিডিও কল চলাকালীন ব্যাক বাটন প্রেস করলে কল বিচ্ছিন্ন হবে না; এটি স্ক্রিনের কোণায় ফ্লুটিং মিনি-উইন্ডোতে মিনিমাইজ হবে।
* **Minimize State Sync:** `POST /api/call/minimize`
* **Restore Full Screen:** `POST /api/call/restore`

---

## 30. Call Termination & Resource Cleanup

* কল শেষ করার জন্য Explicit `End Call` বাটন থাকবে।
* কল শেষ হওয়ার সাথে সাথে Agora Channel Leave হবে, Reverb চ্যানেল ক্লোজ হবে এবং ইউজার স্ট্যাটাস `online` এ ফিরে আসবে।

---

## 31. Call History Records

* **Get Call History:** `GET /api/call/history`, `GET /api/calls`

---

## 32. Live Stream Broadcast History

* হোস্টের বিগত লাইভ সম্প্রচারের সময়কাল, ভিউয়ার সংখ্যা ও মোট অর্জিত ডায়মন্ডের পরিসংখ্যান সেভ থাকবে।

---

## 33. Real-Time Viewer Count Sync

* ভিউয়ার জয়েন বা লিভ করলে `LiveViewerCountUpdated` ইভেন্ট ব্রডকাস্ট হবে এবং ডুপ্লিকেট গণনা প্রতিরোধ করা হবে।

---

## 34. Live Dynamic Ranking & Popularity Metrics

* বর্তমান দর্শক সংখ্যা, প্রাপ্ত গিফট এবং লাইকের ভিত্তিতে লাইভ লিস্ট স্বয়ংক্রিয়ভাবে সাজানো যাবে।

---

## 35. Real-Time Likes & Floating Reactions

* **Send Like:** `POST /api/live/like` (Body: `{"room_id": "45", "count": 10}`)
* `LiveLikeSent` ইভেন্টের মাধ্যমে সকলের স্ক্রিনে লাইক অ্যানিমেশন ভাসবে।

---

## 36. Share Live Room

* **Endpoint:** `GET /live/{id}` (Social share deep link & web preview).

---

## 37. Report & Block System

* **Block User:** `POST /api/chat/block` (Body: `{"target_user_id": 2}`)
* **Report User / Live:** `POST /api/chat/report` (Body: `{"reported_user_id": 2, "reason": "Abusive behavior"}`)

---

## 38. Host & Admin Moderation

* অ্যাডমিন সরাসরি যেকোনো আপত্তিকর লাইভ বন্ধ (`POST /api/admin/live/{id}/terminate`) এবং ব্যবহারকারীকে ব্যান করতে পারবে।

---

## 39. Backend-Generated Dynamic Agora Tokens

* ক্লায়েন্টে কখনোই কোনো Agora App Certificate বা Master Secret রাখা যাবে না।
* ব্যাকএন্ড থেকে HMAC-SHA256 অ্যালগরিদমের মাধ্যমে চ্যানেল নাম, UID, রোল এবং এক্সপায়ারি টাইম দিয়ে টোকেন জেনারেট হবে।

---

## 40. Laravel Reverb Private & Presence Channels Security

* `routes/channels.php` ফাইলে প্রতিটি প্রাইভেট চ্যানেলে Sanctum Bearer Token ভিত্তিক অথেন্টিকেশন বাধ্যতামূলক।

---

## 41. Server-Side Gift & Wallet Security Validation

* ক্লায়েন্ট থেকে পাঠানো কোনো ভ্যালু বিশ্বাস না করে সার্ভার সাইডে অথেন্টিকেশন, ব্যালেন্স, গিফটের দাম ও প্রাপকের আইডি কঠোরভাবে যাচাই করা হয়।

---

## 42. Atomic Database Transactions

* গিফট পাঠানো এবং ব্যালেন্স ট্রাফার `DB::beginTransaction()` এবং `DB::commit()` এর মাধ্যমে পরিচালিত হয়।

---

## 43. Real-Time Event Architecture & Event Class Catalog

* `CallIncoming` — ইনকামিং কল সিগন্যাল
* `CallAccepted` — কল গ্রহণ ইভেন্ট
* `CallRejected` — কল প্রত্যাখ্যান ইভেন্ট
* `CallEnded` — কল সমাপ্তি ইভেন্ট
* `MessageSentEvent` — নতুন মেসেজ
* `GiftSentEvent` / `LiveGiftSentEvent` — গিফট ও অ্যানিমেশন ইভেন্ট
* `LiveStreamEnded` — লাইভ সমাপ্তি
* `LiveViewerCountUpdated` — ভিউয়ার সংখ্যা পরিবর্তন
* `LiveJoinRequested` — গেস্ট রিকোয়েস্ট
* `LiveJoinResponded` — গেস্ট রিকোয়েস্ট ফলাফল
* `LiveLikeSent` — লাইক অ্যানিমেশন
* `StreamStatusChangedEvent` — লাইভ শুরু/শেষ স্টেটাস

---

## 44. Agora Channel Naming Standards

* **1-to-1 Video Call:** `call_{caller_id}_{receiver_id}_{timestamp}`
* **Live Broadcast:** `live_{host_id}_{timestamp}_{random}`
* **Voice Party Room:** `party_{room_id}_{timestamp}`

---

## 45. Agora RTC Roles Architecture

* **1-on-1 Call:** উভয় ব্যবহারকারী `Broadcaster / Publisher`।
* **Live Broadcast:** হোস্ট = `Broadcaster`, সাধারণ দর্শক = `Audience / Subscriber`, এক্সেপ্ট হওয়া গেস্ট = `Broadcaster`।
* **Voice Party:** হোস্ট ও স্পিকারগণ = `Broadcaster`, শ্রোতাগণ = `Audience`।

---

## 46. Flutter Performance & Anti-Freeze Architecture

* ভিডিও রেন্ডারিং এবং UI ফ্রেম ড্রপ রোধ করার জন্য `AgoraVideoView` এবং Reverb Listener লাইফসাইকেল সঠিকভাবে ডেসপোজ করতে হবে।

---

## 47. Flutter App Lifecycle Handling

* অ্যাপ Background এ গেলে ক্যামেরা পজ করা হলেও অডিও সচল থাকবে এবং Foreground এ ফিরলে রেন্ডারিং স্বয়ংক্রিয়ভাবে সচল হবে।

---

## 48. Device Permissions (Camera, Mic, Notifications)

* ক্যামেরা, মাইক্রোফোন এবং নোটিফিকেশন পারমিশন ডিনাই হলে ইউজার ফ্রেন্ডলি ডায়ালগ দেখানো হবে।

---

## 49. Beauty Filters & Camera Controls

* **Get Camera Filters:** `GET /api/filters`, `GET /api/camera/filters`

---

## 50. Unified Messaging System

* সকল ওয়ান-অন-ওয়ান চ্যাট, ইন-কল চ্যাট এবং সিস্টেম মেসেজ একটি সমন্বিত এপিআইয়ের মাধ্যমে আদান-প্রদান করা যায়।

---

## 51. Message History Persistence

* ইউজার স্ক্রিন পরিবর্তন বা অ্যাপ বন্ধ করলেও সকল মেসেজ ডাটাবেসে স্থায়ী থাকবে।

---

## 52. Call Status Message Synchronization

* কল শুরু বা শেষ হলে চ্যাট হিস্ট্রিতে সিস্টেম মেসেজ (যেমন: `Video Call — 05:20`) যুক্ত হবে।

---

## 53. Live Room Data Schema

`live_streams` টেবিল: `id`, `host_id`, `channel_name`, `title`, `cover_image`, `status`, `viewer_count`, `likes_count`, `total_diamonds_earned`, `started_at`, `ended_at`.

---

## 54. Party Room Data Schema

`party_rooms` টেবিল: `id`, `host_id`, `title`, `room_type`, `status`, `channel_name`, `max_seats`, `created_at`, `ended_at`.

---

## 55. Complete RESTful API Endpoints Catalog

| Feature | Method | Endpoint | Description |
| :--- | :---: | :--- | :--- |
| **Auth** | `POST` | `/api/login` | Login with Phone/Email & Password |
| **Auth** | `POST` | `/api/register` | Register New Account |
| **Profile** | `GET` | `/api/profile/{id}` | Get Full User Profile & Online Status |
| **Call** | `POST` | `/api/call/initiate` | Initiate 1-on-1 Audio/Video Call |
| **Call** | `POST` | `/api/call/accept` | Accept Incoming Call |
| **Call** | `POST` | `/api/call/reject` | Reject/Decline Incoming Call |
| **Call** | `POST` | `/api/call/end` | Terminate Active Call |
| **In-Call Chat** | `POST` | `/api/call/message/send` | Send Real-time Text Message During Call |
| **In-Call Gift** | `POST` | `/api/call/gift/send` | Send Virtual Gift During Call |
| **Live** | `GET` | `/api/lives/active` | Get List of Live Streamers |
| **Live** | `POST` | `/api/live/start` | Host Start Live Broadcast |
| **Live** | `POST` | `/api/live/join` | Viewer Join Live Stream |
| **Live** | `POST` | `/api/live/leave` | Viewer Leave Live Stream |
| **Live** | `POST` | `/api/live/end` | Host End Live Stream |
| **Live Chat** | `POST` | `/api/live/send-message` | Send Public Live Comment |
| **Live Gift** | `POST` | `/api/live/send-gift` | Send Gift to Live Host |
| **Live Co-Host** | `POST` | `/api/live/join-request` | Viewer Request to Join as Guest |
| **Live Co-Host** | `POST` | `/api/live/accept-request` | Host Accept/Reject Guest Request |
| **Live Co-Host** | `POST` | `/api/live/kick-guest` | Host Remove/Kick Guest |
| **Live Like** | `POST` | `/api/live/like` | Send Hearts/Likes in Live Stream |
| **Party Room** | `POST` | `/api/party-rooms/create` | Create New Voice Party Room |
| **Party Seat** | `POST` | `/api/party-rooms/{id}/take-seat` | Request / Occupy Speaker Seat |
| **Party Chat** | `POST` | `/api/party-rooms/{id}/send-message`| Send Message in Voice Room |
| **Wallet** | `GET` | `/api/wallet/balance` | Get Coin & Earnings Balance |
| **Gifts** | `GET` | `/api/gifts/catalog` | Get All Available Gifts Catalog |
| **Follow** | `POST` | `/api/user/follow` | Follow a User |

---

## 56. Redis Queue & Concurrency Optimization

* উচ্চ ট্রাফিক হ্যান্ডেল করার জন্য নোটিফিকেশন, ব্যাকগ্রাউন্ড লগিং এবং বাল্ক ইভেন্ট ডিসপ্যাচ Redis Queue এর মাধ্যমে প্রক্রিয়াকৃত হয়।

---

## 57. VPS Server Configuration & Supervisor Daemon

* **Nginx:** SSL টার্মিনেশন এবং `/app` রুটে রিভার্স প্রক্সি কনফিগারেশন।
* **Supervisor:** `php artisan reverb:start --host=0.0.0.0 --port=8080` এবং `php artisan queue:work` নিরবচ্ছিন্নভাবে চালু রাখার জন্য কনফিগার করা।

---

## 58. HTTPS & WSS SSL Encryption

* ক্যামেরা/মাইক্রোফোন পারমিশন এবং সিকিউর ব্রডকাস্টিং নিশ্চিত করার জন্য প্রোডাকশনে `https://` এবং `wss://` সক্রিয় রাখা বাধ্যতামূলক।

---

## 59. Standardized Error Handling & Error Codes

| Error Code | HTTP Status | Meaning / Action |
| :--- | :---: | :--- |
| `INSUFFICIENT_BALANCE` | 422 | পর্যাপ্ত কয়েন নেই, রিচার্জ শিট খুলতে হবে |
| `USER_BUSY` | 409 | ইউজার অন্য কলে বা লাইভে ব্যস্ত আছেন |
| `STREAM_ENDED` | 404 | লাইভ স্ট্রিম ইতিমধ্যে হোস্ট দ্বারা সমাপ্ত |
| `UNAUTHENTICATED` | 401 | টোকেন মেয়াদোত্তীর্ণ বা অবৈধ |

---

## 60. Reconnection Strategy for WebSocket & Agora

* কানেকশন ড্রপ হলে ক্লায়েন্ট প্রতি ২ সেকেন্ড পর পর ব্যাক-অফ অ্যালগরিদম অনুযায়ী পুনঃসংযোগ স্থাপন করবে।

---

## 61. Duplicate Event & Transaction Prevention

* ফ্রন্টএন্ড বাটন ডাবল ক্লিক রোধ এবং ব্যাকএন্ড ট্রানজেকশন আইডিমপোটেন্সি যাচাইকরণ।

---

## 62. Race Condition Protection & Database Locking

* সিট বুকিং এবং গিফট লেনদেনে `DB::table(...)->lockForUpdate()` ব্যবহার করা হয়েছে।

---

## 63. Admin Panel Dynamic Configurations

* ম্যাক্সিমাম গেস্ট সংখ্যা, গিফটের তালিকা ও মূল্য, কয়েন প্যাকেজ এবং রেভিনিউ শেয়ার অ্যাডমিন প্যানেল থেকে নিয়ন্ত্রণযোগ্য।

---

## 64. Post-Live Analytics & Summary

* লাইভ সমাপ্ত হলে মোট ভিউয়ার, পিক কনকারেন্ট ভিউয়ার, অর্জিত কয়েন, নতুন ফলোয়ার ও লাইকের সংখ্যা সারাংশ হিসেবে প্রদর্শিত হয়।

---

## 65. End-to-End User Flow Charts

```
1-to-1 Video Call Flow:
Profile ──► Online Status Check ──► Call Button ──► Incoming Screen (Receiver) 
──► Accept ──► Agora Video Call + Reverb In-Call Chat ──► Send Gift (SVGA Animation) 
──► End Call ──► Call History & Duration Saved

Live Streaming Flow:
Start Live ──► Host Active ──► Public Feed ──► Viewer Joins ──► Live Video Stream 
──► In-Room Chat + Likes + Gifts ──► Guest Request ──► Host Accepts ──► Multi-Guest Grid 
──► Host End ──► Live Summary Analytics

Voice Party Flow:
Create Party ──► Host ──► Room Feed ──► Viewers Join ──► Request Mic Seat 
──► Host Approves ──► Multi-Speaker Voice Adda + Chat + Gifts ──► Leave / End Room
```

---

## 66. Architectural Preservation Guidelines

* বিদ্যমান UI, ডিজাইন, ডাটাবেস কাঠামো এবং বিজনেস লজিক অক্ষুণ্ণ রেখে সিস্টেমটি প্রোডাকশন-রেডি হিসেবে পরিচালিত হবে।

---

## 67. Production QA & Handover Checklist

- [x] 1-on-1 Video Call (Calling, Ringing, Connected, Ended).
- [x] In-Call Real-Time Text Messaging (Both directions sync & persistent DB save).
- [x] In-Call Real-Time Gift Sending with SVGA animations and host coin credit.
- [x] Real-time User Online Presence (Online, Busy, In Call, In Live).
- [x] TikTok/BIGO style Live Video Streaming (Host broadcast & Viewer playback).
- [x] Live In-Room Chat and Moderation (Kick, Mute, Report).
- [x] Multi-Guest Live Grid (Host + 4 Co-Hosts).
- [x] Voice Party Room with 8-10 Seat Layout & Speaker management.
- [x] Dynamic Agora Token generation from Laravel backend.
- [x] Laravel Reverb WebSocket secure authentication and event broadcasting.
- [x] Atomic wallet transactions with race-condition prevention.
- [x] Comprehensive RESTful endpoints & fallback aliases verified.
