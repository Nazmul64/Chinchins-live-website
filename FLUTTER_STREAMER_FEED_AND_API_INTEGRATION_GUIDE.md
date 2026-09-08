# 📱 Chinchins Live — Streamer Feed & Mobile API Integration Guide

**Document:** `FLUTTER_STREAMER_FEED_AND_API_INTEGRATION_GUIDE.md`  
**Target:** Flutter Mobile App Developers, Backend Engineers & Technical Team  
**Backend:** Laravel REST API Engine (`https://chinchins.live/api`)  
**Auth Header:** `Authorization: Bearer <Sanctum_Token>` or `X-User-Id: <User_ID>`  

---

## 📌 1. Summary: Why "No Streamers Found" Happened & How It Was Resolved

When users opened the **Hot** tab on the mobile app, the screen previously displayed **"No Streamers Found"**, even though registered users were visible in the Admin Panel (`chinchins.live/admin/users`). 

Investigation revealed three distinct root causes:

| # | Root Cause | Detail | Status |
|---|---|---|---|
| 1 | **Hardcoded Exclusion in Flutter Code** | In `lib/features/explore/screens/hot_explore_screen.dart` and `lib/core/services/profile_api_service.dart`, the code explicitly skipped any user named `ayeena04` (`name == 'ayeena04' \|\| name == 'ayeena'`). | **Fixed** (Removed hardcoded exclusion) |
| 2 | **Country Filter Mismatch & Country Selector Default** | The app's explore screen was defaulted to `BGD` (`Bangladesh`). In the database, the registered streamers (`Ayeena04` and `Nazmul`) were registered under `Pakistan`, while `Regular User` had an empty country string. When filtered by `country=Bangladesh`, Pakistan users were filtered out and `Regular User` was skipped due to empty string in DB. | **Fixed** (Backend now handles country variants, defaults app country to `Global 🌐 (All)` so all streamers load on start, and added Pakistan to country picker) |
| 3 | **Database Streamer Profile Data** | `Regular User` had missing country (`Bangladesh`), gender, and avatar photo in database. | **Fixed** (Profile updated with complete country, gender, and Unsplash HD avatar) |

---

## 🚀 2. Master RESTful API: Public Home Feed & Streamers List

Use this endpoint to populate the **Hot Screen**, **Home Streamers Grid**, **Discover Page**, and **Host Listings**.

### Endpoint Details
- **Primary Route:** `GET https://chinchins.live/api/home`
- **Aliases (All point to the same controller):**
  - `GET https://chinchins.live/api/users`
  - `GET https://chinchins.live/api/hot`
  - `GET https://chinchins.live/api/streamers`
  - `GET https://chinchins.live/api/home/streamers`

### Query Parameters

| Parameter | Type | Required | Default | Description | Example Values |
|---|---|---|---|---|---|
| `country` | string | No | `All` | Filter by country name, ISO code, or Alpha-3 | `All`, `BGD`, `BD`, `Bangladesh`, `PAK`, `PK`, `Pakistan`, `IND`, `USA` |
| `country_code`| string | No | null | Alternative param for 2-letter ISO code | `BD`, `PK`, `IN`, `US` |
| `region` | string | No | null | Alias for country | `Global`, `Bangladesh`, `Pakistan` |
| `gender` | string | No | `all` | Filter by gender | `female`, `male`, `all` |
| `search` | string | No | null | Search by Name, Nickname, 8-digit Account ID, or City | `Ayeena`, `602281635`, `Dhaka` |
| `page` | integer | No | `1` | Pagination page number | `1`, `2`, `3` |
| `per_page` | integer | No | `20` | Number of items per page | `20`, `30`, `50` |

> 💡 **Best Practice for Mobile App:** Default your country parameter to `'All'` (or omit it) so that when a user first opens the app, they immediately see all active live streamers worldwide!

---

### Response Structure (`200 OK`)

