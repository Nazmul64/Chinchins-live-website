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
        return new Channel('live-stream.' . $this->streamId);
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
