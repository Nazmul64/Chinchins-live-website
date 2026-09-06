# 🚪 User Authentication & Logout RESTful API Documentation
> **Chinchins Live Mobile & Web Ecosystem**  
> **Base URL:** `https://chinchins.live/api` (Production) | `http://127.0.0.1:8000/api` (Local Dev)  
> **Status:** ✅ Production Ready & Fully Tested

---

## 📌 Executive Summary
The **User Logout API** provides a secure, bulletproof session termination mechanism for mobile apps (Flutter, Android Kotlin, iOS Swift, React Native) and web clients. When a user requests to log out:
1. **API Token Revocation:** The active Laravel Sanctum `Bearer` token is permanently deleted/invalidated from the database.
2. **Multi-Device Support:** Optionally revoke tokens on **all devices** simultaneously or only the **current active device**.
3. **Presence & Offline Status:** The user's status is immediately updated in the database (`is_active = false`, `online_status = 'offline'`, `is_busy = false`, `last_seen_at = now()`).
4. **Push Notification Unbinding:** Clears FCM / APNs device push tokens so logged-out users do not receive unwanted call or chat push alerts.
5. **Graceful Fail-Safe:** If a token is already expired or missing, the API still returns HTTP 200 so the mobile app client can safely complete local logout and route the user to the Login screen.

---

## 🔗 Endpoints Reference

| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :--- |
| `POST` / `GET` | `/logout` | Primary User Logout Endpoint | Optional / Bearer Token |
| `POST` / `GET` | `/auth/logout` | Auth Group Logout Alias | Optional / Bearer Token |
| `POST` / `GET` | `/user/logout` | User Namespace Logout Alias | Optional / Bearer Token |
| `GET` / `POST` | `/auth/me` | Check active session validity & get profile | Bearer Token |

---

## 1. 🚪 User Logout API (`POST /api/logout`)

### 📥 Request Headers
```http
POST /api/logout HTTP/1.1
Host: chinchins.live
Authorization: Bearer 1|eS2QfFg7Jp65zabc123456789...
Accept: application/json
Content-Type: application/json
```

### 📋 Request Body (Optional Parameters)
```json
{
  "all_devices": false,
  "clear_fcm": true
}
```

| Field | Type | Default | Description |
| :--- | :--- | :--- | :--- |
| `all_devices` | `boolean` | `false` | If `true`, revokes all auth tokens across all user devices. If `false`, only revokes current device token. |
| `clear_fcm` | `boolean` | `true` | If `true`, clears `fcm_token` and `device_token` from DB to prevent push notifications. |

---

### 📤 Success Response (HTTP 200 OK)
```json
{
  "status": true,
  "message": "Successfully logged out. Session terminated.",
  "data": {
    "user_id": 142,
    "account_id": "87452136",
    "online_status": "offline",
    "logged_out_at": "2026-09-06T14:30:00+06:00"
  }
}
```

### 📤 Graceful Response When Already Logged Out (HTTP 200 OK)
```json
{
  "status": true,
  "message": "Successfully logged out (Session already terminated).",
  "data": null
}
```

---

## 2. 👤 Verify Active Session API (`GET /api/auth/me`)

Used on App Launch to check if saved local token is still valid.

### 📥 Request Headers
```http
GET /api/auth/me HTTP/1.1
Host: chinchins.live
Authorization: Bearer 1|eS2QfFg7Jp65zabc123456789...
Accept: application/json
```

### 📤 Response (HTTP 200 OK - Valid)
```json
{
  "status": true,
  "message": "Session is valid and active.",
  "data": {
    "user": {
      "id": 142,
      "account_id": "87452136",
      "name": "Samia Khan",
      "nickname": "samia",
      "gender": "female",
      "age": 27,
      "level": "Lv4",
      "coins": 45000,
      "is_active": true,
      "online_status": "online"
    }
  }
}
```

### 📤 Response (HTTP 401 Unauthorized - Expired / Invalid)
```json
{
  "status": false,
  "message": "Unauthenticated or session expired."
}
```

---

## 📱 Flutter / Dart Production Implementation

