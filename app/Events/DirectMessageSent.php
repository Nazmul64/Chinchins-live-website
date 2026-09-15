<?php

namespace App\Events;

use App\Models\DirectMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DirectMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct(DirectMessage $message)
    {
        $this->message = $message->load([
            'sender:id,account_id,name,display_name,avatar',
            'receiver:id,account_id,name,display_name,avatar'
        ]);
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('user-chat.' . $this->message->receiver_id),
            new PresenceChannel('user-chat.' . $this->message->receiver_id),
            new Channel('conversation.' . $this->message->conversation_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'message.received';
    }

    /**
     * Data payload sent with broadcast.
     */
    public function broadcastWith(): array
    {
        $sender = $this->message->sender;
        $receiver = $this->message->receiver;

        return [
            'id'              => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender_id'       => $this->message->sender_id,
            'receiver_id'     => $this->message->receiver_id,
            'message'         => $this->message->message,
            'attachment_path' => $this->message->attachment_path,
            'image_url'       => $this->message->attachment_path,
            'type'            => $this->message->type,
            'is_read'         => (bool) $this->message->is_read,
            'created_at'      => $this->message->created_at ? $this->message->created_at->toIso8601String() : now()->toIso8601String(),
            'sender'          => $sender ? [
                'id'           => $sender->id,
                'account_id'   => $sender->account_id,
                'name'         => $sender->display_name ?? $sender->name,
                'display_name' => $sender->display_name ?? $sender->name,
                'avatar'       => $sender->avatar_url,
                'avatar_url'   => $sender->avatar_url,
            ] : null,
            'receiver'        => $receiver ? [
                'id'           => $receiver->id,
                'account_id'   => $receiver->account_id,
                'name'         => $receiver->display_name ?? $receiver->name,
                'display_name' => $receiver->display_name ?? $receiver->name,
                'avatar'       => $receiver->avatar_url,
                'avatar_url'   => $receiver->avatar_url,
            ] : null,
        ];
    }
}
