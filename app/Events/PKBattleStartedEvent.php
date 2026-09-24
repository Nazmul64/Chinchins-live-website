<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PKBattleStartedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $roomIds;
    public array $pkData;

    public function __construct(array|string|int $roomIds, array $pkData)
    {
        $this->roomIds = is_array($roomIds) ? $roomIds : [$roomIds];
        $this->pkData = $pkData;
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
        return 'pk.started';
    }

    public function broadcastWith(): array
    {
        return array_merge([
            'event'     => 'pk.started',
            'timestamp' => now()->toIso8601String(),
        ], $this->pkData);
    }
}
