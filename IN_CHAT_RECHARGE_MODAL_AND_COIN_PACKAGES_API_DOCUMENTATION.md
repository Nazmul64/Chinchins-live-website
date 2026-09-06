# 💎 In-Chat & In-Call Recharge Modal and Coin Packages — RESTful API & Flutter Integration Guide

This guide documents the complete end-to-end architecture, RESTful API endpoints, and Flutter mobile app integration for the **Insufficient Balance Recharge Bottom Sheet Modal** (Chat & Call Flow) matching Chinchins Live design specifications.

---

## 📱 1. User Journey & Modal Architecture

When a user attempts to:
1. **Send a Chat Message** (`POST /api/messages/send` or `POST /api/chat/send`) after exhausting their free messages, OR
2. **Start an Audio/Video Call** (`POST /api/call/start`) without sufficient coin balance,

The server returns an `INSUFFICIENT_BALANCE` / `MESSAGE_LIMIT_REACHED` response and the Flutter app presents the **Recharge Bottom Sheet Modal**.

```
┌─────────────────────────────────────────────────────────────┐
│ 👤 (Avatar) ✨ Chat all you want & connect face-to-face      │
│             — upgrade for more fun!                     [✕] │
├─────────────────────────────────────────────────────────────┤
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐       │
│  │ 50%off  ONCE │  │ 17%off       │  │ 17%off       │       │
│  │    💎 (1)    │  │   💎💎 (2)   │  │  💎💎💎 (3)  │       │
│  │     7560     │  │     8100     │  │    16380     │       │
│  │  BDT 150.00  │  │  BDT 300.00  │  │  BDT 600.00  │       │
│  └──────────────┘  └──────────────┘  └──────────────┘       │
│                                                             │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐       │
│  │ 30%off       │  │ 60%off       │  │ 80%off       │       │
│  │  💎💎💎💎 (4)│  │ 🔮 Tray (5)  │  │ 👑 Chest (6) │       │
│  │    32940     │  │    66600     │  │    167400    │       │
│  │ BDT 1,200.00 │  │ BDT 2,400.00 │  │ BDT 6,100.00 │       │
│  └──────────────┘  └──────────────┘  └──────────────┘       │
├─────────────────────────────────────────────────────────────┤
│                    💎 My Gems: 60                           │
│              [      Continue (Recharge)      ]              │
└─────────────────────────────────────────────────────────────┘
```

---

## 🚀 2. RESTful API Endpoints

### 1️⃣ Get In-Chat/In-Call Recharge Modal Data
- **URL:** `GET /api/recharge/modal-data`
- **Aliases:** `GET /api/coin-packages/recharge-modal`, `GET /api/coin-packages/insufficient-balance-modal`
- **Headers:** `Authorization: Bearer <token>` (or query `user_id`)
- **Query Parameters:**
  - `receiver_id` *(optional)*: ID or Account ID of the host/user being messaged/called (to show their avatar & name in the header).
  - `action` *(optional)*: `chat` or `call` (defaults to `chat`).

