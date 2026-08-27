# Chinchins Live — KYC Identity Verification & Streamer Badge API Documentation

This documentation provides comprehensive specifications for the **KYC Identity Verification System (NID Card, Passport, Birth Certificate)**, **AI-Guided Face Liveness / Selfie Verification**, and **Admin Approval Flow with Verified Badge Integration** for Mobile App Developers (Flutter / Android / iOS / Web).

---

## 🌐 Base URL
```http
Production:  https://your-domain.com/api
Development: http://127.0.0.1:8000/api
```

---

## 🔑 Authentication Architecture
- **Bearer Token**: `Authorization: Bearer <AUTH_TOKEN>`
- **Fallback Support**: `X-User-Id: <USER_ID_OR_ACCOUNT_ID>` or request parameter `user_id=<USER_ID>`
- **Headers**:
```http
Accept: application/json
Content-Type: multipart/form-data (or application/json for Base64)
Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxx
```

---

## 📑 Endpoints Matrix

| Category | Method | Endpoint | Description |
| :--- | :---: | :--- | :--- |
| **Guidelines** | `GET` | `/api/kyc/instructions` | Get supported document types, field requirements & AI face guidance |
| **Verification** | `POST` | `/api/kyc/submit` | Submit KYC verification (NID / Passport / Birth Certificate + Selfie) |
| **Verification** | `GET` | `/api/kyc/status` | Get current KYC status (`pending`, `approved`, `rejected`), submission details & badges |
| **AI Pre-check** | `POST` | `/api/kyc/ai-detect` | AI face landmark, liveness & document legibility pre-check |
| **Admin API** | `GET` | `/api/admin/kyc-verifications` | List all KYC submissions with filters (`status`, `document_type`, `search`) |
| **Admin API** | `POST` | `/api/admin/kyc-verifications/{id}/approve` | Approve KYC and activate Verified badge on profile |
| **Admin API** | `POST` | `/api/admin/kyc-verifications/{id}/reject` | Reject KYC with custom reason |

---

## 🪪 Supported Document Types & Requirements

### 1. 🪪 National ID Card (`nid` or `national_id`)
- **Full Legal Name** (`full_name`): Name exactly as printed on the NID.
- **NID Number** (`document_number`): 10, 13, or 17-digit National ID number.
- **Date of Birth** (`date_of_birth` / `dob`): `YYYY-MM-DD`.
- **Front Part Photo** (`front_image` / `front_part`): Clear photo of the front side of NID card (**Required**).
- **Back Part Photo** (`back_image` / `back_part`): Clear photo of the back side of NID card (**Required**).
- **Live Selfie with Document** (`selfie_image` / `selfie_with_doc`): Clear selfie holding the NID card (**Required**).

### 2. 🛂 International Passport (`passport`)
- **Full Legal Name** (`full_name`): Passport holder's official name.
- **Passport Number** (`document_number`): Valid passport number (e.g. `A01234567`).
- **Date of Birth** (`date_of_birth` / `dob`): `YYYY-MM-DD`.
- **Bio-data Page Photo** (`front_image` / `document_image`): Photo or screenshot of the main bio-data page (**Required**).
- **Back Page Photo** (`back_image`): Optional.
- **Live Selfie with Passport** (`selfie_image` / `selfie_with_doc`): Selfie holding the open passport (**Required**).

### 3. 📜 Birth Certificate (`birth_certificate` or `dob_certificate`)
- **Full Legal Name** (`full_name`): Registered legal name on birth certificate.
- **Certificate Number** (`document_number`): 17-digit online birth registration number.
- **Date of Birth** (`date_of_birth` / `dob`): `YYYY-MM-DD`.
- **Certificate Photo** (`front_image` / `document_image`): Clear photo or digital screenshot of the certificate (**Required**).
- **Live Selfie with Document** (`selfie_image` / `selfie_with_doc`): Selfie holding the birth certificate (**Required**).

---

## 🤖 AI Face Liveness & Camera Instructions

When capturing the live selfie or video verification in the mobile app, follow these steps:
1. **Lighting**: Ensure face and document are well-lit without glare or heavy shadows.
2. **Step 1 (Center)**: Look directly into the camera at eye level.
3. **Step 2 (Left Pose)**: Turn head slightly to the left (15°–30°).
4. **Step 3 (Right Pose)**: Turn head slightly to the right (15°–30°).
5. **Step 4 (Blink / Smile)**: Blink naturally to ensure live human detection.
6. **No Filters / Obstructions**: Do not wear sunglasses, caps, or face masks.

---

## 1. 📖 KYC Instructions & Guidelines API

Returns the complete specifications, required fields for each document, and liveness instructions.

### **Endpoint**
`GET /api/kyc/instructions` *(or `GET /api/kyc/guidelines`)*

