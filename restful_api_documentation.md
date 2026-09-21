# 🚀 Chinchins Live — Complete RESTful API Documentation

Comprehensive RESTful API documentation for **Chinchins Live** mobile applications (Flutter/Android/iOS) and web client.

---

## 📑 Table of Contents
1. [Authentication & User Profile](#1-authentication--user-profile)
2. [🎙️ Voice Party Room & 📹 Multi-Guest Video Stage (4–16 Seats)](#2-voice-party-room--multi-guest-video-stage-416-seats)
3. [💬 In-Room Chat Stream & System Messages](#3-in-room-chat-stream--system-messages)
4. [🎁 Virtual Gifting & Diamond Split Billing Engine](#4-virtual-gifting--diamond-split-billing-engine)
5. [🔥 Firebase Multi-App Push Notifications](#5-firebase-multi-app-push-notifications)
6. [💰 Wallet, Reseller Deposits & Withdrawals](#6-wallet-reseller-deposits--withdrawals)
7. [⚡ WebSocket LiveKit WebRTC & Reverb Events](#7-websocket-livekit-webrtc--reverb-events)

---

## 1. Authentication & User Profile
- `POST /api/login` — User login
- `POST /api/register` — User registration
- `GET /api/user` — Authenticated user profile
- `POST /api/update-profile` — Update name, avatar, bio

---

## 2. 🎙️ Voice Party Room & 📹 Multi-Guest Video Stage (4–16 Seats)

For detailed endpoint documentation, see [voice_chatroom_and_video_streaming_restful_api.md](file:///f:/Chinchins-live-website/voice_chatroom_and_video_streaming_restful_api.md).

### Main Endpoints:
- `GET /api/party-rooms` — Browse active live voice & video rooms.
- `GET /api/party-rooms/config` — Global party room configuration and topic tags.
- `POST /api/party-rooms/create` — Host creates a new 4-5 video room or 8-16 voice stage.
- `GET /api/party-rooms/{id}` — Get complete live room state, stage grid, and LiveKit WebRTC token.
- `POST /api/party-rooms/{id}/join` — Join room as viewer/audience.
- `POST /api/party-rooms/{id}/leave` — Leave room.
- `POST /api/party-rooms/{id}/end` — Host ends the party room.
- `POST /api/party-rooms/{id}/take-seat` — Occupy an open guest seat (`can_publish = true`).
- `POST /api/party-rooms/{id}/leave-seat` — Step down from seat back to audience.
- `POST /api/party-rooms/{id}/speaking` — Broadcast real-time green pulsing wave halo speaking indicator.
- `GET /api/party-rooms/{id}/seat-requests` — Host-only speaker queue list.
- `POST /api/party-rooms/{id}/seat-requests/{requestId}/respond` — Host Accept (`"accept"`) or Reject (`"reject"`).
- `POST /api/party-rooms/{id}/mute-seat` — Host mutes/unmutes speaker seat.
- `POST /api/party-rooms/{id}/kick-seat` — Host removes guest from seat.
- `POST /api/party-rooms/{id}/toggle-mic` — Toggle microphone.
- `POST /api/party-rooms/{id}/toggle-video` — Toggle camera feed.

---

## 3. 💬 In-Room Chat Stream & System Messages
- `GET /api/party-rooms/{id}/messages` — Paginated in-room message feed.
- `POST /api/party-rooms/{id}/send-message` — Send text message or photo.
- `POST /api/party-rooms/{id}/send-gift` — Send virtual gift animation with coins deduction.

---

## 4. 🎁 Virtual Gifting & Diamond Split Billing Engine
- `POST /api/party-rooms/{id}/deduct-interval` — 1-minute billing heartbeat with 50/50 Host/Admin revenue split.

---

## 5. 🔥 Firebase Multi-App Push Notifications
- For full Firebase FCM v1 and OneSignal push notification integration, see [firebase_full_documentation_api.md](file:///f:/Chinchins-live-website/firebase_full_documentation_api.md).

---

## 6. 💰 Wallet, Reseller Deposits & Withdrawals
- `GET /api/wallet/balance` — Get user coin balance.
- `POST /api/deposit/submit` — Submit manual deposit.
- `POST /api/withdraw/submit` — Submit withdrawal request.
