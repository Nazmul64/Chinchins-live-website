<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StreamStatusChangedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $streamId;
    public $status;
    public $stream;

    /**
     * Create a new event instance.
     *
     * @param int|string $streamId
     * @param string $status 'live' | 'ended'
     * @param array $stream
     */
    public function __construct($streamId, string $status, array $stream = [])
    {
        $this->streamId = $streamId;
        $this->status   = $status;
        $this->stream   = $stream;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('stream-lobby'),
            new Channel('live-feed'),
            new Channel('lives'),
            new Channel('presence-stream-lobby'),
            new Channel('presence-live-lobby'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'StreamStatusChanged';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'stream_id'     => $this->streamId,
            'room_id'       => (string) $this->streamId,
            'status'        => $this->status,
            'action'        => $this->status === 'live' ? 'started' : 'ended',
            'stream'        => $this->stream,
            'timestamp'     => now()->toIso8601String(),
        ];
    }
}
