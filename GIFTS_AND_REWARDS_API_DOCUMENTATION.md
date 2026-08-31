# 🎁 Chinchins Live — Gifts, Rewards & Profile "Gifts Received" APIs Documentation

This document provides the complete RESTful API reference and Flutter integration guide for mobile app developers to build:
1. **User Profile Screen — Gifts Received Card** (Screenshot 1: Charm Level, Top Fans, and the 8-slot "Gifts Received >" card showing gift icons, diamond prices like `💎 17.70K`, and received counts like `x2`, `x1`, `x4`, `x32`).
2. **Full Screen "Gifts Received" Page** (Screenshot 2: Header gift box banner, full scrollable grid of all received gifts with dynamic formatted prices like `17.70K x1`, `10K x2`, `9.99K x6`, `5.55K x2`, `5K x18`, `4.44K x43`).
3. **In-App Gift Store & Sending API** (Bottom sheet modal in Live Stream, 1-on-1 Video Calls, Match Tab, and Chat to send gifts, deduct sender coins, credit host earnings, and trigger animated gift overlays).
4. **Admin Panel Management** (Upload gift images to `public/uploads/gifts`, set coin prices, slots/badges, categories, and direct award rewards to hosts).

---

## 🌐 Base URL & Request Headers

- **Base URL:** `http://your-domain.com/api` (or `http://10.0.2.2:8000/api` for Android Emulator / `http://localhost:8000/api` for iOS Simulator / Web)
- **Standard Headers:**
  ```http
  Authorization: Bearer <SANCTUM_TOKEN>
  Accept: application/json
  Content-Type: application/json
  ```
  *(Note: `user_id` or `account_id` can also be passed in body/query or headers as fallback).*

---

# 1️⃣ User Profile "Gifts Received" & Charm Level API

### 📱 Screen Reference: User Profile Screen (Screenshot 1)
Shows:
- **Charm Level:** e.g. `💎 Charm Level Lv6`
- **Top Fans:** e.g. `👑 Top Fans Sajid`
- **Gifts Received >** Card: 8-item grid containing gift icon, diamond badge (e.g. `💎 17.70K`, `💎 17K`, `💎 9.99K`, `💎 6.66K`, `💎 5.55K`), and received count (e.g. `x2`, `x1`, `x4`, `x1`, `x2`, `x32`, `x4`, `x12`). Tapping `>` navigates to the Full Gifts Received Screen.

---

### 🔹 Endpoint 1.1: Get User Gifts & Profile Summary
- **Method:** `GET`
- **Path:** `/api/gifts/received/{userId}` *(or `/api/profile/{userId}/gifts` or `/api/gifts/received/me`)*
- **Headers:** `Authorization: Bearer <TOKEN>` *(optional if public profile)*

