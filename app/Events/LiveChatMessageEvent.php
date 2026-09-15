<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveChatMessageEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $roomId;
    public $messageData;

    /**
     * Create a new event instance.
     * Supports ($roomId, $messageData) or array payload ($messageData).
     */
    public function __construct($roomId, $messageData = null)
    {
        if (is_array($roomId) && $messageData === null) {
            $this->messageData = $roomId;
            $this->roomId = (string) ($roomId['room_id'] ?? $roomId['live_stream_id'] ?? $roomId['stream_id'] ?? '1');
        } else {
            $this->roomId = (string) $roomId;
            $this->messageData = is_object($messageData) && method_exists($messageData, 'toArray') 
                ? $messageData->toArray() 
                : (array) $messageData;
        }
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('live-room.' . $this->roomId),
            new PresenceChannel('live-room.' . $this->roomId),
            new Channel('live-stream.' . $this->roomId),
            new PresenceChannel('live-stream.' . $this->roomId),
            new Channel('live.' . $this->roomId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    /**
     * Data payload sent to listeners.
     */
    public function broadcastWith(): array
    {
        $data = $this->messageData;
        $user = $data['user'] ?? null;

        return [
            'id'             => $data['id'] ?? null,
            'room_id'        => (string) $this->roomId,
            'stream_id'      => (string) $this->roomId,
            'user_id'        => $data['user_id'] ?? ($user['id'] ?? null),
            'user_name'      => $data['user_name'] ?? $data['sender_name'] ?? ($user['display_name'] ?? $user['name'] ?? 'User'),
            'user_avatar'    => $data['user_avatar'] ?? $data['sender_avatar'] ?? ($user['avatar_url'] ?? $user['avatar'] ?? null),
            'user'           => $user ?? [
                'id'           => $data['user_id'] ?? null,
                'display_name' => $data['user_name'] ?? $data['sender_name'] ?? 'User',
                'avatar_url'   => $data['user_avatar'] ?? $data['sender_avatar'] ?? null,
            ],
            'message'        => $data['message'] ?? '',
            'type'           => $data['type'] ?? 'text', // 'text' or 'gift'
            'gift_id'        => $data['gift_id'] ?? null,
            'gift_data'      => $data['gift_data'] ?? $data['gift'] ?? null,
            'level'          => $data['level'] ?? ($user['level'] ?? 'Lv1'),
            'timestamp'      => $data['timestamp'] ?? ($data['created_at'] ?? now()->toIso8601String()),
        ];
    }
}
