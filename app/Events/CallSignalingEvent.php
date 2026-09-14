<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallSignalingEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $callId;
    public $signalData;

    public function __construct($callId, array $signalData)
    {
        $this->callId = (string) $callId;
        $this->signalData = $signalData;
    }

    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('call.' . $this->callId),
            new Channel('call.' . $this->callId),
        ];

        if (!empty($this->signalData['receiver_id'])) {
            $channels[] = new PrivateChannel('user.' . $this->signalData['receiver_id']);
            $channels[] = new Channel('user.' . $this->signalData['receiver_id']);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'CallSignalingEvent';
    }

    public function broadcastWith(): array
    {
        return [
            'call_id'     => $this->callId,
            'sender_id'   => $this->signalData['sender_id'] ?? null,
            'receiver_id' => $this->signalData['receiver_id'] ?? null,
            'type'        => $this->signalData['type'] ?? 'candidate',
            'payload'     => $this->signalData['payload'] ?? null,
            'timestamp'   => $this->signalData['timestamp'] ?? now()->toIso8601String(),
        ];
    }
}
