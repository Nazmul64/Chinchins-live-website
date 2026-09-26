# 🏆 Chinchins Live — Daily, Weekly & Monthly Rank Leaderboard & Badges System
## (Flutter Mobile Developer & RESTful API Implementation Guide)

---

## ১. অ্যাপে স্ক্রিনটি কোথায় এবং কীভাবে ওপেন হবে? (Navigation Flow)

### 📌 ট্রিগার পয়েন্ট (Screenshot ৩ অনুযায়ী):
- **অবস্থান:** মোবাইল অ্যাপের হোম স্ক্রিনে (Home / Live ট্যাব হেডার)।
- **আইকন:** উপরের ডান কোনায় লাল গোল চিহ্নিত **গোল্ডেন ট্রফি আইকন 🏆 (Trophy Icon)**।
- **অ্যাকশন:** ট্রফি আইকনে ট্যাপ করলেই ব্যবহারকারী সরাসরি **র‍্যাংক লিডারবোর্ড স্ক্রিন (Rank Leaderboard Screen)**-এ প্রবেশ করবে।

```dart
// Flutter Home / Live Tab Header
IconButton(
  icon: const Icon(Icons.emoji_events_rounded, color: Color(0xFFF59E0B), size: 26), // 🏆 Trophy Icon
  tooltip: 'Leaderboard & Ranks',
  onPressed: () {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (context) => const RankLeaderboardScreen()),
    );
  },
)
```

---

## ২. লিডারবোর্ড স্ক্রিনের ইউআই ও ডিজাইন স্পেসিফিকেশন (Screenshots ১ ও ২ অনুযায়ী)

স্ক্রিনটিতে ডার্ক থিমযুক্ত প্রিমিয়াম লাইভ-স্ট্রিমিং লুক অ্যান্ড ফিল রয়েছে:

### ক. টপ হেডার (Top Navigation Bar)
1. **ব্যাক বাটন (`<`):** পূর্ববর্তী স্ক্রিনে ফেরার জন্য।
2. **প্রাইমারি ক্যাটাগরি ট্যাব (Primary Tabs):**
   - **`SVIP`** (টপ ভিআইপি কার্ড সাবস্ক্রাইবার ও প্রিভিলেজ ইউজার)
   - **`Rich`** (সর্বোচ্চ কয়েন/ডায়মন্ড খরচকারী গিফটারগণ)
   - **`Charm`** (সর্বোচ্চ গিফট ও কয়েন অর্জনকারী আকর্ষণীয় হোস্টগণ)
   - **অ্যাক্টিভ ইন্ডিকেটর:** নির্বাচিত ট্যাবের নিচে গোল্ডেন/হোয়াইট আন্ডারলাইন।
3. **ইনফো বাটন (`?`):** র্যাংকিং নিয়মাবলী ও পুরষ্কারের নিয়ম দেখার পপআপ।

### খ. সেকেন্ডারি পিরিয়ড পিলস (Period Selector Pills)
- **`Daily`** (দৈনিক র্যাংক)
- **`Weekly`** (সাপ্তাহিক র্যাংক)
- **`Monthly`** (মাসিক র্যাংক)

### গ. কাউন্টডাউন টাইমার ও ডেট স্ট্যাটাস (Timer & Period Tag)
- **রিয়েল-টাইম টাইমার:** `⏰ 0d 19:07:06` (আজকের পিরিয়ড শেষ হতে আর কত বাকি)।
- **পিরিয়ড ট্যাগ:** `Today` (দৈনিক ক্ষেত্রে), `Current Week` (সাপ্তাহিক ক্ষেত্রে), `Current Month` (মাসিক ক্ষেত্রে)।

### ঘ. লিডারবোর্ড লিস্ট কলামস (Table Header)
- `Rank` | `Name` | `Consume 💎`

