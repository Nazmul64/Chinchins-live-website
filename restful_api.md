# 🎙️ Chinchins Live - Complete RESTful API & Real-Time Documentation

---

## 📌 ১. লেটেস্ট আপডেট ও সমস্যা সমাধান (Latest Updates)

1. **405 Method Not Allowed সমাধান**:
   - `POST /api/party-rooms/{id}/seat-requests` এবং `POST /api/party-rooms/{id}/seat-requests/respond` রাউট সংযুক্ত করা হয়েছে।
2. **ডুপ্লিকেট মেসেজ রোধ**:
   - `PartyRoomMessageSent` ইভেন্টটিতে `->toOthers()` ব্যবহার করা হয়েছে যাতে প্রেরক নিজে মেসেজ দুবার রিসিভ না করে।
3. **গিফট অ্যানিমেশন ব্রডকাস্ট (LiveGiftSentEvent)**:
   - ইউজার গিফট পাঠালে তাৎক্ষণিকভাবে কয়েন ট্রানজ্যাকশন সম্পন্ন হয় এবং রুমে থাকা সবার জন্য `LiveGiftSentEvent` ব্রডকাস্ট হয় (অ্যানিমেশন লিঙ্ক ও সাউন্ড সহ)।
4. **সিট রিকোয়েস্ট ইভেন্ট (SeatRequestReceivedEvent & SeatRequestEvent)**:
   - সিট রিকোয়েস্ট পাঠানোর সাথে সাথে হোস্টের কাছে রিয়েল-টাইম পুশ পৌঁছে যায়।

---

## 🌐 ২. পার্টি রুম ও ভয়েস চ্যাট এপিআই (Party Room APIs)

### ২.১ রুমের বিস্তারিত তথ্য (Get Room Details)
- **Method**: `GET`
- **URL**: `https://chinchins.live/api/party-rooms/{id}`
- **Headers**:
  ```http
  Authorization: Bearer {token}
  Accept: application/json
  ```
- **Response**:
```json
{
  "success": true,
  "status": true,
  "token": "eyJhbGciOi...",
  "livekit_token": "eyJhbGciOi...",
  "livekit_url": "wss://chinchins.live/livekit",
  "can_publish": true,
  "data": {
    "room": {
      "id": 22,
      "room_id": "89201481",
      "room_title": "Live Party Room",
      "room_type": "voice",
      "topic_tag": "Singing",
      "channel_name": "party_voice_89201481",
      "max_seats": 8,
      "occupied_seats_count": 1,
      "online_members_count": 12,
      "is_host": true,
      "host": {
        "id": 101,
        "account_id": "84920183",
        "name": "Nazmul",
        "display_name": "Nazmul",
        "avatar": "https://chinchins.live/uploads/profiles/nazmul.jpg",
        "avatar_url": "https://chinchins.live/uploads/profiles/nazmul.jpg",
        "avatar_frame_url": "https://chinchins.live/uploads/bases/crown.png",
        "level": 15,
        "coins": 54000
      },
      "seats": [
        {
          "seat_index": 1,
          "role": "host",
          "status": "occupied",
          "is_occupied": true,
          "is_muted": false,
          "user": {
            "id": 101,
            "account_id": "84920183",
            "name": "Nazmul",
            "display_name": "Nazmul",
            "avatar": "https://chinchins.live/uploads/profiles/nazmul.jpg",
            "avatar_url": "https://chinchins.live/uploads/profiles/nazmul.jpg",
            "level": 15,
            "coins": 54000,
            "is_host": true
          }
        },
        {
          "seat_index": 2,
          "role": "guest",
          "status": "empty",
          "is_occupied": false,
          "is_muted": false,
          "user": null
        }
      ]
    }
  }
}
```

---

### ২.২ সিটে বসার রিকোয়েস্ট পাঠানো (Request a Seat)
- **Method**: `POST`
- **URL**: `https://chinchins.live/api/party-rooms/{id}/seat-requests`
  *(বিকল্প: `https://chinchins.live/api/party-rooms/{id}/request-seat`)*
- **Headers**:
  ```http
  Authorization: Bearer {token}
  Accept: application/json
  Content-Type: application/json
  ```
- **Body**:
  ```json
  {
    "seat_index": 2
  }
  ```
