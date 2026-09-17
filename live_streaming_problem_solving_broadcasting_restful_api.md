# 🔴 Live Streaming Problem Solving Broadcasting RESTful API & In-Call Real-Time Documentation
**System:** Chinchins Live Streaming, Multi-Host Video Engine, 1-on-1 WebRTC Video Calls & In-Call Real-Time Chat & Gifting  
**Backend:** Laravel 12.x RESTful Backend + Laravel Reverb WebSocket Server + Agora RTC / Coturn STUN/TURN  
**Client:** Flutter (Android & iOS) with `flutter_webrtc`, `laravel_echo` / `pusher_client` & `svgaplayer_flutter`  
**Version:** 10.0.0 Production Release  
**Document File:** `live_streaming_problem_solving_broadcasting_restful_api.md`  

---

## 📑 সূচিপত্র (Table of Contents)
1. [১. সিস্টেম আর্কিটেকচার ও রিয়েল-টাইম ইঞ্জিন](#১-সিস্টেম-আর্কিটেকচার-ও-রিয়েল-টাইম-ইঞ্জিন)
2. [২. ডেটাবেজ স্কিমা ও মাইগ্রেশনস (Database Schema & Migrations)](#২-ডেটাবেজ-স্কিমা-ও-মাইগ্রেশনস)
3. [৩. Laravel Reverb ব্রডকাস্ট ইভেন্টস ও চ্যানেল অথেনটিকেশন](#৩-laravel-reverb-ব্রডকাস্ট-ইভেন্টস-ও-চ্যানেল-অথেনটিকেশন)
4. [৪. ইন-কল ও ১-অন-১ ভিডিও চ্যাট API এন্ডপয়েন্ট](#৪-ইন-কল-ও-১-অন-১-ভিডিও-চ্যাট-api-এন্ডপয়েন্ট)
   - [৪.১ ইন-কল মেসেজ সেন্ড API (`POST /api/v1/call/message/send`)](#৪১-ইন-কল-মেসেজ-সেন্ড-api)
   - [৪.২ ইন-কল গিফট সেন্ড ও ওয়ালেট ব্যালেন্স API (`POST /api/v1/call/gift/send`)](#৪২-ইন-কল-গিফট-সেন্ড-ও-ওয়ালেট-ব্যালেন্স-api)
   - [৪.৩ ডাইনামিক কুইক প্রম্পট মেসেজ API (`GET /api/v1/call/quick-messages`)](#৪৩-ডাইনামিক-কুইক-প্রম্পট-মেসেজ-api)
   - [৪.৪ রিসিভার প্রোফাইলে গিফট হিস্ট্রি API (`GET /api/v1/user/received-gifts`)](#৪৪-রিসিভার-প্রোফাইলে-গিফট-হিস্ট্রি-api)
5. [৫. লাইভ স্ট্রিমিং ও ব্রডকাস্টিং লাইফসাইকেল (Live Streaming Engine)](#৫-লাইভ-স্ট্রিমিং-ও-ব্রডকাস্টিং-লাইফসাইকেল)
   - [৫.১ Go to Live - ব্রডকাস্ট শুরু (`POST /api/live/start` & `POST /api/v1/stream/start`)](#৫১-go-to-live---ব্রডকাস্ট-শুরু)
   - [৫.২ একটিভ লাইভ স্ট্রিমস ফিড (`GET /api/lives/active` & `GET /api/live/list`)](#৫২-একটিভ-লাইভ-স্ট্রিমস-ফিড)
   - [৫.৩ লাইভ স্ট্রিমে জয়েন ও লিভ (`POST /api/live/join` & `POST /api/live/leave`)](#৫৩-লাইভ-স্ট্রিমে-জয়েন-ও-লিভ)
   - [৫.৪ আনলিমিটেড লাইভ চ্যাট মেসেজ (`POST /api/live/send-message`)](#৫৪-আনলিমিটেড-লাইভ-চ্যাট-মেসেজ)
   - [৫.৫ লাইভ স্ট্রিমে গিফট সেন্ডিং ও রেভিনিউ স্প্লিট (`POST /api/live/send-gift`)](#৫৫-লাইভ-স্ট্রিমে-গিফট-সেন্ডিং-ও-রেভিনিউ-স্প্লিট)
   - [৫.৬ ৪-৫ জন কো-হোস্ট গ্রিড ও সিগনালিং হ্যান্ডশেক](#৫৬-৪-৫-জন-কো-হোস্ট-গ্রিড-ও-সিগনালিং-হ্যান্ডশেক)
6. [৬. Flutter মোবাইল ক্লায়েন্ট ইন্টিগ্রেশন ও হ্যান্ডওভার গাইড](#৬-flutter-মোবাইল-ক্লায়েন্ট-ইন্টিগ্রেশন-ও-হ্যান্ডওভার-গাইড)

---

## ১. সিস্টেম আর্কিটেকচার ও রিয়েল-টাইম ইঞ্জিন

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                                 FLUTTER MOBILE CLIENT                                  │
│                                                                                        │
│  ┌───────────────────────────┐  ┌───────────────────────────┐  ┌────────────────────┐  │
│  │ 1-on-1 Video Call & Chat  │  │ Live Room (Viewer Mode)   │  │ 4-5 Multi-Host Grid│  │
│  └─────────────┬─────────────┘  └─────────────┬─────────────┘  └──────────┬─────────┘  │
└────────────────┼──────────────────────────────┼───────────────────────────┼────────────┘
                 │                              │                           │
      HTTP / REST (Bearer Token)                │                WebSocket (Reverb Client)
                 │                              │                           │
                 ▼                              ▼                           ▼
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                         LARAVEL 12 & REVERB WEBSOCKET SERVER                           │
│                                                                                        │
│  - WebSocket Port: 8080 (WS) / 443 (WSS)                                               │
│  - Call Private Channel: `call.{sessionId}` & `chat.{userId}`                          │
│  - Live Broadcast Channel: `live-room.{roomId}` & `live-stream.{streamId}`             │
│  - Instant Execution: ShouldBroadcastNow (Zero Queue Lag)                              │
│  - 50/50 Revenue Split Billing Engine & Diamond Wallet                                 │
└────────────────────────────────────────────────────────────────────────────────────────┘
                                 │
                                 ▼
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                        RTC STREAMING & SIGNALING DRIVERS                               │
│  - Dual-Engine: Agora RTC Cloud Engine + VPS Coturn STUN/TURN                          │
│  - STUN/TURN IP: 2.25.131.55:3478                                                      │
│  - WebRTC Peer-to-Peer Mesh Connection for Video Calling & Grid Hosting                │
└────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## ২. ডেটাবেজ স্কিমা ও মাইগ্রেশনস

মেসেজ যেন সরাসরি মেসেঞ্জারে স্টোর হয় এবং লাইভ কল ও ইনবক্স উভয় জায়গায় কমন থাকে:

```php
// 1. messages table (ইনবক্স এবং লাইভ কল দুই জায়গার জন্যই কমন)
Schema::create('messages', function (Blueprint $table) {
    $table->id();
    $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
    $table->foreignId('receiver_id')->nullable()->constrained('users')->onDelete('cascade');
    $table->string('call_session_id')->nullable()->index(); // কল সেশন আইডি
    $table->text('message');
    $table->string('type')->default('text'); // text, quick_reply, image
    $table->boolean('is_read')->default(false);
    $table->timestamps();
});

// 2. user_gifts table (প্রোফাইলে গিফট হিস্ট্রি শো করার জন্য)
Schema::create('user_gifts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('sender_id')->nullable()->constrained('users')->onDelete('cascade');
    $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
    $table->foreignId('gift_id')->constrained('gifts')->onDelete('cascade');
    $table->string('call_session_id')->nullable()->index();
    $table->integer('coin_amount')->default(0);
    $table->timestamps();
});

// 3. users table (ব্যালেন্স ও রিসিভড কয়েনস ট্র্যাক)
Schema::table('users', function (Blueprint $table) {
    $table->unsignedBigInteger('wallet_balance')->default(0);
    $table->unsignedBigInteger('received_coins')->default(0);
});
```

---

## ৩. Laravel Reverb ব্রডকাস্ট ইভেন্টস ও চ্যানেল অথেনটিকেশন

### ক) `app/Events/MessageSentEvent.php`
- **ইমপ্লিমেন্টেশন**: `ShouldBroadcastNow`
- **চ্যানেল**: `call.{call_session_id}`, `chat.{receiver_id}`
- **ইভেন্ট নেম**: `message.sent`

```php
namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSentEvent implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message->loadMissing(['sender:id,account_id,name,nickname,avatar']);
    }

    public function broadcastOn()
    {
        $channels = [];
        if (!empty($this->message->call_session_id)) {
            $channels[] = new PrivateChannel('call.' . $this->message->call_session_id);
            $channels[] = new PrivateChannel('call_chat.' . $this->message->call_session_id);
        }
        if (!empty($this->message->receiver_id)) {
            $channels[] = new PrivateChannel('chat.' . $this->message->receiver_id);
            $channels[] = new PrivateChannel('user-chat.' . $this->message->receiver_id);
        }
        if (!empty($this->message->conversation_id)) {
            $channels[] = new PrivateChannel('conversation.' . $this->message->conversation_id);
        }
        return $channels;
    }

    public function broadcastAs()
    {
        return 'message.sent';
    }
}
```

### খ) `app/Events/GiftSentEvent.php`
- **ইমপ্লিমেন্টেশন**: `ShouldBroadcastNow`
- **চ্যানেল**: `call.{call_session_id}`
- **ইভেন্ট নেম**: `gift.received`

```php
namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GiftSentEvent implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public $giftData;

    public function __construct($giftData)
    {
        $this->giftData = $giftData;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('call.' . $this->giftData['call_session_id']);
    }

    public function broadcastAs()
    {
        return 'gift.received';
    }
}
```

### গ) চ্যানেল অথেনটিকেশন (`routes/channels.php`)
```php
Broadcast::channel('call.{sessionId}', function ($user, $sessionId) {
    return true; 
});

Broadcast::channel('chat.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
```

---

## ৪. ইন-কল ও ১-অন-১ ভিডিও চ্যাট API এন্ডপয়েন্ট

### ৪.১ ইন-কল মেসেজ সেন্ড API
- **Endpoint**: `POST /api/v1/call/message/send` (এবং `/api/call/message/send`)
- **Headers**: `Authorization: Bearer <token>`, `Accept: application/json`
- **Request Body**:
```json
{
  "receiver_id": 12,
  "call_session_id": "CALL_SESSION_UUID_12345",
  "message": "Hi, what's up babe?"
}
```
- **Response (200 OK)**:
```json
{
  "status": true,
  "message": "Message sent successfully",
  "data": {
    "id": 1,
    "sender_id": 1,
    "receiver_id": 12,
    "call_session_id": "CALL_SESSION_UUID_12345",
    "message": "Hi, what's up babe?",
    "type": "text",
    "is_read": false,
    "created_at": "2026-09-17T10:38:10.000000Z",
    "sender": {
      "id": 1,
      "account_id": "1000000001",
      "name": "Super Admin",
      "avatar_url": "https://ui-avatars.com/api/?name=Admin"
    }
  }
}
```

---

### ৪.২ ইন-কল গিফট সেন্ড ও ওয়ালেট ব্যালেন্স API
- **Endpoint**: `POST /api/v1/call/gift/send` (এবং `/api/call/gift/send`)
- **Headers**: `Authorization: Bearer <token>`, `Accept: application/json`
- **Request Body**:
```json
{
  "receiver_id": 12,
  "gift_id": 5,
  "call_session_id": "CALL_SESSION_UUID_12345"
}
```

- **ব্যালেন্স না থাকলে Response (422 Unprocessable Entity)**:
```json
{
  "status": false,
  "code": "INSUFFICIENT_BALANCE",
  "message": "আপনার পর্যাপ্ত পরিমাণে ব্যালেন্স নেই! অনুগ্রহ করে রিচার্জ করুন।",
  "data": {
    "current_balance": 10,
    "required_coins": 100,
    "recharge_url": "/api/coin-packages"
  }
}
```

- **সফল হলে Response (200 OK)**:
```json
{
  "status": true,
  "message": "Gift sent successfully",
  "current_balance": 990,
  "gift_data": {
    "call_session_id": "CALL_SESSION_UUID_12345",
    "sender": {
      "id": 1,
      "name": "Admin",
      "avatar": "https://..."
    },
    "receiver": {
      "id": 12,
      "name": "Ayeena04"
    },
    "gift": {
      "id": 5,
      "name": "Rose",
      "coins": 10,
      "image_url": "https://chinchins.live/uploads/gifts/01_rose.svg",
      "animation_url": "https://chinchins.live/uploads/gifts/01_rose.svg",
      "animation_type": "svg"
    },
    "timestamp": "2026-09-17T10:38:10+00:00"
  }
}
```

---

### ৪.৩ ডাইনামিক কুইক প্রম্পট মেসেজ API
- **Endpoint**: `GET /api/v1/call/quick-messages` (এবং `/api/call/quick-messages`)
- **Response (200 OK)**:
```json
{
  "status": true,
  "data": [
    {"id": 1, "text": "Hi, what's up babe?"},
    {"id": 2, "text": "Be my girlfriend"},
    {"id": 3, "text": "You look beautiful!"},
    {"id": 4, "text": "Can we talk for a few minutes?"},
    {"id": 5, "text": "Sending you lots of love ❤️"},
    {"id": 6, "text": "Let's video chat!"}
  ]
}
```

---

### ৪.৪ রিসিভার প্রোফাইলে গিফট হিস্ট্রি API
- **Endpoint**: `GET /api/v1/user/received-gifts` (এবং `/api/user/received-gifts`)
- **Headers**: `Authorization: Bearer <token>`
- **Response (200 OK)**:
```json
{
  "status": true,
  "total_received_coins": 15400,
  "gifts": [
    {
      "gift_id": 5,
      "gift_name": "Rose",
      "icon_url": "https://chinchins.live/uploads/gifts/01_rose.svg",
      "count": 12,
      "coins_per_unit": 10,
      "total_coins": 120,
      "animation_url": "https://chinchins.live/uploads/gifts/01_rose.svg"
    }
  ]
}
```

---

## ৫. লাইভ স্ট্রিমিং ও ব্রডকাস্টিং লাইফসাইকেল

### ৫.১ Go to Live - ব্রডকাস্ট শুরু
- **Endpoint**: `POST /api/live/start` (এবং `POST /api/v1/stream/start`, `POST /api/v1/live/start`)
- **Request Body (Multipart / Form-Data)**:
  - `title`: `My Tonight Live Show`
  - `cover_image`: (File upload / Image)
- **Response (200 OK)**:
```json
{
  "status": true,
  "success": true,
  "message": "Live stream broadcast started successfully!",
  "data": {
    "room_id": "15",
    "live_stream_id": 15,
    "channel_name": "live_1_1726569600_abcd",
    "title": "My Tonight Live Show",
    "cover_image_url": "https://chinchins.live/uploads/live_streaming/cover_123.jpg",
    "status": "live",
    "role": "host",
    "session": {
      "agora": {
        "app_id": "agora_app_id_xxx",
        "channel_name": "live_1_1726569600_abcd",
        "token": "AGORA_RTC_SESSION_TOKEN_XXX"
      }
    }
  }
}
```

---

### ৫.২ একটিভ লাইভ স্ট্রিমস ফিড
- **Endpoint**: `GET /api/lives/active` (এবং `GET /api/live/active`, `GET /api/live/list`)
- **Response (200 OK)**:
```json
{
  "status": true,
  "data": [
    {
      "id": 15,
      "channel_name": "live_1_1726569600_abcd",
      "title": "My Tonight Live Show",
      "cover_image_url": "https://chinchins.live/uploads/live_streaming/cover_123.jpg",
      "viewer_count": 42,
      "total_diamonds_earned": 12000,
      "host": {
        "id": 1,
        "account_id": "1000000001",
        "display_name": "Ayeena04",
        "avatar_url": "https://chinchins.live/uploads/avatars/ayeena.jpg",
        "level": "Lv8"
      }
    }
  ]
}
```

---

### ৫.৩ লাইভ স্ট্রিমে জয়েন ও লিভ
- **Join Endpoint**: `POST /api/live/join`
  - Request: `{"room_id": 15}`
  - Returns RTC subscriber token & room details.
- **Leave Endpoint**: `POST /api/live/leave`
  - Request: `{"room_id": 15}`
  - Decrements live viewer count.

---

### ৫.৪ আনলিমিটেড লাইভ চ্যাট মেসেজ
- **Endpoint**: `POST /api/live/send-message` (এবং `/api/v1/stream/comment`, `/api/live/comment`)
- **Request Body**:
```json
{
  "room_id": 15,
  "message": "You look gorgeous tonight! 😍"
}
```
- **Reverb Broadcast Event**: `LiveChatMessageEvent` (`message.sent` on `live-room.15` / `live-stream.15`).

---

### ৫.৫ লাইভ স্ট্রিমে গিফট সেন্ডিং ও রেভিনিউ স্প্লিট
- **Endpoint**: `POST /api/live/send-gift` (এবং `/api/v1/stream/send-gift`)
- **Request Body**:
```json
{
  "room_id": 15,
  "gift_id": 5,
  "quantity": 1
}
```
- **Reverb Broadcast Event**: `LiveGiftSentEvent` (`gift.received` on `live-room.15`).

---

## ৬. Flutter মোবাইল ক্লায়েন্ট ইন্টিগ্রেশন ও হ্যান্ডওভার গাইড

### ক) Echo ও Reverb সাবস্ক্রিপশন কোড
```dart
import 'package:laravel_echo/laravel_echo.dart';
import 'package:pusher_client/pusher_client.dart';

// 1. Reverb ইনিশিয়ালাইজ করুন
PusherOptions options = PusherOptions(
  host: 'chinchins.live',
  port: 443,
  wssPort: 443,
  encrypted: true,
  auth: PusherAuth(
    'https://chinchins.live/api/broadcasting/auth',
    headers: {'Authorization': 'Bearer $authToken'},
  ),
);

Echo echo = Echo(
  client: PusherClient('chinchins_app_key', options, autoConnect: true),
  broadcaster: EchoBroadcasterType.Pusher,
);

// 2. কল সেশনে সাবস্ক্রাইব করুন
final callChannel = echo.private('call.$currentSessionId');

// ইন-কল মেসেজ লিসেনিং:
callChannel.listen('.message.sent', (data) {
  print('New in-call message received: ${data['message']}');
  setState(() {
    inCallMessagesList.add(ChatMessageModel.fromJson(data));
  });
});

// ইন-কল গিফট SVGA অ্যানিমেশন লিসেনিং:
callChannel.listen('.gift.received', (data) {
  final giftUrl = data['gift']['animation_url'];
  playSvgaAnimation(giftUrl);
});
```

### খ) ইন-কল গিফট পাঠানোর সময় 422 ব্যালেন্স এরর ডায়ালগ:
```dart
Future<void> sendGift(int receiverId, int giftId, String callSessionId) async {
  final response = await http.post(
    Uri.parse('https://chinchins.live/api/v1/call/gift/send'),
    headers: {
      'Authorization': 'Bearer $authToken',
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    },
    body: jsonEncode({
      'receiver_id': receiverId,
      'gift_id': giftId,
      'call_session_id': callSessionId,
    }),
  );

  final resData = jsonDecode(response.body);

  if (response.statusCode == 422 && resData['code'] == 'INSUFFICIENT_BALANCE') {
    // রিচার্জ ডায়ালগ ওপেন করুন
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Insufficient Coins'),
        content: Text(resData['message'] ?? 'Please recharge your wallet.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pushNamed(context, '/recharge'),
            child: const Text('Recharge Now'),
          ),
        ],
      ),
    );
  } else if (response.statusCode == 200) {
    // নিজের স্ক্রিনেও অ্যানিমেশন প্লে করুন
    playSvgaAnimation(resData['gift_data']['gift']['animation_url']);
  }
}
```

---
*Generated and verified for Chinchins Live Production Engine.*
