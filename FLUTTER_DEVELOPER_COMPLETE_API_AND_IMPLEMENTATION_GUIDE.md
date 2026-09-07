# 📱 Chinchins Live — Complete Master RESTful API & Flutter Integration Guide (A to Z)

**Master Document:** `FLUTTER_DEVELOPER_COMPLETE_API_AND_IMPLEMENTATION_GUIDE.md`  
**Target:** Flutter Mobile App Developers, Full-Stack Engineers & Technical Leads  
**Backend:** Laravel REST API Engine & Laravel Reverb  
**Base URL (Production):** `https://chinchins.live/api`  
**Base URL (Local Development):** `http://10.0.2.2:8000/api` (Android Emulator) or `http://localhost:8000/api`  
**Global Auth Header:** `Authorization: Bearer <Sanctum_Token>` or `X-User-Id: <User_ID>`  

---

## 📑 Table of Contents
1. [📦 Required Flutter Packages (`pubspec.yaml`)](#1--required-flutter-packages)
2. [🎵 Feature 1: Instant Ringtone & Dial Tone Audio Flow](#2--feature-1-instant-ringtone--dial-tone-audio-flow)
   - [RESTful API: Call Ringtones & Branding Config](#restful-api-call-ringtones--branding-config)
   - [Flutter Audio Player Code (Instant Outgoing Dial Tone & Incoming Ringtone)](#flutter-audio-player-code)
3. [📞 Feature 2: High-Definition Calling Engine (Loud Audio & HD Video)](#3--feature-2-high-definition-calling-engine-loud-audio--hd-video)
   - [Dual-Driver Architecture: Agora RTC vs WebRTC](#dual-driver-architecture)
   - [Loud, Crystal-Clear Audio & 720p 60fps HD Video Setup](#loud-crystal-clear-audio--hd-video-setup)
   - [RESTful API: `POST /api/calls` & `POST /api/agora/token/refresh`](#restful-apis-calling--dynamic-token)
   - [Flutter Agora Call Screen Code (`AgoraCallScreen.dart`)](#flutter-agora-call-screen-code)
4. [🎨 Feature 3: Dynamic App Logo on Login & Register Screen](#4--feature-3-dynamic-app-logo-on-login--register-screen)
   - [RESTful API: `GET /api/app/config`](#restful-api-app-config)
   - [Flutter Dynamic Branding Logo Widget](#flutter-dynamic-branding-logo-widget)
5. [👑 Feature 4: Floating Home Screen VIP Widget ("Extra Gems" / Monthly Card)](#5--feature-4-floating-home-screen-vip-widget-extra-gems--monthly-card)
   - [RESTful API: `GET /api/vip/floating-banner`](#restful-api-floating-vip-banner)
   - [Flutter Floating VIP Widget Code](#flutter-floating-vip-widget-code)
6. [🎁 Feature 5: Gifts & In-App Rewards (`public/uploads/gifts`)](#6--feature-5-gifts--in-app-rewards)
   - [RESTful API: `GET /api/gifts/catalog` & `GET /api/gifts/received/{id}`](#restful-apis-for-gifts)
   - [Flutter Gift Item Display Widget (PNG Cached + SVG Fallback)](#flutter-gift-item-display-widget)
7. [💎 Feature 6: Coin Packages Store (`public/uploads/coin_packages`)](#7--feature-6-coin-packages-store)
   - [RESTful API: `GET /api/coin-packages`](#restful-api-coin-packages)
   - [Flutter Coin Package Grid Card Widget](#flutter-coin-package-grid-card-widget)
8. [🎒 Feature 7: My Bag Items (`public/uploads/my_bag`)](#8--feature-7-my-bag-items)
   - [RESTful API: `GET /api/my-bag`](#restful-api-my-bag)
   - [Flutter My Bag Item Widget](#flutter-my-bag-item-widget)
9. [👤 Feature 8: User Profile & Admin Avatar Directory](#9--feature-8-user-profile--admin-avatar-directory)
10. [📋 Complete Flutter Integration Checklist](#10--complete-flutter-integration-checklist)

---

# 1. 📦 Required Flutter Packages

Ensure your `pubspec.yaml` contains these standard packages:

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

  # Ringtones & Audio SFX Player
  audioplayers: ^6.0.0

  # Device Hardware Permissions
  permission_handler: ^11.3.1
```

Run in terminal:
```bash
flutter pub get
```

---

# 2. 🎵 Feature 1: Instant Ringtone & Dial Tone Audio Flow

### Problem Solved:
1. **Admin Customization:** Admin uploads MP3 files in Admin Panel (`/admin/calls/settings`) for:
   - **Incoming Call Ringtone** (Plays continuously on receiver's phone when call arrives).
   - **Outgoing Call Dial Tone** (Plays immediately on caller's phone while waiting for host to answer).
2. **Zero Delay:** As soon as User A hits Call, the dial tone begins playing instantly. As soon as User B receives signaling/push, the ringtone begins playing instantly.

---

### RESTful API: Call Ringtones & Branding Config
- **Endpoint:** `GET /api/app/config` (or `GET /api/app/remote-config`)
- **Response Format (`200 OK`):**
```json
{
  "status": true,
  "data": {
    "app_name": "Chinchins Live",
    "app_tagline": "Meet, Chat & Video Call Live",
    "app_logo_url": "https://chinchins.live/uploads/branding/app_logo_1725701923.png",
    "incoming_ringtone": "https://chinchins.live/uploads/ringtones/incoming_ringtone_1788024.mp3",
    "outgoing_ringtone": "https://assets.mixkit.co/active_storage/sfx/1359/1359-preview.mp3",
    "video_call_rate": 100,
    "free_trial_duration": 16
  }
}
```

---

### Flutter Audio Player Code

Save to `lib/services/call_audio_service.dart`:

```dart
import 'package:audioplayers/audioplayers.dart';

class CallAudioService {
  static final AudioPlayer _player = AudioPlayer();

  /// 🔔 Play Incoming Call Ringtone immediately on receiver side
  static Future<void> playIncomingRingtone(String ringtoneUrl) async {
    await _player.stop();
    await _player.setReleaseMode(ReleaseMode.loop);
    await _player.play(UrlSource(ringtoneUrl));
  }

  /// 📞 Play Outgoing Call Dial Tone immediately on caller side
  static Future<void> playOutgoingDialTone(String dialToneUrl) async {
    await _player.stop();
    await _player.setReleaseMode(ReleaseMode.loop);
    await _player.play(UrlSource(dialToneUrl));
  }

  /// ⏹️ Stop any playing ringtone/dial tone upon connect or hangup
  static Future<void> stopRingtone() async {
    await _player.stop();
  }
}
```

---

# 3. 📞 Feature 2: High-Definition Calling Engine (Loud Audio & HD Video)

### Loud, Crystal-Clear Audio & HD Video Setup
To ensure both caller and receiver hear each other with **extremely loud, crisp, crystal-clear speech** and view **720p 60fps HD video**:

```dart
// 1. Enable Speakerphone & Set Loud High-Quality Audio Scenario
await _engine.setEnableSpeakerphone(true);
await _engine.setDefaultAudioRouteToSpeakerphone(true);
await _engine.setAudioProfile(
  profile: AudioProfileType.audioProfileMusicStandard,
  scenario: AudioScenarioType.audioScenarioGameStreaming,
);

// 2. Maximize Recording & Playback Volume Boost (Up to 400% clarity boost)
await _engine.adjustRecordingSignalVolume(400);
await _engine.adjustPlaybackSignalVolume(400);

// 3. HD 720p 30/60fps Video Configuration
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

### RESTful APIs: Calling & Dynamic Token

#### 1. Initialize Call Session
- **Endpoint:** `POST /api/calls` (Aliases: `/api/stream/session-token`, `/api/calls/initiate`)
- **Request Body:**
  ```json
  {
    "channel_name": "call_room_8f92a7c1", // Optional: Backend generates if empty
    "call_type": "video",                 // "video" or "audio"
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
  "channel_name": "call_room_8f92a7c1",
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

---

### Flutter Agora Call Screen Code

Save to `lib/screens/agora_call_screen.dart`:

```dart
import 'dart:ui';
import 'package:flutter/material.dart';
import 'package:agora_rtc_engine/agora_rtc_engine.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import '../services/call_audio_service.dart';

class AgoraCallScreen extends StatefulWidget {
  final String appId;
  final String token;
  final String channelName;
  final int myUid;
  final String userAuthToken;
  final Map<String, dynamic>? targetUser;
  final String? dialToneUrl;

  const AgoraCallScreen({
    Key? key,
    required this.appId,
    required this.token,
    required this.channelName,
    required this.myUid,
    required this.userAuthToken,
    this.targetUser,
    this.dialToneUrl,
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
    // 🔔 Start Outgoing Dial Tone immediately
    if (widget.dialToneUrl != null) {
      CallAudioService.playOutgoingDialTone(widget.dialToneUrl!);
    }
    _initAgora();
  }

  Future<void> _initAgora() async {
    await [Permission.microphone, Permission.camera].request();

    _engine = createAgoraRtcEngine();
    await _engine.initialize(RtcEngineContext(
      appId: widget.appId,
      channelProfile: ChannelProfileType.channelProfileCommunication,
    ));

    // 🔊 Setup Loud, Crystal-Clear Audio with Speakerphone & Volume Boost
    await _engine.setEnableSpeakerphone(true);
    await _engine.setDefaultAudioRouteToSpeakerphone(true);
    await _engine.setAudioProfile(
      profile: AudioProfileType.audioProfileMusicStandard,
      scenario: AudioScenarioType.audioScenarioGameStreaming,
    );
    await _engine.adjustRecordingSignalVolume(400);
    await _engine.adjustPlaybackSignalVolume(400);

    // 🎥 Setup 720p HD Video Encoder
    await _engine.setVideoEncoderConfiguration(
      const VideoEncoderConfiguration(
        dimensions: VideoDimensions(width: 1280, height: 720),
        frameRate: 30,
        bitrate: 1710,
      ),
    );

    _engine.registerEventHandler(
      RtcEngineEventHandler(
        onJoinChannelSuccess: (RtcConnection connection, int elapsed) {
          debugPrint("✅ [Agora] Local joined channel: ${connection.channelId}");
          setState(() => _localUserJoined = true);
        },
        onUserJoined: (RtcConnection connection, int remoteUid, int elapsed) {
          debugPrint("🎥 [Agora] Remote User Joined: $remoteUid");
          // ⏹️ Stop Dial Tone immediately when remote partner joins!
          CallAudioService.stopRingtone();
          setState(() => _remoteUid = remoteUid);
        },
        onUserOffline: (RtcConnection connection, int remoteUid, UserOfflineReasonType reason) {
          CallAudioService.stopRingtone();
          setState(() => _remoteUid = null);
          if (mounted) Navigator.of(context).pop();
        },
        onError: (ErrorCodeType err, String msg) {
          debugPrint("🚨 [Agora Error] $err: $msg");
        },
      ),
    );

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
    CallAudioService.stopRingtone();
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
                      const Text("Connecting high quality HD call...", style: TextStyle(color: Colors.white70, fontSize: 14)),
                    ],
                  ),
                ),
              ),
            ),
          ],

          // 🎥 2. Remote Full-Screen HD Video Stream
          if (_remoteUid != null)
            AgoraVideoView(
              controller: VideoViewController.remote(
                rtcEngine: _engine,
                canvas: VideoCanvas(uid: _remoteUid),
                connection: RtcConnection(channelId: widget.channelName),
              ),
            ),

          // 📱 3. Local Camera Floating Preview (Top Right)
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

# 4. 🎨 Feature 3: Dynamic App Logo on Login & Register Screen

### RESTful API: App Config
- **Endpoint:** `GET /api/app/config`
- **Response Value:** `data.app_logo_url`

```dart
Widget buildAppLogo(String logoUrl, {double size = 90.0}) {
  return CachedNetworkImage(
    imageUrl: logoUrl,
    width: size,
    height: size,
    fit: BoxFit.contain,
    placeholder: (context, url) => const SizedBox(
      width: 30,
      height: 30,
      child: CircularProgressIndicator(strokeWidth: 2, color: Colors.pinkAccent),
    ),
    errorWidget: (context, url, error) => Image.asset('assets/images/logo.png', width: size, height: size),
  );
}
```

---

# 5. 👑 Feature 4: Floating Home Screen VIP Widget ("Extra Gems" / Monthly Card)

### RESTful API: Floating VIP Banner
- **Endpoint:** `GET /api/vip/floating-banner` (Also in `GET /api/app/config`)
- **Response Format (`200 OK`):**
```json
{
  "status": true,
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

    return Positioned(
      left: position.dx,
      top: position.dy,
      child: Draggable(
        feedback: _buildWidget(imageUrl),
        childWhenDragging: const SizedBox.shrink(),
        onDragEnd: (details) {
          setState(() => position = details.offset);
        },
        child: GestureDetector(
          onTap: widget.onTap,
          child: _buildWidget(imageUrl),
        ),
      ),
    );
  }

  Widget _buildWidget(String imageUrl) {
    return Container(
      width: 78,
      height: 90,
      decoration: const BoxDecoration(
        color: Colors.transparent, // Fully Transparent background!
      ),
      child: CachedNetworkImage(
        imageUrl: imageUrl,
        fit: BoxFit.contain,
        errorWidget: (_, __, ___) => const Icon(Icons.workspace_premium, color: Colors.amber, size: 50),
      ),
    );
  }
}
```

---

# 6. 🎁 Feature 5: Gifts & In-App Rewards (`public/uploads/gifts`)

- **Location on Backend:** `public/uploads/gifts/*.png` and `public/uploads/gifts/*.svg`
- **Endpoint:** `GET /api/gifts/catalog?category=all`
- **Response Format:**
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

# 7. 💎 Feature 6: Coin Packages Store (`public/uploads/coin_packages`)

- **Location on Backend:** `public/uploads/coin_packages/*.svg`
- **Endpoint:** `GET /api/coin-packages`
- **Response Format (`200 OK`):**
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

# 8. 🎒 Feature 7: My Bag Items (`public/uploads/my_bag`)

- **Location on Backend:** `public/uploads/my_bag/*.svg`
- **Endpoint:** `GET /api/my-bag`
- **Response Format (`200 OK`):**
```json
{
  "status": true,
  "data": {
    "user_coins": 23400,
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

# 9. 👤 Feature 8: User Profile & Admin Avatar Directory

- **Upload Avatar API:** `POST /api/user/avatar` (Multipart `avatar` file or Base64 string).
- **Profile API:** `GET /api/user/me` (Returns `avatar_url`, `display_name`, `coins`, `level`).
- **Admin Panel View:** Admin directory at `/admin/users` automatically displays real user uploaded avatar photos with fallback initials.

---

# 10. 📋 Complete Flutter Integration Checklist

- [x] **Calling Engine:** Connects via `POST /api/calls` with dynamic token & joins Agora / WebRTC.
- [x] **Ringtone Audio:** Plays incoming ringtone & outgoing dial tone instantly via `audioplayers`.
- [x] **Loud, Crystal-Clear Speech:** Uses `enableSpeakerphone(true)` + `adjustPlaybackSignalVolume(400)`.
- [x] **HD Video:** Configures 720p 30fps encoder.
- [x] **App Logo:** Dynamically loads logo from `GET /api/app/config`.
- [x] **Floating VIP Banner:** Transparent draggable button from `GET /api/vip/floating-banner`.
- [x] **Gifts, Coins & Bag:** Displays `.png_url` via `CachedNetworkImage` with `.svg_url` fallback.

---
*(End of Master Documentation)*