- **Response**:
```json
{
  "success": true,
  "status": true,
  "message": "Seat request sent to host successfully.",
  "data": {
    "invitation_id": 14,
    "request_id": 14,
    "seat_index": 2,
    "user": {
      "id": 105,
      "account_id": "77391204",
      "name": "Ruma",
      "display_name": "Ruma",
      "avatar": "https://chinchins.live/uploads/profiles/ruma.jpg",
      "avatar_url": "https://chinchins.live/uploads/profiles/ruma.jpg",
      "level": 4
    }
  }
}
```

---

### ২.৩ হোস্টের সিট রিকোয়েস্ট গ্রহণ / বাতিল করা (Respond to Seat Request)
- **Method**: `POST`
- **URL**: `https://chinchins.live/api/party-rooms/{id}/seat-requests/respond`
  *(বিকল্প: `https://chinchins.live/api/party-rooms/{id}/seat-requests/{requestId}/respond`)*
- **Body**:
  ```json
  {
    "request_id": 14,
    "action": "accept"
  }
  ```
- **Response (Accept)**:
```json
{
  "success": true,
  "status": true,
  "action": "accepted",
  "message": "Seat request accepted. Ruma is now on Seat #2.",
  "seat_index": 2,
  "user_id": 105,
  "token": "eyJhbGciOi...",
  "livekit_token": "eyJhbGciOi...",
  "livekit_url": "wss://chinchins.live/livekit",
  "can_publish": true,
  "data": {
    "seat_index": 2,
    "user": {
      "id": 105,
      "account_id": "77391204",
      "name": "Ruma",
      "display_name": "Ruma",
      "avatar": "https://chinchins.live/uploads/profiles/ruma.jpg",
      "avatar_url": "https://chinchins.live/uploads/profiles/ruma.jpg",
      "level": 4
    },
    "can_publish": true
  }
}
```

---

### ২.৪ রুমে মেসেজ পাঠানো (Send In-Room Chat Message)
- **Method**: `POST`
- **URL**: `https://chinchins.live/api/party-rooms/{id}/messages/send`
  *(বিকল্প: `https://chinchins.live/api/party-rooms/{id}/send-message`)*
- **Body**:
  ```json
  {
    "message": "Hello everyone! 🎤"
  }
  ```
- **Response**:
```json
{
  "success": true,
  "status": true,
  "message": "Message sent successfully.",
  "data": {
    "id": 892,
    "room_id": "89201481",
    "type": "text",
    "message": "Hello everyone! 🎤",
    "created_at": "2026-09-21T21:40:00Z",
    "sender": {
      "id": 105,
      "name": "Ruma",
      "avatar_url": "https://chinchins.live/uploads/profiles/ruma.jpg"
    }
  }
}
```

---

### ২.৫ রুমে গিফট পাঠানো (Send In-Room Gift)
- **Method**: `POST`
- **URL**: `https://chinchins.live/api/party-rooms/{id}/send-gift`
  *(বিকল্প: `https://chinchins.live/api/party-rooms/{id}/gift`)*
- **Body**:
  ```json
  {
    "gift_id": 12,
    "receiver_id": 101,
    "count": 1
  }
  ```
- **Response**:
```json
{
  "success": true,
  "status": true,
  "message": "Gift Sports Car sent successfully!",
  "data": {
    "remaining_coins": 18200,
    "total_cost": 500,
    "gift": {
      "id": 12,
      "name": "Sports Car",
      "icon_url": "https://chinchins.live/uploads/gifts/car.png",
      "animation_url": "https://chinchins.live/uploads/gifts/car.svga",
      "count": 1
    },
    "receiver": {
      "id": 101,
      "name": "Nazmul"
    }
  }
}
```

---

## 📡 ৩. রিয়েল-টাইম সকেট ইভেন্ট (Reverb / Pusher WebSockets)

| Channel Name | Event Name (`broadcastAs`) | বিবরণ (Description) |
|---|---|---|
| `party-room.{roomId}` / `party.{roomId}` | `SeatRequestReceivedEvent` / `seat.requested` | নতুন সিট রিকোয়েস্ট আসলে হোস্টের স্ক্রিনে পপআপ দেখাবে |
| `party-room.{roomId}` / `party.{roomId}` | `SeatUpdatedEvent` | সিট দখল, খালি হওয়া, বা মিউট স্ট্যাটাস পরিবর্তন |
| `party-room.{roomId}` / `party.{roomId}` | `PartyRoomMessageSent` | রুমে নতুন চ্যাট মেসেজ আসলে (সেন্ডার বাদে বাকিরা রিসিভ করবে) |
| `party-room.{roomId}` / `live-stream.{roomId}` | `gift.received` (`LiveGiftSentEvent`) | গিফট অ্যানিমেশন ও সাউন্ড এফেক্ট প্লে করার জন্য |

