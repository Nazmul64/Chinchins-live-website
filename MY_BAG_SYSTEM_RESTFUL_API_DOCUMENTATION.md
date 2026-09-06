# 🎒 Chinchins Live — My Bag (আমার ব্যাগ) RESTful API Documentation

This document describes the complete RESTful API specification and Admin Panel integration for the **My Bag** backpack system on Chinchins Live.

---

## 📱 1. Overview & 6 Product Categories

The **My Bag** screen gives users a single inventory backpack to manage their owned items, coupons, and live stream dress-up effects.

| Category Slug | Display Name (English) | বাংলা নাম | Description |
| :--- | :--- | :--- | :--- |
| `coupon` | **Coupon** | কুপন | Discount coupons & Recharge bonus vouchers (Adds coins directly on use) |
| `avatar_frame` | **Avatar frame** | এভাটার ফ্রেম | Dynamic animated border frames around profile/live stream avatar |
| `chat_style` | **Chat style** | চ্যাট স্টাইল | Styled message bubble themes for live stream chat and private messaging |
| `profile_card` | **Profile card** | প্রোফাইল কার্ড | Premium background theme skins for user profile modal & stream view |
| `entrance_bubble` | **Entrance bubble** | এন্ট্রান্স বাবল | Floating announcement bubble badge when entering a live stream room |
| `big_entrance` | **Big entrance** | বিগ এন্ট্রান্স | Luxury car & full-screen vehicle animation effect upon entering a room |

### Item Statuses in User Inventory
- `unused`: Freshly purchased / gifted item ready to be equipped or redeemed.
- `used`: Active equipped visual theme or redeemed coupon.
- `expired`: Time-limited item whose `expires_at` date has passed.

---

## 🚀 2. RESTful API Endpoints

Base URL: `https://your-domain.com/api` (or `http://127.0.0.1:8000/api`)

### 1️⃣ Get User Backpack Inventory (`GET /api/bag`)
Returns user's owned items grouped by category and status.

- **URL:** `GET /api/bag` (Alias: `GET /api/my-bag`, `GET /api/bag/list`)
- **Headers:** `Authorization: Bearer <token>` (or pass `user_id` in query/body)
- **Query Parameters:**
  - `category` *(optional)*: Filter by `coupon`, `avatar_frame`, `chat_style`, `profile_card`, `entrance_bubble`, `big_entrance`. Defaults to `all`.
  - `status` *(optional)*: Filter by `unused`, `used`, `expired`, or `all`. Defaults to `all`.

#### Response Example:
```json
{
  "status": true,
  "message": "My Bag inventory fetched successfully",
  "active_category": "all",
  "active_status": "all",
  "categories": [
    { "slug": "coupon", "name": "Coupon", "name_bn": "কুপন", "icon": "fa-ticket-alt", "count": 2 },
    { "slug": "avatar_frame", "name": "Avatar frame", "name_bn": "এভাটার ফ্রেম", "icon": "fa-circle-notch", "count": 1 },
    { "slug": "chat_style", "name": "Chat style", "name_bn": "চ্যাট স্টাইল", "icon": "fa-comment-dots", "count": 1 },
    { "slug": "profile_card", "name": "Profile card", "name_bn": "প্রোফাইল কার্ড", "icon": "fa-id-card", "count": 1 },
    { "slug": "entrance_bubble", "name": "Entrance bubble", "name_bn": "এন্ট্রান্স বাবল", "icon": "fa-shield-alt", "count": 1 },
    { "slug": "big_entrance", "name": "Big entrance", "name_bn": "বিগ এন্ট্রান্স", "icon": "fa-car-side", "count": 1 }
  ],
  "equipped": {
    "avatar_frame": { "id": 2, "item_name": "Royal Amethyst Frame", "image_url": "https://domain.com/uploads/my_bag/frame_royal_amethyst.svg" },
    "chat_style": null,
    "profile_card": null,
    "entrance_bubble": null,
    "big_entrance": null
  },
  "counts": {
    "unused": 5,
    "used": 1,
    "expired": 0,
    "total": 6
  },
  "items": [
    {
      "id": 1,
      "user_id": 10,
      "item_id": 1,
      "item_name": "Mega Sale 500 Coin Voucher",
      "category": "coupon",
      "image_url": "https://domain.com/uploads/my_bag/coupon_sale_yellow.svg",
      "animation_url": null,
      "status": "unused",
      "is_equipped": false,
      "quantity": 1,
      "days_valid": 30,
      "is_permanent": false,
      "remaining_seconds": 2592000,
      "remaining_human": "30 days left",
      "is_expired": false,
      "created_at": "2026-09-06T12:00:00.000000Z"
    }
  ]
}
```

