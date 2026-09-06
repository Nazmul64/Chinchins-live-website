# 💬 Chat Header Partner Info, User Block & Report Moderation — RESTful API & Flutter Guide

This guide documents the **Chat Header Partner Profile Badge**, **3-Dots Action Menu (Block & Report)**, **In-Chat Moderation Workflow**, and **Admin Panel User Balance Management (+/-)** on Chinchins Live.

---

## 📱 1. Mobile Chat Header & 3-Dots Menu Specification

As shown in the app screenshot, when entering a 1-on-1 chat screen:

```
┌─────────────────────────────────────────────────────────────┐
│ [←] 👤 Badol ⭐ (🟢 Online)              [📞 Call]   [⋮]    │
├─────────────────────────────────────────────────────────────┤
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ 👤 Badol ⭐                                             │ │
│ │ 🇧🇩 Bangladesh • 22 yrs • ♀ Female • Lv. 5              │ │
│ │ "Hey handsome! Thanks for visiting my profile ❤️"       │ │
│ └─────────────────────────────────────────────────────────┘ │
│                                                             │
│         [ 📹 video call ended (00:23) ]                     │
│                                                             │
│ 👤 Hey handsome! Thanks for visiting my profile ❤️           │
│                                                             │
│ ... (Chat messages history) ...                             │
└─────────────────────────────────────────────────────────────┘
```

### 3-Dots Menu (`...`) Options:
1. **Block User (ইউজারকে ব্লক করা):** Prevents the user from calling or sending messages.
2. **Report User (ইউজারকে রিপোর্ট করা):** Opens the Report Dialog with standard complaint categories.
3. **Cancel (বাতিল):** Dismisses the action sheet.

---

## 🚀 2. RESTful API Endpoints

### 1️⃣ Get Chat History & Header Partner Data (`GET /api/chat/{userId}` or `GET /api/messages/{userId}`)
Returns the message history along with complete `chat_partner` profile metadata for the header card.

- **URL:** `GET /api/chat/{userId}`
- **Headers:** `Authorization: Bearer <token>`

#### Response Example:
```json
{
  "status": true,
  "message": "Messages retrieved successfully.",
  "data": {
    "chat_partner": {
      "id": 15,
      "account_id": "88410293",
      "name": "Badol",
      "avatar_url": "https://chinchins.live/uploads/avatars/badol.jpg",
      "is_online": true,
      "is_busy": false,
      "video_call_rate": 1800,
      "level": "Lv. 5",
      "level_number": 5,
      "level_badge_url": "https://chinchins.live/uploads/bases/badge_level_5.svg",
      "badge_color": "#f59e0b",
      "badge_icon": "star",
      "country": "Bangladesh",
      "country_flag": "🇧🇩",
      "age": 22,
      "gender": "female",
      "gender_icon": "♀",
      "bio": "Hey handsome! Thanks for visiting my profile ❤️",
      "greeting_message": "Hey handsome! Thanks for visiting my profile ❤️",
      "is_blocked_by_me": false,
      "is_blocked_by_them": false,
      "header_card": {
        "name": "Badol",
        "star_icon": "⭐",
        "level": "Lv. 5",
        "country_flag": "🇧🇩",
        "country_name": "Bangladesh",
        "age": 22,
        "gender_text": "Female",
        "gender_icon": "♀",
        "summary_text": "🇧🇩 Bangladesh • 22 yrs • ♀ Female • Lv. 5",
        "greeting_text": "Hey handsome! Thanks for visiting my profile ❤️"
      },
      "menu_options": [
        { "id": "block", "title": "Block User", "action": "block" },
        { "id": "report", "title": "Report User", "action": "report" },
        { "id": "cancel", "title": "Cancel", "action": "cancel" }
      ]
    },
    "free_messages_remaining": 3,
    "user_coins": 12500,
    "messages": [ ... ]
  }
}
```

---

