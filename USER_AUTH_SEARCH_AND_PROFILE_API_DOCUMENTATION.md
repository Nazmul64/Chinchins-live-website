# 🆔 User Registration, Auto-Login, 8-Digit Unique Account ID & Search API Documentation
## 🇧🇩 ইউজার রেজিস্ট্রেশন, অটো-লগইন সেশন, ৮ সংখ্যার ইউনিক অ্যাকাউন্ট আইডি এবং সার্চ এপিআই গাইড

> **সারসংক্ষেপ (Overview):**
> ১. **অটো-লগইন (Auto-Login on Registration):** ইউজার একবার রেজিস্ট্রেশন (`POST /api/register`) করলেই ব্যাকএন্ড সাথে সাথে `token` প্রদান করে। ফলে ইউজারকে পুনরায় লগইন স্ক্রিনে পাঠাতে হবে না; সরাসরি হোম/প্রোফাইল স্ক্রিনে পাঠিয়ে সেশন সেভ রাখতে হবে।
> ২. **ইউনিক ৮ সংখ্যার অ্যাকাউন্ট আইডি (8-Digit Unique Account ID):** প্রতিটি ইউজারের জন্য ডাটাবেজের সাধারণ ক্রমিং নং (`id: 2`) দেখানোর বদলে ইউনিক ৮ সংখ্যার অ্যাকাউন্ট আইডি (`account_id: "84920183"` বা `display_id: "84920183"`) অ্যাপে প্রদর্শিত হবে।
> ৩. **আইডি ও নাম সার্চ (Search by ID & Name):** হোম পেজের সার্চ বারে ৮ সংখ্যার আইডি বা নাম লিখলে তাৎক্ষণিক ইউজারের প্রোফাইল খুঁজে পাওয়া যাবে।

---

