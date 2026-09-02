# 💎 Monthly & Weekly VIP Privilege Cards & Daily Check-in Rewards API Documentation
## 🇧🇩 "Spend Less, Get More Gems!" — মান্থলি ও উইকলি ভিআইপি কার্ড এবং ডেইলি রিওয়ার্ড সিস্টেম

> **সিস্টেম সারসংক্ষেপ (Feature Overview):**
> প্রোফাইল/মি (Me) পেজে জেমস ও বিনস সেকশনের নিচে **"Spend Less, Get More Gems! Update to New User Weekly Card"** ব্যানার থাকবে। ব্যানারে বা রিওয়ার্ড মেনুতে ট্যাপ করলে **Monthly Card / Privilege Cards** পেজ ওপেন হবে।
> 
> ব্যবহারকারী ৪ ধরনের ভিআইপি কার্ড প্যাকেজ দেখতে পাবে:
> 1. **New User Weekly Card** (৭ দিনের মেয়াদ, ৩টি এক্সট্রা আউটফিট/কার্ড, ইনস্ট্যান্ট রিওয়ার্ড + ৭ দিনের শিডিউল বোনাস)
> 2. **Super Monthly Card** (৩০ দিনের মেয়াদ, গোল্ডেন ফ্রেম, ইনস্ট্যান্ট রিওয়ার্ড + ৩০ দিনের শিডিউল বোনাস)
> 3. **Luxury Monthly Card** (৩০ দিনের মেয়াদ, ডায়মন্ড ফ্রেম ও এসভিআইপি ক্রাউন, ইনস্ট্যান্ট রিওয়ার্ড + ৩০ দিনের শিডিউল বোনাস)
> 4. **Super Weekly Card** (৭ দিনের মেয়াদ, নিয়ন ফ্রেম ও ভিআইপি ব্যাজ)
>
> কার্ড কেনার সাথে সাথে ইউজার **ইনস্ট্যান্ট বোনাস কয়েন** পাবে এবং প্রতিদিন কার্ড পেজে ঢুকে **"Get Schedule"** থেকে ওই দিনের ডেইলি চেক-ইন রিওয়ার্ড জেমস ফ্রিতে ক্লেইম (Claim) করতে পারবে যা মূল ওয়ালেট ব্যালেন্সে যোগ হবে।

---

