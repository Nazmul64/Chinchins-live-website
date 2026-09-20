<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SeatUpdatedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $roomId;
    public $payload;

    /**
     * Create a new event instance.
     *
     * @param int|string $roomId
     * @param array $payload
     */
    public function __construct($roomId, array $payload = [])
    {
        $this->roomId = (string) $roomId;
        $this->payload = $payload;
    }

    /**
     * Broadcast channels for Voice Party Room Seat updates.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('party.' . $this->roomId),
            new Channel('party-room.' . $this->roomId),
            new Channel('party-room-seat.' . $this->roomId),
            new PresenceChannel('presence-party.' . $this->roomId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'SeatUpdatedEvent';
    }

    /**
     * Broadcast payload.
     */
    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
