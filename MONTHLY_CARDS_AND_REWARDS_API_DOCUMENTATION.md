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
2. [অ্যাডমিন প্যানেল ডায়নামিক শিডিউল ও পার্কস বিল্ডার (Admin UI)](#2-অ্যাডমিন-প্যানেল-ডায়নামিক-শিডিউল-ও-পার্কস-বিল্ডার)
3. [ডাটাবেজ স্কিমা (Database Schema)](#3-ডাটাবেজ-স্কিমা)
4. [সম্পূর্ণ RESTful API এন্ডপয়েন্টস রেফারেন্স (Full API Reference)](#4-সম্পূর্ণ-restful-api-এন্ডপয়েন্টস-রেফারেন্স)
   - ৪.১. কার্ড ক্যাটালগ ও ব্যানার ডাটা (`GET /api/vip-cards` বা `GET /api/monthly-cards`)
   - ৪.২. ইউজারের সাবস্ক্রিপশন ও ক্লেইম স্ট্যাটাস (`GET /api/vip-cards/my-subscriptions` বা `GET /api/monthly-cards/my`)
   - ৪.৩. ভিআইপি কার্ড ক্রয়/অ্যাক্টিভেশন (`POST /api/vip-cards/purchase` বা `POST /api/monthly-cards/purchase`)
   - ৪.৪. প্রতিদিনের শিডিউল রিওয়ার্ড ক্লেইম (`POST /api/vip-cards/claim-daily` বা `POST /api/monthly-cards/claim`)
   - ৪.৫. অ্যাডমিন প্যানেল CRUD এপিআই (`/api/admin/vip-cards`)
5. [Flutter Developer-এর জন্য সম্পূর্ণ UI ও ইন্টিগ্রেশন গাইড](#5-flutter-developer-এর-জন্য-সম্পূর্ণ-ui-ও-ইন্টিগ্রেশন-গাইড)
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
   │ 4. Start Live Countdown (06:23:59:44)   │
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

## 2. অ্যাডমিন প্যানেল ডায়নামিক শিডিউল ও পার্কস বিল্ডার

অ্যাডমিন প্যানেলে কোনো প্রকার ম্যানুয়াল **JSON কোড লেখার প্রয়োজন নেই**। সম্পূর্ণ ইন্টারেক্টিভ ফর্মের মাধ্যমে যেকোনো কার্ড প্যাকেজ কনফিগার করা যায়:

1. **Daily Check-in Schedule Builder:**
   - **Auto-Generate Button:** ৭ দিন বা ৩০ দিনের মেয়াদের ভিত্তিতে স্বয়ংক্রিয়ভাবে প্রতিদিনের কয়েন ও ব্যাজ জেনারেট করে।
   - **Add / Remove Day Rows:** ইচ্ছেমতো যেকোনো দিন যোগ বা ডিলিট করা যায়।
   - **Live Coins Counter:** মোট শিডিউল কয়েন স্বয়ংক্রিয়ভাবে হিসাব করে ইনস্ট্যান্ট রিওয়ার্ডের সাথে যুক্ত করে।
2. **Extra Outfits & Rewards Perks Builder:**
   - **Title & Tag:** আউটফিট ও ফ্রেমের নাম ও সাবটাইটেল (যেমন: `Exclusive Avatar Frame`, `Free Outfits`)।
   - **Preset Icons / Custom File Upload:** প্রিসেট আইকন সিলেক্ট করার পাশাপাশি কম্পিউটার থেকে সরাসরি ইমেজ/আইকন আপলোড করা যায় (সংরক্ষিত হয় `public/uploads/vip_cards/` ফোল্ডারে)।
   - **Live Thumbnail Preview:** আপলোড বা সিলেক্ট করা মাত্রই ইমেজ প্রিভিউ দেখা যায়।

---

## 3. ডাটাবেজ স্কিমা

### ৩.১. `vip_privilege_cards` টেবিল
| Field | Type | Description |
|---|---|---|
| `id` | `BIGINT UNSIGNED` | Primary Key |
| `card_type` | `VARCHAR(50)` | `new_user`, `super_monthly`, `luxury_monthly`, `super_weekly` |
| `name` | `VARCHAR(100)` | কার্ডের নাম (যেমন: "New User Weekly Card") |
| `badge_text` | `VARCHAR(50)` | ব্যাজ (যেমন: "HOT", "50% OFF", "BEST VALUE", "POPULAR") |
| `price_bdt` | `DECIMAL(10,2)` | টাকার মূল্য (যেমন: 300.00, 600.00, 1200.00, 2400.00) |
| `price_coins` | `BIGINT` | কয়েন/জেমস মূল্য (যেমন: 8100, 16200, 32940, 66600) |
| `duration_days` | `INT` | মেয়াদ (৭ দিন বা ৩০ দিন) |
| `instant_reward_coins` | `BIGINT` | কেনার সাথে সাথে প্রাপ্ত কয়েন |
| `daily_checkin_total_coins` | `BIGINT` | সর্বমোট দৈনিক চেক-ইন বোনাস কয়েন |
| `total_return_coins` | `BIGINT` | সর্বমোট মোট রিটার্ন কয়েন (Instant + Daily) |
| `daily_schedule` | `JSON` | প্রতিদিনের রিওয়ার্ডের তালিকা (Array of Day, Coins & Extra Badge) |
| `extra_rewards` | `JSON` | ফ্রেম, আউটফিট, এসভিআইপি ব্যাজের তালিকা ও ইমেজ পাথ |
| `card_color` | `VARCHAR(20)` | কার্ডের ব্যাকগ্রাউন্ড কালার হেক্স কোড (যেমন: `#FF4081`, `#00E676`) |
| `banner_tag` | `VARCHAR(255)` | ব্যানার টেক্সট |
| `is_active` | `BOOLEAN` | সক্রিয় অবস্থা (Default `true`) |
| `sort_order` | `INT` | প্রদর্শনের ক্রম |

---

## 4. সম্পূর্ণ RESTful API এন্ডপয়েন্টস রেফারেন্স

Base URL: `https://yourdomain.com/api`

---

### ৪.১. কার্ড ক্যাটালগ ও ব্যানার ডাটা
প্রোফাইল পেজের ব্যানার এবং মান্থলি কার্ড স্ক্রিনের সমস্ত কার্ডের তথ্য, প্রতিদিনের শিডিউল, এক্সট্রা আউটফিটের ফুল ইমেজ URL এবং টাইমার রিটার্ন করে।

* **Method:** `GET`
* **URL:** `/api/vip-cards` (বা `/api/monthly-cards`)
* **Headers:** `Authorization: Bearer {token}` (Optional - লগইন থাকলে ইউজারের সাবস্ক্রিপশন স্ট্যাটাসসহ আসবে)

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
        "countdown_seconds": 604740,
        "countdown_timer": "06 : 23 : 59 : 00",
        "daily_schedule": [
          {
            "day": 1,
            "day_label": "1st",
            "coins": 8100,
            "extra": "Card x1",
            "icon": null,
            "image_url": null
          },
          {
            "day": 2,
            "day_label": "2nd",
            "coins": 300,
            "extra": null,
            "icon": null,
            "image_url": null
          },
          {
            "day": 3,
            "day_label": "3rd",
            "coins": 210,
            "extra": null,
            "icon": null,
            "image_url": null
          },
          {
            "day": 4,
            "day_label": "4th",
            "coins": 500,
            "extra": null,
            "icon": null,
            "image_url": null
          },
          {
            "day": 5,
            "day_label": "5th",
            "coins": 350,
            "extra": null,
            "icon": null,
            "image_url": null
          },
          {
            "day": 6,
            "day_label": "6th",
            "coins": 300,
            "extra": null,
            "icon": null,
            "image_url": null
          },
          {
            "day": 7,
            "day_label": "7th",
            "coins": 360,
            "extra": "Exclusive Badge",
            "icon": null,
            "image_url": null
          }
        ],
        "extra_rewards": [
          {
            "title": "Exclusive Avatar Frame",
            "tag": "Free Outfits",
            "icon": "frame_avatar",
            "image": null,
            "image_url": "https://yourdomain.com/uploads/vip_cards/perk_1.png"
          },
          {
            "title": "Weekly Card Badge",
            "tag": "SVIP Icon",
            "icon": "badge_svip",
            "image": null,
            "image_url": null
          },
          {
            "title": "Free Lucky Card x1",
            "tag": "Free Card",
            "icon": "lucky_card",
            "image": null,
            "image_url": null
          }
        ],
        "user_subscription": {
          "is_subscribed": false,
          "subscription_id": null,
          "started_at": null,
          "expires_at": null,
          "remaining_seconds": 0,
          "countdown_timer": null,
          "current_day": 1,
          "has_claimed_today": false,
          "claimed_days": []
        }
      }
    ]
  }
}
```

---

### ৪.২. ইউজারের সাবস্ক্রিপশন ও ক্লেইম স্ট্যাটাস
* **Method:** `GET`
* **URL:** `/api/vip-cards/my-subscriptions` (বা `/api/monthly-cards/my`)
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
        "countdown_timer": "06 : 23 : 59 : 10",
        "current_day": 2,
        "total_days": 7,
        "has_claimed_today": false,
        "claimed_days": [1],
        "daily_schedule": [ ... ],
        "extra_rewards": [ ... ]
      }
    ]
  }
}
```

