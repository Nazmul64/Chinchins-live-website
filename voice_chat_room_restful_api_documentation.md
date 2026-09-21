# 🎙️ Chinchins Live - Voice Party Room RESTful API & Flutter Integration Guide

---

## 📌 ১. সমস্যা সমাধান ও ব্যাকএন্ড আপডেট সারাংশ (Bug Fixes Summary)

1. **হোস্টের ছবি (Avatar) ও প্রোফাইল ডাটা ঠিক করা হয়েছে**:
   - `host` অবজেক্টে `avatar` এবং `avatar_url` সরাসরি সম্পূর্ণ ইমেজ লিঙ্ক প্রদান করা হয়েছে। কোনো কারণে ছবি না থাকলে স্বয়ংক্রিয়ভাবে ডিফল্ট অবতার পাঠানো হচ্ছে (কোনো `"H"` প্লেসহোল্ডার আসবে না)।
2. **সিট ২ থেকে ৮ গ্রিড স্টেট (Empty vs Occupied)**:
   - সিট খালি থাকলে `user: null` এবং `is_occupied: false` থাকে (ফ্লাটারে `+` ও `Join Now` বাটন দেখাবে)।
   - হোস্ট সিট রিকোয়েস্ট গ্রহণ (Accept) করলে ওই সিটে যুক্ত ইউজারের প্রোফাইল ছবি (`avatar`/`avatar_url`), পূর্ণ নাম (`name`/`display_name`), লেভেল ইত্যাদি ডাটা পাঠানো হয়।
3. **সিট রিকোয়েস্ট সার্ভার এরর (500 Error) সমাধান**:
   - `/api/party-rooms/{id}/request-seat` এ `seat_index` ফাঁকা থাকলে স্বয়ংক্রিয়ভাবে পরবর্তী খালি সিট অ্যাসাইন করে ডাটাবেসে সেভ হবে। কোনো এসকিউএল এরর ঘটবে না।
4. **ভয়েস চ্যাট লাইভকিট (LiveKit) অডিও পারমিশন ও কানেকশন (`setCanPublish(true)`)**:
   - লাইভকিট টোকেন জেনারেটরে সরাসরি `$grant->setCanPublish(true)` নিশ্চিত করা হয়েছে। এর ফলে লাইভকিট SFU সার্ভার থেকে আর কোনো `no permission to publish track` এরর আসবে না এবং ইউজাররা সিটে বসে স্বচ্ছন্দে কথা বলতে ও সবার কথা শুনতে পারবে।

---

## 🌐 ২. এন্ডপয়েন্ট ও রেসপন্স ডকুমেন্টেশন (RESTful Endpoints)

### ক. রুমের বিস্তারিত তথ্য (Get Room Details)
- **Method**: `GET`
- **URL**: `https://chinchins.live/api/party-rooms/{id}`
- **Headers**:
  ```http
  Authorization: Bearer {token}
  Accept: application/json
  ```
- **Response Example**:
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
      "id": 1,
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
        "name": "Host Name",
        "display_name": "Host Name",
        "avatar": "https://chinchins.live/uploads/profiles/host_avatar.jpg",
        "avatar_url": "https://chinchins.live/uploads/profiles/host_avatar.jpg",
        "avatar_frame_url": "https://chinchins.live/uploads/bases/gold_crown.png",
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
          "is_video_muted": false,
          "user": {
            "id": 101,
            "account_id": "84920183",
            "name": "Host Name",
            "display_name": "Host Name",
            "avatar": "https://chinchins.live/uploads/profiles/host_avatar.jpg",
            "avatar_url": "https://chinchins.live/uploads/profiles/host_avatar.jpg",
            "avatar_frame_url": "https://chinchins.live/uploads/bases/gold_crown.png",
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
          "is_video_muted": false,
          "user": null
        },
        {
          "seat_index": 3,
          "role": "guest",
          "status": "empty",
          "is_occupied": false,
          "is_muted": false,
          "is_video_muted": false,
          "user": null
        }
      ]
    },
    "token": "eyJhbGciOi...",
    "livekit_token": "eyJhbGciOi...",
    "livekit_url": "wss://chinchins.live/livekit",
    "can_publish": true
  }
}
```

---

### খ. সিট রিকোয়েস্ট পাঠানো (Request a Seat)
- **Method**: `POST`
- **URL**: `https://chinchins.live/api/party-rooms/{id}/request-seat`
- **Body (JSON / Form-Data)**:
  ```json
  {
    "seat_index": 2
  }
  ```
  *(যদি `seat_index` না পাঠানো হয়, তবে ব্যাকএন্ড স্বয়ংক্রিয়ভাবে প্রথম খালি সিটটি নির্বাচন করবে)*
