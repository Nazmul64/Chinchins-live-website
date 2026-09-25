# 📱 Chinchins Live - Complete Custom RESTful API & Real-Time Socket Documentation

---

## 📌 ১. লাইভকিট টোকেন ও কো-হোস্ট সকেট আর্কিটেকচার (LiveKit & Co-Host Real-Time Architecture)

### ১.১ লাইভকিট টোকেন পারমিশন (Viewer vs Co-Host vs Host)
- **সাধারণ ভিউয়ার (Viewer)**:
  - যখন সাধারণ কোনো দর্শক লাইভ রুমে যুক্ত হয় (`GET /api/live/get-token` বা `POST /api/live/{id}/join`), তখন টোকেনে পারমিশন থাকবে:
    - `canPublish: false`
    - `canSubscribe: true`
    - `canPublishData: true` (লাইভ টেক্সট চ্যাট ও লাইক পাঠানোর জন্য)
- **হোস্ট ও কো-হোস্ট (Host & Co-Host Guest)**:
  - হোস্ট যখন ব্রডকাস্ট শুরু করে বা কোনো গেস্টের কো-হোস্ট রিকোয়েস্ট অ্যাকসেপ্ট করে (`POST /api/live/respond-request` অথবা `POST /api/live/handle-cohost`), তখন স্বয়ংক্রিয়ভাবে তার পারমিশন আপডেট হবে:
    - `canPublish: true`
    - `canSubscribe: true`
    - `canPublishData: true`

---

### ১.২ কো-হোস্ট জয়েন ইভেন্ট (Dynamic `CoHostJoinedEvent`)
হোস্ট যখন কো-হোস্ট রিকোয়েস্ট একসেপ্ট করে, তখন হার্ডকোডেড কোনো ডাটা না পাঠিয়ে হোস্ট ও গেস্টের আসল নাম ও অ্যাভাটারসহ ডাইনামিক ইভেন্ট ব্রডকাস্ট করা হয়:

```php
broadcast(new \App\Events\CoHostJoinedEvent($roomId, [
    'host_id'      => $host->id,
    'host_name'    => $host->display_name ?? $host->name,
    'host_avatar'  => $host->avatar_url ?? $host->avatar,
    'guest_id'     => $guest->id,
    'guest_name'   => $guest->display_name ?? $guest->name,
    'guest_avatar' => $guest->avatar_url ?? $guest->avatar,
    'can_publish'  => true,
    'token'        => $guestToken['token'],
    'livekit_url'  => $livekitUrl,
]))->toOthers();
```

- **সকেট চ্যানেল**: `live-room.{roomId}`, `presence-live-stream.{roomId}`, `live-stream.{roomId}`, `live.{roomId}`
- **ইভেন্ট নেম**: `CoHostJoinedEvent` (অ্যালিয়াস: `cohost.status.changed`, `cohost.accepted`)
- **পেলোড ফরম্যাট**:
```json
{
  "event": "CoHostJoinedEvent",
  "room_id": "45",
  "host_id": 101,
  "host_name": "piya",
  "host_avatar": "https://chinchins.live/uploads/profiles/piya.jpg",
  "guest_id": 102,
  "guest_name": "Anjali",
  "guest_avatar": "https://chinchins.live/uploads/profiles/anjali.jpg",
  "can_publish": true,
  "token": "eyJhbGciOi...",
  "livekit_url": "wss://chinchins.live/livekit",
  "timestamp": "2026-09-25T14:50:00+06:00"
}
```

---

### ১.৩ ফ্লাটার ক্লায়েন্ট কনফিগারেশন (Flutter Video Bitrate & PK Auto-Fade)
- **গেস্ট ক্যামেরা বিটরেট**:
  - গেস্ট ক্যামেরার বিটরেট **2.5 Mbps (2500 kbps)**-এ সেট করুন যাতে ভিউয়ারদের স্ক্রিন ক্রিস্প ও ক্লিয়ার থাকে:
    ```dart
    const VideoParameters(
      dimensions: VideoDimensionsPreset.h720_169,
      encoding: VideoEncoding(
        maxBitrate: 2500000, // 2.5 Mbps
        maxFramerate: 30,
      ),
    );
    ```