---

## 📱 ৪. Flutter Integration Code Snippet

```dart
// 1. Listen for Seat Requests (Host screen)
pusher.subscribe(
  channelName: 'party-room.$roomId',
  onEvent: (event) {
    if (event.eventName == 'SeatRequestReceivedEvent' || event.eventName == 'seat.requested') {
      final data = jsonDecode(event.data);
      showSeatRequestDialog(data);
    }
    if (event.eventName == 'PartyRoomMessageSent') {
      final msg = jsonDecode(event.data);
      addMessageToChatList(msg);
    }
    if (event.eventName == 'SeatUpdatedEvent') {
      final seatData = jsonDecode(event.data);
      updateSeatState(seatData);
    }
    if (event.eventName == 'gift.received') {
      final giftData = jsonDecode(event.data);
      playGiftAnimation(giftData['animation_url']);
    }
  },
);
```

---

## 🔥 ৫. Firebase FCM পুশ নোটিফিকেশন ভেরিফিকেশন ও টেস্ট (Firebase Diagnostics)

### ৫.১ রেজিস্ট্রেশন ও লগইনে FCM টোকেন পাঠানো (Auto-Sync on Register & Login)
ইউজার যখন অ্যাপে রেজিস্টার বা লগইন করবে, তখন বডিতে `fcm_token` বা `device_token` পাঠালে তা স্বয়ংক্রিয়ভাবে `users` টেবিলে এবং `device_registrations` টেবিলে সেভ হয়ে যায়:
- **Payload (`POST /api/register` বা `POST /api/login`)**:
```json
{
  "email": "user@gmail.com",
  "password": "password123",
  "fcm_token": "fXyZ123456...device_token_from_firebase",
  "device_type": "android"
}
```

### ৫.২ ডিভাইস টোকেন আপডেট / রিফ্রেশ এপিআই (Sync FCM Token)
- **Method**: `POST`
- **URL**: `https://chinchins.live/api/update-fcm-token`
- **Body**:
```json
{
  "fcm_token": "fXyZ123456...device_token_from_firebase",
  "device_type": "android",
  "device_brand": "Samsung",
  "device_model": "Galaxy S23"
}
```

### ৫.৩ ফায়ারবেস কানেকশন স্ট্যাটাস চেক (Check Firebase Status)
- **Method**: `GET`
- **URL**: `https://chinchins.live/api/fcm/status`
- **Headers**:
  ```http
  Authorization: Bearer {token}
  Accept: application/json
  ```
- **Response**:
```json
{
  "status": true,
  "connected": true,
  "message": "Firebase FCM service is active and operational.",
  "diagnostic": {
    "firebase_configured": true,
    "active_firebase_apps": 1,
    "default_project_id": "chinchins-live",
    "package_name": "com.chinchins.live",
    "registered_devices": 145,
    "users_with_fcm_token": 128,
    "current_user": {
      "id": 101,
      "name": "Nazmul",
      "account_id": "84920183",
      "has_fcm_token": true,
      "fcm_token_preview": "fXyZ123456...",
      "device_type": "android"
    }
  }
}
```

### ৫.৪ ইনস্ট্যান্ট টেস্ট নোটিফিকেশন পাঠানো (Trigger Instant Test Push)
- **Method**: `POST`
- **URL**: `https://chinchins.live/api/fcm/test-push`
- **Headers**:
  ```http
  Authorization: Bearer {token}
  Accept: application/json
  ```
- **Body**:
```json
{
  "title": "🎉 ChinChins Live Test Push",
  "body": "Firebase Push Notification connected successfully! 🚀"
}
```
- **Response**:
```json
{
  "status": true,
  "message": "Test push notification dispatched.",
  "fcm_token": "fXyZ123456...",
  "title": "🎉 ChinChins Live Test Push",
  "body": "Firebase Push Notification connected successfully! 🚀",
  "result": {
    "status": true,
    "multicast_id": "89201823"
  }
}
```

---

## ⚡ ৬. জিরো-লেটেন্সি ও হাই-স্পিড এপিআই আর্কিটেকচার (Zero-Latency RESTful APIs)

