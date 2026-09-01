# 📱 Chinchins Live - In-App Updates, Remote Config & Real-Time Push Notification Engine API Documentation

> **Base URL:** `https://chinchins.live` or `http://your-vps-ip:8000`  
> **Prefix:** `/api`  
> **Content-Type:** `application/json`  
> **Accept:** `application/json`  
> **Authorization (Optional / Authenticated):** `Bearer <SANCTUM_TOKEN>` or query parameter `token=<TOKEN>`

---

## 📑 Index of Endpoints

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `POST` / `GET` | [`/api/app/check-update`](#1-in-app-ota-update-check) | Checks if new version exists, force update flag, changelog & download URL |
| `GET` | [`/api/app/remote-config`](#2-dynamic-remote-configuration-no-apk-rebuild) | Dynamic app configurations, branding, call rates, feature flags from VPS |
| `POST` | [`/api/app/device/register`](#3-universal-device-registration-push--call-wake) | Registers Android/iOS/Web device for background call ringing & push |
| `GET` / `POST` | [`/api/call/incoming`](#4-instant-incoming-call-detector-vps-real-time) | Real-time incoming call listener (Continuous ringtone & IMO-style UI) |
| `GET` | [`/api/notifications`](#5-notifications-list--unread-count) | Real-time notification list (Profile views, messages, gifts, updates) |
| `POST` | [`/api/notifications/test-push`](#6-test-push-notification) | Debug endpoint to test push/ringing payload delivery |

---

## 1. 🚀 In-App OTA Update Check

Call this endpoint during app initialization (Splash screen or Main Screen `initState`) to check whether a new version is available or whether a mandatory (force) update is required.

### Request
`POST /api/app/check-update` or `GET /api/app/check-update`

```json
{
  "app_version": "1.0.0",
  "version_code": 1,
  "platform": "android"
}
```

### Headers (Optional)
```http
X-App-Version: 1.0.0
X-App-Version-Code: 1
```

### Response: Update Available (`200 OK`)
```json
{
  "status": true,
  "message": "New update available!",
  "data": {
    "has_update": true,
    "force_update": false,
    "latest_version": "1.0.2",
    "latest_version_code": 2,
    "min_supported_version": "1.0.0",
    "title": "Exciting New Features & Live Updates! 🎉",
    "changelog": "• IMO-style instant incoming call ringing\n• Profile visitor real-time alerts\n• Faster video calling and new gifts",
    "download_url": "https://chinchins.live/downloads/chinchins_live_v1.0.2.apk",
    "file_size": "25 MB",
    "remote_flags": {
      "enable_video_calling": true,
      "enable_audio_calling": true,
      "enable_random_matching": true,
      "enable_instant_call_wake": true,
      "enable_push_notifications": true,
      "enable_in_app_updates": true,
      "enable_profile_view_alert": true,
      "enable_auto_chat_greetings": true,
      "maintenance_mode": false
    },
    "current_installed_version": "1.0.0",
    "current_installed_version_code": 1,
    "server_time": "2026-09-01T22:25:34+06:00",
    "branding": {
      "app_name": "Chinchins Live",
      "app_tagline": "Meet, Chat & Video Call Live",
      "app_logo_url": "https://chinchins.live/assets/images/branding/logo.png",
      "free_messages_limit": 5,
      "message_coin_cost": 5
    }
  }
}
```

### Response: App is Up to Date (`200 OK`)
```json
{
  "status": true,
  "message": "App is up to date.",
  "data": {
    "has_update": false,
    "force_update": false,
    "latest_version": "1.0.0",
    "latest_version_code": 1,
    "min_supported_version": "1.0.0",
    "title": "Up to date",
    "changelog": "",
    "download_url": null,
    "file_size": null,
    "remote_flags": {
      "enable_video_calling": true,
      "enable_audio_calling": true,
      "enable_random_matching": true,
      "enable_instant_call_wake": true,
      "enable_push_notifications": true,
      "enable_in_app_updates": true,
      "enable_profile_view_alert": true,
      "enable_auto_chat_greetings": true,
      "maintenance_mode": false
    }
  }
}
```

---

## 2. ⚡ Dynamic Remote Configuration (No APK Rebuild)

Fetch all dynamic feature toggles, coin rates, call trial settings, and media URLs dynamically from the VPS server without recompiling the mobile app.

### Request
`GET /api/app/remote-config` or `GET /api/app/config`

### Response (`200 OK`)
```json
{
  "status": true,
  "data": {
    "app_name": "Chinchins Live",
    "app_tagline": "Meet, Chat & Video Call Live",
    "app_logo_url": "https://chinchins.live/assets/images/branding/logo.png",
    "app_icon_url": "https://chinchins.live/assets/images/branding/icon.png",
    "latest_version": "1.0.2",
    "free_messages_limit": 5,
    "message_coin_cost": 5,
    "video_call_rate": 100,
    "audio_call_rate": 60,
    "free_trial_duration": 15,
    "incoming_ringtone": "https://chinchins.live/assets/audio/incoming_call.mp3",
    "outgoing_ringtone": "https://chinchins.live/assets/audio/outgoing_ring.mp3",
    "remote_flags": {
      "enable_video_calling": true,
      "enable_audio_calling": true,
      "enable_random_matching": true,
      "enable_instant_call_wake": true,
      "enable_push_notifications": true,
      "enable_in_app_updates": true,
      "enable_profile_view_alert": true,
      "enable_auto_chat_greetings": true,
      "maintenance_mode": false
    },
    "support_email": "support@chinchins.live",
    "support_whatsapp": "+8801700000000"
  }
}
```

---

## 3. 📲 Universal Device Registration (Push & Call Wake)

Call this endpoint when the app starts or when the user logs in to register their device specifications and push token directly to the VPS server.

### Request
`POST /api/app/device/register` or `POST /api/device/register`

```json
{
  "user_id": 1,
  "fcm_token": "eXample_FCM_Or_Device_Push_Token_String_Here",
  "device_type": "android",
  "device_brand": "Samsung",
  "device_model": "Galaxy S24 Ultra",
  "os_version": "Android 14",
  "app_version": "1.0.0",
  "device_id": "unique-hardware-device-uuid"
}
```

### Response (`200 OK`)
```json
{
  "status": true,
  "message": "Device registered successfully for high-priority push notifications and incoming calls.",
  "data": {
    "device_id": 1,
    "user_id": 1,
    "device_type": "android",
    "is_active": true
  }
}
```

---

## 4. 📞 Instant Incoming Call Detector (VPS Real-Time)

When a call is initiated on VPS, the server triggers real-time broadcast and prepares the incoming call queue. The receiver app polls this endpoint (every 1.5s when app is open or triggered via push notification) to immediately launch the **Full-Screen IMO-Style Ringing UI**.

### Request
`GET /api/call/incoming` or `POST /api/call/incoming`

```json
{
  "user_id": 2
}
```

### Response: Active Incoming Call (`200 OK`)
```json
{
  "status": true,
  "has_incoming_call": true,
  "is_incoming": true,
  "call_session": {
    "id": 48,
    "channel_name": "call_video_1_2_1725221940_AbCd",
    "call_type": "video",
    "status": "ringing",
    "rate_per_minute": 100,
    "is_free_trial": true,
    "free_duration_seconds": 15,
    "incoming_ringtone_url": "https://chinchins.live/assets/audio/incoming_call.mp3",
    "caller": {
      "id": 1,
      "account_id": "1000284918",
      "display_name": "Rahim Ahmed",
      "avatar": "https://chinchins.live/uploads/avatars/user_1.jpg",
      "gender": "male"
    }
  }
}
```

---

## 5. 🔔 Notifications List & Unread Count

Fetch real-time notifications for:
1. `profile_view` — "X viewed your profile! Say hi!"
2. `message` — "Message from X"
3. `gift` — "Received [Gift] from X (+Coins)"
4. `call` — "Missed call from X"

### Request
`GET /api/notifications` or `GET /api/user/notifications`

### Response (`200 OK`)
```json
{
  "status": true,
  "data": {
    "unread_count": 3,
    "notifications": [
      {
        "id": 12,
        "type": "profile_view",
        "title": "New Profile Visitor 👁️",
        "message": "Nabila Sultana viewed your profile!",
        "is_read": false,
        "created_at": "2026-09-01T22:20:10+06:00",
        "data": {
          "viewer_id": 5,
          "name": "Nabila Sultana",
          "avatar_url": "https://chinchins.live/uploads/avatars/nabila.jpg",
          "is_online": true
        }
      },
      {
        "id": 11,
        "type": "gift",
        "title": "New Gift Received! 🎁",
        "message": "Sajid sent you 1x Luxury Sports Car (+2500 coins)!",
        "is_read": false,
        "created_at": "2026-09-01T22:15:00+06:00",
        "data": {
          "gift_name": "Luxury Sports Car",
          "gift_icon": "https://chinchins.live/assets/images/gifts/car.png",
          "coins_earned": 2500
        }
      }
    ]
  }
}
```

---

## 6. 🧪 Test Push Notification

Use this endpoint to verify that device tokens and push alerts are successfully firing.

### Request
`POST /api/notifications/test-push`

```json
{
  "user_id": 1,
  "type": "incoming_call",
  "title": "Test Call Alert 📞",
  "body": "Incoming test video call ring from server."
}
```

---

## 📱 Flutter Implementation Snippet

### In-App Update Service
```dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:url_launcher/url_launcher.dart';

class AppUpdateService {
  static const String baseUrl = 'https://chinchins.live/api';

  static Future<void> checkForUpdates(BuildContext context) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/app/check-update'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'app_version': '1.0.0',
          'version_code': 1,
          'platform': 'android',
        }),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body)['data'];
        final bool hasUpdate = data['has_update'] ?? false;
        final bool forceUpdate = data['force_update'] ?? false;

        if (hasUpdate) {
          showUpdateDialog(
            context: context,
            title: data['title'] ?? 'Update Available',
            changelog: data['changelog'] ?? '',
            downloadUrl: data['download_url'] ?? '',
            forceUpdate: forceUpdate,
          );
        }
      }
    } catch (e) {
      print('Update check error: $e');
    }
  }

  static void showUpdateDialog({
    required BuildContext context,
    required String title,
    required String changelog,
    required String downloadUrl,
    required bool forceUpdate,
  }) {
    showDialog(
      context: context,
      barrierDismissible: !forceUpdate,
      builder: (ctx) => WillPopScope(
        onWillPop: () async => !forceUpdate,
        child: AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          title: Text(title, style: const TextStyle(fontWeight: FontWeight.bold)),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text('What\'s New:', style: TextStyle(fontWeight: FontWeight.w600)),
              const SizedBox(height: 8),
              Text(changelog),
            ],
          ),
          actions: [
            if (!forceUpdate)
              TextButton(
                onPressed: () => Navigator.pop(ctx),
                child: const Text('Later'),
              ),
            ElevatedButton(
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFFFF2D55),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(25)),
              ),
              onPressed: () async {
                final uri = Uri.parse(downloadUrl);
                if (await canLaunchUrl(uri)) {
                  await launchUrl(uri, mode: LaunchMode.externalApplication);
                }
              },
              child: const Text('Download & Update', style: TextStyle(color: Colors.white)),
            ),
          ],
        ),
      ),
    );
  }
}
```

---

## 🎯 Summary for Mobile App Developer

1. **On App Start (`main.dart` / Splash):**
   - Call `GET /api/app/remote-config` to load live configs, branding, and feature toggles.
   - Call `POST /api/app/check-update` with the current APK `app_version` & `version_code`.
   - Call `POST /api/app/device/register` to register the device token.

2. **Incoming Calls & Background Wake-Up:**
   - When device receives incoming call signal, play `data.incoming_ringtone_url` continuously and open `VideoCallScreen` (or `AudioCallScreen`) in incoming mode.
   - User tapping **Accept** connects via WebRTC to `channel_name`.
