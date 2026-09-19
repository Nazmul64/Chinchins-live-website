# 🔴 Chinchins Live — Complete A-to-Z 67 Points RESTful API, WebSocket & RTC Architecture Documentation

> **Base URL:** `https://chinchins.live/api`  
> **WebSocket Engine:** Laravel Reverb (`wss://chinchins.live:443/app/chinchins_reverb_key`)  
> **RTC Calling & Streaming Engine:** Agora Cloud RTC (Dynamic Token Authentication) with Hostinger VPS WebRTC Fallback.  
> **Mandatory Request Headers:**
> ```http
> Accept: application/json
> Content-Type: application/json
> Authorization: Bearer <SANCTUM_BEARER_TOKEN>
> ```
> *(Note: For background services or legacy mobile builds, `X-User-Id` header or `user_id` parameter in request body is supported).*

---

# 📑 পূর্ণাঙ্গ সূচিপত্র (Complete 1 to 67 Requirements Index)

- [1. Existing Technology Stack](#point-1-existing-technology-stack)
- [2. 1-to-1 Video Call System](#point-2-1-to-1-video-call-system)
- [3. Video Call During Chat](#point-3-video-call-during-chat)
- [4. Video Call Chat Database](#point-4-video-call-chat-database)
- [5. Profile → Online Status](#point-5-profile--online-status)
- [6. Profile View → Call / Message](#point-6-profile-view--call--message)
- [7. Online Presence System](#point-7-online-presence-system)
- [8. Live Streaming System](#point-8-live-streaming-system)
- [9. Live Streaming List](#point-9-live-streaming-list)
- [10. Join Live](#point-10-join-live)
- [11. Live Chat](#point-11-live-chat)
- [12. Live Gift System](#point-12-live-gift-system)
- [13. Gift Animation](#point-13-gift-animation)
- [14. Host Earnings](#point-14-host-earnings)
- [15. Coin/Wallet System](#point-15-coinwallet-system)
- [16. Live Host → Guest Request](#point-16-live-host--guest-request)
- [17. Multi-Guest Live](#point-17-multi-guest-live)
- [18. Guest Request System](#point-18-guest-request-system)
- [19. Host Controls](#point-19-host-controls)
- [20. Voice Party Room](#point-20-voice-party-room)
- [21. Voice Room Seats](#point-21-voice-room-seats)
- [22. Voice Room Request](#point-22-voice-room-request)
- [23. Voice Room Chat](#point-23-voice-room-chat)
- [24. Voice Room Gifts](#point-24-voice-room-gifts)
- [25. Follow System](#point-25-follow-system)
- [26. Notifications](#point-26-notifications)
- [27. Call Status Management](#point-27-call-status-management)
- [28. Network Handling](#point-28-network-handling)
- [29. Background / Minimize Video Call](#point-29-background--minimize-video-call)
- [30. Call End](#point-30-call-end)
- [31. Call History](#point-31-call-history)
- [32. Live History](#point-32-live-history)
- [33. Viewer Count](#point-33-viewer-count)
- [34. Live Ranking / Popularity](#point-34-live-ranking--popularity)
- [35. Likes](#point-35-likes)
- [36. Share Live](#point-36-share-live)
- [37. Report / Block](#point-37-report--block)
- [38. Moderation](#point-38-moderation)
- [39. Security Requirements](#point-39-security-requirements)
- [40. WebSocket Security](#point-40-websocket-security)
- [41. Gift Security](#point-41-gift-security)
- [42. Database Transaction](#point-42-database-transaction)
- [43. Real-Time Event Architecture](#point-43-real-time-event-architecture)
- [44. Agora Channel Architecture](#point-44-agora-channel-architecture)
- [45. Agora Roles](#point-45-agora-roles)
- [46. App Performance](#point-46-app-performance)
- [47. Flutter Lifecycle](#point-47-flutter-lifecycle)
- [48. Camera & Microphone Permission](#point-48-camera--microphone-permission)
- [49. Beauty / Camera Filters](#point-49-beauty--camera-filters)
- [50. Message System](#point-50-message-system)
- [51. Message Persistence](#point-51-message-persistence)
- [52. Call + Chat Synchronization](#point-52-call--chat-synchronization)
- [53. Live Room Data](#point-53-live-room-data)
- [54. Party Room Data](#point-54-party-room-data)
- [55. API Requirements](#point-55-api-requirements)
- [56. Redis / Queue](#point-56-redis--queue)
- [57. VPS Requirements](#point-57-vps-requirements)
- [58. SSL](#point-58-ssl)
- [59. Error Handling](#point-59-error-handling)
- [60. Reconnection](#point-60-reconnection)
- [61. Duplicate Prevention](#point-61-duplicate-prevention)
- [62. Race Condition Protection](#point-62-race-condition-protection)
- [63. Admin Configuration](#point-63-admin-configuration)
- [64. Analytics](#point-64-analytics)
- [65. Important User Flow](#point-65-important-user-flow)
- [66. Most Important Requirement](#point-66-most-important-requirement)
- [67. Final Testing](#point-67-final-testing)

---

## Point 1: Existing Technology Stack
* **Backend:** Laravel 11, PHP 8.2+, MySQL 8.0, Redis, Laravel Reverb WebSocket, Laravel Sanctum Bearer Token Auth.
* **Frontend:** Flutter (Android & iOS) with responsive design and existing UI preservation.
* **Media Stream RTC:** Agora Cloud RTC Engine for low-latency Audio/Video broadcasting and 1-on-1 calls. WebRTC fallback supported.
* **Application Signaling & Events:** Laravel Reverb (`wss://chinchins.live:443/app/chinchins_reverb_key`) for real-time messaging, call signaling, online presence, live comments, viewer counters, and gift notifications.
* **Boundary Separation:** সমস্ত Audio/Video Media Traffic যাবে Agora ইনফ্রাস্ট্রাকচারের মাধ্যমে। আর সমস্ত Application-level Data Events, Chat, Gift, Call Signaling যাবে Laravel Reverb WebSocket-এর মাধ্যমে।

---

## Point 2: 1-to-1 Video Call System
* **Caller Flow:** User A প্রোফাইল ভিউ করে **Call** বাটনে চাপলে ব্যাকএন্ডে রিকোয়েস্ট যাবে।
* **Receiver Flow:** User B-এর স্ক্রিনে ফুল-স্ক্রিন ইনকামিং কল ডায়ালগ/স্ক্রিন আসবে (`Accept`, `Reject`, `Busy`, `Cancel/Timeout` অপশন সহ)।
* **Active Session:** এক্সেপ্ট করার পর Agora চ্যানেলে দুই প্রান্তের Two-Way Video, Two-Way Audio, Front/Back Camera সুইচ, Mic Mute/Unmute, Speaker Toggle, Call Duration এবং Network Quality সূচক রিয়েল-টাইম কাজ করবে।

### 🔹 API: Initiate Call
* **Endpoint:** `POST /api/call/initiate`
* **Request:**
  ```json
  {
    "receiver_id": 2,
    "call_type": "video"
  }
  ```
* **Response:**
  ```json
  {
    "status": true,
    "message": "Call initiated successfully",
    "data": {
      "call_id": 105,
      "call_session_id": "105",
      "channel_name": "call_1_2_1726718400",
      "call_type": "video",
      "active_engine": "agora",
      "agora_app_id": "c13c72df342d4a1386da678ba4c95f13",
      "agora_token": "007eJxTYDiw6Xf2xZtVj396tZ...==",
      "caller": { "id": 1, "name": "Nazmul", "avatar_url": "https://..." },
      "receiver": { "id": 2, "name": "Sara", "avatar_url": "https://..." },
      "reverb_channel": "call.105"
    }
  }
  ```

---

## Point 3: Video Call During Chat
* ভিডিও কল চলাকালীন স্ক্রিনের নিচে চ্যাট আইকন ট্যাপ করলে বটম শিট চ্যাট প্যানেল ওপেন হবে।
* User A মেসেজ পাঠালে User B-এর স্ক্রিনে সাথে সাথে রিয়েল-টাইম ভেসে উঠবে এবং User B রিপ্লাই করতে পারবে।
* এটি কোনো অস্থায়ী চ্যাট নয়; মেসেজটি স্বয়ংক্রিয়ভাবে User A ও User B-এর পার্মানেন্ট ইনবক্স কনভারসেশনে সেভ থাকবে। কল শেষ হওয়ার পরও মেসেজ হিস্ট্রি দেখা যাবে।

---

## Point 4: Video Call Chat Database
প্রতিটি মেসেজের সাথে ডাটাবেস টেবিল `messages` এ নিচের ফিল্ডগুলো সংরক্ষিত হয়:
* `id` (Primary Key)
* `sender_id` (প্রেরকের ইউজার আইডি)
* `receiver_id` (প্রাপকের ইউজার আইডি)
* `conversation_id` (সংশ্লিষ্ট কনভারসেশন আইডি)
* `call_id` / `call_session_id` (কল চলাকালীন পাঠানো হলে কল আইডি)
* `sent_during_call` (boolean 1/0)
* `message` (টেক্সট কন্টেন্ট)
* `type` (text/image/gift)
* `is_read` (boolean)
* `created_at`, `updated_at`

### 🔹 API: Send Message During Call
* **Endpoint:** `POST /api/call/message/send`
* **Request:**
  ```json
  {
    "call_id": "105",
    "receiver_id": 2,
    "message": "How are you doing today?",
    "type": "text"
  }
  ```

---

## Point 5: Profile → Online Status
ইউজার প্রোফাইল ওপেন করলে রিয়েল-টাইম স্ট্যাটাস প্রদর্শিত হবে:
1. `online` (অনলাইন এবং কল করার জন্য প্রস্তুত — Call button অ্যাক্টিভ)
2. `offline` (অফলাইন — Call বাটন ডিসেবল থাকবে)
3. `busy` / `in_call` (অন্য কলে ব্যস্ত — বাটন Disabled/Busy দেখাবে)
4. `in_live` (লাইভ স্ট্রিমিংয়ে আছে — কলে যুক্ত হওয়ার বদলে Watch Live অপশন আসবে)
5. `in_party` (ভয়েস পার্টি রুমে যুক্ত আছে)
6. `dnd` (Do Not Disturb)

---

## Point 6: Profile View → Call / Message
* প্রোফাইল ভিউ করলে নিজে থেকে অটো-কল হবে না।
* ইউজারের প্রোফাইলে পরিষ্কারভাবে আলাদা বাটন থাকবে: `Video Call`, `Message`, `Follow/Unfollow`, `Gift Send`।
* ইউজার নিজে বাটন প্রেস করলেই শুধুমাত্র কল রিকোয়েস্ট তৈরি হবে।

---

## Point 7: Online Presence System
* Laravel Reverb WebSocket-এর মাধ্যমে রিয়েল-টাইম প্রেজেন্স ট্র্যাকিং।
* ক্লায়েন্ট অ্যাপ প্রতি ২০-৩০ সেকেন্ডে হার্টবিট পাঠাবে:
  - `POST /api/user/heartbeat` (Body: `{"status": "online"}`)
* মাল্টিপল ডিভাইস বা ব্যাকগ্রাউন্ড সেশন হ্যান্ডেল করার জন্য লাস্ট সিন ও টোকেন ভিত্তিক মাল্টি-সেশন ট্র্যাকিং সক্রিয়।

---

## Point 8: Live Streaming System
TikTok/BIGO-Style ব্রডকাস্টিং আর্কিটেকচার:
* **Host Go-Live:** ক্যামেরা প্রিভিউ, টাইটেল, ক্যাটাগরি, কভার ফটো আপলোড করে **Start Live** বাটনে প্রেস করলে হোস্ট লাইভ ব্রডকাস্ট শুরু করবে।
* **Host Controls:** ক্যামেরা ফ্লিপ (Front/Back), Mic Mute/Unmute, বিউটি ফিল্টার, ভিউয়ার কাউন্ট, লাইক কাউন্ট, ডায়মন্ড আর্নিং ট্র্যাকিং।
* **Start Live API:** `POST /api/live/start` (Body: `{"title": "Weekend Music Show", "cover_image": "..."}`)

---

## Point 9: Live Streaming List
বর্তমানে যে সকল হোস্ট লাইভে রয়েছে তাদের ডায়নামিক পেজিনেটেড লিস্ট:
* **Endpoint:** `GET /api/lives/active?page=1&per_page=30&sort=popular`
* প্রতিটি কার্ডে হোস্টের নাম, প্রোফাইল পিকচার, লাইভ থাম্বনেইল, টাইটেল, ভিউয়ার কাউন্ট, লাইক কাউন্ট ও ব্যাজ প্রদর্শিত হবে।

---

## Point 10: Join Live
* দর্শক লাইভ কার্ডে ক্লিক করলে Viewer/Audience হিসেবে Agora চ্যানেলে সাবস্ক্রাইব করবে।
* **Endpoint:** `POST /api/live/join` (Body: `{"room_id": "45"}`)
* রেসপন্সে Agora Audience Token ও Reverb Presence চ্যানেল নাম ফেরত আসবে।

---

## Point 11: Live Chat
* লাইভ রুমে আনলিমিটেড পাবলিক টেক্সট মেসেজ ও কমেন্ট।
* **Endpoint:** `POST /api/live/send-message` (Body: `{"room_id": "45", "message": "Hi host! Nice song 🎵"}`)
* `LiveChatMessageEvent` ইভেন্টের মাধ্যমে রুমের সকল দর্শকের কাছে সাথে সাথে ব্রডকাস্ট হবে।
* হোস্টের জন্য মডারেশন অপশন: মেসেজ ডিলিট, ইউজার মিউট ও রিপোর্ট।

---

## Point 12: Live Gift System
* দর্শক হোস্টকে ভার্চুয়াল গিফট পাঠাতে পারবে (যেমন: Rose, Heart, Diamond Ring, Sports Car, Rocket)।
* **Endpoint:** `POST /api/live/send-gift`
* **Request:** `{"room_id": "45", "gift_id": 12, "quantity": 1}`
* **Flow:** ক্লায়েন্ট রিকোয়েস্ট ➔ সার্ভারে ব্যালেন্স চেক ➔ কয়েন ডিডাক্ট ➔ ট্রানজেকশন রেকর্ড ➔ হোস্ট আর্নিংস ক্রেডিট ➔ রিয়েল-টাইম গিফট অ্যানিমেশন ডিসপ্যাচ।

---

## Point 13: Gift Animation
* গিফট পাঠানোর সাথে সাথে লাইভ স্ক্রিনে ফুল-স্ক্রিন SVGA / Lottie অ্যানিমেশন প্লে হবে।
* `LiveGiftSentEvent` এর মাধ্যমে প্রেরকের নাম, অবতার, গিফটের নাম ও অ্যানিমেশন অ্যাসেট URL ব্রডকাস্ট হবে।

---

## Point 14: Host Earnings
* হোস্ট প্রাপ্ত প্রতিটি গিফটের কয়েন ভ্যালুর ৫০% (বা কনফিগার করা পার্সেন্টেজ) ডায়মন্ড ব্যালেন্সে আর্নিং হিসেবে যোগ হবে।
* টেবিল: `gift_transactions`, `user_gifts`, `wallets`.

---

## Point 15: Coin/Wallet System
* **Balance API:** `GET /api/wallet/balance`
* কয়েন ডিডাকশন সম্পূর্ণ `DB::transaction()` ব্লকের মধ্যে অ্যাটমিকভাবে সম্পন্ন হয়। ডাবল স্পেন্ডিং বা ইনভ্যালিড ডিডাকশন সার্ভার সাইডে ব্লকড।

---

## Point 16: Live Host → Guest Request
* হোস্ট চাইলে যেকোনো দর্শককে গেস্ট হিসেবে ইনভাইট পাঠাতে পারবে:
  - `POST /api/live/invite-cohost` (Body: `{"room_id": "45", "target_user_id": 8}`)

---

## Point 17: Multi-Guest Live
* BIGO/TikTok স্টাইলে হোস্টের সাথে সর্বাধিক ৪-৫ জন গেস্ট ভিডিও গ্রিডে একসাথে স্ক্রিন শেয়ার করে কথা বলতে পারবে।

---

## Point 18: Guest Request System
* দর্শক লাইভে যুক্ত হওয়ার জন্য রিকোয়েস্ট পাঠাবে:
  - `POST /api/live/join-request` (Body: `{"room_id": "45"}`)
* হোস্টের স্ক্রিনে রিয়েল-টাইম পপআপ আসবে (`Accept` / `Reject` বাটন সহ)।
* হোস্ট এক্সেপ্ট করলে গেস্ট ব্রডকাস্টার হিসেবে Agora পাবলিশিং টোকেন পাবে।

---

## Point 19: Host Controls
হোস্টের সম্পূর্ণ নিয়ন্ত্রণ সুবিধা:
* গেস্ট রিমুভ / কিক (`POST /api/live/kick-guest`)
* গেস্টের মাইক মিউট (`POST /api/live/mute-toggle`)
* লাইভ সমাপ্ত করা (`POST /api/live/end`)
* স্প্যামার ব্লক / রিপোর্ট।

---

## Point 20: Voice Party Room
* ডেডিকেটেড অডিও আড্ডা রুম (যেমন: `Party Room #123`)।
* **Create Party Room API:** `POST /api/party-rooms/create`
* **Request:** `{"title": "Bangla Voice Adda 🎙️", "max_seats": 8}`

---

## Point 21: Voice Room Seats
* ৮ থেকে ১০টি সিট গ্রিড লেআউট (হোস্ট টপ সিটে এবং পার্টিসিপেন্টরা সিট ১ থেকে সিট ৮ এ বসবে)।

---

## Point 22: Voice Room Request
* অডিয়েন্স `Request to Speak` বাটনে চাপলে হোস্ট সিট অ্যাসাইন করবে:
  - `POST /api/party-rooms/{id}/take-seat` (Body: `{"seat_index": 2}`)

---

## Point 23: Voice Room Chat
* ভয়েস পার্টি রুমের ভেতরে আনলিমিটেড টেক্সট চ্যাট সাপোর্ট:
  - `POST /api/party-rooms/{id}/send-message`

---

## Point 24: Voice Room Gifts
* ভয়েস রুমে হোস্ট বা স্পিকারদের ভার্চুয়াল গিফট পাঠানো:
  - `POST /api/party-rooms/{id}/send-gift`

---

## Point 25: Follow System
* **Follow User:** `POST /api/user/follow` (Body: `{"target_user_id": 2}`)
* **Unfollow User:** `POST /api/user/unfollow` (Body: `{"target_user_id": 2}`)
* হোস্ট লাইভ শুরু করলে সকল ফলোয়ারদের কাছে নোটিফিকেশন যাবে।

---

## Point 26: Notifications
রিয়েল-টাইম পুশ ও ইন-অ্যাপ নোটিফিকেশন (১১টি টাইপ):
1. `incoming_call` 2. `missed_call` 3. `new_message` 4. `live_started` 5. `viewer_joined` 6. `guest_requested` 7. `guest_accepted` 8. `gift_received` 9. `user_followed` 10. `party_room_invitation` 11. `voice_seat_responded`.

---

## Point 27: Call Status Management
কলের লাইফসাইকেল স্টেট মেশিন:
```
Calling (ডায়ালিং) ──► Ringing ──► Accepted ──► Connected ──► Active ──► Ended
       │                   │
   (Timeout)           (Rejected)
```

---

## Point 28: Network Handling
* দুর্বল ইন্টারনেট বা নেটওয়ার্ক ড্রপ হলে UI হ্যাং হবে না।
* Wi-Fi থেকে Mobile Data পরিবর্তনের সময় স্বয়ংক্রিয় রি-কানেকশন।

---

## Point 29: Background / Minimize Video Call
* ব্যাক বাটন প্রেস করলে কল কাটবে না; স্ক্রিনে ফ্লোটিং পিকচার-ইন-পিকচার (PiP) মিনি উইন্ডো ওপেন হবে।
* **Sync API:** `POST /api/call/minimize`, `POST /api/call/restore`

---

## Point 30: Call End
* **End Call API:** `POST /api/call/end` (Body: `{"call_id": 105, "duration_seconds": 180}`)
* সাথে সাথে Agora Channel Leave হবে এবং ইউজারের স্ট্যাটাস `online` এ ফিরে আসবে।

---

## Point 31: Call History
* **API:** `GET /api/call/history` (সম্পূর্ণ কল হিস্ট্রি, সময়কাল এবং স্ট্যাটাস)।

---

## Point 32: Live History
* হোস্টের অতীত লাইভ সেশনের বিস্তারিত পরিসংখ্যান (মোট ভিউয়ার, সর্বোচ্চ পিক ভিউয়ার, মোট অর্জিত ডায়মন্ড ও সময়কাল)।

---

## Point 33: Viewer Count
* ভিউয়ার জয়েন বা লিভ করলে `LiveViewerCountUpdated` ইভেন্ট ব্রডকাস্ট হবে এবং ডুপ্লিকেট কানেকশন ফিল্টার হবে।

---

## Point 34: Live Ranking / Popularity
* লাইভ ফিড লিস্ট দর্শক সংখ্যা, গিফট ও লাইকের ভিত্তিতে স্বয়ংক্রিয়ভাবে ফিল্টার ও সাজানো থাকবে।

---

## Point 35: Likes
* **Send Like API:** `POST /api/live/like` (Body: `{"room_id": "45", "count": 10}`)
* `LiveLikeSent` ইভেন্টের মাধ্যমে ভাসমান হার্ট অ্যানিমেশন দেখা যাবে।

---

## Point 36: Share Live
* সোশ্যাল মিডিয়া ও ফ্রেন্ডদের কাছে লাইভ শেয়ার ডিপ লিংক: `https://chinchins.live/live/45`।

---

## Point 37: Report / Block
* **Block:** `POST /api/chat/block` (Body: `{"target_user_id": 2}`)
* **Report:** `POST /api/chat/report` (Body: `{"reported_user_id": 2, "reason": "Spam"}`)

---

## Point 38: Moderation
* হোস্ট এবং অ্যাডমিন যে কাউকে লাইভ থেকে ব্যান বা কিক করতে পারে।

---

## Point 39: Security Requirements
* Agora App Certificate কখনো ক্লায়েন্টে থাকবে না; ব্যাকএন্ড থেকে HMAC-SHA256 ডায়নামিক টোকেন তৈরি হবে।

---

## Point 40: WebSocket Security
* Laravel Reverb-এর সমস্ত চ্যানেল Sanctum Bearer Token দিয়ে সুরক্ষিত।

---

## Point 41: Gift Security
* সমস্ত গিফট ট্রানজেকশন সার্ভার সাইডে ব্যালেন্স ও রিসিভার আইডি ভ্যালিডেট করে এক্সিকিউট হয়।

---

## Point 42: Database Transaction
* ব্যালেন্স কাটা ও আর্নিংস ক্রেডিট `DB::beginTransaction()` এবং `DB::commit()` এ নিরাপদ।

---

## Point 43: Real-Time Event Architecture
* Reverb ইভেন্ট ক্যাটালগ: `CallIncoming`, `CallAccepted`, `CallEnded`, `MessageSentEvent`, `LiveGiftSentEvent`, `LiveViewerCountUpdated`, `LiveJoinRequested`।

---

## Point 44: Agora Channel Architecture
* ইউনিক চ্যানেল নেমিং: `call_{caller}_{receiver}_{time}`, `live_{host}_{time}`, `party_{room}_{time}`।

---

## Point 45: Agora Roles
* 1-on-1 Call: Broadcasters
* Live: Host = Broadcaster, Viewers = Audience, Co-Hosts = Broadcasters.
* Voice Party: Host & Speakers = Broadcasters, Listeners = Audience.

---

## Point 46: App Performance
* মেমোরি লিক এবং UI Freeze রোধ করতে Agora রেন্ডারার ও ইভেন্ট লিসেনার প্রপারলি ডিসপোজ হবে।

---

## Point 47: Flutter Lifecycle
* Foreground ➔ Background ➔ Foreground ট্রানজিশনে Agora ইঞ্জিন একাধিকবার ইনিশিয়ালাইজ হবে না।

---

## Point 48: Camera & Microphone Permission
* রানটাইম পারমিশন হ্যান্ডলিং এবং ডিনাই হলে ইউজার ফ্রেন্ডলি ওয়ার্নিং।

---

## Point 49: Beauty / Camera Filters
* **API:** `GET /api/filters`, `GET /api/camera/filters` (স্মুথিং ও ফিল্টার সেটিংস)।

---

## Point 50: Message System
* সমন্বিত মেসেজ সিস্টেম (Text, Media, System Messages, Gift Messages)।

---

## Point 51: Message Persistence
* স্ক্রিন পরিবর্তন বা অ্যাপ বন্ধ করলেও মেসেজ ডাটাবেসে আজীবন সংরক্ষিত থাকবে।

---

## Point 52: Call + Chat Synchronization
* কল শেষ হলে চ্যাট বক্সে `Video Call — 12:35` সিস্টেম রেকর্ড সংরক্ষিত হবে।

---

## Point 53: Live Room Data
* `live_streams` স্কিমা: `id`, `host_id`, `channel_name`, `title`, `cover_image`, `status`, `viewer_count`, `likes_count`, `total_diamonds_earned`।

---

## Point 54: Party Room Data
* `party_rooms` স্কিমা: `id`, `host_id`, `title`, `room_type`, `max_seats`, `channel_name`, `status`।

---

## Point 55: API Requirements
* RESTful এন্ডপয়েন্ট স্ট্যান্ডার্ড অনুযায়ী JSON রেসপন্স কাঠামো।

---

## Point 56: Redis / Queue
* হেভি নোটিফিকেশন ও ব্যাকগ্রাউন্ড টাস্ক Redis কিউ ওয়ার্কার দিয়ে অপ্টিমাইজড।

---

## Point 57: VPS Requirements
* Hostinger VPS-এ Nginx, PHP-FPM, MySQL, Redis, Supervisor ও Laravel Reverb কনফিগারেশন।

---

## Point 58: SSL
* প্রোডাকশনে সিকিউর `https://` এবং `wss://` এনক্রিপশন সক্রিয়।

---

## Point 59: Error Handling
* স্ট্যান্ডার্ড এরর কোড (`INSUFFICIENT_BALANCE`, `USER_BUSY`, `STREAM_ENDED`, `UNAUTHENTICATED`)।

---

## Point 60: Reconnection
* নেটওয়ার্ক বিচ্ছিন্ন হলে প্রতি ২ সেকেন্ডে অটোমেটিক রি-কানেকশন পলিসি।

---

## Point 61: Duplicate Prevention
* একাধিকবার ক্লিক বা নেটওয়ার্ক ডাবল রিকোয়েস্টে ডুপ্লিকেট ব্যালেন্স ডিডাকশন প্রতিরোধ।

---

## Point 62: Race Condition Protection
* ডাটাবেস লেভেলে পেসিমিউজিক লকিং `lockForUpdate()` ব্যবহার।

---

## Point 63: Admin Configuration
* অ্যাডমিন প্যানেল থেকে গিফটের দাম, কয়েন রেট ও পার্সেন্টেজ ডায়নামিক পরিবর্তনযোগ্য।

---

## Point 64: Analytics
* লাইভ শেষে পারফরম্যান্স মেট্রিক্স (পিক ভিউয়ার, গিফট সংখ্যা ও ফলোয়ার গেইন)।

---

## Point 65: Important User Flow
* 1-to-1 Call, Live Broadcast এবং Voice Party এর সম্পূর্ণ ফ্লো ডায়াগ্রাম।

---

## Point 66: Most Important Requirement
* বিদ্যমান UI, ডিজাইন, ডাটাবেস ও বিজনেস লজিক অক্ষুণ্ণ রেখে সিস্টেমটি সম্পূর্ণ প্রোডাকশন-রেডি।

---

## Point 67: Final Testing
* Android ও iOS ডিভাইসে Wi-Fi এবং Mobile Data-তে সম্পূর্ণ ৬৭টি ফিচারের এন্ড-টু-এন্ড টেস্ট ভেরিফিকেশন।
