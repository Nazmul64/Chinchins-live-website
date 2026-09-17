<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GiftSentEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $giftData;

    public function __construct($giftData)
    {
        $this->giftData = $giftData;
    }

    /**
     * Broadcast on call session channel, room channel and receiver channel.
     */
    public function broadcastOn()
    {
        $channels = [];

        // 1. In-call broadcast channel
        if (!empty($this->giftData['call_session_id'])) {
            $channels[] = new PrivateChannel('call.' . $this->giftData['call_session_id']);
            $channels[] = new PrivateChannel('call_chat.' . $this->giftData['call_session_id']);
        }

        // 2. Live Room / Stream broadcast channel
        if (!empty($this->giftData['room_id'])) {
            $channels[] = new PrivateChannel('live-room.' . $this->giftData['room_id']);
            $channels[] = new Channel('live.' . $this->giftData['room_id']);
        }

        // 3. Receiver channel
        if (!empty($this->giftData['receiver_id'])) {
            $channels[] = new PrivateChannel('chat.' . $this->giftData['receiver_id']);
            $channels[] = new PrivateChannel('user.' . $this->giftData['receiver_id']);
        }

        if (empty($channels)) {
            $channels[] = new PrivateChannel('call.global');
        }

        return $channels;
    }

    /**
     * Event broadcast name.
     */
    public function broadcastAs()
    {
        return 'gift.received';
    }

    /**
     * Data payload for Flutter & Web listeners.
     */
    public function broadcastWith()
    {
        return $this->giftData;
    }
}
