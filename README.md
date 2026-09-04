# 📱 Chinchins Live — Backend API & WebRTC Streaming Platform

Chinchins Live is a modern live streaming, 1-on-1 WebRTC video calling, virtual gifting, VIP privileges, and instant wallet deposit/withdrawal platform.

- **Live Server Base URL:** `https://chinchins.live/api`
- **Master API Documentation:** See [`CHINCHINS_MASTER_RESTFUL_API_DOCUMENTATION.md`](./CHINCHINS_MASTER_RESTFUL_API_DOCUMENTATION.md) for full endpoint specifications, request/response formats, and the production Flutter WebRTC service code.

---

## 🌟 Core Features
- **1-on-1 WebRTC Audio & Video Calling:**
  - High-availability STUN and multi-protocol TURN (TCP, UDP, TLS on ports 80, 443, 3478, 5349) for seamless cross-network / 4G/5G / international connectivity.
  - Zero-latency incoming call push notifications and ringtone triggers.
  - Real-time in-call coin billing pulse with 50/50 revenue sharing (50% host earnings, 50% platform revenue).
- **Wallet, Coins & Manual Deposits:**
  - Support for manual payment methods (bKash, Nagad, Rocket, Bank Transfer) with admin review and instant coin top-ups.
  - Withdrawal engine with minimum coin limits and real-time exchange rates.
- **VIP Privilege Cards & Daily Check-In:**
  - Weekly and monthly subscription cards with instant bonus coins and daily claimable rewards.
- **Virtual Gifts & Live Stream Interactions:**
  - SVGA/Lottie animated gifts with real-time coin deductions and top fan leaderboards.
- **In-App Messaging & Profile Visitor Tracking:**
  - Direct 1-on-1 messaging, photo sharing, and profile visit analytics.

---

## 🛠️ Tech Stack
- **Framework:** Laravel 11 / PHP 8.2+
- **Database:** MySQL
- **Real-Time Signaling:** Laravel Reverb / WebSockets / WebRTC RESTful Polling
- **Authentication:** Laravel Sanctum Bearer Tokens
- **Client App:** Flutter (Android & iOS)

---

## 🚀 Getting Started

### 1. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
php artisan migrate
```

### 2. Running Tests
```bash
vendor/bin/phpunit
```

### 3. API Documentation
Read [`CHINCHINS_MASTER_RESTFUL_API_DOCUMENTATION.md`](./CHINCHINS_MASTER_RESTFUL_API_DOCUMENTATION.md) for complete endpoint schemas and Flutter WebRTC client integration.
