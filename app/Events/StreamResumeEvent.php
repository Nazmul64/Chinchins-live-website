<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StreamResumeEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $streamId;
    public array $payload;

    /**
     * Create a new event instance.
     *
     * @param int|string $streamId
     * @param array $payload
     */
    public function __construct($streamId, array $payload = [])
    {
        $this->streamId = $streamId;
        $this->payload  = $payload;
    }

    /**
     * Broadcast to all live stream presence and public channels.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('live-stream.' . $this->streamId),
            new Channel('presence-stream.' . $this->streamId),
            new Channel('live-room.' . $this->streamId),
            new Channel('live.' . $this->streamId),
            new Channel('presence-live-stream.' . $this->streamId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'StreamResumeEvent';
    }

    public function broadcastWith(): array
    {
        return array_merge([
            'event'     => 'StreamResumeEvent',
            'stream_id' => (string) $this->streamId,
            'room_id'   => (string) $this->streamId,
            'status'    => 'live',
            'is_paused' => false,
            'message'   => 'Host is back live',
            'timestamp' => now()->toIso8601String(),
        ], $this->payload);
    }
}
