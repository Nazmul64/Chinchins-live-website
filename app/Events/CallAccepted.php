<?php

namespace App\Events;

use App\Models\Call;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallAccepted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Call $call)
    {
        if (!$this->call->relationLoaded('receiver')) {
            $this->call->load('receiver');
        }
    }

    /**
     * Broadcast to caller's private channel.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->call->caller_id),
        ];
    }

    /**
     * Event name for Flutter caller to start WebRTC offer negotiation.
     */
    public function broadcastAs(): string
    {
        return 'call.accepted';
    }

    /**
     * Event payload.
     */
    public function broadcastWith(): array
    {
        return [
            'event'         => 'call.accepted',
            'call_id'       => $this->call->id,
            'room_id'       => $this->call->room_id,
            'caller_id'     => $this->call->caller_id,
            'receiver_id'   => $this->call->receiver_id,
            'receiver_name' => $this->call->receiver?->name ?? $this->call->receiver?->display_name ?? 'User',
            'status'        => 'accepted',
            'answered_at'   => $this->call->answered_at?->toIso8601String(),
        ];
    }
}
