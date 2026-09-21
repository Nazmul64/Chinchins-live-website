# ⚡ Zero-Loading & High-Concurrency RESTful API Architecture Guide (BIGO / TikTok Standard)

This blueprint documents the production-grade **Zero-Latency & High-Concurrency Architecture** implemented for Chinchins Live. It eliminates database bottlenecks, enforces in-memory caching, utilizes atomic database operations, and dispatches heavy operations to asynchronous background queues.

---

## 📑 Table of Contents
1. [🏛️ Core Architectural Principles (<50ms Response Time)](#1-️-core-architectural-principles-50ms-response-time)
2. [📊 Database Indexing Blueprint](#2--database-indexing-blueprint)
3. [🎁 Gift Sending & Atomic Transaction Engine](#3--gift-sending--atomic-transaction-engine)
4. [⚡ High-Speed Caching (Redis / Cache)](#4--high-speed-caching-redis--cache)
5. [📞 Instant 1-to-1 Calls & Live Streaming RTC Tokens (<30ms)](#5--instant-1-to-1-calls--live-streaming-rtc-tokens-30ms)
6. [🎙️ Voice Party Chatroom Zero-Latency REST APIs](#6-️-voice-party-chatroom-zero-latency-rest-apis)
7. [📡 WebSocket Reverb Broadcast Synchronization](#7--websocket-reverb-broadcast-synchronization)
8. [📱 Mobile Client Best Practices (Flutter / Android / iOS)](#8--mobile-client-best-practices-flutter--android--ios)

---

## 1. 🏛️ Core Architectural Principles (<50ms Response Time)

| Problem | Traditional Pattern | Zero-Latency Architecture |
| :--- | :--- | :--- |
| **Gift Sending** | Heavy DB locks, 6 sequential table queries, synchronous push notifications | **Atomic decrement on `users`, cached single gift lookup, instant WebSocket broadcast, asynchronous `ProcessGiftHistoryJob`** |
| **Store Catalog** | Queries database on every tap | **In-memory cache (`Cache::remember`), 0 database read hits** |
| **User Lists** | N+1 queries executing 150+ SQL queries per list | **Memory-cached ProfileBase, stripped computed queries, `<1ms` serialization** |
| **Push Notifications**| Synchronous Google OAuth2 network requests (3–10s stall) | **Non-blocking background dispatching with 1.0s fast connect timeout** |
| **WebRTC Calls** | Dynamic session lookups | **Pre-calculated LiveKit JWT generation in `<25ms`** |

---

## 2. 📊 Database Indexing Blueprint

The following composite and single-column indexes are active on high-throughput database tables:

```php
// Party Room Seats
Schema::table('party_room_seats', function (Blueprint $table) {
    $table->index(['party_room_id', 'seat_index'], 'prs_room_seat_idx');
    $table->index('user_id', 'prs_user_idx');
    $table->index('status', 'prs_status_idx');
});

// Party Room Messages
Schema::table('party_room_messages', function (Blueprint $table) {
    $table->index(['party_room_id', 'created_at'], 'prm_room_created_idx');
    $table->index('user_id', 'prm_user_idx');
});

// Wallets
Schema::table('wallets', function (Blueprint $table) {
    $table->index('user_id', 'wallets_user_idx');
});

// Gifts
Schema::table('gifts', function (Blueprint $table) {
    $table->index(['is_active', 'category'], 'gifts_active_cat_idx');
});

// Users
Schema::table('users', function (Blueprint $table) {
    $table->index('coins', 'users_coins_idx');
    $table->index('online_status', 'users_online_status_idx');
});
```

---

## 3. 🎁 Gift Sending & Atomic Transaction Engine

### A. Endpoint
- **Endpoint**: `POST /api/gifts/send` or `POST /api/gift/send`
- **Latency**: `< 35ms`

#### Request Payload
```json
{
  "gift_id": 5,
  "receiver_id": 89,
  "room_name": "party_voice_pr982103",
  "gift_count": 1
}
```

#### Response: `200 OK`
```json
{
  "success": true,
  "status": true,
  "message": "গিফট সফলভাবে পাঠানো হয়েছে",
  "remaining_coins": 12400,
  "data": {
    "remaining_coins": 12400,
    "gift_id": 5,
    "gift_name": "Rocket 🚀",
    "total_coins": 500,
    "icon_url": "https://chinchins.live/uploads/gifts/rocket.png",
    "animation_url": "https://chinchins.live/uploads/gifts/rocket.svga"
  }
}
```

### B. Execution Flow (Backend Blueprint)
```
1. Cache::remember("gift_item_{id}") ──► Validates in-memory (< 1ms)
2. Atomic DB::decrement('coins') ────► Deducts balance without table locking
3. Atomic DB::increment('coins') ────► Credits receiver balance
4. broadcast(new LiveGiftSentEvent) ─► Sends animation to room via Reverb
5. dispatch(new ProcessGiftHistory) ─► Writes ledger & push in background worker
```

---

## 4. ⚡ High-Speed Caching (Redis / Cache)

### A. Full Gifts Catalog
- **Endpoint**: `GET /api/gifts/catalog` or `GET /api/gifts`
- **Cache TTL**: 24 Hours (`86,400s`)
- **Latency**: `< 5ms`

### B. Active Payment Gateways
- **Endpoint**: `GET /api/payment/gateways` or `GET /api/payment-gateways`
- **Cache TTL**: 24 Hours
- **Latency**: `< 5ms`

---

## 5. 📞 Instant 1-to-1 Calls & Live Streaming RTC Tokens (<30ms)

### A. 1-to-1 Call Initiation
- **Endpoint**: `POST /api/calls/initiate`
- **Latency**: `< 40ms`
- **Request Body**:
```json
{
  "receiver_id": 89,
  "call_type": "video"
}
```

### B. Live Stream Join
- **Endpoint**: `POST /api/live/join`
- **Request Body**: `{"room_id": 14}`
- **Response**: `200 OK` (Immediate LiveKit JWT token)

---

## 6. 🎙️ Voice Party Chatroom Zero-Latency REST APIs

### A. Room Creation (Seat 1 Default Host)
- **Endpoint**: `POST /api/party-rooms/create`
- **Request Body**:
```json
{
  "room_title": "Fun Hangout 🎙️✨",
  "room_type": "voice",
  "topic_tag": "Singing",
  "max_seats": 10
}
```

### B. Host Responds to Seat Request ("গ্রহণ করুন")
- **Endpoint**: `POST /api/party-rooms/{id}/seat-requests/{requestId}/respond`
- **Request Body**: `{"action": "accept"}`
- **Response**: `200 OK` (Assigns seat, triggers `SeatUpdatedEvent` to display avatar).

### C. Real-Time Speaking Indicator (Green Halo Glow)
- **Endpoint**: `POST /api/party-rooms/{id}/speaking`
- **Request Body**: `{"is_speaking": true}`
- **Reverb Event**: `SeatUpdatedEvent` with `is_speaking: true` / `false`.

---

## 7. 📡 WebSocket Reverb Broadcast Synchronization

| Event | Channel | Description |
| :--- | :--- | :--- |
| `LiveGiftSentEvent` | `party.{roomId}`, `live-stream.{id}` | Instant gift animation and count celebration |
| `PartyRoomMessageSent` | `party.{roomId}` | Instant chat message broadcast |
| `SeatUpdatedEvent` | `party.{roomId}` | Seat occupancy, avatar update, and speaking green glow indicator |
| `SeatRequestEvent` | `party-room.{roomId}` | Seat request queue notifications |

---

## 8. 📱 Mobile Client Best Practices (Flutter / Android / iOS)

1. **Optimistic Gifting UI**:
   - Immediately play local animation and deduct local coin counter upon tapping the gift button.
   - Revert only if the API returns an error (`INSUFFICIENT_BALANCE`).
2. **Persistent WebSocket Listeners**:
   - Keep a single persistent Echo / Reverb connection throughout the app session.
3. **Local SVGA / Lottie Caching**:
   - Cache downloaded gift SVGA animations locally on device disk.

---
*Zero-Loading RESTful API Architecture — Chinchins Live Production System*