> **আর্কিটেকচার লক্ষ্য:** রেসপন্স টাইম `< ২০–৩০ms`, নো-ডিবি হিট (২৪ ঘণ্টার Redis ক্যাশ), লাইভকিট ইন-মেমোরি টোকেন জেনারেশন এবং নন-ব্লকিং ব্যাকগ্রাউন্ড কিউ (Queue Worker)।

---

### ৬.১ অল-ইন-ওয়ান গ্লোবাল বুটস্ট্র্যাপ কনফিগ (All-In-One Bootstrap Config)
অ্যাপ ওপেন করার সময় মাত্র ১টি এপিআই কল দিয়ে পেমেন্ট মেথড, কয়েন প্যাকেজ, গিফট, লেভেল ব্যাজ, ভিআইপি ফ্রেম এবং অ্যাপ সেটিংস রিটার্ন হবে।
- **Method**: `GET`
- **URL**: `https://chinchins.live/api/bootstrap-config` (অ্যালিয়াস: `/api/app-config`, `/api/v1/bootstrap`)
- **Headers**:
  ```http
  Accept: application/json
  ```
- **Response**:
```json
{
  "success": true,
  "status": true,
  "message": "Global bootstrap configuration loaded from memory.",
  "timestamp": 1727179000,
  "data": {
    "payment_methods": [
      {
        "id": 1,
        "name": "bKash Personal",
        "code": "bkash",
        "account_type": "Personal",
        "account_number": "017XXXXXXXX",
        "icon_url": "https://chinchins.live/uploads/payment_methods/bkash.svg",
        "rate_coins": 1000,
        "bonus_coins": 100,
        "total_coins": 1100,
        "rate_bdt": 100.00
      }
    ],
    "coin_packages": [
      {
        "id": 1,
        "title": "500 Gems Pack",
        "coins": 500,
        "bonus_coins": 50,
        "total_coins": 550,
        "price": 50.00,
        "formatted_price": "৳50",
        "badge": "Hot Offer",
        "badge_color": "pink",
        "icon_url": "https://chinchins.live/uploads/coin_packages/gem_small.svg"
      }
    ],
    "gifts_catalog": [
      {
        "id": 31,
        "name": "Private Jet",
        "coins": 1200,
        "coin_price": 1200,
        "icon_url": "https://chinchins.live/uploads/gifts/icons/jet.svg",
        "animation_url": "https://chinchins.live/uploads/gifts/animations/jet.svga",
        "format": "svga",
        "display_type": "fullscreen",
        "category": "svip"
      }
    ],
    "level_badges": [
      {
        "level": 1,
        "name": "Charm Lv.1",
        "required_coins": 0,
        "icon_url": "https://chinchins.live/uploads/levels/lv1.svg",
        "badge_color": "#10b981"
      }
    ],
    "vip_frames": [
      {
        "id": 1,
        "name": "Crown VIP",
        "card_type": "vip_crown",
        "price": 500.00,
        "validity_days": 30,
        "avatar_frame_url": "https://chinchins.live/uploads/vip/frames/crown_frame.svga"
      }
    ],
    "app_settings": {
      "app_name": "ChinChins Live",
      "active_streaming_engine": "livekit",
      "active_calling_engine": "livekit",
      "livekit_ws_url": "wss://chinchins.live/livekit",
      "video_call_rate_default": 100,
      "audio_call_rate_default": 60,
      "free_call_duration": 30,
      "currency": "BDT",
      "currency_symbol": "৳"
    },
    "withdrawal_settings": {
      "is_withdraw_enabled": true,
      "min_withdraw_coins": 1000,
      "max_withdraw_coins": 100000,
      "commission_percent": 5.0,
      "rate_coins": 100,
      "rate_bdt": 10.0,
      "rate_per_bdt": 10.0
    }
  }
}
```

---

### ৬.২ উইথড্র পেমেন্ট মেথডস ড্রপডাউন এপিআই (Active Withdraw Methods)
- **Method**: `GET`
- **URL**: `https://chinchins.live/api/withdraw-methods` (অ্যালিয়াস: `/api/withdraw/methods`)
- **Headers**:
  ```http
  Accept: application/json
  ```
