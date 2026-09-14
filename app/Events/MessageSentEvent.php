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
        $this->message = $message;
    }

    /**
     * Broadcast on private conversation channel.
     */
    public function broadcastOn()
    {
        return [
            new PrivateChannel('conversation.' . $this->message->conversation_id),
            new Channel('conversation.' . $this->message->conversation_id),
        ];
    }

    /**
     * Broadcast event name.
     */
    public function broadcastAs()
    {
        return 'message.sent';
    }

    /**
     * Data payload.
     */
    public function broadcastWith()
    {
        return [
            'id'               => $this->message->id,
            'client_uuid'      => $this->message->client_uuid,
            'conversation_id'  => $this->message->conversation_id,
            'sender_id'        => $this->message->sender_id,
            'message'          => $this->message->message,
            'type'             => $this->message->type ?? 'text',
            'media_url'        => $this->message->media_url,
            'call_id'          => $this->message->call_id,
            'sent_during_call' => (bool) $this->message->sent_during_call,
            'created_at'       => $this->message->created_at?->toIso8601String() ?? now()->toIso8601String(),
        ];
    }
}
