# 📱 Chinchins Live — User Registration, Profile & Media RESTful API Documentation

> **Base URL:** `https://chinchins.live/api` (or `http://127.0.0.1:8000/api` for local testing)  
> **Headers:**
> ```http
> Content-Type: application/json
> Accept: application/json
> Authorization: Bearer <auth_token> (or send user_id in request body / header X-User-Id)
> ```
> **Public Media Directory:** All uploaded avatar, cover, and gallery photos are saved directly to `public/uploads/profile/` and served as absolute URLs (`https://chinchins.live/uploads/profile/...`).

---

## 📑 Table of Contents
1. [User Registration (`POST /api/register`)](#1-user-registration)
2. [User Login (`POST /api/login`)](#2-user-login)
3. [Get User Profile (`GET /api/profile/{id}` & `GET /api/profile/me`)](#3-get-user-profile)
4. [Update Profile Information (`POST /api/profile/update`)](#4-update-profile-information)
5. [Avatar Photo — Upload & Replace (`POST /api/profile/upload-avatar`)](#5-avatar-photo--upload--replace)
6. [Avatar Photo — Delete (`DELETE /api/profile/delete-avatar`)](#6-avatar-photo--delete)
7. [Cover Photo — Upload & Replace (`POST /api/profile/upload-cover`)](#7-cover-photo--upload--replace)
8. [Cover Photo — Delete (`DELETE /api/profile/delete-cover`)](#8-cover-photo--delete)
9. [Gallery Photos — Upload Single/Multiple (`POST /api/profile/upload-photos`)](#9-gallery-photos--upload)
10. [Gallery Photos — Delete Single Photo (`DELETE /api/profile/delete-photo`)](#10-gallery-photos--delete-single-photo)
11. [Gallery Photos — Clear All Photos (`DELETE /api/profile/clear-gallery`)](#11-gallery-photos--clear-all)
12. [Toggle Online/Offline Status (`POST /api/profile/status`)](#12-toggle-onlineoffline-status)
13. [Flutter / Dart Complete Service Example](#13-flutter--dart-integration-example)

---

## 1. User Registration
Creates a new user account with auto-generated 10-digit Account ID, initial coins, and returns authentication Bearer token.

* **Endpoint:** `POST /api/register`
* **Content-Type:** `application/json` or `multipart/form-data`

### Request Body:
```json
{
  "first_name": "Sadia",
  "last_name": "Afrin",
  "phone": "01700000000",
  "password": "password123",
  "password_confirmation": "password123",
  "nickname": "Sadia",
  "gender": "female",
  "age": 22,
  "country": "Bangladesh",
  "city": "Dhaka",
  "introduction": "Hello! Welcome to my live stream profile ❤️",
  "video_call_rate": 100
}
```

### Response (201 Created):
```json
{
  "status": true,
  "message": "Registration successful",
  "data": {
    "user": {
      "id": 5,
      "account_id": "6022816358",
      "first_name": "Sadia",
      "last_name": "Afrin",
      "name": "Sadia Afrin",
      "nickname": "Sadia",
      "display_name": "Sadia",
      "phone": "01700000000",
      "email": "01700000000@user.chinchins.live",
      "coins": 0,
      "avatar_url": null,
      "cover_photo_url": null,
      "gallery_image_urls": [],
      "gender": "female",
      "age": 22,
      "country": "Bangladesh",
      "city": "Dhaka",
      "introduction": "Hello! Welcome to my live stream profile ❤️",
      "video_call_rate": 100,
      "is_active": true,
      "is_online": true,
      "status_text": "Online"
    },
    "token": "1|abcdef123456789...",
    "token_type": "Bearer"
  }
}
```

---

## 2. User Login
Authenticates an existing user via phone number, email, or 10-digit account ID.

* **Endpoint:** `POST /api/login`
* **Content-Type:** `application/json`

### Request Body:
```json
{
  "phone": "01700000000",
  "password": "password123"
}
```

### Response (200 OK):
```json
{
  "status": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 5,
      "account_id": "6022816358",
      "name": "Sadia Afrin",
      "nickname": "Sadia",
      "coins": 5000,
      "avatar_url": "https://chinchins.live/uploads/profile/avatar_5_1787829102.jpg",
      "cover_photo_url": "https://chinchins.live/uploads/profile/cover_5_1787829103.jpg",
      "gallery_image_urls": [
        "https://chinchins.live/uploads/profile/gallery_5_1787829104.jpg",
        "https://chinchins.live/uploads/profile/gallery_5_1787829105.jpg"
      ]
    },
    "token": "2|abcdef123456789...",
    "token_type": "Bearer"
  }
}
```

---

## 3. Get User Profile
View full profile details of any user by their Database ID or 10-digit `account_id`, or `me` for own profile.

* **Endpoint:** `GET /api/profile/{id}` (or `GET /api/profile/me`)
* **Headers:** `Authorization: Bearer <token>` (optional for public profiles)

### Response (200 OK):
```json
{
  "status": true,
  "data": {
    "user": {
      "id": 5,
      "account_id": "6022816358",
      "first_name": "Sadia",
      "last_name": "Afrin",
      "display_name": "Sadia",
      "avatar_url": "https://chinchins.live/uploads/profile/avatar_5_1787829102.jpg",
      "cover_photo_url": "https://chinchins.live/uploads/profile/cover_5_1787829103.jpg",
      "gallery_image_urls": [
        "https://chinchins.live/uploads/profile/gallery_5_1787829104.jpg"
      ],
      "coins": 2500,
      "video_call_rate": 100,
      "is_online": true
    }
  }
}
```

---

## 4. Update Profile Information
Update text profile metadata (name, nickname, bio, gender, age, country, city, video call rate).

* **Endpoint:** `POST /api/profile/update`
* **Headers:** `Authorization: Bearer <token>`
* **Content-Type:** `application/json`

### Request Body:
```json
{
  "first_name": "Sadia",
  "last_name": "Islam",
  "nickname": "Sadia Queen 👑",
  "introduction": "Live streamer & artist. Talk with me on video call! ✨",
  "age": 23,
  "country": "Bangladesh",
  "city": "Dhaka",
  "video_call_rate": 100
}
```

---

## 5. Avatar Photo — Upload & Replace
Uploads or replaces the user's primary profile avatar. Automatically removes the previous avatar from `public/uploads/profile/` to save disk space.

* **Endpoint:** `POST /api/profile/upload-avatar` (Alias: `POST /api/upload-avatar`)
* **Headers:** `Authorization: Bearer <token>`
* **Content-Type:** `multipart/form-data`

### Form-Data Parameters:
| Key | Type | Description |
|---|---|---|
| `avatar` | File (PNG/JPG/SVG/WEBP) | Profile image binary file (Accepts: `avatar`, `photo`, `image`, `profile_picture`) |
| `avatar_base64` | String (Optional) | Base64 string fallback if sending raw data |

### Response (200 OK):
```json
{
  "status": true,
  "message": "Avatar updated successfully",
  "data": {
    "avatar": "uploads/profile/avatar_5_1787829102_xyz123.jpg",
    "avatar_url": "https://chinchins.live/uploads/profile/avatar_5_1787829102_xyz123.jpg",
    "profile_picture": "https://chinchins.live/uploads/profile/avatar_5_1787829102_xyz123.jpg"
  }
}
```

---

## 6. Avatar Photo — Delete
Removes the avatar picture from disk and resets it to `null`.

* **Endpoint:** `DELETE /api/profile/delete-avatar` (Alias: `POST /api/profile/delete-avatar` or `DELETE /api/profile/avatar`)
* **Headers:** `Authorization: Bearer <token>`

### Response (200 OK):
```json
{
  "status": true,
  "message": "Avatar removed successfully",
  "data": {
    "avatar": null,
    "avatar_url": null,
    "profile_picture": null
  }
}
```

---

## 7. Cover Photo — Upload & Replace
Uploads or replaces the user's cover banner background image.

* **Endpoint:** `POST /api/profile/upload-cover` (Alias: `POST /api/upload-cover`)
* **Headers:** `Authorization: Bearer <token>`
* **Content-Type:** `multipart/form-data`

### Form-Data Parameters:
| Key | Type | Description |
|---|---|---|
| `cover_photo` | File (PNG/JPG/WEBP) | Cover image binary file (Accepts: `cover_photo`, `cover`, `photo`, `image`) |

### Response (200 OK):
```json
{
  "status": true,
  "message": "Cover photo updated successfully",
  "data": {
    "cover_photo": "uploads/profile/cover_5_1787829103_abc456.jpg",
    "cover_photo_url": "https://chinchins.live/uploads/profile/cover_5_1787829103_abc456.jpg"
  }
}
```

---

## 8. Cover Photo — Delete
Removes the cover banner picture from disk and sets it to `null`.

* **Endpoint:** `DELETE /api/profile/delete-cover` (Alias: `POST /api/profile/delete-cover` or `DELETE /api/profile/cover`)
* **Headers:** `Authorization: Bearer <token>`

### Response (200 OK):
```json
{
  "status": true,
  "message": "Cover photo removed successfully",
  "data": {
    "cover_photo": null,
    "cover_photo_url": null
  }
}
```

---

## 9. Gallery Photos — Upload
Upload one or multiple photos to the user's gallery collection.

* **Endpoint:** `POST /api/profile/upload-photos` (Aliases: `POST /api/gallery/upload`, `POST /api/upload-photos`)
* **Headers:** `Authorization: Bearer <token>`
* **Content-Type:** `multipart/form-data`

### Form-Data Parameters:
| Key | Type | Description |
|---|---|---|
| `photos[]` or `images[]` | Array of Files | Multiple image files |
| `photo` or `image` | File | Single image file |

### Response (200 OK):
```json
{
  "status": true,
  "message": "2 photo(s) uploaded successfully",
  "data": {
    "uploaded_paths": [
      "uploads/profile/gallery_5_1787829104_g1a2b3.jpg",
      "uploads/profile/gallery_5_1787829105_g4c5d6.jpg"
    ],
    "gallery_image_urls": [
      "https://chinchins.live/uploads/profile/gallery_5_1787829104_g1a2b3.jpg",
      "https://chinchins.live/uploads/profile/gallery_5_1787829105_g4c5d6.jpg"
    ],
    "photos": [
      "https://chinchins.live/uploads/profile/gallery_5_1787829104_g1a2b3.jpg",
      "https://chinchins.live/uploads/profile/gallery_5_1787829105_g4c5d6.jpg"
    ]
  }
}
```

---

## 10. Gallery Photos — Delete Single Photo
Delete an individual photo from the user's gallery by its URL, path, filename, or index.

* **Endpoint:** `DELETE /api/profile/delete-photo` (Aliases: `POST /api/profile/delete-photo`, `DELETE /api/gallery/delete`, `POST /api/gallery/delete`)
* **Headers:** `Authorization: Bearer <token>`
* **Content-Type:** `application/json`

### Request Body (Delete by URL or Path):
```json
{
  "photo": "https://chinchins.live/uploads/profile/gallery_5_1787829104_g1a2b3.jpg"
}
```
*(Or send `"index": 0` to delete the first photo).*

### Response (200 OK):
```json
{
  "status": true,
  "message": "Photo deleted successfully",
  "data": {
    "deleted": true,
    "gallery_image_urls": [
      "https://chinchins.live/uploads/profile/gallery_5_1787829105_g4c5d6.jpg"
    ]
  }
}
```

---

## 11. Gallery Photos — Clear All
Deletes all gallery photos from disk and empties the gallery collection.

* **Endpoint:** `DELETE /api/profile/clear-gallery` (Aliases: `DELETE /api/gallery/clear`, `POST /api/gallery/clear`)
* **Headers:** `Authorization: Bearer <token>`

### Response (200 OK):
```json
{
  "status": true,
  "message": "All gallery photos cleared",
  "data": {
    "gallery_images": [],
    "gallery_image_urls": [],
    "photos": []
  }
}
```

---

## 12. Toggle Online/Offline Status
Update the user's active online presence.

* **Endpoint:** `POST /api/profile/status`
* **Headers:** `Authorization: Bearer <token>`
* **Content-Type:** `application/json`

### Request Body:
```json
{
  "is_active": true
}
```

---

## 13. Flutter / Dart Integration Example

```dart
import 'dart:io';
import 'package:http/http.dart' as http;
import 'dart:convert';

class ProfileApiService {
  static const String baseUrl = 'https://chinchins.live/api';

  // 1. Upload Avatar
  static Future<String?> uploadAvatar(File imageFile, String authToken) async {
    final uri = Uri.parse('$baseUrl/profile/upload-avatar');
    final request = http.MultipartRequest('POST', uri)
      ..headers['Authorization'] = 'Bearer $authToken'
      ..headers['Accept'] = 'application/json'
      ..files.add(await http.MultipartFile.fromPath('avatar', imageFile.path));

    final streamedResponse = await request.send();
    final response = await http.Response.fromStream(streamedResponse);
    final json = jsonDecode(response.body);

    if (response.statusCode == 200 && json['status'] == true) {
      return json['data']['avatar_url'];
    }
    throw Exception(json['message'] ?? 'Failed to upload avatar');
  }

  // 2. Upload Cover Photo
  static Future<String?> uploadCover(File imageFile, String authToken) async {
    final uri = Uri.parse('$baseUrl/profile/upload-cover');
    final request = http.MultipartRequest('POST', uri)
      ..headers['Authorization'] = 'Bearer $authToken'
      ..headers['Accept'] = 'application/json'
      ..files.add(await http.MultipartFile.fromPath('cover_photo', imageFile.path));

    final streamedResponse = await request.send();
    final response = await http.Response.fromStream(streamedResponse);
    final json = jsonDecode(response.body);

    if (response.statusCode == 200 && json['status'] == true) {
      return json['data']['cover_photo_url'];
    }
    throw Exception(json['message'] ?? 'Failed to upload cover');
  }

  // 3. Upload Gallery Photo
  static Future<List<String>> uploadGalleryPhoto(File imageFile, String authToken) async {
    final uri = Uri.parse('$baseUrl/gallery/upload');
    final request = http.MultipartRequest('POST', uri)
      ..headers['Authorization'] = 'Bearer $authToken'
      ..headers['Accept'] = 'application/json'
      ..files.add(await http.MultipartFile.fromPath('photo', imageFile.path));

    final streamedResponse = await request.send();
    final response = await http.Response.fromStream(streamedResponse);
    final json = jsonDecode(response.body);

    if (response.statusCode == 200 && json['status'] == true) {
      return List<String>.from(json['data']['gallery_image_urls']);
    }
    throw Exception(json['message'] ?? 'Failed to upload gallery photo');
  }

  // 4. Delete Single Gallery Photo
  static Future<List<String>> deleteGalleryPhoto(String photoUrl, String authToken) async {
    final response = await http.post(
      Uri.parse('$baseUrl/gallery/delete'),
      headers: {
        'Authorization': 'Bearer $authToken',
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode({'photo': photoUrl}),
    );

    final json = jsonDecode(response.body);
    if (response.statusCode == 200 && json['status'] == true) {
      return List<String>.from(json['data']['gallery_image_urls']);
    }
    throw Exception(json['message'] ?? 'Failed to delete photo');
  }
}
```