## 📑 সূচিপত্র (Table of Contents)
1. [আর্কিটেকচার ও লাইফসাইকেল ফ্লো (Architecture & Flow)](#1-আর্কিটেকচার-ও-লাইফসাইকেল-ফ্লো)
2. [১০-সংখ্যার ইউনিক অ্যাকাউন্ট আইডি (10-Digit Unique Account ID)](#2-১০-সংখ্যার-ইউনিক-অ্যাকাউন্ট-আইডি)
3. [ডাটাবেজ স্কিমা (Database Schema)](#3-ডাটাবেজ-স্কিমা)
4. [সম্পূর্ণ RESTful API এন্ডপয়েন্টস রেফারেন্স (Full API Reference)](#4-সম্পূর্ণ-restful-api-এন্ডপয়েন্টস-রেফারেন্স)
   - ৪.১. কার্ড ক্যাটালগ ও ব্যানার ডাটা (`GET /api/vip-cards`)
   - ৪.২. ইউজারের সাবস্ক্রিপশন ও ক্লেইম স্ট্যাটাস (`GET /api/vip-cards/my-subscriptions`)
   - ৪.৩. ভিআইপি কার্ড ক্রয়/অ্যাক্টিভেশন (`POST /api/vip-cards/purchase`)
   - ৪.৪. প্রতিদিনের শিডিউল রিওয়ার্ড ক্লেইম (`POST /api/vip-cards/claim-daily`)
   - ৪.৫. অ্যাডমিন প্যানেল CRUD এপিআই (`/api/admin/vip-cards`)
5. [Flutter Developer-এর জন্য সম্পূর্ণ UI ও ইন্টিগ্রেশন গাইড](#5-flutter-developer-এর-জন্য-সম্পূর্ণ-ui-ও-ইন্টিগ্রেশন-গাইড)
   - ৫.১. প্রোফাইল/মি স্ক্রিনে ব্যানার উইজেট
   - ৫.২. `MonthlyCardScreen` (কার্ড স্লাইডার, শিডিউল গ্রিড, টাইমার ও ক্লেইম বাটন)
6. [ডেভেলপারদের জন্য এক নজরে চেকলিস্ট](#6-ডেভেলপারদের-জন্য-এক-নজরে-চেকলিস্ট)

---

## 1. আর্কিটেকচার ও লাইফসাইকেল ফ্লো

```
   [ User Profile / Me Screen ]
                │
                ▼ (Taps "Spend Less, Get More Gems!" Banner)
   ┌─────────────────────────────────────────┐
   │     🌟 Monthly / Weekly Card Screen     │
   │  [New User] [Super] [Luxury] [Weekly]   │
   └────────────────────┬────────────────────┘
                        │
                        ▼ (User Purchases Card e.g. BDT 300 / 8,100 Gems)
   ┌─────────────────────────────────────────┐
   │ 1. Deduct Gems or Process BDT Payment   │
   │ 2. Instant Reward Credited (+8,100 Gems)│
   │ 3. Unlock Outfits, Frames & VIP Badge   │
   │ 4. Start Countdown Timer (e.g. 7 Days)  │
   └────────────────────┬────────────────────┘
                        │
                        ▼ (User Returns Every 24h)
   ┌─────────────────────────────────────────┐
   │ Click [Claim Daily Schedule Reward]     │
   │ -> Credits Day N Gems into Main Wallet  │
   │ -> Updates Streak & Claim Status        │
   └─────────────────────────────────────────┘
```

---

## 2. ১০-সংখ্যার ইউনিক অ্যাকাউন্ট আইডি

সিস্টেমের প্রতিটি ইউজারের জন্য একটি **অনন্য ১০-সংখ্যার অ্যাকাউন্ট আইডি (`account_id`)** স্বয়ংক্রিয়ভাবে জেনারেট হয় (যেমন: `8472910382`), যা কোনো ইউজারের সাথেই মিলবে না।

* **মডেল লজিক (`app/Models/User.php`):**
```php
protected static function booted()
{
    static::creating(function ($user) {
        if (empty($user->account_id)) {
            $user->account_id = static::generateUniqueAccountId();
        }
    });
}

public static function generateUniqueAccountId(): string
{
    do {
        $accountId = (string) mt_rand(1000000000, 9999999999);
    } while (static::where('account_id', $accountId)->exists());

    return $accountId;
}
```

---

## 3. ডাটাবেজ স্কিমা

### ৩.১. `vip_privilege_cards` টেবিল (অ্যাডমিন কনফিগারেবল)
| Field | Type | Description |
|---|---|---|
| `id` | `BIGINT UNSIGNED` | Primary Key |
| `card_type` | `VARCHAR(50)` | `new_user`, `super_monthly`, `luxury_monthly`, `super_weekly` |
| `name` | `VARCHAR(100)` | কার্ডের নাম (যেমন: "New User Weekly Card") |
| `badge_text` | `VARCHAR(50)` | ব্যাজ (যেমন: "HOT", "50% OFF", "BEST VALUE") |
| `price_bdt` | `DECIMAL(10,2)` | টাকার মূল্য (যেমন: 300.00, 1200.00, 2400.00) |
| `price_coins` | `BIGINT` | কয়েন/জেমস মূল্য (যেমন: 8100, 32940, 66600) |
| `duration_days` | `INT` | মেয়াদ (৭ দিন বা ৩০ দিন) |
| `instant_reward_coins` | `BIGINT` | কেনার সাথে সাথে প্রাপ্ত কয়েন |
| `daily_checkin_total_coins` | `BIGINT` | সর্বমোট দৈনিক চেক-ইন বোনাস কয়েন |
| `total_return_coins` | `BIGINT` | সর্বমোট মোট রিটার্ন কয়েন |
| `daily_schedule` | `JSON` | প্রতিদিনের রিওয়ার্ডের তালিকা (Array of Day & Coins) |
| `extra_rewards` | `JSON` | ফ্রেম, আউটফিট, এসভিআইপি ব্যাজের তালিকা |
| `card_color` | `VARCHAR(20)` | কার্ডের ব্যাকগ্রাউন্ড কালার হেক্স কোড |
| `banner_tag` | `VARCHAR(255)` | ব্যানার টেক্সট |
| `is_active` | `BOOLEAN` | সক্রিয় অবস্থা (Default `true`) |
| `sort_order` | `INT` | প্রদর্শনের ক্রম |

### ৩.২. `user_vip_card_subscriptions` টেবিল (ইউজারের পারচেজ রেকর্ড)
| Field | Type | Description |
|---|---|---|
| `id` | `BIGINT UNSIGNED` | Primary Key |
| `user_id` | `BIGINT UNSIGNED` | Foreign Key -> `users.id` |
| `vip_card_id` | `BIGINT UNSIGNED` | Foreign Key -> `vip_privilege_cards.id` |
| `card_type` | `VARCHAR(50)` | কার্ডের ধরন |
| `price_paid` | `DECIMAL(10,2)` | পরিশোধিত মূল্য |
| `started_at` | `TIMESTAMP` | সাবস্ক্রিপশন শুরুর তারিখ ও সময় |
| `expires_at` | `TIMESTAMP` | মেয়াদের শেষ তারিখ ও সময় |
| `claimed_days` | `JSON` | যে যে দিনের রিওয়ার্ড ক্লেইম করা হয়েছে (যেমন: `[1, 2, 3]`) |
| `last_claimed_at` | `TIMESTAMP` | সর্বশেষ ক্লেইমের সময় |
| `status` | `VARCHAR(20)` | `active`, `expired` |

---

## 4. সম্পূর্ণ RESTful API এন্ডপয়েন্টস রেফারেন্স

Base URL: `https://yourdomain.com/api`

---

### ৪.১. কার্ড ক্যাটালগ ও ব্যানার ডাটা
প্রোফাইল পেজের ব্যানার এবং মান্থলি কার্ড স্ক্রিনের সমস্ত কার্ডের তথ্য, প্রতিদিনের শিডিউল এবং ইউজার সাবস্ক্রিপশন স্ট্যাটাস রিটার্ন করে।

* **Method:** `GET`
* **URL:** `/api/vip-cards` (বা `/api/monthly-cards`)
* **Headers:** `Authorization: Bearer {token}` (Optional)

#### Success Response (`200 OK`):
```json
{
  "status": true,
  "message": "Monthly and weekly VIP privilege cards retrieved successfully.",
  "data": {
    "banner": {
      "title": "Spend Less, Get More Gems!",
      "subtitle": "Update to New User Weekly Card",
      "action_type": "OPEN_VIP_CARDS"
    },
    "cards": [
      {
        "id": 1,
        "card_type": "new_user",
        "name": "New User Weekly Card",
        "badge_text": "HOT",
        "price_bdt": 300,
        "formatted_price_bdt": "BDT 300.00",
        "price_coins": 8100,
        "duration_days": 7,
        "instant_reward_coins": 8100,
        "daily_checkin_total_coins": 2020,
        "total_return_coins": 10120,
        "card_color": "#FF4081",
        "banner_tag": "Spend Less, Get More Gems! Update to New User Weekly Card",
        "description": "Normal Recharge = 8,100 Gems. Weekly Card = 10,120 Gems + Outfits + Free Cards!",
        "daily_schedule": [
          {"day": 1, "coins": 8100, "extra": "Card x1"},
          {"day": 2, "coins": 300, "extra": null},
          {"day": 3, "coins": 210, "extra": null},
          {"day": 4, "coins": 500, "extra": null},
          {"day": 5, "coins": 350, "extra": null},
          {"day": 6, "coins": 300, "extra": null},
          {"day": 7, "coins": 360, "extra": "Exclusive Badge"}
        ],
        "extra_rewards": [
          {"title": "Exclusive Avatar Frame", "tag": "Free Outfits", "icon": "frame_avatar"},
          {"title": "Weekly Card Badge", "tag": "SVIP Icon", "icon": "badge_svip"},
          {"title": "Free Lucky Card x1", "tag": "Free Card", "icon": "lucky_card"}
        ],
        "user_subscription": {
          "is_subscribed": false,
          "subscription_id": null,
          "remaining_seconds": 0,
          "countdown_timer": null,
          "current_day": 1,
          "has_claimed_today": false,
          "claimed_days": []
        }
      },
      {
        "id": 2,
        "card_type": "super_monthly",
        "name": "Super Monthly Card",
        "badge_text": "BEST VALUE",
        "price_bdt": 1200,
        "formatted_price_bdt": "BDT 1,200.00",
        "price_coins": 32940,
        "duration_days": 30,
        "instant_reward_coins": 32940,
        "daily_checkin_total_coins": 26330,
        "total_return_coins": 59270,
        "card_color": "#7C4DFF",
        "banner_tag": "Super Monthly Card: 59,270 Gems + Outfits + Free Cards!",
        "daily_schedule": [
          {"day": 1, "coins": 32940, "extra": "Gold Frame"},
          {"day": 2, "coins": 1790, "extra": null},
          {"day": 3, "coins": 1210, "extra": null},
          {"day": 4, "coins": 1790, "extra": null},
          {"day": 5, "coins": 1210, "extra": null},
          {"day": 6, "coins": 1790, "extra": null},
          {"day": 7, "coins": 1790, "extra": null}
        ],
        "extra_rewards": [
          {"title": "Super VIP Gold Frame", "tag": "Gold Frame", "icon": "frame_gold"},
          {"title": "Luxury Chat Bubble", "tag": "Special Outfit", "icon": "chat_bubble"},
          {"title": "Privilege Entry Banner", "tag": "Entry Animation", "icon": "entry_anim"}
        ],
        "user_subscription": {
          "is_subscribed": false,
          "subscription_id": null
        }
      },
      {
        "id": 3,
        "card_type": "luxury_monthly",
        "name": "Luxury Monthly Card",
        "badge_text": "50% OFF",
        "price_bdt": 2400,
        "formatted_price_bdt": "BDT 2,400.00",
        "price_coins": 66600,
        "duration_days": 30,
        "instant_reward_coins": 66600,
        "daily_checkin_total_coins": 87110,
        "total_return_coins": 153710,
        "card_color": "#2979FF",
        "daily_schedule": [
          {"day": 1, "coins": 66600, "extra": "Diamond Frame"},
          {"day": 2, "coins": 3500, "extra": null},
          {"day": 3, "coins": 1790, "extra": null},
          {"day": 4, "coins": 3500, "extra": null}
        ],
        "extra_rewards": [
          {"title": "Diamond Halo Frame", "tag": "Luxury Outfit", "icon": "frame_diamond"},
          {"title": "SVIP Crown Badge & Title", "tag": "SVIP Status", "icon": "svip_crown"},
          {"title": "Global Room Entry Effect", "tag": "Super Entry", "icon": "global_entry"},
          {"title": "Free Lucky Cards x5", "tag": "Free Cards", "icon": "lucky_cards_5"}
        ],
        "user_subscription": {
          "is_subscribed": false
        }
      },
      {
        "id": 4,
        "card_type": "super_weekly",
        "name": "Super Weekly Card",
        "badge_text": "POPULAR",
        "price_bdt": 600,
        "formatted_price_bdt": "BDT 600.00",
        "price_coins": 16200,
        "duration_days": 7,
        "instant_reward_coins": 16200,
        "daily_checkin_total_coins": 5000,
        "total_return_coins": 21200,
        "card_color": "#00E676"
      }
    ]
  }
}
```

---

### ৪.২. ইউজারের সাবস্ক্রিপশন ও ক্লেইম স্ট্যাটাস
* **Method:** `GET`
* **URL:** `/api/vip-cards/my-subscriptions`
* **Headers:** `Authorization: Bearer {token}`

#### Success Response (`200 OK`):
```json
{
  "status": true,
  "message": "User card subscriptions retrieved successfully.",
  "data": {
    "user_coins": 12600,
    "subscriptions": [
      {
        "subscription_id": 12,
        "card_id": 1,
        "card_name": "New User Weekly Card",
        "card_type": "new_user",
        "card_color": "#FF4081",
        "is_active": true,
        "started_at": "2026-09-02T23:00:00.000000Z",
        "expires_at": "2026-09-09T23:00:00.000000Z",
        "remaining_seconds": 604750,
        "countdown_timer": "06:23:59:10",
        "current_day": 2,
        "total_days": 7,
        "has_claimed_today": false,
        "claimed_days": [1],
        "daily_schedule": [
          {"day": 1, "coins": 8100, "extra": "Card x1"},
          {"day": 2, "coins": 300, "extra": null},
          {"day": 3, "coins": 210, "extra": null},
          {"day": 4, "coins": 500, "extra": null},
          {"day": 5, "coins": 350, "extra": null},
          {"day": 6, "coins": 300, "extra": null},
          {"day": 7, "coins": 360, "extra": "Exclusive Badge"}
        ]
      }
    ]
  }
}
```

---

### ৪.৩. ভিআইপি কার্ড ক্রয়/অ্যাক্টিভেশন
* **Method:** `POST`
* **URL:** `/api/vip-cards/purchase`
* **Headers:** `Authorization: Bearer {token}`
* **Request Body:**
```json
{
  "card_id": 1,
  "payment_method": "coins"
}
```

#### Success Response (`200 OK`):
```json
{
  "status": true,
  "message": "Congratulations! New User Weekly Card activated successfully! Instant 8100 Gems credited to your wallet.",
  "data": {
    "subscription_id": 12,
    "card_name": "New User Weekly Card",
    "instant_reward_coins": 8100,
    "new_coins_balance": 10600,
    "expires_at": "2026-09-09T23:45:00.000000Z",
    "duration_days": 7
  }
}
```

#### Insufficient Balance Response (`200 OK`):
```json
{
  "status": false,
  "message": "Insufficient Gems/Coins balance! Required: 8100 coins, You have: 2000 coins.",
  "required_coins": 8100,
  "current_coins": 2000,
  "redirect_to_deposit": true
}
```

---

### ৪.৪. প্রতিদিনের শিডিউল রিওয়ার্ড ক্লেইম
ইউজার প্রতিদিন এসে "Claim" বাটনে চাপ দিলে ওই দিনের কয়েন মূল ব্যালেন্সে যোগ হবে।

* **Method:** `POST`
* **URL:** `/api/vip-cards/claim-daily`
* **Headers:** `Authorization: Bearer {token}`
* **Request Body:**
```json
{
  "card_id": 1
}
```

#### Success Response (`200 OK`):
```json
{
  "status": true,
  "message": "Day 2 bonus claimed successfully! +300 Gems added to your wallet.",
  "data": {
    "day": 2,
    "claimed_coins": 300,
    "extra_reward": null,
    "new_coins_balance": 10900,
    "claimed_days": [1, 2]
  }
}
```

#### Already Claimed Today Response (`200 OK`):
```json
{
  "status": false,
  "message": "You have already claimed Day 2 reward today! Come back tomorrow for next day bonus.",
  "current_day": 2,
  "claimed_days": [1, 2]
}
```

---

### ৪.৫. অ্যাডমিন প্যানেল CRUD এপিআই

* **নতুন কার্ড তৈরি:** `POST /api/admin/vip-cards`
* **কার্ড এডিট/আপডেট:** `POST /api/admin/vip-cards/{id}`
* **কার্ড ডিলিট:** `DELETE /api/admin/vip-cards/{id}`

---

## 5. Flutter Developer-এর জন্য সম্পূর্ণ UI ও ইন্টিগ্রেশন গাইড

### ৫.১. প্রোফাইল/মি (Me) স্ক্রিনে ব্যানার উইজেট

```dart
Widget buildVipBanner(BuildContext context) {
  return GestureDetector(
    onTap: () {
      Navigator.push(
        context,
        MaterialPageRoute(builder: (context) => const MonthlyCardScreen()),
      );
    },
    child: Container(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF2C2238), Color(0xFF1E1B2E)],
          begin: Alignment.centerLeft,
          end: Alignment.centerRight,
        ),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.amber.withOpacity(0.4), width: 1),
      ),
      child: Row(
        children: [
          Image.asset('assets/icons/card_gift.png', width: 36, height: 36),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: const [
                Text(
                  "Spend Less, Get More Gems!",
                  style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14),
                ),
                SizedBox(height: 2),
                Text(
                  "Update to New User Weekly Card",
                  style: TextStyle(color: Colors.white70, fontSize: 11),
                ),
              ],
            ),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
            decoration: BoxDecoration(
              color: Colors.amber,
              borderRadius: BorderRadius.circular(20),
            ),
            child: const Text(
              "View",
              style: TextStyle(color: Colors.black, fontWeight: FontWeight.bold, fontSize: 12),
            ),
          ),
        ],
      ),
    ),
  );
}
```

---

### ৫.২. `MonthlyCardScreen.dart` (কার্ড স্লাইডার, শিডিউল ও ক্লেইম বাটন)

```dart
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import '../services/auth_storage_service.dart';

class MonthlyCardScreen extends StatefulWidget {
  const MonthlyCardScreen({Key? key}) : super(key: key);

  @override
  State<MonthlyCardScreen> createState() => _MonthlyCardScreenState();
}

class _MonthlyCardScreenState extends State<MonthlyCardScreen> with SingleTickerProviderStateMixin {
  TabController? _tabController;
  List<dynamic> _cards = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _fetchCards();
  }

  Future<void> _fetchCards() async {
    final token = await AuthStorageService.getToken();
    final response = await http.get(
      Uri.parse('https://yourdomain.com/api/vip-cards'),
      headers: {
        if (token != null) 'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      setState(() {
        _cards = data['data']['cards'] ?? [];
        _tabController = TabController(length: _cards.length, vsync: this);
        _isLoading = false;
      });
    }
  }

  Future<void> _purchaseCard(int cardId) async {
    final token = await AuthStorageService.getToken();
    if (token == null) return;

    final response = await http.post(
      Uri.parse('https://yourdomain.com/api/vip-cards/purchase'),
      headers: {'Authorization': 'Bearer $token', 'Content-Type': 'application/json'},
      body: jsonEncode({'card_id': cardId}),
    );

    final data = jsonDecode(response.body);
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(data['message'])));
    _fetchCards();
  }

  Future<void> _claimDailyBonus(int cardId) async {
    final token = await AuthStorageService.getToken();
    if (token == null) return;

    final response = await http.post(
      Uri.parse('https://yourdomain.com/api/vip-cards/claim-daily'),
      headers: {'Authorization': 'Bearer $token', 'Content-Type': 'application/json'},
      body: jsonEncode({'card_id': cardId}),
    );

    final data = jsonDecode(response.body);
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(data['message'])));
    _fetchCards();
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading || _tabController == null) {
      return const Scaffold(
        backgroundColor: Color(0xFF0F0E17),
        body: Center(child: CircularProgressIndicator(color: Colors.pinkAccent)),
      );
    }

    return Scaffold(
      backgroundColor: const Color(0xFF0F0E17),
      appBar: AppBar(
        title: const Text("Monthly Card"),
        backgroundColor: Colors.transparent,
        elevation: 0,
        bottom: TabBar(
          controller: _tabController,
          isScrollable: true,
          indicatorColor: Colors.pinkAccent,
          tabs: _cards.map((c) => Tab(text: c['name'])).toList(),
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: _cards.map((card) => _buildCardContent(card)).toList(),
      ),
    );
  }

  Widget _buildCardContent(Map<String, dynamic> card) {
    final sub = card['user_subscription'] ?? {};
    final bool isSubscribed = sub['is_subscribed'] ?? false;
    final bool hasClaimedToday = sub['has_claimed_today'] ?? false;
    final List schedule = card['daily_schedule'] ?? [];

    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Card Header Info (Instant + Daily Bonus)
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              gradient: const LinearGradient(colors: [Color(0xFF2E1C38), Color(0xFF1B162B)]),
              borderRadius: BorderRadius.circular(16),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              children: [
                _buildInfoBlock("Instant Reward", "${card['instant_reward_coins']} 💎"),
                const Text("+", style: TextStyle(color: Colors.white, fontSize: 20)),
                _buildInfoBlock("Daily Bonus", "${card['daily_checkin_total_coins']} 💎"),
                const Text("=", style: TextStyle(color: Colors.white, fontSize: 20)),
                _buildInfoBlock("Total Return", "${card['total_return_coins']} 💎"),
              ],
            ),
          ),
          const SizedBox(height: 20),

          // Daily Schedule Grid
          const Text("Get Schedule", style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 16)),
          const SizedBox(height: 12),
          GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 4,
              childAspectRatio: 0.9,
              crossAxisSpacing: 8,
              mainAxisSpacing: 8,
            ),
            itemCount: schedule.length,
            itemBuilder: (context, index) {
              final item = schedule[index];
              final int day = item['day'];
              final bool isClaimed = (sub['claimed_days'] as List? ?? []).contains(day);

              return Container(
                decoration: BoxDecoration(
                  color: isClaimed ? Colors.green.withOpacity(0.2) : const Color(0xFF1E1B2E),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: isClaimed ? Colors.green : Colors.white12),
                ),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text("Day $day", style: const TextStyle(color: Colors.white70, fontSize: 11)),
                    const SizedBox(height: 4),
                    Text("${item['coins']}", style: const TextStyle(color: Colors.amber, fontWeight: FontWeight.bold, fontSize: 13)),
                    if (isClaimed) const Icon(Icons.check_circle, color: Colors.green, size: 16),
                  ],
                ),
              );
            },
          ),
          const SizedBox(height: 24),

          // Action Button (Buy or Claim)
          if (!isSubscribed)
            SizedBox(
              width: double.infinity,
              height: 50,
              child: ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.pinkAccent,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(25)),
                ),
                onPressed: () => _purchaseCard(card['id']),
                child: Text("Buy for ${card['formatted_price_bdt']} (${card['price_coins']} Gems)", style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
              ),
            )
          else
            SizedBox(
              width: double.infinity,
              height: 50,
              child: ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: hasClaimedToday ? Colors.grey : Colors.green,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(25)),
                ),
                onPressed: hasClaimedToday ? null : () => _claimDailyBonus(card['id']),
                child: Text(
                  hasClaimedToday ? "Today's Bonus Claimed" : "Claim Today's Reward (${sub['countdown_timer'] ?? ''})",
                  style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildInfoBlock(String title, String value) {
    return Column(
      children: [
        Text(title, style: const TextStyle(color: Colors.white54, fontSize: 11)),
        const SizedBox(height: 4),
        Text(value, style: const TextStyle(color: Colors.amber, fontWeight: FontWeight.bold, fontSize: 14)),
      ],
    );
  }
}
```

---

## 6. ডেভেলপারদের জন্য এক নজরে চেকলিস্ট

### Backend Developer (Laravel):
- [x] `vip_privilege_cards` ও `user_vip_card_subscriptions` মাইগ্রেশন সম্পন্ন ও ডিফল্ট ৪টি কার্ড সিড করা হয়েছে।
- [x] `User.php`-এ স্বয়ংক্রিয় ১০-সংখ্যার ইউনিক `account_id` জেনারেটর যুক্ত করা হয়েছে।
- [x] `GET /api/vip-cards`, `POST /api/vip-cards/purchase`, `POST /api/vip-cards/claim-daily` এপিআই কার্যকর।

### Flutter Developer:
- [ ] প্রোফাইল/মি স্ক্রিনে ব্যানার উইজেট বসিয়ে `MonthlyCardScreen`-এ নেভিগেট করা।
- [ ] `MonthlyCardScreen`-এ ৪টি কার্ডের ট্যাব, ডেইলি শিডিউল গ্রিড ও ডায়নামিক পারচেজ/ক্লেইম বাটন সেট করা।
