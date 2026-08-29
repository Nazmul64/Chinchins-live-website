<?php

namespace App\Events;

use App\Models\Call;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallCancelled implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Call $call) {}

    /**
     * Broadcast to receiver's private channel so ringing stops immediately.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->call->receiver_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'call.cancelled';
    }

    public function broadcastWith(): array
    {
        return [
            'event'   => 'call.cancelled',
            'call_id' => $this->call->id,
            'room_id' => $this->call->room_id,
            'status'  => 'cancelled',
        ];
    }
}
