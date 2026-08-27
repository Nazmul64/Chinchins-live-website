# 📞 Chinchins Live — WebRTC Audio & Video Calling and 50/50 Revenue Sharing API Documentation

Welcome to the complete RESTful backend specification for **WebRTC Audio & Video Calling**, **New User Free Trial Calling (5s / 10s / 30s / 60s)**, **Random Female Host Matching**, **Real-Time Coin Billing**, and **50/50 Revenue Sharing (Host Earnings vs Admin Revenue)**.

This documentation is created specifically for **Mobile App Developers (Flutter / React Native / Android / iOS)** to easily integrate call signaling, free trials, interval coin billing, and low balance top-up prompts.

---

## 📑 Table of Contents
1. [Calling Architecture & Lifecycle](#-calling-architecture--lifecycle)
2. [Authentication & Request Headers](#-authentication--request-headers)
3. [Summary of Endpoints](#-summary-of-endpoints)
4. [API 1: Get Call Settings & User Free Trial Status](#-1-get-call-settings--rates-api)
5. [API 2: Random Match Online Female Host](#-2-random-match-online-female-host-api)
6. [API 3: Initiate Audio or Video Call (Free Trial Supported)](#-3-initiate-call-api)
7. [API 4: Connect / Start Call](#-4-connect--start-call-api)
8. [API 5: In-Call Pulse Deduction & Low Balance Deposit Prompt](#-5-in-call-pulse-deduction--deposit-prompt-api)
9. [API 6: End Call & Finalize Ledger](#-6-end-call-api)
10. [API 7: User Call History](#-7-user-call-history-api)
11. [50/50 Revenue Sharing Formula](#-5050-revenue-sharing-formula)
12. [Mobile App WebRTC Integration Flow (Step-by-Step)](#-mobile-app-webrtc-integration-flow)

---

## 🔄 Calling Architecture & Lifecycle

```
[ New User Registers or Calls ]
               │
               ▼
[ 1. Check Free Trial Eligibility ]
   │
   ├─► Eligible: Initiates call with `is_free_trial: true` (e.g. 10 seconds free).
   │             Wallet balance is NOT required to start.
   │
   └─► Not Eligible / Normal Call: Checks wallet balance (`coins >= rate_per_minute`).
               │
               ▼
[ 2. Connect WebRTC Media Stream (VPS / Stun-Turn) ]
   │  Calls `POST /api/call/start`
               │
               ▼
[ 3. In-Call Pulse (Every 10s / 30s / 60s) `POST /api/call/deduct-interval` ]
   │
   ├─► Free Trial Active (0 - 10s): 0 coins deducted. Returns `free_seconds_remaining`.
   │
   └─► Free Trial Expired / Paid Call:
         │
         ├─► Caller has Coins:
         │     • 100 coins deducted from Caller
         │     • 50 coins (50%) credited to Host's Wallet (Female User)
         │     • 50 coins (50%) credited to Admin Platform Revenue
         │
         └─► Caller has 0 Coins / Low Balance:
               • Server returns `code: "LOW_BALANCE_DEPOSIT_REQUIRED"`
               • App automatically pauses/terminates call
               • App displays "Please Deposit / Recharge Coins Now" popup
               • Redirects user to Buy Coins / Deposit screen (`/deposit` or Coin Packages)
               │
               ▼
[ 4. Call Ends `POST /api/call/end` ]
   │  Calculates duration, updates status to 'ended', and logs call ledger.
```

---

## 🔑 Authentication & Request Headers

### Header Format
```http
Authorization: Bearer <SANCTUM_TOKEN>
Accept: application/json
Content-Type: application/json
```
*(Fallback: Supports `X-User-Id: <ID>` or `user_id=<ID>` query/body param).*

---

## 🚀 Summary of Endpoints

| Method | Endpoint | Description | Aliases |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/call/config` | Get calling rates (video/audio), free trial duration, host earning %, and user eligibility. | `/api/call/settings` |
| `POST` | `/api/call/random-match` | Find an active online female host with preference filters. | `/api/call/match`, `GET /api/call/match` |
| `POST` | `/api/call/initiate` | Start a call. If user has free trial, initiates with 0 coins. | — |
| `POST` | `/api/call/start` | Mark call as connected when receiver answers. | `/api/call/connect` |
| `POST` | `/api/call/deduct-interval` | Heartbeat pulse billing. Deducts coins, credits 50% to host, and triggers deposit prompt on low balance. | `/api/call/pulse` |
| `POST` | `/api/call/end` | End call and finalize total duration and ledger. | — |
| `GET` | `/api/call/history` | Get user's outgoing and incoming call logs. | — |

---

## ⚙️ 1. Get Call Settings & Rates API

Returns the dynamic pricing, free trial settings, and user's eligibility.

### **Endpoint**
`GET /api/call/config` *(Alias: `/api/call/settings`)*

### **Success Response (200 OK)**
```json
{
  "status": true,
  "message": "Call settings and rates retrieved successfully.",
  "data": {
    "is_call_enabled": true,
    "is_free_call_enabled": true,
    "free_call_duration_seconds": 10,
    "free_calls_per_user": 1,
    "video_call_rate_per_minute": 100,
    "audio_call_rate_per_minute": 60,
    "host_earning_percent": 50.0,
    "admin_commission_percent": 50.0,
    "video_split": {
      "total_rate": 100,
      "host_receives": 50,
      "admin_revenue": 50
    },
    "audio_split": {
      "total_rate": 60,
      "host_receives": 30,
      "admin_revenue": 30
    },
    "user": {
      "user_id": 1,
      "account_id": "1000000001",
      "display_name": "Rahim Khan",
      "gender": "male",
      "coins": 45000,
      "formatted_coins": "45,000 Coins",
      "free_calls_used": 0,
      "free_calls_remaining": 1,
      "is_eligible_for_free_call": true,
      "free_trial_duration_seconds": 10,
      "can_make_video_call": true,
      "can_make_audio_call": true,
      "max_video_minutes": 450,
      "max_audio_minutes": 750
    }
  }
}
```

---

## 🎲 2. Random Match Online Female Host API

Finds an active online host (Female) for one-tap video or audio matching.

### **Endpoint**
`POST /api/call/random-match` *(Aliases: `/api/call/match`, `GET /api/call/match`)*

### **Request Body (JSON)**
```json
{
  "call_type": "video",
  "gender": "female",
  "auto_initiate": false
}
```

### **Success Response (200 OK)**
```json
{
  "status": true,
  "message": "Random match found successfully.",
  "data": {
    "matched_user": {
      "id": 2,
      "account_id": "602281635",
      "display_name": "Ayeena04",
      "gender": "female",
      "avatar_url": "https://images.unsplash.com/photo-1534528741775-53994a69daeb",
      "cover_photo_url": "https://images.unsplash.com/photo-1534528741775-53994a69daeb",
      "country": "Bangladesh",
      "city": "Dhaka",
      "video_call_rate": 100,
      "audio_call_rate": 60,
      "introduction": "Sweet girl looking for friendly chat ✨",
      "tags": ["Live video", "Singing", "Friendly"]
    },
    "caller": {
      "coins": 45000,
      "is_eligible_for_free_call": true,
      "free_trial_duration_seconds": 10
    },
    "call_session": null
  }
}
```

---

## 📞 3. Initiate Call API

Initiates an Audio or Video call between caller and receiver. If the caller has free trial available, the call connects even if the user has **0 coins**!

### **Endpoint**
`POST /api/call/initiate`

### **Request Body (JSON)**
```json
{
  "receiver_id": 2,
  "call_type": "video"
}
```

### **Success Response (Free Trial - 200 OK)**
```json
{
  "status": true,
  "message": "Free trial call initiated! You have 10 seconds of free calling.",
  "data": {
    "call_id": 1,
    "channel_name": "call_video_1_2_1787851605_DPUb",
    "call_type": "video",
    "rate_per_minute": 100,
    "is_free_trial": true,
    "free_duration_seconds": 10,
    "caller_coins": 0,
    "max_call_minutes": 0,
    "max_call_seconds": 10,
    "receiver": {
      "id": 2,
      "account_id": "602281635",
      "name": "Ayeena04",
      "gender": "female",
      "avatar": "https://images.unsplash.com/photo-1534528741775-53994a69daeb"
    }
  }
}
```

### **Error Response: Insufficient Coins & No Free Trial (402 Payment Required)**
```json
{
  "status": false,
  "code": "LOW_BALANCE_DEPOSIT_REQUIRED",
  "message": "Insufficient coin balance. You need at least 100 coins for 1 minute of video call. Your balance is 0 coins.",
  "current_coins": 0,
  "required_coins": 100,
  "is_low_balance": true,
  "redirect_to_deposit": true,
  "deposit_url": "/deposit"
}
```

---

## 🟢 4. Connect / Start Call API

Call this endpoint as soon as the receiver accepts the call and WebRTC negotiation begins.

### **Endpoint**
`POST /api/call/start` *(Alias: `/api/call/connect`)*

### **Request Body (JSON)**
```json
{
  "call_id": 1
}
```

### **Success Response (200 OK)**
```json
{
  "status": true,
  "message": "Call connected successfully.",
  "data": {
    "call_id": 1,
    "channel_name": "call_video_1_2_1787851605_DPUb",
    "call_type": "video",
    "status": "connected",
    "started_at": "2026-08-27T17:29:48Z",
    "rate_per_minute": 100,
    "is_free_trial": true,
    "free_duration_seconds": 10
  }
}
```

---

## 💓 5. In-Call Pulse Deduction & Deposit Prompt API

During the active call, the mobile app sends a heartbeat request (e.g. every 10s or 60s, or when the free trial timer expires).

### **Endpoint**
`POST /api/call/deduct-interval` *(Alias: `/api/call/pulse`)*

### **Request Body (JSON)**
```json
{
  "call_id": 1,
  "elapsed_seconds": 15,
  "coins": 100
}
```

### **State 1: Free Trial Active (200 OK)**
```json
{
  "status": true,
  "is_free_trial": true,
  "free_seconds_remaining": 5,
  "message": "Free trial active (5s remaining).",
  "data": {
    "current_coins": 0,
    "coins_deducted": 0,
    "can_continue": true,
    "should_terminate_call": false
  }
}
```

### **State 2: Free Trial Expired & User Has Coins (50/50 Split - 200 OK)**
```json
{
  "status": true,
  "message": "Deducted 100 coins. Host earned 50 coins.",
  "data": {
    "current_coins": 44900,
    "coins_deducted": 100,
    "host_earned_coins": 50,
    "admin_revenue_coins": 50,
    "total_call_coins_deducted": 100,
    "can_continue": true,
    "should_terminate_call": false
  }
}
```

### **State 3: Free Trial Expired & User Has 0 Coins (Deposit Required - 402 Payment Required)**
```json
{
  "status": false,
  "code": "LOW_BALANCE_DEPOSIT_REQUIRED",
  "message": "Your balance is insufficient to continue calling. Please deposit/recharge coins now.",
  "current_coins": 0,
  "required_coins": 100,
  "should_terminate_call": true,
  "redirect_to_deposit": true,
  "deposit_url": "/deposit",
  "data": {
    "caller_id": 4,
    "call_id": 1,
    "current_coins": 0
  }
}
```
> 💡 **App Action**: When receiving `LOW_BALANCE_DEPOSIT_REQUIRED`, the mobile app must immediately stop the media stream and display a dialogue: **"Your free trial has ended. Recharge coins to talk with [Host Name]!"** and navigate to the Deposit/Coin Packages screen.

---

## 🛑 6. End Call API

Called when either user hangs up or the call terminates.

### **Endpoint**
`POST /api/call/end`

### **Request Body (JSON)**
```json
{
  "call_id": 1,
  "duration_seconds": 60
}
```

### **Success Response (200 OK)**
```json
{
  "status": true,
  "message": "Call session ended successfully.",
  "data": {
    "call_id": 1,
    "call_type": "video",
    "duration_seconds": 60,
    "duration_formatted": "01:00",
    "coins_deducted": 100,
    "host_earned_coins": 50,
    "admin_revenue_coins": 50,
    "caller_remaining_coins": 44900,
    "partner": {
      "id": 2,
      "name": "Ayeena04",
      "avatar": "https://images.unsplash.com/photo-1534528741775-53994a69daeb"
    }
  }
}
```

---

## 📜 7. User Call History API

Returns the list of all past calls made or received by the user.

### **Endpoint**
`GET /api/call/history`

### **Success Response (200 OK)**
```json
{
  "status": true,
  "message": "Call history retrieved successfully.",
  "data": [
    {
      "id": 1,
      "call_type": "video",
      "is_outgoing": true,
      "status": "ended",
      "duration_seconds": 60,
      "formatted_duration": "01:00",
      "is_free_trial": false,
      "coins_spent": 100,
      "coins_earned": 0,
      "created_at": "2026-08-27T17:26:45Z",
      "partner": {
        "id": 2,
        "account_id": "602281635",
        "display_name": "Ayeena04",
        "avatar_url": "https://images.unsplash.com/photo-1534528741775-53994a69daeb",
        "gender": "female"
      }
    }
  ],
  "current_coins": 44900,
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "total": 1
  }
}
```

---

## 💰 50/50 Revenue Sharing Formula

When a user is billed $C$ coins per minute:

1. **Host Share (Female User)**:
   $$\text{Host Earned Coins} = \text{round}\left(C \times \frac{\text{Host \%}}{100}\right)$$
   *Example: $100 \times 50\% = \mathbf{50 \text{ coins}}$ credited to Host Wallet.*

2. **Admin Platform Revenue**:
   $$\text{Admin Revenue Coins} = C - \text{Host Earned Coins}$$
   *Example: $100 - 50 = \mathbf{50 \text{ coins}}$ credited to Admin Profit Ledger.*

---

## 📱 Mobile App WebRTC Integration Flow

1. **On Registration / App Launch**:
   - Query `GET /api/call/config` to check `is_eligible_for_free_call` and `free_trial_duration_seconds`.
2. **Matching / Dialing Screen**:
   - User taps "Quick Match" or clicks "Call" on a female host profile.
   - App calls `POST /api/call/initiate`.
   - If free trial is active, app sets a local timer of `free_duration_seconds` (e.g. 10s).
3. **During Call**:
   - Connect WebRTC audio/video tracks using `channel_name`.
   - Send pulse `POST /api/call/deduct-interval` every 10 seconds.
   - If response has `code: "LOW_BALANCE_DEPOSIT_REQUIRED"`, stop WebRTC connection and show the **"Recharge Coins / Deposit Now"** bottom sheet.
4. **Call End**:
   - Call `POST /api/call/end` and show summary dialog.
