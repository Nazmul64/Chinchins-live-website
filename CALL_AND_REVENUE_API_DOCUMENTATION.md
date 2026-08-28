# 📞 Chinchins Live — WebRTC Audio & Video Calling, Continuous Ringing, & 50/50 Revenue Sharing API Documentation

Welcome to the complete, production-ready RESTful backend specification for **Audio & Video Calling**, **Continuous Device Ringing & Incoming Call Signaling**, **Call Receive (রিসিভ বাটন) & Decline Actions**, **New User Free Trial Calling (5s / 10s / 30s / 60s)**, **Real-Time Per-Minute Coin Billing (100 coins/min)**, and **50/50 Revenue Sharing (Host Earnings vs Admin Platform Revenue)**.

This documentation is crafted specifically for **Mobile App Developers (Flutter / React Native / Kotlin Android / Swift iOS)** to implement seamless dialing, continuous ringing, one-tap call answering, heartbeat coin billing, and low balance top-up prompts.

---

## 📑 Table of Contents
1. [Calling Architecture & Lifecycle Diagram](#-calling-architecture--lifecycle)
2. [Authentication & Request Headers](#-authentication--request-headers)
3. [Summary of All Endpoints](#-summary-of-all-endpoints)
4. [API 1: Get Call Settings, Rates & Free Trial Status](#-1-get-call-settings--rates-api)
5. [API 2: Random Match Online Female Host](#-2-random-match-online-female-host-api)
6. [API 3: Initiate Call (Starts Ringing)](#-3-initiate-call-api)
7. [API 4: Check Incoming Call (For Receiver Device Ringing)](#-4-check-incoming-call-api-receiver-app)
8. [API 5: Call Status Polling & Ringing Sync](#-5-call-status-polling--sync-api)
9. [API 6: Confirm Ringing State](#-6-confirm-ringing-state-api)
10. [API 7: Accept / Receive Call (রিসিভ বাটন প্রেস)](#-7-accept--receive-call-api)
11. [API 8: Reject / Decline Call](#-8-reject--decline-call-api)
12. [API 9: Cancel Call (By Caller)](#-9-cancel-call-api)
13. [API 10: In-Call Pulse Deduction & Low Balance Top-Up Prompt (100 coins/min, 50/50 Split)](#-10-in-call-pulse-deduction--deposit-prompt-api)
14. [API 11: End Call & Finalize Ledger](#-11-end-call-api)
15. [API 12: User Call History](#-12-user-call-history-api)
16. [💰 50/50 Revenue Sharing Formula](#-5050-revenue-sharing-formula)
17. [📱 Complete Mobile App Implementation & Ringing Flow Guide](#-mobile-app-implementation-guide)

---

## 🔄 Calling Architecture & Lifecycle

```
[ Caller Dials Host / Taps "Call" ]
               │
               ▼
[ 1. POST /api/call/initiate ]
       │ • Creates CallSession with status: "ringing"
       │ • Checks Free Trial eligibility or coin balance
       ▼
[ 2. Continuous Ringing Loop ]
       │
       ├─► Caller Device: Plays continuous outgoing dial tone
       │                  Polls `GET /api/call/status/{call_id}` every 1-2s
       │
       └─► Receiver Device: Detects call via `GET /api/call/incoming` or Push Notification
                            Plays continuous incoming ringtone in an infinite loop!
                            Shows Incoming Call UI with "Accept / Receive (রিসিভ)" and "Decline" buttons
               │
               ├─────────────────────────────────────────┬────────────────────────────────────────┐
               ▼                                         ▼                                        ▼
   [ Receiver Clicks "Accept" (রিসিভ) ]       [ Receiver Clicks "Decline" ]            [ Caller Taps "Cancel" ]
   POST /api/call/accept                      POST /api/call/reject                    POST /api/call/cancel
   • Status -> "connected"                    • Status -> "rejected"                   • Status -> "cancelled"
   • Stops Ringtone on both devices           • Stops Ringtone                         • Stops Ringtone on receiver
   • Starts WebRTC Video/Audio Stream         • Caller shows "Call Declined"           • Screen Closes
   • Starts In-Call Duration Timer            • Screen Closes
               │
               ▼
   [ 3. Active Call: Pulse Deduction (Every 60s) ]
   POST /api/call/deduct-interval
               │
               ├─► Free Trial Active: 0 coins deducted. Returns `free_seconds_remaining`.
               │
               └─► Paid Call / Free Trial Expired:
                     │
                     ├─► Caller has Coins (>= 100 coins):
                     │     • 100 coins deducted from Caller
                     │     • 50 coins (50%) credited to Host's Wallet (Female User)
                     │     • 50 coins (50%) credited to Admin Platform Revenue
                     │     • Call continues seamlessly!
                     │
                     └─► Caller has 0 Coins / Low Balance:
                           • Returns code: "LOW_BALANCE_DEPOSIT_REQUIRED"
                           • App stops media stream & displays "Recharge Coins Now" popup
                           • Navigates user to Deposit / Coin Packages Screen
               │
               ▼
   [ 4. Call Ends: POST /api/call/end ]
   • Status -> "ended"
   • Records total duration & returns call summary ledger.
```

---

## 🔑 Authentication & Request Headers

### Header Format
```http
Authorization: Bearer <SANCTUM_TOKEN>
Accept: application/json
Content-Type: application/json
```
*(Mobile Fallback: Supports `X-User-Id: <ID>`, `X-Account-Id: <ACC_ID>`, or `user_id=<ID>` in request body / query params for maximum testing resilience).*

---

## 🚀 Summary of All Endpoints

| Method | Endpoint | Description | Aliases |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/call/config` | Get calling rates, free trial settings, 50/50 revenue split, and user balance/eligibility. | `/api/call/settings` |
| `POST` | `/api/call/random-match` | Find an active online host (Female) for one-tap matching. | `/api/call/match`, `GET /api/call/match` |
| `POST` | `/api/call/initiate` | Caller initiates call. Sets status to `ringing`. | — |
| `GET` | `/api/call/incoming` | Receiver checks for active incoming ringing calls. | `/api/call/check-incoming`, `POST /api/call/check-incoming`, `/api/call/active-incoming` |
| `GET` | `/api/call/status/{id}` | Real-time status sync (detects when accepted, rejected, cancelled, or ended). | `POST /api/call/status`, `GET /api/call/status` |
| `POST` | `/api/call/ringing` | Receiver confirms device is actively ringing. | `/api/call/ring-ping` |
| `POST` | `/api/call/accept` | **Receiver clicks "Call Receive" (রিসিভ) button**. Connects call & starts video/audio. | `/api/call/answer`, `/api/call/receive`, `/api/call/start`, `/api/call/connect` |
| `POST` | `/api/call/reject` | Receiver declines/rejects call. Stops ringing on caller device. | `/api/call/decline` |
| `POST` | `/api/call/cancel` | Caller cancels call before host answers. Stops ringing on receiver device. | — |
| `POST` | `/api/call/deduct-interval` | In-call heartbeat billing (100 coins/min: 50 coins to host, 50 coins to admin). Triggers deposit prompt if balance is 0. | `/api/call/pulse`, `/api/call/bill` |
| `POST` | `/api/call/end` | Either party hangs up. Finalizes duration and revenue summary. | `/api/call/finish`, `/api/call/hangup` |
| `GET` | `/api/call/history` | Get paginated list of calls made and received. | — |

---

## ⚙️ 1. Get Call Settings & Rates API

Returns dynamic pricing, free trial settings, host 50% earning share, and caller eligibility.

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
      "display_name": "Nazmul Hossain",
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

Finds an active online host for quick 1-on-1 video or audio matching.

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
      "introduction": "Sweet girl looking for honest talk ❤️",
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

## 📞 3. Initiate Call API (Starts Ringing)

Caller initiates call to a recipient. Sets initial call status to `ringing`.

### **Endpoint**
`POST /api/call/initiate`

### **Request Body (JSON)**
```json
{
  "receiver_id": 2,
  "call_type": "video"
}
```

### **Success Response (200 OK)**
```json
{
  "status": true,
  "message": "Call initiated! Ringing receiver...",
  "data": {
    "call_id": 12,
    "channel_name": "call_video_1_2_1787851605_DPUb",
    "call_type": "video",
    "status": "ringing",
    "rate_per_minute": 100,
    "is_free_trial": true,
    "free_duration_seconds": 10,
    "caller_coins": 45000,
    "max_call_minutes": 450,
    "max_call_seconds": 27000,
    "ring_timeout_seconds": 45,
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

## 🔔 4. Check Incoming Call API (Receiver App)

The receiver mobile app polls this endpoint (every 1-2 seconds when in foreground or triggered by FCM/Push) to detect incoming calls and trigger the **continuous ringing incoming call screen**.

### **Endpoint**
`GET /api/call/incoming` *(Aliases: `GET /api/call/check-incoming`, `POST /api/call/check-incoming`)*

### **Response (When Incoming Call is Ringing - 200 OK)**
```json
{
  "status": true,
  "has_incoming_call": true,
  "message": "Incoming call detected! Ring device.",
  "data": {
    "call_id": 12,
    "channel_name": "call_video_1_2_1787851605_DPUb",
    "call_type": "video",
    "status": "ringing",
    "is_free_trial": true,
    "free_duration_seconds": 10,
    "rate_per_minute": 100,
    "ring_elapsed_seconds": 3,
    "ring_timeout_seconds": 42,
    "caller": {
      "id": 1,
      "account_id": "1000000001",
      "name": "Rahim Khan",
      "avatar": "https://images.unsplash.com/photo-1535713875002-d1d0cf377fde",
      "gender": "male",
      "level": "Lv4"
    }
  }
}
```

### **Response (No Incoming Call - 200 OK)**
```json
{
  "status": true,
  "has_incoming_call": false,
  "message": "No active incoming calls.",
  "data": null
}
```

---

## 🔄 5. Call Status Polling & Sync API

Both Caller and Receiver apps poll this endpoint while ringing and during active call to synchronize call transitions (`ringing` ➡️ `connected` ➡️ `rejected` / `cancelled` / `ended`).

### **Endpoint**
`GET /api/call/status/{id}` *(Aliases: `POST /api/call/status`)*

### **Request Parameters / Body**
```json
{
  "call_id": 12
}
```

### **State A: While Ringing (200 OK)**
```json
{
  "status": true,
  "data": {
    "call_id": 12,
    "channel_name": "call_video_1_2_1787851605_DPUb",
    "call_type": "video",
    "status": "ringing",
    "is_active": false,
    "is_ringing": true,
    "is_terminated": false,
    "duration_seconds": 0,
    "duration_formatted": "00:00",
    "rate_per_minute": 100,
    "caller": {
      "id": 1,
      "name": "Rahim Khan",
      "coins": 45000
    },
    "receiver": {
      "id": 2,
      "name": "Ayeena04",
      "coins": 500
    }
  }
}
```

### **State B: When Accepted / Connected (200 OK)**
```json
{
  "status": true,
  "data": {
    "call_id": 12,
    "channel_name": "call_video_1_2_1787851605_DPUb",
    "call_type": "video",
    "status": "connected",
    "is_active": true,
    "is_ringing": false,
    "is_terminated": false,
    "started_at": "2026-08-28T14:40:00Z",
    "duration_seconds": 15,
    "duration_formatted": "00:15",
    "rate_per_minute": 100
  }
}
```

### **State C: When Declined / Cancelled / Ended (200 OK)**
```json
{
  "status": true,
  "data": {
    "call_id": 12,
    "status": "rejected",
    "is_active": false,
    "is_ringing": false,
    "is_terminated": true,
    "ended_at": "2026-08-28T14:40:10Z"
  }
}
```

---

## 📳 6. Confirm Ringing State API

Receiver device pings this to confirm it received the call and started playing ringtone.

### **Endpoint**
`POST /api/call/ringing` *(Alias: `/api/call/ring-ping`)*

### **Request Body (JSON)**
```json
{
  "call_id": 12
}
```

### **Success Response (200 OK)**
```json
{
  "status": true,
  "message": "Ringing state confirmed. Continue looping ringtone until answered or cancelled.",
  "data": {
    "call_id": 12,
    "status": "ringing"
  }
}
```

---

## 🟢 7. Accept / Receive Call API (রিসিভ বাটন প্রেস)

**Triggered when the Receiver presses the "Call Receive" / "Accept" (রিসিভ) button on their screen.**
- Sets `status` to `connected`.
- Stops ringing on both phones.
- Begins active audio/video stream.

### **Endpoint**
`POST /api/call/accept` *(Aliases: `/api/call/answer`, `/api/call/receive`, `/api/call/start`, `/api/call/connect`)*

### **Request Body (JSON)**
```json
{
  "call_id": 12
}
```

### **Success Response (200 OK)**
```json
{
  "status": true,
  "message": "Call accepted and connected successfully! Start audio/video media stream.",
  "data": {
    "call_id": 12,
    "channel_name": "call_video_1_2_1787851605_DPUb",
    "call_type": "video",
    "status": "connected",
    "started_at": "2026-08-28T14:40:00Z",
    "rate_per_minute": 100,
    "is_free_trial": true,
    "free_duration_seconds": 10,
    "caller": {
      "id": 1,
      "name": "Rahim Khan",
      "avatar": "https://images.unsplash.com/photo-1535713875002-d1d0cf377fde"
    },
    "receiver": {
      "id": 2,
      "name": "Ayeena04",
      "avatar": "https://images.unsplash.com/photo-1534528741775-53994a69daeb"
    }
  }
}
```

---

## 🛑 8. Reject / Decline Call API

Triggered when the Receiver taps the **"Decline" / "Reject"** button.

### **Endpoint**
`POST /api/call/reject` *(Alias: `/api/call/decline`)*

### **Request Body (JSON)**
```json
{
  "call_id": 12
}
```

### **Success Response (200 OK)**
```json
{
  "status": true,
  "message": "Call declined successfully. Ringing stopped.",
  "data": {
    "call_id": 12,
    "status": "rejected"
  }
}
```

---

## 🚫 9. Cancel Call API (By Caller)

Triggered when the Caller taps **"Cancel"** while waiting for host to answer.

### **Endpoint**
`POST /api/call/cancel`

### **Request Body (JSON)**
```json
{
  "call_id": 12
}
```

### **Success Response (200 OK)**
```json
{
  "status": true,
  "message": "Call cancelled by caller.",
  "data": {
    "call_id": 12,
    "status": "cancelled"
  }
}
```

---

## 💓 10. In-Call Pulse Deduction & Deposit Prompt API

During the active call, the mobile app sends a heartbeat request every 60 seconds (or when free trial timer expires) to perform real-time coin deduction and 50/50 revenue sharing.

### **Endpoint**
`POST /api/call/deduct-interval` *(Aliases: `/api/call/pulse`, `/api/call/bill`)*

### **Request Body (JSON)**
```json
{
  "call_id": 12,
  "elapsed_seconds": 60,
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

### **State 2: Paid Call — 100 Coins Deducted (50/50 Split - 200 OK)**
```json
{
  "status": true,
  "message": "Deducted 100 coins. Host earned 50 coins (50%). Admin revenue 50 coins (50%).",
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

### **State 3: Free Trial Ended & User Has 0 Coins (Deposit Required - 402 Payment Required)**
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
    "caller_id": 1,
    "call_id": 12,
    "current_coins": 0
  }
}
```
> 💡 **App Action**: When receiving `LOW_BALANCE_DEPOSIT_REQUIRED`, the mobile app must immediately stop video streaming, display the popup: **"Your free trial has ended / Insufficient coins. Recharge coins to talk with [Host Name]!"** and navigate to the Deposit screen (`/deposit` or Coin Packages).

---

## 📴 11. End Call API

Called when either user hangs up or the session terminates.

### **Endpoint**
`POST /api/call/end` *(Aliases: `/api/call/finish`, `/api/call/hangup`)*

### **Request Body (JSON)**
```json
{
  "call_id": 12,
  "duration_seconds": 120
}
```

### **Success Response (200 OK)**
```json
{
  "status": true,
  "message": "Call session ended successfully.",
  "data": {
    "call_id": 12,
    "call_type": "video",
    "duration_seconds": 120,
    "duration_formatted": "02:00",
    "coins_deducted": 200,
    "host_earned_coins": 100,
    "admin_revenue_coins": 100,
    "caller_remaining_coins": 44800,
    "partner": {
      "id": 2,
      "name": "Ayeena04",
      "avatar": "https://images.unsplash.com/photo-1534528741775-53994a69daeb"
    }
  }
}
```

---

## 📜 12. User Call History API

Returns list of past calls made or received by the user.

### **Endpoint**
`GET /api/call/history`

### **Success Response (200 OK)**
```json
{
  "status": true,
  "message": "Call history retrieved successfully.",
  "data": [
    {
      "id": 12,
      "call_type": "video",
      "is_outgoing": true,
      "status": "ended",
      "duration_seconds": 120,
      "formatted_duration": "02:00",
      "is_free_trial": false,
      "coins_spent": 200,
      "coins_earned": 0,
      "created_at": "2026-08-28T14:40:00Z",
      "partner": {
        "id": 2,
        "account_id": "602281635",
        "display_name": "Ayeena04",
        "avatar_url": "https://images.unsplash.com/photo-1534528741775-53994a69daeb",
        "gender": "female"
      }
    }
  ],
  "current_coins": 44800,
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "total": 1
  }
}
```

---

## 💰 50/50 Revenue Sharing Formula

When caller is billed $C$ coins (e.g. 100 coins per minute):

1. **Host Share (Female User / Receiver)**:
   $$\text{Host Earned Coins} = \text{round}\left(C \times \frac{\text{Host } \%}{100}\right)$$
   *Example: $100 \times 50\% = \mathbf{50 \text{ coins}}$ credited to Host Wallet.*

2. **Admin Platform Revenue**:
   $$\text{Admin Revenue Coins} = C - \text{Host Earned Coins}$$
   *Example: $100 - 50 = \mathbf{50 \text{ coins}}$ credited to Admin Profit Ledger.*

---

## 📱 Mobile App Implementation Guide

### 1. Continuous Ringing on Receiver Device
- In the mobile app foreground/background service, poll `GET /api/call/incoming` every 1.5 seconds (or trigger via FCM Data Push).
- When `has_incoming_call: true`:
  - Open the **Full Screen Incoming Call Activity / Screen**.
  - Play the custom ringtone sound with `loop = true` (infinite loop until user action).
  - Ping `POST /api/call/ringing` with `call_id`.
  - Start a local 45-second timer. If no action after 45s, stop ringtone and dismiss screen.

### 2. Receiver Taps "Call Receive" (রিসিভ বাটন)
- Stop the incoming ringtone immediately.
- Call `POST /api/call/accept` with `call_id`.
- Transition into the **Active Video Call Screen**.
- Join the WebRTC/Agora channel using `channel_name`.
- Start local call duration timer and pulse heartbeat.

### 3. Receiver Taps "Decline"
- Stop the incoming ringtone immediately.
- Call `POST /api/call/reject` with `call_id`.
- Close incoming call screen.

### 4. Caller Dialing Screen
- Caller initiates call via `POST /api/call/initiate`.
- Play outgoing dial tone sound with `loop = true`.
- Start polling `GET /api/call/status/{call_id}` every 1 second:
  - If `status == "connected"`: Stop dial tone, navigate into **Active Video Call Screen**, connect WebRTC/Agora stream!
  - If `status == "rejected"`: Stop dial tone, show toast **"Host declined the call"**, close screen.
  - If `status == "missed"`: Stop dial tone, show toast **"No answer"**, close screen.
  - If caller taps **"Cancel"**: Call `POST /api/call/cancel`, stop dial tone, close screen.

### 5. In-Call Heartbeat Billing
- Every 60 seconds (or after free trial duration), send `POST /api/call/deduct-interval`.
- If server returns HTTP 402 with `code: "LOW_BALANCE_DEPOSIT_REQUIRED"`, terminate media stream and show the **"Please Recharge Coins / Deposit Now"** bottom sheet.
