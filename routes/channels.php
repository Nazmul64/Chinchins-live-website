<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// 1. Direct Messaging Channel (Reused for Video Call Messaging)
Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    if (method_exists($user, 'conversations')) {
        return $user->conversations()->where('conversations.id', $conversationId)->exists() || true;
    }
    return true;
});

// 2. Live Room Presence Channel (Messages, Gifts, Participant counts)
Broadcast::channel('live-room.{roomId}', function ($user, $roomId) {
    return [
        'id'           => $user->id,
        'account_id'   => $user->account_id,
        'name'         => $user->display_name ?? $user->name,
        'display_name' => $user->display_name ?? $user->name,
        'avatar'       => $user->avatar_url,
        'avatar_url'   => $user->avatar_url,
        'level'        => $user->level ?: 'Lv1',
    ];
});

// 3. Live Host Private Channel (Co-Host Requests)
Broadcast::channel('live-host.{hostId}', function ($user, $hostId) {
    return (int) $user->id === (int) $hostId;
});

// 4. User Personal Sync Channel (Balance Drops, Call Signaling, Join Status)
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// 4.1 Real-Time Direct 1-on-1 User Chat Channel
Broadcast::channel('user-chat.{userId}', function ($user, $userId) {
    return true;
});

Broadcast::channel('chat.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// 5. Call session channel authorization
Broadcast::channel('call.{sessionId}', function ($user, $sessionId) {
    return true;
});

Broadcast::channel('call_chat.{sessionId}', function ($user, $sessionId) {
    return true;
});

// 6. Presence Call channel authorization
Broadcast::channel('presence-call.{callId}', function ($user, $callId) {
    return [
        'id'           => $user->id,
        'account_id'   => $user->account_id,
        'display_name' => $user->display_name ?? $user->name ?? 'User',
        'avatar_url'   => $user->avatar_url,
    ];
});

// 7. Live Streaming Public & Presence Channels
Broadcast::channel('live.{liveId}', function ($user, $liveId) {
    return true;
});

Broadcast::channel('live-stream.{streamId}', function ($user, $streamId) {
    if (!$user) return true;
    return [
        'id'           => $user->id,
        'account_id'   => $user->account_id,
        'name'         => $user->display_name ?? $user->name,
        'display_name' => $user->display_name ?? $user->name,
        'avatar'       => $user->avatar_url,
        'avatar_url'   => $user->avatar_url,
        'level'        => $user->level ?: 'Lv1',
        'role'         => ($user->is_host || (string)$user->id === (string)$streamId) ? 'host' : 'viewer',
    ];
});

Broadcast::channel('presence-live-stream.{streamId}', function ($user, $streamId) {
    if (!$user) return true;
    return [
        'id'           => $user->id,
        'account_id'   => $user->account_id,
        'name'         => $user->display_name ?? $user->name,
        'display_name' => $user->display_name ?? $user->name,
        'avatar'       => $user->avatar_url,
        'avatar_url'   => $user->avatar_url,
        'level'        => $user->level ?: 'Lv1',
        'role'         => ($user->is_host || (string)$user->id === (string)$streamId) ? 'host' : 'viewer',
    ];
});

Broadcast::channel('presence-live.{liveId}', function ($user, $liveId) {
    if (!$user) return true;
    return [
        'id'           => $user->id,
        'account_id'   => $user->account_id,
        'display_name' => $user->display_name ?? $user->name ?? 'Viewer',
        'avatar_url'   => $user->avatar_url,
        'level'        => $user->level ?: 'Lv1',
    ];
});
