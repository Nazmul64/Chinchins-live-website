# Chinchins Live — RESTful API Documentation & Developer Guide

This document contains complete, dynamic, database-driven RESTful API specifications for the **Chinchins Live** Mobile App (Flutter / Android / iOS) and Web Frontend developers.

> **💡 Zero Hardcoded Data Guarantee:**  
> All data (Home feed, user profiles, online/offline statuses, age, country, multi-image gallery photos, and avatars) are 100% dynamic and pulled directly from the live database.

---

## 🌐 Base URL
```http
Production:  https://your-domain.com/api
Development: http://127.0.0.1:8000/api
```

---

## 🔑 Authentication Architecture
- Public routes (`/register`, `/login`, `/home`, `/users`, `/profile/{id}`) do not require a token.
- Protected routes require a Bearer token in the request header:
```http
Authorization: Bearer <AUTH_TOKEN>
Accept: application/json
```

---

## 📑 Complete API Endpoints Matrix

| Category | Method | Endpoint | Auth | Description |
| :--- | :--- | :--- | :---: | :--- |
| **Auth** | **POST** | `/api/register` | No | Register new user (Name, Phone, Age, Country, Password) |
| **Auth** | **POST** | `/api/login` | No | Login with Phone, Email, or 10-digit Account ID |
| **Auth** | **POST** | `/api/logout` | **Yes** | Logout & revoke access token |
| **Feed** | **GET** | `/api/home` *(or `/api/users`)* | No | **Home Feed** (Live database list of streamers with full image URLs) |
| **Profile** | **GET** | `/api/profile/{id}` | No | View full Profile & Gallery by User ID or 10-digit Account ID |
| **Profile** | **GET** | `/api/profile/me` *(or `/api/user`)* | **Yes** | Get current logged-in user profile ("Me" tab) |
| **Profile** | **POST** | `/api/profile/update` | **Yes** | Update bio, age, country, nickname, level, rate, etc. |
| **Profile** | **POST** | `/api/profile/status` | **Yes** | Toggle Online/Offline status (`Online` vs `Offline`) |
| **Avatar** | **POST** | `/api/profile/upload-avatar` | **Yes** | **Add / Edit / Replace** circular profile avatar |
| **Avatar** | **POST** / **DEL** | `/api/profile/delete-avatar` | **Yes** | **Delete / Remove** profile avatar |
| **Cover** | **POST** | `/api/profile/upload-cover` | **Yes** | **Add / Edit / Replace** background cover photo |
| **Cover** | **POST** / **DEL** | `/api/profile/delete-cover` | **Yes** | **Delete / Remove** background cover photo |
| **Gallery** | **POST** | `/api/profile/upload-photos` | **Yes** | **Add / Upload** multiple gallery photos (`photos[]`) |
| **Gallery** | **POST** / **DEL** | `/api/profile/delete-photo` | **Yes** | **Delete / Remove** a single photo from gallery |
| **Gallery** | **POST** | `/api/profile/update-gallery` | **Yes** | **Reorder / Update** entire gallery photo list |
| **Gallery** | **POST** | `/api/profile/clear-gallery` | **Yes** | **Delete All** gallery photos |

---

## 1. 📝 User Registration API

Creates a new user in the database, automatically generates a unique 10-digit `account_id`, sets status to `Online` (`is_active: true`), and returns an authentication Bearer Token.

### **Endpoint**
`POST /api/register`

### **Headers**
```http
Content-Type: application/json
Accept: application/json
```

### **Request Body Parameters**

