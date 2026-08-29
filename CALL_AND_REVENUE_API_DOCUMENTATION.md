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

## 💰 Core Financial & Billing Rules (অবশ্যই মনে রাখতে হবে)

> [!IMPORTANT]
> 1. **শুধুমাত্র কলারের (Caller) ব্যালেন্স থেকে কয়েন কাটবে**:
>    - যে ইউজার কল দিচ্ছে কেবল তার ব্যালেন্স চেক হবে এবং তার অ্যাকাউন্ট থেকেই **১ মিনিটে ১০০ কয়েন** (বা প্রতি সেকেন্ডে ১.৬৭ কয়েন) কাটা হবে।
> 2. **রিসিভার (Receiver / Female Host) সম্পূর্ণ ফ্রি**:
>    - যে ব্যক্তি কল রিসিভ করবে তার ব্যালেন্সে ০ কয়েন থাকলেও সে কল ধরতে পারবে। তার ব্যালেন্স থেকে কোনো টাকা বা কয়েন **কাটবে না**।
> 3. **৫০/৫০ রেভিনিউ শেয়ারিং (50/50 Revenue Split)**:
>    - কলারের থেকে ১০০ কয়েন কাটলে:
>      - **৫০ কয়েন (৫০%)** সরাসরি রিসিভারের (হোস্টের) ওয়ালেটে জমা হবে (Host Earning)।
>      - **৫০ কয়েন (৫০%)** প্ল্যাটফর্ম অ্যাডমিন রেভিনিউ হিসেবে জমা হবে (Admin Profit)।
> 4. **ক্রিস্টাল ক্লিয়ার অডিও ও ভিডিও (Audio & Video Clarity)**:
>    - **অডিও কলে**: দুই প্রান্তের ইউজারই একে অপরের কথা পরিষ্কার শুনতে পারবে।
>    - **ভিডিও কলে**: দুই প্রান্তের ইউজার একে অপরকে ক্যামেরায় লাইভ দেখতে পারবে এবং কথা পরিষ্কার শুনতে পারবে।

---

## 🔄 Calling Architecture & WebRTC Lifecycle

```
[ Caller Dials Host / Taps "Audio" or "Video Call" ]
               │
               ▼
[ 1. POST /api/call/initiate ]
       │ • Checks ONLY Caller's Coin Balance (Receiver requires 0 coins!)
       │ • Creates CallSession with status: "ringing"
       │ • Assigns WebRTC channel_name
       │ • Rate: 100 coins/minute (~1.67 coins/sec)
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
   [ 3. WebRTC VPS REST Signaling ]
   • Caller creates SDP Offer ──► POST /api/call/signal/send (type: 'offer')
   • Receiver fetches Offer   ──► GET  /api/call/signal/receive
   • Receiver sends SDP Answer──► POST /api/call/signal/send (type: 'answer')
   • Caller fetches Answer    ──► GET  /api/call/signal/receive
   • Both exchange ICE candidates via `/api/call/signal/send` & `/api/call/signal/receive`
   • Peer-to-Peer Audio & Video Streams are Live! (Both parties see & hear each other)
               │
               ▼
   [ 4. Active Call Billing: Pulse Heartbeat ]
   POST /api/call/deduct-interval
               │
               ├─► Free Trial Active: 0 coins deducted.
               │
               └─► Paid Call (100 coins/minute):
                     │
                     ├─► Caller has Coins (>= 100 coins):
                     │     • Deducted ONLY from Caller
                     │     • 50 coins (50%) credited to Host's Wallet (Female User)
                     │     • 50 coins (50%) credited to Admin Platform Revenue
                     │     • WebRTC stream continues seamlessly!
                     │
                     └─► Caller has 0 Coins / Low Balance:
                           • Returns code: "LOW_BALANCE_DEPOSIT_REQUIRED" (HTTP 200)
                           • App closes media stream & opens "Recharge Coins" popup
                           • Does NOT logout!
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

## 📱 Flutter WebRTC & Ringtone Implementation Guide

### 1. 🔔 Ringtone & Dial Tone Playback Workflow

When calling or receiving calls, the mobile app uses `audioplayers` or `just_audio` to play the continuous ringtone URL returned by the backend:

```dart
import 'package:audioplayers/audioplayers.dart';

final AudioPlayer _ringtonePlayer = AudioPlayer();

// A. Caller Device: Play Outgoing Dial Tone
void playOutgoingDialTone(String dialToneUrl) async {
  await _ringtonePlayer.setReleaseMode(ReleaseMode.loop);
  await _ringtonePlayer.play(UrlSource(dialToneUrl));
}

// B. Receiver Device: Play Incoming Ringtone
void playIncomingRingtone(String ringtoneUrl) async {
  await _ringtonePlayer.setReleaseMode(ReleaseMode.loop);
  await _ringtonePlayer.play(UrlSource(ringtoneUrl));
}

// C. Stop Ringtone immediately when Call Connects / Declines / Cancels
void stopRingtone() async {
  await _ringtonePlayer.stop();
}
```

---

### 2. 🎥 Fix For Video Not Showing (Remote Stream vs Local Stream)

> ⚠️ **Common Bug**: Why could the receiver only see their own camera instead of the caller's face?
> 
> **Cause**: The Flutter app did not attach `event.streams[0]` to a separate `_remoteRenderer` inside `peerConnection.onTrack`!
> 
> **Solution**: You must instantiate **two separate renderers** in Flutter WebRTC:
> 1. `_localRenderer` for your own front camera (shown in small PiP window).
> 2. `_remoteRenderer` for the partner's camera (shown in full-screen).

```dart
import 'dart:async';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_webrtc/flutter_webrtc.dart';
import 'package:http/http.dart' as http;

class WebrtcCallScreen extends StatefulWidget {
  final int callId;
  final String channelName;
  final bool isCaller;
  final String callType; // 'video' or 'audio'
  final String token;
  final String baseUrl;

  const WebrtcCallScreen({
    Key? key,
    required this.callId,
    required this.channelName,
    required this.isCaller,
    required this.callType,
    required this.token,
    required this.baseUrl,
  }) : super(key: key);

  @override
  _WebrtcCallScreenState createState() => _WebrtcCallScreenState();
}

class _WebrtcCallScreenState extends State<WebrtcCallScreen> {
  final RTCVideoRenderer _localRenderer = RTCVideoRenderer();
  final RTCVideoRenderer _remoteRenderer = RTCVideoRenderer();

  RTCPeerConnection? _peerConnection;
  MediaStream? _localStream;
  Timer? _signalingTimer;
  Timer? _billingTimer;
  int _lastSignalId = 0;
  bool _isMuted = false;
  bool _isSpeakerOn = true;

  @override
  void initState() {
    super.initState();
    initRenderersAndConnection();
  }

  Future<void> initRenderersAndConnection() async {
    // 1. Initialize Renderers
    await _localRenderer.initialize();
    await _remoteRenderer.initialize();

    // 2. Fetch ICE Servers from Backend
    final iceRes = await http.get(
      Uri.parse('${widget.baseUrl}/api/call/ice-servers'),
      headers: {'Authorization': 'Bearer ${widget.token}', 'Accept': 'application/json'},
    );
    final iceData = jsonDecode(iceRes.body);
    final configuration = {
      'iceServers': iceData['data']['iceServers'],
      'sdpSemantics': 'unified-plan',
    };

    // 3. Create PeerConnection
    _peerConnection = await createPeerConnection(configuration);

    // 4. Capture Local Camera / Mic Media Stream
    final isVideo = widget.callType == 'video';
    final mediaConstraints = {
      'audio': true,
      'video': isVideo
          ? {
              'facingMode': 'user',
              'mandatory': {'minWidth': '640', 'minHeight': '480', 'minFrameRate': '30'},
            }
          : false,
    };

    _localStream = await navigator.mediaDevices.getUserMedia(mediaConstraints);
    _localRenderer.srcObject = _localStream;

    // 5. Add local audio/video tracks to PeerConnection
    _localStream!.getTracks().forEach((track) {
      _peerConnection!.addTrack(track, _localStream!);
    });

    // 6. CRITICAL: Handle Incoming Remote Video/Audio Track
    _peerConnection!.onTrack = (RTCTrackEvent event) {
      if (event.streams.isNotEmpty) {
        setState(() {
          // Attaches the partner's remote video stream!
          _remoteRenderer.srcObject = event.streams[0];
        });
      }
    };

    // 7. On Local ICE Candidate Generated -> Send to Backend
    _peerConnection!.onIceCandidate = (RTCIceCandidate candidate) {
      _sendSignal('candidate', {
        'candidate': candidate.candidate,
        'sdpMid': candidate.sdpMid,
        'sdpMLineIndex': candidate.sdpMLineIndex,
      });
    };

    // 8. If Caller -> Create and Send SDP Offer
    if (widget.isCaller) {
      RTCSessionDescription offer = await _peerConnection!.createOffer();
      await _peerConnection!.setLocalDescription(offer);
      await _sendSignal('offer', {'sdp': offer.sdp, 'type': offer.type});
    }

    // 9. Start Polling for Signals (Offer / Answer / ICE Candidates)
    _startSignalingPolling();

    // 10. Start In-Call Pulse Billing (100 coins/min)
    _startBillingHeartbeat();
  }

