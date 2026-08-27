# 🪪 Chinchins Live — KYC Identity Verification API Documentation

This documentation provides the complete technical specification for the **KYC Identity Verification System (National ID, Passport, Birth Certificate)** and **Admin Verification Flow with Verified Badge Integration** for Mobile App Developers (Flutter / Android / iOS) and Backend Administrators.

---

## ⚡ Summary of KYC Workflow

The KYC system is clean, fast, and secure:
1. **Select Document Type**: National ID Card (`nid`), International Passport (`passport`), or Birth Certificate (`birth_certificate`).
2. **User Info**: Full Legal Name (`full_name`), Document Number (`document_number`), Date of Birth (`date_of_birth`).
3. **Document Photos**:
   - **Front Part Photo** (`front_image`) — **Required**.
   - **Back Part Photo** (`back_image`) — **Required for NID**, optional for passport.
4. **1 Single Live Selfie / Selfie with Document** (`selfie_image`) — **Required**: 1 clear selfie holding the identity card.
5. **No 4-angle turns or live video required**: Removed multi-angle gestures (Left, Right, Blink) and face video to ensure maximum upload reliability, zero payload size errors, and fast submission!

---

## 🌐 Base URL & Endpoints

```http
Production:  https://your-domain.com/api
Development: http://127.0.0.1:8000/api
```

> **Note**: For mobile app flexibility, all routes work both **with** `/api/` (e.g. `/api/kyc/submit`) and **without** `/api/` (e.g. `/kyc/submit`). All endpoints strictly return pure JSON `application/json` responses.

---

## 🔑 Authentication Architecture

- **Primary**: `Authorization: Bearer <AUTH_TOKEN>`
- **Fallback**: `X-User-Id: <USER_ID>` or request parameter `user_id=<USER_ID>`
- **Headers**:
```http
Accept: application/json
Content-Type: multipart/form-data
Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxx
```

---

## 📑 Endpoints Matrix

| # | Method | Endpoint | Description |
| :---: | :---: | :--- | :--- |
| **1** | `GET` | `/api/kyc/instructions` | Get document requirements, field lists, and photo guidelines |
| **2** | `POST` | `/api/kyc/submit` | **Submit KYC verification** (NID / Passport / Birth Cert + 1 Selfie) |
| **3** | `GET` | `/api/kyc/status` | Get user's KYC verification status (`pending`, `approved`, `rejected`) and badge info |
| **4** | `POST` | `/api/kyc/ai-detect` | AI quality pre-check (lighting, blur, face detection) |
| **5** | `POST` | `/api/kyc/face/unlock` | Biometric Face Re-Unlock to automatically reactivate blocked/locked accounts |
| **6** | `GET` | `/api/admin/kyc-verifications` | Admin: List all KYC submissions with status filters |
| **7** | `POST` | `/api/admin/kyc-verifications/{id}/approve` | Admin: Approve submission and grant blue **Verified** badge |
| **8** | `POST` | `/api/admin/kyc-verifications/{id}/reject` | Admin: Reject submission with custom reason |
| **9** | `POST` | `/admin/users/{id}/toggle-lock` | Admin: Manually Lock / Unlock user account |

---

## 🪪 1. Supported Document Types & Requirements

### 1. 🪪 National ID Card (`nid`)
- **Full Legal Name** (`full_name`): Name printed on NID (**Required**).
- **NID Number** (`document_number`): 10, 13, or 17-digit National ID number (**Required**).
- **Date of Birth** (`date_of_birth` / `dob`): `YYYY-MM-DD` (**Optional / Recommended**).
- **Front Photo** (`front_image`): Clear photo of NID Front (**Required**).
- **Back Photo** (`back_image`): Clear photo of NID Back (**Required**).
- **Selfie Photo** (`selfie_image`): 1 clear selfie holding NID Card (**Required**).

### 2. 🛂 International Passport (`passport`)
- **Full Legal Name** (`full_name`): Passport holder's name (**Required**).
- **Passport Number** (`document_number`): Valid passport number (**Required**).
- **Date of Birth** (`date_of_birth`): `YYYY-MM-DD`.
- **Bio-data Page Photo** (`front_image`): Photo of passport main page (**Required**).
- **Selfie Photo** (`selfie_image`): 1 clear selfie holding open passport (**Required**).

### 3. 📜 Birth Certificate (`birth_certificate`)
- **Full Legal Name** (`full_name`): Registered legal name (**Required**).
- **Certificate Number** (`document_number`): 17-digit certificate number (**Required**).
- **Date of Birth** (`date_of_birth`): `YYYY-MM-DD`.
- **Certificate Photo** (`front_image`): Clear photo of birth certificate (**Required**).
- **Selfie Photo** (`selfie_image`): 1 clear selfie holding birth certificate (**Required**).

---

## 📤 2. Submit KYC Verification API

