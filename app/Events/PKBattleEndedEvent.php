<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PKBattleEndedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $roomIds;
    public array $resultData;

    public function __construct(array|string|int $roomIds, array $resultData)
    {
        $this->roomIds = is_array($roomIds) ? $roomIds : [$roomIds];
        $this->resultData = $resultData;
    }

    public function broadcastOn(): array
    {
        $channels = [];
        foreach ($this->roomIds as $rId) {
            $channels[] = new Channel('live-stream.' . $rId);
            $channels[] = new PresenceChannel('presence-stream.' . $rId);
            $channels[] = new PresenceChannel('presence-live.' . $rId);
            $channels[] = new Channel('live-room.' . $rId);
            $channels[] = new Channel('live.' . $rId);
        }
        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'pk.ended';
    }

    public function broadcastWith(): array
    {
        return array_merge([
            'event'     => 'pk.ended',
            'timestamp' => now()->toIso8601String(),
        ], $this->resultData);
    }
}
