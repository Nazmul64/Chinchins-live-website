<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebRTCICECandidate implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param int $targetUserId Peer user ID to receive the ICE candidate
     * @param int $callId
     * @param string $roomId
     * @param string|array $candidate RAW candidate string or candidate object
     * @param string|null $sdpMid
     * @param int|null $sdpMLineIndex
     * @param array $extraPayload
     */
    public function __construct(
        public int $targetUserId,
        public int $callId,
        public string $roomId,
        public mixed $candidate,
        public ?string $sdpMid = null,
        public ?int $sdpMLineIndex = null,
        public array $extraPayload = []
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->targetUserId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'webrtc.ice_candidate';
    }

    /**
     * CRITICAL: Do NOT alter or filter candidate data.
     */
    public function broadcastWith(): array
    {
        return array_merge([
            'event'         => 'webrtc.ice_candidate',
            'call_id'       => $this->callId,
            'room_id'       => $this->roomId,
            'candidate'     => $this->candidate,
            'sdpMid'        => $this->sdpMid,
            'sdpMLineIndex' => $this->sdpMLineIndex,
        ], $this->extraPayload);
    }
}
