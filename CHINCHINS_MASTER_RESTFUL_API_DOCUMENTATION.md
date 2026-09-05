# Chinchins Live — RESTful API & Mobile App Developer Documentation
### WebRTC 1-on-1 Video Calling, Free Host (0-Balance) Engine, 16s Free Preview, In-Call Recharge Modal & 50/50 Revenue Split

---

## 🌟 1. System Overview & Business Flow

This documentation explains the complete workflow for mobile app developers (Flutter / Kotlin / Swift) and backend consumers.

### Key Logic & Rules:
1. **Free Hosts (এডমিন প্যানেল থেকে ফ্রি করে দেওয়া হোস্ট/মেয়েরা)**:
   - Admin can designate specific female users/hosts as **Free Host** (`is_free_caller = true`).
   - A Free Host can initiate audio or video calls to any user with **0 coin balance** in her wallet.
   - **0 coins** are deducted from her account when making calls.
2. **16-Second Free Preview (১৬ সেকেন্ড ফ্রি টকটাইম)**:
   - When the receiver answers, both parties can talk and see each other for the initial **16 seconds** (configurable in Admin Panel) for **FREE**.
   - In-call top floating banner shows: `"After 16 seconds, you will be charged 100 coins per minute."` (with real-time countdown).
3. **Post-Free Period & Insufficient Balance (রিচার্জ মডাল ও ব্লার ইফেক্ট)**:
   - After the 16s free preview ends, the system checks the customer's coin balance.
   - If customer has `< 100 coins`:
     - The in-call video is automatically **blurred** (or muted) and the call is paused.
     - The **Recharge Gems Modal Sheet** pops up immediately with host teaser message: `"Let's play baby! Recharge and call me,I want to show you 💋"` and gem package grid (`7560 Gems @ BDT 150.00 [50% OFF]`, `8100 Gems @ BDT 300.00`, etc.).
     - The call is NOT hung up immediately; customer can recharge and resume the video stream smoothly!
4. **Per-Minute Billing & 50/50 Revenue Sharing (১০০ কয়েন/মিনিট ও ৫০/৫০ ভাগাভাগি)**:
   - When customer has sufficient coins, **100 coins per minute** (~1.67 coins/sec) is deducted.
   - **50% (50 coins)** is automatically credited to the female host's wallet (`host_earned_coins`).
   - **50% (50 coins)** is platform admin revenue (`admin_revenue_coins`).
5. **In-Call Quick Icebreaker Chat (কুইক চ্যাট চিপস)**:
   - In-call bottom chips: `"Be my girlfriend"`, `"Hi , what's up babe?"`, etc.
   - Customers get **2 Free Message Chances** during the call.

---

## 📱 2. Mobile App UI Screens Breakdown

```
+-----------------------------------+     +-----------------------------------+     +-----------------------------------+
|  1. Incoming Call Screen          |     |  2. Active Video Call Screen      |     |  3. Recharge Gems Modal Sheet     |
|                                   |     |                                   |     |                                   |
|   [ Fullscreen Caller Photo ]     |     |   [ Fullscreen Remote Video ]     |     |  [Host Avatar] "Let's play baby!  |
|                                   |     |                                   |     |  Recharge and call me..."     [X] |
|   +---------------------------+   |     |   +------------+ [00:11]          |     |                                   |
|   | VIDEO NOW!                |   |     |   | PIP Local  | (Top Right)      |     |  +-------------+ +-------------+  |
|   | Sexy Girl request chat!   |   |     |   +------------+                  |     |  | 7560 Gems   | | 8100 Gems   |  |
|   +---------------------------+   |     |                                   |     |  | BDT 150.00  | | BDT 300.00  |  |
|                                   |     |   [🔔 After 14s you'll be charged]|     |  | [50% OFF]   | | [17% OFF]   |  |
|   (Decline)           (Accept)    |     |   [💎 7560 / BDT 150.00] (Mini)   |     |  +-------------+ +-------------+  |
|    [ 📞 Red ]       [ 📞 Green ]  |     |   [Be my girlfriend] [Hi babe]    |     |  💎 My Gems: 0                    |
|                      (Pulsing)    |     |   You have 2 free message chances |     |  [       Continue Button       ]  |
+-----------------------------------+     +-----------------------------------+     +-----------------------------------+
```

---

## 📡 3. Complete RESTful Endpoints Reference

### Base URL: `https://your-domain.com/api` (or `http://localhost:8000/api`)

---

### 🟢 1. Get Call Settings, Rates & User Eligibility
Retrieve global calling rates, free preview duration, revenue splits, and current user eligibility.

