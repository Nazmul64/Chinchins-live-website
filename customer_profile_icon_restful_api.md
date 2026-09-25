# 📱 Chinchins Live - Customer Profile Icons ("Me" Screen) RESTful API & Architecture Documentation

---

## 📌 ১. ভূমিকা ও আর্কিটেকচার ওভারভিউ (Overview & Architecture)

কাস্টমার অ্যাপের **"Me" (প্রোফাইল)** স্ক্রিনের সবকটি আইটেম ও ফিচার আইকন এডমিন প্যানেল থেকে গতিশীলভাবে নিয়ন্ত্রণ ও কাস্টম পিকচার আপলোড করার জন্য এই আর্কিটেকচারটি তৈরি করা হয়েছে। 

### 🌟 মূল সুবিধাসমূহ:
1. **১০টি সুনির্দিষ্ট প্রোফাইল আইটেম**:
   - `my_gems` (My Gems 💎)
   - `beans_center` (Beans Center 🥚)
   - `spend_less_card` (Spend Less, Get More Gems! 💳)
   - `svip` (SVIP 🏅)
   - `my_bag` (My Bag 🛍️)
   - `gems_center` (Gems Center 💎)
   - `payment_details` (Payment details 👛)
   - `my_level` (My Level 🎖️)
   - `sign_in` (Sign-In 🎁)
   - `reward` (Reward 📅)
2. **কাস্টম পিকচার আপলোড**:
   - এডমিন প্যানেলে **"Customer Profile Icons"** মেনু থেকে প্রতিটি আইটেমে সরাসরি নতুন ইমেজ/পিকচার আপলোড করা যাবে।
   - আপলোড ডিরেক্টরি: `public/uploads/customer_profile_icon/`
   - কোনো কাস্টম পিকচার না থাকলে অ্যাপ বাই-ডিফল্ট ক্রিস্প ভেক্টর আইকন দেখাবে।
3. **জিরো-লোড লোকাল স্টোরেজ ক্যাশিং (Zero-Latency Local Storage Ready)**:
   - অ্যাপ লগইন বা ওপেন হওয়ার সাথে সাথে এই এপিআই থেকে ১০টি আইকনের ডাটা মোবাইলের লোকাল স্টোরেজে (SharedPreferences / Hive / SQLite) ক্যাশ হয়ে যাবে।
   - ফলে পরবর্তীতে প্রোফাইল পেজে ডাটাবেসে কোনো রিকোয়েস্ট পাঠানো লাগবে না, যা সার্ভার লোড সম্পূর্ণ শূন্য করে এবং অ্যাপকে সর্বোচ্চ গতিশীল রাখে।
   - সাপোর্ট করে `ETag` এবং `Cache-Control: public, max-age=86400`।

---

## 🌐 ২. RESTful API এন্ডপয়েন্টস (API Endpoints)

### ২.১ সমস্ত ১০টি প্রোফাইল আইকন ডাটা ও লোকাল ক্যাশ পেলোড (Get All Profile Icons)
- **Method**: `GET`
- **URL**: `https://chinchins.live/api/customer-profile-icons`
- **বিকল্প URLs**: 
  - `https://chinchins.live/api/customer-profile/icons`
  - `https://chinchins.live/api/v1/customer-profile-icons`
  - `https://chinchins.live/api/app-icons/profile`
- **Headers**:
  ```http
  Accept: application/json
  If-None-Match: "chinchins_icons_10_..." (ঐচ্ছিক ETag)
  ```
- **Response Headers**:
  ```http
  HTTP/1.1 200 OK
  Cache-Control: public, max-age=86400, stale-while-revalidate=3600
  ETag: "chinchins_icons_10_1727223423"
  ```
