# 📞 Chinchins Live — WebRTC Audio & Video Calling, Continuous Ringing, & 50/50 Revenue Sharing API Documentation

Welcome to the complete, production-ready RESTful backend specification for **Audio & Video Calling**, **Continuous Device Ringing & Incoming Call Signaling**, **Call Receive (রিসিভ বাটন) & Decline Actions**, **New User Free Trial Calling (5s / 10s / 30s / 60s)**, **Real-Time Per-Minute Coin Billing (100 coins/min)**, and **50/50 Revenue Sharing (Host Earnings vs Admin Platform Revenue)**.

This documentation is crafted specifically for **Mobile App Developers (Flutter / React Native / Kotlin Android / Swift iOS)** to implement seamless dialing, continuous ringing, one-tap call answering, heartbeat coin billing, and low balance top-up prompts.

---

## 📑 Table of Contents
1. [Calling Architecture & WebRTC Lifecycle Diagram](#-calling-architecture--lifecycle)
2. [Authentication & Request Headers](#-authentication--request-headers)
3. [Summary of All Endpoints](#-summary-of-all-endpoints)
4. [API 1: Get Call Settings, Rates & Free Trial Status](#-1-get-call-settings--rates-api)
5. [API 2: Random Match Online Female Host](#-2-random-match-online-female-host-api)
6. [API 3: Initiate Direct Call (Audio / Video, Starts Ringing)](#-3-initiate-call-api)
7. [API 4: Check Incoming Call (For Receiver Device Ringing)](#-4-check-incoming-call-api-receiver-app)
8. [API 5: Call Status Polling & Ringing Sync](#-5-call-status-polling--sync-api)
9. [API 6: Confirm Ringing State](#-6-confirm-ringing-state-api)
10. [API 7: Accept / Receive Call (রিসিভ বাটন প্রেস)](#-7-accept--receive-call-api)
11. [API 8: Reject / Decline Call](#-8-reject--decline-call-api)
12. [API 9: Cancel Call (By Caller)](#-9-cancel-call-api)
13. [API 10: WebRTC ICE Servers Configuration (STUN / TURN)](#-10-webrtc-ice-servers-api)
14. [API 11: WebRTC Signaling Send (SDP Offer / Answer / ICE Candidates)](#-11-webrtc-signaling-send-api)
15. [API 12: WebRTC Signaling Receive & Poll](#-12-webrtc-signaling-receive--poll-api)
16. [API 13: In-Call Real-Time Coin Billing (100 coins/min, Per-Second Auto Calculation, 50/50 Split)](#-13-in-call-pulse-deduction--deposit-prompt-api)
17. [API 14: End Call & Finalize Ledger](#-14-end-call-api)
18. [API 15: User Call History](#-15-user-call-history-api)
19. [💰 100 Coins/Min Billing Formula & 50/50 Revenue Split](#-5050-revenue-sharing-formula)
20. [📱 Complete Flutter WebRTC Mobile Implementation Guide](#-flutter-webrtc-implementation-guide)

---

## 🔄 Calling Architecture & WebRTC Lifecycle

```
[ Caller Dials Host / Taps "Audio" or "Video Call" ]
               │
               ▼
[ 1. POST /api/call/initiate ]
       │ • Creates CallSession with status: "ringing"
       │ • Assigns WebRTC channel_name
       │ • Rate: 100 coins/minute (1.67 coins/sec)
       ▼
[ 2. Continuous Ringing Loop ]
       │
       ├─► Caller Device: Plays continuous outgoing dial tone
       │                  Polls `GET /api/call/status/{call_id}` every 1-2s
       │
       └─► Receiver Device: Detects incoming call via `GET /api/call/incoming` or FCM Push
                            Plays continuous incoming ringtone!
                            Shows Incoming Call UI with "Accept / Receive (রিসিভ)" and "Decline" buttons
               │
               ├─────────────────────────────────────────┬────────────────────────────────────────┐
               ▼                                         ▼                                        ▼
   [ Receiver Clicks "Accept" (রিসিভ) ]       [ Receiver Clicks "Decline" ]            [ Caller Taps "Cancel" ]
   POST /api/call/accept                      POST /api/call/reject                    POST /api/call/cancel
   • Status -> "connected"                    • Status -> "rejected"                   • Status -> "cancelled"
   • Stops Ringtone on both devices           • Stops Ringtone                         • Stops Ringtone on receiver
   • Starts WebRTC SDP & ICE exchange         • Caller shows "Call Declined"           • Screen Closes
   • Starts Call Duration Timer               • Screen Closes
               │
               ▼
   [ 3. WebRTC Signaling via REST ]
   • Caller creates SDP Offer ──► POST /api/call/signal/send (type: 'offer')
   • Receiver fetches Offer   ──► GET  /api/call/signal/receive
   • Receiver sends SDP Answer──► POST /api/call/signal/send (type: 'answer')
   • Caller fetches Answer    ──► GET  /api/call/signal/receive
   • Both exchange ICE candidates via `/api/call/signal/send` & `/api/call/signal/receive`
   • Peer-to-Peer Audio/Video Media Stream is Live!
               │
               ▼
   [ 4. Active Call Billing: Per-Minute / Per-Second Heartbeat ]
   POST /api/call/deduct-interval
               │
               ├─► Free Trial Active: 0 coins deducted. Returns `free_seconds_remaining`.
               │
               └─► Paid Call (100 coins/minute = ~1.67 coins/sec):
                     │
                     ├─► Caller has Coins (>= 100 coins / interval):
                     │     • Deducted from Caller Wallet
                     │     • 50% credited to Host's Wallet (Female User)
                     │     • 50% credited to Admin Platform Revenue
                     │     • WebRTC stream continues seamlessly!
                     │
                     └─► Caller has 0 Coins / Low Balance:
                           • Returns code: "LOW_BALANCE_DEPOSIT_REQUIRED"
                           • App closes media stream & displays "Recharge Coins Now" popup
                           • Navigates user to Deposit / Coin Packages Screen
               │
               ▼
   [ 5. Call Ends: POST /api/call/end ]
   • Status -> "ended"
   • WebRTC PeerConnection closed
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
| `GET` | `/api/call/config` | Get calling rates (100 coins/min), free trial settings, 50/50 split, and user balance. | `/api/call/settings` |
| `POST` | `/api/call/random-match` | Find an active online host (Female) for one-tap matching. | `/api/call/match`, `GET /api/call/match` |
| `POST` | `/api/call/initiate` | Caller initiates direct Audio/Video call. Sets status to `ringing`. | — |
| `GET` | `/api/call/incoming` | Receiver checks for active incoming ringing calls. | `/api/call/check-incoming`, `POST /api/call/check-incoming`, `/api/call/active-incoming` |
| `GET` | `/api/call/status/{id}` | Real-time status sync (detects when accepted, rejected, cancelled, or ended). | `POST /api/call/status`, `GET /api/call/status` |
| `POST` | `/api/call/ringing` | Receiver confirms device is actively ringing. | `/api/call/ring-ping` |
| `POST` | `/api/call/accept` | **Receiver clicks "Call Receive" (রিসিভ) button**. Connects call & starts WebRTC stream. | `/api/call/answer`, `/api/call/receive`, `/api/call/start`, `/api/call/connect` |
| `POST` | `/api/call/reject` | Receiver declines/rejects call. Stops ringing on caller device. | `/api/call/decline` |
| `POST` | `/api/call/cancel` | Caller cancels call before host answers. Stops ringing on receiver device. | — |
| `GET` | `/api/call/ice-servers` | Get STUN/TURN ICE servers list for WebRTC peer connection in Flutter. | — |
| `POST` | `/api/call/signal/send` | Send WebRTC SDP Offer, SDP Answer, or ICE Candidate. | `/api/call/send-signal`, `/api/call/signal` |
| `GET` | `/api/call/signal/receive` | Poll/Receive pending WebRTC signals (Offer/Answer/Candidates) for this user. | `/api/call/signals`, `/api/call/get-signals`, `POST /api/call/signals` |
| `POST` | `/api/call/signal/clear` | Clear / mark WebRTC signals as read. | `/api/call/clear-signals` |
| `POST` | `/api/call/deduct-interval` | In-call heartbeat billing (100 coins/min, 50% host share, 50% admin share). Triggers deposit prompt if balance is 0. | `/api/call/pulse`, `/api/call/bill` |
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

---

## 🧊 10. WebRTC ICE Servers API

Provides standard Google STUN/TURN server configurations for WebRTC PeerConnection creation in Flutter (`flutter_webrtc`).

### **Endpoint**
`GET /api/call/ice-servers`

### **Success Response (200 OK)**
```json
{
  "status": true,
  "message": "WebRTC ICE Servers retrieved successfully.",
  "data": {
    "iceServers": [
      {
        "urls": [
          "stun:stun.l.google.com:19302",
          "stun:stun1.l.google.com:19302",
          "stun:stun2.l.google.com:19302",
          "stun:stun3.l.google.com:19302",
          "stun:stun4.l.google.com:19302"
        ]
      },
      {
        "urls": "stun:global.stun.twilio.com:3478?transport=udp"
      }
    ]
  }
}
```

---

## 📡 11. WebRTC Signaling Send API

Used by Caller and Receiver to exchange SDP Offers, SDP Answers, and ICE Candidates via REST.

### **Endpoint**
`POST /api/call/signal/send` *(Aliases: `/api/call/send-signal`, `/api/call/signal`)*

### **Request Body (Sending SDP Offer)**
```json
{
  "call_id": 12,
  "channel_name": "call_video_1_2_1787851605_DPUb",
  "type": "offer",
  "payload": {
    "sdp": "v=0\r\no=- 423456789 2 IN IP4 127.0.0.1...",
    "type": "offer"
  }
}
```

### **Request Body (Sending SDP Answer)**
```json
{
  "call_id": 12,
  "channel_name": "call_video_1_2_1787851605_DPUb",
  "type": "answer",
  "payload": {
    "sdp": "v=0\r\no=- 987654321 2 IN IP4 127.0.0.1...",
    "type": "answer"
  }
}
```

### **Request Body (Sending ICE Candidate)**
```json
{
  "call_id": 12,
  "channel_name": "call_video_1_2_1787851605_DPUb",
  "type": "candidate",
  "payload": {
    "candidate": "candidate:842163049 1 udp 1677729535 192.168.1.100 56214 typ srflx...",
    "sdpMid": "0",
    "sdpMLineIndex": 0
  }
}
```

### **Success Response (200 OK)**
```json
{
  "status": true,
  "message": "Signal 'offer' sent successfully.",
  "data": {
    "signal_id": 105,
    "call_id": 12,
    "type": "offer",
    "sender_id": 1,
    "receiver_id": 2,
    "created_at": "2026-08-28T14:40:02Z"
  }
}
```

---

## 📬 12. WebRTC Signaling Receive & Poll API

The peer device polls this endpoint every 500ms to 1s to fetch incoming SDP Offers, Answers, and ICE candidates.

### **Endpoint**
`GET /api/call/signal/receive` *(Aliases: `GET /api/call/signals`, `POST /api/call/signals`)*

### **Query Parameters / Body**
- `call_id`: (optional) ID of the call session.
- `channel_name`: (optional) WebRTC channel name.
- `last_signal_id`: (optional) ID of last received signal to fetch only newer signals.
- `auto_read`: (optional, default `true`) Automatically mark fetched signals as read.

### **Success Response (200 OK)**
```json
{
  "status": true,
  "count": 2,
  "data": [
    {
      "id": 106,
      "call_id": 12,
      "channel_name": "call_video_1_2_1787851605_DPUb",
      "sender_id": 2,
      "sender_name": "Ayeena04",
      "type": "answer",
      "payload": {
        "sdp": "v=0\r\no=- 987654321 2 IN IP4...",
        "type": "answer"
      },
      "created_at": "2026-08-28T14:40:04Z"
    },
    {
      "id": 107,
      "call_id": 12,
      "channel_name": "call_video_1_2_1787851605_DPUb",
      "sender_id": 2,
      "sender_name": "Ayeena04",
      "type": "candidate",
      "payload": {
        "candidate": "candidate:842163049 1 udp 1677729535 192.168.1.100 56214 typ srflx...",
        "sdpMid": "0",
        "sdpMLineIndex": 0
      },
      "created_at": "2026-08-28T14:40:05Z"
    }
  ]
}
```

---

## 💓 13. In-Call Pulse Deduction & Deposit Prompt API

During the active call, the mobile app sends a heartbeat request every 60 seconds (or custom interval e.g. 10s, 30s) to perform real-time coin deduction (100 coins/min = ~1.67 coins/sec) and 50/50 revenue sharing.

### **Endpoint**
`POST /api/call/deduct-interval` *(Aliases: `/api/call/pulse`, `/api/call/bill`)*

### **Request Body (Option A: 60-Second Interval)**
```json
{
  "call_id": 12,
  "elapsed_seconds": 60,
  "coins": 100
}
```

### **Request Body (Option B: Dynamic Per-Second / Chunk Calculation)**
```json
{
  "call_id": 12,
  "interval_seconds": 30
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
    "rate_per_minute": 100,
    "rate_per_second": 1.6667,
    "can_continue": true,
    "should_terminate_call": false
  }
}
```

### **State 2: Paid Call — Coins Deducted (50/50 Split - 200 OK)**
```json
{
  "status": true,
  "message": "Deducted 100 coins (Rate: 100 coins/min, 1.6667 coins/sec). Host earned 50 coins (50%). Admin revenue 50 coins (50%).",
  "data": {
    "current_coins": 44900,
    "coins_deducted": 100,
    "host_earned_coins": 50,
    "admin_revenue_coins": 50,
    "total_call_coins_deducted": 100,
    "rate_per_minute": 100,
    "rate_per_second": 1.6667,
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
  "rate_per_minute": 100,
  "rate_per_second": 1.6667,
  "should_terminate_call": true,
  "redirect_to_deposit": true,
  "deposit_url": "/deposit",
  "data": {
    "caller_id": 1,
    "call_id": 12,
    "current_coins": 0,
    "required_coins": 100
  }
}
```
> 💡 **App Action**: When receiving `LOW_BALANCE_DEPOSIT_REQUIRED`, the mobile app must immediately stop video streaming, display the popup: **"Your free trial has ended / Insufficient coins. Recharge coins to talk with [Host Name]!"** and navigate to the Deposit screen (`/deposit` or Coin Packages).

---

## 📴 14. End Call API

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

## 📜 15. User Call History API

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

## 💰 100 Coins/Min Billing Formula & 50/50 Revenue Split

1. **Per-Minute Rate**:
   - $\text{Rate per Minute} = 100 \text{ coins}$
2. **Automatic Per-Second Rate Calculation**:
   - $\text{Rate per Second} = \frac{100}{60} = 1.66667 \text{ coins/second}$
   - For an interval of $T$ seconds: $\text{Coins to Deduct} = \text{round}\left(T \times \frac{100}{60}\right)$
   - Examples:
     - 60 seconds = **100 coins**
     - 30 seconds = **50 coins**
     - 10 seconds = **17 coins**

3. **Host 50% Share (Female User / Receiver)**:
   $$\text{Host Earned Coins} = \text{round}\left(\text{Coins Deducted} \times 50\%\right)$$
   *Example: 100 coins billed $\to \mathbf{50 \text{ coins}}$ credited to Host Wallet.*

4. **Admin 50% Platform Revenue**:
   $$\text{Admin Revenue Coins} = \text{Coins Deducted} - \text{Host Earned Coins}$$
   *Example: 100 coins billed $\to \mathbf{50 \text{ coins}}$ platform net revenue.*

---

## 📱 Flutter WebRTC Implementation Guide

### 1. Flutter WebRTC Call Initialization & SDP Exchange
```dart
// 1. Caller initiates call
final initRes = await http.post(
  Uri.parse('$baseUrl/api/call/initiate'),
  headers: authHeaders,
  body: jsonEncode({'receiver_id': targetUserId, 'call_type': 'video'}),
);
final callId = initRes['data']['call_id'];
final channelName = initRes['data']['channel_name'];

// 2. Fetch ICE Servers
final iceRes = await http.get(Uri.parse('$baseUrl/api/call/ice-servers'));
final configuration = {'iceServers': iceRes['data']['iceServers']};
RTCPeerConnection peerConnection = await createPeerConnection(configuration);

// 3. Create & Send SDP Offer
RTCSessionDescription offer = await peerConnection.createOffer();
await peerConnection.setLocalDescription(offer);

await http.post(
  Uri.parse('$baseUrl/api/call/signal/send'),
  headers: authHeaders,
  body: jsonEncode({
    'call_id': callId,
    'channel_name': channelName,
    'type': 'offer',
    'payload': {'sdp': offer.sdp, 'type': offer.type},
  }),
);

// 4. On ICE Candidate Generated
peerConnection.onIceCandidate = (RTCIceCandidate candidate) {
  http.post(
    Uri.parse('$baseUrl/api/call/signal/send'),
    headers: authHeaders,
    body: jsonEncode({
      'call_id': callId,
      'channel_name': channelName,
      'type': 'candidate',
      'payload': {
        'candidate': candidate.candidate,
        'sdpMid': candidate.sdpMid,
        'sdpMLineIndex': candidate.sdpMLineIndex,
      },
    }),
  );
};
```

### 2. Receiver Answering & Signaling Loop
```dart
// 1. Receiver taps "Receive" (রিসিভ)
await http.post(
  Uri.parse('$baseUrl/api/call/accept'),
  headers: authHeaders,
  body: jsonEncode({'call_id': callId}),
);

// 2. Poll for Signals (SDP Offer / Answer & ICE Candidates)
Timer.periodic(Duration(milliseconds: 750), (timer) async {
  final res = await http.get(
    Uri.parse('$baseUrl/api/call/signal/receive?call_id=$callId'),
    headers: authHeaders,
  );
  for (var signal in res['data']) {
    if (signal['type'] == 'offer') {
      await peerConnection.setRemoteDescription(
        RTCSessionDescription(signal['payload']['sdp'], 'offer'),
      );
      RTCSessionDescription answer = await peerConnection.createAnswer();
      await peerConnection.setLocalDescription(answer);
      await http.post(
        Uri.parse('$baseUrl/api/call/signal/send'),
        headers: authHeaders,
        body: jsonEncode({
          'call_id': callId,
          'type': 'answer',
          'payload': {'sdp': answer.sdp, 'type': answer.type},
        }),
      );
    } else if (signal['type'] == 'answer') {
      await peerConnection.setRemoteDescription(
        RTCSessionDescription(signal['payload']['sdp'], 'answer'),
      );
    } else if (signal['type'] == 'candidate') {
      await peerConnection.addCandidate(
        RTCIceCandidate(
          signal['payload']['candidate'],
          signal['payload']['sdpMid'],
          signal['payload']['sdpMLineIndex'],
        ),
      );
    }
  }
});
```

### 3. Active Call Heartbeat Coin Billing (100 coins/min)
```dart
// In-Call Timer: Runs every 60 seconds (or 10s intervals)
Timer.periodic(Duration(seconds: 60), (timer) async {
  final pulseRes = await http.post(
    Uri.parse('$baseUrl/api/call/deduct-interval'),
    headers: authHeaders,
    body: jsonEncode({'call_id': callId, 'elapsed_seconds': 60, 'coins': 100}),
  );

  if (pulseRes.statusCode == 402 || pulseRes['code'] == 'LOW_BALANCE_DEPOSIT_REQUIRED') {
    timer.cancel();
    // Stop WebRTC stream
    peerConnection.close();
    // Show Low Balance Popup / Navigate to Deposit Screen
    showRechargeCoinDialog();
  }
});
```
