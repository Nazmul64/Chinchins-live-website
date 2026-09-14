<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveJoinRequested implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $liveStreamId;
    public $hostId;
    public $requestData;

    public function __construct($liveStreamId, $hostId, array $requestData)
    {
        $this->liveStreamId = (string) $liveStreamId;
        $this->hostId = (string) $hostId;
        $this->requestData = $requestData;
    }

    public function broadcastOn()
    {
        return [
            new PresenceChannel('presence-live.' . $this->liveStreamId),
            new Channel('user.' . $this->hostId),
        ];
    }

    public function broadcastAs()
    {
        return 'LiveJoinRequested';
    }

    public function broadcastWith()
    {
        return $this->requestData;
    }
}