| Field | Type | Required | Description | Example |
| :--- | :--- | :---: | :--- | :--- |
| `first_name` | `string` | **Yes** | User's first name | `"Ayeena"` |
| `last_name` | `string` | **Yes** | User's last name | `"Khan"` |
| `phone` *(or `phone_number`)* | `string` | **Yes** | Unique phone number | `"+8801712345678"` |
| `password` | `string` | **Yes** | Minimum 6 characters | `"secretPassword123"` |
| `password_confirmation` *(or `confirm_password`)* | `string` | **Yes** | Must match `password` | `"secretPassword123"` |
| `country` | `string` | No | Country name (e.g. `"Bangladesh"`, `"Pakistan"`) | `"Bangladesh"` |
| `age` | `integer` | No | Age (between 18 and 120, Default: `27`) | `27` |
| `nickname` | `string` | No | Display name / nickname (Default: `first_name`) | `"Ayeena04"` |
| `gender` | `string` | No | `"female"`, `"male"`, or `"other"` (Default: `"female"`) | `"female"` |
| `email` | `string` | No | Optional email. Auto-generated if omitted. | `"ayeena@example.com"` |
| `city` | `string` | No | City name | `"Dhaka"` |
| `introduction` | `string` | No | Profile bio | `"Sweet girl looking for honest talk ❤️"` |
| `languages` | `array` | No | Spoken languages | `["English", "Bengali"]` |
| `tags` | `array` | No | Interests / tags | `["Live video", "Music"]` |
| `video_call_rate` | `integer` | No | Diamond rate per minute (Default: `1800`) | `1800` |

### **Example Request**
```json
{
  "first_name": "Ayeena",
  "last_name": "Khan",
  "nickname": "Ayeena04",
  "phone": "+8801712345678",
  "country": "Bangladesh",
  "age": 27,
  "gender": "female",
  "password": "secretPassword123",
  "password_confirmation": "secretPassword123"
}
```

### **Success Response (HTTP 201 Created)**
```json
{
  "status": true,
  "message": "Registration successful",
  "data": {
    "user": {
      "id": 15,
      "account_id": "602281635",
      "first_name": "Ayeena",
      "last_name": "Khan",
      "name": "Ayeena Khan",
      "nickname": "Ayeena04",
      "display_name": "Ayeena04",
      "phone": "+8801712345678",
      "email": "8801712345678@user.chinchins.live",
      "country": "Bangladesh",
      "city": null,
      "gender": "female",
      "age": 27,
      "is_active": true,
      "is_online": true,
      "status_text": "Online",
      "is_verified": true,
      "level": "Lv4",
      "video_call_rate": 1800,
      "close_friends_count": 0,
      "avatar_url": null,
      "cover_photo_url": null,
      "gallery_image_urls": [],
      "profile_picture": null,
      "photos": [],
      "gallery": [],
      "introduction": "Sweet girl looking for honest talk ❤️",
      "languages": ["English", "Bengali"],
      "tags": ["Live video", "Music"],
      "created_at": "2026-08-25T20:25:00.000000Z",
      "updated_at": "2026-08-25T20:25:00.000000Z"
    },
    "token": "1|5gXQyV8tP9...your_sanctum_bearer_token...",
    "token_type": "Bearer"
  }
}
```

---

## 2. 🏠 Home Page Feed API (100% Live Database Data)

Fetches active streamers directly from the database for the **Home / Hot / For You** screen with full image URLs for instant rendering.

### **Endpoint**
`GET /api/home` *(or `GET /api/users`)*

### **Query Parameters (Optional)**
| Parameter | Type | Description | Example |
| :--- | :--- | :--- | :--- |
| `is_active` | `boolean` | Filter by online status (`1` = Online only) | `?is_active=1` |
| `country` | `string` | Filter by country | `?country=Bangladesh` |
| `gender` | `string` | Filter by gender (`female`, `male`) | `?gender=female` |
| `search` | `string` | Search query for name, nickname, ID, country | `?search=Ayeena` |
| `page` | `integer` | Page number | `?page=1` |
| `per_page` | `integer` | Items per page (Default: `20`) | `?per_page=20` |

