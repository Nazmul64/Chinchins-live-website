# Withdraw - User & Seller Withdraw System
## RESTful API Documentation & Backend Integration Guide

---

## 📌 Overview

This document provides the complete API reference and backend specification for:
1. **User Beans & Coins Withdrawal System** (Balance validation, real-time escrow lock, and automated status handling).
2. **Seller / Reseller Withdrawal System** (Independent seller balance withdrawal, transaction history, and custom commission).
3. **Withdrawal Commission Engine** (Configurable in Admin Panel: separate commission percentages for Users and Sellers; supports 0% fee).
4. **Dynamic Party Room Tags** (Zero hardcoding; fetched directly from the database).
5. **High-Priority VoIP Call Signal & Socket Broadcasting** (Wakes and alerts hosts even during active live streaming).

---

## 🚀 Quick Endpoint Reference

| Category | Method | Endpoint | Description |
| :--- | :--- | :--- | :--- |
| **User Withdraw** | `POST` | `/api/wallet/withdraw` | Submit user beans withdrawal request with balance hold |
| **User History** | `GET` | `/api/wallet/withdraw-history` | View user's withdrawal request history and current beans |
| **User Methods** | `GET` | `/api/wallet/withdraw-methods` | Active withdrawal payment methods, min/max limits & rate |
| **User Info** | `GET` | `/api/wallet/withdraw` | User balance, limits, commission, and gateway info |
| **Seller Withdraw** | `POST` | `/api/seller/withdraw` | Submit seller/reseller coins withdrawal request |
| **Seller History** | `GET` | `/api/seller/withdraw-history` | View seller withdrawal request ledger |
| **Seller Methods** | `GET` | `/api/seller/withdraw-methods` | Seller withdrawal limits, commission %, and methods |
| **Party Tags** | `GET` | `/api/party/tags` | Dynamic party room tags from database (zero hardcode) |

---

## 1. 💰 User Withdrawal APIs

### 1.1 Submit Withdrawal Request (with Real-Time Balance Lock)
- **Endpoint:** `POST /api/wallet/withdraw`
- **Aliases:** `POST /api/withdraw/submit`, `POST /api/withdraw/request`
- **Headers:**
  - `Authorization: Bearer <TOKEN>`
  - `Content-Type: application/json`
  - `Accept: application/json`

#### Request Payload:
```json
{
  "amount_beans": 5000,
  "payment_method": "bkash",
  "account_number": "017XXXXXXXX",
  "account_type": "Personal",
  "user_note": "Cash out my weekly live streaming beans"
}
```

> **Note:** The API also accepts `"coins"` or `"amount"` as backward-compatible aliases for `"amount_beans"`. `"payment_method"` accepts `"bkash"`, `"nagad"`, `"rocket"`, `"bank"` or a numerical `payment_method_id`.

#### Backend Execution Flow:
1. Validates that `amount_beans` is within `min_withdraw_coins` and `max_withdraw_coins` set by Admin.
2. Checks that `$user->beans_balance >= amount_beans`.
3. **Escrow Lock:** Deducts `amount_beans` immediately from user balance into pending hold (`is_held = true`) and logs a `withdraw_hold` entry in `coin_transactions`.
4. Saves record in `withdraw_requests` with `status: 'pending'`.
5. If Admin approves: payout transaction ID is recorded and status becomes `'approved'`.
6. If Admin rejects: held beans are automatically refunded back to the user's wallet with ledger entry `withdraw_refund`.

#### Success Response (`201 Created`):
```json
{
  "status": true,
  "success": true,
  "message": "Withdrawal request submitted successfully! Your beans have been held in escrow and sent to admin for payout approval.",
  "data": {
    "withdraw_id": 1,
    "amount_beans": 5000,
    "coins": 5000,
    "formatted_beans": "5,000 Beans",
    "gross_amount": 500.0,
    "formatted_gross_amount": "৳500.00",
    "commission_percent": 5.0,
    "commission_amount": 25.0,
    "formatted_commission_amount": "৳25.00",
    "net_payable_amount": 475.0,
    "formatted_net_payable_amount": "৳475.00",
    "payment_method": "bKash Personal",
    "account_number": "017XXXXXXXX",
    "account_type": "Personal",
    "status": "pending",
    "is_held": true,
    "remaining_beans_balance": 5000,
    "created_at": "2026-09-26T10:14:57+00:00"
  }
}
```

#### Error Response - Insufficient Balance (`422 Unprocessable Content`):
```json
{
  "status": false,
  "message": "Insufficient beans balance. Your current balance is 2,000 beans, but requested 5,000 beans.",
  "data": {
    "current_beans": 2000,
    "requested_beans": 5000,
    "shortfall": 3000
  }
}
```

