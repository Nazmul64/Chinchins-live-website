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

    public int $callSessionId;
    public int $callerId;
    public int $receiverId;

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
     */
    public function __construct(int $callSessionId, int $callerId, int $receiverId)
    {
        $this->callSessionId = $callSessionId;
        $this->callerId = $callerId;
        $this->receiverId = $receiverId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $call = CallSession::find($this->callSessionId);
            $caller = User::find($this->callerId);
            $receiver = User::find($this->receiverId);

            if (!$call || !$caller || !$receiver) {
                return;
            }

            PushNotificationService::sendIncomingCallPush($call, $caller, $receiver);
        } catch (\Throwable $e) {
            Log::error("SendCallNotificationJob failed: " . $e->getMessage());
        }
    }
}