### 2️⃣ Block a User (`POST /api/chat/block` or `POST /api/user/block`)
- **URL:** `POST /api/chat/block`
- **Headers:** `Authorization: Bearer <token>`
- **Request Body:**
```json
{
  "target_user_id": 15,
  "reason": "Harassment or unwanted calls"
}
```
#### Response Example:
```json
{
  "status": true,
  "message": "Successfully blocked Badol.",
  "is_blocked": true,
  "target_user": {
    "id": 15,
    "account_id": "88410293",
    "name": "Badol"
  }
}
```

---

### 3️⃣ Unblock a User (`POST /api/chat/unblock` or `POST /api/user/unblock`)
- **URL:** `POST /api/chat/unblock`
- **Headers:** `Authorization: Bearer <token>`
- **Request Body:**
```json
{
  "target_user_id": 15
}
```

---

### 4️⃣ Submit User Report (`POST /api/chat/report` or `POST /api/user/report`)
- **URL:** `POST /api/chat/report`
- **Headers:** `Authorization: Bearer <token>`
- **Request Body (JSON or Form-Data for screenshot upload):**
```json
{
  "reported_user_id": 15,
  "reason_type": "sexual_content",
  "description": "User is displaying inappropriate content in live call",
  "proof_image": "(Optional File upload)"
}
```

#### Predefined Reason Types (`GET /api/chat/report-reasons`):
| `reason_type` | Reason Title |
| :--- | :--- |
| `child_abuse` | Child sexual abuse and exploitation |
| `unreasonable_demands` | Unreasonable demands / Harassment |
| `sexual_content` | Adult / Sexual related content |
| `harassment` | Abuse, threat, or hate speech |
| `fraud_scam` | Fraud, financial scam, or fake profile |
| `other` | Other rule violations |

#### Response Example:
```json
{
  "status": true,
  "message": "Thank you. Your report has been submitted. Our moderation team will investigate promptly.",
  "report_id": 1,
  "reported_user": {
    "id": 15,
    "name": "Badol"
  }
}
```

---

## 🖥️ 3. Admin Panel Moderation

### 1. Users & Balance Management (`/admin/users`)
- **Real-time Wallet Adjustment:** Click **Adjust** on any user row to Add (`+`), Deduct (`-`), or Set exact Coins balance with an audit log reason.
- **1-Click Block / Unblock:** Instantly ban or unblock malicious users from accessing the app with the **Block** / **Unblock** toggle button.
- **Free Host Toggle:** Set any streamer as a **Free Host** (allows calling with 0 balance).

### 2. User & In-Chat Reports Moderation (`/admin/reports`)
- Dedicated sidebar menu link **User Reports**.
- View user complaints, reporter info, reason categories, description notes, and screenshot proofs.
- Quick action: **"Block User"** immediately suspends the reported account and marks the report as `resolved`.

---

## 📱 4. Flutter Integration Code Snippet

### Showing 3-Dots Popup Action Sheet
```dart
void showChatOptionsMenu(BuildContext context, {required int partnerId, required bool isBlocked}) {
  showModalBottomSheet(
    context: context,
    backgroundColor: const Color(0xFF1E1B2E),
    shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
    builder: (ctx) => SafeArea(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          ListTile(
            leading: Icon(isBlocked ? Icons.lock_open : Icons.block, color: Colors.redAccent),
            title: Text(isBlocked ? 'Unblock User' : 'Block User', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
            onTap: () async {
              Navigator.pop(ctx);
              final endpoint = isBlocked ? '/api/chat/unblock' : '/api/chat/block';
              await dio.post(endpoint, data: {'target_user_id': partnerId});
              ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(isBlocked ? 'User Unblocked' : 'User Blocked')));
            },
          ),
          ListTile(
            leading: const Icon(Icons.flag_outlined, color: Colors.amber),
            title: const Text('Report User', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
            onTap: () {
              Navigator.pop(ctx);
              showReportDialog(context, partnerId: partnerId);
            },
          ),
          ListTile(
            leading: const Icon(Icons.close, color: Colors.grey),
            title: const Text('Cancel', style: TextStyle(color: Colors.grey)),
            onTap: () => Navigator.pop(ctx),
          ),
        ],
      ),
    ),
  );
}
```
