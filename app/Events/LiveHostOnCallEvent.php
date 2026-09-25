<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveHostOnCallEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $roomId;
    public array $data;

    /**
     * Create a new event instance.
     */
    public function __construct(string $roomId, array $data)
    {
        $this->roomId = (string) $roomId;
        $this->data = $data;
    }

    /**
     * Broadcast on active Live Stream Channels.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('live-room.' . $this->roomId),
            new PresenceChannel('presence-live-stream.' . $this->roomId),
            new Channel('live-stream.' . $this->roomId),
            new Channel('live.' . $this->roomId),
        ];
    }

    /**
     * Broadcast Event Name.
     */
    public function broadcastAs(): string
    {
        return 'LiveHostOnCallEvent';
    }

    /**
     * Broadcast Data.
     */
    public function broadcastWith(): array
    {
        return $this->data;
    }
}
