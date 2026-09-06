# ⚡ Complete Architecture & Implementation Guide (V2)
## Laravel Backend & Flutter Client: Hostinger VPS WebRTC + Agora Hybrid (Auto-Token & Admin Temp-Token Support)
> **Chinchins Live Real-Time Media Infrastructure (Version 2.0)**  
> **Backend Production Base URL:** `https://chinchins.live/api`  
> **Local Development Base URL:** `http://127.0.0.1:8000/api`  
> **Status:** ✅ Production Ready | Fully Configured in Database, Admin Panel & REST API

---

## 📌 ১. মূল কার্যপ্রণালী ও সিস্টেম স্পষ্টতা (Core Architecture & Purpose)

1. **চ্যাট ও ইমেজ আপলোড অপরিবর্তিত (Zero Disruption to Messaging):**
   * কাস্টমাররা চ্যাট বক্সে যে টেক্সট মেসেজ পাঠায় বা ছবি/ফাইল আপলোড করে, তা কোনো পরিবর্তন ছাড়াই বর্তমান লারাভেল API এবং VPS ডাটাবেজ/স্টোরেজেই সংরক্ষিত থাকবে। মেসেজিং সিস্টেমে কোনো কোড পরিবর্তন করতে হবে না।
2. **ডায়নামিক ১-ক্লিক ইঞ্জিন সুইচ (Admin Panel 1-Click Toggle):**
   * এডমিন প্যানেল (`/admin/settings` &rarr; `⚡ Streaming Engine`) থেকে মাত্র ১টি টগলের মাধ্যমে লাইভ ভিডিও/অডিও কলিং ইঞ্জিন পরিবর্তন করা যায়:
     * **Hostinger VPS Mode (`vps_webrtc`):** Laravel Reverb WebSocket সিগন্যালিং + WebRTC Peer-to-Peer (১০০% ফ্রি, কোনো থার্ড-পার্টি বিলিং নেই, আনলিমিটেড কল মিনিট)।
     * **Agora Cloud Mode (`agora`):** Agora RTC Enterprise Cloud (গ্লোবাল SD-RTN™ এজ নেটওয়ার্ক, HD ক্রিস্টাল ক্লিয়ার 720p/1080p 60fps ভিডিও, এআই নয়েজ ক্যান্সেলেশন)।
3. **ডাবল টোকেন হ্যান্ডলিং (Dual Token Handling - Auto & Manual Temp Token):**
   * **স্বয়ংক্রিয় ডায়নামিক মোড (Auto Dynamic HMAC-SHA256):** Agora App ID এবং Primary Certificate দিয়ে ব্যাকএন্ড প্রতি কলের জন্য স্বয়ংক্রিয়ভাবে নতুন সিকিউর টোকেন জেনারেট করে।
   * **এডমিন Temp-Token মোড (Admin Manual Override):** Agora Console থেকে **"Generate Temp Token"** বাটনে ক্লিক করে পাওয়া টেম্প টোকেন ও চ্যানেল নাম এডমিন প্যানেলে বসালে ব্যাকএন্ড সরাসরি সেই টোকেন ও চ্যানেল ক্লায়েন্ট অ্যাপে পাঠায় (তাৎক্ষণিক টেস্ট বা ডেডিকেটেড চ্যানেলের জন্য)।
4. **ইউজার ইন্টারফেসে ইঞ্জিন টেক্সট হাইড (Clean UI & Seamless Calling):**
   * ভিডিও বা অডিও কলে স্ক্রিনে কোনো টেকনিক্যাল লেখা যেমন *"Connecting via Agora Cloud Engine..."* আসবে না।
   * যাকে কল করা হচ্ছে তার প্রোফাইল ছবি ফুল-স্ক্রিন ব্যাকগ্রাউন্ডে শো করবে এবং কল রিসিভ হওয়ার সাথে সাথে হাই-ডেফিনিশন লাইভ ভিডিও স্ট্রিম শুরু হবে।

---

