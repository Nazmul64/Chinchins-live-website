# 💎 Spend Less, Get More Gems (Monthly & Weekly Cards & Extra Rewards) RESTful API Documentation

This document provides comprehensive, production-ready RESTful API specifications for the **"Spend Less, Get More Gems!" / Monthly & Weekly VIP Privilege Cards & Extra Rewards** system on the **Chinchins Live** platform.

---

## 🌟 Overview & Feature Matrix

The **Spend Less, Get More Gems** module enables users to buy discounted VIP privilege packages offering:
1. **Instant Gems Reward** (Credited immediately to user wallet upon purchase).
2. **Daily Check-in Bonus Gems** (Claimable once every 24 hours over the 7-day or 30-day card duration).
3. **Extra Outfits & Animated Assets**:
   - **3D Animated Avatar Frames** (e.g. *7 Days NEW STAR Frame*, *30 Days Gold Frame*, *Diamond Frame*).
   - **Chat Bubbles & Nickname Glows**.
   - **SVIP Badges & Room Entry Animations**.
   - **Bonus Lucky Cards / Outfits**.
4. **Day-by-Day Reward Schedules** (Structured 1st, 2nd, 3rd ... 7th / 30th day breakdown).
5. **Real-time Countdown Timers & Promotional Badges** (*50% OFF*, *60% OFF*, *30% OFF*).

---

## 📡 Base URLs & Endpoints Summary

| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/spend-less-get-more` *(or `/api/vip-cards`, `/api/monthly-cards`)* | Get all active Monthly & Weekly VIP Card packages, daily schedules, extra perks & countdown timers | Optional (Returns subscription if authed) |
| `GET` | `/api/spend-less-get-more/banner` *(or `/api/vip-cards/banner`)* | Get Home Screen floating "Extra Gems" widget configuration | No |
| `GET` | `/api/spend-less-get-more/my` *(or `/api/vip-cards/my-subscriptions`)* | Get authenticated user's active card subscriptions & daily check-in claim status | Yes (`Bearer Token` / `user_id`) |
| `POST` | `/api/spend-less-get-more/purchase` *(or `/api/vip-cards/purchase`)* | Purchase a Monthly/Weekly Card using wallet balance / gems | Yes (`Bearer Token` / `user_id`) |
| `POST` | `/api/spend-less-get-more/claim` *(or `/api/vip-cards/claim-daily`)* | Claim today's daily check-in gems & perks for an active card | Yes (`Bearer Token` / `user_id`) |
| `POST` | `/api/admin/vip-cards` | Create a new VIP / Monthly Card package | Admin |
| `PUT` | `/api/admin/vip-cards/{id}` | Update existing VIP / Monthly Card package | Admin |
| `DELETE`| `/api/admin/vip-cards/{id}` | Delete VIP / Monthly Card package | Admin |

---

## 1. 📋 Get All Monthly & Weekly Packages

### Request
```http
GET /api/spend-less-get-more
Accept: application/json
Authorization: Bearer {token} (Optional)
```

### Response Example (`200 OK`)
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
    "floating_banner": {
      "is_enabled": true,
      "title": "Extra Gems",
      "tag": "Monthly Card",
      "image_url": "https://chinchins.live/assets/images/vip/floating_extra_gems.png",
      "action_type": "OPEN_PREMIUM_VIP",
      "target_screen": "/premium-vip"
    },
    "cards": [
      {
        "id": 1,
        "card_type": "new_user_weekly",
        "name": "New User Weekly Card",
        "category_name": "New User Weekly Card",
        "badge_text": "60% OFF",
        "price_bdt": 300.00,
        "original_price_bdt": 750.00,
        "formatted_price_bdt": "৳ 300",
        "formatted_original_price_bdt": "৳ 750.00",
        "discount_percent": 60,
        "price_coins": 8100,
        "duration_days": 7,
        "instant_reward_coins": 8100,
        "instant_reward_text": "Gems in total 8100",
        "daily_checkin_total_coins": 2020,
        "daily_checkin_text": "Gems in total 2020",
        "total_return_coins": 10120,
        "card_color": "#EC4899",
        "banner_tag": "New User Weekly Card: 10,120 Gems + New Star 3D Avatar Frame!",
        "countdown_timer": "02 : 58 : 15 : 481",
        "extra_rewards": [
          {
            "title": "7 Days NEW STAR Frame",
            "tag": "7days",
            "icon": "frame_diamond",
            "image_url": "https://chinchins.live/assets/images/vip/new_star_frame.png"
          },
          {
            "title": "7 Days Newbie Glow",
            "tag": "7days",
            "icon": "chat_bubble",
            "image_url": null
          },
          {
            "title": "Bonus Lucky Cards x7",
            "tag": "x7",
            "icon": "lucky_card",
            "image_url": null
          }
        ],
        "daily_schedule": [
          { "day": 1, "day_label": "1st", "coins": 8100, "extra": "NEW STAR Avatar Frame" },
          { "day": 2, "day_label": "2nd", "coins": 300, "extra": null },
          { "day": 3, "day_label": "3rd", "coins": 210, "extra": null },
          { "day": 4, "day_label": "4th", "coins": 500, "extra": null },
          { "day": 5, "day_label": "5th", "coins": 300, "extra": null },
          { "day": 6, "day_label": "6th", "coins": 210, "extra": null },
          { "day": 7, "day_label": "7th", "coins": 500, "extra": "New Star Title Badge" }
        ],
        "user_subscription": {
          "is_subscribed": false,
          "current_day": 1,
          "has_claimed_today": false,
          "claimed_days": []
        }
      },
      {
        "id": 2,
        "card_type": "super_monthly",
        "name": "Super Monthly Card",
        "category_name": "Super Monthly Card",
        "badge_text": "50% OFF",
        "price_bdt": 1200.00,
        "original_price_bdt": 2400.00,
        "formatted_price_bdt": "৳ 1,200",
        "formatted_original_price_bdt": "৳ 2,400.00",
        "discount_percent": 50,
        "price_coins": 32940,
        "duration_days": 30,
        "instant_reward_coins": 32940,
        "instant_reward_text": "Gems in total 32940",
        "daily_checkin_total_coins": 26330,
        "daily_checkin_text": "Gems in total 26330",
        "total_return_coins": 59270,
        "card_color": "#7C4DFF",
        "banner_tag": "Super Monthly Card: 59,270 Gems + Outfits & Rewards!"
      },
      {
        "id": 3,
        "card_type": "luxury_monthly",
        "name": "Luxury Monthly Card",
        "category_name": "Luxury Monthly Card",
        "badge_text": "50% OFF",
        "price_bdt": 2400.00,
        "original_price_bdt": 4800.00,
        "formatted_price_bdt": "৳ 2,400",
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
        "banner_tag": "Luxury Monthly Card: 153,710 Gems + Outfits + Free Cards!"
      },
      {
        "id": 4,
        "card_type": "super_weekly",
        "name": "Super Weekly Card",
        "category_name": "Super Weekly Card",
        "badge_text": "30% OFF",
        "price_bdt": 450.00,
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
        "banner_tag": "Super Weekly Card: 14,690 Gems + Outfits!"
      }
    ]
  }
}
```

