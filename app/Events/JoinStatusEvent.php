<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JoinStatusEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $payload;

    public function __construct($userId, array $payload)
    {
        $this->userId = (string) $userId;
        $this->payload = $payload;
    }

    public function broadcastOn()
    {
        return [
            new PrivateChannel('user.' . $this->userId),
            new Channel('user.' . $this->userId),
        ];
    }

    public function broadcastAs()
    {
        return 'join.status';
    }

    public function broadcastWith()
    {
        return $this->payload;
    }
}
