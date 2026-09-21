# 🎙️ Chinchins Live - Complete RESTful API & Real-Time Documentation

---

## 📌 ১. লেটেস্ট আপডেট ও সমস্যা সমাধান (Latest Updates)

1. **405 Method Not Allowed সমাধান**:
   - `POST /api/party-rooms/{id}/seat-requests` এবং `POST /api/party-rooms/{id}/seat-requests/respond` রাউট সংযুক্ত করা হয়েছে।
2. **ডুপ্লিকেট মেসেজ রোধ**:
   - `PartyRoomMessageSent` ইভেন্টটিতে `->toOthers()` ব্যবহার করা হয়েছে যাতে প্রেরক নিজে মেসেজ দুবার রিসিভ না করে।
3. **গিফট অ্যানিমেশন ব্রডকাস্ট (LiveGiftSentEvent)**:
   - ইউজার গিফট পাঠালে তাৎক্ষণিকভাবে কয়েন ট্রানজ্যাকশন সম্পন্ন হয় এবং রুমে থাকা সবার জন্য `LiveGiftSentEvent` ব্রডকাস্ট হয় (অ্যানিমেশন লিঙ্ক ও সাউন্ড সহ)।
4. **সিট রিকোয়েস্ট ইভেন্ট (SeatRequestReceivedEvent & SeatRequestEvent)**:
   - সিট রিকোয়েস্ট পাঠানোর সাথে সাথে হোস্টের কাছে রিয়েল-টাইম পুশ পৌঁছে যায়।

---

## 🌐 ২. পার্টি রুম ও ভয়েস চ্যাট এপিআই (Party Room APIs)

### ২.১ রুমের বিস্তারিত তথ্য (Get Room Details)
- **Method**: `GET`
- **URL**: `https://chinchins.live/api/party-rooms/{id}`
- **Headers**:
  ```http
  Authorization: Bearer {token}
  Accept: application/json
  ```
- **Response**:
```json
{
  "success": true,
  "status": true,
  "token": "eyJhbGciOi...",
  "livekit_token": "eyJhbGciOi...",
  "livekit_url": "wss://chinchins.live/livekit",
  "can_publish": true,
  "data": {
    "room": {
      "id": 22,
      "room_id": "89201481",
      "room_title": "Live Party Room",
      "room_type": "voice",
      "topic_tag": "Singing",
      "channel_name": "party_voice_89201481",
      "max_seats": 8,
      "occupied_seats_count": 1,
      "online_members_count": 12,
      "is_host": true,
      "host": {
        "id": 101,
        "account_id": "84920183",
        "name": "Nazmul",
        "display_name": "Nazmul",
        "avatar": "https://chinchins.live/uploads/profiles/nazmul.jpg",
        "avatar_url": "https://chinchins.live/uploads/profiles/nazmul.jpg",
        "avatar_frame_url": "https://chinchins.live/uploads/bases/crown.png",
        "level": 15,
        "coins": 54000
      },
      "seats": [
        {
          "seat_index": 1,
          "role": "host",
          "status": "occupied",
          "is_occupied": true,
          "is_muted": false,
          "user": {
            "id": 101,
            "account_id": "84920183",
            "name": "Nazmul",
            "display_name": "Nazmul",
            "avatar": "https://chinchins.live/uploads/profiles/nazmul.jpg",
            "avatar_url": "https://chinchins.live/uploads/profiles/nazmul.jpg",
            "level": 15,
            "coins": 54000,
            "is_host": true
          }
        },
        {
          "seat_index": 2,
          "role": "guest",
          "status": "empty",
          "is_occupied": false,
          "is_muted": false,
          "user": null
        }
      ]
    }
  }
}
```

---

### ২.২ সিটে বসার রিকোয়েস্ট পাঠানো (Request a Seat)
- **Method**: `POST`
- **URL**: `https://chinchins.live/api/party-rooms/{id}/seat-requests`
  *(বিকল্প: `https://chinchins.live/api/party-rooms/{id}/request-seat`)*
- **Headers**:
  ```http
  Authorization: Bearer {token}
  Accept: application/json
  Content-Type: application/json
  ```
- **Body**:
  ```json
  {
    "seat_index": 2
  }
  ```
- **Response**:
```json
{
  "success": true,
  "status": true,
  "message": "Seat request sent to host successfully.",
  "data": {
    "invitation_id": 14,
    "request_id": 14,
    "seat_index": 2,
    "user": {
      "id": 105,
      "account_id": "77391204",
      "name": "Ruma",
      "display_name": "Ruma",
      "avatar": "https://chinchins.live/uploads/profiles/ruma.jpg",
      "avatar_url": "https://chinchins.live/uploads/profiles/ruma.jpg",
      "level": 4
    }
  }
}
```

---

### ২.৩ হোস্টের সিট রিকোয়েস্ট গ্রহণ / বাতিল করা (Respond to Seat Request)
- **Method**: `POST`
- **URL**: `https://chinchins.live/api/party-rooms/{id}/seat-requests/respond`
  *(বিকল্প: `https://chinchins.live/api/party-rooms/{id}/seat-requests/{requestId}/respond`)*
