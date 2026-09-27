<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveStreamEndedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $streamId;
    public $summary;

    public function __construct($streamId, array $summary = [])
    {
        $this->streamId = (string) $streamId;
        $this->summary = array_merge([
            'room_id'   => (string) $streamId,
            'stream_id' => (string) $streamId,
            'status'    => 'ended',
            'is_live'   => false,
            'ended_at'  => now()->toISOString(),
        ], $summary);
    }

    public function broadcastOn()
    {
        return [
            new Channel('global-live-feed'),
            new Channel('live-stream'),
            new Channel('live.' . $this->streamId),
            new PresenceChannel('presence-live.' . $this->streamId),
        ];
    }

    public function broadcastAs()
    {
        return 'LiveStreamEndedEvent';
    }

    public function broadcastWith()
    {
        return $this->summary;
    }
}
