<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CoHostRequestReceived implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $hostId;
    public $payload;

    /**
     * Create a new event instance.
     *
     * @param int|string $hostId
     * @param array $payload
     */
    public function __construct($hostId, array $payload = [])
    {
        $this->hostId = (string) $hostId;
        $this->payload = $payload;
    }

    /**
     * Broadcast channels for Co-Host Request Received event.
     */
    public function broadcastOn(): array
    {
        $roomId = $this->payload['room_id'] ?? $this->payload['live_stream_id'] ?? null;
        
        $channels = [
            new Channel('live-host.' . $this->hostId),
            new Channel('user.' . $this->hostId),
            new PrivateChannel('live-host.' . $this->hostId),
            new PrivateChannel('user.' . $this->hostId),
        ];

        if ($roomId) {
            $channels[] = new Channel('live-room.' . $roomId);
            $channels[] = new Channel('live-stream.' . $roomId);
            $channels[] = new PresenceChannel('presence-live-stream.' . $roomId);
            $channels[] = new PresenceChannel('presence-live.' . $roomId);
        }

        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'CoHostRequestReceived';
    }

    /**
     * Broadcast payload.
     */
    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
