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
6. [Ready-to-Copy Flutter Dart Implementation Code](#6-ready-to-copy-flutter-dart-implementation-code)
7. [Developer Checklist](#7-developer-checklist)

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
    },
    {
      "id": 3,
      "title": "Popular Pack",
      "coins": 16380,
      "base_coins": 16380,
      "bonus_coins": 0,
      "total_coins": 16380,
      "formatted_coins": "16,380",
      "price": 600.00,
      "price_bdt": 600.00,
      "formatted_price": "BDT 600.00",
      "badge": "17% off",
      "badge_color": "pink",
      "is_once_offer": false,
      "icon_url": "uploads/coin_packages/gem_tier3_triple.svg",
      "icon_full_url": "https://chinchins.live/uploads/coin_packages/gem_tier3_triple.svg",
      "svg_url": "https://chinchins.live/uploads/coin_packages/gem_tier3_triple.svg",
      "png_url": "https://chinchins.live/uploads/coin_packages/gem_tier3_triple.png",
      "image_url": "https://chinchins.live/uploads/coin_packages/gem_tier3_triple.svg",
      "is_popular": false
    },
    {
      "id": 4,
      "title": "Super Pack",
      "coins": 32940,
      "base_coins": 32940,
      "bonus_coins": 0,
      "total_coins": 32940,
      "formatted_coins": "32,940",
      "price": 1200.00,
      "price_bdt": 1200.00,
      "formatted_price": "BDT 1,200.00",
      "badge": "30% off",
      "badge_color": "pink",
      "is_once_offer": false,
      "icon_url": "uploads/coin_packages/gem_tier4_stack.svg",
      "icon_full_url": "https://chinchins.live/uploads/coin_packages/gem_tier4_stack.svg",
      "svg_url": "https://chinchins.live/uploads/coin_packages/gem_tier4_stack.svg",
      "png_url": "https://chinchins.live/uploads/coin_packages/gem_tier4_stack.png",
      "image_url": "https://chinchins.live/uploads/coin_packages/gem_tier4_stack.svg",
      "is_popular": false
    },
    {
      "id": 5,
      "title": "Mega Pack",
      "coins": 66600,
      "base_coins": 66600,
      "bonus_coins": 0,
      "total_coins": 66600,
      "formatted_coins": "66,600",
      "price": 2400.00,
      "price_bdt": 2400.00,
      "formatted_price": "BDT 2,400.00",
      "badge": "60% off",
      "badge_color": "pink",
      "is_once_offer": false,
      "icon_url": "uploads/coin_packages/gem_tier5_tray.svg",
      "icon_full_url": "https://chinchins.live/uploads/coin_packages/gem_tier5_tray.svg",
      "svg_url": "https://chinchins.live/uploads/coin_packages/gem_tier5_tray.svg",
      "png_url": "https://chinchins.live/uploads/coin_packages/gem_tier5_tray.png",
      "image_url": "https://chinchins.live/uploads/coin_packages/gem_tier5_tray.svg",
      "is_popular": false
    },
    {
      "id": 6,
      "title": "VIP King Pack",
      "coins": 167400,
      "base_coins": 167400,
      "bonus_coins": 0,
      "total_coins": 167400,
      "formatted_coins": "167,400",
      "price": 6100.00,
      "price_bdt": 6100.00,
      "formatted_price": "BDT 6,100.00",
      "badge": "80% off",
      "badge_color": "pink",
      "is_once_offer": false,
      "icon_url": "uploads/coin_packages/gem_tier6_chest.svg",
      "icon_full_url": "https://chinchins.live/uploads/coin_packages/gem_tier6_chest.svg",
      "svg_url": "https://chinchins.live/uploads/coin_packages/gem_tier6_chest.svg",
      "png_url": "https://chinchins.live/uploads/coin_packages/gem_tier6_chest.png",
      "image_url": "https://chinchins.live/uploads/coin_packages/gem_tier6_chest.svg",
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

### Correct Flutter Widget for Rendering Package Diamonds
In `lib/features/wallet/widgets/recharge_gems_sheet.dart`:

```dart
import 'package:flutter/material.dart';
import 'package:flutter_svg/flutter_svg.dart';

Widget buildPackageIcon(Map<String, dynamic> pkg, int index, bool isSelected) {
  // 1. Pick the best image URL from the API response
  final String? rawUrl = pkg['svg_url'] ?? 
                         pkg['icon_full_url'] ?? 
                         pkg['image_url'] ?? 
                         pkg['icon_url'];

  if (rawUrl != null && rawUrl.trim().isNotEmpty) {
    String cleanUrl = rawUrl.trim();
    
    // Fix: If on an Android device or emulator and URL contains localhost, rewrite to live domain
    if (cleanUrl.contains('localhost') || cleanUrl.contains('127.0.0.1')) {
      cleanUrl = cleanUrl.replaceAll(RegExp(r'https?://(localhost|127\.0\.0\.1)(:\d+)?/'), 'https://chinchins.live/');
    }

    // 2. Render SVG with flutter_svg
    if (cleanUrl.toLowerCase().endsWith('.svg') || cleanUrl.contains('.svg?')) {
      return SvgPicture.network(
        cleanUrl,
        width: 44,
        height: 44,
        fit: BoxFit.contain,
        placeholderBuilder: (context) => _buildDiamondFallback(index, isSelected),
      );
    }

    // 3. Fallback to raster image if PNG/WebP
    return Image.network(
      cleanUrl,
      width: 44,
      height: 44,
      fit: BoxFit.contain,
      errorBuilder: (context, error, stackTrace) => _buildDiamondFallback(index, isSelected),
    );
  }

  // 4. Default native diamond artwork
  return _buildDiamondFallback(index, isSelected);
}

/// Native Flutter golden diamond backup (never shows purple person icon!)
Widget _buildDiamondFallback(int index, bool isSelected) {
  return Container(
    width: 40,
    height: 40,
    decoration: BoxDecoration(
      shape: BoxShape.circle,
      gradient: RadialGradient(
        colors: isSelected 
          ? [const Color(0xFFFFE066), const Color(0xFFF59E0B)]
          : [const Color(0xFFFBBF24), const Color(0xFFD97706)],
      ),
      boxShadow: [
        BoxShadow(
          color: const Color(0xFFF59E0B).withOpacity(0.4),
          blurRadius: 8,
          offset: const Offset(0, 2),
        ),
      ],
    ),
    child: const Center(
      child: Text('💎', style: TextStyle(fontSize: 22)),
    ),
  );
}
```

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

> 💡 **Best Practice:** Keep default country parameter set to `'All'` so when the user opens the Hot tab, all streamers worldwide are visible immediately without empty state.

---

## 🔍 5. User Search & Single Profile Details APIs

- **User Search:** `GET https://chinchins.live/api/search?q={query}`  
  Supports searching by 8-digit Account ID (`602281635`) or streamer Name (`Ayeena`).
- **Single Profile:** `GET https://chinchins.live/api/profile/{id_or_account_id}`  
  Returns comprehensive host profile with avatar, gallery images, video call rate, and bio.

---

## 💻 6. Ready-to-Copy Flutter Dart Implementation Code

### Call Button Tap Handler (With 0-Delay Instant Recharge Sheet)

Place this logic inside your call button handler on the Streamer Card / Profile Screen:

```dart
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../services/call_api_service.dart';
import '../widgets/recharge_gems_sheet.dart';
import '../screens/agora_call_screen.dart';

Future<void> handleCallButtonPressed({
  required BuildContext context,
  required dynamic host, // Host model or Map
  required String callType, // 'video' or 'audio'
}) async {
  // 1. Get current logged-in user balance from provider/state
  final userState = Provider.of<UserProvider>(context, listen: false);
  final int userCoins = userState.user?.coins ?? 0;
  
  final int ratePerMinute = (callType == 'audio')
      ? (host.audioCallRate ?? 60)
      : (host.videoCallRate ?? host.ratePerMinute ?? 1800);

  // -------------------------------------------------------------
  // ⚡ ZERO-DELAY INSTANT CHECK (< 0.01s):
  // If user has 0 coins or less than 1 minute of call rate,
  // DO NOT navigate to CallScreen!
  // DO NOT show "Call connecting..." or any loading spinner!
  // Immediately show RechargeGemsSheet!
  // -------------------------------------------------------------
  if (userCoins < ratePerMinute) {
    RechargeGemsSheet.show(
      context,
      receiverId: host.id.toString(),
      receiverName: host.name ?? host.displayName ?? 'Streamer',
      receiverAvatar: host.avatarUrl ?? host.avatar,
      ratePerMinute: ratePerMinute,
      currentCoins: userCoins,
      action: 'call',
    );
    return; // Exit immediately!
  }

  // 2. User has coins locally -> verify with server permission API
  final result = await CallApiService.checkCallPermission(
    receiverId: host.id.toString(),
    callType: callType,
  );

  if (!context.mounted) return;

  if (result['can_call'] == true) {
    // 3. Permitted -> launch actual Agora / WebRTC calling screen
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => AgoraCallScreen(
          callId: result['call_id'],
          channelName: result['channel_name'],
          host: host,
          callType: callType,
        ),
      ),
    );
  } else {
    // 4. Server indicated insufficient balance -> show recharge sheet
    RechargeGemsSheet.show(
      context,
      receiverId: host.id.toString(),
      receiverName: host.name,
      receiverAvatar: host.avatarUrl,
      ratePerMinute: ratePerMinute,
      currentCoins: result['user_balance'] ?? userCoins,
      action: 'call',
    );
  }
}
```

---

## ✅ 7. Developer Checklist

- [x] **SVG Format:** All 6 package SVG illustrations (`gem_tier1_single.svg` through `gem_tier6_chest.svg`) are 100% SVG 1.1 compliant without `<feDropShadow>` filters.
- [x] **URL Resolution:** Backend returns absolute `https://chinchins.live/uploads/coin_packages/...` URLs for `svg_url`, `png_url`, and `icon_full_url`.
- [x] **Zero-Delay Balance Check:** Call button checks `userCoins < ratePerMinute` in-memory first; never shows "Call connecting..." or Agora screen when balance is 0.
- [x] **Recharge Modal:** Loads all 6 packages instantly with badges (`50% off`, `ONCE`, `17% off`, etc.) and exact BDT pricing.
- [x] **Streamer Feed:** Explore screen loads all global streamers on start with country filter support for `BGD`, `PAK`, `Global`, etc.
