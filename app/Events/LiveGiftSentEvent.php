<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveGiftSentEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $streamId;
    public $giftData;

    /**
     * Create a new event instance.
     * Supports both __construct($streamId, $giftData) and __construct($giftDataArray).
     */
    public function __construct($streamIdOrGiftData, $giftData = null)
    {
        if (is_array($streamIdOrGiftData) && $giftData === null) {
            $this->giftData = $streamIdOrGiftData;
            $this->streamId = (string) (
                $streamIdOrGiftData['room_name'] 
                ?? $streamIdOrGiftData['stream_id'] 
                ?? $streamIdOrGiftData['room_id'] 
                ?? 'global'
            );
        } else {
            $this->streamId = (string) $streamIdOrGiftData;
            $this->giftData = (array) $giftData;
        }
    }

    /**
     * Broadcast on public, private, and presence channels for this stream / party room.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new Channel('live-stream.' . $this->streamId),
            new Channel('party.' . $this->streamId),
            new Channel('party-room.' . $this->streamId),
            new PresenceChannel('presence-party.' . $this->streamId),
            new PresenceChannel('live-room.' . $this->streamId),
            new PresenceChannel('live-stream.' . $this->streamId),
            new PresenceChannel('presence-stream.' . $this->streamId),
            new PresenceChannel('presence-live.' . $this->streamId),
            new Channel('live-room.' . $this->streamId),
            new Channel('stream.' . $this->streamId),
            new Channel('live.' . $this->streamId),
        ];

        if (!empty($this->giftData['sender']['id'] ?? $this->giftData['sender_id'] ?? null)) {
            $senderId = $this->giftData['sender']['id'] ?? $this->giftData['sender_id'];
            $channels[] = new Channel('user.' . $senderId);
        }
        if (!empty($this->giftData['receiver_id'] ?? null)) {
            $channels[] = new Channel('user.' . $this->giftData['receiver_id']);
        }

        return $channels;
    }

    /**
     * Broadcast event name for Flutter / Web clients.
     */
    public function broadcastAs(): string
    {
        return 'gift.received';
    }

    /**
     * Data payload sent to listeners.
     */
    public function broadcastWith(): array
    {
        return $this->giftData;
    }
}
