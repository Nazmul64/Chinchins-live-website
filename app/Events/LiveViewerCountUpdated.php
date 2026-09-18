<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveViewerCountUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $roomId;
    public $payload;

    /**
     * Create a new event instance.
     */
    public function __construct($roomId, array $payload = [])
    {
        $this->roomId = (string) $roomId;
        $this->payload = $payload;
    }

    /**
     * Channels to broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('live-room.' . $this->roomId),
            new PresenceChannel('live-room.' . $this->roomId),
            new Channel('live-stream.' . $this->roomId),
            new PresenceChannel('live-stream.' . $this->roomId),
            new PresenceChannel('presence-live.' . $this->roomId),
            new PresenceChannel('presence-live-stream.' . $this->roomId),
            new Channel('presence-stream.' . $this->roomId),
            new Channel('presence-room.' . $this->roomId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'viewer.updated';
    }

    /**
     * Data payload sent to listeners.
     */
    public function broadcastWith(): array
    {
        return [
            'room_id'      => (string) $this->roomId,
            'stream_id'    => (string) $this->roomId,
            'viewer_count' => (int) ($this->payload['viewer_count'] ?? 1),
            'action'       => $this->payload['action'] ?? 'joined', // 'joined' or 'left'
            'user'         => $this->payload['user'] ?? null,
            'timestamp'    => $this->payload['timestamp'] ?? now()->toIso8601String(),
        ];
    }
}
