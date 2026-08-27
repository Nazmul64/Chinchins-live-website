# 🪪 Chinchins Live — Binance-Style AI KYC & Streamer Verification API Documentation

This documentation provides the complete technical specification for the **Binance-Style KYC Identity Verification System** (NID Card, Passport, Birth Certificate), **Real-Time AI Video Face Scanner & Voice Prompts**, and **Account Face Re-Unlock System** for Flutter mobile app developers and backend administrators.

---

## 🌐 Base URL & Endpoints

```http
Production:  https://your-domain.com/api
Development: http://127.0.0.1:8000/api
```

> **Note**: For mobile app flexibility, all routes work both **with** `/api/` (e.g. `/api/kyc/submit`) and **without** `/api/` (e.g. `/kyc/submit`). All endpoints strictly return pure JSON `application/json` responses.

---

## 🔑 Authentication Architecture

- **Primary**: `Authorization: Bearer <SANCTUM_TOKEN>`
- **Fallback**: `X-User-Id: <USER_ID_OR_ACCOUNT_ID>` or request parameter `user_id=<USER_ID>`
- **Headers**:
```http
Accept: application/json
Content-Type: multipart/form-data (or application/json for Base64)
Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxx
```

---

## 📑 Complete RESTful API Matrix

| # | Method | Endpoint | Description |
| :---: | :---: | :--- | :--- |
| **1** | `GET` | `/api/kyc/instructions` | Get document requirements, field lists, and AI face scan guidelines |
| **2** | `POST` | `/api/kyc/ai-detect` | Fast AI quality pre-check for document edges, lighting, and face clarity |
| **3** | `POST` | `/api/kyc/video-verify` | **Binance-Style Live Video Face Scan** with voice prompts & circular progress (0-100%) |
| **4** | `POST` | `/api/kyc/submit` | Submit full KYC with document front/back and live face video/selfie |
| **5** | `GET` | `/api/kyc/status` | Get user's KYC verification status (`pending`, `approved`, `rejected`) and badge info |
| **6** | `POST` | `/api/kyc/face/unlock` | **Direct Biometric Face Re-Unlock** to automatically reactivate blocked/locked accounts |
| **7** | `GET` | `/api/admin/kyc-verifications` | Admin: List submissions with status filters and search |
| **8** | `POST` | `/api/admin/kyc-verifications/{id}/approve` | Admin: Approve submission and grant blue **Verified** badge |
| **9** | `POST` | `/api/admin/kyc-verifications/{id}/reject` | Admin: Reject submission with custom reason |
| **10**| `POST` | `/admin/users/{id}/toggle-lock` | Admin: Manually Lock or Unlock user account |

---

## 🎥 1. Binance-Style Live AI Face Scanner Architecture

Unlike traditional apps that ask for 4 static photo uploads, Chinchins Live uses a **single continuous AI face scan** inside a circular camera frame with real-time prompts:

```
                      ┌─────────────────────────────────┐
                      │    [ AI Circular Camera UI ]    │
                      │                                 │
                      │   Step 1: Look Center     (25%) │
                      │   Step 2: Turn Left       (50%) │
                      │   Step 3: Turn Right      (75%) │
                      │   Step 4: Blink / Smile  (100%) │
                      └────────────────┬────────────────┘
                                       │
                         [ Live Video Stream / Frame ]
                                       │
                                       ▼
                   ┌───────────────────────────────────────┐
                   │  Laravel Python AI Engine (OpenCV)    │
                   │  - Face Orientation & Yaw Angle       │
                   │  - Eye Aspect Ratio (EAR) Blink       │
                   │  - Liveness & Anti-Spoofing Score     │
                   └───────────────────┬───────────────────┘
                                       │
                     [ Stored in public/uploads/kyc/ ]
                                       │
                                       ▼
                    ┌─────────────────────────────────────┐
                    │  Admin Dashboard HTML5 Video Player │
                    └─────────────────────────────────────┘
```

---

## 📖 2. API Specifications

### **A. KYC Guidelines & Requirements**
`GET /api/kyc/instructions`

**Response (200 OK):**
```json
{
  "status": true,
  "message": "KYC verification instructions & document guidelines.",
  "data": {
    "supported_documents": [
      {
        "type": "nid",
        "title": "National ID Card (NID)",
        "required_fields": ["full_name", "document_number", "date_of_birth", "front_image", "back_image", "face_video"]
      },
      {
        "type": "passport",
        "title": "International Passport",
        "required_fields": ["full_name", "document_number", "date_of_birth", "front_image", "face_video"]
      },
      {
        "type": "birth_certificate",
        "title": "Birth Certificate (জন্ম নিবন্ধন)",
        "required_fields": ["full_name", "document_number", "date_of_birth", "front_image", "face_video"]
      }
    ]
  }
}
```

---

### **B. AI Quality Pre-Check API**
`POST /api/kyc/ai-detect` *(Aliases: `/api/kyc/pre-check`, `/kyc/ai-detect`)*

**Request Parameters (Multipart or JSON):**
- `front_image`: file or base64 (optional)
- `selfie_image`: file or base64 (optional)
- `user_id`: optional