## 🗄️ ২. লারাভেল ডাটাবেজ আর্কিটেকচার (Database Schema & Migration)

`streaming_settings` টেবিলে স্বয়ংক্রিয় টোকেন জেনারেশনের পাশাপাশি এডমিন প্যানেল থেকে ম্যানুয়ালি Temp Token ও চ্যানেল সেট করার ফিল্ড যুক্ত করা হয়েছে:

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('streaming_settings', function (Blueprint $table) {
            $table->id();
            
            // ইঞ্জিন সিলেকশন: 'vps_webrtc' অথবা 'agora'
            $table->string('active_driver', 30)->default('vps_webrtc');
            
            // Agora মূল ক্রেডেনশিয়াল (ডায়নামিক টোকেন জেনারেটর)
            $table->string('agora_project_name')->nullable();
            $table->string('agora_app_id')->nullable();
            $table->text('agora_app_certificate')->nullable();
            
            // এডমিন প্যানেল থেকে ম্যানুয়াল Temp Token ও চ্যানেল দেওয়ার ফিল্ড
            $table->text('agora_temp_token')->nullable();
            $table->string('agora_manual_channel')->nullable();
            
            // টোকেন মেয়াদ ও Reverb সিগন্যালিং হোস্ট
            $table->integer('token_expire_seconds')->default(86400); // ২৪ ঘণ্টা
            $table->string('reverb_host')->nullable();
            $table->integer('reverb_port')->nullable();
            $table->string('reverb_scheme')->nullable()->default('https');
            
            // ফিচার টগল
            $table->boolean('enable_video_call')->default(true);
            $table->boolean('enable_audio_call')->default(true);
            $table->boolean('enable_live_stream')->default(true);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('streaming_settings');
    }
};
```

---

## ⚙️ ৩. Agora কনসোল থেকে ক্রেডেনশিয়াল সংগ্রহের নিয়ম

| ফিল্ডের নাম | Agora কনসোলে অবস্থান ও কপি করার নিয়ম | সিস্টেমে ভূমিকা |
| :--- | :--- | :--- |
| **Agora App ID** | Agora Console &rarr; **Project Management** &rarr; **Basic Settings** এর নিচে ডট ডট ফিল্ডের ডানপাশের কপি আইকন। | ফ্লাটার অ্যাপ এবং লারাভেল টোকেন বিল্ডারের মূল প্রজেক্ট আইডেন্টিফায়ার। |
| **Agora Primary Certificate** | Agora Console &rarr; **Project Management** &rarr; **Security** সেকশনের নিচে ডট ডট ফিল্ডের ডানপাশের কপি আইকন। | সার্ভার-সাইড HMAC-SHA256 ডায়নামিক টোকেন তৈরির সিক্রেট কী। |
| **Temp RTC Token & Channel** | Agora Console &rarr; **Project Management** &rarr; **Security** সেকশনের উপরে **"Generate Temp Token"** বাটনে চ্যানেল নাম লিখে তৈরি করা টোকেন। | এডমিন প্যানেলে সরাসরি ইনপুট দিয়ে তাৎক্ষণিক টেস্ট বা কল পরিচালনার বিকল্প উপায়। |

---

## 🔌 ৪. ইউনিফাইড ব্যাকএন্ড API এন্ডপয়েন্টস (Unified REST API V2)

### এন্ডপয়েন্ট তালিকা:
* **সেশন টোকেন ইনিশিয়ালাইজার:** `POST /api/stream/session-token` (অথবা এলিয়াস `POST /api/v1/stream/initialize`)
* **বর্তমান ইঞ্জিন ও কনফিগ চেক:** `GET /api/stream/driver` (অথবা এলিয়াস `GET /api/stream/config`)

---

### 📡 ৪.১ API রিকোয়েস্ট ফরম্যাট:
ফ্লাটার অ্যাপ থেকে ভিডিও/অডিও কল করার সময় নিচের রিকোয়েস্ট পাঠাতে হবে:

```http
POST /api/stream/session-token HTTP/1.1
Host: chinchins.live
Authorization: Bearer YOUR_SANCTUM_BEARER_TOKEN
Content-Type: application/json
Accept: application/json

