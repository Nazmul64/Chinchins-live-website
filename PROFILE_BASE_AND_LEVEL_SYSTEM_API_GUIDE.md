# 🎖️ Chinchins Live — Profile Base (Badge / Avatar Frame) & Host Level System RESTful API Guide

> **Version**: 1.0.0  
> **Target Audience**: Mobile App Developers (Flutter / Android / iOS) & Backend Administrators  
> **Status**: Production Ready & Fully Tested  

---

## 📌 1. System Overview & Earning Mechanics

In Chinchins Live, **Profile Bases (Avatar Frames & Badges)** visually wrap around a user or host's profile picture throughout the app (in 1-on-1 audio/video calls, live stream rooms, home feeds, top fans leaderboards, profile pages, and chat).

### 🪙 Host Earning & Level Up Logic (50/50 Revenue Split)
- **Call Session Deduction**:
  - Example: A male customer calls a female host at **100 coins/minute**.
  - **50% (50 coins)** goes to **Admin Revenue**.
  - **50% (50 coins)** goes to the **Female Host** as earned coins.
- **Automatic Progression**:
  - Every coin earned by the host accumulates into their lifetime `total_earned_coins`.
  - As the host crosses coin milestones configured in the Admin Panel (e.g. 1,000 coins for Level 1, 5,000 coins for Level 2, up to Level 10+), their level increments automatically.
  - The unlocked Level Base Frame is automatically applied around the host's profile photo in all app screens.

---

## 🎨 2. Mobile App UI Layering Guide (Avatar + Frame Stacking)

The profile avatar frame is designed as a square (1:1 ratio) transparent SVG / PNG image with a circular opening in the center.

### 📱 Flutter Implementation Example:
```dart
Widget buildAvatarWithFrame({
  required String avatarUrl,
  required String? frameUrl,
  required int level,
  required String badgeColor,
  double size = 96.0, // Avatar diameter
}) {
  final double frameSize = size * 1.45; // Frame is ~1.45x larger to wrap around

  return Stack(
    alignment: Alignment.center,
    clipBehavior: Clip.none,
    children: [
      // 1. Circular User Avatar
      ClipOval(
        child: Image.network(
          avatarUrl,
          width: size,
          height: size,
          fit: BoxFit.cover,
          errorBuilder: (_, __, ___) => Container(
            width: size,
            height: size,
            color: Colors.grey[800],
            child: Icon(Icons.person, color: Colors.white70, size: size * 0.6),
          ),
        ),
      ),

      // 2. Overlaid Animated / Static Base Frame
      if (frameUrl != null && frameUrl.isNotEmpty)
        Positioned(
          child: IgnorePointer(
            child: frameUrl.endsWith('.svg')
                ? SvgPicture.network(
                    frameUrl,
                    width: frameSize,
                    height: frameSize,
                    fit: BoxFit.contain,
                  )
                : Image.network(
                    frameUrl,
                    width: frameSize,
                    height: frameSize,
                    fit: BoxFit.contain,
                  ),
          ),
        ),

      // 3. Level Badge Pill (Bottom Center)
      Positioned(
        bottom: -4,
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
          decoration: BoxDecoration(
            color: HexColor.fromHex(badgeColor),
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: Colors.white, width: 1.5),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.35),
                blurRadius: 4,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          child: Text(
            'Lv.$level',
            style: const TextStyle(
              color: Colors.white,
              fontWeight: FontWeight.w800,
              fontSize: 10,
            ),
          ),
        ),
      ),
    ],
  );
}
```

---

## ⚡ 3. Automatic Appends on Every User Object

Every API endpoint that returns user objects (e.g. `/api/home`, `/api/users`, `/api/profile/{id}`, `/api/profile/me`, `/api/auth/me`, `/api/call/session/*`) **automatically includes** the following attributes:

