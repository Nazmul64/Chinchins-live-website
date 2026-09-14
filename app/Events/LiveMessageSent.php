<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $liveStreamId;
    public $messageData;

    public function __construct($liveStreamId, array $messageData)
    {
        $this->liveStreamId = (string) $liveStreamId;
        $this->messageData = $messageData;
    }

    public function broadcastOn()
    {
        return [
            new PresenceChannel('presence-live.' . $this->liveStreamId),
            new Channel('live.' . $this->liveStreamId),
        ];
    }

    public function broadcastAs()
    {
        return 'LiveMessageSent';
    }

    public function broadcastWith()
    {
        return $this->messageData;
    }
}
