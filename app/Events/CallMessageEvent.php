<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallMessageEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $callId;
    public $messageData;

    /**
     * Create a new event instance.
     */
    public function __construct($callId, array $messageData)
    {
        $this->callId = (string) $callId;
        $this->messageData = $messageData;
    }

    /**
     * Get the channels the event should broadcast on.
     * Supports both PrivateChannel ('private-call.{callId}') and Channel ('call.{callId}') for full client compatibility.
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('call.' . $this->callId),
            new Channel('call.' . $this->callId),
            new PrivateChannel('call_chat.' . $this->callId),
            new Channel('call_chat.' . $this->callId),
        ];

        if (!empty($this->messageData['channel_name']) && $this->messageData['channel_name'] !== $this->callId) {
            $channels[] = new PrivateChannel('call.' . $this->messageData['channel_name']);
            $channels[] = new Channel('call.' . $this->messageData['channel_name']);
        }

        if (!empty($this->messageData['receiver_id'])) {
            $channels[] = new PrivateChannel('user.' . $this->messageData['receiver_id']);
            $channels[] = new Channel('user.' . $this->messageData['receiver_id']);
            $channels[] = new PrivateChannel('chat.' . $this->messageData['receiver_id']);
            $channels[] = new Channel('chat.' . $this->messageData['receiver_id']);
        }

        if (!empty($this->messageData['sender_id'])) {
            $channels[] = new PrivateChannel('user.' . $this->messageData['sender_id']);
            $channels[] = new Channel('user.' . $this->messageData['sender_id']);
            $channels[] = new Channel('chat.' . $this->messageData['sender_id']);
        }

        return $channels;
    }

    /**
     * Broadcast event alias for Flutter Laravel Echo.
     */
    public function broadcastAs(): string
    {
        return 'CallMessageEvent';
    }

    /**
     * Data payload sent to Flutter clients.
     */
    public function broadcastWith(): array
    {
        return [
            'id'          => $this->messageData['id'] ?? null,
            'call_id'     => $this->callId,
            'sender_id'   => $this->messageData['sender_id'] ?? null,
            'sender_name' => $this->messageData['sender_name'] ?? 'User',
            'sender_avatar' => $this->messageData['sender_avatar'] ?? null,
            'receiver_id' => $this->messageData['receiver_id'] ?? null,
            'message'     => $this->messageData['message'] ?? '',
            'media_url'   => $this->messageData['media_url'] ?? $this->messageData['image_url'] ?? null,
            'image_url'   => $this->messageData['image_url'] ?? $this->messageData['media_url'] ?? null,
            'type'        => $this->messageData['type'] ?? 'text',
            'timestamp'   => $this->messageData['timestamp'] ?? $this->messageData['created_at'] ?? now()->toIso8601String(),
            'created_at'  => $this->messageData['created_at'] ?? now()->toIso8601String(),
        ];
    }
}
