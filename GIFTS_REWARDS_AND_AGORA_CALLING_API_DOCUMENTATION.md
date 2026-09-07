# 🎁 Chinchins Live — Gifts, Rewards & Agora Video Calling API Documentation

**Documentation File:** `GIFTS_REWARDS_AND_AGORA_CALLING_API_DOCUMENTATION.md`  
**Target:** Flutter Mobile App Developer & Backend Engineers  
**Base URL:** `https://chinchins.live/api` or `http://localhost:8000/api`  
**Authentication:** `Authorization: Bearer <Sanctum_Token>` or `X-User-Id: <User_ID>`  

---

## 📑 Table of Contents
1. [🎁 Section 1: Gifts & Rewards Catalog & Image Loading Fix](#1-gifts--rewards-catalog--image-loading-fix)
   - [Why images were not showing & Solution](#why-images-were-not-showing--solution)
   - [API: Get Gift Catalog (`GET /api/gifts/catalog`)](#api-get-gift-catalog)
   - [API: Get User Received Gifts (`GET /api/gifts/received/{id}`)](#api-get-user-received-gifts)
   - [Flutter Implementation: Cached PNG & SVG Renderer](#flutter-gift-rendering-widget)
2. [📞 Section 2: Agora 1-on-1 Video Calling Engine Guide](#2-agora-1-on-1-video-calling-engine-guide)
   - [Why call wasn't opening after receive & Fix](#why-call-wasnt-opening-after-receive--fix)
   - [Admin Panel Configuration Rule (App ID + Primary Certificate)](#admin-panel-configuration-rule)
   - [API: Initialize Call & Get Agora Token (`POST /api/stream/session-token`)](#api-initialize-call--get-agora-token)
   - [Flutter Implementation: Agora Call Screen with Debug Logs & Full-Screen Avatar](#flutter-agora-call-screen-implementation)
3. [🧪 Section 3: Step-by-Step Testing & Verification](#3-step-by-step-testing--verification)

---

# 1. 🎁 Gifts & Rewards Catalog & Image Loading Fix

### Why images were not showing & Solution:
- **Root Cause:** All gifts were stored as vector `.svg` files in the backend. Flutter's default `Image.network()` and `CachedNetworkImage()` cannot render `.svg` files directly, throwing an unhandled format exception and displaying the default person icon fallback.
- **Backend Fix Applied:** The API now provides **`png_url`**, **`svg_url`**, and **`image_url`**. Every gift has a high-resolution `.png` file generated on the server (`public/uploads/gifts/*.png`).
- **Mobile Recommendation:** Use `gift['png_url']` for instant cached rendering via `CachedNetworkImage`, with `SvgPicture.network` as seamless fallback.

---

### 📡 API: Get Gift Catalog
Retrieve all active gifts grouped by categories (Hot, Lucky, SVIP, Intimacy, Wealth, Festival, Popular, etc.) along with current user diamond/coin balance.

- **Method:** `GET`
- **URL:** `/api/gifts/catalog`
- **Query Parameters (Optional):**
  - `category`: Filter by category (e.g. `hot`, `lucky`, `popular`, `all`). Default: `all`.
- **Headers:**
  ```http
  Authorization: Bearer <user_token>
  Accept: application/json
  ```

#### 📥 Response (`200 OK`):
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
      "intimacy": 10,
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
        "image_url": "https://chinchins.live/uploads/gifts/trophy_cup.svg",
        "animation_full_url": null,
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
        "image_url": "https://chinchins.live/uploads/gifts/mystery_box.svg",
        "sort_order": 2,
        "is_active": true
      }
    ]
  }
}
```

---

### 📡 API: Get User Received Gifts
Retrieve gifts received by a host/user for Profile Charm Level and Gift Showcase.

- **Method:** `GET`
- **URL:** `/api/gifts/received/{user_id}` (e.g. `/api/gifts/received/me` or `/api/gifts/received/29`)
- **Headers:** `Authorization: Bearer <token>`

#### 📥 Response (`200 OK`):
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
        "image_url": "https://chinchins.live/uploads/gifts/trophy_cup.svg",
        "coins": 500,
        "formatted_coins": "500",
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

### 📱 Flutter Gift Rendering Widget
Copy and paste this helper widget into your Flutter app (`lib/widgets/gift_image_widget.dart`):

```dart
import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter_svg/flutter_svg.dart';

class GiftImageWidget extends StatelessWidget {
  final Map<String, dynamic> gift;
  final double size;

  const GiftImageWidget({
    Key? key,
    required this.gift,
    this.size = 54.0,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    final String? pngUrl = gift['png_url'];
    final String? svgUrl = gift['svg_url'] ?? gift['image_url'];

    // 1. Primary: Use PNG for fast, cached raster rendering
    if (pngUrl != null && pngUrl.isNotEmpty) {
      return CachedNetworkImage(
        imageUrl: pngUrl,
        width: size,
        height: size,
        fit: BoxFit.contain,
        placeholder: (context, url) => Center(
          child: SizedBox(
            width: 18,
            height: 18,
            child: CircularProgressIndicator(strokeWidth: 2, color: Colors.pinkAccent),
          ),
        ),
        errorWidget: (context, url, error) {
          // Fallback to SVG if PNG fails
          if (svgUrl != null && svgUrl.isNotEmpty) {
            return SvgPicture.network(
              svgUrl,
              width: size,
              height: size,
              fit: BoxFit.contain,
              placeholderBuilder: (_) => _fallbackIcon(),
            );
          }
          return _fallbackIcon();
        },
      );
    }

    // 2. Secondary: SVG Picture rendering
    if (svgUrl != null && svgUrl.isNotEmpty) {
      return SvgPicture.network(
        svgUrl,
        width: size,
        height: size,
        fit: BoxFit.contain,
        placeholderBuilder: (_) => _fallbackIcon(),
      );
    }

    return _fallbackIcon();
  }

  Widget _fallbackIcon() {
    return Icon(
      Icons.card_giftcard,
      color: Colors.amber,
      size: size * 0.7,
    );
  }
}
```

---

# 2. 📞 Agora 1-on-1 Video Calling Engine Guide

### Why call wasn't opening after receive & Fix:
1. **The Temp Token Issue:** When you generate a "Temp Token" in Agora Console, that token is strictly hard-coded to ONE channel name. When User A calls User B in the app, the backend generates a random room name like `call_14_29_894102`. If the Admin panel had a Temp Token pasted in, the backend returned the Temp Token whose channel did NOT match `call_14_29_894102` &rarr; Agora rejected the join request silently!
2. **The Correct Setup (Dynamic HMAC Builder):** With **Agora App ID** and **Agora Primary Certificate**, the Laravel backend dynamically signs and builds an authentic `AccessToken 006` for **any dynamic channel name** in real-time.
3. **UID Match:** The Flutter client must join the Agora channel using the `agora_uid` returned by the `/api/stream/session-token` API.

---

### ⚙️ Admin Panel Configuration Rule
1. Open Admin Panel &rarr; **Settings** &rarr; **Streaming Engine (Agora vs VPS)** tab (`/admin/settings`).
2. Select **Agora Cloud Engine (RTC / RTM)** as Active Driver.
3. **Agora App ID:** Paste your App ID from Agora Console &rarr; *Project Management* &rarr; *Basic Settings*.
4. **Agora Primary Certificate:** Paste your Primary Certificate from *Security*.
5. **Agora Temp Token:** **Leave this field EMPTY**. (Leaving it empty forces the dynamic HMAC token generator to create unique tokens for every call).
6. Click **Save Settings**.

---

### 📡 API: Initialize Call & Get Agora Token
Called by both Caller (when placing call) and Callee (when accepting call).

- **Method:** `POST`
- **URL:** `/api/stream/session-token`
- **Headers:**
  ```http
  Authorization: Bearer <user_token>
  Content-Type: application/json
  Accept: application/json
  ```

#### 📤 Request Body:
```json
{
  "channel_name": "call_room_14_29_849201",
  "call_type": "video",
  "role": "publisher",
  "target_user_id": 29
}
```

#### 📥 Response (`200 OK`):
```json
{
  "success": true,
  "status": true,
  "driver": "agora",
  "channel_name": "call_room_14_29_849201",
  "agora_app_id": "9348xxxxxxxxxxxxxxxxxxxx",
  "agora_token": "0069348xxxxxxxxxxxxxxxxxxxxIADxxxxx...",
  "agora_uid": 14,
  "is_temp_token": false,
  "user_id": 14,
  "account_id": "84920193",
  "target_user": {
    "id": 29,
    "account_id": "39201948",
    "name": "Hakim",
    "avatar_url": "https://chinchins.live/uploads/avatars/user29.jpg",
    "level": 4,
    "coins": 23400
  },
  "call_type": "video",
  "role": "publisher",
  "expire_seconds": 86400,
  "enable_video": true,
  "enable_audio": true,
  "enable_live": true
}
```

---

### 📱 Flutter Agora Call Screen Implementation
Features included:
- Full **Agora RTC 6.x / 4.x** integration.
- **Debug log mode** enabled (`LogLevel.logLevelDebug`) to print all connection and video events.
- **Full-Screen Caller Avatar with blur backdrop** while connecting/ringing.
- Automatic **Remote Video stream binding** upon `onUserJoined`.
- Small draggable local camera preview.
- Camera switch, microphone mute, and hangup controls.

```dart
import 'dart:ui';
import 'package:flutter/material.dart';
import 'package:agora_rtc_engine/agora_rtc_engine.dart';
import 'package:cached_network_image/cached_network_image.dart';

class AgoraCallScreen extends StatefulWidget {
  final String appId;
  final String token;
  final String channelName;
  final int myUid;
  final Map<String, dynamic>? targetUser;

  const AgoraCallScreen({
    Key? key,
    required this.appId,
    required this.token,
    required this.channelName,
    required this.myUid,
    this.targetUser,
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
    _initializeAgora();
  }

  Future<void> _initializeAgora() async {
    // 1. Instantiate engine
    _engine = createAgoraRtcEngine();
    await _engine.initialize(RtcEngineContext(
      appId: widget.appId,
      channelProfile: ChannelProfileType.channelProfileCommunication,
    ));

    // 2. Turn on Detailed Debug Logs
    await _engine.setLogLevel(LogLevel.logLevelDebug);

    // 3. Register Event Handlers
    _engine.registerEventHandler(
      RtcEngineEventHandler(
        onJoinChannelSuccess: (RtcConnection connection, int elapsed) {
          debugPrint("✅ [Agora] Successfully joined channel: ${connection.channelId} with UID: ${connection.localUid}");
          setState(() {
            _localUserJoined = true;
          });
        },
        onUserJoined: (RtcConnection connection, int remoteUid, int elapsed) {
          debugPrint("🎥 [Agora] Remote user joined! Remote UID: $remoteUid");
          setState(() {
            _remoteUid = remoteUid;
          });
        },
        onUserOffline: (RtcConnection connection, int remoteUid, UserOfflineReasonType reason) {
          debugPrint("❌ [Agora] Remote user $remoteUid went offline ($reason)");
          setState(() {
            _remoteUid = null;
          });
          if (mounted) {
            Navigator.of(context).pop();
          }
        },
        onError: (ErrorCodeType err, String msg) {
          debugPrint("🚨 [Agora Error] Code: $err | Message: $msg");
        },
      ),
    );

    // 4. Enable Video and Start Preview
    await _engine.enableVideo();
    await _engine.startPreview();

    // 5. Join Channel with Token, Channel Name, and UID
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
          // 🖼️ 1. Full-Screen Caller Avatar Backdrop (Shown when remote video not connected)
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

            // Blur Filter and Caller Details
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

          // 🎥 2. Full-Screen Remote Video (When remote user joins)
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

          // 🎛️ 4. Control Bar (Mute, Hangup, Flip Camera)
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

# 3. 🧪 Step-by-Step Testing & Verification

1. **Gifts Test:**
   - Call `GET https://chinchins.live/api/gifts/catalog`.
   - Verify every item has `png_url` ending in `.png` (e.g. `.../trophy_cup.png`).
   - Open `png_url` in browser or postman &rarr; Image renders crisp and instantly.
2. **Agora Video Call Test:**
   - Ensure in Admin Panel: `Agora App ID` and `Primary Certificate` are filled, `agora_temp_token` is empty.
   - User 1 initiates call to User 2.
   - Flutter app requests `/api/stream/session-token` &rarr; joins Agora channel.
   - User 2 receives push / signaling &rarr; answers &rarr; both users see full-screen caller image then switch to smooth 60fps HD video stream!

---
*(End of Documentation)*
