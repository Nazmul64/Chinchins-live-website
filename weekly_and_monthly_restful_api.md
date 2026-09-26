# 🏆 Daily, Weekly & Monthly Rank Badges & Frames RESTful API & Hive Sync Documentation

## ১. ওভারভিউ (System Overview)
Chinchins Live প্ল্যাটফর্মে প্রতিদিন (Daily), প্রতি সপ্তাহে (Weekly), এবং প্রতি মাসে (Monthly) টপ র‍্যাংক অর্জনকারী ব্যবহারকারীদের (Rich/Gifter এবং Charm/Host) জন্য স্পেশাল **র‍্যাংক ব্যাজ (Badge Icon)** এবং **অ্যাভাটার ফ্রেম (Avatar Frame)** সিস্টেম তৈরি করা হয়েছে। 

এই সিস্টেমটি ব্যাকএন্ডে অ্যাডমিন প্যানেল দ্বারা সম্পূর্ণ কনফিগারযোগ্য এবং মোবাইল অ্যাপে **Hive (Local Memory / NoSQL Storage)**-এর মাধ্যমে তাৎক্ষণিক (Zero Latency) লোড হওয়ার জন্য ডিজাইন করা।

---

## ২. ফাইল ও আপলোড ডিরেক্টরি (File & Storage Structure)

| কনটেন্ট | আপলোড ডিরেক্টরি | ফরম্যাট | পাবলিক ইউআরএল |
| :--- | :--- | :--- | :--- |
| **ব্যাজ আইকন (Badge Icons)** | `public/uploads/ranks/badges/` | PNG, WebP (Max 2MB) | `{BASE_URL}/uploads/ranks/badges/...` |
| **অ্যাভাটার ফ্রেম (Avatar Frames)** | `public/uploads/ranks/frames/` | PNG, WebP (Max 2MB) | `{BASE_URL}/uploads/ranks/frames/...` |

---

## ৩. ডাটাবেস স্কিমা (Database Schema)