- **Endpoint**: `GET /api/call/config` (or `GET /api/call/settings`)
- **Headers**:
  ```http
  Authorization: Bearer <SANCTUM_TOKEN>
  Accept: application/json
  ```
- **Response (200 OK)**:
  ```json
  {
    "status": true,
    "message": "Call settings and rates retrieved successfully.",
    "data": {
      "is_call_enabled": true,
      "is_free_call_enabled": true,
      "free_call_duration_seconds": 16,
      "free_calls_per_user": 1,
      "free_message_chances": 2,
      "video_call_rate_per_minute": 100,
      "audio_call_rate_per_minute": 100,
      "host_earning_percent": 50.0,
      "admin_commission_percent": 50.0,
      "call_recharge_teaser_text": "Let's play baby! Recharge and call me,I want to show you 💋",
      "call_top_badge_text": "VIDEO NOW! Sexy Girl request video chat!",
      "call_quick_messages": [
        "Be my girlfriend",
        "Hi , what's up babe?",
        "Can we talk privately?",
        "You look so pretty! ❤️"
      ],
      "incoming_ringtone_url": "https://assets.mixkit.co/active_storage/sfx/2874/2874-preview.mp3",
      "outgoing_ringtone_url": "https://assets.mixkit.co/active_storage/sfx/1359/1359-preview.mp3",
      "video_split": {
        "total_rate": 100,
        "host_receives": 50,
        "admin_revenue": 50
      },
      "user": {
        "user_id": 1,
        "account_id": "84729104",
        "display_name": "Suhavi",
        "gender": "female",
        "is_free_caller": true,
        "coins": 0,
        "formatted_coins": "0 Coins",
        "is_eligible_for_free_call": true,
        "free_trial_duration_seconds": 16,
        "can_make_video_call": true,
        "can_make_audio_call": true,
        "max_video_minutes": 999999
      }
    }
  }
  ```

---

### 📞 2. Initiate Call (Caller Host / User)
Initiate a 1-on-1 audio or video call. If caller is a **Free Host** (`is_free_caller = true`), bypasses 0 balance check and sets `free_duration_seconds = 16`.

- **Endpoint**: `POST /api/call/initiate`
- **Request Body**:
  ```json
  {
    "receiver_id": 2,
    "call_type": "video"
  }
  ```
- **Success Response (200 OK)**:
  ```json
  {
    "status": true,
    "message": "Free trial call initiated! Ringing receiver... You have 16 seconds of free preview calling.",
    "data": {
      "call_id": 12,
      "channel_name": "call_video_1_2_1772778899_aB3d",
      "call_type": "video",
      "status": "ringing",
      "rate_per_minute": 100,
      "is_free_trial": true,
      "is_caller_free": true,
      "free_duration_seconds": 16,
      "free_message_chances": 2,
      "call_recharge_teaser_text": "Let's play baby! Recharge and call me,I want to show you 💋",
      "call_top_badge_text": "VIDEO NOW! Sexy Girl request video chat!",
      "call_quick_messages": [
        "Be my girlfriend",
        "Hi , what's up babe?"
      ],
      "caller_coins": 0,
      "ring_timeout_seconds": 45,
      "outgoing_ringtone_url": "https://assets.mixkit.co/active_storage/sfx/1359/1359-preview.mp3",
      "receiver": {
        "id": 2,
        "account_id": "91028473",
        "name": "Rakib Hasan",
        "gender": "male",
        "avatar": "https://your-domain.com/uploads/profiles/user_2.jpg"
      }
    }
  }
  ```

---

### 📲 3. Check for Incoming Calls (Receiver App Polling / Push)
Receiver device polls this or listens to WebSocket. When call arrives, app displays the **Incoming Call Fullscreen Screen** (Screenshot 1).

- **Endpoint**: `GET /api/call/incoming` (or `GET /api/call/wait-incoming`)
- **Query / Body**: `?user_id=2`
- **Response (When Call Ringing)**:
  ```json
  {
    "status": true,
    "has_incoming_call": true,
    "message": "Incoming call detected! Ring device.",
    "data": {
      "call_id": 12,
      "channel_name": "call_video_1_2_1772778899_aB3d",
      "call_type": "video",
      "status": "ringing",
      "is_free_trial": true,
      "is_caller_free": true,
      "free_duration_seconds": 16,
      "rate_per_minute": 100,
      "call_top_badge_text": "VIDEO NOW! Sexy Girl request video chat!",
      "call_recharge_teaser_text": "Let's play baby! Recharge and call me,I want to show you 💋",
      "ring_elapsed_seconds": 3,
      "ring_timeout_seconds": 42,
      "incoming_ringtone_url": "https://assets.mixkit.co/active_storage/sfx/2874/2874-preview.mp3",
      "caller": {
        "id": 1,
        "account_id": "84729104",
        "name": "♡Suhavi♡",
        "avatar": "https://your-domain.com/uploads/profiles/suhavi.jpg",
        "gender": "female",
        "country": "Rajasthan",
        "level": "Lv26",
        "is_free_caller": true
      }
    }
  }
  ```

