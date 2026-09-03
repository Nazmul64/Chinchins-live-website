# 🎁 Live Streaming Gift & Wallet Engine — Backend & Client Specification
## 🇧🇩 লাইভ স্ট্রিমিং গিফট ও ওয়ালেট ইঞ্জিন (Laravel Reverb + SVGA/Lottie + Flutter)

> **সিস্টেম সারসংক্ষেপ (Feature Overview):**
> এই ইঞ্জিনটি লাইভ স্ট্রিমিং রুমে ইউজারদের হোস্টকে রিয়েল-টাইম গিফট পাঠানো, ওয়ালেট ব্যালেন্স ট্রানজ্যাকশন ম্যানেজমেন্ট, রেস-কন্ডিশন প্রতিরোধ এবং **Laravel Reverb WebSocket**-এর মাধ্যমে মিলি-সেকেন্ডে সকল দর্শকের স্ক্রিনে **SVGA / Lottie Fullscreen & Bubble অ্যানিমেশন** ব্রডকাস্ট করার জন্য তৈরি।

---

## 📑 সূচিপত্র (Table of Contents)
1. [আর্কিটেকচার ও লাইফসাইকেল ফ্লো (Architecture Flow)](#1-আর্কিটেকচার-ও-লাইফসাইকেল-ফ্লো)
2. [ডাটাবেজ স্কিমা (Database Schema Design)](#2-ডাটাবেজ-স্কিমা-design)
3. [এডমিন প্যানেল লজিক (Admin Panel SVGA & Icon Upload)](#3-এডমিন-প্যানেল-লজিক)
4. [কয়েন কেনা লজিক (Coin Purchase & Recharge Flow)](#4-কয়েন-কেনা-লজিক)
5. [গিফট পাঠানো এবং Reverb ব্রডকাস্টিং (API Specification)](#5-গিফট-পাঠানো-এবং-reverb-ব্রডকাস্টিং)
6. [Laravel Reverb Event ক্লাস (`LiveGiftSentEvent.php`)](#6-laravel-reverb-event-ক্লাস)
7. [Flutter Client Implementation Guide (SVGA, Lottie, Reverb Listener)](#7-flutter-client-implementation-guide)
8. [সম্পূর্ণ RESTful API রেফারেন্স তালিকা](#8-সম্পূর্ণ-restful-api-রেফারেন্স-তালিকা)

---

## 1. আর্কিটেকচার ও লাইফসাইকেল ফ্লো

```
   ┌────────────────────────────────────────────────────────┐
   │                   Sender (Fan / Viewer)                │
   │  Taps Gift (e.g. "Private Jet" - 1,200 Coins, SVGA)    │
   └───────────────────────────┬────────────────────────────┘
                               │
                               ▼ POST /api/gifts/send (stream_id, receiver_id, gift_id)
   ┌────────────────────────────────────────────────────────┐
   │             Backend (Laravel + DB Transaction)         │
   │ 1. Lock Sender Wallet: lockForUpdate() (No negative)   │
   │ 2. Deduct Sender Balance (-1,200 Coins)                │
   │ 3. Credit Host Earnings (+1,200 Earnings)              │
   │ 4. Insert into gift_transactions & user_gifts          │
   └───────────────────────────┬────────────────────────────┘
                               │
                               ▼ broadcast(new LiveGiftSentEvent($streamId, $giftData))
   ┌────────────────────────────────────────────────────────┐
   │        ⚡ Laravel Reverb WebSocket Broadcast           │
   │ Channel: live-stream.{stream_id}                       │
   │ Event: gift.received                                   │
   └───────────────────────────┬────────────────────────────┘
                               │
                               ▼ Real-Time Push to All Connected Viewers
   ┌────────────────────────────────────────────────────────┐
   │             Flutter Live Stream Screen                 │
   │ 1. Triggers SVGAPlayer / Lottie Animation overlay      │
   │ 2. Displays Fullscreen Jet / Supercar Animation        │
   │ 3. Updates Chat Stream: "User X sent Private Jet"     │
   └────────────────────────────────────────────────────────┘
```

---

## 2. ডাটাবেজ স্কিমা Design

### ক. `coin_packages` টেবিল (এডমিন থেকে কয়েন প্যাক সেট করা)
```php
Schema::create('coin_packages', function (Blueprint $table) {
    $table->id();
    $table->string('title')->nullable(); // e.g. "Starter Pack", "VIP Diamond Pack"
    $table->unsignedBigInteger('coins'); // e.g. 500, 8100, 32000
    $table->unsignedBigInteger('bonus_coins')->default(0); // e.g. 8000
    $table->decimal('price', 10, 2); // e.g. 300.00, 550.00
    $table->string('currency', 3)->default('BDT');
    $table->string('badge')->nullable(); // "50% OFF", "Best Value"
    $table->string('badge_color')->nullable()->default('pink');
    $table->boolean('is_popular')->default(false);
    $table->boolean('is_active')->default(true);
    $table->unsignedInteger('sort_order')->default(0);
    $table->timestamps();
});
```

### খ. `gifts` টেবিল (এডমিন প্যানেল থেকে আপলোড করা গিফট)
```php
Schema::create('gifts', function (Blueprint $table) {
    $table->id();
    $table->string('name'); // e.g. "Private Jet", "Supercar", "Rose Bouquet"
    $table->unsignedInteger('coins')->default(100);
    $table->unsignedInteger('coin_price')->default(100); // e.g. 1200
    $table->string('category')->default('popular');
    $table->string('image'); // uploads/gifts/icons/xxx.png
    $table->string('icon_url')->nullable(); // ট্রে-তে দেখানোর আইকন
    $table->string('animation_url')->nullable(); // uploads/gifts/animations/xxx.svga
    $table->string('file_url')->nullable(); // মূল SVGA বা Lottie ফাইলের লিঙ্ক
    $table->enum('format', ['svga', 'lottie', 'webp', 'image'])->default('svga');
    $table->enum('display_type', ['fullscreen', 'bubble'])->default('fullscreen');
    $table->string('sound_url')->nullable();
    $table->boolean('is_broadcast')->default(false);
    $table->boolean('is_active')->default(true);
    $table->string('badge')->nullable();
    $table->unsignedInteger('sort_order')->default(0);
    $table->timestamps();
});
```

### গ. `wallets` টেবিল (ইউজারদের কয়েন ব্যালেন্স ও হোস্ট আর্নিংস)
```php
Schema::create('wallets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
    $table->unsignedBigInteger('balance')->default(0); // পাঠানো কয়েন (Gift Sender Balance / Recharge Coins)
    $table->unsignedBigInteger('earnings')->default(0); // রিসিভড কয়েন (Host Withdrawable Balance)
    $table->timestamps();
});
```

### ঘ. `gift_transactions` টেবিল (হিস্ট্রি লগ)
```php
Schema::create('gift_transactions', function (Blueprint $table) {
    $table->id();
    $table->string('stream_id')->index();
    $table->foreignId('sender_id')->constrained('users');
    $table->foreignId('receiver_id')->constrained('users');
    $table->foreignId('gift_id')->constrained('gifts');
    $table->unsignedInteger('coins_spent');
    $table->timestamps();
});
```

---

## 3. এডমিন প্যানেল লজিক (Admin Panel Upload)

এডমিন প্যানেল থেকে ফর্ম সাবমিট করার সময় ফাইল স্টোরেজের কন্ট্রোলার মেথড:

```php
public function storeGift(Request $request)
{
    $request->validate([
        'name'           => 'required|string|max:100',
        'coin_price'     => 'required|integer|min:1',
        'icon'           => 'required|image|mimes:png,webp,jpg|max:5120',
        'animation_file' => 'nullable|file|max:25600', // SVGA বা JSON (max 25MB)
        'format'         => 'nullable|in:svga,lottie,webp,image',
        'display_type'   => 'required|in:fullscreen,bubble'
    ]);

    // ফাইল স্টোর করা
    $iconPath = 'uploads/gifts/rose.png';
    if ($request->hasFile('icon')) {
        $file = $request->file('icon');
        $filename = 'icon_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads/gifts/icons'), $filename);
        $iconPath = 'uploads/gifts/icons/' . $filename;
    }

    $filePath = null;
    $format = $request->input('format', 'svga');
    if ($request->hasFile('animation_file')) {
        $animFile = $request->file('animation_file');
        $ext = strtolower($animFile->getClientOriginalExtension());
        $animFilename = 'anim_' . time() . '_' . Str::random(8) . '.' . $ext;
        $animFile->move(public_path('uploads/gifts/animations'), $animFilename);
        $filePath = 'uploads/gifts/animations/' . $animFilename;
        $format = ($ext === 'svga') ? 'svga' : (in_array($ext, ['json', 'lottie']) ? 'lottie' : 'webp');
    }

    $gift = Gift::create([
        'name'         => $request->name,
        'coins'        => $request->coin_price,
        'coin_price'   => $request->coin_price,
        'image'        => $iconPath,
        'icon_url'     => asset($iconPath),
        'animation_url'=> $filePath,
        'file_url'     => $filePath ? asset($filePath) : null,
        'format'       => $format,
        'display_type' => $request->display_type,
    ]);

    return response()->json(['status' => true, 'message' => 'Gift uploaded successfully!', 'data' => $gift]);
}
```

---

## 4. কয়েন কেনা লজিক (Coin Purchase Flow)

ইউজার যখন পেমেন্ট গেটওয়ের মাধ্যমে কয়েন প্যাকেজ কিনবে, সফল পেমেন্ট ভেরিফিকেশনের পর ওয়ালেটে কয়েন যুক্ত হবে:

```php
public function addCoinsAfterPayment($userId, $packageId)
{
    return DB::transaction(function () use ($userId, $packageId) {
        $package = CoinPackage::findOrFail($packageId);
        $wallet = Wallet::firstOrCreate(['user_id' => $userId]);

        $totalCoins = (int) ($package->coins + ($package->bonus_coins ?? 0));

        // ব্যালেন্সে কয়েন যোগ
        $wallet->increment('balance', $totalCoins);

        $user = User::find($userId);
        if ($user) {
            $user->increment('coins', $totalCoins);
        }

        // ট্রানজেকশন রেকর্ড রাখা
        CoinPurchaseLog::create([
            'user_id'     => $userId,
            'package_id'  => $packageId,
            'coins'       => $totalCoins,
            'amount_paid' => $package->price,
            'currency'    => $package->currency ?? 'BDT',
        ]);
    });
}
```

---

## 5. গিফট পাঠানো এবং Reverb ব্রডকাস্টিং (API Controller)

রেস কন্ডিশন এড়াতে অবশ্যই `DB::transaction` এবং `lockForUpdate()` ব্যবহার করতে হবে।

* **Method:** `POST`
* **URL:** `/api/gifts/send` (বা `/api/live/send-gift`)
* **Headers:** `Authorization: Bearer {token}`
* **Request Body:**
```json
{
  "stream_id": "room_8921",
  "receiver_id": 42,
  "gift_id": 5
}
```

#### Controller Implementation:
```php
public function sendGift(Request $request)
{
    $request->validate([
        'stream_id'   => 'required',
        'receiver_id' => 'required|exists:users,id',
        'gift_id'     => 'required|exists:gifts,id',
    ]);

    $sender = auth()->user() ?? $this->resolveUser($request);
    $gift = Gift::findOrFail($request->gift_id);
    $cost = (int) ($gift->coin_price ?: $gift->coins);

    return DB::transaction(function () use ($sender, $gift, $cost, $request) {
        // ব্যালেন্স লক করে রিড করা (যাতে নেগেটিভ ব্যালেন্স না হয়)
        $senderWallet = Wallet::where('user_id', $sender->id)->lockForUpdate()->first();
        if (!$senderWallet) {
            $senderWallet = $sender->getOrCreateWallet();
            $senderWallet = Wallet::where('user_id', $sender->id)->lockForUpdate()->first();
        }

        if (!$senderWallet || $senderWallet->balance < $cost) {
            return response()->json(['status' => false, 'message' => 'Insufficient coins!'], 400);
        }

        // ১. সেন্ডারের কয়েন কাটা
        $senderWallet->decrement('balance', $cost);
        $sender->decrement('coins', $cost);

        // ২. হোস্টের আর্নিংয়ে কয়েন যোগ
        $receiverWallet = Wallet::firstOrCreate(['user_id' => $request->receiver_id]);
        $receiverWallet->increment('earnings', $cost);

        $receiver = User::find($request->receiver_id);
        if ($receiver) {
            $receiver->increment('coins', (int) floor($cost * 0.50));
        }

        // ৩. হিস্ট্রি রেকর্ড
        GiftTransaction::create([
            'stream_id'   => $request->stream_id,
            'sender_id'   => $sender->id,
            'receiver_id' => $request->receiver_id,
            'gift_id'     => $gift->id,
            'coins_spent' => $cost,
        ]);

        // ৪. রিয়েল-টাইম ব্রডকাস্ট পে-লোড (Flutter-এর জন্য)
        $eventData = [
            'stream_id'     => $request->stream_id,
            'sender_id'     => $sender->id,
            'sender_name'   => $sender->display_name ?? $sender->name,
            'sender_avatar' => $sender->avatar_url,
            'gift_id'       => $gift->id,
            'gift_name'     => $gift->name,
            'icon_url'      => $gift->icon_url ?: $gift->image_url,
            'file_url'      => $gift->file_url ?: $gift->animation_full_url,
            'format'        => $gift->format ?? 'svga',
            'display_type'  => $gift->display_type ?? 'fullscreen',
            'coins_spent'   => $cost,
        ];

        // Laravel Reverb ইভেন্ট ফায়ার
        broadcast(new LiveGiftSentEvent($request->stream_id, $eventData))->toOthers();

        return response()->json([
            'status'            => true,
            'message'           => 'Gift sent successfully!',
            'remaining_balance' => $senderWallet->fresh()->balance,
            'gift'              => $eventData
        ]);
    });
}
```

#### Success Response (`200 OK`):
```json
{
  "status": true,
  "message": "Gift sent successfully!",
  "remaining_balance": 14200,
  "gift": {
    "stream_id": "room_8921",
    "sender_id": 12,
    "sender_name": "Rahim Khan",
    "sender_avatar": "https://yourdomain.com/uploads/profiles/avatar_12.jpg",
    "gift_id": 5,
    "gift_name": "Private Jet",
    "icon_url": "https://yourdomain.com/uploads/gifts/icons/jet_icon.png",
    "file_url": "https://yourdomain.com/uploads/gifts/animations/private_jet.svga",
    "format": "svga",
    "display_type": "fullscreen",
    "coins_spent": 1200
  }
}
```

---

## 6. Laravel Reverb Event ক্লাস

**File:** `app/Events/LiveGiftSentEvent.php`

```php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveGiftSentEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $streamId;
    public $giftData;

    public function __construct($streamId, $giftData)
    {
        $this->streamId = (string) $streamId;
        $this->giftData = (array) $giftData;
    }

    public function broadcastOn()
    {
        return new Channel('live-stream.' . $this->streamId);
    }

    public function broadcastAs()
    {
        return 'gift.received';
    }

    public function broadcastWith()
    {
        return $this->giftData;
    }
}
```

---

## 7. Flutter Client Implementation Guide

Flutter অ্যাপে **SVGA Player** (`svgaplayer_flutter`) অথবা **Lottie** এবং **Pusher Channels Client** ব্যবহার করে লাইভ স্ট্রিমিং রুমে গিফট রিসিভ ও প্লে করার কোড:

### ক. লাইভ রুম সাবস্ক্রিপশন ও ইভেন্ট লিসেনার
```dart
import 'package:flutter/material.dart';
import 'package:pusher_channels_flutter/pusher_channels_flutter.dart';
import 'package:svgaplayer_flutter/svgaplayer_flutter.dart';

class LiveStreamRoomScreen extends StatefulWidget {
  final String streamId;
  const LiveStreamRoomScreen({Key? key, required this.streamId}) : super(key: key);

  @override
  State<LiveStreamRoomScreen> createState() => _LiveStreamRoomScreenState();
}

class _LiveStreamRoomScreenState extends State<LiveStreamRoomScreen> with SingleTickerProviderStateMixin {
  SVGAAnimationController? _svgaAnimationController;
  Map<String, dynamic>? _currentGiftPlaying;
  bool _isPlayingAnimation = false;

  @override
  void initState() {
    super.initState();
    _svgaAnimationController = SVGAAnimationController(vsync: this);
    _initReverbListener();
  }

  Future<void> _initReverbListener() async {
    PusherChannelsFlutter pusher = PusherChannelsFlutter.getInstance();
    await pusher.init(
      apiKey: "YOUR_REVERB_APP_KEY",
      cluster: "mt1",
      host: "yourdomain.com",
      wsPort: 8080,
      wssPort: 443,
      useTLS: true,
      onEvent: _onLiveEvent,
    );

    // Subscribe to live-stream.{streamId} channel
    await pusher.subscribe(channelName: "live-stream.${widget.streamId}");
    await pusher.connect();
  }

  void _onLiveEvent(PusherEvent event) {
    if (event.eventName == 'gift.received') {
      final giftData = jsonDecode(event.data);
      _playGiftAnimation(giftData);
    }
  }

  Future<void> _playGiftAnimation(Map<String, dynamic> gift) async {
    final String? fileUrl = gift['file_url'];
    final String format = gift['format'] ?? 'svga';

    if (fileUrl == null || fileUrl.isEmpty) return;

    setState(() {
      _currentGiftPlaying = gift;
      _isPlayingAnimation = true;
    });

    if (format == 'svga') {
      final videoItem = await SVGAParser.shared.decodeFromURL(fileUrl);
      _svgaAnimationController?.videoItem = videoItem;
      _svgaAnimationController?.reset();
      _svgaAnimationController?.forward().whenComplete(() {
        if (mounted) {
          setState(() {
            _isPlayingAnimation = false;
            _currentGiftPlaying = null;
          });
        }
      });
    }
  }

  @override
  void dispose() {
    _svgaAnimationController?.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      body: Stack(
        children: [
          // 1. Live Video Stream View
          const Center(child: Text("Live Video Stream", style: TextStyle(color: Colors.white))),

          // 2. Fullscreen SVGA Gift Animation Overlay
          if (_isPlayingAnimation && _svgaAnimationController?.videoItem != null)
            Positioned.fill(
              child: IgnorePointer(
                child: SVGAImage(_svgaAnimationController!),
              ),
            ),

          // 3. Sender Banner Popup (e.g. "Rahim sent Private Jet")
          if (_isPlayingAnimation && _currentGiftPlaying != null)
            Positioned(
              top: 100,
              left: 20,
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(colors: [Color(0xFFFF4081), Color(0xFF7C4DFF)]),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Row(
                  children: [
                    CircleAvatar(
                      radius: 16,
                      backgroundImage: NetworkImage(_currentGiftPlaying!['sender_avatar']),
                    ),
                    const SizedBox(width: 8),
                    Text(
                      "${_currentGiftPlaying!['sender_name']} sent ${_currentGiftPlaying!['gift_name']}!",
                      style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13),
                    ),
                  ],
                ),
              ),
            ),
        ],
      ),
    );
  }
}
```

---

## 8. সম্পূর্ণ RESTful API রেফারেন্স তালিকা

| Method | Endpoint | Description | Auth Required |
|---|---|---|---|
| `GET` | `/api/gifts/catalog` | গিফট ক্যাটালগ ও ক্যাটাগরি তালিকা | ❌ Optional |
| `POST` | `/api/gifts/send` | লাইভ রুমে গিফট পাঠানো ও Reverb ব্রডকাস্ট | ✅ Yes (Bearer) |
| `POST` | `/api/live/send-gift` | লাইভ গিফটের বিকল্প রাউট | ✅ Yes (Bearer) |
| `POST` | `/api/gifts/upload` | এডমিন থেকে গিফট ও SVGA ফাইল আপলোড | ✅ Admin |
| `GET` | `/api/gifts/top-fans/{host_id}` | হোস্টের টপ ফ্যান লিডারবোর্ড | ❌ Optional |
| `POST` | `/api/coin-packages` | নতুন কয়েন প্যাকেজ তৈরি | ✅ Admin |
| `GET` | `/api/coin-packages` | রিচার্জের জন্য কয়েন প্যাকেজ তালিকা | ❌ Optional |

---

✅ **Backend Ready:** ডাটাবেজ মাইগ্রেশন সম্পন্ন, `Wallet` ও `GiftTransaction` মডেল তৈরি, `lockForUpdate()` ট্রানজ্যাকশন সুরক্ষিত এবং `LiveGiftSentEvent` সক্রিয়।
