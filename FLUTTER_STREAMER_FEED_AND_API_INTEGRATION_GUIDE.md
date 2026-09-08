# 📱 Chinchins Live — Flutter Integration & Master API Guide

**Document:** `FLUTTER_STREAMER_FEED_AND_API_INTEGRATION_GUIDE.md`  
**Target Audience:** Flutter Mobile App Developers & Backend Engineers  
**Production API Base:** `https://chinchins.live/api`  
**Authorization Header:** `Authorization: Bearer <Sanctum_Token>` or `X-User-Id: <User_ID>`  

---

## 📑 Table of Contents
1. [Zero-Delay Pre-Call Balance Check & Instant Recharge Modal (CRITICAL UX REQUIREMENT)](#1-zero-delay-pre-call-balance-check--instant-recharge-modal)
2. [Coin Packages & Recharge Modal API (`GET /api/recharge/modal-data`)](#2-coin-packages--recharge-modal-api)
3. [Fixing SVG Diamond Icons in Flutter (`flutter_svg` Guide)](#3-fixing-svg-diamond-icons-in-flutter)
4. [Master RESTful API: Public Home Feed & Streamers List (`GET /api/home`)](#4-master-restful-api-public-home-feed--streamers-list)
5. [User Search & Single Profile Details APIs](#5-user-search--single-profile-details-apis)
6. [Gifts & Rewards System: Catalog, Dynamic Multipliers & Sending Flow](#6-gifts--rewards-system-catalog-dynamic-multipliers--sending-flow)
7. [Host Earnings & Converting Gift Earnings to Main Coins Balance](#7-host-earnings--converting-gift-earnings-to-main-coins-balance)
8. [VIP & Package Daily Claim Rewards in "Me" Section](#8-vip--package-daily-claim-rewards-in-me-section)
9. [Ready-to-Copy Flutter Dart Implementation Code](#9-ready-to-copy-flutter-dart-implementation-code)
10. [Developer Checklist](#10-developer-checklist)

---

## ⚡ 1. Zero-Delay Pre-Call Balance Check & Instant Recharge Modal

> [!CAUTION]
> **STRICT UX RULE:** When a user taps the **Call** (Audio or Video) button and has **0 balance** (or less than the host's rate per minute), the app **MUST NOT** show **"Call connecting..."**, **"Calling..."**, or any loading spinner!
> Showing a calling screen when the user has no balance confuses the user. Instead, the Recharge Modal (`RechargeGemsSheet`) **MUST open INSTANTLY (< 0.1s)** directly from the current screen!

### The Two-Tier Instant Verification Flow:

```
[User Taps Call Button]
         │
         ▼
[Step 1: Local In-Memory Fast Check (0.00s Instant)]
 └─ Check: (currentUser.coins < host.ratePerMinute)
         │
         ├─── YES (Coins < Rate or Coins == 0) ──────► [Instantly Open RechargeGemsSheet]
         │                                            ⛔ NO "Call connecting..."
         │                                            ⛔ NO Agora screen
         │                                            ⛔ NO network waiting delay
         │
         └─── NO (User has enough coins locally) ───► [Step 2: Backend Check]
                                                      └─ POST /api/call/check-permission
                                                           │
                                                           ├── can_call: false ──► [Open RechargeGemsSheet]
                                                           └── can_call: true  ──► [Open Agora Calling Screen]
```

### Pre-Call Permission Verification API
- **Route:** `POST https://chinchins.live/api/call/check-permission`
- **Aliases:** `POST /api/call/can-call`, `POST /api/call/check-balance`
- **Headers:** `Authorization: Bearer <token>`
- **Request Body:**
```json
{
  "receiver_id": 2,
  "call_type": "video"
}
```

### Response When Balance is Insufficient (`200 OK`):
```json
{
  "status": false,
  "can_call": false,
  "code": "INSUFFICIENT_BALANCE",
  "message": "Insufficient coin balance. You have 0 coins, but need at least 1800 coins.",
  "user_balance": 0,
  "user_gems": 0,
  "wallet_label": "My Gems",
  "required_coins": 1800,
  "rate_per_minute": 1800,
  "show_recharge_modal": true,
  "recharge_modal_data": {
    "title": "I want to talk more with you. Recharge and call me back~",
    "teaser_text": "I want to talk more with you. Recharge and call me back~",
    "user_gems": 0,
    "user_gems_text": "My Gems: 0",
    "packages": [...]
  }
}
```

---

## 💎 2. Coin Packages & Recharge Modal API

Use this endpoint to load the recharge bottom sheet with all active packages and live SVG illustrations.

### Endpoint Details
- **Route:** `GET https://chinchins.live/api/recharge/modal-data`
- **Alias:** `GET https://chinchins.live/api/coin-packages`
- **Query Parameters (Optional):**
  - `receiver_id`: Target streamer/host ID (e.g. `2`)
  - `action`: `call` or `chat`

### Response Structure (`200 OK`):
```json
{
  "status": true,
  "message": "Recharge modal data retrieved successfully.",
  "user_gems": 0,
  "wallet_label": "My Gems",
  "button_text": "Continue",
  "packages": [
    {
      "id": 1,
      "title": "Starter Pack",
      "coins": 7560,
      "base_coins": 7560,
      "bonus_coins": 0,
      "total_coins": 7560,
      "formatted_coins": "7,560",
      "price": 150.00,
      "price_bdt": 150.00,
      "formatted_price": "BDT 150.00",
      "badge": "50% off",
      "badge_color": "danger",
      "is_once_offer": true,
      "icon_url": "uploads/coin_packages/gem_tier1_single.svg",
      "icon_full_url": "https://chinchins.live/uploads/coin_packages/gem_tier1_single.svg",
      "svg_url": "https://chinchins.live/uploads/coin_packages/gem_tier1_single.svg",
      "png_url": "https://chinchins.live/uploads/coin_packages/gem_tier1_single.png",
      "image_url": "https://chinchins.live/uploads/coin_packages/gem_tier1_single.svg",
      "is_popular": true
    },
    {
      "id": 2,
      "title": "Basic Pack",
      "coins": 8100,
      "base_coins": 8100,
      "bonus_coins": 0,
      "total_coins": 8100,
      "formatted_coins": "8,100",
      "price": 300.00,
      "price_bdt": 300.00,
      "formatted_price": "BDT 300.00",
      "badge": "17% off",
      "badge_color": "pink",
      "is_once_offer": false,
      "icon_url": "uploads/coin_packages/gem_tier2_double.svg",
      "icon_full_url": "https://chinchins.live/uploads/coin_packages/gem_tier2_double.svg",
      "svg_url": "https://chinchins.live/uploads/coin_packages/gem_tier2_double.svg",
      "png_url": "https://chinchins.live/uploads/coin_packages/gem_tier2_double.png",
      "image_url": "https://chinchins.live/uploads/coin_packages/gem_tier2_double.svg",
      "is_popular": false
    }
  ]
}
```

---

## 🎨 3. Fixing SVG Diamond Icons in Flutter

### Why the Purple Person Placeholder Box Appeared
Previously, the app showed a dark purple square with a person icon (`Icons.person`) inside the package card.
Two factors caused this:
1. **SVG `<feDropShadow>` filters:** Flutter's `flutter_svg` package throws parser errors when encountering SVG `<filter>` tags like `<feDropShadow>`. The backend SVGs have now been completely updated to be 100% SVG 1.1 compliant without filters.
2. **Localhost URLs:** In CLI or unconfigured environments, URLs could previously default to `http://localhost`. The backend now strictly resolves all paths to `https://chinchins.live/uploads/coin_packages/...`.

---

## 🚀 4. Master RESTful API: Public Home Feed & Streamers List

- **Endpoint:** `GET https://chinchins.live/api/home`
- **Aliases:** `GET /api/users`, `GET /api/hot`, `GET /api/streamers`

### Query Parameters

| Parameter | Type | Default | Description | Example Values |
|---|---|---|---|---|
| `country` | string | `All` | Filter by Country name or ISO Alpha-2/Alpha-3 | `All`, `BGD`, `BD`, `Pakistan`, `PK`, `USA` |
| `gender` | string | `all` | Filter by streamer gender | `female`, `male`, `all` |
| `search` | string | null | Search by Name, Nickname, or 8-digit Account ID | `Ayeena`, `602281635`, `Dhaka` |
| `page` | integer | `1` | Pagination page number | `1`, `2`, `3` |
| `per_page` | integer | `20` | Results per page | `20`, `30`, `50` |

---

## 🔍 5. User Search & Single Profile Details APIs

- **User Search:** `GET https://chinchins.live/api/search?q={query}`  
  Supports searching by 8-digit Account ID (`602281635`) or streamer Name (`Ayeena`).
- **Single Profile:** `GET https://chinchins.live/api/profile/{id_or_account_id}`  
  Returns comprehensive host profile with avatar, gallery images, video call rate, and bio.

---

## 🎁 6. Gifts & Rewards System: Catalog, Dynamic Multipliers & Sending Flow

The in-chat and in-call gifts bottom sheet displays 160 active gifts grouped into tabs (`Hot`, `Lucky`, `SVIP`, `Intimacy`, etc.).

### 1. Fetch Gift Catalog
- **Endpoint:** `GET https://chinchins.live/api/gifts/catalog` (or `GET /api/gifts`)
- **Query Params:** `category` (optional, e.g. `hot`, `lucky`, `all`)
- **Headers:** `Authorization: Bearer <token>`

### Response Structure (`200 OK`):
```json
{
  "status": true,
  "message": "Gifts catalog loaded successfully.",
  "data": {
    "user_balance": {
      "coins": 0,
      "formatted_coins": "0"
    },
    "selected_category": "all",
    "multipliers": [1, 10, 66, 99, 520, 1314],
    "default_multiplier": 1,
    "recharge_url": "/api/recharge/modal-data",
    "total_gifts": 160,
    "categories_list": [
      { "key": "all", "label": "All", "emoji": "🎁", "count": 160 },
      { "key": "hot", "label": "Hot", "emoji": "🔥", "count": 71 },
      { "key": "lucky", "label": "Lucky", "emoji": "🍀", "count": 20 }
    ],
    "gifts": [
      {
        "id": 1,
        "name": "Trophy Cup",
        "coins": 500,
        "coin_price": 500,
        "category": "hot",
        "badge": "HOT",
        "image_url": "https://chinchins.live/uploads/gifts/trophy_cup.svg",
        "svg_url": "https://chinchins.live/uploads/gifts/trophy_cup.svg",
        "png_url": "https://chinchins.live/uploads/gifts/trophy_cup.png",
        "icon_url": "https://chinchins.live/uploads/gifts/trophy_cup.svg"
      },
      {
        "id": 2,
        "name": "Mystery Box",
        "coins": 888,
        "coin_price": 888,
        "category": "hot",
        "badge": "MUST WIN",
        "image_url": "https://chinchins.live/uploads/gifts/mystery_box.svg",
        "svg_url": "https://chinchins.live/uploads/gifts/mystery_box.svg",
        "png_url": "https://chinchins.live/uploads/gifts/mystery_box.png",
        "icon_url": "https://chinchins.live/uploads/gifts/mystery_box.svg"
      }
    ]
  }
}
```

### 2. Multipliers (Replacing Hardcoded `x1, x5, x10, x99`):
> [!NOTE]
> Do **NOT** hardcode `x1, x5, x10, x99` buttons! Use the API-provided `data.multipliers` list (`[1, 10, 66, 99, 520, 1314]`).  
> Users tap any multiplier button to select quantity. Default quantity is `1`.

### 3. Send Gift with Instant Balance Verification
- **Endpoint:** `POST https://chinchins.live/api/gifts/send`
- **Headers:** `Authorization: Bearer <token>`
- **Request Body:**
```json
{
  "receiver_id": 2,
  "gift_id": 1,
  "quantity": 1,
  "context": "chat" 
}
```

> [!TIP]
> **Client-Side Pre-Check:** Before calling `POST /api/gifts/send`:
> ```dart
> final int totalCost = selectedGift.coins * selectedQuantity;
> if (currentUser.coins < totalCost) {
>   // Instantly open RechargeGemsSheet without network delay!
>   RechargeGemsSheet.show(context, receiverId: host.id, ...);
>   return;
> }
> ```

---

## 💰 7. Host Earnings & Converting Gift Earnings to Main Coins Balance

When a streamer/host receives gifts:
1. Gifts appear in their profile under **Gifts Received** (`GET /api/profile/{id}/gifts` or `GET /api/gifts/received/{id}`).
2. **Earnings Wallet:** The full value of received gifts credits into the host's `earnings` wallet.
3. **Convert Earnings to Main Coins Balance:**  
   The host can convert their gift earnings into main spending gems/coins (to gift other streamers or use for calls):
   - **Endpoint:** `POST https://chinchins.live/api/wallet/convert-earnings`
   - **Alias:** `POST https://chinchins.live/api/gifts/convert-to-balance`
   - **Request Body:**
   ```json
   {
     "amount": 500
   }
   ```
   *(If `amount` is omitted, it converts 100% of available earnings).*
   - **Response (`200 OK`):**
   ```json
   {
     "status": true,
     "message": "Successfully converted 500 gift earnings into main spending gems!",
     "data": {
       "converted_amount": 500,
       "new_coins_balance": 500,
       "remaining_earnings": 0
     }
   }
   ```

---

## 🎁 8. VIP & Package Daily Claim Rewards in "Me" Section

When users purchase VIP or special reward packages, daily scheduled rewards appear in their **Me** profile section:

1. **Check Active VIP Cards & Claim Status:**  
   - `GET https://chinchins.live/api/vip-cards`  
   - Returns `has_claimed_today: false` if ready to claim today.
2. **Claim Today's Reward:**  
   - `POST https://chinchins.live/api/vip-cards/claim-daily`  
   - Adds today's reward gems directly to the user's main wallet balance!
3. **Spend Less Get More Daily Bonus:**  
   - `POST https://chinchins.live/api/spend-less-get-more/claim`

---

## 💻 9. Ready-to-Copy Flutter Dart Implementation Code

### A. Rendering Gift Card SVG Images in Flutter
```dart
import 'package:flutter/material.dart';
import 'package:flutter_svg/flutter_svg.dart';

Widget buildGiftIcon(Map<String, dynamic> gift) {
  final String? rawUrl = gift['svg_url'] ?? gift['image_url'] ?? gift['icon_url'];

  if (rawUrl != null && rawUrl.trim().isNotEmpty) {
    String cleanUrl = rawUrl.trim();
    
    // Rewrite localhost to live domain if running on mobile device
    if (cleanUrl.contains('localhost') || cleanUrl.contains('127.0.0.1')) {
      cleanUrl = cleanUrl.replaceAll(RegExp(r'https?://(localhost|127\.0\.0\.1)(:\d+)?/'), 'https://chinchins.live/');
    }

    if (cleanUrl.toLowerCase().endsWith('.svg') || cleanUrl.contains('.svg?')) {
      return SvgPicture.network(
        cleanUrl,
        width: 48,
        height: 48,
        fit: BoxFit.contain,
        placeholderBuilder: (_) => const Center(child: Text('🎁', style: TextStyle(fontSize: 28))),
      );
    }

    return Image.network(
      cleanUrl,
      width: 48,
      height: 48,
      fit: BoxFit.contain,
      errorBuilder: (_, __, ___) => const Center(child: Text('🎁', style: TextStyle(fontSize: 28))),
    );
  }

  return const Center(child: Text('🎁', style: TextStyle(fontSize: 28)));
}
```

### B. Send Gift Handler (With Instant Low-Balance Modal)
```dart
Future<void> onSendGiftPressed({
  required BuildContext context,
  required dynamic host,
  required Map<String, dynamic> selectedGift,
  required int quantity,
}) async {
  final userState = Provider.of<UserProvider>(context, listen: false);
  final int userCoins = userState.user?.coins ?? 0;
  final int cost = ((selectedGift['coins'] as num?)?.toInt() ?? 100) * quantity;

  // 1. Instant local balance check (< 0.01s)
  if (userCoins < cost) {
    RechargeGemsSheet.show(
      context,
      receiverId: host.id.toString(),
      receiverName: host.displayName ?? host.name ?? 'Streamer',
      receiverAvatar: host.avatarUrl ?? host.avatar,
      currentCoins: userCoins,
      action: 'gift',
    );
    return;
  }

  // 2. Sufficient coins -> send via API
  final response = await http.post(
    Uri.parse('https://chinchins.live/api/gifts/send'),
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'Authorization': 'Bearer ${userState.token}',
    },
    body: jsonEncode({
      'receiver_id': host.id,
      'gift_id': selectedGift['id'],
      'quantity': quantity,
      'context': 'chat',
    }),
  );

  final res = jsonDecode(response.body);
  if (res['status'] == true) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('Sent ${selectedGift['name']} successfully!')),
    );
  } else if (res['show_recharge_modal'] == true) {
    RechargeGemsSheet.show(context, receiverId: host.id.toString());
  }
}
```

---

## ✅ 10. Developer Checklist

- [x] **SVG Format:** All 160 Gift SVGs in `public/uploads/gifts/` and all 6 Coin Package SVGs are 100% SVG 1.1 compliant without `<feDropShadow>` filters.
- [x] **Asset URLs:** All Gift endpoints return absolute `https://chinchins.live/uploads/gifts/...` URLs for `svg_url`, `png_url`, and `image_url`.
- [x] **Zero-Delay Call Check:** Never shows "Call connecting..." or Agora screen when balance is 0; opens `RechargeGemsSheet` in < 0.1s.
- [x] **Dynamic Multipliers:** Replaced hardcoded `x1, x5, x10, x99` with backend list `[1, 10, 66, 99, 520, 1314]`.
- [x] **Send Gift Pre-Check:** Checks `userCoins < gift.coins * quantity` locally before sending; triggers instant recharge modal if low.
- [x] **Earnings Conversion:** Endpoint `POST /api/wallet/convert-earnings` allows receivers to convert gift earnings to main coins to gift others.
- [x] **VIP Daily Claims:** Users can claim daily rewards via `POST /api/vip-cards/claim-daily` which adds directly to their gems balance.
