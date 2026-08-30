# 💘 Chinchins Live — Match Tab, In-App Messaging & Profile View APIs Documentation

This document provides a comprehensive RESTful API reference for Flutter mobile app developers to implement:
1. **Match Tab & Random Video Matching** (Live host count, profile photo grid, "Start Matching" instant connection, and wallet coin balance check).
2. **Profile View Notification & Auto-Callback Trigger** (When a user views a host profile, an automated greeting & callback/call signal is triggered).
3. **In-App Messaging & Chat System** (Conversation inbox with unread badges, text, voice notes, photos, and free message limit enforcement).

---

## 🌐 Base URL & Headers

- **Base URL:** `http://your-domain.com/api` (or `http://localhost:8000/api` during local development)
- **Headers:**
  ```http
  Authorization: Bearer <SANCTUM_TOKEN>
  Accept: application/json
  Content-Type: application/json
  ```
  *(Fallback: `user_id` can also be passed as query param or body param if not using Bearer token).*

---

# 1️⃣ Match Tab & Random Matching APIs

### 📱 Screen Reference: Match Tab (Live Host Pool & Matching)
Shows the total number of people waiting to meet you (e.g., `5383 People waiting to meet you`), a responsive grid of active host photos, and a **"Start Matching"** button.

---

### 🔹 Endpoint 1.1: Get Match Tab Dashboard Data
Retrieve live waiting host count, active host profile pictures, caller's coin balance, and call config.

- **Method:** `GET`
- **Path:** `/api/match` (or `/api/match/status`, `/api/match/hosts`)
- **Query Parameters:**
  - `gender` *(optional, string)*: `female` | `male` | `any` (default: `female`)
  - `limit` *(optional, integer)*: number of hosts in photo grid (default: `20`)

#### 📥 Success Response (`200 OK`):
```json
{
  "status": true,
  "message": "Match tab data loaded successfully.",
  "data": {
    "waiting_count": 5383,
    "actual_online_hosts": 14,
    "heading": "People waiting to meet you",
    "button_text": "Start Matching",
    "hosts": [
      {
        "id": 12,
        "account_id": "8934217890",
        "display_name": "Sohan Khan",
        "avatar_url": "http://your-domain.com/storage/avatars/host1.jpg",
        "cover_photo_url": "http://your-domain.com/storage/covers/host1.jpg",
        "gallery_images": [
          "http://your-domain.com/storage/gallery/photo1.jpg",
          "http://your-domain.com/storage/gallery/photo2.jpg"
        ],
        "gender": "female",
        "age": 22,
        "level": 4,
        "country": "Bangladesh",
        "city": "Dhaka",
        "is_active": true,
        "is_busy": false,
        "is_online": true,
        "video_call_rate": 1800,
        "introduction": "Sweet girl looking for friendly chats.",
        "tags": ["Sweet", "Active", "Live Host"]
      }
    ],
    "caller": {
      "id": 5,
      "account_id": "1049281745",
      "name": "Alex",
      "coins": 2500,
      "is_eligible_for_free_call": false,
      "free_trial_duration_seconds": 0
    },
    "call_config": {
      "is_call_enabled": true,
      "default_video_rate": 100,
      "free_call_duration_seconds": 10
    }
  }
}
```

---

### 🔹 Endpoint 1.2: Start Instant Random Match ("Start Matching" Button)
Selects a random available host and initiates a video call session.
- **If user has sufficient coins (or free trial):** Creates a call session (`status: ringing`) and returns channel credentials.
- **If user has insufficient coins:** Returns `402 Payment Required` with `LOW_BALANCE_DEPOSIT_REQUIRED` and coin packages list for instant deposit prompt.

- **Method:** `POST`
- **Path:** `/api/match/start` (or `/api/call/match`, `/api/match/random`)
- **Request Body (JSON):**
```json
{
  "call_type": "video",
  "gender": "female"
}
```

