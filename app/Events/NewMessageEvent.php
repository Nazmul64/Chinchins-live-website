<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMessageEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public int $senderId;
    public int $receiverId;

    /**
     * Create a new event instance.
     */
    public function __construct(mixed $message)
    {
        if ($message instanceof Message) {
            $this->message = $message->loadMissing(['sender:id,account_id,name,display_name,avatar,gender,level']);
            $this->senderId = (int) $message->sender_id;
            $this->receiverId = (int) $message->receiver_id;
        } elseif (is_object($message)) {
            $this->message = $message;
            $this->senderId = (int) ($message->sender_id ?? 0);
            $this->receiverId = (int) ($message->receiver_id ?? 0);
        } elseif (is_array($message)) {
            $this->message = (object) $message;
            $this->senderId = (int) ($message['sender_id'] ?? 0);
            $this->receiverId = (int) ($message['receiver_id'] ?? 0);
        }
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        $channels = [];

        if ($this->receiverId > 0) {
            $channels[] = new PrivateChannel('user.' . $this->receiverId);
            $channels[] = new Channel('user.' . $this->receiverId);
            $channels[] = new PrivateChannel('chat.' . $this->receiverId);
            $channels[] = new Channel('chat.' . $this->receiverId);
            $channels[] = new Channel('user-chat.' . $this->receiverId);
        }

        if ($this->senderId > 0) {
            $channels[] = new PrivateChannel('user.' . $this->senderId);
            $channels[] = new Channel('user.' . $this->senderId);
            $channels[] = new PrivateChannel('chat.' . $this->senderId);
            $channels[] = new Channel('chat.' . $this->senderId);
        }

        if (!empty($this->message->conversation_id)) {
            $channels[] = new PrivateChannel('conversation.' . $this->message->conversation_id);
            $channels[] = new Channel('conversation.' . $this->message->conversation_id);
        }

        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'NewMessageEvent';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        $msg = $this->message;
        $sender = is_object($msg) ? ($msg->sender ?? null) : null;

        return [
            'event'       => 'NewMessageEvent',
            'id'          => is_object($msg) ? ($msg->id ?? null) : null,
            'sender_id'   => $this->senderId,
            'receiver_id' => $this->receiverId,
            'message'     => is_object($msg) ? ($msg->message ?? '') : '',
            'type'        => is_object($msg) ? ($msg->type ?? 'text') : 'text',
            'is_read'     => false,
            'sender'      => $sender ? [
                'id'           => $sender->id,
                'account_id'   => $sender->account_id,
                'name'         => $sender->display_name ?? $sender->name ?? 'User',
                'display_name' => $sender->display_name ?? $sender->name ?? 'User',
                'avatar_url'   => $sender->avatar_url ?? null,
                'level'        => $sender->level ?? 'Lv1',
            ] : null,
            'created_at'  => now()->toIso8601String(),
            'timestamp'   => now()->toIso8601String(),
        ];
    }
}
