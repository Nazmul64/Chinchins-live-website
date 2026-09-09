# 🌟 ChinChins Live — Complete RESTful API Documentation

> **Base Production URL:** `https://chinchins.live/api`  
> **Static Assets Base URL:** `https://chinchins.live/uploads/`  
> **Reseller Web Portal:** `https://chinchins.live/reseller/login`  
> **Admin Dashboard:** `https://chinchins.live/admin/dashboard`  
> **Headers:**
> - `Authorization: Bearer <sanctum_token>`
> - `Accept: application/json`
> - `Content-Type: application/json`

---

## 📑 Quick Navigation

1. [Payment Options & Gateways](#1-payment-options--gateways)
2. [Coin Packages & Gems Store](#2-coin-packages--recharge-modal)
3. [Reseller Directory & Profiles](#3-reseller-directory--profiles)
4. [User <-> Reseller Live Chat & Proof Uploads](#4-user---reseller-live-chat)
5. [Reseller Portal & Coin Transfer Engine](#5-reseller-portal--coin-transfer-engine)
6. [Reseller Refill & Cashout Workflows](#6-reseller-refill--cashout-workflows)
7. [Wallet & Manual Deposits](#7-wallet--manual-deposits)

---

## 1. Payment Options & Gateways

### 1.1 Get Payment Options (Recharge Selection Screen)
Returns the selected package amount along with configured payment gateways (bKash, Nagad, etc.), Google Play, and the **Reseller** option badge (`Up To 29%↑`).

- **Endpoint:** `GET /api/payment-options`
- **Aliases:** `GET /api/deposit/options`, `POST /api/payment-options`
- **Query Params (Optional):**
  - `package_id` (e.g. `1`)
  - `amount` (e.g. `150.00`)
  - `coins` (e.g. `7560`)

#### Response (`200 OK`)
```json
{
  "status": true,
  "message": "Payment options retrieved successfully.",
  "package_id": 1,
  "amount": 150.00,
  "coins": 7560,
  "formatted_amount": "BDT 150.00",
  "header_title": "Payment options",
  "options_title": "Options for you",
  "button_text": "Continue",
  "default_selected": "reseller",
  "options": [
    {
      "id": 1,
      "key": "bkash",
      "name": "Bkash",
      "type": "gateway",
      "account_type": "Personal Account",
      "account_number": "01700000000",
      "icon": "https://chinchins.live/uploads/payment_gateways/bkash.png",
      "badge": null,
      "instructions": "1. Send Money to 01700000000\n2. Enter TrxID"
    },
    {
      "id": 2,
      "key": "nagad",
      "name": "Nagad",
      "type": "gateway",
      "account_type": "Personal Account",
      "account_number": "01800000000",
      "icon": "https://chinchins.live/uploads/payment_gateways/nagad.png",
      "badge": null,
      "instructions": "1. Send Money to Nagad Personal"
    },
    {
      "id": "google_play",
      "key": "google_play",
      "name": "Google Play",
      "type": "in_app_purchase",
      "account_type": "Official In-App Store",
      "account_number": null,
      "icon": "https://upload.wikimedia.org/wikipedia/commons/7/7a/Google_Play_2022_logo.svg",
      "badge": null
    },
    {
      "id": "reseller",
      "key": "reseller",
      "name": "Reseller",
      "type": "reseller",
      "account_type": "Direct Agent Chat",
      "account_number": null,
      "icon": "https://ui-avatars.com/api/?name=Reseller&background=1e1b4b&color=fbbf24&bold=true",
      "badge": "Up To 29%↑",
      "badge_color": "#ef4444",
      "active_count": 2,
      "instructions": "Recharge via authorized live resellers with exclusive discounts."
    }
  ]
}
```

---

## 2. Coin Packages & Recharge Modal

### 2.1 Get Recharge Modal Data (In-Call / In-Chat Sheet)
- **Endpoint:** `GET /api/recharge/modal-data`
- **Aliases:** `GET /api/coin-packages/recharge-modal`, `POST /api/recharge/modal-data`

#### Response (`200 OK`)
```json
{
  "status": true,
  "message": "Recharge modal data retrieved successfully.",
  "user_gems": 60,
  "wallet_label": "My Gems",
  "button_text": "Continue",
  "packages": [
    {
      "id": 1,
      "title": "7560 Coins",
      "coins": 7560,
      "bonus_coins": 0,
      "total_coins": 7560,
      "price": 150.00,
      "formatted_price": "BDT 150.00",
      "badge": "50%off ONCE",
      "badge_color": "orange"
    },
    {
      "id": 2,
      "title": "8100 Coins",
      "coins": 8100,
      "price": 300.00,
      "formatted_price": "BDT 300.00",
      "badge": "17%off"
    }
  ]
}
```

---

## 3. Reseller Directory & Profiles

### 3.1 Get All Active Resellers
- **Endpoint:** `GET /api/resellers`
- **Query Params (Optional):** `coins=7560` (or `gems=7560`) to prefill inquiry text.

#### Response (`200 OK`)
```json
{
  "status": true,
  "message": "Resellers retrieved successfully.",
  "default_badge": "Up To 29%↑",
  "data": [
    {
      "id": 1,
      "name": "MURAD COINS RESELLER",
      "avatar": "https://chinchins.live/uploads/reseller/murad.jpg",
      "level": "Lv5",
      "location": "Dhaka",
      "age": 27,
      "gender": "male",
      "phone": "01848340232",
      "bio": "কয়েন রিচার্জ, হোস্টিং এবং বিভিন্ন ধরণের গিফট ক্রয় করা হয়\nযোগাযোগ ০১848340232\nহোস্টিং সেলারি তুলনামূলক বেশি দেওয়া হয়",
      "discount_tag": "Up To 29%↑",
      "badge_title": "Diamond Reseller",
      "is_online": true,
      "status_text": "Online",
      "total_sold_coins": 125000,
      "prefill_message": "Hello! My user ID is 266813634. I want to recharge 7560 gems. How much should I pay? 【GIVE THE BEST DISCOUNT 💎 DIAMOND 💎】"
    }
  ]
}
```

### 3.2 Get Reseller Profile
- **Endpoint:** `GET /api/resellers/{id}`

---

## 4. User <-> Reseller Live Chat

### 4.1 Get Chat Messages with Reseller
- **Endpoint:** `GET /api/resellers/{id}/messages`
- **Alias:** `GET /api/reseller/chat/{resellerId}`

#### Response (`200 OK`)
```json
{
  "status": true,
  "message": "Chat messages retrieved successfully.",
  "reseller": {
    "id": 1,
    "name": "MURAD COINS RESELLER",
    "avatar": "https://chinchins.live/uploads/reseller/murad.jpg",
    "level": "Lv5",
    "is_online": true,
    "status_text": "Online",
    "discount_tag": "Up To 29%↑"
  },
  "user": {
    "id": 15,
    "account_id": "266813634",
    "name": "SweetHeart",
    "coins": 60
  },
  "data": [
    {
      "id": 101,
      "reseller_id": 1,
      "user_id": 15,
      "sender_type": "user",
      "is_me": true,
      "type": "text",
      "message": "Hello! My user ID is 266813634. I want to recharge 7560 gems. How much should I pay? 【GIVE THE BEST DISCOUNT 💎 DIAMOND 💎】",
      "media_url": null,
      "coins_amount": 7560,
      "is_read": true,
      "created_at": "2026-09-09T04:59:00Z",
      "formatted_time": "04:59 AM"
    }
  ]
}
```

### 4.2 Send Message / Screenshot / Voice to Reseller
- **Endpoint:** `POST /api/resellers/{id}/messages`
- **Alias:** `POST /api/reseller/chat/send`
- **Payload (`multipart/form-data` or `json`):**
  - `message` (string, optional if media attached)
  - `type` (`text` | `image` | `voice` | `recharge_request`)
  - `coins_amount` (int, optional)
  - `image` (file, optional image/screenshot saved in `uploads/reseller/`)
  - `voice` (file, optional audio note saved in `uploads/reseller/`)

### 4.3 Upload Chat Attachment
- **Endpoint:** `POST /api/reseller/chat/upload`
- **Payload:** `file` (image or voice note)

### 4.4 Send Coin Gift to Reseller in Chat
- **Endpoint:** `POST /api/reseller/chat/gift`
- **Payload:**
```json
{
  "reseller_id": 1,
  "coins": 500,
  "gift_name": "Diamond Ring"
}
```

---

## 5. Reseller Portal & Coin Transfer Engine

### 5.1 Reseller Login
- **Endpoint:** `POST /api/reseller/login`
- **Payload:**
```json
{
  "email": "murad@chinchins.live",
  "password": "password123"
}
```
#### Response (`200 OK`)
```json
{
  "status": true,
  "message": "Login successful",
  "token": "25|xyz987...",
  "token_type": "Bearer",
  "reseller": {
    "id": 1,
    "name": "MURAD COINS RESELLER",
    "email": "murad@chinchins.live",
    "coins_balance": 500000,
    "total_sold_coins": 125000,
    "level": "Lv5"
  }
}
```

### 5.2 Validate User ID (Pre-Transfer Verification)
Checks if user exists and returns avatar, name, and current coins to prevent wrong transfers.
- **Endpoint:** `POST /api/reseller/validate-user`
- **Web AJAX:** `GET /reseller/validate-user?account_id=266813634`
- **Payload / Query:** `account_id=266813634`

#### Response (`200 OK`)
```json
{
  "status": true,
  "message": "User found",
  "user": {
    "id": 15,
    "account_id": "266813634",
    "name": "SweetHeart",
    "avatar": "https://chinchins.live/uploads/avatars/user15.jpg",
    "level": "Lv3",
    "coins": 60,
    "country": "Bangladesh"
  }
}
```

### 5.3 Transfer Coins to User (Instant Recharge)
Deducts coins from reseller balance and immediately credits target user's wallet with ledger logs.
- **Endpoint:** `POST /api/reseller/transfer-coins`
- **Web Form:** `POST /reseller/transfer`
- **Payload:**
```json
{
  "target_account_id": "266813634",
  "coins": 7560,
  "amount_bdt": 150.00,
  "payment_method": "bKash",
  "transaction_id": "9H8A7B6C",
  "notes": "Recharge via customer chat offer"
}
```
#### Response (`200 OK`)
```json
{
  "status": true,
  "message": "Successfully transferred 7,560 gems to SweetHeart!",
  "data": {
    "transfer_id": 42,
    "coins_transferred": 7560,
    "user_account_id": "266813634",
    "user_name": "SweetHeart",
    "reseller_remaining_coins": 492440
  }
}
```

---

## 6. Reseller Refill & Cashout Workflows

### 6.1 Stock Refill / Deposit Request (Reseller -> Admin)
- **Web Endpoint:** `POST /reseller/deposit/submit`
- **Admin Approval:** `POST /admin/resellers/deposits/{id}/approve`
- Automatically credits coins to reseller upon Admin approval.

### 6.2 Coin Cash-Out / Withdrawal (Reseller -> Admin)
- **Web Endpoint:** `POST /reseller/withdrawal/submit`
- Deducts coins immediately with configurable commission percentage (e.g. 2.5%).
- **Admin Approval:** `POST /admin/resellers/withdrawals/{id}/approve`

---

## 7. Wallet & Manual Deposits

### 7.1 Wallet Balance
- **Endpoint:** `GET /api/wallet` (Aliases: `/api/wallet/balance`, `/api/coins/balance`)

### 7.2 Submit Manual Deposit (User -> Admin)
- **Endpoint:** `POST /api/deposit/submit`
- **Payload:**
```json
{
  "payment_method_id": 1,
  "amount": 500.00,
  "sender_number": "01700000000",
  "transaction_id": "9H8A7B6C"
}
```

---

## ⚙️ Testing & Verification Checklist

| Area | Route / Endpoint | Expected Status | Result |
|---|---|---|---|
| **Payment Options** | `GET /api/payment-options` | `200 OK` (Includes Gateways + Reseller badge) | ✅ Verified |
| **Resellers List** | `GET /api/resellers` | `200 OK` (Active resellers with Lv5, Bio, Badge) | ✅ Verified |
| **User-Reseller Chat** | `GET /api/resellers/1/messages` | `200 OK` (Messages array + Read receipts) | ✅ Verified |
| **Send Chat Message** | `POST /api/reseller/chat/send` | `201 Created` (Text/Screenshot stored in `uploads/reseller/`) | ✅ Verified |
| **User ID Lookup** | `GET /reseller/validate-user?account_id=...` | `200 OK` (Validates user avatar & coins) | ✅ Verified |
| **Coin Transfer** | `POST /reseller/transfer` | `302/200` (Instant deduction & credit to User) | ✅ Verified |
| **Admin Management** | `/admin/resellers` | `200 OK` (CRUD, stock adjust, approval flows) | ✅ Verified |
| **Reseller Portal** | `/reseller/login` | `200 OK` (Dedicated agent dashboard & live chat) | ✅ Verified |
