# 🎙️ Chinchins Live — Party Room (Voice & Video Multi-Guest) RESTful API Documentation

This document provides complete technical specifications for the **Party Room System (Voice Party Audio Stage & Video Party Grid)** in Chinchins Live.

---

## 🌟 Overview & Architecture

- **Voice Party (🎙️ Audio Stage)**: 8–12 seats audio stage (Seat 1 = Host, Seats 2–10 = Active Speakers/Guests) + unlimited audience.
- **Video Party (📹 Video Grid)**: Multi-guest video grid supporting up to 10 live video streams simultaneously.
- **Guest Search & Invitation**: Search users by Name, 8-digit Account ID, or invite from **Connected / Liked Friends List** (mutual likes).
- **Seat Controls**: Take seat, request seat, leave seat, kick guest from seat, mic mute/unmute, video toggle.
- **In-Room Live Communication**: Real-time group chat, announcements, photo/image sharing (uploaded to `public/uploads/host_image/`).
- **Live Gifting**: Send gifts with instant wallet coin balance check, live animation, and recipient coin balance credit.
- **Monetization & Billing (50/50 Revenue Split)**:
  - Configurable rate per minute (default **100 coins/minute**).
  - Every billed minute: **50% (50 coins)** credited to Host wallet balance, **50% (50 coins)** credited to Platform/Admin revenue.
  - **Auto-Eviction**: If a guest's coin balance falls below the required rate, they are automatically moved from stage back to audience.

---

## 🔑 Authentication & Headers

Requests can be authenticated via any of the following:

| Method | Header / Parameter | Description |
| :--- | :--- | :--- |
| **Bearer Token** | `Authorization: Bearer <token>` | Laravel Sanctum token |
| **Custom Header** | `X-User-Id: <user_id>` or `X-Account-Id: <account_id>` | User database ID or 8-digit Account ID |
| **Request Body / Query** | `token=<token>` or `user_id=<user_id>` | Alternative authentication parameter |

---

