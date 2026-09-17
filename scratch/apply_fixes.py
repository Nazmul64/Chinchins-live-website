import os

flutter_lib = r"F:\Chinchins Live\lib"

# ==========================================
# 1. Update signaling_service.dart (Event Deduplication)
# ==========================================
signaling_path = os.path.join(flutter_lib, "core", "services", "signaling_service.dart")
with open(signaling_path, "r", encoding="utf-8") as f:
    s_content = f.read()

if "_isDuplicateEvent" not in s_content:
    s_content = s_content.replace(
        "  bool get isConnected => _isConnected;",
        """  bool get isConnected => _isConnected;
  final Map<String, int> _recentEventSignatures = {};

  bool _isDuplicateEvent(String eventName, Map<String, dynamic> data) {
    final msgId = data['id'] ?? data['message_id'] ?? data['message']?['id'];
    final msgText = data['message'] is String ? data['message'] : data['message']?['message'] ?? data['text'];
    final senderId = data['sender_id'] ?? data['user_id'] ?? data['message']?['sender_id'];
    final sig = '${eventName}_${msgId ?? ''}_${senderId ?? ''}_${msgText ?? ''}';

    final now = DateTime.now().millisecondsSinceEpoch;
    _recentEventSignatures.removeWhere((_, time) => now - time > 3500);

    if (_recentEventSignatures.containsKey(sig)) {
      return true;
    }
    _recentEventSignatures[sig] = now;
    return false;
  }""",
        1
    )

    old_user_chat = "    final isUserChatChannel = chName.contains('user-chat.') ||\n        chName.contains('chat.') ||\n        chName.contains('conversation.');"
    new_user_chat = """    final isUserChatChannel = chName.contains('user-chat.') ||
        chName.contains('chat.') ||
        chName.contains('conversation.');

    if (_isDuplicateEvent(cleanName, data)) {
      return;
    }"""
    s_content = s_content.replace(old_user_chat, new_user_chat, 1)

    with open(signaling_path, "w", encoding="utf-8") as f:
        f.write(s_content)
    print("1. signaling_service.dart updated")
else:
    print("1. signaling_service.dart already has deduplication")


# ==========================================
# 2. Update in_call_chat_overlay.dart (Message Deduplication)
# ==========================================
chat_path = os.path.join(flutter_lib, "features", "call", "widgets", "in_call_chat_overlay.dart")
with open(chat_path, "r", encoding="utf-8") as f:
    c_content = f.read()

old_add_msg = """  void addIncomingMessage(Map<String, dynamic> data) {
    if (!mounted) return;
    final msg = CallChatMessage.fromJson(data, myId: _currentUserId ?? widget.myId);
    
    // Prevent duplicate messages if sender already added locally
    if (msg.isMe) {
      final isDuplicate = _messages.any((m) =>
          m.id == msg.id ||
          (m.isMe && m.message == msg.message && DateTime.now().difference(m.timestamp).inSeconds < 4));
      if (isDuplicate) return;
    }

    setState(() {
      _messages.add(msg);
    });
    _scrollToBottom();
  }"""

new_add_msg = """  void addIncomingMessage(Map<String, dynamic> data) {
    if (!mounted) return;
    final msg = CallChatMessage.fromJson(data, myId: _currentUserId ?? widget.myId);
    
    // Strict deduplication for both incoming and locally-sent messages
    final isDuplicate = _messages.any((m) =>
        m.id == msg.id ||
        (m.message == msg.message &&
         m.type == msg.type &&
         (m.isMe == msg.isMe || m.senderName == msg.senderName) &&
         (DateTime.now().difference(m.timestamp).inSeconds).abs() < 5));
    if (isDuplicate) return;

    setState(() {
      _messages.add(msg);
    });
    _scrollToBottom();
  }"""

if old_add_msg in c_content:
    c_content = c_content.replace(old_add_msg, new_add_msg, 1)
    with open(chat_path, "w", encoding="utf-8") as f:
        f.write(c_content)
    print("2. in_call_chat_overlay.dart updated")
else:
    print("2. in_call_chat_overlay.dart check")


# ==========================================
# 3. Update agora_call_screen.dart (Gift WebSocket + Animation + Chat)
# ==========================================
agora_path = os.path.join(flutter_lib, "features", "call", "screens", "agora_call_screen.dart")
with open(agora_path, "r", encoding="utf-8") as f:
    a_content = f.read()