#### 📥 Success Response (`200 OK` — Sufficient Balance):
```json
{
  "status": true,
  "message": "Match created successfully. Connecting call...",
  "data": {
    "matched_host": {
      "id": 12,
      "account_id": "8934217890",
      "display_name": "Sohan Khan",
      "avatar_url": "http://your-domain.com/storage/avatars/host1.jpg",
      "cover_photo_url": "http://your-domain.com/storage/covers/host1.jpg",
      "country": "Bangladesh",
      "video_call_rate": 1800,
      "level": 4
    },
    "call_session": {
      "call_id": 48,
      "channel_name": "match_5_12_1788114920_kM9x",
      "call_type": "video",
      "status": "ringing",
      "rate_per_minute": 1800,
      "is_free_trial": false,
      "free_duration_seconds": 0
    },
    "caller": {
      "id": 5,
      "coins": 2500,
      "is_eligible_for_free_call": false
    }
  }
}
```

#### ⚠️ Insufficient Balance Response (`402 Payment Required`):
```json
{
  "status": false,
  "code": "LOW_BALANCE_DEPOSIT_REQUIRED",
  "message": "Insufficient coin balance. You need at least 1800 coins for 1 minute of call.",
  "current_coins": 120,
  "required_coins": 1800,
  "is_low_balance": true,
  "redirect_to_deposit": true,
  "matched_host": {
    "id": 12,
    "account_id": "8934217890",
    "display_name": "Sohan Khan",
    "avatar_url": "http://your-domain.com/storage/avatars/host1.jpg",
    "video_call_rate": 1800
  },
  "coin_packages": [
    {
      "id": 1,
      "name": "Standard Pack",
      "coins": 1000,
      "price": 100,
      "bonus_coins": 100
    },
    {
      "id": 2,
      "name": "Popular Pack",
      "coins": 2500,
      "price": 200,
      "bonus_coins": 300
    }
  ]
}
```

---

# 2️⃣ Profile View Notification & Auto-Callback Trigger

### 📱 Screen Reference: User Profile Page
When a user views a host's profile (e.g., Sohan Khan, ID 5, Level 4, 1800 coins/min), the mobile app triggers this endpoint to record the view and trigger automated host interaction / callback.

---

### 🔹 Endpoint 2.1: Record Profile View & Trigger Callback
- **Method:** `POST`
- **Path:** `/api/profile/{id}/view` (or `/api/profile/view`, `/api/user/view-profile`)
- **Request Body (JSON):**
```json
{
  "host_id": 12
}
```

#### 📥 Success Response (`200 OK`):
```json
{
  "status": true,
  "message": "Profile view recorded. Auto-callback notification triggered.",
  "data": {
    "host": {
      "id": 12,
      "account_id": "8934217890",
      "display_name": "Sohan Khan",
      "avatar_url": "http://your-domain.com/storage/avatars/host1.jpg",
      "video_call_rate": 1800,
      "country": "Bangladesh",
      "level": 4,
      "introduction": "Sweet girl looking for friendly chats."
    },
    "callback": {
      "auto_call_triggered": true,
      "viewer_can_receive": true,
      "required_coins": 1800,
      "viewer_coins": 2500
    },
    "auto_message": {
      "id": 102,
      "sender_id": 12,
      "receiver_id": 5,
      "type": "text",
      "message": "Hi Alex! I saw you visited my profile. Call me now?",
      "is_read": false,
      "is_free": true
    }
  }
}
```

---

# 3️⃣ In-App Messaging & Chat APIs (with Free Limits & Voice Notes)

### 📱 Screen Reference: Messages Inbox (Conversations List)
Displays conversations list with:
- Avatar + Online green dot
- Display name (e.g., `Gulabi ❤️`, `SimranGlow_ ⭐`, `DanielleRose 🦋`, `Sumaiya jannat`)
- Last message preview (`[Video Call]`, `[Image]`, `[Voice Note]`, or text message)
- Timestamp (`09:56`, `Yesterday`, etc.)
- Unread badge counter (`1`, `2`, `33`)

---

### 🔹 Endpoint 3.1: Get Conversations / Inbox List
- **Method:** `GET`
- **Path:** `/api/messages` (or `/api/messages/conversations`, `/api/chat/conversations`)