```json
{
  "status": true,
  "success": true,
  "message": "Streamers loaded successfully from database",
  "data": {
    "users": [
      {
        "id": 2,
        "account_id": "602281635",
        "name": "Ayeena04",
        "display_name": "Ayeena04",
        "nickname": "Ayeena04",
        "avatar": "https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=300&auto=format&fit=crop&q=80",
        "avatar_url": "https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=300&auto=format&fit=crop&q=80",
        "profile_picture": "https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=300&auto=format&fit=crop&q=80",
        "cover_photo_url": "https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=600&auto=format&fit=crop&q=80",
        "gallery_images": [
          "https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=300&auto=format&fit=crop&q=80",
          "https://images.unsplash.com/photo-1517841905240-472988babdf9?w=500&auto=format&fit=crop&q=80"
        ],
        "photos": [
          "https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=300&auto=format&fit=crop&q=80"
        ],
        "gender": "female",
        "age": 27,
        "display_age": 27,
        "level": "Lv4",
        "level_number": 4,
        "display_level": "Lv4",
        "country": "Pakistan",
        "country_code": "PK",
        "country_flag": "🇵🇰",
        "city": "Lahore",
        "is_active": true,
        "is_online": true,
        "online_status": "online",
        "status_text": "Online",
        "is_busy": false,
        "is_free_caller": false,
        "is_verified": true,
        "video_call_rate": 1800,
        "rate_per_minute": 1800,
        "audio_call_rate": 60,
        "coins": 0,
        "introduction": "Sweet girl looking for honest talk ❤️",
        "tags": [
          "Live video",
          "Music"
        ]
      },
      {
        "id": 4,
        "account_id": "1000008888",
        "name": "Regular User",
        "display_name": "Regular User",
        "nickname": "Regular User",
        "avatar": "https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=500&auto=format&fit=crop&q=80",
        "avatar_url": "https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=500&auto=format&fit=crop&q=80",
        "profile_picture": "https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=500&auto=format&fit=crop&q=80",
        "cover_photo_url": "https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=800&auto=format&fit=crop&q=80",
        "gallery_images": [
          "https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=500&auto=format&fit=crop&q=80"
        ],
        "gender": "female",
        "age": 24,
        "display_age": 24,
        "level": "Lv4",
        "level_number": 4,
        "display_level": "Lv4",
        "country": "Bangladesh",
        "country_code": "BD",
        "country_flag": "🇧🇩",
        "city": "Dhaka",
        "is_active": true,
        "is_online": true,
        "online_status": "online",
        "status_text": "Online",
        "is_busy": false,
        "is_free_caller": false,
        "is_verified": true,
        "video_call_rate": 1800,
        "rate_per_minute": 1800,
        "audio_call_rate": 60,
        "coins": 0,
        "introduction": "Friendly streamer from Dhaka! Let's video call.",
        "tags": [
          "Sweet",
          "Online",
          "Live"
        ]
      }
    ],
    "total": 2,
    "current_page": 1,
    "last_page": 1,
    "per_page": 20
  }
}
```

---

## 🔍 3. Search Users API (by 8-digit Account ID or Name)

- **Endpoint:** `GET https://chinchins.live/api/search?q={query}`
- **Alternative:** `GET https://chinchins.live/api/users/search?search={query}`

### Example Requests:
- By 8-digit Account ID: `GET /api/search?q=602281635`
- By Name: `GET /api/search?q=Ayeena`

---

## 👤 4. Single Profile Details API

- **Endpoint:** `GET https://chinchins.live/api/profile/{id}`
- **Alternative:** `GET https://chinchins.live/api/profile/{account_id}`

Supports lookup by either internal numeric ID (`2`) or public 8-digit Account ID (`602281635`).

---

## ⚙️ 5. Clean Flutter Dart Integration Code

Here is the recommended Flutter implementation for fetching and displaying the live feed smoothly without unintended client-side filtering:

### Model Parsing (`lib/core/models/model_profile.dart`)

