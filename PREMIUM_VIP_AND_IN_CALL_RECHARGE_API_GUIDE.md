# Chinchins Live — Premium VIP & In-Call Recharge System RESTful API Documentation
> **Target Audience:** Flutter Mobile App Developers & Backend Integrators  
> **Backend Platform:** Laravel 11 / PHP 8.2  
> **Base URL:** `https://your-domain.com/api` (or `http://localhost:8000/api`)

---

## 📑 Table of Contents
1. [Host Cards: Country Flag & Age Display](#1-host-cards-country-flag--age-display)
2. [Home Screen Floating "Extra Gems" VIP Widget](#2-home-screen-floating-extra-gems-vip-widget)
3. [Premium VIP Privilege Cards & Daily Check-in](#3-premium-vip-privilege-cards--daily-check-in)
4. [In-Call 16-Second Preview & Quick Promo Recharge Modal](#4-in-call-16-second-preview--quick-promo-recharge-modal)
5. [Admin Panel URL Endpoints](#5-admin-panel-url-endpoints)

---

## 1. Host Cards: Country Flag & Age Display

On the Mobile App Home Feed grid (`/home`), every host card returns full localized country and age attributes so the UI can display badges like `🇵🇭 39` or `🇧🇩 25`.

### 📌 API Endpoint
`GET /api/home` or `GET /api/users`

#### Headers
```http
Accept: application/json
Authorization: Bearer <user_token> (Optional)
```

#### JSON Response Structure
```json
{
  "status": true,
  "message": "Home feed loaded successfully from database",
  "data": {
    "users": [
      {
        "id": 14,
        "account_id": "84920183",
        "display_name": "Honey 🥰",
        "avatar_url": "https://chinchins.live/uploads/profiles/host_14.jpg",
        "country": "Philippines",
        "country_code": "PH",
        "country_flag": "🇵🇭",
        "age": 39,
        "display_age": 39,
        "is_online": true,
        "video_call_rate": 1800,
        "is_verified": true
      },
      {
        "id": 22,
        "account_id": "93820174",
        "display_name": "Sherlyn Cho...",
        "avatar_url": "https://chinchins.live/uploads/profiles/host_22.jpg",
        "country": "Bangladesh",
        "country_code": "BD",
        "country_flag": "🇧🇩",
        "age": 24,
        "display_age": 24,
        "is_online": true,
        "video_call_rate": 1800,
        "is_verified": true
      }
    ]
  }
}
```

#### 📱 Flutter Implementation Example
```dart
// Host Age & Country Flag Widget
Widget buildHostBadge(User user) {
  return Container(
    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
    decoration: BoxDecoration(
      color: Colors.black.withOpacity(0.55),
      borderRadius: BorderRadius.circular(12),
    ),
    child: Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Text(user.countryFlag, style: const TextStyle(fontSize: 12)), // e.g. 🇵🇭
        const SizedBox(width: 4),
        Text(
          "${user.displayAge}",
          style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
        ),
      ],
    ),
  );
}
```

---

## 2. Home Screen Floating "Extra Gems" VIP Widget

A draggable / movable floating widget displayed in the bottom-left/corner of the home screen with a close (`X`) button and "Extra Gems" card graphic.

### 📌 API Endpoints
- `GET /api/app/config` or `GET /api/app/remote-config`
- `GET /api/vip-cards/banner` or `GET /api/premium-vip/banner`

#### Response Example
```json
{
  "status": true,
  "message": "Floating VIP banner retrieved successfully.",
  "data": {
    "is_enabled": true,
    "title": "Extra Gems",
    "tag": "Monthly Card",
    "image_url": "https://chinchins.live/assets/images/vip/floating_extra_gems.png",
    "action_type": "OPEN_PREMIUM_VIP",
    "target_screen": "/premium-vip"
  }
}
```

#### 📱 Flutter Floating Widget Behavior
- **Tap on Widget:** Navigates directly to `PremiumVipScreen()`.
- **Tap on (X) Button:** Dismisses the floating widget for the current session.
- **Draggable:** Supports `Positioned` + `GestureDetector(onPanUpdate: ...)` to drag anywhere across the screen.

---

## 3. Premium VIP Privilege Cards & Daily Check-in

A luxury dark-themed VIP privilege screen presenting **"Luxury Monthly Card"**, **"Super Weekly Card"**, and **"Super Monthly Card"**.

### 📌 API 3.1: Get All VIP Privilege Cards
`GET /api/vip-cards` or `GET /api/premium-vip/cards`

#### Response Example (Matches Screenshots 2 & 4)
```json
{
  "status": true,
  "message": "Premium VIP cards and privileges retrieved successfully.",
  "data": {
    "banner": {
      "title": "Spend Less, Get More Gems!",
      "subtitle": "Update to New User Weekly Card",
      "action_type": "OPEN_PREMIUM_VIP"
    },
    "cards": [
      {
        "id": 1,
        "card_type": "luxury_monthly",
        "name": "Luxury Monthly Card",
        "category_name": "Luxury Monthly Card",
        "badge_text": "50% OFF",
        "price_bdt": 2400.0,
        "original_price_bdt": 4800.0,
        "formatted_price_bdt": "৳ 2400",
        "formatted_original_price_bdt": "৳ 4,800.00",
        "discount_percent": 50,
        "price_coins": 66600,
        "duration_days": 30,
        "instant_reward_coins": 66600,
        "instant_reward_text": "Gems in total 66600",
        "daily_checkin_total_coins": 87110,
        "daily_checkin_text": "Gems in total 87110",
        "total_return_coins": 153710,
        "card_color": "#2979FF",
        "extra_rewards": [
          {
            "title": "30 Days Diamond Frame",
            "tag": "30days",
            "validity": "30days",
            "icon": "frame_diamond",
            "image_url": "https://chinchins.live/uploads/vip_cards/frame_30d.png"
          },
          {
            "title": "30 Days Chat Bubble",
            "tag": "30days",
            "validity": "30days",
            "icon": "chat_bubble",
            "image_url": "https://chinchins.live/uploads/vip_cards/bubble_30d.png"
          },
          {
            "title": "30 Days VIP Title Badge",
            "tag": "30days",
            "validity": "30days",
            "icon": "svip_crown",
            "image_url": "https://chinchins.live/uploads/vip_cards/badge_30d.png"
          },
          {
            "title": "Bonus Lucky Cards x30",
            "tag": "x30",
            "validity": "x30",
            "icon": "lucky_card",
            "image_url": "https://chinchins.live/uploads/vip_cards/card_x30.png"
          }
        ],
        "user_subscription": {
          "is_subscribed": false,
          "remaining_seconds": 0,
          "has_claimed_today": false
        }
      },
      {
        "id": 2,
        "card_type": "super_weekly",
        "name": "Super Weekly Card",
        "category_name": "Super Weekly Card",
        "badge_text": "HOT",
        "price_bdt": 450.0,
        "original_price_bdt": 642.86,
        "formatted_price_bdt": "৳ 450",
        "formatted_original_price_bdt": "৳ 642.86",
        "discount_percent": 30,
        "price_coins": 12150,
        "duration_days": 7,
        "instant_reward_coins": 12150,
        "instant_reward_text": "Gems in total 12150",
        "daily_checkin_total_coins": 2540,
        "daily_checkin_text": "Gems in total 2540",
        "total_return_coins": 14690,
        "card_color": "#FF4081",
        "extra_rewards": [
          {
            "title": "7 Days Emerald Frame",
            "tag": "7days",
            "validity": "7days",
            "icon": "frame_avatar",
            "image_url": null
          },
          {
            "title": "7 Days Chat Bubble",
            "tag": "7days",
            "validity": "7days",
            "icon": "chat_bubble",
            "image_url": null
          },
          {
            "title": "7 Days VIP Badge",
            "tag": "7days",
            "validity": "7days",
            "icon": "badge_svip",
            "image_url": null
          },
          {
            "title": "Bonus Lucky Cards x7",
            "tag": "x7",
            "validity": "x7",
            "icon": "lucky_card",
            "image_url": null
          }
        ],
        "user_subscription": {
          "is_subscribed": false,
          "remaining_seconds": 0,
          "has_claimed_today": false
        }
      }
    ]
  }
}
```

---

### 📌 API 3.2: Purchase VIP Privilege Card
`POST /api/vip-cards/purchase` or `POST /api/premium-vip/purchase`

#### Request Body (JSON)
```json
{
  "vip_card_id": 1,
  "payment_method": "coins"
}
```
*(Options for `payment_method`: `"coins"`, `"wallet"`, `"bkash"`, `"nagad"`)*

#### Success Response (200 OK)
```json
{
  "status": true,
  "message": "Congratulations! You have successfully purchased Luxury Monthly Card.",
  "data": {
    "subscription_id": 8,
    "card_name": "Luxury Monthly Card",
    "instant_reward_credited": 66600,
    "wallet_balance": 72400,
    "expires_at": "2026-10-05T14:00:00Z"
  }
}
```

---

### 📌 API 3.3: Claim Today's VIP Daily Check-in Gems
`POST /api/vip-cards/claim-daily` or `POST /api/premium-vip/claim-daily`

#### Request Body
```json
{
  "subscription_id": 8
}
```

#### Success Response
```json
{
  "status": true,
  "message": "Today's daily check-in reward claimed successfully!",
  "data": {
    "day": 2,
    "coins_awarded": 3500,
    "extra_awarded": null,
    "new_balance": 75900
  }
}
```

---

## 4. In-Call 16-Second Preview & Quick Promo Recharge Modal

When a video call starts, if the user is on the free preview trial or runs out of gems, after **16 seconds**:
1. Remote host's video stream is blurred / audio paused.
2. Caller's camera remains active in the top PIP view.
3. The **"Continue Video Call"** modal pops up in front of the call (Screenshot 3).

### 📌 API 4.1: In-Call Recharge Offer Config
`GET /api/call/config` or `GET /api/call/in-call-recharge-offer`

#### JSON Response Structure
```json
{
  "status": true,
  "data": {
    "free_call_duration_seconds": 16,
    "video_call_rate_per_minute": 1800,
    "in_call_recharge_offer": {
      "preview_seconds": 16,
      "rate_per_minute": 1800,
      "rate_text": "Continue Video Call 💎 1800/min",
      "promo_coins": 7560,
      "promo_price_bdt": 150.0,
      "formatted_promo_price_bdt": "BDT 150.00",
      "promo_original_price_bdt": 300.0,
      "formatted_original_price": "BDT 300.00",
      "discount_badge": "50% OFF",
      "teaser_text": "Girls are still eagerly waiting for your reply. Recharge and enjoy happy time with her now~",
      "button_text": "Get Coins"
    }
  }
}
```

#### 📱 Modal Components (Matching Screenshot 3)
1. **Top Badge:** `"Continue Video Call 💎 1800/min"`
2. **Top Graphic:** Glowing Golden Diamond.
3. **Left Avatar Box:** Active Host Profile Image (`caller.host.avatar_url`).
4. **Right Description Text:** `"Girls are still eagerly waiting for your reply. Recharge and enjoy happy time with her now~"`
5. **Quick Offer Box:** `💎 7560` `BDT 150.00`
6. **Action Button:** Full-width orange/coral button **"Get Coins"** which opens the deposit/payment sheet.
7. **Close Button (X):** Ends or cancels the preview call.

---

## 5. Admin Panel URL Endpoints

| Feature | Admin Panel Route | Controller Action |
|---|---|---|
| **Premium VIP Cards** | `/admin/vip-cards` | `VipCardAdminController@index` |
| **Floating Home Banner** | `/admin/vip-cards/floating-banner` | `VipCardAdminController@updateFloatingBanner` |
| **VIP Subscriptions** | `/admin/vip-cards/subscriptions` | `VipCardAdminController@subscriptions` |
| **In-Call 16s Preview & Promo** | `/admin/calls/settings` | `CallAdminController@settings` |