#### 📥 Success Response (`200 OK`):
```json
{
  "status": true,
  "message": "User gifts loaded successfully.",
  "data": {
    "user": {
      "id": 12,
      "account_id": "8934217890",
      "display_name": "Nusrat",
      "avatar_url": "http://your-domain.com/uploads/profiles/nusrat_avatar.jpg",
      "coins": 45000
    },
    "charm_level": {
      "level": 6,
      "level_tag": "Lv6",
      "progress": 75
    },
    "top_fan": {
      "id": 45,
      "account_id": "1000293841",
      "name": "Sajid",
      "avatar_url": "http://your-domain.com/uploads/avatars/sajid.jpg",
      "fan_coins": 54200,
      "formatted": "54.20K"
    },
    "summary": {
      "total_unique_gifts": 16,
      "total_items_count": 214,
      "total_coins": 959840,
      "formatted_coins": "959.84K"
    },
    "profile_preview_gifts": [
      {
        "gift_id": 1,
        "name": "Romantic Couple",
        "category": "romantic",
        "image_url": "http://your-domain.com/uploads/gifts/romantic_couple.png",
        "coins": 17700,
        "formatted_coins": "17.70K",
        "quantity": 2,
        "count_label": "x2",
        "total_coins": 35400,
        "formatted_total": "35.40K"
      },
      {
        "gift_id": 2,
        "name": "Golden Sunset Couple",
        "category": "romantic",
        "image_url": "http://your-domain.com/uploads/gifts/sunset_couple.png",
        "coins": 17700,
        "formatted_coins": "17.70K",
        "quantity": 1,
        "count_label": "x1",
        "total_coins": 17700,
        "formatted_total": "17.70K"
      },
      {
        "gift_id": 3,
        "name": "Vintage Romance",
        "category": "romantic",
        "image_url": "http://your-domain.com/uploads/gifts/vintage_romance.png",
        "coins": 17000,
        "formatted_coins": "17K",
        "quantity": 4,
        "count_label": "x4",
        "total_coins": 68000,
        "formatted_total": "68K"
      },
      {
        "gift_id": 4,
        "name": "Candlelight Dinner",
        "category": "luxury",
        "image_url": "http://your-domain.com/uploads/gifts/candlelight_dinner.png",
        "coins": 17000,
        "formatted_coins": "17K",
        "quantity": 1,
        "count_label": "x1",
        "total_coins": 17000,
        "formatted_total": "17K"
      },
      {
        "gift_id": 6,
        "name": "Supercar & Billionaire",
        "category": "luxury",
        "image_url": "http://your-domain.com/uploads/gifts/supercar_luxury.png",
        "coins": 9990,
        "formatted_coins": "9.99K",
        "quantity": 32,
        "count_label": "x32",
        "total_coins": 319680,
        "formatted_total": "319.68K"
      },
      {
        "gift_id": 8,
        "name": "Space Battleship",
        "category": "effects",
        "image_url": "http://your-domain.com/uploads/gifts/space_battleship.png",
        "coins": 6660,
        "formatted_coins": "6.66K",
        "quantity": 4,
        "count_label": "x4",
        "total_coins": 26640,
        "formatted_total": "26.64K"
      },
      {
        "gift_id": 9,
        "name": "Fire Dragon",
        "category": "effects",
        "image_url": "http://your-domain.com/uploads/gifts/fire_dragon.png",
        "coins": 5550,
        "formatted_coins": "5.55K",
        "quantity": 12,
        "count_label": "x12",
        "total_coins": 66600,
        "formatted_total": "66.60K"
      }
    ],
    "gifts_received": [ /* Full list of all received gifts */ ]
  }
}
```

---

# 2️⃣ Full "Gifts Received" Screen API

### 📱 Screen Reference: Gifts Received Full Page (Screenshot 2)
Shows:
- Yellow glowing gift-box header with **"Gifts Received"** title
- 4-column responsive grid of all gifts the host has ever received
- Displays each item with:
  - Gift thumbnail image
  - Diamond coin value tag (e.g. `💎 17.70K`, `💎 10K`, `💎 9.99K`, `💎 7.20K`, `💎 6.66K`, `💎 5.55K`, `💎 5K`, `💎 4.69K`, `💎 4.44K`, `💎 4.21K`, `💎 3.70K`)
  - Multiplier count (e.g. `x1`, `x10`, `x2`, `x6`, `x3`, `x18`, `x43`, `x13`)

---

### 🔹 Endpoint 2.1: Get All Received Gifts
- **Method:** `GET`
- **Path:** `/api/gifts/received/{userId}`
- **Response `data.gifts_received`** contains the complete array of items to render directly in Flutter `GridView.builder`.

---

# 3️⃣ In-App Gifts Catalog & Store API

### 📱 Screen Reference: Gift Picker Dialog / Bottom Sheet
Used by users/callers to choose gifts to send during live streaming, audio/video calls, and chat.

---

### 🔹 Endpoint 3.1: Get Gifts Catalog (Store)
- **Method:** `GET`
- **Path:** `/api/gifts` *(or `/api/gifts/catalog`)*
- **Query Parameters (Optional):**
  - `category` *(string)*: `all` | `popular` | `luxury` | `romantic` | `effects` | `vip`

