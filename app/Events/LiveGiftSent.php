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
        return [
            new PresenceChannel('presence-live.' . $this->liveStreamId),
            new Channel('live.' . $this->liveStreamId),
        ];
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
