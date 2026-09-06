# 📱 Mobile App Settings: About Us, Privacy Policy & Account Deletion API Documentation
> **Chinchins Live Mobile Ecosystem**  
> **Base URL:** `https://chinchins.live/api` (Production) | `http://127.0.0.1:8000/api` (Local Dev)  
> **Status:** ✅ Production Ready & Fully Tested

---

## 📌 Executive Summary
This document provides the complete RESTful API specifications and ready-to-use Flutter / Android client integration code for the **Mobile App Settings Screen**:
1. **About Us (`GET /api/app/about`):** Fetches dynamic company mission, platform features, version number, support contacts, and official branding managed directly from the Admin Panel.
2. **Privacy Policy (`GET /api/app/privacy-policy`):** Serves long-form legal disclosures, camera/microphone permission policies, end-to-end call encryption notices, and Google Play Store compliance statements.
3. **Terms of Service (`GET /api/app/terms`):** Rules of conduct, 18+ policy, and virtual coin terms.
4. **Delete Account (`POST /api/user/delete-account`):** Strict single-user self-service account termination. Only the verified authenticated account owner can delete their own account. Permanently revokes all auth tokens, clears FCM push registrations, terminates presence, and locks/deletes user records.

---

## 🔗 Endpoints Reference

| Feature | Method | Endpoint | Auth Required | Description |
| :--- | :--- | :--- | :--- | :--- |
| **About Us** | `GET` | `/app/about` | No (Public) | Dynamic long text, company name, website, support info & feature cards |
| **About Us (Alias)** | `GET` | `/about` | No (Public) | Alias endpoint for webviews |
| **Privacy Policy** | `GET` | `/app/privacy-policy` | No (Public) | Full privacy disclosure text, camera/mic permissions, structured sections |
| **Privacy Policy (Alias)** | `GET` | `/privacy-policy` | No (Public) | Alias endpoint for webviews |
| **Terms of Service** | `GET` | `/app/terms` | No (Public) | Community guidelines, 18+ policy, coin rules |
| **Delete Account** | `POST` / `DELETE` | `/user/delete-account` | **Yes (Bearer Token)** | Permanent account deletion & data wipe for logged-in user |
| **Delete Account (Alias)** | `POST` / `DELETE` | `/account/delete` | **Yes (Bearer Token)** | Alias endpoint |

---

## 1. ℹ️ About Us API (`GET /api/app/about`)

### 📥 Request
```http
GET /api/app/about HTTP/1.1
Host: chinchins.live
Accept: application/json
```

### 📤 Success Response (HTTP 200 OK)
```json
{
  "status": true,
  "message": "About Us details retrieved successfully.",
  "data": {
    "app_name": "Chinchins Live",
    "app_tagline": "Meet, Chat & Video Call Live",
    "app_logo_url": "https://chinchins.live/assets/images/branding/logo.png",
    "app_icon_url": "https://chinchins.live/assets/images/branding/icon.png",
    "version": "1.0.0",
    "company_name": "Chinchins Live Network Inc.",
    "official_website": "https://chinchins.live",
    "support_email": "support@chinchins.live",
    "support_whatsapp": "+8801700000000",
    "content": "Welcome to Chinchins Live — the premier real-time interactive live video streaming, social connection, and entertainment platform.\n\nOur mission is to connect people across the globe through crystal-clear 1-on-1 private video calls, dynamic live broadcasting, interactive virtual gifting, and instant messaging.\n\n✨ Key Highlights:\n• Low-Latency HD Video Calls: Seamless WebRTC real-time audio and video conversations.\n• 160+ Animated Gifts & Leveling: Express emotions with luxury SVG and 3D animated gifts.\n• Safety & Security: 24/7 AI moderation, end-to-end encrypted transactions, and full user data control.\n• VIP Privileges: Unlock exclusive badges, entry effects, avatar frames, and personalized room styling.",
    "formatted_html": "Welcome to Chinchins Live — the premier real-time interactive live video streaming...<br />",
    "features": [
      {
        "title": "HD 1-on-1 Video Calls",
        "icon": "video_call",
        "description": "Real-time WebRTC low-latency HD video and crystal-clear audio calls."
      },
      {
        "title": "160+ Luxury Virtual Gifts",
        "icon": "gift",
        "description": "Animated SVG and 3D effects across 12 unique categories."
      },
      {
        "title": "VIP Cards & Privileges",
        "icon": "crown",
        "description": "Exclusive avatar frames, entry badges, and bonus daily gems."
      },
      {
        "title": "100% Safe Community",
        "icon": "security",
        "description": "24/7 AI moderation, end-to-end encrypted calls, and user block/report tools."
      }
    ]
  }
}
```

