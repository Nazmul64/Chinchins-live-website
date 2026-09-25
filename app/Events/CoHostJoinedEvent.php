<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CoHostJoinedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $roomId;
    public array $data;

    /**
     * Create a new event instance.
     *
     * @param string|int $roomId
     * @param array $data ['host_id', 'host_name', 'host_avatar', 'guest_id', 'guest_name', 'guest_avatar', ...]
     */
    public function __construct($roomId, array $data = [])
    {
        $this->roomId = (string) $roomId;
        $this->data = $data;
    }

    /**
     * Broadcast channels for Co-Host Joined event.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('live-room.' . $this->roomId),
            new PresenceChannel('live-room.' . $this->roomId),
            new Channel('live-stream.' . $this->roomId),
            new PresenceChannel('live-stream.' . $this->roomId),
            new Channel('live.' . $this->roomId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'CoHostJoinedEvent';
    }

    /**
     * Broadcast payload with dynamic host and guest info.
     */
    public function broadcastWith(): array
    {
        return array_merge([
            'room_id'      => (string) $this->roomId,
            'event'        => 'CoHostJoinedEvent',
            'action'       => 'cohost_joined',
            'can_publish'  => true,
            'timestamp'    => now()->toIso8601String(),
        ], $this->data);
    }
}
