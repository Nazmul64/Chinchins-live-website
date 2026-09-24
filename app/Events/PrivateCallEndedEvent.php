<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PrivateCallEndedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int|string $callerId;
    public int|string $hostId;
    public array $callData;

    /**
     * Create a new event instance.
     */
    public function __construct(int|string $callerId, int|string $hostId, array $callData)
    {
        $this->callerId = $callerId;
        $this->hostId = $hostId;
        $this->callData = $callData;
    }

    /**
     * Broadcast to both caller and host private channels.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->callerId),
            new Channel('user.' . $this->callerId),
            new PrivateChannel('user.' . $this->hostId),
            new Channel('user.' . $this->hostId),
        ];
    }

    /**
     * Event name for Flutter / Web clients.
     */
    public function broadcastAs(): string
    {
        return 'private_call.ended';
    }

    /**
     * Data payload.
     */
    public function broadcastWith(): array
    {
        return array_merge([
            'event'     => 'private_call.ended',
            'timestamp' => now()->toIso8601String(),
        ], $this->callData);
    }
}
