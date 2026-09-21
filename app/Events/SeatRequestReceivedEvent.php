<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SeatRequestReceivedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $roomId;
    public $payload;

    public function __construct($roomId, array $payload = [])
    {
        $this->roomId = (string) $roomId;
        $this->payload = $payload;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('party-room.' . $this->roomId),
            new Channel('party.' . $this->roomId),
            new PrivateChannel('party-room.' . $this->roomId),
            new Channel('party-room-seat.' . $this->roomId),
            new PresenceChannel('presence-party.' . $this->roomId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'SeatRequestReceivedEvent';
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
