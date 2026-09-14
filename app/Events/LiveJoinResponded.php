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
            new PresenceChannel('presence-live.' . $this->liveStreamId),
            new Channel('user.' . $this->guestUserId),
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