if "StreamSubscription? _wsGiftSub;" not in a_content:
    a_content = a_content.replace(
        "  StreamSubscription? _wsInCallMsgSub;",
        "  StreamSubscription? _wsInCallMsgSub;\n  StreamSubscription? _wsGiftSub;",
        1
    )

    old_subscribe = """    _wsInCallMsgSub = signaling.onInCallMessage.listen((data) {
      debugPrint('[AgoraCallScreen] Received InCallMessage via WebSocket: $data');
      if (mounted) {
        _chatKey.currentState?.addIncomingMessage(data);
      }
    });"""

    new_subscribe = """    _wsInCallMsgSub = signaling.onInCallMessage.listen((data) {
      debugPrint('[AgoraCallScreen] Received InCallMessage via WebSocket: $data');
      if (mounted) {
        _chatKey.currentState?.addIncomingMessage(data);
      }
    });

    _wsGiftSub = signaling.onLiveGift.listen((data) {
      debugPrint('[AgoraCallScreen] Received Gift via WebSocket: $data');
      final giftData = data['gift_data'] ?? data['gift'];
      final giftName = (giftData is Map ? giftData['name'] : null) ?? data['gift_name'] ?? 'Luxury Gift';
      final coins = data['total_coins'] ?? (giftData is Map ? giftData['coin_price'] : null) ?? data['coins'] ?? 100;
      final animUrl = (giftData is Map ? (giftData['animation_asset_url'] ?? giftData['icon_url']) : null) ??
          data['animation_url']?.toString() ?? data['animation_asset_url']?.toString() ?? data['image_url']?.toString();
      final senderName = data['sender_name'] ?? data['user_name'] ?? data['sender']?['name'] ?? 'Partner';

      if (mounted) {
        _giftAnimKey.currentState?.playGiftAnimationDynamic(
          giftName: giftName,
          animationUrl: animUrl,
          senderName: senderName,
          coins: coins is int ? coins : int.tryParse('$coins') ?? 100,
        );

        _chatKey.currentState?.addIncomingMessage({
          'id': 'gift_${DateTime.now().millisecondsSinceEpoch}',
          'sender_name': senderName,
          'message': '🎁 sent $giftName ($coins Coins)!',
          'type': 'gift',
          'is_me': false,
        });
      }
    });"""

    a_content = a_content.replace(old_subscribe, new_subscribe, 1)

    # Cancel in dispose
    a_content = a_content.replace(
        "    _wsInCallMsgSub?.cancel();",
        "    _wsInCallMsgSub?.cancel();\n    _wsGiftSub?.cancel();",
        1
    )

    with open(agora_path, "w", encoding="utf-8") as f:
        f.write(a_content)
    print("3. agora_call_screen.dart updated")
else:
    print("3. agora_call_screen.dart already updated")


# ==========================================
# 4. Update video_call_screen.dart (Gift WebSocket + Animation + Chat)
# ==========================================
video_path = os.path.join(flutter_lib, "features", "call", "screens", "video_call_screen.dart")
with open(video_path, "r", encoding="utf-8") as f:
    v_content = f.read()

if "StreamSubscription? _wsGiftSub;" not in v_content:
    v_content = v_content.replace(
        "  StreamSubscription? _wsInCallMsgSub;",
        "  StreamSubscription? _wsInCallMsgSub;\n  StreamSubscription? _wsGiftSub;",
        1
    )

    old_v_sub = """    _wsInCallMsgSub = signaling.onInCallMessage.listen((data) {
      if (mounted) {
        _chatKey.currentState?.addIncomingMessage(data);
      }
    });"""

    new_v_sub = """    _wsInCallMsgSub = signaling.onInCallMessage.listen((data) {
      if (mounted) {
        _chatKey.currentState?.addIncomingMessage(data);
      }
    });

    _wsGiftSub = signaling.onLiveGift.listen((data) {
      debugPrint('[VideoCallScreen] Received Gift via WebSocket: $data');
      final giftData = data['gift_data'] ?? data['gift'];
      final giftName = (giftData is Map ? giftData['name'] : null) ?? data['gift_name'] ?? 'Luxury Gift';
      final coins = data['total_coins'] ?? (giftData is Map ? giftData['coin_price'] : null) ?? data['coins'] ?? 100;
      final animUrl = (giftData is Map ? (giftData['animation_asset_url'] ?? giftData['icon_url']) : null) ??
          data['animation_url']?.toString() ?? data['animation_asset_url']?.toString() ?? data['image_url']?.toString();
      final senderName = data['sender_name'] ?? data['user_name'] ?? data['sender']?['name'] ?? 'Partner';

      if (mounted) {
        _giftAnimKey.currentState?.playGiftAnimationDynamic(
          giftName: giftName,
          animationUrl: animUrl,
          senderName: senderName,
          coins: coins is int ? coins : int.tryParse('$coins') ?? 100,
        );

        _chatKey.currentState?.addIncomingMessage({
          'id': 'gift_${DateTime.now().millisecondsSinceEpoch}',
          'sender_name': senderName,
          'message': '🎁 sent $giftName ($coins Coins)!',
          'type': 'gift',
          'is_me': false,
        });
      }
    });"""

    v_content = v_content.replace(old_v_sub, new_v_sub, 1)

    v_content = v_content.replace(
        "    _wsInCallMsgSub?.cancel();",
        "    _wsInCallMsgSub?.cancel();\n    _wsGiftSub?.cancel();",
        1
    )

    with open(video_path, "w", encoding="utf-8") as f:
        f.write(v_content)
    print("4. video_call_screen.dart updated")
