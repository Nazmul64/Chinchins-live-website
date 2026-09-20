<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CoHostRequestAccepted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $payload;

    /**
     * Create a new event instance.
     *
     * @param int|string $userId
     * @param array $payload
     */
    public function __construct($userId, array $payload = [])
    {
        $this->userId = (string) $userId;
        $this->payload = $payload;
    }

    /**
     * Broadcast channels for Co-Host Request Accepted event.
     */
    public function broadcastOn(): array
    {
        $roomId = $this->payload['room_id'] ?? $this->payload['live_stream_id'] ?? null;

        $channels = [
            new Channel('user.' . $this->userId),
            new PrivateChannel('user.' . $this->userId),
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
        return 'CoHostRequestAccepted';
    }

    /**
     * Broadcast payload.
     */
    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