#### 📥 Success Response (`200 OK`):
```json
{
  "status": true,
  "message": "Conversations loaded successfully.",
  "data": {
    "total_unread_badge": 22,
    "free_messages_remaining": 3,
    "user_coins": 2500,
    "conversations": [
      {
        "user_id": 8,
        "account_id": "9087123456",
        "name": "Gulabi ❤️",
        "avatar_url": "http://your-domain.com/storage/avatars/gulabi.jpg",
        "is_online": true,
        "is_busy": false,
        "unread_count": 0,
        "last_message": {
          "text": "[Video Call]",
          "type": "video_call",
          "time": "5m ago",
          "created_at": "2026-08-30T18:20:00Z"
        },
        "video_call_rate": 1800
      },
      {
        "user_id": 9,
        "account_id": "9087123457",
        "name": "SimranGlow_ ⭐",
        "avatar_url": "http://your-domain.com/storage/avatars/simran.jpg",
        "is_online": true,
        "is_busy": false,
        "unread_count": 0,
        "last_message": {
          "text": "[Image]",
          "type": "image",
          "time": "15m ago",
          "created_at": "2026-08-30T18:10:00Z"
        },
        "video_call_rate": 1500
      },
      {
        "user_id": 10,
        "account_id": "9087123458",
        "name": "Sumaiya jannat",
        "avatar_url": "http://your-domain.com/storage/avatars/sumaiya.jpg",
        "is_online": true,
        "is_busy": false,
        "unread_count": 2,
        "last_message": {
          "text": "[Image]",
          "type": "image",
          "time": "25m ago",
          "created_at": "2026-08-30T18:00:00Z"
        },
        "video_call_rate": 1800
      },
      {
        "user_id": 11,
        "account_id": "9087123459",
        "name": "Ameena",
        "avatar_url": "http://your-domain.com/storage/avatars/ameena.jpg",
        "is_online": true,
        "is_busy": false,
        "unread_count": 1,
        "last_message": {
          "text": "আমাকে দেখতে চাও?",
          "type": "text",
          "time": "1d ago",
          "created_at": "2026-08-29T17:00:00Z"
        },
        "video_call_rate": 1800
      }
    ]
  }
}
```

---

### 🔹 Endpoint 3.2: Get Messages with Specific User
- **Method:** `GET`
- **Path:** `/api/messages/{userId}` (or `/api/chat/{userId}`)
- **Query Parameters:**
  - `page` *(optional, integer)*: pagination page
  - `per_page` *(optional, integer)*: default `50`

#### 📥 Success Response (`200 OK`):
```json
{
  "status": true,
  "message": "Messages retrieved successfully.",
  "data": {
    "chat_partner": {
      "id": 11,
      "account_id": "9087123459",
      "name": "Ameena",
      "avatar_url": "http://your-domain.com/storage/avatars/ameena.jpg",
      "is_online": true,
      "is_busy": false,
      "video_call_rate": 1800
    },
    "free_messages_remaining": 3,
    "user_coins": 2500,
    "message_cost_after_free": 5,
    "messages": [
      {
        "id": 201,
        "sender_id": 11,
        "receiver_id": 5,
        "type": "text",
        "message": "আমাকে দেখতে চাও?",
        "media_url": null,
        "duration": 0,
        "is_read": true,
        "created_at": "2026-08-29T17:00:00.000000Z"
      },
      {
        "id": 202,
        "sender_id": 5,
        "receiver_id": 11,
        "type": "text",
        "message": "হ্যাঁ, তুমি কি ফ্রি আছো?",
        "media_url": null,
        "duration": 0,
        "is_read": true,
        "created_at": "2026-08-29T17:02:00.000000Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "total": 2
    }
  }
}
```

---

### 🔹 Endpoint 3.3: Send Message (Text / Voice Audio / Image)
Supports sending text messages, recording voice messages (`.m4a`, `.mp3`, `.wav`), or uploading pictures.
- **Free Limit Rule:** Users receive 5 free messages.
- After 5 messages, sending costs **5 coins/message**.
- If coins are insufficient, returns `402 Payment Required` with `MESSAGE_LIMIT_REACHED` and triggers deposit recharge bottom sheet.

- **Method:** `POST`
- **Path:** `/api/messages/send` (or `/api/chat/send`)
- **Content-Type:** `multipart/form-data` or `application/json`
- **Form-Data / JSON Fields:**
  - `receiver_id` *(required, integer)*: Host/Partner user ID
  - `type` *(optional, string)*: `text` | `voice` | `image` (default: `text`)
  - `message` *(optional, string)*: Text content
  - `voice_file` *(optional, file)*: Audio file (`.mp3`, `.wav`, `.m4a`, `.aac`)
  - `image_file` *(optional, file)*: Image file (`.jpg`, `.png`, `.webp`)
  - `duration` *(optional, integer)*: Audio duration in seconds