#### 📥 Success Response (`200 OK`):
```json
{
  "status": true,
  "message": "Gifts catalog loaded successfully.",
  "data": {
    "user_balance": {
      "coins": 50000,
      "formatted_coins": "50K"
    },
    "categories": {
      "all": 16,
      "popular": 3,
      "luxury": 4,
      "romantic": 4,
      "effects": 4,
      "vip": 1
    },
    "total_gifts": 16,
    "gifts": [
      {
        "id": 1,
        "name": "Romantic Couple",
        "coins": 17700,
        "formatted_coins": "17.70K",
        "category": "romantic",
        "badge": "HOT",
        "image_url": "http://your-domain.com/uploads/gifts/romantic_couple.png",
        "animation_full_url": "http://your-domain.com/uploads/gifts/romantic_couple.svga",
        "animation_type": "svga",
        "sound_url": null,
        "is_broadcast": false,
        "sort_order": 1
      },
      {
        "id": 6,
        "name": "Supercar & Billionaire",
        "coins": 9990,
        "formatted_coins": "9.99K",
        "category": "luxury",
        "badge": "3D",
        "image_url": "http://your-domain.com/uploads/gifts/supercar_luxury.png",
        "animation_full_url": null,
        "animation_type": "image",
        "is_broadcast": true,
        "sort_order": 6
      }
    ]
  }
}
```

---

# 4️⃣ Send Gift API (Fan ➡️ Host)

### 🔹 Endpoint 4.1: Send Gift to Host / User
Deducts coins from the sender's wallet, records received gift on the receiver's profile, splits revenue with the host, logs transaction history, and returns animation metadata for Flutter to display.

- **Method:** `POST`
- **Path:** `/api/gifts/send` *(or `/api/gift/send`)*
- **Headers:** `Authorization: Bearer <TOKEN>`
- **Request Body (`application/json`):**
  ```json
  {
    "receiver_id": 12,
    "gift_id": 1,
    "quantity": 2,
    "context": "profile",
    "call_session_id": null
  }
  ```

#### 📥 Success Response (`200 OK`):
```json
{
  "status": true,
  "message": "Successfully sent 2x Romantic Couple to Nusrat!",
  "data": {
    "transaction_id": 89,
    "gift": {
      "id": 1,
      "name": "Romantic Couple",
      "coins": 17700,
      "formatted_coins": "17.70K",
      "image_url": "http://your-domain.com/uploads/gifts/romantic_couple.png",
      "animation_url": "http://your-domain.com/uploads/gifts/romantic_couple.svga",
      "animation_type": "svga",
      "sound_url": null,
      "is_broadcast": false
    },
    "quantity": 2,
    "count_label": "x2",
    "total_cost": 35400,
    "formatted_cost": "35.40K",
    "sender": {
      "id": 45,
      "display_name": "Sajid",
      "remaining_coins": 14600,
      "formatted_coins": "14.60K"
    },
    "receiver": {
      "id": 12,
      "display_name": "Nusrat",
      "updated_slot": "x4"
    }
  }
}
```

#### ⚠️ Error Responses:
- **Insufficient Coins (`402 Payment Required`):**
  ```json
  {
    "status": false,
    "message": "Insufficient coin balance! You need 35400 coins but have 5000 coins.",
    "required_coins": 35400,
    "current_coins": 5000,
    "shortage": 30400
  }
  ```
- **Self-Gifting Not Allowed (`400 Bad Request`):**
  ```json
  {
    "status": false,
    "message": "You cannot send a gift to yourself."
  }
  ```

---

# 5️⃣ Flutter Dart Models & API Service

