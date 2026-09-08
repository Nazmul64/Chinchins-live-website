# 📱 Chinchins Live — Flutter Integration & Master API Guide

**Document:** `FLUTTER_STREAMER_FEED_AND_API_INTEGRATION_GUIDE.md`  
**Target Audience:** Flutter Mobile App Developers & Backend Engineers  
**Production API Base:** `https://chinchins.live/api`  
**Authorization Header:** `Authorization: Bearer <Sanctum_Token>` or `X-User-Id: <User_ID>`  

---

## 📑 Table of Contents
1. [Global Countries API & Registration for All Nations](#1-global-countries-api--registration-for-all-nations)
2. [Profile UI & Interactive Actions (Matching Live Design)](#2-profile-ui--interactive-actions-matching-live-design)
   - [A. Love / Like Heart Button (`POST /api/profile/{id}/like`)](#a-love--like-heart-button)
   - [B. Circular "Hi" Greeting Button (`POST /api/chat/send-hi`)](#b-circular-hi-greeting-button)
   - [C. Interest Tags & Speaking Languages](#c-interest-tags--speaking-languages)
   - [D. Charm Level & Top Fans Leaderboard](#d-charm-level--top-fans-leaderboard)
   - [E. Gifts Received 8-Slot Grid](#e-gifts-received-8-slot-grid)
   - [F. Video Call Button with Rate Indicator](#f-video-call-button-with-rate-indicator)
3. [Call End Confirmation Dialog ("Sure to end call?")](#3-call-end-confirmation-dialog-sure-to-end-call)
4. [Zero-Delay Pre-Call Balance Check & Instant Recharge Modal](#4-zero-delay-pre-call-balance-check--instant-recharge-modal)
5. [Coin Packages & Dynamic Multipliers API](#5-coin-packages--dynamic-multipliers-api)
6. [Fixing SVG Diamond Icons & Gifts in Flutter](#6-fixing-svg-diamond-icons--gifts-in-flutter)
7. [Home Feed & Country Filtering (`GET /api/home`)](#7-home-feed--country-filtering-get-apihome)
8. [Ready-to-Copy Flutter Dart Implementation Code](#8-ready-to-copy-flutter-dart-implementation-code)
   - [1. `CountryPickerBottomSheet.dart`](#flutter-snippet-countrypickerbottomsheet)
   - [2. `ProfileScreen.dart`](#flutter-snippet-profilescreen)
   - [3. `CallEndConfirmationDialog.dart`](#flutter-snippet-callendconfirmationdialog)
9. [Developer Checklist](#9-developer-checklist)

---

## 🌍 1. Global Countries API & Registration for All Nations

Users can register from **any country in the world** (not just Bangladesh, Pakistan, or India, but also Nepal, Philippines, Bhutan, Malaysia, Saudi Arabia, UAE, Afghanistan, USA, UK, etc.).

### 1.1 Fetch Worldwide Countries List
- **Endpoint:** `GET https://chinchins.live/api/countries` (Alias: `GET /api/app/countries`)
- **Query Params (Optional):** `?q=nepal` or `?search=philippines`
- **Response Structure (`200 OK`):**
```json
{
  "status": true,
  "message": "90 countries loaded successfully.",
  "data": {
    "default": "Pakistan",
    "countries": [
      {
        "name": "Bangladesh",
        "code": "BD",
        "iso3": "BGD",
        "flag": "🇧🇩",
        "dial_code": "+880"
      },
      {
        "name": "Pakistan",
        "code": "PK",
        "iso3": "PAK",
        "flag": "🇵🇰",
        "dial_code": "+92"
      },
      {
        "name": "India",
        "code": "IN",
        "iso3": "IND",
        "flag": "🇮🇳",
        "dial_code": "+91"
      },
      {
        "name": "Nepal",
        "code": "NP",
        "iso3": "NPL",
        "flag": "🇳🇵",
        "dial_code": "+977"
      },
      {
        "name": "Philippines",
        "code": "PH",
        "iso3": "PHL",
        "flag": "🇵🇭",
        "dial_code": "+63"
      },
      {
        "name": "Bhutan",
        "code": "BT",
        "iso3": "BTN",
        "flag": "🇧🇹",
        "dial_code": "+975"
      },
      {
        "name": "Malaysia",
        "code": "MY",
        "iso3": "MYS",
        "flag": "🇲🇾",
        "dial_code": "+60"
      },
      {
        "name": "Saudi Arabia",
        "code": "SA",
        "iso3": "SAU",
        "flag": "🇸🇦",
        "dial_code": "+966"
      },
      {
        "name": "United Arab Emirates",
        "code": "AE",
        "iso3": "ARE",
        "flag": "🇦🇪",
        "dial_code": "+971"
      },
      {
        "name": "Afghanistan",
        "code": "AF",
        "iso3": "AFG",
        "flag": "🇦🇫",
        "dial_code": "+93"
      }
    ]
  }
}
```

### 1.2 User Registration with Country, Interest Tags & Speaking Languages
- **Endpoint:** `POST https://chinchins.live/api/auth/register`
- **Request Body (`multipart/form-data` or `application/json`):**
```json
{
  "first_name": "Lilibeth",
  "last_name": "Garcia",
  "phone": "+639123456789",
  "password": "password123",
  "password_confirmation": "password123",
  "country": "Philippines",
  "city": "Manila",
  "gender": "female",
  "age": 24,
  "introduction": "Soy una chica sexy 🔥, esperando tu llamada bebé 💋",
  "speaking_languages": ["English", "Spanish"],
  "interest_tags": ["late night fun", "fun show baby", "sexy body"],
  "video_call_rate": 1800
}
```
*Note: The backend automatically resolves the emoji flag (e.g. `🇵🇭`), 2-letter ISO code (`PH`), and dial code.*

---

## 🎨 2. Profile UI & Interactive Actions (Matching Live Design)

When viewing any user's profile (`GET /api/profile/{id}`), the screen presents the complete profile data matching the live design.

### A. Love / Like Heart Button
- **Placement:** Top-right gradient card, pink glowing heart icon button.
- **Action:** Tapping triggers `POST https://chinchins.live/api/profile/{id}/like` (or `POST /api/user/{id}/like`).
- **Behavior:**
  - Increments the host's total likes count.
  - Automatically registers the sender as a **Top Fan** (likers are included in Top Fans!).
  - Triggers floating heart animation on Flutter.
- **Request Body:**
```json
{
  "count": 1,
  "context": "profile"
}
```
- **Response Structure (`200 OK`):**
```json
{
  "status": true,
  "message": "Love heart sent!",
  "data": {
    "receiver_id": 2,
    "total_likes": 248,
    "formatted_likes": "248",
    "sender_likes": 12,
    "top_fan": {
      "id": 1,
      "name": "Raza me",
      "avatar_url": "https://chinchins.live/uploads/avatars/user_1.jpg",
      "formatted": "12 Likes"
    }
  }
}
```

### B. Circular "Hi" Greeting Button
- **Placement:** Bottom-left circular purple bubble with "Hi" white text.
- **Action:** Tapping triggers `POST https://chinchins.live/api/chat/send-hi` or `POST /api/profile/{id}/hi`.
- **Behavior:**
  - Instantly creates a "Hi 👋" chat message in the conversation thread.
  - Returns `open_chat_route: "/chat/{id}"`.
  - Flutter immediately navigates to the Messenger chat room without delay.
- **Request Body:**
```json
{
  "receiver_id": 2,
  "message": "Hi 👋"
}
```
- **Response (`200 OK`):**
```json
{
  "status": true,
  "message": "Hi greeting sent successfully!",
  "data": {
    "message": {
      "id": 105,
      "sender_id": 1,
      "receiver_id": 2,
      "message": "Hi 👋",
      "type": "text"
    },
    "chat_partner": {
      "id": 2,
      "name": "Ayeena04",
      "avatar_url": "https://chinchins.live/uploads/avatars/ayeena.jpg",
      "country_flag": "🇵🇰",
      "country": "Pakistan",
      "display_age": 27,
      "level": "Lv4",
      "video_call_rate": 1800
    },
    "conversation_id": 2,
    "open_chat_route": "/chat/2"
  }
}
```

### C. Interest Tags & Speaking Languages
- Displayed under sections:
  - **`Interest tag`**: Rounded pill chips (e.g. `late night fun`, `fun show baby`, `sexy body`).
  - **`Speaking language`**: Rounded pill chips (e.g. `English`, `Spanish`).
- Users can update their tags and languages anytime via `POST /api/profile/update` with `interest_tags: [...]` and `speaking_languages: [...]`.

### D. Charm Level & Top Fans Leaderboard
- **Charm Level:** Displays badge, e.g. `Charm Level Lv7` with gold crown icon and gradient background.
- **Top Fans:** Displays top fan avatar + name (e.g. `Raza me`).
- Tapping on **Top Fans** opens the leaderboard: `GET /api/profile/{id}/top-fans`.

### E. Gifts Received 8-Slot Grid
- Profile shows a 2-row x 4-column grid (8 slots total) of the most recent gifts received:
  - Gift thumbnail image (`image_url`)
  - Coin value badge (e.g. `💎 100K`, `💎 186.62K`)
  - Quantity badge at the bottom (e.g. `x2`, `x4`, `x5`, `x1`)

### F. Video Call Button with Rate Indicator
- **Placement:** Bottom bar, large magenta gradient pill button.
- **Display:** Video camera icon + `Video Call` + `💎 1800/min`.
- **Pre-Call Verification:** Checks balance locally and via `POST /api/call/check-permission`.

---

## 🛑 3. Call End Confirmation Dialog ("Sure to end call?")

> [!IMPORTANT]
> When a user taps the hangup / end call button or presses the physical Android back button during a video call, **DO NOT terminate immediately**.
> Show the custom confirmation dialog with **Cancel** and **Confirm** buttons.

### UI Specifications (Matching Screenshot 3):
1. **Backdrop:** Dimmed video call screen (dark overlay).
2. **Dialog Card:** Rounded dark glass card (`#1F222A` or blurred background).
3. **Title:** `"Sure to end call?"` (white, centered, 18sp bold).
4. **Action Buttons:**
   - **Cancel Button:** Dark semi-transparent pill button (`#2B2D3A`). Dismisses dialog and keeps call active.
   - **Confirm Button:** Bright cyan / purple gradient pill button. Terminates call, leaves Agora channel, calls `POST /api/call/end`, and navigates back.

---

## ⚡ 4. Zero-Delay Pre-Call Balance Check & Instant Recharge Modal

> [!CAUTION]
> **STRICT UX RULE:** When a user taps Call and has insufficient balance, the app **MUST NOT** show **"Call connecting..."**, **"Calling..."**, or any loading spinner!
> The Recharge Modal (`RechargeGemsSheet`) **MUST open INSTANTLY (< 0.1s)** directly from the current screen!

```
[User Taps Call Button]
         │
         ▼
[Step 1: Local Fast Check (0.00s Instant)]
 └─ Check: (currentUser.coins < host.ratePerMinute)
         │
         ├─── YES (Coins < Rate) ─────────────► [Instantly Open RechargeGemsSheet]
         │                                      ⛔ NO "Call connecting..."
         │                                      ⛔ NO Agora screen
         │
         └─── NO (Enough coins locally) ──────► [Step 2: Backend Check]
                                                └─ POST /api/call/check-permission
                                                     ├── can_call: false ──► [Open RechargeGemsSheet]
                                                     └── can_call: true  ──► [Open Calling Screen]
```

---

## 💎 5. Coin Packages & Dynamic Multipliers API

### Endpoint
- **Route:** `GET https://chinchins.live/api/recharge/modal-data`
- **Aliases:** `GET /api/coin-packages`

### Dynamic Gift Multipliers
- When sending gifts, the dynamic multipliers are: `[1, 10, 66, 99, 520, 1314]`.

---

## 🛠️ 6. Fixing SVG Diamond Icons & Gifts in Flutter

All SVGs hosted at `https://chinchins.live/uploads/` have been processed to remove unsupported `<filter>` and `<feDropShadow>` tags, ensuring 100% crash-free rendering with `flutter_svg`.

---

## 📡 7. Home Feed & Country Filtering (`GET /api/home`)

- **Route:** `GET https://chinchins.live/api/home` (Alias: `GET /api/live/streamers`)
- **Query Params:**
  - `country`: Filter by country name or code (`BD`, `Pakistan`, `India`, `Nepal`, `Philippines`, `Malaysia`, `Saudi Arabia`, `UAE`, `All`, `Global`). Default is **Global** (returns all streamers worldwide).
- Each streamer object contains:
```json
{
  "id": 2,
  "display_name": "Hamna",
  "avatar_url": "https://chinchins.live/uploads/profiles/hamna.jpg",
  "country": "Pakistan",
  "country_code": "PK",
  "country_flag": "🇵🇰",
  "display_age": 27,
  "level": "Lv4",
  "charm_level": "Lv4",
  "video_call_rate": 1800,
  "interest_tags": ["late night fun", "fun show baby", "sexy body"],
  "speaking_languages": ["English", "Spanish"]
}
```

---

## 💻 8. Ready-to-Copy Flutter Dart Implementation Code

### Flutter Snippet: CountryPickerBottomSheet

```dart
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

class Country {
  final String name;
  final String code;
  final String flag;
  final String dialCode;

  Country({required this.name, required this.code, required this.flag, required this.dialCode});

  factory Country.fromJson(Map<String, dynamic> json) {
    return Country(
      name: json['name'] ?? '',
      code: json['code'] ?? '',
      flag: json['flag'] ?? '🌐',
      dialCode: json['dial_code'] ?? '',
    );
  }
}

class CountryPickerBottomSheet extends StatefulWidget {
  final Function(Country) onSelect;
  const CountryPickerBottomSheet({Key? key, required this.onSelect}) : super(key: key);

  @override
  _CountryPickerBottomSheetState createState() => _CountryPickerBottomSheetState();
}

class _CountryPickerBottomSheetState extends State<CountryPickerBottomSheet> {
  List<Country> countries = [];
  List<Country> filtered = [];
  bool isLoading = true;

  @override
  void initState() {
    super.initState();
    fetchCountries();
  }

  Future<void> fetchCountries() async {
    try {
      final res = await http.get(Uri.parse('https://chinchins.live/api/countries'));
      if (res.statusCode == 200) {
        final data = json.decode(res.body)['data']['countries'] as List;
        setState(() {
          countries = data.map((c) => Country.fromJson(c)).toList();
          filtered = countries;
          isLoading = false;
        });
      }
    } catch (_) {
      setState(() => isLoading = false);
    }
  }

  void filter(String query) {
    setState(() {
      filtered = countries.where((c) =>
        c.name.toLowerCase().contains(query.toLowerCase()) ||
        c.dialCode.contains(query)
      ).toList();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 500,
      decoration: const BoxDecoration(
        color: Color(0xFF181A20),
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      child: Column(
        children: [
          Container(
            margin: const EdgeInsets.symmetric(vertical: 12),
            height: 4,
            width: 40,
            decoration: BoxDecoration(color: Colors.white24, borderRadius: BorderRadius.circular(2)),
          ),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: TextField(
              style: const TextStyle(color: Colors.white),
              onChanged: filter,
              decoration: InputDecoration(
                hintText: 'Search country...',
                hintStyle: const TextStyle(color: Colors.white54),
                prefixIcon: const Icon(Icons.search, color: Colors.white54),
                filled: true,
                fillColor: const Color(0xFF262A34),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
              ),
            ),
          ),
          const SizedBox(height: 8),
          Expanded(
            child: isLoading
              ? const Center(child: CircularProgressIndicator(color: Color(0xFFE91E63)))
              : ListView.builder(
                  itemCount: filtered.length,
                  itemBuilder: (ctx, idx) {
                    final c = filtered[idx];
                    return ListTile(
                      leading: Text(c.flag, style: const TextStyle(fontSize: 24)),
                      title: Text(c.name, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600)),
                      trailing: Text(c.dialCode, style: const TextStyle(color: Colors.white70)),
                      onTap: () {
                        widget.onSelect(c);
                        Navigator.pop(context);
                      },
                    );
                  },
                ),
          ),
        ],
      ),
    );
  }
}
```

---

### Flutter Snippet: ProfileScreen

```dart
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

class ProfileScreen extends StatefulWidget {
  final int userId;
  const ProfileScreen({Key? key, required this.userId}) : super(key: key);

  @override
  _ProfileScreenState createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  Map<String, dynamic>? profileData;
  bool isLoading = true;

  @override
  void initState() {
    super.initState();
    loadProfile();
  }

  Future<void> loadProfile() async {
    final res = await http.get(Uri.parse('https://chinchins.live/api/profile/${widget.userId}'));
    if (res.statusCode == 200) {
      setState(() {
        profileData = json.decode(res.body)['data'];
        isLoading = false;
      });
    }
  }

  // 1. Send Love Heart Like
  Future<void> sendLike() async {
    final res = await http.post(
      Uri.parse('https://chinchins.live/api/profile/${widget.userId}/like'),
      headers: {'Accept': 'application/json'},
      body: {'count': '1'},
    );
    if (res.statusCode == 200) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('❤️ Love sent! Added to Top Fans!'), backgroundColor: Colors.pink),
      );
      loadProfile(); // refresh top fan & like count
    }
  }

  // 2. Send Quick "Hi" Greeting & Open Messenger
  Future<void> sendHi() async {
    final res = await http.post(
      Uri.parse('https://chinchins.live/api/profile/${widget.userId}/hi'),
      headers: {'Accept': 'application/json'},
    );
    if (res.statusCode == 200) {
      final data = json.decode(res.body)['data'];
      // Navigate to chat room immediately
      Navigator.pushNamed(context, '/chat', arguments: {'userId': widget.userId, 'partner': data['chat_partner']});
    }
  }

  @override
  Widget build(BuildContext context) {
    if (isLoading || profileData == null) {
      return const Scaffold(backgroundColor: Color(0xFF0F1015), body: Center(child: CircularProgressIndicator()));
    }

    final user = profileData!['user'] ?? {};
    final topFan = profileData!['top_fan'] ?? {};
    final charm = profileData!['charm_level'] ?? {};
    final tags = (profileData!['interest_tags'] as List?)?.cast<String>() ?? [];
    final langs = (profileData!['speaking_languages'] as List?)?.cast<String>() ?? [];
    final gifts = (profileData!['gifts_received'] as List?) ?? [];

    return Scaffold(
      backgroundColor: const Color(0xFF0F1015),
      body: Stack(
        children: [
          CustomScrollView(
            slivers: [
              // Header Image with Back Button & Love Heart Button
              SliverAppBar(
                expandedHeight: 380,
                pinned: true,
                backgroundColor: Colors.transparent,
                leading: IconButton(
                  icon: const Icon(Icons.arrow_back_ios_new, color: Colors.white),
                  onPressed: () => Navigator.pop(context),
                ),
                flexibleSpace: FlexibleSpaceBar(
                  background: Stack(
                    fit: StackFit.expand,
                    children: [
                      Image.network(user['avatar_url'] ?? '', fit: BoxFit.cover),
                      Container(
                        decoration: const BoxDecoration(
                          gradient: LinearGradient(
                            begin: Alignment.topCenter,
                            end: Alignment.bottomCenter,
                            colors: [Colors.black26, Colors.transparent, Color(0xFF0F1015)],
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),

              // Profile Content Card
              SliverToBoxAdapter(
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // User Info Row + Heart Button
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  Text(user['name'] ?? '', style: const TextStyle(color: Colors.white, fontSize: 22, fontWeight: FontWeight.bold)),
                                  const SizedBox(width: 6),
                                  const Icon(Icons.verified, color: Colors.blueAccent, size: 20),
                                ],
                              ),
                              const SizedBox(height: 4),
                              Text("ID: ${user['account_id']}", style: const TextStyle(color: Colors.white54, fontSize: 13)),
                            ],
                          ),
                          // Glowing Heart Like Button
                          GestureDetector(
                            onTap: sendLike,
                            child: Container(
                              width: 52,
                              height: 52,
                              decoration: BoxDecoration(
                                shape: BoxShape.circle,
                                gradient: const LinearGradient(colors: [Color(0xFFFF4081), Color(0xFFE91E63)]),
                                boxShadow: [
                                  BoxShadow(color: const Color(0xFFFF4081).withOpacity(0.5), blurRadius: 16, spreadRadius: 2),
                                ],
                              ),
                              child: const Icon(Icons.favorite, color: Colors.white, size: 28),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),

                      // Status Pills (Active, Lv4, Pakistan, Age)
                      Wrap(
                        spacing: 8,
                        children: [
                          _buildPill("Active", Colors.green, Icons.circle),
                          _buildPill(user['level'] ?? 'Lv4', Colors.purpleAccent, null),
                          _buildPill("${profileData!['country_flag']} ${user['country']}", Colors.teal, null),
                          _buildPill("♀ ${user['display_age']}", Colors.pinkAccent, null),
                        ],
                      ),
                      const SizedBox(height: 20),

                      // Introduction
                      const Text("Introduction", style: TextStyle(color: Colors.white70, fontSize: 14, fontWeight: FontWeight.bold)),
                      const SizedBox(height: 6),
                      Text(user['introduction'] ?? 'No bio yet', style: const TextStyle(color: Colors.white, fontSize: 14)),
                      const SizedBox(height: 20),

                      // Interest Tag Section
                      const Text("Interest tag", style: TextStyle(color: Colors.white70, fontSize: 14, fontWeight: FontWeight.bold)),
                      const SizedBox(height: 8),
                      Wrap(
                        spacing: 8,
                        runSpacing: 8,
                        children: tags.map((t) => Container(
                          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                          decoration: BoxDecoration(color: const Color(0xFF1F222A), borderRadius: BorderRadius.circular(16)),
                          child: Text(t, style: const TextStyle(color: Colors.white, fontSize: 13)),
                        )).toList(),
                      ),
                      const SizedBox(height: 20),

                      // Speaking Language Section
                      const Text("Speaking language", style: TextStyle(color: Colors.white70, fontSize: 14, fontWeight: FontWeight.bold)),
                      const SizedBox(height: 8),
                      Wrap(
                        spacing: 8,
                        children: langs.map((l) => Container(
                          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                          decoration: BoxDecoration(color: const Color(0xFF1F222A), borderRadius: BorderRadius.circular(16)),
                          child: Text(l, style: const TextStyle(color: Colors.white, fontSize: 13)),
                        )).toList(),
                      ),
                      const SizedBox(height: 20),

                      // Honor / Charm Level & Top Fans Card
                      Row(
                        children: [
                          Expanded(
                            child: Container(
                              padding: const EdgeInsets.all(12),
                              decoration: BoxDecoration(color: const Color(0xFF2B1D42), borderRadius: BorderRadius.circular(16)),
                              child: Row(
                                children: [
                                  const Icon(Icons.workspace_premium, color: Colors.amber, size: 28),
                                  const SizedBox(width: 8),
                                  Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      const Text("Charm Level", style: TextStyle(color: Colors.white70, fontSize: 11)),
                                      Text(charm['level_tag'] ?? 'Lv1', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14)),
                                    ],
                                  )
                                ],
                              ),
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Container(
                              padding: const EdgeInsets.all(12),
                              decoration: BoxDecoration(color: const Color(0xFF1A2640), borderRadius: BorderRadius.circular(16)),
                              child: Row(
                                children: [
                                  CircleAvatar(radius: 16, backgroundImage: NetworkImage(topFan['avatar_url'] ?? '')),
                                  const SizedBox(width: 8),
                                  Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      const Text("Top Fans", style: TextStyle(color: Colors.white70, fontSize: 11)),
                                      Text(topFan['name'] ?? 'No fans yet', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13)),
                                    ],
                                  )
                                ],
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 20),

                      // Gifts Received 8-Slot Grid
                      const Text("Gifts Received >", style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold)),
                      const SizedBox(height: 12),
                      GridView.builder(
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                          crossAxisCount: 4,
                          childAspectRatio: 0.75,
                          crossAxisSpacing: 8,
                          mainAxisSpacing: 8,
                        ),
                        itemCount: gifts.length > 8 ? 8 : gifts.length,
                        itemBuilder: (ctx, idx) {
                          final g = gifts[idx];
                          return Container(
                            decoration: BoxDecoration(color: const Color(0xFF1A1C24), borderRadius: BorderRadius.circular(12)),
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Image.network(g['image_url'], height: 44, width: 44),
                                const SizedBox(height: 4),
                                Text(g['formatted_coins'] ?? '', style: const TextStyle(color: Colors.cyanAccent, fontSize: 10, fontWeight: FontWeight.bold)),
                                Text(g['count_label'] ?? 'x1', style: const TextStyle(color: Colors.white54, fontSize: 11)),
                              ],
                            ),
                          );
                        },
                      ),
                      const SizedBox(height: 100), // padding for bottom bar
                    ],
                  ),
                ),
              ),
            ],
          ),

          // Bottom Interactive Bar (Hi Button + Video Call Button)
          Positioned(
            bottom: 0,
            left: 0,
            right: 0,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              decoration: const BoxDecoration(
                color: Color(0xFF0F1015),
                border: Border(top: BorderSide(color: Colors.white10)),
              ),
              child: Row(
                children: [
                  // Circular "Hi" Greeting Button
                  GestureDetector(
                    onTap: sendHi,
                    child: Container(
                      width: 50,
                      height: 50,
                      decoration: const BoxDecoration(
                        shape: BoxShape.circle,
                        gradient: LinearGradient(colors: [Color(0xFF8E2DE2), Color(0xFF4A00E0)]),
                      ),
                      child: const Center(
                        child: Text("Hi", style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 16)),
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),

                  // Video Call Button with Rate
                  Expanded(
                    child: GestureDetector(
                      onTap: () {
                        // Trigger zero-delay balance check & Agora call
                      },
                      child: Container(
                        height: 50,
                        decoration: BoxDecoration(
                          borderRadius: BorderRadius.circular(25),
                          gradient: const LinearGradient(colors: [Color(0xFF8E2DE2), Color(0xFFE91E63)]),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const Icon(Icons.videocam, color: Colors.white),
                            const SizedBox(width: 8),
                            const Text("Video Call", style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 15)),
                            const SizedBox(width: 8),
                            Text("💎 ${profileData!['video_call_rate_text'] ?? '1800/min'}", style: const TextStyle(color: Colors.amberAccent, fontSize: 12)),
                          ],
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPill(String text, Color color, IconData? icon) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(color: color.withOpacity(0.2), borderRadius: BorderRadius.circular(12)),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (icon != null) ...[Icon(icon, color: color, size: 8), const SizedBox(width: 4)],
          Text(text, style: TextStyle(color: color, fontSize: 12, fontWeight: FontWeight.bold)),
        ],
      ),
    );
  }
}
```

---

### Flutter Snippet: CallEndConfirmationDialog

```dart
import 'package:flutter/material.dart';

Future<bool> showCallEndConfirmationDialog(BuildContext context) async {
  final result = await showDialog<bool>(
    context: context,
    barrierDismissible: false,
    builder: (BuildContext ctx) {
      return Dialog(
        backgroundColor: Colors.transparent,
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 28),
          decoration: BoxDecoration(
            color: const Color(0xFF1F222A).withOpacity(0.95),
            borderRadius: BorderRadius.circular(24),
            border: Border.all(color: Colors.white12),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Text(
                "Sure to end call?",
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 28),
              Row(
                children: [
                  // Cancel Button
                  Expanded(
                    child: GestureDetector(
                      onTap: () => Navigator.of(ctx).pop(false),
                      child: Container(
                        height: 46,
                        decoration: BoxDecoration(
                          color: const Color(0xFF2E323E),
                          borderRadius: BorderRadius.circular(23),
                        ),
                        child: const Center(
                          child: Text(
                            "Cancel",
                            style: TextStyle(
                              color: Colors.white70,
                              fontWeight: FontWeight.w600,
                              fontSize: 15,
                            ),
                          ),
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 14),

                  // Confirm Button
                  Expanded(
                    child: GestureDetector(
                      onTap: () => Navigator.of(ctx).pop(true),
                      child: Container(
                        height: 46,
                        decoration: BoxDecoration(
                          gradient: const LinearGradient(
                            colors: [Color(0xFF00C6FF), Color(0xFF0072FF)],
                          ),
                          borderRadius: BorderRadius.circular(23),
                        ),
                        child: const Center(
                          child: Text(
                            "Confirm",
                            style: TextStyle(
                              color: Colors.white,
                              fontWeight: FontWeight.bold,
                              fontSize: 15,
                            ),
                          ),
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      );
    },
  );

  return result ?? false;
}
```

---

## ✅ 9. Developer Checklist

- [x] Worldwide countries endpoint active: `GET /api/countries` (90+ countries with name, code, flag, dial code).
- [x] Registration accepts any country worldwide: `POST /api/auth/register`.
- [x] Profile endpoint returns `interest_tags`, `speaking_languages`, `charm_level`, `top_fan`, `gifts_received`, `video_call_rate_text`.
- [x] Sending love heart like (`POST /api/profile/{id}/like`) automatically adds sender to the Top Fans leaderboard.
- [x] Instant "Hi" greeting endpoint (`POST /api/chat/send-hi` or `POST /api/profile/{id}/hi`) sends greeting & opens chat room.
- [x] Custom call termination confirmation dialog ("Sure to end call?" with Cancel & Confirm) documented.
- [x] All SVGs in `uploads/gifts/` and `uploads/coin_packages/` validated without crash-prone `<filter>` tags.
