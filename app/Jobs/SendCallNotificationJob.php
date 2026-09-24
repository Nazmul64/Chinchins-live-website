<?php

namespace App\Jobs;

use App\Models\CallSession;
use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCallNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public ?int $callSessionId = null;
    public ?int $callerId = null;
    public ?int $receiverId = null;
    public ?string $roomName = null;
    public ?string $callType = null;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 2;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     */
    public int $maxExceptions = 2;

    /**
     * Create a new job instance.
     * Supports both:
     * 1. __construct(int $callSessionId, int $callerId, int $receiverId)
     * 2. __construct(User|int $caller, int $receiverId, string $roomName, string $callType)
     */
    public function __construct($callerOrSessionId, $receiverId = null, $roomNameOrCallerId = null, $callTypeOrReceiverId = null)
    {
        if ($callerOrSessionId instanceof User) {
            $this->callerId = $callerOrSessionId->id;
            $this->receiverId = (int) $receiverId;
            $this->roomName = (string) $roomNameOrCallerId;
            $this->callType = (string) $callTypeOrReceiverId;
        } elseif (is_numeric($callerOrSessionId) && is_numeric($receiverId) && is_numeric($roomNameOrCallerId)) {
            $this->callSessionId = (int) $callerOrSessionId;
            $this->callerId = (int) $receiverId;
            $this->receiverId = (int) $roomNameOrCallerId;
        } elseif (is_numeric($callerOrSessionId) && is_numeric($receiverId)) {
            $this->callerId = (int) $callerOrSessionId;
            $this->receiverId = (int) $receiverId;
            $this->roomName = (string) $roomNameOrCallerId;
            $this->callType = (string) $callTypeOrReceiverId;
        } else {
            $this->callSessionId = is_numeric($callerOrSessionId) ? (int)$callerOrSessionId : null;
            $this->callerId = is_numeric($receiverId) ? (int)$receiverId : null;
            $this->receiverId = is_numeric($roomNameOrCallerId) ? (int)$roomNameOrCallerId : null;
        }
    }

    /**
     * Execute the job in background queue worker.
     */
    public function handle(): void
    {
        try {
            $call = null;
            if ($this->callSessionId) {
                $call = CallSession::find($this->callSessionId);
            } elseif ($this->roomName) {
                $call = CallSession::where('channel_name', $this->roomName)->latest()->first();
            }

            $caller = User::find($this->callerId ?? ($call ? $call->caller_id : 0));
            $receiver = User::find($this->receiverId ?? ($call ? $call->receiver_id : 0));

            if (!$call && $caller && $receiver && $this->roomName) {
                $call = (object) [
                    'id'                    => 0,
                    'channel_name'          => $this->roomName,
                    'call_type'             => $this->callType ?: 'video',
                    'rate_per_minute'       => 100,
                    'is_free_trial'         => false,
                    'free_duration_seconds' => 0,
                ];
            }

            if (!$call || !$caller || !$receiver) {
                return;
            }

            PushNotificationService::sendIncomingCallPush($call, $caller, $receiver);
        } catch (\Throwable $e) {
            Log::error("SendCallNotificationJob failed: " . $e->getMessage());
        }
    }
}
