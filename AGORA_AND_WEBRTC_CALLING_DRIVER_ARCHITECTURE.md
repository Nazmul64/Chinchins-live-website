# 🎙️ Agora RTC & VPS WebRTC Dual-Engine Calling Driver Architecture

**Document:** `AGORA_AND_WEBRTC_CALLING_DRIVER_ARCHITECTURE.md`  
**System:** Chinchins Live — Laravel Backend & Flutter Mobile Client  
**Version:** 2.0 (Dynamic Driver Architecture)  
**Status:** Production Ready  

---

## 📌 Executive Summary & Architecture Philosophy
1. **Existing WebRTC + Laravel Reverb System Intact:** The existing WebRTC audio/video calling, Reverb signaling channels, ICE negotiation, and signaling events are completely preserved and unmodified.
2. **Pluggable Calling Driver Architecture:** Backend uses a modular driver pattern (`CallingManager` &rarr; `WebRTCDriver` / `AgoraDriver`).
3. **1-Click Engine Selection in Admin Panel:** Admin can switch the active media engine between **Hostinger VPS WebRTC** and **Agora Cloud Engine** with zero downtime and without needing to rebuild or re-release the Flutter mobile APK.
4. **Signaling vs Media Separation:**
   - **Laravel Reverb:** Handles all signaling (incoming call notification, call request, accept, reject, cancel, end, user presence).
   - **Agora RTC / WebRTC:** Handles pure audio/video media transmission (camera, microphone, remote tracks).
5. **Security & Dynamic Token Generation:**
   - **Agora Primary Certificate:** Stored only on the server, NEVER exposed to Flutter client.
   - **Dynamic Channel & UID:** Laravel dynamically generates unique channel names (e.g. `call_8f92a7c1`) and stable non-conflicting numeric UIDs per call.
   - **Dynamic HMAC-SHA256 RTC Token:** Automatically generated on every call; hard-coded Temp Tokens are NOT used.
   - **Token Refresh Support:** Dedicated token refresh endpoint prevents sudden call disconnection during extended conversations.

---

## 🏛️ Calling Architecture Diagram

```text
               ┌──────────────────────────────────────────────┐
               │          Admin Panel Settings               │
               │   Calling Driver: [ WebRTC | Agora ]         │
               │   App ID, Primary Certificate, Expiry, Logs │
               └──────────────────────┬───────────────────────┘
                                      │
                                      ▼
                        ┌───────────────────────────┐
                        │   Laravel CallingManager  │
                        └─────────────┬─────────────┘
                                      │
             ┌────────────────────────┴────────────────────────┐
             ▼                                                 ▼
┌─────────────────────────┐                       ┌─────────────────────────┐
│      WebRTCDriver       │                       │       AgoraDriver       │
│  (Hostinger VPS Reverb) │                       │   (Dynamic HMAC Token)  │
└────────────┬────────────┘                       └────────────┬────────────┘
             │                                                 │
             │ Signaling (Reverb)                              │ Signaling (Reverb)
             │ Media: VPS WebRTC P2P                           │ Media: Agora Global SD-RTN™
             ▼                                                 ▼
┌───────────────────────────────────────────────────────────────────────────┐
│                                Flutter App                                │
│       - Receives Driver Type + Dynamic Credentials from Laravel API       │
│       - Uses Reverb for Ringing / Signaling                               │
│       - Mounts WebRTC or Agora RTC Engine for Audio / Video               │
└───────────────────────────────────────────────────────────────────────────┘
```

---

## ⚙️ Admin Panel Configuration

**Location:** Admin Panel &rarr; Settings &rarr; **Streaming Engine (Agora vs VPS)** (`/admin/settings?tab=streaming`)

| Field Name | Type | Description |
| :--- | :--- | :--- |
| **Active Calling Driver** | Radio (`vps_webrtc` / `agora`) | Switches between VPS WebRTC and Agora Cloud. |
| **Agora Status** | Toggle (`Enable` / `Disable`) | Master switch for Agora availability. |
| **Agora Project Name** | Text | Project Name (e.g. `Default Project`). |
| **Agora App ID** | Text (Required for Agora) | App ID from Agora Console Basic Settings. |
| **Agora Primary Certificate** | Password (Required for Agora) | Secret key for server-side HMAC token generation. |
| **Token Expiry Duration** | Number (Seconds) | Default `3600` (1 hour). |
| **Enable Debug Mode** | Toggle (`ON` / `OFF`) | Logs calls, UIDs, channels, and tokens in Laravel log. |
| **Agora SDK Logging** | Toggle (`ON` / `OFF`) | Directs Flutter SDK to output engine diagnostics. |
| **Log Level** | Select (`error`, `warning`, `info`, `verbose`) | Verbosity of client-side logs. |

---

## 📡 API Endpoints Specification

### 1. Initialize Call Session
Called by both Caller (when placing call) and Receiver (when answering call).

- **Method:** `POST`
- **URL:** `/api/calls` (Aliases: `/api/calls/initiate`, `/api/stream/session-token`)
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

