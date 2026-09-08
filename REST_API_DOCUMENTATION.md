# Chinchins Live — RESTful API Documentation & Master Integration Guide

> **Base Production URL:** `https://chinchins.live/api`  
> **Static Assets Base URL:** `https://chinchins.live/uploads/`  
> **Default Auth Headers:**  
> - `Authorization: Bearer <sanctum_token>`  
> - `X-User-Id: <user_id>` (Fallback)  
> - `Accept: application/json`  
> - `Content-Type: application/json`

---

## 📑 Table of Contents

1. [Streamer Profile & UI Integration APIs](#1-streamer-profile--ui-integration-apis)
   - [Get User / Streamer Profile](#11-get-user--streamer-profile)
   - [Like / Heart Host](#12-like--heart-host)
   - [Send Instant Hi / Greeting](#13-send-instant-hi--greeting)
   - [Update Profile Information](#14-update-profile-information)
   - [Top Fans Leaderboard](#15-top-fans-leaderboard)
   - [Received Gifts Catalog & History](#16-received-gifts-catalog--history)
2. [Home Feed & Streamer Discovery](#2-home-feed--streamer-discovery)
   - [Get Streamers Feed](#21-get-streamers-feed)
   - [Search by 8-Digit Account ID or Name](#22-search-by-8-digit-account-id-or-name)
   - [Worldwide Countries List](#23-worldwide-countries-list)
3. [1v1 Video Calling & WebRTC Signaling](#3-1v1-video-calling--webrtc-signaling)
   - [Pre-Call Verification & Balance Check](#31-pre-call-verification--balance-check)
   - [Initiate Video Call](#32-initiate-video-call)
   - [Accept / Reject / Cancel Call](#33-accept--reject--cancel-call)
   - [In-Call Minute Billing Pulse](#34-in-call-minute-billing-pulse)
   - [End Video Call](#35-end-video-call)
4. [My Bag (আমার ব্যাগ) Inventory & Store Catalog](#4-my-bag-আমার-ব্যাগ-inventory--store-catalog)
   - [Get User Bag Inventory](#41-get-user-bag-inventory)
   - [Get Bag Store Catalog](#42-get-bag-store-catalog)
   - [Purchase Bag Item](#43-purchase-bag-item)
   - [Equip / Unequip Item](#44-equip--unequip-item)
   - [Gift Bag Item by Account ID](#45-gift-bag-item-by-account-id)
5. [Wallet, Coin Recharge & Withdrawals](#5-wallet-coin-recharge--withdrawals)
   - [Get Wallet Balance](#51-get-wallet-balance)
   - [Coin Packages & Recharge Modal](#52-coin-packages--recharge-modal)
   - [Submit Deposit (bKash, Nagad, etc.)](#53-submit-deposit-request)
   - [Cash Out / Withdrawal Request](#54-cash-out--withdrawal-request)
6. [VIP Cards & Spend Less Get More](#6-vip-cards--spend-less-get-more)
   - [VIP Cards Catalog & Floating Banner](#61-vip-cards-catalog--floating-banner)
   - [Purchase VIP Card](#62-purchase-vip-card)
   - [Claim Daily VIP Diamonds](#63-claim-daily-vip-diamonds)
7. [Authentication & Account Management](#7-authentication--account-management)
   - [Register & Login](#71-register--login)
   - [Get Current User (`/auth/me`)](#72-get-current-user)
   - [Delete Account](#73-delete-account)

---

## 1. Streamer Profile & UI Integration APIs

### 1.1 Get User / Streamer Profile
Returns the complete profile details corresponding to the streamer detail screen (including Charm Level, Top Fan, Gifts Received grid, Interest Tags, Speaking Languages, Close Friends, and Video Call rates).

- **Endpoint:** `GET /api/profile/{id}` or `GET /api/profile/me`
- **Parameter:** `{id}` can be the User ID (e.g. `12`) or 8-Digit Account ID (e.g. `229051289`).
- **Headers:** `Authorization: Bearer <sanctum_token>` or `user_id: <id>`

#### Sample Request:
```bash
curl -X GET "https://chinchins.live/api/profile/229051289" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abcdef123456"
```

#### Sample Response:
```json
{
  "status": true,
  "data": {
    "user": {
      "id": 12,
      "account_id": "229051289",
      "name": "Hamna",
      "display_name": "Hamna",
      "nickname": "Hamna Live",
      "avatar_url": "https://chinchins.live/uploads/avatars/hamna.jpg",
      "cover_photo_url": "https://chinchins.live/uploads/covers/hamna_cover.jpg",
      "gender": "female",
      "age": 22,
      "display_age": 22,
      "level": "Lv4",
      "display_level": "Lv4",
      "country": "Pakistan",
      "country_code": "PK",
      "country_flag": "🇵🇰",
      "city": "Lahore",
      "is_active": true,
      "is_online": true,
      "is_verified": true,
      "coins": 45000,
      "introduction": "Welcome to my live stream! Happy to connect with everyone.",
      "video_call_rate": 1800
    },
    "charm_level": "Lv7",
    "display_level": "Lv4",
    "interest_tags": [
      "Music",
      "Gaming",
      "Chat",
      "Lifestyle"
    ],
    "speaking_languages": [
      "English",
      "Spanish",
      "Urdu"
    ],
    "top_fan": {
      "id": 84,
      "name": "Raza me",
      "avatar_url": "https://chinchins.live/uploads/avatars/raza.jpg",
      "fan_coins": 54200,
      "formatted": "54.20K"
    },
    "close_friends": {
      "current": 0,
      "max": 3,
      "label": "(0/3)"
    },
    "video_call_rate": 1800,
    "video_call_rate_text": "1800/min",
    "i_like": 0,
    "like_me": 0,
    "my_gems": 45000,
    "beans_central": 0,
    "likes": {
      "total_likes": 2740,
      "formatted_likes": "2.74K",
      "i_like": 0,
      "like_me": 0
    },
    "gifts_count": 16,
    "gifts_total_coins": 916710,
    "formatted_gifts_coins": "916.71K",
    "gifts_received": [
      {
        "gift_id": 1,
        "name": "Supercar",
        "image_url": "https://chinchins.live/uploads/gifts/supercar.png",
        "coins": 200000,
        "formatted_coins": "200K",
        "quantity": 2,
        "count_label": "x2",
        "total_coins": 400000,
        "formatted_total": "400K"
      },
      {
        "gift_id": 2,
        "name": "Sport Bike",
        "image_url": "https://chinchins.live/uploads/gifts/bike.png",
        "coins": 186620,
        "formatted_coins": "186.62K",
        "quantity": 1,
        "count_label": "x1",
        "total_coins": 186620,
        "formatted_total": "186.62K"
      },
      {
        "gift_id": 3,
        "name": "Luxury Yacht",
        "image_url": "https://chinchins.live/uploads/gifts/yacht.png",
        "coins": 100000,
        "formatted_coins": "100K",
        "quantity": 4,
        "count_label": "x4",
        "total_coins": 400000,
        "formatted_total": "400K"
      }
    ]
  }
}
```

---

### 1.2 Like / Heart Host
Triggered when the user taps the circular pink heart button on the top right of the host profile.

- **Endpoint:** `POST /api/profile/{id}/like` or `POST /api/user/{id}/like`
- **Body:** `{ "likes": 1 }` (Optional, defaults to 1)

#### Sample Response:
```json
{
  "status": true,
  "message": "Liked streamer successfully!",
  "data": {
    "user_id": 12,
    "total_likes": 2741,
    "formatted_likes": "2.74K"
  }
}
```

---

### 1.3 Send Instant Hi / Greeting
Triggered when the user taps the circular purple **"Hi"** button.

- **Endpoint:** `POST /api/profile/{id}/hi` or `POST /api/chat/send-hi`
- **Body (Optional):**
```json
{
  "receiver_id": 12,
  "message": "Hi Hamna! 👋"
}
```

#### Sample Response:
```json
{
  "status": true,
  "message": "Hi greeting sent successfully!",
  "data": {
    "conversation_id": 105,
    "message_id": 4820,
    "text": "Hi Hamna! 👋"
  }
}
```

---

### 1.4 Update Profile Information
- **Endpoint:** `POST /api/profile/update`
- **Body Parameters:**
  - `introduction` (string) — Biography / intro text
  - `tags` (array or comma string) — `["Music", "Gaming", "Chat"]`
  - `languages` (array or comma string) — `["English", "Spanish"]`
  - `video_call_rate` (int) — Coin rate per minute (e.g. `1800`)
  - `first_name`, `last_name`, `nickname`, `age`, `city`, `country`

---

## 2. Home Feed & Streamer Discovery

### 2.1 Get Streamers Feed
- **Endpoint:** `GET /api/home` or `GET /api/streamers`
- **Query Parameters:**
  - `country` (string, optional) — Filter by country (e.g. `Pakistan`, `Bangladesh`, `all`)
  - `gender` (string, optional) — `female`, `male`, `all`
  - `search` (string, optional) — Filter by streamer name or ID
  - `page` (int, default: 1)
  - `per_page` (int, default: 30)

#### Sample Response:
```json
{
  "status": true,
  "success": true,
  "data": {
    "streamers": [
      {
        "id": 12,
        "account_id": "229051289",
        "name": "Hamna",
        "avatar_url": "https://chinchins.live/uploads/avatars/hamna.jpg",
        "country": "Pakistan",
        "country_flag": "🇵🇰",
        "is_online": true,
        "is_verified": true,
        "level": "Lv4",
        "charm_level": "Lv7",
        "video_call_rate": 1800,
        "rate_per_minute": 1800,
        "interest_tags": ["Music", "Gaming", "Chat"],
        "speaking_languages": ["English", "Spanish"]
      }
    ],
    "total": 54,
    "current_page": 1,
    "last_page": 2
  }
}
```

---

### 2.2 Search by 8-Digit Account ID or Name
- **Endpoint:** `GET /api/search?q=229051289` or `GET /api/users/search?search=Hamna`

#### Sample Response:
```json
{
  "status": true,
  "message": "User found with ID 229051289",
  "data": {
    "exact_match": true,
    "user": {
      "id": 12,
      "account_id": "229051289",
      "name": "Hamna",
      "avatar_url": "https://chinchins.live/uploads/avatars/hamna.jpg",
      "country": "Pakistan"
    }
  }
}
```

---

## 3. 1v1 Video Calling & WebRTC Signaling

### 3.1 Pre-Call Verification & Balance Check
Verifies if the caller has sufficient coins to connect with the streamer.

- **Endpoint:** `POST /api/call/check-permission`
- **Body:**
```json
{
  "host_id": 12
}
```

#### Sample Response:
```json
{
  "status": true,
  "can_call": true,
  "user_coins": 5000,
  "rate_per_minute": 1800,
  "max_call_minutes": 2
}
```

---

### 3.2 Initiate Video Call
Triggered when tapping the **"Video Call 💎 1800/min"** button.

- **Endpoint:** `POST /api/call/initiate`
- **Body:**
```json
{
  "host_id": 12,
  "call_type": "video"
}
```

#### Sample Response:
```json
{
  "status": true,
  "message": "Calling host...",
  "data": {
    "call_id": 491,
    "channel_name": "call_491_chinchins",
    "token": "006d...agora_or_webrtc_session_token",
    "rate_per_minute": 1800,
    "host": {
      "id": 12,
      "name": "Hamna",
      "avatar_url": "https://chinchins.live/uploads/avatars/hamna.jpg"
    }
  }
}
```

---

### 3.3 In-Call Minute Billing Pulse
Sent every 60 seconds during an active call to deduct coins and split revenue.

- **Endpoint:** `POST /api/call/deduct-interval`
- **Body:**
```json
{
  "call_id": 491
}
```

---

## 4. My Bag (আমার ব্যাগ) Inventory & Store Catalog

My Bag features **6 item categories**:
1. `coupon` — Recharge discounts & coin vouchers
2. `avatar_frame` — Animated avatar frames
3. `chat_style` — Custom glowing chat bubbles
4. `profile_card` — Themed background cards
5. `entrance_bubble` — Room entrance badges
6. `big_entrance` — Fullscreen vehicle ride animations

### 4.1 Get User Bag Inventory
- **Endpoint:** `GET /api/bag` (or `GET /api/my-bag`)
- **Query Params:**
  - `category` (optional) — `all`, `coupon`, `avatar_frame`, `chat_style`, `profile_card`, `entrance_bubble`, `big_entrance`
  - `status` (optional) — `unused`, `used`, `expired`, `all`

---

### 4.2 Get Bag Store Catalog
- **Endpoint:** `GET /api/bag/store`

### 4.3 Purchase Bag Item
- **Endpoint:** `POST /api/bag/purchase`
- **Body:** `{ "bag_item_id": 1 }`

### 4.4 Equip / Unequip Item
- **Equip:** `POST /api/bag/use` `{ "user_bag_item_id": 14 }`
- **Unequip:** `POST /api/bag/unequip` `{ "category": "avatar_frame" }`

### 4.5 Gift Bag Item by Account ID
- **Endpoint:** `POST /api/bag/gift`
- **Body:**
```json
{
  "user_bag_item_id": 14,
  "recipient_account_id": "229051289"
}
```

---

## 5. Wallet, Coin Recharge & Withdrawals

### 5.1 Get Wallet Balance
- **Endpoint:** `GET /api/wallet/balance`

### 5.2 Coin Packages & Recharge Modal
- **Endpoint:** `GET /api/coin-packages` or `GET /api/recharge/modal-data`

### 5.3 Submit Deposit Request
- **Endpoint:** `POST /api/deposit/submit`
- **Body:**
```json
{
  "method": "bkash",
  "amount": 500,
  "transaction_id": "TRX928410294",
  "sender_number": "01700000000"
}
```

### 5.4 Cash Out / Withdrawal Request
- **Endpoint:** `POST /api/withdraw/submit`
- **Body:**
```json
{
  "method": "bkash",
  "coins": 50000,
  "account_number": "01700000000"
}
```

---

## 6. VIP Cards & Spend Less Get More

### 6.1 VIP Cards Catalog & Floating Banner
- **Endpoint:** `GET /api/vip-cards` or `GET /api/vip-cards/banner`

### 6.2 Purchase VIP Card
- **Endpoint:** `POST /api/vip-cards/purchase`
- **Body:** `{ "vip_card_id": 1 }`

### 6.3 Claim Daily VIP Diamonds
- **Endpoint:** `POST /api/vip-cards/claim-daily`
- **Body:** `{ "vip_card_id": 1 }`

---

## 7. Authentication & Account Management

### 7.1 Register & Login
- **Register:** `POST /api/register`
- **Login:** `POST /api/login`
- **Payload:** `{ "phone": "01700000000", "password": "password" }` or `{ "email": "user@example.com", "password": "password" }`

### 7.2 Get Current User
- **Endpoint:** `GET /api/auth/me` or `GET /api/user/me`

### 7.3 Delete Account
- **Endpoint:** `POST /api/user/delete-account`