#### 📥 Success Response (`201 Created`):
```json
{
  "status": true,
  "message": "Message sent successfully.",
  "data": {
    "chat_message": {
      "id": 203,
      "sender_id": 5,
      "receiver_id": 11,
      "type": "voice",
      "message": "Voice Message",
      "media_url": "http://your-domain.com/storage/chat/voice/sample.m4a",
      "duration": 6,
      "is_read": false,
      "is_free": true,
      "coin_cost": 0,
      "created_at": "2026-08-30T18:25:00.000000Z"
    },
    "sender": {
      "id": 5,
      "name": "Alex",
      "coins": 2500,
      "free_messages_remaining": 2
    }
  }
}
```

#### ⚠️ Free Message Limit Reached & Insufficient Coins (`402 Payment Required`):
```json
{
  "status": false,
  "code": "MESSAGE_LIMIT_REACHED",
  "message": "You have reached the free limit of 5 messages. Please deposit coins to continue chatting.",
  "is_limit_reached": true,
  "redirect_to_deposit": true,
  "current_coins": 2,
  "required_coins": 5,
  "free_messages_used": 5,
  "free_messages_limit": 5,
  "coin_packages": [
    {
      "id": 1,
      "name": "Starter Pack",
      "coins": 1000,
      "price": 100
    }
  ]
}
```

---

### 🔹 Endpoint 3.4: Mark Messages as Read
- **Method:** `POST`
- **Path:** `/api/messages/read` (or `/api/chat/read`)
- **Request Body (JSON):**
```json
{
  "sender_id": 11
}
```

#### 📥 Success Response (`200 OK`):
```json
{
  "status": true,
  "message": "Messages marked as read."
}
```

---

# 🚀 Flutter Dart Integration Guide

### 1. Match Tab Implementation (`match_tab_view.dart`)
```dart
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';

class MatchService {
  final Dio _dio = Dio(BaseOptions(baseUrl: 'http://your-domain.com/api'));

  /// Fetch Match Tab live waiting count and host grid
  Future<Map<String, dynamic>> getMatchTabData(String token) async {
    final response = await _dio.get(
      '/match',
      options: Options(headers: {'Authorization': 'Bearer $token'}),
    );
    return response.data['data'];
  }

  /// Start Matching button action
  Future<void> startMatching(BuildContext context, String token) async {
    try {
      final response = await _dio.post(
        '/match/start',
        data: {'call_type': 'video', 'gender': 'female'},
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );

      final callData = response.data['data'];
      // Navigate to WebRTC Video Call Screen with channel_name and matched_host
      Navigator.pushNamed(context, '/video_call', arguments: callData);
    } on DioException catch (e) {
      if (e.response?.statusCode == 402 && e.response?.data['code'] == 'LOW_BALANCE_DEPOSIT_REQUIRED') {
        // Show Coin Deposit / Recharge BottomSheet Modal
        _showRechargeBottomSheet(context, e.response?.data);
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(e.response?.data['message'] ?? 'Matching failed')),
        );
      }
    }
  }

  void _showRechargeBottomSheet(BuildContext context, dynamic data) {
    showModalBottomSheet(
      context: context,
      builder: (ctx) => Container(
        padding: const EdgeInsets.all(16),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text("Insufficient Coins", style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
            SizedBox(height: 8),
            Text("You need ${data['required_coins']} coins to start this video call."),
            SizedBox(height: 16),
            ElevatedButton(
              onPressed: () => Navigator.pushNamed(context, '/deposit'),
              child: Text("Recharge Coins Now"),
            )
          ],
        ),
      ),
    );
  }
}
```

### 2. Auto-Trigger on Profile View
```dart
Future<void> onProfileViewOpened(String hostId, String token) async {
  try {
    await Dio().post(
      'http://your-domain.com/api/profile/$hostId/view',
      options: Options(headers: {'Authorization': 'Bearer $token'}),
    );
  } catch (e) {
    debugPrint("Profile view log error: $e");
  }
}
```