### **Endpoint**
`POST /api/kyc/submit` *(or `POST /kyc/submit`)*

### **Request Parameters (Multipart Form-Data)**

| Field Name | Type | Required | Description |
| :--- | :---: | :---: | :--- |
| `document_type` | string | **Yes** | `nid` (default), `passport`, `birth_certificate` |
| `full_name` | string | **Yes** | Full legal name matching document |
| `document_number` | string | **Yes** | NID / Passport / Certificate number |
| `date_of_birth` | string | Optional | Date of birth `YYYY-MM-DD` (alias: `dob`) |
| `front_image` | file | **Yes** | Document Front photo (saved in `public/uploads/kyc/`) |
| `back_image` | file | **Yes (NID)** | Document Back photo (saved in `public/uploads/kyc/`) |
| `selfie_image` | file | **Yes** | 1 Clear Selfie photo (saved in `public/uploads/kyc/`) |
| `user_notes` | string | Optional | Optional user notes |

### **Example cURL Request**
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

### **Success Response (200 OK / 201 Created)**
```json
{
  "status": true,
  "message": "KYC verification submitted successfully. It is currently under review by our admin team.",
  "data": {
    "kyc_id": 15,
    "status": "pending",
    "document_type": "nid",
    "document_type_label": "National ID Card (NID)",
    "full_name": "Ayeena Khan",
    "document_number": "5523910291",
    "date_of_birth": "1998-05-15",
    "front_image_url": "https://your-domain.com/uploads/kyc/kyc_front_2_1787823000_abc.jpg",
    "back_image_url": "https://your-domain.com/uploads/kyc/kyc_back_2_1787823000_def.jpg",
    "selfie_image_url": "https://your-domain.com/uploads/kyc/kyc_selfie_2_1787823000_ghi.jpg",
    "submitted_at": "2026-08-27T10:30:00Z",
    "user": {
      "id": 2,
      "account_id": "CHIN1082",
      "display_name": "Ayeena Khan",
      "is_verified": false,
      "kyc_status": "pending"
    }
  }
}
```

---

## 🔍 3. KYC Verification Status & Verified Badge API

### **Endpoint**
`GET /api/kyc/status` *(or `GET /kyc/status`)*

### **Success Response (200 OK)**
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
      "id": 15,
      "document_type": "nid",
      "full_name": "Ayeena Khan",
      "document_number": "5523910291",
      "front_image_url": "https://your-domain.com/uploads/kyc/kyc_front_2_1787823000_abc.jpg",
      "back_image_url": "https://your-domain.com/uploads/kyc/kyc_back_2_1787823000_def.jpg",
      "selfie_image_url": "https://your-domain.com/uploads/kyc/kyc_selfie_2_1787823000_ghi.jpg",
      "status": "approved"
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

## 📱 4. Flutter Integration Service (`kyc_api_service.dart`)

```dart
import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;

class KycApiService {
  final String baseUrl = "https://your-domain.com/api";

  /// 1. Submit Clean KYC (Front Image, Back Image, 1 Selfie Photo)
  Future<Map<String, dynamic>> submitKyc({
    required String token,
    required String fullName,
    required String documentNumber,
    required String dob, // 'YYYY-MM-DD'
    required String documentType, // 'nid', 'passport', 'birth_certificate'
    required File frontImage,
    File? backImage,
    required File selfieImage,
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
    if (userNotes != null && userNotes.isNotEmpty) {
      request.fields['user_notes'] = userNotes;
    }

    // Attach document images
    request.files.add(await http.MultipartFile.fromPath('front_image', frontImage.path));
    if (backImage != null) {
      request.files.add(await http.MultipartFile.fromPath('back_image', backImage.path));
    }
    // Attach 1 single selfie
    request.files.add(await http.MultipartFile.fromPath('selfie_image', selfieImage.path));

    var streamedResponse = await request.send();
    var response = await http.Response.fromStream(streamedResponse);
    return jsonDecode(response.body);
  }

  /// 2. Get KYC Verification Status & Verified Badge
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

  /// 3. Biometric Face Re-Unlock for Blocked / Locked Accounts
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
}
```

---

## 🛠️ Instructions for Flutter Developer:
1. **Remove from UI (`kyc_verification_screen.dart`)**:
   - Remove the 4 separate facial angle boxes ("1. Center Face", "2. Turn Left", "3. Turn Right", "4. Blink / Smile").
   - Remove live video recording button.
2. **Keep in UI**:
   - Document Type Picker (`NID Card`, `Passport`, `Birth Certificate`)
   - Full Legal Name text field
   - Document Number text field
   - Date of Birth picker
   - **Front Photo Picker**
   - **Back Photo Picker** (for NID)
   - **1 Selfie Photo Picker** (Selfie holding identity document)
   - **Submit Verification Button**
