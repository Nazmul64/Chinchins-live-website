<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StreamSignalingEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $streamId;
    public $signalData;

    public function __construct($streamId, array $signalData)
    {
        $this->streamId = (string) $streamId;
        $this->signalData = $signalData;
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
        return 'StreamSignalingEvent';
    }

    public function broadcastWith(): array
    {
        return [
            'stream_id'      => $this->streamId,
            'sender_id'      => $this->signalData['sender_id'] ?? null,
            'target_user_id' => $this->signalData['target_user_id'] ?? null,
            'type'           => $this->signalData['type'] ?? 'offer',
            'sdp_or_candidate' => $this->signalData['sdp_or_candidate'] ?? $this->signalData['payload'] ?? null,
            'payload'        => $this->signalData['payload'] ?? $this->signalData['sdp_or_candidate'] ?? null,
            'timestamp'      => $this->signalData['timestamp'] ?? now()->toIso8601String(),
        ];
    }
}