else:
    print("4. video_call_screen.dart already updated")


# ==========================================
# 5. Update live_room_screen.dart (Audio & Video setup)
# ==========================================
live_path = os.path.join(flutter_lib, "features", "call", "screens", "live_room_screen.dart")
with open(live_path, "r", encoding="utf-8") as f:
    l_content = f.read()

if "await _rtcEngine!.enableAudio();" not in l_content:
    old_agora_setup = """      await _rtcEngine!.enableVideo();
      await _rtcEngine!.setClientRole(
        role: isBroadcaster ? ClientRoleType.clientRoleBroadcaster : ClientRoleType.clientRoleAudience,
      );

      if (isBroadcaster) {
        await _rtcEngine!.setVideoEncoderConfiguration(
          const VideoEncoderConfiguration(
            dimensions: VideoDimensions(width: 1280, height: 720),
            frameRate: 30,
            bitrate: 2000,
            orientationMode: OrientationMode.orientationModeAdaptive,
          ),
        );
        await _rtcEngine!.startPreview();
      }

      await _rtcEngine!.joinChannel(
        token: token,
        channelId: channelName,
        uid: uid,
        options: ChannelMediaOptions(
          publishCameraTrack: isBroadcaster,
          publishMicrophoneTrack: isBroadcaster,
          autoSubscribeAudio: true,
          autoSubscribeVideo: true,
          clientRoleType: isBroadcaster ? ClientRoleType.clientRoleBroadcaster : ClientRoleType.clientRoleAudience,
        ),
      );"""

    new_agora_setup = """      await _rtcEngine!.enableVideo();
      await _rtcEngine!.enableAudio();
      await _rtcEngine!.setDefaultAudioRouteToSpeakerphone(true);
      await _rtcEngine!.setClientRole(
        role: isBroadcaster ? ClientRoleType.clientRoleBroadcaster : ClientRoleType.clientRoleAudience,
      );

      if (isBroadcaster) {
        await _rtcEngine!.setVideoEncoderConfiguration(
          const VideoEncoderConfiguration(
            dimensions: VideoDimensions(width: 1280, height: 720),
            frameRate: 30,
            bitrate: 2000,
            orientationMode: OrientationMode.orientationModeAdaptive,
          ),
        );
        await _rtcEngine!.startPreview();
        if (mounted) {
          setState(() => _isEngineReady = true);
        }
      }

      await _rtcEngine!.joinChannel(
        token: token,
        channelId: channelName,
        uid: uid,
        options: ChannelMediaOptions(
          publishCameraTrack: isBroadcaster,
          publishMicrophoneTrack: isBroadcaster,
          autoSubscribeAudio: true,
          autoSubscribeVideo: true,
          enableAudioRecordingOrPlayout: true,
          clientRoleType: isBroadcaster ? ClientRoleType.clientRoleBroadcaster : ClientRoleType.clientRoleAudience,
        ),
      );"""

    l_content = l_content.replace(old_agora_setup, new_agora_setup, 1)
    with open(live_path, "w", encoding="utf-8") as f:
        f.write(l_content)
    print("5. live_room_screen.dart updated")
else:
    print("5. live_room_screen.dart audio already enabled")