  Future<void> _sendSignal(String type, dynamic payload) async {
    await http.post(
      Uri.parse('${widget.baseUrl}/api/call/signal/send'),
      headers: {
        'Authorization': 'Bearer ${widget.token}',
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode({
        'call_id': widget.callId,
        'channel_name': widget.channelName,
        'type': type,
        'payload': payload,
      }),
    );
  }

  void _startSignalingPolling() {
    _signalingTimer = Timer.periodic(const Duration(milliseconds: 750), (timer) async {
      final res = await http.get(
        Uri.parse('${widget.baseUrl}/api/call/signal/receive?call_id=${widget.callId}&last_signal_id=$_lastSignalId'),
        headers: {'Authorization': 'Bearer ${widget.token}', 'Accept': 'application/json'},
      );

      if (res.statusCode == 200) {
        final data = jsonDecode(res.body);
        final List signals = data['data'] ?? [];

        for (var sig in signals) {
          _lastSignalId = sig['id'];
          final type = sig['type'];
          final payload = sig['payload'];

          if (type == 'offer' && !widget.isCaller) {
            // Receiver sets remote Offer and sends Answer
            await _peerConnection!.setRemoteDescription(
              RTCSessionDescription(payload['sdp'], 'offer'),
            );
            RTCSessionDescription answer = await _peerConnection!.createAnswer();
            await _peerConnection!.setLocalDescription(answer);
            await _sendSignal('answer', {'sdp': answer.sdp, 'type': answer.type});
          } else if (type == 'answer' && widget.isCaller) {
            // Caller receives Answer
            await _peerConnection!.setRemoteDescription(
              RTCSessionDescription(payload['sdp'], 'answer'),
            );
          } else if (type == 'candidate') {
            // Add ICE Candidate
            await _peerConnection!.addCandidate(
              RTCIceCandidate(
                payload['candidate'],
                payload['sdpMid'],
                payload['sdpMLineIndex'],
              ),
            );
          }
        }
      }
    });
  }

  void _startBillingHeartbeat() {
    _billingTimer = Timer.periodic(const Duration(seconds: 60), (timer) async {
      final res = await http.post(
        Uri.parse('${widget.baseUrl}/api/call/deduct-interval'),
        headers: {
          'Authorization': 'Bearer ${widget.token}',
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({'call_id': widget.callId, 'elapsed_seconds': 60, 'coins': 100}),
      );

      final data = jsonDecode(res.body);
      if (res.statusCode == 402 || data['code'] == 'LOW_BALANCE_DEPOSIT_REQUIRED') {
        timer.cancel();
        _endCall();
        _showLowBalanceDialog();
      }
    });
  }

  void _showLowBalanceDialog() {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => AlertDialog(
        title: const Text('Insufficient Coins'),
        content: const Text('Your coin balance is low. Please recharge / deposit coins to continue calling.'),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.pop(ctx);
              Navigator.pushNamed(context, '/deposit');
            },
            child: const Text('Recharge Coins Now'),
          ),
        ],
      ),
    );
  }

  void _endCall() async {
    _signalingTimer?.cancel();
    _billingTimer?.cancel();

    await http.post(
      Uri.parse('${widget.baseUrl}/api/call/end'),
      headers: {'Authorization': 'Bearer ${widget.token}', 'Content-Type': 'application/json'},
      body: jsonEncode({'call_id': widget.callId}),
    );

    _localStream?.dispose();
    _peerConnection?.close();
    _localRenderer.dispose();
    _remoteRenderer.dispose();

    if (mounted) Navigator.pop(context);
  }

  @override
  Widget build(BuildContext context) {
    final isVideo = widget.callType == 'video';

    return Scaffold(
      backgroundColor: Colors.black,
      body: Stack(
        children: [
          // 1. FULLSCREEN BACKGROUND: Remote Video (Partner's Face)
          if (isVideo)
            Positioned.fill(
              child: RTCVideoView(
                _remoteRenderer,
                objectFit: RTCVideoViewObjectFit.RTCVideoViewObjectFitCover,
              ),
            )
          else
            // Audio Call Screen UI
            Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: const [
                  CircleAvatar(radius: 60, backgroundImage: NetworkImage('https://images.unsplash.com/photo-1534528741775-53994a69daeb')),
                  SizedBox(height: 16),
                  Text('Audio Call Connected', style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
                ],
              ),
            ),

          // 2. SMALL CORNER PiP: Local Video (Own Face)
          if (isVideo)
            Positioned(
              top: 50,
              right: 20,
              width: 110,
              height: 160,
              child: ClipRRect(
                borderRadius: BorderRadius.circular(12),
                child: RTCVideoView(_localRenderer, mirror: true, objectFit: RTCVideoViewObjectFit.RTCVideoViewObjectFitCover),
              ),
            ),

          // 3. BOTTOM CONTROLS (Mute, Speaker, Hangup)
          Positioned(
            bottom: 40,
            left: 0,
            right: 0,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: [
                FloatingActionButton(
                  heroTag: 'mic',
                  backgroundColor: _isMuted ? Colors.red : Colors.white24,
                  onPressed: () {
                    setState(() {
                      _isMuted = !_isMuted;
                      _localStream?.getAudioTracks()[0].enabled = !_isMuted;
                    });
                  },
                  child: Icon(_isMuted ? Icons.mic_off : Icons.mic, color: Colors.white),
                ),
                FloatingActionButton(
                  heroTag: 'hangup',
                  backgroundColor: Colors.red,
                  onPressed: _endCall,
                  child: const Icon(Icons.call_end, color: Colors.white),
                ),
                FloatingActionButton(
                  heroTag: 'speaker',
                  backgroundColor: _isSpeakerOn ? Colors.green : Colors.white24,
                  onPressed: () {
                    setState(() {
                      _isSpeakerOn = !_isSpeakerOn;
                      _localStream?.getAudioTracks()[0].enableSpeakerphone(_isSpeakerOn);
                    });
                  },
                  child: Icon(_isSpeakerOn ? Icons.volume_up : Icons.volume_down, color: Colors.white),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  @override
  void dispose() {
    _signalingTimer?.cancel();
    _billingTimer?.cancel();
    _localStream?.dispose();
    _peerConnection?.close();
    _localRenderer.dispose();
    _remoteRenderer.dispose();
    super.dispose();
```

---

## 🛠️ Mobile Developer Troubleshooting & Common Bug Fixes

### ❓ Issue 1: "কল রিসিভ করলে বা কথা বলার সময় অ্যাপ অটোমেটিক লগআউট হয়ে যায় কেন?" (Why app logs out during call)
- **Root Cause**: Flutter app global HTTP/Dio Interceptor intercepts HTTP 401/402/404 responses and automatically calls `AuthBloc.logout()` or `SharedPreferences.clear()`.
- **Backend Fix**: All call and signaling endpoints now return **HTTP 200 OK** with structured JSON:
  - When balance is low: `{ "status": false, "code": "LOW_BALANCE_DEPOSIT_REQUIRED", "message": "...", "should_terminate_call": true, "redirect_to_deposit": true }`
  - When call ended/rejected: `{ "status": false, "code": "CALL_TERMINATED", "message": "..." }`
- **Flutter App Action**:
  - In your API service, check `if (response['code'] == 'LOW_BALANCE_DEPOSIT_REQUIRED')`: terminate media stream and show the **"Recharge Coins / Deposit"** dialog. Do **NOT** call logout!

---

### ❓ Issue 2: "ভিডিও কলে কথা বলার সময় ক্যামেরা স্ট্রিম না এসে শুধু প্রোফাইল ছবি ও টাইমার শো করে কেন?" (Why camera video doesn't show)
- **Root Cause**:
  1. The Flutter UI was rendering a static placeholder `Image.network(avatarUrl)` on top of the `RTCVideoView` widget.
  2. `peerConnection.onTrack` event was not updating the `_remoteRenderer.srcObject`.
  3. The signaling loop (SDP Offer ➡️ SDP Answer ➡️ ICE Candidates) was not exchanging credentials.
- **Flutter App Action**:
  1. Initialize **two renderers**:
     ```dart
     final RTCVideoRenderer _localRenderer = RTCVideoRenderer();
     final RTCVideoRenderer _remoteRenderer = RTCVideoRenderer();
     ```
  2. In `peerConnection.onTrack`, always assign the remote stream:
     ```dart
     _peerConnection!.onTrack = (RTCTrackEvent event) {
       if (event.streams.isNotEmpty) {
         setState(() {
           _remoteRenderer.srcObject = event.streams[0]; // Renders partner's live video!
         });
       }
     };
     ```
  3. Build your UI Stack:
     - **Background Fullscreen**: `RTCVideoView(_remoteRenderer, objectFit: RTCVideoViewObjectFitCover)` (Shows caller/receiver face live).
     - **Corner PiP (Top Right)**: `RTCVideoView(_localRenderer, mirror: true)` (Shows own face).
     - Remove any static avatar/image covering the `RTCVideoView` once status is `connected`!

---

### ❓ Issue 4: "স্ক্রিনে 'Ringing & Connecting...' লেখাটি আটকে থাকে কেন?" (Why "Ringing & Connecting..." overlay is stuck)
- **Root Cause**:
  1. The Flutter UI state variable `_isConnecting` or `_isRinging` was not toggled to `false` when call status changed to `'connected'`.
  2. Because WebRTC media tracks were still negotiating or `_remoteRenderer.srcObject` was null, the UI conditional `if (_isConnecting || _remoteRenderer.srcObject == null)` kept showing the loading pill.
- **Flutter App Action**:
  ```dart
  // 1. In Status Polling Listener:
  if (response['data']['status'] == 'connected') {
    setState(() {
      _isConnecting = false; // Hides the "Ringing & Connecting..." overlay immediately!
      _isRinging = false;
    });
    // Stop Outgoing Dialtone
    CallSoundManager.stopRingtone();
  }

  // 2. In peerConnection.onTrack:
  _peerConnection!.onTrack = (RTCTrackEvent event) {
    if (event.streams.isNotEmpty) {
      setState(() {
        _remoteRenderer.srcObject = event.streams[0];
        _isConnecting = false; // Guarantees overlay disappears when video arrives
      });
    }
  };
  ```

---

### ❓ Issue 5: "কল গেলে রিসিভারের ফোনে রিংটোন শব্দ বাজে না কেন?" (Why Ringtone Sound Doesn't Play on Receiver Phone)
- **Root Cause**:
  - `CallSoundManager` wasn't receiving the `incoming_ringtone_url` from `GET /api/call/incoming` or `AudioPlayer` wasn't started in loop mode.
- **Flutter App Action**:
  ```dart
  // When Incoming Call is detected:
  final res = await http.get(Uri.parse('$baseUrl/api/call/incoming'), headers: authHeaders);
  if (res['data']?['has_incoming_call'] == true) {
    final ringtoneUrl = res['data']['incoming_ringtone_url'];
    
    // Play Ringtone in Loop
    await CallSoundManager.playIncomingRingtone(ringtoneUrl);
    
    // Show Incoming Call Screen (রিসিভ বাটন)
    Navigator.pushNamed(context, '/incoming_call', arguments: res['data']);
  }
  ```

---

### ❓ Issue 6: "Complete WebRTC Signaling Loop (VPS RESTful) — Offer, Answer & ICE Candidates"
Here is the exact complete lifecycle code to exchange video & audio bidirectionally on VPS:

```dart
// 1. Caller Creates Offer
RTCSessionDescription offer = await _peerConnection!.createOffer();
await _peerConnection!.setLocalDescription(offer);
await _sendSignal('offer', {'sdp': offer.sdp, 'type': offer.type});

// 2. Both Send ICE Candidates
_peerConnection!.onIceCandidate = (RTCIceCandidate candidate) {
  if (candidate.candidate != null) {
    _sendSignal('candidate', {
      'candidate': candidate.candidate,
      'sdpMid': candidate.sdpMid,
      'sdpMLineIndex': candidate.sdpMLineIndex,
    });
  }
};

// 3. Signaling Poller (Every 500-750ms)
_signalingTimer = Timer.periodic(const Duration(milliseconds: 600), (timer) async {
  final res = await http.get(
    Uri.parse('$baseUrl/api/call/signal/receive?call_id=$callId&last_signal_id=$_lastSignalId'),
    headers: authHeaders,
  );
  if (res.statusCode == 200) {
    final List signals = jsonDecode(res.body)['data'] ?? [];
    for (var sig in signals) {
      _lastSignalId = sig['id'];
      final type = sig['type'];
      final payload = sig['payload'];

      if (type == 'offer' && !widget.isCaller) {
        // Receiver handles Offer & generates Answer
        await _peerConnection!.setRemoteDescription(RTCSessionDescription(payload['sdp'], 'offer'));
        RTCSessionDescription answer = await _peerConnection!.createAnswer();
        await _peerConnection!.setLocalDescription(answer);
        await _sendSignal('answer', {'sdp': answer.sdp, 'type': answer.type});
      } else if (type == 'answer' && widget.isCaller) {
        // Caller sets Answer
        await _peerConnection!.setRemoteDescription(RTCSessionDescription(payload['sdp'], 'answer'));
      } else if (type == 'candidate') {
        // Add ICE Candidate
        await _peerConnection!.addCandidate(RTCIceCandidate(
          payload['candidate'],
          payload['sdpMid'],
          payload['sdpMLineIndex'],
        ));
      }
    }
  }
});
```

---

### ❓ Issue 7: "ভিডিও কলে কেন ব্যাকগ্রাউন্ডে নিজের ক্যামেরা এবং কর্নারে পার্টনারের ছবি দেখাচ্ছিল?" (Fixing Video View & Remote Stream UI Binding)
- **Root Cause**:
  1. In `video_call_screen.dart`, `onRemoteStreamConnected` was missing `setState(() {})`, so Flutter never knew when the remote stream arrived and stayed stuck rendering the static avatar.
  2. The background `RTCVideoView` was bound to `localRenderer` instead of `remoteRenderer`.
- **Flutter App Action**:
  ```dart
  // 1. When starting the call in VideoCallScreen:
  _webrtcService.startCallAsCaller(
    callId: widget.callId,
    channelName: widget.channelName,
    onRemoteStreamConnected: (stream) {
      if (mounted) {
        setState(() {}); // Crucial: Rebuilds UI so remoteRenderer becomes visible!
      }
    },
  );

  // 2. In build() widget layout:
  // Fullscreen Background -> Partner's Remote Video (or photo while connecting)
  Widget _buildBackgroundVideo() {
    if (_webrtcService.hasRemoteStream) {
      return RTCVideoView(
        _webrtcService.remoteRenderer,
        objectFit: RTCVideoViewObjectFit.RTCVideoViewObjectFitCover,
      );
    }
    return Image.network(widget.partnerAvatarUrl, fit: BoxFit.cover);
  }

  // Top-Right Corner PiP -> Own Front Camera
  Widget _buildLocalCameraPiP() {
    return Positioned(
      top: 50, right: 16, width: 110, height: 160,
      child: ClipRRect(
        borderRadius: BorderRadius.circular(16),
        child: RTCVideoView(
          _webrtcService.localRenderer,
          mirror: true,
          objectFit: RTCVideoViewObjectFit.RTCVideoViewObjectFitCover,
        ),
      ),
    );
  }
  ```

---

### ❓ Issue 8: "কল কেটে দিলে অপর প্রান্তের কল কেন কাটে না / স্ক্রিন স্টিল হয়ে আটকে থাকে?" (Synchronous Hangup & Call Termination)
- **Root Cause**:
  - When Caller hangs up (`POST /api/call/end`), Receiver's screen was not listening for the `'bye'` signal or status change to `'ended'`.
- **Backend Feature**:
  - `POST /api/call/end` automatically saves `status = 'ended'` AND creates a `CallSignal` with `type: 'bye'`.
- **Flutter App Action**:
  ```dart
  // 1. Inside Signaling Poller (receiveSignals loop):
  for (var signal in signals) {
    final type = signal['type']?.toString().toLowerCase();
    if (type == 'bye' || type == 'hangup' || type == 'call_ended') {
      await _webrtcService.dispose();
      if (mounted) {
        Navigator.pop(context); // Closes screen instantly!
      }
      return;
    }
  }

  // 2. Inside Call Status Poller (every 1 sec):
  final statusRes = await CallApiService.getCallStatus(widget.callId);
  if (statusRes['status'] == 'ended' || statusRes['status'] == 'rejected' || statusRes['status'] == 'cancelled') {
    await _webrtcService.dispose();
    if (mounted) {
      Navigator.pop(context); // Closes screen instantly!
    }
  }
  ```

---

### ❓ Issue 9: "ভিডিও কল দিলে 'Allow Permission' চায় কেন? অটোমেটিক পারমিশন কীভাবে করবেন?"
- **কারণ**: Android এবং iOS অপারেটিং সিস্টেমের নিজস্ব সিকিউরিটি নিয়মানুযায়ী প্রথমবার ক্যামেরা বা মাইক্রোফোন ব্যবহারের সময় ইউজারের থেকে পারমিশন নিতে হয়।
- **ফ্লাটার সমাধান**: অ্যাপ ওপেন হওয়ার সময় (Splash Screen বা Main Navigation Screen-এ) ক্যামেরা ও মাইক পারমিশন একবার নিয়ে নিলে কল দেওয়ার সময় আর কোনো পারমিশন ডায়ালগ পপআপ হবে না:
  ```dart
  import 'package:permission_handler/permission_handler.dart';

  // Splash Screen বা Login করার সাথে সাথে একবার কল করুন:
  Future<void> requestCallPermissions() async {
    await [
      Permission.camera,
      Permission.microphone,
    ].request();
  }
  ```

---

### ❓ Issue 10: "হোয়াটসঅ্যাপ / ইমো / মেসেঞ্জারের মতো সম্পূর্ণ VideoCallScreen উইজেট কোড"

নিচে সম্পূর্ণ রেডিমেড `VideoCallScreen` কোড দেওয়া হলো যা সরাসরি আপনার `video_call_screen.dart` ফাইলে রিপ্লেস করলে:
1. **ব্যাকগ্রাউন্ডে সবসময় অন্য প্রান্তের ইউজারের (রুমার) লাইভ ক্যামেরা ভিডিও দেখাবে**।
2. **কর্নারের ছোট উইন্ডোতে নিজের লাইভ ফ্রন্ট ক্যামেরা দেখাবে**।
3. **উভয় প্রান্তের কথা লাউডস্পিকারে ক্রিস্টাল ক্লিয়ার শোনা যাবে**।
4. **যে-কোনো একজন লাল বাটন চাপলে উভয়ের স্ক্রিন তৎক্ষণাৎ বন্ধ হয়ে যাবে**।

```dart
import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_webrtc/flutter_webrtc.dart';
import '../services/webrtc_call_service.dart';
import '../services/call_api_service.dart';
import '../services/call_sound_manager.dart';

class VideoCallScreen extends StatefulWidget {
  final dynamic callId;
  final String? channelName;
  final bool isCaller;
  final String partnerName;
  final String partnerAvatarUrl;
  final int ratePerMinute;

  const VideoCallScreen({
    Key? key,
    required this.callId,
    this.channelName,
    required this.isCaller,
    required this.partnerName,
    required this.partnerAvatarUrl,
    this.ratePerMinute = 100,
  }) : super(key: key);

  @override
  State<VideoCallScreen> createState() => _VideoCallScreenState();
}

class _VideoCallScreenState extends State<VideoCallScreen> {
  final WebRTCCallService _webrtcService = WebRTCCallService();
  Timer? _statusTimer;
  Timer? _billingTimer;
  int _callDurationSeconds = 0;
  Timer? _durationTimer;
  bool _isMuted = false;
  bool _isSpeakerOn = true;

  @override
  void initState() {
    super.initState();
    _initCallSession();
  }

  Future<void> _initCallSession() async {
    // 1. Initialize Media & Camera
    await _webrtcService.initializeMedia(isAudioOnly: false);
    if (mounted) setState(() {});

    // 2. Start WebRTC Session
    if (widget.isCaller) {
      await _webrtcService.startCallAsCaller(
        callId: widget.callId,
        channelName: widget.channelName,
        onRemoteStreamConnected: (stream) {
          if (mounted) {
            setState(() {}); // Crucial: Triggers UI rebuild so remote video shows!
          }
        },
        onCallEnded: _onPartnerEndedCall,
      );
    } else {
      await _webrtcService.startCallAsReceiver(
        callId: widget.callId,
        channelName: widget.channelName,
        onRemoteStreamConnected: (stream) {
          if (mounted) {
            setState(() {}); // Crucial: Triggers UI rebuild so remote video shows!
          }
        },
        onCallEnded: _onPartnerEndedCall,
      );
    }

    // 3. Start Duration Timer
    _durationTimer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (mounted) {
        setState(() {
          _callDurationSeconds++;
        });
      }
    });

    // 4. Start Status Sync Poller (Every 1s)
    _statusTimer = Timer.periodic(const Duration(seconds: 1), (timer) async {
      final statusRes = await CallApiService.getCallStatus(widget.callId);
      final status = statusRes['data']?['status'];
      final isTerminated = statusRes['data']?['is_terminated'] ?? false;
      if (status == 'ended' || status == 'cancelled' || status == 'rejected' || isTerminated == true) {
        _onPartnerEndedCall();
      }
    });

    // 5. In-Call Pulse Billing (Only if Caller, every 60s)
    if (widget.isCaller) {
      _billingTimer = Timer.periodic(const Duration(seconds: 60), (timer) async {
        final res = await CallApiService.deductInterval(
          callId: widget.callId,
          elapsedSeconds: _callDurationSeconds,
          coins: widget.ratePerMinute,
        );
        if (res['code'] == 'LOW_BALANCE_DEPOSIT_REQUIRED') {
          _endCall();
          _showDepositDialog();
        }
      });
    }
  }

  void _onPartnerEndedCall() async {
    _statusTimer?.cancel();
    _billingTimer?.cancel();
    _durationTimer?.cancel();
    await _webrtcService.dispose();
    if (mounted) {
      Navigator.pop(context); // Exits call screen instantly
    }
  }

  void _endCall() async {
    _statusTimer?.cancel();
    _billingTimer?.cancel();
    _durationTimer?.cancel();
    await CallApiService.endCall(widget.callId);
    await _webrtcService.dispose();
    if (mounted) {
      Navigator.pop(context);
    }
  }

  void _showDepositDialog() {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => AlertDialog(
        title: const Text('Insufficient Coins'),
        content: const Text('Your coin balance is low. Please recharge / deposit coins to continue calling.'),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.pop(ctx);
              Navigator.pushNamed(context, '/deposit');
            },
            child: const Text('Recharge Coins Now'),
          ),
        ],
      ),
    );
  }

  String _formatDuration(int totalSecs) {
    final mins = (totalSecs ~/ 60).toString().padLeft(2, '0');
    final secs = (totalSecs % 60).toString().padLeft(2, '0');
    return '$mins:$secs';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      body: Stack(
        children: [
          // 1. FULLSCREEN BACKGROUND: Remote Video (Partner's Live Camera Feed)
          Positioned.fill(
            child: _webrtcService.hasRemoteStream
                ? RTCVideoView(
                    _webrtcService.remoteRenderer,
                    objectFit: RTCVideoViewObjectFit.RTCVideoViewObjectFitCover,
                  )
                : Image.network(
                    widget.partnerAvatarUrl,
                    fit: BoxFit.cover,
                  ),
          ),

          // 2. CORNER PiP: Local Camera (Your Own Front Camera Feed)
          Positioned(
            top: 50,
            right: 16,
            width: 110,
            height: 160,
            child: ClipRRect(
              borderRadius: BorderRadius.circular(16),
              child: Container(
                color: Colors.black54,
                child: _webrtcService.isInitialized
                    ? RTCVideoView(
                        _webrtcService.localRenderer,
                        mirror: true,
                        objectFit: RTCVideoViewObjectFit.RTCVideoViewObjectFitCover,
                      )
                    : const Center(child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2)),
              ),
            ),
          ),

          // 3. TOP HEADER: Partner Info & Call Duration Timer
          Positioned(
            top: 50,
            left: 16,
            child: Row(
              children: [
                CircleAvatar(
                  radius: 20,
                  backgroundImage: NetworkImage(widget.partnerAvatarUrl),
                ),
                const SizedBox(width: 8),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      widget.partnerName,
                      style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
                    ),
                    Text(
                      _formatDuration(_callDurationSeconds),
                      style: const TextStyle(color: Colors.white70, fontSize: 13),
                    ),
                  ],
                ),
              ],
            ),
          ),

          // 4. BOTTOM CONTROLS: Switch Camera, Mute, Hangup, Speaker
          Positioned(
            bottom: 40,
            left: 0,
            right: 0,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: [
                // Switch Camera (Front/Back)
                FloatingActionButton(
                  heroTag: 'switch_cam',
                  backgroundColor: Colors.white24,
                  onPressed: () => _webrtcService.switchCamera(),
                  child: const Icon(Icons.cameraswitch, color: Colors.white),
                ),

                // Microphone Mute / Unmute
                FloatingActionButton(
                  heroTag: 'mic',
                  backgroundColor: _isMuted ? Colors.red : Colors.white24,
                  onPressed: () {
                    setState(() {
                      _isMuted = !_isMuted;
                      _webrtcService.toggleMute(_isMuted);
                    });
                  },
                  child: Icon(_isMuted ? Icons.mic_off : Icons.mic, color: Colors.white),
                ),

                // End Call (Hangup)
                FloatingActionButton(
                  heroTag: 'hangup',
                  backgroundColor: Colors.red,
                  onPressed: _endCall,
                  child: const Icon(Icons.call_end, color: Colors.white),
                ),

                // Loudspeaker Toggle
                FloatingActionButton(
                  heroTag: 'speaker',
                  backgroundColor: _isSpeakerOn ? Colors.green : Colors.white24,
                  onPressed: () {
                    setState(() {
                      _isSpeakerOn = !_isSpeakerOn;
                      _webrtcService.toggleSpeakerphone(_isSpeakerOn);
                    });
                  },
                  child: Icon(_isSpeakerOn ? Icons.volume_up : Icons.volume_down, color: Colors.white),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  @override
  void dispose() {
    _statusTimer?.cancel();
    _billingTimer?.cancel();
    _durationTimer?.cancel();
    _webrtcService.dispose();
```

---

### ❓ Issue 11: "🚨 Fatal Error: Unable to RTCPeerConnection::addTrack: peerConnection is null & Surface.release() NPE Fix"

> 🎯 **আপনার স্ক্রিনশটের ডিবাগ লগ ও লগক্যাটের এরর**:
> ```
> [02:32:32] ERROR in _createPeerConnectionInternal: Unable to RTCPeerConnection::addTrack: addTrack(): peerConnection is null
> [02:32:32] Failed to create PeerConnection
> E/MethodChannel#FlutterWebRTC.Method: java.lang.NullPointerException: Attempt to invoke virtual method 'void android.view.Surface.release()' on a null object reference
> ```

#### 🔍 মূল কারণ (Root Cause):
1. `_createPeerConnectionInternal()` মেথডে `final pc = await createPeerConnection(configuration);` তৈরি হওয়ার পর `_peerConnection = pc;` অ্যাসাইন করার আগেই `pc.addTrack(track, _localStream!)` কল করা হয়েছিল। ফলে নেটিভ অ্যান্ড্রয়েড লেয়ারে `peerConnection is null` এক্সেপশন হয়ে পিয়ার কানেকশন ফেইল করছিল।
2. কল ডিসপোজ করার সময় `renderer.srcObject = null` না করে সরাসরি `dispose()` কল করায় অ্যান্ড্রয়েড `Surface.release()` নাল পয়েন্টার এক্সেপশন দিচ্ছিল।

---

#### 🛠️ `webrtc_call_service.dart` ফাইলে নিচের দুটি মেথড সরাসরি রিপ্লেস করুন:

#### ১. `_createPeerConnectionInternal` মেথড:
```dart
  Future<RTCPeerConnection?> _createPeerConnectionInternal(
    dynamic callId,
    String? channelName,
    Function(MediaStream stream)? onRemoteStreamConnected,
  ) async {
    try {
      _log('Fetching ICE servers from server...');
      final iceServers = await CallApiService.getIceServers();
      _log('ICE servers received: ${iceServers.length} servers');

      final Map<String, dynamic> configuration = {
        'iceServers': iceServers,
        'sdpSemantics': 'unified-plan',
      };

      _log('Creating RTCPeerConnection...');
      final pc = await createPeerConnection(configuration);
      _peerConnection = pc; // 🔑 CRUCIAL FIX: _peerConnection আগে অ্যাসাইন করতে হবে!
      pcState = 'Created';
      _log('RTCPeerConnection created and assigned successfully');

      // 🔑 CRUCIAL FIX: Safe Track Adding with fallback
      if (_localStream != null) {
        for (final track in _localStream!.getTracks()) {
          try {
            await pc.addTrack(track, _localStream!);
            _log('Local track added: ${track.kind} (${track.id})');
          } catch (e) {
            _log('addTrack fallback to addStream: $e');
            try {
              await pc.addStream(_localStream!);
            } catch (_) {}
          }
        }
      }

      // 1. Unified-Plan Track Event
      pc.onTrack = (RTCTrackEvent event) {
        _log('ON_TRACK: kind=${event.track.kind}, streams=${event.streams.length}');
        if (event.streams.isNotEmpty) {
          _remoteStream = event.streams[0];
          remoteRenderer.srcObject = _remoteStream;
          _log('Remote stream attached to remoteRenderer (tracks: ${_remoteStream!.getTracks().length})');
          onRemoteStreamConnected?.call(_remoteStream!);
        } else if (event.track.kind == 'video' || event.track.kind == 'audio') {
          _remoteStream ??= event.streams.firstOrNull;
          remoteRenderer.srcObject = _remoteStream;
          onRemoteStreamConnected?.call(_remoteStream!);
        }
      };

      // 2. Fallback onAddTrack handler
      pc.onAddTrack = (MediaStream stream, MediaStreamTrack track) {
        _log('ON_ADD_TRACK: kind=${track.kind}, streamId=${stream.id}');
        _remoteStream = stream;
        remoteRenderer.srcObject = _remoteStream;
        onRemoteStreamConnected?.call(_remoteStream!);
      };

      // 3. Fallback onAddStream handler
      pc.onAddStream = (MediaStream stream) {
        _log('ON_ADD_STREAM: streamId=${stream.id}, tracks=${stream.getTracks().length}');
        _remoteStream = stream;
        remoteRenderer.srcObject = _remoteStream;
        onRemoteStreamConnected?.call(_remoteStream!);
      };

      // 4. Connection State Observers
      pc.onConnectionState = (RTCPeerConnectionState state) {
        pcState = state.toString().split('.').last;
        _log('PC State changed: $pcState');
      };

      pc.onIceConnectionState = (RTCIceConnectionState state) {
        iceState = state.toString().split('.').last;
        _log('ICE State changed: $iceState');
      };

      // 5. ICE Candidate Listener
      pc.onIceCandidate = (RTCIceCandidate candidate) {
        if (candidate.candidate != null && candidate.candidate!.isNotEmpty) {
          iceCandidatesSent++;
          _log('ICE candidate generated (#$iceCandidatesSent), sending to server...');
          CallApiService.sendSignal(
            callId: callId,
            channelName: channelName,
            type: 'candidate',
            payload: {
              'candidate': candidate.candidate,
              'sdpMid': candidate.sdpMid,
              'sdpMLineIndex': candidate.sdpMLineIndex,
            },
          );
        }
      };

      return pc;
    } catch (e, st) {
      lastError = e.toString();
      _log('ERROR in _createPeerConnectionInternal: $e');
      AppLogger.error('CreatePeerConnectionError', e, st);
      return null;
    }
  }
```

---

#### ২. ক্র্যাশ-প্রুফ `dispose()` মেথড (Avoids `Surface.release()` NullPointerException):
```dart
  Future<void> dispose() async {
    try {
      _signalingTimer?.cancel();
      _signalingTimer = null;

      if (_peerConnection != null) {
        try {
          await _peerConnection!.close();
        } catch (_) {}
        try {
          await _peerConnection!.dispose();
        } catch (_) {}
        _peerConnection = null;
      }

      if (_localStream != null) {
        for (final track in _localStream!.getTracks()) {
          try {
            track.stop();
          } catch (_) {}
        }
        try {
          await _localStream!.dispose();
        } catch (_) {}
        _localStream = null;
      }

      // 🔑 CRUCIAL: Detach srcObject before disposing renderers to prevent Surface.release NPE!
      try {
        localRenderer.srcObject = null;
      } catch (_) {}
      try {
        remoteRenderer.srcObject = null;
      } catch (_) {}

      try {
        await localRenderer.dispose();
      } catch (_) {}
      try {
        await remoteRenderer.dispose();
      } catch (_) {}

      _remoteStream = null;
      _isInitialized = false;
      _log('WebRTC streams and renderers disposed cleanly without crash');
    } catch (e, st) {
      _log('WebRTCDisposeError: $e');
      AppLogger.error('WebRTCDisposeError', e, st);
    }
  }
```

---

### ❓ Issue 12: "🚨 Offer Error: Unable to RTCPeerConnection::createOffer / WEBRTC_CREATE_OFFER_ERROR & addTrack null Fix + 0-Second Instant Ringtone"

> 🎯 **আপনার স্ক্রিনশটের ডিবাগ লগ ও এরর**:
> ```
> PC: Created | ICE: Not Started
> Offer: Offer Error: Unable to RTCPeerConnection::createOffer: peerConnectionCreateOffer(): WEBRTC_CREATE_OFFER_ERROR
> Answer: Idle | Cand: S:0 R:0 | Remote: Waiting
> [12:39:25] addTrack(): peerConnection is null
> [12:39:25] addTrack fallback to addStream: Unable to RTCPeerConnection::addTrack: peerConnectionAddStream(): peerConnection is null
> ```

#### 🔍 মূল সমস্যা ৩টি (Root Causes):
1. **`createOffer()` Constraints এরর**: `unified-plan` মোডে `createOffer({'offerToReceiveVideo': 1})` এভাবে ইন্টিজার পাস করলে নেটিভ WebRTC C++ ইঞ্জিন `WEBRTC_CREATE_OFFER_ERROR` থ্রো করে। সঠিক উপায় হলো `createOffer({'mandatory': {'OfferToReceiveAudio': true, 'OfferToReceiveVideo': true}, 'optional': []})` ব্যবহার করা।
2. **`addTrack` এর সময় Native Handle সিঙ্ক**: `createPeerConnection()` কল করার পর নেটিভ অ্যান্ড্রয়েড লেয়ারে অবজেক্ট রেজিস্টার হতে কয়েক মিলি-সেকেন্ড সময় লাগে। তাই `await Future.delayed(const Duration(milliseconds: 100))` দিয়ে তারপর `addTrack` কল করতে হবে এবং `_peerConnection` ভ্যালিডেশন চেক রাখতে হবে।
3. **রিংটোন বাজতে ৫-৭ সেকেন্ড লেট হওয়া**: কল ডায়াল করার পর এপিআই রেসপন্স বা পোলিংয়ের অপেক্ষায় থাকার কারণে রিংটোন দেরিতে বাজে। সমাধান হলো — ইউজার "Call" বাটনে চাপ দেওয়ার সাথে সাথে **Zero Milliseconds (তাৎক্ষণিক)** লোকাল রিংটোন `AudioPlayer.play()` চালু করতে হবে, সার্ভার এপিআই কল হবে ব্যাকগ্রাউন্ডে।

---

#### 🛠️ `webrtc_call_service.dart` ফাইলে নিচের কমপ্লিট মেথডগুলো সরাসরি রিপ্লেস করুন:

```dart
  // ========================================================
  // 1. 100% Robust PeerConnection Creation & Track Binding
  // ========================================================
  Future<RTCPeerConnection?> _createPeerConnectionInternal(
    dynamic callId,
    String? channelName,
    Function(MediaStream stream)? onRemoteStreamConnected,
  ) async {
    try {
      _log('Fetching ICE servers from server...');
      final iceServers = await CallApiService.getIceServers();
      _log('ICE servers received: ${iceServers.length} servers');

      final Map<String, dynamic> configuration = {
        'iceServers': iceServers.isNotEmpty
            ? iceServers
            : [
                {
                  'urls': [
                    'stun:stun.l.google.com:19302',
                    'stun:stun1.l.google.com:19302',
                    'stun:stun2.l.google.com:19302',
                  ]
                },
                {
                  'urls': ['stun:global.stun.twilio.com:3478']
                }
              ],
        'sdpSemantics': 'unified-plan',
      };

      _log('Creating RTCPeerConnection...');
      final pc = await createPeerConnection(configuration);
      _peerConnection = pc;
      pcState = 'Created';
      _log('RTCPeerConnection created and assigned successfully');

      // 🔑 CRUCIAL: 100ms micro-pause to ensure native handle registration
      await Future.delayed(const Duration(milliseconds: 100));

      // 🔑 CRUCIAL: Safe Track Adding with validation
      if (_localStream != null && _peerConnection != null) {
        for (final track in _localStream!.getTracks()) {
          try {
            await _peerConnection!.addTrack(track, _localStream!);
            _log('✅ Local track added: ${track.kind} (${track.id})');
          } catch (e) {
            _log('⚠️ addTrack warning: $e');
          }
        }
      }

      // Unified-Plan Track Event (Remote Stream)
      pc.onTrack = (RTCTrackEvent event) {
        _log('📥 ON_TRACK: kind=${event.track.kind}, streams=${event.streams.length}');
        if (event.streams.isNotEmpty) {
          _remoteStream = event.streams[0];
          remoteRenderer.srcObject = _remoteStream;
          _log('✅ Remote stream attached to remoteRenderer (audio: ${_remoteStream!.getAudioTracks().length}, video: ${_remoteStream!.getVideoTracks().length})');
          onRemoteStreamConnected?.call(_remoteStream!);
        }
      };

      // Connection State Observers
      pc.onConnectionState = (RTCPeerConnectionState state) {
        pcState = state.toString().split('.').last;
        _log('🔗 PC State: $pcState');
      };

      pc.onIceConnectionState = (RTCIceConnectionState state) {
        iceState = state.toString().split('.').last;
        _log('🧊 ICE State: $iceState');
      };

      // ICE Candidate Listener
      pc.onIceCandidate = (RTCIceCandidate candidate) {
        if (candidate.candidate != null && candidate.candidate!.isNotEmpty) {
          iceCandidatesSent++;
          _log('📤 ICE candidate generated (#$iceCandidatesSent), sending to server...');
          CallApiService.sendSignal(
            callId: callId,
            channelName: channelName,
            type: 'candidate',
            payload: {
              'candidate': candidate.candidate,
              'sdpMid': candidate.sdpMid,
              'sdpMLineIndex': candidate.sdpMLineIndex,
            },
          );
        }
      };

      return pc;
    } catch (e, st) {
      lastError = e.toString();
      _log('❌ ERROR in _createPeerConnectionInternal: $e');
      AppLogger.error('CreatePeerConnectionError', e, st);
      return null;
    }
  }

  // ========================================================
  // 2. Fixed createOffer (Eliminates WEBRTC_CREATE_OFFER_ERROR)
  // ========================================================
  Future<bool> createAndSendOffer(dynamic callId, String? channelName) async {
    try {
      if (_peerConnection == null) {
        _log('❌ Cannot create offer: _peerConnection is null');
        return false;
      }

      _log('Creating SDP Offer...');
      
      // 🔑 CRUCIAL: Standard unified-plan constraints
      final Map<String, dynamic> offerConstraints = {
        'mandatory': {
          'OfferToReceiveAudio': true,
          'OfferToReceiveVideo': true,
        },
        'optional': [],
      };

      RTCSessionDescription offer = await _peerConnection!.createOffer(offerConstraints);
      await _peerConnection!.setLocalDescription(offer);
      _log('✅ Local SDP Offer created & set as local description');

      // Send offer to Laravel Backend
      final success = await CallApiService.sendSignal(
        callId: callId,
        channelName: channelName,
        type: 'offer',
        payload: {
          'sdp': offer.sdp,
          'type': offer.type,
        },
      );

      return success;
    } catch (e, st) {
      lastError = 'Offer Error: $e';
      _log('❌ Error in createAndSendOffer: $e');
      AppLogger.error('CreateOfferError', e, st);
      return false;
    }
  }

  // ========================================================
  // 3. Fixed createAnswer (For Receiver Side)
  // ========================================================
  Future<bool> createAndSendAnswer(dynamic callId, String? channelName) async {
    try {
      if (_peerConnection == null) {
        _log('❌ Cannot create answer: _peerConnection is null');
        return false;
      }

      _log('Creating SDP Answer...');
      final Map<String, dynamic> answerConstraints = {
        'mandatory': {
          'OfferToReceiveAudio': true,
          'OfferToReceiveVideo': true,
        },
        'optional': [],
      };

      RTCSessionDescription answer = await _peerConnection!.createAnswer(answerConstraints);
      await _peerConnection!.setLocalDescription(answer);
      _log('✅ Local SDP Answer created & set as local description');

      // Send answer to Laravel Backend
      final success = await CallApiService.sendSignal(
        callId: callId,
        channelName: channelName,
        type: 'answer',
        payload: {
          'sdp': answer.sdp,
          'type': answer.type,
        },
      );

      return success;
    } catch (e, st) {
      lastError = 'Answer Error: $e';
      _log('❌ Error in createAndSendAnswer: $e');
      AppLogger.error('CreateAnswerError', e, st);
      return false;
    }
  }
```

---

#### 🔔 Instant Ringtone Solution (0-Second Delay):

```dart
// 📞 Caller Side: Call Button প্রেস করার সাথে সাথে তাৎক্ষণিক রিংটোন বাজানো
void onStartCallPressed() {
  // ১. সাথে সাথে রিংটোন চালু (Zero delay!)
  RingtoneService.playOutgoingRingtone(); // asset: 'assets/audio/outgoing_ring.mp3', loop: true

  // ২. ব্যাকগ্রাউন্ডে সার্ভার কল ইনিশিয়েট
  CallApiService.initiateCall(receiverId: hostUser.id, callType: 'video').then((res) {
    if (!res.status) {
      RingtoneService.stop();
      showToast(res.message);
    }
  });
}

// 📲 Receiver Side: ইনকামিং কল আসার সাথে সাথে তাৎক্ষণিক রিংটোন
void onIncomingCallDetected(IncomingCallData call) {
  RingtoneService.playIncomingRingtone(); // asset: 'assets/audio/incoming_ring.mp3', loop: true
  showIncomingCallScreen(call);
}

// ⏹️ কল রিসিভ বা কেটে দিলে সাথে সাথে রিংটোন বন্ধ
void onCallConnectedOrEnded() {
  RingtoneService.stop();
}
```

---

### ❓ Issue 13: "এক ফোন থেকে কল দিলে অটোমেটিক নিজের ফোনে রিসিভ হয়ে যায়, কিন্তু অপর প্রান্তে (রিসিভারের ফোনে) কল যায় না এবং WebRTC অডিও-ভিডিও স্ট্রিম হয় না কেন?"

> 🎯 **সমস্যাটির লক্ষণ (Symptoms)**:
> 1. ফোন ১ (কলার) থেকে কল ডায়াল করলে সাথে সাথে কলারের ফোনেই কলটি "Connected / Received" হয়ে যাচ্ছিল (অথচ রিসিভার তখনো কল ধরে নাই)।
> 2. ফোন ২ (রিসিভার/হোস্ট) এর ফোনে কোনো ইনকামিং কল আসছিল না এবং কোনো রিংটোন বাজছিল না।
> 3. ভিডিও কল কানেক্ট হওয়ার পর একে অপরের মুখ দেখা যাচ্ছিল না বা কথা শোনা যাচ্ছিল না।

---

#### 🔍 মূল ৩টি কারণ এবং ব্যাকএন্ড সমাধান (Root Causes & Backend Fixes Applied):

| সমস্যা | মূল কারণ (Root Cause) | ব্যাকএন্ডে কীভাবে ফিক্স করা হলো (Fix Applied) |
| :--- | :--- | :--- |
| **১. কলারের ফোনে অটো-রিসিভ হওয়া** | কলার কল ইনিশিয়েট করার পর অ্যাপের ব্যাকগ্রাউন্ড টাইমার `/api/call/deduct-interval` (পালস বিলিং) কল করছিল। পূর্বের কোডে `deductInterval` স্বয়ংক্রিয়ভাবে স্ট্যাটাসকে `connected` করে ফেলছিল। | **ফিক্সড**: `deductInterval` এখন কল স্ট্যাটাস `ringing` থাকলে কখনোই `connected` করবে না। যতক্ষণ না রিসিভার রিসিভ বাটন প্রেস করে `/api/call/accept` কল করবে, ততক্ষণ কল `ringing` থাকবে। |
| **২. রিসিভারের ফোনে কল না যাওয়া** | রিসিভার অ্যাপ যখন `GET /api/call/incoming` কল করছিল, তখন সঠিক `Authorization: Bearer <TOKEN>` অথবা `user_id` না থাকায় ব্যাকএন্ড রিসিভারকে শনাক্ত করতে না পেরে ডিফল্ট ইউজার হিসেবে চেক করছিল। | **ফিক্সড**: `checkIncoming` এখন হেডার বা বডি/কোয়ারিতে পাঠানো `user_id`, `receiver_id`, `account_id`, `phone` সরাসরি সাপোর্ট করে। ফলে রিসিভার নিশ্চিতভাবে তার ইনকামিং কল পায়। |
| **৩. WebRTC অডিও/ভিডিও স্ট্রিম না আসা** | সিগন্যালিংয়ের সময় অফার/অ্যানসার ও ICE Candidate আদান-প্রদানে ইউজার ফিল্টারিং এবং লোকাল/রিমোট ভিডিও রেন্ডারার উইজেটে স্টেট আপডেট না হওয়ায় লাইভ ক্যামেরা দৃশ্যমান হচ্ছিল না। | **ফিক্সড**: ব্যাকএন্ডে `sendSignal` ও `getSignals` এ পিয়ার সিগন্যালিং ১০০% নির্ভুল করা হয়েছে। ফ্লাটারে `peerConnection.onTrack` এ `setState` ও স্পিকার অন করলেই লাইভ ভিডিও ও অডিও চালু হয়ে যায়। |

---

#### 🚀 সম্পূর্ণ ২-ফোন কল ও WebRTC ফ্লো (Step-by-Step Complete Flow):

```
┌──────────────────────────────┐                         ┌──────────────────────────────┐
│       Phone 1 (Caller)       │                         │      Phone 2 (Receiver)      │
└──────────────┬───────────────┘                         └──────────────┬───────────────┘
               │                                                        │
 1. Initiate Call (POST /api/call/initiate)                             │
    Body: {"receiver_id": 2, "call_type": "video"}                      │
    Returns: {call_id: 12, status: "ringing"}                           │
    • Starts Outgoing Ringtone 🔔                                       │
    • Starts Status Polling (/api/call/status/12)                       │
               │                                                        │
               │                                          2. Polling / Incoming Check
               │                                             (GET /api/call/incoming?user_id=2)
               │                                             Returns: {has_incoming_call: true, call_id: 12}
               │                                             • Starts Incoming Ringtone 🎵
               │                                             • Shows Incoming Call UI (রিসিভ বাটন)
               │                                                        │
               │                                          3. Receiver Taps "Accept (রিসিভ)"
               │                                             POST /api/call/accept
               │                                             Body: {"call_id": 12}
               │                                             • Call Status becomes "connected"
               │                                             • Stops Ringtone ⏹️
               │                                                        │
 4. Caller Detects status == "connected"                                │
    • Stops Ringtone ⏹️                                                 │
    • Opens VideoCallScreen                                             │
               │                                                        │
 5. WebRTC Peer-to-Peer Connection Starts                               │
    • Caller creates SDP Offer  ──► POST /api/call/signal/send ───────► Receiver gets Offer
    • Receiver creates Answer   ◄── POST /api/call/signal/send ◄─────── Receiver sends Answer
    • Both exchange ICE Candidates via /api/call/signal/send & /api/call/signal/receive
               │                                                        │
 6. 🎉 Audio & Video Streams are LIVE!                                  │
    • Phone 1 sees Phone 2's Camera (Fullscreen)                        │ Phone 2 sees Phone 1's Camera
    • Phone 1 hears Phone 2's Voice (Crystal clear audio)               │ Phone 2 hears Phone 1's Voice
               │                                                        │
 7. In-Call 100 Coins/Min Heartbeat (Caller ONLY)                       │
    • Caller sends POST /api/call/deduct-interval every 5-10s           │
    • 50% Coins Credited to Receiver's Wallet (Host Earnings) 💰        │
```

---

#### 📱 ফ্লাটার অ্যাপে রিসিভারের ইনকামিং কল লিসেনার ও রিসিভ বাটন কোড:

```dart
// 1. Receiver Device: Background/Periodic Incoming Call Checker (প্রতি ১-২ সেকেন্ডে চেক করবে)
Timer? _incomingCallPoller;

void startListeningForIncomingCalls(int currentUserId) {
  _incomingCallPoller = Timer.periodic(const Duration(seconds: 2), (timer) async {
    final response = await http.get(
      Uri.parse('$baseUrl/api/call/incoming?user_id=$currentUserId'),
      headers: {
        'Accept': 'application/json',
        'Authorization': 'Bearer $userAuthToken',
      },
    );

    if (response.statusCode == 200) {
      final json = jsonDecode(response.body);
      if (json['has_incoming_call'] == true && json['data'] != null) {
        _incomingCallPoller?.cancel(); // পোলিং বন্ধ
        
        final callData = json['data'];
        
        // রিংটোন বাজানো শুরু
        RingtoneService.playIncomingRingtone();
        
        // ইনকামিং কল স্ক্রিন ওপেন
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => IncomingCallScreen(
              callId: callData['call_id'],
              channelName: callData['channel_name'],
              callerName: callData['caller']['name'],
              callerAvatar: callData['caller']['avatar'],
              callType: callData['call_type'],
            ),
          ),
        );
      }
    }
  });
}

// 2. IncomingCallScreen-এ "Accept / Receive (রিসিভ বাটন)" প্রেস করলে:
Future<void> onReceiveCallPressed(int callId, String channelName, String callerName, String callerAvatar) async {
  // রিংটোন বন্ধ করা
  RingtoneService.stop();

  // ব্যাকএন্ডে Accept পাঠানো
  final res = await http.post(
    Uri.parse('$baseUrl/api/call/accept'),
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'Authorization': 'Bearer $userAuthToken',
    },
    body: jsonEncode({'call_id': callId}),
  );

  final json = jsonDecode(res.body);
  if (json['status'] == true) {
    // সরাসরি WebRTC ভিডিও কল স্ক্রিনে চলে যাবে
    Navigator.pushReplacement(
      context,
      MaterialPageRoute(
        builder: (_) => VideoCallScreen(
          callId: callId,
          channelName: channelName,
          isCaller: false, // রিসিভার
          partnerName: callerName,
          partnerAvatarUrl: callerAvatar,
        ),
      ),
    );
  }
}
```

---

#### 🎙️ WebRTC অডিও ও ভিডিও স্ট্রিমিং নিশ্চিত করার ৩টি জরুরি নিয়ম:

1. **লাউডস্পিকার অন রাখা (Enable Speakerphone)**:
   ```dart
   import 'package:flutter_webrtc/flutter_webrtc.dart';

   // কল কানেক্ট হওয়ার সাথে সাথে অডিও আউটপুট স্পিকারে দিন:
   Helper.setSpeakerphoneOn(true);
   ```

2. **লোকাল ক্যামেরা ও মাইক চালু করা (Local User Media)**:
   ```dart
   final Map<String, dynamic> mediaConstraints = {
     'audio': true,
     'video': {
       'facingMode': 'user', // Front camera
       'width': {'ideal': 640},
       'height': {'ideal': 480},
     },
   };
   MediaStream localStream = await navigator.mediaDevices.getUserMedia(mediaConstraints);
   _localRenderer.srcObject = localStream;

   // PeerConnection-এ ট্র্যাক অ্যাড করা
   for (var track in localStream.getTracks()) {
     await _peerConnection!.addTrack(track, localStream);
   }
   ```

3. **রিমোট ভিডিও ভিউ দেখানো (Remote Track Handling)**:
   ```dart
   _peerConnection!.onTrack = (RTCTrackEvent event) {
     if (event.streams.isNotEmpty) {
       setState(() {
         _remoteRenderer.srcObject = event.streams[0]; // অপরের লাইভ ভিডিও ব্যাকগ্রাউন্ডে চলে আসবে
       });
     }
   };
   ```

---

## 🟢 21. User Online / Inactive Presence & Heartbeat Tracking APIs (অনলাইন / ইন-অ্যাক্টিভ ও ডিভাইস পুশ টোকেন সিস্টেম)

অ্যাপে ইউজার বা হোস্ট আসলেই সক্রিয় আছে কিনা তা যাচাই করার জন্য ব্যাকএন্ডে **Dynamic Real-Time Heartbeat & Presence Engine** ইমপ্লিমেন্ট করা হয়েছে।

### 📌 মূল নীতি (Core Business Rules):
1. **সবাইকে ডিফল্ট অনলাইন দেখাবে না**:
   - পূর্বে সব অ্যাক্টিভ ইউজারকে স্ট্যাটাস `Online` দেখানো হচ্ছিল।
   - এখন থেকে যে ইউজার গত **৫ মিনিটের মধ্যে অ্যাপ ওপেন করেছে বা হার্টবিট পাঠিয়েছে** কেবল তাকে `Online` (`is_online = true`, `status_text: 'Online'`) দেখাবে।
   - যে ইউজার অ্যাপে নেই বা ৫ মিনিট ধরে কোনো অ্যাক্টিভিটি নেই, তাকে সরাসরি **`Inactive` বা `Offline` (`is_online = false`, `status_text: 'Inactive'`)** দেখাবে।
2. **কল চলাকালীন স্ট্যাটাস**:
   - যখন কোনো ইউজার বা হোস্ট কলে ব্যস্ত থাকবে, তার স্ট্যাটাস স্বয়ংক্রিয়ভাবে **`In Call`** দেখাবে।

---

### 📡 Presence Endpoints Summary:

| মেথড | এন্ডপয়েন্ট | বিবরণ |
| :--- | :--- | :--- |
| `POST` | `/api/user/heartbeat` | অ্যাপ ওপেন থাকলে প্রতি ৩০-৬০ সেকেন্ডে হার্টবিট পাঠাবে (Keep-Alive Online). |
| `POST` | `/api/user/status` | হোস্ট বা ইউজার নিজে থেকে অনলাইন/অফলাইন/ইন-অ্যাক্টিভ/বিজি টগল করবে। |
| `POST` | `/api/user/fcm-token` | মোবাইল ফোনের FCM Device Token সংরক্ষণ করে ব্যাকগ্রাউন্ড ইনকামিং কলের জন্য। |
| `GET` | `/api/user/presence/{id}` | যেকোনো নির্দিষ্ট হোস্টের (যেমন: রুমা) লাইভ অনলাইন/ইন-অ্যাক্টিভ স্ট্যাটাস চেক। |
| `GET` | `/api/users/online` | বর্তমানে লাইভ থাকা সকল অনলাইন হোস্টের তালিকা। |
| `GET` | `/api/call/wait-incoming` | **০-সেকেন্ড লেটেন্সি লং-পোলিং স্ট্রিম** — কল আসার সাথে সাথে মিলিসেকেন্ডে রিসিভারের ফোন রিং বাজাবে! |

---

### 💓 ১. Send App Heartbeat (Keep User Online)
- **এন্ডপয়েন্ট**: `POST /api/user/heartbeat` *(Aliases: `/api/profile/heartbeat`, `/api/user/ping`)*
- **রিকোয়েস্ট হেডার / বডি**:
```json
{
  "device_type": "android",
  "status": "online",
  "fcm_token": "eXample_FCM_Device_Token_Here..."
}
```
- **রেসপন্স (200 OK)**:
```json
{
  "status": true,
  "message": "Heartbeat received. User presence updated to Online.",
  "data": {
    "user_id": 2,
    "account_id": "1000000002",
    "is_online": true,
    "status_text": "Online",
    "online_status": "online",
    "last_seen_at": "2026-08-29T17:50:00.000000Z"
  }
}
```

---

### ❓ Issue 14: "রুমাকে (বা যেকোনো হোস্টকে) কল দিলে তার ফোনে সাথে সাথে কল পৌঁছানো ও রিং বাজানোর সম্পূর্ণ ফ্লাটার ব্যাকগ্রাউন্ড ও লং-পোলিং সল্যুশন"

> 🎯 **সমস্যা**: কলার যখন রুমাকে কল দিচ্ছে, কলারের ফোনে রিং বাজছে কিন্তু রুমার ফোনে কল আসতে দেরি হচ্ছে বা রিং বাজছে না।

#### 🛠️ ১০০% প্রফেশনাল ৩ স্তরের সমাধান (3-Tier Call Delivery Solution):

```
┌─────────────────────────────────────────────────────────────────────────────┐
│  Tier 1: Zero-Latency Long-Polling Stream (/api/call/wait-incoming)        │
│  • অ্যাপ যখন স্ক্রিনে ওপেন থাকে, এই রিকোয়েস্ট সার্ভারের সাথে কানেক্টেড থাকে  │
│  • কলার কল চাপার 50 Milliseconds (তাৎক্ষণিক) এর মধ্যে রুমার ফোন রিং বাজে!   │
├─────────────────────────────────────────────────────────────────────────────┤
│  Tier 2: Firebase FCM High-Priority Data Push Notification                  │
│  • অ্যাপ যদি বন্ধ বা ব্যাকগ্রাউন্ডে থাকে, FCM পুশ রুমার ডিভাইস ওয়েকআপ করে │
│  • ফুলস্ক্রিন ইনকামিং কল ডায়ালার ওপেন করে লাউড রিংটোন বাজায়                 │
├─────────────────────────────────────────────────────────────────────────────┤
│  Tier 3: Standard 2-Second Poller Fallback (/api/call/incoming)            │
│  • যেকোনো নেটওয়ার্ক ড্রপের ব্যাকআপ হিসেবে প্রতি ২ সেকেন্ডে চেক করে         │
└─────────────────────────────────────────────────────────────────────────────┘
```

#### 💻 ফ্লাটার অ্যাপে লং-পোলিং লিসেনার সার্ভিস কোড (`incoming_call_manager.dart`):

```dart
import 'dart:async';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import '../services/call_sound_manager.dart';
import '../screens/incoming_call_screen.dart';

class IncomingCallManager {
  static bool _isListening = false;

  /// অ্যাপ ওপেন হলে (Main / Home Screen-এ) এই মেথড একবার কল করুন
  static void startListening(BuildContext context, int myUserId, String authToken) {
    if (_isListening) return;
    _isListening = true;
    _listenLoop(context, myUserId, authToken);
  }

  static Future<void> _listenLoop(BuildContext context, int myUserId, String authToken) async {
    while (_isListening) {
      try {
        // ⚡ Zero-Latency Long Polling (সার্ভার সাথে সাথে রেসপন্স করে যখনই কল আসে)
        final url = Uri.parse('$baseUrl/api/call/wait-incoming?user_id=$myUserId&timeout=15');
        final response = await http.get(
          url,
          headers: {
            'Accept': 'application/json',
            'Authorization': 'Bearer $authToken',
          },
        ).timeout(const Duration(seconds: 25));

        if (response.statusCode == 200) {
          final res = jsonDecode(response.body);
          if (res['has_incoming_call'] == true && res['data'] != null) {
            final call = res['data'];

            // ১. তাৎক্ষণিক রিংটোন বাজানো শুরু
            CallSoundManager.playIncomingRingtone(call['incoming_ringtone_url']);

            // ২. ইনকামিং কল স্ক্রিন পপআপ করা
            if (context.mounted) {
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (_) => IncomingCallScreen(
                    callId: call['call_id'],
                    channelName: call['channel_name'],
                    callerName: call['caller']['name'],
                    callerAvatar: call['caller']['avatar'],
                    callType: call['call_type'],
                  ),
                ),
              );
            }
          }
        }
      } catch (_) {
        // নেটওয়ার্ক সাময়িক ড্রপ হলে ১ সেকেন্ড পজ দিয়ে পুনরায় কানেক্ট করবে
        await Future.delayed(const Duration(seconds: 1));
      }
    }
  }

  static void stopListening() {
    _isListening = false;
  }
}
```
