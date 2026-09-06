# 🎁 Gifts & In-App Rewards RESTful API & Flutter Integration Documentation

This document describes the complete **Gifts & Rewards System** for **Chinchins Live** (Web Admin Panel + Mobile Flutter App), matching top international live streaming applications (Poppo Live, Chamet, Bigo Live).

---

## 🌟 1. Categories & Badges Overview

The live streaming gift tray features **12 Categories** with dynamic counts and rich metadata:

| Key | Category Name | Emoji | Icon | Description | Sample Gifts |
|---|---|---|---|---|---|
| `all` | All Gifts | 🎁 | `fa-gift` | Complete gifts catalog | All items |
| `hot` | Hot | 🔥 | `fa-fire` | Top trending CP & high frequency items | Trophy Cup, Mystery Box, CP Kiss, Slot Machine |
| `lucky` | Lucky | 🍀 | `fa-clover` | Jackpot multipliers & fun interactive items | Lucky Chest (x500 win), Lucky Lips, Bombastic Blast |
| `svip` | SVIP | 👑 | `fa-crown` | Top tier luxury animations (18K - 75K coins) | Diva Star, Bengal Tiger, Convertible, Sky Castle |
| `intimacy` | Intimacy | 💖 | `fa-heart` | Romance, couples & friendship bonding | Love Letter (520), In My Hands (1314), Moon Lovers |
| `wealth` | Wealth | 💰 | `fa-coins` | High-roller items & billionaire assets | Gold Ingot, Blue Diamond, Mansion, Crystal Palace |
| `festival` | Festival | 🎉 | `fa-champagne-glasses` | Seasonal, Eid, New Year & carnival events | Sky Lanterns, Eid Crescent, 2026 Celebration |
| `bag` | Bag | 🎒 | `fa-bag-shopping` | User backpack items & vouchers | Lucky Tortoise, Gift Vouchers, Avatar Frames |
| `popular` | Popular | ⭐ | `fa-star` | Standard popular gifts | Rose Bouquet (99), Birthday Cake, Champagne |
| `romantic` | Romantic | 💕 | `fa-heart-circle-bolt` | Romantic gesture items | Sunset Couple, Love Mailbox, Candlelight Dinner |
| `luxury` | Luxury | 💎 | `fa-gem` | Supercars, private jets, and mega yachts | Ninja Sports Bike, Supercar, Supersonic Jet |
| `effects` | Effects / 3D | ⚡ | `fa-bolt` | Full-screen 3D particle animations | Fire Dragon, Phoenix Rebirth, Cosmic Starship |
| `vip` | VIP | 🌟 | `fa-award` | VIP status gifts | Royal Sovereign Crown, Mythic Treasure Chest |

---

## 🚀 2. RESTful API Endpoints

### 1️⃣ Get Gifts Catalog (`GET /api/gifts` or `GET /api/gifts/catalog`)
Returns the list of active gifts, user coin balance, and category metadata.

- **URL:** `GET /api/gifts` or `GET /api/gifts?category=hot`
- **Headers:** `Authorization: Bearer <token>` or `X-User-Id: <id>`
- **Query Parameters:**
  - `category` *(optional)*: `all`, `hot`, `lucky`, `svip`, `intimacy`, `wealth`, `festival`, `bag`, `popular`, `romantic`, `luxury`, `effects`, `vip`

