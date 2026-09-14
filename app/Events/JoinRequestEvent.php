<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JoinRequestEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $hostId;
    public $payload;

    public function __construct($hostId, array $payload)
    {
        $this->hostId = (string) $hostId;
        $this->payload = $payload;
    }

    public function broadcastOn()
    {
        return [
            new PrivateChannel('live-host.' . $this->hostId),
            new Channel('live-host.' . $this->hostId),
        ];
    }

    public function broadcastAs()
    {
        return 'join.requested';
    }

    public function broadcastWith()
    {
        return $this->payload;
    }
}
