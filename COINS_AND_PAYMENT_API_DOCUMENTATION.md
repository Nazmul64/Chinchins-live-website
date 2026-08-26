# Chinchins Live — Coins, Video Calling & Manual Deposit (bKash/Nagad) API Documentation

This documentation provides comprehensive specifications for the **Coins & Balance Economy**, **Video Calling Rate Deductions (100 Coins = 1 Minute)**, **Payment Methods (bKash, Nagad, etc.)**, and **Manual Deposit System** for Mobile App Developers (Flutter / Android / iOS / Web).

---

## 🌐 Base URL
```http
Production:  https://your-domain.com/api
Development: http://127.0.0.1:8000/api
```

---

## 🔑 Authentication Architecture
- **Bearer Token**: `Authorization: Bearer <AUTH_TOKEN>`
- **Fallback Support**: `X-User-Id: <USER_ID_OR_ACCOUNT_ID>` or parameter `user_id=<USER_ID>`
- **Headers**:
```http
Accept: application/json
Content-Type: application/json
Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxx
```

---

## 📑 Endpoints Matrix

| Category | Method | Endpoint | Description |
| :--- | :---: | :--- | :--- |
| **Wallet** | `GET` | `/api/wallet/balance` | Get current coin balance, call rate & max call minutes |
| **Wallet** | `GET` | `/api/wallet/transactions` | Full ledger of user's coin earnings, spendings & deposits |
| **Payment** | `GET` | `/api/payment-methods` | List active payment accounts (bKash, Nagad, etc.) |
| **Payment** | `GET` | `/api/coin-packages` | List coin pricing tiers & packages |
| **Deposit** | `POST` | `/api/deposit/request` | Submit manual deposit with TrxID, sender number & screenshot |
| **Deposit** | `GET` | `/api/deposit/history` | List deposit request history & statuses (`pending`, `approved`, `rejected`) |
| **Video Call** | `POST` | `/api/call/initiate` | Check balance (min 100 coins) and initiate call session |
| **Video Call** | `POST` | `/api/call/start` | Mark call as connected/started |
| **Video Call** | `POST` | `/api/call/end` | End call, calculate duration, deduct coins (100 coins/min) |
| **Video Call** | `POST` | `/api/call/deduct-interval` | Real-time interval deduction (e.g. 100 coins every 60s) |
| **Video Call** | `GET` | `/api/call/history` | Call log history & coins spent |

---

## 1. 💰 Wallet Balance API

Returns the authenticated user's current coin balance and calculated maximum video call duration.

### **Endpoint**
`GET /api/wallet/balance` *(or `GET /api/coins/balance`)*

### **Success Response (200 OK)**
```json
{
  "status": true,
  "message": "Wallet balance retrieved successfully.",
  "data": {
    "user_id": 1,
    "account_id": "6022816358",
    "name": "Ayeena Khan",
    "coins": 2500,
    "call_rate_per_minute": 100,
    "max_call_minutes": 25,
    "avatar_url": "http://127.0.0.1:8000/uploads/profiles/avatar_1.jpg"
  }
}
```

---

## 2. 💳 Payment Methods API (bKash, Nagad, Rocket, Bank)

Returns all active payment accounts configured by the Admin, including numbers, instructions, and limits.

### **Endpoint**
`GET /api/payment-methods`

### **Success Response (200 OK)**
```json
{
  "status": true,
  "message": "Payment methods retrieved successfully.",
  "data": [
    {
      "id": 1,
      "name": "bKash Personal",
      "code": "bkash",
      "account_type": "Personal",
      "account_number": "01700000000",
      "instructions": "1. Go to bKash App or dial *247#\n2. Select 'Send Money'\n3. Enter Number: 01700000000\n4. Copy Transaction ID (TrxID) and enter below.",
      "icon": "https://raw.githubusercontent.com/Nazmul64/assets/main/bkash.png",
      "qr_code": null,
      "min_deposit": 50,
      "max_deposit": 25000,
      "rate_per_bdt": 10,
      "example": "100 BDT = 1000 Coins"
    },
    {
      "id": 2,
      "name": "Nagad Personal",
      "code": "nagad",
      "account_type": "Personal",
      "account_number": "01800000000",
      "instructions": "1. Open Nagad App\n2. Select 'Send Money'\n3. Enter Number: 01800000000\n4. Copy TxnID and enter below.",
      "icon": "https://raw.githubusercontent.com/Nazmul64/assets/main/nagad.png",
      "qr_code": null,
      "min_deposit": 50,
      "max_deposit": 25000,
      "rate_per_bdt": 10,
      "example": "100 BDT = 1000 Coins"
    }
  ]
}
```

---

## 3. 📦 Coin Packages API

Returns recommended deposit tiers for the app's coin store.

### **Endpoint**
`GET /api/coin-packages`

