<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StreamHoldEvent implements ShouldBroadcastNow
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
        return 'StreamHoldEvent';
    }

    public function broadcastWith(): array
    {
        return array_merge([
            'event'          => 'StreamHoldEvent',
            'stream_id'      => (string) $this->streamId,
            'room_id'        => (string) $this->streamId,
            'status'         => 'paused',
            'is_paused'      => true,
            'message'        => 'I will come back soon',
            'timestamp'      => now()->toIso8601String(),
        ], $this->payload);
    }
}