- **পিকে (PK) স্ট্যাটাস ২ সেকেন্ড পর অটো-ফেড**:
  ```dart
  if (isPkActive) {
    Future.delayed(const Duration(seconds: 2), () {
      if (mounted) {
        setState(() => showPkBadge = false);
      }
    });
  }
  ```

---

## 🏷️ ২. কাস্টমার প্রোফাইল ("Me" স্ক্রিন) আইকন ও লোকাল স্টোরেজ এপিআই (Customer Profile Icons)

কাস্টমার অ্যাপের **"Me" (প্রোফাইল)** স্ক্রিনের ১০টি আইটেম ও ফিচার এডমিন প্যানেল থেকে নিয়ন্ত্রণ এবং জিরো-লোড লোকাল ক্যাশ।

### ২.১ সমস্ত ১০টি প্রোফাইল আইকন ডাটা
- **Method**: `GET`
- **URL**: `https://chinchins.live/api/customer-profile-icons`
- **বিকল্প URLs**: 
  - `/api/customer-profile/icons`
  - `/api/v1/customer-profile-icons`
  - `/api/app-icons/profile`
- **Headers**:
  ```http
  Accept: application/json
  If-None-Match: "chinchins_icons_10_..." (ঐচ্ছিক ETag)
  ```
- **Response Headers**:
  ```http
  HTTP/1.1 200 OK
  Cache-Control: public, max-age=86400, stale-while-revalidate=3600
  ETag: "chinchins_icons_10_1727223423"
  ```
