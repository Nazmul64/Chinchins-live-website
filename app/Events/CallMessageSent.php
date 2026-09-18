<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $callSessionId;
    public $messageData;

    public function __construct($callSessionId, array $messageData)
    {
        $this->callSessionId = (string) $callSessionId;
        $this->messageData = $messageData;
    }

    public function broadcastOn()
    {
        $channels = [
            new Channel('call.' . $this->callSessionId),
            new Channel('call_chat.' . $this->callSessionId),
        ];

        if (!empty($this->messageData['channel_name']) && $this->messageData['channel_name'] !== $this->callSessionId) {
            $channels[] = new Channel('call.' . $this->messageData['channel_name']);
        }

        if (!empty($this->messageData['receiver_id'])) {
            $channels[] = new Channel('user.' . $this->messageData['receiver_id']);
            $channels[] = new Channel('chat.' . $this->messageData['receiver_id']);
        }

        if (!empty($this->messageData['sender_id'])) {
            $channels[] = new Channel('user.' . $this->messageData['sender_id']);
            $channels[] = new Channel('chat.' . $this->messageData['sender_id']);
        }

        return $channels;
    }

    public function broadcastAs()
    {
        return 'call.message.sent';
    }

    public function broadcastWith()
    {
        return $this->messageData;
    }
}
