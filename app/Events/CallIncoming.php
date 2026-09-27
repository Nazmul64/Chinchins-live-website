<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallIncoming implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public mixed $call;
    public int $callId;
    public int $callerId;
    public int $receiverId;
    public string $callType;
    public string $roomId;
    public string $status;
    public ?string $callerName;
    public ?string $callerAvatar;

    public function __construct(mixed $call, int|string|null $callerId = null, int|string|null $receiverId = null)
    {
        $this->call = $call;

        $this->callId = (int) (is_object($call) ? ($call->id ?? 0) : (is_array($call) ? ($call['id'] ?? $call['call_id'] ?? 0) : $call));
        $this->callerId = (int) ($callerId ?: (is_object($call) ? ($call->caller_id ?? 0) : (is_array($call) ? ($call['caller_id'] ?? 0) : 0)));
        $this->receiverId = (int) ($receiverId ?: (is_object($call) ? ($call->receiver_id ?? 0) : (is_array($call) ? ($call['receiver_id'] ?? 0) : 0)));
        $this->callType = (string) (is_object($call) ? ($call->call_type ?? 'video') : (is_array($call) ? ($call['call_type'] ?? 'video') : 'video'));
        $this->roomId = (string) (is_object($call) ? ($call->room_id ?? $call->channel_name ?? '') : (is_array($call) ? ($call['room_id'] ?? $call['channel_name'] ?? '') : ''));
        $this->status = (string) (is_object($call) ? ($call->status ?? 'ringing') : (is_array($call) ? ($call['status'] ?? 'ringing') : 'ringing'));

        // Load caller info
        $caller = null;
        if (is_object($call) && method_exists($call, 'relationLoaded') && $call->relationLoaded('caller') && $call->caller) {
            $caller = $call->caller;
        } elseif ($this->callerId > 0) {
            $caller = User::find($this->callerId);
        }

        $this->callerName = $caller ? ($caller->display_name ?: $caller->name) : 'User';
        $this->callerAvatar = $caller ? ($caller->avatar_url ?: $caller->avatar) : null;
    }

    /**
     * Broadcast to receiver's private channel and public fallback channel.
     */
    public function broadcastOn(): array
    {
        $channels = [];
        if ($this->receiverId > 0) {
            $channels[] = new PrivateChannel('user.' . $this->receiverId);
            $channels[] = new PrivateChannel('private-user.' . $this->receiverId);
            $channels[] = new Channel('user.' . $this->receiverId);
        }
        return $channels;
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
            'action'        => 'incoming_call',
            'call_id'       => $this->callId,
            'id'            => $this->callId,
            'caller_id'     => $this->callerId,
            'caller_name'   => $this->callerName,
            'caller_avatar' => $this->callerAvatar,
            'caller'        => [
                'id'           => $this->callerId,
                'name'         => $this->callerName,
                'display_name' => $this->callerName,
                'avatar_url'   => $this->callerAvatar,
            ],
            'receiver_id'   => $this->receiverId,
            'call_type'     => $this->callType,
            'room_id'       => $this->roomId,
            'channel_name'  => $this->roomId,
            'status'        => $this->status,
            'created_at'    => now()->toIso8601String(),
            'timestamp'     => now()->toIso8601String(),
        ];
    }
}

