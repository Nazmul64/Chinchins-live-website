<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveJoinResponded implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $liveStreamId;
    public $guestUserId;
    public $responseData;

    public function __construct($liveStreamId, $guestUserId, array $responseData)
    {
        $this->liveStreamId = (string) $liveStreamId;
        $this->guestUserId = (string) $guestUserId;
        $this->responseData = $responseData;
    }

    public function broadcastOn()
    {
        return [
            new \Illuminate\Broadcasting\PrivateChannel('user.' . $this->guestUserId),
            new \Illuminate\Broadcasting\PrivateChannel('private-user.' . $this->guestUserId),
            new Channel('user.' . $this->guestUserId),
            new Channel('live_stream.' . $this->liveStreamId),
            new PresenceChannel('presence-live.' . $this->liveStreamId),
        ];
    }

    public function broadcastAs()
    {
        return 'LiveJoinResponded';
    }

    public function broadcastWith()
    {
        return $this->responseData;
    }
}
