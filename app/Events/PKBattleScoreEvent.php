<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PKBattleScoreEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $roomIds;
    public array $scoreData;

    public function __construct(array|string|int $roomIds, array $scoreData)
    {
        $this->roomIds = is_array($roomIds) ? $roomIds : [$roomIds];
        $this->scoreData = $scoreData;
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
        return 'pk.score_updated';
    }

    public function broadcastWith(): array
    {
        return array_merge([
            'event'     => 'pk.score_updated',
            'timestamp' => now()->toIso8601String(),
        ], $this->scoreData);
    }
}