### **Success Response (HTTP 200 OK)**
```json
{
  "status": true,
  "message": "Home feed loaded successfully from database",
  "data": {
    "users": [
      {
        "id": 15,
        "account_id": "602281635",
        "display_name": "Ayeena04",
        "avatar_url": "https://your-domain.com/storage/profiles/15/avatar_xxx.jpg",
        "cover_photo_url": "https://your-domain.com/storage/profiles/15/gallery/img1.jpg",
        "gallery_image_urls": [
          "https://your-domain.com/storage/profiles/15/gallery/img1.jpg",
          "https://your-domain.com/storage/profiles/15/gallery/img2.jpg",
          "https://your-domain.com/storage/profiles/15/gallery/img3.jpg",
          "https://your-domain.com/storage/profiles/15/gallery/img4.jpg",
          "https://your-domain.com/storage/profiles/15/gallery/img5.jpg"
        ],
        "profile_picture": "https://your-domain.com/storage/profiles/15/avatar_xxx.jpg",
        "photos": [
          "https://your-domain.com/storage/profiles/15/gallery/img1.jpg",
          "https://your-domain.com/storage/profiles/15/gallery/img2.jpg",
          "https://your-domain.com/storage/profiles/15/gallery/img3.jpg",
          "https://your-domain.com/storage/profiles/15/gallery/img4.jpg",
          "https://your-domain.com/storage/profiles/15/gallery/img5.jpg"
        ],
        "country": "Bangladesh",
        "age": 27,
        "gender": "female",
        "level": "Lv4",
        "is_active": true,
        "is_online": true,
        "status_text": "Online",
        "is_verified": true,
        "video_call_rate": 1800,
        "close_friends_count": 0,
        "introduction": "Sweet girl looking for honest talk ❤️"
      }
    ],
    "total": 1,
    "current_page": 1,
    "last_page": 1,
    "per_page": 20
  }
}
```

---

## 3. 👤 User Profile & Gallery API

Triggered when a user clicks on any card from the Home feed or opens the "Me" tab.

