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

---

## 💎 ৪. হোম স্ক্রিন ফ্লোটিং ভিআইপি অ্যাকশন আইকন ও ব্যানার এপিআই (Home Screen Floating VIP Widget / Action Icon API)

হোম স্ক্রিনে ডানপাশে থাকা ফ্লোটিং বাটন ("Extra Gems" / "Monthly Card") সম্পূর্ণ ডাইনামিক। অ্যাডমিন প্যানেল থেকে যেকোনো সময় আইকন বা ব্যানার ইমেজ আপলোড ও পরিবর্তন করা যাবে। আপলোড করা ফাইল সরাসরি `public/uploads/floating_action_icons/` এ সেভ হয়।

- **Method**: `GET`
- **Endpoints**: 
  - `https://chinchins.live/api/floating-banner` *(Primary)*
  - `https://chinchins.live/api/floating-action-icon`
  - `https://chinchins.live/api/floating-widget`
  - `https://chinchins.live/api/vip-cards/banner`
- **Response Format**:
```json
{
  "success": true,
  "status": true,
  "data": {
    "is_enabled": true,
    "title": "Extra Gems",
    "subtitle": "Monthly Card",
    "image_url": "https://chinchins.live/uploads/floating_action_icons/extra_gems_1727263590.png",
    "target_action": "OPEN_PREMIUM_VIP"
  }
}
```

### 📱 ফ্লাটার ইমপ্লিমেন্টেশন গাইড (Flutter Implementation):
1. **ডায়নামিক ইমেজ লোডিং**: অ্যাপ চালু হলে বা হোমস্ক্রিনে `GET /api/floating-banner` কল করে `image_url` থেকে আইকন ক্যাশ করে ফ্লোটিং একশন বাটনে দেখাবে।
2. **ক্লিক অ্যাকশন**: বাটনে ট্যাপ করলে `target_action === 'OPEN_PREMIUM_VIP'` চেক করে সরাসরি VIP Privilege Cards / Monthly Card বটমশিট বা স্ক্রিনে রিডাইরেক্ট করবে।
3. **অন/অফ কন্ট্রোল**: `is_enabled: false` হলে অ্যাপে ফ্লোটিং বাটনটি হাইড থাকবে।

---

---

## 💬 ৬. লাইভ স্ট্রিম মেসেজ সেন্ডার লেভেল ও ডুপ্লিকেট ইকো ফিক্স (Live Chat Dynamic Level & Deduplication)

লাইভ স্ট্রিমে মেসেজ পাঠানোর সময় সেন্ডারের আসল লেভেল (যেমন `Lv.7`, `Lv.3`) ডায়নামিকভাবে `User` প্রোফাইল থেকে ক্যালকুলেট হয়ে চ্যাট পেলোডে যুক্ত হয় এবং `toOthers()` সকেট ব্রডকাস্টিংয়ের মাধ্যমে একই মেসেজ ৩ বার আসার ইকো সম্পূর্ণ বন্ধ করা হয়েছে।

- **Endpoint**: `POST /api/live/send-message` (অথবা `/api/v1/streams/send-message`, `/api/live/message`)
- **Payload Example**:
```json
{
  "room_id": "45",
  "message": "Hi"
}
```
- **Response & Socket Broadcast (`LiveChatMessageEvent`)**:
```json
{
  "status": "success",
  "success": true,
  "data": {
    "id": 1052,
    "room_id": "45",
    "user_id": 101,
    "user_name": "nazmul",
    "user_avatar": "https://chinchins.live/uploads/avatars/user_101.jpg",
    "user": {
      "id": 101,
      "name": "nazmul",
      "display_name": "nazmul",
      "avatar_url": "https://chinchins.live/uploads/avatars/user_101.jpg",
      "level": "Lv.7",
      "level_number": 7,
      "current_level": 7
    },
    "message": "Hi",
    "type": "text",
    "level": "Lv.7",
    "level_number": 7,
    "current_level": 7,
    "user_level": "Lv.7",
    "created_at": "2026-09-25T19:30:00+06:00"
  }
}
```

---

## 📸 ৭. হোস্ট অন-কল ব্যাকগ্রাউন্ড ফটো ক্যারোসেল ("I'll back soon...") ও সকেট ইভেন্ট

হোস্ট লাইভ স্ট্রিমিং চলাকালীন কোনো প্রাইভেট ১-অন-১ ভিডিও কল রিসিভ করলে লাইভ রুম কেটে যাবে না। ভিডিও ফিড সাময়িকভাবে হোস্টের প্রোফাইল ও গ্যালারি ছবির অটোমেটিক স্লাইডশোতে রূপান্তরিত হবে এবং স্ক্রিনে `"I'll back soon..."` পিল ব্যাজ প্রদর্শিত হবে। রুমের দর্শকরা স্বাভাবিকভাবে টেক্সট চ্যাট চালিয়ে যেতে পারবেন।