## 📡 Endpoints Summary

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/party-rooms/config` | Get default rates, 50/50 split, max seats, topic tags |
| `GET` | `/api/party-rooms` | Browse active live party rooms with filters |
| `POST` | `/api/party-rooms/create` | Host/Create a new Voice or Video party room |
| `GET` | `/api/party-rooms/{id}` | Get full room state, 10-seat grid, audience count & RTC tokens |
| `POST` | `/api/party-rooms/{id}/join` | Join room as audience |
| `POST` | `/api/party-rooms/{id}/leave` | Leave room |
| `POST` | `/api/party-rooms/{id}/end` | Host ends party room |
| `GET` | `/api/party-rooms/{id}/search-invitees` | Search users or fetch liked/connected friends to invite |
| `POST` | `/api/party-rooms/{id}/invite-guest` | Host invites user to an open seat |
| `POST` | `/api/party-rooms/{id}/respond-invite` | Guest accepts or declines seat invitation |
| `POST` | `/api/party-rooms/{id}/take-seat` | Guest takes an open seat on stage |
| `POST` | `/api/party-rooms/{id}/leave-seat` | Guest leaves seat back to audience |
| `POST` | `/api/party-rooms/{id}/kick-seat` | Host removes guest from seat |
| `POST` | `/api/party-rooms/{id}/toggle-mic` | Toggle microphone mute/unmute |
| `POST` | `/api/party-rooms/{id}/toggle-video` | Toggle camera video on/off |
| `POST` | `/api/party-rooms/{id}/messages/send` | Send text or photo message (`uploads/host_image/`) |
| `GET` | `/api/party-rooms/{id}/messages` | Get in-room live chat message stream |
| `POST` | `/api/party-rooms/{id}/send-gift` | Send gift to host or seat guest (wallet balance check) |
| `POST` | `/api/party-rooms/{id}/deduct-interval` | 50/50 Revenue Billing (100 coins/min -> 50 Host / 50 Admin) |

---

## 📑 Detailed API Endpoints

### 1. Get Party Room Config & Topic Tags
`GET /api/party-rooms/config`

**Response (`200 OK`):**
```json
{
  "success": true,
  "status": true,
  "data": {
    "is_enabled": true,
    "default_voice_rate": 100,
    "default_video_rate": 100,
    "host_commission_percentage": 50.0,
    "admin_commission_percentage": 50.0,
    "max_guests": 10,
    "topic_tags": [
      {"id": "singing", "name": "Singing 🎤", "tag": "Singing"},
      {"id": "dating", "name": "Dating ❤️", "tag": "Dating"},
      {"id": "party", "name": "Party 💃", "tag": "Party"},
      {"id": "chitchat", "name": "ChitChat 💬", "tag": "ChitChat"},
      {"id": "gaming", "name": "Gaming 🎮", "tag": "Gaming"},
      {"id": "latenight", "name": "Late Night 🌙", "tag": "Late Night"}
    ],
    "default_announcement": "Welcome to our Live Fun Hangout 🥳✨! Please be respectful to everyone in the room."
  }
}
```

---

### 2. Browse Live Party Rooms Feed
`GET /api/party-rooms?room_type=voice&topic_tag=Singing&page=1`

**Query Parameters:**
- `room_type`: `voice` | `video` (optional)
- `topic_tag`: e.g. `Singing`, `Gaming` (optional)
- `search`: search query for room title or host name (optional)

**Response (`200 OK`):**
```json
{
  "success": true,
  "status": true,
  "data": [
    {
      "id": 1,
      "room_id": "PR88A9BC",
      "room_title": "My Live Fun Hangout 🥳✨",
      "room_type": "voice",
      "topic_tag": "Singing",
      "room_cover": "http://127.0.0.1:8000/uploads/host_image/party_cover_1725881234_abc.jpg",
      "channel_name": "party_voice_pr88a9bc",
      "max_seats": 10,
      "occupied_seats": 3,
      "online_members": 24,
      "coin_rate_per_minute": 100,
      "is_locked": false,
      "status": "active",
      "host": {
        "id": 5,
        "account_id": "1000000005",
        "name": "Habiba",
        "avatar_url": "http://127.0.0.1:8000/avatars/user5.jpg",
        "level": 12
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "total": 1
  }
}
```

---

### 3. Create / Host a Party Room
`POST /api/party-rooms/create`

**Request Body (`multipart/form-data` or `json`):**
```json
{
  "room_title": "My Live Fun Hangout 🥳✨",
  "room_type": "voice",
  "topic_tag": "Singing",
  "max_seats": 10,
  "coin_rate_per_minute": 100,
  "announcement": "Welcome to My Live Fun Hangout 🥳✨! Please be respectful."
}
```
*(Optional file field `room_cover_file` uploads room cover image to `public/uploads/host_image/`)*

**Response (`201 Created`):**
```json
{
  "success": true,
  "status": true,
  "message": "Party room created successfully!",
  "data": {
    "room": {
      "id": 1,
      "room_id": "PR88A9BC",
      "room_title": "My Live Fun Hangout 🥳✨",
      "room_type": "voice",
      "topic_tag": "Singing",
      "channel_name": "party_voice_pr88a9bc",
      "max_seats": 10,
      "coin_rate_per_minute": 100,
      "host_commission_percentage": 50.0,
      "admin_commission_percentage": 50.0,
      "seats": [
        {
          "seat_index": 1,
          "role": "host",
          "status": "occupied",
          "is_occupied": true,
          "is_muted": false,
          "is_video_muted": false,
          "user": {
            "id": 5,
            "account_id": "1000000005",
            "name": "Habiba",
            "is_host": true
          }
        },
        {"seat_index": 2, "status": "empty", "is_occupied": false, "user": null},
        {"seat_index": 3, "status": "empty", "is_occupied": false, "user": null},
        {"seat_index": 10, "status": "empty", "is_occupied": false, "user": null}
      ]
    },
    "rtc": {
      "driver": "agora",
      "channel_name": "party_voice_pr88a9bc",
      "token": "006d88...",
      "app_id": "...",
      "role": "host"
    }
  }
}
```

---

### 4. Search & Fetch Connected / Liked Friends to Invite
`GET /api/party-rooms/{id}/search-invitees?query=Habiba`

Returns users matching search or from mutual liked/connected friends list.

**Response (`200 OK`):**
```json
{
  "success": true,
  "status": true,
  "data": [
    {
      "id": 12,
      "account_id": "1000000012",
      "name": "Mahi",
      "avatar_url": "http://127.0.0.1:8000/avatars/user12.jpg",
      "level": 8,
      "coins": 500,
      "is_online": true,
      "is_on_seat": false,
      "is_friend": true,
      "is_liked": true
    }
  ]
}
```

---

### 5. Host Invites Guest to Seat
`POST /api/party-rooms/{id}/invite-guest`

**Request Body:**
```json
{
  "user_id": 12,
  "seat_index": 2
}
```

---

### 6. Guest Responds to Seat Invitation
`POST /api/party-rooms/{id}/respond-invite`

**Request Body:**
```json
{
  "invitation_id": 4,
  "action": "accept"
}
```

---

### 7. Take / Leave / Kick Seat
- **Take Open Seat**: `POST /api/party-rooms/{id}/take-seat` (`seat_index`: `2`)
- **Leave Seat**: `POST /api/party-rooms/{id}/leave-seat`
- **Host Kick Guest from Seat**: `POST /api/party-rooms/{id}/kick-seat` (`seat_index`: `2` or `user_id`: `12`)
- **Toggle Mic**: `POST /api/party-rooms/{id}/toggle-mic` (`is_muted`: `true` | `false`)
- **Toggle Video**: `POST /api/party-rooms/{id}/toggle-video` (`is_video_muted`: `true` | `false`)

---

### 8. Send In-Room Chat / Photo Message
`POST /api/party-rooms/{id}/messages/send`

**Request Body (`multipart/form-data`):**
- `message`: Text string (optional if sending photo)
- `file` or `image`: Image file (saved to `public/uploads/host_image/`)

**Response (`200 OK`):**
```json
{
  "success": true,
  "status": true,
  "message": "Message sent successfully.",
  "data": {
    "id": 105,
    "room_id": "PR88A9BC",
    "type": "image",
    "message": "Hey everyone, check this out!",
    "image_url": "http://127.0.0.1:8000/uploads/host_image/party_img_1725881999_xyz.jpg",
    "created_at": "2026-09-09T13:20:00+06:00",
    "sender": {
      "id": 12,
      "account_id": "1000000012",
      "name": "Mahi",
      "avatar_url": "http://127.0.0.1:8000/avatars/user12.jpg",
      "level": 8
    }
  }
}
```

---

### 9. Send Live Gift in Room
`POST /api/party-rooms/{id}/send-gift`

**Request Body:**
```json
{
  "gift_id": 3,
  "receiver_id": 5,
  "count": 1
}
```

**Checks & Balance Processing:**
1. Validates sender `coins >= total_cost`. If insufficient, returns `402 Payment Required`.
2. Deducts coins from sender wallet.
3. Credits coins to receiver wallet.
4. Logs `CoinTransaction` and `GiftTransaction`.
5. Broadcasts live gift animation banner to the room chat stream.

---

### 10. Real-Time 50/50 Minute Billing (100 coins/min)
`POST /api/party-rooms/{id}/deduct-interval`

**Request Body:**
```json
{
  "minutes": 1
}
```

**Billing & Revenue Split Logic:**
- Total Billed: `100 coins`
- **Host Share (50%)**: `50 coins` credited to Host wallet balance.
- **Admin Share (50%)**: `50 coins` credited to Platform revenue.
- If Guest `coins < 100`:
  - Returns `{"insufficient_balance": true, "evicted_from_seat": true}`
  - Automatically steps user down from Seat back to Audience.

**Response on Success (`200 OK`):**
```json
{
  "success": true,
  "status": true,
  "message": "Billed 100 coins successfully. Host received 50 (50%), Admin received 50 (50%).",
  "data": {
    "remaining_coins": 400,
    "deducted_coins": 100,
    "host_earned_coins": 50,
    "admin_earned_coins": 50,
    "seat_index": 2
  }
}
```

---

## 🛡️ Admin Management Panel

Access Admin Dashboard at `/admin/party-rooms`:
- **All Party Rooms**: `/admin/party-rooms` (Active/Ended rooms, live seats, revenue split, force close button).
- **Live Room Monitor**: `/admin/party-rooms/{id}` (Visual 10-seat layout, live chat/gift stream, participant details).
- **Settings**: `/admin/party-rooms/settings` (Configure coin rates, 50% Host / 50% Admin split %, seat limits, topic tags).
