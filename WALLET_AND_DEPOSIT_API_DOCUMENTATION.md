# 💰 Chinchins Live — Wallet & Coin Deposit System API Documentation

This documentation provides the complete technical specification for the **Wallet, Coins/Gems Balance, Coin Packages, Payment Methods (bKash, Nagad, Rocket), Deposit / Recharge Submissions**, and **Deposit History Tracking** for Mobile App Developers (Flutter / Android / iOS) and Backend Administrators.

---

## 🌐 Base URL & Endpoints

```http
Production:  https://chinchins.live/api
Development: http://127.0.0.1:8000/api
```

> **Note**: For mobile app flexibility, all routes work both **with** `/api/` (e.g. `/api/wallet/balance`) and **without** `/api/` (e.g. `/wallet/balance`). All endpoints strictly return pure JSON `application/json` responses.

---

## 🔑 Authentication Architecture

- **Primary**: `Authorization: Bearer <AUTH_TOKEN>`
- **Fallback**: `X-User-Id: <USER_ID>` or request parameter `user_id=<USER_ID>`
- **Headers**:
```http
Accept: application/json
Content-Type: multipart/form-data (or application/json)
Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxx
```

---

## 📑 Complete RESTful API Matrix

| # | Method | Endpoint | Description |
| :---: | :---: | :--- | :--- |
| **1** | `GET` | `/api/wallet/balance` | **Get user wallet balance**, total coins, total deposited coins, BDT amount, and call minutes |
| **2** | `GET` | `/api/payment-methods` | **Get active payment methods** (bKash, Nagad, Rocket, Upay) with account numbers & instructions |
| **3** | `GET` | `/api/coin-packages` | **Get all coin recharge packages** with base coins, bonus gems, and BDT prices |
| **4** | `POST` | `/api/deposit/submit` | **Submit a new deposit/recharge request** (bKash/Nagad TrxID, sender number, amount & receipt photo) |
| **5** | `GET` | `/api/deposit/history` | **Get deposit transaction history** with status (`pending`, `approved`, `rejected`) and method name |
| **6** | `GET` | `/api/wallet/transactions` | Get full coin debit/credit ledger (calls, gifts, deposits) |

---

## 🪙 1. Get Wallet Balance & Summary API

Retrieves the user's current coin/gem balance, total coins received from approved deposits, total BDT spent, and video call duration available.

### **Endpoint**
`GET /api/wallet/balance` *(Aliases: `/api/wallet`, `/api/wallet/summary`, `/wallet/balance`)*

### **Headers**
```http
Authorization: Bearer <TOKEN>
Accept: application/json
X-User-Id: 4
```

### **Success Response (200 OK)**
```json
{
  "status": true,
  "message": "Wallet balance retrieved successfully.",
  "data": {
    "user_id": 4,
    "account_id": "CHIN1082",
    "display_name": "Nazmul",
    "coins": 45000,
    "gems": 45000,
    "beans": 0,
    "formatted_coins": "45,000",
    "total_deposited_coins": 80000,
    "formatted_total_deposited_coins": "80,000",
    "total_deposited_bdt": 1100.0,
    "formatted_total_deposited_bdt": "৳1,100",
    "approved_deposits_count": 2,
    "pending_deposits_count": 0,
    "call_rate_per_minute": 100,
    "max_call_minutes": 450,
    "avatar_url": "https://chinchins.live/uploads/avatars/user_4.jpg",
    "latest_deposit": {
      "id": 2,
      "amount": 550.0,
      "coins": 40000,
      "payment_method": "bKash Personal",
      "transaction_id": "TRX9A8B7C6D",
      "status": "approved",
      "created_at": "2026-08-27T12:00:00Z"
    }
  }
}
```

---

## 🏦 2. Get Payment Methods (bKash / Nagad / Rocket) API

Returns active mobile banking and payment gateway accounts for depositing money.