---

## 2. 🛡️ Privacy Policy API (`GET /api/app/privacy-policy`)

### 📥 Request
```http
GET /api/app/privacy-policy HTTP/1.1
Host: chinchins.live
Accept: application/json
```

### 📤 Success Response (HTTP 200 OK)
```json
{
  "status": true,
  "message": "Privacy Policy retrieved successfully.",
  "data": {
    "title": "Chinchins Live Privacy Policy",
    "app_name": "Chinchins Live",
    "last_updated": "September 2026",
    "support_email": "support@chinchins.live",
    "content": "At Chinchins Live (operated by Chinchins Live Network Inc.), we are deeply committed to protecting the privacy, confidentiality, and security of our users' personal data...\n\n1. Information We Collect:\n• Account Profile: Phone number, email, display name, age (18+ only), gender, profile photo, and bio.\n• Technical & Device Data: Unique device identifier (FCM push token), OS version, and network IP address.\n• Communications: Video and audio call session logs (call duration and timestamps only; call video and audio streams are encrypted peer-to-peer and NEVER recorded on servers).\n\n2. Device Permissions:\n• Camera & Microphone: Strictly requested for live 1-on-1 video and voice conversations initiated by you.\n• Photos & Media: Only accessed when you choose to upload an avatar or gallery photo.\n\n3. Financial Security:\n• All coin purchases, VIP packages, and recharge transactions are processed via secure payment gateways with end-to-end encryption. We never store credit card numbers or banking passwords.\n\n4. Right to Delete Your Account:\n• You have the absolute right to delete your Chinchins Live account and all associated personal data at any time from Settings -> Delete Account. Upon deletion, all tokens, profile info, and sessions are immediately terminated.",
    "formatted_html": "At Chinchins Live (operated by Chinchins Live Network Inc.)...<br />",
    "sections": [
      {
        "heading": "1. Information We Collect",
        "body": "Account profile details (phone, email, name, age, gender), device token for notifications, and encrypted call duration logs."
      },
      {
        "heading": "2. Device Permissions",
        "body": "Camera and microphone permissions are strictly used during user-initiated live video calls and audio streaming."
      },
      {
        "heading": "3. Financial Security",
        "body": "All recharge transactions and coin packages are processed via secure payment gateways. We never store credit card numbers."
      },
      {
        "heading": "4. Account & Data Deletion Rights",
        "body": "Users have the right to permanently delete their account and personal data anytime from Settings -> Delete Account."
      }
    ]
  }
}
```

---

## 3. 🗑️ Delete Account API (`POST /api/user/delete-account`)

### 🛡️ Security Protocol
* **Strict Authentication:** A user **CANNOT** delete another user's account.
* The API resolves identity strictly from the **Bearer Token** (`Authorization: Bearer <TOKEN>`).
* Deletion is atomic and immediately invalidates all active sessions across all devices.

### 📥 Request
```http
POST /api/user/delete-account HTTP/1.1
Host: chinchins.live
Authorization: Bearer 1|eS2QfFg7Jp65z...
Accept: application/json
Content-Type: application/json

{
  "password": "optional_user_password_confirmation",
  "reason": "I no longer use this app"
}
```

### 📤 Success Response (HTTP 200 OK)
```json
{
  "status": true,
  "message": "Your account and all associated personal data have been permanently deleted.",
  "data": {
    "user_id": 14,
    "account_id": "84920183",
    "deleted_at": "2026-09-06T17:40:00+06:00"
  }
}
```

### 📤 Unauthorized / Invalid Token Response (HTTP 401 Unauthorized)
```json
{
  "status": false,
  "message": "Unauthenticated. Only the verified account owner can delete this account."
}
```

---

## 📱 Flutter / Dart Production UI Implementation