- **Body**:
  ```json
  {
    "request_id": 14,
    "action": "accept"
  }
  ```
- **Response (Accept)**:
```json
{
  "success": true,
  "status": true,
  "action": "accepted",
  "message": "Seat request accepted. Ruma is now on Seat #2.",
  "seat_index": 2,
  "user_id": 105,
  "token": "eyJhbGciOi...",
  "livekit_token": "eyJhbGciOi...",
  "livekit_url": "wss://chinchins.live/livekit",
  "can_publish": true,
  "data": {
    "seat_index": 2,
    "user": {
      "id": 105,
      "account_id": "77391204",
      "name": "Ruma",
      "display_name": "Ruma",
      "avatar": "https://chinchins.live/uploads/profiles/ruma.jpg",
      "avatar_url": "https://chinchins.live/uploads/profiles/ruma.jpg",
      "level": 4
    },
    "can_publish": true
  }
}
```

---

### ২.৪ রুমে মেসেজ পাঠানো (Send In-Room Chat Message)
- **Method**: `POST`
- **URL**: `https://chinchins.live/api/party-rooms/{id}/messages/send`
  *(বিকল্প: `https://chinchins.live/api/party-rooms/{id}/send-message`)*
- **Body**:
  ```json
  {
    "message": "Hello everyone! 🎤"
  }
  ```
- **Response**:
```json
{
  "success": true,
  "status": true,
  "message": "Message sent successfully.",
  "data": {
    "id": 892,
    "room_id": "89201481",
    "type": "text",
    "message": "Hello everyone! 🎤",
    "created_at": "2026-09-21T21:40:00Z",
    "sender": {
      "id": 105,
      "name": "Ruma",
      "avatar_url": "https://chinchins.live/uploads/profiles/ruma.jpg"
    }
  }
}
```

---

### ২.৫ রুমে গিফট পাঠানো (Send In-Room Gift)
- **Method**: `POST`
- **URL**: `https://chinchins.live/api/party-rooms/{id}/send-gift`
  *(বিকল্প: `https://chinchins.live/api/party-rooms/{id}/gift`)*
- **Body**:
  ```json
  {
    "gift_id": 12,
    "receiver_id": 101,
    "count": 1
  }
  ```
- **Response**:
```json
{
  "success": true,
  "status": true,
  "message": "Gift Sports Car sent successfully!",
  "data": {
    "remaining_coins": 18200,
    "total_cost": 500,
    "gift": {
      "id": 12,
      "name": "Sports Car",
      "icon_url": "https://chinchins.live/uploads/gifts/car.png",
      "animation_url": "https://chinchins.live/uploads/gifts/car.svga",
      "count": 1
    },
    "receiver": {
      "id": 101,
      "name": "Nazmul"
    }
  }
}
```

---

## 📡 ৩. রিয়েল-টাইম সকেট ইভেন্ট (Reverb / Pusher WebSockets)

| Channel Name | Event Name (`broadcastAs`) | বিবরণ (Description) |
|---|---|---|
| `party-room.{roomId}` / `party.{roomId}` | `SeatRequestReceivedEvent` / `seat.requested` | নতুন সিট রিকোয়েস্ট আসলে হোস্টের স্ক্রিনে পপআপ দেখাবে |
| `party-room.{roomId}` / `party.{roomId}` | `SeatUpdatedEvent` | সিট দখল, খালি হওয়া, বা মিউট স্ট্যাটাস পরিবর্তন |
| `party-room.{roomId}` / `party.{roomId}` | `PartyRoomMessageSent` | রুমে নতুন চ্যাট মেসেজ আসলে (সেন্ডার বাদে বাকিরা রিসিভ করবে) |
| `party-room.{roomId}` / `live-stream.{roomId}` | `gift.received` (`LiveGiftSentEvent`) | গিফট অ্যানিমেশন ও সাউন্ড এফেক্ট প্লে করার জন্য |

---

## 📱 ৪. Flutter Integration Code Snippet

```dart
// 1. Listen for Seat Requests (Host screen)
pusher.subscribe(
  channelName: 'party-room.$roomId',
  onEvent: (event) {
    if (event.eventName == 'SeatRequestReceivedEvent' || event.eventName == 'seat.requested') {
      final data = jsonDecode(event.data);
      showSeatRequestDialog(data);
    }
    if (event.eventName == 'PartyRoomMessageSent') {
      final msg = jsonDecode(event.data);
      addMessageToChatList(msg);
    }
    if (event.eventName == 'SeatUpdatedEvent') {
      final seatData = jsonDecode(event.data);
      updateSeatState(seatData);
    }
    if (event.eventName == 'gift.received') {
      final giftData = jsonDecode(event.data);
      playGiftAnimation(giftData['animation_url']);
    }
  },
);
```