### **Success Response (200 OK)**
```json
{
  "status": true,
  "message": "KYC verification instructions & document guidelines.",
  "data": {
    "supported_documents": [
      {
        "type": "nid",
        "title": "National ID Card (NID)",
        "description": "Official Government National Identity Card.",
        "required_fields": [
          "full_name",
          "document_number",
          "date_of_birth",
          "front_image",
          "back_image",
          "selfie_image"
        ],
        "front_part_guide": "Clear, glare-free photo of the front side of your NID Card with photo and name visible.",
        "back_part_guide": "Clear photo of the back side of your NID Card with address and barcode visible.",
        "selfie_guide": "Take a clear selfie holding your NID card close to your chest/face without blocking your face."
      },
      {
        "type": "passport",
        "title": "International Passport",
        "description": "Valid government-issued international travel passport.",
        "required_fields": [
          "full_name",
          "document_number",
          "date_of_birth",
          "front_image",
          "selfie_image"
        ],
        "front_part_guide": "High-resolution photo or screenshot of the main bio-data page (with photo, MRZ code and passport number).",
        "back_part_guide": "Optional for passport.",
        "selfie_guide": "Take a selfie holding your open passport bio-data page clearly visible."
      },
      {
        "type": "birth_certificate",
        "title": "Birth Certificate (জন্ম নিবন্ধন)",
        "description": "Official 17-digit Online Birth Registration Certificate.",
        "required_fields": [
          "full_name",
          "document_number",
          "date_of_birth",
          "front_image",
          "selfie_image"
        ],
        "front_part_guide": "Clear photo or digital screenshot of the full birth certificate document.",
        "back_part_guide": "Optional.",
        "selfie_guide": "Take a selfie holding the birth certificate document clearly."
      }
    ],
    "ai_liveness_guidelines": {
      "lighting": "Ensure your room is well-lit without direct glare or shadows on your face or document.",
      "face_orientation": {
        "step_1": "Look straight into the camera at eye level.",
        "step_2": "Turn your head slightly to the left when prompted.",
        "step_3": "Turn your head slightly to the right when prompted.",
        "step_4": "Blink naturally or smile to verify live human presence."
      },
      "rules": [
        "No sunglasses, hats, masks, or filters allowed.",
        "All four corners of the identity card/document must be visible.",
        "Text and dates on the document must be sharp, legible, and unblurred.",
        "Selfie face must match the face on the identity document."
      ]
    }
  }
}
```

---

## 2. 📤 Submit KYC Verification API

Allows users to upload their official documents and selfie for admin review. Once submitted, status becomes `pending`.

### **Endpoint**
`POST /api/kyc/submit` *(or `POST /api/profile/kyc/submit`, `POST /api/kyc/verification/submit`)*

### **Request Parameters (Multipart Form-data or JSON)**

| Parameter | Type | Required | Description |
| :--- | :---: | :---: | :--- |
| `document_type` | string | Optional | `nid` (default), `passport`, `birth_certificate` |
| `full_name` | string | **Yes** | Full legal name as printed on the document |
| `document_number` | string | **Yes** | NID number, Passport number, or Birth Certificate number |
| `date_of_birth` | string | Optional | Date of birth format `YYYY-MM-DD` (alias: `dob`) |
| `front_image` | file / base64 | **Yes** | Front photo / bio-data page of document |
| `back_image` | file / base64 | **Yes (NID)** | Back photo of NID Card (optional for passport/birth cert) |
| `selfie_image` | file / base64 | **Yes** | Selfie holding the document or live facial scan |
| `liveness_data` | json / array | Optional | Camera orientation metadata (`center`, `left`, `right`) |
| `user_notes` | string | Optional | Any optional note from the user |

### **Example Request (cURL Multipart)**
```bash
curl -X POST "http://127.0.0.1:8000/api/kyc/submit" \
  -H "Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxx" \
  -H "Accept: application/json" \
  -F "document_type=nid" \
  -F "full_name=Ayeena Khan" \
  -F "document_number=5523910291" \
  -F "date_of_birth=1998-05-15" \
  -F "front_image=@/path/to/nid_front.jpg" \
  -F "back_image=@/path/to/nid_back.jpg" \
  -F "selfie_image=@/path/to/selfie_with_nid.jpg"
```

### **Success Response (201 Created)**
```json
{
  "status": true,
  "message": "KYC verification submitted successfully. It is currently under review by our admin team.",
  "data": {
    "kyc_id": 4,
    "status": "pending",
    "document_type": "nid",
    "document_type_label": "National ID Card (NID)",
    "full_name": "Ayeena Khan",
    "document_number": "5523910291",
    "date_of_birth": "1998-05-15",
    "front_image_url": "https://your-domain.com/uploads/kyc/kyc_front_2_1787818013_OPsDxR.jpg",
    "back_image_url": "https://your-domain.com/uploads/kyc/kyc_back_2_1787818013_NrQ7Ew.jpg",
    "selfie_image_url": "https://your-domain.com/uploads/kyc/kyc_selfie_2_1787818013_dfpckX.jpg",
    "submitted_at": "2026-08-27T08:06:53Z",
    "user": {
      "id": 2,
      "account_id": "6022816358",
      "display_name": "Ayeena Khan",
      "is_verified": false,
      "kyc_status": "pending"
    }
  }
}
```