#### Response Example:
```json
{
  "status": true,
  "message": "Gifts catalog loaded successfully.",
  "data": {
    "user_balance": {
      "coins": 45000,
      "formatted_coins": "45K"
    },
    "selected_category": "hot",
    "categories_list": [
      { "key": "all", "label": "All", "emoji": "🎁", "icon": "fa-gift", "color": "#64748b", "count": 172, "is_active": false },
      { "key": "hot", "label": "Hot", "emoji": "🔥", "icon": "fa-fire", "color": "#f43f5e", "count": 16, "is_active": true },
      { "key": "lucky", "label": "Lucky", "emoji": "🍀", "icon": "fa-clover", "color": "#10b981", "count": 6, "is_active": false },
      { "key": "svip", "label": "SVIP", "emoji": "👑", "icon": "fa-crown", "color": "#f59e0b", "count": 9, "is_active": false },
      { "key": "intimacy", "label": "Intimacy", "emoji": "💖", "icon": "fa-heart", "color": "#ec4899", "count": 7, "is_active": false },
      { "key": "wealth", "label": "Wealth", "emoji": "💰", "icon": "fa-coins", "color": "#eab308", "count": 8, "is_active": false },
      { "key": "festival", "label": "Festival", "emoji": "🎉", "icon": "fa-champagne-glasses", "color": "#8b5cf6", "count": 5, "is_active": false },
      { "key": "bag", "label": "Bag", "emoji": "🎒", "icon": "fa-bag-shopping", "color": "#06b6d4", "count": 3, "is_active": false },
      { "key": "popular", "label": "Popular", "emoji": "⭐", "icon": "fa-star", "color": "#3b82f6", "count": 10, "is_active": false },
      { "key": "romantic", "label": "Romantic", "emoji": "💕", "icon": "fa-heart-circle-bolt", "color": "#fb7185", "count": 12, "is_active": false },
      { "key": "luxury", "label": "Luxury", "emoji": "💎", "icon": "fa-gem", "color": "#6366f1", "count": 14, "is_active": false },
      { "key": "effects", "label": "Effects/3D", "emoji": "⚡", "icon": "fa-bolt", "color": "#14b8a6", "count": 19, "is_active": false },
      { "key": "vip", "label": "VIP", "emoji": "🌟", "icon": "fa-award", "color": "#a855f7", "count": 12, "is_active": false }
    ],
    "total_gifts": 16,
    "gifts": [
      {
        "id": 1,
        "name": "Trophy Cup",
        "coins": 500,
        "coin_price": 500,
        "formatted_coins": "500",
        "category": "hot",
        "badge": "HOT",
        "image_url": "https://chinchins.live/uploads/gifts/trophy_cup.svg",
        "animation_full_url": "https://chinchins.live/uploads/gifts/trophy_cup.svg",
        "format": "svg",
        "display_type": "overlay",
        "sort_order": 1,
        "is_active": true,
        "is_broadcast": false
      },
      {
        "id": 2,
        "name": "Mystery Box",
        "coins": 888,
        "coin_price": 888,
        "formatted_coins": "888",
        "category": "hot",
        "badge": "MUST WIN",
        "image_url": "https://chinchins.live/uploads/gifts/mystery_box.svg",
        "animation_full_url": "https://chinchins.live/uploads/gifts/mystery_box.svg",
        "format": "svg",
        "display_type": "overlay",
        "sort_order": 2,
        "is_active": true,
        "is_broadcast": false
      }
    ]
  }
}
```

---

### 2️⃣ Send Gift (`POST /api/gifts/send` or `POST /api/gift/send`)
Sends a gift from sender to host/streamer in real-time. Deducts sender coins and adds earnings to receiver with Reverb broadcast.

- **URL:** `POST /api/gifts/send`
- **Headers:** `Authorization: Bearer <token>`
- **Body:**
```json
{
  "receiver_id": 15,
  "gift_id": 1,
  "quantity": 1,
  "context": "live_stream",
  "stream_id": "stream_15"
}
```

#### Success Response (200 OK):
```json
{
  "status": true,
  "message": "Gift sent successfully!",
  "remaining_balance": 44500,
  "gift": {
    "stream_id": "stream_15",
    "sender_id": 10,
    "sender_name": "Rahim",
    "sender_avatar": "https://chinchins.live/uploads/avatars/rahim.jpg",
    "gift_id": 1,
    "gift_name": "Trophy Cup",
    "icon_url": "https://chinchins.live/uploads/gifts/trophy_cup.svg",
    "file_url": "https://chinchins.live/uploads/gifts/trophy_cup.svg",
    "format": "svg",
    "display_type": "overlay",
    "quantity": 1,
    "coins_spent": 500
  }
}
```

---

### 3️⃣ Real-Time Broadcasting (Laravel Reverb / Pusher WebSocket)
When a gift is sent, a broadcast event is triggered immediately:

- **Channel:** `live-stream.{stream_id}`
- **Event:** `gift.received` or `LiveGiftSentEvent`
- **Payload:**
```json
{
  "stream_id": "stream_15",
  "sender_id": 10,
  "sender_name": "Rahim",
  "sender_avatar": "https://chinchins.live/uploads/avatars/rahim.jpg",
  "gift_id": 1,
  "gift_name": "Trophy Cup",
  "icon_url": "https://chinchins.live/uploads/gifts/trophy_cup.svg",
  "file_url": "https://chinchins.live/uploads/gifts/trophy_cup.svg",
  "format": "svg",
  "display_type": "overlay",
  "quantity": 1,
  "coins_spent": 500
}
```

---

## 📱 3. Flutter Integration Snippet (Gift Bottom Sheet with 12 Categories)