### টেবিল ১: `period_rank_badges`
| কলাম | টাইপ | বিবরণ |
| :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED (PK)` | অটো ইনক্রিমেন্ট আইডি |
| `badge_name` | `VARCHAR(100)` | ব্যাজের নাম (যেমন: Daily Top Star, Weekly Champion, Monthly Legend) |
| `period_type` | `ENUM('daily', 'weekly', 'monthly')` | র্যাংকের পিরিয়ড |
| `category` | `ENUM('rich', 'charm')` | রিচ (খরচকারী / গিফটার) নাকি চার্ম (আকর্ষণীয় / হোস্ট আর্নার) |
| `rank_position` | `INT` | র্যাংক পজিশন (১ = Gold, ২ = Silver, ৩ = Bronze, ৪+ = Elite) |
| `min_required_coins` | `BIGINT UNSIGNED` | ব্যাজ পাওয়ার জন্য ন্যূনতম কয়েন থ্রেশহোল্ড (Default: 0) |
| `badge_icon` | `VARCHAR(255)` | ফাইল পাথ (যেমন: `uploads/ranks/badges/daily_badge_xxx.png`) |
| `avatar_frame` | `VARCHAR(255)` (Nullable) | ফ্রেম ফাইল পাথ (যেমন: `uploads/ranks/frames/daily_frame_xxx.png`) |
| `is_active` | `BOOLEAN` | সক্রিয় কিনা (Default: true) |
| `created_at` / `updated_at` | `TIMESTAMP` | টাইমস্ট্যাম্প |

### টেবিল ২: `user_period_badges` (ব্যবহারকারীদের অর্জিত ব্যাজ হিস্ট্রি)
| কলাম | টাইপ | বিবরণ |
| :--- | :--- | :--- |
| `id` | `BIGINT UNSIGNED (PK)` | আইডি |
| `user_id` | `BIGINT UNSIGNED (FK)` | ব্যবহারকারী আইডি (`users.id`) |
| `badge_id` | `BIGINT UNSIGNED (FK)` | অর্জিত ব্যাজ আইডি (`period_rank_badges.id`) |
| `period_type` | `ENUM('daily', 'weekly', 'monthly')` | পিরিয়ড |
| `awarded_date` | `DATE` | অর্জনের তারিখ |
| `is_equipped` | `BOOLEAN` | প্রোফাইলে সজ্জিত (Equipped) আছে কিনা |
| `created_at` / `updated_at` | `TIMESTAMP` | টাইমস্ট্যাম্প |

---

## ৪. অ্যাডমিন প্যানেল ম্যানেজমেন্ট (Admin Panel Endpoints)

| মেথড | রাউট | নাম | বিবরণ |
| :--- | :--- | :--- | :--- |
| `GET` | `/admin/rank-badges` | `admin.rank-badges.index` | ডেইলি, উইকলি ও মান্থলি ব্যাজ লিস্ট ও প্রিভিউ |
| `POST` | `/admin/rank-badges` | `admin.rank-badges.store` | নতুন ব্যাজ ও ফ্রেম আপলোড এবং ক্যাশ ফ্লাশ |
| `POST` | `/admin/rank-badges/{id}/toggle` | `admin.rank-badges.toggle` | ব্যাজ অ্যাক্টিভ/ডিঅ্যাক্টিভ টগল |
| `DELETE` | `/admin/rank-badges/{id}` | `admin.rank-badges.destroy` | ব্যাজ ও ফ্রেম স্থায়ীভাবে ডিলিট |

> **অটো ক্যাশ ইনভ্যালিডেশন:** যখনই অ্যাডমিন প্যানেলে কোনো ব্যাজ আপলোড, এডিট বা ডিলিট করা হয়, ব্যাকএন্ড স্বয়ংক্রিয়ভাবে `\Cache::forget('app_period_rank_badges')` এক্সিকিউট করে যাতে মোবাইল অ্যাপ তাৎক্ষণিক নতুন ডাটা পায়।

---

## ৫. মোবাইল অ্যাপ RESTful API (Hive Sync API)

### 📌 এন্ডপয়েন্ট: `GET /api/app/rank-badges-config`
- **বিকল্প অ্যালিয়াস:** `GET /api/ranks/badges-config`, `GET /api/rank-badges-config`
- **অথেনটিকেশন:** প্রয়োজন নেই (পাবলিক কনফিগ)
- **ক্যাশিং:** সার্ভার সাইড মেমোরি ক্যাশ (`rememberForever`), জিরো ডাটাবেস কোয়েরি ওভারহেড।

#### রিকোয়েস্ট:
```http
GET /api/app/rank-badges-config HTTP/1.1
Host: your-domain.com
Accept: application/json
```

#### রেসপন্স ফরম্যাট (`200 OK`):
```json
{
  "success": true,
  "status": true,
  "data": {
    "daily": [
      {
        "id": 1,
        "badge_name": "Daily Top Star #1",
        "period_type": "daily",
        "category": "rich",
        "rank_position": 1,
        "min_required_coins": 100000,
        "badge_icon": "uploads/ranks/badges/daily_badge_1790396800.png",
        "avatar_frame": "uploads/ranks/frames/daily_frame_1790396800.png",
        "badge_icon_url": "https://your-domain.com/uploads/ranks/badges/daily_badge_1790396800.png",
        "avatar_frame_url": "https://your-domain.com/uploads/ranks/frames/daily_frame_1790396800.png",
        "is_active": true
      },
      {
        "id": 2,
        "badge_name": "Daily Charm Star #1",
        "period_type": "daily",
        "category": "charm",
        "rank_position": 1,
        "min_required_coins": 50000,
        "badge_icon": "uploads/ranks/badges/daily_badge_charm_1.png",
        "avatar_frame": "uploads/ranks/frames/daily_frame_charm_1.png",
        "badge_icon_url": "https://your-domain.com/uploads/ranks/badges/daily_badge_charm_1.png",
        "avatar_frame_url": "https://your-domain.com/uploads/ranks/frames/daily_frame_charm_1.png",
        "is_active": true
      }
    ],
    "weekly": [
      {
        "id": 3,
        "badge_name": "Weekly Grand Champion #1",
        "period_type": "weekly",
        "category": "rich",
        "rank_position": 1,
        "min_required_coins": 500000,
        "badge_icon": "uploads/ranks/badges/weekly_badge_rich_1.png",
        "avatar_frame": "uploads/ranks/frames/weekly_frame_rich_1.png",
        "badge_icon_url": "https://your-domain.com/uploads/ranks/badges/weekly_badge_rich_1.png",
        "avatar_frame_url": "https://your-domain.com/uploads/ranks/frames/weekly_frame_rich_1.png",
        "is_active": true
      }
    ],
    "monthly": [
      {
        "id": 4,
        "badge_name": "Monthly Supreme Legend #1",
        "period_type": "monthly",
        "category": "rich",
        "rank_position": 1,
        "min_required_coins": 2000000,
        "badge_icon": "uploads/ranks/badges/monthly_badge_rich_1.png",
        "avatar_frame": "uploads/ranks/frames/monthly_frame_rich_1.png",
        "badge_icon_url": "https://your-domain.com/uploads/ranks/badges/monthly_badge_rich_1.png",
        "avatar_frame_url": "https://your-domain.com/uploads/ranks/frames/monthly_frame_rich_1.png",
        "is_active": true
      }
    ]
  }
}
```

---

## ৬. Flutter মোবাইল অ্যাপে Hive লোকাল মেমোরি আর্কিটেকচার (Client-Side Implementation)

মোবাইল অ্যাপ যাতে প্রতিটি পেজে বা লাইভ স্ট্রিমিং ও পার্টি রুমে প্রবেশের সময় কোনো নেটওয়ার্ক কল ছাড়াই সাথে সাথে ব্যাজ ও ফ্রেম প্রদর্শন করতে পারে, তার জন্য লোকাল স্টোরেজ (Hive) ব্যবহার করা হয়েছে।

### ক. Hive ডাটা মডেল (`period_rank_badge_model.dart`)
```dart
import 'package:hive/hive.dart';