---

### 🟢 4. Accept / Receive Call
Receiver clicks the green **Accept** button. Transitions call state from `'ringing'` to `'connected'` and starts WebRTC audio/video stream.

- **Endpoint**: `POST /api/call/accept`
- **Request Body**:
  ```json
  {
    "call_id": 12
  }
  ```
- **Response (200 OK)**:
  ```json
  {
    "status": true,
    "message": "Call accepted and connected successfully! Start audio/video media stream.",
    "data": {
      "call_id": 12,
      "channel_name": "call_video_1_2_1772778899_aB3d",
      "call_type": "video",
      "status": "connected",
      "started_at": "2026-09-05T12:00:00.000000Z",
      "rate_per_minute": 100,
      "is_free_trial": true,
      "free_duration_seconds": 16
    }
  }
  ```

---

### ⏱️ 5. In-Call Pulse Billing Heartbeat (Every 1-5 Seconds)
Mobile app sends periodic pulse during active call. Automatically tracks the 16s free period, triggers the **Recharge Sheet & Video Blur** when balance is 0, or splits 50/50 when coins are deducted.

- **Endpoint**: `POST /api/call/deduct-interval` (or `POST /api/call/pulse`)
- **Request Body**:
  ```json
  {
    "call_id": 12,
    "elapsed_seconds": 10,
    "interval_seconds": 5
  }
  ```

#### Scenario A: During 16s Free Preview (`elapsed_seconds < 16`)
```json
{
  "status": true,
  "is_free_trial": true,
  "free_seconds_remaining": 6,
  "free_duration_seconds": 16,
  "should_blur_video": false,
  "is_video_blurred": false,
  "message": "Free preview active (6s remaining).",
  "data": {
    "current_coins": 0,
    "coins_deducted": 0,
    "rate_per_minute": 100,
    "can_continue": true,
    "should_terminate_call": false
  }
}
```

#### Scenario B: Free Preview Expired & Zero Balance (`elapsed_seconds >= 16`, `coins = 0`)
App blurs remote video, mutes audio, and opens **Recharge Gems BottomSheet**!
```json
{
  "status": false,
  "code": "LOW_BALANCE_DEPOSIT_REQUIRED",
  "message": "Free preview ended. Your balance is insufficient to continue calling. Please deposit/recharge coins now.",
  "current_coins": 0,
  "required_coins": 100,
  "rate_per_minute": 100,
  "should_terminate_call": false,
  "should_blur_video": true,
  "is_video_blurred": true,
  "should_mute_audio": true,
  "show_recharge_sheet": true,
  "teaser_text": "Let's play baby! Recharge and call me,I want to show you 💋",
  "packages": [
    { "id": 1, "title": "Starter Pack", "coins": 7560, "price": 150.00, "badge": "50% off", "is_popular": true },
    { "id": 2, "title": "Basic Pack", "coins": 8100, "price": 300.00, "badge": "17% off" },
    { "id": 3, "title": "Popular Pack", "coins": 16380, "price": 600.00, "badge": "17% off" },
    { "id": 4, "title": "Super Pack", "coins": 32940, "price": 1200.00, "badge": "30% off" },
    { "id": 5, "title": "Mega Pack", "coins": 66600, "price": 2400.00, "badge": "60% off" },
    { "id": 6, "title": "VIP King Pack", "coins": 167400, "price": 6100.00, "badge": "80% off" }
  ]
}
```

#### Scenario C: Paid Calling with Coins (50/50 Revenue Split)
```json
{
  "status": true,
  "should_blur_video": false,
  "is_video_blurred": false,
  "message": "Deducted 100 coins (Rate: 100 coins/min). Host earned 50 coins (50%). Admin revenue 50 coins (50%).",
  "data": {
    "current_coins": 900,
    "coins_deducted": 100,
    "host_earned_coins": 50,
    "admin_revenue_coins": 50,
    "total_call_coins_deducted": 100,
    "rate_per_minute": 100,
    "can_continue": true,
    "should_terminate_call": false
  }
}
```

---

### 💎 6. Get In-Call Recharge Sheet Modal Data
Fetch packages grid, discount tags, teaser copy, and user's current gems balance for bottom sheet modal (Screenshots 2 & 5).

