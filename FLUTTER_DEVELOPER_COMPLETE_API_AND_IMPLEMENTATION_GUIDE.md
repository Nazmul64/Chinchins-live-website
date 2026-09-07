# 📱 Flutter Developer Complete RESTful API & Implementation Guide (A to Z)

**Documentation File:** `FLUTTER_DEVELOPER_COMPLETE_API_AND_IMPLEMENTATION_GUIDE.md`  
**Target:** Flutter Mobile App Engineers & Team Leads  
**Backend:** Laravel API Engine & Laravel Reverb  
**Base URL (Production):** `https://chinchins.live/api`  
**Base URL (Local/Staging):** `http://10.0.2.2:8000/api` (Android Emulator) or `http://localhost:8000/api`  
**Authentication:** `Authorization: Bearer <Sanctum_Token>` or `X-User-Id: <User_ID>`  

---

## 📑 Table of Contents
1. [📦 Required Flutter Packages (`pubspec.yaml`)](#1--required-flutter-packages)
2. [🎁 Feature 1: Gifts & Rewards Catalog & Rendering (A to Z)](#2--feature-1-gifts--rewards-catalog--rendering)
   - [RESTful APIs for Gifts](#restful-apis-for-gifts)
   - [Flutter Model: `GiftItem.dart`](#flutter-model-giftitemdart)
   - [Flutter Service: `GiftApiService.dart`](#flutter-service-giftapiservicedart)
   - [Flutter UI Widget: `GiftDisplayWidget.dart` (PNG + SVG Dual Support)](#flutter-ui-widget-giftdisplaywidgetdart)
3. [📞 Feature 2: Dual-Engine Calling System (WebRTC + Agora RTC)](#3--feature-2-dual-engine-calling-system-webrtc--agora-rtc)
   - [How Signaling (Reverb) vs Media Engine (Agora/WebRTC) Works](#how-signaling-vs-media-works)
   - [RESTful APIs for Calling](#restful-apis-for-calling)
   - [Flutter Service: `CallingApiService.dart`](#flutter-service-callingapiservicedart)
   - [Flutter Agora Video Call Screen: `AgoraCallScreen.dart`](#flutter-agora-video-call-screen-agoracallscreendart)
4. [🧪 Android & iOS Permission Configurations](#4--android--ios-permission-configurations)
5. [📋 Flutter Developer Checklist](#5--flutter-developer-checklist)

---

# 1. 📦 Required Flutter Packages

Ensure your `pubspec.yaml` contains these standard packages:

```yaml
dependencies:
  flutter:
    sdk: flutter

  # Networking & State
  http: ^1.2.1
  cached_network_image: ^3.3.1
  flutter_svg: ^2.0.10+1

  # Agora Real-Time Communication Engine (Latest 6.x)
  agora_rtc_engine: ^6.3.2

  # Device Permissions
  permission_handler: ^11.3.1
```

Run in terminal:
```bash
flutter pub get
```

---

# 2. 🎁 Feature 1: Gifts & Rewards Catalog & Rendering

### Problem Solved:
All backend gifts are available in both **PNG** (`png_url`) and **SVG** (`svg_url`). Flutter's `Image.network` fails on SVGs without an SVG parser. Using `png_url` ensures high-speed, cached image loading with 0% failure rate.

---

### RESTful APIs for Gifts

#### 1.1 Get Gifts Store Catalog
- **Endpoint:** `GET /api/gifts/catalog`
- **Headers:**
  ```http
  Authorization: Bearer <Sanctum_Token>
  Accept: application/json
  ```
- **Optional Query Parameters:** `?category=hot` or `?category=lucky` or `?category=all`

##### Response Body (`200 OK`):
```json
{
  "status": true,
  "message": "Gifts catalog loaded successfully.",
  "data": {
    "user_balance": {
      "coins": 23400,
      "formatted_coins": "23.40K"
    },
    "categories": {
      "all": 192,
      "hot": 71,
      "lucky": 20,
      "svip": 12,
      "popular": 14
    },
    "categories_list": [
      {
        "key": "all",
        "label": "All",
        "emoji": "🎁",
        "count": 192,
        "is_active": true
      },
      {
        "key": "hot",
        "label": "Hot",
        "emoji": "🔥",
        "count": 71,
        "is_active": false
      }
    ],
    "total_gifts": 192,
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
        "image_url": "https://chinchins.live/uploads/gifts/trophy_cup.png",
        "sort_order": 1,
        "is_active": true
      },
      {
        "id": 2,
        "name": "Mystery Box",
        "category": "hot",
        "coins": 888,
        "formatted_coins": "888",
        "badge": "MUST WIN",
        "png_url": "https://chinchins.live/uploads/gifts/mystery_box.png",
        "svg_url": "https://chinchins.live/uploads/gifts/mystery_box.svg",
        "image_url": "https://chinchins.live/uploads/gifts/mystery_box.png",
        "sort_order": 2,
        "is_active": true
      }
    ]
  }
}
```

---

#### 1.2 Get User's Received Gifts
- **Endpoint:** `GET /api/gifts/received/{user_id}` (Use `/api/gifts/received/me` for own profile)
- **Headers:** `Authorization: Bearer <Sanctum_Token>`

##### Response Body (`200 OK`):
```json
{
  "status": true,
  "message": "User received gifts retrieved.",
  "data": {
    "user": {
      "id": 29,
      "name": "Hakim",
      "avatar_url": "https://chinchins.live/uploads/avatars/user29.jpg"
    },
    "charm_level": {
      "level": 4,
      "badge": "Charm Lv.4",
      "total_coins": 23400
    },
    "total_gifts_count": 85,
    "gifts": [
      {
        "gift_id": 1,
        "name": "Trophy Cup",
        "category": "hot",
        "png_url": "https://chinchins.live/uploads/gifts/trophy_cup.png",
        "svg_url": "https://chinchins.live/uploads/gifts/trophy_cup.svg",
        "coins": 500,
        "quantity": 12,
        "count_label": "x12",
        "total_coins": 6000,
        "formatted_total": "6.00K"
      }
    ]
  }
}
```

---

### Flutter Model: `GiftItem.dart`

Save to `lib/models/gift_item.dart`:

```dart
class GiftItem {
  final int id;
  final String name;
  final String category;
  final int coins;
  final String formattedCoins;
  final String? badge;
  final String pngUrl;
  final String svgUrl;
  final String imageUrl;

  GiftItem({
    required this.id,
    required this.name,
    required this.category,
    required this.coins,
    required this.formattedCoins,
    this.badge,
    required this.pngUrl,
    required this.svgUrl,
    required this.imageUrl,
  });

  factory GiftItem.fromJson(Map<String, dynamic> json) {
    return GiftItem(
      id: json['id'] ?? json['gift_id'] ?? 0,
      name: json['name'] ?? '',
      category: json['category'] ?? 'all',
      coins: json['coins'] ?? 0,
      formattedCoins: json['formatted_coins'] ?? '${json['coins'] ?? 0}',
      badge: json['badge'],
      pngUrl: json['png_url'] ?? json['image_url'] ?? '',
      svgUrl: json['svg_url'] ?? json['image_url'] ?? '',
      imageUrl: json['image_url'] ?? '',
    );
  }
}
```

---

### Flutter Service: `GiftApiService.dart`

Save to `lib/services/gift_api_service.dart`:

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import '../models/gift_item.dart';

class GiftApiService {
  static const String baseUrl = 'https://chinchins.live/api';

  static Future<List<GiftItem>> fetchGiftCatalog({
    required String token,
    String category = 'all',
  }) async {
    final uri = Uri.parse('$baseUrl/gifts/catalog?category=$category');
    final response = await http.get(
      uri,
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      final List rawGifts = data['data']['gifts'] ?? [];
      return rawGifts.map((e) => GiftItem.fromJson(e)).toList();
    } else {
      throw Exception('Failed to load gifts: ${response.statusCode}');
    }
  }
}
```

---

### Flutter UI Widget: `GiftDisplayWidget.dart`

Save to `lib/widgets/gift_display_widget.dart`:

```dart
import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter_svg/flutter_svg.dart';
import '../models/gift_item.dart';

class GiftDisplayWidget extends StatelessWidget {
  final GiftItem gift;
  final double size;
  final bool isSelected;
  final VoidCallback? onTap;

  const GiftDisplayWidget({
    Key? key,
    required this.gift,
    this.size = 56.0,
    this.isSelected = false,
    this.onTap,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(6),
        decoration: BoxDecoration(
          color: isSelected ? const Color(0xFF2C2448) : const Color(0xFF1E1A34),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: isSelected ? const Color(0xFFFF3366) : Colors.transparent,
            width: 1.5,
          ),
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            // 🏷️ Badge (e.g. HOT, 520 LOVE, MUST WIN)
            if (gift.badge != null && gift.badge!.isNotEmpty)
              Align(
                alignment: Alignment.topLeft,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 2),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFF3366),
                    borderRadius: BorderRadius.circular(4),
                  ),
                  child: Text(
                    gift.badge!,
                    style: const TextStyle(color: Colors.white, fontSize: 8, fontWeight: FontWeight.bold),
                  ),
                ),
              ),

            // 🖼️ Gift Image (PNG Primary + SVG Fallback)
            Expanded(
              child: Center(
                child: gift.pngUrl.isNotEmpty
                    ? CachedNetworkImage(
                        imageUrl: gift.pngUrl,
                        width: size,
                        height: size,
                        fit: BoxFit.contain,
                        placeholder: (context, url) => const SizedBox(
                          width: 16,
                          height: 16,
                          child: CircularProgressIndicator(strokeWidth: 1.5, color: Colors.pinkAccent),
                        ),
                        errorWidget: (context, url, error) => SvgPicture.network(
                          gift.svgUrl,
                          width: size,
                          height: size,
                          fit: BoxFit.contain,
                          placeholderBuilder: (_) => const Icon(Icons.card_giftcard, color: Colors.amber),
                        ),
                      )
                    : SvgPicture.network(
                        gift.svgUrl,
                        width: size,
                        height: size,
                        fit: BoxFit.contain,
                        placeholderBuilder: (_) => const Icon(Icons.card_giftcard, color: Colors.amber),
                      ),
              ),
            ),
            const SizedBox(height: 4),

            // 📝 Gift Name
            Text(
              gift.name,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w600),
            ),

            // 💎 Coins
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Icon(Icons.diamond, size: 10, color: Colors.amber),
                const SizedBox(width: 3),
                Text(
                  gift.formattedCoins,
                  style: const TextStyle(color: Colors.amber, fontSize: 10, fontWeight: FontWeight.bold),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
```

---

# 3. 📞 Feature 2: Dual-Engine Calling System (WebRTC + Agora RTC)

### How Signaling vs Media Works:
```text
1. SIGNALING (Laravel Reverb WebSocket)
   ├── User A calls User B (Trigger incoming call ring)
   ├── User B accepts / rejects
   └── User A/B hangs up or cancels

2. MEDIA ENGINE (Dynamic Agora RTC / WebRTC)
   ├── Fetches Dynamic Session Token from Laravel: POST /api/calls
   ├── Joins Channel using Dynamic Channel Name & UID
   └── Agora transmits High-Definition Camera, Mic & Audio Tracks
```

---

### RESTful APIs for Calling

#### 2.1 Initialize Call Session
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
    "channel_name": "call_room_8f92a7c1", // Optional: Backend auto-generates if empty
    "call_type": "video",                 // "video" or "audio"
    "role": "publisher",
    "target_user_id": 29
  }
  ```

##### Response Body (`200 OK`):
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
  "token_expires_at": "2026-09-07T12:00:00+06:00",
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

#### 2.2 Token Refresh Endpoint
Called automatically when token is near expiry (e.g. at 50 minutes of a 60-minute token).

- **Endpoint:** `POST /api/agora/token/refresh`
- **Headers:** `Authorization: Bearer <Sanctum_Token>`
- **Request Body:**
  ```json
  {
    "channel_name": "call_room_8f92a7c1",
    "uid": 1025,
    "role": "publisher"
  }
  ```

##### Response Body (`200 OK`):
```json
{
  "success": true,
  "driver": "agora",
  "token": "006NEW_REFRESHED_TOKEN_HERE...",
  "rtc_token": "006NEW_REFRESHED_TOKEN_HERE...",
  "channel_name": "call_room_8f92a7c1",
  "uid": 1025,
  "expires_at": "2026-09-07T13:00:00+06:00",
  "expire_seconds": 3600
}
```

---

#### 2.3 Check Active Calling Driver
- **Endpoint:** `GET /api/stream/driver`
- **Response Body (`200 OK`):**
```json
{
  "success": true,
  "status": true,
  "data": {
    "active_driver": "agora",
    "is_agora": true,
    "is_vps_webrtc": false,
    "agora_app_id": "9348xxxxxxxxxxxxxxxxxxxx",
    "debug_mode": true,
    "sdk_logging": true,
    "log_level": "info"
  }
}
```

---

### Flutter Service: `CallingApiService.dart`

Save to `lib/services/calling_api_service.dart`:

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;

class CallingApiService {
  static const String baseUrl = 'https://chinchins.live/api';

  /// Initialize call session and obtain dynamic credentials
  static Future<Map<String, dynamic>> initializeCall({
    required String token,
    required int targetUserId,
    String callType = 'video',
    String? channelName,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/calls'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode({
        'target_user_id': targetUserId,
        'call_type': callType,
        if (channelName != null) 'channel_name': channelName,
      }),
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    } else {
      throw Exception('Failed to initialize call: ${response.body}');
    }
  }

  /// Refresh Agora RTC token
  static Future<String> refreshAgoraToken({
    required String token,
    required String channelName,
    required int uid,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/agora/token/refresh'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
      body: jsonEncode({
        'channel_name': channelName,
        'uid': uid,
      }),
    );

    final data = jsonDecode(response.body);
    return data['token'] ?? data['rtc_token'];
  }
}
```

---

### Flutter Agora Video Call Screen: `AgoraCallScreen.dart`

Save to `lib/screens/agora_call_screen.dart`:

```dart
import 'dart:ui';
import 'package:flutter/material.dart';
import 'package:agora_rtc_engine/agora_rtc_engine.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:permission_handler/permission_handler.dart';
import '../services/calling_api_service.dart';

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
    // 1. Request Camera and Microphone Permissions
    await [Permission.microphone, Permission.camera].request();

    // 2. Instantiate Agora RTC Engine
    _engine = createAgoraRtcEngine();
    await _engine.initialize(RtcEngineContext(
      appId: widget.appId,
      channelProfile: ChannelProfileType.channelProfileCommunication,
    ));

    // 3. Set Debug Logging Level
    LogLevel level = LogLevel.logLevelInfo;
    if (widget.logLevel == 'error') level = LogLevel.logLevelError;
    if (widget.logLevel == 'warning') level = LogLevel.logLevelWarn;
    if (widget.logLevel == 'verbose') level = LogLevel.logLevelDebug;
    await _engine.setLogLevel(level);

    // 4. Register Event Listeners
    _engine.registerEventHandler(
      RtcEngineEventHandler(
        onJoinChannelSuccess: (RtcConnection connection, int elapsed) {
          debugPrint("✅ [Agora Event] Local joined channel: ${connection.channelId} | Local UID: ${connection.localUid}");
          setState(() {
            _localUserJoined = true;
          });
        },
        onUserJoined: (RtcConnection connection, int remoteUid, int elapsed) {
          debugPrint("🎥 [Agora Event] Remote user connected: Remote UID: $remoteUid");
          setState(() {
            _remoteUid = remoteUid;
          });
        },
        onUserOffline: (RtcConnection connection, int remoteUid, UserOfflineReasonType reason) {
          debugPrint("❌ [Agora Event] Remote user $remoteUid left. Reason: $reason");
          setState(() {
            _remoteUid = null;
          });
          if (mounted) Navigator.of(context).pop();
        },
        onTokenPrivilegeWillExpire: (RtcConnection connection, String token) async {
          debugPrint("⚠️ [Agora Event] Token expiring. Renewing from backend...");
          try {
            final newToken = await CallingApiService.refreshAgoraToken(
              token: widget.userAuthToken,
              channelName: widget.channelName,
              uid: widget.myUid,
            );
            await _engine.renewToken(newToken);
            debugPrint("🔄 [Agora Event] Token successfully renewed!");
          } catch (e) {
            debugPrint("🚨 [Agora Token Refresh Error]: $e");
          }
        },
        onError: (ErrorCodeType err, String msg) {
          debugPrint("🚨 [Agora Engine Error]: Code: $err | Message: $msg");
        },
      ),
    );

    // 5. Enable Video & Preview
    await _engine.enableVideo();
    await _engine.startPreview();

    // 6. Join Channel using Dynamic Token, Channel Name, and UID
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

            // Blur Backdrop & Caller Profile
            Positioned.fill(
              child: BackdropFilter(
                filter: ImageFilter.blur(sigmaX: 18, sigmaY: 18),
                child: Container(
                  color: Colors.black.withOpacity(0.45),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Container(
                        padding: const EdgeInsets.all(4),
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          border: Border.all(color: Colors.pinkAccent, width: 2.5),
                        ),
                        child: CircleAvatar(
                          radius: 65,
                          backgroundImage: avatarUrl.isNotEmpty ? NetworkImage(avatarUrl) : null,
                          child: avatarUrl.isEmpty ? const Icon(Icons.person, size: 60, color: Colors.white) : null,
                        ),
                      ),
                      const SizedBox(height: 18),
                      Text(
                        targetName,
                        style: const TextStyle(color: Colors.white, fontSize: 24, fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 10),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: const [
                          SizedBox(
                            width: 14,
                            height: 14,
                            child: CircularProgressIndicator(strokeWidth: 2, color: Colors.pinkAccent),
                          ),
                          SizedBox(width: 10),
                          Text(
                            "Connecting high quality video...",
                            style: TextStyle(color: Colors.white70, fontSize: 14),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ],

          // 🎥 2. Full-Screen Remote Video Stream (When remote user joins)
          if (_remoteUid != null)
            AgoraVideoView(
              controller: VideoViewController.remote(
                rtcEngine: _engine,
                canvas: VideoCanvas(uid: _remoteUid),
                connection: RtcConnection(channelId: widget.channelName),
              ),
            ),

          // 📱 3. Floating Local Video Preview (Top Right)
          if (_localUserJoined)
            Positioned(
              top: 50,
              right: 20,
              width: 110,
              height: 160,
              child: ClipRRect(
                borderRadius: BorderRadius.circular(16),
                child: Container(
                  decoration: BoxDecoration(
                    border: Border.all(color: Colors.white.withOpacity(0.5), width: 1.5),
                  ),
                  child: AgoraVideoView(
                    controller: VideoViewController(
                      rtcEngine: _engine,
                      canvas: const VideoCanvas(uid: 0),
                    ),
                  ),
                ),
              ),
            ),

          // 🎛️ 4. Bottom Controls (Mute, End Call, Switch Camera)
          Positioned(
            bottom: 40,
            left: 0,
            right: 0,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: [
                // Mic Button
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
                // End Call Button
                FloatingActionButton(
                  backgroundColor: Colors.redAccent,
                  onPressed: () => Navigator.of(context).pop(),
                  child: const Icon(Icons.call_end, color: Colors.white, size: 30),
                ),
                // Camera Switch Button
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

# 4. 🧪 Android & iOS Permission Configurations

### Android (`android/app/src/main/AndroidManifest.xml`)
Add inside `<manifest>`:
```xml
<uses-permission android:name="android.permission.INTERNET"/>
<uses-permission android:name="android.permission.CAMERA"/>
<uses-permission android:name="android.permission.RECORD_AUDIO"/>
<uses-permission android:name="android.permission.MODIFY_AUDIO_SETTINGS"/>
<uses-permission android:name="android.permission.ACCESS_NETWORK_STATE"/>
<uses-permission android:name="android.permission.BLUETOOTH"/>
<uses-permission android:name="android.permission.BLUETOOTH_CONNECT"/>
```

### iOS (`ios/Runner/Info.plist`)
Add inside `<dict>`:
```xml
<key>NSCameraUsageDescription</key>
<string>Chinchins Live requires camera access for 1-on-1 video calling.</string>
<key>NSMicrophoneUsageDescription</key>
<string>Chinchins Live requires microphone access for voice and video calling.</string>
```

---

# 5. 📋 Flutter Developer Checklist

- [x] Use `gift['png_url']` for instant cached gift loading.
- [x] Call `POST /api/calls` to obtain the active calling driver (`agora` or `vps_webrtc`).
- [x] If `driver == 'agora'`, pass `app_id`, `token`, `channel_name`, and `uid` to `AgoraCallScreen`.
- [x] If `driver == 'vps_webrtc'`, proceed with existing WebRTC implementation.
- [x] Listen for `onTokenPrivilegeWillExpire` and call `POST /api/agora/token/refresh` to renew tokens automatically.
- [x] Ensure camera & microphone permissions are requested prior to joining Agora channel.

---
*(End of Full Guide)*