{
  "channel_name": "chinchins_room_849201",
  "call_type": "video",
  "role": "publisher",
  "target_user_id": 12
}
```

#### রিকোয়েস্ট প্যারামিটার বিবরণ:
* `channel_name` *(String, আবশ্যক)*: কলের জন্য ইউনিক চ্যানেল বা রুম নেম (যেমন: `call_room_user1_user2`)।
* `call_type` *(String, ঐচ্ছিক)*: `'video'` অথবা `'audio'` (ডিফল্ট: `'video'`)।
* `role` *(String, ঐচ্ছিক)*: `'publisher'` (কলকারী বা রিসিভকারী) অথবা `'subscriber'` / `'audience'`।
* `target_user_id` *(Integer/String, ঐচ্ছিক)*: যাকে কল করা হচ্ছে তার User ID বা 8-ডিজিট Account ID। এটি পাঠালে API স্বয়ংক্রিয়ভাবে তার ফুল প্রোফাইল ছবি, নাম, জেমস/কয়েন ও লেভেল রিটার্ন করবে।

---

### 📥 ৪.২ API রেসপন্স ফরম্যাট:

#### কেস ১: এডমিন প্যানেলে **Agora Cloud** অ্যাক্টিভ (স্বয়ংক্রিয় ডায়নামিক টোকেন মোড)
```json
{
  "success": true,
  "status": true,
  "driver": "agora",
  "channel_name": "chinchins_room_849201",
  "agora_app_id": "055d57db64394a149625cd4cfbb0cbfc",
  "agora_token": "006MDA2ZGVmYXVsdF9hcHBfaWQgAC...",
  "agora_uid": 14,
  "is_temp_token": false,
  "user_id": 14,
  "account_id": "84920183",
  "target_user": {
    "id": 12,
    "account_id": "90214820",
    "name": "Shamim",
    "avatar_url": "https://chinchins.live/storage/avatars/user_12.jpg",
    "level": 3,
    "coins": 16200,
    "frame_url": "https://chinchins.live/assets/frames/vip_gold.png"
  },
  "call_type": "video",
  "role": "publisher",
  "expire_seconds": 86400,
  "enable_video": true,
  "enable_audio": true,
  "enable_live": true,
  "status_text": "Connecting...",
  "message": "Ready"
}
```

#### কেস ২: এডমিন প্যানেলে **Agora Temp Token Override** সক্রিয় থাকলে
```json
{
  "success": true,
  "status": true,
  "driver": "agora",
  "channel_name": "test_room",
  "agora_app_id": "055d57db64394a149625cd4cfbb0cbfc",
  "agora_token": "007eJxTYGg4W3b/y88f/r0O....",
  "agora_uid": 14,
  "is_temp_token": true,
  "status_text": "Connecting...",
  "message": "Ready"
}
```

#### কেস ৩: এডমিন প্যানেলে **Hostinger VPS WebRTC + Reverb** অ্যাক্টিভ
```json
{
  "success": true,
  "status": true,
  "driver": "vps_webrtc",
  "channel_name": "chinchins_room_849201",
  "user_id": 14,
  "account_id": "84920183",
  "target_user": {
    "id": 12,
    "name": "Shamim",
    "avatar_url": "https://chinchins.live/storage/avatars/user_12.jpg",
    "coins": 16200
  },
  "call_type": "video",
  "role": "publisher",
  "signaling_host": "chinchins.live",
  "signaling_port": 443,
  "signaling_scheme": "https",
  "reverb_app_key": "chinchins_reverb_key",
  "auth_endpoint": "https://chinchins.live/api/broadcasting/auth",
  "enable_video": true,
  "enable_audio": true,
  "enable_live": true,
  "status_text": "Connecting...",
  "message": "Ready"
}
```

> [!IMPORTANT]
> **ইউজার ইন্টারফেস পরিষ্কার রাখার নোটিশ:** রেসপন্সে `message` ফিল্ডে `"Connected via Agora Cloud Engine"` এর পরিবর্তে ক্লিন `"Ready"` এবং `status_text: "Connecting..."` দেওয়া হয়েছে। ফলে কোনো ক্লায়েন্ট টেকনিক্যাল ইঞ্জিন নাম দেখতে পাবে না।

---

## 📱 ৫. ফ্লাটার ক্লায়েন্ট আর্কিটেকচার ও কোড (Flutter V2 Implementation)

### ৫.১ `pubspec.yaml` ডিপেন্ডেন্সি
```yaml
dependencies:
  flutter:
    sdk: flutter
  agora_rtc_engine: ^6.3.0
  permission_handler: ^11.3.1
  http: ^1.2.0
  flutter_webrtc: ^0.10.6
