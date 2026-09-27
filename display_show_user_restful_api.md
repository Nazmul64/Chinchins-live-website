# 👥 Chinchins Live — Display & Show Users RESTful API Documentation
## (Flutter Mobile & Backend Integration Guide)

---

## 📌 ওভারভিউ (Overview & Fix Summary)

এই ডকুমেন্টে চিনচিনস লাইভ প্ল্যাটফর্মের **সকল রেজিস্টার করা ইউজার, লাইভ স্ট্রিমার ও ডিসকভারি হোস্টদের তালিকা প্রদর্শন (Display / Show Users Feed)** করার সমস্ত RESTful API বিস্তারিতভাবে বর্ণনা করা হয়েছে।

### 🛠️ ব্যাকএন্ডের প্রধান ফিচারসমূহ:
1. **সকল রেজিস্টার করা ইউজার দৃশ্যমান:** নতুন ইউজার রেজিস্ট্রেশন করার সাথে সাথে স্বয়ংক্রিয়ভাবে একটিভ ও অনলাইন স্ট্যাটাসে ফিডে যুক্ত হয়।
2. **স্মার্ট প্রায়োরিটি সর্টিং:** 
   - 🔴 প্রথমে: যারা বর্তমানে **Live Stream**-এ আছেন।
   - 🟢 দ্বিতীয়ত: যারা বর্তমানে **Online** এবং অ্যাক্টিভ আছেন (`is_online = true`)।
   - ⚪ তৃতীয়ত: সম্প্রতি সক্রিয় বা অফলাইন ইউজারগণ (`last_seen_at` ও `id DESC`)।
3. **ডায়নামিক পেজিনেশন ও ফিল্টারিং:** দেশ (`country`), জেন্ডার (`gender`), সার্চ কিউরি (`search`), এবং পেজিনেশন প্যারামিটার সাপোর্ট করে।
4. **প্রিমিয়াম কার্ড ব্যাজ ও অ্যানিমেশন মেটাডাটা:** লাইভ ইকুয়ালাইজার সাউন্ড ওয়েভ, ভেরিফাইড টিক চিহ্ন (`V`), এবং নচড ভিডিও কল অ্যাকশন বাটন কনফিগ প্রদান করে।

---

## 🚀 API Endpoints Overview

| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/users` | মোবাইল অ্যাপের মেইন হোম/ইউজার ফিড লিস্ট | No (Optional) |
| `GET` | `/api/home` | হোম ফিডের বিকল্প এলিয়াস | No (Optional) |
| `GET` | `/api/streamers` | স্ট্রিমার ও হোস্টদের কমপ্লিট লিস্ট | No (Optional) |
| `GET` | `/api/v1/users/discovery` | ডিসকভারি ইউজার লিস্ট (ফিল্টারসহ) | No (Optional) |
| `GET` | `/api/v1/users/active` | বর্তমানে সক্রিয় সকল ইউজার লিস্ট | No (Optional) |
| `GET` | `/api/live/card-feed` | হট লাইভ স্ট্রিমার ও হোস্ট ব্রডকাস্ট কার্ড ফিড | No (Optional) |
| `GET` | `/api/v1/hot` | হট লাইভ স্ট্রিমার ফিড এলিয়াস | No (Optional) |
| `GET` | `/api/users/search` | নাম, নিকনেম বা ৮ ডিজিটের অ্যাকাউন্ট আইডি দিয়ে সার্চ | No (Optional) |
| `POST` | `/api/register` | নতুন ইউজার রেজিস্ট্রেশন ও ইনস্ট্যান্ট ফিড সিঙ্ক | No |

---

## 📡 ১. Primary Home & Users Feed API

### 🔹 Request
- **URL:** `GET /api/users` অথবা `GET /api/home`
- **Headers:**
  ```http
  Accept: application/json
  Authorization: Bearer <TOKEN> (Optional)
  ```

### 🔹 Query Parameters
| Parameter | Type | Default | Description | Example |
| :--- | :--- | :--- | :--- | :--- |
| `page` | `integer` | `1` | পেজ নম্বর | `1` |
| `per_page` | `integer` | `30` | প্রতি পেজে ইউজারের সংখ্যা (ম্যাক্স ১০০) | `20` |
| `gender` | `string` | `all` | জেন্ডার ফিল্টার (`female`, `male`, `all`) | `female` |
| `country` | `string` | `all` | দেশ অনুযায়ী ফিল্টার (`Bangladesh`, `India`, ইত্যাদি) | `Bangladesh` |
| `search` | `string` | `null` | নাম বা অ্যাকাউন্ট আইডি দিয়ে সার্চ | `Sara` |
| `include_offline` | `boolean` | `true` | অফলাইন ইউজারসহ রিটার্ন করবে কিনা | `1` |

### 🔹 Success Response (`200 OK`)
```json
{
  "status": true,
  "success": true,
  "message": "Streamers loaded successfully from database",
  "data": {
    "users": [
      {
        "id": 2,
        "account_id": "84729103",
        "name": "Sara Khan",
        "display_name": "Sara Khan",
        "nickname": "Sara",
        "avatar": "https://chinchins.live/storage/avatars/sara.jpg",
        "avatar_url": "https://chinchins.live/storage/avatars/sara.jpg",
        "profile_picture": "https://chinchins.live/storage/avatars/sara.jpg",
        "cover_photo_url": "https://chinchins.live/storage/covers/sara_cover.jpg",
        "gender": "female",
        "age": "22",
        "display_age": "22",
        "level": "Lv.5",
        "level_number": 5,
        "country": "Bangladesh",
        "country_code": "BD",
        "country_flag": "🇧🇩",
        "city": "Dhaka",
        "is_active": true,
        "is_online": true,
        "is_available": true,
        "is_live": false,
        "is_pulsing": false,
        "online_status": "online",
        "current_status": "online",
        "status_text": "Online",
        "live_badge": {
          "label": "Online",
          "type": "online",
          "is_live": false,
          "is_online": true,
          "sound_wave_animation": false,
          "equalizer_bars_count": 3,
          "badge_style": "glass_dark",
          "gradient_colors": ["rgba(0,0,0,0.45)", "rgba(0,0,0,0.45)"],
          "dot_color": "#22C55E",
          "has_dot": true
        },
        "verified_badge": {
          "is_verified": true,
          "badge_type": "verified_v",
          "label": "V",
          "color": "#38BDF8",
          "icon_url": "https://chinchins.live/assets/images/badges/verified_v.png"
        },
        "action_button": {
          "type": "video_call",
          "icon": "video_camera",
          "is_live": false,
          "is_animating": false,
          "animation_type": "none",
          "gradient_colors": ["#8B5CF6", "#EC4899"],
          "shape": "notched_floating_circle",
          "notch_position": "bottom_right",
          "notch_radius": 28
        },
        "card_design": {
          "has_bottom_right_notch": true,
          "notch_radius": 28,
          "corner_radius": 16,
          "button_floating_outside": true
        },
        "live_stream_id": null,
        "live_stream": null,
        "video_call_rate": 100,
        "rate_per_minute": 100,
        "coins": 500,
        "coins_balance": 500,
        "introduction": "Welcome to my ChinChins Live profile! 🎉",
        "speaking_languages": ["English", "Bengali"],
        "interest_tags": ["Live Chat", "Music", "Gaming"],
        "charm_level": "Lv4"
      }
    ],
    "total": 1,
    "current_page": 1,
    "last_page": 1,
    "per_page": 30
  }
}
```

---

## 📡 ২. Dynamic User Discovery & Active Users API

### 🔹 Request
- **URL:** `GET /api/v1/users/discovery` অথবা `GET /api/v1/users/active`
- **Query Parameters:**
  - `page` (integer, default: 1)
  - `per_page` (integer, default: 30)
  - `include_offline` (boolean, default: 1)

### 🔹 Success Response (`200 OK`)
```json
{
  "status": "success",
  "show_offline_users": true,
  "data": [
    {
      "id": 2,
      "account_id": "84729103",
      "name": "Sara Khan",
      "display_name": "Sara Khan",
      "nickname": "Sara",
      "avatar": "https://chinchins.live/storage/avatars/sara.jpg",
      "avatar_url": "https://chinchins.live/storage/avatars/sara.jpg",
      "cover_photo_url": "https://chinchins.live/storage/covers/sara_cover.jpg",
      "gender": "female",
      "level": "Lv.5",
      "country": "Bangladesh",
      "country_flag": "🇧🇩",
      "city": "Dhaka",
      "is_live": false,
      "is_online": true,
      "is_verified": true,
      "online_status": "online",
      "status_text": "Online",
      "video_call_rate": 100,
      "coins": 500
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 30,
    "total": 1
  }
}
```

---

## 📡 ৩. Live Broadcast & Hot Streamers Card Feed

### 🔹 Request
- **URL:** `GET /api/live/card-feed` অথবা `GET /api/v1/hot`
- **Query Parameters:**
  - `page` (integer)
  - `per_page` (integer)
  - `country` (string)
  - `search` (string)

---

## 📡 ৪. User Search API

### 🔹 Request
- **URL:** `GET /api/users/search` অথবা `GET /api/search`
- **Query Parameters:**
  - `query` / `q` / `search`: ইউজারের নাম, নিকনেম অথবা ৮ ডিজিটের Account ID।
  - `page`: পেজ নম্বর।

---

## 📡 ৫. User Registration & Instant Feed Sync

### 🔹 Request
- **URL:** `POST /api/register`
- **Body (JSON / Form-Data):**
```json
{
  "first_name": "Sara",
  "last_name": "Khan",
  "nickname": "Sara",
  "phone": "+8801712345678",
  "email": "sara@chinchins.live",
  "password": "password123",
  "password_confirmation": "password123",
  "gender": "female",
  "country": "Bangladesh",
  "city": "Dhaka",
  "age": 22,
  "fcm_token": "fcm_token_string_here",
  "device_type": "android"
}
```

### 🔹 Response (`201 Created`)
```json
{
  "status": true,
  "message": "Registration successful",
  "data": {
    "user": {
      "id": 2,
      "account_id": "84729103",
      "name": "Sara Khan",
      "first_name": "Sara",
      "last_name": "Khan",
      "email": "sara@chinchins.live",
      "phone": "+8801712345678",
      "gender": "female",
      "is_active": true,
      "is_online": true,
      "online_status": "online",
      "level": 1,
      "coins": 0
    },
    "token": "1|sanctum_auth_token_string_here",
    "token_type": "Bearer"
  }
}
```

---

## 📱 Flutter Implementation Example

### 🧩 Dart Model Helper
```dart
class UserModel {
  final int id;
  final String accountId;
  final String name;
  final String avatarUrl;
  final String gender;
  final String country;
  final String countryFlag;
  final bool isOnline;
  final bool isLive;
  final int videoCallRate;

  UserModel({
    required this.id,
    required this.accountId,
    required this.name,
    required this.avatarUrl,
    required this.gender,
    required this.country,
    required this.countryFlag,
    required this.isOnline,
    required this.isLive,
    required this.videoCallRate,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: json['id'] ?? 0,
      accountId: json['account_id']?.toString() ?? '',
      name: json['display_name'] ?? json['name'] ?? 'User',
      avatarUrl: json['avatar_url'] ?? json['avatar'] ?? '',
      gender: json['gender'] ?? 'female',
      country: json['country'] ?? 'Bangladesh',
      countryFlag: json['country_flag'] ?? '🇧🇩',
      isOnline: json['is_online'] == true,
      isLive: json['is_live'] == true,
      videoCallRate: json['video_call_rate'] ?? 100,
    );
  }
}
```

### 🌐 Fetch Users Service
```dart
import 'dart:convert';
import 'package:http/http.dart' as http;

Future<List<UserModel>> fetchUsersFeed({int page = 1, String gender = 'all'}) async {
  final uri = Uri.parse('https://chinchins.live/api/users?page=$page&gender=$gender');
  final response = await http.get(uri, headers: {
    'Accept': 'application/json',
  });

  if (response.statusCode == 200) {
    final Map<String, dynamic> data = json.decode(response.body);
    final List list = data['data']?['users'] ?? data['users'] ?? [];
    return list.map((item) => UserModel.fromJson(item)).toList();
  } else {
    throw Exception('Failed to load users feed');
  }
}
```

---

## 🧪 cURL Example for Testing

```bash
curl -X GET "https://chinchins.live/api/users?page=1&per_page=20" \
     -H "Accept: application/json"
```