- **Response**:
```json
{
  "success": true,
  "status": true,
  "message": "Seat request sent to host successfully.",
  "data": {
    "invitation_id": 5,
    "request_id": 5,
    "seat_index": 2,
    "user": {
      "id": 105,
      "account_id": "77391204",
      "name": "Arif",
      "display_name": "Arif",
      "avatar": "https://chinchins.live/uploads/profiles/arif.jpg",
      "avatar_url": "https://chinchins.live/uploads/profiles/arif.jpg",
      "level": 5
    }
  }
}
```

---

### গ. হোস্টের সিট রিকোয়েস্ট গ্রহণ / বাতিল করা (Respond to Seat Request)
- **Method**: `POST`
- **URL**: `https://chinchins.live/api/party-rooms/{id}/respond-seat-request`
- **Body**:
  ```json
  {
    "request_id": 5,
    "action": "accept"
  }
  ```
  *(অ্যাকশন মান: `accept` অথবা `reject`)*
- **Response (Accept)**:
```json
{
  "success": true,
  "status": true,
  "action": "accepted",
  "message": "Seat request accepted. Arif is now on Seat #2.",
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
      "name": "Arif",
      "display_name": "Arif",
      "avatar": "https://chinchins.live/uploads/profiles/arif.jpg",
      "avatar_url": "https://chinchins.live/uploads/profiles/arif.jpg",
      "level": 5
    },
    "can_publish": true
  }
}
```

---

## 📱 ৩. Flutter UI ও LiveKit অডিও ইন্টিগ্রেশন গাইড (Flutter Implementation)

### ক. সিট গ্রিড উইজেট (Seat Grid Widget Logic)
```dart
Widget buildSeatItem(Map<String, dynamic> seat) {
  final bool isOccupied = seat['is_occupied'] == true && seat['user'] != null;
  final int seatIndex = seat['seat_index'] ?? 1;
  final user = seat['user'];

  if (isOccupied) {
    // 👤 সিটে ইউজার থাকলে: প্রোফাইল পিকচার ও নাম
    final String avatarUrl = user['avatar_url'] ?? user['avatar'] ?? '';
    final String name = user['display_name'] ?? user['name'] ?? 'User';
    final bool isHost = seatIndex == 1 || (user['is_host'] == true);

    return Column(
      children: [
        Stack(
          alignment: Alignment.center,
          children: [
            CircleAvatar(
              radius: 30,
              backgroundImage: avatarUrl.isNotEmpty ? NetworkImage(avatarUrl) : null,
              child: avatarUrl.isEmpty ? Text(name.substring(0, 1).toUpperCase()) : null,
            ),
            if (isHost)
              Positioned(
                top: 0,
                child: Image.asset('assets/images/crown.png', width: 20),
              ),
          ],
        ),
        const SizedBox(height: 4),
        Text(
          name,
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
          style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold),
        ),
      ],
    );
  } else {
    // ➕ সিট খালি থাকলে: + বাটন এবং 'Join Now'
    return InkWell(
      onTap: () => requestSeat(seatIndex),
      child: Column(
        children: [
          Container(
            width: 60,
            height: 60,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              border: Border.all(color: Colors.blueAccent.withOpacity(0.5), width: 1.5),
            ),
            child: const Icon(Icons.add, color: Colors.blueAccent, size: 28),
          ),
          const SizedBox(height: 4),
          Text(
            'Seat $seatIndex',
            style: const TextStyle(color: Colors.white70, fontSize: 11),
          ),
          const Text(
            'Join Now',
            style: TextStyle(color: Colors.cyanAccent, fontSize: 10, fontWeight: FontWeight.bold),
          ),
        ],
      ),
    );
  }
}
```

### খ. LiveKit অডিও রুম কানেকশন (LiveKit Room Connection Logic)
```dart
import 'package:livekit_client/livekit_client.dart';

Future<void> connectToLiveKitRoom(String livekitUrl, String token, bool canPublish) async {
  final roomOptions = RoomOptions(
    adaptiveStream: true,
    dynacast: true,
    defaultAudioPublishOptions: AudioPublishOptions(
      dtx: true,
      audioBitrate: 64000,
    ),
  );

  final room = Room(roomOptions: roomOptions);
  await room.connect(livekitUrl, token);

  // যদি হোস্ট বা সিট মেম্বার হয়, স্বয়ংক্রিয়ভাবে অডিও পাবলিশ চালু হবে
  if (canPublish) {
    await room.localParticipant?.setMicrophoneEnabled(true);
  }
}
```