- **Response JSON**:
```json
{
  "status": true,
  "success": true,
  "message": "Customer profile icons retrieved successfully.",
  "timestamp": "2026-09-25T11:00:00+06:00",
  "data": {
    "version_hash": "a1b2c3d4e5f6...",
    "total_icons": 10,
    "cache_ttl_seconds": 86400,
    "cache_strategy": "CACHE_FIRST_WITH_ETAG_REVALIDATION",
    "icons": [
      {
        "id": 1,
        "key": "my_gems",
        "title": "My Gems",
        "subtitle": "User Diamond & Gem Balance",
        "category": "wallet_card",
        "icon_url": "https://chinchins.live/uploads/customer_profile_icon/default_my_gems.png",
        "default_icon_url": "https://chinchins.live/uploads/customer_profile_icon/default_my_gems.png",
        "is_custom": false,
        "badge_text": null,
        "badge_color": null,
        "target_route": "wallet/gems",
        "sort_order": 1,
        "is_active": true,
        "updated_at": "2026-09-25T10:57:03+06:00"
      },
      {
        "id": 2,
        "key": "beans_center",
        "title": "Beans Center",
        "subtitle": "Beans & Earnings Exchange",
        "category": "wallet_card",
        "icon_url": "https://chinchins.live/uploads/customer_profile_icon/default_beans_center.png",
        "default_icon_url": "https://chinchins.live/uploads/customer_profile_icon/default_beans_center.png",
        "is_custom": false,
        "badge_text": null,
        "badge_color": null,
        "target_route": "wallet/beans",
        "sort_order": 2,
        "is_active": true,
        "updated_at": "2026-09-25T10:57:03+06:00"
      },
      {
        "id": 3,
        "key": "spend_less_card",
        "title": "Spend Less, Get More Gems!",
        "subtitle": "Update to New User Weekly Card",
        "category": "banner_card",
        "icon_url": "https://chinchins.live/uploads/customer_profile_icon/default_spend_less_card.png",
        "default_icon_url": "https://chinchins.live/uploads/customer_profile_icon/default_spend_less_card.png",
        "is_custom": false,
        "badge_text": "big discount",
        "badge_color": "#FEF08A",
        "target_route": "wallet/spend_less",
        "sort_order": 3,
        "is_active": true,
        "updated_at": "2026-09-25T10:57:03+06:00"
      },
      {
        "id": 4,
        "key": "svip",
        "title": "SVIP",
        "subtitle": "Exclusive SVIP & VIP Privileges",
        "category": "action_menu",
        "icon_url": "https://chinchins.live/uploads/customer_profile_icon/default_svip.png",
        "default_icon_url": "https://chinchins.live/uploads/customer_profile_icon/default_svip.png",
        "is_custom": false,
        "badge_text": null,
        "badge_color": null,
        "target_route": "wallet/svip",
        "sort_order": 4,
        "is_active": true,
        "updated_at": "2026-09-25T10:57:03+06:00"
      },
      {
        "id": 5,
        "key": "my_bag",
        "title": "My Bag",
        "subtitle": "User Inventory & Backpack Items",
        "category": "action_menu",
        "icon_url": "https://chinchins.live/uploads/customer_profile_icon/default_my_bag.png",
        "default_icon_url": "https://chinchins.live/uploads/customer_profile_icon/default_my_bag.png",
        "is_custom": false,
        "badge_text": null,
        "badge_color": null,
        "target_route": "bag/my_bag",
        "sort_order": 5,
        "is_active": true,
        "updated_at": "2026-09-25T10:57:03+06:00"
      },
      {
        "id": 6,
        "key": "gems_center",
        "title": "Gems Center",
        "subtitle": "Recharge Gems & Packages Store",
        "category": "action_menu",
        "icon_url": "https://chinchins.live/uploads/customer_profile_icon/default_gems_center.png",
        "default_icon_url": "https://chinchins.live/uploads/customer_profile_icon/default_gems_center.png",
        "is_custom": false,
        "badge_text": null,
        "badge_color": null,
        "target_route": "wallet/gems_center",
        "sort_order": 6,
        "is_active": true,
        "updated_at": "2026-09-25T10:57:03+06:00"
      },
      {
        "id": 7,
        "key": "payment_details",
        "title": "Payment details",
        "subtitle": "Wallet, Transactions & Payouts",
        "category": "action_menu",
        "icon_url": "https://chinchins.live/uploads/customer_profile_icon/default_payment_details.png",
        "default_icon_url": "https://chinchins.live/uploads/customer_profile_icon/default_payment_details.png",
        "is_custom": false,
        "badge_text": null,
        "badge_color": null,
        "target_route": "wallet/payment_details",
        "sort_order": 7,
        "is_active": true,
        "updated_at": "2026-09-25T10:57:03+06:00"
      },
      {
        "id": 8,
        "key": "my_level",
        "title": "My Level",
        "subtitle": "Level Badge, Experience & Privileges",
        "category": "action_menu",
        "icon_url": "https://chinchins.live/uploads/customer_profile_icon/default_my_level.png",
        "default_icon_url": "https://chinchins.live/uploads/customer_profile_icon/default_my_level.png",
        "is_custom": false,
        "badge_text": null,
        "badge_color": null,
        "target_route": "profile/my_level",
        "sort_order": 8,
        "is_active": true,
        "updated_at": "2026-09-25T10:57:03+06:00"
      },
      {
        "id": 9,
        "key": "sign_in",
        "title": "Sign-In",
        "subtitle": "Daily Check-in & Free Claim",
        "category": "action_menu",
        "icon_url": "https://chinchins.live/uploads/customer_profile_icon/default_sign_in.png",
        "default_icon_url": "https://chinchins.live/uploads/customer_profile_icon/default_sign_in.png",
        "is_custom": false,
        "badge_text": null,
        "badge_color": null,
        "target_route": "daily_checkin",
        "sort_order": 9,
        "is_active": true,
        "updated_at": "2026-09-25T10:57:03+06:00"
      },
      {
        "id": 10,
        "key": "reward",
        "title": "Reward",
        "subtitle": "Tasks, Quests & Milestone Gifts",
        "category": "action_menu",
        "icon_url": "https://chinchins.live/uploads/customer_profile_icon/default_reward.png",
        "default_icon_url": "https://chinchins.live/uploads/customer_profile_icon/default_reward.png",
        "is_custom": false,
        "badge_text": null,
        "badge_color": null,
        "target_route": "rewards",
        "sort_order": 10,
        "is_active": true,
        "updated_at": "2026-09-25T10:57:03+06:00"
      }
    ],
    "map": {
      "my_gems": { ... },
      "beans_center": { ... },
      "spend_less_card": { ... },
      "svip": { ... },
      "my_bag": { ... },
      "gems_center": { ... },
      "payment_details": { ... },
      "my_level": { ... },
      "sign_in": { ... },
      "reward": { ... }
    },
    "offline_storage": {
      "storage_key": "chinchins_customer_profile_icons_v1",
      "version": "a1b2c3d4e5f6...",
      "last_synced_at": "2026-09-25T11:00:00+06:00",
      "data": { ... }
    }
  }
}
```

