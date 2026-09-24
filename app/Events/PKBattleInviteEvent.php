<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PKBattleInviteEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int|string $targetHostId;
    public array $pkData;

    public function __construct(int|string $targetHostId, array $pkData)
    {
        $this->targetHostId = $targetHostId;
        $this->pkData = $pkData;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->targetHostId),
            new Channel('user.' . $this->targetHostId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'pk.invite';
    }

    public function broadcastWith(): array
    {
        return array_merge([
            'event'     => 'pk.invite',
            'timestamp' => now()->toIso8601String(),
        ], $this->pkData);
    }
}