**Response (200 OK):**
```json
{
  "status": true,
  "message": "AI face and document quality check completed successfully.",
  "data": {
    "face_detected": true,
    "detected_pose": "center",
    "face_centered": true,
    "eyes_open": true,
    "lighting_score": 0.96,
    "blur_score": 0.05,
    "glare_detected": false,
    "document_corners": 4,
    "text_legibility": "excellent",
    "liveness_confidence": 0.99,
    "instruction_en": "Look directly at the camera at eye level.",
    "instruction_bn": "চোখের সমান্তরালে সরাসরি ক্যামেরার দিকে তাকান।",
    "all_steps_progress": {
      "center": true,
      "turn_left": true,
      "turn_right": true,
      "blink": true
    },
    "status": "PASSED"
  }
}
```

---

### **C. Binance-Style Live Video Face Scan API**
`POST /api/kyc/video-verify` *(Aliases: `/api/kyc/video-scan`, `/kyc/video-verify`)*

**Request Parameters (Multipart):**
- `video` or `face_video`: file (MP4/WebM recorded live face scan) or Base64

**Response (200 OK):**
```json
{
  "status": true,
  "message": "Live video face scan processed successfully.",
  "data": {
    "video_url": "https://your-domain.com/uploads/kyc/kyc_video_2_1787821000_abc.mp4",
    "progress_percentage": 100,
    "is_completed": true,
    "face_detected": true,
    "detected_pose": "center",
    "yaw_angle": 0.0,
    "confidence_score": 0.99,
    "audio_prompt_en": "Live face scan verified successfully!",
    "audio_prompt_bn": "লাইভ ফেস স্ক্যান সফলভাবে সম্পন্ন হয়েছে!",
    "all_steps_progress": {
      "center": true,
      "turn_left": true,
      "turn_right": true,
      "blink": true
    }
  }
}
```

---

### **D. Submit Full KYC Verification API**
`POST /api/kyc/submit` *(Aliases: `/api/profile/kyc/submit`, `/kyc/submit`)*

**Request Parameters (Multipart):**
| Field | Type | Required | Description |
| :--- | :---: | :---: | :--- |
| `document_type` | string | **Yes** | `nid` (default), `passport`, `birth_certificate` |
| `full_name` | string | **Yes** | Full legal name matching document |
| `document_number` | string | **Yes** | NID / Passport / Certificate number |
| `date_of_birth` | string | Optional | `YYYY-MM-DD` (alias: `dob`) |
| `front_image` | file | **Yes** | Clear front photo of document |
| `back_image` | file | **Yes (NID)** | Clear back photo of NID card |
| `selfie_image` | file | **Yes** | Selfie / face snapshot |
| `face_video` | file | Optional | Recorded Live AI video scan (`.mp4`) |
| `user_notes` | string | Optional | Optional user note |

**Response (200 OK):**
```json
{
  "status": true,
  "message": "KYC verification submitted successfully. It is currently under review by our admin team.",
  "data": {
    "kyc_id": 12,
    "status": "pending",
    "document_type": "nid",
    "document_type_label": "National ID Card (NID)",
    "full_name": "Ayeena Khan",
    "document_number": "5523910291",
    "date_of_birth": "1998-05-15",
    "front_image_url": "https://your-domain.com/uploads/kyc/kyc_front_2_1787821000_abc.jpg",
    "back_image_url": "https://your-domain.com/uploads/kyc/kyc_back_2_1787821000_def.jpg",
    "selfie_image_url": "https://your-domain.com/uploads/kyc/kyc_selfie_2_1787821000_ghi.jpg",
    "face_video_url": "https://your-domain.com/uploads/kyc/kyc_video_2_1787821000_jkl.mp4",
    "submitted_at": "2026-08-27T10:00:00Z"
  }
}
```

---

### **E. Check Verification Status & Verified Badge API**
`GET /api/kyc/status` *(Aliases: `/api/profile/kyc`, `/kyc/status`)*

**Response (200 OK):**
```json
{
  "status": true,
  "message": "KYC verification status retrieved successfully.",
  "data": {
    "user_id": 2,
    "account_id": "CHIN1082",
    "display_name": "Ayeena Khan",
    "is_verified": true,
    "kyc_status": "approved",
    "latest_submission": {
      "id": 12,
      "status": "approved",
      "front_image_url": "https://your-domain.com/uploads/kyc/kyc_front_2_1787821000_abc.jpg",
      "face_video_url": "https://your-domain.com/uploads/kyc/kyc_video_2_1787821000_jkl.mp4"
    },
    "badge": {
      "text": "Verified",
      "verified": true,
      "icon": "check-circle",
      "color": "#3b82f6"
    }
  }
}
```

---

### **F. Biometric Face Re-Unlock for Blocked Accounts**
`POST /api/kyc/face/unlock` *(Aliases: `/api/auth/face-unlock`, `/kyc/face/unlock`)*

**Request Parameters:**
- `image` / `face` / `video`: live face capture
- `phone` / `email` / `account_id` / `user_id`: user identifier

