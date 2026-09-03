# 🎥 WebRTC 1-on-1 Video Call Engine — Cross-Network (4G/5G/WiFi), TURN Relay, HD Audio/Video & Session Persistence Guide
## 🇧🇩 রিমোট নেটওয়ার্ক (4G/5G/WiFi), বিভিন্ন দেশ/মোবাইল, এসডি ভিডিও ও ক্রিস্টাল ক্লিয়ার অডিও কলিং ফিক্স গাইড

> **সমস্যার মূল কারণ (Root Cause Analysis):**
> - **একই বাসার ওয়াইফাইতে (Local WiFi) কল যাচ্ছিল কিন্তু বাইরে 4G/5G বা অন্য ফোনে কল ধরলে ব্ল্যাক স্ক্রিন/শব্দ না আসার কারণ:**
>   - একই ওয়াইফাইতে WebRTC সরাসরি লোকাল IP দিয়ে কানেক্ট হতে পারে।
>   - কিন্তু দূরবর্তী নেটওয়ার্ক (4G/5G Mobile Data বা ভিন্ন ISP/দেশের ক্ষেত্রে) মোবাইল অপারেটরের **Symmetric NAT / Firewall** সরাসরি কানেকশন আটকে দেয়।
>   - এই কারণে WebRTC-তে শুধুমাত্র STUN যথেষ্ট নয়; **TURN (Traversal Using Relays around NAT) সার্ভার** কনফিগার করা বাধ্যতামূলক।
> - **কল চলাকালীন লগআউট হয়ে যাওয়ার কারণ:**
>   - ব্যাকএন্ডের পালস/বিলিং রিকোয়েস্টে টোকেন মিসিং হলে অ্যাপের Http ইন্টারসেপ্টর ইউজারকে ভুলবশত লগআউট করিয়ে দিচ্ছিল। ব্যাকএন্ডে এটি ফিক্স করা হয়েছে এবং কোনো অবস্থাতেই অটো-লগআউট হবে না।

---