### ঙ. র্যাংক রো ডিজাইন (Leaderboard Row Items)
- **১ম স্থান (Rank 1 - Gold 🥇):** গোল্ডেন ব্যাজ/মেডেল আইকন, স্পেশাল গোল্ডেন উইংস/ক্রাউন অ্যাভাটার ফ্রেম, ইউজারের নাম, কান্ট্রি ফ্ল্যাগ (যেমন: 🇮🇳 / 🇧🇩), লেভেল ব্যাজ (যেমন: `Lv12`), ডায়মন্ড খরচ (যেমন: `31.8m 💎` / `1210m 💎`)।
- **২য় স্থান (Rank 2 - Silver 🥈):** সিলভার ব্যাজ/মেডেল আইকন, সিলভার অ্যাভাটার ফ্রেম, নাম, কান্ট্রি ফ্ল্যাগ, লেভেল ব্যাজ, ডায়মন্ড খরচ (যেমন: `21.2m 💎`)।
- **৩য় স্থান (Rank 3 - Bronze 🥉):** ব্রোঞ্জ ব্যাজ/মেডেল আইকন, ব্রোঞ্জ ফ্রেম, নাম, কান্ট্রি ফ্ল্যাগ, লেভেল ব্যাজ, ডায়মন্ড খরচ (যেমন: `18.3m 💎`)।
- **৪র্থ থেকে ৫০তম স্থান (Rank 4-50):** সাধারণ সিরিয়াল নম্বর `4`, `5`, ..., অ্যাভাটার, নাম, ফ্ল্যাগ, লেভেল এবং কনজিউমড ডায়মন্ডস।

### চ. বটম স্টিকি বার (My Rank & Distance Bar)
- স্ক্রিনের নিচে ফিক্সড স্টিকি বার:
  - `Distance from rank is: 1569928 💎`
  - (ব্যবহারকারী বর্তমানে কততম অবস্থানে আছেন এবং পরবর্তী র্যাংকে উঠতে আর কত কয়েন/ডায়মন্ড লাগবে তার লাইভ দূরত্ব)।

---

## ৩. ব্যাকএন্ড RESTful এপিআই এন্ডপয়েন্ট (Backend APIs)

### এপিআই ১: লিডারবোর্ড র্যাংক ডাটা ফেচ
- **এন্ডপয়েন্ট:** `GET /api/ranks/leaderboard` (অথবা `GET /api/leaderboard`)
- **কোয়েরি প্যারামিটারসমূহ:**
  - `category`: `rich` | `charm` | `svip` (Default: `rich`)
  - `period`: `daily` | `weekly` | `monthly` (Default: `daily`)
  - `limit`: `50` (Default: 50)
  - `user_id`: (অপশনাল - কারেন্ট ইউজারের দূরত্ব হিসাব করতে)

#### রিকোয়েস্ট উদাহরণ:
```http
GET /api/ranks/leaderboard?category=rich&period=daily HTTP/1.1
Host: your-domain.com
Accept: application/json
```

#### রেসপন্স উদাহরণ (`200 OK`):
```json
{
  "success": true,
  "status": true,
  "meta": {
    "category": "rich",
    "period": "daily",
    "period_label": "Today",
    "countdown_seconds": 66293,
    "countdown_human": "0d 18:24:53",
    "total_ranked": 8
  },
  "my_rank": {
    "is_ranked": false,
    "rank": null,
    "consume": 0,
    "consume_formatted": "0",
    "distance_from_rank": 9040001,
    "distance_formatted": "9m",
    "label": "Distance from rank is: 9,040,001 💎"
  },
  "rankings": [
    {
      "rank": 1,
      "user_id": 101,
      "account_id": "743264901",
      "name": "💕 🇮🇳 ABHI 🇮🇳 ...",
      "avatar": "https://your-domain.com/uploads/avatars/user_101.jpg",
      "level": 12,
      "country": "IN",
      "country_flag": "🇮🇳",
      "consume": 31800000,
      "consume_formatted": "31.8m",
      "badge_icon_url": "https://your-domain.com/uploads/ranks/badges/daily_badge_gold.png",
      "avatar_frame_url": "https://your-domain.com/uploads/ranks/frames/daily_frame_gold.png"
    },
    {
      "rank": 2,
      "user_id": 102,
      "account_id": "743264902",
      "name": "X-Factor",
      "avatar": "https://your-domain.com/uploads/avatars/user_102.jpg",
      "level": 9,
      "country": "IN",
      "country_flag": "🇮🇳",
      "consume": 21200000,
      "consume_formatted": "21.2m",
      "badge_icon_url": "https://your-domain.com/uploads/ranks/badges/daily_badge_silver.png",
      "avatar_frame_url": "https://your-domain.com/uploads/ranks/frames/daily_frame_silver.png"
    },
    {
      "rank": 3,
      "user_id": 103,
      "account_id": "743264903",
      "name": "Guest_COc4hV",
      "avatar": "https://your-domain.com/uploads/avatars/user_103.jpg",
      "level": 8,
      "country": "SA",
      "country_flag": "🇸🇦",
      "consume": 18300000,
      "consume_formatted": "18.3m",
      "badge_icon_url": "https://your-domain.com/uploads/ranks/badges/daily_badge_bronze.png",
      "avatar_frame_url": "https://your-domain.com/uploads/ranks/frames/daily_frame_bronze.png"
    },
    {
      "rank": 4,
      "user_id": 104,
      "account_id": "743264904",
      "name": "SAM",
      "avatar": "https://your-domain.com/uploads/avatars/user_104.jpg",
      "level": 7,
      "country": "AE",
      "country_flag": "🇦🇪",
      "consume": 12800000,
      "consume_formatted": "12.8m",
      "badge_icon_url": null,
      "avatar_frame_url": null
    }
  ]
}
```

