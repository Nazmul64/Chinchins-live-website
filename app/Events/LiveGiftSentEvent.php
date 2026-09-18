<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveGiftSentEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $streamId;
    public $giftData;

    public function __construct($streamId, $giftData)
    {
        $this->streamId = (string) $streamId;
        $this->giftData = (array) $giftData;
    }

    /**
     * Broadcast on public/presence channel for this live stream.
     */
    public function broadcastOn()
    {
        $channels = [
            new Channel('live-stream.' . $this->streamId),
            new \Illuminate\Broadcasting\PresenceChannel('live-room.' . $this->streamId),
            new \Illuminate\Broadcasting\PresenceChannel('live-stream.' . $this->streamId),
            new \Illuminate\Broadcasting\PresenceChannel('presence-stream.' . $this->streamId),
            new \Illuminate\Broadcasting\PresenceChannel('presence-live.' . $this->streamId),
            new Channel('live-room.' . $this->streamId),
            new Channel('stream.' . $this->streamId),
            new Channel('live.' . $this->streamId),
        ];

        if (!empty($this->giftData['sender']['id'] ?? $this->giftData['sender_id'] ?? null)) {
            $senderId = $this->giftData['sender']['id'] ?? $this->giftData['sender_id'];
            $channels[] = new Channel('user.' . $senderId);
        }
        if (!empty($this->giftData['receiver_id'] ?? null)) {
            $channels[] = new Channel('user.' . $this->giftData['receiver_id']);
        }

        return $channels;
    }

    /**
     * Broadcast event name for Flutter / Web clients.
     */
    public function broadcastAs()
    {
        return 'gift.received';
    }

    /**
     * Data payload sent to listeners.
     */
    public function broadcastWith()
    {
        return $this->giftData;
    }
}
