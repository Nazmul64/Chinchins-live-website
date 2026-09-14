<?php

namespace App\Services;

use App\Events\MessageSentEvent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MessagingService
{
    /**
     * Finds or initializes a direct 1-to-1 conversation between two entities.
     */
    public function getOrCreateDirectConversation(int $authUserId, int $recipientId): Conversation
    {
        $conversation = Conversation::where('is_group', false)
            ->whereHas('participants', function ($q) use ($authUserId) {
                $q->where('user_id', $authUserId);
            })
            ->whereHas('participants', function ($q) use ($recipientId) {
                $q->where('user_id', $recipientId);
            })
            ->first();

        if (!$conversation) {
            $conversation = DB::transaction(function () use ($authUserId, $recipientId) {
                $conv = Conversation::create(['is_group' => false]);
                $conv->participants()->attach([$authUserId, $recipientId]);
                return $conv;
            });
        }

        return $conversation;
    }

    /**
     * Core message injection using existing conversation pipelines.
     */
    public function sendMessage(User $sender, array $payload): Message
    {
        return DB::transaction(function () use ($sender, $payload) {
            $conversation = Conversation::findOrFail($payload['conversation_id']);

            // Idempotency check via client_uuid
            if (!empty($payload['client_uuid'])) {
                $existing = Message::where('client_uuid', $payload['client_uuid'])->first();
                if ($existing) {
                    return $existing;
                }
            }

            $message = $conversation->messages()->create([
                'client_uuid'      => $payload['client_uuid'] ?? null,
                'sender_id'        => $sender->id,
                'message'          => $payload['message'],
                'type'             => $payload['type'] ?? 'text',
                'media_url'        => $payload['media_url'] ?? null,
                'call_id'          => $payload['call_id'] ?? null,
                'sent_during_call' => !empty($payload['call_id']),
            ]);

            // Update parent conversation for Messaging List ordering
            $conversation->update([
                'last_message_id' => $message->id,
                'updated_at'      => now(),
            ]);

            // Broadcast on the unified private conversation channel
            try {
                broadcast(new MessageSentEvent($message))->toOthers();
            } catch (\Throwable $e) {}

            return $message;
        });
    }
}
