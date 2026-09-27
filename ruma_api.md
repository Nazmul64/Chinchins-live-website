# Chinchins Live — Complete RESTful API & Real-Time Specifications (`ruma_api.md`)

This master specification document outlines all RESTful API endpoints, WebSocket broadcasting channels, LiveKit WebRTC media signaling, dynamic scalable pagination, and balance verification logic for the **Chinchins Live** Mobile App (Flutter) and Web Client.

---

## 📑 Table of Contents
1. [Authentication & Session Headers](#1-authentication--session-headers)
2. [Infinite Scalable User Feed APIs (High-Performance Pagination)](#2-infinite-scalable-user-feed-apis-high-performance-pagination)
3. [User Profile & Live Streaming Status Injection](#3-user-profile--live-streaming-status-injection)
4. [1-on-1 Audio & Video Call Lifecycle (LiveKit & Balance Validation)](#4-1-on-1-audio--video-call-lifecycle-livekit--balance-validation)
5. [In-Call Real-Time Billing, Pulse Deductions & Revenue Split](#5-in-call-real-time-billing-pulse-deductions--revenue-split)
6. [Chat, Messenger & Conversation Inbox APIs](#6-chat-messenger--conversation-inbox-apis)
7. [Multi-User Live Streaming & Co-Hosting APIs](#7-multi-user-live-streaming--co-hosting-apis)
8. [Wallet, Coin Recharge & Withdrawal APIs](#8-wallet-coin-recharge--withdrawal-apis)
9. [WebSocket Real-Time Event & Channel Reference (Laravel Reverb)](#9-websocket-real-time-event--channel-reference-laravel-reverb)
10. [Ubuntu VPS Deployment & LiveKit / Reverb Server Management](#10-ubuntu-vps-deployment--livekit--reverb-server-management)

---

## 1. Authentication & Session Headers

All authenticated requests should pass the user's Sanctum Bearer Token or User Identification Headers:

```http
Authorization: Bearer {user_access_token}
Content-Type: application/json
Accept: application/json
```

*Note: For fallback resilience, custom headers `X-User-Id` or `User-Id` are also supported by backend middleware.*

---

## 2. Infinite Scalable User Feed APIs (High-Performance Pagination)

### Endpoints:
- `GET /api/users/feed`
- `GET /api/feed/users`
- `GET /api/all-users-feed`
- `GET /api/feed/global` (Cursor pagination)

### Description:
Returns dynamically paginated users without artificial hardcoded limits. Supports 1 user up to 1,000,000+ registered users smoothly. Automatically injects active live stream data (`is_live`, `live_room`, `live_token`) from the `live_streams` table.

#### Query Parameters:
| Param | Type | Default | Description |
| :--- | :--- | :--- | :--- |
| `page` | integer | `1` | Page number for standard pagination |
| `per_page` | integer | `30` | Items per page (1 to 100) |
| `cursor` | string | `null` | Cursor string for cursor pagination (`/api/feed/global`) |
| `gender` | string | `null` | Filter by `female`, `male`, or `all` |
| `country` | string | `null` | Country code / name (e.g. `BD`, `PK`, `IN`) |

#### Success Response (`200 OK`):
```json
{
  "success": true,
  "status": true,
  "message": "User feed loaded successfully",
  "data": [
    {
      "id": 202,
      "account_id": "84729103",
      "name": "Nusrat Jahan",
      "display_name": "Nusrat Jahan",
      "nickname": "Nusrat",
      "avatar": "https://chinchins.live/uploads/profile/avatar_202.jpg",
      "avatar_url": "https://chinchins.live/uploads/profile/avatar_202.jpg",
      "country_flag": "🇧🇩",
      "country": "Bangladesh",
      "country_code": "BD",
      "current_level": 8,
      "level": "Lv.8",
      "level_number": 8,
      "is_online": true,
      "is_live": true,
      "live_room": "stream_202_1727430000",
      "live_room_id": "stream_202_1727430000",
      "live_token": "eyJhbGciOi...",
      "live_stream_id": 45,
      "video_call_rate": 100,
      "coins": 1450,
      "gender": "female",
      "age": 21,
      "active_live_stream": {
        "id": 45,
        "user_id": 202,
        "host_id": 202,
        "room_name": "stream_202_1727430000",
        "channel_name": "stream_202_1727430000",
        "status": "live",
        "viewer_count": 128,
        "likes_count": 560,
        "livekit_url": "wss://chinchins.live/livekit"
      }
    }
  ],
  "has_more": true,
  "pagination": {
    "current_page": 1,
    "last_page": 34,
    "per_page": 30,
    "total": 1020,
    "has_more": true
  }
}
```

---

## 3. User Profile & Live Streaming Status Injection

### Endpoints:
- `GET /api/user/{id}/profile`
- `GET /api/profile/{id}`
- `GET /api/user/profile` (Current User)
- `GET /api/profile/me` (Current User)

#### Success Response (`200 OK`):
```json
{
  "status": true,
  "success": true,
  "is_live": true,
  "live_room": "stream_202_1727430000",
  "live_room_id": "stream_202_1727430000",
  "live_token": "eyJhbGciOi...",
  "data": {
    "id": 202,
    "name": "Nusrat Jahan",
    "is_live": true,
    "live_room": "stream_202_1727430000",
    "live_room_id": "stream_202_1727430000",
    "live_token": "eyJhbGciOi...",
    "coins": 1450,
    "diamonds": 4200,
    "followers_count": 890,
    "following_count": 42,
    "video_call_rate": 100,
    "video_call_rate_text": "100/min",
    "live_stream": {
      "id": 45,
      "room_id": "45",
      "room_name": "stream_202_1727430000",
      "channel_name": "stream_202_1727430000",
      "title": "Welcome to my sweet stream 💖",
      "viewer_count": 128,
      "likes_count": 560,
      "livekit_url": "wss://chinchins.live/livekit",
      "status": "live"
    }
  }
}
```

---

## 4. 1-on-1 Audio & Video Call Lifecycle (LiveKit & Balance Validation)

### Endpoints:
- `POST /api/call/initiate`
- `POST /api/call/instant`
- `POST /api/call/make-call`

### Validation Rules:
1. **Coin Balance Check**: If the caller's coins are less than the required call rate (`video_call_rate` or 60 for audio) and the user is not a free host / in free trial, the server returns **`402 Payment Required`** with `INSUFFICIENT_BALANCE`.
2. **Synchronous DB Insertion**: Creates a record in the `calls` table (`caller_id`, `receiver_id`, `call_type`, `status: ringing`, `room_id`) and `call_sessions` table.
3. **Live Host Calling Support**: If the host is currently in live broadcast, the call signal is still broadcast to the host's private channel (`private-user.{host_id}`) so the host receives the incoming call notification.

#### Request Body:
```json
{
  "receiver_id": 202,
  "call_type": "video"
}
```

#### Low Balance Error Response (`402 Payment Required`):
```json
{
  "success": false,
  "status": false,
  "code": "INSUFFICIENT_BALANCE",
  "message": "Insufficient coins to make this call",
  "required_coins": 100,
  "current_coins": 25,
  "redirect_to_deposit": true
}
```

#### Success Response (`200 OK`):
```json
{
  "success": true,
  "status": true,
  "message": "Call initiated! Ringing receiver...",
  "call_id": 1055,
  "id": 1055,
  "session_id": 1055,
  "channel_name": "call_video_101_202_1727431200",
  "room_name": "call_video_101_202_1727431200",
  "call_type": "video",
  "token": "eyJhbGciOi...",
  "livekit_token": "eyJhbGciOi...",
  "livekit_url": "wss://chinchins.live/livekit",
  "caller": {
    "id": 101,
    "display_name": "Rahim",
    "avatar_url": "https://chinchins.live/uploads/profile/avatar_101.jpg"
  },
  "receiver": {
    "id": 202,
    "display_name": "Nusrat Jahan",
    "avatar_url": "https://chinchins.live/uploads/profile/avatar_202.jpg"
  }
}
```

---

### Call Answering, Rejection & Hanging Up:

#### Accept Call (`POST /api/call/accept`):
```json
{
  "call_id": 1055
}
```
*Updates `status` to `connected` in `calls` and `call_sessions`, starts billing timer.*

#### Reject Call (`POST /api/call/reject`):
```json
{
  "call_id": 1055
}
```
*Stops ringing, updates status to `rejected`, and emits `CallRejected` on caller channel.*

#### Cancel Call (`POST /api/call/cancel`):
```json
{
  "call_id": 1055
}
```
*Emits `CallCancelled` on receiver channel.*

#### End / Hang Up Call (`POST /api/call/end`):
```json
{
  "call_id": 1055,
  "duration_seconds": 125
}
```
*Marks call as `completed` / `ended`, executes coin transfers (50% host / 50% admin commission), and broadcasts `CallEndedEvent` on both channels.*

---

## 5. In-Call Real-Time Billing, Pulse Deductions & Revenue Split

### Endpoint:
- `POST /api/call/deduct-interval`
- `POST /api/call/pulse`

### Request Body:
```json
{
  "call_id": 1055,
  "elapsed_seconds": 60,
  "interval_seconds": 30
}
```

### Success Response:
```json
{
  "status": true,
  "is_free_trial": false,
  "coins_deducted": 50,
  "host_earned": 25,
  "admin_earned": 25,
  "user_current_balance": 1400,
  "can_continue": true,
  "should_terminate_call": false
}
```

---

## 6. Chat, Messenger & Conversation Inbox APIs

### Endpoints:
- `GET /api/messages/conversations`
- `GET /api/chat/conversations`
- `GET /api/messages`
- `POST /api/messages/send`
- `GET /api/messages/{userId}`

#### Conversation List Response (`200 OK`):
```json
{
  "status": true,
  "success": true,
  "data": {
    "total_unread_badge": 1,
    "free_messages_limit": 5,
    "free_messages_remaining": 4,
    "user_coins": 1450,
    "conversations": [
      {
        "user_id": 202,
        "account_id": "84729103",
        "name": "Nusrat Jahan",
        "display_name": "Nusrat Jahan",
        "avatar_url": "https://chinchins.live/uploads/profile/avatar_202.jpg",
        "is_online": true,
        "is_busy": false,
        "is_live": true,
        "live_room": "stream_202_1727430000",
        "live_token": "eyJhbGciOi...",
        "unread_count": 1,
        "last_message": {
          "text": "Hi baby! আমি ফ্রি আছি 💋",
          "message": "Hi baby! আমি ফ্রি আছি 💋",
          "type": "text",
          "time": "Just now",
          "created_at": "2026-09-27T16:00:00+06:00"
        }
      }
    ]
  }
}
```

---

## 7. Multi-User Live Streaming & Co-Hosting APIs

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `POST` | `/api/v1/stream/start` | Host starts a new broadcast |
| `POST` | `/api/v1/stream/join` | Viewer joins stream & gets LiveKit token |
| `POST` | `/api/v1/stream/leave` | Viewer leaves stream |
| `POST` | `/api/v1/stream/end` | Host ends live stream |
| `POST` | `/api/v1/stream/like` | Send real-time heart tap / like |
| `POST` | `/api/v1/stream/send-gift` | Send animated gift in live room |
| `POST` | `/api/live/request-join` | Viewer requests 4-person co-host video seat |
| `POST` | `/api/live/accept-join` | Host accepts co-host join request |

---

## 8. Wallet, Coin Recharge & Withdrawal APIs

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/wallet/balance` | Get current coin balance & earnings |
| `GET` | `/api/coin-packages` | List purchasable coin packages |
| `GET` | `/api/payment-methods` | List available deposit methods (bKash, Nagad, etc.) |
| `POST` | `/api/deposit/submit` | Submit manual deposit request with TrxID |
| `GET` | `/api/withdraw/info` | Get withdraw rates & minimum threshold |
| `POST` | `/api/withdraw/submit` | Submit host coin-to-cash withdrawal |

---

## 9. WebSocket Real-Time Event & Channel Reference (Laravel Reverb)

Client connects to Laravel Reverb via Pusher Client protocol (`ws://` or `wss://` on port `8080` or via `/app` proxy):

| Event Class | Channel Name | Event Alias | Description |
| :--- | :--- | :--- | :--- |
| `IncomingCallEvent` | `private-user.{user_id}` / `user.{user_id}` | `IncomingCallEvent` / `call.incoming` | Rings device with caller info & LiveKit room token |
| `CallEndedEvent` | `private-user.{user_id}` / `call.{channel_name}` | `CallEndedEvent` / `call.ended` | Terminates active call immediately on both devices |
| `NewMessageEvent` | `private-user.{user_id}` / `chat.{user_id}` | `NewMessageEvent` / `message.sent` | Instant 1-on-1 chat delivery |
| `LiveStreamStarted` | `live.streams` | `LiveStreamStarted` | Global notification when a favorite host goes live |
| `LiveGiftSentEvent` | `live.{channel_name}` | `LiveGiftSentEvent` | Displays real-time gift animations across viewers |

---

## 10. Ubuntu VPS Deployment & LiveKit / Reverb Server Management

On your direct VPS hosting (without Docker):

### 1. LiveKit WebRTC Server:
```bash
# Check service status:
sudo systemctl status livekit-server

# Start & enable auto-boot:
sudo systemctl start livekit-server
sudo systemctl enable livekit-server

# View live logs:
sudo journalctl -u livekit-server -f
```

### 2. Laravel Reverb WebSocket Server:
```bash
# Test run with debug logs:
php artisan reverb:start --debug

# Manage via Supervisor:
sudo supervisorctl status
sudo supervisorctl restart all
```

### 3. Laravel Cache & Database Migration:
```bash
cd /var/www/html/chinchins-live-website
git pull origin main
php artisan migrate --force
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan queue:restart
```

### 4. Check Open Ports:
```bash
# Verify 7880 (LiveKit), 8080 (Reverb), 80/443 (Nginx) are listening:
sudo ss -tulpn | grep -E '7880|8080|80|443'
```

---
**✅ All specifications are synced, tested, and ready for production deployment.**
