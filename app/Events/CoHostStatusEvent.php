<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CoHostStatusEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $roomId;
    public $action; // 'invite', 'invited', 'accept', 'accepted', 'reject', 'rejected', 'remove', 'removed'
    public $targetUser;

    /**
     * Create a new event instance.
     * Supports ($roomId, $action, $targetUser) or ($roomId, array $statusData).
     */
    public function __construct($roomId, $action, $targetUser = null)
    {
        $this->roomId = (string) $roomId;
        if (is_array($action) && $targetUser === null) {
            $this->action = $action['action'] ?? 'invited';
            $this->targetUser = $action['target_user'] ?? $action['user'] ?? $action;
        } else {
            $this->action = $action;
            $this->targetUser = $targetUser;
        }
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
        return 'cohost.status.changed';
    }

    public function broadcastWith(): array
    {
        $userObj = is_object($this->targetUser) ? $this->targetUser : (is_array($this->targetUser) ? (object)$this->targetUser : null);
        $userId = $userObj->id ?? ($userObj->user_id ?? null);
        $userName = $userObj->display_name ?? ($userObj->name ?? ($userObj->user_name ?? 'User'));
        $userAvatar = $userObj->avatar_url ?? ($userObj->avatar ?? ($userObj->user_avatar ?? null));

        return [
            'room_id'        => (string) $this->roomId,
            'stream_id'      => (string) $this->roomId,
            'action'         => $this->action,
            'target_user_id' => $userId,
            'target_user'    => [
                'id'           => $userId,
                'user_id'      => $userId,
                'name'         => $userName,
                'display_name' => $userName,
                'avatar_url'   => $userAvatar,
                'avatar'       => $userAvatar,
            ],
            'user'           => [
                'id'           => $userId,
                'display_name' => $userName,
                'avatar_url'   => $userAvatar,
            ],
            'timestamp'      => now()->toIso8601String(),
        ];
    }
}