- **Response**:
```json
{
  "success": true,
  "status": true,
  "message": "Active withdrawal payment methods retrieved successfully.",
  "data": [
    {
      "id": 1,
      "name": "bKash Personal",
      "code": "bkash",
      "account_type": "Personal",
      "icon_url": "https://chinchins.live/uploads/payment_methods/bkash.svg",
      "min_withdraw": 50.0,
      "max_withdraw": 50000.0,
      "instructions": "Enter your 11-digit bKash personal mobile number."
    },
    {
      "id": 2,
      "name": "Nagad Personal",
      "code": "nagad",
      "account_type": "Personal",
      "icon_url": "https://chinchins.live/uploads/payment_methods/nagad.svg",
      "min_withdraw": 50.0,
      "max_withdraw": 50000.0,
      "instructions": "Enter your 11-digit Nagad personal mobile number."
    }
  ]
}
```

---

### ৬.৩ ইউজারের উইথড্র রিকোয়েস্ট সাবমিট এপিআই (Submit Withdraw Request)
ইউজার কয়েন পরিমাণ, পেমেন্ট মেথড এবং তার ফোন নাম্বার দিয়ে সাবমিট করবে। রিকোয়েস্ট সাবমিট করার সময় ব্যালেন্স পর্যাপ্ত কি না শুধু চেক হবে, কয়েন কাটবে না (`status = pending` থাকবে)। অ্যাডমিন প্যানেল থেকে Approve করার সাথে সাথে অটোমেটিক ইউজারের ওয়ালেট ও কয়েন ব্যালেন্স থেকে কয়েন মাইনাস হবে।
- **Method**: `POST`
- **URL**: `https://chinchins.live/api/withdraw/submit` (অ্যালিয়াস: `/api/withdraw`, `/api/withdraw-submit`)
- **Headers**:
  ```http
  Authorization: Bearer {token}
  Content-Type: application/json
  Accept: application/json
  ```
- **Body**:
```json
{
  "coins": 2000,
  "payment_method": "bkash",
  "account_number": "01712345678",
  "account_type": "Personal",
  "user_note": "Please process fast"
}
```
- **Response (Success - Pending Approval)**:
```json
{
  "status": true,
  "success": true,
  "message": "উইথড্র রিকোয়েস্ট সফলভাবে জমা হয়েছে। অ্যাডমিন অ্যাপ্রুভ করলে আপনার অ্যাকাউন্টে টাকা পাঠিয়ে কয়েন কাটা হবে।",
  "data": {
    "withdraw_id": 14,
    "coins": 2000,
    "formatted_coins": "2,000 Coins",
    "gross_amount": 200.0,
    "formatted_gross_amount": "৳200.00",
    "commission_percent": 5.0,
    "commission_amount": 10.0,
    "formatted_commission_amount": "৳10.00 (5%)",
    "net_payable_amount": 190.0,
    "formatted_net_payable_amount": "৳190.00",
    "payment_method": "bKash Personal",
    "account_number": "01712345678",
    "account_type": "Personal",
    "status": "pending",
    "user_current_coins": 5400
  }
}
```
- **Response (Error - Insufficient Balance)**:
```json
{
  "status": false,
  "success": false,
  "message": "পর্যাপ্ত ব্যালেন্স নেই!"
}
```

---

### ৬.৪ ইনস্ট্যান্ট ১-অন-১ অডিও/ভিডিও কল ও লাইভকিট টোকেন (Instant Call Token - < 5ms)
মেমোরিতে সরাসরি লাইভকিট টোকেন তৈরি করে সাথে সাথে রেসপন্স দেওয়া হয় এবং রিসিভারের ফোনে ব্যাকগ্রাউন্ড কিউতে পুশ নোটিফিকেশন পাঠানো হয়।
- **Method**: `POST`
- **URL**: `https://chinchins.live/api/call/instant` (অ্যালিয়াস: `/api/call/make-call`, `/api/make-instant-call`)
- **Headers**:
  ```http
  Authorization: Bearer {token}
  Content-Type: application/json
  Accept: application/json
  ```
- **Body**:
```json
{
  "receiver_id": 102,
  "call_type": "video",
  "room_name": "call_video_101_102_1727179000"
}
```
- **Response**:
```json
{
  "success": true,
  "status": true,
  "room_name": "call_video_101_102_1727179000",
  "channel_name": "call_video_101_102_1727179000",
  "token": "eyJhbGciOi...",
  "livekit_token": "eyJhbGciOi...",
  "livekit_url": "wss://chinchins.live/livekit",
  "call_id": 85,
  "call_type": "video"
}
```

---

