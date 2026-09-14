<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InCallMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $callId;
    public $messageData;

    public function __construct($callId, array $messageData)
    {
        $this->callId = (string) $callId;
        $this->messageData = $messageData;
    }

    public function broadcastOn()
    {
        $channels = [
            new PresenceChannel('presence-call.' . $this->callId),
            new Channel('call.' . $this->callId),
            new Channel('call_chat.' . $this->callId),
        ];

        if (!empty($this->messageData['channel_name']) && $this->messageData['channel_name'] !== $this->callId) {
            $channels[] = new Channel('call.' . $this->messageData['channel_name']);
        }

        if (!empty($this->messageData['receiver_id'])) {
            $channels[] = new Channel('user.' . $this->messageData['receiver_id']);
            $channels[] = new Channel('chat.' . $this->messageData['receiver_id']);
        }

        return $channels;
    }

    public function broadcastAs()
    {
        return 'InCallMessageSent';
    }

    public function broadcastWith()
    {
        return $this->messageData;
    }
}