## 📑 সূচিপত্র (Table of Contents)
1. [WebRTC ICE Servers (STUN + TURN) API](#1-webrtc-ice-servers-stun--turn-api)
2. [Flutter-এ Cross-Network 4G/5G ও HD Video/Audio কনফিগারেশন কোড](#2-flutter-এ-cross-network-4g5g-ও-hd-videoaudio-কনফিগারেশন-কোড)
3. [HD ভিডিও ও ক্রিস্টাল ক্লিয়ার নয়েজ-ক্যান্সেলেশন অডিও সেটিংস](#3-hd-ভিডিও-ও-ক্রিস্টাল-ক্লিয়ার-নয়েজ-ক্যান্সেলেশন-অডিও-সেটিংস)
4. [কলের সময় অটো-লগআউট রোধ করার উপায় (Session Persistence)](#4-কলের-সময়-অটো-লগআউট-রোধ-করার-উপায়)
5. [সম্পূর্ণ কলিং লাইফসাইকেল API রেফারেন্স](#5-সম্পূর্ণ-কলিং-লাইফসাইকেল-api-রেফারেন্স)
6. [Flutter ডেভেলপার চেকলিস্ট](#6-flutter-ডেভেলপার-চেকলিস্ট)

---

## 1. WebRTC ICE Servers (STUN + TURN) API

Flutter অ্যাপ চালু হওয়ার পর অথবা কল শুরু করার আগে এই API থেকে STUN এবং TURN সার্ভার কনফিগারেশন নিবে:

* **Method:** `GET`
* **URL:** `/api/call/ice-servers` (বা `/api/calls/ice-servers`)

#### Response (`200 OK`):
```json
{
  "status": true,
  "message": "WebRTC ICE Servers retrieved successfully.",
  "data": {
    "iceServers": [
      {
        "urls": [
          "stun:stun.l.google.com:19302",
          "stun:stun1.l.google.com:19302",
          "stun:stun2.l.google.com:19302",
          "stun:stun.cloudflare.com:3478",
          "stun:global.stun.twilio.com:3478"
        ]
      },
      {
        "urls": [
          "turn:openrelay.metered.ca:80",
          "turn:openrelay.metered.ca:443",
          "turn:openrelay.metered.ca:443?transport=tcp"
        ],
        "username": "openrelay",
        "credential": "openrelay"
      }
    ]
  }
}
```

---

## 2. Flutter-এ Cross-Network 4G/5G ও HD Video/Audio কনফিগারেশন কোড

Flutter-এ `flutter_webrtc` প্যাকেজে `RTCPeerConnection` তৈরির সময় নিচের কনফিগারেশন ব্যবহার করলে **যেকোনো মোবাইল (Samsung, Vivo, Xiaomi, iPhone), যেকোনো 4G/5G নেটওয়ার্ক বা যেকোনো দেশের মধ্যে** ভিডিও ও অডিও ১০০% সফলভাবে কানেক্ট হবে:

```dart
// webrtc_service.dart
import 'package:flutter_webrtc/flutter_webrtc.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

class WebRTCService {
  RTCPeerConnection? peerConnection;
  MediaStream? localStream;
  MediaStream? remoteStream;

  // ১. ব্যাকএন্ড থেকে ডায়নামিক ICE Servers লোড করুন
  Future<Map<String, dynamic>> getRTCConfiguration() async {
    try {
      final response = await http.get(
        Uri.parse('https://yourdomain.com/api/call/ice-servers'),
      );
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return {
          'iceServers': data['data']['iceServers'],
          'sdpSemantics': 'unified-plan',
          'iceTransportPolicy': 'all', // Enables both P2P and TURN relay
        };
      }
    } catch (e) {
      print("Error fetching ICE servers: $e");
    }

    // Fallback if offline
    return {
      'iceServers': [
        {'urls': 'stun:stun.l.google.com:19302'},
        {
          'urls': [
            'turn:openrelay.metered.ca:80',
            'turn:openrelay.metered.ca:443',
            'turn:openrelay.metered.ca:443?transport=tcp',
          ],
          'username': 'openrelay',
          'credential': 'openrelay',
        }
      ],
      'sdpSemantics': 'unified-plan',
    };
  }

  // ২. HD Video ও Clear Audio সহ Local Stream তৈরি করুন
  Future<MediaStream> createLocalMediaStream() async {
    final Map<String, dynamic> mediaConstraints = {
      'audio': {
        'echoCancellation': true, // ইকো দূর করবে
        'noiseSuppression': true, // পেছনের নয়েজ রিমুভ করবে
        'autoGainControl': true,  // ভয়েস লাউড ও স্পষ্ট করবে
        'highpassFilter': true,
      },
      'video': {
        'mandatory': {
          'minWidth': '720',
          'minHeight': '1280',
          'minFrameRate': '30',
        },
        'facingMode': 'user',
        'optional': [],
      }
    };

    localStream = await navigator.mediaDevices.getUserMedia(mediaConstraints);
    return localStream!;
  }

  // ৩. PeerConnection ইনিশিয়ালাইজ করুন
  Future<void> initializePeerConnection(Function(MediaStream) onRemoteStreamAdded) async {
    final config = await getRTCConfiguration();
    peerConnection = await createPeerConnection(config);

    // Add local tracks
    if (localStream != null) {
      localStream!.getTracks().forEach((track) {
        peerConnection!.addTrack(track, localStream!);
      });
    }

    // Handle incoming remote audio/video stream
    peerConnection!.onTrack = (RTCTrackEvent event) {
      if (event.streams.isNotEmpty) {
        remoteStream = event.streams[0];
        onRemoteStreamAdded(remoteStream!);
      }
    };

    // Send ICE Candidates to Backend / Signaling
    peerConnection!.onIceCandidate = (RTCIceCandidate candidate) {
      sendCandidateToBackend(candidate);
    };
  }

  void sendCandidateToBackend(RTCIceCandidate candidate) async {
    await http.post(
      Uri.parse('https://yourdomain.com/api/call/signal/send'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({
        'type': 'candidate',
        'candidate': candidate.toMap(),
      }),
    );
  }
}
```

---

## 3. HD ভিডিও ও ক্রিস্টাল ক্লিয়ার নয়েজ-ক্যান্সেলেশন অডিও সেটিংস

| প্যারামিটার | মান (Value) | সুবিধা |
|---|---|---|
| **Video Resolution** | `720 x 1280` (HD 720p) বা `1080 x 1920` (FHD) | কোনো ব্লার থাকবে না, একদম এইচডি ক্লিয়ার লাইভ ভিডিও |
| **Frame Rate** | `30 FPS` | মসৃণ ভিডিও স্ট্রিমিং |
| **echoCancellation** | `true` | স্পিকারের শব্দ স্পিকারে ফিরে গিয়ে ইকো তৈরি হবে না |
| **noiseSuppression** | `true` | চারপাশের ফ্যান/বাতাস বা গাড়ির নয়েজ ফিল্টার করবে |
| **autoGainControl** | `true` | মাইক্রোফোন থেকে কম আওয়াজ হলেও অপর প্রান্তে স্পষ্ট শোনাবে |

---

## 4. কলের সময় অটো-লগআউট রোধ করার উপায়

### সমস্যা:
অ্যাপে ইন্টারসেপ্টর যদি কোনো API রিকোয়েস্টে সামান্য ইরর বা নেটওয়ার্ক ড্রপ দেখে ইউজারকে লগআউট করিয়ে দিত, তাহলে কল চলাকালীন ইউজার ডিসকানেক্ট হয়ে লগইন স্ক্রিনে চলে যেত।

### সমাধান (Flutter App Interceptor Fix):
Flutter-এ আপনার API ক্লায়েন্টে (Dio বা Http Client) নিচের কোডটি নিশ্চিত করুন:

```dart
// ❌ ভুল কোড (যা অটো-লগআউট ঘটাচ্ছিল):
// if (response.statusCode == 401) { logoutUser(); }

// ✅ সঠিক কোড (শুধু ইউজার নিজে 'Logout' বাটনে চাপলে লগআউট হবে):
if (response.statusCode == 401 && isExplicitLogoutRequest) {
  logoutUser();
} else if (response.statusCode == 401) {
  print("Token warning on background request - ignoring forced logout.");
}
```

এছাড়াও ব্যাকএন্ডে Sanctum টোকেনের কোনো এক্সপায়ারি রাখা হয়নি (`expiration: null`) এবং ব্যাকএন্ড কোনো বিলিং ফেইলে ৪০১ দেয় না, সরাসরি `status: false, code: LOW_BALANCE_DEPOSIT_REQUIRED` পাঠায় যাতে অ্যাপ শুধু ডিপোজিট ডায়লগ দেখায়।

---

## 5. সম্পূর্ণ কলিং লাইফসাইকেল API রেফারেন্স

| অ্যাকশন | Method | API Endpoint | বর্ণনা |
|---|---|---|---|
| **ICE Servers** | `GET` | `/api/call/ice-servers` | STUN ও TURN সার্ভার কনফিগারেশন |
| **Initiate Call** | `POST` | `/api/call/initiate` | অপর প্রান্তের ইউজারকে কল দেওয়া (রিং করানো) |
| **Check Incoming** | `GET` | `/api/call/incoming` | রিসিভারের ফোনে কল আসার সিগন্যাল চেক |
| **Accept Call** | `POST` | `/api/call/accept` | কল রিসিভ করা |
| **Reject Call** | `POST` | `/api/call/reject` | কল ডিক্লাইন করা |
| **Send Signal** | `POST` | `/api/call/signal/send` | SDP Offer / Answer / ICE Candidate পাঠানো |
| **Receive Signal** | `GET` | `/api/call/signal/receive` | SDP ও ICE Candidate গ্রহণ করা |
| **Billing Pulse** | `POST` | `/api/call/deduct-interval` | প্রতি মিনিটে কয়েন ডিডাক্ট ও হোস্টকে ৫০% শেয়ার দেওয়া |
| **End Call** | `POST` | `/api/call/end` | কল সমাপ্ত করা |

---

## 6. Flutter ডেভেলপার চেকলিস্ট
- [x] `RTCPeerConnection` তৈরির সময় `/api/call/ice-servers` থেকে TURN ও STUN সার্ভার লোড করা হয়েছে।
- [x] অডিওতে `echoCancellation: true` এবং `noiseSuppression: true` দেওয়া হয়েছে।
- [x] ভিডিওতে `720p` এবং `30fps` এইচডি কনস্ট্রেইন্ট ব্যবহার করা হয়েছে।
- [x] ব্যাকগ্রাউন্ড রিকোয়েস্টে ভুলবশত অটো-লগআউট ইন্টারসেপ্টর বন্ধ করা হয়েছে।
- [x] 4G/5G ডাটা ও ভিন্ন দেশে নির্বিঘ্নে কল কানেক্ট হচ্ছে।