#### Response Example:
```json
{
  "status": true,
  "message": "Recharge modal data retrieved successfully.",
  "modal": {
    "header_title": "✨ Chat all you want & connect face-to-face — upgrade for more fun!",
    "action_type": "chat",
    "receiver": {
      "id": 12,
      "account_id": "88410293",
      "name": "Shirin Akter",
      "avatar_url": "https://chinchinslive.com/uploads/avatars/user_12.jpg",
      "is_online": true,
      "is_busy": false
    },
    "user_gems": 60,
    "formatted_user_gems": "60",
    "currency_symbol": "💎",
    "default_selected_package_id": 1,
    "button_text": "Continue",
    "packages": [
      {
        "id": 1,
        "title": "Starter Pack",
        "coins": 7560,
        "bonus_coins": 0,
        "total_coins": 7560,
        "formatted_coins": "7,560",
        "price": 150,
        "formatted_price": "BDT 150.00",
        "price_bdt": 150,
        "badge": "50% off",
        "badge_color": "danger",
        "icon_url": "uploads/coin_packages/gem_tier1_single.svg",
        "icon_full_url": "https://chinchinslive.com/uploads/coin_packages/gem_tier1_single.svg",
        "format": "image",
        "is_popular": true,
        "is_once_offer": true
      },
      {
        "id": 2,
        "title": "Basic Pack",
        "coins": 8100,
        "bonus_coins": 0,
        "total_coins": 8100,
        "formatted_coins": "8,100",
        "price": 300,
        "formatted_price": "BDT 300.00",
        "price_bdt": 300,
        "badge": "17% off",
        "badge_color": "pink",
        "icon_url": "uploads/coin_packages/gem_tier2_double.svg",
        "icon_full_url": "https://chinchinslive.com/uploads/coin_packages/gem_tier2_double.svg",
        "format": "image",
        "is_popular": false,
        "is_once_offer": false
      },
      {
        "id": 3,
        "title": "Popular Pack",
        "coins": 16380,
        "bonus_coins": 0,
        "total_coins": 16380,
        "formatted_coins": "16,380",
        "price": 600,
        "formatted_price": "BDT 600.00",
        "price_bdt": 600,
        "badge": "17% off",
        "badge_color": "pink",
        "icon_url": "uploads/coin_packages/gem_tier3_triple.svg",
        "icon_full_url": "https://chinchinslive.com/uploads/coin_packages/gem_tier3_triple.svg",
        "format": "image",
        "is_popular": false,
        "is_once_offer": false
      },
      {
        "id": 4,
        "title": "Super Pack",
        "coins": 32940,
        "bonus_coins": 0,
        "total_coins": 32940,
        "formatted_coins": "32,940",
        "price": 1200,
        "formatted_price": "BDT 1,200.00",
        "price_bdt": 1200,
        "badge": "30% off",
        "badge_color": "pink",
        "icon_url": "uploads/coin_packages/gem_tier4_stack.svg",
        "icon_full_url": "https://chinchinslive.com/uploads/coin_packages/gem_tier4_stack.svg",
        "format": "image",
        "is_popular": false,
        "is_once_offer": false
      },
      {
        "id": 5,
        "title": "Mega Pack",
        "coins": 66600,
        "bonus_coins": 0,
        "total_coins": 66600,
        "formatted_coins": "66,600",
        "price": 2400,
        "formatted_price": "BDT 2,400.00",
        "price_bdt": 2400,
        "badge": "60% off",
        "badge_color": "pink",
        "icon_url": "uploads/coin_packages/gem_tier5_tray.svg",
        "icon_full_url": "https://chinchinslive.com/uploads/coin_packages/gem_tier5_tray.svg",
        "format": "image",
        "is_popular": false,
        "is_once_offer": false
      },
      {
        "id": 6,
        "title": "VIP King Pack",
        "coins": 167400,
        "bonus_coins": 0,
        "total_coins": 167400,
        "formatted_coins": "167,400",
        "price": 6100,
        "formatted_price": "BDT 6,100.00",
        "price_bdt": 6100,
        "badge": "80% off",
        "badge_color": "danger",
        "icon_url": "uploads/coin_packages/gem_tier6_chest.svg",
        "icon_full_url": "https://chinchinslive.com/uploads/coin_packages/gem_tier6_chest.svg",
        "format": "image",
        "is_popular": false,
        "is_once_offer": false
      }
    ]
  }
}
```

---

### 2️⃣ Send Message with Insufficient Balance (`POST /api/messages/send`)
When balance is exhausted, the API immediately returns `HTTP 402 Payment Required`:

```json
{
  "status": false,
  "code": "MESSAGE_LIMIT_REACHED",
  "message": "You have reached your free limit of 5 messages. Please recharge coins to continue chatting.",
  "is_limit_reached": true,
  "redirect_to_deposit": true,
  "current_coins": 60,
  "required_coins": 100,
  "free_messages_used": 5,
  "free_messages_limit": 5,
  "coin_packages": [ ... ],
  "payment_methods": [ ... ]
}
```

---

## 🎨 3. Admin Panel Management (`/admin/coin-packages`)

1. **Preset Artwork Selector:**
   - 1 Gem (`gem_tier1_single.svg`)
   - 2 Gems (`gem_tier2_double.svg`)
   - 3 Gems (`gem_tier3_triple.svg`)
   - 4 Gems (`gem_tier4_stack.svg`)
   - Velvet Tray of Gems (`gem_tier5_tray.svg`)
   - Luxury Chest of Gems (`gem_tier6_chest.svg`)
2. **Discount Badge Chips:** Quick click `50% off`, `17% off`, `30% off`, `60% off`, `80% off`, `🔥 ONCE`.
3. **Live App Card Preview:** Real-time preview card in modal reflecting exact in-app styling, orange highlight gradient for First Tier / ONCE, BDT pricing, and button.

---

## 📱 4. Flutter Integration Code Snippet

```dart
// Function to show the Bottom Sheet Recharge Modal
void showRechargeBottomSheet(BuildContext context, {required int receiverId, String? avatarUrl, String? name}) async {
  // 1. Fetch modal data
  final response = await dio.get('/api/recharge/modal-data', queryParameters: {'receiver_id': receiverId});
  if (response.data['status'] == true) {
    final modalData = response.data['modal'];
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => RechargeModalWidget(data: modalData),
    );
  }
}
```
