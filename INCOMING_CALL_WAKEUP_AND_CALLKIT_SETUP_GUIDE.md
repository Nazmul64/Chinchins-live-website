# 📲 WhatsApp & IMO Style Full-Screen Incoming Call Wake-Up & CallKit Setup Guide
## 🇧🇩 স্ক্রিন বন্ধ / লক থাকা অবস্থায় WhatsApp ও IMO-এর মতো ফুল-স্ক্রিন ইনকামিং কল ও রিংটোন বাজার সমাধান

> **ব্যবহারকারীর অভিজ্ঞতা (User Experience):** 
> ব্যবহারকারীর ফোনে অ্যাপটি শুধু ইনস্টল করা থাকলেই হবে। ফোন লক থাকুক, টেবিলের উপর স্ক্রিন অফ করা থাকুক, অথবা অ্যাপটি ব্যাকগ্রাউন্ডে বা সম্পূর্ণ কিল/ক্লোজ করা থাকুক না কেন—অন্য কোনো ইউজার ভিডিও কল দিলে **স্বয়ংক্রিয়ভাবে স্ক্রিনের আলো জ্বলে উঠবে (Wake-up), ফুল-স্ক্রিন ইনকামিং কল ইন্টারফেস আসবে, কলারের ছবি ও নাম দেখাবে এবং টানা রিংটোন বাজতে থাকবে** (ঠিক যেমন WhatsApp, IMO বা সাধারণ মোবাইল ফোনে কল আসে)।

---

