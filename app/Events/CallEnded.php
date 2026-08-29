<?php

namespace App\Events;

use App\Models\Call;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallEnded implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Call $call,
        public int $endedByUserId,
        public int $targetUserId
    ) {}

    /**
     * Broadcast to the other call participant's private channel.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->targetUserId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'call.ended';
    }

    public function broadcastWith(): array
    {
        return [
            'event'       => 'call.ended',
            'call_id'     => $this->call->id,
            'room_id'     => $this->call->room_id,
            'status'      => 'ended',
            'ended_by'    => $this->endedByUserId,
            'ended_at'    => $this->call->ended_at?->toIso8601String(),
        ];
    }
}
