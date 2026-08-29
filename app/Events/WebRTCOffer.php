<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebRTCOffer implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param int $targetUserId Receiver's user ID
     * @param int $callId
     * @param string $roomId
     * @param string $sdp RAW unmodified SDP offer string
     * @param array $extraPayload Any extra WebRTC fields untouched
     */
    public function __construct(
        public int $targetUserId,
        public int $callId,
        public string $roomId,
        public string $sdp,
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
        return 'webrtc.offer';
    }

    /**
     * CRITICAL: Do NOT alter or reformat SDP.
     */
    public function broadcastWith(): array
    {
        return array_merge([
            'event'   => 'webrtc.offer',
            'call_id' => $this->callId,
            'room_id' => $this->roomId,
            'type'    => 'offer',
            'sdp'     => $this->sdp,
        ], $this->extraPayload);
    }
}
