# 📱 WebRTC Universal Mobile Device Compatibility & VPS TURN Server Setup Guide
## 🇧🇩 সকল মোবাইল ডিভাইসে (Android All Versions & Brands) 100% স্মুথ ভিডিও কলের সমাধান

> **সমস্যা বিশ্লেষণ (Root Cause Analysis):** 
> কিছু কিছু ফোনে কল রিসিভ হওয়ার পর কয়েন কাটে ও টাইমার চলে, কিন্তু ভিডিও দেখা যায় না (কালো স্ক্রিন/ব্ল্যাক স্ক্রিন বা লোডিং)। 
> এর প্রধান কারণ হলো: **মোবাইল ডাটা (4G/5G) ও Carrier-Grade NAT (CGNAT/Symmetric NAT) এর কারণে শুধু STUN সার্ভার দিয়ে Direct P2P কানেক্ট হতে পারে না। এর জন্য VPS-এ একটি TURN Relay Server (Coturn) আবশ্যক।** এছাড়াও লো-এন্ড ফোনে ক্যামেরা রেজোলিউশন ও VP8 কোডেক কনফিগারেশন না থাকা অন্যতম কারণ।

---

## 📑 সূচিপত্র (Table of Contents)
1. [সমস্যার মূল ৫টি কারণ (The 5 Root Causes)](#1-সমস্যার-মূল-৫টি-কারণ)
2. [VPS হোস্টিংয়ে Coturn (TURN/STUN) সার্ভার সেটআপ গাইড (VPS Setup)](#2-vps-হোস্টিংয়ে-coturn-turnstun-সার্ভার-সেটআপ-গাইড)
3. [Laravel RESTful API আপডেট (ICE Servers & Connected Event)](#3-laravel-restful-api-আপডেট)
4. [Flutter Developer-এর জন্য সম্পূর্ণ ফিক্স ও কোড গাইড (Flutter Fixes)](#4-flutter-developer-এর-জন্য-সম্পূর্ণ-ফিক্স-ও-কোড-গাইড)
   - ৪.১. ICE Candidate কিউয়িং (Race Condition Fix)
   - ৪.২. ইউনিভার্সাল ক্যামেরা রেজোলিউশন ও কনস্ট্রেইন্টস (Low-End Phone Support)
   - ৪.৩. VP8 কোডেক প্রায়োরিটাইজেশন (SDP Munging)
   - ৪.৪. RTCVideoRenderer লাইফসাইকেল ও Initialized চেক
   - ৪.৫. Android 12, 13, 14 পারমিশন ও ProGuard রুলস
5. [কয়েন ও বিলিং লজিক সুরক্ষা (Start Billing Only on Media Connected)](#5-কয়েন-ও-বিলিং-লজিক-সুরক্ষা)
6. [ডেভেলপারদের জন্য এক নজরে করণীয় চেকলিস্ট (Checklist for Developers)](#6-ডেভেলপারদের-জন্য-এক-নজরে-করণীয়-চেকলিস্ট)

---

## 1. সমস্যার মূল ৫টি কারণ

```
               [ User A (4G Mobile Data) ]
                           │
             (Symmetric NAT / CGNAT Firewall)
                           │
             ❌ STUN alone fails to punch hole!
                           │
            ┌──────────────┴──────────────┐
            ▼                             ▼
   [ VPS WebRTC TURN Server ]     [ Laravel Reverb ]
   (Relays Video/Audio Stream)    (Signaling / Status)
            ▲
            │ (Media Relayed Successfully 100%)
            │
               [ User B (Any Android Phone / WiFi) ]
```

| নং | কারণ (Cause) | কেন ঘটে? | সমাধান |
|---|---|---|---|
| **১** | **TURN সার্ভার না থাকা (Symmetric NAT/4G)** | Grameenphone, Banglalink, Robi, Airtel সহ যেকোনো মোবাইল নেটওয়ার্ক Symmetric NAT ব্যবহার করে। ফলে শুধু Google STUN দিয়ে সরাসরি ভিডিও প্যাকেট পাঠানো যায় না। | নিজের VPS-এ **Coturn TURN Server** চালু করা। |
| **২** | **ক্যামেরা কনস্ট্রেইন্ট ওভারলোড (Low-end Devices)** | Tecno, Symphony, Walton বা লো-এন্ড ফোনে `1920x1080` বা ফিক্সড `30 FPS` ফ্রন্ট ক্যামেরা সাপোর্ট না করে ক্র্যাশ করে। | ডায়নামিক ক্যামেরা রেজোলিউশন (`640x480` বা `1280x720` উইথ অটো-ফলব্যাক) সেট করা। |
| **৩** | **H.264 কোডেক ইনকম্প্যাটিবিলিটি** | অনেক সস্তা চিপসেটে (MediaTek, Unisoc, Spreadtrum) H.264 হার্ডওয়্যার ডিকোডার থাকে না। | WebRTC SDP-তে **VP8** কোডেককে সর্বোচ্চ প্রায়োরিটি দেওয়া (সকল ফোনে কাজ করে)। |
| **৪** | **ICE Candidate Race Condition** | রিমোট SDP সেট হওয়ার আগেই ICE Candidate যোগ করার চেষ্টা করলে WebRTC কানেকশন ফেইল্ড হয়ে যায়। | `remoteDescription` সেট না হওয়া পর্যন্ত Candidate গুলো `List` বা `Queue`-তে জমা রেখে পরে প্রসেস করা। |
| **৫** | **ভিডিও কানেক্ট হওয়ার আগেই কয়েন কাটা শুরু হওয়া** | কল `accept` হতেই টাইমার স্টার্ট হয়ে যায়, অথচ মিডিয়া কানেকশন `connecting`/`failed` অবস্থায় থাকে। | WebRTC `RTCIceConnectionState.RTCIceConnectionStateConnected` ইভেন্ট আসার পর বিলিং পালস শুরু করা। |

---

## 2. VPS হোস্টিংয়ে Coturn (TURN/STUN) সার্ভার সেটআপ গাইড

আপনার VPS (Ubuntu/Debian) সার্ভারে নিচের কমান্ডগুলো দিয়ে Coturn ইন্সটল ও কনফিগার করুন:

### ধাপ ১: Coturn ইন্সটল করা
```bash
sudo apt update
sudo apt install coturn -y
```

### ধাপ ২: Coturn সার্ভিস এনাবল করা
`/etc/default/coturn` ফাইলটি এডিট করুন:
```bash
sudo nano /etc/default/coturn
```
নিচের লাইনটি আন-কমেন্ট করে সেট করুন:
```ini
TURNSERVER_ENABLED=1
```

### ধাপ ৩: `/etc/turnserver.conf` কনফিগারেশন তৈরি করা
```bash
sudo nano /etc/turnserver.conf
```
নিচের কনফিগারেশনটি পেস্ট করুন (আপনার VPS এর পাবলিক আইপি ও সিক্রেট কি দিয়ে পরিবর্তন করুন):
```ini
# Listening Ports
listening-port=3478
tls-listening-port=5349

# VPS Public IP Address (আপনার VPS এর আইপি দিন)
external-ip=YOUR_VPS_PUBLIC_IP

# Relay Ports Range (Firewall-এ এগুলো ওপেন রাখতে হবে)
min-port=49152
max-port=65535

# Authentication Method (User & Password)
lt-cred-mech
user=chinchinsuser:ChinchinsSecretPass2026@
realm=yourdomain.com

# Performance & Security
fingerprint
stale-nonce
no-loopback-peers
no-multicast-peers
mobility
no-cli

# Logging
log-file=/var/log/coturn.log
verbose
```

### ধাপ ৪: VPS ফায়ারওয়াল (UFW / Cloud Firewall) পোর্ট ওপেন করা
```bash
sudo ufw allow 3478/tcp
sudo ufw allow 3478/udp
sudo ufw allow 5349/tcp
sudo ufw allow 5349/udp
sudo ufw allow 49152:65535/udp
sudo ufw reload
```

### ধাপ ৫: Coturn সার্ভিস চালু ও টেস্ট করা
```bash
sudo systemctl restart coturn
sudo systemctl enable coturn
sudo systemctl status coturn
```

---

## 3. Laravel RESTful API আপডেট

### ৩.১. Laravel `.env` ফাইলে TURN ক্রেডেনশিয়াল যুক্ত করুন:
```env
TURN_SERVER_URL="turn:YOUR_VPS_IP:3478?transport=udp,turn:YOUR_VPS_IP:3478?transport=tcp"
TURN_SERVER_USERNAME="chinchinsuser"
TURN_SERVER_PASSWORD="ChinchinsSecretPass2026@"
```

### ৩.২. `GET /api/call/ice-servers` এন্ডপয়েন্ট:
Flutter অ্যাপ কল শুরু করার পূর্বে এই API থেকে সম্পূর্ণ STUN ও TURN সার্ভার লিস্ট পাবে।

#### Request:
`GET /api/call/ice-servers`  
Header: `Authorization: Bearer {token}`

#### JSON Response (`200 OK`):
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
          "stun:global.stun.twilio.com:3478"
        ]
      },
      {
        "urls": [
          "turn:YOUR_VPS_IP:3478?transport=udp",
          "turn:YOUR_VPS_IP:3478?transport=tcp"
        ],
        "username": "chinchinsuser",
        "credential": "ChinchinsSecretPass2026@"
      }
    ]
  }
}
```

### ৩.৩. `POST /api/call/connected` (মিডিয়া কানেক্ট হলে বিলিং স্টার্টের জন্য):
Flutter অ্যাপ যখন নিশ্চিত হবে ভিডিও কানেক্ট হয়েছে (`RTCIceConnectionStateConnected`), তখন এই এন্ডপয়েন্টে হিট করবে।
#### Request:
`POST /api/call/connected`
```json
{
  "call_id": 100,
  "media_status": "connected"
}
```

---

## 4. Flutter Developer-এর জন্য সম্পূর্ণ ফিক্স ও কোড গাইড

Flutter ডেভেলপারকে নিচের ৫টি রুলস অবশ্যই `video_call_screen.dart` এবং WebRTC সার্ভিস ফাইলে অ্যাপ্লাই করতে হবে:

### ৪.১. ICE Candidate কিউয়িং (Race Condition Fix)

```dart
// WebRTC Call Service / Controller-এ:
bool _hasRemoteDescriptionSet = false;
final List<RTCIceCandidate> _pendingIceCandidates = [];

// রিমোট ডেসক্রিপশন সেট করার মেথড:
Future<void> setRemoteSdp(RTCSessionDescription description) async {
  await _peerConnection?.setRemoteDescription(description);
  _hasRemoteDescriptionSet = true;

  // পেন্ডিং সকল ICE Candidate যোগ করুন
  for (final candidate in _pendingIceCandidates) {
    await _peerConnection?.addCandidate(candidate);
  }
  _pendingIceCandidates.clear();
}

// যখন সিগন্যালিং বা পুশার থেকে ICE Candidate আসবে:
void onIceCandidateReceived(Map<String, dynamic> data) async {
  final candidate = RTCIceCandidate(
    data['candidate'],
    data['sdpMid'] ?? '0',
    data['sdpMLineIndex'] ?? 0,
  );

  if (_hasRemoteDescriptionSet) {
    await _peerConnection?.addCandidate(candidate);
  } else {
    // রিমোট ডেসক্রিপশন সেট না হওয়া পর্যন্ত কিউতে রাখুন
    _pendingIceCandidates.add(candidate);
  }
}
```

---

### ৪.২. ইউনিভার্সাল ক্যামেরা কনস্ট্রেইন্টস (সব মডেলের অ্যান্ড্রয়েড ফোনে সাপোর্ট)

অনেক ফোনে ক্যামেরা বেশি হাই রেজোলিউশনে ইনিশিয়ালাইজ হতে পারে না। সেফ রেজোলিউশন ও অডিও কনস্ট্রেইন্টস:

```dart
Future<MediaStream> getOptimalUserMedia() async {
  final Map<String, dynamic> mediaConstraints = {
    'audio': {
      'echoCancellation': true,
      'noiseSuppression': true,
      'autoGainControl': true,
    },
    'video': {
      'mandatory': {
        'minWidth': '640',
        'minHeight': '480',
        'maxWidth': '1280',
        'maxHeight': '720',
        'minFrameRate': '15',
        'maxFrameRate': '30',
      },
      'facingMode': 'user',
      'optional': [],
    }
  };

  try {
    return await navigator.mediaDevices.getUserMedia(mediaConstraints);
  } catch (e) {
    print("Warning: High resolution camera failed, falling back to basic VGA: $e");
    // Fallback for extreme low-end devices
    return await navigator.mediaDevices.getUserMedia({
      'audio': true,
      'video': {'facingMode': 'user'}
    });
  }
}
```

---

### ৪.৩. VP8 কোডেক প্রায়োরিটাইজেশন (SDP Munging)

সকল ডিভাইসে 100% ভিডিও ডিকোডিং নিশ্চিত করতে SDP-তে VP8 কোডেককে প্রথমে রাখা:

```dart
String preferCodec(String sdp, String codec) {
  final lines = sdp.split('\r\n');
  int? mLineIndex;
  String? payload;

  for (int i = 0; i < lines.length; i++) {
    if (lines[i].startsWith('m=video')) {
      mLineIndex = i;
    }
    if (lines[i].contains('a=rtpmap:') && lines[i].toLowerCase().contains(codec.toLowerCase())) {
      final parts = lines[i].split(' ');
      payload = parts[0].split(':')[1];
      break;
    }
  }

  if (mLineIndex == null || payload == null) return sdp;

  final mLineElements = lines[mLineIndex].split(' ');
  final newMLine = <String>[];
  newMLine.add(mLineElements[0]); // m=video
  newMLine.add(mLineElements[1]); // port
  newMLine.add(mLineElements[2]); // proto
  newMLine.add(payload);          // Put Preferred Codec First

  for (int i = 3; i < mLineElements.length; i++) {
    if (mLineElements[i] != payload) {
      newMLine.add(mLineElements[i]);
    }
  }

  lines[mLineIndex] = newMLine.join(' ');
  return lines.join('\r\n');
}
```

---

### ৪.৪. RTCVideoRenderer লাইফসাইকেল ও UI সেফটি

```dart
class _VideoCallScreenState extends State<VideoCallScreen> {
  final RTCVideoRenderer _localRenderer = RTCVideoRenderer();
  final RTCVideoRenderer _remoteRenderer = RTCVideoRenderer();
  bool _isRendererReady = false;

  @override
  void initState() {
    super.initState();
    _initRenderers();
  }

  Future<void> _initRenderers() async {
    await _localRenderer.initialize();
    await _remoteRenderer.initialize();
    if (mounted) {
      setState(() {
        _isRendererReady = true;
      });
    }
    _startCallWorkflow();
  }

  @override
  void dispose() {
    _localRenderer.srcObject = null;
    _remoteRenderer.srcObject = null;
    _localRenderer.dispose();
    _remoteRenderer.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    if (!_isRendererReady) {
      return const Scaffold(
        backgroundColor: Colors.black,
        body: Center(child: CircularProgressIndicator(color: Colors.pinkAccent)),
      );
    }

    return Scaffold(
      backgroundColor: Colors.black,
      body: Stack(
        children: [
          // Remote Video View
          Positioned.fill(
            child: RTCVideoView(
              _remoteRenderer,
              objectFit: RTCVideoViewObjectFit.RTCVideoViewObjectFitCover,
            ),
          ),
          // Local Self View (Pip)
          Positioned(
            right: 16,
            top: 48,
            width: 110,
            height: 160,
            child: ClipRRect(
              borderRadius: BorderRadius.circular(12),
              child: RTCVideoView(
                _localRenderer,
                mirror: true,
                objectFit: RTCVideoViewObjectFit.RTCVideoViewObjectFitCover,
              ),
            ),
          ),
        ],
      ),
    );
  }
}
```

---

### ৪.৫. `android/app/src/main/AndroidManifest.xml` পারমিশন

```xml
<manifest xmlns:android="http://schemas.android.com/apk/res/android">
    <uses-feature android:name="android.hardware.camera" android:required="false" />
    <uses-feature android:name="android.hardware.camera.autofocus" android:required="false" />
    
    <uses-permission android:name="android.permission.CAMERA" />
    <uses-permission android:name="android.permission.RECORD_AUDIO" />
    <uses-permission android:name="android.permission.MODIFY_AUDIO_SETTINGS" />
    <uses-permission android:name="android.permission.INTERNET" />
    <uses-permission android:name="android.permission.ACCESS_NETWORK_STATE" />
    <uses-permission android:name="android.permission.ACCESS_WIFI_STATE" />
    <uses-permission android:name="android.permission.BLUETOOTH" />
    <uses-permission android:name="android.permission.BLUETOOTH_CONNECT" />
    <uses-permission android:name="android.permission.FOREGROUND_SERVICE" />
    <uses-permission android:name="android.permission.FOREGROUND_SERVICE_CAMERA" />
    <uses-permission android:name="android.permission.FOREGROUND_SERVICE_MICROPHONE" />
    <uses-permission android:name="android.permission.WAKE_LOCK" />
</manifest>
```

---

## 5. কয়েন ও বিলিং লজিক সুরক্ষা

### কীভাবে নিশ্চিত করবেন ভিডিও কানেক্ট হওয়ার পরেই কয়েন কাটবে?
Flutter অ্যাপে `RTCPeerConnection` এর কানেকশন স্টেট লিসেন করুন:

```dart
_peerConnection?.onIceConnectionState = (RTCIceConnectionState state) {
  print("WebRTC ICE Connection State: $state");
  
  if (state == RTCIceConnectionState.RTCIceConnectionStateConnected) {
    // ✅ ভিডিও মিডিয়া সরাসরি কানেক্ট হয়েছে!
    // এখন থেকে প্রতি ১ মিনিটের বিলিং পালস টাইমার স্টার্ট করুন
    _startBillingPulseTimer();
  } else if (state == RTCIceConnectionState.RTCIceConnectionStateFailed) {
    print("Connection failed! Restarting ICE...");
    _peerConnection?.restartIce();
  }
};
```

---

## 6. ডেভেলপারদের জন্য এক নজরে করণীয় চেকলিস্ট

### VPS / Backend Developer:
- [ ] VPS-এ `coturn` ইন্সটল করে `turnserver.conf` কনফিগার করুন এবং সার্ভিস রিস্টার্ট দিন।
- [ ] ফায়ারওয়ালে `3478` এবং `49152:65535` UDP পোর্ট ওপেন করুন।
- [ ] Laravel `.env` ফাইলে TURN ক্রেডেনশিয়াল যুক্ত করুন।
- [ ] `GET /api/call/ice-servers` এপিআই রেসপন্সে TURN সার্ভার রিটার্ন হচ্ছে কিনা পোস্টম্যান বা ব্রাউজারে চেক করুন।

### Flutter Developer:
- [ ] কল স্ক্রিনে ঢোকার আগেই `GET /api/call/ice-servers` থেকে ডায়নামিক `iceServers` ফেচ করে `RTCPeerConnection` এ পাস করুন।
- [ ] `_pendingIceCandidates` কিউ ইমপ্লিমেন্ট করুন যাতে `setRemoteDescription` এর আগে কোনো ক্যান্ডিডেট ড্রপ না হয়।
- [ ] `getOptimalUserMedia()` এ সেফ রেজোলিউশন ও ফলব্যাক ব্যবহার করুন।
- [ ] SDP অফার ও অ্যানসারে `VP8` কোডেক প্রায়োরিটি দিন।
- [ ] `RTCVideoRenderer.initialize()` কমপ্লিট হওয়া পর্যন্ত লোডিং দেখান এবং `onTrack` ইভেন্টে `_remoteRenderer.srcObject = event.streams[0]` সেট করুন।
- [ ] `RTCIceConnectionStateConnected` আসার পর বিলিং টাইমার স্টার্ট করুন।