---

### 1.2 User Withdrawal History
- **Endpoint:** `GET /api/wallet/withdraw-history`
- **Aliases:** `GET /api/withdraw/history`
- **Headers:** `Authorization: Bearer <TOKEN>`

#### Success Response (`200 OK`):
```json
{
  "status": true,
  "success": true,
  "message": "Withdrawal history retrieved successfully.",
  "data": [
    {
      "id": 1,
      "user_id": 42,
      "payment_method_id": 1,
      "payment_method_name": "bKash Personal",
      "coins": 5000,
      "rate_per_bdt": "10.00",
      "gross_amount": "500.00",
      "commission_percent": "5.00",
      "commission_amount": "25.00",
      "net_payable_amount": "475.00",
      "account_number": "017XXXXXXXX",
      "account_type": "Personal",
      "status": "pending",
      "is_held": true,
      "transaction_id": null,
      "admin_note": null,
      "created_at": "2026-09-26T10:14:57.000000Z"
    }
  ],
  "beans_balance": 5000,
  "current_coins": 15120,
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "total": 1
  }
}
```

---

### 1.3 Active Payment Methods & Withdrawal Config
- **Endpoint:** `GET /api/wallet/withdraw-methods`
- **Aliases:** `GET /api/withdraw/methods`, `GET /api/withdraw-methods`
- **Headers:** `Authorization: Bearer <TOKEN>` (Optional)

#### Success Response (`200 OK`):
```json
{
  "success": true,
  "status": true,
  "message": "Active withdrawal payment methods retrieved successfully.",
  "data": [
    {
      "id": 1,
      "name": "bKash Personal",
      "code": "bkash",
      "account_type": "Personal",
      "icon": "https://chinchins.live/assets/images/bkash.png",
      "icon_url": "https://chinchins.live/assets/images/bkash.png",
      "min_withdraw": 50,
      "max_withdraw": 50000,
      "instructions": "Enter your 11-digit personal bKash mobile number."
    },
    {
      "id": 2,
      "name": "Nagad Personal",
      "code": "nagad",
      "account_type": "Personal",
      "icon": "https://chinchins.live/assets/images/nagad.png",
      "icon_url": "https://chinchins.live/assets/images/nagad.png",
      "min_withdraw": 50,
      "max_withdraw": 50000,
      "instructions": "Enter your 11-digit personal Nagad mobile number."
    },
    {
      "id": 3,
      "name": "Rocket Personal",
      "code": "rocket",
      "account_type": "Personal",
      "icon": "https://chinchins.live/assets/images/rocket.png",
      "icon_url": "https://chinchins.live/assets/images/rocket.png",
      "min_withdraw": 50,
      "max_withdraw": 50000,
      "instructions": "Enter your 12-digit personal Rocket account number."
    },
    {
      "id": 4,
      "name": "Bank Transfer",
      "code": "bank",
      "account_type": "Bank",
      "icon": "https://chinchins.live/assets/images/bank.png",
      "icon_url": "https://chinchins.live/assets/images/bank.png",
      "min_withdraw": 500,
      "max_withdraw": 100000,
      "instructions": "Enter Bank Name, Branch, Account Holder Name & Account Number."
    }
  ],
  "beans_balance": 5000,
  "coins_balance": 15120,
  "config": {
    "is_withdraw_enabled": true,
    "min_withdraw_coins": 1000,
    "max_withdraw_coins": 100000,
    "commission_percent": 5.0,
    "seller_commission_percent": 2.5,
    "rate_coins": 100,
    "rate_bdt": 10.0,
    "rate_per_bdt": 10.0,
    "min_withdraw_bdt": 100.0,
    "max_withdraw_bdt": 10000.0,
    "notice": "Withdrawals are processed manually via bKash, Nagad, and Rocket within 1-24 hours.",
    "rate_text": "100 Coins = ৳10.00 BDT (1 BDT = 10 Coins)"
  }
}
```

---

## 2. 🏪 Seller / Reseller Withdrawal APIs

### 2.1 Submit Seller Withdrawal Request
- **Endpoint:** `POST /api/seller/withdraw`
- **Aliases:** `POST /api/reseller/withdraw`
- **Headers:** `Authorization: Bearer <SELLER_TOKEN>`

#### Request Payload:
```json
{
  "coins_amount": 10000,
  "payment_method": "bKash Agent",
  "account_number": "017XXXXXXXX",
  "account_name": "Rahman Gems Agency",
  "notes": "Weekly reseller payout"
}
```