---

## 3. 🔍 KYC Verification Status & Badge API

Retrieves the authenticated user's current identity verification status, submission history, and UI badge attributes.

### **Endpoint**
`GET /api/kyc/status` *(or `GET /api/profile/kyc`, `GET /api/kyc/verification/status`)*

### **Success Response (200 OK)**
```json
{
  "status": true,
  "message": "KYC verification status retrieved successfully.",
  "data": {
    "user_id": 2,
    "account_id": "6022816358",
    "display_name": "Ayeena Khan",
    "is_verified": true,
    "kyc_status": "approved",
    "latest_submission": {
      "id": 4,
      "user_id": 2,
      "document_type": "nid",
      "full_name": "Ayeena Khan",
      "document_number": "5523910291",
      "date_of_birth": "1998-05-15",
      "front_image_url": "https://your-domain.com/uploads/kyc/kyc_front_2_1787818013_OPsDxR.jpg",
      "back_image_url": "https://your-domain.com/uploads/kyc/kyc_back_2_1787818013_NrQ7Ew.jpg",
      "selfie_image_url": "https://your-domain.com/uploads/kyc/kyc_selfie_2_1787818013_dfpckX.jpg",
      "status": "approved",
      "reviewed_at": "2026-08-27T08:15:00Z"
    },
    "submission_history": [
      {
        "id": 4,
        "document_type": "nid",
        "status": "approved",
        "submitted_at": "2026-08-27T08:06:53Z"
      }
    ],
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

## 4. 🤖 AI Pre-Detection Inspection Endpoint

Inspects image quality, face centering, liveness, and document readability prior to final submission.

### **Endpoint**
`POST /api/kyc/ai-detect`

### **Request Parameters**
- `front_image` (file or base64)
- `selfie_image` (file or base64)

### **Success Response (200 OK)**
```json
{
  "status": true,
  "message": "AI face and document detection check passed successfully.",
  "data": {
    "face_detected": true,
    "face_centered": true,
    "eyes_open": true,
    "lighting_score": 0.95,
    "blur_score": 0.08,
    "glare_detected": false,
    "document_corners": 4,
    "text_legibility": "excellent",
    "liveness_confidence": 0.99,
    "status": "PASSED"
  }
}
```

---

## 5. 🛡️ Admin Verification Workflow & Endpoints

### **A. Web Admin Dashboard**
- URL: `https://your-domain.com/admin/kyc`
- Features:
  - **Metrics**: Pending Reviews, Verified Streamers, Total Submissions, Rejected Submissions.
  - **Filter Tabs**: All, Pending, Approved, Rejected, and Document Type filter (NID, Passport, Birth Cert).
  - **High-Resolution Inspection Modal**: Zoomable front image, back image, and selfie with document.
  - **Approve Button**: Marks KYC as `approved`, activates `is_verified = true` on the user account, and displays the blue Verified badge next to Online on the homepage and profile.
  - **Reject Button**: Opens rejection modal to supply custom feedback reason.

### **B. Admin REST APIs**
- **List KYC Submissions**: `GET /api/admin/kyc-verifications?status=pending`
- **Approve Submission**: `POST /api/admin/kyc-verifications/{id}/approve`
- **Reject Submission**: `POST /api/admin/kyc-verifications/{id}/reject` (Body: `{"rejection_reason": "Image was blurred"}`)

---

## 📱 Flutter / Dart Integration Example

```dart
import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;

class KycService {
  final String baseUrl = "https://your-domain.com/api";

  /// Submit NID Verification with Images
  Future<Map<String, dynamic>> submitNidKyc({
    required String token,
    required String fullName,
    required String nidNumber,
    required String dob,
    required File frontImage,
    required File backImage,
    required File selfieImage,
  }) async {
    var uri = Uri.parse('$baseUrl/kyc/submit');
    var request = http.MultipartRequest('POST', uri);

    request.headers['Authorization'] = 'Bearer $token';
    request.headers['Accept'] = 'application/json';

    request.fields['document_type'] = 'nid';
    request.fields['full_name'] = fullName;
    request.fields['document_number'] = nidNumber;
    request.fields['date_of_birth'] = dob;

    request.files.add(await http.MultipartFile.fromPath('front_image', frontImage.path));
    request.files.add(await http.MultipartFile.fromPath('back_image', backImage.path));
    request.files.add(await http.MultipartFile.fromPath('selfie_image', selfieImage.path));

    var streamedResponse = await request.send();
    var response = await http.Response.fromStream(streamedResponse);

    return jsonDecode(response.body);
  }

  /// Get current KYC Status & Verified state
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