Below is a complete, copy-paste ready Flutter service and UI dialog integration:

### 1. `AuthService.dart`
```dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class AuthService {
  static const String baseUrl = 'https://chinchins.live/api';

  /// Performs user logout on server, deletes local token, and cleans state
  static Future<bool> logout({bool logoutAllDevices = false}) async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('auth_token');

    if (token != null && token.isNotEmpty) {
      try {
        final url = Uri.parse('$baseUrl/logout');
        final response = await http.post(
          url,
          headers: {
            'Authorization': 'Bearer $token',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
          },
          body: jsonEncode({
            'all_devices': logoutAllDevices,
            'clear_fcm': true,
          }),
        ).timeout(const Duration(seconds: 8));

        print('Logout response: ${response.body}');
      } catch (e) {
        print('Server logout network error (proceeding with local cleanup): $e');
      }
    }

    // Always clear local session & caches
    await prefs.remove('auth_token');
    await prefs.remove('user_profile');
    await prefs.remove('user_id');
    await prefs.remove('fcm_token');

    return true;
  }
}
```

### 2. `LogoutConfirmationDialog.dart`
```dart
import 'package:flutter/material.dart';
import 'auth_service.dart';
import 'login_screen.dart';

void showLogoutConfirmation(BuildContext context) {
  showDialog(
    context: context,
    builder: (BuildContext ctx) {
      return AlertDialog(
        backgroundColor: const Color(0xFF1E1B2E),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
            Icon(Icons.logout_rounded, color: Color(0xFFFF2E63)),
            SizedBox(width: 10),
            Text(
              'Log Out',
              style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
            ),
          ],
        ),
        content: const Text(
          'Are you sure you want to log out of your Chinchins Live account?',
          style: TextStyle(color: Colors.white70, fontSize: 14),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(),
            child: const Text('Cancel', style: TextStyle(color: Colors.white54)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFFFF2E63),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
            ),
            onPressed: () async {
              Navigator.of(ctx).pop(); // Close dialog

              // Show loading overlay
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text('Logging out...'),
                  duration: Duration(seconds: 1),
                ),
              );

              // Perform API logout
              await AuthService.logout();

              // Navigate to Login Screen and clear route stack
              if (context.mounted) {
                Navigator.of(context).pushAndRemoveUntil(
                  MaterialPageRoute(builder: (context) => const LoginScreen()),
                  (Route<dynamic> route) => false,
                );
              }
            },
            child: const Text('Log Out', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
          ),
        ],
      );
    },
  );
}
```

---

## 🤖 Android (Kotlin / Retrofit) Implementation

```kotlin
interface AuthApiService {
    @POST("api/logout")
    suspend fun logout(
        @Header("Authorization") token: String,
        @Body request: LogoutRequest = LogoutRequest()
    ): Response<LogoutResponse>
}

data class LogoutRequest(
    val all_devices: Boolean = false,
    val clear_fcm: Boolean = true
)

data class LogoutResponse(
    val status: Boolean,
    val message: String,
    val data: LogoutData?
)

data class LogoutData(
    val user_id: Long,
    val account_id: String,
    val online_status: String,
    val logged_out_at: String
)
```

---

## 🌐 cURL Testing Examples

### Simple Logout:
```bash
curl -X POST https://chinchins.live/api/logout \
  -H "Authorization: Bearer YOUR_BEARER_TOKEN" \
  -H "Accept: application/json"
```

### Logout All Devices:
```bash
curl -X POST https://chinchins.live/api/logout \
  -H "Authorization: Bearer YOUR_BEARER_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"all_devices": true, "clear_fcm": true}'
```

---

## ✅ Best Practices for Mobile Developers
1. **Never block the UI:** Always perform local storage token deletion in the `finally` block even if the user has lost internet connection when clicking Log Out.
2. **Disconnect WebSockets / Reverb / Agora:** Make sure to call `pusher.disconnect()` or `agoraRtcEngine.leaveChannel()` immediately upon logout.
3. **Clear Navigation History:** Use `pushAndRemoveUntil` in Flutter or `Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK` in Android so pressing the device back button does not reopen authenticated screens.