## 📑 সূচিপত্র (Table of Contents)
1. [আর্কিটেকচার ও কীভাবে কাজ করে (How Call Wake-Up Works)](#1-আর্কিটেকচার-ও-কীভাবে-কাজ-করে)
2. [কল লাইফসাইকেল ফ্লোচার্ট (Call Wake-Up Lifecycle)](#2-কল-লাইফসাইকেল-ফ্লোচার্ট)
3. [Laravel RESTful API ও FCM পুশ কনফিগারেশন (Backend APIs)](#3-laravel-restful-api-ও-fcm-পুশ-কনফিগারেশন)
   - ৩.১. ডিভাইস টোকেন রেজিস্ট্রেশন (`POST /api/app/device/register`)
   - ৩.২. কল ইনিশিয়েট ও হাই-প্রায়োরিটি VoIP পুশ (`POST /api/call/initiate`)
   - ৩.৩. কল ক্যানসেল পুশ (`POST /api/call/cancel`)
4. [Flutter Developer-এর জন্য স্টেপ-বাই-স্টেপ ইমপ্লিমেন্টেশন ও সম্পূর্ণ কোড](#4-flutter-developer-এর-জন্য-স্টেপ-বাই-স্টেপ-ইমপ্লিমেন্টেশন-ও-সম্পূর্ণ-কোড)
   - ৪.১. প্রয়োজনীয় প্যাকেজ ডিপেনডেন্সি (`pubspec.yaml`)
   - ৪.২. Android পারমিশন ও কনফিগারেশন (`AndroidManifest.xml`)
   - ৪.৩. `CallKitService.dart` (ফুল-স্ক্রিন কল হ্যান্ডলার)
   - ৪.৪. `main.dart` এ ব্যাকগ্রাউন্ড পুশ লিসেনার সেটআপ (`@pragma('vm:entry-point')`)
   - ৪.৫. রিসিভ বাটনে চাপলে সরাসরি ভিডিও কল স্ক্রিনে নেভিগেশন
5. [Xiaomi / Oppo / Vivo / Samsung ব্যাটারি অপটিমাইজেশন সেটিং](#5-ডিভাইস-স্পেসিফিক-ব্যাটারি-অপটিমাইজেশন)
6. [ডেভেলপারদের জন্য এক নজরে চেকলিস্ট](#6-ডেভেলপারদের-জন্য-এক-নজরে-চেকলিস্ট)

---

## 1. আর্কিটেকচার ও কীভাবে কাজ করে

সাধারণ পুশ নোটিফিকেশন শুধুমাত্র নোটিফিকেশন বারে একটি মেসেজ দেখায়। কিন্তু **WhatsApp/IMO**-এর মতো ফুল-স্ক্রিন কল আনার জন্য ৩টি টেকনোলজি একসাথে কাজ করে:

1. **FCM High-Priority VoIP Data Push:** কলার যখন কল দেয়, Laravel ব্যাকএন্ড থেকে প্রাপকের ফোনে একটি `priority: high` বিশিষ্ট Data-Only পুশ পাঠানো হয়।
2. **Android Full-Screen Intent & WakeLock:** ফোন লক থাকা অবস্থাতেও অ্যান্ড্রয়েড ওএস স্ক্রিন অন করে দেয় (`WAKE_LOCK`) এবং লকস্ক্রিনের উপরে ফুল-স্ক্রিন কল অ্যাক্টিভিটি লঞ্চ করে।
3. **Flutter CallKit Incoming (`flutter_callkit_incoming`):** এটি নেটিভ অ্যান্ড্রয়েড ও আইওএস এর অফিসিয়াল `Telecom / CallKit Framework` ব্যবহার করে সিস্টেম লেভেলের অরিজিনাল কল স্ক্রিন তৈরি করে।

---

## 2. কল লাইফসাইকেল ফ্লোচার্ট

```
[ Caller Initiates Video Call ]
               │
               ▼
   [ Laravel API Backend ]
   (Dispatches High-Priority VoIP FCM)
               │
               ▼
[ Receiver Phone (Screen OFF / Locked) ]
               │
   ┌───────────┴──────────────────────────┐
   ▼                                      ▼
[ Firebase Background Handler ]   [ Android WakeLock & FullScreen Intent ]
   │                                      │
   └───────────────┬──────────────────────┘
                   │
                   ▼
┌───────────────────────────────────────────────┐
│   🌟 Native Full-Screen Incoming Call Screen   │
│   - Screen Turns ON Automatically             │
│   - Looping Ringtone Plays Continuously       │
│   - Shows Caller Avatar, Name & Video Badge   │
│   - [ Accept (Green) ] / [ Decline (Red) ]    │
└───────────────────────┬───────────────────────┘
                        │
           ┌────────────┴────────────┐
      User Clicks               User Clicks
       [ Accept ]               [ Decline ]
           │                         │
           ▼                         ▼
 ┌─────────────────────┐   ┌─────────────────────┐
 │ Open Video Screen & │   │ Call /api/call/reject│
 │ Connect WebRTC Stream│  │ & Dismiss Ringing   │
 └─────────────────────┘   └─────────────────────┘
```

---

## 3. Laravel RESTful API ও FCM পুশ কনফিগারেশন

Laravel ব্যাকএন্ডে ইতিমধ্যে এই ফিচারগুলো সম্পূর্ণ রেডি ও ইন্টিগ্রেটেড রয়েছে।

### ৩.১. ডিভাইস রেজিস্ট্রেশন এন্ডপয়েন্ট (Login এর পর Flutter কল করবে)
ইউজার যখনই অ্যাপে লগইন বা ওপেন করবে, তার ডিভাইসের FCM টোকেন ব্যাকএন্ডে পাঠাবে।

* **Method:** `POST`
* **URL:** `/api/app/device/register` (বা `/api/user/device-token`)
* **Headers:** `Authorization: Bearer {token}`
* **Payload:**
```json
{
  "fcm_token": "fcm_token_string_from_firebase...",
  "device_id": "unique_device_uuid",
  "device_type": "android",
  "device_brand": "Samsung",
  "device_model": "Galaxy A54"
}
```

---

### ৩.২. কল ইনিশিয়েট এন্ডপয়েন্ট (স্বয়ংক্রিয় VoIP পুশ পাঠায়)
* **Method:** `POST`
* **URL:** `/api/call/initiate`
* **Payload:**
```json
{
  "receiver_id": 25,
  "call_type": "video"
}
```

#### Laravel ব্যাকএন্ড যে FCM VoIP Data Payload পাঠায়:
```json
{
  "registration_ids": ["RECEIVER_FCM_TOKEN"],
  "priority": "high",
  "data": {
    "action": "INCOMING_CALL",
    "type": "incoming_call",
    "call_id": "105",
    "channel_name": "call_video_10_25_1725300000",
    "call_type": "video",
    "caller_id": "10",
    "caller_name": "Shakib Al Hasan",
    "caller_avatar": "https://yourdomain.com/storage/avatars/shakib.jpg",
    "rate_per_minute": "100",
    "is_free_trial": "0",
    "ring_timeout": "45"
  },
  "android": {
    "priority": "high",
    "ttl": "45s"
  }
}
```

---

### ৩.৩. কলার কল কেটে দিলে রিসিভারের স্ক্রিন বন্ধ করার পুশ
কলার যদি রিসিভার ফোন ধরার আগেই কেটে দেয় (`POST /api/call/cancel`), ব্যাকএন্ড সাথে সাথে রিসিভারের ফোন থেকে রিংটোন বন্ধ করে ইনকামিং স্ক্রিন অফ করার জন্য `CALL_CANCELLED` পুশ পাঠায়:
```json
{
  "action": "CALL_CANCELLED",
  "call_id": "105"
}
```

---

## 4. Flutter Developer-এর জন্য সম্পূর্ণ কোড গাইড

### ৪.১. `pubspec.yaml` ডিপেনডেন্সি যুক্ত করুন:
```yaml
dependencies:
  flutter:
    sdk: flutter
  firebase_core: ^3.6.0
  firebase_messaging: ^15.1.3
  flutter_callkit_incoming: ^2.0.4+2
  flutter_secure_storage: ^9.2.2
  shared_preferences: ^2.3.2
  http: ^1.2.2
```

---

### ৪.২. `android/app/src/main/AndroidManifest.xml` কনফিগারেশন:

`<manifest>` ট্যাগের ভেতর নিচের পারমিশনগুলো যুক্ত করুন:

```xml
<manifest xmlns:android="http://schemas.android.com/apk/res/android">

    <!-- 🌟 Full Screen Call Intent & WakeLock Permissions -->
    <uses-permission android:name="android.permission.USE_FULL_SCREEN_INTENT" />
    <uses-permission android:name="android.permission.WAKE_LOCK" />
    <uses-permission android:name="android.permission.VIBRATE" />
    <uses-permission android:name="android.permission.SYSTEM_ALERT_WINDOW" />
    <uses-permission android:name="android.permission.DISABLE_KEYGUARD" />
    <uses-permission android:name="android.permission.FOREGROUND_SERVICE" />
    <uses-permission android:name="android.permission.FOREGROUND_SERVICE_PHONE_CALL" />
    <uses-permission android:name="android.permission.RECORD_AUDIO" />
    <uses-permission android:name="android.permission.CAMERA" />
    <uses-permission android:name="android.permission.INTERNET" />

    <application
        android:label="Chinchins Live"
        android:icon="@mipmap/ic_launcher"
        android:showWhenLocked="true"
        android:turnScreenOn="true">

        <activity
            android:name=".MainActivity"
            android:exported="true"
            android:launchMode="singleTop"
            android:theme="@style/LaunchTheme"
            android:configChanges="orientation|keyboardHidden|keyboard|screenSize|smallestScreenSize|locale|layoutDirection|fontScale|screenLayout|density|uiMode"
            android:hardwareAccelerated="true"
            android:windowSoftInputMode="adjustResize"
            android:showWhenLocked="true"
            android:turnScreenOn="true">
            
            <intent-filter>
                <action android:name="android.intent.action.MAIN"/>
                <category android:name="android.intent.category.LAUNCHER"/>
            </intent-filter>
        </activity>

        <!-- Firebase Messaging Service -->
        <service
            android:name="io.flutter.plugins.firebase.messaging.FlutterFirebaseMessagingBackgroundService"
            android:permission="android.permission.BIND_JOB_SERVICE"
            android:exported="false" />
    </application>
</manifest>
```

---

### ৪.৩. `CallKitService.dart` (ফুল-স্ক্রিন কল শো ও অ্যাকশন হ্যান্ডলার)

`lib/core/services/callkit_service.dart` ফাইলে নিচের কোডটি তৈরি করুন:

```dart
import 'package:flutter_callkit_incoming/entities/entities.dart';
import 'package:flutter_callkit_incoming/flutter_callkit_incoming.dart';
import 'package:uuid/uuid.dart';

class CallKitService {
  /// Display Full-Screen WhatsApp/IMO-style Call Screen
  static Future<void> showIncomingCall({
    required String callId,
    required String callerName,
    required String callerAvatar,
    required String callType, // 'video' or 'audio'
    required String channelName,
    Map<String, dynamic>? extraData,
  }) async {
    final CallKitParams callKitParams = CallKitParams(
      id: callId,
      nameCaller: callerName,
      appName: 'Chinchins Live',
      avatar: callerAvatar.isNotEmpty 
          ? callerAvatar 
          : 'https://ui-avatars.com/api/?name=${Uri.encodeComponent(callerName)}&background=FF4081&color=fff',
      handle: callType.toUpperCase() == 'VIDEO' ? '🎥 Incoming Video Call' : '📞 Incoming Audio Call',
      type: callType.toUpperCase() == 'VIDEO' ? 1 : 0, // 1 = Video, 0 = Audio
      duration: 45000, // 45 Seconds Ringing Duration
      textAccept: 'Accept',
      textDecline: 'Decline',
      missedCallNotification: const NotificationParams(
        showNotification: true,
        isShowCallback: true,
        subtitle: 'Missed Call',
        callbackText: 'Call back',
      ),
      extra: <String, dynamic>{
        'call_id': callId,
        'channel_name': channelName,
        'call_type': callType,
        'caller_name': callerName,
        'caller_avatar': callerAvatar,
        ...?extraData,
      },
      headers: <String, dynamic>{'apiKey': 'chinchins_live'},
      android: const AndroidParams(
        isCustomNotification: true,
        isShowLogo: false,
        ringtonePath: 'system_ringtone_default',
        backgroundColor: '#0955fa',
        actionColor: '#4CAF50',
        textColor: '#ffffff',
        incomingCallNotificationChannelName: 'Incoming Calls',
        missedCallNotificationChannelName: 'Missed Calls',
        isShowCallID: false,
      ),
      ios: const IOSParams(
        iconName: 'AppIcon',
        handleType: 'generic',
        supportsVideo: true,
        maximumCallGroups: 2,
        maximumCallsPerCallGroup: 1,
        audioSessionMode: 'default',
        audioSessionActive: true,
        audioSessionPreferredSampleRate: 44100.0,
        audioSessionPreferredIOBufferDuration: 0.005,
        supportsDTMF: true,
        supportsHolding: false,
        supportsGrouping: false,
        supportsUngrouping: false,
        ringtonePath: 'system_ringtone_default',
      ),
    );

    await FlutterCallkitIncoming.showCallkitIncoming(callKitParams);
  }

  /// Close Call Screen if caller cancels
  static Future<void> endCall(String callId) async {
    await FlutterCallkitIncoming.endCall(callId);
  }

  /// Close all call screens
  static Future<void> endAllCalls() async {
    await FlutterCallkitIncoming.endAllCalls();
  }
}
```

---

### ৪.৪. `main.dart` এ ব্যাকগ্রাউন্ড মেসেজিং ও অ্যাকশন লিসেনার

`lib/main.dart` ফাইলটি আপডেট করুন:

```dart
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/material.dart';
import 'package:flutter_callkit_incoming/flutter_callkit_incoming.dart';
import 'core/services/callkit_service.dart';
import 'features/call/screens/video_call_screen.dart';

final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();

/// 🌟 CRITICAL: Background Message Handler (Runs when app is killed or screen is locked)
@pragma('vm:entry-point')
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
  print("Background FCM Data Received: ${message.data}");

  final data = message.data;
  final action = data['action'] ?? data['type'];

  if (action == 'INCOMING_CALL' || action == 'incoming_call') {
    await CallKitService.showIncomingCall(
      callId: data['call_id'] ?? '0',
      callerName: data['caller_name'] ?? 'User Calling',
      callerAvatar: data['caller_avatar'] ?? '',
      callType: data['call_type'] ?? 'video',
      channelName: data['channel_name'] ?? '',
      extraData: data,
    );
  } else if (action == 'CALL_CANCELLED') {
    await CallKitService.endCall(data['call_id'] ?? '0');
  }
}

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await Firebase.initializeApp();

  // Register Background FCM Handler
  FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);

  runApp(const MyApp());
}

class MyApp extends StatefulWidget {
  const MyApp({Key? key}) : super(key: key);

  @override
  State<MyApp> createState() => _MyAppState();
}

class _MyAppState extends State<MyApp> {
  @override
  void initState() {
    super.initState();
    _listenToCallEvents();
    _listenToForegroundFcm();
  }

  /// Listen to CallKit Button Clicks (Accept / Decline)
  void _listenToCallEvents() {
    FlutterCallkitIncoming.onEvent.listen((CallEvent? event) {
      if (event == null) return;

      switch (event.event) {
        case Event.actionCallAccept:
          print("Call Accepted by user! Opening Video Call Screen...");
          final extra = event.body['extra'] ?? {};
          navigatorKey.currentState?.push(
            MaterialPageRoute(
              builder: (context) => VideoCallScreen(
                callId: int.tryParse(extra['call_id']?.toString() ?? '0') ?? 0,
                channelName: extra['channel_name'] ?? '',
                callerName: extra['caller_name'] ?? '',
                callerAvatar: extra['caller_avatar'] ?? '',
                isIncoming: true,
              ),
            ),
          );
          break;

        case Event.actionCallDecline:
          print("Call Declined by user.");
          // Call API to reject: /api/call/reject
          break;

        case Event.actionCallEnded:
        case Event.actionCallTimeout:
          print("Call Ended or Timed Out.");
          break;

        default:
          break;
      }
    });
  }

  /// Foreground FCM Handler
  void _listenToForegroundFcm() {
    FirebaseMessaging.onMessage.listen((RemoteMessage message) {
      final data = message.data;
      final action = data['action'] ?? data['type'];

      if (action == 'INCOMING_CALL' || action == 'incoming_call') {
        CallKitService.showIncomingCall(
          callId: data['call_id'] ?? '0',
          callerName: data['caller_name'] ?? 'User Calling',
          callerAvatar: data['caller_avatar'] ?? '',
          callType: data['call_type'] ?? 'video',
          channelName: data['channel_name'] ?? '',
          extraData: data,
        );
      } else if (action == 'CALL_CANCELLED') {
        CallKitService.endCall(data['call_id'] ?? '0');
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      navigatorKey: navigatorKey,
      title: 'Chinchins Live',
      debugShowCheckedModeBanner: false,
      home: const SplashScreen(), // Automatically routes to HomeScreen
    );
  }
}
```

---

## 5. ডিভাইস-স্পেসিফিক ব্যাটারি অপটিমাইজেশন

Xiaomi (MIUI), Oppo (ColorOS), Vivo, ও Huawei ফোনে কড়া ব্যাটারি সেভার থাকে যা ব্যাকগ্রাউন্ড সার্ভিস বন্ধ করে দেয়। ব্যবহারকারীকে অ্যাপের ভেতর একবার একটি সুন্দর ডায়ালগ দেখিয়ে নিচের পারমিশন দুটি অন করতে অনুরোধ করুন:

1. **Auto-Start Permission (স্বয়ংক্রিয় চালু হওয়ার অনুমতি):**
   - Xiaomi/Oppo-তে `Settings -> Apps -> Manage Apps -> Chinchins Live -> AutoStart -> Enable`.
2. **Display Over Other Apps (অন্যান্য অ্যাপের উপর দেখানোর অনুমতি):**
   - `Settings -> Apps -> Chinchins Live -> Appear on top -> Allow`.

Flutter-এ কোডের মাধ্যমে সরাসরি এই সেটিং পেজ ওপেন করতে:
```dart
import 'package:permission_handler/permission_handler.dart';

Future<void> requestCallPermissions() async {
  await Permission.notification.request();
  await Permission.systemAlertWindow.request();
  await Permission.ignoreBatteryOptimizations.request();
}
```

---

## 6. ডেভেলপারদের জন্য এক নজরে চেকলিস্ট

### Flutter Developer:
- [ ] `flutter_callkit_incoming` ও `firebase_messaging` প্যাকেজ যুক্ত করা।
- [ ] `AndroidManifest.xml` এ `USE_FULL_SCREEN_INTENT`, `WAKE_LOCK`, `showWhenLocked="true"`, `turnScreenOn="true"` যুক্ত করা।
- [ ] `main.dart` এ `@pragma('vm:entry-point')` দিয়ে `onBackgroundMessage` সেটআপ করা।
- [ ] `FlutterCallkitIncoming.onEvent` লিসেন করে `Event.actionCallAccept` হলে সরাসরি `VideoCallScreen`-এ নিয়ে যাওয়া।
- [ ] লগইনের পরপরই `POST /api/app/device/register` এপিআই-তে বর্তমান FCM টোকেন পাঠানো।

### Backend Developer / Laravel:
- [ ] `POST /api/call/initiate` এপিআই-তে `PushNotificationService::sendIncomingCallPush()` নিশ্চিত করা (যা ইতিমধ্যে কনফিগার করা আছে)।
- [ ] কলার কল কেটে দিলে `CALL_CANCELLED` পুশ পাঠানো।
