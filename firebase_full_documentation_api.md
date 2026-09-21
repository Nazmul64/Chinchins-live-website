# 🔥 Firebase Push Notification & 1-on-1 Real-Time Calling/Chat RESTful API Documentation
> **Chinchins Live — High-Performance Cloud Messaging & Real-Time Engagement Engine**

---

## 📑 Table of Contents
1. [System Overview & Architecture](#-system-overview--architecture)
2. [Firebase Console Service Account JSON Setup](#-firebase-console-service-account-json-setup)
3. [Database Schema & Models](#-database-schema--models)
4. [Admin Web Management Panel](#-admin-web-management-panel)
5. [Complete RESTful API Specifications](#-complete-restful-api-specifications)
   - [1. Device FCM Token Registration & Sync](#1-device-fcm-token-registration--sync)
   - [2. Active Firebase Apps List](#2-active-firebase-apps-list)
   - [3. 1-to-1 Incoming Call High-Priority Push Notification](#3-1-to-1-incoming-call-high-priority-push-notification)
   - [4. 1-to-1 Chat Message & Photo Push Notification](#4-1-to-1-chat-message--photo-push-notification)
   - [5. System & Broadcast Push Notification](#5-system--broadcast-push-notification)
   - [6. User Notifications Inbox & Mark Read](#6-user-notifications-inbox--mark-read)
   - [7. Push Notification Dispatch History Log](#7-push-notification-dispatch-history-log)
6. [Flutter Client Integration Guide](#-flutter-client-integration-guide)

---

## 🌟 System Overview & Architecture

The Chinchins Live Push Notification Engine provides enterprise-grade, low-latency mobile push notifications with dual-engine protocol support:
- **Firebase HTTP v1 Protocol (OAuth 2.0)**: Uses service account private keys (`JSON`) to securely generate short-lived Google OAuth2 bearer tokens via `Firebase\JWT\JWT`.
- **Firebase Legacy HTTP Protocol**: Fallback support via FCM Server Key.
- **OneSignal Push Engine**: Optional broadcast and targeted segmentation.

```
┌─────────────────────────────────────────────────────────────┐
│                   Laravel Backend Server                     │
│  ┌────────────────────────┐    ┌──────────────────────────┐ │
│  │ FirebaseApiController  │    │ PushNotificationService  │ │
│  └───────────┬────────────┘    └────────────┬─────────────┘ │
└──────────────┼──────────────────────────────┼───────────────┘
               │                              │
       RESTful │                      OAuth2  │ JWT Bearer
       APIs    ▼                              ▼
┌──────────────────────┐        ┌─────────────────────────────┐
│  Flutter Mobile App  │        │   Google Firebase Gateway   │
│  - Incoming Call UI  │ ◄───── │   (HTTP v1 & Legacy FCM)    │
│  - Chat Push Banner  │  Push  └─────────────────────────────┘
└──────────────────────┘
```

---

## 🔑 Firebase Console Service Account JSON Setup

1. Open [Firebase Console](https://console.firebase.google.com/).
2. Select your Project &rarr; Click **Project Settings** (gear icon) &rarr; Go to **Service Accounts** tab.
3. Click **Generate New Private Key** to download the `service-account.json` file.
4. Go to **Admin Panel &rarr; Firebase Push &rarr; Firebase Apps &rarr; + Add New App**.
5. Enter:
   - **App Name**: e.g., `Chinchins Live`
   - **Package Name**: e.g., `com.chinchins.live`
   - **Firebase JSON File**: Upload the downloaded `.json` key file.
   - **Server Key (Optional)**: Enter legacy FCM server key if available.
6. Click **Save App**.

---

## 🗄️ Database Schema & Models

### 1. `firebase_apps` Table
| Column | Type | Description |
| :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED (PK)` | Primary ID |
| `app_name` | `VARCHAR(150)` | Friendly application name |
| `package_name` | `VARCHAR(200) UNIQUE` | Android / iOS bundle identifier |
| `service_account_json` | `TEXT` | Raw JSON credentials |
| `service_account_path` | `VARCHAR(255)` | Storage path to `.json` file |
| `server_key` | `TEXT` | Legacy FCM Server key |
| `project_id` | `VARCHAR(150)` | Google Cloud / Firebase project ID |
| `client_email` | `VARCHAR(200)` | Service account email |
| `is_active` | `BOOLEAN` | Active status (default: true) |

### 2. `push_notifications` Table
| Column | Type | Description |
| :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED (PK)` | Primary ID |
| `firebase_app_id` | `BIGINT UNSIGNED (FK)` | Associated Firebase App |
| `platform` | `VARCHAR(30)` | `firebase`, `onesignal`, or `both` |
| `title` | `VARCHAR(255)` | Notification title |
| `message` | `TEXT` | Notification message body |
| `image_url` | `TEXT` | Optional banner image URL |
| `action_url` | `VARCHAR(255)` | Click action / deep link |
| `send_to` | `VARCHAR(30)` | `all` or `specific` |
| `target_user_ids` | `JSON` | List of target user IDs |
| `sent_count` | `INT UNSIGNED` | Successfully delivered token count |
| `failed_count` | `INT UNSIGNED` | Failed token count |
| `total_target` | `INT UNSIGNED` | Total targeted devices |
| `status` | `VARCHAR(30)` | `delivered`, `partial`, or `failed` |

### 3. `device_registrations` Table
Tracks active FCM tokens, hardware brands, models, and OS versions per user and Firebase app.

---

## 🖥️ Admin Web Management Panel

The admin panel provides 4 dedicated management pages accessible from the sidebar under **Push & Notifications**:

1. **Firebase Apps** (`/admin/firebase/apps`):
   - Add, edit, test, and activate/deactivate multi-project Firebase apps with JSON file uploads.
2. **Send Push Notification** (`/admin/firebase/send`):
   - Choose Platform (OneSignal, Firebase FCM, or Both), choose Firebase App, input Title, Message, Image URL, Action URL, and select All Users or Specific Users.
   - Live summary stats & recent dispatch log.
3. **Notification History & Reports** (`/admin/firebase/history`):
   - Date range and App filters, success rate visual progress bars, total sent/failed statistics, and detailed JSON inspection modals.
4. **Users with FCM Tokens** (`/admin/firebase/users`):
   - Total FCM users, Active tokens, Android vs iOS device counts, instant search by Name/Email/ID, and one-click direct push modal.

---

## 📡 Complete RESTful API Specifications

All endpoints accept standard JSON headers:
```http
Accept: application/json
Content-Type: application/json
Authorization: Bearer <AUTH_TOKEN>
```

---

### 1. Device FCM Token Registration & Sync

Call this endpoint upon user login, app launch, or whenever `FirebaseMessaging.instance.onTokenRefresh` fires in Flutter.

- **Method**: `POST`
- **Endpoints**:
  - `POST /api/update-fcm-token`
  - `POST /api/fcm/register-token`
  - `POST /api/fcm/update-token`

#### Request Headers:
```http
Authorization: Bearer 1|abcdef123456789...
Content-Type: application/json
Accept: application/json
```

#### Request Body:
```json
{
  "fcm_token": "fXyZ_example_long_device_token_from_firebase_messaging...",
  "device_type": "android",
  "device_id": "98a123f-45b6-78c9",
  "device_brand": "Samsung",
  "device_model": "Galaxy S24 Ultra",
  "os_version": "Android 14",
  "app_version": "1.0.0",
  "package_name": "com.chinchins.live"
}
```

#### Response (200 OK):
```json
{
  "status": true,
  "success": true,
  "message": "FCM Token registered and synced successfully.",
  "data": {
    "device_id": 14,
    "user_id": 2,
    "device_type": "android",
    "firebase_app_id": 1,
    "last_active_at": "2026-09-21T13:30:00+06:00"
  }
}
```

---

### 2. Active Firebase Apps List

- **Method**: `GET`
- **Endpoint**: `GET /api/fcm/apps`

#### Response (200 OK):
```json
{
  "status": true,
  "data": [
    {
      "id": 1,
      "app_name": "Chinchins Live",
      "package_name": "com.chinchins.live",
      "project_id": "chinchins-live-prod",
      "is_active": true,
      "created_at": "2026-09-21T10:00:00.000000Z"
    }
  ]
}
```

---

### 3. 1-to-1 Incoming Call High-Priority Push Notification

Triggered when a male or female user/host initiates a 1-on-1 audio or video call. Rings the receiver's phone with incoming call screen and custom ringtone.

- **Method**: `POST`
- **Endpoints**:
  - `POST /api/fcm/send-call-notification`
  - `POST /api/fcm/send-call`
  - `POST /api/fcm/call`

#### Request Body:
```json
{
  "receiver_id": 25,
  "caller_id": 10,
  "call_type": "video",
  "channel_name": "call_room_10_25_9981"
}
```

#### Payload Delivered to Receiver's Device (`RemoteMessage.data`):
```json
{
  "action": "INCOMING_CALL",
  "type": "incoming_call",
  "call_id": "105",
  "channel_name": "call_room_10_25_9981",
  "call_type": "video",
  "caller_id": "10",
  "caller_account_id": "84729103",
  "caller_name": "Sara Khan",
  "caller_avatar": "https://chinchins.live/uploads/profile/sara.jpg",
  "rate_per_minute": "100",
  "is_free_trial": "0",
  "ringtone_url": "https://chinchins.live/assets/audio/incoming_call.mp3",
  "ring_timeout": "45",
  "timestamp": "1758439800"
}
```

#### Android FCM High-Priority Configuration:
- `channel_id`: `chinchins_call_channel`
- `sound`: `call_ringtone`
- `priority`: `HIGH`
- `ttl`: `45s`

#### Response (200 OK):
```json
{
  "status": true,
  "message": "Incoming call push dispatched to receiver.",
  "call_id": 105,
  "channel": "call_room_10_25_9981",
  "caller": {
    "id": 10,
    "name": "Sara Khan",
    "avatar": "https://chinchins.live/uploads/profile/sara.jpg",
    "account_id": "84729103"
  },
  "receiver": {
    "id": 25,
    "name": "Alex Smith",
    "account_id": "19382049"
  },
  "dispatch": {
    "status": true,
    "sent": 1,
    "failed": 0
  }
}
```

---

### 4. 1-to-1 Chat Message & Photo Push Notification

Triggered when a user, female host, or automated greeting sends a message (e.g. *"Hi baby"*, *"How are you?"*) or a picture.

- **Method**: `POST`
- **Endpoints**:
  - `POST /api/fcm/send-chat-notification`
  - `POST /api/fcm/send-chat`
  - `POST /api/fcm/chat`

#### Request Body (Text Message):
```json
{
  "receiver_id": 25,
  "sender_id": 10,
  "message": "Hi baby, how are you doing today? 💕",
  "message_type": "text"
}
```

#### Request Body (Photo / Picture Message):
```json
{
  "receiver_id": 25,
  "sender_id": 10,
  "image_url": "https://chinchins.live/uploads/chat/photo_1294.jpg",
  "message_type": "image"
}
```

#### Payload Delivered to Recipient's Device:
```json
{
  "notification": {
    "title": "Sara Khan",
    "body": "Hi baby, how are you doing today? 💕",
    "image": "https://chinchins.live/uploads/chat/photo_1294.jpg"
  },
  "data": {
    "action": "CHAT_MESSAGE",
    "type": "chat_message",
    "message_id": "184920",
    "sender_id": "10",
    "sender_name": "Sara Khan",
    "sender_avatar": "https://chinchins.live/uploads/profile/sara.jpg",
    "image_url": "https://chinchins.live/uploads/chat/photo_1294.jpg",
    "message_type": "image",
    "text": "📷 Sent a photo",
    "action_url": "/chat/10",
    "timestamp": "1758439850"
  }
}
```

#### Response (200 OK):
```json
{
  "status": true,
  "message": "Chat message push dispatched to recipient.",
  "sender": {
    "id": 10,
    "name": "Sara Khan",
    "avatar": "https://chinchins.live/uploads/profile/sara.jpg"
  },
  "receiver": {
    "id": 25,
    "name": "Alex Smith"
  },
  "payload": {
    "text": "Hi baby, how are you doing today? 💕",
    "image_url": "https://chinchins.live/uploads/chat/photo_1294.jpg",
    "type": "image"
  },
  "dispatch": {
    "status": true,
    "sent": 1,
    "failed": 0
  }
}
```

---

### 5. System & Broadcast Push Notification

- **Method**: `POST`
- **Endpoint**: `POST /api/fcm/send-broadcast`

#### Request Body:
```json
{
  "platform": "firebase",
  "title": "Special Gems Bonus 💎",
  "message": "Recharge now and get 50% extra bonus coins!",
  "image_url": "https://chinchins.live/uploads/promos/bonus_banner.png",
  "action_url": "/wallet",
  "user_ids": ["all"]
}
```

#### Response (200 OK):
```json
{
  "status": true,
  "sent_count": 1250,
  "failed_count": 8,
  "record_id": 42,
  "message": "Broadcast dispatched successfully! Sent: 1250"
}
```

---

### 6. User Notifications Inbox & Mark Read

#### Fetch In-App Notifications:
- **Method**: `GET`
- **Endpoints**: `GET /api/notifications` or `GET /api/fcm/my-notifications`

```json
{
  "status": true,
  "unread_count": 3,
  "data": [
    {
      "id": 102,
      "user_id": 25,
      "type": "message",
      "title": "Sara Khan",
      "message": "Hi baby, how are you doing today? 💕",
      "is_read": false,
      "created_at": "2026-09-21T13:20:00.000000Z",
      "actor": {
        "id": 10,
        "name": "Sara Khan",
        "display_name": "Sara Khan",
        "avatar_url": "https://chinchins.live/uploads/profile/sara.jpg",
        "account_id": "84729103"
      }
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "total": 1
  }
}
```

#### Mark Notification as Read:
- **Method**: `POST`
- **Endpoints**: `POST /api/notifications/read` or `POST /api/fcm/mark-read`

```json
{
  "notification_id": 102
}
```

---

### 7. Push Notification Dispatch History Log

- **Method**: `GET`
- **Endpoint**: `GET /api/fcm/history`

---

## 📱 Flutter Client Integration Guide

### 1. Token Sync on App Launch / Login
```dart
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'dart:io';

Future<void> syncDeviceTokenWithBackend(String authToken) async {
  try {
    FirebaseMessaging messaging = FirebaseMessaging.instance;

    // Request notification permission (Android 13+ & iOS)
    NotificationSettings settings = await messaging.requestPermission(
      alert: true,
      badge: true,
      sound: true,
    );

    String? token = await messaging.getToken();
    if (token == null) return;

    print("📱 Registered FCM Token: $token");

    final response = await http.post(
      Uri.parse('https://chinchins.live/api/update-fcm-token'),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': 'Bearer $authToken',
      },
      body: jsonEncode({
        'fcm_token': token,
        'device_type': Platform.isAndroid ? 'android' : 'ios',
        'package_name': 'com.chinchins.live',
      }),
    );

    if (response.statusCode == 200) {
      print("✅ FCM Token Synced Successfully");
    }
  } catch (e) {
    print("❌ Error syncing FCM token: $e");
  }
}
```

### 2. Handling Incoming Calls & Chat Messages
```dart
// 1. Top-Level Background Handler
@pragma('vm:entry-point')
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  if (message.data['action'] == 'INCOMING_CALL') {
    // Show Full-Screen Incoming Call Screen with Ringtone
    print("📞 Background Incoming Call from: ${message.data['caller_name']}");
  }
}

// 2. In-App Foreground Listener
void setupForegroundPushListeners(BuildContext context) {
  FirebaseMessaging.onMessage.listen((RemoteMessage message) {
    final data = message.data;

    if (data['action'] == 'INCOMING_CALL') {
      // Launch Call Floating Widget or Navigation to Call Screen
      Navigator.pushNamed(context, '/incoming-call', arguments: data);
    } else if (data['action'] == 'CHAT_MESSAGE') {
      // Show In-App Banner or snackbar
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text("${data['sender_name']}: ${data['text']}"),
          backgroundColor: Colors.pinkAccent,
        ),
      );
    }
  });

  FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
    if (message.data['action_url'] != null) {
      // Deep link to screen
      Navigator.pushNamed(context, message.data['action_url']);
    }
  });
}
```

---

## 🚀 Summary
The Firebase Push Notification subsystem is completely integrated into both the Laravel Web Admin and Mobile REST APIs, fully supporting:
- Multi-App Service Account JSON and Legacy FCM credentials.
- 1-to-1 Incoming Call notifications with high-priority audio channels.
- 1-to-1 Chat & Photo notifications with real-time sender avatars and text.
- Broadcast & Targeted push notifications with comprehensive delivery logs and metrics.
