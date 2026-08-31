# 💘 Chinchins Live — Match Tab, In-App Messaging & Profile View APIs Documentation

This document provides a comprehensive RESTful API reference for Flutter mobile app developers to implement:
1. **Match Tab & Random Video Matching** (Live host count, profile photo grid, "Start Matching" instant connection, and wallet coin balance check).
2. **Profile View Notification, Auto-Callback & Profile Visitors List** (When a user views a host profile, an automated real-time notification & greeting is sent, plus visitors list).
3. **In-App Messaging & Media Upload System** (Text with thousands of emojis, Voice audio notes, Photos & Profile pictures stored in `public/uploads/chat_messages/`, unread badges, and free message limit enforcement).
4. **In-App Notifications & Alerts** (Visitor notifications, message alerts, unread counts, and mark-as-read APIs).

---

## 🌐 Base URL & Authentication Headers

- **Base URL:** `http://your-domain.com/api` (or `http://localhost:8000/api` during local development)
- **Headers:**
  ```http
  Authorization: Bearer <SANCTUM_TOKEN>
  Accept: application/json
  ```
  *(Fallback: `user_id` or `X-User-Id` header can also be passed as query param or body param if not using Bearer token).*

---

# 1️⃣ Match Tab & Random Matching APIs

### 📱 Screen Reference: Match Tab (Live Host Pool & Matching)
Shows the total number of people waiting to meet you (e.g., `5383 People waiting to meet you`), a responsive grid of active host photos, and a **"Start Matching"** button.

---

### 🔹 Endpoint 1.1: Get Match Tab Dashboard Data
Retrieve live waiting host count, active host profile pictures, caller's coin balance, and call config.

- **Method:** `GET`
- **Path:** `/api/match` (Aliases: `/api/match/status`, `/api/match/hosts`)
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
        "avatar_url": "http://your-domain.com/uploads/profiles/host1.jpg",
        "cover_photo_url": "http://your-domain.com/uploads/profiles/cover1.jpg",
        "gallery_images": [
          "http://your-domain.com/uploads/profiles/photo1.jpg",
          "http://your-domain.com/uploads/profiles/photo2.jpg"
        ],
        "gender": "female",
        "age": 22,
        "level": "Lv4",
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
- **Path:** `/api/match/start` (Aliases: `/api/match`, `/api/match/random`, `/api/call/match`)
- **Request Body (JSON):**
```json
{
  "call_type": "video",
  "gender": "female"
}
```

#### 📥 Success Response (`200 OK`):
```json
{
  "status": true,
  "message": "Matched successfully with host. Initiating call.",
  "data": {
    "matched_host": {
      "id": 12,
      "account_id": "8934217890",
      "display_name": "Sohan Khan",
      "avatar_url": "http://your-domain.com/uploads/profiles/host1.jpg",
      "video_call_rate": 1800,
      "is_online": true,
      "is_busy": false
    },
    "call_session": {
      "call_id": 1084,
      "channel_name": "chinchins_call_1084_abc123",
      "call_type": "video",
      "status": "ringing",
      "rate_per_minute": 1800,
      "caller_coins": 2500,
      "is_free_trial": false
    }
  }
}
```

---

# 2️⃣ Profile View Notification, Auto-Callback & Visitors List

### 📱 Screen Reference: User Profile Page & Profile Visitors Tab
When User A views User B's profile:
1. The profile view is recorded in `profile_views` table.
2. **Automatic Real-Time Notification** is immediately sent to the profile owner (Host B): `User [Name] viewed your profile!`.
3. An automated welcome/greeting message from the host to the viewer's inbox is created.
4. Host B can view all visitors in the **"Who Viewed My Profile"** screen.

---

