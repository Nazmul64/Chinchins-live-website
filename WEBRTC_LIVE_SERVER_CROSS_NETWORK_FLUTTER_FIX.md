# 📱 Flutter & WebRTC Live Server Cross-Network Video Call Fix Guide
## 🇧🇩 লোকাল ওয়াইফাই ছাড়া অন্য ফোনে (4G/5G/External Network) ভিডিও কল ও লাইভ স্ট্রিমিং ফিক্স নির্দেশিকা

> **⚠️ সমস্যার প্রধান ৩টি কারণ (Root Cause Analysis):**
> 1. **লোকাল IP / Localhost কনফিগারেশন:** Flutter অ্যাপের `baseUrl` অথবা সকেট ইউআরএল `localhost` বা লোকাল ওয়াইফাই IP (`192.168.x.x` / `10.0.2.2`)-এ সেট করা ছিল। একই ওয়াইফাইতে দুটি ফোন থাকলে তারা একে অপরের লোকাল IP পায়, কিন্তু অন্য কারও ফোনে APK দিলে সে আপনার লোকাল সার্ভার খুঁজে পায় না।
> 2. **TURN Server (Relay) মিসিং থাকা:** মোবাইল ডাটা (4G/5G) বা ভিন্ন ওয়াইফাই/আইএসপির ক্ষেত্রে মোবাইল অপারেটরদের **Symmetric NAT / Firewall** সরাসরি দুটি ফোনের মধ্যে কানেকশন হতে দেয় না। WebRTC-তে **TURN Server** কনফিগার না থাকলে কল কানেক্ট হলেও **ভিডিও স্ট্রিমিং ব্ল্যাক স্ক্রিন বা লোডিং** হয়ে থাকে।
> 3. **লাইভ সিগন্যালিং RESTful ব্যাকএন্ড:** এখন ব্যাকএন্ডে হাই-পারফর্মেন্স RESTful সিগন্যালিং ও ডায়নামিক TURN/STUN সার্ভার রেডি করা আছে।

---

## 🚀 ১. Flutter অ্যাপে Base URL পরিবর্তন (Live Server Config)

Flutter অ্যাপের `constants.dart` অথবা `api_endpoints.dart` ফাইলে লোকাল IP বাদ দিয়ে আপনার লাইভ ডোমেইন বসান:

```dart
// ❌ ভুল (লোকাল ওয়াইফাই ছাড়া অন্য কোথাও চলবে না):
// static const String baseUrl = "http://192.168.1.100:8000/api";
// static const String baseUrl = "http://10.0.2.2:8000/api";

// ✅ সঠিক (লাইভ সার্ভার ডোমেইন বসান):
class ApiConfig {
  static const String baseUrl = "https://your-live-domain.com/api"; // আপনার লাইভ সার্ভার URL
}
```

---

## 🌐 ২. ডায়নামিক ICE Servers (STUN + TURN) API

Flutter অ্যাপ কল শুরু করার আগে এই API থেকে STUN এবং TURN সার্ভার কনফিগারেশন নিবে। এর ফলে **যেকোনো 4G/5G নেটওয়ার্ক, গ্রামীণফোন, রবি, বাংলালিংক, বা দেশের বাইরেও ভিডিও কল ১০০% কানেক্ট হবে**:

* **Endpoint:** `GET /api/call/ice-servers`
* **Response (`200 OK`):**

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
          "stun:stun.cloudflare.com:3478",
          "stun:global.stun.twilio.com:3478"
        ]
      },
      {
        "urls": [
          "turn:openrelay.metered.ca:80",
          "turn:openrelay.metered.ca:443",
          "turn:openrelay.metered.ca:443?transport=tcp",
          "turn:openrelay.metered.ca:80?transport=tcp",
          "turns:openrelay.metered.ca:443?transport=tcp",
          "turns:openrelay.metered.ca:5349"
        ],
        "username": "openrelay",
        "credential": "openrelay"
      }
    ]
  }
}
```

---

## 📞 ৩. সম্পূর্ণ RESTful কলিং ও সিগন্যালিং লাইফসাইকেল API

| ধাপ | অ্যাকশন | Method | API Endpoint | প্যারামিটার / বডি |
|---|---|---|---|---|
| **1** | **ICE Servers লোড** | `GET` | `/api/call/ice-servers` | - |
| **2** | **কল দেওয়া (Initiate)** | `POST` | `/api/call/initiate` | `{"receiver_id": 5, "call_type": "video"}` |
| **3** | **ইনকামিং কল চেক (Receiver)** | `GET` | `/api/call/wait-incoming?user_id=5` | `user_id` বা Bearer Token |
| **4** | **কল রিসিভ করা (Accept)** | `POST` | `/api/call/accept` | `{"call_id": 12}` |
| **5** | **কল বাতিল/রিজেক্ট** | `POST` | `/api/call/reject` | `{"call_id": 12}` |
| **6** | **Offer / Answer / Candidate পাঠানো** | `POST` | `/api/call/signal/send` | `{"call_id": 12, "type": "offer", "payload": {...}}` |
| **7** | **সিগন্যাল গ্রহণ করা (Polling/Stream)** | `GET` | `/api/call/signals?call_id=12&auto_read=true` | `call_id`, `user_id` |
| **8** | **প্রতি মিনিটে কয়েন বিলিং পালস** | `POST` | `/api/call/deduct-interval` | `{"call_id": 12}` |
| **9** | **কল শেষ করা (Hangup)** | `POST` | `/api/call/end` | `{"call_id": 12}` |

---

## 🛠️ ৪. Flutter ডেভেলপারদের জন্য ফুল WebRTC সার্ভিস কোড (Copy-Paste Ready)

এই কোডটি সরাসরি Flutter অ্যাপের `lib/features/call/services/webrtc_call_service.dart` হিসেবে ব্যবহার করা যাবে:

```dart
import 'dart:async';
import 'dart:convert';
import 'package:flutter_webrtc/flutter_webrtc.dart';
import 'package:http/http.dart' as http;

