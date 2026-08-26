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

Returns all active payment accounts configured by the Admin, including numbers, instructions, limits, and dynamic Coin-to-BDT conversion ratios.

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
      "rate_coins": 500,
      "rate_bdt": 50,
      "rate_per_bdt": 10,
      "rate_text": "500 Coins = ৳50 BDT",
      "example": "500 Coins = ৳50 BDT (1 BDT = 10 Coins)"
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
      "rate_coins": 500,
      "rate_bdt": 50,
      "rate_per_bdt": 10,
      "rate_text": "500 Coins = ৳50 BDT",
      "example": "500 Coins = ৳50 BDT (1 BDT = 10 Coins)"
    }
  ]
}
```

### **Payment Method Object Fields**
| Field | Type | Description |
| :--- | :--- | :--- |
| `id` | `integer` | Unique ID of payment method |
| `name` | `string` | Method name (e.g. `bKash Personal`, `Nagad Merchant`) |
| `code` | `string` | Identifier code (e.g. `bkash`, `nagad`, `rocket`) |
| `account_type` | `string` | `Personal`, `Agent`, `Merchant`, or `Bank Account` |
| `account_number` | `string` | Phone number / Account number to send money to |
| `instructions` | `string` | Step-by-step instructions displayed to the mobile user |
| `icon` | `string|null` | Image URL of the gateway logo |
| `qr_code` | `string|null` | Image URL of QR code (if applicable) |
| `min_deposit` | `float` | Minimum deposit amount in BDT (e.g. `50.00`) |
| `max_deposit` | `float` | Maximum deposit amount in BDT (e.g. `25000.00`) |
| `rate_coins` | `integer` | Configured Coin quantity (e.g. `500`) |
| `rate_bdt` | `float` | Configured BDT cost for the coin quantity (e.g. `50.00`) |
| `rate_per_bdt` | `float` | Multiplier coins received per 1 BDT (`rate_coins / rate_bdt`, e.g. `10`) |
| `rate_text` | `string` | Formatted summary (e.g. `"500 Coins = ৳50 BDT"`) |
| `example` | `string` | Readable conversion example for UI display |


---

## 3. 📦 Coin & Gems Packages API (Mobile Store Offers)

Returns all configured coin store tiers with base coins, promotional bonus gems, discount tags (e.g. `🔥 50% OFF`, `Best Value`), prices in BDT, and total coins.

### **Endpoint**
`GET /api/coin-packages` *(or alias `GET /api/packages`)*

### **Success Response (200 OK)**
```json
{
  "status": true,
  "message": "Coin packages retrieved successfully.",
  "data": [
    {
      "id": 1,
      "coins": 6000,
      "bonus_coins": 1000,
      "total_coins": 7000,
      "price": 120.0,
      "price_bdt": 120.0,
      "formatted_price": "৳120",
      "badge": null,
      "badge_color": "pink",
      "bonus_text": "+1000 Bonus",
      "bonus_percentage": 17,
      "is_popular": false,
      "popular": false,
      "button_text": "Recharge 7000 Gems (৳120)",
      "currency": "BDT",
      "currency_symbol": "৳"
    },
    {
      "id": 2,
      "coins": 32000,
      "bonus_coins": 8000,
      "total_coins": 40000,
      "price": 550.0,
      "price_bdt": 550.0,
      "formatted_price": "৳550",
      "badge": "🔥 50% OFF",
      "badge_color": "pink",
      "bonus_text": "+8000 Bonus",
      "bonus_percentage": 25,
      "is_popular": true,
      "popular": true,
      "button_text": "Recharge 40000 Gems (৳550)",
      "currency": "BDT",
      "currency_symbol": "৳"
    },
    {
      "id": 3,
      "coins": 70000,
      "bonus_coins": 20000,
      "total_coins": 90000,
      "price": 1150.0,
      "price_bdt": 1150.0,
      "formatted_price": "৳1,150",
      "badge": "Best Value",
      "badge_color": "pink",
      "bonus_text": "+20000 Bonus",
      "bonus_percentage": 29,
      "is_popular": false,
      "popular": false,
      "button_text": "Recharge 90000 Gems (৳1,150)",
      "currency": "BDT",
      "currency_symbol": "৳"
    },
    {
      "id": 4,
      "coins": 150000,
      "bonus_coins": 50000,
      "total_coins": 200000,
      "price": 2400.0,
      "price_bdt": 2400.0,
      "formatted_price": "৳2,400",
      "badge": "+30% Free",
      "badge_color": "pink",
      "bonus_text": "+50000 Bonus",
      "bonus_percentage": 33,
      "is_popular": false,
      "popular": false,
      "button_text": "Recharge 200000 Gems (৳2,400)",
      "currency": "BDT",
      "currency_symbol": "৳"
    },
    {
      "id": 5,
      "coins": 350000,
      "bonus_coins": 120000,
      "total_coins": 470000,
      "price": 5500.0,
      "price_bdt": 5500.0,
      "formatted_price": "৳5,500",
      "badge": "VIP Bonus",
      "badge_color": "pink",
      "bonus_text": "+120000 Bonus",
      "bonus_percentage": 34,
      "is_popular": false,
      "popular": false,
      "button_text": "Recharge 470000 Gems (৳5,500)",
      "currency": "BDT",
      "currency_symbol": "৳"
    }
  ]
}
```

### **Coin Package Object Fields**
| Field | Type | Description |
| :--- | :--- | :--- |
| `id` | `integer` | Unique ID of package |
| `coins` | `integer` | Base coins/gems included (e.g. `32000`) |
| `bonus_coins` | `integer` | Extra bonus coins given (e.g. `8000`) |
| `total_coins` | `integer` | Total coins received (`coins + bonus_coins`, e.g. `40000`) |
| `price` / `price_bdt` | `float` | Price in BDT (e.g. `550.00`) |
| `formatted_price` | `string` | Display formatted price (e.g. `"৳550"`) |
| `badge` | `string|null` | Promotional badge text (e.g. `"🔥 50% OFF"`, `"Best Value"`) |
| `bonus_text` | `string|null` | Ready-to-display bonus label (e.g. `"+8000 Bonus"`) |
| `bonus_percentage` | `integer` | Calculated bonus percentage (e.g. `25`%) |
| `is_popular` / `popular` | `boolean` | `true` if featured/highlighted card in UI |
| `button_text` | `string` | Ready-to-display bottom button label (e.g. `"Recharge 40000 Gems (৳550)"`) |
| `currency` | `string` | `"BDT"` |
| `currency_symbol` | `string` | `"৳"` |


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
