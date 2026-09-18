<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GiftSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $channelId;
    public $payload;

    /**
     * Create a new event instance.
     *
     * @param string|int $channelId Call session ID, stream ID, or user ID
     * @param array $payload Detailed gift and animation metadata
     */
    public function __construct($channelId, array $payload)
    {
        $this->channelId = (string) $channelId;
        $this->payload = $payload;
    }

    /**
     * Broadcast on call/stream channel.
     */
    public function broadcastOn()
    {
        $channels = [
            new Channel('call.' . $this->channelId),
            new PrivateChannel('call.' . $this->channelId),
            new Channel('presence-call.' . $this->channelId),
            new Channel('call_chat.' . $this->channelId),
            new Channel('live-room.' . $this->channelId),
            new PrivateChannel('live-room.' . $this->channelId),
            new Channel('live-stream.' . $this->channelId),
            new Channel('presence-live-stream.' . $this->channelId),
            new Channel('presence-live.' . $this->channelId),
            new Channel('live.' . $this->channelId),
        ];

        $recvId = $this->payload['receiver_id'] ?? ($this->payload['receiver']['id'] ?? null);
        if ($recvId) {
            $channels[] = new Channel('chat.' . $recvId);
            $channels[] = new PrivateChannel('chat.' . $recvId);
            $channels[] = new Channel('user-chat.' . $recvId);
            $channels[] = new Channel('user.' . $recvId);
            $channels[] = new PrivateChannel('user.' . $recvId);
        }

        return $channels;
    }

    /**
     * Broadcast event name for Flutter / Web clients.
     */
    public function broadcastAs()
    {
        return 'GiftSent';
    }

    /**
     * Broadcast payload data.
     */
    public function broadcastWith()
    {
        return $this->payload;
    }
}