---

### ২.২ নির্দিষ্ট আইকন ফেচ করা (Get Single Icon by Key)
- **Method**: `GET`
- **URL**: `https://chinchins.live/api/customer-profile-icons/{key}`
- **উদাহরণ**: `https://chinchins.live/api/customer-profile-icons/my_gems`
- **Response**:
```json
{
  "status": true,
  "success": true,
  "data": {
    "id": 1,
    "key": "my_gems",
    "title": "My Gems",
    "subtitle": "User Diamond & Gem Balance",
    "category": "wallet_card",
    "icon_url": "https://chinchins.live/uploads/customer_profile_icon/my_gems_1727223400_ab12cd.png",
    "default_icon_url": "https://chinchins.live/uploads/customer_profile_icon/default_my_gems.png",
    "is_custom": true,
    "badge_text": null,
    "badge_color": null,
    "target_route": "wallet/gems"
  }
}
```

---

## 🛠️ ৩. এডমিন প্যানেল ইমেজ আপলোড ও ম্যানেজমেন্ট (Admin Panel Guide)

- **এডমিন ইউআরএল**: `https://chinchins.live/admin/customer-profile-icons`
- **সাইডবার মেনু**: `Customer Profile Icons`
- **ফিচারসমূহ**:
  1. প্রতিটি আইকনের বর্তমান ছবি ও লাইভ প্রিভিউ।
  2. সরাসরি ফাইল সিলেক্ট করে নতুন কাস্টম ছবি আপলোড (PNG, SVG, JPG, WebP)।
  3. টাইটেল, সাব-টাইটেল ও ব্যাজ এডিট।
  4. **Reset to Default**: কাস্টম ছবি মুছে দিয়ে পূর্বের ডিফল্ট ভেক্টর আইকন রিস্টোর করার ওয়ান-ক্লিক বাটন।
  5. ডানপাশে থাকা **Mobile Live Preview Mockup**-এ সাথে সাথে রিয়েল-টাইম প্রোফাইল পেজের ডিজাইন আপডেট দেখার সুবিধা।

---

## 📱 ৪. Flutter মোবাইল অ্যাপ ইন্টিগ্রেশন ও লোকাল স্টোরেজ গাইড (Offline Cache Strategy)

### Flutter Service Example (Zero DB Load / Instant Display)

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class CustomerProfileIconService {
  static const String _storageKey = 'chinchins_customer_profile_icons_v1';
  static const String _versionKey = 'chinchins_customer_profile_icons_version';
  static const String _apiUrl = 'https://chinchins.live/api/customer-profile-icons';

  /// 1. Get cached icon URL from Local Storage instantly (0ms latency, No DB hit)
  static String getIconUrl(String key, String fallbackAsset) {
    // Read from SharedPreferences or Hive in memory
    final cachedData = _getCachedIconMap();
    if (cachedData.containsKey(key) && cachedData[key]['icon_url'] != null) {
      return cachedData[key]['icon_url'];
    }
    return fallbackAsset;
  }

  /// 2. Sync icons on App Launch / User Login in background
  static Future<void> syncIconsBackground() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final currentVersion = prefs.getString(_versionKey) ?? '';

      final response = await http.get(
        Uri.parse(_apiUrl),
        headers: {
          'Accept': 'application/json',
          'If-None-Match': '"$currentVersion"',
        },
      );

      // If 304 Not Modified, local cache is already up-to-date!
      if (response.statusCode == 304) {
        return;
      }

      if (response.statusCode == 200) {
        final decoded = json.decode(response.body);
        if (decoded['success'] == true) {
          final data = decoded['data'];
          final versionHash = data['version_hash'] ?? '';
          final iconMap = data['map'] ?? {};

          // Save to Local Storage
          await prefs.setString(_versionKey, versionHash);
          await prefs.setString(_storageKey, json.encode(iconMap));
        }
      }
    } catch (e) {
      // Offline fallback: Use locally saved icons without throwing error
    }
  }

  static Map<String, dynamic> _getCachedIconMap() {
    // Read from persistent memory
    return {};
  }
}
```
