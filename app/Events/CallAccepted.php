<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallAccepted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public mixed $call;
    public int $callId;
    public int $callerId;
    public int $receiverId;
    public string $roomId;
    public ?string $receiverName;

    public ?array $extraData;

    public function __construct(mixed $call, ?array $extraData = null)
    {
        $this->call = $call;
        $this->extraData = $extraData;
        $this->callId = (int) (is_object($call) ? ($call->id ?? 0) : (is_array($call) ? ($call['id'] ?? 0) : $call));
        $this->callerId = (int) (is_object($call) ? ($call->caller_id ?? 0) : (is_array($call) ? ($call['caller_id'] ?? 0) : 0));
        $this->receiverId = (int) (is_object($call) ? ($call->receiver_id ?? 0) : (is_array($call) ? ($call['receiver_id'] ?? 0) : 0));
        $this->roomId = (string) (is_object($call) ? ($call->room_id ?? $call->channel_name ?? '') : (is_array($call) ? ($call['room_id'] ?? $call['channel_name'] ?? '') : ''));

        $receiver = null;
        if (is_object($call) && method_exists($call, 'relationLoaded') && $call->relationLoaded('receiver') && $call->receiver) {
            $receiver = $call->receiver;
        } elseif ($this->receiverId > 0) {
            $receiver = User::find($this->receiverId);
        }

        $this->receiverName = $receiver ? ($receiver->display_name ?: $receiver->name) : 'User';
    }

    /**
     * Broadcast to caller's private channel, call.caller_id, public fallback channel, and call session channels.
     */
    public function broadcastOn(): array
    {
        $channels = [];
        if ($this->callerId > 0) {
            $channels[] = new PrivateChannel('call.' . $this->callerId);
            $channels[] = new Channel('call.' . $this->callerId);
            $channels[] = new PrivateChannel('user.' . $this->callerId);
            $channels[] = new Channel('user.' . $this->callerId);
            $channels[] = new Channel('chat.' . $this->callerId);
        }
        if (!empty($this->roomId)) {
            $channels[] = new PrivateChannel('call.' . $this->roomId);
            $channels[] = new Channel('call.' . $this->roomId);
            $channels[] = new Channel('presence-call.' . $this->roomId);
        }
        if ($this->callId > 0) {
            $channels[] = new PrivateChannel('call.' . $this->callId);
            $channels[] = new Channel('call.' . $this->callId);
            $channels[] = new Channel('presence-call.' . $this->callId);
        }
        return $channels;
    }

    /**
     * Event name for Flutter caller to start WebRTC offer negotiation / LiveKit connection.
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
        $base = [
            'event'         => 'call.accepted',
            'action'        => 'call_accepted',
            'call_id'       => $this->callId,
            'id'            => $this->callId,
            'room_id'       => $this->roomId,
            'channel_name'  => $this->roomId,
            'caller_id'     => $this->callerId,
            'receiver_id'   => $this->receiverId,
            'receiver_name' => $this->receiverName,
            'status'        => 'connected',
            'call_status'   => 'connected',
            'started_at'    => now()->toIso8601String(),
            'answered_at'   => now()->toIso8601String(),
            'timestamp'     => now()->toIso8601String(),
        ];

        if ($this->extraData && is_array($this->extraData)) {
            $base = array_merge($base, $this->extraData);
        }

        return $base;
    }
}