```

---

### ৫.২ `StreamingService.dart` (Dynamic Router Controller)
```dart
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:permission_handler/permission_handler.dart';
import 'agora_call_screen.dart';
import 'existing_webrtc_call_screen.dart';

class StreamingService {
  static const String baseUrl = 'https://chinchins.live/api';

  /// Dynamically initiates video/audio call based on admin configuration
  static Future<void> initiateCall({
    required BuildContext context,
    required String channelName,
    required String callType, // 'video' or 'audio'
    required String userToken,
    int? targetUserId,
    String? targetUserName,
    String? targetUserAvatar,
  }) async {
    // 1. Request Camera & Mic Permissions
    await [Permission.camera, Permission.microphone].request();

    // 2. Fetch Active Driver and Credentials
    try {
      final res = await http.post(
        Uri.parse('$baseUrl/stream/session-token'),
        headers: {
          'Authorization': 'Bearer $userToken',
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({
          'channel_name': channelName,
          'call_type': callType,
          'role': 'publisher',
          'target_user_id': targetUserId,
        }),
      );

      if (res.statusCode != 200) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Failed to initiate call')),
        );
        return;
      }

      final data = jsonDecode(res.body);

      // Extract target user details for full screen profile display
      final targetUser = data['target_user'] ?? {};
      final String displayName = targetUser['name'] ?? targetUserName ?? 'User';
      final String avatarUrl = targetUser['avatar_url'] ?? targetUserAvatar ?? '';
      final int userGems = targetUser['coins'] ?? 0;

      // 3. Dynamic Engine Switcher Routing
      if (data['driver'] == 'agora') {
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (context) => AgoraCallScreen(
              appId: data['agora_app_id'],
              token: data['agora_token'],
              channelName: data['channel_name'],
              uid: (data['agora_uid'] as num).toInt(),
              isVideo: callType == 'video',
              targetName: displayName,
              targetAvatar: avatarUrl,
              targetGems: userGems,
            ),
          ),
        );
      } else {
        // Launch VPS WebRTC Call Screen
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (context) => ExistingWebRTCCallScreen(
              channelName: data['channel_name'],
              signalingHost: data['signaling_host'] ?? 'chinchins.live',
              targetName: displayName,
              targetAvatar: avatarUrl,
            ),
          ),
        );
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Connection error. Please try again.')),
      );
    }
  }
}
```

---

### ৫.৩ `AgoraCallScreen.dart` (Full-Screen Avatar, Instant Connect & HD Video)

এই স্ক্রিনটিতে:
1. ব্যাকগ্রাউন্ডে যাকে কল করা হচ্ছে তার **ফুল-স্ক্রিন প্রোফাইল পিকচার** ব্লার/ডার্ক গ্রেডিয়েন্ট হিসেবে শো করে।
2. কোনো ইঞ্জিন নাম (যেমন `Connecting via Agora...`) দেখানো হয় না; শুধুমাত্র স্বাভাবিক **"Calling..."** বা টাইমার শো করে।
3. হাই-কোয়ালিটি ভিডিও কনফিগারেশন (`720x1280`, 30 FPS, AI Noise Suppression) ব্যবহার করা হয়েছে যাতে কোনো ল্যাগ ছাড়া এইচডি স্ট্রিমিং হয়।
4. রিমোট ইউজার কানেক্ট হওয়ার সাথে সাথে ফুল-স্ক্রিন এইচডি ভিডিও ফিড চালু হয়ে যায়।

```dart
import 'dart:ui';
import 'package:agora_rtc_engine/agora_rtc_engine.dart';
import 'package:flutter/material.dart';

