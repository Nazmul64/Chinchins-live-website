<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallEndedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public mixed $call;
    public int $endedByUserId;
    public int $targetUserId;
    public int $callerId;
    public int $receiverId;
    public int $durationSeconds;
    public string|int $callId;
    public string $channelName;

    /**
     * Create a new event instance.
     */
    public function __construct(mixed $call, int $endedByUserId = 0, int $targetUserId = 0, int $durationSeconds = 0)
    {
        $this->call = $call;
        $this->callId = is_object($call) ? ($call->id ?? 0) : (is_array($call) ? ($call['id'] ?? 0) : $call);
        $this->channelName = is_object($call) ? ($call->channel_name ?? '') : (is_array($call) ? ($call['channel_name'] ?? '') : '');
        
        $this->callerId = (int) (is_object($call) ? ($call->caller_id ?? 0) : (is_array($call) ? ($call['caller_id'] ?? 0) : 0));
        $this->receiverId = (int) (is_object($call) ? ($call->receiver_id ?? 0) : (is_array($call) ? ($call['receiver_id'] ?? 0) : 0));

        $this->endedByUserId = $endedByUserId ?: $this->callerId;
        $this->targetUserId = $targetUserId ?: (($this->endedByUserId === $this->callerId) ? $this->receiverId : $this->callerId);
        $this->durationSeconds = $durationSeconds ?: (int) (is_object($call) ? ($call->duration_seconds ?? 0) : 0);
    }

    /**
     * Broadcast to both caller, receiver and call session channels.
     */
    public function broadcastOn(): array
    {
        $channels = [];

        // Broadcast to target and ended_by user private & public channels
        if ($this->targetUserId) {
            $channels[] = new PrivateChannel('user.' . $this->targetUserId);
            $channels[] = new Channel('user.' . $this->targetUserId);
            $channels[] = new PrivateChannel('chat.' . $this->targetUserId);
            $channels[] = new Channel('chat.' . $this->targetUserId);
        }

        if ($this->endedByUserId) {
            $channels[] = new PrivateChannel('user.' . $this->endedByUserId);
            $channels[] = new Channel('user.' . $this->endedByUserId);
            $channels[] = new PrivateChannel('chat.' . $this->endedByUserId);
            $channels[] = new Channel('chat.' . $this->endedByUserId);
        }

        if ($this->callerId && $this->callerId !== $this->targetUserId && $this->callerId !== $this->endedByUserId) {
            $channels[] = new PrivateChannel('user.' . $this->callerId);
            $channels[] = new Channel('user.' . $this->callerId);
        }

        if ($this->receiverId && $this->receiverId !== $this->targetUserId && $this->receiverId !== $this->endedByUserId) {
            $channels[] = new PrivateChannel('user.' . $this->receiverId);
            $channels[] = new Channel('user.' . $this->receiverId);
        }

        if ($this->callId) {
            $channels[] = new PrivateChannel('call.' . $this->callId);
            $channels[] = new Channel('call.' . $this->callId);
            $channels[] = new Channel('presence-call.' . $this->callId);
        }

        if ($this->channelName) {
            $channels[] = new PrivateChannel('call.' . $this->channelName);
            $channels[] = new Channel('call.' . $this->channelName);
            $channels[] = new Channel('presence-call.' . $this->channelName);
        }

        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'CallEndedEvent';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'event'            => 'CallEndedEvent',
            'action'           => 'call_ended',
            'call_id'          => $this->callId,
            'id'               => $this->callId,
            'room_id'          => (string) $this->callId,
            'channel_name'     => $this->channelName,
            'status'           => 'completed',
            'call_status'      => 'completed',
            'ended_by'         => $this->endedByUserId,
            'duration_seconds' => $this->durationSeconds,
            'timestamp'        => now()->toIso8601String(),
        ];
    }
}
