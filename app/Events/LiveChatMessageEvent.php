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
        $streamId = $this->messageData['stream_id'] ?? $this->messageData['live_stream_id'] ?? '1';
        return [
            new Channel('live-stream.' . $streamId),
            new Channel('presence-live.' . $streamId),
        ];
    }

    /**
     * Broadcast event name for Flutter / Web clients.
     */
    public function broadcastAs()
    {
        return 'chat.message';
    }

    /**
     * Data payload.
     */
    public function broadcastWith()
    {
        return $this->messageData;
    }
}