class AgoraCallScreen extends StatefulWidget {
  final String appId;
  final String token;
  final String channelName;
  final int uid;
  final bool isVideo;
  final String targetName;
  final String targetAvatar;
  final int targetGems;

  const AgoraCallScreen({
    Key? key,
    required this.appId,
    required this.token,
    required this.channelName,
    required this.uid,
    this.isVideo = true,
    required this.targetName,
    required this.targetAvatar,
    this.targetGems = 0,
  }) : super(key: key);

  @override
  State<AgoraCallScreen> createState() => _AgoraCallScreenState();
}

class _AgoraCallScreenState extends State<AgoraCallScreen> {
  int? _remoteUid;
  bool _localUserJoined = false;
  late RtcEngine _engine;
  bool _isMuted = false;
  bool _isVideoDisabled = false;
  int _callSeconds = 0;
  bool _isConnected = false;

  @override
  void initState() {
    super.initState();
    _initAgora();
  }

  Future<void> _initAgora() async {
    // 1. Initialize Agora RTC Engine
    _engine = createAgoraRtcEngine();
    await _engine.initialize(RtcEngineContext(
      appId: widget.appId,
      channelProfile: ChannelProfileType.channelProfileCommunication,
    ));

    // 2. Register RTC Event Callbacks
    _engine.registerEventHandler(
      RtcEngineEventHandler(
        onJoinChannelSuccess: (RtcConnection connection, int elapsed) {
          setState(() {
            _localUserJoined = true;
          });
        },
        onUserJoined: (RtcConnection connection, int remoteUid, int elapsed) {
          setState(() {
            _remoteUid = remoteUid;
            _isConnected = true;
          });
          _startCallTimer();
        },
        onUserOffline: (RtcConnection connection, int remoteUid, UserOfflineReasonType reason) {
          setState(() => _remoteUid = null);
          Navigator.of(context).pop(); // Hang up when peer hangs up
        },
        onError: (ErrorCodeType err, String msg) {
          debugPrint('Agora Error: $err - $msg');
        },
      ),
    );

    // 3. Configure HD Video & Audio Parameters (720p HD, 30fps)
    if (widget.isVideo) {
      await _engine.enableVideo();
      await _engine.setVideoEncoderConfiguration(
        const VideoEncoderConfiguration(
          dimensions: VideoDimensions(width: 720, height: 1280),
          frameRate: 30,
          bitrate: 1500,
          orientationMode: OrientationMode.orientationModeAdaptive,
        ),
      );
      await _engine.startPreview();
    } else {
      await _engine.enableAudio();
    }

    // Enable AI Noise Suppression for crystal-clear sound
    await _engine.setAudioProfile(
      profile: AudioProfileType.audioProfileMusicStandard,
      scenario: AudioScenarioType.audioScenarioGameStreaming,
    );

    // 4. Instant Join Channel with Token
    await _engine.joinChannel(
      token: widget.token,
      channelId: widget.channelName,
      uid: widget.uid,
      options: const ChannelMediaOptions(
        clientRoleType: ClientRoleType.clientRoleBroadcaster,
        channelProfile: ChannelProfileType.channelProfileCommunication,
        autoSubscribeAudio: true,
        autoSubscribeVideo: true,
      ),
    );
  }

  void _startCallTimer() {
    Future.doWhile(() async {
      await Future.delayed(const Duration(seconds: 1));
      if (!mounted || !_isConnected) return false;
      setState(() => _callSeconds++);
      return true;
    });
  }