```dart
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

class GiftTrayBottomSheet extends StatefulWidget {
  final int hostUserId;
  final String streamId;
  final String token;

  const GiftTrayBottomSheet({
    Key? key,
    required this.hostUserId,
    required this.streamId,
    required this.token,
  }) : super(key: key);

  @override
  _GiftTrayBottomSheetState createState() => _GiftTrayBottomSheetState();
}

class _GiftTrayBottomSheetState extends State<GiftTrayBottomSheet> {
  String _selectedCategory = 'hot';
  List<dynamic> _categories = [];
  List<dynamic> _gifts = [];
  bool _isLoading = true;
  String _userBalance = '0';
  int? _selectedGiftId;

  @override
  void initState() {
    super.initState();
    _fetchGifts(_selectedCategory);
  }

  Future<void> _fetchGifts(String category) async {
    setState(() => _isLoading = true);
    final url = Uri.parse('https://chinchins.live/api/gifts?category=$category');
    final response = await http.get(url, headers: {
      'Authorization': 'Bearer ${widget.token}',
      'Accept': 'application/json',
    });

    if (response.statusCode == 200) {
      final res = jsonDecode(response.body);
      setState(() {
        _categories = res['data']['categories_list'] ?? [];
        _gifts = res['data']['gifts'] ?? [];
        _userBalance = res['data']['user_balance']['formatted_coins'] ?? '0';
        _selectedCategory = category;
        _isLoading = false;
      });
    } else {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _sendGift(int giftId) async {
    final url = Uri.parse('https://chinchins.live/api/gifts/send');
    final response = await http.post(
      url,
      headers: {
        'Authorization': 'Bearer ${widget.token}',
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode({
        'receiver_id': widget.hostUserId,
        'gift_id': giftId,
        'quantity': 1,
        'stream_id': widget.streamId,
      }),
    );

    if (response.statusCode == 200) {
      Navigator.pop(context);
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('🎁 Gift sent successfully!')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 480,
      decoration: const BoxDecoration(
        color: Color(0xFF131524),
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      child: Column(
        children: [
          // Header with User Balance & Recharge
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    const Icon(Icons.diamond, color: Color(0xFFF59E0B), size: 20),
                    const SizedBox(width: 6),
                    Text(
                      _userBalance,
                      style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 15),
                    ),
                    const SizedBox(width: 8),
                    const Icon(Icons.chevron_right, color: Colors.white54, size: 18),
                  ],
                ),
                TextButton(
                  onPressed: () {},
                  child: const Text('Recharge >', style: TextStyle(color: Color(0xFFF43F5E), fontWeight: FontWeight.bold)),
                ),
              ],
            ),
          ),

          // Categories Horizontal Tab Bar
          SizedBox(
            height: 40,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 12),
              itemCount: _categories.length,
              itemBuilder: (context, index) {
                final cat = _categories[index];
                final isSelected = cat['key'] == _selectedCategory;
                return Padding(
                  padding: const EdgeInsets.only(right: 8),
                  child: ChoiceChip(
                    label: Text('${cat['emoji']} ${cat['label']} (${cat['count']})'),
                    selected: isSelected,
                    selectedColor: const Color(0xFFF43F5E),
                    backgroundColor: const Color(0xFF1E2139),
                    labelStyle: TextStyle(
                      color: isSelected ? Colors.white : Colors.white70,
                      fontWeight: FontWeight.w600,
                      fontSize: 12,
                    ),
                    onSelected: (_) => _fetchGifts(cat['key']),
                  ),
                );
              },
            ),
          ),

          // Gifts Grid View
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator(color: Color(0xFFF43F5E)))
                : GridView.builder(
                    padding: const EdgeInsets.all(12),
                    gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: 4,
                      childAspectRatio: 0.78,
                      crossAxisSpacing: 8,
                      mainAxisSpacing: 8,
                    ),
                    itemCount: _gifts.length,
                    itemBuilder: (context, index) {
                      final gift = _gifts[index];
                      final isSelected = _selectedGiftId == gift['id'];
                      return GestureDetector(
                        onTap: () => setState(() => _selectedGiftId = gift['id']),
                        child: Container(
                          decoration: BoxDecoration(
                            color: isSelected ? const Color(0xFF2A1B3D) : const Color(0xFF1E2139),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(
                              color: isSelected ? const Color(0xFFF43F5E) : Colors.transparent,
                              width: 1.5,
                            ),
                          ),
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Image.network(
                                gift['image_url'],
                                height: 48,
                                width: 48,
                                errorBuilder: (_, __, ___) => const Icon(Icons.card_giftcard, color: Colors.pink, size: 40),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                gift['name'],
                                style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                              const SizedBox(height: 2),
                              Text(
                                '💎 ${gift['formatted_coins']}',
                                style: const TextStyle(color: Color(0xFFF59E0B), fontSize: 10, fontWeight: FontWeight.w600),
                              ),
                            ],
                          ),
                        ),
                      );
                    },
                  ),
          ),

          // Send Button
          if (_selectedGiftId != null)
            Padding(
              padding: const EdgeInsets.all(12),
              child: SizedBox(
                width: double.infinity,
                height: 44,
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFFF43F5E),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(22)),
                  ),
                  onPressed: () => _sendGift(_selectedGiftId!),
                  child: const Text('Send Gift 🎁', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Colors.white)),
                ),
              ),
            ),
        ],
      ),
    );
  }
}
```