part 'period_rank_badge_model.g.dart';

@HiveType(typeId: 25)
class PeriodRankBadgeModel extends HiveObject {
  @HiveField(0)
  final int id;

  @HiveField(1)
  final String badgeName;

  @HiveField(2)
  final String periodType; // daily, weekly, monthly

  @HiveField(3)
  final String category; // rich, charm

  @HiveField(4)
  final int rankPosition; // 1, 2, 3...

  @HiveField(5)
  final int minRequiredCoins;

  @HiveField(6)
  final String badgeIconUrl;

  @HiveField(7)
  final String? avatarFrameUrl;

  PeriodRankBadgeModel({
    required this.id,
    required this.badgeName,
    required this.periodType,
    required this.category,
    required this.rankPosition,
    required this.minRequiredCoins,
    required this.badgeIconUrl,
    this.avatarFrameUrl,
  });

  factory PeriodRankBadgeModel.fromJson(Map<String, dynamic> json) {
    return PeriodRankBadgeModel(
      id: json['id'] ?? 0,
      badgeName: json['badge_name'] ?? '',
      periodType: json['period_type'] ?? 'daily',
      category: json['category'] ?? 'rich',
      rankPosition: json['rank_position'] ?? 1,
      minRequiredCoins: json['min_required_coins'] ?? 0,
      badgeIconUrl: json['badge_icon_url'] ?? '',
      avatarFrameUrl: json['avatar_frame_url'],
    );
  }
}
```

### খ. Hive সিঙ্ক সার্ভিস (`rank_badge_local_service.dart`)
```dart
import 'package:dio/dio.dart';
import 'package:hive/hive.dart';
import 'period_rank_badge_model.dart';

class RankBadgeLocalService {
  static const String boxName = 'period_rank_badges_box';

  static Future<Box<PeriodRankBadgeModel>> getBox() async {
    return await Hive.openBox<PeriodRankBadgeModel>(boxName);
  }

  /// অ্যাপ ওপেন হলে ব্যাকগ্রাউন্ডে কল হবে এবং লোকাল মেমোরি আপডেট করবে
  static Future<void> syncRankBadgesFromServer() async {
    try {
      final dio = Dio();
      final response = await dio.get('https://your-domain.com/api/app/rank-badges-config');

      if (response.statusCode == 200 && response.data['success'] == true) {
        final box = await getBox();
        await box.clear(); // পূর্বের পুরাতন ক্যাশ ক্লিয়ার

        final data = response.data['data'];
        final periods = ['daily', 'weekly', 'monthly'];

        for (final period in periods) {
          if (data[period] != null) {
            for (final item in data[period]) {
              final badge = PeriodRankBadgeModel.fromJson(item);
              // Key format: "daily_rich_1", "weekly_charm_1" etc.
              final key = '${badge.periodType}_${badge.category}_${badge.rankPosition}';
              await box.put(key, badge);
            }
          }
        }
      }
    } catch (e) {
      // অফলাইন থাকলে পূর্বের সেভ করা লোকাল ক্যাশই চালু থাকবে
      print('Rank badges sync error: $e');
    }
  }

  /// লোকাল মেমোরি থেকে ইনস্ট্যান্ট 0ms-এ ব্যাজ রিটার্ন করবে
  static PeriodRankBadgeModel? getBadgeInstant(String periodType, String category, int rank) {
    final box = Hive.box<PeriodRankBadgeModel>(boxName);
    return box.get('${periodType}_${category}_$rank');
  }
}
```

### গ. লাইভ রুম ও লিডারবোর্ডে ইনস্ট্যান্ট ডিসপ্লে উইজেট
```dart
Widget buildRankBadge(String period, String category, int rank) {
  final badge = RankBadgeLocalService.getBadgeInstant(period, category, rank);
  if (badge == null || badge.badgeIconUrl.isEmpty) {
    return const SizedBox.shrink();
  }

  return CachedNetworkImage(
    imageUrl: badge.badgeIconUrl,
    width: 24,
    height: 24,
    placeholder: (context, url) => const SizedBox(width: 24, height: 24),
    errorWidget: (context, url, error) => const Icon(Icons.star, size: 20, color: Colors.amber),
  );
}
```

---

## ৭. সংক্ষেপে মূল সুবিধাসমূহ
1. **জিরো নেটওয়ার্ক ল্যাগ:** মোবাইল অ্যাপ ওপেন হলেই লোকাল Hive মেমোরি থেকে অফলাইনেও সব র্যাংক ব্যাজ ও অ্যাভাটার ফ্রেম তাৎক্ষণিক দেখা যাবে।
2. **ডাইনামিক অ্যাডমিন কন্ট্রোল:** অ্যাডমিন যেকোনো সময় নতুন র্যাংকের জন্য ব্যাজ/ফ্রেম আপলোড বা পরিবর্তন করতে পারেন।
3. **ক্যাশিং অপ্টিমাইজেশন:** Laravel `rememberForever` ক্যাশিং থাকায় সার্ভার প্রসেসরে কোনো চাপ পড়ে না।
