# 📑 Live Streaming & 1-on-1 Video Calling System Architecture & Problem Solving
**Target Roles:** Full-Stack Engineers (Laravel Backend & Flutter Frontend)  
**System Scope:** 1-on-1 Personal Video Calling & In-Call Real-Time Chat, Multi-User Live Streaming (Viewer Mode), Multi-Host Co-Hosting (Max 4-5 Persons Video Grid), WebRTC Signaling & Laravel Reverb Real-Time Pipeline, FinTech-Grade Coin & Gift Engine  
**Backend:** Laravel 11.x RESTful Backend & WebSocket Server (Reverb/Pusher)  
**Mobile Client:** Flutter (Android & iOS) with `flutter_webrtc` and `laravel_echo`  
**Version:** 7.0.0 Enterprise Live Edition  
**Updated:** September 14, 2026  
**Document Name:** `problem_solving_restful_api.md`

---

## 📑 Table of Contents
1. [১. সিস্টেম ওভারভিউ (System Overview)](#১-সিস্টেম-ওভারভিউ-system-overview)
2. [২. লারাভেল ব্যাকএন্ড স্পেসিফিকেশন (Laravel Backend Spec)](#২-লারাভেল-ব্যাকএন্ড-স্পেসিফিকেশন-laravel-backend-spec)
   - [২.১ WebRTC Signaling & Reverb Channels (`routes/channels.php`)](#২১-webrtc-signaling--reverb-channels-routeschannelsphp)
   - [২.২ ব্রডকাস্ট ইভেন্টস (Broadcast Events)](#২২-ব্রডকাস্ট-ইভেন্টস-broadcast-events)
   - [২.৩ সম্পূর্ণ RESTful APIs রেফারেন্স](#২৩-সম্পূর্ণ-restful-apis-রেফারেন্স)
3. [৩. ফ্লাটার ফ্রন্টএন্ড স্পেসিফিকেশন (Flutter Frontend Spec)](#৩-ফ্লাটার-ফ্রন্টএন্ড-স্পেসিফিকেশন-flutter-frontend-spec)
   - [৩.১ ডিপেনডেন্সি (Dependencies)](#৩১-ডিপেনডেন্সি-dependencies)
   - [৩.২ ফিচার ১: ১-অন-১ পার্সোনাল ভিডিও কল ও রিয়েল-টাইম চ্যাট](#৩২-ফিচার-১-১-অন-১-পার্সোনাল-ভিডিও-কল-ও-রিয়েল-টাইম-চ্যাট)
   - [৩.৩ ফিচার ২: লাইভ স্ট্রিমিং ও মাল্টি-হোস্ট গ্রিড (সর্বোচ্চ ৪-৫ জন)](#৩৩-ফিচার-২-লাইভ-স্ট্রিমিং-ও-মাল্টি-হোস্ট-গ্রিড-সর্বোচ্চ-৪-৫-জন)
4. [৪. ডেভেলপারদের কাজের চেকলিস্ট (Developer Checklist)](#৪-ডেভেলপারদের-কাজের-চেকলিস্ট-developer-checklist)
5. [৫. প্রডাকশন ডেপ্লয়মেন্ট ও কমান্ডস (Production Deployment Commands)](#৫-প্রডাকশন-ডেপ্লয়মেন্ট-ও-কমান্ডস-production-deployment-commands)

---

## ১. সিস্টেম ওভারভিউ (System Overview)

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                                 FLUTTER CLIENT                                         │
│                                                                                        │
│  ┌───────────────────────────┐  ┌───────────────────────────┐  ┌────────────────────┐  │
│  │ 1-on-1 Video Call + Chat  │  │ Live Room (Viewer Mode)   │  │ Co-Host 5-Grid UI  │  │
│  └─────────────┬─────────────┘  └─────────────┬─────────────┘  └──────────┬─────────┘  │
└────────────────┼──────────────────────────────┼───────────────────────────┼────────────┘
                 │                              │                           │
      HTTP / REST (Bearer Token)                │                WebSocket (Presence/Private)
                 │                              │                           │
                 ▼                              ▼                           ▼
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                                LARAVEL REVERB & BACKEND                                │
│                                                                                        │
│  ┌──────────────────────────────────────────────────────────────────────────────────┐  │
│  │ Private Channel: `call.{callId}` -> CallSignalingEvent & CallMessageEvent        │  │
│  │ Presence Channel: `live-stream.{streamId}` -> LiveChatMessageEvent & Signaling   │  │
│  └──────────────────────────────────────────────────────────────────────────────────┘  │
│                                                                                        │
│                     ┌────────────────────────────────────────┐                         │
│                     │  Pessimistic Row Lock Coin Deduction   │                         │
│                     │  Max 5 Co-Hosts Grid Gate Validation   │                         │
│                     └────────────────────────────────────────┘                         │
└────────────────────────────────────────────────────────────────────────────────────────┘
```

1. **1-on-1 Personal Call:** ব্যবহারকারী কাউকে কল দিলে সরাসরি কল যাবে। কথা ও ভিডিও আদান-প্রদান হবে এবং কল চলাকালীন রিয়েল-টাইম টেক্সট ও ছবি সম্পূর্ণ ফ্রিতে আদান-প্রদান করা যাবে (Bidirectional Real-Time Chat & Photo Sharing)।
2. **Live Streaming (Viewer Mode):** লাইভ অপশনে ক্লিক করলে যে হোস্ট লাইভে আছে, তার লাইভ ভিডিও ও অডিও ভিউয়ার তৎক্ষণাৎ দেখতে ও শুনতে পাবে। হাজার হাজার ভিউয়ার ফ্রিতে লাইভ চ্যাটে রিয়েল-টাইম কমেন্ট করতে পারবে এবং ভার্চুয়াল গিফট পাঠাতে পারবে।
3. **Co-Host / Multi-Host Mode (Max 4-5 Persons):** হোস্ট চাইলে কোনো ভিউয়ারকে (বা গিফট পাঠানো ব্যক্তিকে) কো-হোস্ট হিসেবে লাইভে যুক্ত করতে পারবে। যুক্ত হলে তাদের সবার ভিডিও ও অডিও স্ক্রিনে গ্রিড আকারে স্প্লিট হয়ে লাইভে যুক্ত সকল ইউজারের কাছে ব্রডকাস্ট হবে।

---

## ২. লারাভেল ব্যাকএন্ড স্পেসিফিকেশন (Laravel Backend Spec)

### ২.১ WebRTC Signaling & Reverb Channels (`routes/channels.php`)

```php
use Illuminate\Support\Facades\Broadcast;

// ১. ১-অন-১ পার্সোনাল কল ও চ্যাট চ্যানেল
Broadcast::channel('call.{callId}', function ($user, $callId) {
    return true; // ভ্যালিডেশন লজিক (কলার এবং রিসিভার এলাও হবে)
});

// ২. লাইভ স্ট্রিমিং প্রেজেন্স চ্যানেল (ভিউয়ার লিস্ট, লাইভ চ্যাট ও কো-হোস্ট সিগন্যালিং)
Broadcast::channel('live-stream.{streamId}', function ($user, $streamId) {
    if (!$user) return true;
    return [
        'id'           => $user->id,
        'account_id'   => $user->account_id,
        'name'         => $user->display_name ?? $user->name,
        'display_name' => $user->display_name ?? $user->name,
        'avatar'       => $user->avatar_url,
        'avatar_url'   => $user->avatar_url,
        'level'        => $user->level ?: 'Lv1',
        'role'         => ($user->is_host || (string)$user->id === (string)$streamId) ? 'host' : 'viewer',
    ];
});
```

---

### ২.২ ব্রডকাস্ট ইভেন্টস (Broadcast Events)

| Event Class | Channel Type & Name | Client Event Name | Payload Structure |
|---|---|---|---|
| `CallSignalingEvent` | Private: `call.{callId}` | `CallSignalingEvent` | `{ call_id, sender_id, receiver_id, type: "offer"\|"answer"\|"candidate"\|"bye", payload: sdp_or_candidate, timestamp }` |
| `CallMessageEvent` | Private: `call.{callId}` | `CallMessageEvent` | `{ call_id, sender_id, sender_name, sender_avatar, receiver_id, message, media_url, image_url, type: "text"\|"image", timestamp }` |
| `LiveChatMessageEvent` | Presence: `live-stream.{streamId}` | `LiveChatMessageEvent` | `{ stream_id, user_id, user_name, user_avatar, message, type: "text", level: "Lv3", timestamp }` |
| `StreamSignalingEvent` | Presence: `live-stream.{streamId}` | `StreamSignalingEvent` | `{ stream_id, sender_id, target_user_id, type: "offer"\|"answer"\|"candidate", sdp_or_candidate, timestamp }` |
| `CoHostStatusEvent` | Presence: `live-stream.{streamId}` | `CoHostStatusEvent` | `{ stream_id, action: "invited"\|"accepted"\|"rejected"\|"removed", user_id, user_name, user_avatar, co_hosts_count, max_limit: 5, timestamp }` |
| `LiveGiftSentEvent` | Presence: `live-stream.{streamId}` | `LiveGiftSentEvent` | `{ stream_id, sender_id, sender_name, sender_avatar, gift_id, gift_name, animation_url, total_coins, timestamp }` |

---

### ২.৩ সম্পূর্ণ RESTful APIs রেফারেন্স

| Method | Endpoint | Description | Request Body Example |
|---|---|---|---|
| `POST` | `/api/v1/call/initiate` | নতুন কল রিকোয়েস্ট তৈরি করে ও রিসিভারকে পুশ পাঠায় | `{"receiver_id": 105, "call_type": "video"}` |
| `POST` | `/api/v1/call/signal` | Reverb-এর মাধ্যমে SDP ও ICE Candidate পাঠায় | `{"call_id": 482, "receiver_id": 105, "type": "offer", "payload": {"sdp": "..."}}` |
| `POST` | `/api/v1/call/send-message` | কলের ভেতর মেসেজ ও ছবি পাঠায় (১০০% ফ্রি) | `{"call_id": 482, "receiver_id": 105, "message": "Hi handsome!", "image_url": "https://..."}` |
| `POST` | `/api/v1/call/end` | কল সেশন সমাপ্ত করে ও সারাংশ চ্যাটে পাঠায় | `{"call_id": 482, "duration_seconds": 185}` |
| `POST` | `/api/v1/stream/start` | হোস্ট লাইভ শুরু করে (stream_id জেনারেট হয়) | `{"title": "Evening Live Chat", "cover_image": "https://..."}` |
| `POST` | `/api/v1/stream/end` | লাইভ সেশন সমাপ্ত ঘোষণা করে | `{"stream_id": 12}` |
| `POST` | `/api/v1/stream/comment` | লাইভে পাবলিক মেসেজ পাঠায় (১০০% ফ্রি) | `{"stream_id": 12, "message": "Love from Dhaka ❤️"}` |
| `POST` | `/api/v1/stream/invite-cohost` | হোস্ট ভিউয়ারকে কো-হোস্টের জন্য ইনভাইট পাঠায় | `{"stream_id": 12, "user_id": 105}` |
| `POST` | `/api/v1/stream/accept-cohost` | ভিউয়ার এক্সেপ্ট করে (সর্বোচ্চ ৫ জন চেক করে) | `{"stream_id": 12}` |
| `POST` | `/api/v1/stream/signal` | কো-হোস্টদের মধ্যে WebRTC হ্যান্ডশেক সিগন্যাল পাঠায় | `{"stream_id": 12, "target_user_id": 105, "type": "offer", "sdp_or_candidate": "..."}` |
| `POST` | `/api/v1/stream/send-gift` | কয়েন কেটে হোস্টকে ৫০% দেয় এবং অ্যানিমেশন পাঠায় | `{"stream_id": 12, "gift_id": 5, "quantity": 1}` |
| `GET` | `/api/v1/stream/active-streams`| বর্তমানে চলমান সব লাইভ স্ট্রিমের তালিকা পায় | `Query: ?page=1&per_page=20` |

---

## ৩. ফ্লাটার ফ্রন্টএন্ড স্পেসিফিকেশন (Flutter Frontend Spec)

### ৩.১ ডিপেনডেন্সি (Dependencies)
- `flutter_webrtc: ^0.10.0+` (ভিডিও ও অডিও স্ট্রিমের জন্য)
- `laravel_echo: ^1.0.0` / `pusher_client: ^2.0.0` (Laravel Reverb রিয়েল-টাইম কানেকশনের জন্য)

---

### ৩.২ ফিচার ১: ১-অন-১ পার্সোনাল ভিডিও কল ও রিয়েল-টাইম চ্যাট

#### ১. কল শুরু ও কানেকশন:
- কলার ভিডিও কল বাটনে চাপ দিলে `POST /api/v1/call/initiate` কল হবে এবং লোকাল ক্যামেরা স্ট্রিম অন হবে:
```dart
MediaStream localStream = await navigator.mediaDevices.getUserMedia({
  'audio': true,
  'video': {'facingMode': 'user'},
});
_localRenderer.srcObject = localStream;
```
- রিসিভার কল রিসিভ করলে Reverb চ্যানেল `call.{callId}` দিয়ে `CallSignalingEvent`-এর মাধ্যমে WebRTC SDP ও ICE ক্যান্ডিডেট আদান-প্রদান হবে।
- রিমোট ট্র্যাক পাওয়া মাত্র রেন্ডারারে সেট করতে হবে:
```dart
_peerConnection.onTrack = (RTCTrackEvent event) {
  if (event.streams.isNotEmpty) {
    setState(() {
      _remoteRenderer.srcObject = event.streams[0];
    });
  }
};
```

#### ২. ইন-কল চ্যাট ও ফটো শেয়ারিং (১০০% ফ্রি):
- **মেসেজ সেন্ড করার সময়:**
```dart
// টেক্সট বা আপলোড করা ছবির URL API-তে পাঠানো হবে
await http.post(
  Uri.parse('$baseUrl/api/v1/call/send-message'),
  headers: {'Authorization': 'Bearer $authToken'},
  body: {
    'call_id': callId.toString(),
    'receiver_id': targetUserId.toString(),
    'message': messageController.text,
    'image_url': uploadedImageUrl,
    'type': uploadedImageUrl != null ? 'image' : 'text',
  },
);
```

- **মেসেজ রিসিভ করার সময় (রিয়েল-টাইম স্ক্রিনে প্রদর্শন):**
```dart
echo.private('call.$callId')
    .listen('.CallMessageEvent', (dynamic data) {
      setState(() {
        inCallMessagesList.add(CallMessageModel.fromJson(data));
      });
      _chatScrollController.animateTo(
        _chatScrollController.position.maxScrollExtent,
        duration: const Duration(milliseconds: 250),
        curve: Curves.easeOut,
      );
    });
```

---

### ৩.৩ ফিচার ২: লাইভ স্ট্রিমিং ও মাল্টি-হোস্ট গ্রিড (সর্বোচ্চ ৪-৫ জন)

#### ১. ভিউয়ার মোড (হোস্টের ভিডিও ও অডিও প্লেব্যাক):
- যখন একজন ভিউয়ার লাইভ রুমে প্রবেশ করবে:
  1. সে `live-stream.{streamId}` প্রেজেন্স চ্যানেলে সাবস্ক্রাইব করবে।
  2. হোস্ট ভিউয়ারকে রিসিভার মোডে WebRTC Offer পাঠাবে।
  3. ভিউয়ার হ্যান্ডশেক সম্পন্ন করে হোস্টের অডিও/ভিডিও রেন্ডারারে বাইন্ড করবে:
```dart
_remoteLiveRenderer.srcObject = event.streams[0];
```
*(ভিউয়ারের ক্যামেরা ও মাইক তখন সম্পূর্ণ বন্ধ থাকবে)*

#### ২. লাইভ পাবলিক চ্যাট ও অটো-স্ক্রল:
```dart
echo.join('live-stream.$streamId')
    .listen('.LiveChatMessageEvent', (dynamic data) {
      setState(() {
        liveComments.add(LiveCommentModel.fromJson(data));
      });
      _chatScrollController.animateTo(
        _chatScrollController.position.maxScrollExtent,
        duration: const Duration(milliseconds: 250),
        curve: Curves.easeOut,
      );
    });
```

#### ৩. কো-হোস্ট জয়েনিং (মাল্টি-ভিডিও স্প্লিট গ্রিড - সর্বোচ্চ ৪-৫ জন):
- হোস্ট ভিউয়ারকে ইনভাইট পাঠালে বা ভিউয়ার রিকোয়েস্ট এক্সেপ্ট হলে:
  1. কো-হোস্টের ডিভাইসের ক্যামেরা ও মাইক্রোফোন ওপেন হবে (`navigator.mediaDevices.getUserMedia`).
  2. কো-হোস্ট সক্রিয় হোস্টদের সাথে Peer Connection তৈরি করবে।
  3. ফ্লাটার স্ক্রিনে UI ডায়নামিক স্প্লিট গ্রিডে রেন্ডার হবে:

```dart
// সর্বোচ্চ ৫ জনের জন্য Map-এ রেন্ডারার রাখা
Map<String, RTCVideoRenderer> coHostRenderers = {};

// UI Grid View (Dynamic Split Screen for 1, 2, 4, or 5 Users)
GridView.builder(
  physics: const NeverScrollableScrollPhysics(),
  gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
    crossAxisCount: coHostRenderers.length > 2 ? 2 : 1,
    childAspectRatio: coHostRenderers.length == 1 ? 9 / 16 : 1.0,
    crossAxisSpacing: 4.0,
    mainAxisSpacing: 4.0,
  ),
  itemCount: coHostRenderers.length,
  itemBuilder: (context, index) {
    var renderer = coHostRenderers.values.elementAt(index);
    return ClipRRect(
      borderRadius: BorderRadius.circular(12),
      child: RTCVideoView(
        renderer,
        objectFit: RTCVideoViewObjectFit.RTCVideoViewObjectFitCover,
      ),
    );
  },
);
```

---

## ৪. ডেভেলপারদের কাজের চেকলিস্ট (Developer Checklist)

### 💻 Laravel Backend Developer:
- [x] Laravel Reverb সার্ভিস রানিং এবং `routes/channels.php`-তে `call.{callId}` ও `live-stream.{streamId}` কনফিগার করা হয়েছে।
- [x] `CallSignalingEvent`, `CallMessageEvent`, `LiveChatMessageEvent`, `StreamSignalingEvent`, `CoHostStatusEvent` ইভেন্ট তৈরি ও ডিসপ্যাচ করা হয়েছে।
- [x] কলের ভেতর মেসেজ ও ফটো আপলোডের ১০০% ফ্রি এন্ডপয়েন্ট (`POST /api/v1/call/send-message`) তৈরি করা হয়েছে।
- [x] লাইভ স্ট্রিমিংয়ে সর্বোচ্চ ৫ জন কো-হোস্টের মাল্টি-হোস্ট ভ্যালিডেশন ফিল্টার যোগ করা হয়েছে।
- [x] লাইভে গিফট সেন্ড করলে হোস্টের সাথে ৫০% রেভিনিউ শেয়ারিং এবং লাইভ মেসেজে অ্যানিমেশন ব্রডকাস্ট করা হয়েছে।

### 📱 Flutter Frontend Developer:
- [x] কল কানেক্ট হওয়ার পর `remoteRenderer.srcObject` যেন `null` না থাকে এবং `onTrack`-এ স্টেট রিফ্রেশ করা হয়েছে।
- [x] কলের ব্যাকগ্রাউন্ডে Reverb চ্যানেলে লিসেন করে চ্যাট ও ইমেজ উভয় ইউজারের স্ক্রিনে ইনস্ট্যান্ট ডিসপ্লে করা হয়েছে।
- [x] লাইভ স্ক্রিনে ভিউয়ার মোডে ঢোকার সাথে সাথে হোস্টের অডিও/ভিডিও প্লে হচ্ছে (শুধু অ্যাভাটার শো করে আটকে থাকছে না)।
- [x] লাইভ চ্যাট বক্সে মেসেজ টাইপ করলে তা সবার স্ক্রিনে রিয়েল-টাইমে লাইভ চ্যাট লিস্টে স্ক্রল হচ্ছে।
- [x] কো-হোস্ট অ্যাকসেপ্ট করার পর ভিউয়ারের ক্যামেরা ওপেন হয়ে স্ক্রিনে স্প্লিট গ্রিড (সর্বোচ্চ ৪-৫ জন) তৈরি হচ্ছে।

---

## ৫. প্রডাকশন ডেপ্লয়মেন্ট ও কমান্ডস (Production Deployment Commands)

প্রডাকশন VPS সার্ভারে (`/var/www/chinchins-live-website`) কোড আপডেট করার জন্য নিচের কমান্ডগুলো রান করুন:

```bash
cd /var/www/chinchins-live-website
git pull origin main
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear
php artisan optimize
chmod -R 775 public/assets public/uploads storage bootstrap/cache
chown -R www-data:www-data public/assets public/uploads storage bootstrap/cache
```
