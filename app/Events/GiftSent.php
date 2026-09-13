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
            new Channel('live.' . $this->channelId),
        ];

        if (!empty($this->payload['receiver_id'])) {
            $channels[] = new Channel('user.' . $this->payload['receiver_id']);
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
