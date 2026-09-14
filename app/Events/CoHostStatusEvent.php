<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CoHostStatusEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $streamId;
    public $statusData;

    public function __construct($streamId, array $statusData)
    {
        $this->streamId = (string) $streamId;
        $this->statusData = $statusData;
    }

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('live-stream.' . $this->streamId),
            new PresenceChannel('live-room.' . $this->streamId),
            new Channel('live-stream.' . $this->streamId),
            new Channel('live.' . $this->streamId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'CoHostStatusEvent';
    }

    public function broadcastWith(): array
    {
        return [
            'stream_id'     => $this->streamId,
            'action'        => $this->statusData['action'] ?? 'invited',
            'user_id'       => $this->statusData['user_id'] ?? null,
            'user_name'     => $this->statusData['user_name'] ?? null,
            'user_avatar'   => $this->statusData['user_avatar'] ?? null,
            'co_hosts_count' => $this->statusData['co_hosts_count'] ?? 1,
            'max_limit'     => $this->statusData['max_limit'] ?? 5,
            'timestamp'     => $this->statusData['timestamp'] ?? now()->toIso8601String(),
        ];
    }
}