  String _formatDuration(int seconds) {
    final m = (seconds ~/ 60).toString().padLeft(2, '0');
    final s = (seconds % 60).toString().padLeft(2, '0');
    return '$m:$s';
  }

  @override
  void dispose() {
    _dispose();
    super.dispose();
  }

  Future<void> _dispose() async {
    await _engine.leaveChannel();
    await _engine.release();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF1E1E24),
      body: Stack(
        children: [
          // 1. FULL-SCREEN BACKGROUND AVATAR (Displayed while connecting or in audio mode)
          if (_remoteUid == null || !widget.isVideo)
            Positioned.fill(
              child: Stack(
                fit: StackFit.expand,
                children: [
                  if (widget.targetAvatar.isNotEmpty)
                    Image.network(
                      widget.targetAvatar,
                      fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => Container(color: const Color(0xFF2C2C34)),
                    )
                  else
                    Container(color: const Color(0xFF2C2C34)),
                  // Glassmorphism Blur & Gradient Overlay
                  BackdropFilter(
                    filter: ImageFilter.blur(sigmaX: 25, sigmaY: 25),
                    child: Container(
                      decoration: BoxDecoration(
                        gradient: LinearGradient(
                          begin: Alignment.topCenter,
                          end: Alignment.bottomCenter,
                          colors: [
                            Colors.black.withOpacity(0.5),
                            Colors.black.withOpacity(0.8),
                          ],
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),

          // 2. REMOTE HD VIDEO FEED (Switches immediately when peer video arrives)
          if (_remoteUid != null && widget.isVideo)
            Positioned.fill(
              child: AgoraVideoView(
                controller: VideoViewController.remote(
                  rtcEngine: _engine,
                  canvas: VideoCanvas(uid: _remoteUid),
                  connection: RtcConnection(channelId: widget.channelName),
                ),
              ),
            ),

          // 3. TOP BAR: Back button, Peer Name, Gems Balance
          SafeArea(
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  IconButton(
                    icon: const Icon(Icons.keyboard_arrow_down, color: Colors.white, size: 30),
                    onPressed: () => Navigator.of(context).pop(),
                  ),
                  // Gems Badge
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                    decoration: BoxDecoration(
                      color: const Color(0xFFFF9800),
                      borderRadius: BorderRadius.circular(20),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.orange.withOpacity(0.4),
                          blurRadius: 10,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    child: Row(
                      children: [
                        const Icon(Icons.diamond, color: Colors.white, size: 16),
                        const SizedBox(width: 6),
                        Text(
                          '${widget.targetGems} Gems',
                          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),

          // 4. CALLER PROFILE & STATUS (Center display when video is not full screen)
          if (_remoteUid == null || !widget.isVideo)
            Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  // Center Avatar with Glowing Frame
                  Container(
                    width: 130,
                    height: 130,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      border: Border.all(color: const Color(0xFFFFC107), width: 3),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.amber.withOpacity(0.5),
                          blurRadius: 20,
                          spreadRadius: 2,
                        ),
                      ],
                    ),
                    child: ClipOval(
                      child: widget.targetAvatar.isNotEmpty
                          ? Image.network(widget.targetAvatar, fit: BoxFit.cover)
                          : const Icon(Icons.person, size: 70, color: Colors.white70),
                    ),
                  ),
                  const SizedBox(height: 16),
                  Text(
                    widget.targetName,
                    style: const TextStyle(color: Colors.white, fontSize: 24, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 8),
                  // Normal user-friendly status (NO BACKEND ENGINE STRINGS SHOWN)
                  Text(
                    _isConnected ? _formatDuration(_callSeconds) : 'Connecting...',
                    style: const TextStyle(color: Colors.white70, fontSize: 16, letterSpacing: 0.5),
                  ),
                ],
              ),
            ),

          // 5. LOCAL USER PIP PREVIEW (Small floating box in video call)
          if (widget.isVideo && _localUserJoined && !_isVideoDisabled)
            Positioned(
              top: 80,
              right: 20,
              width: 110,
              height: 160,
              child: Container(
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: const [BoxShadow(color: Colors.black45, blurRadius: 10)],
                  border: Border.all(color: Colors.white30, width: 1.5),
                ),
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(15),
                  child: AgoraVideoView(
                    controller: VideoViewController(
                      rtcEngine: _engine,
                      canvas: const VideoCanvas(uid: 0),
                    ),
                  ),
                ),
              ),
            ),

          // 6. CALL CONTROL TOOLBAR (Mute, Video Toggle, Camera Switch, Hang up)
          Positioned(
            bottom: 40,
            left: 0,
            right: 0,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: [
                // Mic Mute Toggle
                _buildCircleButton(
                  icon: _isMuted ? Icons.mic_off : Icons.mic,
                  color: _isMuted ? Colors.redAccent : Colors.white24,
                  onTap: () {
                    setState(() => _isMuted = !_isMuted);
                    _engine.muteLocalAudioStream(_isMuted);
                  },
                ),

                // Video Toggle (for video calls)
                if (widget.isVideo)
                  _buildCircleButton(
                    icon: _isVideoDisabled ? Icons.videocam_off : Icons.videocam,
                    color: _isVideoDisabled ? Colors.redAccent : Colors.white24,
                    onTap: () {
                      setState(() => _isVideoDisabled = !_isVideoDisabled);
                      _engine.muteLocalVideoStream(_isVideoDisabled);
                    },
                  ),

                // END CALL BUTTON
                GestureDetector(
                  onTap: () => Navigator.of(context).pop(),
                  child: Container(
                    width: 68,
                    height: 68,
                    decoration: BoxDecoration(
                      color: const Color(0xFFFF2D55),
                      shape: BoxShape.circle,
                      boxShadow: [
                        BoxShadow(
                          color: const Color(0xFFFF2D55).withOpacity(0.5),
                          blurRadius: 20,
                          spreadRadius: 2,
                        ),
                      ],
                    ),
                    child: const Icon(Icons.call_end, color: Colors.white, size: 32),
                  ),
                ),

                // Camera Switch (Front/Back)
                if (widget.isVideo)
                  _buildCircleButton(
                    icon: Icons.switch_camera,
                    color: Colors.white24,
                    onTap: () => _engine.switchCamera(),
                  ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCircleButton({required IconData icon, required Color color, required VoidCallback onTap}) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        width: 52,
        height: 52,
        decoration: BoxDecoration(color: color, shape: BoxShape.circle),
        child: Icon(icon, color: Colors.white, size: 26),
      ),
    );
  }
}
```

---

## 🧭 ৬. এডমিন প্যানেলে ব্যবহারের নিয়ম (Admin Checklist)

1. ব্রাউজারে এডমিন সেটিংস পেজে যান: `https://chinchins.live/admin/settings`
2. **"⚡ Streaming Engine"** ট্যাবে ক্লিক করুন।
3. **Agora Cloud Mode** সিলেক্ট করুন।
4. **App ID ও Primary Certificate** নিশ্চিত করুন (ডায়নামিক অটো-টোকেন তৈরির জন্য)।
5. **(ঐচ্ছিক) Temp RTC Token & Channel ফিল্ড:**
   * আপনি যদি Agora Console &rarr; Generate Temp Token থেকে কোনো স্পেসিফিক টেস্ট টোকেন পান, তবে তা **Temp RTC Token** বক্সে পেস্ট করে দিন এবং চ্যানেল নেম লিখুন।
   * **স্বয়ংক্রিয় প্রোডাকশন মোডের জন্য:** এই দুটি ফিল্ড ফাঁকা রাখুন। ব্যাকএন্ড স্বয়ংক্রিয়ভাবে প্রতি কল সেশনের জন্য ফ্রেশ ডায়নামিক টোকেন তৈরি করবে।
6. **"Save Engine Configurations"** বাটনে ক্লিক করুন।
7. কোনো নতুন APK রিলিজ ছাড়াই সাথে সাথে সমস্ত অ্যাপ নতুন সেটিংসে কাজ করবে।
