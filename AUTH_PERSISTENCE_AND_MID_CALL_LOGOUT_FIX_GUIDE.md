# 🔐 Persistent Login, Auto-Authentication & In-Call Logout Bug Fix Guide
## 🇧🇩 একবার লগইন করলে চিরস্থায়ী লগইন থাকা এবং ভিডিও কলের মধ্যে লগআউট সমস্যা সমাধানের পূর্ণাঙ্গ গাইড

> **সমস্যার মূল বিশ্লেষণ (Root Cause Analysis):**
> 1. **অ্যাপ ওপেন করলে বারবার লগইন চাওয়া:** Flutter অ্যাপে ইউজার লগইন/রেজিস্ট্রেশনের পর পাওয়া `Bearer Token` লোকাল স্টোরেজে (`SharedPreferences` বা `FlutterSecureStorage`) পার্মানেন্টলি সেভ না থাকা এবং অ্যাপ স্টার্টআপে (`SplashScreen`) টোকেন চেক করে ডিরেক্ট হোমপেজে রিডাইরেক্ট না করা।
> 2. **ভিডিও কলের মাঝে হঠাৎ লগআউট হয়ে যাওয়া:** ভিডিও কলের সময় ব্যাকগ্রাউন্ডে প্রতি মিনিটে কয়েন কাটার জন্য (`/api/call/deduct-interval` বা `/api/call/pulse`) এবং অনলাইন প্রেজেন্সের জন্য (`/api/presence/heartbeat`) রিকোয়েস্ট পাঠানো হয়। নেটওয়ার্ক ফ্ল্যাকচুয়েশনের কারণে বা কোনো এপিআই এরর হলে Flutter অ্যাপের গ্লোবাল `HTTP / Dio Interceptor` সব ধরনের এররকে "Auth Error" মনে করে জোরপূর্বক টোকেন ডিলিট করে ইউজারকে লগইন পেজে ছুঁড়ে ফেলে দেয়।

---