---

### 2️⃣ Get Store Catalog (`GET /api/bag/store`)
Returns available catalog items that users can buy or gift.

- **URL:** `GET /api/bag/store` (Alias: `GET /api/my-bag/store`, `GET /api/bag/catalog`)
- **Query Parameters:**
  - `category` *(optional)*: `coupon`, `avatar_frame`, `chat_style`, `profile_card`, `entrance_bubble`, `big_entrance`.

#### Response Example:
```json
{
  "status": true,
  "message": "Store catalog fetched successfully",
  "category": "all",
  "items": [
    {
      "id": 11,
      "name": "Supercar Cyber Phantom",
      "category": "big_entrance",
      "category_name": "Big entrance",
      "price_coins": 15000,
      "image_url": "https://domain.com/uploads/my_bag/big_entrance_sports_car.svg",
      "animation_url": null,
      "days_valid": 7,
      "is_permanent": false,
      "can_gift": true,
      "is_active": true
    }
  ]
}
```

---

### 3️⃣ Purchase Item from Store (`POST /api/bag/purchase`)
Deducts user coin balance and adds the item to their bag.

- **URL:** `POST /api/bag/purchase` (Alias: `POST /api/my-bag/buy`)
- **Headers:** `Authorization: Bearer <token>`
- **Request Body (JSON / Form Data):**
```json
{
  "item_id": 11,
  "quantity": 1
}
```

#### Response Example:
```json
{
  "status": true,
  "message": "Successfully purchased Supercar Cyber Phantom! Added to your bag.",
  "new_balance": 85000,
  "user_bag_item": {
    "id": 15,
    "user_id": 10,
    "item_id": 11,
    "status": "unused",
    "is_equipped": false,
    "quantity": 1,
    "expires_at": "2026-09-13T12:00:00.000000Z"
  }
}
```

---

### 4️⃣ Use or Equip Item (`POST /api/bag/use`)
- **Coupons:** Redeems voucher and credits the bonus coins to user balance immediately (status changes to `used`).
- **Frames/Chat/Profile/Bubbles/Big Entrance:** Equips the item (`is_equipped = true`, `status = used`) and automatically unequips any previously worn item in that category.

- **URL:** `POST /api/bag/use` (Alias: `POST /api/my-bag/equip`)
- **Headers:** `Authorization: Bearer <token>`
- **Request Body:**
```json
{
  "bag_item_id": 15
}
```

#### Response Example (Equipping Visual Frame):
```json
{
  "status": true,
  "message": "Supercar Cyber Phantom is now equipped!",
  "action": "equipped",
  "category": "big_entrance",
  "user_bag_item": {
    "id": 15,
    "status": "used",
    "is_equipped": true
  }
}
```

---

### 5️⃣ Unequip Item (`POST /api/bag/unequip`)
Takes off an equipped visual item.

- **URL:** `POST /api/bag/unequip`
- **Headers:** `Authorization: Bearer <token>`
- **Request Body:**
```json
{
  "category": "avatar_frame"
}
```

#### Response Example:
```json
{
  "status": true,
  "message": "Unequipped item for category avatar_frame",
  "category": "avatar_frame"
}
```

---

### 6️⃣ Send Item as Gift (`POST /api/bag/gift`)
Gift an item to another user (either by buying directly for them or sending from inventory).

- **URL:** `POST /api/bag/gift` (Alias: `POST /api/my-bag/send-gift`)
- **Headers:** `Authorization: Bearer <token>`
- **Request Body:**
```json
{
  "item_id": 11,
  "receiver_id": 25,
  "quantity": 1
}
```

#### Response Example:
```json
{
  "status": true,
  "message": "Successfully sent Supercar Cyber Phantom to Aisha Rahman!",
  "new_balance": 70000,
  "receiver": {
    "id": 25,
    "name": "Aisha Rahman",
    "account_id": "88234901"
  }
}
```

---

## 🖥️ 3. Admin Panel Management

Administrators can manage all items and user allocations via the web admin panel:

- **Catalog Manager:** `GET /admin/my-bag`
  - Create new items with SVG / PNG / GIF / WebP upload.
  - Set category (`Coupon`, `Avatar frame`, `Chat style`, `Profile card`, `Entrance bubble`, `Big entrance`).
  - Set validity days (or Permanent), price in coins, and coupon reward value.
  - Toggle item availability (Active / Inactive).
  - Edit or delete items.
- **Give Items to Users:** `POST /admin/my-bag/give-user`
  - Instantly grant any bag item to any user by selecting User and Quantity.
- **User Inventory Ledger:** `GET /admin/my-bag/inventory`
  - Search and filter all allocated user backpack items by username, account ID, category, or status.