---

### এপিআই ২: ব্যাজ ও ফ্রেম কনফিগারেশন (Hive Local Cache Sync)
- **এন্ডপয়েন্ট:** `GET /api/app/rank-badges-config`
- **উদ্দেশ্য:** অ্যাপ ওপেন হলে ব্যাকগ্রাউন্ডে কল হবে এবং অ্যাডমিন প্যানেল থেকে আপলোড করা সমস্ত ব্যাজ ও ফ্রেমের মেটাডাটা লোকাল মেমোরিতে (Hive) ক্যাশ করবে।

---

## ৪. ফ্লাটার মোবাইল অ্যাপ সম্পূর্ণ স্ক্রিন কোড (Flutter Implementation)

ফ্লাটার ডেভেলপার সরাসরি নিচের ফাইলটি অ্যাপে যুক্ত করতে পারবেন:

### ফাইল: `lib/screens/rank_leaderboard_screen.dart`
```dart
import 'dart:async';
import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:dio/dio.dart';

class RankLeaderboardScreen extends StatefulWidget {
  const RankLeaderboardScreen({Key? key}) : super(key: key);

  @override
  State<RankLeaderboardScreen> createState() => _RankLeaderboardScreenState();
}

class _RankLeaderboardScreenState extends State<RankLeaderboardScreen> {
  String selectedCategory = 'rich'; // 'svip', 'rich', 'charm'
  String selectedPeriod = 'daily';  // 'daily', 'weekly', 'monthly'

  bool isLoading = true;
  Map<String, dynamic>? metaData;
  Map<String, dynamic>? myRankData;
  List<dynamic> rankings = [];

  int countdownSeconds = 0;
  Timer? _countdownTimer;

  @override
  void initState() {
    super.initState();
    fetchLeaderboard();
  }

  @override
  void dispose() {
    _countdownTimer?.cancel();
    super.dispose();
  }

  Future<void> fetchLeaderboard() async {
    setState(() => isLoading = true);
    try {
      final dio = Dio();
      final url = 'https://your-domain.com/api/ranks/leaderboard?category=$selectedCategory&period=$selectedPeriod';
      final response = await dio.get(url);

      if (response.statusCode == 200 && response.data['success'] == true) {
        setState(() {
          metaData = response.data['meta'];
          myRankData = response.data['my_rank'];
          rankings = response.data['rankings'] ?? [];
          countdownSeconds = (metaData?['countdown_seconds'] ?? 0).toInt();
          isLoading = false;
        });
        startCountdown();
      }
    } catch (e) {
      setState(() => isLoading = false);
    }
  }

  void startCountdown() {
    _countdownTimer?.cancel();
    _countdownTimer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (countdownSeconds > 0) {
        setState(() => countdownSeconds--);
      } else {
        timer.cancel();
      }
    });
  }

  String formatCountdown(int totalSeconds) {
    int days = totalSeconds ~/ 86400;
    int hours = (totalSeconds % 86400) ~/ 3600;
    int minutes = (totalSeconds % 3600) ~/ 60;
    int seconds = totalSeconds % 60;
    return '${days}d ${hours.toString().padLeft(2, '0')}:${minutes.toString().padLeft(2, '0')}:${seconds.toString().padLeft(2, '0')}';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF141416),
      appBar: AppBar(
        backgroundColor: const Color(0xFF141416),
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded, color: Colors.white, size: 20),
          onPressed: () => Navigator.pop(context),
        ),
        title: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            _buildCategoryTab('SVIP', 'svip'),
            const SizedBox(width: 24),
            _buildCategoryTab('Rich', 'rich'),
            const SizedBox(width: 24),
            _buildCategoryTab('Charm', 'charm'),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.help_outline_rounded, color: Colors.white70, size: 22),
            onPressed: () => _showRulesDialog(),
          ),
        ],
      ),
      body: Column(
        children: [
          const SizedBox(height: 10),
          // Period Selector Pills (Daily, Weekly, Monthly)
          _buildPeriodSelector(),
          const SizedBox(height: 14),

          // Countdown Timer & Period Label
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    const Icon(Icons.access_time_rounded, color: Colors.white60, size: 16),
                    const SizedBox(width: 6),
                    Text(
                      formatCountdown(countdownSeconds),
                      style: const TextStyle(color: Colors.white70, fontSize: 13, fontWeight: FontWeight.w600),
                    ),
                  ],
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.08),
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.swap_horiz_rounded, color: Colors.white70, size: 14),
                      const SizedBox(width: 4),
                      Text(
                        metaData?['period_label'] ?? 'Today',
                        style: const TextStyle(color: Colors.white70, fontSize: 12, fontWeight: FontWeight.bold),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 12),

          // List Headers: Rank | Name | Consume
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20),
            child: Row(
              children: const [
                Text('Rank', style: TextStyle(color: Colors.white38, fontSize: 12, fontWeight: FontWeight.w600)),
                SizedBox(width: 40),
                Text('Name', style: TextStyle(color: Colors.white38, fontSize: 12, fontWeight: FontWeight.w600)),
                Spacer(),
                Text('Consume', style: TextStyle(color: Colors.white38, fontSize: 12, fontWeight: FontWeight.w600)),
              ],
            ),
          ),
          const SizedBox(height: 8),

          // Leaderboard List Items
          Expanded(
            child: isLoading
                ? const Center(child: CircularProgressIndicator(color: Color(0xFFF59E0B)))
                : ListView.builder(
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
                    itemCount: rankings.length,
                    itemBuilder: (context, index) {
                      final item = rankings[index];
                      return _buildLeaderboardTile(item);
                    },
                  ),
          ),

          // Sticky Bottom "Distance from rank is: ..." Bar
          _buildBottomUserStatusBar(),
        ],
      ),
    );
  }

  Widget _buildCategoryTab(String title, String key) {
    final bool isSelected = selectedCategory == key;
    return GestureDetector(
      onTap: () {
        if (selectedCategory != key) {
          setState(() => selectedCategory = key);
          fetchLeaderboard();
        }
      },
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(
            title,
            style: TextStyle(
              color: isSelected ? Colors.white : Colors.white54,
              fontSize: 17,
              fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
            ),
          ),
          const SizedBox(height: 4),
          if (isSelected)
            Container(width: 22, height: 3, decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(2))),
        ],
      ),
    );
  }

  Widget _buildPeriodSelector() {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 24),
      padding: const EdgeInsets.all(3),
      decoration: BoxDecoration(
        color: const Color(0xFF26262B),
        borderRadius: BorderRadius.circular(24),
      ),
      child: Row(
        children: [
          _buildPeriodPill('Daily', 'daily'),
          _buildPeriodPill('Weekly', 'weekly'),
          _buildPeriodPill('Monthly', 'monthly'),
        ],
      ),
    );
  }

  Widget _buildPeriodPill(String title, String key) {
    final bool isSelected = selectedPeriod == key;
    return Expanded(
      child: GestureDetector(
        onTap: () {
          if (selectedPeriod != key) {
            setState(() => selectedPeriod = key);
            fetchLeaderboard();
          }
        },
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 8),
          decoration: BoxDecoration(
            color: isSelected ? Colors.white : Colors.transparent,
            borderRadius: BorderRadius.circular(20),
          ),
          alignment: Alignment.center,
          child: Text(
            title,
            style: TextStyle(
              color: isSelected ? const Color(0xFF141416) : Colors.white70,
              fontSize: 13,
              fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildLeaderboardTile(Map<String, dynamic> item) {
    int rank = item['rank'];
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
      decoration: BoxDecoration(
        color: const Color(0xFF1E1E22),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(
        children: [
          // Rank Badge / Icon
          SizedBox(
            width: 32,
            child: _buildRankBadgeWidget(rank, item['badge_icon_url']),
          ),
          const SizedBox(width: 12),

          // User Avatar with Avatar Frame
          Stack(
            alignment: Alignment.center,
            children: [
              ClipRRect(
                borderRadius: BorderRadius.circular(22),
                child: CachedNetworkImage(
                  imageUrl: item['avatar'] ?? '',
                  width: 44,
                  height: 44,
                  fit: BoxFit.cover,
                  errorWidget: (_, __, ___) => const CircleAvatar(radius: 22, backgroundColor: Colors.white24, child: Icon(Icons.person, color: Colors.white)),
                ),
              ),
              if (item['avatar_frame_url'] != null && (item['avatar_frame_url'] as String).isNotEmpty)
                CachedNetworkImage(
                  imageUrl: item['avatar_frame_url'],
                  width: 60,
                  height: 60,
                  fit: BoxFit.contain,
                ),
            ],
          ),
          const SizedBox(width: 12),

          // User Name, Country Flag & Level
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  item['name'] ?? '',
                  style: const TextStyle(color: Colors.white, fontSize: 14, fontWeight: FontWeight.bold),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 4),
                Row(
                  children: [
                    if (item['country_flag'] != null) ...[
                      Text(item['country_flag'], style: const TextStyle(fontSize: 12)),
                      const SizedBox(width: 4),
                    ],
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1),
                      decoration: BoxDecoration(
                        color: Colors.blueAccent,
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        'Lv${item['level'] ?? 1}',
                        style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),

          // Consumed Diamonds / Coins
          Row(
            children: [
              const Icon(Icons.diamond_rounded, color: Color(0xFFF59E0B), size: 16),
              const SizedBox(width: 4),
              Text(
                item['consume_formatted'] ?? '${item['consume']}',
                style: const TextStyle(color: Colors.white, fontSize: 14, fontWeight: FontWeight.bold),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildRankBadgeWidget(int rank, String? badgeIconUrl) {
    if (badgeIconUrl != null && badgeIconUrl.isNotEmpty) {
      return CachedNetworkImage(imageUrl: badgeIconUrl, width: 28, height: 28, fit: BoxFit.contain);
    }
    if (rank == 1) {
      return const Icon(Icons.military_tech_rounded, color: Color(0xFFFFD700), size: 28);
    } else if (rank == 2) {
      return const Icon(Icons.military_tech_rounded, color: Color(0xFFC0C0C0), size: 26);
    } else if (rank == 3) {
      return const Icon(Icons.military_tech_rounded, color: Color(0xFFCD7F32), size: 24);
    } else {
      return Text(
        '$rank',
        textAlign: TextAlign.center,
        style: const TextStyle(color: Colors.white54, fontSize: 14, fontWeight: FontWeight.bold),
      );
    }
  }

  Widget _buildBottomUserStatusBar() {
    final String label = myRankData?['label'] ?? 'Distance from rank is: 10,000 💎';
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 16),
      decoration: const BoxDecoration(
        color: Color(0xFF202024),
        border: Border(top: BorderSide(color: Colors.white12, width: 0.5)),
      ),
      child: SafeArea(
        top: false,
        child: Text(
          label,
          textAlign: TextAlign.center,
          style: const TextStyle(
            color: Color(0xFFE2E8F0),
            fontSize: 13,
            fontWeight: FontWeight.w600,
            letterSpacing: 0.2,
          ),
        ),
      ),
    );
  }

  void _showRulesDialog() {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: const Color(0xFF1E1E22),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Leaderboard Rules', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
        content: const Text(
          '1. Daily leaderboard resets every midnight (00:00 UTC).\n'
          '2. Weekly leaderboard resets every Monday.\n'
          '3. Monthly leaderboard resets on the 1st of each month.\n'
          '4. Top 3 ranks receive exclusive badges and avatar frames in their profile!',
          style: TextStyle(color: Colors.white70, fontSize: 13, height: 1.5),
        ),
        actions: [
          TextButton(
            child: const Text('Got it', style: TextStyle(color: Color(0xFFF59E0B), fontWeight: FontWeight.bold)),
            onPressed: () => Navigator.pop(ctx),
          ),
        ],
      ),
    );
  }
}
```

---

## ৫. সংক্ষেপে মোবাইল অ্যাপ ডেভলপারের চেকলিস্ট

| ধাপ | কাজ | স্ট্যাটাস |
| :--- | :--- | :--- |
| **১** | হোম পেজের টপ ডান পাশের ট্রফি আইকনে (🏆) `RankLeaderboardScreen` নেভিগেশন লিংক করা | সম্পন্ন |
| **২** | স্ক্রিনের টপ ক্যাটাগরি ট্যাব (`SVIP`, `Rich`, `Charm`) ও পিরিয়ড পিলস (`Daily`, `Weekly`, `Monthly`) সেট করা | সম্পন্ন |
| **৩** | ব্যাকএন্ড এপিআই `GET /api/ranks/leaderboard?category=...&period=...` কল করে ডাটা লোড করা | সম্পন্ন |
| **৪** | টপ ১, ২, ৩ ইউজারের জন্য `badge_icon_url` এবং `avatar_frame_url` অ্যাভাটারের উপরে ডিসপ্লে করা | সম্পন্ন |
| **৫** | নিচে কারেন্ট ইউজারের গ্যাপ বার (`Distance from rank is: ... 💎`) শো করা | সম্পন্ন |
