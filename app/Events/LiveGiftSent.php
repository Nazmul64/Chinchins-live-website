<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveGiftSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $liveStreamId;
    public $giftData;

    public function __construct($liveStreamId, array $giftData)
    {
        $this->liveStreamId = (string) $liveStreamId;
        $this->giftData = $giftData;
    }

    public function broadcastOn()
    {
        $channels = [
            new PresenceChannel('presence-live.' . $this->liveStreamId),
            new PresenceChannel('presence-stream.' . $this->liveStreamId),
            new PresenceChannel('presence-room.' . $this->liveStreamId),
            new Channel('live.' . $this->liveStreamId),
            new Channel('stream.' . $this->liveStreamId),
            new Channel('live-stream.' . $this->liveStreamId),
            new Channel('live-room.' . $this->liveStreamId),
        ];

        // Also broadcast directly to host and sender user channels
        if (!empty($this->giftData['sender']['id'] ?? $this->giftData['sender_id'] ?? null)) {
            $senderId = $this->giftData['sender']['id'] ?? $this->giftData['sender_id'];
            $channels[] = new Channel('user.' . $senderId);
        }
        if (!empty($this->giftData['receiver_id'] ?? null)) {
            $channels[] = new Channel('user.' . $this->giftData['receiver_id']);
        }

        return $channels;
    }

    public function broadcastAs()
    {
        return 'LiveGiftSent';
    }

    public function broadcastWith()
    {
        return $this->giftData;
    }
}
