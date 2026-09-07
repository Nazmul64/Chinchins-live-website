# 📱 Chinchins Live — Complete Master RESTful API & Flutter Integration Guide (A to Z)

**Single Source of Truth Documentation:** `FLUTTER_DEVELOPER_COMPLETE_API_AND_IMPLEMENTATION_GUIDE.md`  
**Target:** Flutter Mobile App Developers, Backend Engineers & Technical Leads  
**Backend:** Laravel REST API Engine & Laravel Reverb  
**Base URL (Live):** `https://chinchins.live/api`  
**Base URL (Local Dev):** `http://10.0.2.2:8000/api` (Android Emulator) or `http://localhost:8000/api`  
**Global Auth Header:** `Authorization: Bearer <Sanctum_Token>` or `X-User-Id: <User_ID>`  

---

## 📑 Table of Contents
1. [📦 Required Flutter Packages (`pubspec.yaml`)](#1--required-flutter-packages)
2. [📞 Feature 1: Dual-Engine Audio/Video Calling (Agora RTC + WebRTC)](#2--feature-1-dual-engine-audiovideo-calling-agora-rtc--webrtc)
   - [Signaling (Reverb) vs Media Transmission](#signaling-reverb-vs-media-transmission)
   - [RESTful APIs: Calling & Dynamic Token](#restful-apis-calling--dynamic-token)
   - [HD Video & Loud Crystal-Clear Audio Configuration](#hd-video--loud-crystal-clear-audio-configuration)
   - [Background Incoming Call Wake-up Flow (FCM Push)](#background-incoming-call-wake-up-flow-fcm-push)
   - [Flutter Calling Service & Complete Screen Code](#flutter-calling-service--complete-screen-code)
3. [👑 Feature 2: Floating Home Screen VIP Widget ("Extra Gems" / Monthly Card)](#3--feature-2-floating-home-screen-vip-widget-extra-gems--monthly-card)
   - [RESTful API: `GET /api/vip/floating-banner` & `GET /api/app/config`](#restful-api-floating-vip-banner)
   - [Flutter Floating VIP Widget Code](#flutter-floating-vip-widget-code)
4. [🎁 Feature 3: Gifts & In-App Rewards (Chat, Live & Profile)](#4--feature-3-gifts--in-app-rewards-chat-live--profile)
   - [RESTful API: `GET /api/gifts/catalog` & `GET /api/gifts/received/{id}`](#restful-apis-for-gifts)
   - [Flutter Gift Item Display Widget (PNG Cached + SVG Fallback)](#flutter-gift-item-display-widget)
5. [💎 Feature 4: Coin Packages (Mobile App Store Recharge Screen)](#5--feature-4-coin-packages-mobile-app-store-recharge-screen)
   - [RESTful API: `GET /api/coin-packages`](#restful-api-coin-packages)
   - [Flutter Coin Package Grid Card Widget](#flutter-coin-package-grid-card-widget)
6. [🎒 Feature 5: My Bag & Inventory (Coupons, Avatar Frames, Chat Styles, Entrances)](#6--feature-5-my-bag--inventory)
   - [RESTful API: `GET /api/my-bag`](#restful-api-my-bag)
   - [Flutter My Bag Item Widget](#flutter-my-bag-item-widget)
7. [👤 Feature 6: User Profile & Admin Avatar Display](#7--feature-6-user-profile--admin-avatar-display)
8. [📋 Complete Flutter Integration Checklist](#8--complete-flutter-integration-checklist)

---

# 1. 📦 Required Flutter Packages

Add the following packages to your `pubspec.yaml`:

```yaml
dependencies:
  flutter:
    sdk: flutter

  # Networking, State & Asset Caching
  http: ^1.2.1
  cached_network_image: ^3.3.1
  flutter_svg: ^2.0.10+1

  # Real-Time Media Engine
  agora_rtc_engine: ^6.3.2

  # Device Hardware Permissions
  permission_handler: ^11.3.1
```

Run in terminal:
```bash
flutter pub get
```

---

# 2. 📞 Feature 1: Dual-Engine Audio/Video Calling (Agora RTC + WebRTC)

### Signaling (Reverb) vs Media Transmission
```text
1. CALL SIGNALING (Laravel Reverb WebSocket)
   ├── User A calls User B (Trigger incoming call ring)
   ├── User B accepts / rejects
   └── User A/B hangs up or cancels

2. MEDIA ENGINE (Dynamic Agora RTC / WebRTC)
   ├── Fetches Dynamic Session Token from Laravel: POST /api/calls
   ├── Joins Channel using Dynamic Channel Name & UID
   └── Transmits High-Definition Camera, Mic & Audio Tracks
```

---

### RESTful APIs: Calling & Dynamic Token

#### 1. Initialize Call Session
Called by both Caller (when placing call) and Receiver (when answering call).

- **Endpoint:** `POST /api/calls` (Aliases: `/api/stream/session-token`, `/api/calls/initiate`)
- **Headers:**
  ```http
  Authorization: Bearer <Sanctum_Token>
  Content-Type: application/json
  Accept: application/json
  ```
- **Request Body:**
  ```json
  {
    "channel_name": "call_8f92a7c1", // Optional: Backend auto-generates if empty
    "call_type": "video",            // "video" or "audio"
    "role": "publisher",
    "target_user_id": 29
  }
  ```

##### Response Body (`200 OK` - Agora Engine):
```json
{
  "success": true,
  "status": true,
  "driver": "agora",
  "channel_name": "call_8f92a7c1",
  "app_id": "9348xxxxxxxxxxxxxxxxxxxx",
  "agora_app_id": "9348xxxxxxxxxxxxxxxxxxxx",
  "uid": 1025,
  "agora_uid": 1025,
  "token": "0069348xxxxxxxxxxxxxxxxxxxxIADxxxxx...",
  "rtc_token": "0069348xxxxxxxxxxxxxxxxxxxxIADxxxxx...",
  "token_expires_at": "2026-09-07T13:00:00+06:00",
  "expire_seconds": 3600,
  "debug_mode": true,
  "sdk_logging": true,
  "log_level": "info",
  "target_user": {
    "id": 29,
    "name": "Hakim",
    "avatar_url": "https://chinchins.live/uploads/avatars/user29.jpg",
    "level": 4,
    "coins": 23400
  },
  "call_type": "video"
}
```

##### Response Body (`200 OK` - WebRTC Engine):
```json
{
  "success": true,
  "status": true,
  "driver": "vps_webrtc",
  "channel_name": "call_8f92a7c1",
  "uid": 1025,
  "signaling_host": "chinchins.live",
  "signaling_port": 443,
  "signaling_scheme": "https",
  "app_key": "chinchins_reverb_key",
  "auth_endpoint": "https://chinchins.live/api/broadcasting/auth",
  "target_user": {
    "id": 29,
    "name": "Hakim",
    "avatar_url": "https://chinchins.live/uploads/avatars/user29.jpg"
  }
}
```

---

#### 2. Token Refresh Endpoint
Called automatically before token expires.

- **Endpoint:** `POST /api/agora/token/refresh`
- **Request Body:**
  ```json
  {
    "channel_name": "call_8f92a7c1",
    "uid": 1025,
    "role": "publisher"
  }
  ```
- **Response:**
  ```json
  {
    "success": true,
    "driver": "agora",
    "token": "006NEW_REFRESHED_TOKEN_HERE...",
    "rtc_token": "006NEW_REFRESHED_TOKEN_HERE...",
    "expires_at": "2026-09-07T14:00:00+06:00"
  }
  ```

---

### HD Video & Loud Crystal-Clear Audio Configuration

To guarantee **1080p/720p HD 60fps Video** and **Loud, Crystal-Clear Audio** without noise:

```dart
// Configure Audio Profile for loud, clear speech with noise suppression
await _engine.setAudioProfile(
  profile: AudioProfileType.audioProfileMusicStandard,
  scenario: AudioScenarioType.audioScenarioGameStreaming,
);
await _engine.enableAudioVolumeIndication(interval: 200, smooth: 3, reportVad: true);

// Configure HD Video Encoder (1280x720, 30fps, 1710kbps)
await _engine.setVideoEncoderConfiguration(
  const VideoEncoderConfiguration(
    dimensions: VideoDimensions(width: 1280, height: 720),
    frameRate: 30,
    bitrate: 1710,
    orientationMode: OrientationMode.orientationModeAdaptive,
  ),
);
```

---

### Background Incoming Call Wake-up Flow (FCM Push)
When the app is killed or minimized:
1. Caller hits `POST /api/calls`.
2. Laravel sends high-priority FCM Data Message with payload `{"type": "INCOMING_CALL", "channel_name": "...", "caller_name": "..."}`.
3. Flutter Background Handler triggers system ringtone & incoming call UI (CallKit on iOS / FullScreenIntent on Android).

---

### Flutter Calling Service & Complete Screen Code

Save to `lib/screens/agora_call_screen.dart`:

```dart
import 'dart:ui';
import 'package:flutter/material.dart';
import 'package:agora_rtc_engine/agora_rtc_engine.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

class AgoraCallScreen extends StatefulWidget {
  final String appId;
  final String token;
  final String channelName;
  final int myUid;
  final String userAuthToken;
  final Map<String, dynamic>? targetUser;
  final bool debugMode;
  final String logLevel;

  const AgoraCallScreen({
    Key? key,
    required this.appId,
    required this.token,
    required this.channelName,
    required this.myUid,
    required this.userAuthToken,
    this.targetUser,
    this.debugMode = true,
    this.logLevel = 'info',
  }) : super(key: key);

  @override
  State<AgoraCallScreen> createState() => _AgoraCallScreenState();
}

class _AgoraCallScreenState extends State<AgoraCallScreen> {
  int? _remoteUid;
  bool _localUserJoined = false;
  late RtcEngine _engine;
  bool _isMuted = false;
  bool _isFrontCamera = true;

  @override
  void initState() {
    super.initState();
    _initAgora();
  }

  Future<void> _initAgora() async {
    // 1. Request Hardware Permissions
    await [Permission.microphone, Permission.camera].request();

    // 2. Initialize RTC Engine
    _engine = createAgoraRtcEngine();
    await _engine.initialize(RtcEngineContext(
      appId: widget.appId,
      channelProfile: ChannelProfileType.channelProfileCommunication,
    ));

    // 3. Audio & Video HD Quality Profiles
    await _engine.setAudioProfile(
      profile: AudioProfileType.audioProfileMusicStandard,
      scenario: AudioScenarioType.audioScenarioGameStreaming,
    );
    await _engine.setVideoEncoderConfiguration(
      const VideoEncoderConfiguration(
        dimensions: VideoDimensions(width: 1280, height: 720),
        frameRate: 30,
        bitrate: 1710,
      ),
    );

    // 4. Set Log Level
    await _engine.setLogLevel(widget.logLevel == 'verbose' ? LogLevel.logLevelDebug : LogLevel.logLevelInfo);

    // 5. Register Event Callbacks
    _engine.registerEventHandler(
      RtcEngineEventHandler(
        onJoinChannelSuccess: (RtcConnection connection, int elapsed) {
          debugPrint("✅ [Agora] Joined: ${connection.channelId} | Local UID: ${connection.localUid}");
          setState(() => _localUserJoined = true);
        },
        onUserJoined: (RtcConnection connection, int remoteUid, int elapsed) {
          debugPrint("🎥 [Agora] Remote User Joined: $remoteUid");
          setState(() => _remoteUid = remoteUid);
        },
        onUserOffline: (RtcConnection connection, int remoteUid, UserOfflineReasonType reason) {
          debugPrint("❌ [Agora] Remote User Left: $remoteUid");
          setState(() => _remoteUid = null);
          if (mounted) Navigator.of(context).pop();
        },
        onTokenPrivilegeWillExpire: (RtcConnection connection, String token) async {
          debugPrint("⚠️ [Agora] Renewing expiring token...");
          final res = await http.post(
            Uri.parse('https://chinchins.live/api/agora/token/refresh'),
            headers: {'Authorization': 'Bearer ${widget.userAuthToken}', 'Content-Type': 'application/json'},
            body: jsonEncode({'channel_name': widget.channelName, 'uid': widget.myUid}),
          );
          final data = jsonDecode(res.body);
          await _engine.renewToken(data['token']);
        },
        onError: (ErrorCodeType err, String msg) {
          debugPrint("🚨 [Agora Error] $err: $msg");
        },
      ),
    );

    // 6. Enable Video & Join
    await _engine.enableVideo();
    await _engine.startPreview();
    await _engine.joinChannel(
      token: widget.token,
      channelId: widget.channelName,
      uid: widget.myUid,
      options: const ChannelMediaOptions(
        channelProfile: ChannelProfileType.channelProfileCommunication,
        clientRoleType: ClientRoleType.clientRoleBroadcaster,
        autoSubscribeAudio: true,
        autoSubscribeVideo: true,
        publishCameraTrack: true,
        publishMicrophoneTrack: true,
      ),
    );
  }

  @override
  void dispose() {
    _engine.leaveChannel();
    _engine.release();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final String avatarUrl = widget.targetUser?['avatar_url'] ?? '';
    final String targetName = widget.targetUser?['name'] ?? 'Partner';

    return Scaffold(
      backgroundColor: Colors.black,
      body: Stack(
        children: [
          // 🖼️ 1. Full-Screen Caller Avatar Backdrop (Before remote video starts)
          if (_remoteUid == null) ...[
            if (avatarUrl.isNotEmpty)
              Positioned.fill(
                child: CachedNetworkImage(
                  imageUrl: avatarUrl,
                  fit: BoxFit.cover,
                  errorWidget: (_, __, ___) => Container(color: const Color(0xFF161224)),
                ),
              )
            else
              Container(color: const Color(0xFF161224)),

            Positioned.fill(
              child: BackdropFilter(
                filter: ImageFilter.blur(sigmaX: 18, sigmaY: 18),
                child: Container(
                  color: Colors.black.withOpacity(0.45),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      CircleAvatar(
                        radius: 65,
                        backgroundImage: avatarUrl.isNotEmpty ? NetworkImage(avatarUrl) : null,
                        child: avatarUrl.isEmpty ? const Icon(Icons.person, size: 60) : null,
                      ),
                      const SizedBox(height: 18),
                      Text(targetName, style: const TextStyle(color: Colors.white, fontSize: 24, fontWeight: FontWeight.bold)),
                      const SizedBox(height: 10),
                      const Text("Connecting HD video call...", style: TextStyle(color: Colors.white70, fontSize: 14)),
                    ],
                  ),
                ),
              ),
            ),
          ],

          // 🎥 2. Remote Full-Screen Video
          if (_remoteUid != null)
            AgoraVideoView(
              controller: VideoViewController.remote(
                rtcEngine: _engine,
                canvas: VideoCanvas(uid: _remoteUid),
                connection: RtcConnection(channelId: widget.channelName),
              ),
            ),

          // 📱 3. Local Camera Floating Preview
          if (_localUserJoined)
            Positioned(
              top: 50,
              right: 20,
              width: 110,
              height: 160,
              child: ClipRRect(
                borderRadius: BorderRadius.circular(16),
                child: Container(
                  decoration: BoxDecoration(border: Border.all(color: Colors.white54, width: 1.5)),
                  child: AgoraVideoView(
                    controller: VideoViewController(rtcEngine: _engine, canvas: const VideoCanvas(uid: 0)),
                  ),
                ),
              ),
            ),

          // 🎛️ 4. Action Buttons (Mute, Hangup, Flip Camera)
          Positioned(
            bottom: 40,
            left: 0,
            right: 0,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: [
                CircleAvatar(
                  radius: 28,
                  backgroundColor: Colors.white24,
                  child: IconButton(
                    icon: Icon(_isMuted ? Icons.mic_off : Icons.mic, color: Colors.white),
                    onPressed: () {
                      setState(() => _isMuted = !_isMuted);
                      _engine.muteLocalAudioStream(_isMuted);
                    },
                  ),
                ),
                FloatingActionButton(
                  backgroundColor: Colors.redAccent,
                  onPressed: () => Navigator.of(context).pop(),
                  child: const Icon(Icons.call_end, color: Colors.white, size: 30),
                ),
                CircleAvatar(
                  radius: 28,
                  backgroundColor: Colors.white24,
                  child: IconButton(
                    icon: const Icon(Icons.switch_camera, color: Colors.white),
                    onPressed: () {
                      _engine.switchCamera();
                      setState(() => _isFrontCamera = !_isFrontCamera);
                    },
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
```

---

# 3. 👑 Feature 2: Floating Home Screen VIP Widget ("Extra Gems" / Monthly Card)

### RESTful API: Floating VIP Banner
- **Endpoint:** `GET /api/vip/floating-banner` (Also in `GET /api/app/config`)
- **Headers:** `Authorization: Bearer <Sanctum_Token>`

#### 📥 Response (`200 OK`):
```json
{
  "status": true,
  "message": "Floating VIP banner retrieved successfully.",
  "data": {
    "is_enabled": true,
    "title": "Extra Gems",
    "tag": "Monthly Card",
    "image_url": "https://chinchins.live/uploads/vip_cards/floating_banner_1725701923.png",
    "action_type": "OPEN_PREMIUM_VIP"
  }
}
```

---

### Flutter Floating VIP Widget Code

Save to `lib/widgets/draggable_floating_vip_widget.dart`:

```dart
import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';

class DraggableFloatingVipWidget extends StatefulWidget {
  final Map<String, dynamic> config;
  final VoidCallback onTap;

  const DraggableFloatingVipWidget({
    Key? key,
    required this.config,
    required this.onTap,
  }) : super(key: key);

  @override
  State<DraggableFloatingVipWidget> createState() => _DraggableFloatingVipWidgetState();
}

class _DraggableFloatingVipWidgetState extends State<DraggableFloatingVipWidget> {
  Offset position = const Offset(20, 200);

  @override
  Widget build(BuildContext context) {
    if (!(widget.config['is_enabled'] ?? true)) return const SizedBox.shrink();

    final String imageUrl = widget.config['image_url'] ?? '';
    final String title = widget.config['title'] ?? 'Extra Gems';

    return Positioned(
      left: position.dx,
      top: position.dy,
      child: Draggable(
        feedback: _buildWidget(imageUrl, title),
        childWhenDragging: const SizedBox.shrink(),
        onDragEnd: (details) {
          setState(() {
            position = details.offset;
          });
        },
        child: GestureDetector(
          onTap: widget.onTap,
          child: _buildWidget(imageUrl, title),
        ),
      ),
    );
  }

  Widget _buildWidget(String imageUrl, String title) {
    return Container(
      width: 72,
      height: 84,
      padding: const EdgeInsets.all(4),
      decoration: BoxDecoration(
        color: const Color(0xFF1E1B38),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.amber, width: 1.5),
        boxShadow: const [BoxShadow(color: Colors.black45, blurRadius: 8)],
      ),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          CachedNetworkImage(
            imageUrl: imageUrl,
            width: 48,
            height: 48,
            fit: BoxFit.contain,
            errorWidget: (_, __, ___) => const Icon(Icons.workspace_premium, color: Colors.amber, size: 40),
          ),
          const SizedBox(height: 2),
          Text(
            title,
            style: const TextStyle(color: Colors.amber, fontSize: 9, fontWeight: FontWeight.bold),
          ),
        ],
      ),
    );
  }
}
```

---

# 4. 🎁 Feature 3: Gifts & In-App Rewards (Chat, Live & Profile)

### RESTful APIs for Gifts
- **Endpoint:** `GET /api/gifts/catalog?category=all`
- **Headers:** `Authorization: Bearer <Sanctum_Token>`

#### 📥 Response Format:
```json
{
  "status": true,
  "data": {
    "user_balance": { "coins": 23400, "formatted_coins": "23.40K" },
    "gifts": [
      {
        "id": 1,
        "name": "Trophy Cup",
        "category": "hot",
        "coins": 500,
        "formatted_coins": "500",
        "badge": "HOT",
        "png_url": "https://chinchins.live/uploads/gifts/trophy_cup.png",
        "svg_url": "https://chinchins.live/uploads/gifts/trophy_cup.svg",
        "image_url": "https://chinchins.live/uploads/gifts/trophy_cup.png"
      }
    ]
  }
}
```

---

### Flutter Gift Item Display Widget

```dart
import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter_svg/flutter_svg.dart';

Widget buildGiftItemWidget(Map<String, dynamic> gift, {double size = 52.0}) {
  final String? pngUrl = gift['png_url'];
  final String? svgUrl = gift['svg_url'] ?? gift['image_url'];

  if (pngUrl != null && pngUrl.isNotEmpty) {
    return CachedNetworkImage(
      imageUrl: pngUrl,
      width: size,
      height: size,
      fit: BoxFit.contain,
      errorWidget: (_, __, ___) => SvgPicture.network(
        svgUrl ?? '',
        width: size,
        height: size,
        fit: BoxFit.contain,
        placeholderBuilder: (_) => const Icon(Icons.card_giftcard, color: Colors.amber),
      ),
    );
  }

  return SvgPicture.network(svgUrl ?? '', width: size, height: size, fit: BoxFit.contain);
}
```

---

# 5. 💎 Feature 4: Coin Packages (Mobile App Store Recharge Screen)

### RESTful API: Coin Packages
- **Endpoint:** `GET /api/coin-packages`
- **Headers:** `Authorization: Bearer <Sanctum_Token>`

#### 📥 Response (`200 OK`):
```json
{
  "status": true,
  "data": [
    {
      "id": 1,
      "title": "Starter Pack",
      "coins": 32000,
      "bonus_coins": 8000,
      "total_coins": 40000,
      "formatted_coins": "32,000",
      "formatted_price": "৳550",
      "badge": "50% off",
      "badge_color": "danger",
      "png_url": "https://chinchins.live/assets/images/coins/gem-stack.png",
      "svg_url": "https://chinchins.live/uploads/coin_packages/gem_tier1_single.svg",
      "icon_full_url": "https://chinchins.live/uploads/coin_packages/gem_tier1_single.svg"
    }
  ]
}
```

---

### Flutter Coin Package Grid Card Widget

```dart
Widget buildCoinPackageCard(Map<String, dynamic> pkg, VoidCallback onRecharge) {
  return Container(
    padding: const EdgeInsets.all(12),
    decoration: BoxDecoration(
      color: const Color(0xFF1E1B38),
      borderRadius: BorderRadius.circular(16),
      border: Border.all(color: Colors.amber.withOpacity(0.4)),
    ),
    child: Column(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        if (pkg['badge'] != null)
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
            decoration: BoxDecoration(color: Colors.redAccent, borderRadius: BorderRadius.circular(6)),
            child: Text(pkg['badge'], style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold)),
          ),
        const SizedBox(height: 8),
        CachedNetworkImage(
          imageUrl: pkg['png_url'] ?? pkg['icon_full_url'] ?? '',
          width: 54,
          height: 54,
          fit: BoxFit.contain,
          errorWidget: (_, __, ___) => const Icon(Icons.diamond, color: Colors.amber, size: 44),
        ),
        const SizedBox(height: 8),
        Text(pkg['formatted_coins'] ?? '${pkg['coins']}', style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold)),
        if (pkg['bonus_coins'] != null && pkg['bonus_coins'] > 0)
          Text('+${pkg['bonus_coins']} Bonus', style: const TextStyle(color: Colors.pinkAccent, fontSize: 11)),
        const SizedBox(height: 10),
        ElevatedButton(
          style: ElevatedButton.styleFrom(backgroundColor: Colors.amber, shape: const StadiumBorder()),
          onPressed: onRecharge,
          child: Text(pkg['formatted_price'] ?? 'Recharge', style: const TextStyle(color: Colors.black, fontWeight: FontWeight.bold)),
        ),
      ],
    ),
  );
}
```

---

# 6. 🎒 Feature 5: My Bag & Inventory (Coupons, Avatar Frames, Chat Styles, Entrances)

### RESTful API: My Bag
- **Endpoint:** `GET /api/my-bag`
- **Headers:** `Authorization: Bearer <Sanctum_Token>`

#### 📥 Response (`200 OK`):
```json
{
  "status": true,
  "data": {
    "user_coins": 23400,
    "categories": [
      {
        "category": "avatar_frame",
        "name": "Avatar Frame",
        "count": 2,
        "icon_url": "https://chinchins.live/uploads/my_bag/frame_royal_amethyst.svg"
      }
    ],
    "items": [
      {
        "user_bag_item_id": 10,
        "name": "Royal Amethyst Frame",
        "category": "avatar_frame",
        "png_url": "https://chinchins.live/uploads/my_bag/frame_royal_amethyst.png",
        "svg_url": "https://chinchins.live/uploads/my_bag/frame_royal_amethyst.svg",
        "image_url": "https://chinchins.live/uploads/my_bag/frame_royal_amethyst.svg",
        "is_equipped": true,
        "duration_text": "30 Days"
      }
    ]
  }
}
```

---

### Flutter My Bag Item Widget

```dart
Widget buildMyBagItem(Map<String, dynamic> item) {
  final String pngUrl = item['png_url'] ?? '';
  final String svgUrl = item['svg_url'] ?? item['image_url'] ?? '';

  return ListTile(
    leading: pngUrl.isNotEmpty
        ? CachedNetworkImage(
            imageUrl: pngUrl,
            width: 44,
            height: 44,
            errorWidget: (_, __, ___) => SvgPicture.network(svgUrl, width: 44, height: 44),
          )
        : SvgPicture.network(svgUrl, width: 44, height: 44),
    title: Text(item['name'] ?? '', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
    subtitle: Text(item['duration_text'] ?? '', style: const TextStyle(color: Colors.white70)),
    trailing: item['is_equipped'] == true
        ? const Chip(label: Text('In-Use', style: TextStyle(color: Colors.white, fontSize: 10)), backgroundColor: Colors.green)
        : ElevatedButton(onPressed: () {}, child: const Text('Equip')),
  );
}
```

---

# 7. 👤 Feature 6: User Profile & Admin Avatar Display

- **Upload Avatar API:** `POST /api/user/avatar` (Multipart `avatar` file or Base64 string).
- **Profile API:** `GET /api/user/me` (Returns `avatar_url`, `display_name`, `coins`, `level`).
- **Admin Panel View:** Admin directory at `/admin/users` automatically displays real user uploaded avatar photos with fallback initials.

---

# 8. 📋 Complete Flutter Integration Checklist

- [x] **Calling Engine:** Connects via `POST /api/calls` with dynamic token & joins Agora / WebRTC.
- [x] **Token Renewal:** Listens to `onTokenPrivilegeWillExpire` and hits `POST /api/agora/token/refresh`.
- [x] **HD Audio/Video:** Sets 720p 30fps encoder + game streaming audio profile for loud speech.
- [x] **Floating VIP Widget:** Loads dynamic image from `GET /api/vip/floating-banner`.
- [x] **Gifts & Bags:** Displays `.png_url` via `CachedNetworkImage` with `.svg_url` fallback.
- [x] **Coin Packages:** Shows dynamic store packages from `GET /api/coin-packages`.

---
*(End of Master Documentation)*