### **Success Response (200 OK)**
```json
{
  "status": true,
  "message": "Coin packages retrieved successfully.",
  "data": [
    { "id": 1, "bdt": 50, "coins": 500, "popular": false, "title": "Starter Pack" },
    { "id": 2, "bdt": 100, "coins": 1000, "popular": true, "title": "Basic Pack" },
    { "id": 3, "bdt": 200, "coins": 2100, "popular": false, "bonus": "100 Extra Coins", "title": "Standard Pack" },
    { "id": 4, "bdt": 500, "coins": 5500, "popular": true, "bonus": "500 Extra Coins", "title": "Popular Pack" },
    { "id": 5, "bdt": 1000, "coins": 11500, "popular": false, "bonus": "1,500 Extra Coins", "title": "VIP Pack" }
  ]
}
```

---

## 4. 📥 Submit Manual Deposit Request API

Allows users to submit their payment details after sending money via bKash, Nagad, etc.

### **Endpoint**
`POST /api/deposit/request` *(or `POST /api/wallet/deposit`)*  
**Content-Type**: `multipart/form-data`

### **Request Parameters**

| Field | Type | Required | Description | Example |
| :--- | :---: | :---: | :--- | :--- |
| `payment_method_id` | `integer` | No | ID from `/api/payment-methods` | `1` |
| `payment_method` | `string` | No | Method name/code fallback | `"bkash"` |
| `amount` | `numeric` | **Yes** | Deposited amount in BDT | `500` |
| `coins` | `integer` | No | Expected coins (auto-calculated if omitted) | `5000` |
| `sender_number` | `string` | **Yes** | Phone number money was sent from | `"01712345678"` |
| `transaction_id` | `string` | **Yes** | TrxID / Transaction Reference | `"9G28KLA9"` |
| `screenshot` | `file` | No | Receipt image (jpg, png, webp, max 5MB) | `receipt.jpg` |
| `user_note` | `string` | No | Optional note from user | `"Sent via bKash App"` |

### **Success Response (201 Created)**
```json
{
  "status": true,
  "message": "Deposit request submitted successfully! Your coins will be credited once verified by admin.",
  "data": {
    "deposit_id": 14,
    "amount": 500.00,
    "coins": 5000,
    "payment_method": "bKash Personal",
    "sender_number": "01712345678",
    "transaction_id": "9G28KLA9",
    "screenshot_url": "http://127.0.0.1:8000/uploads/deposits/deposit_1_1724660000.jpg",
    "status": "pending",
    "created_at": "2026-08-26T15:30:00+06:00"
  }
}
```

---

## 5. 📜 Deposit History API

### **Endpoint**
`GET /api/deposit/history`

### **Success Response (200 OK)**
```json
{
  "status": true,
  "message": "Deposit history retrieved successfully.",
  "data": [
    {
      "id": 14,
      "user_id": 1,
      "payment_method_name": "bKash Personal",
      "amount": "500.00",
      "coins": 5000,
      "sender_number": "01712345678",
      "transaction_id": "9G28KLA9",
      "screenshot_url": "http://127.0.0.1:8000/uploads/deposits/deposit_1_1724660000.jpg",
      "status": "approved",
      "admin_note": "Verified TrxID successfully",
      "approved_at": "2026-08-26T15:35:00.000000Z",
      "created_at": "2026-08-26T15:30:00.000000Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "total": 1
  }
}
```

---

## 6. 📞 Video Calling APIs (100 Coins = 1 Minute)

