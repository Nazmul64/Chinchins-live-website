<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GiftReceivedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $receiverId;
    public array $giftData;

    /**
     * Create a new event instance.
     *
     * @param int|string $receiverId
     * @param array $giftData
     */
    public function __construct(int|string $receiverId, array $giftData)
    {
        $this->receiverId = (int) $receiverId;
        $this->giftData = $giftData;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [];
        if ($this->receiverId > 0) {
            $channels[] = new PrivateChannel('user.' . $this->receiverId);
            $channels[] = new PrivateChannel('private-user.' . $this->receiverId);
            $channels[] = new Channel('user.' . $this->receiverId);
        }
        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'gift.received';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return array_merge([
            'event'       => 'gift.received',
            'action'      => 'gift_received',
            'receiver_id' => $this->receiverId,
            'timestamp'   => now()->toIso8601String(),
        ], $this->giftData);
    }
}