### 🔹 Endpoint 2.1: Record Profile View & Send Notification
- **Method:** `POST`
- **Path:** `/api/profile/{id}/view` (Aliases: `/api/profile/view`, `/api/user/view-profile`)
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
      "avatar_url": "http://your-domain.com/uploads/profiles/host1.jpg",
      "video_call_rate": 1800,
      "country": "Bangladesh",
      "level": "Lv4",
      "introduction": "Sweet girl looking for friendly chats."
    },
    "notification": {
      "id": 45,
      "receiver_id": 12,
      "type": "profile_view",
      "title": "New Profile Visitor 👁️",
      "message": "Alex viewed your profile!"
    },
    "callback": {
      "auto_call_triggered": true,
      "viewer_can_receive": true,
      "required_coins": 1800,
      "viewer_coins": 2500,
      "trigger_action": "INCOMING_CALL"
    },
    "auto_message": {
      "id": 102,
      "sender_id": 12,
      "type": "text",
      "message": "Hi Alex! I saw you visited my profile. Call me now?",
      "time": "Just now",
      "created_at": "2026-08-31T21:50:00.000000Z"
    }
  }
}
```

---

### 🔹 Endpoint 2.2: Get Profile Visitors ("Who Viewed My Profile")
- **Method:** `GET`
- **Path:** `/api/profile/visitors` (Aliases: `/api/visitors`, `/api/user/visitors`, `/api/notifications/visitors`)
- **Query Parameters:**
  - `page` *(optional, integer)*: page number
  - `per_page` *(optional, integer)*: default `20`

#### 📥 Success Response (`200 OK`):
```json
{
  "status": true,
  "message": "Profile visitors loaded successfully.",
  "data": {
    "total_visitors": 48,
    "unread_visitors": 3,
    "visitors": [
      {
        "view_id": 105,
        "user_id": 5,
        "account_id": "1049281745",
        "name": "Alex",
        "avatar_url": "http://your-domain.com/uploads/profiles/alex.jpg",
        "is_online": true,
        "is_busy": false,
        "video_call_rate": 100,
        "country": "Bangladesh",
        "gender": "male",
        "level": "Lv2",
        "viewed_at": "2026-08-31T21:45:00.000000Z",
        "time_ago": "5 mins ago"
      },
      {
        "view_id": 104,
        "user_id": 8,
        "account_id": "9087123456",
        "name": "Rahim King",
        "avatar_url": "http://your-domain.com/uploads/profiles/rahim.jpg",
        "is_online": false,
        "is_busy": false,
        "video_call_rate": 100,
        "country": "Bangladesh",
        "gender": "male",
        "level": "Lv5",
        "viewed_at": "2026-08-31T18:00:00.000000Z",
        "time_ago": "3 hours ago"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 3,
      "total": 48
    }
  }
}
```

---

# 3️⃣ In-App Messaging & Media Upload APIs

### 📱 Capabilities Supported:
- **Text Messages:** Full Bangla / English / Unicode characters.
- **Emoji Support:** Thousands of standard Unicode emojis (😀, 😁, 😂, 🔥, ❤️, 🎉, 🚀, etc.) and emoji stickers.
- **Voice Notes / Audio Clips:** `.mp3`, `.wav`, `.m4a`, `.aac`, `.ogg`, `.webm`, `.3gp`, `.amr`, `.opus` with recording duration in seconds.
- **Image & Profile Picture Sharing:** Multi-part file upload or URL.
- **Media File Storage Path:** Stored automatically in `public/uploads/chat_messages/` and fully accessible via public asset URLs.
- **Free Message Limits:** Users receive 5 free messages. Additional messages cost 5 coins each.

---

### 🔹 Endpoint 3.1: Send Message (Text, Emojis, Voice Audio, Images, Profile Pictures)
Send a direct message with optional media attachments.

- **Method:** `POST`
- **Path:** `/api/messages/send` (Alias: `/api/chat/send`)
- **Content-Type:** `multipart/form-data` or `application/json`

#### 📋 Accepted Request Parameters (With Field Aliases for Easy Integration):
| Parameter | Aliases | Type | Description |
|---|---|---|---|
| `receiver_id` | `receiverId`, `to_user_id`, `user_id` | Integer / String | **(Required)** Target user ID or 10-digit Account ID |
| `message` | `text`, `content`, `emoji`, `caption` | String | Message text, emoji, or caption |
| `type` | — | String | `text`, `emoji`, `voice`, `image`, `profile_picture`, `video_call`, `audio_call` *(Auto-detected if omitted)* |
| `image_file` | `image`, `photo`, `picture`, `avatar`, `profile_picture`, `file`, `media_file` | File | Image file (`.jpg`, `.jpeg`, `.png`, `.webp`, `.gif`) |
| `voice_file` | `audio_file`, `voice`, `audio`, `recording`, `voice_note` | File | Audio file (`.mp3`, `.wav`, `.m4a`, `.aac`, `.ogg`, `.webm`, `.3gp`) |
| `duration` | `voice_duration`, `audio_duration`, `length` | Integer | Audio clip length in seconds (e.g. `12`) |
| `media_url` | `url` | String | Pre-uploaded media URL (if using standalone upload) |

#### 📥 Success Response (`201 Created`):
```json
{
  "status": true,
  "message": "Message sent successfully.",
  "data": {
    "chat_message": {
      "id": 204,
      "sender_id": 5,
      "receiver_id": 12,
      "type": "image",
      "message": "Look at my picture! 🔥",
      "media_url": "http://your-domain.com/uploads/chat_messages/msg_img_1725132100_a1b2c3d4.jpg",
      "duration": 0,
      "is_read": false,
      "is_free": true,
      "coin_cost": 0,
      "created_at": "2026-08-31T21:50:00.000000Z"
    },
    "sender": {
      "id": 5,
      "name": "Alex",
      "coins": 2500,
      "free_messages_remaining": 4
    }
  }
}
```

#### 🎤 Example: Sending Voice Note (`multipart/form-data`)
```bash
curl -X POST http://your-domain.com/api/messages/send \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "receiver_id=12" \
  -F "voice_file=@/path/to/voice_recording.m4a" \
  -F "duration=15"
```

#### 😊 Example: Sending Emojis (`application/json`)
```bash
curl -X POST http://your-domain.com/api/messages/send \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"receiver_id": 12, "message": "🔥❤️🎉🚀😍"}'
```

#### ⚠️ Free Message Limit Reached & Insufficient Coins (`402 Payment Required`):
```json
{
  "status": false,
  "code": "MESSAGE_LIMIT_REACHED",
  "message": "You have reached your free limit of 5 messages. Please recharge coins to continue chatting.",
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
      "price": 100,
      "bonus_coins": 100
    }
  ],
  "payment_methods": [
    {
      "id": 1,
      "name": "bKash",
      "code": "bkash",
      "icon_url": "http://your-domain.com/uploads/payment_methods/bkash.png"
    }
  ]
}
```

---

### 🔹 Endpoint 3.2: Standalone Chat Media Upload
Allows mobile developers to upload images, photos, or voice audio files before sending the message.

- **Method:** `POST`
- **Path:** `/api/messages/upload` (Aliases: `/api/chat/upload`, `/api/upload/chat-media`)
- **Content-Type:** `multipart/form-data`
- **Parameters:**
  - `file` *(required, file)*: Image (`.jpg`, `.png`, `.webp`) or Audio (`.mp3`, `.m4a`, `.wav`, `.aac`, `.ogg`)

#### 📥 Success Response (`200 OK`):
```json
{
  "status": true,
  "message": "Media uploaded successfully to public/uploads/chat_messages.",
  "data": {
    "type": "image",
    "filename": "msg_img_1725132500_x8y9z0a1.jpg",
    "file_path": "uploads/chat_messages/msg_img_1725132500_x8y9z0a1.jpg",
    "media_url": "http://your-domain.com/uploads/chat_messages/msg_img_1725132500_x8y9z0a1.jpg",
    "url": "http://your-domain.com/uploads/chat_messages/msg_img_1725132500_x8y9z0a1.jpg",
    "extension": "jpg",
    "mime_type": "image/jpeg",
    "file_size": 245820
  }
}
```

---

### 🔹 Endpoint 3.3: Get Conversations / Inbox List
- **Method:** `GET`
- **Path:** `/api/messages` (Aliases: `/api/messages/conversations`, `/api/chat/conversations`, `/api/messages/inbox`)

#### 📥 Success Response (`200 OK`):
```json
{
  "status": true,
  "message": "Conversations loaded successfully.",
  "data": {
    "total_unread_badge": 3,
    "free_messages_limit": 5,
    "free_messages_remaining": 4,
    "user_coins": 2500,
    "conversations": [
      {
        "user_id": 12,
        "account_id": "8934217890",
        "name": "Sohan Khan",
        "avatar_url": "http://your-domain.com/uploads/profiles/host1.jpg",
        "is_online": true,
        "is_busy": false,
        "unread_count": 2,
        "last_message": {
          "text": "Look at my picture! 🔥",
          "type": "image",
          "time": "5 minutes",
          "media_url": "http://your-domain.com/uploads/chat_messages/msg_img_1725132100_a1b2c3d4.jpg",
          "created_at": "2026-08-31T21:50:00.000000Z"
        },
        "video_call_rate": 1800
      }
    ]
  }
}
```

---

### 🔹 Endpoint 3.4: Get Chat History with Specific User
- **Method:** `GET`
- **Path:** `/api/messages/{userId}` (Alias: `/api/chat/{userId}`)
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
      "id": 12,
      "account_id": "8934217890",
      "name": "Sohan Khan",
      "avatar_url": "http://your-domain.com/uploads/profiles/host1.jpg",
      "is_online": true,
      "is_busy": false,
      "video_call_rate": 1800
    },
    "free_messages_remaining": 4,
    "user_coins": 2500,
    "message_cost_after_free": 5,
    "messages": [
      {
        "id": 201,
        "sender_id": 12,
        "receiver_id": 5,
        "type": "text",
        "message": "Hi Alex! I saw you visited my profile. Call me now?",
        "media_url": null,
        "duration": 0,
        "is_read": true,
        "is_free": true,
        "coin_cost": 0,
        "created_at": "2026-08-31T21:45:00.000000Z"
      },
      {
        "id": 202,
        "sender_id": 5,
        "receiver_id": 12,
        "type": "voice",
        "message": "[Voice Note]",
        "media_url": "http://your-domain.com/uploads/chat_messages/voice_1725132150_b5c6d7e8.m4a",
        "duration": 12,
        "is_read": true,
        "is_free": true,
        "coin_cost": 0,
        "created_at": "2026-08-31T21:46:00.000000Z"
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

### 🔹 Endpoint 3.5: Mark Messages as Read
- **Method:** `POST`
- **Path:** `/api/messages/read` (Alias: `/api/chat/read`)
- **Request Body (JSON):**
```json
{
  "sender_id": 12
}
```

---

# 4️⃣ In-App Notifications & Alerts APIs

### 🔹 Endpoint 4.1: Get Notifications List
- **Method:** `GET`
- **Path:** `/api/notifications` (Alias: `/api/user/notifications`)
- **Query Parameters:**
  - `type` *(optional, string)*: `profile_view`, `message`, `call`, `gift`, `system`
  - `per_page` *(optional, integer)*: default `25`

#### 📥 Success Response (`200 OK`):
```json
{
  "status": true,
  "message": "Notifications retrieved successfully.",
  "data": {
    "unread_count": 5,
    "profile_view_unread": 2,
    "message_unread": 3,
    "notifications": [
      {
        "id": 45,
        "user_id": 12,
        "actor_id": 5,
        "type": "profile_view",
        "title": "New Profile Visitor 👁️",
        "message": "Alex viewed your profile!",
        "data": {
          "viewer_id": 5,
          "account_id": "1049281745",
          "name": "Alex",
          "avatar_url": "http://your-domain.com/uploads/profiles/alex.jpg",
          "is_online": true,
          "video_call_rate": 100,
          "viewed_at": "2026-08-31T21:45:00.000000Z"
        },
        "is_read": false,
        "read_at": null,
        "created_at": "2026-08-31T21:45:00.000000Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "total": 1
    }
  }
}
```

---

### 🔹 Endpoint 4.2: Get Quick Notification Unread Badges Count
- **Method:** `GET`
- **Path:** `/api/notifications/unread-count`

#### 📥 Success Response (`200 OK`):
```json
{
  "status": true,
  "message": "Unread counts retrieved successfully.",
  "data": {
    "total_unread": 5,
    "profile_view_unread": 2,
    "message_unread": 3
  }
}
```

---

### 🔹 Endpoint 4.3: Mark Notifications as Read
- **Method:** `POST`
- **Path:** `/api/notifications/read` (or `/api/notifications/{id}/read`)
- **Request Body (JSON):**
```json
{
  "id": 45
}
```
*(Or omit `id` to mark all notifications as read, or pass `type: "profile_view"` to mark a specific category).*

---

### 🔹 Endpoint 4.4: Clear / Delete Notifications
- **Method:** `DELETE` or `POST`
- **Path:** `/api/notifications` or `/api/notifications/clear`
- **Optional Request Body (JSON):** `{"type": "profile_view"}`
