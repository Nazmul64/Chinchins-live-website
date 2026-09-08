# Chinchins Live — Flutter Integration & Master API Guide (Updated 2026)

> **Base Production URL:** `https://chinchins.live/api`  
> **Static Assets Base:** `https://chinchins.live/uploads/`  
> **Auth Header:** `Authorization: Bearer <sanctum_token>` or `user_id: <id>`  
> **Protocol:** JSON REST API (Clean SVGs & PNGs validated for `flutter_svg`)

---

## 1. Quick Navigation

1. [My Bag (আমার ব্যাগ) Architecture](#2-my-bag-আমার-ব্যাগ-architecture)
2. [My Bag APIs](#3-my-bag-apis)
   - [Get Bag Inventory](#31-get-bag-inventory)
   - [Get Bag Store Catalog](#32-get-bag-store-catalog)
   - [Purchase Bag Item](#33-purchase-bag-item)
   - [Equip / Use Item](#34-equip--use-item)
   - [Unequip Item](#35-unequip-item)
   - [Search Recipient by Account ID](#36-search-recipient-by-account-id-for-gifting)
   - [Gift Item to User by Account ID](#37-gift-item-to-user-by-account-id)
3. [Global & Home Search by 8-Digit Account ID](#4-global--home-search-by-8-digit-account-id)
4. [Instant Greeting & Social Interactions](#5-instant-greeting--social-interactions)
5. [Streamer Feed & Pre-Call Verification](#6-streamer-feed--pre-call-verification)
6. [Flutter Implementation & Widgets](#7-flutter-implementation--widgets)

---

## 2. My Bag (আমার ব্যাগ) Architecture

My Bag supports **6 Item Categories** and **3 Status Sub-filters**:

### 6 Item Categories:
| Category Key | Display Name | Visual Effect | Clean Asset URL |
| :--- | :--- | :--- | :--- |
| `coupon` | **Coupon (কুপন)** | Recharge bonus % or coin vouchers | `https://chinchins.live/uploads/my_bag/coupon_sale_yellow.svg` |
| `avatar_frame` | **Avatar frame (এভাটার ফ্রেম)** | Animated circular frame around user avatar | `https://chinchins.live/uploads/my_bag/frame_royal_amethyst.svg` |
| `chat_style` | **Chat style (চ্যাট স্টাইল)** | Glowing bubble backdrop for live stream & 1v1 chat | `https://chinchins.live/uploads/my_bag/chat_bubble_neon_pink.svg` |
| `profile_card` | **Profile card (প্রোফাইল কার্ড)** | Gradient card background for user's profile | `https://chinchins.live/uploads/my_bag/profile_card_aurora_galaxy.svg` |
| `entrance_bubble`| **Entrance bubble (এন্ট্রান্স বাবল)** | Floating badge effect when user enters a live room | `https://chinchins.live/uploads/my_bag/entrance_bubble_gold_crown.svg` |
| `big_entrance` | **Big entrance (বিগ এন্ট্রান্স)** | Fullscreen sports car / luxury vehicle ride effect | `https://chinchins.live/uploads/my_bag/big_entrance_sports_car.svg` |

### 3 Status Sub-tabs:
- `unused`: Items ready to be equipped or used.
- `used`: Active/equipped items or redeemed coupons.
- `expired`: Items whose duration has elapsed.

---

## 3. My Bag APIs

### 3.1 Get Bag Inventory
Retrieves user's active bag inventory, category counts, equipped items, and balance.

- **Endpoint:** `GET /api/bag` (or `GET /api/my-bag`)
- **Query Params (Optional):**
  - `category`: `all`, `coupon`, `avatar_frame`, `chat_style`, `profile_card`, `entrance_bubble`, `big_entrance` (default: `all`)
  - `status`: `unused`, `used`, `expired`, `all` (default: `unused`)

#### Sample Response:
```json
{
  "status": true,
  "message": "User bag items retrieved successfully.",
  "data": {
    "user": {
      "id": 1,
      "name": "Alex",
      "display_name": "Alex",
      "coins": 4500,
      "account_id": "87261943"
    },
    "active_tab": "coupon",
    "active_status": "unused",
    "categories": [
      {
        "category": "coupon",
        "name": "Coupon",
        "count": 2,
        "is_active_tab": true,
        "icon_url": "https://chinchins.live/uploads/my_bag/coupon_sale_yellow.svg"
      },
      {
        "category": "avatar_frame",
        "name": "Avatar frame",
        "count": 1,
        "is_active_tab": false,
        "icon_url": "https://chinchins.live/uploads/my_bag/frame_royal_amethyst.svg"
      },
      {
        "category": "chat_style",
        "name": "Chat style",
        "count": 0,
        "is_active_tab": false,
        "icon_url": "https://chinchins.live/uploads/my_bag/chat_bubble_neon_pink.svg"
      }
    ],
    "total_count": 3,
    "items": [
      {
        "user_bag_item_id": 14,
        "bag_item_id": 1,
        "name": "50% Off Recharge Coupon",
        "category": "coupon",
        "category_name": "Coupon",
        "code": "coupon_sale_50",
        "quantity": 1,
        "status": "unused",
        "is_equipped": false,
        "icon_url": "https://chinchins.live/uploads/my_bag/coupon_sale_yellow.svg",
        "image_url": "https://chinchins.live/uploads/my_bag/coupon_sale_yellow.svg",
        "format": "svg",
        "badge": "50% OFF",
        "duration_text": "7 Days",
        "expires_at": "2026-09-15 12:00:00",
        "is_giftable": true
      }
    ]
  }
}
```

---

### 3.2 Get Bag Store Catalog
Triggered when user taps **"Visit Bag Store"** button.

- **Endpoint:** `GET /api/bag/store` (or `GET /api/my-bag/store`)
- **Query Params (Optional):**
  - `category`: Filter by category key (e.g. `coupon`, `avatar_frame`).

#### Sample Response:
```json
{
  "status": true,
  "message": "My Bag store catalog retrieved successfully.",
  "data": {
    "categories": {
      "coupon": "Coupon",
      "avatar_frame": "Avatar frame",
      "chat_style": "Chat style",
      "profile_card": "Profile card",
      "entrance_bubble": "Entrance bubble",
      "big_entrance": "Big entrance"
    },
    "total": 11,
    "items": [
      {
        "id": 1,
        "name": "50% Off Recharge Coupon",
        "category": "coupon",
        "category_name": "Coupon",
        "price_coins": 0,
        "price_bdt": 0.0,
        "formatted_price": "Free",
        "duration_days": 7,
        "duration_text": "7 Days",
        "badge": "50% OFF",
        "icon_url": "https://chinchins.live/uploads/my_bag/coupon_sale_yellow.svg",
        "image_url": "https://chinchins.live/uploads/my_bag/coupon_sale_yellow.svg",
        "format": "svg",
        "description": "Gives 50% extra gems on next diamond purchase.",
        "is_giftable": true
      },
      {
        "id": 3,
        "name": "Royal Amethyst Frame",
        "category": "avatar_frame",
        "price_coins": 1500,
        "price_bdt": 120.0,
        "formatted_price": "1,500 Gems",
        "duration_days": 30,
        "duration_text": "30 Days",
        "badge": "POPULAR",
        "icon_url": "https://chinchins.live/uploads/my_bag/frame_royal_amethyst.svg",
        "image_url": "https://chinchins.live/uploads/my_bag/frame_royal_amethyst.svg",
        "format": "svg",
        "is_giftable": true
      }
    ]
  }
}
```

---

### 3.3 Purchase Bag Item
Deducts coins from user wallet and places the item in user's bag.

- **Endpoint:** `POST /api/bag/purchase` (or `POST /api/my-bag/purchase`)
- **Headers:** `Authorization: Bearer <token>`
- **Body (JSON / Form):**
  ```json
  {
    "bag_item_id": 3,
    "quantity": 1
  }
  ```

#### Success Response (200):
```json
{
  "status": true,
  "message": "Successfully purchased Royal Amethyst Frame!",
  "data": {
    "user_bag_item_id": 25,
    "item_name": "Royal Amethyst Frame",
    "quantity": 1,
    "expires_at": "2026-10-08 12:30:00",
    "new_coins_balance": 3000
  }
}
```

#### Insufficient Balance Response (402):
```json
{
  "status": false,
  "message": "Insufficient coins balance. You need 1,500 Gems but have 300 Gems.",
  "code": "INSUFFICIENT_FUNDS",
  "data": {
    "current_coins": 300,
    "required_coins": 1500,
    "shortage": 1200,
    "deposit_url": "https://chinchins.live/admin/deposits/create"
  }
}
```
> **Flutter Action on 402:** Open Coin Recharge / Payment sheet immediately so user can top up.

---

### 3.4 Equip / Use Item
Activates an item (e.g. equips an Avatar Frame or Chat Bubble on profile).

- **Endpoint:** `POST /api/bag/use` (or `POST /api/my-bag/use`)
- **Body:**
  ```json
  {
    "user_bag_item_id": 25
  }
  ```

---

### 3.5 Unequip Item
Removes an equipped cosmetic item without deleting it from inventory.

- **Endpoint:** `POST /api/bag/unequip`
- **Body:**
  ```json
  {
    "user_bag_item_id": 25
  }
  ```

---

### 3.6 Search Recipient by Account ID (For Gifting)
Used when user taps the **"Gift"** icon or searches a friend's **8-digit Account ID** (e.g., `602281635`).

- **Endpoint:** `GET /api/bag/search-user` (or `GET /api/my-bag/search-user`)
- **Query Params:**
  - `q`: Recipient's 8-digit Account ID (e.g., `602281635`) or username/phone.

#### Sample Request:
```http
GET /api/bag/search-user?q=602281635
```

#### Success Response (200):
```json
{
  "status": true,
  "message": "Recipient user found successfully.",
  "data": {
    "id": 2,
    "account_id": "602281635",
    "name": "Ayeena04",
    "username": "Ayeena04",
    "display_name": "Ayeena04",
    "avatar": "https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=300&auto=format&fit=crop&q=80",
    "avatar_url": "https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=300&auto=format&fit=crop&q=80",
    "level": "Lv4",
    "country_flag": "🇧🇩",
    "gender": "female",
    "coins": 0
  }
}
```

---

### 3.7 Gift Item to User by Account ID
Transfers an item directly to the recipient's Bag Inventory.

- **Endpoint:** `POST /api/bag/gift` (or `POST /api/my-bag/send-gift`)
- **Body (JSON):**
  ```json
  {
    "receiver_account_id": "602281635",
    "bag_item_id": 1
  }
  ```
  *(Alternatively, pass `"user_bag_item_id": 14` if gifting from your own bag, or `"receiver_id": 2`)*.

#### Success Response (200):
```json
{
  "status": true,
  "message": "Successfully gifted 50% Off Recharge Coupon to Ayeena04!",
  "data": {
    "new_coins_balance": 4500
  }
}
```

---

## 4. Global & Home Search by 8-Digit Account ID

The search bar at the top of the App Home Screen supports searching by **8-digit Account ID** (e.g. `602281635`) or name:

- **Endpoint:** `GET /api/search?q={account_id_or_name}`
- **Query Params:**
  - `q`: Search keyword or 8-digit numeric Account ID.

#### Sample Request:
```http
GET /api/search?q=602281635
```

#### Sample Response (200):
```json
{
  "status": true,
  "message": "Search results retrieved successfully.",
  "data": {
    "query": "602281635",
    "is_id_search": true,
    "user": {
      "id": 2,
      "account_id": "602281635",
      "display_name": "Ayeena04",
      "avatar_url": "https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=300&auto=format&fit=crop&q=80",
      "country_flag": "🇧🇩",
      "charm_level": 4,
      "video_call_rate_text": "30 coins/min"
    },
    "users": [
      {
        "id": 2,
        "account_id": "602281635",
        "display_name": "Ayeena04",
        "avatar_url": "https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=300&auto=format&fit=crop&q=80",
        "country_flag": "🇧🇩"
      }
    ]
  }
}
```

---

## 5. Instant Greeting & Social Interactions

### 5.1 Instant "Hi" Greeting Button
Sends a pre-formatted friendly greeting to a streamer or user:
- **Endpoint:** `POST /api/chat/send-hi`
- **Body:**
  ```json
  {
    "recipient_id": 2
  }
  ```

### 5.2 Love Heart Like Button
Tapping the floating heart sends an instant like and updates the Top Fan leaderboard:
- **Endpoint:** `POST /api/profile/{id}/like`
- **Sample Response:**
  ```json
  {
    "status": true,
    "message": "Heart sent! Added to Top Fans.",
    "data": {
      "likes_count": 128,
      "is_top_fan": true
    }
  }
  ```

---

## 6. Streamer Feed & Pre-Call Verification

### 6.1 Streamer Profile Details
- **Endpoint:** `GET /api/profile/{id}`
- Returns complete profile info:
  - `speaking_languages`: e.g. `["English", "Bengali"]`
  - `charm_level`: e.g. `Lv8`
  - `top_fan`: Streamer's #1 supporter object
  - `video_call_rate_text`: e.g. `"60 Coins / min"`

### 6.2 Pre-Call Balance Check
Before launching Agora / WebRTC 1v1 video call:
1. Compare user's `coins` from `GET /api/profile/me` against the streamer's `video_call_rate`.
2. If `user.coins < rate`, display the Recharge Dialog.
3. When terminating the call, display confirmation: *"Sure to end call? [Cancel] [Confirm]"*.

---

## 7. Flutter Implementation & Widgets

### 7.1 Safe SVG Rendering (`flutter_svg`)
All SVGs on the server have been cleaned of unsupported `<filter>` and `<feDropShadow>` tags, ensuring 0 crashes.

```dart
import 'package:flutter/material.dart';
import 'package:flutter_svg/flutter_svg.dart';

Widget buildBagItemImage(String imageUrl, {double size = 80}) {
  if (imageUrl.endsWith('.svg')) {
    return SvgPicture.network(
      imageUrl,
      width: size,
      height: size,
      placeholderBuilder: (context) => const SizedBox(
        width: 40,
        height: 40,
        child: Center(child: CircularProgressIndicator(strokeWidth: 2)),
      ),
    );
  }
  return Image.network(
    imageUrl,
    width: size,
    height: size,
    fit: BoxFit.contain,
  );
}
```

---

### 7.2 Gift Modal with Account ID Search (`GiftBagItemDialog`)

```dart
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

class GiftBagItemDialog extends StatefulWidget {
  final int bagItemId;
  final String itemName;
  final String authToken;

  const GiftBagItemDialog({
    Key? key,
    required this.bagItemId,
    required this.itemName,
    required this.authToken,
  }) : super(key: key);

  @override
  State<GiftBagItemDialog> createState() => _GiftBagItemDialogState();
}

class _GiftBagItemDialogState extends State<GiftBagItemDialog> {
  final TextEditingController _searchController = TextEditingController();
  Map<String, dynamic>? _foundUser;
  bool _isLoading = false;
  bool _isSending = false;
  String? _errorMessage;

  Future<void> _searchUser(String accountId) async {
    if (accountId.trim().isEmpty) return;
    setState(() {
      _isLoading = true;
      _errorMessage = null;
      _foundUser = null;
    });

    try {
      final url = Uri.parse('https://chinchins.live/api/bag/search-user?q=${accountId.trim()}');
      final res = await http.get(url, headers: {
        'Accept': 'application/json',
        'Authorization': 'Bearer ${widget.authToken}',
      });

      final body = json.decode(res.body);
      if (res.statusCode == 200 && body['status'] == true) {
        setState(() => _foundUser = body['data']);
      } else {
        setState(() => _errorMessage = body['message'] ?? 'User not found');
      }
    } catch (e) {
      setState(() => _errorMessage = 'Search failed. Please try again.');
    } finally {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _sendGift() async {
    if (_foundUser == null) return;
    setState(() => _isSending = true);

    try {
      final url = Uri.parse('https://chinchins.live/api/bag/gift');
      final res = await http.post(
        url,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer ${widget.authToken}',
        },
        body: json.encode({
          'receiver_account_id': _foundUser!['account_id'],
          'bag_item_id': widget.bagItemId,
        }),
      );

      final body = json.decode(res.body);
      if (res.statusCode == 200 && body['status'] == true) {
        Navigator.pop(context, true);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(body['message'] ?? 'Gift sent successfully!')),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(body['message'] ?? 'Failed to send gift')),
        );
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Error sending gift')),
      );
    } finally {
      setState(() => _isSending = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      backgroundColor: const Color(0xFF1E1B2E),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      title: Text(
        'Gift ${widget.itemName}',
        style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
      ),
      content: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          TextField(
            controller: _searchController,
            keyboardType: TextInputType.number,
            style: const TextStyle(color: Colors.white),
            decoration: InputDecoration(
              hintText: 'Enter 8-digit Account ID',
              hintStyle: const TextStyle(color: Colors.white54),
              filled: true,
              fillColor: Colors.white10,
              suffixIcon: IconButton(
                icon: const Icon(Icons.search, color: Colors.purpleAccent),
                onPressed: () => _searchUser(_searchController.text),
              ),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: BorderSide.none,
              ),
            ),
            onSubmitted: _searchUser,
          ),
          const SizedBox(height: 16),
          if (_isLoading)
            const CircularProgressIndicator(color: Colors.purpleAccent)
          else if (_errorMessage != null)
            Text(_errorMessage!, style: const TextStyle(color: Colors.redAccent))
          else if (_foundUser != null)
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.white.withOpacity(0.05),
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: Colors.purpleAccent.withOpacity(0.3)),
              ),
              child: Row(
                children: [
                  CircleAvatar(
                    radius: 26,
                    backgroundImage: NetworkImage(_foundUser!['avatar_url'] ?? ''),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          _foundUser!['display_name'] ?? '',
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        Text(
                          'ID: ${_foundUser!['account_id']} • ${_foundUser!['country_flag'] ?? ''}',
                          style: const TextStyle(color: Colors.white70, fontSize: 13),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
        ],
      ),
      actions: [
        TextButton(
          onPressed: () => Navigator.pop(context),
          child: const Text('Cancel', style: TextStyle(color: Colors.white54)),
        ),
        ElevatedButton(
          style: ElevatedButton.styleFrom(
            backgroundColor: const Color(0xFF8B5CF6),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          ),
          onPressed: (_foundUser != null && !_isSending) ? _sendGift : null,
          child: _isSending
              ? const SizedBox(
                  width: 20,
                  height: 20,
                  child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                )
              : const Text('Send Gift', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
        ),
      ],
    );
  }
}
```

---

## 8. Summary Checklist

| Item | Status | Verified By |
| :--- | :---: | :--- |
| All 11 My Bag SVGs validated (0 `<filter>` crash tags) | ✅ | Verified with SVG analyzer |
| Production URLs generated via `CoinPackage::resolveAssetUrl` | ✅ | Verified `https://chinchins.live/uploads/my_bag/...` |
| Store API `GET /api/bag/store` returns 11 items with pictures | ✅ | Verified via test script |
| Search by 8-Digit Account ID `GET /api/bag/search-user?q={id}` | ✅ | Verified with `602281635` |
| Gift API `POST /api/bag/gift` with `receiver_account_id` | ✅ | Verified with `602281635` |
| Home Page Search `GET /api/search?q={account_id}` | ✅ | Verified with `602281635` |
