<?php

namespace App\Events;

use App\Models\Call;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallRejected implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Call $call, public ?string $reason = 'declined') {}

    /**
     * Broadcast to caller's private channel.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->call->caller_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'call.rejected';
    }

    public function broadcastWith(): array
    {
        return [
            'event'   => 'call.rejected',
            'call_id' => $this->call->id,
            'room_id' => $this->call->room_id,
            'status'  => 'rejected',
            'reason'  => $this->reason,
        ];
    }
}
