# Chinchins Live — RESTful API Documentation & Master Integration Guide

> **Base Production URL:** `https://chinchins.live/api`  
> **Static Assets Base URL:** `https://chinchins.live/uploads/`  
> **Default Auth Headers:**  
> - `Authorization: Bearer <sanctum_token>`  
> - `Accept: application/json`  
> - `Content-Type: application/json`

---

## 📑 Table of Contents

1. [Authentication & Account Management](#1-authentication--account-management)
   - [Register Account](#11-register-account)
   - [Login (Phone / Email)](#12-login)
   - [Forgot & Reset Password](#13-forgot--reset-password)
   - [User Session / Me](#14-user-session--me)
   - [Delete Account](#15-delete-account)
2. [App Legal & Settings (Admin Configurable)](#2-app-legal--settings)
   - [Terms of Service](#21-terms-of-service)
   - [Privacy Policy](#22-privacy-policy)
   - [About Us & App Version](#23-about-us--app-version)
3. [Streamer Profile & Live Feeds](#3-streamer-profile--live-feeds)
   - [Home Feed / Hot Streamers](#31-home-feed--hot-streamers)
   - [Streamer Profile](#32-streamer-profile)
4. [Wallet, Coin Deposits & Withdrawals](#4-wallet-coin-deposits--withdrawals)
   - [Wallet Balance](#41-wallet-balance)
   - [Deposit via bKash / Nagad / Rocket](#42-deposit-request)
   - [Withdraw Earnings](#43-withdraw-earnings)
5. [WebRTC Audio & Video Calling](#5-webrtc-audio--video-calling)
   - [Check Call Balance / Permission](#51-check-call-permission)
   - [Initiate & Accept Call](#52-initiate--accept-call)
   - [Call Heartbeat Pulse & Billing](#53-pulse-billing)
   - [End Call](#54-end-call)

---

## 1. Authentication & Account Management

### 1.1 Register Account
Creates a new user account with phone number, country, optional email, and password.

- **Method:** `POST`
- **Endpoint:** `/api/auth/register` (Aliases: `/api/register`, `/api/user/register`)
- **Headers:** `Accept: application/json`, `Content-Type: application/json`

#### Request Payload
```json
{
  "phone": "+8801408798865",
  "country": "Bangladesh",
  "email": "user@gmail.com",
  "password": "password123",
  "password_confirmation": "password123",
  "nickname": "SweetHeart",
  "gender": "male"
}
```

> **Note:** `first_name` and `last_name` are optional. If omitted, the system automatically derives a clean name from the nickname, email prefix, or phone digits.

#### Response (201 Created)
```json
{
  "status": true,
  "message": "Registration successful",
  "data": {
    "user": {
      "id": 15,
      "name": "SweetHeart",
      "nickname": "SweetHeart",
      "phone": "+8801408798865",
      "email": "user@gmail.com",
      "country": "Bangladesh",
      "country_flag": "🇧🇩",
      "gender": "male",
      "age": 22,
      "level": "Lv1",
      "charm_level": "Lv1"
    },
    "token": "15|xyz123abc...",
    "token_type": "Bearer"
  }
}
```

---

### 1.2 Login
- **Method:** `POST`
- **Endpoint:** `/api/auth/login` (Alias: `/api/login`)

#### Request Payload
```json
{
  "phone": "+8801408798865",
  "password": "password123"
}
```

---

### 1.3 Forgot & Reset Password
- **Send Reset Code:** `POST /api/auth/forgot-password` (`phone` or `email`)
- **Verify Reset Code:** `POST /api/auth/verify-reset-code` (`phone`/`email`, `code`)
- **Reset Password:** `POST /api/auth/reset-password` (`phone`/`email`, `code`, `password`, `password_confirmation`)

---

### 1.4 User Session / Me
- **Method:** `GET` / `POST`
- **Endpoint:** `/api/auth/me` (Aliases: `/api/auth/check`, `/api/user/me`)
- **Headers:** `Authorization: Bearer <token>`

---

## 2. App Legal & Settings (Admin Configurable)

All legal and about texts are dynamic and editable in the Admin Panel (`/admin/settings`).

### 2.1 Terms of Service
- **Method:** `GET`
- **Endpoint:** `/api/app/terms` (Aliases: `/api/terms-of-service`, `/api/terms`)

#### Response (200 OK)
```json
{
  "status": true,
  "message": "Terms of Service retrieved successfully.",
  "data": {
    "title": "Chinchins Live Terms of Service",
    "app_name": "Chinchins Live",
    "last_updated": "September 2026",
    "content": "Welcome to Chinchins Live! By using our platform, you agree to the following terms...",
    "formatted_html": "Welcome to Chinchins Live!<br />By using our platform..."
  }
}
```

---

### 2.2 Privacy Policy
- **Method:** `GET`
- **Endpoint:** `/api/app/privacy-policy` (Aliases: `/api/privacy-policy`, `/api/privacy`)

#### Response (200 OK)
```json
{
  "status": true,
  "message": "Privacy Policy retrieved successfully.",
  "data": {
    "title": "Chinchins Live Privacy Policy",
    "app_name": "Chinchins Live",
    "last_updated": "September 2026",
    "support_email": "support@chinchins.live",
    "content": "Chinchins Live is committed to protecting your personal information and privacy...",
    "formatted_html": "Chinchins Live is committed to protecting your personal information..."
  }
}
```

---

### 2.3 About Us & App Version
- **Method:** `GET`
- **Endpoint:** `/api/app/about` (Alias: `/api/about`)
- **Check Update:** `GET /api/app/check-update?version_code=100`

---

## 3. Streamer Profile & Live Feeds

### 3.1 Home Feed / Hot Streamers
- **Method:** `GET`
- **Endpoint:** `/api/home` (Aliases: `/api/streamers`, `/api/hot`, `/api/live/streamers`)

---

## 4. Wallet, Coin Deposits & Withdrawals

### 4.1 Wallet Balance
- **Method:** `GET`
- **Endpoint:** `/api/wallet` (Aliases: `/api/wallet/balance`, `/api/coins/balance`)

### 4.2 Deposit Request
- **Method:** `POST`
- **Endpoint:** `/api/deposit/submit`
- **Payload:** `{"method": "bKash", "amount": 500, "transaction_id": "TXN123456"}`

### 4.3 Withdraw Earnings
- **Method:** `POST`
- **Endpoint:** `/api/withdraw/submit`
- **Payload:** `{"method": "bKash", "account_number": "01700000000", "amount": 1000}`

---

## 5. WebRTC Audio & Video Calling

### 5.1 Check Call Permission
- **Method:** `POST`
- **Endpoint:** `/api/call/check-permission`
- **Payload:** `{"target_user_id": 5, "type": "video"}`

### 5.2 Initiate & Accept Call
- **Initiate:** `POST /api/call/initiate` (`{"target_user_id": 5, "type": "video"}`)
- **Accept:** `POST /api/call/accept` (`{"call_id": 12}`)

### 5.3 Pulse Billing
- **Pulse:** `POST /api/call/deduct-interval` (`{"call_id": 12}`)

### 5.4 End Call
- **End:** `POST /api/call/end` (`{"call_id": 12}`)