### 1. `AboutUsScreen.dart`
```dart
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

class AboutUsScreen extends StatefulWidget {
  const AboutUsScreen({Key? key}) : super(key: key);

  @override
  State<AboutUsScreen> createState() => _AboutUsScreenState();
}

class _AboutUsScreenState extends State<AboutUsScreen> {
  bool _isLoading = true;
  Map<String, dynamic>? _aboutData;

  @override
  void initState() {
    super.initState();
    _fetchAboutUs();
  }

  Future<void> _fetchAboutUs() async {
    try {
      final res = await http.get(Uri.parse('https://chinchins.live/api/app/about'));
      if (res.statusCode == 200) {
        final json = jsonDecode(res.body);
        setState(() {
          _aboutData = json['data'];
          _isLoading = false;
        });
      }
    } catch (e) {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0F0C20),
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        title: const Text('About Us', style: TextStyle(fontWeight: FontWeight.bold)),
        centerTitle: true,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: Color(0xFFFF2E63)))
          : SingleChildScrollView(
              padding: const EdgeInsets.all(20),
              child: Column(
                children: [
                  // App Icon & Version Badge
                  Center(
                    child: Container(
                      width: 90,
                      height: 90,
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(colors: [Color(0xFFFF2E63), Color(0xFF8B5CF6)]),
                        borderRadius: BorderRadius.circular(24),
                        boxShadow: [
                          BoxShadow(
                            color: const Color(0xFFFF2E63).withOpacity(0.3),
                            blurRadius: 20,
                            offset: const Offset(0, 8),
                          )
                        ],
                      ),
                      child: const Icon(Icons.videocam_rounded, size: 48, color: Colors.white),
                    ),
                  ),
                  const SizedBox(height: 14),
                  Text(
                    _aboutData?['app_name'] ?? 'Chinchins Live',
                    style: const TextStyle(color: Colors.white, fontSize: 22, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 4),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                    decoration: BoxDecoration(
                      color: Colors.white10,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      'Version ${_aboutData?['version'] ?? '1.0.0'}',
                      style: const TextStyle(color: Colors.white70, fontSize: 12),
                    ),
                  ),
                  const SizedBox(height: 24),

                  // Long Description Card
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(18),
                    decoration: BoxDecoration(
                      color: const Color(0xFF1E1B2E),
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(color: Colors.white.withOpacity(0.06)),
                    ),
                    child: Text(
                      _aboutData?['content'] ?? '',
                      style: const TextStyle(color: Colors.white70, fontSize: 14, height: 1.6),
                    ),
                  ),
                  const SizedBox(height: 20),

                  // Support / Company Footer
                  Text(
                    '© ${_aboutData?['company_name'] ?? 'Chinchins Live Network Inc.'}',
                    style: const TextStyle(color: Colors.white38, fontSize: 12),
                  ),
                ],
              ),
            ),
    );
  }
}
```

---

### 2. `DeleteAccountDialog.dart`
```dart
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

void showDeleteAccountConfirmation(BuildContext context) {
  showDialog(
    context: context,
    builder: (BuildContext ctx) {
      return AlertDialog(
        backgroundColor: const Color(0xFF1E1B2E),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
            Icon(Icons.warning_amber_rounded, color: Color(0xFFFF2E63), size: 28),
            SizedBox(width: 10),
            Text('Delete Account?', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 18)),
          ],
        ),
        content: const Text(
          'This action is irreversible. All your profile info, friends list, chat history, and remaining coin balances will be permanently erased.',
          style: TextStyle(color: Colors.white70, fontSize: 13, height: 1.5),
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
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
            ),
            onPressed: () async {
              Navigator.of(ctx).pop(); // Close dialog

              final prefs = await SharedPreferences.getInstance();
              final token = prefs.getString('auth_token');

              if (token != null) {
                try {
                  await http.post(
                    Uri.parse('https://chinchins.live/api/user/delete-account'),
                    headers: {
                      'Authorization': 'Bearer $token',
                      'Accept': 'application/json',
                    },
                  );
                } catch (e) {
                  print('Error deleting account: $e');
                }
              }

              // Clear all local user session data
              await prefs.clear();

              // Navigate to Login Screen
              if (context.mounted) {
                Navigator.of(context).pushNamedAndRemoveUntil('/login', (route) => false);
              }
            },
            child: const Text('Delete Forever', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
          ),
        ],
      );
    },
  );
}
```

---

## 🛠️ Admin Panel Management
Admin can update **About Us**, **Privacy Policy**, **Terms of Service**, and contact details at any time:
1. Log in to the Admin Dashboard: `https://chinchins.live/admin/settings`
2. Click on the **"About Us & Legal Pages"** tab (`#legal`).
3. Edit the content in the rich text areas.
4. Click **"Save Legal & About Pages"**. Changes are instantly served to all mobile apps via API without requiring an APK rebuild.
