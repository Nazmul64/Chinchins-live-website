<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GiftSentEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $giftData;

    public function __construct($giftData)
    {
        $this->giftData = $giftData;
    }

    /**
     * Broadcast on call session channel, room channel and receiver channel.
     */
    public function broadcastOn()
    {
        $channels = [];

        // 1. In-call broadcast channels
        if (!empty($this->giftData['call_session_id'])) {
            $sessId = (string) $this->giftData['call_session_id'];
            $channels[] = new Channel('call.' . $sessId);
            $channels[] = new PrivateChannel('call.' . $sessId);
            $channels[] = new Channel('presence-call.' . $sessId);
            $channels[] = new Channel('call_chat.' . $sessId);
            $channels[] = new PrivateChannel('call_chat.' . $sessId);
        }

        // 2. Live Room / Stream broadcast channels
        $roomId = (string) ($this->giftData['room_id'] ?? $this->giftData['stream_id'] ?? '');
        if ($roomId) {
            $channels[] = new Channel('live-room.' . $roomId);
            $channels[] = new PrivateChannel('live-room.' . $roomId);
            $channels[] = new Channel('live-stream.' . $roomId);
            $channels[] = new Channel('presence-live-stream.' . $roomId);
            $channels[] = new Channel('presence-live.' . $roomId);
            $channels[] = new Channel('live.' . $roomId);
        }

        // 3. Receiver personal channels
        $recvId = (string) ($this->giftData['receiver_id'] ?? ($this->giftData['receiver']['id'] ?? ''));
        if ($recvId) {
            $channels[] = new Channel('chat.' . $recvId);
            $channels[] = new PrivateChannel('chat.' . $recvId);
            $channels[] = new Channel('user-chat.' . $recvId);
            $channels[] = new Channel('user.' . $recvId);
            $channels[] = new PrivateChannel('user.' . $recvId);
        }

        // 4. Sender personal channels (so sender also gets confirmation if listening)
        $sendId = (string) ($this->giftData['sender_id'] ?? ($this->giftData['sender']['id'] ?? ''));
        if ($sendId && $sendId !== $recvId) {
            $channels[] = new Channel('user.' . $sendId);
            $channels[] = new Channel('user-chat.' . $sendId);
        }

        if (empty($channels)) {
            $channels[] = new Channel('call.global');
        }

        return $channels;
    }

    /**
     * Event broadcast name.
     */
    public function broadcastAs()
    {
        return 'gift.received';
    }

    /**
     * Data payload for Flutter & Web listeners.
     */
    public function broadcastWith()
    {
        $g = $this->giftData['gift'] ?? [];
        $animationUrl = $g['animation_url'] 
                     ?? $g['animation_full_url'] 
                     ?? $g['animation_asset_url'] 
                     ?? $this->giftData['animation_url'] 
                     ?? $this->giftData['file_url'] 
                     ?? ($g['image_url'] ?? null);

        return array_merge($this->giftData, [
            'id'                  => $this->giftData['id'] ?? ($g['id'] ?? null),
            'gift_id'             => $this->giftData['gift_id'] ?? ($g['id'] ?? null),
            'gift_name'           => $this->giftData['gift_name'] ?? ($g['name'] ?? 'Gift'),
            'name'                => $this->giftData['gift_name'] ?? ($g['name'] ?? 'Gift'),
            'image_url'           => $g['image_url'] ?? ($this->giftData['icon_url'] ?? null),
            'icon_url'            => $g['image_url'] ?? ($this->giftData['icon_url'] ?? null),
            'animation_url'       => $animationUrl,
            'animation_full_url'  => $animationUrl,
            'animation_asset_url' => $animationUrl,
            'file_url'            => $animationUrl,
            'animation_type'      => $g['animation_type'] ?? ($this->giftData['format'] ?? 'svga'),
            'display_type'        => 'fullscreen',
            'quantity'            => (int) ($this->giftData['quantity'] ?? 1),
            'coins'               => (int) ($g['coins'] ?? ($this->giftData['coins_spent'] ?? 0)),
            'timestamp'           => $this->giftData['timestamp'] ?? now()->toIso8601String(),
        ]);
    }
}