### 📦 `lib/core/models/gift_item.dart`
```dart
class ReceivedGiftItem {
  final int giftId;
  final String name;
  final String imageUrl;
  final String? animationUrl;
  final String? animationType;
  final int coins;
  final String formattedCoins;
  final int quantity;
  final String countLabel; // e.g. "x2", "x32"
  final int totalCoins;
  final String formattedTotal;
  final String? badge;

  ReceivedGiftItem({
    required this.giftId,
    required this.name,
    required this.imageUrl,
    this.animationUrl,
    this.animationType,
    required this.coins,
    required this.formattedCoins,
    required this.quantity,
    required this.countLabel,
    required this.totalCoins,
    required this.formattedTotal,
    this.badge,
  });

  factory ReceivedGiftItem.fromJson(Map<String, dynamic> json) {
    return ReceivedGiftItem(
      giftId: json['gift_id'] ?? json['id'] ?? 0,
      name: json['name'] ?? '',
      imageUrl: json['image_url'] ?? '',
      animationUrl: json['animation_url'],
      animationType: json['animation_type'] ?? 'image',
      coins: json['coins'] ?? 0,
      formattedCoins: json['formatted_coins'] ?? '${json['coins']}',
      quantity: json['quantity'] ?? 1,
      countLabel: json['count_label'] ?? 'x${json['quantity'] ?? 1}',
      totalCoins: json['total_coins'] ?? 0,
      formattedTotal: json['formatted_total'] ?? '',
      badge: json['badge'],
    );
  }
}
```

### 📦 `lib/features/profile/widgets/gifts_received_card.dart`
```dart
import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';

class GiftsReceivedCard extends StatelessWidget {
  final List<ReceivedGiftItem> gifts;
  final VoidCallback onTapViewAll;

  const GiftsReceivedCard({
    Key? key,
    required this.gifts,
    required this.onTapViewAll,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFF1E1B2E), // Dark purple glassmorphic background
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: Colors.white.withOpacity(0.08)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header Row with Arrow
          InkWell(
            onTap: onTapViewAll,
            borderRadius: BorderRadius.circular(8),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Row(
                  children: [
                    Text(
                      'Gifts Received',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    SizedBox(width: 6),
                    Icon(Icons.arrow_forward_ios, color: Colors.white70, size: 14),
                  ],
                ),
                // Glowing heart badge in corner
                Container(
                  padding: const EdgeInsets.all(6),
                  decoration: BoxDecoration(
                    color: Colors.pink.withOpacity(0.2),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(Icons.favorite, color: Colors.pinkAccent, size: 18),
                ),
              ],
            ),
          ),
          const SizedBox(height: 14),

          // 4-column Grid matching Screenshot 1
          GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: gifts.length > 8 ? 8 : gifts.length,
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 4,
              crossAxisSpacing: 10,
              mainAxisSpacing: 12,
              childAspectRatio: 0.72,
            ),
            itemBuilder: (context, index) {
              final gift = gifts[index];
              return Container(
                padding: const EdgeInsets.all(6),
                decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.05),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: Colors.white.withOpacity(0.05)),
                ),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    // Gift Image
                    Expanded(
                      child: CachedNetworkImage(
                        imageUrl: gift.imageUrl,
                        fit: BoxFit.contain,
                        placeholder: (ctx, url) => const Center(child: CircularProgressIndicator(strokeWidth: 2)),
                        errorWidget: (ctx, url, err) => const Icon(Icons.card_giftcard, color: Colors.pinkAccent),
                      ),
                    ),
                    const SizedBox(height: 4),

                    // Diamond Price Badge (e.g. 💎 17.70K)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
                          colors: [Color(0xFF8B5CF6), Color(0xFF6366F1)],
                        ),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(Icons.diamond, size: 10, color: Colors.cyanAccent),
                          const SizedBox(width: 2),
                          Text(
                            gift.formattedCoins,
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 10,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 2),

                    // Multiplier Count (e.g. x2, x32)
                    Text(
                      gift.countLabel,
                      style: TextStyle(
                        color: Colors.white.withOpacity(0.6),
                        fontSize: 11,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                ),
              );
            },
          ),
        ],
      ),
    );
  }
}
```

---

# 5️⃣ Top Fans Leaderboard & Live Love/Like Heart APIs

### 📱 Screen Reference: Top Fans Leaderboard
When a user taps on the **Top Fans** badge (with the crown) on the Host profile, this leaderboard displays all fans ranked #1, #2, #3 (Gold, Silver, Bronze Crowns) and subsequent ranks based on total coins gifted and love hearts sent.

---

### 🔹 Endpoint 5.1: Get Host's Top Fans Leaderboard
- **Method:** `GET`
- **Path:** `/api/profile/{userId}/top-fans` *(or `/api/gifts/top-fans/{userId}`)*

