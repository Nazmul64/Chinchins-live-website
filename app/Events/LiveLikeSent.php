<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveLikeSent implements ShouldBroadcastNow
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
        return 'live.like';
    }

    /**
     * Data payload sent to listeners.
     */
    public function broadcastWith(): array
    {
        return [
            'room_id'       => (string) $this->roomId,
            'stream_id'     => (string) $this->roomId,
            'likes_count'   => (int) ($this->payload['likes_count'] ?? 1),
            'total_likes'   => (int) ($this->payload['total_likes'] ?? $this->payload['likes_count'] ?? 1),
            'count'         => (int) ($this->payload['count'] ?? 1),
            'sender_id'     => (int) ($this->payload['sender_id'] ?? $this->payload['user_id'] ?? 0),
            'sender_name'   => $this->payload['sender_name'] ?? ($this->payload['user']['display_name'] ?? 'Viewer'),
            'sender_avatar' => $this->payload['sender_avatar'] ?? ($this->payload['user']['avatar_url'] ?? null),
            'user'          => $this->payload['user'] ?? null,
            'timestamp'     => $this->payload['timestamp'] ?? now()->toIso8601String(),
        ];
    }
}