### ৭.১ স্ট্যাটাস আপডেট এপিআই
- **Method**: `POST`
- **Endpoint**: `https://chinchins.live/api/live/{id}/host-call-status` (বা `/api/live/host-call-status`)
- **Request Body**:
```json
{
  "room_id": "45",
  "is_on_call": true
}
```
- **Response & Real-Time Socket Broadcast (`LiveHostOnCallEvent`)**:
  - **চ্যানেল**: `live-room.{roomId}`, `presence-live-stream.{roomId}`, `live.{roomId}`
  - **ইভেন্ট নেম**: `LiveHostOnCallEvent`
```json
{
  "event": "LiveHostOnCallEvent",
  "room_id": "45",
  "host_id": 101,
  "host_name": "Diya",
  "is_on_call": true,
  "status": "busy_on_call",
  "back_soon_text": "I'll back soon...",
  "gallery_photos": [
    "https://chinchins.live/uploads/profiles/diya_1.jpg",
    "https://chinchins.live/uploads/profiles/diya_cover.jpg",
    "https://chinchins.live/uploads/profiles/diya_gallery_2.jpg"
  ],
  "timestamp": "2026-09-25T19:35:00+06:00"
}
```

---

## 📞 ৮. কল হিস্ট্রি এপিআই (Call History & Logs RESTful API)

মেসেজ ও ইনটিমেসি স্ক্রিনের পাশে থাকা "Call History" ট্যাবের জন্য ডেডিকেটেড এপিআই। ব্যবহারকারীর পূর্ববর্তী সকল অডিও/ভিডিও কলের বিস্তারিত তালিকা রিটার্ন করে।

- **Method**: `GET`
- **Endpoints**: 
  - `https://chinchins.live/api/calls/history` *(Primary)*
  - `https://chinchins.live/api/call/history`
  - `https://chinchins.live/api/call-logs`
  - `https://chinchins.live/api/v1/calls/history`
- **Header**: `Authorization: Bearer <user_token>`
- **Query Params**: `page=1`, `per_page=30`
- **Response Format**:
```json
{
  "status": true,
  "success": true,
  "message": "Call history retrieved successfully.",
  "data": [
    {
      "id": 102,
      "call_session_id": "call_65fe8a12",
      "channel_name": "call_102",
      "call_type": "video",
      "call_type_label": "[Video]",
      "is_caller": true,
      "direction": "outgoing",
      "status": "completed",
      "status_label": "Completed",
      "duration_seconds": 165,
      "duration_formatted": "02:45",
      "coins_spent": 50,
      "coins_earned": 0,
      "created_at": "2026-09-25T08:17:00Z",
      "formatted_date": "2026/09/25 08:17",
      "time_ago": "2 hours ago",
      "other_user": {
        "id": 204,
        "name": "Diya",
        "display_name": "Diya",
        "avatar_url": "https://chinchins.live/uploads/profiles/diya.jpg",
        "gender": "female",
        "level": "Lv.7",
        "level_number": 7,
        "is_online": true,
        "video_rate": 60
      }
    },
    {
      "id": 101,
      "call_session_id": "call_65fe8901",
      "channel_name": "call_101",
      "call_type": "video",
      "call_type_label": "[Video]",
      "is_caller": false,
      "direction": "incoming",
      "status": "completed",
      "status_label": "Completed",
      "duration_seconds": 120,
      "duration_formatted": "02:00",
      "coins_spent": 0,
      "coins_earned": 80,
      "created_at": "2026-09-25T08:05:00Z",
      "formatted_date": "2026/09/25 08:05",
      "time_ago": "2 hours ago",
      "other_user": {
        "id": 205,
        "name": "Lali",
        "display_name": "Lali",
        "avatar_url": "https://chinchins.live/uploads/profiles/lali.jpg",
        "gender": "female",
        "level": "Lv.5",
        "level_number": 5,
        "is_online": true,
        "video_rate": 60
      }
    }
  ],
  "current_page": 1,
  "last_page": 1,
  "per_page": 30,
  "total": 2
}
```

---

## ⚡ ৯. ১ সেকেন্ডের মধ্যে ইনস্ট্যান্ট ভিডিও কল কানেক্টিভিটি (<1s Fast Connect Optimization)

- অ্যাপ যখন `POST /api/call/instant` বা `POST /api/call/initiate` কল করবে, তখন একযোগে কলার ও রিসিভারের জন্য লাইভকিট/ওয়েবআরটিসি প্রি-সাইনড টোকেন, এসডিপি অফার ও আইস সার্ভার তালিকা ইনস্ট্যান্ট রিটার্ন করা হয়।
- কোনো সেকেন্ডারি এপিআই পুলিং দরকার নেই; অ্যাপ সরাসরি প্রাপ্ত টোকেন দিয়ে `< 800ms`-এর মধ্যে লাইভ ভিডিও ফিডে জয়েন করে ফেলে।


