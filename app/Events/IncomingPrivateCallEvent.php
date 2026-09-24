<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IncomingPrivateCallEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int|string $hostId;
    public array $callData;

    /**
     * Create a new event instance.
     */
    public function __construct(int|string $hostId, array $callData)
    {
        $this->hostId = $hostId;
        $this->callData = $callData;
    }

    /**
     * Broadcast to host's private channels.
     * Keeps private call dialog strictly between caller and host without alerting other live viewers.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->hostId),
            new Channel('user.' . $this->hostId),
        ];
    }

    /**
     * Event name for Flutter / Web clients.
     */
    public function broadcastAs(): string
    {
        return 'private_call.incoming';
    }

    /**
     * Data payload for host incoming call dialog.
     */
    public function broadcastWith(): array
    {
        return array_merge([
            'event'     => 'private_call.incoming',
            'timestamp' => now()->toIso8601String(),
        ], $this->callData);
    }
}