```dart
class ModelProfile {
  final String id;
  final String accountId;
  final String name;
  final String avatarUrl;
  final String? coverPhotoUrl;
  final List<String> galleryUrls;
  final String country;
  final String countryCode;
  final String countryFlag;
  final String gender;
  final int age;
  final int pricePerMin;
  final bool isOnline;
  final bool isVerified;
  final String intro;

  ModelProfile({
    required this.id,
    required this.accountId,
    required this.name,
    required this.avatarUrl,
    this.coverPhotoUrl,
    required this.galleryUrls,
    required this.country,
    required this.countryCode,
    required this.countryFlag,
    required this.gender,
    required this.age,
    required this.pricePerMin,
    required this.isOnline,
    required this.isVerified,
    required this.intro,
  });

  factory ModelProfile.fromJson(Map<String, dynamic> json) {
    List<String> parseGallery(dynamic list) {
      if (list is List) {
        return list.map((e) => e.toString()).where((e) => e.isNotEmpty).toList();
      }
      return [];
    }

    final avatar = json['avatar_url'] ??
        json['avatar'] ??
        json['profile_picture'] ??
        'https://ui-avatars.com/api/?name=${Uri.encodeComponent(json['name'] ?? 'User')}';

    return ModelProfile(
      id: json['id']?.toString() ?? '',
      accountId: json['account_id']?.toString() ?? '',
      name: json['display_name'] ?? json['name'] ?? 'User',
      avatarUrl: avatar,
      coverPhotoUrl: json['cover_photo_url'],
      galleryUrls: parseGallery(json['gallery_images'] ?? json['photos']),
      country: json['country'] ?? 'Bangladesh',
      countryCode: json['country_code'] ?? 'BD',
      countryFlag: json['country_flag'] ?? '🇧🇩',
      gender: json['gender'] ?? 'female',
      age: (json['display_age'] as num?)?.toInt() ?? (json['age'] as num?)?.toInt() ?? 24,
      pricePerMin: (json['rate_per_minute'] as num?)?.toInt() ?? (json['video_call_rate'] as num?)?.toInt() ?? 1800,
      isOnline: json['is_online'] == true || json['is_active'] == true,
      isVerified: json['is_verified'] == true,
      intro: json['introduction'] ?? '',
    );
  }
}
```

### Feed Service Call (`lib/core/services/profile_api_service.dart`)

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;

class ProfileApiService {
  static const String baseUrl = 'https://chinchins.live/api';

  static Future<List<ModelProfile>> fetchStreamers({
    String? country,
    String? gender,
    int page = 1,
    int perPage = 20,
    String? token,
  }) async {
    final queryParams = <String, String>{
      'page': page.toString(),
      'per_page': perPage.toString(),
    };

    if (country != null && country.isNotEmpty && country != 'All') {
      queryParams['country'] = country;
    }
    if (gender != null && gender.isNotEmpty && gender != 'all') {
      queryParams['gender'] = gender;
    }

    final uri = Uri.parse('$baseUrl/home').replace(queryParameters: queryParams);
    final headers = {
      'Accept': 'application/json',
      if (token != null && token.isNotEmpty) 'Authorization': 'Bearer $token',
    };

    final response = await http.get(uri, headers: headers).timeout(const Duration(seconds: 10));

    if (response.statusCode == 200) {
      final decoded = jsonDecode(response.body);
      final List? userList = decoded['data']?['users'] ?? decoded['users'];
      if (userList != null) {
        return userList
            .whereType<Map<String, dynamic>>()
            .map((item) => ModelProfile.fromJson(item))
            // Exclude only admin system account
            .where((u) => u.name.toLowerCase() != 'admin' && !u.accountId.startsWith('1000000001'))
            .toList();
      }
    }
    return [];
  }
}
```

---

## 🎯 6. Key Takeaways & Checklist

- [x] **Backend Country Matching:** Fully supports `All`, `BGD`, `BD`, `Bangladesh`, `PAK`, `PK`, `Pakistan`, `IND`, `USA`, etc.
- [x] **Streamers Profile Data:** Registered streamers in MySQL have valid avatar, cover, gallery, age, rate, and country.
- [x] **No Hardcoded Exclusions:** Removed `ayeena04` / `ayeena` blocks from client-side parser.
- [x] **Default Region:** App defaults to `Global 🌐 (All)` so the explore screen is populated immediately on cold start.
- [x] **Live Database Live Synced:** Newly registered users appear directly in both Admin Panel and Mobile App.
