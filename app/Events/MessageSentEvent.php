<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSentEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message->loadMissing(['sender:id,account_id,name,nickname,avatar']);
    }

    /**
     * Broadcast on call session channel and receiver's personal chat channel.
     */
    public function broadcastOn()
    {
        $channels = [];

        // 1. Call Session Channel
        if (!empty($this->message->call_session_id)) {
            $channels[] = new PrivateChannel('call.' . $this->message->call_session_id);
            $channels[] = new PrivateChannel('call_chat.' . $this->message->call_session_id);
        }

        if (!empty($this->message->call_id) && $this->message->call_id !== $this->message->call_session_id) {
            $channels[] = new PrivateChannel('call.' . $this->message->call_id);
        }

        // 2. Receiver Personal Chat Channel
        if (!empty($this->message->receiver_id)) {
            $channels[] = new PrivateChannel('chat.' . $this->message->receiver_id);
            $channels[] = new PrivateChannel('user-chat.' . $this->message->receiver_id);
            $channels[] = new PrivateChannel('user.' . $this->message->receiver_id);
        }

        // 3. Conversation Channel
        if (!empty($this->message->conversation_id)) {
            $channels[] = new PrivateChannel('conversation.' . $this->message->conversation_id);
            $channels[] = new Channel('conversation.' . $this->message->conversation_id);
        }

        if (empty($channels)) {
            $channels[] = new PrivateChannel('chat.' . ($this->message->receiver_id ?? $this->message->sender_id));
        }

        return $channels;
    }

    /**
     * Broadcast event name.
     */
    public function broadcastAs()
    {
        return 'message.sent';
    }

    /**
     * Data payload for Flutter & Web clients.
     */
    public function broadcastWith()
    {
        $sender = $this->message->sender;

        return [
            'id'               => $this->message->id,
            'client_uuid'      => $this->message->client_uuid,
            'sender_id'        => $this->message->sender_id,
            'receiver_id'      => $this->message->receiver_id,
            'call_session_id'  => $this->message->call_session_id ?: $this->message->call_id,
            'call_id'          => $this->message->call_id ?: $this->message->call_session_id,
            'conversation_id'  => $this->message->conversation_id,
            'message'          => $this->message->message,
            'type'             => $this->message->type ?? 'text',
            'media_url'        => $this->message->media_url,
            'is_read'          => (bool) $this->message->is_read,
            'sent_during_call' => (bool) ($this->message->sent_during_call || !empty($this->message->call_session_id)),
            'sender'           => $sender ? [
                'id'           => $sender->id,
                'account_id'   => $sender->account_id,
                'name'         => $sender->display_name ?? $sender->name ?? $sender->nickname ?? 'User',
                'display_name' => $sender->display_name ?? $sender->name ?? $sender->nickname ?? 'User',
                'avatar'       => $sender->avatar_url,
                'avatar_url'   => $sender->avatar_url,
            ] : null,
            'created_at'       => $this->message->created_at?->toIso8601String() ?? now()->toIso8601String(),
            'timestamp'        => now()->toIso8601String(),
        ];
    }
}
