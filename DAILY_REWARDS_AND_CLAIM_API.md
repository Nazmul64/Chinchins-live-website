# 🎁 Chinchins Live — Daily Rewards Claim, VIP Floating Widget & Dynamic Ringtone RESTful API Documentation

This document outlines the complete RESTful API specifications for Flutter & Mobile App developers to integrate:
1. **7-Day Daily Check-in & Claim Rewards Engine** (Dynamic rewards, 12h cooldown, auto streak progression, main coin balance credit).
2. **Home Screen Floating Action Widget / Extra Gems VIP Icon** (Real-time dynamic banner icon & navigation).
3. **Dynamic Call Ringtone & Outgoing Dial Tone Configuration** (Real-time audio URLs updated from Admin Panel).
4. **Global Bootstrap Config Integration** (< 5ms Redis in-memory sync for all app settings).

---

## 📑 Table of Contents
- [1. 7-Day Daily Rewards & Claim Engine](#1-7-day-daily-rewards--claim-engine)
  - [1.1 Get Daily Check-in Status & Popup Trigger](#11-get-daily-check-in-status--popup-trigger)
  - [1.2 Claim Today's Daily Reward](#12-claim-todays-daily-reward)
  - [1.3 Admin Management & Image Uploads](#13-admin-management--image-uploads)
- [2. Home Screen Floating VIP Widget API](#2-home-screen-floating-vip-widget-api)
  - [2.1 Get Floating Widget Configuration](#21-get-floating-widget-configuration)
- [3. Dynamic Call Ringtone & Audio Tone API](#3-dynamic-call-ringtone--audio-tone-api)
  - [3.1 Get Dynamic Call & Ringtone Settings](#31-get-dynamic-call--ringtone-settings)
  - [3.2 Get Audio Ringtone URLs Only](#32-get-audio-ringtone-urls-only)
- [4. Global Bootstrap Config (< 5ms Load)](#4-global-bootstrap-config--5ms-load)
- [5. Flutter Implementation Best Practices](#5-flutter-implementation-best-practices)

---

## 1. 7-Day Daily Rewards & Claim Engine

### 1.1 Get Daily Check-in Status & Popup Trigger
Retrieves the user's current 7-day streak, whether they can claim today, next available claim timestamp, coin balances, and full day-by-day reward configuration (Day 1 through Day 7).

- **Method**: `GET` / `POST`
- **Endpoints**:
  - `GET /api/daily-rewards/status`
  - `GET /api/daily-claim/status` *(Alias)*
  - `GET /api/daily-checkin/status` *(Alias)*
  - `GET /api/daily-rewards` *(Alias)*
- **Headers**:
  ```http
  Authorization: Bearer <SANCTUM_TOKEN>
  Accept: application/json
  ```
  *(Alternative fallback header: `X-User-Id: <USER_ID>`)*

#### ✅ Successful Response (200 OK):
```json
{
  "status": true,
  "should_open_popup": true,
  "can_claim": true,
  "current_streak": 2,
  "next_day_number": 3,
  "next_claim_at": null,
  "tomorrow_reward": {
    "coins": 50,
    "text": "Tomorrow for 50 Reward!"
  },
  "user_current_coins": 1520,
  "days": [
    {
      "day_number": 1,
      "reward_coins": 50,
      "icon_image": "https://chinchins.live/uploads/claim/day_1.svg",
      "is_claimed": true,
      "is_current": false,
      "is_locked": false
    },
    {
      "day_number": 2,
      "reward_coins": 50,
      "icon_image": "https://chinchins.live/uploads/claim/day_2.svg",
      "is_claimed": true,
      "is_current": false,
      "is_locked": false
    },
    {
      "day_number": 3,
      "reward_coins": 50,
      "icon_image": "https://chinchins.live/uploads/claim/day_3.svg",
      "is_claimed": false,
      "is_current": true,
      "is_locked": false
    },
    {
      "day_number": 4,
      "reward_coins": 50,
      "icon_image": "https://chinchins.live/uploads/claim/day_4.svg",
      "is_claimed": false,
      "is_current": false,
      "is_locked": true
    },
    {
      "day_number": 5,
      "reward_coins": 50,
      "icon_image": "https://chinchins.live/uploads/claim/day_5.svg",
      "is_claimed": false,
      "is_current": false,
      "is_locked": true
    },
    {
      "day_number": 6,
      "reward_coins": 50,
      "icon_image": "https://chinchins.live/uploads/claim/day_6.svg",
      "is_claimed": false,
      "is_current": false,
      "is_locked": true
    },
    {
      "day_number": 7,
      "reward_coins": 100,
      "icon_image": "https://chinchins.live/uploads/claim/day_7.svg",
      "is_claimed": false,
      "is_current": false,
      "is_locked": true
    }
  ]
}
```

#### 🔑 Field Descriptions for Flutter:
- `should_open_popup`: When `true`, the mobile app automatically displays the daily reward claim dialog popup upon home screen launch.
- `can_claim`: Whether the claim button is active or disabled.
- `current_streak`: Number of consecutive days completed (0 to 6).
- `next_day_number`: Which day card is active to claim next (1 to 7).
- `next_claim_at`: ISO8601 timestamp when user can claim next if currently on 12h cooldown.
- `icon_image`: Direct full URL to day reward asset (from `public/uploads/claim/`).

---

### 1.2 Claim Today's Daily Reward
Claims the reward for the current active day, credits coins to `users.coins`, logs the transaction in `coin_transactions`, and starts the 12-hour cooldown.

- **Method**: `POST`
- **Endpoints**:
  - `POST /api/daily-rewards/claim`
  - `POST /api/daily-claim/claim` *(Alias)*
  - `POST /api/daily-checkin/claim` *(Alias)*
- **Headers**:
  ```http
  Authorization: Bearer <SANCTUM_TOKEN>
  Accept: application/json
  ```

#### ✅ Successful Claim Response (200 OK):
```json
{
  "status": true,
  "message": "Claimed successfully! +50 coins added.",
  "claimed_day": 3,
  "coins_awarded": 50,
  "total_balance": 1570,
  "next_claim_at": "2026-10-08T22:30:00.000000Z"
}
```

#### ⚠️ Cooldown / Rate Limit Response (422 Unprocessable Entity):
```json
{
  "status": false,
  "message": "Please wait 12 hours before claiming the next reward.",
  "next_available_at": "2026-10-08T22:30:00.000000Z"
}
```

---

### 1.3 Admin Management & Image Uploads
- **Web Admin Route**: `https://chinchins.live/admin/daily-rewards`
- **Upload Storage Path**: `public/uploads/claim/`
- **Supported Formats**: SVG, PNG, JPG, WebP.
- **Dynamic Config**: Admins can change Day 1–7 reward coins, toggle active status, and upload custom icons in real time.

---

## 2. Home Screen Floating VIP Widget API

Allows the Flutter app to render a draggable floating action button on the mobile app home screen (e.g. "Extra Gems" Monthly Card icon). When clicked, it opens the Premium VIP Cards page.

### 2.1 Get Floating Widget Configuration
- **Method**: `GET`
- **Endpoints**:
  - `GET /api/floating-banner`
  - `GET /api/floating-action-icon`
  - `GET /api/floating-widget`
  - `GET /api/v1/floating-banner`

#### ✅ Response (200 OK):
```json
{
  "status": true,
  "success": true,
  "data": {
    "is_enabled": true,
    "title": "Extra Gems",
    "subtitle": "Monthly Card",
    "tag": "Monthly Card",
    "image_url": "https://chinchins.live/uploads/floating_action_icons/extra_gems_widget.png",
    "target_action": "OPEN_PREMIUM_VIP",
    "action_type": "OPEN_PREMIUM_VIP",
    "target_screen": "/premium-vip"
  }
}
```

---

## 3. Dynamic Call Ringtone & Audio Tone API

Admins configure custom ringtones in the web admin panel (`/admin/calls/settings`). The Flutter app fetches these URLs dynamically to play:
1. `incoming_ringtone_url`: Audio played continuously on the receiver's phone during an incoming video/audio call.
2. `outgoing_ringtone_url`: Dial tone played on the caller's phone while waiting for the host to answer.

### 3.1 Get Dynamic Call & Ringtone Settings
- **Method**: `GET`
- **Endpoints**:
  - `GET /api/call-settings`
  - `GET /api/call/settings`
  - `GET /api/v1/call-settings`

#### ✅ Response (200 OK):
```json
{
  "status": true,
  "success": true,
  "message": "Call settings and ringtones retrieved successfully.",
  "data": {
    "is_call_enabled": true,
    "is_free_call_enabled": true,
    "free_call_duration_seconds": 16,
    "free_calls_per_user": 1,
    "video_call_rate_per_minute": 100,
    "audio_call_rate_per_minute": 100,
    "host_earning_percent": 50.0,
    "admin_commission_percent": 50.0,
    "incoming_ringtone_url": "https://chinchins.live/uploads/ringtones/incoming_ringtone_1790517785.mp3",
    "outgoing_ringtone_url": "https://assets.mixkit.co/active_storage/sfx/1359/1359-preview.mp3",
    "in_call_promo_coins": 7560,
    "in_call_promo_price_bdt": 150.0,
    "in_call_promo_original_price_bdt": 300.0,
    "in_call_promo_teaser": "I want to talk more with you. Recharge and call me back~",
    "in_call_promo_badge": "50% OFF"
  }
}
```

### 3.2 Get Audio Ringtone URLs Only
- **Method**: `GET`
- **Endpoints**:
  - `GET /api/ringtones`
  - `GET /api/ringtone-settings`
  - `GET /api/v1/ringtones`

#### ✅ Response (200 OK):
```json
{
  "status": true,
  "success": true,
  "data": {
    "incoming_ringtone_url": "https://chinchins.live/uploads/ringtones/incoming_ringtone_1790517785.mp3",
    "outgoing_ringtone_url": "https://assets.mixkit.co/active_storage/sfx/1359/1359-preview.mp3"
  }
}
```

---

## 4. Global Bootstrap Config (< 5ms Load)

All the above configurations are also bundled into the zero-latency global bootstrap endpoint:
- **Endpoint**: `GET /api/bootstrap-config` (or `/api/app-config`, `/api/v1/bootstrap`)

Response includes:
- `data.ringtone_settings` (incoming & outgoing URLs)
- `data.call_settings` (rates, free preview seconds, promos)
- `data.floating_banner` (home floating widget icon & target action)
- `data.payment_methods`, `data.coin_packages`, `data.gifts_catalog`, `data.level_badges`, `data.vip_frames`

---

## 5. Flutter Implementation Best Practices

### Daily Reward Dialog Logic:
```dart
// Fetch status on home screen load
final response = await http.get(
  Uri.parse('https://chinchins.live/api/daily-rewards/status'),
  headers: {'Authorization': 'Bearer $authToken'},
);

final data = jsonDecode(response.body);
if (data['status'] == true && data['should_open_popup'] == true) {
  // Show Animated Daily Check-in Modal Dialog
  showDailyRewardDialog(context, data);
}

// On Claim Button Click
Future<void> onClaimPressed() async {
  final res = await http.post(
    Uri.parse('https://chinchins.live/api/daily-rewards/claim'),
    headers: {'Authorization': 'Bearer $authToken'},
  );
  final claimData = jsonDecode(res.body);
  if (claimData['status'] == true) {
    // Update local wallet coin balance instantly
    updateUserCoins(claimData['total_balance']);
    showSuccessToast(claimData['message']);
  }
}
```

### Dynamic Ringtone Player:
```dart
// Use audio URL from call settings or bootstrap config
final ringtoneUrl = callSettings['incoming_ringtone_url'];
await audioPlayer.play(UrlSource(ringtoneUrl));
```
