<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallRejected implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public mixed $call;
    public ?string $reason;
    public int $callerId;
    public int $receiverId;
    public string $channelName;
    public string|int $callId;

    public function __construct(mixed $call, ?string $reason = 'declined')
    {
        $this->call = $call;
        $this->reason = $reason ?: 'declined';
        $this->callId = is_object($call) ? ($call->id ?? 0) : (is_array($call) ? ($call['id'] ?? 0) : $call);
        $this->channelName = is_object($call) ? ($call->channel_name ?? '') : (is_array($call) ? ($call['channel_name'] ?? '') : '');
        $this->callerId = (int) (is_object($call) ? ($call->caller_id ?? 0) : (is_array($call) ? ($call['caller_id'] ?? 0) : 0));
        $this->receiverId = (int) (is_object($call) ? ($call->receiver_id ?? 0) : (is_array($call) ? ($call['receiver_id'] ?? 0) : 0));
    }

    /**
     * Broadcast to caller, receiver, and call session channels.
     */
    public function broadcastOn(): array
    {
        $channels = [];

        if ($this->callerId) {
            $channels[] = new Channel('user.' . $this->callerId);
            $channels[] = new PrivateChannel('user.' . $this->callerId);
            $channels[] = new Channel('chat.' . $this->callerId);
        }

        if ($this->receiverId) {
            $channels[] = new Channel('user.' . $this->receiverId);
            $channels[] = new PrivateChannel('user.' . $this->receiverId);
            $channels[] = new Channel('chat.' . $this->receiverId);
        }

        if ($this->callId) {
            $channels[] = new Channel('call.' . $this->callId);
            $channels[] = new PrivateChannel('call.' . $this->callId);
            $channels[] = new Channel('presence-call.' . $this->callId);
        }

        if ($this->channelName) {
            $channels[] = new Channel('call.' . $this->channelName);
            $channels[] = new PrivateChannel('call.' . $this->channelName);
            $channels[] = new Channel('presence-call.' . $this->channelName);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'call.rejected';
    }

    public function broadcastWith(): array
    {
        return [
            'event'        => 'call.rejected',
            'action'       => 'call_rejected',
            'call_id'      => $this->callId,
            'id'           => $this->callId,
            'room_id'      => (string) $this->callId,
            'channel_name' => $this->channelName,
            'caller_id'    => $this->callerId,
            'receiver_id'  => $this->receiverId,
            'status'       => 'rejected',
            'reason'       => $this->reason,
            'timestamp'    => now()->toIso8601String(),
        ];
    }
}
