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
    // Allow if user is caller or receiver of the call room
    $call = \App\Models\Call::where('room_id', $roomId)->first();
    if ($call) {
        return (int) $user->id === (int) $call->caller_id || (int) $user->id === (int) $call->receiver_id;
    }
    return true;
});