### **Endpoint**
`GET /api/payment-methods` *(Aliases: `/api/deposit/methods`, `/payment-methods`)*

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
      "account_type": "Personal (Send Money)",
      "account_number": "01700000000",
      "instructions": "1. Go to bKash Send Money.\n2. Enter our number: 01700000000.\n3. Enter amount.\n4. Copy TrxID and submit below.",
      "icon": "https://chinchins.live/images/bkash.png",
      "qr_code": "https://chinchins.live/images/bkash_qr.png",
      "min_deposit": 50.0,
      "max_deposit": 25000.0,
      "rate_coins": 500,
      "bonus_coins": 100,
      "total_coins": 600,
      "rate_bdt": 50.0,
      "offer_tag": "🔥 20% Bonus",
      "button_text": "Recharge 600 Gems (৳50)"
    },
    {
      "id": 2,
      "name": "Nagad Personal",
      "code": "nagad",
      "account_type": "Personal (Send Money)",
      "account_number": "01800000000",
      "instructions": "1. Go to Nagad Send Money.\n2. Enter our number: 01800000000.\n3. Enter amount.\n4. Copy TrxID and submit below.",
      "icon": "https://chinchins.live/images/nagad.png",
      "qr_code": "https://chinchins.live/images/nagad_qr.png",
      "min_deposit": 50.0,
      "max_deposit": 25000.0,
      "rate_coins": 500,
      "bonus_coins": 100,
      "total_coins": 600,
      "rate_bdt": 50.0,
      "offer_tag": "🔥 20% Bonus",
      "button_text": "Recharge 600 Gems (৳50)"
    }
  ]
}
```

---

## 📦 3. Get Coin Packages / Recharge Plans API

Returns pre-configured coin packages with base coins, bonus gems, and BDT prices.

### **Endpoint**
`GET /api/coin-packages` *(Aliases: `/api/deposit/packages`, `/api/packages`, `/coin-packages`)*

### **Success Response (200 OK)**
```json
{
  "status": true,
  "message": "Coin packages retrieved successfully from database.",
  "data": [
    {
      "id": 1,
      "coins": 32000,
      "base_coins": 32000,
      "bonus_coins": 700,
      "total_coins": 32700,
      "formatted_coins": "32,000",
      "formatted_base_coins": "32,000",
      "formatted_bonus_coins": "+700 Bonus",
      "formatted_total_coins": "32,700",
      "coins_title": "32000",
      "display_coins": "32000",
      "display_bonus": "+700 Bonus Gems",
      "price": 150.0,
      "price_bdt": 150.0,
      "formatted_price": "৳150",
      "badge": "🔥 50% OFF",
      "badge_color": "pink",
      "bonus_text": "+700 Bonus",
      "bonus_percentage": 2,
      "is_popular": false,
      "popular": false,
      "button_text": "Recharge 32000 Gems (৳150)",
      "currency": "BDT",
      "currency_symbol": "৳"
    },
    {
      "id": 2,
      "coins": 32000,
      "base_coins": 32000,
      "bonus_coins": 8000,
      "total_coins": 40000,
      "formatted_coins": "32,000",
      "formatted_base_coins": "32,000",
      "formatted_bonus_coins": "+8,000 Bonus",
      "formatted_total_coins": "40,000",
      "coins_title": "32000",
      "display_coins": "32000",
      "display_bonus": "+8000 Bonus Gems",
      "price": 550.0,
      "price_bdt": 550.0,
      "formatted_price": "৳550",
      "badge": "🔥 50% OFF",
      "badge_color": "pink",
      "bonus_text": "+8000 Bonus",
      "bonus_percentage": 25,
      "is_popular": true,
      "popular": true,
      "button_text": "Recharge 32000 Gems (৳550)",
      "currency": "BDT",
      "currency_symbol": "৳"
    }
  ]
}
```

> **📌 Mobile App Card UI Field Binding Guide**:
> - **Main Large Number**: Bind to `pkg['coins']` or `pkg['display_coins']` (e.g. **`32000`**).
> - **Bonus Badge Pill**: Bind to `pkg['display_bonus']` or `pkg['bonus_text']` (e.g. **`+8000 Bonus Gems`**).
> - **Price Button**: Bind to `pkg['formatted_price']` (e.g. **`৳550`**).
> - **Discount Tag**: Bind to `pkg['badge']` (e.g. **`🔥 50% OFF`**).

---

## 📥 4. Submit Deposit / Recharge Request API

When the user sends money via bKash / Nagad, they enter the sender number, transaction ID, amount, and optional receipt screenshot.

### **Endpoint**
`POST /api/deposit/submit` *(Aliases: `/api/deposit/request`, `/api/wallet/deposit`, `/deposit/submit`)*

### **Request Parameters (Multipart Form-Data or JSON)**

| Field Name | Type | Required | Description |
| :--- | :---: | :---: | :--- |
| `payment_method` | string | **Yes** | Method name: `bkash`, `nagad`, `rocket`, `upay` (or `payment_method_id`) |
| `sender_number` | string | **Yes** | User's bKash/Nagad phone number (e.g. `01711223344`) |
| `transaction_id` | string | **Yes** | Transaction ID / TrxID (e.g. `TRX9A8B7C6D`) |
| `amount` | number | **Yes** | Deposit amount in BDT (e.g. `550.00`) |
| `coins` | integer | Optional | Coins to receive (auto-calculated if omitted) |
| `package_id` | integer | Optional | Selected CoinPackage ID |
| `screenshot` | file | Optional | Receipt / transaction proof photo |
| `user_note` | string | Optional | Optional user note |

### **Example cURL Request**
```bash
curl -X POST "https://chinchins.live/api/deposit/submit" \
  -H "Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxx" \
  -H "Accept: application/json" \
  -F "payment_method=bkash" \
  -F "sender_number=01711223344" \
  -F "transaction_id=TRX9A8B7C6D" \
  -F "amount=550" \
  -F "coins=40000" \
  -F "screenshot=@/path/to/receipt.jpg"
