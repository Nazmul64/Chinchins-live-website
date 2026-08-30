<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebRTCAnswer implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param int $targetUserId Caller's user ID
     * @param int $callId
     * @param string $roomId
     * @param string $sdp RAW unmodified SDP answer string
     * @param array $extraPayload
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
        return 'webrtc.answer';
    }

    public function broadcastWith(): array
    {
        $sdpString = is_array($this->sdp) ? ($this->sdp['sdp'] ?? '') : (string)$this->sdp;

        return array_merge([
            'event'       => 'webrtc.answer',
            'call_id'     => $this->callId,
            'room_id'     => $this->roomId,
            'type'        => 'answer',
            'sdp'         => $sdpString,
            'description' => [
                'sdp'  => $sdpString,
                'type' => 'answer',
            ],
        ], $this->extraPayload);
    }
}
