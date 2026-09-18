<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallEnded implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public mixed $call;
    public int $endedByUserId;
    public int $targetUserId;
    public int $durationSeconds;
    public string|int $callId;
    public string $channelName;

    public function __construct(mixed $call, int $endedByUserId = 0, int $targetUserId = 0, int $durationSeconds = 0)
    {
        $this->call = $call;
        $this->callId = is_object($call) ? ($call->id ?? 0) : (is_array($call) ? ($call['id'] ?? 0) : $call);
        $this->channelName = is_object($call) ? ($call->channel_name ?? '') : (is_array($call) ? ($call['channel_name'] ?? '') : '');
        
        $callerId = (int) (is_object($call) ? ($call->caller_id ?? 0) : (is_array($call) ? ($call['caller_id'] ?? 0) : 0));
        $receiverId = (int) (is_object($call) ? ($call->receiver_id ?? 0) : (is_array($call) ? ($call['receiver_id'] ?? 0) : 0));

        $this->endedByUserId = $endedByUserId ?: $callerId;
        $this->targetUserId = $targetUserId ?: (($this->endedByUserId === $callerId) ? $receiverId : $callerId);
        $this->durationSeconds = $durationSeconds ?: (int) (is_object($call) ? ($call->duration_seconds ?? 0) : 0);
    }

    /**
     * Broadcast to both call participants and call session channels.
     */
    public function broadcastOn(): array
    {
        $channels = [];

        if ($this->targetUserId) {
            $channels[] = new Channel('user.' . $this->targetUserId);
            $channels[] = new PrivateChannel('user.' . $this->targetUserId);
            $channels[] = new Channel('chat.' . $this->targetUserId);
        }

        if ($this->endedByUserId) {
            $channels[] = new Channel('user.' . $this->endedByUserId);
            $channels[] = new PrivateChannel('user.' . $this->endedByUserId);
            $channels[] = new Channel('chat.' . $this->endedByUserId);
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
        return 'call.ended';
    }

    public function broadcastWith(): array
    {
        return [
            'event'            => 'call.ended',
            'action'           => 'call_ended',
            'call_id'          => $this->callId,
            'id'               => $this->callId,
            'room_id'          => (string) $this->callId,
            'channel_name'     => $this->channelName,
            'status'           => 'ended',
            'ended_by'         => $this->endedByUserId,
            'duration_seconds' => $this->durationSeconds,
            'timestamp'        => now()->toIso8601String(),
        ];
    }
}