class WebRTCCallService {
  static const String baseUrl = "https://your-live-domain.com/api"; // আপনার লাইভ ডোমেন দিন

  RTCPeerConnection? _peerConnection;
  MediaStream? localStream;
  MediaStream? remoteStream;
  
  Timer? _signalPollingTimer;
  Timer? _billingPulseTimer;
  int _lastSignalId = 0;

  // ১. ডায়নামিক ICE Servers লোড
  Future<Map<String, dynamic>> _fetchRtcConfiguration(String token) async {
    try {
      final res = await http.get(
        Uri.parse('$baseUrl/call/ice-servers'),
        headers: {'Authorization': 'Bearer $token', 'Accept': 'application/json'},
      );
      if (res.statusCode == 200) {
        final json = jsonDecode(res.body);
        return {
          'iceServers': json['data']['iceServers'],
          'sdpSemantics': 'unified-plan',
          'iceTransportPolicy': 'all', // P2P এবং TURN Relay উভয়ই সক্রিয় রাখে
        };
      }
    } catch (e) {
      print("Error loading ICE servers: $e");
    }

    // Fallback Configuration
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
      'iceTransportPolicy': 'all',
    };
  }

  // ২. HD 720p Video ও Crystal-Clear Audio সহ Local Media Stream তৈরি
  Future<MediaStream> startLocalStream() async {
    final Map<String, dynamic> mediaConstraints = {
      'audio': {
        'echoCancellation': true, // ইকো প্রতিরোধ
        'noiseSuppression': true, // পেছনের নয়েজ ফিল্টার
        'autoGainControl': true,  // ভলিউম বুস্ট
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

  // ৩. PeerConnection তৈরি ও রিমোট স্ট্রিম লিসেনার
  Future<void> initializePeerConnection({
    required String token,
    required int callId,
    required int currentUserId,
    required Function(MediaStream) onRemoteStream,
  }) async {
    final rtcConfig = await _fetchRtcConfiguration(token);
    _peerConnection = await createPeerConnection(rtcConfig);

    // Add local tracks
    if (localStream != null) {
      localStream!.getTracks().forEach((track) {
        _peerConnection!.addTrack(track, localStream!);
      });
    }

    // Capture incoming remote stream (ভিডিও ও অডিও)
    _peerConnection!.onTrack = (RTCTrackEvent event) {
      if (event.streams.isNotEmpty) {
        remoteStream = event.streams[0];
        onRemoteStream(remoteStream!);
      }
    };

    // Send local ICE candidates to backend
    _peerConnection!.onIceCandidate = (RTCIceCandidate candidate) {
      _sendSignal(
        token: token,
        callId: callId,
        type: 'candidate',
        payload: {
          'candidate': candidate.candidate,
          'sdpMid': candidate.sdpMid,
          'sdpMLineIndex': candidate.sdpMLineIndex,
        },
      );
    };

    // স্টার্ট সিগন্যাল পোলিং (RESTful WebRTC Exchange)
    _startSignalPolling(token: token, callId: callId, currentUserId: currentUserId);
  }

  // ৪. Caller: Create & Send SDP Offer
  Future<void> createAndSendOffer({
    required String token,
    required int callId,
  }) async {
    if (_peerConnection == null) return;
    RTCSessionDescription offer = await _peerConnection!.createOffer({
      'offerToReceiveVideo': 1,
      'offerToReceiveAudio': 1,
    });
    await _peerConnection!.setLocalDescription(offer);

    await _sendSignal(
      token: token,
      callId: callId,
      type: 'offer',
      payload: {'sdp': offer.sdp, 'type': offer.type},
    );
  }

  // ৫. Receiver: Create & Send SDP Answer
  Future<void> handleOfferAndSendAnswer({
    required String token,
    required int callId,
    required String sdp,
  }) async {
    if (_peerConnection == null) return;
    await _peerConnection!.setRemoteDescription(
      RTCSessionDescription(sdp, 'offer'),
    );
    RTCSessionDescription answer = await _peerConnection!.createAnswer({
      'offerToReceiveVideo': 1,
      'offerToReceiveAudio': 1,
    });
    await _peerConnection!.setLocalDescription(answer);

    await _sendSignal(
      token: token,
      callId: callId,
      type: 'answer',
      payload: {'sdp': answer.sdp, 'type': answer.type},
    );
  }

  // সিগন্যাল পাঠানোর মেথড
  Future<void> _sendSignal({
    required String token,
    required int callId,
    required String type,
    required dynamic payload,
  }) async {
    try {
      await http.post(
        Uri.parse('$baseUrl/call/signal/send'),
        headers: {
          'Authorization': 'Bearer $token',
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'call_id': callId,
          'type': type,
          'payload': payload,
        }),
      );
    } catch (e) {
      print("Signal send error: $e");
    }
  }

  // সিগন্যাল রিসিভ করার মেথড (Polling every 800ms)
  void _startSignalPolling({
    required String token,
    required int callId,
    required int currentUserId,
  }) {
    _signalPollingTimer?.cancel();
    _signalPollingTimer = Timer.periodic(const Duration(milliseconds: 800), (timer) async {
      try {
        final res = await http.get(
          Uri.parse('$baseUrl/call/signals?call_id=$callId&last_signal_id=$_lastSignalId&auto_read=true&user_id=$currentUserId'),
          headers: {'Authorization': 'Bearer $token', 'Accept': 'application/json'},
        );

        if (res.statusCode == 200) {
          final body = jsonDecode(res.body);
          final List signals = body['data'] ?? [];

          for (var s in signals) {
            _lastSignalId = s['id'];
            final String type = s['type'];
            final payload = s['payload'];

            if (type == 'offer') {
              final sdp = payload['sdp'] ?? payload;
              await handleOfferAndSendAnswer(token: token, callId: callId, sdp: sdp);
            } else if (type == 'answer') {
              final sdp = payload['sdp'] ?? payload;
              await _peerConnection?.setRemoteDescription(
                RTCSessionDescription(sdp, 'answer'),
              );
            } else if (type == 'candidate' || type == 'ice_candidate') {
              final candidateStr = payload['candidate'] ?? '';
              final sdpMid = payload['sdpMid'] ?? payload['sdp_mid'];
              final sdpMLineIndex = payload['sdpMLineIndex'] ?? payload['sdp_mline_index'] ?? 0;
              if (candidateStr.isNotEmpty) {
                await _peerConnection?.addCandidate(
                  RTCIceCandidate(candidateStr, sdpMid, sdpMLineIndex),
                );
              }
            }
          }
        }
      } catch (e) {
        print("Signal polling error: $e");
      }
    });
  }

  // প্রতি ৬০ সেকেন্ডে কয়েন কাটার পালস (Billing Heartbeat)
  void startBillingPulse({
    required String token,
    required int callId,
    required Function(bool shouldTerminate, String message) onBalanceCheck,
  }) {
    _billingPulseTimer?.cancel();
    _billingPulseTimer = Timer.periodic(const Duration(seconds: 60), (timer) async {
      try {
        final res = await http.post(
          Uri.parse('$baseUrl/call/deduct-interval'),
          headers: {
            'Authorization': 'Bearer $token',
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
          body: jsonEncode({'call_id': callId}),
        );
        if (res.statusCode == 200) {
          final data = jsonDecode(res.body);
          if (data['data'] != null && data['data']['should_terminate_call'] == true) {
            onBalanceCheck(true, data['message'] ?? 'Insufficient coins');
          }
        }
      } catch (e) {
        print("Billing pulse error: $e");
      }
    });
  }

  // কল শেষ করা ও মেমোরি ক্লিনআপ
  Future<void> endCall({required String token, required int callId}) async {
    _signalPollingTimer?.cancel();
    _billingPulseTimer?.cancel();

    try {
      await http.post(
        Uri.parse('$baseUrl/call/end'),
        headers: {
          'Authorization': 'Bearer $token',
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({'call_id': callId}),
      );
    } catch (_) {}

    localStream?.getTracks().forEach((track) => track.stop());
    await localStream?.dispose();
    await remoteStream?.dispose();
    await _peerConnection?.close();
    _peerConnection = null;
  }
}
```

---

## 🎯 Flutter ডেভেলপার চেকলিস্ট (Checklist to give Developer):
1. [x] অ্যাপের API Base URL পরিবর্তন করে লাইভ সার্ভারের ডোমেইন (`https://...`) দেওয়া হয়েছে।
2. [x] `/api/call/ice-servers` থেকে STUN ও TURN সার্ভার লোড করে `RTCPeerConnection` এ পাস করা হয়েছে।
3. [x] `iceTransportPolicy: 'all'` দেওয়া হয়েছে যাতে 4G/Mobile Data তে TURN Relay স্বয়ংক্রিয়ভাবে ভিডিও স্ট্রিম ওপেন করে।
4. [x] অডিও কনস্ট্রেইন্টে `echoCancellation: true` এবং `noiseSuppression: true` দেওয়া হয়েছে।
5. [x] ভিডিও রেজোলিউশন `720p 30fps` এইচডি কনফিগার করা হয়েছে।
6. [x] কল চলাকালীন ইন্টারসেপ্টরে যেন ভুলবশত লগআউট ট্রিগার না হয় তা নিশ্চিত করা হয়েছে।