### **A. View Any Host/User Profile (Public)**
`GET /api/profile/{id}`
*(Pass either the user's database `id` or the 10-digit `account_id`)*

### **B. View Current User's Profile ("Me" Tab)**
`GET /api/profile/me` *(Requires `Authorization: Bearer <TOKEN>`)*

### **Success Response (HTTP 200 OK)**
```json
{
  "status": true,
  "data": {
    "user": {
      "id": 15,
      "account_id": "602281635",
      "display_name": "Ayeena04",
      "avatar_url": "https://your-domain.com/storage/profiles/15/avatar_xxx.jpg",
      "cover_photo_url": "https://your-domain.com/storage/profiles/15/gallery/img1.jpg",
      "gallery_image_urls": [
        "https://your-domain.com/storage/profiles/15/gallery/img1.jpg",
        "https://your-domain.com/storage/profiles/15/gallery/img2.jpg",
        "https://your-domain.com/storage/profiles/15/gallery/img3.jpg",
        "https://your-domain.com/storage/profiles/15/gallery/img4.jpg",
        "https://your-domain.com/storage/profiles/15/gallery/img5.jpg"
      ],
      "country": "Bangladesh",
      "city": "Dhaka",
      "gender": "female",
      "age": 27,
      "level": "Lv4",
      "is_active": true,
      "is_online": true,
      "status_text": "Online",
      "is_verified": true,
      "close_friends_count": 0,
      "video_call_rate": 1800,
      "introduction": "Sweet girl looking for honest talk ❤️",
      "languages": ["English", "Bengali"],
      "tags": ["Live video", "Music"]
    }
  }
}
```

---

## 4. 🖼️ Multi-Image Gallery APIs (Add, Edit, Reorder, Delete)

### **A. Upload Multiple Gallery Photos (Add / Edit)**
- **Endpoint**: `POST /api/profile/upload-photos`
- **Headers**: `Authorization: Bearer <TOKEN>`, `Content-Type: multipart/form-data`
- **Body**: `photos[]` (Array of image files) or `photo` / `images[]` / `image`
- **Automatic Behavior**: The first uploaded gallery photo is automatically set as the top background `cover_photo` if none is set!

### **B. Delete a Single Photo from Gallery (Delete)**
- **Endpoint**: `POST /api/profile/delete-photo` *(or `DELETE /api/profile/photos`)*
- **Headers**: `Authorization: Bearer <TOKEN>`, `Content-Type: application/json`
- **Body**:
```json
{
  "photo": "https://your-domain.com/storage/profiles/15/gallery/img3.jpg"
}
```

### **C. Reorder / Replace Gallery Array (Edit / Update)**
- **Endpoint**: `POST /api/profile/update-gallery`
- **Headers**: `Authorization: Bearer <TOKEN>`, `Content-Type: application/json`
- **Body**:
```json
{
  "photos": [
    "profiles/15/gallery/img2.jpg",
    "profiles/15/gallery/img1.jpg",
    "profiles/15/gallery/img4.jpg"
  ]
}
```

### **D. Clear All Gallery Photos (Delete All)**
- **Endpoint**: `POST /api/profile/clear-gallery`
- **Headers**: `Authorization: Bearer <TOKEN>`

---

## 5. 📸 Profile Avatar APIs (Add, Edit, Delete)

### **A. Upload or Replace Profile Avatar (Add / Edit)**
- **Endpoint**: `POST /api/profile/upload-avatar`
- **Headers**: `Authorization: Bearer <TOKEN>`, `Content-Type: multipart/form-data`
- **Body**: `avatar` (Image file) *(or `photo` / `image` / `profile_picture`)*

### **B. Delete Profile Avatar (Delete)**
- **Endpoint**: `POST /api/profile/delete-avatar` *(or `DELETE /api/profile/avatar`)*
- **Headers**: `Authorization: Bearer <TOKEN>`

---

## 6. 🌅 Cover Photo APIs (Add, Edit, Delete)

### **A. Upload or Replace Background Cover Photo (Add / Edit)**
- **Endpoint**: `POST /api/profile/upload-cover`
- **Headers**: `Authorization: Bearer <TOKEN>`, `Content-Type: multipart/form-data`
- **Body**: `cover_photo` (Image file) *(or `cover` / `photo` / `image`)*

### **B. Delete Background Cover Photo (Delete)**
- **Endpoint**: `POST /api/profile/delete-cover` *(or `DELETE /api/profile/cover`)*
- **Headers**: `Authorization: Bearer <TOKEN>`

---

## 7. 🟢 Toggle Online / Offline Status API

Changes the user's live status between `Online` and `Offline`.

### **Endpoint**
`POST /api/profile/status`

### **Request Body (Optional - Toggles automatically if omitted)**
```json
{
  "is_active": true
}
```

### **Response (HTTP 200 OK)**
```json
{
  "status": true,
  "message": "Status updated to Active",
  "data": {
    "is_active": true,
    "is_online": true,
    "status": "Active",
    "status_text": "Online"
  }
}
```

---

## 8. 🔐 User Login API

Authenticate using Phone number, Email, or 10-digit Account ID along with Password.

### **Endpoint**
`POST /api/login`

### **Request Body**
```json
{
  "identifier": "+8801712345678",
  "password": "secretPassword123"
}
```

### **Response (HTTP 200 OK)**
```json
{
  "status": true,
  "message": "Login successful",
  "data": {
    "user": { ... },
    "token": "2|9a8Bz...",
    "token_type": "Bearer"
  }
}
```

---

## 📱 Complete Flutter / Dart Service Implementation

```dart
import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;

class ChinchinsApi {
  static const String baseUrl = 'http://127.0.0.1:8000/api';

  // 1. User Registration
  static Future<Map<String, dynamic>> register({
    required String firstName,
    required String lastName,
    required String phone,
    required String password,
    required String country,
    required int age,
    String? nickname,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/register'),
      headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
      body: jsonEncode({
        'first_name': firstName,
        'last_name': lastName,
        'phone': phone,
        'country': country,
        'age': age,
        'nickname': nickname ?? firstName,
        'password': password,
        'password_confirmation': password,
      }),
    );
    return jsonDecode(response.body);
  }

  // 2. Fetch Home Feed (Real Database Live Hosts with full URLs)
  static Future<List<dynamic>> fetchHomeFeed({int page = 1}) async {
    final response = await http.get(
      Uri.parse('$baseUrl/home?page=$page&is_active=1'),
      headers: {'Accept': 'application/json'},
    );
    final data = jsonDecode(response.body);
    return data['data']['users'] ?? [];
  }

  // 3. Fetch Host Profile & Gallery by ID or 10-digit Account ID
  static Future<Map<String, dynamic>> fetchProfile(dynamic idOrAccountId) async {
    final response = await http.get(
      Uri.parse('$baseUrl/profile/$idOrAccountId'),
      headers: {'Accept': 'application/json'},
    );
    final data = jsonDecode(response.body);
    return data['data']['user'];
  }

  // 4. Upload Multiple Gallery Photos (Add / Edit Gallery)
  static Future<Map<String, dynamic>> uploadGalleryPhotos({
    required String token,
    required List<File> imageFiles,
  }) async {
    var request = http.MultipartRequest('POST', Uri.parse('$baseUrl/profile/upload-photos'));
    request.headers['Authorization'] = 'Bearer $token';
    request.headers['Accept'] = 'application/json';

    for (var file in imageFiles) {
      request.files.add(await http.MultipartFile.fromPath('photos[]', file.path));
    }

    var streamedResponse = await request.send();
    var response = await http.Response.fromStream(streamedResponse);
    return jsonDecode(response.body);
  }

  // 5. Delete Single Gallery Photo
  static Future<bool> deleteGalleryPhoto({
    required String token,
    required String photoUrlOrPath,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/profile/delete-photo'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode({'photo': photoUrlOrPath}),
    );
    final data = jsonDecode(response.body);
    return data['status'] == true;
  }

  // 6. Upload / Replace Avatar (Profile Picture)
  static Future<Map<String, dynamic>> uploadAvatar({
    required String token,
    required File avatarFile,
  }) async {
    var request = http.MultipartRequest('POST', Uri.parse('$baseUrl/profile/upload-avatar'));
    request.headers['Authorization'] = 'Bearer $token';
    request.headers['Accept'] = 'application/json';
    request.files.add(await http.MultipartFile.fromPath('avatar', avatarFile.path));

    var streamedResponse = await request.send();
    var response = await http.Response.fromStream(streamedResponse);
    return jsonDecode(response.body);
  }

  // 7. Delete Avatar
  static Future<bool> deleteAvatar({required String token}) async {
    final response = await http.post(
      Uri.parse('$baseUrl/profile/delete-avatar'),
      headers: {'Authorization': 'Bearer $token', 'Accept': 'application/json'},
    );
    final data = jsonDecode(response.body);
    return data['status'] == true;
  }

  // 8. Upload / Replace Cover Photo
  static Future<Map<String, dynamic>> uploadCover({
    required String token,
    required File coverFile,
  }) async {
    var request = http.MultipartRequest('POST', Uri.parse('$baseUrl/profile/upload-cover'));
    request.headers['Authorization'] = 'Bearer $token';
    request.headers['Accept'] = 'application/json';
    request.files.add(await http.MultipartFile.fromPath('cover_photo', coverFile.path));

    var streamedResponse = await request.send();
    var response = await http.Response.fromStream(streamedResponse);
    return jsonDecode(response.body);
  }

  // 9. Delete Cover Photo
  static Future<bool> deleteCover({required String token}) async {
    final response = await http.post(
      Uri.parse('$baseUrl/profile/delete-cover'),
      headers: {'Authorization': 'Bearer $token', 'Accept': 'application/json'},
    );
    final data = jsonDecode(response.body);
    return data['status'] == true;
  }

  // 10. Toggle Online / Offline Status
  static Future<bool> setOnlineStatus({required String token, required bool isOnline}) async {
    final response = await http.post(
      Uri.parse('$baseUrl/profile/status'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode({'is_active': isOnline}),
    );
    final data = jsonDecode(response.body);
    return data['status'] == true;
  }
}
```