- **Endpoint**: `GET /api/call/recharge-sheet`
- **Query**: `?user_id=2&host_id=1`
- **Response (200 OK)**:
  ```json
  {
    "status": true,
    "message": "Recharge sheet data retrieved successfully.",
    "data": {
      "teaser_text": "Let's play baby! Recharge and call me,I want to show you 💋",
      "user_gems": 0,
      "formatted_user_gems": "My Gems: 0",
      "host": {
        "id": 1,
        "account_id": "84729104",
        "name": "♡Suhavi♡",
        "avatar_url": "https://your-domain.com/uploads/profiles/suhavi.jpg"
      },
      "packages": [
        {
          "id": 1,
          "title": "Starter Pack",
          "coins": 7560,
          "total_coins": 7560,
          "price": 150.00,
          "formatted_price": "BDT 150.00",
          "badge": "50% off",
          "badge_color": "danger",
          "is_popular": true,
          "tag": "ONCE"
        },
        {
          "id": 2,
          "title": "Basic Pack",
          "coins": 8100,
          "total_coins": 8100,
          "price": 300.00,
          "formatted_price": "BDT 300.00",
          "badge": "17% off",
          "badge_color": "pink",
          "is_popular": false,
          "tag": null
        },
        {
          "id": 3,
          "title": "Popular Pack",
          "coins": 16380,
          "total_coins": 16380,
          "price": 600.00,
          "formatted_price": "BDT 600.00",
          "badge": "17% off",
          "badge_color": "pink",
          "is_popular": false,
          "tag": null
        },
        {
          "id": 4,
          "title": "Super Pack",
          "coins": 32940,
          "total_coins": 32940,
          "price": 1200.00,
          "formatted_price": "BDT 1,200.00",
          "badge": "30% off",
          "badge_color": "pink",
          "is_popular": false,
          "tag": null
        },
        {
          "id": 5,
          "title": "Mega Pack",
          "coins": 66600,
          "total_coins": 66600,
          "price": 2400.00,
          "formatted_price": "BDT 2,400.00",
          "badge": "60% off",
          "badge_color": "pink",
          "is_popular": false,
          "tag": null
        },
        {
          "id": 6,
          "title": "VIP King Pack",
          "coins": 167400,
          "total_coins": 167400,
          "price": 6100.00,
          "formatted_price": "BDT 6,100.00",
          "badge": "80% off",
          "badge_color": "danger",
          "is_popular": false,
          "tag": null
        }
      ],
      "rate_per_minute": 100
    }
  }
  ```

---

### 💬 7. In-Call Quick Icebreaker Messages & Free Chances
- **Get Messages**: `GET /api/call/quick-messages`
  ```json
  {
    "status": true,
    "data": {
      "messages": [
        "Be my girlfriend",
        "Hi , what's up babe?",
        "Can we talk privately?",
        "You look so pretty! ❤️"
      ],
      "free_chances_total": 2,
      "free_chances_remaining": 2,
      "free_chances_label": "You have 2 free message chances"
    }
  }
  ```
- **Send Message**: `POST /api/call/send-quick-message`
  ```json
  {
    "call_id": 12,
    "receiver_id": 1,
    "message": "Be my girlfriend"
  }
  ```

---

### 🛑 8. End Call Session
Ends call, records final duration, calculates coins, and automatically creates chat thread summary.

- **Endpoint**: `POST /api/call/end`
- **Request Body**:
  ```json
  {
    "call_id": 12,
    "duration_seconds": 120
  }
  ```
- **Response (200 OK)**:
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
      "partner": {
        "id": 1,
        "name": "♡Suhavi♡",
        "avatar_url": "https://your-domain.com/uploads/profiles/suhavi.jpg"
      }
    }
  }
  ```

---

## 🛠️ 4. Admin Panel Operations Summary

1. **Make a Female Host "Free Caller"**:
   - Go to **Admin Panel -> Users Management (`/admin/users`)**.
   - Click the yellow **"Set Free" / "Free Host"** button next to any host.
   - Alternatively, open the user's details page (`/admin/users/{id}`) and click **"Make Free Host (Unlimited 0 Bal Calls)"**.
2. **Configure Free Preview Duration & Rates**:
   - Go to **Admin Panel -> Call Settings (`/admin/calls/settings`)**.
   - Set **Free Trial Duration** (e.g. `16` seconds).
   - Set **Video Call Rate** (e.g. `100` coins/min).
   - Set **Revenue Split** (e.g. `50%` Host, `50%` Admin).
   - Customize **Recharge Teaser Text**, **Top Badge Text**, and **Quick Messages Chips**.
   - Click **"Save Call & Revenue Settings"**.
3. **Manage Coin / Gems Packages**:
   - Go to **Admin Panel -> Coin Packages (`/admin/coin-packages`)** to edit prices, gem counts, and discount badges.