#### 📥 Success Response (`200 OK`):
```json
{
  "status": true,
  "message": "Top fans leaderboard loaded successfully.",
  "data": {
    "host": {
      "id": 12,
      "account_id": "8934217890",
      "display_name": "Nusrat",
      "avatar_url": "http://your-domain.com/uploads/profiles/nusrat_avatar.jpg"
    },
    "top_fans": [
      {
        "rank": 1,
        "user_id": 45,
        "account_id": "1000293841",
        "display_name": "Sajid",
        "avatar_url": "http://your-domain.com/uploads/avatars/sajid.jpg",
        "gender": "male",
        "total_coins": 54200,
        "formatted_coins": "54.20K",
        "gifts_count": 14,
        "crown_type": "gold",
        "badge": "Top #1"
      },
      {
        "rank": 2,
        "user_id": 78,
        "account_id": "1000293842",
        "display_name": "Rahim Khan",
        "avatar_url": "http://your-domain.com/uploads/avatars/rahim.jpg",
        "gender": "male",
        "total_coins": 32000,
        "formatted_coins": "32K",
        "gifts_count": 8,
        "crown_type": "silver",
        "badge": "Top #2"
      },
      {
        "rank": 3,
        "user_id": 92,
        "account_id": "1000293843",
        "display_name": "Tanvir",
        "avatar_url": "http://your-domain.com/uploads/avatars/tanvir.jpg",
        "gender": "male",
        "total_coins": 18500,
        "formatted_coins": "18.50K",
        "gifts_count": 4,
        "crown_type": "bronze",
        "badge": "Top #3"
      }
    ]
  }
}
```

---

### 🔹 Endpoint 5.2: Send Love / Like Heart to Host
Send love hearts during a 1-on-1 video call or on the host's profile.

- **Method:** `POST`
- **Path:** `/api/profile/{userId}/like` *(or `/api/gifts/like`)*
- **Headers:** `Authorization: Bearer <TOKEN>`
- **Request Body (`application/json`):**
  ```json
  {
    "count": 1,
    "context": "call"
  }
  ```

#### 📥 Success Response (`200 OK`):
```json
{
  "status": true,
  "message": "Love heart sent!",
  "data": {
    "receiver_id": 12,
    "total_likes": 1420,
    "formatted_likes": "1.42K"
  }
}
```

---

# 6️⃣ Summary of All Available Endpoints

| Endpoint | Method | Description | Auth Required |
| :--- | :---: | :--- | :---: |
| `/api/gifts` | `GET` | Get full gift catalog & category counts | No |
| `/api/gifts/catalog` | `GET` | Store catalog alias | No |
| `/api/gifts/received/{userId}` | `GET` | Get host received gifts, Charm Level & Top Fan | No |
| `/api/profile/{userId}/gifts` | `GET` | Profile gifts alias | No |
| `/api/profile/{userId}/top-fans` | `GET` | Top Fans Leaderboard (#1 Gold, #2 Silver, #3 Bronze) | No |
| `/api/profile/{userId}/like` | `POST` | Send Love Heart / Like to host | **Yes** (Bearer) |
| `/api/gifts/send` | `POST` | Send gift from fan to host with coin deduction | **Yes** (Bearer) |
| `/admin/gifts` | `GET` | Web Admin Panel Gift Catalog & Stats | Admin |
| `/admin/gifts` | `POST` | Add New Gift (supports `17.70K`, `17.70`, `500` coin input) | Admin |
| `/admin/gifts/{id}` | `PUT` | Update gift price, name, image, category | Admin |
| `/admin/gifts/{id}` | `DELETE` | Delete gift item | Admin |
| `/admin/gifts/{id}/toggle-status` | `POST` | Toggle active/inactive in app | Admin |
| `/admin/gifts/levels` | `POST` | Configure Charm Level coin thresholds (10K, 20K, 30K...) | Admin |
| `/admin/gifts/give-to-user` | `POST` | Direct Admin Reward tool to give gift to host | Admin |
| `/admin/gifts/logs` | `GET` | Complete gifts transaction & received ledger | Admin |

