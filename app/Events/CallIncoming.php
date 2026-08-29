<?php

namespace App\Events;

use App\Models\Call;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallIncoming implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Call $call)
    {
        // Eager load caller if not already loaded
        if (!$this->call->relationLoaded('caller')) {
            $this->call->load('caller');
        }
    }

    /**
     * Broadcast to receiver's private channel.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->call->receiver_id),
        ];
    }

    /**
     * Event name broadcast to the frontend.
     */
    public function broadcastAs(): string
    {
        return 'call.incoming';
    }

    /**
     * Data payload for Flutter client incoming call screen.
     */
    public function broadcastWith(): array
    {
        return [
            'event'         => 'call.incoming',
            'call_id'       => $this->call->id,
            'caller_id'     => $this->call->caller_id,
            'caller_name'   => $this->call->caller?->name ?? $this->call->caller?->display_name ?? 'User',
            'caller_avatar' => $this->call->caller?->avatar_url ?? $this->call->caller?->avatar ?? null,
            'receiver_id'   => $this->call->receiver_id,
            'call_type'     => $this->call->call_type,
            'room_id'       => $this->call->room_id,
            'status'        => $this->call->status,
            'created_at'    => $this->call->created_at?->toIso8601String(),
        ];
    }
}