---

## 2. 🛍️ Purchase a Monthly / Weekly Card

### Request
```http
POST /api/spend-less-get-more/purchase
Content-Type: application/json
Authorization: Bearer {token}

{
  "card_id": 1
}
```
*(Or by card type: `{"card_type": "new_user_weekly"}`)*

### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Congratulations! You purchased New User Weekly Card successfully. Instant 8,100 Gems have been credited to your balance.",
  "data": {
    "subscription_id": 12,
    "card_name": "New User Weekly Card",
    "card_type": "new_user_weekly",
    "duration_days": 7,
    "instant_gems_credited": 8100,
    "new_balance": 18952,
    "expires_at": "2026-09-12T21:40:00.000000Z",
    "extra_rewards_unlocked": [
      {
        "title": "7 Days NEW STAR Frame",
        "validity": "7days",
        "image_url": "https://chinchins.live/assets/images/vip/new_star_frame.png"
      }
    ]
  }
}
```

---

## 3. 🎁 Claim Daily Check-in Bonus

### Request
```http
POST /api/spend-less-get-more/claim
Content-Type: application/json
Authorization: Bearer {token}

{
  "subscription_id": 12
}
```

### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Day 2 Daily Check-in Bonus of 300 Gems claimed successfully!",
  "data": {
    "day_number": 2,
    "coins_claimed": 300,
    "extra_reward": null,
    "new_balance": 19252,
    "total_claimed_so_far": 8400,
    "remaining_days": 5
  }
}
```

---

## 4. ⚙️ Admin Panel VIP & Monthly Card Management

In the **Admin Panel Sidebar**, click **"Spend Less, Get More" / "Monthly & Extra Rewards"** to:
- Add, edit, or delete any Card Package.
- Adjust Selling Price (BDT) vs Original Strikethrough Price (BDT).
- Set Instant Reward gems vs Daily Check-in Bonus gems.
- Dynamically build and customize the Day 1 through Day 30 reward schedules.
- Upload custom Avatar Frame assets (PNG/WebP transparent) and preview live animations in the **Extra Rewards Preview Modal**.
