<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallAcceptedEvent implements ShouldBroadcastNow
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
     * Broadcast to caller's private channel (call.caller_id, user.caller_id) and session channels.
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('call.' . $this->callerId),
            new Channel('call.' . $this->callerId),
            new PrivateChannel('user.' . $this->callerId),
            new Channel('user.' . $this->callerId),
            new Channel('chat.' . $this->callerId),
        ];

        $callId = $this->callData['call_id'] ?? $this->callData['id'] ?? null;
        if ($callId) {
            $channels[] = new PrivateChannel('call.' . $callId);
            $channels[] = new Channel('call.' . $callId);
            $channels[] = new Channel('presence-call.' . $callId);
        }

        $roomId = $this->callData['channel_name'] ?? $this->callData['room_id'] ?? null;
        if ($roomId && $roomId !== (string)$callId) {
            $channels[] = new PrivateChannel('call.' . $roomId);
            $channels[] = new Channel('call.' . $roomId);
            $channels[] = new Channel('presence-call.' . $roomId);
        }

        return $channels;
    }

    /**
     * Event name for Flutter / Web clients to instantly switch to Connected.
     */
    public function broadcastAs(): string
    {
        return 'CallAcceptedEvent';
    }

    /**
     * Data payload including LiveKit tokens.
     */
    public function broadcastWith(): array
    {
        return array_merge([
            'event'         => 'call.accepted',
            'action'        => 'call_accepted',
            'status'        => 'connected',
            'call_status'   => 'connected',
            'answered_at'   => now()->toIso8601String(),
            'timestamp'     => now()->toIso8601String(),
        ], $this->callData);
    }
}