#### 📥 Response when Active Driver is `agora`:
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
  "call_type": "video",
  "role": "publisher"
}
```

#### 📥 Response when Active Driver is `vps_webrtc`:
```json
{
  "success": true,
  "status": true,
  "driver": "vps_webrtc",
  "channel_name": "call_8f92a7c1",
  "user_id": 14,
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
  },
  "call_type": "video"
}
```

---

### 2. Token Refresh Endpoint
Called by Flutter client before token expiry to prevent call drops.

- **Method:** `POST`
- **URL:** `/api/agora/token/refresh` (Alias: `/api/stream/token/refresh`)
- **Headers:** `Authorization: Bearer <Sanctum_Token>`
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
    "token": "006NEW_DYNAMIC_TOKEN_HERE...",
    "rtc_token": "006NEW_DYNAMIC_TOKEN_HERE...",
    "channel_name": "call_8f92a7c1",
    "uid": 1025,
    "expires_at": "2026-09-07T13:00:00+06:00",
    "expire_seconds": 3600
  }
  ```

---

### 3. Check Active Driver Configuration
- **Method:** `GET`
- **URL:** `/api/stream/driver` (Alias: `/api/v1/config/streaming-driver`)
- **Response:**
  ```json
  {
    "success": true,
    "status": true,
    "data": {
      "active_driver": "agora",
      "is_agora": true,
      "is_vps_webrtc": false,
      "agora_app_id": "9348xxxxxxxxxxxxxxxxxxxx",
      "token_expire_seconds": 3600,
      "debug_mode": true,
      "sdk_logging": true,
      "log_level": "info",
      "enable_video_call": true,
      "enable_audio_call": true
    }
  }
  ```

---

## 📱 Flutter Implementation Guidelines

### 1. Dual-Driver Decision Router
```dart
Future<void> startCallSession({
  required int targetUserId,
  required String callType, // 'video' or 'audio'
}) async {
  final response = await http.post(
    Uri.parse('$baseUrl/api/calls'),
    headers: {'Authorization': 'Bearer $authToken', 'Content-Type': 'application/json'},
    body: jsonEncode({
      'target_user_id': targetUserId,
      'call_type': callType,
    }),
  );

  final data = jsonDecode(response.body);
  final String driver = data['driver'] ?? 'vps_webrtc';

  if (driver == 'agora') {
    // Launch Agora RTC Call Screen
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => AgoraCallScreen(
          appId: data['app_id'] ?? data['agora_app_id'],
          token: data['token'] ?? data['rtc_token'],
          channelName: data['channel_name'],
          myUid: data['uid'] ?? data['agora_uid'],
          targetUser: data['target_user'],
          debugMode: data['debug_mode'] ?? false,
          logLevel: data['log_level'] ?? 'info',
        ),
      ),
    );
  } else {
    // Launch Existing WebRTC Call Screen (Unchanged)
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => ExistingWebRTCCallScreen(
          channelName: data['channel_name'],
          targetUser: data['target_user'],
        ),
      ),
    );
  }
}
```

### 2. Agora Event Listeners & Debug Logging
```dart
_engine.registerEventHandler(
  RtcEngineEventHandler(
    onJoinChannelSuccess: (RtcConnection connection, int elapsed) {
      debugPrint("✅ [Agora Event] Joined channel ${connection.channelId} with UID: ${connection.localUid}");
    },
    onUserJoined: (RtcConnection connection, int remoteUid, int elapsed) {
      debugPrint("🎥 [Agora Event] Remote user joined: UID $remoteUid");
      setState(() => _remoteUid = remoteUid);
    },
    onUserOffline: (RtcConnection connection, int remoteUid, UserOfflineReasonType reason) {
      debugPrint("❌ [Agora Event] Remote user $remoteUid left. Reason: $reason");
      setState(() => _remoteUid = null);
      Navigator.of(context).pop();
    },
    onTokenPrivilegeWillExpire: (RtcConnection connection, String token) async {
      debugPrint("⚠️ [Agora Event] Token expiring! Auto-refreshing from Laravel backend...");
      final refreshRes = await http.post(
        Uri.parse('$baseUrl/api/agora/token/refresh'),
        headers: {'Authorization': 'Bearer $authToken', 'Content-Type': 'application/json'},
        body: jsonEncode({
          'channel_name': connection.channelId,
          'uid': connection.localUid,
        }),
      );
      final refreshData = jsonDecode(refreshRes.body);
      final String newToken = refreshData['token'];
      await _engine.renewToken(newToken);
      debugPrint("🔄 [Agora Event] Token renewed successfully.");
    },
    onError: (ErrorCodeType err, String msg) {
      debugPrint("🚨 [Agora SDK Error] Code: $err | Message: $msg");
    },
  ),
);
```

---

## 🔒 Security Best Practices
1. **Never expose `Primary Certificate` in API responses or logs.**
2. **Dynamic Tokens only:** Every call session receives an ephemeral HMAC token valid for the duration configured by the admin (default 3600 seconds).
3. **UID Safety:** UIDs are numeric, positive integers mapped directly to user IDs to avoid collision in 1-on-1 calls.

---
*(End of Architecture Documentation)*
