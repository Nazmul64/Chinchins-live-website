<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CoHostAcceptedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $roomId;
    public $guestUserId;
    public $guestData;

    /**
     * Create a new event instance.
     */
    public function __construct($roomId, $guestUserId, $guestData = null)
    {
        $this->roomId = (string) $roomId;
        $this->guestUserId = (int) $guestUserId;
        $this->guestData = $guestData ?? [
            'guest_user_id' => $this->guestUserId,
            'room_id'       => $this->roomId,
            'status'        => 'accepted',
            'action'        => 'accept',
        ];
    }

    /**
     * Broadcast channels for Co-Host Accepted event.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('live-room.' . $this->roomId),
            new PresenceChannel('live-room.' . $this->roomId),
            new Channel('live-stream.' . $this->roomId),
            new PresenceChannel('live-stream.' . $this->roomId),
            new Channel('live.' . $this->roomId),
            new Channel('user.' . $this->guestUserId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'cohost.accepted';
    }

    /**
     * Broadcast payload.
     */
    public function broadcastWith(): array
    {
        return [
            'room_id'       => (string) $this->roomId,
            'guest_user_id' => (int) $this->guestUserId,
            'status'        => 'accepted',
            'action'        => 'accept',
            'can_publish'   => true,
            'data'          => $this->guestData,
            'timestamp'     => now()->toIso8601String(),
        ];
    }
}
