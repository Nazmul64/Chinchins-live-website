<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveChatMessageEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $messageData;

    public function __construct(array $messageData)
    {
        $this->messageData = $messageData;
    }

    /**
     * Broadcast on live-stream channel.
     */
    public function broadcastOn()
    {
        $streamId = (string) ($this->messageData['stream_id'] ?? $this->messageData['live_stream_id'] ?? '1');
        return [
            new \Illuminate\Broadcasting\PresenceChannel('live-stream.' . $streamId),
            new \Illuminate\Broadcasting\PresenceChannel('live-room.' . $streamId),
            new Channel('live-stream.' . $streamId),
            new Channel('live-room.' . $streamId),
            new Channel('live.' . $streamId),
        ];
    }

    /**
     * Broadcast event name for Flutter / Web clients.
     */
    public function broadcastAs()
    {
        return 'LiveChatMessageEvent';
    }

    /**
     * Data payload.
     */
    public function broadcastWith()
    {
        return [
            'id'          => $this->messageData['id'] ?? null,
            'stream_id'   => $this->messageData['stream_id'] ?? $this->messageData['live_stream_id'] ?? '1',
            'user_id'     => $this->messageData['user_id'] ?? null,
            'user_name'   => $this->messageData['user_name'] ?? $this->messageData['sender_name'] ?? 'User',
            'user_avatar' => $this->messageData['user_avatar'] ?? $this->messageData['sender_avatar'] ?? null,
            'message'     => $this->messageData['message'] ?? '',
            'type'        => $this->messageData['type'] ?? 'text',
            'level'       => $this->messageData['level'] ?? 'Lv1',
            'timestamp'   => $this->messageData['timestamp'] ?? now()->toIso8601String(),
        ];
    }
}
