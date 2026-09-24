<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GlobalTopGiftBannerEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $liveRoomName;
    public array $bannerData;

    /**
     * Create a new event instance.
     */
    public function __construct(string|int $liveRoomName, array $bannerData)
    {
        $this->liveRoomName = (string) $liveRoomName;
        $this->bannerData = $bannerData;
    }

    /**
     * Broadcast to live stream room channels and global alert channels.
     */
    public function broadcastOn(): array
    {
        $cleanRoom = str_replace(['presence-stream.', 'presence-live.', 'live-stream.', 'live.'], '', $this->liveRoomName);

        return [
            new Channel('live-stream.' . $cleanRoom),
            new PresenceChannel('presence-stream.' . $cleanRoom),
            new PresenceChannel('presence-live.' . $cleanRoom),
            new Channel('live-room.' . $cleanRoom),
            new Channel('live.' . $cleanRoom),
            new Channel('global-alerts'),
        ];
    }

    /**
     * Event name for Flutter / Web UI.
     */
    public function broadcastAs(): string
    {
        return 'global.top_gift_banner';
    }

    /**
     * Data payload for top sliding banner animation.
     */
    public function broadcastWith(): array
    {
        return array_merge([
            'event'           => 'global.top_gift_banner',
            'room_name'       => $this->liveRoomName,
            'display_type'    => 'top_banner',
            'banner_duration' => 4, // 4 seconds animation
            'timestamp'       => now()->toIso8601String(),
        ], $this->bannerData);
    }
}
