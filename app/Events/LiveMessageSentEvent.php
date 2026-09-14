<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveMessageSentEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $roomId;
    public $messageData;

    public function __construct($roomId, array $messageData)
    {
        $this->roomId = (string) $roomId;
        $this->messageData = $messageData;
    }

    /**
     * Broadcast on live room presence and public channels.
     */
    public function broadcastOn()
    {
        return [
            new PresenceChannel('live-room.' . $this->roomId),
            new Channel('live-room.' . $this->roomId),
            new Channel('live-stream.' . $this->roomId),
        ];
    }

    /**
     * Broadcast event name.
     */
    public function broadcastAs()
    {
        return 'live.message';
    }

    /**
     * Payload.
     */
    public function broadcastWith()
    {
        return $this->messageData;
    }
}
