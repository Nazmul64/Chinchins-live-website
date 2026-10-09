<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IncomingCallEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int|string $targetUserId;
    public array $callData;

    /**
     * Create a new event instance.
     */
    public function __construct(int|string $targetUserId, array $callData)
    {
        $this->targetUserId = $targetUserId;
        $this->callData = $callData;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('user.' . $this->targetUserId),
            new Channel('user.' . $this->targetUserId),
            new Channel('call.user.' . $this->targetUserId),
        ];

        if (!empty($this->callData['channel'])) {
            $channels[] = new Channel($this->callData['channel']);
        }
        if (!empty($this->callData['channel_name'])) {
            $channels[] = new Channel($this->callData['channel_name']);
        }

        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'incoming-call';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return array_merge([
            'event'        => 'incoming-call',
            'action'       => 'incoming-call',
            'caller_id'    => $this->callData['caller_id'] ?? null,
            'call_id'      => $this->callData['call_id'] ?? $this->callData['id'] ?? null,
            'channel_name' => $this->callData['channel_name'] ?? $this->callData['room_name'] ?? $this->callData['channel'] ?? null,
            'call_type'    => $this->callData['call_type'] ?? 'video',
            'timestamp'    => now()->toIso8601String(),
        ], $this->callData);
    }
}