## 📑 সূচিপত্র (Table of Contents)
1. [সমস্যার মূল কারণ ও আর্কিটেকচার (Core Causes & Architectural Solution)](#1-সমস্যার-মূল-কারণ-ও-আর্কিটেকচার)
2. [অ্যাপ স্টার্টআপ ও অটো-লগইন ফ্লোচার্ট (Auto-Login Flow)](#2-অ্যাপ-স্টার্টআপ-ও-অটো-লগইন-ফ্লোচার্ট)
3. [Laravel RESTful API এন্ডপয়েন্টস (Backend Reference)](#3-laravel-restful-api-এন্ডপয়েন্টস)
   - ৩.১. টোকেন ও সেশন ভেরিফিকেশন (`GET /api/auth/check` বা `GET /api/auth/me`)
   - ৩.২. লগইন ও রেজিস্ট্রেশন রেসপন্স (`POST /api/login`, `POST /api/register`)
   - ৩.৩. এক্সপ্লিসিট লগআউট (`POST /api/logout`)
4. [Flutter Developer-এর জন্য স্টেপ-বাই-স্টেপ সমাধান ও রেডি কোড](#4-flutter-developer-এর-জন্য-স্টেপ-বাই-স্টেপ-সমাধান-ও-রেডি-কোড)
   - ৪.১. `AuthStorageService` (টোকেন ও ইউজার ডাটা পার্মানেন্টলি সেভ রাখা)
   - ৪.২. `SplashScreen` / `AuthGate` (অটো-লগইন হ্যান্ডলার)
   - ৪.৩. গ্লোবাল `Dio/Http Interceptor` ফিক্স (মিড-কল লগআউট বন্ধ করার সমাধান)
   - ৪.৪. ভিডিও কলের মাঝে বিলিং এরর সেফ হ্যান্ডলিং (`video_call_screen.dart`)
   - ৪.৫. ইউজার প্রোফাইল থেকে ম্যানুয়াল লগআউট হ্যান্ডলার
5. [ডেভেলপারের চেকলিস্ট (Actionable Checklist)](#5-ডেভেলপারের-চেকলিস্ট)

---

## 1. সমস্যার মূল কারণ ও আর্কিটেকচার

### সমস্যা ১: অ্যাপ ওপেন করলেই বারবার লগইন চায় কেন?
- **কারণ:** Flutter অ্যাপে যখন ইউজার লগইন করে, তখন টোকেনটি শুধুমাত্র অ্যাপের মেমোরিতে (ইন-মেমোরি স্টেট যেমন সাধারণ ভেরিয়েবল) থাকে। ব্যবহারকারী অ্যাপ বন্ধ করে দিলে বা ব্যাকগ্রাউন্ড থেকে রিমুভ করলে সেই ভেরিয়েবল মুছে যায়।
- **সমাধান:** লগইন/রেজিস্ট্রেশনের সাথে সাথে `token`, `user_id`, এবং `user_data` কে `flutter_secure_storage` অথবা `shared_preferences`-এ সেভ করতে হবে। অ্যাপ চালু হওয়ার সময় `main.dart` সরাসরি `LoginScreen`-এ না গিয়ে একটি `SplashScreen`-এ যাবে, সেখানে টোকেন থাকলে স্বয়ংক্রিয়ভাবে `HomeScreen`-এ পাঠিয়ে দেবে।

### সমস্যা ২: ভিডিও কলের মাঝে হঠাৎ লগআউট হয় কেন?
- **কারণ:** ভিডিও কল চলার সময় প্রতি ৬০ সেকেন্ড পর পর কয়েন বিলিং (`/api/call/deduct-interval`) এবং হার্টবিট রিকোয়েস্ট যায়।
  1. যদি কোনো কারণে ৪জি ডাটার সিগন্যাল ড্রপ করে বা সার্ভার সাময়িক `400` (Insufficient coins), `404` (Call ended), `429` (Rate limit), বা `500` রিটার্ন করে।
  2. Flutter অ্যাপের সেন্ট্রাল `Dio/Http Interceptor` সব এররকে এক কাতারে ফেলে `SharedPreferences.clear()` চালায় এবং `Navigator.pushNamedAndRemoveUntil('/login')` এক্সিকিউট করে।
- **সমাধান:** 
  1. গ্লোবাল ইন্টারসেপ্টরে শুধুমাত্র সত্যিকারের `401 Unauthorized` (টোকেন অবৈধ বা এক্সপায়ার্ড) আসলেই লগআউট হবে, অন্য কোনো সাধারণ নেটওয়ার্ক বা বিলিং এররে **কখনোই লগআউট হবে না**।
  2. ভিডিও কল ও হার্টবিট এপিআই-এর এরর `try-catch` দিয়ে লোকালি হ্যান্ডেল করতে হবে যাতে স্ক্রিন পরিবর্তন না হয়ে শুধু কল ডিসকানেক্টের নোটিশ দেয়।

---

## 2. অ্যাপ স্টার্টআপ ও অটো-লগইন ফ্লোচার্ট

```
            [ User Launches App ]
                      │
                      ▼
             ┌─────────────────┐
             │  SplashScreen   │
             └────────┬────────┘
                      │
           (Read Token from Storage)
                      │
            ┌─────────┴─────────┐
      Token Exists?        No Token
            │                   │
           YES                  ▼
            │          ┌─────────────────┐
            │          │   LoginScreen   │
            │          └─────────────────┘
            ▼
 ┌───────────────────────────┐
 │ Call GET /api/auth/check  │
 └──────────┬────────────────┘
            │
      ┌─────┴────────┐
 Valid (200 OK)   Invalid (401)
      │              │
      ▼              ▼
┌────────────┐ ┌─────────────┐
│ HomeScreen │ │ LoginScreen │
└────────────┘ └─────────────┘
```

---

## 3. Laravel RESTful API এন্ডপয়েন্টস

Laravel ব্যাকএন্ডে টোকেন লাইফটাইম **Sanctum Perpetual (আনলিমিটেড মেয়াদ)** হিসেবে কনফিগার করা আছে (`config/sanctum.php` এ `'expiration' => null`), তাই ব্যাকএন্ড থেকে টোকেন কখনো নিজে নিজে এক্সপায়ার হয় না।

### ৩.১. টোকেন ও সেশন চেক এন্ডপয়েন্ট
Flutter অ্যাপ ওপেন হওয়ার সময় এই এপিআই দিয়ে টোকেনের বৈধতা ও ইউজারের সর্বশেষ ব্যালেন্স/প্রোফাইল তথ্য রিফ্রেশ করবে।

* **Method:** `GET` অথবা `POST`
* **URL:** `/api/auth/check` (বা `/api/auth/me` বা `/api/me`)
* **Headers:**
  ```http
  Authorization: Bearer YOUR_STORED_TOKEN
  Accept: application/json
  ```

#### Success Response (`200 OK`):
```json
{
  "status": true,
  "message": "Session is valid and active.",
  "data": {
    "user": {
      "id": 25,
      "account_id": "8472910382",
      "first_name": "Ayesha",
      "last_name": "Rahman",
      "name": "Ayesha Rahman",
      "nickname": "Ayesha",
      "phone": "+8801700000000",
      "email": "ayesha@gmail.com",
      "coins": 4500,
      "gender": "female",
      "avatar": "https://yourdomain.com/storage/avatars/ayesha.jpg",
      "is_active": true,
      "level": "Lv4"
    }
  }
}
```

#### Invalid / Expired Token Response (`401 Unauthorized`):
```json
{
  "status": false,
  "message": "Unauthenticated or session expired."
}
```

---

### ৩.২. লগইন ও রেজিস্ট্রেশন রেসপন্স (টোকেন রিটার্ন)
* **Login URL:** `POST /api/login`
* **Register URL:** `POST /api/register`

উভয় এপিআই সফল হলে নিচের ফরম্যাটে `token` প্রদান করে:
```json
{
  "status": true,
  "message": "Login successful",
  "data": {
    "token": "105|7vK829xZkLmP01QWERTYUIOPASDFGHJKLZXCVBNM...",
    "token_type": "Bearer",
    "user": {
      "id": 25,
      "account_id": "8472910382",
      "name": "Ayesha Rahman",
      "coins": 4500
    }
  }
}
```

---

### ৩.৩. এক্সপ্লিসিট লগআউট এন্ডপয়েন্ট
ইউজার যখন অ্যাপের প্রোফাইল থেকে "Logout" বাটনে ক্লিক করবে শুধুমাত্র তখনই এই এপিআই কল হবে।

* **Method:** `POST`
* **URL:** `/api/logout`
* **Headers:** `Authorization: Bearer YOUR_TOKEN`

#### Success Response (`200 OK`):
```json
{
  "status": true,
  "message": "Successfully logged out"
}
```

---

## 4. Flutter Developer-এর জন্য স্টেপ-বাই-স্টেপ সমাধান ও রেডি কোড

### ৪.১. `AuthStorageService.dart` (টোকেন পার্মানেন্টলি সেভ রাখার সার্ভিস)

প্রজেক্টে `lib/core/services/auth_storage_service.dart` তৈরি বা আপডেট করুন:

```dart
import 'dart:convert';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:shared_preferences/shared_preferences.dart';

class AuthStorageService {
  static const _storage = FlutterSecureStorage();
  static const String _keyToken = 'auth_token';
  static const String _keyUser = 'user_data';
  static const String _keyIsLoggedIn = 'is_logged_in';

  /// Save Login Session Permanently
  static Future<void> saveAuthSession({
    required String token,
    required Map<String, dynamic> userData,
  }) async {
    // 1. Secure Token Storage
    await _storage.write(key: _keyToken, value: token);

    // 2. Fast SharedPreferences Access
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_keyToken, token);
    await prefs.setString(_keyUser, jsonEncode(userData));
    await prefs.setBool(_keyIsLoggedIn, true);
  }

  /// Get Stored Bearer Token
  static Future<String?> getToken() async {
    String? token = await _storage.read(key: _keyToken);
    if (token == null || token.isEmpty) {
      final prefs = await SharedPreferences.getInstance();
      token = prefs.getString(_keyToken);
    }
    return token;
  }

  /// Get Cached User Data
  static Future<Map<String, dynamic>?> getUserData() async {
    final prefs = await SharedPreferences.getInstance();
    final userJson = prefs.getString(_keyUser);
    if (userJson != null) {
      return jsonDecode(userJson) as Map<String, dynamic>;
    }
    return null;
  }

  /// Check if user is logged in locally
  static Future<bool> isLoggedIn() async {
    final token = await getToken();
    return token != null && token.isNotEmpty;
  }

  /// Clear Session on Explicit Manual Logout ONLY
  static Future<void> clearAuthSession() async {
    await _storage.deleteAll();
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_keyToken);
    await prefs.remove(_keyUser);
    await prefs.setBool(_keyIsLoggedIn, false);
  }
}
```

---

### ৪.২. `SplashScreen.dart` (অটো-লগইন রাউটিং স্ক্রিন)

`main.dart` এর প্রাথমিক রাউট হবে `SplashScreen`:

```dart
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import '../services/auth_storage_service.dart';
import '../screens/home_screen.dart';
import '../screens/auth/login_screen.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({Key? key}) : super(key: key);

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  @override
  void initState() {
    super.initState();
    _checkAutoLogin();
  }

  Future<void> _checkAutoLogin() async {
    await Future.delayed(const Duration(milliseconds: 800)); // Smooth Splash Experience

    final token = await AuthStorageService.getToken();

    if (token == null || token.isEmpty) {
      _navigateToLogin();
      return;
    }

    try {
      // Verify token with backend
      final response = await http.get(
        Uri.parse('https://your-domain.com/api/auth/check'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      ).timeout(const Duration(seconds: 8));

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['status'] == true && data['data']['user'] != null) {
          // Update cached user data
          await AuthStorageService.saveAuthSession(
            token: token,
            userData: data['data']['user'],
          );
          _navigateToHome();
          return;
        }
      } else if (response.statusCode == 401) {
        // Only 401 means token is truly invalid
        await AuthStorageService.clearAuthSession();
        _navigateToLogin();
        return;
      }

      // If server is temporarily unreachable or timeout, still allow entry using offline cache!
      _navigateToHome();
    } catch (e) {
      print("Network error on splash check, continuing with cached session: $e");
      // Offline fallback: Don't block user if internet is slow!
      _navigateToHome();
    }
  }

  void _navigateToHome() {
    if (!mounted) return;
    Navigator.pushReplacement(
      context,
      MaterialPageRoute(builder: (context) => const HomeScreen()),
    );
  }

  void _navigateToLogin() {
    if (!mounted) return;
    Navigator.pushReplacement(
      context,
      MaterialPageRoute(builder: (context) => const LoginScreen()),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF121212),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Image.asset('assets/images/logo.png', width: 120, height: 120),
            const SizedBox(height: 24),
            const CircularProgressIndicator(color: Colors.pinkAccent),
          ],
        ),
      ),
    );
  }
}
```

---

### ৪.৩. গ্লোবাল `Dio/Http Interceptor` ফিক্স (মিড-কল লগআউট বন্ধ করার সমাধান)

যদি অ্যাপে `Dio` ব্যবহার করা হয়, তবে ইন্টারসেপ্টর এমনভাবে কনফিগার করতে হবে যেন সাধারণ কোনো এপিআই এররে লগআউট না হয়ে যায়:

```dart
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import '../services/auth_storage_service.dart';

final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();

Dio createApiClient() {
  final dio = Dio(
    BaseOptions(
      baseUrl: 'https://your-domain.com/api',
      connectTimeout: const Duration(seconds: 15),
      receiveTimeout: const Duration(seconds: 15),
      headers: {'Accept': 'application/json'},
    ),
  );

  dio.interceptors.add(
    InterceptorsWrapper(
      onRequest: (options, handler) async {
        final token = await AuthStorageService.getToken();
        if (token != null && token.isNotEmpty) {
          options.headers['Authorization'] = 'Bearer $token';
        }
        return handler.next(options);
      },
      onError: (DioException error, handler) async {
        final path = error.requestOptions.path;
        final statusCode = error.response?.statusCode;

        // ⚠️ CRITICAL RULE: DO NOT AUTO-LOGOUT ON CALL/BILLING/HEARTBEAT/PRESENCE PATHS!
        final isCallOrPresenceApi = path.contains('/call/') || 
                                    path.contains('/presence/') || 
                                    path.contains('/signal');

        if (statusCode == 401 && !isCallOrPresenceApi) {
          print("CRITICAL: Global Auth Token Invalid (401). Redirecting to login...");
          await AuthStorageService.clearAuthSession();
          navigatorKey.currentState?.pushNamedAndRemoveUntil('/login', (route) => false);
        } else {
          // For all other errors (400, 404, 500, Network Timeout, Call Pulse Error):
          // NEVER LOGOUT! Just let the calling screen handle the error gracefully!
          print("Non-fatal API Error on $path (Status: $statusCode) - Suppressing Auto-Logout.");
        }

        return handler.next(error);
      },
    ),
  );

  return dio;
}
```

---

### ৪.৪. ভিডিও কলের মাঝে বিলিং এরর সেফ হ্যান্ডলিং (`video_call_screen.dart`)

ভিডিও কলের ভেতর প্রতি মিনিটে কয়েন কাটার রিকোয়েস্টে কখনো অ্যাপ ক্র্যাশ বা লগআউট হওয়া যাবে না:

```dart
Timer? _billingPulseTimer;

void _startBillingPulseTimer() {
  _billingPulseTimer?.cancel();
  // প্রতি ৬০ সেকেন্ড পর পর পালস পাঠানো
  _billingPulseTimer = Timer.periodic(const Duration(seconds: 60), (timer) async {
    await _sendDeductIntervalPulse();
  });
}

Future<void> _sendDeductIntervalPulse() async {
  final token = await AuthStorageService.getToken();
  if (token == null) return;

  try {
    final response = await http.post(
      Uri.parse('https://your-domain.com/api/call/deduct-interval'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: jsonEncode({
        'call_id': widget.callId,
        'channel_name': widget.channelName,
      }),
    ).timeout(const Duration(seconds: 10));

    final data = jsonDecode(response.body);

    if (data['status'] == false) {
      // যদি কয়েন শেষ হয়ে যায়: কল সুন্দরভাবে কেটে দিন, কিন্তু ইউজারকে লগআউট করবেন না!
      if (data['message'].toString().toLowerCase().contains('insufficient') || 
          data['message'].toString().toLowerCase().contains('coin')) {
        _endCallGracefully("Coin balance exhausted. Call ended.");
      }
    }
  } catch (e) {
    // নেটওয়ার্ক সাময়িক স্লো হলেও কল চলবে, কখনোই লগআউট হবে না
    print("Warning: Billing pulse minor network error (Suppressing logout): $e");
  }
}

void _endCallGracefully(String reason) {
  _billingPulseTimer?.cancel();
  _disposeWebRtc();
  if (mounted) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(reason)));
    Navigator.pop(context);
  }
}
```

---

### ৪.৫. ইউজার প্রোফাইল থেকে ম্যানুয়াল লগআউট হ্যান্ডলার

ব্যবহারকারী যখন নিজেই প্রোফাইল বা সেটিংস থেকে লগআউট বাটনে চাপ দেবেন:

```dart
Future<void> performUserLogout(BuildContext context) async {
  final token = await AuthStorageService.getToken();
  
  // 1. Backend-কে জানানো
  if (token != null) {
    try {
      await http.post(
        Uri.parse('https://your-domain.com/api/logout'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      ).timeout(const Duration(seconds: 4));
    } catch (_) {}
  }

  // 2. লোকাল স্টোরেজ ক্লিয়ার করা
  await AuthStorageService.clearAuthSession();

  // 3. লগইন স্ক্রিনে নিয়ে যাওয়া
  if (context.mounted) {
    Navigator.pushNamedAndRemoveUntil(context, '/login', (route) => false);
  }
}
```

---

## 5. ডেভেলপারের চেকলিস্ট

### Flutter Developer:
- [ ] `AuthStorageService.dart` ফাইলটি যোগ করুন এবং লগইন/রেজিস্ট্রেশনের পর `saveAuthSession()` কল নিশ্চিত করুন।
- [ ] `main.dart`-এ প্রাথমিক স্ক্রিন হিসেবে `SplashScreen` দিন যা টোকেন ভ্যালিডেট করে অটো-লগইন করাবে।
- [ ] `Dio/Http Interceptor`-এ চেক বসান যেন `/call/` বা `/presence/` এপিআই-এর কোনো এররে কখনোই `clearAuthSession()` বা `/login` এ নেভিগেট না করে।
- [ ] `video_call_screen.dart` ফাইলে ইন-কল বিলিং পালস এরর ট্রাই-ক্যাচ দিয়ে সেফ করুন (কয়েন শেষ হলে কল কাটবে, কিন্তু লগআউট হবে না)।

### Backend Developer / Laravel Server:
- [ ] `GET /api/auth/check` এবং `GET /api/auth/me` এপিআই সেশন ও প্রোফাইল ডাটা ঠিকমতো রিটার্ন করছে।
- [ ] Sanctum কনফিগারেশনে টোকেনের মেয়াদ পার্মানেন্ট রাখা হয়েছে।
