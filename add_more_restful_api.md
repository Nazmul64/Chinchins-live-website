# 📄 Enterprise Architecture & Comprehensive RESTful API Specification (A–Z)
**Target Roles:** Full-Stack Engineers (Laravel Backend & Flutter Frontend)  
**System Scope:** Live Streaming (Audio/Video), 1-on-1 Video Calling, Unified Core Messaging, Reverb Real-Time Pipeline, FinTech-Grade Coin & Gift Engine, Admin-Controlled Visibility  
**Architecture:** Laravel 11.x RESTful Backend & WebSocket Server (Reverb/Pusher) + Flutter Mobile Client (Android & iOS)  
**Version:** 4.0.0 Enterprise Edition  
**Updated:** September 2026  
**Document Name:** `add_more_restful_api.md`  

---

## 📑 Table of Contents
1. [System Topology & Communication Flow](#1-system-topology--communication-flow)
2. [Database Schema & Migrations Reference](#2-database-schema--migrations-reference)
3. [Unified Core Messaging Engine (In-Call & Direct)](#3-unified-core-messaging-engine-in-call--direct)
4. [FinTech-Grade Concurrency-Safe Coin & Gift Engine](#4-fintech-grade-concurrency-safe-coin--gift-engine)
5. [Admin-Controlled Online/Offline User Visibility](#5-admin-controlled-onlineoffline-user-visibility)
6. [Multi-User Live Streaming, Broadcasting & Co-Hosting](#6-multi-user-live-streaming-broadcasting--co-hosting)
7. [Complete RESTful API Reference & Payloads](#7-complete-restful-api-reference--payloads)
   - [A. User Discovery & Profile Visibility](#a-user-discovery--profile-visibility)
   - [B. Unified Messaging & In-Call Chat](#b-unified-messaging--in-call-chat)
   - [C. Live Streaming & Multi-Guest Broadcasting](#c-live-streaming--multi-guest-broadcasting)
   - [D. FinTech Virtual Gift & Wallet Transactions](#d-fintech-virtual-gift--wallet-transactions)
   - [E. 1-on-1 Video & Audio Calling](#e-1-on-1-video--audio-calling)
8. [Real-Time WebSocket Architecture & Event Specifications](#8-real-time-websocket-architecture--event-specifications)
9. [Flutter Frontend Architecture & State Isolation (Zero-Freeze)](#9-flutter-frontend-architecture--state-isolation-zero-freeze)
   - [A. Idempotent Message Repository & Reverb Sync (`message_repository.dart`)](#a-idempotent-message-repository--reverb-sync-message_repositorydart)
   - [B. Zero-Freeze In-Call Chat Overlay Component (`in_call_chat_overlay.dart`)](#b-zero-freeze-in-call-chat-overlay-component-in_call_chat_overlaydart)
   - [C. Hardware-Accelerated Supercar / Gift Overlay Canvas (`live_room_screen.dart`)](#c-hardware-accelerated-supercar--gift-overlay-canvas-live_room_screendart)
10. [End-to-End Test Matrix & Quality Verification](#10-end-to-end-test-matrix--quality-verification)
11. [Admin Panel Dashboard, Sidebar & Management Architecture](#11-admin-panel-dashboard-sidebar--management-architecture)

---

## 1. System Topology & Communication Flow

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                                 FLUTTER CLIENT                                         │
│                                                                                        │
│  ┌───────────────────────┐  ┌────────────────────────┐  ┌───────────────────────────┐  │
│  │ Unified Messaging UI  │  │ Video Call + Chat Sheet │  │ Live Room (Audio / Video) │  │
│  └───────────┬───────────┘  └───────────┬────────────┘  └─────────────┬─────────────┘  │
└──────────────┼──────────────────────────┼─────────────────────────────┼────────────────┘
               │                          │                             │
    HTTP / REST (Bearer Token)            │                  WebSocket (Presence/Private)
               │                          │                             │
               ▼                          ▼                             ▼
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                              LARAVEL BACKEND ECOSYSTEM                                 │
│                                                                                        │
│  ┌──────────────────────────────────────────────────────────────────────────────────┐  │
│  │ REST Routing & Middleware (Sanctum Auth, Idempotency Guard, Input Validation)   │  │
│  └──────────────────────────────────────┬───────────────────────────────────────────┘  │
│                                         │                                              │
│                               DB Transaction Block                                     │
│                     ┌───────────────────┴───────────────────┐                          │
│                     ▼                                       ▼                          │
│      ┌─────────────────────────────┐         ┌─────────────────────────────┐           │
│      │   Pessimistic Row Lock      │         │ Unified Conversation &      │           │
│      │   `lockForUpdate()` Balance │         │ Messaging Service Engine    │           │
│      └──────────────┬──────────────┘         └──────────────┬──────────────┘           │
│                     │                                       │                          │
│                     ▼                                       ▼                          │
│           [ MySQL 8.0 InnoDB ]                     Dispatch Job / Event                │
│                                                             │                          │
│                                                             ▼                          │
│                                                  [ Laravel Reverb Server ]             │
│                                                             │ (Broadcasting)           │
└─────────────────────────────────────────────────────────────┼──────────────────────────┘
                                                              │
                                                              ▼
                                            Subscribed Channels (Clients Update UI)
```

---

## 2. Database Schema & Migrations Reference

### A. Core Messaging Extensibility (`messages` & `conversations`)
```sql
-- Conversations Table
CREATE TABLE conversations (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  is_group BOOLEAN DEFAULT FALSE,
  title VARCHAR(255) NULL,
  last_message_id BIGINT UNSIGNED NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);

-- Conversation Participants Pivot Table
CREATE TABLE conversation_participants (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  conversation_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE (conversation_id, user_id),
  FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Messages Table
CREATE TABLE messages (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  client_uuid CHAR(36) NULL UNIQUE,
  conversation_id BIGINT UNSIGNED NOT NULL,
  call_id VARCHAR(191) NULL,
  sent_during_call BOOLEAN DEFAULT FALSE,
  sender_id BIGINT UNSIGNED NOT NULL,
  message TEXT NOT NULL,
  type VARCHAR(30) DEFAULT 'text',
  media_url VARCHAR(500) NULL,
  is_read BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
  FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX (call_id),
  INDEX (sent_during_call),
  INDEX (conversation_id, created_at)
);
```

### B. App Configuration & Visibility (`app_settings`)
```sql
CREATE TABLE app_settings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(191) UNIQUE NOT NULL,
  `value` TEXT NULL,
  `description` VARCHAR(255) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);

-- Initial seed for visibility
INSERT INTO app_settings (`key`, `value`, `description`, created_at, updated_at) 
VALUES ('show_offline_users', 'false', 'Display offline users in discovery lists when true', NOW(), NOW());
```

### C. Live Streaming Engine (`live_rooms`, `live_participants`, `live_messages`)
```sql
-- Live Rooms Table
CREATE TABLE live_rooms (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  host_id BIGINT UNSIGNED NOT NULL,
  channel_name VARCHAR(191) UNIQUE NOT NULL,
  title VARCHAR(255) NOT NULL,
  type ENUM('video', 'audio') DEFAULT 'video',
  status ENUM('active', 'ended') DEFAULT 'active',
  viewer_count INT UNSIGNED DEFAULT 0,
  total_diamonds_earned BIGINT UNSIGNED DEFAULT 0,
  cover_image VARCHAR(500) NULL,
  stream_token TEXT NULL,
  started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  ended_at TIMESTAMP NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  INDEX (status),
  FOREIGN KEY (host_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Live Participants Table
CREATE TABLE live_participants (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  live_room_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  role ENUM('host', 'co_host', 'viewer') DEFAULT 'viewer',
  join_request_status ENUM('none', 'pending', 'accepted', 'rejected') DEFAULT 'none',
  joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  left_at TIMESTAMP NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE (live_room_id, user_id),
  FOREIGN KEY (live_room_id) REFERENCES live_rooms(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Live Messages Table
CREATE TABLE live_messages (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  live_room_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (live_room_id) REFERENCES live_rooms(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### D. FinTech Coin & Gift Engine (`wallets`, `gifts`, `gift_transactions`)
```sql
-- Wallets Table
CREATE TABLE wallets (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED UNIQUE NOT NULL,
  balance BIGINT UNSIGNED DEFAULT 0,
  earnings BIGINT UNSIGNED DEFAULT 0,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Gifts Catalog Table
CREATE TABLE gifts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(191) NOT NULL,
  slug VARCHAR(191) UNIQUE NOT NULL,
  coin_price BIGINT UNSIGNED NOT NULL,
  icon_url VARCHAR(500) NOT NULL,
  animation_asset_url VARCHAR(500) NOT NULL,
  animation_type ENUM('svg', 'lottie') DEFAULT 'svg',
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  INDEX (is_active)
);

-- Gift Transactions Table
CREATE TABLE gift_transactions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  idempotency_key VARCHAR(191) UNIQUE NOT NULL,
  sender_id BIGINT UNSIGNED NOT NULL,
  receiver_id BIGINT UNSIGNED NOT NULL,
  live_room_id BIGINT UNSIGNED NULL,
  gift_id BIGINT UNSIGNED NOT NULL,
  quantity INT UNSIGNED DEFAULT 1,
  total_coins BIGINT UNSIGNED NOT NULL,
  status ENUM('completed', 'failed') DEFAULT 'completed',
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (live_room_id) REFERENCES live_rooms(id) ON DELETE SET NULL,
  FOREIGN KEY (gift_id) REFERENCES gifts(id) ON DELETE CASCADE
);
```

---

## 3. Unified Core Messaging Engine (In-Call & Direct)

When User A and User B chat inside a video call, the request attaches to the unified conversation pipeline:
- Auto-resolves direct 1-to-1 conversation via `MessagingService::getOrCreateDirectConversation()`.
- Supports optimistic updates on Flutter with `client_uuid` to prevent duplicated messages during network reconnects.
- Marks messages with `sent_during_call: true` and `call_id: "..."`.
- Broadcasts `MessageSentEvent` on `private-conversation.{conversation_id}` as `message.sent`.

---

## 4. FinTech-Grade Concurrency-Safe Coin & Gift Engine

### Pessimistic Locking & Idempotency Pipeline
1. **Idempotency Gate:** Checks if `idempotency_key` already completed in `gift_transactions`.
2. **Pessimistic Row Lock:** Executes `Wallet::where('user_id', $sender->id)->lockForUpdate()->firstOrFail()`.
3. **Balance Validation:** Throws `ValidationException` (HTTP 422) if balance is insufficient.
4. **Atomic Balances Update:** Decrements sender wallet & user balance; credits receiver wallet (50/50 split).
5. **Live Room Diamonds:** Increments `total_diamonds_earned` on active `live_rooms`.
6. **Real-Time Broadcast:** Fires `LiveGiftSentEvent` on `presence-live-room.{roomId}` and `live-stream.{roomId}` as `gift.received`.

---

## 5. Admin-Controlled Online/Offline User Visibility

- Admin toggle in `app_settings` (`show_offline_users = "true"` / `"false"`).
- When `false`, `/api/v1/users/discovery` and `/api/v1/users/active` strictly filter `where('is_online', true)`.
- When `true`, returns all users ordered by `is_online DESC, last_seen_at DESC`.

---

## 6. Multi-User Live Streaming, Broadcasting & Co-Hosting

- **Start Broadcast:** Creates `live_rooms` record, sets host `current_status = 'in_live'`, provides RTC Broadcaster token.
- **Join Broadcast:** Viewers receive Audience RTC token, join `presence-live-room.{roomId}` and `live-stream.{roomId}`.
- **Co-Hosting Grid:** Viewers call `POST /api/live/join-request` (fires `JoinRequestEvent` to host). Host accepts via `POST /api/live/accept-request` (fires `JoinStatusEvent` and generates Broadcaster RTC token for guest). Host can kick guest anytime (`POST /api/live/kick-guest`).

---

## 7. Complete RESTful API Reference & Payloads

### A. User Discovery & Profile Visibility

#### 1. Discover Users
- **URL:** `GET /api/v1/users/discovery` or `GET /api/v1/users/active`
- **Headers:** `Accept: application/json`
- **Response (200 OK):**
```json
{
  "status": "success",
  "show_offline_users": false,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 2,
        "name": "Nusrat Jahan",
        "account_id": "90218492",
        "avatar": "https://chinchins.live/uploads/avatars/host2.jpg",
        "is_online": true,
        "online_status": "available",
        "current_status": "available",
        "last_active_at": "2026-09-14T08:35:00+06:00"
      }
    ],
    "total": 1
  }
}
```

---

### B. Unified Messaging & In-Call Chat

#### 1. Get or Create Direct Conversation
- **URL:** `POST /api/v1/conversations/direct`
- **Headers:** `Authorization: Bearer {token}`, `Content-Type: application/json`
- **Request Body:**
```json
{
  "recipient_id": 2
}
```
- **Response (200 OK):**
```json
{
  "status": "success",
  "data": {
    "id": 10,
    "is_group": false,
    "last_message_id": 45,
    "participants": [
      {"id": 1, "name": "John"},
      {"id": 2, "name": "Nusrat Jahan"}
    ]
  }
}
```

#### 2. Send Message (Direct Chat or In-Call)
- **URL:** `POST /api/v1/messages/send`
- **Headers:** `Authorization: Bearer {token}`, `Content-Type: application/json`
- **Request Body:**
```json
{
  "conversation_id": 10,
  "client_uuid": "c3e98124-7dfb-4f9e-b5c9-20f51272bc14",
  "message": "Hey! Can you hear me clearly in the call? 🎧",
  "call_id": "call_1_2_1726300000"
}
```
- **Response (200 OK):**
```json
{
  "status": "success",
  "message": "Message dispatched successfully.",
  "data": {
    "id": 46,
    "client_uuid": "c3e98124-7dfb-4f9e-b5c9-20f51272bc14",
    "conversation_id": 10,
    "sender_id": 1,
    "message": "Hey! Can you hear me clearly in the call? 🎧",
    "call_id": "call_1_2_1726300000",
    "sent_during_call": true,
    "created_at": "2026-09-14T08:40:00+06:00"
  }
}
```

#### 3. Get Conversation Message History
- **URL:** `GET /api/v1/conversations/{id}/messages`
- **Headers:** `Authorization: Bearer {token}`
- **Response (200 OK):**
```json
{
  "status": "success",
  "data": {
    "data": [
      {
        "id": 46,
        "client_uuid": "c3e98124-7dfb-4f9e-b5c9-20f51272bc14",
        "sender_id": 1,
        "message": "Hey! Can you hear me clearly in the call? 🎧",
        "sent_during_call": true,
        "call_id": "call_1_2_1726300000",
        "created_at": "2026-09-14T08:40:00+06:00"
      }
    ]
  }
}
```

---

### C. Live Streaming & Multi-Guest Broadcasting

#### 1. Host Start Live Room
- **URL:** `POST /api/v1/live/start` or `POST /api/live/start`
- **Headers:** `Authorization: Bearer {token}`, `Content-Type: application/json`
- **Request Body:**
```json
{
  "title": "Evening Acoustic Live 🎸",
  "cover_image_url": "https://chinchins.live/uploads/live_streaming/cover_1.jpg"
}
```
- **Response (200 OK):**
```json
{
  "status": true,
  "success": true,
  "message": "Live stream broadcast started successfully!",
  "data": {
    "live_stream_id": 1,
    "channel_name": "live_2_1726300000_abcd",
    "title": "Evening Acoustic Live 🎸",
    "status": "live",
    "role": "host",
    "session": {
      "agora": {
        "app_id": "YOUR_AGORA_APP_ID",
        "token": "007eJxTYGDA9q30...YOUR_RTC_TOKEN",
        "uid": 2,
        "role": 1
      }
    }
  }
}
```

#### 2. Get Active Live Rooms List
- **URL:** `GET /api/v1/live/active-streams` or `GET /api/lives/active`
- **Headers:** `Accept: application/json`
- **Response (200 OK):** Returns active live rooms with host profile, viewer counts, and guest list.

#### 3. Join / Leave Live Room
- **Join:** `POST /api/v1/live/join` with `{"live_stream_id": 1}` -> Returns viewer session & incremented viewer count.
- **Leave:** `POST /api/v1/live/leave` with `{"live_stream_id": 1}`.

#### 4. Co-Hosting Grid (Request, Accept, Kick)
- **Request Join:** `POST /api/live/join-request` (`{"live_stream_id": 1}`) -> Broadcasts `JoinRequestEvent` (`join.requested`) to host.
- **Host Respond:** `POST /api/live/accept-request` (`{"request_id": 12, "action": "accept"}`) -> Broadcasts `JoinStatusEvent` (`join.status`) and provides Publisher RTC token to guest.
- **Host Kick:** `POST /api/live/kick-guest` (`{"live_stream_id": 1, "guest_user_id": 105}`) -> Broadcasts `LiveGuestKicked` to guest.

---

### D. FinTech Virtual Gift & Wallet Transactions

#### 1. Get Approved Gifts Catalog
- **URL:** `GET /api/v1/gifts`
- **Response (200 OK):**
```json
{
  "status": true,
  "data": [
    {
      "id": 1,
      "name": "Rose",
      "slug": "rose",
      "coin_price": 10,
      "icon_url": "https://chinchins.live/uploads/gifts/rose.png",
      "animation_asset_url": "https://chinchins.live/uploads/gifts/rose.svg",
      "animation_type": "svg"
    },
    {
      "id": 4,
      "name": "Luxury Sports Car",
      "slug": "sports_car",
      "coin_price": 500,
      "icon_url": "https://chinchins.live/uploads/gifts/car.png",
      "animation_asset_url": "https://chinchins.live/uploads/gifts/car.svg",
      "animation_type": "svg"
    }
  ]
}
```

#### 2. Send Gift (FinTech Concurrency-Safe)
- **URL:** `POST /api/v1/live/send-gift` or `POST /api/live/gift`
- **Headers:** `Authorization: Bearer {token}`, `Content-Type: application/json`
- **Request Body:**
```json
{
  "idempotency_key": "idemp_user1_gift4_1726300000",
  "stream_id": 1,
  "gift_id": 4,
  "quantity": 1
}
```
- **Response (200 OK):**
```json
{
  "status": "success",
  "message": "Gift successfully sent!",
  "user_coins": 1250,
  "data": {
    "transaction_id": 85,
    "stream_id": 1,
    "sender": {
      "id": 1,
      "name": "John",
      "avatar": "https://chinchins.live/uploads/avatars/user1.jpg"
    },
    "gift": {
      "id": 4,
      "name": "Luxury Sports Car",
      "slug": "sports_car",
      "coin_price": 500,
      "icon_url": "https://chinchins.live/uploads/gifts/car.png",
      "animation_asset_url": "https://chinchins.live/uploads/gifts/car.svg",
      "animation_type": "svg"
    },
    "quantity": 1,
    "total_coins": 500,
    "timestamp": 1726301400
  }
}
```

---

### E. 1-on-1 Video & Audio Calling

#### 1. Initiate 1-on-1 Call
- **URL:** `POST /api/call/initiate`
- **Headers:** `Authorization: Bearer {token}`, `Content-Type: application/json`
- **Request Body:** `{"receiver_id": 2, "call_type": "video"}`
- **Response (Available Host - 200 OK):** Starts ringing with Agora/WebRTC credentials.
- **Response (Offline Host - 400 Bad Request):** `{"code": "USER_OFFLINE", "message": "Host is currently offline."}`
- **Response (Busy Host - 400 Bad Request):** `{"code": "USER_BUSY", "message": "Host is currently busy in another call or live broadcast."}`

---

## 8. Real-Time WebSocket Architecture & Event Specifications

| Event Class | Broadcast Channel | Broadcast Event Name (`broadcastAs`) | Payload Outline |
|:---|:---|:---|:---|
| **`MessageSentEvent`** | `private-conversation.{id}` | `message.sent` | `{id, client_uuid, conversation_id, sender_id, message, call_id, sent_during_call, created_at}` |
| **`LiveGiftSentEvent`** | `presence-live-room.{id}`, `live-stream.{id}` | `gift.received` | `{transaction_id, sender_id, sender_name, sender_avatar, gift_id, gift_name, animation_asset_url, animation_type, quantity, total_coins}` |
| **`LiveMessageSentEvent`** | `presence-live-room.{id}`, `live-stream.{id}` | `live.message` / `chat.message` | `{id, user_id, user_name, user_avatar, message, created_at}` |
| **`JoinRequestEvent`** | `private-live-host.{hostId}` | `join.requested` | `{request_id, user_id, user_name, user_avatar}` |
| **`JoinStatusEvent`** | `private-user.{userId}` | `join.status` | `{room_id, status: 'accepted' \| 'rejected', guest_session}` |
| **`LiveGuestKicked`** | `presence-live-room.{id}`, `user.{guestId}` | `LiveGuestKicked` | `{live_stream_id, guest_user_id, reason: 'host_removed'}` |
| **`LiveStreamEnded`** | `presence-live-room.{id}`, `live-stream.{id}` | `LiveStreamEnded` | `{live_stream_id, duration_seconds, total_diamonds_earned, peak_viewers}` |

---

## 9. Flutter Frontend Architecture & State Isolation (Zero-Freeze)

```
                  ┌───────────────────────────────┐
                  │    ActiveCallScreen (Host)    │
                  │   KeepAlive RTC Video Surface │
                  └───────────────┬───────────────┘
                                  │
                   DraggableScrollableSheet / Overlay
                                  │
                  ┌───────────────▼───────────────┐
                  │      InCallChatWidget         │
                  │ (Listens to MessageRepository)│
                  └───────────────┬───────────────┘
                                  │
                Updates local ListView via DiffKey
                 without triggering Root Rebuild
```

### A. Idempotent Message Repository & Reverb Sync (`message_repository.dart`)
```dart
import 'dart:async';
import 'package:flutter/foundation.dart';
import 'package:uuid/uuid.dart';

class MessageModel {
  final int? id;
  final String clientUuid;
  final int conversationId;
  final int senderId;
  final String message;
  final String? callId;
  final bool sentDuringCall;
  final DateTime createdAt;
  bool isPending;

  MessageModel({
    this.id,
    required this.clientUuid,
    required this.conversationId,
    required this.senderId,
    required this.message,
    this.callId,
    this.sentDuringCall = false,
    required this.createdAt,
    this.isPending = false,
  });

  factory MessageModel.fromJson(Map<String, dynamic> json) {
    return MessageModel(
      id: json['id'],
      clientUuid: json['client_uuid'] ?? '',
      conversationId: json['conversation_id'],
      senderId: json['sender_id'],
      message: json['message'],
      callId: json['call_id'],
      sentDuringCall: json['sent_during_call'] ?? false,
      createdAt: DateTime.parse(json['created_at']),
      isPending: false,
    );
  }
}

class MessageRepository extends ChangeNotifier {
  final Map<int, List<MessageModel>> _conversationBuffers = {};
  final Set<String> _processedUuids = {};

  List<MessageModel> getMessages(int conversationId) => _conversationBuffers[conversationId] ?? [];

  /// Optimistic Message Dispatch (Works during video call and normal chat)
  Future<void> sendDirectMessage({
    required int conversationId,
    required int currentUserId,
    required String text,
    String? activeCallId,
    required Future<void> Function(Map<String, dynamic> payload) apiTransport,
  }) async {
    final clientUuid = const Uuid().v4();
    final localMsg = MessageModel(
      clientUuid: clientUuid,
      conversationId: conversationId,
      senderId: currentUserId,
      message: text,
      callId: activeCallId,
      sentDuringCall: activeCallId != null,
      createdAt: DateTime.now(),
      isPending: true,
    );

    // 1. Optimistic Local State Append
    _appendOrDeduplicate(conversationId, localMsg);
    notifyListeners();

    // 2. Network Sync
    try {
      await apiTransport({
        'client_uuid': clientUuid,
        'conversation_id': conversationId,
        'message': text,
        'call_id': activeCallId,
      });
      localMsg.isPending = false;
      notifyListeners();
    } catch (e) {
      localMsg.isPending = false;
      notifyListeners();
      rethrow;
    }
  }

  /// Ingests messages arriving via Laravel Reverb
  void handleIncomingSocketMessage(Map<String, dynamic> json) {
    final incoming = MessageModel.fromJson(json);
    if (incoming.clientUuid.isNotEmpty && _processedUuids.contains(incoming.clientUuid)) {
      // Reconcile pending local message
      final buffer = _conversationBuffers[incoming.conversationId];
      final index = buffer?.indexWhere((m) => m.clientUuid == incoming.clientUuid) ?? -1;
      if (index != -1) {
        buffer![index] = incoming;
        notifyListeners();
      }
      return;
    }
    _appendOrDeduplicate(incoming.conversationId, incoming);
    notifyListeners();
  }

  void _appendOrDeduplicate(int conversationId, MessageModel message) {
    _conversationBuffers.putIfAbsent(conversationId, () => []);
    if (message.clientUuid.isNotEmpty) {
      _processedUuids.add(message.clientUuid);
    }
    _conversationBuffers[conversationId]!.add(message);
  }
}
```

---

### B. Zero-Freeze In-Call Chat Overlay Component (`in_call_chat_overlay.dart`)
```dart
import 'package:flutter/material.dart';

class InCallChatOverlay extends StatefulWidget {
  final int conversationId;
  final int currentUserId;
  final String activeCallId;
  final dynamic repository; // MessageRepository
  final Future<void> Function(Map<String, dynamic>) onSend;

  const InCallChatOverlay({
    Key? key,
    required this.conversationId,
    required this.currentUserId,
    required this.activeCallId,
    required this.repository,
    required this.onSend,
  }) : super(key: key);

  @override
  State<InCallChatOverlay> createState() => _InCallChatOverlayState();
}

class _InCallChatOverlayState extends State<InCallChatOverlay> {
  final TextEditingController _inputController = TextEditingController();
  final ScrollController _scrollController = ScrollController();

  void _sendMessage() {
    final text = _inputController.text.trim();
    if (text.isEmpty) return;

    _inputController.clear();
    widget.repository.sendDirectMessage(
      conversationId: widget.conversationId,
      currentUserId: widget.currentUserId,
      text: text,
      activeCallId: widget.activeCallId,
      apiTransport: widget.onSend,
    );

    Future.delayed(const Duration(milliseconds: 100), () {
      if (_scrollController.hasClients) {
        _scrollController.animateTo(
          _scrollController.position.maxScrollExtent,
          duration: const Duration(milliseconds: 250),
          curve: Curves.easeOut,
        );
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: widget.repository,
      builder: (context, _) {
        final messages = widget.repository.getMessages(widget.conversationId);
        return Container(
          decoration: BoxDecoration(
            color: Colors.black.withOpacity(0.72),
            borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
          ),
          child: Column(
            children: [
              Container(
                width: 40,
                height: 4,
                margin: const EdgeInsets.symmetric(vertical: 8),
                decoration: BoxDecoration(
                  color: Colors.white38,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              Expanded(
                child: ListView.builder(
                  controller: _scrollController,
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                  itemCount: messages.length,
                  itemBuilder: (context, index) {
                    final msg = messages[index];
                    final isMe = msg.senderId == widget.currentUserId;
                    return Align(
                      alignment: isMe ? Alignment.centerRight : Alignment.centerLeft,
                      child: Container(
                        margin: const EdgeInsets.symmetric(vertical: 2),
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                        decoration: BoxDecoration(
                          color: isMe ? Colors.blueAccent : Colors.grey[800],
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Text(
                          msg.message,
                          style: const TextStyle(color: Colors.white, fontSize: 14),
                        ),
                      ),
                    );
                  },
                ),
              ),
              Padding(
                padding: EdgeInsets.only(
                  bottom: MediaQuery.of(context).viewInsets.bottom + 8,
                  left: 8,
                  right: 8,
                  top: 4,
                ),
                child: Row(
                  children: [
                    Expanded(
                      child: TextField(
                        controller: _inputController,
                        style: const TextStyle(color: Colors.white),
                        decoration: InputDecoration(
                          hintText: 'Type a message...',
                          hintStyle: const TextStyle(color: Colors.white54),
                          filled: true,
                          fillColor: Colors.white10,
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(24),
                            borderSide: BorderSide.none,
                          ),
                          contentPadding: const EdgeInsets.symmetric(horizontal: 16),
                        ),
                      ),
                    ),
                    IconButton(
                      icon: const Icon(Icons.send, color: Colors.blueAccent),
                      onPressed: _sendMessage,
                    ),
                  ],
                ),
              ),
            ],
          ),
        );
      },
    );
  }
}
```

---

### C. Hardware-Accelerated Supercar / Gift Overlay Canvas (`live_room_screen.dart`)
```dart
import 'package:flutter/material.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:lottie/lottie.dart';

class LiveRoomScreen extends StatefulWidget {
  final int roomId;
  final bool isHost;

  const LiveRoomScreen({Key? key, required this.roomId, required this.isHost}) : super(key: key);

  @override
  State<LiveRoomScreen> createState() => _LiveRoomScreenState();
}

class _LiveRoomScreenState extends State<LiveRoomScreen> with SingleTickerProviderStateMixin {
  late AnimationController _giftAnimController;
  late Animation<Offset> _giftDriveOffset;
  Map<String, dynamic>? _activeGiftPayload;

  @override
  void initState() {
    super.initState();
    _giftAnimController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 3800),
    );

    // Horizontal Supercar Drive-By Curve
    _giftDriveOffset = Tween<Offset>(
      begin: const Offset(-1.5, 0.0),
      end: const Offset(1.5, 0.0),
    ).animate(CurvedAnimation(
      parent: _giftAnimController,
      curve: Curves.easeInOutCubic,
    ));
  }

  void triggerGiftAnimation(Map<String, dynamic> giftData) {
    if (!mounted) return;
    setState(() {
      _activeGiftPayload = giftData;
    });

    _giftAnimController.forward(from: 0.0).then((_) {
      if (mounted) {
        setState(() {
          _activeGiftPayload = null;
        });
      }
    });
  }

  @override
  void dispose() {
    _giftAnimController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      body: Stack(
        fit: StackFit.expand,
        children: [
          // LAYER 1: Core RTC Stream Surface (Video / Audio Indicator)
          const Center(
            child: Text(
              'RTC Stream Running',
              style: TextStyle(color: Colors.white30),
            ),
          ),

          // LAYER 2: Live Room HUD (Host details, Viewer count, Chat Log)
          Positioned(
            left: 12,
            bottom: 80,
            width: MediaQuery.of(context).size.width * 0.75,
            height: 220,
            child: const ColoredBox(color: Colors.transparent),
          ),

          // LAYER 3: Isolated Fullscreen Gift Render Layer
          if (_activeGiftPayload != null)
            Positioned.fill(
              child: IgnorePointer(
                child: Center(
                  child: SlideTransition(
                    position: _giftDriveOffset,
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        if ((_activeGiftPayload!['animation_type'] ?? 'svg') == 'lottie')
                          Lottie.network(
                            _activeGiftPayload!['animation_asset_url'],
                            width: 320,
                            height: 320,
                          )
                        else
                          SvgPicture.network(
                            _activeGiftPayload!['animation_asset_url'],
                            width: 320,
                            placeholderBuilder: (context) => const SizedBox.shrink(),
                          ),
                        const SizedBox(height: 12),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
                          decoration: BoxDecoration(
                            color: Colors.black87,
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(color: Colors.amber, width: 1.5),
                          ),
                          child: Text(
                            "${_activeGiftPayload!['sender_name'] ?? 'User'} sent ${_activeGiftPayload!['gift_name'] ?? 'a Gift'}! ✨",
                            style: const TextStyle(
                              color: Colors.white,
                              fontWeight: FontWeight.bold,
                              fontSize: 16,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
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

## 10. End-to-End Test Matrix & Quality Verification

| Area | Action / Trigger | Expected Result | Pass Criteria |
|:---|:---|:---|:---|
| **Messaging** | Send message inside active Video Call | Stored under existing `conversation_id`. Flagged with `sent_during_call: true`. | Message appears immediately in call chat & standard messaging thread without screen reload. |
| **Messaging** | Reconnect network during call chat | Pending messages sync via `client_uuid`. | No duplicate rows created in DB or displayed in UI. |
| **Messaging** | End video call | Call interface closes; regular messaging thread contains full call history. | History is preserved verbatim; unread badges update accurately. |
| **Live Stream** | Viewer clicks "Request to Join" | `join.requested` sent via WebSocket to `private-live-host.{id}`. | Host receives interactive accept/reject prompt in real time. |
| **Live Stream** | Host taps "Accept" on join request | WebRTC renegotiation triggers. Participant role becomes `co_host`. | Split-screen/dual-feed renders smoothly without stream interruption. |
| **Gifts** | User with 50 coins buys 100-coin gift | Backend rejects with HTTP 422 (Insufficient balance). | No coin deduction; no transaction logged; no event broadcast. |
| **Gifts** | Rapid tap "Send Gift" (5 times) | Unique `idempotency_key` applied per transaction. | Deducts coins exactly once; avoids duplicate deductions. |
| **Visibility** | Admin sets `show_offline_users = false` | `/api/v1/users/discovery` filters strictly by `is_online: true`. | Offline users disappear from discovery lists without session drops. |

---

## 11. Admin Panel Dashboard, Sidebar & Management Architecture

```
┌────────────────────────────────────────────────────────────────────────┐
│                      ADMIN PANEL SIDEBAR NAVIGATION                     │
│                                                                        │
│   ├── 🏠 Dashboard                                                     │
│   ├── 👥 Users & Balance                                               │
│   ├── 🎧 Live Chat Users & 24/7 Support                                │
│   ├── 💳 Payment Methods                                               │
│   ├── 🏪 Resellers Management (Deposits, Withdrawals, Chat)            │
│   ├── 💎 Coin Packages                                                 │
│   ├── 🎁 Gifts & Rewards System                                        │
│   ├── 🎒 My Bag Items                                                  │
│   ├── 👑 Premium VIP                                                   │
│   ├── 💎 Spend Less, Get More                                          │
│   ├── 🏅 Level Badges & Frames                                         │
│   ├── 💸 Deposit Requests                                              │
│   ├── 💵 Withdrawals                                                   │
│   ├── 🪪 KYC Verification                                              │
│   ├── ⚠️ User Reports Moderation                                       │
│   ├── 📹 Call & Revenue (Sessions & Ringtone Settings)                 │
│   │                                                                    │
│   ├── 📡 Live Streaming (Multi-Guest Broadcasts) [NEW]                 │
│   │     ├── 🔴 All Live Streams (`/admin/live-streams`)                │
│   │     ├── 📜 Gift Transactions Log (`/admin/live-streams/gift-tx`)   │
│   │     └── ⚡ Streaming Driver Engine (`/admin/settings/streaming`)   │
│   │                                                                    │
│   ├── 🎙️ Party Rooms                                                   │
│   ├── 🪙 Coin Ledger                                                   │
│   ├── 🛡️ Staff & Roles (RBAC)                                          │
│   ├── 📜 Activity Audit Logs                                           │
│   ├── 🚪 Login History                                                 │
│   ├── 🐛 App Debug & Logs                                              │
│   └── ⚙️ App Branding & Config (`/admin/settings`)                     │
│         └── 👁️ Show Offline Users in App Toggle [NEW]                  │
└────────────────────────────────────────────────────────────────────────┘
```

### 1. Live Streaming Broadcasts Management (`/admin/live-streams`)
- **Route:** `GET /admin/live-streams` (`Admin\LiveStreamAdminController@index`)
- **Real-Time Active Count Badge:** Displays live count in sidebar with pulse animation (e.g. `2 Live`).
- **Monitoring Table:** Shows Stream ID, Host Profile, Channel Name, Viewer Count, Diamonds Earned, and Started Time.
- **Immediate Termination Action:** `POST /admin/live-streams/{id}/force-close` immediately marks stream `status = 'ended'`, disconnects all participants, resets host status to `available`, and broadcasts `LiveStreamEnded` WebSocket event.

### 2. Virtual Gift Transactions Audit Log (`/admin/live-streams/gift-transactions`)
- **Route:** `GET /admin/live-streams/gift-transactions` (`Admin\LiveStreamAdminController@giftTransactions`)
- **Ledger Overview:** Displays total gifts sent, total coins spent, sender info, receiver (host) info, coins spent vs host earnings (50% split), associated live stream ID, and ISO timestamp.

### 3. App Settings — Offline Users Visibility Toggle (`/admin/settings`)
- **Route:** `POST /admin/settings` with `show_offline_users: "1"` or `"0"`
- **Storage:** Saved in `app_settings` table under `key: show_offline_users`.
- **Cache Invalidation:** Automatically invalidates `app_setting_show_offline_users` cache upon update.
- **Client Impact:** Immediately affects `/api/v1/users/discovery` and `/api/v1/users/active` query outputs.

---
*Chinchins Live Technical Specification — Confidential & Proprietary*