---

### ৪.৩. ভিআইপি কার্ড ক্রয়/অ্যাক্টিভেশন
* **Method:** `POST`
* **URL:** `/api/vip-cards/purchase` (বা `/api/monthly-cards/purchase`)
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

---

### ৪.৪. প্রতিদিনের শিডিউল রিওয়ার্ড ক্লেইম
* **Method:** `POST`
* **URL:** `/api/vip-cards/claim-daily` (বা `/api/monthly-cards/claim`)
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

---

## 5. Flutter Developer-এর জন্য সম্পূর্ণ UI ও ইন্টিগ্রেশন গাইড

### `MonthlyCardScreen.dart`
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
        title: const Text("Monthly Card", style: TextStyle(fontWeight: FontWeight.bold)),
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
    final List perks = card['extra_rewards'] ?? [];
    final String countdownTimer = card['countdown_timer'] ?? '06 : 23 : 59 : 00';

    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // 1. Header Card with Box, Title, Diamonds and Countdown Timer
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: const Color(0xFF1E1B2E),
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: Colors.pinkAccent.withOpacity(0.5), width: 1.5),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(card['name'], style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
                        const SizedBox(height: 6),
                        Text("Get 💎 ${card['total_return_coins']} by paying 💎 ${card['price_coins']} price",
                            style: const TextStyle(color: Colors.white70, fontSize: 13)),
                      ],
                    ),
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(color: Colors.pinkAccent, borderRadius: BorderRadius.circular(12)),
                      child: const Icon(Icons.card_membership, color: Colors.white, size: 28),
                    )
                  ],
                ),
                const SizedBox(height: 12),
                // Live Countdown Box
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                  decoration: BoxDecoration(color: Colors.black45, borderRadius: BorderRadius.circular(8)),
                  child: Text(countdownTimer, style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold, letterSpacing: 2)),
                ),
                const SizedBox(height: 10),
                Text(card['banner_tag'] ?? '', style: const TextStyle(color: Colors.white60, fontSize: 12)),
              ],
            ),
          ),
          const SizedBox(height: 16),

          // 2. Summary Row (Instant + Daily + Extra Perks)
          Row(
            children: [
              Expanded(
                child: _buildSummaryBox("Instant Reward", "💎 ${card['instant_reward_coins']}"),
              ),
              const Padding(
                padding: EdgeInsets.symmetric(horizontal: 4),
                child: Text("+", style: TextStyle(color: Colors.white54, fontSize: 18)),
              ),
              Expanded(
                child: _buildSummaryBox("Daily Check-in", "🎁 ${card['daily_checkin_total_coins']}"),
              ),
              const Padding(
                padding: EdgeInsets.symmetric(horizontal: 4),
                child: Text("+", style: TextStyle(color: Colors.white54, fontSize: 18)),
              ),
              Expanded(
                child: _buildSummaryBox("Extra Reward", "${perks.length} Perks"),
              ),
            ],
          ),
          const SizedBox(height: 20),

          // 3. Get Schedule Section
          const Center(
            child: Text("— Get schedule —", style: TextStyle(color: Colors.amber, fontWeight: FontWeight.bold, fontSize: 15)),
          ),
          const SizedBox(height: 12),

          GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 4,
              childAspectRatio: 0.85,
              crossAxisSpacing: 8,
              mainAxisSpacing: 8,
            ),
            itemCount: schedule.length,
            itemBuilder: (context, index) {
              final item = schedule[index];
              final int day = item['day'];
              final String dayLabel = item['day_label'] ?? "${day}th";
              final String? extraBadge = item['extra'];
              final bool isClaimed = (sub['claimed_days'] as List? ?? []).contains(day);

              return Container(
                padding: const EdgeInsets.all(6),
                decoration: BoxDecoration(
                  color: isClaimed ? Colors.green.withOpacity(0.2) : const Color(0xFF1E1B2E),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: isClaimed ? Colors.green : (index == 0 ? Colors.pinkAccent : Colors.white12)),
                ),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text(dayLabel, style: const TextStyle(color: Colors.white70, fontSize: 11)),
                    const SizedBox(height: 4),
                    Text("💎 x${item['coins']}", style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12)),
                    if (extraBadge != null && extraBadge.isNotEmpty) ...[
                      const SizedBox(height: 4),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 2),
                        decoration: BoxDecoration(color: Colors.pink.withOpacity(0.3), borderRadius: BorderRadius.circular(4)),
                        child: Text(extraBadge, style: const TextStyle(color: Colors.pinkAccent, fontSize: 9), overflow: TextOverflow.ellipsis),
                      )
                    ]
                  ],
                ),
              );
            },
          ),
          const SizedBox(height: 24),

          // 4. Bottom Purchase / Claim Button
          if (!isSubscribed)
            SizedBox(
              width: double.infinity,
              height: 52,
              child: ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.pinkAccent,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(26)),
                ),
                onPressed: () => _purchaseCard(card['id']),
                child: Text(card['formatted_price_bdt'] ?? "BDT 300.00", style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
              ),
            )
          else
            SizedBox(
              width: double.infinity,
              height: 52,
              child: ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: hasClaimedToday ? Colors.grey : Colors.green,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(26)),
                ),
                onPressed: hasClaimedToday ? null : () => _claimDailyBonus(card['id']),
                child: Text(
                  hasClaimedToday ? "Today's Bonus Claimed" : "Claim Today's Bonus (Day ${sub['current_day']})",
                  style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildSummaryBox(String title, String value) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 8),
      decoration: BoxDecoration(color: const Color(0xFF1E1B2E), borderRadius: BorderRadius.circular(12)),
      child: Column(
        children: [
          Text(title, style: const TextStyle(color: Colors.white60, fontSize: 11), textAlign: TextAlign.center),
          const SizedBox(height: 4),
          Text(value, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13), textAlign: TextAlign.center),
        ],
      ),
    );
  }
}
```

---

## 6. ডেভেলপারদের জন্য এক নজরে চেকলিস্ট

### Backend Developer (Laravel):
- [x] অ্যাডমিন প্যানেলে JSON textarea সম্পূর্ণ বাতিল করে ইন্টারেক্টিভ **Daily Schedule Builder** এবং **Extra Perks Builder with Image/Icon Upload** যুক্ত করা হয়েছে।
- [x] ইমেজ ও আইকন আপলোড হ্যান্ডলিং সরাসরি `public/uploads/vip_cards/` ফোল্ডারে সাপোর্ট করে।
- [x] `GET /api/vip-cards` এবং `GET /api/vip-cards/my-subscriptions` এপিআই-তে ফুল ইমেজ URL, লাইভ কাউন্টডাউন টাইমার স্ট্রিং (`06 : 23 : 59 : 44`), ইনস্ট্যান্ট রিওয়ার্ড ও শিডিউল রিটার্ন করছে।
- [x] `POST /api/vip-cards/purchase` এবং `POST /api/vip-cards/claim-daily` সম্পূর্ণ ডায়নামিক ও কার্যকর।

### Flutter Developer:
- [ ] `MonthlyCardScreen.dart` কোডটি ইন্টিগ্রেট করুন।
- [ ] প্রোফাইল/মি স্ক্রিনের ব্যানার থেকে `MonthlyCardScreen`-এ নেভিগেট করুন।