- **Response JSON**:
```json
{
  "status": true,
  "success": true,
  "message": "Customer profile icons retrieved successfully.",
  "timestamp": "2026-09-25T14:50:00+06:00",
  "data": {
    "version_hash": "a1b2c3d4e5f6...",
    "total_icons": 10,
    "cache_ttl_seconds": 86400,
    "cache_strategy": "CACHE_FIRST_WITH_ETAG_REVALIDATION",
    "icons": [
      {
        "id": 1,
        "key": "my_gems",
        "title": "My Gems",
        "subtitle": "User Diamond & Gem Balance",
        "category": "wallet_card",
        "icon_url": "https://chinchins.live/uploads/customer_profile_icon/default_my_gems.png",
        "default_icon_url": "https://chinchins.live/uploads/customer_profile_icon/default_my_gems.png",
        "is_custom": false,
        "target_route": "wallet/gems",
        "sort_order": 1,
        "is_active": true
      },
      {
        "id": 2,
        "key": "beans_center",
        "title": "Beans Center",
        "subtitle": "Beans & Earnings Exchange",
        "category": "wallet_card",
        "icon_url": "https://chinchins.live/uploads/customer_profile_icon/default_beans_center.png",
        "default_icon_url": "https://chinchins.live/uploads/customer_profile_icon/default_beans_center.png",
        "is_custom": false,
        "target_route": "wallet/beans",
        "sort_order": 2,
        "is_active": true
      },
      {
        "id": 3,
        "key": "spend_less_card",
        "title": "Spend Less, Get More Gems!",
        "subtitle": "Update to New User Weekly Card",
        "category": "banner_card",
        "icon_url": "https://chinchins.live/uploads/customer_profile_icon/default_spend_less_card.png",
        "badge_text": "big discount",
        "badge_color": "#FEF08A",
        "target_route": "wallet/spend_less",
        "sort_order": 3,
        "is_active": true
      },
      {
        "id": 4,
        "key": "svip",
        "title": "SVIP",
        "category": "action_menu",
        "icon_url": "https://chinchins.live/uploads/customer_profile_icon/default_svip.png",
        "target_route": "wallet/svip",
        "sort_order": 4,
        "is_active": true
      },
      {
        "id": 5,
        "key": "my_bag",
        "title": "My Bag",
        "category": "action_menu",
        "icon_url": "https://chinchins.live/uploads/customer_profile_icon/default_my_bag.png",
        "target_route": "bag/my_bag",
        "sort_order": 5,
        "is_active": true
      },
      {
        "id": 6,
        "key": "gems_center",
        "title": "Gems Center",
        "category": "action_menu",
        "icon_url": "https://chinchins.live/uploads/customer_profile_icon/default_gems_center.png",
        "target_route": "wallet/gems_center",
        "sort_order": 6,
        "is_active": true
      },
      {
        "id": 7,
        "key": "payment_details",
        "title": "Payment details",
        "category": "action_menu",
        "icon_url": "https://chinchins.live/uploads/customer_profile_icon/default_payment_details.png",
        "target_route": "wallet/payment_details",
        "sort_order": 7,
        "is_active": true
      },
      {
        "id": 8,
        "key": "my_level",
        "title": "My Level",
        "category": "action_menu",
        "icon_url": "https://chinchins.live/uploads/customer_profile_icon/default_my_level.png",
        "target_route": "profile/my_level",
        "sort_order": 8,
        "is_active": true
      },
      {
        "id": 9,
        "key": "sign_in",
        "title": "Sign-In",
        "category": "action_menu",
        "icon_url": "https://chinchins.live/uploads/customer_profile_icon/default_sign_in.png",
        "target_route": "daily_checkin",
        "sort_order": 9,
        "is_active": true
      },
      {
        "id": 10,
        "key": "reward",
        "title": "Reward",
        "category": "action_menu",
        "icon_url": "https://chinchins.live/uploads/customer_profile_icon/default_reward.png",
        "target_route": "rewards",
        "sort_order": 10,
        "is_active": true
      }
    ],
    "map": {
      "my_gems": { ... },
      "beans_center": { ... },
      "spend_less_card": { ... },
      "svip": { ... },
      "my_bag": { ... },
      "gems_center": { ... },
      "payment_details": { ... },
      "my_level": { ... },
      "sign_in": { ... },
      "reward": { ... }
    },
    "offline_storage": {
      "storage_key": "chinchins_customer_profile_icons_v1",
      "version": "a1b2c3d4e5f6...",
      "data": { ... }
    }
  }
}
```

---

## 🔴 ৩. হট ও লাইভ কার্ড ফিড এপিআই (Hot & Live Feed Badges)

- **Method**: `GET`
- **URL**: `https://chinchins.live/api/live/card-feed` (বিকল্প: `/api/hot/feed`, `/api/v1/live/card-feed`, `/api/home`, `/api/hot`)
- **ফিচারসমূহ**:
  - `live_badge`: লাইভ থাকলে পার্পল গ্রেডিয়েন্ট ক্যাপসুল (`#A855F7` ➔ `#EC4899`) সাথে ৩টি জাম্পিং সাউন্ড ওয়েভ ইকুয়ালাইজার বার এবং `"Live"` টেক্সট। অনলাইনে থাকলে গ্লাস ক্যাপসুল সাথে গ্রিন ডট (`#22C55E`)।
  - `verified_badge`: সিয়ান-ব্লু ক্যাপসুল (`#38BDF8`) সাথে সাদা `'V'` মার্ক।
  - `action_button`: নিচের ডানপাশের নচ কাটআউট (`notch_radius: 28px`) সহ পালসিং ভিডিও বাটন।

---

## 🛠️ ৪. এডমিন প্যানেল ইউআরএল ও অ্যাকশনস

- **Customer Profile Icons**: `https://chinchins.live/admin/customer-profile-icons`
- **Live Streaming Management**: `https://chinchins.live/admin/live-streams`
- **Party Rooms Management**: `https://chinchins.live/admin/party-rooms`