| Field Name | Type | Description |
| :--- | :--- | :--- |
| `avatar_frame_url` | `string \| null` | Full absolute URL to the user's active Level Base Frame image. |
| `base_frame_url` | `string \| null` | Alias for `avatar_frame_url`. |
| `total_earned_coins` | `integer` | Lifetime coins earned by the user (from calls & gifts). |
| `current_level` | `integer` | User's active level number (e.g., `0`, `1`, `2` ... `10`). |
| `badge_color` | `string` | Hex color code for the level badge (e.g., `#f59e0b`). |
| `badge_icon` | `string` | Icon identifier (`crown`, `gem`, `star`, `fire`, `bolt`, `shield`). |
| `level_info` | `object` | Comprehensive progression stats (coins needed, percentage, next level). |

---

## 🌐 4. RESTful API Endpoints

### 1️⃣ Get All Level Bases & Avatar Frames
Retrieve the master list of all active levels, coin thresholds, and frame assets.

- **Endpoint**: `GET /api/profile-bases`
- **Aliases**: `GET /api/levels`, `GET /api/profile-frames`, `GET /api/profile-bases/list`
- **Authentication**: Optional (Public)

#### Sample Response:
```json
{
  "status": true,
  "message": "Level bases and avatar frames fetched successfully.",
  "total": 11,
  "data": [
    {
      "id": 1,
      "level": 0,
      "name": "Level 0 - Novice Cadet",
      "required_coins": 0,
      "frame_image_url": "http://localhost/uploads/bases/profile_base_royal_gold.svg",
      "base_frame_image": "uploads/bases/profile_base_royal_gold.svg",
      "badge_icon": "user",
      "badge_color": "#94a3b8",
      "glow_color": "rgba(148, 163, 184, 0.3)",
      "privilege_text": "Standard Profile Frame",
      "is_active": true
    },
    {
      "id": 2,
      "level": 1,
      "name": "Level 1 - Bronze Star",
      "required_coins": 1000,
      "frame_image_url": "http://localhost/uploads/bases/profile_base_royal_gold.svg",
      "base_frame_image": "uploads/bases/profile_base_royal_gold.svg",
      "badge_icon": "star",
      "badge_color": "#10b981",
      "glow_color": "rgba(16, 185, 129, 0.45)",
      "privilege_text": "Unlocks Bronze Star Animated Avatar Frame",
      "is_active": true
    },
    {
      "id": 3,
      "level": 2,
      "name": "Level 2 - Silver Wings",
      "required_coins": 5000,
      "frame_image_url": "http://localhost/uploads/bases/profile_base_diamond_wings.svg",
      "base_frame_image": "uploads/bases/profile_base_diamond_wings.svg",
      "badge_icon": "star",
      "badge_color": "#06b6d4",
      "glow_color": "rgba(6, 182, 212, 0.45)",
      "privilege_text": "Unlocks Silver Wings Animated Avatar Frame",
      "is_active": true
    },
    {
      "id": 11,
      "level": 10,
      "name": "Level 10 - Mythic Emperor",
      "required_coins": 5000000,
      "frame_image_url": "http://localhost/uploads/bases/profile_base_svip_crown.svg",
      "base_frame_image": "uploads/bases/profile_base_svip_crown.svg",
      "badge_icon": "crown",
      "badge_color": "#f43f5e",
      "glow_color": "rgba(244, 63, 94, 0.8)",
      "privilege_text": "Supreme Mythic Emperor God-Tier Base Frame & Global Shout",
      "is_active": true
    }
  ]
}
```

---

### 2️⃣ Get User Level Status & Real-Time Progression
Retrieve a user's current level, earned coins, progress percentage towards the next level, and unlockable perks.

- **Endpoint**: `GET /api/user/level-status`
- **Aliases**: `GET /api/profile/level-status`, `GET /api/level/status`
- **Authentication**: Bearer Token OR pass `user_id` / `account_id` as query param / header.

#### Request Examples:
- **Authenticated with Bearer Token**:
  ```http
  GET /api/user/level-status
  Authorization: Bearer 34|AbCdEf...
  ```
- **Public Query by Account ID / User ID**:
  ```http
  GET /api/user/level-status?account_id=84920183
  ```

