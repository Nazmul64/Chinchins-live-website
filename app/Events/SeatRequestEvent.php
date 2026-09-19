<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SeatRequestEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $partyRoomId;
    public $payload;

    public function __construct($partyRoomId, array $payload)
    {
        $this->partyRoomId = (string) $partyRoomId;
        $this->payload = $payload;
    }

    public function broadcastOn()
    {
        return [
            new Channel('party-room.' . $this->partyRoomId),
            new PrivateChannel('party-room.' . $this->partyRoomId),
            new Channel('party-room-seat.' . $this->partyRoomId),
        ];
    }

    public function broadcastAs()
    {
        return 'seat.requested';
    }

    public function broadcastWith()
    {
        return $this->payload;
    }
}
