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

// Private channel for each user (e.g. private-user.10, private-user.25)
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Call session channel authorization
Broadcast::channel('call.{roomId}', function ($user, $roomId) {
    return true;
});

Broadcast::channel('call_chat.{roomId}', function ($user, $roomId) {
    return true;
});

// Presence Call channel authorization
Broadcast::channel('presence-call.{callId}', function ($user, $callId) {
    return [
        'id'           => $user->id,
        'account_id'   => $user->account_id,
        'display_name' => $user->display_name ?? $user->name ?? 'User',
        'avatar_url'   => $user->avatar_url,
    ];
});

// Live Streaming Public & Presence Channels
Broadcast::channel('live.{liveId}', function ($user, $liveId) {
    return true;
});

Broadcast::channel('presence-live.{liveId}', function ($user, $liveId) {
    return [
        'id'           => $user->id,
        'account_id'   => $user->account_id,
        'display_name' => $user->display_name ?? $user->name ?? 'Viewer',
        'avatar_url'   => $user->avatar_url,
        'level'        => $user->level ?: 'Lv1',
    ];
});