#### Success Response (`201 Created`):
```json
{
  "status": true,
  "success": true,
  "message": "Withdrawal request of 10,000 coins (৳975.00) submitted successfully! Pending admin payout.",
  "data": {
    "withdrawal_id": 5,
    "coins_amount": 10000,
    "gross_bdt": 1000.0,
    "commission_percentage": 2.5,
    "commission_amount": 25.0,
    "net_bdt": 975.0,
    "payment_method": "bKash Agent",
    "account_number": "017XXXXXXXX",
    "status": "pending",
    "reseller_remaining_coins": 45000,
    "created_at": "2026-09-26T10:14:57.000000Z"
  }
}
```

### 2.2 Seller Withdrawal History
- **Endpoint:** `GET /api/seller/withdraw-history`
- **Aliases:** `GET /api/reseller/withdraw-history`
- **Headers:** `Authorization: Bearer <SELLER_TOKEN>`

---

## 3. 🎙️ Dynamic Party Room Tags API (Zero Hardcode)

- **Endpoint:** `GET /api/party/tags`
- **Aliases:** `GET /api/party-rooms/tags`, `GET /api/party-room/tags`
- **Authentication:** Public / Optional Bearer token

#### Success Response (`200 OK`):
```json
{
  "status": true,
  "success": true,
  "message": "Active party tags retrieved successfully from database.",
  "data": [
    { "id": 1, "name": "Singing", "slug": "singing", "icon": "🎤", "color": "#ec4899" },
    { "id": 2, "name": "Gaming", "slug": "gaming", "icon": "🎮", "color": "#8b5cf6" },
    { "id": 3, "name": "Dating", "slug": "dating", "icon": "❤️", "color": "#ef4444" },
    { "id": 4, "name": "Chat", "slug": "chat", "icon": "💬", "color": "#3b82f6" },
    { "id": 5, "name": "Music", "slug": "music", "icon": "🎵", "color": "#10b981" },
    { "id": 6, "name": "Friends", "slug": "friends", "icon": "👥", "color": "#f59e0b" },
    { "id": 7, "name": "Poetry", "slug": "poetry", "icon": "📖", "color": "#06b6d4" },
    { "id": 8, "name": "Hangout", "slug": "hangout", "icon": "✨", "color": "#6366f1" }
  ]
}
```

---

## 4. 📞 Real-Time VoIP Signal & Push (Host Live-Streaming Fix)

When a 1-on-1 call is placed to a host:
1. **Real-Time WebSocket Signal:**
   - Broadcasts to channel: `private-user.{host_id}`
   - Event names: `call.incoming` and `private_call.incoming`
   - Client listens on:
     ```dart
     LaravelEcho.instance.private('user.$hostId').listen('.call.incoming', (event) {
       // Display floating incoming call dialog overlay on top of active live stream!
     });
     ```

2. **High-Priority VoIP FCM Data Push:**
   - Both FCM HTTP v1 and Legacy gateways configured with:
     ```json
     {
       "priority": "high",
       "content_available": true,
       "android": {
         "priority": "HIGH",
         "ttl": "45s",
         "notification": {
           "channel_id": "chinchins_call_channel",
           "sound": "call_ringtone"
         }
       },
       "apns": {
         "headers": {
           "apns-priority": "10",
           "apns-push-type": "voip"
         },
         "payload": {
           "aps": {
             "content-available": 1,
             "sound": "call_ringtone.caf"
           }
         }
       },
       "data": {
         "action": "INCOMING_CALL",
         "is_call": "true",
         "content_available": "true",
         "priority": "high",
         "call_id": "123",
         "caller_id": "45",
         "caller_name": "Nazmul",
         "channel_name": "call_video_45_88_..."
       }
     }
     ```
   - **`content_available: true` / `content-available: 1`:** Wakes Flutter FCM background / foreground message handlers immediately while the host is live streaming.

---

## 5. 🎛️ Admin Panel Sidebar & Commission Setup

### Sidebar Navigation:
- **Menu:** **Withdraw** (icon: `fa-money-bill-transfer`, color: `#10b981`)
  1. **User Withdraw Request** (`/admin/withdrawals`): Lists all user cashout requests with pending badge count. Admin can Approve (with Transaction ID) or Reject (with auto-refund).
  2. **Seller Withdraw** (`/admin/resellers/withdrawals`): Lists all reseller/seller withdrawal requests with pending badge count.
  3. **Withdraw Commission Setup** (`/admin/withdrawals/settings`):
     - **User Withdrawal Commission (%):** e.g. `5.0%` (or `0%` for free cashout).
     - **Seller / Reseller Commission (%):** e.g. `2.5%` (or `0%` for free cashout).
     - **Min/Max Limits:** e.g. `1,000` to `100,000` Coins/Beans.
     - **Exchange Rate:** e.g. `100 Coins = ৳10.00 BDT`.
     - **Payment Gateways:** Toggle bKash, Nagad, Rocket, Bank.
