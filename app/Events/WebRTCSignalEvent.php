<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebRTCSignalEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $roomId;
    public $signalData; // { from_user_id, to_user_id, type: offer/answer/candidate, data }

    /**
     * Create a new event instance.
     */
    public function __construct($roomId, $signalData)
    {
        $this->roomId = (string) $roomId;
        $this->signalData = is_object($signalData) && method_exists($signalData, 'toArray') 
            ? $signalData->toArray() 
            : (array) $signalData;
    }

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

    public function broadcastAs(): string
    {
        return 'webrtc.signal';
    }

    public function broadcastWith(): array
    {
        $fromId = $this->signalData['from_user_id'] ?? $this->signalData['sender_id'] ?? null;
        $toId = $this->signalData['to_user_id'] ?? $this->signalData['target_user_id'] ?? null;
        $type = $this->signalData['type'] ?? 'offer';
        $payload = $this->signalData['data'] ?? $this->signalData['payload'] ?? $this->signalData['sdp_or_candidate'] ?? null;

        return [
            'room_id'          => (string) $this->roomId,
            'stream_id'        => (string) $this->roomId,
            'from_user_id'     => $fromId,
            'sender_id'        => $fromId,
            'to_user_id'       => $toId,
            'target_user_id'   => $toId,
            'type'             => $type,
            'data'             => $payload,
            'payload'          => $payload,
            'sdp_or_candidate' => $payload,
            'timestamp'        => $this->signalData['timestamp'] ?? now()->toIso8601String(),
        ];
    }
}