### A. Initiate Call
Checks if caller has at least **100 coins** (or the receiver's custom call rate per minute).

#### **Endpoint**
`POST /api/call/initiate`

#### **Request Body**
```json
{
  "receiver_id": 2
}
```

#### **Success Response (200 OK)**
```json
{
  "status": true,
  "message": "Call initiated successfully.",
  "data": {
    "call_id": 42,
    "channel_name": "call_1_2_1724660000_a1b2",
    "rate_per_minute": 100,
    "caller_coins": 2500,
    "max_call_minutes": 25,
    "max_call_seconds": 1500,
    "receiver": {
      "id": 2,
      "account_id": "9182736450",
      "name": "Sarah Ahmed",
      "avatar": "http://127.0.0.1:8000/uploads/profiles/avatar_2.jpg"
    }
  }
}
```

#### **Insufficient Balance Error (402 Payment Required)**
```json
{
  "status": false,
  "message": "Insufficient coin balance. You need at least 100 coins for 1 minute of video call. Your balance is 40 coins.",
  "current_coins": 40,
  "required_coins": 100,
  "is_low_balance": true
}
```

---

### B. Start / Connect Call
Call this when receiver accepts the video call.

#### **Endpoint**
`POST /api/call/start`

#### **Request Body**
```json
{
  "call_id": 42
}
```

#### **Success Response (200 OK)**
```json
{
  "status": true,
  "message": "Call connected.",
  "data": {
    "call_id": 42,
    "channel_name": "call_1_2_1724660000_a1b2",
    "status": "connected",
    "started_at": "2026-08-26T15:30:10.000000Z",
    "rate_per_minute": 100
  }
}
```

---

### C. End Call & Deduct Coins
Call this when the video call terminates. Automatically calculates duration and deducts **100 coins per minute** (rounded up).

- `1 to 60 seconds` = 1 minute (100 coins)
- `61 to 120 seconds` = 2 minutes (200 coins)
- `121 to 180 seconds` = 3 minutes (300 coins)

#### **Endpoint**
`POST /api/call/end`

#### **Request Body**
```json
{
  "call_id": 42,
  "duration_seconds": 135
}
```

#### **Success Response (200 OK)**
```json
{
  "status": true,
  "message": "Call ended successfully.",
  "data": {
    "call_id": 42,
    "duration_seconds": 135,
    "duration_formatted": "02:15",
    "rate_per_minute": 100,
    "coins_deducted": 300,
    "caller_remaining_coins": 2200
  }
}
```

---

### D. In-Call Interval Pulse Deduction (Optional Real-Time Heartbeat)
For live real-time streams or continuous calls, the mobile app can pulse this endpoint every 60 seconds to deduct 100 coins incrementally.

#### **Endpoint**
`POST /api/call/deduct-interval`

#### **Request Body**
```json
{
  "call_id": 42,
  "coins": 100
}
```

#### **Success Response (200 OK)**
```json
{
  "status": true,
  "message": "Deducted 100 coins for ongoing call.",
  "data": {
    "current_coins": 2400,
    "coins_deducted": 100,
    "total_call_coins_deducted": 100,
    "can_continue": true
  }
}
```

---

## 7. 📜 Coin Transactions Ledger API

Returns paginated ledger entries of all user coin activity.

### **Endpoint**
`GET /api/wallet/transactions` *(or `GET /api/coins/transactions`)*

### **Success Response (200 OK)**
```json
{
  "status": true,
  "message": "Transactions retrieved successfully.",
  "data": [
    {
      "id": 101,
      "user_id": 1,
      "type": "deposit",
      "amount": 5000,
      "balance_after": 5000,
      "description": "Deposit via bKash Personal (TrxID: 9G28KLA9)",
      "reference_id": "deposit_#14",
      "created_at": "2026-08-26T15:35:00.000000Z"
    },
    {
      "id": 102,
      "user_id": 1,
      "type": "video_call_spent",
      "amount": -300,
      "balance_after": 4700,
      "description": "Video call with Sarah Ahmed (135s / 3 min)",
      "reference_id": "call_#42",
      "created_at": "2026-08-26T15:40:00.000000Z"
    }
  ],
  "current_coins": 4700,
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "total": 2
  }
}
```

---

## 💡 Flutter / Dart Integration Example

```dart
// 1. Submit Deposit Request with Screenshot Proof
Future<void> submitDeposit({
  required int paymentMethodId,
  required double amount,
  required String senderNumber,
  required String transactionId,
  File? screenshotFile,
}) async {
  var request = http.MultipartRequest(
    'POST',
    Uri.parse('https://your-domain.com/api/deposit/request'),
  );

  request.headers['Authorization'] = 'Bearer $userAuthToken';
  request.fields['payment_method_id'] = paymentMethodId.toString();
  request.fields['amount'] = amount.toString();
  request.fields['sender_number'] = senderNumber;
  request.fields['transaction_id'] = transactionId;

  if (screenshotFile != null) {
    request.files.add(
      await http.MultipartFile.fromPath('screenshot', screenshotFile.path),
    );
  }

  var response = await request.send();
  var responseData = await http.Response.fromStream(response);
  print(responseData.body);
}

// 2. Video Call Lifecycle with 100 Coins / Minute
Future<void> startVideoCall(int receiverId) async {
  // Step 1: Check balance and initiate
  final initRes = await http.post(
    Uri.parse('https://your-domain.com/api/call/initiate'),
    headers: {'Authorization': 'Bearer $userAuthToken', 'Content-Type': 'application/json'},
    body: jsonEncode({'receiver_id': receiverId}),
  );

  if (initRes.statusCode == 402) {
    // Show 'Low Coin Balance' Modal & Navigate to Deposit Screen
    print("Insufficient coins. Please deposit.");
    return;
  }

  final callData = jsonDecode(initRes.body)['data'];
  final int callId = callData['call_id'];

  // Step 2: When connected
  await http.post(
    Uri.parse('https://your-domain.com/api/call/start'),
    headers: {'Authorization': 'Bearer $userAuthToken', 'Content-Type': 'application/json'},
    body: jsonEncode({'call_id': callId}),
  );

  // Step 3: When call finishes
  await http.post(
    Uri.parse('https://your-domain.com/api/call/end'),
    headers: {'Authorization': 'Bearer $userAuthToken', 'Content-Type': 'application/json'},
    body: jsonEncode({
      'call_id': callId,
      'duration_seconds': 120, // Total seconds
    }),
  );
}
```