#### Sample Response:
```json
{
  "status": true,
  "message": "User level progression status retrieved successfully.",
  "data": {
    "user": {
      "id": 14,
      "account_id": "84920183",
      "name": "Ayesha Rahman",
      "nickname": "Ayesha",
      "avatar_url": "http://localhost/uploads/profiles/host_14.jpg",
      "gender": "female",
      "total_earned_coins": 18500
    },
    "progression": {
      "current_level": 3,
      "level_name": "Level 3 - Golden Sparkle",
      "total_earned_coins": 18500,
      "coins_for_current_level": 15000,
      "next_level": 4,
      "coins_for_next_level": 50000,
      "coins_needed_to_level_up": 31500,
      "progress_percentage": 10.0,
      "is_max_level": false,
      "avatar_frame_url": "http://localhost/uploads/bases/profile_base_royal_gold.svg",
      "badge_color": "#f59e0b",
      "badge_icon": "gem",
      "glow_color": "rgba(245, 158, 11, 0.5)",
      "privilege_text": "Unlocks Golden Sparkle Avatar Frame & Profile Glow"
    },
    "levels_scale": [
      {
        "level": 0,
        "name": "Level 0 - Novice Cadet",
        "required_coins": 0,
        "frame_image_url": "http://localhost/uploads/bases/profile_base_royal_gold.svg",
        "badge_icon": "user",
        "badge_color": "#94a3b8",
        "privilege_text": "Standard Profile Frame",
        "is_unlocked": true,
        "is_current": false
      },
      {
        "level": 1,
        "name": "Level 1 - Bronze Star",
        "required_coins": 1000,
        "frame_image_url": "http://localhost/uploads/bases/profile_base_royal_gold.svg",
        "badge_icon": "star",
        "badge_color": "#10b981",
        "privilege_text": "Unlocks Bronze Star Animated Avatar Frame",
        "is_unlocked": true,
        "is_current": false
      },
      {
        "level": 2,
        "name": "Level 2 - Silver Wings",
        "required_coins": 5000,
        "frame_image_url": "http://localhost/uploads/bases/profile_base_diamond_wings.svg",
        "badge_icon": "star",
        "badge_color": "#06b6d4",
        "privilege_text": "Unlocks Silver Wings Animated Avatar Frame",
        "is_unlocked": true,
        "is_current": false
      },
      {
        "level": 3,
        "name": "Level 3 - Golden Sparkle",
        "required_coins": 15000,
        "frame_image_url": "http://localhost/uploads/bases/profile_base_royal_gold.svg",
        "badge_icon": "gem",
        "badge_color": "#f59e0b",
        "privilege_text": "Unlocks Golden Sparkle Avatar Frame & Profile Glow",
        "is_unlocked": true,
        "is_current": true
      },
      {
        "level": 4,
        "name": "Level 4 - Cyber Neon",
        "required_coins": 50000,
        "frame_image_url": "http://localhost/uploads/bases/profile_base_cyber_neon.svg",
        "badge_icon": "bolt",
        "badge_color": "#00f0ff",
        "privilege_text": "Unlocks Cyber Neon Animated Avatar Frame & Blue Beam",
        "is_unlocked": false,
        "is_current": false
      }
    ]
  }
}
```

---

## 🛠️ 5. Admin Panel Interface

The admin panel provides complete management at `/admin/profile-bases`:
1. **Interactive Live Avatar Preview**:
   - Allows admin to select any level and see the frame wrapped around sample avatars with glow and badge colors.
2. **10-Level Batch Editor Table**:
   - Admin can edit all 10+ levels simultaneously (Required Coins, Level Name, Badge Icon, Badge Color, Frame Preset, Privilege Description, Active Status).
   - Single "Save All Level Changes" button commits changes instantly.
3. **Custom Frame Upload**:
   - Admin can upload custom SVG / PNG frames for any specific level.
4. **Create Custom Level Modal**:
   - Allows creating extra tiers (Level 11, Level 12, etc.).

---

## 🏁 6. Summary Checklist for Frontend / App Developer

- [x] Use `user.avatar_frame_url` or `user.base_frame_url` to render the frame above the avatar in a `Stack`.
- [x] Use `user.current_level` and `user.badge_color` to show the level tag.
- [x] Call `GET /api/user/level-status` on the user's Profile / Host Earnings screen to show the progress bar and how many coins remain until the next level.
