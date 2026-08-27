# 💸 Chinchins Live — Coin Withdrawal & Cash Out API Documentation

Welcome to the complete RESTful API documentation for the **Coin Withdrawal / Cash Out System** of **Chinchins Live**.

This document is prepared specifically for **Mobile App Developers (Flutter / React Native / Android / iOS)** and **Frontend Developers** to seamlessly integrate the Withdrawal feature.

---

## 📑 Table of Contents
1. [System Architecture & Lifecycle](#-system-architecture--lifecycle)
2. [Authentication & Request Headers](#-authentication--request-headers)
3. [Summary of Endpoints](#-summary-of-endpoints)
4. [API 1: Get Withdrawal Info & Configuration](#-1-get-withdrawal-info--configuration-api)
5. [API 2: Dynamic Withdrawal Calculator / Preview](#-2-dynamic-withdrawal-calculator--preview-api)
6. [API 3: Submit Withdrawal Request](#-3-submit-withdrawal-request-api)
7. [API 4: Get Withdrawal History](#-4-get-withdrawal-history-api)
8. [API 5: Get Single Withdrawal Details](#-5-get-single-withdrawal-details-api)
9. [Calculation Formula & Business Logic](#-calculation-formula--business-logic)
10. [Admin Approval & Coin Deduction Lifecycle](#-admin-approval--coin-deduction-lifecycle)
11. [Mobile UI Integration Flow (Me Screen -> Withdraw)](#-mobile-ui-integration-flow)

---

## 🔄 System Architecture & Lifecycle

```
[ User in App (Me / Wallet Screen) ]
               │
               ▼ (Clicks "Withdraw" button)
[ GET /api/withdraw/info ] ────────► Fetches user coins, min/max limits, commission %, 
                                      conversion rate, and available payout methods (bKash, Nagad, etc.)
               │
               ▼ (User enters Coins & selects Payment Method + Phone Number)
[ POST /api/withdraw/calculate ] ──► (Optional) Previews Gross BDT, Commission fee, and Net Payout
               │
               ▼ (User clicks "Submit Withdrawal")
[ POST /api/withdraw/submit ] ─────► Validates balance & limits -> Creates Pending Request
               │
               ▼
[ Admin Reviews in Admin Panel ]
               │
      ┌────────┴────────┐
      ▼                 ▼
[ APPROVE ]        [ REJECT ]
      │                 │
      ├─► Deducts coins from user wallet   └─► No coins deducted
      ├─► Records in Coin Ledger ledger       └─► Rejection reason logged
      └─► Status becomes 'approved'           └─► Status becomes 'rejected'
```

---

## 🔑 Authentication & Request Headers

All withdrawal APIs support **flexible and resilient authentication**:

### Option 1: Bearer Token (Recommended)
```http
Authorization: Bearer <SANCTUM_PERSONAL_ACCESS_TOKEN>
Accept: application/json
Content-Type: application/json
```

### Option 2: Custom Header User Identifier
```http
X-User-Id: 1
# OR
X-Account-Id: 1000000001
Accept: application/json
Content-Type: application/json
```

### Option 3: Request Body / Query Param Fallback
Pass `user_id` or `account_id` directly in the query string or JSON payload.

---

## 🚀 Summary of Endpoints

| Method | Endpoint | Description | Aliases |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/withdraw/info` | Get withdrawal configuration, user wallet balance, limits, commission, and payout methods dropdown. | `/api/withdraw/config`, `/api/wallet/withdraw/info`, `/api/wallet/withdraw` |
| `POST` | `/api/withdraw/calculate` | Calculate gross BDT, commission fee, and net payable amount in real-time. | `/api/withdraw/preview` |
| `POST` | `/api/withdraw/submit` | Submit a cash out request. Status defaults to `pending`. | `/api/withdraw/request`, `/api/withdraw/create`, `/api/wallet/withdraw` |
| `GET` | `/api/withdraw/history` | Get user's past withdrawal history with pagination and status. | `/api/wallet/withdraw/history`, `/api/wallet/withdrawals` |
| `GET` | `/api/withdraw/{id}` | Get details of a specific withdrawal request. | — |

---

## 📦 1. Get Withdrawal Info & Configuration API

Fetches all dynamic data required to render the **Withdrawal Screen** in the app:
- Current user wallet coin balance
- Minimum and Maximum withdrawal limits (in Coins and BDT)
- Commission percentage (e.g. `5.0%`)
- Coin-to-BDT Exchange rate (e.g. `100 Coins = ৳10.00 BDT`, meaning `1 BDT = 10 Coins`)
- Array of active payout methods for the dropdown (e.g. bKash, Nagad, Rocket, Upay)
- Admin instructions and guidelines

### **Endpoint**
`GET /api/withdraw/info` *(Aliases: `/api/withdraw/config`, `/api/wallet/withdraw`)*

### **Success Response (200 OK)**
```json
{
  "status": true,
  "message": "Withdrawal information and settings retrieved successfully.",
  "data": {
    "is_enabled": true,
    "min_withdraw_coins": 1000,
    "max_withdraw_coins": 100000,
    "commission_percent": 5.0,
    "rate_coins": 100,
    "rate_bdt": 10.0,
    "rate_per_bdt": 10.0,
    "min_withdraw_bdt": 100.0,
    "max_withdraw_bdt": 10000.0,
    "rate_text": "100 Coins = ৳10.00 BDT (1 BDT = 10 Coins)",
    "notice": "Withdrawals are processed manually via bKash, Nagad, and Rocket within 1-24 hours. A standard platform commission applies on all cash outs.",
    "user": {
      "user_id": 1,
      "account_id": "1000000001",
      "display_name": "Nazmul Hossain",
      "phone": "01700000000",
      "coins": 45000,
      "formatted_coins": "45,000 Coins",
      "estimated_gross_bdt": 4500.0,
      "estimated_commission_bdt": 225.0,
      "estimated_net_bdt": 4275.0,
      "formatted_estimated_net_bdt": "৳4,275.00",
      "can_withdraw": true,
      "total_withdrawn_coins": 5000,
      "formatted_total_withdrawn_coins": "5,000 Coins",
      "total_withdrawn_bdt": 475.0,
      "formatted_total_withdrawn_bdt": "৳475.00",
      "pending_withdraws_count": 0
    },
    "payment_methods": [
      {
        "id": 1,
        "name": "bKash Personal",
        "code": "bkash",
        "account_type": "Personal",
        "icon": "https://yourdomain.com/assets/images/bkash.png",
        "icon_url": "https://yourdomain.com/assets/images/bkash.png",
        "min_withdraw": 50.0,
        "max_withdraw": 50000.0,
        "instructions": "Enter your 11-digit bKash Personal mobile number."
      },
      {
        "id": 2,
        "name": "Nagad Personal",
        "code": "nagad",
        "account_type": "Personal",
        "icon": "https://yourdomain.com/assets/images/nagad.png",
        "icon_url": "https://yourdomain.com/assets/images/nagad.png",
        "min_withdraw": 50.0,
        "max_withdraw": 50000.0,
        "instructions": "Enter your 11-digit Nagad Personal mobile number."
      },
      {
        "id": 3,
        "name": "Rocket Personal",
        "code": "rocket",
        "account_type": "Personal",
        "icon": "https://yourdomain.com/assets/images/rocket.png",
        "icon_url": "https://yourdomain.com/assets/images/rocket.png",
        "min_withdraw": 50.0,
        "max_withdraw": 50000.0,
        "instructions": "Enter your 12-digit Rocket account number."
      }
    ]
  }
}
```

---

## 🧮 2. Dynamic Withdrawal Calculator / Preview API

Allows the app to live-calculate payout breakdown and validate limits as the user types coins in the input box.

### **Endpoint**
`POST /api/withdraw/calculate` *(Alias: `/api/withdraw/preview`)*

### **Request Body (JSON)**
```json
{
  "coins": 5000
}
```

### **Success Response (200 OK)**
```json
{
  "status": true,
  "message": "Withdrawal calculation generated.",
  "data": {
    "coins": 5000,
    "formatted_coins": "5,000 Coins",
    "gross_amount": 500.0,
    "formatted_gross_amount": "৳500.00",
    "commission_percent": 5.0,
    "commission_amount": 25.0,
    "formatted_commission_amount": "৳25.00 (5%)",
    "net_payable_amount": 475.0,
    "formatted_net_payable_amount": "৳475.00",
    "rate_per_bdt": 10.0,
    "rate_text": "100 Coins = ৳10.00 BDT (1 BDT = 10 Coins)",
    "is_valid": true,
    "error_message": null
  }
}
```

---

## 📤 3. Submit Withdrawal Request API

Submits a new cash out request. The request is created with status **`pending`**.

### **Endpoint**
`POST /api/withdraw/submit` *(Aliases: `/api/withdraw/request`, `/api/withdraw/create`, `/api/wallet/withdraw`)*

### **Request Headers**
```http
Authorization: Bearer <TOKEN>
Content-Type: application/json
Accept: application/json
```

### **Request Body (JSON)**
```json
{
  "coins": 5000,
  "payment_method_id": 1,
  "payment_method": "bkash",
  "account_number": "01712345678",
  "account_type": "Personal",
  "user_note": "Please process to my primary bKash number."
}
```

### **Field Descriptions**
| Field | Type | Required | Description |
| :--- | :--- | :--- | :--- |
| `coins` | Integer | **Yes** | Amount of coins to withdraw (e.g. `5000`). Must be between min and max limits. |
| `payment_method_id` | Integer | Optional | The ID of the selected Payment Method from `/api/withdraw/info`. |
| `payment_method` | String | Optional | Method code or name (e.g. `'bkash'`, `'nagad'`, `'rocket'`). |
| `account_number` | String | **Yes** | User's bKash/Nagad/Rocket account phone number. |
| `account_type` | String | Optional | `'Personal'`, `'Agent'`, or `'Merchant'` (default: `'Personal'`). |
| `user_note` | String | Optional | Optional user remark or instruction. |

### **Success Response (201 Created)**
```json
{
  "status": true,
  "message": "Withdrawal request submitted successfully! It is now pending admin approval. Once approved, coins will be deducted from your wallet and payment sent to your account.",
  "data": {
    "withdraw_id": 1,
    "coins": 5000,
    "formatted_coins": "5,000 Coins",
    "gross_amount": 500.0,
    "formatted_gross_amount": "৳500.00",
    "commission_percent": 5.0,
    "commission_amount": 25.0,
    "formatted_commission_amount": "৳25.00",
    "net_payable_amount": 475.0,
    "formatted_net_payable_amount": "৳475.00",
    "payment_method": "bKash Personal",
    "account_number": "01712345678",
    "account_type": "Personal",
    "status": "pending",
    "user_current_coins": 50000,
    "created_at": "2026-08-27T16:49:23Z"
  }
}
```

### **Error Responses**

#### 1. Insufficient Coins Balance (422 Unprocessable Content)
```json
{
  "status": false,
  "message": "Insufficient coin balance. Your current balance is 2,000 coins, but requested 5,000 coins.",
  "data": {
    "current_coins": 2000,
    "requested_coins": 5000,
    "shortfall_coins": 3000
  }
}
```

#### 2. Below Minimum Limit (422 Unprocessable Content)
```json
{
  "status": false,
  "message": "Minimum withdrawal limit is 1000 Coins (৳100.00)."
}
```

#### 3. Above Maximum Limit (422 Unprocessable Content)
```json
{
  "status": false,
  "message": "Maximum withdrawal limit is 100000 Coins (৳10,000.00)."
}
```

#### 4. Withdrawals Temporarily Disabled by Admin (403 Forbidden)
```json
{
  "status": false,
  "message": "Withdrawals are currently disabled by administrator. Please try again later."
}
```

---

## 📜 4. Get Withdrawal History API

Retrieves the paginated list of all withdrawal requests for the authenticated user.

### **Endpoint**
`GET /api/withdraw/history` *(Aliases: `/api/wallet/withdraw/history`, `/api/wallet/withdrawals`)*

### **Query Parameters**
- `page`: Integer (default `1`)

### **Success Response (200 OK)**
```json
{
  "status": true,
  "message": "Withdrawal history retrieved successfully.",
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "payment_method_id": 1,
      "payment_method_name": "bKash Personal",
      "coins": 5000,
      "rate_per_bdt": "10.00",
      "gross_amount": "500.00",
      "commission_percent": "5.00",
      "commission_amount": "25.00",
      "net_payable_amount": "475.00",
      "account_number": "01712345678",
      "account_type": "Personal",
      "user_note": "Please process fast",
      "status": "approved",
      "transaction_id": "BK789456123",
      "admin_note": "Paid via bKash Personal",
      "approved_at": "2026-08-27T16:49:37Z",
      "rejected_at": null,
      "created_at": "2026-08-27T16:49:23Z",
      "formatted_coins": "5,000 Coins",
      "formatted_gross_amount": "৳500.00",
      "formatted_commission_amount": "৳25.00 (5.00%)",
      "formatted_net_payable_amount": "৳475.00",
      "status_badge_class": "badge-soft-success",
      "payment_method_icon_url": "https://yourdomain.com/assets/images/bkash.png"
    }
  ],
  "current_coins": 45000,
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "total": 1
  }
}
```

---

## 🔍 5. Get Single Withdrawal Details API

### **Endpoint**
`GET /api/withdraw/{id}`

### **Success Response (200 OK)**
```json
{
  "status": true,
  "message": "Withdrawal request details retrieved.",
  "data": {
    "id": 1,
    "user_id": 1,
    "payment_method_name": "bKash Personal",
    "coins": 5000,
    "gross_amount": "500.00",
    "commission_percent": "5.00",
    "commission_amount": "25.00",
    "net_payable_amount": "475.00",
    "account_number": "01712345678",
    "account_type": "Personal",
    "status": "approved",
    "transaction_id": "BK789456123",
    "admin_note": "Paid via bKash Personal",
    "created_at": "2026-08-27T16:49:23Z"
  }
}
```

---

## 📐 Calculation Formula & Business Logic

The backend calculates conversion and payouts using the formulas configured in the Admin Panel:

1. **Exchange Rate**:
   $$\text{Rate Per BDT} = \frac{\text{Rate Coins}}{\text{Rate BDT}}$$
   *Example: $100 \text{ Coins} = 10 \text{ BDT} \implies 1 \text{ BDT} = 10 \text{ Coins}$.*

2. **Gross BDT**:
   $$\text{Gross BDT} = \frac{\text{Coins}}{\text{Rate Per BDT}}$$
   *Example: $5,000 \text{ Coins} / 10 = \text{৳}500.00 \text{ BDT}$.*

3. **Commission Fee**:
   $$\text{Commission Amount} = \text{Gross BDT} \times \left(\frac{\text{Commission \%}}{100}\right)$$
   *Example: $\text{৳}500.00 \times \left(\frac{5.0}{100}\right) = \text{৳}25.00 \text{ BDT}$.*

4. **Net Payable to User**:
   $$\text{Net Payable Amount} = \text{Gross BDT} - \text{Commission Amount}$$
   *Example: $\text{৳}500.00 - \text{৳}25.00 = \text{৳}475.00 \text{ BDT}$.*

---

## ⚡ Admin Approval & Coin Deduction Lifecycle

1. **Submission**:
   - User submits cash out request with coins & payment details.
   - Status is set to **`pending`**.
   - User coins remain visible in wallet during pending verification.

2. **Admin Approval**:
   - Admin reviews the request in the Admin Panel (`/admin/withdrawals`).
   - Admin clicks **Approve** and optionally enters the payment Transaction ID (TrxID).
   - The backend atomically **deducts the coins from the user's wallet** and creates an entry in the **`coin_transactions`** ledger (`type: 'withdraw'`).
   - Status updates to **`approved`**.

3. **Admin Rejection**:
   - If admin rejects the request, no coins are deducted from user balance.
   - Admin enters the rejection reason.
   - Status updates to **`rejected`**.

---

## 📱 Mobile UI Integration Flow

Here is the recommended UI/UX flow for the Mobile Application:

### 1. **Me / Profile / Wallet Screen**
- Display current coin balance (from `GET /api/wallet/balance` or `GET /api/withdraw/info`).
- Add a **"Withdraw" / "Cash Out"** button next to "Recharge / Deposit".

### 2. **Withdrawal Form Screen**
- Fetch `GET /api/withdraw/info`.
- Display:
  - **Available Balance**: e.g., `45,000 Coins`
  - **Limits Banner**: e.g., `Min: 1,000 Coins (৳100) | Max: 100,000 Coins (৳10,000)`
  - **Commission Notice**: e.g., `Platform Fee: 5%`
  - **Exchange Rate**: e.g., `100 Coins = ৳10 BDT`
- **Payment Method Dropdown**:
  - Show list from `data.payment_methods` (bKash, Nagad, Rocket with icons).
- **Account Number Input**:
  - User enters their mobile number (e.g. `01712345678`).
- **Coins Amount Input**:
  - As user types coins, call `POST /api/withdraw/calculate` (or calculate locally with the formulas above) to show live breakdown:
    - **Gross Value**: `৳500.00`
    - **Commission (5%)**: `-৳25.00`
    - **You will receive**: `৳475.00 BDT`
- **Submit Button**:
  - Calls `POST /api/withdraw/submit`.
  - Shows success popup: *"Withdrawal request submitted! Your funds will be sent to your bKash account within 1-24 hours."*

### 3. **Withdrawal History Screen**
- Calls `GET /api/withdraw/history`.
- Displays card for each request:
  - Status Badge (`Pending` [yellow], `Approved` [green], `Rejected` [red])
  - Coins requested & Net BDT received
  - Method (bKash/Nagad), Account Number, Date, and TrxID / Admin Remark.
