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
            new Channel('user.' . $this->targetUserId),
            new Channel('call.user.' . $this->targetUserId),
            new PrivateChannel('user.' . $this->targetUserId),
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
        return 'incoming_call';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return array_merge([
            'event'     => 'incoming_call',
            'timestamp' => now()->toIso8601String(),
        ], $this->callData);
    }
}
