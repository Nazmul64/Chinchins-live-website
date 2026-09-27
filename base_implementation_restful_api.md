# 🏆 Chinchins Live - Profile Base & Rank Frame Complete RESTful API Guide
**API Version:** `v1.0.0` (Production Ready)  
**Base URL:** `https://chinchins.live/api`  
**Flutter Architecture:** Zero Latency Offline-First Hive Caching + Real-Time Sync

---

## ১. ওভারভিউ ও আর্কিটেকচার (Overview & Architecture)

এই ডকুমেন্টে অ্যাপের **৩টি গুরুত্বপূর্ণ ফিচার** এর সম্পূর্ণ গাইড দেওয়া হলো:
1. **Profile Base Frame & Period Rank Frame (Level 1-10+ এবং Daily/Weekly/Monthly Rank #1-#3):**
   - ইউজার লেভেল আপগ্রেড হলে (Level 1 Base Frame) অথবা লিডারবোর্ডে টপ র‍্যাঙ্কে আসলে (Daily/Weekly/Monthly Rank Frame), তার অ্যাভাটারের চারপাশে গোল্ডেন/লাক্সারি ফ্রেম এবং প্রোফাইলে ব্যাজ ডিসপ্লে হবে।
2. **হোম পেজের ট্রফি আইকন লজিক (🏆 Trophy Icon: Hot vs Live Tab):**
   - **`Hot` ট্যাবে** ট্রফি আইকন **সম্পূর্ণ হাইড** থাকবে।
   - **`Live` ট্যাবে** ট্রফি আইকন **দৃশ্যমান (Visible)** থাকবে এবং ক্লিকে `RankLeaderboardScreen`-এ যাবে।
3. **লাইভ স্ট্রিম এক্সিট ও স্ট্যাটাস রিয়েলটাইম আপডেট (Live Stream Auto-End on Leave):**
   - হোস্ট লাইভ স্ক্রিন কেটে দিলে বা বের হয়ে গেলে সার্ভারে স্ট্যাটাস সাথে সাথে `ended` হবে, ফলে হোম ফিড বা প্রোফাইলে ভুল করে লাইভ চলমান দেখাবে না।

---

## ২. Profile & Me Screen API (`GET /api/profile`, `GET /api/user/profile`, `GET /api/auth/me`)

ইউজারের নিজের প্রোফাইল (`Me` Tab), ফিড ইউজার কার্ড বা অন্যের প্রোফাইল স্ক্রিন ওপেন করলে এই এপিআই কল হয়।

### রিকোয়েস্ট:
```http
GET /api/profile HTTP/1.1
Host: chinchins.live
Authorization: Bearer <USER_BEARER_TOKEN>
Accept: application/json
```

### রেসপন্স (Response Payload):
```json
{
  "status": true,
  "data": {
    "user": {
      "id": 40985974,
      "account_id": "40985974",
      "name": "nazmul Hossain",
      "display_name": "nazmul Hossain",
      "nickname": "nazmul",
      "avatar": "https://chinchins.live/uploads/profiles/avatar_123.jpg",
      "avatar_url": "https://chinchins.live/uploads/profiles/avatar_123.jpg",
      "current_level": 1,
      "display_level": "Lv.1",
      "my_gems": 14120,
      "coins": 14120,
      "beans": 0,
      "i_like": 0,
      "like_me": 6,
      "followers_count": 0,
      "following_count": 0,
      "avatar_frame_url": "https://chinchins.live/uploads/ranks/frames/daily_rich_frame1_1790481576.png",
      "base_frame_url": "https://chinchins.live/uploads/bases/profile_base_1_1790479123.png",
      "frame_url": "https://chinchins.live/uploads/ranks/frames/daily_rich_frame1_1790481576.png",
      "active_avatar_frame_url": "https://chinchins.live/uploads/ranks/frames/daily_rich_frame1_1790481576.png",
      "rank_badge_frame_url": "https://chinchins.live/uploads/ranks/frames/daily_rich_frame1_1790481576.png",
      "rank_badge_icon_url": "https://chinchins.live/uploads/ranks/badges/daily_rich_rank1_1790481576.png",
      "badge_icon": "crown",
      "badge_color": "#f59e0b",
      "level_info": {
        "current_level": 1,
        "level_name": "Level 1",
        "earned_coins": 14120,
        "required_coins": 1000,
        "next_level": 2,
        "next_required_coins": 2000,
        "progress_percent": 100.0,
        "avatar_frame_url": "https://chinchins.live/uploads/bases/profile_base_1_1790479123.png",
        "badge_icon": "crown",
        "badge_color": "#f59e0b"
      }
    }
  }
}
```

---

## ৩. Flutter ইউআই ইমপ্লিমেন্টেশন (Avatar Frame & Base Overlay Widget)

Flutter অ্যাপের `Me` প্রোফাইল পেজ, হোম ফিড এবং চ্যাট হেডার-এ ইউজারের ছবির চারপাশে ফ্রেম শো করানোর জন্য রি-ইউজেবল উইজেট:

```dart
import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';

class UserAvatarWithFrame extends StatelessWidget {
  final String avatarUrl;
  final String? frameUrl;
  final String? rankBadgeIconUrl;
  final double avatarSize;
  final double frameSize;
  final VoidCallback? onTap;

  const UserAvatarWithFrame({
    Key? key,
    required this.avatarUrl,
    this.frameUrl,
    this.rankBadgeIconUrl,
    this.avatarSize = 76.0,
    this.frameSize = 104.0,
    this.onTap,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: SizedBox(
        width: frameSize,
        height: frameSize,
        child: Stack(
          alignment: Alignment.center,
          clipBehavior: Clip.none,
          children: [
            // ১. মূল গোলাকার প্রোফাইল ছবি
            Container(
              width: avatarSize,
              height: avatarSize,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                border: Border.all(
                  color: (frameUrl == null || frameUrl!.isEmpty) 
                      ? const Color(0xFF38BDF8).withOpacity(0.6) 
                      : Colors.transparent,
                  width: 2.0,
                ),
              ),
              child: ClipOval(
                child: CachedNetworkImage(
                  imageUrl: avatarUrl,
                  fit: BoxFit.cover,
                  placeholder: (context, url) => Container(
                    color: const Color(0xFF1E293B),
                    child: const Icon(Icons.person, color: Colors.white54),
                  ),
                  errorWidget: (context, url, error) => Container(
                    color: const Color(0xFF1E293B),
                    child: const Icon(Icons.person, color: Colors.white54),
                  ),
                ),
              ),
            ),

            // ২. গোল্ডেন র‍্যাঙ্ক / লেভেল বেস ফ্রেম (Overlay Frame)
            if (frameUrl != null && frameUrl!.isNotEmpty)
              Positioned.fill(
                child: IgnorePointer(
                  child: CachedNetworkImage(
                    imageUrl: frameUrl!,
                    fit: BoxFit.contain,
                    errorWidget: (context, url, error) => const SizedBox.shrink(),
                  ),
                ),
              ),

            // ৩. র‍্যাঙ্ক ১/২/৩ মেডেল / ক্রাউন ব্যাজ আইকন (টপ-লেফট বা বটম-রাইট)
            if (rankBadgeIconUrl != null && rankBadgeIconUrl!.isNotEmpty)
              Positioned(
                top: 0,
                left: 0,
                child: CachedNetworkImage(
                  imageUrl: rankBadgeIconUrl!,
                  width: 28,
                  height: 28,
                  fit: BoxFit.contain,
                ),
              ),
          ],
        ),
      ),
    );
  }
}
```

### ব্যবহার (Usage in Me Profile Screen):
```dart
UserAvatarWithFrame(
  avatarUrl: user.avatarUrl,
  frameUrl: user.avatarFrameUrl ?? user.baseFrameUrl, // র‍্যাঙ্ক ফ্রেম অথবা লেভেল ফ্রেম
  rankBadgeIconUrl: user.rankBadgeIconUrl,
  avatarSize: 84.0,
  frameSize: 116.0,
)
```

---

## ৪. হোম পেজ হেডার ট্রফি আইকন (Hot vs Live Tab Logic)

### রিকোয়ারমেন্ট:
- **`Hot` ট্যাবে:** ট্রফি আইকন হাইড থাকবে (`showTrophy = false`).
- **`Live` ট্যাবে:** ট্রফি আইকন শো করবে (`showTrophy = true`), যাতে ব্যবহারকারী ক্লিক করে সরাসরি লিডারবোর্ড স্ক্রিনে যেতে পারেন।

### Flutter কোড স্নিপেট (`HomeScreenHeader`):
```dart
int _currentSelectedTabIndex = 0; // 0 = Hot, 1 = Live, 2 = Party, 3 = Match

AppBar(
  backgroundColor: const Color(0xFF0F172A),
  elevation: 0,
  title: Row(
    children: [
      _buildTabItem("Hot", 0),
      const SizedBox(width: 16),
      _buildTabItem("Live", 1),
      const SizedBox(width: 16),
      _buildTabItem("Party", 2),
      const SizedBox(width: 16),
      _buildTabItem("Match", 3),
    ],
  ),
  actions: [
    // সার্চ বাটন
    IconButton(
      icon: const Icon(Icons.search, color: Colors.white),
      onPressed: () => Navigator.pushNamed(context, '/search'),
    ),
    // ভাষা সিলেক্টর
    IconButton(
      icon: const Icon(Icons.language, color: Color(0xFFA855F7)),
      onPressed: () => _showLanguageModal(),
    ),
    // 🏆 শুধুমাত্র Live ট্যাবে কাপ/ট্রফি আইকন দৃশ্যমান থাকবে!
    if (_currentSelectedTabIndex == 1) // 1 == Live Tab
      IconButton(
        icon: const Icon(Icons.emoji_events, color: Color(0xFFF59E0B)), // Trophy
        tooltip: 'Leaderboard Rankings',
        onPressed: () {
          Navigator.push(
            context,
            MaterialPageRoute(builder: (context) => const RankLeaderboardScreen()),
          );
        },
      )
    else
      const SizedBox(width: 8), // Hot বা অন্যান্য ট্যাবে স্পেসার
  ],
)
```

---

## ৫. লাইভ স্ট্রিম এক্সিট ও ক্লোজ হ্যান্ডলিং (Live Stream Auto-Termination)

হোস্ট লাইভ স্ক্রিন থেকে ব্যাক করলে বা ক্লোজ করলে অবিলম্বে সার্ভারে `POST /api/stream/end` কল করতে হবে:

```dart
Future<void> terminateLiveStream(String roomId) async {
  try {
    final response = await http.post(
      Uri.parse('https://chinchins.live/api/stream/end'),
      headers: {
        'Authorization': 'Bearer $userAuthToken',
        'Accept': 'application/json',
      },
      body: {
        'room_id': roomId,
      },
    );
    print('Live Stream terminated: ${response.body}');
  } catch (e) {
    print('Error terminating live stream: $e');
  }
}
```

Flutter-এ `WillPopScope` বা `PopScope` দিয়ে হ্যান্ডলিং:
```dart
@override
Widget build(BuildContext context) {
  return PopScope(
    canPop: false,
    onPopInvokedWithResult: (didPop, result) async {
      if (didPop) return;
      
      // কনফার্মেশন ও স্বয়ংক্রিয়ভাবে লাইভ বন্ধ
      final shouldLeave = await _showExitConfirmDialog();
      if (shouldLeave == true) {
        await terminateLiveStream(widget.roomId);
        if (mounted) Navigator.of(context).pop();
      }
    },
    child: Scaffold( ... ),
  );
}
```

---

## ৬. সংক্ষেপে চেকলিস্ট (Developer Checklist)

| ধাপ | ফিচার | স্ট্যাটাস |
| :--- | :--- | :--- |
| **১** | `Me` প্রোফাইল স্ক্রিনে `user.avatar_frame_url` দিয়ে অ্যাভাটারের ওপর ফ্রেম রেন্ডার করা | সম্পন্ন |
| **২** | হোম স্ক্রিনে `Hot` ট্যাবে ট্রফি আইকন হাইড এবং `Live` ট্যাবে ভিজিবল রাখা | সম্পন্ন |
| **৩** | লিডারবোর্ড স্ক্রিনে টপ ১, ২, ৩ ইউজারের অ্যাভাটার ফ্রেম ডিসপ্লে করা | সম্পন্ন |
| **৪** | হোস্ট লাইভ ত্যাগ করলে সাথে সাথে ব্যাকএন্ডে `ended` স্ট্যাটাস সিঙ্ক হওয়া | সম্পন্ন |
| **৫** | Hive ক্যাশিং ব্যবহার করে ০ সেকেন্ড ল্যাটেন্সিতে প্রোফাইল লোড করা | সম্পন্ন |
