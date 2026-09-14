<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveGuestKicked implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $liveStreamId;
    public $guestUserId;
    public $kickData;

    public function __construct($liveStreamId, $guestUserId, array $kickData)
    {
        $this->liveStreamId = (string) $liveStreamId;
        $this->guestUserId = (string) $guestUserId;
        $this->kickData = $kickData;
    }

    public function broadcastOn()
    {
        return [
            new PresenceChannel('presence-live.' . $this->liveStreamId),
            new Channel('user.' . $this->guestUserId),
        ];
    }

    public function broadcastAs()
    {
        return 'LiveGuestKicked';
    }

    public function broadcastWith()
    {
        return $this->kickData;
    }
}
