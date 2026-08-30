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

    public function broadcastWith(): array
    {
        $candStr = is_array($this->candidate) ? ($this->candidate['candidate'] ?? '') : (string)$this->candidate;
        $sdpMid = $this->sdpMid ?? (is_array($this->candidate) ? ($this->candidate['sdpMid'] ?? $this->candidate['sdp_mid'] ?? null) : null);
        $sdpMLineIndex = $this->sdpMLineIndex ?? (is_array($this->candidate) ? ($this->candidate['sdpMLineIndex'] ?? $this->candidate['sdp_mline_index'] ?? 0) : 0);

        return array_merge([
            'event'         => 'webrtc.ice_candidate',
            'call_id'       => $this->callId,
            'room_id'       => $this->roomId,
            'candidate'     => $candStr ?: $this->candidate,
            'sdpMid'        => $sdpMid,
            'sdpMLineIndex' => $sdpMLineIndex,
        ], $this->extraPayload);
    }
}