**Response (200 OK):**
```json
{
  "status": true,
  "message": "Face verification matched! Your account has been successfully unlocked.",
  "data": {
    "user_id": 2,
    "account_id": "CHIN1082",
    "display_name": "Ayeena Khan",
    "is_locked": false,
    "is_active": true,
    "is_verified": true,
    "confidence": 0.99,
    "instruction_en": "Account unlocked successfully! Welcome back.",
    "instruction_bn": "ফেস ভেরিফিকেশন সফল! আপনার একাউন্ট আনলক করা হয়েছে।"
  }
}
```

---

## 📱 Complete Flutter / Dart Integration Code

You can directly use this `KycApiService` in your Flutter app:

```dart
import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;

class KycApiService {
  final String baseUrl = "https://your-domain.com/api";

  /// 1. Run AI Quality & Face Pre-check
  Future<Map<String, dynamic>> runAiQualityPreCheck({
    required String token,
    File? frontImage,
    File? selfieImage,
  }) async {
    var uri = Uri.parse('$baseUrl/kyc/ai-detect');
    var request = http.MultipartRequest('POST', uri);

    request.headers['Authorization'] = 'Bearer $token';
    request.headers['Accept'] = 'application/json';

    if (frontImage != null) {
      request.files.add(await http.MultipartFile.fromPath('front_image', frontImage.path));
    }
    if (selfieImage != null) {
      request.files.add(await http.MultipartFile.fromPath('selfie_image', selfieImage.path));
    }

    var streamedResponse = await request.send();
    var response = await http.Response.fromStream(streamedResponse);
    return jsonDecode(response.body);
  }

  /// 2. Binance-Style Live Video Face Scan Upload
  Future<Map<String, dynamic>> uploadLiveFaceVideoScan({
    required String token,
    required File videoFile,
  }) async {
    var uri = Uri.parse('$baseUrl/kyc/video-verify');
    var request = http.MultipartRequest('POST', uri);

    request.headers['Authorization'] = 'Bearer $token';
    request.headers['Accept'] = 'application/json';

    request.files.add(await http.MultipartFile.fromPath('video', videoFile.path));

    var streamedResponse = await request.send();
    var response = await http.Response.fromStream(streamedResponse);
    return jsonDecode(response.body);
  }

  /// 3. Submit Full KYC with Document & Face Video/Selfie
  Future<Map<String, dynamic>> submitFullKyc({
    required String token,
    required String fullName,
    required String documentNumber,
    required String dob,
    required String documentType, // 'nid', 'passport', 'birth_certificate'
    required File frontImage,
    File? backImage,
    required File selfieImage,
    File? faceVideoFile,
    String? userNotes,
  }) async {
    var uri = Uri.parse('$baseUrl/kyc/submit');
    var request = http.MultipartRequest('POST', uri);

    request.headers['Authorization'] = 'Bearer $token';
    request.headers['Accept'] = 'application/json';

    request.fields['document_type'] = documentType;
    request.fields['full_name'] = fullName;
    request.fields['document_number'] = documentNumber;
    request.fields['date_of_birth'] = dob;
    if (userNotes != null) {
      request.fields['user_notes'] = userNotes;
    }

    request.files.add(await http.MultipartFile.fromPath('front_image', frontImage.path));
    if (backImage != null) {
      request.files.add(await http.MultipartFile.fromPath('back_image', backImage.path));
    }
    request.files.add(await http.MultipartFile.fromPath('selfie_image', selfieImage.path));
    if (faceVideoFile != null) {
      request.files.add(await http.MultipartFile.fromPath('face_video', faceVideoFile.path));
    }

    var streamedResponse = await request.send();
    var response = await http.Response.fromStream(streamedResponse);
    return jsonDecode(response.body);
  }

  /// 4. Biometric Face Re-Unlock for Blocked Account
  Future<Map<String, dynamic>> unlockAccountWithFace({
    required String accountIdentifier, // phone, email, or account_id
    required File liveFaceFile,
  }) async {
    var uri = Uri.parse('$baseUrl/kyc/face/unlock');
    var request = http.MultipartRequest('POST', uri);

    request.headers['Accept'] = 'application/json';
    request.fields['phone'] = accountIdentifier;
    request.files.add(await http.MultipartFile.fromPath('image', liveFaceFile.path));

    var streamedResponse = await request.send();
    var response = await http.Response.fromStream(streamedResponse);
    return jsonDecode(response.body);
  }

  /// 5. Get current KYC Status & Verified state
  Future<Map<String, dynamic>> getKycStatus(String token) async {
    var response = await http.get(
      Uri.parse('$baseUrl/kyc/status'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );
    return jsonDecode(response.body);
  }
}
```

---

## 🛡️ 3. Admin Verification Workflow

- **Admin KYC Dashboard**: `https://your-domain.com/admin/kyc`
- **Features**:
  - Live HTML5 Video Player with controls to watch the recorded face scan.
  - High-resolution zoom inspection for Document Front and Back.
  - One-click **Approve & Grant Verified Badge** button (activates blue checkmark on profile and live feed).
  - One-click **Reject with custom feedback** modal.
  - User Account Lock/Unlock toggle.
