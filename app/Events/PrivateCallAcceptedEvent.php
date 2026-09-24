<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PrivateCallAcceptedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int|string $callerId;
    public array $callData;

    /**
     * Create a new event instance.
     */
    public function __construct(int|string $callerId, array $callData)
    {
        $this->callerId = $callerId;
        $this->callData = $callData;
    }

    /**
     * Broadcast to caller's private channel.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->callerId),
            new Channel('user.' . $this->callerId),
        ];
    }

    /**
     * Event name for Flutter / Web clients.
     */
    public function broadcastAs(): string
    {
        return 'private_call.accepted';
    }

    /**
     * Data payload.
     */
    public function broadcastWith(): array
    {
        return array_merge([
            'event'     => 'private_call.accepted',
            'timestamp' => now()->toIso8601String(),
        ], $this->callData);
    }
}
