<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AudioMuteEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $roomId;
    public $payload;

    /**
     * Create a new event instance.
     * Supports ($payload) or ($roomId, $payload)
     */
    public function __construct($roomId, $payload = null)
    {
        if (is_array($roomId) && $payload === null) {
            $this->payload = $roomId;
            $this->roomId = (string) ($roomId['room_id'] ?? $roomId['live_stream_id'] ?? $roomId['stream_id'] ?? '1');
        } else {
            $this->roomId = (string) $roomId;
            $this->payload = is_object($payload) && method_exists($payload, 'toArray') 
                ? $payload->toArray() 
                : (array) $payload;
        }
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('live-room.' . $this->roomId),
            new PresenceChannel('live-room.' . $this->roomId),
            new Channel('live-stream.' . $this->roomId),
            new PresenceChannel('live-stream.' . $this->roomId),
            new Channel('live.' . $this->roomId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'audio.mute.toggled';
    }

    public function broadcastWith(): array
    {
        return [
            'room_id'        => (string) $this->roomId,
            'stream_id'      => (string) $this->roomId,
            'target_user_id' => (int) ($this->payload['target_user_id'] ?? $this->payload['user_id'] ?? 0),
            'user_id'        => (int) ($this->payload['target_user_id'] ?? $this->payload['user_id'] ?? 0),
            'is_muted'       => (bool) ($this->payload['is_muted'] ?? false),
            'muted_by_host'  => (bool) ($this->payload['muted_by_host'] ?? false),
            'timestamp'      => now()->toIso8601String(),
        ];
    }
}