```

### **Success Response (201 Created)**
```json
{
  "status": true,
  "message": "Deposit request submitted successfully! Your coins will be credited once verified by admin.",
  "data": {
    "deposit_id": 5,
    "amount": 550.0,
    "coins": 40000,
    "payment_method": "bKash Personal",
    "sender_number": "01711223344",
    "transaction_id": "TRX9A8B7C6D",
    "screenshot_url": "https://chinchins.live/uploads/deposits/deposit_4_1787825000_abc.jpg",
    "status": "pending",
    "created_at": "2026-08-27T12:30:00Z"
  }
}
```

---

## 📜 5. Get Deposit History API

Retrieves all previous deposit/recharge orders submitted by the user along with their verification status (`pending`, `approved`, `rejected`).

### **Endpoint**
`GET /api/deposit/history` *(Aliases: `/api/wallet/history`, `/api/wallet/deposits`, `/deposit/history`)*

### **Success Response (200 OK)**
```json
{
  "status": true,
  "message": "Deposit history retrieved successfully.",
  "data": [
    {
      "id": 5,
      "user_id": 4,
      "payment_method_id": 1,
      "payment_method_name": "bKash Personal",
      "amount": "550.00",
      "coins": 40000,
      "sender_number": "01711223344",
      "transaction_id": "TRX9A8B7C6D",
      "screenshot_url": "https://chinchins.live/uploads/deposits/deposit_4_1787825000_abc.jpg",
      "status": "pending",
      "status_badge_class": "badge-warning",
      "admin_note": null,
      "created_at": "2026-08-27T12:30:00.000000Z"
    },
    {
      "id": 2,
      "user_id": 4,
      "payment_method_id": 1,
      "payment_method_name": "bKash Personal",
      "amount": "550.00",
      "coins": 40000,
      "sender_number": "01711223344",
      "transaction_id": "TRX4ED09CC3",
      "screenshot_url": null,
      "status": "approved",
      "status_badge_class": "badge-success",
      "admin_note": "Payment verified and coins credited",
      "created_at": "2026-08-27T10:00:00.000000Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "total": 2
  }
}
```

---

## 📱 6. Complete Flutter Integration Service (`wallet_api_service.dart`)

```dart
import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;

class WalletApiService {
  final String baseUrl = "https://chinchins.live/api";

  /// 1. Get Wallet Balance, Total Deposited Coins & Statistics
  Future<Map<String, dynamic>> getWalletBalance(String token) async {
    var response = await http.get(
      Uri.parse('$baseUrl/wallet/balance'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );
    return jsonDecode(response.body);
  }

  /// 2. Get Active Payment Methods (bKash, Nagad, Rocket)
  Future<Map<String, dynamic>> getPaymentMethods() async {
    var response = await http.get(
      Uri.parse('$baseUrl/payment-methods'),
      headers: {'Accept': 'application/json'},
    );
    return jsonDecode(response.body);
  }

  /// 3. Get Coin Packages / Recharge Plans
  Future<Map<String, dynamic>> getCoinPackages() async {
    var response = await http.get(
      Uri.parse('$baseUrl/coin-packages'),
      headers: {'Accept': 'application/json'},
    );
    return jsonDecode(response.body);
  }

  /// 4. Submit a Deposit / Recharge Request
  Future<Map<String, dynamic>> submitDepositRequest({
    required String token,
    required String paymentMethod, // 'bkash', 'nagad', 'rocket'
    required String senderNumber, // '01711223344'
    required String transactionId, // 'TRX9A8B7C6D'
    required double amount, // 550.0
    int? coins, // 40000
    int? packageId,
    File? screenshotFile,
    String? userNote,
  }) async {
    var uri = Uri.parse('$baseUrl/deposit/submit');
    var request = http.MultipartRequest('POST', uri);

    request.headers['Authorization'] = 'Bearer $token';
    request.headers['Accept'] = 'application/json';

    request.fields['payment_method'] = paymentMethod;
    request.fields['sender_number'] = senderNumber;
    request.fields['transaction_id'] = transactionId;
    request.fields['amount'] = amount.toString();
    if (coins != null) {
      request.fields['coins'] = coins.toString();
    }
    if (packageId != null) {
      request.fields['package_id'] = packageId.toString();
    }
    if (userNote != null) {
      request.fields['user_note'] = userNote;
    }

    if (screenshotFile != null) {
      request.files.add(await http.MultipartFile.fromPath('screenshot', screenshotFile.path));
    }

    var streamedResponse = await request.send();
    var response = await http.Response.fromStream(streamedResponse);
    return jsonDecode(response.body);
  }

  /// 5. Get User's Deposit History
  Future<Map<String, dynamic>> getDepositHistory(String token, {int page = 1}) async {
    var response = await http.get(
      Uri.parse('$baseUrl/deposit/history?page=$page'),
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

## 🛠️ Instructions for Flutter Developer:
1. **Add `Wallet` button in profile / Me tab** (as shown in your screenshot).
2. **On clicking `Wallet`**:
   - Call `getWalletBalance()` to display:
     - **Current Coins**: `coins` (e.g. `45,000`)
     - **Total Deposited Coins**: `total_deposited_coins` (e.g. `80,000`)
     - **Total Spent**: `formatted_total_deposited_bdt` (e.g. `৳1,100`)
3. **Recharge Button / Tab**:
   - Call `getCoinPackages()` to list packages (e.g. `32,000 + 8,000 Bonus = ৳550`).
   - Call `getPaymentMethods()` to show bKash / Nagad account numbers.
   - On payment done, call `submitDepositRequest()`.
4. **Deposit History Tab**:
   - Call `getDepositHistory()` to show the list of deposits with method name (bKash/Nagad), TrxID, amount, coins, and status badge (`pending`, `approved`, `rejected`).
5. **Withdrawal / Cash Out System**:
   - For full Withdrawal / Cash Out endpoints (`/api/withdraw/info`, `/api/withdraw/calculate`, `/api/withdraw/submit`, `/api/withdraw/history`), please refer to [WITHDRAW_API_DOCUMENTATION.md](file:///f:/Chinchins-live-website/WITHDRAW_API_DOCUMENTATION.md).
