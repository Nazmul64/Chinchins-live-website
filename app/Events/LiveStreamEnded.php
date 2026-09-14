<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveStreamEnded implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $liveStreamId;
    public $summaryData;

    public function __construct($liveStreamId, array $summaryData)
    {
        $this->liveStreamId = (string) $liveStreamId;
        $this->summaryData = $summaryData;
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
        return 'LiveStreamEnded';
    }

    public function broadcastWith()
    {
        return $this->summaryData;
    }
}
