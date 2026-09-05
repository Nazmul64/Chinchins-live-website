# 🏆 Chinchins Live — MASTER RESTful API & Mobile Client Integration Specification
### Complete Backend API Engine for Flutter / Android / iOS
**Covers: 100+ Live Streaming Animated Gifts & Emojis, 8-Tier VIP Cards, Level Bases & Frames, WebRTC 1-on-1 Video Calls, 50/50 Revenue Split, and In-Call Recharge Engine**

---

## 📑 Table of Contents
1. [System Architecture & Core Business Rules](#-1-system-architecture--core-business-rules)
2. [100+ Animated Live Gifts, Emojis & Chat Stickers](#-2-100-animated-live-gifts-emojis--chat-stickers)
3. [8-Tier Premium VIP Privilege Cards & Daily Check-in Engine](#-3-8-tier-premium-vip-privilege-cards--daily-check-in-engine)
4. [Customer Profile Base (Avatar Frames) & Host Level System](#-4-customer-profile-base-avatar-frames--host-level-system)
5. [Coin Packages & Gems Store Engine](#-5-coin-packages--gems-store-engine)
6. [WebRTC 1-on-1 Video Calling & 50/50 Revenue Split](#-6-webrtc-1-on-1-video-calling--5050-revenue-split)
7. [Complete RESTful API Endpoints Reference](#-7-complete-restful-api-endpoints-reference)
8. [Mobile Client UI & Animation Implementation (Flutter Code Snippets)](#-8-mobile-client-ui--animation-implementation-flutter-code-snippets)

---

## 🌟 1. System Architecture & Core Business Rules

Chinchins Live is an interactive live streaming and 1-on-1 video chat platform designed for high-concurrency engagement, creator monetization, and social interaction.

### 🪙 1.1 Host Earning & Coin Revenue Split (50/50 Rule)
- When a customer makes a call or sends a gift, the cost is deducted in **Coins/Gems**.
- **1-on-1 Audio/Video Calling**: Standard rate is **100 coins/minute** (~1.67 coins/sec).
  - **50% (50 coins)** is automatically credited to the female host's wallet earnings (`host_earned_coins`).
  - **50% (50 coins)** is credited as platform administrator revenue (`admin_revenue_coins`).
- **Free Hosts (এডমিন প্যানেল থেকে ফ্রি করে দেওয়া হোস্ট/মেয়েরা)**:
  - Admin can designate specific female users/hosts as **Free Host** (`is_free_caller = true`).
  - A Free Host can initiate calls with **0 coin balance**.
- **16-Second Free Trial Preview**:
  - The first **16 seconds** of answered video calls are free for trial discovery.
  - After 16 seconds, if customer has `< 100 coins`, the remote video stream is automatically **blurred** and the **Recharge Modal Sheet** pops up without disconnecting the session.

---

## 🎁 2. 100+ Animated Live Gifts, Emojis & Chat Stickers

The platform provides **100+ high-definition 2D & 3D animated gifts** stored in `public/uploads/gifts/` categorized into 8 dynamic groups:

| Category | Item Highlights | Price Range | Animation Types |
| :--- | :--- | :--- | :--- |
| **Popular** | Rose Bouquet, Romantic Kiss, Heart Fireworks, Teddy Bear, Birthday Cake, Champagne Pop, Golden Lotus, Sakura Rain | 50 – 1,999 Coins | `flying_petals`, `particle_hearts`, `fullscreen_fireworks`, `bounce_3d` |
| **Luxury** | Sports Bike, Luxury Supercar, 24K Diamond Ring, VIP Helicopter, Private Jet, Mega Yacht, Diamond Castle, Limousine | 3,333 – 150,000 Coins | `speed_drive`, `drive_in_drift`, `flying_helicopter`, `ocean_cruise`, `fullscreen_castle` |
| **Desi / Traditional** | Traditional Rickshaw, VIP Golden Rickshaw, Krishna Banshi (Flute), Mishti Dokan (Sweet Shop), Tong Dokan Tea, Bengal Tiger | 150 – 35,000 Coins | `rickshaw_ride`, `flute_melody`, `dokan_sweets_rain`, `tiger_roar_screen` |
| **Birds & Flying** | White Dove, Royal Golden Eagle, Lovebirds Nest, Hummingbird, Macaw Parrot, Swan Lake Romance, Peacock Feathers | 199 – 12,000 Coins | `flying_dove`, `eagle_soar`, `lovebirds_kiss`, `peacock_spread` |
| **Romantic** | Love Mailbox, Candlelight Dinner, Sunset Beach Walk, Midnight Lovers Moon, Vintage Carriage, Aurora Kiss | 520 – 21,000 Coins | `flying_envelope`, `romantic_glow`, `cinematic_sunset`, `celestial_moon` |
| **Effects & 3D** | Space Rocket, Magic Genie Lamp, Flaming Fire Dragon, Flying Phoenix, Galaxy Portal, Space Battleship, Thor Hammer | 7,777 – 75,000 Coins | `rocket_blastoff`, `dragon_roar_fire`, `phoenix_flight`, `cosmic_portal_warp` |
| **VIP Sovereign** | Fairy Tiara, Royal Sovereign Crown, Mythic Treasure Chest, Dragon Throne, Black Card, Cosmic Gauntlet | 1,999 – 500,000 Coins | `crown_descend_gold`, `chest_burst_gems`, `throne_ascend`, `snap_universe` |
| **Emojis & Stickers** | LOL Tears, Love Eyes, Flame Lit, Money Bag, Mind Blown, Party Popper, 100 Points, Golden Thumbs Up | 10 – 200 Coins | `sticker_bounce`, `sticker_heart_pop`, `sticker_cash_rain`, `sticker_stamp` |

### 🛰️ Live Stream Gift Broadcasting:
When a luxury, effects, or VIP gift is sent, the server broadcasts a `LiveGiftSentEvent` across Pusher / Laravel Reverb to all viewers in the room triggering a full-screen particle overlay, audio sound effect, and room-wide banner notification.

---

## 👑 3. 8-Tier Premium VIP Privilege Cards & Daily Check-in Engine

The VIP system offers 8 tiered subscription cards with daily login reward claims and exclusive profile outfits:

| Tier | Card Name | Duration | Price (BDT) | Instant Gems | Daily Check-in Total | Total Return | Unlocked Benefits |
| :---: | :--- | :---: | :---: | :---: | :---: | :---: | :--- |
| **1** | **Trial 3-Day VIP Card** | 3 Days | ৳ 150 | 3,000 | 1,500 | **4,500 Gems** | 3D Star Avatar Frame, Chat Glow |
| **2** | **Super Weekly Card** | 7 Days | ৳ 450 | 12,150 | 2,540 | **14,690 Gems** | Emerald Avatar Frame, VIP Badge, 7x Lucky Cards |
| **3** | **Super Monthly Card** | 30 Days | ৳ 1,200 | 32,940 | 26,330 | **59,270 Gems** | 24K Gold Frame, Luxury Chat Bubble, 15x Lucky Cards |
| **4** | **Luxury Monthly Card** | 30 Days | ৳ 2,400 | 66,600 | 87,110 | **153,710 Gems** | 3D Diamond Frame, VIP Crown, 30x Lucky Cards |
| **5** | **SVIP Quarterly 90-Day** | 90 Days | ৳ 6,500 | 200,000 | 250,000 | **450,000 Gems** | Fire Dragon Frame, Supersonic Jet Entry, 100x Lucky Cards |
| **6** | **Royal Semi-Annual (180D)**| 180 Days| ৳ 12,000 | 450,000 | 550,000 | **1,000,000 Gems**| 24K Sovereign Crown Frame, Golden Nickname, 250x Cards |
| **7** | **Galactic Annual (365D)** | 365 Days| ৳ 22,000 | 1,000,000| 1,250,000 | **2,250,000 Gems**| Cyber Neon Ultra Frame, Space Battleship Entry, 500x Cards |
| **8** | **Black Diamond Sovereign** | 365 Days| ৳ 50,000 | 3,000,000| 3,000,000 | **6,000,000 Gems**| Mythic Emperor God-Tier Frame, Unlimited Global Broadcasts |

---

## 🎖️ 4. Customer Profile Base (Avatar Frames) & Host Level System

Profile Bases automatically wrap around the user's profile picture everywhere in the app based on their lifetime earned coins:

```
[ LEVEL PROGRESSION SCALE ]
Level 0: Novice Cadet       -->       0 Coins (Standard Frame)
Level 1: Bronze Star        -->   1,000 Coins (Bronze Star Frame)
Level 2: Silver Wings       -->   5,000 Coins (Silver Wings Frame)
Level 3: Golden Sparkle     -->  15,000 Coins (Golden Sparkle Frame)
Level 4: Cyber Neon         -->  50,000 Coins (Cyber Neon Future Frame)
Level 5: Fire Dragon        --> 100,000 Coins (Flaming Fire Dragon Frame)
Level 6: Diamond Wings      --> 250,000 Coins (Diamond Luxury Frame)
Level 7: Royal Gold         --> 500,000 Coins (24K Sovereign Crown Frame)
Level 8: SVIP Supreme       --> 1,000,000 Coins (SVIP Supreme Animated Crown)
Level 9: Galactic Sovereign --> 2,500,000 Coins (Galactic Ultra Frame)
Level 10: Mythic Emperor    --> 5,000,000 Coins (Supreme God-Tier Emperor Frame)
```

Every user object automatically appends `avatar_frame_url`, `current_level`, `badge_color`, `total_earned_coins`, and `level_info`.

---

## 💎 5. Coin Packages & Gems Store Engine

Coin packages allow instant recharging through bKash, Nagad, Rocket, Upay, Cards, and SSLCommerz:

| Package Name | Base Gems | Bonus Gems | Total Gems | Price (BDT) | Promo Tag |
| :--- | :---: | :---: | :---: | :---: | :---: |
| **Starter Pack** | 7,560 | 0 | 7,560 | ৳ 150.00 | `50% OFF` |
| **Basic Pack** | 8,100 | 0 | 8,100 | ৳ 300.00 | `17% OFF` |
| **Popular Pack** | 16,380 | 1,000 | 17,380 | ৳ 600.00 | `POPULAR` |
| **Super Pack** | 32,940 | 3,000 | 35,940 | ৳ 1,200.00 | `30% OFF` |
| **Mega Pack** | 66,600 | 8,000 | 74,600 | ৳ 2,400.00 | `60% OFF` |
| **VIP King Pack** | 167,400 | 25,000 | 192,400 | ৳ 6,100.00 | `80% OFF` |
| **Whale Sovereign** | 500,000 | 100,000 | 600,000 | ৳ 18,000.00 | `KING DEAL` |

---

## 📡 7. Complete RESTful API Endpoints Reference

### 🌐 Base URL: `https://chinchins.live/api` (or `http://localhost:8000/api`)

---

### 🎁 7.1 Gifts & In-App Rewards Endpoints

#### 1. Get All Live Streaming Gifts Catalog
- **Endpoint**: `GET /api/gifts/catalog` (or `GET /api/gifts/categories`)
- **Query Params**: `category` (`all`, `popular`, `luxury`, `romantic`, `effects`, `vip`, `desi`, `emojis`, `stickers`)
- **Sample Response**:
  ```json
  {
    "status": true,
    "message": "Gifts catalog loaded successfully.",
    "data": {
      "user_balance": { "coins": 25000, "formatted_coins": "25.00K" },
      "categories": {
        "all": 105,
        "popular": 15,
        "luxury": 13,
        "romantic": 11,
        "effects": 15,
        "vip": 12,
        "desi": 12,
        "emojis": 13,
        "stickers": 14
      },
      "total_gifts": 105,
      "gifts": [
        {
          "id": 1,
          "name": "Traditional Rickshaw",
          "coins": 999,
          "category": "desi",
          "image_url": "https://chinchins.live/uploads/gifts/traditional_rickshaw.svg",
          "animation_full_url": "https://chinchins.live/uploads/gifts/traditional_rickshaw.svg",
          "animation_type": "rickshaw_ride",
          "display_type": "fullscreen",
          "badge": "Desi Hit",
          "description": "Colorful hand-painted Bengali rickshaw rolling with ringing bell."
        },
        {
          "id": 2,
          "name": "Melodic Bamboo Flute",
          "coins": 520,
          "category": "desi",
          "image_url": "https://chinchins.live/uploads/gifts/krishna_flute_banshi.svg",
          "animation_full_url": "https://chinchins.live/uploads/gifts/krishna_flute_banshi.svg",
          "animation_type": "flute_melody",
          "display_type": "overlay",
          "badge": "Banshi",
          "description": "Traditional bamboo flute playing magical floating musical notes."
        }
      ]
    }
  }
  ```

#### 2. Send Gift to Host / User
- **Endpoint**: `POST /api/gifts/send` (or `POST /api/gift/send`, `POST /api/live/send-gift`)
- **Request Body**:
  ```json
  {
    "gift_id": 1,
    "receiver_id": 14,
    "quantity": 1,
    "context": "live_stream",
    "room_id": "room_101"
  }
  ```
- **Response**:
  ```json
  {
    "status": true,
    "message": "Sent 1x Traditional Rickshaw to Ayesha successfully!",
    "data": {
      "transaction_id": "GIFT_TX_1725549021",
      "sender_coins_after": 24001,
      "host_earned_coins": 499,
      "gift": {
        "name": "Traditional Rickshaw",
        "animation_url": "https://chinchins.live/uploads/gifts/traditional_rickshaw.svg",
        "animation_type": "rickshaw_ride"
      }
    }
  }
  ```

---

### 💳 7.2 Premium VIP Cards & Daily Check-in Endpoints

#### 1. Get All VIP Privilege Cards & Home Floating Banner
- **Endpoint**: `GET /api/vip-cards` (or `GET /api/premium-vip/cards`)
- **Sample Response**:
  ```json
  {
    "status": true,
    "message": "VIP privilege cards loaded successfully.",
    "data": {
      "floating_banner": {
        "is_enabled": true,
        "title": "Extra Gems",
        "tag": "Monthly Card",
        "action_type": "OPEN_PREMIUM_VIP",
        "target_screen": "/premium-vip"
      },
      "cards": [
        {
          "id": 1,
          "card_type": "luxury_monthly",
          "name": "Luxury Monthly Card",
          "price_bdt": 2400.0,
          "original_price_bdt": 4800.0,
          "discount_percent": 50,
          "duration_days": 30,
          "instant_reward_coins": 66600,
          "daily_checkin_total_coins": 87110,
          "total_return_coins": 153710,
          "card_color": "#2979FF",
          "extra_rewards": [
            { "title": "30 Days Diamond Frame", "icon": "frame_diamond" },
            { "title": "30 Days VIP Crown", "icon": "svip_crown" }
          ]
        }
      ]
    }
  }
  ```

#### 2. Claim Daily VIP Login Bonus
- **Endpoint**: `POST /api/vip-cards/claim-daily`
- **Request Body**: `{ "subscription_id": 12 }`
- **Response**:
  ```json
  {
    "status": true,
    "message": "Successfully claimed 3,500 Daily Gems for Day 2!",
    "data": {
      "coins_claimed": 3500,
      "current_balance": 28500,
      "day": 2,
      "is_completed": false
    }
  }
  ```

---

### 🎖️ 7.3 Profile Base Frames & Level Status Endpoints

#### 1. Get All Level Bases & Avatar Frames
- **Endpoint**: `GET /api/profile-bases` (or `GET /api/levels`, `GET /api/profile-frames`)

#### 2. Get User Level Status & Progression
- **Endpoint**: `GET /api/user/level-status` (or `GET /api/user/level-status?account_id=84920183`)
- **Response**:
  ```json
  {
    "status": true,
    "data": {
      "user": {
        "id": 14,
        "account_id": "84920183",
        "name": "Ayesha Rahman",
        "avatar_url": "https://chinchins.live/uploads/profiles/host_14.jpg",
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
        "avatar_frame_url": "https://chinchins.live/uploads/bases/profile_base_royal_gold.svg",
        "badge_color": "#f59e0b",
        "badge_icon": "gem"
      }
    }
  }
  ```

---

### 📞 7.4 WebRTC 1-on-1 Calling & In-Call Recharge Endpoints

- **`GET /api/call/config`**: Get call rate (100 coins/min), 16s free trial settings, teaser message, and quick messages.
- **`POST /api/call/initiate`**: Start call session. (Free Host can call with 0 coins).
- **`POST /api/call/accept`**: Answer call and begin 16s free countdown.
- **`POST /api/call/heartbeat`**: Send every 5-10s to deduct coins (50% host / 50% admin) and check balance.

---

## 📱 8. Mobile Client UI & Animation Implementation (Flutter Code Snippets)

### 🎨 8.1 Avatar with Dynamic Level Base Frame Overlay:
```dart
Widget buildAvatarWithLevelFrame({
  required String avatarUrl,
  required String? frameUrl,
  required int level,
  required String badgeColor,
  double size = 96.0,
}) {
  final double frameSize = size * 1.45;

  return Stack(
    alignment: Alignment.center,
    clipBehavior: Clip.none,
    children: [
      // 1. Circular Avatar Photo
      ClipOval(
        child: Image.network(
          avatarUrl,
          width: size,
          height: size,
          fit: BoxFit.cover,
          errorBuilder: (_, __, ___) => Container(
            width: size,
            height: size,
            color: Colors.grey[850],
            child: const Icon(Icons.person, color: Colors.white70),
          ),
        ),
      ),

      // 2. Overlaid Base Frame (SVG / PNG)
      if (frameUrl != null && frameUrl.isNotEmpty)
        Positioned(
          child: IgnorePointer(
            child: frameUrl.endsWith('.svg')
                ? SvgPicture.network(frameUrl, width: frameSize, height: frameSize, fit: BoxFit.contain)
                : Image.network(frameUrl, width: frameSize, height: frameSize, fit: BoxFit.contain),
          ),
        ),

      // 3. Level Badge Pill
      Positioned(
        bottom: -4,
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
          decoration: BoxDecoration(
            color: Color(int.parse(badgeColor.replaceFirst('#', '0xFF'))),
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: Colors.white, width: 1.5),
            boxShadow: const [BoxShadow(color: Colors.black45, blurRadius: 4, offset: Offset(0, 2))],
          ),
          child: Text(
            'Lv.$level',
            style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 10),
          ),
        ),
      ),
    ],
  );
}
```

### 🎆 8.2 Full-Screen Live Streaming Gift Player:
```dart
class LiveGiftPlayerOverlay extends StatefulWidget {
  final String giftAnimationUrl;
  final String animationType; // e.g. 'rickshaw_ride', 'flying_dove', 'dragon_roar_fire'
  final VoidCallback onComplete;

  const LiveGiftPlayerOverlay({
    Key? key,
    required this.giftAnimationUrl,
    required this.animationType,
    required this.onComplete,
  }) : super(key: key);

  @override
  _LiveGiftPlayerOverlayState createState() => _LiveGiftPlayerOverlayState();
}

class _LiveGiftPlayerOverlayState extends State<LiveGiftPlayerOverlay>
    with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  late Animation<Offset> _slideAnimation;
  late Animation<double> _scaleAnimation;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(vsync: this, duration: const Duration(seconds: 4));

    if (widget.animationType == 'rickshaw_ride' || widget.animationType == 'speed_drive') {
      // Moves across screen from right to left with vibration
      _slideAnimation = Tween<Offset>(
        begin: const Offset(1.5, 0.3),
        end: const Offset(-1.5, 0.3),
      ).animate(CurvedAnimation(parent: _controller, curve: Curves.easeInOut));
      _scaleAnimation = Tween<double>(begin: 1.0, end: 1.2).animate(_controller);
    } else if (widget.animationType == 'flying_dove' || widget.animationType == 'phoenix_flight') {
      // Flies upwards with sine wave curve
      _slideAnimation = Tween<Offset>(
        begin: const Offset(0.0, 1.2),
        end: const Offset(0.0, -1.2),
      ).animate(CurvedAnimation(parent: _controller, curve: Curves.easeOut));
      _scaleAnimation = Tween<double>(begin: 0.8, end: 1.3).animate(_controller);
    } else {
      // Fullscreen 3D burst
      _slideAnimation = Tween<Offset>(begin: Offset.zero, end: Offset.zero).animate(_controller);
      _scaleAnimation = TweenSequence<double>([
        TweenSequenceItem(tween: Tween(begin: 0.0, end: 1.2).chain(CurveTween(curve: Curves.elasticOut)), weight: 40),
        TweenSequenceItem(tween: Tween(begin: 1.2, end: 1.0), weight: 30),
        TweenSequenceItem(tween: Tween(begin: 1.0, end: 0.0).chain(CurveTween(curve: Curves.easeIn)), weight: 30),
      ]).animate(_controller);
    }

    _controller.forward().whenComplete(widget.onComplete);
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Positioned.fill(
      child: IgnorePointer(
        child: SlideTransition(
          position: _slideAnimation,
          child: ScaleTransition(
            scale: _scaleAnimation,
            child: Center(
              child: SizedBox(
                width: 300,
                height: 300,
                child: SvgPicture.network(widget.giftAnimationUrl, fit: BoxFit.contain),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
```

---

## 🏁 Summary Checklist for Mobile Developers

- [x] Use `GET /api/gifts/catalog` to load the 100+ gifts organized in 8 category tabs.
- [x] When a gift is sent via `POST /api/gifts/send`, trigger `LiveGiftPlayerOverlay` with the animation type.
- [x] Use `user.avatar_frame_url` and `user.current_level` from every profile API response to display the avatar base frame.
- [x] Integrate `GET /api/vip-cards` and `POST /api/vip-cards/claim-daily` on the Premium VIP screen.
- [x] In video calls, enforce the 16s free countdown from `/api/call/config` and present the In-Call Recharge Modal if coins run low.