### ৬.৫ জিরো-ল্যাগ গিফট সেন্ড ও রিয়েল-টাইম ব্রডকাস্ট (Send Gift with Atomic Deduct)
অ্যাটমিক ব্যালেন্স চেক ও ডিডাক্ট, হোস্ট ওয়ালেট ক্রেডিট এবং `toOthers()` দিয়ে সবার স্ক্রিনে রিয়েল-টাইম গিফট অ্যানিমেশন ব্রডকাস্ট।
- **Method**: `POST`
- **URL**: `https://chinchins.live/api/gifts/send` (অ্যালিয়াস: `/api/gift/send`)
- **Headers**:
  ```http
  Authorization: Bearer {token}
  Content-Type: application/json
  Accept: application/json
  ```
- **Body**:
```json
{
  "gift_id": 31,
  "receiver_id": 102,
  "room_name": "stream_102",
  "quantity": 1
}
```
- **Response**:
```json
{
  "success": true,
  "status": true,
  "message": "Gift sent successfully!",
  "remaining_coins": 4200,
  "remaining_balance": 4200,
  "data": {
    "remaining_coins": 4200,
    "remaining_balance": 4200,
    "gift_id": 31,
    "gift_name": "Private Jet",
    "total_coins": 1200,
    "icon_url": "https://chinchins.live/uploads/gifts/icons/jet.svg",
    "animation_url": "https://chinchins.live/uploads/gifts/animations/jet.svga"
  }
}
```

---

### ৬.৬ অ্যাক্টিভ গিফটস ক্যাটালগ এপিআই (Active Gifts Catalog - 24hr Cache)
- **Method**: `GET`
- **URL**: `https://chinchins.live/api/gifts` (অ্যালিয়াস: `/api/gifts/catalog`)
- **Headers**:
  ```http
  Accept: application/json
  ```
- **Response**:
```json
{
  "success": true,
  "status": true,
  "message": "Active gifts catalog loaded from cache.",
  "data": [
    {
      "id": 31,
      "name": "Private Jet",
      "coins": 1200,
      "coin_price": 1200,
      "icon_url": "https://chinchins.live/uploads/gifts/icons/jet.svg",
      "image_url": "https://chinchins.live/uploads/gifts/icons/jet.svg",
      "animation_url": "https://chinchins.live/uploads/gifts/animations/jet.svga",
      "format": "svga",
      "display_type": "fullscreen",
      "category": "svip"
    }
  ]
}
```

---

### ৬.৭ প্রোফাইল ভিউ এপিআই (Profile View - Auto-Call Disabled)
> **গুরুত্বপূর্ণ আপডেট:** কোনো ব্যবহারকারী বা হোস্টের প্রোফাইল ভিউ করলে কোনো অটোমেটিক ইনকামিং কল ট্রিগার হবে না (`auto_call_triggered: false` এবং `trigger_action: "NONE"` থাকবে)। শুধুমাত্র ভিজিটর হিস্ট্রি ও হোস্ট নোটিফিকেশন রেকর্ড হবে।

- **Method**: `POST`
- **URL**: `https://chinchins.live/api/profile/{id}/view` (অ্যালিয়াস: `/api/profile/view`, `/api/user/view-profile`)
- **Headers**:
  ```http
  Authorization: Bearer {token}
  Content-Type: application/json
  Accept: application/json
  ```
- **Body** (ঐচ্ছিক যদি URL প্যারামিটারে ID থাকে):
  ```json
  {
    "host_id": 102
  }
  ```
- **Response**:
```json
{
  "status": true,
  "message": "Profile view recorded successfully. Automatic call is disabled.",
  "data": {
    "host": {
      "id": 102,
      "account_id": "83749201",
      "display_name": "Ayesha Khan",
      "avatar_url": "https://chinchins.live/uploads/avatars/host102.jpg",
      "is_online": true,
      "is_busy": false,
      "is_available": true,
      "video_call_rate": 100,
      "country": "Bangladesh",
      "level": "Lv3"
    },
    "notification": {
      "id": 842,
      "receiver_id": 102,
      "type": "profile_view",
      "title": "New Profile Visitor 👁️",
      "message": "Nazmul viewed your profile!"
    },
    "callback": {
      "auto_call_triggered": false,
      "host_is_available": true,
      "is_busy": false,
      "viewer_can_receive": true,
      "required_coins": 100,
      "viewer_coins": 5000,
      "trigger_action": "NONE"
    },
    "auto_message": {
      "id": 1290,
      "sender_id": 102,
      "message": "Hi Nazmul! Thanks for visiting my profile ❤️",
      "type": "text",
      "time": "Just now"
    }
  }
}
```