## 📑 সূচিপত্র (Table of Contents)
1. [অটো-লগইন ও সেশন লাইফসাইকেল (Auto-Login Lifecycle)](#1-অটো-লগইন-ও-সেশন-লাইফসাইকেল)
2. [Flutter-এ অটো-লগইন ও সেশন সেভ রাখার কোড (Flutter Implementation)](#2-flutter-এ-অটো-লগইন-ও-সেশন-সেভ-রাখার-কোড)
3. [৮ সংখ্যার অ্যাকাউন্ট আইডি ও ফিল্ড ম্যাপিং](#3-৮-সংখ্যার-অ্যাকাউন্ট-আইডি-ও-ফিল্ড-ম্যাপিং)
4. [সম্পূর্ণ RESTful API রেফারেন্স](#4-সম্পূর্ণ-restful-api-রেফারেন্স)
   - ৪.১. ইউজার রেজিস্ট্রেশন ও ইনস্ট্যান্ট অটো-লগইন (`POST /api/register`)
   - ৪.২. ইউজার লগইন (`POST /api/login`)
   - ৪.৩. প্রোফাইল দেখা (`GET /api/profile/me` বা `GET /api/profile/{account_id}`)
   - ৪.৪. ৮ সংখ্যার আইডি ও নাম দিয়ে সার্চ (`GET /api/search?q=84920183`)

---

## 1. অটো-লগইন ও সেশন লাইফসাইকেল

```
[ New User Opens App ]
         │
         ▼
[ Fill Registration Form ] ──► (POST /api/register)
                                       │
                                       ▼ (Backend creates user + auto-generates Bearer Token)
[ Response contains { "token": "...", "user": { "account_id": "84920183" } } ]
         │
         ▼
[ Flutter Saves `token` to SharedPreferences / SecureStorage ]
         │
         ▼
[ Directly Navigate to HomeScreen (NO LOGIN SCREEN REQUIRED!) ]
         │
         ▼
(Next App Launch): [ SplashScreen checks if `token != null` ] ──► [ Open HomeScreen directly ]
```

---

## 2. Flutter-এ অটো-লগইন ও সেশন সেভ রাখার কোড

### ২.১. রেজিস্ট্রেশন সফল হলে টোকেন সেভ ও ডাইরেক্ট হোম পেজে যাওয়া:
```dart
// register_screen.dart
Future<void> registerUser() async {
  final response = await http.post(
    Uri.parse('https://yourdomain.com/api/register'),
    body: {
      'first_name': firstNameController.text,
      'last_name': lastNameController.text,
      'phone': phoneController.text,
      'password': passwordController.text,
      'password_confirmation': confirmPasswordController.text,
      'gender': 'female', // বা male
      'country': 'Bangladesh',
    },
  );

  final jsonResponse = jsonDecode(response.body);

  if (jsonResponse['status'] == true) {
    final token = jsonResponse['data']['token'];
    final user = jsonResponse['data']['user'];

    // ১. লোকাল স্টোরেজে টোকেন এবং ইউজার ডাটা সেভ করুন
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('auth_token', token);
    await prefs.setString('user_data', jsonEncode(user));

    // ২. পুনরায় লগইন স্ক্রিনে না পাঠিয়ে সরাসরি হোম স্ক্রিনে নেভিগেট করুন
    Navigator.pushAndRemoveUntil(
      context,
      MaterialPageRoute(builder: (context) => const HomeScreen()),
      (route) => false,
    );
  } else {
    // Show error message
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(jsonResponse['message'] ?? 'Registration failed')),
    );
  }
}
```

### ২.২. অ্যাপ ওপেন করার সময় অটো-লগইন চেক (Splash Screen):
```dart
// splash_screen.dart
void checkAutoLogin() async {
  final prefs = await SharedPreferences.getInstance();
  final token = prefs.getString('auth_token');

  if (token != null && token.isNotEmpty) {
    // ইউজার ইতিমধ্যে লগইন অবস্থায় আছে, সরাসরি হোমে চলে যাবে
    Navigator.pushReplacement(
      context,
      MaterialPageRoute(builder: (context) => const HomeScreen()),
    );
  } else {
    // কোনো সেশন না থাকলে লগইন/রেজিস্টার অপশন দেখাবে
    Navigator.pushReplacement(
      context,
      MaterialPageRoute(builder: (context) => const LoginOrRegisterScreen()),
    );
  }
}
```

---

## 3. ৮ সংখ্যার অ্যাকাউন্ট আইডি ও ফিল্ড ম্যাপিং

প্রোফাইল পেজের ব্যাজে `ID 2` বা ডাটাবেজ ক্রমিক নং দেখানোর বদলে নিচের ফিল্ড ব্যবহার করুন:

| ফিল্ড নাম | রিটার্ন ভ্যালু উদাহরণ | বর্ণনা |
|---|---|---|
| `account_id` | `"84920183"` | ৮ সংখ্যার ইউনিক অ্যাকাউন্ট আইডি (স্ট্রিং) |
| `display_id` | `"84920183"` | অ্যাপের UI-তে প্রদর্শনের জন্য তৈরি সহজ ফিল্ড |
| `uid` | `"84920183"` | অল্টারনেটিভ UID ফিল্ড |

### 🛠️ Flutter UI Widget Fix:
```dart
// ❌ পূর্বে (ভুল - ডাটাবেজ আইডি ২ দেখাত):
Text("ID ${user['id']}")

// ✅ বর্তমানে (সঠিক - ৮ সংখ্যার ইউনিক আইডি):
Text("ID ${user['account_id'] ?? user['display_id']}") // যেমন: ID 84920183
```

---

## 4. সম্পূর্ণ RESTful API রেফারেন্স

Base URL: `https://yourdomain.com/api`

---

### ৪.১. ইউজার রেজিস্ট্রেশন ও ইনস্ট্যান্ট অটো-লগইন
- **Method:** `POST`
- **URL:** `/api/register`
- **Request Headers:** `Accept: application/json`
- **Request Body (Form-Data / JSON):**
```json
{
  "first_name": "Nazmul",
  "last_name": "Hossain",
  "phone": "01706640864",
  "password": "password123",
  "password_confirmation": "password123",
  "gender": "female",
  "age": 27,
  "country": "Bangladesh",
  "city": "Dhaka",
  "introduction": "Sweet girl looking for honest talk ❤️"
}
```

#### Success Response (`201 Created`):
```json
{
  "status": true,
  "message": "Registration successful",
  "data": {
    "token": "1|qwertysanctumtoken...",
    "token_type": "Bearer",
    "user": {
      "id": 2,
      "account_id": "84920183",
      "display_id": "84920183",
      "uid": "84920183",
      "name": "Nazmul Hossain",
      "nickname": "Nazmul",
      "phone": "01706640864",
      "email": "01706640864@user.chinchins.live",
      "gender": "female",
      "age": 27,
      "country": "Bangladesh",
      "city": "Dhaka",
      "coins": 0,
      "is_active": true,
      "avatar_url": null,
      "cover_photo_url": null,
      "gallery_image_urls": []
    }
  }
}
```

---

### ৪.২. ইউজার লগইন (যদি ইউজার কোনো কারণে লগআউট করে থাকে)
- **Method:** `POST`
- **URL:** `/api/login`
- **Request Body:**
```json
{
  "login": "84920183", 
  "password": "password123"
}
```
*(নোট: `login` ফিল্ডে ফোন নম্বর `01706640864`, ইমেইল অথবা ৮ সংখ্যার `account_id` যেকোনোটি ব্যবহার করা যাবে)*

#### Success Response (`200 OK`):
```json
{
  "status": true,
  "message": "Login successful",
  "data": {
    "token": "2|anothersanctumtoken...",
    "token_type": "Bearer",
    "user": {
      "id": 2,
      "account_id": "84920183",
      "display_id": "84920183",
      "name": "Nazmul Hossain",
      "phone": "01706640864",
      "country": "Bangladesh"
    }
  }
}
```

---

### ৪.৩. প্রোফাইল দেখা (`GET /api/profile/me` বা `GET /api/profile/{account_id}`)
- **Method:** `GET`
- **URL:** `/api/profile/me` (নিজের প্রোফাইল) অথবা `/api/profile/84920183` (অন্য ইউজারের প্রোফাইল)
- **Headers:** `Authorization: Bearer <TOKEN>`

#### Success Response (`200 OK`):
```json
{
  "status": true,
  "data": {
    "user": {
      "id": 2,
      "account_id": "84920183",
      "display_id": "84920183",
      "name": "Nazmul Hossain",
      "gender": "female",
      "age": 27,
      "country": "Bangladesh",
      "coins": 434165,
      "avatar_url": "https://yourdomain.com/uploads/profile/avatar.png",
      "cover_photo_url": "https://yourdomain.com/uploads/profile/cover.png",
      "gallery_image_urls": [
        "https://yourdomain.com/uploads/profile/photo1.png"
      ]
    },
    "charm_level": {
      "level": 1,
      "title": "Star 1",
      "badge": "Lv1"
    },
    "top_fan": {
      "name": "Sajid",
      "fan_coins": 54200
    },
    "likes": {
      "total_likes": 0
    }
  }
}
```

---

### ৪.৪. ৮ সংখ্যার আইডি ও নাম দিয়ে সার্চ (`GET /api/search?q=84920183`)
হোম পেজের সার্চ বক্সে ইউজার ৮ সংখ্যার আইডি টাইপ করলে সরাসরি ইউজারের প্রোফাইল রিটার্ন করে:

- **Method:** `GET`
- **URL:** `/api/search?q=84920183` (বা `/api/users/search?search=84920183`)

#### Success Response (`200 OK`):
```json
{
  "status": true,
  "message": "User found with ID 84920183",
  "data": {
    "exact_match": true,
    "users": [
      {
        "id": 2,
        "account_id": "84920183",
        "display_id": "84920183",
        "name": "Nazmul Hossain",
        "gender": "female",
        "age": 27,
        "country": "Bangladesh",
        "avatar_url": "https://yourdomain.com/uploads/profile/avatar.png"
      }
    ]
  }
}
```

---

## 5. ডেভেলপার চেকলিস্ট (Developer Checklist)
- [x] রেজিস্ট্রেশনের পর সরাসরি `data.token` লোকাল স্টোরেজে সেভ করে হোম স্ক্রিনে রিডাইরেক্ট করা হয়েছে (বারবার লগইন করতে হবে না)।
- [x] Splash Screen-এ লোকাল টোকেন চেক করে অটো-লগইন হ্যান্ডেল করা হয়েছে।
- [x] UI-তে `ID ${user.id}` এর বদলে `ID ${user.account_id}` প্রদর্শন করা হয়েছে।
- [x] সার্চ বারে ৮ সংখ্যার অ্যাকাউন্ট আইডি দিয়ে ইউজার প্রোফাইল সার্চ সক্রিয় করা হয়েছে।
