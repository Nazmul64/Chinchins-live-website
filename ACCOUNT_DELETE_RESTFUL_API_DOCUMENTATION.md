# ChinChins Live - Account Deletion & Lifecycle Management RESTful API Documentation

This document outlines the complete specifications for **Admin-Initiated User Account Deletion, Mobile App Self Account Deletion (App Store/Play Store Compliance), and Deleted Account Login Interception**.

---

## 📌 Architecture & Lifecycle Rules

1. **Admin Deletion (`/admin/users`):**
   - Admin can click the **Delete** button on any user row in the Users & Balance directory.
   - Instantly revokes all active authentication tokens (`Sanctum`).
   - Soft-deletes the user profile with audit tracking (`deleted_at`, `deleted_reason`, `deleted_by`).
   - Frees up phone/email identifiers so the person can register a fresh account later if desired.

2. **Login Interception for Deleted Accounts:**
   - If a deleted user attempts to log in via mobile app using Email, Phone, or Account ID, the backend detects the deleted state and responds with:
     ```json
     {
       "status": false,
       "is_deleted": true,
       "message": "Your account has been deleted by administration. Please register a new account to continue.",
       "action": "register_new"
     }
     ```
   - The Flutter mobile app can display a popup dialog and redirect the user directly to the Registration screen.

3. **In-App Self-Deletion (`App Store / Google Play Store Compliance`):**
   - End-users can permanently delete their account from the App Settings screen via `POST /api/user/delete-account`.

---

## 🔑 Authentication & Headers

```http
Authorization: Bearer <user_sanctum_token>
Content-Type: application/json
Accept: application/json
```

---

## 🚪 API 1: User Login (with Deleted Account Check)

Attempts user login. If account has been deleted, returns HTTP `403 Forbidden` with `"is_deleted": true`.

- **Method:** `POST`
- **Endpoint:** `/api/login`
- **Authentication:** None (Public)

### Request Payload:
```json
{
  "identifier": "01706640868",
  "password": "password123"
}
```
*(Accepts `email`, `phone`, `account_id`, or `identifier`)*

---

### Responses:

#### 1. Success (`200 OK`):
```json
{
  "status": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 42,
      "account_id": "53895291",
      "name": "Nazmul Hossain",
      "phone": "+8801706640868",
      "email": "user@chinchins.live",
      "coins": 0,
      "is_active": true
    },
    "token": "12|xK9Yp...plainTextToken",
    "token_type": "Bearer"
  }
}
```

#### 2. Account Deleted by Admin (`403 Forbidden`):
```json
{
  "status": false,
  "is_deleted": true,
  "message": "Your account has been deleted by administration. Please register a new account to continue.",
  "action": "register_new"
}
```

#### 3. Account Blocked / Locked (`403 Forbidden`):
```json
{
  "status": false,
  "is_locked": true,
  "message": "Your account has been blocked: Community guidelines violation. Please contact administration."
}
```

#### 4. Invalid Password / Credentials (`401 Unauthorized`):
```json
{
  "status": false,
  "message": "Invalid email/phone/account ID or password"
}
```

---

## 🗑️ API 2: User Self-Delete Account (Mobile App)

Allows an authenticated mobile app user to permanently delete their account.

- **Method:** `POST` or `DELETE`
- **Endpoints:**
  - `POST /api/user/delete-account`
  - `DELETE /api/user/account`
  - `POST /api/user/account/delete`
- **Authentication:** Bearer Token (Required)

### Request Payload:
```json
{
  "reason": "I no longer wish to use this account"
}
```

### Response (`200 OK`):
```json
{
  "status": true,
  "is_deleted": true,
  "message": "Your account has been deleted successfully. You may register a new account anytime."
}
```

---

## 🛠️ API 3: Admin Delete User Account

Admin endpoint triggered via web dashboard or API.

- **Method:** `POST` or `DELETE`
- **Endpoints:**
  - `POST /admin/users/{id}/delete`
  - `DELETE /admin/users/{id}`
- **Authentication:** Admin Session / Auth Token

### Request Parameters:
- `id`: User primary ID (in URL path)
- `reason` *(optional)*: Audit reason for deletion

### Response (`200 OK`):
```json
{
  "status": true,
  "message": "User Nazmul Hossain has been successfully deleted."
}
```

---

## 📱 Flutter Implementation Example

### Handling Deleted Account During Login in Flutter

```dart
Future<void> loginUser(String identifier, String password, BuildContext context) async {
  final response = await http.post(
    Uri.parse('https://chinchins.live/api/login'),
    headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
    body: jsonEncode({
      'identifier': identifier,
      'password': password,
    }),
  );

  final data = jsonDecode(response.body);

  if (response.statusCode == 200 && data['status'] == true) {
    // Login Success -> Navigate to Home / Stream
    String token = data['data']['token'];
    saveToken(token);
    Navigator.pushReplacementNamed(context, '/home');
  } else if (response.statusCode == 403 && data['is_deleted'] == true) {
    // Account is Deleted -> Show Alert & Guide to Register
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Account Deleted', style: TextStyle(fontWeight: FontWeight.bold)),
        content: Text(data['message'] ?? 'Your account has been deleted. Please register a new account.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFFE11D48)),
            onPressed: () {
              Navigator.pop(ctx);
              Navigator.pushReplacementNamed(context, '/register');
            },
            child: const Text('Register New Account', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  } else {
    // Show normal error toast
    showToast(data['message'] ?? 'Login failed');
  }
}
```

---

## 💾 Database Audit & Migration

```sql
ALTER TABLE users ADD COLUMN deleted_at TIMESTAMP NULL;
ALTER TABLE users ADD COLUMN deleted_reason VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN deleted_by VARCHAR(50) NULL DEFAULT 'admin';
```
